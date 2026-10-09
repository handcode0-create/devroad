---
title: Volumes et réseaux
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Tu sais lancer un conteneur et construire une image. Mais deux questions pratiques se posent très vite. Première question : **où vont mes données** quand je supprime un conteneur MySQL ? Seconde question : **comment mon application PHP trouve-t-elle la base de données** qui tourne dans un autre conteneur ? Les réponses s'appellent **volumes** et **réseaux**.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi les données d'un conteneur sont éphémères ;
- créer et utiliser des **volumes nommés** pour conserver des données ;
- distinguer volume nommé, **bind mount** et `tmpfs` ;
- créer un **réseau** personnalisé et faire communiquer deux conteneurs par leur nom ;
- expliquer la différence entre un port **publié** et un port interne ;
- sauvegarder et restaurer le contenu d'un volume.

Prérequis : les chapitres « Comprendre les conteneurs » et « Dockerfile ». Prévois deux heures trente. Les exemples utilisent MySQL, la base de données de DevRoad.

## Le problème : des données qui disparaissent

Fais l'expérience. Lance un MySQL, crée une table, puis supprime le conteneur :

```bash
docker run --name db-test -d -e MYSQL_ROOT_PASSWORD=secret -e MYSQL_DATABASE=devroad mysql:8.0
sleep 25
docker exec db-test mysql -uroot -psecret -e "CREATE TABLE devroad.notes (id INT); SHOW TABLES FROM devroad;"
docker rm -f db-test
```

Relance ensuite un conteneur identique : la table `notes` a disparu. Rappelle-toi le fonctionnement des couches : un conteneur ajoute une fine couche inscriptible au-dessus de l'image, et cette couche est détruite avec lui. C'est voulu : un conteneur doit être **jetable et remplaçable** à tout moment.

Mais une base de données, elle, ne doit pas être jetable. La solution est de stocker ses fichiers **en dehors** du conteneur, dans un espace géré par Docker ou sur ta machine.

## Les trois façons de stocker des données

| Type | Où sont les données ? | Usage typique |
| --- | --- | --- |
| **Volume nommé** | Dans un espace géré par Docker | Base de données, données persistantes |
| **Bind mount** | Dans un dossier précis de ta machine | Code source en développement, fichiers de config |
| **tmpfs** | En mémoire vive, jamais sur le disque | Données temporaires sensibles |

### Le volume nommé

Un **volume** est un dossier géré par Docker, indépendant du cycle de vie des conteneurs. Tu le crées et l'attaches à un chemin du conteneur :

```bash
docker volume create devroad-mysql-data

docker run --name devroad-db -d \
  -e MYSQL_ROOT_PASSWORD=secret \
  -e MYSQL_DATABASE=devroad \
  -v devroad-mysql-data:/var/lib/mysql \
  mysql:8.0
```

L'option `-v nom:chemin` signifie : « monte le volume `devroad-mysql-data` dans le dossier `/var/lib/mysql` du conteneur ». C'est dans ce dossier que MySQL range ses fichiers. Refais maintenant l'expérience : crée une table, supprime le conteneur avec `docker rm -f devroad-db`, relance la même commande `docker run`. La table est toujours là, car le volume a survécu.

Les commandes utiles pour gérer les volumes :

```bash
docker volume ls                          # liste les volumes
docker volume inspect devroad-mysql-data  # détails, dont l'emplacement
docker volume rm devroad-mysql-data       # supprime un volume (irréversible)
docker volume prune                       # supprime les volumes non utilisés
```

> **Attention** : `docker volume rm` et `docker volume prune` détruisent définitivement les données. Avant de nettoyer, assure-toi qu'aucun volume utile n'est "orphelin" simplement parce que son conteneur est arrêté.

### Le bind mount

Un **bind mount** relie un dossier **de ta machine** à un dossier du conteneur. Les deux voient exactement les mêmes fichiers, en temps réel :

```bash
docker run --rm -p 8080:80 \
  -v "$(pwd)/public:/usr/share/nginx/html:ro" \
  nginx:1.27
```

Ici, le dossier `public` de ton projet remplace le contenu servi par Nginx. Si tu modifies un fichier dans ton éditeur, le changement est immédiatement visible dans le navigateur, sans reconstruire d'image. Le suffixe `:ro` (*read-only*) empêche le conteneur d'écrire dans ce dossier. Ce mécanisme est la base du confort de développement avec Docker, que tu approfondiras au chapitre « Docker en développement ».

Sous Windows PowerShell, remplace `$(pwd)` par `${PWD}`.

### Volume ou bind mount ?

Retiens cette règle simple :

- tu veux **conserver des données gérées par un service** (MySQL, fichiers uploadés) : volume nommé ;
- tu veux **partager ton code ou ta configuration** avec le conteneur pour travailler dessus : bind mount.

### Le tmpfs

Un montage `tmpfs` vit uniquement en mémoire et disparaît à l'arrêt. Il sert aux données temporaires que l'on ne veut pas écrire sur le disque :

```bash
docker run --rm --tmpfs /tmp:rw,size=64m alpine df -h /tmp
```

:::quiz
Tu veux conserver les données d'une base MySQL même si le conteneur est supprimé. Quelle solution est la plus adaptée ?
- [ ] Écrire les données dans la couche inscriptible du conteneur
- [x] Monter un volume nommé sur /var/lib/mysql
- [ ] Utiliser un montage tmpfs
- [ ] Ajouter l'option -d à docker run
> Un volume nommé est géré par Docker et survit à la suppression du conteneur. La couche inscriptible et tmpfs disparaissent avec lui, et -d ne concerne que l'exécution en arrière-plan.
:::

## Sauvegarder et restaurer un volume

Un volume est un dossier : on peut donc l'archiver avec un conteneur temporaire. Pour sauvegarder :

```bash
docker run --rm \
  -v devroad-mysql-data:/data:ro \
  -v "$(pwd):/backup" \
  alpine tar czf /backup/mysql-data.tar.gz -C /data .
```

Le conteneur monte le volume en lecture seule, ainsi que ton dossier courant, puis crée une archive. Pour restaurer dans un volume neuf :

```bash
docker volume create devroad-mysql-restore
docker run --rm \
  -v devroad-mysql-restore:/data \
  -v "$(pwd):/backup" \
  alpine tar xzf /backup/mysql-data.tar.gz -C /data
```

Pour une base de données, privilégie aussi une sauvegarde logique, plus portable entre versions :

```bash
docker exec devroad-db mysqldump -uroot -psecret devroad > sauvegarde.sql
docker exec -i devroad-db mysql -uroot -psecret devroad < sauvegarde.sql
```

## Les réseaux : comment les conteneurs se parlent

Par défaut, chaque conteneur est branché sur un réseau virtuel privé. Il a sa propre adresse IP interne, invisible depuis l'extérieur. C'est pour cela qu'il faut **publier** un port avec `-p` pour y accéder depuis ton navigateur.

Mais entre conteneurs, la situation est différente. Imagine ton application Laravel dans un conteneur `app` et MySQL dans un conteneur `db`. Que mettre dans `DB_HOST` ? Ni `localhost` (ce serait le conteneur `app` lui-même), ni une adresse IP (elle change à chaque redémarrage). La réponse : **le nom du conteneur**, sur un réseau personnalisé.

### Créer un réseau et y connecter des conteneurs

```bash
docker network create devroad-net

docker run --name db -d --network devroad-net \
  -e MYSQL_ROOT_PASSWORD=secret -e MYSQL_DATABASE=devroad \
  -v devroad-mysql-data:/var/lib/mysql \
  mysql:8.0

docker run --rm -it --network devroad-net mysql:8.0 \
  mysql -h db -uroot -psecret -e "SHOW DATABASES;"
```

Dans la dernière commande, le client MySQL tourne dans un **second conteneur** et atteint la base grâce à `-h db`. Docker fournit un **DNS interne** sur les réseaux personnalisés : le nom `db` est automatiquement traduit en adresse IP du bon conteneur. Aucune publication de port n'a été nécessaire, car la communication reste à l'intérieur du réseau.

> **Astuce** : cette résolution par nom ne fonctionne que sur les réseaux que tu crées toi-même, pas sur le réseau `bridge` par défaut. Crée toujours un réseau dédié à ton projet.

Les commandes pour inspecter les réseaux :

```bash
docker network ls
docker network inspect devroad-net
docker network connect devroad-net un-autre-conteneur
docker network rm devroad-net
```

### Les pilotes de réseau

| Pilote | Rôle |
| --- | --- |
| `bridge` | Réseau privé sur une machine ; c'est le choix par défaut et celui qu'on utilise le plus |
| `host` | Le conteneur partage le réseau de l'hôte, sans isolation (Linux) |
| `none` | Aucun réseau, isolation totale |
| `overlay` | Réseau entre plusieurs machines (clusters, hors périmètre de ce cours) |

## Port publié ou port interne ?

C'est une confusion classique, alors fixe bien la règle :

- le port **publié** (`-p 3307:3306`) sert à joindre un conteneur **depuis ta machine** (navigateur, client SQL, `curl`) ;
- le port **interne** (3306) sert à joindre un conteneur **depuis un autre conteneur** du même réseau.

Dans l'exemple, ton client MySQL installé sur ta machine se connecte à `127.0.0.1:3307`, alors que le conteneur Laravel se connecte à `db:3306`. Même base, deux chemins, deux ports différents.

C'est aussi une bonne pratique de sécurité : en production, ta base de données ne devrait **pas** publier de port du tout. Seul le serveur web est exposé, et la base n'est joignable que par les conteneurs du réseau interne.

:::quiz
Dans un réseau Docker personnalisé, comment un conteneur Laravel doit-il désigner le conteneur MySQL nommé db ?
- [ ] Avec localhost
- [ ] Avec l'adresse IP notée à la main lors du premier lancement
- [x] Avec le nom du conteneur, par exemple DB_HOST=db
- [ ] Avec le port publié sur la machine hôte
> Le DNS interne de Docker traduit le nom du conteneur en adresse IP. localhost désigne le conteneur lui-même, et les IP changent à chaque redémarrage.
:::

## Mise en pratique : DevRoad avec une base persistante

Voici le schéma que tu vas monter à la main dans l'atelier :

```text
 Ton navigateur
      |
   :8000 (port publié)
      v
 +-----------+   réseau devroad-net   +-----------+
 | conteneur |  -------------------->  | conteneur |
 |   app     |      DB_HOST=db:3306    |    db     |
 +-----------+                         +-----+-----+
                                             |
                                    volume devroad-mysql-data
```

Dans le fichier `.env` de Laravel, la configuration devient :

```text
DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=devroad
DB_USERNAME=root
DB_PASSWORD=secret
```

Tout le reste (migrations, seeders) fonctionne exactement comme avant, car pour Laravel, `db` est simplement un nom d'hôte.

## Atelier guidé : base persistante et conteneurs connectés

Compte une heure trente.

1. Crée un réseau avec `docker network create devroad-net` et vérifie-le avec `docker network ls`.
2. Crée un volume `devroad-mysql-data`.
3. Lance MySQL nommé `db` sur le réseau `devroad-net`, avec le volume monté sur `/var/lib/mysql`, sans publier de port.
4. Attends « ready for connections » dans `docker logs db`.
5. Lance un conteneur temporaire `mysql:8.0` sur le même réseau et exécute `mysql -h db -uroot -psecret -e "CREATE TABLE devroad.roadmaps (id INT, titre VARCHAR(100)); INSERT INTO devroad.roadmaps VALUES (1, 'Docker');"`.
6. Supprime le conteneur avec `docker rm -f db`.
7. Relance exactement la même commande de l'étape 3 et vérifie avec un client temporaire que la ligne « Docker » est toujours présente.
8. Inspecte le volume avec `docker volume inspect devroad-mysql-data` et note son point de montage.
9. Réalise une sauvegarde du volume dans une archive `.tar.gz`, puis une sauvegarde logique avec `mysqldump`.
10. Lance un Nginx en bind mount sur un dossier `public` contenant une page `index.html`, modifie la page et observe le résultat sans relancer le conteneur.
11. Termine par un nettoyage : conteneurs, réseau, puis volume en dernier.

Pour t'auto-évaluer : explique pourquoi `DB_HOST=localhost` ne fonctionne pas depuis un conteneur applicatif, et quand utiliser un volume plutôt qu'un bind mount.

## Erreurs fréquentes

- **Oublier le volume sur MySQL.** À la première suppression du conteneur, toute la base disparaît.
- **Utiliser `localhost` comme hôte de base de données.** Depuis un conteneur, `localhost` est le conteneur lui-même.
- **Compter sur la résolution de noms sur le réseau par défaut.** Crée un réseau personnalisé.
- **Confondre le port hôte et le port conteneur.** Entre conteneurs, on utilise toujours le port interne.
- **Changer le mot de passe MySQL après coup.** Les variables `MYSQL_ROOT_PASSWORD` ne sont lues qu'à l'initialisation d'un volume vide ; supprime le volume pour repartir de zéro en développement.
- **Supprimer un volume par `prune` sans réfléchir.** La perte est définitive.
- **Monter un bind mount sur un chemin du conteneur déjà rempli.** Le contenu d'origine de l'image est masqué par le dossier monté.

## Bonnes pratiques

- Un **volume nommé** pour tout service qui stocke des données (base, fichiers uploadés).
- Un **réseau dédié** par projet, avec des noms de conteneurs explicites.
- Ne publie un port que lorsqu'il est utile à l'utilisateur ou à toi en développement.
- Monte en lecture seule (`:ro`) tout ce que le conteneur n'a pas besoin de modifier.
- Planifie des sauvegardes régulières et teste au moins une fois leur restauration.
- Ne mets jamais de données importantes dans la couche inscriptible d'un conteneur.
- Nomme tes volumes avec le nom du projet pour les identifier en un coup d'œil.

## À retenir

- La couche inscriptible d'un conteneur est jetée avec lui : les données importantes vont dans un **volume**.
- Le **volume nommé** conserve les données gérées par un service, le **bind mount** partage un dossier de ta machine, le **tmpfs** vit en mémoire.
- `-v nom:/chemin` monte un volume ; ajoute `:ro` pour une lecture seule.
- Sur un **réseau personnalisé**, les conteneurs se joignent par leur **nom** grâce au DNS interne de Docker.
- Le port publié (`-p`) sert à l'extérieur ; entre conteneurs, on utilise le port interne.
- En production, ne publie pas le port de la base de données.
- Sauvegarde tes volumes et teste la restauration.
