---
title: Objets, tableaux et méthodes
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application manipule des données : des utilisateurs, des roadmaps, des leçons, des commandes. En JavaScript, ces données vivent dans des **objets** (une fiche avec des champs) et des **tableaux** (une liste ordonnée). Savoir les transformer avec aisance, c'est la compétence la plus utilisée au quotidien, surtout avec React.

À la fin du chapitre, tu seras capable de :

- créer, lire et modifier des objets et des tableaux ;
- utiliser la décomposition (*destructuring*) et l'opérateur de propagation (*spread*) ;
- transformer des listes avec `map`, `filter`, `find`, `some`, `every` et `reduce` ;
- trier un tableau sans le corrompre ;
- copier des données sans modifier l'original (immutabilité) ;
- lire et écrire du JSON ;
- utiliser l'accès sécurisé `?.` pour les données incomplètes.

Prérequis : les chapitres « Fondamentaux JavaScript » et « Fonctions et portée ». Prévois deux heures et demie. Teste chaque exemple dans la console.

## Les objets

Un objet regroupe des valeurs sous des noms appelés **propriétés**.

```js
const roadmap = {
  id: 1,
  titre: 'Laravel',
  niveau: 'intermediate',
  lecons: 12,
  tags: ['php', 'backend'],
  auteur: { nom: 'Awa', pays: 'CI' },
};

console.log(roadmap.titre);          // 'Laravel'
console.log(roadmap['niveau']);      // 'intermediate'
console.log(roadmap.auteur.nom);     // 'Awa'

roadmap.lecons = 14;                 // modification (permise malgré const)
roadmap.publie = true;               // ajout d'une propriété
delete roadmap.tags;                 // suppression
```

La notation avec crochets sert quand le nom de la propriété est dans une variable :

```js
const champ = 'titre';
console.log(roadmap[champ]); // 'Laravel'
```

Un objet peut aussi contenir des fonctions, appelées **méthodes** :

```js
const utilisateur = {
  prenom: 'Kofi',
  saluer() {
    return `Bonjour, je suis ${this.prenom}`;
  },
};
```

### Accès sécurisé et valeur par défaut

Lire une propriété d'un objet qui n'existe pas déclenche une erreur. L'opérateur `?.` s'arrête proprement et retourne `undefined` :

```js
const eleve = { nom: 'Awa' };

console.log(eleve.adresse.ville);        // TypeError !
console.log(eleve.adresse?.ville);       // undefined
console.log(eleve.adresse?.ville ?? 'Inconnue'); // 'Inconnue'
```

### Décomposition et propagation

La **décomposition** extrait des propriétés en variables ; le **spread** `...` copie ou fusionne.

```js
const { titre, niveau } = roadmap;
const { lecons: nombreLecons = 0 } = roadmap;   // renommage + défaut

const copie = { ...roadmap, titre: 'Laravel 11' };  // copie avec modification
const fusion = { ...parametresParDefaut, ...parametresUtilisateur };
```

Le spread ne copie qu'un niveau (copie **superficielle**) : les objets imbriqués restent partagés. Pour une copie complète, utilise `structuredClone(objet)`.

## Les tableaux

Un tableau est une liste ordonnée, indexée à partir de **0**.

```js
const technos = ['HTML', 'CSS', 'JavaScript'];

console.log(technos[0]);        // 'HTML'
console.log(technos.length);    // 3
console.log(technos.at(-1));    // 'JavaScript' (dernier élément)

technos.push('React');          // ajoute à la fin
technos.pop();                  // retire le dernier
technos.unshift('Web');         // ajoute au début
technos.shift();                // retire le premier
console.log(technos.includes('CSS')); // true
console.log(technos.indexOf('CSS'));  // 1
```

Décomposition et spread fonctionnent aussi :

```js
const [premier, deuxieme, ...reste] = ['a', 'b', 'c', 'd'];
const toutes = [...technos, 'Node.js'];
```

> **Attention** : `push`, `pop`, `splice`, `sort` et `reverse` **modifient** le tableau d'origine. En React, on évite ces méthodes sur les données d'état et on crée un nouveau tableau à la place.

## Les méthodes qui transforment

C'est le cœur du chapitre. Ces méthodes remplacent la plupart des boucles `for` et **ne modifient pas** le tableau d'origine.

Prenons un jeu de données DevRoad :

```js
const lecons = [
  { id: 1, titre: 'Variables', minutes: 90, niveau: 'beginner', terminee: true },
  { id: 2, titre: 'Fonctions', minutes: 120, niveau: 'beginner', terminee: true },
  { id: 3, titre: 'Tableaux', minutes: 150, niveau: 'intermediate', terminee: false },
  { id: 4, titre: 'Async', minutes: 150, niveau: 'intermediate', terminee: false },
];
```

### map : transformer chaque élément

`map` retourne un nouveau tableau de même longueur.

```js
const titres = lecons.map((lecon) => lecon.titre);
// ['Variables', 'Fonctions', 'Tableaux', 'Async']

const avecHeures = lecons.map((l) => ({ ...l, heures: l.minutes / 60 }));
```

### filter : garder certains éléments

```js
const aFaire = lecons.filter((l) => !l.terminee);
const debutant = lecons.filter((l) => l.niveau === 'beginner');
```

### find et findIndex : trouver un élément

```js
const lecon3 = lecons.find((l) => l.id === 3);      // l'objet, ou undefined
const position = lecons.findIndex((l) => l.id === 3); // 2, ou -1
```

### some et every : tester une condition

```js
lecons.some((l) => l.terminee);   // true  : au moins une
lecons.every((l) => l.terminee);  // false : toutes ?
```

### reduce : réduire à une seule valeur

`reduce` accumule un résultat. Il reçoit une fonction `(accumulateur, element)` et une valeur de départ.

```js
const totalMinutes = lecons.reduce((total, l) => total + l.minutes, 0);
// 510

const parNiveau = lecons.reduce((groupes, l) => {
  groupes[l.niveau] = (groupes[l.niveau] ?? 0) + 1;
  return groupes;
}, {});
// { beginner: 2, intermediate: 2 }
```

Les méthodes se **chaînent** :

```js
const minutesRestantes = lecons
  .filter((l) => !l.terminee)
  .map((l) => l.minutes)
  .reduce((a, b) => a + b, 0);
// 300
```

:::quiz
Quelle méthode retourne un nouveau tableau contenant uniquement les éléments qui respectent une condition ?
- [ ] map
- [x] filter
- [ ] find
- [ ] reduce
> `filter` garde les éléments pour lesquels la fonction retourne `true`. `map` transforme chaque élément, `find` n'en retourne qu'un seul et `reduce` produit une valeur unique.
:::

### sort : trier

`sort` modifie le tableau sur place et, sans argument, trie par ordre alphabétique (même les nombres !). Fournis toujours une fonction de comparaison et travaille sur une copie.

```js
const nombres = [10, 9, 1, 100];
console.log([...nombres].sort());              // [1, 10, 100, 9] : faux !
console.log([...nombres].sort((a, b) => a - b)); // [1, 9, 10, 100]

const parDuree = [...lecons].sort((a, b) => b.minutes - a.minutes);
const parTitre = [...lecons].sort((a, b) => a.titre.localeCompare(b.titre, 'fr'));
```

### Autres méthodes utiles

- `slice(debut, fin)` : extrait une portion sans modifier ;
- `concat` ou le spread : assemble des tableaux ;
- `flat` et `flatMap` : aplatissent des tableaux imbriqués ;
- `Array.from({ length: 5 }, (_, i) => i)` : crée `[0, 1, 2, 3, 4]` ;
- `join(', ')` : transforme un tableau en chaîne.

:::quiz
Pourquoi écrit-on `[...lecons].sort(...)` plutôt que `lecons.sort(...)` ?
- [ ] Parce que sort n'existe pas sur les tableaux d'objets
- [ ] Parce que le spread accélère le tri
- [x] Parce que sort modifie le tableau d'origine et qu'on veut le préserver
- [ ] Parce que sort retourne toujours undefined
> `sort` trie sur place. En copiant d'abord avec le spread, on évite de modifier les données d'origine, ce qui est indispensable avec l'état de React.
:::

## Parcourir un objet

Pour traiter les propriétés d'un objet, transforme-le en tableau :

```js
const scores = { html: 90, css: 75, js: 60 };

console.log(Object.keys(scores));    // ['html', 'css', 'js']
console.log(Object.values(scores));  // [90, 75, 60]
console.log(Object.entries(scores)); // [['html', 90], ['css', 75], ['js', 60]]

for (const [matiere, note] of Object.entries(scores)) {
  console.log(`${matiere} : ${note}`);
}

const doubles = Object.fromEntries(
  Object.entries(scores).map(([cle, valeur]) => [cle, valeur * 2])
);
```

## L'immutabilité : ne pas modifier, remplacer

En React et dans beaucoup de bibliothèques, on ne modifie jamais une donnée en place : on en crée une **nouvelle version**. Voici les quatre opérations classiques sur un tableau d'objets :

```js
// Ajouter
const apresAjout = [...lecons, { id: 5, titre: 'DOM', minutes: 90 }];

// Supprimer
const apresSuppression = lecons.filter((l) => l.id !== 2);

// Modifier un élément
const apresModif = lecons.map((l) =>
  l.id === 3 ? { ...l, terminee: true } : l
);

// Modifier une propriété imbriquée
const mis = { ...roadmap, auteur: { ...roadmap.auteur, pays: 'SN' } };
```

Cette habitude rend les changements prévisibles et permet à React de détecter facilement ce qui a changé.

## Le format JSON

JSON (*JavaScript Object Notation*) est le format texte standard pour échanger des données entre un front-end et une API, comme Laravel. Il ressemble aux objets JavaScript, mais les clés sont toujours entre guillemets doubles et il n'accepte ni fonctions, ni `undefined`, ni commentaires.

```js
const texte = JSON.stringify({ titre: 'Laravel', lecons: 12 });
// '{"titre":"Laravel","lecons":12}'

const objet = JSON.parse(texte);
console.log(objet.titre); // 'Laravel'
```

`JSON.parse` lève une erreur sur un texte invalide : entoure-le d'un `try / catch` quand la source n'est pas sûre.

```js
function lireSecurise(texte) {
  try {
    return JSON.parse(texte);
  } catch {
    return null;
  }
}
```

## Atelier guidé : le tableau de bord de progression

Compte une heure et demie. Crée un fichier `lecons.js` avec le jeu de données `lecons` de ce chapitre, complété par 4 ou 5 leçons supplémentaires de ton choix.

1. Affiche la liste des titres avec `map`, puis joins-les avec `join(' / ')`.
2. Filtre les leçons non terminées et affiche leur nombre.
3. Utilise `find` pour retrouver la leçon d'id 3, et affiche son titre avec l'accès sécurisé `?.` (teste aussi avec un id inexistant).
4. Calcule avec `reduce` le total des minutes, puis le total des minutes restantes en chaînant `filter`, `map` et `reduce`.
5. Calcule le pourcentage de leçons terminées avec `filter` et `length`.
6. Trie les leçons par durée décroissante **sans modifier** le tableau d'origine ; vérifie en affichant l'original.
7. Écris trois fonctions immuables : `ajouterLecon(liste, lecon)`, `supprimerLecon(liste, id)`, `basculerTerminee(liste, id)`.
8. Regroupe les leçons par niveau avec `reduce`, puis affiche le résultat avec `Object.entries`.
9. Sérialise le résultat avec `JSON.stringify(objet, null, 2)` pour l'afficher joliment, puis relis-le avec `JSON.parse`.

Pour t'auto-évaluer : sans regarder, écris l'expression qui change `terminee` à `true` pour la leçon d'id 4 sans muter le tableau d'origine. Si tu y arrives, tu maîtrises l'immutabilité.

## Erreurs fréquentes

- **Oublier la valeur initiale de `reduce`.** Sans elle, le premier élément sert d'accumulateur, ce qui casse sur un tableau vide.
- **Oublier `return` dans le callback d'un `map` avec accolades.** Tu obtiens un tableau de `undefined`.
- **Retourner un objet littéral sans parenthèses dans une flèche.** Écris `=> ({ ...l })`.
- **Utiliser `sort()` sur des nombres sans comparateur.** Le tri est alphabétique.
- **Modifier un tableau reçu en paramètre.** Tu corromps les données de l'appelant ; copie d'abord.
- **Croire que le spread copie en profondeur.** Les objets imbriqués restent partagés ; utilise `structuredClone`.
- **Comparer deux objets avec `===`.** `{} === {}` est faux : on compare des références, pas des contenus.
- **Accéder à une propriété d'un `undefined`.** Utilise `?.` et `??`.

## Bonnes pratiques

- Préfère `map`, `filter` et `reduce` aux boucles `for` quand tu transformes des données.
- Ne mute jamais les données reçues : copie avec le spread, remplace avec `map` ou `filter`.
- Donne des noms parlants aux paramètres de callbacks : `lecon` plutôt que `x`.
- Chaîne les méthodes en les alignant sur plusieurs lignes pour qu'elles se lisent comme une phrase.
- Utilise `find` quand tu attends un seul résultat, `filter` quand tu en attends plusieurs.
- Valide les données externes (JSON, API) : elles ne ressemblent pas toujours à ce que tu espères.

## À retenir

- Un objet regroupe des propriétés nommées ; un tableau est une liste indexée à partir de 0.
- La décomposition et le spread permettent d'extraire, copier et fusionner simplement.
- `map` transforme, `filter` sélectionne, `find` trouve, `some` et `every` testent, `reduce` agrège.
- `sort`, `push`, `splice` et `reverse` modifient l'original ; copie avant de les utiliser.
- Pour modifier des données de façon immuable : spread, `map` et `filter` créent de nouvelles versions.
- `Object.entries` et `Object.fromEntries` font le pont entre objets et tableaux.
- `JSON.stringify` et `JSON.parse` assurent l'échange de données ; `?.` et `??` protègent des données incomplètes.
