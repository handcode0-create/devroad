---
title: Collections et Streams
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Les tableaux ont une taille fixe et peu de fonctionnalités. Dans une vraie application, tu manipules des listes d'utilisateurs, des dictionnaires de produits, des ensembles d'identifiants. Java fournit pour cela le **framework de collections**, et depuis Java 8 l'**API Stream** pour transformer ces données avec un style déclaratif proche de ce que tu connais en JavaScript avec `map` et `filter`.

À la fin du chapitre, tu seras capable de :

- choisir entre `List`, `Set` et `Map` selon le besoin ;
- utiliser les génériques pour typer tes collections ;
- trier avec `Comparable` et `Comparator` ;
- écrire des **lambdas** et utiliser les références de méthodes ;
- construire des pipelines de **Streams** (`filter`, `map`, `collect`, `groupingBy`) ;
- manipuler `Optional` pour éviter les `null`.

Prérequis : le chapitre « Classes et objets » (classes, interfaces, records). Prévois deux heures et demie.

## Les génériques en bref

Une collection est un conteneur. Les **génériques** précisent ce qu'il contient, avec des chevrons :

```java
List<String> langages = new ArrayList<>();
langages.add("Java");
langages.add("PHP");
// langages.add(42);   // erreur de compilation : 42 n'est pas un String

String premier = langages.get(0);   // pas besoin de cast
```

Sans génériques, tu aurais une liste d'`Object` et des conversions risquées. Les chevrons vides `<>` (opérateur *diamant*) laissent le compilateur déduire le type. Les types génériques ne contiennent que des **objets** : pour les types primitifs, on utilise leurs classes enveloppes (`Integer`, `Double`, `Boolean`), la conversion étant automatique.

```java
List<Integer> nombres = new ArrayList<>();
nombres.add(5);          // int converti en Integer automatiquement
int n = nombres.get(0);  // et inversement
```

## List : une séquence ordonnée

Une `List` garde l'ordre d'insertion et accepte les doublons. L'implémentation courante est `ArrayList`.

```java
List<String> technos = new ArrayList<>(List.of("Java", "Spring", "Maven"));

technos.add("JUnit");
technos.add(1, "Kotlin");           // insertion à l'index 1
technos.remove("Maven");            // suppression par valeur
System.out.println(technos.size());         // 4
System.out.println(technos.contains("Java")); // true
System.out.println(technos.indexOf("JUnit")); // 3

for (String t : technos) {
    System.out.println(t);
}
```

`List.of(...)` crée une liste **immuable** : tenter de la modifier lève une `UnsupportedOperationException`. Si tu veux une liste modifiable, passe-la au constructeur de `ArrayList` comme ci-dessus.

Choisir l'implémentation : `ArrayList` est rapide pour la lecture par index et convient dans 95 % des cas. `LinkedList` ne se justifie que dans des cas très particuliers.

## Set : des éléments uniques

Un `Set` interdit les doublons. Trois implémentations à connaître :

| Implémentation | Ordre |
| --- | --- |
| `HashSet` | aucun ordre garanti, le plus rapide |
| `LinkedHashSet` | ordre d'insertion |
| `TreeSet` | ordre trié |

```java
Set<String> tags = new HashSet<>();
tags.add("java");
tags.add("backend");
boolean ajoute = tags.add("java");     // false : déjà présent

System.out.println(tags.size());       // 2
```

Pour détecter les doublons, un `HashSet` utilise `equals` et `hashCode`. C'est pourquoi tu dois les redéfinir ensemble (ou utiliser un record, qui le fait pour toi). Un `Set` est idéal pour dédupliquer ou tester une appartenance rapidement.

## Map : des paires clé-valeur

Une `Map` associe une **clé** unique à une valeur, comme un dictionnaire :

```java
Map<String, Integer> notes = new HashMap<>();
notes.put("Awa", 15);
notes.put("Koffi", 12);
notes.put("Awa", 17);                       // remplace la valeur de Awa

System.out.println(notes.get("Awa"));       // 17
System.out.println(notes.get("Inconnu"));   // null
System.out.println(notes.getOrDefault("Inconnu", 0));   // 0
System.out.println(notes.containsKey("Koffi"));         // true

for (Map.Entry<String, Integer> e : notes.entrySet()) {
    System.out.println(e.getKey() + " -> " + e.getValue());
}
```

Quelques méthodes très utiles pour éviter du code répétitif :

```java
Map<String, List<String>> parNiveau = new HashMap<>();

// ajoute la liste si elle n'existe pas, puis ajoute l'élément
parNiveau.computeIfAbsent("debutant", k -> new ArrayList<>()).add("HTML");

// compte des occurrences
Map<String, Integer> compte = new HashMap<>();
for (String mot : List.of("a", "b", "a")) {
    compte.merge(mot, 1, Integer::sum);
}
// {a=2, b=1}
```

Comme pour les listes, `HashMap` n'a pas d'ordre garanti, `LinkedHashMap` garde l'ordre d'insertion et `TreeMap` trie par clé.

:::quiz
Quelle collection choisir pour stocker des identifiants sans doublons ?
- [ ] ArrayList
- [x] HashSet
- [ ] Un tableau d'entiers
- [ ] LinkedList
> Un Set refuse les doublons par conception. HashSet est l'implémentation la plus rapide quand l'ordre n'importe pas.
:::

## Trier : Comparable et Comparator

Pour trier une liste d'objets, Java doit savoir comment les comparer. Deux approches :

```java
public record Etudiant(String nom, int note) implements Comparable<Etudiant> {
    @Override
    public int compareTo(Etudiant autre) {
        return nom.compareTo(autre.nom);   // ordre naturel : par nom
    }
}
```

`Comparable` définit **l'ordre naturel** de la classe. Pour d'autres critères, on fournit un `Comparator` externe :

```java
List<Etudiant> classe = new ArrayList<>(List.of(
    new Etudiant("Awa", 15), new Etudiant("Koffi", 12), new Etudiant("Mariam", 15)));

Collections.sort(classe);   // ordre naturel (par nom)

classe.sort(Comparator.comparingInt(Etudiant::note).reversed()
        .thenComparing(Etudiant::nom));
```

Les comparateurs se composent : trier par note décroissante, puis par nom en cas d'égalité. Évite d'écrire `a.note() - b.note()` à la main, qui peut déborder sur de grands entiers.

## Lambdas et interfaces fonctionnelles

Une **lambda** est une fonction anonyme concise. Elle s'écrit `(paramètres) -> expression`. Elle peut s'utiliser partout où l'on attend une **interface fonctionnelle**, c'est-à-dire une interface à une seule méthode abstraite.

```java
Comparator<String> parLongueur = (a, b) -> Integer.compare(a.length(), b.length());

List<String> mots = new ArrayList<>(List.of("Spring", "Java", "Maven"));
mots.sort(parLongueur);
mots.forEach(m -> System.out.println(m));
mots.removeIf(m -> m.startsWith("M"));
```

Quand la lambda ne fait qu'appeler une méthode existante, une **référence de méthode** est plus lisible : `System.out::println` à la place de `m -> System.out.println(m)`, `String::length` à la place de `s -> s.length()`.

Les interfaces fonctionnelles du paquet `java.util.function` à connaître :

| Interface | Rôle | Exemple |
| --- | --- | --- |
| `Predicate` | renvoie un booléen | `n -> n > 10` |
| `Function` | transforme une valeur | `s -> s.length()` |
| `Consumer` | consomme sans rien renvoyer | `s -> System.out.println(s)` |
| `Supplier` | fournit une valeur | `() -> new ArrayList<>()` |

Une lambda peut lire les variables locales de son contexte à condition qu'elles soient **effectivement finales** : jamais réaffectées après leur initialisation.

## Les Streams

Un **Stream** est une séquence d'éléments sur laquelle on enchaîne des opérations. Il ne modifie pas la source et ne stocke rien : il décrit un traitement. Un pipeline comporte une **source**, des opérations **intermédiaires** (`filter`, `map`, `sorted`) et une opération **terminale** (`collect`, `forEach`, `count`) qui déclenche l'exécution.

```java
List<Etudiant> classe = List.of(
    new Etudiant("Awa", 15), new Etudiant("Koffi", 8),
    new Etudiant("Mariam", 17), new Etudiant("Yao", 11));

List<String> admis = classe.stream()
        .filter(e -> e.note() >= 10)
        .sorted(Comparator.comparingInt(Etudiant::note).reversed())
        .map(Etudiant::nom)
        .toList();

System.out.println(admis);   // [Mariam, Awa, Yao]
```

Lis ce pipeline comme une phrase : « à partir de la classe, garde les notes d'au moins 10, trie par note décroissante, extrais les noms, mets-les dans une liste ». La méthode `toList()` (Java 16+) renvoie une liste non modifiable.

### Les opérations courantes

```java
long nbAdmis = classe.stream().filter(e -> e.note() >= 10).count();

double moyenne = classe.stream()
        .mapToInt(Etudiant::note)
        .average()
        .orElse(0);

int meilleure = classe.stream().mapToInt(Etudiant::note).max().orElse(0);

boolean tousAdmis = classe.stream().allMatch(e -> e.note() >= 10);
boolean unExcellent = classe.stream().anyMatch(e -> e.note() >= 16);

String noms = classe.stream().map(Etudiant::nom).collect(Collectors.joining(", "));
```

Pour les nombres, `mapToInt` produit un `IntStream` avec `sum`, `average`, `min` et `max`, sans passer par des objets `Integer`. Des flux de nombres s'obtiennent aussi avec `IntStream.rangeClosed(1, 5)`.

### Regrouper avec Collectors

Les collecteurs sont la partie la plus puissante des streams :

```java
Map<Boolean, List<Etudiant>> parReussite =
        classe.stream().collect(Collectors.partitioningBy(e -> e.note() >= 10));

Map<Integer, List<String>> parNote = classe.stream()
        .collect(Collectors.groupingBy(Etudiant::note,
                 Collectors.mapping(Etudiant::nom, Collectors.toList())));

Map<String, Integer> nomVersNote = classe.stream()
        .collect(Collectors.toMap(Etudiant::nom, Etudiant::note));

Map<Boolean, Long> comptage = classe.stream()
        .collect(Collectors.groupingBy(e -> e.note() >= 10, Collectors.counting()));
```

`groupingBy` est l'équivalent du `GROUP BY` de SQL : il regroupe selon un critère et peut calculer des agrégats (compte, somme, moyenne). Attention, `toMap` lève une exception si deux éléments produisent la même clé, sauf si tu fournis une fonction de fusion en troisième argument.

> **Attention** : un stream ne se réutilise pas. Après une opération terminale, l'appeler une seconde fois lève une `IllegalStateException`. Recrée le stream à partir de la source.

:::quiz
Que fait une opération terminale comme collect ou count dans un pipeline de Stream ?
- [ ] Elle construit simplement la liste des opérations sans les exécuter
- [x] Elle déclenche l'exécution du pipeline et produit un résultat
- [ ] Elle modifie la collection d'origine
- [ ] Elle trie les éléments
> Les opérations intermédiaires (filter, map) sont paresseuses ; rien ne s'exécute tant qu'une opération terminale n'est pas appelée. La source n'est jamais modifiée.
:::

## Optional : éviter les null

`null` est la cause de la fameuse `NullPointerException`. `Optional` représente une valeur qui peut être absente et oblige à y penser :

```java
Optional<Etudiant> meilleur = classe.stream()
        .max(Comparator.comparingInt(Etudiant::note));

String message = meilleur
        .map(Etudiant::nom)
        .map(nom -> "Major : " + nom)
        .orElse("Classe vide");

meilleur.ifPresent(e -> System.out.println(e.nom()));
Etudiant e = meilleur.orElseThrow();   // exception si vide
```

Utilise `Optional` comme **type de retour** d'une méthode qui peut ne rien trouver (par exemple `trouverParEmail`). Évite-le pour les attributs et les paramètres. N'appelle `get()` qu'après avoir vérifié `isPresent`, ou mieux, utilise `orElse`, `orElseThrow` et `ifPresent`.

## Quel outil pour quel besoin ?

- besoin d'une séquence ordonnée avec doublons : `List` ;
- besoin d'unicité ou de test d'appartenance rapide : `Set` ;
- besoin de retrouver une valeur par une clé : `Map` ;
- besoin de filtrer, transformer, agréger : `stream` ;
- une boucle avec effet de bord (écrire en base, modifier un objet) : reste une boucle `for`, pas un stream.

## Atelier guidé : statistiques sur un catalogue de cours

Compte une heure et demie. Crée un record `Cours(String titre, String categorie, int minutes, int niveau)` et une liste d'au moins dix cours répartis en trois catégories (par exemple Java, PHP, Design).

1. Affiche tous les titres triés par ordre alphabétique avec un stream.
2. Filtre les cours de plus de 90 minutes et affiche leur nombre.
3. Calcule la durée totale en heures, puis la durée moyenne, avec `mapToInt`.
4. Regroupe les cours par catégorie avec `groupingBy` et affiche le nombre de cours par catégorie.
5. Calcule, pour chaque catégorie, la durée totale avec `Collectors.summingInt`.
6. Trouve le cours le plus long avec `max` et affiche-le avec `Optional`, en gérant le cas d'une liste vide.
7. Construis une `Map` du titre vers la durée avec `toMap`, puis provoque volontairement un doublon et observe l'exception.
8. Écris un `Comparator` qui trie par niveau croissant puis par durée décroissante.
9. Bonus : produis un `Set` des catégories distinctes avec `Collectors.toSet()`.

Pour t'auto-évaluer : explique la différence entre une opération intermédiaire et terminale, et dis pourquoi `List.of` renvoie une liste que tu ne peux pas modifier.

## Erreurs fréquentes

- **Modifier une liste pendant qu'on la parcourt.** Une `ConcurrentModificationException` apparaît : utilise `removeIf` ou un itérateur.
- **Oublier `equals` et `hashCode`.** Les objets ne sont pas dédupliqués dans un `Set` ni retrouvés dans une `Map`.
- **Réutiliser un stream déjà consommé.** Il faut le recréer.
- **Appeler `get()` sur un `Optional` vide.** Cela lève une `NoSuchElementException`.
- **Confondre `remove(int)` et `remove(Object)`** sur une `List` d'entiers : le premier supprime par index, le second par valeur.
- **Mettre des effets de bord dans `map` ou `filter`.** Ces étapes doivent rester pures et sans modification externe.
- **Abuser des streams.** Un pipeline de dix lignes illisible est pire qu'une boucle claire.

## Bonnes pratiques

- Déclare le type par l'interface (`List`, `Map`) et instancie l'implémentation (`ArrayList`, `HashMap`).
- Préfère les collections immuables (`List.of`, `toList()`) quand les données ne changent pas.
- Donne des noms parlants aux lambdas et aux comparateurs réutilisés, en les extrayant dans des méthodes.
- Utilise les références de méthodes quand elles sont plus lisibles qu'une lambda.
- Casse un long pipeline en plusieurs variables intermédiaires bien nommées.
- Ne renvoie jamais `null` à la place d'une collection : renvoie une liste vide.
- Utilise `Optional` en retour de méthode, pas comme paramètre ni attribut.

## À retenir

- `List` pour l'ordre et les doublons, `Set` pour l'unicité, `Map` pour les paires clé-valeur.
- Les **génériques** typent les collections et évitent les conversions risquées.
- `Comparable` donne l'ordre naturel, `Comparator` offre des tris composables.
- Une **lambda** est une fonction concise ; les références de méthodes allègent encore l'écriture.
- Un **Stream** décrit un traitement : source, opérations intermédiaires, opération terminale.
- `Collectors.groupingBy` et `toMap` regroupent et transforment les données, comme `GROUP BY` en SQL.
- `Optional` rend l'absence de valeur explicite et réduit les `NullPointerException`.
