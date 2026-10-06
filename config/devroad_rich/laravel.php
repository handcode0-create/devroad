<?php

return [
    'lessons' => [

        'Découvrir Laravel' => [
            'description' => 'Comprendre ce qu\'est Laravel, à quoi il sert et comment une requête HTTP traverse une application web moderne.',
            'objective' => 'Expliquer en tes propres mots le rôle de Laravel et le chemin d\'une requête (route, contrôleur, réponse), puis écrire deux routes qui répondent.',
            'content' => <<<'MD'
## Pourquoi cette notion

Quand une agence d'Abidjan livre une boutique en ligne, une application de réservation ou un outil de gestion scolaire, elle ne repart jamais de zéro. Elle s'appuie sur un framework : un socle de code déjà écrit qui gère les tâches répétitives (recevoir une requête, parler à la base de données, envoyer un e-mail, protéger un formulaire). Laravel est l'un des frameworks PHP les plus utilisés au monde, et celui que tu rencontreras le plus souvent dans les missions web en Afrique francophone.

Comprendre Laravel dès le départ t'évite l'erreur classique du débutant : copier du code sans savoir où il s'exécute. Dans cette formation, tu vas construire DevRoad, une application de roadmaps pour développeurs, avec Laravel, Inertia et React. Chaque leçon ajoute une brique à ce projet.

## Les concepts clés

### Qu'est-ce qu'un framework ?

Un framework impose une organisation. Au lieu de mélanger HTML, SQL et logique dans un même fichier PHP, Laravel te demande de ranger chaque responsabilité à sa place. Le gain est énorme : un autre développeur ouvre ton projet et sait immédiatement où chercher.

### Le modèle MVC

Laravel s'inspire du modèle MVC : Model, View, Controller. Le Model représente les données et parle à la base. Le Controller reçoit la demande, décide quoi faire et prépare la réponse. La View, ou ici la page Inertia, affiche le résultat. Entre l'utilisateur et le contrôleur, il y a le routeur, qui associe une adresse (l'URL) à une action.

### Le cycle d'une requête

Quand un visiteur ouvre une adresse, le serveur web envoie la requête au fichier « public/index.php ». Laravel démarre, passe la requête dans des middlewares (des filtres, par exemple pour vérifier la connexion), puis le routeur cherche la route qui correspond. La route appelle une fonction ou un contrôleur, qui retourne une réponse : du texte, du JSON, une page. Cette réponse repart vers le navigateur.

### Artisan, l'outil en ligne de commande

Laravel est livré avec « artisan », une commande qui génère du code (contrôleurs, modèles, migrations), lance le serveur de développement et exécute les tâches de maintenance. Tu l'utiliseras à chaque leçon.

## Exemple pas à pas

Le fichier d'exemple est « routes/web.php », le point d'entrée des routes web. Dans l'étape 1, on importe la classe « Route », qui permet de déclarer les adresses de l'application. Dans l'étape 2, on déclare la route de la page d'accueil : quand quelqu'un visite « / », la fonction renvoie une vue. L'étape 3 ajoute une route qui retourne simplement du texte, pratique pour tester qu'une adresse répond. L'étape 4 renvoie un tableau PHP : Laravel le convertit automatiquement en JSON, ce qui est la base d'une API. Enfin, l'étape 5 utilise la fonction « config » pour lire le nom de l'application dans la configuration, sans jamais l'écrire en dur.

## Erreurs fréquentes

- Modifier le dossier « vendor » : ces fichiers appartiennent aux bibliothèques et sont écrasés à chaque installation. Écris ton code dans « app », « routes » et « resources ».
- Mélanger la logique et l'affichage dans la route : une route doit rester courte. Dès qu'elle dépasse quelques lignes, déplace le travail dans un contrôleur.
- Oublier que le serveur doit être redémarré après un changement du fichier d'environnement : relance « php artisan serve » ou vide le cache de configuration.
- Confondre « return » et « echo » dans une route : une route doit retourner sa réponse, sinon Laravel affiche une page vide.
- Écrire des valeurs sensibles directement dans le code : utilise le fichier « .env » et la fonction « config ».

## Bonnes pratiques

- Garde les routes lisibles : une ligne par route quand c'est possible.
- Lis la documentation officielle avant de chercher une solution tierce : Laravel fournit déjà beaucoup de choses.
- Utilise artisan plutôt que de créer les fichiers à la main, pour respecter les conventions de nommage.
- Versionne ton projet avec Git dès le premier jour, sans jamais envoyer le fichier « .env ».

## Auto-évaluation

- Que signifie MVC et quel rôle joue chaque lettre ?
- Quel fichier reçoit toutes les requêtes web d'une application Laravel ?
- Quelle est la différence entre une route et un contrôleur ?
- Pourquoi un tableau retourné par une route devient-il du JSON ?
- À quoi sert la commande artisan ?

## À retenir

- Laravel est un framework PHP qui impose une organisation claire du code.
- Une requête passe par les middlewares, le routeur, puis le contrôleur, et ressort en réponse.
- Le fichier « routes/web.php » déclare les adresses de l'application.
- Artisan génère du code et lance les tâches de maintenance.
- Les valeurs de configuration se lisent avec « config » et le fichier d'environnement.
MD,
            'code_example' => <<<'CODE'
<?php

// routes/web.php : point d'entrée des routes web de DevRoad

// Étape 1 : on importe la classe Route pour déclarer nos adresses
use Illuminate\Support\Facades\Route;

// Étape 2 : la page d'accueil renvoie une vue Blade
Route::get('/', function () {
    return view('welcome');
});

// Étape 3 : une route de test qui renvoie du texte brut
Route::get('/bonjour', function () {
    return 'Bienvenue sur DevRoad !';
});

// Étape 4 : un tableau PHP est converti automatiquement en JSON
Route::get('/api/statut', function () {
    return [
        'service' => 'devroad',
        'statut' => 'en ligne',
    ];
});

// Étape 5 : on lit le nom de l'application dans la configuration
// (valeur APP_NAME du fichier .env), jamais en dur dans le code
Route::get('/api/nom', function () {
    return [
        'nom' => config('app.name'),
        'environnement' => config('app.env'),
    ];
});
CODE,
            'estimated_minutes' => 40,
            'exercise_title' => 'Tes deux premières routes',
            'exercise_description' => <<<'MD'
Dans « routes/web.php », ajoute deux routes pour une future boutique : une page texte « À propos » et un petit point d'accès JSON qui décrit le service. Teste-les dans ton navigateur avec « php artisan serve ».

Critères de réussite :
- La route GET « /a-propos » retourne un texte qui contient le nom de la boutique.
- La route GET « /api/ping » retourne du JSON avec une clé « message » valant « pong ».
- La route « /api/ping » retourne aussi une clé « app » lue avec la fonction « config » (pas écrite en dur).
- Aucune route ne contient de « echo » : toutes utilisent « return ».
MD,
            'exercise_hint' => 'Une route Laravel retourne sa réponse avec « return ». Un tableau associatif devient automatiquement du JSON, et config(\'app.name\') donne le nom de l\'application.',
            'exercise_solution' => <<<'CODE'
<?php

use Illuminate\Support\Facades\Route;

// Page texte : on retourne (return) une chaîne, pas d'echo
Route::get('/a-propos', function () {
    return 'Boutique Wara : vêtements et accessoires livrés à Abidjan.';
});

// Point d'accès JSON : le tableau est converti en JSON par Laravel
Route::get('/api/ping', function () {
    return [
        'message' => 'pong',
        // Le nom vient de la configuration (APP_NAME dans .env)
        'app' => config('app.name'),
    ];
});
CODE,
        ],

        'Installer et lancer Laravel' => [
            'description' => 'Installer Laravel avec Composer, configurer le fichier .env et lancer le serveur de développement pour voir ta première page.',
            'objective' => 'Créer un projet Laravel, configurer l\'environnement, lancer le serveur et vérifier qu\'une route de santé répond correctement.',
            'content' => <<<'MD'
## Pourquoi cette notion

Avant d'écrire la moindre fonctionnalité, tu dois avoir un environnement qui tourne sur ta machine. En entreprise, la première journée d'un développeur consiste presque toujours à cloner un projet, installer les dépendances, configurer l'environnement et lancer l'application. Si tu maîtrises ce rituel, tu deviens autonome sur n'importe quel projet Laravel, y compris ceux que tu n'as pas créés.

À Abidjan, beaucoup de développeurs travaillent avec une connexion limitée : savoir ce que fait chaque commande t'évite de relancer des installations lourdes pour rien.

## Les concepts clés

### Prérequis

Laravel a besoin de PHP dans une version récente, de Composer (le gestionnaire de dépendances de PHP), et, pour cette formation, de Node.js et npm, car l'interface React est compilée par un outil de build. Tu peux utiliser XAMPP, Laragon ou une installation native selon ton système. Vérifie toujours les versions minimales dans la documentation officielle plutôt que de te fier à une liste ancienne.

### Composer

Composer lit le fichier « composer.json », télécharge les bibliothèques dans le dossier « vendor » et note les versions exactes dans « composer.lock ». Pour créer un projet, tu utilises la commande « composer create-project » ou l'installateur Laravel. Pour un projet existant, « composer install » suffit.

### Le fichier d'environnement

Le fichier « .env » contient tout ce qui change d'une machine à l'autre : nom de l'application, mode debug, identifiants de base de données, clés secrètes. Il n'est jamais envoyé sur Git. Le fichier « .env.example » sert de modèle : on le copie en « .env », puis on génère la clé de l'application avec « php artisan key:generate ». Sans cette clé, les sessions et le chiffrement ne fonctionnent pas.

### Le serveur de développement

La commande « php artisan serve » démarre un petit serveur local, par défaut sur le port 8000. Il est parfait pour développer, mais il n'est pas fait pour la production, où l'on utilise Nginx ou Apache.

## Exemple pas à pas

L'exemple ajoute une route de santé dans « routes/web.php ». L'étape 1 récupère le nom de l'application et l'environnement courant depuis la configuration. L'étape 2 vérifie que la clé de l'application existe : si elle est vide, on retourne un message clair avec le code HTTP 500 au lieu d'une erreur obscure. L'étape 3 teste la connexion à la base avec un simple appel, dans un bloc « try », pour ne pas casser la page si la base est éteinte. L'étape 4 retourne un JSON résumant l'état : application, environnement, clé présente, base joignable. Tu peux ainsi diagnostiquer une installation en une seule requête.

## Erreurs fréquentes

- Oublier de copier « .env.example » vers « .env » : l'application affiche une erreur de clé. Copie le fichier puis lance « php artisan key:generate ».
- Lancer « composer install » sans l'extension PHP requise : l'installation s'arrête. Lis le message, active l'extension dans « php.ini » puis relance.
- Laisser « APP_DEBUG=true » en production : des informations sensibles s'affichent aux visiteurs. Mets la valeur à « false » sur le serveur.
- Envoyer le fichier « .env » sur Git : tes mots de passe fuient. Vérifie que « .env » figure dans « .gitignore ».
- Modifier « .env » sans redémarrer ni vider le cache de configuration : l'ancienne valeur reste utilisée. Lance « php artisan config:clear ».

## Bonnes pratiques

- Garde « .env.example » à jour pour que ton équipe sache quelles variables sont nécessaires.
- Utilise « config » dans le code et réserve « env » aux fichiers du dossier « config ».
- Écris la procédure d'installation dans un fichier README court et testé.
- Crée une route ou une commande de diagnostic pour vérifier rapidement l'état d'une installation.

## Auto-évaluation

- Quelle commande installe les dépendances d'un projet Laravel existant ?
- Pourquoi le fichier « .env » n'est-il jamais versionné ?
- À quoi sert « php artisan key:generate » ?
- Quelle différence y a-t-il entre « composer.json » et « composer.lock » ?
- Pourquoi « php artisan serve » ne convient-il pas à la production ?

## À retenir

- Composer installe les dépendances PHP, npm celles du front.
- Le fichier « .env » porte la configuration propre à chaque machine et reste secret.
- La clé de l'application se génère une fois avec artisan.
- Le serveur « artisan serve » sert uniquement au développement.
- Une route de diagnostic fait gagner du temps à chaque nouvelle installation.
MD,
            'code_example' => <<<'CODE'
<?php

// routes/web.php : route de santé pour vérifier une installation

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

Route::get('/sante', function () {
    // Étape 1 : informations de base lues dans la configuration
    $application = config('app.name');
    $environnement = config('app.env');

    // Étape 2 : la clé APP_KEY doit exister (php artisan key:generate)
    if (empty(config('app.key'))) {
        return response()->json([
            'erreur' => 'APP_KEY manquante : lance php artisan key:generate',
        ], 500);
    }

    // Étape 3 : test de la base, sans casser la page si elle est éteinte
    $baseJoignable = true;
    try {
        DB::connection()->getPdo();
    } catch (\Throwable $e) {
        $baseJoignable = false;
    }

    // Étape 4 : résumé de l'état de l'installation
    return response()->json([
        'application' => $application,
        'environnement' => $environnement,
        'cle_presente' => true,
        'base_joignable' => $baseJoignable,
    ]);
});
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Route de diagnostic d\'installation',
            'exercise_description' => <<<'MD'
Après avoir installé ton projet et copié « .env.example » vers « .env », crée une route de diagnostic qui aide un collègue à vérifier son installation.

Critères de réussite :
- La route GET « /diagnostic » retourne du JSON.
- Le JSON contient « debug » (booléen lu avec « config('app.debug') ») et « environnement ».
- Le JSON contient « timezone » lue dans la configuration de l'application.
- Le JSON contient « version_php » obtenue avec la constante « PHP_VERSION ».
- Aucune valeur secrète (clé d'application, mot de passe) n'apparaît dans la réponse.
MD,
            'exercise_hint' => 'Utilise config(\'app.debug\'), config(\'app.env\'), config(\'app.timezone\') et la constante PHP_VERSION. Ne retourne jamais config(\'app.key\').',
            'exercise_solution' => <<<'CODE'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/diagnostic', function () {
    // Le tableau devient du JSON. On n'expose aucun secret :
    // ni APP_KEY, ni identifiants de base de données.
    return [
        'debug' => (bool) config('app.debug'),
        'environnement' => config('app.env'),
        'timezone' => config('app.timezone'),
        'version_php' => PHP_VERSION,
    ];
});
CODE,
        ],

        'Comprendre la structure du projet' => [
            'description' => 'Découvrir le rôle de chaque dossier d\'un projet Laravel pour savoir où écrire chaque morceau de code.',
            'objective' => 'Associer chaque type de code (route, contrôleur, modèle, migration, vue, configuration) à son dossier et organiser un petit module sans te tromper.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet Laravel contient des dizaines de dossiers dès l'installation. Le débutant se sent perdu ; le professionnel, lui, sait où aller en quelques secondes, parce que Laravel suit des conventions strictes. Quand tu rejoins une équipe, personne ne t'explique l'organisation : on attend de toi que tu la reconnaisses. Cette leçon te donne la carte du territoire.

Bien ranger son code n'est pas de l'esthétique. Un bug dans un paiement mobile money se corrige plus vite quand tu sais que la logique est dans un contrôleur ou un service précis, et pas éparpillée dans dix fichiers.

## Les concepts clés

### Le dossier app

C'est le cœur de ton application. On y trouve « app/Models » pour les modèles Eloquent, « app/Http/Controllers » pour les contrôleurs, « app/Http/Requests » pour la validation, « app/Policies » pour les règles d'autorisation et « app/Http/Middleware » pour les filtres. Les classes suivent l'espace de noms « App », lié au dossier par Composer : le fichier « app/Models/Roadmap.php » déclare « namespace App\Models ».

### Routes, vues et ressources

Le dossier « routes » contient « web.php » (pages avec session) et « api.php » (API sans état). Le dossier « resources » contient le code front : les vues Blade et, avec Inertia, les pages React dans « resources/js/Pages ». Les fichiers compilés partent dans « public », seul dossier exposé au monde.

### Base de données et configuration

Le dossier « database » contient les migrations (le schéma), les factories et les seeders (données de test). Le dossier « config » regroupe les fichiers de configuration, qui lisent le fichier « .env ». Le dossier « storage » reçoit les journaux, le cache et les fichiers envoyés par les utilisateurs.

### Ce qu'il ne faut pas toucher

Le dossier « vendor » est géré par Composer, « node_modules » par npm, et « bootstrap/cache » par le framework. Tu ne les modifies jamais à la main.

## Exemple pas à pas

L'exemple est un petit script de contrôle, placé dans « routes/web.php », qui vérifie la présence des dossiers attendus. L'étape 1 définit la liste des dossiers essentiels avec leur rôle, sous forme de tableau associatif. L'étape 2 utilise les fonctions d'aide « base_path », « app_path » et « resource_path », qui retournent des chemins absolus fiables, quel que soit le système. L'étape 3 parcourt la liste et indique pour chaque dossier s'il existe, grâce à « is_dir ». L'étape 4 retourne le résultat en JSON. Tu apprends ainsi les chemins en les manipulant plutôt qu'en les récitant.

## Erreurs fréquentes

- Placer la logique métier dans les routes : le fichier devient illisible. Déplace-la dans un contrôleur, ou dans une classe de service.
- Écrire des chemins en dur comme « C:\xampp\... » : le code casse sur une autre machine. Utilise « base_path » et les fonctions similaires.
- Mettre des fichiers sensibles dans « public » : ils deviennent accessibles à tous. Garde-les dans « storage » et sers-les par un contrôleur.
- Créer des classes hors des dossiers conventionnels sans adapter l'espace de noms : la classe n'est pas trouvée. Le namespace doit refléter le chemin.
- Modifier « vendor » pour corriger un bug : tout disparaît à la prochaine installation. Étends la classe ou ouvre un correctif en amont.

## Bonnes pratiques

- Respecte les conventions de nommage : une classe par fichier, nom du fichier identique au nom de la classe.
- Génère les fichiers avec « php artisan make: » pour obtenir le bon emplacement et le bon namespace.
- Garde les contrôleurs minces et déplace le code réutilisable dans « app/Services » ou des actions dédiées.
- Ne publie que le dossier « public » comme racine web du serveur.

## Auto-évaluation

- Dans quel dossier vont les modèles Eloquent et quel est leur namespace ?
- Quelle différence y a-t-il entre « routes/web.php » et « routes/api.php » ?
- Où se trouvent les pages React avec Inertia ?
- Pourquoi « public » est-il le seul dossier exposé au serveur web ?
- Quels dossiers ne dois-tu jamais modifier à la main ?

## À retenir

- Le dossier « app » contient la logique de ton application.
- Les routes se déclarent dans « routes », les vues et pages dans « resources ».
- Les migrations et seeders vivent dans « database ».
- Le namespace d'une classe suit le chemin de son fichier.
- « vendor » et « node_modules » ne se modifient jamais.
MD,
            'code_example' => <<<'CODE'
<?php

// routes/web.php : contrôle de la structure du projet (outil d'apprentissage)

use Illuminate\Support\Facades\Route;

Route::get('/structure', function () {
    // Étape 1 : dossiers essentiels et leur rôle
    $dossiers = [
        'Modèles Eloquent' => app_path('Models'),
        'Contrôleurs' => app_path('Http/Controllers'),
        'Form Requests' => app_path('Http/Requests'),
        'Policies' => app_path('Policies'),
        'Migrations' => database_path('migrations'),
        'Pages React (Inertia)' => resource_path('js/Pages'),
        'Fichiers publics' => public_path(),
        'Journaux et cache' => storage_path(),
    ];

    // Étape 2 : on vérifie l'existence de chaque dossier
    $resultat = [];
    foreach ($dossiers as $role => $chemin) {
        $resultat[] = [
            'role' => $role,
            // base_path() donne la racine ; on l'enlève pour afficher un chemin relatif
            'dossier' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $chemin),
            'existe' => is_dir($chemin),
        ];
    }

    // Étape 3 : réponse JSON
    return $resultat;
});
CODE,
            'estimated_minutes' => 40,
            'exercise_title' => 'Cartographier ton projet',
            'exercise_description' => <<<'MD'
Crée une route qui produit la « carte » de ton projet pour un nouveau développeur arrivant dans l'équipe.

Critères de réussite :
- La route GET « /carte » retourne du JSON.
- Le JSON contient au moins cinq entrées, chacune avec « role » et « dossier ».
- Les chemins sont obtenus avec les fonctions d'aide (« app_path », « database_path », « resource_path », etc.) et jamais écrits en dur.
- Chaque entrée contient « existe », un booléen calculé avec « is_dir ».
MD,
            'exercise_hint' => 'Construis un tableau de rôles vers chemins avec app_path(), database_path(), resource_path(), puis boucle dessus avec foreach et teste is_dir().',
            'exercise_solution' => <<<'CODE'
<?php

use Illuminate\Support\Facades\Route;

Route::get('/carte', function () {
    // Chemins obtenus via les fonctions d'aide, jamais en dur
    $dossiers = [
        'Modèles' => app_path('Models'),
        'Contrôleurs' => app_path('Http/Controllers'),
        'Migrations' => database_path('migrations'),
        'Pages React' => resource_path('js/Pages'),
        'Configuration' => config_path(),
        'Fichiers publics' => public_path(),
    ];

    $carte = [];
    foreach ($dossiers as $role => $chemin) {
        $carte[] = [
            'role' => $role,
            'dossier' => str_replace(base_path() . DIRECTORY_SEPARATOR, '', $chemin),
            'existe' => is_dir($chemin),
        ];
    }

    return $carte;
});
CODE,
        ],

        'Les routes Laravel' => [
            'description' => 'Maîtriser les routes : méthodes HTTP, paramètres, routes nommées, groupes, middlewares et liaison automatique avec les modèles.',
            'objective' => 'Déclarer un jeu de routes propre pour une ressource, avec paramètres contraints, noms, préfixe et middleware d\'authentification.',
            'content' => <<<'MD'
## Pourquoi cette notion

La route est la porte d'entrée de ton application. Chaque bouton, chaque formulaire et chaque appel d'API passe par une route. Dans un vrai projet, comme une application de réservation ou une plateforme de livraison, il y a rapidement plusieurs dizaines de routes : si elles sont mal organisées, personne ne s'y retrouve, et les failles de sécurité s'y glissent facilement (une page d'administration oubliée sans protection, par exemple).

Savoir écrire des routes propres te permet aussi de concevoir des adresses prévisibles, que l'on peut lire à voix haute : « /roadmaps/12/steps » dit clairement ce qu'on demande.

## Les concepts clés

### Méthodes HTTP et intentions

Chaque méthode a un sens. GET lit une ressource sans rien modifier. POST en crée une. PUT ou PATCH la met à jour. DELETE la supprime. Respecter ces conventions rend ton application compatible avec les navigateurs, les caches et les outils de test. Un lien qui supprime des données en GET est dangereux, car un simple robot peut le suivre.

### Paramètres et contraintes

Une adresse peut contenir des parties variables entre accolades, par exemple « /roadmaps/{roadmap} ». La valeur est passée à ta fonction. La méthode « where » restreint le format accepté, par exemple uniquement des chiffres : une adresse invalide donne alors une erreur 404 propre au lieu d'un plantage.

### Liaison implicite avec les modèles

Si le paramètre porte le même nom que la variable typée avec un modèle, Laravel charge lui-même l'enregistrement correspondant, ou répond 404 s'il n'existe pas. C'est la liaison implicite : elle supprime le code répétitif de recherche.

### Noms, groupes et middlewares

Donner un nom à une route avec « name » permet de générer l'adresse avec « route('roadmaps.show', $id) » : si l'URL change, plus rien à corriger ailleurs. Un groupe applique en une fois un préfixe, un middleware ou un nom commun à plusieurs routes. Le middleware « auth » refuse l'accès aux visiteurs non connectés.

### Routes de ressource

La méthode « resource » déclare d'un coup les sept routes classiques (liste, formulaire de création, enregistrement, affichage, formulaire d'édition, mise à jour, suppression) vers un contrôleur.

## Exemple pas à pas

Le fichier d'exemple décrit les routes de DevRoad. L'étape 1 importe le contrôleur et la classe Route. L'étape 2 déclare une page publique d'accueil. L'étape 3 ouvre un groupe protégé par « auth » avec le préfixe « roadmaps » et le préfixe de nom « roadmaps. ». À l'intérieur, l'étape 4 déclare la liste et l'enregistrement, puis l'étape 5 une route d'affichage avec un paramètre contraint aux chiffres et la liaison implicite. L'étape 6 ajoute une suppression en DELETE. Enfin, l'étape 7 utilise « route » pour générer une adresse à partir d'un nom.

## Erreurs fréquentes

- Utiliser GET pour une action qui modifie des données : un lien ou un robot peut déclencher la suppression. Utilise POST, PATCH ou DELETE.
- Oublier le middleware « auth » sur les routes privées : n'importe qui accède aux données. Place-les dans un groupe protégé.
- Déclarer une route fixe après une route à paramètre : « /roadmaps/creer » est capturée par « /roadmaps/{roadmap} ». Mets les routes fixes en premier.
- Écrire les adresses en dur dans les vues : un changement d'URL casse tout. Donne des noms aux routes et utilise « route ».
- Ne pas contraindre les paramètres : une valeur inattendue arrive jusqu'à la base. Ajoute « where » ou « whereNumber ».
- Mettre trop de logique dans la fonction de la route : elle devient difficile à maintenir. Passe par un contrôleur.

## Bonnes pratiques

- Nomme chaque route avec une convention régulière comme « ressource.action ».
- Regroupe les routes qui partagent un préfixe ou un middleware.
- Préfère les pluriels pour les ressources : « roadmaps », « steps ».
- Utilise « php artisan route:list » pour auditer régulièrement ce qui est exposé.

## Auto-évaluation

- Quelle méthode HTTP utiliser pour supprimer une ressource et pourquoi pas GET ?
- Comment Laravel retrouve-t-il un enregistrement avec la liaison implicite ?
- À quoi sert le nom d'une route ?
- Que fait un groupe de routes avec un middleware ?
- Quelle commande liste toutes les routes de l'application ?

## À retenir

- Une route associe une méthode HTTP et une adresse à une action.
- Les paramètres se contraignent avec « where » pour éviter les valeurs invalides.
- La liaison implicite charge le modèle ou répond 404.
- Les routes nommées et les groupes rendent les adresses faciles à maintenir.
- Les routes privées doivent toujours passer par le middleware « auth ».
MD,
            'code_example' => <<<'CODE'
<?php

// routes/web.php : routes de DevRoad

// Étape 1 : imports
use App\Http\Controllers\RoadmapController;
use App\Models\Roadmap;
use Illuminate\Support\Facades\Route;

// Étape 2 : page publique d'accueil
Route::get('/', fn () => view('welcome'))->name('accueil');

// Étape 3 : groupe protégé (utilisateur connecté), préfixe d'URL et de nom
Route::middleware('auth')
    ->prefix('roadmaps')
    ->name('roadmaps.')
    ->group(function () {

        // Étape 4 : liste (GET) et création (POST) ; les routes fixes passent avant
        Route::get('/', [RoadmapController::class, 'index'])->name('index');
        Route::post('/', [RoadmapController::class, 'store'])->name('store');

        // Étape 5 : affichage d'une roadmap. {roadmap} est contraint aux chiffres
        // et Laravel charge le modèle automatiquement (liaison implicite)
        Route::get('/{roadmap}', function (Roadmap $roadmap) {
            return $roadmap;
        })->whereNumber('roadmap')->name('show');

        // Étape 6 : suppression, jamais en GET
        Route::delete('/{roadmap}', [RoadmapController::class, 'destroy'])
            ->whereNumber('roadmap')
            ->name('destroy');
    });

// Étape 7 : génération d'adresse depuis un nom de route
Route::get('/raccourci', function () {
    // Donne /roadmaps/12 ; si l'URL change, ce code reste valide
    return redirect(route('roadmaps.show', ['roadmap' => 12]));
})->middleware('auth');
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Routes d\'une boutique',
            'exercise_description' => <<<'MD'
Écris les routes d'un petit catalogue de produits pour une boutique en ligne. Les routes peuvent renvoyer des textes simples : l'objectif est l'organisation.

Critères de réussite :
- Un groupe avec le préfixe « produits » et le préfixe de nom « produits. » contient toutes les routes du catalogue.
- La liste (GET) et la création (POST) existent, et la route d'affichage « /{produit} » est limitée aux nombres avec « whereNumber ».
- La suppression utilise la méthode DELETE.
- Le groupe entier est protégé par le middleware « auth ».
- Chaque route possède un nom, et l'accueil de la boutique est public.
MD,
            'exercise_hint' => 'Route::middleware(\'auth\')->prefix(\'produits\')->name(\'produits.\')->group(...). Pense à whereNumber(\'produit\') et à name() sur chaque route.',
            'exercise_solution' => <<<'CODE'
<?php

use Illuminate\Support\Facades\Route;

// Accueil public de la boutique
Route::get('/', fn () => 'Bienvenue à la boutique')->name('accueil');

// Catalogue protégé : préfixe d'URL, préfixe de nom, middleware auth
Route::middleware('auth')
    ->prefix('produits')
    ->name('produits.')
    ->group(function () {
        Route::get('/', fn () => 'Liste des produits')->name('index');

        Route::post('/', fn () => 'Produit créé')->name('store');

        // Paramètre limité aux nombres
        Route::get('/{produit}', fn (string $produit) => "Produit numéro $produit")
            ->whereNumber('produit')
            ->name('show');

        // Suppression en DELETE
        Route::delete('/{produit}', fn (string $produit) => "Produit $produit supprimé")
            ->whereNumber('produit')
            ->name('destroy');
    });
CODE,
        ],

        'Les Controllers' => [
            'description' => 'Organiser la logique des requêtes dans des contrôleurs : actions, injection de dépendances et réponses JSON.',
            'objective' => 'Écrire un contrôleur de ressource avec les actions index, store, show et destroy, en utilisant l\'objet Request et les codes HTTP appropriés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dès qu'une route fait plus de quelques lignes, elle devient difficile à lire. Le contrôleur est la classe qui regroupe les actions liées à une même ressource. Dans une application de gestion d'école, un contrôleur « EleveController » rassemble tout ce qui concerne les élèves : liste, création, modification, suppression. Les équipes professionnelles organisent presque tout leur code web de cette manière, ce qui rend les projets lisibles et testables.

Le contrôleur est aussi l'endroit où se rencontrent la requête de l'utilisateur et le reste de ton application : validation, modèles, autorisation, réponse.

## Les concepts clés

### Une action, une responsabilité

Chaque méthode publique d'un contrôleur correspond à une action : « index » liste, « store » enregistre, « show » affiche, « update » modifie, « destroy » supprime. Ces noms sont des conventions partagées par tout l'écosystème Laravel, ce qui permet à la méthode « Route::resource » de les relier automatiquement.

### L'objet Request

Laravel injecte l'objet « Request » dans tes méthodes simplement en le déclarant en paramètre : c'est l'injection de dépendances. Il donne accès aux données envoyées (« input », « string », « integer »), à l'utilisateur connecté (« user ») et aux en-têtes. Tu n'utilises jamais directement les tableaux globaux de PHP.

### Les réponses

Un contrôleur retourne une réponse. « response()->json » produit du JSON avec un code HTTP : 200 pour un succès, 201 pour une création, 204 pour une réponse sans contenu, 404 si la ressource n'existe pas. Choisir le bon code aide les clients de ton API à réagir correctement.

### Contrôleurs minces

Un bon contrôleur ne contient pas toute la logique du métier. Il reçoit, délègue et répond. Les règles complexes vont dans les modèles, des services ou des actions dédiées.

### Génération avec artisan

La commande « php artisan make:controller RoadmapController --resource » crée la classe avec les sept méthodes. Tu supprimes celles dont tu n'as pas besoin.

## Exemple pas à pas

L'exemple est le contrôleur « RoadmapController ». L'étape 1 déclare le namespace et importe le modèle et la requête. L'étape 2, « index », récupère uniquement les roadmaps de l'utilisateur connecté grâce à la relation, triées de la plus récente à la plus ancienne. L'étape 3, « store », crée une roadmap pour l'utilisateur et retourne le code 201. L'étape 4, « show », reçoit le modèle par liaison implicite. L'étape 5, « destroy », supprime l'enregistrement et retourne 204 sans contenu. Remarque que chaque action fait peu de choses et que chaque réponse porte le bon code.

## Erreurs fréquentes

- Mettre du SQL brut dans le contrôleur : le code devient fragile. Passe par les modèles Eloquent.
- Oublier de filtrer par utilisateur : un utilisateur voit les données d'un autre. Passe par « $request->user()->roadmaps() » ou une policy.
- Retourner toujours 200, même en cas d'erreur : le client ne sait pas si l'action a réussi. Utilise 201, 204, 404 ou 422 selon le cas.
- Faire un contrôleur géant avec des dizaines de méthodes : découpe-le par ressource.
- Lire directement « $_POST » : tu perds la protection de Laravel. Utilise l'objet « Request ».
- Créer un enregistrement avec « $request->all() » : un champ non prévu peut être modifié. Choisis les champs ou passe par une validation.

## Bonnes pratiques

- Nomme les contrôleurs au singulier suivi de « Controller », par exemple « RoadmapController ».
- Garde chaque action sous une quinzaine de lignes.
- Type toujours les paramètres pour profiter de l'injection et de la liaison implicite.
- Retourne des codes HTTP cohérents.

## Auto-évaluation

- Quelles sont les sept actions d'un contrôleur de ressource ?
- Qu'est-ce que l'injection de dépendances dans une méthode de contrôleur ?
- Quel code HTTP retourne-t-on après une création réussie ?
- Pourquoi filtrer les données par utilisateur connecté ?
- Que signifie « contrôleur mince » ?

## À retenir

- Un contrôleur regroupe les actions d'une ressource.
- L'objet « Request » est injecté et remplace les tableaux globaux.
- Les codes HTTP font partie de la réponse : choisis-les avec soin.
- Les règles métier complexes ne vivent pas dans le contrôleur.
- Les contrôleurs de ressource suivent des noms d'actions standards.
MD,
            'code_example' => <<<'CODE'
<?php

// app/Http/Controllers/RoadmapController.php

// Étape 1 : namespace et imports
namespace App\Http\Controllers;

use App\Models\Roadmap;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoadmapController extends Controller
{
    // Étape 2 : liste des roadmaps de l'utilisateur connecté uniquement
    public function index(Request $request): JsonResponse
    {
        $roadmaps = $request->user()
            ->roadmaps()
            ->latest()
            ->get();

        return response()->json($roadmaps);
    }

    // Étape 3 : création, code 201 (Created)
    public function store(Request $request): JsonResponse
    {
        $roadmap = $request->user()->roadmaps()->create([
            'title' => $request->string('title')->toString(),
            'description' => $request->string('description')->toString(),
            'status' => 'active',
        ]);

        return response()->json($roadmap, 201);
    }

    // Étape 4 : affichage, le modèle est chargé par liaison implicite
    public function show(Roadmap $roadmap): JsonResponse
    {
        return response()->json($roadmap);
    }

    // Étape 5 : suppression, code 204 (No Content)
    public function destroy(Roadmap $roadmap): JsonResponse
    {
        $roadmap->delete();

        return response()->json(null, 204);
    }
}
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Contrôleur des produits',
            'exercise_description' => <<<'MD'
Écris un « ProduitController » pour un catalogue de boutique. Les produits sont stockés dans un tableau en mémoire pour te concentrer sur la structure du contrôleur.

Critères de réussite :
- La méthode « index » retourne la liste en JSON avec le code 200.
- La méthode « show » reçoit l'identifiant et retourne le produit, ou une erreur 404 si l'identifiant n'existe pas.
- La méthode « store » lit « nom » et « prix » dans la requête et retourne le produit créé avec le code 201.
- Les méthodes déclarent des types de retour et utilisent l'objet « Request », jamais « $_POST ».
MD,
            'exercise_hint' => 'Utilise response()->json($donnees, 201) pour une création, et response()->json([\'message\' => ...], 404) quand l\'identifiant est introuvable. $request->string(\'nom\') et $request->integer(\'prix\') lisent les champs.',
            'exercise_solution' => <<<'CODE'
<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProduitController extends Controller
{
    // Données en mémoire pour l'exercice (prix en FCFA)
    private function produits(): array
    {
        return [
            1 => ['id' => 1, 'nom' => 'Pagne wax', 'prix' => 8500],
            2 => ['id' => 2, 'nom' => 'Sandales cuir', 'prix' => 12000],
        ];
    }

    public function index(): JsonResponse
    {
        return response()->json(array_values($this->produits()), 200);
    }

    public function show(int $id): JsonResponse
    {
        $produits = $this->produits();

        // Identifiant inconnu : 404
        if (! isset($produits[$id])) {
            return response()->json(['message' => 'Produit introuvable'], 404);
        }

        return response()->json($produits[$id], 200);
    }

    public function store(Request $request): JsonResponse
    {
        $produit = [
            'id' => 3,
            'nom' => $request->string('nom')->toString(),
            'prix' => $request->integer('prix'),
        ];

        // 201 : ressource créée
        return response()->json($produit, 201);
    }
}
CODE,
        ],

        'Migrations et schéma de base' => [
            'description' => 'Décrire la structure de la base de données avec des migrations versionnées plutôt qu\'avec du SQL écrit à la main.',
            'objective' => 'Écrire une migration complète avec clé étrangère, contraintes et index, la lancer, puis l\'annuler proprement.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application évolue : on ajoute un champ « prix », on crée une table « commandes », on renomme une colonne. Si chaque développeur modifie la base à la main, les versions divergent et le déploiement devient un cauchemar. Les migrations règlent ce problème : ce sont des fichiers PHP, versionnés avec Git, qui décrivent chaque changement du schéma. En lançant une seule commande, n'importe quelle machine, de ton ordinateur au serveur de production, arrive au même état.

Pour un projet qui manipule des paiements ou des stocks, c'est aussi une question de fiabilité : les contraintes de base de données empêchent des données incohérentes d'entrer.

## Les concepts clés

### Une migration, deux méthodes

Une migration est une classe avec une méthode « up », qui applique le changement, et une méthode « down », qui l'annule. Laravel garde la liste des migrations déjà exécutées dans la table « migrations », ce qui lui permet de ne lancer que les nouvelles.

### Le constructeur de schéma

Dans « up », tu utilises « Schema::create » avec un objet « Blueprint » pour déclarer les colonnes : « id », « string », « text », « unsignedInteger », « boolean », « timestamp », « timestamps ». Les modificateurs comme « nullable », « default » et « unique » précisent leur comportement. Pour de l'argent en FCFA, qui n'a pas de centimes, un entier non signé est un bon choix.

### Clés étrangères et index

La méthode « foreignId('user_id')->constrained()->cascadeOnDelete() » crée la colonne, la clé étrangère vers la table « users » et la suppression en cascade. Un index accélère les recherches sur une colonne souvent filtrée, comme « status ».

### Les commandes essentielles

« php artisan make:migration » crée le fichier. « php artisan migrate » exécute les migrations en attente. « php artisan migrate:rollback » annule le dernier lot. « php artisan migrate:fresh » supprime tout et recrée : jamais en production.

### Ne pas modifier une migration déjà partagée

Une fois qu'une migration a été exécutée ailleurs, tu ne la modifies plus : tu crées une nouvelle migration pour changer la table.

## Exemple pas à pas

L'exemple crée la table « roadmaps ». L'étape 1 déclare la classe anonyme qui étend « Migration ». L'étape 2, dans « up », appelle « Schema::create » avec la table. L'étape 3 définit l'identifiant et la clé étrangère vers l'utilisateur avec suppression en cascade. L'étape 4 ajoute le titre, la description optionnelle et le statut avec une valeur par défaut. L'étape 5 pose un index composé sur l'utilisateur et le statut, utile pour la requête « mes roadmaps actives ». L'étape 6 ajoute les colonnes de dates. Enfin, l'étape 7 écrit « down », qui supprime la table si elle existe.

## Erreurs fréquentes

- Écrire « down » vide : impossible de revenir en arrière. Fais toujours l'opération inverse.
- Modifier une ancienne migration déjà exécutée : les autres environnements ne la rejouent pas. Crée une nouvelle migration.
- Oublier « nullable » sur un champ facultatif : l'insertion échoue. Ajoute « nullable » ou une valeur par défaut.
- Lancer « migrate:fresh » sur la production : toutes les données disparaissent. Réserve-la au développement.
- Créer une clé étrangère avant que la table cible existe : l'ordre des fichiers compte, car il suit leur date. Respecte l'ordre de création des tables.
- Stocker un montant en nombre décimal flottant : des erreurs d'arrondi apparaissent. Utilise un entier ou un type décimal exact.

## Bonnes pratiques

- Une migration par changement logique, avec un nom explicite.
- Ajoute des clés étrangères et des index sur les colonnes de recherche.
- Teste « migrate » puis « migrate:rollback » en local avant de pousser.
- Sauvegarde la base de production avant toute migration risquée.

## Auto-évaluation

- Quel est le rôle des méthodes « up » et « down » ?
- Comment Laravel sait-il quelles migrations ont déjà été exécutées ?
- Pourquoi ne modifie-t-on pas une migration déjà partagée ?
- Que fait « constrained()->cascadeOnDelete() » ?
- Pourquoi éviter « migrate:fresh » en production ?

## À retenir

- Les migrations versionnent le schéma de la base comme le code.
- Chaque migration définit « up » et « down ».
- Les contraintes et les index protègent et accélèrent la base.
- Un changement sur une table existante passe par une nouvelle migration.
- Les montants sans centimes se stockent en entiers.
MD,
            'code_example' => <<<'CODE'
<?php

// database/migrations/2026_01_01_000000_create_roadmaps_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Étape 1 : classe anonyme qui étend Migration
return new class extends Migration
{
    // Étape 2 : up() applique le changement
    public function up(): void
    {
        Schema::create('roadmaps', function (Blueprint $table) {
            // Étape 3 : identifiant et clé étrangère (cascade à la suppression)
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // Étape 4 : colonnes métier
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('status')->default('active');

            // Étape 5 : index composé pour « mes roadmaps actives »
            $table->index(['user_id', 'status']);

            // Étape 6 : created_at et updated_at
            $table->timestamps();
        });
    }

    // Étape 7 : down() annule exactement ce que up() a fait
    public function down(): void
    {
        Schema::dropIfExists('roadmaps');
    }
};
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Migration de la table produits',
            'exercise_description' => <<<'MD'
Pour une boutique en ligne, écris la migration de la table « produits » puis lance-la avec « php artisan migrate ».

Critères de réussite :
- La table contient « nom » (texte), « sku » (code unique), « prix » (entier non signé en FCFA) et « stock » (entier non signé, valeur par défaut 0).
- Une colonne « description » facultative est présente (nullable).
- Un index est posé sur la colonne « nom ».
- Les colonnes de dates « created_at » et « updated_at » existent.
- La méthode « down » supprime la table avec « dropIfExists ».
MD,
            'exercise_hint' => 'Utilise $table->string(\'sku\')->unique(), $table->unsignedInteger(\'prix\'), $table->unsignedInteger(\'stock\')->default(0), $table->text(\'description\')->nullable() et $table->index(\'nom\').',
            'exercise_solution' => <<<'CODE'
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('produits', function (Blueprint $table) {
            $table->id();
            $table->string('nom');
            $table->string('sku')->unique();
            // FCFA : pas de centimes, un entier suffit
            $table->unsignedInteger('prix');
            $table->unsignedInteger('stock')->default(0);
            $table->text('description')->nullable();

            // Index pour accélérer la recherche par nom
            $table->index('nom');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('produits');
    }
};
CODE,
        ],

        'Models et Eloquent' => [
            'description' => 'Utiliser Eloquent, l\'ORM de Laravel, pour lire et écrire des données avec des objets PHP plutôt que du SQL.',
            'objective' => 'Écrire un modèle avec champs assignables, casts et scope, puis créer, lire, modifier et supprimer des enregistrements.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application passe son temps à lire et écrire des données : commandes, paiements, inscriptions. Écrire du SQL à la main pour chaque opération est long et sujet aux erreurs, y compris aux failles d'injection SQL. Eloquent est l'ORM de Laravel : chaque table correspond à une classe, chaque ligne à un objet. Tu écris « Roadmap::create(...) » au lieu d'une requête INSERT, et Laravel se charge de la protection des paramètres.

C'est l'un des points qui rend Laravel productif : en quelques lignes, tu obtiens des opérations complètes sur ta base.

## Les concepts clés

### Convention de nommage

Un modèle « Roadmap » correspond par défaut à la table « roadmaps » (nom au pluriel, en minuscules). La clé primaire est « id ». Les colonnes « created_at » et « updated_at » sont gérées automatiquement. Si ta table ne suit pas la convention, tu peux la préciser, mais mieux vaut suivre la norme.

### Assignation de masse

Quand tu appelles « create » avec un tableau, Laravel refuse par défaut les champs inconnus, pour empêcher qu'un utilisateur modifie une colonne sensible comme « is_admin ». La propriété « $fillable » liste explicitement les champs autorisés. C'est une protection, pas un détail.

### Les casts

La propriété « casts » convertit les valeurs lues en base : un champ « 1 » devient un booléen, une chaîne de date devient un objet Carbon, un JSON devient un tableau. Tu manipules ainsi de vrais types PHP.

### Les requêtes

Le modèle sert de point de départ à un constructeur de requêtes : « where », « orderBy », « latest », « first », « get », « find », « count ». La méthode « get » retourne une collection, « first » un seul modèle ou null, « findOrFail » un modèle ou une erreur 404. Pour modifier, tu changes les propriétés puis appelles « save », ou tu utilises « update ». Pour supprimer, « delete ».

### Les scopes

Un scope local est une méthode préfixée par « scope » qui encapsule un filtre réutilisable. « Roadmap::active() » est plus lisible que « where('status', 'active') » répété partout.

## Exemple pas à pas

Le fichier est le modèle « Roadmap ». L'étape 1 déclare la classe et le trait « HasFactory ». L'étape 2 définit « $fillable ». L'étape 3 déclare les casts : ici, une date de publication. L'étape 4 écrit le scope « active » avec la requête reçue en paramètre. L'étape 5 ajoute la relation vers l'utilisateur, détaillée dans la leçon suivante. Ensuite, à la fin du fichier, des exemples en commentaire montrent comment créer, lire avec le scope, modifier et supprimer un enregistrement.

## Erreurs fréquentes

- Oublier « $fillable » : l'erreur d'assignation de masse apparaît. Liste les champs autorisés.
- Mettre tous les champs dans « $guarded = [] » : tu ouvres la porte à la modification de champs sensibles. Liste précisément ce qui est modifiable.
- Utiliser « get » quand tu attends un seul résultat : tu récupères une collection. Utilise « first » ou « findOrFail ».
- Charger toute la table puis filtrer en PHP : c'est lent. Filtre dans la requête avec « where ».
- Modifier un objet sans appeler « save » : rien n'est enregistré. Appelle « save » ou « update ».
- Confondre « find » et « findOrFail » : « find » retourne null, ce qui provoque une erreur plus loin. Choisis consciemment.

## Bonnes pratiques

- Un modèle par table, au singulier, avec un nom explicite.
- Déclare toujours « $fillable » et les casts utiles.
- Écris des scopes pour les filtres répétés.
- Pagine les listes avec « paginate » plutôt que de tout charger.
- Garde la logique métier liée aux données dans le modèle.

## Auto-évaluation

- À quelle table correspond le modèle « Roadmap » par défaut ?
- Pourquoi la propriété « $fillable » existe-t-elle ?
- Quelle est la différence entre « get », « first » et « findOrFail » ?
- À quoi sert un cast ?
- Comment écrit-on un scope local et comment l'appelle-t-on ?

## À retenir

- Un modèle Eloquent représente une table, un objet représente une ligne.
- « $fillable » protège contre l'assignation de masse non voulue.
- Les casts donnent de vrais types PHP aux colonnes.
- Les scopes rendent les filtres lisibles et réutilisables.
- Pense toujours à filtrer dans la requête, pas en PHP.
MD,
            'code_example' => <<<'CODE'
<?php

// app/Models/Roadmap.php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// Étape 1 : un modèle par table (ici « roadmaps »)
class Roadmap extends Model
{
    use HasFactory;

    // Étape 2 : seuls ces champs peuvent être remplis par create()/update()
    protected $fillable = ['title', 'description', 'status', 'published_at'];

    // Étape 3 : conversion automatique des types à la lecture
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    // Étape 4 : scope local réutilisable -> Roadmap::active()
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    // Étape 5 : relation (voir la leçon suivante)
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}

// Exemples d'utilisation (dans un contrôleur) :
// $r = Roadmap::create(['title' => 'Apprendre Laravel']);   // créer
// $actives = Roadmap::active()->latest()->get();             // lire avec le scope
// $r->update(['status' => 'archived']);                      // modifier
// $r->delete();                                              // supprimer
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Modèle Produit',
            'exercise_description' => <<<'MD'
Écris le modèle « Produit » (table « produits » de l'exercice précédent) et une méthode d'affichage du prix.

Critères de réussite :
- Le modèle déclare explicitement « $table » avec la valeur « produits ».
- « $fillable » contient « nom », « sku », « prix », « stock » et « description ».
- Un scope « enStock » ne retient que les produits dont le stock est supérieur à 0.
- Un accesseur « prixFormate » retourne par exemple « 12 500 FCFA » à partir de l'entier « prix ».
- Les casts convertissent « prix » et « stock » en entiers.
MD,
            'exercise_hint' => 'Déclare $table = \'produits\' pour ne pas laisser Eloquent deviner. Pour l\'accesseur, utilise Attribute::make(get: fn () => ...) avec number_format($this->prix, 0, \',\', \' \').',
            'exercise_solution' => <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Produit extends Model
{
    // Table explicite (convention française, on ne laisse pas Eloquent deviner)
    protected $table = 'produits';

    protected $fillable = ['nom', 'sku', 'prix', 'stock', 'description'];

    // Casts : valeurs lues comme de vrais entiers
    protected function casts(): array
    {
        return [
            'prix' => 'integer',
            'stock' => 'integer',
        ];
    }

    // Scope : Produit::enStock()->get()
    public function scopeEnStock(Builder $query): Builder
    {
        return $query->where('stock', '>', 0);
    }

    // Accesseur : $produit->prix_formate donne « 12 500 FCFA »
    protected function prixFormate(): Attribute
    {
        return Attribute::make(
            get: fn () => number_format($this->prix, 0, ',', ' ') . ' FCFA',
        );
    }
}
CODE,
        ],

        'Relations Eloquent' => [
            'description' => 'Relier les modèles entre eux (un à plusieurs, plusieurs à plusieurs) et éviter le problème des requêtes en trop.',
            'objective' => 'Déclarer des relations hasMany et belongsTo entre deux modèles, charger les données liées avec « with » et compter les enregistrements avec « withCount ».',
            'content' => <<<'MD'
## Pourquoi cette notion

Les données d'une vraie application ne vivent jamais seules. Un client a plusieurs commandes, une commande contient plusieurs produits, une école a plusieurs classes. Dans DevRoad, une roadmap contient plusieurs étapes. Les relations Eloquent traduisent ces liens en code lisible : au lieu d'écrire des jointures SQL, tu écris « $roadmap->steps ».

Mal utilisées, les relations provoquent aussi l'un des problèmes de performance les plus courants des applications Laravel : le problème N+1, qui peut transformer une page en centaines de requêtes. Le comprendre est indispensable.

## Les concepts clés

### Un à plusieurs

La relation la plus fréquente. Côté « un », tu déclares « hasMany » : une roadmap a plusieurs étapes. Côté « plusieurs », tu déclares « belongsTo » : une étape appartient à une roadmap. La table des étapes porte la clé étrangère « roadmap_id », que la migration a créée avec « foreignId ».

### Plusieurs à plusieurs

Quand chaque côté peut avoir plusieurs de l'autre, par exemple des étudiants et des formations, on passe par une table intermédiaire (pivot) et la relation « belongsToMany ». Par convention, la table pivot porte les deux noms au singulier, rangés par ordre alphabétique, comme « etudiant_formation ».

### Accéder aux relations

Quand tu écris « $roadmap->steps » sans parenthèses, tu obtiens la collection déjà chargée. Quand tu écris « $roadmap->steps() » avec parenthèses, tu obtiens le constructeur de requête, sur lequel tu peux enchaîner « where » ou « create ». La création via la relation remplit automatiquement la clé étrangère.

### Le problème N+1

Si tu affiches 50 roadmaps et que tu lis les étapes de chacune dans une boucle, Eloquent exécute une requête pour la liste, puis une par roadmap : 51 requêtes. La solution est le chargement anticipé : « Roadmap::with('steps')->get() » exécute seulement deux requêtes. Pour juste compter, « withCount('steps') » ajoute la propriété « steps_count » sans charger les lignes.

## Exemple pas à pas

L'exemple contient deux classes. L'étape 1 déclare dans « Roadmap » la relation « steps » avec « hasMany » et un tri par position. L'étape 2 déclare dans « RoadmapStep » la relation inverse « roadmap » avec « belongsTo ». L'étape 3 liste les champs autorisés de l'étape. Les exemples en commentaire montrent ensuite l'étape 4, la création d'une étape via la relation, l'étape 5, le chargement anticipé avec « with » et « withCount », et l'étape 6, l'accès à la roadmap parente depuis une étape. Compare le nombre de requêtes avec et sans « with » en activant le journal des requêtes.

## Erreurs fréquentes

- Lire une relation dans une boucle sans « with » : le N+1 ralentit la page. Charge la relation en avance.
- Oublier la clé étrangère dans la migration : la relation échoue. Ajoute « foreignId('roadmap_id')->constrained() ».
- Confondre « $roadmap->steps » et « $roadmap->steps() » : l'un donne les données, l'autre le constructeur de requête. Utilise celui qui correspond à ton besoin.
- Nommer la relation au mauvais nombre : « step » au lieu de « steps » prête à confusion. Utilise le pluriel pour hasMany et le singulier pour belongsTo.
- Charger toutes les étapes juste pour les compter : c'est inutile. Utilise « withCount ».
- Oublier de supprimer les enfants quand le parent disparaît : des données orphelines restent. Utilise « cascadeOnDelete » dans la migration.

## Bonnes pratiques

- Déclare toujours les deux côtés d'une relation.
- Active la prévention du chargement paresseux en développement pour détecter les N+1 tôt.
- Utilise « with » dans les contrôleurs de liste et « withCount » pour les compteurs.
- Crée les enfants via la relation pour garder la clé étrangère cohérente.

## Auto-évaluation

- Quelle relation déclares-tu côté parent, et laquelle côté enfant ?
- Quelle est la différence entre « $roadmap->steps » et « $roadmap->steps() » ?
- Pourquoi le problème N+1 apparaît-il et comment le corriger ?
- Quand utilises-tu une table pivot ?
- Que fait « withCount » ?

## À retenir

- « hasMany » et « belongsTo » se déclarent en paire.
- La clé étrangère est portée par la table enfant.
- « with » charge les relations en avance et évite le N+1.
- « withCount » compte sans charger les lignes.
- Créer via la relation remplit automatiquement la clé étrangère.
MD,
            'code_example' => <<<'CODE'
<?php

// app/Models/Roadmap.php (extrait) : le côté « un »
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roadmap extends Model
{
    protected $fillable = ['title', 'description', 'status'];

    // Étape 1 : une roadmap possède plusieurs étapes, triées par position
    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('position');
    }
}

// app/Models/RoadmapStep.php : le côté « plusieurs »
class RoadmapStep extends Model
{
    // Étape 3 : champs autorisés pour create()
    protected $fillable = ['title', 'position', 'is_done'];

    // Étape 2 : chaque étape appartient à une roadmap (clé roadmap_id)
    public function roadmap(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}

// Exemples d'utilisation :
//
// Étape 4 : créer une étape via la relation (roadmap_id rempli automatiquement)
// $roadmap->steps()->create(['title' => 'Installer Laravel', 'position' => 1]);
//
// Étape 5 : chargement anticipé (2 requêtes au lieu de N+1) + compteur
// $roadmaps = Roadmap::with('steps')->withCount('steps')->get();
// foreach ($roadmaps as $r) { echo $r->title, ' : ', $r->steps_count, ' étapes'; }
//
// Étape 6 : remonter vers le parent depuis une étape
// $nom = RoadmapStep::find(1)->roadmap->title;
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Catégories et produits',
            'exercise_description' => <<<'MD'
Dans la boutique, une catégorie (par exemple « Vêtements ») contient plusieurs produits. Écris les deux modèles et un exemple d'utilisation efficace.

Critères de réussite :
- Le modèle « Categorie » déclare une relation « produits » de type « hasMany ».
- Le modèle « Produit » déclare une relation « categorie » de type « belongsTo ».
- Un exemple charge toutes les catégories avec « with('produits') » pour éviter le N+1.
- Un second exemple utilise « withCount('produits') » et lit la propriété « produits_count ».
- La création d'un produit passe par la relation « produits() » de la catégorie.
MD,
            'exercise_hint' => 'La table produits porte categorie_id. Dans Categorie : $this->hasMany(Produit::class). Dans Produit : $this->belongsTo(Categorie::class). withCount(\'produits\') crée la propriété produits_count.',
            'exercise_solution' => <<<'CODE'
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categorie extends Model
{
    protected $fillable = ['nom'];

    // Une catégorie contient plusieurs produits
    public function produits(): HasMany
    {
        return $this->hasMany(Produit::class);
    }
}

class Produit extends Model
{
    protected $fillable = ['nom', 'prix', 'stock'];

    // Un produit appartient à une catégorie (colonne categorie_id)
    public function categorie(): BelongsTo
    {
        return $this->belongsTo(Categorie::class);
    }
}

// Utilisation :
//
// Chargement anticipé : 2 requêtes seulement, quel que soit le nombre de catégories
// $categories = Categorie::with('produits')->get();
//
// Compteur sans charger les produits
// $categories = Categorie::withCount('produits')->get();
// foreach ($categories as $c) { echo $c->nom . ' : ' . $c->produits_count; }
//
// Création via la relation : categorie_id est rempli automatiquement
// $categorie->produits()->create(['nom' => 'Pagne wax', 'prix' => 8500, 'stock' => 20]);
CODE,
        ],

        'Validation avec Form Requests' => [
            'description' => 'Valider les données entrantes dans des classes dédiées, avec règles, messages en français et préparation des champs.',
            'objective' => 'Écrire un Form Request avec règles, messages personnalisés et préparation des données, puis l\'utiliser dans un contrôleur.',
            'content' => <<<'MD'
## Pourquoi cette notion

Ne fais jamais confiance aux données qui arrivent du navigateur. Un utilisateur peut oublier un champ, saisir un prix négatif, envoyer un texte de cent mille caractères ou, pire, forger une requête à la main. Sur une plateforme de paiement ou de réservation, des données invalides peuvent fausser un stock, créer des doublons ou ouvrir une faille. La validation est ta première ligne de défense.

Laravel propose de valider directement dans le contrôleur, mais dès que les règles grossissent, le contrôleur devient illisible. Les Form Requests déplacent ces règles dans une classe dédiée, réutilisable et testable.

## Les concepts clés

### Qu'est-ce qu'un Form Request ?

C'est une classe qui étend « FormRequest ». Tu la crées avec « php artisan make:request StoreRoadmapRequest ». Quand tu la types dans le paramètre d'une méthode de contrôleur, Laravel l'instancie et lance la validation avant même d'entrer dans ta méthode. Si la validation échoue, l'utilisateur est renvoyé vers le formulaire avec ses erreurs, ou reçoit une réponse 422 en JSON pour une API.

### Les méthodes principales

La méthode « authorize » dit si l'utilisateur a le droit de faire cette requête : elle retourne un booléen. La méthode « rules » retourne un tableau des règles par champ : « required », « string », « max:120 », « integer », « min:0 », « in:active,archived », « unique:table,colonne », « exists:table,colonne ». La méthode « messages » personnalise les textes d'erreur, et « attributes » renomme les champs dans ces messages. La méthode « prepareForValidation » nettoie les données avant la validation, par exemple en retirant les espaces.

### Données validées

Dans le contrôleur, « $request->validated() » retourne uniquement les champs qui ont passé la validation. Passer ce tableau à « create » est beaucoup plus sûr que « $request->all() ».

### Règles conditionnelles

Une règle peut dépendre du contexte. Pour une mise à jour, la règle « unique » doit ignorer l'enregistrement courant avec « Rule::unique(...)->ignore(...) », sinon l'utilisateur ne peut pas enregistrer sans changer sa valeur.

## Exemple pas à pas

L'exemple contient le Form Request puis le contrôleur. L'étape 1 déclare la classe. L'étape 2, « authorize », autorise ici tout utilisateur connecté. L'étape 3 nettoie le titre avec « prepareForValidation ». L'étape 4 liste les règles : titre obligatoire de 120 caractères maximum, description facultative, statut limité à deux valeurs. L'étape 5 écrit les messages en français. L'étape 6 montre l'utilisation dans le contrôleur : le paramètre est typé avec le Form Request, et « validated » fournit les données sûres à « create ».

## Erreurs fréquentes

- Laisser « authorize » retourner « false » par défaut : toutes les requêtes reçoivent une erreur 403. Retourne « true » ou implémente la vérification.
- Utiliser « $request->all() » après la validation : des champs non validés passent. Utilise « validated ».
- Oublier « ignore » dans la règle « unique » lors d'une mise à jour : la mise à jour est refusée à tort. Ajoute « Rule::unique(...)->ignore($id) ».
- Valider seulement côté React : un attaquant contourne le navigateur. Valide toujours côté serveur.
- Laisser les messages d'erreur en anglais pour des utilisateurs francophones : prévois « messages » ou les fichiers de langue.
- Oublier « nullable » sur un champ facultatif : une chaîne vide est refusée. Ajoute « nullable ».

## Bonnes pratiques

- Un Form Request par action d'écriture : « Store » et « Update ».
- Nomme les règles de façon lisible, en tableau plutôt qu'en chaîne longue.
- Utilise « exists » pour vérifier qu'une clé étrangère existe vraiment.
- Écris des messages clairs, qui disent à l'utilisateur comment corriger.
- Teste au moins un cas valide et un cas invalide.

## Auto-évaluation

- À quoi servent les méthodes « authorize » et « rules » ?
- Que se passe-t-il quand la validation échoue ?
- Pourquoi préférer « validated » à « all » ?
- Comment valider une valeur unique lors d'une mise à jour ?
- Pourquoi la validation côté serveur est-elle indispensable même avec React ?

## À retenir

- Un Form Request isole les règles de validation et l'autorisation d'une requête.
- La validation s'exécute avant l'entrée dans la méthode du contrôleur.
- « validated » ne retourne que les champs vérifiés.
- Les messages se personnalisent en français.
- La validation serveur ne se remplace jamais par celle du navigateur.
MD,
            'code_example' => <<<'CODE'
<?php

// app/Http/Requests/StoreRoadmapRequest.php

// Étape 1 : la classe étend FormRequest
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoadmapRequest extends FormRequest
{
    // Étape 2 : seuls les utilisateurs connectés peuvent créer
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    // Étape 3 : nettoyage avant validation (retire les espaces superflus)
    protected function prepareForValidation(): void
    {
        $this->merge([
            'title' => trim((string) $this->input('title')),
        ]);
    }

    // Étape 4 : règles par champ
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:2000'],
            'status' => ['required', 'in:active,archived'],
        ];
    }

    // Étape 5 : messages en français
    public function messages(): array
    {
        return [
            'title.required' => 'Le titre est obligatoire.',
            'title.max' => 'Le titre ne doit pas dépasser 120 caractères.',
            'status.in' => 'Le statut doit être « active » ou « archived ».',
        ];
    }
}

// Étape 6 : utilisation dans le contrôleur
// public function store(StoreRoadmapRequest $request)
// {
//     // validated() ne contient que les champs vérifiés
//     $roadmap = $request->user()->roadmaps()->create($request->validated());
//     return response()->json($roadmap, 201);
// }
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Valider un produit',
            'exercise_description' => <<<'MD'
Écris le Form Request « StoreProduitRequest » pour la création d'un produit de boutique.

Critères de réussite :
- « nom » est obligatoire, de type texte, 120 caractères maximum.
- « sku » est obligatoire et unique dans la table « produits ».
- « prix » est un entier d'au moins 100 (FCFA) et « stock » un entier d'au moins 0.
- « categorie_id » est facultatif mais, s'il est fourni, doit exister dans la table « categories ».
- Au moins deux messages d'erreur sont rédigés en français et « authorize » retourne « true ».
MD,
            'exercise_hint' => 'Utilise \'unique:produits,sku\' pour le code unique, \'exists:categories,id\' pour la catégorie avec \'nullable\' devant, et \'integer\', \'min:100\' pour le prix.',
            'exercise_solution' => <<<'CODE'
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProduitRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nom' => ['required', 'string', 'max:120'],
            // Code produit unique dans la table produits
            'sku' => ['required', 'string', 'unique:produits,sku'],
            // Prix en FCFA : entier d'au moins 100
            'prix' => ['required', 'integer', 'min:100'],
            'stock' => ['required', 'integer', 'min:0'],
            // Facultatif, mais doit exister s'il est fourni
            'categorie_id' => ['nullable', 'exists:categories,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'nom.required' => 'Le nom du produit est obligatoire.',
            'sku.unique' => 'Ce code produit existe déjà.',
            'prix.min' => 'Le prix doit être d\'au moins 100 FCFA.',
            'stock.min' => 'Le stock ne peut pas être négatif.',
        ];
    }
}
CODE,
        ],

        'Policies et autorisation' => [
            'description' => 'Contrôler qui a le droit de voir, modifier ou supprimer une ressource grâce aux policies.',
            'objective' => 'Écrire une policy pour un modèle, l\'appliquer dans un contrôleur avec « authorize » et gérer le cas d\'un administrateur.',
            'content' => <<<'MD'
## Pourquoi cette notion

L'authentification répond à la question « qui es-tu ? ». L'autorisation répond à « as-tu le droit de faire ça ? ». Imagine une application de gestion de commandes : chaque client peut consulter ses propres commandes, mais pas celles du voisin. Si tu oublies cette vérification, il suffit de changer le numéro dans l'adresse pour lire les données d'un autre utilisateur. Cette faille, très répandue, s'appelle une référence directe non sécurisée à un objet.

Les policies sont la manière propre, centralisée et testable de répondre à ces questions dans Laravel.

## Les concepts clés

### Qu'est-ce qu'une policy ?

Une policy est une classe qui regroupe les règles d'autorisation pour un modèle. Chaque méthode correspond à une action : « viewAny », « view », « create », « update », « delete ». Elle reçoit l'utilisateur connecté et, souvent, l'instance du modèle, et retourne « true » ou « false ». Tu la génères avec « php artisan make:policy RoadmapPolicy --model=Roadmap ».

### Découverte automatique

Laravel associe automatiquement « Roadmap » à « RoadmapPolicy » si elles suivent la convention de nommage et se trouvent dans « app/Policies ». Tu n'as normalement rien à enregistrer.

### Utiliser la policy

Dans un contrôleur, « $this->authorize('update', $roadmap) » lance la vérification et retourne une erreur 403 si elle échoue. Sur une route, le middleware « can » fait la même chose. Dans l'interface, tu peux transmettre à React les droits calculés côté serveur pour masquer un bouton, mais la vérification serveur reste la seule qui protège vraiment.

### Le raccourci « before »

Une méthode « before » exécutée avant les autres permet d'accorder tous les droits à un administrateur : si elle retourne « true », les autres méthodes ne sont pas évaluées. Si elle retourne « null », le calcul normal continue. Fais attention à ne jamais retourner « false » pour un non-administrateur, sinon plus personne n'a de droit.

## Exemple pas à pas

L'exemple contient la policy et son usage. L'étape 1 déclare la classe. L'étape 2, « before », donne tout pouvoir à l'administrateur et retourne « null » pour les autres. L'étape 3, « viewAny », autorise tout utilisateur connecté à lister ses roadmaps. L'étape 4, « view », compare l'identifiant du propriétaire à celui de l'utilisateur. Les étapes 5 et 6 appliquent la même logique pour « update » et « delete ». L'étape 7 montre l'appel de « authorize » dans les actions du contrôleur : une tentative sur la roadmap d'un autre utilisateur reçoit un 403.

## Erreurs fréquentes

- Ne vérifier que dans l'interface en masquant un bouton : un utilisateur peut quand même envoyer la requête. Vérifie toujours côté serveur.
- Comparer des types différents, par exemple « 1 » et 1 avec un opérateur strict : la comparaison échoue. Compare les identifiants entiers entre eux ou utilise « $user->is($model) ».
- Retourner « false » dans « before » pour les non-administrateurs : tu bloques tout le monde. Retourne « null ».
- Oublier d'appeler « authorize » dans une action : la policy existe mais ne protège rien. Ajoute l'appel dans chaque action concernée.
- Nommer la policy différemment du modèle sans l'enregistrer : Laravel ne la trouve pas. Respecte la convention ou enregistre-la.
- Autoriser la création sans vérifier le rôle : n'importe qui peut créer. Implémente « create ».

## Bonnes pratiques

- Une policy par modèle, des méthodes courtes et lisibles.
- Applique le principe du moindre privilège : refuse par défaut, autorise explicitement.
- Teste les cas « propriétaire », « autre utilisateur » et « administrateur ».
- Place la vérification au début de chaque action du contrôleur.

## Auto-évaluation

- Quelle différence y a-t-il entre authentification et autorisation ?
- Que se passe-t-il quand « authorize » échoue dans un contrôleur ?
- À quoi sert la méthode « before » et quelle valeur doit-elle retourner pour continuer ?
- Pourquoi masquer un bouton dans React ne suffit-il pas ?
- Comment Laravel associe-t-il un modèle à sa policy ?

## À retenir

- Une policy centralise les règles d'autorisation d'un modèle.
- « authorize » dans le contrôleur renvoie un 403 si l'accès est refusé.
- La vérification se fait toujours côté serveur.
- « before » retourne « true » pour un administrateur et « null » sinon.
- Refuse par défaut et autorise explicitement.
MD,
            'code_example' => <<<'CODE'
<?php

// app/Policies/RoadmapPolicy.php

// Étape 1 : une policy par modèle
namespace App\Policies;

use App\Models\Roadmap;
use App\Models\User;

class RoadmapPolicy
{
    // Étape 2 : l'administrateur a tous les droits.
    // true = autorisé immédiatement ; null = on continue avec les règles normales
    public function before(User $user, string $ability): ?bool
    {
        return $user->is_admin ? true : null;
    }

    // Étape 3 : tout utilisateur connecté peut lister (le contrôleur filtre)
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Étape 4 : on ne voit que ses propres roadmaps
    public function view(User $user, Roadmap $roadmap): bool
    {
        return $user->id === $roadmap->user_id;
    }

    // Étape 5 : seul le propriétaire modifie
    public function update(User $user, Roadmap $roadmap): bool
    {
        return $user->id === $roadmap->user_id;
    }

    // Étape 6 : seul le propriétaire supprime
    public function delete(User $user, Roadmap $roadmap): bool
    {
        return $user->id === $roadmap->user_id;
    }
}

// Étape 7 : utilisation dans le contrôleur (403 si refusé)
// public function destroy(Roadmap $roadmap)
// {
//     $this->authorize('delete', $roadmap);
//     $roadmap->delete();
//     return response()->json(null, 204);
// }
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Policy des commandes',
            'exercise_description' => <<<'MD'
Dans une boutique, un client ne doit voir que ses propres commandes, et seul un administrateur peut les supprimer. Écris la « CommandePolicy ».

Critères de réussite :
- La méthode « view » autorise uniquement le client propriétaire de la commande (« client_id »).
- La méthode « update » autorise uniquement le propriétaire tant que le statut de la commande est « en_attente ».
- La méthode « delete » n'autorise que les utilisateurs dont « is_admin » vaut vrai.
- Une méthode « before » donne la permission de voir à un administrateur sans bloquer les autres cas (retour « null »).
- Un exemple montre l'appel de « authorize » dans un contrôleur.
MD,
            'exercise_hint' => 'Dans before(), vérifie $ability === \'view\' pour ne donner que ce droit aux administrateurs, et retourne null sinon. Pour update, combine l\'égalité des identifiants et le statut « en_attente ».',
            'exercise_solution' => <<<'CODE'
<?php

namespace App\Policies;

use App\Models\Commande;
use App\Models\User;

class CommandePolicy
{
    // L'administrateur peut voir ; pour le reste, on continue le calcul normal
    public function before(User $user, string $ability): ?bool
    {
        if ($user->is_admin && $ability === 'view') {
            return true;
        }

        return null;
    }

    // Le client ne voit que ses commandes
    public function view(User $user, Commande $commande): bool
    {
        return $user->id === $commande->client_id;
    }

    // Modification : propriétaire ET commande encore en attente
    public function update(User $user, Commande $commande): bool
    {
        return $user->id === $commande->client_id
            && $commande->statut === 'en_attente';
    }

    // Suppression : réservée aux administrateurs
    public function delete(User $user, Commande $commande): bool
    {
        return (bool) $user->is_admin;
    }
}

// Usage dans CommandeController :
// public function show(Commande $commande)
// {
//     $this->authorize('view', $commande); // 403 si refusé
//     return response()->json($commande);
// }
CODE,
        ],

        'Inertia et React' => [
            'description' => 'Relier Laravel et React avec Inertia : le contrôleur envoie des props, la page React les affiche, sans API à construire.',
            'objective' => 'Rendre une page React depuis un contrôleur avec « Inertia::render », afficher des données en props et créer un formulaire avec « useForm ».',
            'content' => <<<'MD'
## Pourquoi cette notion

Beaucoup d'équipes veulent une interface React moderne, mais ne veulent pas construire et maintenir une API complète séparée, avec gestion de jetons, CORS et duplication des routes. Inertia propose un compromis : tu gardes les routes, les contrôleurs, la session et la validation de Laravel, et tu écris tes pages en React. Pour une petite agence qui livre vite à des clients, c'est un gain de temps considérable.

Dans DevRoad, tu vas utiliser cette approche pour afficher la liste des roadmaps et créer de nouvelles entrées avec une interface réactive.

## Les concepts clés

### Comment fonctionne Inertia

Inertia n'est ni un framework front, ni une API. C'est une couche qui relie les deux. À la première visite, le serveur renvoie une page HTML complète qui contient le composant React à afficher et ses données. Ensuite, quand l'utilisateur clique sur un lien, Inertia fait une requête en arrière-plan, reçoit en JSON le nom du composant et ses props, et met à jour la page sans rechargement complet.

### Rendre une page depuis un contrôleur

Au lieu de retourner une vue Blade ou du JSON, le contrôleur retourne « Inertia::render('Roadmaps/Index', [...]) ». Le premier argument est le chemin du composant dans « resources/js/Pages », le second les props. Les props sont sérialisées en JSON : n'envoie que ce dont la page a besoin, jamais le modèle complet avec des champs sensibles.

### Le composant de page

Une page est un composant React qui reçoit les props en paramètre. Le composant « Link » d'Inertia remplace la balise « a » pour naviguer sans rechargement. Le composant « Head » modifie le titre de la page.

### Formulaires avec useForm

Le hook « useForm » gère les valeurs du formulaire, l'envoi, l'état de chargement « processing » et les erreurs renvoyées par la validation Laravel, dans « errors ». Les messages de ton Form Request apparaissent directement dans l'interface, sans code supplémentaire.

### Données partagées

Le middleware d'Inertia peut partager des données sur toutes les pages, comme l'utilisateur connecté ou les messages flash. Tu y accèdes avec le hook « usePage ».

## Exemple pas à pas

L'exemple est la page « Roadmaps/Index.jsx », avec en commentaire le contrôleur correspondant. L'étape 1 montre le contrôleur qui envoie la liste des roadmaps en props. L'étape 2 importe « Head », « Link » et « useForm ». L'étape 3 déclare le composant qui reçoit « roadmaps » en prop. L'étape 4 initialise le formulaire avec le champ « title ». L'étape 5 envoie le formulaire en POST vers la route de création, et réinitialise le champ en cas de succès. L'étape 6 affiche l'erreur de validation du titre. L'étape 7 affiche la liste ou un message si elle est vide, avec un lien vers chaque roadmap.

## Erreurs fréquentes

- Retourner du JSON depuis une route Inertia : la navigation casse. Retourne « Inertia::render ».
- Envoyer le modèle complet en props : des champs sensibles se retrouvent dans le HTML. Sélectionne les colonnes utiles.
- Utiliser une balise « a » pour naviguer : la page se recharge entièrement. Utilise « Link ».
- Ne pas afficher « errors » : l'utilisateur ne comprend pas pourquoi rien ne se passe. Affiche chaque message sous son champ.
- Mauvais chemin du composant : une casse différente provoque une erreur sur Linux. Respecte exactement les majuscules.
- Oublier de désactiver le bouton pendant « processing » : le formulaire est envoyé en double. Utilise « disabled={processing} ».

## Bonnes pratiques

- Garde les pages minces et extrais les composants réutilisables dans un dossier dédié.
- Valide toujours côté Laravel avec un Form Request ; Inertia renvoie les erreurs automatiquement.
- Envoie les listes paginées plutôt que des milliers de lignes.
- Partage les messages flash pour confirmer les actions.

## Auto-évaluation

- Quelle différence y a-t-il entre Inertia et une API classique ?
- Que fait « Inertia::render » et que signifient ses deux arguments ?
- Pourquoi utiliser « Link » plutôt qu'une balise « a » ?
- Où apparaissent les erreurs de validation dans la page React ?
- Pourquoi limiter les props envoyées à la page ?

## À retenir

- Inertia relie contrôleurs Laravel et pages React sans API séparée.
- Les props sont envoyées en JSON : n'expose que le nécessaire.
- « useForm » gère l'envoi, le chargement et les erreurs.
- « Link » navigue sans recharger la page.
- Les composants de page vivent dans « resources/js/Pages ».
MD,
            'code_example' => <<<'CODE'
// resources/js/Pages/Roadmaps/Index.jsx

// Étape 1 (côté Laravel, RoadmapController@index) :
// return Inertia::render('Roadmaps/Index', [
//     'roadmaps' => $request->user()->roadmaps()->latest()
//         ->get(['id', 'title', 'status']),   // seulement les colonnes utiles
// ]);

// Étape 2 : imports Inertia
import { Head, Link, useForm } from '@inertiajs/react';

// Étape 3 : la prop « roadmaps » vient du contrôleur
export default function Index({ roadmaps }) {
    // Étape 4 : état du formulaire, d'envoi et d'erreurs
    const { data, setData, post, processing, errors, reset } = useForm({
        title: '',
    });

    // Étape 5 : envoi POST vers la route de création
    const submit = (e) => {
        e.preventDefault();
        post('/roadmaps', { onSuccess: () => reset('title') });
    };

    return (
        <>
            <Head title="Mes roadmaps" />
            <h1>Mes roadmaps</h1>

            <form onSubmit={submit}>
                <input
                    value={data.title}
                    onChange={(e) => setData('title', e.target.value)}
                    placeholder="Titre de la roadmap"
                />
                {/* Étape 6 : erreur de validation renvoyée par le Form Request */}
                {errors.title && <p role="alert">{errors.title}</p>}
                <button type="submit" disabled={processing}>Créer</button>
            </form>

            {/* Étape 7 : liste ou message d'état vide */}
            {roadmaps.length === 0 ? (
                <p>Aucune roadmap pour le moment.</p>
            ) : (
                <ul>
                    {roadmaps.map((r) => (
                        <li key={r.id}>
                            <Link href={`/roadmaps/${r.id}`}>{r.title}</Link>
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Page catalogue React',
            'exercise_description' => <<<'MD'
Crée la page « resources/js/Pages/Produits/Index.jsx » qui affiche le catalogue d'une boutique à partir de props envoyées par Laravel, avec un petit formulaire d'ajout.

Critères de réussite :
- La page reçoit une prop « produits » (tableau) et affiche pour chacun son nom et son prix suivi de « FCFA ».
- Un message « Aucun produit » s'affiche quand la liste est vide.
- Un formulaire avec « useForm » contient les champs « nom » et « prix » et envoie un POST vers « /produits ».
- Les erreurs de validation de « nom » et « prix » s'affichent sous leur champ.
- Le bouton est désactivé pendant « processing » et la page définit un titre avec « Head ».
MD,
            'exercise_hint' => 'Reprends la structure de l\'exemple : useForm({ nom: \'\', prix: \'\' }), post(\'/produits\'), errors.nom et errors.prix. Pour l\'affichage du prix : Number(p.prix).toLocaleString(\'fr-FR\').',
            'exercise_solution' => <<<'CODE'
// resources/js/Pages/Produits/Index.jsx
// Côté Laravel : return Inertia::render('Produits/Index', ['produits' => Produit::all(['id','nom','prix'])]);

import { Head, useForm } from '@inertiajs/react';

export default function Index({ produits }) {
    const { data, setData, post, processing, errors, reset } = useForm({
        nom: '',
        prix: '',
    });

    const submit = (e) => {
        e.preventDefault();
        post('/produits', { onSuccess: () => reset() });
    };

    return (
        <>
            <Head title="Catalogue" />
            <h1>Catalogue</h1>

            <form onSubmit={submit}>
                <input
                    value={data.nom}
                    onChange={(e) => setData('nom', e.target.value)}
                    placeholder="Nom du produit"
                />
                {errors.nom && <p role="alert">{errors.nom}</p>}

                <input
                    type="number"
                    value={data.prix}
                    onChange={(e) => setData('prix', e.target.value)}
                    placeholder="Prix en FCFA"
                />
                {errors.prix && <p role="alert">{errors.prix}</p>}

                <button type="submit" disabled={processing}>Ajouter</button>
            </form>

            {produits.length === 0 ? (
                <p>Aucun produit</p>
            ) : (
                <ul>
                    {produits.map((p) => (
                        <li key={p.id}>
                            {p.nom} : {Number(p.prix).toLocaleString('fr-FR')} FCFA
                        </li>
                    ))}
                </ul>
            )}
        </>
    );
}
CODE,
        ],

        'Construire un CRUD complet' => [
            'description' => 'Assembler routes, contrôleur, Form Requests, policy et pages Inertia pour une gestion complète : créer, lire, modifier, supprimer.',
            'objective' => 'Mettre en place un CRUD complet et sécurisé sur une ressource, avec validation, autorisation, redirections et messages de confirmation.',
            'content' => <<<'MD'
## Pourquoi cette notion

CRUD signifie Create, Read, Update, Delete : créer, lire, modifier, supprimer. Presque toutes les applications de gestion reposent sur ces quatre opérations, qu'il s'agisse de produits, d'élèves, de clients ou de réservations. Savoir assembler proprement un CRUD, c'est la compétence la plus demandée aux développeurs Laravel juniors : tu réutilises ce schéma des dizaines de fois dans ta carrière.

Cette leçon rassemble tout ce que tu as vu : routes, contrôleur, migration, modèle, validation, autorisation et interface Inertia. L'enjeu n'est plus d'apprendre une brique, mais de les faire travailler ensemble sans trous de sécurité.

## Les concepts clés

### La route de ressource

Une seule ligne, « Route::resource('roadmaps', RoadmapController::class) », déclare sept routes : « index », « create », « store », « show », « edit », « update » et « destroy ». Tu peux restreindre avec « only » ou « except » : une interface Inertia n'a souvent pas besoin des méthodes « create » et « edit » si tu utilises des formulaires dans la page de liste.

### Le circuit d'une écriture

Pour une création ou une modification, la requête suit toujours le même chemin : le middleware vérifie la connexion, le Form Request valide les données, la policy confirme les droits, le modèle enregistre, puis le contrôleur redirige. Chaque maillon a une responsabilité unique.

### Redirection et messages flash

Après un POST, un PATCH ou un DELETE, ne retourne jamais directement une page : redirige avec « redirect()->route(...) » ou « back() ». Ainsi, un rafraîchissement du navigateur ne renvoie pas le formulaire une deuxième fois. La méthode « with » stocke un message flash en session, que tu partages avec Inertia pour afficher une confirmation.

### Sécurité de la ressource

Chaque action de lecture ou d'écriture qui cible un enregistrement précis doit passer par « authorize ». La liste doit être filtrée par propriétaire. Les champs enregistrés viennent de « validated », jamais de « all ».

## Exemple pas à pas

L'exemple regroupe les routes et le contrôleur. L'étape 1 déclare la route de ressource limitée aux actions utiles, derrière le middleware « auth ». L'étape 2 montre « index », qui envoie les roadmaps de l'utilisateur à la page. L'étape 3 montre « store » : le Form Request valide, la relation crée l'enregistrement, puis une redirection avec message flash confirme. L'étape 4, « update », autorise d'abord, puis modifie avec les données validées. L'étape 5, « destroy », autorise et supprime. Lis chaque action en repérant les quatre maillons du circuit : validation, autorisation, écriture, redirection.

## Erreurs fréquentes

- Oublier d'autoriser « update » et « destroy » : n'importe quel utilisateur connecté modifie les données des autres. Appelle « authorize » dans chaque action ciblant un enregistrement.
- Retourner une vue après un POST : le rafraîchissement renvoie le formulaire. Redirige toujours.
- Utiliser un Form Request unique pour création et modification : les règles « unique » bloquent les mises à jour. Crée « Store » et « Update » séparés, avec « ignore ».
- Lister tous les enregistrements au lieu de ceux de l'utilisateur : fuite de données. Passe par la relation.
- Supprimer sans confirmation côté interface : des suppressions accidentelles surviennent. Demande une confirmation.
- Oublier de protéger les routes par « auth » : des visiteurs anonymes accèdent à la gestion. Place la ressource dans un groupe protégé.

## Bonnes pratiques

- Génère le contrôleur avec « make:controller --resource » pour garder les noms standards.
- Paginer les listes dès qu'elles peuvent dépasser quelques dizaines de lignes.
- Partage les messages flash pour que chaque action donne un retour visible.
- Écris au moins un test de fonctionnalité par action.
- Utilise des routes nommées dans les liens et les redirections.

## Auto-évaluation

- Quelles sept routes crée « Route::resource » et lesquelles peux-tu retirer avec « only » ?
- Quel est le circuit d'une écriture, du middleware à la redirection ?
- Pourquoi redirige-t-on après un POST ?
- Pourquoi faut-il un « ignore » dans la règle « unique » d'une mise à jour ?
- Comment empêcher un utilisateur de modifier la ressource d'un autre ?

## À retenir

- Un CRUD relie routes, contrôleur, validation, autorisation et interface.
- Valide avec un Form Request, autorise avec une policy, enregistre avec « validated ».
- Redirige toujours après une écriture et confirme par un message flash.
- Filtre les listes par propriétaire et autorise chaque action ciblée.
- Le schéma se réutilise tel quel pour toutes tes ressources.
MD,
            'code_example' => <<<'CODE'
<?php

// routes/web.php + app/Http/Controllers/RoadmapController.php (extraits réunis)

use App\Http\Controllers\RoadmapController;
use App\Http\Requests\StoreRoadmapRequest;
use App\Http\Requests\UpdateRoadmapRequest;
use App\Models\Roadmap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

// Étape 1 : route de ressource protégée, limitée aux actions utiles
Route::middleware('auth')->group(function () {
    Route::resource('roadmaps', RoadmapController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});

class RoadmapController extends Controller
{
    // Étape 2 : lecture, filtrée par propriétaire
    public function index(Request $request)
    {
        return Inertia::render('Roadmaps/Index', [
            'roadmaps' => $request->user()->roadmaps()->latest()->get(),
        ]);
    }

    // Étape 3 : création = validation (Form Request) + écriture + redirection
    public function store(StoreRoadmapRequest $request)
    {
        $request->user()->roadmaps()->create($request->validated());

        return redirect()->route('roadmaps.index')->with('success', 'Roadmap créée.');
    }

    // Étape 4 : modification = autorisation + validation + écriture
    public function update(UpdateRoadmapRequest $request, Roadmap $roadmap)
    {
        $this->authorize('update', $roadmap);
        $roadmap->update($request->validated());

        return back()->with('success', 'Roadmap mise à jour.');
    }

    // Étape 5 : suppression = autorisation + écriture + redirection
    public function destroy(Roadmap $roadmap)
    {
        $this->authorize('delete', $roadmap);
        $roadmap->delete();

        return redirect()->route('roadmaps.index')->with('success', 'Roadmap supprimée.');
    }
}
CODE,
            'estimated_minutes' => 80,
            'exercise_title' => 'CRUD des produits',
            'exercise_description' => <<<'MD'
Construis le CRUD « ProduitController » pour la boutique. Chaque produit appartient à l'utilisateur connecté (« user_id »). Les pages Inertia ne sont pas demandées : concentre-toi sur les routes et le contrôleur.

Critères de réussite :
- Les routes sont déclarées avec « Route::resource » limitée à « index », « store », « update » et « destroy », dans un groupe « auth ».
- « index » ne retourne que les produits de l'utilisateur connecté, via la relation.
- « store » et « update » utilisent des Form Requests distincts et enregistrent « validated() ».
- « update » et « destroy » appellent « authorize » avant toute modification.
- Chaque écriture se termine par une redirection avec un message flash « success ».
MD,
            'exercise_hint' => 'Suis le circuit : Form Request, authorize, écriture, redirect()->route(\'produits.index\')->with(\'success\', ...). Pour index, utilise $request->user()->produits()->latest()->get().',
            'exercise_solution' => <<<'CODE'
<?php

// routes/web.php
use App\Http\Controllers\ProduitController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::resource('produits', ProduitController::class)
        ->only(['index', 'store', 'update', 'destroy']);
});

// app/Http/Controllers/ProduitController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreProduitRequest;
use App\Http\Requests\UpdateProduitRequest;
use App\Models\Produit;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProduitController extends Controller
{
    // Uniquement les produits de l'utilisateur connecté
    public function index(Request $request)
    {
        return Inertia::render('Produits/Index', [
            'produits' => $request->user()->produits()->latest()->get(),
        ]);
    }

    public function store(StoreProduitRequest $request)
    {
        $request->user()->produits()->create($request->validated());

        return redirect()->route('produits.index')->with('success', 'Produit ajouté.');
    }

    public function update(UpdateProduitRequest $request, Produit $produit)
    {
        $this->authorize('update', $produit);
        $produit->update($request->validated());

        return redirect()->route('produits.index')->with('success', 'Produit modifié.');
    }

    public function destroy(Produit $produit)
    {
        $this->authorize('delete', $produit);
        $produit->delete();

        return redirect()->route('produits.index')->with('success', 'Produit supprimé.');
    }
}
CODE,
        ],

        'Projet final : construire un module' => [
            'description' => 'Réaliser un module complet de bout en bout : notes personnelles sur les étapes d\'une roadmap, avec base, sécurité et interface.',
            'objective' => 'Concevoir, sécuriser et tester un module Laravel complet qui réunit migration, modèle, relations, validation, policy, contrôleur, routes et page Inertia.',
            'content' => <<<'MD'
## Pourquoi cette notion

Jusqu'ici, tu as appris chaque brique séparément. Dans un vrai projet client, personne ne te demande « écris une migration » : on te demande « ajoute la possibilité de prendre des notes sur chaque étape ». Transformer une demande métier en un ensemble cohérent de fichiers est ce qui distingue un développeur autonome d'un simple exécutant. Ce projet final est aussi une pièce de portfolio que tu peux présenter à un employeur ou à un client.

Le module que tu vas construire s'appelle « Notes d'étape » : un utilisateur peut écrire, lire et supprimer des notes personnelles sur les étapes d'une roadmap. C'est volontairement simple dans son idée, pour que tu te concentres sur la qualité de l'assemblage.

## Les concepts clés

### Partir du besoin

Commence par écrire en trois phrases ce que le module doit faire et pour qui. Ici : un utilisateur connecté ajoute une note sur une étape, voit uniquement ses notes, et peut supprimer ses propres notes. Cette définition guide chaque choix technique et te sert de liste de contrôle à la fin.

### Le plan d'assemblage

Procède toujours dans le même ordre : la base de données d'abord (migration), puis les modèles et leurs relations, puis la validation et l'autorisation, puis le contrôleur et les routes, et enfin l'interface. Cet ordre te permet de tester chaque couche avant de passer à la suivante, et d'éviter de chercher un bug dans cinq fichiers à la fois.

### Penser sécurité dès le départ

Pose-toi trois questions pour chaque action : qui est connecté, que peut-il envoyer, a-t-il le droit sur cet enregistrement ? Ces trois questions correspondent au middleware « auth », au Form Request et à la policy. Une note ne doit jamais être visible ou modifiable par un autre utilisateur.

### Tester le module

Un test de fonctionnalité simule une requête HTTP complète et vérifie la réponse et la base. Tester qu'un utilisateur ne peut pas supprimer la note d'un autre est le test le plus important du module.

## Exemple pas à pas

L'exemple est un fichier de test qui décrit le comportement attendu du module et sert de modèle pour tes propres tests. L'étape 1 prépare deux utilisateurs et une étape de roadmap grâce aux factories. L'étape 2 vérifie qu'un utilisateur connecté peut créer une note et qu'elle apparaît en base. L'étape 3 vérifie qu'une note vide est refusée par la validation. L'étape 4 est le test clé : un second utilisateur reçoit une erreur 403 en essayant de supprimer la note du premier, et la note existe toujours. Lis ces trois comportements comme la définition de ce que ton module doit garantir.

## Erreurs fréquentes

- Commencer par l'interface : tu ne sais pas encore quelles données existent. Commence par la base et le modèle.
- Oublier la clé étrangère vers l'utilisateur : tu ne peux pas filtrer par propriétaire. Ajoute « user_id » et la relation.
- Ne tester que le cas heureux : les failles restent invisibles. Teste aussi le refus pour un autre utilisateur et la donnée invalide.
- Livrer sans relire la liste de contrôle : un critère est oublié. Relis le besoin initial à la fin.
- Mélanger plusieurs responsabilités dans le contrôleur : il devient illisible. Garde validation et autorisation dans leurs classes.
- Ne pas versionner ton travail avec des commits clairs : impossible de revenir en arrière. Commit après chaque couche terminée.

## Bonnes pratiques

- Écris le besoin et la liste de contrôle avant le premier fichier.
- Avance couche par couche et teste après chacune.
- Fais relire ton code par une autre personne ou relis-le à froid le lendemain.
- Documente le module dans un court fichier README avec les routes et les règles de sécurité.
- Garde ce projet propre : il fait partie de ton portfolio.

## Auto-évaluation

- Dans quel ordre assembles-tu les couches d'un module et pourquoi ?
- Quelles trois questions de sécurité poses-tu pour chaque action ?
- Quel test vérifie qu'un utilisateur ne peut pas toucher la note d'un autre ?
- Quel rôle jouent « validated » et « authorize » dans le contrôleur ?
- Comment prouves-tu que ton module respecte le besoin initial ?

## À retenir

- Un module part d'un besoin métier et se découpe en couches bien ordonnées.
- Base de données, modèles, validation, autorisation, contrôleur, routes, interface : dans cet ordre.
- La sécurité se pense à chaque action, pas à la fin.
- Les tests de refus sont aussi importants que les tests de succès.
- Un projet propre et documenté devient une pièce de portfolio.
MD,
            'code_example' => <<<'CODE'
<?php

// tests/Feature/StepNoteTest.php : comportement attendu du module « Notes d'étape »

namespace Tests\Feature;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\StepNote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StepNoteTest extends TestCase
{
    use RefreshDatabase;
    // Étape 1 : deux utilisateurs et une étape de roadmap
    private function preparer(): array
    {
        $auteur = User::factory()->create();
        $autre = User::factory()->create();
        $roadmap = Roadmap::factory()->create(['user_id' => $auteur->id]);
        $etape = RoadmapStep::factory()->create(['roadmap_id' => $roadmap->id]);

        return [$auteur, $autre, $etape];
    }

    // Étape 2 : un utilisateur connecté crée une note, elle est en base
    public function test_un_utilisateur_cree_une_note(): void
    {
        [$auteur, , $etape] = $this->preparer();

        $this->actingAs($auteur)
            ->post('/step-notes', ['roadmap_step_id' => $etape->id, 'body' => 'Revoir les policies'])
            ->assertRedirect();

        $this->assertDatabaseHas('step_notes', ['user_id' => $auteur->id, 'body' => 'Revoir les policies']);
    }

    // Étape 3 : une note vide est refusée par la validation
    public function test_une_note_vide_est_refusee(): void
    {
        [$auteur, , $etape] = $this->preparer();

        $this->actingAs($auteur)
            ->post('/step-notes', ['roadmap_step_id' => $etape->id, 'body' => ''])
            ->assertSessionHasErrors('body');
    }

    // Étape 4 : le test clé, un autre utilisateur ne peut pas supprimer la note
    public function test_un_autre_utilisateur_ne_peut_pas_supprimer(): void
    {
        [$auteur, $autre, $etape] = $this->preparer();
        $note = StepNote::create(['user_id' => $auteur->id, 'roadmap_step_id' => $etape->id, 'body' => 'Privé']);

        $this->actingAs($autre)->delete("/step-notes/{$note->id}")->assertForbidden();

        $this->assertDatabaseHas('step_notes', ['id' => $note->id]);
    }
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Mini-projet : module Notes d\'étape',
            'exercise_description' => <<<'MD'
Construis le module « Notes d'étape » dans DevRoad : un utilisateur connecté écrit, lit et supprime ses notes personnelles sur les étapes d'une roadmap. Dépose ton travail dans un dépôt Git avec des commits clairs.

Livrables :
- Une migration « step_notes » avec « user_id » et « roadmap_step_id » (clés étrangères avec suppression en cascade), « body » (texte) et les dates.
- Le modèle « StepNote » avec « $fillable » et les relations « user » et « step », plus la relation « notes » dans « RoadmapStep ».
- Un Form Request « StoreStepNoteRequest » : « body » obligatoire (2000 caractères maximum) et « roadmap_step_id » qui existe.
- Une « StepNotePolicy » : seul le propriétaire peut supprimer sa note.
- Un contrôleur avec « index » (notes de l'utilisateur chargées avec « with »), « store » et « destroy », ainsi que les routes protégées par « auth ».
- Une page Inertia « StepNotes/Index.jsx » qui liste les notes, contient un formulaire avec « useForm » et un bouton de suppression.
MD,
            'exercise_hint' => 'Avance couche par couche : migration, modèle, validation, policy, contrôleur et routes, puis page. Dans le contrôleur, suis le circuit validation, autorisation, écriture, redirection. Utilise with(\'step\') pour éviter le N+1.',
            'exercise_solution' => <<<'CODE'
<?php

// 1) database/migrations/2026_02_01_000000_create_step_notes_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('step_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('roadmap_step_id')->constrained()->cascadeOnDelete();
            $table->text('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('step_notes');
    }
};

// 2) app/Models/StepNote.php
// Ajoute aussi : RoadmapStep::notes() et User::stepNotes() en hasMany(StepNote::class)
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StepNote extends Model
{
    protected $fillable = ['user_id', 'roadmap_step_id', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function step(): BelongsTo
    {
        return $this->belongsTo(RoadmapStep::class, 'roadmap_step_id');
    }
}

// 3) app/Http/Requests/StoreStepNoteRequest.php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreStepNoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'roadmap_step_id' => ['required', 'exists:roadmap_steps,id'],
            'body' => ['required', 'string', 'max:2000'],
        ];
    }
}

// 4) app/Policies/StepNotePolicy.php : seul le propriétaire supprime
namespace App\Policies;

use App\Models\StepNote;
use App\Models\User;

class StepNotePolicy
{
    public function delete(User $user, StepNote $note): bool
    {
        return $user->id === $note->user_id;
    }
}

// 5) app/Http/Controllers/StepNoteController.php
namespace App\Http\Controllers;

use App\Http\Requests\StoreStepNoteRequest;
use App\Models\StepNote;
use Illuminate\Http\Request;
use Inertia\Inertia;

class StepNoteController extends Controller
{
    public function index(Request $request)
    {
        return Inertia::render('StepNotes/Index', [
            // with('step') : évite le problème N+1
            'notes' => StepNote::with('step:id,title')
                ->where('user_id', $request->user()->id)
                ->latest()
                ->get(),
        ]);
    }

    public function store(StoreStepNoteRequest $request)
    {
        $request->user()->stepNotes()->create($request->validated());

        return redirect()->route('step-notes.index')->with('success', 'Note ajoutée.');
    }

    public function destroy(StepNote $stepNote)
    {
        $this->authorize('delete', $stepNote);
        $stepNote->delete();

        return redirect()->route('step-notes.index')->with('success', 'Note supprimée.');
    }
}

// 6) routes/web.php
// Route::middleware('auth')->group(function () {
//     Route::resource('step-notes', StepNoteController::class)->only(['index', 'store', 'destroy']);
// });

// 7) resources/js/Pages/StepNotes/Index.jsx
// import { Head, router, useForm } from '@inertiajs/react';
//
// export default function Index({ notes }) {
//     const { data, setData, post, processing, errors, reset } = useForm({ roadmap_step_id: '', body: '' });
//     const submit = (e) => { e.preventDefault(); post('/step-notes', { onSuccess: () => reset('body') }); };
//
//     return (
//         <>
//             <Head title="Mes notes" />
//             <form onSubmit={submit}>
//                 <input value={data.roadmap_step_id} onChange={(e) => setData('roadmap_step_id', e.target.value)} placeholder="Numéro de l'étape" />
//                 <textarea value={data.body} onChange={(e) => setData('body', e.target.value)} />
//                 {errors.body && <p role="alert">{errors.body}</p>}
//                 <button type="submit" disabled={processing}>Ajouter</button>
//             </form>
//             <ul>
//                 {notes.map((n) => (
//                     <li key={n.id}>
//                         {n.step?.title} : {n.body}
//                         <button onClick={() => router.delete(`/step-notes/${n.id}`)}>Supprimer</button>
//                     </li>
//                 ))}
//             </ul>
//         </>
//     );
// }
CODE,
        ],

    ],
];
