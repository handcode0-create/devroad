---
title: Exceptions, fichiers et tests
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Un programme réel doit survivre à l'imprévu : un fichier absent, un nombre mal saisi, un réseau coupé. Java gère ces situations avec les **exceptions**. Tu dois aussi savoir lire et écrire des fichiers, puis prouver que ton code fonctionne grâce aux **tests automatisés**. Ces trois sujets sont ce qui sépare un exercice d'un code de production.

À la fin du chapitre, tu seras capable de :

- distinguer les exceptions vérifiées et non vérifiées ;
- utiliser `try`, `catch`, `finally` et `try-with-resources` ;
- créer tes propres exceptions métier ;
- lire et écrire des fichiers avec `Path` et `Files` ;
- écrire des tests unitaires avec **JUnit 5** et les lancer avec Maven ;
- appliquer une démarche simple de test (arrange, act, assert).

Prérequis : les chapitres « Classes et objets » et « Collections et Streams ». Prévois deux heures et demie.

## Les exceptions : le principe

Une **exception** est un objet qui signale qu'une situation anormale s'est produite. Quand elle est lancée, l'exécution normale s'arrête et remonte la pile d'appels jusqu'à ce que quelqu'un l'**attrape**. Si personne ne le fait, le programme s'arrête avec une trace d'erreur.

```java
int[] tableau = {1, 2, 3};
System.out.println(tableau[5]);   // ArrayIndexOutOfBoundsException
```

La hiérarchie se résume ainsi :

| Type | Exemples | Obligation |
| --- | --- | --- |
| `Error` | `OutOfMemoryError` | on ne les attrape pas |
| Exception **vérifiée** | `IOException`, `SQLException` | à déclarer ou à attraper |
| Exception **non vérifiée** (`RuntimeException`) | `NullPointerException`, `IllegalArgumentException` | libre |

Une exception **vérifiée** (*checked*) représente un problème extérieur prévisible, comme un fichier introuvable : le compilateur t'oblige à la traiter. Une exception **non vérifiée** traduit généralement un bug ou un mauvais usage de l'API, et le compilateur ne t'impose rien.

## try, catch et finally

```java
public static int lireEntier(String texte) {
    try {
        return Integer.parseInt(texte);
    } catch (NumberFormatException e) {
        System.out.println("Pas un nombre : " + texte);
        return 0;
    } finally {
        System.out.println("Vérification terminée");   // toujours exécuté
    }
}
```

- le bloc `try` contient le code à risque ;
- chaque `catch` traite un type d'exception ; le premier compatible est exécuté ;
- `finally` s'exécute toujours, qu'il y ait une erreur ou non ;
- on peut regrouper plusieurs types : `catch (IOException | SQLException e)`.

Attrape toujours les exceptions **les plus spécifiques** d'abord, puis les plus générales. Un `catch (Exception e)` placé en premier masquerait tous les autres.

### Lancer une exception

Avec `throw`, tu signales toi-même un problème :

```java
public static double diviser(double a, double b) {
    if (b == 0) {
        throw new IllegalArgumentException("Le diviseur ne peut pas être zéro");
    }
    return a / b;
}
```

Une méthode qui peut lancer une exception **vérifiée** doit le déclarer avec `throws` :

```java
public static String lire(Path chemin) throws IOException {
    return Files.readString(chemin);
}
```

L'appelant doit alors soit l'attraper, soit la déclarer à son tour.

> **Erreur fréquente** : écrire `catch (Exception e) { }` avec un bloc vide. L'erreur disparaît silencieusement et personne ne comprend pourquoi le programme ne fait rien. Au minimum, journalise l'exception ou relance-la.

:::quiz
Quand le bloc finally est-il exécuté ?
- [ ] Seulement quand une exception est lancée
- [ ] Seulement quand aucune exception n'est lancée
- [x] Dans tous les cas, avec ou sans exception
- [ ] Jamais si le bloc try contient un return
> finally s'exécute toujours à la sortie du try, y compris en présence d'un return ou d'une exception. On l'utilise pour libérer des ressources.
:::

## try-with-resources

Un fichier, une connexion ou un flux doivent être **fermés** après usage, sinon on gaspille des ressources. Le `try-with-resources` ferme automatiquement tout objet qui implémente `AutoCloseable`, même en cas d'erreur :

```java
try (BufferedReader lecteur = Files.newBufferedReader(Path.of("notes.txt"))) {
    String ligne;
    while ((ligne = lecteur.readLine()) != null) {
        System.out.println(ligne);
    }
} catch (IOException e) {
    System.out.println("Impossible de lire le fichier : " + e.getMessage());
}
```

La ressource, déclarée entre parenthèses, est fermée à la fin du bloc sans que tu aies à écrire de `finally`. Utilise-le systématiquement pour tout ce qui s'ouvre et se ferme.

## Créer ses propres exceptions

Pour exprimer une règle métier, crée une exception dédiée. Étends `RuntimeException` pour une erreur non vérifiée, ou `Exception` pour une erreur que l'appelant doit traiter :

```java
public class SoldeInsuffisantException extends RuntimeException {

    private final long manquant;

    public SoldeInsuffisantException(long manquant) {
        super("Il manque " + manquant + " FCFA");
        this.manquant = manquant;
    }

    public long getManquant() {
        return manquant;
    }
}
```

```java
public void retirer(long montant) {
    if (montant > solde) {
        throw new SoldeInsuffisantException(montant - solde);
    }
    solde -= montant;
}
```

Une exception explicite vaut mieux qu'un code de retour obscur comme `-1`. Quand tu enveloppes une exception technique, **conserve la cause** pour ne pas perdre la trace d'origine :

```java
try {
    Files.readString(chemin);
} catch (IOException e) {
    throw new IllegalStateException("Configuration illisible : " + chemin, e);
}
```

Aujourd'hui, la tendance est d'utiliser surtout des exceptions non vérifiées pour les erreurs métier : cela évite d'alourdir chaque signature avec des `throws`.

## Lire et écrire des fichiers

L'API moderne repose sur `Path` (un chemin) et `Files` (des opérations prêtes à l'emploi). Elle remplace l'ancienne classe `File`.

```java
import java.io.IOException;
import java.nio.charset.StandardCharsets;
import java.nio.file.Files;
import java.nio.file.Path;
import java.nio.file.StandardOpenOption;
import java.util.List;

Path fichier = Path.of("data", "etudiants.csv");

// écrire (crée le dossier puis le fichier)
Files.createDirectories(fichier.getParent());
Files.writeString(fichier, "nom;note\nAwa;15\nKoffi;12\n", StandardCharsets.UTF_8);

// ajouter une ligne à la fin
Files.writeString(fichier, "Mariam;17\n", StandardOpenOption.APPEND);

// lire en bloc
String contenu = Files.readString(fichier);

// lire ligne par ligne
List<String> lignes = Files.readAllLines(fichier);

// tester l'existence
boolean existe = Files.exists(fichier);
```

Pour de gros fichiers, ne charge pas tout en mémoire : `Files.lines(chemin)` renvoie un `Stream` de lignes **à fermer** avec un `try-with-resources` :

```java
try (var flux = Files.lines(fichier)) {
    long nb = flux.skip(1).filter(l -> !l.isBlank()).count();
    System.out.println(nb + " étudiants");
}
```

Précise toujours l'encodage `UTF_8` quand tu manipules du texte avec des accents, pour que le résultat soit le même sur toutes les machines.

### Un exemple complet : parser un CSV

```java
public record Etudiant(String nom, int note) {}

public static List<Etudiant> charger(Path fichier) {
    try (var flux = Files.lines(fichier)) {
        return flux.skip(1)
                .filter(l -> !l.isBlank())
                .map(l -> l.split(";"))
                .map(c -> new Etudiant(c[0].trim(), Integer.parseInt(c[1].trim())))
                .toList();
    } catch (IOException e) {
        throw new UncheckedIOException("Lecture impossible : " + fichier, e);
    }
}
```

Ce code combine ce que tu as vu : streams, records, `try-with-resources` et enveloppe d'exception avec conservation de la cause.

## Les tests automatisés avec JUnit 5

Tester à la main, c'est lent et on oublie de le refaire. Un **test unitaire** vérifie automatiquement qu'une petite unité de code se comporte comme prévu. **JUnit 5** est le framework de référence. Ajoute-le dans le `pom.xml` :

```xml
<dependencies>
  <dependency>
    <groupId>org.junit.jupiter</groupId>
    <artifactId>junit-jupiter</artifactId>
    <version>5.10.2</version>
    <scope>test</scope>
  </dependency>
</dependencies>

<build>
  <plugins>
    <plugin>
      <groupId>org.apache.maven.plugins</groupId>
      <artifactId>maven-surefire-plugin</artifactId>
      <version>3.2.5</version>
    </plugin>
  </plugins>
</build>
```

Les tests se placent dans `src/test/java`, dans le même paquet que le code testé. Voici une classe à tester :

```java
public class Calculatrice {

    public int additionner(int a, int b) {
        return a + b;
    }

    public double diviser(double a, double b) {
        if (b == 0) {
            throw new IllegalArgumentException("Division par zéro");
        }
        return a / b;
    }
}
```

Et son test :

```java
import static org.junit.jupiter.api.Assertions.*;

import org.junit.jupiter.api.BeforeEach;
import org.junit.jupiter.api.DisplayName;
import org.junit.jupiter.api.Test;

class CalculatriceTest {

    private Calculatrice calculatrice;

    @BeforeEach
    void preparer() {
        calculatrice = new Calculatrice();
    }

    @Test
    @DisplayName("Additionne deux entiers positifs")
    void additionneDeuxEntiers() {
        int resultat = calculatrice.additionner(2, 3);
        assertEquals(5, resultat);
    }

    @Test
    void diviseNormalement() {
        assertEquals(2.5, calculatrice.diviser(5, 2), 0.0001);
    }

    @Test
    void refuseLaDivisionParZero() {
        var e = assertThrows(IllegalArgumentException.class,
                () -> calculatrice.diviser(1, 0));
        assertEquals("Division par zéro", e.getMessage());
    }
}
```

Lance les tests avec Maven :

```bash
mvn test
```

Maven affiche le nombre de tests exécutés, réussis et échoués. Un test qui échoue indique précisément la valeur attendue et la valeur obtenue.

### Structure d'un bon test : arrange, act, assert

1. **Arrange** : prépare les données et les objets ;
2. **Act** : appelle la méthode testée ;
3. **Assert** : vérifie le résultat.

Les assertions principales sont `assertEquals`, `assertTrue`, `assertFalse`, `assertNull`, `assertNotNull`, `assertThrows` et `assertAll`. Les annotations `@BeforeEach` (avant chaque test), `@AfterEach`, `@Disabled` et `@DisplayName` complètent la boîte à outils.

:::quiz
Quelle assertion JUnit 5 vérifie qu'un appel lève bien une exception ?
- [ ] assertEquals
- [ ] assertNull
- [x] assertThrows
- [ ] assertTrue
> assertThrows prend le type d'exception attendu et une lambda exécutant le code. Le test échoue si aucune exception, ou une autre, est lancée.
:::

### Tests paramétrés

Quand plusieurs cas suivent le même schéma, un test paramétré évite de copier-coller. Ajoute la dépendance `junit-jupiter-params` (déjà incluse dans `junit-jupiter`) :

```java
import org.junit.jupiter.params.ParameterizedTest;
import org.junit.jupiter.params.provider.CsvSource;

class PremierTest {

    @ParameterizedTest
    @CsvSource({"2,true", "3,true", "4,false", "9,false", "13,true"})
    void detectePremier(int n, boolean attendu) {
        assertEquals(attendu, Outils.estPremier(n));
    }
}
```

Chaque ligne du `@CsvSource` crée un test distinct, avec son propre résultat dans le rapport.

### Que tester ?

- les cas normaux (le chemin « heureux ») ;
- les **cas limites** : zéro, liste vide, valeur maximale, chaîne vide ;
- les **erreurs** : entrées invalides, exceptions attendues.

Un test doit être **rapide**, **indépendant** des autres (aucun ordre imposé) et **déterministe** (même résultat à chaque exécution). Évite d'y inclure du hasard, la date du jour ou l'accès à Internet.

Pour tester la lecture de fichiers sans polluer ton projet, JUnit propose le répertoire temporaire :

```java
@Test
void chargeUnCsv(@TempDir Path dossier) throws IOException {
    Path fichier = dossier.resolve("test.csv");
    Files.writeString(fichier, "nom;note\nAwa;15\n");

    var etudiants = Chargeur.charger(fichier);

    assertEquals(1, etudiants.size());
    assertEquals("Awa", etudiants.get(0).nom());
}
```

## Atelier guidé : un gestionnaire de notes testé

Compte une heure et demie. Crée un projet Maven `fr.devroad.notes` avec JUnit 5.

1. Crée le record `Etudiant(String nom, int note)` avec une validation : la note doit être entre 0 et 20, sinon `IllegalArgumentException`.
2. Crée l'exception `FormatCsvException` étendant `RuntimeException`.
3. Écris `Chargeur.charger(Path)` qui lit un CSV `nom;note`, ignore les lignes vides et lance `FormatCsvException` (avec la ligne fautive) si le format est invalide.
4. Écris `Statistiques.moyenne(List)` qui renvoie un `OptionalDouble` pour gérer la liste vide.
5. Écris `Rapport.ecrire(Path, List)` qui produit un fichier texte trié par note décroissante.
6. Écris au moins six tests : moyenne normale, liste vide, note invalide, ligne CSV invalide, fichier absent (avec `@TempDir`), rapport écrit.
7. Lance `mvn test` et fais passer tous les tests au vert.
8. Casse volontairement le code d'une méthode pour voir un test échouer, puis corrige-le.

Pour t'auto-évaluer : pourquoi préférer `try-with-resources` à `finally` pour fermer un fichier ? Pourquoi conserver la cause d'une exception ? Qu'est-ce qu'un test indépendant ?

## Erreurs fréquentes

- **Bloc `catch` vide.** Il avale l'erreur : journalise ou relance.
- **Attraper `Exception` partout.** Tu masques des bugs réels ; attrape le type précis que tu sais traiter.
- **Perdre la cause.** `throw new RuntimeException("Erreur")` sans passer `e` supprime l'information utile au diagnostic.
- **Oublier de fermer un flux.** Utilise `try-with-resources`.
- **Utiliser les exceptions pour le flux normal.** Tester une valeur avec `if` est plus clair et plus rapide qu'attendre une exception.
- **Tests dépendants les uns des autres.** Un test qui doit passer après un autre échoue de manière aléatoire.
- **Tester l'implémentation plutôt que le comportement.** Le test casse à chaque refactorisation sans raison.

## Bonnes pratiques

- Lance les exceptions **tôt** (validation des arguments) et attrape-les **tard**, là où tu sais quoi en faire.
- Mets un message précis et utile dans chaque exception : quoi, où, quelle valeur.
- Crée des exceptions métier plutôt que de réutiliser `RuntimeException` partout.
- Utilise `Path` et `Files`, et indique l'encodage UTF-8.
- Nomme les tests selon le comportement : `refuseLaDivisionParZero` parle mieux que `test1`.
- Écris un test en même temps que le code, ou même avant (approche TDD).
- Vise des tests rapides : tout doit s'exécuter en quelques secondes avec `mvn test`.

## À retenir

- Une exception remonte la pile d'appels jusqu'à un `catch` ; les exceptions **vérifiées** doivent être traitées, les **non vérifiées** traduisent surtout des bugs.
- `try-with-resources` ferme automatiquement les ressources ; `finally` s'exécute toujours.
- Les exceptions métier personnalisées rendent le code expressif ; conserve toujours la **cause**.
- `Path` et `Files` lisent et écrivent des fichiers simplement ; `Files.lines` demande une fermeture.
- JUnit 5 : `@Test`, `@BeforeEach`, `assertEquals`, `assertThrows`, tests paramétrés et `@TempDir`.
- Un bon test est rapide, indépendant, déterministe et structuré en arrange, act, assert.
