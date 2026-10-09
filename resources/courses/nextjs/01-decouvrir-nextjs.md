---
title: Découvrir Next.js
minutes: 100
level: beginner
---

## Ce que tu vas apprendre

Tu connais React : tu sais écrire des composants, gérer un état, afficher des listes. Mais React seul ne sait ni découper ton application en pages, ni charger des données côté serveur, ni optimiser le référencement. **Next.js** est le framework qui comble ces manques. Il est utilisé en production par de très nombreuses entreprises, et c'est aussi la base de nombreux produits développés pour le marché ouest-africain.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est Next.js et ce qu'il ajoute à React ;
- distinguer les grands modes de rendu : client, serveur, statique ;
- décrire le principe du routage par dossiers de l'**App Router** ;
- comparer Next.js avec Laravel + Inertia (la stack de DevRoad) et savoir quand choisir l'un ou l'autre ;
- reconnaître la structure d'un projet Next.js et le rôle de ses fichiers spéciaux.

Prérequis : avoir suivi le chapitre « Découvrir React » (composants, JSX, état) et connaître les bases de JavaScript moderne (modules `import` / `export`, `async` / `await`). Prévois environ une heure quarante. Ce chapitre est surtout conceptuel : tu n'as rien à installer, l'installation viendra au chapitre suivant.

## Pourquoi un framework au-dessus de React ?

React est une **bibliothèque** : il s'occupe de l'interface, rien d'autre. Dès que tu construis une vraie application, des questions apparaissent que React ne règle pas :

- Comment passer d'une page à une autre sans recharger tout le site ?
- Comment faire pour que Google lise le contenu de mes pages, et pas une page blanche en attente de JavaScript ?
- Où charger les données : dans le navigateur, ou plus près de la base de données ?
- Comment découper le code pour que le navigateur ne télécharge que ce dont il a besoin ?
- Comment optimiser les images, les polices, les scripts ?

Avec React pur, tu assembles toi-même la réponse : un routeur, un outil de compilation, une solution de rendu serveur, une stratégie de cache. Chaque projet devient un puzzle différent. Un **framework** prend ces décisions pour toi et te donne une structure commune.

Next.js, développé par la société Vercel, apporte notamment :

| Besoin | Ce que Next.js fournit |
| --- | --- |
| Navigation | Routage automatique basé sur les dossiers |
| Référencement (SEO) | Rendu côté serveur, balises `title` et `meta` gérées par code |
| Données | Chargement côté serveur avec `async` / `await` directement dans les composants |
| Back-end léger | Route Handlers pour créer des API dans le même projet |
| Performance | Découpage automatique du code, optimisation des images et des polices |
| Déploiement | Mise en ligne en quelques minutes, notamment sur Vercel |

> **À retenir** : Next.js n'est pas un concurrent de React, c'est un framework **construit sur React**. Tout ce que tu as appris (composants, props, état) reste valable.

:::quiz
Quelle est la relation entre React et Next.js ?
- [ ] Next.js remplace React par une technologie plus récente
- [x] Next.js est un framework construit sur React qui ajoute routage, rendu serveur et optimisations
- [ ] Next.js est un langage de programmation différent de JavaScript
- [ ] React est une extension optionnelle de Next.js pour le style
> Next.js utilise React pour écrire l'interface, et y ajoute tout ce qui manque à une application complète : routage, rendu côté serveur, optimisation, API.
:::

## Les modes de rendu : où la page est-elle fabriquée ?

Comprendre le **rendu** (*rendering*) est la clé pour ne pas se perdre dans Next.js. Le rendu, c'est le moment où ton code React est transformé en HTML. Trois grandes options existent.

### Le rendu côté client (CSR)

C'est le fonctionnement d'une application React classique créée avec Vite. Le serveur envoie une page HTML presque vide et un gros fichier JavaScript. Le navigateur exécute ce JavaScript, qui construit l'interface.

```html
<!-- Ce que reçoit le navigateur avec du CSR pur -->
<body>
  <div id="root"></div>
  <script src="/assets/app.js"></script>
</body>
```

Avantage : une fois chargée, l'application est très réactive. Inconvénients : écran blanc au démarrage sur une connexion lente, et contenu invisible pour les robots qui n'exécutent pas le JavaScript. Sur un réseau mobile limité, c'est un vrai problème.

### Le rendu côté serveur (SSR)

À chaque requête, le serveur exécute tes composants, produit le HTML complet et l'envoie. L'utilisateur voit le contenu immédiatement, même avant que le JavaScript soit chargé.

```html
<!-- Ce que reçoit le navigateur avec du SSR -->
<body>
  <h1>Roadmap React</h1>
  <p>12 étapes pour maîtriser React</p>
</body>
```

C'est idéal pour les pages dont le contenu change souvent ou dépend de l'utilisateur (un tableau de bord, par exemple).

### La génération statique (SSG)

Le HTML est fabriqué **une seule fois**, au moment de la compilation (*build*), puis servi tel quel à tout le monde. C'est le plus rapide possible : il n'y a aucun calcul à chaque visite. C'est parfait pour une page « À propos », un blog, une page de tarifs.

| Mode | HTML fabriqué | Idéal pour |
| --- | --- | --- |
| CSR | Dans le navigateur | Zones très interactives, derrière une connexion |
| SSR | À chaque requête, sur le serveur | Contenu personnalisé ou changeant |
| SSG | Une fois, au build | Contenu stable et public |

Le point fort de Next.js : tu peux **mélanger les trois dans la même application**, page par page, et même composant par composant. Une page de blog peut être statique, le tableau de bord dynamique, et un bouton « J'aime » interactif côté client.

> **Astuce** : pose-toi toujours la question « ce contenu change-t-il d'un utilisateur à l'autre, ou d'une minute à l'autre ? ». Si non, privilégie le statique. Si oui, le serveur. Si c'est de l'interaction pure, le client.

## L'App Router : le routage par dossiers

Dans une application React classique, tu installes une bibliothèque de routage et tu déclares une liste de routes dans le code. Avec Next.js, **ta structure de dossiers est ta table de routes**. Depuis Next.js 13, ce système s'appelle l'**App Router** et vit dans un dossier `app/`.

```text
app/
├── page.tsx                  → /
├── a-propos/
│   └── page.tsx              → /a-propos
└── roadmaps/
    ├── page.tsx              → /roadmaps
    └── [slug]/
        └── page.tsx          → /roadmaps/react, /roadmaps/laravel…
```

Les règles sont simples :

- un **dossier** correspond à un segment d'URL ;
- un fichier nommé **`page`** rend ce segment accessible publiquement ;
- un nom entre crochets, comme `[slug]`, crée un segment **dynamique**.

Le contenu d'une page est un composant React exporté par défaut :

```tsx
// app/a-propos/page.tsx
export default function PageAPropos() {
  return (
    <main>
      <h1>À propos de DevRoad</h1>
      <p>Des roadmaps pour apprendre à ton rythme.</p>
    </main>
  );
}
```

Aucune configuration : crée le fichier, la page existe. Tu approfondiras tout cela au chapitre « App Router et routing ».

## Les fichiers spéciaux

Next.js reconnaît des noms de fichiers qui ont chacun un rôle précis. Tu en croiseras beaucoup, voici les principaux :

| Fichier | Rôle |
| --- | --- |
| `page.tsx` | Le contenu d'une route |
| `layout.tsx` | Une mise en page partagée (en-tête, menu) qui entoure les pages |
| `loading.tsx` | Ce qui s'affiche pendant le chargement des données |
| `error.tsx` | Ce qui s'affiche quand une erreur survient |
| `not-found.tsx` | La page 404 personnalisée |
| `route.ts` | Un point d'entrée d'API (sans interface) |

Le `layout.tsx` racine est obligatoire : il définit la structure HTML de toute l'application.

```tsx
// app/layout.tsx
import type { ReactNode } from 'react';

export const metadata = {
  title: 'DevRoad',
  description: 'Roadmaps et fiches mémo pour développeurs',
};

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="fr">
      <body>
        <header>DevRoad</header>
        {children}
      </body>
    </html>
  );
}
```

La propriété `children` représente la page courante : quand tu navigues, le layout reste en place et seul `children` change. Ce comportement évite de recharger l'en-tête à chaque clic.

:::quiz
Quel fichier faut-il créer pour que l'URL /contact soit accessible dans l'App Router ?
- [ ] app/contact.tsx
- [ ] app/routes/contact.tsx
- [x] app/contact/page.tsx
- [ ] app/contact/index.html
> Dans l'App Router, un dossier représente un segment d'URL et c'est le fichier page.tsx à l'intérieur qui rend la route accessible.
:::

## Le grand changement : composants serveur par défaut

C'est la nouveauté qui déroute le plus les développeurs React, alors lis attentivement. Dans l'App Router, **tous les composants sont des composants serveur par défaut**. Ils s'exécutent sur le serveur, et seul leur résultat (du HTML) est envoyé au navigateur. Leur code JavaScript n'est pas téléchargé par l'utilisateur.

Conséquence très agréable : un composant serveur peut être `async` et lire directement les données.

```tsx
// app/roadmaps/page.tsx — composant serveur
type Roadmap = { id: number; title: string };

async function chargerRoadmaps(): Promise<Roadmap[]> {
  const reponse = await fetch('https://api.exemple.com/roadmaps');
  return reponse.json();
}

export default async function PageRoadmaps() {
  const roadmaps = await chargerRoadmaps();

  return (
    <ul>
      {roadmaps.map((r) => (
        <li key={r.id}>{r.title}</li>
      ))}
    </ul>
  );
}
```

Pas de `useEffect`, pas d'état de chargement à gérer à la main, pas de requête visible depuis le navigateur. Quand tu as besoin d'interactivité (un clic, un état avec `useState`), tu marques le fichier avec `'use client'` et il devient un **composant client**. Le chapitre 4 est entièrement consacré à cette distinction : pour l'instant, retiens simplement l'idée.

## Next.js face à Laravel + Inertia

DevRoad est construit avec Laravel (back-end) et Inertia (liaison avec React). Pourquoi alors apprendre Next.js ? Parce que ce sont deux philosophies, et que tu rencontreras les deux.

| Critère | Laravel + Inertia + React | Next.js |
| --- | --- | --- |
| Langage du back-end | PHP | JavaScript / TypeScript |
| Base de données | Eloquent, migrations intégrées | À choisir : Prisma, Supabase… |
| Authentification | Fournie (Breeze, Fortify) | À choisir : Auth.js, Supabase Auth… |
| Routage | Fichier `routes/web.php` | Dossiers dans `app/` |
| Rendu | Pages React avec props du contrôleur | Composants serveur et client |
| Points forts | Back-end complet « tout inclus » | Une seule langue, SEO, performance, écosystème JavaScript |

Aucune des deux n'est « meilleure ». Si ton projet est une application de gestion très riche en règles métier (un ERP, par exemple), Laravel est excellent. Si tu construis un site vitrine ou un produit très orienté contenu, performance et référencement, ou si ton équipe est surtout composée de développeurs JavaScript, Next.js est un très bon choix. Beaucoup de projets combinent d'ailleurs les deux : un back-end Laravel exposant une API, et un front Next.js.

> **Exemple** : une plateforme de petites annonces à Abidjan où le SEO est vital (chaque annonce doit être trouvée sur Google) tirera grand profit du rendu serveur de Next.js. Un outil interne de gestion de stock, lui, se contentera très bien de Laravel + Inertia.

## Atelier guidé : explorer sans installer

Compte quarante minutes. L'objectif est d'observer la différence entre rendu client et rendu serveur, sans écrire de code Next.js.

1. Ouvre un site fait avec Next.js (par exemple le site officiel nextjs.org) dans ton navigateur.
2. Fais un clic droit, puis « Afficher le code source de la page » (et non « Inspecter »). Repère que le texte de la page est présent dans le HTML : c'est du rendu serveur.
3. Ouvre ensuite une application React classique en CSR (par exemple une démo créée avec Vite) et regarde son code source : tu ne trouves qu'une `div` vide.
4. Dans les outils de développement, onglet « Réseau », recharge la page de nextjs.org et observe les fichiers JavaScript chargés. Note leur nombre approximatif.
5. Sur une feuille, dessine l'arborescence `app/` d'un site de blog avec : une page d'accueil, une liste d'articles, une page par article, une page « Contact ».
6. À côté de chaque page, écris le mode de rendu que tu choisirais (statique, serveur ou client) et justifie en une phrase.
7. Rédige en trois lignes la différence entre un composant serveur et un composant client, avec tes propres mots.

Pour t'auto-évaluer : sans regarder ce chapitre, peux-tu expliquer pourquoi le texte d'une page Next.js est visible dans le code source, alors qu'il ne l'est pas dans une application React créée avec Vite ?

## Erreurs fréquentes

- **Croire que Next.js remplace React.** Tu continues d'écrire des composants React ; Next.js les organise.
- **Confondre `pages/` et `app/`.** Le dossier `pages/` est l'ancien système (Pages Router). Ce cours utilise l'App Router, dans `app/`. Les tutoriels anciens peuvent donc mélanger les deux.
- **Oublier que tout est un composant serveur par défaut.** Utiliser `useState` dans un composant sans `'use client'` provoque une erreur. Nous y reviendrons en détail.
- **Choisir un mode de rendu au hasard.** Tout rendre côté serveur à chaque requête alourdit le serveur ; tout rendre côté client dégrade le SEO.
- **Vouloir tout apprendre d'un coup.** Next.js est vaste. Avance par couches : routage, composants, données, puis performance.

## Bonnes pratiques

- Commence par le routage et les composants serveur ; n'ajoute le JavaScript client que là où l'interaction l'exige.
- Choisis le mode de rendu selon la nature du contenu, pas par habitude.
- Utilise TypeScript dès le départ : il t'évite beaucoup d'erreurs dans un framework riche en conventions.
- Lis la documentation officielle (nextjs.org/docs) en vérifiant que tu consultes bien la section **App Router**.
- Garde une structure de dossiers lisible : une route, un dossier, un rôle clair.

## À retenir

- Next.js est un **framework construit sur React** qui ajoute routage, rendu serveur, optimisations et back-end léger.
- Le rendu peut être **client**, **serveur** ou **statique**, et Next.js permet de les combiner.
- L'**App Router** utilise le dossier `app/` : un dossier est un segment d'URL, `page.tsx` rend la route accessible.
- Les fichiers spéciaux (`layout`, `loading`, `error`, `not-found`, `route`) ont chacun un rôle fixé par convention.
- Par défaut, les composants sont des **composants serveur** ; `'use client'` active l'interactivité.
- Next.js et Laravel + Inertia sont deux approches complémentaires : choisis selon le projet et l'équipe.
