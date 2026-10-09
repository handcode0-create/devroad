---
title: Migrations et schéma de base
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Une application sans données n'est qu'une coquille. Mais comment décrire proprement la structure de ta base, la partager avec ton équipe et la faire évoluer sans rien casser ? Laravel répond avec les **migrations** : du code PHP qui construit et modifie la base de données.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi on ne modifie plus jamais une base « à la main » ;
- créer une migration et décrire une table avec les types de colonnes adaptés ;
- relier deux tables avec une clé étrangère ;
- ajouter une colonne à une table existante sans perdre de données ;
- exécuter, annuler et recommencer des migrations en connaissant les risques ;
- concevoir le schéma de DevRoad : utilisateurs, roadmaps, étapes, fiches et tags.

Prévois deux heures et demie. Garde phpMyAdmin (ou ton client de base) ouvert pour voir les effets de chaque commande.

## Pourquoi des migrations ?

Imagine que tu travailles à deux. Tu ajoutes une colonne `technology` dans ta base locale avec phpMyAdmin. Ton collègue lance son code, et plante : *sa* base ne contient pas la colonne. Et en production ? Il faudrait refaire la manipulation à la main, sans rien oublier. À chaque modification, le risque d'erreur grandit.

Une **migration** règle ce problème. C'est un fichier PHP qui décrit un changement de la base. Les fichiers sont rangés dans `database/migrations`, datés, exécutés dans l'ordre. Résultat :

- tout le monde obtient exactement la même structure avec une commande ;
- l'historique des changements est dans Git, comme le reste du code ;
- tu peux revenir en arrière ;
- la mise en production d'une évolution se fait par une seule commande.

Pense aux migrations comme à un **carnet d'instructions** : à partir d'une base vide, en le rejouant du début à la fin, on reconstruit toute la structure.

## Anatomie d'une migration

Crée ta première migration :

```bash
php artisan make:migration create_roadmaps_table
```

Laravel comprend le nom : « create » + « roadmaps » + « table ». Il génère un fichier daté, avec un squelette déjà adapté à la création d'une table :

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmaps', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmaps');
    }
};
```

Une migration contient deux méthodes :

- **`up()`** fait le changement (créer la table) ;
- **`down()`** l'annule (supprimer la table).

L'objet `$table` (un `Blueprint`) est ton crayon : chaque ligne décrit une colonne.

:::quiz
Quel est le rôle de la méthode down() d'une migration ?
- [ ] Elle crée la table
- [x] Elle annule le changement fait par up()
- [ ] Elle remplit la table de données
- [ ] Elle vérifie la connexion à la base
> up() applique le changement, down() le défait. C'est ce qui permet le retour en arrière avec migrate:rollback.
:::

## Les types de colonnes

Chaque colonne a un **type**, qui dit quelles données elle accepte. Voici ceux que tu utiliseras tout le temps.

| Méthode | Colonne créée | Usage |
| --- | --- | --- |
| `id()` | Entier auto-incrémenté, clé primaire | Identifiant de chaque ligne |
| `string('title')` | Texte court (255 caractères) | Titres, noms, e-mails |
| `text('description')` | Texte long | Descriptions |
| `longText('content')` | Très long texte | Contenu d'un cours |
| `integer('position')` | Nombre entier | Quantités, ordre |
| `unsignedSmallInteger('minutes')` | Petit entier positif | Durées |
| `boolean('is_favorite')` | Vrai ou faux | Drapeaux |
| `timestamp('completed_at')` | Date et heure | Moments précis |
| `json('settings')` | Données structurées | Préférences |
| `timestamps()` | `created_at` et `updated_at` | Suivi automatique |

### Les modificateurs

On enchaîne ensuite des **modificateurs** pour préciser la colonne :

```php
$table->string('status')->default('todo');           // valeur par défaut
$table->text('description')->nullable();             // peut rester vide
$table->string('slug')->unique();                    // valeur unique dans la table
$table->integer('position')->index();                // accélère les recherches
$table->boolean('is_favorite')->default(false);
```

Retiens la différence essentielle : une colonne est **obligatoire par défaut**. Si une valeur peut légitimement manquer, ajoute `nullable()`. Sans cela, l'enregistrement d'une ligne sans cette donnée échoue.

> **Astuce** : choisis le type le plus **petit qui convient**. Une durée en minutes ne dépassera jamais 65 535 : `unsignedSmallInteger` suffit, pas besoin d'un entier géant.

## Relier les tables avec une clé étrangère

Une roadmap appartient à un utilisateur. Chaque roadmap contient plusieurs étapes. Pour exprimer ces liens, on utilise une **clé étrangère** : une colonne qui stocke l'identifiant de la ligne liée.

```php
Schema::create('roadmaps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('technology')->nullable();
    $table->timestamps();
});

Schema::create('roadmap_steps', function (Blueprint $table) {
    $table->id();
    $table->foreignId('roadmap_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->string('status')->default('todo');
    $table->unsignedInteger('position')->default(1);
    $table->timestamps();
});
```

Décortiquons `foreignId('user_id')->constrained()->cascadeOnDelete()` :

- `foreignId('user_id')` crée une colonne d'entier adaptée aux identifiants ;
- `constrained()` devine la table liée (`users`) grâce au nom de la colonne, et crée la **contrainte** : la base refusera un `user_id` qui n'existe pas ;
- `cascadeOnDelete()` précise que, si l'utilisateur est supprimé, ses roadmaps le sont aussi.

Sans la bonne convention de nommage (`user_id` pour la table `users`), il faudrait préciser le nom de la table à la main. C'est un exemple de plus de l'intérêt des conventions.

> **Attention** : réfléchis toujours au comportement de suppression. `cascadeOnDelete()` est pratique mais efface en chaîne. Si tu préfères interdire la suppression d'un parent qui a encore des enfants, utilise `restrictOnDelete()`.

### Les tables pivot

Une fiche peut avoir plusieurs tags, et un tag peut s'appliquer à plusieurs fiches : c'est une relation « plusieurs à plusieurs ». On la représente avec une **table pivot**, nommée avec les deux modèles dans l'ordre alphabétique, au singulier : `memo_tag`.

```php
Schema::create('memo_tag', function (Blueprint $table) {
    $table->foreignId('memo_id')->constrained()->cascadeOnDelete();
    $table->foreignId('tag_id')->constrained()->cascadeOnDelete();
    $table->primary(['memo_id', 'tag_id']);
});
```

La clé primaire sur les deux colonnes garantit qu'un même tag ne peut pas être associé deux fois à la même fiche.

:::quiz
Que fait `->constrained()` après `foreignId('roadmap_id')` ?
- [ ] Il rend la colonne obligatoire uniquement
- [x] Il crée une contrainte qui oblige la valeur à exister dans la table roadmaps
- [ ] Il supprime la colonne
- [ ] Il transforme la colonne en texte
> constrained() déduit la table liée du nom de la colonne et pose une contrainte de clé étrangère : la base refuse un identifiant inexistant.
:::

## Exécuter, annuler, recommencer

Les migrations se pilotent avec `artisan`. Voici les commandes à connaître.

```bash
# Exécute toutes les migrations qui n'ont pas encore été jouées
php artisan migrate

# Affiche l'état de chaque migration (jouée ou en attente)
php artisan migrate:status

# Annule le dernier lot de migrations
php artisan migrate:rollback

# Annule tout, puis rejoue tout (DÉTRUIT les données)
php artisan migrate:fresh

# Idem, puis remplit la base avec des données de départ
php artisan migrate:fresh --seed
```

Laravel garde en mémoire, dans une table `migrations`, la liste des fichiers déjà exécutés. Ainsi, `php artisan migrate` ne rejoue jamais deux fois la même migration et n'exécute que les nouvelles.

> **Attention** : `migrate:fresh` **supprime toutes les tables et leurs données**. Pratique en développement, désastreux en production. En production, on utilise uniquement `php artisan migrate --force`, qui ne fait qu'ajouter.

## Faire évoluer une table existante

Ton application est en ligne et tu veux ajouter la colonne `technology` aux roadmaps. Tu **ne modifies jamais** une migration déjà exécutée : les autres bases ne la rejoueraient pas. Tu crées une **nouvelle** migration.

```bash
php artisan make:migration add_technology_to_roadmaps_table
```

Le nom « add... to... table » indique à Laravel qu'il s'agit d'une modification (`Schema::table`) et non d'une création :

```php
public function up(): void
{
    Schema::table('roadmaps', function (Blueprint $table) {
        $table->string('technology')->nullable()->after('title');
    });
}

public function down(): void
{
    Schema::table('roadmaps', function (Blueprint $table) {
        $table->dropColumn('technology');
    });
}
```

Remarque le `nullable()` : la table contient déjà des lignes. Si tu ajoutais une colonne obligatoire sans valeur par défaut, la base ne saurait pas quoi mettre dans les lignes existantes, et la migration échouerait. Pour une colonne obligatoire, prévois une valeur par défaut avec `default(...)`.

> **À retenir** : une migration déjà partagée est **intouchable**. Pour changer quelque chose, on écrit une nouvelle migration. L'historique de ton projet se lit alors comme un journal.

:::quiz
Ta table contient déjà des données. Tu ajoutes une colonne obligatoire sans valeur par défaut. Que risque-t-il d'arriver ?
- [ ] Rien, Laravel remplit automatiquement
- [x] La migration échoue, car la base ne sait pas quoi mettre dans les lignes existantes
- [ ] Les anciennes lignes sont supprimées
- [ ] La colonne devient nullable
> Les lignes existantes n'ont aucune valeur pour la nouvelle colonne : il faut un default, ou nullable(), pour que la migration passe.
:::

## Les seeders et les factories en deux mots

Une base vide est peu pratique pour développer. Deux outils t'aident :

- une **factory** fabrique de fausses lignes réalistes (`User::factory()->count(10)->create()`) ;
- un **seeder** lance ces fabrications et remplit la base de départ (`php artisan db:seed`).

Tu t'en serviras surtout pour les tests. Retiens simplement leur existence, tu les croiseras au chapitre sur le CRUD.

## Atelier guidé : le schéma de DevRoad

Compte une heure et demie. Travaille sur un projet vide, pour ne rien casser.

1. Crée la migration `create_roadmaps_table` avec : `user_id` (clé étrangère, suppression en cascade), `title`, `description` (nullable), `status` (défaut `active`), horodatages.
2. Crée `create_roadmap_steps_table` avec : `roadmap_id` (clé étrangère), `title`, `description` (nullable), `status` (défaut `todo`), `position` (défaut 1), `estimated_minutes` (petit entier, nullable), horodatages.
3. Lance `php artisan migrate`, puis `php artisan migrate:status`.
4. Ouvre phpMyAdmin et vérifie les colonnes et les clés étrangères.
5. Crée `add_technology_to_roadmaps_table` : ajoute `technology` (nullable) après `title`.
6. Lance `php artisan migrate` : seule la nouvelle migration doit être exécutée.
7. Teste `php artisan migrate:rollback` : la colonne `technology` disparaît. Relance `migrate` pour la remettre.
8. Crée la table pivot `memo_tag` avec clé primaire composite, puis tente d'y insérer deux fois la même paire dans phpMyAdmin : que se passe-t-il ?

Pour t'auto-évaluer : peux-tu expliquer pourquoi on crée une nouvelle migration au lieu de modifier l'ancienne ?

## Erreurs fréquentes

- **Modifier une migration déjà exécutée.** Elle ne sera pas rejouée : crée-en une nouvelle.
- **Ordre des migrations incorrect.** Une table avec clé étrangère doit être créée **après** la table qu'elle référence. Les noms datés garantissent l'ordre, vérifie-le.
- **Lancer `migrate:fresh` en production.** Tu perds toutes les données.
- **Oublier `nullable()`.** L'enregistrement échoue dès qu'une donnée manque.
- **Oublier `down()`.** Sans lui, aucun retour en arrière propre n'est possible.
- **Un nom de colonne qui casse la convention.** Utilise `user_id`, pas `userId` ni `id_user`.

## Bonnes pratiques

- Une migration, un changement, un nom parlant.
- Écris toujours `down()` pour pouvoir annuler.
- Ne modifie jamais une migration qui a déjà été partagée ou déployée.
- Pense à `nullable()`, `default()` et `index()` dès la conception.
- Réfléchis au comportement de suppression pour chaque clé étrangère.
- Sauvegarde la base avant toute migration en production.

## À retenir

- Les migrations décrivent la structure de la base en code, versionné et rejouable.
- `up()` applique le changement, `down()` l'annule.
- Les clés étrangères (`foreignId()->constrained()`) garantissent la cohérence des liens entre tables.
- Une table pivot nommée dans l'ordre alphabétique porte la relation plusieurs-à-plusieurs.
- `migrate` ajoute, `migrate:rollback` annule, `migrate:fresh` détruit tout : jamais en production.
- Pour faire évoluer une table existante, on crée une nouvelle migration `add_..._to_..._table`.
