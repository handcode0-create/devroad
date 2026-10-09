---
title: Migrations
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Ton schéma évolue : tu ajoutes une colonne, tu renommes un champ, tu crées une table. Mais la base de données, elle, contient déjà des données qu'il ne faut surtout pas perdre. Les **migrations** sont la réponse : un historique versionné, rejouable et partagé par toute l'équipe, qui fait passer la base d'un état à l'autre en toute sécurité. Bien les comprendre, c'est la différence entre un déploiement serein et une catastrophe un vendredi soir.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est une migration et pourquoi on les versionne ;
- créer et appliquer une migration avec `prisma migrate dev` ;
- lire un fichier de migration SQL et la table `_prisma_migrations` ;
- déployer en production avec `prisma migrate deploy` ;
- ajouter une colonne obligatoire à une table qui contient déjà des données ;
- modifier une migration avant de l'appliquer (`--create-only`) ;
- distinguer `migrate dev`, `migrate deploy`, `migrate reset` et `db push` ;
- résoudre un problème de dérive (*drift*) entre le schéma et la base.

Prérequis : les chapitres « Découvrir Prisma » et « Schéma et modèles », ainsi qu'une base PostgreSQL locale. Prévois deux heures. Utilise une base de test : tu vas en casser et en réinitialiser plusieurs fois.

## Pourquoi des migrations ?

Imagine que tu travailles à deux sur une boutique. Tu ajoutes une colonne `telephone` dans ta base locale. Ton collègue ne l'a pas : son code plante. Puis vient la production, qui contient de vrais clients : comment y ajouter la colonne ? Modifier la base à la main dans chaque environnement est lent, risqué, et impossible à reproduire.

Une **migration** est un fichier SQL qui décrit **un changement** de la structure. Les migrations sont :

- **ordonnées** : chaque fichier porte un horodatage et s'exécute dans l'ordre ;
- **versionnées** : elles sont committées dans Git avec le code ;
- **rejouables** : en partant d'une base vide, les appliquer toutes reconstruit exactement la structure actuelle ;
- **tracées** : la base enregistre celles qui ont déjà été appliquées.

> **À retenir** : le schéma Prisma dit **où tu veux aller**, les migrations disent **comment y aller** depuis l'état précédent.

## Créer ta première migration

Pars d'un schéma simple :

```prisma
model Client {
  id    Int    @id @default(autoincrement())
  nom   String
  email String @unique
}
```

Lance la commande de développement :

```bash
npx prisma migrate dev --name creation_client
```

Prisma compare ton schéma à l'historique, génère le SQL, l'applique à ta base locale et régénère le client. Un nouveau dossier apparaît :

```text
prisma/
├── schema.prisma
└── migrations/
    ├── migration_lock.toml
    └── 20261009101500_creation_client/
        └── migration.sql
```

Le contenu de `migration.sql` est du SQL ordinaire, que tu peux et dois relire :

```sql
-- CreateTable
CREATE TABLE "Client" (
    "id" SERIAL NOT NULL,
    "nom" TEXT NOT NULL,
    "email" TEXT NOT NULL,

    CONSTRAINT "Client_pkey" PRIMARY KEY ("id")
);

-- CreateIndex
CREATE UNIQUE INDEX "Client_email_key" ON "Client"("email");
```

Le fichier `migration_lock.toml` indique le fournisseur (`postgresql`) et empêche de mélanger plusieurs types de bases dans un même historique. Tu le committes aussi.

### La table _prisma_migrations

Prisma crée dans ta base une table `_prisma_migrations`. Elle mémorise, pour chaque migration appliquée, son nom, une empreinte (*checksum*) de son contenu et la date d'exécution. Tu peux la consulter :

```sql
SELECT migration_name, finished_at FROM _prisma_migrations;
```

Grâce à elle, Prisma sait quelles migrations restent à appliquer. Grâce au checksum, il détecte aussi si quelqu'un a **modifié un fichier déjà appliqué**, ce qui est interdit : un historique appliqué ne se réécrit jamais.

:::quiz
Que contient le dossier `prisma/migrations/` d'un projet ?
- [ ] Les données de la base, exportées en JSON
- [x] Des dossiers horodatés avec un fichier `migration.sql` décrivant chaque changement de structure
- [ ] Le code de Prisma Client
- [ ] Uniquement les migrations qui ont échoué
> Chaque dossier contient le SQL d'un changement. L'ensemble forme l'historique de la structure de la base, versionné avec le code.
:::

## Faire évoluer le schéma

### Ajouter une colonne optionnelle

Le cas le plus simple : un nouveau champ qui accepte `null`.

```prisma
model Client {
  id        Int     @id @default(autoincrement())
  nom       String
  email     String  @unique
  telephone String?
}
```

```bash
npx prisma migrate dev --name ajout_telephone
```

Le SQL généré est une simple instruction :

```sql
ALTER TABLE "Client" ADD COLUMN "telephone" TEXT;
```

Les lignes existantes reçoivent `NULL` pour cette colonne. Aucun risque.

### Ajouter une colonne obligatoire

Cas plus délicat : tu ajoutes `ville String` sans valeur par défaut, alors que la table contient déjà des clients. Que mettre dans la colonne pour les lignes existantes ? Prisma t'avertit et refuse de continuer :

```text
Added the required column `ville` to the `Client` table without a default value.
There are 12 rows in this table, it is not possible to execute this step.
```

Trois solutions, à choisir selon le cas :

1. **Donner une valeur par défaut** : `ville String @default("Abidjan")`. Les lignes existantes reçoivent cette valeur.
2. **Rendre le champ optionnel** : `ville String?`, puis le remplir progressivement.
3. **Migrer en plusieurs étapes** : ajouter la colonne optionnelle, remplir les données par un script ou du SQL, puis la rendre obligatoire dans une seconde migration.

La troisième approche est la plus sûre en production, car chaque étape est simple et réversible. Tu peux même écrire le remplissage dans la migration elle-même, comme tu vas le voir.

## Éditer une migration avant de l'appliquer

Parfois, le SQL généré ne suffit pas. Par exemple, tu veux **renommer** `nom` en `nomComplet` sans perdre les données. Si tu changes simplement le nom dans le schéma, Prisma voit « une colonne supprimée et une autre créée » et génère un `DROP COLUMN` suivi d'un `ADD COLUMN` : tes données disparaissent.

Pour garder la main, utilise l'option `--create-only` : Prisma crée le fichier SQL **sans l'appliquer**, et tu le modifies.

```bash
npx prisma migrate dev --create-only --name renommer_nom_client
```

Remplace alors le contenu généré par une instruction de renommage :

```sql
-- AlterTable
ALTER TABLE "Client" RENAME COLUMN "nom" TO "nomComplet";
```

Puis applique la migration :

```bash
npx prisma migrate dev
```

> **Attention** : lis **toujours** le SQL d'une migration qui supprime ou renomme quelque chose avant de l'appliquer. Un `DROP COLUMN` ou un `DROP TABLE` détruit définitivement les données concernées. Prisma affiche un avertissement, ne l'ignore jamais.

Voici un exemple en deux temps avec remplissage de données, pour passer un champ `ville` en obligatoire :

```sql
-- 1. Ajouter la colonne en la permettant NULL
ALTER TABLE "Client" ADD COLUMN "ville" TEXT;

-- 2. Remplir les lignes existantes
UPDATE "Client" SET "ville" = 'Abidjan' WHERE "ville" IS NULL;

-- 3. Rendre la colonne obligatoire
ALTER TABLE "Client" ALTER COLUMN "ville" SET NOT NULL;
```

Les trois instructions peuvent vivre dans un même fichier `migration.sql` écrit avec `--create-only`.

:::quiz
Tu renommes un champ dans `schema.prisma` et lances directement `migrate dev`. Que risque-t-il de se passer ?
- [ ] Rien, Prisma détecte toujours les renommages
- [x] La colonne peut être supprimée puis recréée, ce qui efface ses données
- [ ] La base est automatiquement sauvegardée
- [ ] Prisma renomme les fichiers de migration précédents
> Prisma ne devine pas un renommage : il voit une suppression et un ajout. Avec `--create-only`, tu remplaces le SQL par un `RENAME COLUMN` qui conserve les données.
:::

## Les commandes à connaître

Prisma propose plusieurs commandes pour synchroniser schéma et base. Elles ne se valent pas :

| Commande | Environnement | Effet |
| --- | --- | --- |
| `prisma migrate dev` | Développement | Crée et applique les migrations, régénère le client |
| `prisma migrate deploy` | Production, CI | Applique uniquement les migrations existantes, sans en créer |
| `prisma migrate status` | Tous | Indique les migrations appliquées ou en attente |
| `prisma migrate reset` | Développement | Supprime la base, rejoue tout, lance le seed |
| `prisma db push` | Prototypage | Aligne la base sur le schéma sans créer de fichier de migration |
| `prisma db seed` | Tous | Exécute le script de données initiales |

### migrate deploy en production

En production, on ne crée **jamais** de migration : on applique celles qui ont été écrites, testées et relues en développement.

```bash
npx prisma migrate deploy
```

Cette commande ne demande rien, ne compare pas au schéma, n'efface jamais de données et n'utilise pas de base « fantôme ». Elle est faite pour tourner dans un pipeline de déploiement (GitHub Actions, Vercel, Render). Une habitude fréquente consiste à l'inclure dans le script de build :

```json
{
  "scripts": {
    "build": "prisma migrate deploy && next build"
  }
}
```

### migrate reset

Pendant le développement, quand ta base locale est dans un état confus, repars de zéro :

```bash
npx prisma migrate reset
```

Cette commande **supprime toutes les données**, rejoue chaque migration et exécute le seed. Ne la lance jamais sur une base de production : Prisma détecte certains environnements mais ne te protège pas de tout.

### db push : un outil de prototypage

`prisma db push` applique directement le schéma à la base, sans fichier de migration. C'est rapide pour explorer une idée dans les premières heures d'un projet. Mais il ne laisse aucun historique : dès que tu as des données à préserver ou une équipe, passe aux migrations.

## Les données initiales avec le seed

Un **seed** est un script qui insère les données de départ (rôles, catégories, compte administrateur de test). Déclare-le dans `package.json` :

```json
{
  "prisma": {
    "seed": "tsx prisma/seed.ts"
  }
}
```

Puis écris le script :

```ts
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  await prisma.client.upsert({
    where: { email: 'demo@example.com' },
    update: {},
    create: { nomComplet: 'Client Démo', email: 'demo@example.com', ville: 'Abidjan' },
  });
}

main()
  .catch((e) => {
    console.error(e);
    process.exit(1);
  })
  .finally(() => prisma.$disconnect());
```

L'utilisation de `upsert` (créer ou mettre à jour) rend le seed **idempotent** : tu peux le relancer sans créer de doublons. Lance-le avec `npx prisma db seed`. Il s'exécute aussi automatiquement après `migrate reset`.

## Gérer la dérive et les conflits

Une **dérive** (*drift*) se produit quand la base ne correspond plus à l'historique des migrations : quelqu'un a modifié une table directement avec un outil graphique, par exemple. `migrate dev` te le signale :

```text
Drift detected: Your database schema is not in sync with your migration history.
```

En développement, la solution est de lancer `migrate reset` (en acceptant de perdre les données locales). Si la base contient des choses importantes, retrouve ce qui a été modifié à la main et crée une migration qui l'intègre.

Autre situation fréquente : deux développeurs créent chacun une migration sur une branche différente. Après fusion, les deux historiques doivent cohabiter. Le plus souvent, un `git pull` suivi d'un `migrate dev` suffit. En cas de conflit sur le même champ, résous-le dans le schéma, supprime ta migration locale non partagée, et régénère-la.

Pour adopter Prisma sur une base **existante** déjà peuplée, on crée une migration initiale à partir de l'état actuel, puis on la marque comme déjà appliquée :

```bash
npx prisma migrate resolve --applied 20261009101500_init
```

:::quiz
Quelle commande lancer dans un pipeline de déploiement pour mettre à jour la base de production ?
- [ ] `prisma migrate reset`
- [ ] `prisma migrate dev`
- [x] `prisma migrate deploy`
- [ ] `prisma db seed`
> `migrate deploy` applique seulement les migrations déjà écrites et versionnées, sans rien créer ni effacer. `reset` détruit les données et `dev` est réservé au développement.
:::

## Atelier guidé : faire évoluer une boutique sans perdre de données

Compte quarante-cinq minutes. Utilise une base locale jetable.

1. Crée le modèle `Client` (id, nom, email unique) et lance `migrate dev --name creation_client`.
2. Insère trois clients avec un script ou Prisma Studio.
3. Ajoute `telephone String?` et lance `migrate dev --name ajout_telephone`. Ouvre le SQL et vérifie qu'il s'agit d'un `ADD COLUMN`.
4. Ajoute `ville String` sans valeur par défaut, lance `migrate dev` et lis le message d'avertissement de Prisma.
5. Annule, puis recommence avec `--create-only --name ajout_ville` : écris les trois instructions SQL (ajout nullable, `UPDATE`, `SET NOT NULL`) et applique-les.
6. Renomme `nom` en `nomComplet` avec `--create-only` et un `RENAME COLUMN`. Vérifie dans Prisma Studio que les trois clients ont gardé leur nom.
7. Écris un `seed.ts` idempotent avec `upsert`, lance `npx prisma db seed` deux fois et vérifie qu'il n'y a pas de doublon.
8. Lance `npx prisma migrate status` puis `npx prisma migrate reset` et constate que tout est reconstruit.

Auto-évaluation : explique à un collègue pourquoi on ne modifie jamais une migration déjà appliquée, et ce qui distingue `migrate dev` de `migrate deploy`.

## Erreurs fréquentes

- **Modifier un fichier de migration déjà appliqué.** Le checksum ne correspond plus et Prisma signale une migration modifiée.
- **Lancer `migrate dev` en production.** La commande peut proposer de réinitialiser la base. Utilise uniquement `migrate deploy`.
- **Ne pas relire le SQL généré.** Un `DROP COLUMN` passé inaperçu supprime des données.
- **Ajouter une colonne obligatoire sans défaut sur une table remplie.** Utilise une valeur par défaut ou une migration en plusieurs étapes.
- **Oublier de committer le dossier `migrations`.** Tes collègues et la production n'ont alors pas ton historique.
- **Modifier la base à la main avec un outil graphique.** Tu crées une dérive que `migrate dev` finit par détecter.
- **Lancer `migrate reset` sur la mauvaise base.** Vérifie toujours la `DATABASE_URL` active avant.

## Bonnes pratiques

- Une migration = un changement cohérent, avec un nom explicite (`ajout_ville_client`).
- Relis et teste chaque migration sur une copie de données réalistes avant la production.
- Passe par plusieurs migrations simples pour les changements risqués (renommer, rendre obligatoire).
- Sauvegarde la base de production avant une migration sensible.
- Fais tourner `migrate deploy` dans la CI ou au déploiement, jamais depuis ton ordinateur vers la production.
- Garde des seeds idempotents avec `upsert`.
- Utilise `db push` uniquement pour du prototypage jetable.

## À retenir

- Une migration est un fichier SQL horodaté, versionné dans Git, qui décrit un changement de structure.
- `migrate dev` crée et applique en développement ; `migrate deploy` applique seulement en production.
- La table `_prisma_migrations` mémorise l'historique appliqué, et on ne modifie jamais une migration déjà appliquée.
- `--create-only` permet de réécrire le SQL pour renommer ou transformer des données sans perte.
- Une colonne obligatoire sur une table remplie demande une valeur par défaut ou plusieurs étapes.
- `migrate reset` efface tout : il est réservé au développement.
