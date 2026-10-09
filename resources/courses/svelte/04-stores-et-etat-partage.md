---
title: Stores et état partagé
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, ton état vivait dans un seul composant, transmis aux enfants par les props. Dès qu'une application grandit, plusieurs composants éloignés doivent lire et modifier la même donnée : le panier, l'utilisateur connecté, le thème clair ou sombre, une liste de notifications. Passer ces valeurs de parent en enfant sur dix niveaux devient pénible. Ce chapitre présente les outils de Svelte pour **partager l'état**.

À la fin du chapitre, tu seras capable de :

- reconnaître quand les props ne suffisent plus et choisir un mécanisme de partage ;
- créer un état partagé avec un fichier `.svelte.js` et la rune `$state` ;
- encapsuler la logique dans une **classe** réactive ;
- utiliser les **stores** classiques (`writable`, `readable`, `derived`) et la syntaxe `$store` ;
- utiliser le **contexte** (`setContext` et `getContext`) pour un état propre à une partie de l'arbre ;
- persister un état dans `localStorage` ;
- éviter le piège de l'état partagé côté serveur dans SvelteKit.

Prérequis : les chapitres 1 à 3. Prévois deux heures et demie.

## Le problème des props qui descendent trop loin

Imagine une application avec un en-tête qui affiche le nombre d'articles du panier, une page produit qui ajoute des articles, et une page de paiement qui lit le total. Si l'état `panier` vit dans le composant racine, tu dois le passer à chaque niveau intermédiaire, même à ceux qui n'en ont pas besoin. On appelle ça le **prop drilling**.

Trois solutions existent, de la plus simple à la plus ciblée :

| Solution | Quand l'utiliser |
| --- | --- |
| **Module `.svelte.js` avec `$state`** | État global simple et moderne (Svelte 5) |
| **Stores (`writable`...)** | Code existant, bibliothèques, flux asynchrones |
| **Contexte** | État propre à un sous-arbre ou à une requête |

> **À retenir** : avant de partager, vérifie que l'état doit vraiment l'être. Si un seul composant et ses enfants directs l'utilisent, les props restent la meilleure option.

## Un état partagé avec un module .svelte.js

Dans Svelte 5, les runes fonctionnent aussi en dehors des composants, à condition que le fichier se termine par **`.svelte.js`** (ou `.svelte.ts`). Le compilateur sait alors les traiter.

Crée `src/lib/panier.svelte.js` :

```js
export const panier = $state({
  articles: [],
  ajouter(article) {
    const existant = this.articles.find((a) => a.id === article.id);
    if (existant) {
      existant.quantite += 1;
    } else {
      this.articles.push({ ...article, quantite: 1 });
    }
  },
  retirer(id) {
    this.articles = this.articles.filter((a) => a.id !== id);
  },
  vider() {
    this.articles = [];
  }
});
```

Dans n'importe quel composant :

```svelte
<script>
  import { panier } from '$lib/panier.svelte.js';
</script>

<button onclick={() => panier.ajouter({ id: 1, nom: 'Clavier', prix: 15000 })}>
  Ajouter ({panier.articles.length})
</button>
```

Tous les composants qui importent `panier` partagent le même objet réactif. Quand l'un l'ajoute, tous les autres se mettent à jour.

### Une limite à connaître

Tu ne peux pas **exporter une variable réassignée** directement :

```js
// Ne fonctionne pas comme prévu
export let compteur = $state(0);
// ailleurs : compteur++ dans un autre module est impossible
```

Les modules ES exportent la valeur au moment de l'import, pas une référence modifiable. La solution : exporter un **objet** dont on modifie les propriétés (comme `panier` ci-dessus), ou exporter des **fonctions** (getters et setters).

## Une classe réactive

Les classes sont idéales pour regrouper données et méthodes. Les champs déclarés avec `$state` et `$derived` deviennent réactifs :

```js
// src/lib/panier.svelte.js
export class Panier {
  articles = $state([]);

  total = $derived(
    this.articles.reduce((somme, a) => somme + a.prix * a.quantite, 0)
  );

  nombre = $derived(
    this.articles.reduce((somme, a) => somme + a.quantite, 0)
  );

  ajouter(article) {
    const existant = this.articles.find((a) => a.id === article.id);
    if (existant) existant.quantite += 1;
    else this.articles.push({ ...article, quantite: 1 });
  }

  retirer(id) {
    this.articles = this.articles.filter((a) => a.id !== id);
  }
}

export const panier = new Panier();
```

```svelte
<script>
  import { panier } from '$lib/panier.svelte.js';
</script>

<p>{panier.nombre} article(s) : {panier.total} FCFA</p>
```

> **Attention** : si tu passes une méthode comme gestionnaire (`onclick={panier.vider}`), `this` est perdu. Écris une flèche : `onclick={() => panier.vider()}`, ou définis la méthode comme champ fléché (`vider = () => { ... }`).

:::quiz
Dans quel type de fichier peut-on utiliser les runes `$state` et `$derived` en dehors d'un composant ?
- [ ] Un fichier `.js` ordinaire
- [x] Un fichier dont le nom se termine par `.svelte.js` ou `.svelte.ts`
- [ ] Uniquement dans `+layout.svelte`
- [ ] Les runes ne fonctionnent que dans les composants
> Le compilateur Svelte traite les fichiers `.svelte.js` et `.svelte.ts` pour y activer les runes.
:::

## Les stores : l'approche classique

Avant Svelte 5, le partage d'état passait par les **stores**, du module `svelte/store`. Ils restent supportés, très utilisés dans des projets existants, et utiles pour les flux asynchrones. Un store est un objet avec une méthode `subscribe`.

### writable

```js
// src/lib/stores.js
import { writable } from 'svelte/store';

export const theme = writable('clair');
```

Tu peux le modifier avec `set` ou `update` :

```js
theme.set('sombre');
theme.update((actuel) => (actuel === 'clair' ? 'sombre' : 'clair'));
```

Dans un composant, le préfixe **`$`** devant le nom du store s'abonne automatiquement et se désabonne à la destruction du composant :

```svelte
<script>
  import { theme } from '$lib/stores.js';
</script>

<p>Thème : {$theme}</p>
<button onclick={() => $theme = $theme === 'clair' ? 'sombre' : 'clair'}>
  Basculer
</button>
```

Écrire `$theme = ...` appelle `theme.set(...)`. Attention à ne pas confondre ce `$theme` (un store) avec les runes comme `$state` : une rune est suivie de parenthèses et appartient au langage, un `$nom` de store référence une variable importée ou déclarée au premier niveau du composant.

### readable et derived

```js
import { readable, derived, writable } from 'svelte/store';

// Un store en lecture seule qui se met à jour toute seule
export const heure = readable(new Date(), (set) => {
  const id = setInterval(() => set(new Date()), 1000);
  return () => clearInterval(id);
});

const prixUnitaire = writable(2500);
const quantite = writable(2);

// Un store calculé à partir d'autres stores
export const total = derived(
  [prixUnitaire, quantite],
  ([$prix, $quantite]) => $prix * $quantite
);
```

`readable` reçoit une fonction de démarrage appelée au premier abonné, qui renvoie une fonction d'arrêt appelée quand il n'y en a plus. `derived` recalcule dès qu'une source change.

### Un store personnalisé

Tu peux envelopper un `writable` pour n'exposer que les actions utiles :

```js
import { writable } from 'svelte/store';

function creerCompteur() {
  const { subscribe, update, set } = writable(0);
  return {
    subscribe,
    incrementer: () => update((n) => n + 1),
    reinitialiser: () => set(0)
  };
}

export const compteur = creerCompteur();
```

Dans un composant : `{$compteur}` et `onclick={compteur.incrementer}`. L'important est de fournir la méthode `subscribe`.

### Stores ou runes ?

Pour un nouveau projet Svelte 5, les runes dans un module `.svelte.js` sont plus simples : pas de `$` d'abonnement, pas de `.set` ni `.update`, une syntaxe homogène dans les composants et les fichiers partagés. Garde les stores pour les bibliothèques qui les exposent (`$page` en Svelte 4 / SvelteKit 1, par exemple), pour les flux asynchrones avec démarrage et arrêt, ou pour les projets déjà en place. Sache aussi que SvelteKit 2 propose maintenant `page` depuis `$app/state`, utilisable sans `$`, à la place de `$app/stores`.

:::quiz
En Svelte, que fait `$compteur` dans un composant où `compteur` est un store importé ?
- [ ] Il crée un nouvel état réactif
- [ ] Il déclare une rune
- [x] Il lit la valeur du store et s'y abonne automatiquement
- [ ] Il supprime le store
> Le préfixe `$` devant un store appelle `subscribe` pour lire la valeur courante et se désabonner à la destruction du composant ; l'affecter appelle `set`.
:::

## Le contexte : un état pour un sous-arbre

Un état global dans un module est **unique** pour toute l'application. Mais parfois tu veux un état partagé uniquement entre un composant et ses descendants, avec une instance différente pour chaque usage (un système d'onglets, un formulaire en plusieurs étapes). Le **contexte** répond à ce besoin.

```svelte
<!-- Onglets.svelte -->
<script>
  import { setContext } from 'svelte';

  let { children } = $props();

  const onglets = $state({ actif: 'a' });
  setContext('onglets', onglets);
</script>

<div class="onglets">{@render children()}</div>
```

```svelte
<!-- Onglet.svelte -->
<script>
  import { getContext } from 'svelte';

  let { id, titre } = $props();
  const onglets = getContext('onglets');
</script>

<button class:actif={onglets.actif === id} onclick={() => onglets.actif = id}>
  {titre}
</button>
```

`setContext` doit être appelé pendant l'initialisation du composant (pas dans un gestionnaire). Les descendants, à n'importe quelle profondeur, retrouvent l'objet avec `getContext`. Comme c'est un objet `$state`, il reste réactif.

> **Astuce** : utilise un symbole ou une constante pour la clé du contexte, afin d'éviter les fautes de frappe : `const CLE = Symbol('onglets')`.

## Persister avec localStorage

Un thème ou un panier doit souvent survivre à un rechargement. Voici une petite classe qui lit et écrit dans `localStorage`, sans casser le rendu serveur :

```js
// src/lib/preferences.svelte.js
import { browser } from '$app/environment';

class Preferences {
  theme = $state('clair');

  constructor() {
    if (browser) {
      this.theme = localStorage.getItem('theme') ?? 'clair';
    }
  }

  basculer() {
    this.theme = this.theme === 'clair' ? 'sombre' : 'clair';
    if (browser) localStorage.setItem('theme', this.theme);
  }
}

export const preferences = new Preferences();
```

`browser` (depuis `$app/environment`) est vrai uniquement dans le navigateur. Sans cette protection, `localStorage` provoquerait une erreur sur le serveur où il n'existe pas.

### Le piège SSR : l'état global fuit entre utilisateurs

C'est le point le plus important de ce chapitre. Dans SvelteKit, le code serveur tourne dans **un seul processus** qui sert tous les visiteurs. Un module `.svelte.js` qui crée `export const panier = new Panier()` est donc **partagé entre tous les utilisateurs** pendant le rendu serveur. Si tu y mets des données personnelles (le profil d'un utilisateur), elles peuvent apparaître dans la réponse d'un autre visiteur.

Règles de sécurité :

- sur le serveur, ne modifie jamais un état global dépendant de l'utilisateur ;
- charge les données personnelles dans les fonctions `load` (elles reçoivent la requête) et passe-les par `data` ;
- pour un état propre à une requête ou à un utilisateur, crée l'instance dans un composant racine et distribue-la avec le **contexte**.

## Atelier guidé : un panier partagé avec thème persistant

Compte deux heures. Tu peux continuer dans le projet SvelteKit des chapitres précédents.

1. Crée `src/lib/panier.svelte.js` avec une classe `Panier` contenant `articles = $state([])`.
2. Ajoute les valeurs dérivées `total` et `nombre` avec `$derived`.
3. Ajoute les méthodes `ajouter`, `retirer` et `vider`.
4. Crée un composant `EnTete.svelte` qui affiche `panier.nombre` et lis-le dans la mise en page `+layout.svelte`.
5. Crée un composant `CarteProduit.svelte` avec un bouton « Ajouter au panier » qui appelle `panier.ajouter(produit)`.
6. Affiche une page `/panier` listant les articles avec un bouton « Retirer » et le total en FCFA.
7. Crée `preferences.svelte.js` avec un thème persistant dans `localStorage` et protégé par `browser`.
8. Ajoute un bouton « Basculer le thème » et applique une classe sur la balise racine avec `class:sombre`.
9. Pour comparer, recrée le compteur de la section précédente avec un store `writable` et la syntaxe `$compteur`.
10. Écris dans un commentaire une règle d'équipe : « aucune donnée utilisateur dans un module global côté serveur ».

Auto-évaluation : explique pourquoi un état global de panier dans un module est acceptable côté navigateur, mais dangereux s'il contient le profil de l'utilisateur lors du rendu serveur.

## Erreurs fréquentes

- **Exporter une primitive `$state` réassignée.** Exporte un objet, une classe ou des fonctions.
- **Oublier l'extension `.svelte.js`.** Sans elle, les runes ne sont pas compilées et tu obtiens une erreur.
- **Perdre `this` avec une méthode passée en callback.** Utilise une flèche ou un champ fléché.
- **Écrire `$store` pour une variable qui n'est pas un store.** Svelte cherche une méthode `subscribe` et échoue.
- **Appeler `setContext` ou `getContext` dans un gestionnaire d'événement.** Ils ne fonctionnent que pendant l'initialisation du composant.
- **Utiliser `localStorage` sans vérifier `browser`.** Erreur au rendu serveur.
- **Stocker des données utilisateur dans un module global côté serveur.** Fuite possible entre visiteurs.

## Bonnes pratiques

- Reste sur les props tant que le partage ne concerne que deux ou trois niveaux.
- Pour du nouveau code Svelte 5, préfère les classes et objets `$state` dans des fichiers `.svelte.js`.
- Expose une API claire (méthodes `ajouter`, `retirer`) plutôt que de laisser les composants manipuler directement les tableaux.
- Garde les valeurs calculables dans `$derived`.
- Utilise le contexte pour les composants « compound » (onglets, accordéon, formulaire en étapes).
- Teste ton application avec le rendu serveur actif, pas seulement en navigation côté client.

## À retenir

- Le partage d'état évite le **prop drilling** : module `.svelte.js`, stores ou contexte selon le besoin.
- En Svelte 5, les runes `$state` et `$derived` fonctionnent dans les fichiers `.svelte.js`, y compris dans des classes.
- On n'exporte pas une variable réassignée : on exporte un objet ou des fonctions.
- Les stores (`writable`, `readable`, `derived`) restent valables ; `$store` s'abonne et se désabonne automatiquement.
- Le contexte limite un état à un sous-arbre et doit être posé à l'initialisation du composant.
- Sur le serveur, un module global est partagé entre tous les visiteurs : n'y stocke aucune donnée personnelle.
