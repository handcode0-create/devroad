---
title: Asynchrone et Promises
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Charger une roadmap depuis un serveur, enregistrer une progression, attendre une réponse de paiement Wave ou Orange Money : toutes ces opérations prennent du temps. JavaScript ne peut pas figer la page pendant qu'il attend. Il utilise donc l'**asynchrone**. Ce chapitre t'explique le mécanisme, puis les outils modernes pour l'écrire proprement : les **Promises** et `async / await`.

À la fin du chapitre, tu seras capable de :

- expliquer ce qui différencie code synchrone et asynchrone ;
- décrire la boucle d'événements (*event loop*) en termes simples ;
- créer et consommer une Promise avec `then`, `catch` et `finally` ;
- écrire du code lisible avec `async / await` ;
- gérer les erreurs avec `try / catch` ;
- lancer plusieurs opérations en parallèle avec `Promise.all` ;
- appeler une API avec `fetch` et lire du JSON.

Prérequis : les trois chapitres précédents (fonctions, callbacks, objets). Prévois deux heures et demie. Les exemples avec `fetch` fonctionnent dans le navigateur et dans Node.js 18 ou plus récent.

## Synchrone, asynchrone : le problème

Par défaut, JavaScript exécute le code **ligne après ligne**, une seule instruction à la fois : on dit qu'il est **mono-thread**. Si une ligne prend dix secondes, tout attend, y compris l'affichage et les clics.

```js
console.log('1. Début');

setTimeout(() => {
  console.log('2. Après 1 seconde');
}, 1000);

console.log('3. Fin');
```

Le résultat affiché est : `1. Début`, `3. Fin`, puis `2. Après 1 seconde`. `setTimeout` ne bloque pas : il confie le minuteur au navigateur, et le callback sera exécuté plus tard. Le programme continue pendant ce temps.

### La boucle d'événements

Pour comprendre l'ordre d'exécution, imagine trois éléments : la **pile d'appels** (le code en cours), les **API du navigateur** (minuteurs, réseau) et une **file d'attente** de tâches prêtes. La boucle d'événements regarde en permanence : « la pile est-elle vide ? Si oui, je prends la prochaine tâche dans la file ». Ainsi, un callback n'est jamais exécuté tant que le code synchrone n'est pas terminé, même avec un délai de 0 milliseconde.

```js
setTimeout(() => console.log('timeout 0'), 0);
console.log('synchrone');
// synchrone
// timeout 0
```

> **À retenir** : le code asynchrone ne s'exécute jamais « en parallèle » dans ton code JavaScript : il est simplement mis en attente, puis repris quand la pile est libre.

## Le « callback hell »

Avant les Promises, on enchaînait des callbacks. Voici trois opérations dépendantes :

```js
chargerUtilisateur(1, (erreur, utilisateur) => {
  if (erreur) return console.error(erreur);
  chargerRoadmaps(utilisateur.id, (erreur, roadmaps) => {
    if (erreur) return console.error(erreur);
    chargerLecons(roadmaps[0].id, (erreur, lecons) => {
      if (erreur) return console.error(erreur);
      console.log(lecons);
    });
  });
});
```

Le code dérive vers la droite, les erreurs sont répétées, la lecture est pénible. On appelle ce phénomène la « pyramide de l'enfer ». Les Promises ont été créées pour l'éviter.

## Les Promises

Une **Promise** est un objet qui représente le résultat futur d'une opération asynchrone. Elle a trois états :

| État | Signification |
| --- | --- |
| `pending` | L'opération est en cours |
| `fulfilled` | L'opération a réussi, une valeur est disponible |
| `rejected` | L'opération a échoué, une erreur est disponible |

Une fois réglée (réussie ou échouée), une Promise ne change plus jamais d'état.

### Créer une Promise

```js
function attendre(ms) {
  return new Promise((resolve) => {
    setTimeout(resolve, ms);
  });
}

function chargerRoadmap(id) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (id <= 0) {
        reject(new Error('Identifiant invalide'));
      } else {
        resolve({ id, titre: 'Laravel' });
      }
    }, 500);
  });
}
```

Le constructeur reçoit une fonction avec `resolve` (appelée en cas de succès) et `reject` (appelée en cas d'échec). Dans la pratique, tu créeras rarement des Promises à la main : la plupart des API (comme `fetch`) t'en renvoient déjà.

### Consommer une Promise : then, catch, finally

```js
chargerRoadmap(3)
  .then((roadmap) => {
    console.log('Reçu :', roadmap.titre);
    return roadmap.id;
  })
  .then((id) => console.log('Id :', id))
  .catch((erreur) => console.error('Échec :', erreur.message))
  .finally(() => console.log('Terminé, succès ou non'));
```

Chaque `then` retourne une **nouvelle** Promise, ce qui permet d'enchaîner à plat. Si une erreur survient n'importe où dans la chaîne, elle saute directement au `catch`. `finally` s'exécute dans tous les cas : parfait pour masquer un indicateur de chargement.

:::quiz
Dans quel état se trouve une Promise dont l'opération est encore en cours ?
- [ ] fulfilled
- [ ] rejected
- [x] pending
- [ ] resolved
> Une Promise démarre à l'état `pending`. Elle passe ensuite à `fulfilled` en cas de succès ou à `rejected` en cas d'échec, puis ne change plus.
:::

## async / await

`async / await` est une syntaxe qui permet d'écrire du code asynchrone comme s'il était synchrone. C'est la façon standard d'écrire aujourd'hui.

- Une fonction marquée `async` retourne **toujours** une Promise.
- Le mot-clé `await` **met en pause la fonction** jusqu'à ce que la Promise soit réglée, puis donne sa valeur. Il ne bloque pas le reste de la page.

```js
async function afficherRoadmap(id) {
  const roadmap = await chargerRoadmap(id);
  console.log(`Roadmap : ${roadmap.titre}`);
  return roadmap;
}

afficherRoadmap(3);
```

Comparé à la pyramide d'avant, le même enchaînement devient lisible :

```js
async function afficherLecons() {
  const utilisateur = await chargerUtilisateur(1);
  const roadmaps = await chargerRoadmaps(utilisateur.id);
  const lecons = await chargerLecons(roadmaps[0].id);
  console.log(lecons);
}
```

### Gérer les erreurs avec try / catch

Une Promise rejetée devient, avec `await`, une exception ordinaire :

```js
async function afficherRoadmapSecurise(id) {
  try {
    const roadmap = await chargerRoadmap(id);
    console.log(roadmap.titre);
  } catch (erreur) {
    console.error('Impossible de charger :', erreur.message);
  } finally {
    console.log('Fin du chargement');
  }
}

afficherRoadmapSecurise(-1); // Impossible de charger : Identifiant invalide
```

> **Erreur fréquente** : oublier `await`. `const roadmap = chargerRoadmap(3)` ne donne pas la roadmap mais une **Promise**. Tu verras alors `Promise { <pending> }` dans la console.

:::quiz
Que retourne toujours une fonction déclarée avec le mot-clé `async` ?
- [ ] La valeur de son dernier `return`, directement
- [ ] undefined
- [x] Une Promise
- [ ] Un tableau
> Une fonction `async` enveloppe toujours son résultat dans une Promise. C'est pourquoi on l'appelle avec `await` ou `then`.
:::

## Plusieurs opérations en même temps

Si deux opérations sont indépendantes, les attendre l'une après l'autre fait perdre du temps :

```js
// Séquentiel : 1 s + 1 s = environ 2 s
const a = await chargerRoadmap(1);
const b = await chargerRoadmap(2);

// Parallèle : environ 1 s au total
const [r1, r2] = await Promise.all([chargerRoadmap(1), chargerRoadmap(2)]);
```

`Promise.all` démarre toutes les Promises en même temps et attend que **toutes** réussissent. Si une seule échoue, le tout est rejeté. Pour obtenir le résultat de chacune, réussite ou non, utilise `Promise.allSettled` :

```js
const resultats = await Promise.allSettled([
  chargerRoadmap(1),
  chargerRoadmap(-5),
]);

resultats.forEach((r) => {
  if (r.status === 'fulfilled') console.log('OK', r.value);
  else console.log('KO', r.reason.message);
});
```

`Promise.race` retourne le résultat de la première Promise réglée : pratique pour imposer un délai maximum.

## Appeler une API avec fetch

`fetch` envoie une requête HTTP et retourne une Promise. Il faut **deux** `await` : un pour la réponse, un pour lire le corps.

```js
async function chargerPosts() {
  const reponse = await fetch('https://jsonplaceholder.typicode.com/posts?_limit=3');

  if (!reponse.ok) {
    throw new Error(`Erreur HTTP ${reponse.status}`);
  }

  const donnees = await reponse.json();
  return donnees;
}
```

Point crucial : `fetch` ne rejette sa Promise **que pour les erreurs réseau** (pas de connexion, domaine introuvable). Une réponse 404 ou 500 est considérée comme réussie ! Vérifie donc toujours `reponse.ok`.

Pour envoyer des données, précise la méthode, les en-têtes et le corps :

```js
async function creerProgression(leconId) {
  const reponse = await fetch('/api/progressions', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
    },
    body: JSON.stringify({ lecon_id: leconId }),
  });

  if (!reponse.ok) throw new Error(`Erreur ${reponse.status}`);
  return reponse.json();
}
```

Avec Laravel, il faudra aussi fournir le jeton CSRF ou utiliser l'authentification par jeton ; Inertia s'en occupe pour toi dans DevRoad.

### Annuler et limiter l'attente

Un utilisateur peut quitter la page avant la fin d'une requête. `AbortController` permet d'annuler :

```js
const controleur = new AbortController();
const minuterie = setTimeout(() => controleur.abort(), 5000);

try {
  const reponse = await fetch('/api/roadmaps', { signal: controleur.signal });
  console.log(await reponse.json());
} catch (erreur) {
  if (erreur.name === 'AbortError') console.log('Requête annulée (délai dépassé)');
  else throw erreur;
} finally {
  clearTimeout(minuterie);
}
```

:::quiz
Que se passe-t-il quand un serveur répond avec le statut 404 à un `fetch` ?
- [ ] La Promise est rejetée automatiquement
- [x] La Promise est réussie, il faut vérifier `reponse.ok`
- [ ] Le navigateur recharge la page
- [ ] fetch relance la requête
> `fetch` ne rejette que sur une erreur réseau. Pour détecter une réponse 4xx ou 5xx, il faut tester `reponse.ok` ou `reponse.status` soi-même.
:::

## Pièges classiques de l'asynchrone

### await dans une boucle forEach

`forEach` ne sait pas attendre les fonctions `async` : il lance tout sans attendre. Utilise `for...of` pour un traitement séquentiel, ou `Promise.all` avec `map` pour un traitement parallèle.

```js
// Séquentiel
for (const id of [1, 2, 3]) {
  const roadmap = await chargerRoadmap(id);
  console.log(roadmap.titre);
}

// Parallèle
const roadmaps = await Promise.all([1, 2, 3].map((id) => chargerRoadmap(id)));
```

### Promesse rejetée non gérée

Si tu oublies le `catch` ou le `try / catch`, l'erreur remonte en « unhandled rejection » dans la console. Gère toujours les erreurs au niveau où tu sais quoi en faire (afficher un message, réessayer).

## Atelier guidé : un chargeur de roadmaps robuste

Compte une heure et demie. Crée `chargeur.js`, exécutable avec Node.js 18 ou plus.

1. Écris `attendre(ms)` qui retourne une Promise résolue après `ms` millisecondes.
2. Écris `chargerRoadmap(id)` simulée avec `attendre`, qui rejette si `id <= 0`.
3. Consomme-la d'abord avec `then / catch / finally`, avec un `id` valide puis invalide.
4. Réécris le même code avec `async / await` et `try / catch / finally`.
5. Charge trois roadmaps en séquence, mesure le temps avec `console.time` et `console.timeEnd`, puis recommence avec `Promise.all` et compare.
6. Utilise `Promise.allSettled` avec un identifiant invalide dans la liste et affiche un résumé « 2 réussies, 1 échouée ».
7. Appelle `fetch` sur `https://jsonplaceholder.typicode.com/users/1`, vérifie `reponse.ok`, puis affiche le nom de l'utilisateur.
8. Ajoute une fonction `avecDelai(promesse, ms)` qui utilise `Promise.race` pour rejeter si la promesse dépasse le délai.
9. Teste volontairement une URL erronée et un statut 404 (`/users/99999`) et observe la différence entre erreur réseau et erreur HTTP.

Pour t'auto-évaluer : explique pourquoi l'affichage de `'timeout 0'` arrive après un `console.log` synchrone, et pourquoi on a besoin de deux `await` pour lire une réponse `fetch`.

## Erreurs fréquentes

- **Oublier `await`.** Tu manipules une Promise au lieu de sa valeur.
- **Utiliser `await` hors d'une fonction `async`.** Cela déclenche une erreur de syntaxe (sauf au niveau supérieur d'un module).
- **Ne pas vérifier `reponse.ok`.** Une erreur 500 passe pour un succès.
- **Enchaîner des appels indépendants avec des `await` successifs.** Utilise `Promise.all` pour gagner du temps.
- **Utiliser `forEach` avec `async`.** Les opérations ne sont pas attendues ; choisis `for...of` ou `Promise.all`.
- **Avaler les erreurs.** Un `catch` vide cache les problèmes ; au minimum, journalise avec `console.error`.
- **Oublier de retourner la Promise dans une chaîne `then`.** La suite démarre sans attendre.
- **Modifier l'interface sans indicateur de chargement.** L'utilisateur croit que l'application est cassée.

## Bonnes pratiques

- Préfère `async / await` à `then` pour la lisibilité, et garde `then` pour les cas simples.
- Gère les trois états côté interface : chargement, succès, erreur.
- Vérifie toujours `reponse.ok` et prévois un message d'erreur clair pour l'utilisateur.
- Parallélise les appels indépendants avec `Promise.all`.
- Mets en place un délai maximum ou une annulation avec `AbortController` pour les requêtes longues.
- Isole les appels réseau dans des fonctions dédiées (`api.js`) plutôt que de les éparpiller dans l'interface.

## À retenir

- JavaScript est mono-thread : l'asynchrone évite de bloquer la page pendant les attentes.
- La boucle d'événements exécute les callbacks seulement quand la pile d'appels est vide.
- Une Promise est `pending`, puis `fulfilled` ou `rejected`, sans retour en arrière.
- `then`, `catch` et `finally` enchaînent à plat ; `async / await` rend le code encore plus clair.
- Avec `await`, une Promise rejetée se traite par `try / catch`.
- `Promise.all` lance en parallèle ; `allSettled` donne chaque résultat ; `race` prend le plus rapide.
- `fetch` nécessite de tester `reponse.ok` et d'attendre `reponse.json()`.
