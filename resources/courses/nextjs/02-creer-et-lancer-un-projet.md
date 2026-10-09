---
title: Créer et lancer un projet
minutes: 110
level: beginner
---

## Ce que tu vas apprendre

La théorie, c'est bien ; voir sa première page s'afficher, c'est mieux. Dans ce chapitre, tu installes l'environnement, tu génères un projet Next.js 15, tu comprends ce que contient chaque fichier, et tu lances le serveur de développement. Tu repartiras avec la base du projet « DevRoad Web » que tu feras grandir tout au long du cours.

À la fin du chapitre, tu seras capable de :

- vérifier et installer les outils nécessaires (Node.js, npm, un éditeur) ;
- créer un projet avec `create-next-app` en choisissant les bonnes options ;
- lire l'arborescence d'un projet Next.js et expliquer le rôle de chaque fichier ;
- lancer le serveur de développement, le build de production et le linter ;
- configurer des variables d'environnement et des alias d'import ;
- modifier une page et constater le rechargement à chaud.

Prérequis : avoir lu le chapitre « Découvrir Next.js », savoir ouvrir un terminal et exécuter une commande. Prévois environ une heure cinquante.

## Préparer son environnement

Next.js 15 a besoin de **Node.js** version 18.18 ou plus récente ; la version 20 ou 22 LTS est recommandée. Node.js est l'environnement qui permet d'exécuter du JavaScript en dehors du navigateur. Vérifie ce que tu as déjà :

```bash
node --version
npm --version
```

Si la commande échoue, ou si la version est trop ancienne, installe la version LTS depuis nodejs.org. Sous Linux ou macOS, un gestionnaire de versions comme `nvm` est pratique pour passer d'une version à l'autre :

```bash
nvm install 22
nvm use 22
```

`npm` est installé avec Node.js : c'est le gestionnaire de paquets qui télécharge les bibliothèques dont ton projet a besoin. Pour l'éditeur, Visual Studio Code avec les extensions **ESLint**, **Prettier** et **Tailwind CSS IntelliSense** est un excellent choix.

> **Astuce** : si ta connexion est limitée, installe les dépendances une fois à la maison ou au bureau avec un bon réseau. Un projet Next.js télécharge plusieurs centaines de paquets lors de la première installation. Les installations suivantes réutilisent le cache de npm.

## Créer le projet avec create-next-app

L'outil officiel de génération s'appelle `create-next-app`. Dans ton dossier de travail, lance :

```bash
npx create-next-app@latest devroad-web
```

`npx` exécute un outil sans l'installer durablement. L'assistant te pose des questions. Voici les réponses conseillées pour ce cours :

| Question | Réponse | Pourquoi |
| --- | --- | --- |
| Utiliser TypeScript ? | Oui | Détecte les erreurs avant l'exécution |
| Utiliser ESLint ? | Oui | Vérifie la qualité du code |
| Utiliser Tailwind CSS ? | Oui | Styles rapides par classes utilitaires |
| Utiliser le dossier `src/` ? | Oui | Sépare ton code des fichiers de configuration |
| Utiliser l'App Router ? | Oui | Le système moderne de Next.js |
| Utiliser Turbopack en dev ? | Oui | Démarrage et rechargement plus rapides |
| Personnaliser l'alias d'import ? | Non (garde `@/*`) | Imports courts et lisibles |

Quand l'installation est finie, entre dans le dossier :

```bash
cd devroad-web
```

Tu peux aussi passer toutes les options en une seule ligne, utile pour automatiser ou documenter :

```bash
npx create-next-app@latest devroad-web --ts --tailwind --eslint --app --src-dir --turbopack --import-alias "@/*"
```

## Lancer le serveur de développement

Dans le dossier du projet :

```bash
npm run dev
```

Après quelques secondes, le terminal affiche une adresse locale, généralement `http://localhost:3000`. Ouvre-la : tu vois la page d'accueil par défaut de Next.js.

Le serveur de développement offre le **rechargement à chaud** (*Fast Refresh*) : dès que tu enregistres un fichier, la page se met à jour sans rechargement complet, en conservant l'état de tes composants quand c'est possible. Teste-le tout de suite en ouvrant `src/app/page.tsx`, en remplaçant tout son contenu par ceci, puis en enregistrant :

```tsx
export default function Accueil() {
  return (
    <main className="mx-auto max-w-2xl p-8">
      <h1 className="text-3xl font-bold">DevRoad Web</h1>
      <p className="mt-2 text-gray-600">
        Mon premier projet Next.js est en ligne sur localhost.
      </p>
    </main>
  );
}
```

Le navigateur se met à jour instantanément. Pour arrêter le serveur, appuie sur `Ctrl + C` dans le terminal.

:::quiz
Quelle commande lance le serveur de développement d'un projet Next.js créé avec create-next-app ?
- [ ] npm start
- [x] npm run dev
- [ ] npx next install
- [ ] node app.js
> Le script « dev » défini dans package.json démarre le serveur de développement avec rechargement à chaud. « npm start » sert à lancer l'application déjà compilée en production.
:::

## Visiter l'arborescence

Voici ce que contient ton projet (certains fichiers peuvent varier légèrement selon la version) :

```text
devroad-web/
├── public/                 fichiers servis tels quels (images, favicon)
├── src/
│   └── app/
│       ├── favicon.ico
│       ├── globals.css     styles globaux (inclut Tailwind)
│       ├── layout.tsx      mise en page racine
│       └── page.tsx        page d'accueil (/)
├── .gitignore
├── eslint.config.mjs       règles ESLint
├── next.config.ts          configuration de Next.js
├── package.json            dépendances et scripts
├── postcss.config.mjs      configuration PostCSS (utilisée par Tailwind)
└── tsconfig.json           configuration TypeScript
```

Prends le temps de comprendre les fichiers clés.

### package.json

C'est la carte d'identité du projet : son nom, ses dépendances, et ses **scripts**.

```json
{
  "name": "devroad-web",
  "version": "0.1.0",
  "private": true,
  "scripts": {
    "dev": "next dev --turbopack",
    "build": "next build",
    "start": "next start",
    "lint": "next lint"
  },
  "dependencies": {
    "next": "15.x.x",
    "react": "^19.0.0",
    "react-dom": "^19.0.0"
  }
}
```

Chaque script s'exécute avec `npm run nom`. Les quatre à connaître :

- `npm run dev` : serveur de développement ;
- `npm run build` : compile l'application pour la production et affiche, route par route, si elle est statique ou dynamique ;
- `npm run start` : sert l'application compilée (il faut avoir fait un `build` avant) ;
- `npm run lint` : lance ESLint pour repérer les problèmes.

### Le dossier public

Tout fichier placé dans `public/` est accessible à la racine du site. Un fichier `public/logo.svg` est servi à l'adresse `/logo.svg`. Utilise-le pour les images, les polices locales, le fichier `robots.txt`.

### Le layout racine

Ouvre `src/app/layout.tsx`. C'est la coquille HTML de toute l'application. Personnalise-la pour DevRoad :

```tsx
import type { Metadata } from 'next';
import './globals.css';

export const metadata: Metadata = {
  title: 'DevRoad Web',
  description: 'Roadmaps d’apprentissage pour développeurs francophones',
};

export default function RootLayout({
  children,
}: Readonly<{ children: React.ReactNode }>) {
  return (
    <html lang="fr">
      <body className="bg-white text-gray-900 antialiased">{children}</body>
    </html>
  );
}
```

Remarque deux choses importantes : l'attribut `lang="fr"` (utile pour l'accessibilité et le SEO), et l'import de `globals.css` qui charge Tailwind pour toute l'application.

### tsconfig.json et l'alias @

Dans `tsconfig.json`, la section `paths` définit l'alias `@/*` qui pointe vers `src/*`. Au lieu d'écrire de longs chemins relatifs, tu écris :

```tsx
import CarteRoadmap from '@/components/CarteRoadmap';
// plutôt que
import CarteRoadmap from '../../../components/CarteRoadmap';
```

Cet alias garde tes imports lisibles, quelle que soit la profondeur du fichier.

## Créer ta première page et ton premier composant

Ajoutons une page de liste de roadmaps et un composant réutilisable. D'abord, le composant, dans `src/components/CarteRoadmap.tsx` :

```tsx
type Props = {
  titre: string;
  etapes: number;
};

export default function CarteRoadmap({ titre, etapes }: Props) {
  return (
    <article className="rounded-lg border p-4 shadow-sm">
      <h2 className="text-lg font-semibold">{titre}</h2>
      <p className="text-sm text-gray-500">{etapes} étapes</p>
    </article>
  );
}
```

Puis la page, dans `src/app/roadmaps/page.tsx` :

```tsx
import CarteRoadmap from '@/components/CarteRoadmap';

const roadmaps = [
  { id: 1, titre: 'React', etapes: 12 },
  { id: 2, titre: 'Laravel', etapes: 15 },
  { id: 3, titre: 'Node.js', etapes: 9 },
];

export default function PageRoadmaps() {
  return (
    <main className="mx-auto max-w-2xl space-y-4 p-8">
      <h1 className="text-2xl font-bold">Les roadmaps</h1>
      {roadmaps.map((r) => (
        <CarteRoadmap key={r.id} titre={r.titre} etapes={r.etapes} />
      ))}
    </main>
  );
}
```

Visite `http://localhost:3000/roadmaps` : la page existe sans aucune configuration de routes. Tu viens de vérifier concrètement que le dossier `roadmaps` devient l'URL `/roadmaps`.

## Variables d'environnement

Une application a des valeurs qui changent selon l'environnement : l'adresse d'une API, une clé secrète. On ne les écrit **jamais** directement dans le code. On les place dans un fichier `.env.local`, à la racine du projet :

```bash
# .env.local
API_URL=https://api.exemple.com
NEXT_PUBLIC_NOM_APP=DevRoad Web
```

Deux catégories existent :

- une variable **sans préfixe** (`API_URL`) n'est lisible que côté serveur ;
- une variable préfixée par **`NEXT_PUBLIC_`** est incorporée au code envoyé au navigateur, donc visible par tout le monde.

```tsx
// Dans un composant serveur
const url = process.env.API_URL;
// Utilisable partout, y compris côté client
const nom = process.env.NEXT_PUBLIC_NOM_APP;
```

> **Attention** : ne place jamais de secret (clé d'API privée, mot de passe de base de données) dans une variable `NEXT_PUBLIC_`. Elle serait lisible par n'importe quel visiteur. Le fichier `.env.local` est déjà ignoré par Git grâce au `.gitignore` généré : vérifie qu'il l'est bien avant tout `git push`.

Après avoir modifié un fichier `.env.local`, redémarre le serveur de développement.

## Construire pour la production

Avant de déployer, vérifie que ton projet compile :

```bash
npm run build
```

La sortie ressemble à ceci :

```text
Route (app)                Size     First Load JS
┌ ○ /                      1.2 kB   105 kB
├ ○ /_not-found            977 B    101 kB
└ ○ /roadmaps              1.1 kB   105 kB

○  (Static)  prerendered as static content
```

Le symbole `○` indique que la route est **statique** : fabriquée au build. Tu verras `ƒ (Dynamic)` pour les routes rendues à chaque requête. Cette lecture te permet de vérifier que tes choix de rendu sont bien ceux que tu voulais. Teste ensuite la version de production localement avec `npm run start`.

:::quiz
Dans quel cas une variable d'environnement est-elle accessible dans le navigateur ?
- [ ] Lorsqu'elle est définie dans le fichier .gitignore
- [ ] Lorsqu'elle est écrite en majuscules
- [x] Lorsque son nom commence par NEXT_PUBLIC_
- [ ] Toujours, quelle que soit son nom
> Seules les variables préfixées par NEXT_PUBLIC_ sont incorporées au code client. Les autres restent côté serveur, ce qui protège tes secrets.
:::

## Atelier guidé : le socle de DevRoad Web

Compte une heure. Tu vas créer le projet que tu enrichiras dans les chapitres suivants.

1. Vérifie `node --version` (18.18 minimum, 20 ou 22 recommandé).
2. Lance `npx create-next-app@latest devroad-web` avec les options du tableau (TypeScript, ESLint, Tailwind, `src/`, App Router).
3. Lance `npm run dev` et ouvre `http://localhost:3000`.
4. Remplace le contenu de `src/app/page.tsx` par une page d'accueil avec un titre « DevRoad Web » et une phrase de présentation.
5. Modifie `layout.tsx` : titre de la métadonnée « DevRoad Web », `lang="fr"`.
6. Crée le composant `CarteRoadmap` dans `src/components/` et la page `/roadmaps` qui affiche trois cartes.
7. Ajoute un fichier `.env.local` avec `NEXT_PUBLIC_NOM_APP=DevRoad Web` et affiche cette valeur dans le titre de la page d'accueil. Redémarre le serveur pour qu'elle soit prise en compte.
8. Lance `npm run lint` puis `npm run build`. Corrige les éventuelles erreurs.
9. Initialise un dépôt Git et fais un premier commit, après avoir vérifié que `.env.local` n'est pas suivi.

Pour t'auto-évaluer : sans aide, peux-tu créer une nouvelle page `/contact` en moins de deux minutes, et dire où irait un composant `Entete` réutilisable ?

## Erreurs fréquentes

- **Lancer `npm run dev` hors du dossier du projet.** L'erreur « missing script: dev » signifie presque toujours que tu n'es pas dans le bon dossier.
- **Oublier de redémarrer après avoir modifié `.env.local`.** Les variables sont lues au démarrage.
- **Mettre un secret dans `NEXT_PUBLIC_`.** Il devient public.
- **Placer le fichier `page.tsx` au mauvais endroit.** Il doit être dans `src/app/…`, pas à la racine du projet.
- **Nommer un fichier `Page.tsx` ou `index.tsx`.** L'App Router attend exactement `page.tsx` en minuscules.
- **Ignorer les erreurs du build.** Une page qui fonctionne en développement peut échouer à la compilation de production ; lance `npm run build` régulièrement.
- **Supprimer `layout.tsx`.** Le layout racine est obligatoire.

## Bonnes pratiques

- Garde ton Node.js sur une version LTS et note-la dans le README du projet.
- Lance `npm run lint` et `npm run build` avant chaque envoi de code important.
- Organise `src/` en dossiers clairs : `app/` pour le routage, `components/` pour l'interface, `lib/` pour la logique.
- Ne commite jamais `.env.local` ; fournis plutôt un fichier `.env.example` sans valeurs secrètes.
- Utilise l'alias `@/` pour tous les imports internes.
- Lis le message d'erreur dans le terminal **et** dans le navigateur : Next.js affiche une surcouche très détaillée en développement.

## À retenir

- Next.js 15 nécessite Node.js 18.18 minimum ; `create-next-app` génère un projet prêt à l'emploi.
- Les scripts essentiels sont `dev`, `build`, `start` et `lint`.
- `src/app/` contient le routage : `layout.tsx` est la coquille racine, `page.tsx` l'accueil.
- Le dossier `public/` sert les fichiers statiques à la racine du site.
- L'alias `@/` pointe vers `src/` et simplifie les imports.
- Les variables d'environnement vont dans `.env.local` ; seules celles préfixées par `NEXT_PUBLIC_` atteignent le navigateur.
- `npm run build` montre pour chaque route si elle est statique ou dynamique.
