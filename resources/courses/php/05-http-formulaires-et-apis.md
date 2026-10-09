---
title: HTTP, formulaires et APIs
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

PHP est né pour répondre à des requêtes web. Pour construire un site ou une API, tu dois comprendre ce qui circule entre le navigateur et le serveur : les requêtes, les réponses, les formulaires, les sessions, et les données JSON. Tu dois aussi savoir stocker des données avec **PDO**, l'interface standard de PHP vers les bases de données. Ce chapitre rassemble ces briques, qui sont exactement celles que Laravel te cache derrière ses abstractions.

À la fin du chapitre, tu seras capable de :

- expliquer le cycle requête / réponse HTTP, les méthodes et les codes de statut ;
- lire les données d'une requête avec `$_GET`, `$_POST` et `$_SERVER` ;
- traiter un formulaire HTML avec validation et affichage des erreurs ;
- utiliser les sessions et les cookies ;
- te connecter à une base avec **PDO** et exécuter des requêtes préparées ;
- écrire un petit endpoint JSON avec les bons en-têtes et codes de statut ;
- appeler une API externe en PHP.

Prérequis : les chapitres précédents, notions de HTML (formulaires) et de SQL de base (`SELECT`, `INSERT`). Prévois deux heures et demie.

## Le cycle HTTP

Quand tu ouvres `http://localhost:8000/roadmaps?niveau=beginner`, le navigateur envoie une **requête** et le serveur renvoie une **réponse**.

```text
GET /roadmaps?niveau=beginner HTTP/1.1
Host: localhost:8000
Accept: text/html

HTTP/1.1 200 OK
Content-Type: text/html; charset=utf-8

<html>...</html>
```

Une requête comporte une **méthode**, un **chemin**, des **en-têtes** et éventuellement un **corps**. Les méthodes principales :

| Méthode | Usage | Corps |
| --- | --- | --- |
| `GET` | Lire une ressource | Non |
| `POST` | Créer une ressource, envoyer un formulaire | Oui |
| `PUT` / `PATCH` | Modifier | Oui |
| `DELETE` | Supprimer | Rarement |

Les codes de statut de la réponse t'indiquent le résultat :

- `200` OK, `201` créé, `204` succès sans contenu ;
- `301` / `302` redirection ;
- `400` requête invalide, `401` non authentifié, `403` interdit, `404` introuvable, `422` données invalides ;
- `500` erreur du serveur.

Une règle d'or : `GET` ne doit **jamais** modifier de données. Un robot ou un préchargement pourrait déclencher la requête sans que l'utilisateur l'ait voulu.

## Lire une requête en PHP

PHP expose la requête à travers des **superglobales** :

```php
<?php

declare(strict_types=1);

$methode = $_SERVER['REQUEST_METHOD'];                // GET, POST...
$chemin  = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$niveau  = $_GET['niveau'] ?? null;                   // ?niveau=beginner
$titre   = $_POST['titre'] ?? '';                     // champ de formulaire

echo "$methode $chemin";
```

- `$_GET` contient les paramètres de l'URL ;
- `$_POST` contient les champs d'un formulaire envoyé en `POST` ;
- `$_SERVER` contient la méthode, l'URI, les en-têtes ;
- `$_FILES` contient les fichiers téléversés ;
- `$_COOKIE` et `$_SESSION` contiennent les cookies et la session.

Toutes les valeurs de `$_GET` et `$_POST` sont des **chaînes** venues de l'extérieur : traite-les toujours comme non fiables.

> **Attention** : ne fais jamais confiance à une donnée de la requête. Un utilisateur peut envoyer n'importe quoi, y compris des champs que ton formulaire ne propose pas. Valide chaque entrée.

## Un formulaire complet : ajouter une roadmap

Voici un formulaire qui s'envoie à lui-même. Le principe : afficher le formulaire en `GET`, traiter en `POST`, rediriger en cas de succès.

```php
<?php

declare(strict_types=1);

session_start();

$erreurs = [];
$anciennes = ['titre' => '', 'minutes' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titre   = trim((string) ($_POST['titre'] ?? ''));
    $minutes = filter_var($_POST['minutes'] ?? '', FILTER_VALIDATE_INT);
    $anciennes = ['titre' => $titre, 'minutes' => $_POST['minutes'] ?? ''];

    if ($titre === '' || mb_strlen($titre) > 120) {
        $erreurs['titre'] = "Le titre est obligatoire (120 caractères maximum).";
    }
    if ($minutes === false || $minutes < 30 || $minutes > 600) {
        $erreurs['minutes'] = "La durée doit être un entier entre 30 et 600.";
    }

    if ($erreurs === []) {
        // ici : enregistrement en base (voir plus bas)
        $_SESSION['flash'] = "Roadmap « $titre » enregistrée.";
        header('Location: /');
        exit;
    }
}
?>
<!doctype html>
<html lang="fr">
<head><meta charset="utf-8"><title>Nouvelle roadmap</title></head>
<body>
<form method="post" action="">
    <label for="titre">Titre</label>
    <input id="titre" name="titre" value="<?= htmlspecialchars($anciennes['titre']) ?>">
    <?php if (isset($erreurs['titre'])): ?>
        <p role="alert"><?= htmlspecialchars($erreurs['titre']) ?></p>
    <?php endif; ?>

    <label for="minutes">Durée (minutes)</label>
    <input id="minutes" name="minutes" value="<?= htmlspecialchars((string) $anciennes['minutes']) ?>">
    <?php if (isset($erreurs['minutes'])): ?>
        <p role="alert"><?= htmlspecialchars($erreurs['minutes']) ?></p>
    <?php endif; ?>

    <button type="submit">Enregistrer</button>
</form>
</body>
</html>
```

Plusieurs réflexes à retenir dans cet exemple :

- **valider côté serveur** : la validation HTML (`required`) est un confort, pas une protection ;
- `filter_var(..., FILTER_VALIDATE_INT)` vérifie qu'une chaîne est bien un entier ;
- `htmlspecialchars()` échappe tout ce qui est affiché dans la page : c'est ta protection contre les failles XSS ;
- on **réaffiche** les anciennes valeurs pour ne pas faire retaper l'utilisateur ;
- après un `POST` réussi, on **redirige** (`header('Location: ...'); exit;`) : c'est le motif *Post / Redirect / Get*, qui évite qu'un rafraîchissement renvoie le formulaire une deuxième fois.

La balise courte `<?= ... ?>` est un raccourci pour `<?php echo ... ?>`.

:::quiz
Pourquoi redirige-t-on l'utilisateur après un traitement de formulaire réussi ?
- [ ] Pour accélérer la page
- [x] Pour éviter que le rafraîchissement de la page renvoie à nouveau le formulaire
- [ ] Parce que PHP l'exige
- [ ] Pour supprimer les cookies
> Après un POST, un simple rafraîchissement redemande la même requête POST. En redirigeant vers une page affichée en GET (motif Post / Redirect / Get), on évite les doublons.
:::

## Sessions et cookies

HTTP est **sans état** : le serveur ne se souvient pas de toi d'une requête à l'autre. Les **cookies** (petites données stockées dans le navigateur) et les **sessions** (données stockées côté serveur, identifiées par un cookie) résolvent ce problème.

```php
<?php

declare(strict_types=1);

session_start([
    'cookie_httponly' => true,
    'cookie_samesite' => 'Lax',
]);

$_SESSION['visites'] = ($_SESSION['visites'] ?? 0) + 1;
echo "Visite numéro " . $_SESSION['visites'];

// Pour déconnecter :
// session_destroy();
```

`session_start()` doit être appelé **avant** toute sortie. Le cookie de session reçoit l'option `httponly` pour être inaccessible au JavaScript. Les sessions servent à garder un utilisateur connecté, à mémoriser un panier, ou à transmettre un message *flash* (affiché une fois puis supprimé) comme dans l'exemple précédent.

## Stocker des données avec PDO

**PDO** (*PHP Data Objects*) est une couche d'accès commune à MySQL, PostgreSQL, SQLite... Pour apprendre sans rien installer, utilisons SQLite, un fichier unique :

```php
<?php

declare(strict_types=1);

$pdo = new PDO('sqlite:' . __DIR__ . '/devroad.sqlite', options: [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$pdo->exec('CREATE TABLE IF NOT EXISTS roadmaps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    titre TEXT NOT NULL,
    minutes INTEGER NOT NULL
)');
```

Avec MySQL, seule la chaîne de connexion change : `mysql:host=127.0.0.1;dbname=devroad;charset=utf8mb4`, plus un utilisateur et un mot de passe.

Les deux options sont essentielles : `ERRMODE_EXCEPTION` fait lever une exception en cas d'erreur SQL (au lieu d'échouer en silence), et `FETCH_ASSOC` renvoie les lignes sous forme de tableaux associatifs.

### Les requêtes préparées

Ne concatène **jamais** une donnée utilisateur dans une requête SQL :

```php
// DANGEREUX : injection SQL
$pdo->query("SELECT * FROM roadmaps WHERE titre = '$titre'");
// avec $titre = "' OR '1'='1", la requête renvoie toute la table
```

Utilise une **requête préparée** : la requête et les valeurs voyagent séparément, donc les valeurs ne sont jamais interprétées comme du SQL.

```php
// Insertion
$stmt = $pdo->prepare('INSERT INTO roadmaps (titre, minutes) VALUES (:titre, :minutes)');
$stmt->execute(['titre' => $titre, 'minutes' => $minutes]);
$id = (int) $pdo->lastInsertId();

// Lecture de plusieurs lignes
$stmt = $pdo->prepare('SELECT * FROM roadmaps WHERE minutes >= :min ORDER BY titre');
$stmt->execute(['min' => 120]);
$roadmaps = $stmt->fetchAll();

// Lecture d'une ligne
$stmt = $pdo->prepare('SELECT * FROM roadmaps WHERE id = :id');
$stmt->execute(['id' => 1]);
$roadmap = $stmt->fetch();   // false si absente
```

C'est exactement ce que fait Eloquent à ta place. Savoir ce qui se passe dessous t'aide à comprendre les erreurs et les lenteurs.

Pour plusieurs écritures qui doivent réussir ensemble, utilise une **transaction** :

```php
$pdo->beginTransaction();
try {
    $pdo->prepare('INSERT INTO roadmaps (titre, minutes) VALUES (?, ?)')->execute(['A', 60]);
    $pdo->prepare('INSERT INTO roadmaps (titre, minutes) VALUES (?, ?)')->execute(['B', 90]);
    $pdo->commit();
} catch (Throwable $e) {
    $pdo->rollBack();
    throw $e;
}
```

:::quiz
Quelle est la bonne manière d'insérer une valeur saisie par l'utilisateur dans une requête SQL ?
- [ ] La concaténer dans la chaîne avec le point
- [ ] La passer dans addslashes() puis la concaténer
- [x] Utiliser une requête préparée avec des paramètres liés
- [ ] La vérifier en JavaScript avant l'envoi
> Les requêtes préparées séparent la structure SQL des données, ce qui neutralise l'injection SQL. Les autres méthodes sont fragiles ou contournables.
:::

## Écrire un endpoint JSON

Une API renvoie des données, généralement en JSON, plutôt que du HTML. Voici un fichier `api.php` qui liste les roadmaps (`GET`) et en crée une (`POST`) :

```php
<?php

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');

function reponse(int $statut, array $donnees): never
{
    http_response_code($statut);
    echo json_encode($donnees, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/devroad.sqlite', options: [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
]);

$methode = $_SERVER['REQUEST_METHOD'];

if ($methode === 'GET') {
    $lignes = $pdo->query('SELECT id, titre, minutes FROM roadmaps ORDER BY id DESC')->fetchAll();
    reponse(200, ['data' => $lignes]);
}

if ($methode === 'POST') {
    $corps = json_decode(file_get_contents('php://input') ?: '', true);

    if (!is_array($corps)) {
        reponse(400, ['erreur' => 'JSON invalide']);
    }

    $titre   = trim((string) ($corps['titre'] ?? ''));
    $minutes = filter_var($corps['minutes'] ?? null, FILTER_VALIDATE_INT);

    if ($titre === '' || $minutes === false) {
        reponse(422, ['erreur' => 'Données invalides', 'champs' => ['titre', 'minutes']]);
    }

    $stmt = $pdo->prepare('INSERT INTO roadmaps (titre, minutes) VALUES (?, ?)');
    $stmt->execute([$titre, $minutes]);

    reponse(201, ['data' => ['id' => (int) $pdo->lastInsertId(), 'titre' => $titre, 'minutes' => $minutes]]);
}

reponse(405, ['erreur' => 'Méthode non autorisée']);
```

Détails à noter :

- `Content-Type: application/json` indique au client le format ;
- un corps JSON ne passe pas par `$_POST` : on lit le flux brut `php://input` puis on le décode avec `json_decode` ;
- le type de retour `never` indique que la fonction termine toujours le script ;
- on choisit le **bon code de statut** : `201` pour une création, `422` pour des données invalides, `405` pour une méthode non gérée.

Teste avec `curl` :

```bash
php -S localhost:8000
curl -X POST localhost:8000/api.php -H "Content-Type: application/json" \
  -d '{"titre":"Docker","minutes":90}'
curl localhost:8000/api.php
```

## Appeler une API externe

Pour consommer une API, `file_get_contents` suffit pour un cas simple ; cURL donne plus de contrôle :

```php
function appelerApi(string $url): array
{
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 5,
        CURLOPT_HTTPHEADER => ['Accept: application/json'],
    ]);

    $corps = curl_exec($ch);
    $statut = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($corps === false || $statut >= 400) {
        throw new RuntimeException("Appel échoué (statut $statut)");
    }

    return json_decode($corps, true, flags: JSON_THROW_ON_ERROR);
}
```

Définis toujours un **délai maximal** (`CURLOPT_TIMEOUT`) : sans lui, une API lente bloque ta page. Dans un vrai projet, on utilise souvent la bibliothèque Guzzle ou le client HTTP de Laravel, installés avec Composer.

## Atelier guidé : un mini-gestionnaire de roadmaps

Compte une heure et demie. Tu vas relier formulaire, base et API.

1. Crée un projet `roadmaps-web` avec les fichiers `index.php`, `nouvelle.php`, `api.php` et `db.php`.
2. Dans `db.php`, crée une fonction `connexion(): PDO` qui ouvre la base SQLite, active les exceptions, crée la table si elle n'existe pas et renvoie l'objet.
3. Dans `nouvelle.php`, reprends le formulaire du cours : validation, anciennes valeurs, échappement, enregistrement avec requête préparée.
4. Après un enregistrement réussi, stocke un message flash dans la session et redirige vers `index.php`.
5. Dans `index.php`, affiche le message flash (puis supprime-le de la session) et liste les roadmaps dans un tableau HTML, avec `htmlspecialchars` partout.
6. Ajoute un filtre `?niveau=...` ou `?min=...` sur la liste, avec un paramètre lié dans la requête.
7. Dans `api.php`, écris les routes `GET` et `POST` du cours, puis ajoute `DELETE` avec `?id=3` renvoyant `204`.
8. Teste chaque route avec `curl` et vérifie les codes `200`, `201`, `204`, `400`, `422` et `405`.
9. Essaie volontairement d'enregistrer le titre `<script>alert(1)</script>` et vérifie qu'il s'affiche comme du texte, sans s'exécuter.

Pour t'auto-évaluer : peux-tu expliquer à quoi servent `htmlspecialchars`, les requêtes préparées et la redirection après POST ? Chacune protège contre un problème différent.

## Erreurs fréquentes

- **Afficher une donnée sans `htmlspecialchars`** : faille XSS, un script injecté s'exécute chez tes visiteurs.
- **Concaténer des variables dans le SQL** : injection SQL.
- **Appeler `header()` ou `session_start()` après avoir envoyé du texte** : l'erreur « headers already sent » apparaît. Aucune sortie avant ces appels.
- **Oublier `exit` après `header('Location: ...')`** : le script continue de s'exécuter.
- **Lire du JSON dans `$_POST`** : il reste vide, utilise `php://input`.
- **Renvoyer `200` pour une erreur** : le client ne peut pas distinguer succès et échec. Utilise les codes adaptés.
- **Ne pas définir de délai d'attente** lors d'un appel d'API externe.

## Bonnes pratiques

- Valide toutes les entrées côté serveur, avec des messages d'erreur clairs par champ.
- Échappe en sortie avec `htmlspecialchars($valeur, ENT_QUOTES)` dans les vues.
- N'utilise que des requêtes préparées pour parler à la base.
- Active `PDO::ERRMODE_EXCEPTION` et gère les exceptions plutôt que d'ignorer les erreurs.
- Applique le motif Post / Redirect / Get après chaque modification.
- Renvoie des codes de statut et un format d'erreur cohérents dans tes APIs.
- Garde les identifiants de base de données dans un fichier `.env`, hors du dépôt.
- Sépare l'accès aux données, la logique et l'affichage dans des fichiers ou des classes distinctes.

## À retenir

- Une requête HTTP a une méthode, un chemin, des en-têtes et un corps ; la réponse porte un code de statut.
- `$_GET`, `$_POST`, `$_SERVER`, `$_SESSION` donnent accès à la requête, mais leurs valeurs ne sont jamais fiables.
- Un formulaire robuste valide côté serveur, ré-affiche les valeurs, échappe la sortie et redirige après succès.
- Les sessions donnent de la mémoire à un protocole sans état.
- PDO avec requêtes préparées est la manière sûre de dialoguer avec une base de données.
- Une API JSON définit `Content-Type`, lit `php://input`, renvoie les bons statuts (`201`, `422`, `405`...).
- Un appel à une API externe exige un délai maximal et une gestion des erreurs.
