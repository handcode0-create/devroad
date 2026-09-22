<?php

return json_decode(<<<'JSON'
{
    "laravel": {
        "sources": [
            [
                "Laravel Documentation",
                "https://laravel.com/docs",
                "Documentation officielle"
            ],
            [
                "Laravel Routing",
                "https://laravel.com/docs/routing",
                "Documentation officielle"
            ],
            [
                "Laravel Eloquent",
                "https://laravel.com/docs/eloquent",
                "Documentation officielle"
            ],
            [
                "Laravel Validation",
                "https://laravel.com/docs/validation",
                "Documentation officielle"
            ],
            [
                "Laravel Authorization",
                "https://laravel.com/docs/authorization",
                "Documentation officielle"
            ]
        ],
        "lessons": {
            "Découvrir Laravel": {
                "description": "Comprendre Laravel comme framework HTTP et apprendre le chemin complet d’une requête.",
                "objective": "Expliquer le rôle du framework, du routeur, des middleware, du contrôleur, du modèle et de la réponse.",
                "content": "## Laravel : le framework derrière l’application\n\nLaravel est un framework PHP orienté applications web. Son intérêt n’est pas seulement de fournir des fonctions pratiques : il impose une organisation cohérente autour des requêtes HTTP, de la configuration, des données, de la validation, de l’authentification et des tests.\n\n## Le trajet d’une requête\n\nUne requête arrive sur l’application, traverse le middleware approprié, est associée à une route puis dirigée vers une action. Cette action peut charger des données avec Eloquent et retourner une vue, une réponse JSON ou une page Inertia.\n\n## La bonne manière d’apprendre Laravel\n\nPour chaque fonctionnalité, identifie la donnée à stocker, la route exposée, l’autorisation, la validation, la logique applicative, la réponse et le test qui prouve le comportement.\n\nCette méthode correspond à la façon dont une fonctionnalité réelle de DevRoad est construite.",
                "code_example": "use Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/bonjour', function () {\n    return 'Bonjour DevRoad';\n});"
            },
            "Les routes Laravel": {
                "content": "## Une route est un contrat HTTP\n\nUne route associe une méthode HTTP et une URL à une action. GET lit, POST crée, PUT/PATCH modifie et DELETE supprime.\n\n## Routes nommées\n\nUne route doit généralement avoir un nom stable. Le code applicatif peut ainsi générer une URL sans recopier son chemin partout.\n\n## Routes resource\n\nPour un CRUD, Route::resource() fournit une convention autour de index, create, store, show, edit, update et destroy.\n\n## À éviter\n\nÉvite de placer une logique métier importante directement dans une closure de route. Une route doit rester lisible et déléguer le travail à une action dédiée.",
                "code_example": "use App\\Http\\Controllers\\RoadmapController;\nuse Illuminate\\Support\\Facades\\Route;\n\nRoute::get('/roadmaps', [RoadmapController::class, 'index'])\n    ->name('roadmaps.index');\n\nRoute::resource('roadmaps', RoadmapController::class);"
            },
            "Les Controllers": {
                "content": "## Le contrôleur orchestre\n\nUn contrôleur est une frontière entre HTTP et le reste de l’application. Il reçoit les données validées, appelle les modèles ou services nécessaires et construit la réponse.\n\n## Un contrôleur lisible\n\nUne méthode d’action doit permettre de comprendre rapidement quelle ressource est manipulée, quelle autorisation est appliquée, quelles données sont chargées, quelle action est effectuée et quelle réponse est retournée.\n\nLa validation appartient aux Form Requests. L’autorisation appartient aux Policies. La persistance appartient aux modèles ou à une couche applicative dédiée.\n\n## Avec Inertia\n\nLaravel conserve le contrôle de la requête et choisit une page React. Les données deviennent des props pour l’interface.",
                "code_example": "use Inertia\\Inertia;\n\npublic function index(Request $request)\n{\n    $roadmaps = $request->user()\n        ->roadmaps()\n        ->latest()\n        ->get();\n\n    return Inertia::render('Roadmaps/Index', [\n        'roadmaps' => $roadmaps,\n    ]);\n}"
            },
            "Migrations et schéma de base": {
                "content": "## Une migration versionne le schéma\n\nUne migration est une modification reproductible de la structure de la base. Elle doit pouvoir être exécutée sur une nouvelle installation et comprise plusieurs mois plus tard.\n\nCommence par identifier les entités, leurs relations et leurs contraintes. Une foreign key exprime une relation et peut porter une contrainte d’intégrité.\n\nup() applique l’évolution. down() décrit le retour arrière lorsque celui-ci est possible.\n\nUne migration doit rester petite et ciblée : évite d’y mélanger plusieurs changements métier indépendants.",
                "code_example": "Schema::create('roadmaps', function (Blueprint $table) {\n    $table->id();\n    $table->foreignId('user_id')->constrained()->cascadeOnDelete();\n    $table->string('title');\n    $table->text('description')->nullable();\n    $table->timestamps();\n});"
            },
            "Models et Eloquent": {
                "content": "## Le modèle représente la donnée\n\nUn modèle Eloquent décrit une ressource persistée et ses relations. Il peut définir les attributs autorisés, les casts et les relations.\n\n## Ne mélange pas les couches\n\nModel = données, relations et comportement directement lié à la ressource. Request = validation. Policy = autorisation. Controller ou Action = orchestration HTTP.\n\n## Requêtes lisibles\n\nEloquent permet de construire des requêtes expressives. Commence par une requête simple, puis utilise scopes, relations ou services lorsque la complexité réelle le justifie.",
                "code_example": "class Roadmap extends Model\n{\n    protected $fillable = [\n        'title',\n        'description',\n        'status',\n    ];\n}\n\n// Dans une action :\n$roadmaps = $user->roadmaps()\n    ->where('status', 'active')\n    ->latest()\n    ->get();"
            },
            "Relations Eloquent": {
                "content": "## Les relations décrivent le modèle métier\n\nUne relation Eloquent traduit une relation entre tables. hasMany signifie qu’une ressource possède plusieurs éléments. belongsTo indique qu’un élément appartient à une autre ressource.\n\nUne relation bien définie évite de répéter des jointures et rend les intentions du code visibles.\n\nAttention au problème N+1 : charger une relation dans une boucle peut déclencher une requête par élément. Lorsque les données sont connues à l’avance, utilise l’eager loading avec with().\n\nDans DevRoad, une roadmap possède plusieurs étapes et une étape appartient à une roadmap.",
                "code_example": "class Roadmap extends Model\n{\n    public function steps(): HasMany\n    {\n        return $this->hasMany(RoadmapStep::class)\n            ->orderBy('position');\n    }\n}\n\nclass RoadmapStep extends Model\n{\n    public function roadmap(): BelongsTo\n    {\n        return $this->belongsTo(Roadmap::class);\n    }\n}"
            },
            "Validation avec Form Requests": {
                "content": "## Valider avant de persister\n\nUne validation définit ce que l’application accepte : présence, type, taille, format et contraintes métier simples.\n\nLes Form Requests sortent ces règles du contrôleur. Cela rend les actions plus lisibles et facilite la réutilisation.\n\n## Validation ≠ autorisation\n\nUne donnée peut être valide mais interdite pour l’utilisateur courant. La validation répond à « est-ce acceptable ? ». La Policy répond à « cette personne peut-elle le faire ? ».\n\nLes erreurs doivent être suffisamment précises pour permettre au frontend d’indiquer le champ à corriger sans exposer de détails internes.",
                "code_example": "public function rules(): array\n{\n    return [\n        'title' => ['required', 'string', 'min:3', 'max:255'],\n        'description' => ['nullable', 'string', 'max:5000'],\n    ];\n}"
            },
            "Policies et autorisation": {
                "content": "## Authentification et autorisation\n\nL’authentification identifie l’utilisateur. L’autorisation décide s’il peut effectuer une action sur une ressource.\n\nUne Policy par ressource rend les règles explicites. Pour une roadmap, la règle de base peut être la propriété de la ressource, mais elle peut aussi dépendre d’un rôle, d’un statut ou d’une organisation.\n\nToujours autoriser côté serveur. Masquer un bouton React n’est pas une protection : le serveur doit vérifier chaque action sensible.",
                "code_example": "public function update(User $user, Roadmap $roadmap): bool\n{\n    return $roadmap->user_id === $user->id;\n}\n\n$this->authorize('update', $roadmap);"
            },
            "Inertia et React": {
                "content": "## Le modèle mental Inertia\n\nInertia permet de construire une application avec Laravel et React sans transformer tout le projet en API REST publique.\n\nLaravel reste responsable des routes, de l’autorisation, de la validation et des données. React rend l’interface à partir des props reçues.\n\nUn formulaire React envoie une requête Inertia vers Laravel. Laravel valide, persiste et renvoie la nouvelle représentation de la page.\n\nPour DevRoad, ce modèle permet de partager les règles métier entre roadmaps, cours, mémos et DevLab tout en gardant une interface React riche.",
                "code_example": "import { Head } from '@inertiajs/react';\n\nexport default function Index({ roadmaps }) {\n    return (\n        <>\n            <Head title=\"Roadmaps\" />\n            {roadmaps.map((roadmap) => (\n                <article key={roadmap.id}>\n                    <h2>{roadmap.title}</h2>\n                </article>\n            ))}\n        </>\n    );\n}"
            },
            "Construire un CRUD complet": {
                "content": "## Construire par couches\n\nUn CRUD robuste combine schéma, modèle, relations, validation, autorisation, routes, contrôleur, interface et tests.\n\n### Ordre conseillé\n\n1. définir la donnée ;\n2. créer la migration ;\n3. créer le modèle et les relations ;\n4. créer les Form Requests ;\n5. définir les Policies ;\n6. implémenter les actions ;\n7. déclarer les routes ;\n8. construire l’interface ;\n9. traiter loading, empty et error states ;\n10. tester les parcours autorisés et interdits.\n\nUn CRUD est terminé lorsque les cas nominaux, les erreurs de validation et les accès non autorisés sont tous traités.",
                "code_example": "Route::resource('roadmaps', RoadmapController::class)\n    ->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);"
            }
        }
    },
    "javascript": {
        "sources": [
            [
                "MDN JavaScript Guide",
                "https://developer.mozilla.org/fr/docs/Web/JavaScript/Guide",
                "Guide de référence et d’apprentissage"
            ],
            [
                "MDN JavaScript",
                "https://developer.mozilla.org/fr/docs/Web/JavaScript",
                "Documentation officielle MDN"
            ]
        ],
        "lessons": {
            "Fondamentaux JavaScript": {
                "content": "## JavaScript : le langage, pas seulement le navigateur\n\nJavaScript est un langage dynamique utilisé dans les navigateurs mais aussi dans des runtimes comme Node.js. Avant d’apprendre un framework, il faut comprendre le langage lui-même.\n\nPrivilégie const lorsque la liaison ne doit pas être réassignée et let lorsqu’elle doit évoluer. Évite var dans le code moderne.\n\nTu rencontreras string, number, boolean, null, undefined, bigint, symbol et object. Comprendre les valeurs primitives et les objets évite de nombreuses surprises.\n\nif, switch, boucles et opérateur conditionnel expriment les décisions. Une condition complexe mérite souvent d’être extraite dans une fonction.",
                "code_example": "const score = 14;\nconst passed = score >= 10;\n\nif (passed) {\n    console.log('Réussi');\n} else {\n    console.log('À revoir');\n}"
            },
            "Fonctions et portée": {
                "content": "## Les fonctions sont des valeurs\n\nUne fonction peut être stockée dans une variable, passée en argument et retournée par une autre fonction. Cette propriété est essentielle pour les callbacks, les événements et les méthodes de tableau.\n\nJavaScript utilise une portée lexicale : une fonction interne peut conserver l’accès aux variables de son environnement. C’est le principe d’une closure.\n\nUtilise des paramètres explicites et retourne une valeur lorsqu’une fonction représente un calcul. Une fonction qui modifie plusieurs parties de l’application devient difficile à tester.",
                "code_example": "function createFormatter(prefix) {\n    return function format(value) {\n        return `${prefix}: ${value.trim()}`;\n    };\n}\n\nconst formatTitle = createFormatter('Titre');\nconsole.log(formatTitle('DevRoad'));"
            },
            "Objets, tableaux et méthodes": {
                "content": "## Transformer des données\n\nmap transforme chaque élément, filter conserve certains éléments, find recherche le premier résultat et reduce produit une valeur cumulée.\n\nDans une interface, évite de modifier directement les tableaux ou objets que tu reçois. Crée une nouvelle valeur à partir de l’ancienne.\n\nNe choisis pas reduce parce qu’il est puissant. Utilise la méthode qui décrit le mieux l’intention du code. La lisibilité est plus importante que la densité syntaxique.",
                "code_example": "const courses = [\n    { title: 'JavaScript', active: true },\n    { title: 'PHP', active: false },\n    { title: 'React', active: true },\n];\n\nconst activeTitles = courses\n    .filter(course => course.active)\n    .map(course => course.title);"
            },
            "Asynchrone et Promises": {
                "content": "## Pourquoi l’asynchrone ?\n\nUne requête réseau, une lecture de fichier ou une opération système prend du temps. Le programme doit pouvoir continuer à gérer d’autres tâches pendant cette attente.\n\nUne Promise représente une opération qui sera terminée plus tard. Elle peut être fulfilled ou rejected.\n\nasync/await rend le flux plus lisible, mais une fonction async retourne toujours une Promise. Utilise try/catch pour traiter les erreurs et vérifie les réponses HTTP avant de parser les données.",
                "code_example": "async function loadCourses() {\n    const response = await fetch('/api/courses');\n\n    if (!response.ok) {\n        throw new Error('Impossible de charger les cours');\n    }\n\n    return response.json();\n}\n\nloadCourses().catch(console.error);"
            },
            "Modules et navigateur": {
                "content": "## Organiser le code\n\nUn module ES expose explicitement ce qu’un fichier rend disponible avec export, puis l’importe ailleurs avec import.\n\nDans le navigateur, JavaScript peut manipuler le DOM, écouter les événements et utiliser des APIs comme localStorage.\n\nSépare API, stockage, logique métier et rendu. Cette organisation facilite les tests et prépare le passage vers React.\n\nÉvite les sélecteurs fragiles et pense à la durée de vie des event listeners.",
                "code_example": "// dom.js\nexport function renderTitle(title) {\n    document.querySelector('#app').textContent = title;\n}\n\n// main.js\nimport { renderTitle } from './dom.js';\n\nrenderTitle('DevRoad');"
            },
            "Qualité et debugging": {
                "content": "## Déboguer méthodiquement\n\nReproduis le problème, observe les entrées, vérifie les hypothèses puis isole la cause. Ne modifie pas dix lignes au hasard.\n\nUtilise console, breakpoints, tests et linter. Un linter détecte des problèmes de style ou certaines erreurs avant l’exécution.\n\nÀ la frontière avec une API externe, traite les statuts HTTP et les données inattendues. Lance des erreurs explicites lorsque le contrat d’une fonction ne peut pas être respecté.",
                "code_example": "function parseCourse(payload) {\n    if (!payload || typeof payload.title !== 'string') {\n        throw new TypeError('Course invalide');\n    }\n\n    return { title: payload.title.trim() };\n}"
            }
        }
    },
    "react": {
        "sources": [
            [
                "React Learn",
                "https://react.dev/learn",
                "Documentation officielle React"
            ],
            [
                "Adding Interactivity",
                "https://react.dev/learn/adding-interactivity",
                "Documentation officielle React"
            ],
            [
                "Managing State",
                "https://react.dev/learn/managing-state",
                "Documentation officielle React"
            ]
        ],
        "lessons": {
            "Découvrir React": {
                "content": "## React raisonne en composants\n\nUne interface React est un arbre de composants. Chaque composant reçoit des données, produit du JSX et peut posséder une logique locale.\n\nLe modèle est déclaratif : tu décris l’interface correspondant à l’état courant au lieu de manipuler manuellement le DOM à chaque changement.\n\nLes props viennent du parent. Le state représente une mémoire locale nécessaire lorsque l’interface doit évoluer.\n\nAvant de créer un state, demande-toi si la valeur peut être calculée à partir des props ou d’un autre state. Dupliquer une donnée crée souvent des incohérences.",
                "code_example": "function CourseCard({ title, minutes }) {\n    return (\n        <article>\n            <h2>{title}</h2>\n            <p>{minutes} min</p>\n        </article>\n    );\n}"
            },
            "Créer un projet React": {
                "content": "## Vite et React\n\nUn projet React moderne a besoin d’un outil de développement pour servir les modules, gérer le build et accélérer le feedback. Vite est une option adaptée à une application React indépendante.\n\nVite ne remplace pas React : il fournit l’outillage autour du code.\n\nCommence avec une petite structure : components pour les briques UI, pages ou écrans pour les vues, hooks pour la logique réutilisable et services pour les appels externes. Ne crée pas dix couches avant d’avoir une vraie complexité.",
                "code_example": "npm create vite@latest devroad-react -- --template react\ncd devroad-react\nnpm install\nnpm run dev"
            },
            "Composants et props": {
                "content": "## Une responsabilité claire\n\nUn composant devrait avoir une responsabilité compréhensible. Les props sont des données d’entrée et doivent être considérées comme en lecture seule par le composant enfant.\n\nSi une interaction doit modifier une donnée partagée, la responsabilité de cette donnée doit généralement remonter vers le parent approprié.\n\nPréfère composer plusieurs petits composants plutôt qu’un composant gigantesque. Pour une liste, chaque élément doit recevoir une key stable représentant son identité.",
                "code_example": "function CourseList({ courses }) {\n    return (\n        <ul>\n            {courses.map(course => (\n                <li key={course.id}>{course.title}</li>\n            ))}\n        </ul>\n    );\n}"
            },
            "State et événements": {
                "content": "## Le state représente une mémoire d’interface\n\nuseState conserve une valeur entre les rendus et fournit une fonction pour demander sa mise à jour. Un événement appelle une fonction de ton composant ; la mise à jour du state provoque ensuite un nouveau rendu.\n\nNe mute pas directement les tableaux ou objets du state. Construis une nouvelle valeur.\n\nStocke uniquement ce qui doit réellement être mémorisé. Une valeur dérivable doit être calculée plutôt que dupliquée dans un second state.",
                "code_example": "import { useState } from 'react';\n\nexport default function Counter() {\n    const [count, setCount] = useState(0);\n\n    return (\n        <button onClick={() => setCount(value => value + 1)}>\n            {count}\n        </button>\n    );\n}"
            },
            "Effets et données": {
                "content": "## useEffect sert à synchroniser avec l’extérieur\n\nUn effet sert à synchroniser un composant avec quelque chose d’extérieur à React : abonnement, API navigateur ou système externe.\n\nNe mets pas dans un effet une valeur qui peut être calculée pendant le rendu.\n\nPour les données distantes, définis clairement qui charge les données et comment sont gérés loading, error et empty states. La liste de dépendances décrit les valeurs externes utilisées par l’effet ; ne la traite pas comme une liste à faire taire.",
                "code_example": "import { useEffect, useState } from 'react';\n\nfunction CourseTitle({ courseId }) {\n    const [course, setCourse] = useState(null);\n\n    useEffect(() => {\n        let cancelled = false;\n\n        fetch(`/api/courses/${courseId}`)\n            .then(response => response.json())\n            .then(data => {\n                if (!cancelled) setCourse(data);\n            });\n\n        return () => { cancelled = true; };\n    }, [courseId]);\n\n    return <h1>{course?.title ?? 'Chargement...'}</h1>;\n}"
            },
            "Architecture et performance": {
                "content": "## La performance commence par l’architecture\n\nÉvite d’ajouter de la mémorisation partout avant d’avoir mesuré un problème. Commence par comprendre le flux des données et la taille de l’arbre de rendu.\n\nUn composant peut rendre l’UI, un hook encapsuler une logique réutilisable et un service gérer une API.\n\nPlace le state aussi près que possible de l’endroit qui en a besoin. Quand un problème de performance est réel, mesure-le avec les outils de développement avant d’introduire une optimisation.",
                "code_example": "function CourseFilters({ query, onQueryChange }) {\n    return (\n        <label>\n            Rechercher\n            <input\n                value={query}\n                onChange={event => onQueryChange(event.target.value)}\n            />\n        </label>\n    );\n}"
            }
        }
    },
    "nextjs": {
        "sources": [
            [
                "Next.js Documentation",
                "https://nextjs.org/docs",
                "Documentation officielle Next.js"
            ],
            [
                "Layouts and Pages",
                "https://nextjs.org/learn/dashboard-app/creating-layouts-and-pages",
                "Tutoriel officiel Next.js"
            ]
        ],
        "lessons": {
            "Découvrir Next.js": {
                "content": "## Next.js ajoute une architecture à React\n\nNext.js est un framework React pour construire des applications web full-stack. Il apporte notamment le routing par fichiers, les layouts, le rendu serveur, les Route Handlers et des conventions de production.\n\nLe parcours DevRoad utilise l’App Router. Les fichiers spéciaux comme page.tsx et layout.tsx décrivent la structure de l’application.\n\nReact reste la bibliothèque UI ; Next.js organise l’application autour des routes, du serveur, du client et du chargement des données.",
                "code_example": "export default function Page() {\n    return <h1>Bienvenue sur DevRoad</h1>;\n}"
            },
            "Créer et lancer un projet": {
                "content": "## Créer une application\n\nLe générateur Next.js prépare un projet avec les options de TypeScript, App Router et outillage.\n\nPendant le développement, le serveur local fournit le rendu et recharge les changements. Le build de production est une étape distincte : il vérifie que le projet peut réellement être compilé.\n\nAvant de multiplier les abstractions, comprends app/, public/, les fichiers de configuration et les dépendances.",
                "code_example": "npx create-next-app@latest devroad-next\ncd devroad-next\nnpm run dev"
            },
            "App Router et routing": {
                "content": "## Le routing est basé sur les fichiers\n\nDans l’App Router, un dossier représente un segment d’URL. Un fichier page.tsx rend la page accessible.\n\nLes layouts conservent une structure commune entre plusieurs routes. Ils sont particulièrement utiles pour les dashboards où sidebar et en-tête restent présents pendant la navigation.\n\nUn dossier [id] représente un segment dynamique. Sa valeur devient une donnée exploitable par la page.",
                "code_example": "app/\n├── page.tsx\n├── dashboard/\n│   ├── layout.tsx\n│   └── page.tsx\n└── courses/\n    └── [id]/\n        └── page.tsx"
            },
            "Server et Client Components": {
                "content": "## La frontière serveur/client\n\nDans l’App Router, les composants sont par défaut exécutés côté serveur. Cela permet de préparer l’UI et certaines données sans envoyer toute la logique au navigateur.\n\nAjoute \"use client\" lorsqu’un composant doit utiliser une interaction navigateur ou certaines APIs client.\n\nNe rends pas toute l’application client par réflexe. Garde les composants interactifs petits et place-les au plus près de l’interface qui en a besoin.",
                "code_example": "// Server Component\nexport default async function Page() {\n    const response = await fetch('https://example.com/api/courses');\n    const courses = await response.json();\n\n    return <CourseList courses={courses} />;\n}\n\n// Client Component\n'use client';\n\nimport { useState } from 'react';"
            },
            "Données, formulaires et API": {
                "content": "## Charger les données au bon endroit\n\nUn Server Component peut récupérer des données sans transformer chaque page en composant client.\n\nPour exposer un endpoint HTTP dans l’App Router, utilise un Route Handler dans route.ts.\n\nUn formulaire doit avoir une validation côté serveur même si l’interface possède déjà une validation client. Le serveur est l’autorité finale.\n\nPrévois loading, empty et error states : une application robuste doit gérer les échecs réseau et les données absentes.",
                "code_example": "// app/api/health/route.ts\nexport async function GET() {\n    return Response.json({\n        ok: true,\n        service: 'devroad',\n    });\n}"
            },
            "Performance et bonnes pratiques": {
                "content": "## Performance : réduire le travail inutile\n\nCommence par choisir correctement ce qui doit être exécuté serveur ou client. Ensuite, maîtrise les images, le chargement des données, les métadonnées et les états d’interface.\n\nUtilise les conventions de l’App Router pour les layouts, erreurs et chargements avant d’inventer une architecture parallèle.\n\nTeste toujours un build de production. Une application qui fonctionne uniquement avec le serveur de développement n’a pas encore validé son chemin de livraison.",
                "code_example": "export const metadata = {\n    title: 'DevRoad',\n    description: 'Plateforme d’apprentissage pour développeurs',\n};\n\nexport default function Layout({ children }) {\n    return <main>{children}</main>;\n}"
            }
        }
    },
    "typescript": {
        "sources": [
            [
                "TypeScript Handbook",
                "https://www.typescriptlang.org/docs/handbook/",
                "Documentation officielle TypeScript"
            ],
            [
                "Everyday Types",
                "https://www.typescriptlang.org/docs/handbook/2/everyday-types.html",
                "Documentation officielle TypeScript"
            ],
            [
                "Generics",
                "https://www.typescriptlang.org/docs/handbook/2/generics.html",
                "Documentation officielle TypeScript"
            ]
        ],
        "lessons": {
            "Découvrir TypeScript": {
                "content": "## TypeScript vérifie ton JavaScript avant l’exécution\n\nTypeScript ajoute un système de types statiques à JavaScript. Le compilateur analyse le programme et signale des incohérences avant que le code ne soit exécuté.\n\nLe typage ne valide pas automatiquement les données provenant du réseau, d’un formulaire ou d’un fichier. Une API peut annoncer un objet incorrect et TypeScript ne peut pas le savoir à l’exécution.\n\nLe bénéfice principal apparaît quand le projet grandit : les contrats deviennent explicites, les refactorings sont plus sûrs et l’éditeur fournit de meilleures informations.",
                "code_example": "const title: string = 'DevRoad';\nconst duration: number = 45;\nconst published: boolean = true;"
            },
            "Types primitifs et fonctions": {
                "content": "## Annoter les contrats\n\nLes paramètres et valeurs de retour sont souvent les meilleurs endroits pour commencer. Une fonction bien typée indique immédiatement ce qu’elle accepte et ce qu’elle renvoie.\n\nTu n’as pas besoin d’annoter chaque variable : TypeScript peut souvent déduire le type.\n\nÉvite any. Pour une donnée inconnue venant de l’extérieur, unknown force à vérifier le type avant utilisation.",
                "code_example": "function formatCourse(title: string, minutes: number): string {\n    return `${title} — ${minutes} min`;\n}"
            },
            "Interfaces et types": {
                "content": "## Modéliser les données métier\n\nLes interfaces et alias de types décrivent la forme des objets utilisés par l’application. Le but est de donner des contrats utiles aux frontières du code, pas de rendre chaque ligne complexe.\n\nUne propriété optionnelle exprime une donnée qui peut être absente ; le code doit alors traiter explicitement ce cas.\n\nLes types des réponses API documentent le contrat attendu mais ne remplacent pas une validation runtime lorsqu’une donnée vient d’un système externe.",
                "code_example": "interface Course {\n    id: number;\n    title: string;\n    duration: number;\n    description?: string;\n}\n\nfunction displayCourse(course: Course) {\n    return course.description\n        ? `${course.title}: ${course.description}`\n        : course.title;\n}"
            },
            "Generics et unions": {
                "content": "## Réutiliser sans perdre le type\n\nUn generic permet d’écrire une fonction ou une structure qui fonctionne avec plusieurs types tout en conservant l’information sur le type concret.\n\nLes unions représentent plusieurs possibilités. Elles sont utiles pour modéliser des états comme loading, success et error.\n\nAprès une union, le code doit souvent vérifier quelle branche est présente. TypeScript utilise ces vérifications pour réduire le type disponible dans chaque branche.",
                "code_example": "function first<T>(items: T[]): T | undefined {\n    return items[0];\n}\n\ntype Result =\n    | { status: 'loading' }\n    | { status: 'success'; data: string[] }\n    | { status: 'error'; message: string };"
            },
            "TypeScript avec React": {
                "content": "## Typer les props avant tout\n\nLes props constituent le contrat d’un composant. Commence par les typer clairement, puis laisse l’inférence faire le reste lorsque cela reste lisible.\n\nLes événements React ont des types précis. Utiliser le bon type évite les castings arbitraires.\n\nUne réponse API doit représenter les états possibles : donnée présente, absence, chargement et erreur. Les types doivent refléter le comportement réel de l’interface.",
                "code_example": "type CourseCardProps = {\n    title: string;\n    minutes: number;\n    onOpen: () => void;\n};\n\nexport function CourseCard({\n    title,\n    minutes,\n    onOpen,\n}: CourseCardProps) {\n    return (\n        <button onClick={onOpen}>\n            {title} — {minutes} min\n        </button>\n    );\n}"
            },
            "Configuration et qualité": {
                "content": "## Le compilateur est un outil de qualité\n\ntsconfig.json décrit comment TypeScript analyse le projet. Un mode strict réduit les valeurs implicites et fait remonter plus tôt certaines erreurs.\n\nNe cherche pas à faire taire TypeScript avec des casts partout. Avant un as, demande-toi si tu peux améliorer le type, vérifier la donnée ou écrire une fonction de narrowing.\n\nLe type-check doit faire partie de la CI. Un projet TypeScript sans vérification automatique peut accumuler des erreurs malgré un éditeur correctement configuré.",
                "code_example": "{\n  \"compilerOptions\": {\n    \"strict\": true,\n    \"noEmit\": true\n  }\n}"
            }
        }
    },
    "php": {
        "sources": [
            [
                "PHP Manual",
                "https://www.php.net/manual/fr/",
                "Documentation officielle PHP"
            ],
            [
                "PHP Language Reference",
                "https://www.php.net/manual/fr/langref.php",
                "Référence du langage"
            ]
        ],
        "lessons": {
            "Fondamentaux PHP": {
                "content": "## PHP moderne\n\nPHP est un langage serveur. Une application PHP reçoit une requête, exécute du code côté serveur puis produit une réponse.\n\nUtilise `declare(strict_types=1);` lorsque le projet le permet, des types explicites et des fonctions courtes. Apprends d’abord variables, tableaux, fonctions, conditions et exceptions avant les frameworks.",
                "code_example": "<?php\n\ndeclare(strict_types=1);\n\nfunction greet(string $name): string\n{\n    return \"Bonjour {$name}\";\n}\n\necho greet('DevRoad');"
            },
            "Fonctions et types": {
                "content": "## Des contrats explicites\n\nLes types de paramètres et de retour rendent les fonctions plus faciles à comprendre et à refactorer. Utilise les types adaptés plutôt que de laisser toutes les valeurs implicites.\n\nUne fonction doit avoir une responsabilité claire. Lorsqu’elle devient difficile à nommer ou tester, découpe-la.",
                "code_example": "function calculateAverage(float ...$grades): float\n{\n    return array_sum($grades) / count($grades);\n}"
            },
            "Tableaux et collections": {
                "content": "## Manipuler les données\n\nLes tableaux PHP peuvent représenter des listes ou des structures clé/valeur. `array_map`, `array_filter` et `array_reduce` permettent de transformer des listes, mais une boucle explicite peut être plus lisible lorsque la logique est complexe.\n\nNe mélange pas transformation et effets de bord sans raison.",
                "code_example": "$courses = [\n    ['title' => 'PHP', 'active' => true],\n    ['title' => 'React', 'active' => false],\n];\n\n$active = array_filter($courses, fn (array $course) => $course['active']);"
            },
            "POO et classes": {
                "content": "## Encapsuler un comportement\n\nUne classe regroupe des données et comportements liés. Utilise visibilité, constructeur et méthodes pour expliciter l’état valide d’un objet.\n\nÉvite de transformer chaque tableau en classe sans besoin : l’abstraction doit répondre à un problème réel.",
                "code_example": "final class Course\n{\n    public function __construct(\n        public readonly string $title,\n        public readonly int $minutes,\n    ) {}\n}"
            },
            "Exceptions et erreurs": {
                "content": "## Échouer explicitement\n\nUne exception représente une situation que le code appelant doit traiter. Ne masque pas les erreurs importantes avec des valeurs par défaut silencieuses.\n\nÀ la frontière d’une application, transforme les erreurs techniques en réponses adaptées sans exposer les détails internes.",
                "code_example": "try {\n    $course = loadCourse($id);\n} catch (RuntimeException $exception) {\n    report($exception);\n    throw $exception;\n}"
            },
            "Composer et autoloading": {
                "content": "## Dépendances PHP\n\nComposer gère les dépendances et l’autoloading PSR-4. Le fichier `composer.json` décrit le projet et ses contraintes.\n\nEn production, installe les dépendances avec les options adaptées à l’environnement et ne commit pas le dossier `vendor` dans un projet standard.",
                "code_example": "composer require monolog/monolog\ncomposer dump-autoload"
            },
            "Projet final PHP": {
                "content": "## Projet final\n\nConstruis un petit gestionnaire de cours en PHP orienté objet : création d’un cours, validation du titre, calcul de durée totale et gestion d’une erreur lorsque le cours demandé n’existe pas.\n\nSépare domaine, entrée utilisateur et affichage.",
                "code_example": "final class CourseCatalog\n{\n    /** @var Course[] */\n    private array $courses = [];\n\n    public function add(Course $course): void\n    {\n        $this->courses[] = $course;\n    }\n}"
            }
        }
    },
    "html": {
        "sources": [
            [
                "MDN HTML",
                "https://developer.mozilla.org/fr/docs/Web/HTML",
                "Documentation HTML MDN"
            ],
            [
                "WHATWG HTML",
                "https://html.spec.whatwg.org/",
                "Spécification HTML"
            ]
        ],
        "lessons": {
            "Fondamentaux HTML": {
                "content": "## HTML décrit la structure\n\nHTML donne une structure sémantique au contenu. Utilise les éléments selon leur sens plutôt que pour leur apparence.\n\nUne page accessible commence par une hiérarchie de titres cohérente, des liens explicites et des formulaires correctement associés à leurs labels.",
                "code_example": "<!doctype html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"utf-8\">\n    <meta name=\"viewport\" content=\"width=device-width, initial-scale=1\">\n    <title>DevRoad</title>\n</head>\n<body>\n    <main>\n        <h1>Mes cours</h1>\n    </main>\n</body>\n</html>"
            },
            "Sémantique et accessibilité": {
                "content": "## Le HTML sémantique\n\n`header`, `nav`, `main`, `section`, `article` et `footer` donnent des repères au navigateur et aux technologies d’assistance.\n\nN’utilise pas un `div` cliquable à la place d’un bouton ou d’un lien lorsque l’élément natif existe.",
                "code_example": "<article>\n    <header>\n        <h2>JavaScript</h2>\n    </header>\n    <p>Apprendre les bases du langage.</p>\n    <a href=\"/courses/javascript\">Voir le cours</a>\n</article>"
            },
            "Formulaires HTML": {
                "content": "## Des formulaires robustes\n\nAssocie chaque champ à un `label`, utilise les types HTML appropriés et indique les contraintes simples avec les attributs natifs.\n\nLa validation HTML améliore l’UX mais ne remplace jamais la validation côté serveur.",
                "code_example": "<form method=\"post\">\n    <label for=\"email\">Email</label>\n    <input id=\"email\" name=\"email\" type=\"email\" required>\n    <button type=\"submit\">S’inscrire</button>\n</form>"
            },
            "Images, liens et médias": {
                "content": "## Contenu multimédia\n\nUn lien doit indiquer sa destination. Une image informative doit avoir un texte alternatif utile ; une image purement décorative peut être ignorée par les technologies d’assistance.\n\nUtilise les éléments natifs `img`, `picture`, `video` et `audio` selon le besoin.",
                "code_example": "<a href=\"/roadmaps\">\n    <img src=\"/images/roadmap.webp\" alt=\"Roadmap DevRoad\">\n    Découvrir les roadmaps\n</a>"
            },
            "SEO et métadonnées": {
                "content": "## Décrire une page\n\nLe titre, la description et la structure des contenus aident les utilisateurs et les moteurs à comprendre une page. Utilise une seule information `title` claire et une hiérarchie de titres logique.\n\nLes métadonnées sociales et les données structurées sont des sujets complémentaires : ne remplace pas une bonne structure HTML par des métadonnées.",
                "code_example": "<head>\n    <title>Roadmaps Laravel — DevRoad</title>\n    <meta name=\"description\" content=\"Parcours Laravel pour développeurs.\">\n</head>"
            },
            "Projet final HTML": {
                "content": "## Projet final\n\nConstruis une page de cours complète : navigation, titre, résumé, objectifs, contenu, exemple de code, exercice et formulaire de recherche.\n\nLe rendu doit rester compréhensible sans CSS ni JavaScript.",
                "code_example": "<main>\n    <nav aria-label=\"Fil d’Ariane\">\n        <a href=\"/roadmaps\">Roadmaps</a> / Laravel\n    </nav>\n    <article>\n        <h1>Découvrir Laravel</h1>\n        <p>Comprendre le cycle HTTP.</p>\n    </article>\n</main>"
            }
        }
    },
    "css": {
        "sources": [
            [
                "MDN CSS",
                "https://developer.mozilla.org/fr/docs/Web/CSS",
                "Documentation CSS MDN"
            ],
            [
                "web.dev Learn CSS",
                "https://web.dev/learn/css/",
                "Cours CSS"
            ]
        ],
        "lessons": {
            "Fondamentaux CSS": {
                "content": "## CSS sépare structure et présentation\n\nCSS applique des règles à des éléments HTML. Une règle combine un sélecteur et des déclarations.\n\nCommence par comprendre cascade, héritage, spécificité et box model avant d’accumuler des hacks.",
                "code_example": "body {\n    margin: 0;\n    font-family: system-ui, sans-serif;\n}\n\n.card {\n    padding: 1rem;\n    border: 1px solid #ddd;\n}"
            },
            "Sélecteurs et cascade": {
                "content": "## Pourquoi une règle gagne\n\nLa cascade détermine quelle déclaration s’applique lorsque plusieurs règles ciblent le même élément. La spécificité, l’ordre et l’origine des règles comptent.\n\nÉvite `!important` comme solution par défaut : il masque souvent un problème d’architecture des styles.",
                "code_example": ".card h2 { margin: 0; }\n.card--featured h2 { font-size: 1.5rem; }"
            },
            "Flexbox": {
                "content": "## Disposer une interface en une dimension\n\nFlexbox est adapté aux lignes ou colonnes dont les éléments doivent être distribués et alignés.\n\nComprends `display:flex`, `gap`, `justify-content`, `align-items`, `flex` et le comportement de `flex-wrap`.",
                "code_example": ".toolbar {\n    display: flex;\n    align-items: center;\n    justify-content: space-between;\n    gap: 1rem;\n    flex-wrap: wrap;\n}"
            },
            "Grid et responsive": {
                "content": "## Grille et adaptation\n\nCSS Grid est utile pour les mises en page en deux dimensions. Les media queries adaptent la présentation à la largeur et aux capacités du viewport.\n\nPour DevRoad mobile-first, commence par le petit écran puis ajoute les améliorations pour les écrans plus larges.",
                "code_example": ".courses {\n    display: grid;\n    grid-template-columns: 1fr;\n    gap: 1rem;\n}\n\n@media (min-width: 768px) {\n    .courses { grid-template-columns: repeat(2, 1fr); }\n}"
            },
            "Design system CSS": {
                "content": "## Des tokens plutôt que des valeurs dispersées\n\nCentralise couleurs, espacements, rayons et typographies avec des custom properties. Cela permet de faire évoluer le système visuel sans rechercher des centaines de valeurs dans le code.\n\nLes composants doivent consommer les tokens plutôt que réinventer leurs propres valeurs.",
                "code_example": ":root {\n    --color-bg: #0b0b0b;\n    --color-text: #f5f5f5;\n    --space-4: 1rem;\n    --radius-md: 0.75rem;\n}\n\n.card {\n    padding: var(--space-4);\n    border-radius: var(--radius-md);\n}"
            },
            "Projet final CSS": {
                "content": "## Projet final\n\nConstruis le système visuel d’une page DevRoad : header, sidebar, cartes de cours, boutons, formulaire et états responsive.\n\nVérifie clavier, focus visible, overflow et lisibilité avant de considérer la page terminée.",
                "code_example": ".button:focus-visible {\n    outline: 2px solid currentColor;\n    outline-offset: 3px;\n}"
            }
        }
    },
    "tailwind": {
        "sources": [
            [
                "Tailwind CSS Documentation",
                "https://tailwindcss.com/docs",
                "Documentation officielle Tailwind CSS"
            ]
        ],
        "lessons": {
            "Découvrir Tailwind": {
                "content": "## Tailwind et les utility classes\n\nTailwind fournit des classes utilitaires composables pour écrire l’interface directement dans le markup. Le but n’est pas de supprimer CSS, mais de standardiser les décisions fréquentes.\n\nCommence par spacing, typography, colors, flex et grid avant les fonctionnalités avancées.",
                "code_example": "<article class=\"rounded-xl border border-white/10 bg-black p-6 text-white shadow-lg\">\n    <h2 class=\"text-xl font-semibold\">Laravel</h2>\n    <p class=\"mt-2 text-sm text-white/60\">Parcours complet</p>\n</article>"
            },
            "Responsive design": {
                "content": "## Mobile-first\n\nTailwind applique les classes sans préfixe au petit écran puis utilise des variantes comme `md:` et `lg:` pour les écrans plus larges.\n\nConçois d’abord une interface fonctionnelle sur mobile, puis augmente progressivement la densité.",
                "code_example": "<div class=\"grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3\">\n    <!-- cartes -->\n</div>"
            },
            "États et variantes": {
                "content": "## Des composants avec états lisibles\n\nLes variantes `hover:`, `focus:`, `disabled:`, `dark:` et `group-*` permettent de décrire les états directement dans les classes.\n\nNe sacrifie pas le focus clavier pour une esthétique plus propre : un état focus visible fait partie d’une interface utilisable.",
                "code_example": "<button class=\"rounded-lg bg-white px-4 py-2 text-black transition hover:bg-white/90 focus-visible:outline-2 focus-visible:outline-offset-2 disabled:cursor-not-allowed disabled:opacity-50\">\n    Enregistrer\n</button>"
            },
            "Architecture des composants": {
                "content": "## Éviter les chaînes de classes ingérables\n\nLorsque la même combinaison revient, crée un composant ou une abstraction adaptée au projet. Tailwind ne dispense pas de penser en composants.\n\nLes design tokens et variantes doivent rester cohérents avec le système visuel global de DevRoad.",
                "code_example": "const buttonClass = 'inline-flex items-center rounded-lg px-4 py-2 font-medium transition';"
            },
            "Projet final Tailwind": {
                "content": "## Projet final\n\nConstruis un dashboard responsive avec sidebar, header, statistiques, liste de cours, recherche et états vides.\n\nVérifie que les classes responsive et les états clavier fonctionnent réellement sur plusieurs largeurs.",
                "code_example": "<section class=\"grid gap-4 sm:grid-cols-2 xl:grid-cols-4\">\n    <article class=\"rounded-xl border p-5\">Cours terminés</article>\n    <article class=\"rounded-xl border p-5\">Temps étudié</article>\n</section>"
            }
        }
    },
    "node": {
        "sources": [
            [
                "Node.js Documentation",
                "https://nodejs.org/docs/latest/api/",
                "Documentation officielle Node.js"
            ],
            [
                "Node.js Learn",
                "https://nodejs.org/en/learn",
                "Ressources officielles Node.js"
            ]
        ],
        "lessons": {
            "Découvrir Node.js": {
                "content": "## JavaScript côté serveur\n\nNode.js permet d’exécuter JavaScript hors navigateur avec un runtime basé sur V8. Il fournit des APIs pour fichiers, réseau, processus et événements.\n\nNode n’est pas un framework web : Express et d’autres outils se construisent au-dessus du runtime.",
                "code_example": "console.log(`Node ${process.version}`);"
            },
            "Modules et npm": {
                "content": "## Dépendances et modules\n\nUn projet Node est décrit par `package.json`. Les dépendances doivent être versionnées et installées de manière reproductible.\n\nUtilise les scripts npm pour standardiser les commandes du projet.",
                "code_example": "{\n  \"scripts\": {\n    \"dev\": \"node src/index.js\",\n    \"test\": \"node --test\"\n  }\n}"
            },
            "Serveur HTTP": {
                "content": "## Construire un serveur minimal\n\nNode fournit le module `http` pour créer un serveur sans framework. Comprendre ce niveau permet ensuite de mieux comprendre Express et les frameworks plus haut niveau.\n\nTraite méthode, URL, statut et headers explicitement.",
                "code_example": "import { createServer } from 'node:http';\n\ncreateServer((req, res) => {\n    res.writeHead(200, { 'content-type': 'application/json' });\n    res.end(JSON.stringify({ ok: true }));\n}).listen(3000);"
            },
            "Asynchrone et événements": {
                "content": "## Le modèle événementiel\n\nNode s’appuie fortement sur les opérations asynchrones et les événements. Les APIs modernes retournent souvent des Promises.\n\nÉvite les opérations synchrones longues dans un serveur qui doit rester réactif.",
                "code_example": "import { readFile } from 'node:fs/promises';\n\nconst content = await readFile('README.md', 'utf8');\nconsole.log(content);"
            },
            "API et validation": {
                "content": "## Une API robuste\n\nUne API doit définir ses routes, statuts HTTP, format de réponse et stratégie d’erreur. Valide toujours les données entrantes avant de les utiliser.\n\nNe considère jamais le JSON client comme fiable.",
                "code_example": "function parsePayload(payload) {\n    if (!payload || typeof payload.title !== 'string') {\n        throw new TypeError('title est requis');\n    }\n    return { title: payload.title.trim() };\n}"
            },
            "Tests et production": {
                "content": "## Livrer un service Node\n\nAutomatise les tests, fixe les variables d’environnement nécessaires et expose uniquement les informations utiles dans les logs.\n\nUn service de production doit gérer arrêt propre, erreurs non traitées, santé et observabilité selon son niveau de criticité.",
                "code_example": "process.on('SIGTERM', () => {\n    server.close(() => process.exit(0));\n});"
            },
            "Projet final Node.js": {
                "content": "## Projet final\n\nConstruis une petite API de cours en Node.js : GET pour lister, GET par identifiant, POST avec validation et réponse 404 lorsque le cours n’existe pas.\n\nAjoute au minimum un test de succès et un test d’erreur.",
                "code_example": "const courses = [\n    { id: 1, title: 'JavaScript' },\n];"
            }
        }
    },
    "git": {
        "sources": [
            [
                "Git Documentation",
                "https://git-scm.com/docs",
                "Documentation officielle Git"
            ],
            [
                "Git Book",
                "https://git-scm.com/book/fr/v2",
                "Livre officiel Git"
            ]
        ],
        "lessons": {
            "Comprendre Git": {
                "content": "## Git versionne l’état d’un projet\n\nGit enregistre des snapshots et permet de comparer, restaurer et partager l’historique. Le working tree, l’index et le commit sont trois états à comprendre.\n\nGit n’est pas GitHub : Git est le système de versioning, GitHub est une plateforme de collaboration autour de dépôts Git.",
                "code_example": "git status\ngit diff\ngit log --oneline"
            },
            "Commits et historique": {
                "content": "## Un commit doit raconter un changement\n\nUn bon commit est cohérent et suffisamment petit pour être compris. Le message décrit l’intention du changement.\n\nUtilise `git show`, `git diff` et `git log` pour comprendre l’historique plutôt que de modifier à l’aveugle.",
                "code_example": "git add resources/js/Pages/Steps/Create.jsx\ngit commit -m \"feat(courses): add course creation page\""
            },
            "Branches et fusion": {
                "content": "## Travailler en branches\n\nUne branche isole une ligne de travail. La fusion intègre les commits d’une branche dans une autre.\n\nAvant une fusion, synchronise ton contexte, lis les différences et résous les conflits en comprenant les deux versions.",
                "code_example": "git switch -c feature/course-editor\ngit add .\ngit commit -m \"feat: build course editor\"\ngit switch main\ngit merge feature/course-editor"
            },
            "Rebase et conflits": {
                "content": "## Réécrire avec prudence\n\nLe rebase rejoue des commits sur une autre base. Il peut produire un historique linéaire mais réécrit les identifiants de commit concernés.\n\nNe réécris pas une branche partagée sans coordination. Lors d’un conflit, examine chaque fichier et vérifie le comportement après résolution.",
                "code_example": "git fetch origin\ngit rebase origin/main\n# résoudre les conflits\ngit add <fichier>\ngit rebase --continue"
            },
            "Git propre et sécurisé": {
                "content": "## Ce qui ne doit pas entrer dans Git\n\nSecrets, clés privées, fichiers `.env`, dépendances générées et artefacts locaux ne doivent généralement pas être versionnés.\n\nUn `.gitignore` réduit les erreurs, mais il ne supprime pas un secret déjà commité. Si une clé a été exposée, elle doit être révoquée et remplacée.",
                "code_example": "printf \"\\.env\\n/vendor/\\n/node_modules/\\n\" >> .gitignore\ngit status"
            },
            "Projet final Git": {
                "content": "## Projet final\n\nSimule un workflow d’équipe : crée une branche, effectue deux commits cohérents, synchronise main, résous un conflit volontaire puis fusionne la branche.\n\nTermine par un historique lisible et un working tree propre.",
                "code_example": "git status\ngit log --oneline --decorate --graph --all"
            }
        }
    },
    "github": {
        "sources": [
            [
                "GitHub Docs",
                "https://docs.github.com/fr",
                "Documentation officielle GitHub"
            ],
            [
                "GitHub Actions",
                "https://docs.github.com/fr/actions",
                "Documentation officielle GitHub Actions"
            ]
        ],
        "lessons": {
            "Découvrir GitHub": {
                "content": "## GitHub et Git\n\nGitHub héberge des dépôts Git et ajoute collaboration, pull requests, issues, reviews, Actions et releases.\n\nLe dépôt distant n’est pas une sauvegarde magique : garde une stratégie de branches et protège les branches importantes.",
                "code_example": "git remote -v\ngit push -u origin main"
            },
            "Pull Requests": {
                "content": "## Une PR comme unité de revue\n\nUne pull request décrit un changement, facilite la revue et permet d’automatiser des contrôles avant fusion.\n\nLe titre et la description doivent expliquer pourquoi le changement existe, ce qui a été modifié et comment il a été vérifié.",
                "code_example": "git switch -c feat/course-search\ngit push -u origin feat/course-search"
            },
            "Issues et projet": {
                "content": "## Organiser le travail\n\nLes issues décrivent bugs, tâches ou besoins. Une bonne issue contient contexte, résultat attendu et critères de validation.\n\nDécoupe les travaux importants en unités vérifiables plutôt qu’en tickets vagues.",
                "code_example": "gh issue create --title \"Ajouter la recherche de cours\" --body \"Résultat attendu : recherche filtrée et testée.\""
            },
            "GitHub Actions": {
                "content": "## CI automatisée\n\nGitHub Actions exécute des workflows déclenchés par push, pull request ou événement planifié. Un pipeline peut installer les dépendances, lancer lint, tests, build et migrations de vérification.\n\nUne CI utile échoue rapidement et reproduit les contrôles nécessaires avant livraison.",
                "code_example": "name: CI\non: [push, pull_request]\njobs:\n  test:\n    runs-on: ubuntu-latest\n    steps:\n      - uses: actions/checkout@v4\n      - run: npm ci\n      - run: npm test"
            },
            "Secrets et environnements": {
                "content": "## Ne jamais mettre les secrets dans le dépôt\n\nLes secrets doivent être fournis par l’environnement de CI ou le gestionnaire de secrets. Un fichier de workflow ne doit pas contenir de token privé en clair.\n\nSépare secrets, variables publiques et configuration par environnement.",
                "code_example": "env:\n  API_URL: ${{ vars.API_URL }}\n  API_TOKEN: ${{ secrets.API_TOKEN }}"
            },
            "Projet final GitHub": {
                "content": "## Projet final\n\nCrée un dépôt, ouvre une pull request, ajoute une CI qui installe les dépendances et lance les tests, puis configure une règle empêchant la fusion lorsque la CI échoue.\n\nDocumente le workflow dans le README.",
                "code_example": "# .github/workflows/ci.yml\nname: CI\non: [push, pull_request]"
            }
        }
    },
    "docker": {
        "sources": [
            [
                "Docker Docs",
                "https://docs.docker.com/",
                "Documentation officielle Docker"
            ],
            [
                "Docker Compose",
                "https://docs.docker.com/compose/",
                "Documentation officielle Compose"
            ]
        ],
        "lessons": {
            "Découvrir Docker": {
                "content": "## Conteneuriser un environnement\n\nDocker empaquette une application et ses dépendances dans des images exécutables sous forme de conteneurs.\n\nUne image est un artefact ; un conteneur est une instance en cours d’exécution. Comprendre cette différence évite de traiter le conteneur comme une VM.",
                "code_example": "docker run --rm hello-world"
            },
            "Dockerfile": {
                "content": "## Construire une image\n\nUn Dockerfile décrit les étapes nécessaires pour produire une image. Choisis une image de base adaptée, définis le répertoire de travail, installe les dépendances puis copie le code nécessaire.\n\nN’embarque jamais de secret dans une image.",
                "code_example": "FROM node:22-alpine\nWORKDIR /app\nCOPY package*.json ./\nRUN npm ci\nCOPY . .\nCMD [\"npm\", \"run\", \"dev\"]"
            },
            "Volumes et réseaux": {
                "content": "## Persistance et communication\n\nLe système de fichiers d’un conteneur est éphémère. Un volume permet de conserver des données au-delà du cycle de vie d’un conteneur.\n\nLes réseaux Docker permettent aux services de communiquer par nom plutôt que par IP codée en dur.",
                "code_example": "docker volume create devroad-data\ndocker network create devroad-net"
            },
            "Docker Compose": {
                "content": "## Décrire plusieurs services\n\nCompose permet de déclarer application, base de données, réseau et volumes dans un fichier YAML.\n\nCette approche rend l’environnement local reproductible et explicite les dépendances entre services.",
                "code_example": "services:\n  app:\n    build: .\n  db:\n    image: postgres:16"
            },
            "Optimiser les images": {
                "content": "## Images petites et reproductibles\n\nUtilise des builds multi-stage lorsque la compilation nécessite plus d’outils que l’exécution. Réduis le contexte de build avec `.dockerignore`.\n\nNe lance pas l’application en root si le runtime permet un utilisateur non privilégié.",
                "code_example": "docker build -t devroad-app .\ndocker image ls devroad-app"
            },
            "Projet final Docker": {
                "content": "## Projet final\n\nConteneurise une application web et PostgreSQL avec Compose. Ajoute un volume pour la base, des variables d’environnement et un healthcheck adapté.\n\nVérifie que l’environnement peut être recréé depuis zéro.",
                "code_example": "docker compose up -d\ndocker compose ps\ndocker compose logs --tail=100"
            }
        }
    },
    "mysql": {
        "sources": [
            [
                "MySQL 8.4 Reference Manual",
                "https://dev.mysql.com/doc/refman/8.4/en/",
                "Documentation officielle MySQL"
            ]
        ],
        "lessons": {
            "Comprendre le modèle relationnel": {
                "content": "## Modéliser avant SQL\n\nUne base relationnelle organise les données en tables liées par des clés. Identifie entités, cardinalités et contraintes avant d’écrire les requêtes.\n\nLa normalisation réduit les duplications, mais le modèle doit rester adapté aux besoins de lecture et d’écriture.",
                "code_example": "CREATE TABLE users (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    name VARCHAR(255) NOT NULL\n);"
            },
            "SQL de base": {
                "content": "## CRUD SQL\n\nSELECT lit, INSERT crée, UPDATE modifie et DELETE supprime. Utilise toujours une clause WHERE appropriée pour les UPDATE et DELETE métier.\n\nTeste d’abord un SELECT avec le même filtre lorsque tu modifies des données.",
                "code_example": "SELECT id, name FROM users WHERE id = 1;\n\nUPDATE users\nSET name = 'Ada'\nWHERE id = 1;"
            },
            "Jointures et agrégations": {
                "content": "## Croiser les données\n\nJOIN relie des tables selon une relation. GROUP BY regroupe les lignes avant agrégation avec COUNT, SUM, AVG ou d’autres fonctions.\n\nUne requête doit exprimer clairement le résultat métier recherché.",
                "code_example": "SELECT u.name, COUNT(o.id) AS orders_count\nFROM users u\nLEFT JOIN orders o ON o.user_id = u.id\nGROUP BY u.id, u.name;"
            },
            "Schéma et contraintes": {
                "content": "## Faire respecter les invariants\n\nPRIMARY KEY identifie une ligne, FOREIGN KEY relie les tables, UNIQUE évite les doublons et CHECK peut imposer certaines contraintes.\n\nLes contraintes de base protègent les données même lorsqu’un autre client que ton application écrit dans la base.",
                "code_example": "CREATE TABLE orders (\n    id BIGINT PRIMARY KEY AUTO_INCREMENT,\n    user_id BIGINT NOT NULL,\n    total DECIMAL(10,2) NOT NULL CHECK (total >= 0),\n    FOREIGN KEY (user_id) REFERENCES users(id)\n);"
            },
            "Index et transactions": {
                "content": "## Performance et atomicité\n\nUn index accélère certaines recherches mais ajoute un coût aux écritures et au stockage. Choisis-le à partir des requêtes réelles.\n\nUne transaction regroupe plusieurs opérations afin qu’elles soient validées ou annulées ensemble.",
                "code_example": "START TRANSACTION;\nUPDATE accounts SET balance = balance - 100 WHERE id = 1;\nUPDATE accounts SET balance = balance + 100 WHERE id = 2;\nCOMMIT;"
            },
            "Sécurité et sauvegardes": {
                "content": "## Administration responsable\n\nAccorde le minimum de privilèges nécessaire aux comptes applicatifs. Sépare utilisateurs humains, applications et administration.\n\nUne sauvegarde n’est utile que si sa restauration est testée. Automatise les sauvegardes et documente le processus de restauration.",
                "code_example": "CREATE USER 'devroad_app'@'%' IDENTIFIED BY 'change-me';\nGRANT SELECT, INSERT, UPDATE, DELETE ON devroad.* TO 'devroad_app'@'%';"
            },
            "Projet final MySQL": {
                "content": "## Projet final\n\nConçois la base d’une application de livraison : utilisateurs, adresses, restaurants, commandes, lignes de commande et statuts.\n\nAjoute clés étrangères, index nécessaires et une transaction de création de commande.",
                "code_example": "CREATE INDEX idx_orders_user_status\nON orders(user_id, status);"
            }
        }
    },
    "postgresql": {
        "sources": [
            [
                "PostgreSQL Documentation",
                "https://www.postgresql.org/docs/current/",
                "Documentation officielle PostgreSQL"
            ],
            [
                "PostgreSQL Tutorial",
                "https://www.postgresql.org/docs/current/tutorial.html",
                "Tutoriel officiel"
            ]
        ],
        "lessons": {
            "Découvrir PostgreSQL": {
                "content": "## Un SGBD relationnel complet\n\nPostgreSQL fournit SQL, transactions, contraintes, index, rôles et des fonctionnalités avancées comme JSONB.\n\nComprends d’abord les fondamentaux relationnels avant d’utiliser des fonctionnalités spécifiques au moteur.",
                "code_example": "SELECT version();\nSELECT current_database();"
            },
            "SQL et CRUD": {
                "content": "## Requêtes essentielles\n\nSELECT lit les données, INSERT les crée, UPDATE les modifie et DELETE les supprime. Paramètre toujours les valeurs venant de l’utilisateur dans l’application plutôt que de construire du SQL par concaténation.",
                "code_example": "SELECT id, title FROM courses WHERE status = 'published';"
            },
            "Relations et contraintes": {
                "content": "## Garantir l’intégrité\n\nLes clés primaires et étrangères structurent les relations. NOT NULL, UNIQUE et CHECK peuvent empêcher des états invalides.\n\nLes contraintes doivent exprimer les invariants réellement nécessaires au domaine.",
                "code_example": "CREATE TABLE courses (\n    id BIGSERIAL PRIMARY KEY,\n    title TEXT NOT NULL,\n    roadmap_id BIGINT NOT NULL REFERENCES roadmaps(id) ON DELETE CASCADE\n);"
            },
            "Fonctionnalités PostgreSQL": {
                "content": "## JSONB avec discernement\n\nJSONB permet de stocker des structures semi-structurées et de les interroger avec les opérateurs PostgreSQL.\n\nN’utilise pas JSONB pour remplacer une relation clairement modélisée : choisis la représentation qui correspond au besoin de requête et d’intégrité.",
                "code_example": "CREATE TABLE events (\n    id BIGSERIAL PRIMARY KEY,\n    payload JSONB NOT NULL\n);\n\nSELECT payload->>'type' FROM events;"
            },
            "Index et transactions": {
                "content": "## Mesurer avant d’optimiser\n\nUn index doit répondre à un pattern de requête réel. Utilise EXPLAIN pour comprendre le plan d’exécution.\n\nLes transactions regroupent des opérations atomiques et les niveaux d’isolation déterminent les phénomènes de concurrence acceptés.",
                "code_example": "EXPLAIN ANALYZE\nSELECT * FROM orders WHERE user_id = 42;\n\nBEGIN;\nUPDATE accounts SET balance = balance - 100 WHERE id = 1;\nCOMMIT;"
            },
            "Administration": {
                "content": "## Rôles, migrations et sauvegardes\n\nSépare les rôles PostgreSQL selon leurs responsabilités et applique le principe du moindre privilège.\n\nLes migrations versionnent le schéma. Les sauvegardes doivent être automatisées et les restaurations régulièrement testées.",
                "code_example": "CREATE ROLE devroad_app LOGIN PASSWORD 'change-me';\nGRANT CONNECT ON DATABASE devroad TO devroad_app;"
            },
            "Projet final PostgreSQL": {
                "content": "## Projet final\n\nConçois une base de marketplace avec utilisateurs, annonces, commandes, paiements et historique. Ajoute contraintes, index et une transaction de commande.\n\nDocumente les requêtes importantes et justifie chaque index.",
                "code_example": "CREATE INDEX idx_orders_created_at\nON orders(created_at DESC);"
            }
        }
    }
}
JSON, true);
