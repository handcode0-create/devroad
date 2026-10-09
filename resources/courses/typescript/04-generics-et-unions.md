---
title: Generics et unions
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Deux fonctionnalités donnent à TypeScript toute sa puissance. Les **unions** décrivent une valeur qui peut avoir plusieurs formes (un résultat réussi **ou** une erreur). Les **generics** permettent d'écrire du code réutilisable pour n'importe quel type sans perdre la sécurité. Ensemble, ils te permettent de modéliser très précisément les états d'une application.

À la fin du chapitre, tu seras capable de :

- écrire des unions de types et les rétrécir (*narrowing*) ;
- construire des **unions discriminées** pour modéliser des états ;
- faire une vérification exhaustive avec `never` ;
- écrire des fonctions, interfaces et types génériques ;
- contraindre un générique avec `extends` ;
- utiliser des **type guards** personnalisés ;
- typer une réponse d'API générique.

Prérequis : les chapitres précédents, en particulier interfaces et types utilitaires. Prévois deux heures et demie.

## Les unions

Une union `A | B` signifie « soit A, soit B ». Tu l'as déjà croisée avec `string | null`.

```ts
type Identifiant = string | number;

function afficherId(id: Identifiant): string {
  if (typeof id === 'number') {
    return `#${id.toString().padStart(4, '0')}`;
  }
  return id.toUpperCase();
}
```

Tant que tu n'as pas rétréci le type, tu ne peux utiliser que ce qui est **commun** à tous les membres de l'union. Dans le `if`, TypeScript sait que `id` est un `number` ; après, c'est un `string`. Les vérifications reconnues sont `typeof`, `instanceof`, l'égalité (`===`), `in` et la vérification de vérité.

```ts
function longueur(x: string | string[]): number {
  return x.length;   // OK : length existe sur les deux
}

function majuscule(x: string | string[]): string | string[] {
  return x.toUpperCase();
  // Erreur : toUpperCase n'existe pas sur string[]
}
```

### L'opérateur in

Pour distinguer deux objets, vérifie la présence d'une propriété :

```ts
interface Chat { miauler(): void }
interface Chien { aboyer(): void }

function parler(animal: Chat | Chien) {
  if ('miauler' in animal) animal.miauler();
  else animal.aboyer();
}
```

## Les unions discriminées

Le motif le plus utile en pratique. Chaque membre de l'union possède une propriété commune, le **discriminant**, dont la valeur est un littéral unique. TypeScript s'en sert pour savoir exactement de quelle forme il s'agit.

Modélisons l'état d'un chargement de données, comme dans une page DevRoad :

```ts
interface Roadmap { id: number; titre: string }

type EtatChargement =
  | { statut: 'inactif' }
  | { statut: 'chargement' }
  | { statut: 'succes'; donnees: Roadmap[] }
  | { statut: 'erreur'; message: string };

function decrire(etat: EtatChargement): string {
  switch (etat.statut) {
    case 'inactif':
      return 'Rien à afficher';
    case 'chargement':
      return 'Chargement…';
    case 'succes':
      return `${etat.donnees.length} roadmaps`;   // donnees est disponible ici
    case 'erreur':
      return `Erreur : ${etat.message}`;          // message est disponible ici
  }
}
```

Dans chaque `case`, TypeScript rétrécit automatiquement l'union : `etat.donnees` n'existe que pour `'succes'`. Les **états impossibles** sont éliminés par construction : on ne peut pas avoir `statut: 'chargement'` **et** des données en même temps. Comparé à un objet `{ chargement: boolean; erreur?: string; donnees?: Roadmap[] }`, c'est bien plus sûr.

> **Astuce** : en React, les unions discriminées remplacent avantageusement trois `useState` séparés (`isLoading`, `error`, `data`) dont les combinaisons peuvent être incohérentes.

### La vérification exhaustive avec never

Si tu ajoutes un nouveau statut à l'union, tu veux que le compilateur te signale tous les `switch` à compléter. Il suffit d'un cas final qui n'accepte que `never` :

```ts
function verifierExhaustif(valeur: never): never {
  throw new Error(`Cas non géré : ${JSON.stringify(valeur)}`);
}

function icone(etat: EtatChargement): string {
  switch (etat.statut) {
    case 'inactif':
    case 'chargement':
      return 'clock';
    case 'succes':
      return 'check';
    case 'erreur':
      return 'alert';
    default:
      return verifierExhaustif(etat);   // erreur de compilation si un cas manque
  }
}
```

Quand tous les cas sont couverts, `etat` vaut `never` dans le `default`. Si tu ajoutes `{ statut: 'annule' }` à l'union, la ligne du `default` devient une erreur : le compilateur t'a rappelé qu'il reste du travail.

:::quiz
Qu'est-ce qui caractérise une union discriminée ?
- [ ] Elle contient uniquement des types primitifs
- [x] Chaque membre possède une propriété commune à valeur littérale unique qui permet de les distinguer
- [ ] Elle ne peut pas être utilisée dans un `switch`
- [ ] Elle remplace les interfaces
> Le discriminant (par exemple `statut`) permet à TypeScript de rétrécir l'union dans un `switch` ou un `if` et de savoir quelles propriétés sont disponibles.
:::

## Les generics

Imagine une fonction qui retourne le premier élément d'un tableau. Sans generics, tu dois choisir entre dupliquer le code pour chaque type ou utiliser `any` :

```ts
function premierAny(liste: any[]): any {
  return liste[0];
}

const x = premierAny([1, 2, 3]);   // x : any — on a perdu le type
```

Un **generic** est un « type en paramètre ». On le déclare entre chevrons :

```ts
function premier<T>(liste: T[]): T | undefined {
  return liste[0];
}

const n = premier([1, 2, 3]);          // n : number | undefined
const s = premier(['a', 'b']);         // s : string | undefined
const l = premier<Roadmap>([]);        // type fourni explicitement
```

`T` est un nom de variable de type (par convention `T`, `U`, `K`, `V`). TypeScript l'**infère** à partir des arguments dans presque tous les cas : pas besoin de l'écrire à l'appel.

### Generics et fonctions utilitaires

Voici trois fonctions que tu écriras réellement dans un projet :

```ts
function grouperPar<T, K extends string | number>(
  liste: T[],
  cle: (element: T) => K
): Record<K, T[]> {
  const resultat = {} as Record<K, T[]>;
  for (const element of liste) {
    const groupe = cle(element);
    (resultat[groupe] ??= []).push(element);
  }
  return resultat;
}

interface Lecon { id: number; titre: string; niveau: 'beginner' | 'intermediate' }
const lecons: Lecon[] = [
  { id: 1, titre: 'Types', niveau: 'beginner' },
  { id: 2, titre: 'Generics', niveau: 'intermediate' },
];

const parNiveau = grouperPar(lecons, (l) => l.niveau);
// Record<'beginner' | 'intermediate', Lecon[]>
```

### Contraindre un générique avec extends

Parfois, `T` doit respecter une condition. `T extends { id: number }` signifie « tout type qui possède au moins une propriété `id` de type nombre » :

```ts
function trouverParId<T extends { id: number }>(liste: T[], id: number): T | undefined {
  return liste.find((element) => element.id === id);
}

trouverParId(lecons, 2);                 // OK
trouverParId([{ nom: 'Awa' }], 1);       // Erreur : pas de propriété id
```

Avec `keyof`, tu sécurises l'accès à une propriété :

```ts
function extraire<T, K extends keyof T>(objet: T, cle: K): T[K] {
  return objet[cle];
}

const titre = extraire(lecons[0], 'titre');   // string
const id = extraire(lecons[0], 'id');         // number
extraire(lecons[0], 'prix');                  // Erreur : 'prix' n'est pas une clé de Lecon
```

Le type de retour `T[K]` change selon la clé : le compilateur sait que `titre` est une chaîne et `id` un nombre.

:::quiz
Que signifie `<T extends { id: number }>` dans la signature d'une fonction ?
- [ ] T doit être exactement le type `{ id: number }`
- [x] T peut être n'importe quel type possédant au moins une propriété `id` de type `number`
- [ ] T est toujours un nombre
- [ ] T est optionnel
> `extends` pose une contrainte minimale : le type fourni peut avoir d'autres propriétés, mais il doit au moins respecter la forme demandée.
:::

## Interfaces et types génériques

Les generics ne servent pas qu'aux fonctions. Ils s'appliquent aux interfaces et aux alias.

```ts
interface ReponseApi<T> {
  succes: boolean;
  donnees: T;
  message?: string;
}

interface Pagination<T> {
  elements: T[];
  page: number;
  total: number;
}

type ReponseRoadmaps = ReponseApi<Pagination<Roadmap>>;
```

Tu peux aussi donner une **valeur par défaut** au type :

```ts
interface Resultat<T, E = Error> {
  donnees?: T;
  erreur?: E;
}
```

Une version plus rigoureuse du résultat utilise une union discriminée générique :

```ts
type Resultat<T, E = string> =
  | { ok: true; valeur: T }
  | { ok: false; erreur: E };

function diviser(a: number, b: number): Resultat<number> {
  if (b === 0) return { ok: false, erreur: 'Division par zéro' };
  return { ok: true, valeur: a / b };
}

const r = diviser(10, 2);
if (r.ok) {
  console.log(r.valeur);    // number
} else {
  console.log(r.erreur);    // string
}
```

Cette forme oblige l'appelant à **traiter l'erreur** avant d'accéder à la valeur, sans lever d'exception.

### Typer un appel d'API générique

Combine `fetch`, les generics et `async` :

```ts
async function getJson<T>(url: string): Promise<T> {
  const reponse = await fetch(url);
  if (!reponse.ok) {
    throw new Error(`Erreur HTTP ${reponse.status}`);
  }
  return (await reponse.json()) as T;
}

const roadmaps = await getJson<Roadmap[]>('/api/roadmaps');
```

> **Attention** : `as T` est une promesse, pas une vérification. Si l'API renvoie autre chose que `Roadmap[]`, TypeScript ne peut pas le savoir. Pour du code robuste, valide la réponse à l'exécution (avec une bibliothèque comme Zod, vue au chapitre sur la configuration).

## Les type guards personnalisés

Quand les vérifications intégrées ne suffisent pas, écris une fonction qui **déclare** à TypeScript comment rétrécir. Son type de retour est un prédicat `valeur is Type` :

```ts
interface Lecon { id: number; titre: string }

function estLecon(valeur: unknown): valeur is Lecon {
  return (
    typeof valeur === 'object' &&
    valeur !== null &&
    'id' in valeur &&
    typeof valeur.id === 'number' &&
    'titre' in valeur &&
    typeof valeur.titre === 'string'
  );
}

function traiter(entree: unknown) {
  if (estLecon(entree)) {
    console.log(entree.titre.toUpperCase());   // entree : Lecon
  }
}
```

Combiné à `unknown`, c'est la façon propre de valider des données externes. On l'utilise aussi pour filtrer les valeurs nulles d'un tableau :

```ts
const valeurs: (string | null)[] = ['a', null, 'b'];
const textes = valeurs.filter((v): v is string => v !== null);   // string[]
```

:::quiz
Quel est le rôle du type de retour `valeur is Lecon` dans une fonction ?
- [ ] Il convertit la valeur en `Lecon`
- [ ] Il valide automatiquement la valeur
- [x] Il indique à TypeScript que, si la fonction retourne true, la valeur est de type `Lecon`
- [ ] Il rend la fonction asynchrone
> Un prédicat de type informe le compilateur du résultat du test : dans le bloc `if`, la valeur est rétrécie. C'est à toi d'écrire un test correct.
:::

## Atelier guidé : un état de chargement et des utilitaires génériques

Compte une heure et demie. Crée `generics.ts` dans un projet avec `strict`.

1. Définis l'union discriminée `EtatChargement<T>` générique, avec les états `inactif`, `chargement`, `succes` (portant `donnees: T`) et `erreur` (portant `message: string`).
2. Écris `decrire<T>(etat: EtatChargement<T>): string` avec un `switch` exhaustif et une fonction `verifierExhaustif`.
3. Ajoute un cinquième statut `annule` et observe l'erreur du compilateur dans le `default`, puis complète les `switch`.
4. Écris `premier<T>`, `dernier<T>` et `compter<T>(liste: T[], predicat: (e: T) => boolean): number`.
5. Écris `grouperPar` comme dans le cours et teste-la sur des leçons groupées par niveau.
6. Écris `trouverParId<T extends { id: number }>` et teste-la avec un type qui n'a pas d'`id`.
7. Écris `Resultat<T, E>` et une fonction `parserNombre(texte: string): Resultat<number>` qui n'utilise aucune exception.
8. Écris `estLecon(valeur: unknown): valeur is Lecon`, puis une fonction `lireLecons(brut: string): Lecon[]` qui parse du JSON et filtre avec ce garde.
9. Écris `getJson<T>(url: string): Promise<T>` et appelle-la avec `https://jsonplaceholder.typicode.com/todos/1` en définissant l'interface de la réponse.
10. Lance `npx tsc --noEmit`.

Pour t'auto-évaluer : explique pourquoi `EtatChargement` est plus sûr que `{ chargement: boolean; erreur?: string; donnees?: T[] }`.

## Erreurs fréquentes

- **Utiliser `any` à la place d'un générique.** Tu perds le lien entre l'entrée et la sortie.
- **Oublier de rétrécir une union avant d'utiliser une propriété.** L'erreur indique la propriété absente d'un des membres.
- **Un `switch` sans cas par défaut exhaustif.** Un nouveau statut passe inaperçu.
- **Donner trop de paramètres génériques.** Si tu en as quatre, ta fonction fait probablement trop de choses.
- **Contraindre trop peu (`T`) ou trop (`T extends string`).** Pose seulement la condition dont le code a besoin.
- **Croire que `as T` valide les données.** C'est une assertion sans contrôle à l'exécution.
- **Écrire un type guard faux.** Le compilateur te fait confiance : un test incomplet crée de vrais bugs.
- **Utiliser des booléens multiples pour un état.** Préfère une union discriminée.

## Bonnes pratiques

- Modélise les états par des unions discriminées ; rends les états impossibles impossibles à écrire.
- Ajoute systématiquement une vérification exhaustive avec `never` sur les `switch` d'unions.
- Donne des noms explicites aux paramètres de type quand ils ne sont pas évidents (`TElement`, `TReponse`).
- Laisse TypeScript inférer les generics à l'appel ; fournis-les seulement si nécessaire.
- Utilise `unknown` et des type guards pour valider les données externes.
- Écris d'abord la fonction concrète, généralise ensuite quand le besoin apparaît à deux endroits.
- Limite la complexité : un type que personne ne comprend est une dette.

## À retenir

- Une union `A | B` représente plusieurs formes possibles ; on la rétrécit avec `typeof`, `in`, `===` ou `instanceof`.
- Une union discriminée possède une propriété littérale commune et permet des `switch` sûrs.
- `never` dans le `default` garantit l'exhaustivité du traitement.
- Un generic `<T>` paramètre un type : il relie l'entrée à la sortie sans `any`.
- `extends` contraint un générique, `keyof` sécurise les accès aux propriétés.
- Les interfaces et alias génériques (`ReponseApi<T>`, `Resultat<T, E>`) modélisent des structures réutilisables.
- Un type guard `valeur is Type` associé à `unknown` valide proprement les données externes.
