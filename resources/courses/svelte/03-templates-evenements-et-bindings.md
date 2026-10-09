---
title: Templates, événements et bindings
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Tu sais maintenant déclarer un état et le dériver. Il reste à apprendre le langage de **template** de Svelte : comment afficher selon une condition, répéter des éléments, réagir aux événements et synchroniser un champ de formulaire avec une variable. C'est ce qui fait de Svelte un langage particulièrement agréable pour construire des interfaces interactives.

À la fin du chapitre, tu seras capable de :

- utiliser les blocs `{#if}`, `{#each}`, `{#await}` et `{#key}` ;
- appliquer des classes et des styles dynamiques avec `class:` et `style:` ;
- gérer des événements avec `onclick`, `oninput`, `onsubmit` et empêcher le comportement par défaut ;
- lier un champ à une variable avec `bind:value`, `bind:checked`, `bind:group` et `bind:this` ;
- ajouter une transition simple ;
- repérer les différences avec la syntaxe de Svelte 4 (`on:click`, modificateurs).

Prérequis : les chapitres « Découvrir Svelte » et « Composants et réactivité ». Prévois deux heures.

## Les blocs conditionnels : {#if}

Svelte ajoute à HTML des **blocs** qui commencent par `{#`, continuent avec `{:` et se terminent par `{/`. Le premier est la condition :

```svelte
<script>
  let connecte = $state(false);
  let panier = $state([]);
</script>

{#if connecte}
  <p>Bienvenue !</p>
{:else}
  <button onclick={() => connecte = true}>Se connecter</button>
{/if}

{#if panier.length === 0}
  <p>Ton panier est vide.</p>
{:else if panier.length < 5}
  <p>{panier.length} article(s).</p>
{:else}
  <p>Beaucoup d'articles !</p>
{/if}
```

Le contenu du bloc est créé ou détruit selon la condition. Pour les petites décisions de texte, un ternaire dans les accolades reste pratique : `{connecte ? 'En ligne' : 'Hors ligne'}`.

> **À retenir** : à la différence de React, il n'y a pas de piège avec le zéro. Un `{#if nombre}` avec `nombre = 0` n'affiche simplement rien, sans caractère parasite.

## Répéter avec {#each}

Pour afficher une liste, on utilise `{#each}`. Ajoute presque toujours une **clé** entre parenthèses :

```svelte
<script>
  let roadmaps = $state([
    { id: 'svelte', nom: 'Svelte', niveau: 'débutant' },
    { id: 'laravel', nom: 'Laravel', niveau: 'intermédiaire' },
    { id: 'docker', nom: 'Docker', niveau: 'débutant' }
  ]);
</script>

<ul>
  {#each roadmaps as roadmap, index (roadmap.id)}
    <li>{index + 1}. {roadmap.nom} ({roadmap.niveau})</li>
  {:else}
    <li>Aucune roadmap pour le moment.</li>
  {/each}
</ul>
```

- `roadmap` est l'élément courant et `index` sa position ;
- `(roadmap.id)` est la clé : Svelte l'utilise pour savoir quel élément du DOM correspond à quelle donnée quand la liste change ;
- la partie `{:else}` s'affiche quand le tableau est vide : plus besoin d'un `if` à part.

Tu peux déstructurer directement : `{#each roadmaps as { id, nom } (id)}`.

### Calculer dans le template : {@const}

Pour garder une valeur intermédiaire dans un bloc :

```svelte
{#each produits as produit (produit.id)}
  {@const total = produit.prix * produit.quantite}
  <p>{produit.nom} : {total} FCFA</p>
{/each}
```

:::quiz
À quoi sert la clé dans `{#each liste as item (item.id)}` ?
- [ ] À trier la liste par identifiant
- [ ] À empêcher toute modification de la liste
- [x] À permettre à Svelte d'associer chaque élément du DOM à la bonne donnée quand la liste change
- [ ] À afficher l'identifiant dans la page
> La clé donne une identité stable à chaque élément : lors d'un ajout, d'une suppression ou d'un tri, Svelte déplace les bons nœuds au lieu de les réutiliser par position.
:::

## Attendre des données : {#await}

Svelte sait afficher les trois états d'une promesse directement dans le template :

```svelte
<script>
  async function chargerCitation() {
    const reponse = await fetch('https://dummyjson.com/quotes/random');
    if (!reponse.ok) throw new Error('Erreur réseau');
    return reponse.json();
  }

  let promesse = $state(chargerCitation());
</script>

{#await promesse}
  <p>Chargement…</p>
{:then citation}
  <blockquote>{citation.quote}</blockquote>
{:catch erreur}
  <p>Oups : {erreur.message}</p>
{/await}

<button onclick={() => promesse = chargerCitation()}>Une autre</button>
```

Quand tu réassignes `promesse`, le bloc retourne à l'état « Chargement… » puis affiche le nouveau résultat. Dans SvelteKit, tu chargeras plutôt les données dans une fonction `load` (chapitre 5), mais `{#await}` reste utile pour des appels déclenchés par l'utilisateur.

Le bloc `{#key valeur}` détruit et recrée son contenu à chaque changement de `valeur`, pratique pour rejouer une animation.

## Classes et styles dynamiques

Pour activer une classe selon une condition, utilise la directive `class:` :

```svelte
<script>
  let actif = $state(false);
</script>

<button class="onglet" class:actif onclick={() => actif = !actif}>
  Mon onglet
</button>

<style>
  .onglet { padding: 0.5rem 1rem; border: 1px solid #ccc; }
  .actif { background: #ff3e00; color: white; }
</style>
```

`class:actif` est le raccourci de `class:actif={actif}`. Pour plusieurs classes conditionnelles, Svelte 5.16 et plus récent accepte aussi un objet ou un tableau dans l'attribut `class` :

```svelte
<div class={['carte', { actif, desactive: !disponible }]}>…</div>
```

Pour un style en ligne, la directive `style:` est lisible et sûre :

```svelte
<script>
  let progression = $state(40);
</script>

<div class="barre">
  <div class="remplissage" style:width="{progression}%" style:background-color={progression === 100 ? 'green' : 'orange'}></div>
</div>
```

## Les événements

En Svelte 5, un gestionnaire est un attribut qui s'appelle `on` suivi du nom de l'événement, en minuscules : `onclick`, `oninput`, `onchange`, `onsubmit`, `onkeydown`, `onfocus`.

```svelte
<script>
  let texte = $state('');
  let derniereTouche = $state('');

  function surFrappe(event) {
    derniereTouche = event.key;
  }
</script>

<input oninput={(e) => texte = e.currentTarget.value} onkeydown={surFrappe} />
<p>Texte : {texte} (dernière touche : {derniereTouche})</p>
```

Le gestionnaire reçoit l'objet événement standard du navigateur. Utilise `event.currentTarget` pour l'élément sur lequel le gestionnaire est attaché.

### Empêcher le comportement par défaut

En Svelte 4, on écrivait `on:submit|preventDefault={...}` avec des **modificateurs**. En Svelte 5, ces modificateurs n'existent plus ; on appelle simplement la méthode dans la fonction :

```svelte
<script>
  let email = $state('');

  function envoyer(event) {
    event.preventDefault();
    console.log('Envoi de', email);
  }
</script>

<form onsubmit={envoyer}>
  <input type="email" bind:value={email} required />
  <button>S'inscrire</button>
</form>
```

> **Astuce** : dans un projet SvelteKit, tu vas souvent laisser le formulaire s'envoyer naturellement et utiliser les **actions** (chapitre 6). Le `preventDefault` manuel est réservé aux cas purement côté navigateur.

### Raccourci : passer l'événement au parent

Les props-fonctions du chapitre précédent servent aussi à relayer un événement. Tu peux même transmettre tous les gestionnaires avec `{...autres}`. Les anciens `createEventDispatcher` et `on:click` redirigé (sans valeur) sont remplacés par ce mécanisme.

## Les bindings : synchroniser un champ et une variable

Un **binding** crée un lien dans les deux sens : si la variable change, le champ change, et inversement. La directive est `bind:`.

```svelte
<script>
  let nom = $state('');
  let age = $state(18);
  let accepte = $state(false);
</script>

<input bind:value={nom} placeholder="Ton nom" />
<input type="number" bind:value={age} min="0" />
<label><input type="checkbox" bind:checked={accepte} /> J'accepte</label>

<p>{nom || 'inconnu'}, {age} ans, accord : {accepte ? 'oui' : 'non'}</p>
```

Remarque que `bind:value` sur un champ `type="number"` te donne un **nombre**, pas une chaîne. Plus de `Number(event.target.value)`.

### Groupes de cases et boutons radio

```svelte
<script>
  let niveau = $state('debutant');
  let langages = $state([]);
</script>

<label><input type="radio" bind:group={niveau} value="debutant" /> Débutant</label>
<label><input type="radio" bind:group={niveau} value="avance" /> Avancé</label>

<label><input type="checkbox" bind:group={langages} value="js" /> JavaScript</label>
<label><input type="checkbox" bind:group={langages} value="php" /> PHP</label>

<p>{niveau} / {langages.join(', ')}</p>
```

Avec des boutons radio, la variable contient la valeur choisie ; avec des cases à cocher, elle contient un **tableau** des valeurs cochées.

### Select, textarea et référence à un élément

```svelte
<script>
  let ville = $state('Abidjan');
  let message = $state('');
  let champ;

  $effect(() => {
    champ?.focus();
  });
</script>

<select bind:value={ville}>
  <option>Abidjan</option>
  <option>Bouaké</option>
  <option>Yamoussoukro</option>
</select>

<textarea bind:value={message} bind:this={champ}></textarea>
```

`bind:this` donne accès à l'élément DOM une fois créé (par exemple pour lui donner le focus). Il vaut `undefined` avant le montage, d'où l'utilisation dans un `$effect`.

:::quiz
Quelle directive lie une case à cocher à une variable booléenne `accepte` ?
- [ ] `<input type="checkbox" value={accepte} />`
- [x] `<input type="checkbox" bind:checked={accepte} />`
- [ ] `<input type="checkbox" on:check={accepte} />`
- [ ] `<input type="checkbox" bind:value={accepte} />`
> Pour une case à cocher, on lie la propriété `checked` avec `bind:checked`. `bind:value` est destiné aux champs de texte, nombres et listes.
:::

## Transitions

Svelte inclut un module d'animations simples. On les applique avec la directive `transition:` :

```svelte
<script>
  import { fade, slide } from 'svelte/transition';
  let visible = $state(true);
</script>

<button onclick={() => visible = !visible}>Afficher / cacher</button>

{#if visible}
  <p transition:fade={{ duration: 300 }}>Je m'efface en douceur.</p>
{/if}

{#if visible}
  <div transition:slide>Je glisse.</div>
{/if}
```

Les transitions se déclenchent à l'apparition et à la disparition d'un élément géré par un bloc `{#if}` ou `{#each}`. Les variantes `in:` et `out:` permettent d'avoir deux effets différents. Pense à respecter la préférence « réduire les animations » des utilisateurs sensibles au mouvement.

> **Attention** : `{@html contenu}` insère du HTML brut sans le nettoyer. Ne l'utilise jamais avec du texte venant d'un utilisateur : c'est une porte ouverte aux attaques XSS.

:::quiz
Comment empêcher le rechargement de la page à l'envoi d'un formulaire en Svelte 5 ?
- [ ] `<form on:submit|preventDefault={envoyer}>`
- [x] Appeler `event.preventDefault()` dans la fonction passée à `onsubmit`
- [ ] Ajouter l'attribut `no-reload`
- [ ] Utiliser `bind:submit`
> Les modificateurs d'événements de Svelte 4 ont disparu : en Svelte 5, on appelle directement `event.preventDefault()` dans le gestionnaire.
:::

## Atelier guidé : un mini-catalogue filtrable

Compte une heure et demie. Travaille dans `src/routes/+page.svelte`.

1. Déclare un tableau `cours` avec `$state` (au moins six éléments : `id`, `titre`, `niveau`, `minutes`, `termine`).
2. Ajoute un champ `recherche` lié avec `bind:value` et un `select` `niveauChoisi` (« tous », débutant, intermédiaire, professionnel).
3. Crée un `$derived` nommé `filtres` qui applique la recherche (insensible à la casse) puis le niveau.
4. Affiche `filtres` avec `{#each ... (cours.id)}` et un bloc `{:else}` « Aucun cours trouvé ».
5. Ajoute une case à cocher par cours avec `bind:checked={cours.termine}` et la directive `class:termine` pour barrer le titre.
6. Affiche le nombre de cours terminés avec un `$derived`.
7. Ajoute un bouton « Réinitialiser » qui vide les champs de recherche.
8. Ajoute une `transition:slide` sur chaque carte pour animer l'apparition lors d'un filtrage.
9. Ajoute un formulaire d'ajout de cours avec `onsubmit`, `event.preventDefault()` et `cours.push(...)`.

Auto-évaluation : explique à voix haute la différence entre un binding (`bind:value`) et un gestionnaire (`oninput`), et dans quel cas tu préfères l'un à l'autre.

## Erreurs fréquentes

- **Utiliser `on:click` dans un projet Svelte 5 en mode runes.** Il faut `onclick` ; mélanger les deux dans un même composant est interdit.
- **Appeler la fonction au lieu de la passer.** `onclick={supprimer(id)}` s'exécute au rendu ; écris `onclick={() => supprimer(id)}`.
- **Oublier la clé dans un `{#each}` modifiable.** Les champs de saisie gardent le mauvais contenu après un tri.
- **Lier une prop sans `$bindable`.** Un `bind:` vers un enfant exige que l'enfant déclare la prop avec `$bindable()`.
- **Utiliser `{@html}` avec des données non fiables.** Risque de faille XSS.
- **Lire `bind:this` trop tôt.** La référence n'existe qu'après le montage du composant.

## Bonnes pratiques

- Préfère les **bindings** pour les formulaires simples et les **gestionnaires** quand il y a de la logique à exécuter.
- Garde les expressions du template courtes ; déplace les calculs dans `$derived`.
- Mets toujours une clé stable dans les listes dynamiques.
- Utilise `class:` et `style:` plutôt que de concaténer des chaînes.
- Soigne l'accessibilité : un `label` par champ, des `button` pour les actions, du texte alternatif pour les images.
- Limite les transitions aux éléments importants et garde-les courtes.

## À retenir

- Les blocs `{#if}`, `{#each}`, `{#await}` et `{#key}` décrivent les conditions, les listes et les promesses directement dans le HTML.
- `{#each}` accepte une clé et un `{:else}` pour le cas vide.
- `class:` et `style:` rendent les styles dynamiques lisibles.
- Svelte 5 écrit les événements `onclick`, `oninput`, `onsubmit` ; les modificateurs de Svelte 4 sont remplacés par des appels comme `event.preventDefault()`.
- `bind:value`, `bind:checked`, `bind:group` et `bind:this` synchronisent le DOM et l'état dans les deux sens.
- `transition:` ajoute des animations d'apparition et de disparition ; `{@html}` est à manier avec une grande prudence.
