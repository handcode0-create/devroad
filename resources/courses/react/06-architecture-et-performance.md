---
title: Architecture et performance
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application React qui fonctionne n'est pas forcément une application **maintenable** ni **rapide**. Quand le projet grandit, trois questions se posent : comment organiser les fichiers, comment partager des données sans passer des props à travers dix niveaux, et comment éviter que l'interface ne ralentisse. Ce chapitre répond aux trois.

À la fin du chapitre, tu seras capable de :

- organiser un projet par **fonctionnalités** et nommer clairement les dossiers ;
- partager des données globales avec **Context** et choisir quand l'utiliser ;
- simplifier un état complexe avec **useReducer** ;
- comprendre quand React refait un rendu et mesurer avant d'optimiser ;
- utiliser `memo`, `useMemo` et `useCallback` à bon escient ;
- charger du code à la demande avec `lazy` et `Suspense`, et gérer les erreurs avec une *error boundary*.

Prérequis : les chapitres sur les props, le state et les effets. Prévois deux heures et demie.

## Organiser un projet

Deux manières classiques de ranger les fichiers :

- **par type** : `components/`, `hooks/`, `pages/`, `utils/`. Simple au début, mais pour modifier une fonctionnalité tu ouvres cinq dossiers ;
- **par fonctionnalité** : tout ce qui concerne les roadmaps vit ensemble.

Voici une structure par fonctionnalité pour une application de suivi d'apprentissage :

```text
src/
├── components/          composants génériques (Button, Modal, Badge)
├── features/
│   ├── roadmaps/
│   │   ├── components/  CarteRoadmap.jsx, ListeRoadmaps.jsx
│   │   ├── hooks/       useRoadmaps.js
│   │   └── api.js       fonctions d'appel au serveur
│   └── progression/
│       ├── ProgressionContext.jsx
│       └── BarreProgression.jsx
├── hooks/               hooks réutilisables (useFetch, useLocalStorage)
├── layouts/             AppLayout.jsx
├── pages/               Accueil.jsx, Parcours.jsx
├── lib/                 fonctions utilitaires (formatDate, cn)
└── main.jsx
```

Quelques principes :

1. **Le code qui change ensemble vit ensemble.** Une fonctionnalité doit pouvoir être modifiée, voire supprimée, sans toucher dix dossiers ;
2. **Les composants génériques ne connaissent pas le métier.** Un `Button` ne sait rien des roadmaps ;
3. **Une page assemble**, elle n'implémente pas : elle appelle des composants de fonctionnalités ;
4. **Un fichier d'index** par dossier (`index.js`) peut simplifier les imports, mais sans excès : trop de réexportations ralentissent l'outil et rendent la navigation confuse.

Tu peux configurer un alias d'import dans Vite pour éviter les `../../../` :

```js
// vite.config.js
import { defineConfig } from 'vite';
import react from '@vitejs/plugin-react';
import path from 'node:path';

export default defineConfig({
  plugins: [react()],
  resolve: {
    alias: { '@': path.resolve(__dirname, 'src') },
  },
});
```

On écrit alors `import Badge from '@/components/Badge'`. C'est exactement le principe que DevRoad utilise avec son dossier `resources/js`.

## Context : partager sans percer

Certaines données sont nécessaires partout : l'utilisateur connecté, le thème, la langue. Les passer par props à chaque niveau est pénible. **Context** permet de les rendre disponibles à tout un sous-arbre.

Trois étapes : créer, fournir, consommer.

```jsx
// src/features/progression/ProgressionContext.jsx
import { createContext, useContext, useState } from 'react';

const ProgressionContext = createContext(null);

export function ProgressionProvider({ children }) {
  const [termines, setTermines] = useState([]);

  function basculer(id) {
    setTermines((courant) =>
      courant.includes(id)
        ? courant.filter((x) => x !== id)
        : [...courant, id]
    );
  }

  return (
    <ProgressionContext.Provider value={{ termines, basculer }}>
      {children}
    </ProgressionContext.Provider>
  );
}

export function useProgression() {
  const contexte = useContext(ProgressionContext);
  if (contexte === null) {
    throw new Error('useProgression doit être utilisé dans un ProgressionProvider');
  }
  return contexte;
}
```

On enveloppe l'application une fois :

```jsx
// main.jsx
<ProgressionProvider>
  <App />
</ProgressionProvider>
```

Et n'importe quel composant descendant lit la valeur :

```jsx
function BoutonChapitre({ id }) {
  const { termines, basculer } = useProgression();
  const fait = termines.includes(id);
  return (
    <button onClick={() => basculer(id)}>
      {fait ? 'Terminé' : 'Marquer comme terminé'}
    </button>
  );
}
```

Encapsuler `useContext` dans un hook (`useProgression`) est une bonne habitude : l'import est plus court et l'erreur plus claire si on oublie le fournisseur.

> **Attention** : quand la valeur du contexte change, **tous** les composants qui le consomment sont ré-affichés. Context convient bien à des données qui changent rarement (thème, utilisateur, langue). Pour un état qui bouge à chaque frappe, garde-le local ou utilise une bibliothèque d'état dédiée.

:::quiz
Quand Context est-il le plus adapté ?
- [ ] Pour stocker le texte d'un champ qui change à chaque frappe
- [x] Pour partager une donnée utilisée partout et qui change peu, comme le thème ou l'utilisateur connecté
- [ ] Pour remplacer entièrement les props
- [ ] Pour charger des données depuis une API
> Context évite de faire descendre une donnée à travers de nombreux niveaux, mais tous les consommateurs se ré-affichent à chaque changement : il convient aux valeurs stables.
:::

## useReducer : un état complexe, des actions claires

Quand un état comporte plusieurs champs qui évoluent ensemble ou que beaucoup de gestionnaires le modifient, `useState` devient confus. `useReducer` centralise les changements dans une fonction pure, le **reducer**, qui reçoit l'état courant et une **action** et retourne le nouvel état.

```jsx
import { useReducer } from 'react';

function reducer(etat, action) {
  switch (action.type) {
    case 'ajouter':
      return [...etat, { id: action.id, titre: action.titre, fait: false }];
    case 'basculer':
      return etat.map((t) =>
        t.id === action.id ? { ...t, fait: !t.fait } : t
      );
    case 'supprimer':
      return etat.filter((t) => t.id !== action.id);
    default:
      throw new Error(`Action inconnue : ${action.type}`);
  }
}

function Taches() {
  const [taches, dispatch] = useReducer(reducer, []);

  return (
    <>
      <button
        onClick={() =>
          dispatch({ type: 'ajouter', id: Date.now(), titre: 'Réviser les hooks' })
        }
      >
        Ajouter
      </button>
      <ul>
        {taches.map((t) => (
          <li key={t.id}>
            <label>
              <input
                type="checkbox"
                checked={t.fait}
                onChange={() => dispatch({ type: 'basculer', id: t.id })}
              />
              {t.titre}
            </label>
            <button onClick={() => dispatch({ type: 'supprimer', id: t.id })}>
              Supprimer
            </button>
          </li>
        ))}
      </ul>
    </>
  );
}
```

Les avantages : toute la logique de mise à jour est au même endroit, testable sans React (c'est une simple fonction), et les composants ne font que **décrire ce qui s'est passé** (`dispatch({ type: 'supprimer' })`). Combiné avec Context, `useReducer` donne une gestion d'état globale légère : le fournisseur expose `taches` et `dispatch`.

## Comprendre quand React refait un rendu

Pour optimiser, il faut d'abord comprendre. Un composant est ré-affiché quand :

1. son **état** change ;
2. son **parent** est ré-affiché (même si ses props sont identiques) ;
3. un **contexte** qu'il consomme change.

Le point 2 surprend : si un parent change d'état, tous ses enfants sont ré-exécutés. C'est presque toujours **sans conséquence**, car React est rapide : exécuter une fonction de composant coûte peu, et le DOM n'est modifié que si le résultat diffère. Il ne faut donc **pas optimiser par réflexe**.

Une première amélioration, souvent suffisante, consiste à **rapprocher l'état** de l'endroit où il est utilisé :

```jsx
// Lent : taper dans le champ ré-affiche toute la page
function Page() {
  const [recherche, setRecherche] = useState('');
  return (
    <>
      <input value={recherche} onChange={(e) => setRecherche(e.target.value)} />
      <GrosTableau />
    </>
  );
}

// Mieux : l'état vit dans un composant dédié
function ChampRecherche() {
  const [recherche, setRecherche] = useState('');
  return <input value={recherche} onChange={(e) => setRecherche(e.target.value)} />;
}

function Page() {
  return (
    <>
      <ChampRecherche />
      <GrosTableau />
    </>
  );
}
```

Autre technique sans aucun hook : passer le contenu lourd en `children`. Un composant qui reçoit des `children` déjà créés par son parent ne les recrée pas quand son propre état change.

## Mesurer avant d'optimiser

L'outil de référence est l'extension **React Developer Tools** et son onglet **Profiler**. Tu enregistres une interaction (taper dans un champ), puis tu observes quels composants ont été ré-affichés et combien de temps cela a pris. Si rien n'est lent à l'usage, il n'y a rien à optimiser.

> **Astuce** : active dans les outils du navigateur la limitation du processeur (« CPU 4x slowdown ») pour simuler un téléphone d'entrée de gamme, très courant auprès de ton audience.

## memo, useMemo, useCallback

Quand le profileur révèle un vrai problème, trois outils existent.

**`memo`** enveloppe un composant : il n'est ré-affiché que si ses props ont changé (comparaison superficielle).

```jsx
import { memo } from 'react';

const LigneChapitre = memo(function LigneChapitre({ chapitre, onBasculer }) {
  return (
    <li>
      <button onClick={() => onBasculer(chapitre.id)}>{chapitre.titre}</button>
    </li>
  );
});
```

**`useMemo`** mémorise le **résultat d'un calcul coûteux** tant que ses dépendances ne changent pas :

```jsx
const statistiques = useMemo(
  () => calculerStatistiques(chapitres),   // calcul lourd
  [chapitres]
);
```

**`useCallback`** mémorise une **fonction**, pour que sa référence reste stable d'un rendu à l'autre :

```jsx
const basculer = useCallback((id) => {
  setChapitres((courant) =>
    courant.map((c) => (c.id === id ? { ...c, fait: !c.fait } : c))
  );
}, []);
```

Pourquoi la stabilité compte-t-elle ? Sans `useCallback`, `basculer` est une **nouvelle fonction** à chaque rendu : pour `memo`, la prop a « changé », et l'optimisation est annulée. Les trois outils vont donc ensemble : `memo` sur l'enfant, `useCallback` pour les fonctions passées, `useMemo` pour les objets et calculs passés ou coûteux.

Mais attention : mémoriser a un coût (comparaison, mémoire) et complexifie le code. Utilise-les **uniquement** si le profileur montre un problème, ou pour des listes longues, des calculs réellement lourds, ou des composants enfants coûteux.

:::quiz
Quelle est la bonne démarche pour optimiser les performances d'une interface React ?
- [ ] Envelopper tous les composants dans `memo` dès le départ
- [ ] Utiliser `useMemo` pour chaque variable
- [x] Mesurer avec le Profiler, rapprocher l'état, puis mémoriser seulement ce qui pose problème
- [ ] Remplacer `useState` par `useReducer`
> L'optimisation prématurée alourdit le code sans bénéfice. On mesure d'abord, on corrige la structure (état plus proche), puis on mémorise ce qui est réellement coûteux.
:::

## Charger le code à la demande

Plus l'application grandit, plus le fichier JavaScript envoyé au navigateur grossit, ce qui pénalise les connexions lentes. Le **découpage du code** (*code splitting*) consiste à ne charger une page ou un composant lourd que lorsqu'il devient nécessaire. React fournit `lazy` et `Suspense`, et Vite génère automatiquement un fichier séparé pour chaque import dynamique :

```jsx
import { lazy, Suspense } from 'react';

const Editeur = lazy(() => import('./features/editeur/Editeur'));

function PageNotes() {
  return (
    <Suspense fallback={<p>Chargement de l'éditeur…</p>}>
      <Editeur />
    </Suspense>
  );
}
```

`lazy` attend un import dynamique d'un module dont l'**export par défaut** est le composant. Le `fallback` s'affiche pendant le téléchargement. Les bons candidats : les pages rarement visitées, les modales lourdes, les bibliothèques de graphiques ou d'éditeurs.

## Gérer les erreurs avec une error boundary

Si un composant lève une exception pendant le rendu, tout l'arbre disparaît et l'écran devient blanc. Une **error boundary** intercepte l'erreur et affiche un secours. En React 18, c'est encore un composant de classe (ou on utilise la bibliothèque `react-error-boundary`) :

```jsx
import { Component } from 'react';

class ErrorBoundary extends Component {
  state = { erreur: null };

  static getDerivedStateFromError(erreur) {
    return { erreur };
  }

  componentDidCatch(erreur, info) {
    console.error('Erreur de rendu', erreur, info.componentStack);
  }

  render() {
    if (this.state.erreur) {
      return <p role="alert">Une erreur est survenue. Recharge la page.</p>;
    }
    return this.props.children;
  }
}
```

On entoure les zones à risque : `<ErrorBoundary><Editeur /></ErrorBoundary>`. Elle ne capte **pas** les erreurs des gestionnaires d'événements ni du code asynchrone : pour ceux-là, utilise `try/catch`.

## Atelier guidé : réorganiser et accélérer

Compte deux heures. Pars d'un projet React avec quelques composants (ceux des chapitres précédents).

1. Crée les dossiers `features/roadmaps` et `features/progression` et déplace-y les composants concernés. Corrige les imports.
2. Configure l'alias `@` dans `vite.config.js` et dans `jsconfig.json`, puis remplace les chemins relatifs profonds.
3. Crée `ProgressionContext` avec un fournisseur, le hook `useProgression` et l'erreur explicite hors fournisseur.
4. Affiche un `BoutonChapitre` dans trois endroits distincts de l'application, tous synchronisés grâce au contexte.
5. Remplace l'état du contexte par un `useReducer` avec les actions `basculer` et `reinitialiser`.
6. Génère une liste de 2 000 chapitres. Avec le Profiler, enregistre une frappe dans la recherche et note le temps.
7. Déplace l'état de la recherche dans un composant dédié, puis compare à nouveau.
8. Enveloppe `LigneChapitre` dans `memo` et stabilise le gestionnaire avec `useCallback` ; mesure encore.
9. Charge la page « Statistiques » avec `lazy` et `Suspense` ; vérifie dans l'onglet Réseau qu'un fichier séparé se télécharge à l'ouverture.
10. Entoure cette page d'une `ErrorBoundary` et provoque volontairement une erreur pour tester le secours.

Auto-évaluation :

- Peux-tu supprimer une fonctionnalité en n'effaçant qu'un dossier ?
- Ton hook de contexte lève-t-il une erreur claire hors fournisseur ?
- As-tu un chiffre avant/après pour chaque optimisation ?
- Le code splitting produit-il bien un fichier distinct dans le build ?

:::quiz
Que se passe-t-il quand la valeur d'un Context change ?
- [ ] Seul le composant qui l'a modifiée est ré-affiché
- [ ] Rien, il faut recharger la page
- [x] Tous les composants qui consomment ce contexte sont ré-affichés
- [ ] Les composants parents sont détruits
> Chaque composant qui appelle `useContext` sur ce contexte est ré-affiché quand la valeur du fournisseur change.
:::

## Erreurs fréquentes

- **Mettre tout dans un contexte global.** Les consommateurs se ré-affichent sans cesse ; garde l'état local par défaut.
- **Créer la valeur du fournisseur à chaque rendu sans nécessité.** `value={{ a, b }}` est un nouvel objet à chaque fois ; pour des contextes très consommés, mémorise-le avec `useMemo`.
- **Muter l'état dans un reducer.** Un reducer doit retourner un nouvel objet ou tableau.
- **Utiliser `memo` avec des props instables.** Sans `useCallback` sur les fonctions et `useMemo` sur les objets, `memo` ne sert à rien.
- **Utiliser `lazy` avec un export nommé.** `lazy` attend un export par défaut.
- **Oublier `Suspense`.** Un composant paresseux sans `Suspense` lève une erreur.
- **Optimiser sans mesurer.** On complexifie le code pour un gain invisible.

## Bonnes pratiques

- Organise par fonctionnalité, garde `components/` pour le générique.
- Garde l'état aussi local que possible ; remonte-le seulement si nécessaire.
- Encapsule chaque contexte dans un fournisseur et un hook dédié.
- Utilise `useReducer` quand les transitions d'état sont nombreuses ou liées.
- Mesure avec le Profiler et un processeur ralenti avant toute optimisation.
- Découpe le code par page, et ajoute des error boundaries autour des zones critiques.

## À retenir

- Une structure **par fonctionnalité** garde ensemble le code qui change ensemble.
- **Context** évite le *prop drilling* pour des données globales et stables ; il ré-affiche tous ses consommateurs.
- **useReducer** centralise les transitions d'état dans une fonction pure pilotée par des actions.
- Un composant est ré-affiché quand son état, son parent ou un contexte change ; rapprocher l'état est la première optimisation.
- `memo`, `useMemo` et `useCallback` se combinent et ne s'utilisent qu'après mesure.
- `lazy` et `Suspense` chargent le code à la demande ; une **error boundary** évite l'écran blanc.
