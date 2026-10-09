---
title: Classes et objets
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Java est un langage **orienté objet**. Au lieu de manipuler des variables et des fonctions éparpillées, tu regroupes les données et le comportement qui vont ensemble dans des **classes**, puis tu crées des **objets** à partir de ces plans. C'est la base de tout code Java professionnel, de Spring à Android.

À la fin du chapitre, tu seras capable de :

- définir une classe avec des attributs, un constructeur et des méthodes ;
- protéger les données grâce à l'**encapsulation** (`private`, getters) ;
- redéfinir `toString`, `equals` et `hashCode` ;
- utiliser les membres `static` à bon escient ;
- écrire des **records** et des **enums** ;
- appliquer l'**héritage**, les classes abstraites et les **interfaces** ;
- expliquer le **polymorphisme** avec un exemple concret.

Prérequis : les chapitres « Premiers pas avec Java » et « Structures de contrôle et méthodes ». Prévois deux heures et demie.

## Pourquoi des objets ?

Imagine que tu gères les roadmaps de DevRoad avec des variables séparées :

```java
String titre1 = "Laravel";
int minutes1 = 180;
String titre2 = "React";
int minutes2 = 120;
```

Avec cent roadmaps, c'est ingérable. Une classe regroupe les données (les **attributs**) et ce qu'on peut faire avec (les **méthodes**). Une **classe** est un plan ; un **objet** est une instance concrète construite à partir de ce plan.

## Ta première classe

```java
public class Roadmap {

    private String titre;
    private int minutes;

    public Roadmap(String titre, int minutes) {
        this.titre = titre;
        this.minutes = minutes;
    }

    public String getTitre() {
        return titre;
    }

    public int getMinutes() {
        return minutes;
    }

    public String duree() {
        return minutes / 60 + " h " + minutes % 60 + " min";
    }
}
```

Et son utilisation :

```java
Roadmap laravel = new Roadmap("Laravel", 150);
Roadmap react = new Roadmap("React", 120);

System.out.println(laravel.getTitre());   // Laravel
System.out.println(laravel.duree());      // 2 h 30 min
```

Points clés :

- `new Roadmap(...)` crée un objet et appelle le **constructeur**, une méthode spéciale qui porte le nom de la classe et n'a pas de type de retour ;
- `this` désigne l'objet courant ; `this.titre = titre` distingue l'attribut du paramètre de même nom ;
- chaque objet possède ses **propres valeurs** : modifier `laravel` ne change pas `react` ;
- on appelle les méthodes avec le point : `objet.methode()`.

Si tu n'écris aucun constructeur, Java en fournit un par défaut sans paramètre. Dès que tu en écris un, le constructeur par défaut disparaît. Tu peux aussi définir plusieurs constructeurs (surcharge) et les enchaîner avec `this(...)`.

## L'encapsulation

Pourquoi les attributs sont-ils `private` ? Parce qu'un attribut public peut être modifié n'importe où, avec n'importe quelle valeur :

```java
roadmap.minutes = -50;   // possible si l'attribut est public : état invalide
```

L'**encapsulation** consiste à cacher l'intérieur de la classe et à n'exposer que des méthodes qui garantissent la cohérence :

```java
public class Compte {

    private long solde;

    public Compte(long soldeInitial) {
        if (soldeInitial < 0) {
            throw new IllegalArgumentException("Le solde initial ne peut pas être négatif");
        }
        this.solde = soldeInitial;
    }

    public void deposer(long montant) {
        if (montant <= 0) {
            throw new IllegalArgumentException("Le montant doit être positif");
        }
        solde += montant;
    }

    public boolean retirer(long montant) {
        if (montant <= 0 || montant > solde) {
            return false;
        }
        solde -= montant;
        return true;
    }

    public long getSolde() {
        return solde;
    }
}
```

Impossible, de l'extérieur, de mettre un solde négatif : le seul moyen de le modifier passe par des méthodes qui valident. Il n'y a pas de `setSolde`, car la logique métier (déposer, retirer) vaut mieux qu'un setter brut.

Les niveaux de visibilité :

| Modificateur | Visible depuis |
| --- | --- |
| `private` | la classe seule |
| (aucun) | le même paquet |
| `protected` | le même paquet et les sous-classes |
| `public` | partout |

Règle simple : commence par `private`, puis ouvre uniquement ce qui est nécessaire.

:::quiz
Pourquoi déclare-t-on les attributs d'une classe private ?
- [ ] Pour que le programme s'exécute plus vite
- [x] Pour contrôler leur modification et garantir un état toujours valide
- [ ] Parce que Java l'impose pour tous les attributs
- [ ] Pour les rendre accessibles uniquement aux tests
> L'encapsulation cache les détails internes et force à passer par des méthodes qui valident les données. Rien n'oblige Java à mettre private, c'est un choix de conception.
:::

## Les membres static

Un membre `static` appartient à la **classe** et non à un objet. Il est partagé par tous.

```java
public class Utilisateur {

    private static int compteur = 0;
    private final int id;
    private final String nom;

    public Utilisateur(String nom) {
        this.id = ++compteur;
        this.nom = nom;
    }

    public static int nombreCree() {
        return compteur;
    }
}
```

`Utilisateur.nombreCree()` s'appelle sur la classe, sans objet. Les constantes se déclarent souvent `public static final`. Attention : un état statique modifiable est partagé par tout le programme, ce qui complique les tests. Utilise-le avec parcimonie.

## Les méthodes de Object : toString, equals, hashCode

Toute classe hérite implicitement de `Object`, qui fournit trois méthodes importantes à redéfinir.

```java
public class Produit {

    private final String reference;
    private final long prix;

    public Produit(String reference, long prix) {
        this.reference = reference;
        this.prix = prix;
    }

    @Override
    public String toString() {
        return "Produit[" + reference + ", " + prix + " FCFA]";
    }

    @Override
    public boolean equals(Object autre) {
        if (this == autre) return true;
        if (!(autre instanceof Produit p)) return false;
        return reference.equals(p.reference);
    }

    @Override
    public int hashCode() {
        return reference.hashCode();
    }
}
```

- `toString` définit la représentation texte, très utile pour le débogage ; sans elle, tu obtiens un texte illisible comme `Produit@1b6d3586` ;
- `equals` définit quand deux objets sont **égaux** (ici, même référence produit) ; par défaut, c'est l'identité en mémoire ;
- `hashCode` doit être cohérent avec `equals` : deux objets égaux ont obligatoirement le même `hashCode`. Sans cela, les collections à base de hachage (`HashMap`, `HashSet`) se comportent mal.

L'annotation `@Override` demande au compilateur de vérifier que tu redéfinis bien une méthode existante : elle détecte les fautes de frappe.

## Les records

Écrire à chaque fois attributs, constructeur, getters, `equals`, `hashCode` et `toString` est fastidieux pour de simples porteurs de données. Les **records** (Java 16+) font tout cela en une ligne :

```java
public record Point(int x, int y) {}

Point p = new Point(3, 4);
System.out.println(p.x());          // 3 : accesseur généré
System.out.println(p);              // Point[x=3, y=4]
System.out.println(p.equals(new Point(3, 4)));   // true
```

Un record est **immuable** : ses composants sont `final`. Tu peux ajouter une validation dans un **constructeur compact** et des méthodes :

```java
public record Prix(long montant, String devise) {

    public Prix {
        if (montant < 0) {
            throw new IllegalArgumentException("Prix négatif");
        }
    }

    public Prix plus(Prix autre) {
        return new Prix(montant + autre.montant, devise);
    }
}
```

Utilise un record pour les objets de transfert de données (DTO), les valeurs, les résultats de requêtes. Utilise une classe classique quand l'objet a un état qui évolue.

## Les enums

Une **énumération** représente un ensemble fini de valeurs. C'est plus sûr que des chaînes ou des entiers « magiques » :

```java
public enum Niveau {
    DEBUTANT("Débutant", 1),
    INTERMEDIAIRE("Intermédiaire", 2),
    PROFESSIONNEL("Professionnel", 3);

    private final String libelle;
    private final int rang;

    Niveau(String libelle, int rang) {
        this.libelle = libelle;
        this.rang = rang;
    }

    public String getLibelle() {
        return libelle;
    }

    public boolean estAvance() {
        return rang >= 3;
    }
}
```

On les utilise partout où une valeur ne peut être qu'un choix parmi une liste, notamment avec `switch` :

```java
Niveau n = Niveau.INTERMEDIAIRE;
String message = switch (n) {
    case DEBUTANT -> "Commence par les bases";
    case INTERMEDIAIRE -> "Continue, tu progresses";
    case PROFESSIONNEL -> "Passe aux projets";
};
```

Quand le `switch` couvre tous les cas d'un enum, le `default` devient inutile, et le compilateur te prévient si tu en ajoutes un nouveau et oublies de le traiter. `Niveau.values()` renvoie toutes les valeurs, `Niveau.valueOf("DEBUTANT")` convertit un texte.

## L'héritage

Une classe peut **étendre** une autre pour réutiliser et spécialiser son comportement :

```java
public class Animal {

    protected final String nom;

    public Animal(String nom) {
        this.nom = nom;
    }

    public String crier() {
        return "...";
    }
}

public class Chien extends Animal {

    public Chien(String nom) {
        super(nom);
    }

    @Override
    public String crier() {
        return "Wouf";
    }
}
```

`super(nom)` appelle le constructeur de la classe parente ; il doit être la première instruction. Une classe ne peut étendre qu'**une seule** classe. L'héritage exprime une relation « est un » : un chien *est un* animal. Si la relation n'est pas évidente, préfère la **composition** (un objet contient un autre objet).

Le mot `final` devant une classe l'empêche d'être étendue ; devant une méthode, d'être redéfinie.

### Classes abstraites et interfaces

Une **classe abstraite** ne peut pas être instanciée ; elle sert de base commune et peut imposer des méthodes à implémenter :

```java
public abstract class Forme {
    public abstract double aire();

    public String description() {
        return "Forme d'aire " + aire();
    }
}
```

Une **interface** décrit un contrat, c'est-à-dire ce qu'un objet sait faire, sans dire comment. Une classe peut en implémenter plusieurs :

```java
public interface Imprimable {
    String imprimer();
}

public class Facture implements Imprimable {
    @Override
    public String imprimer() {
        return "Facture n° 2025-001";
    }
}
```

Choisis une interface pour définir un **rôle** et pouvoir changer d'implémentation (c'est le principe sur lequel repose Spring). Choisis une classe abstraite pour partager du code et des attributs entre des classes proches.

## Le polymorphisme

Le polymorphisme permet de manipuler des objets de classes différentes via un type commun, chacun réagissant à sa manière :

```java
public record Cercle(double rayon) implements Mesurable {
    public double aire() { return Math.PI * rayon * rayon; }
}

public record Rectangle(double l, double h) implements Mesurable {
    public double aire() { return l * h; }
}

public interface Mesurable {
    double aire();
}

// Utilisation
List<Mesurable> formes = List.of(new Cercle(2), new Rectangle(3, 4));
for (Mesurable forme : formes) {
    System.out.println(forme.aire());   // chaque objet calcule à sa façon
}
```

Le code appelant ne connaît que `Mesurable`. Tu peux ajouter un `Triangle` sans modifier la boucle. C'est la clé d'un code évolutif.

:::quiz
Quelle relation exprime l'héritage entre deux classes ?
- [ ] « possède un »
- [x] « est un »
- [ ] « utilise un »
- [ ] « appelle un »
> L'héritage modélise une relation « est un » : un Chien est un Animal. Pour « possède un », on utilise la composition.
:::

## Atelier guidé : une petite bibliothèque

Compte une heure et demie. Crée un projet Maven avec le paquet `fr.devroad.bibliotheque`.

1. Crée l'enum `Categorie` avec trois valeurs (par exemple ROMAN, TECHNIQUE, BD) et un libellé.
2. Crée le record `Auteur(String nom, String pays)`.
3. Crée la classe `Livre` avec les attributs privés `titre`, `auteur`, `categorie` et `disponible`, un constructeur et des getters.
4. Redéfinis `toString`, `equals` et `hashCode` à partir du titre et de l'auteur.
5. Ajoute les méthodes `emprunter()` et `rendre()` : `emprunter` renvoie `false` si le livre n'est pas disponible.
6. Crée l'interface `Empruntable` avec ces deux méthodes, et fais-la implémenter par `Livre`.
7. Crée une classe abstraite `Membre` avec une méthode abstraite `limiteEmprunts()`, puis deux sous-classes `Etudiant` (3 livres) et `Enseignant` (10 livres).
8. Dans `main`, affiche les limites de plusieurs membres via une liste de type `Membre`, pour observer le polymorphisme.
9. Ajoute un compteur statique du nombre de livres créés.

Pour t'auto-évaluer : explique en trois phrases pourquoi on rend les attributs privés, quand utiliser un record plutôt qu'une classe, et la différence entre classe abstraite et interface.

## Erreurs fréquentes

- **Attributs publics.** Ils cassent l'encapsulation : n'importe quel code peut les rendre invalides.
- **Redéfinir `equals` sans `hashCode`.** Les objets « égaux » se retrouvent à deux endroits dans un `HashSet`.
- **Oublier `@Override`.** Une faute de frappe (`tostring`) crée une nouvelle méthode au lieu de redéfinir.
- **Appeler une méthode sur `null`.** Une `NullPointerException` survient quand la variable n'a pas été initialisée.
- **Comparer des objets avec `==`.** Cet opérateur compare l'identité, pas le contenu : utilise `equals`.
- **Abuser de l'héritage.** Une hiérarchie profonde est rigide ; préfère les interfaces et la composition.
- **Tout mettre en `static`.** On retombe alors dans la programmation procédurale, sans bénéfice de l'objet.

## Bonnes pratiques

- Attributs `private`, immuables (`final`) quand c'est possible.
- Valide dans le constructeur : un objet doit être valide dès sa création.
- Préfère les méthodes métier (`deposer`) aux setters automatiques.
- Utilise des records pour les données simples, des enums pour les listes fermées.
- Programme vers une **interface** (`List`, `Mesurable`) plutôt que vers une classe concrète.
- Une classe, une responsabilité : si tu hésites sur son nom, elle en fait sûrement trop.
- Respecte les conventions : classes en PascalCase, une classe publique par fichier.

## À retenir

- Une **classe** est un plan, un **objet** une instance ; le constructeur initialise l'objet.
- L'**encapsulation** protège les données avec `private` et des méthodes qui valident.
- `toString`, `equals` et `hashCode` se redéfinissent ensemble avec `@Override`.
- Les **records** décrivent des données immuables en une ligne ; les **enums**, des valeurs en nombre fixe.
- L'**héritage** exprime « est un » ; les **interfaces** définissent des contrats ; la **composition** exprime « possède un ».
- Le **polymorphisme** permet de manipuler des objets différents via un type commun.
