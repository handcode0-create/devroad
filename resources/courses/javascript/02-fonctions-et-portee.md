---
title: Fonctions et portée
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Une fonction est un morceau de code nommé que tu peux appeler autant de fois que tu veux. C'est l'outil de base pour éviter la répétition et organiser un programme. Ce chapitre t'apprend à écrire des fonctions propres, à comprendre où vivent tes variables (la **portée**) et à découvrir l'un des concepts les plus puissants du langage : les **closures**.

À la fin du chapitre, tu seras capable de :

- déclarer une fonction de trois façons différentes et choisir la bonne ;
- utiliser des paramètres par défaut, le reste (`...rest`) et la décomposition de paramètres ;
- distinguer retour de valeur et effet de bord ;
- expliquer la portée globale, de fonction et de bloc ;
- comprendre ce qu'est une closure et à quoi elle sert ;
- passer une fonction en argument (fonction de rappel, ou *callback*).

Prérequis : le chapitre « Fondamentaux JavaScript » (variables, conditions, boucles). Prévois deux heures. Tout se teste dans la console du navigateur ou avec Node.js.

## Déclarer une fonction

Il existe trois écritures. Elles font presque la même chose, mais pas exactement.

```js
// 1. Déclaration de fonction
function additionner(a, b) {
  return a + b;
}

// 2. Expression de fonction
const soustraire = function (a, b) {
  return a - b;
};

// 3. Fonction fléchée
const multiplier = (a, b) => a * b;

console.log(additionner(2, 3)); // 5
console.log(soustraire(5, 2));  // 3
console.log(multiplier(4, 3));  // 12
```

La **fonction fléchée** (`=>`) est la plus courte. Quand le corps tient en une expression, le `return` est implicite. Avec plusieurs lignes, il faut des accolades et un `return` explicite :

```js
const calculerPourcentage = (fait, total) => {
  if (total === 0) return 0;
  return Math.round((fait / total) * 100);
};
```

Une différence importante : les **déclarations** (`function nom() {}`) sont « hissées », c'est-à-dire utilisables avant la ligne où elles apparaissent. Les expressions et les fonctions fléchées stockées dans un `const` ne le sont pas : les appeler avant leur définition provoque une erreur.

> **Astuce** : dans les projets React modernes, on utilise surtout des fonctions fléchées pour les petites fonctions et des déclarations `function` pour les composants et fonctions principales. L'important est de rester cohérent dans ton équipe.

## Paramètres, arguments et valeurs de retour

Les **paramètres** sont les noms déclarés dans la fonction ; les **arguments** sont les valeurs passées à l'appel.

```js
function saluer(prenom, salutation = 'Bonjour') {
  return `${salutation} ${prenom} !`;
}

console.log(saluer('Awa'));            // Bonjour Awa !
console.log(saluer('Kofi', 'Salut'));  // Salut Kofi !
```

`salutation = 'Bonjour'` est un **paramètre par défaut** : il est utilisé quand l'argument est absent ou `undefined`.

### Le paramètre rest

Pour accepter un nombre quelconque d'arguments, utilise `...` devant le dernier paramètre. Tu obtiens un vrai tableau :

```js
function somme(...nombres) {
  let total = 0;
  for (const n of nombres) {
    total += n;
  }
  return total;
}

console.log(somme(1, 2, 3, 4)); // 10
```

### Décomposer un objet en paramètre

Quand une fonction a beaucoup de paramètres, passe un objet et décompose-le directement. L'ordre n'a plus d'importance et le code est lisible à l'appel :

```js
function creerLecon({ titre, minutes = 60, niveau = 'beginner' }) {
  return { titre, minutes, niveau };
}

const lecon = creerLecon({ titre: 'Closures', niveau: 'intermediate' });
console.log(lecon); // { titre: 'Closures', minutes: 60, niveau: 'intermediate' }
```

### Retourner une valeur

`return` termine immédiatement la fonction et renvoie une valeur. Sans `return`, la fonction renvoie `undefined`. Une fonction qui calcule quelque chose doit **retourner** le résultat plutôt que de l'afficher avec `console.log` : ainsi, tu peux le réutiliser.

```js
// Mauvaise idée : le résultat est perdu
function doubleAffiche(n) {
  console.log(n * 2);
}

// Bonne idée : le résultat est réutilisable
function double(n) {
  return n * 2;
}

const resultat = double(21) + 1; // 43
```

Une fonction **pure** retourne toujours la même valeur pour les mêmes arguments et ne modifie rien en dehors d'elle. Elles sont faciles à comprendre et à tester. Une fonction avec **effet de bord** modifie quelque chose d'extérieur (affichage, variable globale, page web). Les deux existent, mais garde la logique de calcul pure autant que possible.

:::quiz
Que retourne une fonction qui n'a pas d'instruction `return` ?
- [ ] 0
- [ ] null
- [x] undefined
- [ ] Une chaîne vide
> Sans `return`, une fonction renvoie `undefined`. C'est pourquoi une fonction qui calcule doit retourner son résultat plutôt que de seulement l'afficher.
:::

## La portée (scope)

La **portée** détermine où une variable est accessible. Il y a trois niveaux à connaître.

```js
const global = 'visible partout';

function exemple() {
  const local = 'visible dans la fonction';

  if (true) {
    const bloc = 'visible dans ce bloc uniquement';
    console.log(global, local, bloc); // tout est accessible
  }

  console.log(bloc); // ReferenceError : bloc is not defined
}
```

- **Portée globale** : les variables déclarées en dehors de toute fonction. Accessibles partout, donc dangereuses (n'importe quel code peut les modifier).
- **Portée de fonction** : les variables déclarées dans une fonction ne sont pas visibles à l'extérieur.
- **Portée de bloc** : avec `let` et `const`, une variable déclarée dans `{ }` (un `if`, un `for`) n'existe que dans ce bloc.

Une fonction peut lire les variables de la portée dans laquelle elle est **définie**. JavaScript cherche d'abord dans la portée courante, puis dans la portée parente, et ainsi de suite jusqu'à la portée globale. C'est la **chaîne de portées** (*scope chain*).

```js
const taxe = 0.18;

function prixTTC(prixHT) {
  return prixHT * (1 + taxe); // taxe est trouvée dans la portée parente
}
```

### Le piège de `var`

Avec `var`, la portée est celle de la fonction, pas du bloc, ce qui crée des bugs subtils :

```js
for (var i = 0; i < 3; i++) {}
console.log(i); // 3 : la variable a « fuité » hors de la boucle

for (let j = 0; j < 3; j++) {}
console.log(j); // ReferenceError : c'est le comportement attendu
```

> **Erreur fréquente** : créer une variable globale par accident en oubliant `const` ou `let` (`total = 5;`). En mode strict (activé par défaut dans les modules), cela déclenche une erreur, ce qui est une bonne chose.

## Les closures

Une **closure** (fermeture) est une fonction qui **se souvient** des variables de la portée où elle a été créée, même après la fin de l'exécution de cette portée. Le concept paraît abstrait, mais l'exemple est simple :

```js
function creerCompteur() {
  let compte = 0;

  return function () {
    compte += 1;
    return compte;
  };
}

const compteurA = creerCompteur();
const compteurB = creerCompteur();

console.log(compteurA()); // 1
console.log(compteurA()); // 2
console.log(compteurA()); // 3
console.log(compteurB()); // 1 : chaque compteur a sa propre variable
```

La fonction `creerCompteur` est terminée depuis longtemps, mais la fonction retournée a gardé l'accès à `compte`. Cette variable n'est visible de nulle part ailleurs : c'est un moyen de créer des données **privées**.

Les closures servent partout : gestionnaires d'événements, `setTimeout`, fonctions de configuration, et les **hooks de React** reposent entièrement dessus. Voici un exemple concret avec DevRoad :

```js
function creerSuiviProgression(total) {
  let terminees = 0;

  return {
    valider() {
      if (terminees < total) terminees++;
      return terminees;
    },
    pourcentage() {
      return Math.round((terminees / total) * 100);
    },
  };
}

const suiviLaravel = creerSuiviProgression(10);
suiviLaravel.valider();
suiviLaravel.valider();
console.log(suiviLaravel.pourcentage()); // 20
```

Personne ne peut modifier `terminees` directement : il faut passer par `valider`. C'est de l'**encapsulation** sans classe.

:::quiz
Qu'est-ce qu'une closure ?
- [ ] Une fonction qui se ferme automatiquement après son appel
- [x] Une fonction qui garde l'accès aux variables de la portée où elle a été créée
- [ ] Une variable globale protégée
- [ ] Un bloc de code sans accolades
> Une closure mémorise son environnement de création. C'est ce qui permet à `compteurA` de conserver sa variable `compte` entre deux appels.
:::

## Les fonctions comme valeurs : les callbacks

En JavaScript, les fonctions sont des **valeurs** comme les autres : tu peux les stocker dans une variable, les passer en argument, les retourner. Une fonction passée à une autre fonction s'appelle un **callback**.

```js
function repeter(fois, action) {
  for (let i = 0; i < fois; i++) {
    action(i);
  }
}

repeter(3, (index) => console.log(`Tour ${index}`));
```

Les fonctions qui reçoivent ou retournent d'autres fonctions s'appellent des fonctions d'**ordre supérieur**. Tu en utiliseras en permanence : `map`, `filter`, `addEventListener`, `setTimeout`.

```js
setTimeout(() => {
  console.log('Affiché après 1 seconde');
}, 1000);
```

### this et les fonctions fléchées

Le mot `this` désigne « l'objet sur lequel la fonction est appelée ». Une fonction classique reçoit son propre `this` selon la façon dont on l'appelle ; une fonction fléchée **n'a pas de `this` propre** et utilise celui de son contexte de définition. Pour l'instant, retiens simplement : dans un callback à l'intérieur d'une méthode, préfère la fonction fléchée pour garder le `this` attendu.

### Récursivité

Une fonction peut s'appeler elle-même. Il faut toujours un **cas d'arrêt**, sinon elle tourne jusqu'à l'erreur « Maximum call stack size exceeded ».

```js
function factorielle(n) {
  if (n <= 1) return 1;          // cas d'arrêt
  return n * factorielle(n - 1); // appel récursif
}

console.log(factorielle(5)); // 120
```

:::quiz
Dans `const mult = (a, b) => a * b;`, que se passe-t-il sans accolades ?
- [ ] Une erreur de syntaxe
- [x] La valeur de l'expression est retournée implicitement
- [ ] La fonction retourne undefined
- [ ] La fonction devient asynchrone
> Une fonction fléchée dont le corps est une seule expression, sans accolades, retourne automatiquement la valeur de cette expression.
:::

## Atelier guidé : une petite boîte à outils pour DevRoad

Compte une heure. Crée un fichier `outils.js`.

1. Écris une fonction `formaterDuree(minutes)` qui retourne « 1 h 30 » pour 90 et « 45 min » pour 45. Utilise `Math.floor`, `%` et un template literal.
2. Transforme-la en fonction fléchée à une seule expression si possible, ou garde un corps avec accolades si tu as besoin de plusieurs lignes.
3. Écris `calculerMoyenne(...notes)` avec un paramètre rest. Gère le cas où aucune note n'est passée (retourne 0).
4. Écris `creerLecon({ titre, minutes = 60, niveau = 'beginner' })` qui retourne un objet, puis teste-la avec et sans valeurs par défaut.
5. Écris `creerCompteur()` avec une closure et vérifie que deux compteurs sont indépendants.
6. Écris `creerSuiviProgression(total)` comme dans le cours, avec `valider()` et `pourcentage()`.
7. Écris une fonction `executerPlusieursFois(n, action)` et passe-lui un callback qui affiche un message.
8. Teste la portée : déclare une variable dans un bloc `if`, essaie de la lire en dehors, observe l'erreur et note le message.

Pour t'auto-évaluer : explique à voix haute pourquoi `compte` n'est pas accessible depuis l'extérieur de `creerCompteur`, et pourquoi pourtant la fonction retournée peut le modifier.

## Erreurs fréquentes

- **Afficher au lieu de retourner.** Une fonction qui fait seulement `console.log` ne peut pas être réutilisée dans un calcul.
- **Oublier `return` dans une fonction à accolades.** Le résultat est `undefined`.
- **Écrire `=> { titre: 'x' }` pour retourner un objet.** Les accolades sont lues comme un bloc ; entoure l'objet de parenthèses : `=> ({ titre: 'x' })`.
- **Appeler une fonction fléchée avant sa définition.** Elle n'est pas hissée.
- **Confondre référence et appel.** `setTimeout(action(), 1000)` appelle `action` tout de suite ; il faut écrire `setTimeout(action, 1000)` pour passer la fonction elle-même.
- **Modifier des variables globales depuis une fonction.** Ça rend le code imprévisible ; passe des arguments et retourne des valeurs.
- **Boucle avec `var` et closure.** Tous les callbacks partagent la même variable ; utilise `let`.

## Bonnes pratiques

- Une fonction fait **une seule chose** et porte un nom qui commence par un verbe : `calculerPourcentage`, `creerLecon`.
- Garde-les courtes : si tu as besoin de commenter plusieurs blocs, découpe.
- Préfère les fonctions pures pour les calculs, et isole les effets de bord (affichage, requêtes) à un endroit précis.
- Au-delà de trois paramètres, passe un objet et décompose-le.
- Donne des valeurs par défaut explicites plutôt que de tester `undefined` à la main.
- Limite la portée : déclare les variables au plus près de leur usage, avec `const`.

## À retenir

- Trois écritures de fonctions : déclaration, expression, fonction fléchée.
- Paramètres par défaut, `...rest` et décomposition d'objet rendent les signatures expressives.
- Une fonction qui calcule doit **retourner** sa valeur ; sans `return`, c'est `undefined`.
- La portée est globale, de fonction ou de bloc ; `let` et `const` respectent les blocs.
- Une **closure** garde l'accès à l'environnement où elle a été créée : base des compteurs, des modules et des hooks React.
- Les fonctions sont des valeurs : on peut les passer en callback et les retourner.
- Une récursion a toujours besoin d'un cas d'arrêt.
