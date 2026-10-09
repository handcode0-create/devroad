---
title: HTTP et APIs
minutes: 130
level: beginner
---

## Ce que tu vas apprendre

Une application mobile, un site web ou un autre service ont besoin de parler à ton programme. Ils le font via le protocole **HTTP**, en appelant une **API**. Dans ce chapitre, tu comprends comment fonctionne une requête HTTP, tu crées un serveur avec le module intégré `node:http`, puis tu construis une API REST complète avec **Express**. C'est le cœur du métier de développeur back-end.

À la fin du chapitre, tu seras capable de :

- décrire une requête et une réponse HTTP (méthode, URL, en-têtes, corps, statut) ;
- créer un serveur avec `node:http` et en comprendre les limites ;
- construire une API REST avec Express : routes, paramètres, corps JSON ;
- utiliser des middlewares (journalisation, JSON, gestion d'erreurs) ;
- choisir les bons codes de statut ;
- tester une API avec `curl` et appeler une API externe avec `fetch`.

Prérequis : les chapitres 1 et 2 de ce cours. Prévois environ deux heures dix.

## Comprendre HTTP

**HTTP** est un protocole de type question / réponse. Le **client** (navigateur, application, `curl`) envoie une **requête** ; le **serveur** répond.

Une requête contient :

- une **méthode** : ce qu'on veut faire ;
- une **URL** : la ressource visée ;
- des **en-têtes** (*headers*) : informations complémentaires (type de contenu, autorisation) ;
- éventuellement un **corps** (*body*) : les données envoyées.

```text
POST /roadmaps HTTP/1.1
Host: api.devroad.exemple
Content-Type: application/json

{"titre":"Docker","niveau":"intermediaire"}
```

La réponse contient un **code de statut**, des en-têtes et souvent un corps :

```text
HTTP/1.1 201 Created
Content-Type: application/json

{"id":4,"titre":"Docker","niveau":"intermediaire"}
```

### Les méthodes principales

| Méthode | Rôle | Exemple |
| --- | --- | --- |
| `GET` | Lire | Lister les roadmaps |
| `POST` | Créer | Ajouter une roadmap |
| `PUT` / `PATCH` | Remplacer / modifier | Changer le titre |
| `DELETE` | Supprimer | Retirer une roadmap |

### Les codes de statut à connaître

| Code | Signification | Quand l'utiliser |
| --- | --- | --- |
| `200` | OK | Lecture ou modification réussie |
| `201` | Créé | Après une création |
| `204` | Pas de contenu | Suppression réussie |
| `400` | Requête invalide | Données mal formées |
| `401` | Non authentifié | Identité absente ou incorrecte |
| `403` | Interdit | Authentifié mais sans droit |
| `404` | Introuvable | Ressource inexistante |
| `422` | Entité non traitable | Données bien formées mais invalides |
| `500` | Erreur serveur | Bug de ton côté |

Retiens la famille : `2xx` succès, `4xx` erreur du **client**, `5xx` erreur du **serveur**.

:::quiz
Quel code de statut convient après la création réussie d'une ressource ?
- [ ] 200
- [x] 201
- [ ] 301
- [ ] 404
> 201 (Created) indique que la ressource a bien été créée. 200 est plus générique, et 404 signifie « introuvable ».
:::

## Un premier serveur avec node:http

Node fournit tout le nécessaire pour écouter des requêtes, sans aucune installation.

```js
// src/serveur-brut.js
import http from 'node:http';

const serveur = http.createServer((req, res) => {
  console.log(`${req.method} ${req.url}`);

  if (req.method === 'GET' && req.url === '/') {
    res.writeHead(200, { 'Content-Type': 'text/plain; charset=utf-8' });
    res.end('Bienvenue sur l’API DevRoad');
    return;
  }

  if (req.method === 'GET' && req.url === '/roadmaps') {
    res.writeHead(200, { 'Content-Type': 'application/json; charset=utf-8' });
    res.end(JSON.stringify([{ id: 1, titre: 'React' }, { id: 2, titre: 'Node.js' }]));
    return;
  }

  res.writeHead(404, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ message: 'Route introuvable' }));
});

const PORT = process.env.PORT ?? 3000;
serveur.listen(PORT, () => {
  console.log(`Serveur démarré sur http://localhost:${PORT}`);
});
```

Lance-le avec `node src/serveur-brut.js`, puis teste dans un second terminal :

```bash
curl http://localhost:3000/
curl http://localhost:3000/roadmaps
curl -i http://localhost:3000/inconnu
```

L'option `-i` de `curl` affiche aussi les en-têtes et le statut. Cela fonctionne, mais imagine l'ajout de dix routes, de paramètres dans l'URL, de lecture du corps JSON, de validation : le code devient vite pénible. C'est pour cela qu'on utilise un framework.

## Express : un framework minimaliste

**Express** est le framework web le plus répandu pour Node.js. Il simplifie le routage, la lecture du corps de requête et les réponses. Installe-le :

```bash
npm install express
```

Le même serveur, version Express :

```js
// src/app.js
import express from 'express';

const app = express();

app.get('/', (req, res) => {
  res.send('Bienvenue sur l’API DevRoad');
});

app.get('/roadmaps', (req, res) => {
  res.json([{ id: 1, titre: 'React' }, { id: 2, titre: 'Node.js' }]);
});

const PORT = process.env.PORT ?? 3000;
app.listen(PORT, () => console.log(`API sur http://localhost:${PORT}`));
```

`res.json` envoie automatiquement le bon en-tête `Content-Type` et sérialise l'objet. Les gestionnaires reçoivent trois informations : `req` (la requête), `res` (la réponse) et, pour les middlewares, `next`.

## Construire une API REST

**REST** est un style d'API où chaque **ressource** (roadmaps, fiches, utilisateurs) a une URL, et où l'on agit dessus avec les méthodes HTTP. Voici le schéma classique pour la ressource `roadmaps` :

| Méthode et URL | Action |
| --- | --- |
| `GET /roadmaps` | Liste |
| `GET /roadmaps/:id` | Détail |
| `POST /roadmaps` | Création |
| `PUT /roadmaps/:id` | Modification |
| `DELETE /roadmaps/:id` | Suppression |

Les noms d'URL sont des **noms au pluriel**, pas des verbes : `/roadmaps`, et non `/getRoadmaps`. Voici l'API complète, avec un stockage en mémoire pour commencer :

```js
// src/app.js
import express from 'express';

const app = express();
app.use(express.json());              // lit les corps JSON

let roadmaps = [
  { id: 1, titre: 'React', niveau: 'debutant' },
  { id: 2, titre: 'Node.js', niveau: 'intermediaire' },
];
let prochainId = 3;

// Lister, avec filtre optionnel ?niveau=debutant
app.get('/roadmaps', (req, res) => {
  const { niveau } = req.query;
  const resultat = niveau ? roadmaps.filter((r) => r.niveau === niveau) : roadmaps;
  res.json(resultat);
});

// Détail : :id est un paramètre d'URL
app.get('/roadmaps/:id', (req, res) => {
  const id = Number(req.params.id);
  const roadmap = roadmaps.find((r) => r.id === id);

  if (!roadmap) {
    return res.status(404).json({ message: 'Roadmap introuvable' });
  }
  res.json(roadmap);
});

// Création
app.post('/roadmaps', (req, res) => {
  const { titre, niveau } = req.body ?? {};

  if (typeof titre !== 'string' || titre.trim().length < 3) {
    return res.status(422).json({ message: 'Le titre doit contenir au moins 3 caractères' });
  }

  const roadmap = { id: prochainId++, titre: titre.trim(), niveau: niveau ?? 'debutant' };
  roadmaps.push(roadmap);
  res.status(201).json(roadmap);
});

// Suppression
app.delete('/roadmaps/:id', (req, res) => {
  const id = Number(req.params.id);
  const avant = roadmaps.length;
  roadmaps = roadmaps.filter((r) => r.id !== id);

  if (roadmaps.length === avant) {
    return res.status(404).json({ message: 'Roadmap introuvable' });
  }
  res.status(204).end();
});

const PORT = process.env.PORT ?? 3000;
app.listen(PORT, () => console.log(`API sur http://localhost:${PORT}`));
```

Teste chaque route avec `curl` :

```bash
curl http://localhost:3000/roadmaps?niveau=debutant
curl -X POST http://localhost:3000/roadmaps \
  -H "Content-Type: application/json" \
  -d '{"titre":"Docker","niveau":"intermediaire"}'
curl -i -X DELETE http://localhost:3000/roadmaps/1
```

Trois sources de données à distinguer dans Express :

- `req.params` : les segments dynamiques de l'URL (`/roadmaps/:id`) ;
- `req.query` : les paramètres après le `?` ;
- `req.body` : le corps de la requête, disponible grâce à `express.json()`.

> **Attention** : tout ce qui vient de `req.params`, `req.query` et `req.body` est du texte brut contrôlé par l'utilisateur. Convertis et **valide** toujours avant de t'en servir. Ici, `Number(req.params.id)` donne `NaN` pour une valeur invalide, et la recherche échoue proprement.

## Les middlewares

Un **middleware** est une fonction qui s'exécute entre la réception de la requête et l'envoi de la réponse. Elle peut lire ou modifier `req` et `res`, puis passer la main avec `next()` ou répondre elle-même. `express.json()` en est un exemple. Voici un middleware de journalisation :

```js
function journal(req, res, next) {
  const debut = Date.now();
  res.on('finish', () => {
    console.log(`${req.method} ${req.originalUrl} -> ${res.statusCode} (${Date.now() - debut} ms)`);
  });
  next();
}

app.use(journal);
```

Les middlewares s'exécutent **dans l'ordre où tu les déclares** : place `express.json()` et `journal` avant tes routes. Un middleware peut aussi protéger des routes :

```js
function exigerCle(req, res, next) {
  if (req.get('x-api-key') !== process.env.API_KEY) {
    return res.status(401).json({ message: 'Clé API manquante ou invalide' });
  }
  next();
}

app.delete('/roadmaps/:id', exigerCle, (req, res) => {
  // ...
});
```

### Gérer les routes inconnues et les erreurs

Deux middlewares se placent **après** toutes les routes. Le premier attrape les URL inconnues, le second centralise les erreurs (il a quatre paramètres, ce qui le distingue pour Express) :

```js
app.use((req, res) => {
  res.status(404).json({ message: 'Route introuvable' });
});

app.use((err, req, res, next) => {
  console.error(err);
  res.status(500).json({ message: 'Erreur interne du serveur' });
});
```

Ne renvoie jamais au client le détail de l'erreur ou la pile d'appels : ces informations aident un attaquant. Journalise-les côté serveur.

:::quiz
Pourquoi express.json() doit-il être déclaré avant les routes qui lisent req.body ?
- [ ] Parce qu'Express trie les middlewares par ordre alphabétique
- [x] Parce que les middlewares s'exécutent dans l'ordre de déclaration et req.body doit être rempli avant la route
- [ ] Parce que les routes ne fonctionnent pas sans lui
- [ ] Parce qu'il remplace la validation des données
> Express exécute les middlewares dans l'ordre. Si express.json() arrive après la route, req.body vaut undefined au moment où la route s'exécute.
:::

## Appeler une API externe avec fetch

Depuis Node 18, la fonction `fetch` est disponible nativement, comme dans le navigateur. Tu peux donc consommer d'autres API sans paquet :

```js
async function chargerDepot() {
  const reponse = await fetch('https://api.github.com/repos/nodejs/node', {
    headers: { Accept: 'application/vnd.github+json' },
  });

  if (!reponse.ok) {
    throw new Error(`Erreur HTTP ${reponse.status}`);
  }

  const depot = await reponse.json();
  console.log(`${depot.full_name} : ${depot.stargazers_count} étoiles`);
}

chargerDepot().catch((erreur) => console.error(erreur.message));
```

Comme dans le navigateur, `fetch` ne rejette la promesse que pour un problème réseau : vérifie toujours `reponse.ok`. Le mot-clé `await` est détaillé au chapitre suivant.

## Atelier guidé : l'API DevRoad

Compte une heure trente.

1. Crée un projet `devroad-api` en ESM (`npm init -y`, `"type": "module"`), puis installe Express.
2. Écris d'abord le serveur brut `node:http` avec deux routes, teste-le avec `curl`, puis supprime-le : l'objectif est de voir ce qu'Express simplifie.
3. Crée `src/app.js` avec `GET /` (message de bienvenue) et `GET /roadmaps`, avec stockage en mémoire.
4. Ajoute `GET /roadmaps/:id` avec un 404 si l'identifiant est inconnu ou invalide.
5. Ajoute `POST /roadmaps` avec validation du titre et réponse `201`.
6. Ajoute `PUT /roadmaps/:id` pour modifier le titre et `DELETE /roadmaps/:id` avec réponse `204`.
7. Ajoute le filtre `?niveau=` sur la liste.
8. Ajoute le middleware `journal`, le middleware 404 et le gestionnaire d'erreurs.
9. Protège la suppression avec une clé d'API lue dans `process.env.API_KEY`.
10. Ajoute un script `dev` dans `package.json` (`node --watch src/app.js`) et teste chaque route avec `curl -i`.

Pour t'auto-évaluer : sans regarder, peux-tu associer chaque action (lire, créer, supprimer, route introuvable, données invalides) à sa méthode HTTP et à son code de statut ?

## Erreurs fréquentes

- **Oublier `app.use(express.json())`.** `req.body` est `undefined` et la création échoue.
- **Répondre deux fois à une requête.** L'erreur « Cannot set headers after they are sent » indique un `res.json` appelé deux fois : utilise `return` après une réponse précoce.
- **Oublier `next()` dans un middleware.** La requête reste suspendue sans réponse.
- **Placer le middleware d'erreur avant les routes.** Il doit venir après.
- **Faire confiance à `req.params.id` sans conversion.** C'est toujours une chaîne.
- **Utiliser un verbe dans l'URL.** Préfère `POST /roadmaps` à `/creerRoadmap`.
- **Renvoyer `200` pour une erreur.** Les clients se fient au code de statut.
- **Exposer les messages d'erreur internes.** Journalise côté serveur, réponds de façon générique.

## Bonnes pratiques

- Une ressource = une URL au pluriel + des méthodes HTTP appropriées.
- Retourne toujours du JSON, avec un format d'erreur cohérent (`{ "message": "..." }`).
- Utilise les bons codes de statut, et jamais `200` pour tout.
- Valide chaque entrée avant de l'utiliser.
- Lis le port et les clés depuis des variables d'environnement.
- Sépare les routes, la logique et les données dès que le fichier dépasse une centaine de lignes.
- Journalise chaque requête pour comprendre ce qui se passe en production.

## À retenir

- HTTP fonctionne par **requêtes** (méthode, URL, en-têtes, corps) et **réponses** (statut, en-têtes, corps).
- `node:http` crée un serveur sans dépendance, mais **Express** simplifie le routage et la lecture du JSON.
- Une API **REST** expose des ressources au pluriel avec `GET`, `POST`, `PUT` / `PATCH`, `DELETE`.
- `req.params`, `req.query` et `req.body` sont des entrées non fiables à valider.
- Les **middlewares** s'exécutent dans l'ordre ; les gestionnaires 404 et d'erreurs se placent à la fin.
- Les codes `2xx`, `4xx` et `5xx` indiquent succès, erreur client et erreur serveur.
- `fetch` est disponible nativement pour appeler d'autres API ; vérifie toujours `reponse.ok`.
