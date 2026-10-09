---
title: Installer et lancer Laravel
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Ce chapitre te mène de « je n'ai rien d'installé » à « mon application Laravel tourne dans mon navigateur ». C'est l'étape qui bloque le plus de débutants, alors on la prend calmement, en expliquant chaque commande et en listant les erreurs que tu risques de rencontrer.

À la fin du chapitre, tu seras capable de :

- vérifier que PHP, Composer, Node.js et une base de données sont prêts ;
- créer un projet Laravel avec Composer ;
- configurer le fichier `.env` et créer ta base de données ;
- installer l'interface (Breeze avec Inertia et React) et lancer le serveur de développement ;
- diagnostiquer les cinq erreurs d'installation les plus courantes.

Prévois environ deux heures, en comptant les téléchargements. Si tu travailles sous Windows avec XAMPP, tu trouveras des remarques dédiées dans chaque partie.

## Les outils dont Laravel a besoin

Laravel n'est pas un programme que l'on installe comme un logiciel. C'est un ensemble de fichiers PHP qui tournent grâce à plusieurs outils. Avant de créer ton projet, vérifie que chacun est présent.

| Outil | Rôle | Version attendue |
| --- | --- | --- |
| PHP | Exécute le code de l'application | 8.2 ou plus |
| Composer | Installe les paquets PHP (dont Laravel) | 2.x |
| Node.js et npm | Compile l'interface React avec Vite | Node 20 ou plus |
| Base de données | Stocke les données (MySQL, MariaDB, PostgreSQL ou SQLite) | au choix |
| Git | Sauvegarde et partage ton code | récent |

Pour contrôler ce que tu as déjà, ouvre un terminal et tape :

```bash
php -v
composer -V
node -v
npm -v
git --version
```

Chaque commande doit afficher un numéro de version. Si l'une répond « commande introuvable », l'outil n'est pas installé, ou son dossier n'est pas dans la variable d'environnement `PATH`.

> **Astuce** : sous Windows, XAMPP installe PHP et MySQL ensemble. PHP se trouve dans `C:\xampp\php`. Ajoute ce dossier au `PATH` de Windows pour que `php -v` fonctionne depuis n'importe quel terminal, puis ferme et rouvre le terminal.

### Les extensions PHP indispensables

Laravel exige quelques extensions PHP. Si l'une manque, Composer le signale pendant l'installation. Les principales sont `mbstring`, `openssl`, `pdo_mysql` (ou `pdo_pgsql`, `pdo_sqlite`), `fileinfo`, `tokenizer`, `xml`, `ctype` et `json`. Avec XAMPP, elles sont presque toutes actives. Si l'une manque, ouvre `php.ini`, supprime le point-virgule devant la ligne `extension=nom`, puis redémarre.

Pour voir ce qui est actif :

```bash
php -m
```

:::quiz
Tu tapes `php -v` et le terminal répond « commande introuvable ». Quelle est la cause la plus probable ?
- [ ] Laravel n'est pas installé
- [x] PHP n'est pas installé, ou son dossier n'est pas dans le PATH
- [ ] La base de données est arrêtée
- [ ] Le fichier .env est manquant
> Cette commande ne dépend pas de Laravel. Elle cherche simplement l'exécutable PHP : s'il est introuvable, il faut l'installer ou l'ajouter au PATH.
:::

## Créer le projet

Place-toi dans le dossier où tu ranges tes projets (avec XAMPP, `C:\xampp\htdocs` fonctionne, mais n'importe quel dossier convient avec le serveur intégré de Laravel). Puis lance :

```bash
composer create-project laravel/laravel devroad
cd devroad
```

Composer télécharge Laravel et toutes ses dépendances dans le dossier `vendor/`. Cela peut prendre quelques minutes la première fois. À la fin, regarde ce qu'il a fait pour toi : un fichier `.env` a été créé à partir de `.env.example`, et une clé d'application a été générée.

Vérifie que l'installation est saine :

```bash
php artisan --version
```

Tu dois voir « Laravel Framework 12.x ». Si c'est le cas, le cœur fonctionne.

### La clé d'application

Dans `.env`, la ligne `APP_KEY=` contient une longue clé secrète. Laravel s'en sert pour chiffrer les sessions et les cookies. Si elle est vide, tu verras l'erreur « No application encryption key has been specified ». Corrige-la avec :

```bash
php artisan key:generate
```

> **Attention** : ne partage jamais ton `APP_KEY` de production, et ne la change pas sur un site en ligne sans raison : les sessions existantes deviendraient illisibles.

## Configurer la base de données

Ouvre le fichier `.env` et trouve la section de la base de données. Voici la configuration pour MySQL avec XAMPP :

```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=devroad
DB_USERNAME=root
DB_PASSWORD=
```

Avec XAMPP, l'utilisateur `root` n'a par défaut aucun mot de passe : laisse `DB_PASSWORD` vide. En production, ce serait une très mauvaise idée.

Laravel ne crée pas la base à ta place. Tu dois la créer toi-même :

1. Démarre MySQL depuis le panneau de contrôle de XAMPP.
2. Ouvre `http://localhost/phpmyadmin`.
3. Clique sur « Nouvelle base de données », nomme-la `devroad`, choisis l'interclassement `utf8mb4_unicode_ci`, puis valide.

Une alternative très simple pour débuter est SQLite : aucun serveur à lancer, la base est un simple fichier. Il suffit de mettre `DB_CONNECTION=sqlite` et de supprimer les autres lignes `DB_`. Laravel crée le fichier `database/database.sqlite` au besoin.

Teste maintenant la connexion en créant les tables de départ :

```bash
php artisan migrate
```

Si tout va bien, Laravel affiche une liste de migrations avec la mention `DONE`. Tu viens de créer les tables `users`, `cache` et `jobs`. Ouvre phpMyAdmin pour les voir de tes yeux.

:::quiz
`php artisan migrate` affiche « Connection refused ». Que vérifies-tu en premier ?
- [ ] Que le projet est bien dans le dossier htdocs
- [x] Que le serveur MySQL est démarré et que le port et les identifiants du .env sont corrects
- [ ] Que Node.js est à jour
- [ ] Que APP_DEBUG vaut true
> Cette erreur signifie que Laravel n'arrive pas à joindre le serveur de base de données : serveur arrêté, mauvais port ou mauvais identifiants.
:::

## Installer l'interface avec Breeze

Un projet Laravel vierge n'a ni page de connexion ni interface. **Laravel Breeze** est un kit de démarrage officiel qui ajoute l'inscription, la connexion, la réinitialisation du mot de passe et un tableau de bord. DevRoad l'utilise avec Inertia et React.

```bash
composer require laravel/breeze --dev
php artisan breeze:install react
```

L'installateur te pose quelques questions (tests, mode sombre, rendu côté serveur). Pour apprendre, accepte les choix par défaut. Il ajoute les routes d'authentification, les contrôleurs, les pages React dans `resources/js/Pages` et configure Vite. Termine avec la création des tables d'authentification :

```bash
php artisan migrate
npm install
```

La commande `npm install` télécharge les paquets JavaScript dans `node_modules/`. Comme `vendor/`, ce dossier ne se publie jamais dans Git.

> **À retenir** : Composer gère les paquets PHP (dossier `vendor/`), npm gère les paquets JavaScript (dossier `node_modules/`). Deux gestionnaires, deux dossiers, deux fichiers de verrouillage : `composer.lock` et `package-lock.json`.

## Lancer l'application

Une application Laravel avec React demande **deux processus** en parallèle : le serveur PHP, et Vite, qui compile le JavaScript et le met à jour à chaque sauvegarde. Ouvre deux terminaux dans le dossier du projet.

Terminal 1, le serveur PHP :

```bash
php artisan serve
```

Terminal 2, Vite :

```bash
npm run dev
```

Ouvre ensuite `http://127.0.0.1:8000`. Tu dois voir la page d'accueil de Laravel, avec des liens « Log in » et « Register ». Crée un compte, connecte-toi, et tu arrives sur un tableau de bord : tout fonctionne.

Les projets récents proposent aussi un raccourci qui lance tout en une commande :

```bash
composer run dev
```

Elle démarre le serveur, la file d'attente et Vite ensemble. Pratique, mais garde en tête qu'il y a bien plusieurs processus derrière.

### Développement et production

En développement, `npm run dev` sert le JavaScript à la volée. Pour la production, on construit des fichiers optimisés une fois pour toutes :

```bash
npm run build
```

Cette commande crée le dossier `public/build/`. Si tu l'oublies en production, tu verras l'erreur « Vite manifest not found ».

## Les cinq erreurs que tu vas probablement rencontrer

1. **« No application encryption key has been specified »** : lance `php artisan key:generate`.
2. **« could not find driver »** : l'extension PDO de ta base de données n'est pas activée dans `php.ini`.
3. **« SQLSTATE[HY000] [1049] Unknown database »** : la base n'existe pas encore. Crée-la dans phpMyAdmin et vérifie `DB_DATABASE`.
4. **« Vite manifest not found »** : lance `npm run dev` (développement) ou `npm run build` (production).
5. **« Address already in use »** : le port 8000 est occupé. Lance `php artisan serve --port=8001`.

> **Erreur fréquente** : modifier le `.env` et ne voir aucun changement. Si la configuration est en cache, vide-la avec `php artisan config:clear`, puis relance le serveur.

:::quiz
Tu vois la page de Laravel mais tout est sans style, et l'erreur parle de « Vite manifest ». Que manque-t-il ?
- [ ] Une migration
- [ ] Une clé d'application
- [x] Le processus Vite (npm run dev) ou le build (npm run build)
- [ ] Un utilisateur dans la base
> Les fichiers CSS et JavaScript sont produits par Vite. Sans `npm run dev` ni `npm run build`, la page ne trouve pas ses ressources.
:::

## Sauvegarder avec Git

Termine par une bonne habitude : versionne ton projet dès le premier jour. Laravel fournit déjà un fichier `.gitignore` qui exclut `vendor/`, `node_modules/` et `.env`.

```bash
git init
git add .
git commit -m "Installation de Laravel et Breeze"
```

Vérifie que `.env` n'est **pas** dans le commit avec `git status` avant de pousser quoi que ce soit en ligne.

## Atelier guidé : ton environnement complet

Compte une heure. Suis les étapes dans l'ordre et note l'erreur éventuelle de chaque étape.

1. Vérifie les versions de `php`, `composer`, `node` et `git`.
2. Crée le projet : `composer create-project laravel/laravel devroad-atelier`.
3. Crée la base `devroad_atelier` (ou choisis SQLite).
4. Renseigne le fichier `.env`, puis lance `php artisan migrate`.
5. Installe Breeze avec React, puis `npm install`.
6. Lance `php artisan serve` et `npm run dev` dans deux terminaux.
7. Crée un compte depuis le navigateur, puis ouvre phpMyAdmin et retrouve ton utilisateur dans la table `users`.
8. Initialise Git et fais un premier commit.

Pour t'auto-évaluer : si tu retrouves ton compte dans la table `users`, tu as compris tout le circuit, du navigateur jusqu'à la base de données.

## Bonnes pratiques

- Garde une base de données distincte pour chaque projet.
- Ne commite jamais `.env`. Maintiens plutôt un fichier `.env.example` à jour, sans secrets, pour que tes collègues sachent quelles variables définir.
- Lis le message d'erreur en entier avant de chercher une solution : il contient presque toujours la réponse.
- Utilise des versions récentes de PHP et de Node : Laravel évolue vite.
- Après une modification de configuration, vide le cache avec `php artisan optimize:clear`.

## À retenir

- Laravel a besoin de PHP 8.2 ou plus, de Composer, de Node.js et d'une base de données.
- `composer create-project` crée l'application, `php artisan key:generate` fournit la clé secrète.
- La base de données se crée à la main, ses identifiants se renseignent dans `.env`, puis `php artisan migrate` crée les tables.
- Breeze ajoute l'authentification et l'interface ; Composer gère PHP, npm gère JavaScript.
- Une application Inertia et React demande deux processus : `php artisan serve` et `npm run dev`.
- En production, on compile avec `npm run build`.
