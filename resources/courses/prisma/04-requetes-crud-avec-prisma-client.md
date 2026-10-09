---
title: Requêtes CRUD avec Prisma Client
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Tu sais décrire tes données et les migrer. Il est temps de les manipuler. Prisma Client offre une API cohérente pour les quatre opérations de base, **C**reate, **R**ead, **U**pdate, **D**elete, et pour tout ce qui gravite autour : filtres, tri, pagination, sélection de champs, agrégations et gestion d'erreurs. Ce chapitre est celui que tu rouvriras le plus souvent.

À la fin du chapitre, tu seras capable de :

- créer un ou plusieurs enregistrements avec `create` et `createMany` ;
- lire avec `findUnique`, `findFirst`, `findMany` et leurs variantes `OrThrow` ;
- filtrer avec les opérateurs (`contains`, `gte`, `in`, `AND`, `OR`, `NOT`) ;
- trier, paginer par décalage (`skip`/`take`) et par curseur ;
- limiter les champs retournés avec `select` ;
- modifier avec `update`, `updateMany`, `upsert` et les opérations atomiques ;
- supprimer avec `delete` et `deleteMany` ;
- compter et agréger avec `count`, `aggregate` et `groupBy` ;
- attraper et interpréter les erreurs Prisma (`P2002`, `P2025`).

Prérequis : les trois premiers chapitres et un schéma avec un modèle `Produit` (voir ci-dessous). Prévois deux heures et demie, éditeur ouvert et Prisma Studio à portée de main.

## Le schéma d'exemple

Tous les exemples utilisent ce schéma de boutique :

```prisma
enum Categorie {
  ELECTRONIQUE
  MODE
  MAISON
}

model Produit {
  id        Int       @id @default(autoincrement())
  nom       String
  prixXof   Int
  stock     Int       @default(0)
  categorie Categorie
  actif     Boolean   @default(true)
  reference String    @unique
  creeLe    DateTime  @default(now())
}

model Vente {
  id        Int      @id @default(autoincrement())
  produitId Int
  quantite  Int
  creeLe    DateTime @default(now())
}
```

Le modèle `Vente` sert pour la section sur les transactions ; sa clé étrangère vers `Produit` sera déclarée proprement au chapitre suivant. Voici maintenant l'instance partagée du client, créée une seule fois dans `lib/prisma.ts` :

```ts
import { PrismaClient } from '@prisma/client';

export const prisma = new PrismaClient();
```

## Create : créer des données

### create

`create` insère une ligne et la retourne, avec l'`id` généré :

```ts
const casque = await prisma.produit.create({
  data: {
    nom: 'Casque audio',
    prixXof: 25000,
    stock: 12,
    categorie: 'ELECTRONIQUE',
    reference: 'CASQ-001',
  },
});
// casque.id vaut par exemple 1
```

Les champs avec une valeur par défaut (`actif`, `creeLe`) sont facultatifs. Si tu oublies un champ obligatoire, TypeScript te le signale avant même l'exécution.

### createMany

Pour insérer plusieurs lignes en une seule requête SQL, bien plus rapide qu'une boucle de `create` :

```ts
const resultat = await prisma.produit.createMany({
  data: [
    { nom: 'T-shirt', prixXof: 6000, categorie: 'MODE', reference: 'TSH-001' },
    { nom: 'Lampe', prixXof: 9000, categorie: 'MAISON', reference: 'LAM-001' },
  ],
  skipDuplicates: true,
});
console.log(resultat.count); // nombre de lignes insérées
```

`createMany` retourne seulement `{ count }`, pas les objets créés. L'option `skipDuplicates` ignore les lignes qui violent une contrainte d'unicité au lieu de faire échouer toute l'opération.

## Read : lire des données

### Trouver un enregistrement

| Méthode | Usage | Si rien n'est trouvé |
| --- | --- | --- |
| `findUnique` | Recherche par champ `@id` ou `@unique` | `null` |
| `findUniqueOrThrow` | Idem | Lève une erreur |
| `findFirst` | Premier résultat selon un filtre quelconque | `null` |
| `findFirstOrThrow` | Idem | Lève une erreur |
| `findMany` | Liste de tous les résultats | Tableau vide |

```ts
const parId = await prisma.produit.findUnique({ where: { id: 1 } });
const parRef = await prisma.produit.findUnique({ where: { reference: 'CASQ-001' } });

if (!parId) {
  throw new Error('Produit introuvable');
}
```

Le type de `parId` est `Produit | null` : TypeScript t'oblige à gérer le cas absent. Avec `findUniqueOrThrow`, le type est `Produit` et une exception est lancée si la ligne n'existe pas, pratique dans une route où tu veux répondre 404.

### Filtrer avec where

Le filtre s'écrit comme un objet qui ressemble aux données :

```ts
// Égalité simple
await prisma.produit.findMany({ where: { categorie: 'MODE' } });

// Opérateurs de comparaison
await prisma.produit.findMany({
  where: {
    prixXof: { gte: 5000, lte: 20000 },   // entre 5 000 et 20 000
    stock: { gt: 0 },                     // en stock
  },
});

// Texte
await prisma.produit.findMany({
  where: { nom: { contains: 'casque', mode: 'insensitive' } },
});

// Appartenance à une liste
await prisma.produit.findMany({
  where: { categorie: { in: ['MODE', 'MAISON'] } },
});
```

Voici les opérateurs les plus utiles :

| Opérateur | Sens | Exemple |
| --- | --- | --- |
| `equals`, `not` | égal, différent | `{ not: 'MODE' }` |
| `lt`, `lte`, `gt`, `gte` | comparaisons | `{ gte: 100 }` |
| `in`, `notIn` | dans une liste | `{ in: [1, 2] }` |
| `contains`, `startsWith`, `endsWith` | texte | `{ startsWith: 'CASQ' }` |
| `mode: 'insensitive'` | ignore la casse (PostgreSQL) | avec `contains` |

Pour combiner des conditions, utilise `AND`, `OR` et `NOT`. Par défaut, plusieurs champs dans un même objet sont combinés par un **ET** :

```ts
await prisma.produit.findMany({
  where: {
    actif: true,
    OR: [
      { categorie: 'ELECTRONIQUE' },
      { prixXof: { lt: 3000 } },
    ],
    NOT: { stock: 0 },
  },
});
```

Cette requête cherche les produits actifs, en stock, qui sont soit de l'électronique, soit bon marché.

:::quiz
Que retourne `prisma.produit.findUnique({ where: { id: 999 } })` si aucun produit n'a l'id 999 ?
- [ ] Une erreur est lancée
- [ ] Un tableau vide
- [x] `null`
- [ ] `undefined`
> `findUnique` retourne `null` quand rien n'est trouvé. C'est `findUniqueOrThrow` qui lance une exception dans ce cas.
:::

### Trier, limiter et paginer

```ts
const page = await prisma.produit.findMany({
  where: { actif: true },
  orderBy: [{ categorie: 'asc' }, { prixXof: 'desc' }],
  skip: 20,
  take: 10,
});
```

`orderBy` accepte un tableau pour trier sur plusieurs critères. `take` limite le nombre de lignes, `skip` en saute. Pour afficher la page numéro `n` avec `taille` éléments, on calcule `skip = (n - 1) * taille`.

La pagination par décalage devient lente sur de gros volumes, car PostgreSQL doit lire puis ignorer toutes les lignes sautées. La **pagination par curseur** est plus efficace : on repart de la dernière ligne vue.

```ts
const suite = await prisma.produit.findMany({
  take: 10,
  skip: 1,                     // on saute le curseur lui-même
  cursor: { id: dernierIdVu },
  orderBy: { id: 'asc' },
});
```

Retiens : décalage pour des pages numérotées simples, curseur pour le défilement infini ou les grosses tables.

### Choisir les champs avec select

Par défaut, Prisma retourne tous les champs scalaires. Avec `select`, tu ne demandes que ce dont tu as besoin, ce qui réduit les données transférées et protège les champs sensibles :

```ts
const resume = await prisma.produit.findMany({
  select: { id: true, nom: true, prixXof: true },
});
// Type : { id: number; nom: string; prixXof: number }[]
```

Le type du résultat s'adapte exactement à la sélection. Il existe aussi `omit` (Prisma 6) pour exclure certains champs : `omit: { reference: true }` retourne tout sauf `reference`. Tu ne peux pas mélanger `select` et `include` au même niveau : choisis l'un ou l'autre (nous verrons `include` au chapitre suivant).

## Update : modifier des données

### update et updateMany

```ts
// Modifier une ligne identifiée par un champ unique
const modifie = await prisma.produit.update({
  where: { id: 1 },
  data: { prixXof: 22000, actif: true },
});

// Modifier plusieurs lignes selon un filtre
const resultat = await prisma.produit.updateMany({
  where: { categorie: 'MODE' },
  data: { actif: false },
});
console.log(resultat.count);
```

`update` exige un `where` **unique** et lance une erreur si la ligne n'existe pas. `updateMany` accepte n'importe quel filtre et retourne seulement un `count`.

### Les opérations atomiques

Pour modifier un nombre à partir de sa valeur actuelle, n'écris jamais « lire, calculer, écrire » : deux requêtes simultanées s'écraseraient. Utilise les opérations atomiques, exécutées directement par la base :

```ts
await prisma.produit.update({
  where: { id: 1 },
  data: {
    stock: { decrement: 2 },
    prixXof: { multiply: 2 },
  },
});
```

Les opérations disponibles sont `increment`, `decrement`, `multiply`, `divide` et `set`.

### upsert

`upsert` met à jour si la ligne existe, la crée sinon, en une seule opération :

```ts
const produit = await prisma.produit.upsert({
  where: { reference: 'CASQ-001' },
  update: { stock: { increment: 10 } },
  create: {
    nom: 'Casque audio',
    prixXof: 25000,
    stock: 10,
    categorie: 'ELECTRONIQUE',
    reference: 'CASQ-001',
  },
});
```

## Delete : supprimer des données

```ts
// Une ligne (where unique)
await prisma.produit.delete({ where: { id: 1 } });

// Plusieurs lignes
const { count } = await prisma.produit.deleteMany({
  where: { actif: false, stock: 0 },
});
```

> **Attention** : `deleteMany({})` sans `where` supprime **toute la table**. Vérifie toujours ton filtre. Pour des données importantes, préfère la **suppression logique** : un champ `supprimeLe DateTime?` que tu remplis au lieu d'effacer la ligne, et que tu filtres dans tes lectures.

:::quiz
Quelle est la bonne façon de décrémenter le stock sans risque de conflit entre deux requêtes simultanées ?
- [ ] Lire le produit, calculer `stock - 1`, puis écrire le résultat
- [x] `data: { stock: { decrement: 1 } }`
- [ ] Utiliser `findMany` puis une boucle de `update`
- [ ] Faire un `delete` puis un `create`
> `decrement` est exécuté par la base en une seule instruction atomique. L'approche « lire puis écrire » peut perdre des modifications quand deux requêtes se croisent.
:::

## Compter et agréger

```ts
// Compter
const total = await prisma.produit.count({ where: { actif: true } });

// Calculs sur une colonne
const stats = await prisma.produit.aggregate({
  where: { categorie: 'MODE' },
  _avg: { prixXof: true },
  _min: { prixXof: true },
  _max: { prixXof: true },
  _sum: { stock: true },
});
console.log(stats._avg.prixXof);

// Regrouper : le stock total par catégorie
const parCategorie = await prisma.produit.groupBy({
  by: ['categorie'],
  _sum: { stock: true },
  _count: { _all: true },
  orderBy: { categorie: 'asc' },
});
```

`groupBy` correspond au `GROUP BY` du SQL. Le résultat est un tableau d'objets du type `{ categorie: 'MODE', _sum: { stock: 40 }, _count: { _all: 3 } }`. Ajoute `having` pour filtrer sur les valeurs agrégées.

## Gérer les erreurs

Quand une opération échoue à cause de la base, Prisma lance une `PrismaClientKnownRequestError` avec un **code** :

| Code | Signification | Cause typique |
| --- | --- | --- |
| `P2002` | Contrainte d'unicité violée | Un e-mail ou une référence existe déjà |
| `P2025` | Enregistrement introuvable | `update` ou `delete` sur un `id` inexistant |
| `P2003` | Contrainte de clé étrangère violée | Référence vers une ligne qui n'existe pas |

```ts
import { Prisma } from '@prisma/client';

try {
  await prisma.produit.create({ data: { /* ... */ reference: 'CASQ-001', nom: 'X', prixXof: 1, categorie: 'MODE' } });
} catch (erreur) {
  if (erreur instanceof Prisma.PrismaClientKnownRequestError) {
    if (erreur.code === 'P2002') {
      console.log('Cette référence existe déjà');
      return;
    }
  }
  throw erreur; // on ne masque jamais une erreur inconnue
}
```

Convertir ces codes en messages clairs pour l'utilisateur fait partie d'une API de qualité. Relance toujours l'erreur si tu ne sais pas la traiter.

## Transactions

Parfois, plusieurs écritures doivent réussir **ensemble ou pas du tout** : débiter un stock et enregistrer une vente, par exemple. Prisma propose `$transaction` :

```ts
const [vente, produit] = await prisma.$transaction([
  prisma.vente.create({ data: { produitId: 1, quantite: 2 } }),
  prisma.produit.update({ where: { id: 1 }, data: { stock: { decrement: 2 } } }),
]);
```

Si l'une des opérations échoue, tout est annulé. Pour une logique avec des lectures intermédiaires, la forme interactive donne un client dédié :

```ts
await prisma.$transaction(async (tx) => {
  const produit = await tx.produit.findUniqueOrThrow({ where: { id: 1 } });
  if (produit.stock < 2) {
    throw new Error('Stock insuffisant'); // annule la transaction
  }
  await tx.produit.update({ where: { id: 1 }, data: { stock: { decrement: 2 } } });
});
```

Dans le bloc, utilise `tx` et non `prisma`, sinon tes requêtes sortent de la transaction. Garde les transactions courtes : elles bloquent des ressources.

## Atelier guidé : le catalogue de la boutique

Compte quarante-cinq minutes. Crée un script `catalogue.ts` par étape ou un seul fichier commenté.

1. Insère douze produits répartis dans les trois catégories avec `createMany`.
2. Affiche les produits d'une catégorie triés par prix décroissant, avec `select` pour n'avoir que `nom` et `prixXof`.
3. Écris une fonction `rechercher(texte, categorie?, prixMax?)` qui construit un `where` dynamique et utilise `contains` avec `mode: 'insensitive'`.
4. Implémente `page(numero, taille)` avec `skip`, `take` et un `count` pour retourner aussi le nombre total de pages.
5. Écris la version par curseur et compare les deux sur quelques pages.
6. Applique une réduction de 10 % aux produits de mode avec `updateMany`, puis utilise `increment` pour réapprovisionner un produit.
7. Utilise `upsert` pour ajouter du stock à une référence, qu'elle existe ou non.
8. Provoque une erreur `P2002` en créant deux fois la même référence et affiche un message lisible.
9. Calcule avec `groupBy` le stock total et le prix moyen par catégorie.
10. Écris une vente dans une transaction interactive qui refuse la vente si le stock est insuffisant.

Auto-évaluation : quelle est la différence entre `findUnique` et `findFirst` ? Pourquoi `increment` vaut-il mieux qu'une lecture suivie d'une écriture ?

## Erreurs fréquentes

- **Utiliser `update` avec un filtre non unique.** `update` demande un champ `@id` ou `@unique` ; pour un filtre libre, utilise `updateMany`.
- **Oublier `await`.** Tu manipules une promesse au lieu des données.
- **Lancer `deleteMany({})` sans filtre.** Toute la table est vidée.
- **Ne pas gérer le `null` de `findUnique`.** TypeScript t'avertit, mais un `!` forcé ne fait que repousser le crash.
- **Faire une boucle de `create` dans un `for`.** Utilise `createMany`, beaucoup plus rapide.
- **Instancier `PrismaClient` à chaque appel.** Utilise l'instance partagée.
- **Utiliser `prisma` à l'intérieur d'une transaction interactive.** Utilise `tx`.
- **Masquer toutes les erreurs dans un `catch`.** Traite les codes connus et relance le reste.

## Bonnes pratiques

- Un fichier `lib/prisma.ts` unique exporte l'instance du client.
- Utilise `select` pour ne retourner que ce dont le client a besoin, jamais un mot de passe haché par exemple.
- Préfère les opérations atomiques (`increment`, `decrement`) aux calculs côté application.
- Pagine toujours les listes qui peuvent grandir : jamais de `findMany` sans limite sur une grande table.
- Valide les entrées utilisateur (avec Zod, par exemple) **avant** de les passer à `where` ou `data`.
- Convertis les codes d'erreur Prisma en réponses claires dans ton API.
- Pense à la suppression logique pour les données qu'on pourrait vouloir restaurer.

## À retenir

- `create`, `createMany`, `findUnique`, `findFirst`, `findMany`, `update`, `updateMany`, `upsert`, `delete`, `deleteMany` forment le socle de l'API.
- `findUnique` exige un champ unique et renvoie `null` si rien n'est trouvé ; les variantes `OrThrow` lancent une erreur.
- `where` combine des opérateurs (`gte`, `contains`, `in`) avec `AND`, `OR` et `NOT`.
- `orderBy`, `skip`/`take` et `cursor` gèrent tri et pagination ; `select` limite les champs.
- `increment`, `decrement` et `upsert` évitent les conflits de lecture puis écriture.
- Les erreurs `P2002` et `P2025` se traitent avec `PrismaClientKnownRequestError`.
- `$transaction` garantit que plusieurs écritures réussissent ensemble ou pas du tout.
