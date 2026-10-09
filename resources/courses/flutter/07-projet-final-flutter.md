---
title: Projet final Flutter
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru les fondations de Flutter : widgets, état, navigation, formulaires, API, Riverpod, thème, animations et tests. Il est temps de tout assembler dans un vrai produit. Dans ce projet guidé, tu construis **MissionTrack**, une application mobile de suivi de missions de proximité à Abidjan : un client publie une petite mission (livraison, courses, réparation), un prestataire l'accepte, la fait avancer de statut en statut, puis elle est payée par Mobile Money.

À la fin du projet, tu auras :

- conçu une architecture en couches (interface, état, données) maintenable ;
- implémenté une navigation complète avec GoRouter, onglets et redirection d'authentification ;
- créé un formulaire de mission avec validation ;
- branché l'application sur des données distantes via un dépôt et Riverpod, avec un mode de secours hors connexion ;
- modélisé le cycle de vie d'une mission sous forme de machine à états ;
- appliqué un thème Material 3 clair et sombre, avec quelques animations utiles ;
- écrit des tests unitaires et de widgets sur la logique critique.

Prérequis : les chapitres 1 à 6 de ce parcours. Prévois six heures, que tu peux répartir sur plusieurs séances. Il te faut Flutter 3.x, un émulateur ou un téléphone, et un éditeur de code. Tu n'as pas besoin d'un vrai serveur : tu commenceras avec un faux dépôt en mémoire, puis tu pourras le remplacer par une API réelle (Supabase, Firebase ou Laravel).

> **Exemple** : ce projet est volontairement proche de ce qu'un client te demanderait réellement. Soigne-le : une fois terminé, il devient une pièce de portfolio qui montre que tu sais livrer une application complète.

## Cahier des charges

### Contexte et utilisateurs

À Abidjan, beaucoup de petits services se négocient par WhatsApp et se paient en espèces, sans suivi. MissionTrack structure cet échange. Deux profils utilisent l'application :

- le **client**, qui publie des missions et suit leur avancement ;
- le **prestataire**, qui consulte les missions disponibles près de lui, en accepte une et met à jour son statut.

Pour limiter le périmètre, l'application que tu construis propose un seul compte avec un sélecteur de rôle pendant la connexion. L'authentification réelle viendra plus tard.

### Fonctionnalités attendues

| Code | Fonctionnalité | Priorité |
| --- | --- | --- |
| F1 | Connexion simulée avec choix du rôle (client ou prestataire) | Indispensable |
| F2 | Liste des missions avec filtre par statut et par quartier | Indispensable |
| F3 | Détail d'une mission avec chronologie des statuts | Indispensable |
| F4 | Création d'une mission (titre, description, quartier, budget en FCFA) | Indispensable |
| F5 | Changement de statut selon des règles précises | Indispensable |
| F6 | Choix d'un moyen de paiement Mobile Money (Wave, Orange Money, MTN MoMo) | Important |
| F7 | Mode sombre et choix du thème | Important |
| F8 | Gestion du hors connexion avec message et bouton « Réessayer » | Important |
| F9 | Animation de transition liste vers détail | Souhaitable |

### Cycle de vie d'une mission

Une mission traverse des statuts dans un ordre strict. C'est le cœur métier de l'application :

```text
publiee  --->  acceptee  --->  en_cours  --->  terminee  --->  payee
   |              |
   +---> annulee <+
```

Règles : une mission peut être annulée tant qu'elle n'est pas en cours ; seul le prestataire passe de `acceptee` à `en_cours` puis à `terminee` ; seul le client déclenche le paiement, uniquement sur une mission terminée ; une mission payée ou annulée ne bouge plus.

:::quiz
Pourquoi modéliser le cycle de vie d'une mission avec une règle centralisée plutôt que de tester les statuts dans chaque écran ?
- [ ] Parce que Flutter l'impose
- [x] Pour avoir une seule source de vérité : les règles sont écrites, testées et modifiées à un seul endroit
- [ ] Parce que cela rend l'application plus colorée
- [ ] Pour éviter d'utiliser Riverpod
> Une logique métier dispersée dans les écrans produit des incohérences. Centralisée dans le modèle, elle devient testable sans interface.
:::

### Contraintes non fonctionnelles

- Compatible avec des téléphones Android d'entrée de gamme : listes avec `ListView.builder`, peu d'images lourdes.
- Interface en français, montants affichés au format `12 500 FCFA`.
- Aucun secret ou clé d'API dans le code source.
- Au moins douze tests automatisés qui passent.

## Architecture du projet

Organise le code par fonctionnalité plutôt que par type de fichier. Chaque dossier contient ses propres écrans, états et données :

```text
lib/
├── main.dart
├── app.dart
├── core/
│   ├── router.dart
│   ├── theme.dart
│   └── format.dart
├── features/
│   ├── auth/
│   │   ├── auth_provider.dart
│   │   └── connexion_page.dart
│   └── missions/
│       ├── domain/
│       │   ├── mission.dart
│       │   └── statut_mission.dart
│       ├── data/
│       │   └── depot_missions.dart
│       ├── state/
│       │   └── missions_provider.dart
│       └── ui/
│           ├── liste_missions_page.dart
│           ├── detail_mission_page.dart
│           ├── nouvelle_mission_page.dart
│           └── widgets/
│               ├── carte_mission.dart
│               └── chronologie_statuts.dart
test/
├── statut_mission_test.dart
├── format_test.dart
└── liste_missions_page_test.dart
```

Les flèches de dépendance vont toujours dans le même sens : l'**interface** dépend de l'**état**, qui dépend des **données**, qui dépendent du **domaine** (les modèles et les règles). Le domaine, lui, ne connaît ni Flutter ni Riverpod, ce qui le rend très simple à tester.

## Étape 1 : le domaine

Commence par le plus important : les règles métier. Un `enum` Dart 3 peut porter des méthodes et des propriétés, ce qui convient parfaitement ici :

```dart
enum StatutMission {
  publiee('Publiée'),
  acceptee('Acceptée'),
  enCours('En cours'),
  terminee('Terminée'),
  payee('Payée'),
  annulee('Annulée');

  const StatutMission(this.libelle);
  final String libelle;

  bool get estFinal => this == payee || this == annulee;

  /// Statuts accessibles depuis le statut courant.
  List<StatutMission> get suivants => switch (this) {
        publiee => [acceptee, annulee],
        acceptee => [enCours, annulee],
        enCours => [terminee],
        terminee => [payee],
        payee || annulee => const [],
      };

  bool peutPasserA(StatutMission cible) => suivants.contains(cible);
}
```

Le `switch` expression de Dart 3 est exhaustif : si tu ajoutes un statut, le compilateur te signale tout endroit où tu l'as oublié. Écris maintenant le modèle de mission, immuable, avec une méthode qui applique une transition ou lève une erreur claire :

```dart
class Mission {
  const Mission({
    required this.id,
    required this.titre,
    required this.description,
    required this.quartier,
    required this.budget,
    required this.statut,
    required this.creeeLe,
    this.moyenPaiement,
  });

  final String id;
  final String titre;
  final String description;
  final String quartier;
  final int budget; // en FCFA
  final StatutMission statut;
  final DateTime creeeLe;
  final String? moyenPaiement;

  Mission changerStatut(StatutMission cible) {
    if (!statut.peutPasserA(cible)) {
      throw StateError('Transition interdite : ${statut.libelle} vers ${cible.libelle}');
    }
    return copyWith(statut: cible);
  }

  Mission copyWith({StatutMission? statut, String? moyenPaiement}) {
    return Mission(
      id: id,
      titre: titre,
      description: description,
      quartier: quartier,
      budget: budget,
      statut: statut ?? this.statut,
      creeeLe: creeeLe,
      moyenPaiement: moyenPaiement ?? this.moyenPaiement,
    );
  }
}
```

Ajoute aussi `fromJson` et `toJson` comme au chapitre 5, avec `StatutMission.values.byName(json['statut'])` pour relire le statut. Dans `core/format.dart`, écris une fonction qui affiche les montants :

```dart
String formaterFcfa(int montant) {
  final texte = montant.toString();
  final buffer = StringBuffer();
  for (var i = 0; i < texte.length; i++) {
    final restant = texte.length - i;
    buffer.write(texte[i]);
    if (restant > 1 && restant % 3 == 1) buffer.write(' ');
  }
  return '$buffer FCFA';
}
```

## Étape 2 : les données avec un dépôt

Définis d'abord un **contrat** sous forme de classe abstraite. L'application dépendra de ce contrat, pas d'une implémentation :

```dart
abstract class DepotMissions {
  Future<List<Mission>> lister();
  Future<Mission> creer(Mission mission);
  Future<Mission> mettreAJour(Mission mission);
}
```

Fournis ensuite une implémentation en mémoire, avec un délai pour simuler le réseau et une panne volontaire pour tester le cas d'erreur :

```dart
class DepotMissionsMemoire implements DepotMissions {
  DepotMissionsMemoire({this.panne = false});

  final bool panne;
  final List<Mission> _donnees = [
    Mission(
      id: '1',
      titre: 'Livrer un colis à Cocody',
      description: 'Colis de 2 kg à remettre avant 17 h.',
      quartier: 'Cocody',
      budget: 2500,
      statut: StatutMission.publiee,
      creeeLe: DateTime(2026, 10, 8, 9, 30),
    ),
    Mission(
      id: '2',
      titre: 'Réparer une prise électrique',
      description: 'Remplacement d\'une prise murale.',
      quartier: 'Yopougon',
      budget: 8000,
      statut: StatutMission.acceptee,
      creeeLe: DateTime(2026, 10, 7, 14, 0),
    ),
  ];

  Future<void> _attendre() async {
    await Future.delayed(const Duration(milliseconds: 600));
    if (panne) throw Exception('Réseau indisponible');
  }

  @override
  Future<List<Mission>> lister() async {
    await _attendre();
    return List.unmodifiable(_donnees);
  }

  @override
  Future<Mission> creer(Mission mission) async {
    await _attendre();
    _donnees.insert(0, mission);
    return mission;
  }

  @override
  Future<Mission> mettreAJour(Mission mission) async {
    await _attendre();
    final index = _donnees.indexWhere((m) => m.id == mission.id);
    if (index == -1) throw Exception('Mission introuvable');
    _donnees[index] = mission;
    return mission;
  }
}
```

Quand tu voudras une vraie API, tu écriras `DepotMissionsHttp implements DepotMissions` sans toucher à une seule ligne d'interface.

## Étape 3 : l'état avec Riverpod

```dart
final depotMissionsProvider = Provider<DepotMissions>((ref) {
  return DepotMissionsMemoire();
});

class MissionsNotifier extends AsyncNotifier<List<Mission>> {
  @override
  Future<List<Mission>> build() => ref.watch(depotMissionsProvider).lister();

  Future<void> ajouter(Mission mission) async {
    final creee = await ref.read(depotMissionsProvider).creer(mission);
    state = AsyncData([creee, ...state.requireValue]);
  }

  Future<void> avancer(String id, StatutMission cible) async {
    final actuelles = state.requireValue;
    final mission = actuelles.firstWhere((m) => m.id == id);
    final modifiee = mission.changerStatut(cible);

    final enregistree = await ref.read(depotMissionsProvider).mettreAJour(modifiee);
    state = AsyncData([
      for (final m in actuelles) m.id == id ? enregistree : m,
    ]);
  }
}

final missionsProvider =
    AsyncNotifierProvider<MissionsNotifier, List<Mission>>(MissionsNotifier.new);

final filtreStatutProvider = NotifierProvider<FiltreStatut, StatutMission?>(FiltreStatut.new);

class FiltreStatut extends Notifier<StatutMission?> {
  @override
  StatutMission? build() => null;
  void choisir(StatutMission? statut) => state = statut;
}

final missionsFiltreesProvider = Provider<AsyncValue<List<Mission>>>((ref) {
  final filtre = ref.watch(filtreStatutProvider);
  return ref.watch(missionsProvider).whenData(
        (liste) => filtre == null
            ? liste
            : liste.where((m) => m.statut == filtre).toList(),
      );
});

final missionParIdProvider = Provider.family<Mission?, String>((ref, id) {
  final missions = ref.watch(missionsProvider).valueOrNull ?? const [];
  return missions.where((m) => m.id == id).firstOrNull;
});
```

Deux nouveautés. `Provider.family` crée un provider paramétré : un par identifiant de mission. Et `valueOrNull` retourne la valeur si elle est disponible, sinon `null`, ce qui évite d'écrire un `when` complet pour une simple recherche.

> **Attention** : `state.requireValue` lève une exception si l'état n'est pas encore chargé. N'appelle `ajouter` et `avancer` que depuis des écrans qui ont déjà affiché la liste, ou protège-toi avec `state.valueOrNull ?? []`.

## Étape 4 : l'authentification simulée et le routeur

Un `Notifier` suffit pour la session :

```dart
enum Role { client, prestataire }

class Session {
  const Session({required this.nom, required this.role});
  final String nom;
  final Role role;
}

class AuthNotifier extends Notifier<Session?> {
  @override
  Session? build() => null;

  void connecter(String nom, Role role) => state = Session(nom: nom, role: role);
  void deconnecter() => state = null;
}

final authProvider = NotifierProvider<AuthNotifier, Session?>(AuthNotifier.new);
```

Le routeur lit cet état pour rediriger automatiquement. Le `ChangeNotifier` ci-dessous fait le pont entre Riverpod et le paramètre `refreshListenable` de GoRouter :

```dart
final routerProvider = Provider<GoRouter>((ref) {
  final signal = ValueNotifier<int>(0);
  ref.listen(authProvider, (_, __) => signal.value++);
  ref.onDispose(signal.dispose);

  return GoRouter(
    initialLocation: '/missions',
    refreshListenable: signal,
    redirect: (context, state) {
      final connecte = ref.read(authProvider) != null;
      final surConnexion = state.matchedLocation == '/connexion';
      if (!connecte) return surConnexion ? null : '/connexion';
      if (surConnexion) return '/missions';
      return null;
    },
    routes: [
      GoRoute(path: '/connexion', builder: (c, s) => const ConnexionPage()),
      GoRoute(
        path: '/missions/nouvelle',
        builder: (c, s) => const NouvelleMissionPage(),
      ),
      StatefulShellRoute.indexedStack(
        builder: (c, s, shell) => CoqueNavigation(shell: shell),
        branches: [
          StatefulShellBranch(routes: [
            GoRoute(
              path: '/missions',
              builder: (c, s) => const ListeMissionsPage(),
              routes: [
                GoRoute(
                  path: ':id',
                  builder: (c, s) =>
                      DetailMissionPage(missionId: s.pathParameters['id']!),
                ),
              ],
            ),
          ]),
          StatefulShellBranch(routes: [
            GoRoute(path: '/profil', builder: (c, s) => const ProfilPage()),
          ]),
        ],
      ),
    ],
  );
});
```

Dans `app.dart`, lis ce provider avec `ref.watch(routerProvider)` pour alimenter `MaterialApp.router`. La route `/missions/nouvelle` est déclarée **en dehors** de la coque et avant `:id`, de façon à s'ouvrir en plein écran et à ne pas être confondue avec un identifiant.

:::quiz
Dans cette architecture, quelle couche est la mieux placée pour décider si le passage de `terminee` à `payee` est autorisé ?
- [ ] Le bouton « Payer » dans l'interface
- [ ] Le dépôt HTTP
- [x] Le domaine (`StatutMission` et `Mission.changerStatut`)
- [ ] Le thème
> La règle métier appartient au domaine. L'interface ne fait qu'afficher les actions possibles et le dépôt ne fait que stocker : aucun des deux ne doit réécrire la règle.
:::

## Étape 5 : les écrans

### Liste filtrée

Dans la liste, affiche une rangée de `FilterChip` (un par statut plus « Tous »), puis les cartes. Voici l'ossature :

```dart
class ListeMissionsPage extends ConsumerWidget {
  const ListeMissionsPage({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final missions = ref.watch(missionsFiltreesProvider);
    final filtre = ref.watch(filtreStatutProvider);

    return Scaffold(
      appBar: AppBar(title: const Text('Missions')),
      floatingActionButton: FloatingActionButton.extended(
        onPressed: () => context.push('/missions/nouvelle'),
        icon: const Icon(Icons.add),
        label: const Text('Nouvelle mission'),
      ),
      body: Column(
        children: [
          SizedBox(
            height: 56,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: [
                for (final s in [null, ...StatutMission.values])
                  Padding(
                    padding: const EdgeInsets.only(right: 8),
                    child: FilterChip(
                      label: Text(s?.libelle ?? 'Tous'),
                      selected: filtre == s,
                      onSelected: (_) =>
                          ref.read(filtreStatutProvider.notifier).choisir(s),
                    ),
                  ),
              ],
            ),
          ),
          Expanded(
            child: missions.when(
              loading: () => const Center(child: CircularProgressIndicator()),
              error: (e, _) => EtatErreur(
                message: 'Impossible de charger les missions.',
                onRetry: () => ref.invalidate(missionsProvider),
              ),
              data: (liste) => liste.isEmpty
                  ? const Center(child: Text('Aucune mission pour ce filtre.'))
                  : ListView.builder(
                      itemCount: liste.length,
                      itemBuilder: (_, i) => CarteMission(mission: liste[i]),
                    ),
            ),
          ),
        ],
      ),
    );
  }
}
```

À toi d'écrire `CarteMission` (titre, quartier, budget formaté, pastille de statut colorée avec le `ColorScheme`) et `EtatErreur` (message, icône et bouton « Réessayer »). Enveloppe l'avatar de la carte dans un `Hero`.

### Détail et actions

Le détail affiche toutes les informations et la **chronologie** des statuts. Les boutons d'action dépendent du rôle ET du statut :

```dart
List<Widget> actionsPossibles(Mission m, Role role, WidgetRef ref) {
  Widget bouton(String texte, StatutMission cible) => FilledButton(
        onPressed: () =>
            ref.read(missionsProvider.notifier).avancer(m.id, cible),
        child: Text(texte),
      );

  return switch ((role, m.statut)) {
    (Role.prestataire, StatutMission.publiee) => [bouton('Accepter', StatutMission.acceptee)],
    (Role.prestataire, StatutMission.acceptee) => [bouton('Démarrer', StatutMission.enCours)],
    (Role.prestataire, StatutMission.enCours) => [bouton('Marquer terminée', StatutMission.terminee)],
    (Role.client, StatutMission.terminee) => [bouton('Payer', StatutMission.payee)],
    (Role.client, StatutMission.publiee) => [bouton('Annuler', StatutMission.annulee)],
    _ => const [],
  };
}
```

Les *patterns* de Dart 3 sur un enregistrement `(role, statut)` rendent ce tableau de décisions très lisible. Pour le paiement, ouvre un `showModalBottomSheet` avec trois `RadioListTile` (Wave, Orange Money, MTN MoMo) avant d'appeler `avancer`. Aucun vrai paiement n'est exécuté dans ce projet : tu enregistres simplement le moyen choisi.

> **Astuce** : après une action asynchrone, vérifie `if (!context.mounted) return;` avant d'utiliser le `context` pour afficher une `SnackBar` ou fermer la feuille.

### Création d'une mission

Réutilise le formulaire du chapitre 4 : titre (5 caractères minimum), description, quartier (liste déroulante `DropdownButtonFormField` : Cocody, Yopougon, Abobo, Marcory, Plateau) et budget (minimum 500 FCFA). À la validation, crée une `Mission` avec un identifiant unique, le statut `publiee`, appelle `ajouter`, puis `context.pop()`.

## Étape 6 : thème et finitions

Copie `theme.dart` du chapitre 6 et adapte la couleur de marque. Branche `theme`, `darkTheme` et `themeMode` dans `app.dart`. Pour la pastille de statut, centralise les couleurs dans une extension :

```dart
extension StatutCouleurs on StatutMission {
  Color couleur(ColorScheme c) => switch (this) {
        StatutMission.publiee => c.secondary,
        StatutMission.acceptee => c.tertiary,
        StatutMission.enCours => c.primary,
        StatutMission.terminee => Colors.green.shade700,
        StatutMission.payee => c.outline,
        StatutMission.annulee => c.error,
      };
}
```

Ajoute un `AnimatedSwitcher` sur la pastille du détail pour que le changement de statut se voie, et un `AnimatedSize` sur la chronologie.

## Étape 7 : les tests

Vise au moins douze tests. Voici un exemple pour chaque catégorie :

```dart
void main() {
  group('StatutMission', () {
    test('une mission publiée peut être acceptée ou annulée', () {
      expect(StatutMission.publiee.suivants,
          containsAll([StatutMission.acceptee, StatutMission.annulee]));
    });

    test('une mission payée est finale', () {
      expect(StatutMission.payee.estFinal, isTrue);
      expect(StatutMission.payee.suivants, isEmpty);
    });
  });

  group('Mission.changerStatut', () {
    final base = Mission(
      id: 'x', titre: 'T', description: 'D', quartier: 'Cocody',
      budget: 1000, statut: StatutMission.publiee, creeeLe: DateTime(2026),
    );

    test('refuse de passer directement de publiée à payée', () {
      expect(() => base.changerStatut(StatutMission.payee), throwsStateError);
    });
  });

  group('formaterFcfa', () {
    test('sépare les milliers', () {
      expect(formaterFcfa(12500), '12 500 FCFA');
    });
  });
}
```

Pour un test de widget, surcharge `depotMissionsProvider` avec un dépôt en mémoire, affiche la liste, appuie sur un `FilterChip` et vérifie que le nombre de cartes diminue. Teste aussi le cas `DepotMissionsMemoire(panne: true)` : le bouton « Réessayer » doit apparaître.

## Atelier guidé : le plan de route sur six heures

Voici un enchaînement réaliste. Fais un commit à la fin de chaque étape.

1. **Initialisation (20 min)** : `flutter create missiontrack`, ajoute `go_router` et `flutter_riverpod`, crée l'arborescence des dossiers, vérifie que l'application se lance.
2. **Domaine (45 min)** : `StatutMission`, `Mission`, `formaterFcfa`, puis les tests correspondants avant d'aller plus loin.
3. **Données (30 min)** : le contrat `DepotMissions` et l'implémentation en mémoire.
4. **État (45 min)** : les providers de missions, du filtre et de la session.
5. **Navigation (40 min)** : `routerProvider`, redirection, coque à onglets, écran de connexion avec choix du rôle.
6. **Écrans (90 min)** : liste filtrée, carte, détail avec actions selon le rôle, formulaire de création, feuille de paiement.
7. **Thème et animations (30 min)** : thème clair et sombre, `Hero`, `AnimatedSwitcher`.
8. **Tests et corrections (50 min)** : complète jusqu'à douze tests, lance `flutter test` et `flutter analyze`, corrige les avertissements.
9. **Livraison (10 min)** : écris un `README.md` avec une capture, la liste des fonctionnalités et les commandes pour lancer le projet.

Pour t'auto-évaluer : présente ton application à voix haute comme à un client. Peux-tu montrer chaque statut, expliquer pourquoi un client ne peut pas démarrer une mission, et prouver par un test qu'aucune transition interdite n'est possible ?

## Checklist d'acceptation

Ton projet est terminé quand tu peux cocher chacun de ces points :

- La connexion simulée fonctionne avec les deux rôles ; la déconnexion ramène à l'écran de connexion et un lien direct vers `/missions` redirige quand on n'est pas connecté.
- La liste affiche chargement, erreur avec bouton « Réessayer », vide et données.
- Le filtre par statut met à jour la liste immédiatement.
- Le détail montre toutes les informations, la chronologie, et seulement les actions autorisées pour le rôle et le statut.
- Une transition interdite est impossible depuis l'interface et lève une erreur dans le domaine.
- Le formulaire valide chaque champ et affiche des messages clairs ; une mission créée apparaît en tête de liste.
- Les montants s'affichent au format `12 500 FCFA` partout.
- Le choix du moyen de paiement (Wave, Orange Money, MTN MoMo) est enregistré avant le passage à `payee`.
- Les modes clair et sombre sont lisibles, sans couleur codée en dur.
- Au moins une animation `Hero` ou `AnimatedSwitcher` est présente et sobre.
- `flutter analyze` ne signale aucune erreur et `flutter test` passe avec au moins douze tests.
- Aucun contrôleur ou animation n'est oublié dans `dispose`, et aucun secret n'est présent dans le code.
- Le `README.md` explique comment lancer le projet.

## Pour aller plus loin

Une fois la checklist validée, plusieurs extensions te feront passer au niveau professionnel :

- remplacer le dépôt en mémoire par une API Supabase ou Laravel, avec un `DepotMissionsHttp` et la même interface ;
- mettre en cache la dernière liste avec `shared_preferences` ou une base locale pour un vrai mode hors connexion ;
- ajouter les notifications push à chaque changement de statut avec Firebase Cloud Messaging ;
- intégrer un vrai paiement via un agrégateur comme CinetPay, côté serveur uniquement ;
- ajouter un test d'intégration qui parcourt tout le cycle de vie d'une mission ;
- publier une version de test sur Google Play avec une intégration continue.

## Erreurs fréquentes

- **Mettre les règles métier dans les widgets.** Elles se dupliquent et divergent ; garde-les dans le domaine.
- **Laisser un provider dépendre d'un écran.** Les providers ne doivent jamais importer de widgets.
- **Utiliser `requireValue` sans que les données soient chargées.** Cela lève une exception au premier affichage.
- **Mal ordonner les routes.** `/missions/nouvelle` après `/missions/:id` s'ouvre comme un détail.
- **Oublier `context.mounted`** après un `await`, ce qui provoque des erreurs au changement d'écran.
- **Stocker une liste mutable dans l'état.** Crée toujours une nouvelle liste pour que Riverpod détecte le changement.
- **Écrire les tests à la fin.** Les règles métier se testent dès l'étape 2, quand elles sont encore fraîches.
- **Viser trop large.** Termine d'abord les fonctionnalités indispensables avant les souhaitables.

## Bonnes pratiques

- Organise le code par fonctionnalité et respecte le sens des dépendances.
- Programme contre des interfaces (`DepotMissions`) pour pouvoir changer de back-end sans douleur.
- Rends le domaine pur Dart : aucun import de Flutter, donc testable en une seconde.
- Fais des commits petits et fréquents, avec des messages qui décrivent l'intention.
- Lance `flutter analyze` et `flutter test` avant chaque commit.
- Utilise `const` partout où c'est possible pour de meilleures performances.
- Teste sur un vrai téléphone modeste, pas seulement sur un émulateur puissant.
- Documente : un projet bien présenté compte autant que son code dans un portfolio.

## À retenir

- Un projet réel se construit par couches : domaine, données, état, interface, avec des dépendances à sens unique.
- Un `enum` Dart 3 avec méthodes et un `switch` exhaustif exprime proprement un cycle de vie métier.
- Un dépôt abstrait permet de commencer avec des données en mémoire et de passer à une vraie API sans réécrire l'interface.
- Riverpod relie tout : `AsyncNotifier` pour les données, `Notifier` pour la session et les filtres, `Provider.family` pour une entité par identifiant.
- GoRouter redirige selon la session et organise les onglets avec `StatefulShellRoute`.
- Les tests de la logique métier sont les plus rentables ; les surcharges de provider isolent les tests de widgets.
- Une checklist d'acceptation transforme « ça marche chez moi » en livraison vérifiable.
