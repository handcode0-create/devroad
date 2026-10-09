---
title: SQL et CRUD
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Tu as un serveur PostgreSQL et une table `users`. Il est temps d'apprendre le cœur du langage : les quatre opérations **CRUD** (*Create, Read, Update, Delete*). Tu apprendras aussi à filtrer, trier, paginer, et à profiter d'une fonctionnalité très appréciée de PostgreSQL : la clause `RETURNING`, qui renvoie les lignes modifiées sans requête supplémentaire.

À la fin du chapitre, tu seras capable de :

- insérer une ou plusieurs lignes avec `INSERT` et récupérer l'identifiant créé avec `RETURNING` ;
- lire avec `SELECT`, choisir des colonnes, utiliser des alias et des expressions ;
- filtrer avec `WHERE`, `AND`, `OR`, `IN`, `BETWEEN`, `LIKE` et `ILIKE` ;
- gérer `NULL` correctement ;
- trier avec `ORDER BY`, paginer avec `LIMIT` et `OFFSET` ;
- modifier avec `UPDATE`, supprimer avec `DELETE`, et réaliser un « upsert » avec `ON CONFLICT`.

Prérequis : le chapitre 1 et un serveur PostgreSQL 16 accessible avec `psql`. Prévois deux heures et demie.

## Préparer les tables

Crée les trois tables du fil rouge. Nous détaillerons les contraintes au chapitre suivant ; ici, on veut surtout des données à manipuler.

```sql
CREATE TABLE users (
  id          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name        text NOT NULL,
  email       text NOT NULL UNIQUE,
  created_at  timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE roadmaps (
  id            bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  user_id       bigint NOT NULL REFERENCES users (id),
  title         text NOT NULL,
  slug          text NOT NULL UNIQUE,
  level         text NOT NULL DEFAULT 'beginner',
  is_published  boolean NOT NULL DEFAULT false,
  created_at    timestamptz NOT NULL DEFAULT now()
);

CREATE TABLE roadmap_steps (
  id          bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  roadmap_id  bigint NOT NULL REFERENCES roadmaps (id),
  title       text NOT NULL,
  position    integer NOT NULL,
  minutes     integer,
  done        boolean NOT NULL DEFAULT false
);
```

## INSERT : ajouter des lignes

On nomme les colonnes, puis les valeurs dans le même ordre. Les colonnes omises prennent leur valeur par défaut :

```sql
INSERT INTO users (name, email)
VALUES ('Awa', 'awa@mail.ci'),
       ('Koffi', 'koffi@mail.ci'),
       ('Mariam', 'mariam@mail.ci');

INSERT INTO roadmaps (user_id, title, slug, level, is_published) VALUES
  (1, 'Laravel', 'laravel', 'intermediate', true),
  (1, 'React',   'react',   'beginner', true),
  (2, 'Docker',  'docker',  'beginner', false);

INSERT INTO roadmap_steps (roadmap_id, title, position, minutes, done) VALUES
  (1, 'Routes', 1, 90, true),
  (1, 'Controllers', 2, 120, true),
  (1, 'Eloquent', 3, 150, false),
  (2, 'JSX', 1, 60, true),
  (2, 'Hooks', 2, NULL, false);
```

### RETURNING : récupérer ce qu'on vient de créer

Dans une application, on a souvent besoin de l'identifiant généré. En PostgreSQL, `RETURNING` le renvoie directement, sans second appel :

```sql
INSERT INTO roadmaps (user_id, title, slug)
VALUES (3, 'SQL', 'sql')
RETURNING id, slug, created_at;
```

| id | slug | created_at |
| --- | --- | --- |
| 4 | sql | 2026-10-09 10:20:41+00 |

`RETURNING` fonctionne aussi avec `UPDATE` et `DELETE`. C'est l'une des fonctionnalités les plus pratiques de PostgreSQL.

## SELECT : lire des données

La forme minimale lit toutes les colonnes d'une table. Pour l'exploration, c'est pratique ; dans le code d'une application, nomme les colonnes dont tu as besoin :

```sql
SELECT id, title, level FROM roadmaps;
```

| id | title | level |
| --- | --- | --- |
| 1 | Laravel | intermediate |
| 2 | React | beginner |
| 3 | Docker | beginner |
| 4 | SQL | beginner |

Un **alias** renomme une colonne dans le résultat, avec `AS`. On peut aussi calculer :

```sql
SELECT title AS titre, round(minutes / 60.0, 1) AS heures
FROM roadmap_steps;
```

Observe `60.0` : en PostgreSQL, la division de deux entiers est **entière** (90 / 60 donne 1). Pour un résultat décimal, il faut qu'un des opérandes soit décimal. `DISTINCT` supprime les doublons :

```sql
SELECT DISTINCT level FROM roadmaps ORDER BY level;
```

L'opérateur `||` concatène du texte :

```sql
SELECT name || ' <' || email || '>' AS contact FROM users;
```

## WHERE : filtrer les lignes

`WHERE` ne garde que les lignes qui vérifient une condition. Les comparaisons sont `=`, `<>` (ou `!=`), `<`, `>`, `<=`, `>=`. On les combine avec `AND`, `OR` et `NOT`.

```sql
SELECT title FROM roadmaps
WHERE level = 'beginner' AND is_published;
```

Comme `is_published` est déjà un booléen, on peut l'écrire seul sans `= true`. D'autres opérateurs très utiles :

```sql
-- appartenance à une liste
SELECT title FROM roadmaps WHERE level IN ('beginner', 'intermediate');

-- intervalle, bornes incluses
SELECT title FROM roadmap_steps WHERE minutes BETWEEN 60 AND 120;

-- motif de texte (sensible à la casse)
SELECT title FROM roadmaps WHERE title LIKE 'La%';

-- motif insensible à la casse : spécifique à PostgreSQL
SELECT title FROM roadmaps WHERE title ILIKE '%react%';
```

Le joker `%` remplace n'importe quelle suite de caractères, `_` un seul caractère. **`LIKE` est sensible à la casse** en PostgreSQL : cherche `'la%'` et tu ne trouveras pas « Laravel ». Utilise `ILIKE` pour ignorer la casse.

> **Attention** : `AND` est évalué avant `OR`. Quand tu mélanges les deux, ajoute des parenthèses : `WHERE a AND (b OR c)`.

:::quiz
Tu cherches toutes les roadmaps dont le titre contient « docker », quelle que soit la casse. Quelle clause convient ?
- [ ] WHERE title LIKE 'docker'
- [ ] WHERE title = '%docker%'
- [x] WHERE title ILIKE '%docker%'
- [ ] WHERE title CONTAINS 'docker'
> ILIKE est la variante insensible à la casse de LIKE, propre à PostgreSQL. Les jokers % permettent de chercher le mot n'importe où dans le titre.
:::

## NULL : l'absence de valeur

Pour l'étape « Hooks », `minutes` vaut `NULL`, c'est-à-dire « inconnu ». `NULL` n'est ni 0 ni une chaîne vide. Toute comparaison avec `NULL` n'est ni vraie ni fausse, elle est inconnue, donc `WHERE minutes = NULL` ne renvoie jamais rien. On utilise des opérateurs spéciaux :

```sql
SELECT title FROM roadmap_steps WHERE minutes IS NULL;
SELECT title FROM roadmap_steps WHERE minutes IS NOT NULL;
```

Pour remplacer `NULL` par une valeur par défaut à l'affichage, utilise `COALESCE` :

```sql
SELECT title, COALESCE(minutes, 0) AS minutes FROM roadmap_steps;
```

PostgreSQL propose aussi `IS DISTINCT FROM`, une comparaison qui traite `NULL` comme une valeur : `a IS DISTINCT FROM b` est vrai quand les valeurs diffèrent, y compris si l'une est `NULL`.

## ORDER BY, LIMIT et OFFSET

L'ordre des lignes n'est **jamais garanti** sans `ORDER BY`. Par défaut le tri est croissant (`ASC`) ; ajoute `DESC` pour l'inverser. PostgreSQL place les `NULL` en dernier en tri croissant, et tu peux le contrôler :

```sql
SELECT title, minutes
FROM roadmap_steps
ORDER BY minutes DESC NULLS LAST, title;
```

`LIMIT` plafonne le nombre de lignes et `OFFSET` en saute :

```sql
-- les 3 dernières roadmaps créées
SELECT id, title FROM roadmaps ORDER BY created_at DESC LIMIT 3;

-- page 2 avec 2 résultats par page
SELECT id, title FROM roadmaps ORDER BY id LIMIT 2 OFFSET 2;
```

La formule : `OFFSET = (numéro_de_page - 1) × taille_de_page`. Associe toujours `LIMIT` à un `ORDER BY` stable, avec une colonne unique en dernier critère. Sur de très grandes tables, `OFFSET` devient lent car la base doit lire puis jeter toutes les lignes sautées ; on préfère alors la **pagination par curseur** : `WHERE id > :dernier_id ORDER BY id LIMIT 20`.

## UPDATE : modifier des lignes

`UPDATE` change des valeurs. `SET` dit quoi, `WHERE` dit où :

```sql
UPDATE roadmaps
SET is_published = true
WHERE id = 3
RETURNING id, title, is_published;
```

Grâce à `RETURNING`, tu vois immédiatement le résultat. Une expression peut utiliser l'ancienne valeur :

```sql
UPDATE roadmap_steps
SET minutes = minutes + 15
WHERE roadmap_id = 1 AND position = 3;
```

> **Erreur fréquente** : oublier le `WHERE`. Un `UPDATE` sans `WHERE` modifie toutes les lignes de la table.

## DELETE : supprimer des lignes

```sql
DELETE FROM roadmap_steps
WHERE roadmap_id = 2 AND NOT done
RETURNING id, title;
```

La clause `RETURNING` t'affiche ce qui a été supprimé, ce qui sert de journal. Pour vider une table entière, `TRUNCATE TABLE roadmap_steps;` est bien plus rapide mais irréversible hors transaction (et elle refuse de vider une table référencée par une clé étrangère, sauf avec `CASCADE`).

Pour supprimer des lignes en toute sécurité, la bonne habitude est d'abord un `SELECT` avec le même `WHERE`, puis le `DELETE`. Mieux encore : lance l'opération dans une **transaction**. Si le résultat te déplaît, `ROLLBACK` annule tout :

```sql
BEGIN;
DELETE FROM roadmap_steps WHERE roadmap_id = 1;   -- oups, trop large ?
ROLLBACK;                                          -- rien n'est perdu
```

PostgreSQL est l'une des rares bases où même la plupart des changements de structure (`ALTER TABLE`, `DROP TABLE`) peuvent être annulés dans une transaction.

## L'upsert avec ON CONFLICT

Un besoin courant : « insère cette ligne, mais si elle existe déjà, mets-la à jour ». PostgreSQL propose `INSERT ... ON CONFLICT`, souvent appelé **upsert** :

```sql
INSERT INTO users (name, email)
VALUES ('Awa Koné', 'awa@mail.ci')
ON CONFLICT (email)
DO UPDATE SET name = EXCLUDED.name
RETURNING id, name;
```

`EXCLUDED` désigne la ligne qu'on a tenté d'insérer. Ici, comme l'e-mail existe déjà (contrainte `UNIQUE`), la ligne existante est mise à jour au lieu de provoquer une erreur. Pour simplement ignorer le doublon, écris `ON CONFLICT (email) DO NOTHING`.

:::quiz
Quelle clause permet de récupérer l'identifiant généré lors d'un INSERT en PostgreSQL, sans second appel ?
- [ ] SELECT LAST_INSERT_ID()
- [x] RETURNING id
- [ ] OUTPUT id
- [ ] GET id
> RETURNING renvoie les colonnes demandées pour les lignes insérées, modifiées ou supprimées. LAST_INSERT_ID est une fonction de MySQL.
:::

## Atelier guidé : gérer les données de DevRoad

Compte une heure et demie.

1. Crée les trois tables et insère les données de départ du chapitre.
2. Affiche les utilisateurs triés par nom, avec leur e-mail en minuscules (`lower(email)`).
3. Affiche les roadmaps publiées de niveau `beginner`.
4. Affiche les étapes de 60 à 120 minutes, de la plus longue à la plus courte.
5. Affiche les étapes sans durée avec `IS NULL`, puis toutes les étapes avec `COALESCE(minutes, 30)`.
6. Recherche les roadmaps contenant « a » dans le titre avec `ILIKE`, puis compare avec `LIKE` pour constater la différence de casse.
7. Insère un utilisateur avec `RETURNING id`, puis crée une roadmap à son nom avec l'identifiant obtenu.
8. Utilise un `UPDATE ... RETURNING` pour passer la roadmap Docker en publiée.
9. Fais un upsert sur un utilisateur existant : change son nom via `ON CONFLICT (email) DO UPDATE`.
10. Ouvre une transaction, supprime des étapes, vérifie avec `SELECT`, puis termine par `ROLLBACK` et confirme que tout est revenu.
11. Affiche la page 2 des roadmaps avec 2 lignes par page.

Pour t'auto-évaluer : sans relire, écris la requête qui renvoie les 3 étapes non terminées les plus longues, en plaçant les durées inconnues à la fin.

## Erreurs fréquentes

- **Oublier le `WHERE` d'un `UPDATE` ou d'un `DELETE`.** Toute la table est concernée.
- **Écrire `= NULL`.** Utilise `IS NULL`.
- **Utiliser `LIKE` en croyant qu'il ignore la casse.** Prends `ILIKE`.
- **Diviser deux entiers en attendant un décimal.** `5 / 2` donne 2 ; écris `5 / 2.0`.
- **Utiliser des guillemets doubles pour du texte.** Ils désignent des identifiants, le texte va entre apostrophes.
- **Paginer sans `ORDER BY`.** Les pages ne sont pas stables.
- **Ne pas utiliser `RETURNING`** et refaire un `SELECT` pour retrouver la ligne qu'on vient d'écrire.

## Bonnes pratiques

- Nomme toujours les colonnes dans un `INSERT` et dans un `SELECT` d'application.
- Utilise `RETURNING` pour obtenir identifiants et valeurs calculées en un seul aller-retour.
- Vérifie les modifications de masse par un `SELECT` préalable ou dans une transaction avec `ROLLBACK`.
- Place `ORDER BY` avec une colonne unique en dernier critère avant tout `LIMIT`.
- Préfère `ON CONFLICT` à la combinaison « je vérifie puis j'insère », qui est sujette aux conflits entre requêtes simultanées.
- Passe toujours les valeurs variables par des paramètres liés (`$1`, `$2`) et jamais par concaténation de texte.

## À retenir

- Le CRUD se fait avec `INSERT`, `SELECT`, `UPDATE`, `DELETE`.
- `RETURNING` renvoie les lignes écrites, modifiées ou supprimées sans requête supplémentaire.
- `WHERE` filtre ; `LIKE` est sensible à la casse, `ILIKE` ne l'est pas ; `NULL` se teste avec `IS NULL`.
- `ORDER BY` trie, `LIMIT` plafonne, `OFFSET` saute ; pour les gros volumes, préfère la pagination par curseur.
- `ON CONFLICT` réalise un upsert : `DO UPDATE` ou `DO NOTHING`.
- Les transactions (`BEGIN`, `ROLLBACK`) sont ton filet de sécurité avant une suppression en masse.
