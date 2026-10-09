---
title: Projet final Svelte
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru les composants, la réactivité, les templates, l'état partagé, le routage et les formulaires. Il est temps de tout assembler dans un **projet guidé** de bout en bout : **MaRoadmap**, une application web où un apprenant crée ses parcours d'apprentissage, y ajoute des étapes, coche sa progression et la suit sur un tableau de bord. Tu la construis avec Svelte 5 et SvelteKit 2, et tu la mets en ligne.

À la fin du projet, tu seras capable de :

- structurer une application SvelteKit réelle (routes, layouts, `$lib`, `$lib/server`) ;
- modéliser des données et les stocker dans une base SQLite ;
- implémenter une authentification simple par cookie de session ;
- écrire des actions de formulaire validées avec `use:enhance` ;
- partager un état d'interface (thème, notifications) avec des runes ;
- construire des composants réutilisables et accessibles ;
- construire, tester et déployer l'application avec `adapter-node`.

Prérequis : les chapitres 1 à 6 de ce cours, Node.js 18 ou plus récent, Git. Prévois six heures, réparties sur deux ou trois sessions. Il n'y a pas de solution unique : les extraits ci-dessous sont des points de départ à adapter.

## Cahier des charges

**Contexte.** Beaucoup d'apprenants suivent plusieurs roadmaps en même temps (web, mobile, DevOps) et perdent le fil. MaRoadmap leur permet de planifier et de mesurer leur avancement.

**Utilisateurs.** Un visiteur peut consulter la page d'accueil. Un utilisateur connecté gère ses propres roadmaps ; il ne voit jamais celles des autres.

### Fonctionnalités obligatoires

| Réf. | Fonctionnalité |
| --- | --- |
| F1 | Inscription et connexion par e-mail et mot de passe, déconnexion |
| F2 | Liste des roadmaps de l'utilisateur avec pourcentage d'avancement |
| F3 | Création, modification et suppression d'une roadmap (titre, description, niveau) |
| F4 | Dans une roadmap : ajouter, cocher, décocher et supprimer des étapes |
| F5 | Tableau de bord : nombre de roadmaps, d'étapes terminées, progression globale |
| F6 | Recherche et filtre par niveau sur la liste des roadmaps |
| F7 | Thème clair ou sombre mémorisé dans le navigateur |
| F8 | Notifications temporaires (« Roadmap créée ») |
| F9 | API JSON en lecture : `GET /api/roadmaps` pour l'utilisateur connecté |
| F10 | Pages d'erreur 404 et 500 personnalisées |

### Contraintes techniques

- Svelte 5 avec les runes (aucun `export let`, aucun `on:click`) et SvelteKit 2.
- Validation **côté serveur** de tous les formulaires.
- Fonctionnement des formulaires **sans JavaScript**, avec `use:enhance` en amélioration.
- Mots de passe hachés, cookies `httpOnly`, aucun secret dans le dépôt.
- Interface lisible sur mobile (largeur 360 px) et au clavier.

## Architecture visée

```text
maroadmap/
├── src/
│   ├── hooks.server.js
│   ├── app.css
│   ├── lib/
│   │   ├── server/
│   │   │   ├── db.js            <- connexion SQLite et schéma
│   │   │   ├── auth.js          <- hachage, sessions
│   │   │   └── roadmaps.js      <- requêtes
│   │   ├── components/
│   │   │   ├── BarreProgression.svelte
│   │   │   ├── CarteRoadmap.svelte
│   │   │   └── Notifications.svelte
│   │   ├── notifications.svelte.js
│   │   └── theme.svelte.js
│   └── routes/
│       ├── +layout.svelte
│       ├── +layout.server.js
│       ├── +page.svelte                     <- accueil
│       ├── connexion/ inscription/
│       ├── (app)/
│       │   ├── +layout.server.js            <- protège la section
│       │   ├── tableau-de-bord/
│       │   └── roadmaps/
│       │       ├── +page.svelte
│       │       ├── nouvelle/
│       │       └── [id]/
│       └── api/roadmaps/+server.js
└── package.json
```

Le groupe `(app)` regroupe les pages privées sans changer les URL ; son `+layout.server.js` redirige vers `/connexion` si personne n'est connecté, ce qui évite de répéter la vérification dans chaque page.

## Étape 1 : initialiser et modéliser

Crée le projet, installe les dépendances et prépare la base :

```bash
npx sv create maroadmap
cd maroadmap
npm install
npm install better-sqlite3 @node-rs/argon2
npm install -D @sveltejs/adapter-node
```

Le schéma de données tient en trois tables :

```js
// src/lib/server/db.js
import Database from 'better-sqlite3';

export const db = new Database('donnees.db');
db.pragma('journal_mode = WAL');
db.pragma('foreign_keys = ON');

db.exec(`
  CREATE TABLE IF NOT EXISTS utilisateurs (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    mot_de_passe TEXT NOT NULL
  );
  CREATE TABLE IF NOT EXISTS sessions (
    id TEXT PRIMARY KEY,
    utilisateur_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    expire_le INTEGER NOT NULL
  );
  CREATE TABLE IF NOT EXISTS roadmaps (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    utilisateur_id INTEGER NOT NULL REFERENCES utilisateurs(id) ON DELETE CASCADE,
    titre TEXT NOT NULL,
    description TEXT NOT NULL DEFAULT '',
    niveau TEXT NOT NULL CHECK (niveau IN ('debutant','intermediaire','professionnel'))
  );
  CREATE TABLE IF NOT EXISTS etapes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    roadmap_id INTEGER NOT NULL REFERENCES roadmaps(id) ON DELETE CASCADE,
    titre TEXT NOT NULL,
    fait INTEGER NOT NULL DEFAULT 0
  );
`);
```

Ajoute `donnees.db` et `.env` au fichier `.gitignore`. Place ensuite les requêtes dans `src/lib/server/roadmaps.js`, en filtrant **toujours** par `utilisateur_id` :

```js
import { db } from './db.js';

export function listerRoadmaps(utilisateurId, { recherche = '', niveau = '' } = {}) {
  return db.prepare(`
    SELECT r.id, r.titre, r.description, r.niveau,
           COUNT(e.id) AS total,
           COALESCE(SUM(e.fait), 0) AS faites
    FROM roadmaps r
    LEFT JOIN etapes e ON e.roadmap_id = r.id
    WHERE r.utilisateur_id = @utilisateurId
      AND r.titre LIKE @recherche
      AND (@niveau = '' OR r.niveau = @niveau)
    GROUP BY r.id
    ORDER BY r.id DESC
  `).all({ utilisateurId, recherche: `%${recherche}%`, niveau });
}

export function trouverRoadmap(utilisateurId, id) {
  return db.prepare(
    'SELECT * FROM roadmaps WHERE id = ? AND utilisateur_id = ?'
  ).get(id, utilisateurId);
}
```

> **Attention** : utilise toujours des requêtes préparées avec des paramètres (`@nom` ou `?`). Concaténer une saisie utilisateur dans le SQL ouvre la porte aux injections.

## Étape 2 : authentification et sessions

Écris `auth.js` avec quatre fonctions : `creerUtilisateur(email, motDePasse)` qui hache avec `argon2.hash`, `verifierIdentifiants`, `creerSession(utilisateurId)` qui génère un identifiant aléatoire avec `crypto.randomUUID()` et le stocke avec une date d'expiration, et `utilisateurDepuisSession(sessionId)` qui supprime la session si elle est expirée.

Relie le tout dans `hooks.server.js` :

```js
import { utilisateurDepuisSession } from '$lib/server/auth.js';

export async function handle({ event, resolve }) {
  const sessionId = event.cookies.get('session');
  event.locals.utilisateur = sessionId
    ? utilisateurDepuisSession(sessionId)
    : null;
  return resolve(event);
}
```

Dans `src/routes/+layout.server.js`, retourne `{ utilisateur: locals.utilisateur }` pour que le menu sache qui est connecté. Dans `(app)/+layout.server.js` :

```js
import { redirect } from '@sveltejs/kit';

export function load({ locals }) {
  if (!locals.utilisateur) redirect(303, '/connexion');
  return {};
}
```

Les actions `inscription` et `connexion` suivent le modèle du chapitre 6 : lecture de `formData`, validation (e-mail valide, mot de passe de 8 caractères minimum), `fail(400, ...)`, pose du cookie puis `redirect(303, '/tableau-de-bord')`. Pour la déconnexion, crée une action `deconnexion` dans le layout racine : suppression de la session en base, `cookies.delete('session', { path: '/' })`, redirection.

:::quiz
Pourquoi les requêtes SQL du projet filtrent-elles systématiquement par `utilisateur_id` ?
- [ ] Pour accélérer l'affichage des pages
- [x] Pour qu'un utilisateur ne puisse jamais lire ou modifier les données d'un autre, même en devinant un identifiant
- [ ] Parce que SQLite l'exige
- [ ] Pour éviter d'utiliser des sessions
> Vérifier l'appartenance de chaque ressource côté serveur empêche les accès non autorisés (IDOR) : la sécurité ne repose jamais sur le fait que l'URL soit difficile à deviner.
:::

## Étape 3 : les routes et les formulaires de roadmaps

La page `roadmaps/+page.server.js` lit `url.searchParams` pour la recherche et le niveau :

```js
import { listerRoadmaps } from '$lib/server/roadmaps.js';

export function load({ locals, url }) {
  const recherche = url.searchParams.get('q') ?? '';
  const niveau = url.searchParams.get('niveau') ?? '';
  return {
    roadmaps: listerRoadmaps(locals.utilisateur.id, { recherche, niveau }),
    filtres: { recherche, niveau }
  };
}
```

Le formulaire de filtre est un simple `<form method="GET">` : l'état de la recherche vit **dans l'URL**, ce qui permet de partager ou de recharger la page sans rien perdre.

```svelte
<form method="GET" role="search">
  <input name="q" value={data.filtres.recherche} placeholder="Rechercher…" />
  <select name="niveau" value={data.filtres.niveau}>
    <option value="">Tous les niveaux</option>
    <option value="debutant">Débutant</option>
    <option value="intermediaire">Intermédiaire</option>
    <option value="professionnel">Professionnel</option>
  </select>
  <button>Filtrer</button>
</form>
```

Pour la page `roadmaps/[id]`, le `load` charge la roadmap **et** ses étapes (404 si la roadmap n'appartient pas à l'utilisateur). Ses actions nommées sont `ajouterEtape`, `basculerEtape`, `supprimerEtape`, `modifier` et `supprimer`. Chaque action vérifie d'abord l'appartenance de la roadmap avant de toucher aux étapes.

## Étape 4 : composants et état d'interface

Extrais des composants courts et accessibles. La barre de progression en est un bon exemple :

```svelte
<!-- src/lib/components/BarreProgression.svelte -->
<script>
  let { faites, total, libelle = 'Progression' } = $props();
  let pourcentage = $derived(total > 0 ? Math.round((faites / total) * 100) : 0);
</script>

<div
  class="barre"
  role="progressbar"
  aria-label={libelle}
  aria-valuemin="0"
  aria-valuemax="100"
  aria-valuenow={pourcentage}
>
  <div class="remplissage" style:width="{pourcentage}%"></div>
</div>
<p>{faites}/{total} étapes ({pourcentage} %)</p>
```

Pour les notifications (F8), un module d'état partagé suffit :

```js
// src/lib/notifications.svelte.js
class Notifications {
  liste = $state([]);

  ajouter(message, type = 'info') {
    const id = crypto.randomUUID();
    this.liste.push({ id, message, type });
    setTimeout(() => this.retirer(id), 4000);
  }

  retirer(id) {
    this.liste = this.liste.filter((n) => n.id !== id);
  }
}

export const notifications = new Notifications();
```

Le composant `Notifications.svelte` parcourt `notifications.liste` avec un `{#each}` à clé et un `transition:fly`. Il est placé une seule fois dans le layout racine. Comme ce module ne contient **aucune donnée utilisateur** et n'est modifié que dans le navigateur, il ne présente pas le risque de fuite entre visiteurs décrit au chapitre 4. Déclenche une notification après une action réussie en lisant `form` dans un `$effect`, ou dans la fonction retournée par `use:enhance`.

Le thème (F7) suit le modèle de `preferences.svelte.js` du chapitre 4 : lecture de `localStorage` protégée par `browser`, et une classe appliquée sur `document.documentElement`.

## Étape 5 : tableau de bord et API

Le `load` de `tableau-de-bord` fait une requête agrégée (nombre de roadmaps, d'étapes, d'étapes faites). La page affiche trois cartes de chiffres et une `BarreProgression` globale ; chaque valeur composée est un `$derived` à partir de `data`.

L'API (F9) reste volontairement minimale :

```js
// src/routes/api/roadmaps/+server.js
import { json, error } from '@sveltejs/kit';
import { listerRoadmaps } from '$lib/server/roadmaps.js';

export function GET({ locals }) {
  if (!locals.utilisateur) error(401, 'Non authentifié');
  return json(listerRoadmaps(locals.utilisateur.id));
}
```

## Étape 6 : qualité, accessibilité et déploiement

Avant de publier, passe la checklist suivante :

- navigue **uniquement au clavier** : tous les boutons et champs sont atteignables, le focus est visible ;
- chaque champ a un `label`, les messages d'erreur sont reliés au champ (`aria-describedby`) ;
- ajoute `<svelte:head>` avec un `title` unique par page ;
- vérifie le rendu à 360 px de large ;
- désactive JavaScript et teste connexion, création de roadmap et ajout d'étape ;
- lance `npm run check` pour détecter les erreurs de typage ou de syntaxe.

Pour le déploiement, configure `adapter-node` dans `svelte.config.js`, puis :

```bash
npm run build
PORT=3000 ORIGIN=https://maroadmap.exemple.com node build
```

Choisis un hébergement qui conserve un **disque persistant** (VPS, Render avec disque, Railway avec volume), car SQLite écrit dans un fichier. Sur une plateforme serverless sans disque, remplace SQLite par PostgreSQL ou Supabase en ne modifiant que le dossier `$lib/server`.

## Atelier guidé : le déroulé du projet

Voici l'ordre de travail conseillé, avec une durée indicative.

1. (20 min) Initialise le projet, installe les dépendances, crée la structure de dossiers et le dépôt Git avec un `.gitignore` correct.
2. (30 min) Écris `db.js` et `roadmaps.js` ; teste les requêtes avec un petit script Node.
3. (45 min) Implémente `auth.js`, `hooks.server.js`, les pages `inscription` et `connexion`, et la déconnexion.
4. (20 min) Crée le layout racine avec menu adapté à l'état connecté, puis le groupe `(app)` protégé.
5. (45 min) Liste des roadmaps avec recherche et filtre par niveau via l'URL.
6. (45 min) Création, modification et suppression d'une roadmap, avec validation et `fail`.
7. (45 min) Page détail : étapes, bascule `fait`, suppression, avec `use:enhance`.
8. (30 min) Composants `BarreProgression`, `CarteRoadmap`, notifications et thème.
9. (25 min) Tableau de bord et API JSON.
10. (30 min) Pages d'erreur, accessibilité, test sans JavaScript et sur mobile.
11. (25 min) Build, déploiement et vérification en ligne.

Auto-évaluation : présente ton projet à voix haute en cinq minutes, comme à un client. Peux-tu expliquer où se trouve chaque donnée (base, URL, état de composant, cookie) et pourquoi ?

## Checklist d'acceptation

Ton projet est terminé quand toutes ces cases peuvent être cochées :

- Je peux m'inscrire, me déconnecter, me reconnecter ; un mauvais mot de passe affiche une erreur claire et conserve l'e-mail saisi.
- Un visiteur non connecté qui ouvre `/roadmaps` est redirigé vers `/connexion`.
- Je peux créer, modifier et supprimer une roadmap ; les erreurs de validation s'affichent sous les champs.
- Je peux ajouter, cocher et supprimer des étapes ; le pourcentage se met à jour sans recharger la page.
- La recherche et le filtre fonctionnent, et l'URL reflète les filtres.
- Un utilisateur A ne peut ni voir ni modifier la roadmap d'un utilisateur B, même en changeant l'identifiant dans l'URL (testé : réponse 404).
- Le tableau de bord affiche des chiffres cohérents avec les données.
- Le thème est conservé après rechargement ; les notifications disparaissent seules.
- Tous les formulaires fonctionnent avec JavaScript désactivé.
- Aucun `export let`, `on:click` ni store hérité de Svelte 4 dans le code.
- `npm run build` et `npm run check` passent sans erreur.
- Aucun secret n'est présent dans le dépôt Git ; les mots de passe sont hachés.
- L'application est déployée et accessible par une URL publique en HTTPS.

:::quiz
Où doit se faire la vérification de l'appartenance d'une roadmap à l'utilisateur connecté ?
- [ ] Dans le composant Svelte, en masquant les boutons
- [x] Côté serveur, dans chaque `load` et chaque action qui touche la ressource
- [ ] Dans `localStorage`
- [ ] Nulle part : les identifiants sont aléatoires
> Seul le serveur est digne de confiance. Cacher un bouton n'empêche pas d'envoyer une requête manuelle.
:::

## Erreurs fréquentes

- **Oublier de filtrer par `utilisateur_id`.** C'est la faille la plus grave du projet.
- **Faire confiance à un champ caché.** Un `id` dans un champ `hidden` peut être modifié : revérifie l'appartenance côté serveur.
- **Déstructurer `data` sans `$derived`** dans une page dynamique : l'affichage ne suit pas la navigation.
- **Stocker l'utilisateur courant dans un module global.** Utilise `locals` et `data`, jamais un état partagé côté serveur.
- **Déployer sur un hébergeur sans disque persistant avec SQLite.** La base disparaît à chaque redémarrage.
- **Oublier `ORIGIN`.** Les formulaires échouent en production avec une erreur 403.
- **Négliger l'accessibilité.** Un site inutilisable au clavier est un site à moitié fini.

## Bonnes pratiques

- Avance par petites étapes et fais un commit Git après chaque fonctionnalité qui marche.
- Garde la logique métier dans `$lib/server` et des routes très fines.
- Valide toutes les entrées côté serveur et ne renvoie jamais de donnée sensible.
- Nomme les composants et fonctions en français cohérent, ou en anglais partout, mais pas un mélange aléatoire.
- Écris un `README` avec les commandes d'installation, de lancement et de déploiement.
- Relis ton code avec la question : « que se passe-t-il si deux utilisateurs font la même chose en même temps ? »
- Pour aller plus loin : tests avec Vitest et Playwright, authentification par un service tiers, envoi d'e-mails, import et export des roadmaps, mode hors ligne.

## À retenir

- Un projet réel assemble tout le cours : composants et runes pour l'interface, `load` pour lire, actions pour écrire, hooks pour la session.
- La sécurité vit côté serveur : appartenance des données, validation, cookies `httpOnly`, mots de passe hachés.
- L'état se place au bon endroit : base de données pour le durable, URL pour les filtres, `$state` pour l'éphémère, module partagé pour l'interface globale.
- L'amélioration progressive garantit que l'application fonctionne même dans de mauvaises conditions réseau.
- Un projet se termine par un déploiement vérifié, une checklist d'acceptation validée et une présentation claire.
