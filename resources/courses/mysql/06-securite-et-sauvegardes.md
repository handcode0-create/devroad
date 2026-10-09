---
title: Sécurité et sauvegardes
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une base de données contient ce qu'une application a de plus précieux : les comptes, les mots de passe, les paiements. Une base compromise ou perdue peut tuer un projet, surtout quand on est seul à gérer les clients. Ce chapitre couvre les trois piliers d'une exploitation sérieuse de MySQL : **protéger les accès**, **empêcher les injections SQL**, et **sauvegarder pour pouvoir restaurer**.

À la fin du chapitre, tu seras capable de :

- créer des utilisateurs MySQL avec des droits limités (`CREATE USER`, `GRANT`, `REVOKE`) ;
- appliquer le principe du **moindre privilège** ;
- expliquer et empêcher une **injection SQL** avec des requêtes préparées ;
- stocker les mots de passe de façon sûre (hachage, jamais en clair) ;
- sauvegarder avec `mysqldump` et restaurer une base ;
- planifier des sauvegardes et vérifier qu'elles fonctionnent ;
- appliquer les réglages de durcissement essentiels.

Prérequis : les chapitres 1 à 5, et le conteneur MySQL 8. Prévois deux heures et demie.

## Le compte root n'est pas pour ton application

Quand tu as lancé MySQL avec Docker, tu t'es connecté avec `root`, le superutilisateur capable de tout faire, y compris supprimer toutes les bases. Beaucoup de tutoriels, et beaucoup de projets en production, laissent l'application se connecter en `root` : c'est une faute grave. Si une faille permet à un attaquant d'exécuter du SQL, il aura **tous les pouvoirs** sur le serveur.

La bonne approche est le **moindre privilège** : chaque compte reçoit uniquement les droits nécessaires à sa mission, pas un de plus.

## Utilisateurs et droits

Crée un utilisateur dédié à l'application. Il ne pourra se connecter que depuis les machines autorisées :

```sql
CREATE USER 'devroad_app'@'%' IDENTIFIED BY 'Un-Mot-De-Passe-Long-Et-Unique-42!';
```

Le `'%'` signifie « depuis n'importe quelle adresse ». En production, restreins à l'adresse du serveur d'application (`'devroad_app'@'10.0.0.5'`) ou à `'localhost'` si la base est sur la même machine. Puis accorde les droits **sur la seule base concernée** :

```sql
GRANT SELECT, INSERT, UPDATE, DELETE ON devroad.* TO 'devroad_app'@'%';
```

Cette application peut lire et écrire les données, mais elle ne peut ni créer ni supprimer de tables : si elle est compromise, l'attaquant ne pourra pas faire un `DROP TABLE`. Les migrations de schéma se font avec un **autre compte**, plus privilégié, utilisé seulement lors des déploiements.

```sql
-- compte de migration : peut modifier la structure
CREATE USER 'devroad_migrate'@'10.0.0.5' IDENTIFIED BY '...';
GRANT ALL PRIVILEGES ON devroad.* TO 'devroad_migrate'@'10.0.0.5';

-- compte de lecture seule pour les statistiques
CREATE USER 'devroad_reader'@'%' IDENTIFIED BY '...';
GRANT SELECT ON devroad.* TO 'devroad_reader'@'%';
```

Pour vérifier et retirer des droits :

```sql
SHOW GRANTS FOR 'devroad_app'@'%';
REVOKE DELETE ON devroad.* FROM 'devroad_app'@'%';
DROP USER 'devroad_reader'@'%';
```

MySQL 8 propose aussi les **rôles** (`CREATE ROLE`), qui regroupent des droits et s'attribuent à plusieurs comptes d'un coup, pratique quand l'équipe grandit.

> **Attention** : ne donne jamais `GRANT ALL` sur « toutes les bases, toutes les tables » à un compte d'application. Ce niveau global donne accès à toutes les bases du serveur, y compris celle qui gère les utilisateurs.

:::quiz
Quel droit minimal faut-il donner au compte utilisé par une application web qui ne fait que lire et écrire des lignes ?
- [ ] ALL PRIVILEGES sur toutes les bases
- [ ] Le compte root, c'est plus simple
- [x] SELECT, INSERT, UPDATE, DELETE sur la seule base de l'application
- [ ] Uniquement DROP et CREATE
> Le principe du moindre privilège limite les dégâts en cas de compromission : pas de DROP, pas d'accès aux autres bases.
:::

## L'injection SQL

L'**injection SQL** est l'une des attaques les plus anciennes et les plus dévastatrices. Elle se produit quand ton code **construit une requête en collant du texte venant de l'utilisateur**. Voici un formulaire de connexion mal écrit en PHP :

```php
$email = $_POST['email'];
$sql = "SELECT * FROM users WHERE email = '$email'";
```

Si un attaquant saisit comme e-mail `' OR '1'='1`, la requête devient :

```sql
SELECT * FROM users WHERE email = '' OR '1'='1';
```

La condition est toujours vraie : toutes les lignes sont renvoyées. Avec d'autres saisies, l'attaquant peut lire des tables entières, modifier des données ou en supprimer. Le problème : la donnée saisie est **interprétée comme du code**.

### La solution : les requêtes préparées

Une **requête préparée** (*prepared statement*) sépare le code SQL de ses valeurs. Le serveur reçoit d'abord la structure avec des emplacements (`?`), puis les valeurs à part : elles ne peuvent plus jamais être interprétées comme du SQL.

```php
$stmt = $pdo->prepare('SELECT id, name FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();
```

Même avec la saisie malveillante, MySQL cherche littéralement un utilisateur dont l'e-mail est la chaîne `' OR '1'='1`, et n'en trouve aucun. Tu peux voir le mécanisme directement en SQL :

```sql
PREPARE stmt FROM 'SELECT id, name FROM users WHERE email = ?';
SET @e = 'awa@mail.ci';
EXECUTE stmt USING @e;
DEALLOCATE PREPARE stmt;
```

Dans Laravel, Eloquent et le query builder utilisent des requêtes préparées automatiquement : `User::where('email', $email)->first()` est sûr. Le danger revient dès que tu écris `DB::select("... $variable ...")` ou `whereRaw` avec une variable collée dans le texte. Utilise alors les paramètres liés : `whereRaw('email = ?', [$email])`.

> **Erreur fréquente** : croire que « nettoyer » les apostrophes suffit. Les filtres maison se contournent. Seules les requêtes préparées sont une protection fiable.

## Stocker les mots de passe

Ne stocke **jamais** un mot de passe en clair dans la table `users`. Si la base fuite, tous les comptes sont compromis, et comme les gens réutilisent leurs mots de passe, d'autres services aussi. On stocke une **empreinte** (*hash*) calculée avec un algorithme lent et salé, conçu pour ça : `bcrypt` ou `argon2id`.

```sql
ALTER TABLE users ADD COLUMN password_hash VARCHAR(255) NOT NULL;
```

Le hachage se fait dans l'application (en Laravel, `Hash::make($motDePasse)`), pas en SQL, et on compare avec `Hash::check`. Évite absolument `MD5` et `SHA1`, trop rapides donc cassables par force brute. Prévois une colonne assez large : 255 caractères laissent de la marge pour les futurs algorithmes.

D'autres bonnes habitudes de protection des données : ne stocke pas ce dont tu n'as pas besoin (surtout les numéros de carte ; les paiements Mobile Money et les passerelles gèrent cela pour toi), chiffre les données très sensibles, et limite ce qu'un compte lit avec des vues SQL.

## Durcir le serveur

Quelques réglages réduisent fortement la surface d'attaque :

- **ne pas exposer le port 3306 sur Internet.** Place la base dans un réseau privé ou derrière un tunnel SSH. Avec Docker, évite de publier le port en production ;
- **supprimer les comptes anonymes et la base de test** (la commande `mysql_secure_installation` le fait sur une installation classique) ;
- **utiliser TLS** pour chiffrer la connexion entre l'application et la base, surtout si elles sont sur deux machines ;
- **mettre à jour** MySQL régulièrement pour corriger les failles connues ;
- **activer les journaux** (`general_log` ponctuellement, `slow_query_log` en continu) pour détecter les comportements anormaux ;
- **garder les secrets hors du code** : le mot de passe de la base va dans un fichier `.env`, jamais dans le dépôt Git.

## Sauvegarder avec mysqldump

Une base finit toujours par avoir un problème : suppression par erreur, disque défaillant, migration ratée, ransomware. La seule vraie parade est une **sauvegarde** testée. L'outil standard est `mysqldump`, qui produit un fichier SQL rejouable.

```bash
# Sauvegarde complète d'une base, cohérente, sans verrou pour InnoDB
mysqldump -u devroad_migrate -p --single-transaction --routines --triggers \
  --default-character-set=utf8mb4 devroad > devroad-$(date +%F).sql

# Compressée
mysqldump -u devroad_migrate -p --single-transaction devroad | gzip > devroad-$(date +%F).sql.gz
```

L'option `--single-transaction` prend un instantané cohérent des tables InnoDB **sans bloquer** les écritures. Sans elle, tu risques une sauvegarde incohérente ou un site figé.

Pour restaurer :

```bash
# Créer la base vide puis y injecter la sauvegarde
mysql -u root -p -e "CREATE DATABASE devroad_restore CHARACTER SET utf8mb4;"
mysql -u root -p devroad_restore < devroad-2026-10-09.sql

# Depuis un fichier compressé
gunzip < devroad-2026-10-09.sql.gz | mysql -u root -p devroad_restore
```

Avec Docker, passe par `docker exec` : `docker exec mysql-devroad mysqldump -uroot -psecret devroad > sauvegarde.sql`.

## Une stratégie de sauvegarde sérieuse

Une sauvegarde n'a de valeur que si elle respecte la règle **3-2-1** : trois copies de tes données, sur deux supports différents, dont une hors site. Un fichier sur le même serveur que la base disparaît avec lui.

- **Automatise** : une tâche planifiée (`cron`) lance la sauvegarde chaque nuit. Une sauvegarde manuelle est une sauvegarde oubliée.
- **Externalise** : envoie le fichier vers un stockage distant (un espace S3 compatible, Google Drive via un script, un autre serveur).
- **Conserve plusieurs générations** : garde par exemple 7 sauvegardes quotidiennes et 4 hebdomadaires, car une erreur peut mettre plusieurs jours à être détectée.
- **Chiffre** les fichiers qui partent hors du serveur.
- **Teste la restauration.** Une sauvegarde jamais restaurée est une hypothèse, pas une garantie.

Exemple de ligne `cron` qui lance une sauvegarde à 2 h chaque nuit et supprime celles de plus de 14 jours :

```text
0 2 * * * /usr/local/bin/backup-devroad.sh && find /backups -name "devroad-*.sql.gz" -mtime +14 -delete
```

Pour de gros volumes, `mysqldump` devient lent. On passe alors aux sauvegardes physiques (Percona XtraBackup, MySQL Enterprise Backup) et à la **réplication** avec **binlogs**, qui permettent la récupération à un instant précis (*point-in-time recovery*). C'est une étape suivante, mais retiens son existence.

:::quiz
Quelle option de mysqldump garantit une sauvegarde cohérente d'une base InnoDB sans bloquer les écritures ?
- [ ] --lock-all-tables
- [x] --single-transaction
- [ ] --no-data
- [ ] --skip-triggers
> --single-transaction ouvre une transaction et lit un instantané cohérent grâce à l'isolation d'InnoDB, sans verrouiller les tables.
:::

## Atelier guidé : sécuriser DevRoad

Compte une heure et demie.

1. Connecte-toi en `root` et crée l'utilisateur `devroad_app` avec uniquement `SELECT, INSERT, UPDATE, DELETE` sur la base `devroad`.
2. Vérifie ses droits avec `SHOW GRANTS`, connecte-toi avec ce compte et essaie un `DROP TABLE roadmaps;`. Note l'erreur de refus.
3. Crée un compte `devroad_reader` en lecture seule et vérifie qu'il ne peut pas faire d'`INSERT`.
4. Ajoute la colonne `password_hash` à `users`. Génère un hash bcrypt avec une commande PHP puis insère-le :

```bash
php -r "echo password_hash('MonMotDePasse', PASSWORD_BCRYPT), PHP_EOL;"
```

5. Écris un petit script PHP avec PDO qui cherche un utilisateur par e-mail avec une requête préparée. Teste-le avec l'entrée malveillante `' OR '1'='1` et constate qu'il ne renvoie rien.
6. Réalise une sauvegarde compressée de la base avec `mysqldump --single-transaction`.
7. Supprime volontairement la table `roadmap_steps`, puis restaure la base à partir de ta sauvegarde dans une nouvelle base `devroad_restore` et compare le nombre de lignes avec `COUNT`.
8. Écris un script `backup-devroad.sh` qui sauvegarde, compresse, nomme le fichier avec la date et supprime les fichiers de plus de 14 jours. Planifie-le avec `cron`.

Pour t'auto-évaluer : explique à voix haute pourquoi `root` n'est pas adapté à l'application, comment une requête préparée neutralise l'injection, et ce qui prouve qu'une sauvegarde est bonne.

## Erreurs fréquentes

- **Connecter l'application avec `root`.** Une seule faille donne tous les pouvoirs.
- **Concaténer des variables dans une requête SQL.** C'est la porte ouverte à l'injection.
- **Stocker les mots de passe en clair ou avec `MD5`.** Une fuite devient une catastrophe.
- **Exposer le port 3306 sur Internet** avec un mot de passe faible.
- **Commiter le fichier `.env` dans Git.** Les secrets deviennent publics.
- **Ne sauvegarder que sur le serveur de la base.** Un incident disque emporte tout.
- **Ne jamais tester la restauration.** On découvre que la sauvegarde est vide le jour du sinistre.
- **Oublier `--single-transaction`.** La sauvegarde peut être incohérente.

## Bonnes pratiques

- Un compte par usage : application, migrations, lecture seule, sauvegarde.
- Mots de passe longs, uniques, gérés par un gestionnaire de mots de passe et stockés hors du code.
- Toujours des requêtes préparées ; relis spécifiquement tout usage de SQL brut.
- Hash `bcrypt` ou `argon2id` pour les mots de passe, jamais de chiffrement réversible.
- Sauvegarde automatique, externalisée, chiffrée, conservée plusieurs semaines, avec un test de restauration mensuel.
- Documente la procédure de restauration : on ne la découvre pas dans l'urgence.

## À retenir

- Applique le **moindre privilège** : un compte dédié par usage, jamais `root` dans l'application.
- L'**injection SQL** vient de la concaténation de texte utilisateur ; les **requêtes préparées** la neutralisent.
- Les mots de passe sont hachés (bcrypt, argon2id), jamais stockés en clair.
- Ne laisse pas le port de la base ouvert sur Internet ; chiffre les connexions et garde les secrets hors du dépôt.
- `mysqldump --single-transaction` sauvegarde une base InnoDB de façon cohérente ; applique la règle **3-2-1**.
- Une sauvegarde n'existe vraiment que si tu as **testé sa restauration**.
