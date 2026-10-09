---
title: Fonctionnalités PostgreSQL
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Tu maîtrises le SQL classique. PostgreSQL va bien plus loin : il sait stocker des documents JSON, manipuler des tableaux, décomposer des problèmes complexes avec des expressions de table commune, calculer des classements et des totaux cumulés avec les fonctions de fenêtre, et faire de la recherche plein texte. Ces fonctionnalités changent la façon de concevoir une application, et elles sont une des raisons pour lesquelles on choisit Postgres plutôt qu'une autre base.

À la fin du chapitre, tu seras capable de :

- stocker et interroger des données semi-structurées avec **JSONB** ;
- utiliser des **tableaux** et les opérateurs associés ;
- écrire des requêtes lisibles avec les **CTE** (`WITH`), y compris récursives ;
- calculer classements, cumuls et comparaisons avec les **fonctions de fenêtre** ;
- créer des **vues** et des **vues matérialisées** ;
- mettre en place une **recherche plein texte** ;
- choisir entre `jsonb`, tableaux et tables relationnelles.

Prérequis : les chapitres 1 à 3 et les tables `users`, `roadmaps`, `roadmap_steps`. Prévois trois heures.

## JSONB : des documents dans une table

Il arrive qu'une donnée n'ait pas de structure fixe : les préférences d'un utilisateur, des métadonnées variables, la réponse brute d'une API de paiement. PostgreSQL propose deux types JSON ; **utilise toujours `jsonb`** (JSON binaire). Il est décomposé à l'écriture, indexable et rapide à interroger, contrairement à `json` qui garde le texte brut.

Ajoutons des préférences à `users` :

```sql
ALTER TABLE users ADD COLUMN settings jsonb NOT NULL DEFAULT '{}'::jsonb;

UPDATE users
SET settings = '{"lang": "fr", "theme": "dark", "notifications": {"email": true, "sms": false}}'
WHERE name = 'Awa';
```

Le suffixe `::jsonb` est un **cast** (conversion de type). Pour lire dedans, deux opérateurs essentiels : `->` renvoie du JSON, `->>` renvoie du **texte**.

```sql
SELECT name,
       settings ->> 'lang'                         AS langue,
       settings -> 'notifications' ->> 'email'     AS notif_email
FROM users
WHERE settings ->> 'theme' = 'dark';
```

| name | langue | notif_email |
| --- | --- | --- |
| Awa | fr | true |

D'autres opérateurs très utiles :

```sql
-- contient ce sous-document ?
SELECT name FROM users WHERE settings @> '{"lang": "fr"}';

-- la clé existe ?
SELECT name FROM users WHERE settings ? 'theme';

-- modifier une seule clé sans réécrire tout le document
UPDATE users
SET settings = jsonb_set(settings, '{theme}', '"light"')
WHERE name = 'Awa';

-- fusionner des documents
UPDATE users SET settings = settings || '{"beta": true}';
```

Pour accélérer les recherches avec `@>` et `?`, crée un **index GIN** (chapitre 5) :

```sql
CREATE INDEX users_settings_gin ON users USING gin (settings);
```

> **Attention** : `jsonb` n'est pas une excuse pour éviter de modéliser. Si tu filtres, joins ou valides toujours les mêmes champs, c'est que ce sont de vraies colonnes. Réserve `jsonb` à ce qui est vraiment variable.

:::quiz
Quelle expression renvoie la valeur de la clé lang d'une colonne jsonb sous forme de texte ?
- [ ] settings -> 'lang'
- [x] settings ->> 'lang'
- [ ] settings @> 'lang'
- [ ] settings ? 'lang'
> L'opérateur ->> extrait la valeur en texte, tandis que -> renvoie un objet jsonb. @> teste l'inclusion et ? teste l'existence d'une clé.
:::

## Les tableaux

PostgreSQL permet de stocker un tableau dans une colonne. Pratique pour de petites listes de valeurs simples, comme des étiquettes :

```sql
ALTER TABLE roadmaps ADD COLUMN tags text[] NOT NULL DEFAULT '{}';

UPDATE roadmaps SET tags = ARRAY['php', 'backend', 'framework'] WHERE slug = 'laravel';
UPDATE roadmaps SET tags = ARRAY['javascript', 'frontend'] WHERE slug = 'react';
```

On les interroge avec des opérateurs dédiés :

```sql
-- contient la valeur 'frontend' ?
SELECT title FROM roadmaps WHERE 'frontend' = ANY (tags);

-- contient tous ces éléments ?
SELECT title FROM roadmaps WHERE tags @> ARRAY['php', 'backend'];

-- dérouler un tableau en lignes
SELECT title, unnest(tags) AS tag FROM roadmaps;

-- regrouper des lignes en tableau
SELECT r.title, array_agg(s.title ORDER BY s.position) AS etapes
FROM roadmaps r JOIN roadmap_steps s ON s.roadmap_id = r.id
GROUP BY r.id, r.title;
```

La dernière requête renvoie pour Laravel : `{Routes,Controllers,Eloquent}`. `array_agg` et son cousin `string_agg(s.title, ', ')` sont précieux pour produire des résumés.

Un tableau est commode, mais il ne garantit pas l'intégrité : rien n'empêche des étiquettes mal orthographiées. Si les étiquettes sont gérées, partagées et comptées, une table `tags` avec une table pivot est préférable.

## Les expressions de table commune (CTE)

Une **CTE** (*Common Table Expression*) nomme un résultat intermédiaire avec `WITH`. Elle rend lisible une requête qu'il faudrait sinon écrire avec des sous-requêtes imbriquées. Calculons l'avancement de chaque roadmap, puis gardons celles au-dessus de 50 % :

```sql
WITH avancement AS (
  SELECT roadmap_id,
         count(*)                          AS total,
         count(*) FILTER (WHERE done)      AS terminees
  FROM roadmap_steps
  GROUP BY roadmap_id
)
SELECT r.title,
       a.terminees,
       a.total,
       round(100.0 * a.terminees / a.total) AS pourcentage
FROM avancement a
JOIN roadmaps r ON r.id = a.roadmap_id
WHERE a.terminees * 2 >= a.total
ORDER BY pourcentage DESC;
```

| title | terminees | total | pourcentage |
| --- | --- | --- | --- |
| Laravel | 2 | 3 | 67 |
| React | 1 | 2 | 50 |

On peut enchaîner plusieurs CTE séparées par des virgules, chacune pouvant utiliser les précédentes. Lis la requête de haut en bas, comme un script en étapes.

### CTE récursives

Une CTE peut s'appeler elle-même. C'est l'outil idéal pour parcourir des hiérarchies : catégories imbriquées, commentaires avec réponses, organigrammes. Imaginons une table de catégories avec un parent :

```sql
CREATE TABLE categories (
  id        bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name      text NOT NULL,
  parent_id bigint REFERENCES categories (id)
);

INSERT INTO categories (name, parent_id) VALUES
  ('Développement', NULL), ('Back-end', 1), ('Front-end', 1), ('PHP', 2), ('Laravel', 4);

WITH RECURSIVE arbre AS (
  SELECT id, name, parent_id, name::text AS chemin, 1 AS niveau
  FROM categories WHERE parent_id IS NULL
  UNION ALL
  SELECT c.id, c.name, c.parent_id, a.chemin || ' > ' || c.name, a.niveau + 1
  FROM categories c
  JOIN arbre a ON c.parent_id = a.id
)
SELECT chemin, niveau FROM arbre ORDER BY chemin;
```

| chemin | niveau |
| --- | --- |
| Développement | 1 |
| Développement > Back-end | 2 |
| Développement > Back-end > PHP | 3 |
| Développement > Back-end > PHP > Laravel | 4 |
| Développement > Front-end | 2 |

Une CTE récursive se compose d'une **requête d'ancrage** (les racines), de `UNION ALL`, puis d'une **partie récursive** qui joint la table à ce qui a déjà été produit. Elle s'arrête quand la partie récursive ne renvoie plus de ligne.

## Les fonctions de fenêtre

Une agrégation classique avec `GROUP BY` **écrase** les lignes. Une **fonction de fenêtre** (*window function*) calcule sur un ensemble de lignes **sans les regrouper** : chaque ligne d'origine reste visible, enrichie d'une valeur calculée. La syntaxe repose sur `OVER (...)`.

```sql
SELECT s.roadmap_id, s.position, s.title, s.minutes,
       sum(s.minutes) OVER (PARTITION BY s.roadmap_id ORDER BY s.position) AS cumul,
       row_number()   OVER (PARTITION BY s.roadmap_id ORDER BY s.position) AS rang
FROM roadmap_steps s
ORDER BY s.roadmap_id, s.position;
```

| roadmap_id | position | title | minutes | cumul | rang |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | Routes | 90 | 90 | 1 |
| 1 | 2 | Controllers | 120 | 210 | 2 |
| 1 | 3 | Eloquent | 150 | 360 | 3 |
| 2 | 1 | JSX | 60 | 60 | 1 |
| 2 | 2 | Hooks | NULL | 60 | 2 |

`PARTITION BY` découpe les lignes en groupes (ici par roadmap), `ORDER BY` définit l'ordre à l'intérieur de chaque groupe, et la fonction s'applique sur cette fenêtre. Le cumul repart de zéro à chaque roadmap.

Les fonctions de fenêtre les plus utiles :

| Fonction | Rôle |
| --- | --- |
| `row_number()` | numérote les lignes (1, 2, 3...) |
| `rank()` / `dense_rank()` | classement avec ex æquo (avec ou sans trous) |
| `lag(col)` / `lead(col)` | valeur de la ligne précédente / suivante |
| `sum() / avg() OVER` | cumul ou moyenne glissante |
| `first_value()` | première valeur de la fenêtre |

Un cas d'usage classique : « la dernière roadmap de chaque auteur », qu'on écrit en deux temps avec une CTE :

```sql
WITH classees AS (
  SELECT r.*,
         row_number() OVER (PARTITION BY r.user_id ORDER BY r.created_at DESC) AS rn
  FROM roadmaps r
)
SELECT user_id, title, created_at FROM classees WHERE rn = 1;
```

> **À retenir** : un `WHERE` ne peut pas filtrer sur une fonction de fenêtre. D'où le passage par une CTE ou une sous-requête pour filtrer sur `rn`.

:::quiz
Quelle est la différence essentielle entre GROUP BY et une fonction de fenêtre ?
- [ ] La fonction de fenêtre est plus lente et déconseillée
- [x] GROUP BY réduit les lignes d'un groupe en une seule, la fonction de fenêtre conserve toutes les lignes
- [ ] GROUP BY conserve toutes les lignes, la fonction de fenêtre les regroupe
- [ ] Il n'y a aucune différence
> GROUP BY produit une ligne par groupe. Une fonction de fenêtre calcule une valeur pour chaque ligne en regardant les lignes voisines, sans les fusionner.
:::

## Vues et vues matérialisées

Une **vue** est une requête enregistrée sous un nom. Elle se manipule comme une table en lecture, ce qui simplifie le code et masque la complexité (ou des colonnes sensibles) :

```sql
CREATE VIEW v_roadmap_stats AS
SELECT r.id, r.title, u.name AS auteur,
       count(s.id) AS nb_etapes,
       coalesce(sum(s.minutes), 0) AS total_minutes
FROM roadmaps r
JOIN users u ON u.id = r.user_id
LEFT JOIN roadmap_steps s ON s.roadmap_id = r.id
GROUP BY r.id, r.title, u.name;

SELECT * FROM v_roadmap_stats WHERE nb_etapes > 0;
```

Une vue recalcule sa requête à chaque lecture. Quand le calcul est coûteux et que les données changent peu, une **vue matérialisée** stocke le résultat sur disque :

```sql
CREATE MATERIALIZED VIEW mv_top_roadmaps AS
SELECT roadmap_id, count(*) AS inscrits
FROM enrollments
GROUP BY roadmap_id;

-- à relancer quand les données ont changé (par une tâche planifiée par exemple)
REFRESH MATERIALIZED VIEW mv_top_roadmaps;
```

Le compromis est clair : lecture quasi instantanée, mais données **potentiellement périmées** jusqu'au prochain `REFRESH`. Avec `REFRESH ... CONCURRENTLY` (qui exige un index unique), les lectures ne sont pas bloquées pendant la mise à jour.

## La recherche plein texte

Un `ILIKE '%mot%'` est lent et trouve seulement la chaîne exacte. PostgreSQL embarque une vraie **recherche plein texte** : elle découpe en mots, ignore les mots vides, ramène les mots à leur racine (les variantes d'un verbe), et classe les résultats par pertinence.

```sql
ALTER TABLE roadmaps ADD COLUMN description text;
UPDATE roadmaps SET description = 'Apprendre à construire des applications web avec le framework PHP Laravel';

SELECT title,
       ts_rank(to_tsvector('french', title || ' ' || coalesce(description, '')),
               plainto_tsquery('french', 'application web')) AS score
FROM roadmaps
WHERE to_tsvector('french', title || ' ' || coalesce(description, ''))
      @@ plainto_tsquery('french', 'application web')
ORDER BY score DESC;
```

`to_tsvector` transforme le texte en vecteur de mots normalisés pour la langue indiquée, `plainto_tsquery` transforme la recherche, et `@@` teste la correspondance. Pour la rapidité, on crée une colonne générée et un index GIN :

```sql
ALTER TABLE roadmaps ADD COLUMN search tsvector
  GENERATED ALWAYS AS (to_tsvector('french', title || ' ' || coalesce(description, ''))) STORED;

CREATE INDEX roadmaps_search_gin ON roadmaps USING gin (search);
```

Pour les fautes de frappe, l'extension `pg_trgm` (recherche par similarité de trigrammes) complète très bien : `CREATE EXTENSION pg_trgm;`.

## Autres fonctionnalités à connaître

- **Colonnes générées** (`GENERATED ALWAYS AS (...) STORED`) : valeur calculée automatiquement à partir d'autres colonnes.
- **`uuid`** : identifiants universels, avec `gen_random_uuid()` intégré, pratiques quand les identifiants sont exposés publiquement.
- **Types intervalle et plage** (`daterange`, `tstzrange`) : réservations sans chevauchement, grâce aux contraintes d'exclusion.
- **Extensions** : `pgcrypto`, `pg_trgm`, `uuid-ossp`, `postgis`, `pgvector` (recherche vectorielle pour l'IA).
- **`LATERAL`** : une sous-requête qui peut référencer les lignes qui la précèdent dans le `FROM`.

## Atelier guidé : enrichir DevRoad

Compte deux heures.

1. Ajoute une colonne `settings jsonb` à `users`. Remplis-la pour deux utilisateurs avec des préférences différentes, puis écris la requête qui trouve ceux dont le thème est `dark`.
2. Utilise `jsonb_set` pour changer la langue d'un utilisateur sans toucher aux autres clés, et `||` pour ajouter une clé.
3. Crée l'index GIN sur `settings` et vérifie avec `EXPLAIN` qu'une recherche `@>` l'utilise (sur un volume suffisant).
4. Ajoute la colonne `tags text[]` à `roadmaps`, remplis-la, puis trouve toutes les roadmaps portant l'étiquette `frontend`.
5. Avec `array_agg`, affiche chaque roadmap avec la liste ordonnée de ses étapes sur une ligne.
6. Écris une CTE qui calcule l'avancement par roadmap, puis garde celles à plus de 50 %.
7. Crée la table `categories` et écris la CTE récursive qui affiche l'arborescence complète avec le chemin.
8. Écris une requête avec `sum() OVER` pour afficher le cumul des minutes par roadmap, puis une autre avec `row_number()` pour obtenir la dernière roadmap de chaque auteur.
9. Crée la vue `v_roadmap_stats`, puis la vue matérialisée `mv_top_roadmaps` et teste son `REFRESH`.
10. Ajoute la recherche plein texte avec la colonne `search` et l'index GIN, puis recherche « framework php ».

Pour t'auto-évaluer : explique à voix haute dans quels cas tu choisis `jsonb`, un tableau, ou une table relationnelle, avec un exemple pour chacun.

## Erreurs fréquentes

- **Utiliser `json` au lieu de `jsonb`.** Tu perds l'indexation et la vitesse.
- **Confondre `->` et `->>`.** Le premier renvoie du JSON, le second du texte ; comparer du JSON à une chaîne ne fonctionne pas.
- **Tout mettre dans `jsonb`.** Tu perds les contraintes, les clés étrangères et la lisibilité du schéma.
- **Filtrer sur une fonction de fenêtre dans le `WHERE`.** Passe par une CTE.
- **Oublier `PARTITION BY`.** La fenêtre englobe alors toute la table.
- **Oublier de rafraîchir une vue matérialisée.** Les chiffres affichés sont périmés.
- **Écrire une CTE récursive sans condition d'arrêt**, ce qui provoque une boucle infinie sur des données cycliques.

## Bonnes pratiques

- Choisis `jsonb` pour les données variables, des vraies colonnes pour tout ce que tu filtres souvent.
- Indexe les colonnes `jsonb` et `tsvector` avec GIN.
- Découpe les requêtes complexes en CTE nommées clairement : le nom documente l'intention.
- Utilise les fonctions de fenêtre plutôt que des auto-jointures pour les classements et cumuls.
- Planifie le `REFRESH` des vues matérialisées (cron, pg_cron ou tâche applicative).
- Précise toujours la langue (`'french'`) dans les fonctions de recherche plein texte.

## À retenir

- `jsonb` stocke des documents indexables ; `->` renvoie du JSON, `->>` du texte, `@>` teste l'inclusion.
- Les tableaux conviennent aux petites listes simples ; au-delà, une table pivot s'impose.
- Une CTE (`WITH`) nomme des étapes intermédiaires ; `WITH RECURSIVE` parcourt les hiérarchies.
- Les fonctions de fenêtre calculent cumuls, classements et comparaisons sans écraser les lignes.
- Une vue simplifie, une vue matérialisée accélère mais doit être rafraîchie.
- La recherche plein texte repose sur `tsvector`, `tsquery`, `@@` et un index GIN.
