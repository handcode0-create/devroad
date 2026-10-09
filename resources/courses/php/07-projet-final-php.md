---
title: Projet final PHP
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru les fondamentaux, les tableaux, la POO, Composer, HTTP et la sécurité. Il est temps de tout assembler dans un vrai projet, livrable et présentable dans un portfolio : **DevCarnet**, une petite application de suivi d'apprentissage écrite en PHP 8.3 pur, sans framework. Tu y retrouveras, à échelle réduite, l'architecture de Laravel : un point d'entrée unique, un routeur, des contrôleurs, des dépôts, des vues, de l'authentification et des tests.

À la fin du projet, tu seras capable de :

- structurer une application PHP en couches avec Composer et PSR-4 ;
- écrire un routeur minimal et un point d'entrée unique ;
- implémenter une authentification complète et sécurisée ;
- modéliser des données avec des classes, des enums et un dépôt PDO ;
- exposer une API JSON en plus de l'interface HTML ;
- protéger l'application (XSS, injection SQL, CSRF, session) ;
- couvrir la logique métier par des tests automatisés ;
- préparer une livraison : configuration, `.env`, README.

Prérequis : les six chapitres précédents du parcours PHP. Durée : environ six heures, à répartir sur plusieurs séances. Avance par étapes et teste après chacune.

## Le cahier des charges

**DevCarnet** permet à un apprenant de noter ce qu'il a étudié, de suivre son temps de travail et de visualiser sa progression.

### Fonctionnalités attendues

- **Inscription et connexion** par e-mail et mot de passe, déconnexion.
- **Sujets d'étude** : créer, lister, modifier, supprimer ses propres sujets (titre, niveau, objectif en heures).
- **Séances** : enregistrer une séance de travail liée à un sujet (date, durée en minutes, note libre).
- **Tableau de bord** : total d'heures par sujet, pourcentage de l'objectif atteint, séries des derniers jours.
- **API JSON** en lecture : `GET /api/sujets` et `GET /api/stats`, réservée à l'utilisateur connecté.
- **Messages flash** après chaque action.

### Contraintes techniques

- PHP 8.3, `declare(strict_types=1);` dans tous les fichiers.
- Composer avec autoloading PSR-4 (`DevCarnet\` vers `src/`).
- SQLite pour la base (un fichier), PDO avec requêtes préparées uniquement.
- Aucun framework ni bibliothèque de routage : tu écris le routeur.
- Seul le dossier `public/` est exposé au serveur web.
- Chaque utilisateur ne voit et ne modifie **que ses propres données**.

### Structure cible

```text
devcarnet/
├── composer.json
├── .env.example
├── .gitignore
├── README.md
├── database/
│   └── schema.sql
├── public/
│   └── index.php
├── src/
│   ├── Core/            Routeur, Requete, Reponse, Vue, Session, Csrf, Database
│   ├── Domain/          Niveau, Sujet, Seance, Utilisateur
│   ├── Repository/      SujetRepository, SeanceRepository, UtilisateurRepository
│   ├── Service/         Authentification, Statistiques, ValidateurSujet
│   └── Controller/      AuthController, SujetController, SeanceController, ApiController
├── templates/           layout.php, auth/, sujets/, seances/, dashboard.php
├── storage/             devcarnet.sqlite (ignoré par Git)
└── tests/               run.php et fichiers de tests
```

## Étape 1 : mettre le projet en place (30 min)

Crée le dossier, initialise Composer et déclare l'autoloading :

```bash
mkdir devcarnet && cd devcarnet
composer init --name="smith/devcarnet" --require="php:>=8.3" --no-interaction
```

Dans `composer.json`, ajoute :

```json
"autoload": { "psr-4": { "DevCarnet\\": "src/" } },
"scripts": {
    "start": "php -S localhost:8000 -t public",
    "test": "php tests/run.php"
}
```

Puis `composer dump-autoload`. Ajoute un `.gitignore` contenant `/vendor/`, `.env` et `/storage/*.sqlite`.

Crée le schéma `database/schema.sql` :

```sql
CREATE TABLE IF NOT EXISTS utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    mot_de_passe TEXT NOT NULL,
    cree_le TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS sujets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    titre TEXT NOT NULL,
    niveau TEXT NOT NULL,
    objectif_heures INTEGER NOT NULL
);

CREATE TABLE IF NOT EXISTS seances (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    sujet_id INTEGER NOT NULL REFERENCES sujets(id) ON DELETE CASCADE,
    date_seance TEXT NOT NULL,
    minutes INTEGER NOT NULL,
    note TEXT
);
```

La classe `Core\Database` ouvre la connexion et l'expose :

```php
<?php

declare(strict_types=1);

namespace DevCarnet\Core;

use PDO;

final class Database
{
    public static function connecter(string $chemin): PDO
    {
        $pdo = new PDO('sqlite:' . $chemin, options: [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);
        $pdo->exec('PRAGMA foreign_keys = ON');

        return $pdo;
    }

    public static function migrer(PDO $pdo, string $fichierSql): void
    {
        $pdo->exec((string) file_get_contents($fichierSql));
    }
}
```

**Point de contrôle** : `php -r "require 'vendor/autoload.php'; ..."` ou un petit script qui appelle `Database::connecter` puis `migrer` et crée le fichier SQLite sans erreur.

## Étape 2 : le domaine (35 min)

Écris l'enum `Niveau` et deux classes de valeurs immuables :

```php
<?php

declare(strict_types=1);

namespace DevCarnet\Domain;

enum Niveau: string
{
    case Debutant = 'beginner';
    case Intermediaire = 'intermediate';
    case Professionnel = 'professional';

    public function libelle(): string
    {
        return match ($this) {
            self::Debutant => 'Débutant',
            self::Intermediaire => 'Intermédiaire',
            self::Professionnel => 'Professionnel',
        };
    }
}

final class Sujet
{
    public function __construct(
        public readonly ?int $id,
        public readonly int $utilisateurId,
        public readonly string $titre,
        public readonly Niveau $niveau,
        public readonly int $objectifHeures,
    ) {
    }
}
```

Place chaque classe dans son propre fichier (`Niveau.php`, `Sujet.php`...). Crée aussi `Seance` (`id`, `sujetId`, `dateSeance`, `minutes`, `note`) et `Utilisateur` (`id`, `email`, `motDePasseHache`).

Ajoute le service `ValidateurSujet`, qui renvoie un tableau d'erreurs :

```php
final class ValidateurSujet
{
    /** @return array<string, string> */
    public function valider(array $donnees): array
    {
        $erreurs = [];

        $titre = trim((string) ($donnees['titre'] ?? ''));
        if ($titre === '' || mb_strlen($titre) > 100) {
            $erreurs['titre'] = "Le titre est obligatoire (100 caractères maximum).";
        }
        if (Niveau::tryFrom((string) ($donnees['niveau'] ?? '')) === null) {
            $erreurs['niveau'] = "Niveau invalide.";
        }
        $objectif = filter_var($donnees['objectif_heures'] ?? null, FILTER_VALIDATE_INT);
        if ($objectif === false || $objectif < 1 || $objectif > 500) {
            $erreurs['objectif_heures'] = "L'objectif doit être compris entre 1 et 500 heures.";
        }

        return $erreurs;
    }
}
```

Fais la même chose pour les séances (`ValidateurSeance` : date au format `Y-m-d`, durée entre 5 et 600 minutes, note de 500 caractères maximum).

:::quiz
Pourquoi les classes du domaine (`Sujet`, `Seance`) utilisent-elles des propriétés `readonly` ?
- [ ] Pour que PHP les exécute plus vite
- [x] Pour garantir que l'objet reste cohérent : il ne peut plus être modifié après sa création
- [ ] Parce que PDO l'exige
- [ ] Pour empêcher leur affichage
> Un objet immuable ne peut pas se retrouver dans un état incohérent après coup. Pour une modification, on crée un nouvel objet, ce qui rend le comportement prévisible.
:::

## Étape 3 : les dépôts (45 min)

Chaque dépôt regroupe **toutes** les requêtes d'une table et reçoit le `PDO` par son constructeur.

```php
<?php

declare(strict_types=1);

namespace DevCarnet\Repository;

use DevCarnet\Domain\{Niveau, Sujet};
use PDO;

final class SujetRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    /** @return Sujet[] */
    public function deUtilisateur(int $utilisateurId): array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sujets WHERE utilisateur_id = :u ORDER BY titre');
        $stmt->execute(['u' => $utilisateurId]);

        return array_map($this->hydrater(...), $stmt->fetchAll());
    }

    public function trouver(int $id, int $utilisateurId): ?Sujet
    {
        $stmt = $this->pdo->prepare('SELECT * FROM sujets WHERE id = :id AND utilisateur_id = :u');
        $stmt->execute(['id' => $id, 'u' => $utilisateurId]);
        $ligne = $stmt->fetch();

        return $ligne ? $this->hydrater($ligne) : null;
    }

    public function creer(int $utilisateurId, string $titre, Niveau $niveau, int $objectif): int
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO sujets (utilisateur_id, titre, niveau, objectif_heures) VALUES (?, ?, ?, ?)'
        );
        $stmt->execute([$utilisateurId, $titre, $niveau->value, $objectif]);

        return (int) $this->pdo->lastInsertId();
    }

    public function supprimer(int $id, int $utilisateurId): void
    {
        $this->pdo->prepare('DELETE FROM sujets WHERE id = ? AND utilisateur_id = ?')
            ->execute([$id, $utilisateurId]);
    }

    private function hydrater(array $l): Sujet
    {
        return new Sujet((int) $l['id'], (int) $l['utilisateur_id'], $l['titre'],
            Niveau::from($l['niveau']), (int) $l['objectif_heures']);
    }
}
```

Observe un point essentiel : **chaque requête filtre par `utilisateur_id`**. C'est ce qui garantit qu'un utilisateur ne peut pas lire ou supprimer le sujet d'un autre en devinant son identifiant (une faille appelée IDOR). Écris dans le même esprit la méthode `modifier`, puis `SeanceRepository` (`deSujet`, `creer`, `supprimer`, `totalMinutesParSujet`) et `UtilisateurRepository` (`parEmail`, `creer`).

Pour les statistiques, une seule requête agrégée est préférable à une requête par sujet :

```sql
SELECT s.id, s.titre, s.objectif_heures, COALESCE(SUM(se.minutes), 0) AS minutes
FROM sujets s
LEFT JOIN seances se ON se.sujet_id = s.id
WHERE s.utilisateur_id = :u
GROUP BY s.id
```

## Étape 4 : le noyau HTTP (45 min)

Écris le **point d'entrée unique** `public/index.php`, qui démarre la session, charge la configuration, construit les dépendances et délègue au routeur.

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DevCarnet\Core\{Database, Routeur, Session};

Session::demarrer();
$pdo = Database::connecter(__DIR__ . '/../storage/devcarnet.sqlite');
Database::migrer($pdo, __DIR__ . '/../database/schema.sql');

$routeur = new Routeur();
// les routes sont déclarées à l'étape suivante

$routeur->repartir($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
```

Un routeur minimal associe une méthode et un chemin à une fonction. Il gère les paramètres comme `/sujets/{id}` :

```php
final class Routeur
{
    /** @var array<int, array{string, string, callable}> */
    private array $routes = [];

    public function ajouter(string $methode, string $modele, callable $action): void
    {
        $regex = '#^' . preg_replace('#\{(\w+)\}#', '(?P<$1>[^/]+)', $modele) . '$#';
        $this->routes[] = [$methode, $regex, $action];
    }

    public function repartir(string $methode, string $chemin): void
    {
        foreach ($this->routes as [$m, $regex, $action]) {
            if ($m === $methode && preg_match($regex, $chemin, $trouve)) {
                $params = array_filter($trouve, 'is_string', ARRAY_FILTER_USE_KEY);
                $action(...$params);
                return;
            }
        }
        http_response_code(404);
        echo 'Page introuvable';
    }
}
```

Ajoute les classes `Session` (démarrage sécurisé, messages flash), `Csrf` (jeton et vérification, voir le chapitre sécurité), `Vue` (inclusion d'un template avec extraction des variables et fonction `e()`) et `Reponse` (méthodes `redirection`, `json`, `html`).

```php
final class Vue
{
    public static function rendre(string $template, array $donnees = []): void
    {
        extract($donnees, EXTR_SKIP);
        ob_start();
        require __DIR__ . "/../../templates/$template.php";
        $contenu = ob_get_clean();
        require __DIR__ . '/../../templates/layout.php';
    }
}
```

Dans `layout.php`, affiche `$contenu`, le message flash et la navigation. Utilise `e()` pour **toutes** les données affichées.

:::quiz
Pourquoi n'expose-t-on que le dossier `public/` au serveur web ?
- [ ] Pour que le site se charge plus vite
- [ ] Parce que PHP ne sait pas lire les autres dossiers
- [x] Pour qu'on ne puisse pas télécharger directement le code source, la base ou le fichier .env
- [ ] Pour désactiver les sessions
> Tout fichier placé dans le dossier exposé peut être demandé par URL. En ne servant que `public/`, la base SQLite, la configuration et le code restent inaccessibles depuis l'extérieur.
:::

## Étape 5 : authentification et sécurité (50 min)

Crée le service `Authentification` :

```php
final class Authentification
{
    public function __construct(private UtilisateurRepository $utilisateurs)
    {
    }

    public function inscrire(string $email, string $motDePasse): int
    {
        $email = mb_strtolower(trim($email));
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("E-mail invalide.");
        }
        if (mb_strlen($motDePasse) < 10) {
            throw new InvalidArgumentException("Le mot de passe doit contenir au moins 10 caractères.");
        }
        if ($this->utilisateurs->parEmail($email) !== null) {
            throw new InvalidArgumentException("Cette adresse est déjà utilisée.");
        }

        return $this->utilisateurs->creer($email, password_hash($motDePasse, PASSWORD_DEFAULT));
    }

    public function connecter(string $email, string $motDePasse): ?int
    {
        $u = $this->utilisateurs->parEmail(mb_strtolower(trim($email)));

        if ($u === null || !password_verify($motDePasse, $u->motDePasseHache)) {
            return null; // message identique dans tous les cas d'échec
        }
        session_regenerate_id(true);

        return $u->id;
    }
}
```

Écris `AuthController` avec les routes `GET/POST /inscription`, `GET/POST /connexion` et `POST /deconnexion`. Crée une fonction `exigerConnexion(): int` qui redirige vers `/connexion` si `$_SESSION['user_id']` est absent et renvoie sinon l'identifiant. **Chaque** contrôleur protégé l'appelle en première ligne.

Vérifie le jeton CSRF sur **toutes** les routes `POST`. Un moyen propre est de le faire dans le routeur : pour toute méthode autre que `GET`, appeler `Csrf::verifier($_POST['_csrf'] ?? null)` avant l'action. Configure enfin les cookies de session (`httponly`, `samesite`, `secure` si HTTPS).

## Étape 6 : les fonctionnalités métier (60 min)

Implémente progressivement, en testant dans le navigateur à chaque étape :

1. `GET /sujets` : liste des sujets de l'utilisateur avec l'objectif et le temps cumulé.
2. `GET /sujets/nouveau` et `POST /sujets` : formulaire et création, avec validation et réaffichage des anciennes valeurs.
3. `GET /sujets/{id}` : détail d'un sujet et de ses séances. Si le sujet n'appartient pas à l'utilisateur, réponds `404`.
4. `POST /sujets/{id}/modifier` et `POST /sujets/{id}/supprimer`.
5. `POST /sujets/{id}/seances` : ajout d'une séance (date, minutes, note).
6. `GET /` : tableau de bord avec les statistiques.

Le service `Statistiques` calcule les indicateurs à partir de tableaux simples, ce qui le rend très facile à tester :

```php
final class Statistiques
{
    /** @param array<int, array{titre: string, objectif_heures: int, minutes: int}> $lignes */
    public function progression(array $lignes): array
    {
        return array_map(function (array $l): array {
            $objectifMinutes = $l['objectif_heures'] * 60;
            $pourcent = $objectifMinutes > 0 ? min(100, (int) round($l['minutes'] / $objectifMinutes * 100)) : 0;

            return ['titre' => $l['titre'], 'heures' => round($l['minutes'] / 60, 1), 'pourcent' => $pourcent];
        }, $lignes);
    }

    public function totalHeures(array $lignes): float
    {
        return round(array_sum(array_column($lignes, 'minutes')) / 60, 1);
    }
}
```

## Étape 7 : l'API JSON (25 min)

Expose deux routes en lecture, réservées aux utilisateurs connectés :

```php
$routeur->ajouter('GET', '/api/sujets', function () use ($sujets) {
    $id = Session::utilisateurId() ?? Reponse::json(['erreur' => 'Non authentifié'], 401);

    Reponse::json(['data' => array_map(
        fn($s) => ['id' => $s->id, 'titre' => $s->titre, 'niveau' => $s->niveau->value],
        $sujets->deUtilisateur($id)
    )]);
});
```

La méthode `Reponse::json(array $donnees, int $statut = 200): never` envoie l'en-tête `Content-Type: application/json`, le code de statut, le JSON encodé avec `JSON_UNESCAPED_UNICODE`, puis termine le script. Ajoute `GET /api/stats` qui renvoie la progression par sujet.

## Étape 8 : les tests (45 min)

Crée `tests/run.php` avec le mini-moteur du chapitre précédent (fonctions `test()` et `assertEgal()`), puis couvre au minimum :

- `ValidateurSujet` : titre vide, titre trop long, niveau inconnu, objectif hors bornes, cas valide (5 tests) ;
- `Statistiques` : objectif atteint, objectif dépassé plafonné à 100 %, objectif nul, aucun temps (4 tests) ;
- `SujetRepository` avec une base `sqlite::memory:` : création, lecture, **isolation entre deux utilisateurs**, suppression (4 tests) ;
- `Authentification` : inscription valide, e-mail en doublon, mot de passe trop court, connexion réussie, mauvais mot de passe (5 tests).

Le test d'isolation est le plus important : il crée un sujet pour l'utilisateur 1, demande `trouver($id, 2)` et exige `null`.

## Étape 9 : la livraison (25 min)

- Rédige un `README.md` : présentation, prérequis, installation (`composer install`, copie de `.env.example`), lancement (`composer start`), tests (`composer test`), structure du projet.
- Vérifie que `.env`, `vendor/` et la base SQLite sont ignorés par Git.
- Désactive l'affichage des erreurs quand `APP_ENV=production` et journalise-les dans `storage/erreurs.log`.
- Relis chaque template : y a-t-il une donnée affichée sans `e()` ?
- Fais un dernier test manuel complet : inscription, connexion, création d'un sujet, ajout de séances, tableau de bord, API, déconnexion.

## Checklist d'acceptation

Ton projet est terminé lorsque **toutes** ces cases sont cochées :

- [ ] `composer install` puis `composer start` suffisent à lancer l'application.
- [ ] Tous les fichiers PHP commencent par `declare(strict_types=1);`.
- [ ] L'autoloading PSR-4 fonctionne : aucun `require_once` pour les classes.
- [ ] Un visiteur peut s'inscrire, se connecter et se déconnecter.
- [ ] Les mots de passe sont hachés avec `password_hash`.
- [ ] Un utilisateur non connecté est redirigé depuis toutes les pages privées.
- [ ] Un utilisateur ne peut ni voir ni modifier les données d'un autre (testé).
- [ ] Toutes les requêtes SQL sont préparées.
- [ ] Toutes les données affichées sont échappées avec `e()`.
- [ ] Tous les formulaires `POST` contiennent et vérifient un jeton CSRF.
- [ ] Les erreurs de validation s'affichent par champ, avec réaffichage des valeurs saisies.
- [ ] Les actions réussies redirigent (Post / Redirect / Get) et affichent un message flash.
- [ ] L'API renvoie du JSON avec les codes `200`, `401` et `404` appropriés.
- [ ] Au moins 18 tests passent avec `composer test`, avec un code de sortie `0`.
- [ ] Seul `public/` est exposé ; `.env` et la base ne sont pas versionnés.
- [ ] Le README permet à une personne extérieure de lancer le projet en cinq minutes.

## Atelier guidé : revue finale et extensions

Compte une heure pour la revue, puis choisis une extension.

1. Relis ton code avec les yeux d'un attaquant : essaie `<script>` dans chaque champ, modifie un `id` dans l'URL, supprime le jeton CSRF avec les outils du navigateur.
2. Lance `php -l` sur chaque fichier pour détecter les erreurs de syntaxe : `find src -name '*.php' -exec php -l {} \;`.
3. Repère les fonctions de plus de trente lignes et découpe-les.
4. Mesure ta couverture à la main : liste les règles métier et vérifie qu'un test existe pour chacune.
5. Choisis une extension : export CSV des séances, pagination de la liste, filtre par niveau, objectif hebdomadaire, ou une limite de tentatives de connexion.
6. Écris le test **avant** l'extension, vois-le échouer, puis implémente jusqu'à ce qu'il passe.

Pour t'auto-évaluer : explique à quelqu'un, en cinq minutes, ce qui se passe entre le clic sur « Enregistrer » et l'affichage du message flash : requête, routeur, vérification CSRF, validation, dépôt, redirection. Si tu y arrives, tu comprends le cycle de vie d'une requête dans n'importe quel framework.

## Erreurs fréquentes

- **Oublier le filtre `utilisateur_id`** dans une requête : un utilisateur accède aux données d'un autre.
- **Oublier `exit` ou `return` après une redirection** : le code continue de s'exécuter.
- **Mettre de la logique métier dans les templates ou les contrôleurs** : elle devient impossible à tester.
- **Casser la casse des fichiers** : `sujet.php` au lieu de `Sujet.php` fonctionne sous Windows mais pas en production.
- **Commencer par l'interface** au lieu du domaine et des tests : tu réécris tout ensuite.
- **Oublier `PRAGMA foreign_keys = ON`** : SQLite ignore alors les clés étrangères et les suppressions en cascade.
- **Tester uniquement le cas nominal** : les bugs vivent dans les cas limites.

## Bonnes pratiques

- Avance par petites étapes et commite après chacune, avec des messages clairs.
- Sépare les responsabilités : contrôleur (HTTP), service (règles métier), dépôt (SQL), vue (affichage).
- Injecte les dépendances par le constructeur, en un seul endroit (le point d'entrée).
- Écris d'abord les tests des règles critiques : validation, autorisation, calculs.
- Centralise la sécurité (CSRF, échappement, authentification) pour ne rien oublier.
- Documente les choix non évidents dans le README.
- Relis ton code le lendemain : un code lisible vaut mieux qu'un code astucieux.

## À retenir

- Un projet PHP propre repose sur Composer, PSR-4, un point d'entrée unique et un dossier `public/` isolé.
- Les couches (domaine, dépôts, services, contrôleurs, vues) rendent chaque morceau testable et remplaçable.
- La sécurité est un ensemble de réflexes systématiques : requêtes préparées, échappement, CSRF, hachage, isolation des données.
- Les tests protègent les règles métier, et celui de l'isolation entre utilisateurs est l'un des plus importants.
- Tu viens de reconstruire, à petite échelle, ce que Laravel automatise : tu sauras donc l'utiliser et le déboguer avec une vraie compréhension.
- Ce projet, versionné et documenté, est une pièce solide pour ton portfolio.
