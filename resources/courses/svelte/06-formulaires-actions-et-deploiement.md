---
title: Formulaires, actions et déploiement
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Une application ne fait pas que lire des données : elle en reçoit. Inscription, connexion, commande, candidature avec CV... Tout passe par des formulaires. SvelteKit propose une approche élégante : les **actions de formulaire**, qui fonctionnent même sans JavaScript, et que l'on améliore progressivement avec `use:enhance`. Ce chapitre se termine par la mise en ligne de ton application.

À la fin du chapitre, tu seras capable de :

- écrire une action de formulaire dans `+page.server.js` et la lier à un `<form>` ;
- valider les données côté serveur et renvoyer des erreurs avec `fail` ;
- afficher ces erreurs et conserver la saisie grâce à la prop `form` ;
- améliorer l'expérience avec `use:enhance` ;
- gérer une session simple avec les cookies et `hooks.server.js` ;
- choisir un adaptateur et déployer sur un hébergeur.

Prérequis : les chapitres 1 à 5. Prévois trois heures. Un compte sur un hébergeur (Vercel, Render ou un VPS) est utile pour la dernière partie, mais pas obligatoire.

## Pourquoi des actions ?

Un `<form method="POST">` HTML envoie les données au serveur et recharge la page : c'est vieux comme le web, et ça marche partout, même si le JavaScript n'a pas fini de charger sur un téléphone à la connexion lente. SvelteKit s'appuie sur ce mécanisme : le serveur reçoit la requête dans une **action**, la traite, puis SvelteKit réaffiche la page avec le résultat.

Avantages :

- le formulaire fonctionne **sans JavaScript** (progressive enhancement) ;
- la validation et l'accès à la base de données restent **sur le serveur**, là où tu contrôles tout ;
- tu n'as pas à écrire de route d'API ni de `fetch` manuel.

> **À retenir** : côté navigateur, on peut tricher. Toute validation importante doit être refaite côté serveur.

## Ta première action

```js
// src/routes/contact/+page.server.js
import { fail } from '@sveltejs/kit';

export const actions = {
  default: async ({ request }) => {
    const donnees = await request.formData();
    const nom = donnees.get('nom')?.toString().trim() ?? '';
    const email = donnees.get('email')?.toString().trim() ?? '';
    const message = donnees.get('message')?.toString().trim() ?? '';

    const erreurs = {};
    if (nom.length < 2) erreurs.nom = 'Le nom est trop court.';
    if (!email.includes('@')) erreurs.email = 'Adresse e-mail invalide.';
    if (message.length < 10) erreurs.message = 'Écris au moins 10 caractères.';

    if (Object.keys(erreurs).length > 0) {
      return fail(400, { erreurs, valeurs: { nom, email, message } });
    }

    // Ici : enregistrer en base, envoyer un e-mail...
    return { succes: true };
  }
};
```

Et la page :

```svelte
<!-- src/routes/contact/+page.svelte -->
<script>
  let { form } = $props();
</script>

<h1>Contactez-nous</h1>

{#if form?.succes}
  <p class="ok">Merci, ton message a bien été envoyé !</p>
{/if}

<form method="POST">
  <label>
    Nom
    <input name="nom" value={form?.valeurs?.nom ?? ''} />
  </label>
  {#if form?.erreurs?.nom}<p class="erreur">{form.erreurs.nom}</p>{/if}

  <label>
    E-mail
    <input name="email" type="email" value={form?.valeurs?.email ?? ''} />
  </label>
  {#if form?.erreurs?.email}<p class="erreur">{form.erreurs.email}</p>{/if}

  <label>
    Message
    <textarea name="message">{form?.valeurs?.message ?? ''}</textarea>
  </label>
  {#if form?.erreurs?.message}<p class="erreur">{form.erreurs.message}</p>{/if}

  <button>Envoyer</button>
</form>
```

Décryptage :

- `export const actions` regroupe les actions ; `default` s'exécute pour un `<form method="POST">` sans attribut `action` ;
- `request.formData()` donne accès aux champs, par leur attribut `name` ;
- `fail(400, {...})` renvoie un statut d'erreur et des données **sans** interrompre la page ;
- la prop `form` contient ce qu'a retourné l'action. Elle vaut `undefined` avant toute soumission, d'où les `?.` ;
- on **renvoie les valeurs saisies** pour ne pas obliger l'utilisateur à tout retaper. Ne renvoie jamais un mot de passe.

:::quiz
Que fait `fail(400, { erreurs })` dans une action de formulaire ?
- [ ] Il interrompt le serveur
- [x] Il renvoie un statut d'erreur avec des données, accessibles dans la prop `form` de la page
- [ ] Il redirige vers une page 400
- [ ] Il supprime les valeurs saisies
> `fail` signale un échec de validation : la page est réaffichée avec le statut donné, et les données retournées arrivent dans `form`.
:::

## Plusieurs actions dans une même page

Pour des boutons différents (« Enregistrer », « Supprimer »), nomme les actions et cible-les avec l'attribut `action="?/nom"` :

```js
export const actions = {
  creer: async ({ request }) => {
    const donnees = await request.formData();
    // ...
    return { succes: true };
  },
  supprimer: async ({ request }) => {
    const donnees = await request.formData();
    const id = donnees.get('id');
    // ...
    return { supprime: id };
  }
};
```

```svelte
<form method="POST" action="?/creer">
  <input name="titre" />
  <button>Créer</button>
</form>

<form method="POST" action="?/supprimer">
  <input type="hidden" name="id" value={tache.id} />
  <button>Supprimer</button>
</form>
```

Tu ne peux pas mélanger une action `default` et des actions nommées dans la même page.

## Améliorer avec use:enhance

Sans JavaScript, la page entière se recharge à chaque envoi. Avec **`use:enhance`**, SvelteKit envoie le formulaire en arrière-plan avec `fetch`, met à jour `form` et les données, et conserve le défilement et le focus : une expérience fluide.

```svelte
<script>
  import { enhance } from '$app/forms';

  let { form } = $props();
  let envoi = $state(false);
</script>

<form
  method="POST"
  use:enhance={() => {
    envoi = true;
    return async ({ update }) => {
      await update();
      envoi = false;
    };
  }}
>
  <input name="titre" />
  <button disabled={envoi}>{envoi ? 'Envoi…' : 'Créer'}</button>
</form>
```

- `use:enhance` seul suffit dans la plupart des cas ;
- la fonction passée s'exécute **avant** l'envoi (idéale pour afficher un chargement) et renvoie une fonction exécutée **après** la réponse ;
- `update()` applique le comportement par défaut (mise à jour de `form`, réinitialisation du formulaire en cas de succès, rafraîchissement des données de la page).

Le `use:` est une **action Svelte** : une fonction qui reçoit l'élément DOM. Ne la confonds pas avec les actions de formulaire côté serveur, c'est un homonyme malheureux.

### Rediriger après succès

Après la création d'une ressource, on redirige pour éviter qu'un rechargement renvoie le formulaire :

```js
import { fail, redirect } from '@sveltejs/kit';

export const actions = {
  creer: async ({ request }) => {
    const donnees = await request.formData();
    const titre = donnees.get('titre')?.toString().trim();
    if (!titre) return fail(400, { erreur: 'Titre requis' });

    const id = await creerRoadmap({ titre });
    redirect(303, `/roadmaps/${id}`);
  }
};
```

Le statut **303** indique « va voir cette page avec une requête GET », le bon réflexe après un POST.

## Cookies, sessions et hooks

Pour une connexion, on stocke un **identifiant de session** dans un cookie. Le fichier `src/hooks.server.js` s'exécute à chaque requête : c'est le bon endroit pour lire ce cookie et renseigner `event.locals`.

```js
// src/hooks.server.js
import { trouverUtilisateurParSession } from '$lib/server/auth.js';

export async function handle({ event, resolve }) {
  const sessionId = event.cookies.get('session');

  if (sessionId) {
    event.locals.utilisateur = await trouverUtilisateurParSession(sessionId);
  }

  return resolve(event);
}
```

L'action de connexion crée la session et pose le cookie :

```js
// src/routes/connexion/+page.server.js
import { fail, redirect } from '@sveltejs/kit';
import { verifierIdentifiants, creerSession } from '$lib/server/auth.js';

export const actions = {
  default: async ({ request, cookies }) => {
    const donnees = await request.formData();
    const email = donnees.get('email')?.toString() ?? '';
    const motDePasse = donnees.get('motdepasse')?.toString() ?? '';

    const utilisateur = await verifierIdentifiants(email, motDePasse);
    if (!utilisateur) {
      return fail(401, { erreur: 'Identifiants incorrects.', email });
    }

    const sessionId = await creerSession(utilisateur.id);
    cookies.set('session', sessionId, {
      path: '/',
      httpOnly: true,
      sameSite: 'lax',
      secure: true,
      maxAge: 60 * 60 * 24 * 7
    });

    redirect(303, '/tableau-de-bord');
  }
};
```

Les options de cookie sont essentielles : `httpOnly` empêche JavaScript de lire le cookie, `secure` l'impose en HTTPS, `sameSite` limite les envois depuis d'autres sites. Dans un `load`, protège ensuite tes pages privées en testant `locals.utilisateur` et en appelant `redirect(303, '/connexion')`.

> **Attention** : n'écris jamais toi-même un hachage de mot de passe maison. Utilise une bibliothèque éprouvée (argon2, bcrypt) ou un service d'authentification (Supabase Auth, Better Auth, Lucia-like) et ne stocke jamais de mot de passe en clair.

SvelteKit vérifie aussi par défaut l'en-tête `Origin` des soumissions de formulaires pour se protéger du CSRF. Ne le désactive pas sans raison.

:::quiz
Quel est l'avantage principal de `use:enhance` sur un formulaire SvelteKit ?
- [ ] Il rend la validation côté serveur inutile
- [ ] Il transforme le formulaire en route d'API
- [x] Il envoie le formulaire en arrière-plan sans recharger la page, tout en gardant un fonctionnement correct sans JavaScript
- [ ] Il chiffre les mots de passe
> `use:enhance` ajoute une couche d'amélioration progressive : le formulaire marche sans JavaScript, et avec JavaScript l'envoi est fluide.
:::

## Déployer avec un adaptateur

Un projet SvelteKit se construit pour une plateforme précise grâce à un **adaptateur**, configuré dans `svelte.config.js`.

| Adaptateur | Usage |
| --- | --- |
| `adapter-auto` | Détecte la plateforme (Vercel, Netlify, Cloudflare) au moment du build |
| `adapter-node` | Produit un serveur Node.js à lancer sur un VPS, Render ou Docker |
| `adapter-static` | Génère un site statique (toutes les pages prérendues) |
| `adapter-vercel` | Fonctionnalités avancées spécifiques à Vercel |

Pour un serveur Node :

```bash
npm install -D @sveltejs/adapter-node
```

```js
// svelte.config.js
import adapter from '@sveltejs/adapter-node';

export default {
  kit: {
    adapter: adapter()
  }
};
```

```bash
npm run build
node build
```

La construction crée un dossier `build/` contenant le serveur. Les variables d'environnement se fournissent à l'exécution : par exemple `PORT=3000 ORIGIN=https://monsite.com DATABASE_URL=... node build`. La variable `ORIGIN` est nécessaire pour que la vérification CSRF connaisse l'adresse publique.

### Checklist avant la mise en ligne

1. `npm run build` passe sans erreur, et `npm run preview` donne le même résultat qu'en développement.
2. Aucun secret dans le dépôt : `.env` figure dans `.gitignore` et les variables sont saisies dans le tableau de bord de l'hébergeur.
3. Les pages statiques ont `prerender = true`.
4. Les cookies de session sont `httpOnly`, `secure` et `sameSite`.
5. Les pages d'erreur 404 et 500 sont personnalisées.
6. Les images sont compressées (format WebP ou AVIF) : important pour un public à forfait de données limité.
7. Les métadonnées (`<svelte:head>` avec `title` et `description`) sont renseignées.

```svelte
<svelte:head>
  <title>Contact | DevRoad</title>
  <meta name="description" content="Écris-nous pour toute question." />
</svelte:head>
```

## Atelier guidé : un formulaire de candidature complet

Compte deux heures et demie.

1. Crée la route `/candidature` avec un formulaire : nom, e-mail, poste (liste déroulante), message.
2. Écris l'action `default` dans `+page.server.js` : lecture de `formData`, nettoyage avec `trim`.
3. Valide chaque champ côté serveur et retourne `fail(400, { erreurs, valeurs })`.
4. Affiche les erreurs sous chaque champ et réinjecte les valeurs saisies.
5. Ajoute `use:enhance` et un état `envoi` pour désactiver le bouton pendant l'envoi.
6. Stocke la candidature (fichier JSON, SQLite ou Supabase selon ton choix) dans une fonction de `$lib/server`.
7. Redirige vers `/candidature/merci` avec `redirect(303, ...)` après succès.
8. Ajoute une page `/connexion` avec cookie `httpOnly` et `hooks.server.js` pour renseigner `locals.utilisateur`.
9. Protège `/admin` dans son `load` : redirection vers `/connexion` si l'utilisateur est absent.
10. Installe `adapter-node`, lance `npm run build` puis `node build`, et teste le formulaire avec JavaScript désactivé dans le navigateur.

Auto-évaluation : explique ce qui se passe, étape par étape, entre le clic sur « Envoyer » et l'apparition d'un message d'erreur, avec et sans `use:enhance`.

## Erreurs fréquentes

- **Oublier `method="POST"`.** Le formulaire envoie un GET et les données apparaissent dans l'URL.
- **Oublier l'attribut `name` sur un champ.** Il n'apparaît pas dans `formData`.
- **Ne valider que côté navigateur.** N'importe qui peut envoyer une requête sans passer par ton interface.
- **Renvoyer des données sensibles dans `fail`.** Elles sont réaffichées dans la page.
- **Mélanger `default` et actions nommées.** SvelteKit refuse cette combinaison.
- **Ne pas définir `ORIGIN` avec `adapter-node` derrière un proxy.** Les soumissions sont rejetées avec une erreur 403.
- **Utiliser `redirect` avec un statut 302 après un POST.** Préfère 303.

## Bonnes pratiques

- Écris les actions pour qu'elles fonctionnent d'abord sans JavaScript, puis ajoute `use:enhance`.
- Centralise la validation dans un module (ou avec une bibliothèque comme Zod) pour la réutiliser.
- Retourne des messages d'erreur précis et bienveillants, en français.
- Limite la taille des fichiers téléversés et vérifie leur type côté serveur.
- Ne mets jamais de secret dans le code : variables d'environnement uniquement.
- Prévois la sauvegarde de la base de données et la surveillance des erreurs avant la mise en production.

## À retenir

- Une **action** est une fonction serveur qui reçoit un `<form method="POST">` ; son résultat arrive dans la prop `form`.
- `fail(statut, données)` signale une erreur de validation sans casser la page ; `redirect(303, ...)` termine proprement un succès.
- Les noms des champs (`name`) alimentent `formData` ; la validation serveur est obligatoire.
- `use:enhance` ajoute une expérience fluide sans retirer le fonctionnement de base.
- `hooks.server.js`, `locals` et les cookies `httpOnly` structurent une authentification.
- Un **adaptateur** (`adapter-node`, `adapter-static`, `adapter-vercel`...) prépare le build pour ta plateforme ; vérifie variables d'environnement, `ORIGIN` et pages d'erreur avant de publier.
