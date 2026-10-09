---
title: Projet final Node.js
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu connais maintenant Node.js, npm, HTTP, l'asynchrone, les fichiers, la configuration, la sécurité et les tests. Ce projet final les réunit : tu vas construire de bout en bout **l'API DevRoad**, un back-end REST qui gère des roadmaps, des étapes, des utilisateurs, la progression de chacun et des fiches mémo personnelles. C'est exactement le type de livrable qu'un client attend d'un développeur back-end, et une excellente pièce de portfolio.

À la fin du projet, tu seras capable de :

- cadrer une API à partir d'un cahier des charges et en dessiner les ressources et les routes ;
- structurer un projet Node.js en couches claires (routes, services, stockage, configuration) ;
- implémenter authentification, autorisation et validation de manière sûre ;
- persister les données et gérer les erreurs de façon centralisée ;
- écrire une suite de tests automatisés avec `node:test` ;
- documenter l'API, la lancer en conteneur et la déployer ;
- vérifier ton travail avec une checklist d'acceptation.

Prérequis : les six chapitres précédents, Git et un client HTTP (`curl` ou équivalent). Durée : environ six heures, en plusieurs sessions. Fais un commit à la fin de chaque étape.

## Le cahier des charges

### Contexte

Une communauté de développeurs francophones souhaite offrir une application mobile et un site pour suivre des parcours d'apprentissage. Il leur faut une API unique, simple, documentée et sûre, qui pourra être consommée par un front Next.js et par une application Flutter.

### Ressources et règles métier

- **Utilisateur** : s'inscrit, se connecte, a un rôle (`apprenant` par défaut, ou `admin`).
- **Roadmap** : un parcours avec un `slug` unique, un titre, une description, un niveau (`debutant`, `intermediaire`, `professionnel`). Seul un `admin` peut la créer, la modifier ou la supprimer.
- **Étape** : appartient à une roadmap, possède un titre et un ordre.
- **Progression** : un apprenant marque des étapes comme terminées. Il consulte son pourcentage d'avancement par roadmap.
- **Fiche** : une note personnelle (titre, contenu). Un utilisateur ne voit, modifie et supprime que **ses** fiches.

### Routes attendues

| Méthode et URL | Accès | Rôle |
| --- | --- | --- |
| `POST /auth/inscription` | Public | Créer un compte |
| `POST /auth/connexion` | Public | Obtenir un jeton |
| `GET /roadmaps` | Public | Lister (filtre `?niveau=`, pagination) |
| `GET /roadmaps/:slug` | Public | Détail avec étapes |
| `POST /roadmaps` | Admin | Créer |
| `PUT /roadmaps/:slug` | Admin | Modifier |
| `DELETE /roadmaps/:slug` | Admin | Supprimer |
| `PUT /progression/:etapeId` | Connecté | Marquer une étape terminée |
| `DELETE /progression/:etapeId` | Connecté | Annuler |
| `GET /progression/:slug` | Connecté | Pourcentage d'avancement |
| `GET /fiches` | Connecté | Mes fiches |
| `POST /fiches` | Connecté | Créer une fiche |
| `DELETE /fiches/:id` | Connecté | Supprimer ma fiche |
| `GET /sante` | Public | Vérifier que l'API répond |

### Contraintes techniques

- Node.js 20 ou plus, modules ESM, Express.
- Zod pour la validation, `bcryptjs` pour les mots de passe, `jsonwebtoken` pour les jetons.
- Persistance : fichiers JSON écrits atomiquement (niveau de base), ou SQLite / PostgreSQL (niveau avancé, recommandé pour le portfolio).
- Configuration par variables d'environnement, validée au démarrage.
- Tests avec `node:test`, aucun framework de test externe.
- Aucun secret dans Git.

### Contraintes non fonctionnelles

- Format d'erreur uniforme : `{ "message": "...", "erreurs": { ... } }` (le champ `erreurs` est facultatif).
- Journal de chaque requête (méthode, URL, statut, durée).
- Pagination sur la liste des roadmaps : `?page=1&limite=10`, limite maximale de 50.
- Arrêt propre à la réception de `SIGTERM`.

## Étape 1 : initialisation et architecture (30 min)

```bash
mkdir devroad-api && cd devroad-api
git init
npm init -y
npm install express zod bcryptjs jsonwebtoken helmet cors express-rate-limit
```

Dans `package.json`, passe en ESM, ajoute `"engines": { "node": ">=20" }` et les scripts :

```json
{
  "type": "module",
  "scripts": {
    "start": "node src/serveur.js",
    "dev": "node --env-file=.env --watch src/serveur.js",
    "test": "node --test"
  }
}
```

Organise le code en couches. Chaque couche ne connaît que celle du dessous :

```text
devroad-api/
├── src/
│   ├── serveur.js        démarre l'écoute, gère l'arrêt propre
│   ├── app.js            crée et configure l'application Express
│   ├── config.js         lit et valide process.env
│   ├── routes/           auth.js, roadmaps.js, progression.js, fiches.js
│   ├── services/         logique métier (calculs, règles)
│   ├── stockage/         lecture / écriture des données
│   ├── middlewares/      auth.js, erreurs.js, journal.js, validation.js
│   └── validation/       schémas Zod
├── test/
├── donnees/              (ignoré par Git)
├── .env.example
└── README.md
```

Crée `.gitignore` (avec `node_modules/`, `.env`, `donnees/`) et `.env.example` avec `PORT`, `JWT_SECRET`, `DONNEES_DIR`. Rédige `src/config.js` en suivant le modèle du chapitre 5 : il échoue au démarrage si `JWT_SECRET` fait moins de 32 caractères.

## Étape 2 : stockage et données de départ (45 min)

Écris une petite couche de stockage générique, réutilisée par chaque ressource. Pour le niveau de base, appuie-toi sur `lireJson` / `ecrireJson` du chapitre 5. Pour éviter les écritures simultanées qui s'écrasent, sérialise les écritures avec une file de promesses :

```js
// src/stockage/fichier.js
import { readFile, writeFile, rename, mkdir } from 'node:fs/promises';
import path from 'node:path';

export function creerCollection(dossier, nom) {
  const chemin = path.join(dossier, `${nom}.json`);
  let file = Promise.resolve(); // chaîne d'opérations pour sérialiser les écritures

  async function lire() {
    try {
      return JSON.parse(await readFile(chemin, 'utf8'));
    } catch (erreur) {
      if (erreur.code === 'ENOENT') return [];
      throw erreur;
    }
  }

  function modifier(transformation) {
    const operation = file.then(async () => {
      const actuel = await lire();
      const suivant = await transformation(actuel);
      await mkdir(dossier, { recursive: true });
      await writeFile(`${chemin}.tmp`, JSON.stringify(suivant, null, 2));
      await rename(`${chemin}.tmp`, chemin);
      return suivant;
    });
    file = operation.catch(() => {}); // une erreur ne bloque pas la file
    return operation;
  }

  return { lire, modifier };
}
```

Chaque appel à `modifier` attend la fin du précédent : deux requêtes simultanées ne peuvent plus s'écraser. Crée ensuite les collections `utilisateurs`, `roadmaps`, `etapes`, `progressions` et `fiches`. Écris un script `scripts/amorcer.js` qui insère un compte admin (mot de passe lu depuis l'environnement) et trois roadmaps de six étapes.

:::quiz
Pourquoi sérialise-t-on les écritures dans la couche de stockage avec une file de promesses ?
- [ ] Pour accélérer les écritures en les parallélisant
- [x] Pour que deux modifications simultanées du même fichier ne s'écrasent pas mutuellement
- [ ] Pour chiffrer les données sur le disque
- [ ] Pour éviter d'utiliser async / await
> Chaque modification relit, transforme puis réécrit le fichier. Sans file d'attente, deux requêtes simultanées liraient le même état initial et la dernière écriture effacerait la première.
:::

## Étape 3 : application, journal et erreurs (30 min)

Dans `src/app.js`, assemble l'application et exporte-la sans l'écouter (indispensable aux tests) :

```js
import express from 'express';
import helmet from 'helmet';
import cors from 'cors';
import { journal } from './middlewares/journal.js';
import { introuvable, gestionnaireErreurs } from './middlewares/erreurs.js';
import { routesAuth } from './routes/auth.js';

export function creerApp({ stockage, config }) {
  const app = express();

  app.disable('x-powered-by');
  app.use(helmet());
  app.use(cors({ origin: config.originesAutorisees }));
  app.use(express.json({ limit: '100kb' }));
  app.use(journal);

  app.get('/sante', (req, res) => res.json({ statut: 'ok' }));
  app.use('/auth', routesAuth({ stockage, config }));
  // autres routeurs ici

  app.use(introuvable);
  app.use(gestionnaireErreurs);
  return app;
}
```

Une fonction `creerApp` qui reçoit ses dépendances en paramètre permet aux tests de lui fournir un stockage temporaire. Définis une classe d'erreur applicative pour centraliser la gestion :

```js
// src/middlewares/erreurs.js
export class ErreurHttp extends Error {
  constructor(statut, message, erreurs) {
    super(message);
    this.statut = statut;
    this.erreurs = erreurs;
  }
}

export function introuvable(req, res) {
  res.status(404).json({ message: 'Route introuvable' });
}

export function gestionnaireErreurs(err, req, res, next) {
  if (err instanceof ErreurHttp) {
    return res.status(err.statut).json({ message: err.message, erreurs: err.erreurs });
  }
  if (err.type === 'entity.parse.failed') {
    return res.status(400).json({ message: 'JSON invalide' });
  }
  console.error(err);
  res.status(500).json({ message: 'Erreur interne du serveur' });
}
```

Écris aussi un middleware `valider(schema)` qui passe le corps, les paramètres ou la requête dans un schéma Zod et lance une `ErreurHttp(422, ...)` en cas d'échec. Dans `src/serveur.js`, démarre l'écoute et gère l'arrêt propre :

```js
const serveur = app.listen(config.port, () => console.log(`API sur le port ${config.port}`));

process.on('SIGTERM', () => {
  console.log('Arrêt demandé, fermeture des connexions…');
  serveur.close(() => process.exit(0));
});
```

:::quiz
Dans la route de création d'une fiche, d'où doit provenir l'identifiant du propriétaire ?
- [ ] Du corps de la requête, envoyé par le client
- [ ] D'un paramètre d'URL
- [x] Du jeton JWT vérifié par le serveur
- [ ] D'un en-tête personnalisé
> Tout ce qui est fourni par le client peut être falsifié. L'identité doit venir du jeton dont le serveur a vérifié la signature.
:::

## Étape 4 : authentification et autorisation (60 min)

Implémente `routes/auth.js` :

1. `POST /auth/inscription` : valide `{ email, motDePasse, nom }`, refuse un e-mail déjà utilisé (409), hache avec bcrypt (coût 12), enregistre avec le rôle `apprenant`, répond `201` **sans** le hachage.
2. `POST /auth/connexion` : compare le mot de passe, répond avec un jeton JWT d'une heure, ou `401 Identifiants incorrects` (message identique pour e-mail inconnu et mot de passe faux). Applique une limite de débit de dix tentatives par quart d'heure.

Écris les middlewares `exigerAuth` et `exigerRole('admin')` du chapitre 6. Dans `exigerAuth`, relis l'utilisateur depuis le stockage pour refuser un jeton valide dont le compte a été supprimé.

## Étape 5 : roadmaps, progression et fiches (80 min)

Pour chaque routeur, suis le même schéma : validation, appel du service, réponse. Points d'attention :

- **Liste des roadmaps** : filtre `niveau` validé par `z.enum`, pagination avec `page` et `limite` (valeurs par défaut 1 et 10, maximum 50). Réponds `{ donnees: [...], page, limite, total }`.
- **Détail** : retourne la roadmap avec ses étapes triées par `ordre`, ou `404`.
- **CRUD admin** : le `slug` est unique (409 en cas de doublon) ; la suppression d'une roadmap supprime aussi ses étapes et les progressions liées.
- **Progression** : `PUT /progression/:etapeId` est **idempotent** (le rappeler ne crée pas de doublon). Le calcul du pourcentage vit dans un service pur, facile à tester :

```js
// src/services/progression.js
export function calculerAvancement(etapes, progressionsUtilisateur) {
  if (etapes.length === 0) return { terminees: 0, total: 0, pourcentage: 0 };

  const ids = new Set(etapes.map((e) => e.id));
  const terminees = progressionsUtilisateur.filter((p) => ids.has(p.etapeId)).length;

  return {
    terminees,
    total: etapes.length,
    pourcentage: Math.round((terminees / etapes.length) * 100),
  };
}
```

- **Fiches** : chaque requête filtre sur `utilisateurId` venant du jeton, jamais d'un paramètre fourni par le client. Supprimer la fiche d'un autre doit répondre `404`, sans révéler qu'elle existe.

## Étape 6 : tests automatisés (60 min)

Vise au moins vingt tests répartis entre unitaires et intégration.

- **Unitaires** : `calculerAvancement` (liste vide, partielle, complète, progressions d'une autre roadmap ignorées), validation des schémas, configuration.
- **Intégration** : démarre `creerApp` avec un stockage dans un dossier temporaire (`mkdtemp` de `node:fs/promises` et `os.tmpdir()`), sur le port `0`.

Cas à couvrir au minimum : inscription valide, inscription avec e-mail déjà pris (409), connexion réussie et échouée, accès sans jeton (401), accès d'un apprenant à une route admin (403), création de roadmap admin (201), doublon de slug (409), progression idempotente, fiche d'un autre utilisateur introuvable (404), pagination limitée à 50, JSON invalide (400).

```js
import { before, after, describe, it } from 'node:test';
import assert from 'node:assert/strict';
import { mkdtemp, rm } from 'node:fs/promises';
import { tmpdir } from 'node:os';
import path from 'node:path';
```

Utilise ces imports pour préparer un dossier de données éphémère dans `before` et le supprimer dans `after`. Ainsi, chaque exécution part d'un état propre. Mesure la couverture avec `node --test --experimental-test-coverage` et ajoute des tests jusqu'à dépasser 80 % sur `services/` et `routes/`.

## Étape 7 : documentation, conteneur et déploiement (45 min)

Rédige un `README.md` avec : présentation, installation, variables d'environnement, tableau des routes (méthode, URL, accès, exemple de requête et de réponse), commandes de test, choix techniques. Ajoute des exemples `curl` complets :

```bash
curl -X POST http://localhost:3000/auth/connexion \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@devroad.exemple","motDePasse":"un-mot-de-passe-solide"}'
```

Ajoute un `Dockerfile` minimal :

```dockerfile
FROM node:22-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY src ./src
ENV NODE_ENV=production
EXPOSE 3000
USER node
CMD ["node", "src/serveur.js"]
```

Construis et lance : `docker build -t devroad-api .` puis `docker run -p 3000:3000 --env-file .env devroad-api`. Déploie sur la plateforme de ton choix (Render, Railway ou un VPS), en définissant les variables d'environnement sur la plateforme et un volume ou une base persistante pour les données. Vérifie `GET /sante` sur l'URL publique.

## Checklist d'acceptation

Le projet est terminé quand tu peux cocher chaque ligne :

1. Toutes les routes du tableau existent et répondent avec les bons codes de statut.
2. Les mots de passe sont hachés ; aucune réponse ne contient de hachage.
3. Un apprenant ne peut ni modifier une roadmap, ni lire les fiches d'un autre.
4. Toutes les entrées sont validées avec Zod ; les erreurs ont un format uniforme.
5. La configuration est validée au démarrage ; l'application refuse de démarrer si `JWT_SECRET` est trop court.
6. Aucun secret n'est dans Git ; `.env.example` est fourni.
7. Deux écritures simultanées ne font perdre aucune donnée (test ou démonstration).
8. `npm test` passe avec au moins vingt tests ; la couverture de `services/` dépasse 80 %.
9. `npm audit` ne signale aucune faille élevée ou critique non traitée.
10. L'arrêt avec `SIGTERM` est propre ; `GET /sante` répond.
11. L'image Docker se construit et se lance.
12. Le README permet à une autre personne d'utiliser l'API sans t'appeler.

Auto-évaluation finale : présente ton API à voix haute comme à un client. Explique l'architecture en couches, comment tu protèges les données d'un utilisateur, ce qui se passe quand deux requêtes écrivent en même temps, et ce que tu changerais pour passer de cent à cent mille utilisateurs (base de données, cache, plusieurs instances).

## Erreurs fréquentes

- **Mettre toute la logique dans les routes.** Les gestionnaires deviennent illisibles et intestables ; déplace les règles dans `services/`.
- **Lire l'identifiant de l'utilisateur dans le corps de la requête.** Il doit toujours venir du jeton vérifié.
- **Renvoyer le hachage du mot de passe** dans une réponse d'inscription ou de profil.
- **Oublier `return` après `res.status(...).json(...)`.** L'exécution continue et provoque une double réponse.
- **Tests qui partagent un même fichier de données.** Utilise un dossier temporaire par suite.
- **Coder le secret JWT dans le dépôt.** Il doit venir de l'environnement.
- **Ignorer les cas limites** : liste vide, identifiant non numérique, JSON invalide, corps trop gros.
- **Attendre la fin pour écrire les tests.** Écris-les au fur et à mesure de chaque étape.

## Bonnes pratiques

- Découpe en couches : routes minces, services purs, stockage isolé.
- Injecte les dépendances pour rendre chaque morceau testable.
- Centralise erreurs, validation et journalisation dans des middlewares.
- Rends les opérations d'écriture idempotentes quand c'est possible.
- Commite par petites étapes avec des messages clairs.
- Écris la documentation en même temps que le code, pas après.
- Révise chaque route avec trois questions : qui peut l'appeler, avec quelles données, que se passe-t-il en cas d'échec ?
- Garde la porte ouverte : l'abstraction du stockage te permet de passer à PostgreSQL sans réécrire les routes.

## À retenir

- Un back-end solide, c'est une architecture claire, des entrées validées, des accès contrôlés et des erreurs maîtrisées.
- L'identité de l'utilisateur vient toujours du jeton vérifié, jamais d'une donnée fournie par le client.
- Les écritures concurrentes sur un fichier exigent une sérialisation ; une vraie base de données les gère nativement.
- Les tests automatisés avec `node:test` protègent chaque modification future.
- La configuration par environnement, les secrets hors de Git et l'arrêt propre sont des exigences de production.
- Ce projet documenté, testé et déployé est une réalisation concrète que tu peux présenter à un employeur ou à un client.
