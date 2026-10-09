---
title: Relations et contraintes
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Une base de données n'est pas un simple stockage : c'est un **gardien de la cohérence**. Dans ce chapitre, tu apprends à relier les tables entre elles et à poser des règles que PostgreSQL fait respecter à chaque écriture. Tu apprendras aussi à interroger plusieurs tables à la fois avec des jointures, et à résumer des données avec des agrégations.

À la fin du chapitre, tu seras capable de :

- poser les contraintes `NOT NULL`, `UNIQUE`, `CHECK`, `DEFAULT` ;
- déclarer des clés étrangères avec `ON DELETE CASCADE`, `RESTRICT` ou `SET NULL` ;
- modéliser une relation plusieurs-à-plusieurs avec une table pivot ;
- relier des tables avec `JOIN` et `LEFT JOIN` ;
- calculer des totaux avec `COUNT`, `SUM`, `AVG`, `GROUP BY` et `HAVING` ;
- faire évoluer un schéma avec `ALTER TABLE`.

Prérequis : les chapitres 1 et 2. Prévois deux heures et demie.

## Les contraintes : des règles dans la base

Une **contrainte** est une règle vérifiée à chaque `INSERT` et `UPDATE`. Si la règle n'est pas respectée, PostgreSQL refuse l'opération avec une erreur explicite. C'est bien plus fiable que de valider seulement dans le code de l'application : un script, un import ou un autre service ne contourne pas la base.

| Contrainte | Garantit |
| --- | --- |
| `NOT NULL` | la colonne ne peut pas être vide |
| `DEFAULT` | une valeur est fournie quand on n'en donne pas |
| `UNIQUE` | pas de doublon dans la colonne (ou le groupe de colonnes) |
| `PRIMARY KEY` | unique, non nulle : identifie chaque ligne |
| `CHECK` | une condition est vraie pour chaque ligne |
| `FOREIGN KEY` | la valeur existe dans la table référencée |

Voici la table `roadmaps` réécrite avec des contraintes **nommées**, ce qui rend les messages d'erreur lisibles :

```sql
DROP TABLE IF EXISTS roadmap_steps, roadmaps;

CREATE TABLE roadmaps (
  id            bigint GENERATED ALWAYS AS IDENTITY,
  user_id       bigint      NOT NULL,
  title         text        NOT NULL,
  slug          text        NOT NULL,
  level         text        NOT NULL DEFAULT 'beginner',
  is_published  boolean     NOT NULL DEFAULT false,
  created_at    timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT roadmaps_pkey        PRIMARY KEY (id),
  CONSTRAINT roadmaps_slug_key    UNIQUE (slug),
  CONSTRAINT roadmaps_level_chk   CHECK (level IN ('beginner', 'intermediate', 'professional')),
  CONSTRAINT roadmaps_title_chk   CHECK (length(trim(title)) > 0),
  CONSTRAINT roadmaps_user_fk     FOREIGN KEY (user_id) REFERENCES users (id)
);
```

La contrainte `CHECK` valide une règle métier : le niveau doit appartenir à une liste fermée et le titre ne peut pas être vide. Essaie d'insérer le niveau `'expert'` : PostgreSQL répond `ERROR: new row for relation "roadmaps" violates check constraint "roadmaps_level_chk"`. Le nom de la contrainte te dit immédiatement où chercher.

> **Astuce** : pour une liste de valeurs possibles, PostgreSQL propose aussi les types `ENUM`. Le `CHECK ... IN (...)` est pourtant plus simple à faire évoluer : ajouter une valeur ne demande qu'à remplacer la contrainte.

## Les clés étrangères et ON DELETE

La clé étrangère garantit qu'une roadmap pointe vers un utilisateur **qui existe**. Reste à décider ce qui se passe quand on supprime l'utilisateur. La clause `ON DELETE` choisit le comportement :

| Option | Effet à la suppression de la ligne parente |
| --- | --- |
| `NO ACTION` / `RESTRICT` (défaut) | la suppression est refusée s'il reste des enfants |
| `CASCADE` | les enfants sont supprimés automatiquement |
| `SET NULL` | la clé étrangère des enfants devient `NULL` |
| `SET DEFAULT` | elle prend la valeur par défaut de la colonne |

Les étapes n'ont de sens que dans leur roadmap : `CASCADE` est naturel.

```sql
CREATE TABLE roadmap_steps (
  id          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  roadmap_id  bigint  NOT NULL REFERENCES roadmaps (id) ON DELETE CASCADE,
  title       text    NOT NULL,
  position    integer NOT NULL,
  minutes     integer,
  done        boolean NOT NULL DEFAULT false,
  CONSTRAINT steps_position_chk CHECK (position >= 1),
  CONSTRAINT steps_minutes_chk  CHECK (minutes IS NULL OR minutes > 0),
  CONSTRAINT steps_position_uq  UNIQUE (roadmap_id, position)
);
```

La contrainte `UNIQUE (roadmap_id, position)` porte sur deux colonnes : deux étapes peuvent avoir la position 1 dans deux roadmaps différentes, mais pas dans la même.

Pour l'auteur d'une roadmap, au contraire, garde `RESTRICT` : supprimer un compte ne doit pas effacer silencieusement ses contenus publiés. Retiens la règle : **`CASCADE` pour la composition (le fils n'existe pas sans le père), `RESTRICT` pour le reste.**

:::quiz
Que se passe-t-il quand on supprime une roadmap dont les étapes ont une clé étrangère en ON DELETE CASCADE ?
- [ ] La suppression est refusée
- [ ] Les étapes restent avec un roadmap_id invalide
- [x] Les étapes de cette roadmap sont supprimées automatiquement
- [ ] Les étapes sont déplacées vers une autre roadmap
> CASCADE propage la suppression aux lignes enfants. Avec RESTRICT, la suppression aurait été refusée tant qu'il reste des étapes.
:::

## Plusieurs-à-plusieurs : la table pivot

Un utilisateur peut suivre plusieurs roadmaps, et une roadmap a plusieurs abonnés. On crée une table de liaison à clé primaire composée :

```sql
CREATE TABLE enrollments (
  user_id      bigint      NOT NULL REFERENCES users (id)    ON DELETE CASCADE,
  roadmap_id   bigint      NOT NULL REFERENCES roadmaps (id) ON DELETE CASCADE,
  enrolled_at  timestamptz NOT NULL DEFAULT now(),
  PRIMARY KEY (user_id, roadmap_id)
);

INSERT INTO enrollments (user_id, roadmap_id) VALUES (2, 1), (3, 1), (3, 2);
```

La clé primaire `(user_id, roadmap_id)` empêche l'inscription en double du même utilisateur à la même roadmap.

## Les jointures : recombiner les tables

Les données sont réparties dans plusieurs tables. Une **jointure** les recombine à la lecture. Reprenons les données du fil rouge :

| roadmaps.id | user_id | title |
| --- | --- | --- |
| 1 | 1 | Laravel |
| 2 | 1 | React |
| 3 | 2 | Docker |

### JOIN : les correspondances uniquement

```sql
SELECT r.title AS roadmap, u.name AS auteur
FROM roadmaps AS r
JOIN users AS u ON u.id = r.user_id
ORDER BY r.id;
```

| roadmap | auteur |
| --- | --- |
| Laravel | Awa |
| React | Awa |
| Docker | Koffi |

`JOIN` (équivalent à `INNER JOIN`) ne garde que les lignes qui ont une correspondance des deux côtés. La condition `ON` relie la clé étrangère à la clé primaire. Les alias `r` et `u` raccourcissent l'écriture.

### LEFT JOIN : garder les lignes sans correspondance

Pour lister **tous** les utilisateurs, y compris ceux sans roadmap, utilise `LEFT JOIN`. Les colonnes de la table de droite valent `NULL` quand il n'y a pas de correspondance :

```sql
SELECT u.name, r.title
FROM users AS u
LEFT JOIN roadmaps AS r ON r.user_id = u.id;
```

| name | title |
| --- | --- |
| Awa | Laravel |
| Awa | React |
| Koffi | Docker |
| Mariam | NULL |

Cette propriété permet de détecter les lignes orphelines : ajoute `WHERE r.id IS NULL` et tu obtiens les utilisateurs qui n'ont rien publié.

### Joindre trois tables

On enchaîne les `JOIN` :

```sql
SELECT u.name, r.title AS roadmap, s.title AS etape, s.minutes
FROM roadmap_steps AS s
JOIN roadmaps AS r ON r.id = s.roadmap_id
JOIN users    AS u ON u.id = r.user_id
ORDER BY r.id, s.position;
```

PostgreSQL propose aussi `FULL JOIN` (toutes les lignes des deux côtés) et `CROSS JOIN` (toutes les combinaisons), plus rares. Retiens surtout `JOIN` et `LEFT JOIN`, qui couvrent 95 % des besoins.

## Les agrégations

Une **agrégation** résume plusieurs lignes en une valeur : `COUNT` compte, `SUM` additionne, `AVG` calcule la moyenne, `MIN` et `MAX` donnent les extrêmes. Elles **ignorent les `NULL`** (sauf le comptage global des lignes).

```sql
SELECT
  count(*)       AS nb_etapes,
  count(minutes) AS nb_avec_duree,
  sum(minutes)   AS total,
  round(avg(minutes), 1) AS moyenne
FROM roadmap_steps;
```

| nb_etapes | nb_avec_duree | total | moyenne |
| --- | --- | --- | --- |
| 5 | 4 | 420 | 105.0 |

La moyenne est de 420 / 4, car l'étape « Hooks » sans durée est ignorée.

### GROUP BY et HAVING

`GROUP BY` forme des groupes de lignes et calcule l'agrégation pour chacun. `HAVING` filtre ensuite les groupes (alors que `WHERE` filtre les lignes avant le regroupement) :

```sql
SELECT r.title,
       count(s.id)                 AS nb_etapes,
       coalesce(sum(s.minutes), 0) AS total_minutes
FROM roadmaps AS r
LEFT JOIN roadmap_steps AS s ON s.roadmap_id = r.id
GROUP BY r.id, r.title
HAVING count(s.id) >= 1
ORDER BY total_minutes DESC;
```

| title | nb_etapes | total_minutes |
| --- | --- | --- |
| Laravel | 3 | 360 |
| React | 2 | 60 |

Docker, sans étape, est éliminé par `HAVING`. Deux règles à retenir. Tout champ du `SELECT` doit être dans le `GROUP BY` ou dans une fonction d'agrégation. Et on compte `s.id` plutôt que toutes les lignes après un `LEFT JOIN`, pour ne pas compter la ligne vide de Docker comme 1.

PostgreSQL ajoute une fonctionnalité élégante, `FILTER`, pour compter conditionnellement :

```sql
SELECT roadmap_id,
       count(*) AS total,
       count(*) FILTER (WHERE done) AS terminees
FROM roadmap_steps
GROUP BY roadmap_id;
```

:::quiz
Tu veux garder uniquement les roadmaps qui ont au moins 3 étapes. Où places-tu la condition sur count(s.id) ?
- [ ] Dans le WHERE
- [x] Dans le HAVING
- [ ] Dans le ORDER BY
- [ ] Dans le ON de la jointure
> HAVING filtre les groupes après l'agrégation. WHERE agit avant le regroupement et ne peut pas utiliser une fonction d'agrégation.
:::

## ALTER TABLE : faire évoluer le schéma

Un schéma évolue avec l'application. `ALTER TABLE` ajoute, modifie ou retire des colonnes et contraintes sur une table qui contient déjà des données :

```sql
ALTER TABLE roadmaps ADD COLUMN description text;
ALTER TABLE roadmaps ADD COLUMN deleted_at timestamptz;
ALTER TABLE users ADD CONSTRAINT users_email_chk CHECK (email LIKE '%@%');
ALTER TABLE roadmap_steps RENAME COLUMN done TO is_done;
ALTER TABLE roadmaps DROP COLUMN description;
```

Pour ajouter une colonne `NOT NULL` à une table déjà remplie, fournis une valeur par défaut, sinon PostgreSQL ne sait pas quoi mettre dans les lignes existantes. Dans un projet réel (Laravel, Prisma, Supabase CLI), tu écris ces changements dans des **migrations** versionnées, jamais à la main sur la production. Et comme PostgreSQL gère le DDL transactionnel, une migration peut s'exécuter dans un `BEGIN ... COMMIT` : si une étape échoue, toutes sont annulées.

## Atelier guidé : un schéma blindé

Compte une heure et demie.

1. Repars d'une base propre (ou supprime les tables) et écris `users`, `roadmaps`, `roadmap_steps` avec toutes les contraintes nommées du chapitre.
2. Crée la table pivot `enrollments` et insère quelques inscriptions.
3. Teste chaque contrainte : un e-mail en double, un niveau `'expert'`, une étape de -5 minutes, deux étapes à la même position, une roadmap avec un `user_id` inexistant. Note chaque message d'erreur.
4. Insère une roadmap avec trois étapes, supprime-la et vérifie que les étapes ont disparu (`CASCADE`).
5. Essaie de supprimer un utilisateur qui possède des roadmaps : constate le refus, puis explique pourquoi c'est le bon comportement.
6. Écris la requête qui affiche chaque roadmap avec son auteur et son nombre d'étapes, y compris celles sans étape.
7. Écris la requête qui liste les utilisateurs sans aucune inscription (`LEFT JOIN` + `IS NULL`).
8. Calcule par roadmap le pourcentage d'étapes terminées avec `FILTER`.
9. Ajoute une colonne `deleted_at` avec `ALTER TABLE`, dans une transaction que tu valides avec `COMMIT`.

Pour t'auto-évaluer : pour chaque contrainte, explique quel bug elle évite, et dis pourquoi `CASCADE` est adapté aux étapes mais pas aux auteurs.

## Erreurs fréquentes

- **Se contenter de valider dans le code.** Les données sales arrivent par d'autres chemins.
- **Oublier `NOT NULL`.** Les colonnes obligatoires acceptent du vide et faussent les calculs.
- **Utiliser `CASCADE` partout.** Une suppression anodine efface des données en chaîne.
- **Oublier la condition `ON`.** Le produit cartésien explose le nombre de lignes.
- **Utiliser un `JOIN` quand il faut un `LEFT JOIN`.** Des lignes disparaissent du résultat.
- **Mélanger types incompatibles entre clé étrangère et clé primaire** (`integer` contre `bigint`).
- **Mettre une agrégation dans un `WHERE`.** Il faut `HAVING`.

## Bonnes pratiques

- Écris les contraintes dès la création de la table et donne-leur des noms explicites.
- Indexe les colonnes de clés étrangères : PostgreSQL ne le fait pas automatiquement (voir chapitre 5).
- Choisis `ON DELETE` selon le sens métier et documente-le.
- Une relation plusieurs-à-plusieurs passe toujours par une table pivot à clé primaire composée.
- Utilise des alias de tables courts et préfixe les colonnes dans les jointures.
- Versionne tout changement de schéma dans des migrations.

## À retenir

- Les contraintes (`NOT NULL`, `UNIQUE`, `CHECK`, clés étrangères) protègent les données à la source.
- `ON DELETE` choisit le comportement à la suppression : `RESTRICT`, `CASCADE` ou `SET NULL`.
- `JOIN` garde les correspondances ; `LEFT JOIN` garde toute la table de gauche.
- `GROUP BY` regroupe, `HAVING` filtre les groupes, `FILTER` compte sous condition.
- Une relation plusieurs-à-plusieurs se modélise avec une table pivot.
- `ALTER TABLE` fait évoluer le schéma, idéalement via des migrations transactionnelles.
