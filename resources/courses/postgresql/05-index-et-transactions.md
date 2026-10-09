---
title: Index et transactions
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application qui marche avec cent lignes peut s'effondrer avec dix millions. Et deux utilisateurs qui modifient les mêmes données au même instant peuvent créer des incohérences. Ce chapitre traite des deux piliers de la fiabilité en production : la **performance** par les index et le planificateur, et l'**intégrité** par les transactions et le modèle de concurrence MVCC de PostgreSQL.

À la fin du chapitre, tu seras capable de :

- expliquer comment un index accélère les lectures et ce qu'il coûte ;
- créer des index B-tree, composés, partiels, d'expression et GIN ;
- lire un plan d'exécution avec `EXPLAIN` et `EXPLAIN ANALYZE` ;
- reconnaître et corriger les causes classiques de lenteur ;
- utiliser les transactions (`BEGIN`, `COMMIT`, `ROLLBACK`, `SAVEPOINT`) ;
- expliquer MVCC, les niveaux d'isolation et le verrouillage avec `FOR UPDATE` ;
- comprendre le rôle de `VACUUM`.

Prérequis : les chapitres 1 à 4. Prévois deux heures et demie.

## Pourquoi un index ?

Sans index, pour trouver les roadmaps d'un utilisateur, PostgreSQL lit **toute la table** : c'est un *sequential scan*. C'est comme chercher un mot dans un livre sans lexique, page après page. Un **index** est une structure annexe, triée, qui permet de retrouver directement les lignes concernées. Le type par défaut, le **B-tree**, est un arbre équilibré : trouver une ligne parmi dix millions demande trois ou quatre lectures au lieu de dix millions.

Les index ne sont pas gratuits. Chacun occupe de l'espace disque et **ralentit les écritures**, car chaque `INSERT`, `UPDATE` ou `DELETE` doit aussi le mettre à jour. La règle : indexe ce que tu cherches, joins ou tries souvent, et rien de plus.

PostgreSQL crée automatiquement un index pour chaque clé primaire et chaque contrainte `UNIQUE`. En revanche, **il ne crée pas d'index sur les colonnes de clés étrangères**. C'est à toi de le faire, car les jointures et les suppressions en cascade en dépendent.

## Créer des index

Génère d'abord un volume de données qui rende les mesures parlantes :

```sql
INSERT INTO roadmaps (user_id, title, slug, level, is_published, created_at)
SELECT 1 + (g % 3),
       'Roadmap ' || g,
       'roadmap-' || g,
       (ARRAY['beginner', 'intermediate', 'professional'])[1 + g % 3],
       g % 5 <> 0,
       now() - (g % 365) * interval '1 day'
FROM generate_series(1, 200000) AS g;

ANALYZE roadmaps;
```

`generate_series` produit une suite de nombres, pratique pour fabriquer des données de test. `ANALYZE` met à jour les statistiques que le planificateur utilise pour choisir son plan. Puis crée un index sur la clé étrangère :

```sql
CREATE INDEX roadmaps_user_id_idx ON roadmaps (user_id);
```

### Index composé

Un index sur plusieurs colonnes sert quand tu filtres sur le **préfixe le plus à gauche** : l'index `(a, b)` aide les recherches sur `a`, ou sur `a` et `b`, mais pas sur `b` seul.

```sql
CREATE INDEX roadmaps_pub_created_idx ON roadmaps (is_published, created_at DESC);
```

### Index partiel

PostgreSQL permet d'indexer **seulement une partie** des lignes. L'index est plus petit et plus rapide. Parfait pour « les roadmaps publiées » :

```sql
CREATE INDEX roadmaps_published_idx
  ON roadmaps (created_at DESC)
  WHERE is_published;
```

La requête de la page d'accueil l'utilisera, à condition qu'elle contienne le même `WHERE is_published`.

### Index d'expression

Quand tu cherches sur une valeur transformée, indexe la transformation elle-même. Recherche d'e-mail insensible à la casse :

```sql
CREATE INDEX users_email_lower_idx ON users (lower(email));
-- utilisable avec : WHERE lower(email) = 'awa@mail.ci'
```

### Autres types d'index

| Type | Usage |
| --- | --- |
| B-tree (défaut) | égalité, comparaison, tri, intervalles |
| GIN | `jsonb`, tableaux, plein texte |
| GiST | géométrie, plages, recherche par similarité |
| BRIN | très grosses tables ordonnées naturellement (journaux horodatés) |
| Hash | égalité stricte uniquement (rarement nécessaire) |

Sur une table déjà en production, crée l'index sans bloquer les écritures avec `CREATE INDEX CONCURRENTLY`. C'est plus long, mais il évite de figer l'application.

## EXPLAIN : lire le plan d'exécution

`EXPLAIN` montre le plan que PostgreSQL choisit, sans exécuter la requête. `EXPLAIN ANALYZE` l'exécute vraiment et affiche les temps mesurés.

```sql
EXPLAIN ANALYZE
SELECT id, title FROM roadmaps WHERE user_id = 2 AND is_published
ORDER BY created_at DESC LIMIT 10;
```

Sortie typique, avant l'index :

```text
Limit  (cost=7120.4..7120.5 rows=10) (actual time=58.1..58.1 rows=10 loops=1)
  ->  Sort  (cost=7120.4..7370.9 rows=53333) (actual time=58.1..58.1 rows=10)
        Sort Key: created_at DESC
        ->  Seq Scan on roadmaps  (cost=0.0..5400.0 rows=53333)
              Filter: (is_published AND (user_id = 2))
Planning Time: 0.2 ms
Execution Time: 58.4 ms
```

Et après la création d'un index adapté `(user_id, created_at DESC) WHERE is_published` :

```text
Limit  (cost=0.4..8.9 rows=10) (actual time=0.03..0.05 rows=10 loops=1)
  ->  Index Scan using roadmaps_user_pub_idx on roadmaps
Execution Time: 0.08 ms
```

Les éléments à repérer :

- **Seq Scan** : parcours complet de la table. Normal pour une petite table, suspect pour une grosse ;
- **Index Scan / Index Only Scan / Bitmap Heap Scan** : un index est utilisé ;
- **Sort** : un tri coûteux que l'index aurait pu éviter ;
- **cost** : une estimation relative ; **actual time** : le temps réel en millisecondes ;
- l'écart entre `rows` estimé et réel : s'il est énorme, relance `ANALYZE`.

> **Astuce** : PostgreSQL choisit parfois un `Seq Scan` volontairement, même avec un index, quand la requête touche une grande partie de la table. Ce n'est pas un bug : lire séquentiellement est alors moins cher.

:::quiz
Tu constates un Seq Scan sur une table de 5 millions de lignes pour une recherche sur une clé étrangère. Quelle est la première action pertinente ?
- [ ] Ajouter de la mémoire au serveur
- [x] Créer un index sur la colonne de la clé étrangère puis relire EXPLAIN ANALYZE
- [ ] Remplacer la requête par un LIKE
- [ ] Supprimer la clé étrangère
> PostgreSQL n'indexe pas automatiquement les clés étrangères. Un index sur la colonne permet de remplacer le parcours complet par une recherche ciblée, à vérifier avec EXPLAIN ANALYZE.
:::

## Pourquoi un index peut ne pas servir

Un index peut exister sans être utilisé. Les causes fréquentes :

```sql
-- 1. Fonction sur la colonne indexée
SELECT id FROM users WHERE lower(email) = 'awa@mail.ci';   -- il faut un index d'expression

-- 2. LIKE avec joker au début : un B-tree ne peut pas l'exploiter
SELECT id FROM roadmaps WHERE title LIKE '%react';          -- préfère pg_trgm + GIN

-- 3. Type incompatible entre colonne et valeur
-- 4. Table minuscule ou condition peu sélective : le Seq Scan est plus rapide
```

Toutes les statistiques viennent de `ANALYZE`, normalement lancé automatiquement par le processus *autovacuum*. Après une grosse importation, lance-le à la main.

## Les transactions

Une **transaction** regroupe plusieurs instructions en une seule opération indivisible : tout est validé ou rien ne l'est. C'est indispensable dès qu'une opération métier touche plusieurs lignes. Exemple : dupliquer une roadmap et ses étapes.

```sql
BEGIN;

INSERT INTO roadmaps (user_id, title, slug, level)
VALUES (2, 'Laravel (copie)', 'laravel-copie', 'intermediate')
RETURNING id;   -- supposons 200004

INSERT INTO roadmap_steps (roadmap_id, title, position, minutes)
SELECT 200004, title, position, minutes
FROM roadmap_steps WHERE roadmap_id = 1;

COMMIT;
```

`COMMIT` rend les changements définitifs ; `ROLLBACK` les annule. Si une erreur survient dans une transaction, PostgreSQL la marque comme échouée et refuse toute instruction suivante jusqu'au `ROLLBACK`. Pour annuler seulement une partie, utilise des **savepoints** :

```sql
BEGIN;
INSERT INTO users (name, email) VALUES ('Fatou', 'fatou@mail.ci');
SAVEPOINT avant_risque;
INSERT INTO users (name, email) VALUES ('Fatou bis', 'awa@mail.ci');  -- échoue : doublon
ROLLBACK TO avant_risque;
COMMIT;   -- Fatou est enregistrée
```

Un atout de PostgreSQL : même les changements de structure sont transactionnels. Tu peux faire un `ALTER TABLE` dans un `BEGIN` et l'annuler.

## ACID et MVCC

Les transactions respectent **ACID** : *Atomicité* (tout ou rien), *Cohérence* (les contraintes sont respectées), *Isolation* (les transactions parallèles ne se perturbent pas) et *Durabilité* (une fois validé, c'est conservé même après une panne, grâce au journal WAL).

PostgreSQL gère la concurrence avec **MVCC** (*Multi-Version Concurrency Control*). Au lieu de bloquer, il conserve **plusieurs versions** d'une ligne : chaque transaction voit un instantané cohérent. Conséquence heureuse : **les lecteurs ne bloquent jamais les écrivains, et inversement.** Seuls deux écrivains sur la même ligne s'attendent.

Les niveaux d'isolation :

| Niveau | Comportement |
| --- | --- |
| `READ COMMITTED` (défaut) | chaque instruction voit les données validées au moment où elle démarre |
| `REPEATABLE READ` | toute la transaction voit le même instantané |
| `SERIALIZABLE` | comme si les transactions s'exécutaient une par une ; peut renvoyer une erreur de sérialisation à rejouer |

Le défaut `READ COMMITTED` convient à la majorité des cas.

## Verrouiller pour éviter les conflits

Un scénario classique : deux administrateurs réservent en même temps la dernière place d'une session. Les deux lisent « 1 place », les deux la prennent. Pour l'éviter, verrouille la ligne dès la lecture :

```sql
BEGIN;
SELECT seats_left FROM sessions WHERE id = 7 FOR UPDATE;   -- l'autre transaction attend
UPDATE sessions SET seats_left = seats_left - 1 WHERE id = 7 AND seats_left > 0;
COMMIT;
```

`FOR UPDATE` bloque les autres écritures sur cette ligne jusqu'à la fin de la transaction. Deux variantes précieuses : `FOR UPDATE NOWAIT` échoue immédiatement au lieu d'attendre, et `FOR UPDATE SKIP LOCKED` ignore les lignes verrouillées, parfait pour une file de tâches traitée par plusieurs workers.

Un **deadlock** survient quand deux transactions s'attendent mutuellement. PostgreSQL le détecte, annule l'une d'elles et renvoie l'erreur `40P01`. Pour les limiter : garde des transactions courtes, accède aux lignes toujours dans le même ordre, et rejoue la transaction en cas d'erreur.

:::quiz
Dans PostgreSQL avec MVCC, qu'arrive-t-il quand une transaction lit une ligne qu'une autre transaction est en train de modifier ?
- [ ] La lecture attend la fin de la modification
- [x] La lecture n'est pas bloquée et voit la version validée de la ligne
- [ ] La lecture échoue avec une erreur
- [ ] La modification est annulée
> Grâce à MVCC, PostgreSQL conserve plusieurs versions de chaque ligne : les lecteurs ne bloquent pas les écrivains, et inversement.
:::

## VACUUM : le ménage des anciennes versions

MVCC a un coût : une ligne modifiée ou supprimée laisse une **ancienne version** (*dead tuple*) dans la table. Si on ne les nettoie pas, la table grossit inutilement et les requêtes ralentissent. `VACUUM` récupère cet espace. Le processus **autovacuum** s'en charge automatiquement dans la grande majorité des cas, et tu n'as qu'à surveiller qu'il n'est pas désactivé. Pour consulter l'état :

```sql
SELECT relname, n_live_tup, n_dead_tup, last_autovacuum
FROM pg_stat_user_tables
ORDER BY n_dead_tup DESC;
```

Une transaction qui reste ouverte très longtemps empêche le nettoyage des versions qu'elle pourrait encore voir : une raison de plus de garder les transactions courtes.

## Atelier guidé : accélérer et sécuriser DevRoad

Compte une heure et demie.

1. Génère 200 000 roadmaps avec `generate_series` et lance `ANALYZE roadmaps`.
2. Active `\timing` puis exécute avec `EXPLAIN ANALYZE` la recherche des roadmaps publiées d'un utilisateur, triées par date, avec `LIMIT 10`. Note le plan et le temps.
3. Crée l'index sur `user_id`, relance et compare.
4. Crée l'index composé ou partiel le mieux adapté (`WHERE is_published`) et observe la disparition du tri.
5. Teste `WHERE lower(email) = ...` sur `users` avec 100 000 utilisateurs générés, avant et après l'index d'expression.
6. Compare `LIKE 'Roadmap 5%'` et `LIKE '%5000'` : explique la différence de plan.
7. Écris la transaction de duplication de roadmap. Exécute-la avec `COMMIT`, puis refais-la avec `ROLLBACK` et vérifie qu'il ne reste rien.
8. Teste un `SAVEPOINT` : provoque un doublon d'e-mail au milieu d'une transaction et annule seulement cette partie.
9. Ouvre deux sessions `psql` : dans la première, lance `BEGIN; SELECT ... FOR UPDATE;` sur une ligne, dans la seconde tente un `UPDATE` sur la même ligne et observe l'attente. Termine par `COMMIT`.
10. Consulte `pg_stat_user_tables` pour voir les lignes mortes de `roadmaps`.

Pour t'auto-évaluer : explique pourquoi on n'indexe pas toutes les colonnes, ce que MVCC change pour les lectures, et à quoi sert `VACUUM`.

## Erreurs fréquentes

- **Oublier d'indexer les clés étrangères.** Jointures et suppressions en cascade deviennent lentes.
- **Indexer toutes les colonnes.** Les écritures ralentissent et le disque gonfle.
- **Appliquer une fonction sur la colonne indexée** sans index d'expression correspondant.
- **Ne pas lancer `ANALYZE` après un gros import.** Le planificateur choisit de mauvais plans.
- **Créer un index sur une grosse table de production sans `CONCURRENTLY`.** Les écritures sont bloquées.
- **Laisser une transaction ouverte longtemps.** Elle retient des verrous et bloque le nettoyage.
- **Ignorer l'erreur de deadlock ou de sérialisation.** Rejoue la transaction.

## Bonnes pratiques

- Mesure avec `EXPLAIN ANALYZE` avant et après chaque optimisation.
- Privilégie les index partiels et d'expression quand ils correspondent exactement à tes requêtes.
- Place dans un index composé les colonnes comparées par égalité avant celles du tri ou de l'intervalle.
- Regroupe toute opération métier à plusieurs écritures dans une transaction courte.
- Prévois la reprise sur erreur : deadlock, conflit de sérialisation, perte de connexion.
- Active `pg_stat_statements` en production pour repérer les requêtes les plus coûteuses.

## À retenir

- Un index B-tree évite le parcours complet mais coûte en écriture ; PostgreSQL n'indexe pas les clés étrangères tout seul.
- Index composés, partiels, d'expression et GIN couvrent la plupart des besoins.
- `EXPLAIN ANALYZE` montre le plan réel : repère `Seq Scan`, `Sort` et l'écart entre estimations et réalité.
- Une transaction garantit « tout ou rien » ; les savepoints annulent une partie seulement.
- MVCC : les lecteurs ne bloquent pas les écrivains ; `FOR UPDATE` verrouille une ligne quand il le faut.
- `VACUUM` et l'autovacuum nettoient les anciennes versions ; garde les transactions courtes.
