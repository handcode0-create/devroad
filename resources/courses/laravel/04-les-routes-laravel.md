---
title: Les routes Laravel
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Les routes sont le **plan de circulation** de ton application. Chaque adresse que l'utilisateur peut visiter est déclarée ici. Bien les maîtriser rend tout le reste plus simple, parce que c'est par elles que tout commence.

À la fin du chapitre, tu seras capable de :

- choisir la bonne méthode HTTP pour chaque action ;
- déclarer des routes avec paramètres, contraintes et noms ;
- regrouper des routes avec un préfixe, un middleware ou un nom commun ;
- générer les sept routes d'une ressource d'un seul coup ;
- utiliser la liaison implicite pour que Laravel retrouve un enregistrement à ta place ;
- inspecter et déboguer tes routes avec `php artisan route:list`.

Les routes web se déclarent dans `routes/web.php`. Dans tout ce chapitre, on construit les routes de la fonctionnalité « roadmaps » de DevRoad.

## Les méthodes HTTP : choisir l'intention

Une route associe une **méthode HTTP** et une **adresse** à du code. La méthode exprime l'intention de la requête, et tu dois la choisir avec soin.

| Méthode | Intention | Exemple |
| --- | --- | --- |
| `GET` | Lire, sans rien modifier | Afficher la liste des roadmaps |
| `POST` | Créer | Enregistrer une nouvelle roadmap |
| `PUT` ou `PATCH` | Modifier (`PUT` remplace tout, `PATCH` change une partie) | Mettre à jour le titre |
| `DELETE` | Supprimer | Supprimer une roadmap |

Voici les déclarations correspondantes :

```php
<?php

use App\Http\Controllers\RoadmapController;
use Illuminate\Support\Facades\Route;

Route::get('/roadmaps', [RoadmapController::class, 'index']);
Route::post('/roadmaps', [RoadmapController::class, 'store']);
Route::patch('/roadmaps/{roadmap}', [RoadmapController::class, 'update']);
Route::delete('/roadmaps/{roadmap}', [RoadmapController::class, 'destroy']);
```

Remarque que `GET /roadmaps` et `POST /roadmaps` ont la même adresse mais des intentions différentes. L'adresse désigne la **ressource** (les roadmaps), la méthode précise l'**action**.

> **Attention** : n'utilise jamais `GET` pour modifier des données. Une requête `GET` peut être déclenchée par un simple lien, un robot d'indexation ou un préchargement du navigateur, sans que l'utilisateur ne l'ait voulu.

:::quiz
Quelle méthode HTTP convient pour supprimer une roadmap ?
- [ ] GET
- [ ] POST
- [x] DELETE
- [ ] PUT
> DELETE exprime l'intention de supprimer. GET doit rester sans effet de bord.
:::

## Les paramètres de route

Une partie variable de l'adresse se déclare entre accolades. Laravel la transmet à ta fonction ou à ta méthode de contrôleur.

```php
Route::get('/roadmaps/{id}', function (string $id) {
    return "Roadmap numéro {$id}";
});
```

### Paramètre optionnel

Ajoute un point d'interrogation pour rendre le paramètre facultatif, et donne alors une valeur par défaut :

```php
Route::get('/recherche/{terme?}', function (?string $terme = null) {
    return $terme ? "Résultats pour {$terme}" : 'Tape un mot à chercher';
});
```

### Contraintes

Par défaut, `{id}` accepte n'importe quoi, y compris du texte. Tu peux restreindre ce qui est accepté :

```php
Route::get('/roadmaps/{id}', function (string $id) {
    // ...
})->whereNumber('id');

Route::get('/technologies/{nom}', function (string $nom) {
    // ...
})->whereAlpha('nom');

Route::get('/etapes/{code}', function (string $code) {
    // ...
})->where('code', '[A-Z]{3}[0-9]{2}');
```

Si l'adresse ne respecte pas la contrainte, Laravel répond 404 sans exécuter ton code. C'est une première barrière contre les valeurs absurdes.

## Nommer ses routes

Écrire des adresses en dur partout dans le code est fragile : si tu changes `/roadmaps` en `/parcours`, tu dois retrouver chaque occurrence. La solution est de **nommer** les routes.

```php
Route::get('/roadmaps', [RoadmapController::class, 'index'])->name('roadmaps.index');
Route::get('/roadmaps/{roadmap}', [RoadmapController::class, 'show'])->name('roadmaps.show');
```

Tu génères ensuite l'adresse à partir du nom :

```php
// Dans un contrôleur
return redirect()->route('roadmaps.show', $roadmap);

// Dans du code PHP quelconque
$adresse = route('roadmaps.index'); // https://monsite.test/roadmaps
```

Si l'adresse change un jour, tu modifies une seule ligne. La convention est `ressource.action` : `roadmaps.index`, `roadmaps.create`, `roadmaps.store`, et ainsi de suite.

## Regrouper des routes

Quand plusieurs routes partagent un point commun (même préfixe, même middleware), un **groupe** évite de se répéter.

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::prefix('roadmaps')->name('roadmaps.')->group(function () {
        Route::get('/', [RoadmapController::class, 'index'])->name('index');
        Route::get('/create', [RoadmapController::class, 'create'])->name('create');
        Route::post('/', [RoadmapController::class, 'store'])->name('store');
    });
});
```

Trois outils se combinent ici :

- `middleware(['auth', 'verified'])` : toutes les routes du groupe exigent un utilisateur connecté et vérifié ;
- `prefix('roadmaps')` : toutes les adresses commencent par `/roadmaps` ;
- `name('roadmaps.')` : tous les noms commencent par `roadmaps.`.

Le middleware `auth` renvoie automatiquement les visiteurs non connectés vers la page de connexion. C'est la protection la plus courante de l'application.

:::quiz
Que produit `Route::prefix('admin')->name('admin.')->group(...)` pour une route `Route::get('/users', ...)->name('users')` ?
- [ ] L'adresse /users et le nom users
- [x] L'adresse /admin/users et le nom admin.users
- [ ] L'adresse /admin et le nom admin
- [ ] Une erreur, car on ne peut pas combiner prefix et name
> Le préfixe s'ajoute devant l'adresse et le nom de groupe devant le nom de chaque route.
:::

## Les routes de ressource

Presque toutes les fonctionnalités ont besoin des mêmes actions : lister, afficher un formulaire de création, créer, afficher un élément, afficher le formulaire de modification, modifier, supprimer. Laravel les génère toutes avec une seule ligne :

```php
Route::resource('roadmaps', RoadmapController::class);
```

Voici les sept routes obtenues :

| Méthode | Adresse | Action | Nom |
| --- | --- | --- | --- |
| `GET` | `/roadmaps` | `index` | `roadmaps.index` |
| `GET` | `/roadmaps/create` | `create` | `roadmaps.create` |
| `POST` | `/roadmaps` | `store` | `roadmaps.store` |
| `GET` | `/roadmaps/{roadmap}` | `show` | `roadmaps.show` |
| `GET` | `/roadmaps/{roadmap}/edit` | `edit` | `roadmaps.edit` |
| `PUT` ou `PATCH` | `/roadmaps/{roadmap}` | `update` | `roadmaps.update` |
| `DELETE` | `/roadmaps/{roadmap}` | `destroy` | `roadmaps.destroy` |

Si tu n'as pas besoin de tout, limite la liste :

```php
Route::resource('roadmaps', RoadmapController::class)->only(['index', 'show']);
Route::resource('tags', TagController::class)->except(['show']);
```

> **Astuce** : lance `php artisan route:list --name=roadmaps` pour n'afficher que les routes dont le nom contient « roadmaps ». Indispensable quand le projet grossit.

## La liaison implicite de modèle

Regarde la route `GET /roadmaps/{roadmap}`. Le paramètre s'appelle `roadmap`, comme le modèle. Si ta méthode de contrôleur déclare un paramètre du même nom **avec le type du modèle**, Laravel retrouve l'enregistrement tout seul :

```php
public function show(Roadmap $roadmap)
{
    // $roadmap est déjà chargée depuis la base, grâce à l'id de l'adresse.
    return Inertia::render('Roadmaps/Show', ['roadmap' => $roadmap]);
}
```

Sans cette fonctionnalité, tu écrirais `Roadmap::findOrFail($id)` dans chaque méthode. Ici, si l'identifiant n'existe pas, Laravel répond automatiquement par une erreur 404.

Attention : la liaison implicite retrouve n'importe quelle roadmap, même celle d'un autre utilisateur. Ce n'est pas un contrôle d'accès. Tu verras au chapitre sur les policies comment vérifier que l'enregistrement appartient bien à l'utilisateur connecté.

## Quelques outils supplémentaires

- **Redirection simple** : `Route::redirect('/accueil', '/dashboard');`.
- **Page statique** : `Route::inertia('/a-propos', 'About');` ou, avec Blade, `Route::view('/a-propos', 'about');`.
- **Route de secours** : `Route::fallback(...)` s'exécute quand aucune route ne correspond. Utile pour une page 404 personnalisée.
- **Cache des routes** : en production, `php artisan route:cache` accélère le démarrage. Il refuse les routes écrites avec une fonction anonyme : utilise des contrôleurs dans les projets sérieux.

### Et la sécurité des formulaires ?

Pour les routes `POST`, `PUT`, `PATCH` et `DELETE`, Laravel vérifie un **jeton CSRF** qui prouve que la demande vient bien de ton site. Avec Inertia et React, ce jeton est géré automatiquement : tu n'as rien à écrire. Si tu appelles ces routes depuis un outil externe, tu obtiendras une erreur 419 : c'est le jeton qui manque.

:::quiz
Une requête POST envoyée depuis un outil externe répond « 419 ». Quelle est la cause la plus probable ?
- [ ] La route n'existe pas
- [ ] L'utilisateur n'est pas connecté
- [x] Le jeton CSRF est absent ou invalide
- [ ] La base de données est arrêtée
> Le code 419 signifie que la page a expiré ou que le jeton CSRF n'a pas été fourni avec la requête.
:::

## Atelier guidé : les routes de DevRoad

Compte une heure. Tu n'as pas besoin de contrôleurs complets : des fonctions simples suffisent pour tester.

1. Dans `routes/web.php`, ajoute un groupe protégé par le middleware `auth`.
2. Dans ce groupe, déclare `GET /parcours` nommée `parcours.index`, qui retourne un tableau de trois technologies.
3. Ajoute `GET /parcours/{id}` nommée `parcours.show` avec la contrainte `whereNumber('id')`.
4. Ajoute `GET /recherche/{terme?}` avec une valeur par défaut.
5. Lance `php artisan route:list` et vérifie les méthodes, les adresses et les noms.
6. Visite `/parcours/abc` : tu dois obtenir une erreur 404, grâce à la contrainte.
7. Remplace tes routes `parcours` par `Route::resource('parcours', ParcoursController::class)->only(['index', 'show']);` après avoir créé le contrôleur avec `php artisan make:controller ParcoursController --resource`.
8. Compare le résultat de `route:list` avant et après : tu dois reconnaître les mêmes adresses.

Pour t'auto-évaluer : peux-tu expliquer pourquoi une route nommée est plus robuste qu'une adresse écrite en dur ?

## Erreurs fréquentes

- **Une route qui masque une autre.** `/roadmaps/{roadmap}` déclarée avant `/roadmaps/create` capture « create » comme un identifiant. Déclare toujours les adresses fixes avant celles avec paramètre.
- **Oublier de nommer les routes.** Tu te retrouves à écrire des adresses en dur partout.
- **Utiliser `GET` pour une action qui modifie des données.**
- **Confondre `{roadmap}` et `{id}`.** La liaison implicite n'agit que si le nom du paramètre correspond à celui de la méthode du contrôleur.
- **Croire que la liaison implicite protège.** Elle retrouve l'enregistrement, mais ne vérifie pas à qui il appartient.

## Bonnes pratiques

- Une route décrit une ressource et une action ; garde les adresses lisibles et au pluriel (`/roadmaps`).
- Nomme chaque route, avec la convention `ressource.action`.
- Utilise `Route::resource` quand ta fonctionnalité suit le schéma classique.
- Regroupe les routes protégées derrière `auth` pour ne pas en oublier une.
- Relis `php artisan route:list` avant chaque mise en ligne : c'est le meilleur audit rapide de ce qui est exposé.

## À retenir

- Une route associe une méthode HTTP et une adresse à du code ; la méthode exprime l'intention.
- Les paramètres se déclarent entre accolades, avec des contraintes (`whereNumber`, `where`) pour filtrer les valeurs.
- Nommer les routes évite les adresses écrites en dur ; `route('nom', $modele)` génère l'adresse.
- Les groupes factorisent préfixe, middleware et nom.
- `Route::resource` génère les sept routes classiques d'une ressource.
- La liaison implicite charge le modèle, mais ne remplace jamais un contrôle d'autorisation.
