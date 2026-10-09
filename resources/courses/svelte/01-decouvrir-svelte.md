---
title: Découvrir Svelte
minutes: 90
level: beginner
---

## Ce que tu vas apprendre

Svelte est un framework pour construire des interfaces web, mais il fonctionne différemment de React ou Vue : c'est un **compilateur**. Tu écris des composants dans des fichiers `.svelte`, et au moment de la construction du projet, Svelte les transforme en JavaScript très léger, sans « moteur » lourd à envoyer au navigateur. Le résultat : des pages rapides, un code court et lisible. C'est un excellent choix quand ton audience navigue avec une connexion limitée.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est Svelte et en quoi un compilateur diffère d'une bibliothèque qui tourne dans le navigateur ;
- créer un projet avec `sv` et lancer le serveur de développement ;
- lire la structure d'un fichier `.svelte` (script, balisage, style) ;
- afficher des données avec les accolades et réagir à un clic grâce à la **rune** `$state` ;
- situer Svelte, SvelteKit et Vite dans l'écosystème.

Prérequis : HTML, CSS et JavaScript de base (variables, fonctions, tableaux), ainsi que Node.js installé (version 18 ou plus récente). Prévois une heure et demie. Tu peux aussi tout tester sans rien installer dans le Playground officiel sur svelte.dev.

## Un compilateur plutôt qu'un moteur

Quand tu utilises une bibliothèque classique, le navigateur télécharge le code de la bibliothèque **et** le tien. À chaque changement de donnée, la bibliothèque compare l'ancienne et la nouvelle version de l'interface pour savoir quoi mettre à jour.

Svelte déplace ce travail **en amont**, au moment de la compilation. Il analyse tes composants, repère quelle donnée influence quel morceau de la page, et génère des instructions précises du genre « quand `compteur` change, mets à jour ce nœud de texte ». Il n'y a rien à comparer à l'exécution.

Conséquences concrètes :

- les paquets envoyés au navigateur sont **petits** ;
- le code que tu écris est **proche du HTML** : peu de cérémonie ;
- les mises à jour sont **directes** et rapides.

> **À retenir** : Svelte est un compilateur. Ce que tu écris n'est pas ce que le navigateur exécute : c'est du JavaScript optimisé, généré pour toi.

:::quiz
Quelle est la particularité principale de Svelte par rapport à beaucoup d'autres bibliothèques d'interface ?
- [ ] Il fonctionne uniquement côté serveur
- [x] Il compile les composants en JavaScript ciblé au moment de la construction, sans gros moteur envoyé au navigateur
- [ ] Il remplace HTML, CSS et JavaScript par un nouveau langage
- [ ] Il nécessite obligatoirement une base de données
> Svelte analyse les composants à la compilation et génère du code qui met à jour précisément le DOM, d'où des paquets légers.
:::

## Svelte, SvelteKit et Vite

Quatre noms reviennent tout le temps, ne les confonds pas :

| Outil | Rôle |
| --- | --- |
| **Svelte** | Le langage de composants : balisage, réactivité, styles |
| **SvelteKit** | Le framework complet bâti sur Svelte : routes, chargement des données, formulaires, rendu serveur |
| **Vite** | L'outil de développement et de construction, utilisé par SvelteKit |
| **sv** | La commande officielle pour créer et enrichir un projet |

On peut utiliser Svelte seul pour ajouter un widget interactif dans une page existante. Mais pour une vraie application (plusieurs pages, formulaires, API), on prend **SvelteKit**. C'est ce que tu feras à partir du chapitre 5. Dans ce cours, nous utilisons **Svelte 5** et **SvelteKit 2**.

> **Astuce** : si tu trouves un tutoriel avec `export let` pour les propriétés ou `$:` pour les valeurs calculées, il date de Svelte 4. Ça fonctionne encore dans certains contextes, mais Svelte 5 a introduit les **runes** qui remplacent ces syntaxes. Nous y reviendrons au fil des chapitres.

## Créer ton premier projet

Ouvre un terminal et lance :

```bash
npx sv create mon-app
cd mon-app
npm install
npm run dev
```

L'assistant te pose quelques questions. Pour débuter, choisis le modèle « SvelteKit minimal », active TypeScript si tu es à l'aise (sinon, JavaScript simple), et ignore les options supplémentaires. Le serveur de développement démarre, généralement sur `http://localhost:5173`. Chaque fois que tu enregistres un fichier, la page se met à jour sans rechargement complet.

Voici les dossiers qui comptent :

```text
mon-app/
├── src/
│   ├── routes/
│   │   └── +page.svelte    <- la page d'accueil
│   └── lib/                <- tes composants et utilitaires
├── static/                 <- fichiers servis tels quels (favicon...)
├── svelte.config.js
├── vite.config.js
└── package.json
```

Pour ce chapitre, tu vas seulement modifier `src/routes/+page.svelte`. Les routes seront expliquées en détail au chapitre 5.

## Anatomie d'un fichier .svelte

Un composant Svelte est un fichier avec jusqu'à trois zones : du JavaScript, du balisage HTML et du CSS. Elles sont toutes optionnelles.

```svelte
<script>
  let nom = 'Awa';
</script>

<h1>Bonjour {nom} !</h1>
<p>Bienvenue sur DevRoad.</p>

<style>
  h1 {
    color: #ff3e00;
  }
</style>
```

Trois choses à remarquer :

1. le bloc `<script>` contient la logique du composant ; ses variables sont utilisables dans le balisage ;
2. les **accolades** `{ }` insèrent n'importe quelle expression JavaScript dans le HTML ;
3. le bloc `<style>` est **limité au composant** : le `h1` ci-dessus n'affecte aucun autre `h1` de l'application. Svelte ajoute automatiquement une classe unique pour cela.

Il n'y a pas de fonction à écrire, pas de `return`, pas de règle « un seul élément racine ». Tu peux mettre plusieurs balises côte à côte.

### Les accolades en détail

Les accolades acceptent des expressions, pas des instructions :

```svelte
<script>
  let prix = 2500;
  let quantite = 3;
  const devise = 'FCFA';
</script>

<p>Total : {prix * quantite} {devise}</p>
<p>Majuscules : {devise.toLowerCase()}</p>
<p>Arrondi : {Math.round(prix / 7)}</p>
```

Elles fonctionnent aussi dans les attributs :

```svelte
<script>
  let lien = 'https://svelte.dev';
  let description = 'Site officiel de Svelte';
</script>

<a href={lien} title={description}>Documentation</a>
<img src="/logo.png" alt="Logo de {description}" />
```

Remarque : pour un attribut qui contient uniquement une expression, pas de guillemets (`href={lien}`). Pour du texte mélangé à des expressions, les guillemets restent nécessaires (`alt="Logo de {description}"`).

> **Attention** : pour afficher un caractère accolade littéral dans le HTML, utilise les entités `&#123;` et `&#125;`, sinon Svelte pense que tu ouvres une expression.

## La réactivité : première rune

Une variable ordinaire ne fait pas réagir l'interface. Pour qu'un changement de valeur mette la page à jour, on déclare la variable avec la rune **`$state`**.

Une **rune** est un mot-clé spécial de Svelte 5, qui commence par `$`. Ce n'est pas une fonction à importer : le compilateur la reconnaît directement.

```svelte
<script>
  let compteur = $state(0);

  function incrementer() {
    compteur += 1;
  }
</script>

<button onclick={incrementer}>
  Clics : {compteur}
</button>
```

Tu écris `compteur += 1` comme en JavaScript normal. Le compilateur a repéré que `compteur` est un état, et que le texte du bouton en dépend : il met à jour ce texte à chaque modification. Pas de `setCompteur`, pas de `useState`.

Quelques points importants :

- l'événement s'écrit `onclick` en minuscules, comme l'attribut HTML (en Svelte 4, c'était `on:click`) ;
- tu passes la fonction, tu ne l'appelles pas : `onclick={incrementer}` et non `onclick={incrementer()}` ;
- tu peux écrire la fonction directement : `onclick={() => compteur++}`.

:::quiz
Quel code déclare correctement une variable qui met l'interface à jour quand elle change (Svelte 5) ?
- [ ] let compteur = 0;
- [ ] const [compteur, setCompteur] = useState(0);
- [x] let compteur = $state(0);
- [ ] export let compteur = 0;
> `$state` est la rune qui déclare un état réactif. Une variable `let` simple ne déclenche aucune mise à jour, et `export let` sert, en Svelte 4, à déclarer une propriété.
:::

## Des composants qui se composent

Comme dans tout framework moderne, une interface est un assemblage de composants. Crée le fichier `src/lib/Salutation.svelte` :

```svelte
<script>
  let { prenom } = $props();
</script>

<p>Salut {prenom}, content de te voir !</p>
```

Puis utilise-le dans `src/routes/+page.svelte` :

```svelte
<script>
  import Salutation from '$lib/Salutation.svelte';
</script>

<Salutation prenom="Awa" />
<Salutation prenom="Koffi" />
```

Deux nouveautés :

- `$lib` est un **alias** vers `src/lib`, pratique pour éviter les chemins relatifs du genre `../../lib` ;
- la rune `$props()` récupère les propriétés passées par le parent. Nous l'étudierons en détail au chapitre suivant.

Un nom de composant commence **toujours par une majuscule** dans le balisage (`<Salutation />`), ce qui le distingue d'une balise HTML.

## Un premier mini-composant complet

Voici un petit composant qui combine tout ce que tu viens de voir : un état, une valeur affichée, un événement et du style.

```svelte
<script>
  let likes = $state(0);
</script>

<button class="like" onclick={() => likes++}>
  J'aime ({likes})
</button>

<style>
  .like {
    padding: 0.5rem 1rem;
    border-radius: 999px;
    border: none;
    background: #ff3e00;
    color: white;
    cursor: pointer;
  }
</style>
```

Trente secondes pour l'écrire, un résultat entièrement interactif. Compare avec la version « à la main » du DOM : plus de `querySelector`, plus de `textContent`, plus de risque d'oublier de mettre un endroit à jour.

## Atelier guidé : ta première page Svelte

Compte une heure. Tu peux travailler dans le Playground de svelte.dev ou dans le projet créé plus haut.

1. Crée le projet avec `npx sv create` (modèle minimal) et lance `npm run dev`. Ouvre la page dans le navigateur.
2. Dans `src/routes/+page.svelte`, remplace tout le contenu par un `h1` « Ma première page Svelte ».
3. Ajoute un bloc `<script>` avec une variable `prenom`, puis affiche « Bonjour, {prenom} » dans un paragraphe.
4. Ajoute un état `compteur` avec `$state(0)` et un bouton qui l'incrémente. Vérifie que le texte se met à jour.
5. Ajoute un deuxième bouton « Remettre à zéro » qui repasse le compteur à 0.
6. Crée `src/lib/CarteTechno.svelte` qui reçoit une propriété `nom` avec `$props()` et l'affiche dans une `div` stylée.
7. Utilise ce composant trois fois dans la page, avec trois technologies différentes.
8. Ajoute un bloc `<style>` dans la page pour colorer le `h1`, puis vérifie qu'aucun autre titre du site n'est modifié.
9. Volontairement, écris `let compteur = 0;` sans `$state` et constate que l'écran ne bouge plus. Remets ensuite `$state`.

Auto-évaluation : sans regarder le cours, explique à voix haute pourquoi Svelte est qualifié de compilateur, et ce qui différencie une variable `let` d'une variable `$state`.

## Erreurs fréquentes

- **Oublier `$state`.** La variable change dans le code mais l'écran reste figé.
- **Écrire `onclick={fonction()}`.** La fonction est appelée immédiatement au rendu au lieu d'au clic.
- **Utiliser `on:click` ou `export let` dans un tutoriel Svelte 4** sans l'adapter. Svelte 5 préfère `onclick` et `$props()`.
- **Nommer un composant avec une minuscule.** `<carte />` est lu comme une balise HTML inconnue.
- **Importer sans extension.** Le fichier doit être importé avec `.svelte` à la fin : `import Carte from '$lib/Carte.svelte'`.
- **Confondre Svelte et SvelteKit.** Svelte gère les composants ; les routes, les formulaires et le serveur viennent de SvelteKit.

## Bonnes pratiques

- Un composant, une responsabilité, un nom en PascalCase (`CarteRoadmap.svelte`).
- Garde les composants courts et lisibles ; extrais un sous-composant dès qu'un bloc devient répétitif.
- Profite des styles limités au composant plutôt que de CSS global partout.
- Utilise des balises sémantiques (`button`, `nav`, `ul`) : Svelte avertit même dans la console quand l'accessibilité est négligée.
- Suis la documentation officielle sur svelte.dev et le tutoriel interactif : ils sont à jour pour Svelte 5.

## À retenir

- Svelte est un **compilateur** : il produit du JavaScript léger et ciblé.
- **SvelteKit** est le framework complet autour de Svelte ; **Vite** assure le développement et la construction ; `sv` crée les projets.
- Un fichier `.svelte` contient un `<script>`, du balisage et un `<style>` limité au composant.
- Les accolades insèrent des expressions JavaScript dans le HTML et dans les attributs.
- La rune `$state` déclare une valeur réactive ; `onclick` gère le clic ; `$props()` reçoit les propriétés.
- Svelte 5 utilise les **runes** là où Svelte 4 utilisait `export let`, `$:` et `on:click`.
