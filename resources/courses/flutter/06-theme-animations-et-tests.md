---
title: Thème, animations et tests
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application qui fonctionne, c'est bien. Une application cohérente, fluide et fiable, c'est ce qui donne confiance aux utilisateurs. Ce chapitre ajoute les trois couches de finition d'un projet professionnel : un **thème** unique réutilisable (couleurs, typographie, mode sombre), des **animations** qui guident l'œil sans ralentir l'application, et des **tests** qui t'évitent de casser ce qui marche.

À la fin du chapitre, tu seras capable de :

- définir un thème Material 3 avec `ColorScheme.fromSeed` et `ThemeData` ;
- gérer un mode clair et un mode sombre, y compris un choix manuel de l'utilisateur ;
- personnaliser la typographie et les composants (boutons, cartes, champs) globalement ;
- utiliser des animations implicites (`AnimatedContainer`, `AnimatedOpacity`) ;
- créer une animation explicite avec `AnimationController` et `Tween` ;
- ajouter une transition entre pages et une animation `Hero` ;
- écrire des tests unitaires et des tests de widgets avec `flutter_test` ;
- isoler un provider Riverpod dans un test grâce aux surcharges.

Prérequis : les chapitres 1 à 5. Prévois deux heures et demie. Pour les tests, tu n'as besoin d'aucun appareil : ils s'exécutent en ligne de commande.

## Le thème : une identité définie une seule fois

Quand tu écris `Colors.teal` ou `fontSize: 18` dans cinquante widgets, changer l'apparence devient un cauchemar. Un **thème** centralise ces décisions. Les widgets Material lisent automatiquement le thème de l'application via `Theme.of(context)`.

Material 3 génère toute une palette cohérente à partir d'**une seule couleur de départ**, la *seed* :

```dart
import 'package:flutter/material.dart';

class ThemeApp {
  static const _couleurMarque = Color(0xFF0F766E); // un vert-teal

  static ThemeData clair() => _construire(Brightness.light);
  static ThemeData sombre() => _construire(Brightness.dark);

  static ThemeData _construire(Brightness luminosite) {
    final schema = ColorScheme.fromSeed(
      seedColor: _couleurMarque,
      brightness: luminosite,
    );

    return ThemeData(
      useMaterial3: true,
      colorScheme: schema,
      textTheme: const TextTheme(
        headlineMedium: TextStyle(fontWeight: FontWeight.w700),
        titleMedium: TextStyle(fontWeight: FontWeight.w600),
      ),
      cardTheme: CardTheme(
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: BorderSide(color: schema.outlineVariant),
        ),
      ),
      inputDecorationTheme: InputDecorationTheme(
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        filled: true,
      ),
      filledButtonTheme: FilledButtonThemeData(
        style: FilledButton.styleFrom(
          minimumSize: const Size.fromHeight(52),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
    );
  }
}
```

Il suffit ensuite de l'enregistrer :

```dart
MaterialApp.router(
  routerConfig: router,
  theme: ThemeApp.clair(),
  darkTheme: ThemeApp.sombre(),
  themeMode: ThemeMode.system, // suit le réglage du téléphone
)
```

Dans tes widgets, tu **lis** le thème au lieu de coder des valeurs en dur :

```dart
Widget build(BuildContext context) {
  final couleurs = Theme.of(context).colorScheme;
  final textes = Theme.of(context).textTheme;

  return Card(
    color: couleurs.surfaceContainerHighest,
    child: Padding(
      padding: const EdgeInsets.all(16),
      child: Text('Mission terminée', style: textes.titleMedium?.copyWith(color: couleurs.primary)),
    ),
  );
}
```

Le mode sombre fonctionne alors sans une ligne de code supplémentaire dans tes écrans, car `couleurs.surfaceContainerHighest` change de valeur selon la luminosité.

> **Attention** : n'écris pas `Colors.white` ou `Colors.black` pour un fond ou un texte. Ces valeurs ne s'adaptent pas au mode sombre et rendent le texte illisible. Utilise `colorScheme.surface` et `colorScheme.onSurface`.

### Laisser l'utilisateur choisir

Le choix clair, sombre ou système est un état modifiable : un `Notifier` Riverpod convient parfaitement.

```dart
class ModeTheme extends Notifier<ThemeMode> {
  @override
  ThemeMode build() => ThemeMode.system;

  void changer(ThemeMode mode) => state = mode;
}

final modeThemeProvider = NotifierProvider<ModeTheme, ThemeMode>(ModeTheme.new);
```

Dans `MonApp`, devenu un `ConsumerWidget`, lis `ref.watch(modeThemeProvider)` et passe la valeur à `themeMode`. Pour conserver le choix entre deux lancements, enregistre-le avec le package `shared_preferences`.

:::quiz
Pourquoi vaut-il mieux utiliser `Theme.of(context).colorScheme.surface` que `Colors.white` ?
- [ ] Parce que `Colors.white` n'existe plus avec Material 3
- [x] Parce que la couleur du thème s'adapte automatiquement au mode sombre et à la marque
- [ ] Parce que c'est plus rapide à écrire
- [ ] Parce que `Colors.white` consomme plus de mémoire
> Les couleurs du `ColorScheme` changent selon la luminosité. Une couleur codée en dur reste identique et casse le contraste en mode sombre.
:::

## Animer l'interface

Une bonne animation a un but : montrer qu'un élément apparaît, a changé ou a été pris en compte. Flutter propose deux familles, des plus simples aux plus puissantes.

### Animations implicites : on change la valeur, Flutter anime

Les widgets `Animated...` interpolent tout seuls entre l'ancienne et la nouvelle valeur d'une propriété. Tu n'écris aucun contrôleur :

```dart
class CarteSelectionnable extends StatefulWidget {
  const CarteSelectionnable({super.key, required this.titre});
  final String titre;

  @override
  State<CarteSelectionnable> createState() => _CarteSelectionnableState();
}

class _CarteSelectionnableState extends State<CarteSelectionnable> {
  bool _choisie = false;

  @override
  Widget build(BuildContext context) {
    final couleurs = Theme.of(context).colorScheme;

    return GestureDetector(
      onTap: () => setState(() => _choisie = !_choisie),
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        curve: Curves.easeOutCubic,
        padding: const EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: _choisie ? couleurs.primaryContainer : couleurs.surface,
          borderRadius: BorderRadius.circular(_choisie ? 24 : 12),
          border: Border.all(color: _choisie ? couleurs.primary : couleurs.outlineVariant),
        ),
        child: Text(widget.titre),
      ),
    );
  }
}
```

Au toucher, la couleur, la bordure et le rayon des coins se transforment en douceur pendant 250 millisecondes. Parmi les widgets implicites utiles : `AnimatedOpacity`, `AnimatedAlign`, `AnimatedSize`, `AnimatedSwitcher` (transition entre deux widgets) et `AnimatedCrossFade`.

> **Astuce** : garde les durées entre 150 et 350 millisecondes. En dessous, l'œil ne voit rien ; au-dessus, l'application paraît lente. Sur les téléphones d'entrée de gamme, plus c'est sobre, mieux c'est.

### Animations explicites : contrôler le temps

Quand tu veux répéter, enchaîner, ou arrêter une animation, tu utilises un `AnimationController`. Il a besoin d'un `TickerProvider`, fourni par le mixin `SingleTickerProviderStateMixin` :

```dart
class IndicateurPulsation extends StatefulWidget {
  const IndicateurPulsation({super.key});

  @override
  State<IndicateurPulsation> createState() => _IndicateurPulsationState();
}

class _IndicateurPulsationState extends State<IndicateurPulsation>
    with SingleTickerProviderStateMixin {
  late final AnimationController _controleur;
  late final Animation<double> _echelle;

  @override
  void initState() {
    super.initState();
    _controleur = AnimationController(
      vsync: this,
      duration: const Duration(milliseconds: 900),
    )..repeat(reverse: true);

    _echelle = Tween<double>(begin: 0.85, end: 1.15).animate(
      CurvedAnimation(parent: _controleur, curve: Curves.easeInOut),
    );
  }

  @override
  void dispose() {
    _controleur.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return ScaleTransition(
      scale: _echelle,
      child: const Icon(Icons.location_on, size: 40, color: Colors.redAccent),
    );
  }
}
```

Le contrôleur produit une valeur de 0 à 1 dans le temps. Le `Tween` convertit cette valeur en échelle de 0,85 à 1,15. `ScaleTransition` redessine uniquement le widget concerné, sans reconstruire le reste. Et surtout : **`dispose` est obligatoire**, sinon le contrôleur continue de tourner et de consommer la batterie.

:::quiz
Quel est le bon choix pour faire changer en douceur la couleur d'un conteneur quand l'utilisateur le touche ?
- [ ] Un `AnimationController` écrit à la main dans tous les cas
- [x] Un `AnimatedContainer` : il anime seul entre l'ancienne et la nouvelle valeur
- [ ] Un `Timer` qui appelle `setState` toutes les millisecondes
- [ ] Un `FutureBuilder`
> Les widgets d'animation implicite sont conçus pour ce cas. On réserve `AnimationController` aux animations répétées, enchaînées ou contrôlées finement.
:::

### Transitions entre écrans : Hero et pages personnalisées

L'animation `Hero` fait « voyager » un élément d'un écran à l'autre, par exemple la photo d'une mission qui s'agrandit vers l'écran de détail. Il suffit de donner la **même étiquette** au widget sur les deux pages :

```dart
// Dans la liste
Hero(
  tag: 'mission-${mission.id}',
  child: CircleAvatar(child: Text(mission.titre[0])),
),

// Dans le détail
Hero(
  tag: 'mission-${mission.id}',
  child: CircleAvatar(radius: 48, child: Text(mission.titre[0])),
),
```

Avec GoRouter, une transition personnalisée s'écrit avec `pageBuilder` et `CustomTransitionPage` :

```dart
GoRoute(
  path: '/missions/:id',
  pageBuilder: (context, state) => CustomTransitionPage(
    key: state.pageKey,
    child: DetailMissionPage(missionId: state.pathParameters['id']!),
    transitionsBuilder: (context, animation, _, child) =>
        FadeTransition(opacity: animation, child: child),
  ),
),
```

## Tester : pourquoi et quoi ?

Un test est un petit programme qui vérifie le comportement du tien. Il te protège des **régressions** : tu modifies une fonction, tu relances les tests, et si quelque chose a cassé ailleurs, tu le sais en quelques secondes. Flutter distingue trois niveaux :

| Niveau | Ce qu'il vérifie | Vitesse |
| --- | --- | --- |
| Test unitaire | Une fonction ou une classe pure (validation, modèle, notifier) | Très rapide |
| Test de widget | Un widget isolé : texte affiché, réaction à un appui | Rapide |
| Test d'intégration | L'application entière sur un appareil | Lent |

La règle d'or de la pyramide : beaucoup de tests unitaires, un bon nombre de tests de widgets, peu de tests d'intégration. Les fichiers de test vivent dans le dossier `test/` et se terminent par `_test.dart`.

### Test unitaire

Reprenons la validation du téléphone du chapitre 4 :

```dart
import 'package:flutter_test/flutter_test.dart';
import 'package:missions_app/validateurs.dart';

void main() {
  group('validerTelephone', () {
    test('refuse un champ vide', () {
      expect(validerTelephone(''), 'Le numéro est obligatoire');
    });

    test('refuse un numéro trop court', () {
      expect(validerTelephone('0160'), isNotNull);
    });

    test('accepte 10 chiffres, même avec des espaces', () {
      expect(validerTelephone('01 60 70 62 21'), isNull);
    });
  });
}
```

On lance les tests avec :

```bash
flutter test
```

Un bon test suit la structure **Arrange, Act, Assert** : préparer les données, exécuter l'action, vérifier le résultat. Donne-lui un nom qui décrit le comportement attendu, pas l'implémentation.

### Test de widget

`WidgetTester` permet d'afficher un widget et d'interagir avec lui, sans émulateur :

```dart
import 'package:flutter/material.dart';
import 'package:flutter_test/flutter_test.dart';

class Compteur extends StatefulWidget {
  const Compteur({super.key});
  @override
  State<Compteur> createState() => _CompteurState();
}

class _CompteurState extends State<Compteur> {
  int _valeur = 0;
  @override
  Widget build(BuildContext context) => Column(children: [
        Text('Valeur : $_valeur'),
        FilledButton(
          onPressed: () => setState(() => _valeur++),
          child: const Text('Ajouter'),
        ),
      ]);
}

void main() {
  testWidgets('le compteur augmente au toucher', (tester) async {
    await tester.pumpWidget(const MaterialApp(home: Scaffold(body: Compteur())));

    expect(find.text('Valeur : 0'), findsOneWidget);

    await tester.tap(find.text('Ajouter'));
    await tester.pump(); // reconstruit l'interface

    expect(find.text('Valeur : 1'), findsOneWidget);
  });
}
```

`find` repère des widgets (par texte, type ou clé), `tap` simule un doigt, et `pump` demande à Flutter de redessiner. Pour une animation, `pumpAndSettle` attend qu'elle se termine.

### Tester avec Riverpod : les surcharges

Pour tester un écran qui dépend d'une API, remplace le dépôt réel par un **faux dépôt** grâce à `overrides` :

```dart
class FauxDepot extends DepotMissions {
  FauxDepot() : super(baseUrl: '');

  @override
  Future<List<Mission>> listerMissions() async => const [
        Mission(id: 1, titre: 'Livrer un colis', budget: 2000, quartier: 'Cocody'),
      ];
}

void main() {
  testWidgets('affiche les missions du dépôt', (tester) async {
    await tester.pumpWidget(
      ProviderScope(
        overrides: [depotMissionsProvider.overrideWithValue(FauxDepot())],
        child: const MaterialApp(home: ListeMissionsPage()),
      ),
    );

    await tester.pumpAndSettle();

    expect(find.text('Livrer un colis'), findsOneWidget);
  });
}
```

Le test ne dépend plus du réseau : il est rapide, reproductible, et fonctionne même hors connexion. C'est la récompense d'avoir isolé l'accès aux données dans un dépôt au chapitre précédent.

:::quiz
Que fait `await tester.pumpAndSettle()` dans un test de widget ?
- [ ] Il ferme le test immédiatement
- [ ] Il envoie une requête réseau réelle
- [x] Il reconstruit l'interface jusqu'à ce que les animations et tâches planifiées soient terminées
- [ ] Il efface le thème de l'application
> `pumpAndSettle` appelle `pump` en boucle tant que des images sont planifiées, ce qui laisse finir les animations et les `Future` simples.
:::

## Atelier guidé : donner une identité et sécuriser le code

Compte une heure et demie sur ton projet de missions.

1. Crée `theme.dart` avec `ThemeApp.clair()` et `ThemeApp.sombre()`, basés sur une couleur de seed de ton choix.
2. Branche `theme`, `darkTheme` et `themeMode` dans `MaterialApp.router`.
3. Remplace toutes les couleurs codées en dur par des valeurs de `colorScheme`.
4. Ajoute un `modeThemeProvider` et un `SegmentedButton` Clair / Sombre / Système dans l'écran de profil.
5. Dans la liste, anime l'apparition des filtres avec `AnimatedSwitcher`, puis ajoute un `Hero` entre la liste et le détail.
6. Crée un indicateur de pulsation avec `AnimationController` et vérifie qu'il est bien libéré dans `dispose`.
7. Écris trois tests unitaires pour tes validateurs, dont un cas limite (espaces, lettres).
8. Écris un test de widget qui vérifie que la liste affiche les missions d'un faux dépôt.
9. Lance `flutter test` et corrige jusqu'au vert.

Pour t'auto-évaluer : sais-tu expliquer quand préférer une animation implicite plutôt qu'explicite ? Peux-tu dire pourquoi un test de widget n'a pas besoin d'émulateur ? Ton application reste-t-elle lisible en mode sombre ?

## Erreurs fréquentes

- **Coder les couleurs en dur.** Le mode sombre devient illisible et la marque difficile à changer.
- **Oublier `useMaterial3: true`** dans un projet ancien : les composants gardent l'ancien style.
- **Ne pas appeler `dispose` sur un `AnimationController`.** Fuite de mémoire et message d'erreur au quittage de l'écran.
- **Mettre une animation trop longue.** Plus de 400 millisecondes, l'interface paraît lente.
- **Tester l'implémentation plutôt que le comportement.** Le test casse au moindre refactoring sans avoir protégé de bug.
- **Oublier `pump` après un `tap`.** L'interface n'est pas reconstruite et l'assertion échoue.
- **Dépendre du réseau dans les tests.** Ils deviennent lents et instables ; utilise des faux dépôts.

## Bonnes pratiques

- Définis ton thème une fois, dans un seul fichier, et ne le contourne pas.
- Teste ton application en mode clair, en mode sombre et avec un texte agrandi dans les réglages d'accessibilité.
- Choisis des animations utiles et sobres : confirmation, continuité, hiérarchie.
- Respecte l'option système « réduire les animations » en consultant `MediaQuery.of(context).disableAnimations`.
- Écris d'abord des tests pour la logique pure (validateurs, calculs de montants) : ils sont les plus rentables.
- Nomme chaque test avec une phrase qui décrit le comportement attendu.
- Lance `flutter test` avant chaque commit, idéalement dans une intégration continue.

## À retenir

- Un thème centralise couleurs, typographie et styles de composants ; `ColorScheme.fromSeed` génère une palette cohérente.
- Lis le thème avec `Theme.of(context)` et propose un mode sombre via `darkTheme` et `themeMode`.
- Les animations implicites (`AnimatedContainer`...) suffisent dans la majorité des cas ; l'`AnimationController` sert aux animations contrôlées et doit être libéré.
- `Hero` et `CustomTransitionPage` soignent les transitions entre écrans.
- Les tests unitaires, de widgets et d'intégration forment une pyramide : privilégie la base.
- `testWidgets`, `find`, `tap` et `pump` permettent de tester l'interface sans appareil.
- Les `overrides` de `ProviderScope` remplacent les dépendances réelles par des faux dans les tests.
