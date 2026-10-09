---
title: State et interactions
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Jusqu'ici, tes écrans affichent des données figées. Une vraie application réagit : on touche un bouton, on coche une étape, on tape dans un champ, et l'écran change. Pour cela, Flutter doit **mémoriser** des valeurs entre deux affichages. Cette mémoire s'appelle le **state** (l'état), et on la manipule avec `StatefulWidget` et `setState`.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi une simple variable ne suffit pas pour mettre à jour l'écran ;
- écrire un `StatefulWidget` avec sa classe `State` et appeler `setState` ;
- gérer les interactions : `ElevatedButton`, `Checkbox`, `Switch`, `TextField` ;
- mettre à jour des listes sans les muter de façon dangereuse ;
- « remonter l'état » dans le parent commun lorsque deux widgets le partagent ;
- connaître les bases du cycle de vie (`initState`, `dispose`).

Prérequis : le chapitre « Widgets et mise en page ». Prévois deux heures et demie.

## Pourquoi une variable ne suffit pas

Essayons un compteur d'étapes terminées avec un `StatelessWidget` et une variable :

```dart
class CompteurEtapes extends StatelessWidget {
  CompteurEtapes({super.key});

  int terminees = 0;

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: () {
        terminees++;
        print(terminees);
      },
      child: Text('Terminées : $terminees'),
    );
  }
}
```

Appuie sur le bouton : la console affiche 1, 2, 3, mais **l'écran reste à 0**. Pourquoi ?

1. Un `StatelessWidget` est immuable : il est conçu pour ne pas changer, et Flutter peut le recréer à tout moment, ce qui remettrait la variable à zéro.
2. Modifier une variable ne **prévient pas** Flutter. Il ne sait pas qu'il doit redessiner l'écran.

Il nous faut un mécanisme qui conserve la valeur **et** demande un nouvel affichage. C'est le rôle de `State`.

## StatefulWidget et setState

Un `StatefulWidget` est composé de deux classes : le widget lui-même (immuable) et sa classe `State` (qui vit longtemps et garde les données).

```dart
import 'package:flutter/material.dart';

class CompteurEtapes extends StatefulWidget {
  const CompteurEtapes({super.key});

  @override
  State<CompteurEtapes> createState() => _CompteurEtapesState();
}

class _CompteurEtapesState extends State<CompteurEtapes> {
  int _terminees = 0;

  void _ajouter() {
    setState(() {
      _terminees++;
    });
  }

  @override
  Widget build(BuildContext context) {
    return ElevatedButton(
      onPressed: _ajouter,
      child: Text('Terminées : $_terminees'),
    );
  }
}
```

Le point clé est `setState`. Tu y places le changement de donnée, et Flutter **rappelle `build`** pour redessiner l'écran avec la nouvelle valeur. Sans `setState`, la donnée change mais l'écran non.

> **Astuce** : dans VS Code et Android Studio, tape `stful` puis valide pour générer automatiquement le squelette d'un `StatefulWidget`.

Le préfixe `_` rend un membre privé au fichier. On l'utilise pour les champs et méthodes internes du `State`.

:::quiz
Que se passe-t-il si tu modifies `_terminees++` sans l'envelopper dans `setState` ?
- [ ] Flutter lève une exception immédiatement
- [x] La valeur change en mémoire, mais l'écran n'est pas redessiné
- [ ] L'application redémarre
- [ ] La valeur n'est pas modifiée du tout
> Sans `setState`, Flutter ne sait pas qu'il doit appeler `build` à nouveau. La donnée est bien modifiée, mais l'affichage reste ancien.
:::

## Réagir aux interactions

Flutter propose des widgets interactifs prêts à l'emploi. Chacun expose un **callback** : une fonction appelée quand l'utilisateur agit.

| Widget | Callback | Usage |
| --- | --- | --- |
| `ElevatedButton`, `TextButton` | `onPressed` | Action principale ou secondaire |
| `IconButton` | `onPressed` | Bouton icône (favori, supprimer) |
| `Checkbox` | `onChanged` | Case à cocher |
| `Switch` | `onChanged` | Interrupteur on/off |
| `TextField` | `onChanged`, `onSubmitted` | Saisie de texte |
| `InkWell`, `GestureDetector` | `onTap`, `onLongPress` | Rendre n'importe quel widget cliquable |

Voici une liste d'étapes cochables, un cas typique de DevRoad :

```dart
class EtapesPage extends StatefulWidget {
  const EtapesPage({super.key});

  @override
  State<EtapesPage> createState() => _EtapesPageState();
}

class _EtapesPageState extends State<EtapesPage> {
  final Map<String, bool> _etapes = {
    'Installer Flutter': false,
    'Premier widget': false,
    'Mise en page': false,
  };

  int get _nombreTerminees =>
      _etapes.values.where((fait) => fait).length;

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('$_nombreTerminees / ${_etapes.length} terminées'),
      ),
      body: ListView(
        children: _etapes.keys.map((nom) {
          return CheckboxListTile(
            title: Text(nom),
            value: _etapes[nom],
            onChanged: (valeur) {
              setState(() => _etapes[nom] = valeur ?? false);
            },
          );
        }).toList(),
      ),
    );
  }
}
```

Observe trois choses. `onChanged` reçoit un `bool?`, d'où le `?? false`. Le nombre d'étapes terminées est **calculé** (`get`) plutôt que stocké : une seule source de vérité, pas de risque d'incohérence. Et la barre du titre se met à jour toute seule, car `build` est rappelée.

> **À retenir** : ne stocke que les données de base ; **déduis** le reste par calcul. Un compteur séparé de la liste finit toujours par se désynchroniser.

## Les champs de saisie avec TextEditingController

Pour lire et contrôler un `TextField`, on utilise un `TextEditingController`. Il doit être créé une fois et **libéré** à la fin.

```dart
class AjoutMissionPage extends StatefulWidget {
  const AjoutMissionPage({super.key});

  @override
  State<AjoutMissionPage> createState() => _AjoutMissionPageState();
}

class _AjoutMissionPageState extends State<AjoutMissionPage> {
  final _controller = TextEditingController();
  final List<String> _missions = [];

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  void _ajouter() {
    final texte = _controller.text.trim();
    if (texte.isEmpty) return;
    setState(() {
      _missions.add(texte);
    });
    _controller.clear();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Missions')),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _controller,
                    decoration: const InputDecoration(
                      labelText: 'Nouvelle mission',
                      border: OutlineInputBorder(),
                    ),
                    onSubmitted: (_) => _ajouter(),
                  ),
                ),
                const SizedBox(width: 8),
                IconButton.filled(
                  onPressed: _ajouter,
                  icon: const Icon(Icons.add),
                ),
              ],
            ),
          ),
          Expanded(
            child: ListView.builder(
              itemCount: _missions.length,
              itemBuilder: (context, i) => ListTile(
                title: Text(_missions[i]),
                trailing: IconButton(
                  icon: const Icon(Icons.delete_outline),
                  onPressed: () => setState(() => _missions.removeAt(i)),
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }
}
```

La méthode `dispose` est appelée quand le widget disparaît. Libérer le contrôleur évite une fuite de mémoire.

## Le cycle de vie d'un State

La classe `State` possède quelques méthodes appelées à des moments précis :

- `initState()` : appelée **une fois** à la création. C'est là qu'on initialise ce qui dépend du widget (un contrôleur, un abonnement).
- `build()` : appelée à chaque changement d'état. Elle doit être rapide et sans effet de bord.
- `didUpdateWidget()` : appelée quand le parent fournit de nouveaux paramètres.
- `dispose()` : appelée **une fois** à la destruction. On y libère contrôleurs, minuteries et flux.

```dart
class _ChronoState extends State<Chrono> {
  Timer? _timer;
  int _secondes = 0;

  @override
  void initState() {
    super.initState();
    _timer = Timer.periodic(const Duration(seconds: 1), (_) {
      setState(() => _secondes++);
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) => Text('$_secondes s');
}
```

Ce chrono (qui demande `import 'dart:async';`) illustre la règle : ce que tu ouvres dans `initState`, tu le fermes dans `dispose`.

:::quiz
Où faut-il annuler un `Timer` démarré dans `initState` ?
- [ ] Dans `build`, à chaque affichage
- [ ] Dans `createState`
- [x] Dans `dispose`
- [ ] Nulle part, Flutter s'en charge
> `dispose` est appelée quand le widget est retiré de l'arbre. C'est le bon endroit pour libérer les ressources ouvertes dans `initState`.
:::

## Remonter l'état

Que faire quand deux widgets doivent partager la même donnée ? Par exemple, une liste d'étapes et un en-tête qui affiche la progression. La solution classique : placer l'état dans leur **parent commun**, qui passe la donnée en paramètre et reçoit les changements via des callbacks.

```dart
class ProgressionPage extends StatefulWidget {
  const ProgressionPage({super.key});

  @override
  State<ProgressionPage> createState() => _ProgressionPageState();
}

class _ProgressionPageState extends State<ProgressionPage> {
  final List<bool> _faites = [false, false, false, false];

  void _basculer(int index, bool valeur) {
    setState(() => _faites[index] = valeur);
  }

  @override
  Widget build(BuildContext context) {
    final fait = _faites.where((f) => f).length;
    return Column(
      children: [
        EnteteProgression(valeur: fait / _faites.length),
        for (var i = 0; i < _faites.length; i++)
          LigneEtape(
            titre: 'Étape ${i + 1}',
            terminee: _faites[i],
            onChanged: (v) => _basculer(i, v),
          ),
      ],
    );
  }
}

class LigneEtape extends StatelessWidget {
  const LigneEtape({
    super.key,
    required this.titre,
    required this.terminee,
    required this.onChanged,
  });

  final String titre;
  final bool terminee;
  final ValueChanged<bool> onChanged;

  @override
  Widget build(BuildContext context) {
    return CheckboxListTile(
      title: Text(titre),
      value: terminee,
      onChanged: (v) => onChanged(v ?? false),
    );
  }
}
```

Les données descendent par les **paramètres**, les événements remontent par les **callbacks**. `LigneEtape` reste un `StatelessWidget` simple, facile à réutiliser et à tester. Quand l'état devient partagé par de nombreux écrans, cette technique devient lourde : c'est pourquoi le chapitre 5 introduira Riverpod.

:::quiz
Deux widgets frères doivent lire et modifier la même donnée. Où placer cette donnée ?
- [ ] Dans une variable globale à chaque widget
- [ ] Dans le premier widget, et copier la valeur dans l'autre
- [x] Dans leur parent commun, avec des paramètres et des callbacks
- [ ] Dans `main()`
> En remontant l'état dans le parent commun, on garde une seule source de vérité : les données descendent en paramètres, les changements remontent par callbacks.
:::

## Atelier guidé : suivi de progression

Prévois environ une heure et demie.

1. Crée `lib/pages/progression_page.dart` avec un `StatefulWidget` nommé `ProgressionPage`.
2. Déclare une liste de cinq étapes sous forme de `List<String>` et une liste de `bool` de même taille pour leur état.
3. Affiche chaque étape avec un `CheckboxListTile` et un `onChanged` qui appelle `setState`.
4. Ajoute en haut un `LinearProgressIndicator` dont la valeur est calculée à partir du nombre d'étapes cochées.
5. Extrais le widget `LigneEtape` en `StatelessWidget` avec `titre`, `terminee` et `onChanged`.
6. Ajoute un `TextField` avec un `TextEditingController` pour créer une nouvelle étape, et libère le contrôleur dans `dispose`.
7. Ajoute un bouton « Tout réinitialiser » qui remet toutes les cases à `false`.
8. Affiche un message « Bravo, parcours terminé ! » quand toutes les étapes sont cochées.

Pour t'auto-évaluer : explique à voix haute pourquoi la progression est calculée plutôt que stockée, et ce qui se passerait si tu oubliais `setState` dans l'étape 3.

## Erreurs fréquentes

- **Oublier `setState`.** La donnée change, l'écran reste figé.
- **Modifier l'état dans `build`.** Cela peut créer une boucle infinie de reconstructions.
- **Oublier `dispose`.** Les contrôleurs et minuteries restent actifs et consomment de la mémoire.
- **Appeler `setState` après `dispose`.** Après un délai asynchrone, vérifie `if (!mounted) return;` avant de modifier l'état.
- **Stocker une donnée dérivée.** Le « nombre de terminées » doit être calculé, pas copié.
- **Mettre tout l'état dans un seul gros widget.** Remonte l'état seulement aussi haut que nécessaire.

## Bonnes pratiques

- Garde l'état aussi **local** que possible, et remonte-le seulement si plusieurs widgets en ont besoin.
- Calcule les valeurs dérivées avec des `get` plutôt que de les dupliquer.
- Rends privés (`_`) les champs et méthodes internes du `State`.
- Garde `build` sans effet de bord : pas de requête réseau, pas de modification d'état.
- Libère toujours contrôleurs, minuteries et abonnements dans `dispose`.
- Préfère les widgets `StatelessWidget` pour les composants d'affichage et réserve `StatefulWidget` aux écrans qui gèrent un état.

## À retenir

- Une variable normale ne redessine pas l'écran ; `setState` si.
- Un `StatefulWidget` sépare le widget immuable de l'objet `State` qui garde les données.
- Les widgets interactifs exposent des callbacks (`onPressed`, `onChanged`, `onTap`).
- `initState` initialise, `dispose` libère : ce qu'on ouvre, on le ferme.
- On remonte l'état dans le parent commun : données par paramètres, événements par callbacks.
- Calcule les valeurs dérivées au lieu de les stocker.
