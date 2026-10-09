---
title: Projet final JavaScript
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru les variables, les fonctions, les objets, l'asynchrone, les modules, le DOM et la qualité. Ce projet final rassemble tout dans une application complète, construite de A à Z, que tu pourras montrer dans ton portfolio : **DevRoad Mini**, un suivi de roadmaps d'apprentissage en JavaScript pur.

À la fin du projet, tu auras :

- structuré une application en modules cohérents ;
- chargé des données depuis un fichier JSON avec `fetch` et géré les états de chargement et d'erreur ;
- construit une interface dynamique sans framework, avec le DOM et la délégation d'événements ;
- persisté la progression dans `localStorage` de façon défensive ;
- mis en place ESLint, Prettier et des tests Vitest ;
- livré un projet propre, documenté, prêt à être publié.

Prérequis : les six chapitres précédents. Durée : environ six heures, à répartir sur plusieurs séances. Outils : Node.js 18 ou plus, un éditeur, un navigateur, Git.

## Cahier des charges

L'application permet à un apprenant de suivre sa progression dans plusieurs roadmaps.

### Fonctionnalités obligatoires

1. **Catalogue** : afficher la liste des roadmaps chargée depuis `data/roadmaps.json`, avec titre, niveau, nombre de leçons et durée totale.
2. **Détail** : en cliquant sur une roadmap, afficher ses leçons avec titre, durée et case « terminée ».
3. **Progression** : valider ou annuler une leçon ; afficher une barre et un pourcentage par roadmap, et un total global.
4. **Filtres** : filtrer par niveau (`beginner`, `intermediate`, `professional`) et rechercher par texte dans le titre.
5. **Tri** : trier par titre ou par durée.
6. **Persistance** : conserver la progression après rechargement via `localStorage`.
7. **Notes personnelles** : ajouter et supprimer une note sur une roadmap (formulaire validé).
8. **États** : afficher clairement le chargement, une erreur réseau avec bouton « Réessayer », et l'état vide « Aucun résultat ».

### Contraintes techniques

- JavaScript pur (ES2022), modules ES, aucun framework.
- Aucun usage de `innerHTML` avec des données dynamiques.
- Aucune variable globale ; pas de `var` ; comparaison avec `===`.
- Le code de calcul est séparé du code d'affichage et testé.
- Accessibilité de base : éléments sémantiques, `label` sur les champs, `button` pour les actions, contraste suffisant.

## Étape 1 : préparer le projet

```bash
mkdir devroad-mini && cd devroad-mini
git init
npm init -y
npm install --save-dev vite vitest eslint @eslint/js prettier
mkdir -p src data tests
```

Dans `package.json`, ajoute :

```json
{
  "type": "module",
  "scripts": {
    "dev": "vite",
    "build": "vite build",
    "test": "vitest run",
    "lint": "eslint src tests",
    "format": "prettier --write ."
  }
}
```

Crée aussi `eslint.config.js` (règles `eqeqeq`, `prefer-const`, `no-var`, `no-unused-vars`) et `.prettierrc`, comme au chapitre précédent. Fais un premier commit : `chore: initialise le projet`.

Structure cible :

```text
devroad-mini/
├── index.html
├── data/roadmaps.json
├── src/
│   ├── main.js          (point d'entrée, orchestration)
│   ├── api.js           (chargement des données)
│   ├── etat.js          (état de l'application)
│   ├── calculs.js       (fonctions pures)
│   ├── stockage.js      (localStorage défensif)
│   └── vue.js           (rendu DOM)
└── tests/
    ├── calculs.test.js
    └── stockage.test.js
```

## Étape 2 : les données

Crée `data/roadmaps.json` avec au moins quatre roadmaps et trois à cinq leçons chacune :

```json
[
  {
    "id": "js",
    "titre": "JavaScript",
    "niveau": "beginner",
    "lecons": [
      { "id": "js-1", "titre": "Variables et types", "minutes": 90 },
      { "id": "js-2", "titre": "Fonctions et portée", "minutes": 120 },
      { "id": "js-3", "titre": "Tableaux et objets", "minutes": 150 }
    ]
  }
]
```

Utilise des identifiants texte **stables** : ils servent de clés dans le stockage et dans le DOM.

## Étape 3 : les calculs purs, avec tests

Dans `src/calculs.js`, écris des fonctions sans effet de bord :

```js
export function calculerPourcentage(fait, total) {
  if (!Number.isFinite(fait) || !Number.isFinite(total)) {
    throw new TypeError('fait et total doivent être des nombres');
  }
  if (total <= 0) return 0;
  return Math.round((Math.min(fait, total) / total) * 100);
}

export function dureeTotale(roadmap) {
  return roadmap.lecons.reduce((somme, l) => somme + l.minutes, 0);
}

export function leconsTerminees(roadmap, terminees) {
  return roadmap.lecons.filter((l) => terminees.has(l.id)).length;
}

export function filtrerRoadmaps(roadmaps, { niveau = 'tous', recherche = '' } = {}) {
  const terme = recherche.trim().toLowerCase();
  return roadmaps.filter((r) => {
    const niveauOk = niveau === 'tous' || r.niveau === niveau;
    const texteOk = terme === '' || r.titre.toLowerCase().includes(terme);
    return niveauOk && texteOk;
  });
}

export function trierRoadmaps(roadmaps, critere) {
  const copie = [...roadmaps];
  if (critere === 'duree') return copie.sort((a, b) => dureeTotale(b) - dureeTotale(a));
  return copie.sort((a, b) => a.titre.localeCompare(b.titre, 'fr'));
}

export function formaterDuree(minutes) {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  if (h === 0) return `${m} min`;
  return m === 0 ? `${h} h` : `${h} h ${m}`;
}
```

Écris ensuite `tests/calculs.test.js` : au moins **12 tests** couvrant les cas normaux et limites (liste vide, total à zéro, terme de recherche avec majuscules, tri qui ne modifie pas l'original). Fais passer `npm test` avant d'avancer.

:::quiz
Pourquoi isoler les calculs dans un module de fonctions pures ?
- [ ] Parce que les fonctions pures s'exécutent plus vite dans le navigateur
- [x] Parce qu'elles sont prévisibles et faciles à tester sans DOM
- [ ] Parce que le navigateur interdit de calculer dans le DOM
- [ ] Parce qu'elles évitent d'utiliser des modules
> Une fonction pure retourne toujours le même résultat pour les mêmes arguments et ne touche à rien d'extérieur. On peut donc la tester simplement, sans page web.
:::

## Étape 4 : le stockage défensif

Dans `src/stockage.js`, la progression est un ensemble d'identifiants de leçons terminées :

```js
const CLE = 'devroad-mini:progression';

export function lireProgression() {
  try {
    const brut = localStorage.getItem(CLE);
    if (!brut) return new Set();
    const donnees = JSON.parse(brut);
    if (!Array.isArray(donnees)) return new Set();
    return new Set(donnees.filter((id) => typeof id === 'string'));
  } catch {
    return new Set();
  }
}

export function ecrireProgression(terminees) {
  try {
    localStorage.setItem(CLE, JSON.stringify([...terminees]));
    return true;
  } catch {
    return false;
  }
}
```

Fais de même pour les notes. Pour tester ce module sans navigateur, passe-lui le stockage en paramètre (`lireProgression(storage = localStorage)`) et utilise un faux objet dans les tests. Teste : stockage vide, JSON invalide, mauvais type, valeurs mélangées.

## Étape 5 : le chargement des données

Dans `src/api.js` :

```js
export async function chargerRoadmaps(url = '/data/roadmaps.json') {
  const reponse = await fetch(url);
  if (!reponse.ok) {
    throw new Error(`Chargement impossible (HTTP ${reponse.status})`);
  }
  const donnees = await reponse.json();
  if (!Array.isArray(donnees)) {
    throw new Error('Format de données invalide');
  }
  return donnees;
}
```

Place `data/roadmaps.json` dans un dossier `public/` (ou configure Vite) pour qu'il soit servi à la racine. Vérifie dans l'onglet Réseau que la requête retourne 200.

## Étape 6 : l'état et le rendu

Dans `src/etat.js`, centralise l'état dans un seul objet et expose des fonctions pour le modifier :

```js
export const etat = {
  statut: 'chargement',   // 'chargement' | 'pret' | 'erreur'
  erreur: null,
  roadmaps: [],
  terminees: new Set(),
  notes: {},
  filtre: { niveau: 'tous', recherche: '' },
  tri: 'titre',
  selection: null,
};

export function basculerLecon(idLecon) {
  if (etat.terminees.has(idLecon)) etat.terminees.delete(idLecon);
  else etat.terminees.add(idLecon);
}
```

Dans `src/vue.js`, écris une fonction `rendre(etat)` qui lit l'état et reconstruit l'interface. Respecte la règle d'or : **l'interface est le résultat de l'état**. Une fonction par bloc visuel (`carteRoadmap`, `detailRoadmap`, `barreProgression`, `messageErreur`), toutes construites avec `createElement` et `textContent`.

Pour la barre de progression, utilise l'élément sémantique `progress` ou un `div` avec les attributs `role="progressbar"` et `aria-valuenow`.

## Étape 7 : l'orchestration et les événements

Dans `src/main.js`, relie tout :

```js
import { chargerRoadmaps } from './api.js';
import { etat, basculerLecon } from './etat.js';
import { lireProgression, ecrireProgression } from './stockage.js';
import { rendre } from './vue.js';

async function demarrer() {
  etat.statut = 'chargement';
  rendre(etat);
  try {
    etat.roadmaps = await chargerRoadmaps();
    etat.terminees = lireProgression();
    etat.statut = 'pret';
  } catch (erreur) {
    etat.statut = 'erreur';
    etat.erreur = erreur.message;
  }
  rendre(etat);
}

document.addEventListener('click', (evenement) => {
  const cible = evenement.target.closest('[data-action]');
  if (!cible) return;

  switch (cible.dataset.action) {
    case 'basculer-lecon':
      basculerLecon(cible.dataset.id);
      ecrireProgression(etat.terminees);
      break;
    case 'selectionner':
      etat.selection = cible.dataset.id;
      break;
    case 'reessayer':
      demarrer();
      return;
  }
  rendre(etat);
});

demarrer();
```

Ajoute les écouteurs `input` (recherche), `change` (niveau, tri) et `submit` (notes avec `preventDefault`). Pour la recherche, ajoute un **anti-rebond** (*debounce*) de 250 ms avec une closure pour éviter de redessiner à chaque frappe.

:::quiz
Dans l'étape 7, pourquoi utilise-t-on un seul écouteur `click` avec des attributs `data-action` ?
- [ ] Parce que le navigateur n'accepte qu'un seul écouteur par page
- [x] Pour appliquer la délégation d'événements, y compris sur des éléments recréés à chaque rendu
- [ ] Pour éviter d'utiliser `addEventListener`
- [ ] Pour accélérer le chargement du JSON
> La délégation évite d'attacher des écouteurs à chaque élément recréé lors d'un rendu. Un seul gestionnaire lit `data-action` pour savoir quoi faire.
:::

## Étape 8 : qualité et finitions

1. Lance `npm run lint` : corrige toutes les erreurs, sans désactiver de règle.
2. Lance `npm run format` pour uniformiser le style.
3. Vérifie la navigation au clavier : tout est atteignable avec Tab, les boutons ont un focus visible.
4. Teste le mode hors ligne dans l'onglet Réseau (option « Offline ») et vérifie que le bouton « Réessayer » fonctionne.
5. Teste avec un `localStorage` corrompu : écris du texte invalide dans la clé et recharge ; l'application ne doit pas planter.
6. Lance `npm run build` et vérifie que le dossier `dist` s'ouvre sans erreur.
7. Écris un `README.md` : description, captures, commandes, structure, choix techniques.

## Livrables

- Un dépôt Git avec au moins 8 commits au message clair (`feat:`, `fix:`, `test:`, `docs:`).
- Le code organisé comme décrit plus haut.
- Un `README.md` complet.
- Au moins 12 tests qui passent avec `npm test`.
- Un lint sans erreur.
- Optionnel : un déploiement gratuit (GitHub Pages, Netlify ou Vercel).

## Checklist de validation

Coche chaque point avant de considérer le projet terminé.

- [ ] Le catalogue s'affiche depuis le fichier JSON, avec durée totale formatée.
- [ ] Le clic sur une roadmap affiche le détail de ses leçons.
- [ ] Valider ou annuler une leçon met à jour la barre, le pourcentage et le total global.
- [ ] La progression survit au rechargement de la page.
- [ ] Les filtres par niveau, la recherche et le tri fonctionnent ensemble.
- [ ] Les notes se créent et se suppriment, et un texte vide est refusé avec un message.
- [ ] Les états de chargement, d'erreur avec « Réessayer » et de résultat vide sont visibles.
- [ ] Aucun `innerHTML` n'est utilisé avec des données dynamiques.
- [ ] Aucun `var`, aucune variable globale, uniquement `===`.
- [ ] Les fonctions de calcul sont pures et testées, avec au moins 12 tests qui passent.
- [ ] Un `localStorage` invalide ne fait pas planter l'application.
- [ ] `npm run lint` ne remonte aucune erreur.
- [ ] Le README explique comment installer, lancer et tester le projet.

## Pistes d'amélioration

- Ajouter un mode sombre mémorisé dans `localStorage`.
- Exporter et importer la progression en JSON.
- Calculer une série de jours consécutifs d'étude (« streak ») avec les dates.
- Remplacer le fichier JSON par une vraie API Laravel et gérer l'authentification.
- Ajouter des animations de transition respectant `prefers-reduced-motion`.
- Réécrire l'application en TypeScript, puis en React : tu mesureras tout ce que ces outils apportent.

## Atelier guidé : plan de travail suggéré

Voici un découpage en séances pour tenir les six heures.

1. Séance 1 (60 min) : étapes 1 et 2, projet initialisé, données prêtes, premier commit.
2. Séance 2 (75 min) : étape 3, calculs purs et tests verts.
3. Séance 3 (60 min) : étapes 4 et 5, stockage et chargement des données.
4. Séance 4 (90 min) : étapes 6 et 7, état, rendu et événements.
5. Séance 5 (45 min) : notes, filtres, tri et anti-rebond.
6. Séance 6 (30 min) : étape 8, qualité, README, build.

Auto-évaluation finale : demande à quelqu'un de lire ton code sans explication. Peut-il retrouver où se trouve la logique du pourcentage, celle du stockage et celle de l'affichage en moins d'une minute ? Si oui, ton architecture est lisible.

## Erreurs fréquentes

- **Mélanger calcul et DOM dans la même fonction.** Tu ne peux plus tester le calcul sans navigateur.
- **Modifier directement le tableau issu du JSON pour trier.** Travaille sur une copie.
- **Oublier de re-rendre après avoir modifié l'état.** L'interface reste en retard sur les données.
- **Stocker des objets complets plutôt que des identifiants.** Les données stockées deviennent vite obsolètes.
- **Oublier de gérer l'échec du chargement.** L'écran reste blanc sans explication.
- **Ajouter des écouteurs à chaque rendu.** Les actions se déclenchent plusieurs fois.
- **Utiliser des index comme identifiants.** Ils changent avec le tri et le filtre.

## Bonnes pratiques

- Commence par les données et les calculs, ensuite seulement l'interface.
- Un seul endroit pour l'état, un seul chemin pour le modifier, un seul rendu.
- Fais des commits fréquents et petits ; chaque commit laisse les tests au vert.
- Teste les cas limites : listes vides, JSON invalide, très long texte, double clic rapide.
- Écris le README comme si un recruteur allait le lire en premier.
- Garde du temps pour l'accessibilité et les états d'erreur : c'est ce qui distingue un projet professionnel d'un exercice.

## À retenir

- Un projet réel s'organise en modules : données, calculs, stockage, état, vue, orchestration.
- L'interface est le résultat de l'état ; on modifie l'état, puis on re-rend.
- Les fonctions pures testées protègent la logique métier de la régression.
- Toute donnée externe (réseau, stockage) est validée et peut échouer : prévois toujours le cas d'erreur.
- ESLint, Prettier et Vitest font partie du projet dès le premier jour, pas à la fin.
- Tu as maintenant les bases solides pour passer à TypeScript puis à React, qui reprennent exactement ces idées.
