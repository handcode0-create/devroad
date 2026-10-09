---
title: Créer un projet React
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Écrire un composant dans un éditeur en ligne, c'est bien pour apprendre. Pour construire une vraie application, il faut un **projet** sur ton ordinateur : des outils qui compilent le JSX, rechargent la page à chaque sauvegarde et préparent le code pour la production. Ce chapitre te guide de zéro à une application qui tourne.

À la fin du chapitre, tu seras capable de :

- créer un projet React avec **Vite** ;
- lire l'arborescence d'un projet et dire à quoi sert chaque fichier ;
- lancer le serveur de développement et comprendre le rechargement à chaud ;
- ajouter une dépendance avec npm ;
- organiser tes composants et tes styles dans des dossiers cohérents ;
- produire une version de production et diagnostiquer les erreurs courantes.

Prérequis : **Node.js** (version 20 ou plus) et **npm** installés. Vérifie avec `node -v` et `npm -v`. Prévois deux heures.

## Pourquoi un outil de compilation ?

Le navigateur ne comprend pas le JSX. Il ne comprend pas non plus facilement des centaines de fichiers qui s'importent les uns les autres. Un **outil de compilation** (*bundler*) fait le travail de traduction :

1. il transforme le JSX en JavaScript standard ;
2. il assemble tous tes fichiers en quelques paquets optimisés ;
3. il recharge la page automatiquement pendant que tu développes ;
4. il réduit la taille du code pour la production.

**Vite** est l'outil le plus utilisé aujourd'hui pour cela. Il démarre en une fraction de seconde et met à jour la page presque instantanément. C'est aussi lui que DevRoad utilise pour compiler son interface.

> **À retenir** : tu écris du JSX et du JavaScript moderne ; Vite les transforme en code que tous les navigateurs comprennent.

:::quiz
Pourquoi un projet React a-t-il besoin d'un outil comme Vite ?
- [ ] Parce que React ne fonctionne pas sans connexion internet
- [x] Parce que le navigateur ne comprend pas le JSX et qu'il faut compiler et assembler le code
- [ ] Parce que Vite stocke les données de l'application
- [ ] Parce que Vite remplace React
> Le JSX doit être traduit en JavaScript standard, et les fichiers assemblés. Vite fait cette compilation et rafraîchit la page pendant le développement.
:::

## Créer le projet

Dans un terminal, place-toi dans ton dossier de projets et lance :

```bash
npm create vite@latest mon-app -- --template react
```

Cette commande crée un dossier `mon-app` contenant un projet React minimal (avec JavaScript, sans TypeScript). Termine l'installation :

```bash
cd mon-app
npm install
npm run dev
```

- `npm install` télécharge les dépendances dans le dossier `node_modules/` ;
- `npm run dev` démarre le serveur de développement.

Le terminal affiche une adresse, en général `http://localhost:5173`. Ouvre-la : tu vois la page de démonstration de Vite et React, avec un compteur sur lequel tu peux cliquer.

Pour voir la magie du rechargement à chaud (*hot reload*), ouvre `src/App.jsx`, modifie un texte et sauvegarde. La page se met à jour **sans recharger entièrement** et en conservant l'état du compteur.

## Visiter l'arborescence

Regarde ce que Vite a créé :

```text
mon-app/
├── index.html
├── package.json
├── vite.config.js
├── public/
│   └── vite.svg
└── src/
    ├── main.jsx
    ├── App.jsx
    ├── App.css
    ├── index.css
    └── assets/
```

Voici le rôle de chaque élément important.

| Fichier ou dossier | Rôle |
| --- | --- |
| `index.html` | L'unique page HTML. Elle contient `<div id="root"></div>` où React va s'afficher. |
| `package.json` | La carte d'identité du projet : nom, dépendances, scripts (`dev`, `build`…). |
| `vite.config.js` | La configuration de Vite (plugins, alias, serveur). |
| `public/` | Les fichiers copiés tels quels (favicon, images à adresse fixe). |
| `src/main.jsx` | Le point d'entrée : il démarre React. |
| `src/App.jsx` | Le composant racine de l'application. |
| `src/assets/` | Les images et fichiers importés depuis le code. |
| `node_modules/` | Les paquets installés. On n'y touche jamais, on ne le publie jamais. |

### Le point d'entrée

Ouvre `src/main.jsx` :

```jsx
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';
import './index.css';

createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>,
);
```

Ce fichier fait trois choses :

1. il trouve l'élément `#root` dans `index.html` ;
2. il y installe React avec `createRoot` ;
3. il affiche le composant `App` à l'intérieur.

`StrictMode` est un outil de développement : il t'avertit des mauvaises pratiques et exécute volontairement certaines choses deux fois pour révéler des bugs. Il n'a aucun effet en production.

### Le fichier package.json

```json
{
  "name": "mon-app",
  "private": true,
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "preview": "vite preview"
  },
  "dependencies": {
    "react": "^19.0.0",
    "react-dom": "^19.0.0"
  }
}
```

Les `scripts` sont des raccourcis que tu lances avec `npm run nom`. Les `dependencies` sont les bibliothèques dont ton code a besoin pour fonctionner. Les `devDependencies` (non montrées ici) sont des outils utilisés seulement pendant le développement, comme Vite lui-même.

> **Astuce** : le numéro de version `^19.0.0` signifie « 19.0.0 ou une mise à jour compatible ». Le fichier `package-lock.json` fige les versions exactes installées, pour que toute l'équipe ait les mêmes. Ne le supprime pas.

## Importer et exporter

Un projet React est découpé en nombreux fichiers qui se connectent par **import** et **export**.

```jsx
// src/components/CarteTechno.jsx
export default function CarteTechno({ nom }) {
  return <div className="carte">{nom}</div>;
}
```

```jsx
// src/App.jsx
import CarteTechno from './components/CarteTechno.jsx';

export default function App() {
  return <CarteTechno nom="React" />;
}
```

- `export default` expose **un** élément principal du fichier, importé sans accolades ;
- les exports nommés (`export function Titre() {}`) s'importent avec accolades : `import { Titre } from './Titre.jsx'`.

Le chemin commence par `./` pour un fichier du même dossier, `../` pour remonter d'un niveau.

## Organiser son code

Un projet qui grandit sans organisation devient vite illisible. Une structure simple et efficace pour débuter :

```text
src/
├── components/     # composants réutilisables (Bouton, CarteTechno)
├── pages/          # un composant par écran (Accueil, Parcours)
├── hooks/          # fonctions réutilisables avec état (vu plus tard)
├── assets/         # images
└── App.jsx
```

Quelques règles de rangement :

- **un composant par fichier**, avec le même nom que le fichier (`CarteTechno.jsx` contient `CarteTechno`) ;
- les composants généraux (bouton, carte) dans `components/`, ceux qui représentent un écran dans `pages/` ;
- garde un nom en **PascalCase** pour les fichiers de composants, et en minuscules pour le reste.

### Les styles

Plusieurs approches existent. Pour commencer, importe simplement un fichier CSS :

```jsx
import './CarteTechno.css';
```

Vite l'ajoute automatiquement à la page. Plus tard, tu pourras utiliser Tailwind CSS, qui évite d'écrire des fichiers CSS séparés.

## Ajouter une dépendance

Besoin d'une icône, d'un outil de dates, d'un routeur ? Installe un paquet avec npm :

```bash
npm install lucide-react
```

Le paquet est téléchargé dans `node_modules/` et ajouté à `package.json`. Utilise-le ensuite :

```jsx
import { Map } from 'lucide-react';

export default function Titre() {
  return <h1><Map size={20} aria-hidden="true" /> Mes parcours</h1>;
}
```

Pour un outil seulement utile au développement :

```bash
npm install --save-dev prettier
```

> **Attention** : avant d'installer un paquet, vérifie qu'il est maintenu, populaire et qu'il correspond vraiment à ton besoin. Chaque dépendance ajoutée alourdit ton projet et peut introduire des failles de sécurité.

:::quiz
Quelle commande ajoute une bibliothèque nécessaire au fonctionnement de ton application ?
- [ ] npm run dev
- [x] npm install nom-du-paquet
- [ ] npm create vite
- [ ] npm run build
> npm install télécharge le paquet et l'enregistre dans package.json. Les autres commandes démarrent, créent ou compilent le projet.
:::

## Préparer la production

Le serveur de développement est pratique, mais il n'est pas destiné aux vrais visiteurs. Pour la mise en ligne, on compile une version optimisée :

```bash
npm run build
```

Vite crée un dossier `dist/` contenant des fichiers HTML, CSS et JavaScript réduits et prêts à être hébergés sur n'importe quel serveur statique. Pour l'essayer en local :

```bash
npm run preview
```

Dans une application Laravel avec Inertia (comme DevRoad), le principe est le même : `npm run dev` pendant le développement, `npm run build` avant la mise en production.

## Les erreurs d'installation classiques

1. **« npm : commande introuvable »** : Node.js n'est pas installé ou son dossier n'est pas dans le `PATH`.
2. **« Cannot find module »** : tu as oublié `npm install`, ou un import a un mauvais chemin.
3. **« Port 5173 is already in use »** : un autre serveur Vite tourne déjà. Ferme-le ou laisse Vite proposer un autre port.
4. **Page blanche** : ouvre la console du navigateur (F12) : l'erreur y est expliquée. Souvent, un import cassé ou une faute dans le JSX.
5. **Les modifications n'apparaissent pas** : vérifie que le fichier est bien sauvegardé et que le serveur tourne toujours.

> **Astuce** : quand quelque chose ne marche pas, lis **toujours** la console du terminal (côté Vite) et celle du navigateur (côté React). Les deux disent presque toujours exactement ce qui ne va pas, avec le nom du fichier et la ligne.

## Atelier guidé : ton projet de zéro

Compte une heure et demie.

1. Vérifie `node -v` et `npm -v`, puis crée le projet : `npm create vite@latest roadmap-ui -- --template react`.
2. Lance `npm install`, puis `npm run dev`, et ouvre l'adresse dans le navigateur.
3. Modifie le texte de `App.jsx` et observe le rechargement à chaud.
4. Crée les dossiers `src/components` et `src/pages`.
5. Crée `CarteTechno.jsx` dans `components/` (un composant qui reçoit un nom en prop) et utilise-le dans `App.jsx`.
6. Crée un fichier CSS pour la carte et importe-le dans le composant.
7. Installe `lucide-react` et affiche une icône dans ta carte.
8. Lance `npm run build`, repère le dossier `dist/`, puis `npm run preview` pour voir la version de production.

Pour t'auto-évaluer : sans regarder, explique à quoi servent `index.html`, `main.jsx` et `package.json`.

## Erreurs fréquentes

- **Oublier `npm install` après avoir cloné un projet.** Le dossier `node_modules` n'est pas dans Git.
- **Un chemin d'import incorrect.** Vérifie les `./` et les majuscules : `Carte.jsx` n'est pas `carte.jsx` sur tous les systèmes.
- **Un fichier de composant en `.js` avec du JSX.** Utilise l'extension `.jsx`.
- **Modifier `node_modules`.** Tes changements disparaissent à la prochaine installation.
- **Publier `node_modules` ou `dist` dans Git.** Ajoute-les au `.gitignore`.
- **Confondre `dependencies` et `devDependencies`.** Un outil de développement n'a pas sa place dans les dépendances de production.

## Bonnes pratiques

- Un composant par fichier, nommé comme lui, rangé dans le bon dossier.
- Commite `package.json` et `package-lock.json`, jamais `node_modules`.
- Reste proche de la structure standard : un autre développeur doit s'y retrouver.
- Lis les messages d'erreur avant de chercher sur internet.
- Teste la version de production (`build` puis `preview`) avant chaque mise en ligne.

## À retenir

- Vite compile le JSX, assemble les fichiers et recharge la page pendant le développement.
- `npm create vite@latest`, puis `npm install` et `npm run dev` lancent un projet.
- `index.html` contient `#root`, `main.jsx` démarre React, `App.jsx` est le composant racine.
- `import` et `export` relient les fichiers ; `export default` pour l'élément principal.
- `npm install nom` ajoute une dépendance ; `npm run build` produit le dossier `dist/`.
- Les erreurs se lisent dans le terminal et dans la console du navigateur.
