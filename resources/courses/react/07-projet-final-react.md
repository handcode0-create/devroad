---
title: Projet final React
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as vu les composants, les props, le state, les effets, le contexte et les optimisations. Il est temps de tout assembler dans un **vrai projet**, de bout en bout, comme tu le ferais pour un client : cahier des besoins, découpage, développement par étapes, vérification. Tu vas construire **TaskRoad**, un suivi d'apprentissage : l'utilisateur crée des objectifs, y ajoute des étapes, les coche, filtre, recherche, et retrouve ses données à sa prochaine visite.

À la fin du projet, tu auras :

- une application React 18 construite avec **Vite**, propre et organisée par fonctionnalités ;
- un état global géré avec **Context** et **useReducer**, persisté dans le `localStorage` ;
- des données chargées depuis une API (ici un fichier JSON simulant un serveur) avec gestion du chargement et des erreurs ;
- un formulaire validé, des listes filtrables, une interface accessible et fluide ;
- une application **déployable**, avec un build de production vérifié.

Prérequis : tous les chapitres précédents de cette roadmap, Node.js 20 ou plus. Prévois six heures, que tu peux répartir en deux ou trois sessions. Un seul principe : **avance par petites étapes et teste après chacune**.

## Cahier des charges

### Contexte

Des apprenants francophones suivent plusieurs parcours en parallèle (React, Laravel, design). Ils veulent un outil léger, rapide sur mobile, qui fonctionne même avec une connexion instable. TaskRoad leur permet de découper un parcours en objectifs et en étapes, et de visualiser leur avancement.

### Fonctionnalités obligatoires

1. **Liste des objectifs** : chaque objectif a un titre, une catégorie (`frontend`, `backend`, `design`), une priorité (`basse`, `normale`, `haute`) et une liste d'étapes.
2. **Création et suppression** d'un objectif via un formulaire contrôlé validé (titre de 3 caractères minimum, catégorie obligatoire).
3. **Étapes** : ajouter, cocher, supprimer une étape dans un objectif.
4. **Progression** : pourcentage d'avancement par objectif et global, **calculé** et jamais stocké.
5. **Recherche et filtres** : par texte, par catégorie, par statut (tous, en cours, terminés).
6. **Persistance** : l'état est conservé dans le `localStorage` entre deux visites.
7. **Import de modèles** : un bouton charge des objectifs prédéfinis depuis `public/modeles.json`, avec états de chargement et d'erreur.
8. **Thème** clair/sombre mémorisé, géré par un contexte.

### Contraintes techniques

- React 18, Vite, JavaScript (ou TypeScript si tu es à l'aise), aucun autre gestionnaire d'état ;
- composants fonctionnels et hooks uniquement ;
- aucune mutation d'état ;
- au moins un hook personnalisé et une *error boundary* ;
- une page de statistiques chargée avec `lazy` et `Suspense` ;
- navigation au clavier possible, étiquettes de formulaires associées, contrastes lisibles ;
- aucun avertissement dans la console.

### Hors périmètre

Authentification, base de données réelle, tests automatisés (tu pourras les ajouter ensuite), synchronisation multi-appareil.

## Phase 1 : initialiser et structurer (30 min)

Crée le projet et supprime le superflu :

```bash
npm create vite@latest taskroad -- --template react
cd taskroad
npm install
npm run dev
```

Crée l'arborescence suivante dans `src/` :

```text
src/
├── components/        Button.jsx, Badge.jsx, ProgressBar.jsx
├── features/
│   ├── objectifs/     ObjectifsContext.jsx, reducer.js, ObjectifCard.jsx,
│   │                  ObjectifForm.jsx, ListeObjectifs.jsx, Filtres.jsx
│   ├── etapes/        EtapeItem.jsx, EtapeForm.jsx
│   └── theme/         ThemeContext.jsx
├── hooks/             useLocalStorage.js, useFetch.js
├── lib/               stats.js, validation.js
├── pages/             Accueil.jsx, Statistiques.jsx
├── App.jsx
└── main.jsx
```

Ajoute l'alias `@` dans `vite.config.js` (voir le chapitre précédent) et vérifie qu'un `import Badge from '@/components/Badge'` fonctionne.

### Modélise tes données avant de coder

Décide de la forme d'un objectif **maintenant**, tout le reste en dépend :

```js
{
  id: 'obj-1',
  titre: 'Maîtriser les hooks',
  categorie: 'frontend',
  priorite: 'haute',
  etapes: [
    { id: 'e-1', texte: 'Lire le chapitre useState', fait: true },
    { id: 'e-2', texte: 'Construire un compteur', fait: false },
  ],
}
```

Remarque ce qui **manque** volontairement : pas de champ `progression` ni `termine`. Ces valeurs se calculent à partir des étapes.

## Phase 2 : la logique pure (40 min)

Avant toute interface, écris la logique dans des fichiers sans React. Elle est plus facile à raisonner et à tester.

```js
// src/lib/stats.js
export function progressionObjectif(objectif) {
  const total = objectif.etapes.length;
  if (total === 0) return 0;
  const faites = objectif.etapes.filter((e) => e.fait).length;
  return Math.round((faites / total) * 100);
}

export function progressionGlobale(objectifs) {
  const etapes = objectifs.flatMap((o) => o.etapes);
  if (etapes.length === 0) return 0;
  return Math.round((etapes.filter((e) => e.fait).length / etapes.length) * 100);
}

export function estTermine(objectif) {
  return objectif.etapes.length > 0 && objectif.etapes.every((e) => e.fait);
}
```

```js
// src/lib/validation.js
export function validerObjectif({ titre, categorie }) {
  const erreurs = {};
  if (titre.trim().length < 3) {
    erreurs.titre = 'Le titre doit contenir au moins 3 caractères.';
  }
  if (!categorie) {
    erreurs.categorie = 'Choisis une catégorie.';
  }
  return erreurs;
}
```

Écris ensuite le **reducer** dans `features/objectifs/reducer.js`. Il gère au minimum : `objectif/ajouter`, `objectif/supprimer`, `etape/ajouter`, `etape/basculer`, `etape/supprimer`, `modeles/importer`, `etat/reinitialiser`.

```js
export function objectifsReducer(etat, action) {
  switch (action.type) {
    case 'objectif/ajouter':
      return [...etat, { ...action.objectif, etapes: [] }];
    case 'objectif/supprimer':
      return etat.filter((o) => o.id !== action.id);
    case 'etape/ajouter':
      return etat.map((o) =>
        o.id === action.objectifId
          ? { ...o, etapes: [...o.etapes, action.etape] }
          : o
      );
    case 'etape/basculer':
      return etat.map((o) =>
        o.id === action.objectifId
          ? {
              ...o,
              etapes: o.etapes.map((e) =>
                e.id === action.etapeId ? { ...e, fait: !e.fait } : e
              ),
            }
          : o
      );
    // à toi : etape/supprimer, modeles/importer, etat/reinitialiser
    default:
      throw new Error(`Action inconnue : ${action.type}`);
  }
}
```

Pour générer des identifiants, utilise `crypto.randomUUID()` (disponible dans les navigateurs récents, sur HTTPS ou localhost).

:::quiz
Pourquoi la progression d'un objectif ne doit-elle pas être stockée dans l'état ?
- [ ] Parce que `localStorage` n'accepte pas les nombres
- [x] Parce qu'elle se calcule à partir des étapes ; la stocker créerait deux sources de vérité qui peuvent diverger
- [ ] Parce que React interdit les pourcentages
- [ ] Parce que le reducer ne peut pas la modifier
> Ce qui peut être dérivé de l'état doit être calculé au rendu. Stocker la progression obligerait à la mettre à jour partout et provoquerait des incohérences.
:::

## Phase 3 : état global et persistance (45 min)

Crée d'abord le hook `useLocalStorage` (vu au chapitre sur les effets), puis le contexte des objectifs. Le reducer a besoin d'une persistance : plutôt que de la mélanger dans le reducer (qui doit rester pur), synchronise-la avec un effet.

```jsx
// src/features/objectifs/ObjectifsContext.jsx
import { createContext, useContext, useEffect, useReducer } from 'react';
import { objectifsReducer } from './reducer';

const ObjectifsContext = createContext(null);
const CLE = 'taskroad:objectifs';

function charger() {
  try {
    const brut = localStorage.getItem(CLE);
    return brut ? JSON.parse(brut) : [];
  } catch {
    return [];
  }
}

export function ObjectifsProvider({ children }) {
  const [objectifs, dispatch] = useReducer(objectifsReducer, null, charger);

  useEffect(() => {
    try {
      localStorage.setItem(CLE, JSON.stringify(objectifs));
    } catch {
      // stockage plein ou indisponible : l'application continue de fonctionner
    }
  }, [objectifs]);

  return (
    <ObjectifsContext.Provider value={{ objectifs, dispatch }}>
      {children}
    </ObjectifsContext.Provider>
  );
}

export function useObjectifs() {
  const contexte = useContext(ObjectifsContext);
  if (!contexte) throw new Error('useObjectifs requiert ObjectifsProvider');
  return contexte;
}
```

Le troisième argument de `useReducer` est une fonction d'**initialisation paresseuse** : elle ne lit le stockage qu'une fois. Tout le `try/catch` est nécessaire : le stockage peut être désactivé (navigation privée stricte) ou contenir un JSON corrompu.

Fais de même pour le thème : un contexte avec `theme` et `basculerTheme`, qui ajoute `data-theme="sombre"` sur `document.documentElement` via un effet.

> **Astuce** : après chaque phase, vérifie dans l'onglet Application des outils de développement que la clé `taskroad:objectifs` contient bien le JSON attendu.

## Phase 4 : composants d'interface (70 min)

Construis les composants du plus petit au plus grand.

1. **`Badge`** et **`ProgressBar`** : génériques, sans connaissance du métier. `ProgressBar` reçoit `valeur` (0 à 100) et expose `role="progressbar"`, `aria-valuenow`, `aria-valuemin` et `aria-valuemax`.
2. **`EtapeItem`** : une case à cocher avec un libellé associé, un bouton « Supprimer » avec un `aria-label` explicite (`Supprimer l'étape : …`).
3. **`EtapeForm`** : un champ contrôlé qui refuse le texte vide et se vide après envoi.
4. **`ObjectifCard`** : titre, badges de catégorie et de priorité, barre de progression, liste d'étapes, formulaire d'ajout d'étape, bouton de suppression avec confirmation.
5. **`ObjectifForm`** : formulaire contrôlé avec validation. Affiche les erreurs sous chaque champ avec `aria-describedby`, et un focus sur le premier champ en erreur.
6. **`ListeObjectifs`** : affiche les cartes, ou un état vide encourageant (« Aucun objectif pour l'instant, crée le premier ! »).

Voici le formulaire d'objectif, pour t'orienter sur l'accessibilité :

```jsx
function ObjectifForm({ onAjouter }) {
  const [valeurs, setValeurs] = useState({
    titre: '', categorie: '', priorite: 'normale',
  });
  const [erreurs, setErreurs] = useState({});

  function handleChange(e) {
    setValeurs({ ...valeurs, [e.target.name]: e.target.value });
  }

  function handleSubmit(e) {
    e.preventDefault();
    const trouvees = validerObjectif(valeurs);
    setErreurs(trouvees);
    if (Object.keys(trouvees).length > 0) return;
    onAjouter({ id: crypto.randomUUID(), ...valeurs, titre: valeurs.titre.trim() });
    setValeurs({ titre: '', categorie: '', priorite: 'normale' });
  }

  return (
    <form onSubmit={handleSubmit} noValidate>
      <label htmlFor="titre">Titre de l'objectif</label>
      <input
        id="titre"
        name="titre"
        value={valeurs.titre}
        onChange={handleChange}
        aria-invalid={Boolean(erreurs.titre)}
        aria-describedby={erreurs.titre ? 'erreur-titre' : undefined}
      />
      {erreurs.titre && <p id="erreur-titre" role="alert">{erreurs.titre}</p>}
      {/* catégorie (select) et priorité à compléter */}
      <button type="submit">Ajouter l'objectif</button>
    </form>
  );
}
```

## Phase 5 : recherche, filtres et données distantes (60 min)

### Filtres

L'état des filtres (`recherche`, `categorie`, `statut`) est local à la page `Accueil`, car seuls `Filtres` et `ListeObjectifs` en dépendent. Les objectifs visibles sont une **valeur dérivée** :

```jsx
const visibles = useMemo(
  () =>
    objectifs.filter((o) => {
      const texteOk = o.titre.toLowerCase().includes(recherche.toLowerCase());
      const categorieOk = categorie === 'toutes' || o.categorie === categorie;
      const termine = estTermine(o);
      const statutOk =
        statut === 'tous' || (statut === 'termines' ? termine : !termine);
      return texteOk && categorieOk && statutOk;
    }),
  [objectifs, recherche, categorie, statut]
);
```

Ajoute un message « Aucun résultat pour ces filtres » avec un bouton qui réinitialise les filtres, différent de l'état vide général.

### Import de modèles

Crée `public/modeles.json` avec trois objectifs prédéfinis. Écris le bouton « Importer des modèles » avec `useFetch` déclenché à la demande, ou une fonction `async` appelée dans un gestionnaire d'événement (c'est une action de l'utilisateur, donc pas d'effet). Affiche :

- « Chargement… » et le bouton désactivé pendant la requête ;
- un message d'erreur avec un bouton « Réessayer » si la requête échoue (teste en renommant temporairement le fichier) ;
- une confirmation « 3 modèles importés » en cas de succès, et évite les doublons en comparant les `id`.

:::quiz
L'import de modèles est déclenché par un clic sur un bouton. Où placer l'appel `fetch` ?
- [ ] Dans un `useEffect` sans dépendances
- [x] Dans le gestionnaire d'événement du bouton
- [ ] Dans le corps du composant
- [ ] Dans le reducer
> L'appel est la conséquence d'une action de l'utilisateur : c'est un événement. Le reducer doit rester pur, et le corps du composant ne doit pas lancer de requête à chaque rendu.
:::

## Phase 6 : performance, découpage et robustesse (45 min)

1. Charge la page `Statistiques` avec `lazy` et `Suspense` (affiche un squelette de chargement). Elle présente la progression globale, le nombre d'objectifs par catégorie et le nombre d'étapes terminées.
2. Entoure l'application d'une `ErrorBoundary` avec un bouton « Réinitialiser les données » qui vide le `localStorage` et recharge la page : utile si les données stockées sont corrompues.
3. Génère 500 objectifs de test (script dans la console ou bouton réservé au développement) et enregistre une frappe dans la recherche avec le Profiler. Si c'est lent, applique `memo` à `ObjectifCard` et stabilise les fonctions passées avec `useCallback`.
4. Vérifie que la valeur du contexte est stable (`useMemo`) si la mesure montre des rendus inutiles.

## Phase 7 : build, vérification et déploiement (30 min)

```bash
npm run build
npm run preview
```

Ouvre l'adresse affichée et teste le parcours complet **sur la version de production**. Contrôle ensuite :

- le poids des fichiers affiché par `vite build` et la présence d'un fichier distinct pour `Statistiques` ;
- un audit Lighthouse (onglet du navigateur) : vise au moins 90 en accessibilité et en bonnes pratiques ;
- le comportement avec la limitation « Slow 3G » et avec le mode hors ligne simulé.

Pour déployer, n'importe quel hébergeur de sites statiques convient (Netlify, Vercel, Cloudflare Pages) : commande de build `npm run build`, dossier de sortie `dist`.

## Checklist d'acceptation

Coche chaque ligne avant de considérer le projet terminé.

**Fonctionnel**

- Je peux créer un objectif valide ; un titre trop court affiche une erreur claire.
- Je peux ajouter, cocher et supprimer des étapes.
- La progression par objectif et globale se met à jour instantanément.
- La recherche et les trois filtres se combinent correctement.
- Les données survivent à un rechargement de la page.
- L'import de modèles gère chargement, erreur, succès et doublons.
- Le thème clair/sombre est mémorisé.

**Qualité du code**

- Aucune mutation d'état (aucun `push`, `splice` ou affectation sur l'état).
- La progression et les listes filtrées sont calculées, jamais dupliquées dans l'état.
- Chaque `useEffect` a ses dépendances correctes et son nettoyage si nécessaire.
- Les fonctionnalités sont rangées dans `features/`, les composants génériques dans `components/`.
- La console ne montre aucun avertissement ni erreur.

**Accessibilité et expérience**

- Tout le parcours est possible au clavier, avec un focus visible.
- Chaque champ possède un `label` associé ; les erreurs sont annoncées (`role="alert"`).
- Les états vides, de chargement et d'erreur sont tous traités.
- L'interface reste utilisable à 360 px de large.

**Livraison**

- `npm run build` réussit et la prévisualisation de production fonctionne.
- La page Statistiques est dans un fichier séparé.
- Un fichier `README.md` explique comment installer et lancer le projet.

## Pour aller plus loin

- Ajoute le glisser-déposer pour réordonner les étapes.
- Remplace `useFetch` par TanStack Query et compare le code obtenu.
- Passe le projet en TypeScript et type les actions du reducer avec une union discriminée.
- Écris des tests avec Vitest et React Testing Library pour le reducer et le formulaire.
- Branche l'application sur une API Laravel et remplace le `localStorage` par la base de données.

## Erreurs fréquentes

- **Commencer par l'interface sans modéliser les données.** Tu réécriras tout quand tu découvriras qu'il manque un champ.
- **Tout mettre dans le contexte.** Les filtres n'ont pas besoin d'être globaux.
- **Stocker des valeurs dérivées.** Progression, nombre de résultats, liste filtrée : calcule-les.
- **Écrire dans le `localStorage` dans le reducer.** Un reducer est pur ; la persistance est un effet.
- **Oublier le `try/catch` autour de `JSON.parse` et du stockage.** Une donnée corrompue provoquerait un écran blanc au démarrage.
- **Utiliser l'index comme `key`.** Les cases cochées se mélangent à la suppression d'un élément.
- **Tester uniquement en développement.** Le mode strict et le build de production révèlent des problèmes différents.

## Bonnes pratiques

- Avance par phases livrables et vérifie le navigateur après chacune.
- Écris la logique métier dans des fonctions pures, hors des composants.
- Garde les composants courts : au-delà d'une soixantaine de lignes, découpe.
- Traite systématiquement les états vide, de chargement et d'erreur.
- Mesure avant d'optimiser, et note les chiffres.
- Fais des commits fréquents et lisibles, par phase terminée.

## À retenir

- Un projet réussi commence par un **cahier des charges** et une **modélisation des données**.
- Sépare la logique pure (`lib/`, reducer) de l'interface : elle devient simple à tester et à faire évoluer.
- **Context + useReducer** suffisent pour un état global de taille moyenne ; la persistance est un **effet**.
- Ce qui se calcule ne se stocke pas : progression, filtres et totaux sont dérivés.
- L'accessibilité (labels, rôles, clavier, messages d'erreur) fait partie du travail fini.
- Le build de production, le découpage du code et la gestion des erreurs distinguent une démo d'une application livrable.
