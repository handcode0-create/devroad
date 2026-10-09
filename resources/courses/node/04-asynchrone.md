---
title: Asynchrone
minutes: 140
level: intermediate
---

## Ce que tu vas apprendre

Presque tout ce qu'un programme Node.js fait d'utile est asynchrone : lire un fichier, interroger une base de données, appeler une API de paiement. Maîtriser l'asynchrone, c'est la compétence qui sépare un débutant d'un développeur à l'aise avec Node.js. Dans ce chapitre, tu pars des callbacks, tu passes aux promesses, puis à `async` / `await`, et tu apprends à gérer les erreurs et la concurrence.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi l'asynchrone existe et comment fonctionne la boucle d'événements ;
- lire et écrire des callbacks, et reconnaître le « callback hell » ;
- créer et enchaîner des **promesses** ;
- utiliser `async` / `await` avec `try` / `catch` ;
- exécuter des tâches en parallèle avec `Promise.all` et `Promise.allSettled` ;
- gérer les délais d'expiration (*timeouts*) et annuler une opération avec `AbortController` ;
- éviter les erreurs classiques : `await` oublié, boucles séquentielles, rejets non gérés.

Prérequis : les chapitres précédents et les fonctions fléchées. Prévois environ deux heures vingt.

## Pourquoi l'asynchrone ?

Imagine un restaurant avec un seul serveur. Un **serveur synchrone** prend la commande d'un client, va en cuisine, **attend** que le plat soit prêt, le sert, puis seulement ensuite s'occupe du client suivant. Un **serveur asynchrone** prend la commande, la transmet à la cuisine, et va aussitôt servir d'autres tables ; quand un plat est prêt, il le porte.

Node.js fonctionne comme le second : un seul fil d'exécution, mais qui ne reste jamais à attendre. Les opérations lentes (disque, réseau) sont déléguées, et leur résultat est traité plus tard.

```js
import { readFileSync } from 'node:fs';
import { readFile } from 'node:fs/promises';

// Synchrone : bloque tout le programme pendant la lecture
const contenu = readFileSync('gros-fichier.txt', 'utf8');

// Asynchrone : le programme continue, le résultat arrive plus tard
const promesse = readFile('gros-fichier.txt', 'utf8');
```

Dans un serveur, une opération synchrone lente fige **toutes** les requêtes en cours. En dehors du démarrage de l'application, on privilégie donc les versions asynchrones.

## Première approche : les callbacks

Historiquement, Node.js gérait l'asynchrone avec des **callbacks** : une fonction passée en argument, appelée quand l'opération est terminée. Par convention, le premier paramètre est l'erreur éventuelle.

```js
import { readFile } from 'node:fs';

readFile('roadmap.json', 'utf8', (erreur, texte) => {
  if (erreur) {
    console.error('Lecture impossible :', erreur.message);
    return;
  }
  console.log('Contenu :', texte);
});

console.log('Cette ligne s’affiche AVANT le contenu du fichier');
```

Le problème apparaît quand les opérations dépendent les unes des autres :

```js
// Le « callback hell » : pyramide difficile à lire et à déboguer
lireUtilisateur(id, (e1, utilisateur) => {
  if (e1) return gerer(e1);
  lireCommandes(utilisateur.id, (e2, commandes) => {
    if (e2) return gerer(e2);
    lirePaiement(commandes[0].id, (e3, paiement) => {
      if (e3) return gerer(e3);
      console.log(paiement);
    });
  });
});
```

Chaque niveau ajoute de l'indentation et une gestion d'erreur répétée. Les promesses ont été inventées pour résoudre ce problème.

## Les promesses

Une **promesse** (`Promise`) représente le résultat futur d'une opération. Elle est dans l'un de ces trois états : en attente (*pending*), tenue (*fulfilled*, avec une valeur) ou rompue (*rejected*, avec une erreur).

```js
function attendre(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

function chargerRoadmap(id) {
  return new Promise((resolve, reject) => {
    setTimeout(() => {
      if (id === 1) resolve({ id: 1, titre: 'Node.js' });
      else reject(new Error(`Roadmap ${id} introuvable`));
    }, 300);
  });
}

chargerRoadmap(1)
  .then((roadmap) => {
    console.log('Reçu :', roadmap.titre);
    return roadmap.id;
  })
  .then((id) => console.log('Identifiant :', id))
  .catch((erreur) => console.error('Échec :', erreur.message))
  .finally(() => console.log('Terminé'));
```

- `.then()` s'exécute quand la promesse est tenue, et retourne une nouvelle promesse : on peut donc **chaîner** ;
- `.catch()` intercepte toute erreur survenue plus haut dans la chaîne ;
- `.finally()` s'exécute dans tous les cas, utile pour nettoyer (fermer une connexion).

Les modules modernes de Node fournissent des versions à promesses de leurs API. Ce sont celles à utiliser : `node:fs/promises`, `node:timers/promises`, et `fetch` en natif.

```js
import { setTimeout as pause } from 'node:timers/promises';

await pause(500); // attend 500 ms sans bloquer
```

## async / await : écrire de l'asynchrone qui se lit comme du synchrone

`async` et `await` sont du « sucre syntaxique » au-dessus des promesses. Une fonction `async` retourne toujours une promesse ; `await` suspend **cette fonction** (pas le programme entier) jusqu'à ce que la promesse soit résolue.

```js
import { readFile } from 'node:fs/promises';

async function lireRoadmap(chemin) {
  const texte = await readFile(chemin, 'utf8');
  return JSON.parse(texte);
}

const roadmap = await lireRoadmap('./roadmap.json');
console.log(roadmap.titre);
```

Le `await` à la racine du fichier (*top-level await*) est autorisé en ESM : pas besoin d'envelopper ton script dans une fonction. Le callback hell du début devient lisible :

```js
async function afficherPaiement(id) {
  const utilisateur = await lireUtilisateur(id);
  const commandes = await lireCommandes(utilisateur.id);
  const paiement = await lirePaiement(commandes[0].id);
  console.log(paiement);
}
```

### Gérer les erreurs avec try / catch

Un `await` sur une promesse rompue **lance une exception**. On l'attrape avec le `try` / `catch` habituel :

```js
async function chargerSecurise(id) {
  try {
    const roadmap = await chargerRoadmap(id);
    return roadmap;
  } catch (erreur) {
    console.error('Impossible de charger :', erreur.message);
    return null;
  } finally {
    console.log('Fin de la tentative');
  }
}
```

Dans Express 5, une exception levée dans un gestionnaire `async` est transmise automatiquement au middleware d'erreurs. Avec Express 4, il faut l'attraper toi-même et appeler `next(erreur)`.

:::quiz
Que retourne toujours une fonction déclarée avec le mot-clé async ?
- [ ] La valeur retournée, telle quelle
- [ ] undefined
- [x] Une promesse
- [ ] Un callback
> Une fonction async enveloppe sa valeur de retour dans une promesse. C'est pourquoi on utilise await (ou then) pour récupérer le résultat.
:::

## Séquentiel ou parallèle ?

C'est l'erreur de performance la plus courante. Si deux opérations sont **indépendantes**, ne les attends pas l'une après l'autre.

```js
// Séquentiel : durée = A + B
const roadmap = await chargerRoadmap(1);
const fiches = await chargerFiches(1);

// Parallèle : durée = max(A, B)
const [roadmap2, fiches2] = await Promise.all([
  chargerRoadmap(1),
  chargerFiches(1),
]);
```

Avec trois appels de 300 ms chacun, la version séquentielle prend environ 900 ms, la version parallèle environ 300 ms.

### Les combinateurs de promesses

| Fonction | Résultat | Si une promesse échoue |
| --- | --- | --- |
| `Promise.all([...])` | Tableau de toutes les valeurs | Rompue immédiatement |
| `Promise.allSettled([...])` | Tableau d'états (réussite ou échec) | Attend tout, jamais rompue |
| `Promise.race([...])` | Le premier terminé (succès ou échec) | Dépend du premier arrivé |
| `Promise.any([...])` | Le premier **réussi** | Rompue seulement si toutes échouent |

`Promise.allSettled` est idéale quand tu veux continuer même si certaines tâches échouent, par exemple notifier plusieurs services :

```js
const resultats = await Promise.allSettled([
  envoyerEmail(utilisateur),
  envoyerSms(utilisateur),
  notifierSlack(utilisateur),
]);

for (const r of resultats) {
  if (r.status === 'fulfilled') console.log('OK :', r.value);
  else console.error('Échec :', r.reason.message);
}
```

### Le piège de la boucle

`forEach` ne sait pas attendre les fonctions `async` : il lance tout et n'attend rien.

```js
// Mauvais : forEach n'attend pas les await
ids.forEach(async (id) => {
  await traiter(id);
});
console.log('Terminé ?'); // s'affiche trop tôt

// Bon, séquentiel : for...of avec await
for (const id of ids) {
  await traiter(id);
}

// Bon, parallèle : map + Promise.all
await Promise.all(ids.map((id) => traiter(id)));
```

Choisis le séquentiel quand l'ordre compte ou quand tu dois ménager un service (limite de requêtes), le parallèle sinon. Pour des centaines de tâches, limite la concurrence par lots plutôt que de tout lancer en même temps.

## Délais d'expiration et annulation

Un appel réseau peut ne jamais répondre. Sans limite de temps, ton programme attend indéfiniment. `AbortSignal.timeout` interrompt `fetch` après un délai :

```js
try {
  const reponse = await fetch('https://api.exemple.com/lent', {
    signal: AbortSignal.timeout(5000), // 5 secondes maximum
  });
  const donnees = await reponse.json();
  console.log(donnees);
} catch (erreur) {
  if (erreur.name === 'TimeoutError') {
    console.error('Le service a mis trop de temps à répondre');
  } else {
    console.error('Erreur réseau :', erreur.message);
  }
}
```

Pour annuler à la demande (l'utilisateur ferme la page, par exemple), crée un `AbortController` et passe son `signal` :

```js
const controleur = new AbortController();
const requete = fetch('https://api.exemple.com/donnees', { signal: controleur.signal });

setTimeout(() => controleur.abort(), 1000); // annule après une seconde
```

### Réessayer en cas d'échec

Les services de paiement mobile (Wave, Orange Money, CinetPay) ou les réseaux instables échouent parfois ponctuellement. Une fonction de nouvelle tentative avec attente croissante est un classique :

```js
import { setTimeout as pause } from 'node:timers/promises';

export async function avecReessais(tache, tentatives = 3) {
  let derniereErreur;

  for (let essai = 1; essai <= tentatives; essai++) {
    try {
      return await tache();
    } catch (erreur) {
      derniereErreur = erreur;
      console.warn(`Essai ${essai}/${tentatives} échoué : ${erreur.message}`);
      if (essai < tentatives) await pause(2 ** essai * 200); // 400 ms, 800 ms…
    }
  }
  throw derniereErreur;
}

const donnees = await avecReessais(() =>
  fetch('https://api.exemple.com/statut').then((r) => {
    if (!r.ok) throw new Error(`HTTP ${r.status}`);
    return r.json();
  }),
);
```

> **Attention** : ne réessaie que les opérations **idempotentes** (lecture, ou écriture protégée par un identifiant unique). Réessayer aveuglément un paiement peut débiter le client deux fois.

:::quiz
Quelle écriture exécute trois requêtes indépendantes en parallèle et attend leur résultat ?
- [ ] Trois await successifs, l'un après l'autre
- [ ] ids.forEach(async (id) => await traiter(id))
- [x] await Promise.all(ids.map((id) => traiter(id)))
- [ ] Promise.race([...]) avec les trois requêtes
> Promise.all lance toutes les promesses en même temps et attend qu'elles soient toutes terminées. forEach n'attend pas, et des await successifs sont séquentiels.
:::

## Les rejets non gérés

Une promesse rompue sans aucun `catch` provoque un **rejet non géré** (*unhandled rejection*). Depuis Node 15, cela arrête le processus avec une erreur. C'est une bonne chose : un échec silencieux est pire qu'un échec visible. Tu peux ajouter un filet de sécurité global pour journaliser avant l'arrêt :

```js
process.on('unhandledRejection', (raison) => {
  console.error('Rejet non géré :', raison);
  process.exit(1);
});
```

Mais le bon réflexe reste de gérer l'erreur à l'endroit où tu appelles une fonction asynchrone.

## Atelier guidé : un agrégateur de données asynchrone

Compte une heure trente. Tu vas écrire un script qui charge plusieurs sources en parallèle, de façon robuste.

1. Crée `src/pause.js` qui exporte `attendre(ms)` avec `node:timers/promises`.
2. Crée `src/sources.js` avec trois fonctions asynchrones simulées (`chargerRoadmaps`, `chargerFiches`, `chargerStatistiques`), chacune attendant entre 200 et 600 ms. Fais échouer l'une d'elles une fois sur trois avec `Math.random()`.
3. Dans `src/index.js`, appelle-les **séquentiellement** et mesure le temps avec `console.time` / `console.timeEnd`.
4. Réécris avec `Promise.all` et compare les durées. Note l'écart.
5. Remplace `Promise.all` par `Promise.allSettled` et affiche pour chaque source si elle a réussi ou échoué, sans arrêter le programme.
6. Écris `avecReessais(tache, tentatives)` avec attente croissante et applique-la à la source instable.
7. Ajoute une limite de temps : écris `avecDelai(promesse, ms)` à l'aide de `Promise.race` qui rejette avec une erreur « Délai dépassé » après `ms` millisecondes.
8. Ajoute une récupération réelle avec `fetch` et `AbortSignal.timeout(3000)` vers une API publique de ton choix, en gérant `TimeoutError`.
9. Traite une liste de dix identifiants avec `for...of` (séquentiel) puis avec `Promise.all` et `map`. Observe la différence de durée.

Pour t'auto-évaluer : explique pourquoi `forEach(async ...)` ne convient pas pour attendre des tâches, et dans quel cas tu préfères `allSettled` à `all`.

## Erreurs fréquentes

- **Oublier `await`.** Tu manipules une promesse au lieu de sa valeur : tu obtiens `Promise { <pending> }`.
- **Utiliser `await` hors d'une fonction `async` dans un fichier CommonJS.** En ESM, le `await` racine est autorisé.
- **Attendre en séquence des tâches indépendantes.** Utilise `Promise.all`.
- **Utiliser `forEach` avec `async`.** Passe à `for...of` ou à `map` avec `Promise.all`.
- **Avaler une erreur avec un `catch` vide.** Au minimum, journalise-la.
- **Oublier de vérifier `reponse.ok` après `fetch`.** Une erreur 500 serait traitée comme un succès.
- **Mélanger callbacks et promesses.** Convertis avec `util.promisify` ou utilise les API `node:fs/promises`.
- **Lancer trop de tâches en parallèle.** Des milliers de requêtes simultanées saturent le réseau ou la base.

## Bonnes pratiques

- Privilégie `async` / `await` pour la lisibilité, et les promesses pour les combinaisons (`all`, `race`).
- Utilise les API à promesses : `node:fs/promises`, `node:timers/promises`.
- Mets toujours un délai maximum sur les appels réseau.
- Gère les erreurs au bon niveau : près de l'appel pour réagir, tout en haut pour journaliser.
- Rends les opérations réessayables idempotentes avant d'ajouter des tentatives.
- Limite la concurrence quand tu traites de gros volumes.
- Ne bloque jamais la boucle d'événements avec des API synchrones dans un serveur.

## À retenir

- Node.js est **non bloquant** : les opérations lentes sont déléguées et leur résultat traité plus tard.
- Les **callbacks** sont l'ancienne méthode ; les **promesses** les remplacent et se chaînent avec `then`, `catch`, `finally`.
- `async` / `await` rend le code asynchrone lisible ; une fonction `async` retourne toujours une promesse.
- Les erreurs d'un `await` s'attrapent avec `try` / `catch`.
- `Promise.all` exécute en parallèle ; `allSettled` tolère les échecs ; `race` et `any` prennent le premier résultat.
- `AbortSignal.timeout` et `AbortController` limitent ou annulent une opération.
- Un rejet non géré arrête le processus : gère toujours tes erreurs.
