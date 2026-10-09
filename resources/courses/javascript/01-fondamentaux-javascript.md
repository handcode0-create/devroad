---
title: Fondamentaux JavaScript
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

JavaScript est le seul langage que tous les navigateurs comprennent. C'est lui qui rend une page vivante : un clic, une liste qui se filtre, un formulaire qui se vérifie avant l'envoi. C'est aussi la base de React, de Next.js et de TypeScript. Ce chapitre pose les fondations sur lesquelles tout le reste repose.

À la fin du chapitre, tu seras capable de :

- déclarer des variables avec `const` et `let`, et expliquer pourquoi on évite `var` ;
- reconnaître les types de base : nombre, chaîne, booléen, `null`, `undefined` ;
- écrire des conditions et des boucles ;
- comparer des valeurs avec `===` plutôt que `==` ;
- comprendre les valeurs « vraies » et « fausses » (*truthy* et *falsy*) ;
- utiliser les gabarits de texte (template literals) ;
- tester du code dans la console du navigateur ou avec Node.js.

Prérequis : savoir ce qu'est une page web (HTML). Aucune expérience de programmation n'est nécessaire. Prévois deux heures. Tu n'as rien à installer : ouvre ton navigateur, appuie sur F12 puis choisis l'onglet **Console**.

## Où exécuter du JavaScript ?

Il existe deux environnements principaux. Le **navigateur** exécute le JavaScript d'une page web : il peut modifier le HTML, réagir aux clics, appeler des API. **Node.js** exécute du JavaScript en dehors du navigateur, sur ton ordinateur ou sur un serveur. Les deux comprennent le même langage ; seuls les outils autour changent (le navigateur a `document`, Node.js a l'accès aux fichiers).

Pour un premier test, crée un fichier `test.js` et lance-le avec Node.js :

```js
console.log('Bonjour DevRoad');
```

```bash
node test.js
```

`console.log` affiche une valeur dans la console. Ce sera ton meilleur ami pour comprendre ce que fait ton code.

## Les variables : `const`, `let` et `var`

Une variable est une boîte nommée qui contient une valeur. Aujourd'hui, on utilise deux mots-clés :

```js
const nomApp = 'DevRoad';   // ne sera jamais réaffecté
let progression = 0;        // va changer au fil du temps

progression = 25;           // autorisé
nomApp = 'Autre';           // TypeError : Assignment to constant variable
```

La règle simple : **utilise `const` par défaut**, et `let` seulement quand la valeur doit être réaffectée. Cela rend le code plus prévisible : quand tu lis un `const`, tu sais qu'il ne changera pas.

Le vieux mot-clé `var` existe encore mais il a des comportements surprenants (il ignore les blocs et il est « hissé »). Tu le verras dans d'anciens tutoriels ; évite-le dans ton code.

> **Astuce** : choisis des noms qui disent ce que contient la variable. `nbLecons` est bien meilleur que `n` ou `x`. En JavaScript, on écrit les noms en camelCase : `nomUtilisateur`, `estTermine`.

Un `const` qui contient un tableau ou un objet peut quand même être modifié de l'intérieur (on ajoute un élément, on change une propriété). Ce qui est interdit, c'est de remplacer la variable par une autre valeur. Nous y reviendrons au chapitre sur les objets et les tableaux.

## Les types de base

Chaque valeur a un type. Tu peux le connaître avec l'opérateur `typeof`.

| Type | Exemple | `typeof` |
| --- | --- | --- |
| Nombre | `42`, `3.14` | `number` |
| Chaîne de caractères | `'Awa'`, `"Kofi"` | `string` |
| Booléen | `true`, `false` | `boolean` |
| Absence volontaire | `null` | `object` (bug historique) |
| Non défini | `undefined` | `undefined` |

```js
console.log(typeof 42);          // number
console.log(typeof 'texte');     // string
console.log(typeof true);        // boolean
console.log(typeof undefined);   // undefined
```

`undefined` signifie « cette variable existe mais n'a pas de valeur ». `null` signifie « il n'y a volontairement rien ici ». Dans le doute, retiens : `undefined` arrive tout seul, `null` est posé par le développeur.

JavaScript est **typé dynamiquement** : une variable `let` peut contenir un nombre, puis une chaîne. C'est pratique, mais source d'erreurs. C'est précisément ce que TypeScript viendra corriger plus tard.

### Les nombres

Les opérateurs habituels sont `+`, `-`, `/` (division) et `%` (le reste de la division), plus l'opérateur de multiplication. Attention aux flottants :

```js
console.log(10 % 3);        // 1
console.log(0.1 + 0.2);     // 0.30000000000000004
console.log((0.1 + 0.2).toFixed(2)); // "0.30"
```

Pour manipuler de l'argent (par exemple des FCFA), travaille en entiers : stocke 1500 FCFA plutôt qu'un nombre à virgule.

### Les chaînes et les gabarits

Pour assembler du texte, utilise les **template literals** avec des accents graves et `${ }` :

```js
const prenom = 'Awa';
const lecons = 12;

const message = `Bravo ${prenom}, tu as terminé ${lecons} leçons !`;
console.log(message);
console.log(message.length);        // nombre de caractères
console.log(prenom.toUpperCase());  // AWA
```

C'est bien plus lisible que `'Bravo ' + prenom + ', tu as terminé ' + lecons + ' leçons !'`.

:::quiz
Quelle déclaration est la plus adaptée pour une valeur qui ne sera jamais réaffectée ?
- [ ] var nomApp = 'DevRoad';
- [ ] let nomApp = 'DevRoad';
- [x] const nomApp = 'DevRoad';
- [ ] nomApp = 'DevRoad';
> On utilise `const` par défaut. `let` sert seulement quand la valeur doit changer, et `var` est à éviter à cause de ses comportements surprenants.
:::

## Comparer et décider

### Égalité stricte

JavaScript propose deux façons de comparer : `==` (qui convertit les types avant de comparer) et `===` (qui compare la valeur **et** le type). Les conversions de `==` produisent des résultats déroutants :

```js
console.log(5 == '5');     // true  (surprenant)
console.log(5 === '5');    // false (logique)
console.log(0 == false);   // true
console.log(0 === false);  // false
```

> **Attention** : utilise toujours `===` et `!==`. Tu éviteras toute une famille de bugs liés aux conversions silencieuses.

### Valeurs « vraies » et « fausses »

Dans une condition, JavaScript convertit la valeur en booléen. Six valeurs sont **falsy** : `false`, `0`, `''` (chaîne vide), `null`, `undefined` et `NaN`. Tout le reste est **truthy**, y compris `'0'`, `[]` et `{}`.

```js
const nom = '';

if (nom) {
  console.log('Nom renseigné');
} else {
  console.log('Nom manquant'); // affiché car '' est falsy
}
```

### if, else if, else

```js
function niveau(score) {
  if (score >= 80) {
    return 'professionnel';
  } else if (score >= 50) {
    return 'intermédiaire';
  } else {
    return 'débutant';
  }
}

console.log(niveau(65)); // intermédiaire
```

### Opérateurs logiques et ternaire

`&&` (et), `||` (ou) et `!` (non) combinent des conditions. L'opérateur **ternaire** écrit une condition simple sur une ligne :

```js
const estConnecte = true;
const aAbonnement = false;

console.log(estConnecte && aAbonnement);   // false
console.log(estConnecte || aAbonnement);   // true
console.log(!estConnecte);                 // false

const label = aAbonnement ? 'Premium' : 'Gratuit';
```

Deux opérateurs modernes sont très utiles pour les valeurs par défaut : `??` ne remplace que `null` et `undefined`, alors que `||` remplace toute valeur falsy.

```js
const points = 0;
console.log(points || 10);  // 10 (le zéro est écarté, souvent un bug)
console.log(points ?? 10);  // 0  (le zéro est conservé)
```

:::quiz
Que retourne l'expression `0 ?? 10` ?
- [ ] 10
- [x] 0
- [ ] undefined
- [ ] null
> L'opérateur `??` ne remplace que `null` et `undefined`. Le zéro est une vraie valeur, donc il est conservé, contrairement à `||` qui le remplacerait.
:::

### switch

Quand tu compares une valeur à plusieurs cas précis, `switch` est plus lisible :

```js
function icone(statut) {
  switch (statut) {
    case 'termine':
      return 'check';
    case 'en-cours':
      return 'clock';
    default:
      return 'circle';
  }
}
```

N'oublie pas `return` ou `break` à la fin de chaque cas, sinon l'exécution continue dans le cas suivant.

## Les boucles

Une boucle répète du code. Trois formes à connaître.

```js
// 1. for : quand tu connais le nombre de tours
for (let i = 1; i <= 3; i++) {
  console.log(`Leçon ${i}`);
}

// 2. while : tant qu'une condition est vraie
let tentatives = 0;
while (tentatives < 3) {
  tentatives++;
}

// 3. for...of : pour parcourir un tableau
const technos = ['HTML', 'CSS', 'JavaScript'];
for (const techno of technos) {
  console.log(techno);
}
```

`for...of` est la forme la plus agréable pour parcourir une liste. Le chapitre suivant te montrera des méthodes encore plus expressives (`map`, `filter`) qui remplacent souvent les boucles.

> **Erreur fréquente** : une boucle `while` dont la condition ne devient jamais fausse tourne à l'infini et fige l'onglet. Vérifie toujours que quelque chose change à chaque tour.

## Convertir des types

Les données qui viennent d'un formulaire sont toujours du texte. Il faut les convertir avant de calculer :

```js
const saisie = '42';

console.log(Number(saisie) + 1);   // 43
console.log(parseInt('12px', 10)); // 12
console.log(Number('abc'));        // NaN (Not a Number)
console.log(String(99));           // "99"
console.log(Boolean(''));          // false
```

`NaN` est le résultat d'une conversion impossible. Pour le détecter, utilise `Number.isNaN(valeur)`, car `NaN === NaN` est faux.

:::quiz
Pourquoi `'5' + 1` donne-t-il `'51'` et non `6` ?
- [ ] Parce que JavaScript refuse d'additionner
- [x] Parce que `+` concatène dès qu'un opérande est une chaîne
- [ ] Parce que 5 est converti en booléen
- [ ] Parce que la chaîne est vide
> Avec une chaîne, l'opérateur `+` fait de la concaténation. Il faut convertir explicitement avec `Number('5') + 1` pour obtenir 6.
:::

## Atelier guidé : le calculateur de progression DevRoad

Compte une heure. Crée un fichier `progression.js` et lance-le avec `node progression.js` (ou colle le code dans la console du navigateur).

1. Déclare trois constantes : `titre` (le nom d'une roadmap), `totalLecons` (par exemple 20) et une variable `leconsTerminees` initialisée à 0.
2. Affiche une phrase avec un template literal : « Roadmap Laravel : 0 / 20 leçons ».
3. Écris une boucle `for` qui simule la validation de 12 leçons en incrémentant `leconsTerminees`.
4. Calcule le pourcentage : `Math.round(leconsTerminees / totalLecons x 100)` (multiplie par 100 avec l'opérateur de multiplication).
5. Écris une condition `if / else if / else` qui affiche le niveau : moins de 30 % « débutant », moins de 70 % « intermédiaire », sinon « avancé ».
6. Ajoute un ternaire qui affiche « Roadmap terminée » ou « Roadmap en cours ».
7. Simule une saisie utilisateur `const saisie = '15'`, convertis-la avec `Number`, et vérifie qu'elle n'est pas `NaN` avant de l'utiliser comme nombre de leçons.
8. Utilise `??` pour fournir une valeur par défaut de 0 si la saisie est `undefined`.

Pour t'auto-évaluer : peux-tu expliquer, sans regarder ce chapitre, la différence entre `==` et `===`, entre `null` et `undefined`, et entre `||` et `??` ? Si une de ces réponses est floue, relis la section concernée et refais l'étape correspondante.

## Erreurs fréquentes

- **Utiliser `==` au lieu de `===`.** Les conversions implicites produisent des résultats inattendus.
- **Oublier que les saisies sont du texte.** `'2' + '3'` donne `'23'`, pas `5`. Convertis avec `Number`.
- **Utiliser `||` pour une valeur par défaut avec un nombre.** Le zéro est écarté ; préfère `??`.
- **Réaffecter un `const`.** C'est une erreur : utilise `let` si la valeur doit changer.
- **Boucle infinie.** Vérifie que la condition d'arrêt finit par être atteinte.
- **Comparer avec `NaN`.** `x === NaN` est toujours faux ; utilise `Number.isNaN(x)`.
- **Oublier `break` ou `return` dans un `switch`.** Les cas suivants s'exécutent aussi.

## Bonnes pratiques

- `const` par défaut, `let` seulement si nécessaire, jamais `var`.
- Toujours `===` et `!==`.
- Des noms de variables explicites, en camelCase, écrits en français ou en anglais mais de façon cohérente dans tout le projet.
- Utilise les template literals plutôt que la concaténation avec `+`.
- Convertis explicitement les types plutôt que de compter sur les conversions automatiques.
- Teste souvent avec `console.log` : un petit pas, une vérification.

## À retenir

- JavaScript s'exécute dans le navigateur et dans Node.js ; la console est ton laboratoire.
- `const` par défaut, `let` quand la valeur change, `var` à éviter.
- Types de base : nombre, chaîne, booléen, `null`, `undefined` ; `typeof` permet de les inspecter.
- Compare avec `===` ; connais les six valeurs falsy : `false`, `0`, `''`, `null`, `undefined`, `NaN`.
- `if`, `switch`, ternaire et `for`, `while`, `for...of` couvrent l'essentiel du contrôle de flux.
- `??` fournit une valeur par défaut sans écarter le zéro ni la chaîne vide.
- Les saisies de formulaire sont du texte : convertis-les avant de calculer.
