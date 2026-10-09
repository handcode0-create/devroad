---
title: Projet final Docker
minutes: 360
level: professional
---

## Ce que tu vas apprendre

C'est l'heure de mettre tout le parcours en pratique. Tu vas conteneuriser **DevRoad de bout en bout**, comme le ferait une équipe professionnelle : un environnement de développement qui démarre en une commande, une image de production optimisée et sûre, une chaîne d'intégration continue qui construit et publie l'image, et un déploiement sur un serveur avec sauvegardes et supervision. Ce projet est le livrable qui prouve que tu maîtrises Docker.

À la fin du projet, tu auras :

- un **Dockerfile multi-étapes** pour DevRoad (Laravel, MySQL, Vite) avec des cibles `development` et `production` ;
- des fichiers **Compose** séparés pour le développement et la production ;
- une configuration **Nginx**, **PHP-FPM** et **MySQL** avec volumes, réseaux et healthchecks ;
- un **pipeline CI** (GitHub Actions) qui teste, construit et publie l'image ;
- un **déploiement** sur un serveur Linux (VPS) avec HTTPS, sauvegardes et mise à jour sans perte de données ;
- un dossier de documentation qui permet à un tiers de reprendre le projet.

Prérequis : les six chapitres précédents, un projet Laravel avec Vite et React fonctionnel (le dépôt DevRoad ou un projet équivalent), un compte GitHub et, pour la dernière partie, un petit serveur Linux (VPS) accessible en SSH avec Docker installé. Si tu n'as pas de serveur, remplace la partie 6 par un déploiement local simulé avec le fichier de production. Prévois six heures, à répartir sur plusieurs sessions.

## Cahier des charges

### Contexte

L'équipe DevRoad grandit. Aujourd'hui, installer le projet prend une demi-journée et les déploiements se font à la main par FTP. Tu es chargé de professionnaliser la livraison avec Docker.

### Exigences fonctionnelles

1. Un développeur clone le dépôt, copie `.env.example` en `.env`, tape `make up`, et obtient une application accessible sur `http://localhost:8000` avec des données de démonstration.
2. Le rechargement à chaud du front (Vite) et la modification instantanée du code PHP fonctionnent en développement.
3. Les e-mails sont interceptés par Mailpit en développement.
4. Les tests automatisés s'exécutent avec une seule commande dans des conteneurs.
5. La production sert l'application en HTTPS, avec une base MySQL persistante non exposée à Internet.
6. Chaque `push` sur la branche principale déclenche : tests, construction, analyse de sécurité et publication d'une image versionnée.
7. Le déploiement d'une nouvelle version se fait avec une seule commande, sans perdre de données.
8. Une sauvegarde quotidienne de la base est planifiée et sa restauration a été testée.

### Exigences non fonctionnelles

| Domaine | Critère mesurable |
| --- | --- |
| Taille | Image de production inférieure à 300 Mo |
| Vitesse | Rebuild après modification de code PHP inférieur à 30 secondes en local |
| Sécurité | Aucun conteneur en root, aucun secret dans l'image ni dans Git, aucune vulnérabilité critique corrigeable |
| Fiabilité | Healthchecks sur la base et l'application, `restart: unless-stopped` |
| Reproductibilité | Versions d'images figées, fichiers de verrouillage (`composer.lock`, `package-lock.json`) |
| Documentation | README avec démarrage, commandes, variables, procédure de déploiement et de restauration |

### Architecture cible

```text
Internet
   |
 :443 / :80
   v
+---------+      +-----------+      +----------+
| proxy   | ---> |   web     | ---> |   app    |
| (HTTPS) |      |  (Nginx)  |      | (PHP-FPM)|
+---------+      +-----------+      +----+-----+
                                          |
                          +---------------+---------------+
                          |                               |
                     +----v-----+                   +-----v----+
                     |   db     |                   |  redis   |
                     | (MySQL)  |                   |  (cache) |
                     +----+-----+                   +----------+
                          |
                  volume db-data
```

Seul le proxy publie des ports. Tout le reste vit sur un réseau interne.

## Étape 1 : préparer le dépôt

Crée une branche et organise l'arborescence.

```bash
git checkout -b feature/docker
mkdir -p docker/nginx docker/php docker/mysql docker/scripts .github/workflows
```

Structure attendue à la fin du projet :

```text
devroad/
├── Dockerfile
├── compose.yaml
├── compose.override.yaml
├── compose.prod.yaml
├── .dockerignore
├── .env.example
├── Makefile
├── docker/
│   ├── nginx/default.conf
│   ├── php/opcache.ini
│   ├── php/xdebug.ini
│   ├── mysql/init.sql
│   └── scripts/backup.sh
└── .github/workflows/ci.yml
```

Écris d'abord le `.dockerignore`, en t'inspirant du chapitre « Optimiser les images » : `.git`, `node_modules`, `vendor`, `.env`, `storage/logs`, `tests`, etc. Ajoute `.env` au `.gitignore` s'il n'y est pas déjà.

## Étape 2 : le Dockerfile multi-étapes

Rédige un Dockerfile avec cinq étapes : `base`, `development`, `vendor`, `assets`, `production`. L'étape `base` contient PHP-FPM et les extensions. L'étape `development` y ajoute Composer et Xdebug. Les étapes `vendor` et `assets` compilent les dépendances. L'étape `production` assemble le résultat.

```dockerfile
# syntax=docker/dockerfile:1
ARG PHP_VERSION=8.3

FROM php:${PHP_VERSION}-fpm-alpine AS base
RUN apk add --no-cache libzip-dev icu-dev \
    && docker-php-ext-install pdo_mysql zip intl opcache
WORKDIR /var/www/html

FROM base AS development
RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && pecl install xdebug redis \
    && docker-php-ext-enable xdebug redis \
    && apk del $PHPIZE_DEPS
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN --mount=type=cache,target=/tmp/cache \
    composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN --mount=type=cache,target=/root/.npm npm ci
COPY resources ./resources
COPY vite.config.js ./
COPY public ./public
RUN npm run build

FROM base AS production
RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install redis && docker-php-ext-enable redis \
    && apk del .build-deps
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini
COPY --from=vendor --chown=www-data:www-data /app ./
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
RUN mkdir -p storage/framework/{cache,sessions,views} storage/logs bootstrap/cache \
    && chown -R www-data:www-data storage bootstrap/cache
USER www-data
EXPOSE 9000
HEALTHCHECK --interval=30s --timeout=5s --retries=3 \
  CMD php-fpm -t || exit 1
CMD ["php-fpm"]
```

Adapte les noms de fichiers (`vite.config.js`, dossier `resources/js`) à ton projet. Construis les deux cibles et compare :

```bash
docker build --target development -t devroad:dev .
docker build --target production -t devroad:prod .
docker images devroad
```

L'image `prod` doit être sous les 300 Mo et ne contenir ni Composer, ni Node, ni Xdebug. Vérifie-le :

```bash
docker run --rm devroad:prod sh -c "which composer node; php -m | grep -i xdebug; id"
```

Le résultat attendu : aucune de ces commandes ne trouve quoi que ce soit, et `id` indique l'utilisateur `www-data`.

:::quiz
Pourquoi l'étape `production` ne doit-elle pas être construite à partir de l'étape `development` ?
- [ ] Parce que Docker interdit d'hériter d'une autre étape
- [x] Parce qu'elle embarquerait Composer et Xdebug, inutiles et risqués en production
- [ ] Parce que l'étape development ne contient pas PHP
- [ ] Parce que cela empêcherait le cache de fonctionner
> Chaque étape finale doit être minimale. Les outils de développement augmentent la taille et la surface d'attaque de l'image servie aux utilisateurs.
:::

## Étape 3 : l'environnement de développement

Écris `compose.yaml` pour les services communs, puis `compose.override.yaml` pour le développement. Le fichier commun décrit `app`, `web`, `db`, `redis` et les volumes. L'override ajoute le bind mount du code, le service `node` (Vite), `mailpit` et `phpmyadmin`.

```yaml
# compose.yaml
services:
  app:
    build:
      context: .
      target: production
    env_file: .env
    depends_on:
      db:
        condition: service_healthy
      redis:
        condition: service_started
    restart: unless-stopped

  web:
    image: nginx:1.27-alpine
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app
    restart: unless-stopped

  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
    volumes:
      - db-data:/var/lib/mysql
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-p${DB_ROOT_PASSWORD}"]
      interval: 5s
      timeout: 5s
      retries: 12
      start_period: 20s
    restart: unless-stopped

  redis:
    image: redis:7-alpine
    restart: unless-stopped

volumes:
  db-data:
```

```yaml
# compose.override.yaml
services:
  app:
    build:
      target: development
    volumes:
      - .:/var/www/html
    environment:
      APP_ENV: local
      APP_DEBUG: "true"
    extra_hosts:
      - "host.docker.internal:host-gateway"

  web:
    ports:
      - "${APP_PORT:-8000}:80"
    volumes:
      - .:/var/www/html:ro

  node:
    image: node:20-alpine
    working_dir: /app
    command: sh -c "npm install && npm run dev -- --host 0.0.0.0"
    ports:
      - "5173:5173"
    volumes:
      - .:/app
      - node_modules:/app/node_modules

  mailpit:
    image: axllent/mailpit
    ports:
      - "8025:8025"

  phpmyadmin:
    image: phpmyadmin:5
    environment:
      PMA_HOST: db
    ports:
      - "8081:80"

volumes:
  node_modules:
```

Écris ensuite le `Makefile` (tabulations obligatoires) avec les cibles `up`, `down`, `build`, `install`, `art`, `test`, `shell`, `logs`. La cible `install` enchaîne `composer install`, `key:generate` et `migrate --seed`. Teste le parcours du nouveau développeur dans un dossier vierge :

```bash
git clone <ton-depot> devroad-test && cd devroad-test
cp .env.example .env
make up && make install
```

Si une seule étape échoue ou demande une action manuelle non documentée, corrige-la avant de continuer.

## Étape 4 : la configuration de production

Crée `compose.prod.yaml`. Il retire les bind mounts, ferme les ports de la base, ajoute un proxy HTTPS et des limites de ressources. Pour le HTTPS automatique, **Caddy** est le choix le plus simple : il obtient et renouvelle les certificats Let's Encrypt tout seul.

```yaml
# compose.prod.yaml
services:
  app:
    image: ghcr.io/ton-organisation/devroad:${APP_VERSION:-latest}
    environment:
      APP_ENV: production
      APP_DEBUG: "false"
    read_only: true
    tmpfs:
      - /tmp
    volumes:
      - app-storage:/var/www/html/storage
    deploy:
      resources:
        limits:
          memory: 512M

  web:
    volumes:
      - ./docker/nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
      - public-assets:/var/www/html/public:ro

  proxy:
    image: caddy:2-alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/Caddyfile:/etc/caddy/Caddyfile:ro
      - caddy-data:/data
    depends_on:
      - web
    restart: unless-stopped

volumes:
  app-storage:
  public-assets:
  caddy-data:
```

Le `Caddyfile` tient en trois lignes :

```text
devroad.example.com {
    reverse_proxy web:80
}
```

> **Attention** : `read_only: true` est une excellente protection, mais Laravel doit pouvoir écrire dans `storage` et `bootstrap/cache`. D'où le volume dédié. Teste soigneusement : une application qui plante sur un système de fichiers en lecture seule est un classique.

Vérifie la fusion des fichiers et l'absence de port exposé pour la base :

```bash
docker compose -f compose.yaml -f compose.prod.yaml config | grep -A2 "ports"
```

Seul le service `proxy` doit apparaître avec ses ports 80 et 443.

## Étape 5 : l'intégration continue

Le pipeline GitHub Actions teste, construit et publie. Crée `.github/workflows/ci.yml` :

```yaml
name: CI

on:
  push:
    branches: [main]
    tags: ["v*"]
  pull_request:

jobs:
  test:
    runs-on: ubuntu-latest
    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_DATABASE: devroad_test
          MYSQL_ROOT_PASSWORD: secret
        ports: ["3306:3306"]
        options: >-
          --health-cmd="mysqladmin ping -psecret"
          --health-interval=5s --health-retries=12
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env && php artisan key:generate
      - run: php artisan test
        env:
          DB_HOST: 127.0.0.1
          DB_DATABASE: devroad_test
          DB_PASSWORD: secret

  build:
    needs: test
    if: github.event_name != 'pull_request'
    runs-on: ubuntu-latest
    permissions:
      contents: read
      packages: write
    steps:
      - uses: actions/checkout@v4
      - uses: docker/setup-buildx-action@v3
      - uses: docker/login-action@v3
        with:
          registry: ghcr.io
          username: ${{ github.actor }}
          password: ${{ secrets.GITHUB_TOKEN }}
      - uses: docker/metadata-action@v5
        id: meta
        with:
          images: ghcr.io/${{ github.repository_owner }}/devroad
          tags: |
            type=sha
            type=ref,event=tag
            type=raw,value=latest,enable={{is_default_branch}}
      - uses: docker/build-push-action@v6
        with:
          context: .
          target: production
          push: true
          tags: ${{ steps.meta.outputs.tags }}
          cache-from: type=gha
          cache-to: type=gha,mode=max
      - uses: aquasecurity/trivy-action@0.28.0
        with:
          image-ref: ghcr.io/${{ github.repository_owner }}/devroad:latest
          severity: CRITICAL
          ignore-unfixed: true
          exit-code: "1"
```

Le job `test` tourne sur chaque pull request. Le job `build` ne s'exécute qu'après des tests verts et uniquement hors pull request ; il publie l'image avec plusieurs tags (le SHA du commit, le tag Git et `latest`) puis analyse les vulnérabilités critiques. Le cache `type=gha` réutilise les couches d'un build à l'autre.

Pousse la branche, ouvre une pull request et vérifie que les tests s'exécutent. Fusionne-la et contrôle la présence de l'image dans l'onglet « Packages » de GitHub.

## Étape 6 : déploiement et exploitation

Sur le serveur, installe Docker puis récupère les fichiers de configuration (et seulement eux, pas le code source) :

```bash
ssh deploy@ton-serveur
mkdir -p ~/devroad && cd ~/devroad
# copie compose.yaml, compose.prod.yaml, docker/, .env de production
docker login ghcr.io
```

Le fichier `.env` de production contient de vrais secrets générés (par exemple avec `openssl rand -base64 32`) et il ne quitte jamais le serveur. Lance la première mise en ligne :

```bash
export APP_VERSION=sha-abc1234
docker compose -f compose.yaml -f compose.prod.yaml pull
docker compose -f compose.yaml -f compose.prod.yaml up -d
docker compose -f compose.yaml -f compose.prod.yaml exec app php artisan migrate --force
docker compose -f compose.yaml -f compose.prod.yaml exec app php artisan config:cache
```

Écris un script `docker/scripts/deploy.sh` qui enchaîne ces commandes pour une nouvelle version, et qui s'arrête à la première erreur (`set -e`). Conserve l'ancien tag de l'image : pour un **retour arrière**, il suffit de relancer le script avec la version précédente.

### Sauvegardes

Crée `docker/scripts/backup.sh` :

```bash
#!/bin/sh
set -e
cd "$(dirname "$0")/../.."
DATE=$(date +%F-%H%M)
mkdir -p backups
docker compose -f compose.yaml -f compose.prod.yaml exec -T db \
  sh -c 'mysqldump -uroot -p"$MYSQL_ROOT_PASSWORD" --single-transaction "$MYSQL_DATABASE"' \
  | gzip > "backups/devroad-$DATE.sql.gz"
find backups -name "*.sql.gz" -mtime +14 -delete
```

Planifie-le avec `crontab -e` pour 3 h du matin :

```text
0 3 * * * /home/deploy/devroad/docker/scripts/backup.sh >> /home/deploy/backup.log 2>&1
```

Une sauvegarde jamais restaurée n'est pas une sauvegarde. Teste la restauration sur une base vide :

```bash
gunzip -c backups/devroad-2026-10-09-0300.sql.gz | \
  docker compose -f compose.yaml -f compose.prod.yaml exec -T db \
  sh -c 'mysql -uroot -p"$MYSQL_ROOT_PASSWORD" "$MYSQL_DATABASE"'
```

### Supervision

Vérifie régulièrement la santé de la pile :

```bash
docker compose -f compose.yaml -f compose.prod.yaml ps
docker compose -f compose.yaml -f compose.prod.yaml logs --tail 50 app
docker stats --no-stream
docker system df
```

Configure la rotation des logs pour ne pas saturer le disque, en ajoutant à chaque service dans `compose.prod.yaml` :

```yaml
    logging:
      driver: json-file
      options:
        max-size: "10m"
        max-file: "3"
```

:::quiz
Pourquoi une sauvegarde de base de données doit-elle être testée par une restauration ?
- [ ] Parce que mysqldump ne fonctionne qu'une fois sur deux
- [ ] Pour économiser de l'espace disque
- [x] Pour s'assurer que le fichier est complet, lisible et exploitable le jour où on en aura besoin
- [ ] Parce que Docker supprime les sauvegardes non testées
> Une sauvegarde corrompue, incomplète ou impossible à restaurer ne protège de rien. Seule une restauration réussie prouve que la procédure fonctionne.
:::

## Checklist de validation

Parcours chaque point. Le projet est réussi quand tout est coché.

Développement :

1. `git clone`, copie de `.env.example` et `make up` suffisent pour obtenir DevRoad sur `http://localhost:8000`.
2. Une modification d'un composant React se reflète sans recharger la page.
3. Une modification d'un contrôleur PHP est visible immédiatement, sans rebuild.
4. Un e-mail envoyé depuis l'application apparaît dans Mailpit.
5. `make test` exécute la suite de tests dans un conteneur et passe.
6. Aucun fichier du projet n'appartient à `root` après une session de travail.

Image et sécurité :

1. L'image de production fait moins de 300 Mo.
2. Elle ne contient ni Composer, ni Node, ni Xdebug.
3. Elle s'exécute avec un utilisateur non root.
4. `docker history` ne révèle aucun secret.
5. L'analyse Trivy ne remonte aucune vulnérabilité critique corrigeable.
6. Le `.dockerignore` exclut `.env`, `.git`, `node_modules` et `vendor`.

Production :

1. Seuls les ports 80 et 443 sont ouverts, la base n'est pas joignable depuis Internet.
2. Le site répond en HTTPS avec un certificat valide.
3. Les healthchecks passent au vert (`docker compose ps`).
4. Les données de la base survivent à `docker compose down` puis `up -d`.
5. Une nouvelle version se déploie avec une seule commande et un retour arrière est possible.
6. Une sauvegarde quotidienne est planifiée et sa restauration a été testée.
7. Les logs sont limités par rotation.

CI et documentation :

1. Une pull request déclenche les tests.
2. Un merge sur `main` publie l'image avec un tag de version et un tag `latest`.
3. Le README explique : démarrage, commandes `make`, variables d'environnement, déploiement, sauvegarde et restauration.
4. Un collègue a réussi à démarrer le projet en suivant uniquement le README.

## Pour aller plus loin

- Ajoute un conteneur `queue` qui exécute `php artisan queue:work` avec la même image et une commande différente.
- Ajoute un conteneur `scheduler` pour `php artisan schedule:work`.
- Mets en place une mise à jour sans interruption avec deux instances `app` et un proxy qui bascule.
- Surveille la pile avec un outil léger (Uptime Kuma, Portainer ou un export Prometheus).
- Utilise les **secrets Docker** (`secrets:` dans Compose) plutôt que des variables d'environnement pour les mots de passe.
- Explore Kubernetes ou Docker Swarm pour comprendre l'étape suivante, quand un seul serveur ne suffit plus.

## Erreurs fréquentes

- **Oublier d'adapter les chemins du Dockerfile.** Le build échoue parce que `vite.config.js` ou `resources` n'existent pas sous ce nom.
- **Exposer le port 3306 en production.** La base devient accessible depuis Internet.
- **Commiter `.env` de production.** Les secrets fuitent dans l'historique Git ; renouvelle-les immédiatement si c'est arrivé.
- **Oublier `migrate --force`.** En environnement de production, Artisan refuse de migrer sans cette option.
- **Utiliser `latest` pour déployer.** Tu ne sais plus quelle version tourne ni vers laquelle revenir.
- **Système de fichiers en lecture seule sans volume pour `storage`.** Laravel plante au premier log.
- **Ne jamais tester la restauration.** Le jour d'un incident, la sauvegarde est inutilisable.
- **Cache CI non configuré.** Chaque build repart de zéro et dure dix minutes.
- **Faire un `docker compose down -v` en production.** Le volume de la base est détruit.

## Bonnes pratiques

- Un seul Dockerfile, plusieurs cibles, des surcharges Compose par environnement.
- Des tags d'image immuables (SHA ou version) pour pouvoir déployer et revenir en arrière.
- Les secrets hors du dépôt, hors de l'image, fournis à l'exécution.
- Un proxy unique exposé, tout le reste sur un réseau interne.
- Des healthchecks et des politiques de redémarrage sur chaque service critique.
- Une CI qui échoue vite : tests d'abord, construction ensuite, analyse de sécurité avant la mise en ligne.
- Des sauvegardes automatiques, chiffrées si possible, copiées hors du serveur et restaurées à blanc régulièrement.
- Une documentation vivante : si le README n'est pas à jour, le projet n'est pas terminé.

## À retenir

- Un projet Docker professionnel se juge sur quatre axes : **reproductibilité**, **sécurité**, **automatisation** et **exploitabilité**.
- Le Dockerfile multi-étapes sert à la fois le développement (outils, débogage) et la production (image minimale).
- Compose combine un fichier commun et des surcharges par environnement.
- La CI construit, scanne et publie des images versionnées ; le déploiement consiste à tirer une version précise.
- La base de données vit dans un volume, jamais exposée, sauvegardée et restaurée à blanc.
- Ce que tu as construit ici, un nouvel arrivant peut le démarrer en une commande : c'est la vraie valeur de Docker pour une équipe.
