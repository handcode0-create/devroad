---
title: Schéma et contraintes
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, tu as utilisé des tables préparées pour toi. Dans un vrai projet, c'est à toi de **concevoir le schéma** : choisir les tables, les types, et surtout les **contraintes** qui empêchent les données invalides d'entrer. Une bonne contrainte vaut mieux que cent vérifications dans le code : elle protège la base, même si une autre application, un script ou un collègue y écrit.

À la fin du chapitre, tu seras capable de :

- créer et modifier des tables avec `CREATE TABLE` et `ALTER TABLE` ;
- poser les contraintes `NOT NULL`, `UNIQUE`, `DEFAULT`, `CHECK` ;
- déclarer des clés étrangères et choisir `ON DELETE` (`CASCADE`, `RESTRICT`, `SET NULL`) ;
- normaliser un schéma (première, deuxième, troisième forme normale) ;
- modéliser une relation plusieurs-à-plusieurs avec une table pivot ;
- faire évoluer un schéma avec des migrations de façon sûre.

Prérequis : les chapitres 1 à 3. Prévois deux heures et demie. Le conteneur MySQL 8 des chapitres précédents suffit.

## CREATE TABLE : décrire une table

La commande `CREATE TABLE` définit les colonnes, leurs types et leurs règles. Voici une version complète de `roadmaps`, que nous allons commenter :

```sql
CREATE TABLE roadmaps (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  user_id BIGINT UNSIGNED NOT NULL,
  title VARCHAR(150) NOT NULL,
  slug VARCHAR(160) NOT NULL,
  level ENUM('beginner', 'intermediate', 'professional') NOT NULL DEFAULT 'beginner',
  is_published BOOLEAN NOT NULL DEFAULT FALSE,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_roadmaps_slug (slug),
  CONSTRAINT fk_roadmaps_user FOREIGN KEY (user_id) REFERENCES users (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

Quelques éléments à noter. `ENUM` limite la colonne à une liste de valeurs : toute autre valeur est refusée. `updated_at` se met à jour automatiquement à chaque modification grâce à `ON UPDATE CURRENT_TIMESTAMP`. Les contraintes sont **nommées** (`uq_roadmaps_slug`, `fk_roadmaps_user`) : en cas d'erreur, le message cite ce nom, et tu peux la supprimer facilement plus tard.

> **Astuce** : `SHOW CREATE TABLE roadmaps;` affiche l'instruction exacte qui recrée la table. C'est le meilleur moyen de relire un schéma existant.

## Les contraintes de colonne

Une contrainte est une règle que MySQL vérifie à chaque `INSERT` et `UPDATE`. Voici les principales.

| Contrainte | Garantit |
| --- | --- |
| `NOT NULL` | la colonne ne peut pas être vide |
| `DEFAULT valeur` | une valeur est fournie si on n'en donne pas |
| `UNIQUE` | aucune valeur en double dans la colonne |
| `PRIMARY KEY` | unique, non nulle, identifie la ligne |
| `CHECK (condition)` | la condition est vraie pour chaque ligne |
| `FOREIGN KEY` | la valeur existe dans la table référencée |

### NOT NULL et DEFAULT

Pose `NOT NULL` sur **tout ce qui est obligatoire**. Un `NULL` introduit de l'incertitude dans les requêtes (tu l'as vu au chapitre 2). Réserve-le aux informations vraiment optionnelles, comme `minutes` pour une étape dont la durée est inconnue.

### UNIQUE

`UNIQUE` empêche les doublons : deux utilisateurs avec le même e-mail, deux roadmaps avec le même `slug`. On peut aussi l'appliquer à un **groupe de colonnes**. Par exemple, dans une roadmap, deux étapes ne doivent pas avoir la même position :

```sql
ALTER TABLE roadmap_steps
  ADD CONSTRAINT uq_steps_position UNIQUE (roadmap_id, position);
```

Cette contrainte autorise la position 1 dans la roadmap 1 et aussi dans la roadmap 2, mais pas deux fois dans la même.

### CHECK

Depuis MySQL 8.0.16, `CHECK` est réellement appliqué. Il valide une règle métier :

```sql
ALTER TABLE roadmap_steps
  ADD CONSTRAINT chk_steps_minutes CHECK (minutes IS NULL OR minutes > 0),
  ADD CONSTRAINT chk_steps_position CHECK (position >= 1);
```

Désormais, insérer une étape de -30 minutes déclenche l'erreur 3819 (*Check constraint is violated*).

:::quiz
Quelle contrainte empêche deux utilisateurs d'avoir la même adresse e-mail ?
- [ ] NOT NULL
- [ ] DEFAULT
- [x] UNIQUE
- [ ] CHECK sur la longueur
> UNIQUE interdit les valeurs en double dans une colonne. NOT NULL interdit seulement l'absence de valeur.
:::

## Les clés étrangères et ON DELETE

Une clé étrangère garantit qu'une roadmap pointe vers un utilisateur **qui existe**. Mais que se passe-t-il quand on supprime cet utilisateur ? C'est la clause `ON DELETE` qui décide :

```sql
ALTER TABLE roadmap_steps
  ADD CONSTRAINT fk_steps_roadmap
  FOREIGN KEY (roadmap_id) REFERENCES roadmaps (id)
  ON DELETE CASCADE;
```

Les options principales :

| Option | Effet quand on supprime la ligne parente |
| --- | --- |
| `RESTRICT` (par défaut) | la suppression est refusée s'il reste des lignes enfants |
| `CASCADE` | les lignes enfants sont supprimées automatiquement |
| `SET NULL` | la clé étrangère des enfants devient `NULL` (colonne nullable obligatoire) |

Le bon choix dépend du sens métier. Les étapes n'existent que dans une roadmap : `CASCADE` est logique, supprimer la roadmap supprime ses étapes. À l'inverse, supprimer un utilisateur qui a des roadmaps publiées est dangereux : garde `RESTRICT`, ou mieux, utilise une suppression logique (une colonne `deleted_at`). Quant à `SET NULL`, il convient quand la relation est facultative, par exemple l'auteur d'un commentaire que l'on garde même si le compte disparaît.

> **Attention** : `CASCADE` est puissant et silencieux. Une suppression qui paraît anodine peut effacer des milliers de lignes en chaîne. Réserve-le aux vraies relations de composition.

## La table pivot : plusieurs-à-plusieurs

Reprenons l'exemple du chapitre 1 : un utilisateur peut suivre plusieurs roadmaps. On crée la table de liaison `roadmap_follows` avec une clé primaire composée :

```sql
CREATE TABLE roadmap_follows (
  user_id BIGINT UNSIGNED NOT NULL,
  roadmap_id BIGINT UNSIGNED NOT NULL,
  followed_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (user_id, roadmap_id),
  CONSTRAINT fk_follows_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE,
  CONSTRAINT fk_follows_roadmap FOREIGN KEY (roadmap_id) REFERENCES roadmaps (id) ON DELETE CASCADE
) ENGINE=InnoDB;
```

La clé primaire `(user_id, roadmap_id)` fait deux choses d'un coup : elle identifie la ligne et interdit qu'un utilisateur suive deux fois la même roadmap. Les deux `CASCADE` nettoient la table quand un utilisateur ou une roadmap disparaît.

## ALTER TABLE : faire évoluer un schéma

Un schéma n'est jamais figé. `ALTER TABLE` ajoute, modifie ou supprime des éléments sur une table qui contient déjà des données :

```sql
-- ajouter une colonne
ALTER TABLE users ADD COLUMN avatar_url VARCHAR(255) NULL AFTER email;

-- modifier un type
ALTER TABLE roadmaps MODIFY title VARCHAR(200) NOT NULL;

-- renommer une colonne
ALTER TABLE roadmap_steps RENAME COLUMN done TO is_done;

-- supprimer une colonne ou une contrainte
ALTER TABLE users DROP COLUMN avatar_url;
ALTER TABLE roadmap_steps DROP CONSTRAINT chk_steps_position;
```

Quand tu ajoutes une colonne `NOT NULL` à une table qui a déjà des lignes, fournis un `DEFAULT`, sinon MySQL ne sait pas quoi mettre dans les lignes existantes. Sur une grosse table en production, certains `ALTER` bloquent les écritures pendant plusieurs minutes. MySQL 8 sait en faire beaucoup « en ligne » (`ALGORITHM=INPLACE` ou `INSTANT`), mais teste toujours sur une copie.

Dans un projet Laravel, tu n'écris pas ces commandes à la main dans la base de production : tu passes par des **migrations**, des fichiers versionnés qui décrivent chaque évolution et que toute l'équipe rejoue dans le même ordre. L'esprit est identique : un changement de schéma est une opération tracée et réversible.

## La normalisation en trois étapes

La **normalisation** est la méthode qui évite la répétition de données. Trois formes suffisent dans 95 % des cas.

**Première forme normale (1FN)** : chaque colonne contient une valeur **atomique**, pas une liste. Une colonne `tags` contenant `php,laravel,sql` viole la 1FN : on crée une table `tags` et une table pivot.

**Deuxième forme normale (2FN)** : toute colonne dépend de **toute** la clé primaire, pas d'une partie seulement. Dans une table pivot `(user_id, roadmap_id)`, mettre le titre de la roadmap violerait la 2FN, car il ne dépend que de `roadmap_id`.

**Troisième forme normale (3FN)** : une colonne ne dépend pas d'une autre colonne non clé. Si `roadmaps` contenait `author_name` en plus de `user_id`, le nom dépendrait de `user_id` et non de la roadmap : on le retire, on le lit par jointure.

Résumé facile à retenir : **chaque donnée est stockée une seule fois, à l'endroit où elle a un sens.** Dans la pratique, on dénormalise parfois volontairement (un compteur `steps_count` sur `roadmaps`) pour gagner en vitesse de lecture, mais c'est une décision consciente, documentée, et jamais le point de départ.

:::quiz
Une table roadmaps contient les colonnes user_id et author_email. Quel problème cela pose-t-il ?
- [ ] Aucun, c'est plus rapide à lire
- [x] L'e-mail dépend de l'utilisateur, pas de la roadmap : il est dupliqué et peut devenir incohérent
- [ ] MySQL refuse les colonnes texte à côté d'une clé étrangère
- [ ] L'e-mail devient obligatoirement unique
> C'est une violation de la troisième forme normale : author_email dépend de user_id. Il faut le lire dans users par jointure.
:::

## Un schéma complet et cohérent

Voici le schéma final de DevRoad, que tu vas écrire dans l'atelier. Le choix de chaque contrainte répond à une règle métier :

| Règle métier | Contrainte |
| --- | --- |
| Un e-mail identifie un compte | `UNIQUE (email)` |
| Une roadmap a toujours un auteur | `user_id NOT NULL` + clé étrangère |
| L'URL d'une roadmap est unique | `UNIQUE (slug)` |
| Le niveau est dans une liste fermée | `ENUM` ou `CHECK (level IN (...))` |
| Une étape disparaît avec sa roadmap | `ON DELETE CASCADE` |
| Les positions sont uniques par roadmap | `UNIQUE (roadmap_id, position)` |
| Une durée est positive | `CHECK (minutes IS NULL OR minutes > 0)` |

## Atelier guidé : construire le schéma de DevRoad

Compte une heure et demie.

1. Crée une base `devroad_v2` en `utf8mb4` et sélectionne-la.
2. Écris `CREATE TABLE users` avec `id`, `name` (`NOT NULL`), `email` (`NOT NULL`, `UNIQUE`), `created_at`.
3. Écris `CREATE TABLE roadmaps` avec toutes les contraintes du tableau ci-dessus, clé étrangère vers `users` en `RESTRICT`.
4. Écris `CREATE TABLE roadmap_steps` avec `ON DELETE CASCADE`, l'unicité `(roadmap_id, position)` et les deux `CHECK`.
5. Crée la table pivot `roadmap_follows` avec sa clé primaire composée.
6. Teste chaque contrainte : insère un e-mail en double, une roadmap pour un `user_id` inexistant, une étape de -10 minutes, deux étapes à la même position. Note à chaque fois le numéro et le message d'erreur.
7. Insère une roadmap avec trois étapes, supprime-la et vérifie avec un `SELECT` que les étapes ont disparu grâce au `CASCADE`.
8. Ajoute avec `ALTER TABLE` une colonne `description TEXT NULL` à `roadmaps`, puis une colonne `deleted_at TIMESTAMP NULL`.
9. Affiche le schéma final avec `SHOW CREATE TABLE` pour chaque table.

Pour t'auto-évaluer : pour chaque contrainte, explique à voix haute quel bug elle empêcherait si elle était absente.

## Erreurs fréquentes

- **Tout laisser nullable.** Le moindre champ oublié devient `NULL` et fausse les calculs.
- **Valider uniquement dans le code applicatif.** Un script, un import ou un deuxième service contourne ta validation.
- **Utiliser `CASCADE` partout.** Une suppression en chaîne efface des données précieuses.
- **Stocker une liste dans une colonne.** Passe par une table pivot.
- **Oublier de nommer les contraintes.** Leurs noms générés (`roadmaps_ibfk_1`) sont illisibles.
- **Utiliser des types différents pour la clé étrangère et la clé primaire.** `INT` contre `BIGINT UNSIGNED` provoque l'erreur 3780 ou 1215.
- **Lancer un `ALTER TABLE` sur une grosse table de production sans test.** Il peut bloquer l'application.

## Bonnes pratiques

- Écris les contraintes dès la création de la table : les ajouter plus tard, sur des données déjà sales, est un cauchemar.
- Nomme tes contraintes avec un préfixe : `pk_`, `uq_`, `fk_`, `chk_`.
- Choisis `ON DELETE` selon le sens métier et documente-le.
- Garde des clés primaires numériques simples ; ajoute un `UNIQUE` sur la clé naturelle (e-mail, slug).
- Versionne chaque changement de schéma dans des migrations, jamais à la main en production.
- Prévois `created_at` et `updated_at` sur chaque table métier.

## À retenir

- Les contraintes (`NOT NULL`, `UNIQUE`, `CHECK`, `FOREIGN KEY`, `DEFAULT`) protègent la base à la source.
- `ON DELETE` choisit le comportement à la suppression : `RESTRICT`, `CASCADE` ou `SET NULL`.
- Une relation plusieurs-à-plusieurs se modélise avec une table pivot à clé primaire composée.
- La normalisation (1FN, 2FN, 3FN) stocke chaque donnée une seule fois ; on ne dénormalise que sciemment.
- `ALTER TABLE` fait évoluer un schéma existant ; en projet réel, on passe par des migrations versionnées.
