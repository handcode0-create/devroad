---
title: Projet final PostgreSQL
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru toute la chaîne : installation, SQL et CRUD, relations et contraintes, fonctionnalités avancées, index et transactions, administration. Ce chapitre final n'ajoute aucune notion. Il te demande de **tout mobiliser** dans un projet complet, de la même façon que pour un client : concevoir une base PostgreSQL 16, la construire, l'interroger avec les fonctionnalités avancées, l'optimiser, la sécuriser et prouver qu'elle se restaure.

Le projet : **le back-end de données de DevRoad**, une plateforme où des auteurs publient des roadmaps d'apprentissage composées d'étapes, et où des apprenants suivent ces roadmaps, cochent leur progression et reçoivent des recommandations.

À la fin du projet, tu auras :

- un schéma PostgreSQL normalisé avec contraintes, types adaptés et identifiants modernes ;
- un jeu de données volumineux généré avec `generate_series` ;
- des requêtes métier mobilisant jointures, CTE, fonctions de fenêtre et `jsonb` ;
- une recherche plein texte indexée ;
- des index justifiés par des `EXPLAIN ANALYZE` avant et après ;
- des fonctions transactionnelles fiables ;
- des rôles à droits limités, de la Row Level Security et une sauvegarde restaurée avec succès ;
- un dossier de livraison documenté et reproductible.

Prérequis : les chapitres 1 à 6. Durée : environ six heures, à répartir sur une ou plusieurs séances. Chaque étape décrit le résultat attendu et donne des extraits aux endroits les plus délicats.

## Le cahier des charges

**Comptes.** Un utilisateur a un nom, un e-mail unique (insensible à la casse), un mot de passe haché, un rôle (`learner`, `author`, `admin`), des préférences au format JSON et une date d'inscription. Un compte peut être désactivé sans être supprimé.

**Roadmaps.** Un auteur crée des roadmaps : titre, slug unique, description, niveau (`beginner`, `intermediate`, `professional`), état de publication, étiquettes. Une roadmap appartient à une catégorie, organisée en arbre (Développement, puis Back-end, puis PHP, par exemple).

**Étapes.** Une roadmap contient des étapes ordonnées : titre, contenu, durée optionnelle en minutes, position unique dans la roadmap.

**Suivi.** Un apprenant s'inscrit à une roadmap (une seule fois) et valide des étapes. On calcule son avancement. Chaque validation est horodatée.

**Recherche.** Les visiteurs recherchent des roadmaps par mots-clés en français (titre, description, étiquettes), avec classement par pertinence.

**Statistiques.** Tableau de bord : roadmaps les plus suivies, taux de complétion, apprenants actifs de la semaine, séries de jours consécutifs d'activité, classement des auteurs.

**Contraintes techniques.** PostgreSQL 16. La page d'accueil (20 dernières roadmaps publiées) et la recherche doivent répondre en moins de 50 ms avec 200 000 roadmaps. L'application ne doit jamais utiliser un compte superutilisateur. Un apprenant ne doit pouvoir lire et modifier que sa propre progression.

## Livrables

Tu produis un dépôt `devroad-pg/` :

```text
devroad-pg/
├── docker-compose.yml     # PostgreSQL 16 reproductible
├── sql/
│   ├── 01-schema.sql      # tables, types, contraintes
│   ├── 02-seed.sql        # données de démonstration
│   ├── 03-queries.sql     # requêtes métier commentées
│   ├── 04-search.sql      # recherche plein texte
│   ├── 05-indexes.sql     # index + EXPLAIN avant/après (commentaires)
│   ├── 06-functions.sql   # fonctions transactionnelles
│   └── 07-security.sql    # rôles, droits, RLS
├── backup/
│   ├── backup.sh
│   └── RESTORE.md
└── README.md
```

Pour rendre le projet reproductible, écris un `docker-compose.yml` qui lance PostgreSQL 16 avec un volume et qui exécute automatiquement les scripts SQL placés dans `/docker-entrypoint-initdb.d/`.

## Étape 1 : modéliser (40 minutes)

Dessine le diagramme entité-relation (papier, dbdiagram.io ou draw.io). Entités : `users`, `categories` (auto-référencée), `roadmaps`, `roadmap_steps`, `enrollments` (pivot), `step_completions` (pivot). Vérifie les trois formes normales du cours de base de données : pas de liste dans une colonne simple, pas de donnée dupliquée, pas de dépendance entre colonnes non clés.

Fais des choix de types conscients et note-les dans le README :

- identifiants : `bigint GENERATED ALWAYS AS IDENTITY`, ou `uuid` avec `gen_random_uuid()` si les identifiants sont exposés publiquement ;
- horodatages : `timestamptz` partout ;
- préférences : `jsonb` ; étiquettes : `text[]` (petite liste simple, sans gestion séparée) ;
- niveau et rôle : `text` avec `CHECK`, ou type `ENUM` si la liste est stable.

> **Astuce** : écris en une phrase la règle métier derrière chaque flèche et chaque colonne obligatoire. Chaque phrase deviendra une contrainte.

## Étape 2 : écrire le schéma (50 minutes)

Dans `01-schema.sql`, crée les extensions et les tables. Début de l'exemple, à compléter :

```sql
CREATE EXTENSION IF NOT EXISTS pg_trgm;
CREATE EXTENSION IF NOT EXISTS pgcrypto;

CREATE TABLE users (
  id             bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name           text        NOT NULL,
  email          text        NOT NULL,
  password_hash  text        NOT NULL,
  role           text        NOT NULL DEFAULT 'learner',
  settings       jsonb       NOT NULL DEFAULT '{}'::jsonb,
  is_active      boolean     NOT NULL DEFAULT true,
  created_at     timestamptz NOT NULL DEFAULT now(),
  CONSTRAINT users_role_chk  CHECK (role IN ('learner', 'author', 'admin')),
  CONSTRAINT users_email_chk CHECK (email LIKE '%@%')
);
CREATE UNIQUE INDEX users_email_lower_uq ON users (lower(email));

CREATE TABLE categories (
  id         bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
  name       text NOT NULL UNIQUE,
  parent_id  bigint REFERENCES categories (id) ON DELETE RESTRICT
);
```

Complète avec `roadmaps` (colonne `search tsvector` générée, étape 4), `roadmap_steps`, `enrollments` et `step_completions`. Exigences :

- chaque clé étrangère est nommée et son `ON DELETE` est justifié par écrit ;
- `roadmap_steps` possède `UNIQUE (roadmap_id, position)` et deux `CHECK` (position positive, durée positive ou nulle) ;
- `enrollments` : clé primaire `(user_id, roadmap_id)` ;
- `step_completions` : clé primaire `(user_id, step_id)` et `completed_at timestamptz NOT NULL DEFAULT now()` ;
- l'unicité de l'e-mail est insensible à la casse, via l'index unique d'expression ci-dessus ;
- chaque colonne de clé étrangère reçoit son index (PostgreSQL ne le crée pas tout seul).

Le script doit s'exécuter sans erreur du premier coup sur une base vide, et pouvoir être rejoué après `DROP SCHEMA public CASCADE; CREATE SCHEMA public;`.

## Étape 3 : alimenter la base (40 minutes)

Dans `02-seed.sql`, insère d'abord un petit jeu lisible : une arborescence de six catégories, 6 utilisateurs (dont 2 auteurs), 8 roadmaps, une trentaine d'étapes, des inscriptions et validations. Il servira à vérifier les résultats attendus des requêtes.

Puis génère un volume de test avec `generate_series`, en produisant des variations réalistes :

```sql
INSERT INTO roadmaps (user_id, category_id, title, slug, description, level, is_published, tags, created_at)
SELECT 1 + (g % 2),
       1 + (g % 6),
       'Roadmap ' || g || ' ' || (ARRAY['Laravel','React','Docker','SQL','Next.js'])[1 + g % 5],
       'roadmap-' || g,
       'Parcours pour apprendre ' || (ARRAY['le backend','le frontend','les bases de données','le déploiement'])[1 + g % 4],
       (ARRAY['beginner','intermediate','professional'])[1 + g % 3],
       g % 5 <> 0,
       ARRAY['tag' || (g % 20), 'tag' || (g % 7)],
       now() - (g % 365) * interval '1 day'
FROM generate_series(1, 200000) AS g;
```

Génère de la même façon des centaines de milliers d'étapes et de validations. Termine par `ANALYZE;` et note dans le README le nombre de lignes de chaque table.

## Étape 4 : requêtes métier avancées (65 minutes)

Dans `03-queries.sql`, écris les requêtes suivantes avec un commentaire sur le résultat attendu pour le petit jeu de données.

1. **Catalogue** : 20 dernières roadmaps publiées avec auteur, catégorie et nombre d'étapes.
2. **Avancement d'un apprenant** : pour chaque roadmap suivie, étapes validées, totales et pourcentage (clause `FILTER` sur `count`).
3. **Détail d'une roadmap** : étapes ordonnées avec durée cumulée (`sum() OVER (ORDER BY position)`).
4. **Arborescence des catégories** avec chemin complet et nombre de roadmaps (CTE récursive).
5. **Top 5 des roadmaps les plus suivies** avec leur rang (`dense_rank()`).
6. **Taux de complétion** par roadmap : part des inscrits ayant validé toutes les étapes.
7. **Dernière roadmap de chaque auteur** (`row_number() OVER (PARTITION BY ...)`).
8. **Apprenants actifs de la semaine** (au moins une validation sur 7 jours).
9. **Série de jours consécutifs** d'activité par apprenant : technique classique, `date - row_number()` pour regrouper les jours consécutifs.
10. **Préférences** : utilisateurs ayant le thème `dark` et les notifications e-mail activées (`jsonb`, opérateurs `->>` et `@>`).
11. **Étiquettes les plus utilisées** (`unnest(tags)` + `GROUP BY`).
12. **Étapes dont la durée dépasse la moyenne de leur roadmap** (`avg() OVER (PARTITION BY ...)`).

Exemple de départ pour la requête 9, à comprendre puis adapter :

```sql
WITH jours AS (
  SELECT DISTINCT user_id, completed_at::date AS jour
  FROM step_completions
),
numerotes AS (
  SELECT user_id, jour,
         jour - (row_number() OVER (PARTITION BY user_id ORDER BY jour))::int AS groupe
  FROM jours
)
SELECT user_id, min(jour) AS debut, max(jour) AS fin, count(*) AS nb_jours
FROM numerotes
GROUP BY user_id, groupe
ORDER BY nb_jours DESC;
```

Des jours consécutifs ont la même valeur `groupe` : on a soustrait le numéro de ligne à la date.

## Étape 5 : recherche plein texte (30 minutes)

Dans `04-search.sql`, ajoute une colonne générée `search tsvector` sur `roadmaps` combinant titre (poids A), description (poids B) et étiquettes (poids C), avec la configuration `'french'`, puis un index GIN :

```sql
ALTER TABLE roadmaps ADD COLUMN search tsvector GENERATED ALWAYS AS (
  setweight(to_tsvector('french', coalesce(title, '')), 'A') ||
  setweight(to_tsvector('french', coalesce(description, '')), 'B') ||
  setweight(to_tsvector('french', array_to_string(tags, ' ')), 'C')
) STORED;

CREATE INDEX roadmaps_search_gin ON roadmaps USING gin (search);
```

Écris la requête de recherche avec `websearch_to_tsquery('french', 'apprendre laravel')`, le classement `ts_rank` et un extrait surligné avec `ts_headline`. Ajoute un index `pg_trgm` sur le titre pour tolérer les fautes de frappe via l'opérateur de similarité `%`. Mesure les deux approches avec `EXPLAIN ANALYZE`.

## Étape 6 : optimiser (35 minutes)

Dans `05-indexes.sql`, traite les requêtes critiques. Pour chacune : `EXPLAIN ANALYZE` **avant**, création de l'index, `EXPLAIN ANALYZE` **après**, commentaire sur le gain.

- Page d'accueil : un index **partiel** `ON roadmaps (created_at DESC) WHERE is_published` doit supprimer le tri.
- Avancement d'un apprenant : vérifie les index des clés étrangères de `step_completions` et `roadmap_steps`.
- Index `GIN` sur `settings` (`jsonb`) et sur `tags` (tableau) ; prouve leur utilité avec `@>`.
- Documente **un index que tu as choisi de ne pas créer**, avec la raison, et lance la requête sur les index inutilisés dans `pg_stat_user_indexes`.

Vise un temps d'exécution inférieur à 50 ms pour l'accueil et la recherche. Utilise `CREATE INDEX CONCURRENTLY` dans les scripts destinés à la production.

## Étape 7 : fiabiliser avec des fonctions transactionnelles (30 minutes)

Dans `06-functions.sql`, écris en PL/pgSQL :

1. `complete_step(p_user bigint, p_step bigint) RETURNS numeric` : vérifie que l'utilisateur est inscrit à la roadmap de l'étape (sinon `RAISE EXCEPTION`), insère la validation avec `ON CONFLICT DO NOTHING` (idempotent) et renvoie le pourcentage d'avancement.
2. `duplicate_roadmap(p_roadmap bigint, p_user bigint) RETURNS bigint` : copie la roadmap et ses étapes, renvoie le nouvel identifiant. Toute la fonction s'exécute dans la transaction de l'appelant : si une erreur survient, tout est annulé.

Squelette de la première :

```sql
CREATE OR REPLACE FUNCTION complete_step(p_user bigint, p_step bigint)
RETURNS numeric
LANGUAGE plpgsql AS $$
DECLARE
  v_roadmap bigint;
  v_total   integer;
  v_done    integer;
BEGIN
  SELECT roadmap_id INTO v_roadmap FROM roadmap_steps WHERE id = p_step;
  IF v_roadmap IS NULL THEN
    RAISE EXCEPTION 'Étape % introuvable', p_step;
  END IF;
  -- à toi : vérifier l'inscription, insérer, calculer le pourcentage
  RETURN round(100.0 * v_done / v_total, 1);
END;
$$;
```

Teste le chemin de réussite **et** les chemins d'échec, y compris la double validation, dans des blocs `BEGIN ... ROLLBACK`. Écris aussi un test de concurrence à deux sessions utilisant `FOR UPDATE`.

## Étape 8 : sécuriser (30 minutes)

Dans `07-security.sql`, applique le moindre privilège avec trois rôles :

- `devroad_owner` : propriétaire des objets, utilisé pour les migrations ;
- `devroad_app` : `SELECT, INSERT, UPDATE, DELETE` sur les tables, `USAGE` sur les séquences, `EXECUTE` sur les fonctions, aucun DDL ;
- `devroad_readonly` : lecture seule sur une vue `v_public_users` qui masque e-mail et hash.

Active la **Row Level Security** sur `step_completions` et `enrollments` :

```sql
ALTER TABLE step_completions ENABLE ROW LEVEL SECURITY;

CREATE POLICY completions_owner ON step_completions
  USING (user_id = current_setting('app.current_user_id')::bigint)
  WITH CHECK (user_id = current_setting('app.current_user_id')::bigint);
```

Démontre dans le README, avec les commandes et les résultats, que `devroad_app` ne peut pas faire de `DROP TABLE`, que `devroad_readonly` ne peut pas écrire, et qu'avec `app.current_user_id = 2`, un apprenant ne voit pas la progression de l'utilisateur 3. Vérifie qu'aucun exemple de code ne concatène de saisie utilisateur dans une requête.

## Étape 9 : sauvegarder et restaurer (25 minutes)

Écris `backup/backup.sh` : `pg_dump -Fc`, horodatage, suppression des fichiers de plus de 14 jours, code de sortie non nul en cas d'échec, et export des rôles avec `pg_dumpall --globals-only`. Puis **teste la restauration complète** :

1. sauvegarde la base ;
2. supprime-la ;
3. recrée une base vide et restaure avec `pg_restore -j 4` ;
4. compare le nombre de lignes de chaque table et relance trois requêtes métier ;
5. mesure la durée totale.

Consigne dans `RESTORE.md` la procédure exacte, la durée mesurée et ta stratégie 3-2-1. Mentionne ce que le point-in-time recovery (WAL) apporterait en production.

## Étape 10 : documenter et livrer (15 minutes)

Rédige le `README.md` : diagramme du schéma, justification des types et contraintes, volumes de données, tableau des gains d'index (avant et après), rôles et droits, procédure pour tout rejouer depuis zéro en moins de 15 minutes avec `docker compose up`. Termine par « Limites et pistes d'évolution » : partitionnement par date, pool de connexions PgBouncer, réplique en lecture, `pgvector` pour des recommandations par similarité.

## Vérification express

:::quiz
Dans la fonction complete_step, pourquoi utiliser ON CONFLICT DO NOTHING pour l'insertion de la validation ?
- [ ] Pour ralentir volontairement les doublons
- [x] Pour rendre l'opération idempotente : valider deux fois la même étape ne produit ni erreur ni doublon
- [ ] Pour désactiver la clé primaire
- [ ] Pour contourner la Row Level Security
> La clé primaire (user_id, step_id) interdit le doublon ; ON CONFLICT DO NOTHING l'ignore proprement, ce qui évite une erreur lors d'un double clic.
:::

:::quiz
Quel est l'intérêt d'un index partiel WHERE is_published pour la page d'accueil ?
- [ ] Il remplace la clé primaire
- [x] Il n'indexe que les roadmaps publiées : plus petit, plus rapide et sans tri supplémentaire
- [ ] Il accélère les écritures sur toutes les lignes
- [ ] Il permet de supprimer ANALYZE
> L'index partiel ne contient que les lignes qui satisfont la condition, donc il est plus compact, et la requête de l'accueil, qui filtre sur le même prédicat, peut l'utiliser.
:::

## Checklist d'acceptation

**Schéma**
- Les six tables métier existent avec `timestamptz`, identifiants identité et contraintes nommées.
- L'unicité de l'e-mail est insensible à la casse.
- Chaque clé étrangère est indexée et son `ON DELETE` est justifié.
- Les contraintes `CHECK`, `UNIQUE` et la clé composée des pivots sont testées avec des insertions invalides.
- `01-schema.sql` s'exécute sans erreur sur une base vide et est rejouable.

**Données et requêtes**
- La base contient au moins 200 000 roadmaps.
- Les douze requêtes métier renvoient les résultats attendus sur le petit jeu.
- Au moins une CTE récursive, trois fonctions de fenêtre et deux requêtes `jsonb` sont utilisées.
- La recherche plein texte classe par pertinence, extrait un résumé et utilise l'index GIN.

**Performance**
- Accueil et recherche s'exécutent en moins de 50 ms.
- Chaque index est justifié par un `EXPLAIN ANALYZE` avant et après.
- Un index volontairement non créé est documenté.

**Fiabilité**
- `complete_step` est idempotente et `duplicate_roadmap` atomique.
- Les chemins d'échec sont testés avec `ROLLBACK`.

**Sécurité**
- L'application n'utilise jamais `postgres`.
- Trois rôles aux droits démontrés par des tests de refus.
- La RLS isole la progression de chaque apprenant.
- Aucun secret n'est présent dans le dépôt.

**Sauvegarde**
- `backup.sh` fonctionne et se planifie avec `cron`.
- Une restauration complète a été exécutée et vérifiée par comparaison des volumes.

**Livraison**
- Une personne extérieure reconstruit le projet avec `docker compose up` en moins de 15 minutes grâce au README.

## Pour aller plus loin

Quand tout est validé, tu peux brancher la base à une vraie application (Next.js avec Prisma ou Drizzle, Laravel, ou Supabase), ajouter du **partitionnement** de `step_completions` par mois, un journal d'audit alimenté par des déclencheurs (`TRIGGER`), des notifications temps réel avec `LISTEN` et `NOTIFY`, ou une recommandation de roadmaps par similarité avec l'extension `pgvector`. Chacune de ces pistes réutilise ce que tu as appris.

## Auto-évaluation

Prépare une présentation de dix minutes comme pour un client : le schéma et ses choix de types, une requête avancée (fenêtre ou CTE récursive), un `EXPLAIN ANALYZE` avant et après, la fonction transactionnelle, la démonstration de la RLS, et la preuve que la sauvegarde se restaure. Si tu justifies chaque choix, tu maîtrises le sujet.

## Erreurs fréquentes

- **Coder sans avoir modélisé.** Les corrections de schéma tardives coûtent cher.
- **Tester avec dix lignes.** La performance ne se révèle qu'avec du volume et des statistiques à jour.
- **Oublier `ANALYZE` après l'import.** Le planificateur prend de mauvaises décisions.
- **Créer des index sans mesure.** Tu ignores leur utilité et leur coût.
- **Compter sans `DISTINCT` après plusieurs jointures.** Les lignes se multiplient et faussent les totaux.
- **Tester la RLS avec le propriétaire de la table**, qui la contourne.
- **Utiliser `jsonb` pour tout.** Tu perds l'intégrité référentielle et la lisibilité.
- **Déclarer la sauvegarde finie sans avoir restauré.**

## Bonnes pratiques

- Avance par incréments : schéma, données, requêtes, optimisation, sécurité ; rejoue tout le dossier après chaque étape.
- Versionne les scripts dans Git avec des messages de commit explicites.
- Commente le **pourquoi** des choix techniques, pas seulement le quoi.
- Présente des mesures (millisecondes, lignes lues) plutôt que des impressions.
- Rends le projet reproductible avec Docker et des scripts idempotents.
- Prévois la reprise sur erreur et teste les échecs aussi sérieusement que les succès.

## À retenir

- Un projet de base de données se conduit dans l'ordre : modéliser, construire, alimenter, interroger, optimiser, fiabiliser, sécuriser, sauvegarder, documenter.
- Les types riches de PostgreSQL (`jsonb`, tableaux, `tsvector`, `timestamptz`) sont des outils à employer avec discernement.
- Les CTE et fonctions de fenêtre rendent lisibles des calculs analytiques complexes.
- Index partiels, d'expression et GIN se justifient par `EXPLAIN ANALYZE`.
- Rôles à moindre privilège, RLS et requêtes paramétrées forment le socle de la sécurité.
- Une sauvegarde n'existe que si la restauration a été **prouvée**.
