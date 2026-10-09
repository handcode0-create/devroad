---
title: Administration
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Écrire des requêtes ne suffit pas : une base en production doit être **sécurisée, sauvegardée, surveillée et mise à jour**. C'est le métier d'administrateur de base de données (DBA), que tout développeur indépendant finit par endosser, surtout quand il livre seul les applications de ses clients. Ce chapitre te donne le socle d'une exploitation sérieuse de PostgreSQL 16.

À la fin du chapitre, tu seras capable de :

- gérer les **rôles** et les droits avec `CREATE ROLE`, `GRANT`, `REVOKE` ;
- appliquer le principe du **moindre privilège** à une application ;
- comprendre `pg_hba.conf` et les connexions sécurisées ;
- protéger l'accès aux lignes avec la **Row Level Security** (RLS) ;
- **sauvegarder** avec `pg_dump` et restaurer avec `pg_restore` ou `psql` ;
- connaître les sauvegardes continues (WAL, point-in-time recovery) et la réplication ;
- surveiller une instance avec les vues de statistiques (préfixe `pg_stat_`) ;
- empêcher les injections SQL avec des requêtes paramétrées.

Prérequis : les chapitres 1 à 5 et le conteneur PostgreSQL du premier chapitre. Prévois deux heures et demie.

## Les rôles : utilisateurs et groupes

PostgreSQL ne distingue pas utilisateurs et groupes : tout est un **rôle**. Un rôle qui peut se connecter (`LOGIN`) joue le rôle d'un utilisateur ; un rôle sans `LOGIN` sert de groupe auquel on attribue des droits. Le superutilisateur `postgres` peut tout faire : **ton application ne doit jamais l'utiliser.**

```sql
\du                      -- liste les rôles (dans psql)

CREATE ROLE devroad_app LOGIN PASSWORD 'Un-Mot-De-Passe-Long-Et-Unique-42!';
CREATE ROLE devroad_readonly LOGIN PASSWORD '...';
CREATE ROLE devroad_owner LOGIN PASSWORD '...' CREATEDB;
```

Chaque rôle peut ensuite recevoir des droits sur les objets avec `GRANT`. Les droits se posent à plusieurs niveaux : la base (`CONNECT`), le schéma (`USAGE`), les tables (`SELECT`, `INSERT`, `UPDATE`, `DELETE`) et les séquences.

```sql
GRANT CONNECT ON DATABASE devroad TO devroad_app;
GRANT USAGE ON SCHEMA public TO devroad_app;
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO devroad_app;
```

Les tables créées plus tard n'héritent pas de ces droits. Pour qu'elles les aient automatiquement :

```sql
ALTER DEFAULT PRIVILEGES IN SCHEMA public
  GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO devroad_app;
```

Pour un compte en lecture seule (statistiques, outil de BI) :

```sql
GRANT USAGE ON SCHEMA public TO devroad_readonly;
GRANT SELECT ON ALL TABLES IN SCHEMA public TO devroad_readonly;
```

Retirer ou vérifier des droits :

```sql
REVOKE DELETE ON roadmaps FROM devroad_app;
\dp roadmaps             -- affiche les droits de la table
```

## Le moindre privilège

Le **moindre privilège** consiste à ne donner à chaque compte que ce dont il a besoin. Une organisation robuste sépare trois rôles :

| Rôle | Droits | Utilisé par |
| --- | --- | --- |
| `devroad_owner` | propriétaire des objets, peut modifier le schéma | migrations, déploiements |
| `devroad_app` | lecture et écriture des données, aucun DDL | l'application en production |
| `devroad_readonly` | lecture seule | analyses, tableaux de bord |

Ainsi, si une faille compromet l'application, l'attaquant ne peut pas faire un `DROP TABLE`. Sur PostgreSQL 15 et plus, les utilisateurs ordinaires ne peuvent plus créer d'objets dans le schéma `public` par défaut, ce qui renforce cette séparation. Tu peux aussi regrouper des droits dans un rôle sans `LOGIN` puis l'attribuer : `CREATE ROLE lecteurs; GRANT lecteurs TO devroad_readonly;`.

> **Attention** : ne donne jamais `SUPERUSER` à un compte d'application, et ne l'expose jamais à Internet. Un superutilisateur peut même exécuter des commandes sur le serveur.

:::quiz
Pourquoi l'application web ne doit-elle pas se connecter avec le rôle postgres ?
- [ ] Parce que postgres est plus lent
- [ ] Parce que postgres ne peut pas lire les tables
- [x] Parce qu'une faille donnerait à l'attaquant tous les pouvoirs sur le serveur
- [ ] Parce que postgres n'a pas de mot de passe
> Le principe du moindre privilège limite les dégâts : un compte dédié sans droit de modifier le schéma ne peut pas supprimer de tables.
:::

## Les connexions : pg_hba.conf et TLS

Qui peut se connecter, depuis où, et comment ? Le fichier **`pg_hba.conf`** (*host-based authentication*) l'indique. Chaque ligne décrit une règle : type de connexion, base, rôle, adresse et méthode d'authentification.

```text
# TYPE   DATABASE  USER           ADDRESS         METHOD
local    all       postgres                       peer
host     devroad   devroad_app    10.0.0.0/24     scram-sha-256
hostssl  devroad   devroad_app    0.0.0.0/0       scram-sha-256
```

Retiens trois principes. Utilise la méthode `scram-sha-256`, la plus sûre pour les mots de passe (évite `md5` et surtout `trust`, qui autorise sans mot de passe). Restreins les **adresses** autorisées au strict nécessaire. Et force le **chiffrement TLS** avec `hostssl` quand la base est sur une autre machine que l'application. Le paramètre `listen_addresses` de `postgresql.conf` contrôle les interfaces réseau sur lesquelles le serveur écoute.

N'expose jamais le port 5432 directement sur Internet. Place la base dans un réseau privé, derrière un pare-feu, ou utilise un tunnel SSH ou un VPN. Les offres gérées (Supabase, Neon, Render) imposent déjà TLS et fournissent une chaîne de connexion à protéger comme un secret.

## La Row Level Security

Parfois, la protection par table ne suffit pas : dans une application multi-utilisateurs, chaque personne ne doit voir que **ses propres lignes**. La **Row Level Security** (RLS) de PostgreSQL applique cette règle dans la base, quelle que soit la requête envoyée. C'est le mécanisme sur lequel s'appuie Supabase.

```sql
ALTER TABLE roadmaps ENABLE ROW LEVEL SECURITY;

CREATE POLICY roadmaps_owner_policy ON roadmaps
  USING (user_id = current_setting('app.current_user_id')::bigint);
```

Une fois la RLS activée, un rôle non propriétaire ne voit que les lignes pour lesquelles la politique est vraie. L'application indique l'utilisateur courant au début de sa session :

```sql
SET app.current_user_id = '1';
SELECT title FROM roadmaps;     -- ne renvoie que les roadmaps de l'utilisateur 1
```

On peut écrire des politiques distinctes selon l'opération (`FOR SELECT`, `FOR INSERT`...), et ajouter `WITH CHECK` pour contrôler ce qui peut être écrit. Teste toujours depuis le rôle applicatif : le propriétaire de la table et les superutilisateurs contournent la RLS par défaut.

## Se protéger de l'injection SQL

Une **injection SQL** survient quand du texte venant de l'utilisateur est collé dans une requête. Si ton code construit `"SELECT ... WHERE email = '" + email + "'"` et que l'utilisateur saisit `' OR '1'='1`, la condition devient toujours vraie et toute la table fuit. La parade absolue : les **requêtes paramétrées**, qui envoient la structure et les valeurs séparément.

```sql
PREPARE trouver_user (text) AS
  SELECT id, name FROM users WHERE email = $1;

EXECUTE trouver_user ('awa@mail.ci');
```

Dans le code, tous les pilotes proposent cette fonction. En Node.js avec `pg` : `client.query('SELECT id FROM users WHERE email = $1', [email])`. Prisma, Eloquent, Drizzle et Supabase le font pour toi, sauf quand tu écris du SQL brut avec des variables collées dans le texte : relis ces endroits avec attention. Ne stocke jamais les mots de passe en clair : hache-les avec `bcrypt` ou `argon2id` côté application, ou utilise l'extension `pgcrypto` si tu dois le faire en SQL.

## Sauvegarder avec pg_dump

Une panne, une erreur humaine ou un ransomware surviendront un jour. Seule une **sauvegarde testée** te sauve. L'outil logique est `pg_dump`, qui extrait une base entière.

```bash
# Format SQL texte, lisible et rejouable avec psql
pg_dump -U postgres -d devroad -f devroad.sql

# Format "custom" compressé : le meilleur choix, restauration sélective possible
pg_dump -U postgres -d devroad -Fc -f devroad-$(date +%F).dump

# Avec Docker
docker exec pg-devroad pg_dump -U postgres -Fc devroad > devroad-$(date +%F).dump
```

`pg_dump` prend un instantané cohérent grâce à MVCC, **sans bloquer** les écritures. Pour tout le cluster, y compris les rôles, utilise `pg_dumpall --globals-only` pour exporter les rôles à part.

La restauration dépend du format :

```bash
# Dump texte : on le rejoue avec psql
psql -U postgres -d devroad_restore -f devroad.sql

# Dump custom : on utilise pg_restore
createdb -U postgres devroad_restore
pg_restore -U postgres -d devroad_restore --no-owner --clean --if-exists devroad-2026-10-09.dump

# Avec parallélisme sur une grosse base
pg_restore -U postgres -d devroad_restore -j 4 devroad-2026-10-09.dump
```

## Une stratégie de sauvegarde complète

Applique la règle **3-2-1** : trois copies, sur deux supports différents, dont une hors site. Et surtout :

- **automatise** avec `cron` ou le planificateur de ton hébergeur ;
- **externalise** vers un stockage distant (compatible S3, serveur distinct) ;
- **conserve plusieurs générations** (quotidiennes, hebdomadaires, mensuelles) ;
- **chiffre** les fichiers qui quittent le serveur ;
- **teste la restauration** régulièrement, car une sauvegarde jamais restaurée n'est qu'une hypothèse.

Pour les bases importantes, `pg_dump` ne suffit pas : on le complète par l'**archivage continu des journaux WAL** (*Write-Ahead Log*) et une sauvegarde de base (`pg_basebackup`). Cela permet le **point-in-time recovery** : restaurer la base à l'instant précis d'avant l'erreur, par exemple 14 h 32 avant un `DELETE` malheureux. Des outils comme pgBackRest ou WAL-G automatisent le tout. Les offres gérées proposent cette fonction en un clic.

La **réplication** (un serveur secondaire qui suit le principal en continu) apporte la haute disponibilité et permet de répartir les lectures. Attention, une réplique n'est pas une sauvegarde : une suppression accidentelle se réplique immédiatement.

:::quiz
Quel outil restaure un fichier produit par pg_dump -Fc ?
- [ ] psql -f
- [ ] mysqldump
- [x] pg_restore
- [ ] pg_basebackup
> Le format custom (-Fc) est binaire et compressé : il se restaure avec pg_restore. Les dumps au format SQL texte se rejouent avec psql.
:::

## Surveiller l'instance

PostgreSQL expose son état dans des vues système dont le nom commence par `pg_stat_`. Quelques requêtes à garder sous la main :

```sql
-- Connexions en cours et ce qu'elles font
SELECT pid, usename, state, now() - query_start AS duree, left(query, 60) AS requete
FROM pg_stat_activity
WHERE state <> 'idle'
ORDER BY duree DESC;

-- Taille des bases et des tables
SELECT pg_size_pretty(pg_database_size('devroad'));
SELECT relname, pg_size_pretty(pg_total_relation_size(relid)) AS taille
FROM pg_stat_user_tables ORDER BY pg_total_relation_size(relid) DESC LIMIT 5;

-- Index jamais utilisés (candidats à la suppression)
SELECT relname, indexrelname, idx_scan
FROM pg_stat_user_indexes WHERE idx_scan = 0;

-- Arrêter une requête qui bloque
SELECT pg_cancel_backend(12345);
```

Active aussi l'extension **`pg_stat_statements`**, qui classe les requêtes par temps cumulé : c'est le meilleur outil pour trouver les vraies causes de lenteur. Et règle `log_min_duration_statement = 500` pour consigner les requêtes de plus de 500 ms dans les journaux.

## Maintenance et mises à jour

- **Autovacuum** : laisse-le actif ; surveille les lignes mortes (`n_dead_tup`) ;
- **Mises à jour mineures** (16.3 vers 16.4) : fréquentes, simples, à appliquer rapidement car elles corrigent des failles ;
- **Mises à jour majeures** (15 vers 16) : changent le format de stockage, passent par `pg_upgrade` ou une migration dump/restauration, à préparer et tester sur une copie ;
- **Connexions** : PostgreSQL crée un processus par connexion, donc évite des centaines de connexions directes. Utilise un *pooler* comme PgBouncer, ou celui fourni par Supabase ;
- **Configuration** : ajuste `shared_buffers` (environ 25 % de la mémoire), `work_mem` et `max_connections` en fonction de ta machine.

## Atelier guidé : exploiter DevRoad en sécurité

Compte une heure et demie.

1. Connecte-toi en `postgres` et crée les rôles `devroad_app` et `devroad_readonly` avec mots de passe forts.
2. Accorde à `devroad_app` les droits `CONNECT`, `USAGE` et `SELECT, INSERT, UPDATE, DELETE` sur les tables du schéma, plus `USAGE` sur les séquences, puis configure `ALTER DEFAULT PRIVILEGES`.
3. Reconnecte-toi avec `devroad_app` (`\c devroad devroad_app`) et essaie un `DROP TABLE roadmaps;`. Note l'erreur.
4. Vérifie que `devroad_readonly` ne peut pas faire d'`INSERT`.
5. Active la RLS sur `roadmaps` avec une politique par propriétaire, puis teste avec `SET app.current_user_id` depuis `devroad_app`.
6. Écris un `PREPARE` / `EXECUTE` pour chercher un utilisateur par e-mail et teste l'entrée `' OR '1'='1`.
7. Fais un `pg_dump -Fc` de la base.
8. Supprime volontairement une table, crée une base `devroad_restore` et restaure-y la sauvegarde avec `pg_restore`. Compare les nombres de lignes.
9. Écris un script `backup.sh` qui sauvegarde, horodate et supprime les fichiers de plus de 14 jours, puis planifie-le avec `cron`.
10. Consulte `pg_stat_activity` et `pg_stat_user_tables`, puis cherche les index inutilisés.

Pour t'auto-évaluer : explique la différence entre `pg_dump` et le point-in-time recovery, et pourquoi une réplique n'est pas une sauvegarde.

## Erreurs fréquentes

- **Connecter l'application avec `postgres`.** Une faille compromet tout.
- **Utiliser la méthode `trust` dans `pg_hba.conf`** ou ouvrir le port 5432 à tout le monde.
- **Concaténer des variables dans le SQL.** C'est la porte ouverte à l'injection.
- **Stocker les mots de passe en clair** ou avec `MD5`.
- **Oublier les droits sur les séquences.** Les `INSERT` échouent pour la colonne identité.
- **Compter sur la réplication comme sauvegarde.** Les erreurs se répliquent.
- **Ne jamais tester la restauration.** Le jour du sinistre, la sauvegarde est inutilisable.
- **Se fier à la RLS en testant avec le propriétaire de la table**, qui la contourne.

## Bonnes pratiques

- Trois rôles distincts : propriétaire/migrations, application, lecture seule.
- Authentification `scram-sha-256`, TLS obligatoire hors machine locale, secrets hors du dépôt Git.
- Requêtes paramétrées partout ; hash `bcrypt` ou `argon2id` pour les mots de passe.
- Sauvegardes automatisées, chiffrées, externalisées, avec un test de restauration mensuel.
- Surveille `pg_stat_statements`, `pg_stat_activity` et la taille des tables.
- Documente la procédure de restauration avant d'en avoir besoin.

## À retenir

- Tout est rôle dans PostgreSQL ; applique le **moindre privilège** avec `GRANT` et `ALTER DEFAULT PRIVILEGES`.
- `pg_hba.conf` décide qui se connecte d'où ; privilégie `scram-sha-256` et TLS.
- La **RLS** limite les lignes visibles par rôle ou par utilisateur applicatif.
- Les **requêtes paramétrées** neutralisent l'injection SQL.
- `pg_dump -Fc` sauvegarde, `pg_restore` restaure ; WAL et PITR permettent de revenir à un instant précis.
- Une sauvegarde n'existe que si la **restauration a été testée** ; une réplique n'est pas une sauvegarde.
