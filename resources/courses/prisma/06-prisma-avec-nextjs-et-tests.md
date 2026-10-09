---
title: Prisma avec Next.js et tests
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Prisma brille surtout dans une application complète. Next.js, avec son App Router, ses composants serveur et ses Server Actions, est l'un des meilleurs terrains pour l'utiliser : ton code TypeScript lit la base directement, sans API intermédiaire, avec des types de bout en bout. Mais ce duo a ses pièges : rechargement à chaud qui multiplie les connexions, déploiement serverless, génération du client au build. Et puis il y a la question qui sépare les amateurs des professionnels : comment **tester** du code qui parle à une base de données ?

À la fin du chapitre, tu seras capable de :

- intégrer Prisma dans un projet Next.js avec une instance unique du client ;
- lire des données dans un composant serveur ;
- écrire des données avec une Server Action et valider les entrées avec Zod ;
- exposer une route API avec un Route Handler ;
- adapter la configuration à un hébergement serverless (pooling, `directUrl`) ;
- préparer le build et le déploiement (`prisma generate`, `migrate deploy`) ;
- écrire des tests d'intégration contre une vraie base PostgreSQL de test avec Vitest ;
- écrire des tests unitaires en simulant le client Prisma ;
- faire tourner les tests dans une chaîne d'intégration continue.

Prérequis : les chapitres 1 à 5, des bases de Next.js (App Router, composants serveur) et de Vitest ou Jest. Prévois trois heures. Les exemples utilisent Next.js 15 et Prisma 6.

## Mettre en place Prisma dans Next.js

Dans un projet Next.js existant, ajoute Prisma comme avant :

```bash
npm install prisma --save-dev
npm install @prisma/client zod
npx prisma init --datasource-provider postgresql
```

Reprends ensuite le schéma de boutique (`Produit` avec `nom`, `prixXof`, `stock`, `reference`) et lance ta migration.

### Le piège du rechargement à chaud

En développement, Next.js recharge les modules à chaque modification. Si tu écris simplement `export const prisma = new PrismaClient()`, chaque rechargement crée **un nouveau client**, qui ouvre ses propres connexions. Au bout de quelques minutes, PostgreSQL répond « too many connections ». La solution consiste à ranger l'instance sur l'objet global, qui survit aux rechargements :

```ts
// lib/prisma.ts
import { PrismaClient } from '@prisma/client';

const globalPourPrisma = globalThis as unknown as { prisma?: PrismaClient };

export const prisma =
  globalPourPrisma.prisma ??
  new PrismaClient({
    log: process.env.NODE_ENV === 'development' ? ['query', 'error'] : ['error'],
  });

if (process.env.NODE_ENV !== 'production') {
  globalPourPrisma.prisma = prisma;
}
```

En production, il n'y a pas de rechargement à chaud : une seule instance est créée par processus, ce qui est suffisant.

> **À retenir** : un seul `PrismaClient` par processus. Importe toujours `prisma` depuis `lib/prisma.ts`, ne crée jamais d'instance ailleurs.

## Lire dans un composant serveur

Dans l'App Router, les composants sont **serveur** par défaut. Ils peuvent donc appeler Prisma directement, et le résultat est envoyé au navigateur sous forme de HTML :

```tsx
// app/produits/page.tsx
import { prisma } from '@/lib/prisma';

export default async function PageProduits() {
  const produits = await prisma.produit.findMany({
    where: { actif: true },
    orderBy: { creeLe: 'desc' },
    select: { id: true, nom: true, prixXof: true, stock: true },
  });

  return (
    <main>
      <h1>Catalogue</h1>
      <ul>
        {produits.map((p) => (
          <li key={p.id}>
            {p.nom} : {p.prixXof.toLocaleString('fr-FR')} FCFA
            {p.stock === 0 && ' (épuisé)'}
          </li>
        ))}
      </ul>
    </main>
  );
}
```

Aucune route d'API, aucun `fetch`, aucun `useEffect`. Les types de `produits` viennent du schéma. Deux règles importantes :

1. **Ne jamais importer `prisma` dans un composant client** (celui qui commence par `'use client'`). Le client Prisma ne doit exister que côté serveur, sinon tes identifiants de base seraient exposés.
2. Pour une page dynamique, récupère les paramètres de façon asynchrone (Next.js 15) :

```tsx
// app/produits/[id]/page.tsx
import { notFound } from 'next/navigation';
import { prisma } from '@/lib/prisma';

export default async function PageProduit({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const produit = await prisma.produit.findUnique({ where: { id: Number(id) } });

  if (!produit) {
    notFound();
  }

  return <h1>{produit.nom}</h1>;
}
```

`notFound()` affiche la page 404 de Next.js. Vérifie aussi que `Number(id)` n'est pas `NaN` si l'identifiant provient d'une URL.

:::quiz
Où est-il correct d'appeler `prisma.produit.findMany()` dans une application Next.js App Router ?
- [ ] Dans un composant marqué `'use client'`
- [x] Dans un composant serveur, une Server Action ou un Route Handler
- [ ] Dans un `useEffect` côté navigateur
- [ ] Dans un fichier chargé par le navigateur
> Prisma doit s'exécuter côté serveur uniquement : il se connecte à la base avec des identifiants secrets qui ne doivent jamais atteindre le navigateur.
:::

## Écrire avec les Server Actions

Une **Server Action** est une fonction exécutée sur le serveur, appelable depuis un formulaire. Elle est idéale pour créer ou modifier des données. Valide toujours les entrées : un formulaire peut être falsifié.

```ts
// app/produits/actions.ts
'use server';

import { revalidatePath } from 'next/cache';
import { z } from 'zod';
import { Prisma } from '@prisma/client';
import { prisma } from '@/lib/prisma';

const schemaProduit = z.object({
  nom: z.string().min(2, 'Nom trop court'),
  prixXof: z.coerce.number().int().positive('Prix invalide'),
  stock: z.coerce.number().int().min(0),
  reference: z.string().min(3),
});

export type EtatFormulaire = { erreur?: string; ok?: boolean };

export async function creerProduit(
  _etat: EtatFormulaire,
  formData: FormData
): Promise<EtatFormulaire> {
  const analyse = schemaProduit.safeParse(Object.fromEntries(formData));

  if (!analyse.success) {
    return { erreur: analyse.error.issues[0].message };
  }

  try {
    await prisma.produit.create({
      data: { ...analyse.data, categorie: 'ELECTRONIQUE' },
    });
  } catch (e) {
    if (e instanceof Prisma.PrismaClientKnownRequestError && e.code === 'P2002') {
      return { erreur: 'Cette référence existe déjà' };
    }
    throw e;
  }

  revalidatePath('/produits');
  return { ok: true };
}
```

Observe les trois étapes classiques : **valider** avec Zod, **écrire** avec Prisma en traduisant les erreurs connues, puis **invalider le cache** avec `revalidatePath` pour que la liste se mette à jour. Le formulaire, côté client, utilise `useActionState` :

```tsx
'use client';

import { useActionState } from 'react';
import { creerProduit } from './actions';

export function FormulaireProduit() {
  const [etat, action, enCours] = useActionState(creerProduit, {});

  return (
    <form action={action}>
      <input name="nom" placeholder="Nom" required />
      <input name="prixXof" type="number" placeholder="Prix en FCFA" required />
      <input name="stock" type="number" defaultValue={0} />
      <input name="reference" placeholder="Référence" required />
      <button disabled={enCours}>{enCours ? 'Envoi…' : 'Ajouter'}</button>
      {etat.erreur && <p role="alert">{etat.erreur}</p>}
    </form>
  );
}
```

> **Attention** : une Server Action est un point d'entrée public, comme n'importe quelle route. Vérifie l'authentification et les droits à l'intérieur de l'action, pas seulement dans l'interface.

## Les Route Handlers

Quand tu dois exposer une API HTTP (application mobile, webhook de paiement), utilise un Route Handler :

```ts
// app/api/produits/route.ts
import { NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';

export async function GET(requete: Request) {
  const { searchParams } = new URL(requete.url);
  const page = Math.max(1, Number(searchParams.get('page') ?? 1));
  const taille = 20;

  const [total, produits] = await prisma.$transaction([
    prisma.produit.count({ where: { actif: true } }),
    prisma.produit.findMany({
      where: { actif: true },
      skip: (page - 1) * taille,
      take: taille,
      orderBy: { id: 'asc' },
    }),
  ]);

  return NextResponse.json({ page, total, produits });
}
```

Si ta réponse contient un `BigInt` ou un `Decimal`, convertis-les avant : `NextResponse.json` ne sait pas sérialiser un `BigInt`.

## Connexions, serverless et déploiement

### Le pooling

En serverless (Vercel, par exemple), chaque invocation peut démarrer son propre processus, donc ouvrir de nouvelles connexions. PostgreSQL en supporte peu (une centaine). Les fournisseurs comme Neon et Supabase offrent un **pooler** de connexions (PgBouncer). On utilise deux URL :

```prisma
datasource db {
  provider  = "postgresql"
  url       = env("DATABASE_URL")   // via le pooler, pour l'application
  directUrl = env("DIRECT_URL")     // connexion directe, pour les migrations
}
```

Avec PgBouncer en mode transaction, ajoute souvent `?pgbouncer=true` à l'URL du pooler. Les commandes de migration ont besoin de la connexion directe, d'où `directUrl`.

### Le build

Sur une plateforme de déploiement, `node_modules` est reconstruit. Le client Prisma doit donc être généré pendant l'installation ou le build :

```json
{
  "scripts": {
    "postinstall": "prisma generate",
    "build": "prisma migrate deploy && next build"
  }
}
```

Sans `postinstall`, tu obtiens l'erreur « @prisma/client did not initialize yet ». Ne mets pas `migrate deploy` dans le build si plusieurs instances peuvent le lancer en parallèle : fais-le plutôt dans une étape unique du déploiement.

## Tester du code qui utilise Prisma

Un test fiable doit être **rapide**, **isolé** et **reproductible**. Avec une base de données, deux stratégies se complètent.

| Stratégie | Principe | Quand l'utiliser |
| --- | --- | --- |
| Test unitaire avec simulacre (*mock*) | On remplace le client Prisma par un faux | Logique métier pure, cas d'erreur difficiles à provoquer |
| Test d'intégration | On teste contre une vraie base PostgreSQL de test | Requêtes, contraintes, relations, transactions |

> **Astuce** : un mock ne vérifie pas que ta requête est correcte, seulement que ton code appelle Prisma comme prévu. Pour valider un `where`, une contrainte unique ou une transaction, il n'y a pas mieux qu'une vraie base.

### Tests d'intégration avec Vitest

Crée une base dédiée aux tests, distincte de celle de développement, avec son propre fichier d'environnement :

```bash
# .env.test
DATABASE_URL="postgresql://dev:dev@localhost:5432/boutique_test?schema=public"
```

Installe Vitest et prépare le schéma de test avant la suite :

```bash
npm install vitest --save-dev
```

```json
{
  "scripts": {
    "test": "dotenv -e .env.test -- sh -c \"prisma migrate deploy && vitest run\""
  }
}
```

(Installe `dotenv-cli` pour que la commande `dotenv` fonctionne : `npm install dotenv-cli --save-dev`.) Ensuite, une fonction de nettoyage vide les tables entre les tests pour qu'ils ne dépendent pas les uns des autres :

```ts
// tests/helpers.ts
import { prisma } from '@/lib/prisma';

export async function viderBase() {
  await prisma.$executeRaw`TRUNCATE TABLE "Vente", "Produit" RESTART IDENTITY CASCADE`;
}
```

Voici une fonction métier à tester et son test :

```ts
// lib/ventes.ts
import { prisma } from '@/lib/prisma';

export async function vendre(produitId: number, quantite: number) {
  return prisma.$transaction(async (tx) => {
    const produit = await tx.produit.findUniqueOrThrow({ where: { id: produitId } });
    if (produit.stock < quantite) {
      throw new Error('Stock insuffisant');
    }
    await tx.produit.update({
      where: { id: produitId },
      data: { stock: { decrement: quantite } },
    });
    return tx.vente.create({ data: { produitId, quantite } });
  });
}
```

```ts
// tests/ventes.test.ts
import { beforeEach, afterAll, describe, expect, it } from 'vitest';
import { prisma } from '@/lib/prisma';
import { vendre } from '@/lib/ventes';
import { viderBase } from './helpers';

describe('vendre', () => {
  beforeEach(async () => {
    await viderBase();
    await prisma.produit.create({
      data: { id: 1, nom: 'Casque', prixXof: 25000, stock: 5, categorie: 'ELECTRONIQUE', reference: 'CASQ-001' },
    });
  });

  afterAll(async () => {
    await prisma.$disconnect();
  });

  it('décrémente le stock et enregistre la vente', async () => {
    await vendre(1, 2);

    const produit = await prisma.produit.findUniqueOrThrow({ where: { id: 1 } });
    expect(produit.stock).toBe(3);
    expect(await prisma.vente.count()).toBe(1);
  });

  it('refuse la vente et ne change rien si le stock est insuffisant', async () => {
    await expect(vendre(1, 10)).rejects.toThrow('Stock insuffisant');

    const produit = await prisma.produit.findUniqueOrThrow({ where: { id: 1 } });
    expect(produit.stock).toBe(5);
    expect(await prisma.vente.count()).toBe(0);
  });
});
```

Le second test est le plus précieux : il prouve que la transaction n'a laissé aucune trace. Configure l'alias `@` dans `vitest.config.ts`, et exécute les fichiers de test **séquentiellement** (`fileParallelism: false`) tant qu'ils partagent la même base.

:::quiz
Pourquoi vide-t-on les tables avant chaque test d'intégration ?
- [ ] Pour accélérer PostgreSQL
- [x] Pour que chaque test parte d'un état connu et ne dépende pas des autres
- [ ] Pour éviter d'avoir à lancer les migrations
- [ ] Parce que Vitest l'exige
> Des tests qui partagent des données deviennent dépendants de leur ordre d'exécution. Un état initial connu rend chaque test reproductible.
:::

### Tests unitaires avec un simulacre

Pour tester une logique sans base, on injecte un faux client. La façon la plus propre est de passer le client en paramètre de la fonction :

```ts
// lib/promotions.ts
import type { PrismaClient } from '@prisma/client';

export async function appliquerReduction(db: Pick<PrismaClient, 'produit'>, pourcent: number) {
  if (pourcent <= 0 || pourcent >= 100) {
    throw new Error('Pourcentage invalide');
  }
  const { count } = await db.produit.updateMany({
    where: { actif: true },
    data: { prixXof: { multiply: (100 - pourcent) / 100 } },
  });
  return count;
}
```

```ts
// tests/promotions.test.ts
import { describe, expect, it, vi } from 'vitest';
import { appliquerReduction } from '@/lib/promotions';

describe('appliquerReduction', () => {
  it('refuse un pourcentage hors limites sans toucher à la base', async () => {
    const db = { produit: { updateMany: vi.fn() } };
    await expect(appliquerReduction(db as never, 150)).rejects.toThrow('Pourcentage invalide');
    expect(db.produit.updateMany).not.toHaveBeenCalled();
  });

  it('retourne le nombre de produits modifiés', async () => {
    const db = { produit: { updateMany: vi.fn().mockResolvedValue({ count: 4 }) } };
    expect(await appliquerReduction(db as never, 10)).toBe(4);
  });
});
```

Cette approche, appelée **injection de dépendance**, garde la logique testable. Des bibliothèques comme `vitest-mock-extended` génèrent des mocks profonds typés de `PrismaClient` si tu préfères les utiliser.

### Intégration continue

Dans GitHub Actions, un service PostgreSQL démarre à côté de tes tests :

```bash
# .github/workflows/test.yml (extrait)
services:
  postgres:
    image: postgres:16
    env:
      POSTGRES_USER: dev
      POSTGRES_PASSWORD: dev
      POSTGRES_DB: boutique_test
    ports:
      - 5432:5432
    options: >-
      --health-cmd pg_isready --health-interval 5s --health-retries 10
```

Ajoute les étapes `npm ci`, puis `npm test` avec `DATABASE_URL` pointant vers ce service. Ainsi, chaque Pull Request exécute migrations et tests sur une base vierge.

## Atelier guidé : un catalogue testé

Compte une heure et demie.

1. Crée un projet Next.js avec `create-next-app`, installe Prisma et Zod, puis reprends le schéma `Produit` et `Vente`.
2. Écris `lib/prisma.ts` avec l'instance globale et vérifie en modifiant un fichier que le nombre de connexions ne grimpe pas.
3. Crée la page `/produits` en composant serveur avec `select` et affiche le stock.
4. Crée la page `/produits/[id]` avec `notFound()`.
5. Écris la Server Action `creerProduit` avec validation Zod, gestion de `P2002` et `revalidatePath`, ainsi que le formulaire avec `useActionState`.
6. Ajoute le Route Handler `GET /api/produits` paginé.
7. Crée la base `boutique_test`, le fichier `.env.test` et le script `npm test`.
8. Écris `vendre` dans `lib/ventes.ts` et les deux tests d'intégration (succès et stock insuffisant).
9. Écris `appliquerReduction` avec injection du client et deux tests unitaires.
10. Ajoute `postinstall: prisma generate` et un workflow GitHub Actions avec le service PostgreSQL.

Auto-évaluation : pourquoi ne peut-on pas créer un `new PrismaClient()` à la racine d'un fichier sans précaution en développement ? Quelle différence entre test unitaire avec mock et test d'intégration, et lequel détecte une faute dans un `where` ?

## Erreurs fréquentes

- **Un `new PrismaClient()` par module.** Les connexions s'accumulent : « too many connections ».
- **Importer `prisma` dans un composant client.** Le build échoue ou, pire, des secrets fuient.
- **Faire confiance aux données d'un formulaire.** Valide avec Zod dans l'action.
- **Oublier `prisma generate` au déploiement.** Le client n'est pas initialisé.
- **Lancer les migrations avec l'URL du pooler.** Utilise `directUrl`.
- **Tester contre la base de développement.** Tes données disparaissent au premier `TRUNCATE`.
- **Des tests qui dépendent les uns des autres.** Nettoie la base avant chaque test.
- **Ne tester qu'avec des mocks.** Une contrainte unique manquante passe alors inaperçue.
- **Oublier `await params` dans Next.js 15.** `params` est une promesse.

## Bonnes pratiques

- Une instance unique de `PrismaClient`, exportée depuis `lib/prisma.ts`.
- Garde l'accès aux données dans des fonctions dédiées (`lib/`) plutôt que dans les composants : elles se testent mieux et se réutilisent.
- Valide toutes les entrées et vérifie les droits dans chaque action serveur.
- Utilise `select` pour ne jamais renvoyer au navigateur des champs sensibles.
- Sépare les bases de développement, de test et de production, chacune avec son `.env`.
- Mélange tests unitaires pour la logique et tests d'intégration pour les requêtes.
- Fais tourner migrations et tests dans la CI à chaque Pull Request.
- Active les journaux de requêtes en développement uniquement.

## À retenir

- Dans Next.js, stocke le client sur `globalThis` en développement pour éviter l'accumulation de connexions.
- Les composants serveur, Server Actions et Route Handlers peuvent appeler Prisma ; jamais un composant client.
- Une Server Action suit trois étapes : valider (Zod), écrire (Prisma), invalider le cache (`revalidatePath`).
- En serverless, utilise un pooler pour l'application et `directUrl` pour les migrations ; génère le client avec `postinstall`.
- Les tests d'intégration contre une vraie base PostgreSQL valident les requêtes ; les mocks isolent la logique métier.
- Une base de test dédiée, vidée entre les tests, rend les résultats reproductibles.
- La CI démarre un service PostgreSQL, applique `migrate deploy` puis lance les tests.
