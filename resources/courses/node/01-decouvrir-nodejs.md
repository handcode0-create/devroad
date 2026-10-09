---
title: Découvrir Node.js
minutes: 100
level: beginner
---

## Ce que tu vas apprendre

Tu écris du JavaScript dans le navigateur : tu manipules des pages, des boutons, des formulaires. **Node.js** te permet d'utiliser le même langage en dehors du navigateur, pour créer des serveurs, des API, des outils en ligne de commande et des scripts d'automatisation. C'est l'une des briques les plus répandues du développement web moderne, et la base de Next.js, de nombreux back-ends et d'une grande partie des outils que tu utilises déjà (Vite, ESLint, Tailwind).

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est Node.js et ce qui le différencie du JavaScript du navigateur ;
- vérifier ou installer Node.js, et exécuter un fichier ou une commande ;
- utiliser la console interactive (REPL) pour expérimenter ;
- lire les arguments de la ligne de commande et les variables d'environnement avec `process` ;
- utiliser quelques modules intégrés (`node:os`, `node:path`) ;
- comprendre le principe de la boucle d'événements, au niveau d'une première intuition.

Prérequis : JavaScript de base (variables, fonctions, tableaux, objets) et un terminal. Prévois environ une heure quarante. Ce chapitre utilise Node.js 20 ou plus récent.

## Qu'est-ce que Node.js ?

JavaScript est né dans le navigateur, qui contient un **moteur** capable de l'exécuter (V8 dans Chrome). En 2009, Node.js a pris ce même moteur V8 et l'a placé dans un programme autonome, avec des outils pour accéder au système : fichiers, réseau, processus.

Node.js n'est donc ni un langage ni un framework : c'est un **environnement d'exécution** (*runtime*) pour JavaScript hors du navigateur.

| | Navigateur | Node.js |
| --- | --- | --- |
| Objet global | `window` | `globalThis` (et `process`) |
| Accès au DOM | Oui (`document`) | Non |
| Accès aux fichiers du disque | Non (très limité) | Oui (`node:fs`) |
| Créer un serveur | Non | Oui (`node:http`) |
| Modules | `import` / balises script | `import` (ESM) ou `require` (CommonJS) |

Ce que tu sais déjà (variables, fonctions, promesses, `map`, `filter`) fonctionne à l'identique. Ce qui change, ce sont les **API disponibles** : pas de `document` ni de `alert`, mais des modules pour le système.

### À quoi sert Node.js ?

- Des **API et serveurs web** (pour une application mobile Flutter, un site, un SaaS) ;
- Des **outils de développement** : compilation, tests, formatage ;
- Des **scripts d'automatisation** : renommer des fichiers, convertir des données, appeler des API ;
- Des **applications temps réel** : discussion en direct, notifications ;
- Des **bots** (Telegram, WhatsApp) et des traitements en arrière-plan.

> **À retenir** : avec Node.js, tu peux construire le front-end **et** le back-end avec un seul langage. C'est un énorme gain pour une petite équipe, ou pour un développeur qui travaille seul.

:::quiz
Qu'est-ce que Node.js ?
- [ ] Un framework JavaScript pour créer des interfaces
- [ ] Un langage de programmation qui remplace JavaScript
- [x] Un environnement d'exécution qui permet d'exécuter JavaScript en dehors du navigateur
- [ ] Une base de données
> Node.js embarque le moteur V8 et fournit des API système (fichiers, réseau). Ce n'est ni un langage ni un framework d'interface.
:::

## Installer et vérifier Node.js

Dans ton terminal, tape :

```bash
node --version
npm --version
```

Si tu vois une version (par exemple `v22.11.0`), Node.js est installé. Ce cours demande la version 20 ou plus récente. Sinon, télécharge la version **LTS** (*Long Term Support*, support à long terme) sur nodejs.org. Les versions paires (20, 22) sont les versions LTS ; ce sont celles à utiliser en production.

Pour changer facilement de version, installe un gestionnaire comme `nvm` (macOS, Linux) ou `nvm-windows` :

```bash
nvm install 22
nvm use 22
nvm alias default 22
```

`npm` est installé en même temps que Node.js : nous le verrons au chapitre suivant.

## Exécuter du code

### Un fichier

Crée un dossier de travail `node-cours`, puis un fichier `bonjour.js` :

```js
const prenom = 'Awa';
const heure = new Date().getHours();

console.log(`Bonjour ${prenom} !`);
console.log(`Il est ${heure} h.`);
```

Exécute-le :

```bash
node bonjour.js
```

Le terminal affiche tes deux lignes. Aucun navigateur, aucune page HTML : `console.log` écrit simplement dans la sortie du terminal.

### Le REPL : un bac à sable interactif

Lance `node` sans argument pour ouvrir le **REPL** (*Read-Eval-Print Loop*). Tu tapes une expression, Node l'évalue et affiche le résultat immédiatement.

```text
$ node
> 2 + 3
5
> [1, 2, 3].map((n) => n * 2)
[ 2, 4, 6 ]
> const roadmaps = ['React', 'Node.js']
undefined
> roadmaps.length
2
> .exit
```

Le REPL est parfait pour tester une idée ou une fonction en quelques secondes. Quitte-le avec `.exit` ou `Ctrl + D`.

### Une commande rapide

L'option `-e` exécute directement du code sans fichier, pratique dans un script :

```bash
node -e "console.log(process.version)"
```

## L'objet process

Node expose un objet global **`process`** qui décrit le programme en cours et son environnement. Tu l'utiliseras tout le temps.

```js
// infos.js
console.log('Version de Node :', process.version);
console.log('Système :', process.platform);
console.log('Dossier courant :', process.cwd());
console.log('Mémoire (Mo) :', Math.round(process.memoryUsage().rss / 1024 / 1024));
```

### Lire les arguments de la ligne de commande

Les arguments passés après le nom du fichier sont dans `process.argv`, un tableau dont les deux premiers éléments sont le chemin de Node et celui du script.

```js
// saluer.js
const [, , prenom = 'inconnu'] = process.argv;
console.log(`Salut ${prenom} !`);
```

```bash
node saluer.js Kofi
# Salut Kofi !
```

Pour des besoins plus évolués, Node propose `util.parseArgs`, qui gère les options nommées :

```js
import { parseArgs } from 'node:util';

const { values } = parseArgs({
  options: {
    nom: { type: 'string', short: 'n' },
    majuscules: { type: 'boolean', short: 'm' },
  },
});

const message = `Bonjour ${values.nom ?? 'monde'}`;
console.log(values.majuscules ? message.toUpperCase() : message);
```

```bash
node saluer.js --nom Awa --majuscules
# BONJOUR AWA
```

### Les variables d'environnement

`process.env` contient les variables d'environnement du système. On y place les valeurs qui changent selon la machine : port, clés d'API.

```js
const port = process.env.PORT ?? 3000;
console.log(`Le serveur écouterait sur le port ${port}`);
```

```bash
PORT=8080 node serveur.js
```

### Terminer proprement

`process.exit(code)` arrête le programme. Le code `0` signifie « succès », tout autre nombre signale une erreur. Les outils d'automatisation s'appuient sur ce code.

```js
if (!process.argv[2]) {
  console.error('Erreur : un nom est requis.');
  process.exit(1);
}
```

`console.error` écrit sur le flux d'erreur, séparé de la sortie normale : c'est la bonne pratique pour les messages d'échec.

## Les modules intégrés

Node.js est livré avec de nombreux modules prêts à l'emploi. Ils s'importent avec le préfixe **`node:`**, qui indique clairement qu'il s'agit d'un module du cœur et non d'un paquet externe.

```js
import os from 'node:os';
import path from 'node:path';

console.log('Machine :', os.hostname());
console.log('Processeurs :', os.cpus().length);
console.log('Mémoire libre (Mo) :', Math.round(os.freemem() / 1024 / 1024));

const chemin = path.join('projets', 'devroad', 'README.md');
console.log(chemin);              // projets/devroad/README.md
console.log(path.basename(chemin)); // README.md
console.log(path.extname(chemin));  // .md
```

Le module `node:path` construit des chemins qui fonctionnent sur tous les systèmes (les séparateurs diffèrent entre Windows et Linux). N'assemble jamais des chemins avec de simples `+` ou des barres obliques écrites à la main.

### ESM et CommonJS : un mot sur les deux styles

Node connaît deux systèmes de modules. **ESM** (`import` / `export`) est le standard moderne, celui du navigateur. **CommonJS** (`require` / `module.exports`) est l'ancien système de Node, encore très présent dans d'anciens tutoriels. Pour utiliser `import` dans un fichier `.js`, ajoute `"type": "module"` dans ton `package.json` (nous le ferons au chapitre suivant), ou nomme tes fichiers avec l'extension `.mjs`. Ce cours utilise **ESM** partout.

## La boucle d'événements : première intuition

JavaScript n'exécute qu'**une chose à la fois** (un seul fil d'exécution). Pourtant, un serveur Node.js peut gérer des milliers de connexions. Comment ? Grâce à la **boucle d'événements**.

Quand Node lance une opération lente (lire un fichier, interroger une base, appeler une API), il ne reste pas bloqué à attendre. Il confie le travail au système, continue d'exécuter autre chose, et revient traiter le résultat quand il est prêt.

```js
console.log('1. Début');

setTimeout(() => {
  console.log('3. Résultat arrivé après 1 seconde');
}, 1000);

console.log('2. Fin du script');
```

La sortie est : `1. Début`, `2. Fin du script`, puis `3. Résultat arrivé…`. Le script n'a pas attendu la seconde. Cette façon de fonctionner, dite **non bloquante**, est la clé de la performance de Node.js. Nous l'étudierons en détail au chapitre « Asynchrone ».

> **Attention** : si tu lances un calcul très long et synchrone (une boucle de plusieurs secondes), tu **bloques** la boucle d'événements et tout le serveur se fige. Node excelle pour les opérations d'entrée-sortie (réseau, fichiers, bases de données) plus que pour les calculs lourds.

:::quiz
Que signifie « non bloquant » pour Node.js ?
- [ ] Qu'il exécute plusieurs fils JavaScript en parallèle par défaut
- [ ] Qu'il refuse les opérations lentes
- [x] Qu'il ne reste pas à attendre une opération lente et continue de traiter d'autres tâches
- [ ] Qu'il ne peut pas lire de fichiers
> Node délègue les opérations d'entrée-sortie au système et reprend le résultat quand il est prêt, ce qui lui permet de gérer beaucoup de connexions avec un seul fil JavaScript.
:::

## Atelier guidé : ton premier outil en ligne de commande

Compte quarante-cinq minutes. Tu vas créer un petit outil « DevRoad CLI » qui affiche des informations sur une roadmap.

1. Crée un dossier `devroad-cli` et ouvre-le dans ton éditeur.
2. Crée `infos.js` : affiche la version de Node, le système et le dossier courant avec `process`. Exécute-le.
3. Ouvre le REPL, teste `[3, 1, 2].sort()` puis `'node'.toUpperCase()`, et quitte-le.
4. Crée `roadmap.js` qui lit le premier argument de `process.argv` (le nom d'une technologie) et affiche « Roadmap : <nom> ». Si l'argument est absent, affiche un message d'erreur avec `console.error` et termine avec `process.exit(1)`.
5. Ajoute un petit tableau d'objets `{ nom, etapes }` pour trois technologies. Si le nom saisi existe, affiche le nombre d'étapes ; sinon, liste les technologies disponibles.
6. Remplace la lecture manuelle d'`argv` par `parseArgs`, avec une option `--nom` et une option booléenne `--json` qui affiche le résultat en JSON.
7. Utilise `process.env.LANGUE` : si elle vaut `en`, affiche les messages en anglais, sinon en français. Lance `LANGUE=en node roadmap.js --nom react`.
8. Utilise `node:path` et `node:os` pour afficher le nom de l'utilisateur courant (`os.userInfo().username`) et le nom du fichier du script (`path.basename(process.argv[1])`).

Pour t'auto-évaluer : peux-tu expliquer, avec tes mots, la différence entre exécuter du JavaScript dans le navigateur et avec Node.js, et pourquoi `console.log('Fin')` s'affiche avant le contenu d'un `setTimeout` ?

## Erreurs fréquentes

- **Utiliser `document` ou `window` dans Node.** Ces objets n'existent que dans le navigateur : tu obtiens « document is not defined ».
- **Lancer `node` dans le mauvais dossier.** L'erreur « Cannot find module » vient souvent d'un chemin relatif incorrect.
- **Mélanger `import` et `require` dans le même fichier.** Choisis un système et garde-le.
- **Oublier le préfixe `node:`.** Il fonctionne sans, mais il lève l'ambiguïté avec un paquet portant le même nom.
- **Écrire des chemins à la main.** Utilise `path.join` pour rester compatible Windows / Linux.
- **Bloquer la boucle d'événements avec un calcul long.** Le serveur ne répond plus pendant ce temps.
- **Ignorer les codes de sortie.** Un script qui échoue doit terminer avec un code différent de zéro.

## Bonnes pratiques

- Utilise une version **LTS** de Node.js et documente-la dans ton projet.
- Écris en ESM avec `import`, et le préfixe `node:` pour les modules intégrés.
- Lis la configuration depuis `process.env`, jamais en dur dans le code.
- Envoie les erreurs sur `console.error` et termine avec un code de sortie correct.
- Teste tes idées dans le REPL avant de les mettre dans un fichier.
- Prends l'habitude de lire la documentation officielle sur nodejs.org/docs.

## À retenir

- **Node.js** est un environnement d'exécution qui permet de faire tourner JavaScript hors du navigateur, avec accès au système.
- On exécute un fichier avec `node fichier.js` et on expérimente dans le **REPL** avec `node`.
- L'objet `process` donne accès aux arguments (`argv`), aux variables d'environnement (`env`) et au code de sortie (`exit`).
- Les modules intégrés s'importent avec le préfixe `node:` ; `node:path` construit des chemins portables.
- La **boucle d'événements** permet un fonctionnement non bloquant avec un seul fil JavaScript.
- Ce cours utilise Node.js 20 ou plus et les modules **ESM**.
