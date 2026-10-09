---
title: Effets et données
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Un composant React calcule de l'interface à partir de props et d'état. Mais une vraie application doit aussi **parler au monde extérieur** : appeler une API, s'abonner à un événement du navigateur, lancer un minuteur, mettre à jour le titre de l'onglet. Ces actions ne font pas partie du « calcul de l'interface » : on les appelle des **effets de bord**, et React les gère avec le hook `useEffect`.

À la fin du chapitre, tu seras capable de :

- distinguer ce qui relève du rendu et ce qui relève d'un effet ;
- écrire un `useEffect` avec son **tableau de dépendances** ;
- **nettoyer** un effet (abonnement, minuteur, requête en cours) ;
- charger des données depuis une API avec `fetch`, en gérant les états **chargement / erreur / succès** ;
- éviter les « conditions de course » et les effets inutiles ;
- extraire une logique réutilisable dans un **hook personnalisé**.

Prérequis : les chapitres sur les props et le state. Prévois deux heures et demie. Tu as besoin d'un projet Vite qui tourne.

## Rendu pur, effets séparés

Le corps d'un composant doit être **pur** : avec les mêmes props et le même état, il produit le même JSX, sans rien modifier à l'extérieur. Voici ce qu'il ne faut pas faire :

```jsx
function Mauvais() {
  document.title = 'DevRoad';          // modifie le monde extérieur pendant le rendu
  fetch('/api/roadmaps');              // lance une requête à chaque rendu
  return <h1>Salut</h1>;
}
```

Le composant peut être rendu plusieurs fois (React 18 en mode strict le fait volontairement en développement pour détecter ces problèmes). Chaque rendu relancerait la requête. Les actions qui touchent à l'extérieur doivent être déclarées dans un **effet**, que React exécute **après** avoir affiché le composant.

Deux grandes familles d'actions existent :

- celles qui répondent à un **événement** (clic, envoi de formulaire) : elles vont dans un gestionnaire d'événement, pas dans un effet ;
- celles qui doivent rester **synchronisées** avec ce qui est affiché (charger les données d'une page, s'abonner à une source) : elles vont dans un effet.

## useEffect : syntaxe et dépendances

```jsx
import { useEffect, useState } from 'react';

function TitrePage({ titre }) {
  useEffect(() => {
    document.title = `${titre} | DevRoad`;
  }, [titre]);

  return <h1>{titre}</h1>;
}
```

`useEffect` prend deux arguments :

1. une **fonction** : le code de l'effet, exécuté après l'affichage ;
2. un **tableau de dépendances** : la liste des valeurs (props, état, variables du composant) que l'effet utilise.

React compare le tableau à celui du rendu précédent. L'effet est ré-exécuté **seulement si une dépendance a changé**. Trois cas :

| Tableau | Quand l'effet s'exécute |
| --- | --- |
| `[titre]` | Après le premier affichage, puis quand `titre` change |
| `[]` | Une seule fois, après le premier affichage |
| absent | Après chaque rendu (presque jamais voulu) |

Règle : **tout ce que l'effet lit et qui peut changer doit figurer dans les dépendances**. L'extension ESLint `react-hooks/exhaustive-deps`, activée dans les projets Vite, te signale les oublis. Ne la fais pas taire : elle protège de bugs où l'effet utilise une valeur périmée.

:::quiz
Que signifie un tableau de dépendances vide `[]` dans `useEffect(() => { ... }, [])` ?
- [ ] L'effet ne s'exécute jamais
- [ ] L'effet s'exécute après chaque rendu
- [x] L'effet s'exécute une seule fois, après le premier affichage
- [ ] L'effet s'exécute à chaque clic
> Sans dépendance à surveiller, React n'a aucune raison de relancer l'effet : il s'exécute une fois au montage du composant.
:::

## Le nettoyage

Certains effets créent quelque chose qu'il faut **défaire** : un minuteur, un abonnement, un écouteur d'événement. L'effet peut alors retourner une **fonction de nettoyage**, appelée avant chaque ré-exécution et quand le composant disparaît :

```jsx
function Chrono() {
  const [secondes, setSecondes] = useState(0);

  useEffect(() => {
    const id = setInterval(() => {
      setSecondes((s) => s + 1);
    }, 1000);

    return () => clearInterval(id);   // nettoyage
  }, []);

  return <p>Session : {secondes} s</p>;
}
```

Sans nettoyage, chaque affichage du composant empilerait un minuteur supplémentaire, et un composant démonté continuerait à tourner. Même principe pour un écouteur :

```jsx
useEffect(() => {
  function onResize() {
    setLargeur(window.innerWidth);
  }
  window.addEventListener('resize', onResize);
  return () => window.removeEventListener('resize', onResize);
}, []);
```

> **À retenir** : en mode développement avec `<React.StrictMode>`, React monte, démonte puis remonte chaque composant une fois. Si ton effet est bien écrit (avec son nettoyage), tu ne vois aucune différence ; sinon, tu découvres le bug avant la production.

## Charger des données depuis une API

Le cas le plus courant : afficher des données venues du serveur. Une requête a trois issues possibles, et ton interface doit les représenter toutes : **chargement**, **erreur**, **succès**.

```jsx
function ListeRoadmaps() {
  const [roadmaps, setRoadmaps] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    const controleur = new AbortController();

    async function charger() {
      try {
        setChargement(true);
        setErreur(null);
        const reponse = await fetch('/api/roadmaps', {
          signal: controleur.signal,
        });
        if (!reponse.ok) {
          throw new Error(`Erreur serveur (${reponse.status})`);
        }
        const donnees = await reponse.json();
        setRoadmaps(donnees);
      } catch (e) {
        if (e.name !== 'AbortError') setErreur(e.message);
      } finally {
        if (!controleur.signal.aborted) setChargement(false);
      }
    }

    charger();
    return () => controleur.abort();
  }, []);

  if (chargement) return <p>Chargement…</p>;
  if (erreur) return <p role="alert">Impossible de charger : {erreur}</p>;
  if (roadmaps.length === 0) return <p>Aucune roadmap.</p>;

  return (
    <ul>
      {roadmaps.map((r) => <li key={r.id}>{r.titre}</li>)}
    </ul>
  );
}
```

Plusieurs détails importants :

- `fetch` ne rejette **pas** la promesse pour une réponse 404 ou 500 : c'est à toi de tester `reponse.ok` ;
- la fonction passée à `useEffect` ne peut pas être `async` directement (elle doit retourner une fonction de nettoyage ou rien, pas une promesse). On déclare donc une fonction `async` à l'intérieur, qu'on appelle ;
- `AbortController` permet d'**annuler** la requête si le composant disparaît avant la réponse ;
- les trois retours anticipés rendent l'interface lisible : un état par situation.

### La condition de course

Imagine une recherche : l'utilisateur tape « re », puis « react ». Deux requêtes partent. Si la première répond **après** la seconde, tu affiches les résultats de « re » alors que le champ affiche « react ». C'est une **condition de course**. Le nettoyage de l'effet règle le problème : à chaque nouvelle saisie, la requête précédente est annulée.

```jsx
useEffect(() => {
  if (recherche.length < 2) return;
  const controleur = new AbortController();

  fetch(`/api/roadmaps?q=${encodeURIComponent(recherche)}`, {
    signal: controleur.signal,
  })
    .then((r) => r.json())
    .then(setResultats)
    .catch((e) => {
      if (e.name !== 'AbortError') console.error(e);
    });

  return () => controleur.abort();
}, [recherche]);
```

`encodeURIComponent` protège l'URL contre les caractères spéciaux. Dans une vraie application, on ajoute en plus un **délai** (*debounce*) pour ne pas envoyer une requête à chaque frappe.

> **Astuce** : dans un projet Laravel + Inertia comme DevRoad, les données de page arrivent déjà en props depuis le contrôleur : tu n'as pas besoin de `useEffect` pour les charger. Les effets servent alors pour les appels complémentaires (recherche en direct, notifications) et pour les API du navigateur.

:::quiz
Pourquoi retourne-t-on `() => controleur.abort()` dans l'effet qui appelle `fetch` ?
- [ ] Pour accélérer la requête
- [x] Pour annuler la requête si le composant disparaît ou si les dépendances changent avant la réponse
- [ ] Pour mettre en cache la réponse
- [ ] Parce que `fetch` l'exige
> La fonction de nettoyage s'exécute avant la prochaine exécution de l'effet et au démontage : elle annule la requête devenue inutile et évite les conditions de course.
:::

## Tu n'as peut-être pas besoin d'un effet

L'erreur classique du débutant : utiliser `useEffect` pour tout. Un effet sert à synchroniser avec l'extérieur, pas à transformer des données.

```jsx
// À éviter : état dérivé calculé dans un effet
const [visibles, setVisibles] = useState([]);
useEffect(() => {
  setVisibles(roadmaps.filter((r) => r.niveau === niveau));
}, [roadmaps, niveau]);

// Correct : simple calcul pendant le rendu
const visibles = roadmaps.filter((r) => r.niveau === niveau);
```

La première version provoque un rendu supplémentaire et un état en double. Autres cas où l'effet est inutile :

- **réagir à un clic** : mets le code dans le gestionnaire ;
- **réinitialiser un état quand une prop change** : donne une `key` différente au composant ;
- **envoyer un formulaire** : c'est un événement, pas un effet.

Pose-toi la question : « *Qu'est-ce qui provoque ce code ?* ». Si c'est une action de l'utilisateur, c'est un événement. Si c'est « ce composant est affiché à l'écran », c'est un effet.

## Les hooks personnalisés

Quand plusieurs composants répètent la même logique (état + effet), on l'extrait dans un **hook personnalisé** : une fonction dont le nom commence par `use` et qui peut appeler d'autres hooks.

```jsx
// src/hooks/useFetch.js
import { useEffect, useState } from 'react';

export function useFetch(url) {
  const [donnees, setDonnees] = useState(null);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    const controleur = new AbortController();
    setChargement(true);
    setErreur(null);

    fetch(url, { signal: controleur.signal })
      .then((r) => {
        if (!r.ok) throw new Error(`Erreur ${r.status}`);
        return r.json();
      })
      .then(setDonnees)
      .catch((e) => {
        if (e.name !== 'AbortError') setErreur(e.message);
      })
      .finally(() => {
        if (!controleur.signal.aborted) setChargement(false);
      });

    return () => controleur.abort();
  }, [url]);

  return { donnees, chargement, erreur };
}
```

Utilisation, bien plus lisible :

```jsx
function Roadmaps() {
  const { donnees, chargement, erreur } = useFetch('/api/roadmaps');

  if (chargement) return <p>Chargement…</p>;
  if (erreur) return <p role="alert">{erreur}</p>;
  return <ul>{donnees.map((r) => <li key={r.id}>{r.titre}</li>)}</ul>;
}
```

Chaque composant qui appelle le hook obtient **son propre état** : les hooks partagent la logique, pas les données. Un autre exemple classique est un hook qui mémorise une valeur dans le `localStorage` :

```jsx
export function useLocalStorage(cle, valeurInitiale) {
  const [valeur, setValeur] = useState(() => {
    try {
      const stocke = localStorage.getItem(cle);
      return stocke ? JSON.parse(stocke) : valeurInitiale;
    } catch {
      return valeurInitiale;
    }
  });

  useEffect(() => {
    localStorage.setItem(cle, JSON.stringify(valeur));
  }, [cle, valeur]);

  return [valeur, setValeur];
}
```

Remarque `useState(() => ...)` : on passe une fonction pour que la lecture du stockage ne se fasse **qu'au premier rendu** (initialisation paresseuse).

Pour des besoins avancés (cache, rechargement, pagination), des bibliothèques comme TanStack Query remplacent avantageusement un `useFetch` maison. Comprendre la mécanique d'abord te permettra de les utiliser sans magie.

## Atelier guidé : un catalogue chargé depuis une API

Compte deux heures. Utilise l'API publique JSONPlaceholder (`https://jsonplaceholder.typicode.com/posts`) comme source, ou un fichier `public/roadmaps.json` que tu crées.

1. Crée `src/hooks/useFetch.js` avec le code ci-dessus.
2. Crée un composant `Catalogue` qui appelle `useFetch` et affiche les trois cas : chargement, erreur, liste.
3. Teste l'erreur : change l'URL pour une adresse inexistante et vérifie le message affiché.
4. Ajoute un champ de recherche contrôlé et filtre la liste par **calcul pendant le rendu**, sans effet.
5. Ajoute un `useEffect` qui met à jour `document.title` avec « Catalogue (N résultats) ».
6. Crée un composant `Horloge` avec un minuteur nettoyé, puis affiche-le et masque-le avec un bouton pour vérifier que le minuteur s'arrête.
7. Crée `useLocalStorage` et mémorise le texte de recherche entre deux visites.
8. Ouvre l'onglet Réseau des outils de développement, passe en « Slow 3G » et observe l'état de chargement.
9. Ajoute un délai de 300 ms avant de lancer une recherche serveur (debounce avec `setTimeout` nettoyé dans l'effet).

Auto-évaluation :

- Chaque effet a-t-il les bonnes dépendances, sans avertissement ESLint ?
- Les minuteurs et écouteurs sont-ils nettoyés ?
- Les trois états (chargement, erreur, succès) sont-ils gérés ?
- As-tu évité les effets qui ne font que transformer des données ?

:::quiz
Où faut-il placer le code qui envoie un formulaire ?
- [ ] Dans un `useEffect` sans dépendances
- [x] Dans un gestionnaire d'événement `onSubmit`
- [ ] Directement dans le corps du composant
- [ ] Dans la fonction de nettoyage
> L'envoi est la conséquence d'une action de l'utilisateur : c'est un événement. Un effet sert à synchroniser le composant affiché avec l'extérieur.
:::

## Erreurs fréquentes

- **Oublier le tableau de dépendances.** L'effet s'exécute après chaque rendu et peut créer une boucle infinie (effet qui change l'état qui relance l'effet).
- **Mettre un objet ou une fonction recréés à chaque rendu dans les dépendances.** L'effet se relance sans cesse : sors la valeur du composant ou stabilise-la.
- **Rendre la fonction de l'effet `async`.** Déclare une fonction asynchrone interne.
- **Ne pas tester `response.ok`.** Un 500 est traité comme un succès et `json()` échoue.
- **Oublier le nettoyage.** Fuites de mémoire, doublons d'écouteurs, mises à jour sur un composant démonté.
- **Utiliser un effet pour dériver des données.** Calcule pendant le rendu.
- **Ignorer l'avertissement `exhaustive-deps`.** C'est presque toujours un vrai bug en attente.

## Bonnes pratiques

- Un effet = **une seule responsabilité** ; sépare les effets indépendants.
- Représente toujours les états de chargement, d'erreur et de liste vide.
- Annule les requêtes obsolètes avec `AbortController`.
- Extrais la logique répétée dans des hooks personnalisés nommés `useXxx`, rangés dans `src/hooks/`.
- Préfère un gestionnaire d'événement à un effet chaque fois que c'est possible.
- Pour des applications riches en données, envisage une bibliothèque dédiée (TanStack Query) ou, avec Inertia, les props de page.

## À retenir

- Le rendu est pur ; les **effets de bord** vivent dans `useEffect`, exécuté après l'affichage.
- Le **tableau de dépendances** décide quand l'effet se relance : `[]` une fois, `[x]` quand `x` change.
- La **fonction de nettoyage** défait ce que l'effet a créé (minuteur, écouteur, requête).
- Une requête a trois états à gérer : chargement, erreur, succès ; pense aussi à `response.ok`.
- `AbortController` évite les conditions de course et les requêtes inutiles.
- Ne crée pas d'effet pour calculer une valeur ou répondre à un clic.
- Un **hook personnalisé** partage de la logique, pas des données.
