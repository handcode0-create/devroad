<?php

/*
|--------------------------------------------------------------------------
| DevRoad course templates
|--------------------------------------------------------------------------
|
| Source unique utilisée par le générateur de parcours.
|
*/

return [
        'laravel' => [
            'title' => 'Laravel',
            'description' => 'Parcours progressif pour apprendre Laravel de la base jusqu\'à la construction d\'une application complète.',
            'lessons' => [
                [
                    'title' => 'Découvrir Laravel',
                    'description' => 'Comprendre Laravel, son rôle et le fonctionnement général d\\\'une application Laravel.',
                    'objective' => 'Identifier les principales briques de Laravel et comprendre le cycle requête → route → contrôleur → réponse.',
                    'content' => '## Qu\\\'est-ce que Laravel ?

Laravel est un framework PHP destiné à construire des applications web modernes. Il fournit une structure claire pour organiser les routes, contrôleurs, modèles, vues, validations, authentification et accès aux données.

## Le cycle d\\\'une requête

Une requête HTTP arrive dans l\\\'application. Laravel la fait passer par son système de routes et son middleware avant de l\\\'envoyer vers le contrôleur approprié.

Le contrôleur exécute la logique nécessaire, utilise éventuellement les modèles Eloquent, puis retourne une réponse HTML, JSON ou une réponse Inertia.

## Les briques que tu vas apprendre

- Routes
- Controllers
- Models et Eloquent
- Migrations
- Form Requests
- Policies
- Inertia et React
- Tests',
                    'code_example' => 'use Illuminate\\Support\\Facades\\Route;

Route::get(\'/bonjour\', function () {
    return \'Bonjour DevRoad\';
});',
                    'estimated_minutes' => 20,
                ],
                [
                    'title' => 'Installer et lancer Laravel',
                    'description' => 'Comprendre la structure minimale nécessaire pour démarrer un projet Laravel.',
                    'objective' => 'Savoir créer un projet, installer ses dépendances et lancer le serveur de développement.',
                    'content' => '## Préparer l\\\'environnement

Un projet Laravel utilise PHP et Composer pour le backend. Une application avec Inertia + React utilise également Node.js et npm pour le frontend.

## Les commandes essentielles

Après la création du projet, installe les dépendances PHP et JavaScript puis lance le serveur Laravel et Vite.

## Comprendre les deux serveurs

Laravel sert les requêtes PHP. Vite construit et recharge les assets frontend pendant le développement.',
                    'code_example' => 'composer install
npm install

php artisan migrate

php artisan serve
npm run dev',
                    'estimated_minutes' => 25,
                ],
                [
                    'title' => 'Comprendre la structure du projet',
                    'description' => 'Se repérer dans les principaux dossiers d\\\'une application Laravel.',
                    'objective' => 'Savoir où placer routes, contrôleurs, modèles, migrations et fichiers React.',
                    'content' => '## Les dossiers importants

app/ contient la logique PHP de l\\\'application.

routes/ contient les routes HTTP.

database/migrations/ décrit l\\\'évolution de la base de données.

resources/js/ contient notre interface React avec Inertia.

public/ contient les assets accessibles publiquement.

## La règle importante

Ne mélange pas les responsabilités. Une route ne doit pas devenir un énorme bloc métier et un composant React ne doit pas connaître les détails de ta base de données.',
                    'code_example' => 'app/
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
└── web.php',
                    'estimated_minutes' => 20,
                ],
                [
                    'title' => 'Les routes Laravel',
                    'description' => 'Créer des routes simples et comprendre le lien entre URL, contrôleur et action.',
                    'objective' => 'Être capable de créer, nommer et organiser des routes.',
                    'content' => '## Une route relie une URL à une action

Une route peut retourner directement une réponse ou appeler une méthode d\\\'un contrôleur.

## Pourquoi nommer les routes ?

Les noms permettent d\\\'éviter de dépendre des URLs en dur dans toute l\\\'application. Laravel peut ensuite générer l\\\'URL à partir du nom de route.

## Routes resource

Une resource génère les routes classiques d\\\'un CRUD : index, create, store, show, edit, update et destroy.',
                    'code_example' => 'use App\\Http\\Controllers\\RoadmapController;
use Illuminate\\Support\\Facades\\Route;

Route::get(\'/hello\', fn () => \'Hello\');

Route::get(\'/roadmaps\', [RoadmapController::class, \'index\'])
    ->name(\'roadmaps.index\');

Route::resource(\'roadmaps\', RoadmapController::class);',
                    'estimated_minutes' => 30,
                ],
                [
                    'title' => 'Les Controllers',
                    'description' => 'Déplacer la logique HTTP dans des contrôleurs propres et maintenables.',
                    'objective' => 'Comprendre le rôle d\\\'un contrôleur et savoir retourner une réponse Inertia.',
                    'content' => '## Le rôle du contrôleur

Le contrôleur reçoit la requête, orchestre la logique de l\\\'application et retourne une réponse.

Avec Inertia, Laravel choisit une page React et lui transmet des props.

## Garder le contrôleur lisible

Le contrôleur ne doit pas contenir toute la logique métier. Les validations vont dans les Form Requests, l\\\'autorisation dans les Policies et l\\\'accès aux données dans Eloquent.',
                    'code_example' => 'use Inertia\\Inertia;

public function index(Request $request)
{
    $roadmaps = $request->user()
        ->roadmaps()
        ->latest()
        ->get();

    return Inertia::render(\'Roadmaps/Index\', [
        \'roadmaps\' => $roadmaps,
    ]);
}',
                    'estimated_minutes' => 30,
                ],
                [
                    'title' => 'Migrations et schéma de base',
                    'description' => 'Décrire les tables de l\\\'application avec des migrations Laravel.',
                    'objective' => 'Savoir créer une table, ajouter des colonnes et faire évoluer le schéma sans modifier directement la base.',
                    'content' => '## Une migration est une version du schéma

Elle décrit ce qui doit être créé ou modifié dans la base de données.

## La méthode up()

up() applique le changement.

## La méthode down()

down() permet de revenir en arrière.

## Bon réflexe

Chaque changement structurel doit être versionné dans Git avec une migration.',
                    'code_example' => 'Schema::create(\'roadmaps\', function (Blueprint $table) {
    $table->id();
    $table->foreignId(\'user_id\')
        ->constrained()
        ->cascadeOnDelete();
    $table->string(\'title\');
    $table->text(\'description\')->nullable();
    $table->timestamps();
});',
                    'estimated_minutes' => 35,
                ],
                [
                    'title' => 'Models et Eloquent',
                    'description' => 'Manipuler les données avec Eloquent plutôt qu\\\'avec du SQL brut partout.',
                    'objective' => 'Créer un modèle, définir ses champs autorisés et interagir avec une table.',
                    'content' => '## Eloquent

Eloquent représente les tables de la base sous forme de modèles PHP.

Les méthodes comme create, where, find et update permettent de travailler avec les données de manière expressive.

## Fillable

La propriété $fillable protège les créations massives et définit les attributs acceptés par create() et update().',
                    'code_example' => '$roadmap = Roadmap::create([
    \'title\' => \'Apprendre Laravel\',
    \'description\' => \'Parcours personnel\',
    \'status\' => \'active\',
]);

$roadmaps = Roadmap::query()
    ->where(\'status\', \'active\')
    ->latest()
    ->get();',
                    'estimated_minutes' => 35,
                ],
                [
                    'title' => 'Relations Eloquent',
                    'description' => 'Relier utilisateurs, roadmaps et étapes.',
                    'objective' => 'Comprendre belongsTo, hasMany et exploiter les relations dans les requêtes.',
                    'content' => '## Une roadmap possède plusieurs étapes

Dans DevRoad, un utilisateur possède plusieurs roadmaps et une roadmap possède plusieurs étapes.

## Les deux côtés de la relation

La roadmap utilise hasMany(RoadmapStep::class) tandis que l\\\'étape utilise belongsTo(Roadmap::class).

Cette relation permet ensuite d\\\'écrire du code lisible comme $roadmap->steps() ou $step->roadmap.',
                    'code_example' => 'class Roadmap extends Model
{
    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)
            ->orderBy(\'position\');
    }
}

class RoadmapStep extends Model
{
    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}',
                    'estimated_minutes' => 35,
                ],
                [
                    'title' => 'Validation avec Form Requests',
                    'description' => 'Valider les données entrantes avant de les enregistrer.',
                    'objective' => 'Créer des règles de validation propres et retourner des erreurs exploitables par React.',
                    'content' => '## Pourquoi un Form Request ?

Il centralise la validation et évite de transformer les contrôleurs en longues listes de règles.

## Validation et sécurité

La validation vérifie le format des données. Elle ne remplace pas l\\\'autorisation. Les deux sont complémentaires.

Avec Inertia, les erreurs de validation peuvent être affichées directement dans le formulaire React.',
                    'code_example' => 'public function rules(): array
{
    return [
        \'title\' => [\'required\', \'string\', \'min:3\', \'max:255\'],
        \'description\' => [\'nullable\', \'string\', \'max:5000\'],
    ];
}',
                    'estimated_minutes' => 30,
                ],
                [
                    'title' => 'Policies et autorisation',
                    'description' => 'Empêcher un utilisateur de lire ou modifier les ressources d\\\'un autre utilisateur.',
                    'objective' => 'Comprendre la différence entre authentification et autorisation et appliquer une Policy.',
                    'content' => '## Authentification

Elle répond à la question : « Qui est connecté ? »

## Autorisation

Elle répond à la question : « Cette personne a-t-elle le droit de faire cette action ? »

## Dans DevRoad

Une roadmap appartient à un utilisateur. La Policy vérifie donc la propriété avant d\\\'autoriser la lecture, modification ou suppression.',
                    'code_example' => 'public function view(User $user, Roadmap $roadmap): bool
{
    return $roadmap->user_id === $user->id;
}

// Dans le contrôleur :
$this->authorize(\'view\', $roadmap);',
                    'estimated_minutes' => 30,
                ],
                [
                    'title' => 'Inertia et React',
                    'description' => 'Construire une interface React alimentée directement par Laravel.',
                    'objective' => 'Comprendre le passage des données Laravel vers une page React et les actions avec useForm ou router.',
                    'content' => '## Le flux

Laravel choisit une page Inertia et lui envoie des props.

React reçoit ces props et construit l\\\'interface.

Une action utilisateur peut ensuite déclencher une requête Inertia vers Laravel sans recharger toute l\\\'application.

## Résultat

On conserve la puissance du backend Laravel avec une expérience frontend moderne.',
                    'code_example' => 'import { Head } from \'@inertiajs/react\';

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
}',
                    'estimated_minutes' => 35,
                ],
                [
                    'title' => 'Construire un CRUD complet',
                    'description' => 'Assembler routes, requests, policy, controller, modèle et interface.',
                    'objective' => 'Être capable de construire une fonctionnalité CRUD complète sans mélanger les responsabilités.',
                    'content' => '## La chaîne complète

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

Cette méthode constitue le cœur de la façon dont DevRoad est lui-même construit.',
                    'code_example' => 'Migration
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
React',
                    'estimated_minutes' => 45,
                ],
                [
                    'title' => 'Projet final : construire un module',
                    'description' => 'Mettre en pratique les notions du parcours dans une vraie fonctionnalité Laravel.',
                    'objective' => 'Construire seul une fonctionnalité complète et sécurisée.',
                    'content' => '## Mission

Construis un petit module de gestion de ressources.

Ton module doit posséder :

- une migration ;
- un modèle ;
- au moins une relation ;
- un CRUD ;
- une validation ;
- une Policy ;
- une interface React/Inertia ;
- une gestion d\\\'état ;
- une progression ou un indicateur métier.

## Critère de réussite

Tu dois pouvoir expliquer chaque couche et justifier pourquoi elle existe.

Le but n\\\'est pas de recopier du code : tu dois être capable de reconstruire le fonctionnement à partir du besoin.',
                    'code_example' => 'Besoin
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
Tests',
                    'estimated_minutes' => 60,
                ],
            ],
        ],
'nextjs' => [
            'title' => 'Next.js',
            'description' => 'Parcours progressif pour construire des applications Next.js modernes avec App Router.',
            'lessons' => [
                ['Découvrir Next.js', 'Comprendre Next.js, React Server Components, le routing et le rôle du framework.', 'Comprendre comment Next.js complète React avec un framework full-stack.', 'Next.js fournit le routing, le rendu serveur, les layouts, les APIs et les optimisations nécessaires à une application web moderne.'],
                ['Créer et lancer un projet', 'Installer Next.js et comprendre les outils de développement.', 'Savoir créer une application et lancer son environnement local.', 'Utilise create-next-app puis lance le serveur de développement. Le dossier app contient les routes et les composants de l’application.'],
                ['App Router et routing', 'Créer des pages, layouts et routes dynamiques.', 'Construire une navigation structurée avec App Router.', 'Chaque dossier de app peut représenter un segment d’URL. page.tsx rend une page et layout.tsx permet de partager une structure.'],
                ['Server et Client Components', 'Comprendre la frontière serveur/client.', 'Choisir correctement entre rendu serveur et interaction navigateur.', 'Par défaut, les composants de l’App Router sont exécutés côté serveur. Ajoute "use client" uniquement lorsqu’une interaction ou une API navigateur est nécessaire.'],
                ['Données, formulaires et API', 'Récupérer des données et construire des endpoints.', 'Relier une interface Next.js à une source de données.', 'Les Server Components peuvent récupérer des données directement. Les Route Handlers permettent de créer des endpoints HTTP dans app/api.'],
                ['Performance et bonnes pratiques', 'Optimiser images, chargements, erreurs et métadonnées.', 'Construire une application robuste et performante.', 'Utilise les outils natifs de Next.js pour les images, métadonnées, loading.tsx, error.tsx et la séparation des responsabilités.'],
                ['Projet final Next.js', 'Construire une petite application complète.', 'Assembler routing, composants, données et interface responsive.', 'Projet conseillé : tableau de bord avec authentification, liste de données, page détail, formulaire et endpoint API.'],
            ],
            'code' => ['export default function Page() { return <h1>Hello Next.js</h1>; }', 'npx create-next-app@latest devroad-next', 'export default function Layout({ children }) { return <main>{children}</main>; }', '"use client";\n\nimport { useState } from "react";', 'export async function GET() { return Response.json({ ok: true }); }', 'export const metadata = { title: "DevRoad" };', 'app/\n├── page.tsx\n├── layout.tsx\n└── api/\n    └── hello/\n        └── route.ts'],
        ],
        'react' => [
            'title' => 'React',
            'description' => 'Parcours progressif pour maîtriser React et construire des interfaces composables.',
            'lessons' => [
                ['Découvrir React', 'Comprendre composants, JSX et rendu déclaratif.', 'Comprendre le modèle mental de React.', 'React permet de décrire l’interface à partir de l’état et des propriétés des composants. Une application est construite comme un arbre de composants.'],
                ['Créer un projet React', 'Préparer un environnement moderne avec Vite.', 'Savoir créer, lancer et structurer une application React.', 'Vite fournit le serveur de développement et le build. Le point d’entrée rend généralement le composant racine dans le DOM.'],
                ['Composants et props', 'Créer des composants réutilisables et leur transmettre des données.', 'Découper une interface sans dupliquer la logique.', 'Les props sont des données en entrée. Un composant doit rester prévisible : les mêmes props produisent le même rendu.'],
                ['State et événements', 'Gérer les interactions avec useState.', 'Faire évoluer l’interface en réponse aux actions utilisateur.', 'Le state appartient au composant qui possède la responsabilité de la donnée. Un changement de state déclenche un nouveau rendu.'],
                ['Effets et données', 'Comprendre useEffect et le chargement de données.', 'Éviter les effets inutiles et synchroniser l’interface avec le monde extérieur.', 'useEffect sert aux synchronisations externes. Pour les données, privilégie une stratégie adaptée à ton architecture plutôt que des effets imbriqués.'],
                ['Architecture et performance', 'Organiser composants, hooks et logique métier.', 'Garder une application React maintenable.', 'Sépare composants de présentation, logique réutilisable et accès aux données. Évite les abstractions prématurées et les re-renders inutiles.'],
                ['Projet final React', 'Construire une interface complète.', 'Assembler composants, state, formulaires et données.', 'Projet conseillé : gestionnaire de tâches avec filtres, formulaire, détail et persistance via API.'],
            ],
            'code' => ['function Welcome() { return <h1>Hello React</h1>; }', 'npm create vite@latest devroad-react -- --template react', 'function Card({ title }) { return <article>{title}</article>; }', 'const [count, setCount] = useState(0);', 'useEffect(() => { document.title = "DevRoad"; }, []);', 'const visible = items.filter((item) => item.active);', 'src/\n├── components/\n├── hooks/\n├── pages/\n└── App.jsx'],
        ],
        'javascript' => [
            'title' => 'JavaScript',
            'description' => 'Parcours complet des fondamentaux JavaScript jusqu’aux applications modernes.',
            'lessons' => [
                ['Fondamentaux JavaScript', 'Variables, types, opérateurs et contrôle du flux.', 'Maîtriser la syntaxe essentielle du langage.', 'JavaScript propose let et const, plusieurs types primitifs, des objets et des structures de contrôle comme if, for et switch.'],
                ['Fonctions et portée', 'Comprendre fonctions, paramètres, closures et portée.', 'Écrire une logique réutilisable et prévisible.', 'Les fonctions sont des valeurs. La portée lexicale permet à une fonction de conserver l’accès aux variables de son environnement.'],
                ['Objets, tableaux et méthodes', 'Manipuler les collections avec map, filter, find et reduce.', 'Transformer des données proprement.', 'Les méthodes de tableaux permettent d’exprimer les transformations de données sans multiplier les boucles impératives.'],
                ['Asynchrone et Promises', 'Comprendre Promise, async/await et fetch.', 'Construire du code qui travaille avec des opérations asynchrones.', 'Une Promise représente le résultat futur d’une opération. async/await rend le flux asynchrone plus lisible.'],
                ['Modules et navigateur', 'Organiser le code et manipuler le DOM.', 'Construire une petite application navigateur structurée.', 'Les modules ES permettent import/export. Les APIs navigateur donnent accès au DOM, au stockage et aux événements.'],
                ['Qualité et debugging', 'Gérer erreurs, linting et tests.', 'Développer du JavaScript fiable.', 'Utilise des erreurs explicites, un debugger, des tests automatisés et un linter pour détecter les problèmes tôt.'],
                ['Projet final JavaScript', 'Construire une application sans framework.', 'Mettre en pratique syntaxe, DOM, asynchrone et stockage.', 'Projet conseillé : gestionnaire de tâches avec recherche, filtres, formulaire et localStorage.'],
            ],
            'code' => ['const name = "DevRoad";\nconsole.log(name);', 'function add(a, b) { return a + b; }', 'const active = items.filter((item) => item.active);', 'const response = await fetch("/api/items");\nconst data = await response.json();', 'export function formatTitle(value) { return value.trim(); }', 'try { await save(); } catch (error) { console.error(error); }', 'src/\n├── app.js\n├── api.js\n├── dom.js\n└── storage.js'],
        ],
        'typescript' => [
            'title' => 'TypeScript',
            'description' => 'Parcours pour maîtriser le typage statique et construire des applications TypeScript robustes.',
            'lessons' => [
                ['Découvrir TypeScript', 'Comprendre types, compilation et intérêt du typage.', 'Savoir pourquoi et quand utiliser TypeScript.', 'TypeScript ajoute un système de types à JavaScript puis produit du JavaScript exécutable. Les erreurs de type peuvent être détectées avant l’exécution.'],
                ['Types primitifs et fonctions', 'Annoter variables, paramètres et valeurs de retour.', 'Écrire des fonctions dont le contrat est explicite.', 'Les annotations documentent les données attendues et rendent les erreurs de manipulation visibles dans l’éditeur.'],
                ['Interfaces et types', 'Modéliser les objets métier.', 'Définir des contrats réutilisables.', 'Les interfaces et alias de types permettent de décrire précisément la forme des données utilisées par l’application.'],
                ['Generics et unions', 'Construire des fonctions et structures flexibles.', 'Réutiliser du code sans perdre les informations de type.', 'Les generics paramètrent un type. Les unions permettent d’exprimer plusieurs états possibles.'],
                ['TypeScript avec React', 'Typer props, state, événements et données API.', 'Éviter les erreurs fréquentes dans les interfaces React.', 'Les types des props et des réponses API constituent une frontière importante entre composants et données externes.'],
                ['Configuration et qualité', 'Comprendre tsconfig, strict mode et linting.', 'Configurer TypeScript pour un projet réel.', 'Un strict mode correctement adopté réduit les valeurs implicites et rend les contrats du code plus fiables.'],
                ['Projet final TypeScript', 'Construire une application typée de bout en bout.', 'Combiner modèles, API, composants et validation.', 'Projet conseillé : dashboard React avec modèles TypeScript, données API, formulaires et états explicites.'],
            ],
            'code' => ['const title: string = "DevRoad";', 'function add(a: number, b: number): number { return a + b; }', 'interface User { id: number; name: string; }', 'function first<T>(items: T[]): T | undefined { return items[0]; }', 'type Props = { title: string; count?: number };', 'const user: User | null = null;', 'src/\n├── types/\n├── components/\n├── services/\n└── main.tsx'],
        ],
        'php' => [
            'title' => 'PHP',
            'description' => 'Parcours PHP des fondamentaux jusqu’aux applications web et APIs structurées.',
            'lessons' => [
                ['Fondamentaux PHP', 'Variables, types, conditions, boucles et fonctions.', 'Maîtriser la syntaxe serveur de base.', 'PHP s’exécute côté serveur et produit généralement une réponse HTML ou JSON. Les types et fonctions constituent la base du langage.'],
                ['Tableaux et fonctions', 'Manipuler données et collections.', 'Transformer des données avec un code lisible.', 'Les tableaux PHP peuvent être indexés ou associatifs. Les fonctions permettent d’isoler des responsabilités.'],
                ['POO et classes', 'Comprendre objets, propriétés, méthodes et héritage.', 'Structurer une application avec la programmation orientée objet.', 'Les classes regroupent état et comportement. Les interfaces et la composition permettent de construire des composants découplés.'],
                ['Composer et autoloading', 'Gérer les dépendances PHP.', 'Comprendre le rôle de Composer dans un projet moderne.', 'Composer installe les dépendances et génère l’autoloading PSR-4. Le fichier composer.json décrit les dépendances du projet.'],
                ['HTTP, formulaires et APIs', 'Recevoir des données et produire des réponses.', 'Construire un endpoint PHP propre.', 'Une application web doit valider les entrées, gérer les erreurs et retourner une réponse cohérente, HTML ou JSON selon le besoin.'],
                ['Sécurité et tests', 'Comprendre validation, échappement et tests.', 'Éviter les vulnérabilités classiques.', 'Ne fais jamais confiance aux entrées utilisateur. Valide les données, échappe les sorties HTML et utilise des tests pour verrouiller les comportements.'],
                ['Projet final PHP', 'Construire un petit module métier complet.', 'Assembler PHP, HTTP, données et sécurité.', 'Projet conseillé : mini API de gestion de tâches avec CRUD, validation, authentification et tests.'],
            ],
            'code' => ['<?php\n$name = "DevRoad";\necho $name;', 'function add(int $a, int $b): int { return $a + $b; }', 'class User { public function __construct(public string $name) {} }', 'composer require vlucas/phpdotenv', 'header("Content-Type: application/json");\necho json_encode(["ok" => true]);', 'filter_input(INPUT_POST, "email", FILTER_VALIDATE_EMAIL);', 'app/\n├── src/\n├── public/\n├── tests/\n└── composer.json'],
        ],
        'html' => [
            'title' => 'HTML',
            'description' => 'Parcours HTML moderne orienté structure sémantique, accessibilité et intégration web.',
            'lessons' => [
                ['Comprendre HTML', 'Découvrir document, éléments, attributs et structure.', 'Savoir construire une page HTML valide.', 'HTML décrit la structure et la sémantique d’un document. Les balises donnent du sens au contenu.'],
                ['Sémantique', 'Utiliser header, nav, main, section, article et footer.', 'Construire une structure accessible et compréhensible.', 'Les éléments sémantiques rendent la structure explicite pour les navigateurs, moteurs de recherche et technologies d’assistance.'],
                ['Texte, liens et médias', 'Structurer titres, paragraphes, liens, images et vidéos.', 'Présenter correctement du contenu riche.', 'Les attributs alt, href et les titres doivent être utilisés selon leur rôle. Les médias doivent rester accessibles.'],
                ['Formulaires', 'Créer des champs et contraintes de validation natives.', 'Construire des formulaires utilisables.', 'label, name, type, required et les autres attributs HTML permettent de créer des formulaires compréhensibles et validables.'],
                ['Accessibilité', 'Comprendre labels, clavier, focus et alternatives textuelles.', 'Produire une interface utilisable par davantage de personnes.', 'L’accessibilité commence par une structure sémantique correcte et des contrôles natifs correctement nommés.'],
                ['SEO et structure', 'Comprendre title, meta, headings et données structurées.', 'Donner une structure exploitable aux moteurs et aux utilisateurs.', 'Un document bien structuré facilite la compréhension du contenu et son indexation.'],
                ['Projet final HTML', 'Construire une page complète sans framework.', 'Assembler sémantique, formulaire, médias et accessibilité.', 'Projet conseillé : page de présentation responsive avec navigation, sections, galerie et formulaire de contact.'],
            ],
            'code' => ['<!doctype html>\n<html lang="fr">\n<head><title>DevRoad</title></head>\n<body></body>\n</html>', '<main><h1>Apprendre le web</h1></main>', '<a href="/roadmaps">Voir les roadmaps</a>', '<label for="email">Email</label>\n<input id="email" name="email" type="email" required>', '<img src="/assets/laravel.png" alt="Logo Laravel">', '<meta name="description" content="Cours de développement web">', '<header>...</header>\n<nav>...</nav>\n<main>...</main>\n<footer>...</footer>'],
        ],
        'css' => [
            'title' => 'CSS',
            'description' => 'Parcours CSS moderne : cascade, layout, responsive design, animations et architecture.',
            'lessons' => [
                ['Comprendre la cascade', 'Sélecteurs, héritage, spécificité et box model.', 'Comprendre pourquoi une règle CSS gagne sur une autre.', 'CSS applique des règles selon cascade, spécificité et ordre. Le box model définit contenu, padding, border et margin.'],
                ['Flexbox', 'Construire des layouts en une dimension.', 'Aligner proprement les éléments.', 'Flexbox permet de contrôler direction, alignement, espace et dimensionnement des éléments dans une ligne ou une colonne.'],
                ['Grid', 'Construire des layouts en deux dimensions.', 'Créer des interfaces complexes sans hacks.', 'CSS Grid permet de définir lignes, colonnes, zones et espacements pour organiser une interface.'],
                ['Responsive design', 'Media queries, unités fluides et mobile-first.', 'Adapter une interface aux différentes tailles d’écran.', 'Un design responsive part généralement d’une structure flexible puis ajoute des contraintes selon les besoins des écrans.'],
                ['États et animations', 'Pseudo-classes, transitions et keyframes.', 'Créer des interactions visuelles maîtrisées.', 'Les transitions servent aux changements d’état et les keyframes aux animations plus complexes.'],
                ['Architecture CSS', 'Organiser les styles et limiter les effets de bord.', 'Maintenir une base CSS évolutive.', 'Choisis une convention, limite les sélecteurs trop spécifiques et centralise les tokens récurrents.'],
                ['Projet final CSS', 'Construire une interface complète responsive.', 'Combiner layout, composants, états et responsive.', 'Projet conseillé : dashboard sombre avec sidebar desktop, navigation mobile, cartes, tableaux et formulaires.'],
            ],
            'code' => ['.card { padding: 1rem; border-radius: 1rem; }', '.row { display: flex; align-items: center; gap: 1rem; }', '.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1rem; }', '@media (max-width: 768px) { .grid { grid-template-columns: 1fr; } }', '.button:hover { transform: translateY(-2px); }', ':root { --accent: #ff6a00; }', '.layout { min-height: 100vh; display: grid; grid-template-columns: 260px 1fr; }'],
        ],
        'tailwind' => [
            'title' => 'Tailwind CSS',
            'description' => 'Parcours Tailwind CSS pour construire des interfaces modernes, cohérentes et responsive.',
            'lessons' => [
                ['Découvrir Tailwind', 'Comprendre les utility classes et le modèle de composition.', 'Savoir construire une interface sans écrire une feuille CSS pour chaque composant.', 'Tailwind fournit des classes utilitaires composables pour le spacing, les couleurs, la typographie, les layouts et les états.'],
                ['Layout et spacing', 'Maîtriser flex, grid, gap, padding et margin.', 'Construire des layouts propres.', 'Les utilitaires de layout permettent d’exprimer directement la structure de l’interface dans le markup.'],
                ['Responsive design', 'Utiliser les breakpoints et le mobile-first.', 'Adapter les composants à tous les écrans.', 'Les variantes sm, md, lg et xl appliquent des styles à partir d’un breakpoint.'],
                ['Composants et états', 'Créer boutons, cartes, formulaires et états hover/focus.', 'Construire un design system cohérent.', 'Combine les utilitaires avec des composants réutilisables plutôt que de copier de longues chaînes partout.'],
                ['Dark mode et tokens', 'Configurer couleurs, typographie et variantes.', 'Créer une identité visuelle durable.', 'Les tokens permettent de centraliser les valeurs de design et de faire évoluer le thème sans réécrire les composants.'],
                ['Architecture Tailwind', 'Éviter les classes incohérentes et factoriser correctement.', 'Garder un projet maintenable.', 'Les composants UI, variants et tokens sont préférables à une accumulation de styles arbitraires.'],
                ['Projet final Tailwind', 'Construire un dashboard complet.', 'Mettre en pratique layout, responsive, composants et thème.', 'Projet conseillé : dashboard SaaS dark avec sidebar, cartes statistiques, tableau, formulaires et navigation mobile.'],
            ],
            'code' => ['<button className="rounded-xl bg-orange-500 px-4 py-2 font-semibold text-white">Continuer</button>', '<div className="flex items-center gap-4">...</div>', '<div className="grid gap-4 md:grid-cols-2 xl:grid-cols-3">...</div>', '<div className="hidden md:block">Sidebar</div>', '<button className="bg-slate-900 hover:bg-slate-800 focus:ring-2">...</button>', '<div className="bg-[#0D1725] text-white">...</div>', 'components/\n├── Button.jsx\n├── Card.jsx\n├── Input.jsx\n└── Navigation.jsx'],
        ],
        'node' => [
            'title' => 'Node.js',
            'description' => 'Parcours Node.js pour construire des services, APIs et outils JavaScript côté serveur.',
            'lessons' => [
                ['Découvrir Node.js', 'Comprendre runtime, event loop et modules.', 'Comprendre ce qui différencie Node.js du JavaScript navigateur.', 'Node.js exécute JavaScript avec V8 et fournit des APIs système pour fichiers, réseau, processus et HTTP.'],
                ['NPM et modules', 'Gérer dépendances et modules ES.', 'Structurer un projet Node.js moderne.', 'package.json décrit le projet et ses dépendances. Les modules permettent de séparer le code en responsabilités claires.'],
                ['HTTP et APIs', 'Créer un serveur et comprendre les requêtes HTTP.', 'Construire un endpoint simple.', 'Node.js fournit le module http. Les frameworks comme Express ou Fastify peuvent ensuite simplifier le routing et les middlewares.'],
                ['Asynchrone', 'Maîtriser Promise, async/await et l’event loop.', 'Éviter les blocages et gérer correctement les opérations I/O.', 'Les opérations réseau et fichiers sont généralement asynchrones. Évite les traitements bloquants sur le chemin critique.'],
                ['Fichiers, variables et configuration', 'Lire des fichiers et gérer la configuration.', 'Construire un service configurable.', 'Utilise process.env pour la configuration et les APIs fs/promises pour les opérations de fichiers asynchrones.'],
                ['Sécurité et tests', 'Validation, erreurs, logs et tests.', 'Rendre une API Node.js fiable.', 'Valide les entrées, centralise la gestion des erreurs et teste les comportements critiques.'],
                ['Projet final Node.js', 'Construire une API REST.', 'Assembler routing, données, validation et tests.', 'Projet conseillé : API de tâches avec CRUD, pagination, validation, authentification et tests HTTP.'],
            ],
            'code' => ['console.log("Hello Node.js");', 'npm init -y\nnpm install express', 'import http from "node:http";\nhttp.createServer((req, res) => res.end("OK")).listen(3000);', 'const data = await fs.readFile("data.json", "utf8");', 'const port = process.env.PORT ?? 3000;', 'app.use((error, req, res, next) => res.status(500).json({ error: error.message }));', 'src/\n├── routes/\n├── services/\n├── repositories/\n└── server.js'],
        ],
        'git' => [
            'title' => 'Git',
            'description' => 'Parcours Git de la gestion locale des versions jusqu’aux workflows collaboratifs.',
            'lessons' => [
                ['Comprendre Git', 'Repository, working tree, index et commits.', 'Comprendre le modèle de versionnement distribué.', 'Git enregistre des snapshots du projet. Le working tree contient les modifications et l’index prépare le prochain commit.'],
                ['Commits et historique', 'Créer des commits propres et lire l’historique.', 'Pouvoir retrouver et comprendre les changements.', 'Un commit doit représenter une unité logique de changement. git log et git show permettent d’inspecter l’historique.'],
                ['Branches et merge', 'Créer des branches et fusionner du travail.', 'Développer plusieurs fonctionnalités sans mélanger les changements.', 'Les branches sont des pointeurs légers vers des commits. merge rassemble deux historiques compatibles.'],
                ['Rebase et conflits', 'Comprendre rebase et résoudre les conflits.', 'Garder un historique propre sans perdre de travail.', 'Un conflit doit être résolu en comprenant les deux versions avant de continuer l’opération Git.'],
                ['Remote et collaboration', 'push, pull, fetch et tracking branches.', 'Synchroniser un projet avec un dépôt distant.', 'fetch récupère les références distantes. pull combine récupération et intégration selon la configuration.'],
                ['Workflow professionnel', 'Branches de fonctionnalités, messages et revues.', 'Adopter un workflow reproductible.', 'Utilise des branches courtes, des commits lisibles et des pull requests pour faire relire les changements.'],
                ['Projet final Git', 'Simuler un projet collaboratif.', 'Mettre en pratique branches, merge, conflit et remote.', 'Crée une fonctionnalité sur une branche, ouvre une PR, résous un conflit puis fusionne le travail proprement.'],
            ],
            'code' => ['git init\ngit status', 'git add .\ngit commit -m "feat: add roadmap"', 'git switch -c feature/course', 'git fetch origin\ngit rebase origin/main', 'git remote add origin <url>\ngit push -u origin main', 'git log --oneline --graph --decorate', 'git switch main\ngit merge feature/course'],
        ],
        'github' => [
            'title' => 'GitHub',
            'description' => 'Parcours GitHub pour collaborer, gérer du code et automatiser les workflows.',
            'lessons' => [
                ['Découvrir GitHub', 'Repositories, issues, permissions et README.', 'Comprendre GitHub au-delà du simple hébergement Git.', 'GitHub ajoute une couche collaborative au contrôle de version : discussions, issues, pull requests, projets et automatisation.'],
                ['Repositories et branches', 'Configurer un dépôt et organiser les branches.', 'Mettre en place un dépôt propre.', 'Un repository doit disposer d’un README clair, de règles de contribution et d’une stratégie de branches adaptée à l’équipe.'],
                ['Pull Requests', 'Créer, relire et fusionner une PR.', 'Comprendre le cycle de revue de code.', 'Une PR présente un changement isolé. Les reviewers vérifient comportement, qualité et risques avant fusion.'],
                ['Issues et projet', 'Suivre bugs, tâches et fonctionnalités.', 'Transformer les besoins en travail traçable.', 'Les issues décrivent le problème ou le besoin. Les labels, milestones et Projects facilitent le suivi.'],
                ['GitHub Actions', 'Comprendre workflows, jobs et steps.', 'Automatiser tests et contrôles.', 'Actions exécute des workflows déclenchés par des événements GitHub. Chaque job peut installer les dépendances, tester et publier des résultats.'],
                ['Sécurité du dépôt', 'Secrets, permissions et dépendances.', 'Réduire les risques liés au code et aux credentials.', 'Les secrets ne doivent jamais être commités. Utilise les secrets GitHub, permissions minimales et alertes de dépendances.'],
                ['Projet final GitHub', 'Mettre en place un workflow complet.', 'Combiner repository, PR, issues et CI.', 'Crée un repository, définis des issues, développe via PR et ajoute une Action qui lance les tests à chaque changement.'],
            ],
            'code' => ['git clone https://github.com/user/project.git', 'gh issue create --title "Ajouter la recherche"', 'gh pr create --title "feat: add search"', 'name: Tests\non: [push, pull_request]\njobs: {}', 'permissions:\n  contents: read', 'git push -u origin feature/search', 'Repository\n├── README.md\n├── .github/workflows/\n└── src/'],
        ],
        'docker' => [
            'title' => 'Docker',
            'description' => 'Parcours Docker pour conteneuriser, développer et déployer des applications reproductibles.',
            'lessons' => [
                ['Comprendre les conteneurs', 'Images, conteneurs, registry et isolation.', 'Comprendre le modèle Docker.', 'Une image est un artefact immuable servant à créer des conteneurs. Un conteneur est une instance en exécution de cette image.'],
                ['Dockerfile', 'Construire une image personnalisée.', 'Savoir empaqueter une application.', 'Un Dockerfile décrit les étapes de construction d’une image : base, dépendances, fichiers, commande et port exposé.'],
                ['Volumes et réseaux', 'Persister des données et connecter des services.', 'Faire communiquer des conteneurs proprement.', 'Les volumes vivent indépendamment du cycle de vie du conteneur. Les réseaux Docker permettent aux services de se joindre par nom.'],
                ['Docker Compose', 'Orchestrer plusieurs services localement.', 'Lancer une stack complète avec une seule commande.', 'Compose décrit services, réseaux, volumes et variables dans un fichier compose.yaml.'],
                ['Optimiser les images', 'Cache, multi-stage builds et sécurité.', 'Produire des images plus petites et reproductibles.', 'Les builds multi-stage séparent environnement de compilation et runtime. Évite d’embarquer des secrets dans les images.'],
                ['Docker en développement', 'Hot reload, variables et dépendances.', 'Utiliser Docker sans dégrader l’expérience développeur.', 'Monte uniquement les répertoires nécessaires, utilise des volumes adaptés et sépare les configurations locales des images de production.'],
                ['Projet final Docker', 'Conteneuriser une application multi-services.', 'Assembler application, base et réseau.', 'Projet conseillé : application web + base PostgreSQL + reverse proxy avec Compose, volumes persistants et variables d’environnement.'],
            ],
            'code' => ['docker run --rm hello-world', 'FROM node:22-alpine\nWORKDIR /app\nCOPY package*.json ./\nRUN npm ci\nCOPY . .\nCMD ["npm", "run", "dev"]', 'docker build -t devroad-app .', 'docker volume create devroad-data', 'docker network create devroad-net', 'docker compose up -d', 'services:\n  app:\n    build: .\n  db:\n    image: postgres:16'],
        ],
        'mysql' => [
            'title' => 'MySQL',
            'description' => 'Parcours MySQL pour concevoir, interroger et maintenir des bases relationnelles.',
            'lessons' => [
                ['Comprendre le modèle relationnel', 'Tables, lignes, colonnes, clés et relations.', 'Modéliser correctement les données.', 'Une base relationnelle organise les données en tables liées par des clés. Une bonne modélisation réduit les incohérences.'],
                ['SQL de base', 'SELECT, INSERT, UPDATE et DELETE.', 'Écrire les opérations CRUD essentielles.', 'SQL décrit ce que tu veux obtenir plutôt que chaque étape algorithmique de calcul.'],
                ['Jointures et agrégations', 'JOIN, GROUP BY, HAVING et fonctions d’agrégation.', 'Interroger plusieurs tables efficacement.', 'Les jointures relient les tables selon une condition. Les agrégations permettent de produire des indicateurs.'],
                ['Schéma et contraintes', 'PRIMARY KEY, FOREIGN KEY, UNIQUE et CHECK.', 'Garantir l’intégrité des données.', 'Les contraintes déplacent certaines garanties au niveau de la base et protègent les données même lorsque plusieurs clients écrivent.'],
                ['Index et transactions', 'Comprendre index, transactions et isolation.', 'Éviter les requêtes lentes et les écritures incohérentes.', 'Les index accélèrent certaines recherches mais ont un coût. Les transactions regroupent plusieurs opérations atomiques.'],
                ['Sécurité et sauvegardes', 'Utilisateurs, permissions et backups.', 'Administrer une base sans exposer les données.', 'Accorde le minimum de privilèges nécessaire et automatise les sauvegardes vérifiées.'],
                ['Projet final MySQL', 'Construire le schéma d’une application.', 'Mettre en pratique modélisation, SQL et intégrité.', 'Projet conseillé : base d’une application de livraison avec utilisateurs, commandes, produits, statuts et historiques.'],
            ],
            'code' => ['CREATE DATABASE devroad;', 'CREATE TABLE users (id BIGINT PRIMARY KEY, name VARCHAR(255) NOT NULL);', 'SELECT * FROM users WHERE id = 1;', 'SELECT users.name, orders.total FROM users JOIN orders ON orders.user_id = users.id;', 'CREATE INDEX idx_orders_user_id ON orders(user_id);', 'START TRANSACTION;\nUPDATE accounts SET balance = balance - 100 WHERE id = 1;\nCOMMIT;', 'users\norders\norder_items\nproducts'],
        ],
        'postgresql' => [
            'title' => 'PostgreSQL',
            'description' => 'Parcours PostgreSQL pour maîtriser SQL, modélisation, performance et fonctionnalités avancées.',
            'lessons' => [
                ['Découvrir PostgreSQL', 'Comprendre serveur, bases, schémas et tables.', 'Installer et utiliser PostgreSQL correctement.', 'PostgreSQL est un SGBD relationnel avancé qui fournit SQL, transactions, contraintes et de nombreuses extensions.'],
                ['SQL et CRUD', 'Manipuler les données avec SELECT, INSERT, UPDATE et DELETE.', 'Construire les requêtes essentielles.', 'Les opérations CRUD sont la base de toute application utilisant une base relationnelle.'],
                ['Relations et contraintes', 'Clés, foreign keys, joins et intégrité.', 'Construire un modèle de données fiable.', 'Les contraintes protègent les invariants et les jointures permettent de reconstruire les vues métier à partir de plusieurs tables.'],
                ['Fonctionnalités PostgreSQL', 'JSONB, arrays et types spécialisés.', 'Choisir correctement les fonctionnalités avancées.', 'PostgreSQL peut stocker des structures JSONB et proposer des opérateurs spécialisés tout en conservant le modèle relationnel.'],
                ['Index et transactions', 'B-tree, transactions et concurrence.', 'Améliorer performances et cohérence.', 'Un index doit répondre à un besoin de requête. Les transactions et niveaux d’isolation permettent de contrôler la concurrence.'],
                ['Administration', 'Rôles, permissions, migrations et backups.', 'Exploiter PostgreSQL dans un projet réel.', 'Sépare les rôles, automatise les migrations et teste les restaurations de sauvegarde.'],
                ['Projet final PostgreSQL', 'Concevoir une base métier complète.', 'Combiner SQL, contraintes, index et transactions.', 'Projet conseillé : backend d’une marketplace avec utilisateurs, annonces, commandes, paiements et historique.'],
            ],
            'code' => ['CREATE DATABASE devroad;', 'CREATE TABLE users (id BIGSERIAL PRIMARY KEY, name TEXT NOT NULL);', 'SELECT * FROM users WHERE id = 1;', 'SELECT u.name, o.total FROM users u JOIN orders o ON o.user_id = u.id;', 'CREATE INDEX idx_orders_created_at ON orders(created_at);', 'BEGIN;\nUPDATE accounts SET balance = balance - 100 WHERE id = 1;\nCOMMIT;', 'CREATE TABLE events (payload JSONB NOT NULL);'],
        ],
];
