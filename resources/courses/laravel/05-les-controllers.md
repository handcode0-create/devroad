---
title: Les Controllers
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

La route décide **quelle** porte s'ouvre. Le contrôleur décide **ce qui se passe** derrière. C'est le chef d'orchestre de chaque requête : il reçoit la demande, fait appel aux bons outils, puis renvoie la réponse.

À la fin du chapitre, tu seras capable de :

- créer un contrôleur avec `artisan` et le relier à une route ;
- écrire les sept actions classiques d'un contrôleur de ressource ;
- recevoir des données grâce à l'injection de la requête et des modèles ;
- renvoyer une page, du JSON ou une redirection avec un message ;
- garder des contrôleurs **minces** en déplaçant la logique ailleurs ;
- reconnaître quand utiliser un contrôleur invocable.

On construit ici le contrôleur des roadmaps de DevRoad, étape par étape.

## Le rôle d'un contrôleur

Un contrôleur est une classe PHP rangée dans `app/Http/Controllers`. Chacune de ses méthodes répond à une route. Son travail se résume à cinq actions, toujours dans le même ordre :

1. **recevoir** la requête (ce que l'utilisateur envoie) ;
2. **valider** les données et **vérifier les droits** ;
3. **demander** le travail aux modèles ou aux services ;
4. **choisir** la réponse ;
5. **renvoyer** cette réponse.

Remarque ce qui n'est pas dans la liste : écrire du SQL, calculer des statistiques compliquées, dessiner du HTML. Le contrôleur **coordonne**, il ne fait pas tout lui-même. Retiens cette phrase, elle guidera chacune de tes décisions : *un contrôleur est mince*.

## Créer un contrôleur

Le plus simple est de laisser `artisan` écrire le squelette :

```bash
php artisan make:controller RoadmapController --resource
```

L'option `--resource` génère le fichier `app/Http/Controllers/RoadmapController.php` avec les sept méthodes habituelles, vides. Voici le squelette simplifié :

```php
<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    public function index() {}                          // lister
    public function create() {}                         // formulaire de création
    public function store(Request $request) {}          // enregistrer
    public function show(Roadmap $roadmap) {}           // afficher un élément
    public function edit(Roadmap $roadmap) {}           // formulaire de modification
    public function update(Request $request, Roadmap $roadmap) {} // modifier
    public function destroy(Roadmap $roadmap) {}        // supprimer
}
```

Ces méthodes correspondent exactement aux routes générées par `Route::resource('roadmaps', RoadmapController::class)` que tu as vues au chapitre précédent. Les noms des méthodes ne sont pas un hasard : c'est la convention qui relie routes et contrôleur.

:::quiz
Quelle méthode d'un contrôleur de ressource affiche le formulaire de création, sans rien enregistrer ?
- [ ] store
- [x] create
- [ ] show
- [ ] update
> `create` affiche le formulaire, `store` reçoit ensuite les données envoyées pour les enregistrer.
:::

## Les actions de lecture : index et show

Commençons par ce qui ne modifie rien. Dans DevRoad, l'interface est une page React rendue grâce à **Inertia**. Le contrôleur lui transmet des données sous forme de props.

```php
use Inertia\Inertia;

public function index(Request $request)
{
    $roadmaps = $request->user()
        ->roadmaps()
        ->latest('updated_at')
        ->paginate(12);

    return Inertia::render('Roadmaps/Index', [
        'roadmaps' => $roadmaps,
    ]);
}

public function show(Roadmap $roadmap)
{
    $this->authorize('view', $roadmap);

    return Inertia::render('Roadmaps/Show', [
        'roadmap' => $roadmap->load('steps'),
    ]);
}
```

Trois idées à noter :

- `$request->user()` renvoie l'utilisateur connecté. On liste **ses** roadmaps seulement, pas celles des autres.
- `paginate(12)` découpe les résultats en pages de douze éléments, au lieu de tout charger.
- `$this->authorize('view', $roadmap)` vérifie que l'utilisateur a le droit de voir cette roadmap. Sans cette ligne, n'importe qui pourrait ouvrir la roadmap d'un autre en changeant l'identifiant dans l'adresse. Tu approfondiras cela au chapitre sur les policies.

`Inertia::render('Roadmaps/Index', ...)` cherche le composant `resources/js/Pages/Roadmaps/Index.jsx` et lui passe les données. Tu n'écris aucune API : le contrôleur parle directement à la page.

## Les actions d'écriture : store, update, destroy

Ici, le contrôleur reçoit des données de l'extérieur. La règle d'or est de **ne jamais leur faire confiance** avant de les avoir validées.

```php
public function store(Request $request)
{
    $data = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'technology' => ['required', 'string'],
    ]);

    $roadmap = $request->user()->roadmaps()->create($data);

    return redirect()
        ->route('roadmaps.show', $roadmap)
        ->with('success', 'Roadmap créée.');
}

public function update(Request $request, Roadmap $roadmap)
{
    $this->authorize('update', $roadmap);

    $roadmap->update($request->validate([
        'title' => ['required', 'string', 'max:255'],
    ]));

    return back()->with('success', 'Roadmap mise à jour.');
}

public function destroy(Roadmap $roadmap)
{
    $this->authorize('delete', $roadmap);

    $roadmap->delete();

    return redirect()
        ->route('roadmaps.index')
        ->with('success', 'Roadmap supprimée.');
}
```

Observe le schéma commun : **valider, enregistrer, rediriger**. Après une écriture, on redirige toujours. Ainsi, si l'utilisateur actualise la page, son navigateur ne renvoie pas le formulaire une deuxième fois et ne crée pas un doublon.

Le message `with('success', ...)` est un **message flash** : il est conservé pour la requête suivante, le temps de s'afficher, puis disparaît. Tu verras comment l'afficher côté React au chapitre sur Inertia.

> **Attention** : dans `store`, on utilise `$data` (le résultat de `validate`) et jamais `$request->all()`. Avec `all()`, un utilisateur malveillant pourrait glisser des champs que tu n'as pas prévus. Tu verras la protection complète avec `$fillable` dans le chapitre sur Eloquent.

:::quiz
Pourquoi redirige-t-on après un `store` réussi plutôt que d'afficher directement une page ?
- [ ] Parce que PHP l'exige
- [x] Pour éviter qu'un rafraîchissement de la page renvoie le formulaire et crée un doublon
- [ ] Pour accélérer la base de données
- [ ] Pour économiser de la mémoire
> C'est le motif « Post, Redirect, Get » : après une écriture, on redirige vers une page en lecture, ce qui rend l'actualisation sans danger.
:::

## Répondre autrement : JSON et fichiers

Un contrôleur peut renvoyer bien d'autres choses qu'une page. Quelques exemples utiles :

```php
// Du JSON pour une petite requête asynchrone
return response()->json(['progress' => 42]);

// Un code de statut explicite
return response()->json(['message' => 'Introuvable'], 404);

// Un fichier à télécharger
return response()->download(storage_path('app/export.pdf'));

// Une erreur 403 volontaire
abort(403, 'Action non autorisée.');
```

Choisis la réponse qui correspond à l'intention : une page pour un humain, du JSON pour un script, une redirection après une écriture, une erreur claire quand l'action est interdite.

## Le contrôleur invocable

Parfois, un contrôleur n'a qu'**une seule** action. Inutile alors de le remplir de méthodes. Laravel propose le contrôleur **invocable**, avec la méthode spéciale `__invoke` :

```bash
php artisan make:controller DashboardController --invokable
```

```php
class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        return Inertia::render('Dashboard', [
            'stats' => [
                'roadmaps' => $request->user()->roadmaps()->count(),
            ],
        ]);
    }
}
```

Dans les routes, on référence alors la classe seule :

```php
Route::get('/dashboard', DashboardController::class)->name('dashboard');
```

C'est exactement ainsi que l'accueil de DevRoad est organisé : une seule page, un seul contrôleur, une seule méthode.

## Garder des contrôleurs minces

Un contrôleur de cent lignes qui calcule, formate, envoie des e-mails et écrit de la logique métier devient impossible à tester et à relire. Voici les trois endroits où déplacer la logique.

| Si le code... | Déplace-le vers... |
| --- | --- |
| vérifie les données d'un formulaire | une **Form Request** |
| décide des droits | une **Policy** |
| contient une règle métier réutilisable | une classe de **service** (`app/Services`) |
| concerne une seule table | une méthode du **modèle** |

DevRoad en donne un exemple concret : la génération des étapes d'une roadmap à partir d'un catalogue de cours est confiée à une classe `RoadmapGenerator`. Le contrôleur ne fait qu'appeler `$generator->create(...)`. Le jour où la façon de générer change, le contrôleur n'est pas modifié.

> **Astuce** : si une méthode de contrôleur dépasse une vingtaine de lignes, pose-toi la question : qu'est-ce qui pourrait vivre ailleurs ? Le plus souvent, la validation part dans une Form Request et la logique dans un service.

:::quiz
Où est-il préférable de placer une règle métier utilisée dans plusieurs contrôleurs ?
- [ ] Dans chacun des contrôleurs, en la copiant
- [ ] Dans routes/web.php
- [x] Dans une classe de service
- [ ] Dans le fichier .env
> Une classe de service centralise la règle : elle est réutilisable, testable séparément et allège les contrôleurs.
:::

## Atelier guidé : le contrôleur des fiches mémo

Compte une heure. Tu vas construire un contrôleur complet pour des « fiches » à partir de zéro, même si la base de données n'est pas prête : on retourne des tableaux pour s'entraîner.

1. Crée le contrôleur : `php artisan make:controller FicheController --resource`.
2. Déclare `Route::resource('fiches', FicheController::class);` dans `routes/web.php`, à l'intérieur d'un groupe protégé par `auth`.
3. Dans `index`, retourne un tableau de deux fiches factices (titre et contenu).
4. Dans `show`, retourne `['id' => $id]` en acceptant l'identifiant en paramètre.
5. Dans `store`, valide `title` (obligatoire, texte, 255 caractères maximum) et `content` (obligatoire), puis redirige vers `fiches.index` avec un message flash.
6. Lance `php artisan route:list --name=fiches` et compare avec la table du chapitre précédent.
7. Teste `store` en envoyant un formulaire vide : observe les erreurs de validation que Laravel renvoie.
8. Transforme `index` en contrôleur invocable séparé, `ListeFichesController`, et note ce que cela change dans les routes.

Pour t'auto-évaluer : explique pourquoi `store` doit rediriger plutôt qu'afficher une page, et pourquoi on utilise `validate` plutôt que `$request->all()`.

## Erreurs fréquentes

- **Mettre toute la logique dans une méthode.** Découpe en Request, Policy, service.
- **Oublier l'autorisation.** Une route protégée par `auth` empêche seulement les visiteurs non connectés : elle n'empêche pas un utilisateur connecté de toucher les données d'un autre.
- **Faire confiance à `$request->all()`.** Utilise toujours les données validées.
- **Ne pas rediriger après une écriture.** Chaque rafraîchissement crée un doublon.
- **Un nom de méthode qui ne correspond pas à la route.** Une faute de frappe donne l'erreur « Method does not exist ».

## Bonnes pratiques

- Un contrôleur par ressource, nommé au singulier avec le suffixe `Controller`.
- Des méthodes courtes, qui lisent comme une suite d'étapes.
- Valide toujours avant d'écrire, autorise toujours avant de modifier.
- Redirige après chaque écriture avec un message clair.
- Ne retourne que les données dont la page a besoin, jamais un modèle entier par réflexe.

## À retenir

- Le contrôleur reçoit la requête, valide, autorise, délègue le travail et renvoie une réponse.
- `make:controller --resource` génère les sept actions : `index`, `create`, `store`, `show`, `edit`, `update`, `destroy`.
- Laravel injecte la requête et les modèles dans les paramètres des méthodes.
- Après une écriture : valider, enregistrer, rediriger avec un message flash.
- Un contrôleur invocable convient à une action unique, comme le tableau de bord.
- Un contrôleur reste mince : la validation va en Form Request, les droits en Policy, la logique en service.
