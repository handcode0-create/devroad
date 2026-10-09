---
title: Jointures et agrégations
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Jusqu'ici, tu as interrogé une table à la fois. Mais tout l'intérêt du modèle relationnel est de **recombiner** les tables : afficher une roadmap avec le nom de son auteur, compter les étapes de chaque roadmap, calculer la durée totale d'un parcours. Deux outils le permettent : les **jointures** (`JOIN`) et les **agrégations** (`COUNT`, `SUM`, `GROUP BY`).

À la fin du chapitre, tu seras capable de :

- relier deux ou trois tables avec `INNER JOIN` ;
- conserver les lignes sans correspondance avec `LEFT JOIN` ;
- utiliser des alias de tables pour écrire des requêtes lisibles ;
- calculer des totaux, moyennes, minimums et maximums avec les fonctions d'agrégation ;
- regrouper avec `GROUP BY` et filtrer les groupes avec `HAVING` ;
- écrire une sous-requête simple et reconnaître quand une jointure est préférable.

Prérequis : les chapitres 1 et 2, et les tables `users`, `roadmaps`, `roadmap_steps` remplies. Prévois deux heures et demie.

## Les données de départ

Rappelle-toi le contenu de nos tables (après les insertions du chapitre 2). La table `users` :

| id | name |
| --- | --- |
| 1 | Awa |
| 2 | Koffi |
| 3 | Mariam |

La table `roadmaps` (colonnes utiles) :

| id | user_id | title | level |
| --- | --- | --- | --- |
| 1 | 1 | Laravel | intermediate |
| 2 | 1 | React | beginner |
| 3 | 2 | Docker | beginner |

La table `roadmap_steps` (colonnes utiles) :

| id | roadmap_id | title | minutes | done |
| --- | --- | --- | --- | --- |
| 1 | 1 | Routes | 90 | 1 |
| 2 | 1 | Controllers | 120 | 1 |
| 3 | 1 | Eloquent | 150 | 0 |
| 4 | 2 | JSX | 60 | 1 |
| 5 | 2 | Hooks | NULL | 0 |

Remarque que Mariam n'a aucune roadmap, et que la roadmap Docker n'a aucune étape. Ces deux cas vont nous aider à comprendre les différents types de jointures.

## INNER JOIN : ne garder que les correspondances

Pour afficher chaque roadmap avec le nom de son auteur, on **joint** `roadmaps` et `users` en indiquant la condition de liaison avec `ON` : la clé étrangère égale la clé primaire.

```sql
SELECT roadmaps.title, users.name
FROM roadmaps
INNER JOIN users ON roadmaps.user_id = users.id;
```

Résultat :

| title | name |
| --- | --- |
| Laravel | Awa |
| React | Awa |
| Docker | Koffi |

Pour chaque roadmap, MySQL a trouvé l'utilisateur dont l'`id` correspond. Mariam n'apparaît pas : aucune roadmap ne pointe vers elle. **`INNER JOIN` ne garde que les lignes qui ont une correspondance des deux côtés.** Le mot `INNER` est facultatif : `JOIN` seul signifie la même chose.

Quand les noms de colonnes se répètent d'une table à l'autre (`id`, `title`), il faut les préfixer par le nom de la table pour lever l'ambiguïté. C'est vite pénible : on utilise donc des **alias de tables** :

```sql
SELECT r.title AS roadmap, u.name AS auteur
FROM roadmaps AS r
JOIN users AS u ON r.user_id = u.id
WHERE r.level = 'beginner'
ORDER BY u.name;
```

Le `WHERE`, le `ORDER BY` et le reste fonctionnent exactement comme avant : la jointure produit un résultat qu'on filtre et trie normalement.

> **Astuce** : lis une jointure à voix haute : « les roadmaps `r`, jointes aux utilisateurs `u`, là où `r.user_id` égale `u.id` ». Si tu ne sais pas la dire, la condition est probablement fausse.

## Joindre trois tables

On enchaîne simplement les `JOIN`. Voici chaque étape avec sa roadmap et l'auteur :

```sql
SELECT u.name AS auteur, r.title AS roadmap, s.title AS etape, s.minutes
FROM roadmap_steps AS s
JOIN roadmaps AS r ON s.roadmap_id = r.id
JOIN users AS u ON r.user_id = u.id
ORDER BY r.id, s.position;
```

| auteur | roadmap | etape | minutes |
| --- | --- | --- | --- |
| Awa | Laravel | Routes | 90 |
| Awa | Laravel | Controllers | 120 |
| Awa | Laravel | Eloquent | 150 |
| Awa | React | JSX | 60 |
| Awa | React | Hooks | NULL |

La roadmap Docker n'apparaît pas puisqu'elle n'a pas d'étape. Chaque `JOIN` supplémentaire réduit ou prolonge le résultat selon ses correspondances.

## LEFT JOIN : garder les lignes sans correspondance

Parfois, on veut **toutes** les lignes de la table de gauche, qu'elles aient une correspondance ou non. C'est le rôle de `LEFT JOIN`. Quels sont tous les utilisateurs, avec leurs roadmaps s'ils en ont ?

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

Mariam est présente, avec `NULL` à la place du titre. Cette particularité sert à trouver les lignes **orphelines** : en filtrant sur `NULL` côté droit, on obtient les utilisateurs sans aucune roadmap.

```sql
SELECT u.name
FROM users AS u
LEFT JOIN roadmaps AS r ON r.user_id = u.id
WHERE r.id IS NULL;
```

Résultat : Mariam. `RIGHT JOIN` existe aussi : c'est le symétrique, mais on l'utilise rarement, car il suffit d'inverser l'ordre des tables et d'écrire un `LEFT JOIN`.

| Jointure | Garde |
| --- | --- |
| `INNER JOIN` | uniquement les lignes avec correspondance des deux côtés |
| `LEFT JOIN` | toutes les lignes de gauche, `NULL` à droite s'il n'y a rien |
| `RIGHT JOIN` | toutes les lignes de droite, `NULL` à gauche s'il n'y a rien |

:::quiz
Tu veux la liste de tous les utilisateurs, même ceux qui n'ont créé aucune roadmap. Quelle jointure choisis-tu depuis users ?
- [ ] INNER JOIN roadmaps
- [x] LEFT JOIN roadmaps
- [ ] Aucune, un WHERE suffit
- [ ] CROSS JOIN roadmaps
> LEFT JOIN conserve toutes les lignes de la table de gauche (users) et met NULL quand aucune roadmap ne correspond. INNER JOIN éliminerait les utilisateurs sans roadmap.
:::

## Les fonctions d'agrégation

Une **agrégation** résume plusieurs lignes en une seule valeur. Les cinq fonctions essentielles :

| Fonction | Rôle |
| --- | --- |
| `COUNT(...)` | compte les lignes |
| `SUM(colonne)` | additionne |
| `AVG(colonne)` | calcule la moyenne |
| `MIN(colonne)` | trouve la plus petite valeur |
| `MAX(colonne)` | trouve la plus grande valeur |

```sql
SELECT
  COUNT(*) AS nb_etapes,
  SUM(minutes) AS total_minutes,
  AVG(minutes) AS moyenne,
  MIN(minutes) AS plus_courte,
  MAX(minutes) AS plus_longue
FROM roadmap_steps;
```

| nb_etapes | total_minutes | moyenne | plus_courte | plus_longue |
| --- | --- | --- | --- | --- |
| 5 | 420 | 105.0000 | 60 | 150 |

Observe un détail capital : la forme « compter toutes les lignes » de `COUNT` (avec l'étoile) compte **5 lignes**, mais `SUM` et `AVG` **ignorent les `NULL`**. La moyenne vaut 420 / 4 = 105 et non 420 / 5. De même, `COUNT(minutes)` renverrait 4, car il ne compte que les valeurs non nulles. Choisis donc la forme de `COUNT` selon ce que tu veux compter.

## GROUP BY : une ligne par groupe

Le total global est utile, mais on veut souvent un résultat **par catégorie** : par roadmap, par utilisateur, par niveau. `GROUP BY` forme des groupes de lignes ayant la même valeur, et les fonctions d'agrégation s'appliquent à chaque groupe.

Combien d'étapes et combien de minutes par roadmap ?

```sql
SELECT roadmap_id, COUNT(*) AS nb_etapes, SUM(minutes) AS total_minutes
FROM roadmap_steps
GROUP BY roadmap_id;
```

| roadmap_id | nb_etapes | total_minutes |
| --- | --- | --- |
| 1 | 3 | 360 |
| 2 | 2 | 60 |

Pour afficher le titre plutôt que l'identifiant, on joint puis on regroupe :

```sql
SELECT r.title, COUNT(s.id) AS nb_etapes, COALESCE(SUM(s.minutes), 0) AS total_minutes
FROM roadmaps AS r
LEFT JOIN roadmap_steps AS s ON s.roadmap_id = r.id
GROUP BY r.id, r.title
ORDER BY total_minutes DESC;
```

| title | nb_etapes | total_minutes |
| --- | --- | --- |
| Laravel | 3 | 360 |
| React | 2 | 60 |
| Docker | 0 | 0 |

Deux points importants. D'abord, on utilise `LEFT JOIN` pour que Docker, sans étape, apparaisse quand même avec zéro. Ensuite, on compte `s.id` et non toutes les lignes avec l'étoile : avec un `LEFT JOIN`, la ligne de Docker existe (avec `NULL` côté étapes), donc ce comptage global renverrait 1 au lieu de 0, alors que `COUNT(s.id)` ignore le `NULL`.

> **À retenir** : en MySQL 8, avec le mode `ONLY_FULL_GROUP_BY` activé par défaut, **chaque colonne du `SELECT` doit être soit dans le `GROUP BY`, soit à l'intérieur d'une fonction d'agrégation**. C'est une protection contre les résultats ambigus.

## HAVING : filtrer les groupes

`WHERE` filtre les lignes **avant** le regroupement ; il ne peut donc pas utiliser une agrégation. Pour filtrer sur le résultat d'un `COUNT` ou d'un `SUM`, on utilise `HAVING`, qui agit **après** :

```sql
SELECT roadmap_id, SUM(minutes) AS total_minutes
FROM roadmap_steps
WHERE done = TRUE
GROUP BY roadmap_id
HAVING SUM(minutes) >= 100;
```

L'ordre d'exécution logique aide à ne pas se tromper : `FROM`/`JOIN` d'abord, puis `WHERE`, `GROUP BY`, `HAVING`, `SELECT`, `ORDER BY`, et enfin `LIMIT`. Voilà pourquoi `WHERE` ne connaît pas encore les alias définis dans le `SELECT`.

:::quiz
Tu veux garder uniquement les roadmaps qui ont au moins 3 étapes. Quelle clause utilises-tu ?
- [ ] WHERE COUNT(id) >= 3
- [x] HAVING COUNT(id) >= 3
- [ ] GROUP BY COUNT(id) >= 3
- [ ] ORDER BY COUNT(id) >= 3
> Une condition sur une fonction d'agrégation se place dans HAVING, qui s'applique après le regroupement. WHERE agit avant le regroupement et ne peut pas utiliser COUNT.
:::

## Les sous-requêtes

Une **sous-requête** est un `SELECT` placé à l'intérieur d'un autre. Exemple : les étapes plus longues que la moyenne.

```sql
SELECT title, minutes
FROM roadmap_steps
WHERE minutes > (SELECT AVG(minutes) FROM roadmap_steps);
```

Avec `IN`, une sous-requête peut fournir une liste de valeurs : les utilisateurs qui ont au moins une roadmap publiée.

```sql
SELECT name
FROM users
WHERE id IN (SELECT user_id FROM roadmaps WHERE is_published = TRUE);
```

Souvent, une jointure exprime la même chose, parfois plus clairement et plus vite. Retiens la règle : **la sous-requête convient pour comparer à une valeur calculée ; la jointure convient pour afficher des colonnes de plusieurs tables.** Pour les cas plus complexes, MySQL 8 propose aussi les expressions de table commune (`WITH ... AS`), qui rendent les requêtes longues beaucoup plus lisibles.

## Atelier guidé : le tableau de bord de DevRoad

Compte une heure et demie. Chaque question correspond à un petit écran de l'application.

1. Affiche chaque roadmap avec le nom de son auteur (`INNER JOIN`).
2. Affiche tous les utilisateurs avec le nombre de roadmaps qu'ils ont créées (`LEFT JOIN` + `GROUP BY`). Mariam doit apparaître avec 0.
3. Affiche pour chaque roadmap : le titre, le nombre d'étapes, le nombre d'étapes terminées (`SUM(s.done)`) et la durée totale. Docker doit apparaître.
4. Calcule le pourcentage d'avancement d'une roadmap : étapes terminées divisées par étapes totales, multiplié par 100, arrondi avec `ROUND(..., 0)`.
5. Ne garde que les roadmaps dont la durée totale dépasse 100 minutes (`HAVING`).
6. Trouve les utilisateurs qui n'ont aucune roadmap avec un `LEFT JOIN` et `IS NULL`.
7. Trouve les étapes dont la durée dépasse la moyenne, avec une sous-requête.
8. Bonus : affiche le niveau (`level`) avec le nombre de roadmaps publiées pour chaque niveau.

Pour t'auto-évaluer : explique la différence entre `WHERE` et `HAVING`, puis dis pourquoi la moyenne de `minutes` est calculée sur 4 lignes et non sur 5.

## Erreurs fréquentes

- **Oublier la condition `ON`.** Sans elle, chaque ligne est associée à toutes les autres (produit cartésien) : le résultat explose.
- **Utiliser `INNER JOIN` quand on veut garder les lignes vides.** Des utilisateurs ou des roadmaps disparaissent du résultat.
- **Placer le filtre de la table de droite dans le `WHERE` d'un `LEFT JOIN`.** Cela annule l'effet du `LEFT JOIN` ; mets-le dans le `ON`.
- **Mettre une agrégation dans un `WHERE`.** Utilise `HAVING`.
- **Sélectionner une colonne non agrégée absente du `GROUP BY`.** MySQL 8 refuse la requête.
- **Compter toutes les lignes avec l'étoile après un `LEFT JOIN`.** Les lignes vides sont comptées pour 1 ; compte une colonne de la table jointe.
- **Oublier que `AVG` et `SUM` ignorent `NULL`.** La moyenne n'est pas celle que tu crois.

## Bonnes pratiques

- Donne des alias courts et parlants aux tables (`r`, `s`, `u`) et préfixe systématiquement les colonnes.
- Une jointure s'écrit sur sa propre ligne, avec sa condition `ON` juste à côté.
- Construis tes requêtes par étapes : d'abord la jointure seule, puis le filtre, enfin le regroupement.
- Utilise `COALESCE` pour afficher 0 plutôt que `NULL` dans les totaux.
- Pour comprendre une requête lente, préfixe-la par `EXPLAIN` (chapitre 5).

## À retenir

- `INNER JOIN` garde les correspondances ; `LEFT JOIN` garde toute la table de gauche et met `NULL` quand il n'y a rien à droite.
- La condition de jointure relie clé étrangère et clé primaire avec `ON`.
- `COUNT`, `SUM`, `AVG`, `MIN`, `MAX` résument des lignes ; ils ignorent `NULL` (sauf `COUNT` des lignes).
- `GROUP BY` crée un groupe par valeur, `HAVING` filtre les groupes, `WHERE` filtre les lignes avant.
- Une sous-requête compare à une valeur calculée ; une jointure combine des colonnes de plusieurs tables.
