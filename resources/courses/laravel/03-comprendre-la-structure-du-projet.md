---
title: Comprendre la structure du projet
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Un projet Laravel contient des dizaines de dossiers et des centaines de fichiers. Le secret n'est pas de tout connaître, mais de savoir **où chercher** quand tu veux faire quelque chose. Ce chapitre te donne la carte.

À la fin du chapitre, tu seras capable de :

- dire dans quel dossier se range chaque type de fichier (modèle, contrôleur, règle de validation, migration, page React) ;
- appliquer les conventions de nommage de Laravel, qui évitent beaucoup de configuration ;
- expliquer le rôle du fichier `bootstrap/app.php` ;
- comprendre ce qu'est l'injection de dépendances, sans jargon ;
- suivre une fonctionnalité de bout en bout à travers les fichiers qui la composent.

Dans le chapitre précédent, tu as installé le projet. Ouvre-le dans ton éditeur, tu vas t'y promener.

## Un dossier, une responsabilité

Laravel range le code par **responsabilité**. Chaque sorte de fichier a un emplacement prévisible, donc tu n'as jamais à deviner.

### Le dossier app/

C'est là que vit ton code métier. Ses sous-dossiers les plus importants :

| Dossier | Contenu | Exemple dans DevRoad |
| --- | --- | --- |
| `app/Models` | Une classe par table de la base de données | `Roadmap.php`, `Memo.php` |
| `app/Http/Controllers` | Les contrôleurs, qui reçoivent les requêtes | `RoadmapController.php` |
| `app/Http/Requests` | Les règles de validation des formulaires | `StoreRoadmapRequest.php` |
| `app/Http/Middleware` | Les filtres exécutés avant et après les contrôleurs | `HandleInertiaRequests.php` |
| `app/Policies` | Les règles d'autorisation (qui a le droit de faire quoi) | `RoadmapPolicy.php` |
| `app/Services` | Les classes de logique métier que tu crées toi-même | `RoadmapGenerator.php` |
| `app/Providers` | Le branchement des services au démarrage | `AppServiceProvider.php` |

Tous ces dossiers ne sont pas créés d'emblée : Laravel les génère quand tu en as besoin avec les commandes `make:`. Par exemple, `php artisan make:request StoreRoadmapRequest` crée le dossier `app/Http/Requests` s'il n'existe pas.

### Les autres dossiers

- `routes/` contient `web.php` (pages), `console.php` (commandes planifiées) et `auth.php` (routes d'authentification ajoutées par Breeze).
- `database/` contient `migrations/` (structure des tables), `factories/` (fausses données de test) et `seeders/` (données de départ).
- `resources/js/` contient l'interface React : `Pages/` (une page par écran), `Components/` (briques réutilisables) et `Layouts/` (cadres communs).
- `resources/css/` contient les styles, et `lang/` les traductions.
- `storage/` contient les journaux (`logs/`), le cache et les fichiers téléversés (`app/`).
- `tests/` contient `Feature/` (tests d'un parcours complet) et `Unit/` (tests d'une petite pièce isolée).
- `public/` est le seul dossier servi au navigateur.

> **Astuce** : quand tu ne sais pas où mettre un fichier, demande-toi quelle responsabilité il porte. « Il vérifie des données » : Requests. « Il décide qui a le droit » : Policies. « Il parle à une table » : Models.

:::quiz
Où range-t-on les règles de validation d'un formulaire dans une application Laravel bien organisée ?
- [ ] Dans app/Models
- [ ] Dans routes/web.php
- [x] Dans app/Http/Requests
- [ ] Dans public/
> Les Form Requests regroupent les règles de validation. Les modèles parlent aux tables, et les routes ne font que déclarer des adresses.
:::

## Le fichier bootstrap/app.php

Si tu as déjà vu d'anciens tutoriels Laravel, tu as peut-être croisé des fichiers comme `app/Http/Kernel.php`. Dans les versions récentes (Laravel 11 et suivantes), le squelette du projet est **allégé** : ces fichiers ont disparu et la configuration globale se fait en un seul endroit, `bootstrap/app.php`.

Voici sa forme générale :

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // On ajoute ici les middlewares globaux ou de groupe.
        $middleware->web(append: [
            \App\Http\Middleware\HandleInertiaRequests::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // On personnalise ici la gestion des erreurs.
    })
    ->create();
```

Tu y lis trois décisions : quels fichiers de routes charger, quels middlewares ajouter, comment traiter les exceptions. C'est le **tableau de bord** de ton application. Quand Breeze avec Inertia installe son middleware, c'est ici qu'il le branche.

L'option `health: '/up'` crée une adresse de santé que les hébergeurs interrogent pour savoir si l'application répond.

## Les conventions de nommage

Laravel suit le principe « convention plutôt que configuration » : si tu nommes les choses comme il l'attend, il devine le reste. Apprends ces règles par cœur, elles te feront gagner un temps énorme.

| Élément | Convention | Exemple |
| --- | --- | --- |
| Modèle | Singulier, PascalCase | `Roadmap` |
| Table | Pluriel, snake_case | `roadmaps` |
| Clé étrangère | Nom du modèle au singulier + `_id` | `roadmap_id` |
| Contrôleur | Nom du modèle + `Controller` | `RoadmapController` |
| Migration | Verbe, table, `_table` | `create_roadmaps_table` |
| Form Request | `Store` ou `Update` + modèle + `Request` | `StoreRoadmapRequest` |
| Policy | Nom du modèle + `Policy` | `RoadmapPolicy` |
| Table pivot | Deux modèles, ordre alphabétique, singulier | `memo_tag` |
| Page Inertia | Dossier du modèle pluriel, action | `Roadmaps/Index.jsx` |

Grâce à ces règles, le modèle `Roadmap` sait tout seul qu'il correspond à la table `roadmaps`, et la `RoadmapPolicy` est reliée automatiquement au modèle `Roadmap`. Tu n'écris aucune ligne de configuration pour cela.

> **Erreur fréquente** : nommer une table au singulier (`roadmap`) ou un modèle au pluriel (`Roadmaps`). Le code fonctionnera peut-être, mais tu devras compenser avec de la configuration à chaque endroit, et ton équipe sera perdue.

:::quiz
Un modèle s'appelle `RoadmapStep`. Quel est le nom attendu de sa table ?
- [ ] roadmapstep
- [ ] RoadmapSteps
- [x] roadmap_steps
- [ ] steps_roadmap
> Laravel déduit le nom de la table en passant le nom de la classe en snake_case et au pluriel : RoadmapStep devient roadmap_steps.
:::

## Fournisseurs de services et injection de dépendances

Deux notions semblent intimidantes au début. Voici de quoi tu as besoin, sans jargon.

### Le conteneur de services

Imagine un atelier où tu demandes « donne-moi un marteau » sans te soucier de sa fabrication. Laravel possède un **conteneur de services** qui fabrique et fournit les objets dont tu as besoin. Tu n'écris jamais `new` à chaque fois : tu demandes, il fournit.

La façon la plus courante de demander est l'**injection de dépendances** : tu déclares l'objet voulu dans les paramètres de ta méthode, et Laravel le construit pour toi.

```php
<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    // Laravel fournit $request (la requête en cours)
    // et $roadmap (retrouvée en base grâce à l'adresse /roadmaps/{roadmap}).
    public function show(Request $request, Roadmap $roadmap)
    {
        $utilisateur = $request->user();

        // ...
    }
}
```

Tu n'as rien instancié : tu as seulement écrit les types des paramètres. C'est de la magie contrôlée, et tu vas l'utiliser en permanence.

### Les fournisseurs de services

Les **service providers** décrivent ce qui doit être préparé au démarrage de l'application. Le fichier `app/Providers/AppServiceProvider.php` est ton point d'entrée pour y brancher tes propres réglages, par exemple la langue des dates ou un comportement global. Tu ne le toucheras pas souvent au début, mais il est utile de savoir qu'il existe.

### Facades et fonctions d'aide

Tu verras dans le code des écritures comme `Route::get(...)`, `Auth::user()` ou `config('app.name')`. Les premières sont des **facades**, un raccourci d'écriture pour accéder à un service du conteneur. Les secondes sont des **fonctions d'aide** (helpers). Elles font le même travail ; retiens simplement qu'elles ne sont pas de la magie noire, juste des raccourcis.

## Suivre une fonctionnalité de bout en bout

La meilleure façon de comprendre la structure est de suivre une action réelle. Prenons « un utilisateur crée une roadmap » dans DevRoad et listons chaque fichier concerné.

1. **`routes/web.php`** déclare `POST /roadmaps` et l'associe à `RoadmapController@store`.
2. **`StoreRoadmapRequest`** vérifie que le titre est présent et valide.
3. **`RoadmapPolicy`** confirme que l'utilisateur a le droit de créer.
4. **`RoadmapController@store`** appelle le modèle ou un service pour enregistrer.
5. **`Roadmap`** (le modèle) parle à la table `roadmaps`.
6. **`database/migrations/..._create_roadmaps_table.php`** a défini la forme de cette table.
7. **`resources/js/Pages/Roadmaps/Create.jsx`** affiche le formulaire et envoie les données.

Sept fichiers, sept responsabilités distinctes. Quand une fonctionnalité plante, tu sais par où commencer : la validation échoue, regarde la Form Request ; l'accès est refusé, regarde la Policy ; la donnée n'est pas enregistrée, regarde le modèle et la migration.

> **À retenir** : une fonctionnalité Laravel n'est jamais un gros fichier. C'est une petite chaîne de fichiers courts, chacun avec une seule responsabilité.

## Atelier guidé : la chasse aux fichiers

Compte quarante-cinq minutes. Utilise un projet Laravel (celui de l'atelier précédent, ou DevRoad) et la recherche globale de ton éditeur.

1. Ouvre `bootstrap/app.php` et repère les trois blocs : routing, middleware, exceptions.
2. Dans `routes/web.php`, choisis une route. Note le contrôleur qu'elle appelle, puis ouvre-le.
3. Dans ce contrôleur, repère un paramètre injecté (un `Request` ou un modèle). Explique à voix haute ce que Laravel fait à ta place.
4. Trouve le modèle utilisé, puis la migration qui crée sa table. Compare les noms : respectent-ils les conventions ?
5. Lance `php artisan make:request StoreExempleRequest`. Retrouve le fichier créé et note son dossier.
6. Supprime ce fichier de test.
7. Dessine sur une feuille le trajet d'une fonctionnalité de ton choix, avec les fichiers traversés.

Pour te tester : si un camarade te demande « où je mets ce fichier ? », tu dois pouvoir répondre sans hésiter pour un modèle, une validation, une autorisation et une page.

## Erreurs fréquentes

- **Tout mettre dans le contrôleur.** Cela marche au début, puis le fichier devient illisible. Sépare dès que possible : validation en Request, droits en Policy.
- **Casser les conventions de nommage.** Tu te forces ensuite à tout configurer à la main.
- **Modifier `vendor/`.** Ces fichiers appartiennent aux paquets et sont écrasés à chaque mise à jour.
- **Chercher `Kernel.php`.** Il n'existe plus dans les versions récentes : tout se règle dans `bootstrap/app.php`.
- **Créer les fichiers à la main au mauvais endroit.** Préfère `php artisan make:...`, qui connaît les bons dossiers et les bons espaces de noms.

## Bonnes pratiques

- Un fichier, une responsabilité.
- Respecte les conventions de nommage, même quand tu es pressé.
- Utilise les commandes `make:` pour générer les fichiers.
- Crée tes propres classes métier dans `app/Services` quand un contrôleur devient trop long.
- Garde des noms parlants : `GenerateRoadmap` dit ce qu'il fait, `Helper2` non.

## À retenir

- Laravel range le code par responsabilité : modèles, contrôleurs, requêtes, policies, services.
- `bootstrap/app.php` centralise le routage, les middlewares et la gestion des erreurs.
- Les conventions de nommage (modèle singulier, table pluriel, `_id`, `Controller`, `Policy`) évitent la configuration.
- Le conteneur de services fournit les objets demandés dans les paramètres : c'est l'injection de dépendances.
- Une fonctionnalité est une chaîne de petits fichiers : route, requête, policy, contrôleur, modèle, migration, page.
