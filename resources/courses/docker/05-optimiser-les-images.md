---
title: Optimiser les images
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Ton premier Dockerfile fonctionne, mais l'image fait 1,4 Go, met huit minutes à se construire et embarque Composer, Node, des outils de compilation et peut-être même tes fichiers de tests. Pour la production, c'est trop lourd, trop lent à déployer et trop risqué : chaque outil inutile est une surface d'attaque en plus. Ce chapitre t'apprend à fabriquer des images **petites, rapides à construire et sûres**.

À la fin du chapitre, tu seras capable de :

- mesurer la taille d'une image et identifier ce qui la fait grossir ;
- choisir une image de base adaptée (`alpine`, `slim`, `distroless`) ;
- écrire un **build multi-étapes** (*multi-stage build*) ;
- tirer le meilleur du **cache** de build, y compris avec BuildKit ;
- exclure le superflu avec `.dockerignore` ;
- durcir une image : utilisateur non root, versions figées, analyse de vulnérabilités ;
- construire une image de production pour DevRoad : PHP-FPM compilé avec le front Vite.

Prérequis : les chapitres « Dockerfile » et « Docker Compose ». Prévois deux heures trente. Quelques notions de Composer et de npm sont utiles.

## Mesurer avant d'optimiser

On n'optimise pas à l'aveugle. Commence par regarder où en est l'image :

```bash
docker images devroad-app
docker history devroad-app:naif
docker history --no-trunc --format "{{.Size}}\t{{.CreatedBy}}" devroad-app:naif | head -20
```

La commande `docker history` liste les couches avec leur taille. Tu y repéreras vite les coupables : un `apt-get install` sans nettoyage, un `COPY . .` qui embarque `node_modules`, un `npm install` complet avec les dépendances de développement.

Pour explorer l'image couche par couche, l'outil open source **dive** est très pratique :

```bash
dive devroad-app:naif
```

Note la taille de départ : tu vas la diviser par trois ou quatre.

## Choisir la bonne image de base

La base pèse souvent plus que ton application. Voici l'ordre de grandeur pour Node :

| Image | Taille approximative | Remarque |
| --- | --- | --- |
| `node:20` | plus de 1 Go | Debian complète, nombreux outils |
| `node:20-slim` | environ 200 Mo | Debian allégée |
| `node:20-alpine` | environ 150 Mo | Alpine Linux, très compacte |

Les variantes **Alpine** utilisent la bibliothèque `musl` au lieu de `glibc`. Dans 95 % des cas, tout fonctionne. Mais certains modules natifs précompilés pour `glibc` posent problème ; dans ce cas, passe à `slim`. Pour PHP, `php:8.3-fpm-alpine` est une excellente base.

Il existe aussi les images **distroless** de Google, qui contiennent uniquement ton programme et ses dépendances d'exécution : pas de shell, pas de gestionnaire de paquets. Elles sont très sûres, mais plus difficiles à déboguer.

> **Astuce** : pour un bon compromis, vise `alpine` ou `slim`, fixe la version mineure (`node:20.18-alpine`) et mets-la à jour régulièrement.

## Le build multi-étapes : la technique reine

Pour **compiler** une application, il faut des outils (Composer, Node, npm, compilateurs). Pour l'**exécuter**, on n'en a pas besoin. Le build multi-étapes sépare les deux : plusieurs `FROM` dans un même Dockerfile, et seule la dernière étape devient l'image finale. On y copie uniquement le résultat des étapes précédentes.

Premier exemple avec une application front compilée par Vite et servie par Nginx :

```dockerfile
# Étape 1 : compilation
FROM node:20-alpine AS build
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# Étape 2 : image finale, uniquement les fichiers compilés
FROM nginx:1.27-alpine
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80
```

L'image finale ne contient ni Node, ni `node_modules`, ni code source : juste Nginx et quelques fichiers statiques. On passe de plus de 1 Go à une quarantaine de mégaoctets. Le mot clé `AS build` nomme une étape, et `COPY --from=build` y pioche des fichiers.

Tu peux aussi construire une étape précise avec `--target` :

```bash
docker build --target build -t devroad-front:build .
```

C'est utile pour déboguer une étape intermédiaire ou pour lancer les tests dans une étape dédiée.

:::quiz
Quel est l'intérêt principal d'un build multi-étapes ?
- [ ] Construire plusieurs images en parallèle sur plusieurs machines
- [x] Garder dans l'image finale uniquement ce qui est nécessaire à l'exécution, sans les outils de compilation
- [ ] Rendre le build plus lent mais plus sûr
- [ ] Éviter d'écrire un fichier .dockerignore
> Les outils de build (Node, Composer, compilateurs) restent dans les étapes intermédiaires. Seul le résultat est copié dans l'étape finale, ce qui réduit fortement la taille et la surface d'attaque.
:::

## Une image de production pour DevRoad

DevRoad combine PHP (Laravel) et un front compilé par Vite. Un Dockerfile de production en trois étapes :

```dockerfile
# syntax=docker/dockerfile:1

# Étape 1 : dépendances PHP de production
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY . .
RUN composer dump-autoload --optimize --no-dev

# Étape 2 : compilation du front avec Vite
FROM node:20-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
COPY --from=vendor /app/vendor ./vendor
RUN npm run build

# Étape 3 : image finale
FROM php:8.3-fpm-alpine AS production
RUN apk add --no-cache libzip-dev \
    && docker-php-ext-install pdo_mysql zip opcache

WORKDIR /var/www/html
COPY --from=vendor --chown=www-data:www-data /app ./
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build

USER www-data
EXPOSE 9000
CMD ["php-fpm"]
```

Plusieurs points méritent explication. La première étape installe **sans** les dépendances de développement (`--no-dev`) : PHPUnit, Faker et consorts n'ont rien à faire en production. La deuxième étape a besoin de `vendor` seulement si Vite référence des assets provenant de packages PHP (comme certains composants Livewire ou Inertia via Ziggy) ; supprime cette ligne si ce n'est pas ton cas. La troisième étape part d'une base PHP propre et n'y copie que l'application et les fichiers compilés dans `public/build`. L'option `--chown` règle directement les droits au moment de la copie, ce qui évite un `RUN chown -R` qui dupliquerait toutes les couches.

L'extension `opcache` accélère nettement PHP en production, en gardant le code compilé en mémoire. Active-la avec un petit fichier de configuration :

```text
; docker/php/opcache.ini
opcache.enable=1
opcache.memory_consumption=128
opcache.validate_timestamps=0
```

La valeur `validate_timestamps=0` interdit à PHP de relire les fichiers : parfait en production où le code ne change plus, mais à éviter en développement.

## Maîtriser le cache de build

Le cache fonctionne couche par couche, comme vu au chapitre « Dockerfile ». Voici trois leviers pour aller plus loin.

### 1. L'ordre et le regroupement

Mets en tête les couches stables. Regroupe les commandes dont le résultat doit être nettoyé dans **la même instruction** `RUN`, sinon le nettoyage ne réduit rien : la couche précédente contient déjà les fichiers.

```dockerfile
# Mauvais : les fichiers temporaires restent dans la couche précédente
RUN apt-get update && apt-get install -y curl
RUN rm -rf /var/lib/apt/lists/*

# Bon : une seule couche, déjà nettoyée
RUN apt-get update \
    && apt-get install -y --no-install-recommends curl \
    && rm -rf /var/lib/apt/lists/*
```

### 2. Les caches montés de BuildKit

BuildKit, le moteur de build moderne de Docker, permet de conserver le cache des gestionnaires de paquets **entre les builds**, sans l'inclure dans l'image :

```dockerfile
# syntax=docker/dockerfile:1
RUN --mount=type=cache,target=/root/.npm \
    npm ci
```

Même quand `package-lock.json` change, npm réutilise les paquets déjà téléchargés. Même idée avec Composer : `--mount=type=cache,target=/tmp/cache`.

### 3. Le cache en intégration continue

Dans un pipeline CI, chaque build démarre sur une machine vide. Exporte et réimporte le cache :

```bash
docker buildx build \
  --cache-from type=registry,ref=ghcr.io/hancode/devroad:cache \
  --cache-to type=registry,ref=ghcr.io/hancode/devroad:cache,mode=max \
  -t ghcr.io/hancode/devroad:1.0.0 --push .
```

:::quiz
Pourquoi regrouper `apt-get install` et `rm -rf /var/lib/apt/lists/*` dans la même instruction RUN ?
- [ ] Parce que Docker interdit deux RUN consécutifs
- [ ] Pour que la commande s'exécute plus vite
- [x] Parce que les fichiers supprimés dans une couche ultérieure restent présents dans la couche précédente et occupent de la place
- [ ] Pour désactiver le cache de build
> Chaque couche est immuable. Supprimer un fichier dans une nouvelle couche le masque seulement ; sa taille reste comptée dans l'image. Il faut nettoyer dans la même couche.
:::

## Exclure le superflu

Un `.dockerignore` bien écrit réduit le contexte de build et évite d'invalider le cache pour rien :

```text
.git
.github
node_modules
vendor
tests
storage/logs/*
storage/framework/cache/*
public/build
.env
.env.*
docker-compose*.yml
compose*.yaml
README.md
```

Chaque fois que tu modifies un fichier du contexte copié par `COPY . .`, la couche est invalidée. Un fichier de logs qui change à chaque requête peut ainsi ruiner tout ton cache : exclus-le.

## Durcir l'image : sécurité

Une image optimisée est aussi une image plus sûre. Voici une liste de réflexes.

1. **Utilisateur non root.** `USER www-data` ou `USER node` : si quelqu'un exploite une faille, il n'a pas les droits root dans le conteneur.
2. **Moins de paquets.** Chaque binaire installé est une faille potentielle. Pas de `curl`, `git` ou `vim` dans l'image finale.
3. **Versions figées.** `php:8.3.12-fpm-alpine` plutôt que `php:latest`. Tu contrôles les mises à jour.
4. **Pas de secrets dans l'image.** Ni dans `ENV`, ni copiés par `COPY`. Fournis-les à l'exécution via variables ou secrets.
5. **Système de fichiers en lecture seule** quand c'est possible, avec `read_only: true` dans Compose et un `tmpfs` pour les dossiers inscriptibles.
6. **Analyse des vulnérabilités.** Docker intègre un scanner :

```bash
docker scout quickview devroad-app:1.0.0
docker scout cves devroad-app:1.0.0
```

Des outils open source comme **Trivy** font la même chose et s'intègrent facilement en CI :

```bash
trivy image devroad-app:1.0.0
```

Corrige en priorité les vulnérabilités critiques qui ont un correctif disponible : souvent, il suffit de mettre à jour l'image de base.

## Comparer avant et après

Garde une trace de tes gains. Voici un exemple de résultat plausible pour DevRoad :

```text
devroad-app:naif        1.42 GB   build à froid : 7 min 40 s
devroad-app:multistage  218 MB    build à froid : 3 min 05 s
devroad-app:multistage  218 MB    rebuild après modif du code : 12 s
```

Le rebuild de 12 secondes est le résultat d'un bon ordre des couches et d'un cache bien utilisé. C'est ce chiffre qui améliore ton quotidien de développeur.

## Atelier guidé : alléger l'image de DevRoad

Compte une heure trente.

1. Construis une version naïve : image `php:8.3` (Debian complète), `COPY . .`, `composer install` complet et `npm install` dans la même image. Nomme-la `devroad-app:naif`.
2. Note sa taille avec `docker images` et regarde les couches avec `docker history`.
3. Crée un `.dockerignore` complet et reconstruis : mesure la différence de taille et de temps.
4. Réécris le Dockerfile en trois étapes (`vendor`, `assets`, `production`) comme ci-dessus. Adapte les noms de fichiers de configuration à ton projet.
5. Construis `devroad-app:multistage` et compare les tailles.
6. Modifie un contrôleur PHP et reconstruis : vérifie que seules les dernières couches sont rejouées.
7. Ajoute `RUN --mount=type=cache` pour `npm ci` et compare le temps après modification de `package.json`.
8. Vérifie que l'image tourne avec un utilisateur non root : `docker run --rm devroad-app:multistage id`.
9. Analyse l'image avec `docker scout quickview` ou `trivy image` et note les vulnérabilités critiques.
10. Remplace l'image de base par une version plus récente et relance l'analyse.

Pour t'auto-évaluer : explique à un collègue pourquoi l'image finale ne contient plus Node, et ce que fait `COPY --from=assets`.

## Erreurs fréquentes

- **Optimiser sans mesurer.** Regarde toujours `docker images` et `docker history` avant et après.
- **Installer les dépendances de développement en production.** Utilise `--no-dev` pour Composer et `npm ci --omit=dev` pour une étape d'exécution Node.
- **Nettoyer dans une couche séparée.** Le nettoyage n'a aucun effet sur la taille.
- **Copier `node_modules` ou `vendor` de ta machine.** Ils peuvent être incompatibles avec Linux et gonflent l'image.
- **Oublier `--chown`.** Un `chown -R` après coup double le poids de la couche.
- **Laisser l'image tourner en root.** Une faille dans l'application donne alors tous les droits dans le conteneur.
- **Utiliser Alpine pour un module natif incompatible `musl`.** Bascule sur `slim` si tu rencontres des erreurs obscures.
- **Mettre des secrets dans le Dockerfile.** Ils restent dans l'historique de l'image même si tu les supprimes ensuite.

## Bonnes pratiques

- Une base `alpine` ou `slim` avec une version fixée.
- Un build multi-étapes dès que l'application nécessite une compilation.
- Dépendances stables en haut du Dockerfile, code source en bas.
- Un `.dockerignore` maintenu avec le même soin que le `.gitignore`.
- `USER` non privilégié et `--chown` lors des copies.
- Analyse des vulnérabilités automatisée dans la CI.
- Un tag précis par version (`1.4.2`), jamais seulement `latest` en production.
- Un objectif chiffré de taille et de durée de build, suivi dans le temps.

## À retenir

- Mesure d'abord : `docker images`, `docker history`, `dive`.
- Choisis une base légère : `alpine`, `slim` ou `distroless`.
- Le **build multi-étapes** laisse les outils de compilation dans les étapes intermédiaires et ne garde que le résultat.
- Le cache fonctionne par couche : ordre stable puis changeant, nettoyage dans la même instruction `RUN`, caches montés BuildKit.
- `.dockerignore` réduit le contexte et protège les secrets.
- Une image de production tourne en **non root**, sans outils inutiles, avec des versions figées et scannée régulièrement.
