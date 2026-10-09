---
title: Navigation et formulaires
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application réelle n'est jamais un écran unique. Elle en compte des dizaines : liste de missions, détail d'une mission, profil, paiement, connexion. Il faut pouvoir passer de l'un à l'autre, transmettre des informations, revenir en arrière, et surtout laisser l'utilisateur saisir des données proprement. Ce chapitre couvre les deux piliers : **la navigation** avec GoRouter et **les formulaires** avec validation.

À la fin du chapitre, tu seras capable de :

- utiliser `Navigator.push` et `pop` pour comprendre le fonctionnement de la pile d'écrans ;
- déclarer des routes nommées avec **GoRouter** et naviguer avec `context.go` et `context.push` ;
- passer des paramètres dans l'URL (`/missions/42`) et des objets via `extra` ;
- organiser une navigation à onglets avec une barre inférieure ;
- construire un formulaire avec `Form`, `TextFormField` et `GlobalKey<FormState>` ;
- valider les saisies (champ obligatoire, numéro de téléphone, montant) ;
- gérer le clavier, le focus et les contrôleurs de texte.

Prérequis : les chapitres 1 à 3 (widgets, mise en page, `StatefulWidget` et `setState`). Prévois deux heures et demie. Tu as besoin d'un projet Flutter 3.x qui se lance sur un émulateur ou un téléphone.

## La navigation de base : une pile d'écrans

Dans Flutter, chaque écran est un widget. Le `Navigator` garde une **pile** (*stack*) de ces écrans : `push` en ajoute un par-dessus, `pop` retire celui du dessus et révèle le précédent. C'est comme une pile d'assiettes : on ne touche qu'à celle du dessus.

```dart
import 'package:flutter/material.dart';

class ListeMissionsPage extends StatelessWidget {
  const ListeMissionsPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Missions')),
      body: Center(
        child: FilledButton(
          onPressed: () {
            Navigator.of(context).push(
              MaterialPageRoute(builder: (_) => const DetailMissionPage()),
            );
          },
          child: const Text('Voir le détail'),
        ),
      ),
    );
  }
}

class DetailMissionPage extends StatelessWidget {
  const DetailMissionPage({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Détail')),
      body: Center(
        child: OutlinedButton(
          onPressed: () => Navigator.of(context).pop(),
          child: const Text('Retour'),
        ),
      ),
    );
  }
}
```

Dans le second écran, `AppBar` affiche automatiquement une flèche de retour : Flutter sait qu'il existe un écran en dessous dans la pile.

Cette navigation impérative suffit pour une petite application. Mais elle montre vite ses limites : impossible d'ouvrir directement un écran profond depuis un lien, difficile de gérer le bouton retour du navigateur sur le web, et le code de navigation est dispersé partout. C'est pourquoi on adopte un routeur déclaratif.

:::quiz
Que fait `Navigator.of(context).pop()` ?
- [ ] Il ferme complètement l'application
- [ ] Il ajoute un nouvel écran sur la pile
- [x] Il retire l'écran du dessus de la pile et revient au précédent
- [ ] Il recharge l'écran courant
> `pop` dépile l'écran visible. L'écran qui se trouvait juste en dessous redevient visible.
:::

## GoRouter : une navigation déclarative par URL

**GoRouter** est le package de routage recommandé par l'équipe Flutter. Chaque écran possède une **adresse** (comme `/missions/42`), ce qui fonctionne aussi bien sur mobile que sur le web et permet les liens profonds (*deep links*), par exemple un lien WhatsApp qui ouvre directement une mission.

Ajoute le package :

```bash
flutter pub add go_router
```

Déclare ensuite les routes dans un fichier `router.dart` :

```dart
import 'package:go_router/go_router.dart';

final router = GoRouter(
  initialLocation: '/missions',
  routes: [
    GoRoute(
      path: '/missions',
      name: 'missions',
      builder: (context, state) => const ListeMissionsPage(),
      routes: [
        GoRoute(
          path: ':id',
          name: 'mission-detail',
          builder: (context, state) {
            final id = state.pathParameters['id']!;
            return DetailMissionPage(missionId: id);
          },
        ),
      ],
    ),
    GoRoute(
      path: '/profil',
      name: 'profil',
      builder: (context, state) => const ProfilPage(),
    ),
  ],
);
```

Puis branche-le dans `MaterialApp.router` :

```dart
void main() => runApp(const MonApp());

class MonApp extends StatelessWidget {
  const MonApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp.router(
      title: 'Missions de proximité',
      routerConfig: router,
      theme: ThemeData(colorSchemeSeed: Colors.teal, useMaterial3: true),
    );
  }
}
```

Remarque la route enfant `:id`. Le deux-points indique un **paramètre de chemin** : l'adresse `/missions/42` affichera `DetailMissionPage` avec `missionId` valant `'42'`. Les paramètres sont toujours des chaînes de caractères, tu dois les convertir (`int.parse`) si besoin.

### go ou push ?

GoRouter propose deux manières principales de naviguer :

| Méthode | Comportement | Quand l'utiliser |
| --- | --- | --- |
| `context.go('/profil')` | Remplace la pile par la destination | Changer de section, après une connexion |
| `context.push('/missions/42')` | Empile la destination au-dessus | Ouvrir un détail, puis revenir avec `pop` |
| `context.pop()` | Dépile l'écran courant | Bouton retour, fermeture d'un formulaire |
| `context.goNamed('profil')` | Comme `go`, mais par nom de route | Éviter les chaînes d'adresse en dur |

```dart
ListTile(
  title: Text(mission.titre),
  onTap: () => context.push('/missions/${mission.id}'),
),
```

> **Astuce** : utilise les routes nommées (`goNamed`, `pushNamed`) avec `pathParameters: {'id': '42'}`. Si tu changes un jour la forme de l'adresse, tu ne modifies qu'un seul fichier.

### Passer un objet avec extra

Pour éviter de recharger une donnée déjà en mémoire, tu peux transmettre un objet entier avec `extra` :

```dart
context.push('/missions/${mission.id}', extra: mission);

// dans le routeur
builder: (context, state) {
  final mission = state.extra as Mission?;
  return DetailMissionPage(missionId: state.pathParameters['id']!, mission: mission);
},
```

> **Attention** : `extra` est perdu quand l'utilisateur rafraîchit la page web ou ouvre un lien profond. Ton écran doit donc toujours savoir se débrouiller avec le seul identifiant, par exemple en rechargeant la donnée. Traite `extra` comme un simple raccourci d'optimisation.

:::quiz
Tu es sur la liste des missions et tu veux ouvrir le détail, puis permettre à l'utilisateur de revenir à la liste. Quelle méthode choisis-tu ?
- [ ] `context.go('/missions/42')`, car elle garde toujours la liste en dessous
- [x] `context.push('/missions/42')`, car elle empile le détail au-dessus de la liste
- [ ] `context.pop('/missions/42')`
- [ ] `Navigator.replace`
> `push` ajoute l'écran au-dessus de la pile. `go` remplace la pile par la destination, ce qui convient aux changements de section plutôt qu'à l'ouverture d'un détail.
:::

## Navigation à onglets avec ShellRoute

Beaucoup d'applications ont une barre de navigation en bas : Missions, Messages, Profil. GoRouter propose `StatefulShellRoute.indexedStack`, qui conserve l'état de chaque onglet (position de défilement, formulaire à moitié rempli) quand on passe de l'un à l'autre.

```dart
final router = GoRouter(
  initialLocation: '/missions',
  routes: [
    StatefulShellRoute.indexedStack(
      builder: (context, state, navigationShell) {
        return Scaffold(
          body: navigationShell,
          bottomNavigationBar: NavigationBar(
            selectedIndex: navigationShell.currentIndex,
            onDestinationSelected: (index) => navigationShell.goBranch(
              index,
              initialLocation: index == navigationShell.currentIndex,
            ),
            destinations: const [
              NavigationDestination(icon: Icon(Icons.work_outline), label: 'Missions'),
              NavigationDestination(icon: Icon(Icons.chat_bubble_outline), label: 'Messages'),
              NavigationDestination(icon: Icon(Icons.person_outline), label: 'Profil'),
            ],
          ),
        );
      },
      branches: [
        StatefulShellBranch(routes: [
          GoRoute(path: '/missions', builder: (c, s) => const ListeMissionsPage()),
        ]),
        StatefulShellBranch(routes: [
          GoRoute(path: '/messages', builder: (c, s) => const MessagesPage()),
        ]),
        StatefulShellBranch(routes: [
          GoRoute(path: '/profil', builder: (c, s) => const ProfilPage()),
        ]),
      ],
    ),
  ],
);
```

`NavigationBar` est le composant Material 3 qui remplace l'ancien `BottomNavigationBar`. Le paramètre `initialLocation` fait revenir à la racine de l'onglet quand on appuie une seconde fois sur l'onglet déjà actif : un comportement que les utilisateurs attendent.

## Rediriger : protéger des écrans

GoRouter permet de rediriger selon une condition, typiquement la connexion de l'utilisateur :

```dart
final router = GoRouter(
  redirect: (context, state) {
    final connecte = utilisateurConnecte; // issu de ton état d'authentification
    final surConnexion = state.matchedLocation == '/connexion';

    if (!connecte && !surConnexion) return '/connexion';
    if (connecte && surConnexion) return '/missions';
    return null; // aucune redirection
  },
  routes: [ /* ... */ ],
);
```

Retourner `null` signifie « tout va bien, continue ». Au chapitre 5, tu relieras cette redirection à un état Riverpod pour que le routeur réagisse automatiquement à la connexion et à la déconnexion.

## Les formulaires : Form et TextFormField

Saisir une mission, un numéro Mobile Money, un mot de passe : les formulaires sont partout. Flutter fournit `Form`, un conteneur qui regroupe plusieurs champs et sait tous les valider d'un coup. On l'associe à une `GlobalKey<FormState>` pour déclencher la validation.

```dart
class NouvelleMissionPage extends StatefulWidget {
  const NouvelleMissionPage({super.key});

  @override
  State<NouvelleMissionPage> createState() => _NouvelleMissionPageState();
}

class _NouvelleMissionPageState extends State<NouvelleMissionPage> {
  final _formKey = GlobalKey<FormState>();
  final _titreCtrl = TextEditingController();
  final _budgetCtrl = TextEditingController();

  @override
  void dispose() {
    _titreCtrl.dispose();
    _budgetCtrl.dispose();
    super.dispose();
  }

  void _envoyer() {
    if (_formKey.currentState!.validate()) {
      final budget = int.parse(_budgetCtrl.text);
      // envoyer la mission (chapitre 5)
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text('Mission publiée : ${_titreCtrl.text} ($budget FCFA)')),
      );
      context.pop();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Nouvelle mission')),
      body: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.all(16),
          children: [
            TextFormField(
              controller: _titreCtrl,
              textInputAction: TextInputAction.next,
              decoration: const InputDecoration(
                labelText: 'Titre de la mission',
                border: OutlineInputBorder(),
              ),
              validator: (valeur) {
                if (valeur == null || valeur.trim().isEmpty) {
                  return 'Le titre est obligatoire';
                }
                if (valeur.trim().length < 5) {
                  return 'Au moins 5 caractères';
                }
                return null;
              },
            ),
            const SizedBox(height: 16),
            TextFormField(
              controller: _budgetCtrl,
              keyboardType: TextInputType.number,
              decoration: const InputDecoration(
                labelText: 'Budget',
                suffixText: 'FCFA',
                border: OutlineInputBorder(),
              ),
              validator: (valeur) {
                final montant = int.tryParse(valeur ?? '');
                if (montant == null) return 'Saisis un montant valide';
                if (montant < 500) return 'Minimum 500 FCFA';
                return null;
              },
            ),
            const SizedBox(height: 24),
            FilledButton(onPressed: _envoyer, child: const Text('Publier')),
          ],
        ),
      ),
    );
  }
}
```

Le fonctionnement est simple : le `validator` de chaque champ retourne un **message d'erreur** (une `String`) si la valeur est invalide, ou `null` si tout va bien. L'appel `validate()` exécute tous les validateurs et affiche les messages sous les champs concernés. Il retourne `true` uniquement si tous les champs sont valides.

> **Erreur fréquente** : appeler `_formKey.currentState!.validate()` alors que la clé n'est attachée à aucun `Form`. Le `!` lève alors une exception. Vérifie que la même clé est bien passée à la propriété `key` du `Form`.

:::quiz
Que doit retourner la fonction `validator` d'un `TextFormField` quand la saisie est correcte ?
- [ ] `true`
- [ ] Une chaîne vide `''`
- [x] `null`
- [ ] Rien : elle doit lever une exception
> Le validateur retourne un message d'erreur si la saisie est invalide, et `null` si elle est valide. Une chaîne vide serait considérée comme un message d'erreur.
:::

### Valider un numéro de téléphone ivoirien

Pour un paiement Mobile Money, tu veux vérifier un numéro. Les numéros ivoiriens ont 10 chiffres depuis la réforme de 2021. Extrais la logique dans une fonction réutilisable plutôt que de la répéter dans chaque formulaire :

```dart
String? validerTelephone(String? valeur) {
  final numero = (valeur ?? '').replaceAll(' ', '');
  if (numero.isEmpty) return 'Le numéro est obligatoire';
  if (!RegExp(r'^[0-9]{10}$').hasMatch(numero)) {
    return 'Le numéro doit contenir 10 chiffres';
  }
  return null;
}
```

Utilise-la ensuite avec `validator: validerTelephone`. Combine-la avec `keyboardType: TextInputType.phone` pour afficher le pavé numérique, et `inputFormatters: [FilteringTextInputFormatter.digitsOnly]` (du package `flutter/services.dart`) pour empêcher la saisie de lettres.

## Focus, clavier et expérience de saisie

Quelques réglages font toute la différence sur mobile :

- `textInputAction: TextInputAction.next` fait passer au champ suivant avec la touche du clavier, et `TextInputAction.done` ferme le clavier sur le dernier champ ;
- `obscureText: true` masque un mot de passe ; ajoute une icône œil dans `suffixIcon` pour l'afficher ;
- `autovalidateMode: AutovalidateMode.onUserInteraction` sur le `Form` affiche les erreurs dès que l'utilisateur a touché un champ, sans attendre le bouton ;
- `FocusScope.of(context).unfocus()` ferme le clavier, par exemple quand on touche en dehors des champs.

Quand le clavier apparaît, il peut cacher les champs du bas. Place donc le formulaire dans un `ListView` ou un `SingleChildScrollView`, comme dans l'exemple ci-dessus, pour que l'écran reste défilable.

## Atelier guidé : naviguer et saisir une mission

Compte une heure. Crée un projet : `flutter create missions_app`, puis `flutter pub add go_router`.

1. Crée trois pages : `ListeMissionsPage`, `DetailMissionPage` et `NouvelleMissionPage`, chacune avec un `Scaffold` et un titre.
2. Définis un modèle `Mission` simple (`id`, `titre`, `budget`) et une liste de cinq missions fictives codées en dur.
3. Configure GoRouter avec les routes `/missions`, `/missions/:id` et `/missions/nouvelle`. Attention à l'ordre : déclare `nouvelle` avant `:id`, sinon `nouvelle` sera lu comme un identifiant.
4. Remplace `MaterialApp` par `MaterialApp.router`.
5. Dans la liste, affiche les missions avec `ListView.builder` et ouvre le détail avec `context.push`.
6. Ajoute un `FloatingActionButton` qui ouvre `NouvelleMissionPage`.
7. Dans ce formulaire, ajoute les champs titre et budget avec leurs validateurs, puis un champ téléphone avec `validerTelephone`.
8. Transforme la structure en barre d'onglets Missions / Profil avec `StatefulShellRoute.indexedStack`.
9. Ajoute `autovalidateMode` et `textInputAction`, puis teste au clavier réel de ton téléphone.

Pour t'auto-évaluer : peux-tu expliquer la différence entre `go` et `push` ? Sais-tu dire pourquoi on libère les `TextEditingController` dans `dispose` ? Que se passe-t-il quand tu rafraîchis la page web sur un détail ouvert avec `extra` ?

## Erreurs fréquentes

- **Mélanger `Navigator.push` et GoRouter.** Choisis une seule approche pour tout le projet, sinon l'historique devient incohérent.
- **Utiliser `go` pour ouvrir un détail.** Le bouton retour ne ramène alors pas à la liste comme prévu.
- **Oublier de convertir un paramètre.** `state.pathParameters['id']` est une `String` ; l'utiliser comme un `int` provoque une erreur de type.
- **Ordre des routes incorrect.** Une route générique `:id` déclarée avant une route fixe `nouvelle` la masque.
- **Ne pas libérer les contrôleurs.** Oublier `dispose` crée des fuites de mémoire.
- **Valider avec `==` sur un texte non nettoyé.** Pense à `trim()` pour ignorer les espaces superflus.
- **Utiliser le `BuildContext` après une opération asynchrone.** Vérifie `if (!mounted) return;` avant d'appeler `context.pop()`.

## Bonnes pratiques

- Centralise tes routes dans un seul fichier et utilise des routes nommées.
- Considère l'URL comme l'API publique de ton application : stable, lisible, prévisible.
- Garde les fonctions de validation dans un fichier dédié, testables sans interface.
- Affiche des messages d'erreur clairs, courts, qui disent quoi faire (« Saisis 10 chiffres »).
- Adapte le clavier au type de champ : numérique, téléphone, e-mail.
- Évite de transmettre de gros objets par `extra` ; préfère l'identifiant et recharge la donnée.

## À retenir

- Le `Navigator` gère une pile d'écrans ; `push` empile, `pop` dépile.
- GoRouter rend la navigation déclarative, basée sur des URL, compatible web et liens profonds.
- `context.go` remplace la pile, `context.push` ajoute un écran au-dessus.
- Les paramètres de chemin (`:id`) sont des chaînes ; `extra` ne survit pas à un rafraîchissement.
- `StatefulShellRoute.indexedStack` et `NavigationBar` offrent une navigation à onglets qui garde l'état.
- Un `Form` avec `GlobalKey<FormState>` valide tous ses `TextFormField` d'un coup ; un validateur retourne `null` si c'est valide.
- Libère les contrôleurs dans `dispose` et soigne le clavier, le focus et le défilement.
