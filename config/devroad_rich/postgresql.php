<?php

return [
    'lessons' => [
        'Découvrir PostgreSQL' => [
            'description' => 'Comprendre l’organisation d’un serveur PostgreSQL : cluster, bases de données, schémas et tables, et prendre en main le client en ligne de commande psql.',
            'objective' => 'Créer une base et un schéma, y définir des tables avec des types PostgreSQL adaptés, et parcourir la structure avec les commandes de psql.',
            'content' => <<<'MD'
## Pourquoi cette notion

PostgreSQL est un système de gestion de base de données relationnel réputé pour sa fiabilité, sa conformité au standard SQL et sa richesse fonctionnelle. Il est très utilisé pour les applications sérieuses : SaaS, plateformes de paiement, marketplaces. De nombreux hébergeurs et services managés le proposent, et des outils comme Supabase s’appuient dessus. Savoir l’installer, s’y connecter et comprendre comment il organise les données est le point de départ de tout ce qui suit.

## Les concepts clés

### Serveur, bases et schémas

Un serveur PostgreSQL, souvent appelé cluster, héberge plusieurs bases de données indépendantes. À l’intérieur d’une base, les objets sont rangés dans des schémas, qui fonctionnent comme des dossiers. Le schéma par défaut s’appelle « public ». Utiliser des schémas distincts permet de séparer proprement des domaines, par exemple « ventes » et « comptabilite », et de gérer les droits par schéma.

### Les tables et les types

Une table contient des lignes et des colonnes typées. PostgreSQL propose des types précis : entiers de plusieurs tailles, texte sans limite avec le type TEXT, booléens, dates et horodatages, montants exacts avec NUMERIC, identifiants UUID, et plus tard JSONB. Pour les identifiants auto-incrémentés, on utilise une colonne d’identité ou le type BIGSERIAL, qui s’appuie sur une séquence.

### Le client psql

psql est le client en ligne de commande. Il possède des méta-commandes qui commencent par une barre oblique inverse : lister les bases, se connecter à l’une d’elles, lister les tables, décrire une table, afficher les schémas, quitter. Ces commandes sont propres à psql et ne sont pas du SQL. Elles servent à explorer rapidement une base.

### Rôles et connexion

PostgreSQL gère les accès avec des rôles. Pour te connecter tu fournis un hôte, un port, un nom de base et un rôle. Tu verras ces paramètres dans les chaînes de connexion de tes projets.

## Exemple pas à pas

Le code d’exemple démarre au niveau du serveur. À l’étape 1, on crée une base dédiée à la boutique. À l’étape 2, on se connecte à cette base avec une méta-commande de psql, indiquée en commentaire. À l’étape 3, on crée un schéma nommé « ventes ». À l’étape 4, on y crée une table de clients avec un identifiant BIGSERIAL, un texte obligatoire et un horodatage par défaut. À l’étape 5, on insère un client et on le relit. Les méta-commandes d’exploration sont listées en fin de fichier.

## Erreurs fréquentes

- Chercher une table dans la mauvaise base : PostgreSQL ne permet pas de requêter entre bases par défaut. Vérifie avec la méta-commande de connexion quelle base est active.
- Oublier le préfixe de schéma quand on sort de « public » : la table est introuvable. Écris ventes.clients ou règle le chemin de recherche.
- Mettre un point-virgule manquant dans psql : la commande attend la suite et semble figée. Termine toujours l’instruction SQL par un point-virgule.
- Utiliser des guillemets doubles pour des valeurs texte : PostgreSQL les lit comme des noms d’objets. Les valeurs vont entre guillemets simples.
- Choisir un type FLOAT pour un montant : les arrondis faussent les calculs. Utilise NUMERIC, ou un entier en FCFA.
- Confondre méta-commande psql et SQL : essayer d’exécuter la méta-commande depuis une application échoue. Elle ne marche que dans psql.

## Bonnes pratiques

- Crée une base par application et un schéma par domaine fonctionnel quand le projet grandit.
- Nomme tables et colonnes en minuscules avec des underscores pour éviter les guillemets.
- Choisis TEXT pour du texte libre et TIMESTAMPTZ pour des dates avec fuseau.
- Utilise psql régulièrement pour comprendre la structure réelle plutôt que de la supposer.
- Ne travaille pas avec le rôle administrateur au quotidien.

## Auto-évaluation

- Quelle est la différence entre un cluster, une base et un schéma ?
- Comment accéder à une table qui n’est pas dans le schéma public ?
- Quel type utiliser pour un montant exact ?
- Une méta-commande de psql est-elle du SQL ?
- Pourquoi terminer chaque instruction par un point-virgule dans psql ?

## À retenir

- Un serveur contient des bases, une base contient des schémas, un schéma contient des tables.
- PostgreSQL offre des types riches : TEXT, NUMERIC, TIMESTAMPTZ, UUID, JSONB.
- psql est l’outil d’exploration ; ses méta-commandes ne sont pas du SQL.
- Les valeurs texte utilisent des guillemets simples.
- La connexion se définit par hôte, port, base et rôle.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : création d'une base dédiée (à exécuter depuis la base postgres)
CREATE DATABASE boutique;

-- Étape 2 : se connecter à la nouvelle base (méta-commande psql, pas du SQL)
-- \c boutique

-- Étape 3 : un schéma pour regrouper les objets liés aux ventes
CREATE SCHEMA ventes;

-- Étape 4 : table des clients dans ce schéma
CREATE TABLE ventes.clients (
    id BIGSERIAL PRIMARY KEY,             -- identifiant auto-incrémenté
    nom TEXT NOT NULL,                    -- texte sans limite de longueur
    telephone TEXT NOT NULL,
    solde_fcfa NUMERIC(12, 0) NOT NULL DEFAULT 0,  -- montant exact en FCFA
    cree_le TIMESTAMPTZ NOT NULL DEFAULT now()     -- date avec fuseau horaire
);

-- Étape 5 : insertion et relecture
INSERT INTO ventes.clients (nom, telephone)
VALUES ('Awa Koné', '0102030405');

SELECT id, nom, telephone, cree_le FROM ventes.clients;

-- Méta-commandes utiles pour explorer (à taper dans psql) :
--   \l              liste des bases de données
--   \dn             liste des schémas
--   \dt ventes.*    liste des tables du schéma ventes
--   \d ventes.clients   description détaillée d'une table
--   \q              quitter psql
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Préparer la base d’une école',
            'exercise_description' => <<<'TXT'
Prépare l’environnement PostgreSQL d’une application de gestion d’école : base, schéma et première table.

Critères de réussite :
- Le script crée une base nommée « ecole » et indique en commentaire la méta-commande pour s’y connecter.
- Un schéma « scolarite » est créé.
- Une table scolarite.eleves est créée avec une clé BIGSERIAL, un nom en TEXT NOT NULL, une date de naissance de type DATE et un horodatage TIMESTAMPTZ par défaut.
- Deux élèves sont insérés puis relus par un SELECT qualifié par le schéma.
- Les méta-commandes psql pour lister les schémas, les tables et décrire la table sont données en commentaires.
TXT,
            'exercise_hint' => 'Une méta-commande psql commence par une barre oblique inverse et ne se termine pas par un point-virgule. Préfixe la table avec son schéma dans le CREATE TABLE et dans les requêtes.',
            'exercise_solution' => <<<'CODE'
-- Création de la base (depuis la base postgres)
CREATE DATABASE ecole;

-- Connexion à la base (méta-commande psql)
-- \c ecole

-- Schéma dédié à la scolarité
CREATE SCHEMA scolarite;

-- Table des élèves
CREATE TABLE scolarite.eleves (
    id BIGSERIAL PRIMARY KEY,
    nom TEXT NOT NULL,
    date_naissance DATE NOT NULL,
    cree_le TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Deux élèves de test
INSERT INTO scolarite.eleves (nom, date_naissance) VALUES
    ('Kouadio Yao', '2012-03-14'),
    ('Fatou Traoré', '2011-09-02');

-- Relecture
SELECT id, nom, date_naissance FROM scolarite.eleves;

-- Méta-commandes d'exploration :
--   \dn                  liste des schémas
--   \dt scolarite.*      tables du schéma scolarite
--   \d scolarite.eleves  description de la table
CODE,
        ],

        'SQL et CRUD' => [
            'description' => 'SELECT, INSERT, UPDATE et DELETE dans PostgreSQL, avec la clause RETURNING qui renvoie directement les lignes affectées et le comportement de ON CONFLICT.',
            'objective' => 'Écrire des requêtes CRUD complètes avec filtres, tri et pagination, utiliser RETURNING pour récupérer les valeurs générées et gérer un doublon avec ON CONFLICT.',
            'content' => <<<'MD'
## Pourquoi cette notion

Chaque fonctionnalité d’une application revient à créer, lire, modifier ou supprimer des données : inscrire un client, afficher le catalogue, corriger un prix, retirer une annonce. PostgreSQL ajoute à SQL des outils très pratiques qui simplifient le code applicatif. La clause RETURNING évite une requête supplémentaire pour récupérer l’identifiant d’une ligne fraîchement insérée, et ON CONFLICT permet d’écrire proprement des opérations « insérer ou mettre à jour ».

## Les concepts clés

### Lire avec SELECT

SELECT choisit les colonnes, FROM la table, WHERE filtre les lignes, ORDER BY trie, LIMIT et OFFSET paginent. Pour le texte, LIKE est sensible à la casse alors que ILIKE ne l’est pas, une particularité de PostgreSQL très utile pour les recherches. IN teste l’appartenance à une liste, BETWEEN un intervalle. Pour tester l’absence de valeur, utilise IS NULL, jamais le signe égal.

### Insérer avec INSERT

INSERT INTO ajoute une ou plusieurs lignes ; liste toujours les colonnes. La clause RETURNING renvoie les colonnes demandées des lignes insérées, par exemple l’identifiant généré. C’est la façon idiomatique de connaître l’id d’un nouvel enregistrement.

### Modifier et supprimer

UPDATE ... SET change des valeurs, DELETE FROM supprime des lignes. Les deux ont besoin d’un WHERE précis : sans lui, toute la table est concernée. RETURNING fonctionne aussi avec eux et permet de voir exactement ce qui a été modifié ou supprimé, ce qui est un excellent garde-fou.

### Gérer les doublons avec ON CONFLICT

Quand une insertion viole une contrainte d’unicité, la requête échoue normalement. Avec ON CONFLICT, tu choisis : DO NOTHING ignore la ligne en doublon, DO UPDATE met à jour la ligne existante. Dans DO UPDATE, le mot EXCLUDED désigne les valeurs que tu tentais d’insérer. On parle d’upsert.

### Ordre logique d’une requête

PostgreSQL applique FROM, puis WHERE, puis GROUP BY, HAVING, SELECT et enfin ORDER BY et LIMIT. Cela explique pourquoi un alias défini dans SELECT n’est pas utilisable dans WHERE.

## Exemple pas à pas

Le code d’exemple gère un catalogue. À l’étape 1, on crée la table avec une contrainte d’unicité sur la référence. À l’étape 2, on insère des produits et on récupère leurs identifiants avec RETURNING. À l’étape 3, on lit avec un filtre, un tri et une pagination. À l’étape 4, on utilise ON CONFLICT pour faire un upsert sur une référence existante. À l’étape 5, on met à jour un prix avec RETURNING, puis on supprime les produits en rupture en affichant ce qui est retiré.

## Erreurs fréquentes

- Lancer UPDATE ou DELETE sans WHERE : toute la table est touchée. Écris d’abord un SELECT équivalent, puis ajoute RETURNING pour contrôler.
- Utiliser des guillemets doubles pour une valeur texte : PostgreSQL cherche une colonne de ce nom. Utilise des guillemets simples.
- Comparer à NULL avec le signe égal : le résultat n’est jamais vrai. Utilise IS NULL ou IS NOT NULL.
- Se servir de OFFSET très grand pour paginer : le moteur doit lire et jeter toutes les lignes précédentes. Pour de gros volumes, pagine par curseur sur une colonne triée.
- Faire une requête pour insérer puis une autre pour retrouver l’id : c’est plus lent et sujet aux courses. Utilise RETURNING id.
- Gérer les doublons par un SELECT préalable puis un INSERT : deux requêtes concurrentes passent toutes les deux. Laisse la contrainte d’unicité et ON CONFLICT trancher.

## Bonnes pratiques

- Utilise RETURNING pour obtenir id et valeurs calculées dans la même requête.
- Vérifie les UPDATE et DELETE sensibles par un SELECT préalable ou dans une transaction.
- Préfère ILIKE pour les recherches insensibles à la casse sur de petits volumes.
- Liste explicitement les colonnes plutôt que d’utiliser l’étoile dans le code applicatif.
- Passe toujours les valeurs saisies par l’utilisateur par des requêtes préparées.

## Auto-évaluation

- À quoi sert la clause RETURNING et avec quelles instructions s’utilise-t-elle ?
- Quelle est la différence entre LIKE et ILIKE ?
- Que fait ON CONFLICT DO UPDATE et que désigne EXCLUDED ?
- Pourquoi OFFSET devient-il coûteux sur de grandes tables ?
- Comment tester qu’une colonne est vide au sens de NULL ?

## À retenir

- CRUD correspond à INSERT, SELECT, UPDATE et DELETE.
- RETURNING évite des allers-retours et sécurise les modifications.
- ON CONFLICT implémente proprement l’upsert.
- WHERE est indispensable pour UPDATE et DELETE.
- Les valeurs texte utilisent des guillemets simples ; NULL se teste avec IS NULL.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : table de catalogue avec une référence unique
CREATE TABLE produits (
    id BIGSERIAL PRIMARY KEY,
    reference TEXT NOT NULL UNIQUE,
    libelle TEXT NOT NULL,
    prix_fcfa INTEGER NOT NULL,
    stock INTEGER NOT NULL DEFAULT 0
);

-- Étape 2 : insertion multiple, RETURNING renvoie les identifiants générés
INSERT INTO produits (reference, libelle, prix_fcfa, stock) VALUES
    ('RIZ-5KG', 'Riz 5 kg', 4500, 40),
    ('HUI-1L', 'Huile 1 L', 1500, 0),
    ('SAV-01', 'Savon', 300, 200)
RETURNING id, reference;

-- Étape 3 : lecture filtrée, triée et paginée (recherche insensible à la casse)
SELECT id, libelle, prix_fcfa
FROM produits
WHERE libelle ILIKE '%riz%' OR prix_fcfa < 1000
ORDER BY prix_fcfa ASC
LIMIT 10 OFFSET 0;

-- Étape 4 : upsert, si la référence existe déjà on met à jour le prix et le stock
INSERT INTO produits (reference, libelle, prix_fcfa, stock)
VALUES ('RIZ-5KG', 'Riz 5 kg', 4800, 60)
ON CONFLICT (reference)
DO UPDATE SET prix_fcfa = EXCLUDED.prix_fcfa, stock = EXCLUDED.stock
RETURNING id, prix_fcfa, stock;

-- Étape 5 : mise à jour ciblée, avec contrôle du résultat grâce à RETURNING
UPDATE produits SET prix_fcfa = prix_fcfa + 100
WHERE reference = 'SAV-01'
RETURNING reference, prix_fcfa;

-- Suppression des produits en rupture, en affichant ce qui est retiré
DELETE FROM produits WHERE stock = 0 RETURNING id, libelle;
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Annuaire de prestataires avec upsert',
            'exercise_description' => <<<'TXT'
Gère un annuaire de prestataires (plombiers, électriciens, coiffeurs) dans une table « prestataires » (id, telephone unique, nom, metier, note).

Critères de réussite :
- La table est créée avec une clé BIGSERIAL et une contrainte UNIQUE sur le téléphone.
- Trois prestataires sont insérés en une requête avec RETURNING id, nom.
- Une requête ILIKE recherche les prestataires dont le métier contient « plomb », triés par note décroissante avec LIMIT 5.
- Un upsert avec ON CONFLICT (telephone) DO UPDATE met à jour le nom et la note d’un prestataire existant.
- Un DELETE avec WHERE supprime les prestataires dont la note est NULL et affiche les lignes supprimées avec RETURNING.
TXT,
            'exercise_hint' => 'Dans ON CONFLICT DO UPDATE, utilise EXCLUDED.nom et EXCLUDED.note pour réutiliser les valeurs proposées. La condition sur NULL s’écrit IS NULL.',
            'exercise_solution' => <<<'CODE'
-- Table des prestataires
CREATE TABLE prestataires (
    id BIGSERIAL PRIMARY KEY,
    telephone TEXT NOT NULL UNIQUE,
    nom TEXT NOT NULL,
    metier TEXT NOT NULL,
    note NUMERIC(2, 1)
);

-- Trois prestataires insérés en une requête
INSERT INTO prestataires (telephone, nom, metier, note) VALUES
    ('0101010101', 'Koffi Plomberie', 'Plombier', 4.5),
    ('0202020202', 'Ibrahim Électricité', 'Électricien', 4.0),
    ('0303030303', 'Salon Aminata', 'Coiffeuse', NULL)
RETURNING id, nom;

-- Recherche insensible à la casse, meilleurs d'abord
SELECT id, nom, note
FROM prestataires
WHERE metier ILIKE '%plomb%'
ORDER BY note DESC
LIMIT 5;

-- Upsert : le téléphone existe déjà, on met à jour nom et note
INSERT INTO prestataires (telephone, nom, metier, note)
VALUES ('0101010101', 'Koffi Plomberie Pro', 'Plombier', 4.8)
ON CONFLICT (telephone)
DO UPDATE SET nom = EXCLUDED.nom, note = EXCLUDED.note
RETURNING id, nom, note;

-- Suppression des prestataires sans note, avec affichage des lignes retirées
DELETE FROM prestataires WHERE note IS NULL RETURNING id, nom;
CODE,
        ],

        'Relations et contraintes' => [
            'description' => 'Clés primaires et étrangères, jointures, UNIQUE, CHECK et comportements ON DELETE pour construire un modèle de données fiable dans PostgreSQL.',
            'objective' => 'Modéliser trois tables liées avec des contraintes qui protègent les règles métier, et écrire des jointures qui reconstituent les vues utiles.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dès qu’une application dépasse une seule table, il faut relier des données : une commande appartient à un client, une annonce à un vendeur, un paiement à une commande. PostgreSQL permet de faire respecter ces liens et les règles métier directement dans la base. C’est capital : l’application web, l’application mobile, les scripts d’import et les administrateurs écrivent tous dans la même base, et aucun ne doit pouvoir corrompre les données.

## Les concepts clés

### Clé primaire et clé étrangère

La clé primaire identifie une ligne de manière unique et non nulle. La clé étrangère référence la clé primaire d’une autre table, et PostgreSQL refuse toute valeur qui ne correspond à aucune ligne existante. Un client peut avoir plusieurs commandes : la colonne client_id se place du côté « plusieurs ». Une relation plusieurs-à-plusieurs passe par une table de liaison portant deux clés étrangères.

### Les contraintes de validité

NOT NULL impose une valeur. UNIQUE interdit les doublons, éventuellement sur une combinaison de colonnes. CHECK vérifie une condition, par exemple un montant positif ou un statut parmi une liste de valeurs autorisées. Ces règles sont vérifiées à chaque écriture, quelle que soit l’origine de la requête.

### Le comportement à la suppression

ON DELETE détermine ce qui arrive aux lignes enfants quand le parent est supprimé. NO ACTION ou RESTRICT refuse la suppression tant que des enfants existent. CASCADE supprime aussi les enfants, adapté à des détails sans valeur propre. SET NULL met la clé à NULL. Pour des données financières, choisis la protection plutôt que la cascade.

### Les jointures

JOIN, ou INNER JOIN, ne garde que les lignes qui ont une correspondance des deux côtés. LEFT JOIN garde toutes les lignes de gauche et met NULL à droite s’il n’y a pas de correspondance, ce qui permet de trouver les clients sans commande. Les alias de tables raccourcissent les requêtes et évitent les ambiguïtés.

### Une particularité de PostgreSQL

Contrairement à d’autres systèmes, PostgreSQL ne crée pas automatiquement d’index sur les colonnes de clé étrangère. Les clés primaires et les contraintes UNIQUE en ont un, mais pas les clés étrangères côté enfant : tu les indexes toi-même si tu fais souvent des jointures ou des suppressions de parent.

## Exemple pas à pas

Le code d’exemple relie clients, commandes et paiements. À l’étape 1, la table des clients impose un email unique. À l’étape 2, les commandes référencent les clients avec RESTRICT et limitent les statuts par CHECK. À l’étape 3, les paiements référencent les commandes et exigent un montant positif. À l’étape 4, on indexe les clés étrangères. À l’étape 5, on insère des données et on les relit avec une jointure puis un LEFT JOIN pour repérer les commandes sans paiement.

## Erreurs fréquentes

- Ne pas déclarer la clé étrangère, en comptant sur l’application : des commandes orphelines apparaissent. Déclare toujours la contrainte.
- Utiliser CASCADE sur des données financières : supprimer un client efface ses paiements. Préfère RESTRICT et une désactivation logique.
- Oublier d’indexer la clé étrangère côté enfant : jointures et suppressions du parent deviennent lentes. Crée l’index manuellement.
- Utiliser JOIN quand on veut conserver les lignes sans correspondance : des clients ou commandes disparaissent du rapport. Utilise LEFT JOIN.
- Valider un statut uniquement dans le code : une valeur inattendue entre par un script. Ajoute un CHECK.
- Ajouter une contrainte sur une table déjà remplie sans nettoyer : l’ajout échoue. Repère les lignes fautives avec un SELECT et corrige d’abord.

## Bonnes pratiques

- Donne des noms explicites aux contraintes pour que les messages d’erreur soient lisibles.
- Indexe les clés étrangères des tables enfants.
- Choisis RESTRICT par défaut et CASCADE uniquement pour des données purement dépendantes.
- Teste chaque contrainte avec une insertion volontairement invalide.
- Écris les contraintes dans des migrations versionnées.

## Auto-évaluation

- Que se passe-t-il quand on insère une clé étrangère qui n’existe pas dans la table parente ?
- Quelle différence entre ON DELETE RESTRICT et ON DELETE CASCADE ?
- Comment trouver les clients qui n’ont jamais commandé ?
- Pourquoi faut-il indexer manuellement les clés étrangères dans PostgreSQL ?
- À quoi sert une contrainte CHECK ?

## À retenir

- Les contraintes protègent les données quelle que soit la source d’écriture.
- Une clé étrangère garantit qu’une référence pointe vers une ligne existante.
- Le choix de ON DELETE dépend de la valeur des données enfants.
- LEFT JOIN conserve les lignes sans correspondance.
- Les clés étrangères ne sont pas indexées automatiquement.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : clients, avec un email unique
CREATE TABLE clients (
    id BIGSERIAL PRIMARY KEY,
    nom TEXT NOT NULL,
    email TEXT NOT NULL,
    CONSTRAINT uq_clients_email UNIQUE (email)
);

-- Étape 2 : commandes liées aux clients, statuts limités par un CHECK
CREATE TABLE commandes (
    id BIGSERIAL PRIMARY KEY,
    client_id BIGINT NOT NULL,
    statut TEXT NOT NULL DEFAULT 'en_attente',
    cree_le TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT ck_commandes_statut CHECK (statut IN ('en_attente', 'payee', 'livree', 'annulee')),
    CONSTRAINT fk_commandes_client FOREIGN KEY (client_id)
        REFERENCES clients (id) ON DELETE RESTRICT
);

-- Étape 3 : paiements liés aux commandes, montant strictement positif
CREATE TABLE paiements (
    id BIGSERIAL PRIMARY KEY,
    commande_id BIGINT NOT NULL,
    montant_fcfa NUMERIC(12, 0) NOT NULL,
    operateur TEXT NOT NULL,
    CONSTRAINT ck_paiements_montant CHECK (montant_fcfa > 0),
    CONSTRAINT fk_paiements_commande FOREIGN KEY (commande_id)
        REFERENCES commandes (id) ON DELETE RESTRICT
);

-- Étape 4 : PostgreSQL n'indexe pas automatiquement les clés étrangères côté enfant
CREATE INDEX idx_commandes_client_id ON commandes (client_id);
CREATE INDEX idx_paiements_commande_id ON paiements (commande_id);

-- Étape 5 : données de test puis jointures
INSERT INTO clients (nom, email) VALUES ('Awa Koné', 'awa@example.com'), ('Yao Kouassi', 'yao@example.com');
INSERT INTO commandes (client_id, statut) VALUES (1, 'payee'), (2, 'en_attente');
INSERT INTO paiements (commande_id, montant_fcfa, operateur) VALUES (1, 12000, 'wave');

-- Chaque commande avec le nom de son client
SELECT c.nom, o.id AS commande, o.statut
FROM commandes o
JOIN clients c ON c.id = o.client_id;

-- Commandes sans aucun paiement (LEFT JOIN puis test de NULL)
SELECT o.id, o.statut
FROM commandes o
LEFT JOIN paiements p ON p.commande_id = o.id
WHERE p.id IS NULL;
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Modèle d’une marketplace d’annonces',
            'exercise_description' => <<<'TXT'
Modélise une petite marketplace avec trois tables : « vendeurs », « annonces » et « messages ». Un vendeur publie plusieurs annonces, et chaque annonce reçoit plusieurs messages d’acheteurs.

Critères de réussite :
- vendeurs a un email UNIQUE et NOT NULL.
- annonces référence vendeurs avec ON DELETE RESTRICT et possède un CHECK imposant un prix supérieur ou égal à zéro.
- messages référence annonces avec ON DELETE CASCADE et une colonne contenu NOT NULL.
- Un index est créé sur chaque clé étrangère des tables enfants.
- Une jointure affiche le vendeur, le titre de l’annonce et le nombre de messages, et un LEFT JOIN retrouve les annonces sans message.
TXT,
            'exercise_hint' => 'Pour compter les messages par annonce, fais un LEFT JOIN de messages puis GROUP BY l’identifiant de l’annonce avec COUNT sur la colonne id des messages. Les annonces sans message ont ce COUNT à zéro.',
            'exercise_solution' => <<<'CODE'
-- Vendeurs : email unique et obligatoire
CREATE TABLE vendeurs (
    id BIGSERIAL PRIMARY KEY,
    nom TEXT NOT NULL,
    email TEXT NOT NULL,
    CONSTRAINT uq_vendeurs_email UNIQUE (email)
);

-- Annonces : le vendeur ne peut pas être supprimé tant qu'il a des annonces
CREATE TABLE annonces (
    id BIGSERIAL PRIMARY KEY,
    vendeur_id BIGINT NOT NULL,
    titre TEXT NOT NULL,
    prix_fcfa INTEGER NOT NULL,
    CONSTRAINT ck_annonces_prix CHECK (prix_fcfa >= 0),
    CONSTRAINT fk_annonces_vendeur FOREIGN KEY (vendeur_id)
        REFERENCES vendeurs (id) ON DELETE RESTRICT
);

-- Messages : supprimés avec leur annonce
CREATE TABLE messages (
    id BIGSERIAL PRIMARY KEY,
    annonce_id BIGINT NOT NULL,
    contenu TEXT NOT NULL,
    cree_le TIMESTAMPTZ NOT NULL DEFAULT now(),
    CONSTRAINT fk_messages_annonce FOREIGN KEY (annonce_id)
        REFERENCES annonces (id) ON DELETE CASCADE
);

-- Index sur les clés étrangères des tables enfants
CREATE INDEX idx_annonces_vendeur_id ON annonces (vendeur_id);
CREATE INDEX idx_messages_annonce_id ON messages (annonce_id);

-- Données de test
INSERT INTO vendeurs (nom, email) VALUES ('Koffi', 'koffi@example.com');
INSERT INTO annonces (vendeur_id, titre, prix_fcfa) VALUES (1, 'Téléphone d''occasion', 45000), (1, 'Table en bois', 25000);
INSERT INTO messages (annonce_id, contenu) VALUES (1, 'Encore disponible ?'), (1, 'Dernier prix ?');

-- Vendeur, titre et nombre de messages par annonce
SELECT v.nom, a.titre, COUNT(m.id) AS nb_messages
FROM annonces a
JOIN vendeurs v ON v.id = a.vendeur_id
LEFT JOIN messages m ON m.annonce_id = a.id
GROUP BY v.nom, a.id, a.titre;

-- Annonces sans aucun message
SELECT a.id, a.titre
FROM annonces a
LEFT JOIN messages m ON m.annonce_id = a.id
WHERE m.id IS NULL;
CODE,
        ],

        'Fonctionnalités PostgreSQL' => [
            'description' => 'JSONB, tableaux et types spécialisés : savoir quand ces fonctionnalités avancées apportent un vrai gain et comment les interroger et les indexer.',
            'objective' => 'Stocker et interroger des données semi-structurées avec JSONB et des tableaux, créer un index GIN adapté et expliquer quand préférer des colonnes classiques.',
            'content' => <<<'MD'
## Pourquoi cette notion

Les données réelles ne rentrent pas toujours dans des colonnes fixes. Un catalogue de produits peut avoir des attributs différents selon la catégorie. Un journal d’événements d’une application mobile contient des charges utiles variables. Un article de blog porte une liste d’étiquettes. PostgreSQL permet de garder la rigueur relationnelle tout en stockant ces structures souples dans des colonnes JSONB et des tableaux, avec des opérateurs et des index dédiés.

## Les concepts clés

### JSON et JSONB

PostgreSQL propose deux types JSON. JSON conserve le texte tel quel. JSONB le stocke dans un format binaire décomposé, plus rapide à interroger et indexable. Dans presque tous les cas, tu choisis JSONB. Les clés dupliquées sont dédupliquées et l’ordre des clés n’est pas conservé.

### Extraire et filtrer

L’opérateur flèche simple extrait un champ en gardant le type JSONB, la double flèche extrait le champ en texte. Pour accéder à un niveau imbriqué, tu enchaînes les flèches ou tu utilises l’opérateur de chemin. L’opérateur de contenance, qui ressemble à un arobase suivi d’un chevron, teste si un document contient une structure donnée : c’est l’opérateur à privilégier pour les filtres. L’opérateur point d’interrogation teste la présence d’une clé.

### Modifier du JSONB

La fonction jsonb_set remplace une valeur à un chemin donné, et l’opérateur de concaténation fusionne deux documents. Chaque modification produit un nouveau document : pour de très gros JSONB modifiés souvent, la colonne devient coûteuse.

### Les tableaux

Une colonne peut contenir un tableau, par exemple TEXT[] pour des étiquettes. Tu les interroges avec ANY pour tester l’appartenance, avec l’opérateur de contenance pour vérifier qu’un tableau contient des éléments, et avec unnest pour transformer les éléments en lignes.

### Index GIN

Un index B-tree ordinaire ne sait pas chercher à l’intérieur d’un JSONB ou d’un tableau. L’index GIN est conçu pour cela et accélère les opérateurs de contenance et de présence de clé.

### Quand ne pas l’utiliser

Si une donnée est présente partout, filtrée souvent, jointe ou soumise à des contraintes, fais-en une vraie colonne. JSONB convient aux attributs variables et aux données reçues de l’extérieur, pas à remplacer le modèle relationnel.

## Exemple pas à pas

Le code d’exemple crée une table d’événements d’une boutique en ligne. À l’étape 1, la table reçoit une colonne JSONB pour la charge utile et un tableau d’étiquettes. À l’étape 2, on insère des événements avec des structures différentes. À l’étape 3, on extrait des champs avec les opérateurs flèche. À l’étape 4, on filtre avec l’opérateur de contenance et avec ANY sur le tableau. À l’étape 5, on met à jour une valeur avec jsonb_set, puis on crée un index GIN pour accélérer les filtres.

## Erreurs fréquentes

- Choisir JSON au lieu de JSONB : les requêtes sont plus lentes et non indexables. Utilise JSONB sauf besoin de conserver le texte exact.
- Tout mettre dans une colonne JSONB : on perd contraintes, types et jointures. Garde en colonnes classiques les données centrales.
- Filtrer avec la double flèche sur de gros volumes sans index : la table est parcourue. Utilise la contenance avec un index GIN.
- Oublier que la double flèche renvoie du texte : la comparaison avec un nombre échoue. Convertis explicitement avec un cast.
- Réécrire un énorme document JSONB à chaque petite modification : le coût d’écriture explose. Sépare les parties qui changent souvent.
- Stocker des relations dans un tableau d’identifiants : aucune clé étrangère ne protège ces liens. Utilise une table de liaison.

## Bonnes pratiques

- Réserve JSONB aux attributs variables ou aux données externes.
- Crée un index GIN quand tu filtres souvent sur le contenu d’une colonne JSONB ou d’un tableau.
- Valide la structure attendue avec une contrainte CHECK simple, par exemple la présence d’une clé.
- Garde les champs fréquents, filtrés ou jointés dans de vraies colonnes.
- Mesure avec EXPLAIN avant et après avoir créé l’index.

## Auto-évaluation

- Quelle est la différence entre JSON et JSONB ?
- Quel opérateur renvoie un champ JSONB sous forme de texte ?
- Pourquoi un index B-tree ne suffit-il pas pour chercher dans du JSONB ?
- Dans quel cas préférer une colonne classique à un champ JSONB ?
- Comment tester si un tableau d’étiquettes contient une valeur donnée ?

## À retenir

- JSONB est le type à privilégier pour les données semi-structurées.
- La contenance et la présence de clé s’appuient sur un index GIN.
- Les tableaux conviennent aux petites listes de valeurs simples.
- Les données centrales et contraintes doivent rester en colonnes classiques.
- JSONB complète le modèle relationnel, il ne le remplace pas.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : journal d'événements avec charge utile JSONB et étiquettes en tableau
CREATE TABLE evenements (
    id BIGSERIAL PRIMARY KEY,
    type TEXT NOT NULL,
    charge JSONB NOT NULL,
    etiquettes TEXT[] NOT NULL DEFAULT '{}',
    cree_le TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Étape 2 : des événements aux structures différentes
INSERT INTO evenements (type, charge, etiquettes) VALUES
    ('commande', '{"client": "Awa", "total_fcfa": 12000, "paiement": {"operateur": "wave"}}', ARRAY['mobile', 'promo']),
    ('commande', '{"client": "Yao", "total_fcfa": 3500, "paiement": {"operateur": "orange_money"}}', ARRAY['web']),
    ('connexion', '{"client": "Awa", "appareil": "android"}', ARRAY['mobile']);

-- Étape 3 : extraction de champs (flèche simple = JSONB, double flèche = texte)
SELECT id,
       charge ->> 'client' AS client,
       (charge ->> 'total_fcfa')::INTEGER AS total,
       charge -> 'paiement' ->> 'operateur' AS operateur
FROM evenements
WHERE type = 'commande';

-- Étape 4 : filtres par contenance JSONB et par appartenance au tableau
SELECT id, charge FROM evenements
WHERE charge @> '{"paiement": {"operateur": "wave"}}';

SELECT id, type FROM evenements WHERE 'mobile' = ANY (etiquettes);

-- Étape 5 : modification d'une valeur imbriquée avec jsonb_set
UPDATE evenements
SET charge = jsonb_set(charge, '{total_fcfa}', '13500')
WHERE id = 1
RETURNING charge;

-- Index GIN pour accélérer les opérateurs de contenance sur le JSONB et le tableau
CREATE INDEX idx_evenements_charge ON evenements USING GIN (charge);
CREATE INDEX idx_evenements_etiquettes ON evenements USING GIN (etiquettes);
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Catalogue avec attributs variables',
            'exercise_description' => <<<'TXT'
Crée une table « articles » pour une boutique qui vend des téléphones, des vêtements et des cahiers, chacun avec des attributs différents.

Critères de réussite :
- La table contient id, nom, prix_fcfa en colonnes classiques, une colonne attributs de type JSONB NOT NULL et une colonne mots_cles de type TEXT[].
- Quatre articles sont insérés avec des attributs différents selon leur type (par exemple stockage pour un téléphone, taille pour un vêtement).
- Une requête extrait en texte un attribut avec la double flèche pour tous les articles qui le possèdent.
- Une requête filtre avec l’opérateur de contenance sur les attributs, et une autre avec ANY sur mots_cles.
- Deux index GIN sont créés, un sur attributs et un sur mots_cles.
TXT,
            'exercise_hint' => 'La contenance s’écrit attributs @> suivi d’un littéral JSON entre guillemets simples. Pour le tableau, utilise une valeur = ANY (mots_cles). Le nom et le prix restent en colonnes classiques car ils sont communs à tous les articles.',
            'exercise_solution' => <<<'CODE'
-- Colonnes communes en colonnes classiques, attributs variables en JSONB
CREATE TABLE articles (
    id BIGSERIAL PRIMARY KEY,
    nom TEXT NOT NULL,
    prix_fcfa INTEGER NOT NULL CHECK (prix_fcfa >= 0),
    attributs JSONB NOT NULL,
    mots_cles TEXT[] NOT NULL DEFAULT '{}'
);

-- Quatre articles avec des attributs différents
INSERT INTO articles (nom, prix_fcfa, attributs, mots_cles) VALUES
    ('Smartphone X', 90000, '{"stockage_go": 128, "couleur": "noir"}', ARRAY['telephone', 'promo']),
    ('Robe wax', 15000, '{"taille": "M", "couleur": "bleu"}', ARRAY['vetement']),
    ('Pagne tissé', 25000, '{"taille": "unique", "couleur": "rouge"}', ARRAY['vetement', 'promo']),
    ('Cahier 200 pages', 800, '{"pages": 200}', ARRAY['papeterie']);

-- Extraction du texte de l'attribut couleur pour les articles qui en ont une
SELECT nom, attributs ->> 'couleur' AS couleur
FROM articles
WHERE attributs ? 'couleur';

-- Filtre par contenance JSONB : articles de taille M
SELECT nom FROM articles WHERE attributs @> '{"taille": "M"}';

-- Filtre sur le tableau : articles en promotion
SELECT nom, prix_fcfa FROM articles WHERE 'promo' = ANY (mots_cles);

-- Index GIN pour accélérer ces recherches
CREATE INDEX idx_articles_attributs ON articles USING GIN (attributs);
CREATE INDEX idx_articles_mots_cles ON articles USING GIN (mots_cles);
CODE,
        ],

        'Index et transactions' => [
            'description' => 'Index B-tree, EXPLAIN ANALYZE, transactions et niveaux d’isolation : accélérer les requêtes et garantir la cohérence face aux accès concurrents.',
            'objective' => 'Créer un index justifié par EXPLAIN ANALYZE, écrire une transaction de paiement atomique avec verrouillage de ligne, et expliquer ce que protège chaque niveau d’isolation.',
            'content' => <<<'MD'
## Pourquoi cette notion

Quand le volume de données augmente, une requête qui parcourait cent lignes se met à en parcourir des millions. L’index est l’outil qui évite ce parcours complet. En parallèle, beaucoup d’opérations métier modifient plusieurs lignes : un paiement débite un portefeuille et crédite un autre, une commande diminue des stocks. Si deux utilisateurs agissent en même temps ou si le serveur plante au milieu, les données peuvent devenir incohérentes. Les transactions et l’isolation sont là pour l’empêcher.

## Les concepts clés

### L’index B-tree

L’index par défaut est un arbre équilibré trié. Il accélère l’égalité, les comparaisons, les intervalles et le tri sur la colonne indexée. Il coûte de l’espace et ralentit un peu les écritures, car chaque insertion doit aussi le mettre à jour. On indexe donc ce que les requêtes utilisent vraiment : colonnes de WHERE, de jointure et d’ORDER BY. Un index sur plusieurs colonnes sert quand les requêtes filtrent sur leur combinaison, et l’ordre des colonnes compte.

### Lire un plan avec EXPLAIN

EXPLAIN montre le plan choisi par PostgreSQL, et EXPLAIN ANALYZE exécute vraiment la requête pour afficher les temps réels. Un parcours séquentiel signale une lecture de toute la table, un parcours d’index signale l’usage d’un index. Sur une toute petite table, PostgreSQL préfère parfois le parcours séquentiel, car il est plus rapide : ne conclus qu’avec un volume réaliste. Attention, EXPLAIN ANALYZE exécute les écritures : enveloppe-le dans une transaction annulée si tu testes un UPDATE.

### Les transactions

Une transaction commence par BEGIN et se termine par COMMIT pour valider ou ROLLBACK pour annuler. Elle est atomique : tout ou rien. Elle est cohérente avec les contraintes, isolée des autres sessions et durable une fois validée. En cas d’erreur à l’intérieur d’une transaction, PostgreSQL la marque comme avortée : il faut faire un ROLLBACK.

### Concurrence et verrous

Grâce au MVCC, les lectures ne bloquent pas les écritures. Le niveau d’isolation par défaut est READ COMMITTED : chaque requête voit les données validées au moment où elle démarre. Le niveau REPEATABLE READ donne une vue stable pendant toute la transaction. SERIALIZABLE simule une exécution une par une et peut renvoyer des erreurs à rejouer. SELECT ... FOR UPDATE verrouille les lignes lues pour empêcher qu’une autre transaction les modifie avant la fin de la tienne.

## Exemple pas à pas

Le code d’exemple commence par les index. À l’étape 1, EXPLAIN ANALYZE montre le plan d’une recherche de commandes par date. À l’étape 2, on crée un index et on relance l’analyse. À l’étape 3, on exécute un transfert entre deux portefeuilles : BEGIN, verrouillage du portefeuille débiteur avec FOR UPDATE, deux mises à jour relatives, enregistrement de l’opération, puis COMMIT. À l’étape 4, un bloc en commentaire montre un ROLLBACK après un échec.

## Erreurs fréquentes

- Indexer sans mesurer : l’index n’est pas utilisé et ralentit les écritures. Vérifie avec EXPLAIN ANALYZE avant et après.
- Conclure sur une petite table de test : le plan diffère avec de vraies données. Teste avec un volume réaliste.
- Lire un solde, calculer dans l’application puis réécrire : deux requêtes simultanées s’écrasent. Utilise une mise à jour relative ou FOR UPDATE.
- Laisser une transaction ouverte longtemps : elle bloque d’autres sessions et gêne le nettoyage des anciennes versions. Garde-la courte.
- Continuer après une erreur dans une transaction : toutes les requêtes suivantes sont refusées. Fais un ROLLBACK, puis recommence.
- Appliquer une fonction sur la colonne filtrée, comme lower sur le nom : l’index normal n’est plus utilisé. Crée un index sur l’expression ou réécris la condition.

## Bonnes pratiques

- Indexe les clés étrangères, les colonnes très filtrées et celles de tri fréquent.
- Mesure avec EXPLAIN ANALYZE sur des données représentatives.
- Regroupe toute opération métier multi-lignes dans une transaction courte.
- Utilise des mises à jour relatives et, au besoin, FOR UPDATE.
- Prévois côté application la gestion des erreurs avec ROLLBACK et, en SERIALIZABLE, la relance.

## Auto-évaluation

- Quel est le coût d’un index sur les écritures ?
- Que montre EXPLAIN ANALYZE que EXPLAIN seul ne montre pas ?
- Que signifie qu’une transaction est atomique ?
- À quoi sert SELECT ... FOR UPDATE ?
- Quel niveau d’isolation PostgreSQL utilise-t-il par défaut ?

## À retenir

- Un index accélère les lectures mais a un coût en écriture et en espace.
- EXPLAIN ANALYZE sert à vérifier plutôt qu’à supposer.
- BEGIN, COMMIT et ROLLBACK délimitent une unité de travail atomique.
- FOR UPDATE et les mises à jour relatives évitent les écrasements concurrents.
- Garde les transactions courtes et gère les erreurs explicitement.
MD,
            'code_example' => <<<'CODE'
-- Tables supposées : commandes(id, client_id, cree_le, total_fcfa),
-- portefeuilles(id, titulaire, solde_fcfa CHECK (solde_fcfa >= 0)), operations(id, source_id, dest_id, montant_fcfa)

-- Étape 1 : plan d'exécution avant l'index (chercher « Seq Scan » dans le résultat)
EXPLAIN ANALYZE
SELECT id, total_fcfa FROM commandes WHERE cree_le >= now() - interval '7 days';

-- Étape 2 : création d'un index B-tree, puis nouvelle mesure (chercher « Index Scan »)
CREATE INDEX idx_commandes_cree_le ON commandes (cree_le);
EXPLAIN ANALYZE
SELECT id, total_fcfa FROM commandes WHERE cree_le >= now() - interval '7 days';

-- Étape 3 : transfert atomique de 5000 FCFA du portefeuille 1 vers le portefeuille 2
BEGIN;

-- Verrouillage de la ligne du débiteur : une autre transaction doit attendre
SELECT solde_fcfa FROM portefeuilles WHERE id = 1 FOR UPDATE;

-- Mises à jour relatives : le calcul se fait dans la base
UPDATE portefeuilles SET solde_fcfa = solde_fcfa - 5000 WHERE id = 1;
UPDATE portefeuilles SET solde_fcfa = solde_fcfa + 5000 WHERE id = 2;

-- Trace de l'opération
INSERT INTO operations (source_id, dest_id, montant_fcfa) VALUES (1, 2, 5000);

COMMIT;

-- Étape 4 : cas d'échec, la contrainte CHECK refuse un solde négatif
-- BEGIN;
-- UPDATE portefeuilles SET solde_fcfa = solde_fcfa - 99999999 WHERE id = 1;  -- erreur de contrainte
-- ROLLBACK;   -- la transaction est annulée, rien n'a changé

-- Niveau d'isolation plus strict pour une transaction précise
-- BEGIN ISOLATION LEVEL REPEATABLE READ;
-- ... requêtes ...
-- COMMIT;
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Réservation de places sans survente',
            'exercise_description' => <<<'TXT'
Une salle de cinéma vend des places via une table « seances » (id, titre, places_restantes avec CHECK >= 0) et enregistre les achats dans « reservations » (id, seance_id, nom_client, nombre). Écris un script qui évite la survente.

Critères de réussite :
- Un index est créé sur reservations(seance_id) et un EXPLAIN ANALYZE d’une recherche par seance_id est exécuté.
- La réservation de 2 places se fait dans une transaction : BEGIN, SELECT ... FOR UPDATE sur la séance, UPDATE relatif de places_restantes, INSERT de la réservation, COMMIT.
- Le CHECK sur places_restantes empêche de passer sous zéro.
- Un second bloc montre une réservation impossible qui se termine par ROLLBACK, avec un commentaire expliquant que le stock de places reste inchangé.
- Aucune lecture suivie d’une réécriture du nombre de places depuis l’application n’est utilisée.
TXT,
            'exercise_hint' => 'La mise à jour relative s’écrit places_restantes = places_restantes - 2. Si la contrainte CHECK échoue, la transaction est avortée : le seul bon réflexe est ROLLBACK.',
            'exercise_solution' => <<<'CODE'
-- Tables
CREATE TABLE seances (
    id BIGSERIAL PRIMARY KEY,
    titre TEXT NOT NULL,
    places_restantes INTEGER NOT NULL,
    CONSTRAINT ck_seances_places CHECK (places_restantes >= 0)
);

CREATE TABLE reservations (
    id BIGSERIAL PRIMARY KEY,
    seance_id BIGINT NOT NULL REFERENCES seances (id),
    nom_client TEXT NOT NULL,
    nombre INTEGER NOT NULL CHECK (nombre > 0)
);

INSERT INTO seances (titre, places_restantes) VALUES ('Film de 20 h', 3);

-- Index sur la clé étrangère, puis vérification du plan
CREATE INDEX idx_reservations_seance_id ON reservations (seance_id);
EXPLAIN ANALYZE SELECT * FROM reservations WHERE seance_id = 1;

-- Réservation atomique de 2 places
BEGIN;
SELECT places_restantes FROM seances WHERE id = 1 FOR UPDATE;
UPDATE seances SET places_restantes = places_restantes - 2 WHERE id = 1;
INSERT INTO reservations (seance_id, nom_client, nombre) VALUES (1, 'Awa Koné', 2);
COMMIT;

-- Réservation impossible : il ne reste qu'une place, le CHECK refuse de passer sous zéro
BEGIN;
UPDATE seances SET places_restantes = places_restantes - 4 WHERE id = 1;  -- erreur de contrainte
ROLLBACK;
-- Le ROLLBACK annule la transaction : places_restantes reste à 1 et aucune réservation n'est créée.
CODE,
        ],

        'Administration' => [
            'description' => 'Rôles et privilèges, migrations versionnées, sauvegardes avec pg_dump et restauration testée : exploiter PostgreSQL en conditions réelles.',
            'objective' => 'Créer des rôles à privilèges minimaux, décrire une stratégie de migration versionnée et documenter une procédure de sauvegarde et de restauration vérifiée.',
            'content' => <<<'MD'
## Pourquoi cette notion

Créer des tables, c’est le début. Une base en production doit être sécurisée, évoluer sans casser l’application et être récupérable après un incident. Beaucoup d’équipes découvrent trop tard que leur application se connecte avec le compte administrateur, que le schéma a été modifié à la main sur le serveur sans trace, ou que leur sauvegarde ne se restaure pas. Cette leçon rassemble les habitudes qui évitent ces situations.

## Les concepts clés

### Rôles et privilèges

PostgreSQL utilise un concept unique : le rôle. Un rôle avec le droit de connexion joue le rôle d’un utilisateur, un rôle sans connexion peut servir de groupe de privilèges. On crée un rôle avec CREATE ROLE, on lui accorde des droits avec GRANT et on les retire avec REVOKE. Les droits portent à plusieurs niveaux : connexion à la base, usage d’un schéma, opérations sur les tables comme SELECT, INSERT, UPDATE et DELETE, usage des séquences. Le principe du moindre privilège s’applique : l’application ne reçoit que ce qu’elle utilise, un compte de lecture sert aux rapports, un compte administrateur sert aux migrations.

### Les migrations

Une migration est un fichier de script qui fait évoluer le schéma, numéroté et appliqué dans l’ordre sur tous les environnements. Elle remplace les modifications manuelles : chaque changement est tracé, relu, rejouable et réversible quand c’est possible. Les frameworks comme Laravel ou Prisma fournissent leur système de migrations, et la règle reste la même : le schéma de production ne se modifie que par migration. Pour les opérations risquées sur de grosses tables, on procède par étapes compatibles avec l’ancien code.

### Sauvegarde et restauration

L’outil pg_dump, qui s’utilise dans un terminal, exporte une base. Le format personnalisé est compressé et permet une restauration sélective avec pg_restore, le format texte produit un script SQL rejouable avec psql. La règle 3-2-1 recommande trois copies sur deux supports dont une hors site. Surtout, une sauvegarde n’est valide que si on l’a restaurée avec succès : programme des tests de restauration sur une base séparée.

### Surveiller la santé

PostgreSQL range les anciennes versions de lignes et les nettoie par le processus VACUUM, lancé automatiquement par défaut. Surveille les connexions, la taille de la base et les requêtes lentes, dont les vues système donnent une image.

## Exemple pas à pas

Le code d’exemple crée d’abord un rôle de groupe en lecture, puis un rôle applicatif avec connexion. À l’étape 1, on retire les droits larges accordés par défaut sur le schéma public. À l’étape 2, on accorde l’usage du schéma et les droits sur les tables. À l’étape 3, on accorde l’usage des séquences, nécessaire pour les colonnes auto-incrémentées. À l’étape 4, on définit des droits par défaut pour les tables futures. Les commandes de sauvegarde et de restauration sont fournies en commentaires, car elles s’exécutent dans un terminal. Un exemple de migration versionnée termine le fichier.

## Erreurs fréquentes

- Connecter l’application avec le rôle administrateur : une injection SQL peut tout détruire. Crée un rôle applicatif limité.
- Oublier les droits sur les séquences : les INSERT sur des colonnes auto-incrémentées échouent. Accorde USAGE sur les séquences.
- Modifier le schéma à la main en production : les environnements divergent. Passe par des migrations versionnées.
- Sauvegarder sans jamais restaurer : on découvre l’échec le jour de l’incident. Teste la restauration régulièrement.
- Laisser des mots de passe dans le dépôt : ils fuitent avec le code. Utilise des variables d’environnement ou un gestionnaire de secrets.
- Ouvrir le serveur à toutes les adresses : il devient une cible. Restreins l’accès réseau et exige un chiffrement des connexions.

## Bonnes pratiques

- Un rôle par usage : application, rapports, migrations, administration.
- Revois régulièrement les droits et retire ceux qui ne servent plus.
- Versionne et relis toutes les migrations avec le code.
- Automatise les sauvegardes, garde plusieurs générations et copie-les hors du serveur.
- Documente la procédure de restauration pour qu’un autre membre de l’équipe puisse l’exécuter.

## Auto-évaluation

- Quelle est la différence entre un rôle de connexion et un rôle de groupe ?
- Pourquoi l’application ne doit-elle pas utiliser le compte administrateur ?
- Qu’est-ce qu’une migration et pourquoi est-elle préférable à une modification manuelle ?
- Quel outil exporte une base PostgreSQL et comment vérifier que l’export est valable ?
- Pourquoi faut-il accorder des droits sur les séquences ?

## À retenir

- Le moindre privilège limite les dégâts en cas de compromission.
- Le schéma évolue uniquement par migrations versionnées.
- Une sauvegarde non restaurée n’est pas une sauvegarde.
- Les secrets ne vont jamais dans le dépôt de code.
- Les droits se contrôlent et se revoient régulièrement.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : rôle de groupe en lecture seule et rôle applicatif avec connexion
CREATE ROLE lecture_boutique NOLOGIN;
CREATE ROLE app_boutique LOGIN PASSWORD 'MotDePasseFortAChanger_2026';
-- (le mot de passe réel vient d'une variable d'environnement, jamais du dépôt)

-- On retire le droit de créer des objets dans le schéma public à tout le monde
REVOKE CREATE ON SCHEMA public FROM PUBLIC;

-- Étape 2 : droits de connexion et d'usage du schéma
GRANT CONNECT ON DATABASE boutique TO app_boutique;
GRANT USAGE ON SCHEMA public TO app_boutique, lecture_boutique;

-- Droits sur les tables existantes
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_boutique;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO lecture_boutique;

-- Étape 3 : séquences, nécessaires pour les colonnes auto-incrémentées
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_boutique;

-- Étape 4 : droits par défaut pour les tables créées plus tard
ALTER DEFAULT PRIVILEGES IN SCHEMA public
    GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO app_boutique;

-- Un rapporteur reçoit les droits du groupe de lecture
CREATE ROLE comptable LOGIN PASSWORD 'AutreMotDePasseFort_2026';
GRANT lecture_boutique TO comptable;

-- Exemple de migration versionnée (fichier 0004_ajout_telephone_clients.sql)
-- ALTER TABLE clients ADD COLUMN telephone TEXT;
-- CREATE UNIQUE INDEX uq_clients_telephone ON clients (telephone);

-- Sauvegarde (terminal) : format personnalisé compressé
--   pg_dump -U admin -Fc boutique -f sauvegarde_boutique.dump
-- Restauration de test dans une base séparée (terminal) :
--   createdb -U admin boutique_test
--   pg_restore -U admin -d boutique_test sauvegarde_boutique.dump
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Plan d’administration d’une marketplace',
            'exercise_description' => <<<'TXT'
Prépare l’administration de la base « marketplace » : rôles, droits, migration et sauvegarde.

Critères de réussite :
- Trois rôles sont créés : un rôle applicatif avec connexion, un rôle de groupe de lecture sans connexion et un rôle de rapports avec connexion appartenant au groupe de lecture.
- Le rôle applicatif reçoit SELECT, INSERT, UPDATE et DELETE sur les tables du schéma public, ainsi que les droits USAGE et SELECT sur les séquences.
- Le groupe de lecture reçoit uniquement SELECT.
- Une migration versionnée ajoute une colonne « statut » avec un CHECK sur une table existante, écrite en commentaire avec son nom de fichier numéroté.
- Les commandes pg_dump et pg_restore de sauvegarde et de restauration de test sont données en commentaires.
TXT,
            'exercise_hint' => 'Un rôle de groupe se crée avec NOLOGIN. Pour qu’un rôle en hérite, accorde le groupe au rôle avec GRANT nom_du_groupe TO nom_du_role. N’oublie pas les séquences pour les colonnes BIGSERIAL.',
            'exercise_solution' => <<<'CODE'
-- Rôles : application, groupe de lecture, rapports
CREATE ROLE app_marketplace LOGIN PASSWORD 'SecretApp_ChangeMoi1';
CREATE ROLE lecture_marketplace NOLOGIN;
CREATE ROLE rapports_marketplace LOGIN PASSWORD 'SecretRapports_ChangeMoi2';

-- Accès à la base et au schéma
GRANT CONNECT ON DATABASE marketplace TO app_marketplace, rapports_marketplace;
GRANT USAGE ON SCHEMA public TO app_marketplace, lecture_marketplace;

-- Application : lecture et écriture des tables, usage des séquences
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_marketplace;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_marketplace;

-- Groupe de lecture : SELECT uniquement
GRANT SELECT ON ALL TABLES IN SCHEMA public TO lecture_marketplace;

-- Le rôle de rapports hérite des droits du groupe de lecture
GRANT lecture_marketplace TO rapports_marketplace;

-- Migration versionnée (fichier 0007_ajout_statut_annonces.sql)
-- ALTER TABLE annonces ADD COLUMN statut TEXT NOT NULL DEFAULT 'brouillon';
-- ALTER TABLE annonces ADD CONSTRAINT ck_annonces_statut
--     CHECK (statut IN ('brouillon', 'publiee', 'vendue'));

-- Sauvegarde (terminal) :
--   pg_dump -U admin -Fc marketplace -f sauvegarde_marketplace.dump
-- Restauration de test (terminal) :
--   createdb -U admin marketplace_test
--   pg_restore -U admin -d marketplace_test sauvegarde_marketplace.dump
-- La sauvegarde est copiée hors du serveur et la restauration est testée chaque mois.
CODE,
        ],

        'Projet final PostgreSQL' => [
            'description' => 'Mini-projet : concevoir le backend de données d’une marketplace avec utilisateurs, annonces, commandes, paiements et historique, en combinant contraintes, index, JSONB et transactions.',
            'objective' => 'Livrer un jeu de scripts SQL cohérent qui s’exécute sur une base vide et qui démontre modélisation, contraintes, index, JSONB, transaction de paiement et requêtes d’analyse.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une marketplace réunit des acheteurs et des vendeurs : annonces, commandes, paiements par Mobile Money, évaluations, historique. C’est un excellent terrain pour combiner tout ce que tu as appris dans PostgreSQL. Le défi n’est pas d’écrire une requête isolée, mais de concevoir un ensemble cohérent où les contraintes protègent les règles métier, où les index soutiennent les requêtes fréquentes et où les opérations sensibles, comme un paiement, sont atomiques.

## Les concepts clés

### Partir des acteurs et des actions

Liste d’abord qui fait quoi : un vendeur publie une annonce, un acheteur passe commande, le paiement est enregistré, la commande est livrée. Les noms deviennent des tables, les verbes deviennent des relations ou des changements d’état. Dessine le schéma avant d’écrire le SQL.

### Contraintes et états

Les statuts d’une commande et d’une annonce sont limités par des contraintes CHECK. Les relations sont protégées par des clés étrangères avec RESTRICT pour les données financières. Les montants utilisent NUMERIC ou des entiers en FCFA. L’unicité empêche les doublons comme deux paiements avec la même référence de transaction d’opérateur.

### Données variables avec JSONB

Les attributs d’une annonce varient selon la catégorie. Une colonne JSONB accueille ces attributs, avec un index GIN si tu filtres dessus. Les données centrales, comme le prix et le vendeur, restent dans des colonnes classiques.

### Historique et traçabilité

Une table d’historique enregistre les changements de statut avec leur date. Cela permet de reconstituer le parcours d’une commande et de prouver ce qui s’est passé en cas de litige.

### Opérations atomiques

Le paiement d’une commande met à jour la commande, crée le paiement et écrit l’historique. Ces trois écritures doivent réussir ensemble ou pas du tout : on les place dans une transaction, avec verrouillage de la commande pour éviter un double paiement.

## Exemple pas à pas

Le code d’exemple pose le squelette. À l’étape 1, on crée les tables principales avec leurs contraintes. À l’étape 2, on ajoute les index sur les clés étrangères et sur les attributs JSONB. À l’étape 3, on insère des données de test. À l’étape 4, une transaction paie la commande : elle verrouille la commande, vérifie qu’elle est en attente, crée le paiement, change le statut et ajoute la ligne d’historique. À l’étape 5, une requête d’analyse calcule le chiffre d’affaires par vendeur.

## Erreurs fréquentes

- Écrire les tables sans avoir listé les acteurs et actions : le modèle oublie des cas. Commence par les phrases métier.
- Laisser deux paiements possibles pour la même commande : un clic double crée un doublon. Verrouille la commande avec FOR UPDATE et ajoute une unicité sur la référence de transaction.
- Oublier d’indexer les clés étrangères : les jointures ralentissent avec le volume. Crée les index manuellement.
- Tout mettre en JSONB pour aller vite : plus de contraintes ni de jointures solides. Garde les données centrales en colonnes classiques.
- Ne pas copier le prix dans la ligne de commande : une modification du prix de l’annonce fausse les anciennes commandes. Enregistre le prix au moment de l’achat.
- Ne pas tester l’exécution complète sur une base vide : un ordre de création erroné casse le script. Rejoue tout depuis zéro.

## Bonnes pratiques

- Découpe les scripts en fichiers numérotés : schéma, index, données, requêtes, sécurité.
- Protège les règles métier par des contraintes plutôt que par des conventions.
- Teste chaque opération sensible dans une transaction et vérifie le résultat.
- Joins un schéma dessiné ou une liste des tables et relations au dépôt.
- Mesure les requêtes d’analyse avec EXPLAIN ANALYZE sur des données représentatives.

## Auto-évaluation

- Pourquoi enregistrer le prix unitaire dans la ligne de commande ?
- Comment éviter qu’une commande soit payée deux fois ?
- Quelles colonnes de ce projet mérite-t-on d’indexer et pourquoi ?
- Dans quel cas utiliser JSONB plutôt qu’une colonne classique ?
- Quelles écritures doivent être regroupées dans la transaction de paiement ?

## À retenir

- Un bon modèle part des acteurs et des actions du métier.
- Contraintes, index et transactions se complètent pour fiabiliser la base.
- JSONB sert aux attributs variables, pas aux données centrales.
- L’historique des statuts permet la traçabilité.
- Un livrable se vérifie en rejouant tout depuis une base vide.
MD,
            'code_example' => <<<'CODE'
-- Étape 1 : tables principales avec contraintes
CREATE TABLE utilisateurs (
    id BIGSERIAL PRIMARY KEY,
    nom TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL DEFAULT 'acheteur' CHECK (role IN ('acheteur', 'vendeur', 'admin'))
);
CREATE TABLE annonces (
    id BIGSERIAL PRIMARY KEY,
    vendeur_id BIGINT NOT NULL REFERENCES utilisateurs (id) ON DELETE RESTRICT,
    titre TEXT NOT NULL,
    prix_fcfa INTEGER NOT NULL CHECK (prix_fcfa >= 0),
    attributs JSONB NOT NULL DEFAULT '{}',
    statut TEXT NOT NULL DEFAULT 'publiee' CHECK (statut IN ('brouillon', 'publiee', 'vendue'))
);
CREATE TABLE commandes (
    id BIGSERIAL PRIMARY KEY,
    acheteur_id BIGINT NOT NULL REFERENCES utilisateurs (id) ON DELETE RESTRICT,
    annonce_id BIGINT NOT NULL REFERENCES annonces (id) ON DELETE RESTRICT,
    prix_fcfa INTEGER NOT NULL,  -- prix copié au moment de la commande
    statut TEXT NOT NULL DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'payee', 'livree', 'annulee'))
);
CREATE TABLE paiements (
    id BIGSERIAL PRIMARY KEY,
    commande_id BIGINT NOT NULL REFERENCES commandes (id) ON DELETE RESTRICT,
    montant_fcfa INTEGER NOT NULL CHECK (montant_fcfa > 0),
    operateur TEXT NOT NULL,
    reference_operateur TEXT NOT NULL UNIQUE  -- empêche deux paiements avec la même référence
);
CREATE TABLE historique_commandes (
    id BIGSERIAL PRIMARY KEY,
    commande_id BIGINT NOT NULL REFERENCES commandes (id) ON DELETE CASCADE,
    statut TEXT NOT NULL,
    change_le TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- Étape 2 : index sur les clés étrangères et sur les attributs JSONB
CREATE INDEX idx_annonces_vendeur ON annonces (vendeur_id);
CREATE INDEX idx_commandes_acheteur ON commandes (acheteur_id);
CREATE INDEX idx_paiements_commande ON paiements (commande_id);
CREATE INDEX idx_annonces_attributs ON annonces USING GIN (attributs);

-- Étape 3 : données de test
INSERT INTO utilisateurs (nom, email, role) VALUES ('Koffi', 'koffi@example.com', 'vendeur'), ('Awa', 'awa@example.com', 'acheteur');
INSERT INTO annonces (vendeur_id, titre, prix_fcfa, attributs) VALUES (1, 'Téléphone d''occasion', 45000, '{"stockage_go": 64}');
INSERT INTO commandes (acheteur_id, annonce_id, prix_fcfa) VALUES (2, 1, 45000);

-- Étape 4 : paiement atomique de la commande 1
BEGIN;
SELECT statut FROM commandes WHERE id = 1 AND statut = 'en_attente' FOR UPDATE;  -- doit renvoyer une ligne
INSERT INTO paiements (commande_id, montant_fcfa, operateur, reference_operateur) VALUES (1, 45000, 'wave', 'WV-0001');
UPDATE commandes SET statut = 'payee' WHERE id = 1;
INSERT INTO historique_commandes (commande_id, statut) VALUES (1, 'payee');
COMMIT;

-- Étape 5 : chiffre d'affaires par vendeur
SELECT u.nom, SUM(p.montant_fcfa) AS chiffre_affaires
FROM paiements p
JOIN commandes c ON c.id = p.commande_id
JOIN annonces a ON a.id = c.annonce_id
JOIN utilisateurs u ON u.id = a.vendeur_id
GROUP BY u.id, u.nom;
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Projet : backend de données d’une marketplace',
            'exercise_description' => <<<'TXT'
Conçois et livre les scripts SQL du backend d’une marketplace locale (annonces entre particuliers et petits commerçants). Reprends l’exemple de la leçon comme base, puis adapte-le avec tes propres règles métier.

Livrables :
- Un fichier 01_schema.sql qui crée au moins cinq tables : utilisateurs, annonces, commandes, paiements et historique des commandes.
- Un fichier 02_index.sql avec au moins trois index justifiés par un commentaire, dont un index GIN sur une colonne JSONB.
- Un fichier 03_donnees.sql avec un jeu de test (au moins deux vendeurs, trois annonces, deux acheteurs et trois commandes).
- Un fichier 04_paiement.sql avec la transaction de paiement atomique.
- Un fichier 05_analyse.sql avec trois requêtes d’analyse et un fichier 06_securite.sql avec deux rôles aux droits limités et les commandes pg_dump et pg_restore en commentaires.

Critères de réussite :
- L’ensemble s’exécute dans l’ordre sur une base vide sans erreur.
- Chaque table a une clé primaire, les clés étrangères sont définies, et des CHECK et un UNIQUE protègent les règles métier.
- Le prix est copié dans la commande et la référence de transaction de paiement est unique.
- La transaction de paiement verrouille la commande, crée le paiement, change le statut et écrit l’historique avant le COMMIT.
- Les rôles ne reçoivent que les droits nécessaires et aucun mot de passe réel n’apparaît dans les fichiers.
TXT,
            'exercise_hint' => 'Crée les tables parentes avant les enfants. Pour éviter un double paiement, combine FOR UPDATE sur la commande, une unicité sur la référence de transaction et un statut de départ vérifié dans la transaction.',
            'exercise_solution' => <<<'CODE'
-- ===== 01_schema.sql =====
CREATE TABLE utilisateurs (
    id BIGSERIAL PRIMARY KEY,
    nom TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    role TEXT NOT NULL DEFAULT 'acheteur' CHECK (role IN ('acheteur', 'vendeur', 'admin'))
);
CREATE TABLE annonces (
    id BIGSERIAL PRIMARY KEY,
    vendeur_id BIGINT NOT NULL REFERENCES utilisateurs (id) ON DELETE RESTRICT,
    titre TEXT NOT NULL,
    prix_fcfa INTEGER NOT NULL CHECK (prix_fcfa >= 0),
    attributs JSONB NOT NULL DEFAULT '{}',
    statut TEXT NOT NULL DEFAULT 'publiee' CHECK (statut IN ('brouillon', 'publiee', 'vendue'))
);
CREATE TABLE commandes (
    id BIGSERIAL PRIMARY KEY,
    acheteur_id BIGINT NOT NULL REFERENCES utilisateurs (id) ON DELETE RESTRICT,
    annonce_id BIGINT NOT NULL REFERENCES annonces (id) ON DELETE RESTRICT,
    prix_fcfa INTEGER NOT NULL CHECK (prix_fcfa >= 0),
    statut TEXT NOT NULL DEFAULT 'en_attente' CHECK (statut IN ('en_attente', 'payee', 'livree', 'annulee')),
    cree_le TIMESTAMPTZ NOT NULL DEFAULT now()
);
CREATE TABLE paiements (
    id BIGSERIAL PRIMARY KEY,
    commande_id BIGINT NOT NULL REFERENCES commandes (id) ON DELETE RESTRICT,
    montant_fcfa INTEGER NOT NULL CHECK (montant_fcfa > 0),
    operateur TEXT NOT NULL,
    reference_operateur TEXT NOT NULL UNIQUE
);
CREATE TABLE historique_commandes (
    id BIGSERIAL PRIMARY KEY,
    commande_id BIGINT NOT NULL REFERENCES commandes (id) ON DELETE CASCADE,
    statut TEXT NOT NULL,
    change_le TIMESTAMPTZ NOT NULL DEFAULT now()
);

-- ===== 02_index.sql =====
-- Les clés étrangères ne sont pas indexées automatiquement : on les indexe pour les jointures
CREATE INDEX idx_annonces_vendeur ON annonces (vendeur_id);
CREATE INDEX idx_commandes_acheteur ON commandes (acheteur_id);
CREATE INDEX idx_paiements_commande ON paiements (commande_id);
-- Recherche fréquente par attributs variables des annonces
CREATE INDEX idx_annonces_attributs ON annonces USING GIN (attributs);

-- ===== 03_donnees.sql =====
INSERT INTO utilisateurs (nom, email, role) VALUES
    ('Koffi', 'koffi@example.com', 'vendeur'),
    ('Marie', 'marie@example.com', 'vendeur'),
    ('Awa', 'awa@example.com', 'acheteur'),
    ('Yao', 'yao@example.com', 'acheteur');
INSERT INTO annonces (vendeur_id, titre, prix_fcfa, attributs) VALUES
    (1, 'Téléphone d''occasion', 45000, '{"stockage_go": 64}'),
    (1, 'Table en bois', 25000, '{"matiere": "bois"}'),
    (2, 'Robe wax', 15000, '{"taille": "M"}');
INSERT INTO commandes (acheteur_id, annonce_id, prix_fcfa) VALUES (3, 1, 45000), (4, 3, 15000), (3, 2, 25000);

-- ===== 04_paiement.sql =====
BEGIN;
-- Verrouille la commande et vérifie qu'elle est encore en attente (doit renvoyer une ligne)
SELECT id FROM commandes WHERE id = 1 AND statut = 'en_attente' FOR UPDATE;
INSERT INTO paiements (commande_id, montant_fcfa, operateur, reference_operateur) VALUES (1, 45000, 'wave', 'WV-0001');
UPDATE commandes SET statut = 'payee' WHERE id = 1;
INSERT INTO historique_commandes (commande_id, statut) VALUES (1, 'payee');
COMMIT;

-- ===== 05_analyse.sql =====
-- Chiffre d'affaires par vendeur
SELECT u.nom, SUM(p.montant_fcfa) AS chiffre_affaires
FROM paiements p
JOIN commandes c ON c.id = p.commande_id
JOIN annonces a ON a.id = c.annonce_id
JOIN utilisateurs u ON u.id = a.vendeur_id
GROUP BY u.id, u.nom;

-- Commandes en attente de paiement
SELECT c.id, u.nom AS acheteur, c.prix_fcfa
FROM commandes c
JOIN utilisateurs u ON u.id = c.acheteur_id
WHERE c.statut = 'en_attente';

-- Annonces avec le nombre de commandes reçues
SELECT a.titre, COUNT(c.id) AS nb_commandes
FROM annonces a
LEFT JOIN commandes c ON c.annonce_id = a.id
GROUP BY a.id, a.titre
ORDER BY nb_commandes DESC;

-- ===== 06_securite.sql =====
CREATE ROLE app_marketplace LOGIN PASSWORD 'SecretApp_ChangeMoi1';
CREATE ROLE rapports_marketplace LOGIN PASSWORD 'SecretRapports_ChangeMoi2';
GRANT USAGE ON SCHEMA public TO app_marketplace, rapports_marketplace;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO app_marketplace;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO app_marketplace;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO rapports_marketplace;
-- Sauvegarde (terminal) : pg_dump -U admin -Fc marketplace -f sauvegarde.dump
-- Restauration de test (terminal) : createdb -U admin marketplace_test && pg_restore -U admin -d marketplace_test sauvegarde.dump
CODE,
        ],
    ],
];
