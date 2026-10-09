---
title: Remote et collaboration
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, tout est resté sur ta machine. Or un projet réel se partage : avec une équipe, avec un serveur de déploiement, avec le monde entier. Git y parvient grâce aux **dépôts distants** (*remotes*). Ce chapitre explique comment ton dépôt local dialogue avec une copie hébergée ailleurs, et comment travailler à plusieurs sans se marcher dessus.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un **remote** et le rôle du nom `origin` ;
- connecter un dépôt local à un dépôt distant et le cloner ;
- envoyer ton travail avec `git push` et récupérer celui des autres avec `git fetch` et `git pull` ;
- comprendre les **branches de suivi** (`origin/main`) et le *upstream* ;
- expliquer la différence entre `fetch`, `pull` et `pull --rebase` ;
- gérer un `push` rejeté et un historique divergent ;
- t'authentifier de façon sûre avec une clé SSH.

Prérequis : branches, merge et rebase. Prévois deux heures trente. Pour la pratique, tu auras besoin d'un compte sur un service d'hébergement (GitHub, GitLab ou autre). Le fonctionnement de GitHub lui-même est détaillé dans le parcours dédié.

## Le dépôt distant

Un **remote** est une référence nommée vers un autre dépôt Git, généralement hébergé sur un serveur. Le nom conventionnel du dépôt d'origine est **`origin`**. Ce n'est qu'un alias vers une URL, comme un contact dans ton téléphone.

```text
  Ton ordinateur                              Serveur
┌────────────────────┐   push ──────▶   ┌──────────────────┐
│  Dépôt local       │                  │  Dépôt distant   │
│  main              │   ◀────── pull   │  (origin)        │
│  origin/main       │                  │  main            │
└────────────────────┘                  └──────────────────┘
```

Pour voir les remotes d'un dépôt :

```bash
git remote -v
```

```text
origin  git@github.com:hancode/devroad.git (fetch)
origin  git@github.com:hancode/devroad.git (push)
```

Chaque remote apparaît deux fois, car l'URL de lecture et d'écriture peut différer.

## Récupérer un projet : git clone

La façon la plus courante d'obtenir un dépôt distant est de le cloner :

```bash
git clone git@github.com:hancode/devroad.git
cd devroad
```

`git clone` fait en une commande :

1. crée le dossier `devroad` et y copie **tout l'historique** ;
2. déclare automatiquement le remote `origin` ;
3. te place sur la branche par défaut (`main`) ;
4. configure `main` pour qu'elle suive `origin/main`.

Tu peux choisir le nom du dossier (`git clone <url> mon-dossier`) ou limiter l'historique pour aller plus vite (`git clone --depth 1 <url>`), utile pour les très gros dépôts ou pour l'intégration continue.

## Brancher un dépôt existant à un remote

Dans l'autre sens, tu as un projet local et tu veux le publier. Crée un dépôt **vide** sur ton service d'hébergement, puis :

```bash
git remote add origin git@github.com:hancode/devroad.git
git push -u origin main
```

L'option `-u` (`--set-upstream`) mémorise que ta branche locale `main` correspond à `origin/main`. Ensuite, tu pourras simplement taper `git push` ou `git pull`. Pour changer l'URL d'un remote ou le supprimer :

```bash
git remote set-url origin <nouvelle-url>
git remote remove origin
```

## Les branches de suivi

C'est le point qui trouble le plus les débutants. Dans ton dépôt local, il existe deux sortes de branches :

- les **branches locales** (`main`, `feature/x`) : tu les modifies en commitant ;
- les **branches de suivi** (*remote-tracking branches*) comme `origin/main` : ce sont des **copies en lecture seule** de l'état du serveur à la dernière communication.

`origin/main` ne se met pas à jour toute seule. Elle n'est actualisée que lorsque tu parles au serveur (`fetch`, `pull`, `push`). Le serveur peut donc avoir avancé sans que tu le saches.

```bash
git status
```

```text
On branch main
Your branch is ahead of 'origin/main' by 2 commits.
```

Cette phrase compare `main` à la dernière version connue d'`origin/main`. Elle peut dire *ahead* (en avance), *behind* (en retard) ou *diverged* (divergée).

:::quiz
Que représente la branche `origin/main` dans ton dépôt local ?
- [ ] La branche sur laquelle tu fais tes commits
- [x] Une copie en lecture seule de la branche `main` du serveur, mise à jour lors des échanges
- [ ] Une branche créée automatiquement à chaque commit
- [ ] Un alias de `HEAD`
> `origin/main` est une branche de suivi : elle mémorise l'état de `main` sur le serveur à ton dernier échange. On ne commite pas dessus directement.
:::

## Envoyer et récupérer

### git push : envoyer

```bash
git push                              # envoie la branche courante (si upstream configuré)
git push -u origin feature/filtre     # première fois pour une nouvelle branche
git push origin --delete feature/old  # supprime une branche sur le serveur
```

### git fetch : regarder sans toucher

```bash
git fetch
git log --oneline main..origin/main   # ce qui est arrivé sur le serveur
git diff main origin/main             # voir les changements
```

`git fetch` télécharge les nouveaux commits et met à jour les branches de suivi, **sans modifier** ton répertoire de travail ni tes branches locales. C'est l'opération sans danger : tu peux la lancer à tout moment.

### git pull : récupérer et intégrer

`git pull` est en réalité la combinaison de deux commandes :

```text
git pull  =  git fetch  +  git merge origin/main
```

Il télécharge, puis intègre immédiatement dans ta branche courante. Pratique, mais il peut créer des commits de merge superflus. Ajoute `--rebase` pour rejouer tes commits locaux au-dessus de ceux du serveur :

```bash
git pull --rebase
```

Tu peux en faire le comportement par défaut :

```bash
git config --global pull.rebase true
```

Le résultat est un historique linéaire, sans « Merge branch 'main' of github.com… » dans tous les sens. Le risque habituel du rebase s'applique : tu ne réécris ici que **tes** commits locaux non publiés, donc c'est sûr.

> **Astuce** : fais `git fetch` régulièrement et regarde `git status` avant de commencer à travailler. Tu sauras si la branche a bougé avant de produire du code qui entrera en conflit.

## Quand le push est rejeté

Scénario classique : Awa et Koffi travaillent sur `main`. Koffi pousse un commit. Awa, qui n'a pas récupéré ce commit, tente de pousser le sien :

```text
 ! [rejected]        main -> main (fetch first)
error: failed to push some refs to 'git@github.com:hancode/devroad.git'
hint: Updates were rejected because the remote contains work that you do
hint: do not have locally.
```

Git protège le serveur : il refuse d'écraser le commit de Koffi. La marche à suivre :

```bash
git pull --rebase       # récupère les commits de Koffi, rejoue le tien dessus
# résoudre les conflits éventuels, puis git rebase --continue
git push
```

> **Attention** : ne force **jamais** un push (`--force`) pour « faire passer » le message d'erreur sur une branche partagée. Tu effacerais le travail de Koffi du serveur. Le rejet est ton ami.

Pour comprendre l'état exact avant d'agir :

```bash
git fetch
git status
git log --oneline --graph --all
```

:::quiz
Ton `git push` est rejeté avec « Updates were rejected because the remote contains work that you do not have locally ». Que fais-tu ?
- [ ] Tu relances `git push --force` pour passer outre
- [ ] Tu supprimes ton dépôt local et tu le reclones
- [x] Tu récupères d'abord les changements distants (`git pull --rebase`), puis tu repousses
- [ ] Tu supprimes la branche sur le serveur
> Le serveur contient des commits que tu n'as pas. Il faut les intégrer localement avant de pousser. Forcer écraserait le travail des autres.
:::

## S'authentifier proprement avec SSH

Pour ne pas taper ton mot de passe à chaque `push`, utilise une **clé SSH**. Le principe : tu génères une paire de clés ; la **clé publique** est déposée sur le service d'hébergement, la **clé privée** reste précieusement sur ta machine.

```bash
ssh-keygen -t ed25519 -C "awa@example.com"
```

Accepte l'emplacement par défaut et choisis une phrase secrète. Puis affiche la clé publique :

```bash
cat ~/.ssh/id_ed25519.pub
```

Copie cette ligne dans les paramètres de ton compte (section « SSH keys »). Teste la connexion :

```bash
ssh -T git@github.com
```

Tu dois lire un message de bienvenue avec ton nom d'utilisateur. Désormais, utilise les URL de type `git@github.com:utilisateur/depot.git`. Si tu as cloné en HTTPS, bascule avec `git remote set-url origin git@github.com:utilisateur/depot.git`.

> **Attention** : ne partage **jamais** le fichier `id_ed25519` (sans `.pub`). C'est ta clé privée. Quiconque la possède agit à ta place.

## Travailler à plusieurs : le flux de base

Voici le cycle quotidien d'un développeur dans une équipe :

```bash
git switch main
git pull --rebase                      # 1. se mettre à jour
git switch -c feature/filtre-niveau    # 2. créer sa branche
# ... travailler, commiter ...
git fetch origin
git rebase origin/main                 # 3. se resynchroniser avant de partager
git push -u origin feature/filtre-niveau   # 4. publier la branche
# ... ouvrir une demande de fusion sur la plateforme ...
```

La branche est ensuite relue par un collègue, puis fusionnée dans `main` depuis la plateforme (c'est l'objet du parcours GitHub). Une fois fusionnée :

```bash
git switch main
git pull --rebase
git branch -d feature/filtre-niveau
git fetch --prune                      # nettoie les branches de suivi disparues
```

## Travailler avec un fork et plusieurs remotes

Quand tu contribues à un projet qui n'est pas le tien (open source), tu ne peux pas pousser directement dessus. Tu crées une copie personnelle sur ton compte, un **fork**, et tu la clones. Tu déclares alors un deuxième remote, par convention **`upstream`**, qui pointe vers le projet d'origine :

```bash
git remote add upstream git@github.com:projet-original/devroad.git
git fetch upstream
git rebase upstream/main
git push origin feature/ma-correction
```

`origin` est ton fork (où tu peux écrire), `upstream` est la source de vérité (que tu lis).

## Atelier guidé : un aller-retour avec un remote

Compte une heure trente. Il te faut un compte d'hébergement Git et, idéalement, un second dossier pour simuler un collègue.

1. Génère une clé SSH `ed25519`, ajoute la clé publique à ton compte, et valide avec `ssh -T git@github.com`.
2. Sur la plateforme, crée un dépôt vide `atelier-remote` (sans README).
3. En local, crée un dépôt avec deux commits, puis lance `git remote add origin <url>` et `git push -u origin main`. Contrôle avec `git remote -v` et `git branch -vv`.
4. Clone ce dépôt dans un second dossier `collegue` : `git clone <url> collegue`.
5. Dans `collegue`, fais un commit et pousse-le.
6. Dans ton dossier d'origine, fais un autre commit **sans** récupérer celui du collègue, puis tente `git push` : observe le rejet.
7. Lance `git fetch`, puis `git log --oneline --graph --all` pour visualiser la divergence.
8. Résous avec `git pull --rebase` puis `git push`.
9. Crée une branche `feature/test`, pousse-la avec `-u`, supprime-la sur le serveur avec `git push origin --delete`, puis lance `git fetch --prune`.
10. Active `pull.rebase true` et recommence le scénario du rejet pour constater la différence.

Pour t'auto-évaluer : explique la différence entre `origin/main` et `main`, et dis ce que fait précisément `git pull`.

## Erreurs fréquentes

- **Penser que `origin/main` est à jour.** Elle ne reflète que ton dernier `fetch`. Récupère avant de comparer.
- **Pousser avec `--force` pour résoudre un rejet.** Tu écrases le travail des autres. Utilise `pull --rebase`.
- **Créer le dépôt distant avec un README, puis pousser un dépôt local.** Les deux historiques n'ont rien en commun et Git refuse. Crée le dépôt distant vide.
- **Pousser des secrets.** Une clé d'API poussée est compromise, même si tu supprimes le commit ensuite : l'historique la contient toujours. Change la clé.
- **Cloner en HTTPS puis s'étonner d'être redemandé en mot de passe.** Passe en SSH ou configure un gestionnaire d'identifiants.
- **Partager sa clé privée.** Seule la clé `.pub` se partage.

## Bonnes pratiques

- Commence chaque journée par `git fetch` et `git status`.
- Utilise `git pull --rebase` (ou configure-le par défaut) pour un historique propre.
- Pousse tes branches souvent : ton travail est sauvegardé et visible.
- Ne pousse jamais directement sur `main` si l'équipe passe par des demandes de fusion.
- Nettoie régulièrement les branches mortes (`git fetch --prune`).
- Protège ta clé SSH par une phrase secrète et ne la copie jamais dans un dépôt.
- Utilise `git push --force-with-lease` dans les rares cas de réécriture de ta propre branche.

## À retenir

- Un **remote** est un alias vers un dépôt hébergé ; `origin` est le nom par défaut.
- `git clone` copie tout l'historique et configure `origin` automatiquement.
- `origin/main` est une branche de suivi en lecture seule, actualisée par `fetch`, `pull` et `push`.
- `git fetch` télécharge sans rien modifier ; `git pull` = `fetch` + intégration ; `pull --rebase` garde l'historique linéaire.
- Un push rejeté signifie qu'il faut d'abord intégrer le travail distant ; on ne force pas.
- Une clé SSH remplace le mot de passe : la publique se partage, la privée jamais.
- Avec un fork, `origin` désigne ton fork et `upstream` le projet d'origine.
