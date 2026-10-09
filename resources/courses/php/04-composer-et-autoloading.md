---
title: Composer et autoloading
minutes: 120
level: intermediate
---

## Ce que tu vas apprendre

Dans les chapitres précédents, tu inclus tes fichiers à la main avec `require_once`. Dès que le projet compte trente classes, cette méthode devient ingérable. De plus, tu voudras réutiliser des bibliothèques écrites par d'autres : envoyer des e-mails, manipuler des dates, lire des variables d'environnement. **Composer** règle ces deux problèmes : c'est le gestionnaire de dépendances de PHP, et il fournit un **autoloader** qui charge tes classes automatiquement. Laravel est entièrement bâti dessus.

À la fin du chapitre, tu seras capable de :

- installer Composer et créer un projet avec `composer init` ;
- ajouter, mettre à jour et retirer des dépendances ;
- lire un fichier `composer.json` et comprendre le rôle de `composer.lock` ;
- expliquer le versionnement sémantique et les contraintes (`^`, `~`) ;
- organiser ton code en **namespaces** ;
- configurer l'autoloading **PSR-4** ;
- utiliser des scripts Composer et distinguer `require` de `require-dev` ;
- structurer un projet PHP propre avec un dossier `public/` et un dossier `src/`.

Prérequis : le chapitre « POO et classes ». Prévois deux heures. Il te faut PHP 8.3 installé et un terminal.

## Le problème des inclusions manuelles

Imagine ce début de fichier :

```php
require_once 'src/Roadmap.php';
require_once 'src/Lecon.php';
require_once 'src/Parcours.php';
require_once 'src/Exportable.php';
require_once 'src/Niveau.php';
// ... vingt autres lignes
```

Chaque nouvelle classe impose d'ajouter une ligne, dans le bon ordre, dans chaque script qui l'utilise. Un oubli provoque une erreur `Class not found`. L'**autoloading** supprime ce travail : au moment où PHP rencontre une classe inconnue, il appelle une fonction qui sait retrouver le bon fichier. Composer écrit cette fonction pour toi, à partir d'une simple convention.

## Installer Composer

Rends-toi sur getcomposer.org et suis la procédure de ton système. Vérifie ensuite :

```bash
composer --version
```

Composer est lui-même un programme PHP. Il lit un fichier `composer.json` à la racine de ton projet et télécharge les bibliothèques demandées depuis **Packagist**, le catalogue public des paquets PHP.

## Créer un projet

Crée un dossier et initialise-le :

```bash
mkdir devroad-cli && cd devroad-cli
composer init
```

Réponds aux questions (nom du paquet `smith/devroad-cli`, description, type `project`). Tu obtiens un `composer.json` minimal :

```json
{
    "name": "smith/devroad-cli",
    "description": "Petit outil en ligne de commande pour DevRoad",
    "type": "project",
    "require": {
        "php": ">=8.3"
    }
}
```

Le nom suit le format `vendeur/paquet`, en minuscules. La clé `require` liste ce dont le projet a besoin pour fonctionner.

## Ajouter une dépendance

Installons une bibliothèque qui lit un fichier `.env`, pratique pour ne pas écrire mots de passe et clés d'API dans le code :

```bash
composer require vlucas/phpdotenv
```

Composer fait quatre choses :

1. il télécharge le paquet et ses propres dépendances dans le dossier `vendor/` ;
2. il ajoute une ligne dans `require` de ton `composer.json` ;
3. il écrit (ou met à jour) le fichier `composer.lock` ;
4. il génère `vendor/autoload.php`, le point d'entrée de l'autoloading.

Ton `composer.json` contient maintenant :

```json
"require": {
    "php": ">=8.3",
    "vlucas/phpdotenv": "^5.6"
}
```

Pour utiliser la bibliothèque, il suffit d'inclure **un seul fichier** :

```php
<?php

declare(strict_types=1);

require __DIR__ . '/vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->safeLoad();

echo $_ENV['APP_NAME'] ?? 'DevRoad';
```

Aucun `require_once` supplémentaire n'est nécessaire : l'autoloader trouve la classe `Dotenv\Dotenv` tout seul.

### Les commandes à connaître

| Commande | Effet |
| --- | --- |
| `composer require paquet` | Ajoute une dépendance |
| `composer require --dev paquet` | Ajoute une dépendance de développement |
| `composer install` | Installe exactement les versions du `composer.lock` |
| `composer update` | Met à jour selon les contraintes et réécrit le lock |
| `composer remove paquet` | Retire une dépendance |
| `composer dump-autoload` | Régénère l'autoloader |
| `composer show` | Liste les paquets installés |

### composer.json, composer.lock et vendor

- `composer.json` exprime ce que **tu veux** : des contraintes de versions.
- `composer.lock` enregistre ce qui a **réellement été installé**, version par version. Il garantit que ton collègue et ton serveur de production auront exactement les mêmes versions que toi. **Versionne-le dans Git.**
- `vendor/` contient le code téléchargé. Il se reconstruit avec `composer install` : **ne le mets pas dans Git**, ajoute `/vendor/` à ton `.gitignore`.

> **Attention** : en production, lance `composer install` (qui respecte le lock) et non `composer update`, sous peine d'obtenir des versions que tu n'as jamais testées.

## Le versionnement sémantique

Les versions de paquets s'écrivent `MAJEUR.MINEUR.CORRECTIF`, par exemple `5.6.1` :

- le **correctif** (5.6.**1**) corrige un bug sans rien changer au fonctionnement ;
- le **mineur** (5.**6**.0) ajoute des fonctionnalités en restant compatible ;
- le **majeur** (**5**.0.0) peut casser la compatibilité.

Les contraintes de `composer.json` s'appuient dessus :

| Contrainte | Signification |
| --- | --- |
| `^5.6` | Toute version de 5.6.0 à moins de 6.0.0 (la plus courante) |
| `~5.6.1` | Toute version de 5.6.1 à moins de 5.7.0 |
| `5.6.*` | Toute version 5.6.x |
| `>=5.0 <6.0` | Un intervalle explicite |
| `5.6.1` | Une version exacte |

Le signe `^` est le choix par défaut : tu profites des correctifs et des nouveautés sans risque de rupture.

:::quiz
Quel fichier faut-il versionner dans Git pour garantir que toute l'équipe utilise les mêmes versions de paquets ?
- [ ] Le dossier vendor/
- [x] composer.lock
- [ ] vendor/autoload.php
- [ ] Aucun des deux
> `composer.lock` fige les versions installées. Le dossier `vendor/` est volumineux et se reconstruit avec `composer install`, donc il reste hors de Git.
:::

## Les namespaces

Quand tu utilises beaucoup de code, des noms de classes finissent par se ressembler : deux bibliothèques peuvent chacune définir une classe `Logger`. Les **namespaces** (espaces de noms) évitent ces collisions, comme des dossiers pour les classes.

```php
<?php

declare(strict_types=1);

namespace DevRoad\Domain;

final class Lecon
{
    public function __construct(
        public readonly string $titre,
        public readonly int $minutes,
    ) {
    }
}
```

Le nom complet de la classe est maintenant `DevRoad\Domain\Lecon`. Pour l'utiliser depuis un autre fichier, on l'importe avec `use` :

```php
<?php

declare(strict_types=1);

namespace DevRoad\Service;

use DevRoad\Domain\Lecon;
use DevRoad\Domain\Parcours as ParcoursDomaine; // alias possible

final class Calculateur
{
    public function total(Lecon ...$lecons): int
    {
        return array_sum(array_map(fn(Lecon $l) => $l->minutes, $lecons));
    }
}
```

Quelques règles :

- `namespace` est la première instruction après `declare(strict_types=1);` ;
- sans `use`, il faudrait écrire le nom complet `\DevRoad\Domain\Lecon` ;
- les fonctions natives de PHP (`strlen`, `count`) se trouvent dans l'espace global : elles restent utilisables sans préfixe dans la quasi-totalité des cas ;
- les classes natives (`DateTimeImmutable`, `PDO`) s'écrivent avec un antislash initial `\PDO` ou s'importent avec `use PDO;`.

## L'autoloading PSR-4

**PSR-4** est la convention standard : un préfixe de namespace correspond à un dossier, et le reste du nom correspond au chemin du fichier. On le déclare dans `composer.json` :

```json
"autoload": {
    "psr-4": {
        "DevRoad\\": "src/"
    }
}
```

Avec cette ligne, la classe `DevRoad\Domain\Lecon` doit se trouver dans `src/Domain/Lecon.php`. Le nom du fichier est celui de la classe, avec la même casse. Voici la structure correspondante :

```text
devroad-cli/
├── composer.json
├── composer.lock
├── vendor/
├── public/
│   └── index.php
└── src/
    ├── Domain/
    │   ├── Lecon.php
    │   └── Niveau.php
    └── Service/
        └── Calculateur.php
```

Après avoir modifié la section `autoload`, **régénère** la table de correspondance :

```bash
composer dump-autoload
```

Désormais, plus aucun `require_once` pour tes classes :

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DevRoad\Domain\Lecon;
use DevRoad\Service\Calculateur;

$calcul = new Calculateur();
echo $calcul->total(new Lecon("PHP", 120), new Lecon("Composer", 90)); // 210
```

C'est exactement ce qui se passe dans Laravel : le préfixe `App\` pointe vers le dossier `app/`, donc `App\Models\Roadmap` vit dans `app/Models/Roadmap.php`.

:::quiz
Avec le mapping PSR-4 `"DevRoad\\": "src/"`, où doit se trouver la classe `DevRoad\Service\Mailer` ?
- [ ] src/Mailer.php
- [ ] src/service/mailer.php
- [x] src/Service/Mailer.php
- [ ] DevRoad/Service/Mailer.php
> Le préfixe `DevRoad\` est remplacé par `src/`, puis le reste du namespace devient des dossiers et le nom de la classe devient le nom du fichier, avec la même casse.
:::

## Dépendances de développement et scripts

Certains outils servent uniquement pendant le développement : analyse statique, formatage du code, tests. On les installe avec `--dev` :

```bash
composer require --dev phpunit/phpunit
```

Ils vont dans `require-dev` et ne sont pas installés en production avec `composer install --no-dev`.

La section `scripts` permet de définir des raccourcis :

```json
"scripts": {
    "start": "php -S localhost:8000 -t public",
    "test": "php tests/run.php"
}
```

On les lance ensuite avec `composer start` ou `composer test`. Tout le monde dans l'équipe utilise les mêmes commandes, sans avoir à les mémoriser.

> **Astuce** : `composer outdated` liste les paquets pour lesquels une version plus récente existe, et `composer audit` signale les failles de sécurité connues dans tes dépendances. Lance-le régulièrement.

## Choisir un paquet avec discernement

Packagist contient plus de 400 000 paquets. Avant d'ajouter une dépendance, vérifie :

- **la maintenance** : date du dernier commit, nombre de contributeurs ;
- **la popularité** : nombre d'installations, étoiles ;
- **la compatibilité** avec PHP 8.3 ;
- **la licence**, compatible avec ton projet (MIT, BSD, Apache) ;
- **le besoin réel** : une fonction de cinq lignes ne justifie pas une dépendance.

Chaque paquet ajouté est du code que tu dois mettre à jour et dont tu dépends pour ta sécurité.

## Atelier guidé : un projet structuré avec autoloading

Compte une heure et demie. Tu vas mettre en place un petit projet « DevRoad CLI » avec Composer.

1. Crée le dossier `devroad-cli` et lance `composer init` (nom `smith/devroad-cli`, PHP `>=8.3`).
2. Ajoute dans `composer.json` la section `autoload` PSR-4 pour `DevRoad\\` vers `src/`.
3. Crée `src/Domain/Niveau.php` (une enum avec trois cas) et `src/Domain/Lecon.php` (propriétés `readonly`). Respecte les namespaces et les majuscules des fichiers.
4. Crée `src/Service/Catalogue.php` avec une méthode `dureeTotale()` et une méthode `parNiveau(Niveau $n)`.
5. Lance `composer dump-autoload`.
6. Crée `public/index.php` : il inclut uniquement `vendor/autoload.php`, instancie quelques leçons et affiche le total.
7. Installe `vlucas/phpdotenv` et crée un fichier `.env` contenant `APP_NAME=DevRoad CLI`. Charge-le et affiche le nom dans `index.php`.
8. Ajoute un fichier `.gitignore` contenant `/vendor/` et `.env`, puis crée un `.env.example` à versionner.
9. Ajoute un script Composer `start` et lance le serveur avec `composer start`.
10. Supprime le dossier `vendor/` puis lance `composer install` : constate que tout revient à l'identique grâce au lock.

Pour t'auto-évaluer : sais-tu expliquer pourquoi `.env` n'est pas versionné alors que `composer.lock` l'est ? Et que se passe-t-il si tu renommes `Lecon.php` en `lecon.php` sous Linux ?

## Erreurs fréquentes

- **`Class "..." not found`** : le namespace ne correspond pas au chemin du fichier, ou tu as oublié `composer dump-autoload` après avoir modifié `composer.json`.
- **Casse du fichier incorrecte** : `lecon.php` fonctionne sous Windows mais échoue sous Linux, donc en production. Respecte exactement la casse.
- **Oublier `require 'vendor/autoload.php'`** dans le point d'entrée.
- **Mettre `vendor/` dans Git** : le dépôt devient énorme et inutilement lourd.
- **Lancer `composer update` en production** : tu installes des versions jamais testées.
- **Déclarer le namespace après du code** : `namespace` doit venir en tout début de fichier.
- **Committer `.env`** : tes secrets se retrouvent publics. Ajoute-le au `.gitignore` dès le départ.

## Bonnes pratiques

- Un fichier = une classe, dont le nom est celui du fichier.
- Utilise PSR-4 dans tous tes projets et abandonne les `require_once` pour les classes.
- Versionne `composer.json` et `composer.lock`, ignore `vendor/`.
- Garde la contrainte `^` par défaut et mets à jour régulièrement avec `composer outdated` et `composer audit`.
- Sépare dépendances de production et de développement.
- Place le point d'entrée web dans `public/`, et le code applicatif en dehors : le serveur ne doit exposer que `public/`.
- Fournis un `.env.example` documentant les variables attendues, sans valeurs secrètes.
- Choisis des paquets maintenus et évalue le coût de chaque dépendance.

## À retenir

- Composer gère les dépendances PHP depuis Packagist et génère l'autoloader `vendor/autoload.php`.
- `composer.json` décrit tes contraintes, `composer.lock` fige les versions : on versionne les deux, jamais `vendor/`.
- Le versionnement sémantique (majeur.mineur.correctif) et la contrainte `^` règlent les mises à jour sûres.
- Les namespaces organisent les classes ; `use` les importe.
- PSR-4 relie un préfixe de namespace à un dossier ; `composer dump-autoload` actualise la table.
- `require-dev` et `scripts` structurent le travail d'équipe.
- Laravel applique exactement ce schéma : `App\` pointe vers `app/`.
