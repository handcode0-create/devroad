---
title: Fichiers, variables et configuration
minutes: 120
level: intermediate
---

## Ce que tu vas apprendre

Une vraie application lit et écrit des fichiers (données, journaux, exports), et s'adapte à son environnement : la même base de code tourne sur ton ordinateur, sur un serveur de test et en production, avec des réglages différents. Dans ce chapitre, tu maîtrises le module `node:fs`, la gestion des chemins, les flux pour les gros fichiers, les variables d'environnement avec `.env` et une configuration validée au démarrage.

À la fin du chapitre, tu seras capable de :

- lire, écrire, ajouter et supprimer des fichiers avec `node:fs/promises` ;
- construire des chemins fiables avec `node:path` et `import.meta.url` ;
- lire et écrire du JSON de manière sûre ;
- traiter de gros fichiers avec des **flux** (*streams*) ;
- charger un fichier `.env` avec `--env-file` ou le paquet `dotenv` ;
- centraliser et valider la configuration au démarrage ;
- ne jamais exposer de secrets dans le dépôt Git.

Prérequis : les chapitres 1 à 4, en particulier `async` / `await`. Prévois environ deux heures.

## Lire et écrire des fichiers

Le module `node:fs/promises` offre des fonctions à promesses pour manipuler le système de fichiers. Comme tout ce qui touche au disque est lent, on utilise toujours ces versions asynchrones dans un serveur.

```js
import { readFile, writeFile, appendFile, rm, mkdir, readdir, stat } from 'node:fs/promises';

// Lire un fichier texte
const texte = await readFile('notes.txt', 'utf8');

// Écrire (remplace tout le contenu, crée le fichier si besoin)
await writeFile('notes.txt', 'Première ligne\n', 'utf8');

// Ajouter à la fin
await appendFile('notes.txt', 'Deuxième ligne\n', 'utf8');

// Créer un dossier, y compris les parents
await mkdir('donnees/exports', { recursive: true });

// Lister un dossier
const fichiers = await readdir('donnees');

// Informations sur un fichier
const infos = await stat('notes.txt');
console.log(infos.size, infos.isFile(), infos.mtime);

// Supprimer (force: true ignore l'absence du fichier)
await rm('notes.txt', { force: true });
```

Le deuxième argument `'utf8'` indique l'encodage. Sans lui, `readFile` retourne un `Buffer` (des octets bruts), ce qui est utile pour une image mais pas pour du texte.

### Gérer l'absence d'un fichier

Un fichier manquant lève une erreur dont le code est `ENOENT`. Distingue ce cas des vraies pannes :

```js
async function lireOptionnel(chemin) {
  try {
    return await readFile(chemin, 'utf8');
  } catch (erreur) {
    if (erreur.code === 'ENOENT') return null; // le fichier n'existe pas encore
    throw erreur;                                // autre problème : on remonte l'erreur
  }
}
```

Plutôt que de tester l'existence du fichier avant de l'ouvrir (ce qui crée une situation de concurrence : il peut disparaître entre les deux instructions), tente l'opération et gère l'erreur.

## Les chemins : path et import.meta.url

Un chemin relatif comme `'./donnees/roadmaps.json'` est résolu à partir du **dossier courant du processus** (`process.cwd()`), c'est-à-dire de l'endroit d'où tu lances la commande, et non de l'emplacement du fichier de code. Cela provoque des erreurs surprenantes.

Pour viser un fichier relatif au code, construis le chemin depuis `import.meta.url` :

```js
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const dossierCourant = path.dirname(fileURLToPath(import.meta.url));
const fichierDonnees = path.join(dossierCourant, '..', 'donnees', 'roadmaps.json');
```

Depuis Node 20.11, `import.meta.dirname` offre un raccourci direct :

```js
const fichierDonnees = path.join(import.meta.dirname, '..', 'donnees', 'roadmaps.json');
```

Quelques fonctions de `node:path` à connaître : `path.join` (assemble), `path.resolve` (chemin absolu), `path.basename`, `path.extname` et `path.dirname` (décomposent).

:::quiz
Pourquoi construire un chemin avec import.meta.dirname plutôt qu'écrire './donnees/x.json' ?
- [ ] Parce que les chemins relatifs sont interdits dans Node.js
- [x] Parce qu'un chemin relatif dépend du dossier depuis lequel on lance la commande, pas de l'emplacement du fichier de code
- [ ] Parce que cela rend la lecture plus rapide
- [ ] Parce que import.meta.dirname chiffre le chemin
> Un chemin relatif est résolu à partir de process.cwd(). Lancer le programme depuis un autre dossier le casserait. Partir de l'emplacement du fichier de code est fiable.
:::

## Persister des données en JSON

Pour un petit projet, un fichier JSON sert de base de données rudimentaire. Voici un module de stockage réutilisable :

```js
// src/stockage.js
import { readFile, writeFile, rename, mkdir } from 'node:fs/promises';
import path from 'node:path';

export async function lireJson(chemin, parDefaut = []) {
  try {
    return JSON.parse(await readFile(chemin, 'utf8'));
  } catch (erreur) {
    if (erreur.code === 'ENOENT') return parDefaut;
    throw erreur; // JSON invalide ou autre erreur : on ne masque pas
  }
}

export async function ecrireJson(chemin, donnees) {
  await mkdir(path.dirname(chemin), { recursive: true });
  const temporaire = `${chemin}.tmp`;

  await writeFile(temporaire, JSON.stringify(donnees, null, 2), 'utf8');
  await rename(temporaire, chemin); // remplacement atomique
}
```

L'astuce du fichier temporaire est importante : si le programme plante au milieu de l'écriture, le fichier d'origine reste intact, car `rename` remplace le fichier en une seule opération. Sans cela, une coupure de courant peut laisser un JSON tronqué et illisible.

> **Attention** : si plusieurs requêtes écrivent le même fichier en même temps, tu peux perdre des données (la dernière écriture écrase les autres). Un fichier JSON convient à un outil personnel, pas à une application à plusieurs utilisateurs. Passe alors à une vraie base de données.

## Les flux pour les gros fichiers

`readFile` charge tout le fichier en mémoire. Pour un journal de plusieurs gigaoctets, c'est impossible. Les **flux** (*streams*) traitent les données par morceaux, avec une consommation mémoire constante.

```js
import { createReadStream, createWriteStream } from 'node:fs';
import { pipeline } from 'node:stream/promises';
import { createGzip } from 'node:zlib';

// Compresse un gros fichier sans le charger entièrement en mémoire
await pipeline(
  createReadStream('journal.log'),
  createGzip(),
  createWriteStream('journal.log.gz'),
);
console.log('Compression terminée');
```

`pipeline` relie les flux entre eux, propage les erreurs et libère les ressources. Pour lire un gros fichier texte ligne par ligne :

```js
import { createReadStream } from 'node:fs';
import { createInterface } from 'node:readline';

const lignes = createInterface({ input: createReadStream('acces.log'), crlfDelay: Infinity });

let erreurs = 0;
for await (const ligne of lignes) {
  if (ligne.includes(' 500 ')) erreurs++;
}
console.log(`${erreurs} erreurs serveur trouvées`);
```

Autre application classique : envoyer un fichier en réponse HTTP sans le charger en mémoire, avec `createReadStream(chemin).pipe(res)`.

## Les variables d'environnement

On ne met jamais les réglages sensibles (mot de passe de base de données, clé d'API de paiement) dans le code. On les fournit via l'**environnement** du processus, accessible avec `process.env`. Pour le développement, on les range dans un fichier **`.env`** :

```bash
# .env
PORT=3000
NODE_ENV=development
DATABASE_URL=postgresql://utilisateur:motdepasse@localhost:5432/devroad
API_KEY=cle-de-test-pas-secrete
CINETPAY_SECRET=remplace-moi
```

Depuis Node 20.6, tu peux charger ce fichier sans installer aucun paquet, avec l'option `--env-file` :

```bash
node --env-file=.env src/index.js
```

Dans `package.json` :

```json
{
  "scripts": {
    "start": "node src/index.js",
    "dev": "node --env-file=.env --watch src/index.js"
  }
}
```

Alternative : le paquet `dotenv` (`npm install dotenv`, puis `import 'dotenv/config'` en première ligne). Le principe est le même.

### Les règles d'or

1. **Ne commite jamais `.env`.** Ajoute-le à `.gitignore`.
2. **Commite un `.env.example`** avec les noms des variables et des valeurs factices, pour documenter ce qu'il faut renseigner.
3. **En production**, définis les variables dans la plateforme d'hébergement (Render, Railway, VPS), pas dans un fichier.
4. Une variable d'environnement est **toujours une chaîne**. `process.env.PORT` vaut `'3000'`, pas `3000`.

```js
console.log(typeof process.env.PORT);       // 'string'
const port = Number(process.env.PORT ?? 3000);
```

> **Erreur fréquente** : écrire `if (process.env.DEBUG)` quand la valeur est `'false'`. La chaîne `'false'` est « vraie » en JavaScript. Compare explicitement : `process.env.DEBUG === 'true'`.

:::quiz
Que vaut process.env.DEBUG si le fichier .env contient DEBUG=false, et comment tester correctement cette valeur ?
- [ ] Le booléen false ; if (process.env.DEBUG) suffit
- [ ] Le nombre 0 ; if (process.env.DEBUG == 0) suffit
- [x] La chaîne 'false' ; il faut comparer avec process.env.DEBUG === 'true'
- [ ] undefined, car les booléens ne sont pas supportés
> Toute variable d'environnement est une chaîne. La chaîne 'false' est non vide donc « vraie » en JavaScript : une comparaison explicite est nécessaire.
:::

## Centraliser et valider la configuration

Éparpiller `process.env.QUELQUE_CHOSE` dans tout le code est fragile : une faute de frappe donne `undefined` sans erreur, et on découvre le problème en production. La bonne pratique est de lire et valider **toute** la configuration en un seul endroit, au démarrage, et d'échouer immédiatement si quelque chose manque.

```js
// src/config.js
const requises = ['DATABASE_URL', 'API_KEY'];
const manquantes = requises.filter((nom) => !process.env[nom]);

if (manquantes.length > 0) {
  console.error(`Configuration incomplète. Variables manquantes : ${manquantes.join(', ')}`);
  process.exit(1);
}

const port = Number(process.env.PORT ?? 3000);
if (!Number.isInteger(port) || port < 1 || port > 65535) {
  console.error(`PORT invalide : ${process.env.PORT}`);
  process.exit(1);
}

export const config = Object.freeze({
  env: process.env.NODE_ENV ?? 'development',
  port,
  databaseUrl: process.env.DATABASE_URL,
  apiKey: process.env.API_KEY,
  estProduction: process.env.NODE_ENV === 'production',
});
```

Le reste du code importe `config` et n'accède plus jamais à `process.env`. `Object.freeze` empêche toute modification accidentelle. Tu peux aussi valider avec Zod pour des règles plus riches (format d'URL, valeurs autorisées).

```js
import { config } from './config.js';

console.log(`Démarrage en mode ${config.env} sur le port ${config.port}`);
```

Le principe s'appelle « **échouer vite** » (*fail fast*) : une application qui refuse de démarrer avec un message clair vaut mieux qu'une application qui démarre et plante à la première requête.

### Plusieurs environnements

Beaucoup de projets utilisent `NODE_ENV` (`development`, `test`, `production`) pour adapter le comportement : niveau de journalisation, affichage des erreurs détaillées, base de données utilisée. Évite néanmoins la multiplication de cas particuliers : la production doit ressembler le plus possible au développement.

## Atelier guidé : un petit magasin de fiches mémo

Compte une heure.

1. Crée un projet `fiches-cli` en ESM avec les dossiers `src/` et `donnees/`.
2. Écris `src/stockage.js` avec `lireJson` et `ecrireJson` (écriture via fichier temporaire).
3. Écris `src/config.js` avec validation : `FICHES_FICHIER` (chemin du fichier JSON, valeur par défaut `donnees/fiches.json`) et `NIVEAU_LOGS`.
4. Crée un `.env`, un `.env.example` et un `.gitignore` qui ignore `.env` et `donnees/`.
5. Écris `src/index.js` : avec `parseArgs`, supporte les commandes `ajouter --titre "..."`, `lister` et `supprimer --id 3`.
6. Chaque commande lit le JSON, modifie la liste et la réécrit. Les identifiants sont incrémentés automatiquement.
7. Ajoute la commande `exporter --format csv` qui écrit un fichier CSV avec un flux d'écriture (`createWriteStream`).
8. Teste le démarrage avec `node --env-file=.env src/index.js lister`, puis sans `.env` pour voir le message d'échec rapide.
9. Lance le programme depuis un autre dossier (`cd .. && node fiches-cli/src/index.js lister`) et vérifie que les chemins fonctionnent toujours grâce à `import.meta.dirname`.
10. Corromps volontairement `fiches.json` (retire une accolade) et observe le comportement. Décide comment présenter l'erreur à l'utilisateur.

Pour t'auto-évaluer : explique pourquoi on écrit dans un fichier temporaire avant de renommer, et pourquoi `.env` est ignoré par Git alors que `.env.example` est versionné.

## Erreurs fréquentes

- **Chemins relatifs dépendant du dossier courant.** Construis-les avec `import.meta.dirname`.
- **Utiliser les versions synchrones (`readFileSync`) dans un serveur.** Elles bloquent la boucle d'événements.
- **Oublier l'encodage `'utf8'`.** Tu récupères un `Buffer` au lieu d'une chaîne.
- **Commiter `.env`.** Des secrets publics sont à considérer comme compromis ; il faut les renouveler.
- **Croire qu'une variable d'environnement est un nombre ou un booléen.** C'est toujours une chaîne.
- **Parser du JSON sans gérer l'erreur.** `JSON.parse` lève une exception sur un contenu invalide.
- **Écrire un fichier partagé depuis plusieurs requêtes simultanées.** Tu risques d'écraser des données.
- **Charger un gros fichier avec `readFile`.** Utilise un flux.

## Bonnes pratiques

- Préfère `node:fs/promises` et `node:path` à tout assemblage manuel.
- Écris atomiquement (fichier temporaire puis `rename`) pour éviter la corruption.
- Utilise des flux pour tout fichier susceptible d'être volumineux.
- Centralise la configuration dans un module, validé au démarrage.
- Fournis `.env.example`, ignore `.env`, et change les secrets dès qu'ils fuient.
- Distingue les erreurs attendues (`ENOENT`) des erreurs réelles et ne masque jamais ces dernières.
- Limite les droits : ne lis et n'écris que dans les dossiers dont le programme a besoin.

## À retenir

- `node:fs/promises` lit, écrit, ajoute, liste et supprime des fichiers sans bloquer.
- Les chemins relatifs dépendent de `process.cwd()` ; pars de `import.meta.dirname` pour viser le code.
- L'écriture via fichier temporaire et `rename` protège contre la corruption.
- Les **flux** et `pipeline` traitent les gros fichiers avec peu de mémoire.
- `node --env-file=.env` charge la configuration ; `.env` reste hors de Git, `.env.example` documente.
- Les variables d'environnement sont des chaînes : convertis et valide-les dans un module `config.js`, au démarrage.
- « Échouer vite » avec un message clair évite des pannes tardives et obscures.
