# DevRoad Sandbox — architecture

## Objectif

Le Sandbox est la couche d’exécution de DevRoad pour les projets complets : React/Vite, Next.js, Node.js, PHP et Laravel.

DevLab reste l’IDE éducatif et son runtime navigateur. Le Sandbox doit, lui, exécuter le projet dans une infrastructure isolée.

## Règle de sécurité

Le code utilisateur ne doit jamais être exécuté avec exec(), shell_exec(), Process ou une commande équivalente directement dans le processus PHP de DevRoad ou dans le conteneur applicatif Railway.

L’architecture cible est :

    Navigateur
       │
       │ HTTPS / WebSocket
       ▼
    DevRoad Laravel
       │
       │ SandboxExecutor
       ▼
    Sandbox Manager
       │
       ├── lifecycle
       ├── terminal / PTY
       ├── filesystem
       ├── process manager
       ├── preview proxy
       └── resource limits
       │
       ▼
    Runtime isolé par utilisateur
       ├── Node.js
       ├── PHP / Composer
       └── application processes

## Modèle de données

### sandbox_projects

Représente le projet logique appartenant à l’utilisateur.

- user_id
- name
- template
- runtime
- runtime_version
- status
- preview_url
- settings
- metadata
- timestamps de démarrage/arrêt

### sandbox_instances

Représente une instance d’exécution concrète.

- sandbox_project_id
- driver
- provider_instance_id
- status
- région
- CPU / RAM / stockage
- heartbeat
- timestamps

### sandbox_processes

Représente les processus applicatifs exposés par une instance.

- terminal/dev server
- commande
- port
- statut
- identifiant du processus côté provider
- exit code

## États

    stopped → starting → running → stopping → stopped
                        └→ error
    running → sleeping → running

## Templates initiaux

- React + Vite
- Next.js
- Node.js
- PHP
- Laravel

Les versions sont centralisées dans config/sandbox.php afin de pouvoir faire évoluer les images/runtime sans modifier les contrôleurs.

## Adapter d’exécution

Laravel dépend uniquement du contrat App\Contracts\SandboxExecutor.

L’implémentation actuelle est volontairement UnavailableSandboxExecutor.

Cela permet de déployer la couche de gestion et l’interface sans activer accidentellement une exécution non isolée.

Le futur provider doit implémenter :

- start
- stop
- restart
- status
- destroy

## Activation

Le runtime est explicitement désactivé par défaut :

    DEVROAD_SANDBOX_ENABLED=false
    DEVROAD_SANDBOX_DRIVER=unavailable
    DEVROAD_SANDBOX_REGION=auto

Aucun projet utilisateur ne doit pouvoir déclencher un processus réel tant qu’un executor isolé n’est pas branché.

## Prochaine phase

1. choisir le runtime isolé ;
2. créer les images/templates Node/PHP ;
3. provisionner une instance par Sandbox ;
4. connecter le filesystem persistant ;
5. connecter un PTY via WebSocket ;
6. gérer les ports de preview ;
7. appliquer CPU/RAM/disque/temps d’exécution ;
8. ajouter les logs et le heartbeat ;
9. ajouter start/stop/restart réel ;
10. connecter l’éditeur DevLab au filesystem Sandbox.
