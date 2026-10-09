---
title: Dockerfile
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Jusqu'ici, tu as utilisé des images toutes faites : Nginx, MySQL. Pour faire tourner **ton** application, il faut fabriquer **ta** propre image. Cette recette s'écrit dans un fichier texte nommé `Dockerfile`. C'est l'un des fichiers les plus importants d'un projet moderne : il documente précisément comment construire l'environnement de ton application.

À la fin du chapitre, tu seras capable de :

- écrire un `Dockerfile` avec les instructions `FROM`, `WORKDIR`, `COPY`, `RUN`, `ENV`, `EXPOSE`, `CMD` ;
- construire une image avec `docker build` et la lancer ;
- expliquer le système de **couches** et le **cache** de build ;
- utiliser un fichier `.dockerignore` ;
- distinguer `CMD` et `ENTRYPOINT`, `COPY` et `ADD` ;
- conteneuriser une petite application Node et une application Laravel.

Prérequis : le chapitre « Comprendre les conteneurs » (images, conteneurs, `docker run`). Prévois deux heures trente. Tu auras besoin de Docker installé et d'un éditeur de code.

## Qu'est-ce qu'un Dockerfile ?

Un `Dockerfile` est une liste d'instructions que Docker exécute **de haut en bas** pour produire une image. Chaque instruction crée une **couche**. Voici le plus petit exemple utile, pour une application Node :

```dockerfile
FROM node:20-alpine
WORKDIR /app
COPY . .
RUN npm install
CMD ["node", "server.js"]
```

Lis-le comme une recette :

1. partir de l'image `node:20-alpine` ;
2. se placer dans le dossier `/app` (il est créé s'il n'existe pas) ;
3. copier tous les fichiers du projet dans `/app` ;
4. installer les dépendances ;
5. définir la commande lancée au démarrage du conteneur.

Pour construire l'image, place-toi dans le dossier du `Dockerfile` :

```bash
docker build -t mon-app:1.0 .
```

L'option `-t` donne un nom et un tag à l'image. Le point final est le **contexte de build** : le dossier dont Docker peut copier les fichiers. Ensuite, lance-la :

```bash
docker run --rm -p 3000:3000 mon-app:1.0
```

L'option `--rm` supprime automatiquement le conteneur quand il s'arrête, ce qui évite d'encombrer ta machine pendant les essais.

## Les instructions essentielles

### FROM : l'image de départ

Chaque Dockerfile commence par `FROM`. Choisis une image officielle et une **version précise** :

```dockerfile
FROM php:8.3-fpm-alpine
```

Plus la base est petite, plus l'image finale est légère et sûre. Nous y reviendrons au chapitre sur l'optimisation.

### WORKDIR : le dossier de travail

`WORKDIR /app` définit le répertoire courant pour toutes les instructions suivantes (`COPY`, `RUN`, `CMD`). Préfère-le à `RUN cd /app`, qui ne persiste pas d'une instruction à l'autre.

### COPY : apporter tes fichiers

```dockerfile
COPY package.json package-lock.json ./
COPY src ./src
```

La syntaxe est `COPY source destination`. La source est relative au contexte de build, la destination relative au `WORKDIR`. Il existe aussi `ADD`, qui sait en plus décompresser des archives et télécharger des URL, mais ce comportement implicite surprend : utilise `COPY` par défaut, et `ADD` seulement pour une archive à extraire.

### RUN : exécuter une commande pendant la construction

`RUN` exécute une commande **au moment du build** et fige le résultat dans une couche :

```dockerfile
RUN npm ci
RUN apk add --no-cache git
```

Ne confonds pas avec `CMD`, qui s'exécute **au démarrage** du conteneur.

### ENV et ARG : configurer

```dockerfile
ENV NODE_ENV=production
ARG APP_VERSION=1.0
```

`ENV` définit une variable disponible dans l'image **et** dans le conteneur en cours d'exécution. `ARG` n'existe que pendant le build : on la fournit avec `docker build --build-arg APP_VERSION=2.0 .`.

> **Attention** : n'utilise jamais `ENV` ou `ARG` pour stocker un mot de passe ou une clé d'API. Les valeurs restent visibles dans l'historique de l'image (`docker history`). Les secrets se fournissent à l'exécution, pas à la construction.

### EXPOSE : documenter le port

```dockerfile
EXPOSE 3000
```

`EXPOSE` est une **documentation** : elle indique quel port l'application écoute. Elle ne publie rien. C'est toujours `-p` (ou Compose) qui ouvre réellement le port vers l'extérieur.

### CMD et ENTRYPOINT : que faire au démarrage ?

```dockerfile
CMD ["node", "server.js"]
```

`CMD` définit la commande par défaut. On peut la remplacer en ajoutant une commande à `docker run` : `docker run mon-app:1.0 node --version`. `ENTRYPOINT`, lui, définit l'exécutable fixe, et `CMD` ne fournit plus que ses arguments par défaut :

```dockerfile
ENTRYPOINT ["php", "artisan"]
CMD ["serve", "--host=0.0.0.0"]
```

Avec ce couple, `docker run mon-image` lance `php artisan serve --host=0.0.0.0`, et `docker run mon-image migrate` lance `php artisan migrate`. Écris toujours ces instructions sous la **forme tableau** (`["node", "server.js"]`) : le processus reçoit alors correctement les signaux d'arrêt.

:::quiz
Quelle instruction s'exécute au moment de la construction de l'image, et non au démarrage du conteneur ?
- [ ] CMD
- [ ] ENTRYPOINT
- [x] RUN
- [ ] EXPOSE
> RUN exécute une commande pendant le build et enregistre le résultat dans une couche. CMD et ENTRYPOINT définissent ce qui est lancé au démarrage du conteneur.
:::

## Les couches et le cache : la clé de la vitesse

Chaque instruction produit une couche. Docker met ces couches en **cache**. Au build suivant, si une instruction et tout ce qui la précède sont inchangés, Docker réutilise la couche existante au lieu de la recalculer. Tu le vois dans la sortie : `CACHED`.

Mais dès qu'une couche change, **toutes les suivantes sont reconstruites**. L'ordre des instructions compte donc énormément. Compare :

```dockerfile
# Mauvais ordre : la moindre modification de code relance npm install
FROM node:20-alpine
WORKDIR /app
COPY . .
RUN npm install
CMD ["node", "server.js"]
```

```dockerfile
# Bon ordre : npm install n'est relancé que si les dépendances changent
FROM node:20-alpine
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
CMD ["node", "server.js"]
```

Dans le second cas, tu copies d'abord **uniquement** les fichiers de dépendances. Tant qu'ils ne changent pas, l'installation reste en cache, même si tu modifies cent fois ton code source. Un rebuild passe de deux minutes à deux secondes.

> **À retenir** : place en haut ce qui change **rarement** (système, dépendances), et en bas ce qui change **souvent** (ton code).

Pour voir les couches d'une image et leur taille :

```bash
docker history mon-app:1.0
```

## Le fichier .dockerignore

Quand tu lances `docker build .`, Docker envoie tout le dossier au démon. Sans filtre, il envoie aussi `node_modules`, `.git`, `vendor`, tes fichiers `.env`... Cela ralentit le build, gonfle l'image et peut y glisser des secrets. Le fichier `.dockerignore`, placé à côté du Dockerfile, fonctionne comme un `.gitignore` :

```text
.git
node_modules
vendor
.env
.env.*
storage/logs
storage/framework/cache
public/build
Dockerfile
docker-compose.yml
*.md
```

Le dossier `node_modules` de ta machine peut contenir des binaires compilés pour ton système (Windows, macOS) qui ne fonctionneront pas dans un conteneur Linux. Les exclure n'est pas qu'une optimisation : c'est une nécessité.

## Exemple complet : une petite API Node

Crée un dossier `demo-node` avec trois fichiers. D'abord `package.json` :

```json
{
  "name": "demo-node",
  "version": "1.0.0",
  "scripts": { "start": "node server.js" },
  "dependencies": {}
}
```

Puis `server.js`, un serveur minimal sans dépendance :

```js
const http = require('http');

const port = process.env.PORT || 3000;

http.createServer((req, res) => {
  res.writeHead(200, { 'Content-Type': 'application/json' });
  res.end(JSON.stringify({ app: 'DevRoad demo', env: process.env.NODE_ENV }));
}).listen(port, '0.0.0.0', () => console.log(`Écoute sur ${port}`));
```

Remarque l'écoute sur `0.0.0.0` : si ton serveur écoute seulement `127.0.0.1`, il n'est joignable que **depuis l'intérieur** du conteneur, et le port publié ne répondra pas. Enfin, le `Dockerfile` :

```dockerfile
FROM node:20-alpine
WORKDIR /app
ENV NODE_ENV=production
COPY package.json ./
RUN npm install --omit=dev
COPY . .
EXPOSE 3000
USER node
CMD ["node", "server.js"]
```

Construis et teste :

```bash
docker build -t demo-node:1.0 .
docker run --rm -p 3000:3000 demo-node:1.0
curl http://localhost:3000
```

L'instruction `USER node` fait tourner l'application avec un utilisateur non privilégié, déjà présent dans l'image officielle Node. C'est une habitude de sécurité à prendre dès le début.

## Exemple : une image pour Laravel

DevRoad est une application PHP avec une partie front compilée par Vite. Voici une première version d'un Dockerfile pour la partie PHP :

```dockerfile
FROM php:8.3-fpm-alpine

RUN apk add --no-cache git unzip libzip-dev \
    && docker-php-ext-install pdo_mysql zip

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader

COPY . .
RUN composer dump-autoload --optimize \
    && chown -R www-data:www-data storage bootstrap/cache

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
```

Plusieurs idées sont à retenir. La commande `docker-php-ext-install` installe les extensions PHP nécessaires (ici `pdo_mysql` pour MySQL). La ligne `COPY --from=composer:2` récupère le binaire Composer depuis une autre image, sans l'installer à la main. Enfin, on applique la même astuce de cache que pour Node : `composer.json` et `composer.lock` sont copiés avant le reste du code.

Le caractère `\` en fin de ligne permet de chaîner plusieurs commandes dans **un seul** `RUN` avec `&&`, ce qui produit une seule couche au lieu de plusieurs.

:::quiz
Pourquoi copie-t-on `package.json` avant le reste du code dans un Dockerfile Node ?
- [ ] Parce que Docker refuse de copier tout le dossier d'un coup
- [ ] Pour que l'image soit chiffrée
- [x] Pour que la couche d'installation des dépendances reste en cache tant que les dépendances ne changent pas
- [ ] Pour accélérer le démarrage du conteneur
> Docker reconstruit toutes les couches situées après une modification. En copiant d'abord les fichiers de dépendances, l'installation n'est relancée que lorsqu'ils changent.
:::

## Atelier guidé : conteneuriser une application

Compte une heure trente. Tu peux utiliser l'exemple `demo-node` ou un petit projet à toi.

1. Crée le dossier `demo-node` avec `package.json` et `server.js` comme ci-dessus.
2. Écris un premier `Dockerfile` volontairement naïf, avec `COPY . .` avant `RUN npm install`.
3. Construis-le avec `docker build -t demo-node:naif .` et note le temps du build.
4. Modifie le message dans `server.js`, reconstruis, et observe quelles étapes sont rejouées.
5. Réécris le Dockerfile avec le bon ordre des couches, construis `demo-node:1.0`, puis refais la modification de l'étape 4 : constate les lignes `CACHED`.
6. Crée un `.dockerignore` qui exclut `node_modules`, `.git` et `.env`, et observe la baisse de la taille du contexte envoyé.
7. Lance le conteneur avec `-p 3000:3000` et teste avec `curl`.
8. Passe la variable `PORT=4000` avec `-e PORT=4000` et adapte le mappage de port : `-p 4000:4000`.
9. Affiche les couches avec `docker history demo-node:1.0` et repère la plus grosse.
10. Bonus : écris un Dockerfile pour ton projet Laravel en t'inspirant de l'exemple, sans chercher à le lancer entièrement (la base de données arrive avec Compose).

Pour t'auto-évaluer : peux-tu expliquer pourquoi l'ordre des instructions change le temps de build, et quelle est la différence entre `RUN`, `CMD` et `ENTRYPOINT` ?

## Erreurs fréquentes

- **Copier tout le code avant d'installer les dépendances.** Le cache est invalidé à chaque modification.
- **Oublier `.dockerignore`.** L'image embarque `node_modules`, `.git` et parfois des secrets.
- **Écouter sur `127.0.0.1` dans le conteneur.** L'application est inaccessible depuis l'extérieur ; écoute sur `0.0.0.0`.
- **Écrire `CMD node server.js` sans tableau.** La forme shell lance un shell intermédiaire qui peut avaler les signaux d'arrêt.
- **Mettre un secret dans `ENV` ou `ARG`.** Il reste lisible dans l'historique de l'image.
- **Confondre `EXPOSE` et `-p`.** `EXPOSE` documente, `-p` publie.
- **Utiliser `latest` dans `FROM`.** Le build n'est plus reproductible.
- **Multiplier les `RUN`.** Chaque instruction ajoute une couche ; regroupe les commandes liées avec `&&`.

## Bonnes pratiques

- Fixe la version de l'image de base : `node:20-alpine`, `php:8.3-fpm-alpine`.
- Ordonne les instructions du plus stable au plus changeant.
- Utilise un `.dockerignore` dès le premier jour.
- Fais tourner l'application avec un utilisateur **non root** grâce à `USER`.
- Privilégie `npm ci` et `composer install` avec un fichier de verrouillage pour des builds reproductibles.
- Nettoie les caches dans la même instruction `RUN` que l'installation (`--no-cache` avec `apk`).
- Écris `CMD` et `ENTRYPOINT` sous forme de tableau JSON.
- Relis ton Dockerfile comme une documentation : un collègue doit comprendre l'environnement en le lisant.

## À retenir

- Un **Dockerfile** est la recette qui décrit comment fabriquer une image ; `docker build -t nom:tag .` la construit.
- Les instructions clés : `FROM`, `WORKDIR`, `COPY`, `RUN`, `ENV`, `EXPOSE`, `CMD`, `ENTRYPOINT`, `USER`.
- `RUN` s'exécute au build, `CMD` au démarrage du conteneur.
- Chaque instruction crée une couche mise en cache ; une modification invalide toutes les couches suivantes.
- Copie les fichiers de dépendances avant le code pour profiter du cache.
- `.dockerignore` allège le contexte de build et protège tes secrets.
- Écoute sur `0.0.0.0` dans le conteneur et lance l'application avec un utilisateur non privilégié.
