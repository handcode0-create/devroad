---
title: Comprendre Git
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Tu as déjà vu des fichiers nommés `projet_final.zip`, `projet_final_v2.zip`, `projet_final_VRAIMENT_final.zip`. Git existe pour que tu n'aies plus jamais à faire ça. C'est l'outil que tous les développeurs du monde utilisent pour garder la trace de leur code, revenir en arrière et travailler à plusieurs sans s'écraser mutuellement.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un **système de gestion de versions** et pourquoi Git est devenu la référence ;
- installer Git et le configurer avec ton identité ;
- créer un dépôt avec `git init` et comprendre le contenu du dossier `.git` ;
- distinguer les trois zones de Git : **répertoire de travail**, **index** (staging) et **dépôt** ;
- lire l'état de ton projet avec `git status` ;
- ignorer les fichiers sensibles ou inutiles avec `.gitignore`.

Prérequis : savoir ouvrir un terminal et naviguer dans des dossiers (`cd`, `ls`, `mkdir`). Prévois deux heures. Tout se fait sur ton ordinateur, sans compte en ligne : GitHub viendra plus tard, dans un autre parcours.

## Le problème : sauvegarder ne suffit pas

Imagine que tu travailles sur DevRoad. Lundi, tout fonctionne. Mardi, tu modifies la page des roadmaps et plus rien ne s'affiche. Tu voudrais revenir à lundi, mais tu n'as pas de copie. Ou alors tu as copié le dossier trois fois et tu ne sais plus laquelle est la bonne.

Maintenant, imagine que tu travailles avec un collègue. Vous modifiez tous les deux le même fichier. Celui qui enregistre en dernier **écrase** le travail de l'autre. C'est le scénario classique de la perte de données.

Un **système de gestion de versions** (*Version Control System*, VCS) résout ces deux problèmes :

- il enregistre l'**historique** complet : qui a changé quoi, quand et pourquoi ;
- il permet de **revenir** à n'importe quel état passé du projet ;
- il permet de **fusionner** le travail de plusieurs personnes sans écrasement ;
- il permet d'**expérimenter** sur une copie isolée sans risquer le code qui fonctionne.

> **À retenir** : Git n'est pas une simple sauvegarde. C'est une machine à remonter le temps, couplée à un outil de collaboration.

## Git, un système distribué

Il existait des outils de versions avant Git (Subversion, CVS). Ils fonctionnaient avec un **serveur central** : un seul endroit contenait l'historique, et sans connexion, tu ne pouvais plus rien faire.

Git est **distribué**. Chaque développeur possède sur sa machine une **copie complète** du projet *avec tout son historique*. Conséquences concrètes :

- tu peux travailler dans le train, sans internet ;
- les opérations sont quasi instantanées, car tout est local ;
- si le serveur disparaît, n'importe quelle copie peut le reconstruire.

```text
        Serveur distant (GitHub, GitLab…)
              ▲               ▲
              │               │
     ┌────────┴───┐     ┌─────┴──────┐
     │ Ordinateur │     │ Ordinateur │
     │  de Awa    │     │  de Koffi  │
     │ (historique│     │ (historique│
     │  complet)  │     │  complet)  │
     └────────────┘     └────────────┘
```

Git a été créé en 2005 par Linus Torvalds pour gérer le noyau Linux. Il est aujourd'hui utilisé dans la quasi-totalité des projets logiciels.

> **Astuce** : ne confonds pas **Git** et **GitHub**. Git est l'outil installé sur ta machine. GitHub est un site qui héberge des dépôts Git et ajoute des fonctionnalités de collaboration. Tu peux utiliser Git sans GitHub, pas l'inverse.

:::quiz
Quelle est la principale caractéristique d'un système de versions « distribué » comme Git ?
- [ ] L'historique est stocké uniquement sur un serveur central
- [x] Chaque développeur possède une copie complète du projet avec son historique
- [ ] Il faut une connexion internet pour enregistrer une modification
- [ ] Il ne fonctionne qu'avec GitHub
> Dans un système distribué, chaque copie locale contient tout l'historique. On peut donc travailler hors ligne et le projet survit à la perte d'un serveur.
:::

## Installer et configurer Git

Vérifie d'abord si Git est déjà présent :

```bash
git --version
```

Si la commande n'existe pas, installe-le :

```bash
# Ubuntu / Debian / WSL
sudo apt update && sudo apt install git

# macOS (avec Homebrew)
brew install git

# Windows : télécharge l'installateur sur git-scm.com
# (il fournit aussi « Git Bash », un terminal pratique)
```

Avant ton premier enregistrement, Git doit connaître ton **identité**. Elle sera inscrite dans chaque commit que tu feras :

```bash
git config --global user.name "Awa Kouassi"
git config --global user.email "awa@example.com"
git config --global init.defaultBranch main
git config --global core.editor "code --wait"
```

La dernière ligne définit VS Code comme éditeur pour les messages de commit ; remplace-la par `nano` si tu préfères. Pour vérifier :

```bash
git config --list
```

Il existe trois niveaux de configuration : `--system` (toute la machine), `--global` (ton utilisateur) et `--local` (le dépôt courant, qui a priorité). Dans la plupart des cas, `--global` suffit.

> **Attention** : l'adresse e-mail renseignée apparaîtra dans l'historique. Si tu publies du code sur GitHub plus tard, utilise l'adresse que tu acceptes de rendre visible, ou l'adresse « noreply » que GitHub te propose.

## Créer un dépôt : git init

Un **dépôt** (*repository*, souvent abrégé « repo ») est un dossier dont Git surveille l'évolution. Crée-en un :

```bash
mkdir mon-premier-depot
cd mon-premier-depot
git init
```

Git répond : `Initialized empty Git repository`. Il a créé un dossier caché `.git` :

```bash
ls -a
```

```text
.  ..  .git
```

Ce dossier `.git` **est** ton dépôt : il contient tout l'historique, la configuration locale et les références. Ton dossier de projet n'est que le « répertoire de travail ». Deux conséquences importantes :

- ne modifie jamais le contenu de `.git` à la main ;
- si tu supprimes `.git`, tu perds tout l'historique, mais tes fichiers restent. Le projet n'est plus suivi par Git.

Dans un projet qui existe déjà (comme un projet Laravel), tu te places à la racine et tu lances simplement `git init`. Un projet créé avec `composer create-project laravel/laravel` est d'ailleurs déjà initialisé.

## Les trois zones de Git

C'est **le** concept à comprendre pour ne plus jamais être perdu avec Git. Un fichier traverse trois zones :

```text
Répertoire de travail  ──git add──▶  Index (staging)  ──git commit──▶  Dépôt (.git)
  (tes fichiers)                     (la sélection)                    (l'historique)
```

1. **Le répertoire de travail** (*working directory*) : les fichiers tels que tu les vois et les modifies dans ton éditeur.
2. **L'index**, ou zone de préparation (*staging area*) : la liste des modifications que tu as choisies d'inclure dans le prochain enregistrement.
3. **Le dépôt** : l'historique permanent, composé de **commits**.

Pourquoi une zone intermédiaire ? Imagine que tu as corrigé un bug **et** commencé une nouvelle fonctionnalité dans la même heure. Grâce à l'index, tu peux enregistrer d'abord seulement la correction, avec un message clair, puis la fonctionnalité plus tard. Un historique propre commence par là.

Comme un photographe : le répertoire de travail est la scène, l'index est le cadrage (tu choisis qui est dans la photo), le commit est le déclic qui fige l'image.

:::quiz
Que fait la commande `git add` ?
- [ ] Elle enregistre définitivement les modifications dans l'historique
- [ ] Elle envoie les fichiers vers GitHub
- [x] Elle place des modifications dans l'index, en préparation du prochain commit
- [ ] Elle crée un nouveau dépôt
> `git add` copie l'état actuel des fichiers choisis dans l'index. L'enregistrement dans l'historique est réalisé ensuite par `git commit`.
:::

## Lire l'état du projet : git status

La commande que tu taperas le plus souvent est `git status`. Elle te dit où en sont tes fichiers :

```bash
echo "# Mon projet" > README.md
git status
```

```text
On branch main

No commits yet

Untracked files:
  (use "git add <file>..." to include in what will be committed)
        README.md

nothing added to commit but untracked files present (use "git add" to track)
```

Un fichier peut avoir plusieurs états :

| État | Signification |
| --- | --- |
| **Untracked** | Git ne le connaît pas encore |
| **Modified** | Suivi par Git, modifié depuis le dernier commit |
| **Staged** | Ajouté à l'index, prêt à être commité |
| **Committed** | Enregistré dans l'historique, aucune différence |

Ajoute le fichier à l'index et revérifie :

```bash
git add README.md
git status
```

```text
Changes to be committed:
  (use "git rm --cached <file>..." to unstage)
        new file:   README.md
```

Le fichier est maintenant « staged ». Dans le prochain chapitre, tu le commiteras pour de bon. Pour une version compacte de la sortie, utilise `git status -s`.

> **Astuce** : prends l'habitude de lancer `git status` avant et après chaque commande Git. Il indique même la commande à utiliser pour annuler ce que tu viens de faire.

## Comment Git stocke les données

Tu n'as pas besoin de connaître les détails, mais une idée simple t'évitera des erreurs : Git ne stocke pas des « différences » fichier par fichier, il prend des **instantanés** (*snapshots*) de tout le projet à chaque commit. Si un fichier n'a pas changé, il garde simplement un lien vers la version précédente.

Chaque commit reçoit un identifiant unique de 40 caractères, une **empreinte SHA-1** (par exemple `3f2a9c1…`). On en utilise souvent les 7 premiers caractères. Cette empreinte est calculée à partir du contenu : si un octet change, l'identifiant change. C'est ce qui garantit l'intégrité de l'historique.

## Ignorer des fichiers avec .gitignore

Certains fichiers ne doivent **jamais** entrer dans Git :

- les dépendances téléchargeables (`node_modules/`, `vendor/`) : des milliers de fichiers qu'une commande reconstruit ;
- les fichiers de configuration secrets (`.env`) : mots de passe, clés d'API ;
- les fichiers générés (`dist/`, `storage/logs`) et ceux de ton système ou éditeur (`.DS_Store`, `.idea/`).

Tu les déclares dans un fichier `.gitignore` à la racine. Voici celui d'un projet Laravel + React comme DevRoad :

```text
/vendor
/node_modules
/public/build
/public/hot
/storage/*.key
.env
.env.backup
.phpunit.result.cache
.DS_Store
.idea
.vscode
```

Règles de syntaxe :

- un motif par ligne ;
- un `/` final désigne un dossier ;
- un `/` au début ancre le motif à la racine du dépôt ;
- `!motif` fait une exception ;
- les lignes commençant par `#` sont des commentaires.

```bash
echo ".env" >> .gitignore
git status
```

Le fichier `.env` n'apparaît plus dans les fichiers non suivis.

> **Erreur fréquente** : ajouter un fichier à `.gitignore` **après** l'avoir commité ne le retire pas de l'historique. Git continue de le suivre. Il faut le « désuivre » avec `git rm --cached .env`, et si un secret a déjà été publié, le considérer comme compromis et le changer.

:::quiz
Pourquoi le fichier `.env` d'un projet Laravel doit-il figurer dans `.gitignore` ?
- [ ] Parce qu'il est trop volumineux
- [ ] Parce que Laravel le régénère à chaque exécution
- [x] Parce qu'il contient des secrets (mots de passe, clés) qui ne doivent pas être partagés
- [ ] Parce que Git ne sait pas lire les fichiers cachés
> `.env` contient des identifiants propres à chaque environnement. On versionne plutôt un fichier `.env.example` sans valeurs sensibles.
:::

## Atelier guidé : ton premier dépôt

Compte une heure. Tu vas tout faire dans le terminal.

1. Vérifie ton installation avec `git --version`, puis configure `user.name`, `user.email` et `init.defaultBranch`.
2. Crée un dossier `atelier-git` et entre dedans.
3. Lance `git init` puis `ls -a` pour repérer le dossier `.git`. Lance `git status` et lis chaque ligne.
4. Crée un fichier `README.md` avec un titre et une phrase de description. Relance `git status` : dans quelle catégorie apparaît-il ?
5. Crée un dossier `node_modules` contenant un fichier `test.txt`, et un fichier `.env` avec la ligne `SECRET=1234`. Constate que Git les liste comme non suivis.
6. Crée `.gitignore` avec les lignes `node_modules/` et `.env`. Relance `git status` : que remarques-tu ?
7. Ajoute `README.md` et `.gitignore` à l'index avec `git add`. Observe `git status -s`.
8. Modifie `README.md` après l'avoir ajouté, puis relance `git status`. Pourquoi le fichier apparaît-il dans deux sections ? (indice : l'index contient l'ancienne version).
9. Affiche le contenu de `.git` avec `ls .git` et repère `HEAD`, `config`, `objects`.

Pour t'auto-évaluer, explique à voix haute, sans notes : (a) la différence entre Git et GitHub, (b) le rôle de chacune des trois zones, (c) pourquoi on utilise `.gitignore`.

## Erreurs fréquentes

- **Lancer `git init` dans le mauvais dossier.** Un `git init` dans ton dossier personnel fait suivre tout ton disque. Vérifie toujours avec `pwd` avant de lancer la commande.
- **Créer un dépôt dans un dépôt.** Si tu fais `git init` dans un sous-dossier d'un projet déjà suivi, tu obtiens un dépôt imbriqué qui prête à confusion.
- **Oublier de configurer son identité.** Git refuse de commiter ou affiche un message « Please tell me who you are ».
- **Confondre `add` et `commit`.** `add` prépare, `commit` enregistre. Sans commit, rien n'est sauvegardé dans l'historique.
- **Versionner `node_modules` ou `vendor`.** Cela alourdit le dépôt pour rien. Crée le `.gitignore` **avant** le premier commit.
- **Pousser un `.env`.** Les robots scrutent GitHub en permanence à la recherche de clés : un secret publié se retrouve exploité en quelques minutes.

## Bonnes pratiques

- Lance `git status` très souvent, c'est ton tableau de bord.
- Crée le `.gitignore` dès l'initialisation du projet.
- Utilise toujours `main` comme nom de branche par défaut pour rester cohérent avec GitHub.
- Versionne un `.env.example` documentant les variables nécessaires, jamais le `.env` réel.
- Un dépôt par projet : ne mélange pas deux applications dans le même dépôt.
- Apprends les commandes dans le terminal avant de passer aux interfaces graphiques : elles ne font que les encapsuler.

## À retenir

- Git est un système de versions **distribué** : chaque copie contient tout l'historique.
- Git est local ; GitHub est un service d'hébergement distinct.
- `git init` crée le dossier `.git`, qui contient tout le dépôt.
- Trois zones : répertoire de travail, index (`git add`), dépôt (`git commit`).
- `git status` est la commande de référence pour savoir où tu en es.
- Le fichier `.gitignore` exclut les dépendances, les fichiers générés et surtout les secrets.
- Configure ton identité une fois avec `git config --global`.
