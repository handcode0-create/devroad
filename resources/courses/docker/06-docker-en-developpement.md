---
title: Docker en développement
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Docker n'est pas seulement un outil de déploiement : c'est d'abord un excellent **environnement de développement**. Un nouveau membre de l'équipe clone le dépôt, tape une commande, et tout fonctionne : PHP, MySQL, Redis, Node, Mailpit pour tester les e-mails. Mais pour que ce confort soit réel, il faut régler quelques difficultés : le rechargement à chaud du code, les droits de fichiers, le débogage, les tests, la lenteur sous Windows et macOS.

À la fin du chapitre, tu seras capable de :

- structurer des fichiers Compose pour le développement et pour la production ;
- obtenir un cycle de développement rapide avec bind mounts et rechargement à chaud de Vite ;
- exécuter Artisan, Composer, npm et les tests dans les conteneurs ;
- ajouter des services d'appoint : Redis, Mailpit, phpMyAdmin ;
- résoudre les problèmes de permissions de fichiers ;
- brancher un débogueur (Xdebug) sur ton éditeur ;
- simplifier le quotidien avec un `Makefile` ou des scripts.

Prérequis : les chapitres précédents, en particulier « Docker Compose » et « Optimiser les images ». Prévois deux heures trente. Un projet Laravel avec Vite est supposé en place.

## Deux contextes, deux configurations

En production, tu veux une image figée, optimisée, sans bind mount. En développement, tu veux l'inverse : du code modifiable en direct, des outils de débogage, des ports ouverts. Compose permet de combiner **plusieurs fichiers** : un fichier de base, puis un fichier qui le surcharge.

Par convention, un fichier nommé `compose.override.yaml` est lu **automatiquement** avec `compose.yaml`. Organisation recommandée :

```text
devroad/
├── compose.yaml            # services communs : app, web, db
├── compose.override.yaml   # surcharges de développement (chargé automatiquement)
├── compose.prod.yaml       # surcharges de production (chargé explicitement)
├── Dockerfile
└── docker/
    ├── nginx/default.conf
    └── php/xdebug.ini
```

En développement, `docker compose up -d` suffit. En production, on indique les fichiers à combiner :

```bash
docker compose -f compose.yaml -f compose.prod.yaml up -d
```

Pour que le même Dockerfile serve aux deux mondes, on utilise les **cibles** (`target`) du build multi-étapes. Ajoute une étape `development` à ton Dockerfile :

```dockerfile
FROM php:8.3-fpm-alpine AS base
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install pdo_mysql zip
WORKDIR /var/www/html

FROM base AS development
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug \
    && docker-php-ext-enable xdebug \
    && apk del $PHPIZE_DEPS
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

FROM base AS production
COPY --chown=www-data:www-data . .
USER www-data
```

Puis, dans `compose.override.yaml`, tu choisis la cible de développement :

```yaml
services:
  app:
    build:
      target: development
    volumes:
      - .:/var/www/html
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
```

> **Astuce** : lance `docker compose config` pour voir le résultat de la fusion des fichiers. Les valeurs scalaires sont remplacées, les listes comme `ports` et `volumes` sont fusionnées.

:::quiz
Quel fichier Compose est chargé automatiquement avec compose.yaml, sans l'option -f ?
- [ ] compose.dev.yaml
- [ ] compose.prod.yaml
- [x] compose.override.yaml
- [ ] docker-compose.local.yml
> Compose lit compose.yaml puis compose.override.yaml s'il existe. Les autres fichiers doivent être indiqués explicitement avec -f.
:::

## Le code en direct : bind mounts et rechargement

Le bind mount `.:/var/www/html` fait que ton code vit sur ta machine, dans ton éditeur habituel, tandis que PHP l'exécute dans le conteneur. Chaque sauvegarde est visible immédiatement. Pas de rebuild pour une modification de code ; on ne reconstruit que si le Dockerfile ou les dépendances système changent.

### Vite et le rechargement à chaud

Pour le front, ajoute un service Node qui lance le serveur de développement Vite :

```yaml
  node:
    image: node:20-alpine
    working_dir: /app
    command: sh -c "npm install && npm run dev -- --host 0.0.0.0"
    ports:
      - "5173:5173"
    volumes:
      - .:/app
      - node_modules:/app/node_modules

volumes:
  node_modules:
```

Deux détails comptent. L'option `--host 0.0.0.0` rend Vite joignable depuis ton navigateur, sinon il n'écoute qu'à l'intérieur du conteneur. Et le volume nommé `node_modules` **masque** le dossier du même nom côté machine : les modules sont installés pour Linux dans le conteneur, sans mélange avec ceux de ton système.

Dans `vite.config.js`, indique à Vite comment le navigateur doit le joindre et configure la surveillance de fichiers pour Windows et macOS :

```js
import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

export default defineConfig({
    plugins: [
        laravel({ input: ['resources/js/app.jsx'], refresh: true }),
        react(),
    ],
    server: {
        host: '0.0.0.0',
        port: 5173,
        strictPort: true,
        hmr: { host: 'localhost' },
        watch: { usePolling: true },
    },
});
```

L'option `usePolling` est utile quand les événements de fichiers ne traversent pas bien la frontière entre ton système et le conteneur (cas fréquent avec Docker Desktop). Elle consomme un peu de processeur : ne l'active que si le rechargement ne se déclenche pas.

## Exécuter ses outils dans les conteneurs

Tout se passe dans le conteneur, pas sur ta machine. Plus besoin d'y installer PHP ni Composer :

```bash
docker compose exec app composer require laravel/sanctum
docker compose exec app php artisan make:model Roadmap -m
docker compose exec app php artisan migrate
docker compose exec app php artisan tinker
docker compose exec node npm install axios
docker compose exec app php artisan test
```

Pour les commandes ponctuelles lorsque la pile n'est pas démarrée, utilise `run --rm` qui crée un conteneur temporaire :

```bash
docker compose run --rm app composer install
```

Taper `docker compose exec app php artisan` vingt fois par jour est fastidieux. Deux solutions : un alias, ou un `Makefile`.

```text
# Makefile
up:
	docker compose up -d

down:
	docker compose down

art:
	docker compose exec app php artisan $(c)

test:
	docker compose exec app php artisan test

shell:
	docker compose exec app sh
```

Les lignes de commande d'un Makefile doivent commencer par une **tabulation**, pas par des espaces. Tu écris ensuite `make up`, `make art c="migrate:fresh --seed"` ou `make test`.

## Les services d'appoint

L'avantage de Compose est de brancher des services supplémentaires en quelques lignes.

```yaml
  redis:
    image: redis:7-alpine
    volumes:
      - redis-data:/data

  mailpit:
    image: axllent/mailpit
    ports:
      - "8025:8025"   # interface web
      - "1025:1025"   # serveur SMTP

  phpmyadmin:
    image: phpmyadmin:5
    environment:
      PMA_HOST: db
    ports:
      - "8081:80"
    depends_on:
      - db
```

Côté Laravel, le fichier `.env` désigne ces services par leur nom :

```text
CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis
REDIS_HOST=redis

MAIL_MAILER=smtp
MAIL_HOST=mailpit
MAIL_PORT=1025
```

**Mailpit** intercepte tous les e-mails envoyés par l'application et les affiche sur `http://localhost:8025`. Tu testes les mails de réinitialisation de mot de passe sans en envoyer un seul à un vrai utilisateur. Les services de développement ne vont évidemment **pas** dans `compose.yaml` de production : place-les dans `compose.override.yaml`.

## Les droits de fichiers

C'est la source de frustration numéro un sous Linux. Le conteneur exécute PHP avec l'utilisateur `www-data` (identifiant 33). Les fichiers de ton projet t'appartiennent (identifiant 1000, par exemple). Résultat : Laravel ne peut pas écrire dans `storage/logs`, ou au contraire les fichiers créés par le conteneur t'appartiennent à `root` et tu ne peux plus les modifier.

La solution propre est de faire correspondre l'utilisateur du conteneur à ton utilisateur, grâce à des arguments de build :

```dockerfile
FROM base AS development
ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} dev && adduser -D -u ${UID} -G dev dev
USER dev
```

```yaml
  app:
    build:
      target: development
      args:
        UID: ${UID:-1000}
        GID: ${GID:-1000}
```

Sur Linux, `id -u` te donne ton UID. Sous Windows avec WSL 2 et sous macOS avec Docker Desktop, les montages gèrent mieux les droits, mais si tu vois une erreur « Permission denied » sur `storage` ou `bootstrap/cache`, vérifie d'abord l'utilisateur effectif avec `docker compose exec app id`.

> **Attention** : n'utilise pas `chmod -R 777` comme réflexe pour « faire taire » une erreur de droits. Ça fonctionne, mais c'est une faille de sécurité et un mauvais pli à ne pas transposer en production.

## Les performances sur Windows et macOS

Les bind mounts traversent une couche de virtualisation, ce qui ralentit les opérations sur de nombreux petits fichiers (comme `vendor` ou `node_modules`). Trois conseils :

1. Sous Windows, **place ton projet dans le système de fichiers WSL 2** (par exemple `~/projets/devroad`), pas dans `C:\Users\...`. Les performances sont souvent multipliées par cinq à dix.
2. Garde `node_modules` et éventuellement `vendor` dans des volumes nommés, comme montré plus haut.
3. Exclus du montage les dossiers volumineux inutiles à l'édition (`storage/logs`, `public/build`).

## Déboguer avec Xdebug

Un `dd()` dans le code, c'est bien. Un vrai point d'arrêt avec inspection des variables, c'est mieux. Ajoute le fichier `docker/php/xdebug.ini` :

```text
zend_extension=xdebug
xdebug.mode=debug
xdebug.start_with_request=trigger
xdebug.client_host=host.docker.internal
xdebug.client_port=9003
```

Le nom `host.docker.internal` désigne ta machine hôte depuis le conteneur. Il est disponible par défaut sous Docker Desktop. Sous Linux, ajoute dans le service :

```yaml
    extra_hosts:
      - "host.docker.internal:host-gateway"
```

Dans VS Code, installe l'extension PHP Debug et crée `.vscode/launch.json` avec la correspondance des chemins :

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Xdebug Docker",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": { "/var/www/html": "${workspaceFolder}" }
    }
  ]
}
```

Le `pathMappings` traduit les chemins du conteneur en chemins de ta machine : sans lui, l'éditeur ne trouve pas les fichiers. Avec `start_with_request=trigger`, le débogage ne s'active que sur demande (via une extension de navigateur ou le cookie `XDEBUG_SESSION`), ce qui évite de ralentir chaque requête.

:::quiz
Pourquoi monte-t-on un volume nommé sur /app/node_modules dans le service Node de développement ?
- [ ] Pour accélérer le réseau entre conteneurs
- [x] Pour garder les modules installés pour Linux dans le conteneur sans les mélanger avec ceux de la machine hôte
- [ ] Pour que Vite démarre sans package.json
- [ ] Parce qu'un bind mount est interdit dans un service Node
> Le volume nommé masque le dossier node_modules du bind mount. Les binaires compilés pour Linux restent dans le conteneur, et les accès sont plus rapides que via un bind mount.
:::

## Tester dans des conteneurs

Les tests s'exécutent dans le même environnement que l'application, ce qui élimine le « ça passe chez moi ». Prévois une base dédiée pour ne pas effacer tes données de développement. Dans `phpunit.xml` :

```text
<env name="DB_DATABASE" value="devroad_test"/>
```

Crée cette base au démarrage avec un script SQL monté dans MySQL :

```yaml
  db:
    volumes:
      - db-data:/var/lib/mysql
      - ./docker/mysql/init.sql:/docker-entrypoint-initdb.d/init.sql:ro
```

```text
-- docker/mysql/init.sql
CREATE DATABASE IF NOT EXISTS devroad_test;
GRANT ALL PRIVILEGES ON devroad_test.* TO 'devroad'@'%';
```

Les scripts placés dans `/docker-entrypoint-initdb.d` sont exécutés une seule fois, à la création du volume. Ensuite, `make test` lance toute la suite dans le conteneur. La même commande tourne telle quelle dans ta CI.

## Atelier guidé : un environnement complet en une commande

Compte une heure trente.

1. Réorganise ton Dockerfile en étapes `base`, `development` et `production`.
2. Crée `compose.yaml` (services communs) et `compose.override.yaml` (développement : target `development`, bind mounts, services d'appoint).
3. Ajoute les services `node` (Vite), `redis`, `mailpit` et `phpmyadmin`.
4. Configure `vite.config.js` avec `host`, `hmr` et, si nécessaire, `usePolling`.
5. Lance `docker compose up -d` et vérifie les services avec `docker compose ps`.
6. Modifie un composant React : vérifie que le navigateur se met à jour sans recharger.
7. Déclenche un envoi d'e-mail depuis `php artisan tinker` et observe-le dans Mailpit.
8. Écris le `Makefile` avec au moins `up`, `down`, `art`, `test` et `shell`.
9. Configure Xdebug et pose un point d'arrêt dans un contrôleur : l'exécution doit s'arrêter dans ton éditeur.
10. Fais cloner le dépôt par un collègue (ou dans un autre dossier) : `cp .env.example .env && make up` doit suffire à obtenir une application fonctionnelle.

Pour t'auto-évaluer : explique pourquoi `compose.override.yaml` est pratique, et comment tu corrigerais proprement une erreur de permissions sur `storage`.

## Erreurs fréquentes

- **Reconstruire l'image à chaque modification de code.** Utilise un bind mount en développement.
- **Vite inaccessible depuis le navigateur.** Il manque `--host 0.0.0.0` ou la publication du port 5173.
- **HMR qui ne se déclenche pas.** Active `watch.usePolling` ou déplace le projet dans WSL 2.
- **Fichiers créés en `root` dans le projet.** Aligne l'UID et le GID de l'utilisateur du conteneur.
- **Résoudre un souci de droits avec `chmod 777`.** C'est un mauvais réflexe de sécurité.
- **Mettre phpMyAdmin ou Mailpit dans la configuration de production.** Ils n'ont rien à y faire.
- **Brancher Xdebug sans `pathMappings`.** L'éditeur ne retrouve pas les fichiers.
- **Lancer les tests sur la base de développement.** Tu perds tes données ; utilise une base de test.

## Bonnes pratiques

- Un `compose.yaml` commun, des surcharges pour le développement et la production.
- Un seul Dockerfile multi-étapes avec une cible `development` et une cible `production`.
- Tous les outils (Composer, Artisan, npm, tests) passent par les conteneurs.
- Un `Makefile` ou des scripts pour raccourcir les commandes répétitives.
- Des volumes nommés pour `node_modules` et pour les données.
- Des services d'appoint isolés dans l'override : Mailpit, phpMyAdmin, Redis de test.
- Un `README` d'une dizaine de lignes qui explique comment démarrer le projet.
- L'onboarding d'un nouveau développeur comme critère de qualité : il doit tenir en moins de dix minutes.

## À retenir

- `compose.override.yaml` est chargé automatiquement et sert aux surcharges de développement.
- Un Dockerfile multi-étapes avec les cibles `development` et `production` évite de maintenir deux fichiers.
- Les **bind mounts** donnent le rechargement instantané du code ; un volume nommé protège `node_modules`.
- Vite doit écouter sur `0.0.0.0` et exposer le port 5173 pour le rechargement à chaud.
- Mailpit, Redis et phpMyAdmin se branchent en quelques lignes et se désignent par leur nom de service.
- Les problèmes de permissions se règlent en alignant l'UID et le GID, pas avec `chmod 777`.
- Xdebug se connecte via `host.docker.internal` et nécessite des `pathMappings` côté éditeur.
