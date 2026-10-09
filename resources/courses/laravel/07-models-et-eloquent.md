---
title: Models et Eloquent
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Tu sais décrire une table avec une migration. Reste à lire et écrire dedans depuis ton code PHP, sans rédiger une seule requête SQL à la main. C'est le rôle d'**Eloquent**, l'ORM de Laravel.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un ORM et pourquoi il simplifie le travail ;
- créer un modèle et le relier à sa table ;
- créer, lire, modifier et supprimer des enregistrements ;
- construire des requêtes avec filtres, tri, limites et pagination ;
- protéger tes données avec `$fillable` et transformer les types avec `$casts` ;
- écrire un scope réutilisable et manipuler des collections ;
- tester tout cela dans `tinker`, sans créer de page.

Ce chapitre est dense : prends ton temps. Chaque exemple se teste dans `php artisan tinker`.

## Qu'est-ce qu'un ORM ?

Une base de données comprend le SQL. Ton application, elle, manipule des objets PHP. Un **ORM** (*Object-Relational Mapping*) fait le traducteur : chaque **table** devient une **classe**, chaque **ligne** devient un **objet**, chaque **colonne** devient une **propriété**.

Sans ORM, pour retrouver une roadmap :

```php
$resultat = DB::select('SELECT * FROM roadmaps WHERE id = ?', [3]);
```

Avec Eloquent :

```php
$roadmap = Roadmap::find(3);
echo $roadmap->title;
```

Le gain n'est pas seulement la brièveté. Eloquent protège contre les **injections SQL** (il prépare les requêtes de façon sûre), rend le code lisible et te laisse penser en objets plutôt qu'en tables.

## Créer un modèle

Un modèle est une classe qui représente une table. Génère-le, avec sa migration, en une commande :

```bash
php artisan make:model Roadmap -m
```

L'option `-m` crée aussi la migration. Le fichier `app/Models/Roadmap.php` contient :

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Roadmap extends Model
{
    protected $fillable = [
        'title',
        'description',
        'technology',
        'status',
    ];

    protected $casts = [
        'is_public' => 'boolean',
        'settings' => 'array',
    ];
}
```

Tu n'as écrit aucun nom de table : Eloquent déduit que `Roadmap` correspond à la table `roadmaps`. C'est la convention de nommage du chapitre 3 qui travaille pour toi.

### Les trois propriétés à connaître

- **`$fillable`** liste les colonnes que l'on a le droit de remplir **en masse** (avec `create` ou `update`). C'est une protection : si un utilisateur envoie un champ `is_admin` dans un formulaire, il sera ignoré car absent de la liste.
- **`$casts`** convertit automatiquement les types : une colonne `boolean` devient vraiment `true` ou `false` en PHP, une colonne `json` devient un tableau, une date devient un objet `Carbon`.
- **`$hidden`** masque des colonnes quand le modèle est converti en JSON, par exemple le mot de passe d'un utilisateur.

> **Attention** : sans `$fillable` (ou son contraire `$guarded`), Eloquent refuse l'écriture en masse et lève une erreur « Add [title] to fillable property ». C'est volontaire : il te protège de l'**affectation de masse**, une faille classique.

:::quiz
À quoi sert la propriété $fillable d'un modèle ?
- [ ] À définir le nom de la table
- [x] À lister les colonnes que l'on peut remplir en masse avec create() ou update()
- [ ] À cacher des colonnes dans le JSON
- [ ] À créer les colonnes dans la base
> $fillable est une liste blanche : seuls les champs listés sont acceptés lors d'une écriture en masse, ce qui empêche d'injecter des champs inattendus.
:::

## Lire des données

Ouvre `php artisan tinker`. C'est une console où tu peux taper du PHP dans le contexte de ton application. Essaie ces lectures.

```php
// Toutes les lignes (attention sur une grosse table)
Roadmap::all();

// Une ligne par identifiant (ou null si elle n'existe pas)
Roadmap::find(1);

// Idem, mais lève une erreur 404 si elle n'existe pas
Roadmap::findOrFail(1);

// La première ligne qui correspond à un critère
Roadmap::where('technology', 'laravel')->first();
```

### Le constructeur de requêtes

Eloquent permet d'**enchaîner** des conditions, comme des phrases. La requête n'est exécutée qu'à la fin, avec `get()`, `first()`, `count()` ou `paginate()`.

```php
$roadmaps = Roadmap::query()
    ->where('status', 'active')
    ->where('technology', 'laravel')
    ->orderBy('title')
    ->limit(10)
    ->get();
```

Quelques méthodes à retenir :

| Méthode | Effet |
| --- | --- |
| `where('col', 'valeur')` | Filtre sur une colonne |
| `whereIn('col', [..])` | Filtre sur une liste de valeurs |
| `whereNull('col')` | Colonne vide |
| `orderBy('col')` / `latest()` | Tri croissant / plus récent d'abord |
| `limit(n)` | Au plus n résultats |
| `count()` | Nombre de lignes |
| `exists()` | Vrai si au moins une ligne |
| `paginate(12)` | Résultats par pages de 12 |
| `pluck('col')` | Liste d'une seule colonne |

Pour une recherche partielle, utilise `like` :

```php
Roadmap::where('title', 'like', '%laravel%')->get();
```

### Lire les valeurs

Une ligne est un objet : tu lis ses colonnes comme des propriétés.

```php
$roadmap = Roadmap::findOrFail(1);

echo $roadmap->title;
echo $roadmap->created_at->format('d/m/Y');   // une date Carbon, formatable
echo $roadmap->created_at->diffForHumans();   // « il y a 3 jours »
```

:::quiz
Quelle méthode retourne le premier résultat ou lève une erreur 404 s'il n'existe pas ?
- [ ] find()
- [ ] first()
- [x] findOrFail()
- [ ] all()
> findOrFail() renvoie l'enregistrement, ou déclenche automatiquement une erreur 404 : idéal quand la ressource doit exister.
:::

## Créer, modifier, supprimer

### Créer

```php
$roadmap = Roadmap::create([
    'title' => 'Apprendre Laravel',
    'technology' => 'laravel',
]);
```

`create()` enregistre et retourne l'objet. Les colonnes `id`, `created_at` et `updated_at` sont remplies automatiquement. Seuls les champs présents dans `$fillable` sont acceptés.

Tu peux aussi construire l'objet puis l'enregistrer :

```php
$roadmap = new Roadmap();
$roadmap->title = 'Apprendre React';
$roadmap->save();
```

### Modifier

```php
$roadmap = Roadmap::findOrFail(1);
$roadmap->update(['title' => 'Maîtriser Laravel']);
```

Ou, propriété par propriété :

```php
$roadmap->status = 'archived';
$roadmap->save();
```

Eloquent met à jour `updated_at` tout seul, et n'envoie à la base que les colonnes réellement modifiées.

### Supprimer

```php
$roadmap = Roadmap::findOrFail(1);
$roadmap->delete();

// Ou directement, sans charger l'objet
Roadmap::destroy(1);
```

> **Attention** : `Roadmap::where('status', 'draft')->delete()` supprime **toutes** les lignes qui correspondent. Vérifie toujours ta condition avec un `count()` avant de lancer une suppression de masse.

## Convertir les types avec $casts

Une base stocke tout en texte ou en nombres. Les `$casts` rendent les valeurs plus pratiques à manipuler :

```php
protected $casts = [
    'is_favorite' => 'boolean',
    'estimated_minutes' => 'integer',
    'workspace_files' => 'array',
    'exercise_completed_at' => 'datetime',
];
```

Après ce réglage, `$step->is_favorite` est un vrai booléen, `$step->workspace_files` un tableau PHP (stocké en JSON dans la base), et `$step->exercise_completed_at` un objet date. Sans cast, tu manipulerais `1`, `"0"` et des chaînes JSON, avec des bugs subtils à la clé.

## Les scopes : des filtres réutilisables

Tu vas écrire souvent la même condition, par exemple « les roadmaps actives ». Plutôt que de la copier partout, définis un **scope** dans le modèle :

```php
use Illuminate\Database\Eloquent\Builder;

class Roadmap extends Model
{
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    public function scopeForTechnology(Builder $query, string $technology): Builder
    {
        return $query->where('technology', $technology);
    }
}
```

On les utilise ensuite sans le préfixe `scope`, et on les combine :

```php
Roadmap::active()->forTechnology('laravel')->latest()->get();
```

Le code se lit comme une phrase, et si la définition d'« active » change un jour, tu ne modifies qu'un seul endroit.

:::quiz
Quel est l'intérêt principal d'un scope ?
- [ ] Il accélère la base de données
- [ ] Il remplace les migrations
- [x] Il centralise une condition réutilisable et rend les requêtes lisibles
- [ ] Il supprime les doublons
> Un scope donne un nom à une condition fréquente : on la réutilise partout et on ne la modifie qu'à un seul endroit.
:::

## Manipuler des collections

Quand tu appelles `get()` ou `all()`, tu ne reçois pas un simple tableau, mais une **collection** : un objet riche, avec des méthodes pratiques.

```php
$roadmaps = Roadmap::all();

$titres = $roadmaps->pluck('title');                    // liste des titres
$actives = $roadmaps->where('status', 'active');         // filtre en mémoire
$tries = $roadmaps->sortBy('title');                      // tri en mémoire
$total = $roadmaps->count();                              // nombre d'éléments

$resume = $roadmaps->map(fn ($r) => [
    'id' => $r->id,
    'title' => $r->title,
]);
```

Attention à une distinction importante : `Roadmap::where('status', 'active')->get()` filtre **dans la base** (efficace), alors que `Roadmap::all()->where('status', 'active')` charge **toutes** les lignes en mémoire puis filtre (coûteux). Filtre toujours dans la requête quand c'est possible.

> **Astuce** : `->toArray()` ou `->toJson()` convertissent un modèle ou une collection pour l'envoyer à une page. Pense à `$hidden` pour ne jamais exposer un champ sensible.

## Atelier guidé : explorer Eloquent avec tinker

Compte une heure et demie. Utilise la table `roadmaps` du chapitre précédent.

1. Crée le modèle : `php artisan make:model Roadmap`. Ajoute `$fillable` avec `user_id`, `title`, `description`, `technology`, `status`.
2. Lance `php artisan tinker`. Crée trois roadmaps avec `Roadmap::create([...])`, avec des technologies différentes.
3. Affiche-les toutes, puis retrouve-en une par son identifiant avec `find`.
4. Modifie le titre de l'une d'elles avec `update`, puis vérifie `updated_at`.
5. Écris une requête qui retourne uniquement les roadmaps Laravel, triées par titre.
6. Ajoute le scope `scopeActive` dans le modèle, relance tinker (`exit` puis `php artisan tinker`) et teste `Roadmap::active()->count()`.
7. Essaie d'enregistrer `Roadmap::create(['title' => 'X', 'is_admin' => true])` : observe ce qui se passe avec un champ absent de `$fillable`.
8. Convertis la collection en tableau de titres avec `pluck('title')`, puis supprime une roadmap avec `delete()` et vérifie avec `count()`.

Pour t'auto-évaluer : explique pourquoi `Roadmap::all()->where(...)` est moins efficace que `Roadmap::where(...)->get()`.

## Erreurs fréquentes

- **Oublier `$fillable`.** L'erreur « Add [champ] to fillable property » apparaît dès le premier `create`.
- **Utiliser `all()` sur une grosse table.** Charge toute la table en mémoire : préfère `paginate` ou une requête filtrée.
- **Confondre `find` et `findOrFail`.** `find` renvoie `null`, et ton code plante plus loin avec « Attempt to read property on null ».
- **Supprimer sans condition.** Un `delete()` sur une requête trop large efface plus que prévu.
- **Écrire des champs sensibles dans `$fillable`.** Ne mets jamais `is_admin` ou `role` dans cette liste si l'utilisateur peut atteindre ce champ.

## Bonnes pratiques

- Un modèle par table, nommé au singulier.
- Déclare `$fillable` et `$casts` pour chaque modèle.
- Transforme les conditions répétées en scopes.
- Filtre dans la base, pas dans la collection.
- Pagine les listes plutôt que de tout charger.
- Teste tes requêtes dans `tinker` avant de les mettre dans un contrôleur.

## À retenir

- Eloquent traduit tables, lignes et colonnes en classes, objets et propriétés.
- `$fillable` protège de l'affectation de masse, `$casts` convertit les types, `$hidden` masque des champs.
- `create`, `find`, `update`, `delete` couvrent le cycle de vie d'un enregistrement.
- Les requêtes s'enchaînent (`where`, `orderBy`, `limit`) et s'exécutent avec `get`, `first`, `count` ou `paginate`.
- Un scope donne un nom à une condition réutilisable.
- Filtre dans la requête, pas sur la collection chargée en mémoire.
