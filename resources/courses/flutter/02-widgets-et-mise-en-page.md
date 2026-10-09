---
title: Widgets et mise en page
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Tu sais maintenant que tout est widget. Reste à savoir **lesquels utiliser** et comment les disposer pour obtenir un écran propre, qui s'adapte aux petits et aux grands téléphones. Ce chapitre t'apprend le vocabulaire de la mise en page Flutter, en construisant l'écran d'accueil d'une application de cours.

À la fin du chapitre, tu seras capable de :

- distinguer `StatelessWidget` et `StatefulWidget`, et savoir lequel choisir ;
- utiliser `Text`, `Container`, `Padding`, `SizedBox`, `Icon` et `Image` ;
- aligner des widgets avec `Row`, `Column`, `Expanded` et `Stack` ;
- afficher de longues listes avec `ListView.builder` ;
- construire des cartes Material 3 (`Card`, `ListTile`) ;
- découper une interface en petits widgets réutilisables.

Prérequis : le chapitre « Découvrir Flutter et Dart ». Prévois deux heures. Tu peux tout essayer dans DartPad ou dans ton projet `devroad_mobile`.

## StatelessWidget : un widget qui ne change pas seul

Un `StatelessWidget` décrit une interface qui dépend uniquement de ses paramètres. Si les paramètres ne changent pas, l'affichage non plus. C'est le type de widget le plus courant.

```dart
import 'package:flutter/material.dart';

class CarteCours extends StatelessWidget {
  const CarteCours({
    super.key,
    required this.titre,
    required this.progression,
  });

  final String titre;
  final double progression; // entre 0 et 1

  @override
  Widget build(BuildContext context) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(titre, style: Theme.of(context).textTheme.titleMedium),
            const SizedBox(height: 8),
            LinearProgressIndicator(value: progression),
          ],
        ),
      ),
    );
  }
}
```

Les paramètres de la classe sont stockés dans des champs `final` : un widget est **immuable**. Pour changer ce qu'il affiche, on le reconstruit avec de nouvelles valeurs. Nous verrons au chapitre suivant comment gérer ce qui évolue grâce à `StatefulWidget`.

Remarque `Theme.of(context)` : le `BuildContext` est la position du widget dans l'arbre. Il permet de retrouver le thème, la taille de l'écran, ou la navigation.

## Les widgets de base

### Text et style

```dart
const Text(
  'Roadmap Flutter',
  style: TextStyle(
    fontSize: 20,
    fontWeight: FontWeight.bold,
    color: Colors.indigo,
  ),
  maxLines: 2,
  overflow: TextOverflow.ellipsis,
)
```

`maxLines` et `overflow` évitent qu'un titre trop long casse la mise en page : il est coupé avec des points de suspension.

### Padding, SizedBox, Container

- `Padding` ajoute de l'espace **autour** d'un enfant (`EdgeInsets.all(16)`, `EdgeInsets.symmetric(horizontal: 12)`).
- `SizedBox` impose une taille, ou crée un espacement vide : `SizedBox(height: 12)`.
- `Container` combine plusieurs rôles : couleur, bordure arrondie, ombre, marge, taille.

```dart
Container(
  padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
  decoration: BoxDecoration(
    color: Colors.green.shade100,
    borderRadius: BorderRadius.circular(20),
  ),
  child: const Text('Terminé'),
)
```

> **Astuce** : n'utilise pas `Container` par réflexe. Si tu veux seulement un espace, `SizedBox` ou `Padding` sont plus simples et plus légers.

### Icon et Image

```dart
const Icon(Icons.school, size: 32, color: Colors.indigo)

Image.asset('assets/logo.png', width: 80)
Image.network('https://exemple.ci/photo.jpg', height: 120, fit: BoxFit.cover)
```

Pour utiliser `Image.asset`, déclare le dossier dans `pubspec.yaml`. Attention à l'indentation, le YAML y est très sensible :

```yaml
flutter:
  uses-material-design: true
  assets:
    - assets/
```

## Organiser l'espace : Row, Column, Expanded

`Row` aligne ses enfants **horizontalement**, `Column` **verticalement**. Chacun a deux axes :

| Propriété | Rôle |
| --- | --- |
| `mainAxisAlignment` | Disposition le long de l'axe principal (horizontal pour `Row`, vertical pour `Column`) |
| `crossAxisAlignment` | Alignement sur l'axe perpendiculaire |
| `mainAxisSize` | Occuper tout l'espace (`max`) ou le minimum (`min`) |

```dart
Row(
  mainAxisAlignment: MainAxisAlignment.spaceBetween,
  crossAxisAlignment: CrossAxisAlignment.center,
  children: [
    const Icon(Icons.timer),
    const Text('45 min'),
    TextButton(onPressed: () {}, child: const Text('Voir')),
  ],
)
```

### Expanded et Flexible

Quand le contenu d'une `Row` dépasse la largeur de l'écran, Flutter affiche une bande jaune et noire : c'est l'erreur de débordement (*overflow*). `Expanded` répartit l'espace restant entre les enfants.

```dart
Row(
  children: [
    const Icon(Icons.menu_book),
    const SizedBox(width: 12),
    Expanded(
      child: Text(
        'Un titre de chapitre vraiment très très long qui doit passer à la ligne',
        maxLines: 2,
        overflow: TextOverflow.ellipsis,
      ),
    ),
    const Text('12 min'),
  ],
)
```

Avec `Expanded`, le titre prend l'espace libre, et l'icône et la durée gardent leur taille naturelle. Tu peux donner un poids avec `flex` : `Expanded(flex: 2, ...)` occupe deux fois plus de place qu'un `Expanded` de `flex` 1.

:::quiz
Une `Row` contient une icône, un très long `Text` et un bouton, et Flutter affiche une bande jaune et noire de débordement. Quelle est la solution la plus adaptée ?
- [ ] Remplacer la `Row` par un `Container`
- [ ] Réduire la taille de l'écran
- [x] Envelopper le `Text` dans un `Expanded`
- [ ] Ajouter `const` devant la `Row`
> `Expanded` donne au texte l'espace restant au lieu de lui laisser une largeur infinie, ce qui permet de le couper ou de le passer à la ligne.
:::

## Superposer avec Stack

`Stack` place ses enfants **les uns sur les autres**. C'est l'outil idéal pour un badge sur une image, ou un texte posé sur une photo.

```dart
Stack(
  children: [
    Image.asset('assets/couverture.png', width: double.infinity, height: 160, fit: BoxFit.cover),
    Positioned(
      right: 12,
      top: 12,
      child: Chip(label: const Text('Nouveau')),
    ),
    const Positioned(
      left: 16,
      bottom: 16,
      child: Text('Flutter', style: TextStyle(color: Colors.white, fontSize: 24)),
    ),
  ],
)
```

`Positioned` fixe l'emplacement d'un enfant par rapport aux bords du `Stack`. Sans `Positioned`, l'enfant se place en haut à gauche.

## Les listes : ListView

Une `Column` affiche tous ses enfants d'un coup et ne défile pas. Pour une liste qui défile, utilise `ListView`. Pour de longues listes, utilise le constructeur **`ListView.builder`**, qui ne construit que les éléments visibles à l'écran :

```dart
class ListeCours extends StatelessWidget {
  const ListeCours({super.key, required this.cours});

  final List<String> cours;

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: cours.length,
      itemBuilder: (context, index) {
        final titre = cours[index];
        return Card(
          child: ListTile(
            leading: const Icon(Icons.code),
            title: Text(titre),
            subtitle: const Text('7 chapitres'),
            trailing: const Icon(Icons.chevron_right),
            onTap: () {},
          ),
        );
      },
    );
  }
}
```

`ListTile` est un widget prêt à l'emploi pour une ligne de liste : icône à gauche, titre, sous-titre, action à droite. Utilise `ListView.separated` si tu veux un séparateur entre chaque élément.

> **Attention** : place un `ListView` dans une `Column` sans le contraindre et tu obtiens une erreur de hauteur infinie. Enveloppe-le dans un `Expanded`.

## Adapter l'interface à la taille de l'écran

Tous les téléphones n'ont pas la même largeur. Flutter te donne la taille avec `MediaQuery`, et la contrainte disponible avec `LayoutBuilder`.

```dart
Widget build(BuildContext context) {
  final largeur = MediaQuery.sizeOf(context).width;
  final colonnes = largeur > 600 ? 3 : 2;

  return GridView.count(
    crossAxisCount: colonnes,
    padding: const EdgeInsets.all(16),
    crossAxisSpacing: 12,
    mainAxisSpacing: 12,
    children: const [
      CarteCours(titre: 'Dart', progression: 0.4),
      CarteCours(titre: 'Flutter', progression: 0.1),
    ],
  );
}
```

Ici l'application affiche deux colonnes sur téléphone et trois sur tablette. Pense aussi à `SafeArea`, qui évite que ton contenu passe sous l'encoche ou la barre de navigation.

:::quiz
Quel widget choisir pour afficher une liste de 500 missions qui défile sans ralentir l'application ?
- [ ] `Column` avec 500 enfants
- [ ] `Row` avec 500 enfants
- [x] `ListView.builder`
- [ ] `Stack` avec 500 enfants
> `ListView.builder` ne construit que les éléments visibles, ce qui est bien plus économe qu'une `Column` qui construit tout.
:::

## Découper en petits widgets

Une méthode `build` de 200 lignes devient illisible. Découpe en **widgets dédiés**, pas en méthodes qui retournent des widgets : un widget séparé peut être `const`, ce qui aide les performances.

```dart
class EcranMissions extends StatelessWidget {
  const EcranMissions({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Missions à Abidjan')),
      body: const SafeArea(
        child: Column(
          children: [
            EnteteStats(),
            Expanded(child: ListeMissions()),
          ],
        ),
      ),
    );
  }
}
```

Chaque morceau a un nom qui dit ce qu'il fait. Quand tu relis le code dans trois semaines, tu comprends l'écran en un coup d'œil.

:::quiz
Pourquoi préfère-t-on créer une classe de widget plutôt qu'une méthode helper qui retourne un widget ?
- [ ] Parce que les méthodes sont interdites en Dart
- [x] Parce qu'une classe peut être `const` et se reconstruit séparément
- [ ] Parce que les classes s'exécutent plus tard
- [ ] Parce que Flutter ne supporte qu'une méthode `build`
> Un widget dédié peut être déclaré `const` et Flutter peut éviter de le reconstruire, ce que ne permet pas une simple méthode.
:::

## Atelier guidé : l'accueil d'une application de cours

Prévois environ une heure et demie.

1. Dans `devroad_mobile`, crée le dossier `lib/widgets/` et le fichier `carte_cours.dart` avec le widget `CarteCours` du chapitre.
2. Ajoute un paramètre `dureeMinutes` et affiche-le avec une `Icon(Icons.timer)` et un `Text` dans une `Row`.
3. Crée `lib/pages/accueil_page.dart` avec un `Scaffold`, une `AppBar` et un `SafeArea`.
4. Dans le `body`, ajoute une `Column` avec un titre « Mes cours » et un `Expanded` contenant une `ListView.builder`.
5. Génère une liste de cinq cours codés en dur (titre, progression, durée) et affiche une `CarteCours` pour chacun.
6. Ajoute un badge « Terminé » avec `Container` et `BoxDecoration` quand la progression vaut 1.
7. Remplace la liste par une `GridView.count` à deux colonnes quand la largeur dépasse 600 pixels.
8. Provoque volontairement un débordement (long titre sans `Expanded`), observe la bande jaune, puis corrige-le.

Pour t'auto-évaluer : sans regarder le cours, dessine sur papier l'arbre de widgets de ton écran, de `Scaffold` jusqu'à `Text`.

## Erreurs fréquentes

- **Une `ListView` dans une `Column` sans `Expanded`.** Flutter signale une hauteur infinie ; enveloppe la liste dans `Expanded`.
- **Une `Row` qui déborde.** Utilise `Expanded`, `Flexible` ou un `maxLines` avec `overflow`.
- **Confondre `mainAxisAlignment` et `crossAxisAlignment`.** Rappelle-toi que l'axe principal change selon `Row` ou `Column`.
- **Tout mettre dans un seul `build`.** Découpe en widgets nommés.
- **Oublier `SafeArea`.** Le contenu peut passer sous l'encoche ou la barre système.
- **Oublier de déclarer les assets dans `pubspec.yaml`.** L'image reste introuvable, même si le fichier existe.

## Bonnes pratiques

- Ajoute `const` partout où c'est possible.
- Utilise `SizedBox` pour les espacements plutôt que des `Container` vides.
- Préfère `ListView.builder` dès que la liste peut grandir.
- Reste cohérent sur les marges : par exemple 8, 12, 16 et 24 pixels uniquement.
- Teste sur un petit écran et sur un grand, en mode portrait et paysage.
- Récupère les couleurs et les styles de texte via `Theme.of(context)` plutôt que de les coder en dur.

## À retenir

- Un `StatelessWidget` décrit une interface qui dépend seulement de ses paramètres.
- `Row` et `Column` alignent ; `Expanded` répartit l'espace ; `Stack` superpose.
- `ListView.builder` est l'outil des listes longues ; `ListTile` et `Card` accélèrent la mise en place.
- `MediaQuery` et `LayoutBuilder` permettent d'adapter l'écran à sa taille.
- Découpe ton interface en petits widgets nommés et `const`.
- Une erreur de débordement n'est pas un bug mystérieux : c'est un manque de contrainte, qu'on règle avec `Expanded`.
