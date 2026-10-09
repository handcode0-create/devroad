---
title: App Router et routing
minutes: 130
level: beginner
---

## Ce que tu vas apprendre

Le routage est le cœur de Next.js : c'est lui qui fait correspondre une adresse tapée dans le navigateur à une page de ton application. Dans ce chapitre, tu maîtrises le système de routes par dossiers, les layouts imbriqués, les routes dynamiques, la navigation entre pages et la gestion des états de chargement et d'erreur.

À la fin du chapitre, tu seras capable de :

- créer des routes statiques et imbriquées avec des dossiers et `page.tsx` ;
- créer des routes dynamiques (`[slug]`) et lire leurs paramètres ;
- partager une mise en page avec des `layout.tsx` imbriqués et des groupes de routes ;
- naviguer avec le composant `Link` et le hook `useRouter` ;
- gérer le chargement (`loading.tsx`), les erreurs (`error.tsx`) et les pages 404 (`not-found.tsx`) ;
- définir des métadonnées de page, statiques et dynamiques.

Prérequis : le projet « devroad-web » du chapitre précédent, et les bases de React (props, composants). Prévois environ deux heures dix.

## Le principe : les dossiers sont les routes

Dans `src/app/`, chaque dossier représente un segment d'URL. Un dossier n'est accessible publiquement que s'il contient un fichier **`page.tsx`**.

```text
src/app/
├── page.tsx                    → /
├── roadmaps/
│   ├── page.tsx                → /roadmaps
│   └── [slug]/
│       └── page.tsx            → /roadmaps/react
└── fiches/
    ├── page.tsx                → /fiches
    └── [id]/
        └── page.tsx            → /fiches/42
```

Un dossier sans `page.tsx` ne crée pas de route : on peut donc y ranger librement des composants liés à une section. Pour éviter toute ambiguïté, tu peux aussi préfixer un dossier par un underscore (`_composants`), ce qui l'exclut du routage.

Chaque `page.tsx` exporte **par défaut** un composant. Le nom de la fonction n'a pas d'importance technique, mais choisis un nom explicite (`PageRoadmaps`) : il apparaîtra dans les messages d'erreur.

## Les routes dynamiques

Il serait absurde de créer un dossier par roadmap. Les crochets créent un **segment dynamique** : `[slug]` capture n'importe quelle valeur à cet endroit de l'URL. Le segment capturé est transmis à la page dans la prop `params`.

```tsx
// src/app/roadmaps/[slug]/page.tsx
type Props = {
  params: Promise<{ slug: string }>;
};

export default async function PageRoadmap({ params }: Props) {
  const { slug } = await params;

  return (
    <main className="mx-auto max-w-2xl p-8">
      <h1 className="text-2xl font-bold">Roadmap : {slug}</h1>
    </main>
  );
}
```

> **Attention** : depuis Next.js 15, `params` est une **promesse** (`Promise`). Tu dois l'attendre avec `await` (dans un composant `async`) avant de lire ses valeurs. Dans les versions antérieures, c'était un objet simple : si tu suis un vieux tutoriel, c'est la cause de nombreuses erreurs.

Avec cette seule page, les adresses `/roadmaps/react`, `/roadmaps/laravel` et `/roadmaps/nimporte-quoi` fonctionnent toutes. Voici les variantes de segments dynamiques :

| Dossier | Capture | Exemple d'URL |
| --- | --- | --- |
| `[slug]` | Un seul segment | `/roadmaps/react` |
| `[...chemin]` | Un ou plusieurs segments | `/docs/a/b/c` |
| `[[...chemin]]` | Zéro, un ou plusieurs segments | `/docs` ou `/docs/a/b` |

### Les paramètres de recherche

Les paramètres après le point d'interrogation (`/roadmaps?niveau=debutant`) arrivent dans la prop `searchParams`, également asynchrone :

```tsx
// src/app/roadmaps/page.tsx
type Props = {
  searchParams: Promise<{ niveau?: string }>;
};

export default async function PageRoadmaps({ searchParams }: Props) {
  const { niveau } = await searchParams;

  return <p>Filtre actuel : {niveau ?? 'aucun'}</p>;
}
```

### Générer des pages statiques pour les routes dynamiques

Par défaut, une route dynamique est rendue à la demande. Si tu connais à l'avance les valeurs possibles, tu peux demander à Next.js de fabriquer ces pages au build, avec `generateStaticParams` :

```tsx
const slugs = ['react', 'laravel', 'nodejs'];

export function generateStaticParams() {
  return slugs.map((slug) => ({ slug }));
}
```

Au `npm run build`, trois pages statiques sont produites : elles s'afficheront instantanément.

:::quiz
Dans Next.js 15, comment lit-on le paramètre slug d'une route /roadmaps/[slug] ?
- [ ] Avec props.params.slug directement, sans attendre
- [x] En attendant la promesse : const { slug } = await params
- [ ] Avec document.location
- [ ] Avec require('slug')
> Depuis Next.js 15, params est une promesse. Il faut l'attendre avec await dans un composant async avant d'accéder à slug.
:::

## Les layouts imbriqués

Un **layout** est une mise en page qui entoure les pages d'un segment et de ses sous-segments. Le layout racine (`app/layout.tsx`) enveloppe tout. Tu peux ajouter un layout à n'importe quel dossier : il enveloppe alors uniquement cette section.

```tsx
// src/app/roadmaps/layout.tsx
import type { ReactNode } from 'react';

export default function LayoutRoadmaps({ children }: { children: ReactNode }) {
  return (
    <div className="mx-auto max-w-4xl p-6">
      <nav className="mb-6 border-b pb-3 text-sm text-gray-500">
        Roadmaps &gt; Parcours d’apprentissage
      </nav>
      {children}
    </div>
  );
}
```

Le point crucial : les layouts **ne se re-rendent pas** quand tu navigues entre deux pages du même segment. Un menu latéral, un champ de recherche ou un lecteur vidéo placé dans un layout conserve son état. C'est un avantage énorme par rapport à un site où chaque lien recharge tout.

Les layouts s'emboîtent comme des poupées russes : pour `/roadmaps/react`, Next.js affiche `RootLayout` > `LayoutRoadmaps` > `PageRoadmap`.

### Les groupes de routes

Parfois, tu veux des layouts différents pour des pages dont l'URL ne doit pas changer. Les parenthèses créent un **groupe de routes** : le nom du dossier n'apparaît pas dans l'URL.

```text
src/app/
├── (vitrine)/
│   ├── layout.tsx        ← en-tête marketing
│   ├── page.tsx          → /
│   └── tarifs/page.tsx   → /tarifs
└── (application)/
    ├── layout.tsx        ← menu latéral connecté
    └── tableau-de-bord/page.tsx   → /tableau-de-bord
```

Les pages du groupe `(vitrine)` et celles du groupe `(application)` partagent la même racine d'URL mais ont des mises en page distinctes. Attention : deux groupes ne peuvent pas définir la même URL.

## Naviguer : Link et useRouter

### Le composant Link

Pour passer d'une page à l'autre, n'utilise **pas** une balise `<a>` brute (elle recharge toute la page). Utilise le composant `Link` de Next.js :

```tsx
import Link from 'next/link';

export default function Menu() {
  return (
    <nav className="flex gap-4">
      <Link href="/">Accueil</Link>
      <Link href="/roadmaps">Roadmaps</Link>
      <Link href="/roadmaps/react">React</Link>
    </nav>
  );
}
```

`Link` apporte deux bénéfices. D'abord, la navigation se fait **côté client** : seul le nécessaire est rechargé, sans écran blanc. Ensuite, il **précharge** automatiquement les pages dont le lien est visible à l'écran, ce qui rend la navigation presque instantanée.

### Mettre en évidence le lien actif

Pour savoir quelle page est affichée, utilise le hook `usePathname`. Comme les hooks ne marchent que dans les composants client, on ajoute `'use client'` en haut du fichier :

```tsx
'use client';

import Link from 'next/link';
import { usePathname } from 'next/navigation';

export default function LienMenu({ href, children }: { href: string; children: React.ReactNode }) {
  const chemin = usePathname();
  const actif = chemin === href;

  return (
    <Link
      href={href}
      className={actif ? 'font-bold text-blue-600' : 'text-gray-600'}
    >
      {children}
    </Link>
  );
}
```

### Naviguer par le code

Pour rediriger après une action (par exemple après une connexion réussie), utilise `useRouter` dans un composant client :

```tsx
'use client';

import { useRouter } from 'next/navigation';

export default function BoutonRetour() {
  const router = useRouter();

  return (
    <div className="flex gap-2">
      <button onClick={() => router.back()}>Retour</button>
      <button onClick={() => router.push('/roadmaps')}>Voir les roadmaps</button>
    </div>
  );
}
```

Côté serveur, la fonction `redirect` de `next/navigation` fait la même chose :

```tsx
import { redirect } from 'next/navigation';

export default async function PageAncienne() {
  redirect('/roadmaps');
}
```

> **Astuce** : préfère toujours `Link` pour les liens visibles par l'utilisateur. Il est accessible, indexable par les moteurs de recherche et préchargé. Réserve `useRouter` aux navigations déclenchées par une action.

## Chargement, erreurs et pages introuvables

Les fichiers spéciaux de l'App Router gèrent trois situations que tu devrais toujours prévoir.

### loading.tsx

Quand une page attend des données, Next.js affiche automatiquement le contenu de `loading.tsx` du même dossier, pendant que le reste se charge.

```tsx
// src/app/roadmaps/loading.tsx
export default function Chargement() {
  return <p className="animate-pulse p-8">Chargement des roadmaps…</p>;
}
```

### error.tsx

Si une erreur survient pendant le rendu, `error.tsx` s'affiche à la place de la page, sans faire planter tout le site. Ce fichier doit être un composant client :

```tsx
// src/app/roadmaps/error.tsx
'use client';

export default function ErreurRoadmaps({
  error,
  reset,
}: {
  error: Error;
  reset: () => void;
}) {
  return (
    <div className="p-8">
      <h2 className="font-bold">Oups, quelque chose s’est mal passé.</h2>
      <p className="text-sm text-gray-500">{error.message}</p>
      <button onClick={() => reset()} className="mt-4 rounded border px-3 py-1">
        Réessayer
      </button>
    </div>
  );
}
```

La fonction `reset` tente de rendre à nouveau le segment : pratique après une erreur réseau passagère.

### not-found.tsx et notFound()

Pour une roadmap inexistante, on veut une vraie erreur 404, pas une page vide. La fonction `notFound()` déclenche l'affichage du fichier `not-found.tsx` le plus proche :

```tsx
// src/app/roadmaps/[slug]/page.tsx
import { notFound } from 'next/navigation';

const roadmaps: Record<string, string> = {
  react: 'Roadmap React',
  laravel: 'Roadmap Laravel',
};

export default async function PageRoadmap({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;
  const titre = roadmaps[slug];

  if (!titre) {
    notFound();
  }

  return <h1 className="p-8 text-2xl font-bold">{titre}</h1>;
}
```

```tsx
// src/app/roadmaps/[slug]/not-found.tsx
import Link from 'next/link';

export default function RoadmapIntrouvable() {
  return (
    <div className="p-8">
      <h2 className="text-xl font-bold">Roadmap introuvable</h2>
      <Link href="/roadmaps" className="text-blue-600 underline">
        Retour à la liste
      </Link>
    </div>
  );
}
```

:::quiz
Quel fichier spécial s'affiche automatiquement pendant qu'une page attend ses données ?
- [ ] error.tsx
- [ ] template.tsx
- [x] loading.tsx
- [ ] not-found.tsx
> loading.tsx fournit l'état de chargement du segment. error.tsx gère les erreurs et not-found.tsx les ressources introuvables.
:::

## Les métadonnées de page

Chaque page devrait avoir son propre titre et sa description, essentiels pour le référencement et pour l'onglet du navigateur. Pour une page statique, exporte un objet `metadata` :

```tsx
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: 'Roadmaps | DevRoad Web',
  description: 'Tous les parcours d’apprentissage pour développeurs.',
};
```

Pour une page dynamique, exporte une fonction `generateMetadata` qui reçoit les mêmes paramètres que la page :

```tsx
export async function generateMetadata({
  params,
}: {
  params: Promise<{ slug: string }>;
}): Promise<Metadata> {
  const { slug } = await params;
  return {
    title: `Roadmap ${slug} | DevRoad Web`,
  };
}
```

Les métadonnées s'emboîtent aussi avec les layouts. Tu peux définir un modèle de titre dans le layout racine : `title: { template: '%s | DevRoad Web', default: 'DevRoad Web' }`. Chaque page n'a plus qu'à fournir son titre court.

## Atelier guidé : la navigation de DevRoad Web

Compte une heure trente en repartant du projet `devroad-web`.

1. Crée `src/app/roadmaps/[slug]/page.tsx` qui affiche le slug dans un `h1`, en attendant correctement `params`.
2. Dans la page `/roadmaps`, transforme chaque carte en `Link` vers `/roadmaps/<slug>`. Ajoute un champ `slug` aux données.
3. Ajoute `generateStaticParams` pour fabriquer au build les pages de tes trois roadmaps. Vérifie avec `npm run build` qu'elles sont marquées statiques.
4. Crée `src/app/roadmaps/layout.tsx` avec un fil d'Ariane et vérifie qu'il entoure toutes les pages du segment.
5. Crée un composant client `LienMenu` qui utilise `usePathname` pour colorer le lien actif, et place un menu dans le layout racine.
6. Crée `loading.tsx` dans `roadmaps/`. Pour le voir, ajoute temporairement `await new Promise((r) => setTimeout(r, 2000))` dans la page.
7. Crée `not-found.tsx` et appelle `notFound()` quand le slug ne correspond à aucune roadmap. Teste `/roadmaps/inconnue`.
8. Crée `error.tsx`, lance volontairement `throw new Error('test')` dans la page, et vérifie que le bouton « Réessayer » apparaît. Retire ensuite l'erreur.
9. Ajoute des métadonnées dynamiques avec `generateMetadata`.

Pour t'auto-évaluer : sans regarder le chapitre, dessine l'arborescence de dossiers pour obtenir les URL `/`, `/roadmaps`, `/roadmaps/react` et `/fiches/42`.

## Erreurs fréquentes

- **Utiliser `params.slug` sans `await` dans Next.js 15.** Tu obtiens `undefined` ou un avertissement ; attends la promesse.
- **Oublier `'use client'` avec `useRouter` ou `usePathname`.** Ces hooks n'existent que côté client.
- **Importer `useRouter` depuis `next/router`.** C'est l'ancien routeur. Dans l'App Router, importe depuis `next/navigation`.
- **Utiliser `<a href>` pour la navigation interne.** La page se recharge entièrement.
- **Placer `error.tsx` sans `'use client'`.** Ce fichier doit être un composant client.
- **Créer un dossier sans `page.tsx` et s'étonner d'une 404.** Sans ce fichier, la route n'existe pas.
- **Deux groupes de routes avec la même URL.** Next.js signale un conflit.

## Bonnes pratiques

- Une page = un fichier court ; déplace la logique et les gros morceaux d'interface dans des composants.
- Prévois systématiquement `loading.tsx`, `error.tsx` et `not-found.tsx` pour les sections qui chargent des données.
- Mets dans les layouts uniquement ce qui doit survivre à la navigation (menu, lecteur, barre latérale).
- Définis des métadonnées uniques et descriptives pour chaque page publique.
- Utilise `generateStaticParams` pour les contenus connus à l'avance : c'est gratuit en performance.
- Garde des URL lisibles, en minuscules, sans accents, avec des tirets : `/roadmaps/node-js`.

## À retenir

- Un dossier dans `app/` est un segment d'URL ; `page.tsx` rend la route accessible.
- `[slug]` crée une route dynamique ; dans Next.js 15, `params` et `searchParams` sont des promesses à attendre.
- Les `layout.tsx` s'imbriquent et conservent leur état pendant la navigation ; les groupes `(nom)` organisent sans changer l'URL.
- `Link` navigue côté client avec préchargement ; `useRouter` et `usePathname` viennent de `next/navigation` et exigent `'use client'`.
- `loading.tsx`, `error.tsx` et `not-found.tsx` gèrent attente, erreur et 404 de façon déclarative.
- `metadata` et `generateMetadata` définissent titre et description de chaque page.
