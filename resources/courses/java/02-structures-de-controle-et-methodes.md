---
title: Structures de contrôle et méthodes
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Un programme qui exécute toujours les mêmes lignes dans le même ordre n'est pas très utile. Les **structures de contrôle** permettent de décider (si, sinon, selon), de répéter (pour chaque, tant que), et les **méthodes** permettent de découper un programme en morceaux nommés et réutilisables. Ce sont les outils qui transforment une suite d'instructions en vrai raisonnement.

À la fin du chapitre, tu seras capable de :

- écrire des conditions avec `if`, `else`, l'opérateur ternaire et `switch` ;
- utiliser les expressions `switch` modernes de Java 21 ;
- répéter du code avec `for`, `for` amélioré, `while` et `do while` ;
- manipuler des tableaux à une et deux dimensions ;
- écrire des méthodes avec paramètres, valeur de retour et surcharge ;
- comprendre la portée des variables et le passage des arguments.

Prérequis : le chapitre « Premiers pas avec Java » (variables, types, `String`, `Scanner`). Prévois deux heures.

## Les conditions : if, else et le ternaire

La structure `if` exécute un bloc seulement si une condition booléenne est vraie :

```java
int note = 14;

if (note >= 16) {
    System.out.println("Très bien");
} else if (note >= 12) {
    System.out.println("Bien");
} else if (note >= 10) {
    System.out.println("Passable");
} else {
    System.out.println("Insuffisant");
}
```

Les conditions sont évaluées de haut en bas, et **seule la première branche vraie** s'exécute. L'ordre compte donc : en testant d'abord `note >= 10`, tu afficherais « Passable » pour un 18.

Pour combiner des tests, utilise `&&` (et), `||` (ou) et `!` (non). Java évalue en **court-circuit** : dans `a && b`, si `a` est faux, `b` n'est jamais évalué. On s'en sert pour éviter des erreurs :

```java
String nom = null;

if (nom != null && nom.length() > 3) {
    System.out.println("Nom long");
}
```

Sans le test `nom != null` placé en premier, `nom.length()` lèverait une `NullPointerException`.

L'**opérateur ternaire** condense un `if` qui produit une valeur :

```java
String statut = note >= 10 ? "Admis" : "Refusé";
```

> **Astuce** : réserve le ternaire aux cas simples. Dès qu'il contient un autre ternaire, repasse à un `if` pour garder un code lisible.

Toujours mettre les accolades, même pour une seule instruction : sans elles, une ligne ajoutée plus tard se retrouve hors du bloc et provoque des bugs sournois.

## Le switch moderne

Quand tu compares une valeur à plusieurs cas, `switch` est plus clair qu'une cascade de `if`. Java 21 propose les **expressions switch** : elles renvoient une valeur, n'ont pas de `break` et ne « tombent » pas dans le cas suivant.

```java
String jour = "samedi";

String type = switch (jour) {
    case "samedi", "dimanche" -> "Week-end";
    case "lundi", "mardi", "mercredi", "jeudi", "vendredi" -> "Semaine";
    default -> "Jour inconnu";
};
```

Quand un cas demande plusieurs instructions, utilise un bloc avec `yield` pour produire la valeur :

```java
int mois = 2;
int jours = switch (mois) {
    case 4, 6, 9, 11 -> 30;
    case 2 -> {
        boolean bissextile = false;
        yield bissextile ? 29 : 28;
    }
    default -> 31;
};
```

L'ancienne forme à deux-points avec `break` existe toujours, mais elle oublie facilement un `break` et enchaîne les cas par erreur. Préfère la flèche.

Java 21 permet aussi le **filtrage par motifs** dans un `switch`, très pratique avec des types d'objets. Tu le retrouveras avec les records dans un chapitre ultérieur :

```java
Object valeur = 42;
String description = switch (valeur) {
    case Integer i when i > 100 -> "Grand entier";
    case Integer i -> "Entier : " + i;
    case String s -> "Texte de " + s.length() + " caractères";
    default -> "Autre type";
};
```

:::quiz
Quel est l'avantage principal de l'expression switch avec flèche (arrow) par rapport à l'ancien switch ?
- [ ] Elle est plus rapide à l'exécution
- [x] Elle renvoie une valeur et ne tombe pas dans le cas suivant sans break
- [ ] Elle accepte uniquement des nombres
- [ ] Elle remplace complètement la boucle for
> La forme avec flèche est une expression : elle produit une valeur, et chaque cas est indépendant. On évite ainsi l'oubli classique du break.
:::

## Les boucles

### La boucle for

Utilise `for` quand tu sais combien de fois répéter. Elle a trois parties : initialisation, condition, incrément.

```java
for (int i = 1; i <= 5; i++) {
    System.out.println("Ligne " + i);
}
```

La variable `i` n'existe que dans la boucle. Les boucles s'imbriquent, par exemple pour afficher une table de multiplication :

```java
for (int ligne = 1; ligne <= 3; ligne++) {
    for (int colonne = 1; colonne <= 3; colonne++) {
        System.out.print(ligne * colonne + "\t");
    }
    System.out.println();
}
```

### Le for amélioré

Pour parcourir tous les éléments d'un tableau ou d'une collection, le *for-each* est plus sûr, car il n'y a plus d'index à gérer :

```java
String[] langages = {"Java", "PHP", "Dart"};

for (String langage : langages) {
    System.out.println(langage);
}
```

Lis-le « pour chaque `langage` dans `langages` ».

### while et do while

`while` répète tant qu'une condition est vraie, et peut ne jamais s'exécuter. `do while` s'exécute au moins une fois, puis teste :

```java
int tentatives = 0;
while (tentatives < 3) {
    System.out.println("Tentative " + (tentatives + 1));
    tentatives++;
}

int choix;
do {
    System.out.print("Entre un nombre positif : ");
    choix = new java.util.Scanner(System.in).nextInt();
} while (choix <= 0);
```

### break et continue

`break` quitte immédiatement la boucle ; `continue` saute à l'itération suivante :

```java
for (int n = 1; n <= 10; n++) {
    if (n % 2 == 0) {
        continue;           // ignore les nombres pairs
    }
    if (n > 7) {
        break;              // arrête tout après 7
    }
    System.out.println(n);  // 1, 3, 5, 7
}
```

> **Attention** : une boucle `while` dont la condition ne devient jamais fausse tourne à l'infini. Vérifie toujours qu'une variable de la condition évolue à chaque tour.

## Les tableaux

Un **tableau** stocke un nombre **fixe** d'éléments du même type. Sa taille est définie à la création et ne change plus.

```java
int[] notes = new int[4];        // quatre zéros
notes[0] = 12;
notes[1] = 15;

int[] moyennes = {10, 14, 18};   // création avec valeurs
System.out.println(moyennes.length);   // 3
System.out.println(moyennes[2]);       // 18
```

Les indices commencent à **0**. Accéder à `moyennes[3]` lève une `ArrayIndexOutOfBoundsException`, l'erreur la plus classique avec les tableaux.

La classe utilitaire `Arrays` offre l'essentiel :

```java
import java.util.Arrays;

int[] valeurs = {5, 2, 9, 1};
Arrays.sort(valeurs);
System.out.println(Arrays.toString(valeurs));   // [1, 2, 5, 9]
System.out.println(Arrays.stream(valeurs).sum()); // 17
```

Un tableau à deux dimensions est un tableau de tableaux, pratique pour une grille ou un tableau de notes :

```java
int[][] grille = {
    {1, 2, 3},
    {4, 5, 6}
};
System.out.println(grille[1][2]);   // 6
```

Comme la taille est fixe, tu utiliseras plutôt des listes (`ArrayList`) dès que le nombre d'éléments varie. C'est le sujet du chapitre sur les collections.

## Les méthodes

Une **méthode** est un bloc de code nommé qu'on peut appeler autant de fois qu'on veut. Elle évite la duplication et donne un nom à une intention.

```java
public class Calculs {

    static int additionner(int a, int b) {
        return a + b;
    }

    static void saluer(String prenom) {
        System.out.println("Bonjour " + prenom);
    }

    public static void main(String[] args) {
        int somme = additionner(3, 4);
        saluer("Awa");
        System.out.println(somme);   // 7
    }
}
```

Une signature se lit ainsi : modificateurs, **type de retour**, **nom**, **paramètres**. `void` signifie « ne renvoie rien ». `return` termine la méthode et renvoie la valeur. Le mot `static` indique que la méthode appartient à la classe et non à un objet ; il est nécessaire ici parce que `main` est lui-même statique. Il sera remplacé par des méthodes d'instance au prochain chapitre.

### La surcharge

Plusieurs méthodes peuvent porter le même nom si leurs paramètres diffèrent (nombre ou types). C'est la **surcharge** :

```java
static double aire(double rayon) {
    return Math.PI * rayon * rayon;
}

static double aire(double largeur, double hauteur) {
    return largeur * hauteur;
}
```

Le compilateur choisit la bonne version selon les arguments. Changer seulement le type de retour ne suffit pas.

### Les paramètres variables

Un paramètre `int...` accepte un nombre quelconque d'arguments, qu'il reçoit sous forme de tableau :

```java
static int somme(int... nombres) {
    int total = 0;
    for (int n : nombres) {
        total += n;
    }
    return total;
}

// somme(1, 2, 3) renvoie 6 ; somme() renvoie 0
```

### Passage par valeur

Java passe **toujours** les arguments par valeur : la méthode reçoit une copie. Pour un type primitif, modifier le paramètre n'affecte pas l'original :

```java
static void incrementer(int x) {
    x = x + 1;   // modifie la copie locale
}

int n = 5;
incrementer(n);
System.out.println(n);   // toujours 5
```

Pour un objet ou un tableau, la copie est celle de la **référence** : la méthode ne peut pas remplacer l'objet de l'appelant, mais elle peut modifier son contenu. Retiens donc qu'un tableau passé en paramètre peut être modifié à l'intérieur de la méthode.

### La portée des variables

Une variable n'existe que dans le bloc où elle est déclarée. Une variable de boucle disparaît après la boucle, un paramètre disparaît à la fin de la méthode. Évite de réutiliser un même nom dans des blocs imbriqués : Java le refuse d'ailleurs pour les variables locales.

:::quiz
Que contient la variable n après ce code ? static void incrementer(int x) { x = x + 1; } puis int n = 5; incrementer(n);
- [x] 5
- [ ] 6
- [ ] 0
- [ ] Une erreur de compilation
> Java passe les arguments par valeur : la méthode modifie une copie de n. La variable de l'appelant reste inchangée.
:::

## Écrire de bonnes méthodes

Une méthode devrait faire **une seule chose** et porter un nom de verbe qui la décrit : `calculerMoyenne`, `estPremier`, `afficherMenu`. Une méthode qui renvoie un booléen commence souvent par `est` ou `a`.

Voici un exemple complet, un petit jeu de devinette qui combine tout le chapitre :

```java
import java.util.Random;
import java.util.Scanner;

public class Devinette {

    static int lireEntier(Scanner clavier, String question) {
        System.out.print(question);
        return Integer.parseInt(clavier.nextLine().trim());
    }

    static String indice(int proposition, int secret) {
        if (proposition < secret) {
            return "Plus grand";
        }
        return proposition > secret ? "Plus petit" : "Gagné";
    }

    public static void main(String[] args) {
        var clavier = new Scanner(System.in);
        int secret = new Random().nextInt(1, 101);
        int essais = 0;
        String reponse;

        do {
            int proposition = lireEntier(clavier, "Ta proposition : ");
            essais++;
            reponse = indice(proposition, secret);
            System.out.println(reponse);
        } while (!reponse.equals("Gagné"));

        System.out.println("Trouvé en " + essais + " essais.");
    }
}
```

Observe comment `main` reste court : le travail de lecture et de décision est délégué à deux méthodes bien nommées.

## Atelier guidé : un mini-jeu et des statistiques

Compte une heure et demie. Crée un projet Maven (ou un simple fichier) avec une classe `Outils`.

1. Écris `static boolean estPair(int n)` et teste-la avec une boucle de 1 à 10.
2. Écris `static boolean estPremier(int n)` avec une boucle qui teste les diviseurs jusqu'à la racine carrée.
3. Affiche tous les nombres premiers entre 1 et 50 avec `for` et `continue`.
4. Écris `static double moyenne(int... notes)` et protège le cas où aucune note n'est fournie.
5. Écris `static String mention(double moyenne)` avec une expression `switch` ou une cascade de `if`.
6. Dans `main`, crée un tableau de dix notes, trie-le avec `Arrays.sort`, puis affiche le minimum, le maximum et la moyenne.
7. Recopie la classe `Devinette` ci-dessus et ajoute une limite de 7 essais avec `break`.
8. Surcharge `afficher` pour qu'elle accepte soit un `int`, soit un `String`, soit un tableau d'entiers.

Pour t'auto-évaluer : sans regarder le cours, explique la différence entre `break` et `continue`, entre `while` et `do while`, et ce que « passage par valeur » veut dire.

## Erreurs fréquentes

- **Point-virgule après `if (...)` ou `for (...)`.** Il termine l'instruction : le bloc qui suit s'exécute toujours.
- **Une erreur d'une unité dans la boucle.** Écrire `i <= tableau.length` au lieu de `i < tableau.length` mène à une `ArrayIndexOutOfBoundsException`.
- **Oublier `break` dans l'ancien `switch`.** Les cas suivants s'exécutent aussi. La flèche règle ce problème.
- **Boucle infinie.** La variable de condition n'est jamais modifiée.
- **Oublier `return` dans un chemin.** Une méthode non `void` doit renvoyer une valeur dans tous les cas, sinon le compilateur proteste.
- **Penser qu'un paramètre `int` modifié change l'original.** Java passe par valeur.
- **Comparer avec `=` au lieu de `==`.** `if (x = 5)` ne compile pas pour un entier, mais c'est une confusion fréquente.

## Bonnes pratiques

- Mets toujours des accolades, même pour une seule ligne.
- Préfère le *for-each* quand tu n'as pas besoin de l'index.
- Garde chaque méthode courte (une vingtaine de lignes au maximum) et dédiée à une tâche.
- Nomme les méthodes avec un verbe, les booléens avec `est`, `a` ou `peut`.
- Sors tôt d'une méthode avec un `return` pour éviter les `if` imbriqués sur cinq niveaux.
- Utilise `switch` avec flèche pour les choix multiples, et évite les « nombres magiques » en les nommant par des constantes.
- Teste les cas limites : tableau vide, zéro, valeur négative.

## À retenir

- `if`, `else` et `switch` choisissent un chemin ; l'expression `switch` avec flèche renvoie une valeur et ne tombe pas dans le cas suivant.
- `for` répète un nombre connu de fois, *for-each* parcourt une collection, `while` teste avant, `do while` après.
- Un tableau a une taille fixe, des indices de 0 à `length - 1`, et `Arrays` fournit les outils de base.
- Une méthode a un type de retour, un nom, des paramètres ; elle peut être surchargée.
- Java passe les arguments **par valeur** ; pour un objet, la valeur est la référence.
- Des méthodes courtes et bien nommées rendent un programme lisible et testable.
