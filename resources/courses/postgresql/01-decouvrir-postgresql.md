---
title: Découvrir PostgreSQL
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

PostgreSQL, souvent appelé simplement « Postgres », est une base de données relationnelle open source réputée pour sa rigueur, sa richesse fonctionnelle et sa fiabilité. C'est la base de prédilection de nombreuses équipes pour de nouvelles applications, et c'est le moteur qui se cache derrière des services comme Supabase, très utilisé dans l'écosystème Next.js. Comprendre PostgreSQL te donne donc un avantage direct sur tes projets.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est une base relationnelle et ce qui distingue PostgreSQL ;
- installer PostgreSQL 16 avec Docker et te connecter avec `psql` ;
- utiliser les commandes `psql` essentielles (`\l`, `\c`, `\dt`, `\d`) ;
- créer une base, un schéma et une première table ;
- choisir les bons types de colonnes (`text`, `integer`, `boolean`, `timestamptz`) ;
- comprendre l'architecture : cluster, bases, schémas, tables, rôles.

Prérequis : savoir ouvrir un terminal. Aucune connaissance de SQL n'est exigée, on le découvre pas à pas. Prévois deux heures.

## Pourquoi PostgreSQL ?

Une base relationnelle range les données dans des **tables** (lignes et colonnes) reliées entre elles par des **clés**. MySQL, SQLite, SQL Server ou Oracle fonctionnent sur le même principe. PostgreSQL se distingue par plusieurs atouts :

- **le respect strict de SQL** : il applique les règles et refuse les données invalides plutôt que de les corriger en silence ;
- **des types riches** : JSON binaire (`jsonb`), tableaux, intervalles, types géographiques avec l'extension PostGIS ;
- **des fonctionnalités avancées** : expressions de table commune, fonctions de fenêtre, index partiels, recherche plein texte intégrée ;
- **des transactions solides** grâce au modèle MVCC : les lectures ne bloquent pas les écritures ;
- **une extensibilité** exceptionnelle : on ajoute des fonctionnalités par extensions (`pgcrypto`, `pg_trgm`, `pgvector` pour l'IA...).

Il est gratuit, développé par une communauté mondiale depuis plus de vingt-cinq ans, et disponible chez tous les hébergeurs cloud. La version majeure utilisée dans ce cours est la **16**.

## Installer PostgreSQL avec Docker

Le moyen le plus propre et le plus reproductible est un conteneur Docker. Une seule commande suffit :

```bash
docker run --name pg-devroad \
  -e POSTGRES_PASSWORD=secret \
  -e POSTGRES_DB=devroad \
  -p 5432:5432 \
  -v pgdata:/var/lib/postgresql/data \
  -d postgres:16
```

Chaque option a son rôle : `POSTGRES_PASSWORD` définit le mot de passe du superutilisateur `postgres`, `POSTGRES_DB` crée une base `devroad` au démarrage, `-p 5432:5432` expose le port standard de PostgreSQL, et `-v pgdata:...` crée un **volume** pour que tes données survivent à la suppression du conteneur. Vérifie que tout tourne :

```bash
docker ps
docker logs pg-devroad --tail 5
```

Tu dois lire un message du type « database system is ready to accept connections ». Si tu ne veux pas utiliser Docker, installe PostgreSQL depuis postgresql.org ou via ton gestionnaire de paquets, ou crée une base gratuite sur Supabase ou Neon.

## Se connecter avec psql

`psql` est le client en ligne de commande officiel. Lance-le dans le conteneur :

```bash
docker exec -it pg-devroad psql -U postgres -d devroad
```

Le prompt devient `devroad=#`. Le `#` indique que tu es connecté en superutilisateur ; pour un utilisateur normal, ce serait `>`. Tu peux maintenant taper du SQL, qui doit se terminer par un point-virgule :

```sql
SELECT version();
SELECT now();
```

Le premier affiche la version de PostgreSQL, le second la date et l'heure du serveur. Pour quitter, tape `\q`.

### Les méta-commandes de psql

Les commandes qui commencent par un **antislash** ne sont pas du SQL : elles appartiennent à `psql`. Apprends celles-ci, tu les utiliseras tous les jours :

| Commande | Rôle |
| --- | --- |
| `\l` | liste les bases de données |
| `\c nom_base` | se connecte à une autre base |
| `\dt` | liste les tables du schéma courant |
| `\d nom_table` | décrit une table (colonnes, index, contraintes) |
| `\dn` | liste les schémas |
| `\du` | liste les rôles (utilisateurs) |
| `\x` | bascule l'affichage étendu, utile pour les lignes larges |
| `\timing` | affiche le temps d'exécution des requêtes |
| `\i fichier.sql` | exécute un fichier SQL |
| `\?` | affiche l'aide des commandes `psql` |
| `\q` | quitte |

> **Astuce** : active `\timing` dès le début de la séance. Prendre l'habitude de voir le temps de chaque requête te rend sensible à la performance.

:::quiz
Quelle commande psql affiche la structure d'une table (colonnes, index, contraintes) ?
- [ ] \dt nom_table
- [x] \d nom_table
- [ ] \l nom_table
- [ ] SHOW TABLE nom_table
> \d nom_table décrit la table. \dt liste les tables, \l liste les bases. SHOW TABLE n'existe pas en PostgreSQL.
:::

## L'architecture : cluster, bases, schémas, tables

PostgreSQL organise ses objets en niveaux emboîtés, un peu comme des dossiers :

```text
Serveur (cluster)
└── Base de données  : devroad
    └── Schéma       : public
        └── Table    : users, roadmaps, roadmap_steps
```

- Un **cluster** est une instance de PostgreSQL qui écoute sur un port.
- Un cluster contient plusieurs **bases de données**, isolées les unes des autres : une requête ne peut pas joindre deux bases.
- Une base contient plusieurs **schémas**, qui sont des espaces de noms. Le schéma par défaut s'appelle `public`. Supabase en utilise plusieurs (`auth`, `storage`...).
- Un schéma contient les **tables**, les vues, les fonctions, etc.

Tu peux créer un schéma pour ranger tes tables par domaine :

```sql
CREATE SCHEMA IF NOT EXISTS app;
```

Pour désigner une table d'un schéma, on écrit `schéma.table`, par exemple `app.users`. Sans préfixe, PostgreSQL cherche dans le `search_path`, qui vaut par défaut `"$user", public`.

Les **rôles** représentent les utilisateurs et les groupes. Le rôle `postgres` est le superutilisateur ; en production, on crée des rôles dédiés à droits limités (chapitre 6).

## Les types de données

PostgreSQL propose un grand choix de types. Pour débuter, retiens ceux-ci :

| Besoin | Type PostgreSQL | Exemple |
| --- | --- | --- |
| Identifiant | `integer`, `bigint`, `uuid` | `42` |
| Texte | `text` (ou `varchar(n)`) | `'Awa'` |
| Vrai ou faux | `boolean` | `true` |
| Nombre exact (prix) | `numeric(10,2)` | `1500.00` |
| Nombre à virgule approché | `double precision` | `3.14` |
| Date seule | `date` | `2026-10-09` |
| Date et heure avec fuseau | `timestamptz` | `2026-10-09 14:30:00+00` |
| Données JSON | `jsonb` | `{"lang": "fr"}` |

Trois conseils propres à PostgreSQL. D'abord, `text` et `varchar` ont la **même performance** : tu n'as aucune raison de limiter artificiellement la longueur, sauf règle métier. Ensuite, utilise **toujours `timestamptz`** (*timestamp with time zone*) plutôt que `timestamp` : il stocke un instant absolu, indépendant du fuseau, ce qui évite des bugs d'heure quand ton application et tes utilisateurs ne sont pas dans le même fuseau. Enfin, pour l'argent, `numeric` plutôt que `double precision`, dont l'arrondi est approximatif.

## Créer une première table

Voici la table `users` de DevRoad, écrite en PostgreSQL moderne :

```sql
CREATE TABLE users (
  id          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name        text        NOT NULL,
  email       text        NOT NULL UNIQUE,
  created_at  timestamptz NOT NULL DEFAULT now()
);
```

Décortiquons la ligne `id`. `GENERATED ALWAYS AS IDENTITY` demande à PostgreSQL de générer automatiquement un numéro croissant (1, 2, 3...). C'est l'équivalent moderne de l'ancien type `serial` et de l'`AUTO_INCREMENT` de MySQL. `PRIMARY KEY` en fait l'identifiant unique de la ligne. `now()` renvoie l'instant courant : la colonne se remplit seule.

Insère deux lignes et lis-les :

```sql
INSERT INTO users (name, email)
VALUES ('Awa', 'awa@mail.ci'), ('Koffi', 'koffi@mail.ci');

SELECT * FROM users;
```

Résultat :

| id | name | email | created_at |
| --- | --- | --- | --- |
| 1 | Awa | awa@mail.ci | 2026-10-09 10:12:03+00 |
| 2 | Koffi | koffi@mail.ci | 2026-10-09 10:12:03+00 |

Essaie de réinsérer `awa@mail.ci` : PostgreSQL répond par `ERROR: duplicate key value violates unique constraint "users_email_key"`. C'est la contrainte `UNIQUE` qui te protège.

## Le schéma de DevRoad en PostgreSQL

Voici le fil rouge des sept chapitres. Retiens-le :

```text
users          id (PK), name, email (unique), created_at
roadmaps       id (PK), user_id (FK -> users), title, slug (unique),
               level, is_published, created_at
roadmap_steps  id (PK), roadmap_id (FK -> roadmaps), title,
               position, minutes, done
```

Un utilisateur crée plusieurs roadmaps ; une roadmap contient plusieurs étapes ordonnées. Les clés étrangères relient les tables et garantissent qu'une roadmap pointe toujours vers un utilisateur existant. Nous les construirons au chapitre 3. Pour l'instant, crée seulement `users`.

## PostgreSQL face à MySQL : les différences à connaître

Si tu viens de MySQL, ou si tu apprends les deux, voici les principaux points de vigilance :

| Sujet | MySQL | PostgreSQL |
| --- | --- | --- |
| Auto-incrément | `AUTO_INCREMENT` | `GENERATED ... AS IDENTITY` |
| Guillemets pour les noms | accents graves | guillemets doubles `"` |
| Texte | apostrophes (et guillemets) | **apostrophes uniquement** |
| Sensibilité à la casse des valeurs | souvent insensible | **sensible** (`'Awa'` est différent de `'awa'`) |
| Limiter les lignes | `LIMIT n` | `LIMIT n` (identique) |
| Booléen | `TINYINT(1)` | vrai type `boolean` |
| Types | plus limités | très riches (`jsonb`, tableaux...) |

Une conséquence importante : en PostgreSQL, les identifiants non entourés de guillemets sont convertis en **minuscules**. Écris tes noms de tables et colonnes en minuscules avec des underscores (`created_at`), et tu n'auras jamais besoin de guillemets.

:::quiz
Quel type est recommandé pour stocker la date de création d'une ligne dans une application utilisée dans plusieurs fuseaux horaires ?
- [ ] timestamp
- [ ] varchar(30)
- [x] timestamptz
- [ ] date
> timestamptz stocke un instant absolu et gère les fuseaux horaires. Le type timestamp ignore le fuseau, ce qui crée des erreurs d'heure.
:::

## Atelier guidé : ton premier serveur PostgreSQL

Compte une heure.

1. Lance le conteneur PostgreSQL 16 avec la commande `docker run` du chapitre et vérifie avec `docker ps` qu'il tourne.
2. Connecte-toi avec `psql` et exécute `SELECT version();`. Note le numéro de version.
3. Active `\timing`, puis liste les bases avec `\l` et les schémas avec `\dn`.
4. Crée la table `users` avec les colonnes et contraintes du chapitre, puis vérifie sa structure avec `\d users`.
5. Insère trois utilisateurs (Awa, Koffi, Mariam) avec une seule instruction `INSERT`.
6. Affiche-les avec `SELECT`, puis essaie d'insérer un doublon d'e-mail et lis le message d'erreur.
7. Crée un schéma `app`, puis une table `app.notes` (`id`, `contenu`). Observe avec la commande `\dn` puis `\d app.notes` qu'elle est rangée à part.
8. Arrête puis redémarre le conteneur avec `docker stop` puis `docker start`, reconnecte-toi et vérifie que tes données sont toujours là grâce au volume.

Pour t'auto-évaluer : explique la différence entre un cluster, une base et un schéma, et pourquoi on préfère `timestamptz` à `timestamp`.

## Erreurs fréquentes

- **Oublier le point-virgule final.** `psql` attend la suite et affiche un prompt `devroad-#` : termine avec `;`.
- **Écrire du texte entre guillemets doubles.** Ils désignent des noms d'objets ; le texte va entre apostrophes.
- **Utiliser des majuscules dans les noms de tables.** Il faudrait ensuite les citer partout entre guillemets.
- **Choisir `timestamp` au lieu de `timestamptz`.** Les heures sont fausses dès qu'un fuseau change.
- **Oublier le volume Docker.** En supprimant le conteneur, tu perds la base.
- **Confondre méta-commande et SQL.** `\dt` n'a pas de point-virgule et ne marche que dans `psql`.

## Bonnes pratiques

- Écris les mots-clés SQL en majuscules et les noms d'objets en `snake_case` minuscule.
- Choisis `bigint GENERATED ALWAYS AS IDENTITY` pour les clés primaires numériques.
- Utilise `text`, `timestamptz`, `boolean` et `numeric` par défaut.
- Range les tables d'une application dans un schéma dédié ou dans `public`, mais reste cohérent.
- Ne te connecte pas avec `postgres` depuis ton application : crée un rôle dédié (chapitre 6).
- Garde la documentation officielle (postgresql.org/docs) ouverte : elle est excellente et en grande partie bien écrite.

## À retenir

- PostgreSQL est une base relationnelle open source, stricte, riche en types et en fonctionnalités avancées.
- Un cluster contient des bases ; une base contient des schémas ; un schéma contient des tables.
- `psql` est ton client : apprends `\l`, `\c`, `\dt`, `\d`, `\timing` et `\q`.
- Utilise `text`, `boolean`, `numeric` et `timestamptz`, et `GENERATED ALWAYS AS IDENTITY` pour les identifiants.
- Texte entre apostrophes, noms d'objets en minuscules : PostgreSQL est strict et sensible à la casse des valeurs.
- Le fil rouge : `users`, `roadmaps`, `roadmap_steps`, reliés par des clés étrangères.
