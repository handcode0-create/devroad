---
title: Données, API et Riverpod
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, tes écrans affichaient des données codées en dur. Une vraie application récupère ses informations auprès d'un serveur : liste de missions, solde Mobile Money, profil. Cela pose trois questions. Comment appeler une API ? Comment transformer du JSON en objets Dart ? Et surtout, comment partager ces données entre plusieurs écrans sans que `setState` ne devienne ingérable ? C'est le rôle de **Riverpod**, la bibliothèque de gestion d'état que nous utiliserons.

À la fin du chapitre, tu seras capable de :

- appeler une API REST avec le package `http` et lire la réponse ;
- modéliser des données avec une classe Dart immuable, `fromJson` et `toJson` ;
- gérer proprement les états de chargement, d'erreur et de succès avec `FutureBuilder` ;
- installer Riverpod et déclarer des **providers** ;
- utiliser `Notifier` pour un état modifiable et `AsyncNotifier` pour un état venant du réseau ;
- lire un provider avec `ref.watch` et `ref.read` dans un `ConsumerWidget` ;
- structurer ton code avec un **dépôt** (*repository*) qui isole l'accès aux données.

Prérequis : les chapitres 1 à 4, en particulier `StatefulWidget`, listes et GoRouter. Prévois trois heures. Connaître les bases du JSON et des verbes HTTP (GET, POST) aide, mais tout est rappelé ici.

## Appeler une API avec le package http

Ajoute le package :

```bash
flutter pub add http
```

Sur Android, autorise l'accès à Internet dans `android/app/src/main/AndroidManifest.xml` si ce n'est pas déjà le cas (la ligne `uses-permission` avec `android.permission.INTERNET`). Sur un émulateur Android, `localhost` de ton ordinateur est accessible via `10.0.2.2`.

Une requête GET se fait en quelques lignes :

```dart
import 'dart:convert';
import 'package:http/http.dart' as http;

Future<List<dynamic>> chargerMissionsBrutes() async {
  final reponse = await http.get(
    Uri.parse('https://api.exemple.ci/missions'),
    headers: {'Accept': 'application/json'},
  );

  if (reponse.statusCode != 200) {
    throw Exception('Erreur serveur : ${reponse.statusCode}');
  }
  return jsonDecode(reponse.body) as List<dynamic>;
}
```

Trois idées à retenir. Premièrement, l'appel est **asynchrone** : il retourne un `Future`, une promesse de résultat, et on l'attend avec `await` dans une fonction marquée `async`. Deuxièmement, le code de statut HTTP doit être vérifié : un 404 ou un 500 ne lève pas d'exception tout seul. Troisièmement, `jsonDecode` transforme le texte JSON en `List` et `Map` génériques, ce qui est pratique mais dangereux : aucune garantie sur les types.

## Modéliser les données

Pour travailler avec des objets typés plutôt qu'avec des `Map<String, dynamic>`, crée une classe modèle. Elle sait se construire depuis du JSON :

```dart
class Mission {
  const Mission({
    required this.id,
    required this.titre,
    required this.budget,
    required this.quartier,
    this.terminee = false,
  });

  final int id;
  final String titre;
  final int budget;
  final String quartier;
  final bool terminee;

  factory Mission.fromJson(Map<String, dynamic> json) {
    return Mission(
      id: json['id'] as int,
      titre: json['titre'] as String,
      budget: json['budget'] as int,
      quartier: json['quartier'] as String? ?? 'Non précisé',
      terminee: json['terminee'] as bool? ?? false,
    );
  }

  Map<String, dynamic> toJson() => {
        'id': id,
        'titre': titre,
        'budget': budget,
        'quartier': quartier,
        'terminee': terminee,
      };

  Mission copyWith({String? titre, int? budget, bool? terminee}) {
    return Mission(
      id: id,
      titre: titre ?? this.titre,
      budget: budget ?? this.budget,
      quartier: quartier,
      terminee: terminee ?? this.terminee,
    );
  }
}
```

La classe est **immuable** : tous ses champs sont `final`. Pour « modifier » une mission, on crée une copie avec `copyWith`. Cette immutabilité est essentielle avec Riverpod, car l'état est considéré comme changé quand l'objet est remplacé par un autre. Les valeurs `?? 'Non précisé'` gèrent un champ absent ou `null` dans le JSON, ce qui évite de planter sur une donnée incomplète.

> **Astuce** : quand tes modèles se multiplient, les packages `freezed` et `json_serializable` génèrent `fromJson`, `copyWith` et l'égalité pour toi. Apprends d'abord à les écrire à la main pour comprendre ce que le générateur produit.

:::quiz
Pourquoi rend-on les modèles immuables (champs `final`) avec Riverpod ?
- [ ] Pour que le code compile plus vite
- [x] Pour détecter un changement d'état : on remplace l'objet par une nouvelle copie au lieu de le modifier
- [ ] Parce que Dart interdit les champs modifiables
- [ ] Pour économiser de la mémoire sur le téléphone
> Riverpod notifie les widgets quand la valeur de l'état change. Remplacer l'objet par une copie rend ce changement détectable et évite les modifications cachées.
:::

## Afficher une réponse asynchrone avec FutureBuilder

Le widget `FutureBuilder` reconstruit l'interface selon l'avancement d'un `Future` :

```dart
FutureBuilder<List<Mission>>(
  future: depot.listerMissions(),
  builder: (context, snapshot) {
    if (snapshot.connectionState == ConnectionState.waiting) {
      return const Center(child: CircularProgressIndicator());
    }
    if (snapshot.hasError) {
      return Center(child: Text('Oups : ${snapshot.error}'));
    }
    final missions = snapshot.data!;
    return ListView(
      children: missions.map((m) => ListTile(title: Text(m.titre))).toList(),
    );
  },
)
```

Cela fonctionne, mais attention au piège classique : si `depot.listerMissions()` est appelé directement dans `build`, une **nouvelle requête part à chaque reconstruction** du widget. Pour une vraie application, on préfère confier ce travail à un provider, qui met le résultat en cache et le partage. C'est exactement ce que fait Riverpod.

## Riverpod : le principe

Un **provider** est un objet global qui fabrique et conserve une valeur : une configuration, une liste de missions, l'utilisateur connecté. N'importe quel widget peut le **lire** ou s'**abonner** à ses changements, sans passer de paramètres de widget en widget. Quand la valeur change, seuls les widgets abonnés se reconstruisent.

Installe les packages :

```bash
flutter pub add flutter_riverpod
```

Enveloppe l'application dans un `ProviderScope`, qui stocke l'état de tous les providers :

```dart
void main() {
  runApp(const ProviderScope(child: MonApp()));
}
```

### Provider simple : une valeur en lecture seule

Le premier type, `Provider`, expose une valeur calculée une fois, comme un service :

```dart
import 'package:flutter_riverpod/flutter_riverpod.dart';

final depotMissionsProvider = Provider<DepotMissions>((ref) {
  return DepotMissions(baseUrl: 'https://api.exemple.ci');
});
```

### Notifier : un état modifiable

Quand l'état doit changer (un compteur, un filtre, un panier), on écrit un `Notifier`. C'est une classe avec un état initial dans `build` et des méthodes qui le modifient :

```dart
class FiltreQuartier extends Notifier<String?> {
  @override
  String? build() => null; // aucun filtre au départ

  void choisir(String quartier) => state = quartier;
  void effacer() => state = null;
}

final filtreQuartierProvider =
    NotifierProvider<FiltreQuartier, String?>(FiltreQuartier.new);
```

Affecter `state = ...` notifie automatiquement tous les widgets abonnés. Ne modifie jamais un objet de l'intérieur (`state.add(x)` sur une liste) : crée toujours une nouvelle valeur (`state = [...state, x]`).

### AsyncNotifier : un état qui vient du réseau

Pour une donnée chargée de façon asynchrone, `AsyncNotifier` est fait pour toi. Son `build` retourne un `Future` et Riverpod enveloppe le résultat dans un `AsyncValue`, qui représente à la fois **chargement, erreur et donnée** :

```dart
class MissionsNotifier extends AsyncNotifier<List<Mission>> {
  @override
  Future<List<Mission>> build() {
    return ref.watch(depotMissionsProvider).listerMissions();
  }

  Future<void> ajouter(Mission mission) async {
    final depot = ref.read(depotMissionsProvider);
    state = const AsyncLoading();
    state = await AsyncValue.guard(() async {
      await depot.creer(mission);
      return depot.listerMissions();
    });
  }

  Future<void> rafraichir() async {
    ref.invalidateSelf();
    await future;
  }
}

final missionsProvider =
    AsyncNotifierProvider<MissionsNotifier, List<Mission>>(MissionsNotifier.new);
```

`AsyncValue.guard` exécute une opération et capture une éventuelle exception dans un état d'erreur plutôt que de planter l'application. `invalidateSelf` demande au provider de recalculer son `build`, c'est-à-dire de relancer la requête.

:::quiz
Quel type de provider choisis-tu pour une liste de missions chargée depuis une API, que l'on peut aussi rafraîchir ?
- [ ] `Provider`
- [ ] `Notifier` avec un état `List<Mission>`
- [x] `AsyncNotifier`, dont l'état est un `AsyncValue<List<Mission>>`
- [ ] Une variable globale modifiée avec `setState`
> `AsyncNotifier` gère nativement les trois états d'une donnée distante (chargement, erreur, valeur) et offre des méthodes pour la rafraîchir.
:::

## Lire un provider dans un widget

Pour utiliser `ref`, ton widget doit hériter de `ConsumerWidget` (ou `ConsumerStatefulWidget`) au lieu de `StatelessWidget` :

```dart
class ListeMissionsPage extends ConsumerWidget {
  const ListeMissionsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final missionsAsync = ref.watch(missionsProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Missions près de toi')),
      body: missionsAsync.when(
        loading: () => const Center(child: CircularProgressIndicator()),
        error: (erreur, _) => Center(
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              Text('Impossible de charger : $erreur'),
              const SizedBox(height: 12),
              FilledButton(
                onPressed: () => ref.invalidate(missionsProvider),
                child: const Text('Réessayer'),
              ),
            ],
          ),
        ),
        data: (missions) => RefreshIndicator(
          onRefresh: () => ref.read(missionsProvider.notifier).rafraichir(),
          child: ListView.builder(
            itemCount: missions.length,
            itemBuilder: (context, i) => ListTile(
              title: Text(missions[i].titre),
              subtitle: Text('${missions[i].quartier} • ${missions[i].budget} FCFA'),
            ),
          ),
        ),
      ),
    );
  }
}
```

La méthode `when` t'oblige à traiter les trois cas. Impossible d'oublier l'état d'erreur, source d'écrans blancs mystérieux.

### watch ou read ?

C'est la règle la plus importante de Riverpod :

| Méthode | Où l'utiliser | Effet |
| --- | --- | --- |
| `ref.watch(p)` | Dans `build` | S'abonne : le widget se reconstruit quand `p` change |
| `ref.read(p)` | Dans un callback (`onPressed`) | Lit la valeur une fois, sans s'abonner |
| `ref.listen(p, ...)` | Dans `build` | Réagit à un changement (SnackBar, navigation) sans reconstruire |

> **Erreur fréquente** : appeler `ref.read` dans `build` pour afficher une valeur. L'écran ne se mettra alors jamais à jour. Inversement, `ref.watch` dans un `onPressed` est interdit : on y lit avec `ref.read`.

Combiner des providers est très simple. Voici une liste filtrée par quartier, qui se recalcule seule dès que la liste ou le filtre change :

```dart
final missionsFiltreesProvider = Provider<AsyncValue<List<Mission>>>((ref) {
  final filtre = ref.watch(filtreQuartierProvider);
  final missions = ref.watch(missionsProvider);

  return missions.whenData((liste) {
    if (filtre == null) return liste;
    return liste.where((m) => m.quartier == filtre).toList();
  });
});
```

## Le dépôt : isoler l'accès aux données

Dans les exemples précédents, un `DepotMissions` apparaît. C'est une classe dont l'unique rôle est de parler à l'API. Les écrans ne connaissent pas `http`, ils ne voient que des méthodes Dart :

```dart
class DepotMissions {
  DepotMissions({required this.baseUrl, http.Client? client})
      : _client = client ?? http.Client();

  final String baseUrl;
  final http.Client _client;

  Future<List<Mission>> listerMissions() async {
    final reponse = await _client.get(Uri.parse('$baseUrl/missions'));
    if (reponse.statusCode != 200) {
      throw Exception('Chargement impossible (${reponse.statusCode})');
    }
    final liste = jsonDecode(reponse.body) as List<dynamic>;
    return liste
        .map((e) => Mission.fromJson(e as Map<String, dynamic>))
        .toList();
  }

  Future<void> creer(Mission mission) async {
    final reponse = await _client.post(
      Uri.parse('$baseUrl/missions'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode(mission.toJson()),
    );
    if (reponse.statusCode != 201) {
      throw Exception('Création impossible (${reponse.statusCode})');
    }
  }
}
```

Le client HTTP est injectable par le constructeur : dans les tests (chapitre 6), tu le remplaceras par un faux client. Si tu changes un jour d'API pour Supabase ou Firebase, seul le dépôt change, pas tes écrans. C'est l'intérêt d'une architecture en couches : interface, état (providers), données (dépôt).

:::quiz
Dans un `onPressed`, comment lis-tu le notifier pour appeler une méthode ?
- [ ] `ref.watch(missionsProvider)`
- [x] `ref.read(missionsProvider.notifier).ajouter(mission)`
- [ ] `missionsProvider.ajouter(mission)`
- [ ] `ref.listen(missionsProvider, ...)`
> Dans un callback, on utilise `ref.read` ; l'accès à `.notifier` donne la classe contenant les méthodes de modification.
:::

## Atelier guidé : une liste de missions branchée sur une API

Compte une heure et demie. Si tu n'as pas d'API, utilise un faux dépôt qui retourne une liste après un `Future.delayed(const Duration(seconds: 1))`, ou lance un serveur JSON local.

1. Ajoute `http` et `flutter_riverpod`, puis enveloppe l'application dans `ProviderScope`.
2. Écris la classe `Mission` avec `fromJson`, `toJson` et `copyWith`.
3. Crée `DepotMissions` avec `listerMissions` et `creer`.
4. Déclare `depotMissionsProvider` et `missionsProvider` (un `AsyncNotifier`).
5. Transforme `ListeMissionsPage` en `ConsumerWidget` avec `missionsAsync.when`.
6. Teste les trois états : lance l'application, coupe le Wi-Fi pour provoquer l'erreur et vérifie que le bouton « Réessayer » fonctionne.
7. Ajoute un `RefreshIndicator` qui appelle `rafraichir`.
8. Ajoute `filtreQuartierProvider` et des `ChoiceChip` pour filtrer la liste.
9. Branche le formulaire du chapitre 4 sur la méthode `ajouter` et affiche une `SnackBar` avec `ref.listen` en cas d'erreur.

Pour t'auto-évaluer : explique la différence entre `watch` et `read`, puis pourquoi on utilise `copyWith` au lieu de modifier un champ directement. Demande-toi aussi ce qui se passerait si l'écran lançait une requête dans `build`.

## Erreurs fréquentes

- **Oublier `ProviderScope`.** L'application plante dès la première lecture d'un provider.
- **Utiliser `ref.read` dans `build`.** L'interface ne se met plus à jour.
- **Muter l'état.** Faire `state.add(x)` modifie la liste existante sans que Riverpod ne le détecte. Écris `state = [...state, x]`.
- **Lancer une requête dans `build` d'un widget.** Elle repart à chaque reconstruction.
- **Faire confiance au JSON.** Un champ absent ou d'un autre type lève une exception ; prévois des valeurs par défaut et des conversions sûres.
- **Ignorer le code de statut.** Un 500 renvoyé avec du HTML casse `jsonDecode`.
- **Utiliser `localhost` sur émulateur Android.** Utilise `10.0.2.2` ou l'adresse IP de ta machine.

## Bonnes pratiques

- Sépare les couches : écrans, providers, dépôts, modèles.
- Rends tes modèles immuables et fournis `copyWith`.
- Traite toujours les trois états d'une donnée distante : chargement, erreur, succès.
- Donne un bouton « Réessayer » : le réseau mobile est instable, c'est la norme et non l'exception.
- Choisis `ref.watch` dans `build` et `ref.read` dans les callbacks.
- Ne stocke jamais de clés secrètes dans l'application : un fichier APK se décompile facilement.
- Pense aux connexions lentes : affiche un indicateur, évite les réponses trop lourdes, mets en cache.

## À retenir

- Un appel réseau est asynchrone : `Future`, `async` et `await`.
- Vérifie le code de statut puis transforme le JSON en objets avec `fromJson`.
- Un modèle immuable avec `copyWith` rend les changements d'état détectables.
- `ProviderScope` active Riverpod ; `Provider` expose un service, `Notifier` un état modifiable, `AsyncNotifier` un état venu du réseau.
- `AsyncValue.when` force à gérer chargement, erreur et donnée.
- `ref.watch` dans `build`, `ref.read` dans les actions, `ref.listen` pour les effets de bord.
- Un dépôt isole l'accès aux données et facilite les tests et les changements de back-end.
