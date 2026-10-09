---
title: TypeScript avec React
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

React et TypeScript forment aujourd'hui le duo standard des interfaces web professionnelles. Les types détectent les props oubliées, les états incohérents et les mauvaises données avant même que tu ouvres le navigateur. DevRoad lui-même est construit sur ce principe : des composants React reçoivent des données venant de Laravel via Inertia, et les types décrivent exactement ce contrat.

À la fin du chapitre, tu seras capable de :

- créer un projet React + TypeScript avec Vite ;
- typer les props d'un composant, y compris `children` et les valeurs par défaut ;
- typer `useState`, `useRef`, `useReducer` et les contextes ;
- typer les événements (`onClick`, `onChange`, `onSubmit`) ;
- écrire un hook personnalisé générique ;
- typer les props d'une page Inertia venant de Laravel ;
- valider une réponse d'API à l'exécution avec Zod.

Prérequis : le chapitre « Generics et unions » et les bases de React (composants, props, état, effets). Prévois deux heures et demie.

## Démarrer un projet

Vite propose un modèle React + TypeScript :

```bash
npm create vite@latest devroad-ts -- --template react-ts
cd devroad-ts
npm install
npm run dev
```

Les composants ont l'extension **`.tsx`** (TypeScript + JSX). Les fichiers sans JSX restent en `.ts`. Le projet contient `tsconfig.json` avec `"strict": true` et `"jsx": "react-jsx"` : tu n'as plus besoin d'importer React dans chaque fichier.

Pour vérifier les types sans lancer l'application :

```bash
npx tsc --noEmit
```

## Typer les props d'un composant

Une fonction composant est une fonction normale : on type son paramètre `props`. On décrit les props avec une interface ou un alias, puis on les décompose.

```tsx
interface CarteLeconProps {
  titre: string;
  minutes: number;
  terminee?: boolean;
}

export function CarteLecon({ titre, minutes, terminee = false }: CarteLeconProps) {
  return (
    <article className={terminee ? 'carte carte--terminee' : 'carte'}>
      <h3>{titre}</h3>
      <p>{minutes} min</p>
    </article>
  );
}
```

Utilisation et erreurs détectées :

```tsx
<CarteLecon titre="Les types" minutes={90} />            {/* OK */}
<CarteLecon titre="Les types" />                          {/* Erreur : minutes manquante */}
<CarteLecon titre="Les types" minutes="90" />             {/* Erreur : string au lieu de number */}
<CarteLecon titre="Les types" minutes={90} couleur="a" /> {/* Erreur : prop inconnue */}
```

L'éditeur propose en plus l'autocomplétion des props. Pour un composant que tu ne connais pas, il suffit de taper `<` et le nom pour voir ce qu'il attend.

### children et composition

Pour un composant conteneur, utilise `ReactNode`, qui regroupe tout ce que React peut afficher (texte, éléments, tableaux, `null`) :

```tsx
import type { ReactNode } from 'react';

interface PanneauProps {
  titre: string;
  children: ReactNode;
}

export function Panneau({ titre, children }: PanneauProps) {
  return (
    <section>
      <h2>{titre}</h2>
      {children}
    </section>
  );
}
```

### Props de fonctions et unions de littéraux

Les callbacks se typent comme n'importe quelle fonction. Les unions de littéraux limitent les variantes d'un composant :

```tsx
type Variante = 'primaire' | 'secondaire' | 'danger';

interface BoutonProps {
  variante?: Variante;
  onClick: () => void;
  children: ReactNode;
}

export function Bouton({ variante = 'primaire', onClick, children }: BoutonProps) {
  return (
    <button className={`bouton bouton--${variante}`} onClick={onClick}>
      {children}
    </button>
  );
}
```

Pour reprendre toutes les props d'un élément HTML natif tout en en ajoutant, utilise les types intégrés de React :

```tsx
import type { ComponentPropsWithoutRef } from 'react';

interface ChampProps extends ComponentPropsWithoutRef<'input'> {
  label: string;
}

export function Champ({ label, id, ...reste }: ChampProps) {
  return (
    <label htmlFor={id}>
      {label}
      <input id={id} {...reste} />
    </label>
  );
}
```

:::quiz
Quel type utilise-t-on pour la prop `children` d'un composant conteneur ?
- [ ] string
- [ ] JSX
- [x] ReactNode
- [ ] Element[]
> `ReactNode` couvre tout ce que React peut rendre : texte, nombres, éléments, tableaux, fragments, `null` et `undefined`.
:::

## Typer l'état avec useState

Dans la plupart des cas, `useState` infère le type de la valeur initiale :

```tsx
const [compteur, setCompteur] = useState(0);        // number
const [recherche, setRecherche] = useState('');     // string
```

Il faut préciser le type avec un generic quand l'état initial ne dit pas tout : valeur `null`, tableau vide, ou union.

```tsx
interface Lecon { id: number; titre: string; terminee: boolean }

const [lecons, setLecons] = useState<Lecon[]>([]);
const [selection, setSelection] = useState<Lecon | null>(null);
const [niveau, setNiveau] = useState<'beginner' | 'intermediate' | 'professional'>('beginner');
```

Sans le generic, `useState([])` donnerait `never[]` et `useState(null)` donnerait `null` : rien d'autre ne pourrait être stocké.

La fonction de mise à jour est elle aussi typée, ce qui rend les modifications immuables sûres :

```tsx
function basculer(id: number) {
  setLecons((precedentes) =>
    precedentes.map((l) => (l.id === id ? { ...l, terminee: !l.terminee } : l))
  );
}
```

### Un état de chargement avec union discriminée

Réutilise l'union du chapitre précédent plutôt que trois `useState` séparés :

```tsx
type Etat<T> =
  | { statut: 'chargement' }
  | { statut: 'succes'; donnees: T }
  | { statut: 'erreur'; message: string };

function ListeRoadmaps() {
  const [etat, setEtat] = useState<Etat<Roadmap[]>>({ statut: 'chargement' });

  useEffect(() => {
    fetch('/api/roadmaps')
      .then((r) => (r.ok ? (r.json() as Promise<Roadmap[]>) : Promise.reject(new Error(`HTTP ${r.status}`))))
      .then((donnees) => setEtat({ statut: 'succes', donnees }))
      .catch((e: unknown) =>
        setEtat({ statut: 'erreur', message: e instanceof Error ? e.message : 'Erreur inconnue' })
      );
  }, []);

  if (etat.statut === 'chargement') return <p>Chargement…</p>;
  if (etat.statut === 'erreur') return <p role="alert">{etat.message}</p>;

  return (
    <ul>
      {etat.donnees.map((r) => (
        <li key={r.id}>{r.titre}</li>
      ))}
    </ul>
  );
}
```

Remarque `catch((e: unknown)` : une erreur capturée est `unknown`, pas `Error`. On la rétrécit avec `instanceof Error`.

## Typer les événements

React fournit des types d'événements génériques sur l'élément concerné.

```tsx
import type { ChangeEvent, FormEvent, MouseEvent } from 'react';

function FormulaireRecherche({ onRechercher }: { onRechercher: (terme: string) => void }) {
  const [terme, setTerme] = useState('');

  function surChangement(evenement: ChangeEvent<HTMLInputElement>) {
    setTerme(evenement.target.value);          // value : string
  }

  function surSoumission(evenement: FormEvent<HTMLFormElement>) {
    evenement.preventDefault();
    onRechercher(terme.trim());
  }

  function surClic(evenement: MouseEvent<HTMLButtonElement>) {
    console.log(evenement.currentTarget.name);
  }

  return (
    <form onSubmit={surSoumission}>
      <input value={terme} onChange={surChangement} />
      <button name="ok" onClick={surClic}>Chercher</button>
    </form>
  );
}
```

Quand le gestionnaire est écrit en ligne, TypeScript infère le type de l'événement tout seul : `onChange={(e) => setTerme(e.target.value)}` fonctionne sans annotation. N'annote que lorsque tu extrais la fonction.

:::quiz
Comment typer correctement un état qui contient d'abord `null` puis une leçon ?
- [ ] useState(null)
- [x] useState<Lecon | null>(null)
- [ ] useState<Lecon>(null)
- [ ] useState<any>()
> Sans generic, l'état serait typé `null` uniquement. L'union `Lecon | null` exprime les deux situations et force à vérifier `null` avant l'utilisation.
:::

## useRef, useReducer et le contexte

### useRef

Pour référencer un élément du DOM, précise son type et initialise à `null` :

```tsx
const champRef = useRef<HTMLInputElement>(null);

useEffect(() => {
  champRef.current?.focus();      // current : HTMLInputElement | null
}, []);

return <input ref={champRef} />;
```

### useReducer

Un reducer est un cas idéal pour les unions discriminées : chaque action est une forme précise.

```tsx
interface EtatPanier { articles: { id: number; quantite: number }[] }

type Action =
  | { type: 'ajouter'; id: number }
  | { type: 'retirer'; id: number }
  | { type: 'vider' };

function reducer(etat: EtatPanier, action: Action): EtatPanier {
  switch (action.type) {
    case 'ajouter': {
      const existant = etat.articles.find((a) => a.id === action.id);
      return existant
        ? { articles: etat.articles.map((a) => (a.id === action.id ? { ...a, quantite: a.quantite + 1 } : a)) }
        : { articles: [...etat.articles, { id: action.id, quantite: 1 }] };
    }
    case 'retirer':
      return { articles: etat.articles.filter((a) => a.id !== action.id) };
    case 'vider':
      return { articles: [] };
  }
}

const [etat, dispatch] = useReducer(reducer, { articles: [] });
dispatch({ type: 'ajouter', id: 5 });
dispatch({ type: 'supprimer', id: 5 });   // Erreur : action inconnue
```

### Context

Le contexte doit gérer le cas « pas de fournisseur ». Un hook dédié lève une erreur claire et évite de tester `null` partout :

```tsx
import { createContext, useContext, useState, type ReactNode } from 'react';

interface ThemeContext {
  theme: 'clair' | 'sombre';
  basculer: () => void;
}

const Contexte = createContext<ThemeContext | null>(null);

export function ThemeProvider({ children }: { children: ReactNode }) {
  const [theme, setTheme] = useState<ThemeContext['theme']>('clair');
  const basculer = () => setTheme((t) => (t === 'clair' ? 'sombre' : 'clair'));
  return <Contexte.Provider value={{ theme, basculer }}>{children}</Contexte.Provider>;
}

export function useTheme(): ThemeContext {
  const ctx = useContext(Contexte);
  if (ctx === null) throw new Error('useTheme doit être utilisé dans un ThemeProvider');
  return ctx;
}
```

## Un hook personnalisé générique

Les generics brillent dans les hooks réutilisables. Voici un hook qui synchronise un état avec `localStorage`, quel que soit le type :

```tsx
import { useState, useEffect } from 'react';

export function useLocalStorage<T>(cle: string, valeurInitiale: T) {
  const [valeur, setValeur] = useState<T>(() => {
    try {
      const brut = localStorage.getItem(cle);
      return brut === null ? valeurInitiale : (JSON.parse(brut) as T);
    } catch {
      return valeurInitiale;
    }
  });

  useEffect(() => {
    try {
      localStorage.setItem(cle, JSON.stringify(valeur));
    } catch {
      // stockage indisponible : on ignore
    }
  }, [cle, valeur]);

  return [valeur, setValeur] as const;
}

const [terminees, setTerminees] = useLocalStorage<number[]>('terminees', []);
```

`as const` fait retourner un **tuple** `[T, Dispatch<...>]` plutôt qu'un tableau flou. C'est ce qui donne à l'appelant les bons types sur chaque position.

## TypeScript et Inertia dans DevRoad

Avec Inertia, un contrôleur Laravel renvoie des props que la page React reçoit directement. Décris-les avec une interface :

```tsx
import { Head, usePage } from '@inertiajs/react';

interface Roadmap {
  id: number;
  slug: string;
  title: string;
  level: 'beginner' | 'intermediate' | 'professional';
  lessons_count: number;
}

interface PageProps {
  roadmaps: Roadmap[];
  filters: { search: string | null };
}

export default function Index({ roadmaps, filters }: PageProps) {
  return (
    <>
      <Head title="Roadmaps" />
      <p>Recherche : {filters.search ?? 'aucune'}</p>
      <ul>
        {roadmaps.map((r) => (
          <li key={r.id}>{r.title} ({r.lessons_count} leçons)</li>
        ))}
      </ul>
    </>
  );
}
```

Garde les noms de propriétés **identiques** à ceux que renvoie Laravel (souvent en `snake_case`). Les types restent une promesse : si le contrôleur change, ils ne s'ajusteront pas tout seuls. Une bonne pratique d'équipe est de centraliser ces interfaces dans un fichier `resources/js/types/index.d.ts`.

## Valider les données externes avec Zod

TypeScript ne vérifie pas ce que reçoit réellement l'application. **Zod** déclare un schéma à l'exécution, et en **déduit** le type : une seule définition pour deux usages.

```bash
npm install zod
```

```ts
import { z } from 'zod';

const SchemaLecon = z.object({
  id: z.number(),
  titre: z.string().min(1),
  minutes: z.number().int().positive(),
  niveau: z.enum(['beginner', 'intermediate', 'professional']),
});

type Lecon = z.infer<typeof SchemaLecon>;   // type déduit du schéma

async function chargerLecons(): Promise<Lecon[]> {
  const reponse = await fetch('/api/lecons');
  if (!reponse.ok) throw new Error(`HTTP ${reponse.status}`);
  const brut: unknown = await reponse.json();
  return z.array(SchemaLecon).parse(brut);   // lève une erreur si la forme est incorrecte
}
```

`parse` lève une exception détaillée si les données ne correspondent pas ; `safeParse` retourne un résultat `{ success, data | error }` sans exception.

:::quiz
Quel est l'intérêt principal de Zod avec TypeScript ?
- [ ] Il rend le code plus rapide
- [ ] Il remplace React
- [x] Il valide les données à l'exécution et en déduit le type TypeScript
- [ ] Il supprime le besoin de `tsc`
> Les types disparaissent à l'exécution. Zod vérifie les données réelles et fournit le type correspondant via `z.infer`, sans définition en double.
:::

## Atelier guidé : DevRoad Mini en React + TypeScript

Compte une heure et demie.

1. Crée le projet avec `npm create vite@latest devroad-ts -- --template react-ts` et vérifie que `npm run dev` fonctionne.
2. Crée `src/types.ts` avec `Niveau`, `Lecon` et `Roadmap`.
3. Écris le composant `CarteLecon` avec ses props, une prop optionnelle `terminee` et une prop `onBasculer: (id: number) => void`.
4. Écris le composant `Panneau` avec `children: ReactNode`.
5. Dans `App`, stocke les leçons avec `useState<Lecon[]>` et implémente la bascule avec une mise à jour immuable.
6. Ajoute un champ de recherche typé avec `ChangeEvent<HTMLInputElement>` et filtre la liste.
7. Écris `useLocalStorage<T>` et utilise-le pour mémoriser les identifiants terminés.
8. Crée `Etat<T>` en union discriminée et charge un fichier JSON local avec `fetch`, en affichant les trois états.
9. Installe Zod, déclare un schéma `SchemaLecon`, déduis le type et valide la réponse.
10. Lance `npx tsc --noEmit` et corrige toutes les erreurs.

Pour t'auto-évaluer : explique pourquoi `useState([])` pose problème et pourquoi `catch (e)` donne un `unknown`.

## Erreurs fréquentes

- **Oublier le generic de `useState` pour `null` ou un tableau vide.** Le type inféré est trop étroit.
- **Typer les props avec `any` ou `object`.** Tu perds l'autocomplétion et la sécurité.
- **Utiliser `React.FC` par habitude.** Une fonction avec un paramètre typé est plus simple et plus claire.
- **Utiliser `!` sur `ref.current`.** Si l'élément n'est pas monté, l'application plante ; préfère `?.`.
- **Annoter des événements en ligne alors que l'inférence suffit.** Cela alourdit sans rien apporter.
- **Faire confiance à `as Type` sur une réponse d'API.** Valide avec Zod ou un type guard.
- **Mal nommer les propriétés de props Inertia.** Si le contrôleur renvoie `lessons_count`, le type doit dire `lessons_count`.
- **Laisser un `useContext` retourner `null` sans le traiter.** Crée un hook qui lève une erreur explicite.

## Bonnes pratiques

- Interface `NomDuComposantProps` définie juste au-dessus du composant.
- Un fichier de types partagés pour les modèles du domaine ; les props locales restent dans leur composant.
- Préfère les unions de littéraux aux booléens multiples pour les variantes et les états.
- Utilise `ComponentPropsWithoutRef` pour envelopper un élément natif sans perdre ses attributs.
- Valide à la frontière (API, stockage) avec Zod, et fais confiance aux types à l'intérieur.
- Vérifie les types dans la CI avec `npx tsc --noEmit`.
- Garde le mode `strict` : c'est lui qui donne toute sa valeur à TypeScript.

## À retenir

- Les composants React se typent par leurs props : interface, valeurs par défaut, `children: ReactNode`.
- `useState<T>` se précise pour `null`, les tableaux vides et les unions.
- Les événements ont des types dédiés (`ChangeEvent`, `FormEvent`, `MouseEvent`), inférés quand le gestionnaire est en ligne.
- `useReducer` avec une union discriminée d'actions est sûr et exhaustif.
- Un hook générique comme `useLocalStorage<T>` se réutilise pour n'importe quel type.
- Les props Inertia se décrivent par une interface alignée sur les données de Laravel.
- Zod valide les données réelles et déduit le type : types et exécution restent cohérents.
