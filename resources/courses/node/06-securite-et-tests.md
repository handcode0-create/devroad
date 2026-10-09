---
title: Sécurité et tests
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une API qui fonctionne n'est pas une API fiable. Dès qu'elle est sur Internet, des inconnus vont la tester, volontairement ou non. Ce chapitre réunit les deux disciplines qui rendent un back-end digne de confiance : la **sécurité** (se protéger des attaques courantes) et les **tests** (prouver que le code fait ce qu'on attend, et continuer à le prouver après chaque modification).

À la fin du chapitre, tu seras capable de :

- valider et assainir toutes les entrées avec Zod ;
- hacher les mots de passe et protéger des routes avec un jeton JWT ;
- configurer des en-têtes de sécurité, le CORS et une limite de débit ;
- éviter les failles courantes : injection, fuite d'informations, secrets exposés ;
- écrire des tests unitaires et d'intégration avec le **lanceur de tests intégré** `node:test` ;
- tester une API HTTP sans serveur externe ;
- utiliser des doublures (*mocks*) et mesurer la couverture.

Prérequis : les chapitres 1 à 5, en particulier Express et `async` / `await`. Prévois environ deux heures trente.

## Le principe de base : ne jamais faire confiance à l'entrée

Toute donnée qui vient de l'extérieur est **hostile jusqu'à preuve du contraire** : corps de requête, paramètres d'URL, en-têtes, cookies, fichiers téléversés, et même réponses d'une API tierce. La première ligne de défense est de décrire précisément ce que tu acceptes, et de rejeter le reste.

### Valider avec Zod

```bash
npm install zod
```

```js
// src/validation.js
import { z } from 'zod';

export const schemaInscription = z.object({
  email: z.string().trim().toLowerCase().email('E-mail invalide'),
  motDePasse: z.string().min(8, 'Au moins 8 caractères').max(128),
  nom: z.string().trim().min(2).max(60),
});

export const schemaIdentifiant = z.coerce.number().int().positive();
```

```js
app.post('/inscription', async (req, res) => {
  const resultat = schemaInscription.safeParse(req.body);

  if (!resultat.success) {
    return res.status(422).json({ erreurs: resultat.error.flatten().fieldErrors });
  }

  const { email, motDePasse, nom } = resultat.data; // données propres et typées
  // ...
});
```

`safeParse` retourne les données nettoyées (e-mail en minuscules, espaces supprimés) ou la liste des erreurs. Les champs inconnus sont ignorés par défaut, ce qui t'évite de stocker n'importe quoi.

### Se protéger de l'injection SQL

L'**injection SQL** consiste à glisser du SQL dans une entrée pour faire exécuter autre chose que prévu. Elle apparaît quand on construit une requête en concaténant du texte :

```js
// DANGEREUX : si email vaut  ' OR '1'='1  la condition est toujours vraie
const sql = `SELECT * FROM utilisateurs WHERE email = '${email}'`;

// SÛR : requête préparée, la valeur est transmise séparément
const resultat = await db.query('SELECT * FROM utilisateurs WHERE email = $1', [email]);
```

La règle est absolue : **jamais de concaténation** de données utilisateur dans une requête. Utilise des paramètres liés, ou un ORM (Prisma, Sequelize) qui les emploie pour toi.

:::quiz
Quelle est la protection fondamentale contre l'injection SQL ?
- [ ] Mettre le site en HTTPS
- [ ] Cacher les messages d'erreur
- [x] Utiliser des requêtes préparées avec des paramètres liés, sans concaténer les entrées
- [ ] Limiter le nombre de requêtes par minute
> Les paramètres liés séparent le code SQL des données : la valeur saisie ne peut jamais être interprétée comme une commande.
:::

## Mots de passe et authentification

### Hacher les mots de passe

On ne stocke **jamais** un mot de passe en clair, ni chiffré de façon réversible, ni haché avec un algorithme rapide comme SHA-256 seul. On utilise un algorithme conçu pour être lent et salé, comme **bcrypt** ou **argon2**.

```bash
npm install bcryptjs
```

```js
import bcrypt from 'bcryptjs';

const hash = await bcrypt.hash(motDePasse, 12);     // à l'inscription
const valide = await bcrypt.compare(motDePasse, hash); // à la connexion
```

Le second argument (12) est le coût de calcul : plus il est élevé, plus l'attaque par force brute devient longue. Ajuste-le pour que le hachage prenne environ 100 à 300 ms sur ton serveur.

### Les jetons JWT

Après la connexion, le serveur délivre un **jeton signé** (JWT, *JSON Web Token*) que le client renvoie à chaque requête. Le serveur vérifie la signature, sans consulter de base de données.

```bash
npm install jsonwebtoken
```

```js
import jwt from 'jsonwebtoken';
import { config } from './config.js';

export function creerJeton(utilisateur) {
  return jwt.sign({ sub: utilisateur.id, role: utilisateur.role }, config.jwtSecret, {
    expiresIn: '1h',
  });
}

export function exigerAuth(req, res, next) {
  const entete = req.get('authorization') ?? '';
  const [type, jeton] = entete.split(' ');

  if (type !== 'Bearer' || !jeton) {
    return res.status(401).json({ message: 'Authentification requise' });
  }

  try {
    req.utilisateur = jwt.verify(jeton, config.jwtSecret);
    next();
  } catch {
    res.status(401).json({ message: 'Jeton invalide ou expiré' });
  }
}
```

Quelques règles :

- le secret JWT est long, aléatoire et stocké dans une variable d'environnement ;
- la durée de vie est courte ; ne place **aucune donnée sensible** dans le jeton (il est signé, pas chiffré, donc lisible par tous) ;
- distingue **authentification** (qui es-tu ?, erreur 401) et **autorisation** (as-tu le droit ?, erreur 403).

```js
export function exigerRole(role) {
  return (req, res, next) => {
    if (req.utilisateur?.role !== role) {
      return res.status(403).json({ message: 'Accès interdit' });
    }
    next();
  };
}

app.delete('/roadmaps/:id', exigerAuth, exigerRole('admin'), supprimerRoadmap);
```

### Message d'erreur de connexion

À la connexion, renvoie le **même message** si l'e-mail n'existe pas ou si le mot de passe est faux (« Identifiants incorrects »). Distinguer les deux permet à un attaquant de lister les comptes existants.

## Durcir l'API : en-têtes, CORS, limite de débit

Quelques paquets réduisent fortement la surface d'attaque :

```bash
npm install helmet cors express-rate-limit
```

```js
import express from 'express';
import helmet from 'helmet';
import cors from 'cors';
import rateLimit from 'express-rate-limit';

const app = express();

app.disable('x-powered-by');          // ne dit pas qu'on utilise Express
app.use(helmet());                    // en-têtes de sécurité HTTP
app.use(cors({ origin: ['https://devroad.exemple.com'] })); // origines autorisées
app.use(express.json({ limit: '100kb' }));                   // taille maximale du corps

const limiteurConnexion = rateLimit({
  windowMs: 15 * 60 * 1000, // 15 minutes
  limit: 10,                // 10 tentatives maximum
  standardHeaders: true,
  legacyHeaders: false,
  message: { message: 'Trop de tentatives, réessaie plus tard' },
});

app.post('/connexion', limiteurConnexion, connexion);
```

- **Helmet** ajoute des en-têtes qui limitent le détournement du navigateur ;
- **CORS** détermine quels sites web peuvent appeler ton API depuis un navigateur : n'autorise jamais `*` pour une API authentifiée ;
- la **limite de débit** freine la force brute sur la connexion et les abus ;
- la **limite de taille** du corps évite qu'on te noie sous des requêtes énormes.

> **Attention** : la sécurité ne s'ajoute pas à la fin. Un secret commité dans Git, même supprimé ensuite, reste dans l'historique : change-le immédiatement. Garde tes dépendances à jour avec `npm audit`, et n'affiche jamais de piles d'erreur à l'utilisateur en production.

## Pourquoi tester ?

Un test est un petit programme qui vérifie le comportement d'un autre. Les bénéfices sont concrets : tu détectes les régressions (« j'ai corrigé une chose et cassé une autre »), tu oses refactorer, et tu documentes ce que le code est censé faire. On distingue :

| Type | Ce qu'il vérifie | Vitesse |
| --- | --- | --- |
| Unitaire | Une fonction isolée | Très rapide |
| Intégration | Plusieurs morceaux ensemble (route + validation) | Rapide |
| De bout en bout | L'application complète, comme un utilisateur | Lent |

## Le lanceur de tests intégré : node:test

Node.js embarque son propre framework de test (stable depuis Node 20), sans aucune dépendance. Le module de validation `node:assert` fournit les vérifications.

Prenons une fonction à tester :

```js
// src/progression.js
export function calculerProgression(terminees, total) {
  if (!Number.isInteger(terminees) || !Number.isInteger(total) || total <= 0) {
    throw new RangeError('Valeurs invalides');
  }
  if (terminees < 0 || terminees > total) {
    throw new RangeError('Nombre d’étapes terminées hors limites');
  }
  return Math.round((terminees / total) * 100);
}
```

Et son fichier de test :

```js
// test/progression.test.js
import { describe, it } from 'node:test';
import assert from 'node:assert/strict';
import { calculerProgression } from '../src/progression.js';

describe('calculerProgression', () => {
  it('retourne 0 quand rien n’est terminé', () => {
    assert.equal(calculerProgression(0, 10), 0);
  });

  it('arrondit le pourcentage', () => {
    assert.equal(calculerProgression(1, 3), 33);
  });

  it('retourne 100 quand tout est terminé', () => {
    assert.equal(calculerProgression(5, 5), 100);
  });

  it('refuse un total nul', () => {
    assert.throws(() => calculerProgression(0, 0), RangeError);
  });

  it('refuse plus d’étapes terminées que le total', () => {
    assert.throws(() => calculerProgression(6, 5), { name: 'RangeError' });
  });
});
```

Lance les tests :

```bash
node --test
```

Node cherche automatiquement les fichiers `*.test.js` (ou ceux du dossier `test/`) et affiche un rapport. Ajoute un script npm : `"test": "node --test"`. Pour relancer à chaque modification : `node --test --watch`.

Les assertions les plus utiles de `node:assert/strict` :

| Assertion | Vérifie |
| --- | --- |
| `assert.equal(a, b)` | Égalité stricte de valeurs simples |
| `assert.deepEqual(a, b)` | Égalité de contenu pour objets et tableaux |
| `assert.ok(valeur)` | La valeur est vraie |
| `assert.throws(fn, erreur)` | La fonction lève bien l'erreur attendue |
| `assert.rejects(promesse, erreur)` | La promesse est rompue avec l'erreur attendue |

### Tester de l'asynchrone

Une fonction de test peut être `async`; le test attend sa fin.

```js
it('rejette quand la roadmap est inconnue', async () => {
  await assert.rejects(() => chargerRoadmap(999), { message: /introuvable/ });
});
```

## Tester une API HTTP

Pour tester les routes Express, sépare la **création de l'application** du **démarrage du serveur** : un fichier exporte `app`, un autre appelle `listen`.

```js
// src/app.js
import express from 'express';

export const app = express();
app.use(express.json());

app.get('/roadmaps', (req, res) => res.json([{ id: 1, titre: 'Node.js' }]));
app.post('/roadmaps', (req, res) => {
  const titre = req.body?.titre;
  if (typeof titre !== 'string' || titre.length < 3) {
    return res.status(422).json({ message: 'Titre invalide' });
  }
  res.status(201).json({ id: 2, titre });
});
```

```js
// src/serveur.js
import { app } from './app.js';
app.listen(3000);
```

Le test démarre l'application sur un port libre (le port `0` laisse le système choisir) et appelle les routes avec `fetch` :

```js
// test/api.test.js
import { describe, it, before, after } from 'node:test';
import assert from 'node:assert/strict';
import { app } from '../src/app.js';

describe('API roadmaps', () => {
  let serveur;
  let base;

  before(async () => {
    await new Promise((resolve) => {
      serveur = app.listen(0, resolve);
    });
    base = `http://localhost:${serveur.address().port}`;
  });

  after(() => serveur.close());

  it('GET /roadmaps retourne la liste', async () => {
    const reponse = await fetch(`${base}/roadmaps`);
    assert.equal(reponse.status, 200);
    const corps = await reponse.json();
    assert.ok(Array.isArray(corps));
  });

  it('POST /roadmaps crée avec un titre valide', async () => {
    const reponse = await fetch(`${base}/roadmaps`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ titre: 'Docker' }),
    });
    assert.equal(reponse.status, 201);
    assert.equal((await reponse.json()).titre, 'Docker');
  });

  it('POST /roadmaps refuse un titre trop court', async () => {
    const reponse = await fetch(`${base}/roadmaps`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ titre: 'ab' }),
    });
    assert.equal(reponse.status, 422);
  });
});
```

Les fonctions `before` et `after` préparent et nettoient l'environnement, comme `beforeEach` et `afterEach` le font autour de chaque test.

:::quiz
Pourquoi sépare-t-on la création de l'application Express (app.js) du démarrage du serveur (listen) ?
- [ ] Parce qu'Express interdit d'appeler listen dans le même fichier
- [x] Pour pouvoir importer l'application dans les tests et la démarrer sur un port libre, sans lancer le vrai serveur
- [ ] Pour accélérer le démarrage en production
- [ ] Pour chiffrer les requêtes
> Exporter app permet aux tests de la démarrer eux-mêmes sur un port aléatoire (listen(0)) et de la fermer ensuite, sans conflit avec un serveur déjà lancé.
:::

## Doublures (mocks) et couverture

Pour tester une fonction qui appelle un service externe (envoi d'e-mail, paiement), on remplace ce service par une **doublure** afin de ne pas envoyer de vrais e-mails pendant les tests. `node:test` fournit `mock` :

```js
import { it, mock } from 'node:test';
import assert from 'node:assert/strict';

it('appelle le service de paiement une seule fois', async () => {
  const payer = mock.fn(async () => ({ statut: 'ok' }));

  await traiterCommande({ montant: 5000 }, { payer });

  assert.equal(payer.mock.callCount(), 1);
  assert.deepEqual(payer.mock.calls[0].arguments[0], { montant: 5000 });
});
```

Cette technique suppose que la fonction reçoive ses dépendances en paramètre (**injection de dépendances**), ce qui la rend aussi plus facile à tester. Pour mesurer quelles lignes sont exécutées par tes tests :

```bash
node --test --experimental-test-coverage
```

Une couverture élevée ne garantit pas la qualité, mais une zone à 0 % te montre ce qui n'est jamais vérifié.

## Atelier guidé : sécuriser et tester l'API DevRoad

Compte une heure trente en repartant de l'API du chapitre « HTTP et APIs ».

1. Sépare `src/app.js` (exporte `app`) et `src/serveur.js` (appelle `listen`).
2. Installe Zod et crée les schémas de création et de modification d'une roadmap. Utilise-les dans les routes `POST` et `PUT`, avec réponse `422` détaillée.
3. Ajoute l'inscription et la connexion : hachage `bcryptjs`, jeton JWT d'une heure, secret lu depuis la configuration validée (chapitre 5).
4. Écris les middlewares `exigerAuth` et `exigerRole('admin')` et protège `POST`, `PUT` et `DELETE`.
5. Ajoute Helmet, CORS limité à une origine, `x-powered-by` désactivé, limite de taille du corps et limite de débit sur `/connexion`.
6. Écris `test/progression.test.js` pour une fonction pure de ton choix, avec au moins cinq cas, dont deux cas d'erreur.
7. Écris `test/api.test.js` : liste, détail, création valide, création invalide (422), route protégée sans jeton (401), avec jeton non-admin (403).
8. Ajoute le script `"test": "node --test"` et lance-le. Corrige jusqu'à ce que tout soit vert.
9. Lance `node --test --experimental-test-coverage` et ajoute un test pour la zone la moins couverte.
10. Lance `npm audit` et note le résultat.

Pour t'auto-évaluer : explique la différence entre 401 et 403, et pourquoi le message d'échec de connexion est volontairement identique pour un e-mail inconnu et un mauvais mot de passe.

## Erreurs fréquentes

- **Se fier à la validation côté navigateur.** Elle se contourne en une ligne ; revalide toujours côté serveur.
- **Concaténer des entrées dans du SQL.** C'est l'injection assurée.
- **Stocker un mot de passe en clair ou avec un hachage rapide.** Utilise bcrypt ou argon2.
- **Mettre des données sensibles dans un JWT.** Il est lisible par quiconque le possède.
- **Autoriser tous les domaines avec CORS sur une API authentifiée.** Liste les origines permises.
- **Renvoyer l'erreur interne au client.** Journalise côté serveur, réponds de façon générique.
- **Tests dépendant les uns des autres ou de l'ordre d'exécution.** Chaque test doit pouvoir passer seul.
- **Tester l'implémentation plutôt que le comportement.** Les tests cassent à chaque refactoring inutilement.
- **Laisser un serveur ouvert après les tests.** Le processus ne se termine pas : ferme-le dans `after`.

## Bonnes pratiques

- Valide en entrée, assainis en sortie, et applique le principe du moindre privilège.
- Garde les secrets hors du code et du dépôt ; fais-les tourner en cas de fuite.
- Combine plusieurs couches : validation, authentification, autorisation, limitation de débit.
- Écris un test pour chaque bug corrigé : il ne reviendra pas.
- Nomme les tests comme des phrases qui décrivent le comportement attendu.
- Fais tourner `npm test` et `npm audit` automatiquement avant chaque déploiement.
- Teste les cas limites et d'erreur, pas seulement le cas heureux.

## À retenir

- Toute entrée est hostile : valide-la avec **Zod**, et utilise des requêtes préparées contre l'injection SQL.
- Hache les mots de passe avec **bcrypt** ; protège les routes avec des **JWT** de courte durée ; distingue 401 et 403.
- **Helmet**, **CORS** restreint, limite de débit et limite de taille du corps réduisent la surface d'attaque.
- `node:test` et `node:assert/strict` permettent d'écrire des tests sans dépendance, lancés avec `node --test`.
- Sépare `app` et `listen` pour tester une API Express sur un port libre avec `fetch`.
- Les doublures (`mock.fn`) isolent les services externes ; la couverture révèle le code non testé.
- La sécurité et les tests sont des habitudes continues, pas une étape finale.
