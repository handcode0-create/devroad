---
title: Configuration et qualité
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Écrire des types est une chose ; configurer l'outil pour qu'il te protège vraiment en est une autre. Un `tsconfig.json` mal réglé laisse passer des erreurs que TypeScript pourrait détecter. Ce chapitre t'apprend à configurer le compilateur, à organiser les types d'un projet, à mettre en place ESLint, Prettier et des tests, et à migrer progressivement un projet JavaScript.

À la fin du chapitre, tu seras capable de :

- lire et régler un `tsconfig.json` (`strict`, `target`, `module`, `paths`, `include`) ;
- expliquer les options de rigueur les plus importantes ;
- utiliser les fichiers de déclaration `.d.ts` et les paquets `@types` ;
- configurer ESLint avec `typescript-eslint` et Prettier ;
- écrire des tests typés avec Vitest ;
- mettre en place un script de vérification pour la CI ;
- migrer un projet JavaScript vers TypeScript par étapes.

Prérequis : les chapitres précédents et Node.js 18 ou plus. Prévois deux heures et demie.

## Le fichier tsconfig.json

Ce fichier à la racine du projet dit au compilateur **quoi vérifier** et **comment**. Voici une configuration moderne pour une application front-end avec Vite :

```json
{
  "compilerOptions": {
    "target": "ES2022",
    "lib": ["ES2022", "DOM", "DOM.Iterable"],
    "module": "ESNext",
    "moduleResolution": "bundler",
    "jsx": "react-jsx",
    "strict": true,
    "noUncheckedIndexedAccess": true,
    "noImplicitReturns": true,
    "noFallthroughCasesInSwitch": true,
    "noUnusedLocals": true,
    "noUnusedParameters": true,
    "isolatedModules": true,
    "skipLibCheck": true,
    "noEmit": true,
    "baseUrl": ".",
    "paths": { "@/*": ["src/*"] }
  },
  "include": ["src"],
  "exclude": ["node_modules", "dist"]
}
```

Passons en revue les groupes d'options.

### Cible et modules

| Option | Rôle |
| --- | --- |
| `target` | Version de JavaScript visée ; les syntaxes plus récentes sont transformées |
| `lib` | API disponibles dans les types (`DOM` pour le navigateur) |
| `module` et `moduleResolution` | Façon de résoudre les `import` ; `bundler` convient aux projets Vite et Next.js |
| `jsx` | Transformation du JSX (`react-jsx` pour React moderne) |
| `noEmit` | Ne produit aucun fichier : le bundler s'en charge, `tsc` ne fait que vérifier |
| `isolatedModules` | Garantit que chaque fichier peut être transformé indépendamment |

### La rigueur : strict et ses amis

`"strict": true` active un groupe d'options. Les plus importantes :

- **`strictNullChecks`** : `null` et `undefined` ne sont plus assignables partout. C'est l'option qui évite le plus de bugs.
- **`noImplicitAny`** : interdit les `any` implicites (un paramètre sans type est une erreur).
- **`strictFunctionTypes`** et **`strictPropertyInitialization`** : vérifient la cohérence des signatures et l'initialisation des propriétés de classes.

À activer en plus du mode strict :

- **`noUncheckedIndexedAccess`** : `tableau[0]` devient `T | undefined`, ce qui reflète la réalité.
- **`noImplicitReturns`** : toutes les branches d'une fonction doivent retourner une valeur.
- **`noUnusedLocals`** et **`noUnusedParameters`** : repèrent le code mort.
- **`exactOptionalPropertyTypes`** (optionnel, plus strict) : distingue une propriété absente d'une propriété valant `undefined`.

```ts
const lecons: string[] = ['Types'];
const premiere = lecons[0];
console.log(premiere.toUpperCase());
// Avec noUncheckedIndexedAccess : Erreur, 'premiere' is possibly 'undefined'
console.log(premiere?.toUpperCase());   // OK
```

> **Attention** : ne désactive pas `strict` pour « avancer plus vite ». Les erreurs que tu fais taire aujourd'hui reviennent en production.

### Les alias de chemins

Les imports relatifs profonds (`../../../components/Bouton`) sont pénibles. Avec `paths`, tu écris `@/components/Bouton`. Le compilateur comprend l'alias, mais le **bundler** doit le connaître aussi. Avec Vite :

```ts
// vite.config.ts
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: { '@': fileURLToPath(new URL('./src', import.meta.url)) },
  },
});
```

:::quiz
Que fait l'option `strictNullChecks` ?
- [ ] Elle supprime les valeurs null à l'exécution
- [x] Elle interdit d'utiliser `null` et `undefined` là où un autre type est attendu sans vérification
- [ ] Elle accélère la compilation
- [ ] Elle convertit automatiquement null en chaîne vide
> Avec `strictNullChecks`, `null` et `undefined` ne sont plus des valeurs valides de tous les types. TypeScript t'oblige donc à les traiter explicitement.
:::

## Fichiers de déclaration et paquets @types

Quand tu importes une bibliothèque JavaScript sans types, TypeScript ne sait pas ce qu'elle expose. Deux solutions.

1. **Paquet `@types`** : la communauté publie des types pour de nombreuses bibliothèques.

```bash
npm install --save-dev @types/node
```

2. **Fichier de déclaration `.d.ts`** : tu décris toi-même les types.

```ts
// src/types/global.d.ts
declare module 'ancienne-lib' {
  export function calculer(valeur: number): number;
}

// Étendre un type global existant
interface Window {
  dataLayer: unknown[];
}
```

Les bibliothèques modernes (React Query, Zod, Inertia) embarquent leurs propres types ; tu n'as rien de plus à installer. Pour les variables d'environnement avec Vite :

```ts
// src/vite-env.d.ts
interface ImportMetaEnv {
  readonly VITE_API_URL: string;
}
interface ImportMeta {
  readonly env: ImportMetaEnv;
}
```

## Imports de types

Depuis TypeScript 5, distingue clairement les imports qui ne servent qu'au typage. Ils sont supprimés à la compilation, ce qui évite des cycles et allège le bundle :

```ts
import type { Lecon } from '@/types';
import { type Roadmap, chargerRoadmaps } from '@/api';
```

L'option `verbatimModuleSyntax` renforce cette discipline en exigeant `import type` pour les types.

## ESLint avec typescript-eslint

Le compilateur détecte les erreurs de type. **ESLint** repère les mauvaises pratiques : promesse non attendue, `any` explicite, variable inutilisée. Installe la configuration officielle :

```bash
npm install --save-dev eslint @eslint/js typescript-eslint
```

Crée `eslint.config.js` :

```js
import js from '@eslint/js';
import tseslint from 'typescript-eslint';

export default tseslint.config(
  { ignores: ['dist', 'node_modules'] },
  js.configs.recommended,
  ...tseslint.configs.recommendedTypeChecked,
  {
    languageOptions: {
      parserOptions: { projectService: true, tsconfigRootDir: import.meta.dirname },
    },
    rules: {
      '@typescript-eslint/no-explicit-any': 'error',
      '@typescript-eslint/no-floating-promises': 'error',
      '@typescript-eslint/consistent-type-imports': 'error',
      '@typescript-eslint/no-unused-vars': ['error', { argsIgnorePattern: '^_' }],
    },
  }
);
```

Règles particulièrement précieuses :

- **`no-floating-promises`** : signale une promesse oubliée (sans `await` ni `catch`), source classique de bugs silencieux ;
- **`no-explicit-any`** : empêche l'introduction de `any` ;
- **`consistent-type-imports`** : impose `import type` ;
- **`no-unnecessary-condition`** : détecte les vérifications toujours vraies ou fausses.

```ts
async function sauvegarder(): Promise<void> { /* ... */ }

function surClic() {
  sauvegarder();          // ESLint : promesse flottante
  void sauvegarder();     // OK : intention explicite d'ignorer
}
```

Ajoute **Prettier** pour le formatage, comme en JavaScript : `npm install --save-dev prettier`, un fichier `.prettierrc` et un script `format`. ESLint se charge de la qualité du code, Prettier de sa mise en forme : ne leur fais pas faire le travail de l'autre.

## Scripts de vérification

Réunis tout dans `package.json` :

```json
{
  "scripts": {
    "dev": "vite",
    "build": "tsc --noEmit && vite build",
    "typecheck": "tsc --noEmit",
    "lint": "eslint .",
    "format": "prettier --write .",
    "test": "vitest run",
    "verifier": "npm run typecheck && npm run lint && npm run test"
  }
}
```

`npm run verifier` doit réussir **avant chaque commit** et dans la CI. Voici un exemple de workflow GitHub Actions :

```yaml
name: CI
on: [push, pull_request]
jobs:
  verifier:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: npm
      - run: npm ci
      - run: npm run verifier
```

:::quiz
Quelle règle ESLint détecte une promesse lancée sans `await` ni `catch` ?
- [ ] no-explicit-any
- [x] @typescript-eslint/no-floating-promises
- [ ] consistent-type-imports
- [ ] no-unused-vars
> `no-floating-promises` signale les promesses dont le résultat ou l'erreur est ignoré, ce qui cache de nombreux bugs asynchrones.
:::

## Tester du code TypeScript avec Vitest

Vitest comprend TypeScript sans configuration supplémentaire. Tes tests sont eux aussi typés : une erreur de signature est détectée tout de suite.

```ts
// src/calculs.ts
export interface Lecon {
  id: number;
  minutes: number;
  terminee: boolean;
}

export function progression(lecons: Lecon[]): number {
  if (lecons.length === 0) return 0;
  const faites = lecons.filter((l) => l.terminee).length;
  return Math.round((faites / lecons.length) * 100);
}
```

```ts
// src/calculs.test.ts
import { describe, it, expect } from 'vitest';
import { progression, type Lecon } from './calculs';

const fabrique = (surcharge: Partial<Lecon> = {}): Lecon => ({
  id: 1,
  minutes: 60,
  terminee: false,
  ...surcharge,
});

describe('progression', () => {
  it('retourne 0 pour une liste vide', () => {
    expect(progression([])).toBe(0);
  });

  it('calcule le pourcentage de leçons terminées', () => {
    const lecons = [fabrique({ id: 1, terminee: true }), fabrique({ id: 2 })];
    expect(progression(lecons)).toBe(50);
  });
});
```

La petite fonction `fabrique` utilise `Partial<Lecon>` pour créer des données de test en ne précisant que ce qui compte. Pour vérifier les **types eux-mêmes**, Vitest propose `expectTypeOf` :

```ts
import { expectTypeOf } from 'vitest';

expectTypeOf(progression).returns.toEqualTypeOf<number>();
```

## Migrer un projet JavaScript vers TypeScript

On ne réécrit pas tout d'un coup. Voici une stratégie progressive et sûre.

1. Installe TypeScript et crée un `tsconfig.json` avec `"allowJs": true` et `"checkJs": false`. Le projet continue de fonctionner tel quel.
2. Renomme les fichiers un par un, en commençant par les **feuilles** (utilitaires sans dépendances), de `.js` en `.ts` ou `.tsx`.
3. Ajoute les types des paramètres et des retours. Utilise `unknown` plutôt que `any` quand tu hésites.
4. Corrige les erreurs, puis commite. Chaque fichier migré doit laisser `tsc --noEmit` au vert.
5. Active les options de rigueur une à une (`noImplicitAny`, puis `strictNullChecks`, puis `strict`).
6. À la fin, retire `allowJs` et active `strict` pour de bon.

Pour un fichier encore difficile, un commentaire `// @ts-expect-error: raison` vaut mieux que `@ts-ignore` : il échoue si l'erreur disparaît, ce qui t'évite de garder une exception inutile.

## Atelier guidé : un projet TypeScript solide

Compte une heure et demie.

1. Crée un projet avec `npm create vite@latest qualite-ts -- --template react-ts` et ouvre `tsconfig.app.json` (ou `tsconfig.json`).
2. Active `noUncheckedIndexedAccess`, `noImplicitReturns` et `noFallthroughCasesInSwitch`, puis lance `npx tsc --noEmit` et corrige les nouvelles erreurs.
3. Configure l'alias `@/` dans `tsconfig` et `vite.config.ts`, puis remplace un import relatif par l'alias.
4. Crée `src/vite-env.d.ts` avec une variable `VITE_API_URL` typée et utilise-la dans un composant.
5. Installe `typescript-eslint` et configure `eslint.config.js` avec les règles `no-explicit-any`, `no-floating-promises` et `consistent-type-imports`.
6. Écris volontairement un `any` et une promesse oubliée, lance `npm run lint` et corrige.
7. Ajoute Prettier et un script `format`.
8. Écris `src/calculs.ts` avec `progression` et trois tests Vitest, dont un avec la fabrique `Partial<Lecon>`.
9. Ajoute le script `verifier` et le workflow GitHub Actions de ce chapitre.
10. Migre un petit fichier JavaScript de ton choix en TypeScript en suivant la démarche indiquée.

Pour t'auto-évaluer : sans regarder, cite quatre options de `tsconfig.json` qui rendent le code plus sûr et explique pour chacune l'erreur qu'elle évite.

## Erreurs fréquentes

- **Désactiver `strict` pour faire taire les erreurs.** Tu abandonnes la protection principale.
- **Confondre `tsc` et le bundler.** Le bundler (Vite) transforme sans vérifier les types ; seule la commande `tsc --noEmit` les contrôle.
- **Configurer `paths` seulement dans `tsconfig`.** Le bundler doit connaître l'alias lui aussi.
- **Laisser des `@ts-ignore` sans explication.** Préfère `@ts-expect-error` avec la raison.
- **Installer des `@types` inutiles.** Beaucoup de bibliothèques embarquent déjà leurs types.
- **Ne pas vérifier les types en CI.** Le build Vite réussit même avec des erreurs de type.
- **Importer un type comme valeur.** Utilise `import type` pour éviter des dépendances circulaires.
- **Migrer tout d'un coup.** Une migration progressive, fichier par fichier, est moins risquée.

## Bonnes pratiques

- Démarre tout nouveau projet avec `strict: true` et `noUncheckedIndexedAccess`.
- Un seul script `verifier` (types, lint, tests) lancé en local et en CI.
- Ajoute des règles ESLint qui utilisent les informations de types (`no-floating-promises`).
- Documente les exceptions : toute suppression d'erreur porte un commentaire expliquant pourquoi.
- Garde les fichiers `.d.ts` dans un dossier dédié (`src/types`).
- Mets à jour TypeScript régulièrement : chaque version améliore l'inférence et les messages d'erreur.
- Valide les données externes à l'exécution et fais confiance aux types ensuite.

## À retenir

- `tsconfig.json` décide de ce que TypeScript vérifie : `strict` est indispensable.
- `noUncheckedIndexedAccess`, `noImplicitReturns` et `noUnusedLocals` complètent utilement le mode strict.
- `paths` crée des alias d'import, à déclarer aussi dans le bundler.
- Les paquets `@types` et les fichiers `.d.ts` apportent des types aux bibliothèques qui n'en ont pas.
- ESLint avec `typescript-eslint` et Prettier maintiennent la qualité et la cohérence.
- Vitest teste du TypeScript tel quel, `expectTypeOf` teste les types eux-mêmes.
- Une migration depuis JavaScript se fait par étapes, avec `allowJs`, puis en renforçant la rigueur.
