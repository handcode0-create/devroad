---
title: Relations Eloquent
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Dans une vraie application, les données ne vivent pas seules : une roadmap appartient à un utilisateur, contient des étapes, une fiche porte des tags. Eloquent permet de **naviguer entre ces liens** comme entre des objets, sans écrire de jointure.

À la fin du chapitre, tu seras capable de :

- déclarer les relations un-à-plusieurs, un-à-un et plusieurs-à-plusieurs ;
- lire les données liées avec `$roadmap->steps` et `$step->roadmap` ;
- créer des enregistrements liés à travers la relation ;
- expliquer le **problème N+1** et le corriger avec `with()` ;
- compter et filtrer sur des relations avec `withCount` et `whereHas` ;
- gérer des tags avec `attach`, `detach` et `sync`.

Ce chapitre suppose que tu as les tables `users`, `roadmaps`, `roadmap_steps`, `memos`, `tags` et `memo_tag` du chapitre sur les migrations.

## Penser en relations

Dessine d'abord le schéma de DevRoad :

- un **utilisateur** possède plusieurs **roadmaps** ;
- une **roadmap** contient plusieurs **étapes**, et chaque étape appartient à une seule roadmap ;
- une **fiche** (mémo) peut avoir plusieurs **tags**, et un tag peut s'appliquer à plusieurs fiches.

Il y a trois formes de lien :

| Forme | Exemple | Où est la clé étrangère ? |
| --- | --- | --- |
| Un à plusieurs | Une roadmap a plusieurs étapes | Dans la table des « plusieurs » (`roadmap_steps.roadmap_id`) |
| Un à un | Un utilisateur a un profil | Dans la table « enfant » |
| Plusieurs à plusieurs | Fiches et tags | Dans une table pivot (`memo_tag`) |

Repère toujours le côté **« plusieurs »** : c'est lui qui porte la clé étrangère. Les relations Eloquent ne font que **décrire** ce que la base contient déjà.

## Un à plusieurs : hasMany et belongsTo

On déclare chaque côté de la relation dans son modèle, sous forme d'une méthode.

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roadmap extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('position');
    }
}

class RoadmapStep extends Model
{
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}
```

Le vocabulaire est simple :

- **`hasMany`** : « cette roadmap **possède** plusieurs étapes » ;
- **`belongsTo`** : « cette étape **appartient à** une roadmap ».

Grâce aux conventions, Eloquent devine la clé étrangère (`roadmap_id`) à partir du nom du modèle. Tu peux même ajouter un tri par défaut, comme `orderBy('position')` ci-dessus.

Du côté de `User`, déclare l'autre sens :

```php
public function roadmaps(): HasMany
{
    return $this->hasMany(Roadmap::class);
}
```

### Lire les données liées

Une relation se lit comme une **propriété** :

```php
$roadmap = Roadmap::find(1);

$roadmap->steps;                  // collection des étapes
$roadmap->steps->count();         // nombre d'étapes
$roadmap->steps->first()->title;  // titre de la première

$step = RoadmapStep::find(5);
$step->roadmap->title;            // titre de la roadmap parente
$step->roadmap->user->name;       // nom du propriétaire, en chaînant
```

Remarque la différence entre `$roadmap->steps` (sans parenthèses) et `$roadmap->steps()` (avec). Sans parenthèses, tu obtiens **les résultats** ; avec parenthèses, tu obtiens **la relation**, sur laquelle tu peux ajouter des conditions :

```php
$roadmap->steps()->where('status', 'completed')->count();
```

:::quiz
Quelle table porte la clé étrangère dans une relation « une roadmap a plusieurs étapes » ?
- [ ] roadmaps
- [x] roadmap_steps
- [ ] users
- [ ] Une table pivot
> La clé étrangère est toujours du côté « plusieurs » : roadmap_steps contient la colonne roadmap_id.
:::

## Créer des enregistrements liés

Quand tu crées une étape, tu veux qu'elle soit automatiquement rattachée à la bonne roadmap. Passe par la relation, c'est plus sûr que de remplir la clé étrangère à la main :

```php
$roadmap = Roadmap::find(1);

$step = $roadmap->steps()->create([
    'title' => 'Installer Laravel',
    'position' => 1,
]);
```

Eloquent renseigne `roadmap_id` tout seul. Même principe depuis l'utilisateur connecté, ce qui garantit qu'une roadmap créée lui appartient :

```php
$roadmap = $request->user()->roadmaps()->create($data);
```

C'est une bonne habitude de sécurité : le propriétaire n'est jamais lu dans le formulaire (que l'utilisateur pourrait falsifier), il vient de la session.

### Toucher le parent

Quand une étape change, la roadmap qui la contient a été modifiée elle aussi. Pour que son `updated_at` suive, déclare dans le modèle de l'étape :

```php
class RoadmapStep extends Model
{
    protected $touches = ['roadmap'];
}
```

Chaque fois qu'une étape est enregistrée, Eloquent met à jour la date de sa roadmap. DevRoad s'en sert pour trier les parcours « modifiés récemment » : terminer une étape fait remonter la roadmap en tête de liste.

## Le problème N+1

C'est **le** piège classique d'Eloquent. Regarde ce code, qui affiche chaque roadmap avec le nombre de ses étapes :

```php
$roadmaps = Roadmap::all();           // 1 requête

foreach ($roadmaps as $roadmap) {
    echo $roadmap->steps->count();    // 1 requête PAR roadmap
}
```

Avec 50 roadmaps, tu exécutes **51 requêtes** : une pour la liste, plus une pour chaque roadmap. C'est le problème **N+1** (N requêtes supplémentaires, plus la première). Sur une grosse page, cela rend l'application lente, sans qu'aucune erreur ne s'affiche.

### La solution : le chargement anticipé

La méthode `with()` charge les relations **en une seule requête supplémentaire** pour toutes les roadmaps :

```php
$roadmaps = Roadmap::with('steps')->get();   // 2 requêtes au total

foreach ($roadmaps as $roadmap) {
    echo $roadmap->steps->count();            // aucune requête de plus
}
```

Quel que soit le nombre de roadmaps, tu restes à **2 requêtes**. On appelle cela l'*eager loading*.

Tu peux charger plusieurs relations, ou des relations imbriquées :

```php
Roadmap::with(['steps', 'user'])->get();
Roadmap::with('steps.devLabProject')->get();   // relation de la relation
```

### Limiter ce qui est chargé

Si tu n'as besoin que d'une partie des étapes, filtre dans le chargement :

```php
Roadmap::with(['steps' => fn ($query) => $query
    ->where('status', '!=', 'completed')
    ->orderBy('position'),
])->get();
```

> **Astuce** : pour repérer un N+1, installe Laravel Debugbar en développement : il affiche le nombre de requêtes de chaque page. Ou, plus simple, active dans `AppServiceProvider` la ligne `Model::preventLazyLoading(! app()->isProduction());` : Laravel lèvera une erreur dès qu'une relation est chargée paresseusement.

:::quiz
Tu affiches 100 roadmaps et le nombre d'étapes de chacune. Combien de requêtes exécute `Roadmap::all()` suivi de `$roadmap->steps->count()` dans une boucle ?
- [ ] 1
- [ ] 2
- [x] 101
- [ ] 100
> Une requête charge les roadmaps, puis chacune des 100 déclenche sa propre requête pour ses étapes : c'est le problème N+1.
:::

## Compter et filtrer sur une relation

Souvent, tu n'as pas besoin des étapes elles-mêmes, seulement de leur nombre. Charger toutes les lignes juste pour les compter serait du gaspillage. Utilise `withCount` :

```php
$roadmaps = Roadmap::withCount('steps')->get();

echo $roadmaps->first()->steps_count;
```

Eloquent ajoute une propriété `steps_count`, calculée directement par la base, en une seule requête. Tu peux compter selon une condition :

```php
Roadmap::withCount([
    'steps',
    'steps as completed_steps_count' => fn ($query) => $query->where('status', 'completed'),
])->get();
```

Avec ces deux compteurs, calculer le pourcentage d'avancement d'une roadmap est immédiat : `completed_steps_count / steps_count`. C'est exactement ce que fait DevRoad pour afficher la progression.

### Filtrer les parents selon leurs enfants

`whereHas` retourne les parents qui ont au moins un enfant respectant une condition :

```php
// Les roadmaps qui ont au moins une étape non terminée
Roadmap::whereHas('steps', fn ($q) => $q->where('status', '!=', 'completed'))->get();

// Les roadmaps qui n'ont aucune étape
Roadmap::doesntHave('steps')->get();
```

DevRoad utilise `whereHas` pour retrouver la roadmap à « reprendre » : celle qui contient encore des étapes à faire.

## Plusieurs à plusieurs : belongsToMany

Une fiche peut avoir plusieurs tags, un tag plusieurs fiches. On déclare la relation des deux côtés avec `belongsToMany`, qui s'appuie sur la table pivot `memo_tag` :

```php
class Memo extends Model
{
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }
}

class Tag extends Model
{
    public function memos(): BelongsToMany
    {
        return $this->belongsToMany(Memo::class);
    }
}
```

Eloquent déduit le nom de la table pivot (`memo_tag`) grâce à l'ordre alphabétique. Pour manipuler les liens, tu disposes de trois méthodes :

```php
$memo = Memo::find(1);

$memo->tags()->attach($tagId);                  // ajoute un lien
$memo->tags()->detach($tagId);                  // retire un lien
$memo->tags()->sync([1, 4, 7]);                 // remplace TOUS les liens par cette liste
```

`sync` est la plus pratique pour un formulaire : tu lui donnes la liste finale des tags cochés, et Eloquent ajoute ceux qui manquent et retire ceux qui ne sont plus voulus.

```php
$memo->tags()->sync($request->input('tag_ids', []));
```

Lire les tags d'une fiche se fait comme n'importe quelle relation : `$memo->tags` (et `Memo::with('tags')` pour éviter le N+1).

> **Attention** : `sync()` supprime les liens qui ne figurent pas dans la liste. Si tu veux seulement ajouter sans rien retirer, utilise `syncWithoutDetaching()`.

:::quiz
Quelle méthode remplace l'ensemble des tags d'une fiche par une nouvelle liste ?
- [ ] attach()
- [ ] detach()
- [x] sync()
- [ ] create()
> sync() compare la liste donnée aux liens existants : elle ajoute les nouveaux et supprime les absents.
:::

## Atelier guidé : relier DevRoad

Compte une heure et demie. Reprends les modèles `User`, `Roadmap`, `RoadmapStep`, `Memo`, `Tag`.

1. Déclare `user()` et `steps()` dans `Roadmap`, `roadmap()` dans `RoadmapStep`, `roadmaps()` dans `User`.
2. Dans `tinker`, crée un utilisateur de test, puis une roadmap via `$user->roadmaps()->create([...])`.
3. Ajoute trois étapes avec `$roadmap->steps()->create([...])`, dont une avec `status` à `completed`.
4. Affiche le titre de la roadmap d'une étape, puis le nom de l'utilisateur propriétaire.
5. Compte les étapes terminées avec `$roadmap->steps()->where('status', 'completed')->count()`.
6. Écris `Roadmap::withCount('steps')->get()` et affiche `steps_count`.
7. Déclare `tags()` des deux côtés avec `belongsToMany`. Crée trois tags, associe-en deux à une fiche avec `attach`, puis remplace par un seul avec `sync`.
8. Active `Model::preventLazyLoading()` dans `AppServiceProvider::boot()`, charge des roadmaps sans `with` et lis leurs étapes : observe l'erreur, puis corrige avec `with('steps')`.

Pour t'auto-évaluer : explique à voix haute pourquoi `with()` réduit le nombre de requêtes, et pourquoi on crée une étape via `$roadmap->steps()->create()`.

## Erreurs fréquentes

- **Oublier `with()` dans une boucle.** C'est le N+1, invisible tant qu'il y a peu de données.
- **Confondre `$roadmap->steps` et `$roadmap->steps()`.** Le premier renvoie les résultats, le second la relation.
- **Écrire `sync()` à la place de `attach()`.** Tu supprimes alors des liens sans le vouloir.
- **Mauvais sens de la relation.** `hasMany` va du parent vers les enfants, `belongsTo` des enfants vers le parent.
- **Écrire la clé étrangère à la main.** Passe plutôt par la relation (`$roadmap->steps()->create`).
- **Charger toute une relation juste pour la compter.** Utilise `withCount`.

## Bonnes pratiques

- Déclare chaque relation des deux côtés, avec les types de retour (`HasMany`, `BelongsTo`).
- Utilise `with()` dès que tu lis une relation pour plusieurs lignes.
- Préfère `withCount` à un comptage en mémoire.
- Crée les enregistrements liés à travers leur relation.
- Ajoute `$touches` quand la modification d'un enfant doit remonter au parent.
- Surveille le nombre de requêtes de tes pages principales.

## À retenir

- Une relation décrit un lien déjà présent dans la base : la clé étrangère est du côté « plusieurs ».
- `hasMany` et `belongsTo` couvrent le un-à-plusieurs, `belongsToMany` le plusieurs-à-plusieurs avec une table pivot.
- `$roadmap->steps` renvoie les résultats, `$roadmap->steps()` la relation interrogeable.
- Le problème N+1 se règle avec `with()` ; `withCount` et `whereHas` évitent de tout charger.
- `attach`, `detach` et `sync` gèrent les liens plusieurs-à-plusieurs ; `sync` remplace la liste.
- Crée les enregistrements liés à travers la relation : la clé étrangère est renseignée pour toi.
