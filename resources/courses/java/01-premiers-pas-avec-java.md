---
title: Premiers pas avec Java
minutes: 90
level: beginner
---

## Ce que tu vas apprendre

Java est l'un des langages les plus utilisés au monde : applications d'entreprise, banques, Android, services web, outils de données. Il est réputé pour sa **robustesse**, son typage strict et son écosystème immense. Ce chapitre t'installe dans le langage : comment un programme Java s'écrit, se compile et s'exécute, et comment manipuler les données de base.

À la fin du chapitre, tu seras capable de :

- expliquer la différence entre JDK, JRE et JVM ;
- compiler et exécuter un programme avec `javac` et `java`, puis avec Maven ;
- déclarer des variables avec les types primitifs, `String` et `var` ;
- utiliser les opérateurs, les conversions et les blocs de texte ;
- lire une saisie au clavier avec `Scanner` ;
- afficher des résultats formatés.

Prérequis : savoir ouvrir un terminal et un éditeur de code (VS Code, IntelliJ IDEA Community). Aucune connaissance de Java n'est nécessaire. Prévois une heure et demie. Installe un **JDK 21** (Temurin ou OpenJDK) avant de commencer.

## Comment Java fonctionne

Quand tu écris un programme en C, le compilateur le transforme directement en code machine pour ton processeur. Java choisit une autre voie, avec deux étapes :

1. le compilateur `javac` transforme ton code source (`.java`) en **bytecode** (`.class`), un langage intermédiaire ;
2. la **JVM** (*Java Virtual Machine*) lit ce bytecode et l'exécute sur ta machine.

Le bytecode est identique partout. La JVM, elle, existe pour Windows, macOS et Linux. C'est la promesse historique de Java : *write once, run anywhere*. Tu compiles une fois, ton programme tourne sur un serveur Linux comme sur ton portable.

Trois sigles reviennent sans cesse :

| Sigle | Signification | Rôle |
| --- | --- | --- |
| **JVM** | Java Virtual Machine | Exécute le bytecode |
| **JRE** | Java Runtime Environment | JVM plus les bibliothèques standard, pour **exécuter** |
| **JDK** | Java Development Kit | JRE plus le compilateur et les outils, pour **développer** |

Pour développer, tu installes donc le **JDK**. Vérifie l'installation dans un terminal :

```bash
java -version
javac -version
```

Les deux commandes doivent afficher une version 21. Si `javac` est introuvable, c'est que tu n'as installé qu'un JRE, ou que la variable `PATH` ne pointe pas vers le JDK.

> **À retenir** : le JDK sert à développer, la JVM à exécuter. Le fichier `.class` est du bytecode, pas du code machine.

## Ton premier programme

Crée un dossier `hello` et un fichier `Main.java` :

```java
public class Main {
    public static void main(String[] args) {
        System.out.println("Bonjour DevRoad !");
    }
}
```

Chaque mot a une raison d'être :

- `public class Main` déclare une classe. En Java, **tout le code vit dans une classe**. Le nom du fichier doit être identique à celui de la classe publique : `Main.java` pour `Main` ;
- `public static void main(String[] args)` est le **point d'entrée**. La JVM cherche exactement cette signature pour démarrer ;
- `System.out.println` affiche une ligne dans la console ;
- chaque instruction se termine par un point-virgule `;`, et les blocs sont délimités par des accolades.

Compile puis exécute :

```bash
javac Main.java
java Main
```

La première commande produit `Main.class`, la seconde le lance. Depuis Java 11, tu peux aussi exécuter directement un fichier simple avec `java Main.java`, pratique pour tester une idée en quelques secondes.

:::quiz
Que produit la commande `javac Main.java` ?
- [ ] Le programme s'exécute et affiche son résultat
- [x] Un fichier Main.class contenant du bytecode
- [ ] Un exécutable Windows .exe
- [ ] Une archive JAR prête à être déployée
> javac compile le code source en bytecode (fichier .class). C'est ensuite la commande java, donc la JVM, qui exécute ce bytecode.
:::

## Les variables et les types primitifs

Java est **statiquement typé** : chaque variable a un type fixé à la déclaration, et le compilateur refuse les incohérences avant même que le programme ne tourne. C'est une grande force : beaucoup d'erreurs sont détectées à la compilation.

```java
int age = 28;
double prix = 1499.99;
boolean actif = true;
char initiale = 'S';
long population = 3_000_000_000L;
```

Voici les huit types primitifs. Tu utiliseras surtout les quatre premiers de ce tableau.

| Type | Contenu | Exemple |
| --- | --- | --- |
| `int` | entier sur 32 bits | `42` |
| `double` | décimal sur 64 bits | `3.14` |
| `boolean` | vrai ou faux | `true` |
| `char` | un caractère | `'A'` |
| `long` | grand entier sur 64 bits | `10_000_000_000L` |
| `float` | décimal sur 32 bits | `2.5f` |
| `short` et `byte` | petits entiers | rarement utilisés |

Quelques détails importants :

- le suffixe `L` signale un littéral `long`, le suffixe `f` un `float` ;
- les underscores dans les nombres (`1_000_000`) améliorent la lisibilité sans changer la valeur ;
- un `char` s'écrit entre **apostrophes**, une chaîne entre **guillemets**.

### Les constantes avec final

Une variable déclarée `final` ne peut être affectée qu'une seule fois. Par convention, les constantes s'écrivent en majuscules :

```java
final double TVA = 0.18;
final int MINUTES_PAR_HEURE = 60;
```

### L'inférence de type avec var

Depuis Java 10, `var` laisse le compilateur déduire le type à partir de la valeur. Le typage reste statique : `var` n'est pas un type dynamique.

```java
var nom = "Awa";          // String
var total = 12 * 3;       // int
var moyenne = 14.5;       // double
```

Utilise `var` quand le type est évident à la lecture. Quand il ne l'est pas, écris-le : un code lisible vaut mieux qu'un code court.

## Les chaînes de caractères

`String` n'est pas un type primitif mais une **classe**, d'où la majuscule. Une chaîne est **immuable** : aucune méthode ne la modifie, toutes en renvoient une nouvelle.

```java
String prenom = "Kouadio";
String message = "Bonjour " + prenom;

System.out.println(message.length());          // 15
System.out.println(message.toUpperCase());     // BONJOUR KOUADIO
System.out.println(prenom.charAt(0));          // K
System.out.println(message.contains("jour"));  // true
System.out.println("  texte ".strip());        // texte
System.out.println("a,b,c".replace(",", ";")); // a;b;c
```

### Comparer des chaînes

C'est le piège numéro un des débutants. L'opérateur `==` compare les **références** (est-ce le même objet en mémoire ?), pas le contenu. Pour comparer le texte, utilise `equals` :

```java
String a = new String("java");
String b = new String("java");

System.out.println(a == b);          // false : deux objets différents
System.out.println(a.equals(b));     // true : même contenu
System.out.println("JAVA".equalsIgnoreCase(a)); // true
```

### Formater du texte

Pour insérer des valeurs dans une chaîne, `formatted` (ou `String.format`) est plus propre que les concaténations :

```java
String ligne = "%s a %d ans et paie %.2f FCFA".formatted("Awa", 28, 1500.5);
System.out.println(ligne); // Awa a 28 ans et paie 1500,50 FCFA
```

Les codes courants sont `%s` (texte), `%d` (entier), `%.2f` (décimal avec deux chiffres) et `%n` (saut de ligne). Le séparateur décimal dépend de la langue configurée sur ta machine.

### Les blocs de texte

Pour un texte sur plusieurs lignes (JSON, SQL, HTML), les **text blocks** de Java 15+ évitent les `\n` et les guillemets échappés :

```java
String json = """
        {
          "titre": "Java",
          "niveau": "débutant"
        }
        """;
```

Le bloc commence et se termine par trois guillemets. L'indentation commune est automatiquement retirée.

:::quiz
Quelle expression compare correctement le contenu de deux chaînes a et b ?
- [ ] a == b
- [x] a.equals(b)
- [ ] a = b
- [ ] a.compare(b)
> equals compare le contenu. L'opérateur == compare les références, c'est-à-dire l'identité des objets, ce qui donne souvent un résultat inattendu avec des chaînes.
:::

## Les opérateurs et les conversions

Les opérateurs arithmétiques sont `+`, `-`, `*`, `/` et `%` (le reste de la division). Attention à la division entière :

```java
System.out.println(7 / 2);     // 3 : division entière, la partie décimale est perdue
System.out.println(7 / 2.0);   // 3.5
System.out.println(7 % 2);     // 1
```

Si les deux opérandes sont des entiers, le résultat est un entier. Pour obtenir un décimal, il faut qu'au moins un opérande soit décimal.

Les opérateurs de comparaison (`==`, `!=`, `<`, `>`, `<=`, `>=`) renvoient un `boolean`. Les opérateurs logiques sont `&&` (et), `||` (ou) et `!` (non). Les raccourcis `+=`, `-=`, `++` et `--` modifient une variable en place.

### Les conversions de type

Convertir vers un type plus large est automatique ; vers un type plus étroit, il faut un **cast** explicite, car de l'information peut se perdre :

```java
int entier = 10;
double decimal = entier;         // automatique : 10.0

double pi = 3.99;
int tronque = (int) pi;          // cast : 3, la partie décimale est supprimée

String texte = "42";
int nombre = Integer.parseInt(texte);   // 42
double d = Double.parseDouble("3.5");   // 3.5
String retour = String.valueOf(nombre); // "42"
```

`Integer.parseInt` lance une exception si le texte n'est pas un nombre valide. Tu apprendras à la gérer au chapitre sur les exceptions.

> **Attention** : les calculs d'argent ne se font jamais avec `double`. Les décimaux binaires sont approximatifs : `0.1 + 0.2` donne `0.30000000000000004`. Pour des montants, utilise `BigDecimal`, ou stocke des entiers en francs CFA.

## Lire une saisie au clavier

La classe `Scanner` lit ce que l'utilisateur tape. Elle appartient au paquet `java.util`, qu'il faut **importer** :

```java
import java.util.Scanner;

public class Salutation {
    public static void main(String[] args) {
        Scanner clavier = new Scanner(System.in);

        System.out.print("Ton prénom : ");
        String prenom = clavier.nextLine();

        System.out.print("Ton âge : ");
        int age = clavier.nextInt();

        System.out.println("Salut " + prenom + ", dans un an tu auras " + (age + 1) + " ans.");
        clavier.close();
    }
}
```

`nextLine` lit une ligne complète, `nextInt` un entier, `nextDouble` un décimal. Un piège classique : après `nextInt`, le retour à la ligne reste dans le tampon, et un `nextLine` suivant renvoie une chaîne vide. Dans ce cas, ajoute un `clavier.nextLine()` « à vide » pour consommer la fin de ligne, ou lis tout avec `nextLine` et convertis avec `parseInt`.

## Organiser un projet avec Maven

Les vrais projets ne se compilent pas à la main fichier par fichier. On utilise un **outil de build** : **Maven** (ou Gradle). Maven impose une structure standard et gère les dépendances grâce à un fichier `pom.xml`.

```bash
mon-projet/
├── pom.xml
└── src/
    ├── main/java/fr/devroad/Main.java
    └── test/java/fr/devroad/MainTest.java
```

Un `pom.xml` minimal pour Java 21 :

```xml
<project xmlns="http://maven.apache.org/POM/4.0.0">
  <modelVersion>4.0.0</modelVersion>

  <groupId>fr.devroad</groupId>
  <artifactId>premiers-pas</artifactId>
  <version>1.0.0</version>

  <properties>
    <maven.compiler.release>21</maven.compiler.release>
    <project.build.sourceEncoding>UTF-8</project.build.sourceEncoding>
  </properties>
</project>
```

Les commandes que tu utiliseras tous les jours :

```bash
mvn compile          # compile le code
mvn test             # lance les tests
mvn package          # produit un fichier JAR dans target/
java -cp target/classes fr.devroad.Main
```

Le fichier source déclare son **paquet** (*package*) en première ligne : `package fr.devroad;`. Le paquet correspond au chemin du dossier, et sert à ranger les classes comme des dossiers rangent des fichiers. La convention est d'utiliser un nom de domaine inversé, en minuscules.

## Atelier guidé : un convertisseur de monnaie

Compte une heure. L'objectif : un programme qui convertit des euros en francs CFA (1 euro vaut 655,957 FCFA, taux fixe) et affiche un petit reçu.

1. Crée un projet Maven avec le `pom.xml` ci-dessus et la classe `fr.devroad.Convertisseur` dans `src/main/java`.
2. Déclare une constante `final double TAUX = 655.957;`.
3. Demande à l'utilisateur un montant en euros avec `Scanner` et `nextDouble`.
4. Calcule le montant en FCFA et arrondis-le à l'entier le plus proche avec `Math.round`.
5. Affiche le résultat avec `formatted` : `"%.2f EUR = %d FCFA"`.
6. Ajoute un bloc de texte qui affiche un reçu de plusieurs lignes (titre, montant, taux appliqué).
7. Utilise `var` pour au moins deux variables dont le type est évident.
8. Compile avec `mvn compile`, puis lance le programme.
9. Bonus : demande aussi le nom du client et affiche-le en majuscules dans le reçu.

Pour t'auto-évaluer, réponds sans regarder le cours : pourquoi `7 / 2` vaut-il 3 ? Pourquoi ne compare-t-on pas des chaînes avec `==` ? Quelle différence entre JDK et JVM ?

## Erreurs fréquentes

- **Nom de fichier différent du nom de la classe publique.** `Main.java` doit contenir `public class Main`, sinon le compilateur refuse.
- **Oublier le point-virgule.** L'erreur apparaît parfois à la ligne suivante : lis le message en remontant.
- **Comparer des chaînes avec `==`.** Utilise toujours `equals`.
- **La division entière.** `1 / 2` vaut 0 ; écris `1.0 / 2` pour obtenir 0.5.
- **Confondre guillemets et apostrophes.** `'a'` est un `char`, `"a"` est un `String`.
- **Utiliser `double` pour de l'argent.** Les arrondis binaires créent des écarts de quelques centimes.
- **Tester `java` sans le bon JDK.** Si `java -version` n'affiche pas 21, ton `PATH` pointe vers une ancienne installation.

## Bonnes pratiques

- Nomme les variables en **camelCase** (`nombreDeLikes`), les classes en **PascalCase** (`Convertisseur`), les constantes en MAJUSCULES.
- Choisis des noms explicites : `montantEnEuros` vaut mieux que `m`.
- Déclare une variable au plus près de son utilisation, et préfère `final` pour ce qui ne change pas.
- Utilise `var` seulement quand le type se lit immédiatement à droite.
- Lance toujours ton code via Maven dès que le projet dépasse un fichier.
- Lis les messages d'erreur du compilateur : ils indiquent le fichier, la ligne et souvent la solution.

## À retenir

- Java compile le code source en **bytecode**, exécuté par la **JVM** ; le **JDK** sert à développer.
- Tout code vit dans une classe ; `public static void main(String[] args)` est le point d'entrée.
- Java est statiquement typé : types primitifs (`int`, `double`, `boolean`, `char`, `long`) et `String`.
- `var` déduit le type mais ne le rend pas dynamique ; `final` crée une constante.
- Compare les chaînes avec `equals`, jamais avec `==` ; la division de deux entiers reste un entier.
- Les blocs de texte (trois guillemets) et `formatted` simplifient l'affichage.
- Maven gère la structure du projet, la compilation et les dépendances via le `pom.xml`.
