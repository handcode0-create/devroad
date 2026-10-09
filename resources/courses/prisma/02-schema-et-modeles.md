---
title: Schéma et modèles
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Le fichier `schema.prisma` est le cœur de tout projet Prisma. Il décrit tes tables, tes colonnes, leurs contraintes et leurs liens. Tout le reste (le client typé, les migrations, l'autocomplétion) en découle. Bien modéliser tes données dès le départ t'évite des mois de corrections douloureuses : ce chapitre t'apprend à le faire proprement.

À la fin du chapitre, tu seras capable de :

- déclarer des modèles avec les types scalaires de Prisma ;
- rendre un champ optionnel, unique, avec une valeur par défaut ;
- choisir une clé primaire adaptée : entier auto-incrémenté, `cuid` ou `uuid` ;
- définir des **enums** pour des valeurs limitées ;
- ajouter des contraintes composées avec `@@unique`, `@@id` et `@@index` ;
- renommer tables et colonnes avec `@map` et `@@map` ;
- déclarer une première relation entre deux modèles ;
- formater et valider ton schéma avec `prisma format` et `prisma validate`.

Prérequis : le chapitre « Découvrir Prisma » (projet initialisé, base PostgreSQL en route). Prévois deux heures. Tu travailleras sur un petit schéma d'école en ligne : élèves, cours et inscriptions.

## Anatomie d'un modèle

Un `model` décrit une table. Chaque ligne est un champ :

```prisma
model Eleve {
  id        Int      @id @default(autoincrement())
  prenom    String
  nom       String
  email     String   @unique
  age       Int?
  actif     Boolean  @default(true)
  creeLe    DateTime @default(now())
}
```

Chaque champ suit la même structure : **nom**, **type**, puis des **modificateurs** et des **attributs**.

- `?` après le type rend le champ **optionnel** : `age Int?` accepte `null`. Sans `?`, le champ est obligatoire (`NOT NULL` en SQL).
- `[]` après le type désigne une liste (nous y reviendrons).
- `@` introduit un **attribut de champ** : `@id`, `@unique`, `@default(...)`.
- `@@` introduit un **attribut de modèle**, qui concerne la table entière.

> **À retenir** : dans Prisma, tout est obligatoire par défaut. C'est le contraire de beaucoup d'habitudes : tu dois dire explicitement qu'une valeur peut manquer, avec `?`.

## Les types scalaires

Les types de base se traduisent en types PostgreSQL et en types TypeScript :

| Type Prisma | Type PostgreSQL | Type TypeScript |
| --- | --- | --- |
| `String` | `text` | `string` |
| `Int` | `integer` | `number` |
| `BigInt` | `bigint` | `bigint` |
| `Float` | `double precision` | `number` |
| `Decimal` | `decimal(65,30)` | `Decimal` (objet) |
| `Boolean` | `boolean` | `boolean` |
| `DateTime` | `timestamp(3)` | `Date` |
| `Json` | `jsonb` | valeur JSON |
| `Bytes` | `bytea` | `Buffer` |

Deux choix méritent ton attention.

**L'argent.** N'utilise jamais `Float` pour des montants : les nombres à virgule flottante font des erreurs d'arrondi (`0.1 + 0.2` ne vaut pas exactement `0.3`). Deux bonnes solutions : stocker des entiers dans la plus petite unité (en FCFA, il n'y a pas de centimes, donc un `Int` convient très bien), ou utiliser `Decimal` avec une précision précise.

```prisma
model Paiement {
  id        Int      @id @default(autoincrement())
  montantXof Int                          // 25000 = 25 000 FCFA
  taxe      Decimal  @db.Decimal(10, 2)   // 10 chiffres, dont 2 après la virgule
}
```

L'attribut `@db.Decimal(10, 2)` est un **type natif** : il précise le type exact de la colonne PostgreSQL. D'autres exemples : `@db.VarChar(255)` pour limiter la taille d'un texte, `@db.Uuid` pour un vrai type UUID.

**Les dates.** `DateTime` est stocké avec le fuseau UTC. Affiche-le dans le fuseau local côté interface, mais stocke toujours en UTC. `@default(now())` remplit la date à l'insertion, et `@updatedAt` met à jour automatiquement le champ à chaque modification.

```prisma
model Cours {
  id          Int      @id @default(autoincrement())
  titre       String   @db.VarChar(150)
  description String?
  creeLe      DateTime @default(now())
  modifieLe   DateTime @updatedAt
}
```

:::quiz
Comment déclares-tu en Prisma un champ `age` qui peut ne pas avoir de valeur ?
- [ ] `age Int null`
- [ ] `age Int @optional`
- [x] `age Int?`
- [ ] `age Int[]`
> Le point d'interrogation après le type rend le champ optionnel (`NULL` autorisé en base). `[]` désigne une liste, pas une valeur optionnelle.
:::

## Les clés primaires

Chaque modèle a besoin d'un identifiant unique, déclaré avec `@id`. Trois stratégies courantes :

```prisma
model A {
  id Int @id @default(autoincrement())      // 1, 2, 3, ...
}

model B {
  id String @id @default(cuid())            // "clx9f2k0a0000..."
}

model C {
  id String @id @default(uuid())            // "3f1c0c1e-...-4e2b"
  // ou, avec un vrai type UUID PostgreSQL : @db.Uuid
}
```

| Stratégie | Avantages | Inconvénients |
| --- | --- | --- |
| `autoincrement()` | Simple, léger, lisible | Prévisible, expose le volume de données dans les URL |
| `cuid()` | Non prévisible, triable approximativement | Texte plus long |
| `uuid()` | Standard universel | Texte long, moins lisible |

Si tes identifiants apparaissent dans des URL publiques (`/commande/12`), préfère un `cuid` ou un `uuid` : un utilisateur curieux ne pourra pas deviner les numéros voisins.

Pour une table de liaison, la clé peut être **composée** de plusieurs champs avec `@@id([champA, champB])`. Tu verras un exemple dans le dernier modèle de ce chapitre.

## Valeurs par défaut et unicité

L'attribut `@default(...)` fournit une valeur quand tu n'en donnes pas à la création :

```prisma
model Inscription {
  id        Int      @id @default(autoincrement())
  statut    String   @default("EN_ATTENTE")
  note      Int      @default(0)
  creeLe    DateTime @default(now())
}
```

Les fonctions disponibles incluent `now()`, `autoincrement()`, `cuid()`, `uuid()` et `dbgenerated()` pour du SQL personnalisé.

L'attribut `@unique` interdit deux lignes avec la même valeur. C'est indispensable pour un e-mail ou un pseudo. Pour garantir l'unicité d'une **combinaison** de champs, utilise `@@unique` :

```prisma
model Inscription {
  id       Int @id @default(autoincrement())
  eleveId  Int
  coursId  Int

  @@unique([eleveId, coursId])   // un élève ne s'inscrit qu'une fois à un cours
}
```

> **Astuce** : une règle métier qui doit **toujours** être vraie (pas deux comptes avec le même e-mail, pas deux inscriptions identiques) doit être garantie par la base avec une contrainte, pas seulement par le code. Le code peut contenir un bug, la contrainte non.

## Les enums

Quand un champ ne peut prendre qu'un nombre limité de valeurs (un statut, un rôle), utilise un **enum**. PostgreSQL en possède de natifs, et Prisma les utilise :

```prisma
enum Role {
  ELEVE
  FORMATEUR
  ADMIN
}

enum StatutInscription {
  EN_ATTENTE
  VALIDEE
  ANNULEE
}

model Utilisateur {
  id    Int    @id @default(autoincrement())
  email String @unique
  role  Role   @default(ELEVE)
}
```

Côté TypeScript, Prisma génère un type correspondant que tu peux importer :

```ts
import { Role } from '@prisma/client';

const formateurs = await prisma.utilisateur.findMany({
  where: { role: Role.FORMATEUR },
});
```

Si tu écris `role: 'FORMTEUR'`, TypeScript signale la faute. Avec un simple `String`, rien ne t'avertirait. Les valeurs sont écrites en MAJUSCULES par convention.

:::quiz
Pourquoi préférer un enum à un champ `String` pour un statut de commande ?
- [ ] Parce que les enums sont plus rapides à écrire
- [ ] Parce que les `String` ne peuvent pas être stockés en base
- [x] Parce que seules les valeurs prévues sont acceptées, en base et dans les types TypeScript
- [ ] Parce que les enums permettent de stocker des objets
> Un enum limite les valeurs possibles. TypeScript refuse une valeur inconnue et PostgreSQL aussi, ce qui évite les statuts mal orthographiés.
:::

## Index, noms de tables et de colonnes

### Les index

Un **index** accélère les recherches sur un champ, comme l'index d'un livre. Sans index, PostgreSQL doit parcourir toutes les lignes. Les clés primaires et les champs `@unique` sont déjà indexés. Pour le reste :

```prisma
model Commande {
  id         Int      @id @default(autoincrement())
  clientId   Int
  statut     String
  creeLe     DateTime @default(now())

  @@index([clientId])
  @@index([statut, creeLe])
}
```

Ajoute un index sur les champs que tu utilises souvent dans un `where` ou un `orderBy`, notamment les clés étrangères. N'en mets pas partout : chaque index ralentit légèrement les écritures et occupe de l'espace.

### Renommer avec @map et @@map

Par défaut, la table porte le nom du modèle (`Utilisateur`) et les colonnes celui des champs. Beaucoup d'équipes préfèrent du `snake_case` en base tout en gardant un code TypeScript en camelCase :

```prisma
model Utilisateur {
  id        Int      @id @default(autoincrement())
  nomComplet String  @map("nom_complet")
  creeLe    DateTime @default(now()) @map("cree_le")

  @@map("utilisateurs")
}
```

Dans le code, tu écris toujours `nomComplet` et `prisma.utilisateur`. En base, la table s'appelle `utilisateurs` et la colonne `nom_complet`. Décide de cette convention **dès le début** : la changer ensuite demande une migration.

## Première relation

Les relations sont détaillées dans le chapitre 5, mais il te faut une base pour modéliser. Un cours a plusieurs inscriptions ; chaque inscription appartient à un élève et à un cours. Voici le schéma complet de l'école :

```prisma
model Eleve {
  id           Int           @id @default(autoincrement())
  prenom       String
  nom          String
  email        String        @unique
  inscriptions Inscription[]
}

model Cours {
  id           Int           @id @default(autoincrement())
  titre        String
  inscriptions Inscription[]
}

model Inscription {
  id       Int               @id @default(autoincrement())
  statut   StatutInscription @default(EN_ATTENTE)
  eleveId  Int
  coursId  Int
  eleve    Eleve             @relation(fields: [eleveId], references: [id])
  cours    Cours             @relation(fields: [coursId], references: [id])

  @@unique([eleveId, coursId])
  @@index([coursId])
}
```

Lis attentivement `Inscription` :

- `eleveId` est la **clé étrangère**, une vraie colonne en base ;
- `eleve` est un **champ de relation** : il n'existe pas en base, il sert à naviguer dans le code ;
- `@relation(fields: [eleveId], references: [id])` dit : « mon `eleveId` pointe vers l'`id` de `Eleve` » ;
- `inscriptions Inscription[]` côté `Eleve` est le **côté inverse** : la liste des inscriptions de cet élève.

Les deux côtés de la relation sont obligatoires dans le schéma : Prisma refuse un schéma où un seul côté est déclaré.

## Formater et valider

Deux commandes t'aident à garder un schéma propre :

```bash
npx prisma format     # aligne et met en forme le fichier
npx prisma validate   # vérifie la syntaxe et la cohérence
```

`format` ajoute même automatiquement le côté inverse d'une relation que tu as oublié. Installe aussi l'extension VS Code **Prisma** : coloration, autocomplétion et formatage à l'enregistrement.

Si tu travailles sur une base existante, `npx prisma db pull` fait le chemin inverse : il lit la base et écrit le schéma correspondant. C'est utile pour reprendre un projet existant, mais pour un nouveau projet, pars toujours du schéma.

## Atelier guidé : le schéma de l'école en ligne

Compte quarante-cinq minutes. Reprends ton projet du chapitre 1.

1. Remplace le contenu de tes modèles par ceux de l'école : `Eleve`, `Cours`, `Inscription` et l'enum `StatutInscription`.
2. Ajoute à `Cours` les champs `description String?`, `prixXof Int @default(0)`, `creeLe` et `modifieLe` (avec `@updatedAt`).
3. Ajoute un enum `Role` et un champ `role Role @default(ELEVE)` sur `Eleve`.
4. Lance `npx prisma format` puis `npx prisma validate` et corrige les éventuelles erreurs.
5. Ajoute `@map` et `@@map` pour que les tables s'appellent `eleves`, `cours` et `inscriptions` en snake_case.
6. Ajoute un `@@index` sur `coursId` dans `Inscription`.
7. Exécute `npx prisma migrate dev --name ecole` puis ouvre le SQL généré pour repérer `UNIQUE INDEX`, `FOREIGN KEY` et le type enum.
8. Dans un script, crée un élève, un cours, puis une inscription avec `eleveId` et `coursId`. Tente de créer deux fois la même inscription et lis l'erreur obtenue.

Auto-évaluation : pourquoi la clé étrangère `eleveId` existe-t-elle en base alors que le champ `eleve` n'y existe pas ? Quelle différence entre `@unique` et `@@unique` ?

## Erreurs fréquentes

- **Oublier le côté inverse d'une relation.** Prisma affiche une erreur de validation ; `prisma format` peut la corriger.
- **Utiliser `Float` pour de l'argent.** Les arrondis faussent les totaux. Choisis `Int` ou `Decimal`.
- **Oublier `?` sur un champ qui peut être vide.** La création échoue ensuite avec une erreur « Argument missing ».
- **Changer la convention de nommage en cours de route.** Un `@@map` ajouté tardivement renomme la table : prévois-le au début.
- **Ne pas indexer les clés étrangères.** Sur de gros volumes, les jointures deviennent lentes.
- **Confondre `@@unique([a, b])` et deux `@unique` séparés.** Le premier interdit la combinaison, les seconds interdisent chaque valeur individuellement.

## Bonnes pratiques

- Un modèle au singulier en PascalCase, des champs en camelCase, des enums en MAJUSCULES.
- Ajoute `creeLe` et `modifieLe` (avec `@updatedAt`) sur chaque modèle important : tu seras content de les avoir.
- Garantis les règles métier essentielles avec `@unique`, `@@unique` et des enums plutôt qu'avec du code seul.
- Choisis tes identifiants selon leur exposition : `cuid` ou `uuid` s'ils sont visibles dans les URL.
- Lance `prisma format` et `prisma validate` avant chaque migration.
- Commente les champs ambigus (unité d'un montant, signification d'un statut) avec `//`.

## À retenir

- Un `model` est une table ; chaque champ a un nom, un type et des attributs.
- Un champ est obligatoire par défaut ; `?` le rend optionnel, `[]` en fait une liste ou un côté inverse de relation.
- Les clés primaires se déclarent avec `@id` et `autoincrement()`, `cuid()` ou `uuid()`.
- `@unique`, `@@unique`, `@@index` et les enums mettent les règles et les performances dans la base.
- `@map` et `@@map` séparent les noms de la base de ceux du code.
- Une relation s'écrit avec une clé étrangère, un champ de relation et son côté inverse.
