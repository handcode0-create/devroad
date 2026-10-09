---
title: State et événements
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Jusqu'ici, tes composants affichent des données fixes. Une vraie application réagit : on clique, on tape, on coche, et l'écran change. Pour cela, React a besoin de **mémoriser** des valeurs entre deux affichages. Cette mémoire s'appelle le **state** (l'état), et on la manipule avec le hook `useState`.

À la fin du chapitre, tu seras capable de :

- gérer des événements (`onClick`, `onChange`, `onSubmit`) ;
- déclarer et mettre à jour un état avec `useState` ;
- expliquer pourquoi une variable classique ne suffit pas ;
- mettre à jour des tableaux et des objets **sans les muter** ;
- construire un **formulaire contrôlé** ;
- « remonter » l'état dans le parent commun lorsque deux composants le partagent.

Prérequis : le chapitre « Composants et props ». Prévois deux heures et demie.

## Réagir aux événements

En React, on attache un gestionnaire d'événement directement dans le JSX, avec un attribut en camelCase :

```jsx
function BoutonSalut() {
  function handleClick() {
    alert('Bienvenue sur DevRoad !');
  }
  return <button onClick={handleClick}>Dis bonjour</button>;
}
```

Tu passes **la fonction**, tu ne l'appelles pas (pas de parenthèses après `handleClick`). Les événements courants sont `onClick`, `onChange` (champ de saisie), `onSubmit` (formulaire), `onKeyDown`, `onFocus`, `onBlur`.

Le gestionnaire reçoit un **objet événement** qui décrit ce qui s'est passé :

```jsx
function Champ() {
  function handleChange(event) {
    console.log(event.target.value);   // le texte actuellement saisi
  }
  return <input onChange={handleChange} />;
}
```

Pour un formulaire, on empêche le rechargement de la page avec `event.preventDefault()` :

```jsx
function handleSubmit(event) {
  event.preventDefault();
  // traiter les données ici
}
```

## Pourquoi une variable ne suffit pas

Essayons un compteur de chapitres terminés avec une simple variable :

```jsx
function Compteur() {
  let termines = 0;

  function ajouter() {
    termines = termines + 1;
    console.log(termines);   // affiche 1, 2, 3...
  }

  return <button onClick={ajouter}>Terminés : {termines}</button>;
}
```

Clique : la console affiche 1, 2, 3, mais **l'écran reste à 0**. Deux raisons :

1. La variable locale est **recréée à chaque appel** de la fonction composant, donc elle repart de zéro ;
2. Modifier une variable ne **déclenche pas** de nouvel affichage. React ne sait pas que quelque chose a changé.

Il nous faut un mécanisme qui fait les deux : **conserver** la valeur entre les affichages et **demander** à React de ré-afficher le composant.

## useState : la mémoire d'un composant

`useState` est un **hook**, une fonction spéciale de React qui commence par `use`. On l'importe et on l'appelle au début du composant :

```jsx
import { useState } from 'react';

function Compteur() {
  const [termines, setTermines] = useState(0);

  return (
    <button onClick={() => setTermines(termines + 1)}>
      Terminés : {termines}
    </button>
  );
}
```

`useState(0)` retourne un tableau de deux éléments, que l'on déstructure :

- `termines` : la **valeur actuelle** de l'état (initialement `0`) ;
- `setTermines` : la **fonction de mise à jour**.

Quand tu appelles `setTermines(1)`, React enregistre la nouvelle valeur puis **ré-exécute** le composant, qui cette fois lit `1`. Le cycle est toujours le même :

1. un événement se produit (clic) ;
2. un setter est appelé ;
3. React ré-affiche le composant avec la nouvelle valeur ;
4. React met à jour le DOM uniquement là où c'est nécessaire.

> **À retenir** : l'état d'un composant est **privé** et propre à chaque instance. Deux `<Compteur />` côte à côte ont chacun leur propre valeur.

### Les règles des hooks

- Appelle les hooks **uniquement au niveau supérieur** du composant, jamais dans une condition, une boucle ou une fonction imbriquée ;
- Appelle-les **uniquement depuis un composant** ou un autre hook (fonction commençant par `use`).

React identifie chaque état par son **ordre d'appel**. Si l'ordre change d'un rendu à l'autre, tout se décale.

:::quiz
Pourquoi une variable déclarée avec `let` dans un composant ne permet-elle pas de faire un compteur ?
- [ ] Parce que `let` est interdit dans React
- [x] Parce qu'elle est recréée à chaque rendu et que la modifier ne déclenche pas de nouvel affichage
- [ ] Parce qu'elle est partagée entre tous les composants
- [ ] Parce que React ne sait pas lire les nombres
> Une variable locale est réinitialisée à chaque rendu et React n'est pas prévenu de sa modification. `useState` conserve la valeur et déclenche le nouveau rendu.
:::

## L'état est un instantané

Un point qui surprend tout le monde. Observe :

```jsx
function Triple() {
  const [n, setN] = useState(0);

  function handleClick() {
    setN(n + 1);
    setN(n + 1);
    setN(n + 1);
  }
  return <button onClick={handleClick}>{n}</button>;
}
```

Un clic fait passer `n` de 0 à **1**, pas à 3. Pendant ce rendu, `n` vaut 0 pour toute la fonction : c'est un instantané. Les trois appels demandent « mets 0 + 1 ». Si la nouvelle valeur dépend de la précédente, passe une **fonction de mise à jour** :

```jsx
setN((precedent) => precedent + 1);
setN((precedent) => precedent + 1);
setN((precedent) => precedent + 1);   // n passe à 3
```

React enchaîne les fonctions en passant chaque résultat à la suivante. Prends l'habitude de cette forme dès que la nouvelle valeur se calcule à partir de l'ancienne.

## Ne jamais muter l'état

Pour les nombres et les chaînes, pas de piège. Pour les **tableaux** et **objets**, il faut toujours créer une **copie** modifiée. React compare l'ancienne et la nouvelle valeur par référence : si tu modifies l'objet existant, la référence est identique et React ne voit aucun changement.

Imaginons la liste des chapitres suivis dans DevRoad :

```jsx
const [chapitres, setChapitres] = useState([
  { id: 1, titre: 'Découvrir React', fait: false },
  { id: 2, titre: 'Créer un projet', fait: false },
]);
```

Les quatre opérations de base, sans mutation :

```jsx
// Ajouter
setChapitres([...chapitres, { id: 3, titre: 'Props', fait: false }]);

// Supprimer
setChapitres(chapitres.filter((c) => c.id !== 2));

// Modifier un élément
setChapitres(
  chapitres.map((c) => (c.id === 1 ? { ...c, fait: true } : c))
);

// Trier une copie
setChapitres([...chapitres].sort((a, b) => a.titre.localeCompare(b.titre)));
```

Pour un objet, on utilise la décomposition : `setProfil({ ...profil, ville: 'Abidjan' })` copie toutes les propriétés puis écrase `ville`. Retiens la liste des méthodes **sûres** (`map`, `filter`, `slice`, `concat`, `[...]`) et celles à **éviter** sur l'état (`push`, `pop`, `splice`, `sort`, `reverse`, et l'affectation `obj.cle = valeur`).

> **Erreur fréquente** : `chapitres.push(x); setChapitres(chapitres);` ne fonctionne pas. Le tableau est le même, React considère qu'il n'a pas changé.

:::quiz
Quelle instruction ajoute correctement un élément `x` au tableau d'état `liste` ?
- [ ] `liste.push(x); setListe(liste);`
- [ ] `liste[liste.length] = x;`
- [x] `setListe([...liste, x]);`
- [ ] `setListe(liste.push(x));`
> Il faut fournir un nouveau tableau. `push` modifie le tableau existant (la référence ne change pas) et retourne un nombre, pas un tableau.
:::

## Les formulaires contrôlés

Dans un formulaire **contrôlé**, c'est React (et non le navigateur) qui détient la valeur du champ. On lie la valeur à un état et on la met à jour à chaque frappe :

```jsx
function AjoutNote() {
  const [texte, setTexte] = useState('');
  const [notes, setNotes] = useState([]);

  function handleSubmit(event) {
    event.preventDefault();
    const propre = texte.trim();
    if (propre === '') return;
    setNotes([...notes, { id: Date.now(), texte: propre }]);
    setTexte('');
  }

  return (
    <form onSubmit={handleSubmit}>
      <label htmlFor="note">Ma note</label>
      <input
        id="note"
        value={texte}
        onChange={(e) => setTexte(e.target.value)}
      />
      <button type="submit" disabled={texte.trim() === ''}>Ajouter</button>

      <ul>
        {notes.map((n) => <li key={n.id}>{n.texte}</li>)}
      </ul>
    </form>
  );
}
```

Avantages : tu peux valider en direct, désactiver le bouton, vider le champ après envoi. Pour plusieurs champs, utilise un objet d'état et un nom de champ dynamique :

```jsx
const [form, setForm] = useState({ nom: '', email: '' });

function handleChange(e) {
  setForm({ ...form, [e.target.name]: e.target.value });
}
```

Chaque `input` possède alors un attribut `name` correspondant à la clé. Pour une case à cocher, on lit `e.target.checked` et on utilise la prop `checked`.

## Remonter l'état (lifting state up)

Que faire quand **deux composants** doivent partager la même donnée ? Par exemple, un champ de recherche et une liste filtrée. Chacun ne peut pas avoir son propre état : ils se désynchroniseraient. La solution : placer l'état dans leur **plus proche parent commun** et leur passer la valeur et le setter en props.

```jsx
function PageRoadmaps({ roadmaps }) {
  const [recherche, setRecherche] = useState('');

  const visibles = roadmaps.filter((r) =>
    r.titre.toLowerCase().includes(recherche.toLowerCase())
  );

  return (
    <>
      <ChampRecherche valeur={recherche} onChange={setRecherche} />
      <ListeRoadmaps roadmaps={visibles} />
    </>
  );
}

function ChampRecherche({ valeur, onChange }) {
  return (
    <input
      type="search"
      placeholder="Rechercher une roadmap"
      value={valeur}
      onChange={(e) => onChange(e.target.value)}
    />
  );
}
```

Remarque un point essentiel : `visibles` n'est **pas** un état. C'est une valeur **dérivée** calculée à chaque rendu à partir de `roadmaps` et `recherche`. Règle d'or : ne stocke dans l'état que le **minimum** ; tout ce qui peut se calculer à partir d'autre chose se calcule.

> **Astuce** : pour décider où placer l'état, cherche tous les composants qui l'utilisent, puis remonte jusqu'à leur ancêtre commun le plus proche.

## Atelier guidé : suivi de progression

Compte deux heures. Reprends le projet des chapitres précédents.

1. Crée un composant `Progression` qui reçoit un tableau `chapitres` (`id`, `titre`, `fait`) en état initial local, avec cinq chapitres de ton choix.
2. Affiche chaque chapitre dans un `li` avec une case à cocher contrôlée par `fait`.
3. Au changement de la case, mets à jour le bon chapitre avec `map` et une copie `{ ...c, fait: ... }`.
4. Affiche « 2 / 5 chapitres terminés » en **calculant** le nombre à partir du tableau (pas d'état séparé).
5. Ajoute une barre de progression : un `div` dont la largeur vaut le pourcentage terminé.
6. Ajoute un formulaire contrôlé pour ajouter un chapitre, qui refuse un titre vide et se vide après envoi.
7. Ajoute un bouton « Supprimer » par chapitre avec `filter`.
8. Ajoute un champ de recherche dont l'état est remonté dans le parent, et filtre l'affichage avec une valeur dérivée.
9. Ajoute un bouton « Tout réinitialiser » qui remet tous les chapitres à `fait: false`.

Auto-évaluation :

- Aucun `push`, `splice` ou affectation directe sur un état ?
- Le compteur et le pourcentage sont-ils calculés plutôt que stockés ?
- Le champ se vide-t-il après un ajout, et le bouton se désactive-t-il quand il est vide ?
- Chaque `li` a-t-il une `key` stable ?

:::quiz
Un champ de recherche et une liste filtrée doivent partager le texte saisi. Où placer l'état ?
- [ ] Dans le champ de recherche uniquement
- [ ] Dans la liste uniquement
- [x] Dans leur plus proche parent commun, transmis aux deux par props
- [ ] Dans une variable globale modifiée directement
> Quand deux composants partagent une donnée, on remonte l'état au plus proche parent commun, qui transmet la valeur et le setter par props.
:::

## Erreurs fréquentes

- **Appeler le setter pendant le rendu.** `setN(1)` directement dans le corps du composant crée une boucle infinie. Appelle-le dans un gestionnaire d'événement.
- **Écrire `onClick={setN(n + 1)}`.** Ça s'exécute immédiatement. Écris `onClick={() => setN(n + 1)}`.
- **Muter un tableau ou un objet d'état.** Utilise toujours une copie.
- **Compter sur la valeur juste après le setter.** `setN(5); console.log(n)` affiche encore l'ancienne valeur.
- **Dupliquer l'état.** Stocker `chapitres` et `nombreTermines` crée deux sources de vérité qui divergent.
- **Passer un champ de `value` sans `onChange`.** Le champ devient en lecture seule et React avertit.
- **Appeler un hook dans une condition.** Cela casse l'ordre des hooks.

## Bonnes pratiques

- Garde l'état **minimal** et calcule le reste à chaque rendu.
- Utilise la forme fonctionnelle `setX(prev => ...)` quand la nouvelle valeur dépend de l'ancienne.
- Nomme les paires `[valeur, setValeur]` et les gestionnaires `handleXxx`.
- Place l'état au plus près des composants qui l'utilisent, et pas plus haut que nécessaire.
- Regroupe les champs d'un formulaire dans un seul objet quand ils vont ensemble.
- Utilise des identifiants stables (`id`) pour retrouver les éléments à modifier ou supprimer.

## À retenir

- Un événement appelle un gestionnaire ; on passe la fonction, on ne l'appelle pas.
- `useState` conserve une valeur entre les rendus et déclenche un nouvel affichage quand on appelle son setter.
- L'état est un instantané : utilise `setX(prev => ...)` pour enchaîner des mises à jour.
- Ne mute jamais l'état : copie avec `[...]`, `{...}`, `map` et `filter`.
- Un formulaire contrôlé lie `value` et `onChange` à un état.
- Quand deux composants partagent une donnée, **remonte l'état** dans leur parent commun.
- Ce qui peut être calculé à partir de l'état ne doit pas être stocké dans l'état.
