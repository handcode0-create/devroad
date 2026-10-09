---
title: Découvrir Flutter et Dart
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Flutter est le framework de Google pour construire des applications mobiles (Android et iOS), web et desktop avec **un seul code source**. Il s'appuie sur un langage, **Dart**. Ce chapitre te donne les bases des deux : comment Flutter pense l'interface, comment lire et écrire du Dart moderne, et comment lancer ta première application.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est Flutter, ce qu'est Dart, et pourquoi on les associe ;
- installer l'environnement et lancer une application avec `flutter run` ;
- lire la structure d'un projet Flutter (`lib/`, `pubspec.yaml`) ;
- écrire du Dart de base : variables, types, fonctions, classes, listes ;
- utiliser la **null safety** (`?`, `??`, `!`) sans te piéger ;
- comprendre l'idée centrale : *tout est widget*.

Prérequis : avoir déjà écrit un peu de code dans un langage quelconque (JavaScript, PHP, Python). Prévois deux heures. Pour tester du Dart sans rien installer, utilise DartPad (dartpad.dev) directement dans ton navigateur.

## Pourquoi Flutter ?

Avant Flutter, publier une application sur Android et iOS demandait deux équipes et deux langages (Kotlin et Swift). Des solutions hybrides existaient, mais elles reposaient sur une page web affichée dans une application ou sur des ponts vers les composants natifs.

Flutter a choisi une autre voie : il **dessine lui-même chaque pixel** de l'écran avec son propre moteur graphique. Conséquences concrètes :

- l'application a **le même aspect** sur un vieux téléphone Android et sur un iPhone ;
- les animations sont fluides, car le moteur contrôle tout le rendu ;
- un seul code source suffit pour plusieurs plateformes ;
- le **Hot Reload** met à jour l'écran en moins d'une seconde pendant que tu codes, sans perdre l'état de l'application.

Pour un développeur en Côte d'Ivoire qui vise des clients d'Afrique de l'Ouest, c'est un avantage réel : la majorité des utilisateurs sont sur Android, souvent avec des appareils modestes, et une seule base de code te permet de couvrir aussi iOS plus tard.

> **À retenir** : Flutter est le **framework** (les composants d'interface, le moteur de rendu, les outils) ; Dart est le **langage** dans lequel tu écris ton code.

## Installer et lancer ton premier projet

Suis le guide d'installation officiel sur docs.flutter.dev pour ton système, puis vérifie ton installation :

```bash
flutter doctor
```

Cette commande liste ce qui manque (SDK Android, éditeur, émulateur). Corrige les lignes marquées d'une croix avant de continuer. Crée ensuite ton projet et lance-le :

```bash
flutter create devroad_mobile
cd devroad_mobile
flutter run
```

Tu peux choisir un émulateur Android, un téléphone branché en USB ou Chrome pour tester rapidement. Pendant que l'application tourne, appuie sur `r` dans le terminal pour un Hot Reload, et sur `R` pour un redémarrage complet.

### La structure d'un projet

Voici les éléments importants du dossier créé :

| Élément | Rôle |
| --- | --- |
| `lib/main.dart` | Point d'entrée : c'est ici que ton code commence |
| `pubspec.yaml` | Nom, dépendances, assets et polices du projet |
| `android/`, `ios/`, `web/` | Code natif de chaque plateforme (tu y touches rarement) |
| `test/` | Tes tests automatisés |

Le fichier `pubspec.yaml` déclare les dépendances. Pour ajouter un paquet, tu utilises la commande suivante, qui modifie le fichier pour toi :

```bash
flutter pub add go_router
```

Ton code vit presque entièrement dans `lib/`. Retiens cette règle : tu écris de Dart dans `lib/`, tu configures dans `pubspec.yaml`.

## Les bases de Dart

Dart ressemble à un mélange de JavaScript, Java et TypeScript. Il est **typé** : chaque valeur a un type connu à la compilation, ce qui évite beaucoup d'erreurs.

### Variables et types

```dart
void main() {
  String prenom = 'Awa';
  int chapitres = 12;
  double progression = 0.75;
  bool termine = false;

  // Dart devine le type avec var
  var ville = 'Abidjan';

  // final : valeur affectée une seule fois, à l'exécution
  final maintenant = DateTime.now();

  // const : valeur connue dès la compilation
  const tva = 0.18;

  print('Bonjour $prenom de $ville');
  print('Progression : ${progression * 100} %');
}
```

Deux détails importants. L'**interpolation** s'écrit `$variable` pour une variable simple et `${expression}` pour un calcul. Et on préfère `final` à `var` dès que la valeur ne change plus : le code est plus sûr.

:::quiz
Quelle déclaration crée une valeur qui ne pourra plus être modifiée après son affectation et dont le type est deviné par Dart ?
- [ ] `var age = 20;`
- [x] `final age = 20;`
- [ ] `int age;`
- [ ] `dynamic age = 20;`
> `final` interdit toute nouvelle affectation, et Dart déduit le type `int` de la valeur. Avec `var`, la variable peut être réaffectée.
:::

### Fonctions

```dart
int additionner(int a, int b) {
  return a + b;
}

// Syntaxe courte avec une flèche
int doubler(int n) => n * 2;

// Paramètres nommés : plus lisibles à l'appel
String salutation({required String nom, String ville = 'Abidjan'}) {
  return 'Salut $nom, bienvenue à $ville';
}

void main() {
  print(additionner(2, 3));
  print(salutation(nom: 'Koffi'));
}
```

Les **paramètres nommés** (entre accolades) sont partout dans Flutter. Le mot `required` oblige à les fournir ; sans lui, il faut une valeur par défaut ou un type nullable.

### Collections

```dart
void main() {
  final langages = ['Dart', 'PHP', 'JavaScript'];
  langages.add('Kotlin');

  final scores = {'Awa': 80, 'Koffi': 65};
  scores['Mariam'] = 92;

  // Transformer une liste
  final majuscules = langages.map((l) => l.toUpperCase()).toList();

  // Filtrer
  final bons = scores.entries.where((e) => e.value >= 70);

  for (final entree in bons) {
    print('${entree.key} : ${entree.value}');
  }
  print(majuscules);
}
```

Retiens `map`, `where` et `toList()` : tu les utiliseras sans cesse pour transformer des listes de données en listes de widgets.

## La null safety

Dart distingue les valeurs qui **peuvent être nulles** de celles qui **ne le peuvent pas**. Un type simple comme `String` ne peut jamais être `null`. Pour l'autoriser, on ajoute `?`.

```dart
String? surnom;               // peut être null
String nom = 'Awa';           // ne sera jamais null

void main() {
  // Opérateur ?? : valeur de repli
  print(surnom ?? 'Pas de surnom');

  // Opérateur ?. : appel sécurisé
  print(surnom?.length);       // null, sans erreur

  // Opérateur ??= : affecter si null
  surnom ??= 'Aya';

  // Promotion de type après un test
  if (surnom != null) {
    print(surnom.toUpperCase());
  }
}
```

L'opérateur `!` dit « je suis certain que ce n'est pas null ». Si tu te trompes, l'application plante. Utilise-le le moins possible.

> **Attention** : écrire `valeur!` partout pour faire taire le compilateur est un piège. Préfère un test `if (valeur != null)`, un `??` ou un `?.`.

## Les classes

Une classe regroupe des données et des comportements. Voici le modèle d'une roadmap de DevRoad :

```dart
class Roadmap {
  final String titre;
  final int totalEtapes;
  int etapesTerminees;

  Roadmap({
    required this.titre,
    required this.totalEtapes,
    this.etapesTerminees = 0,
  });

  double get progression =>
      totalEtapes == 0 ? 0 : etapesTerminees / totalEtapes;

  bool get estTerminee => etapesTerminees >= totalEtapes;

  void terminerEtape() {
    if (!estTerminee) etapesTerminees++;
  }
}

void main() {
  final flutter = Roadmap(titre: 'Flutter', totalEtapes: 7);
  flutter.terminerEtape();
  print('${flutter.titre} : ${(flutter.progression * 100).round()} %');
}
```

Trois éléments à noter. Le constructeur utilise `this.titre` pour affecter directement le champ. Les `get` sont des propriétés calculées. Et les champs `final` doivent être fournis à la construction.

:::quiz
Dans `Roadmap({required this.titre, this.etapesTerminees = 0})`, que se passe-t-il si on écrit `Roadmap(titre: 'Dart')` ?
- [ ] Une erreur : `etapesTerminees` est obligatoire
- [x] La roadmap est créée avec `etapesTerminees` égal à 0
- [ ] La roadmap est créée avec `titre` égal à null
- [ ] Une erreur à l'exécution seulement
> Les paramètres nommés non `required` utilisent leur valeur par défaut. Ici `etapesTerminees` vaut 0.
:::

## Le principe central : tout est widget

En Flutter, **tout ce que tu vois à l'écran est un widget** : un texte, un bouton, une marge, une colonne, et même l'application entière. Un widget est une classe qui décrit une portion d'interface. Tu construis ton écran en **imbriquant** des widgets, comme des poupées russes.

Voici ta première application complète, à mettre dans `lib/main.dart` :

```dart
import 'package:flutter/material.dart';

void main() {
  runApp(const DevRoadApp());
}

class DevRoadApp extends StatelessWidget {
  const DevRoadApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'DevRoad',
      theme: ThemeData(
        colorSchemeSeed: Colors.indigo,
        useMaterial3: true,
      ),
      home: const AccueilPage(),
    );
  }
}

class AccueilPage extends StatelessWidget {
  const AccueilPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('DevRoad')),
      body: const Center(
        child: Text('Bienvenue, prêt à apprendre ?'),
      ),
    );
  }
}
```

Décortiquons. `runApp` démarre l'application avec un widget racine. `MaterialApp` configure le thème et l'écran d'accueil. `Scaffold` fournit la structure d'une page Material : barre du haut (`appBar`), contenu (`body`). `Center` place son enfant au milieu, et `Text` affiche du texte.

La méthode `build` **retourne l'arbre de widgets** correspondant à l'écran. Si tu connais React, c'est la même idée : l'interface est le résultat d'une description, pas d'une suite d'ordres.

> **Astuce** : le mot `const` devant un widget indique qu'il ne changera jamais. Flutter peut alors le réutiliser sans le reconstruire, ce qui économise de la performance. L'éditeur te propose de l'ajouter automatiquement.

:::quiz
Que retourne la méthode `build` d'un widget ?
- [ ] Une chaîne de caractères HTML
- [ ] Un fichier de mise en page séparé
- [x] Un autre widget, qui décrit l'interface
- [ ] Une valeur booléenne
> `build` retourne un widget (souvent composé d'autres widgets). C'est ainsi que se forme l'arbre d'interface.
:::

## Atelier guidé : ton premier écran DevRoad

Prévois environ une heure.

1. Exécute `flutter create devroad_mobile`, ouvre le dossier dans ton éditeur et lance `flutter run`.
2. Remplace tout le contenu de `lib/main.dart` par l'application de ce chapitre et fais un Hot Reload.
3. Change `colorSchemeSeed` en `Colors.teal` et observe le thème se mettre à jour.
4. Crée une classe `Roadmap` dans un nouveau fichier `lib/roadmap.dart`, avec `titre`, `totalEtapes` et `etapesTerminees`.
5. Dans `main.dart`, importe-la avec `import 'roadmap.dart';` et crée une liste de trois roadmaps codées en dur.
6. Affiche le titre de la première roadmap dans le `Text` central, avec son pourcentage de progression.
7. Ajoute une propriété nullable `String? description` à `Roadmap` et affiche `description ?? 'Aucune description'`.
8. Fais volontairement une faute de frappe dans un nom, lis le message d'erreur du terminal, puis corrige-la.

Pour t'auto-évaluer : explique à voix haute la différence entre `final` et `const`, puis entre `String` et `String?`, et dis pourquoi `build` peut être appelée plusieurs fois.

## Erreurs fréquentes

- **Oublier d'importer Material.** Sans `import 'package:flutter/material.dart';`, `Scaffold` et `Text` sont inconnus.
- **Confondre Hot Reload et redémarrage.** Un changement dans `main()` ou dans un état initial demande un Hot Restart (`R`).
- **Abuser du `!`.** Cela déplace l'erreur du compilateur vers l'exécution, où l'utilisateur la subit.
- **Oublier `required` ou une valeur par défaut** sur un paramètre nommé non nullable : le compilateur refuse le code.
- **Modifier `pubspec.yaml` avec une mauvaise indentation.** Le YAML est sensible aux espaces ; lance `flutter pub get` après chaque changement.
- **Écrire une logique lourde dans `build`.** Cette méthode peut être appelée très souvent : garde-la rapide.

## Bonnes pratiques

- Préfère `final` à `var`, et `const` pour tout ce qui est constant.
- Donne aux classes des noms en PascalCase et aux variables des noms en camelCase.
- Un fichier par classe importante, rangé dans `lib/`.
- Utilise `flutter analyze` régulièrement pour attraper les problèmes tôt.
- Formate ton code avec `dart format .` pour garder un style uniforme.
- Écris tes noms de données en français ou en anglais, mais reste cohérent dans tout le projet.

## À retenir

- Flutter dessine lui-même l'interface ; Dart est le langage utilisé pour l'écrire.
- Un projet vit dans `lib/` et se configure dans `pubspec.yaml`.
- Dart est typé et protège contre le null grâce à `?`, `??` et `?.`.
- Les paramètres nommés et `required` sont omniprésents en Flutter.
- Tout est widget : `build` retourne un arbre de widgets imbriqués.
- Le Hot Reload accélère énormément ton travail : utilise-le à chaque modification.
