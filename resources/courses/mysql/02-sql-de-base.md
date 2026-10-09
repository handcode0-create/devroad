---
title: SQL de base
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Tu sais maintenant comment les données sont organisées. Il est temps de les manipuler. SQL se résume à quatre gestes que les développeurs appellent le **CRUD** : *Create* (créer), *Read* (lire), *Update* (modifier), *Delete* (supprimer). Ce chapitre t'apprend à les faire correctement, et surtout à **filtrer, trier et paginer** ce que tu lis.

À la fin du chapitre, tu seras capable de :

- insérer des lignes avec `INSERT` ;
- lire des données avec `SELECT`, choisir les colonnes et renommer avec des alias ;
- filtrer avec `WHERE` et les opérateurs de comparaison, `AND`, `OR`, `IN`, `LIKE`, `BETWEEN` ;
- gérer les valeurs absentes avec `NULL` ;
- trier avec `ORDER BY` et paginer avec `LIMIT` et `OFFSET` ;
- modifier avec `UPDATE` et supprimer avec `DELETE` sans te tromper de lignes.

Prérequis : le chapitre 1, et un serveur MySQL 8 qui fonctionne (conteneur Docker de l'atelier précédent). Prévois deux heures et demie.

## Préparer les données

Pour pratiquer, crée les trois tables du fil rouge et remplis-les. Ces instructions sont un peu longues : copie-colle-les dans ton client MySQL.

```sql
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE roadmaps (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL UNIQUE,
  level VARCHAR(20) NOT NULL DEFAULT 'beginner',
  is_published BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id)
);

CREATE TABLE roadmap_steps (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  roadmap_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  position INT NOT NULL,
  minutes INT NULL,
  done BOOLEAN NOT NULL DEFAULT FALSE,
  FOREIGN KEY (roadmap_id) REFERENCES roadmaps(id)
);
```

Nous détaillerons les contraintes au chapitre 4. Pour l'instant, fais-leur confiance.

## INSERT : ajouter des lignes

L'instruction `INSERT INTO` ajoute une ligne. On nomme les colonnes, puis les valeurs dans le **même ordre** :

```sql
INSERT INTO users (name, email)
VALUES ('Awa', 'awa@mail.ci');
```

Tu n'as pas fourni `id` ni `created_at` : MySQL les remplit seul (auto-incrément et valeur par défaut). On peut aussi insérer plusieurs lignes d'un coup, ce qui est plus rapide :

```sql
INSERT INTO users (name, email) VALUES
  ('Koffi', 'koffi@mail.ci'),
  ('Mariam', 'mariam@mail.ci');

INSERT INTO roadmaps (user_id, title, slug, level, is_published) VALUES
  (1, 'Laravel', 'laravel', 'intermediate', TRUE),
  (1, 'React', 'react', 'beginner', TRUE),
  (2, 'Docker', 'docker', 'beginner', FALSE);

INSERT INTO roadmap_steps (roadmap_id, title, position, minutes, done) VALUES
  (1, 'Routes', 1, 90, TRUE),
  (1, 'Controllers', 2, 120, TRUE),
  (1, 'Eloquent', 3, 150, FALSE),
  (2, 'JSX', 1, 60, TRUE),
  (2, 'Hooks', 2, NULL, FALSE);
```

Les textes se mettent entre **apostrophes simples**. Pour afficher l'identifiant que MySQL vient de générer, utilise `SELECT LAST_INSERT_ID();`.

## SELECT : lire des données

`SELECT` est l'instruction que tu utiliseras le plus. Sa forme minimale :

```sql
SELECT * FROM roadmaps;
```

L'étoile signifie « toutes les colonnes ». Pratique pour explorer, mais en code d'application, **nomme les colonnes** dont tu as besoin :

```sql
SELECT id, title, level FROM roadmaps;
```

Résultat :

| id | title | level |
| --- | --- | --- |
| 1 | Laravel | intermediate |
| 2 | React | beginner |
| 3 | Docker | beginner |

Un **alias** renomme une colonne ou une table dans le résultat, avec `AS` :

```sql
SELECT title AS titre, minutes / 60 AS heures
FROM roadmap_steps;
```

Tu peux faire des calculs dans le `SELECT`. Avec `DISTINCT`, tu élimines les doublons :

```sql
SELECT DISTINCT level FROM roadmaps;
```

| level |
| --- |
| intermediate |
| beginner |

> **Astuce** : écris tes requêtes sur plusieurs lignes, un mot-clé par ligne (`SELECT`, `FROM`, `WHERE`...). Elles deviennent lisibles même quand elles grossissent.

## WHERE : filtrer les lignes

Sans filtre, `SELECT` renvoie tout. `WHERE` garde seulement les lignes qui vérifient une condition :

```sql
SELECT title, level
FROM roadmaps
WHERE level = 'beginner';
```

Les opérateurs de comparaison sont `=`, `<>` (différent), `<`, `>`, `<=` et `>=`. Attention : en SQL, l'égalité s'écrit avec un seul `=`.

On combine les conditions avec `AND` (les deux), `OR` (l'une ou l'autre) et `NOT` :

```sql
SELECT title
FROM roadmaps
WHERE level = 'beginner' AND is_published = TRUE;
```

Quelques opérateurs très pratiques :

```sql
-- plusieurs valeurs possibles
SELECT title FROM roadmaps WHERE level IN ('beginner', 'intermediate');

-- intervalle (bornes incluses)
SELECT title, minutes FROM roadmap_steps WHERE minutes BETWEEN 60 AND 120;

-- recherche de texte : % remplace n'importe quelle suite de caractères
SELECT title FROM roadmaps WHERE title LIKE 'La%';
SELECT title FROM roadmaps WHERE title LIKE '%act%';
```

`LIKE 'La%'` trouve tout ce qui **commence** par « La » ; `LIKE '%act%'` tout ce qui **contient** « act ». Le caractère `_` remplace un seul caractère.

> **Attention** : quand tu mélanges `AND` et `OR`, mets des **parenthèses**. `AND` est évalué avant `OR`, donc `a OR b AND c` signifie `a OR (b AND c)`, ce qui n'est presque jamais ce que tu voulais.

:::quiz
Quelle requête renvoie les roadmaps dont le titre commence par « R » ?
- [ ] WHERE title = 'R%'
- [x] WHERE title LIKE 'R%'
- [ ] WHERE title LIKE '%R'
- [ ] WHERE title IN 'R'
> LIKE avec le joker % permet la recherche partielle. 'R%' signifie : commence par R, suivi de n'importe quoi. Avec = le % serait pris littéralement.
:::

## NULL : l'absence de valeur

Dans notre jeu de données, l'étape « Hooks » n'a pas de durée : `minutes` vaut `NULL`. **`NULL` ne veut pas dire zéro ni texte vide** : il signifie « inconnu ». Cela entraîne un piège classique, car aucune comparaison avec `NULL` n'est vraie, pas même l'égalité :

```sql
SELECT title FROM roadmap_steps WHERE minutes = NULL;   -- ne renvoie rien !
```

On utilise des opérateurs dédiés :

```sql
SELECT title FROM roadmap_steps WHERE minutes IS NULL;
SELECT title FROM roadmap_steps WHERE minutes IS NOT NULL;
```

Pour remplacer un `NULL` par une valeur par défaut à l'affichage, utilise `COALESCE` :

```sql
SELECT title, COALESCE(minutes, 0) AS minutes
FROM roadmap_steps;
```

Résultat :

| title | minutes |
| --- | --- |
| Routes | 90 |
| Controllers | 120 |
| Eloquent | 150 |
| JSX | 60 |
| Hooks | 0 |

## ORDER BY, LIMIT et OFFSET

Par défaut, l'ordre des lignes renvoyées n'est **pas garanti**. Si l'ordre compte, demande-le avec `ORDER BY`. `ASC` (croissant) est la valeur par défaut, `DESC` inverse :

```sql
SELECT title, minutes
FROM roadmap_steps
ORDER BY minutes DESC, title ASC;
```

On peut trier sur plusieurs colonnes : la seconde départage les égalités de la première. Pour limiter le nombre de lignes, ajoute `LIMIT` :

```sql
SELECT title FROM roadmaps ORDER BY created_at DESC LIMIT 5;
```

Cette requête donne « les 5 dernières roadmaps ». Pour une **pagination**, combine `LIMIT` et `OFFSET` (le nombre de lignes à sauter) :

```sql
-- page 3 avec 10 résultats par page : on saute 20 lignes
SELECT id, title FROM roadmaps ORDER BY id LIMIT 10 OFFSET 20;
```

La formule : `OFFSET = (numéro_de_page - 1) × taille_de_page`. Pense à toujours associer `LIMIT` à un `ORDER BY`, sinon les pages ne sont pas stables.

:::quiz
Tu affiches 20 roadmaps par page. Quelle clause donne la page 4 ?
- [ ] LIMIT 20 OFFSET 4
- [ ] LIMIT 4 OFFSET 20
- [x] LIMIT 20 OFFSET 60
- [ ] LIMIT 60 OFFSET 20
> On saute (4 - 1) × 20 = 60 lignes, puis on en lit 20.
:::

## UPDATE : modifier des lignes

`UPDATE` change les valeurs d'une ou plusieurs colonnes. La clause `SET` dit quoi changer, `WHERE` dit **où** :

```sql
UPDATE roadmaps
SET is_published = TRUE
WHERE id = 3;
```

On peut modifier plusieurs colonnes à la fois et utiliser un calcul :

```sql
UPDATE roadmap_steps
SET minutes = minutes + 15, done = FALSE
WHERE roadmap_id = 1 AND position = 3;
```

MySQL affiche ensuite le nombre de lignes modifiées (*rows affected*). Surveille ce chiffre : si tu attendais 1 et que tu en vois 500, tu as un problème.

> **Erreur fréquente** : oublier le `WHERE`. Un `UPDATE` sans `WHERE` modifie **toutes** les lignes de la table, sans demander confirmation.

## DELETE : supprimer des lignes

`DELETE` retire les lignes qui correspondent au filtre :

```sql
DELETE FROM roadmap_steps
WHERE roadmap_id = 2 AND done = FALSE;
```

Là encore, sans `WHERE`, tout disparaît. Autre point : à cause de la clé étrangère, tu ne peux pas supprimer une roadmap qui possède encore des étapes. MySQL refuse avec l'erreur 1451 (*Cannot delete or update a parent row*). Il faut d'abord supprimer les étapes, ou configurer une suppression en cascade (chapitre 4).

Pour vider entièrement une table, `TRUNCATE TABLE nom;` est beaucoup plus rapide, mais irréversible et il remet l'auto-incrément à zéro.

## Le réflexe de sécurité : SELECT avant UPDATE ou DELETE

Voici une habitude qui sauvera tes données. Avant toute modification en masse, **écris d'abord un `SELECT` avec le même `WHERE`**, regarde ce qu'il renvoie, puis remplace `SELECT ...` par `UPDATE` ou `DELETE` :

```sql
-- 1. je vérifie les lignes ciblées
SELECT id, title FROM roadmap_steps WHERE roadmap_id = 2 AND done = FALSE;

-- 2. seulement ensuite, je supprime
DELETE FROM roadmap_steps WHERE roadmap_id = 2 AND done = FALSE;
```

Dans la pratique professionnelle, on ajoute souvent `LIMIT 1` sur un `UPDATE` ou `DELETE` visant une ligne précise, comme garde-fou supplémentaire. Et le mode « mises à jour sécurisées » (`SET sql_safe_updates = 1;`) refuse les `UPDATE`/`DELETE` sans condition sur une clé.

## Atelier guidé : explorer et corriger les données de DevRoad

Compte une heure et demie. Utilise la base `devroad` préparée plus haut.

1. Affiche l'identifiant, le nom et l'e-mail de tous les utilisateurs, triés par nom.
2. Affiche les titres des roadmaps publiées de niveau `beginner`.
3. Affiche les étapes dont la durée est comprise entre 60 et 120 minutes, de la plus longue à la plus courte.
4. Trouve les étapes sans durée renseignée avec `IS NULL`, puis affiche les durées avec `COALESCE(minutes, 30)` pour supposer 30 minutes par défaut.
5. Recherche les roadmaps dont le titre contient la lettre « a » avec `LIKE`.
6. Ajoute un utilisateur « Fatou » et une roadmap « SQL » à son nom. Récupère l'`id` généré avec `LAST_INSERT_ID()` pour créer la roadmap.
7. Corrige la durée de l'étape « Hooks » à 80 minutes avec un `UPDATE` ciblé sur son `id`. Vérifie avec un `SELECT`.
8. Teste le réflexe de sécurité : écris le `SELECT` puis le `DELETE` pour retirer les étapes non terminées de la roadmap 2.
9. Affiche la deuxième page de 2 roadmaps (triées par `id`) avec `LIMIT` et `OFFSET`.

Pour t'auto-évaluer : sans relire le chapitre, écris de mémoire une requête qui renvoie les 3 étapes les plus longues non terminées, et explique pourquoi `= NULL` ne fonctionne pas.

## Erreurs fréquentes

- **Oublier le `WHERE` d'un `UPDATE` ou d'un `DELETE`.** Toute la table est touchée.
- **Écrire `= NULL` au lieu de `IS NULL`.** La comparaison ne renvoie jamais de ligne.
- **Utiliser des guillemets doubles pour du texte.** Reste sur les apostrophes simples, standard en SQL.
- **Mélanger `AND` et `OR` sans parenthèses.** Le résultat ne correspond pas à l'intention.
- **Paginer sans `ORDER BY`.** L'ordre n'étant pas garanti, des lignes peuvent apparaître deux fois ou jamais.
- **Prendre l'habitude de sélectionner « toutes les colonnes » avec l'étoile dans le code applicatif.** Tu transportes des colonnes inutiles et ton code casse si la table évolue.
- **Insérer sans nommer les colonnes.** Si la table change, l'ordre des valeurs ne correspond plus.

## Bonnes pratiques

- Nomme toujours les colonnes dans un `INSERT` et dans un `SELECT` de production.
- Vérifie toute modification de masse par un `SELECT` préalable, et lis le nombre de lignes affectées.
- Écris chaque clause sur sa propre ligne et mets les mots-clés SQL en majuscules.
- Associe toujours `LIMIT` à un `ORDER BY` déterministe, avec une colonne unique en dernier critère (souvent `id`).
- Ne construis jamais une requête en collant du texte venant d'un utilisateur : utilise des requêtes préparées (chapitre 6).

## À retenir

- Le CRUD se fait avec `INSERT`, `SELECT`, `UPDATE`, `DELETE`.
- `WHERE` filtre avec des comparaisons, `AND`, `OR`, `IN`, `BETWEEN` et `LIKE` ; parenthèses obligatoires quand on mélange `AND` et `OR`.
- `NULL` signifie « inconnu » : on le teste avec `IS NULL` et on le remplace avec `COALESCE`.
- `ORDER BY` trie, `LIMIT` plafonne, `OFFSET` saute ; page n = `OFFSET (n - 1) × taille`.
- Un `UPDATE` ou un `DELETE` sans `WHERE` touche toute la table : vérifie d'abord avec un `SELECT`.
