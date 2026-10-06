<?php

return [
    'lessons' => [
        'Comprendre le modèle relationnel' => [
            'description' => 'Tables, lignes, colonnes, clés et relations : le vocabulaire et la logique d’une base relationnelle comme MySQL. Tu apprends à passer d’un besoin métier à un schéma cohérent.',
            'objective' => 'Être capable de modéliser un besoin simple (boutique, école) en au moins trois tables reliées par des clés primaires et étrangères, sans duplication de données.',
            'content' => <<<'MD'
## Pourquoi cette notion

Presque toutes les applications que tu vas construire stockent des données : les clients d’une boutique à Abidjan, les élèves d’une école, les courses d’un service de livraison, les paiements Mobile Money. Ces données ne vivent pas dans des fichiers Excel éparpillés. Elles vivent dans une base de données, et la plus répandue dans les projets web est la base relationnelle. MySQL est l’un des systèmes les plus utilisés, notamment avec Laravel et PHP.

Un mauvais modèle de données coûte cher. Si le nom d’un client est recopié dans cinquante commandes, une simple correction doit être faite cinquante fois, et tu finis avec des versions différentes de la même personne. Bien modéliser dès le départ évite ces incohérences et rend toutes les requêtes futures plus simples.

## Les concepts clés

### Tables, lignes et colonnes

Une table représente un type d’objet métier : « clients », « produits », « commandes ». Chaque colonne est une propriété de cet objet avec un type précis (texte, entier, date, montant). Chaque ligne est une occurrence : un client réel, une commande réelle. Le schéma est l’ensemble des tables et de leurs colonnes.

### Clé primaire et clé étrangère

La clé primaire identifie une ligne de façon unique, en général une colonne « id » numérique générée automatiquement. Elle n’est jamais vide et ne change jamais. La clé étrangère est une colonne qui contient la clé primaire d’une autre table. Par exemple, la colonne « client_id » d’une commande pointe vers le client qui a passé cette commande. C’est ce lien qui rend la base « relationnelle ».

### Les types de relations

Une relation un-à-plusieurs signifie qu’un client a plusieurs commandes, mais qu’une commande appartient à un seul client : la clé étrangère se place côté « plusieurs ». Une relation plusieurs-à-plusieurs, comme les produits et les commandes (une commande contient plusieurs produits, un produit apparaît dans plusieurs commandes), demande une table intermédiaire, ici « lignes_commande », qui porte deux clés étrangères et souvent une quantité.

### La normalisation en pratique

L’idée est simple : chaque information est stockée à un seul endroit. Le prix d’un produit est dans la table produits, pas recopié partout. Si tu te surprends à copier la même donnée dans plusieurs tables, c’est un signe qu’il manque une table et une clé étrangère.

## Exemple pas à pas

Le code d’exemple modélise une petite boutique. À l’étape 1, on crée la base et on la sélectionne. À l’étape 2, on crée la table des clients avec sa clé primaire auto-incrémentée. À l’étape 3, on crée la table des produits. À l’étape 4, on crée les commandes avec une clé étrangère vers les clients. À l’étape 5, on crée la table intermédiaire qui relie commandes et produits. Enfin, on insère quelques données et on vérifie la structure avec une requête de lecture et une commande de description de table.

## Erreurs fréquentes

- Stocker plusieurs valeurs dans une même colonne, par exemple une liste de produits séparés par des virgules : cela empêche de filtrer et de compter. Crée une table intermédiaire à la place.
- Oublier la clé primaire : sans elle, impossible d’identifier une ligne précisément. Ajoute toujours une colonne « id » en clé primaire.
- Recopier le nom du client dans la table des commandes : les données divergent avec le temps. Garde uniquement la clé étrangère « client_id ».
- Placer la clé étrangère du mauvais côté : elle va toujours côté « plusieurs ». Pose-toi la question « combien de commandes par client, combien de clients par commande ? ».
- Choisir un type texte pour un montant : tu perds les calculs fiables. Utilise un type décimal pour l’argent, par exemple en FCFA sans décimales ou avec deux décimales selon le besoin.
- Utiliser le téléphone ou l’email comme clé primaire : ces valeurs peuvent changer. Garde un identifiant technique stable et mets une contrainte d’unicité sur l’email.

## Bonnes pratiques

- Nomme les tables au pluriel et les colonnes en minuscules avec des underscores, de façon cohérente dans tout le projet.
- Dessine ton schéma sur papier avant d’écrire le SQL : entités, relations, cardinalités.
- Utilise le moteur InnoDB, qui gère les clés étrangères et les transactions.
- Ajoute des colonnes de date de création pour tracer les événements importants.
- Choisis des types adaptés à la donnée plutôt que de tout mettre en texte.

## Auto-évaluation

- Quelle est la différence entre une clé primaire et une clé étrangère ?
- Dans une relation un-à-plusieurs, de quel côté place-t-on la clé étrangère ?
- Pourquoi une relation plusieurs-à-plusieurs nécessite-t-elle une table intermédiaire ?
- Quel problème concret apparaît quand une même donnée est dupliquée dans plusieurs tables ?
- Quel type de colonne choisis-tu pour un prix en FCFA, et pourquoi ?

## À retenir

- Une base relationnelle organise les données en tables liées par des clés.
- La clé primaire identifie une ligne, la clé étrangère relie deux tables.
- Chaque information est stockée une seule fois : c’est le principe de la normalisation.
- Plusieurs-à-plusieurs égale table intermédiaire.
- Un bon schéma simplifie toutes les requêtes qui viendront ensuite.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : créer la base de la boutique et la sélectionner
CREATE DATABASE IF NOT EXISTS boutique CHARACTER SET utf8mb4;
USE boutique;

-- Étape 2 : table des clients (clé primaire auto-incrémentée)
CREATE TABLE clients (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- Étape 3 : table des produits (prix en FCFA, sans décimales)
CREATE TABLE produits (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(150) NOT NULL,
    prix_fcfa INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

-- Étape 4 : commandes, avec une clé étrangère vers clients (un client, plusieurs commandes)
CREATE TABLE commandes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    passee_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (client_id) REFERENCES clients(id)
) ENGINE=InnoDB;

-- Étape 5 : table intermédiaire plusieurs-à-plusieurs entre commandes et produits
CREATE TABLE lignes_commande (
    commande_id BIGINT UNSIGNED NOT NULL,
    produit_id BIGINT UNSIGNED NOT NULL,
    quantite INT UNSIGNED NOT NULL,
    PRIMARY KEY (commande_id, produit_id),
    FOREIGN KEY (commande_id) REFERENCES commandes(id),
    FOREIGN KEY (produit_id) REFERENCES produits(id)
) ENGINE=InnoDB;

-- Insertion de quelques données de test
INSERT INTO clients (nom, telephone) VALUES ('Awa Koné', '0102030405');
INSERT INTO produits (libelle, prix_fcfa) VALUES ('Riz 5 kg', 4500), ('Huile 1 L', 1500);
INSERT INTO commandes (client_id) VALUES (1);
INSERT INTO lignes_commande (commande_id, produit_id, quantite) VALUES (1, 1, 2), (1, 2, 1);

-- Vérification : structure d'une table et lecture des données
DESCRIBE commandes;
SELECT * FROM lignes_commande;
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Modéliser une petite école',
            'exercise_description' => <<<'TXT'
Une école veut suivre ses élèves, ses classes et les inscriptions annuelles. Un élève peut être inscrit dans une classe différente chaque année, et une classe accueille plusieurs élèves. Écris le script SQL qui crée la base « ecole » et les tables nécessaires, puis insère un jeu de données minimal.

Critères de réussite :
- Le script crée au moins trois tables : eleves, classes et inscriptions.
- Chaque table possède une clé primaire.
- La table inscriptions contient deux clés étrangères vers eleves et classes, ainsi qu’une colonne « annee ».
- Aucune information d’un élève (nom, date de naissance) n’est dupliquée dans la table inscriptions.
- Au moins deux élèves, deux classes et trois inscriptions sont insérés sans erreur.
TXT,
            'exercise_hint' => 'Une inscription relie un élève à une classe pour une année donnée : c’est une relation plusieurs-à-plusieurs, donc une table intermédiaire avec deux clés étrangères.',
            'exercise_solution' => <<<'CODE'
-- Base de l'école
CREATE DATABASE IF NOT EXISTS ecole CHARACTER SET utf8mb4;
USE ecole;

-- Table des élèves : les informations personnelles sont stockées une seule fois
CREATE TABLE eleves (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    date_naissance DATE NOT NULL
) ENGINE=InnoDB;

-- Table des classes
CREATE TABLE classes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

-- Table intermédiaire : un élève dans une classe pour une année
CREATE TABLE inscriptions (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    eleve_id BIGINT UNSIGNED NOT NULL,
    classe_id BIGINT UNSIGNED NOT NULL,
    annee SMALLINT UNSIGNED NOT NULL,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id),
    FOREIGN KEY (classe_id) REFERENCES classes(id)
) ENGINE=InnoDB;

-- Jeu de données minimal
INSERT INTO eleves (nom, date_naissance) VALUES
    ('Kouadio Yao', '2012-03-14'),
    ('Fatou Traoré', '2011-09-02');
INSERT INTO classes (libelle) VALUES ('CM1'), ('CM2');
INSERT INTO inscriptions (eleve_id, classe_id, annee) VALUES
    (1, 1, 2024),
    (2, 1, 2024),
    (1, 2, 2025);

-- Vérification
SELECT * FROM inscriptions;
CODE,
        ],

        'SQL de base' => [
            'description' => 'SELECT, INSERT, UPDATE et DELETE : les quatre opérations CRUD, avec filtres, tri et limitation des résultats. C’est le socle de toute interaction avec MySQL.',
            'objective' => 'Écrire sans aide des requêtes CRUD avec WHERE, ORDER BY et LIMIT, et modifier ou supprimer des lignes sans toucher à celles qui ne sont pas visées.',
            'content' => <<<'MD'
## Pourquoi cette notion

Quand un vendeur ajoute un produit dans une application, quand un client consulte son historique, quand un administrateur corrige un prix ou supprime un compte, c’est du SQL qui s’exécute derrière. Les opérations de création, lecture, mise à jour et suppression, qu’on appelle CRUD, représentent l’essentiel des requêtes d’une application web. Même si tu utilises plus tard un ORM comme Eloquent, comprendre le SQL qu’il génère te permet de déboguer et d’optimiser.

## Les concepts clés

### SQL est déclaratif

Tu décris le résultat voulu, pas les étapes pour l’obtenir. Tu écris « donne-moi les produits de moins de 2000 FCFA triés par prix » et le moteur décide comment le faire efficacement.

### Lire avec SELECT

La clause SELECT choisit les colonnes, FROM désigne la table, WHERE filtre les lignes. Tu peux combiner les conditions avec AND, OR et NOT, utiliser LIKE pour une recherche partielle, IN pour une liste de valeurs, BETWEEN pour un intervalle. ORDER BY trie le résultat, ASC par défaut ou DESC en ordre inverse. LIMIT restreint le nombre de lignes, ce qui est indispensable pour la pagination. Évite de sélectionner toutes les colonnes avec l’étoile dans le code d’application : liste uniquement les colonnes utiles.

### Écrire avec INSERT

INSERT INTO ajoute des lignes. Précise toujours la liste des colonnes pour que la requête reste valide si la table évolue. Tu peux insérer plusieurs lignes en une seule requête, ce qui est plus rapide que de les envoyer une par une.

### Modifier avec UPDATE et supprimer avec DELETE

UPDATE change les valeurs de colonnes existantes, DELETE retire des lignes. Les deux dépendent entièrement de la clause WHERE. Sans WHERE, ils s’appliquent à toute la table. C’est l’erreur la plus coûteuse du débutant. Prends l’habitude d’écrire d’abord un SELECT avec le même WHERE pour voir quelles lignes seront touchées, puis de transformer ce SELECT en UPDATE ou DELETE.

### La valeur NULL

NULL signifie « inconnu » ou « absent », ce n’est ni zéro ni une chaîne vide. On ne la teste pas avec le signe égal mais avec IS NULL et IS NOT NULL.

## Exemple pas à pas

Le code d’exemple travaille sur une table de produits d’une boutique. À l’étape 1, on crée la table. À l’étape 2, on insère plusieurs produits en une seule requête. À l’étape 3, on lit les produits abordables avec un filtre, un tri et une limite. À l’étape 4, on applique une hausse de prix ciblée sur une catégorie grâce à un UPDATE protégé par un WHERE. À l’étape 5, on supprime un produit précis par son identifiant. À la fin, un SELECT de contrôle confirme l’état de la table.

## Erreurs fréquentes

- Lancer un UPDATE ou un DELETE sans WHERE : toute la table est modifiée ou vidée. Teste d’abord avec un SELECT et utilise une transaction quand c’est possible.
- Comparer avec NULL en utilisant le signe égal : la condition n’est jamais vraie. Utilise IS NULL.
- Mettre des guillemets doubles autour des chaînes ou oublier les guillemets simples : la requête échoue ou lit un nom de colonne. Les valeurs texte vont entre guillemets simples.
- Utiliser LIKE avec un joker au début, comme « %riz » : c’est lent sur de grosses tables. Préfère un joker en fin de motif quand c’est possible.
- Se fier à l’ordre naturel des lignes : sans ORDER BY, l’ordre n’est pas garanti. Trie explicitement.
- Concaténer des valeurs saisies par l’utilisateur directement dans la requête : c’est la porte ouverte à l’injection SQL. Utilise toujours des requêtes préparées côté application.

## Bonnes pratiques

- Écris les mots-clés SQL en majuscules et les noms en minuscules pour la lisibilité.
- Liste explicitement les colonnes dans INSERT et SELECT.
- Vérifie chaque UPDATE ou DELETE par un SELECT préalable.
- Ajoute LIMIT quand tu explores une grosse table.
- Préfère modifier par clé primaire quand tu vises une ligne unique.

## Auto-évaluation

- Que se passe-t-il si tu exécutes un DELETE sans clause WHERE ?
- Comment tester qu’une colonne est vide au sens de NULL ?
- Quelle clause permet de trier du plus cher au moins cher ?
- Comment insérer trois lignes avec une seule requête INSERT ?
- À quoi sert LIMIT dans une page de résultats ?

## À retenir

- CRUD correspond à INSERT, SELECT, UPDATE et DELETE.
- WHERE est ta protection : sans lui, UPDATE et DELETE visent toute la table.
- NULL se teste avec IS NULL, jamais avec le signe égal.
- ORDER BY et LIMIT rendent les résultats prévisibles et paginables.
- Les valeurs venant d’un utilisateur ne se collent jamais directement dans une requête.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : table des produits d'une boutique
USE boutique;
CREATE TABLE IF NOT EXISTS catalogue (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(150) NOT NULL,
    categorie VARCHAR(50) NOT NULL,
    prix_fcfa INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0
) ENGINE=InnoDB;

-- Étape 2 : insertion de plusieurs lignes en une seule requête
INSERT INTO catalogue (libelle, categorie, prix_fcfa, stock) VALUES
    ('Riz 5 kg', 'alimentaire', 4500, 40),
    ('Huile 1 L', 'alimentaire', 1500, 120),
    ('Savon', 'hygiene', 300, 200),
    ('Cahier 200 pages', 'papeterie', 800, 75);

-- Étape 3 : lecture avec filtre, tri et limite (les 3 produits les moins chers sous 2000 FCFA)
SELECT libelle, prix_fcfa
FROM catalogue
WHERE prix_fcfa < 2000
ORDER BY prix_fcfa ASC
LIMIT 3;

-- Étape 4 : mise à jour ciblée (hausse de 10 % sur l'alimentaire uniquement)
-- On vérifie d'abord les lignes visées avec un SELECT
SELECT id, libelle FROM catalogue WHERE categorie = 'alimentaire';
UPDATE catalogue
SET prix_fcfa = ROUND(prix_fcfa * 1.10)
WHERE categorie = 'alimentaire';

-- Étape 5 : suppression d'un produit précis par sa clé primaire
DELETE FROM catalogue WHERE id = 4;

-- Contrôle final
SELECT id, libelle, categorie, prix_fcfa, stock FROM catalogue ORDER BY id;
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Gérer le stock d’un maquis',
            'exercise_description' => <<<'TXT'
Un maquis gère ses boissons dans une table « boissons » (id, nom, prix_fcfa, stock). Écris le script qui crée la table, insère des données, puis exécute les opérations de gestion demandées.

Critères de réussite :
- La table est créée avec une clé primaire auto-incrémentée et au moins cinq boissons sont insérées en une seule requête INSERT.
- Une requête SELECT affiche les boissons dont le stock est inférieur à 10, triées par stock croissant.
- Un UPDATE augmente de 100 FCFA le prix d’une seule boisson ciblée par son id.
- Un DELETE supprime uniquement les boissons dont le stock est égal à 0.
- Chaque UPDATE et DELETE contient une clause WHERE.
TXT,
            'exercise_hint' => 'Écris d’abord le SELECT avec la condition, vérifie les lignes retournées, puis réutilise exactement le même WHERE dans l’UPDATE et le DELETE.',
            'exercise_solution' => <<<'CODE'
-- Création de la table
CREATE TABLE IF NOT EXISTS boissons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    prix_fcfa INT UNSIGNED NOT NULL,
    stock INT UNSIGNED NOT NULL
) ENGINE=InnoDB;

-- Insertion de cinq boissons en une seule requête
INSERT INTO boissons (nom, prix_fcfa, stock) VALUES
    ('Bière 65 cl', 1000, 48),
    ('Jus de bissap', 500, 6),
    ('Eau minérale', 300, 0),
    ('Soda', 400, 9),
    ('Jus de gingembre', 500, 0);

-- Boissons presque épuisées, triées par stock croissant
SELECT id, nom, stock
FROM boissons
WHERE stock < 10
ORDER BY stock ASC;

-- Hausse de 100 FCFA pour une seule boisson (le soda, id 4)
UPDATE boissons
SET prix_fcfa = prix_fcfa + 100
WHERE id = 4;

-- Suppression des boissons en rupture totale
DELETE FROM boissons WHERE stock = 0;

-- Contrôle final
SELECT * FROM boissons;
CODE,
        ],

        'Jointures et agrégations' => [
            'description' => 'JOIN, LEFT JOIN, GROUP BY, HAVING et fonctions d’agrégation. Tu apprends à combiner plusieurs tables et à produire des indicateurs chiffrés.',
            'objective' => 'Écrire une requête qui relie au moins deux tables et calcule des totaux par groupe, avec un filtre sur les groupes via HAVING.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans une base bien modélisée, l’information est répartie sur plusieurs tables. Pour afficher « le nom du client et le total de sa commande », il faut relier la table des clients et celle des commandes. Pour un tableau de bord, on veut le chiffre d’affaires par mois, le nombre de commandes par ville ou les clients qui ont dépensé plus de 50 000 FCFA. Ce sont des jointures et des agrégations, et elles sont au cœur de tous les rapports et tableaux de bord que tu construiras.

## Les concepts clés

### La jointure interne

JOIN, ou INNER JOIN, relie deux tables avec une condition écrite après ON, généralement l’égalité entre une clé étrangère et une clé primaire. Seules les lignes qui ont une correspondance des deux côtés apparaissent. Un client sans commande n’est donc pas listé.

### La jointure externe

LEFT JOIN garde toutes les lignes de la table de gauche, même sans correspondance. Les colonnes de droite valent alors NULL. C’est l’outil pour répondre à « quels clients n’ont jamais commandé ? » : on fait un LEFT JOIN puis on filtre les lignes où la clé de droite est NULL.

### Alias de tables

On donne un alias court à chaque table, par exemple « c » pour clients et « o » pour commandes. Cela raccourcit la requête et lève les ambiguïtés quand deux tables ont une colonne du même nom.

### Les fonctions d’agrégation

COUNT compte les lignes, SUM additionne, AVG calcule la moyenne, MIN et MAX donnent les extrêmes. Sans GROUP BY, elles résument toute la table en une seule ligne.

### GROUP BY, WHERE et HAVING

GROUP BY regroupe les lignes qui partagent la même valeur, et les agrégations s’appliquent alors à chaque groupe. Toute colonne affichée qui n’est pas agrégée doit figurer dans le GROUP BY. WHERE filtre les lignes avant le regroupement, HAVING filtre les groupes après le calcul. Tu ne peux pas écrire une condition sur une somme dans WHERE : c’est le rôle de HAVING.

## Exemple pas à pas

Le code d’exemple utilise les tables clients et commandes d’une boutique. À l’étape 1, un JOIN simple affiche chaque commande avec le nom de son client. À l’étape 2, un LEFT JOIN retrouve les clients sans aucune commande. À l’étape 3, un GROUP BY calcule le nombre de commandes et le montant total par client. À l’étape 4, HAVING ne garde que les clients dont le total dépasse un seuil. À l’étape 5, on trie les résultats par montant décroissant pour obtenir un classement des meilleurs clients.

## Erreurs fréquentes

- Oublier la condition ON : la base produit un produit cartésien, chaque ligne de gauche est associée à toutes celles de droite, et le résultat explose. Écris toujours ON avec la clé étrangère.
- Utiliser JOIN alors qu’il faut LEFT JOIN : les lignes sans correspondance disparaissent silencieusement et les chiffres sont faux. Demande-toi si tu veux conserver les éléments sans lien.
- Mettre une condition sur un agrégat dans WHERE : MySQL renvoie une erreur. Déplace-la dans HAVING.
- Sélectionner une colonne non agrégée absente du GROUP BY : le résultat est invalide ou refusé selon le mode SQL. Ajoute la colonne au GROUP BY ou agrège-la.
- Compter toutes les lignes avec COUNT et l’étoile après un LEFT JOIN : les lignes sans correspondance comptent pour une. Utilise COUNT sur la colonne de droite pour ignorer les NULL.
- Oublier que SUM d’une colonne vide renvoie NULL : encadre avec COALESCE pour obtenir zéro.

## Bonnes pratiques

- Utilise des alias courts et cohérents pour toutes les tables d’une requête.
- Écris la jointure et sa condition ON sur des lignes distinctes pour relire facilement.
- Filtre le plus tôt possible avec WHERE pour réduire le volume avant le regroupement.
- Vérifie le résultat d’une agrégation sur un petit jeu de données dont tu connais la réponse.
- Nomme les colonnes calculées avec AS pour des résultats lisibles.

## Auto-évaluation

- Quelle est la différence entre JOIN et LEFT JOIN ?
- Comment trouver les clients qui n’ont jamais passé de commande ?
- Quelle est la différence entre WHERE et HAVING ?
- Pourquoi une colonne non agrégée doit-elle apparaître dans le GROUP BY ?
- Que se passe-t-il si on oublie la clause ON d’une jointure ?

## À retenir

- Les jointures reconstruisent l’information répartie sur plusieurs tables.
- LEFT JOIN conserve les lignes sans correspondance, JOIN les écarte.
- Les agrégations calculent un résultat par groupe défini par GROUP BY.
- WHERE filtre les lignes, HAVING filtre les groupes.
- Un résultat d’agrégation doit toujours être vérifié sur un cas simple.
MD,
            'code_example' => <<<'CODE'
-- Données supposées : tables clients(id, nom) et commandes(id, client_id, total_fcfa)
USE boutique;

-- Étape 1 : jointure interne, chaque commande avec le nom de son client
SELECT c.nom, o.id AS commande, o.total_fcfa
FROM commandes o
JOIN clients c ON c.id = o.client_id
ORDER BY o.id;

-- Étape 2 : jointure externe, les clients qui n'ont jamais commandé
SELECT c.id, c.nom
FROM clients c
LEFT JOIN commandes o ON o.client_id = c.id
WHERE o.id IS NULL;

-- Étape 3 : agrégation par client (nombre de commandes et montant total)
SELECT c.nom,
       COUNT(o.id) AS nb_commandes,
       COALESCE(SUM(o.total_fcfa), 0) AS total_depense
FROM clients c
LEFT JOIN commandes o ON o.client_id = c.id
GROUP BY c.id, c.nom;

-- Étape 4 : HAVING ne garde que les clients qui ont dépensé plus de 50 000 FCFA
SELECT c.nom, SUM(o.total_fcfa) AS total_depense
FROM clients c
JOIN commandes o ON o.client_id = c.id
GROUP BY c.id, c.nom
HAVING SUM(o.total_fcfa) > 50000;

-- Étape 5 : classement des meilleurs clients, du plus gros au plus petit
SELECT c.nom, SUM(o.total_fcfa) AS total_depense
FROM clients c
JOIN commandes o ON o.client_id = c.id
GROUP BY c.id, c.nom
ORDER BY total_depense DESC
LIMIT 5;
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Tableau de bord d’une école',
            'exercise_description' => <<<'TXT'
Une école possède les tables « classes » (id, libelle), « eleves » (id, nom, classe_id) et « notes » (id, eleve_id, matiere, valeur). Écris le script qui crée ces tables avec quelques données, puis les requêtes d’analyse demandées.

Critères de réussite :
- Une requête avec JOIN affiche le nom de chaque élève avec le libellé de sa classe.
- Une requête avec LEFT JOIN liste les élèves qui n’ont encore aucune note.
- Une requête avec GROUP BY donne la moyenne des notes par élève, arrondie à deux décimales.
- Une requête avec HAVING ne garde que les élèves dont la moyenne est supérieure ou égale à 10.
- Les résultats sont triés par moyenne décroissante.
TXT,
            'exercise_hint' => 'Pour les élèves sans note, fais partir la requête de la table eleves avec un LEFT JOIN sur notes puis filtre sur la clé de notes égale à NULL. Pour la moyenne, utilise AVG puis ROUND.',
            'exercise_solution' => <<<'CODE'
-- Création du schéma
CREATE TABLE classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(50) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE eleves (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(100) NOT NULL,
    classe_id INT UNSIGNED NOT NULL,
    FOREIGN KEY (classe_id) REFERENCES classes(id)
) ENGINE=InnoDB;

CREATE TABLE notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    eleve_id INT UNSIGNED NOT NULL,
    matiere VARCHAR(50) NOT NULL,
    valeur DECIMAL(4,2) NOT NULL,
    FOREIGN KEY (eleve_id) REFERENCES eleves(id)
) ENGINE=InnoDB;

-- Données de test
INSERT INTO classes (libelle) VALUES ('6e A'), ('5e B');
INSERT INTO eleves (nom, classe_id) VALUES ('Aya', 1), ('Moussa', 1), ('Ibrahim', 2);
INSERT INTO notes (eleve_id, matiere, valeur) VALUES
    (1, 'Maths', 14.5), (1, 'Français', 12),
    (2, 'Maths', 7), (2, 'Français', 9);

-- 1. Élèves avec le libellé de leur classe (JOIN)
SELECT e.nom, c.libelle
FROM eleves e
JOIN classes c ON c.id = e.classe_id;

-- 2. Élèves sans aucune note (LEFT JOIN + IS NULL)
SELECT e.nom
FROM eleves e
LEFT JOIN notes n ON n.eleve_id = e.id
WHERE n.id IS NULL;

-- 3 et 4. Moyenne par élève, uniquement si elle est supérieure ou égale à 10, triée par moyenne décroissante
SELECT e.nom, ROUND(AVG(n.valeur), 2) AS moyenne
FROM eleves e
JOIN notes n ON n.eleve_id = e.id
GROUP BY e.id, e.nom
HAVING AVG(n.valeur) >= 10
ORDER BY moyenne DESC;
CODE,
        ],

        'Schéma et contraintes' => [
            'description' => 'PRIMARY KEY, FOREIGN KEY, UNIQUE, NOT NULL, DEFAULT et CHECK : comment la base elle-même garantit la qualité des données.',
            'objective' => 'Définir un schéma qui rejette automatiquement les données invalides (doublons, valeurs négatives, références inexistantes) et choisir le bon comportement ON DELETE.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application n’est jamais le seul client d’une base. Il y a l’application web, l’application mobile, les scripts d’import, un collègue qui se connecte en console. Si les règles métier ne sont vérifiées que dans le code PHP, une seule faille ou un seul script oublié suffit pour insérer un solde négatif, un email en double ou une commande rattachée à un client qui n’existe pas. Les contraintes placent ces garanties dans la base, là où personne ne peut les contourner.

## Les concepts clés

### NOT NULL et DEFAULT

NOT NULL interdit l’absence de valeur. DEFAULT fournit une valeur automatique quand aucune n’est donnée, par exemple un statut « en_attente » ou une date de création. Un champ réellement obligatoire doit toujours être NOT NULL.

### PRIMARY KEY et UNIQUE

La clé primaire est unique et non nulle. UNIQUE ajoute la même garantie d’unicité à d’autres colonnes : l’email d’un utilisateur, le numéro de téléphone, la référence d’un produit. On peut aussi définir une unicité sur une combinaison de colonnes, par exemple un élève ne peut être inscrit qu’une fois à la même classe pour la même année.

### FOREIGN KEY et comportements de suppression

La clé étrangère impose que la valeur référencée existe dans la table parente. Il faut aussi décider ce qui arrive quand la ligne parente est supprimée. ON DELETE RESTRICT, le comportement par défaut, refuse la suppression tant que des lignes enfants existent. ON DELETE CASCADE supprime aussi les enfants : pratique pour des lignes de détail, dangereux pour des données comptables. ON DELETE SET NULL met la clé enfant à NULL, à condition que la colonne l’accepte. Les clés étrangères exigent le moteur InnoDB.

### CHECK

CHECK vérifie une condition sur les valeurs d’une ligne, par exemple un prix supérieur ou égal à zéro ou une quantité strictement positive. Les versions récentes de MySQL appliquent réellement ces contraintes, alors que d’anciennes versions les acceptaient sans les vérifier : teste toujours qu’un INSERT invalide est bien rejeté sur ton serveur.

### Modifier un schéma existant

ALTER TABLE permet d’ajouter une colonne ou une contrainte après coup. Sur une table déjà remplie, l’ajout échoue si les données existantes violent la nouvelle règle : il faut d’abord les corriger.

## Exemple pas à pas

Le code d’exemple définit une table de comptes et une table de transferts. À l’étape 1, la table des comptes combine NOT NULL, UNIQUE sur le numéro de téléphone, DEFAULT pour le solde et CHECK pour interdire un solde négatif. À l’étape 2, la table des transferts ajoute une clé étrangère avec ON DELETE RESTRICT, car on ne veut pas effacer l’historique financier. À l’étape 3, on insère des données valides. À l’étape 4, on tente volontairement des insertions invalides en commentaire : chacune doit produire une erreur, et c’est précisément le but.

## Erreurs fréquentes

- Faire confiance uniquement à la validation du code applicatif : un script externe peut écrire des données invalides. Double la validation avec des contraintes.
- Utiliser ON DELETE CASCADE partout : supprimer un client efface tout son historique de paiements. Réserve CASCADE aux données purement dépendantes.
- Oublier UNIQUE sur l’email : deux comptes identiques apparaissent. Ajoute la contrainte et gère l’erreur de doublon côté application.
- Ajouter une contrainte sur une table qui contient déjà des données invalides : ALTER échoue. Nettoie les lignes fautives d’abord, avec un SELECT pour les repérer.
- Utiliser un type incompatible entre clé étrangère et clé primaire, par exemple signé contre non signé : la création de la contrainte échoue. Garde exactement le même type des deux côtés.
- Supposer que CHECK fonctionne sans l’avoir testé : insère une valeur interdite et confirme l’erreur.

## Bonnes pratiques

- Donne un nom explicite aux contraintes importantes pour comprendre les messages d’erreur.
- Marque NOT NULL toute colonne obligatoire dès la création.
- Choisis RESTRICT par défaut pour les données sensibles et CASCADE seulement pour les détails sans valeur propre.
- Teste chaque contrainte avec une insertion volontairement invalide.
- Ajoute les contraintes dans les migrations versionnées, pas à la main en production.

## Auto-évaluation

- Quelle différence y a-t-il entre PRIMARY KEY et UNIQUE ?
- Que fait ON DELETE RESTRICT quand on supprime un parent qui a des enfants ?
- Pourquoi ne pas se contenter de la validation côté application ?
- Comment imposer qu’une quantité soit strictement positive au niveau de la base ?
- Que faut-il faire avant d’ajouter une contrainte UNIQUE sur une table déjà remplie ?

## À retenir

- Les contraintes protègent les données même quand plusieurs clients écrivent.
- NOT NULL, UNIQUE, CHECK et FOREIGN KEY couvrent la plupart des règles métier simples.
- ON DELETE doit être choisi selon la valeur des données enfants.
- Une contrainte non testée est une contrainte dont on ne peut pas être sûr.
- Les types des colonnes liées par une clé étrangère doivent être identiques.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : table des comptes Mobile Money avec contraintes
CREATE TABLE comptes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    titulaire VARCHAR(120) NOT NULL,
    telephone VARCHAR(20) NOT NULL,
    solde_fcfa BIGINT NOT NULL DEFAULT 0,
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- un numéro de téléphone ne peut appartenir qu'à un seul compte
    CONSTRAINT uq_comptes_telephone UNIQUE (telephone),
    -- le solde ne peut jamais être négatif
    CONSTRAINT ck_comptes_solde CHECK (solde_fcfa >= 0)
) ENGINE=InnoDB;

-- Étape 2 : table des transferts liée aux comptes
CREATE TABLE transferts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    compte_source_id BIGINT UNSIGNED NOT NULL,
    compte_dest_id BIGINT UNSIGNED NOT NULL,
    montant_fcfa BIGINT NOT NULL,
    effectue_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    -- le montant doit être strictement positif
    CONSTRAINT ck_transferts_montant CHECK (montant_fcfa > 0),
    -- RESTRICT : on ne supprime pas un compte qui a un historique
    CONSTRAINT fk_transferts_source FOREIGN KEY (compte_source_id)
        REFERENCES comptes(id) ON DELETE RESTRICT,
    CONSTRAINT fk_transferts_dest FOREIGN KEY (compte_dest_id)
        REFERENCES comptes(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Étape 3 : insertions valides
INSERT INTO comptes (titulaire, telephone, solde_fcfa) VALUES
    ('Awa Koné', '0102030405', 50000),
    ('Yao Kouassi', '0708091011', 20000);
INSERT INTO transferts (compte_source_id, compte_dest_id, montant_fcfa) VALUES (1, 2, 5000);

-- Étape 4 : tests d'insertions invalides (chacune doit provoquer une erreur)
-- INSERT INTO comptes (titulaire, telephone) VALUES ('Doublon', '0102030405');   -- violation UNIQUE
-- INSERT INTO comptes (titulaire, telephone, solde_fcfa) VALUES ('Négatif', '0000000000', -10);   -- violation CHECK
-- INSERT INTO transferts (compte_source_id, compte_dest_id, montant_fcfa) VALUES (1, 99, 100);   -- clé étrangère inexistante
-- DELETE FROM comptes WHERE id = 1;   -- refusé par ON DELETE RESTRICT
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Sécuriser le schéma d’une boutique',
            'exercise_description' => <<<'TXT'
Crée un schéma pour une boutique avec les tables « categories », « produits » et « avis », en utilisant les contraintes pour verrouiller les règles métier.

Critères de réussite :
- La table categories a un libellé NOT NULL et UNIQUE.
- La table produits référence categories par clé étrangère avec ON DELETE RESTRICT, et possède un CHECK qui impose un prix supérieur ou égal à zéro.
- La table avis référence produits par clé étrangère avec ON DELETE CASCADE et un CHECK qui limite la note entre 1 et 5.
- La colonne stock de produits a une valeur par défaut de 0.
- Des insertions valides réussissent, et au moins trois insertions invalides sont écrites en commentaire avec l’erreur attendue.
TXT,
            'exercise_hint' => 'Combine CONSTRAINT nom CHECK (condition) pour le prix et la note, et pense à utiliser BETWEEN 1 AND 5 pour la note. N’oublie pas ENGINE=InnoDB pour les clés étrangères.',
            'exercise_solution' => <<<'CODE'
-- Catégories : libellé obligatoire et unique
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    libelle VARCHAR(80) NOT NULL,
    CONSTRAINT uq_categories_libelle UNIQUE (libelle)
) ENGINE=InnoDB;

-- Produits : RESTRICT pour protéger les catégories utilisées, CHECK sur le prix, stock par défaut à 0
CREATE TABLE produits (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    categorie_id INT UNSIGNED NOT NULL,
    nom VARCHAR(150) NOT NULL,
    prix_fcfa INT NOT NULL,
    stock INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT ck_produits_prix CHECK (prix_fcfa >= 0),
    CONSTRAINT fk_produits_categorie FOREIGN KEY (categorie_id)
        REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- Avis : CASCADE car un avis n'a pas de sens sans son produit, note entre 1 et 5
CREATE TABLE avis (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    produit_id INT UNSIGNED NOT NULL,
    note TINYINT NOT NULL,
    commentaire VARCHAR(255),
    CONSTRAINT ck_avis_note CHECK (note BETWEEN 1 AND 5),
    CONSTRAINT fk_avis_produit FOREIGN KEY (produit_id)
        REFERENCES produits(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Insertions valides
INSERT INTO categories (libelle) VALUES ('Alimentaire'), ('Hygiène');
INSERT INTO produits (categorie_id, nom, prix_fcfa) VALUES (1, 'Riz 5 kg', 4500);
INSERT INTO avis (produit_id, note, commentaire) VALUES (1, 5, 'Très bon riz');

-- Insertions invalides à tester (chacune doit échouer)
-- INSERT INTO categories (libelle) VALUES ('Alimentaire');            -- erreur : doublon UNIQUE
-- INSERT INTO produits (categorie_id, nom, prix_fcfa) VALUES (1, 'Test', -100);   -- erreur : CHECK prix
-- INSERT INTO avis (produit_id, note) VALUES (1, 9);                  -- erreur : CHECK note hors 1 à 5
-- DELETE FROM categories WHERE id = 1;                                -- erreur : RESTRICT, des produits existent
CODE,
        ],

        'Index et transactions' => [
            'description' => 'Index pour accélérer les recherches, EXPLAIN pour vérifier leur utilisation, et transactions pour regrouper plusieurs écritures de façon atomique.',
            'objective' => 'Créer un index adapté à une requête lente, vérifier son utilisation avec EXPLAIN, et écrire une transaction qui valide ou annule un transfert d’argent de manière cohérente.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application qui répond en une demi-seconde avec cent clients peut mettre dix secondes avec cent mille. La plupart du temps, la cause est une requête qui lit toute la table pour retrouver quelques lignes. Un index règle ce problème. Par ailleurs, dès qu’une opération métier touche plusieurs lignes, comme débiter un compte et en créditer un autre lors d’un transfert Mobile Money, une panne au milieu laisserait la base dans un état faux. Les transactions garantissent que tout réussit ou que rien n’est appliqué.

## Les concepts clés

### Ce qu’est un index

Un index est une structure triée, en pratique un arbre, qui permet de retrouver des lignes sans parcourir toute la table. Comme l’index à la fin d’un livre, il évite de lire chaque page. MySQL crée déjà un index sur la clé primaire et sur les contraintes UNIQUE. Tu ajoutes les autres avec CREATE INDEX.

### Quand créer un index

Indexe les colonnes utilisées souvent dans WHERE, dans les conditions de jointure et dans ORDER BY, comme les clés étrangères. Un index sur plusieurs colonnes est utile quand les requêtes filtrent sur la combinaison, et l’ordre des colonnes compte : la première doit être celle sur laquelle on filtre le plus souvent. Les index ont un coût : ils occupent de l’espace et ralentissent légèrement les écritures. N’en crée donc pas sur toutes les colonnes.

### Vérifier avec EXPLAIN

EXPLAIN placé avant un SELECT décrit le plan d’exécution. Si la colonne type indique ALL, la table est parcourue entièrement. Si elle indique ref ou range avec un nom d’index dans la colonne key, l’index est utilisé. C’est ton outil de diagnostic : mesure avant et après.

### Les transactions et les propriétés ACID

Une transaction commence avec START TRANSACTION et se termine par COMMIT pour valider ou ROLLBACK pour annuler. Elle est atomique, cohérente, isolée et durable. Avec InnoDB, les autres connexions ne voient pas tes modifications avant le COMMIT. L’instruction SELECT ... FOR UPDATE verrouille les lignes lues pour empêcher une modification concurrente pendant que tu décides quoi écrire.

## Exemple pas à pas

Le code d’exemple traite d’abord les index. À l’étape 1, on observe avec EXPLAIN une recherche par téléphone sans index approprié. À l’étape 2, on crée l’index et on relance EXPLAIN pour constater la différence. À l’étape 3, on démarre une transaction de transfert : on verrouille la ligne du compte débiteur, on débite, on crédite, on enregistre le transfert, puis on valide avec COMMIT. À l’étape 4, un second bloc en commentaire montre l’annulation avec ROLLBACK quand une vérification échoue.

## Erreurs fréquentes

- Indexer toutes les colonnes par précaution : les écritures ralentissent et l’espace explose. Indexe seulement ce que tes requêtes utilisent réellement.
- Appliquer une fonction sur la colonne indexée dans WHERE, comme LOWER sur le nom : l’index n’est plus utilisé. Compare la colonne brute ou réécris la condition.
- Ne pas vérifier avec EXPLAIN : tu crois avoir accéléré la requête alors que l’index n’est pas choisi. Mesure toujours.
- Oublier COMMIT : la transaction reste ouverte, les verrous bloquent d’autres utilisateurs et les modifications sont perdues à la déconnexion. Termine toujours par COMMIT ou ROLLBACK.
- Faire des transactions longues avec des attentes réseau à l’intérieur : les verrous durent trop longtemps. Garde-les courtes et ciblées.
- Lire un solde, calculer dans l’application puis écrire sans verrou : deux opérations simultanées s’écrasent. Utilise FOR UPDATE ou une mise à jour relative, comme solde = solde - montant.

## Bonnes pratiques

- Indexe les clés étrangères et les colonnes très filtrées, puis vérifie avec EXPLAIN.
- Regroupe toutes les écritures liées à une même opération métier dans une seule transaction.
- Garde les transactions les plus courtes possible.
- Utilise des mises à jour relatives plutôt que lire puis réécrire une valeur.
- Prévois la gestion d’erreur côté application avec ROLLBACK en cas d’exception.

## Auto-évaluation

- Pourquoi un index accélère-t-il une recherche, et quel est son coût ?
- Comment lire dans EXPLAIN que ta requête utilise bien un index ?
- Que signifie qu’une transaction est atomique ?
- À quoi sert SELECT ... FOR UPDATE ?
- Que se passe-t-il si la connexion coupe avant le COMMIT ?

## À retenir

- Un index accélère les lectures mais ralentit un peu les écritures.
- EXPLAIN permet de vérifier le plan d’exécution et d’éviter les suppositions.
- Une transaction regroupe plusieurs écritures : tout est appliqué ou rien.
- COMMIT valide, ROLLBACK annule, et une transaction ne doit jamais rester ouverte.
- Les verrous protègent des écritures concurrentes mais doivent rester courts.
MD,
            'code_example' => <<<'CODE'
-- Table de comptes supposée existante : comptes(id, titulaire, telephone, solde_fcfa)
-- et transferts(id, compte_source_id, compte_dest_id, montant_fcfa)

-- Étape 1 : sans index sur titulaire, EXPLAIN montre un parcours complet (type = ALL)
EXPLAIN SELECT id, solde_fcfa FROM comptes WHERE titulaire = 'Awa Koné';

-- Étape 2 : création de l'index, puis nouvelle vérification (la colonne key doit citer l'index)
CREATE INDEX idx_comptes_titulaire ON comptes (titulaire);
EXPLAIN SELECT id, solde_fcfa FROM comptes WHERE titulaire = 'Awa Koné';

-- Étape 3 : transfert de 5000 FCFA du compte 1 vers le compte 2, de façon atomique
START TRANSACTION;

-- On verrouille le compte débiteur pour empêcher une modification concurrente
SELECT solde_fcfa FROM comptes WHERE id = 1 FOR UPDATE;

-- Mises à jour relatives : le calcul reste dans la base
UPDATE comptes SET solde_fcfa = solde_fcfa - 5000 WHERE id = 1;
UPDATE comptes SET solde_fcfa = solde_fcfa + 5000 WHERE id = 2;

-- Trace du transfert
INSERT INTO transferts (compte_source_id, compte_dest_id, montant_fcfa) VALUES (1, 2, 5000);

-- Tout s'est bien passé : on valide
COMMIT;

-- Étape 4 : cas d'échec (par exemple solde insuffisant constaté par l'application)
-- START TRANSACTION;
-- UPDATE comptes SET solde_fcfa = solde_fcfa - 999999 WHERE id = 1;
-- ROLLBACK;   -- annule tout : le solde du compte 1 reste inchangé
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Accélérer une requête et sécuriser un achat',
            'exercise_description' => <<<'TXT'
Tu disposes des tables « produits » (id, nom, categorie, stock, prix_fcfa) et « ventes » (id, produit_id, quantite, vendu_le). Écris un script qui accélère les recherches par catégorie puis enregistre une vente de façon atomique.

Critères de réussite :
- Un EXPLAIN est exécuté avant et après la création d’un index sur la colonne categorie.
- Un index est créé sur ventes(produit_id), colonne de jointure fréquente.
- La vente est faite dans une transaction : START TRANSACTION, verrouillage du produit avec FOR UPDATE, baisse du stock avec une mise à jour relative, insertion de la vente, puis COMMIT.
- Un second bloc montre une vente annulée avec ROLLBACK, et un commentaire explique pourquoi le stock reste inchangé.
- Aucune mise à jour du stock n’est faite en lisant la valeur puis en la réécrivant depuis l’application.
TXT,
            'exercise_hint' => 'La mise à jour relative s’écrit stock = stock - quantité. Le SELECT ... FOR UPDATE se place juste après START TRANSACTION et avant les UPDATE.',
            'exercise_solution' => <<<'CODE'
-- Plan d'exécution avant l'index
EXPLAIN SELECT id, nom FROM produits WHERE categorie = 'alimentaire';

-- Index sur la colonne filtrée, puis nouvelle vérification
CREATE INDEX idx_produits_categorie ON produits (categorie);
EXPLAIN SELECT id, nom FROM produits WHERE categorie = 'alimentaire';

-- Index sur la clé étrangère utilisée dans les jointures
CREATE INDEX idx_ventes_produit_id ON ventes (produit_id);

-- Vente atomique de 3 unités du produit 1
START TRANSACTION;

-- Verrouillage de la ligne produit pendant l'opération
SELECT stock FROM produits WHERE id = 1 FOR UPDATE;

-- Mise à jour relative du stock (aucun calcul côté application)
UPDATE produits SET stock = stock - 3 WHERE id = 1;

-- Enregistrement de la vente
INSERT INTO ventes (produit_id, quantite, vendu_le) VALUES (1, 3, NOW());

COMMIT;

-- Vente annulée : le ROLLBACK défait la baisse de stock et l'insertion,
-- donc le stock du produit 2 reste exactement comme avant la transaction.
START TRANSACTION;
UPDATE produits SET stock = stock - 5 WHERE id = 2;
INSERT INTO ventes (produit_id, quantite, vendu_le) VALUES (2, 5, NOW());
ROLLBACK;
CODE,
        ],

        'Sécurité et sauvegardes' => [
            'description' => 'Comptes utilisateurs MySQL, privilèges minimaux, bonnes pratiques de mots de passe et stratégie de sauvegarde vérifiée.',
            'objective' => 'Créer un compte applicatif limité à une base, lui accorder uniquement les privilèges nécessaires, et décrire une procédure de sauvegarde et de restauration testée.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une base de données contient ce que ton client a de plus précieux : ses clients, ses ventes, parfois des paiements. Deux risques menacent ces données : la fuite ou la modification par quelqu’un qui n’aurait pas dû y accéder, et la perte pure et simple, par erreur humaine, panne de disque ou attaque. Beaucoup de petits projets se connectent en superutilisateur depuis l’application et n’ont jamais testé une restauration. Le jour de l’incident, c’est trop tard. Cette leçon te donne les réflexes d’un administrateur sérieux.

## Les concepts clés

### Le principe du moindre privilège

Chaque compte ne reçoit que les droits dont il a besoin. L’application web n’a pas besoin de supprimer des tables ni de créer d’autres utilisateurs : elle a besoin de lire et d’écrire dans les tables d’une seule base. Si ce compte est compromis, les dégâts restent limités. Le compte administrateur, lui, sert uniquement aux opérations d’administration et n’est jamais écrit dans le code de l’application.

### Créer des comptes et accorder des droits

CREATE USER définit un compte, lié à un hôte autorisé : une connexion locale ou une adresse précise plutôt que n’importe où. GRANT accorde des privilèges sur une base ou une table, par exemple SELECT, INSERT, UPDATE et DELETE. REVOKE retire un privilège. SHOW GRANTS affiche les droits d’un compte, ce qui permet de contrôler ce que tu as réellement donné. Évite les droits globaux sur toutes les bases et n’utilise pas la clause qui permet de transmettre ses droits sauf besoin précis.

### Mots de passe et connexions

Utilise des mots de passe longs et uniques, stockés dans des variables d’environnement ou un gestionnaire de secrets, jamais dans le dépôt Git. Le compte root ne doit pas être accessible depuis Internet. Limite l’exposition réseau du serveur de base de données à ce qui est nécessaire.

### Les sauvegardes

La sauvegarde logique consiste à exporter la base en fichier SQL avec l’utilitaire mysqldump, qui s’exécute en ligne de commande. La règle d’or est la règle 3-2-1 : trois copies, sur deux supports différents, dont une hors site. Surtout, une sauvegarde qui n’a jamais été restaurée n’est pas une sauvegarde : planifie des tests de restauration sur une base séparée et automatise l’export, par exemple avec une tâche planifiée quotidienne.

## Exemple pas à pas

Le code d’exemple se lit en quatre temps. À l’étape 1, on crée un compte applicatif restreint à une connexion locale. À l’étape 2, on lui accorde seulement les quatre privilèges de manipulation de données sur une base précise. À l’étape 3, on vérifie avec SHOW GRANTS. À l’étape 4, on crée un compte en lecture seule pour les rapports. Les commandes de sauvegarde et de restauration sont fournies en commentaires, car elles s’exécutent dans un terminal et non dans le client SQL.

## Erreurs fréquentes

- Connecter l’application avec le compte root : une faille d’injection peut alors tout détruire. Crée un compte dédié aux droits limités.
- Accorder tous les privilèges sur toutes les bases par facilité : le moindre incident devient total. Accorde par base, puis par table si nécessaire.
- Laisser des mots de passe en clair dans le code ou dans Git : ils fuitent avec le dépôt. Passe par l’environnement et change les secrets déjà exposés.
- Faire des sauvegardes sans jamais les restaurer : on découvre qu’elles sont vides ou corrompues au pire moment. Teste la restauration régulièrement.
- Stocker la sauvegarde sur le même disque que la base : une panne emporte les deux. Copie vers un autre support ou un stockage distant.
- Autoriser la connexion depuis n’importe quelle adresse : le serveur devient une cible. Restreins l’hôte et le pare-feu.

## Bonnes pratiques

- Un compte par application et par usage : application, rapports, administration.
- Revois régulièrement les droits avec SHOW GRANTS et retire ce qui n’est plus utile.
- Automatise les sauvegardes et conserve plusieurs générations.
- Chiffre ou protège les fichiers de sauvegarde, car ils contiennent toutes les données.
- Documente la procédure de restauration pour que quelqu’un d’autre puisse l’exécuter.

## Auto-évaluation

- Pourquoi l’application ne doit-elle pas se connecter en root ?
- Quelle commande permet de vérifier les droits d’un compte ?
- Que signifie le principe du moindre privilège ?
- Pourquoi une sauvegarde non testée est-elle risquée ?
- En quoi consiste la règle 3-2-1 ?

## À retenir

- Donne à chaque compte uniquement les droits nécessaires.
- Les secrets ne vont jamais dans le code ni dans Git.
- Automatise les sauvegardes et conserve-les hors du serveur principal.
- Teste la restauration : c’est elle qui prouve que la sauvegarde sert.
- Contrôle les droits réels avec SHOW GRANTS.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : compte applicatif, utilisable seulement depuis le serveur local
-- (remplace le mot de passe par un secret fort stocké hors du dépôt)
CREATE USER 'app_boutique'@'localhost' IDENTIFIED BY 'MotDePasseFortAChanger_2026';

-- Étape 2 : uniquement les droits de manipulation de données, sur une seule base
GRANT SELECT, INSERT, UPDATE, DELETE ON boutique.* TO 'app_boutique'@'localhost';

-- Étape 3 : contrôle des droits réellement accordés
SHOW GRANTS FOR 'app_boutique'@'localhost';

-- Étape 4 : compte en lecture seule pour les rapports et le comptable
CREATE USER 'rapports_boutique'@'localhost' IDENTIFIED BY 'AutreMotDePasseFort_2026';
GRANT SELECT ON boutique.* TO 'rapports_boutique'@'localhost';

-- Retirer un droit devenu inutile
-- REVOKE DELETE ON boutique.* FROM 'app_boutique'@'localhost';

-- Supprimer un compte qui n'est plus utilisé
-- DROP USER 'rapports_boutique'@'localhost';

-- Sauvegarde logique (à lancer dans un terminal, pas dans le client SQL) :
--   mysqldump -u admin -p --single-transaction boutique > sauvegarde_boutique.sql
-- Restauration dans une base de test pour vérifier que la sauvegarde est exploitable :
--   mysql -u admin -p -e "CREATE DATABASE boutique_test"
--   mysql -u admin -p boutique_test < sauvegarde_boutique.sql
-- Planification quotidienne (exemple de tâche cron à 2 h du matin) :
--   0 2 * * * mysqldump -u admin --single-transaction boutique > /sauvegardes/boutique.sql
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Plan de sécurité d’une base d’école',
            'exercise_description' => <<<'TXT'
Écris un script SQL qui sécurise la base « ecole » pour trois profils : l’application, la secrétaire qui consulte les données, et un script d’import des notes. Documente aussi la procédure de sauvegarde en commentaires.

Critères de réussite :
- Trois comptes sont créés, tous restreints à l’hôte localhost.
- Le compte applicatif reçoit SELECT, INSERT, UPDATE et DELETE sur la base ecole uniquement, sans droit global.
- Le compte de consultation reçoit uniquement SELECT sur ecole.
- Le compte d’import reçoit uniquement SELECT et INSERT sur la table notes.
- Une commande SHOW GRANTS est écrite pour chaque compte, et la commande de sauvegarde ainsi que celle de restauration sont données en commentaires.
TXT,
            'exercise_hint' => 'Pour limiter à une table, écris GRANT ... ON ecole.notes TO ... au lieu de ecole.*. Les commandes mysqldump et mysql se placent en commentaires car elles s’exécutent hors du client SQL.',
            'exercise_solution' => <<<'CODE'
-- Trois comptes distincts, tous limités à la connexion locale
CREATE USER 'app_ecole'@'localhost' IDENTIFIED BY 'SecretApplication_ChangeMoi1';
CREATE USER 'secretariat_ecole'@'localhost' IDENTIFIED BY 'SecretLecture_ChangeMoi2';
CREATE USER 'import_notes'@'localhost' IDENTIFIED BY 'SecretImport_ChangeMoi3';

-- Application : manipulation des données de la seule base ecole
GRANT SELECT, INSERT, UPDATE, DELETE ON ecole.* TO 'app_ecole'@'localhost';

-- Secrétariat : lecture seule sur toute la base ecole
GRANT SELECT ON ecole.* TO 'secretariat_ecole'@'localhost';

-- Script d'import : lecture et insertion uniquement sur la table notes
GRANT SELECT, INSERT ON ecole.notes TO 'import_notes'@'localhost';

-- Contrôle des droits de chaque compte
SHOW GRANTS FOR 'app_ecole'@'localhost';
SHOW GRANTS FOR 'secretariat_ecole'@'localhost';
SHOW GRANTS FOR 'import_notes'@'localhost';

-- Sauvegarde (terminal) :
--   mysqldump -u admin -p --single-transaction ecole > sauvegarde_ecole.sql
-- Restauration de test (terminal) :
--   mysql -u admin -p -e "CREATE DATABASE ecole_test"
--   mysql -u admin -p ecole_test < sauvegarde_ecole.sql
-- Règle : la sauvegarde est copiée sur un autre support et la restauration est testée chaque mois.
CODE,
        ],

        'Projet final MySQL' => [
            'description' => 'Mini-projet : concevoir et livrer le schéma complet d’une application de livraison de repas avec contraintes, index, requêtes d’analyse et transaction de commande.',
            'objective' => 'Livrer un script SQL complet et cohérent qui combine modélisation, contraintes, index, requêtes de reporting et une transaction, et qui s’exécute sans erreur sur une base vide.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet de livraison de repas, comme ceux qui fleurissent dans les grandes villes ivoiriennes, rassemble tous les problèmes classiques d’une base de données : des utilisateurs de rôles différents, des produits, des commandes qui passent par plusieurs statuts, des paiements et un historique à conserver. Ce projet final te demande de combiner tout ce que tu as appris : modèle relationnel, SQL, jointures, contraintes, index, transactions et sécurité. C’est aussi un livrable que tu peux présenter dans ton portfolio.

## Les concepts clés

### Partir du besoin, pas des tables

Commence par lister les acteurs et les actions : un client passe une commande dans un restaurant, un livreur la prend en charge, le paiement est enregistré, le statut évolue de « reçue » à « livrée ». De ces phrases sortent les entités : utilisateurs, restaurants, plats, commandes, lignes de commande, paiements et historique des statuts.

### Les statuts et l’historique

Une commande change d’état dans le temps. Plutôt que d’écraser l’ancien statut sans trace, une table d’historique enregistre chaque changement avec sa date. Tu peux ainsi répondre à « quand la commande a-t-elle été livrée ? ». Un CHECK ou un type énuméré limite les statuts aux valeurs autorisées.

### Les montants et la cohérence

Le prix d’un plat peut changer. La ligne de commande doit donc conserver le prix au moment de l’achat, sinon l’historique devient faux. C’est l’un des rares cas où l’on copie volontairement une valeur.

### Valider par des requêtes

Un schéma n’est terminé que lorsqu’il répond aux questions du métier : chiffre d’affaires par restaurant, commandes en cours, meilleurs clients. Ces requêtes valident ton modèle.

## Exemple pas à pas

Le code d’exemple pose le squelette du projet. À l’étape 1, on crée les tables principales avec leurs contraintes. À l’étape 2, on ajoute les index sur les clés étrangères et les colonnes très filtrées. À l’étape 3, on insère un jeu de données de test. À l’étape 4, une transaction enregistre une commande complète avec ses lignes et son premier statut. À l’étape 5, une requête d’analyse calcule le chiffre d’affaires par restaurant et sert de vérification finale.

## Erreurs fréquentes

- Commencer à écrire des tables avant d’avoir listé les acteurs et les actions : le schéma est incomplet. Rédige d’abord les phrases métier.
- Ne stocker que l’id du plat dans la ligne de commande : si le prix change, les anciennes commandes sont fausses. Copie le prix unitaire à l’achat.
- Écraser le statut sans historique : on perd la trace des événements. Ajoute une table d’historique.
- Oublier les index sur les clés étrangères : les jointures deviennent lentes avec le volume. Indexe chaque clé étrangère utilisée.
- Écrire une commande en plusieurs requêtes sans transaction : une panne laisse une commande sans lignes. Entoure l’opération d’une transaction.
- Ne pas tester le script sur une base vide : une dépendance d’ordre de création casse l’exécution. Crée les tables parentes avant les enfants et relance tout depuis zéro.

## Bonnes pratiques

- Découpe le script en sections numérotées et commentées en français.
- Teste l’exécution complète sur une base vide avant de livrer.
- Ajoute des données de test réalistes pour valider les requêtes.
- Protège les règles métier avec des contraintes plutôt qu’avec des conventions.
- Livre aussi un schéma dessiné ou une liste des tables avec leurs relations.

## Auto-évaluation

- Pourquoi copier le prix du plat dans la ligne de commande ?
- Quelle est l’utilité d’une table d’historique de statuts ?
- Dans quel ordre doit-on créer les tables d’un schéma avec clés étrangères ?
- Quelles colonnes mérite-t-on d’indexer dans ce projet ?
- Pourquoi enregistrer une commande dans une transaction ?

## À retenir

- Un bon projet part des acteurs et des actions du métier.
- Les contraintes, les index et les transactions travaillent ensemble pour fiabiliser la base.
- Conserver l’historique évite de perdre l’information utile.
- Les requêtes d’analyse prouvent que le schéma répond au besoin.
- Un script livrable s’exécute depuis zéro sans intervention manuelle.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : schéma de l'application de livraison
CREATE DATABASE IF NOT EXISTS livraison CHARACTER SET utf8mb4; USE livraison;
CREATE TABLE utilisateurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    role VARCHAR(20) NOT NULL DEFAULT 'client',
    CONSTRAINT ck_utilisateurs_role CHECK (role IN ('client', 'livreur', 'admin'))
) ENGINE=InnoDB;
CREATE TABLE restaurants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, nom VARCHAR(150) NOT NULL, quartier VARCHAR(100) NOT NULL
) ENGINE=InnoDB;
CREATE TABLE plats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, restaurant_id BIGINT UNSIGNED NOT NULL,
    nom VARCHAR(150) NOT NULL, prix_fcfa INT UNSIGNED NOT NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
) ENGINE=InnoDB;
CREATE TABLE commandes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL, restaurant_id BIGINT UNSIGNED NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'recue', cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ck_commandes_statut CHECK (statut IN ('recue', 'preparation', 'en_route', 'livree', 'annulee')),
    FOREIGN KEY (client_id) REFERENCES utilisateurs(id), FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
) ENGINE=InnoDB;
CREATE TABLE lignes_commande (
    commande_id BIGINT UNSIGNED NOT NULL, plat_id BIGINT UNSIGNED NOT NULL, quantite INT UNSIGNED NOT NULL,
    prix_unitaire_fcfa INT UNSIGNED NOT NULL, -- prix copié au moment de l'achat
    PRIMARY KEY (commande_id, plat_id),
    CONSTRAINT ck_lignes_quantite CHECK (quantite > 0),
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE, FOREIGN KEY (plat_id) REFERENCES plats(id)
) ENGINE=InnoDB;
CREATE TABLE historique_statuts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, commande_id BIGINT UNSIGNED NOT NULL,
    statut VARCHAR(20) NOT NULL, change_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
) ENGINE=InnoDB;
-- Étape 2 : index sur les colonnes de jointure et de filtrage
CREATE INDEX idx_commandes_client ON commandes (client_id);
CREATE INDEX idx_commandes_statut ON commandes (statut);
CREATE INDEX idx_plats_restaurant ON plats (restaurant_id);
-- Étape 3 : données de test
INSERT INTO utilisateurs (nom, telephone, role) VALUES ('Awa Koné', '0102030405', 'client');
INSERT INTO restaurants (nom, quartier) VALUES ('Chez Tantie Marie', 'Cocody');
INSERT INTO plats (restaurant_id, nom, prix_fcfa) VALUES (1, 'Attiéké poisson', 2500), (1, 'Alloco poulet', 2000);
-- Étape 4 : enregistrement atomique d'une commande complète
START TRANSACTION;
INSERT INTO commandes (client_id, restaurant_id) VALUES (1, 1);
SET @commande = LAST_INSERT_ID();
INSERT INTO lignes_commande (commande_id, plat_id, quantite, prix_unitaire_fcfa) VALUES
    (@commande, 1, 2, 2500), (@commande, 2, 1, 2000);
INSERT INTO historique_statuts (commande_id, statut) VALUES (@commande, 'recue');
COMMIT;
-- Étape 5 : chiffre d'affaires par restaurant (vérification finale)
SELECT r.nom, SUM(l.quantite * l.prix_unitaire_fcfa) AS chiffre_affaires
FROM restaurants r
JOIN commandes c ON c.restaurant_id = r.id
JOIN lignes_commande l ON l.commande_id = c.id
WHERE c.statut <> 'annulee'
GROUP BY r.id, r.nom;
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Projet : base d’une application de livraison',
            'exercise_description' => <<<'TXT'
Conçois et livre le script SQL complet de la base d’une application de livraison de nourriture pour une ville de ton choix. Reprends l’exemple de la leçon comme point de départ, mais personnalise-le avec tes propres règles métier.

Livrables :
- Un fichier schema.sql qui crée la base et au moins six tables : utilisateurs, restaurants, plats, commandes, lignes de commande et historique des statuts.
- Un fichier donnees.sql avec un jeu de données de test réaliste (au moins deux restaurants, quatre plats, deux clients, trois commandes).
- Un fichier requetes.sql avec au moins trois requêtes d’analyse : chiffre d’affaires par restaurant, commandes en cours, meilleurs clients.
- Un fichier securite.sql avec deux comptes aux droits limités (application et rapports) et les commandes de sauvegarde en commentaires.

Critères de réussite :
- Le script s’exécute en entier sur une base vide sans erreur.
- Chaque table a une clé primaire, les clés étrangères sont définies, et au moins un CHECK et un UNIQUE protègent les règles métier.
- Au moins deux index sont créés et justifiés par un commentaire.
- Une commande avec ses lignes et son statut initial est enregistrée dans une transaction.
- Le prix unitaire est copié dans les lignes de commande.
TXT,
            'exercise_hint' => 'Crée les tables parentes avant les tables enfants, utilise LAST_INSERT_ID pour récupérer l’identifiant de la commande dans la transaction, et relance tout le script sur une base fraîchement créée pour le valider.',
            'exercise_solution' => <<<'CODE'
-- ===== schema.sql =====
CREATE DATABASE IF NOT EXISTS livraison_projet CHARACTER SET utf8mb4;
USE livraison_projet;

CREATE TABLE utilisateurs (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(120) NOT NULL,
    telephone VARCHAR(20) NOT NULL UNIQUE,
    role VARCHAR(20) NOT NULL DEFAULT 'client',
    CONSTRAINT ck_utilisateurs_role CHECK (role IN ('client', 'livreur', 'admin'))
) ENGINE=InnoDB;

CREATE TABLE restaurants (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nom VARCHAR(150) NOT NULL,
    quartier VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE plats (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    nom VARCHAR(150) NOT NULL,
    prix_fcfa INT UNSIGNED NOT NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
) ENGINE=InnoDB;

CREATE TABLE commandes (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    client_id BIGINT UNSIGNED NOT NULL,
    restaurant_id BIGINT UNSIGNED NOT NULL,
    statut VARCHAR(20) NOT NULL DEFAULT 'recue',
    cree_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT ck_commandes_statut CHECK (statut IN ('recue', 'preparation', 'en_route', 'livree', 'annulee')),
    FOREIGN KEY (client_id) REFERENCES utilisateurs(id),
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id)
) ENGINE=InnoDB;

CREATE TABLE lignes_commande (
    commande_id BIGINT UNSIGNED NOT NULL,
    plat_id BIGINT UNSIGNED NOT NULL,
    quantite INT UNSIGNED NOT NULL,
    prix_unitaire_fcfa INT UNSIGNED NOT NULL,
    PRIMARY KEY (commande_id, plat_id),
    CONSTRAINT ck_lignes_quantite CHECK (quantite > 0),
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE,
    FOREIGN KEY (plat_id) REFERENCES plats(id)
) ENGINE=InnoDB;

CREATE TABLE historique_statuts (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    commande_id BIGINT UNSIGNED NOT NULL,
    statut VARCHAR(20) NOT NULL,
    change_le TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (commande_id) REFERENCES commandes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Index justifiés : les commandes sont souvent recherchées par client et filtrées par statut
CREATE INDEX idx_commandes_client ON commandes (client_id);
CREATE INDEX idx_commandes_statut ON commandes (statut);
CREATE INDEX idx_plats_restaurant ON plats (restaurant_id);

-- ===== donnees.sql =====
INSERT INTO utilisateurs (nom, telephone, role) VALUES
    ('Awa Koné', '0102030405', 'client'),
    ('Yao Kouassi', '0708091011', 'client');
INSERT INTO restaurants (nom, quartier) VALUES
    ('Chez Tantie Marie', 'Cocody'),
    ('Grillade du Plateau', 'Plateau');
INSERT INTO plats (restaurant_id, nom, prix_fcfa) VALUES
    (1, 'Attiéké poisson', 2500),
    (1, 'Alloco poulet', 2000),
    (2, 'Poulet braisé', 3500),
    (2, 'Brochettes', 1500);

-- Trois commandes, chacune dans une transaction
START TRANSACTION;
INSERT INTO commandes (client_id, restaurant_id) VALUES (1, 1);
SET @c = LAST_INSERT_ID();
INSERT INTO lignes_commande (commande_id, plat_id, quantite, prix_unitaire_fcfa) VALUES (@c, 1, 2, 2500), (@c, 2, 1, 2000);
INSERT INTO historique_statuts (commande_id, statut) VALUES (@c, 'recue');
COMMIT;

START TRANSACTION;
INSERT INTO commandes (client_id, restaurant_id, statut) VALUES (2, 2, 'livree');
SET @c = LAST_INSERT_ID();
INSERT INTO lignes_commande (commande_id, plat_id, quantite, prix_unitaire_fcfa) VALUES (@c, 3, 1, 3500);
INSERT INTO historique_statuts (commande_id, statut) VALUES (@c, 'recue'), (@c, 'livree');
COMMIT;

START TRANSACTION;
INSERT INTO commandes (client_id, restaurant_id) VALUES (1, 2);
SET @c = LAST_INSERT_ID();
INSERT INTO lignes_commande (commande_id, plat_id, quantite, prix_unitaire_fcfa) VALUES (@c, 4, 4, 1500);
INSERT INTO historique_statuts (commande_id, statut) VALUES (@c, 'recue');
COMMIT;

-- ===== requetes.sql =====
-- Chiffre d'affaires par restaurant (commandes non annulées)
SELECT r.nom, SUM(l.quantite * l.prix_unitaire_fcfa) AS chiffre_affaires
FROM restaurants r
JOIN commandes c ON c.restaurant_id = r.id
JOIN lignes_commande l ON l.commande_id = c.id
WHERE c.statut <> 'annulee'
GROUP BY r.id, r.nom;

-- Commandes en cours
SELECT c.id, u.nom AS client, c.statut
FROM commandes c
JOIN utilisateurs u ON u.id = c.client_id
WHERE c.statut IN ('recue', 'preparation', 'en_route');

-- Meilleurs clients par montant dépensé
SELECT u.nom, SUM(l.quantite * l.prix_unitaire_fcfa) AS total
FROM utilisateurs u
JOIN commandes c ON c.client_id = u.id
JOIN lignes_commande l ON l.commande_id = c.id
GROUP BY u.id, u.nom
ORDER BY total DESC;

-- ===== securite.sql =====
CREATE USER 'app_livraison'@'localhost' IDENTIFIED BY 'SecretApp_ChangeMoi1';
CREATE USER 'rapports_livraison'@'localhost' IDENTIFIED BY 'SecretRapports_ChangeMoi2';
GRANT SELECT, INSERT, UPDATE, DELETE ON livraison_projet.* TO 'app_livraison'@'localhost';
GRANT SELECT ON livraison_projet.* TO 'rapports_livraison'@'localhost';
-- Sauvegarde (terminal) : mysqldump -u admin -p --single-transaction livraison_projet > sauvegarde.sql
-- Restauration de test (terminal) : mysql -u admin -p livraison_test < sauvegarde.sql
CODE,
        ],
    ],
];
