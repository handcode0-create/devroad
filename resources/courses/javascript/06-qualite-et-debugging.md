---
title: Qualité et debugging
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Écrire du code qui marche une fois ne suffit pas. Un développeur professionnel écrit du code qui continue à marcher quand l'application grandit, et qui sait retrouver rapidement la cause d'un bug. Ce chapitre t'équipe pour cela : lire les erreurs, déboguer avec les outils du navigateur, gérer les exceptions proprement, vérifier la qualité avec ESLint et Prettier, et écrire des tests automatisés.

À la fin du chapitre, tu seras capable de :

- reconnaître les types d'erreurs JavaScript et lire une pile d'appels ;
- déboguer avec la console, les points d'arrêt et l'onglet Réseau ;
- lancer et gérer des exceptions avec `throw`, `try / catch` et des erreurs personnalisées ;
- programmer de façon défensive (valider les entrées) ;
- configurer ESLint et Prettier dans un projet ;
- écrire des tests unitaires avec Vitest ;
- refactoriser une fonction sans casser son comportement.

Prérequis : les chapitres précédents et Node.js installé (version 18 ou plus). Prévois deux heures et demie.

## Lire une erreur

Une erreur n'est pas un échec : c'est une information. Apprends à la lire de haut en bas.

```text
Uncaught TypeError: Cannot read properties of undefined (reading 'titre')
    at afficherTitre (main.js:12:25)
    at HTMLButtonElement.<anonymous> (main.js:30:5)
```

Cette trace s'appelle la **pile d'appels** (*stack trace*). Trois informations comptent :

1. **Le type** : `TypeError`.
2. **Le message** : on a essayé de lire `titre` sur `undefined`.
3. **L'endroit** : fonction `afficherTitre`, fichier `main.js`, ligne 12, colonne 25.

Les types d'erreurs courants :

| Type | Cause typique |
| --- | --- |
| `SyntaxError` | Code mal écrit (parenthèse, virgule, accolade manquante) |
| `ReferenceError` | Variable utilisée sans être déclarée ou hors de portée |
| `TypeError` | Opération sur le mauvais type (appeler un non-fonction, lire `undefined`) |
| `RangeError` | Valeur hors limite, récursion infinie |

Un `SyntaxError` empêche l'exécution de tout le fichier ; les autres surviennent à l'exécution, quand la ligne fautive est atteinte.

> **Astuce** : lis toujours le **message complet** avant de chercher sur internet, puis remonte la pile d'appels jusqu'à la première ligne qui appartient à **ton** code. C'est presque toujours là qu'il faut regarder.

## Déboguer avec les outils du navigateur

### La console, au-delà de console.log

```js
console.log('valeur', 42);
console.warn('Attention, valeur suspecte');
console.error('Échec du chargement');
console.table([{ id: 1, titre: 'A' }, { id: 2, titre: 'B' }]); // affiche un tableau
console.group('Chargement');
console.log('étape 1');
console.groupEnd();
console.time('calcul');
// ... du code à mesurer
console.timeEnd('calcul');
```

`console.table` est idéal pour inspecter des listes d'objets. Quand tu logues un objet, affiche-le avec une étiquette (`console.log({ lecon })`) : tu verras le nom de la variable en plus de sa valeur.

### Les points d'arrêt

`console.log` oblige à modifier le code, relancer et deviner. Un **point d'arrêt** (*breakpoint*) met l'exécution en pause pour que tu inspectes tout l'état.

1. Ouvre les outils du navigateur (F12) puis l'onglet **Sources**.
2. Ouvre ton fichier et clique sur le numéro d'une ligne : un marqueur apparaît.
3. Déclenche l'action : l'exécution s'arrête avant cette ligne.
4. Regarde les variables dans le panneau **Scope**, et la pile dans **Call Stack**.
5. Avance avec **Step over** (ligne suivante), **Step into** (entrer dans la fonction) et **Resume** (continuer).

Tu peux aussi écrire l'instruction `debugger;` dans ton code : si les outils sont ouverts, l'exécution s'y arrête. Pense à la retirer ensuite.

### L'onglet Réseau

Pour tout problème d'API, ouvre l'onglet **Network** : tu y vois chaque requête, son URL, son statut (200, 404, 422, 500), ses en-têtes, le corps envoyé et la réponse reçue. Avec Laravel, une erreur de validation retourne un statut **422** avec la liste des champs fautifs : tu la lis directement ici.

:::quiz
Que permet de faire un point d'arrêt (breakpoint) ?
- [ ] Supprimer une ligne de code défectueuse
- [x] Mettre l'exécution en pause pour inspecter les variables et la pile d'appels
- [ ] Empêcher une fonction de s'exécuter
- [ ] Compiler le code plus vite
> Un point d'arrêt suspend l'exécution à une ligne précise. Tu peux alors lire les valeurs des variables et avancer pas à pas.
:::

## Gérer les erreurs : throw et try / catch

Une fonction qui reçoit une mauvaise donnée doit échouer **clairement**, au lieu de produire un résultat absurde en silence.

```js
function calculerPourcentage(fait, total) {
  if (typeof fait !== 'number' || typeof total !== 'number') {
    throw new TypeError('fait et total doivent être des nombres');
  }
  if (total <= 0) {
    throw new RangeError('total doit être supérieur à 0');
  }
  return Math.round((fait / total) * 100);
}
```

On intercepte ensuite l'erreur à l'endroit où l'on sait quoi en faire :

```js
try {
  const pct = calculerPourcentage(3, 0);
  console.log(pct);
} catch (erreur) {
  console.error(`Impossible de calculer : ${erreur.message}`);
} finally {
  console.log('Calcul terminé');
}
```

### Erreurs personnalisées

Pour distinguer facilement tes erreurs métier, crée tes propres classes :

```js
class ValidationError extends Error {
  constructor(message, champ) {
    super(message);
    this.name = 'ValidationError';
    this.champ = champ;
  }
}

function validerLecon(lecon) {
  if (!lecon.titre?.trim()) {
    throw new ValidationError('Le titre est obligatoire', 'titre');
  }
  if (lecon.minutes < 15) {
    throw new ValidationError('Durée minimale : 15 minutes', 'minutes');
  }
}

try {
  validerLecon({ titre: '', minutes: 60 });
} catch (erreur) {
  if (erreur instanceof ValidationError) {
    console.log(`Erreur sur le champ ${erreur.champ} : ${erreur.message}`);
  } else {
    throw erreur; // une erreur inattendue ne doit pas être avalée
  }
}
```

La dernière ligne est essentielle : si tu ne sais pas traiter l'erreur, **relance-la**.

## La programmation défensive

Ne fais jamais confiance aux données qui entrent dans ton code : saisies utilisateur, réponses d'API, contenu de `localStorage`. Vérifie-les à la frontière.

```js
function lireProgression(brut) {
  let donnees;
  try {
    donnees = JSON.parse(brut);
  } catch {
    return [];
  }
  if (!Array.isArray(donnees)) return [];

  return donnees.filter(
    (e) => e && typeof e.id === 'number' && typeof e.terminee === 'boolean'
  );
}
```

Autres réflexes : utiliser `?.` et `??` pour les données optionnelles, retourner tôt (*early return*) pour traiter les cas invalides en premier, et ne pas laisser un `catch` vide.

:::quiz
Dans un `catch`, que faire d'une erreur que tu ne sais pas traiter ?
- [ ] L'ignorer pour que l'application continue
- [ ] L'afficher dans un `alert`
- [x] La relancer avec `throw erreur`
- [ ] La remplacer par `null`
> Une erreur inattendue ne doit pas être avalée. En la relançant, tu laisses un niveau supérieur la traiter ou l'enregistrer, et tu évites de masquer un vrai bug.
:::

## ESLint et Prettier

Deux outils automatisent la qualité du code.

- **ESLint** analyse ton code et signale les erreurs probables : variable inutilisée, comparaison `==`, `await` oublié.
- **Prettier** formate automatiquement : indentation, guillemets, espaces. Fini les discussions de style.

Installe-les dans un projet :

```bash
npm init -y
npm install --save-dev eslint @eslint/js prettier
```

Crée un fichier `eslint.config.js` à la racine :

```js
import js from '@eslint/js';

export default [
  js.configs.recommended,
  {
    languageOptions: { ecmaVersion: 2024, sourceType: 'module' },
    rules: {
      eqeqeq: 'error',
      'no-unused-vars': 'warn',
      'prefer-const': 'error',
    },
  },
];
```

Puis ajoute des scripts dans `package.json` et un fichier `.prettierrc` :

```json
{
  "type": "module",
  "scripts": {
    "lint": "eslint .",
    "format": "prettier --write ."
  }
}
```

```json
{ "singleQuote": true, "semi": true, "trailingComma": "es5" }
```

Lance `npm run lint` pour voir les problèmes et `npm run format` pour tout reformater. Dans VS Code, active « Format on save » pour que Prettier travaille à chaque enregistrement.

## Tester avec Vitest

Un **test automatisé** est un petit programme qui vérifie qu'une fonction se comporte comme prévu. Il te protège de la régression : quand tu modifies le code, tu sais immédiatement si tu as cassé quelque chose. **Vitest** est rapide, simple et très utilisé dans l'écosystème Vite et React.

```bash
npm install --save-dev vitest
```

Ajoute `"test": "vitest run"` dans `scripts`. Crée `utils.js` puis `utils.test.js` :

```js
// utils.js
export function formaterDuree(minutes) {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  if (h === 0) return `${m} min`;
  return m === 0 ? `${h} h` : `${h} h ${m}`;
}
```

```js
// utils.test.js
import { describe, it, expect } from 'vitest';
import { formaterDuree } from './utils.js';

describe('formaterDuree', () => {
  it('affiche les minutes seules sous une heure', () => {
    expect(formaterDuree(45)).toBe('45 min');
  });

  it('affiche les heures pleines', () => {
    expect(formaterDuree(120)).toBe('2 h');
  });

  it('affiche heures et minutes', () => {
    expect(formaterDuree(150)).toBe('2 h 30');
  });
});
```

Lance `npm test`. Chaque `it` décrit **un comportement attendu**. `expect(...)` reçoit la valeur réelle, et un « matcher » (`toBe`, `toEqual`, `toThrow`) la compare à la valeur attendue.

Pour tester qu'une fonction lève une erreur :

```js
it('rejette un total nul', () => {
  expect(() => calculerPourcentage(1, 0)).toThrow('total doit être supérieur à 0');
});
```

Pour comparer des objets ou des tableaux, utilise `toEqual` (comparaison du contenu) plutôt que `toBe` (comparaison de référence).

Une méthode très efficace est de **tester les cas limites** : zéro, tableau vide, chaîne vide, `null`, très grands nombres. Les bugs s'y cachent.

## Refactoriser avec un filet de sécurité

Refactoriser, c'est améliorer la structure du code **sans changer son comportement**. Les tests te servent de filet : tant qu'ils restent verts, tu n'as rien cassé. Compare ces deux versions de la même fonction :

```js
// Avant
function niveauPourScore(s) {
  if (s >= 80) { return 'professional'; } else { if (s >= 50) { return 'intermediate'; } else { return 'beginner'; } }
}

// Après
const SEUIL_PRO = 80;
const SEUIL_INTERMEDIAIRE = 50;

function niveauPourScore(score) {
  if (score >= SEUIL_PRO) return 'professional';
  if (score >= SEUIL_INTERMEDIAIRE) return 'intermediate';
  return 'beginner';
}
```

Écris d'abord les tests sur l'ancienne version, puis refactorise, puis relance les tests.

## Atelier guidé : sécuriser un module de progression

Compte une heure et demie. Crée un dossier avec `npm init -y` et ajoute `"type": "module"` dans `package.json`.

1. Installe ESLint, Prettier et Vitest, puis crée `eslint.config.js` et `.prettierrc` comme dans le cours.
2. Crée `progression.js` qui exporte `calculerPourcentage(fait, total)` avec la validation par `throw` du chapitre.
3. Ajoute volontairement un `var` et une comparaison `==` dans le fichier, lance `npm run lint` et lis les messages, puis corrige-les.
4. Écris `progression.test.js` avec au moins 5 tests : cas normal, zéro leçon terminée, tout terminé, total à 0 (erreur), type invalide (erreur).
5. Ajoute `lireProgression(brut)` défensive, et teste-la avec du JSON invalide, un objet au lieu d'un tableau, et des éléments incomplets.
6. Crée la classe `ValidationError` et une fonction `validerLecon`, puis teste que le bon champ est signalé.
7. Provoque un bug : change `Math.round` en `Math.floor` et regarde quel test échoue. Corrige.
8. Dans un navigateur, pose un point d'arrêt dans une de tes fonctions appelée depuis une page et avance pas à pas.
9. Refactorise `niveauPourScore` comme dans le cours, avec des tests avant et après.

Pour t'auto-évaluer : peux-tu expliquer pourquoi un test qui échoue après un changement est une bonne nouvelle, et quand utiliser `toBe` plutôt que `toEqual` ?

## Erreurs fréquentes

- **Ignorer le message d'erreur.** Il indique presque toujours le fichier, la ligne et la cause.
- **Disséminer des `console.log` et les oublier.** Retire-les avant de livrer ; ESLint peut les signaler avec la règle `no-console`.
- **Écrire un `catch` vide.** Tu masques les bugs au lieu de les traiter.
- **Attraper une erreur trop tôt.** Traite-la là où tu peux réagir réellement (afficher un message, réessayer).
- **Utiliser `toBe` sur des objets.** Ils sont comparés par référence ; utilise `toEqual`.
- **Tester uniquement le cas qui marche.** Les cas limites et les erreurs sont ceux qui comptent.
- **Désactiver une règle ESLint pour faire taire un avertissement.** Comprends d'abord ce qu'elle protège.
- **Refactoriser sans tests.** Tu ne sais plus si le comportement a changé.

## Bonnes pratiques

- Reproduis d'abord le bug de façon fiable, puis isole la cause, puis corrige, puis ajoute un test qui l'empêche de revenir.
- Utilise les points d'arrêt pour comprendre, `console.table` pour inspecter des listes.
- Valide les données aux frontières : formulaire, API, stockage local.
- Lève des erreurs claires avec des messages utiles et des classes dédiées pour les erreurs métier.
- Lance `npm run lint` et `npm test` avant chaque commit, idéalement automatiquement dans la CI.
- Des tests petits, nommés par comportement, indépendants les uns des autres.
- Refactorise par petits pas, en relançant les tests à chaque étape.

## À retenir

- Une erreur se lit en trois parties : type, message, emplacement dans la pile d'appels.
- Les points d'arrêt, `console.table` et l'onglet Réseau sont tes outils de diagnostic.
- `throw` signale un problème, `try / catch` le traite, et ce qu'on ne sait pas traiter se relance.
- La programmation défensive vérifie les données à leur entrée dans ton code.
- ESLint détecte les erreurs probables, Prettier uniformise le format.
- Vitest permet d'écrire des tests unitaires : `describe`, `it`, `expect`, `toBe`, `toEqual`, `toThrow`.
- Les tests sont le filet qui rend la refactorisation sûre.
