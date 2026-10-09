---
title: Projet final Next.js
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru le routage, les composants serveur et client, les données, les formulaires, les API et la performance. Il est temps d'assembler tout cela dans un vrai projet, de A à Z, comme tu le ferais pour un client. Tu vas construire **DevRoad Lite** : une application web de roadmaps d'apprentissage où l'on consulte des parcours, on coche ses étapes terminées et on rédige ses propres fiches mémo.

À la fin du projet, tu seras capable de :

- cadrer un petit produit à partir d'un cahier des charges et le découper en tâches ;
- concevoir un modèle de données et le manipuler avec Prisma ;
- construire des pages serveur, des îlots clients et des formulaires avec Server Actions ;
- implémenter une authentification simple par session et protéger les routes ;
- exposer une API JSON documentée avec des Route Handlers ;
- optimiser, tester manuellement et déployer l'application ;
- présenter ton travail avec un README et une checklist d'acceptation.

Prérequis : les six chapitres précédents, Git et un compte GitHub. Durée : environ six heures, à répartir sur plusieurs sessions. Avance par étapes et fais un commit à la fin de chacune.

## Le cahier des charges

### Contexte

Des apprenants francophones veulent suivre des parcours structurés sans se perdre. DevRoad Lite leur permet de consulter des roadmaps, de suivre leur progression et de garder des notes personnelles. Beaucoup utiliseront un téléphone avec une connexion limitée : l'application doit donc être légère et rapide.

### Fonctionnalités attendues

| Réf. | Fonctionnalité | Priorité |
| --- | --- | --- |
| F1 | Page d'accueil présentant le produit et les roadmaps populaires | Obligatoire |
| F2 | Liste des roadmaps avec filtre par niveau (`?niveau=`) | Obligatoire |
| F3 | Page détail d'une roadmap avec ses étapes ordonnées | Obligatoire |
| F4 | Inscription, connexion, déconnexion | Obligatoire |
| F5 | Marquer une étape comme terminée et voir sa progression (en pourcentage) | Obligatoire |
| F6 | Créer, lister et supprimer ses fiches mémo | Obligatoire |
| F7 | API JSON : liste des roadmaps et détail d'une roadmap | Obligatoire |
| F8 | SEO : métadonnées par page, sitemap, robots | Obligatoire |
| F9 | Recherche de roadmaps par mot-clé | Bonus |
| F10 | Mode sombre persistant | Bonus |

### Contraintes techniques

- Next.js 15, App Router, TypeScript strict.
- Prisma avec SQLite en développement (PostgreSQL possible en production).
- Zod pour toutes les validations.
- Tailwind CSS pour les styles.
- Composants serveur par défaut ; `'use client'` seulement pour les îlots interactifs.
- Aucun secret dans le dépôt Git.

### Contraintes non fonctionnelles

- Score Lighthouse mobile en production : performance au moins 85, accessibilité au moins 90.
- Toutes les pages ont un titre et une description uniques.
- Les mots de passe sont hachés ; les routes privées sont protégées côté serveur.
- Les messages affichés à l'utilisateur sont en français.

## Étape 1 : cadrage et initialisation (30 min)

Crée le projet et le dépôt :

```bash
npx create-next-app@latest devroad-lite --ts --tailwind --eslint --app --src-dir --turbopack --import-alias "@/*"
cd devroad-lite
git init && git add -A && git commit -m "Initialisation du projet"
npm install prisma @prisma/client zod bcryptjs jose
npm install -D @types/bcryptjs
npx prisma init --datasource-provider sqlite
```

Organise `src/` ainsi :

```text
src/
├── app/
│   ├── (public)/        accueil, roadmaps, connexion, inscription
│   ├── (prive)/         mes-fiches, progression
│   └── api/roadmaps/    API JSON
├── components/          composants d'interface
└── lib/                 prisma.ts, session.ts, validations.ts, donnees.ts
```

Écris dans le README la liste des fonctionnalités F1 à F10 : elle te servira de feuille de route.

## Étape 2 : modèle de données (40 min)

Dans `prisma/schema.prisma`, décris les tables :

```prisma
model Utilisateur {
  id          Int          @id @default(autoincrement())
  email       String       @unique
  nom         String
  motDePasse  String
  creeLe      DateTime     @default(now())
  fiches      Fiche[]
  progressions Progression[]
}

model Roadmap {
  id          Int      @id @default(autoincrement())
  slug        String   @unique
  titre       String
  description String
  niveau      String
  etapes      Etape[]
}

model Etape {
  id        Int      @id @default(autoincrement())
  titre     String
  ordre     Int
  roadmapId Int
  roadmap   Roadmap  @relation(fields: [roadmapId], references: [id], onDelete: Cascade)
  progressions Progression[]
}

model Progression {
  utilisateurId Int
  etapeId       Int
  terminee      Boolean     @default(true)
  utilisateur   Utilisateur @relation(fields: [utilisateurId], references: [id], onDelete: Cascade)
  etape         Etape       @relation(fields: [etapeId], references: [id], onDelete: Cascade)

  @@id([utilisateurId, etapeId])
}

model Fiche {
  id            Int         @id @default(autoincrement())
  titre         String
  contenu       String
  utilisateurId Int
  utilisateur   Utilisateur @relation(fields: [utilisateurId], references: [id], onDelete: Cascade)
}
```

Crée la migration, puis le client unique :

```bash
npx prisma migrate dev --name init
```

```ts
// src/lib/prisma.ts
import { PrismaClient } from '@prisma/client';

const globalPourPrisma = globalThis as unknown as { prisma?: PrismaClient };

export const prisma = globalPourPrisma.prisma ?? new PrismaClient();

if (process.env.NODE_ENV !== 'production') {
  globalPourPrisma.prisma = prisma;
}
```

Ce petit motif évite de créer un nouveau client à chaque rechargement à chaud. Écris ensuite `prisma/seed.ts` pour insérer au moins trois roadmaps de six étapes chacune, déclare-le dans `package.json` (`"prisma": { "seed": "tsx prisma/seed.ts" }`), et lance `npx prisma db seed`.

:::quiz
Pourquoi crée-t-on le client Prisma dans un module unique stocké sur globalThis en développement ?
- [ ] Pour accélérer les requêtes SQL
- [x] Pour éviter de multiplier les connexions à chaque rechargement à chaud
- [ ] Parce que Prisma ne fonctionne pas dans les composants serveur
- [ ] Pour chiffrer la base de données
> Le rechargement à chaud ré-exécute les modules. Sans singleton, chaque rechargement créerait un nouveau client et de nouvelles connexions.
:::

## Étape 3 : pages publiques (50 min)

Réalise F1, F2 et F3 uniquement avec des composants serveur.

```tsx
// src/app/(public)/roadmaps/page.tsx
import Link from 'next/link';
import { prisma } from '@/lib/prisma';

export const metadata = { title: 'Roadmaps', description: 'Tous les parcours DevRoad Lite.' };

const NIVEAUX = ['debutant', 'intermediaire', 'professionnel'];

export default async function PageRoadmaps({
  searchParams,
}: {
  searchParams: Promise<{ niveau?: string }>;
}) {
  const { niveau } = await searchParams;
  const filtre = niveau && NIVEAUX.includes(niveau) ? niveau : undefined;

  const roadmaps = await prisma.roadmap.findMany({
    where: filtre ? { niveau: filtre } : undefined,
    include: { _count: { select: { etapes: true } } },
    orderBy: { titre: 'asc' },
  });

  return (
    <main className="mx-auto max-w-3xl space-y-6 p-6">
      <h1 className="text-2xl font-bold">Roadmaps</h1>
      <nav className="flex gap-3 text-sm">
        <Link href="/roadmaps">Tous</Link>
        {NIVEAUX.map((n) => (
          <Link key={n} href={`/roadmaps?niveau=${n}`}>{n}</Link>
        ))}
      </nav>
      <ul className="grid gap-4 sm:grid-cols-2">
        {roadmaps.map((r) => (
          <li key={r.id} className="rounded-lg border p-4">
            <Link href={`/roadmaps/${r.slug}`} className="font-semibold">{r.titre}</Link>
            <p className="text-sm text-gray-500">{r._count.etapes} étapes · {r.niveau}</p>
          </li>
        ))}
      </ul>
    </main>
  );
}
```

Pour la page détail, utilise `generateStaticParams` (liste des slugs), `generateMetadata`, `notFound()` si le slug est inconnu, et `include: { etapes: { orderBy: { ordre: 'asc' } } }`. Ajoute `loading.tsx`, `error.tsx` et `not-found.tsx`.

## Étape 4 : authentification par session (60 min)

Tu vas implémenter un système simple, sans bibliothèque lourde : un cookie `session` contenant un jeton signé (JWT) avec `jose`.

```ts
// src/lib/session.ts
import 'server-only';
import { SignJWT, jwtVerify } from 'jose';
import { cookies } from 'next/headers';

const cle = new TextEncoder().encode(process.env.SESSION_SECRET);

export async function creerSession(utilisateurId: number) {
  const jeton = await new SignJWT({ utilisateurId })
    .setProtectedHeader({ alg: 'HS256' })
    .setExpirationTime('7d')
    .sign(cle);

  (await cookies()).set('session', jeton, {
    httpOnly: true,
    secure: process.env.NODE_ENV === 'production',
    sameSite: 'lax',
    path: '/',
    maxAge: 60 * 60 * 24 * 7,
  });
}

export async function lireSession(): Promise<number | null> {
  const jeton = (await cookies()).get('session')?.value;
  if (!jeton) return null;
  try {
    const { payload } = await jwtVerify(jeton, cle);
    return Number(payload.utilisateurId);
  } catch {
    return null;
  }
}

export async function detruireSession() {
  (await cookies()).delete('session');
}
```

Ajoute `SESSION_SECRET` dans `.env.local` (une longue chaîne aléatoire, générée par `openssl rand -base64 32`) et un `.env.example` sans valeur. Écris ensuite les Server Actions `inscription`, `connexion` et `deconnexion` :

- valide l'e-mail et le mot de passe (8 caractères minimum) avec Zod ;
- hache avec `bcrypt.hash(motDePasse, 10)` à l'inscription, compare avec `bcrypt.compare` à la connexion ;
- à la connexion, renvoie le **même message** (« Identifiants incorrects ») que l'e-mail ou le mot de passe soit faux, pour ne pas révéler quels comptes existent ;
- crée la session puis `redirect('/roadmaps')`.

Crée enfin une fonction `exigerUtilisateur()` qui lit la session et redirige vers `/connexion` si elle est absente. Appelle-la en tête de **chaque** page et action privée. Le `middleware.ts` peut ajouter une redirection rapide pour `/mes-fiches`, mais la vraie protection reste dans les fonctions serveur.

## Étape 5 : progression et fiches (70 min)

Pour F5, crée une Server Action `basculerEtape(etapeId)` : elle vérifie la session, valide l'identifiant, ajoute ou supprime la ligne `Progression`, puis appelle `revalidatePath` sur la page de la roadmap. Le bouton est un petit composant client qui utilise `useOptimistic` ou `useTransition` pour une réaction immédiate :

```tsx
'use client';

import { useTransition } from 'react';
import { basculerEtape } from '@/app/(prive)/actions';

export default function CaseEtape({ etapeId, terminee }: { etapeId: number; terminee: boolean }) {
  const [enCours, demarrer] = useTransition();

  return (
    <button
      onClick={() => demarrer(() => basculerEtape(etapeId))}
      disabled={enCours}
      aria-pressed={terminee}
      className="rounded border px-2 py-1 text-sm disabled:opacity-50"
    >
      {terminee ? '✔ Terminée' : 'Marquer terminée'}
    </button>
  );
}
```

Calcule la progression côté serveur (étapes terminées divisées par le nombre total) et affiche-la dans une barre de progression accessible (`role="progressbar"` avec `aria-valuenow`).

Pour F6, reprends le motif du chapitre 5 : schéma Zod, Server Action `creerFiche`, formulaire client avec `useActionState` et `useFormStatus`, liste des fiches **de l'utilisateur connecté uniquement**, et action `supprimerFiche`. Dans la suppression, vérifie que la fiche appartient bien à l'utilisateur :

```ts
await prisma.fiche.deleteMany({ where: { id, utilisateurId } });
```

L'utilisation de `deleteMany` avec le propriétaire dans le filtre empêche de supprimer la fiche d'un autre en devinant son identifiant.

:::quiz
Pourquoi supprimer avec deleteMany({ where: { id, utilisateurId } }) plutôt qu'avec delete({ where: { id } }) ?
- [ ] Parce que delete n'existe pas dans Prisma
- [x] Pour s'assurer que l'utilisateur ne peut supprimer que ses propres fiches
- [ ] Pour supprimer toutes les fiches de la base
- [ ] Pour accélérer l'affichage de la liste
> Inclure le propriétaire dans le filtre empêche un utilisateur de supprimer la ressource d'un autre en devinant un identifiant.
:::

## Étape 6 : API JSON (30 min)

Crée `GET /api/roadmaps` (liste, avec filtre optionnel `niveau`) et `GET /api/roadmaps/[id]` (détail avec étapes). Retourne les statuts HTTP adéquats (`200`, `404`) et un corps JSON cohérent. Ajoute l'en-tête de cache suivant pour la liste, qui change peu :

```ts
return NextResponse.json(roadmaps, {
  headers: { 'Cache-Control': 'public, s-maxage=300, stale-while-revalidate=600' },
});
```

Documente les deux endpoints (méthode, URL, paramètres, exemple de réponse) dans le README.

## Étape 7 : performance, SEO et accessibilité (40 min)

- Ajoute une police avec `next/font` et une image avec `next/image` (dimensions et `alt`).
- Mets `Suspense` autour de la liste des fiches pour la diffuser en streaming.
- Crée `sitemap.ts` (qui liste dynamiquement les roadmaps depuis la base) et `robots.ts` (qui interdit `/api/` et les pages privées).
- Vérifie la navigation au clavier, les contrastes, les labels des champs et la structure des titres (un seul `h1` par page).
- Lance Lighthouse mobile sur un build de production et corrige jusqu'à atteindre les seuils.

## Étape 8 : déploiement et présentation (30 min)

Pousse le dépôt sur GitHub. Sur Vercel, importe le projet, définis `SESSION_SECRET` et `DATABASE_URL`. SQLite ne convient pas à Vercel (le système de fichiers n'y est pas persistant) : pour la production, crée une base PostgreSQL gratuite (Supabase ou Neon), change `provider` en `postgresql` dans le schéma, ajoute la commande `prisma migrate deploy` à ton processus de build, puis relance le seed. Rédige enfin un README clair : présentation, captures d'écran, installation locale, variables d'environnement, endpoints d'API et choix techniques.

## Checklist d'acceptation

Ton projet est terminé quand tu peux cocher chaque ligne :

1. L'accueil, la liste filtrable et le détail d'une roadmap fonctionnent (F1 à F3).
2. Un visiteur peut s'inscrire, se connecter et se déconnecter ; les mots de passe sont hachés (F4).
3. Une étape se coche et se décoche ; la progression se met à jour (F5).
4. Un utilisateur crée, liste et supprime ses fiches, et ne voit jamais celles d'un autre (F6).
5. Les deux endpoints d'API répondent avec les bons statuts (F7).
6. Chaque page a un titre et une description uniques ; `sitemap.xml` et `robots.txt` existent (F8).
7. Les routes privées redirigent un visiteur non connecté, **y compris** si l'action est appelée directement.
8. Aucun secret n'est dans Git ; `.env.example` est fourni.
9. `npm run lint` et `npm run build` passent sans erreur.
10. Lighthouse mobile : performance au moins 85, accessibilité au moins 90.
11. L'application est déployée et accessible par une URL publique.
12. Le README permet à un inconnu de lancer le projet en moins de dix minutes.

Auto-évaluation finale : explique à voix haute, comme pour un client, pourquoi tel composant est serveur et tel autre client, comment ton application protège les données d'un utilisateur, et ce que tu changerais pour faire face à dix mille utilisateurs.

## Erreurs fréquentes

- **Se lancer dans le code sans cahier des charges.** Tu perds du temps à refaire ; garde la table F1 à F10 sous les yeux.
- **Oublier de protéger les actions.** Un contrôle dans le middleware ou dans l'interface ne suffit pas.
- **Stocker le mot de passe en clair ou renvoyer l'objet utilisateur complet.** Ne renvoie jamais le hachage.
- **Utiliser SQLite en production sur Vercel.** Les données disparaissent ; choisis PostgreSQL.
- **Mettre `'use client'` sur les pages.** Garde les pages en serveur et isole les boutons.
- **Ne pas valider l'identifiant venant d'un formulaire.** Convertis et vérifie avec Zod.
- **Négliger les états vides et les erreurs.** Une liste sans élément doit afficher un message utile.
- **Faire un énorme commit final.** Fais un commit par étape.

## Bonnes pratiques

- Travaille par petites tranches verticales : une fonctionnalité de la base jusqu'à l'écran, puis la suivante.
- Centralise la validation (Zod), l'accès aux données (`lib/`) et la session (`lib/session.ts`).
- Relis chaque action en te demandant : « qui peut l'appeler, avec quelles données ? ».
- Écris des messages de commit clairs, en français ou en anglais, mais de façon cohérente.
- Teste manuellement les cas d'erreur : formulaire vide, identifiant inexistant, session expirée.
- Garde la performance en tête dès le départ, pas à la fin.
- Documente tes décisions : un projet bien expliqué se vend mieux dans un portfolio.

## À retenir

- Un projet réussi commence par un cahier des charges, un modèle de données et un découpage en étapes.
- Les composants serveur lisent les données ; les îlots clients portent l'interaction.
- Chaque Server Action et chaque Route Handler valide ses entrées et vérifie l'identité de l'appelant.
- Les secrets restent dans les variables d'environnement ; la production demande une base adaptée.
- Lint, build, Lighthouse et checklist d'acceptation sont ton filet de sécurité avant la mise en ligne.
- Ce projet, déployé et documenté, est une vraie pièce de portfolio.
