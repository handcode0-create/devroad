---
title: Composants et réactivité
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Le chapitre précédent t'a montré `$state` et `$props` en coup d'œil. Ici, on entre dans le cœur de Svelte 5 : le système de **réactivité** fondé sur les runes, et la façon de construire des composants qui communiquent proprement entre eux.

À la fin du chapitre, tu seras capable de :

- déclarer un état avec `$state`, y compris pour des objets et des tableaux ;
- calculer des valeurs dérivées avec `$derived` et `$derived.by` ;
- exécuter des effets de bord avec `$effect` et savoir quand s'en passer ;
- recevoir des propriétés avec `$props`, leurs valeurs par défaut et le reste des propriétés ;
- injecter du contenu dans un composant avec `children` et les snippets ;
- comparer avec la syntaxe de Svelte 4 pour lire du code plus ancien.

Prérequis : le chapitre « Découvrir Svelte ». Prévois deux heures. Tu peux tester dans le Playground de svelte.dev ou dans un projet créé avec `npx sv create`.

## $state : l'état réactif

Une variable déclarée avec `$state` est réactive : chaque lecture dans le balisage est liée à la valeur, et chaque modification met la page à jour.

```svelte
<script>
  let points = $state(0);
</script>

<button onclick={() => points += 10}>Score : {points}</button>
```

### Les objets et tableaux sont réactifs en profondeur

Avec Svelte 5, quand tu passes un objet ou un tableau à `$state`, Svelte l'enveloppe dans un **proxy**. Tu peux alors le modifier directement, sans le recréer :

```svelte
<script>
  let taches = $state([
    { id: 1, titre: 'Lire le chapitre', fait: false },
    { id: 2, titre: 'Faire l\'atelier', fait: false }
  ]);

  function ajouter(titre) {
    taches.push({ id: Date.now(), titre, fait: false });
  }

  function basculer(tache) {
    tache.fait = !tache.fait;
  }
</script>

<ul>
  {#each taches as tache (tache.id)}
    <li>
      <button onclick={() => basculer(tache)}>
        {tache.fait ? 'Fait' : 'À faire'}
      </button>
      {tache.titre}
    </li>
  {/each}
</ul>

<button onclick={() => ajouter('Nouvelle tâche')}>Ajouter</button>
```

`taches.push(...)` et `tache.fait = ...` suffisent. Pas besoin du style « immuable » qu'impose React avec des copies de tableaux. C'est l'une des plus grandes différences au quotidien.

> **Astuce** : le proxy ne s'applique qu'aux objets simples et aux tableaux. Une instance de classe n'est pas rendue réactive automatiquement ; il faut utiliser des champs `$state` dans la classe (nous le verrons au chapitre 4).

### Comparer avec Svelte 4

En Svelte 4, une variable `let` du composant était réactive d'office, mais seule l'**affectation** déclenchait la mise à jour. Il fallait écrire `taches = [...taches, nouvelle]` ou `taches = taches` après un `push`. Svelte 5 supprime cet inconvénient : l'état est explicite avec `$state`, et la réactivité suit les modifications profondes.

:::quiz
Avec Svelte 5, que se passe-t-il si tu écris `taches.push(nouvelle)` sur un tableau déclaré avec `$state([])` ?
- [ ] Rien : il faut obligatoirement réaffecter le tableau
- [ ] Une erreur est levée car le tableau est en lecture seule
- [x] L'interface se met à jour, car le tableau est réactif en profondeur
- [ ] Le tableau est dupliqué
> Svelte enveloppe les tableaux et objets passés à `$state` dans un proxy qui détecte les modifications directes comme `push`.
:::

## $derived : les valeurs calculées

Souvent, une valeur dépend d'une autre : le total d'un panier, le nombre de tâches restantes, un nom en majuscules. Plutôt que de recalculer à la main, on utilise `$derived`.

```svelte
<script>
  let prix = $state(2500);
  let quantite = $state(2);

  let total = $derived(prix * quantite);
  let libelle = $derived(`${quantite} article(s) : ${total} FCFA`);
</script>

<input type="number" bind:value={quantite} min="1" />
<p>{libelle}</p>
```

Svelte sait que `total` dépend de `prix` et `quantite` : il le recalcule uniquement quand l'une d'elles change. Une valeur dérivée est **en lecture seule** : on ne lui affecte rien, on modifie ses sources.

Pour une logique plus longue qu'une expression, utilise `$derived.by` avec une fonction :

```svelte
<script>
  let taches = $state([
    { titre: 'A', fait: true },
    { titre: 'B', fait: false },
    { titre: 'C', fait: false }
  ]);

  let bilan = $derived.by(() => {
    const faites = taches.filter((t) => t.fait).length;
    const reste = taches.length - faites;
    const pourcentage = taches.length
      ? Math.round((faites / taches.length) * 100)
      : 0;
    return { faites, reste, pourcentage };
  });
</script>

<p>{bilan.faites} faite(s), {bilan.reste} restante(s) ({bilan.pourcentage} %)</p>
```

> **Erreur fréquente** : stocker une valeur calculable dans un second `$state` et la synchroniser avec un effet. Utilise `$derived` : il n'y a plus de désynchronisation possible.

En Svelte 4, on écrivait `$: total = prix * quantite;`. Le principe est le même, mais les runes rendent la dépendance plus explicite et utilisable aussi dans des fichiers `.js`.

## $effect : réagir aux changements

Un **effet** exécute du code à la suite d'un changement, pour des opérations qui sortent de l'interface : sauvegarder dans le stockage du navigateur, mettre à jour le titre de l'onglet, lancer un minuteur, brancher une bibliothèque externe.

```svelte
<script>
  let compteur = $state(0);

  $effect(() => {
    document.title = `Compteur : ${compteur}`;
  });
</script>

<button onclick={() => compteur++}>+1</button>
```

L'effet se lance après l'affichage du composant, puis chaque fois qu'une valeur réactive **lue dedans** change. Il peut renvoyer une fonction de **nettoyage**, appelée avant la prochaine exécution et à la destruction du composant :

```svelte
<script>
  let delai = $state(1000);
  let secondes = $state(0);

  $effect(() => {
    const id = setInterval(() => secondes++, delai);
    return () => clearInterval(id);
  });
</script>

<p>{secondes} s (intervalle : {delai} ms)</p>
<input type="range" min="200" max="3000" bind:value={delai} />
```

Ici, `delai` est lu dans l'effet : quand tu bouges le curseur, le minuteur est nettoyé puis relancé.

> **Attention** : les effets ne tournent pas pendant le rendu côté serveur. Tu peux donc y utiliser `window`, `document` ou `localStorage` sans casser le rendu serveur de SvelteKit.

### Quand éviter $effect

C'est un outil de dernier recours. Avant d'en écrire un, demande-toi :

- ma valeur peut-elle être calculée ? Alors c'est `$derived` ;
- le changement vient-il d'un clic ou d'une saisie ? Alors mets la logique directement dans le gestionnaire d'événement ;
- ai-je besoin de synchroniser un état avec un autre ? C'est presque toujours un signe qu'il y a un seul état de trop.

:::quiz
Tu veux afficher `prenom + ' ' + nom` à partir de deux états. Quelle est la meilleure approche ?
- [ ] Un `$effect` qui met à jour un troisième `$state`
- [x] `let complet = $derived(prenom + ' ' + nom);`
- [ ] Une fonction appelée dans un `setInterval`
- [ ] Un `$state` recalculé manuellement à chaque saisie
> Une valeur qui se déduit d'autres valeurs se déclare avec `$derived` : elle est toujours à jour et ne nécessite aucun effet.
:::

## $props : recevoir des données

Un composant reçoit ses données de son parent via la rune `$props()`. On utilise la déstructuration :

```svelte
<script>
  let { titre, niveau = 'débutant', termine = false } = $props();
</script>

<article class:termine>
  <h3>{titre}</h3>
  <span>{niveau}</span>
</article>
```

- `titre` est obligatoire dans les faits (sans valeur par défaut) ;
- `niveau` et `termine` ont une **valeur par défaut** utilisée si le parent ne les fournit pas ;
- `class:termine` ajoute la classe `termine` quand la variable est vraie (raccourci de `class:termine={termine}`).

Côté parent :

```svelte
<script>
  import CarteCours from '$lib/CarteCours.svelte';
</script>

<CarteCours titre="Svelte" niveau="intermédiaire" />
<CarteCours titre="CSS" termine />
```

Écrire `termine` seul revient à `termine={true}`.

### Renommer, récupérer le reste

```svelte
<script>
  let { class: classe = '', titre, ...autres } = $props();
</script>

<div class={classe} {...autres}>
  <h3>{titre}</h3>
</div>
```

`class` est un mot réservé de JavaScript, on le renomme donc en `classe`. Le reste `...autres` regroupe toutes les autres propriétés (par exemple `id` ou `aria-label`) et `{...autres}` les répartit sur la balise.

### Les props se lisent, ne s'écrasent pas

Un composant enfant ne doit pas réaffecter une prop reçue d'un parent : le flux normal est **les données descendent, les événements remontent**. Pour qu'un enfant prévienne son parent, on lui passe une **fonction** en prop :

```svelte
<!-- Enfant : BoutonLike.svelte -->
<script>
  let { likes, onlike } = $props();
</script>

<button onclick={onlike}>J'aime ({likes})</button>
```

```svelte
<!-- Parent -->
<script>
  import BoutonLike from '$lib/BoutonLike.svelte';
  let likes = $state(0);
</script>

<BoutonLike {likes} onlike={() => likes++} />
```

`{likes}` est le raccourci de `likes={likes}`. En Svelte 4, on aurait utilisé `export let likes` et `createEventDispatcher` ; les props-fonctions de Svelte 5 sont plus simples et se typent mieux.

## Le contenu imbriqué : children et snippets

Pour un composant « conteneur » (carte, modale, bouton), le parent passe du contenu entre les balises. Dans Svelte 5, il arrive dans la prop spéciale `children`, que l'on affiche avec `{@render ...}` :

```svelte
<!-- Carte.svelte -->
<script>
  let { titre, children } = $props();
</script>

<section class="carte">
  <h2>{titre}</h2>
  {@render children?.()}
</section>
```

```svelte
<Carte titre="Astuce du jour">
  <p>Utilise <code>$derived</code> plutôt qu'un effet.</p>
</Carte>
```

Le `?.()` rend le contenu optionnel : si le parent ne fournit rien, rien ne s'affiche.

Pour plusieurs zones de contenu, on déclare des **snippets** nommés avec `{#snippet}` :

```svelte
<!-- Parent -->
<Modale>
  {#snippet entete()}
    <h2>Confirmation</h2>
  {/snippet}
  <p>Veux-tu vraiment continuer ?</p>
</Modale>
```

```svelte
<!-- Modale.svelte -->
<script>
  let { entete, children } = $props();
</script>

<div class="modale">
  <header>{@render entete?.()}</header>
  {@render children?.()}
</div>
```

Les snippets remplacent les **slots** de Svelte 4 (`<slot />` et `<slot name="entete" />`). Les slots fonctionnent encore, mais sont dépréciés.

## Atelier guidé : une liste de tâches avec bilan

Compte une heure et demie. Travaille dans un projet SvelteKit, dans `+page.svelte` et `src/lib`.

1. Crée un état `taches` avec `$state` : un tableau de trois objets `{ id, titre, fait }`.
2. Affiche-les dans une liste avec `{#each taches as tache (tache.id)}`.
3. Ajoute un bouton par tâche qui bascule `tache.fait` directement, sans copier le tableau.
4. Ajoute un état `nouveauTitre` et un bouton « Ajouter » qui utilise `taches.push(...)`.
5. Crée un `$derived` nommé `restantes` qui compte les tâches non faites, et affiche « X tâche(s) restante(s) ».
6. Utilise `$derived.by` pour calculer un objet `{ faites, restantes, pourcentage }` et affiche une barre de progression.
7. Extrais un composant `TacheItem.svelte` qui reçoit `tache` et `onbasculer` via `$props`.
8. Crée un composant `Carte.svelte` avec `children`, et entoure-y la liste.
9. Ajoute un `$effect` qui met à jour `document.title` avec le nombre de tâches restantes.

Auto-évaluation : peux-tu justifier pour chaque valeur de ton composant pourquoi c'est un `$state`, un `$derived` ou une prop ? Si tu hésites pour l'une d'elles, c'est un signe de duplication.

## Erreurs fréquentes

- **Utiliser `$effect` pour calculer une valeur.** Préfère `$derived`.
- **Muter une prop de l'enfant.** Passe une fonction de rappel ou, plus tard, utilise `$bindable`.
- **Oublier la clé dans `{#each}`.** Sans `(tache.id)`, Svelte réutilise les éléments par position, ce qui cause des bugs sur les listes modifiables.
- **Déstructurer un `$state` et croire qu'il reste réactif.** `let { fait } = tache;` copie la valeur à l'instant T.
- **Appeler `$state` ou `$derived` dans une condition ou une fonction ordinaire.** Les runes se déclarent au niveau supérieur d'un composant ou d'un module.
- **Mélanger les syntaxes.** Dans un même composant, évite de combiner `export let` (Svelte 4) et `$props()`.

## Bonnes pratiques

- Garde le **minimum d'état** : tout ce qui se calcule devient `$derived`.
- Nomme les props-fonctions avec le préfixe `on` (`onlike`, `onsupprimer`).
- Donne des valeurs par défaut sensées aux props optionnelles.
- Réserve `$effect` aux interactions avec le monde extérieur et pense toujours au nettoyage.
- Utilise `children` et les snippets pour des composants flexibles plutôt que de multiplier les props booléennes.

## À retenir

- `$state` crée un état réactif, profond pour les objets et tableaux : on peut les modifier directement.
- `$derived` et `$derived.by` calculent des valeurs à partir d'autres valeurs et restent en lecture seule.
- `$effect` sert aux effets de bord (titre, minuteur, stockage) et peut renvoyer une fonction de nettoyage.
- `$props()` reçoit les propriétés, avec valeurs par défaut, renommage et `...reste`.
- Les données descendent par les props, les événements remontent par des props-fonctions.
- `children`, `{@render}` et `{#snippet}` remplacent les slots de Svelte 4.
