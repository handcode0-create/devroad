---
title: Projet final Prisma
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as appris à modéliser, migrer, interroger et tester. Place maintenant à la pratique réelle : tu vas construire de bout en bout **StockMaquis**, une application de gestion de stock et de ventes pour un maquis ou une petite boutique, avec Next.js, PostgreSQL et Prisma. C'est le type de projet qu'un client de Côte d'Ivoire peut te commander demain : produits, approvisionnements, ventes encaissées en Mobile Money, historique fiable et rapports.

À la fin du projet, tu auras :

- conçu un schéma relationnel complet avec relations, enums, index et contraintes ;
- géré son évolution avec des migrations versionnées et un seed idempotent ;
- écrit une couche de services transactionnelle (ventes, approvisionnements, ajustements) ;
- construit une interface Next.js lisant et écrivant via Prisma ;
- protégé l'intégrité du stock avec des transactions et des contraintes ;
- écrit des tests d'intégration sur les règles métier critiques ;
- préparé le déploiement avec les bonnes commandes et variables d'environnement.

Prérequis : les chapitres 1 à 6 de ce cours, des bases de Next.js (App Router) et de Git. Prévois six heures, réparties sur une ou deux sessions. Avance par étapes, commit après chaque étape qui fonctionne.

## Le cahier des charges

### Le contexte

Aya gère un maquis à Abidjan. Elle note ses stocks et ses ventes dans un cahier : il y a des erreurs, des pertes qu'elle ne sait pas expliquer, et aucune idée de son bénéfice. Elle veut une application où chaque entrée et sortie de marchandise est tracée.

### Les fonctionnalités

| Fonctionnalité | Description |
| --- | --- |
| Catalogue | Produits classés en catégories, avec prix de vente et seuil d'alerte de stock |
| Approvisionnement | Enregistrer l'achat de marchandises à un fournisseur : le stock augmente |
| Vente | Enregistrer une vente de plusieurs produits ; le stock diminue ; le paiement est tracé (espèces, Wave, Orange Money, MTN MoMo) |
| Mouvements de stock | Chaque variation (vente, achat, ajustement, perte) est conservée dans un journal |
| Tableau de bord | Chiffre d'affaires du jour, produits en rupture ou sous le seuil, meilleurs produits |
| Rôles | Gérante (tout faire) et serveur (ventes uniquement) |

### Les règles métier

1. Le stock d'un produit ne peut **jamais être négatif**.
2. Une vente est **atomique** : toutes ses lignes et ses mouvements de stock sont enregistrés ensemble, ou rien du tout.
3. Le prix d'une ligne de vente est **figé** à la date de la vente ; modifier le prix d'un produit ne change pas l'historique.
4. Un produit qui a des ventes n'est jamais supprimé, il est **désactivé**.
5. Le stock est toujours égal à la somme de ses mouvements : le journal est la preuve.
6. Un serveur ne peut ni modifier un prix ni faire un ajustement de stock.
7. Les montants sont en FCFA, stockés en entiers.

### Le périmètre technique

- Next.js 15 (App Router, TypeScript), Prisma 6, PostgreSQL 16.
- Validation avec Zod, tests avec Vitest.
- Authentification : à ton choix (Auth.js, ou un simple jeton de session). Le cours se concentre sur la donnée ; si tu veux gagner du temps, simule l'utilisateur connecté par un cookie.

## Étape de conception : le schéma

Prends le temps de réfléchir avant d'écrire. Le schéma est la partie la plus difficile à corriger plus tard.

### Les entités

Cinq groupes de données se dessinent :

- **Utilisateur** (rôle gérante ou serveur) ;
- **Catégorie**, **Produit** et **Fournisseur** (le catalogue) ;
- **Approvisionnement** et ses lignes (les entrées de stock) ;
- **Vente** et ses lignes, avec un **Paiement** (les sorties) ;
- **MouvementStock**, le journal qui relie le tout.

### Le schéma complet

```prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

enum Role {
  GERANTE
  SERVEUR
}

enum TypeMouvement {
  ACHAT
  VENTE
  AJUSTEMENT
  PERTE
}

enum ModePaiement {
  ESPECES
  WAVE
  ORANGE_MONEY
  MTN_MOMO
}

model Utilisateur {
  id        String   @id @default(cuid())
  email     String   @unique
  nom       String
  role      Role     @default(SERVEUR)
  creeLe    DateTime @default(now())
  ventes    Vente[]
  mouvements MouvementStock[]
}

model Categorie {
  id       Int       @id @default(autoincrement())
  nom      String    @unique
  produits Produit[]
}

model Fournisseur {
  id               Int                @id @default(autoincrement())
  nom              String
  telephone        String?
  approvisionnements Approvisionnement[]
}

model Produit {
  id            Int      @id @default(autoincrement())
  nom           String
  reference     String   @unique
  prixVenteXof  Int
  stock         Int      @default(0)
  seuilAlerte   Int      @default(5)
  actif         Boolean  @default(true)
  categorieId   Int
  categorie     Categorie @relation(fields: [categorieId], references: [id], onDelete: Restrict)
  creeLe        DateTime @default(now())
  modifieLe     DateTime @updatedAt
  lignesVente   LigneVente[]
  lignesAppro   LigneApprovisionnement[]
  mouvements    MouvementStock[]

  @@index([categorieId])
  @@index([actif, stock])
}

model Approvisionnement {
  id            Int      @id @default(autoincrement())
  fournisseurId Int
  fournisseur   Fournisseur @relation(fields: [fournisseurId], references: [id], onDelete: Restrict)
  creeLe        DateTime @default(now())
  lignes        LigneApprovisionnement[]

  @@index([fournisseurId])
}

model LigneApprovisionnement {
  approvisionnementId Int
  produitId           Int
  quantite            Int
  coutUnitaireXof     Int
  approvisionnement   Approvisionnement @relation(fields: [approvisionnementId], references: [id], onDelete: Cascade)
  produit             Produit @relation(fields: [produitId], references: [id], onDelete: Restrict)

  @@id([approvisionnementId, produitId])
}

model Vente {
  id          Int          @id @default(autoincrement())
  numero      String       @unique
  totalXof    Int
  mode        ModePaiement
  referencePaiement String?
  serveurId   String
  serveur     Utilisateur  @relation(fields: [serveurId], references: [id], onDelete: Restrict)
  creeLe      DateTime     @default(now())
  lignes      LigneVente[]

  @@index([creeLe])
  @@index([serveurId])
}

model LigneVente {
  venteId        Int
  produitId      Int
  quantite       Int
  prixUnitaireXof Int
  vente          Vente   @relation(fields: [venteId], references: [id], onDelete: Cascade)
  produit        Produit @relation(fields: [produitId], references: [id], onDelete: Restrict)

  @@id([venteId, produitId])
}

model MouvementStock {
  id        Int           @id @default(autoincrement())
  produitId Int
  type      TypeMouvement
  variation Int           // positif = entrée, négatif = sortie
  motif     String?
  auteurId  String
  produit   Produit       @relation(fields: [produitId], references: [id], onDelete: Restrict)
  auteur    Utilisateur   @relation(fields: [auteurId], references: [id], onDelete: Restrict)
  creeLe    DateTime      @default(now())

  @@index([produitId, creeLe])
}
```

Prends deux minutes pour justifier chaque choix : pourquoi `Restrict` presque partout ? Pourquoi `prixUnitaireXof` dans `LigneVente` ? Pourquoi un `cuid` pour l'utilisateur mais un entier pour les produits ?

### Une contrainte que Prisma ne sait pas exprimer

La règle « le stock n'est jamais négatif » est trop importante pour reposer sur le seul code. PostgreSQL sait la garantir avec une contrainte `CHECK`, que Prisma ne déclare pas dans le schéma. Tu l'ajoutes en éditant la migration avec `--create-only` :

```sql
ALTER TABLE "Produit" ADD CONSTRAINT "produit_stock_positif" CHECK ("stock" >= 0);
ALTER TABLE "LigneVente" ADD CONSTRAINT "ligne_vente_quantite_positive" CHECK ("quantite" > 0);
```

Désormais, même un bug dans ton code ne pourra pas rendre le stock négatif : la base refusera l'écriture avec une erreur.

:::quiz
Pourquoi stocker `prixUnitaireXof` dans `LigneVente` plutôt que de lire le prix du produit ?
- [ ] Pour économiser de l'espace disque
- [ ] Parce que Prisma l'exige pour les relations
- [x] Pour que l'historique des ventes reste exact même si le prix du produit change
- [ ] Pour éviter d'utiliser un index
> Le prix d'un produit évolue. En copiant le prix dans la ligne au moment de la vente, les totaux passés ne changent jamais rétroactivement.
:::

## Étape 1 : mise en place et migrations

Crée le projet, installe les dépendances, puis lance la première migration en deux temps pour y glisser la contrainte `CHECK` :

```bash
npx create-next-app@latest stockmaquis --typescript --app --eslint
cd stockmaquis
npm install @prisma/client zod
npm install prisma vitest tsx dotenv-cli --save-dev
npx prisma init --datasource-provider postgresql
npx prisma migrate dev --create-only --name init
```

Ouvre le fichier `migration.sql` généré, ajoute les contraintes `CHECK` en fin de fichier, puis applique-le :

```bash
npx prisma migrate dev
```

Crée aussi `lib/prisma.ts` avec l'instance globale (chapitre 6) et vérifie dans Prisma Studio que toutes les tables existent.

## Étape 2 : le seed

Un seed réaliste te permet de développer l'interface sans tout saisir à la main. Il doit être **idempotent** :

```ts
// prisma/seed.ts
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  const gerante = await prisma.utilisateur.upsert({
    where: { email: 'aya@stockmaquis.ci' },
    update: {},
    create: { email: 'aya@stockmaquis.ci', nom: 'Aya Koné', role: 'GERANTE' },
  });

  await prisma.utilisateur.upsert({
    where: { email: 'serveur@stockmaquis.ci' },
    update: {},
    create: { email: 'serveur@stockmaquis.ci', nom: 'Moussa', role: 'SERVEUR' },
  });

  const boissons = await prisma.categorie.upsert({
    where: { nom: 'Boissons' },
    update: {},
    create: { nom: 'Boissons' },
  });

  const produits = [
    { reference: 'BIERE-33', nom: 'Bière 33 cl', prixVenteXof: 1000, stock: 48 },
    { reference: 'COCA-50', nom: 'Coca-Cola 50 cl', prixVenteXof: 500, stock: 36 },
    { reference: 'EAU-150', nom: 'Eau minérale 1,5 L', prixVenteXof: 500, stock: 24 },
  ];

  for (const p of produits) {
    const produit = await prisma.produit.upsert({
      where: { reference: p.reference },
      update: {},
      create: { ...p, categorieId: boissons.id },
    });
    // Le journal doit justifier le stock initial
    const deja = await prisma.mouvementStock.count({ where: { produitId: produit.id } });
    if (deja === 0) {
      await prisma.mouvementStock.create({
        data: {
          produitId: produit.id,
          type: 'AJUSTEMENT',
          variation: p.stock,
          motif: 'Stock initial',
          auteurId: gerante.id,
        },
      });
    }
  }
}

main().finally(() => prisma.$disconnect());
```

Déclare la commande dans `package.json` (`"prisma": { "seed": "tsx prisma/seed.ts" }`) puis exécute `npx prisma db seed`. Lance-la deux fois : le nombre de lignes ne doit pas changer.

## Étape 3 : la couche de services

C'est le cœur du projet. Toute écriture passe par des fonctions dédiées dans `lib/services/`, jamais directement depuis les composants. Chaque fonction applique une règle métier et se teste séparément.

### La vente

```ts
// lib/services/ventes.ts
import { prisma } from '@/lib/prisma';
import type { ModePaiement } from '@prisma/client';

export type LigneDemandee = { produitId: number; quantite: number };

export class ErreurMetier extends Error {}

export async function enregistrerVente(entree: {
  serveurId: string;
  mode: ModePaiement;
  referencePaiement?: string;
  lignes: LigneDemandee[];
}) {
  if (entree.lignes.length === 0) {
    throw new ErreurMetier('Une vente doit contenir au moins un produit');
  }
  if (entree.mode !== 'ESPECES' && !entree.referencePaiement) {
    throw new ErreurMetier('La référence de transaction Mobile Money est obligatoire');
  }

  return prisma.$transaction(async (tx) => {
    const ids = entree.lignes.map((l) => l.produitId);
    const produits = await tx.produit.findMany({ where: { id: { in: ids }, actif: true } });

    if (produits.length !== new Set(ids).size) {
      throw new ErreurMetier('Un produit est introuvable ou désactivé');
    }

    let total = 0;
    const lignesVente = entree.lignes.map((l) => {
      const produit = produits.find((p) => p.id === l.produitId)!;
      total += produit.prixVenteXof * l.quantite;
      return { produitId: l.produitId, quantite: l.quantite, prixUnitaireXof: produit.prixVenteXof };
    });

    for (const l of entree.lignes) {
      const { count } = await tx.produit.updateMany({
        where: { id: l.produitId, stock: { gte: l.quantite } },
        data: { stock: { decrement: l.quantite } },
      });
      if (count === 0) {
        throw new ErreurMetier(`Stock insuffisant pour le produit ${l.produitId}`);
      }
    }

    const vente = await tx.vente.create({
      data: {
        numero: `V-${Date.now()}`,
        totalXof: total,
        mode: entree.mode,
        referencePaiement: entree.referencePaiement,
        serveurId: entree.serveurId,
        lignes: { create: lignesVente },
      },
    });

    await tx.mouvementStock.createMany({
      data: entree.lignes.map((l) => ({
        produitId: l.produitId,
        type: 'VENTE' as const,
        variation: -l.quantite,
        motif: vente.numero,
        auteurId: entree.serveurId,
      })),
    });

    return vente;
  });
}
```

Observe le point clé : `updateMany` avec `stock: { gte: quantite }` et `decrement` vérifie **et** décrémente en une seule instruction atomique. Si deux serveurs vendent le dernier produit en même temps, un seul réussit : l'autre obtient `count === 0`. C'est ce qu'on appelle éviter une *race condition*.

> **Attention** : une version naïve (lire le stock, comparer, écrire) laisse passer deux ventes simultanées du dernier article. La condition dans le `where` de l'écriture est ce qui protège réellement le stock.

### L'approvisionnement et l'ajustement

L'approvisionnement suit le même schéma : une transaction crée l'approvisionnement et ses lignes, applique `increment` sur chaque produit, et ajoute un mouvement `ACHAT` positif. L'ajustement, réservé à la gérante, crée un mouvement `AJUSTEMENT` ou `PERTE` avec un motif obligatoire :

```ts
// lib/services/stock.ts
export async function ajusterStock(entree: {
  produitId: number;
  variation: number;
  type: 'AJUSTEMENT' | 'PERTE';
  motif: string;
  auteur: { id: string; role: 'GERANTE' | 'SERVEUR' };
}) {
  if (entree.auteur.role !== 'GERANTE') {
    throw new ErreurMetier('Action réservée à la gérante');
  }
  if (entree.motif.trim().length < 3) {
    throw new ErreurMetier('Un motif est obligatoire');
  }
  return prisma.$transaction([
    prisma.produit.update({
      where: { id: entree.produitId },
      data: { stock: { increment: entree.variation } },
    }),
    prisma.mouvementStock.create({
      data: {
        produitId: entree.produitId,
        type: entree.type,
        variation: entree.variation,
        motif: entree.motif,
        auteurId: entree.auteur.id,
      },
    }),
  ]);
}
```

Si l'ajustement rendait le stock négatif, la contrainte `CHECK` de la base ferait échouer la transaction entière.

:::quiz
Pourquoi vérifier le stock dans le `where` de `updateMany` plutôt que dans un `if` après un `findUnique` ?
- [ ] Parce que `findUnique` est lent
- [x] Parce que la vérification et la modification forment une seule instruction atomique, sans fenêtre où une autre vente pourrait passer
- [ ] Parce que `if` n'existe pas dans une transaction
- [ ] Parce que Prisma interdit de lire avant d'écrire
> Entre la lecture et l'écriture, une autre requête peut modifier le stock. Mettre la condition dans l'écriture elle-même élimine cette fenêtre.
:::

## Étape 4 : requêtes du tableau de bord

Les rapports mettent en œuvre l'agrégation et les relations vues aux chapitres 4 et 5 :

```ts
// lib/services/rapports.ts
import { prisma } from '@/lib/prisma';

export async function chiffreAffairesDuJour() {
  const debut = new Date();
  debut.setHours(0, 0, 0, 0);

  const resultat = await prisma.vente.aggregate({
    where: { creeLe: { gte: debut } },
    _sum: { totalXof: true },
    _count: { _all: true },
  });
  return { total: resultat._sum.totalXof ?? 0, nombreVentes: resultat._count._all };
}

export async function produitsSousLeSeuil() {
  // Comparaison entre deux colonnes : SQL brut paramétré
  return prisma.$queryRaw<{ id: number; nom: string; stock: number; seuilAlerte: number }[]>`
    SELECT "id", "nom", "stock", "seuilAlerte"
    FROM "Produit"
    WHERE "actif" = true AND "stock" <= "seuilAlerte"
    ORDER BY "stock" ASC
  `;
}

export async function meilleursProduits(limite = 5) {
  const groupes = await prisma.ligneVente.groupBy({
    by: ['produitId'],
    _sum: { quantite: true },
    orderBy: { _sum: { quantite: 'desc' } },
    take: limite,
  });
  const produits = await prisma.produit.findMany({
    where: { id: { in: groupes.map((g) => g.produitId) } },
    select: { id: true, nom: true },
  });
  return groupes.map((g) => ({
    nom: produits.find((p) => p.id === g.produitId)?.nom ?? 'Inconnu',
    quantite: g._sum.quantite ?? 0,
  }));
}
```

`produitsSousLeSeuil` utilise le SQL brut parce que Prisma ne compare pas facilement deux colonnes dans un `where`. `meilleursProduits` évite un N+1 : un `groupBy`, puis **un seul** `findMany` avec `in` pour récupérer les noms.

## Étape 5 : l'interface

Construis les pages suivantes, toutes en composants serveur, avec des Server Actions pour les écritures :

1. `/` : tableau de bord (chiffre d'affaires, alertes de stock, meilleurs produits).
2. `/produits` : liste paginée avec recherche par nom et filtre par catégorie ; formulaire de création réservé à la gérante.
3. `/ventes/nouvelle` : saisie d'une vente (choix des produits, quantités, mode de paiement, référence Mobile Money).
4. `/ventes` : historique paginé avec le détail de chaque vente (`include` des lignes).
5. `/stock/mouvements` : journal des mouvements, filtrable par produit et par type.

Chaque Server Action suit le même gabarit : vérifier l'utilisateur et son rôle, valider avec Zod, appeler le service, traduire `ErreurMetier` et les codes Prisma en messages clairs, puis `revalidatePath`. Ne mets **aucune** requête Prisma dans les composants de présentation autres que de la simple lecture.

## Étape 6 : tests

Crée la base `stockmaquis_test` et le script `npm test` (voir chapitre 6). Écris au minimum ces tests d'intégration :

1. une vente valide décrémente le stock, crée la vente, ses lignes et un mouvement par ligne ;
2. une vente avec un stock insuffisant échoue et **ne laisse aucune trace** (ni vente, ni mouvement, ni stock modifié) ;
3. deux ventes lancées en parallèle sur le dernier article : une seule réussit (`Promise.allSettled`) ;
4. un serveur ne peut pas appeler `ajusterStock` ;
5. le stock d'un produit égale la somme de ses mouvements après une série d'opérations ;
6. une tentative d'écrire un stock négatif directement en base est refusée par la contrainte `CHECK`.

Voici le test de concurrence, le plus instructif :

```ts
it('une seule vente réussit pour le dernier article', async () => {
  // stock initial du produit 1 : 1
  const resultats = await Promise.allSettled([
    enregistrerVente({ serveurId: 'u1', mode: 'ESPECES', lignes: [{ produitId: 1, quantite: 1 }] }),
    enregistrerVente({ serveurId: 'u1', mode: 'ESPECES', lignes: [{ produitId: 1, quantite: 1 }] }),
  ]);

  const reussies = resultats.filter((r) => r.status === 'fulfilled');
  expect(reussies).toHaveLength(1);
  const produit = await prisma.produit.findUniqueOrThrow({ where: { id: 1 } });
  expect(produit.stock).toBe(0);
});
```

## Étape 7 : déploiement

Prépare la mise en production :

```bash
# Variables d'environnement à définir sur la plateforme
DATABASE_URL="postgresql://...pooler..."
DIRECT_URL="postgresql://...direct..."
```

1. Ajoute `directUrl` au bloc `datasource` si ton hébergeur utilise un pooler.
2. Ajoute `"postinstall": "prisma generate"` dans `package.json`.
3. Lance `npx prisma migrate deploy` depuis la CI ou une étape de déploiement dédiée.
4. Exécute le seed uniquement pour créer le premier compte gérante, jamais avec les données de démonstration.
5. Active la sauvegarde automatique de la base chez l'hébergeur et teste une restauration.

## Atelier guidé : plan de réalisation

Voici l'ordre conseillé, avec un temps indicatif. Coche chaque étape avant de passer à la suivante.

1. (20 min) Crée le projet Next.js, branche PostgreSQL, vérifie la connexion.
2. (40 min) Écris le schéma complet, lance `format` et `validate`, puis crée la migration initiale avec `--create-only` et ajoute les contraintes `CHECK`.
3. (20 min) Écris le seed idempotent et exécute-le deux fois.
4. (60 min) Écris les services `enregistrerVente`, `enregistrerApprovisionnement` et `ajusterStock` avec leurs erreurs métier.
5. (60 min) Écris les tests d'intégration des services, y compris le test de concurrence. Les tests doivent passer avant l'interface.
6. (20 min) Écris les requêtes de rapport.
7. (90 min) Construis les cinq pages avec Server Actions et validation Zod.
8. (30 min) Gère les rôles : masque les actions interdites dans l'interface **et** refuse-les dans les actions.
9. (20 min) Active `log: ['query']`, parcours chaque page et vérifie l'absence de N+1.
10. (20 min) Prépare le déploiement : scripts, variables, `migrate deploy`.

## Checklist d'acceptation

Ton projet est terminé quand tu peux cocher chaque ligne :

- [ ] `npx prisma validate` passe sans erreur et le schéma est formaté.
- [ ] Les migrations rejouées sur une base vide (`migrate reset`) reconstruisent tout, seed compris.
- [ ] Le seed peut être relancé sans créer de doublons.
- [ ] Une contrainte `CHECK` empêche un stock négatif au niveau de la base.
- [ ] Une vente enregistre sa vente, ses lignes, ses mouvements et le stock dans **une seule transaction**.
- [ ] Une vente en échec ne laisse aucune trace en base.
- [ ] Deux ventes simultanées du dernier article : une seule réussit.
- [ ] Le prix de chaque ligne est figé ; modifier un prix ne change pas l'historique.
- [ ] Le stock de chaque produit égale la somme de ses mouvements.
- [ ] Un serveur ne peut ni ajuster le stock ni modifier un prix, côté interface et côté serveur.
- [ ] Les listes sont paginées et aucune page ne présente de problème N+1.
- [ ] Les erreurs `P2002` et les erreurs métier sont traduites en messages lisibles.
- [ ] Aucun composant client n'importe Prisma.
- [ ] Les tests d'intégration passent sur une base dédiée, en local et dans la CI.
- [ ] Le fichier `.env` n'est pas committé et `.env.example` documente les variables.

Pour t'auto-évaluer, présente ton projet à voix haute comme face à un client : explique comment tu garantis que le stock ne ment jamais, et ce qui se passerait si deux serveurs encaissaient au même instant.

## Erreurs fréquentes

- **Mettre la logique métier dans les composants.** Elle devient impossible à tester et se duplique. Centralise-la dans des services.
- **Lire le stock puis l'écrire.** Une vente simultanée passe entre les deux : mets la condition dans l'écriture.
- **Oublier `Restrict` sur les relations historiques.** Supprimer un produit efface alors des ventes passées.
- **Calculer le total côté navigateur.** Recalcule-le toujours côté serveur à partir des prix en base.
- **Se fier uniquement au code pour les règles critiques.** Double la protection avec des contraintes `CHECK`, `UNIQUE` et des clés étrangères.
- **Tester avec des mocks seulement.** Les transactions et les contraintes ne se vérifient que sur une vraie base.
- **Lancer le seed de démonstration en production.** Tu crées de faux produits et de faux comptes.
- **Ne pas sauvegarder la base avant une migration risquée.** Un `DROP COLUMN` est irréversible.

## Bonnes pratiques

- Écris le schéma et les règles métier **avant** l'interface : les pages deviennent presque mécaniques.
- Fais de la base ta dernière ligne de défense avec des contraintes, des index et des relations strictes.
- Journalise chaque variation de stock : un journal se rejoue, une valeur seule ne s'explique pas.
- Garde des services petits, nommés par action métier (`enregistrerVente`), avec des erreurs explicites.
- Valide les entrées avec Zod à la frontière, et fais confiance aux types à l'intérieur.
- Commit après chaque étape qui fonctionne, avec la migration correspondante.
- Documente dans un `README` : installation, variables d'environnement, commandes de migration, de seed et de test.

## À retenir

- Un bon projet Prisma commence par un schéma réfléchi : relations, enums, index et `onDelete` choisis selon le métier.
- Les règles critiques se garantissent à plusieurs niveaux : transaction, condition atomique dans le `where`, contrainte `CHECK` en base.
- Figer les prix dans les lignes et journaliser les mouvements rend l'historique fiable et explicable.
- Une couche de services transactionnelle isole la logique métier et la rend testable.
- Les tests d'intégration sur une vraie base, dont un test de concurrence, prouvent que les règles tiennent.
- Le déploiement repose sur `postinstall: prisma generate`, `migrate deploy`, des URL séparées pour le pooler et une sauvegarde testée.
- Tu sais maintenant livrer une application de données complète, de la conception au déploiement.
