---
title: Modules et navigateur
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, tu as écrit du JavaScript « pur ». Il est temps de le brancher sur une vraie page web et d'organiser ton code en fichiers. Ce chapitre couvre deux sujets complémentaires : les **modules ES** (`import` / `export`), qui découpent une application en morceaux, et le **DOM**, l'interface qui permet à JavaScript de lire et modifier la page.

À la fin du chapitre, tu seras capable de :

- exporter et importer des fonctions, constantes et objets entre fichiers ;
- charger un module dans une page avec `<script type="module">` ;
- sélectionner des éléments du DOM et modifier leur texte, leurs classes et leurs attributs ;
- créer et insérer de nouveaux éléments ;
- réagir aux événements (clic, saisie, soumission de formulaire) ;
- utiliser la délégation d'événements ;
- mémoriser des données avec `localStorage`.

Prérequis : les chapitres précédents, surtout fonctions, tableaux et asynchrone. Prévois deux heures et demie. Tu as besoin d'un éditeur de code et d'un navigateur. Pour que les modules fonctionnent, sers les fichiers via un serveur local (extension Live Server de VS Code, ou `npx serve`) plutôt qu'en ouvrant le fichier directement.

## Pourquoi des modules ?

Un fichier de 3 000 lignes est impossible à maintenir. Un **module** est un fichier JavaScript qui expose seulement ce qu'il décide de partager. Chaque module possède sa propre portée : ses variables ne polluent pas l'espace global.

Voici une petite structure de projet :

```text
projet/
├── index.html
└── js/
    ├── main.js
    ├── stockage.js
    └── utils.js
```

### export et import nommés

Dans `js/utils.js`, exporte ce que d'autres fichiers pourront utiliser :

```js
// js/utils.js
export const NIVEAUX = ['beginner', 'intermediate', 'professional'];

export function formaterDuree(minutes) {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  if (h === 0) return `${m} min`;
  return m === 0 ? `${h} h` : `${h} h ${m}`;
}

export function calculerPourcentage(fait, total) {
  return total === 0 ? 0 : Math.round((fait / total) * 100);
}
```

Dans `js/main.js`, importe-les par leur nom, entre accolades :

```js
// js/main.js
import { formaterDuree, calculerPourcentage, NIVEAUX } from './utils.js';

console.log(formaterDuree(150));        // 2 h 30
console.log(calculerPourcentage(3, 12)); // 25
```

Tu peux renommer à l'import avec `as`, ou tout récupérer d'un coup :

```js
import { formaterDuree as formater } from './utils.js';
import * as utils from './utils.js';

utils.calculerPourcentage(1, 4);
```

### export par défaut

Un fichier peut aussi avoir **un** export par défaut, importé sans accolades et avec le nom de ton choix :

```js
// js/stockage.js
const CLE = 'devroad-progression';

export default {
  lire() {
    try {
      return JSON.parse(localStorage.getItem(CLE)) ?? [];
    } catch {
      return [];
    }
  },
  ecrire(donnees) {
    localStorage.setItem(CLE, JSON.stringify(donnees));
  },
};
```

```js
import stockage from './stockage.js';
const terminees = stockage.lire();
```

> **Astuce** : dans la pratique, beaucoup d'équipes préfèrent les exports nommés. Le nom est imposé, l'autocomplétion fonctionne mieux et un renommage est plus facile à suivre dans tout le projet.

### Charger un module dans la page

Le fichier HTML doit déclarer le script avec `type="module"`. Le chemin d'import doit inclure l'extension et commencer par `./` ou `../`.

```html
<script type="module" src="./js/main.js"></script>
```

Un script de type module est automatiquement différé : il s'exécute après l'analyse du HTML, donc le DOM est prêt. Il fonctionne en mode strict.

:::quiz
Comment importer la fonction nommée `formaterDuree` depuis `./utils.js` ?
- [ ] import formaterDuree from './utils.js';
- [x] import { formaterDuree } from './utils.js';
- [ ] require('./utils.js').formaterDuree;
- [ ] import './utils.js' as formaterDuree;
> Les exports nommés s'importent entre accolades avec leur nom exact. Sans accolades, on importe l'export par défaut.
:::

## Le DOM : la page vue par JavaScript

Quand le navigateur lit ton HTML, il construit un arbre d'objets : le **DOM** (*Document Object Model*). `document` en est la racine. Modifier cet arbre modifie immédiatement la page affichée.

Prenons ce HTML de départ :

```html
<main>
  <h1 id="titre">Ma progression</h1>
  <p id="resume"></p>
  <ul id="liste"></ul>
  <form id="formulaire">
    <input id="saisie" type="text" placeholder="Nouvelle leçon" required />
    <button type="submit">Ajouter</button>
  </form>
</main>
<script type="module" src="./js/main.js"></script>
```

### Sélectionner des éléments

```js
const titre = document.querySelector('#titre');        // premier élément qui correspond
const items = document.querySelectorAll('#liste li');  // tous les éléments (NodeList)
const formulaire = document.getElementById('formulaire');
```

`querySelector` accepte n'importe quel sélecteur CSS. Il retourne `null` si rien ne correspond : vérifie avant d'utiliser le résultat.

### Lire et modifier

```js
titre.textContent = 'Ma progression DevRoad';    // texte brut, sûr
titre.classList.add('actif');                    // ajoute une classe CSS
titre.classList.toggle('masque');                // ajoute ou retire
titre.setAttribute('data-niveau', 'beginner');   // attribut
titre.dataset.niveau;                            // lecture d'un attribut data-
titre.style.color = 'teal';                      // style en ligne (à éviter)
```

> **Attention** : `innerHTML` interprète le texte comme du HTML. Si tu y insères une donnée saisie par un utilisateur, un attaquant peut injecter du code (faille **XSS**). Préfère `textContent`, ou construis les éléments avec `createElement`.

### Créer et insérer des éléments

```js
function creerItem(lecon) {
  const li = document.createElement('li');
  li.textContent = `${lecon.titre} (${lecon.minutes} min)`;
  li.dataset.id = lecon.id;
  if (lecon.terminee) li.classList.add('terminee');
  return li;
}

const liste = document.querySelector('#liste');
liste.append(creerItem({ id: 1, titre: 'Variables', minutes: 90, terminee: true }));
liste.prepend(creerItem({ id: 0, titre: 'Introduction', minutes: 30 }));
liste.replaceChildren();   // vide la liste
```

Pour afficher un tableau, on le transforme avec `map` puis on insère le tout en une fois, ce qui évite de redessiner la page à chaque ajout :

```js
function afficher(lecons) {
  const elements = lecons.map(creerItem);
  document.querySelector('#liste').replaceChildren(...elements);
}
```

## Réagir aux événements

Un **événement** est un signal envoyé par le navigateur : clic, frappe au clavier, soumission, chargement. On l'écoute avec `addEventListener`, qui reçoit le type d'événement et un callback.

```js
const bouton = document.querySelector('#bouton');

bouton.addEventListener('click', (evenement) => {
  console.log('Cliqué !', evenement.target);
});
```

Les événements les plus utiles :

| Événement | Quand se déclenche-t-il ? |
| --- | --- |
| `click` | Clic sur un élément |
| `input` | À chaque modification d'un champ |
| `change` | Quand un champ perd le focus après modification |
| `submit` | Soumission d'un formulaire |
| `keydown` | Touche enfoncée |
| `DOMContentLoaded` | HTML entièrement analysé |

### Traiter un formulaire

Par défaut, un formulaire recharge la page. On empêche ce comportement avec `preventDefault`.

```js
const formulaire = document.querySelector('#formulaire');
const saisie = document.querySelector('#saisie');
const lecons = [];

formulaire.addEventListener('submit', (evenement) => {
  evenement.preventDefault();

  const titre = saisie.value.trim();
  if (titre === '') return;

  lecons.push({ id: Date.now(), titre, minutes: 60, terminee: false });
  saisie.value = '';
  afficher(lecons);
});
```

### La délégation d'événements

Si tu ajoutes un écouteur sur chacun des 200 éléments d'une liste, c'est lourd, et les éléments créés plus tard n'en ont pas. La solution : écouter **sur le parent** et regarder qui a été cliqué. Les événements « remontent » (*bubbling*) du plus profond vers les parents.

```js
document.querySelector('#liste').addEventListener('click', (evenement) => {
  const li = evenement.target.closest('li');
  if (!li) return;

  const id = Number(li.dataset.id);
  const lecon = lecons.find((l) => l.id === id);
  lecon.terminee = !lecon.terminee;
  afficher(lecons);
});
```

`closest('li')` remonte depuis l'élément cliqué jusqu'au premier `li` parent, même si le clic est tombé sur un enfant.

:::quiz
Pourquoi appelle-t-on `evenement.preventDefault()` dans un gestionnaire `submit` ?
- [ ] Pour supprimer l'événement du navigateur
- [x] Pour empêcher le rechargement de la page par défaut
- [ ] Pour valider automatiquement le formulaire
- [ ] Pour ralentir l'envoi
> Le comportement par défaut d'un formulaire est d'envoyer les données et de recharger la page. `preventDefault` l'annule pour que JavaScript prenne le relais.
:::

## Mémoriser avec localStorage

`localStorage` conserve des paires clé / valeur dans le navigateur, même après fermeture. Il ne stocke que du **texte** : on passe par JSON.

```js
localStorage.setItem('lecons', JSON.stringify(lecons));

const sauvegarde = JSON.parse(localStorage.getItem('lecons')) ?? [];

localStorage.removeItem('lecons');
```

Limites à connaître : environ 5 Mo, données lisibles par n'importe quel script de la page, accès synchrone. N'y mets jamais de mot de passe, de jeton secret ou de données sensibles. Enveloppe aussi l'accès dans un `try / catch`, car il peut échouer (mode privé, stockage plein, accès bloqué).

:::quiz
Quelle méthode évite d'attacher un écouteur sur chaque élément d'une longue liste ?
- [ ] querySelectorAll
- [x] La délégation d'événements sur le parent
- [ ] setTimeout
- [ ] innerHTML
> En écoutant sur le parent et en identifiant la cible avec `closest`, un seul écouteur suffit, y compris pour les éléments ajoutés plus tard.
:::

## Atelier guidé : une liste de leçons persistante

Compte une heure et demie. Crée un dossier avec `index.html`, `js/main.js`, `js/utils.js` et `js/stockage.js`, puis lance un serveur local.

1. Dans `index.html`, écris la structure vue plus haut (titre, résumé, liste, formulaire) et charge `main.js` avec `type="module"`.
2. Dans `utils.js`, exporte `formaterDuree` et `calculerPourcentage` comme dans le cours.
3. Dans `stockage.js`, exporte `lire()` et `ecrire(donnees)` avec `try / catch` autour de `localStorage`.
4. Dans `main.js`, importe tout cela, charge les leçons depuis le stockage au démarrage.
5. Écris `creerItem(lecon)` avec `createElement` et `textContent`, jamais `innerHTML`.
6. Écris `afficher()` qui redessine la liste avec `replaceChildren` et met à jour le résumé : « 2 / 5 terminées (40 %) ».
7. Gère la soumission du formulaire : `preventDefault`, validation du texte, ajout, sauvegarde, rendu.
8. Ajoute la délégation d'événements pour basculer l'état d'une leçon au clic et sauvegarde le résultat.
9. Ajoute un bouton « Supprimer » dans chaque ligne ; en cas de clic, retire la leçon avec `filter`.
10. Recharge la page : les leçons doivent être conservées.

Pour t'auto-évaluer : peux-tu expliquer la différence entre export nommé et export par défaut, et pourquoi tu as utilisé `textContent` plutôt que `innerHTML` pour afficher le titre saisi ?

## Erreurs fréquentes

- **Ouvrir `index.html` avec `file://` et utiliser des modules.** Le navigateur bloque les imports ; passe par un serveur local.
- **Oublier l'extension `.js` ou le préfixe `./` dans l'import.** Le navigateur ne devine pas le chemin.
- **Mélanger export par défaut et nommé à l'import.** Les accolades ne servent que pour les exports nommés.
- **Sélectionner un élément avant sa création.** `querySelector` retourne `null` ; place le script en fin de page ou utilise un module.
- **Utiliser `innerHTML` avec une saisie utilisateur.** C'est une faille XSS.
- **Oublier `preventDefault` sur un formulaire.** La page se recharge et ton état disparaît.
- **Ajouter des écouteurs à chaque rendu.** Les gestionnaires s'accumulent ; utilise la délégation ou n'attache qu'une fois.
- **Stocker des objets directement dans `localStorage`.** Tu obtiens `[object Object]` ; passe par `JSON.stringify`.

## Bonnes pratiques

- Un module, une responsabilité : utilitaires, stockage, rendu, point d'entrée.
- Préfère les exports nommés et garde des chemins relatifs clairs.
- Sépare la logique (données, calculs) de l'affichage (DOM) pour pouvoir la tester.
- Utilise `textContent` et `createElement` pour les données dynamiques ; réserve `innerHTML` au contenu de confiance.
- Délègue les événements sur un parent stable.
- Garde des classes CSS pour le style plutôt que des styles en ligne.
- Entoure `localStorage` et `JSON.parse` de `try / catch`.

## À retenir

- Un module ES expose ce qu'il exporte et cache le reste ; `import` récupère les exports nommés entre accolades.
- `<script type="module">` charge un module, différé et en mode strict, depuis un serveur.
- Le DOM est l'arbre d'objets de la page ; `querySelector` sélectionne, `textContent` et `classList` modifient.
- Crée les éléments avec `createElement`, insère-les avec `append` ou `replaceChildren`.
- `addEventListener` écoute les événements ; `preventDefault` annule le comportement par défaut ; la délégation évite les écouteurs multiples.
- `localStorage` stocke du texte persistant : sérialise en JSON et ne mets jamais de secrets.
