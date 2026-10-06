<?php

return [
    'lessons' => [
        'Fondamentaux PHP' => [
            'description' => 'Découvre comment PHP s’exécute côté serveur, puis maîtrise les variables, les types, les conditions, les boucles et les fonctions typées.',
            'objective' => 'Écrire un script PHP de plusieurs dizaines de lignes qui calcule un total de commande avec des fonctions typées, des conditions et une boucle, sans erreur ni avertissement.',
            'content' => <<<'MD'
## Pourquoi cette notion

PHP est un langage exécuté côté serveur. Quand un client ouvre une page de ta boutique en ligne, le serveur lit ton fichier PHP, exécute le code, puis renvoie uniquement le résultat (du HTML ou du JSON) au navigateur ou à l'application mobile. Le visiteur ne voit jamais ton code source PHP.

Tu rencontreras PHP partout dans les projets web francophones : sites vitrines, back-offices de gestion, plateformes de réservation, intégrations de paiement mobile money. Des frameworks comme Laravel sont construits dessus. Comprendre les bases du langage, c'est donc se donner les moyens de lire et d'écrire n'importe quel code PHP professionnel.

Dans cette leçon, tu poses les fondations : stocker des valeurs, prendre des décisions, répéter des actions et regrouper de la logique dans des fonctions réutilisables.

## Les concepts clés

### Variables et types

Une variable PHP commence toujours par le signe dollar, par exemple « $prix ». Tu n'as pas besoin de déclarer son type à l'avance, mais PHP en manipule plusieurs : « int » pour les entiers, « float » pour les décimaux, « string » pour le texte, « bool » pour vrai ou faux, « array » pour les collections et « null » pour l'absence de valeur.

PHP convertit parfois les types automatiquement, ce qui peut surprendre. Pour éviter ces pièges, active le mode strict avec la ligne « declare(strict_types=1); » en haut du fichier. PHP refusera alors de transformer silencieusement une chaîne en nombre quand tu appelles une fonction typée.

### Conditions et comparaisons

Les conditions utilisent « if », « elseif » et « else », ainsi que « match » pour choisir une valeur selon un cas. Prends l'habitude d'utiliser l'opérateur de comparaison strict « === » plutôt que « == ». Le premier compare la valeur ET le type, le second tente des conversions qui produisent des résultats inattendus.

### Boucles

« foreach » parcourt un tableau élément par élément, c'est la boucle que tu utiliseras le plus. « for » sert quand tu connais le nombre d'itérations, « while » quand la condition d'arrêt dépend d'un état. Dans tous les cas, vérifie que la boucle se termine.

### Fonctions typées

Une fonction regroupe une logique réutilisable. Tu peux typer ses paramètres et sa valeur de retour, par exemple « function total(float $prix, int $quantite): float ». Les types documentent ton intention, et PHP lève une erreur claire quand on les viole. Une fonction doit faire une seule chose et retourner son résultat plutôt que l'afficher directement.

## Exemple pas à pas

L'exemple de code simule le panier d'une petite boutique à Abidjan. Suis les étapes dans l'ordre.

- Étape 1 : le mode strict est activé en première instruction du fichier.
- Étape 2 : une fonction « calculerTotalLigne » multiplie le prix unitaire par la quantité et retourne un entier en FCFA.
- Étape 3 : une fonction « calculerRemise » applique une remise selon le montant, grâce à des conditions.
- Étape 4 : un tableau de lignes de commande est parcouru avec « foreach » pour cumuler le sous-total.
- Étape 5 : le total final est affiché avec « echo » et un formatage lisible des montants.

Exécute le script en ligne de commande avec « php fichier.php » et modifie les quantités pour observer comment la remise change.

## Erreurs fréquentes

- Oublier le signe dollar devant une variable : PHP signale une constante inconnue. Écris toujours « $nom » et jamais « nom ».
- Utiliser « = » au lieu de « === » dans une condition : tu affectes une valeur au lieu de comparer. Relis chaque « if » en te demandant si tu compares ou si tu affectes.
- Utiliser « == » et obtenir des résultats surprenants entre chaînes et nombres : adopte « === » systématiquement.
- Oublier le point-virgule à la fin d'une instruction : l'erreur de syntaxe est souvent signalée à la ligne suivante, donc regarde aussi la ligne précédente.
- Afficher dans la fonction au lieu de retourner : la fonction devient impossible à réutiliser ou à tester. Utilise « return » puis affiche à l'extérieur.
- Lire une variable qui n'existe pas : PHP émet un avertissement. Initialise toujours tes variables avant de les utiliser.

## Bonnes pratiques

- Commence chaque fichier par « declare(strict_types=1); » pour des conversions de types prévisibles.
- Donne des noms explicites aux variables et aux fonctions : « $montantTotal » est plus clair que « $mt ».
- Garde des fonctions courtes qui font une seule chose et retournent une valeur.
- Utilise « === » pour comparer et « foreach » pour parcourir les collections.
- Active l'affichage des erreurs en développement pour corriger les problèmes dès qu'ils apparaissent.

## Auto-évaluation

- Où le code PHP est-il exécuté et que reçoit réellement le navigateur ?
- Quelle différence existe entre « == » et « === » et pourquoi préférer la seconde ?
- Quels sont les six types de base de PHP ?
- Que change « declare(strict_types=1); » quand tu appelles une fonction typée ?
- Pourquoi une fonction doit-elle retourner une valeur plutôt que l'afficher ?

## À retenir

- PHP s'exécute sur le serveur et renvoie du HTML ou du JSON au client.
- Une variable commence par « $ » et peut changer de type si tu n'actives pas le mode strict.
- Compare avec « === » pour éviter les conversions implicites.
- « foreach » est la boucle de référence pour parcourir les tableaux.
- Les fonctions typées rendent le code plus clair et plus sûr.
MD,
            'code_example' => <<<'CODE'
<?php
// Active le mode strict : PHP ne convertit plus les types en silence
declare(strict_types=1);

// Calcule le total d'une ligne de commande en FCFA
function calculerTotalLigne(int $prixUnitaire, int $quantite): int
{
    return $prixUnitaire * $quantite;
}

// Applique une remise selon le montant du sous-total
function calculerRemise(int $sousTotal): int
{
    if ($sousTotal >= 50000) {
        return (int) round($sousTotal * 0.10); // 10 % de remise
    } elseif ($sousTotal >= 20000) {
        return (int) round($sousTotal * 0.05); // 5 % de remise
    }

    return 0; // Pas de remise
}

// Panier d'une petite boutique : tableau de lignes associatives
$panier = [
    ['produit' => 'Riz 25 kg', 'prix' => 14500, 'quantite' => 2],
    ['produit' => 'Huile 5 L', 'prix' => 6500, 'quantite' => 3],
    ['produit' => 'Sucre 1 kg', 'prix' => 900, 'quantite' => 10],
];

$sousTotal = 0;

// Parcourt chaque ligne et cumule le sous-total
foreach ($panier as $ligne) {
    $totalLigne = calculerTotalLigne($ligne['prix'], $ligne['quantite']);
    echo $ligne['produit'] . ' : ' . number_format($totalLigne, 0, ',', ' ') . " FCFA\n";
    $sousTotal += $totalLigne;
}

$remise = calculerRemise($sousTotal);
$total = $sousTotal - $remise;

echo "Sous-total : " . number_format($sousTotal, 0, ',', ' ') . " FCFA\n";
echo "Remise : " . number_format($remise, 0, ',', ' ') . " FCFA\n";
echo "Total à payer : " . number_format($total, 0, ',', ' ') . " FCFA\n";
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Calculateur de facture de boutique',
            'exercise_description' => <<<'CODE'
Écris un script « facture.php » qui calcule la facture d'un client d'une boutique. Le panier contient au moins quatre produits, chacun avec un nom, un prix unitaire en FCFA et une quantité. Le script doit afficher une ligne par produit, le sous-total, la remise éventuelle et le total à payer.

Règles de remise : 10 % à partir de 40 000 FCFA, 5 % à partir de 15 000 FCFA, sinon aucune remise.

Critères de réussite :
- Le fichier commence par « declare(strict_types=1); ».
- Une fonction « calculerTotalLigne » typée retourne le total d'une ligne sans rien afficher.
- Une fonction « calculerRemise » typée applique les trois paliers avec des conditions et la comparaison stricte.
- Le panier est parcouru avec « foreach » et le script affiche le sous-total, la remise et le total.
- Le script s'exécute avec « php facture.php » sans erreur ni avertissement.
CODE,
            'exercise_hint' => 'Teste d\'abord tes fonctions isolément avec deux ou trois valeurs, puis assemble la boucle. Pense à vérifier le palier le plus élevé en premier dans tes conditions.',
            'exercise_solution' => <<<'CODE'
<?php
declare(strict_types=1);

function calculerTotalLigne(int $prixUnitaire, int $quantite): int
{
    return $prixUnitaire * $quantite;
}

function calculerRemise(int $sousTotal): int
{
    if ($sousTotal >= 40000) {
        return (int) round($sousTotal * 0.10);
    } elseif ($sousTotal >= 15000) {
        return (int) round($sousTotal * 0.05);
    }

    return 0;
}

$panier = [
    ['produit' => 'Savon', 'prix' => 500, 'quantite' => 12],
    ['produit' => 'Lait en poudre', 'prix' => 3200, 'quantite' => 4],
    ['produit' => 'Pâtes 500 g', 'prix' => 600, 'quantite' => 10],
    ['produit' => 'Huile 5 L', 'prix' => 6500, 'quantite' => 2],
];

$sousTotal = 0;

foreach ($panier as $ligne) {
    $totalLigne = calculerTotalLigne($ligne['prix'], $ligne['quantite']);
    echo $ligne['produit'] . ' x' . $ligne['quantite'] . ' : ' . number_format($totalLigne, 0, ',', ' ') . " FCFA\n";
    $sousTotal += $totalLigne;
}

$remise = calculerRemise($sousTotal);

echo "Sous-total : " . number_format($sousTotal, 0, ',', ' ') . " FCFA\n";
echo "Remise : " . number_format($remise, 0, ',', ' ') . " FCFA\n";
echo "Total à payer : " . number_format($sousTotal - $remise, 0, ',', ' ') . " FCFA\n";
CODE,
        ],

        'Tableaux et fonctions' => [
            'description' => 'Manipule les tableaux indexés et associatifs, puis transforme des collections avec des fonctions claires et les fonctions anonymes.',
            'objective' => 'Filtrer, transformer et agréger une liste de données avec array_filter, array_map, usort et array_sum, puis retourner un résultat structuré depuis une fonction.',
            'content' => <<<'MD'
## Pourquoi cette notion

Presque toutes les données d'une application arrivent sous forme de listes : produits d'une boutique, élèves d'une classe, courses d'un livreur, transactions mobile money. Les tableaux sont la structure que PHP utilise pour les représenter. Une grande partie de ton travail quotidien consistera à filtrer, trier, transformer et agréger ces collections.

Savoir le faire proprement te permet de produire des rapports, des statistiques ou des réponses d'API sans écrire des boucles imbriquées illisibles. Dans un projet réel, ces tableaux viennent souvent d'une base de données, mais les techniques restent identiques.

## Les concepts clés

### Tableaux indexés et associatifs

Un tableau indexé associe des positions numériques (0, 1, 2) à des valeurs. Un tableau associatif associe des clés textuelles à des valeurs, par exemple « ['nom' => 'Awa', 'note' => 15] ». C'est la forme la plus courante pour représenter un enregistrement, comme une ligne de base de données. Un tableau peut contenir d'autres tableaux, ce qui permet de décrire une liste d'enregistrements.

### Fonctions utiles sur les tableaux

PHP fournit des fonctions prêtes à l'emploi. « count » donne la taille, « array_keys » et « array_values » extraient clés et valeurs, « in_array » teste la présence d'une valeur, « array_key_exists » teste la présence d'une clé. « array_sum » additionne des nombres, « array_column » extrait une colonne d'une liste d'enregistrements.

### Filtrer, transformer, trier

« array_filter » garde les éléments qui satisfont une condition. « array_map » applique une transformation à chaque élément et retourne un nouveau tableau. « usort » trie avec une fonction de comparaison que tu écris. Attention : « array_filter » conserve les clés d'origine, utilise « array_values » pour réindexer.

### Fonctions anonymes et portée

Une fonction anonyme s'écrit « function (array $e): bool { ... } » ou, en version courte, « fn(array $e) => $e['note'] >= 10 ». La fonction fléchée capture automatiquement les variables du contexte parent en lecture seule. Une fonction classique, elle, ne voit pas les variables extérieures sans le mot-clé « use ». Chaque fonction a sa propre portée de variables.

## Exemple pas à pas

L'exemple traite les notes d'une classe.

- Étape 1 : un tableau d'élèves associatifs est défini avec un nom et une note.
- Étape 2 : « array_filter » extrait les élèves admis, c'est-à-dire ceux dont la note atteint la moyenne.
- Étape 3 : « array_map » construit une liste de chaînes lisibles pour l'affichage.
- Étape 4 : « usort » classe les élèves du meilleur au moins bon avec l'opérateur de comparaison combiné.
- Étape 5 : une fonction « resumerClasse » retourne un tableau associatif contenant la moyenne, la meilleure note et le nombre d'admis.

Remarque comment chaque étape produit un nouveau tableau sans modifier l'original, ce qui rend le code plus facile à raisonner.

## Erreurs fréquentes

- Lire une clé qui n'existe pas : PHP émet un avertissement. Teste avec « isset » ou utilise l'opérateur « ?? » pour fournir une valeur par défaut.
- Croire que « array_filter » réindexe le résultat : les clés d'origine sont conservées. Enveloppe le résultat avec « array_values » si tu veux des indices continus.
- Oublier que « usort » modifie le tableau passé en paramètre : copie-le d'abord si tu as besoin de l'ordre d'origine.
- Diviser par « count » sans vérifier que le tableau est vide : tu obtiens une erreur de division par zéro. Vérifie la taille avant.
- Utiliser une variable extérieure dans une fonction anonyme classique sans « use » : la variable est vue comme non définie. Utilise « use » ou une fonction fléchée.
- Mélanger affichage et calcul dans la même fonction : retourne un tableau et affiche à l'extérieur.

## Bonnes pratiques

- Préfère « array_map », « array_filter » et « array_sum » aux boucles manuelles quand l'intention est simple.
- Retourne des tableaux associatifs clairement nommés plutôt que des tableaux indexés difficiles à lire.
- Utilise « ?? » pour les valeurs par défaut au lieu de multiplier les « isset ».
- Garde chaque fonction pure : mêmes entrées, mêmes sorties, aucun effet de bord caché.
- Type les paramètres et les retours, y compris « array », pour détecter les erreurs tôt.

## Auto-évaluation

- Quelle différence existe entre un tableau indexé et un tableau associatif ?
- Que fait « array_filter » avec les clés et comment les réindexer ?
- Quand préférer une fonction fléchée à une fonction anonyme classique ?
- Que fait « usort » et que doit retourner sa fonction de comparaison ?
- Comment éviter un avertissement quand une clé peut être absente ?

## À retenir

- Un enregistrement se représente par un tableau associatif, une collection par une liste de ces tableaux.
- « array_filter », « array_map » et « usort » couvrent la majorité des transformations.
- Les fonctions fléchées capturent le contexte en lecture seule.
- Une fonction doit retourner une valeur structurée plutôt qu'afficher.
- Vérifie toujours l'existence d'une clé ou d'un tableau vide avant de calculer.
MD,
            'code_example' => <<<'CODE'
<?php
declare(strict_types=1);

// Liste d'élèves : un tableau d'enregistrements associatifs
$eleves = [
    ['nom' => 'Awa', 'note' => 15.5],
    ['nom' => 'Koffi', 'note' => 8.0],
    ['nom' => 'Mariam', 'note' => 12.0],
    ['nom' => 'Yao', 'note' => 17.25],
    ['nom' => 'Fatou', 'note' => 9.5],
];

// Étape 2 : garde uniquement les élèves admis (note >= 10)
$admis = array_values(array_filter($eleves, fn(array $e): bool => $e['note'] >= 10));

// Étape 3 : transforme chaque élève admis en texte lisible
$lignes = array_map(
    fn(array $e): string => $e['nom'] . ' : ' . number_format($e['note'], 2, ',', ' ') . '/20',
    $admis
);

// Étape 4 : classe du meilleur au moins bon (copie pour garder l'original)
$classement = $eleves;
usort($classement, fn(array $a, array $b): int => $b['note'] <=> $a['note']);

// Étape 5 : retourne un résumé structuré, sans rien afficher
function resumerClasse(array $eleves): array
{
    if (count($eleves) === 0) {
        return ['moyenne' => 0.0, 'meilleure' => null, 'admis' => 0];
    }

    $notes = array_column($eleves, 'note');

    return [
        'moyenne' => round(array_sum($notes) / count($notes), 2),
        'meilleure' => max($notes),
        'admis' => count(array_filter($notes, fn(float $n): bool => $n >= 10)),
    ];
}

echo implode("\n", $lignes) . "\n";
echo 'Premier : ' . $classement[0]['nom'] . "\n";
print_r(resumerClasse($eleves));
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Rapport de ventes par produit',
            'exercise_description' => <<<'CODE'
À partir d'une liste d'au moins six ventes (produit, quantité, prix unitaire en FCFA, avec un même produit présent plusieurs fois), écris un script « rapport.php » qui produit un rapport de ventes.

Critères de réussite :
- Une fonction « totalParProduit » reçoit la liste des ventes et retourne un tableau associatif produit => chiffre d'affaires cumulé.
- Une fonction « topProduits » retourne les trois produits au plus fort chiffre d'affaires, triés du plus grand au plus petit, avec « usort » ou « arsort ».
- Une vente avec une quantité inférieure ou égale à zéro est ignorée grâce à « array_filter ».
- Les fonctions retournent des tableaux et n'affichent rien ; seul le script principal affiche le rapport.
- Le script gère une liste vide sans erreur et s'exécute sans avertissement.
CODE,
            'exercise_hint' => 'Pour cumuler par produit, crée un tableau vide puis, dans un foreach, ajoute au total existant avec « ?? 0 » comme valeur de départ. « arsort » trie un tableau associatif par valeur décroissante en gardant les clés.',
            'exercise_solution' => <<<'CODE'
<?php
declare(strict_types=1);

function totalParProduit(array $ventes): array
{
    // Ignore les ventes dont la quantité est invalide
    $valides = array_filter($ventes, fn(array $v): bool => $v['quantite'] > 0);

    $totaux = [];
    foreach ($valides as $vente) {
        $totaux[$vente['produit']] = ($totaux[$vente['produit']] ?? 0)
            + $vente['quantite'] * $vente['prix'];
    }

    return $totaux;
}

function topProduits(array $totaux, int $limite = 3): array
{
    arsort($totaux); // tri décroissant en gardant les clés

    return array_slice($totaux, 0, $limite, true);
}

$ventes = [
    ['produit' => 'Riz', 'quantite' => 3, 'prix' => 14500],
    ['produit' => 'Huile', 'quantite' => 2, 'prix' => 6500],
    ['produit' => 'Riz', 'quantite' => 1, 'prix' => 14500],
    ['produit' => 'Sucre', 'quantite' => 10, 'prix' => 900],
    ['produit' => 'Lait', 'quantite' => 0, 'prix' => 3200],
    ['produit' => 'Huile', 'quantite' => 4, 'prix' => 6500],
    ['produit' => 'Savon', 'quantite' => 12, 'prix' => 500],
];

$totaux = totalParProduit($ventes);
$top = topProduits($totaux);

echo "Chiffre d'affaires par produit :\n";
foreach ($totaux as $produit => $montant) {
    echo "- $produit : " . number_format($montant, 0, ',', ' ') . " FCFA\n";
}

echo "\nTop 3 :\n";
foreach ($top as $produit => $montant) {
    echo "- $produit : " . number_format($montant, 0, ',', ' ') . " FCFA\n";
}

// Cas liste vide : aucun produit, aucune erreur
var_dump(totalParProduit([]));
CODE,
        ],

        'POO et classes' => [
            'description' => 'Modélise ton domaine métier avec des classes, des propriétés typées, des interfaces et la composition pour obtenir un code organisé.',
            'objective' => 'Concevoir au moins deux classes collaborant entre elles et une interface, avec des propriétés privées, un constructeur promu et une méthode métier qui protège ses règles.',
            'content' => <<<'MD'
## Pourquoi cette notion

Quand une application grossit, des fonctions et des tableaux ne suffisent plus. Une réservation d'hôtel, un compte client ou une commande ont chacun des données et des règles qui vont ensemble. La programmation orientée objet regroupe cet état et ce comportement dans une classe, ce qui rend le code plus lisible et plus facile à faire évoluer.

Les frameworks modernes comme Laravel sont entièrement orientés objet : contrôleurs, modèles, services, requêtes. Tu ne peux pas les utiliser sereinement sans comprendre classes, interfaces et visibilité.

## Les concepts clés

### Classe, objet et constructeur

Une classe est un plan de construction. Un objet est une instance créée avec « new ». Le constructeur initialise l'objet. Depuis PHP 8, la promotion de propriétés permet de déclarer et d'initialiser en une seule ligne : « public function __construct(private string $nom) {} ».

### Visibilité et encapsulation

Une propriété ou une méthode « public » est accessible partout, « protected » dans la classe et ses enfants, « private » uniquement dans la classe elle-même. Le principe d'encapsulation consiste à cacher l'état interne et à n'exposer que des méthodes qui garantissent la cohérence. Par exemple, un compte ne doit pas permettre de modifier directement son solde : il expose « crediter » et « debiter » qui vérifient les règles.

### Héritage, interfaces et composition

L'héritage avec « extends » permet de réutiliser une classe parente, mais il couple fortement les classes. Une interface définit un contrat : les méthodes que toute classe qui l'implémente doit fournir. Elle permet de remplacer une implémentation par une autre, par exemple un paiement par carte ou par mobile money. La composition consiste à donner à un objet d'autres objets en dépendance plutôt qu'à hériter. En règle générale, préfère la composition et les interfaces à l'héritage profond.

### Types, propriétés en lecture seule et exceptions

Type toujours tes propriétés. Le mot-clé « readonly » interdit de modifier une propriété après son initialisation, utile pour les identifiants et les montants figés. Pour signaler une règle violée, lance une exception avec « throw new InvalidArgumentException(...) » plutôt que de retourner un code d'erreur ambigu.

## Exemple pas à pas

L'exemple modélise un petit portefeuille de paiement.

- Étape 1 : une interface « Moyen de paiement » impose une méthode « payer » qui reçoit un montant.
- Étape 2 : la classe « Portefeuille » possède un solde privé et deux méthodes, « crediter » et « debiter », qui refusent les montants négatifs ou supérieurs au solde.
- Étape 3 : « Portefeuille » implémente l'interface pour pouvoir être utilisé comme moyen de paiement.
- Étape 4 : une classe « Commande » reçoit un moyen de paiement dans son constructeur, c'est la composition. Elle ne sait pas quel type de paiement elle utilise.
- Étape 5 : le script crée les objets, paie une commande et attrape l'exception quand le solde est insuffisant.

## Erreurs fréquentes

- Rendre toutes les propriétés publiques : n'importe quel code peut casser les règles de l'objet. Passe-les en « private » et expose des méthodes.
- Oublier « $this-> » pour accéder à une propriété dans une méthode : PHP cherche une variable locale. Écris « $this->solde ».
- Confondre classe et objet : la classe est le plan, « new » crée l'objet. Chaque objet a son propre état.
- Utiliser l'héritage pour partager du code utilitaire : tu crées un couplage inutile. Utilise la composition ou une interface.
- Ne pas typer les propriétés et les paramètres : les erreurs apparaissent tard et loin de leur cause. Type tout ce qui peut l'être.
- Retourner « false » ou « null » pour signaler une erreur métier : l'appelant oublie de tester. Lance une exception explicite.

## Bonnes pratiques

- Une classe doit avoir une seule responsabilité clairement nommée.
- Garde l'état privé et expose un comportement, pas des données brutes.
- Dépends d'interfaces plutôt que de classes concrètes pour pouvoir remplacer une implémentation.
- Utilise la promotion de constructeur et « readonly » pour réduire le code répétitif.
- Valide les règles métier dans l'objet lui-même, pas seulement dans le contrôleur.

## Auto-évaluation

- Quelle est la différence entre une classe et un objet ?
- Que signifient « public », « protected » et « private » ?
- À quoi sert une interface et pourquoi est-elle utile pour remplacer une implémentation ?
- Pourquoi préférer la composition à l'héritage dans la plupart des cas ?
- Quand lancer une exception plutôt que retourner « false » ?

## À retenir

- Une classe regroupe des données et le comportement qui les manipule.
- L'encapsulation protège les règles métier en cachant l'état interne.
- Une interface définit un contrat et rend les composants interchangeables.
- La composition rend le code plus souple que l'héritage.
- Les exceptions signalent clairement une règle violée.
MD,
            'code_example' => <<<'CODE'
<?php
declare(strict_types=1);

// Étape 1 : contrat commun à tous les moyens de paiement
interface MoyenPaiement
{
    public function payer(int $montant): void;
}

// Étape 2 et 3 : un portefeuille encapsule son solde et respecte le contrat
class Portefeuille implements MoyenPaiement
{
    // Propriété promue : déclarée et initialisée dans le constructeur
    public function __construct(private int $solde = 0)
    {
    }

    public function crediter(int $montant): void
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant doit être positif.');
        }
        $this->solde += $montant;
    }

    public function payer(int $montant): void
    {
        if ($montant <= 0) {
            throw new InvalidArgumentException('Le montant doit être positif.');
        }
        if ($montant > $this->solde) {
            throw new RuntimeException('Solde insuffisant.');
        }
        $this->solde -= $montant;
    }

    public function solde(): int
    {
        return $this->solde;
    }
}

// Étape 4 : la commande dépend d'un contrat, pas d'une classe précise
class Commande
{
    public function __construct(
        private readonly string $reference,
        private readonly int $montant,
        private MoyenPaiement $paiement
    ) {
    }

    public function regler(): string
    {
        $this->paiement->payer($this->montant);

        return "Commande {$this->reference} réglée.";
    }
}

// Étape 5 : utilisation et gestion de l'erreur métier
$portefeuille = new Portefeuille(10000);
$portefeuille->crediter(5000);

try {
    echo (new Commande('CMD-001', 12000, $portefeuille))->regler() . "\n";
    echo (new Commande('CMD-002', 9000, $portefeuille))->regler() . "\n";
} catch (RuntimeException $e) {
    echo 'Échec : ' . $e->getMessage() . "\n";
}

echo 'Solde restant : ' . $portefeuille->solde() . " FCFA\n";
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Gestion de stock orientée objet',
            'exercise_description' => <<<'CODE'
Modélise le stock d'une boutique en deux classes : « Produit » et « Stock ». Écris le tout dans un fichier « stock.php » avec un petit script de démonstration.

Critères de réussite :
- La classe « Produit » utilise la promotion de constructeur, avec un nom et un prix en « readonly », et une quantité privée.
- « Produit » expose « ajouter(int $quantite) » et « retirer(int $quantite) » ; « retirer » lance une exception si la quantité demandée dépasse le stock ou n'est pas positive.
- La classe « Stock » contient une collection de produits (composition), avec « enregistrer(Produit $p) » et « valeurTotale(): int » qui additionne prix multiplié par quantité.
- Aucune propriété n'est publique en écriture ; l'état n'est modifiable que par des méthodes.
- Le script de démonstration attrape l'exception d'un retrait impossible et affiche la valeur totale.
CODE,
            'exercise_hint' => 'Commence par la classe Produit seule et teste « retirer » avec une quantité trop grande. Pour Stock, un tableau privé de Produit suffit ; « valeurTotale » est une boucle qui additionne.',
            'exercise_solution' => <<<'CODE'
<?php
declare(strict_types=1);

class Produit
{
    public function __construct(
        public readonly string $nom,
        public readonly int $prix,
        private int $quantite = 0
    ) {
    }

    public function ajouter(int $quantite): void
    {
        if ($quantite <= 0) {
            throw new InvalidArgumentException('La quantité doit être positive.');
        }
        $this->quantite += $quantite;
    }

    public function retirer(int $quantite): void
    {
        if ($quantite <= 0) {
            throw new InvalidArgumentException('La quantité doit être positive.');
        }
        if ($quantite > $this->quantite) {
            throw new RuntimeException("Stock insuffisant pour {$this->nom}.");
        }
        $this->quantite -= $quantite;
    }

    public function quantite(): int
    {
        return $this->quantite;
    }
}

class Stock
{
    /** @var Produit[] */
    private array $produits = [];

    public function enregistrer(Produit $produit): void
    {
        $this->produits[] = $produit;
    }

    public function valeurTotale(): int
    {
        $total = 0;
        foreach ($this->produits as $produit) {
            $total += $produit->prix * $produit->quantite();
        }

        return $total;
    }
}

$riz = new Produit('Riz 25 kg', 14500, 10);
$huile = new Produit('Huile 5 L', 6500, 4);

$stock = new Stock();
$stock->enregistrer($riz);
$stock->enregistrer($huile);

try {
    $huile->retirer(2);
    $huile->retirer(10); // doit échouer
} catch (RuntimeException $e) {
    echo 'Erreur : ' . $e->getMessage() . "\n";
}

echo 'Valeur du stock : ' . $stock->valeurTotale() . " FCFA\n";
CODE,
        ],

        'Composer et autoloading' => [
            'description' => 'Comprends le rôle de Composer, du fichier composer.json et de l\'autoloading PSR-4 pour organiser un projet PHP en espaces de noms.',
            'objective' => 'Initialiser un projet avec Composer, configurer l\'autoloading PSR-4, installer une dépendance et charger ses propres classes sans aucun « require » manuel.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dès qu'un projet dépasse quelques fichiers, inclure chaque classe à la main avec « require » devient ingérable. De plus, tu ne veux pas réécrire ce que d'autres ont déjà bien fait : envoi d'e-mails, lecture de fichiers de configuration, génération de PDF. Composer résout ces deux problèmes : il télécharge les bibliothèques dont ton projet dépend et il génère un chargeur automatique de classes.

Tous les projets PHP professionnels utilisent Composer, y compris Laravel. Comprendre son fonctionnement t'évite de subir une configuration que tu ne maîtrises pas.

## Les concepts clés

### Composer et composer.json

Composer est un gestionnaire de dépendances. Le fichier « composer.json » décrit ton projet : son nom, la version de PHP requise, les paquets dont il dépend dans la section « require » et les règles d'autoloading. La commande « composer require vendor/paquet » ajoute une dépendance, la télécharge dans le dossier « vendor » et met à jour le fichier.

### composer.lock et vendor

Le fichier « composer.lock » enregistre les versions exactes installées. Il garantit que toute l'équipe et le serveur de production utilisent les mêmes versions. Tu le versionnes avec Git. Le dossier « vendor » contient le code téléchargé : tu ne le modifies jamais et tu ne le versionnes pas, il se régénère avec « composer install ».

### Espaces de noms

Un espace de noms, ou namespace, évite les conflits de noms entre classes. On le déclare en haut du fichier avec « namespace App\Services; ». Le nom complet de la classe devient « App\Services\Calculateur ». Le mot-clé « use » permet ensuite de l'importer sous son nom court.

### Autoloading PSR-4

PSR-4 est une convention qui relie un préfixe d'espace de noms à un dossier. Dans « composer.json », la section « autoload » peut déclarer que le préfixe « App\ » correspond au dossier « src/ ». Une classe « App\Services\Calculateur » doit alors se trouver dans le fichier « src/Services/Calculateur.php ». Le nom du fichier doit correspondre exactement au nom de la classe. Après toute modification de cette section, exécute « composer dump-autoload ». Il ne reste qu'à inclure une seule fois « vendor/autoload.php » au point d'entrée.

## Exemple pas à pas

L'exemple utilise un projet minimal avec une classe métier et la bibliothèque « vlucas/phpdotenv » pour lire un fichier d'environnement.

- Étape 1 : « composer init » crée « composer.json » et « composer require vlucas/phpdotenv » installe la dépendance.
- Étape 2 : la section « autoload » associe « App\ » au dossier « src/ », suivie de « composer dump-autoload ».
- Étape 3 : la classe « Calculateur » est placée dans « src/Calculateur.php » avec le bon espace de noms.
- Étape 4 : le point d'entrée « public/index.php » charge uniquement « vendor/autoload.php ».
- Étape 5 : il importe la classe avec « use », lit une variable du fichier « .env » et affiche un résultat.

Aucun « require » de classe n'est nécessaire : l'autoloader trouve le fichier grâce au nom complet.

## Erreurs fréquentes

- Erreur « Class not found » : l'espace de noms ne correspond pas au chemin du fichier, ou la casse diffère. Vérifie que le nom de fichier est identique au nom de classe, majuscules comprises.
- Oublier « composer dump-autoload » après avoir modifié la section autoload : le nouveau mapping n'est pas pris en compte. Relance la commande.
- Versionner le dossier « vendor » : le dépôt devient énorme. Ajoute-le à « .gitignore » et utilise « composer install ».
- Modifier le code dans « vendor » : tes changements disparaissent à la prochaine mise à jour. Contourne le problème dans ton propre code.
- Supprimer « composer.lock » ou utiliser « composer update » en production : les versions changent sans contrôle. Utilise « composer install » pour reproduire exactement.
- Mettre des secrets dans un fichier versionné : ils fuient dans l'historique. Garde-les dans un fichier « .env » ignoré par Git.

## Bonnes pratiques

- Versionne « composer.json » et « composer.lock », jamais « vendor » ni « .env ».
- Respecte PSR-4 : un fichier par classe, nom de fichier identique au nom de classe.
- Choisis des paquets maintenus, avec une documentation claire, avant de les ajouter.
- Sur un serveur de production, installe avec « composer install --no-dev » pour exclure les outils de développement.
- Garde le point d'entrée minimal : autoload, configuration, démarrage.

## Auto-évaluation

- Quels problèmes Composer résout-il dans un projet PHP ?
- Quelle est la différence entre « composer install » et « composer update » ?
- Pourquoi versionner « composer.lock » mais pas « vendor » ?
- Comment PSR-4 associe-t-il un espace de noms à un chemin de fichier ?
- Que faire après avoir modifié la section autoload de « composer.json » ?

## À retenir

- Composer installe les dépendances et génère l'autoloader.
- « composer.json » décrit le projet, « composer.lock » fige les versions exactes.
- PSR-4 relie un préfixe d'espace de noms à un dossier.
- Un seul « require » du fichier « vendor/autoload.php » suffit.
- Les secrets restent dans « .env », jamais dans le dépôt.
MD,
            'code_example' => <<<'CODE'
<?php
// Fichier public/index.php
// Structure attendue du projet :
//   composer.json          (autoload PSR-4 : "App\\" => "src/")
//   .env                   (contient APP_NAME=MaBoutique)
//   src/Calculateur.php    (namespace App;)
//   public/index.php
//
// Extrait du composer.json :
//   "autoload": { "psr-4": { "App\\": "src/" } }
//   "require":  { "vlucas/phpdotenv": "^5.0" }

declare(strict_types=1);

// Un seul require : l'autoloader de Composer charge tout le reste
require __DIR__ . '/../vendor/autoload.php';

use App\Calculateur;
use Dotenv\Dotenv;

// Charge les variables du fichier .env (dossier parent de public/)
$dotenv = Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->safeLoad(); // ne plante pas si le fichier est absent

// Lit une variable de configuration avec une valeur par défaut
$nomApplication = $_ENV['APP_NAME'] ?? 'Application';

// La classe est trouvée automatiquement grâce à PSR-4
$calculateur = new Calculateur();

echo $nomApplication . " - total TTC : " . $calculateur->ttc(10000, 18) . " FCFA\n";

/*
// Contenu de src/Calculateur.php :
<?php
declare(strict_types=1);

namespace App;

class Calculateur
{
    // Calcule un montant TTC à partir d'un HT et d'un taux en pourcentage
    public function ttc(int $ht, int $tauxTva): int
    {
        return (int) round($ht * (1 + $tauxTva / 100));
    }
}
*/
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Projet structuré avec autoloading PSR-4',
            'exercise_description' => <<<'CODE'
Crée un petit projet « mini-caisse » structuré avec Composer. Le projet doit calculer un montant TTC et lire le nom de la boutique depuis la configuration.

Critères de réussite :
- Le projet contient un fichier « composer.json » généré avec « composer init », avec la dépendance « vlucas/phpdotenv » dans « require ».
- La section « autoload » déclare le préfixe « Caisse\ » vers le dossier « src/ » et « composer dump-autoload » a été exécuté.
- Une classe « Caisse\Facture » est placée dans « src/Facture.php », avec le bon espace de noms et une méthode « totalTtc ».
- Le fichier « public/index.php » ne contient qu'un seul « require », celui de « vendor/autoload.php ».
- Le nom de la boutique vient d'un fichier « .env » et le dossier « vendor » est listé dans « .gitignore ».
CODE,
            'exercise_hint' => 'Si tu obtiens « Class not found », compare l\'espace de noms de la classe, le préfixe PSR-4 du composer.json et le nom exact du fichier, majuscules comprises, puis relance « composer dump-autoload ».',
            'exercise_solution' => <<<'CODE'
<?php
// ===== composer.json (extrait) =====
// {
//   "name": "monnom/mini-caisse",
//   "require": { "vlucas/phpdotenv": "^5.0" },
//   "autoload": { "psr-4": { "Caisse\\": "src/" } }
// }
//
// ===== .env =====
// NOM_BOUTIQUE="Boutique Adjamé"
//
// ===== .gitignore =====
// /vendor
// .env
//
// Commandes : composer init ; composer require vlucas/phpdotenv ; composer dump-autoload

// ===== src/Facture.php =====
declare(strict_types=1);

namespace Caisse;

class Facture
{
    public function __construct(private int $montantHt, private int $tauxTva = 18)
    {
    }

    public function totalTtc(): int
    {
        return (int) round($this->montantHt * (1 + $this->tauxTva / 100));
    }
}

// ===== public/index.php =====
// <?php
// declare(strict_types=1);
//
// require __DIR__ . '/../vendor/autoload.php';
//
// use Caisse\Facture;
// use Dotenv\Dotenv;
//
// Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();
//
// $boutique = $_ENV['NOM_BOUTIQUE'] ?? 'Boutique';
// $facture = new Facture(25000);
//
// echo $boutique . ' - Total TTC : ' . $facture->totalTtc() . " FCFA\n";
CODE,
        ],

        'HTTP, formulaires et APIs' => [
            'description' => 'Apprends à lire une requête HTTP en PHP, valider les données reçues et renvoyer une réponse JSON avec le bon code de statut.',
            'objective' => 'Écrire un endpoint PHP qui accepte une requête POST au format JSON, valide les champs, retourne des codes HTTP cohérents et refuse les méthodes non prévues.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application web, c'est avant tout un échange : le client envoie une requête, le serveur répond. Ton site de réservation, ton application mobile ou le webhook d'un service de paiement communiquent tous ainsi. Savoir lire une requête, la valider et produire une réponse cohérente est le cœur du métier de développeur back-end.

Une API bien conçue est prévisible : elle répond toujours dans le même format et utilise les codes de statut HTTP pour signaler le résultat. Cela facilite le travail des développeurs qui l'utilisent, y compris le tien, dans six mois.

## Les concepts clés

### Méthodes et codes de statut

Une requête HTTP possède une méthode : GET pour lire, POST pour créer, PUT ou PATCH pour modifier, DELETE pour supprimer. La réponse porte un code de statut. Retiens les principaux : 200 pour un succès, 201 pour une ressource créée, 400 pour une requête mal formée, 404 pour une ressource introuvable, 405 pour une méthode non autorisée, 422 pour des données invalides et 500 pour une erreur serveur.

### Lire la requête en PHP

PHP expose la méthode dans « $_SERVER['REQUEST_METHOD'] ». Les champs d'un formulaire classique arrivent dans « $_POST » et les paramètres d'URL dans « $_GET ». Pour une API qui reçoit du JSON, le corps brut se lit avec « file_get_contents('php://input') », puis se décode avec « json_decode ». Pense à vérifier que le décodage a réussi.

### Valider les données

Ne suppose jamais que les données sont correctes. Vérifie la présence de chaque champ, son type et son format. La fonction « filter_var » avec « FILTER_VALIDATE_EMAIL » ou « FILTER_VALIDATE_INT » couvre les cas courants. Collecte toutes les erreurs dans un tableau pour les renvoyer ensemble au client plutôt qu'une par une.

### Répondre en JSON

Pour une API, définis l'en-tête « Content-Type » avec « header('Content-Type: application/json; charset=utf-8') », fixe le code de statut avec « http_response_code », puis affiche « json_encode » du résultat. Garde une forme de réponse constante, par exemple une clé « data » en cas de succès et une clé « errors » en cas d'échec.

## Exemple pas à pas

L'exemple crée un endpoint d'inscription à un événement.

- Étape 1 : une fonction « repondre » centralise l'envoi du code de statut, de l'en-tête et du corps JSON.
- Étape 2 : le script refuse toute méthode autre que POST avec un code 405 et l'en-tête « Allow ».
- Étape 3 : le corps JSON est lu et décodé ; s'il est invalide, la réponse est un code 400.
- Étape 4 : les champs nom, e-mail et nombre de places sont validés et les erreurs sont accumulées dans un tableau.
- Étape 5 : en cas d'erreurs, la réponse est un code 422 ; sinon, le script renvoie un code 201 avec les données acceptées.

Teste l'endpoint avec « curl » ou un client comme Postman en changeant volontairement les données pour déclencher chaque branche.

## Erreurs fréquentes

- Renvoyer toujours un code 200, même en cas d'erreur : les clients ne peuvent plus distinguer un échec d'un succès. Utilise le code adapté.
- Utiliser « $_POST » pour une requête JSON : il reste vide car le corps n'est pas un formulaire. Lis « php://input » et décode.
- Ne pas vérifier le résultat de « json_decode » : tu travailles sur « null » sans le savoir. Teste le retour et réponds par un 400.
- Afficher du texte ou des avertissements avant l'en-tête : PHP déclare que les en-têtes sont déjà envoyés. Définis les en-têtes avant toute sortie.
- Oublier « exit » après une réponse d'erreur : le script continue et envoie une deuxième réponse. Arrête l'exécution.
- Faire confiance aux données reçues : une valeur manquante ou d'un mauvais type provoque des erreurs ou des failles. Valide chaque champ.

## Bonnes pratiques

- Garde un format de réponse constant, avec des clés prévisibles pour les succès et les erreurs.
- Utilise le code de statut le plus précis plutôt qu'un code générique.
- Valide toutes les entrées côté serveur, même si le client le fait déjà.
- Isole la lecture de la requête, la validation et la réponse dans des fonctions distinctes.
- Ne divulgue jamais les détails internes (chemins, requêtes SQL, traces) dans un message d'erreur destiné au client.

## Auto-évaluation

- Quelle est la différence entre les codes 400, 404 et 422 ?
- Où PHP place-t-il les champs d'un formulaire et comment lire un corps JSON ?
- Pourquoi définir l'en-tête « Content-Type » d'une API ?
- Quel code renvoyer quand la méthode HTTP n'est pas autorisée ?
- Pourquoi valider côté serveur même quand le client valide déjà ?

## À retenir

- Une API répond avec un code de statut précis et un format constant.
- Un corps JSON se lit avec « php://input » puis « json_decode ».
- Les erreurs de validation se collectent et se renvoient ensemble avec un code 422.
- Les en-têtes s'envoient avant toute sortie, et « exit » arrête le script après une erreur.
- Le serveur ne fait jamais confiance au client.
MD,
            'code_example' => <<<'CODE'
<?php
declare(strict_types=1);

// Étape 1 : envoie une réponse JSON puis arrête le script
function repondre(int $statut, array $corps): never
{
    http_response_code($statut);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($corps, JSON_UNESCAPED_UNICODE);
    exit;
}

// Étape 2 : seule la méthode POST est acceptée
if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    repondre(405, ['errors' => ['Méthode non autorisée.']]);
}

// Étape 3 : lit et décode le corps JSON
$brut = file_get_contents('php://input');
$donnees = json_decode($brut ?: '', true);

if (!is_array($donnees)) {
    repondre(400, ['errors' => ['Corps JSON invalide.']]);
}

// Étape 4 : valide chaque champ et accumule les erreurs
$erreurs = [];

$nom = trim((string) ($donnees['nom'] ?? ''));
if ($nom === '' || mb_strlen($nom) < 2) {
    $erreurs['nom'] = 'Le nom est obligatoire (2 caractères minimum).';
}

$email = filter_var($donnees['email'] ?? '', FILTER_VALIDATE_EMAIL);
if ($email === false) {
    $erreurs['email'] = 'Adresse e-mail invalide.';
}

$places = filter_var($donnees['places'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 5],
]);
if ($places === false) {
    $erreurs['places'] = 'Le nombre de places doit être entre 1 et 5.';
}

// Étape 5 : 422 si des erreurs existent, sinon 201
if ($erreurs !== []) {
    repondre(422, ['errors' => $erreurs]);
}

repondre(201, ['data' => ['nom' => $nom, 'email' => $email, 'places' => $places]]);
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Endpoint de réservation de chambre',
            'exercise_description' => <<<'CODE'
Crée un fichier « reservation.php » qui expose un endpoint de réservation de chambre d'hôtel. Il reçoit en POST un JSON contenant « client », « telephone », « nuits » et « type_chambre » (valeurs autorisées : « simple », « double », « suite »).

Critères de réussite :
- Une méthode autre que POST renvoie un code 405 avec l'en-tête « Allow: POST ».
- Un corps vide ou un JSON invalide renvoie un code 400 avec un message d'erreur.
- Les champs sont validés (client non vide, téléphone d'au moins 8 chiffres, nuits entre 1 et 30, type de chambre autorisé) et toutes les erreurs sont renvoyées ensemble avec un code 422.
- Une réservation valide renvoie un code 201 avec une clé « data » contenant les informations et un montant total calculé selon le type de chambre.
- Toutes les réponses sont en JSON avec l'en-tête « Content-Type » défini et le script s'arrête après chaque réponse.
CODE,
            'exercise_hint' => 'Écris d\'abord la fonction « repondre » et teste le refus des mauvaises méthodes. Pour le type de chambre, un tableau associatif type => prix par nuit sert à la fois à valider (« isset ») et à calculer le montant.',
            'exercise_solution' => <<<'CODE'
<?php
declare(strict_types=1);

function repondre(int $statut, array $corps): never
{
    http_response_code($statut);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($corps, JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    header('Allow: POST');
    repondre(405, ['errors' => ['Méthode non autorisée.']]);
}

$donnees = json_decode(file_get_contents('php://input') ?: '', true);

if (!is_array($donnees)) {
    repondre(400, ['errors' => ['Corps JSON invalide.']]);
}

// Prix par nuit en FCFA selon le type de chambre
$tarifs = ['simple' => 25000, 'double' => 40000, 'suite' => 85000];

$erreurs = [];

$client = trim((string) ($donnees['client'] ?? ''));
if ($client === '') {
    $erreurs['client'] = 'Le nom du client est obligatoire.';
}

$telephone = preg_replace('/\D/', '', (string) ($donnees['telephone'] ?? ''));
if (strlen($telephone) < 8) {
    $erreurs['telephone'] = 'Le téléphone doit contenir au moins 8 chiffres.';
}

$nuits = filter_var($donnees['nuits'] ?? null, FILTER_VALIDATE_INT, [
    'options' => ['min_range' => 1, 'max_range' => 30],
]);
if ($nuits === false) {
    $erreurs['nuits'] = 'Le nombre de nuits doit être entre 1 et 30.';
}

$type = (string) ($donnees['type_chambre'] ?? '');
if (!isset($tarifs[$type])) {
    $erreurs['type_chambre'] = 'Type de chambre invalide (simple, double ou suite).';
}

if ($erreurs !== []) {
    repondre(422, ['errors' => $erreurs]);
}

repondre(201, ['data' => [
    'client' => $client,
    'telephone' => $telephone,
    'nuits' => $nuits,
    'type_chambre' => $type,
    'montant_total' => $tarifs[$type] * $nuits,
]]);
CODE,
        ],

        'Sécurité et tests' => [
            'description' => 'Protège ton application contre les attaques courantes (injection SQL, XSS, mots de passe faibles) et verrouille son comportement avec des tests automatisés.',
            'objective' => 'Utiliser des requêtes préparées PDO, échapper les sorties HTML, hacher un mot de passe avec password_hash et écrire des tests PHPUnit qui vérifient ces comportements.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application qui fonctionne n'est pas forcément une application sûre. Dès qu'elle est en ligne, elle reçoit des données de n'importe qui, y compris de personnes malveillantes. Une faille d'injection SQL peut exposer toute ta base clients, un mot de passe stocké en clair peut être revendu, une page non protégée peut exécuter du code étranger dans le navigateur de tes utilisateurs. Pour une entreprise qui gère des paiements ou des données personnelles, c'est une catastrophe de réputation.

Les tests automatisés complètent cette protection : ils vérifient que ton code se comporte comme prévu et t'avertissent immédiatement quand une modification casse quelque chose.

## Les concepts clés

### Ne jamais faire confiance aux entrées

Toute donnée venant de l'extérieur (formulaire, URL, en-tête, cookie, fichier) est potentiellement dangereuse. Tu la valides à l'entrée, tu l'échappes à la sortie, et tu n'utilises jamais directement une valeur brute dans une requête ou dans du HTML.

### Injection SQL et requêtes préparées

L'injection SQL survient quand on concatène une valeur utilisateur dans une requête. Un attaquant peut alors modifier la requête elle-même. La parade est la requête préparée avec PDO : on écrit la requête avec des paramètres nommés comme « :email », puis on transmet les valeurs séparément avec « execute ». La base distingue ainsi le code des données.

### Faille XSS et échappement

Une faille XSS apparaît quand tu affiches une donnée utilisateur dans une page sans l'échapper : un script injecté s'exécute chez les autres visiteurs. La fonction « htmlspecialchars » avec « ENT_QUOTES » et l'encodage « UTF-8 » transforme les caractères spéciaux en entités inoffensives. Applique-la à chaque sortie HTML dynamique.

### Mots de passe et sessions

Ne stocke jamais un mot de passe en clair ni avec un hachage rapide comme md5. Utilise « password_hash » pour le créer et « password_verify » pour le contrôler : ces fonctions gèrent le sel et l'algorithme automatiquement. Après une connexion réussie, appelle « session_regenerate_id(true) » pour limiter le vol de session.

### Tests automatisés avec PHPUnit

PHPUnit est l'outil de test standard de PHP. Un test est une méthode qui prépare des données, appelle ton code et vérifie le résultat avec des assertions comme « assertSame » ou « assertTrue ». On teste le comportement attendu et aussi les cas limites : valeur vide, valeur négative, entrée malveillante.

## Exemple pas à pas

L'exemple regroupe une classe de sécurité et son test.

- Étape 1 : une classe « Securite » contient une méthode « echapper » qui applique « htmlspecialchars » avec les bons paramètres.
- Étape 2 : une méthode « hasherMotDePasse » utilise « password_hash » et une méthode « verifierMotDePasse » utilise « password_verify ».
- Étape 3 : une fonction « trouverUtilisateur » exécute une requête préparée PDO avec un paramètre nommé.
- Étape 4 : une classe de test PHPUnit vérifie que « echapper » neutralise une balise script.
- Étape 5 : le test vérifie qu'un mot de passe haché n'est pas égal au texte clair et qu'il se valide avec la fonction de contrôle.

Lance les tests avec « vendor/bin/phpunit » et provoque une erreur volontaire pour voir comment l'échec est signalé.

## Erreurs fréquentes

- Concaténer une variable dans une requête SQL : c'est la porte ouverte à l'injection. Utilise des requêtes préparées avec des paramètres.
- Afficher une donnée utilisateur sans « htmlspecialchars » : une balise script injectée s'exécute. Échappe chaque sortie HTML.
- Stocker les mots de passe avec md5 ou en clair : ils sont cassables en quelques secondes. Utilise « password_hash ».
- Comparer des mots de passe avec « == » : tu contournes la logique de hachage. Utilise « password_verify ».
- Afficher les erreurs détaillées en production : tu révèles chemins et requêtes. Journalise l'erreur et affiche un message générique.
- N'écrire que des tests du cas normal : les bugs se cachent dans les cas limites. Teste aussi les valeurs vides, extrêmes et hostiles.

## Bonnes pratiques

- Valide à l'entrée, échappe à la sortie, prépare toutes les requêtes SQL.
- Utilise toujours « password_hash » et « password_verify » avec l'algorithme par défaut.
- Désactive l'affichage des erreurs en production et écris-les dans un journal.
- Écris un test pour chaque bug corrigé afin qu'il ne revienne jamais.
- Garde un test simple, nommé selon le comportement vérifié, avec une seule idée par test.

## Auto-évaluation

- Comment fonctionne une injection SQL et pourquoi une requête préparée l'empêche-t-elle ?
- Qu'est-ce qu'une faille XSS et quelle fonction PHP s'en protège ?
- Pourquoi md5 est-il inadapté au stockage des mots de passe ?
- À quoi servent « password_hash » et « password_verify » ?
- Quels cas limites faut-il tester en plus du cas normal ?

## À retenir

- Valide les entrées, échappe les sorties, prépare les requêtes.
- Les mots de passe se hachent avec « password_hash » et se contrôlent avec « password_verify ».
- N'affiche jamais d'erreurs techniques détaillées aux visiteurs en production.
- Un test automatisé verrouille un comportement et détecte les régressions.
- Les cas limites et hostiles méritent des tests autant que le cas normal.
MD,
            'code_example' => <<<'CODE'
<?php
declare(strict_types=1);

namespace App;

use PDO;

// Étapes 1 à 3 : utilitaires de sécurité
class Securite
{
    // Neutralise le HTML avant l'affichage (protection XSS)
    public static function echapper(string $texte): string
    {
        return htmlspecialchars($texte, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    // Crée un hachage sûr (sel et algorithme gérés par PHP)
    public static function hasherMotDePasse(string $motDePasse): string
    {
        return password_hash($motDePasse, PASSWORD_DEFAULT);
    }

    public static function verifierMotDePasse(string $motDePasse, string $hash): bool
    {
        return password_verify($motDePasse, $hash);
    }

    // Requête préparée : la valeur n'est jamais concaténée dans le SQL
    public static function trouverUtilisateur(PDO $pdo, string $email): ?array
    {
        $requete = $pdo->prepare('SELECT id, nom FROM utilisateurs WHERE email = :email');
        $requete->execute(['email' => $email]);
        $resultat = $requete->fetch(PDO::FETCH_ASSOC);

        return $resultat === false ? null : $resultat;
    }
}

/*
// Étapes 4 et 5 : tests/SecuriteTest.php (PHPUnit)
use PHPUnit\Framework\TestCase;

class SecuriteTest extends TestCase
{
    public function test_echapper_neutralise_une_balise_script(): void
    {
        $sortie = Securite::echapper('<script>alert(1)</script>');
        $this->assertSame('&lt;script&gt;alert(1)&lt;/script&gt;', $sortie);
    }

    public function test_le_hash_differe_du_texte_clair_et_se_verifie(): void
    {
        $hash = Securite::hasherMotDePasse('secret123');
        $this->assertNotSame('secret123', $hash);
        $this->assertTrue(Securite::verifierMotDePasse('secret123', $hash));
        $this->assertFalse(Securite::verifierMotDePasse('autre', $hash));
    }
}
*/
CODE,
            'estimated_minutes' => 80,
            'exercise_title' => 'Sécuriser un formulaire de commentaires',
            'exercise_description' => <<<'CODE'
Tu reçois un module de commentaires vulnérable (requête SQL concaténée, sortie HTML brute, mot de passe en clair). Écris une version sécurisée dans une classe « Commentaires » avec ses tests PHPUnit.

Critères de réussite :
- La méthode « ajouter » utilise une requête préparée PDO avec des paramètres nommés, sans concaténation.
- La méthode « afficher » échappe l'auteur et le texte avec « htmlspecialchars » et « ENT_QUOTES », en UTF-8.
- Une méthode « inscrire » stocke le mot de passe avec « password_hash » et une méthode « connecter » le contrôle avec « password_verify ».
- Les entrées vides ou dépassant 500 caractères sont refusées avec une « InvalidArgumentException ».
- Au moins trois tests PHPUnit passent : échappement d'une balise script, refus d'une entrée vide, vérification d'un mot de passe correct et d'un mot de passe faux.
CODE,
            'exercise_hint' => 'Pour tester sans serveur de base de données, tu peux te concentrer sur les méthodes pures (échappement, validation, hachage). Une base SQLite en mémoire avec « new PDO(\'sqlite::memory:\') » permet aussi de tester « ajouter ».',
            'exercise_solution' => <<<'CODE'
<?php
declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use PDO;

class Commentaires
{
    public function __construct(private PDO $pdo)
    {
    }

    public function ajouter(string $auteur, string $texte): void
    {
        $auteur = trim($auteur);
        $texte = trim($texte);

        if ($auteur === '' || $texte === '' || mb_strlen($texte) > 500) {
            throw new InvalidArgumentException('Commentaire invalide.');
        }

        $requete = $this->pdo->prepare(
            'INSERT INTO commentaires (auteur, texte) VALUES (:auteur, :texte)'
        );
        $requete->execute(['auteur' => $auteur, 'texte' => $texte]);
    }

    public function afficher(string $auteur, string $texte): string
    {
        $flags = ENT_QUOTES | ENT_SUBSTITUTE;

        return '<p><strong>' . htmlspecialchars($auteur, $flags, 'UTF-8') . '</strong> : '
            . htmlspecialchars($texte, $flags, 'UTF-8') . '</p>';
    }

    public function inscrire(string $email, string $motDePasse): void
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO utilisateurs (email, mot_de_passe) VALUES (:email, :hash)'
        );
        $requete->execute([
            'email' => $email,
            'hash' => password_hash($motDePasse, PASSWORD_DEFAULT),
        ]);
    }

    public function connecter(string $email, string $motDePasse): bool
    {
        $requete = $this->pdo->prepare('SELECT mot_de_passe FROM utilisateurs WHERE email = :email');
        $requete->execute(['email' => $email]);
        $hash = $requete->fetchColumn();

        return is_string($hash) && password_verify($motDePasse, $hash);
    }
}

// ===== tests/CommentairesTest.php =====
// use PHPUnit\Framework\TestCase;
//
// class CommentairesTest extends TestCase
// {
//     private Commentaires $service;
//
//     protected function setUp(): void
//     {
//         $pdo = new PDO('sqlite::memory:');
//         $pdo->exec('CREATE TABLE commentaires (auteur TEXT, texte TEXT)');
//         $pdo->exec('CREATE TABLE utilisateurs (email TEXT, mot_de_passe TEXT)');
//         $this->service = new Commentaires($pdo);
//     }
//
//     public function test_afficher_echappe_une_balise_script(): void
//     {
//         $html = $this->service->afficher('Awa', '<script>alert(1)</script>');
//         $this->assertStringNotContainsString('<script>', $html);
//     }
//
//     public function test_une_entree_vide_est_refusee(): void
//     {
//         $this->expectException(InvalidArgumentException::class);
//         $this->service->ajouter('Awa', '   ');
//     }
//
//     public function test_connexion_correcte_et_incorrecte(): void
//     {
//         $this->service->inscrire('awa@example.com', 'secret123');
//         $this->assertTrue($this->service->connecter('awa@example.com', 'secret123'));
//         $this->assertFalse($this->service->connecter('awa@example.com', 'faux'));
//     }
// }
CODE,
        ],

        'Projet final PHP' => [
            'description' => 'Assemble tout ce que tu as appris dans un mini-projet complet : une API de gestion de tâches avec validation, authentification, sécurité et tests.',
            'objective' => 'Livrer une API PHP structurée en classes PSR-4 qui gère le CRUD de tâches, valide les entrées, protège les routes par authentification et dispose de tests automatisés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Jusqu'ici, tu as appris chaque brique séparément. Un vrai projet demande de les assembler : un point d'entrée qui reçoit les requêtes, un routage, des classes qui portent la logique métier, une base de données, une validation rigoureuse, une authentification et des tests. C'est exactement ce que fait un développeur dans une mission client, par exemple pour livrer une application de suivi d'interventions ou de gestion de tâches d'équipe.

Ce projet final est aussi ta première pièce de portfolio : un dépôt propre, lisible et testé, que tu peux montrer à un employeur ou à un client.

## Les concepts clés

### Architecture en couches

Sépare les responsabilités. Le point d'entrée (« public/index.php ») reçoit la requête. Un routeur choisit le traitement. Un contrôleur lit les données et formate la réponse. Un service ou dépôt (repository) parle à la base de données. Cette séparation rend chaque partie testable et remplaçable. Chaque classe respecte PSR-4 dans le dossier « src/ ».

### CRUD et conception de l'API

CRUD signifie créer, lire, mettre à jour, supprimer. Pour une ressource « tâches », les routes classiques sont : GET sur la liste, GET sur une tâche, POST pour créer, PUT ou PATCH pour modifier, DELETE pour supprimer. Chaque route renvoie un code de statut cohérent : 200, 201, 204, 404 ou 422 selon le cas.

### Authentification par jeton

Pour protéger les routes, une approche simple est le jeton d'API : à la connexion, le serveur génère un jeton aléatoire avec « bin2hex(random_bytes(32)) », le stocke (idéalement sous forme hachée) et le client l'envoie dans l'en-tête « Authorization ». Un contrôle avant chaque route protégée refuse les requêtes sans jeton valide avec un code 401.

### Tests d'ensemble

Écris des tests unitaires pour la validation et la logique métier, et au moins un test d'intégration qui parcourt un scénario complet : création d'une tâche, lecture, modification puis suppression. Une base SQLite en mémoire rend ces tests rapides et indépendants.

## Exemple pas à pas

L'exemple montre le cœur du projet : un dépôt de tâches et un contrôleur.

- Étape 1 : la classe « TacheRepository » reçoit un objet PDO et expose « toutes », « trouver », « creer » et « supprimer », toutes avec des requêtes préparées.
- Étape 2 : la classe « TacheValidator » vérifie que le titre est présent et que le statut appartient à une liste autorisée.
- Étape 3 : le contrôleur « TacheController » appelle le validateur avant de créer et renvoie 422 avec les erreurs si besoin.
- Étape 4 : une route inconnue ou une tâche absente produit une réponse 404 en JSON.
- Étape 5 : le point d'entrée choisit la route selon la méthode et le chemin, après avoir contrôlé le jeton d'authentification.

Lis le code dans cet ordre, puis lance quelques requêtes avec « curl » pour voir chaque code de statut.

## Erreurs fréquentes

- Mettre toute la logique dans « index.php » : le fichier devient illisible et intestable. Découpe en classes avec des responsabilités claires.
- Oublier de valider à la modification : on valide souvent à la création seulement. Applique les mêmes règles partout.
- Autoriser n'importe quel utilisateur à modifier n'importe quelle tâche : vérifie que la tâche appartient bien à l'utilisateur authentifié.
- Renvoyer des erreurs HTML au lieu de JSON : les clients de l'API ne peuvent pas les lire. Capture les exceptions et réponds toujours en JSON.
- Livrer sans tests ni documentation : personne ne sait comment lancer le projet. Ajoute un « README » avec les commandes et exemples.
- Commiter le fichier « .env » ou la base de données : tu exposes des secrets. Ignore-les dans « .gitignore » et fournis un « .env.example ».

## Bonnes pratiques

- Commence petit : une route fonctionnelle de bout en bout avant d'ajouter les autres.
- Fais des commits fréquents avec des messages clairs qui décrivent le changement.
- Écris le test d'une règle métier dès que tu la codes.
- Garde les secrets hors du dépôt et fournis un exemple de configuration.
- Relis ton propre code comme un étranger : les noms et la structure doivent se comprendre sans explication.

## Auto-évaluation

- Comment répartis-tu les responsabilités entre point d'entrée, contrôleur et dépôt ?
- Quels codes de statut renvoie chaque opération CRUD ?
- Comment fonctionne une authentification par jeton, de la génération au contrôle ?
- Pourquoi utiliser une base SQLite en mémoire pour les tests ?
- Que doit contenir le README pour qu'un autre développeur lance ton projet ?

## À retenir

- Un projet réel assemble routage, logique métier, base de données, validation, sécurité et tests.
- Des classes PSR-4 aux responsabilités claires rendent le code testable.
- Chaque route renvoie un code de statut cohérent et un JSON prévisible.
- Les routes sensibles sont protégées par un jeton vérifié avant tout traitement.
- Un dépôt propre avec README et tests est un livrable professionnel.
MD,
            'code_example' => <<<'CODE'
<?php
declare(strict_types=1);

namespace App;

use InvalidArgumentException;
use PDO;

// Étape 2 : règles de validation d'une tâche
class TacheValidator
{
    private const STATUTS = ['a_faire', 'en_cours', 'terminee'];

    // Retourne un tableau d'erreurs (vide si tout est correct)
    public function valider(array $donnees): array
    {
        $erreurs = [];

        $titre = trim((string) ($donnees['titre'] ?? ''));
        if ($titre === '' || mb_strlen($titre) > 120) {
            $erreurs['titre'] = 'Le titre est obligatoire (120 caractères maximum).';
        }

        $statut = $donnees['statut'] ?? 'a_faire';
        if (!in_array($statut, self::STATUTS, true)) {
            $erreurs['statut'] = 'Statut invalide.';
        }

        return $erreurs;
    }
}

// Étape 1 : accès aux données avec requêtes préparées
class TacheRepository
{
    public function __construct(private PDO $pdo)
    {
    }

    public function toutes(int $utilisateurId): array
    {
        $requete = $this->pdo->prepare('SELECT * FROM taches WHERE utilisateur_id = :uid ORDER BY id DESC');
        $requete->execute(['uid' => $utilisateurId]);

        return $requete->fetchAll(PDO::FETCH_ASSOC);
    }

    public function creer(int $utilisateurId, string $titre, string $statut): int
    {
        $requete = $this->pdo->prepare(
            'INSERT INTO taches (utilisateur_id, titre, statut) VALUES (:uid, :titre, :statut)'
        );
        $requete->execute(['uid' => $utilisateurId, 'titre' => $titre, 'statut' => $statut]);

        return (int) $this->pdo->lastInsertId();
    }

    public function supprimer(int $utilisateurId, int $id): bool
    {
        // Filtre sur l'utilisateur : on ne supprime que ses propres tâches
        $requete = $this->pdo->prepare('DELETE FROM taches WHERE id = :id AND utilisateur_id = :uid');
        $requete->execute(['id' => $id, 'uid' => $utilisateurId]);

        return $requete->rowCount() > 0;
    }
}

// Étapes 3 et 4 : le contrôleur valide puis répond avec le bon statut
class TacheController
{
    public function __construct(private TacheRepository $depot, private TacheValidator $validateur)
    {
    }

    // Retourne [code HTTP, corps] ; le point d'entrée se charge d'envoyer le JSON
    public function creer(int $utilisateurId, array $donnees): array
    {
        $erreurs = $this->validateur->valider($donnees);
        if ($erreurs !== []) {
            return [422, ['errors' => $erreurs]];
        }

        $id = $this->depot->creer($utilisateurId, trim($donnees['titre']), $donnees['statut'] ?? 'a_faire');

        return [201, ['data' => ['id' => $id]]];
    }

    public function supprimer(int $utilisateurId, int $id): array
    {
        return $this->depot->supprimer($utilisateurId, $id)
            ? [204, []]
            : [404, ['errors' => ['Tâche introuvable.']]];
    }
}
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Projet final : API de gestion de tâches',
            'exercise_description' => <<<'CODE'
Construis une API REST de gestion de tâches en PHP, structurée avec Composer et PSR-4, avec une base SQLite ou MySQL. Livre un dépôt propre qui se lance avec le serveur intégré de PHP.

Livrables :
- Un « composer.json » avec l'autoloading PSR-4 et la structure « src/ », « public/index.php », « tests/ ».
- Les routes CRUD sur « /taches » (liste, lecture, création, modification, suppression) avec les codes 200, 201, 204, 404 et 422.
- Une validation centralisée (titre obligatoire, statut parmi une liste autorisée) et des requêtes préparées partout.
- Une authentification par jeton : inscription, connexion avec « password_hash » et « password_verify », puis rejet des routes protégées sans jeton valide (code 401).
- Au moins quatre tests PHPUnit (validation, création, suppression d'une tâche inexistante, accès sans jeton) et un « README » expliquant l'installation et les appels « curl ».
CODE,
            'exercise_hint' => 'Avance route par route : fais fonctionner la création de bout en bout avec ses tests avant de passer à la suite. Ajoute l\'authentification en dernier, en protégeant les routes dans le point d\'entrée avant le routage.',
            'exercise_solution' => <<<'CODE'
<?php
// Point d'entrée : public/index.php (solution condensée)
declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use App\TacheController;
use App\TacheRepository;
use App\TacheValidator;

function repondre(int $statut, array $corps = []): never
{
    http_response_code($statut);
    if ($statut !== 204) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($corps, JSON_UNESCAPED_UNICODE);
    }
    exit;
}

$pdo = new PDO('sqlite:' . __DIR__ . '/../database.sqlite');
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec('CREATE TABLE IF NOT EXISTS utilisateurs (id INTEGER PRIMARY KEY, email TEXT UNIQUE, mot_de_passe TEXT, jeton TEXT)');
$pdo->exec('CREATE TABLE IF NOT EXISTS taches (id INTEGER PRIMARY KEY, utilisateur_id INTEGER, titre TEXT, statut TEXT)');

$methode = $_SERVER['REQUEST_METHOD'];
$chemin = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$donnees = json_decode(file_get_contents('php://input') ?: '', true) ?? [];

try {
    // Inscription
    if ($methode === 'POST' && $chemin === '/inscription') {
        $requete = $pdo->prepare('INSERT INTO utilisateurs (email, mot_de_passe) VALUES (:e, :h)');
        $requete->execute(['e' => $donnees['email'] ?? '', 'h' => password_hash((string) ($donnees['mot_de_passe'] ?? ''), PASSWORD_DEFAULT)]);
        repondre(201, ['data' => ['id' => (int) $pdo->lastInsertId()]]);
    }

    // Connexion : génère un jeton aléatoire
    if ($methode === 'POST' && $chemin === '/connexion') {
        $requete = $pdo->prepare('SELECT id, mot_de_passe FROM utilisateurs WHERE email = :e');
        $requete->execute(['e' => $donnees['email'] ?? '']);
        $u = $requete->fetch(PDO::FETCH_ASSOC);

        if (!$u || !password_verify((string) ($donnees['mot_de_passe'] ?? ''), $u['mot_de_passe'])) {
            repondre(401, ['errors' => ['Identifiants invalides.']]);
        }

        $jeton = bin2hex(random_bytes(32));
        $pdo->prepare('UPDATE utilisateurs SET jeton = :j WHERE id = :id')->execute(['j' => hash('sha256', $jeton), 'id' => $u['id']]);
        repondre(200, ['data' => ['jeton' => $jeton]]);
    }

    // Routes protégées : contrôle du jeton Bearer
    $entete = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    $jeton = str_starts_with($entete, 'Bearer ') ? substr($entete, 7) : '';
    $requete = $pdo->prepare('SELECT id FROM utilisateurs WHERE jeton = :j');
    $requete->execute(['j' => hash('sha256', $jeton)]);
    $utilisateurId = $jeton !== '' ? $requete->fetchColumn() : false;

    if ($utilisateurId === false) {
        repondre(401, ['errors' => ['Authentification requise.']]);
    }
    $utilisateurId = (int) $utilisateurId;

    $controleur = new TacheController(new TacheRepository($pdo), new TacheValidator());
    $depot = new TacheRepository($pdo);

    if ($chemin === '/taches' && $methode === 'GET') {
        repondre(200, ['data' => $depot->toutes($utilisateurId)]);
    }

    if ($chemin === '/taches' && $methode === 'POST') {
        [$code, $corps] = $controleur->creer($utilisateurId, $donnees);
        repondre($code, $corps);
    }

    if (preg_match('#^/taches/(\d+)$#', $chemin, $m) && $methode === 'DELETE') {
        [$code, $corps] = $controleur->supprimer($utilisateurId, (int) $m[1]);
        repondre($code, $corps);
    }

    repondre(404, ['errors' => ['Route introuvable.']]);
} catch (Throwable $e) {
    // Journalise l'erreur, ne révèle aucun détail au client
    error_log($e->getMessage());
    repondre(500, ['errors' => ['Erreur interne du serveur.']]);
}
CODE,
        ],
    ],
];
