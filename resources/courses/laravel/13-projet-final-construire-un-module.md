---
title: Projet final : construire un module
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru les routes, les contrôleurs, la base, Eloquent, les relations, la validation, les policies, Inertia et un CRUD complet. Ce dernier chapitre est un **projet** : tu construis seul un module entier, avec un cahier des charges, comme dans une vraie mission.

Ici, je ne te donne plus la solution étape par étape. Tu reçois un objectif, des contraintes, un plan et des indices. C'est la meilleure façon de vérifier que tu sais vraiment, et pas seulement que tu as su suivre.

À la fin du projet, tu auras :

- conçu un schéma de base avec une relation plusieurs-à-plusieurs ;
- construit un module complet, sécurisé et testé, de la base jusqu'à l'interface ;
- relu ton propre code avec une grille d'évaluation professionnelle ;
- préparé la mise en production avec une liste de vérifications.

Prévois six heures, réparties sur deux ou trois séances. Travaille dans une branche Git dédiée et fais un commit à chaque phase.

## Le sujet : tags et recherche pour les fiches mémo

Reprends le CRUD des fiches mémo du chapitre précédent. Ton module ajoute à chaque fiche des **tags** (étiquettes) et un **filtre de recherche**, pour retrouver rapidement ses notes.

### Ce que l'utilisateur doit pouvoir faire

1. Créer et nommer ses propres tags (« routes », « eloquent », « sécurité »).
2. Attacher plusieurs tags à une fiche, depuis le formulaire de création ou de modification.
3. Voir les tags de chaque fiche dans la liste.
4. Filtrer la liste de ses fiches par tag, via l'adresse `/memos?tag=routes`.
5. Rechercher une fiche par mot-clé dans le titre ou le contenu, via `/memos?q=policy`.
6. Cumuler les deux : `/memos?tag=routes&q=nom`.
7. Supprimer un tag sans supprimer les fiches qui le portent.

### Les règles à respecter absolument

- Les tags sont **privés** : chaque utilisateur a les siens. Deux utilisateurs peuvent avoir chacun un tag « laravel ».
- Un utilisateur ne peut **jamais** voir, utiliser ou modifier les tags ou les fiches d'un autre.
- Un tag n'existe qu'une fois par utilisateur : pas deux « Laravel » pour la même personne.
- Un nom de tag est limité à 50 caractères.
- La liste doit rester rapide : **pas de problème N+1**, pagination de 12 fiches.

Relis cette liste deux fois. Chaque règle correspond à un chapitre : relations, validation, policies, performance.

## Phase 1 : concevoir le schéma

Avant d'écrire du code, **dessine** sur papier. Réponds à ces questions :

- quelles tables as-tu besoin d'ajouter ?
- quelle relation relie fiches et tags : un-à-plusieurs, ou plusieurs-à-plusieurs ?
- où se trouve la clé étrangère de l'utilisateur sur les tags ?
- quelle contrainte garantit l'unicité du tag par utilisateur ?
- que se passe-t-il pour les liens quand un tag est supprimé ?

Voici la solution attendue, à comparer avec ton dessin **après** avoir réfléchi :

| Table | Colonnes principales | Remarques |
| --- | --- | --- |
| `tags` | `id`, `user_id`, `name`, `slug`, horodatages | Unicité sur (`user_id`, `slug`) |
| `memo_tag` | `memo_id`, `tag_id` | Clé primaire composite, suppression en cascade des deux côtés |

Écris les deux migrations, lance `php artisan migrate`, puis vérifie dans phpMyAdmin que l'unicité empêche de créer deux fois le même `slug` pour un même `user_id`.

> **Point de contrôle** : tu dois pouvoir insérer le même `slug` pour deux utilisateurs différents, et être refusé pour le même utilisateur.

:::quiz
Une fiche peut avoir plusieurs tags et un tag peut s'appliquer à plusieurs fiches. Quelle structure faut-il ?
- [ ] Une colonne tag_id dans la table memos
- [ ] Une colonne memo_id dans la table tags
- [x] Une table pivot memo_tag avec deux clés étrangères
- [ ] Deux tables indépendantes sans lien
> Une relation plusieurs-à-plusieurs se représente par une table pivot qui relie les identifiants des deux côtés.
:::

## Phase 2 : modèles, relations et policy

Crée le modèle `Tag`, puis déclare les relations :

- `Memo::tags()` et `Tag::memos()` avec `belongsToMany` ;
- `Tag::user()` avec `belongsTo`, et `User::tags()` avec `hasMany`.

Écris `TagPolicy` : seul le propriétaire peut modifier ou supprimer un tag. Vérifie le tout dans `tinker`.

**Un indice sur le `slug`.** On le fabrique à partir du nom avec `Str::slug()`. Tu peux le calculer dans la Form Request (`prepareForValidation`) pour que la règle d'unicité s'applique directement sur le `slug`.

```php
protected function prepareForValidation(): void
{
    $this->merge(['slug' => Str::slug((string) $this->input('name'))]);
}
```

> **Point de contrôle** : dans `tinker`, `$memo->tags()->sync([...])` doit remplir la table pivot, et `$memo->tags` doit retourner la collection des tags.

## Phase 3 : le CRUD des tags

Construis la gestion des tags en suivant la liste de contrôle du chapitre précédent : migration, modèle, policy, Form Requests, contrôleur, routes, page, tests.

Deux points demandent de la vigilance.

**L'unicité par utilisateur, à la création et à la modification :**

```php
'slug' => [
    'required',
    'string',
    Rule::unique('tags', 'slug')
        ->where('user_id', $this->user()->id)
        ->ignore($this->route('tag')),
],
```

**La suppression** : la cascade de la table pivot retire les liens, mais **pas** les fiches. Teste-le explicitement.

## Phase 4 : associer les tags aux fiches

C'est le cœur du projet. Modifie `StoreMemoRequest` et `UpdateMemoRequest` pour accepter une liste d'identifiants de tags :

```php
'tag_ids' => ['nullable', 'array'],
'tag_ids.*' => ['integer', 'exists:tags,id'],
```

Mais attention, cette règle a un **trou de sécurité** : `exists:tags,id` vérifie que le tag existe, pas qu'il appartient à l'utilisateur. Un utilisateur malveillant pourrait attacher à sa fiche le tag d'un autre, voire en deviner l'existence. À toi de colmater : restreins la règle aux tags de l'utilisateur.

```php
use Illuminate\Validation\Rule;

'tag_ids.*' => [
    'integer',
    Rule::exists('tags', 'id')->where('user_id', $this->user()->id),
],
```

Dans le contrôleur, après avoir enregistré la fiche, synchronise les liens :

```php
$memo->tags()->sync($request->validated('tag_ids', []));
```

Pense aussi à :

- **charger les tags** dans la liste avec `with('tags:id,name,slug')` pour éviter le N+1 ;
- afficher des cases à cocher dans `MemoForm`, alimentées par les tags de l'utilisateur ;
- ne transmettre à la page que les champs utiles des tags.

> **Point de contrôle** : crée deux utilisateurs. Avec le second, envoie à la main un `tag_ids` contenant l'identifiant d'un tag du premier. La requête doit être refusée par la validation.

:::quiz
Pourquoi la règle `exists:tags,id` seule est-elle insuffisante ici ?
- [ ] Elle est trop lente
- [x] Elle vérifie l'existence du tag mais pas son appartenance à l'utilisateur
- [ ] Elle n'accepte pas les tableaux
- [ ] Elle supprime les tags inexistants
> Sans restriction sur user_id, un utilisateur pourrait attacher le tag d'un autre à sa fiche. Il faut contraindre la règle aux tags du propriétaire.
:::

## Phase 5 : filtres et recherche

Modifie `MemoController@index` pour accepter les paramètres `tag` et `q`. L'idée : construire la requête petit à petit, en ajoutant des conditions seulement si le paramètre est présent.

```php
public function index(Request $request)
{
    $memos = $request->user()
        ->memos()
        ->with('tags:id,name,slug')
        ->when($request->query('tag'), fn ($query, $slug) => $query->whereHas(
            'tags',
            fn ($tags) => $tags->where('slug', $slug)
        ))
        ->when($request->query('q'), fn ($query, $terme) => $query->where(
            fn ($inner) => $inner
                ->where('title', 'like', "%{$terme}%")
                ->orWhere('content', 'like', "%{$terme}%")
        ))
        ->latest('updated_at')
        ->paginate(12)
        ->withQueryString();

    return Inertia::render('Memos/Index', [
        'memos' => $memos,
        'filters' => $request->only('tag', 'q'),
        'tags' => $request->user()->tags()->orderBy('name')->get(['id', 'name', 'slug']),
    ]);
}
```

Plusieurs détails à comprendre, pas à recopier :

- `when(valeur, callback)` n'applique la condition que si la valeur est présente : ça évite des `if` en cascade ;
- les conditions `like` sont **regroupées dans une closure**, pour que le `orWhere` ne contamine pas les autres filtres (sans cela, le filtre par tag serait contourné) ;
- `withQueryString()` conserve `tag` et `q` dans les liens de pagination ;
- `filters` renvoie les valeurs actuelles à la page, pour pré-remplir la recherche.

Côté React, ajoute un champ de recherche et une liste de tags cliquables. Utilise `router.get('/memos', { q, tag }, { preserveState: true, replace: true })` pour ne pas polluer l'historique du navigateur.

> **Attention** : n'envoie jamais le terme de recherche tel quel dans une requête SQL écrite à la main. Ici, le constructeur de requêtes d'Eloquent protège contre l'injection SQL. N'écris pas de SQL brut avec la saisie de l'utilisateur.

## Phase 6 : tests

Écris au minimum ces tests. Ils servent de **cahier de recette** de ton module.

1. Un utilisateur crée un tag.
2. Un nom de tag vide est refusé.
3. Un slug en double est refusé pour le même utilisateur, mais accepté pour un autre.
4. Un intrus ne peut ni modifier ni supprimer le tag d'un autre.
5. Une fiche peut recevoir plusieurs tags ; `sync` retire ceux qui sont décochés.
6. Un utilisateur ne peut **pas** attacher le tag d'un autre à sa fiche.
7. `?tag=routes` ne retourne que les fiches portant ce tag.
8. `?q=policy` retourne les fiches dont le titre ou le contenu contient ce mot.
9. `?tag=routes&q=nom` combine bien les deux filtres.
10. Supprimer un tag laisse les fiches en place.

Pour vérifier l'absence de N+1 dans la liste, tu peux utiliser `DB::enableQueryLog()` et compter les requêtes pour 3 fiches, puis pour 30 : le nombre doit rester **le même**.

```php
DB::enableQueryLog();
$this->actingAs($user)->get(route('memos.index'));
$this->assertLessThanOrEqual(8, count(DB::getQueryLog()));
```

## Grille d'auto-évaluation

Relis ton propre travail comme le ferait un relecteur. Note chaque ligne sur 2 : 0 (absent), 1 (partiel), 2 (solide).

| Critère | Questions à te poser |
| --- | --- |
| Schéma | Clés étrangères, unicité, suppression en cascade corrects ? |
| Sécurité | Aucun accès aux données d'un autre ? `user_id` absent de `$fillable` ? |
| Validation | Toutes les entrées contrôlées, y compris `tag_ids.*` ? |
| Performance | Pas de N+1, liste paginée, requêtes raisonnables ? |
| Contrôleur | Méthodes courtes, logique déléguée ? |
| Interface | États vide, erreur et chargement ? Libellés et accessibilité ? |
| Tests | Cas normal, cas refusés, filtres, absence de N+1 ? |
| Lisibilité | Noms clairs, code sans doublon ? |

**Total de 14 sur 16 ou plus** : ton module est de niveau professionnel. **Entre 10 et 13** : il fonctionne, mais retravaille les lignes à 0 ou 1. **En dessous de 10** : reprends la phase où tu as le plus de lignes faibles, c'est là que se cache l'apprentissage.

## Préparer la mise en production

Un module n'est pas terminé tant qu'il n'est pas déployé en sécurité. Passe cette liste avant de mettre en ligne.

- `APP_ENV=production` et `APP_DEBUG=false` dans l'environnement du serveur.
- `APP_KEY` définie, secrets uniquement dans les variables d'environnement, **jamais** dans Git.
- `php artisan migrate --force` : jamais `migrate:fresh`.
- Sauvegarde de la base **avant** de migrer.
- `npm run build` exécuté, dossier `public/build` présent.
- `php artisan config:cache`, `route:cache` et `view:cache` pour accélérer le démarrage (les routes doivent utiliser des contrôleurs, pas des fonctions anonymes).
- Tests au vert : `php artisan test`.
- Une vérification manuelle du parcours complet avec deux comptes différents.

> **À retenir** : la différence entre un code qui marche sur ton ordinateur et un code prêt pour de vrais utilisateurs tient dans cette liste. Sécurité, sauvegarde, tests, configuration.

## Pour aller plus loin

Quand ton module est terminé et que tu te sens à l'aise, voici des pistes :

- ajouter des **couleurs** aux tags et les afficher dans l'interface ;
- permettre de **renommer** un tag en fusionnant les doublons ;
- ajouter un **compteur** de fiches par tag avec `withCount` ;
- remplacer la recherche `like` par une recherche plein texte de la base ;
- documenter le module dans un `README` pour qu'un autre développeur le reprenne ;
- présenter ton module à un camarade et expliquer chaque décision de sécurité.

Expliquer son travail à quelqu'un d'autre est le meilleur test de compréhension qui soit.

## Erreurs fréquentes sur ce projet

- **`exists` sans restriction d'utilisateur.** C'est la faille la plus courante du module.
- **Un `orWhere` non groupé.** Il casse les autres filtres : mets-le dans une closure.
- **Oublier `withQueryString()`.** Les filtres disparaissent en changeant de page.
- **Oublier `with('tags')`.** La liste devient lente avec le N+1.
- **Supprimer un tag en supprimant les fiches.** Vérifie les cascades de la table pivot.
- **Livrer sans tests de refus.** La sécurité ne se prouve qu'avec des cas où l'accès est refusé.

## À retenir

- Un module se conçoit d'abord sur papier : tables, relations, contraintes, droits.
- Les règles de sécurité (propriété des données, unicité par utilisateur) doivent figurer dans la validation, la policy et les tests.
- `exists` doit toujours être contraint à l'utilisateur quand les données sont privées.
- Les filtres se construisent avec `when`, avec des conditions groupées et `withQueryString`.
- Évite le N+1 avec `with()` et vérifie-le avec un test qui compte les requêtes.
- Un module est terminé quand il est testé, relu avec une grille et prêt à être déployé en sécurité.
