---
title: Docker Compose
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Dans le chapitre précédent, pour faire tourner DevRoad tu as tapé plusieurs longues commandes : créer un réseau, créer un volume, lancer MySQL avec cinq options, lancer l'application avec cinq autres. Un oubli, une faute de frappe, et rien ne marche. Pire : personne d'autre dans l'équipe ne sait exactement quelles commandes tu as tapées. **Docker Compose** résout ce problème en décrivant toute l'application dans un seul fichier, qui se lance avec une seule commande.

À la fin du chapitre, tu seras capable de :

- écrire un fichier `compose.yaml` décrivant plusieurs services ;
- configurer images, builds, ports, volumes, réseaux et variables d'environnement ;
- gérer l'ordre de démarrage avec `depends_on` et les **healthchecks** ;
- utiliser un fichier `.env` pour séparer la configuration du code ;
- maîtriser les commandes `docker compose up`, `down`, `logs`, `exec`, `ps` ;
- lancer une pile complète : application Laravel, MySQL, serveur web.

Prérequis : les chapitres « Dockerfile » et « Volumes et réseaux », et une lecture à l'aise du format YAML (indentation par deux espaces, listes avec des tirets). Prévois deux heures trente.

## Le principe : décrire plutôt que commander

Un fichier Compose est un document **YAML** qui déclare des **services**. Un service correspond à un conteneur (ou à plusieurs copies du même). Voici le strict minimum, un site Nginx :

```yaml
services:
  web:
    image: nginx:1.27
    ports:
      - "8080:80"
```

Enregistre-le sous le nom `compose.yaml` (le nom historique `docker-compose.yml` fonctionne aussi), puis lance :

```bash
docker compose up -d
```

Compose lit le fichier, crée un réseau dédié au projet, télécharge l'image si besoin et démarre le conteneur. Pour tout arrêter et nettoyer :

```bash
docker compose down
```

> **Astuce** : sur les installations récentes, la commande s'écrit `docker compose` (avec un espace). L'ancien outil `docker-compose` (avec un tiret) est obsolète.

Remarque ce que Compose fait pour toi sans que tu le demandes : il crée un **réseau par projet** sur lequel tous les services se retrouvent par leur nom, exactement comme au chapitre précédent. Le service `web` est joignable depuis les autres services avec le nom d'hôte `web`.

## Anatomie d'un service

Voici les clés que tu utiliseras tout le temps.

| Clé | Rôle |
| --- | --- |
| `image` | Image à télécharger (`mysql:8.0`) |
| `build` | Dossier contenant un Dockerfile à construire |
| `container_name` | Nom fixe du conteneur (optionnel) |
| `ports` | Ports publiés, au format `hôte:conteneur` |
| `environment` | Variables d'environnement |
| `env_file` | Fichier de variables à charger |
| `volumes` | Volumes et bind mounts |
| `depends_on` | Ordre de démarrage entre services |
| `networks` | Réseaux auxquels le service est connecté |
| `restart` | Politique de redémarrage |
| `healthcheck` | Test de bonne santé du service |

### image ou build ?

Pour un service standard (base de données, cache), on utilise une `image`. Pour ton propre code, on utilise `build` :

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
```

Compose construit l'image avec ton Dockerfile avant de démarrer le service. Pour reconstruire après un changement, ajoute `--build` : `docker compose up -d --build`.

### Ports, variables et volumes

```yaml
  db:
    image: mysql:8.0
    environment:
      MYSQL_ROOT_PASSWORD: secret
      MYSQL_DATABASE: devroad
    ports:
      - "3307:3306"
    volumes:
      - db-data:/var/lib/mysql

volumes:
  db-data:
```

Les volumes nommés doivent être déclarés dans une section `volumes:` au niveau racine du fichier. C'est l'équivalent de `docker volume create`. Le `-v db-data:/var/lib/mysql` du chapitre précédent devient simplement `- db-data:/var/lib/mysql`.

:::quiz
Quelle commande démarre en arrière-plan tous les services décrits dans le fichier Compose ?
- [ ] docker compose run
- [x] docker compose up -d
- [ ] docker compose start --all
- [ ] docker compose build -d
> docker compose up crée le réseau, les volumes et démarre les services ; l'option -d les lance en arrière-plan.
:::

## Le fichier .env : séparer configuration et code

Écrire `MYSQL_ROOT_PASSWORD: secret` en dur dans un fichier versionné est une mauvaise habitude. Compose lit automatiquement un fichier `.env` placé à côté de `compose.yaml` et permet d'utiliser ses valeurs avec la syntaxe `${NOM}` :

```text
# .env (non versionné !)
DB_DATABASE=devroad
DB_USERNAME=devroad
DB_PASSWORD=un-mot-de-passe-solide
DB_ROOT_PASSWORD=un-autre-mot-de-passe
APP_PORT=8000
```

```yaml
  db:
    image: mysql:8.0
    environment:
      MYSQL_DATABASE: ${DB_DATABASE}
      MYSQL_USER: ${DB_USERNAME}
      MYSQL_PASSWORD: ${DB_PASSWORD}
      MYSQL_ROOT_PASSWORD: ${DB_ROOT_PASSWORD}
```

Tu peux fournir une valeur par défaut : `${APP_PORT:-8000}` vaut 8000 si la variable n'est pas définie. Commite un fichier `.env.example` avec des valeurs factices pour documenter les variables attendues, et ajoute `.env` à ton `.gitignore`.

Pour vérifier le résultat final après substitution :

```bash
docker compose config
```

Cette commande affiche le fichier Compose complet, variables remplacées, et signale les erreurs de syntaxe. Utilise-la dès que quelque chose te surprend.

## L'ordre de démarrage : depends_on et healthcheck

Un classique : l'application démarre plus vite que MySQL et plante parce que la base n'est pas prête. `depends_on` règle l'ordre de **démarrage**, mais un conteneur « démarré » n'est pas forcément « prêt ». Il faut ajouter un **healthcheck** :

```yaml
  db:
    image: mysql:8.0
    healthcheck:
      test: ["CMD", "mysqladmin", "ping", "-h", "localhost", "-p${DB_ROOT_PASSWORD}"]
      interval: 5s
      timeout: 5s
      retries: 12
      start_period: 20s

  app:
    build: .
    depends_on:
      db:
        condition: service_healthy
```

Le service `db` exécute `mysqladmin ping` toutes les cinq secondes. Tant que la commande échoue, l'état est `starting`, puis `healthy` quand elle réussit. Grâce à `condition: service_healthy`, `app` n'est démarré qu'une fois MySQL réellement prêt. Consulte l'état avec `docker compose ps`.

:::quiz
Pourquoi ajouter un healthcheck sur MySQL en plus de depends_on ?
- [ ] Pour que MySQL consomme moins de mémoire
- [x] Parce que depends_on seul attend que le conteneur démarre, pas que le service soit prêt à accepter des connexions
- [ ] Parce que depends_on ne fonctionne qu'avec un healthcheck sur chaque service
- [ ] Pour chiffrer le mot de passe
> Un conteneur MySQL est démarré bien avant d'être prêt. Le healthcheck permet à Compose d'attendre le vrai état de santé avant de lancer les services dépendants.
:::

## Un exemple complet : DevRoad avec Compose

Voici une pile réaliste : PHP-FPM pour Laravel, Nginx comme serveur web, MySQL pour les données. À la racine du projet, crée un dossier `docker/nginx` avec le fichier `default.conf` :

```text
server {
    listen 80;
    root /var/www/html/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass app:9000;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

La ligne `fastcgi_pass app:9000` illustre la résolution de noms : Nginx contacte le service `app` sur son port interne 9000. Puis le fichier `compose.yaml` :

```yaml
services:
  app:
    build:
      context: .
      dockerfile: Dockerfile
    environment:
      DB_HOST: db
      DB_PORT: 3306
      DB_DATABASE: ${DB_DATABASE}
      DB_USERNAME: ${DB_USERNAME}
      DB_PASSWORD: ${DB_PASSWORD}
    volumes:
      - .:/var/www/html
    depends_on:
      db:
        condition: service_healthy
    restart: unless-stopped

  web:
    image: nginx:1.27-alpine
    ports:
      - "${APP_PORT:-8000}:80"
    volumes:
      - .:/var/www/html:ro
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

volumes:
  db-data:
```

Quelques remarques. Seul `web` publie un port : la base et PHP-FPM ne sont accessibles que depuis le réseau interne. La politique `restart: unless-stopped` relance les conteneurs après un crash ou un redémarrage de la machine. Le bind mount `.:/var/www/html` partage le code avec le conteneur : tu modifies un fichier, la page se met à jour.

Lance la pile et prépare l'application :

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
```

Ouvre `http://localhost:8000` : DevRoad répond.

## Les commandes du quotidien

| Commande | Effet |
| --- | --- |
| `docker compose up -d` | Crée et démarre les services en arrière-plan |
| `docker compose up -d --build` | Reconstruit les images avant de démarrer |
| `docker compose ps` | État des services |
| `docker compose logs -f app` | Suit les logs du service `app` |
| `docker compose exec app bash` | Ouvre un shell dans un service en cours |
| `docker compose run --rm app php artisan test` | Lance une commande ponctuelle dans un nouveau conteneur |
| `docker compose stop` / `start` | Arrête / redémarre sans supprimer |
| `docker compose down` | Supprime conteneurs et réseau, conserve les volumes |
| `docker compose down -v` | Supprime aussi les volumes (données perdues) |

> **Attention** : `docker compose down -v` efface les volumes, donc ta base de données. Réserve-le aux moments où tu veux repartir de zéro.

La différence entre `exec` et `run` est importante : `exec` agit dans un conteneur **déjà en marche**, `run` crée un **nouveau** conteneur temporaire pour exécuter une commande.

## Atelier guidé : DevRoad en une commande

Compte une heure trente. Pars de ton projet Laravel, ou d'un projet vierge créé avec `composer create-project laravel/laravel devroad-docker`.

1. Ajoute à la racine un `Dockerfile` PHP-FPM inspiré du chapitre précédent, avec les extensions `pdo_mysql` et `zip`.
2. Crée `docker/nginx/default.conf` avec la configuration ci-dessus.
3. Crée un fichier `.env.docker` à la racine contenant les variables `DB_DATABASE`, `DB_USERNAME`, `DB_PASSWORD`, `DB_ROOT_PASSWORD` et `APP_PORT`, puis copie-le en `.env` pour Compose. Ajoute-le à `.gitignore` et commite un `.env.example` factice.
4. Écris le `compose.yaml` avec les trois services `app`, `web` et `db` et le volume `db-data`.
5. Valide le fichier avec `docker compose config`.
6. Lance `docker compose up -d --build`, puis `docker compose ps` : attends que `db` soit `healthy`.
7. Exécute `composer install`, `key:generate` et `migrate --seed` avec `docker compose exec`.
8. Ouvre `http://localhost:8000` et vérifie que la page s'affiche.
9. Lance `docker compose down`, puis `docker compose up -d` : les données sont-elles conservées ? Pourquoi ?
10. Termine par `docker compose logs --tail 20 web` pour lire les accès Nginx.

Pour t'auto-évaluer : explique pourquoi seul le service `web` publie un port, et ce qui se passerait au démarrage sans healthcheck sur `db`.

## Erreurs fréquentes

- **Mauvaise indentation YAML.** Une erreur d'un espace casse le fichier ; utilise `docker compose config` pour la repérer.
- **Utiliser des tabulations dans le YAML.** Seuls les espaces sont autorisés.
- **Mettre `DB_HOST=localhost`.** Dans Compose, l'hôte de la base est le **nom du service**, ici `db`.
- **Oublier de déclarer le volume dans la section racine `volumes:`.** Compose signale une erreur.
- **Croire que `depends_on` attend que le service soit prêt.** Ajoute un healthcheck avec `condition: service_healthy`.
- **Commiter le fichier `.env`.** Les mots de passe se retrouvent dans l'historique Git.
- **Utiliser `down -v` par réflexe.** Les données de la base sont détruites.
- **Modifier le Dockerfile sans `--build`.** Compose réutilise l'ancienne image.

## Bonnes pratiques

- Un seul `compose.yaml` versionné, accompagné d'un `.env.example` documenté.
- Ne publie que les ports nécessaires ; garde la base de données sur le réseau interne en production.
- Ajoute un `healthcheck` aux services dont d'autres dépendent (base, cache).
- Fixe les versions d'images (`mysql:8.0`, `nginx:1.27-alpine`).
- Utilise `restart: unless-stopped` pour les services qui doivent rester actifs.
- Vérifie le résultat avec `docker compose config` avant de lancer.
- Documente dans le README la commande de démarrage du projet : idéalement, `docker compose up -d` suffit.

## À retenir

- **Docker Compose** décrit une application multi-conteneurs dans un fichier `compose.yaml`.
- Un **service** correspond à un conteneur ; Compose crée un **réseau** où les services se joignent par leur nom.
- Les clés principales : `image`, `build`, `ports`, `environment`, `volumes`, `depends_on`, `healthcheck`, `restart`.
- Le fichier **`.env`** sépare la configuration sensible du code ; ne le commite pas.
- `depends_on` avec `condition: service_healthy` garantit que la base est prête avant l'application.
- `up -d`, `down`, `ps`, `logs`, `exec` et `config` sont les commandes de tous les jours.
- `down -v` supprime aussi les volumes : prudence avec tes données.
