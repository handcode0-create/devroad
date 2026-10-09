---
title: Découvrir Laravel
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Dans ce chapitre, tu pars de zéro. À la fin, tu sauras expliquer ce qu'est Laravel, pourquoi on l'utilise, comment une requête web traverse une application, et tu auras déjà écrit tes premières routes.

À la fin du chapitre, tu seras capable de :

- expliquer en trois phrases ce qu'est un framework et ce que Laravel t'apporte ;
- décrire le trajet d'une requête, du navigateur jusqu'à la réponse ;
- reconnaître le rôle de chaque grand dossier d'un projet Laravel ;
- lancer des commandes `artisan` et lire la liste de tes routes ;
- lire une valeur de configuration sans jamais écrire un mot de passe dans ton code.

Prévois environ deux heures. Les prérequis : savoir lire du PHP de base (variables, tableaux, fonctions) et connaître l'idée générale d'une page web. Si tu hésites sur la notion de classe, ne t'inquiète pas : on la reverra au fil des chapitres.

> **Astuce** : lis ce chapitre avec un terminal ouvert à côté. Chaque exemple est fait pour être tapé, pas seulement lu. On retient dix fois mieux ce qu'on a écrit soi-même.

## Pourquoi un framework ?

Imagine que tu veuilles construire DevRoad, une application où chaque développeur suit ses parcours d'apprentissage. Sans framework, tu dois résoudre toi-même une longue liste de problèmes avant même d'écrire la première fonctionnalité utile :

- lire l'adresse demandée et décider quel code exécuter ;
- te connecter à la base de données de façon sûre ;
- vérifier les données envoyées par un formulaire ;
- gérer les comptes, les mots de passe et les sessions ;
- te protéger des failles classiques (injection SQL, falsification de formulaire, vol de session).

Un **framework** est une boîte à outils déjà assemblée qui résout ces problèmes de la même manière dans tous les projets. Tu ne réinventes plus la roue : tu te concentres sur ce qui rend ton application unique.

Laravel est le framework PHP le plus utilisé aujourd'hui. Il est apprécié pour trois raisons :

1. **Une organisation claire** : chaque type de code a sa place, donc un projet Laravel que tu n'as jamais vu reste lisible.
2. **Des outils fournis** : routage, base de données, validation, authentification, files d'attente, e-mails.
3. **Une grande communauté** : documentation soignée, tutoriels, paquets, et beaucoup d'entreprises qui recrutent sur cette technologie.

### Laravel suit le modèle MVC

Laravel organise le code selon le modèle **MVC** (Modèle, Vue, Contrôleur). L'idée est de séparer trois responsabilités :

- le **Modèle** gère les données (par exemple une roadmap et ses étapes) et parle à la base de données ;
- la **Vue** décide de ce que voit l'utilisateur (la page, les boutons, les textes) ;
- le **Contrôleur** reçoit la demande, demande les données au modèle, puis choisit la vue à renvoyer.

Prends l'exemple d'un restaurant. Le client passe commande au serveur (le contrôleur). Le serveur demande le plat à la cuisine (le modèle), puis le dépose sur la table dans une belle assiette (la vue). Chacun fait son métier : si la présentation change, la cuisine n'a rien à modifier.

Dans DevRoad, l'interface est écrite avec React et reliée à Laravel par Inertia. La vue n'est donc pas un fichier Blade, mais un composant React. Le principe reste identique : le contrôleur prépare les données, l'interface les affiche.

:::quiz
Dans le modèle MVC, quel élément a pour rôle de parler à la base de données ?
- [ ] La vue
- [x] Le modèle
- [ ] La route
- [ ] Le navigateur
> Le modèle représente les données et communique avec la base. La vue affiche, le contrôleur coordonne.
:::

## Le trajet d'une requête

Comprendre ce trajet est la clé de tout le reste. Chaque fois que quelqu'un ouvre une page de ton application, il se passe toujours la même chose, dans le même ordre.

1. Le navigateur envoie une **requête HTTP** (par exemple `GET /roadmaps`).
2. Le serveur web la transmet à un unique fichier d'entrée : `public/index.php`.
3. Ce fichier démarre l'application Laravel, qui charge sa configuration.
4. La requête traverse les **middlewares**, des filtres qui s'exécutent avant ton code (est-il connecté ? le jeton de sécurité est-il valide ?).
5. Le **routeur** compare l'adresse à la liste des routes déclarées et choisit laquelle s'applique.
6. La route appelle un **contrôleur**, qui travaille avec les modèles.
7. Le contrôleur renvoie une **réponse** (une page, du JSON, une redirection) qui repasse par les middlewares puis retourne au navigateur.

Retiens une chose : **tout passe par `public/index.php`**. C'est pour cette raison que seul le dossier `public` doit être exposé sur internet. Le reste de ton code (configuration, mots de passe, logique métier) reste inaccessible depuis un navigateur.

> **À retenir** : une application Laravel est une chaîne. Requête, middlewares, route, contrôleur, réponse. Quand quelque chose ne marche pas, la première question est toujours : à quel maillon de la chaîne ça casse ?

:::quiz
Quel est le seul point d'entrée des requêtes web dans un projet Laravel ?
- [ ] routes/web.php
- [ ] app/Http/Controllers
- [x] public/index.php
- [ ] .env
> Toutes les requêtes arrivent à public/index.php. Le fichier routes/web.php, lui, déclare les adresses une fois l'application démarrée.
:::

## Visiter un projet Laravel

Ouvre un projet Laravel (ou celui de DevRoad) et regarde la racine. Voici les dossiers que tu croiseras tout le temps.

| Dossier ou fichier | À quoi il sert |
| --- | --- |
| `app/` | Le cœur de ton code : modèles, contrôleurs, règles de validation, politiques d'accès. |
| `bootstrap/` | Le démarrage de l'application et ses réglages globaux (middlewares, routes). |
| `config/` | Les fichiers de configuration, un par sujet (base de données, e-mail, session…). |
| `database/` | Les migrations (structure des tables), les seeders et les factories. |
| `public/` | Le seul dossier visible depuis internet : `index.php`, images, fichiers compilés. |
| `resources/` | Les vues, les composants React, les fichiers CSS et les traductions. |
| `routes/` | Les fichiers qui déclarent les adresses de l'application. |
| `storage/` | Les fichiers générés : logs, cache, fichiers envoyés par les utilisateurs. |
| `tests/` | Les tests automatiques. |
| `vendor/` | Les paquets installés par Composer. On n'y touche jamais. |
| `.env` | Les réglages propres à chaque machine et les secrets. |

Ne cherche pas à tout retenir d'un coup. Dans les prochains chapitres, tu vas travailler dans `routes/`, `app/` et `database/`, et tu apprendras à connaître chacun naturellement.

> **Attention** : le dossier `vendor/` et le fichier `.env` ne se publient jamais dans un dépôt Git. Le premier se recrée avec Composer, le second contient tes secrets.

## Artisan, ton assistant en ligne de commande

Laravel est livré avec un outil appelé **artisan**. Il se lance dans le terminal, à la racine du projet, avec `php artisan`. Il génère du code, lance des tâches et répond à des questions sur ton application.

Commence par afficher tout ce qu'il sait faire :

```bash
php artisan list
```

Voici les commandes que tu utiliseras le plus souvent :

```bash
# Démarrer un serveur de développement
php artisan serve

# Afficher la liste de toutes les routes de l'application
php artisan route:list

# Créer un contrôleur, un modèle, une migration…
php artisan make:controller RoadmapController
php artisan make:model Roadmap
php artisan make:migration create_roadmaps_table

# Ouvrir une console pour essayer du code PHP dans ton application
php artisan tinker
```

Le préfixe `make:` est ton meilleur ami : il crée le fichier au bon endroit, avec la bonne structure de départ. Tu gagnes du temps et tu évites les fautes de frappe dans les noms de dossiers.

Pour savoir comment utiliser une commande précise, ajoute `--help` :

```bash
php artisan make:controller --help
```

> **Astuce** : `php artisan tinker` est un terrain de jeu idéal. Tu peux y tester une idée en deux lignes sans créer de page ni de route.

:::quiz
Quelle commande affiche toutes les routes déclarées dans l'application ?
- [ ] php artisan serve
- [x] php artisan route:list
- [ ] php artisan make:route
- [ ] php artisan tinker
> `route:list` liste chaque route avec sa méthode, son adresse et sa destination. C'est la première commande à lancer pour comprendre une application inconnue.
:::

## Écrire tes premières routes

Une **route** associe une adresse (et une méthode HTTP) à un morceau de code. Les routes web se déclarent dans le fichier `routes/web.php`.

Voici la plus simple possible :

```php
<?php

use Illuminate\Support\Facades\Route;

// Quand quelqu'un visite /bonjour, on répond par ce texte.
Route::get('/bonjour', function () {
    return 'Bonjour depuis DevRoad !';
});
```

Lance `php artisan serve`, puis ouvre `http://127.0.0.1:8000/bonjour` dans ton navigateur. Le texte s'affiche : tu viens de créer ta première page.

### Retourner du JSON

Si une route retourne un tableau PHP, Laravel le convertit automatiquement en JSON. C'est la base d'une API :

```php
Route::get('/api-test', function () {
    return [
        'application' => 'DevRoad',
        'parcours' => ['Laravel', 'React', 'Docker'],
    ];
});
```

### Utiliser un paramètre dans l'adresse

Les accolades déclarent une partie variable de l'adresse. Laravel la transmet à ta fonction :

```php
Route::get('/parcours/{nom}', function (string $nom) {
    return "Tu consultes le parcours : {$nom}";
});
```

Visite `/parcours/Laravel` : la page affiche « Tu consultes le parcours : Laravel ». Dans un vrai projet, ce paramètre servira à retrouver un enregistrement en base de données. Tu le feras au chapitre sur les routes.

> **Erreur fréquente** : oublier le `/` du début, ou écrire la route dans un autre fichier que `routes/web.php`. Si une adresse renvoie une erreur 404, lance `php artisan route:list` : si ta route n'y figure pas, Laravel ne la connaît pas.

## Configuration et fichier .env

Ton application ne tourne pas au même endroit partout. Sur ton ordinateur, elle utilise une base de données locale. En production, elle en utilise une autre, avec un mot de passe différent. Il faut donc séparer **le code**, identique partout, et **la configuration**, propre à chaque machine.

Laravel utilise pour cela deux niveaux :

- le fichier `.env` à la racine, qui contient les valeurs propres à la machine ;
- les fichiers du dossier `config/`, qui lisent ces valeurs et les rendent disponibles dans l'application.

Voici un extrait typique de `.env` :

```bash
APP_NAME=DevRoad
APP_ENV=local
APP_DEBUG=true
DB_CONNECTION=mysql
DB_DATABASE=devroad
DB_USERNAME=root
DB_PASSWORD=
```

Dans ton code, tu ne lis jamais `.env` directement. Tu passes par la fonction `config()` :

```php
// Lit la clé "name" du fichier config/app.php
$nom = config('app.name');
```

Le fichier `config/app.php` contient lui-même, quelque part, l'appel `env('APP_NAME', 'Laravel')`. C'est le seul endroit où l'on utilise `env()`. Pourquoi ? Parce qu'en production Laravel met la configuration en cache pour aller plus vite, et `env()` hors des fichiers de config cesse alors de fonctionner.

> **Attention** : `APP_DEBUG=true` affiche les détails de tes erreurs, y compris des informations sensibles. Passe-le à `false` en production, sans exception.

:::quiz
Où doit-on utiliser la fonction env() ?
- [ ] Dans les contrôleurs
- [ ] Dans les vues
- [x] Uniquement dans les fichiers du dossier config/
- [ ] Partout, c'est équivalent à config()
> Dans le reste du code, on lit les valeurs avec config(). La configuration est mise en cache en production, et env() ne marche plus en dehors des fichiers de config.
:::

## Atelier guidé : ta première mini-application

Tu vas assembler tout le chapitre dans un petit atelier. Compte trente minutes.

1. Dans le terminal, vérifie que tu es à la racine du projet, puis lance `php artisan route:list` et note le nombre de routes existantes.
2. Ouvre `routes/web.php` et ajoute une route `/a-propos` qui retourne le texte « DevRoad, ton compagnon d'apprentissage ».
3. Ajoute une route `/stack` qui retourne un tableau avec les clés `backend` (valeur `Laravel`) et `frontend` (valeur `React`).
4. Ajoute une route `/bienvenue/{prenom}` qui retourne `Bienvenue, {prenom} !` avec le prénom reçu.
5. Relance `php artisan route:list` : tes trois routes doivent apparaître dans la liste.
6. Lance `php artisan serve` et teste chacune des trois adresses dans le navigateur.
7. Dans `.env`, change `APP_NAME` puis affiche `config('app.name')` dans une quatrième route. Si la valeur ne change pas, essaie `php artisan config:clear`.

Quand tout fonctionne, pose-toi trois questions pour vérifier ta compréhension :

- Sur quel fichier arrive la requête avant d'atteindre ta route ?
- Pourquoi ne mets-tu pas ton mot de passe de base de données directement dans une route ?
- Que fais-tu quand une adresse renvoie une erreur 404 ?

## Erreurs fréquentes

- **Modifier `.env` sans voir d'effet.** Si la configuration est en cache, vide-la avec `php artisan config:clear`.
- **Chercher une route qui n'existe pas.** Vérifie toujours avec `php artisan route:list` avant de soupçonner un bug plus profond.
- **Lancer artisan depuis le mauvais dossier.** La commande `php artisan` ne fonctionne qu'à la racine du projet, là où se trouve le fichier `artisan`.
- **Toucher au dossier `vendor/`.** Tout ce que tu y changes est écrasé à la prochaine installation.
- **Oublier `<?php` en haut d'un fichier PHP.** Sans cette ouverture, le code s'affiche en texte brut.

## Bonnes pratiques

- Lis les messages d'erreur en entier : Laravel est très explicite sur la ligne et la cause.
- Utilise `php artisan make:...` plutôt que de créer les fichiers à la main.
- Ne mets jamais de secret dans le code ni dans Git : tout passe par `.env`.
- Garde `APP_DEBUG=false` dès que l'application est accessible à d'autres personnes.
- Prends l'habitude de consulter la documentation officielle de Laravel : elle est claire, à jour, et c'est la source de référence.

## À retenir

- Un framework fournit une structure et des outils pour ne pas tout réécrire.
- Laravel suit le modèle MVC : le modèle gère les données, la vue l'affichage, le contrôleur coordonne.
- Une requête traverse toujours la même chaîne : `public/index.php`, middlewares, route, contrôleur, réponse.
- `routes/web.php` déclare les adresses ; `php artisan route:list` les affiche.
- `php artisan` génère du code et lance des tâches ; `php artisan serve` démarre l'application en local.
- Les secrets vivent dans `.env` et se lisent avec `config()`, jamais avec `env()` hors du dossier `config/`.
