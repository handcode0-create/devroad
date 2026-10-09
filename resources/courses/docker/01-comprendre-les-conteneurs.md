---
title: Comprendre les conteneurs
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

« Chez moi, ça marche. » Si tu as déjà entendu ou prononcé cette phrase, tu as rencontré le problème que Docker résout. Un projet fonctionne sur ton ordinateur, mais plante chez ton collègue, sur le serveur de test ou en production, parce que la version de PHP, de Node ou de MySQL n'est pas la même. Docker propose une réponse simple : **emballer l'application avec tout ce dont elle a besoin** dans une boîte standard qui se comporte de la même façon partout.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un **conteneur** et en quoi il diffère d'une machine virtuelle ;
- distinguer une **image**, un **conteneur** et un **registre** ;
- installer Docker et vérifier qu'il fonctionne ;
- lancer, lister, arrêter et supprimer des conteneurs avec la ligne de commande ;
- exposer un port, passer une variable d'environnement et lire les logs ;
- entrer dans un conteneur pour inspecter ce qui s'y passe.

Prérequis : savoir utiliser un terminal (se déplacer dans les dossiers, lancer une commande). Aucune connaissance de Docker n'est nécessaire. Prévois deux heures et un ordinateur sous Windows, macOS ou Linux avec quelques gigaoctets d'espace libre.

## Le problème : des environnements qui divergent

Prenons DevRoad, une application Laravel qui utilise PHP 8.3, MySQL 8 et Node 20 pour compiler le front avec Vite. Pour la faire tourner « à la main », tu dois installer chacun de ces outils, dans la bonne version, avec les bonnes extensions PHP, puis configurer MySQL, créer la base, etc.

Maintenant, imagine :

- un nouveau développeur rejoint l'équipe et passe une journée à tout installer ;
- ton ordinateur a PHP 8.1 pour un autre projet, et DevRoad en exige 8.3 ;
- le serveur de production tourne avec une extension PHP différente de la tienne ;
- tu mets à jour MySQL et un ancien projet cesse de fonctionner.

Tous ces soucis ont la même cause : **l'environnement n'est pas décrit, il est installé de manière artisanale**. Docker permet de le décrire dans un fichier, puis de le reproduire à l'identique en une commande.

## Conteneur ou machine virtuelle ?

Avant Docker, la solution classique était la **machine virtuelle** (VM). Une VM simule un ordinateur complet : elle embarque son propre système d'exploitation, démarre en plusieurs dizaines de secondes et pèse plusieurs gigaoctets.

Un **conteneur** est plus léger. Il ne simule pas un ordinateur : il utilise le **noyau** du système hôte et isole simplement un ou plusieurs processus, avec leur propre système de fichiers, leur propre réseau et leurs propres limites de ressources.

| Critère | Machine virtuelle | Conteneur |
| --- | --- | --- |
| Système d'exploitation | Un complet par VM | Partage le noyau de l'hôte |
| Taille typique | Plusieurs Go | De quelques Mo à quelques centaines de Mo |
| Démarrage | Dizaines de secondes | Moins d'une seconde |
| Isolation | Très forte | Forte, mais moins étanche qu'une VM |
| Densité | Quelques VM par machine | Des dizaines de conteneurs par machine |

Une image pour retenir : une VM, c'est une **maison entière** avec ses fondations ; un conteneur, c'est un **appartement** dans un immeuble qui partage les fondations (le noyau) mais où chacun a sa porte, son compteur et ses meubles.

> **À retenir** : un conteneur n'est pas une mini-machine virtuelle. C'est un **processus isolé** qui voit un environnement qui lui est propre.

:::quiz
Quelle est la principale différence technique entre un conteneur et une machine virtuelle ?
- [ ] Le conteneur n'a pas besoin d'un ordinateur pour fonctionner
- [x] Le conteneur partage le noyau du système hôte, alors que la VM embarque son propre système complet
- [ ] La machine virtuelle est toujours plus rapide à démarrer
- [ ] Le conteneur fonctionne uniquement avec PHP
> Les conteneurs isolent des processus en s'appuyant sur le noyau de l'hôte. C'est ce qui les rend légers et rapides à démarrer, contrairement aux VM qui simulent un système entier.
:::

## Les trois mots à connaître : image, conteneur, registre

Le vocabulaire de Docker tient en trois notions.

- **Image** : un modèle en lecture seule. Elle contient le système de fichiers minimal, les programmes et la configuration nécessaires. Par exemple, l'image `mysql:8.0` contient MySQL 8.0 prêt à l'emploi. Une image ne s'exécute pas, elle se **décrit** et se **partage**.
- **Conteneur** : une instance en cours d'exécution d'une image. Tu peux créer dix conteneurs à partir de la même image, comme tu crées dix objets à partir d'une même classe.
- **Registre** : un serveur qui stocke et distribue des images. Le plus connu est **Docker Hub**. Il existe aussi GitHub Container Registry et des registres privés.

L'analogie avec la programmation objet est parlante : l'image est la **classe**, le conteneur est l'**objet**, et le registre est le **dépôt** où l'on publie ses classes.

Une image est identifiée par un nom et une **étiquette** (*tag*) : `nom:tag`. Sans tag, Docker utilise `latest`, ce qui est pratique pour tester mais dangereux en production, car la version change sans prévenir.

## Installer Docker

Sur Windows et macOS, installe **Docker Desktop**, qui regroupe le moteur, l'interface graphique et l'outil Compose. Sur Windows, il s'appuie sur WSL 2 ; l'installateur te guide pour l'activer. Sur Linux, installe **Docker Engine** en suivant la documentation officielle de ta distribution, puis ajoute ton utilisateur au groupe `docker` pour éviter de taper `sudo` à chaque commande.

Vérifie l'installation :

```bash
docker --version
docker compose version
docker run hello-world
```

La dernière commande télécharge une minuscule image de test, crée un conteneur, affiche un message de bienvenue, puis s'arrête. Si tu vois « Hello from Docker! », tout est en place.

Voici ce qui s'est passé en coulisses :

1. le client `docker` a contacté le **démon** Docker (le service qui fait réellement le travail) ;
2. le démon n'a pas trouvé l'image `hello-world` en local ;
3. il l'a téléchargée depuis Docker Hub ;
4. il a créé un conteneur à partir de cette image et l'a exécuté ;
5. le conteneur a affiché son message et s'est arrêté, car son unique processus était terminé.

> **Astuce** : un conteneur vit **tant que son processus principal tourne**. Quand ce processus se termine, le conteneur s'arrête. C'est une idée centrale qui explique beaucoup de comportements déroutants au début.

## Lancer un vrai service : un serveur web

Lançons Nginx, un serveur web très répandu :

```bash
docker run --name mon-nginx -d -p 8080:80 nginx:1.27
```

Décortiquons chaque morceau :

| Élément | Signification |
| --- | --- |
| `docker run` | Crée un conteneur à partir d'une image et le démarre |
| `--name mon-nginx` | Donne un nom lisible au conteneur |
| `-d` | Mode détaché : le conteneur tourne en arrière-plan |
| `-p 8080:80` | Relie le port 8080 de ta machine au port 80 du conteneur |
| `nginx:1.27` | L'image à utiliser, avec une version précise |

Ouvre `http://localhost:8080` dans ton navigateur : la page d'accueil de Nginx apparaît. Le conteneur écoute sur son port 80, mais ce port est **invisible** de l'extérieur tant que tu ne l'as pas publié avec `-p`. Le format est toujours `port-hôte:port-conteneur`.

### Observer ce qui tourne

```bash
docker ps                # conteneurs en cours d'exécution
docker ps -a             # tous les conteneurs, y compris arrêtés
docker logs mon-nginx    # sortie du conteneur
docker logs -f mon-nginx # suivre les logs en direct (Ctrl+C pour quitter)
docker stats --no-stream # consommation CPU et mémoire
```

Recharge la page dans ton navigateur, puis relance `docker logs mon-nginx` : tu vois ta requête apparaître. Les logs d'un conteneur sont tout ce que son processus écrit sur la sortie standard et la sortie d'erreur. C'est ton premier réflexe de débogage.

### Arrêter, redémarrer, supprimer

```bash
docker stop mon-nginx    # arrêt propre
docker start mon-nginx   # redémarre le même conteneur
docker restart mon-nginx
docker rm mon-nginx      # supprime un conteneur arrêté
docker rm -f mon-nginx   # force la suppression d'un conteneur en cours
```

Un conteneur arrêté n'est pas supprimé : il occupe encore de la place et son nom reste réservé. Si tu relances `docker run --name mon-nginx ...` sans l'avoir supprimé, Docker refuse car le nom existe déjà.

:::quiz
Que signifie l'option `-p 8080:80` dans `docker run` ?
- [ ] Le conteneur utilise 8080 Mo de mémoire et 80 % du processeur
- [x] Le port 8080 de ta machine est relié au port 80 du conteneur
- [ ] Le conteneur écoute le port 80 de ta machine uniquement
- [ ] Le conteneur est limité à 80 requêtes par seconde
> Le format est port-hôte:port-conteneur. Le navigateur contacte localhost:8080 et Docker transmet la requête au port 80 à l'intérieur du conteneur.
:::

## Passer de la configuration : les variables d'environnement

Beaucoup d'images se configurent avec des **variables d'environnement**, passées avec `-e`. Lançons MySQL, la base utilisée par DevRoad :

```bash
docker run --name devroad-db -d \
  -e MYSQL_ROOT_PASSWORD=secret \
  -e MYSQL_DATABASE=devroad \
  -p 3307:3306 \
  mysql:8.0
```

Ici, le port de MySQL dans le conteneur est 3306, mais nous le publions sur **3307** côté machine, pour ne pas entrer en conflit avec un éventuel MySQL déjà installé chez toi. Patiente une vingtaine de secondes, puis consulte les logs :

```bash
docker logs devroad-db
```

Tu dois voir la mention « ready for connections ». Ton application Laravel pourrait déjà s'y connecter avec `DB_HOST=127.0.0.1` et `DB_PORT=3307`.

> **Attention** : `MYSQL_ROOT_PASSWORD=secret` convient pour un test local. Ne mets jamais de vrai mot de passe dans une commande que tu partages ou que tu commits dans Git.

## Entrer dans un conteneur

Pour inspecter l'intérieur d'un conteneur en cours d'exécution, utilise `docker exec` :

```bash
docker exec -it devroad-db mysql -uroot -psecret
```

Les options `-it` te donnent un terminal interactif. Tu te retrouves dans le client MySQL, à l'intérieur du conteneur :

```sql
SHOW DATABASES;
EXIT;
```

Pour ouvrir un shell dans un conteneur :

```bash
docker exec -it mon-nginx bash
```

Si `bash` n'existe pas (cas fréquent des images minimales comme Alpine), essaie `sh`. Dans ce shell, tu peux explorer : `ls /usr/share/nginx/html`, `cat /etc/nginx/nginx.conf`. Tape `exit` pour sortir ; le conteneur continue de tourner.

## Les images : télécharger et gérer

```bash
docker pull node:20-alpine   # télécharge une image sans la lancer
docker images                # liste les images locales
docker rmi node:20-alpine    # supprime une image
docker image prune           # supprime les images inutilisées (sans tag)
```

Le suffixe `-alpine` désigne une variante basée sur Alpine Linux, une distribution minuscule : `node:20-alpine` pèse environ 150 Mo contre plus de 1 Go pour `node:20`. Tu reverras cette idée dans le chapitre sur l'optimisation des images.

Une image est composée de **couches** (*layers*) empilées, en lecture seule. Docker les met en cache : si deux images partagent la même couche de base, elle n'est téléchargée qu'une fois. Quand tu lances un conteneur, Docker ajoute une fine couche **inscriptible** au sommet. C'est pourquoi les fichiers créés dans un conteneur **disparaissent** quand on le supprime : la couche inscriptible est jetée avec lui. Le chapitre « Volumes et réseaux » explique comment conserver des données.

## Nettoyer son environnement

Après des essais, Docker accumule conteneurs et images. Fais le ménage :

```bash
docker rm -f mon-nginx devroad-db
docker system df          # espace occupé par Docker
docker system prune       # supprime conteneurs arrêtés, réseaux et images orphelines
```

> **Attention** : `docker system prune -a` supprime **toutes** les images non utilisées, ce qui obligera à tout re-télécharger. Lis toujours la confirmation avant de valider.

## Atelier guidé : ton premier environnement DevRoad

Compte une heure. Tu vas monter à la main la base de données et un serveur web de test.

1. Vérifie ton installation avec `docker --version` et `docker run hello-world`.
2. Télécharge l'image MySQL avec `docker pull mysql:8.0` et observe, dans la sortie, les couches téléchargées une à une.
3. Lance le conteneur `devroad-db` avec les variables `MYSQL_ROOT_PASSWORD`, `MYSQL_DATABASE` et le port `3307:3306`.
4. Attends « ready for connections » dans `docker logs devroad-db`.
5. Entre dans le conteneur avec `docker exec -it devroad-db mysql -uroot -psecret` et exécute `SHOW DATABASES;`. Vérifie que `devroad` existe.
6. Lance un Nginx sur le port 8081 et ouvre la page dans ton navigateur.
7. Avec `docker exec`, ouvre un shell dans Nginx et affiche le contenu de `/usr/share/nginx/html/index.html`.
8. Arrête les deux conteneurs avec `docker stop`, vérifie avec `docker ps -a`, puis supprime-les avec `docker rm`.
9. Relance la commande de l'étape 3 : la base `devroad` est-elle toujours là ? Pourquoi (ou pourquoi pas) ?

Pour t'auto-évaluer : explique avec tes mots la différence entre une image et un conteneur, puis dis pourquoi un conteneur Nginx s'arrête immédiatement si tu lui demandes d'exécuter simplement `echo bonjour`.

## Erreurs fréquentes

- **Oublier de publier le port.** L'application tourne dans le conteneur mais `localhost` ne répond pas : il manque `-p`.
- **Inverser les ports.** Dans `-p 8080:80`, l'hôte est à gauche, le conteneur à droite.
- **Port déjà utilisé.** L'erreur « port is already allocated » signifie qu'un autre processus occupe ce port : choisis-en un autre côté hôte.
- **Réutiliser un nom de conteneur existant.** Supprime l'ancien avec `docker rm` ou choisis un autre nom.
- **Croire que les données survivent.** Supprimer un conteneur efface sa couche inscriptible, donc ses données.
- **Utiliser `latest` partout.** La version peut changer d'un jour à l'autre et casser ton projet.
- **Chercher `bash` dans une image Alpine.** Utilise `sh`.

## Bonnes pratiques

- Donne toujours un **nom** à tes conteneurs avec `--name` pour les retrouver facilement.
- Fixe une **version précise** d'image (`mysql:8.0`, `nginx:1.27`) plutôt que `latest`.
- Préfère les variantes `-alpine` ou `-slim` quand elles conviennent.
- Consulte `docker logs` avant de chercher ailleurs : la réponse y est souvent.
- Supprime régulièrement les conteneurs et images dont tu n'as plus besoin.
- Ne place jamais de secrets réels dans l'historique de ton terminal ou dans un dépôt Git.

## À retenir

- Docker emballe une application et son environnement pour qu'elle se comporte de la même façon partout.
- Un conteneur est un **processus isolé** qui partage le noyau de l'hôte ; il est plus léger qu'une VM.
- Une **image** est un modèle en lecture seule, un **conteneur** en est une instance, un **registre** les distribue.
- Les commandes de base : `docker run`, `ps`, `logs`, `exec`, `stop`, `rm`, `pull`, `images`.
- `-p hôte:conteneur` publie un port, `-e` passe une variable d'environnement, `-d` détache le conteneur.
- Un conteneur vit tant que son processus principal tourne, et ses données disparaissent avec lui sans volume.
