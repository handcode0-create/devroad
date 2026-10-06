<?php

return [
    'lessons' => [
        'Découvrir Node.js' => [
            'description' => 'Comprends ce qu\'est Node.js, comment il exécute JavaScript hors du navigateur grâce à V8 et à sa boucle d\'événements, et ce qui le distingue du JavaScript front-end.',
            'objective' => 'Écrire et exécuter un script Node.js qui lit des informations système, utilise un module intégré et illustre l\'ordre d\'exécution de la boucle d\'événements.',
            'content' => <<<'MD'
## Pourquoi cette notion

Node.js permet d'utiliser JavaScript pour écrire des serveurs, des APIs, des scripts d'automatisation et des outils en ligne de commande. Une équipe qui maîtrise déjà JavaScript côté navigateur peut ainsi construire tout le produit avec un seul langage. C'est le cas de nombreuses startups et agences qui livrent des applications web et mobiles avec une API Node.js.

Avant d'écrire un serveur, tu dois comprendre ce qu'est réellement Node.js, ce qu'il apporte par rapport au navigateur et pourquoi son modèle d'exécution est adapté aux applications qui attendent beaucoup de réseau ou de fichiers.

## Les concepts clés

### Un runtime, pas un langage

Node.js n'est pas un nouveau langage : c'est un environnement d'exécution. Il embarque le moteur V8, celui de Chrome, qui exécute le JavaScript, et il ajoute des APIs système : accès aux fichiers, au réseau, aux processus et au système d'exploitation. Dans le navigateur, tu as « window » et « document » ; dans Node.js, ils n'existent pas, mais tu disposes de « process », de « fs » ou de « http ».

### Modules intégrés

Node.js fournit des modules prêts à l'emploi qu'on importe avec le préfixe « node: », par exemple « node:os », « node:path » ou « node:fs ». Ce préfixe indique clairement qu'il s'agit d'un module du cœur de Node.js et non d'un paquet externe. Tu les importes avec « import » dans un fichier de module ES.

### Monothread et boucle d'événements

Ton code JavaScript s'exécute sur un seul fil d'exécution. Pour ne pas bloquer, Node.js délègue les opérations lentes (lecture de fichier, requête réseau, minuteur) au système et continue d'exécuter le reste. Quand une opération se termine, son résultat est placé dans une file, et la boucle d'événements exécute la fonction de rappel correspondante dès que le fil est libre. C'est pourquoi un « setTimeout » de zéro milliseconde s'exécute après le code synchrone en cours.

### Le dossier process

L'objet global « process » donne accès à des informations utiles : « process.argv » contient les arguments de la ligne de commande, « process.env » les variables d'environnement, « process.platform » le système et « process.exit » permet de terminer le programme avec un code de sortie.

## Exemple pas à pas

L'exemple est un script nommé « decouverte.js » à exécuter avec « node decouverte.js ».

- Étape 1 : on importe le module « node:os » pour lire le système et le module « node:path » pour manipuler un chemin.
- Étape 2 : on affiche la version de Node.js, la plateforme et le nombre de cœurs du processeur.
- Étape 3 : on lit un argument passé en ligne de commande avec « process.argv » pour personnaliser le message.
- Étape 4 : on planifie un « setTimeout » et une « Promise » déjà résolue, puis on affiche du code synchrone.
- Étape 5 : on observe l'ordre d'affichage : d'abord le synchrone, ensuite la promesse, enfin le minuteur.

Relance le script avec un prénom en argument et vérifie que l'ordre d'affichage reste identique.

## Erreurs fréquentes

- Chercher « window » ou « document » dans Node.js : ils n'existent pas hors du navigateur. Utilise « globalThis » et les APIs de Node.js.
- Oublier le préfixe « node: » ou le mélanger avec un paquet externe du même nom : la lecture devient ambiguë. Utilise toujours « node:fs » pour les modules intégrés.
- Utiliser « import » dans un fichier traité comme CommonJS : Node.js lève une erreur de syntaxe. Utilise l'extension « .mjs » ou déclare « "type": "module" » dans « package.json ».
- Croire qu'un « setTimeout » de zéro s'exécute immédiatement : il passe après le code synchrone. Ne compte pas sur lui pour ordonner la logique.
- Bloquer le fil avec une boucle de calcul très longue : toutes les requêtes en attente gèlent. Découpe le travail ou délègue-le à un autre processus.
- Lancer le script depuis le mauvais dossier : les chemins relatifs échouent. Vérifie ton dossier courant ou construis des chemins absolus.

## Bonnes pratiques

- Vérifie la version de Node.js utilisée avec « node --version » et privilégie une version à support long terme.
- Importe les modules intégrés avec le préfixe « node: ».
- Utilise les modules ES (« import ») dans les nouveaux projets.
- Garde le fil principal libre : les opérations lentes doivent être asynchrones.
- Termine un programme en erreur avec un code de sortie non nul pour que les outils d'automatisation détectent l'échec.

## Auto-évaluation

- Qu'est-ce qui distingue Node.js du JavaScript exécuté dans le navigateur ?
- Quel est le rôle du moteur V8 et quel est celui des APIs système ?
- Pourquoi dit-on que Node.js est monothread tout en gérant beaucoup d'opérations en parallèle ?
- Dans quel ordre s'affichent du code synchrone, une promesse résolue et un « setTimeout » de zéro ?
- À quoi servent « process.argv » et « process.env » ?

## À retenir

- Node.js est un runtime qui exécute JavaScript avec V8 et ajoute des APIs système.
- Les modules intégrés s'importent avec le préfixe « node: ».
- La boucle d'événements exécute les rappels une fois le code synchrone terminé.
- Une opération longue et synchrone bloque tout le programme.
- L'objet « process » donne les arguments, l'environnement et le contrôle de la sortie.
MD,
            'code_example' => <<<'CODE'
// decouverte.js — exécuter avec : node decouverte.js Awa
// Étape 1 : modules intégrés (préfixe node:)
import os from 'node:os';
import path from 'node:path';

// Étape 2 : informations sur l'environnement d'exécution
console.log('Version de Node.js :', process.version);
console.log('Plateforme :', process.platform);
console.log('Cœurs du processeur :', os.cpus().length);

// Étape 3 : lecture d'un argument de la ligne de commande
// process.argv[0] = node, process.argv[1] = le script, puis les arguments
const prenom = process.argv[2] ?? 'visiteur';
console.log(`Bonjour ${prenom}, bienvenue sur Node.js !`);

// Un chemin construit proprement, quel que soit le système
const fichier = path.join(os.tmpdir(), 'devroad', 'notes.txt');
console.log('Chemin de travail :', fichier);

// Étape 4 : trois façons de planifier du travail
setTimeout(() => {
  console.log('3. Minuteur (setTimeout 0)');
}, 0);

Promise.resolve().then(() => {
  console.log('2. Promesse résolue (file des microtâches)');
});

console.log('1. Code synchrone terminé');

// Étape 5 : l'ordre d'affichage sera 1, puis 2, puis 3
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Fiche d\'identité du système',
            'exercise_description' => <<<'CODE'
Écris un script « info.js » qui affiche une fiche d'information sur la machine et prouve que tu as compris l'ordre d'exécution de la boucle d'événements. Il doit accepter un nom en argument de ligne de commande.

Critères de réussite :
- Le script utilise l'import de modules ES avec « node:os » et « node:path ».
- Il affiche la version de Node.js, la plateforme, le nombre de cœurs et la mémoire totale en gigaoctets, arrondie à une décimale.
- Il lit un nom passé en argument avec « process.argv » et utilise la valeur « inconnu » en son absence.
- Il planifie un « setTimeout » de zéro, une promesse résolue et un message synchrone, et l'ordre d'affichage final est : synchrone, promesse, minuteur.
- Le script s'exécute avec « node info.js » sans erreur, avec un fichier « package.json » contenant « "type": "module" ».
CODE,
            'exercise_hint' => 'La mémoire totale vient de « os.totalmem() » en octets : divise par 1024 trois fois. Pour vérifier l\'ordre, numérote tes messages comme dans l\'exemple.',
            'exercise_solution' => <<<'CODE'
// info.js — nécessite un package.json avec { "type": "module" }
import os from 'node:os';

// Lecture du nom passé en argument
const nom = process.argv[2] ?? 'inconnu';

// Conversion des octets en gigaoctets, arrondie à une décimale
const memoireGo = (os.totalmem() / 1024 ** 3).toFixed(1);

console.log('=== Fiche système ===');
console.log('Demandée par :', nom);
console.log('Node.js :', process.version);
console.log('Plateforme :', process.platform);
console.log('Cœurs :', os.cpus().length);
console.log('Mémoire totale :', memoireGo, 'Go');

// Démonstration de la boucle d'événements
setTimeout(() => console.log('3. Minuteur'), 0);
Promise.resolve().then(() => console.log('2. Promesse'));
console.log('1. Synchrone');
CODE,
        ],

        'NPM et modules' => [
            'description' => 'Apprends à initialiser un projet avec npm, gérer les dépendances et scripts de package.json et découper ton code en modules ES.',
            'objective' => 'Créer un projet Node.js avec package.json, installer une dépendance, définir des scripts et organiser le code en au moins deux modules ES avec exports nommés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet Node.js réel ne tient jamais dans un seul fichier. Il utilise des bibliothèques écrites par d'autres, comme un framework web, un validateur de données ou un client de paiement, et il sépare son propre code en fichiers cohérents. npm, le gestionnaire de paquets de Node.js, et le système de modules rendent cela possible.

En entreprise, la première chose qu'on te demande en rejoignant un projet est de cloner le dépôt, d'exécuter « npm install » puis « npm run dev ». Tu dois donc comprendre ce que font ces commandes et ce que contient « package.json ».

## Les concepts clés

### package.json

Ce fichier décrit ton projet : son nom, sa version, ses dépendances et ses scripts. Tu le crées avec « npm init -y ». La clé « "type": "module" » indique à Node.js que tes fichiers « .js » sont des modules ES. La section « scripts » regroupe des commandes lancées avec « npm run nom », par exemple « start » ou « dev ».

### Dépendances et verrouillage

« npm install nom-du-paquet » télécharge le paquet dans le dossier « node_modules » et l'ajoute à « dependencies ». Avec l'option « --save-dev », il va dans « devDependencies » : outils utiles seulement pendant le développement, comme un outil de test. Le fichier « package-lock.json » fige les versions exactes installées pour que toute l'équipe obtienne les mêmes. Tu le versionnes, alors que « node_modules » se régénère et n'est jamais versionné.

### Modules ES

Chaque fichier est un module avec sa propre portée. Il expose ce qu'il veut partager avec « export » et le reste est privé. Un autre fichier l'importe avec « import { nom } from './fichier.js' ». Avec les modules ES, l'extension du fichier fait partie du chemin relatif et doit être écrite. Un export par défaut existe aussi, mais les exports nommés rendent le code plus explicite et plus facile à retrouver.

### Responsabilités et structure

Un bon découpage sépare ce qui change pour des raisons différentes : les règles métier, l'accès aux fichiers ou aux données, et le point d'entrée. Un dossier « src » regroupe ton code, et chaque module a un rôle clair et un nom qui le reflète.

## Exemple pas à pas

L'exemple est un petit projet de calcul de prix avec deux modules et un point d'entrée.

- Étape 1 : « npm init -y » crée « package.json » ; on ajoute « "type": "module" » et un script « start ».
- Étape 2 : le module « prix.js » exporte deux fonctions nommées, « calculerTtc » et « formaterFcfa ».
- Étape 3 : le point d'entrée « index.js » les importe avec la syntaxe « import { ... } from ».
- Étape 4 : on installe une dépendance externe avec « npm install » et on l'importe comme un module.
- Étape 5 : « npm start » lance le programme ; on modifie la valeur de départ pour observer le résultat.

Supprime ensuite le dossier « node_modules » puis relance « npm install » : tout est restauré depuis « package.json » et « package-lock.json ».

## Erreurs fréquentes

- Oublier l'extension dans un import relatif : Node.js ne trouve pas le fichier. Écris « ./prix.js » en entier.
- Utiliser « import » sans « "type": "module" » : erreur de syntaxe. Ajoute la clé ou utilise l'extension « .mjs ».
- Versionner « node_modules » : le dépôt devient énorme. Ajoute-le à « .gitignore ».
- Installer un paquet avec « -g » alors qu'il est nécessaire au projet : il n'apparaît pas dans « package.json » et les collègues ne l'ont pas. Installe-le localement.
- Modifier à la main les versions dans « package-lock.json » : tu crées des incohérences. Laisse npm gérer ce fichier.
- Faire des importations circulaires entre deux modules : certaines valeurs sont indéfinies au chargement. Extrais le code commun dans un troisième module.

## Bonnes pratiques

- Versionne « package.json » et « package-lock.json » mais jamais « node_modules ».
- Sépare « dependencies » et « devDependencies » pour alléger la production.
- Définis des scripts npm pour les tâches répétitives : démarrer, tester, vérifier.
- Préfère les exports nommés et des modules petits à responsabilité unique.
- Vérifie la réputation et la maintenance d'un paquet avant de l'ajouter.

## Auto-évaluation

- Que contiennent « package.json » et « package-lock.json » et lequel versionner ?
- Quelle différence existe entre « dependencies » et « devDependencies » ?
- Comment déclarer qu'un projet utilise les modules ES ?
- Pourquoi l'extension est-elle obligatoire dans un import relatif ES ?
- Que fait « npm install » dans un projet fraîchement cloné ?

## À retenir

- « package.json » décrit le projet, ses dépendances et ses scripts.
- « package-lock.json » garantit des installations reproductibles.
- « node_modules » se régénère et ne se versionne pas.
- Un module expose explicitement ce qu'il partage avec « export ».
- Des modules courts et nommés selon leur rôle rendent le projet lisible.
MD,
            'code_example' => <<<'CODE'
// === package.json (extrait) ===
// {
//   "name": "calcul-prix",
//   "version": "1.0.0",
//   "type": "module",
//   "scripts": { "start": "node src/index.js" }
// }

// === src/prix.js : module métier avec exports nommés ===

// Calcule un montant TTC à partir d'un HT et d'un taux en pourcentage
export function calculerTtc(montantHt, tauxTva = 18) {
  if (!Number.isFinite(montantHt) || montantHt < 0) {
    throw new RangeError('Le montant HT doit être un nombre positif.');
  }
  return Math.round(montantHt * (1 + tauxTva / 100));
}

// Formate un nombre en FCFA avec les conventions françaises
export function formaterFcfa(montant) {
  return `${new Intl.NumberFormat('fr-FR').format(montant)} FCFA`;
}

// === src/index.js : point d'entrée ===
// L'extension .js est obligatoire dans les imports relatifs
// import { calculerTtc, formaterFcfa } from './prix.js';
//
// const panier = [
//   { produit: 'Riz 25 kg', ht: 14500 },
//   { produit: 'Huile 5 L', ht: 6500 },
// ];
//
// let total = 0;
// for (const ligne of panier) {
//   const ttc = calculerTtc(ligne.ht);
//   total += ttc;
//   console.log(`${ligne.produit} : ${formaterFcfa(ttc)}`);
// }
// console.log(`Total TTC : ${formaterFcfa(total)}`);
//
// Lancer avec : npm start
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Mini-bibliothèque de statistiques de ventes',
            'exercise_description' => <<<'CODE'
Crée un projet « stats-ventes » qui calcule des statistiques sur des ventes de boutique. Organise le code en modules ES et fournis des scripts npm.

Critères de réussite :
- Le projet est initialisé avec « npm init -y » et « package.json » contient « "type": "module" » et un script « start ».
- Un module « src/stats.js » exporte les fonctions nommées « total », « moyenne » et « maximum », qui prennent un tableau de nombres.
- « moyenne » lève une erreur claire si le tableau est vide.
- Un module « src/index.js » importe ces fonctions avec l'extension « .js » et affiche les statistiques d'une liste d'au moins cinq montants en FCFA.
- Un fichier « .gitignore » exclut « node_modules » et « npm start » s'exécute sans erreur.
CODE,
            'exercise_hint' => 'Pour le maximum, « Math.max(...montants) » suffit. Commence par exporter une seule fonction et vérifie l\'import avant d\'ajouter les autres.',
            'exercise_solution' => <<<'CODE'
// === package.json (extrait) ===
// { "name": "stats-ventes", "version": "1.0.0", "type": "module",
//   "scripts": { "start": "node src/index.js" } }
// === .gitignore ===
// node_modules

// === src/stats.js ===
export function total(montants) {
  return montants.reduce((somme, montant) => somme + montant, 0);
}

export function moyenne(montants) {
  if (montants.length === 0) {
    throw new Error('Impossible de calculer la moyenne d\'une liste vide.');
  }
  return total(montants) / montants.length;
}

export function maximum(montants) {
  return Math.max(...montants);
}

// === src/index.js ===
// import { total, moyenne, maximum } from './stats.js';
//
// const ventes = [12500, 8000, 23000, 4500, 17500];
// const fcfa = new Intl.NumberFormat('fr-FR');
//
// console.log(`Total : ${fcfa.format(total(ventes))} FCFA`);
// console.log(`Moyenne : ${fcfa.format(Math.round(moyenne(ventes)))} FCFA`);
// console.log(`Meilleure vente : ${fcfa.format(maximum(ventes))} FCFA`);
CODE,
        ],

        'HTTP et APIs' => [
            'description' => 'Crée un serveur HTTP avec le module intégré de Node.js, lis les requêtes, route par méthode et chemin et réponds en JSON avec les bons codes de statut.',
            'objective' => 'Construire un serveur HTTP qui expose plusieurs routes JSON, gère la lecture du corps d\'une requête POST et renvoie des codes 200, 201, 400, 404 et 405 appropriés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Quand une application mobile affiche la liste des produits, ou quand un service de paiement te notifie qu'une transaction est validée, tout passe par des requêtes HTTP. Node.js est très utilisé pour construire ces APIs. Avant d'utiliser un framework comme Express ou Fastify, il est essentiel de comprendre ce qu'ils font à ta place : lire la méthode et l'URL, lire le corps, choisir une route et écrire la réponse.

Comprendre le module « http » de base te donne une vraie compréhension du protocole et te permet de déboguer n'importe quelle API.

## Les concepts clés

### Requête et réponse

Un serveur créé avec « http.createServer » reçoit pour chaque requête deux objets : « req » (requête entrante) et « res » (réponse sortante). Sur « req », tu lis « req.method », « req.url » et « req.headers ». Sur « res », tu définis le code avec « res.statusCode », les en-têtes avec « res.setHeader » et tu termines avec « res.end(corps) ». Sans « res.end », le client attend indéfiniment.

### Routage

Le routage consiste à choisir le traitement selon la méthode et le chemin. Pour analyser l'URL proprement, on utilise la classe « URL » avec une base fictive, par exemple « new URL(req.url, 'http://localhost') », ce qui donne accès à « pathname » et aux paramètres de recherche. Un routeur simple est une série de conditions ou une table qui associe un couple méthode et chemin à une fonction.

### Lire le corps d'une requête

Le corps d'une requête POST arrive en morceaux. Comme « req » est un flux asynchrone, on peut le parcourir avec « for await (const morceau of req) » pour accumuler les données, puis décoder avec « JSON.parse » dans un bloc « try » car le contenu peut être invalide. Il faut aussi limiter la taille acceptée pour éviter qu'un client malveillant envoie un corps énorme.

### Codes de statut et JSON

Une API cohérente utilise des codes précis : 200 succès, 201 création, 400 requête invalide, 404 route introuvable, 405 méthode non autorisée, 500 erreur serveur. Elle renvoie toujours du JSON avec l'en-tête « Content-Type: application/json ».

## Exemple pas à pas

L'exemple expose une mini-API de produits en mémoire.

- Étape 1 : une fonction « envoyerJson » centralise le code de statut, l'en-tête et la sérialisation.
- Étape 2 : une fonction « lireCorpsJson » accumule le flux de la requête et décode le JSON.
- Étape 3 : le serveur extrait le chemin avec « new URL » puis teste méthode et chemin.
- Étape 4 : « GET /produits » renvoie la liste et « POST /produits » valide le corps puis renvoie un code 201.
- Étape 5 : une route inconnue renvoie 404 ; une méthode non prévue sur « /produits » renvoie 405 ; une exception renvoie 400 ou 500.

Teste avec « curl » en envoyant un JSON valide, un JSON cassé et une méthode « DELETE » pour déclencher chaque branche.

## Erreurs fréquentes

- Oublier d'appeler « res.end » : la requête reste en attente et le client finit par expirer. Termine toujours la réponse.
- Envoyer du JSON sans l'en-tête « Content-Type » : le client ne l'interprète pas correctement. Définis l'en-tête avant « res.end ».
- Ne pas gérer l'erreur de « JSON.parse » : le serveur plante avec une exception non attrapée. Entoure le décodage d'un « try ».
- Répondre deux fois à la même requête : Node.js lève une erreur sur les en-têtes déjà envoyés. Utilise « return » après chaque réponse.
- Comparer « req.url » directement avec un chemin : les paramètres de recherche (« ?page=2 ») cassent la comparaison. Utilise « pathname ».
- Accepter un corps de taille illimitée : un client peut saturer la mémoire. Interromps la lecture au-delà d'une limite.

## Bonnes pratiques

- Centralise l'écriture des réponses dans une fonction pour garder un format cohérent.
- Utilise les codes de statut les plus précis possibles.
- Entoure le traitement d'une requête d'un « try » et renvoie une erreur propre au client sans détails internes.
- Valide toujours le corps reçu avant de l'utiliser.
- Utilise un framework quand le routage devient complexe, mais garde en tête ce qu'il fait sous le capot.

## Auto-évaluation

- Quels sont les deux objets reçus par le gestionnaire de « http.createServer » et que contiennent-ils ?
- Pourquoi utiliser « new URL » plutôt que comparer « req.url » ?
- Comment lire le corps JSON d'une requête POST de façon sûre ?
- Quand renvoyer 400, 404 et 405 ?
- Que se passe-t-il si tu oublies « res.end » ?

## À retenir

- Le module « http » suffit pour construire une API complète.
- Chaque requête reçoit une réponse unique, terminée par « res.end ».
- Le routage repose sur la méthode et le chemin analysé.
- Le corps d'une requête est un flux qu'il faut lire et décoder prudemment.
- Les codes de statut et le format JSON doivent rester cohérents.
MD,
            'code_example' => <<<'CODE'
// serveur.js — node serveur.js puis tester avec curl sur http://localhost:3000
import http from 'node:http';

const produits = [{ id: 1, nom: 'Riz 25 kg', prix: 14500 }];
const TAILLE_MAX = 10_000; // limite du corps en octets

// Étape 1 : réponse JSON centralisée
function envoyerJson(res, statut, corps) {
  res.statusCode = statut;
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.end(JSON.stringify(corps));
}

// Étape 2 : lecture et décodage du corps de la requête
async function lireCorpsJson(req) {
  let brut = '';
  for await (const morceau of req) {
    brut += morceau;
    if (brut.length > TAILLE_MAX) throw new Error('Corps trop volumineux');
  }
  return JSON.parse(brut); // lève une erreur si le JSON est invalide
}

const serveur = http.createServer(async (req, res) => {
  try {
    // Étape 3 : extraction du chemin sans les paramètres de recherche
    const { pathname } = new URL(req.url, 'http://localhost');

    if (pathname !== '/produits') {
      return envoyerJson(res, 404, { erreur: 'Route introuvable' });
    }

    // Étape 4 : GET liste, POST création
    if (req.method === 'GET') {
      return envoyerJson(res, 200, { data: produits });
    }

    if (req.method === 'POST') {
      const donnees = await lireCorpsJson(req);
      if (typeof donnees.nom !== 'string' || !Number.isInteger(donnees.prix) || donnees.prix <= 0) {
        return envoyerJson(res, 400, { erreur: 'Nom et prix entier positif requis' });
      }
      const produit = { id: produits.length + 1, nom: donnees.nom, prix: donnees.prix };
      produits.push(produit);
      return envoyerJson(res, 201, { data: produit });
    }

    // Étape 5 : méthode non prévue
    res.setHeader('Allow', 'GET, POST');
    return envoyerJson(res, 405, { erreur: 'Méthode non autorisée' });
  } catch (erreur) {
    // JSON invalide ou corps trop gros : requête incorrecte
    return envoyerJson(res, 400, { erreur: 'Requête invalide' });
  }
});

serveur.listen(3000, () => console.log('Serveur prêt sur http://localhost:3000'));
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'API de réservations de salles',
            'exercise_description' => <<<'CODE'
Construis avec le module « http » (sans framework) une API de réservation de salles de réunion, en mémoire, dans un fichier « api.js ».

Critères de réussite :
- « GET /salles » renvoie la liste des salles (au moins trois) avec un code 200.
- « POST /reservations » reçoit un JSON avec « salle », « nom » et « heure » (entier entre 8 et 18), valide les champs et renvoie 201 avec la réservation créée.
- Un corps JSON invalide ou des champs incorrects renvoient 400 avec un message ; une salle inconnue renvoie 404.
- Une salle déjà réservée à la même heure renvoie 409 (conflit).
- Une route inconnue renvoie 404, une méthode non autorisée sur une route connue renvoie 405, et toutes les réponses sont en JSON avec le bon en-tête.
CODE,
            'exercise_hint' => 'Écris d\'abord « envoyerJson » et la lecture du corps. Pour détecter un conflit, utilise « reservations.some(...) » avec la salle et l\'heure.',
            'exercise_solution' => <<<'CODE'
import http from 'node:http';

const salles = ['Lagune', 'Baobab', 'Savane'];
const reservations = [];

function envoyerJson(res, statut, corps) {
  res.statusCode = statut;
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.end(JSON.stringify(corps));
}

async function lireCorpsJson(req) {
  let brut = '';
  for await (const morceau of req) {
    brut += morceau;
    if (brut.length > 10_000) throw new Error('Corps trop volumineux');
  }
  return JSON.parse(brut);
}

const serveur = http.createServer(async (req, res) => {
  try {
    const { pathname } = new URL(req.url, 'http://localhost');

    if (pathname === '/salles') {
      if (req.method !== 'GET') {
        res.setHeader('Allow', 'GET');
        return envoyerJson(res, 405, { erreur: 'Méthode non autorisée' });
      }
      return envoyerJson(res, 200, { data: salles });
    }

    if (pathname === '/reservations') {
      if (req.method !== 'POST') {
        res.setHeader('Allow', 'POST');
        return envoyerJson(res, 405, { erreur: 'Méthode non autorisée' });
      }

      const { salle, nom, heure } = await lireCorpsJson(req);

      if (typeof nom !== 'string' || nom.trim() === '' || !Number.isInteger(heure) || heure < 8 || heure > 18) {
        return envoyerJson(res, 400, { erreur: 'Nom et heure (8 à 18) requis' });
      }
      if (!salles.includes(salle)) {
        return envoyerJson(res, 404, { erreur: 'Salle inconnue' });
      }
      if (reservations.some((r) => r.salle === salle && r.heure === heure)) {
        return envoyerJson(res, 409, { erreur: 'Salle déjà réservée à cette heure' });
      }

      const reservation = { id: reservations.length + 1, salle, nom: nom.trim(), heure };
      reservations.push(reservation);
      return envoyerJson(res, 201, { data: reservation });
    }

    return envoyerJson(res, 404, { erreur: 'Route introuvable' });
  } catch {
    return envoyerJson(res, 400, { erreur: 'Requête invalide' });
  }
});

serveur.listen(3000, () => console.log('API prête sur http://localhost:3000'));
CODE,
        ],

        'Asynchrone' => [
            'description' => 'Maîtrise les callbacks, les Promise et async/await, comprends la boucle d\'événements et apprends à exécuter des tâches en séquence ou en parallèle sans bloquer le serveur.',
            'objective' => 'Écrire des fonctions asynchrones avec async/await, gérer les erreurs avec try/catch, exécuter des opérations en parallèle avec Promise.all et expliquer la différence avec du code bloquant.',
            'content' => <<<'MD'
## Pourquoi cette notion

Presque tout ce que fait un serveur Node.js est lent à l'échelle d'un processeur : lire un fichier, interroger une base de données, appeler l'API d'un opérateur de paiement mobile. Si le programme restait bloqué pendant chaque attente, il ne pourrait servir qu'un client à la fois. Le modèle asynchrone permet de lancer l'opération, de continuer à traiter d'autres requêtes, puis de reprendre quand le résultat arrive.

Maîtriser les promesses et « async » et « await » est indispensable : c'est le style utilisé par toutes les bibliothèques modernes et tous les frameworks Node.js.

## Les concepts clés

### Des callbacks aux promesses

Historiquement, on passait une fonction de rappel (callback) à chaque opération. Imbriquer plusieurs rappels produisait un code illisible. Une Promise représente une valeur disponible plus tard. Elle est dans l'un de trois états : en attente, résolue avec une valeur ou rejetée avec une erreur. On la consomme avec « then » et « catch ».

### async et await

Une fonction déclarée avec « async » retourne toujours une promesse. À l'intérieur, « await » suspend cette fonction jusqu'à la résolution de la promesse, sans bloquer le reste du programme. Le code se lit alors de haut en bas comme du code synchrone. Les erreurs se gèrent avec « try » et « catch », comme pour des exceptions classiques.

### Séquence ou parallèle

Deux « await » à la suite s'exécutent l'un après l'autre. Si les opérations sont indépendantes, lance-les ensemble avec « Promise.all([...]) », qui attend que toutes soient terminées et échoue dès qu'une échoue. « Promise.allSettled » attend toutes les promesses et rapporte le résultat de chacune, réussite ou échec, ce qui convient quand une panne partielle est acceptable.

### Éviter de bloquer la boucle

Le code synchrone long, comme une grosse boucle de calcul ou une lecture synchrone de fichier (« readFileSync »), empêche la boucle d'événements de traiter les autres requêtes. Sur le chemin critique d'un serveur, utilise les versions asynchrones.

## Exemple pas à pas

L'exemple simule la récupération du profil d'un client et de ses commandes depuis deux services.

- Étape 1 : une fonction « attendre » enveloppe « setTimeout » dans une promesse pour simuler une latence.
- Étape 2 : « chargerClient » et « chargerCommandes » sont des fonctions « async » qui attendent puis retournent des données ou lèvent une erreur.
- Étape 3 : une version séquentielle enchaîne deux « await » et mesure la durée totale.
- Étape 4 : une version parallèle utilise « Promise.all » et mesure une durée proche de la plus longue des deux opérations.
- Étape 5 : un « try » et « catch » intercepte l'erreur d'un service défaillant et affiche un message clair.

Compare les durées affichées : le gain du parallèle est la raison principale d'utiliser « Promise.all ».

## Erreurs fréquentes

- Oublier « await » devant un appel asynchrone : tu manipules une promesse au lieu de sa valeur. Ajoute « await » ou enchaîne avec « then ».
- Utiliser « await » dans une boucle pour des opérations indépendantes : tout s'exécute en série et tout est lent. Utilise « Promise.all » avec « map ».
- Ne pas gérer les rejets : une promesse rejetée sans « catch » provoque une erreur non gérée qui peut arrêter le processus. Entoure d'un « try » ou ajoute « catch ».
- Utiliser « forEach » avec une fonction « async » : la boucle n'attend pas les promesses. Utilise « for...of » avec « await » ou « Promise.all ».
- Bloquer avec une API synchrone dans un gestionnaire de requête : tous les clients attendent. Utilise la version asynchrone.
- Lancer plusieurs milliers de promesses en même temps sur une ressource limitée : tu satures la base ou l'API distante. Traite par lots.

## Bonnes pratiques

- Privilégie « async » et « await » pour la lisibilité.
- Lance en parallèle les opérations indépendantes, en série celles qui dépendent l'une de l'autre.
- Gère les erreurs au bon niveau : là où tu sais quoi faire, sinon laisse-les remonter.
- Utilise « Promise.allSettled » quand un échec partiel est tolérable.
- Mesure la durée avec « performance.now() » ou « console.time » avant d'optimiser.

## Auto-évaluation

- Quels sont les trois états d'une promesse ?
- Que retourne une fonction « async » et que fait « await » ?
- Quand préférer « Promise.all » à des « await » successifs ?
- Pourquoi « forEach » ne convient-il pas avec une fonction « async » ?
- Pourquoi une lecture synchrone de fichier est-elle risquée dans un serveur ?

## À retenir

- Une promesse représente un résultat futur, réussi ou en erreur.
- « async » et « await » rendent le code asynchrone lisible comme du code synchrone.
- « Promise.all » exécute des tâches indépendantes en parallèle.
- Les erreurs asynchrones se gèrent avec « try » et « catch ».
- Le code bloquant gèle tout le serveur, il faut l'éviter sur le chemin critique.
MD,
            'code_example' => <<<'CODE'
// asynchrone.js — exécuter avec : node asynchrone.js
// Étape 1 : transforme setTimeout en promesse pour simuler une latence
const attendre = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

// Étape 2 : deux "services" simulés
async function chargerClient(id) {
  await attendre(300); // simule un appel réseau
  return { id, nom: 'Awa Koné' };
}

async function chargerCommandes(id, echec = false) {
  await attendre(400);
  if (echec) throw new Error('Service des commandes indisponible');
  return [{ ref: 'CMD-001', montant: 12500 }, { ref: 'CMD-002', montant: 8000 }];
}

// Étape 3 : version séquentielle (durée = 300 + 400 ms)
async function enSequence() {
  const debut = performance.now();
  const client = await chargerClient(1);
  const commandes = await chargerCommandes(1);
  console.log('Séquence :', Math.round(performance.now() - debut), 'ms', client.nom, commandes.length);
}

// Étape 4 : version parallèle (durée proche de 400 ms)
async function enParallele() {
  const debut = performance.now();
  const [client, commandes] = await Promise.all([chargerClient(1), chargerCommandes(1)]);
  console.log('Parallèle :', Math.round(performance.now() - debut), 'ms', client.nom, commandes.length);
}

// Étape 5 : gestion d'une erreur avec try/catch
async function avecErreur() {
  try {
    await Promise.all([chargerClient(1), chargerCommandes(1, true)]);
  } catch (erreur) {
    console.log('Erreur interceptée :', erreur.message);
  }
}

await enSequence();
await enParallele();
await avecErreur();
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Agrégateur de prix de transport',
            'exercise_description' => <<<'CODE'
Écris un script « transport.js » qui interroge trois services simulés de transport (chacun renvoie un tarif après un délai différent) et affiche le meilleur tarif. Un des services peut échouer.

Critères de réussite :
- Trois fonctions « async » simulent trois compagnies avec des délais différents (200, 350 et 500 ms) via une fonction « attendre » basée sur une promesse.
- Une version utilise des « await » successifs et une autre utilise « Promise.all » ; le script affiche la durée de chacune avec « performance.now() ».
- Une troisième version utilise « Promise.allSettled », ignore les services en échec et affiche le tarif le plus bas parmi les réussites.
- Une des compagnies lève une erreur dans au moins un scénario, et le script ne plante pas.
- Le script affiche un message clair si toutes les compagnies échouent.
CODE,
            'exercise_hint' => 'Avec « Promise.allSettled », chaque résultat a une propriété « status » valant « fulfilled » ou « rejected » ; la valeur se lit dans « value ». Filtre d\'abord les réussites, puis prends le minimum.',
            'exercise_solution' => <<<'CODE'
const attendre = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

async function compagnieA() { await attendre(200); return { nom: 'Trans A', tarif: 3500 }; }
async function compagnieB() { await attendre(350); return { nom: 'Trans B', tarif: 3000 }; }
async function compagnieC(echec = false) {
  await attendre(500);
  if (echec) throw new Error('Compagnie C indisponible');
  return { nom: 'Trans C', tarif: 2800 };
}

async function chronometrer(titre, fonction) {
  const debut = performance.now();
  const resultat = await fonction();
  console.log(`${titre} : ${Math.round(performance.now() - debut)} ms`);
  return resultat;
}

// Versions séquentielle et parallèle (toutes les compagnies répondent)
await chronometrer('Séquence', async () => [await compagnieA(), await compagnieB(), await compagnieC()]);
await chronometrer('Parallèle', () => Promise.all([compagnieA(), compagnieB(), compagnieC()]));

// Version tolérante aux pannes : la compagnie C échoue
async function meilleurTarif(echecC) {
  const resultats = await Promise.allSettled([compagnieA(), compagnieB(), compagnieC(echecC)]);

  const reussites = resultats
    .filter((r) => r.status === 'fulfilled')
    .map((r) => r.value);

  if (reussites.length === 0) {
    console.log('Aucune compagnie disponible.');
    return null;
  }

  const meilleur = reussites.reduce((min, offre) => (offre.tarif < min.tarif ? offre : min));
  console.log(`Meilleur tarif : ${meilleur.nom} à ${meilleur.tarif} FCFA`);
  return meilleur;
}

await meilleurTarif(true);
await meilleurTarif(false);
CODE,
        ],

        'Fichiers, variables et configuration' => [
            'description' => 'Lis et écris des fichiers avec fs/promises, gère les chemins de façon portable et configure ton service avec des variables d\'environnement et une validation au démarrage.',
            'objective' => 'Construire un petit service qui charge sa configuration depuis process.env avec des valeurs par défaut validées, lit un fichier JSON et écrit un fichier de résultat de manière asynchrone.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application qui fonctionne sur ton ordinateur doit aussi fonctionner sur le serveur de test et sur celui de production, avec d'autres ports, d'autres bases de données et d'autres clés d'API. Coder ces valeurs en dur dans le programme oblige à modifier le code à chaque déploiement et expose des secrets dans le dépôt. La bonne pratique est de lire la configuration depuis l'environnement.

De même, beaucoup de services lisent ou écrivent des fichiers : import de produits depuis un fichier, journaux, exports de rapports. Tu dois savoir le faire sans bloquer le serveur et sans dépendre du dossier depuis lequel on lance le programme.

## Les concepts clés

### Variables d'environnement

L'objet « process.env » contient les variables définies par le système. Toutes les valeurs sont des chaînes de caractères, ou « undefined » si la variable est absente. Il faut donc convertir explicitement un port en nombre. L'opérateur « ?? » fournit une valeur par défaut seulement si la variable est absente. Les secrets (clés d'API, mots de passe de base de données) ne se codent jamais dans le programme ni dans le dépôt.

### Fichier .env et chargement

En développement, on garde les variables dans un fichier « .env » ignoré par Git. Node.js sait charger un tel fichier avec l'option « --env-file=.env » au lancement, ce qui évite une dépendance supplémentaire. On fournit un fichier « .env.example » avec les noms des variables sans leurs valeurs secrètes pour documenter la configuration.

### Le module fs/promises

« node:fs/promises » offre des fonctions asynchrones qui retournent des promesses : « readFile », « writeFile », « mkdir », « readdir », « access ». On précise l'encodage « utf8 » pour obtenir du texte plutôt qu'un tampon binaire. Chaque appel peut échouer, par exemple si le fichier n'existe pas, et l'erreur porte un « code » comme « ENOENT » qu'on peut tester.

### Chemins portables

Ne construis pas de chemins par concaténation de chaînes. Utilise « path.join » et « path.resolve » pour obtenir un chemin correct sur n'importe quel système. En module ES, l'emplacement du fichier courant se retrouve avec « import.meta.dirname » ou en passant par « fileURLToPath(import.meta.url) ». Ainsi le programme fonctionne quel que soit le dossier de lancement.

## Exemple pas à pas

L'exemple charge une configuration, lit un catalogue JSON puis écrit un rapport.

- Étape 1 : la fonction « chargerConfig » lit « PORT » et « NOM_BOUTIQUE » dans « process.env » avec des valeurs par défaut.
- Étape 2 : elle convertit le port en nombre et lève une erreur claire si la valeur est invalide.
- Étape 3 : « lireCatalogue » lit un fichier JSON avec « readFile » et le décode, en gérant l'erreur « ENOENT ».
- Étape 4 : le programme calcule la valeur totale du stock.
- Étape 5 : « ecrireRapport » crée le dossier de sortie avec « mkdir » récursif puis écrit le rapport avec « writeFile ».

Lance le script avec et sans variables définies pour voir les valeurs par défaut et le message d'erreur sur un port invalide.

## Erreurs fréquentes

- Traiter « process.env.PORT » comme un nombre : c'est une chaîne et une addition produit une concaténation. Convertis avec « Number » et valide.
- Utiliser « || » pour une valeur par défaut numérique : un zéro valide est écarté. Utilise « ?? ».
- Utiliser « readFileSync » dans un gestionnaire de requête : le serveur est bloqué pendant la lecture. Utilise « fs/promises ».
- Utiliser des chemins relatifs dépendant du dossier courant : le programme casse selon l'endroit où on le lance. Construis les chemins à partir de l'emplacement du fichier avec « path.join ».
- Versionner le fichier « .env » : les secrets fuient dans l'historique. Ajoute-le à « .gitignore » et fournis « .env.example ».
- Ignorer les erreurs de fichier : un fichier absent ou un JSON invalide plante le programme sans explication. Intercepte et affiche un message exploitable.

## Bonnes pratiques

- Centralise la lecture de la configuration dans un seul module qui valide tout au démarrage.
- Fais échouer le programme tôt avec un message clair si une variable obligatoire manque.
- Utilise « fs/promises » et jamais d'API synchrone sur le chemin des requêtes.
- Ne journalise jamais les valeurs secrètes.
- Documente chaque variable dans un « .env.example ».

## Auto-évaluation

- Quel est le type de valeur d'une variable de « process.env » et comment en tirer un nombre ?
- Quelle différence existe entre « ?? » et « || » pour une valeur par défaut ?
- Pourquoi ne pas versionner « .env » mais versionner « .env.example » ?
- Comment obtenir un chemin indépendant du dossier de lancement ?
- Que contient l'erreur quand un fichier n'existe pas ?

## À retenir

- La configuration vient de l'environnement, pas du code.
- Les variables d'environnement sont des chaînes qu'il faut convertir et valider.
- « fs/promises » lit et écrit sans bloquer la boucle d'événements.
- « path.join » et l'emplacement du module garantissent des chemins portables.
- Un service valide sa configuration au démarrage et échoue vite avec un message clair.
MD,
            'code_example' => <<<'CODE'
// rapport.js — exécuter avec : node --env-file=.env rapport.js
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import path from 'node:path';

// Étapes 1 et 2 : configuration lue depuis l'environnement et validée
function chargerConfig() {
  const port = Number(process.env.PORT ?? 3000);
  if (!Number.isInteger(port) || port < 1 || port > 65535) {
    throw new Error(`PORT invalide : "${process.env.PORT}"`);
  }
  return {
    port,
    nomBoutique: process.env.NOM_BOUTIQUE ?? 'Ma Boutique',
  };
}

// Chemin construit depuis l'emplacement de ce fichier (indépendant du dossier de lancement)
const dossier = import.meta.dirname;
const fichierCatalogue = path.join(dossier, 'data', 'catalogue.json');
const dossierSortie = path.join(dossier, 'sortie');

// Étape 3 : lecture et décodage du JSON avec gestion de l'erreur
async function lireCatalogue() {
  try {
    const texte = await readFile(fichierCatalogue, 'utf8');
    return JSON.parse(texte);
  } catch (erreur) {
    if (erreur.code === 'ENOENT') {
      throw new Error(`Catalogue introuvable : ${fichierCatalogue}`);
    }
    throw erreur;
  }
}

// Étape 5 : crée le dossier si besoin puis écrit le rapport
async function ecrireRapport(contenu) {
  await mkdir(dossierSortie, { recursive: true });
  const cible = path.join(dossierSortie, 'rapport.txt');
  await writeFile(cible, contenu, 'utf8');
  return cible;
}

try {
  const config = chargerConfig();
  const catalogue = await lireCatalogue(); // ex. [{ "nom": "Riz", "prix": 14500, "stock": 10 }]

  // Étape 4 : valeur totale du stock
  const valeur = catalogue.reduce((somme, p) => somme + p.prix * p.stock, 0);
  const fichier = await ecrireRapport(`${config.nomBoutique} : stock = ${valeur} FCFA\n`);
  console.log(`Rapport écrit dans ${fichier} (port ${config.port})`);
} catch (erreur) {
  console.error('Échec :', erreur.message);
  process.exitCode = 1;
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Importeur de contacts configurable',
            'exercise_description' => <<<'CODE'
Écris un script « importer.js » qui lit un fichier JSON de contacts, filtre ceux qui ont un numéro valide et écrit un fichier de résultat. Le chemin du fichier et le préfixe téléphonique pays viennent de la configuration.

Critères de réussite :
- Un module de configuration lit « FICHIER_CONTACTS » (valeur par défaut « data/contacts.json ») et « INDICATIF » (valeur par défaut « 225 ») depuis « process.env ».
- Il lève une erreur claire si « INDICATIF » n'est pas composé uniquement de chiffres.
- Le script lit le fichier avec « fs/promises », gère l'erreur d'un fichier absent et celle d'un JSON invalide avec des messages distincts.
- Il garde les contacts dont le numéro contient au moins 8 chiffres et écrit le résultat dans « sortie/contacts-valides.json » après avoir créé le dossier.
- Les chemins sont construits avec « path.join » à partir de « import.meta.dirname », et un fichier « .env.example » documente les variables.
CODE,
            'exercise_hint' => 'Pour ne garder que les chiffres d\'un numéro, utilise « numero.replace(/\D/g, \'\') ». Distingue les deux erreurs : « ENOENT » pour le fichier absent, « SyntaxError » (instance de) pour un JSON invalide.',
            'exercise_solution' => <<<'CODE'
// importer.js — node --env-file=.env importer.js
import { readFile, writeFile, mkdir } from 'node:fs/promises';
import path from 'node:path';

// .env.example :
// FICHIER_CONTACTS=data/contacts.json
// INDICATIF=225

function chargerConfig() {
  const indicatif = process.env.INDICATIF ?? '225';
  if (!/^\d+$/.test(indicatif)) {
    throw new Error(`INDICATIF invalide : "${indicatif}" (chiffres uniquement)`);
  }
  return {
    fichier: path.join(import.meta.dirname, process.env.FICHIER_CONTACTS ?? 'data/contacts.json'),
    indicatif,
  };
}

async function lireContacts(fichier) {
  try {
    return JSON.parse(await readFile(fichier, 'utf8'));
  } catch (erreur) {
    if (erreur.code === 'ENOENT') throw new Error(`Fichier introuvable : ${fichier}`);
    if (erreur instanceof SyntaxError) throw new Error('Le fichier ne contient pas un JSON valide.');
    throw erreur;
  }
}

try {
  const { fichier, indicatif } = chargerConfig();
  const contacts = await lireContacts(fichier);

  const valides = contacts
    .filter((c) => String(c.telephone ?? '').replace(/\D/g, '').length >= 8)
    .map((c) => ({ ...c, telephone: `+${indicatif}${String(c.telephone).replace(/\D/g, '')}` }));

  const dossierSortie = path.join(import.meta.dirname, 'sortie');
  await mkdir(dossierSortie, { recursive: true });
  await writeFile(path.join(dossierSortie, 'contacts-valides.json'), JSON.stringify(valides, null, 2), 'utf8');

  console.log(`${valides.length} contact(s) valide(s) sur ${contacts.length}.`);
} catch (erreur) {
  console.error('Échec :', erreur.message);
  process.exitCode = 1;
}
CODE,
        ],

        'Sécurité et tests' => [
            'description' => 'Valide les entrées, centralise la gestion des erreurs, journalise proprement et teste tes fonctions et ton API avec le lanceur de tests intégré de Node.js.',
            'objective' => 'Écrire une fonction de validation, un gestionnaire d\'erreurs centralisé qui masque les détails internes et au moins trois tests avec node:test et node:assert qui couvrent cas normaux et cas limites.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une API exposée sur Internet reçoit des requêtes de toutes sortes : des clients légitimes, des erreurs de programmation, mais aussi des tentatives d'abus. Une donnée mal validée peut corrompre ta base, faire planter le serveur ou exposer des informations. Une erreur non gérée peut afficher des détails internes qui aident un attaquant. Et sans tests, chaque modification est un risque de casser ce qui fonctionnait.

Une API fiable valide ce qu'elle reçoit, traite les erreurs au même endroit, enregistre ce qui se passe dans des journaux exploitables et prouve son comportement par des tests automatisés.

## Les concepts clés

### Validation des entrées

Ne fais jamais confiance aux données reçues. Vérifie chaque champ : présence, type, format, longueur et plage de valeurs. Rejette tout ce qui ne respecte pas le contrat avec un code 400 ou 422 et un message utile. La validation se place dans une fonction dédiée, facile à tester, plutôt qu'éparpillée dans les routes. Applique une liste blanche : n'accepte que les champs attendus et ignore ou refuse le reste.

### Gestion centralisée des erreurs

Définis des erreurs métier avec une classe qui porte un code de statut, par exemple « ErreurHttp ». Un seul gestionnaire attrape toutes les exceptions : il renvoie le statut et le message pour les erreurs prévues, et un message générique avec le code 500 pour tout le reste, en journalisant le détail côté serveur. Le client ne doit jamais voir une trace de pile ni un chemin de fichier.

### Journalisation

Un journal utile indique ce qui s'est passé, quand et à quel endroit : méthode, chemin, code de statut, durée, message d'erreur. Écris sur la sortie standard dans un format régulier. Ne journalise jamais de mots de passe, de jetons ou de données personnelles sensibles.

### Sécurité de base

Limite la taille des corps acceptés, valide et assainis les entrées, ne construis jamais une commande système ou une requête SQL par concaténation, garde les secrets dans l'environnement, et maintiens tes dépendances à jour avec « npm audit ». Retourne les mêmes messages pour un mot de passe faux et un utilisateur inconnu afin de ne pas révéler quels comptes existent.

### Tests avec node:test

Node.js intègre un lanceur de tests : on importe « test » depuis « node:test » et les assertions depuis « node:assert/strict ». Chaque test est une fonction qui prépare, exécute et vérifie. On lance l'ensemble avec « node --test ». On teste le cas normal, les cas limites et les cas d'erreur avec « assert.throws » ou « assert.rejects ».

## Exemple pas à pas

L'exemple valide une commande, centralise les erreurs et teste le tout.

- Étape 1 : une classe « ErreurHttp » porte un statut HTTP et un message destiné au client.
- Étape 2 : « validerCommande » vérifie le nom, la quantité et le moyen de paiement par liste blanche, et lève une « ErreurHttp » 422 en cas de problème.
- Étape 3 : « gererErreur » renvoie le statut prévu pour une « ErreurHttp » ou un message générique avec le code 500 pour toute autre erreur, en journalisant le détail.
- Étape 4 : le test du cas normal vérifie que la commande valide est retournée nettoyée.
- Étape 5 : deux tests vérifient les cas d'erreur : une quantité négative et un moyen de paiement non autorisé.

Lance « node --test » puis casse volontairement la validation pour constater qu'un test échoue.

## Erreurs fréquentes

- Faire confiance aux données du client : une valeur inattendue provoque un plantage ou une corruption. Valide chaque champ avec des règles explicites.
- Renvoyer « error.message » ou la trace de pile au client : tu révèles des détails internes. Renvoie un message générique pour les erreurs inconnues et journalise le détail.
- Disperser les « try » et « catch » dans chaque route : les réponses d'erreur divergent. Centralise dans un gestionnaire.
- Journaliser des mots de passe ou des jetons : les journaux deviennent une source de fuite. Masque ou omet ces valeurs.
- Ne tester que le cas heureux : les bugs se cachent dans les cas limites. Ajoute des tests pour les valeurs vides, négatives et inattendues.
- Écrire des tests qui dépendent les uns des autres ou de l'ordre d'exécution : ils deviennent fragiles. Chaque test prépare ses propres données.

## Bonnes pratiques

- Valide à l'entrée avec une liste blanche de champs et de valeurs.
- Distingue les erreurs prévues, qui ont un statut, des erreurs inattendues, qui sont générées en 500.
- Journalise de façon structurée et sans donnée sensible.
- Écris un test pour chaque bug corrigé afin qu'il ne revienne pas.
- Exécute « npm audit » régulièrement et mets à jour les dépendances vulnérables.

## Auto-évaluation

- Pourquoi une validation par liste blanche est-elle plus sûre qu'une liste de valeurs interdites ?
- Que doit voir le client en cas d'erreur inattendue et que doit-on garder côté serveur ?
- Quel est l'intérêt d'un gestionnaire d'erreurs unique ?
- Comment tester qu'une fonction lève une erreur avec « node:assert » ?
- Quelles informations ne faut-il jamais écrire dans les journaux ?

## À retenir

- Toute entrée externe est suspecte tant qu'elle n'est pas validée.
- Un gestionnaire d'erreurs centralisé garantit des réponses cohérentes et sans fuite d'information.
- Les journaux décrivent ce qui se passe sans exposer de secrets.
- « node --test » suffit pour des tests solides sans dépendance supplémentaire.
- Les cas limites et les cas d'erreur méritent autant de tests que le cas normal.
MD,
            'code_example' => <<<'CODE'
// commande.js — module à tester avec : node --test
// Étape 1 : erreur métier qui porte son statut HTTP
export class ErreurHttp extends Error {
  constructor(statut, message) {
    super(message);
    this.statut = statut;
  }
}

const PAIEMENTS_AUTORISES = ['wave', 'orange_money', 'mtn_momo', 'especes'];

// Étape 2 : validation par liste blanche, retourne un objet nettoyé
export function validerCommande(entree) {
  const nom = typeof entree?.nom === 'string' ? entree.nom.trim() : '';
  if (nom === '' || nom.length > 80) {
    throw new ErreurHttp(422, 'Le nom est obligatoire (80 caractères maximum).');
  }
  if (!Number.isInteger(entree.quantite) || entree.quantite < 1 || entree.quantite > 100) {
    throw new ErreurHttp(422, 'La quantité doit être un entier entre 1 et 100.');
  }
  if (!PAIEMENTS_AUTORISES.includes(entree.paiement)) {
    throw new ErreurHttp(422, 'Moyen de paiement non autorisé.');
  }
  // On ne retourne que les champs attendus
  return { nom, quantite: entree.quantite, paiement: entree.paiement };
}

// Étape 3 : gestionnaire centralisé : message précis si prévu, générique sinon
export function gererErreur(erreur) {
  if (erreur instanceof ErreurHttp) {
    return { statut: erreur.statut, corps: { erreur: erreur.message } };
  }
  console.error('[ERREUR]', erreur); // détail gardé côté serveur
  return { statut: 500, corps: { erreur: 'Erreur interne du serveur.' } };
}

// === commande.test.js ===
// import test from 'node:test';
// import assert from 'node:assert/strict';
// import { validerCommande, gererErreur, ErreurHttp } from './commande.js';
//
// // Étape 4 : cas normal
// test('accepte une commande valide et nettoie le nom', () => {
//   const r = validerCommande({ nom: '  Awa ', quantite: 2, paiement: 'wave', admin: true });
//   assert.deepEqual(r, { nom: 'Awa', quantite: 2, paiement: 'wave' });
// });
//
// // Étape 5 : cas d'erreur
// test('refuse une quantité négative', () => {
//   assert.throws(() => validerCommande({ nom: 'Awa', quantite: -1, paiement: 'wave' }), ErreurHttp);
// });
//
// test('masque le détail des erreurs inattendues', () => {
//   const r = gererErreur(new Error('mot de passe base : secret'));
//   assert.equal(r.statut, 500);
//   assert.equal(r.corps.erreur, 'Erreur interne du serveur.');
// });
CODE,
            'estimated_minutes' => 75,
            'exercise_title' => 'Valider et tester une inscription',
            'exercise_description' => <<<'CODE'
Écris un module « inscription.js » qui valide une demande d'inscription et un fichier de tests « inscription.test.js » avec le lanceur intégré de Node.js.

Critères de réussite :
- Une classe « ErreurHttp » porte un statut, et « validerInscription » lève une « ErreurHttp » 422 en cas de donnée invalide.
- La validation exige un e-mail au format correct, un mot de passe d'au moins 8 caractères contenant un chiffre, et un téléphone de 8 à 15 chiffres ; les champs inconnus sont ignorés.
- La fonction retourne un objet nettoyé contenant uniquement « email » (en minuscules), « motDePasse » et « telephone » (chiffres seulement).
- Une fonction « gererErreur » renvoie le statut prévu pour une « ErreurHttp » et un message générique avec le code 500 pour toute autre erreur.
- Au moins quatre tests passent avec « node --test » : cas normal, e-mail invalide, mot de passe faible et erreur inattendue masquée.
CODE,
            'exercise_hint' => 'Pour l\'e-mail, une expression régulière simple suffit : « /^[^\\s@]+@[^\\s@]+\\.[^\\s@]+$/ ». Pour tester une erreur levée, utilise « assert.throws(() => ..., ErreurHttp) » après avoir importé la classe.',
            'exercise_solution' => <<<'CODE'
// === inscription.js ===
export class ErreurHttp extends Error {
  constructor(statut, message) {
    super(message);
    this.statut = statut;
  }
}

const REGEX_EMAIL = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

export function validerInscription(entree) {
  const email = typeof entree?.email === 'string' ? entree.email.trim().toLowerCase() : '';
  if (!REGEX_EMAIL.test(email)) {
    throw new ErreurHttp(422, 'Adresse e-mail invalide.');
  }

  const motDePasse = typeof entree.motDePasse === 'string' ? entree.motDePasse : '';
  if (motDePasse.length < 8 || !/\d/.test(motDePasse)) {
    throw new ErreurHttp(422, 'Le mot de passe doit faire 8 caractères et contenir un chiffre.');
  }

  const telephone = String(entree.telephone ?? '').replace(/\D/g, '');
  if (telephone.length < 8 || telephone.length > 15) {
    throw new ErreurHttp(422, 'Le téléphone doit contenir entre 8 et 15 chiffres.');
  }

  return { email, motDePasse, telephone };
}

export function gererErreur(erreur) {
  if (erreur instanceof ErreurHttp) {
    return { statut: erreur.statut, corps: { erreur: erreur.message } };
  }
  console.error('[ERREUR]', erreur);
  return { statut: 500, corps: { erreur: 'Erreur interne du serveur.' } };
}

// === inscription.test.js ===
// import test from 'node:test';
// import assert from 'node:assert/strict';
// import { validerInscription, gererErreur, ErreurHttp } from './inscription.js';
//
// test('accepte une inscription valide et nettoie les champs', () => {
//   const r = validerInscription({ email: ' AWA@Mail.com ', motDePasse: 'abcdefg1', telephone: '+225 01 60 70 62', role: 'admin' });
//   assert.deepEqual(r, { email: 'awa@mail.com', motDePasse: 'abcdefg1', telephone: '2250160 7062'.replace(/\D/g, '') });
// });
//
// test('refuse un e-mail invalide', () => {
//   assert.throws(() => validerInscription({ email: 'pas-un-mail', motDePasse: 'abcdefg1', telephone: '0160706212' }), ErreurHttp);
// });
//
// test('refuse un mot de passe faible', () => {
//   assert.throws(() => validerInscription({ email: 'a@b.ci', motDePasse: 'court', telephone: '0160706212' }), ErreurHttp);
// });
//
// test('masque une erreur inattendue', () => {
//   const r = gererErreur(new Error('détail interne'));
//   assert.equal(r.statut, 500);
//   assert.equal(r.corps.erreur, 'Erreur interne du serveur.');
// });
CODE,
        ],

        'Projet final Node.js' => [
            'description' => 'Réalise une API REST complète de gestion de tâches avec routage, validation, pagination, authentification et tests HTTP, en assemblant tout ce que tu as appris.',
            'objective' => 'Livrer une API Node.js structurée en routes, services et dépôt, avec CRUD, pagination, validation, authentification par jeton et tests HTTP automatisés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans une mission réelle, on ne te demande pas de comprendre un module isolé, mais de livrer un service complet : une API qu'une application mobile ou un site peut utiliser en confiance. Elle doit répondre de façon cohérente, refuser les mauvaises données, protéger les accès, rester rapide quand les données grossissent et se tester facilement.

Ce projet final rassemble tes acquis : modules ES, serveur HTTP, asynchrone, configuration, validation, gestion d'erreurs et tests. Il constitue aussi une pièce de portfolio concrète, par exemple pour un outil de suivi de tâches d'équipe.

## Les concepts clés

### Architecture en couches

Sépare les responsabilités. La couche routes lit la requête et écrit la réponse. La couche services contient les règles métier. La couche dépôt (repository) gère le stockage : au début un tableau en mémoire ou un fichier JSON, ensuite une vraie base de données. Cette séparation permet de remplacer le stockage sans toucher aux routes, et de tester les règles sans serveur.

### Conception REST

Une ressource « tâches » expose des routes prévisibles : « GET /taches » liste, « GET /taches/:id » lit, « POST /taches » crée, « PATCH /taches/:id » modifie, « DELETE /taches/:id » supprime. Les codes sont cohérents : 200, 201, 204, 400, 401, 404, 422. Les réponses suivent un format constant.

### Pagination

Renvoyer toute une table à chaque requête devient vite lent et coûteux, surtout pour des utilisateurs avec une connexion mobile limitée. La pagination par « page » et « limite » en paramètres d'URL renvoie un sous-ensemble et des métadonnées : total, page courante, nombre de pages. On valide ces paramètres et on plafonne la limite.

### Authentification par jeton

À la connexion, le serveur vérifie l'identité puis émet un jeton aléatoire ou signé. Le client l'envoie dans l'en-tête « Authorization: Bearer ... ». Un middleware ou une fonction de contrôle l'examine avant chaque route protégée et renvoie 401 en cas d'absence ou d'invalidité. Les mots de passe sont hachés avec un algorithme adapté comme scrypt, disponible dans « node:crypto », jamais stockés en clair.

### Tests HTTP

Un test d'intégration démarre le serveur sur un port libre, envoie de vraies requêtes avec « fetch » et vérifie le code et le corps de la réponse. Avec « node:test », on utilise « before » et « after » pour démarrer et arrêter le serveur.

## Exemple pas à pas

L'exemple montre le cœur d'une API : service de tâches, pagination et route protégée.

- Étape 1 : un dépôt en mémoire stocke les tâches par utilisateur avec un identifiant incrémental.
- Étape 2 : le service valide le titre et le statut, puis délègue au dépôt ; il lève une erreur 422 ou 404 selon le cas.
- Étape 3 : la fonction de liste reçoit page et limite, les borne, découpe le tableau et retourne les métadonnées.
- Étape 4 : une fonction d'authentification lit l'en-tête Bearer et retrouve l'utilisateur dans une table de jetons, sinon elle lève une erreur 401.
- Étape 5 : le serveur route la requête, applique l'authentification, appelle le service et passe toute exception au gestionnaire d'erreurs central.

Teste ensuite avec « curl » en envoyant des requêtes avec et sans jeton, avec des pages différentes et des titres invalides.

## Erreurs fréquentes

- Mettre toute la logique dans les routes : le code devient impossible à tester et à faire évoluer. Place les règles dans des services.
- Oublier de filtrer par utilisateur : un client peut lire ou supprimer les tâches des autres. Applique toujours la condition de propriété.
- Accepter une limite de pagination illimitée : un client peut demander des millions de lignes. Plafonne la limite.
- Stocker les mots de passe en clair ou avec un hachage rapide : une fuite expose tous les comptes. Utilise « scrypt » avec un sel aléatoire.
- Tester seulement les fonctions isolées : les erreurs d'assemblage passent inaperçues. Ajoute des tests HTTP de bout en bout.
- Livrer sans documentation : personne ne sait lancer ni utiliser l'API. Rédige un « README » avec les variables d'environnement et des exemples de requêtes.

## Bonnes pratiques

- Construis une tranche verticale complète (route, service, dépôt, test) avant d'en ajouter d'autres.
- Garde les routes minces, les services riches en règles et le dépôt remplaçable.
- Renvoie toujours le même format de réponse et les codes de statut les plus précis.
- Protège chaque route sensible et vérifie la propriété des ressources.
- Fais des commits fréquents avec des messages clairs et garde les secrets hors du dépôt.

## Auto-évaluation

- Quel est le rôle de chaque couche : routes, services, dépôt ?
- Quels codes de statut renvoient les opérations CRUD et les cas d'erreur ?
- Pourquoi plafonner la limite de pagination et que renvoyer comme métadonnées ?
- Comment fonctionne l'authentification par jeton Bearer ?
- Quel est l'intérêt d'un test HTTP de bout en bout par rapport à un test unitaire ?

## À retenir

- Un projet complet assemble routage, règles métier, stockage, validation, sécurité et tests.
- L'architecture en couches rend le code testable et le stockage remplaçable.
- La pagination protège les performances et la bande passante.
- Chaque route sensible vérifie l'identité puis la propriété de la ressource.
- Un dépôt avec README, tests et configuration documentée est un vrai livrable professionnel.
MD,
            'code_example' => <<<'CODE'
// taches.js — cœur de l'API : dépôt, service, pagination, authentification
export class ErreurHttp extends Error {
  constructor(statut, message) {
    super(message);
    this.statut = statut;
  }
}

// Étape 1 : dépôt en mémoire (remplaçable plus tard par une base de données)
const taches = [];
let prochainId = 1;
const jetons = new Map(); // jeton -> identifiant utilisateur

const STATUTS = ['a_faire', 'en_cours', 'terminee'];

// Étape 2 : service avec validation et règles de propriété
export function creerTache(utilisateurId, entree) {
  const titre = typeof entree?.titre === 'string' ? entree.titre.trim() : '';
  const statut = entree?.statut ?? 'a_faire';
  if (titre === '' || titre.length > 120) throw new ErreurHttp(422, 'Titre obligatoire (120 caractères max).');
  if (!STATUTS.includes(statut)) throw new ErreurHttp(422, 'Statut invalide.');

  const tache = { id: prochainId++, utilisateurId, titre, statut };
  taches.push(tache);
  return tache;
}

export function supprimerTache(utilisateurId, id) {
  // Filtre sur le propriétaire : on ne supprime que ses propres tâches
  const index = taches.findIndex((t) => t.id === id && t.utilisateurId === utilisateurId);
  if (index === -1) throw new ErreurHttp(404, 'Tâche introuvable.');
  taches.splice(index, 1);
}

// Étape 3 : liste paginée avec limite plafonnée et métadonnées
export function listerTaches(utilisateurId, { page = 1, limite = 10 } = {}) {
  const p = Math.max(1, Number.parseInt(page, 10) || 1);
  const l = Math.min(50, Math.max(1, Number.parseInt(limite, 10) || 10));
  const siennes = taches.filter((t) => t.utilisateurId === utilisateurId);

  return {
    data: siennes.slice((p - 1) * l, p * l),
    meta: { page: p, limite: l, total: siennes.length, pages: Math.max(1, Math.ceil(siennes.length / l)) },
  };
}

// Étape 4 : authentification par jeton Bearer
export function authentifier(enteteAuthorization) {
  const jeton = enteteAuthorization?.startsWith('Bearer ') ? enteteAuthorization.slice(7) : '';
  const utilisateurId = jetons.get(jeton);
  if (utilisateurId === undefined) throw new ErreurHttp(401, 'Authentification requise.');
  return utilisateurId;
}

// Étape 5 : le serveur appelle authentifier() puis ces services,
// et envoie toute ErreurHttp au gestionnaire d'erreurs central.
export function enregistrerJeton(jeton, utilisateurId) {
  jetons.set(jeton, utilisateurId);
}
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Projet final : API REST de gestion de tâches',
            'exercise_description' => <<<'CODE'
Construis une API REST de gestion de tâches avec le module « http » ou un framework de ton choix. Livre un dépôt propre qui se lance avec « npm start » et dont les tests passent avec « npm test ».

Livrables :
- Un projet structuré en « src/routes », « src/services » et « src/repositories » avec « package.json » (« "type": "module" » et scripts « start » et « test »).
- Les routes CRUD sur « /taches » avec les codes 200, 201, 204, 404 et 422 et un format de réponse constant.
- Une pagination par « page » et « limite » (limite plafonnée) qui renvoie des métadonnées : total, page, nombre de pages.
- Une authentification : inscription, connexion avec un mot de passe haché via « scrypt » de « node:crypto », jeton Bearer, code 401 sans jeton, et chaque utilisateur ne voit que ses tâches.
- Une validation centralisée, un gestionnaire d'erreurs qui masque les détails internes, une configuration par variables d'environnement avec « .env.example », au moins quatre tests HTTP avec « node:test » et un « README » avec des exemples « curl ».
CODE,
            'exercise_hint' => 'Avance par tranches verticales : fais marcher la création de bout en bout avec son test avant de passer à la pagination, puis ajoute l\'authentification en dernier en protégeant les routes avant le routage.',
            'exercise_solution' => <<<'CODE'
// server.js — solution condensée (le dépôt réel sépare routes, services et dépôt)
import http from 'node:http';
import crypto from 'node:crypto';
import { creerTache, supprimerTache, listerTaches, authentifier, enregistrerJeton, ErreurHttp } from './taches.js';

const utilisateurs = new Map(); // email -> { id, sel, hash }

function envoyer(res, statut, corps) {
  res.statusCode = statut;
  if (statut === 204) return res.end();
  res.setHeader('Content-Type', 'application/json; charset=utf-8');
  res.end(JSON.stringify(corps));
}

async function lireCorps(req) {
  let brut = '';
  for await (const morceau of req) {
    brut += morceau;
    if (brut.length > 10_000) throw new ErreurHttp(413, 'Corps trop volumineux.');
  }
  try {
    return brut ? JSON.parse(brut) : {};
  } catch {
    throw new ErreurHttp(400, 'JSON invalide.');
  }
}

function hasher(motDePasse, sel) {
  return crypto.scryptSync(motDePasse, sel, 64).toString('hex');
}

export const serveur = http.createServer(async (req, res) => {
  try {
    const url = new URL(req.url, 'http://localhost');
    const { pathname } = url;

    if (req.method === 'POST' && pathname === '/inscription') {
      const { email, motDePasse } = await lireCorps(req);
      if (typeof email !== 'string' || typeof motDePasse !== 'string' || motDePasse.length < 8) {
        throw new ErreurHttp(422, 'E-mail et mot de passe (8 caractères) requis.');
      }
      const sel = crypto.randomBytes(16).toString('hex');
      utilisateurs.set(email, { id: utilisateurs.size + 1, sel, hash: hasher(motDePasse, sel) });
      return envoyer(res, 201, { data: { email } });
    }

    if (req.method === 'POST' && pathname === '/connexion') {
      const { email, motDePasse } = await lireCorps(req);
      const u = utilisateurs.get(email);
      // Même message pour un utilisateur inconnu et un mot de passe faux
      if (!u || typeof motDePasse !== 'string' ||
          !crypto.timingSafeEqual(Buffer.from(hasher(motDePasse, u.sel)), Buffer.from(u.hash))) {
        throw new ErreurHttp(401, 'Identifiants invalides.');
      }
      const jeton = crypto.randomBytes(32).toString('hex');
      enregistrerJeton(jeton, u.id);
      return envoyer(res, 200, { data: { jeton } });
    }

    // Routes protégées
    const utilisateurId = authentifier(req.headers.authorization);

    if (pathname === '/taches' && req.method === 'GET') {
      return envoyer(res, 200, listerTaches(utilisateurId, {
        page: url.searchParams.get('page'),
        limite: url.searchParams.get('limite'),
      }));
    }
    if (pathname === '/taches' && req.method === 'POST') {
      return envoyer(res, 201, { data: creerTache(utilisateurId, await lireCorps(req)) });
    }

    const correspondance = pathname.match(/^\/taches\/(\d+)$/);
    if (correspondance && req.method === 'DELETE') {
      supprimerTache(utilisateurId, Number(correspondance[1]));
      return envoyer(res, 204);
    }

    throw new ErreurHttp(404, 'Route introuvable.');
  } catch (erreur) {
    // Gestionnaire central : détail masqué pour les erreurs inattendues
    if (erreur instanceof ErreurHttp) return envoyer(res, erreur.statut, { erreur: erreur.message });
    console.error('[ERREUR]', erreur);
    return envoyer(res, 500, { erreur: 'Erreur interne du serveur.' });
  }
});

if (process.argv[1] === import.meta.filename) {
  const port = Number(process.env.PORT ?? 3000);
  serveur.listen(port, () => console.log(`API prête sur http://localhost:${port}`));
}

// === test HTTP (tests/api.test.js) ===
// import test, { before, after } from 'node:test';
// import assert from 'node:assert/strict';
// import { serveur } from '../src/server.js';
//
// let base;
// before(() => new Promise((ok) => serveur.listen(0, () => { base = `http://localhost:${serveur.address().port}`; ok(); })));
// after(() => serveur.close());
//
// test('refuse /taches sans jeton', async () => {
//   const r = await fetch(`${base}/taches`);
//   assert.equal(r.status, 401);
// });
CODE,
        ],
    ],
];
