---
title: Server et Client Components
minutes: 140
level: intermediate
---

## Ce que tu vas apprendre

C'est le chapitre le plus important pour comprendre Next.js moderne. Depuis React 19 et l'App Router, une application se compose de deux familles de composants : ceux qui s'exécutent sur le **serveur** et ceux qui s'exécutent dans le **navigateur**. Bien choisir entre les deux détermine la performance, la sécurité et la simplicité de ton code.

À la fin du chapitre, tu seras capable de :

- expliquer ce qui distingue un Server Component d'un Client Component ;
- décider, pour chaque composant, lequel utiliser ;
- utiliser la directive `'use client'` et comprendre la **frontière** qu'elle crée ;
- composer les deux familles correctement (passer un composant serveur en `children` d'un composant client) ;
- charger des données dans un composant serveur et les transmettre à un composant client ;
- éviter les fuites de code ou de secrets vers le navigateur.

Prérequis : les chapitres précédents, ainsi que `useState` et `useEffect` de React. Prévois environ deux heures vingt.

## Deux mondes, un seul arbre

Dans une application Next.js, tes composants forment un arbre. Chaque nœud de cet arbre est soit un composant serveur, soit un composant client.

Un **Server Component** (composant serveur) :

- s'exécute uniquement sur le serveur, au build ou à la requête ;
- peut être `async` et lire directement une base de données, un fichier ou une API ;
- peut utiliser des secrets (clés d'API, variables non publiques) sans risque ;
- n'envoie **aucun JavaScript** au navigateur : seul le HTML résultant et un format interne léger sont transmis ;
- ne peut pas utiliser `useState`, `useEffect`, ni les événements (`onClick`), ni les API du navigateur (`window`, `localStorage`).

Un **Client Component** (composant client) :

- est pré-rendu en HTML sur le serveur, puis « hydraté » dans le navigateur, où son JavaScript s'exécute ;
- peut utiliser l'état, les effets, les événements et les API du navigateur ;
- ne peut pas être `async` ni lire directement une base de données.

| Besoin | Serveur | Client |
| --- | --- | --- |
| Lire une base de données ou une API privée | Oui | Non |
| Utiliser des secrets | Oui | Non |
| `useState`, `useEffect`, `useRef` | Non | Oui |
| Gérer un clic, une saisie | Non | Oui |
| Accéder à `window`, `localStorage` | Non | Oui |
| Réduire le JavaScript envoyé | Oui | Non |

Dans l'App Router, **un composant est un Server Component tant que tu ne dis pas le contraire**. C'est l'inverse d'une application React classique, où tout est client.

> **À retenir** : le réflexe moderne est « serveur par défaut, client seulement quand l'interaction l'exige ». Moins de JavaScript envoyé, c'est une application plus rapide, surtout sur mobile et avec un forfait data limité.

## La directive 'use client'

Pour transformer un composant en composant client, place la ligne `'use client'` tout en haut du fichier, avant les imports :

```tsx
// src/components/BoutonFavori.tsx
'use client';

import { useState } from 'react';

export default function BoutonFavori() {
  const [favori, setFavori] = useState(false);

  return (
    <button
      onClick={() => setFavori(!favori)}
      className="rounded border px-3 py-1"
      aria-pressed={favori}
    >
      {favori ? '★ Retiré des favoris' : '☆ Ajouter aux favoris'}
    </button>
  );
}
```

Sans la directive, cet exemple échouerait avec un message du type « You're importing a component that needs `useState`. It only works in a Client Component ». Next.js te le signale clairement.

### Ce que la directive déclenche vraiment

`'use client'` ne marque pas seulement un composant : elle crée une **frontière**. Tout ce que ce fichier importe, et tous les composants qu'il importe, deviennent partie du bundle client. Il n'est donc pas nécessaire (ni souhaitable) de répéter la directive dans chaque fichier enfant.

Conséquence pratique : **place `'use client'` le plus bas possible dans l'arbre**. Si toute une page a besoin d'un bouton interactif, ne transforme pas la page en composant client, isole seulement le bouton.

## Choisir : arbre de décision

Pose-toi ces questions dans l'ordre pour chaque composant :

1. Le composant utilise-t-il `useState`, `useEffect`, `useRef`, un contexte, ou un événement ? Si oui : **client**.
2. Utilise-t-il `window`, `document`, `localStorage` ou une API navigateur ? Si oui : **client**.
3. Utilise-t-il une bibliothèque qui dépend de l'état ou du navigateur (un carrousel, un graphique interactif) ? Si oui : **client**.
4. Sinon : **serveur** (c'est le défaut).

Un exemple concret, la fiche d'une roadmap sur DevRoad :

```text
PageRoadmap (serveur)         → charge la roadmap depuis la base
├── EnteteRoadmap (serveur)   → titre, description, niveau
├── ListeEtapes (serveur)     → affiche les étapes
│   └── BoutonTermine (client) → coche une étape (état, clic)
└── BarreProgression (client) → animation interactive
```

Seules deux petites feuilles de l'arbre sont du JavaScript client. Tout le reste reste léger.

:::quiz
Quel composant doit obligatoirement être un Client Component ?
- [ ] Un composant qui affiche une liste d'articles lue depuis une base de données
- [ ] Un composant qui affiche un titre statique
- [x] Un composant qui utilise useState pour gérer l'ouverture d'un menu
- [ ] Un composant qui lit une variable d'environnement privée
> Dès qu'un composant utilise un état, un effet ou un gestionnaire d'événements, il doit s'exécuter dans le navigateur : c'est un composant client. Les trois autres cas fonctionnent très bien côté serveur.
:::

## Charger des données dans un composant serveur

Voici l'un des plus grands bénéfices : un composant serveur peut être `async` et récupérer ses données sans `useEffect` ni état de chargement.

```tsx
// src/app/roadmaps/[slug]/page.tsx
import { notFound } from 'next/navigation';
import ListeEtapes from '@/components/ListeEtapes';

type Etape = { id: number; titre: string };
type Roadmap = { slug: string; titre: string; etapes: Etape[] };

async function chargerRoadmap(slug: string): Promise<Roadmap | null> {
  const reponse = await fetch(`${process.env.API_URL}/roadmaps/${slug}`);
  if (!reponse.ok) return null;
  return reponse.json();
}

export default async function PageRoadmap({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const roadmap = await chargerRoadmap(slug);

  if (!roadmap) notFound();

  return (
    <main className="mx-auto max-w-2xl p-8">
      <h1 className="text-2xl font-bold">{roadmap.titre}</h1>
      <ListeEtapes etapes={roadmap.etapes} />
    </main>
  );
}
```

La variable `API_URL` n'est pas préfixée par `NEXT_PUBLIC_` : elle reste privée. Le navigateur ne voit jamais cette URL, ni la requête. C'est une excellente protection.

### Plusieurs requêtes en parallèle

Si tu as besoin de plusieurs données indépendantes, ne les attends pas l'une après l'autre : lance-les ensemble.

```tsx
const [roadmap, progression] = await Promise.all([
  chargerRoadmap(slug),
  chargerProgression(slug),
]);
```

Avec deux `await` successifs, la seconde requête attendrait la fin de la première : le temps de chargement s'additionne. Avec `Promise.all`, il se limite à la plus lente.

## Composer serveur et client

Les deux familles doivent cohabiter. Voici les règles de composition.

### Règle 1 : un composant serveur peut importer un composant client

C'est le cas le plus courant, comme `BoutonTermine` utilisé dans `ListeEtapes`.

```tsx
// src/components/ListeEtapes.tsx (serveur)
import BoutonTermine from './BoutonTermine';

type Etape = { id: number; titre: string };

export default function ListeEtapes({ etapes }: { etapes: Etape[] }) {
  return (
    <ul className="mt-4 space-y-2">
      {etapes.map((e) => (
        <li key={e.id} className="flex items-center justify-between">
          <span>{e.titre}</span>
          <BoutonTermine etapeId={e.id} />
        </li>
      ))}
    </ul>
  );
}
```

### Règle 2 : un composant client ne peut pas importer un composant serveur

Si tu importes un composant serveur dans un fichier `'use client'`, il devient automatiquement client (avec ses restrictions). Mais il existe une astuce : le passer en **`children`** (ou en prop). Le composant client reçoit alors un résultat déjà calculé côté serveur.

```tsx
// src/components/Panneau.tsx
'use client';

import { useState, type ReactNode } from 'react';

export default function Panneau({ titre, children }: { titre: string; children: ReactNode }) {
  const [ouvert, setOuvert] = useState(true);

  return (
    <section className="rounded border">
      <button onClick={() => setOuvert(!ouvert)} className="w-full p-3 text-left font-semibold">
        {titre} {ouvert ? '▲' : '▼'}
      </button>
      {ouvert && <div className="p-3">{children}</div>}
    </section>
  );
}
```

```tsx
// src/app/page.tsx (serveur)
import Panneau from '@/components/Panneau';
import DernieresRoadmaps from '@/components/DernieresRoadmaps'; // composant serveur async

export default function Accueil() {
  return (
    <Panneau titre="Dernières roadmaps">
      <DernieresRoadmaps />
    </Panneau>
  );
}
```

`Panneau` gère l'ouverture (état client), mais son contenu `DernieresRoadmaps` reste un composant serveur qui lit ses données : la frontière client ne l'a pas « avalé ».

### Règle 3 : les props transmises doivent être sérialisables

Quand un composant serveur passe des props à un composant client, elles voyagent sur le réseau. Tu peux donc transmettre des chaînes, nombres, booléens, tableaux, objets simples et dates. Tu ne peux **pas** transmettre une fonction ordinaire, une classe ou un objet complexe.

```tsx
// Incorrect : une fonction n'est pas sérialisable
<BoutonClient onClick={() => console.log('clic')} />

// Correct : des données simples
<BoutonClient etapeId={42} libelle="Terminer" />
```

:::quiz
Comment afficher un composant serveur à l'intérieur d'un composant client sans le transformer en composant client ?
- [ ] En l'important directement dans le fichier du composant client
- [x] En le passant en children (ou en prop) depuis un composant serveur parent
- [ ] En ajoutant use server dans le composant client
- [ ] C'est impossible dans Next.js
> En passant le composant serveur comme children, il est calculé côté serveur dans le parent, et le composant client se contente d'afficher le résultat.
:::

## Pièges : secrets et code uniquement serveur

Un fichier utilitaire peut accidentellement être importé côté client et embarquer des secrets. Pour te protéger, installe le petit paquet officiel `server-only` :

```bash
npm install server-only
```

```ts
// src/lib/donnees.ts
import 'server-only';

const cle = process.env.SECRET_API_KEY;

export async function chargerDonneesPrivees() {
  const reponse = await fetch('https://api.exemple.com/prive', {
    headers: { Authorization: `Bearer ${cle}` },
  });
  return reponse.json();
}
```

Si quelqu'un importe ce module depuis un composant client, le build échoue avec une erreur explicite, au lieu de livrer ta clé au navigateur. Le paquet jumeau `client-only` fait l'inverse pour du code qui dépend du navigateur.

### Les fournisseurs de contexte

Les contextes React (par exemple un thème sombre ou clair) ne fonctionnent que côté client. La bonne pratique : créer un composant `Providers` client, et l'utiliser dans le layout serveur.

```tsx
// src/components/Providers.tsx
'use client';

import { createContext, useState, type ReactNode } from 'react';

export const ThemeContext = createContext({ sombre: false, basculer: () => {} });

export default function Providers({ children }: { children: ReactNode }) {
  const [sombre, setSombre] = useState(false);
  return (
    <ThemeContext.Provider value={{ sombre, basculer: () => setSombre(!sombre) }}>
      {children}
    </ThemeContext.Provider>
  );
}
```

```tsx
// src/app/layout.tsx (serveur)
import Providers from '@/components/Providers';

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="fr">
      <body>
        <Providers>{children}</Providers>
      </body>
    </html>
  );
}
```

Grâce à la règle des `children`, les pages restent des composants serveur même si elles sont enveloppées par `Providers`.

## Atelier guidé : rendre DevRoad interactif sans l'alourdir

Compte une heure trente avec le projet `devroad-web`.

1. Crée un tableau `etapes` de quatre éléments (id, titre) dans une page `/roadmaps/[slug]`, et un composant serveur `ListeEtapes` qui l'affiche.
2. Crée un composant client `BoutonTermine` (`'use client'`) avec un état `fait` qui bascule au clic et change le texte du bouton.
3. Utilise `BoutonTermine` dans `ListeEtapes`. Vérifie que la page reste un composant serveur : elle n'a pas de directive.
4. Ajoute un compteur « Étapes terminées : X / 4 » dans un composant client `Progression`. Pour que les boutons et le compteur partagent l'état, remonte l'état dans un composant client parent `SuiviEtapes` qui reçoit les étapes en prop.
5. Charge les étapes depuis une fonction asynchrone `chargerEtapes()` dans la page serveur, avec un `await new Promise((r) => setTimeout(r, 500))` pour simuler un délai, et transmets-les à `SuiviEtapes`.
6. Crée `Panneau` (client) et utilise-le pour envelopper un composant serveur affichant la description de la roadmap, en le passant en `children`.
7. Crée `src/lib/donnees.ts` avec `import 'server-only'`, puis tente de l'importer dans un composant client : observe l'erreur de build, puis corrige.
8. Lance `npm run build` et compare la taille du JavaScript de la page avant et après avoir isolé le client dans des petites feuilles.

Pour t'auto-évaluer : pour chacun des composants suivants, dis s'il est serveur ou client et pourquoi : un en-tête de page, un formulaire de recherche avec saisie en direct, une carte de roadmap lue en base, un menu burger.

## Erreurs fréquentes

- **Mettre `'use client'` en haut de la page entière « pour être tranquille ».** Tu perds les bénéfices du serveur ; isole plutôt les feuilles interactives.
- **Utiliser `useState` sans la directive.** L'erreur indique qu'il faut un composant client.
- **Rendre un composant client `async`.** Ce n'est pas supporté : charge les données dans le parent serveur et passe-les en props.
- **Passer une fonction en prop d'un serveur vers un client.** Les props doivent être sérialisables.
- **Préfixer un secret par `NEXT_PUBLIC_`.** Il se retrouve dans le bundle du navigateur.
- **Accéder à `window` dans un composant serveur.** `window` n'existe pas sur le serveur ; garde ce code dans un composant client, dans un `useEffect`.
- **Importer un composant serveur dans un client.** Il devient client par contagion ; passe-le en `children`.

## Bonnes pratiques

- Serveur par défaut ; client pour les feuilles interactives uniquement.
- Charge les données le plus haut possible dans l'arbre serveur, puis descends-les par props.
- Utilise `Promise.all` pour paralléliser les requêtes indépendantes.
- Protège les modules sensibles avec `server-only`.
- Garde les composants clients petits et ciblés : un bouton, un formulaire, un menu.
- Regroupe les fournisseurs de contexte dans un seul composant `Providers` client.

## À retenir

- Par défaut, les composants de l'App Router sont des **Server Components** : ils s'exécutent côté serveur, peuvent être `async` et n'envoient pas leur JavaScript.
- `'use client'` crée une **frontière** : le fichier et ses imports rejoignent le bundle du navigateur.
- État, effets, événements et API navigateur exigent un composant client.
- Un composant serveur peut importer un client ; l'inverse passe par `children` ou les props.
- Les props entre serveur et client doivent être **sérialisables**.
- Les secrets restent côté serveur ; `server-only` empêche les imports accidentels.
- Place la directive le plus bas possible dans l'arbre pour réduire le JavaScript envoyé.
