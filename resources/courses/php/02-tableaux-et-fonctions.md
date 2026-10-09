---
title: Tableaux et fonctions
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Presque tout ce que manipule une application web est une collection : une liste de roadmaps, les champs d'un formulaire, les lignes d'une table SQL. En PHP, cette collection s'appelle un **tableau** (*array*). Les fonctions, elles, te permettent de nommer un morceau de logique pour le réutiliser. Ensemble, ces deux outils couvrent l'essentiel du travail quotidien.

À la fin du chapitre, tu seras capable de :

- créer des tableaux indexés et associatifs, les lire et les modifier ;
- parcourir un tableau avec `foreach` et manipuler des tableaux imbriqués ;
- utiliser les fonctions de tableaux courantes (`count`, `in_array`, `array_map`, `array_filter`, `usort`...) ;
- écrire des fonctions avec paramètres typés, valeurs par défaut et type de retour ;
- utiliser les arguments nommés, le spread `...` et les fonctions fléchées ;
- comprendre la portée des variables et les fonctions anonymes ;
- séparer ton code en plusieurs fichiers avec `require`.

Prérequis : le chapitre « Fondamentaux PHP » (variables, conditions, boucles). Prévois deux heures.

## Les tableaux indexés

Un tableau indexé est une liste ordonnée dont les positions commencent à **0** :

```php
<?php

declare(strict_types=1);

$technos = ["PHP", "Laravel", "React"];

echo $technos[0];        // PHP
echo $technos[2];        // React
echo count($technos);    // 3

$technos[] = "Docker";   // ajoute à la fin
$technos[1] = "Symfony"; // remplace la case 1
unset($technos[0]);      // supprime la case 0
```

Attention : `unset` laisse un « trou » dans les index. Pour réindexer, utilise `array_values($technos)`.

Quelques fonctions essentielles :

```php
$langages = ["PHP", "Python", "JS"];

array_push($langages, "Go");       // ajoute à la fin
array_pop($langages);              // retire le dernier
array_unshift($langages, "C");     // ajoute au début
array_shift($langages);            // retire le premier

in_array("PHP", $langages, true);  // true : le 3e argument active la comparaison stricte
array_search("Python", $langages); // 1 : l'index trouvé (ou false)
array_slice($langages, 0, 2);      // extrait 2 éléments à partir de 0
implode(", ", $langages);          // "PHP, Python, JS"
explode(",", "a,b,c");             // ["a", "b", "c"]
sort($langages);                   // trie sur place
```

## Les tableaux associatifs

Un tableau associatif relie des **clés** à des **valeurs**. C'est l'équivalent d'un objet JSON :

```php
$roadmap = [
    "titre" => "Apprendre PHP",
    "niveau" => "beginner",
    "minutes" => 120,
    "publiee" => true,
];

echo $roadmap["titre"];                 // Apprendre PHP
$roadmap["niveau"] = "intermediate";    // modification
$roadmap["auteur"] = "Smith";           // ajout

var_dump(isset($roadmap["auteur"]));    // true
echo $roadmap["inconnue"] ?? "n/a";     // n/a : pas d'erreur
```

Pour lire une clé qui peut ne pas exister, utilise `??`. Sinon PHP émet un avertissement `Undefined array key`.

### Parcourir avec clés et valeurs

```php
foreach ($roadmap as $cle => $valeur) {
    echo "$cle : " . var_export($valeur, true) . "\n";
}
```

`var_export` convertit n'importe quelle valeur en texte lisible, utile pour les booléens.

### Tableaux imbriqués

Les tableaux peuvent contenir d'autres tableaux. C'est ainsi qu'on représente un jeu de données :

```php
$roadmaps = [
    ["titre" => "Laravel", "minutes" => 180, "niveau" => "intermediate"],
    ["titre" => "React", "minutes" => 120, "niveau" => "beginner"],
    ["titre" => "Docker", "minutes" => 90, "niveau" => "intermediate"],
];

foreach ($roadmaps as $r) {
    echo "{$r['titre']} : {$r['minutes']} min\n";
}

echo $roadmaps[1]["titre"]; // React
```

Remarque l'écriture `{$r['titre']}` : les accolades sont nécessaires pour lire une clé dans une chaîne entre guillemets doubles.

:::quiz
Que vaut `$a["x"] ?? 0` si la clé `"x"` n'existe pas dans `$a` ?
- [ ] Une erreur fatale
- [ ] null
- [x] 0
- [ ] false
> `??` renvoie la valeur de droite quand la clé est absente ou nulle, sans émettre d'avertissement. C'est la façon propre de fournir une valeur par défaut.
:::

## Transformer des tableaux

PHP offre des fonctions pour transformer un tableau sans écrire de boucle explicite :

```php
$minutes = [180, 120, 90];

// array_map : applique une fonction à chaque élément
$heures = array_map(fn(int $m): float => $m / 60, $minutes);
// [3, 2, 1.5]

// array_filter : garde les éléments pour lesquels la fonction renvoie true
$longs = array_filter($minutes, fn(int $m): bool => $m >= 120);
// [0 => 180, 1 => 120]  (les clés d'origine sont conservées)

// array_sum / max / min
echo array_sum($minutes); // 390
echo max($minutes);       // 180

// array_column : extrait une colonne d'un tableau de tableaux
$titres = array_column($roadmaps, "titre");
// ["Laravel", "React", "Docker"]
```

Pour trier un tableau de tableaux selon un critère, utilise `usort` avec une fonction de comparaison. L'opérateur **spaceship** `<=>` renvoie -1, 0 ou 1 :

```php
usort($roadmaps, fn(array $a, array $b): int => $a["minutes"] <=> $b["minutes"]);
// du plus court au plus long
```

Pour obtenir un total par niveau, une boucle reste souvent la plus claire :

```php
$parNiveau = [];

foreach ($roadmaps as $r) {
    $parNiveau[$r["niveau"]] = ($parNiveau[$r["niveau"]] ?? 0) + $r["minutes"];
}
// ["intermediate" => 270, "beginner" => 120]
```

> **Astuce** : `print_r($tableau)` et `var_dump($tableau)` affichent la structure d'un tableau. Dans un navigateur, entoure-les de `<pre>` pour conserver la mise en forme.

## Les fonctions

Une fonction regroupe des instructions sous un nom. Elle reçoit des **paramètres** et renvoie une valeur avec `return`.

```php
function formaterDuree(int $minutes): string
{
    $heures = intdiv($minutes, 60);
    $reste = $minutes % 60;

    return $reste === 0 ? "{$heures} h" : "{$heures} h {$reste} min";
}

echo formaterDuree(150); // 2 h 30 min
```

Prends l'habitude de typer chaque paramètre et le retour : `int $minutes`, `: string`. Le mot `void` indique qu'une fonction ne renvoie rien. Un type suivi de `?` accepte aussi `null` : `?string`.

### Valeurs par défaut et arguments nommés

```php
function saluer(string $prenom, string $salutation = "Bonjour", bool $crier = false): string
{
    $message = "$salutation $prenom";
    return $crier ? mb_strtoupper($message) . " !" : $message;
}

echo saluer("Awa");                          // Bonjour Awa
echo saluer("Awa", crier: true);             // BONJOUR AWA !
echo saluer(prenom: "Koffi", salutation: "Salut");
```

Les **arguments nommés** (PHP 8) rendent les appels lisibles, surtout quand une fonction a plusieurs paramètres optionnels. Les paramètres avec valeur par défaut se placent en dernier.

### Nombre d'arguments variable

L'opérateur `...` (spread) récolte plusieurs arguments dans un tableau, ou au contraire déplie un tableau en arguments :

```php
function total(int ...$montants): int
{
    return array_sum($montants);
}

echo total(100, 250, 50);          // 400
echo total(...[10, 20, 30]);       // 60
```

### Renvoyer plusieurs valeurs

Une fonction ne renvoie qu'une valeur, mais cette valeur peut être un tableau :

```php
function statistiques(array $notes): array
{
    return [
        "min" => min($notes),
        "max" => max($notes),
        "moyenne" => round(array_sum($notes) / count($notes), 2),
    ];
}

['min' => $min, 'max' => $max] = statistiques([12, 18, 9]);
echo "$min - $max"; // 9 - 18
```

La syntaxe de **déstructuration** `['min' => $min] = ...` extrait directement les valeurs voulues.

:::quiz
Quelle ligne appelle correctement `saluer(string $prenom, string $salutation = "Bonjour")` avec un argument nommé ?
- [ ] saluer("Awa", "Salut", salutation)
- [ ] saluer(salutation => "Salut", "Awa")
- [x] saluer("Awa", salutation: "Salut")
- [ ] saluer("Awa", $salutation = "Salut" ...)
> En PHP 8, on nomme un argument avec `nom: valeur`. Les arguments positionnels viennent d'abord, les nommés ensuite.
:::

## Portée des variables

Une variable créée dans une fonction n'existe que dans cette fonction. Contrairement à JavaScript, une fonction ne voit **pas** les variables du fichier :

```php
$taux = 0.18;

function ttc(float $ht): float
{
    return $ht * (1 + $taux); // Avertissement : $taux est indéfini ici
}
```

La bonne solution est de passer la valeur en paramètre :

```php
function ttc(float $ht, float $taux = 0.18): float
{
    return $ht * (1 + $taux);
}
```

Évite le mot-clé `global` : il crée des dépendances cachées et rend le code difficile à tester.

## Fonctions anonymes et fonctions fléchées

Une fonction peut être stockée dans une variable ou passée à une autre fonction. Une fonction anonyme (*closure*) n'accède aux variables extérieures que si tu les déclares avec `use` :

```php
$taux = 0.18;

$calculerTtc = function (float $ht) use ($taux): float {
    return $ht * (1 + $taux);
};

echo $calculerTtc(1000); // 1180
```

La **fonction fléchée** `fn` est une version courte qui capture automatiquement les variables extérieures (en lecture) :

```php
$calculerTtc = fn(float $ht): float => $ht * (1 + $taux);
```

Utilise `fn` pour les petites expressions, `function` pour les blocs de plusieurs lignes.

## Organiser son code en fichiers

Quand un fichier grossit, découpe-le. `require_once` inclut un autre fichier PHP une seule fois ; si le fichier est introuvable, le script s'arrête :

```php
// helpers.php
<?php

declare(strict_types=1);

function formaterDuree(int $minutes): string
{
    return intdiv($minutes, 60) . " h " . ($minutes % 60) . " min";
}
```

```php
// index.php
<?php

declare(strict_types=1);

require_once __DIR__ . '/helpers.php';

echo formaterDuree(150);
```

`__DIR__` est le dossier du fichier courant : il garantit un chemin correct quel que soit l'endroit où tu lances le script. Au chapitre sur Composer, tu verras comment automatiser ces inclusions.

:::quiz
Quelle fonction garde les éléments d'un tableau qui respectent une condition ?
- [ ] array_map
- [x] array_filter
- [ ] array_column
- [ ] array_sum
> `array_filter` conserve les éléments pour lesquels la fonction de rappel renvoie `true`. `array_map` transforme chaque élément sans en retirer.
:::

## Atelier guidé : le catalogue de roadmaps

Compte une heure. Tu vas construire un mini-catalogue DevRoad en PHP pur.

1. Crée un dossier `catalogue` avec les fichiers `donnees.php`, `fonctions.php` et `index.php`. Mets `declare(strict_types=1);` partout.
2. Dans `donnees.php`, écris un `return [...]` contenant au moins cinq roadmaps (clés `titre`, `minutes`, `niveau`, `tags` où `tags` est un tableau).
3. Dans `fonctions.php`, écris `formaterDuree(int $minutes): string` comme dans le cours.
4. Écris `filtrerParNiveau(array $roadmaps, string $niveau): array` avec `array_filter`.
5. Écris `dureeTotale(array $roadmaps): int` avec `array_column` et `array_sum`.
6. Écris `trierParDuree(array $roadmaps, bool $croissant = true): array` avec `usort` et `<=>`.
7. Dans `index.php`, charge les données avec `$roadmaps = require __DIR__ . '/donnees.php';` et les fonctions avec `require_once`.
8. Affiche les roadmaps `beginner` triées par durée, une ligne chacune, avec leurs tags séparés par des virgules (`implode`).
9. Affiche la durée totale du catalogue avec `formaterDuree`.
10. Ajoute une roadmap sans clé `tags` et assure-toi que ton code ne produit aucun avertissement grâce à `??`.

Pour t'auto-évaluer : sans regarder le cours, écris une fonction qui prend un tableau de nombres et renvoie ceux qui sont supérieurs à la moyenne.

## Erreurs fréquentes

- **Lire une clé inexistante** : `Undefined array key`. Utilise `??` ou `isset`.
- **Croire que `array_filter` réindexe** : les clés d'origine sont conservées, passe par `array_values` si tu as besoin d'une liste continue.
- **Oublier le `use` dans une closure** : la variable extérieure est alors indéfinie.
- **Utiliser `in_array` sans le troisième argument** : sans `true`, la comparaison est laxiste.
- **Modifier un tableau dans une fonction en croyant changer l'original** : les tableaux sont passés **par valeur**, la fonction travaille sur une copie. Renvoie le nouveau tableau.
- **Placer un paramètre avec valeur par défaut avant un paramètre obligatoire** : PHP 8 l'avertit comme obsolète.
- **Oublier `return`** : la fonction renvoie `null` et le bug apparaît plus loin.

## Bonnes pratiques

- Type tous les paramètres et les retours de tes fonctions.
- Une fonction fait **une seule chose** et a un nom qui commence par un verbe : `calculerTotal`, `filtrerParNiveau`.
- Renvoie une valeur plutôt que d'afficher avec `echo` dans la fonction : elle sera réutilisable et testable.
- Préfère `array_map`, `array_filter` et `array_column` aux boucles quand l'intention est claire ; garde `foreach` quand la logique se complexifie.
- Utilise les arguments nommés pour les appels avec plusieurs paramètres optionnels.
- Évite `global` et les variables partagées : passe tout en paramètre.
- Utilise `__DIR__` dans tous tes chemins d'inclusion.

## À retenir

- Un tableau indexé est une liste (positions 0, 1, 2...), un tableau associatif relie clés et valeurs.
- `foreach ($tableau as $cle => $valeur)` parcourt les deux ; `??` protège contre les clés absentes.
- `array_map`, `array_filter`, `array_column`, `usort` et `array_sum` couvrent la majorité des besoins.
- Une fonction se déclare avec des types, des valeurs par défaut et un type de retour ; les arguments nommés rendent les appels lisibles.
- Une fonction ne voit pas les variables extérieures : passe-les en paramètre ou via `use`.
- `fn` est la forme courte des fonctions anonymes ; `require_once __DIR__ . '/fichier.php'` découpe le code.
