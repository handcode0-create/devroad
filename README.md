# DevRoad

> **Ton parcours de développeur, dans une seule application.**

DevRoad est une plateforme d’apprentissage destinée aux développeurs qui veulent **apprendre, pratiquer et progresser dans un même environnement**.

Le projet combine des **roadmaps pédagogiques**, des **leçons structurées**, des **exercices**, des **mémos** et un **DevLab** intégré servant d’environnement de développement dans le navigateur.

![DevRoad](https://raw.githubusercontent.com/handcode0-create/devroad/main/public/logo.png)

## Vision

DevRoad cherche à rapprocher deux choses qui sont souvent séparées :

**apprendre une technologie** et **écrire réellement du code**.

Le parcours suit donc une logique :

```text
Cours
  ↓
Leçon
  ↓
Exercice
  ↓
DevLab
  ↓
Code
  ↓
Exécution / aperçu
  ↓
Validation
  ↓
Progression
```

## Fonctionnalités

### Roadmaps

Les utilisateurs peuvent créer des parcours d'apprentissage autour de différentes technologies.

Les technologies actuellement référencées dans DevRoad comprennent notamment :

- Laravel
- PHP
- Next.js
- React
- JavaScript
- TypeScript
- HTML
- CSS
- Tailwind CSS
- Node.js
- Git
- GitHub
- Docker
- MySQL
- PostgreSQL

### Expérience d'apprentissage

Chaque roadmap est composée d'étapes ordonnées avec :

- progression ;
- étape courante ;
- verrouillage des étapes futures ;
- suivi de la dernière consultation ;
- exercices associés ;
- validation de l'exercice avant progression lorsque nécessaire ;
- passage automatique à l'étape suivante.

### DevLab

DevLab est l'environnement de développement intégré de DevRoad.

Il est pensé dans l'esprit des IDE mobiles comme **TrebEdit**, tout en étant intégré directement au parcours pédagogique.

Le workspace comprend :

- explorateur de fichiers ;
- onglets de fichiers ;
- éditeur de code ;
- création de fichiers ;
- import de fichiers ;
- sauvegarde locale du workspace ;
- terminal pédagogique ;
- aperçu intégré pour le Web ;
- sélection de runtime.

Les runtimes actuellement pris en charge dans l'architecture sont :

| Environnement | Mode |
|---|---|
| HTML | Navigateur |
| CSS | Navigateur |
| JavaScript | Navigateur |
| Node.js | Runtime local |
| PHP | Runtime local |
| Laravel | Runtime local |

L'architecture du runtime est extensible pour ajouter d'autres environnements par la suite.

### Mémos

Une leçon peut générer directement un mémo afin de conserver :

- des notes ;
- des explications ;
- des extraits de code ;
- des points importants à retenir.

### Profil et préférences

Le profil permet notamment de gérer :

- informations du compte ;
- technologie prioritaire ;
- objectif quotidien ;
- objectif hebdomadaire ;
- notifications ;
- rappels d'apprentissage ;
- aide et support.

## Stack technique

### Backend

- Laravel 12
- PHP 8.2+
- SQLite en développement / environnement configurable
- Eloquent
- Form Requests
- Policies
- Inertia

### Frontend

- React
- Inertia React
- Vite
- Tailwind CSS
- Lucide React

### DevLab

Le DevLab combine un éditeur navigateur et des runtimes adaptés au type de projet.

L'exécution locale s'appuie actuellement sur les outils disponibles sur la machine hôte pour les runtimes concernés. Une architecture sandboxée pourra être utilisée pour un déploiement de production.

## Installation

Cloner le projet :

```powershell
git clone https://github.com/handcode0-create/devroad.git
cd devroad
```

Installer les dépendances PHP :

```powershell
composer install
```

Installer les dépendances JavaScript :

```powershell
npm install
```

Créer le fichier d'environnement :

```powershell
copy .env.example .env
php artisan key:generate
```

Lancer les migrations :

```powershell
php artisan migrate
```

Construire les assets :

```powershell
npm run build
```

Lancer le serveur Laravel :

```powershell
php artisan serve
```

Puis ouvrir :

```text
http://127.0.0.1:8000
```

## Tester

Suite complète :

```powershell
php artisan test
```

Tests du parcours d'apprentissage et du runtime :

```powershell
php artisan test --filter=RoadmapStepTest
```

## Runtime Node.js

Pour utiliser les fonctionnalités Node.js du DevLab en environnement local, Node.js et npm doivent être installés sur la machine hôte.

Vérifier :

```powershell
node --version
npm --version
```

Puis redémarrer le serveur Laravel après l'installation de Node.js.

## Architecture du workspace

Le DevLab conserve les fichiers du projet dans un workspace propre à l'utilisateur et à l'environnement de développement.

Le navigateur conserve également l'état d'édition local du workspace pour permettre de reprendre son travail.

## Arborescence principale

```text
app/
├── Http/
├── Models/
├── Policies/
└── Services/
    ├── CodeRunnerService.php
    ├── DevLabRuntimeService.php
    └── RoadmapGenerator.php

resources/
└── js/
    ├── Components/
    │   └── Learning/
    │       └── CodeWorkspace.jsx
    ├── Config/
    ├── Layouts/
    └── Pages/
        ├── DevLab/
        ├── Roadmaps/
        ├── Steps/
        └── Profile/

public/
└── logo.png

routes/
└── web.php
```

## État actuel du projet

Le projet possède actuellement un socle fonctionnel couvrant :

- authentification ;
- gestion des profils ;
- roadmaps ;
- progression pédagogique ;
- étapes verrouillées ;
- exercices ;
- mémos ;
- recherche ;
- DevLab ;
- IDE navigateur ;
- explorateur de fichiers ;
- import de fichiers ;
- runtimes Node.js / PHP / Laravel selon l'environnement.

La suite du développement porte notamment sur l'évolution de l'IDE vers une expérience mobile et desktop encore plus complète : coloration syntaxique avancée, gestion de projet multi-fichiers, console améliorée, preview Web et runtimes sandboxés pour la production.

## Licence

Projet en développement par **HANCODE STUDIO**.


## Déploiement de test — Railway + PostgreSQL

DevRoad est une application **Laravel full-stack avec Inertia + React**. React n'est pas déployé séparément : Laravel sert les routes, les contrôleurs, les réponses Inertia et les pages React.

Le premier environnement cloud recommandé pour le projet est **Railway avec PostgreSQL**.

### Pré-requis détectés

- Laravel 12
- PHP 8.2+
- Node.js 22.x
- npm 10.x
- PostgreSQL
- Vite 7
- Inertia.js 2 + React 18

### Build de production

Le build doit générer les assets dans `public/build` :

```bash
composer install --no-dev --optimize-autoloader
npm ci
npm run build
```

`public/build` reste ignoré par Git : il est généré pendant le build cloud.

### Variables Railway

Créer un service PostgreSQL dans le même projet Railway puis configurer le service DevRoad avec :

```text
APP_NAME=DevRoad
APP_ENV=production
APP_DEBUG=false
APP_KEY=<clé générée localement>
APP_URL=https://<domaine-railway>

DB_CONNECTION=pgsql
DB_URL=<URL PostgreSQL Railway>

SESSION_DRIVER=database
SESSION_SECURE_COOKIE=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=lax

CACHE_STORE=database
QUEUE_CONNECTION=database

FILESYSTEM_DISK=local

LOG_CHANNEL=stderr
LOG_LEVEL=error
```

**Ne mets jamais `APP_KEY`, un mot de passe PostgreSQL ou une clé privée dans Git.**

Railway peut également fournir une référence de variable vers PostgreSQL plutôt que de copier manuellement la chaîne de connexion.

### Commandes Railway

Dans **Settings → Build**, utiliser :

```text
composer install --no-dev --optimize-autoloader && npm ci && npm run build
```

Dans **Settings → Deploy → Pre-deploy Command** :

```bash
sh scripts/railway-predeploy.sh
```

Le script exécute :

```bash
php artisan optimize:clear
php artisan migrate --force
php artisan config:cache
php artisan view:cache
```

Aucun seeder n'est exécuté automatiquement.

Dans **Settings → Deploy → Start Command** :

```bash
php artisan serve --host=0.0.0.0 --port=$PORT
```

Le service doit écouter sur `0.0.0.0:$PORT`.

Health check recommandé :

```text
/up
```

Laravel expose déjà cette route dans `bootstrap/app.php`.

### HTTPS

Après le déploiement, utiliser **Networking → Generate Domain** dans Railway. Railway fournit alors une URL publique HTTPS.

Définir ensuite cette URL exacte dans :

```text
APP_URL=https://...
```

Puis redéployer.

### Base de données, sessions et cache

DevRoad utilise actuellement :

| Fonction | Driver | Production |
|---|---|---|
| Base de données | SQLite local / PostgreSQL disponible | PostgreSQL |
| Sessions | database | PostgreSQL |
| Cache | database | PostgreSQL |
| Queue | database | PostgreSQL |
| Fichiers | local | aucun stockage persistant requis actuellement |

Les migrations Laravel existantes créent notamment les tables nécessaires à :

- `sessions`
- `cache`
- `cache_locks`
- `jobs`
- `job_batches`
- `failed_jobs`

### Queues et scheduler

Aucun job `ShouldQueue` ni tâche planifiée applicative n'est actuellement utilisé par DevRoad.

Il n'est donc pas nécessaire de créer un worker ou un cron pour le premier déploiement.

Si des jobs sont ajoutés plus tard, prévoir un service Railway séparé avec :

```bash
php artisan queue:work
```

Si des tâches sont ajoutées au scheduler, prévoir un cron Railway exécutant :

```bash
php artisan schedule:run
```

### Uploads et stockage

Aucun upload serveur persistant n'a été identifié dans la version actuelle.

L'import de fichiers du DevLab est réalisé côté navigateur. Les fichiers d'exécution du runtime local sont écrits dans `storage/app/devlab`, mais ce runtime est volontairement désactivé hors environnement local.

Si DevRoad doit plus tard conserver des fichiers utilisateur dans le cloud, utiliser un stockage objet comme S3, Cloudflare R2 ou Supabase Storage plutôt que le filesystem local du conteneur.

### Limitation actuelle du DevLab en production

Le runtime serveur du DevLab vérifie actuellement `app()->isLocal()`.

Conséquence :

- HTML/CSS/preview navigateur : utilisables côté client ;
- éditeur et explorateur : utilisables ;
- PHP : runtime serveur indisponible en production ;
- Node.js : runtime serveur indisponible en production ;
- Laravel : sandbox serveur indisponible en production.

C'est volontaire. Autoriser directement l'exécution de code utilisateur sur le serveur Laravel de production serait une faille critique.

Pour activer un vrai DevLab cloud, il faudra une sandbox isolée par workspace/utilisateur, par exemple avec des conteneurs éphémères ou un service d'exécution dédié.

### Tests locaux avant déploiement

Exécuter obligatoirement :

```powershell
composer install
npm ci
npm run build
php artisan optimize:clear
php artisan migrate --force
php artisan route:list
```

Puis :

```powershell
php artisan test
```

Pour vérifier le serveur avec le même contrat de port :

```powershell
$env:PORT=8000
php artisan serve --host=0.0.0.0 --port=$env:PORT
```

Puis ouvrir :

```text
http://127.0.0.1:8000
```

### Diagnostic Railway

Si le build échoue :

```text
Build logs → vérifier Composer → npm ci → npm run build
```

Si la migration échoue :

```bash
php artisan migrate:status
php artisan migrate --force
```

Si Laravel répond en erreur :

```bash
php artisan optimize:clear
php artisan about
php artisan route:list
```

Si Railway indique qu'aucun port n'est ouvert, vérifier que le processus utilise exactement :

```text
0.0.0.0:$PORT
```

### Test sur téléphone

Après un déploiement réussi :

1. Générer le domaine public Railway.
2. Ouvrir l'URL `https://...` sur le téléphone.
3. Créer un compte DevRoad.
4. Vérifier la connexion.
5. Ouvrir le dashboard.
6. Ouvrir une roadmap.
7. Ouvrir une leçon.
8. Tester la progression et l'exercice.
9. Tester les mémos.
10. Ouvrir DevLab.
11. Tester l'éditeur et l'aperçu navigateur.
12. Vérifier qu'un rechargement complet d'une URL Inertia fonctionne.

Le fonctionnement réel du runtime Node/PHP/Laravel ne doit pas être considéré comme validé tant qu'une sandbox cloud dédiée n'est pas déployée.

### Ce qui doit encore être vérifié dans le cloud

Les points suivants ne peuvent pas être déclarés validés depuis GitHub seul :

- connexion réelle à PostgreSQL Railway ;
- exécution réelle des migrations sur PostgreSQL ;
- génération réelle des assets dans l'image de déploiement ;
- démarrage réel sur `0.0.0.0:$PORT` ;
- cookies HTTPS et persistance de session ;
- authentification après rechargement ;
- routes Inertia après rechargement direct ;
- comportement réel sur iOS/Android ;
- disponibilité réelle du runtime DevLab.

Le déploiement ne sera considéré comme réussi qu'après exécution effective des commandes de validation et vérification de l'URL HTTPS.
