---
title: Projet final MySQL
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru tout le cycle : modèle relationnel, SQL, jointures, contraintes, index, transactions, sécurité et sauvegardes. Ce dernier chapitre n'introduit aucune notion nouvelle. Il te demande de **tout assembler** dans un projet complet, comme tu le ferais pour un client : concevoir une base, la construire, l'alimenter, l'interroger, l'optimiser, la sécuriser et prouver qu'elle se restaure.

Le projet : **la base de données du back-end de DevRoad**, une application où des auteurs publient des roadmaps d'apprentissage composées d'étapes, et où des apprenants suivent ces roadmaps et cochent leur progression.

À la fin du projet, tu auras :

- un schéma MySQL 8 normalisé, avec toutes ses contraintes ;
- un jeu de données de test réaliste (plusieurs milliers de lignes) ;
- une dizaine de requêtes métier avec jointures et agrégations ;
- des index justifiés par des `EXPLAIN` avant et après ;
- une procédure transactionnelle fiable ;
- des comptes à droits limités et une stratégie de sauvegarde testée ;
- un dossier de livraison documenté, prêt à présenter à un client ou à mettre dans ton portfolio.

Prérequis : les chapitres 1 à 6. Durée : environ six heures, à répartir sur une ou plusieurs séances. Tu peux travailler en autonomie : chaque étape décrit ce qu'il faut obtenir, et des solutions partielles te guident aux endroits clés.

## Le cahier des charges

Le client veut une plateforme où l'on retrouve les fonctionnalités suivantes.

**Comptes.** Un utilisateur a un nom, un e-mail unique, un mot de passe haché, un rôle (`learner`, `author` ou `admin`) et une date d'inscription. Un compte peut être désactivé sans être supprimé.

**Roadmaps.** Un auteur crée des roadmaps avec un titre, une URL unique (slug), une description, un niveau (`beginner`, `intermediate`, `professional`) et un état de publication. Une roadmap est classée dans une catégorie (Back-end, Front-end, Base de données, DevOps). Elle peut porter plusieurs étiquettes (tags).

**Étapes.** Une roadmap contient des étapes ordonnées, chacune avec un titre, une durée estimée en minutes (optionnelle) et une position unique dans la roadmap.

**Suivi.** Un apprenant s'inscrit à une roadmap (une seule fois) et marque des étapes comme terminées, avec la date. On veut calculer son pourcentage d'avancement.

**Statistiques.** L'équipe veut un tableau de bord : roadmaps les plus suivies, taux de complétion par roadmap, apprenants actifs du mois, auteurs les plus productifs.

**Contraintes techniques.** MySQL 8, moteur InnoDB, jeu de caractères `utf8mb4`. La page d'accueil (10 dernières roadmaps publiées) doit répondre en moins de 50 ms avec 100 000 roadmaps. Aucune donnée personnelle ne doit fuiter en cas de compromission d'un compte applicatif.

## Livrables

Tu produis un dossier `devroad-db/` contenant :

```text
devroad-db/
├── 01-schema.sql          # création des tables et contraintes
├── 02-seed.sql            # données de démonstration
├── 03-queries.sql         # requêtes métier, commentées
├── 04-indexes.sql         # index et résultats d'EXPLAIN (en commentaires)
├── 05-procedures.sql      # transactions / procédures
├── 06-security.sql        # utilisateurs et droits
├── backup/backup.sh       # script de sauvegarde
├── backup/restore.md      # procédure de restauration testée
└── README.md              # schéma, choix, comment tout rejouer
```

## Étape 1 : modéliser (45 minutes)

Avant tout code, dessine le **diagramme entité-relation** sur papier ou avec un outil gratuit (dbdiagram.io, draw.io). Identifie les entités et les relations :

- `users` 1-N `roadmaps` (l'auteur) ;
- `categories` 1-N `roadmaps` ;
- `roadmaps` 1-N `roadmap_steps` ;
- `roadmaps` N-N `tags` via `roadmap_tag` ;
- `users` N-N `roadmaps` via `enrollments` (inscription) ;
- `users` N-N `roadmap_steps` via `step_completions` (progression).

Vérifie ton schéma contre les trois formes normales du chapitre 4. Pose-toi ces questions : une colonne contient-elle une liste ? Une donnée est-elle répétée ? Un attribut dépend-il d'une autre colonne non clé ?

> **Astuce** : écris en une phrase la règle métier derrière chaque flèche (« une roadmap appartient à exactement une catégorie »). Chaque phrase deviendra une contrainte.

## Étape 2 : créer le schéma (45 minutes)

Dans `01-schema.sql`, crée la base et les tables. Voici le début, à compléter par tes soins :

```sql
CREATE DATABASE IF NOT EXISTS devroad_pro
  CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE devroad_pro;

CREATE TABLE users (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('learner', 'author', 'admin') NOT NULL DEFAULT 'learner',
  is_active BOOLEAN NOT NULL DEFAULT TRUE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_users_email (email)
) ENGINE=InnoDB;

CREATE TABLE categories (
  id SMALLINT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(60) NOT NULL,
  PRIMARY KEY (id),
  UNIQUE KEY uq_categories_name (name)
) ENGINE=InnoDB;
```

Complète avec `roadmaps`, `roadmap_steps`, `tags`, `roadmap_tag`, `enrollments` et `step_completions`. Exigences minimales :

- toutes les clés étrangères sont nommées et ont un `ON DELETE` justifié (cascade pour les étapes et les tables pivots, restriction pour les auteurs) ;
- `roadmap_steps` a une contrainte d'unicité sur `(roadmap_id, position)` et un `CHECK` sur la durée ;
- `enrollments` a pour clé primaire `(user_id, roadmap_id)` ;
- `step_completions` a pour clé primaire `(user_id, step_id)` et une colonne `completed_at` ;
- chaque table métier possède `created_at`, et `roadmaps` possède `updated_at` automatique.

Exécute le fichier sur une base vide : il doit passer **sans erreur du premier coup**, et pouvoir être rejoué après un `DROP DATABASE`.

## Étape 3 : alimenter la base (45 minutes)

Dans `02-seed.sql`, insère un petit jeu de données à la main pour les tests fonctionnels : 4 catégories, 8 tags, 5 utilisateurs (dont 2 auteurs), 6 roadmaps, une vingtaine d'étapes, quelques inscriptions et complétions.

Pour tester les performances, génère ensuite un gros volume avec une expression de table commune récursive, comme au chapitre 5 :

```sql
SET SESSION cte_max_recursion_depth = 100000;

INSERT INTO roadmaps (user_id, category_id, title, slug, level, is_published, created_at)
WITH RECURSIVE seq AS (
  SELECT 1 AS n UNION ALL SELECT n + 1 FROM seq WHERE n < 100000
)
SELECT
  1 + (n MOD 2),
  1 + (n MOD 4),
  CONCAT('Roadmap générée ', n),
  CONCAT('roadmap-generee-', n),
  ELT(1 + (n MOD 3), 'beginner', 'intermediate', 'professional'),
  (n MOD 5) <> 0,
  NOW() - INTERVAL (n MOD 365) DAY
FROM seq;
```

Génère de la même manière quelques centaines de milliers d'étapes et de complétions. Compte tes lignes avec `COUNT` sur chaque table et note les volumes dans le README.

## Étape 4 : écrire les requêtes métier (60 minutes)

Dans `03-queries.sql`, écris et commente les requêtes suivantes. Pour chacune, note en commentaire le résultat attendu sur le petit jeu de données.

1. **Catalogue public** : titre, auteur, catégorie et nombre d'étapes des roadmaps publiées, triées par date décroissante, paginées par 10.
2. **Détail d'une roadmap** : toutes ses étapes ordonnées avec la durée cumulée (fonction de fenêtre `SUM(minutes) OVER (ORDER BY position)`, disponible en MySQL 8).
3. **Avancement d'un apprenant** : pour chaque roadmap suivie, étapes terminées, étapes totales et pourcentage arrondi.
4. **Roadmaps les plus suivies** : le top 5 avec le nombre d'inscrits.
5. **Taux de complétion** : pour chaque roadmap, la part des inscrits ayant terminé toutes les étapes.
6. **Apprenants actifs du mois** : ceux qui ont validé au moins une étape depuis le début du mois en cours.
7. **Auteurs les plus productifs** : nombre de roadmaps publiées et durée totale de leur contenu, avec `HAVING` pour exclure ceux qui n'ont rien publié.
8. **Roadmaps orphelines** : roadmaps publiées sans aucune étape (`LEFT JOIN` + `IS NULL`).
9. **Recherche** : roadmaps dont le titre ou un tag contient un mot donné.
10. **Étapes les plus longues que la moyenne de leur propre roadmap** : sous-requête corrélée ou fonction de fenêtre `AVG(...) OVER (PARTITION BY roadmap_id)`.

Voici un exemple de correction pour la requête 3, à adapter à tes noms de colonnes :

```sql
SELECT r.title,
       COUNT(DISTINCT sc.step_id) AS terminees,
       COUNT(DISTINCT s.id)       AS total,
       ROUND(100 * COUNT(DISTINCT sc.step_id) / COUNT(DISTINCT s.id)) AS pourcentage
FROM enrollments e
JOIN roadmaps r        ON r.id = e.roadmap_id
JOIN roadmap_steps s   ON s.roadmap_id = r.id
LEFT JOIN step_completions sc
       ON sc.step_id = s.id AND sc.user_id = e.user_id
WHERE e.user_id = 3
GROUP BY r.id, r.title;
```

Remarque la condition `sc.user_id = e.user_id` placée dans le `ON` et non dans le `WHERE` : c'est ce qui préserve le `LEFT JOIN` (chapitre 3).

## Étape 5 : optimiser avec les index (45 minutes)

Dans `04-indexes.sql`, traite les trois requêtes les plus critiques. Pour chacune, exécute `EXPLAIN ANALYZE`, **colle le résultat avant**, crée l'index, **colle le résultat après**, et commente le gain.

- La page d'accueil (requête 1) doit passer sous les 50 ms. Pense à un index composé `(is_published, created_at)`.
- L'avancement d'un apprenant (requête 3) : vérifie que les clés étrangères de `step_completions` et `roadmap_steps` sont indexées.
- La recherche (requête 9) : compare le `LIKE` avec joker initial et un index `FULLTEXT` interrogé avec `MATCH ... AGAINST`.

Documente aussi un index que tu as **choisi de ne pas créer**, avec la raison (faible sélectivité, table peu lue, coût en écriture). Savoir refuser un index est une marque de maturité.

## Étape 6 : fiabiliser avec les transactions (30 minutes)

Dans `05-procedures.sql`, écris une procédure stockée `complete_step(p_user_id, p_step_id)` qui, dans **une seule transaction** :

1. vérifie que l'utilisateur est inscrit à la roadmap de l'étape, sinon lève une erreur avec `SIGNAL SQLSTATE '45000'` ;
2. insère la complétion (en ignorant proprement un doublon avec `INSERT IGNORE` ou `ON DUPLICATE KEY UPDATE`) ;
3. renvoie le nouveau pourcentage d'avancement.

Gère les erreurs avec un `DECLARE EXIT HANDLER FOR SQLEXCEPTION` qui fait un `ROLLBACK` puis relance l'erreur (`RESIGNAL`). Écris aussi `duplicate_roadmap(p_roadmap_id, p_user_id)` qui copie une roadmap et ses étapes, comme au chapitre 5. Teste les chemins de réussite **et** d'échec : un rollback jamais testé n'est pas un rollback.

## Étape 7 : sécuriser (30 minutes)

Dans `06-security.sql`, crée trois comptes en appliquant le moindre privilège :

- `devroad_app` : `SELECT, INSERT, UPDATE, DELETE` sur la base, et `EXECUTE` sur les procédures, rien d'autre ;
- `devroad_migrate` : `ALL PRIVILEGES` sur la base seulement, réservé aux déploiements ;
- `devroad_stats` : `SELECT` uniquement, **et** pas d'accès à `password_hash`.

Pour ce dernier point, crée une **vue** `v_public_users` qui expose `id`, `name` et `role` sans e-mail ni hash, et donne les droits sur la vue plutôt que sur la table :

```sql
CREATE VIEW v_public_users AS
  SELECT id, name, role, created_at FROM users WHERE is_active = TRUE;

GRANT SELECT ON devroad_pro.v_public_users TO 'devroad_stats'@'%';
```

Démontre dans le README que chaque compte est bien limité : ajoute les commandes de test et leurs erreurs de refus attendues. Vérifie aussi qu'aucune requête de ton code d'exemple ne concatène de saisie utilisateur (requêtes préparées partout).

## Étape 8 : sauvegarder et restaurer (30 minutes)

Écris `backup/backup.sh` : il lance `mysqldump --single-transaction --routines --triggers`, compresse, horodate le fichier, supprime ceux de plus de 14 jours et retourne un code d'erreur non nul en cas d'échec. Puis **teste la restauration complète** :

1. sauvegarde la base ;
2. supprime-la (`DROP DATABASE`) ;
3. restaure dans une base neuve ;
4. compare le nombre de lignes de chaque table avant et après ;
5. relance une requête métier et vérifie qu'elle renvoie le même résultat.

Consigne dans `backup/restore.md` la procédure pas à pas, les commandes exactes et la durée mesurée. Précise ta stratégie 3-2-1 : où vont les copies, qui y a accès, à quelle fréquence.

## Étape 9 : documenter et livrer (30 minutes)

Rédige le `README.md` : le diagramme du schéma, une table décrivant chaque table et chaque contrainte, les volumes de données, les gains mesurés par chaque index, la liste des comptes et de leurs droits, et la marche à suivre pour tout rejouer depuis zéro avec Docker. Termine par une section « Limites et pistes d'évolution » (partitionnement, réplication, cache applicatif).

## Vérification express

:::quiz
Pourquoi placer la condition sur l'utilisateur dans le ON d'un LEFT JOIN plutôt que dans le WHERE, pour la requête d'avancement ?
- [ ] Le WHERE est interdit avec un LEFT JOIN
- [x] Dans le WHERE, elle éliminerait les lignes sans correspondance et transformerait le LEFT JOIN en INNER JOIN
- [ ] Le ON est toujours plus rapide
- [ ] Cela évite d'utiliser GROUP BY
> Un filtre sur la table de droite dans le WHERE rejette les lignes dont les colonnes sont NULL : les étapes non terminées disparaîtraient du calcul.
:::

:::quiz
Quel élément prouve qu'une sauvegarde est fiable ?
- [ ] La taille du fichier produit
- [ ] Le code de sortie de mysqldump
- [x] Une restauration complète testée, avec comparaison du nombre de lignes
- [ ] Le fait qu'elle soit planifiée avec cron
> Seule une restauration réellement exécutée et vérifiée démontre que la sauvegarde est exploitable.
:::

## Checklist d'acceptation

Ton projet est terminé quand tu peux cocher chaque ligne.

**Schéma**
- Les sept tables métier existent et sont en InnoDB `utf8mb4`.
- Aucune colonne ne contient de liste ; aucune donnée n'est dupliquée inutilement.
- Chaque clé étrangère est nommée et a un `ON DELETE` justifié.
- Les contraintes `UNIQUE` et `CHECK` du cahier des charges sont présentes et testées avec des insertions invalides.
- `01-schema.sql` s'exécute sans erreur sur une base vide.

**Données et requêtes**
- Le jeu de test contient au moins 100 000 roadmaps.
- Les dix requêtes renvoient les résultats attendus sur le petit jeu de données.
- Les requêtes utilisent des alias, des colonnes nommées et un `ORDER BY` déterministe avec `LIMIT`.

**Performance**
- La page d'accueil s'exécute en moins de 50 ms avec 100 000 roadmaps.
- Chaque index créé est justifié par un `EXPLAIN` avant et après.
- Aucune requête critique ne fait de parcours complet (`type = ALL`) sur une grande table.

**Fiabilité**
- `complete_step` et `duplicate_roadmap` s'exécutent dans une transaction avec `ROLLBACK` testé.
- Une tentative de double complétion ne produit ni erreur ni doublon.

**Sécurité**
- L'application ne se connecte jamais en `root`.
- Les trois comptes ont les droits décrits, démontrés par des tests.
- Les mots de passe sont stockés sous forme de hash.
- Aucun secret n'est présent dans le dépôt.

**Sauvegarde**
- Le script de sauvegarde fonctionne et se planifie avec `cron`.
- Une restauration complète a été exécutée et vérifiée par comparaison des volumes.

**Livraison**
- Le README permet à une autre personne de reconstruire tout le projet en moins de 15 minutes.

## Pour aller plus loin

Quand tu auras terminé, tu peux enrichir le projet : ajouter des **commentaires** avec réponses imbriquées (table auto-référencée), mettre en place un **journal d'audit** alimenté par des déclencheurs (`TRIGGER`), partitionner une grosse table par date, configurer une réplication lecture seule, ou brancher le tout à un back-end Laravel avec ses migrations et ses modèles Eloquent. Ce dernier pas est naturel : tu comprends maintenant ce que l'ORM génère sous le capot, ce qui fait de toi un meilleur développeur Laravel.

## Auto-évaluation

Pour t'auto-évaluer, présente ton projet à voix haute comme devant un client, en dix minutes : le schéma et ses choix, une requête avec son `EXPLAIN`, la transaction qui protège les données, la stratégie de sécurité et la preuve que la sauvegarde se restaure. Si tu peux justifier chaque choix, tu maîtrises le sujet.

## Erreurs fréquentes

- **Commencer par le code sans modèle.** Les corrections de schéma après coup coûtent cher.
- **Tester avec dix lignes seulement.** Les problèmes de performance n'apparaissent qu'avec du volume.
- **Créer des index sans mesurer.** Tu ne sais pas s'ils servent, ni ce qu'ils coûtent.
- **Compter avec `COUNT` sans `DISTINCT` après plusieurs jointures.** Les lignes se multiplient et les totaux sont faux.
- **Écrire une procédure transactionnelle sans tester l'échec.** Le rollback reste théorique.
- **Donner des droits trop larges « pour aller vite ».** Cette dette de sécurité est rarement remboursée.
- **Déclarer la sauvegarde finie sans restauration.** Seul un test de restauration prouve qu'elle fonctionne.

## Bonnes pratiques

- Travaille par petits incréments : schéma, puis données, puis requêtes, puis optimisation. Rejoue tout le dossier après chaque étape.
- Versionne tes fichiers SQL dans Git avec des messages de commit clairs.
- Commente le **pourquoi** des choix, pas seulement le quoi.
- Nomme systématiquement tes contraintes, index et vues avec des préfixes cohérents.
- Garde un jeu de données de test reproductible, qui permet à n'importe qui de retrouver tes résultats.
- Présente des chiffres (millisecondes, lignes examinées) plutôt que des impressions.

## À retenir

- Un projet de base de données se mène dans l'ordre : **modéliser, construire, alimenter, interroger, optimiser, fiabiliser, sécuriser, sauvegarder, documenter**.
- Le schéma et ses contraintes sont la première ligne de défense de la qualité des données.
- Les index se justifient par des mesures (`EXPLAIN ANALYZE`), avant et après.
- Les transactions protègent les opérations composées ; leurs échecs doivent être testés.
- Le moindre privilège, les requêtes préparées et les mots de passe hachés forment le socle de la sécurité.
- Une sauvegarde ne compte que si la restauration a été **prouvée**.
