---
title: Découvrir React
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

React est aujourd'hui la bibliothèque la plus utilisée pour construire des interfaces web. Ce chapitre répond aux questions que tout débutant se pose : qu'est-ce que c'est, à quoi ça sert, et en quoi penser « à la React » change la façon de construire une page.

À la fin du chapitre, tu seras capable de :

- expliquer le problème que React résout par rapport au JavaScript « à la main » ;
- définir ce qu'est un **composant** et pourquoi tout est découpé ainsi ;
- lire et écrire du **JSX**, la syntaxe de React ;
- expliquer l'idée centrale : l'interface est **le résultat d'un état** ;
- situer React dans l'écosystème (Vite, Next.js, Inertia).

Prérequis : savoir lire du HTML et du JavaScript de base (variables, fonctions, tableaux). Prévois deux heures. Tu peux tout tester sans rien installer, dans l'éditeur en ligne de react.dev (rubrique « Playground ») ou dans CodeSandbox.

## Le problème : modifier la page à la main

Imagine un bouton « J'aime » qui affiche le nombre de likes. En JavaScript classique, tu dois **dire au navigateur chaque étape** :

```js
const bouton = document.querySelector('#like');
const compteur = document.querySelector('#compteur');
let likes = 0;

bouton.addEventListener('click', () => {
  likes += 1;
  compteur.textContent = likes;                 // 1. mettre à jour le texte
  bouton.classList.add('actif');                // 2. changer le style
  document.title = `${likes} likes`;            // 3. changer le titre
});
```

Ça marche pour un bouton. Mais une vraie application a des dizaines d'éléments qui dépendent des mêmes données : le compteur, la liste, le badge dans l'en-tête, le message « aucun résultat ». À chaque changement, tu dois penser à **tous** les endroits à modifier. Tu en oublies un, et la page affiche des informations contradictoires. C'est la source de nombreux bugs.

React propose une autre approche, dite **déclarative** : tu ne décris plus *comment* modifier la page, tu décris *à quoi elle ressemble* pour un état donné. React se charge de la mettre à jour.

> **À retenir** : en JavaScript classique, tu donnes des **ordres** (« change ce texte, ajoute cette classe »). Avec React, tu donnes une **description** (« voici l'interface pour ces données »).

:::quiz
Quelle est la différence essentielle entre l'approche de React et le JavaScript manipulant le DOM à la main ?
- [ ] React est plus rapide car il n'utilise pas de navigateur
- [x] React décrit l'interface pour un état donné et se charge de mettre la page à jour
- [ ] React remplace HTML et CSS
- [ ] React exécute le code côté serveur uniquement
> Avec React, on décrit le résultat attendu à partir des données (approche déclarative) au lieu de donner chaque étape de modification de la page.
:::

## L'idée centrale : l'interface est une fonction de l'état

Retiens cette phrase, elle résume tout React :

**interface = fonction(état)**

L'**état** (*state*) est l'ensemble des données qui changent au fil du temps : le nombre de likes, le texte saisi, la liste des tâches. Pour un état donné, il n'existe qu'une seule interface possible. Quand l'état change, React recalcule l'interface correspondante et met à jour ce qui est nécessaire dans la page.

Pense à une recette de cuisine : tu ne dis pas « casse un œuf, puis remue 3 fois ». Tu dis « pour un gâteau avec ces ingrédients, voilà le résultat ». Si les ingrédients changent, le résultat suit.

Cette façon de penser a un avantage énorme : **il n'y a plus qu'une source de vérité**. Le compteur affiché dans l'en-tête et celui de la liste lisent la même donnée. Ils ne peuvent plus se contredire.

## Les composants : des briques réutilisables

Une interface React est construite avec des **composants**. Un composant est une **fonction JavaScript qui retourne de l'interface**. Le nom commence toujours par une majuscule.

```jsx
function Bienvenue() {
  return <h1>Bienvenue sur DevRoad</h1>;
}
```

Un composant se réutilise comme une balise HTML :

```jsx
function App() {
  return (
    <div>
      <Bienvenue />
      <Bienvenue />
    </div>
  );
}
```

L'intérêt apparaît quand l'application grandit. Au lieu d'une page géante de 2 000 lignes, tu as de petits composants qui se composent :

```text
App
├── Header
│   ├── Logo
│   └── Menu
├── ListeRoadmaps
│   └── CarteRoadmap (répété pour chaque roadmap)
└── Footer
```

Chaque composant a **une seule responsabilité** et peut être modifié, testé et réutilisé indépendamment. C'est exactement le principe du chapitre sur la structure d'un projet Laravel, appliqué à l'interface.

> **Astuce** : quand tu hésites à découper, demande-toi : « est-ce que je vais réutiliser ce morceau ? » ou « est-ce que ce bloc est devenu difficile à lire ? ». Si oui, c'est un composant.

## Le JSX : du HTML dans JavaScript

Le code qui ressemble à du HTML dans les exemples précédents s'appelle **JSX**. Ce n'est pas du vrai HTML, c'est une syntaxe qui est transformée en JavaScript. Elle ressemble à du HTML, avec quelques règles à connaître.

### Règle 1 : un seul élément racine

Un composant retourne **un seul** élément parent. Si tu as plusieurs éléments côte à côte, enveloppe-les dans une `div` ou dans un fragment `<>...</>` qui n'ajoute rien à la page :

```jsx
function Titre() {
  return (
    <>
      <h1>DevRoad</h1>
      <p>Ton compagnon d'apprentissage</p>
    </>
  );
}
```

### Règle 2 : les accolades pour insérer du JavaScript

Entre accolades `{ }`, tu peux écrire n'importe quelle expression JavaScript :

```jsx
function Salutation() {
  const prenom = 'Awa';
  const heure = new Date().getHours();

  return (
    <p>
      Bonjour {prenom}, il est {heure} h. Dans 2 heures : {heure + 2} h.
    </p>
  );
}
```

### Règle 3 : quelques attributs sont renommés

Comme JSX est du JavaScript, certains mots sont déjà pris. `class` devient `className`, et `for` devient `htmlFor` :

```jsx
<label htmlFor="email" className="champ">E-mail</label>
<input id="email" type="email" />
```

Les attributs composés s'écrivent en camelCase : `onClick`, `tabIndex`, `maxLength`.

### Règle 4 : toutes les balises doivent être fermées

En HTML, `<input>` ou `<br>` peuvent rester ouverts. En JSX, il faut les fermer : `<input />`, `<br />`, `<img src="..." alt="..." />`.

:::quiz
Quel est le bon code JSX pour afficher la variable `prenom` dans un paragraphe ?
- [ ] <p>Bonjour prenom</p>
- [ ] <p>Bonjour ${prenom}</p>
- [x] <p>Bonjour {prenom}</p>
- [ ] <p>Bonjour [prenom]</p>
> Les accolades permettent d'insérer une expression JavaScript dans le JSX. Sans elles, « prenom » s'afficherait comme du texte brut.
:::

## Afficher une liste

Dans une application, on affiche souvent des listes de données. On utilise `map`, la méthode des tableaux, pour transformer chaque élément en composant :

```jsx
const technologies = ['Laravel', 'React', 'Docker'];

function Liste() {
  return (
    <ul>
      {technologies.map((nom) => (
        <li key={nom}>{nom}</li>
      ))}
    </ul>
  );
}
```

Remarque l'attribut **`key`**. React en a besoin pour savoir quel élément correspond à quelle donnée quand la liste change (un élément ajouté, supprimé, déplacé). La clé doit être **unique et stable** : un identifiant venant de ta base de données est idéal. Évite d'utiliser l'index du tableau quand l'ordre peut changer.

## Afficher selon une condition

L'interface change selon les données. Trois écritures courantes :

```jsx
function Statut({ termine }) {
  // 1. Une condition avec l'opérateur ternaire
  return <p>{termine ? 'Terminé ✔' : 'En cours…'}</p>;
}

function Alerte({ message }) {
  // 2. Afficher quelque chose seulement si la condition est vraie
  return <div>{message && <p role="alert">{message}</p>}</div>;
}

function Panier({ articles }) {
  // 3. Sortir tôt du composant
  if (articles.length === 0) {
    return <p>Ton panier est vide.</p>;
  }
  return <p>{articles.length} article(s)</p>;
}
```

> **Attention** : `{nombre && <p>…</p>}` affiche `0` quand `nombre` vaut 0, car `0` est « faux » mais s'affiche tout de même. Pour un nombre, écris `{nombre > 0 && …}`.

## Où se situe React dans l'écosystème ?

React est une **bibliothèque** : elle s'occupe uniquement de l'interface. Pour construire une application complète, on l'associe à d'autres outils :

| Outil | Rôle |
| --- | --- |
| **Vite** | Lance un projet React en développement et le compile pour la production |
| **React Router** | Gère la navigation entre pages d'une application React seule |
| **Next.js** | Framework complet bâti sur React (routage, rendu serveur) |
| **Inertia** | Relie React à un back-end comme Laravel, sans API séparée |
| **Tailwind CSS** | Styles par classes utilitaires |

DevRoad, par exemple, utilise **React** pour l'interface, **Inertia** pour le lien avec Laravel, **Vite** pour la compilation et **Tailwind** pour les styles. Tu retrouveras chacune de ces briques dans les chapitres suivants.

## Atelier guidé : ta première interface

Compte une heure. Ouvre le Playground sur react.dev ou un nouveau bac à sable CodeSandbox avec le modèle React.

1. Remplace le contenu de `App.js` par un composant `Bienvenue` qui affiche « Bienvenue sur DevRoad » dans un `h1`.
2. Ajoute une constante `prenom` et affiche « Bonjour, {prenom} » dans un paragraphe.
3. Crée un composant `CarteTechno` qui affiche un nom de technologie dans un `div` avec une classe `carte`.
4. Dans `App`, utilise `CarteTechno` trois fois, avec trois noms différents codés en dur.
5. Remplace ces trois lignes par un tableau de technologies et un `map` avec une `key`.
6. Ajoute une constante `termine` et affiche « Terminé ✔ » ou « À faire » avec un ternaire.
7. Si le tableau est vide, affiche « Aucune technologie » grâce à un retour anticipé.
8. Volontairement, retire la `key` et observe l'avertissement dans la console du navigateur.

Pour t'auto-évaluer : explique à voix haute la différence entre un composant et une fonction JavaScript ordinaire, et pourquoi on parle d'approche déclarative.

## Erreurs fréquentes

- **Un composant sans majuscule.** `function carte()` est traité comme une balise HTML et non comme un composant : écris `Carte`.
- **Retourner plusieurs éléments sans parent.** Enveloppe-les dans un fragment `<>…</>`.
- **Utiliser `class` au lieu de `className`.** Une erreur ou un avertissement apparaît.
- **Oublier la `key` dans une liste.** Cela provoque des avertissements et des comportements étranges.
- **Écrire `{nombre && …}` avec un nombre.** Le zéro s'affiche à l'écran.
- **Oublier de fermer une balise.** `<input>` doit s'écrire `<input />`.

## Bonnes pratiques

- Un composant, une responsabilité, un nom explicite en PascalCase.
- Garde les composants courts : en dessous d'une cinquantaine de lignes, c'est confortable.
- Donne des clés stables aux éléments d'une liste.
- Pense « état d'abord » : quelles données changent, et à quoi ressemble l'interface pour chacune ?
- Utilise des balises HTML sémantiques (`button`, `nav`, `ul`) plutôt que des `div` partout.

## À retenir

- React est une bibliothèque pour construire des interfaces de façon **déclarative**.
- Interface = fonction(état) : l'état est la seule source de vérité.
- Un composant est une fonction qui retourne du JSX ; son nom commence par une majuscule.
- Le JSX ressemble à du HTML : un seul parent, des accolades pour le JavaScript, `className` et `htmlFor`.
- Les listes s'affichent avec `map` et une `key` unique ; les conditions avec `?:`, `&&` ou un retour anticipé.
- React se combine avec Vite, Next.js, Inertia et Tailwind pour faire une application complète.
