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
