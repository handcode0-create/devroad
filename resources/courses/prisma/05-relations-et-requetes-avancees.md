---
title: Relations et requêtes avancées
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Les données du monde réel sont liées : un client passe des commandes, une commande contient des produits, un article reçoit des commentaires. La force d'une base relationnelle comme PostgreSQL, c'est justement de modéliser ces liens. Prisma te permet de les déclarer dans le schéma puis de les parcourir en TypeScript sans écrire une seule jointure. Ce chapitre couvre les trois grands types de relations, la lecture et l'écriture de données liées, les transactions, le SQL brut, et surtout le piège de performance le plus classique : le problème « N+1 ».

À la fin du chapitre, tu seras capable de :

- modéliser des relations un-à-plusieurs, un-à-un et plusieurs-à-plusieurs ;
- choisir une relation plusieurs-à-plusieurs implicite ou explicite ;
- charger des données liées avec `include` et `select` imbriqués ;
- filtrer sur une relation (`some`, `every`, `none`, `is`) ;
- créer et modifier des données liées en une seule opération (`create`, `connect`, `connectOrCreate`, `set`) ;
- configurer les actions référentielles (`onDelete`) ;
- repérer et éviter le problème N+1 ;
- utiliser `$transaction`, les requêtes brutes `$queryRaw` et les relations auto-référentes.

Prérequis : les chapitres 1 à 4. Prévois deux heures et demie. Les exemples suivent une boutique en ligne avec clients, commandes et produits.

## Les relations un-à-plusieurs

C'est la relation la plus fréquente : un client a plusieurs commandes, une commande appartient à un seul client.

```prisma
model Client {
  id        Int        @id @default(autoincrement())
  nom       String
  email     String     @unique
  commandes Commande[]
}

model Commande {
  id         Int      @id @default(autoincrement())
  clientId   Int
  client     Client   @relation(fields: [clientId], references: [id], onDelete: Cascade)
  totalXof   Int
  payee      Boolean  @default(false)
  creeLe     DateTime @default(now())

  @@index([clientId])
}
```

Le côté qui possède la **clé étrangère** (`Commande.clientId`) porte l'attribut `@relation`. L'autre côté (`Client.commandes`) est une simple liste virtuelle.

### Que se passe-t-il à la suppression ? onDelete

Si on supprime un client, que deviennent ses commandes ? L'argument `onDelete` répond :

| Valeur | Effet |
| --- | --- |
| `Cascade` | Les commandes sont supprimées avec le client |
| `Restrict` | La suppression du client est refusée tant qu'il a des commandes |
| `SetNull` | `clientId` devient `null` (le champ doit être optionnel) |
| `NoAction` | Comme `Restrict`, vérifié en fin d'instruction |

Choisis selon le sens métier. Des lignes de panier peuvent disparaître avec leur panier (`Cascade`), mais des commandes comptables ne doivent pas s'effacer avec un client : préfère `Restrict`, ou une suppression logique.

> **Attention** : `Cascade` est pratique mais dangereux. Une suppression peut en entraîner des dizaines d'autres en chaîne. Réserve-le aux données qui n'ont aucun sens sans leur parent.

## Les relations un-à-un

Un utilisateur a un seul profil, et un profil appartient à un seul utilisateur. La clé étrangère porte une contrainte `@unique`, ce qui garantit l'unicité :

```prisma
model Utilisateur {
  id      Int     @id @default(autoincrement())
  email   String  @unique
  profil  Profil?
}

model Profil {
  id            Int         @id @default(autoincrement())
  bio           String?
  utilisateurId Int         @unique
  utilisateur   Utilisateur @relation(fields: [utilisateurId], references: [id])
}
```

Remarque le `?` sur `profil Profil?` côté `Utilisateur` : un utilisateur peut ne pas encore avoir de profil. Le côté qui porte la clé étrangère est obligatoire ou non selon le type de `utilisateurId`.

## Les relations plusieurs-à-plusieurs

Une commande contient plusieurs produits, et un produit apparaît dans plusieurs commandes. Prisma offre deux modèles.

### Relation implicite

Prisma crée lui-même la table de jonction cachée :

```prisma
model Article {
  id    Int    @id @default(autoincrement())
  titre String
  tags  Tag[]
}

model Tag {
  id       Int       @id @default(autoincrement())
  nom      String    @unique
  articles Article[]
}
```

C'est le plus simple, parfait quand le lien n'a **aucune donnée propre**.

### Relation explicite

Dès que le lien porte une information (une quantité, un prix à la date de l'achat, une date d'ajout), il faut une vraie table de jonction :

```prisma
model Produit {
  id       Int            @id @default(autoincrement())
  nom      String
  prixXof  Int
  lignes   LigneCommande[]
}

model Commande {
  id       Int             @id @default(autoincrement())
  lignes   LigneCommande[]
}

model LigneCommande {
  commandeId Int
  produitId  Int
  quantite   Int
  prixUnitaireXof Int
  commande   Commande @relation(fields: [commandeId], references: [id], onDelete: Cascade)
  produit    Produit  @relation(fields: [produitId], references: [id])

  @@id([commandeId, produitId])
}
```

La clé primaire composée `@@id([commandeId, produitId])` empêche d'avoir deux fois le même produit dans une commande. Le champ `prixUnitaireXof` enregistre le prix **au moment de l'achat** : si le prix du produit change plus tard, l'historique reste juste.

:::quiz
Quand faut-il préférer une relation plusieurs-à-plusieurs explicite ?
- [ ] Quand la base est très grande
- [ ] Quand on utilise PostgreSQL
- [x] Quand le lien porte ses propres données, comme une quantité ou une date
- [ ] Quand on n'a pas besoin de clé étrangère
> Une table de jonction explicite est un modèle à part entière : elle peut contenir des champs supplémentaires (quantité, prix, date), ce que la relation implicite ne permet pas.
:::

## Lire des données liées

### include

Par défaut, une requête ne retourne que les champs scalaires. `include` charge aussi les relations :

```ts
const client = await prisma.client.findUnique({
  where: { id: 1 },
  include: {
    commandes: {
      orderBy: { creeLe: 'desc' },
      take: 5,
      include: {
        lignes: { include: { produit: true } },
      },
    },
  },
});

// client.commandes[0].lignes[0].produit.nom est typé et autocomplété
```

Chaque niveau accepte `where`, `orderBy` et `take`. Le type du résultat inclut précisément les relations demandées, ni plus ni moins.

### select imbriqué

Pour limiter les champs à chaque niveau, utilise `select` partout :

```ts
const resume = await prisma.client.findMany({
  select: {
    nom: true,
    commandes: {
      select: { id: true, totalXof: true },
    },
    _count: { select: { commandes: true } },
  },
});
```

`_count` donne directement le nombre de commandes par client, sans les charger. Souviens-toi : `select` et `include` ne se mélangent pas au même niveau, mais tu peux placer l'un dans l'autre à des niveaux différents.

### Filtrer sur une relation

Pour trouver les clients selon leurs commandes, on filtre à travers la relation :

```ts
// Clients ayant AU MOINS UNE commande de plus de 50 000
await prisma.client.findMany({
  where: { commandes: { some: { totalXof: { gt: 50000 } } } },
});

// Clients dont TOUTES les commandes sont payées
await prisma.client.findMany({
  where: { commandes: { every: { payee: true } } },
});

// Clients sans aucune commande
await prisma.client.findMany({
  where: { commandes: { none: {} } },
});

// Côté "un" d'une relation : is / isNot
await prisma.commande.findMany({
  where: { client: { is: { email: { endsWith: '@example.com' } } } },
});
```

`some`, `every` et `none` s'appliquent aux listes ; `is` et `isNot` aux relations vers un seul enregistrement.

## Écrire des données liées

Prisma permet de créer un parent et ses enfants en une seule opération, enveloppée dans une transaction implicite :

```ts
const commande = await prisma.commande.create({
  data: {
    client: { connect: { id: 1 } },          // lier à un client existant
    totalXof: 31000,
    lignes: {
      create: [
        { produitId: 1, quantite: 1, prixUnitaireXof: 25000 },
        { produitId: 2, quantite: 1, prixUnitaireXof: 6000 },
      ],
    },
  },
  include: { lignes: true },
});
```

Les opérations de relation les plus courantes :

| Opération | Effet |
| --- | --- |
| `create` | Crée une nouvelle ligne liée |
| `connect` | Lie une ligne existante |
| `connectOrCreate` | Lie la ligne si elle existe, la crée sinon |
| `disconnect` | Retire le lien (relations optionnelles ou plusieurs-à-plusieurs) |
| `set` | Remplace tous les liens d'une liste |
| `update`, `upsert`, `delete` | Agissent sur les lignes liées |

```ts
// Ajouter un tag à un article, en le créant s'il n'existe pas
await prisma.article.update({
  where: { id: 1 },
  data: {
    tags: {
      connectOrCreate: {
        where: { nom: 'prisma' },
        create: { nom: 'prisma' },
      },
    },
  },
});
```

Tu peux aussi utiliser directement la clé étrangère (`clientId: 1`) à la place de `client: { connect: ... }`. Les deux formes sont équivalentes, mais ne les mélange pas dans un même appel.

## Le problème N+1

C'est l'erreur de performance la plus répandue avec un ORM. Observe ce code :

```ts
const clients = await prisma.client.findMany();          // 1 requête

for (const client of clients) {
  const commandes = await prisma.commande.findMany({    // N requêtes !
    where: { clientId: client.id },
  });
  console.log(client.nom, commandes.length);
}
```

Pour 100 clients, cela fait **101 requêtes** : une pour la liste, puis une par client. Chaque aller-retour vers la base coûte quelques millisecondes, et la page devient lente à mesure que les données grossissent. La solution est de charger les relations en une fois :

```ts
const clients = await prisma.client.findMany({
  include: { commandes: true },       // 2 requêtes au total, quel que soit N
});
```

Prisma exécute alors deux requêtes (les clients, puis toutes leurs commandes avec un `IN`) et assemble le résultat. Active `log: ['query']` pour compter les requêtes émises : si le nombre augmente avec la taille des données, tu as un N+1.

Dans les cas où seul un compteur t'intéresse, `_count` est encore plus économe :

```ts
const clients = await prisma.client.findMany({
  select: { nom: true, _count: { select: { commandes: true } } },
});
```

:::quiz
Un `findMany` de 50 clients est suivi d'un `findMany` de commandes dans une boucle `for`. Combien de requêtes sont émises, et comment corriger ?
- [ ] 2 requêtes, rien à corriger
- [x] 51 requêtes ; on charge les commandes avec `include` dans la première requête
- [ ] 50 requêtes ; on ajoute un index
- [ ] 1 requête ; on utilise `createMany`
> C'est le problème N+1 : une requête pour la liste plus une par élément. `include` (ou `_count` pour un simple compteur) règle le problème en 2 requêtes.
:::

## Relation auto-référente et SQL brut

### Une table liée à elle-même

Une catégorie peut avoir une catégorie parente. Le modèle se relie à lui-même, avec des noms de relation pour distinguer les deux sens :

```prisma
model Categorie {
  id        Int         @id @default(autoincrement())
  nom       String
  parentId  Int?
  parent    Categorie?  @relation("Hierarchie", fields: [parentId], references: [id])
  enfants   Categorie[] @relation("Hierarchie")
}
```

Quand un modèle a **plusieurs** relations vers le même autre modèle, ou une relation vers lui-même, il faut un nom de relation (la chaîne `"Hierarchie"`) pour que Prisma sache quels champs vont ensemble.

### Requêtes SQL brutes

Prisma ne couvre pas tout. Pour une requête complexe (fonction PostgreSQL, vue, recherche plein texte), tu peux écrire du SQL :

```ts
const topClients = await prisma.$queryRaw<{ nom: string; total: bigint }[]>`
  SELECT c."nom", SUM(o."totalXof") AS total
  FROM "Client" c
  JOIN "Commande" o ON o."clientId" = c."id"
  GROUP BY c."nom"
  ORDER BY total DESC
  LIMIT 5
`;
```

La requête est un **gabarit étiqueté** (*tagged template*) : les valeurs insérées avec `${...}` deviennent des paramètres, ce qui te protège de l'injection SQL.

```ts
const minimum = 50000;
await prisma.$queryRaw`SELECT * FROM "Commande" WHERE "totalXof" > ${minimum}`;
```

> **Erreur fréquente** : construire le SQL avec une chaîne classique (`"... WHERE id = " + saisie`) et la passer à `$queryRawUnsafe`. Une saisie malveillante peut alors exécuter du SQL arbitraire. Utilise toujours la forme avec gabarit.

N'oublie pas que PostgreSQL renvoie les `SUM` et `COUNT` en `bigint`, que JavaScript sérialise mal en JSON : convertis avec `Number(...)` avant de répondre à un client.

## Atelier guidé : commandes et clients

Compte quarante-cinq minutes. Pars du schéma de boutique de ce chapitre.

1. Écris les modèles `Client`, `Commande`, `Produit` et `LigneCommande` avec `onDelete` adapté (`Cascade` pour les lignes, `Restrict` pour les commandes d'un client).
2. Lance `migrate dev --name relations` et vérifie les `FOREIGN KEY` dans le SQL.
3. Écris un script de seed qui crée trois clients, cinq produits et plusieurs commandes avec leurs lignes en une seule opération.
4. Affiche un client avec ses cinq dernières commandes et, pour chacune, le nom des produits.
5. Liste les clients qui n'ont jamais commandé, puis ceux qui ont une commande de plus de 50 000 FCFA.
6. Écris volontairement la version N+1 d'un affichage « clients et nombre de commandes », compte les requêtes avec `log: ['query']`, puis corrige avec `_count`.
7. Teste `onDelete` : supprime une commande et vérifie la suppression de ses lignes ; tente de supprimer un client avec des commandes et lis l'erreur `P2003`.
8. Ajoute le modèle `Categorie` auto-référent et crée une catégorie avec deux sous-catégories.
9. Écris une requête `$queryRaw` qui renvoie le total des ventes par client et convertis les `bigint`.

Auto-évaluation : à quel moment utilises-tu `connect` plutôt que `create` ? Comment détecter un problème N+1 sans toucher à la base ?

## Erreurs fréquentes

- **Oublier le côté inverse de la relation.** La validation du schéma échoue.
- **Oublier l'index sur la clé étrangère.** Les jointures et les `include` ralentissent sur de gros volumes.
- **Utiliser `include` partout.** Tu charges des données inutiles ; privilégie `select`.
- **Écrire des boucles de requêtes** (N+1) au lieu d'un `include`.
- **Choisir `Cascade` par réflexe.** Une suppression peut effacer des données comptables.
- **Mélanger `select` et `include` au même niveau.** Prisma refuse la requête.
- **Utiliser `$queryRawUnsafe` avec une saisie utilisateur.** Faille d'injection SQL.
- **Renvoyer un `bigint` tel quel en JSON.** L'erreur « Do not know how to serialize a BigInt » apparaît.

## Bonnes pratiques

- Déclare toujours `@@index` sur les clés étrangères que tu filtres souvent.
- Utilise une relation explicite dès que le lien a des données propres.
- Choisis `onDelete` en fonction du sens métier, pas par habitude.
- Stocke le prix au moment de l'achat dans la ligne de commande, jamais seulement la référence au produit.
- Utilise `select` et `_count` pour ne charger que le nécessaire.
- Surveille le nombre de requêtes avec `log: ['query']` pendant le développement.
- Réserve le SQL brut aux besoins que l'API de Prisma ne couvre pas, et paramètre toujours les valeurs.

## À retenir

- Une relation se compose d'une clé étrangère, d'un champ `@relation` et de son côté inverse.
- Un-à-plusieurs et un-à-un portent la clé sur un côté ; le un-à-un ajoute `@unique`.
- Le plusieurs-à-plusieurs est implicite (simple) ou explicite (quand le lien a des données).
- `include`, `select` imbriqué, `_count`, `some`, `every`, `none` et `is` lisent et filtrent à travers les relations.
- `create`, `connect`, `connectOrCreate` et `set` écrivent des données liées en une opération.
- `onDelete` règle ce qui arrive aux enfants quand le parent disparaît.
- Le N+1 se corrige en chargeant les relations dans la requête principale.
- `$queryRaw` avec gabarit étiqueté permet le SQL brut sans risque d'injection.
