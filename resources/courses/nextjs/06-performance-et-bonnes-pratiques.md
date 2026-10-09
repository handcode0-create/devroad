---
title: Performance et bonnes pratiques
minutes: 140
level: intermediate
---

## Ce que tu vas apprendre

Une application qui fonctionne n'est pas forcément une application agréable. Sur un téléphone d'entrée de gamme, avec une connexion 3G instable, chaque kilo-octet compte. Next.js fournit beaucoup d'outils pour la performance, mais il faut savoir s'en servir. Ce chapitre rassemble les optimisations qui comptent vraiment, ainsi que les bonnes pratiques de sécurité et de qualité à adopter avant un déploiement.

À la fin du chapitre, tu seras capable de :

- mesurer la performance avec Lighthouse et les **Core Web Vitals** ;
- optimiser les images avec `next/image` et les polices avec `next/font` ;
- utiliser **Suspense** et le streaming pour afficher la page progressivement ;
- charger du code à la demande avec `next/dynamic` ;
- configurer le SEO : métadonnées, Open Graph, `sitemap.ts` et `robots.ts` ;
- ajouter un fichier `middleware.ts` pour protéger des routes ;
- préparer et réussir un déploiement (variables, build, vérifications).

Prérequis : les chapitres précédents. Prévois environ deux heures vingt.

## Mesurer avant d'optimiser

Optimiser sans mesurer, c'est deviner. L'outil de base est **Lighthouse**, intégré à Chrome (onglet « Lighthouse » des outils de développement). Fais toujours le test sur une version de production, jamais sur `npm run dev`, qui est volontairement plus lent :

```bash
npm run build
npm run start
```

Lighthouse attribue des notes sur la performance, l'accessibilité, les bonnes pratiques et le SEO. Les chiffres qui comptent, les **Core Web Vitals**, sont au nombre de trois :

| Indicateur | Ce qu'il mesure | Objectif |
| --- | --- | --- |
| LCP (Largest Contentful Paint) | Temps d'affichage du plus gros élément visible | Moins de 2,5 s |
| INP (Interaction to Next Paint) | Réactivité aux clics et saisies | Moins de 200 ms |
| CLS (Cumulative Layout Shift) | Stabilité visuelle (éléments qui sautent) | Moins de 0,1 |

Active la simulation d'un mobile avec réseau lent dans les outils de développement. Si ton site est agréable ainsi, il l'est partout.

> **Astuce** : mesure avant, optimise un point à la fois, puis mesure après. Note les chiffres. C'est la seule façon de savoir si une modification a servi à quelque chose.

## Les images avec next/image

Les images sont généralement la première cause de lenteur. Le composant `Image` de Next.js fait beaucoup de travail à ta place : conversion en formats modernes (WebP, AVIF), redimensionnement selon l'écran, chargement différé et réservation de l'espace pour éviter les sauts de mise en page.

```tsx
import Image from 'next/image';

export default function Banniere() {
  return (
    <Image
      src="/images/banniere.jpg"
      alt="Illustration d’une carte de parcours d’apprentissage"
      width={1200}
      height={600}
      priority
      className="h-auto w-full rounded-lg"
    />
  );
}
```

Les points essentiels :

- `width` et `height` sont obligatoires pour une image locale importée par chemin : ils permettent de réserver la place et d'éviter le CLS ;
- `alt` est obligatoire, et doit décrire l'image (ou être vide pour une image purement décorative) ;
- `priority` est à réserver à l'image principale visible dès l'arrivée (le LCP) ; toutes les autres se chargent paresseusement par défaut ;
- `sizes` indique la largeur réellement occupée selon l'écran, afin de servir une version plus petite aux mobiles.

Pour une image qui remplit un conteneur de taille variable, utilise `fill` avec un parent positionné :

```tsx
<div className="relative aspect-video w-full">
  <Image
    src="/images/react.png"
    alt="Logo React"
    fill
    sizes="(max-width: 768px) 100vw, 50vw"
    className="object-cover"
  />
</div>
```

Pour les images hébergées ailleurs (un CDN, Supabase Storage), déclare le domaine autorisé dans `next.config.ts`, sans quoi Next.js refuse de les optimiser :

```ts
import type { NextConfig } from 'next';

const config: NextConfig = {
  images: {
    remotePatterns: [
      { protocol: 'https', hostname: 'monprojet.supabase.co' },
    ],
  },
};

export default config;
```

## Les polices avec next/font

Les polices chargées depuis un service externe provoquent un délai et des sauts d'affichage. Avec `next/font`, la police est téléchargée au build et servie depuis ton propre domaine, sans requête supplémentaire à l'exécution :

```tsx
// src/app/layout.tsx
import { Inter } from 'next/font/google';

const inter = Inter({
  subsets: ['latin'],
  display: 'swap',
});

export default function RootLayout({ children }: { children: React.ReactNode }) {
  return (
    <html lang="fr" className={inter.className}>
      <body>{children}</body>
    </html>
  );
}
```

Choisis le moins de graisses possible (chaque graisse est un fichier à télécharger) et limite-toi à une ou deux familles.

:::quiz
Quel est l'intérêt principal de passer width et height à un composant Image de Next.js ?
- [ ] Cela change la qualité de compression JPEG
- [x] Cela réserve l'espace de l'image et évite que la page saute au chargement
- [ ] Cela rend l'image cliquable
- [ ] Cela désactive le chargement paresseux
> Réserver l'espace évite le décalage de mise en page (CLS), l'un des trois Core Web Vitals.
:::

## Streaming et Suspense

Imagine une page de tableau de bord avec trois blocs : un résumé rapide, une liste lente (une requête de deux secondes) et des statistiques. Si tu attends tout avant d'envoyer la page, l'utilisateur fixe un écran blanc. Avec **Suspense**, Next.js envoie d'abord ce qui est prêt et diffuse le reste en **streaming** dès qu'il arrive.

```tsx
// src/app/tableau-de-bord/page.tsx
import { Suspense } from 'react';
import Resume from '@/components/Resume';
import ListeLente from '@/components/ListeLente';

export default function TableauDeBord() {
  return (
    <main className="space-y-6 p-8">
      <h1 className="text-2xl font-bold">Tableau de bord</h1>
      <Resume />
      <Suspense fallback={<p className="animate-pulse">Chargement de l’activité…</p>}>
        <ListeLente />
      </Suspense>
    </main>
  );
}
```

```tsx
// src/components/ListeLente.tsx — composant serveur async
export default async function ListeLente() {
  const reponse = await fetch(`${process.env.API_URL}/activite`, { cache: 'no-store' });
  const activites: { id: number; texte: string }[] = await reponse.json();

  return (
    <ul>
      {activites.map((a) => (
        <li key={a.id}>{a.texte}</li>
      ))}
    </ul>
  );
}
```

Le titre et le résumé s'affichent immédiatement ; la liste arrive quand elle est prête, remplaçant le texte de chargement. Le fichier `loading.tsx` du chapitre 3 n'est en réalité qu'un `Suspense` automatique autour de la page : utilise `Suspense` à la main pour un contrôle plus fin, bloc par bloc.

## Charger du code à la demande

Certains composants sont lourds (un éditeur de texte riche, un graphique, une carte) et pas nécessaires dès l'arrivée. `next/dynamic` les télécharge seulement quand ils sont affichés :

```tsx
'use client';

import dynamic from 'next/dynamic';

const Graphique = dynamic(() => import('@/components/Graphique'), {
  loading: () => <p>Chargement du graphique…</p>,
  ssr: false,
});

export default function Statistiques() {
  return <Graphique />;
}
```

L'option `ssr: false` (réservée aux composants clients) évite d'essayer de rendre sur le serveur un composant qui dépend du navigateur. Applique cette technique avec parcimonie, sur les gros composants peu fréquents.

Vérifie aussi le poids de tes dépendances : importer une bibliothèque entière pour utiliser une fonction est un piège classique. Préfère les imports ciblés et surveille la colonne « First Load JS » à la fin de `npm run build`.

## Le SEO : métadonnées, sitemap et robots

Un bon référencement commence par des métadonnées soignées (voir le chapitre 3), complétées par les balises de partage pour les réseaux sociaux (Open Graph) :

```tsx
import type { Metadata } from 'next';

export const metadata: Metadata = {
  title: { default: 'DevRoad Web', template: '%s | DevRoad Web' },
  description: 'Roadmaps et fiches mémo pour développeurs francophones.',
  openGraph: {
    title: 'DevRoad Web',
    description: 'Apprends à ton rythme avec des parcours structurés.',
    locale: 'fr_FR',
    type: 'website',
  },
};
```

Next.js génère les fichiers `sitemap.xml` et `robots.txt` à partir de simples fichiers de code :

```ts
// src/app/sitemap.ts
import type { MetadataRoute } from 'next';

export default function sitemap(): MetadataRoute.Sitemap {
  const base = 'https://devroad.exemple.com';
  return [
    { url: base, lastModified: new Date() },
    { url: `${base}/roadmaps`, lastModified: new Date() },
  ];
}
```

```ts
// src/app/robots.ts
import type { MetadataRoute } from 'next';

export default function robots(): MetadataRoute.Robots {
  return {
    rules: { userAgent: '*', allow: '/', disallow: ['/api/', '/tableau-de-bord'] },
    sitemap: 'https://devroad.exemple.com/sitemap.xml',
  };
}
```

## Le middleware : protéger des routes

Le **middleware** est un code qui s'exécute avant qu'une requête n'atteigne ta page. Il est idéal pour rediriger un utilisateur non connecté. Il se place dans `src/middleware.ts` :

```ts
import { NextResponse, type NextRequest } from 'next/server';

export function middleware(requete: NextRequest) {
  const session = requete.cookies.get('session')?.value;

  if (!session) {
    const url = new URL('/connexion', requete.url);
    url.searchParams.set('retour', requete.nextUrl.pathname);
    return NextResponse.redirect(url);
  }

  return NextResponse.next();
}

export const config = {
  matcher: ['/tableau-de-bord/:path*', '/fiches/nouvelle'],
};
```

Le `matcher` limite le middleware aux routes concernées, pour ne pas ralentir tout le reste.

> **Attention** : le middleware est une première barrière, pas la seule. Vérifie **aussi** l'authentification dans les Server Actions et les Route Handlers. Une redirection ne protège pas une action appelée directement.

:::quiz
À quoi sert le composant Suspense avec un composant serveur lent ?
- [ ] À mettre la requête en cache définitivement
- [x] À afficher d'abord le reste de la page et diffuser ce bloc dès qu'il est prêt
- [ ] À transformer le composant en composant client
- [ ] À remplacer la validation des données
> Suspense permet le streaming : les parties rapides sont envoyées tout de suite avec un contenu de remplacement, et la partie lente arrive ensuite.
:::

## Préparer le déploiement

Avant de mettre en ligne, passe cette liste :

1. `npm run lint` et `npm run build` passent sans erreur.
2. Les variables d'environnement sont configurées sur la plateforme de déploiement (Vercel ou autre). Rien de secret n'est préfixé par `NEXT_PUBLIC_`.
3. Les métadonnées, le `sitemap` et le `robots` pointent vers le vrai domaine.
4. Les pages d'erreur (`error`, `not-found`) existent et sont testées.
5. Lighthouse en production affiche de bons scores sur mobile.
6. Les en-têtes de sécurité sont configurés si nécessaire.

Les en-têtes de sécurité se déclarent dans `next.config.ts` :

```ts
const config: NextConfig = {
  async headers() {
    return [
      {
        source: '/(.*)',
        headers: [
          { key: 'X-Content-Type-Options', value: 'nosniff' },
          { key: 'X-Frame-Options', value: 'DENY' },
          { key: 'Referrer-Policy', value: 'strict-origin-when-cross-origin' },
        ],
      },
    ];
  },
};
```

Vercel est la plateforme la plus simple : tu relies ton dépôt Git, tu saisis les variables d'environnement, et chaque `push` déclenche un déploiement avec une adresse de prévisualisation. Tu peux aussi héberger Next.js sur un serveur Node.js classique avec `npm run build` puis `npm run start`, ou dans un conteneur Docker.

## Atelier guidé : auditer et optimiser DevRoad Web

Compte une heure trente.

1. Fais un `npm run build` puis `npm run start`. Lance Lighthouse en mode mobile sur la page d'accueil et note les scores et le LCP.
2. Ajoute une bannière avec `next/image` (une image de ton choix dans `public/images`), avec `priority`, `width`, `height` et un `alt` pertinent. Mesure à nouveau.
3. Configure une police avec `next/font/google` dans le layout racine. Vérifie dans l'onglet « Réseau » qu'aucune requête ne part vers Google Fonts à l'exécution.
4. Crée une page `/tableau-de-bord` avec un bloc rapide et un bloc lent (simule deux secondes avec `setTimeout`). Enveloppe le bloc lent dans `Suspense` avec un `fallback`.
5. Charge un composant lourd (même fictif) avec `next/dynamic`.
6. Crée `sitemap.ts` et `robots.ts`, puis visite `/sitemap.xml` et `/robots.txt`.
7. Crée `middleware.ts` qui redirige `/tableau-de-bord` vers `/connexion` en l'absence de cookie `session`. Teste avec et sans cookie.
8. Ajoute les en-têtes de sécurité et vérifie-les dans l'onglet « Réseau ».
9. Relance Lighthouse, compare avec tes notes initiales et écris trois lignes de conclusion.

Pour t'auto-évaluer : peux-tu expliquer pourquoi on ne mesure jamais la performance avec `npm run dev`, et citer les trois Core Web Vitals avec leur objectif ?

## Erreurs fréquentes

- **Mesurer en mode développement.** Les chiffres sont trompeurs ; utilise toujours le build de production.
- **Mettre `priority` sur toutes les images.** Cela annule l'avantage du chargement différé.
- **Oublier `alt`.** Mauvais pour l'accessibilité et le SEO.
- **Charger de grosses bibliothèques côté client pour un détail.** Le JavaScript explose ; cherche une alternative légère ou charge à la demande.
- **Faire dépendre toute la protection d'un middleware.** Vérifie les droits aussi dans les actions et les API.
- **Oublier de déclarer un domaine d'images distant.** Next.js lève une erreur sur l'image externe.
- **Pas de `loading.tsx` ni de `Suspense` sur les pages lentes.** L'utilisateur croit que le site est figé.
- **Déployer sans variables d'environnement.** Le build passe en local mais l'application échoue en production.

## Bonnes pratiques

- Mesure, optimise un point, mesure de nouveau.
- Envoie le moins de JavaScript possible : composants serveur par défaut, clients ciblés.
- Fournis toujours des dimensions aux images et limite le nombre de polices.
- Streame les blocs lents avec `Suspense` plutôt que de bloquer toute la page.
- Pense aux utilisateurs mobiles et aux forfaits data limités : compresse, diffère, allège.
- Soigne l'accessibilité : balises sémantiques, `alt`, contrastes, navigation au clavier.
- Automatise lint et build dans ton dépôt avant chaque déploiement.

## À retenir

- Mesure avec **Lighthouse** sur un build de production ; les **Core Web Vitals** sont LCP, INP et CLS.
- `next/image` optimise formats, tailles et chargement ; `next/font` sert les polices sans requête externe.
- **Suspense** et le streaming affichent la page progressivement ; `next/dynamic` charge le code lourd à la demande.
- `metadata`, `sitemap.ts` et `robots.ts` structurent ton référencement.
- Le **middleware** filtre les requêtes avant les pages, mais ne remplace pas les contrôles dans les actions et les API.
- Un déploiement réussi commence par un lint, un build et des variables d'environnement bien configurés.
