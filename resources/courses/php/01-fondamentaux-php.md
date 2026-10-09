---
title: Fondamentaux PHP
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

PHP fait tourner une immense partie du web : WordPress, Laravel, Symfony, et la quasi-totalité des hébergements mutualisés. C'est le langage qui se cache derrière DevRoad. Avant d'utiliser un framework, il faut comprendre le langage lui-même : sa syntaxe, ses types, ses structures de contrôle. Ce chapitre pose ces fondations avec PHP 8.3.

À la fin du chapitre, tu seras capable de :

- installer PHP et exécuter un script en ligne de commande ;
- écrire des variables, des constantes et des commentaires ;
- distinguer les types scalaires (`int`, `float`, `string`, `bool`) et `null` ;
- manipuler des chaînes avec l'interpolation et les fonctions courantes ;
- écrire des conditions (`if`, `match`) et des boucles (`for`, `while`, `foreach`) ;
- comprendre le typage strict avec `declare(strict_types=1)` ;
- lancer un petit serveur de développement avec `php -S`.

Prérequis : savoir ouvrir un terminal et un éditeur de code. Aucune connaissance de PHP n'est nécessaire, mais avoir déjà vu une variable et une boucle dans un autre langage (JavaScript par exemple) aide beaucoup. Prévois deux heures.

## Installer PHP et lancer un premier script

Vérifie d'abord si PHP est présent :

```bash
php -v
```

Si la commande n'existe pas, installe PHP 8.3 : XAMPP ou Laragon sous Windows, `brew install php` sous macOS, ou `sudo apt install php8.3-cli` sous Ubuntu. Tu dois voir une ligne commençant par `PHP 8.3`.

Crée un dossier `php-cours` et, dedans, un fichier `bonjour.php` :

```php
<?php

echo "Bonjour DevRoad !\n";
```

Exécute-le :

```bash
php bonjour.php
```

Quelques remarques importantes :

- le fichier commence par la balise `<?php`. Dans un fichier qui ne contient que du PHP, on **n'écrit pas** la balise fermante `?>` ;
- chaque instruction se termine par un point-virgule `;` ;
- `echo` affiche du texte ; `\n` est un retour à la ligne ;
- PHP s'exécute **côté serveur** : le navigateur ne voit jamais ton code, seulement le résultat.

> **Astuce** : en développement, `php -S localhost:8000` lance un serveur web minimal dans le dossier courant. Ouvre ensuite `http://localhost:8000/bonjour.php` dans ton navigateur. Pas besoin d'Apache pour apprendre.

## Variables, constantes et commentaires

En PHP, une variable commence par le signe dollar `$`. Elle n'a pas besoin d'être déclarée avec un mot-clé comme `let` ou `const` :

```php
<?php

$prenom = "Awa";
$age = 24;
$note = 15.5;
$estInscrit = true;
$adresse = null;

echo $prenom;
```

Les noms sont **sensibles à la casse** : `$Prenom` et `$prenom` sont deux variables différentes. Par convention on écrit en `camelCase` : `$nombreDeLecons`. Un nom ne peut pas commencer par un chiffre.

Pour une valeur qui ne changera jamais, utilise une **constante** :

```php
const TVA = 0.18;
define('NOM_SITE', 'DevRoad');

echo NOM_SITE . " applique une TVA de " . (TVA * 100) . " %\n";
```

`const` est la forme moderne ; `define()` reste utile pour des noms calculés. Le point `.` sert à **concaténer** (coller) des chaînes. Une constante s'écrit en MAJUSCULES et sans `$`.

Les commentaires aident le lecteur, y compris toi dans six mois :

```php
// Commentaire sur une ligne
# Autre forme, moins courante

/*
 * Commentaire
 * sur plusieurs lignes
 */
```

## Les types de base

PHP possède quatre types **scalaires** et un type « absence de valeur » :

| Type | Exemple | Usage |
| --- | --- | --- |
| `int` | `42`, `-7` | Nombres entiers |
| `float` | `3.14` | Nombres décimaux |
| `string` | `"texte"` | Chaînes de caractères |
| `bool` | `true`, `false` | Vrai ou faux |
| `null` | `null` | Aucune valeur |

La fonction `var_dump()` affiche le type et la valeur : c'est ton meilleur ami pour déboguer.

```php
var_dump(42);        // int(42)
var_dump(3.14);      // float(3.14)
var_dump("42");      // string(2) "42"
var_dump(true);      // bool(true)
var_dump(null);      // NULL
```

Attention à la différence entre `42` (entier) et `"42"` (chaîne). PHP est un langage **à typage dynamique** : il convertit parfois automatiquement d'un type à l'autre, ce qu'on appelle le *type juggling*. `"5" + 3` donne `8`. Pratique, mais source de bugs. On verra plus bas comment se protéger.

### Opérateurs

```php
$a = 10;
$b = 3;

echo $a + $b;   // 13
echo $a - $b;   // 7
echo $a * $b;   // 30
echo $a / $b;   // 3.3333333333333
echo $a % $b;   // 1 (reste de la division)
echo $a ** $b;  // 1000 (puissance)
intdiv($a, $b); // 3 (division entière)
```

Pour comparer, PHP a deux familles d'opérateurs :

- `==` compare les **valeurs** après conversion de type : `0 == "0"` est vrai ;
- `===` compare la valeur **et** le type : `0 === "0"` est faux.

Utilise presque toujours `===` et `!==`. Les comparaisons laxistes ont causé de nombreuses failles de sécurité.

:::quiz
Quel est le résultat de `var_dump(5 === "5");` ?
- [ ] bool(true)
- [x] bool(false)
- [ ] int(5)
- [ ] Une erreur fatale
> L'opérateur `===` compare aussi le type : `5` est un entier, `"5"` est une chaîne, donc le résultat est `false`. Avec `==`, le résultat serait `true`.
:::

## Travailler avec les chaînes

PHP distingue deux types de guillemets :

```php
$langage = "PHP";

echo 'Je programme en $langage\n';   // texte brut : $langage\n
echo "Je programme en $langage\n";   // interpolation : Je programme en PHP
echo "Version : {$langage}8.3\n";    // accolades pour délimiter la variable
```

Les **guillemets doubles** interprètent les variables et les séquences comme `\n`. Les **guillemets simples** affichent le texte tel quel. Pour afficher une propriété ou une case de tableau, les accolades évitent les ambiguïtés.

Quelques fonctions de chaînes que tu utiliseras tous les jours :

```php
$titre = "  Roadmap Laravel  ";

echo strlen($titre);              // 20 (octets)
echo trim($titre);                // "Roadmap Laravel"
echo strtoupper($titre);          // "  ROADMAP LARAVEL  "
echo str_replace('Laravel', 'PHP', $titre);
echo substr("DevRoad", 0, 3);     // "Dev"
echo str_contains($titre, 'Laravel') ? 'oui' : 'non';
echo ucfirst("bonjour");          // "Bonjour"
```

Pour les textes avec accents (essentiel en français), préfère les versions « multi-octets » : `mb_strlen("été")` renvoie 3, alors que `strlen("été")` renvoie 5 car chaque accent occupe deux octets en UTF-8.

> **Attention** : `strlen`, `strtoupper` et `substr` ne gèrent pas correctement l'UTF-8. Pour du texte français, utilise `mb_strlen`, `mb_strtoupper` et `mb_substr`.

## Les conditions

### if, elseif, else

```php
$note = 14;

if ($note >= 16) {
    echo "Très bien";
} elseif ($note >= 12) {
    echo "Bien";
} elseif ($note >= 10) {
    echo "Passable";
} else {
    echo "À retravailler";
}
```

Combine les conditions avec `&&` (et), `||` (ou) et `!` (non).

### L'opérateur ternaire et la coalescence

```php
$statut = $note >= 10 ? "admis" : "refusé";

// ?? renvoie la valeur de droite si celle de gauche est null ou absente
$pseudo = $_GET['pseudo'] ?? "anonyme";
```

L'opérateur `??` (*null coalescing*) est l'un des plus utiles de PHP : il évite une erreur quand une clé n'existe pas.

### match, le switch moderne

Depuis PHP 8, `match` remplace avantageusement `switch` : il renvoie une valeur, compare en `===` et n'a pas besoin de `break`.

```php
$niveau = "intermediate";

$libelle = match ($niveau) {
    "beginner" => "Débutant",
    "intermediate" => "Intermédiaire",
    "professional" => "Professionnel",
    default => "Inconnu",
};

echo $libelle; // Intermédiaire
```

Si aucune branche ne correspond et qu'il n'y a pas de `default`, PHP lève une erreur `UnhandledMatchError` : c'est volontaire, cela t'empêche d'oublier un cas.

:::quiz
Que fait l'opérateur `??` dans `$x = $valeur ?? "défaut";` ?
- [ ] Il compare deux valeurs en ignorant le type
- [ ] Il convertit `$valeur` en chaîne
- [x] Il utilise "défaut" seulement si `$valeur` est null ou n'existe pas
- [ ] Il lève une exception si `$valeur` est vide
> `??` est l'opérateur de coalescence : il renvoie la valeur de gauche si elle existe et n'est pas `null`, sinon celle de droite. Une chaîne vide `""` ou le chiffre `0` sont conservés.
:::

## Les boucles

```php
// for : quand on connaît le nombre de tours
for ($i = 1; $i <= 3; $i++) {
    echo "Leçon $i\n";
}

// while : tant qu'une condition est vraie
$tentatives = 0;
while ($tentatives < 3) {
    $tentatives++;
}

// foreach : pour parcourir un tableau (le plus fréquent)
$technos = ["PHP", "Laravel", "React"];
foreach ($technos as $techno) {
    echo "- $techno\n";
}
```

`break` sort de la boucle, `continue` passe au tour suivant. Nous reviendrons sur `foreach` et les tableaux au chapitre suivant ; retiens pour l'instant qu'il est préférable à `for` dès que tu parcours une liste.

## Un script complet : calculer une moyenne

Mettons le tout ensemble dans un petit programme :

```php
<?php

declare(strict_types=1);

$notes = [12, 15.5, 9, 18];
$somme = 0;

foreach ($notes as $note) {
    $somme += $note;
}

$moyenne = $somme / count($notes);

$mention = match (true) {
    $moyenne >= 16 => "Très bien",
    $moyenne >= 14 => "Bien",
    $moyenne >= 10 => "Passable",
    default => "Insuffisant",
};

echo "Moyenne : " . round($moyenne, 2) . " (" . $mention . ")\n";
```

`match (true)` est une astuce courante pour écrire des conditions en cascade de façon lisible.

## Le typage strict

Remarque la deuxième ligne : `declare(strict_types=1);`. Elle doit être la **première instruction** du fichier, juste après `<?php`. Elle demande à PHP de **refuser les conversions silencieuses** lors de l'appel de fonctions :

```php
<?php

declare(strict_types=1);

function doubler(int $nombre): int
{
    return $nombre * 2;
}

echo doubler(4);      // 8
echo doubler("4");    // TypeError : "4" est une chaîne, pas un int
```

Sans le mode strict, PHP aurait converti `"4"` en `4` sans rien dire. Avec lui, l'erreur apparaît immédiatement, au bon endroit. Les types déclarés (`int $nombre`, `: int`) documentent aussi ton code. Prends l'habitude d'activer le mode strict dans **tous** tes fichiers.

:::quiz
Où doit se placer `declare(strict_types=1);` ?
- [ ] À la fin du fichier
- [ ] Dans chaque fonction
- [x] Comme première instruction du fichier, juste après `<?php`
- [ ] Uniquement dans le fichier php.ini
> La directive s'applique au fichier où elle se trouve et doit être la toute première instruction. Elle impose des types stricts pour les appels de fonctions faits depuis ce fichier.
:::

## Atelier guidé : un convertisseur FCFA

Compte une heure. Tu vas écrire un script `convertisseur.php` qui convertit des montants en FCFA.

1. Crée le fichier avec `<?php` et `declare(strict_types=1);`.
2. Déclare une constante `TAUX_EUR` valant `655.957` (1 euro en FCFA).
3. Crée trois variables : `$montantFcfa` (un entier, par exemple `50000`), `$client` (une chaîne) et `$estVip` (un booléen).
4. Calcule le montant en euros avec une division et arrondis-le à deux décimales avec `round()`.
5. Affiche une phrase avec interpolation : « Awa doit 76.22 EUR (50000 FCFA). ».
6. Si le client est VIP, applique une remise de 10 % avec un `if`, sinon aucune remise.
7. Utilise `match (true)` pour afficher une catégorie : moins de 10 000 FCFA « petit montant », moins de 100 000 « moyen », sinon « gros montant ».
8. Remplace la valeur unique par un tableau de trois montants et affiche chaque ligne avec un `foreach`.
9. Lance ton script avec `php convertisseur.php`, puis avec `php -S localhost:8000` et observe la différence de sortie dans le navigateur.

Pour t'auto-évaluer : explique la différence entre `==` et `===`, entre `'...'` et `"..."`, et dis ce que fait le mode strict. Si tu peux répondre sans relire, tu as acquis l'essentiel.

## Erreurs fréquentes

- **Oublier le `$`** devant un nom de variable : `prenom = "Awa";` provoque une erreur de syntaxe.
- **Oublier le point-virgule** : l'erreur est signalée à la ligne suivante, ce qui trompe.
- **Utiliser `==` au lieu de `===`** : `"abc" == 0` valait vrai avant PHP 8, et d'autres comparaisons surprenantes existent encore.
- **Confondre `=` et `==`** : `if ($a = 5)` affecte la valeur au lieu de la comparer.
- **Utiliser `strlen` sur du texte accentué** : le résultat est faux, préfère `mb_strlen`.
- **Lire une variable non définie** : PHP émet un avertissement `Undefined variable`. Initialise toujours tes variables ou utilise `??`.
- **Placer du texte avant `<?php`** (même un espace) : cela peut casser les en-têtes HTTP plus tard.

## Bonnes pratiques

- Active `declare(strict_types=1);` dans chaque fichier et type tes paramètres.
- Utilise `===` et `!==` par défaut.
- Nomme les variables en `camelCase`, les constantes en `MAJUSCULES`.
- Préfère `foreach` pour parcourir des tableaux et `match` à `switch`.
- Utilise `??` pour fournir une valeur par défaut plutôt que de tester avec `isset` partout.
- Pour du texte français, utilise les fonctions `mb_*`.
- Utilise `var_dump()` pour comprendre ce qu'il se passe, puis supprime-le avant de livrer.
- Écris des scripts courts et exécute-les souvent : l'erreur est plus facile à localiser.

## À retenir

- PHP s'exécute côté serveur ; un script se lance avec `php fichier.php` ou via `php -S`.
- Les variables commencent par `$`, les instructions finissent par `;`, la balise fermante est inutile dans un fichier PHP pur.
- Les types de base sont `int`, `float`, `string`, `bool` et `null` ; `var_dump()` les révèle.
- `===` compare valeur et type ; `??` fournit une valeur par défaut.
- `match` est une version moderne, stricte et sans `break` du `switch`.
- `foreach` est la boucle de référence pour parcourir une liste.
- `declare(strict_types=1);` évite les conversions silencieuses et rend le code plus sûr.
