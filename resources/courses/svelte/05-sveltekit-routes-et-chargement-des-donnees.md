---
title: SvelteKit : routes et chargement des données
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Svelte construit des composants ; **SvelteKit** construit des applications. Il apporte le routage par fichiers, le chargement de données côté serveur ou navigateur, le rendu serveur (SSR), les API et le déploiement. C'est le chapitre où ton interface devient un vrai site avec plusieurs pages alimentées par des données.

À la fin du chapitre, tu seras capable de :

- créer des pages et des routes dynamiques avec le dossier `src/routes` ;
- partager un cadre commun avec les fichiers `+layout.svelte` ;
- charger des données avec les fonctions `load` (`+page.js` et `+page.server.js`) ;
- afficher ces données dans la page grâce à la prop `data` ;
- gérer les erreurs (404, redirections) ;
- créer un point d'API avec `+server.js` ;
- expliquer les options de rendu : SSR, prérendu et rendu côté client.

Prérequis : les chapitres 1 à 4. Prévois deux heures et demie. Tu as besoin d'un projet SvelteKit (`npx sv create`).

## Le routage par fichiers

Dans SvelteKit, **l'arborescence de `src/routes` est ton plan de site**. Chaque dossier correspond à un segment d'URL, et un fichier spécial décrit ce qu'on y trouve.

```text
src/routes/
├── +layout.svelte           <- cadre commun à toutes les pages
├── +page.svelte             <- /
├── a-propos/
│   └── +page.svelte         <- /a-propos
├── roadmaps/
│   ├── +page.svelte         <- /roadmaps
│   ├── +page.server.js      <- données de /roadmaps
│   └── [slug]/
│       ├── +page.svelte     <- /roadmaps/svelte, /roadmaps/docker...
│       └── +page.server.js
└── api/
    └── roadmaps/
        └── +server.js       <- API /api/roadmaps
```

Les noms de fichiers commençant par `+` sont réservés par SvelteKit :

| Fichier | Rôle |
| --- | --- |
| `+page.svelte` | Le composant de la page |
| `+page.js` | Fonction `load` exécutée sur le serveur **et** dans le navigateur |
| `+page.server.js` | Fonction `load` et actions exécutées **uniquement sur le serveur** |
| `+layout.svelte` | Cadre commun (menu, pied de page) autour des pages enfants |
| `+layout.server.js` | Données partagées par toutes les pages enfants |
| `+error.svelte` | Page d'erreur de la section |
| `+server.js` | Point d'API (GET, POST...) |

Un dossier entre crochets comme `[slug]` est un **paramètre** : `/roadmaps/svelte` et `/roadmaps/docker` utilisent la même page, et la valeur est accessible dans `params.slug`.

## Les layouts : un cadre partagé

`src/routes/+layout.svelte` entoure toutes les pages. Il affiche le contenu de la page courante avec `children` :

```svelte
<script>
  import { page } from '$app/state';

  let { children } = $props();
</script>

<nav>
  <a href="/" aria-current={page.url.pathname === '/' ? 'page' : undefined}>Accueil</a>
  <a href="/roadmaps">Roadmaps</a>
  <a href="/a-propos">À propos</a>
</nav>

<main>
  {@render children()}
</main>

<footer>HANCODE STUDIO</footer>
```

Remarque :

- la navigation se fait avec de simples liens `<a href>`. SvelteKit les intercepte pour naviguer **sans recharger** la page, tout en gardant des URL réelles ;
- `page` vient de `$app/state` (SvelteKit 2.12 et plus récent) et contient l'URL courante, les paramètres, les données ; en versions antérieures, on utilisait le store `$page` de `$app/stores` ;
- les layouts peuvent être **imbriqués** : un `+layout.svelte` dans `roadmaps/` s'ajoute à celui de la racine.

> **Astuce** : pour partager un layout entre plusieurs routes sans ajouter de segment à l'URL, utilise un **groupe de routes** entre parenthèses : `src/routes/(app)/tableau-de-bord/+page.svelte` correspond toujours à `/tableau-de-bord`.

:::quiz
Quelle URL correspond au fichier `src/routes/roadmaps/[slug]/+page.svelte` ?
- [ ] Uniquement `/roadmaps/slug`
- [x] Toute URL de la forme `/roadmaps/quelquechose`, avec la valeur disponible dans `params.slug`
- [ ] `/roadmaps` seulement
- [ ] `/[slug]/roadmaps`
> Les crochets déclarent un paramètre dynamique : le segment correspondant de l'URL est capturé et transmis à la fonction `load`.
:::

## Charger des données avec load

Une page a presque toujours besoin de données. On les prépare dans une fonction **`load`**, qui s'exécute **avant** l'affichage de la page. Le résultat est transmis au composant via la prop `data`.

Pour une page qui lit une base de données ou utilise des secrets, on utilise `+page.server.js` :

```js
// src/routes/roadmaps/+page.server.js
import { error } from '@sveltejs/kit';

export async function load({ fetch }) {
  const reponse = await fetch('/api/roadmaps');

  if (!reponse.ok) {
    error(502, 'Impossible de charger les roadmaps');
  }

  const roadmaps = await reponse.json();
  return { roadmaps };
}
```

Et dans la page :

```svelte
<!-- src/routes/roadmaps/+page.svelte -->
<script>
  let { data } = $props();
</script>

<h1>Roadmaps</h1>

<ul>
  {#each data.roadmaps as roadmap (roadmap.slug)}
    <li><a href="/roadmaps/{roadmap.slug}">{roadmap.titre}</a></li>
  {/each}
</ul>
```

Points importants :

- l'objet retourné par `load` (ici `{ roadmaps }`) devient `data.roadmaps` ;
- le `fetch` fourni dans l'argument de `load` est amélioré : il fonctionne côté serveur, conserve les cookies et peut appeler tes propres routes API sans URL absolue ;
- en Svelte 4 / SvelteKit 1, on déclarait `export let data;` ; avec Svelte 5, c'est `let { data } = $props();`.

### +page.js ou +page.server.js ?

Choisis selon l'endroit où le code doit tourner :

- **`+page.server.js`** : accès à la base de données, aux variables secrètes, aux cookies ; les données sont sérialisées vers le navigateur ;
- **`+page.js`** : code qui peut tourner aussi dans le navigateur lors des navigations, utile pour appeler une API publique, et capable de retourner des valeurs non sérialisables comme un composant.

Dans le doute, prends `+page.server.js`. Tu pourras alléger plus tard.

## Les routes dynamiques

Pour `/roadmaps/[slug]`, la fonction `load` reçoit `params` :

```js
// src/routes/roadmaps/[slug]/+page.server.js
import { error } from '@sveltejs/kit';
import { trouverRoadmap } from '$lib/server/roadmaps.js';

export async function load({ params }) {
  const roadmap = await trouverRoadmap(params.slug);

  if (!roadmap) {
    error(404, `La roadmap « ${params.slug} » n'existe pas`);
  }

  return { roadmap };
}
```

```svelte
<script>
  let { data } = $props();
  let { roadmap } = $derived(data);
</script>

<h1>{roadmap.titre}</h1>
<p>{roadmap.description}</p>
```

La ligne `$derived(data)` est importante : quand l'utilisateur passe de `/roadmaps/svelte` à `/roadmaps/docker`, SvelteKit **réutilise le même composant** et met à jour `data`. Une simple déstructuration `let { roadmap } = data;` figerait la valeur initiale, alors qu'avec `$derived` elle suit.

Le dossier `src/lib/server/` est spécial : tout ce qui s'y trouve ne peut **jamais** être importé côté navigateur. SvelteKit fait échouer la compilation si tu essaies. C'est l'endroit idéal pour tes accès base de données.

## Erreurs et redirections

Depuis `load`, tu peux interrompre le chargement avec deux fonctions :

```js
import { error, redirect } from '@sveltejs/kit';

export async function load({ locals }) {
  if (!locals.utilisateur) {
    redirect(303, '/connexion');
  }
  if (!locals.utilisateur.estAdmin) {
    error(403, 'Accès réservé aux administrateurs');
  }
  return { utilisateur: locals.utilisateur };
}
```

Avec SvelteKit 2, on **n'a plus besoin de faire `throw`** : `error(...)` et `redirect(...)` lèvent eux-mêmes l'exception. Dans SvelteKit 1, il fallait écrire `throw error(...)`.

Pour personnaliser l'affichage d'une erreur, ajoute `+error.svelte` :

```svelte
<script>
  import { page } from '$app/state';
</script>

<h1>{page.status}</h1>
<p>{page.error?.message}</p>
<a href="/">Retour à l'accueil</a>
```

:::quiz
Quel est le rôle de la fonction `load` d'un fichier `+page.server.js` ?
- [ ] Elle remplace le composant `+page.svelte`
- [x] Elle s'exécute sur le serveur avant l'affichage et retourne les données transmises à la page via `data`
- [ ] Elle s'exécute uniquement dans le navigateur après le clic
- [ ] Elle déclare les routes de l'application
> `load` prépare les données de la page. Dans `+page.server.js`, elle tourne exclusivement sur le serveur, ce qui permet d'utiliser une base de données ou des secrets.
:::

## Une API avec +server.js

Parfois tu veux exposer des données en JSON (pour une application mobile Flutter, par exemple). Un fichier `+server.js` exporte une fonction par méthode HTTP :

```js
// src/routes/api/roadmaps/+server.js
import { json, error } from '@sveltejs/kit';
import { listerRoadmaps, creerRoadmap } from '$lib/server/roadmaps.js';

export async function GET({ url }) {
  const niveau = url.searchParams.get('niveau');
  const roadmaps = await listerRoadmaps({ niveau });
  return json(roadmaps);
}

export async function POST({ request }) {
  const corps = await request.json();
  if (!corps.titre) error(400, 'Le titre est obligatoire');
  const roadmap = await creerRoadmap(corps);
  return json(roadmap, { status: 201 });
}
```

Teste-la avec `curl` :

```bash
curl "http://localhost:5173/api/roadmaps?niveau=debutant"
curl -X POST http://localhost:5173/api/roadmaps \
  -H "Content-Type: application/json" \
  -d '{"titre":"Svelte"}'
```

## Variables d'environnement

Les secrets (clé d'API, URL de base de données) vont dans un fichier `.env`, jamais dans le dépôt Git :

```bash
DATABASE_URL="postgres://utilisateur:motdepasse@localhost:5432/devroad"
PUBLIC_NOM_APP="DevRoad"
```

Et dans le code :

```js
import { env } from '$env/dynamic/private';
import { PUBLIC_NOM_APP } from '$env/static/public';
```

Seules les variables préfixées par `PUBLIC_` peuvent être importées côté navigateur. Les autres sont réservées au serveur : SvelteKit refuse de les importer dans un composant client.

## Navigation et rafraîchissement des données

Quelques outils pratiques :

```svelte
<script>
  import { goto, invalidateAll } from '$app/navigation';
</script>

<a href="/roadmaps" data-sveltekit-preload-data="hover">Roadmaps</a>

<button onclick={() => goto('/connexion')}>Aller à la connexion</button>
<button onclick={() => invalidateAll()}>Actualiser les données</button>
```

- `data-sveltekit-preload-data="hover"` charge les données d'une page dès que la souris survole le lien, pour une navigation quasi instantanée ;
- `goto` navigue par programmation ;
- `invalidateAll()` relance toutes les fonctions `load` de la page actuelle ; `invalidate('app:panier')` ne cible que celles qui ont déclaré `depends('app:panier')`.

## Options de rendu

Par défaut, SvelteKit rend chaque page **sur le serveur** (SSR) pour la première visite, puis la rend interactive dans le navigateur (hydratation), et gère les navigations suivantes côté client. Tu peux ajuster dans un fichier `+page.js` ou `+layout.js` :

```js
export const prerender = true;  // page générée à la construction (site statique)
export const ssr = false;       // pas de rendu serveur (mode application)
export const csr = false;       // aucun JavaScript côté navigateur
```

Quelques repères :

- **prerender** pour les pages qui ne changent pas (à propos, tarifs, articles de blog) : rapide et peu coûteux à héberger ;
- **ssr** par défaut, bon pour le référencement et le temps d'affichage ;
- **ssr = false** pour des tableaux de bord entièrement dépendants du navigateur.

## Atelier guidé : un catalogue de roadmaps

Compte deux heures. Pars d'un projet SvelteKit neuf.

1. Crée `src/lib/server/roadmaps.js` exportant un tableau de cinq roadmaps (`slug`, `titre`, `niveau`, `description`) et deux fonctions `listerRoadmaps` et `trouverRoadmap(slug)`.
2. Ajoute `src/routes/+layout.svelte` avec un menu (Accueil, Roadmaps) et un pied de page.
3. Crée `src/routes/roadmaps/+page.server.js` dont `load` retourne la liste.
4. Crée `+page.svelte` correspondant, qui affiche les roadmaps avec un lien vers chacune.
5. Crée la route dynamique `roadmaps/[slug]` avec son `load` et affiche titre et description.
6. Retourne une `error(404, ...)` quand le slug n'existe pas et crée un `+error.svelte` personnalisé.
7. Crée `api/roadmaps/+server.js` avec un `GET` acceptant un paramètre `niveau`, et teste-le avec `curl`.
8. Ajoute `data-sveltekit-preload-data="hover"` sur les liens principaux.
9. Mets `export const prerender = true;` dans `a-propos/+page.js` et vérifie avec `npm run build`.
10. Mets en évidence le lien actif du menu avec `page.url.pathname`.

Auto-évaluation : explique la différence entre `+page.js` et `+page.server.js`, et pourquoi on utilise `$derived(data)` dans une page dynamique.

## Erreurs fréquentes

- **Oublier que `+page.js` s'exécute aussi dans le navigateur.** N'y mets jamais de secret ni d'accès direct à la base de données.
- **Déstructurer `data` sans `$derived`.** La page ne se met pas à jour lors du passage d'un paramètre à un autre.
- **Utiliser un `fetch` global dans `load`.** Prends le `fetch` fourni en argument.
- **Écrire `throw error(...)` en pensant à SvelteKit 1.** En SvelteKit 2, on appelle simplement `error(...)`.
- **Importer un module de `$lib/server` dans un composant.** Le build échoue volontairement.
- **Oublier le préfixe `PUBLIC_`.** La variable est `undefined` côté navigateur.
- **Mal nommer les fichiers spéciaux.** `page.svelte` sans le `+` est ignoré.

## Bonnes pratiques

- Garde la logique d'accès aux données dans `src/lib/server`, appelée depuis des `load` courtes.
- Retourne des objets simples et sérialisables depuis `load`.
- Valide toujours les paramètres (`params.slug`, `url.searchParams`) avant de les utiliser.
- Utilise des groupes de routes pour organiser sans changer les URL.
- Prérends tout ce qui peut l'être.
- Soigne les pages d'erreur : un 404 clair rassure l'utilisateur.

## À retenir

- Les dossiers de `src/routes` définissent les URL ; les fichiers `+page`, `+layout`, `+error` et `+server` définissent leur rôle.
- `[param]` crée une route dynamique, accessible via `params`.
- `load` prépare les données avant l'affichage ; `+page.server.js` tourne uniquement sur le serveur.
- La page reçoit les données par `let { data } = $props();` ; utilise `$derived(data)` pour les routes dynamiques.
- `error()` et `redirect()` interrompent le chargement ; en SvelteKit 2, pas de `throw`.
- `+server.js` expose des API ; `$env` et le préfixe `PUBLIC_` protègent les secrets.
- `prerender`, `ssr` et `csr` ajustent la façon dont chaque page est rendue.
