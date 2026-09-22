<?php

return {
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
    }
};
