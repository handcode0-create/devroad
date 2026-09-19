<?php

namespace Database\Seeders;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\User;
use Illuminate\Database\Seeder;

class DevRoadCourseSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::query()->first();

        if (! $user) {
            $user = User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        $roadmap = $user->roadmaps()->firstOrCreate(
            ['title' => 'Laravel'],
            [
                'description' => 'Parcours progressif pour apprendre Laravel de la base jusqu\'à la construction d\'une application complète.',
                'status' => 'active',
            ],
        );

        $lessons = [
            [
                'title' => 'Découvrir Laravel',
                'description' => 'Comprendre Laravel, son rôle et le fonctionnement général d\'une application Laravel.',
                'objective' => 'Identifier les principales briques de Laravel et comprendre le cycle requête → route → contrôleur → réponse.',
                'content' => <<<'MD'
## Qu\'est-ce que Laravel ?

Laravel est un framework PHP destiné à construire des applications web modernes. Il fournit une structure claire pour organiser les routes, contrôleurs, modèles, vues, validations, authentification et accès aux données.

## Le cycle d\'une requête

Une requête HTTP arrive dans l\'application. Laravel la fait passer par son système de routes et son middleware avant de l\'envoyer vers le contrôleur approprié.

Le contrôleur exécute la logique nécessaire, utilise éventuellement les modèles Eloquent, puis retourne une réponse HTML, JSON ou une réponse Inertia.

## Les briques que tu vas apprendre

- Routes
- Controllers
- Models et Eloquent
- Migrations
- Form Requests
- Policies
- Inertia et React
- Tests
MD,
                'code_example' => <<<'PHP'
use Illuminate\Support\Facades\Route;

Route::get('/bonjour', function () {
    return 'Bonjour DevRoad';
});
PHP,
                'estimated_minutes' => 20,
            ],
            [
                'title' => 'Installer et lancer Laravel',
                'description' => 'Comprendre la structure minimale nécessaire pour démarrer un projet Laravel.',
                'objective' => 'Savoir créer un projet, installer ses dépendances et lancer le serveur de développement.',
                'content' => <<<'MD'
## Préparer l\'environnement

Un projet Laravel utilise PHP et Composer pour le backend. Une application avec Inertia + React utilise également Node.js et npm pour le frontend.

## Les commandes essentielles

Après la création du projet, installe les dépendances PHP et JavaScript puis lance le serveur Laravel et Vite.

## Comprendre les deux serveurs

Laravel sert les requêtes PHP. Vite construit et recharge les assets frontend pendant le développement.
MD,
                'code_example' => <<<'BASH'
composer install
npm install

php artisan migrate

php artisan serve
npm run dev
BASH,
                'estimated_minutes' => 25,
            ],
            [
                'title' => 'Comprendre la structure du projet',
                'description' => 'Se repérer dans les principaux dossiers d\'une application Laravel.',
                'objective' => 'Savoir où placer routes, contrôleurs, modèles, migrations et fichiers React.',
                'content' => <<<'MD'
## Les dossiers importants

app/ contient la logique PHP de l\'application.

routes/ contient les routes HTTP.

database/migrations/ décrit l\'évolution de la base de données.

resources/js/ contient notre interface React avec Inertia.

public/ contient les assets accessibles publiquement.

## La règle importante

Ne mélange pas les responsabilités. Une route ne doit pas devenir un énorme bloc métier et un composant React ne doit pas connaître les détails de ta base de données.
MD,
                'code_example' => <<<'TEXT'
app/
├── Http/
│   ├── Controllers/
│   └── Requests/
├── Models/
└── Policies/

database/
└── migrations/

resources/
└── js/
    └── Pages/

routes/
└── web.php
TEXT,
                'estimated_minutes' => 20,
            ],
            [
                'title' => 'Les routes Laravel',
                'description' => 'Créer des routes simples et comprendre le lien entre URL, contrôleur et action.',
                'objective' => 'Être capable de créer, nommer et organiser des routes.',
                'content' => <<<'MD'
## Une route relie une URL à une action

Une route peut retourner directement une réponse ou appeler une méthode d\'un contrôleur.

## Pourquoi nommer les routes ?

Les noms permettent d\'éviter de dépendre des URLs en dur dans toute l\'application. Laravel peut ensuite générer l\'URL à partir du nom de route.

## Routes resource

Une resource génère les routes classiques d\'un CRUD : index, create, store, show, edit, update et destroy.
MD,
                'code_example' => <<<'PHP'
use App\Http\Controllers\RoadmapController;
use Illuminate\Support\Facades\Route;

Route::get('/hello', fn () => 'Hello');

Route::get('/roadmaps', [RoadmapController::class, 'index'])
    ->name('roadmaps.index');

Route::resource('roadmaps', RoadmapController::class);
PHP,
                'estimated_minutes' => 30,
            ],
            [
                'title' => 'Les Controllers',
                'description' => 'Déplacer la logique HTTP dans des contrôleurs propres et maintenables.',
                'objective' => 'Comprendre le rôle d\'un contrôleur et savoir retourner une réponse Inertia.',
                'content' => <<<'MD'
## Le rôle du contrôleur

Le contrôleur reçoit la requête, orchestre la logique de l\'application et retourne une réponse.

Avec Inertia, Laravel choisit une page React et lui transmet des props.

## Garder le contrôleur lisible

Le contrôleur ne doit pas contenir toute la logique métier. Les validations vont dans les Form Requests, l\'autorisation dans les Policies et l\'accès aux données dans Eloquent.
MD,
                'code_example' => <<<'PHP'
use Inertia\Inertia;

public function index(Request $request)
{
    $roadmaps = $request->user()
        ->roadmaps()
        ->latest()
        ->get();

    return Inertia::render('Roadmaps/Index', [
        'roadmaps' => $roadmaps,
    ]);
}
PHP,
                'estimated_minutes' => 30,
            ],
            [
                'title' => 'Migrations et schéma de base',
                'description' => 'Décrire les tables de l\'application avec des migrations Laravel.',
                'objective' => 'Savoir créer une table, ajouter des colonnes et faire évoluer le schéma sans modifier directement la base.',
                'content' => <<<'MD'
## Une migration est une version du schéma

Elle décrit ce qui doit être créé ou modifié dans la base de données.

## La méthode up()

up() applique le changement.

## La méthode down()

down() permet de revenir en arrière.

## Bon réflexe

Chaque changement structurel doit être versionné dans Git avec une migration.
MD,
                'code_example' => <<<'PHP'
Schema::create('roadmaps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')
        ->constrained()
        ->cascadeOnDelete();
    $table->string('title');
    $table->text('description')->nullable();
    $table->timestamps();
});
PHP,
                'estimated_minutes' => 35,
            ],
            [
                'title' => 'Models et Eloquent',
                'description' => 'Manipuler les données avec Eloquent plutôt qu\'avec du SQL brut partout.',
                'objective' => 'Créer un modèle, définir ses champs autorisés et interagir avec une table.',
                'content' => <<<'MD'
## Eloquent

Eloquent représente les tables de la base sous forme de modèles PHP.

Les méthodes comme create, where, find et update permettent de travailler avec les données de manière expressive.

## Fillable

La propriété $fillable protège les créations massives et définit les attributs acceptés par create() et update().
MD,
                'code_example' => <<<'PHP'
$roadmap = Roadmap::create([
    'title' => 'Apprendre Laravel',
    'description' => 'Parcours personnel',
    'status' => 'active',
]);

$roadmaps = Roadmap::query()
    ->where('status', 'active')
    ->latest()
    ->get();
PHP,
                'estimated_minutes' => 35,
            ],
            [
                'title' => 'Relations Eloquent',
                'description' => 'Relier utilisateurs, roadmaps et étapes.',
                'objective' => 'Comprendre belongsTo, hasMany et exploiter les relations dans les requêtes.',
                'content' => <<<'MD'
## Une roadmap possède plusieurs étapes

Dans DevRoad, un utilisateur possède plusieurs roadmaps et une roadmap possède plusieurs étapes.

## Les deux côtés de la relation

La roadmap utilise hasMany(RoadmapStep::class) tandis que l\'étape utilise belongsTo(Roadmap::class).

Cette relation permet ensuite d\'écrire du code lisible comme $roadmap->steps() ou $step->roadmap.
MD,
                'code_example' => <<<'PHP'
class Roadmap extends Model
{
    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)
            ->orderBy('position');
    }
}

class RoadmapStep extends Model
{
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}
PHP,
                'estimated_minutes' => 35,
            ],
            [
                'title' => 'Validation avec Form Requests',
                'description' => 'Valider les données entrantes avant de les enregistrer.',
                'objective' => 'Créer des règles de validation propres et retourner des erreurs exploitables par React.',
                'content' => <<<'MD'
## Pourquoi un Form Request ?

Il centralise la validation et évite de transformer les contrôleurs en longues listes de règles.

## Validation et sécurité

La validation vérifie le format des données. Elle ne remplace pas l\'autorisation. Les deux sont complémentaires.

Avec Inertia, les erreurs de validation peuvent être affichées directement dans le formulaire React.
MD,
                'code_example' => <<<'PHP'
public function rules(): array
{
    return [
        'title' => ['required', 'string', 'min:3', 'max:255'],
        'description' => ['nullable', 'string', 'max:5000'],
    ];
}
PHP,
                'estimated_minutes' => 30,
            ],
            [
                'title' => 'Policies et autorisation',
                'description' => 'Empêcher un utilisateur de lire ou modifier les ressources d\'un autre utilisateur.',
                'objective' => 'Comprendre la différence entre authentification et autorisation et appliquer une Policy.',
                'content' => <<<'MD'
## Authentification

Elle répond à la question : « Qui est connecté ? »

## Autorisation

Elle répond à la question : « Cette personne a-t-elle le droit de faire cette action ? »

## Dans DevRoad

Une roadmap appartient à un utilisateur. La Policy vérifie donc la propriété avant d\'autoriser la lecture, modification ou suppression.
MD,
                'code_example' => <<<'PHP'
public function view(User $user, Roadmap $roadmap): bool
{
    return $roadmap->user_id === $user->id;
}

// Dans le contrôleur :
$this->authorize('view', $roadmap);
PHP,
                'estimated_minutes' => 30,
            ],
            [
                'title' => 'Inertia et React',
                'description' => 'Construire une interface React alimentée directement par Laravel.',
                'objective' => 'Comprendre le passage des données Laravel vers une page React et les actions avec useForm ou router.',
                'content' => <<<'MD'
## Le flux

Laravel choisit une page Inertia et lui envoie des props.

React reçoit ces props et construit l\'interface.

Une action utilisateur peut ensuite déclencher une requête Inertia vers Laravel sans recharger toute l\'application.

## Résultat

On conserve la puissance du backend Laravel avec une expérience frontend moderne.
MD,
                'code_example' => <<<'JSX'
import { Head } from '@inertiajs/react';

export default function Index({ roadmaps }) {
    return (
        <>
            <Head title="Roadmaps" />

            {roadmaps.map((roadmap) => (
                <div key={roadmap.id}>
                    {roadmap.title}
                </div>
            ))}
        </>
    );
}
JSX,
                'estimated_minutes' => 35,
            ],
            [
                'title' => 'Construire un CRUD complet',
                'description' => 'Assembler routes, requests, policy, controller, modèle et interface.',
                'objective' => 'Être capable de construire une fonctionnalité CRUD complète sans mélanger les responsabilités.',
                'content' => <<<'MD'
## La chaîne complète

1. Concevoir la table.
2. Créer la migration.
3. Créer le modèle.
4. Définir les relations.
5. Créer les Form Requests.
6. Définir la Policy.
7. Construire le Controller.
8. Déclarer les routes.
9. Construire les pages React.
10. Tester le parcours complet.

Cette méthode constitue le cœur de la façon dont DevRoad est lui-même construit.
MD,
                'code_example' => <<<'TEXT'
Migration
   ↓
Model
   ↓
Request
   ↓
Policy
   ↓
Controller
   ↓
Route
   ↓
Inertia
   ↓
React
TEXT,
                'estimated_minutes' => 45,
            ],
            [
                'title' => 'Projet final : construire un module',
                'description' => 'Mettre en pratique les notions du parcours dans une vraie fonctionnalité Laravel.',
                'objective' => 'Construire seul une fonctionnalité complète et sécurisée.',
                'content' => <<<'MD'
## Mission

Construis un petit module de gestion de ressources.

Ton module doit posséder :

- une migration ;
- un modèle ;
- au moins une relation ;
- un CRUD ;
- une validation ;
- une Policy ;
- une interface React/Inertia ;
- une gestion d\'état ;
- une progression ou un indicateur métier.

## Critère de réussite

Tu dois pouvoir expliquer chaque couche et justifier pourquoi elle existe.

Le but n\'est pas de recopier du code : tu dois être capable de reconstruire le fonctionnement à partir du besoin.
MD,
                'code_example' => <<<'TEXT'
Besoin
  ↓
MCD / structure
  ↓
Migration
  ↓
Model + relations
  ↓
Validation + Policy
  ↓
Controller + routes
  ↓
Interface
  ↓
Tests
TEXT,
                'estimated_minutes' => 60,
            ],
        ];

        foreach ($lessons as $index => $lesson) {
            $roadmap->steps()->updateOrCreate(
                ['position' => $index + 1],
                [
                    ...$lesson,
                    'status' => $index === 0
                        ? RoadmapStep::IN_PROGRESS
                        : RoadmapStep::TODO,
                ],
            );
        }
    }
}
