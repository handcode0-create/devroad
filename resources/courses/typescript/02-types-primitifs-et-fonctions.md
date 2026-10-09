---
title: Types primitifs et fonctions
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Maintenant que tu sais ce qu'est TypeScript, passons à ses briques de base : les types des valeurs simples, les tableaux, les tuples, et surtout la façon de typer des **fonctions**. Une fonction bien typée est une documentation vivante : on sait ce qu'elle attend et ce qu'elle retourne sans lire son code.

À la fin du chapitre, tu seras capable de :

- utiliser `string`, `number`, `boolean`, `null`, `undefined` et `bigint` ;
- typer des tableaux et des tuples ;
- distinguer `any`, `unknown` et `never` et préférer `unknown` ;
- typer les paramètres, les valeurs de retour et les paramètres optionnels ;
- définir des types littéraux et des énumérations simples ;
- typer une fonction passée en callback ;
- gérer `null` et `undefined` grâce au rétrécissement de type (*narrowing*).

Prérequis : le chapitre « Découvrir TypeScript ». Prévois deux heures. Utilise le Playground ou un projet avec `tsx`.

## Les types primitifs

```ts
const titre: string = 'Closures';
const minutes: number = 120;
const termine: boolean = true;
const absent: null = null;
const nonDefini: undefined = undefined;
const grandNombre: bigint = 9007199254740993n;
```

`number` couvre entiers et décimaux. `bigint` sert pour des entiers énormes, rarement utile au quotidien. Un `null` ou `undefined` isolé n'est pas très utile ; ils servent surtout dans les **unions** (`string | null`) vues plus loin.

### Les types littéraux

Un type peut être une **valeur précise** plutôt qu'une famille de valeurs :

```ts
let niveau: 'beginner' | 'intermediate' | 'professional' = 'beginner';

niveau = 'intermediate';   // OK
niveau = 'expert';
// Erreur : Type '"expert"' is not assignable to type '"beginner" | "intermediate" | "professional"'.
```

C'est l'un des outils les plus utiles de TypeScript : tu interdis les valeurs hors liste, et l'éditeur te propose automatiquement les choix valides. Les littéraux fonctionnent aussi pour les nombres (`1 | 2 | 3`) et les booléens.

## Les tableaux et les tuples

Deux notations équivalentes pour un tableau :

```ts
const durees: number[] = [90, 120, 150];
const tags: Array<string> = ['js', 'ts'];

durees.push(60);        // OK
durees.push('soixante');
// Erreur : Argument of type 'string' is not assignable to parameter of type 'number'.
```

Un **tuple** est un tableau de longueur fixe où chaque position a son type :

```ts
const point: [number, number] = [48.85, 2.35];
const paire: [string, number] = ['Laravel', 12];

const [nom, lecons] = paire;   // nom: string, lecons: number
```

Les tuples sont utiles pour retourner plusieurs valeurs ; tu les croiseras avec le hook `useState` de React, qui retourne `[valeur, setValeur]`.

Pour un tableau qu'on ne veut pas modifier, utilise `readonly` :

```ts
const NIVEAUX: readonly string[] = ['beginner', 'intermediate'];
NIVEAUX.push('professional');
// Erreur : Property 'push' does not exist on type 'readonly string[]'.
```

## any, unknown et never

### any : l'échappatoire à éviter

`any` désactive la vérification pour une valeur : tout est permis.

```ts
let valeur: any = 'texte';
valeur.nimporteQuoi();     // aucune erreur... mais plantage à l'exécution
```

Chaque `any` est un trou dans ton filet de sécurité, et il se propage : une valeur dérivée d'un `any` devient elle aussi `any`.

### unknown : la bonne alternative

`unknown` signifie « je ne sais pas encore ce que c'est ». Tu **dois vérifier** le type avant de t'en servir :

```ts
function afficherLongueur(valeur: unknown): number {
  if (typeof valeur === 'string') {
    return valeur.length;    // ici TypeScript sait que c'est un string
  }
  return 0;
}
```

Pour toute donnée dont tu ne maîtrises pas la forme (réponse d'API, `JSON.parse`, erreur dans un `catch`), préfère `unknown` à `any`.

### never : l'impossible

`never` désigne une valeur qui ne peut jamais exister : une fonction qui lève toujours une erreur, ou un cas « impossible » dans une vérification exhaustive. Nous l'utiliserons dans le chapitre sur les unions.

:::quiz
Pourquoi préfère-t-on `unknown` à `any` pour une valeur dont on ne connaît pas le type ?
- [ ] Parce que `unknown` est plus rapide à l'exécution
- [ ] Parce que `any` n'existe plus en TypeScript
- [x] Parce que `unknown` oblige à vérifier le type avant l'utilisation
- [ ] Parce que `unknown` convertit automatiquement les valeurs
> `any` désactive toute vérification, alors que `unknown` force à rétrécir le type (par exemple avec `typeof`) avant d'accéder aux propriétés de la valeur.
:::

## Typer une fonction

Une signature de fonction contient le type de chaque paramètre et celui du retour.

```ts
function calculerPrixTTC(prixHT: number, taux: number): number {
  return prixHT * (1 + taux);
}

const double = (n: number): number => n * 2;
```

Si tu oublies le type de retour, TypeScript l'infère. Il est pourtant recommandé de l'écrire pour les fonctions exportées : si tu modifies le corps par erreur, le compilateur détecte l'incohérence avec le contrat.

### Paramètres optionnels et par défaut

```ts
function saluer(prenom: string, salutation?: string): string {
  return `${salutation ?? 'Bonjour'} ${prenom}`;
}

function creerMessage(texte: string, niveau: 'info' | 'erreur' = 'info'): string {
  return `[${niveau.toUpperCase()}] ${texte}`;
}

saluer('Awa');            // OK : salutation est string | undefined
creerMessage('Chargé');   // OK : niveau vaut 'info'
```

Un paramètre optionnel (`?`) a pour type `string | undefined` dans la fonction : il faut donc gérer le cas absent. Les paramètres optionnels doivent venir **après** les obligatoires.

### Paramètre rest

```ts
function somme(...nombres: number[]): number {
  return nombres.reduce((total, n) => total + n, 0);
}
```

### Retour `void`

Une fonction qui ne retourne rien a pour type de retour `void` :

```ts
function journaliser(message: string): void {
  console.log(message);
}
```

### Typer une fonction passée en argument

On décrit la signature d'un callback avec la syntaxe des flèches :

```ts
function repeter(fois: number, action: (index: number) => void): void {
  for (let i = 0; i < fois; i++) {
    action(i);
  }
}

repeter(3, (i) => console.log(i));   // i est inféré comme number
```

On peut nommer cette signature pour la réutiliser :

```ts
type Predicat = (valeur: number) => boolean;

const estPair: Predicat = (n) => n % 2 === 0;
```

Dans une fonction passée directement comme argument, TypeScript infère les types des paramètres du callback : pas besoin de les annoter (`i` plus haut).

:::quiz
Quel est le type d'un paramètre déclaré `titre?: string` à l'intérieur de la fonction ?
- [ ] string
- [x] string | undefined
- [ ] null
- [ ] unknown
> Un paramètre optionnel peut être absent, donc il vaut `string` ou `undefined`. Il faut gérer ce second cas avant de l'utiliser comme une chaîne.
:::

## null, undefined et le rétrécissement de type

Avec l'option `strict` (activée dans tout bon projet), `null` et `undefined` ne sont **pas** des valeurs valides pour les autres types. C'est la fameuse « erreur à un milliard de dollars » enfin maîtrisée.

```ts
function longueurTitre(titre: string | null): number {
  return titre.length;
  // Erreur : 'titre' is possibly 'null'.
}
```

Pour corriger, on **rétrécit** le type (*narrowing*) avec une vérification que TypeScript comprend :

```ts
function longueurTitre(titre: string | null): number {
  if (titre === null) {
    return 0;
  }
  return titre.length;   // ici titre est string
}
```

Les techniques de rétrécissement courantes :

```ts
// 1. typeof pour les primitifs
function formater(valeur: string | number): string {
  return typeof valeur === 'number' ? valeur.toFixed(2) : valeur.toUpperCase();
}

// 2. Vérification de vérité (truthiness)
function afficher(titre?: string): string {
  if (!titre) return 'Sans titre';
  return titre;
}

// 3. Accès sécurisé et valeur par défaut
const longueur = utilisateur?.nom?.length ?? 0;

// 4. Tableau : Array.isArray
function normaliser(entree: string | string[]): string[] {
  return Array.isArray(entree) ? entree : [entree];
}
```

> **Astuce** : évite l'opérateur de non-nullité `!` (par exemple `element!.value`). Il fait taire le compilateur sans rien vérifier. Préfère un `if` ou `?.`.

## Les énumérations et leurs alternatives

TypeScript propose `enum`, mais beaucoup d'équipes préfèrent les unions de littéraux, plus simples et sans code généré :

```ts
// Union de littéraux : recommandé
type Statut = 'a-faire' | 'en-cours' | 'termine';

// Objet constant, pour disposer des valeurs à l'exécution
const STATUTS = ['a-faire', 'en-cours', 'termine'] as const;
type StatutDepuisListe = (typeof STATUTS)[number];
```

`as const` fige le tableau (lecture seule, littéraux précis), et `(typeof STATUTS)[number]` extrait l'union des éléments : une seule source de vérité pour la liste et le type.

## Les assertions de type

Parfois, tu en sais plus que le compilateur. L'assertion `as` dit « fais-moi confiance » :

```ts
const champ = document.querySelector('#titre') as HTMLInputElement;
```

Une assertion **ne convertit rien** et ne vérifie rien : si l'élément n'existe pas ou n'est pas un `input`, tu auras une erreur à l'exécution. Utilise-la rarement, et préfère une vérification :

```ts
const element = document.querySelector('#titre');
if (element instanceof HTMLInputElement) {
  console.log(element.value);
}
```

:::quiz
Quelle écriture rétrécit correctement `valeur` de `string | number` à `string` ?
- [ ] if (valeur === 'string')
- [x] if (typeof valeur === 'string')
- [ ] if (valeur as string)
- [ ] if (valeur.type === string)
> `typeof valeur === 'string'` est une vérification reconnue par TypeScript : dans le bloc, `valeur` est typé `string`. Une assertion `as` ne vérifie rien.
:::

## Atelier guidé : une bibliothèque d'utilitaires typés

Compte une heure. Crée `utils.ts` dans un projet avec `strict` activé (`npx tsc --init` l'active par défaut).

1. Écris `formaterDuree(minutes: number): string` qui retourne « 2 h 30 » ou « 45 min ».
2. Définis `type Niveau = 'beginner' | 'intermediate' | 'professional'` et écris `libelleNiveau(niveau: Niveau): string` avec un `switch` ; essaie d'appeler la fonction avec `'expert'` et lis l'erreur.
3. Écris `moyenne(...notes: number[]): number` qui retourne 0 sans argument.
4. Écris `saluer(prenom: string, salutation?: string)` et utilise `??` pour la valeur par défaut.
5. Écris `longueurTitre(titre: string | null | undefined): number` en utilisant d'abord un `if`, puis `?.` et `??`.
6. Crée un tuple `type Couple = [string, number]` et une fonction `meilleurScore(scores: Couple[]): Couple | undefined`.
7. Écris `repeter(fois: number, action: (index: number) => void)` et appelle-la avec une flèche.
8. Écris `lireNombre(valeur: unknown): number` qui accepte un nombre ou une chaîne numérique et retourne 0 sinon, avec des `typeof` et `Number.isNaN`.
9. Définis `const NIVEAUX = ['beginner', 'intermediate', 'professional'] as const;` et déduis le type `Niveau` depuis cette liste.
10. Lance `npx tsc --noEmit` : aucune erreur ne doit rester.

Pour t'auto-évaluer : explique pourquoi `const x: any = ...` est un problème, et comment `unknown` plus un `typeof` le résout.

## Erreurs fréquentes

- **Utiliser `any` par réflexe.** Tu perds toute la protection ; prends `unknown` ou un vrai type.
- **Oublier de gérer `undefined` avec un paramètre optionnel.** L'erreur « possibly undefined » apparaît.
- **Écrire `string | null` puis utiliser la valeur sans vérifier.** Rétrécis avec un `if`.
- **Utiliser `!` ou `as` pour réduire au silence le compilateur.** Le bug réapparaît à l'exécution.
- **Placer un paramètre optionnel avant un paramètre obligatoire.** C'est refusé par le compilateur.
- **Confondre `void` et `undefined`.** `void` indique qu'on ignore la valeur de retour ; ne l'utilise que comme type de retour.
- **Modifier un tableau `readonly`.** Utilise `map` ou le spread pour créer une nouvelle version.
- **Taper un tableau vide sans annotation.** `const liste = []` s'infère mal ; écris `const liste: string[] = []`.

## Bonnes pratiques

- Active le mode `strict` dès le premier jour.
- Écris toujours les types de paramètres, et ceux de retour pour les fonctions exportées.
- Remplace les chaînes magiques par des unions de littéraux.
- Pour les données inconnues, utilise `unknown` et rétrécis.
- Préfère `?.` et `??` aux assertions de non-nullité.
- Dérive les types depuis des constantes (`as const`) pour éviter les doublons.
- Nomme les signatures de callbacks répétées avec `type`.

## À retenir

- Types de base : `string`, `number`, `boolean`, `null`, `undefined`, `bigint`, en minuscules.
- Un type littéral restreint à des valeurs précises : `'beginner' | 'intermediate'`.
- Tableaux : `number[]` ; tuples : `[string, number]` ; `readonly` interdit la modification.
- `any` désactive les vérifications ; `unknown` oblige à rétrécir ; `never` représente l'impossible.
- Une fonction se type par ses paramètres et son retour ; `?` rend un paramètre optionnel (`T | undefined`).
- En mode `strict`, `null` et `undefined` doivent être traités explicitement par rétrécissement.
- `as` et `!` ne vérifient rien : utilise-les avec parcimonie.
