---
title: NPM et modules
minutes: 110
level: beginner
---

## Ce que tu vas apprendre

Aucun développeur n'écrit tout son code seul. L'écosystème JavaScript compte plus de deux millions de paquets réutilisables, accessibles via **npm**. Dans ce chapitre, tu apprends à initialiser un projet, à installer et gérer des dépendances, à écrire tes propres modules en ESM et à définir des scripts. Ce sont les gestes quotidiens de tout projet Node.js.

À la fin du chapitre, tu seras capable de :

- initialiser un projet avec `npm init` et lire un `package.json` ;
- installer, mettre à jour et supprimer des dépendances (production et développement) ;
- comprendre le versionnement sémantique et le rôle de `package-lock.json` ;
- écrire et importer tes propres modules avec `import` / `export` ;
- définir des scripts avec `npm run` et utiliser `npx` ;
- auditer tes dépendances pour repérer les failles connues ;
- choisir un paquet fiable avant de l'installer.

Prérequis : le chapitre « Découvrir Node.js ». Prévois environ une heure cinquante.

## npm et package.json

**npm** (*Node Package Manager*) est à la fois un registre en ligne de paquets et un outil en ligne de commande pour les installer. Chaque projet Node.js est décrit par un fichier **`package.json`**, sa carte d'identité.

Crée un dossier et initialise le projet :

```bash
mkdir devroad-outils
cd devroad-outils
npm init -y
```

L'option `-y` accepte toutes les valeurs par défaut. Le fichier généré ressemble à ceci :

```json
{
  "name": "devroad-outils",
  "version": "1.0.0",
  "description": "",
  "main": "index.js",
  "scripts": {
    "test": "echo \"Error: no test specified\" && exit 1"
  },
  "license": "ISC"
}
```

Modifie-le pour passer en ESM et fixer la version de Node demandée :

```json
{
  "name": "devroad-outils",
  "version": "1.0.0",
  "private": true,
  "type": "module",
  "engines": { "node": ">=20" },
  "scripts": {
    "start": "node src/index.js"
  }
}
```

- `"type": "module"` active `import` / `export` dans les fichiers `.js` ;
- `"private": true` empêche de publier le projet par erreur sur le registre ;
- `"engines"` documente la version de Node nécessaire ;
- `"scripts"` regroupe tes commandes (voir plus bas).

## Installer des dépendances

Pour ajouter un paquet, utilise `npm install` (ou son alias `npm i`). Installons `chalk`, une bibliothèque qui colore le texte du terminal :

```bash
npm install chalk
```

Trois choses se produisent :

1. le paquet est téléchargé dans le dossier **`node_modules/`** ;
2. il est ajouté à la section `dependencies` de `package.json` ;
3. un fichier **`package-lock.json`** enregistre les versions exactes installées.

```js
// src/index.js
import chalk from 'chalk';

console.log(chalk.green('Succès : le projet fonctionne !'));
console.log(chalk.red.bold('Attention : ceci est une alerte.'));
```

```bash
npm start
```

### Dépendances de production et de développement

Certains paquets servent seulement pendant le développement (outils de test, formatage). On les installe avec l'option `-D` ; ils vont dans `devDependencies`, et ne sont pas installés en production :

```bash
npm install -D prettier
```

| Section | Contenu | Exemples |
| --- | --- | --- |
| `dependencies` | Nécessaire à l'exécution | `express`, `zod`, `chalk` |
| `devDependencies` | Nécessaire au développement | `prettier`, `eslint`, `nodemon` |

### Supprimer, mettre à jour, lister

```bash
npm uninstall chalk        # retire le paquet
npm update                 # met à jour dans les limites permises par package.json
npm outdated               # liste les paquets ayant une version plus récente
npm ls --depth=0           # liste les dépendances directes installées
```

### Installer un projet existant

Quand tu clones le projet de quelqu'un d'autre, `node_modules/` n'existe pas (il n'est pas versionné). Une seule commande reconstitue tout :

```bash
npm install
```

En intégration continue ou en déploiement, préfère `npm ci`, qui installe exactement les versions du `package-lock.json` et échoue en cas d'écart : le résultat est reproductible.

> **Attention** : ne commite **jamais** le dossier `node_modules/`. Ajoute-le dans `.gitignore`. En revanche, commite toujours `package.json` **et** `package-lock.json`.

:::quiz
Quelle commande installe un paquet uniquement pour le développement (par exemple Prettier) ?
- [ ] npm install prettier --production
- [x] npm install -D prettier
- [ ] npm global prettier
- [ ] npm add prettier --prod
> L'option -D (ou --save-dev) place le paquet dans devDependencies : il est utile pendant le développement mais pas nécessaire à l'exécution en production.
:::

## Le versionnement sémantique

Chaque paquet a une version de la forme **MAJEUR.MINEUR.CORRECTIF**, par exemple `4.18.2` :

- le **correctif** (dernier nombre) corrige des bugs sans rien casser ;
- le **mineur** ajoute des fonctionnalités de façon compatible ;
- le **majeur** introduit des changements qui peuvent casser ton code.

Dans `package.json`, les symboles devant le numéro définissent les mises à jour autorisées :

| Écriture | Signification | Autorise |
| --- | --- | --- |
| `"4.18.2"` | Version exacte | Rien d'autre |
| `"^4.18.2"` | Compatible (par défaut) | Mineurs et correctifs : de 4.18.2 à moins de 5.0.0 |
| `"~4.18.2"` | Approximative | Correctifs seulement : de 4.18.2 à moins de 4.19.0 |

C'est **`package-lock.json`** qui garantit que tout le monde utilise exactement les mêmes versions, même si `package.json` autorise une fourchette. Ne le modifie jamais à la main.

## Écrire et importer tes propres modules

Un **module** est un fichier qui expose des fonctions ou des valeurs aux autres fichiers. Découper son code en modules rend le projet lisible, testable et réutilisable.

### Les exports nommés

Crée `src/roadmaps.js` :

```js
const roadmaps = [
  { slug: 'react', titre: 'React', etapes: 12 },
  { slug: 'node', titre: 'Node.js', etapes: 9 },
  { slug: 'laravel', titre: 'Laravel', etapes: 15 },
];

export function listerRoadmaps() {
  return roadmaps;
}

export function trouverRoadmap(slug) {
  return roadmaps.find((r) => r.slug === slug) ?? null;
}

export const NOMBRE_MAX = 50;
```

Importe-les dans `src/index.js` entre accolades, en indiquant **l'extension** `.js` (obligatoire en ESM pour les fichiers relatifs) :

```js
import { listerRoadmaps, trouverRoadmap } from './roadmaps.js';

console.log(listerRoadmaps().length);        // 3
console.log(trouverRoadmap('node')?.titre);  // Node.js
```

### L'export par défaut

Un module peut aussi exposer **une** valeur principale, importée sans accolades et nommée librement :

```js
// src/formatage.js
export default function majusculeInitiale(texte) {
  return texte.charAt(0).toUpperCase() + texte.slice(1);
}
```

```js
import majusculeInitiale from './formatage.js';
console.log(majusculeInitiale('bonjour')); // Bonjour
```

Préfère en général les exports nommés : ils sont plus faciles à retrouver et à renommer avec les outils de l'éditeur.

### Importer un module du cœur et un paquet

```js
import { readFile } from 'node:fs/promises';   // module intégré
import chalk from 'chalk';                       // paquet installé
import { listerRoadmaps } from './roadmaps.js';  // ton module
```

L'ordre habituel est : modules du cœur, puis paquets, puis tes fichiers.

### Et CommonJS ?

Tu rencontreras `const express = require('express')` et `module.exports = ...` dans d'anciens projets. Node.js sait toujours les exécuter (fichiers `.cjs`, ou `.js` sans `"type": "module"`). Pour un nouveau projet, choisis ESM. Dans un projet ESM, `__dirname` n'existe pas ; l'équivalent est :

```js
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
```

:::quiz
Dans un projet ESM, comment importer la fonction exportée par ./utils.js ?
- [ ] import { aider } from './utils'
- [x] import { aider } from './utils.js'
- [ ] const aider = require('./utils.js')
- [ ] include './utils.js'
> En ESM, l'extension du fichier est obligatoire pour les imports relatifs. require appartient à CommonJS.
:::

## Les scripts npm et npx

La section `scripts` de `package.json` nomme des commandes que tout le monde lance de la même façon :

```json
{
  "scripts": {
    "start": "node src/index.js",
    "dev": "node --watch src/index.js",
    "format": "prettier --write src",
    "check": "prettier --check src"
  }
}
```

```bash
npm start            # raccourci pour 'npm run start'
npm run dev
npm run format
```

Seuls `start` et `test` peuvent se lancer sans `run`. L'option `node --watch` (disponible depuis Node 18) relance automatiquement le programme à chaque modification de fichier : plus besoin d'installer `nodemon` pour cela.

**`npx`** exécute un outil d'un paquet sans l'installer durablement :

```bash
npx prettier --check src
npx create-next-app@latest mon-site
```

Les exécutables installés localement (comme Prettier) sont automatiquement trouvés par les scripts npm, sans `npx`.

## Choisir et auditer ses dépendances

Chaque paquet que tu installes est du code écrit par un inconnu, exécuté sur ta machine puis sur ton serveur. Cela mérite de la prudence.

Avant d'installer, vérifie :

- **la popularité et la maintenance** : nombre de téléchargements hebdomadaires, date de la dernière publication ;
- **le dépôt** : lien vers le code source, issues traitées, licence claire ;
- **la taille** et le nombre de sous-dépendances ;
- **le nom exact** : méfie-toi des fautes de frappe (`expres` au lieu d'`express`), technique utilisée par des paquets malveillants.

Pour une fonction de trois lignes, n'ajoute pas un paquet entier. Et vérifie régulièrement la sécurité :

```bash
npm audit
npm audit fix
```

`npm audit` signale les failles connues dans tes dépendances. `npm audit fix` applique les mises à jour sûres. Lis le rapport avant d'utiliser l'option `--force`, qui peut casser ton projet en changeant de version majeure.

## Atelier guidé : un utilitaire de fiches en ligne de commande

Compte une heure.

1. Crée le dossier `devroad-outils`, lance `npm init -y`, passe le projet en ESM et ajoute `engines` et `private`.
2. Crée un `.gitignore` contenant `node_modules/` et `.env`.
3. Installe `chalk` puis `prettier` en dépendance de développement. Observe les changements dans `package.json` et la création de `package-lock.json`.
4. Crée `src/roadmaps.js` avec les fonctions `listerRoadmaps` et `trouverRoadmap` et un tableau de quatre roadmaps.
5. Crée `src/affichage.js` qui exporte une fonction `afficherRoadmap(roadmap)`, laquelle utilise `chalk` pour afficher le titre en vert et le nombre d'étapes en gris.
6. Dans `src/index.js`, importe les deux modules, lis un slug dans `process.argv` et affiche la roadmap, ou un message d'erreur en rouge si elle est inconnue.
7. Ajoute les scripts `start`, `dev` (avec `--watch`), `format` et `check`.
8. Supprime le dossier `node_modules`, puis restaure-le avec `npm ci`. Constate que tout refonctionne.
9. Lance `npm outdated` puis `npm audit` et note le résultat.
10. Désinstalle `chalk`, vérifie que le programme échoue, puis réinstalle-le.

Pour t'auto-évaluer : explique la différence entre `^1.2.3`, `~1.2.3` et `1.2.3`, et pourquoi on commite `package-lock.json` mais pas `node_modules`.

## Erreurs fréquentes

- **Oublier `"type": "module"`.** Tu obtiens « Cannot use import statement outside a module ».
- **Oublier l'extension `.js` dans un import relatif.** Erreur `ERR_MODULE_NOT_FOUND`.
- **Commiter `node_modules/`.** Le dépôt devient énorme et inutilement lourd.
- **Modifier `package-lock.json` à la main.** Laisse npm le gérer.
- **Installer un paquet globalement par réflexe** (`npm i -g`). Préfère l'installation locale au projet.
- **Mélanger `npm` et un autre gestionnaire** (yarn, pnpm) dans le même projet. Tu produirais plusieurs fichiers de verrouillage contradictoires.
- **Ignorer `npm audit`.** Des failles connues restent dans ton application.
- **Taper `npm install` pour un déploiement.** Utilise `npm ci` pour un résultat reproductible.

## Bonnes pratiques

- Un projet = un `package.json` complet : nom, version de Node, scripts utiles.
- Commite `package.json` et `package-lock.json` ; ignore `node_modules/`.
- Utilise des exports nommés et des fichiers courts, avec une seule responsabilité.
- Mets les outils de développement dans `devDependencies`.
- Évalue chaque nouvelle dépendance : en as-tu vraiment besoin ?
- Lance `npm audit` régulièrement et mets à jour tes dépendances par petites étapes.
- Documente les scripts dans le README.

## À retenir

- **npm** installe des paquets et lit `package.json`, qui décrit le projet, ses dépendances et ses scripts.
- `npm install` ajoute un paquet ; `-D` pour le développement ; `npm ci` pour une installation reproductible.
- Le **versionnement sémantique** distingue majeur, mineur et correctif ; `^` et `~` fixent les mises à jour autorisées.
- `package-lock.json` fige les versions exactes ; `node_modules/` ne se commite jamais.
- En **ESM**, on utilise `import` / `export`, avec l'extension `.js` dans les imports relatifs.
- `npm run` lance des scripts ; `npx` exécute un outil sans l'installer ; `npm audit` repère les failles.
