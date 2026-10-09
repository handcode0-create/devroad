---
title: Index et transactions
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Ton application fonctionne avec cent lignes. Avec cent mille, certaines pages deviennent lentes. Et si deux utilisateurs modifient les mêmes données au même instant, ou qu'une panne survient au milieu d'une opération, tes données peuvent devenir incohérentes. Ce chapitre traite de ces deux sujets essentiels : la **performance** avec les index, et la **fiabilité** avec les transactions.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un index et pourquoi il accélère les lectures ;
- créer des index simples, uniques et composés, et connaître leur coût ;
- lire le plan d'exécution d'une requête avec `EXPLAIN` ;
- reconnaître les requêtes qui empêchent MySQL d'utiliser un index ;
- regrouper plusieurs écritures dans une **transaction** (`START TRANSACTION`, `COMMIT`, `ROLLBACK`) ;
- expliquer les propriétés ACID et les niveaux d'isolation ;
- éviter les blocages (*deadlocks*) et utiliser `SELECT ... FOR UPDATE`.

Prérequis : les chapitres 1 à 4. Prévois deux heures et demie.

## Pourquoi un index ?

Imagine un livre de 800 pages sans index alphabétique à la fin. Pour retrouver la mention du mot « Docker », tu devrais feuilleter chaque page. C'est exactement ce que fait MySQL sans index : un **parcours complet de la table** (*full table scan*), ligne après ligne.

Un **index** est une structure annexe, triée, qui permet de retrouver rapidement les lignes correspondant à une valeur. MySQL InnoDB utilise des **arbres B+** : au lieu de lire un million de lignes, il descend dans un arbre de trois ou quatre niveaux et trouve la bonne ligne en quelques lectures. La différence se compte en millisecondes contre secondes.

Bonne nouvelle : tu as déjà des index sans le savoir. La clé primaire et toute contrainte `UNIQUE` créent automatiquement un index. Les clés étrangères aussi, dans InnoDB.

## Créer et supprimer un index

Imaginons qu'on cherche souvent les roadmaps d'un niveau donné :

```sql
SELECT id, title FROM roadmaps WHERE level = 'beginner' ORDER BY created_at DESC;
```

On crée un index sur les colonnes concernées :

```sql
CREATE INDEX idx_roadmaps_level ON roadmaps (level);
```

Pour un index unique, qui sert aussi de contrainte : `CREATE UNIQUE INDEX uq_... ON ...`. Pour voir les index d'une table et les retirer :

```sql
SHOW INDEX FROM roadmaps;
DROP INDEX idx_roadmaps_level ON roadmaps;
```

Un index n'est pas gratuit. Il occupe de l'espace disque et **ralentit les écritures** : à chaque `INSERT`, `UPDATE` ou `DELETE`, MySQL doit aussi mettre à jour tous les index concernés. On n'indexe donc pas tout : on indexe ce qu'on **cherche, joint ou trie** fréquemment.

## EXPLAIN : voir ce que fait MySQL

Comment savoir si un index est utilisé ? Préfixe ta requête par `EXPLAIN` : MySQL décrit son plan sans l'exécuter.

```sql
EXPLAIN SELECT id, title FROM roadmaps WHERE level = 'beginner';
```

Résultat simplifié, sans index :

| table | type | possible_keys | key | rows | Extra |
| --- | --- | --- | --- | --- | --- |
| roadmaps | ALL | NULL | NULL | 98000 | Using where |

Et après la création de `idx_roadmaps_level` :

| table | type | possible_keys | key | rows | Extra |
| --- | --- | --- | --- | --- | --- |
| roadmaps | ref | idx_roadmaps_level | idx_roadmaps_level | 31000 | NULL |

Les colonnes à lire en priorité :

- **type** : `ALL` signifie parcours complet (mauvais signe sur une grosse table) ; `ref`, `range`, `const` indiquent l'usage d'un index ;
- **key** : l'index réellement choisi ;
- **rows** : le nombre de lignes que MySQL estime devoir examiner ;
- **Extra** : `Using filesort` ou `Using temporary` signalent un tri ou une table temporaire coûteux.

Depuis MySQL 8.0.18, `EXPLAIN ANALYZE` exécute vraiment la requête et affiche les temps mesurés, plus fiable que l'estimation.

> **Astuce** : un index peu sélectif aide peu. Indexer `level` qui n'a que trois valeurs possibles sur 98 000 lignes gagne peu : environ un tiers de la table reste à lire. Un index sur `slug` ou `email`, aux valeurs presque toutes distinctes, est excellent.

:::quiz
EXPLAIN affiche type = ALL pour une requête sur une table de 2 millions de lignes. Que signifie-t-il ?
- [ ] La requête utilise tous les index disponibles
- [x] MySQL parcourt toute la table ligne par ligne
- [ ] La requête est parfaitement optimisée
- [ ] La requête a échoué
> Le type ALL correspond à un parcours complet de la table (full table scan), généralement à éviter sur de gros volumes.
:::

## Les index composés

Un index peut porter sur **plusieurs colonnes**. Il est alors trié par la première, puis par la deuxième à l'intérieur de chaque valeur de la première, comme un annuaire trié par nom puis par prénom.

```sql
CREATE INDEX idx_roadmaps_pub_created ON roadmaps (is_published, created_at);
```

Cet index sert efficacement la requête suivante, car il filtre sur la première colonne et trie sur la seconde :

```sql
SELECT id, title
FROM roadmaps
WHERE is_published = TRUE
ORDER BY created_at DESC
LIMIT 10;
```

La règle fondamentale est celle du **préfixe le plus à gauche** : l'index `(a, b, c)` sert les recherches sur `a`, sur `a, b`, ou sur `a, b, c`, mais **pas** sur `b` seul ni sur `c` seul. L'ordre des colonnes compte donc beaucoup : mets en premier celles que tu filtres avec `=`, puis celles du tri ou des intervalles.

## Les pièges qui désactivent un index

Un index peut exister et ne pas être utilisé. Voici les causes les plus courantes :

```sql
-- 1. Fonction appliquée sur la colonne : l'index sur created_at est ignoré
SELECT id FROM roadmaps WHERE YEAR(created_at) = 2026;

-- Réécriture qui utilise l'index
SELECT id FROM roadmaps
WHERE created_at >= '2026-01-01' AND created_at < '2027-01-01';

-- 2. LIKE avec joker au début : impossible d'utiliser l'arbre trié
SELECT id FROM roadmaps WHERE title LIKE '%react';   -- parcours complet
SELECT id FROM roadmaps WHERE title LIKE 'react%';   -- utilise l'index

-- 3. Comparaison avec un type différent (nombre contre texte)
SELECT id FROM users WHERE email = 12345;
```

Retiens ce principe : **ne transforme jamais la colonne indexée, transforme la valeur comparée.** Pour la recherche de texte libre, MySQL propose des index `FULLTEXT`, plus adaptés qu'un `LIKE` avec joker initial.

## Les transactions : tout ou rien

Passons à la fiabilité. Imagine qu'un utilisateur clique sur « Dupliquer cette roadmap » : ton code doit insérer une roadmap, puis copier ses cinq étapes. Si le serveur plante après la roadmap mais avant les étapes, tu te retrouves avec une roadmap vide : des données **incohérentes**.

Une **transaction** regroupe plusieurs instructions en une seule opération indivisible : soit tout réussit, soit rien n'est enregistré.

```sql
START TRANSACTION;

INSERT INTO roadmaps (user_id, title, slug, level)
VALUES (2, 'Laravel (copie)', 'laravel-copie', 'intermediate');

SET @new_id = LAST_INSERT_ID();

INSERT INTO roadmap_steps (roadmap_id, title, position, minutes)
SELECT @new_id, title, position, minutes
FROM roadmap_steps
WHERE roadmap_id = 1;

COMMIT;
```

`COMMIT` valide définitivement. Si quelque chose se passe mal avant, `ROLLBACK` annule tout, comme si rien n'avait eu lieu :

```sql
START TRANSACTION;
DELETE FROM roadmap_steps WHERE roadmap_id = 1;
-- oups, mauvaise roadmap
ROLLBACK;
```

> **À retenir** : par défaut, MySQL fonctionne en mode `autocommit` : chaque instruction est sa propre transaction, validée aussitôt. `START TRANSACTION` suspend ce mode jusqu'au `COMMIT` ou au `ROLLBACK`.

Les transactions exigent InnoDB. Dans une application Laravel, tu les écris avec `DB::transaction(function () { ... })` : si une exception est levée, le `ROLLBACK` est automatique.

## ACID : les quatre garanties

Les transactions d'InnoDB respectent les propriétés **ACID** :

| Lettre | Propriété | Signification |
| --- | --- | --- |
| A | Atomicité | tout ou rien |
| C | Cohérence | les contraintes sont respectées avant et après |
| I | Isolation | des transactions simultanées ne se perturbent pas |
| D | Durabilité | une fois validé, le changement survit à une panne |

## L'isolation et la concurrence

Quand plusieurs transactions s'exécutent en parallèle, l'**isolation** détermine ce qu'elles voient les unes des autres. Le niveau par défaut d'InnoDB est `REPEATABLE READ` : à l'intérieur d'une transaction, tu lis toujours le même « instantané » des données, même si d'autres valident des changements entre-temps. Les niveaux, du plus permissif au plus strict :

| Niveau | Particularité |
| --- | --- |
| `READ UNCOMMITTED` | peut lire des données non validées (à éviter) |
| `READ COMMITTED` | ne lit que du validé, mais deux lectures peuvent différer |
| `REPEATABLE READ` | lectures stables dans la transaction (défaut InnoDB) |
| `SERIALIZABLE` | exécution comme si les transactions étaient l'une après l'autre |

Plus le niveau est strict, plus les transactions s'attendent mutuellement. `REPEATABLE READ` est un bon compromis pour la plupart des applications.

### Verrouiller une ligne avec FOR UPDATE

Un scénario classique : deux administrateurs réservent en même temps la dernière place d'une session de formation. Les deux lisent « 1 place », les deux décrémentent, et la place est vendue deux fois. Pour l'éviter, verrouille la ligne dès la lecture :

```sql
START TRANSACTION;

SELECT seats_left FROM sessions WHERE id = 7 FOR UPDATE;
-- la ligne est verrouillée : l'autre transaction attend ici

UPDATE sessions SET seats_left = seats_left - 1 WHERE id = 7 AND seats_left > 0;

COMMIT;
```

`FOR UPDATE` bloque les autres transactions qui voudraient modifier ou verrouiller cette ligne jusqu'au `COMMIT`. Garde donc tes transactions **courtes**.

## Les deadlocks

Un **deadlock** survient quand deux transactions s'attendent mutuellement : A verrouille la ligne 1 et attend la ligne 2, pendant que B verrouille la ligne 2 et attend la ligne 1. InnoDB détecte la situation, annule l'une des deux transactions et renvoie l'erreur 1213. Ce n'est pas un bug : c'est un événement normal à prévoir.

Pour en réduire la fréquence : accède toujours aux tables et aux lignes **dans le même ordre**, garde les transactions courtes, et utilise des index (sans index, MySQL verrouille plus de lignes que nécessaire). Dans ton code, prévois de **rejouer** la transaction une ou deux fois si tu reçois cette erreur. Laravel le permet avec le second paramètre de `DB::transaction`.

:::quiz
Pourquoi regrouper l'insertion d'une roadmap et de ses étapes dans une transaction ?
- [ ] Pour que la requête s'exécute plus vite
- [ ] Pour éviter d'écrire les clés étrangères
- [x] Pour que tout soit enregistré ou que rien ne le soit, même en cas d'erreur ou de panne
- [ ] Pour contourner la contrainte UNIQUE
> L'atomicité d'une transaction garantit que l'ensemble des opérations réussit ou est annulé : pas de roadmap orpheline sans ses étapes.
:::

## Atelier guidé : mesurer, indexer, sécuriser

Compte une heure et demie. Il te faut une base avec des données en volume ; on les génère.

1. Dans la base `devroad`, génère 100 000 roadmaps factices avec une requête récursive :

```sql
SET SESSION cte_max_recursion_depth = 100000;
INSERT INTO roadmaps (user_id, title, slug, level, is_published)
WITH RECURSIVE seq AS (
  SELECT 1 AS n UNION ALL SELECT n + 1 FROM seq WHERE n < 100000
)
SELECT 1, CONCAT('Roadmap ', n), CONCAT('roadmap-', n),
       ELT(1 + n MOD 3, 'beginner', 'intermediate', 'professional'), n MOD 2
FROM seq;
```

2. Lance `EXPLAIN` sur une recherche par titre exact : `WHERE title = 'Roadmap 5000'`. Note `type` et `rows`.
3. Crée `idx_roadmaps_title` sur `title`, relance l'`EXPLAIN` et compare.
4. Teste `LIKE 'Roadmap 50%'` puis `LIKE '%5000'` avec `EXPLAIN` : lequel utilise l'index ?
5. Crée un index composé `(is_published, created_at)` et vérifie qu'il est choisi pour la requête des 10 dernières roadmaps publiées.
6. Mesure avec `EXPLAIN ANALYZE` la requête `WHERE YEAR(created_at) = 2026`, puis réécris-la avec un intervalle de dates.
7. Écris la transaction de duplication de roadmap, exécute-la avec `COMMIT`, puis refais-la avec un `ROLLBACK` et vérifie que rien n'a changé.
8. Ouvre deux terminaux MySQL. Dans le premier, démarre une transaction et exécute un `SELECT ... FOR UPDATE` sur une roadmap. Dans le second, tente un `UPDATE` sur la même ligne : observe l'attente, puis fais un `COMMIT` dans le premier.

Pour t'auto-évaluer : explique pourquoi on ne met pas un index sur toutes les colonnes, et ce que se passerait sans transaction dans l'exemple de duplication.

## Erreurs fréquentes

- **Indexer toutes les colonnes « au cas où ».** Les écritures ralentissent et le disque gonfle.
- **Oublier d'indexer les colonnes de jointure et de tri.** Les pages deviennent lentes dès que les tables grossissent.
- **Appliquer une fonction sur une colonne indexée dans le `WHERE`.** L'index est ignoré.
- **Mettre les colonnes d'un index composé dans le mauvais ordre.** La règle du préfixe à gauche ne s'applique plus.
- **Laisser une transaction ouverte longtemps**, par exemple pendant un appel réseau. Elle bloque tout le monde.
- **Ne pas gérer l'erreur de deadlock.** L'utilisateur voit une erreur alors qu'un simple rejeu suffisait.
- **Croire que `ROLLBACK` annule un `ALTER TABLE` ou un `DROP TABLE`.** Ces instructions DDL valident implicitement la transaction.

## Bonnes pratiques

- Mesure avant d'optimiser : `EXPLAIN` d'abord, index ensuite, `EXPLAIN` encore pour vérifier.
- Indexe en priorité les clés étrangères, les colonnes des `WHERE` fréquents et celles des `ORDER BY`.
- Place dans l'index composé les colonnes testées avec `=` avant celles utilisées pour un intervalle ou un tri.
- Garde les transactions courtes et déterministes : pas d'appel externe à l'intérieur.
- Accède aux ressources toujours dans le même ordre pour limiter les deadlocks.
- Active le journal des requêtes lentes (`slow_query_log`) pour repérer les vrais problèmes en production.

## À retenir

- Un **index** est un arbre trié qui évite de parcourir toute la table ; il accélère les lectures mais coûte à l'écriture.
- `EXPLAIN` montre si l'index est utilisé : méfie-toi de `type = ALL` et de `Using filesort` sur de gros volumes.
- Les index composés suivent la règle du préfixe le plus à gauche ; une fonction sur la colonne indexée annule l'index.
- Une **transaction** garantit « tout ou rien » avec `START TRANSACTION`, `COMMIT` et `ROLLBACK`.
- ACID : atomicité, cohérence, isolation, durabilité ; InnoDB isole par défaut en `REPEATABLE READ`.
- `FOR UPDATE` verrouille une ligne ; les deadlocks sont normaux et se gèrent en rejouant la transaction.
