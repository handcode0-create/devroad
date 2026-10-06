<?php

return [
    'lessons' => [
        'Comprendre les conteneurs' => [
            'description' => 'Découvre ce qu’est un conteneur, en quoi il diffère d’une machine virtuelle, et le rôle des images et des registries. Tu lances tes premiers conteneurs.',
            'objective' => 'Expliquer la différence entre image et conteneur, puis télécharger une image, lancer, lister, arrêter et supprimer des conteneurs en ligne de commande.',
            'content' => <<<'MD'
## Pourquoi cette notion

Tu connais sûrement la phrase « chez moi, ça marche ». Ton application tourne sur ton ordinateur, mais elle plante sur le serveur du client parce que la version de PHP, de Node.js ou de la base de données n’est pas la même. Docker résout ce problème en empaquetant l’application avec tout son environnement dans une unité standard qui s’exécute de la même façon partout.

Docker est aujourd’hui un outil quotidien pour déployer des applications, monter un environnement de développement en quelques minutes et faire travailler une équipe sur des bases identiques.

## Les concepts clés

### Le conteneur

Un conteneur est un processus isolé qui s’exécute avec son propre système de fichiers, ses propres processus et son propre réseau, tout en partageant le noyau du système hôte. Il démarre en quelques secondes et consomme peu de ressources.

### Conteneur ou machine virtuelle

Une machine virtuelle embarque un système d’exploitation complet par-dessus un hyperviseur : elle est lourde et lente à démarrer. Un conteneur partage le noyau de l’hôte et n’embarque que l’application et ses dépendances : il est léger et rapide. L’isolation d’une machine virtuelle est plus forte, celle d’un conteneur est suffisante dans la grande majorité des cas.

### Image et conteneur

Une image est un modèle en lecture seule, construit en couches, qui contient le code, les bibliothèques et la configuration. Un conteneur est une instance en exécution d’une image. Tu peux créer dix conteneurs à partir de la même image, comme dix pièces issues du même moule.

### Registry et tags

Une registry est un entrepôt d’images. Docker Hub est la plus connue. Une image se désigne par un nom et un tag, par exemple « nginx:alpine », où le tag précise la variante ou la version. Sans tag, Docker utilise « latest », ce qui est peu prévisible.

### Les commandes de base

La commande « docker pull » télécharge une image. La commande « docker run » crée et lance un conteneur. La commande « docker ps » liste les conteneurs en cours, et « docker ps -a » liste aussi les arrêtés. Enfin « docker stop » arrête, « docker rm » supprime un conteneur, et « docker rmi » supprime une image.

## Exemple pas à pas

Le code d’exemple vérifie d’abord l’installation avec « docker version » puis lance le conteneur « hello-world ». À l’étape 2, tu télécharges l’image « nginx:alpine », un petit serveur web. À l’étape 3, tu le lances en arrière-plan avec « -d », tu nommes le conteneur avec « --name » et tu publies le port avec « -p 8080:80 » : le port 8080 de ta machine renvoie vers le port 80 du conteneur.

À l’étape 4, tu ouvres la page dans le navigateur ou avec « curl ». À l’étape 5, tu consultes les logs et la liste des conteneurs. À l’étape 6, tu arrêtes et supprimes le conteneur, puis tu nettoies l’image si besoin.

## Erreurs fréquentes

- Confondre image et conteneur : tu cherches à modifier l’image en cours d’exécution. Rappelle-toi que le conteneur est une instance, et que l’image reste intacte.
- Oublier « -p » : le service tourne mais reste inaccessible depuis ta machine. Publie le port avec « -p port_hôte:port_conteneur ».
- Laisser s’accumuler les conteneurs arrêtés : le disque se remplit. Utilise « --rm » ou nettoie avec « docker container prune ».
- Utiliser le tag « latest » en production : la version peut changer sans prévenir. Précise un tag explicite.
- Croire que les données d’un conteneur sont permanentes : elles disparaissent avec lui. Utilise des volumes, vus dans une prochaine leçon.
- Avoir un port déjà utilisé sur l’hôte : le lancement échoue. Choisis un autre port hôte ou arrête le programme concerné.

## Bonnes pratiques

- Donne un nom à tes conteneurs avec « --name » pour les retrouver facilement.
- Utilise « --rm » pour les conteneurs de test temporaires.
- Choisis des images officielles et des tags précis.
- Consulte les logs avec « docker logs » avant de chercher ailleurs.
- Nettoie régulièrement les ressources inutilisées.

## Auto-évaluation

- Quelle différence y a-t-il entre une image et un conteneur ?
- En quoi un conteneur diffère-t-il d’une machine virtuelle ?
- Que signifie « -p 8080:80 » ?
- Où sont stockées les images que tu télécharges ?
- Que deviennent les données d’un conteneur supprimé ?

## À retenir

- Un conteneur est un processus isolé, léger et rapide.
- Une image est un modèle immuable, un conteneur en est une instance.
- Une registry stocke et distribue des images.
- « docker run » crée et lance, « docker ps » liste.
- Sans volume, les données disparaissent avec le conteneur.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : vérifier l'installation et lancer un premier conteneur de test
docker version
docker run --rm hello-world     # --rm : supprimé automatiquement à la fin

# Étape 2 : télécharger une image depuis Docker Hub (nginx, version alpine)
docker pull nginx:alpine
docker images                   # liste les images locales

# Étape 3 : lancer un conteneur en arrière-plan
#   -d      : détaché (en arrière-plan)
#   --name  : nom lisible
#   -p      : port 8080 de ma machine => port 80 du conteneur
docker run -d --name vitrine -p 8080:80 nginx:alpine

# Étape 4 : tester que le serveur web répond
curl http://localhost:8080

# Étape 5 : observer ce qui tourne
docker ps                       # conteneurs en cours
docker logs vitrine             # journaux du conteneur
docker ps -a                    # inclut les conteneurs arrêtés

# Étape 6 : arrêter, supprimer le conteneur, puis l'image si besoin
docker stop vitrine
docker rm vitrine
docker rmi nginx:alpine
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Lancer un serveur web conteneurisé',
            'exercise_description' => <<<'MD'
Lance deux conteneurs nginx à partir de la même image pour illustrer la différence entre image et conteneur, puis nettoie tout.

Critères de réussite :
- L’image « nginx:alpine » est présente localement (visible avec « docker images »).
- Deux conteneurs nommés « site-a » et « site-b » tournent en même temps, sur les ports hôte 8081 et 8082.
- Les deux répondent à « curl » sur leur port respectif.
- La commande « docker ps » affiche les deux conteneurs.
- Après nettoyage, « docker ps -a » ne montre plus aucun des deux conteneurs.
MD,
            'exercise_hint' => 'Utilise deux fois « docker run -d » avec des noms et des ports hôte différents mais le même port conteneur 80.',
            'exercise_solution' => <<<'CODE'
docker pull nginx:alpine
docker images

# Deux conteneurs issus de la MÊME image
docker run -d --name site-a -p 8081:80 nginx:alpine
docker run -d --name site-b -p 8082:80 nginx:alpine

# Vérifications
curl http://localhost:8081
curl http://localhost:8082
docker ps

# Nettoyage
docker stop site-a site-b
docker rm site-a site-b
docker ps -a
CODE,
        ],

        'Dockerfile' => [
            'description' => 'Apprends à écrire un Dockerfile pour empaqueter ta propre application dans une image, avec les instructions essentielles et le fonctionnement du cache.',
            'objective' => 'Écrire un Dockerfile pour une petite application Node.js, construire l’image et lancer un conteneur qui répond sur un port publié.',
            'content' => <<<'MD'
## Pourquoi cette notion

Utiliser des images existantes, c’est bien, mais ton application à toi n’existe sur aucun registry. Pour la déployer chez un client ou la faire tourner sur un serveur, tu dois la transformer en image. Le Dockerfile est la recette qui décrit cette transformation, étape par étape. Il se versionne avec ton code, ce qui rend la construction reproductible par n’importe qui.

C’est le fichier que tu écriras le plus souvent lorsque tu conteneuriseras tes projets.

## Les concepts clés

### Les instructions essentielles

Chaque ligne d’un Dockerfile est une instruction. L’instruction « FROM » choisit l’image de base, par exemple une image Node.js légère. « WORKDIR » définit le dossier de travail dans l’image. « COPY » copie des fichiers de ta machine vers l’image. « RUN » exécute une commande pendant la construction, comme l’installation des dépendances. « EXPOSE » documente le port d’écoute. « CMD » définit la commande lancée au démarrage du conteneur.

### Les couches et le cache

Chaque instruction crée une couche. Docker met ces couches en cache et ne reconstruit que celles qui ont changé, ainsi que toutes celles qui les suivent. D’où une règle d’or : place d’abord ce qui change rarement, comme le fichier des dépendances, puis ce qui change souvent, comme ton code source. De cette façon, une modification du code ne relance pas l’installation des dépendances.

### Le fichier dockerignore

Le fichier « .dockerignore » liste ce qu’il ne faut pas envoyer à Docker lors de la construction : « node_modules », « .git », « .env ». Il accélère la construction et évite d’embarquer des secrets ou des fichiers inutiles.

### CMD et ENTRYPOINT

« CMD » fournit la commande par défaut, que l’on peut remplacer au lancement. « ENTRYPOINT » fixe l’exécutable principal. Pour débuter, « CMD » suffit. Utilise la forme tableau, par exemple « ["node", "server.js"] », qui évite de passer par un shell.

### Construire et lancer

La commande « docker build -t nom:tag . » construit l’image à partir du dossier courant, désigné par le point final. Ensuite, « docker run » lance un conteneur.

## Exemple pas à pas

Le code d’exemple contient trois fichiers pour un petit serveur HTTP. Le premier est le serveur « server.js », qui répond avec un message. Le deuxième est le « Dockerfile », construit dans l’ordre idéal : image de base, dossier de travail, copie de « package.json » seul, installation, puis copie du reste du code.

Le troisième est le « .dockerignore ». Ensuite, les commandes de l’étape finale construisent l’image avec un tag, lancent un conteneur en publiant le port 3000 et testent la réponse avec « curl ». Reconstruire après une modification de « server.js » montre le cache en action : l’étape d’installation est réutilisée.

## Erreurs fréquentes

- Copier tout le code avant d’installer les dépendances : chaque modification relance l’installation. Copie d’abord « package.json », installe, puis copie le reste.
- Oublier le « .dockerignore » : « node_modules » local écrase celui de l’image ou la construction est lente. Ajoute-le avec « node_modules » et « .git ».
- Oublier le point final de « docker build » : la commande échoue car le contexte manque. Termine par « . ».
- Croire que « EXPOSE » publie le port : il ne fait que documenter. Publie le port avec « -p » au lancement.
- Écouter sur « localhost » à l’intérieur du conteneur : le service reste injoignable de l’extérieur. Écoute sur « 0.0.0.0 ».
- Mettre des secrets dans le Dockerfile : ils restent dans les couches de l’image. Passe-les à l’exécution par variables d’environnement.

## Bonnes pratiques

- Choisis une image de base officielle, légère et avec un tag précis.
- Ordonne les instructions du moins changeant au plus changeant.
- Utilise un « .dockerignore » dès le début.
- Une image doit faire une chose : un processus principal par conteneur.
- Donne des tags explicites à tes images, comme « boutique:1.0 ».

## Auto-évaluation

- À quoi servent FROM, WORKDIR, COPY, RUN et CMD ?
- Pourquoi copier d’abord le fichier des dépendances ?
- Que fait le cache de construction ?
- À quoi sert le fichier « .dockerignore » ?
- Pourquoi un serveur dans un conteneur doit-il écouter sur « 0.0.0.0 » ?

## À retenir

- Un Dockerfile est la recette de construction d’une image.
- Chaque instruction crée une couche mise en cache.
- Place les éléments stables en premier pour profiter du cache.
- Le « .dockerignore » accélère et sécurise la construction.
- « EXPOSE » documente, « -p » publie réellement le port.
MD,
            'code_example' => <<<'CODE'
# ===== Fichier : server.js =====
# import http from "node:http";
# // Serveur minimal : écoute sur 0.0.0.0 pour être joignable hors du conteneur
# http.createServer((req, res) => res.end("Boutique en ligne : OK\n"))
#   .listen(3000, "0.0.0.0");

# ===== Fichier : .dockerignore =====
# node_modules
# .git
# .env

# ===== Fichier : Dockerfile =====

# Image de base officielle et légère (tag précis)
FROM node:22-alpine

# Dossier de travail dans l'image
WORKDIR /app

# 1. Copier d'abord uniquement les fichiers de dépendances (rarement modifiés)
COPY package*.json ./

# 2. Installer les dépendances : cette couche reste en cache tant que
#    package.json ne change pas
RUN npm ci --omit=dev

# 3. Copier ensuite le code source (modifié souvent)
COPY . .

# Documenter le port d'écoute (ne le publie pas)
EXPOSE 3000

# Commande lancée au démarrage du conteneur (forme tableau, sans shell)
CMD ["node", "server.js"]

# ===== Commandes (dans le terminal) =====
# docker build -t boutique:1.0 .
# docker run -d --name boutique -p 3000:3000 boutique:1.0
# curl http://localhost:3000
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Conteneuriser ta propre application',
            'exercise_description' => <<<'MD'
Écris un Dockerfile pour une petite application Node.js « api-menu » qui répond le texte du menu du jour, construis l’image et lance-la.

Critères de réussite :
- Le Dockerfile part d’une image « node » avec un tag précis et définit un WORKDIR.
- Le fichier « package.json » est copié et les dépendances installées avant la copie du reste du code.
- Un fichier « .dockerignore » exclut au moins « node_modules » et « .git ».
- L’image est construite avec le tag « api-menu:1.0 ».
- Un conteneur lancé avec « -p 3000:3000 » répond au « curl http://localhost:3000 ».
MD,
            'exercise_hint' => 'Ton serveur doit écouter sur 0.0.0.0, pas seulement sur localhost. Crée un package.json avec « npm init -y » pour que « npm ci » ait un fichier de verrouillage, en lançant d’abord « npm install ».',
            'exercise_solution' => <<<'CODE'
# server.js
# import http from "node:http";
# http.createServer((req, res) => res.end("Menu du jour : garba et alloco\n"))
#   .listen(3000, "0.0.0.0");
#
# Préparation : npm init -y && npm pkg set type=module && npm install

# ===== .dockerignore =====
node_modules
.git
.env

# ===== Dockerfile =====
FROM node:22-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY . .
EXPOSE 3000
CMD ["node", "server.js"]

# ===== Commandes =====
# docker build -t api-menu:1.0 .
# docker run -d --name api-menu -p 3000:3000 api-menu:1.0
# curl http://localhost:3000
# docker rm -f api-menu
CODE,
        ],

        'Volumes et réseaux' => [
            'description' => 'Fais persister les données de tes conteneurs avec les volumes et fais communiquer plusieurs conteneurs grâce aux réseaux Docker.',
            'objective' => 'Créer un volume pour conserver les données d’une base, un réseau dédié, et faire dialoguer deux conteneurs par leur nom.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un conteneur est éphémère : quand tu le supprimes, tout ce qu’il contenait disparaît. Pour un serveur web sans état, ce n’est pas grave. Pour une base de données qui contient les commandes d’une boutique, c’est une catastrophe. Les volumes règlent ce problème en stockant les données en dehors du cycle de vie du conteneur.

Dans une vraie application, plusieurs conteneurs doivent aussi se parler, par exemple l’API et sa base de données. Les réseaux Docker permettent cette communication de façon propre et sécurisée.

## Les concepts clés

### Les volumes nommés

Un volume nommé est un espace de stockage géré par Docker, indépendant des conteneurs. Tu le crées avec « docker volume create nom », puis tu le montes dans un conteneur avec l’option « -v nom:/chemin ». Si le conteneur est supprimé puis recréé avec le même volume, les données sont retrouvées. C’est la solution recommandée pour les données persistantes comme celles d’une base.

### Les bind mounts

Un bind mount relie un dossier précis de ta machine à un dossier du conteneur, avec la forme « -v /chemin/hôte:/chemin/conteneur ». Il est pratique en développement, car les modifications de tes fichiers apparaissent immédiatement dans le conteneur. Il dépend toutefois de la structure de ta machine, donc il est moins portable.

### Les réseaux

Docker crée par défaut un réseau pour les conteneurs. Sur un réseau défini par l’utilisateur, créé avec « docker network create nom », les conteneurs se retrouvent par leur nom, grâce à un DNS interne. L’API peut donc joindre la base à l’adresse « db » au lieu d’une adresse IP qui change.

### Publier ou non un port

Seul ce qui doit être accessible depuis l’extérieur est publié avec « -p ». Une base de données, qui ne sert qu’à l’API, n’a pas besoin d’être publiée : elle reste joignable sur le réseau interne uniquement, ce qui réduit la surface d’attaque.

### Les variables d’environnement

L’option « -e » transmet une variable à un conteneur. Les images de bases de données l’utilisent pour définir le mot de passe ou le nom de la base à la première initialisation.

## Exemple pas à pas

Le code d’exemple crée une base PostgreSQL persistante et un conteneur qui lui parle. À l’étape 1, tu crées le volume et le réseau. À l’étape 2, tu lances la base en montant le volume sur le dossier de données de PostgreSQL, en la rattachant au réseau et en lui passant ses variables d’environnement.

À l’étape 3, tu crées une table et une ligne avec un client « psql » lancé dans un second conteneur sur le même réseau, en utilisant le nom « db » comme hôte. À l’étape 4, tu supprimes la base et tu la relances avec le même volume. À l’étape 5, tu constates que la donnée est toujours là. À l’étape 6, tu inspectes et tu nettoies.

## Erreurs fréquentes

- Stocker une base de données sans volume : tout est perdu à la suppression. Monte un volume sur le dossier de données.
- Utiliser une adresse IP pour joindre un autre conteneur : elle change au redémarrage. Utilise le nom du conteneur sur un réseau dédié.
- Placer les conteneurs sur des réseaux différents : ils ne se voient pas. Rattache-les au même réseau avec « --network ».
- Publier le port de la base sur internet : elle devient exposée. Ne publie que ce qui est nécessaire.
- Mettre le mot de passe en dur dans les commandes partagées : il fuit dans l’historique. Passe-le par variable d’environnement ou par un fichier ignoré.
- Supprimer un volume sans réfléchir : les données partent définitivement. Vérifie avec « docker volume ls » et sauvegarde avant.

## Bonnes pratiques

- Utilise des volumes nommés pour les données persistantes.
- Crée un réseau par application pour isoler les services.
- Ne publie que les ports nécessaires.
- Garde les mots de passe hors du dépôt.
- Sauvegarde régulièrement le contenu des volumes importants.

## Auto-évaluation

- Pourquoi les données d’un conteneur disparaissent-elles à sa suppression ?
- Quelle différence y a-t-il entre un volume nommé et un bind mount ?
- Comment deux conteneurs se retrouvent-ils par leur nom ?
- Pourquoi ne pas publier le port d’une base de données ?
- Que fait l’option « -e » ?

## À retenir

- Un volume conserve les données au-delà de la vie du conteneur.
- Les bind mounts conviennent au développement.
- Un réseau défini par l’utilisateur offre la résolution par nom.
- Publie le minimum de ports.
- Les variables d’environnement configurent les conteneurs.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : créer un volume persistant et un réseau dédié à l'application
docker volume create donnees-boutique
docker network create reseau-boutique

# Étape 2 : lancer PostgreSQL
#   -v  : le volume est monté sur le dossier de données de PostgreSQL
#   --network : rattache le conteneur au réseau (joignable par le nom « db »)
#   -e  : variables d'initialisation (mot de passe à ne pas versionner)
#   (aucun -p : la base n'est pas exposée en dehors du réseau Docker)
docker run -d --name db \
  --network reseau-boutique \
  -v donnees-boutique:/var/lib/postgresql/data \
  -e POSTGRES_PASSWORD=secret_local \
  -e POSTGRES_DB=boutique \
  postgres:16-alpine

# Étape 3 : un second conteneur (client) joint la base par son NOM « db »
sleep 5   # laisser PostgreSQL démarrer
docker run --rm --network reseau-boutique -e PGPASSWORD=secret_local \
  postgres:16-alpine psql -h db -U postgres -d boutique \
  -c "CREATE TABLE produits (nom text, prix int);" \
  -c "INSERT INTO produits VALUES ('Pagne wax', 5000);"

# Étape 4 : supprimer le conteneur de la base, puis le recréer AVEC le volume
docker rm -f db
docker run -d --name db --network reseau-boutique \
  -v donnees-boutique:/var/lib/postgresql/data \
  -e POSTGRES_PASSWORD=secret_local postgres:16-alpine

# Étape 5 : la donnée est toujours présente
sleep 5
docker run --rm --network reseau-boutique -e PGPASSWORD=secret_local \
  postgres:16-alpine psql -h db -U postgres -d boutique -c "SELECT * FROM produits;"

# Étape 6 : inspecter puis nettoyer
docker volume ls
docker network inspect reseau-boutique
docker rm -f db && docker network rm reseau-boutique
# docker volume rm donnees-boutique   # supprime DÉFINITIVEMENT les données
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Une base de données qui survit',
            'exercise_description' => <<<'MD'
Prouve que les données d’une base survivent à la suppression de son conteneur grâce à un volume, et que deux conteneurs se parlent par leur nom sur un réseau.

Critères de réussite :
- Un volume « donnees-clients » et un réseau « reseau-clients » sont créés.
- Un conteneur « db » PostgreSQL utilise ce volume et ce réseau, sans publier de port.
- Une table « clients » contenant une ligne est créée depuis un second conteneur utilisant l’hôte « db ».
- Après « docker rm -f db » puis une nouvelle création avec le même volume, la requête « SELECT » retourne toujours la ligne.
- Le nettoyage supprime le conteneur, le réseau et le volume.
MD,
            'exercise_hint' => 'Laisse quelques secondes à PostgreSQL pour démarrer avant la première requête. Le dossier de données à monter est /var/lib/postgresql/data.',
            'exercise_solution' => <<<'CODE'
docker volume create donnees-clients
docker network create reseau-clients

docker run -d --name db --network reseau-clients \
  -v donnees-clients:/var/lib/postgresql/data \
  -e POSTGRES_PASSWORD=secret_local -e POSTGRES_DB=clients \
  postgres:16-alpine
sleep 5

docker run --rm --network reseau-clients -e PGPASSWORD=secret_local \
  postgres:16-alpine psql -h db -U postgres -d clients \
  -c "CREATE TABLE clients (nom text);" \
  -c "INSERT INTO clients VALUES ('Mme Yao');"

# Suppression du conteneur puis recréation avec le même volume
docker rm -f db
docker run -d --name db --network reseau-clients \
  -v donnees-clients:/var/lib/postgresql/data \
  -e POSTGRES_PASSWORD=secret_local postgres:16-alpine
sleep 5

docker run --rm --network reseau-clients -e PGPASSWORD=secret_local \
  postgres:16-alpine psql -h db -U postgres -d clients -c "SELECT * FROM clients;"

# Nettoyage
docker rm -f db
docker network rm reseau-clients
docker volume rm donnees-clients
CODE,
        ],

        'Docker Compose' => [
            'description' => 'Décris toute une stack (application, base de données, volumes, réseau) dans un seul fichier compose.yaml et lance-la avec une commande.',
            'objective' => 'Écrire un fichier compose.yaml avec une application et sa base, puis démarrer, observer et arrêter la stack avec docker compose.',
            'content' => <<<'MD'
## Pourquoi cette notion

À la leçon précédente, tu as lancé une base et son réseau avec une longue suite de commandes. Imagine devoir les retaper à chaque démarrage, ou les expliquer à un collègue. Docker Compose résout ce problème : tu décris l’ensemble de ton application dans un fichier, et une seule commande démarre tout.

C’est l’outil de référence pour les environnements de développement locaux et pour de petits déploiements. Tout nouveau développeur d’une équipe lance la stack du projet avec « docker compose up ».

## Les concepts clés

### Le fichier compose.yaml

Ce fichier YAML décrit les services de l’application. Un service correspond à un conteneur : par exemple « app » pour ton code, « db » pour la base. Chaque service précise son image ou son contexte de construction, ses ports, ses variables d’environnement, ses volumes et ses dépendances. Les clés principales sont « services », « volumes » et « networks ».

### Les clés d’un service

La clé « image » désigne une image à télécharger, la clé « build » indique un dossier contenant un Dockerfile. La clé « ports » publie des ports. La clé « environment » définit des variables. La clé « volumes » monte des volumes. La clé « depends_on » exprime un ordre de démarrage. La clé « restart » définit la politique de redémarrage.

### Réseau automatique

Compose crée automatiquement un réseau pour ta stack. Chaque service y est joignable par son nom : l’application peut donc se connecter à la base avec l’hôte « db ».

### Attendre qu’un service soit prêt

« depends_on » seul garantit l’ordre de démarrage, mais pas que la base soit prête à accepter des connexions. Pour attendre réellement, on définit un « healthcheck » sur la base et on utilise « depends_on » avec la condition « service_healthy ».

### Variables et fichier env

Pour éviter les mots de passe en dur dans le fichier, Compose lit un fichier « .env » placé à côté, et tu y fais référence avec la forme « ${NOM} ». Ce fichier ne doit pas être versionné.

### Les commandes

La commande « docker compose up -d » construit si besoin et démarre en arrière-plan. La commande « docker compose ps » liste les services. La commande « docker compose logs -f » suit les journaux. La commande « docker compose down » arrête et supprime les conteneurs, et l’option « -v » supprime aussi les volumes.

## Exemple pas à pas

Le code d’exemple décrit une application et sa base PostgreSQL. Le service « db » utilise l’image PostgreSQL, un volume nommé pour la persistance, des variables lues depuis « .env » et un contrôle de santé. Le service « app » se construit depuis le Dockerfile du dossier courant, publie le port 3000, reçoit l’adresse de la base et attend que « db » soit en bonne santé.

La fin du fichier déclare le volume. Les commandes finales démarrent la stack, la listent, suivent les logs puis l’arrêtent proprement.

## Erreurs fréquentes

- Se tromper d’indentation YAML : Compose refuse le fichier. Utilise deux espaces, jamais de tabulations.
- Croire que « depends_on » attend que la base soit prête : l’application échoue à la connexion. Ajoute un « healthcheck » et la condition « service_healthy ».
- Écrire les mots de passe dans le fichier versionné : ils fuitent. Utilise un fichier « .env » ignoré par Git.
- Utiliser « docker compose down -v » par réflexe : les données de la base disparaissent. N’ajoute « -v » que si tu veux repartir de zéro.
- Utiliser « localhost » pour joindre la base depuis l’application : cela désigne le conteneur de l’application lui-même. Utilise le nom du service.
- Oublier de reconstruire après un changement de code : l’ancienne image tourne. Lance « docker compose up -d --build ».

## Bonnes pratiques

- Un fichier « compose.yaml » à la racine de chaque projet.
- Nomme clairement les services et utilise des volumes nommés.
- Ajoute des « healthcheck » aux bases de données.
- Documente les variables dans un « .env.example ».
- Ne publie que les ports utiles.

## Auto-évaluation

- Quel problème Docker Compose résout-il ?
- Qu’est-ce qu’un service dans un fichier Compose ?
- Comment l’application joint-elle la base de données ?
- Pourquoi « depends_on » ne suffit-il pas toujours ?
- Que fait « docker compose down -v » de plus que « down » ?

## À retenir

- Compose décrit toute la stack dans un seul fichier.
- Un service correspond à un conteneur.
- Les services se joignent par leur nom sur le réseau créé automatiquement.
- Un healthcheck garantit qu’un service est prêt.
- « down -v » supprime aussi les volumes, donc les données.
MD,
            'code_example' => <<<'CODE'
# Fichier : compose.yaml
# Stack locale : application web + base PostgreSQL

services:
  # --- Base de données ---
  db:
    image: postgres:16-alpine
    restart: unless-stopped
    environment:
      # Valeurs lues depuis le fichier .env (non versionné)
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: ${DB_NAME}
    volumes:
      # Volume nommé : les données survivent à « docker compose down »
      - donnees-db:/var/lib/postgresql/data
    healthcheck:
      # La base est « prête » quand pg_isready répond
      test: ["CMD-SHELL", "pg_isready -U ${DB_USER} -d ${DB_NAME}"]
      interval: 5s
      timeout: 3s
      retries: 10

  # --- Application ---
  app:
    build: .                # utilise le Dockerfile du dossier courant
    restart: unless-stopped
    ports:
      - "3000:3000"         # seul port publié vers l'extérieur
    environment:
      # Le nom du service « db » sert d'hôte sur le réseau Compose
      DATABASE_URL: postgres://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}
    depends_on:
      db:
        condition: service_healthy   # attend que la base soit réellement prête

volumes:
  donnees-db:

# --- Fichier .env (à ne pas versionner) ---
# DB_USER=boutique
# DB_PASSWORD=mot_de_passe_local
# DB_NAME=boutique

# --- Commandes ---
# docker compose up -d --build   # construire et démarrer
# docker compose ps              # état des services
# docker compose logs -f app     # suivre les logs de l'application
# docker compose down            # arrêter (les données sont conservées)
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Ta première stack Compose',
            'exercise_description' => <<<'MD'
Écris un fichier « compose.yaml » pour une stack « suivi-ventes » composée d’une base PostgreSQL et d’une interface d’administration de base de données, puis démarre-la.

Critères de réussite :
- Le fichier définit deux services : « db » (image postgres) et « admin » (image adminer).
- La base utilise un volume nommé et ses identifiants viennent d’un fichier « .env » (non versionné).
- Le service « db » possède un « healthcheck » et « admin » attend qu’il soit sain.
- Seul le port 8080 de « admin » est publié.
- « docker compose up -d » démarre la stack, « docker compose ps » affiche les deux services, et « docker compose down » l’arrête sans supprimer le volume.
MD,
            'exercise_hint' => 'Adminer écoute sur le port 8080 dans son conteneur. Dans l’interface, utilise « db » comme serveur. Teste que le volume reste après « down » avec « docker volume ls ».',
            'exercise_solution' => <<<'CODE'
# Fichier : .env
# DB_USER=ventes
# DB_PASSWORD=mot_de_passe_local
# DB_NAME=ventes

# Fichier : compose.yaml
services:
  db:
    image: postgres:16-alpine
    environment:
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: ${DB_NAME}
    volumes:
      - donnees-ventes:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USER} -d ${DB_NAME}"]
      interval: 5s
      timeout: 3s
      retries: 10

  admin:
    image: adminer
    ports:
      - "8080:8080"
    depends_on:
      db:
        condition: service_healthy

volumes:
  donnees-ventes:

# Commandes :
# docker compose up -d
# docker compose ps
# docker compose down
# docker volume ls   # le volume est toujours présent
CODE,
        ],

        'Optimiser les images' => [
            'description' => 'Réduis la taille et améliore la sécurité de tes images grâce au cache, aux images légères, aux builds multi-stage et à un utilisateur non administrateur.',
            'objective' => 'Transformer un Dockerfile simple en Dockerfile multi-stage plus léger, qui n’embarque ni outils de compilation ni secrets et s’exécute sans droits root.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une image lourde est lente à construire, lente à télécharger et coûteuse à stocker. Sur un serveur avec une connexion limitée, chaque déploiement qui télécharge un gros volume de données devient un vrai frein. Une image trop riche contient aussi plus d’outils qu’un attaquant peut exploiter.

Optimiser tes images, c’est gagner du temps à chaque déploiement et réduire la surface d’attaque. Ce sont deux critères qu’un employeur remarque tout de suite.

## Les concepts clés

### Choisir une base légère

Les images de base ont des variantes. Les variantes « alpine » ou « slim » sont bien plus petites que les variantes complètes parce qu’elles contiennent moins d’outils. Pense à vérifier la compatibilité de tes dépendances avant de changer de variante.

### Le cache de construction

Docker réutilise les couches inchangées. Pour en profiter, ordonne les instructions du moins changeant au plus changeant, comme tu l’as vu dans la leçon sur le Dockerfile. Regrouper des commandes avec « && » dans une même instruction « RUN » limite aussi le nombre de couches.

### Le build multi-stage

Pour construire une application, il faut souvent des outils lourds : compilateur, gestionnaire de paquets, dépendances de développement. Ils sont inutiles à l’exécution. Un build multi-stage utilise plusieurs instructions « FROM » dans le même Dockerfile. La première étape, nommée avec « AS », compile le projet. La dernière étape part d’une image minimale et récupère uniquement le résultat avec « COPY --from=nom ». L’image finale ne contient que ce qui est nécessaire pour tourner.

### Sécurité de l’image

Par défaut, un conteneur s’exécute en administrateur. L’instruction « USER » permet de lancer l’application avec un utilisateur sans privilèges. Aucun secret ne doit être copié dans l’image, car il resterait visible dans ses couches. On le fournit à l’exécution. Pense aussi à utiliser des versions précises d’images et à les mettre à jour régulièrement pour récupérer les correctifs de sécurité.

### Mesurer

La commande « docker images » affiche la taille des images, et « docker history » montre la taille de chaque couche. Mesurer avant et après prouve le gain.

## Exemple pas à pas

Le code d’exemple compile une petite application front-end avec Node.js et la sert avec nginx. La première étape, nommée « build », part d’une image Node.js, installe les dépendances et lance la commande de construction. La seconde étape part de nginx en version alpine, ne récupère que le dossier de résultat avec « COPY --from=build », et n’emporte donc ni Node.js ni les sources.

Les commandes finales construisent l’image, comparent les tailles avec « docker images » et inspectent les couches avec « docker history ». Une variante pour une application serveur montre l’utilisation de « USER node ».

## Erreurs fréquentes

- Utiliser une image complète quand une variante légère suffit : l’image pèse plusieurs fois plus. Teste « alpine » ou « slim ».
- Copier le dossier « node_modules » local dans l’image : l’image grossit et peut contenir des binaires incompatibles. Ajoute-le au « .dockerignore ».
- Laisser les outils de compilation dans l’image finale : ils servent uniquement à la construction. Utilise un build multi-stage.
- Copier un fichier « .env » dans l’image : le secret reste dans les couches même après suppression. Exclus-le et passe les variables à l’exécution.
- Lancer l’application en administrateur : une faille donne tous les droits. Ajoute une instruction « USER ».
- Supprimer un fichier dans une couche ultérieure en pensant gagner de la place : la couche précédente le contient toujours. Nettoie dans la même instruction « RUN ».

## Bonnes pratiques

- Pars d’une image minimale et d’un tag précis.
- Utilise des builds multi-stage pour séparer construction et exécution.
- Maintiens un « .dockerignore » rigoureux.
- Exécute le processus avec un utilisateur non administrateur.
- Mesure la taille avant et après chaque optimisation.

## Auto-évaluation

- Pourquoi une image légère est-elle un avantage ?
- Comment fonctionne un build multi-stage ?
- Que fait « COPY --from=build » ?
- Pourquoi éviter de copier un fichier de secrets dans une image ?
- Comment mesurer la taille des couches d’une image ?

## À retenir

- Une image légère se construit, se télécharge et se déploie plus vite.
- Le build multi-stage sépare outils de construction et runtime.
- Un « .dockerignore » rigoureux évite le superflu et les secrets.
- Les secrets ne vont jamais dans l’image.
- Exécuter sans droits administrateur limite les dégâts.
MD,
            'code_example' => <<<'CODE'
# Fichier : Dockerfile (build multi-stage)
# Objectif : compiler une application front-end, puis ne livrer que le résultat

# ===== ÉTAPE 1 : construction (image lourde, jetée ensuite) =====
FROM node:22-alpine AS build
WORKDIR /app

# Copier d'abord les dépendances pour profiter du cache
COPY package*.json ./
RUN npm ci

# Copier le code puis compiler (produit le dossier /app/dist)
COPY . .
RUN npm run build

# ===== ÉTAPE 2 : exécution (image minimale) =====
FROM nginx:alpine

# Ne récupérer QUE le résultat de la compilation
# (ni Node.js, ni node_modules, ni le code source n'arrivent ici)
COPY --from=build /app/dist /usr/share/nginx/html

EXPOSE 80
# nginx démarre déjà avec la commande par défaut de l'image

# ===== Variante pour une application serveur Node.js : utilisateur sans droits =====
# FROM node:22-alpine
# WORKDIR /app
# COPY --from=build /app/dist ./dist
# COPY package*.json ./
# RUN npm ci --omit=dev
# USER node                      # ne pas tourner en administrateur
# CMD ["node", "dist/server.js"]

# ===== Mesurer le gain (dans le terminal) =====
# docker build -t vitrine:optimisee .
# docker images vitrine          # taille de l'image finale
# docker history vitrine:optimisee   # taille de chaque couche
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Alléger une image',
            'exercise_description' => <<<'MD'
Pars d’un Dockerfile « naïf » pour un petit site statique construit avec Node.js, puis transforme-le en build multi-stage et compare les tailles.

Critères de réussite :
- Une première image « site:naif » est construite avec un seul stage basé sur « node ».
- Une seconde image « site:optimise » utilise deux stages : « node » pour construire, « nginx:alpine » pour servir.
- L’image optimisée ne contient ni Node.js ni « node_modules » (vérifiable en lançant un shell dans le conteneur).
- Un fichier « .dockerignore » exclut « node_modules », « .git » et « .env ».
- La commande « docker images site » montre que l’image optimisée est nettement plus petite.
MD,
            'exercise_hint' => 'Ton projet doit produire un dossier « dist » avec « npm run build ». Pour vérifier le contenu, lance « docker run --rm site:optimise ls /usr/share/nginx/html ».',
            'exercise_solution' => <<<'CODE'
# ===== .dockerignore =====
node_modules
.git
.env

# ===== Dockerfile.naif (un seul stage) =====
FROM node:22
WORKDIR /app
COPY . .
RUN npm ci && npm run build
CMD ["npx", "serve", "dist"]

# ===== Dockerfile (multi-stage optimisé) =====
FROM node:22-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci
COPY . .
RUN npm run build

FROM nginx:alpine
COPY --from=build /app/dist /usr/share/nginx/html
EXPOSE 80

# ===== Commandes =====
# docker build -f Dockerfile.naif -t site:naif .
# docker build -t site:optimise .
# docker images site          # comparer les tailles
# docker run --rm site:optimise ls /usr/share/nginx/html
# docker run --rm site:optimise which node   # ne doit rien afficher
CODE,
        ],

        'Docker en développement' => [
            'description' => 'Utilise Docker au quotidien sans perdre en confort : rechargement à chaud, bind mounts ciblés, variables d’environnement et séparation entre configuration locale et production.',
            'objective' => 'Configurer une stack Compose de développement avec rechargement à chaud, dépendances préservées et configuration séparée de la production.',
            'content' => <<<'MD'
## Pourquoi cette notion

Conteneuriser une application, c’est bien, mais si chaque modification de code oblige à reconstruire l’image, le développement devient insupportable. Beaucoup d’équipes abandonnent Docker en local pour cette raison. Il existe pourtant des techniques simples qui rendent l’expérience aussi fluide qu’un lancement direct sur ta machine, tout en gardant l’avantage d’un environnement identique pour toute l’équipe.

Savoir configurer cela te rend immédiatement utile dans un projet où Docker est utilisé pour le développement.

## Les concepts clés

### Le rechargement à chaud

L’idée est de monter ton code source dans le conteneur avec un bind mount. Quand tu modifies un fichier sur ta machine, il change aussi dans le conteneur. Un outil de rechargement, comme le mode développement de ton framework ou un observateur de fichiers, détecte le changement et relance l’application sans reconstruire l’image.

### Préserver les dépendances de l’image

Monter tout le dossier du projet écrase le dossier des dépendances installé dans l’image par celui de ta machine, qui peut être absent ou incompatible. La solution consiste à ajouter un volume anonyme ou nommé par-dessus le dossier des dépendances, par exemple « /app/node_modules ». Le conteneur conserve alors ses propres dépendances, et seul le code source est partagé.

### Monter uniquement le nécessaire

Évite de monter des dossiers inutiles. Monte le code source, pas ton dossier personnel entier. Moins il y a de fichiers partagés, plus les performances sont bonnes, surtout sur les systèmes où le partage de fichiers est plus lent.

### Séparer développement et production

Une configuration de développement diffère de celle de production : rechargement à chaud, ports de débogage, bind mounts. Une approche courante est d’avoir un fichier « compose.yaml » de base et un fichier « compose.override.yaml » chargé automatiquement en local, qui ajoute les spécificités de développement. En production, on n’utilise pas ce fichier de surcharge. Les variables d’environnement sont aussi différentes, avec des fichiers « .env » distincts.

### Une cible de construction dédiée

Dans un Dockerfile multi-stage, tu peux nommer une étape « dev » avec les outils de développement, et une étape « prod » allégée. Compose choisit l’étape avec la clé « target ».

## Exemple pas à pas

Le code d’exemple présente un Dockerfile avec une étape « dev » et une étape « prod », puis deux fichiers Compose. Le fichier de base décrit l’application et la base de données de façon commune. Le fichier de surcharge, utilisé en local, choisit l’étape « dev », monte le code source, protège le dossier « node_modules » du conteneur avec un volume, publie le port de débogage et lance le serveur en mode développement.

Les commandes finales démarrent l’environnement local, suivent les logs pendant que tu modifies un fichier pour observer le rechargement, puis montrent comment lancer la configuration de production en ignorant explicitement le fichier de surcharge.

## Erreurs fréquentes

- Monter le projet entier sans protéger « node_modules » : les dépendances de l’image sont écrasées et l’application plante. Ajoute un volume sur ce dossier.
- Reconstruire l’image à chaque modification : le développement est lent. Utilise un bind mount et un outil de rechargement.
- Utiliser la même configuration en développement et en production : tu exposes des ports de débogage ou des bind mounts en production. Sépare avec un fichier de surcharge.
- Écouter sur « localhost » dans le conteneur : le navigateur ne peut pas joindre l’application. Écoute sur « 0.0.0.0 ».
- Problèmes de permissions sur les fichiers créés par le conteneur : ils appartiennent à l’administrateur. Exécute le conteneur avec ton identifiant utilisateur ou ajuste les droits.
- Versionner le fichier « .env » local : les secrets fuient. Ignore-le et fournis un « .env.example ».

## Bonnes pratiques

- Un environnement de développement démarrable avec une seule commande.
- Monte seulement les dossiers nécessaires.
- Sépare clairement configuration locale et production.
- Documente les commandes de démarrage dans le README.
- Garde l’environnement local proche de la production pour éviter les surprises.

## Auto-évaluation

- Comment obtenir le rechargement à chaud avec Docker ?
- Pourquoi ajouter un volume sur « node_modules » ?
- À quoi sert un fichier « compose.override.yaml » ?
- Comment choisir une étape précise d’un Dockerfile multi-stage ?
- Pourquoi séparer les configurations de développement et de production ?

## À retenir

- Un bind mount partage le code, un outil de rechargement fait le reste.
- Protège les dépendances de l’image avec un volume dédié.
- Le fichier de surcharge isole la configuration de développement.
- Les étapes « dev » et « prod » partagent un même Dockerfile.
- Le confort du développeur compte pour garder Docker au quotidien.
MD,
            'code_example' => <<<'CODE'
# ===== Fichier : Dockerfile (deux cibles : dev et prod) =====
FROM node:22-alpine AS base
WORKDIR /app
COPY package*.json ./

# --- Cible développement : toutes les dépendances + rechargement à chaud ---
FROM base AS dev
RUN npm ci
COPY . .
CMD ["npm", "run", "dev"]

# --- Cible production : dépendances de production uniquement ---
FROM base AS prod
RUN npm ci --omit=dev
COPY . .
USER node
CMD ["node", "server.js"]

# ===== Fichier : compose.yaml (base commune à tous les environnements) =====
# services:
#   app:
#     build: .
#     environment:
#       DATABASE_URL: postgres://boutique:${DB_PASSWORD}@db:5432/boutique
#     depends_on:
#       - db
#   db:
#     image: postgres:16-alpine
#     environment:
#       POSTGRES_USER: boutique
#       POSTGRES_PASSWORD: ${DB_PASSWORD}
#       POSTGRES_DB: boutique
#     volumes:
#       - donnees-db:/var/lib/postgresql/data
# volumes:
#   donnees-db:

# ===== Fichier : compose.override.yaml (chargé automatiquement en local) =====
services:
  app:
    build:
      target: dev                 # utilise la cible « dev » du Dockerfile
    ports:
      - "3000:3000"               # application
      - "9229:9229"               # port de débogage (jamais en production)
    volumes:
      - ./src:/app/src            # monter uniquement le code source
      - /app/node_modules         # protéger les dépendances de l'image
    environment:
      NODE_ENV: development

# ===== Commandes =====
# docker compose up -d                       # développement (base + override)
# docker compose logs -f app                 # observer le rechargement à chaud
# docker compose -f compose.yaml up -d --build   # production : sans override
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Un environnement de développement fluide',
            'exercise_description' => <<<'MD'
Configure l’environnement de développement d’une petite API Node.js « api-boutique » avec rechargement à chaud et séparation entre développement et production.

Critères de réussite :
- Le Dockerfile contient deux cibles nommées « dev » et « prod ».
- Un « compose.yaml » décrit l’application et une base PostgreSQL de façon commune.
- Un « compose.override.yaml » choisit la cible « dev », monte le dossier « src » et protège « node_modules » avec un volume.
- Modifier un fichier dans « src » est pris en compte sans reconstruire l’image.
- La commande « docker compose -f compose.yaml up -d » lance la version sans bind mount de développement.
MD,
            'exercise_hint' => 'Ajoute dans package.json un script « dev » avec « node --watch src/server.js » pour obtenir le rechargement sans dépendance supplémentaire.',
            'exercise_solution' => <<<'CODE'
# ===== package.json (extrait) =====
# "scripts": {
#   "dev": "node --watch src/server.js",
#   "start": "node src/server.js"
# }

# ===== Dockerfile =====
FROM node:22-alpine AS base
WORKDIR /app
COPY package*.json ./

FROM base AS dev
RUN npm ci
COPY . .
CMD ["npm", "run", "dev"]

FROM base AS prod
RUN npm ci --omit=dev
COPY . .
USER node
CMD ["npm", "start"]

# ===== compose.yaml =====
# services:
#   app:
#     build:
#       context: .
#       target: prod
#     depends_on:
#       - db
#   db:
#     image: postgres:16-alpine
#     environment:
#       POSTGRES_PASSWORD: ${DB_PASSWORD}
#     volumes:
#       - donnees-db:/var/lib/postgresql/data
# volumes:
#   donnees-db:

# ===== compose.override.yaml =====
services:
  app:
    build:
      target: dev
    ports:
      - "3000:3000"
    volumes:
      - ./src:/app/src
      - /app/node_modules
    environment:
      NODE_ENV: development

# ===== Commandes =====
# docker compose up -d --build              # développement
# (modifier src/server.js : l'application redémarre seule)
# docker compose -f compose.yaml up -d --build   # configuration sans override
CODE,
        ],

        'Projet final Docker' => [
            'description' => 'Conteneurise une application multi-services complète : application web, base PostgreSQL et reverse proxy, avec volumes persistants et variables d’environnement.',
            'objective' => 'Livrer une stack Compose fonctionnelle de trois services, avec un Dockerfile optimisé, des données persistantes et une configuration par variables d’environnement.',
            'content' => <<<'MD'
## Pourquoi cette notion

Ce projet final rassemble tout ton parcours : image personnalisée, volumes, réseaux, Compose, optimisation et configuration. C’est exactement l’architecture que tu rencontreras dans un projet réel : une application web, une base de données persistante et un reverse proxy qui reçoit le trafic public et le redirige vers l’application.

Tu peux présenter ce livrable à un client ou à un recruteur : il montre que tu sais empaqueter et lancer une application complète de façon reproductible.

## Les concepts clés

### L’architecture à trois services

Le reverse proxy, ici nginx, est le seul service exposé à l’extérieur. Il reçoit les requêtes sur le port 80 et les transmet à l’application. L’application, construite avec ton Dockerfile, contient la logique métier. La base PostgreSQL stocke les données et n’est accessible que depuis le réseau interne. Cette organisation protège la base et centralise l’entrée du trafic.

### Le rôle du reverse proxy

Un reverse proxy se place devant l’application. Il peut gérer le chiffrement, répartir la charge, servir des fichiers statiques et masquer l’adresse réelle de l’application. Sa configuration indique vers quel service transmettre, en utilisant simplement le nom du service Compose comme adresse.

### Persistance et configuration

Un volume nommé conserve les données de la base. Les identifiants viennent d’un fichier « .env » non versionné, accompagné d’un « .env.example » qui documente les variables. Aucun secret n’est présent dans les images ni dans le dépôt.

### Fiabilité

Un « healthcheck » sur la base garantit que l’application démarre seulement quand la base est prête. La politique « restart » relance les services après un arrêt inattendu ou un redémarrage de la machine.

### Livrer proprement

Un bon livrable inclut un README qui explique comment cloner, configurer et démarrer la stack. Une personne qui découvre ton projet doit pouvoir le lancer en quelques commandes.

## Exemple pas à pas

Le code d’exemple contient quatre éléments. Le premier est le Dockerfile de l’application, en deux étapes pour rester léger et exécuté sans droits administrateur. Le deuxième est la configuration nginx qui transmet toutes les requêtes au service « app » sur son port interne.

Le troisième est le fichier Compose avec les trois services : la base avec volume et contrôle de santé, l’application qui attend la base, et nginx qui publie le port 80 et monte sa configuration en lecture seule. Le quatrième est la séquence de commandes : copier le fichier d’exemple des variables, construire, démarrer, vérifier avec « curl » et arrêter en conservant les données.

## Erreurs fréquentes

- Publier le port de la base de données : elle devient accessible de l’extérieur. Ne publie que le port du reverse proxy.
- Oublier le montage de la configuration nginx : le serveur affiche sa page par défaut au lieu de transmettre à l’application. Monte le fichier de configuration au bon chemin.
- Utiliser « localhost » comme cible du proxy : il désigne le conteneur nginx lui-même. Utilise le nom du service, « app ».
- Versionner le fichier « .env » : les mots de passe fuient. Ignore-le et fournis un « .env.example ».
- Ne pas attendre que la base soit prête : l’application plante au démarrage. Ajoute un « healthcheck » et « service_healthy ».
- Perdre les données avec « docker compose down -v » : le volume est supprimé. N’utilise « -v » que pour repartir de zéro.

## Bonnes pratiques

- Un seul point d’entrée public, le reverse proxy.
- Des volumes nommés pour les données, des variables pour la configuration.
- Des images légères, sans secret, exécutées sans droits administrateur.
- Un README clair avec les commandes de démarrage.
- Teste un cycle complet : démarrage, arrêt, redémarrage, données conservées.

## Auto-évaluation

- Quel est le rôle de chaque service de la stack ?
- Pourquoi seul le reverse proxy publie-t-il un port ?
- Comment nginx joint-il l’application ?
- Comment garantir que la base est prête avant l’application ?
- Comment prouver que les données sont persistantes ?

## À retenir

- Une stack réaliste combine proxy, application et base.
- Seul le point d’entrée est exposé, la base reste interne.
- Volumes pour les données, variables pour la configuration.
- Healthcheck et restart rendent la stack fiable.
- Un README clair rend ton projet utilisable par d’autres.
MD,
            'code_example' => <<<'CODE'
# ===== Fichier : Dockerfile (application, multi-stage, utilisateur non root) =====
FROM node:22-alpine AS build
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev

FROM node:22-alpine
WORKDIR /app
COPY --from=build /app/node_modules ./node_modules
COPY . .
USER node
EXPOSE 3000
CMD ["node", "server.js"]

# ===== Fichier : nginx/default.conf (reverse proxy vers le service « app ») =====
# server {
#   listen 80;
#   location / {
#     proxy_pass http://app:3000;        # « app » = nom du service Compose
#     proxy_set_header Host $host;
#     proxy_set_header X-Real-IP $remote_addr;
#   }
# }

# ===== Fichier : compose.yaml =====
services:
  db:
    image: postgres:16-alpine
    restart: unless-stopped
    environment:
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: ${DB_NAME}
    volumes:
      - donnees-db:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USER} -d ${DB_NAME}"]
      interval: 5s
      timeout: 3s
      retries: 10

  app:
    build: .
    restart: unless-stopped
    environment:
      DATABASE_URL: postgres://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}
    depends_on:
      db:
        condition: service_healthy

  proxy:
    image: nginx:alpine
    restart: unless-stopped
    ports:
      - "80:80"                  # seul port exposé à l'extérieur
    volumes:
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app

volumes:
  donnees-db:

# ===== Commandes =====
# cp .env.example .env           # puis renseigner les vraies valeurs
# docker compose up -d --build
# docker compose ps
# curl http://localhost          # passe par nginx puis l'application
# docker compose down            # arrêter en conservant les données
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : stack web complète',
            'exercise_description' => <<<'MD'
Conteneurise une application « reservation-api » composée d’une API Node.js, d’une base PostgreSQL et d’un reverse proxy nginx.

Livrables : Dockerfile, compose.yaml, configuration nginx, .env.example, .dockerignore et README de démarrage.

Critères de réussite :
- Trois services (« db », « app », « proxy ») démarrent avec « docker compose up -d --build » et apparaissent dans « docker compose ps ».
- Seul le port 80 du service « proxy » est publié, et « curl http://localhost » renvoie la réponse de l’API.
- La base utilise un volume nommé et un « healthcheck », et « app » attend qu’elle soit saine.
- Les identifiants viennent d’un fichier « .env » ignoré par Git, avec un « .env.example » fourni.
- Après « docker compose down » puis un nouveau « up », les données de la base sont toujours présentes.
MD,
            'exercise_hint' => 'Fais écouter ton API sur 0.0.0.0, et utilise « app » comme cible dans « proxy_pass ». Pour tester la persistance, crée une table avant « down » avec « docker compose exec db psql ».',
            'exercise_solution' => <<<'CODE'
# ===== .env.example =====
# DB_USER=reservation
# DB_PASSWORD=changer_ce_mot_de_passe
# DB_NAME=reservation

# ===== .dockerignore =====
node_modules
.git
.env

# ===== Dockerfile =====
FROM node:22-alpine
WORKDIR /app
COPY package*.json ./
RUN npm ci --omit=dev
COPY . .
USER node
EXPOSE 3000
CMD ["node", "server.js"]

# ===== nginx/default.conf =====
# server {
#   listen 80;
#   location / {
#     proxy_pass http://app:3000;
#   }
# }

# ===== compose.yaml =====
services:
  db:
    image: postgres:16-alpine
    restart: unless-stopped
    environment:
      POSTGRES_USER: ${DB_USER}
      POSTGRES_PASSWORD: ${DB_PASSWORD}
      POSTGRES_DB: ${DB_NAME}
    volumes:
      - donnees-reservation:/var/lib/postgresql/data
    healthcheck:
      test: ["CMD-SHELL", "pg_isready -U ${DB_USER} -d ${DB_NAME}"]
      interval: 5s
      timeout: 3s
      retries: 10

  app:
    build: .
    restart: unless-stopped
    environment:
      DATABASE_URL: postgres://${DB_USER}:${DB_PASSWORD}@db:5432/${DB_NAME}
    depends_on:
      db:
        condition: service_healthy

  proxy:
    image: nginx:alpine
    restart: unless-stopped
    ports:
      - "80:80"
    volumes:
      - ./nginx/default.conf:/etc/nginx/conf.d/default.conf:ro
    depends_on:
      - app

volumes:
  donnees-reservation:

# ===== Vérifications =====
# cp .env.example .env
# docker compose up -d --build
# docker compose ps
# curl http://localhost
# docker compose exec db psql -U reservation -d reservation -c "CREATE TABLE test (id int);"
# docker compose down
# docker compose up -d
# docker compose exec db psql -U reservation -d reservation -c "\dt"   # la table existe
CODE,
        ],
    ],
];
