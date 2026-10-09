---
title: Découvrir Prisma
minutes: 90
level: beginner
---

## Ce que tu vas apprendre

Presque toutes les applications web stockent des données : des utilisateurs, des commandes, des articles, des leçons. Pour parler à une base de données, il faut du SQL. Prisma est un **ORM** moderne pour TypeScript qui te permet de lire et d'écrire dans PostgreSQL avec du code typé, sans écrire une ligne de SQL pour les cas courants. Ce chapitre pose les bases : ce qu'est Prisma, comment il est organisé, et comment réaliser ta première requête.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un ORM et le problème qu'il résout ;
- nommer les trois briques de Prisma : le **schéma**, **Prisma Client** et **Prisma Migrate** ;
- démarrer une base PostgreSQL locale avec Docker ;
- initialiser Prisma dans un projet TypeScript avec `prisma init` ;
- lire le fichier `schema.prisma` et en comprendre les trois blocs ;
- exécuter ta première requête avec Prisma Client et l'afficher dans le terminal ;
- ouvrir Prisma Studio pour visualiser tes données.

Prérequis : bases de TypeScript (types, fonctions, `async`/`await`), Node.js installé (version 20 ou plus) et une idée de ce qu'est une table dans une base de données. Prévois une heure et demie. Docker est conseillé pour PostgreSQL, mais une base hébergée gratuitement (Neon, Supabase) fonctionne aussi.

## Le problème : parler à une base de données

Une base PostgreSQL stocke les données dans des **tables**, comme des feuilles de tableur. Pour récupérer les utilisateurs majeurs, tu écris du SQL :

```sql
SELECT id, nom, email
FROM "Utilisateur"
WHERE age >= 18
ORDER BY nom ASC;
```

Dans une application TypeScript, tu envoies cette requête sous forme de chaîne de caractères, et tu reçois un résultat dont personne ne connaît le type. Voici ce que ça donne avec le pilote `pg`, très répandu :

```ts
const resultat = await client.query(
  'SELECT id, nom, email FROM "Utilisateur" WHERE age >= $1',
  [18]
);
// resultat.rows est de type any : aucune aide de l'éditeur
console.log(resultat.rows[0].nmo); // faute de frappe non détectée
```

Trois soucis apparaissent vite :

1. **Aucune vérification** : une faute de frappe dans une colonne n'est découverte qu'à l'exécution, parfois en production.
2. **Aucune autocomplétion** : tu dois te souvenir de tous les noms de colonnes.
3. **Du code répétitif** : insérer, modifier, filtrer, trier et paginer demandent beaucoup de chaînes à assembler à la main.

Un **ORM** (*Object-Relational Mapper*) règle ces trois problèmes. Il fait la correspondance entre les **tables** de la base et des **objets** de ton langage. Au lieu d'écrire du SQL, tu appelles des fonctions, et l'ORM génère le SQL à ta place.

> **À retenir** : un ORM ne remplace pas la base de données. Il traduit ton code en requêtes SQL et traduit les résultats en objets utilisables dans ton langage.

:::quiz
Quel est l'intérêt principal d'un ORM comme Prisma dans un projet TypeScript ?
- [ ] Il remplace la base de données et stocke lui-même les données
- [x] Il traduit des appels de fonctions typés en requêtes SQL et renvoie des objets typés
- [ ] Il rend le SQL inutile à connaître dans tous les cas
- [ ] Il fonctionne uniquement avec MongoDB
> Prisma génère les requêtes SQL et te donne des résultats typés. La base de données (PostgreSQL) reste le lieu de stockage, et connaître le SQL reste utile pour comprendre ce qui se passe.
:::

## Les trois briques de Prisma

Prisma n'est pas un seul outil, c'est une petite boîte à outils construite autour d'un **fichier central** : le schéma.

| Brique | Rôle |
| --- | --- |
| **Schéma Prisma** (`schema.prisma`) | Décrit ta base : connexion, modèles, relations. C'est la source de vérité |
| **Prisma Client** | Bibliothèque TypeScript générée à partir du schéma, avec laquelle tu fais tes requêtes |
| **Prisma Migrate** | Fait évoluer la structure de la base (créer, modifier des tables) de façon versionnée |
| **Prisma Studio** | Interface web pour voir et modifier les données dans le navigateur |

Le flux de travail est toujours le même :

1. tu décris tes données dans `schema.prisma` ;
2. tu appliques ce schéma à la base avec une migration ;
3. Prisma génère un client typé adapté **exactement** à ton schéma ;
4. tu utilises ce client dans ton code.

Si tu renommes un champ dans le schéma, le client est régénéré et TypeScript te signale immédiatement tous les endroits du code à corriger. C'est la grande force de Prisma : le schéma, la base et le code restent synchronisés.

## Installer l'environnement

### Une base PostgreSQL avec Docker

Le plus simple pour travailler en local est de lancer PostgreSQL dans un conteneur :

```bash
docker run --name devroad-pg \
  -e POSTGRES_USER=dev \
  -e POSTGRES_PASSWORD=dev \
  -e POSTGRES_DB=boutique \
  -p 5432:5432 \
  -d postgres:16
```

Cette commande crée une base nommée `boutique`, accessible sur le port 5432 avec l'utilisateur `dev`. Pour l'arrêter puis la relancer plus tard :

```bash
docker stop devroad-pg
docker start devroad-pg
```

### Un projet TypeScript minimal

Crée un dossier et installe les outils :

```bash
mkdir boutique && cd boutique
npm init -y
npm install typescript tsx @types/node --save-dev
npm install prisma --save-dev
npm install @prisma/client
npx tsc --init
```

Quelques précisions utiles :

- `prisma` est la **ligne de commande** (CLI) : elle ne sert qu'en développement, donc en `--save-dev` ;
- `@prisma/client` est la bibliothèque utilisée par ton application ;
- `tsx` exécute directement un fichier TypeScript, sans étape de compilation.

### Initialiser Prisma

```bash
npx prisma init --datasource-provider postgresql
```

La commande crée deux éléments : un dossier `prisma/` avec le fichier `schema.prisma`, et un fichier `.env` contenant l'URL de connexion. Remplace cette URL par celle de ta base Docker :

```bash
DATABASE_URL="postgresql://dev:dev@localhost:5432/boutique?schema=public"
```

L'URL se lit ainsi : `postgresql://utilisateur:motdepasse@hôte:port/base?schema=schéma`.

> **Attention** : le fichier `.env` contient des secrets. Vérifie qu'il figure dans ton `.gitignore` avant ton premier commit. Ne publie jamais une vraie URL de base de données sur GitHub.

## Lire le fichier schema.prisma

Ouvre `prisma/schema.prisma`. Il se compose de trois types de blocs :

```prisma
generator client {
  provider = "prisma-client-js"
}

datasource db {
  provider = "postgresql"
  url      = env("DATABASE_URL")
}

model Produit {
  id    Int    @id @default(autoincrement())
  nom   String
  prix  Int
}
```

- **`generator`** indique à Prisma ce qu'il doit générer. Ici, le client JavaScript/TypeScript ;
- **`datasource`** décrit la base : son type (`postgresql`) et l'URL, lue dans la variable d'environnement `DATABASE_URL` ;
- **`model`** décrit une table. Chaque ligne du bloc est un champ avec un **nom**, un **type** et des **attributs** optionnels commençant par `@`.

Dans le modèle `Produit`, `id` est un entier, clé primaire (`@id`), qui s'incrémente automatiquement (`@default(autoincrement())`). Le nom du modèle est écrit en PascalCase et au singulier, par convention. Prisma crée la table correspondante dans PostgreSQL.

Tu vas approfondir chaque type et attribut dans le chapitre suivant. Pour l'instant, retiens simplement la structure.

## Première migration et génération du client

Pour créer la table `Produit` dans PostgreSQL, tu lances une migration :

```bash
npx prisma migrate dev --name init
```

Cette commande fait trois choses d'un coup :

1. elle compare ton schéma à la base et génère un fichier SQL dans `prisma/migrations/` ;
2. elle exécute ce SQL sur la base ;
3. elle (re)génère Prisma Client.

Si tu veux seulement régénérer le client, par exemple après avoir cloné un projet :

```bash
npx prisma generate
```

Prisma Client est généré dans `node_modules/.prisma/client`, d'où l'import `@prisma/client` qui fonctionne partout dans ton projet.

:::quiz
À quoi sert la commande `npx prisma generate` ?
- [ ] À créer les tables dans PostgreSQL
- [x] À générer le client TypeScript typé à partir du schéma
- [ ] À supprimer toutes les données
- [ ] À démarrer le serveur PostgreSQL
> `generate` produit Prisma Client d'après `schema.prisma`. La création des tables passe par les migrations (`migrate dev`), qui régénèrent aussi le client.
:::

## Ta première requête avec Prisma Client

Crée un fichier `index.ts` à la racine :

```ts
import { PrismaClient } from '@prisma/client';

const prisma = new PrismaClient();

async function main() {
  // 1. Créer un produit
  const cree = await prisma.produit.create({
    data: { nom: 'Casque audio', prix: 25000 },
  });
  console.log('Créé :', cree);

  // 2. Lire tous les produits
  const produits = await prisma.produit.findMany();
  console.log('Tous les produits :', produits);
}

main()
  .catch((erreur) => {
    console.error(erreur);
    process.exit(1);
  })
  .finally(async () => {
    await prisma.$disconnect();
  });
```

Lance-le :

```bash
npx tsx index.ts
```

Le terminal affiche un objet du type `{ id: 1, nom: 'Casque audio', prix: 25000 }`. Observe plusieurs détails importants :

- `prisma.produit` : le modèle `Produit` devient une propriété en **camelCase** (première lettre en minuscule) ;
- `create` et `findMany` sont des méthodes fournies par le client ;
- les résultats sont **typés** : survole `cree` dans ton éditeur, tu verras `id: number`, `nom: string`, `prix: number` ;
- `$disconnect()` ferme proprement la connexion à la fin du script.

Essaie de te tromper volontairement :

```ts
await prisma.produit.create({
  data: { nom: 'Souris', prix: 'gratuit' }, // Erreur TypeScript : prix doit être un nombre
});
```

TypeScript signale l'erreur **avant** l'exécution. Voilà ce que Prisma t'apporte par rapport au SQL brut.

> **Astuce** : active le journal des requêtes pour voir le SQL généré. Écris `new PrismaClient({ log: ['query'] })`. C'est la meilleure façon de comprendre ce que Prisma fait derrière tes appels.

## Visualiser les données avec Prisma Studio

Prisma Studio est une interface web pour consulter et modifier tes tables :

```bash
npx prisma studio
```

Ouvre l'adresse indiquée (généralement `http://localhost:5555`). Tu y vois tes modèles à gauche, les lignes au centre, et tu peux ajouter, éditer ou supprimer des enregistrements. C'est très pratique pour vérifier qu'un script a bien fait ce que tu attendais, sans écrire de requête SQL.

:::quiz
Dans `schema.prisma`, que contient le bloc `datasource` ?
- [ ] La liste des requêtes de l'application
- [ ] Le code de Prisma Client
- [x] Le type de base de données et l'URL de connexion
- [ ] Les données initiales à insérer
> Le bloc `datasource` indique le fournisseur (`postgresql`) et l'URL, généralement lue dans une variable d'environnement avec `env("DATABASE_URL")`.
:::

## Atelier guidé : ta première boutique

Compte quarante-cinq minutes. Tu pars d'un dossier vide.

1. Démarre PostgreSQL avec la commande Docker `docker run` du chapitre et vérifie avec `docker ps` que le conteneur tourne.
2. Crée le projet, installe `prisma`, `@prisma/client`, `typescript` et `tsx`, puis lance `npx prisma init --datasource-provider postgresql`.
3. Remplace `DATABASE_URL` dans `.env` par l'URL de ta base et ajoute `.env` à `.gitignore`.
4. Dans `schema.prisma`, déclare un modèle `Produit` avec `id`, `nom` et `prix`.
5. Lance `npx prisma migrate dev --name init` et ouvre le fichier SQL généré dans `prisma/migrations/` pour lire le `CREATE TABLE`.
6. Crée `index.ts` avec trois produits (via `create`), puis affiche-les avec `findMany`.
7. Active `log: ['query']` et repère les requêtes `INSERT` et `SELECT` dans le terminal.
8. Ouvre Prisma Studio, modifie le prix d'un produit à la main, puis relance `findMany` pour voir le changement.
9. Provoque une erreur de type volontaire (par exemple un `prix` en texte) et observe le message de l'éditeur.

Pour t'auto-évaluer, réponds sans regarder le cours : quelle est la différence entre `prisma generate` et `prisma migrate dev` ? Où est stockée l'URL de connexion, et pourquoi n'est-elle pas dans le code ?

## Erreurs fréquentes

- **Oublier de lancer `migrate dev` après avoir modifié le schéma.** Le client est à jour mais la table n'existe pas : tu obtiens une erreur « table does not exist ».
- **Une `DATABASE_URL` incorrecte.** Vérifie le port, le nom de la base et le mot de passe. L'erreur `P1001` signifie que le serveur est injoignable (souvent Docker arrêté).
- **Écrire `prisma.Produit` au lieu de `prisma.produit`.** Le client expose les modèles en camelCase.
- **Créer un `new PrismaClient()` à chaque requête.** Chaque instance ouvre ses propres connexions. Crée une instance unique et réutilise-la.
- **Committer le fichier `.env`.** Tes identifiants se retrouvent publics. Ajoute-le au `.gitignore` dès le départ.
- **Oublier `await`.** Les méthodes de Prisma renvoient des promesses : sans `await`, tu manipules une promesse et non le résultat.

## Bonnes pratiques

- Considère `schema.prisma` comme la **source de vérité** : on modifie le schéma, jamais les tables à la main.
- Versionne le dossier `prisma/migrations/` dans Git, mais jamais le `.env`.
- Nomme tes modèles au singulier et en PascalCase (`Produit`, `Utilisateur`).
- Active le journal des requêtes pendant le développement pour apprendre le SQL généré.
- Appelle `$disconnect()` à la fin de tes scripts ponctuels.
- Utilise Prisma Studio pour vérifier rapidement l'état des données, pas pour remplacer des scripts reproductibles.

## À retenir

- Un **ORM** traduit ton code en SQL et les résultats en objets ; Prisma y ajoute un typage fort.
- Prisma repose sur le **schéma**, **Prisma Client**, **Prisma Migrate** et **Prisma Studio**.
- `prisma init` crée `schema.prisma` et `.env` ; l'URL de connexion se lit via `env("DATABASE_URL")`.
- `migrate dev` crée la migration, l'applique et régénère le client ; `generate` ne fait que régénérer le client.
- Les modèles du client sont en camelCase : `prisma.produit.findMany()`.
- Les résultats sont typés, ce qui détecte les erreurs avant l'exécution.
