---
title: Comprendre le modèle relationnel
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Presque toutes les applications web stockent leurs données dans une base de données. MySQL est l'une des plus répandues au monde : elle fait tourner des milliers de sites, et c'est la base par défaut de beaucoup d'hébergements en Afrique de l'Ouest comme ailleurs. Avant d'écrire la moindre requête, il faut comprendre **comment les données sont organisées**. C'est le rôle du modèle relationnel.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est une base de données relationnelle et pourquoi on n'utilise pas un simple tableur ;
- définir les mots **table**, **ligne**, **colonne**, **clé primaire** et **clé étrangère** ;
- choisir un type de colonne adapté (entier, texte, date, booléen) ;
- décrire les trois types de relations : un-à-un, un-à-plusieurs, plusieurs-à-plusieurs ;
- lire un schéma simple et dessiner celui de DevRoad ;
- installer MySQL 8 avec Docker et te connecter en ligne de commande.

Prérequis : aucun, sinon savoir ouvrir un terminal. Prévois deux heures. Ce chapitre est surtout conceptuel, mais l'atelier te fait manipuler un vrai serveur MySQL.

## Pourquoi pas un tableur ?

Imagine que tu suives les roadmaps de DevRoad dans un fichier Excel, une seule grande feuille :

| auteur | email_auteur | roadmap | etape | minutes |
| --- | --- | --- | --- | --- |
| Awa | awa@mail.ci | Laravel | Routes | 90 |
| Awa | awa@mail.ci | Laravel | Controllers | 120 |
| Awa | awa@mail.ci | React | JSX | 60 |
| Koffi | koffi@mail.ci | React | Composants | 100 |

Trois problèmes apparaissent vite :

1. **La répétition.** L'e-mail d'Awa est écrit trois fois. Si elle change d'adresse, il faut modifier trois lignes, et en oublier une est facile.
2. **L'incohérence.** Rien n'empêche d'écrire « awa@mail.ci » sur une ligne et « awa@mail.com » sur une autre.
3. **L'absence de règles.** Rien n'interdit de saisir « beaucoup » dans la colonne des minutes.

Une base de données relationnelle résout ces trois problèmes : on **sépare** les données en plusieurs tables, on **relie** les tables entre elles, et on **impose des règles** que le serveur fait respecter.

## Table, ligne, colonne

Une base de données relationnelle (*relational database*) range l'information dans des **tables**. Une table ressemble à un tableau, mais avec des règles strictes :

- une **colonne** (*column*) décrit un attribut et porte un **type** : `name` est du texte, `created_at` une date ;
- une **ligne** (*row*) représente un enregistrement : un utilisateur, une roadmap ;
- chaque ligne respecte la même structure, celle de la table.

Voici la table `users` de DevRoad :

| id | name | email | created_at |
| --- | --- | --- | --- |
| 1 | Awa | awa@mail.ci | 2026-01-10 |
| 2 | Koffi | koffi@mail.ci | 2026-01-12 |
| 3 | Mariam | mariam@mail.ci | 2026-02-01 |

Une base MySQL peut contenir plusieurs **bases** (on dit aussi *schémas*), et chaque base contient plusieurs tables. Pour DevRoad, tu auras une base `devroad` avec les tables `users`, `roadmaps` et `roadmap_steps`.

Le langage pour dialoguer avec la base s'appelle **SQL** (*Structured Query Language*). Tu le découvriras en détail dans le chapitre suivant. Pour l'instant, retiens qu'une instruction SQL se termine par un point-virgule et que les mots-clés s'écrivent traditionnellement en majuscules :

```sql
SELECT name, email FROM users;
```

:::quiz
Dans une table `users`, que représente une ligne ?
- [ ] Un attribut, comme l'e-mail
- [x] Un enregistrement complet, par exemple un utilisateur
- [ ] Le type d'une colonne
- [ ] Une base de données entière
> Une colonne décrit un attribut commun à toutes les lignes ; une ligne est un enregistrement, ici un utilisateur avec toutes ses informations.
:::

## Les types de données

Chaque colonne déclare ce qu'elle accepte. Choisir le bon type évite des erreurs et économise de la place. Voici les types MySQL que tu utiliseras tout le temps :

| Besoin | Type MySQL | Exemple |
| --- | --- | --- |
| Identifiant, compteur | `INT`, `BIGINT` | `42` |
| Texte court (nom, e-mail) | `VARCHAR(255)` | `'Awa'` |
| Texte long (description) | `TEXT` | un paragraphe |
| Vrai ou faux | `BOOLEAN` (alias de `TINYINT(1)`) | `1` ou `0` |
| Nombre décimal exact (prix) | `DECIMAL(10,2)` | `1500.00` |
| Date et heure | `DATETIME`, `TIMESTAMP` | `2026-01-10 14:30:00` |
| Date seule | `DATE` | `2026-01-10` |

Deux remarques importantes. D'abord, pour de l'argent, utilise toujours `DECIMAL` et jamais un nombre à virgule flottante (`FLOAT`) : ce dernier arrondit de façon approximative, ce qui est inacceptable pour des francs CFA. Ensuite, `VARCHAR(255)` signifie « jusqu'à 255 caractères » ; la valeur n'occupe que la place nécessaire.

> **Astuce** : en cas de doute sur un type, demande-toi ce que tu feras de la donnée. Si tu veux calculer avec, prends un nombre. Si tu veux la trier chronologiquement, prends une date et non un texte.

## La clé primaire : l'identité d'une ligne

Comment distinguer deux utilisateurs qui s'appellent tous les deux « Koffi » ? Avec un identifiant unique. La **clé primaire** (*primary key*) est une colonne, ou un groupe de colonnes, dont la valeur identifie **sans ambiguïté** chaque ligne.

Ses règles sont strictes : elle est **unique** et **jamais vide** (`NOT NULL`). Dans la grande majorité des tables, on utilise une colonne `id` entière qui s'incrémente toute seule (`AUTO_INCREMENT`). Tu n'as pas à choisir les valeurs : MySQL attribue 1, 2, 3...

```sql
CREATE TABLE users (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL,
  email VARCHAR(190) NOT NULL UNIQUE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

Tu remarques aussi `UNIQUE` sur l'e-mail : deux comptes ne peuvent pas partager la même adresse. C'est la base qui garantit cette règle, pas seulement ton code applicatif. Nous approfondirons les contraintes au chapitre 4.

## La clé étrangère : relier les tables

Reprenons notre tableur. Au lieu de répéter l'e-mail d'Awa à chaque ligne, on crée une table `roadmaps` qui contient seulement l'**identifiant** de son auteur :

| id | user_id | title | level |
| --- | --- | --- | --- |
| 1 | 1 | Laravel | intermediate |
| 2 | 1 | React | beginner |
| 3 | 2 | Docker | beginner |

La colonne `user_id` contient une valeur qui existe dans la colonne `id` de `users`. On l'appelle une **clé étrangère** (*foreign key*). Elle crée le lien : la roadmap 1 appartient à l'utilisateur 1, donc à Awa.

Avantages immédiats :

- l'e-mail d'Awa n'existe qu'à **un seul endroit** ; pour le changer, on modifie une seule ligne ;
- la base peut **refuser** une roadmap dont le `user_id` n'existe pas dans `users` ;
- on peut retrouver toutes les roadmaps d'un utilisateur en une requête (jointures, chapitre 3).

> **À retenir** : une clé étrangère est une colonne qui **pointe vers la clé primaire d'une autre table**. C'est le fil qui relie tes tables.

## Les trois types de relations

Les relations entre tables se classent en trois familles.

### Un-à-plusieurs (one-to-many)

C'est la plus fréquente. **Un** utilisateur possède **plusieurs** roadmaps, mais chaque roadmap n'a qu'**un** auteur. De même, une roadmap contient plusieurs étapes. La clé étrangère se place toujours du côté du « plusieurs » : `roadmaps.user_id` et `roadmap_steps.roadmap_id`.

```text
users 1 ────< roadmaps 1 ────< roadmap_steps
```

Le symbole `<` se lit « plusieurs ».

### Un-à-un (one-to-one)

Un utilisateur a **un seul** profil, et un profil appartient à **un seul** utilisateur. On place la clé étrangère dans l'une des deux tables, avec une contrainte d'unicité pour empêcher d'en avoir deux. Cette relation est assez rare : on s'en sert pour isoler des colonnes rarement lues ou sensibles.

### Plusieurs-à-plusieurs (many-to-many)

Un utilisateur peut suivre **plusieurs** roadmaps, et une roadmap est suivie par **plusieurs** utilisateurs. Impossible de le représenter avec une seule clé étrangère. On crée une **table de liaison** (ou table pivot) qui contient deux clés étrangères :

| user_id | roadmap_id | followed_at |
| --- | --- | --- |
| 1 | 3 | 2026-02-01 |
| 2 | 1 | 2026-02-03 |
| 2 | 2 | 2026-02-03 |

Chaque ligne de cette table signifie « cet utilisateur suit cette roadmap ». Ici, la clé primaire est le **couple** (`user_id`, `roadmap_id`) : un même utilisateur ne peut pas suivre deux fois la même roadmap.

:::quiz
Un auteur écrit plusieurs articles ; chaque article a un seul auteur. Où place-t-on la clé étrangère ?
- [ ] Dans la table des auteurs, colonne article_id
- [x] Dans la table des articles, colonne auteur_id
- [ ] Dans une table de liaison obligatoire
- [ ] Nulle part : MySQL devine la relation
> Dans une relation un-à-plusieurs, la clé étrangère se trouve du côté « plusieurs », ici la table des articles.
:::

## Le schéma de DevRoad

Rassemblons tout. Voici le schéma qui nous servira de fil rouge dans les sept chapitres :

```text
users
  id (PK), name, email (unique), created_at

roadmaps
  id (PK), user_id (FK -> users.id), title, slug (unique),
  level, is_published, created_at

roadmap_steps
  id (PK), roadmap_id (FK -> roadmaps.id), title,
  position, minutes, done
```

Lis-le comme une phrase : « un utilisateur crée des roadmaps ; une roadmap est composée d'étapes ordonnées par `position` ». Voici quelques lignes de `roadmap_steps` pour la roadmap 1 (Laravel) :

| id | roadmap_id | title | position | minutes | done |
| --- | --- | --- | --- | --- | --- |
| 1 | 1 | Routes | 1 | 90 | 1 |
| 2 | 1 | Controllers | 2 | 120 | 1 |
| 3 | 1 | Eloquent | 3 | 150 | 0 |

Cette organisation s'appelle la **normalisation** : chaque information vit à un seul endroit. Si le titre d'une roadmap change, une seule ligne est modifiée, et toutes les étapes restent correctement rattachées.

## Le moteur InnoDB

MySQL peut stocker les tables avec plusieurs **moteurs**. Depuis MySQL 5.7, le moteur par défaut est **InnoDB**, et c'est le seul que tu dois utiliser pour une application. Il apporte trois choses indispensables : les **clés étrangères** réellement vérifiées, les **transactions** (chapitre 5) et la récupération après un arrêt brutal du serveur. Un vieux moteur nommé MyISAM existe encore, mais il ignore les clés étrangères : évite-le.

## Atelier guidé : ton premier serveur MySQL

Compte une heure. Il te faut Docker installé. Sans Docker, tu peux utiliser MySQL fourni avec XAMPP ou Laragon : seules les premières commandes changent.

1. Lance un serveur MySQL 8 dans un conteneur :

```bash
docker run --name mysql-devroad -e MYSQL_ROOT_PASSWORD=secret -p 3306:3306 -d mysql:8
```

2. Attends une vingtaine de secondes, puis ouvre le client en ligne de commande :

```bash
docker exec -it mysql-devroad mysql -uroot -psecret
```

3. Dans le prompt `mysql>`, crée la base et sélectionne-la :

```sql
CREATE DATABASE devroad CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE devroad;
```

4. Crée la table `users` avec le code vu plus haut, puis vérifie sa structure :

```sql
DESCRIBE users;
```

5. Insère deux utilisateurs et affiche-les :

```sql
INSERT INTO users (name, email) VALUES ('Awa', 'awa@mail.ci'), ('Koffi', 'koffi@mail.ci');
SELECT * FROM users;
```

6. Essaie d'insérer une troisième personne avec l'e-mail `awa@mail.ci`. Lis le message d'erreur : c'est la contrainte `UNIQUE` qui t'a protégé.
7. Sur papier, dessine les trois tables de DevRoad avec leurs clés primaires et étrangères, puis trace les flèches.
8. Liste les bases et les tables avec `SHOW DATABASES;` puis `SHOW TABLES;`, et quitte avec `exit`.

Pour t'auto-évaluer : explique à voix haute pourquoi on sépare `users` et `roadmaps` plutôt que de tout mettre dans une seule table, et où irait la clé étrangère si une roadmap pouvait avoir plusieurs auteurs.

## Erreurs fréquentes

- **Tout mettre dans une seule grande table.** Tu retombes dans les problèmes du tableur : répétitions et incohérences.
- **Stocker une liste dans une colonne**, par exemple `1,3,7` dans un champ texte. Utilise une table de liaison.
- **Choisir `FLOAT` pour de l'argent.** Les arrondis faussent les calculs ; prends `DECIMAL`.
- **Placer la clé étrangère du mauvais côté.** Elle va toujours dans la table du côté « plusieurs ».
- **Oublier la clé primaire.** Sans identifiant unique, impossible de modifier ou supprimer proprement une ligne précise.
- **Utiliser un moteur autre qu'InnoDB.** Les clés étrangères ne seraient pas vérifiées.

## Bonnes pratiques

- Donne aux tables des noms au pluriel (`users`) et aux colonnes des noms explicites en minuscules avec des underscores (`created_at`).
- Nomme les clés étrangères selon le motif `table_au_singulier_id` : `user_id`, `roadmap_id`.
- Une table représente une seule chose, une colonne une seule information.
- Utilise `utf8mb4` comme jeu de caractères pour gérer les accents, les emojis et tous les alphabets.
- Dessine ton schéma avant d'écrire du SQL : dix minutes de réflexion économisent des heures de refactorisation.

## À retenir

- Une base relationnelle range les données dans des **tables** composées de **lignes** et de **colonnes** typées.
- La **clé primaire** identifie chaque ligne de façon unique ; la **clé étrangère** pointe vers la clé primaire d'une autre table.
- Les relations sont un-à-un, un-à-plusieurs (la plus courante) ou plusieurs-à-plusieurs via une table de liaison.
- Séparer les données évite répétitions et incohérences : c'est la normalisation.
- Le moteur **InnoDB** est celui qu'on utilise : il vérifie les clés étrangères et gère les transactions.
- Le schéma de DevRoad : `users` 1-N `roadmaps` 1-N `roadmap_steps`.
