---
title: Repositories et branches
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Un dépôt GitHub n'est pas qu'un dossier de fichiers en ligne : c'est un espace de travail qu'on organise, qu'on partage et qu'on protège. Dans ce chapitre, tu apprends à structurer un dépôt, à inviter des collaborateurs, à gérer les branches depuis l'interface et à empêcher les accidents sur la branche principale.

À la fin du chapitre, tu seras capable de :

- rédiger un bon `README.md` et les fichiers d'accompagnement d'un dépôt ;
- naviguer dans l'historique, les branches et les tags depuis l'interface ;
- créer, comparer et supprimer des branches sur GitHub ;
- définir la **branche par défaut** ;
- inviter des collaborateurs et choisir leurs permissions ;
- **forker** un projet pour contribuer à un dépôt qui n'est pas le tien ;
- protéger `main` avec des règles de branche ;
- publier une version avec une **release**.

Prérequis : le chapitre « Découvrir GitHub » et les bases des branches Git. Prévois deux heures. Tu travailleras dans un dépôt de test.

## Le dépôt, vu de l'intérieur

Sur la page d'un dépôt, l'onglet **Code** affiche l'arborescence des fichiers, la dernière activité et le contenu du `README.md`. Quelques repères utiles :

- le **sélecteur de branche** en haut à gauche, pour changer de branche ou de tag ;
- le nombre de **commits** : clique dessus pour parcourir l'historique, chaque commit ayant sa page de détail avec le diff ;
- le bouton vert **Code** : l'URL de clonage (HTTPS, SSH, GitHub CLI) et le téléchargement en ZIP ;
- la barre latérale : description, sujets (*topics*), licence, nombre d'étoiles, langages utilisés.

Sur n'importe quel fichier, tu peux voir :

- **Blame** : qui a modifié chaque ligne, et dans quel commit ;
- **History** : tous les commits qui ont touché ce fichier ;
- **Raw** : le contenu brut.

Tu peux même copier un lien vers une ligne précise : clique sur le numéro de ligne, puis copie l'URL (`…#L42`). Idéal pour référencer un morceau de code dans une discussion.

## Les fichiers qui font un bon dépôt

GitHub reconnaît certains fichiers par leur nom et les met en valeur automatiquement.

| Fichier | Rôle |
| --- | --- |
| `README.md` | Page d'accueil du projet |
| `LICENSE` | Conditions de réutilisation |
| `CONTRIBUTING.md` | Comment contribuer (branches, commits, tests) |
| `CODE_OF_CONDUCT.md` | Règles de comportement de la communauté |
| `SECURITY.md` | Comment signaler une faille |
| `.gitignore` | Fichiers exclus du suivi |
| `.github/` | Modèles d'issues, de PR, workflows d'automatisation |

### Un README efficace

Un README répond en quelques secondes à : *c'est quoi, comment l'installer, comment l'utiliser, comment contribuer ?* Une structure éprouvée :

```markdown
# DevRoad

Application web de roadmaps et de fiches mémo pour développeurs.

## Aperçu
(capture d'écran ou GIF)

## Stack
Laravel 11, Inertia, React, Tailwind CSS.

## Installation
git clone git@github.com:hancode/devroad.git
cd devroad
composer install && npm install
cp .env.example .env && php artisan key:generate
php artisan migrate --seed
composer dev

## Contribuer
Voir CONTRIBUTING.md.

## Licence
MIT
```

Le README est écrit en **Markdown**, le même format que celui des commentaires et des issues. Apprends ses bases : titres avec `#`, listes avec `-`, code entre triples accents graves, liens `[texte](url)`, et cases à cocher `- [ ]`.

> **Astuce** : ajoute en haut du README des **badges** (état des tests, version, licence). Ils donnent immédiatement une impression de projet sérieux et entretenu.

## Gérer les branches depuis GitHub

Tu connais les branches avec Git. GitHub en offre une vue d'ensemble :

- **Branches** (lien près du sélecteur) : liste toutes les branches, leur auteur, leur retard ou avance par rapport à la branche par défaut ;
- création d'une branche en tapant un nouveau nom dans le sélecteur de branche ;
- suppression d'une branche fusionnée en un clic ;
- possibilité de **restaurer** une branche supprimée par erreur.

Pour comparer deux branches, utilise l'URL spéciale `github.com/utilisateur/depot/compare/main...feature/filtre` : GitHub affiche les commits et le diff, et propose d'ouvrir une pull request.

### La branche par défaut

La **branche par défaut** est celle que GitHub affiche à l'ouverture du dépôt et qui sert de base aux nouvelles pull requests. Presque toujours, c'est `main`. Pour la changer : *Settings → Branches → Default branch*.

Pour renommer `master` en `main` sur un vieux dépôt :

```bash
git branch -m master main
git push -u origin main
# Puis, sur GitHub : Settings → Branches → changer la branche par défaut
git push origin --delete master
```

### Synchroniser les branches locales avec GitHub

Quand une branche est supprimée sur GitHub (après une fusion), ta copie locale de la branche de suivi reste. Nettoie avec :

```bash
git fetch --prune
git branch -vv          # repère les branches marquées [gone]
git branch -d feature/ancienne
```

Tu peux aussi activer dans *Settings → General* l'option **Automatically delete head branches** : GitHub supprime la branche après la fusion d'une pull request.

:::quiz
Que fait l'option « Default branch » d'un dépôt GitHub ?
- [ ] Elle définit la branche qui ne peut jamais être supprimée localement
- [x] Elle détermine la branche affichée par défaut et servant de base aux nouvelles pull requests
- [ ] Elle fusionne automatiquement toutes les branches
- [ ] Elle masque les autres branches
> La branche par défaut est celle que GitHub présente d'abord et vers laquelle les pull requests pointent par défaut, en général `main`.
:::

## Collaborer : inviter, forker

Il existe deux grands modèles de contribution.

### Modèle 1 : les collaborateurs (équipe)

Tu invites les personnes de ton équipe sur le dépôt. Elles poussent des branches directement. Dans *Settings → Collaborators* (dépôt personnel), saisis un nom d'utilisateur ou un e-mail. Pour un dépôt appartenant à une **organisation**, tu gères plutôt des **équipes** et des rôles :

| Rôle | Peut |
| --- | --- |
| **Read** | Lire, cloner, commenter |
| **Triage** | Gérer les issues et PR sans écrire de code |
| **Write** | Pousser des branches, fusionner des PR |
| **Maintain** | Gérer le dépôt sans les paramètres sensibles |
| **Admin** | Tout, y compris supprimer le dépôt |

Applique le **principe du moindre privilège** : donne à chacun le rôle minimal dont il a besoin. Un stagiaire n'a pas besoin d'*Admin*.

### Modèle 2 : le fork (contribution externe)

Pour contribuer à un projet dont tu n'es pas membre, tu ne peux pas pousser dessus. Tu crées une copie sous ton compte, un **fork**, avec le bouton *Fork*. Le workflow :

1. Forke le dépôt original sur GitHub.
2. Clone **ton fork** : `git clone git@github.com:toi/projet.git`.
3. Ajoute le dépôt d'origine comme remote `upstream`.
4. Crée une branche, commite, pousse sur ton fork.
5. Ouvre une pull request du fork vers le projet d'origine.

```bash
git remote add upstream git@github.com:proprietaire/projet.git
git fetch upstream
git switch -c fix/faute-readme
# ... commit ...
git push -u origin fix/faute-readme
```

Pour tenir ton fork à jour : `git fetch upstream`, puis `git rebase upstream/main` (ou le bouton *Sync fork* de l'interface).

## Protéger la branche principale

Même dans une petite équipe, une erreur sur `main` coûte cher : un push direct cassé, une suppression accidentelle, un `--force`. GitHub permet de **protéger** une branche. Le chemin : *Settings → Branches → Add branch protection rule* (ou *Rules → Rulesets* dans l'interface récente), avec le motif `main`.

Réglages essentiels :

- **Require a pull request before merging** : plus de push direct. Ajoute « Require approvals » avec au moins une approbation ;
- **Require status checks to pass** : les tests automatiques doivent être verts (tu les créeras au chapitre sur Actions) ;
- **Require conversation resolution** : tous les commentaires de relecture doivent être traités ;
- **Require linear history** : interdit les commits de merge ;
- **Block force pushes** et **restrict deletions** : personne ne peut réécrire ou supprimer `main`.

> **Attention** : sur un dépôt personnel où tu es le seul développeur, la règle « approbation requise » te bloquera toi-même. Garde alors au minimum l'obligation de passer par une PR avec tests verts, et l'interdiction du force push.

## Les releases : publier une version

Une **release** est une publication officielle d'une version, basée sur un tag Git. Elle apparaît dans la barre latérale du dépôt avec des notes et d'éventuels fichiers joints.

Depuis le terminal :

```bash
git tag -a v1.0.0 -m "Première version stable"
git push origin v1.0.0
```

Puis sur GitHub : *Releases → Draft a new release*, choisis le tag, clique sur *Generate release notes* (GitHub liste les PR fusionnées depuis la dernière version) et publie. Avec la CLI :

```bash
gh release create v1.0.0 --generate-notes
```

Nomme tes versions en **SemVer** (`MAJEUR.MINEUR.CORRECTIF`) pour que les utilisateurs comprennent l'impact d'une mise à jour.

:::quiz
Tu veux proposer une correction à un projet open source dont tu n'es pas collaborateur. Quelle est la bonne méthode ?
- [ ] Pousser directement ta branche sur le dépôt d'origine
- [x] Forker le dépôt, pousser ta branche sur ton fork, puis ouvrir une pull request vers l'original
- [ ] Demander le mot de passe du propriétaire
- [ ] Copier les fichiers dans un nouveau dépôt sans lien
> Le fork crée une copie sous ton compte sur laquelle tu peux écrire. La pull request propose ensuite tes changements au projet d'origine.
:::

## Autres paramètres utiles

Dans *Settings → General*, tu trouves :

- **Features** : activer ou désactiver Issues, Projects, Wiki, Discussions ;
- **Pull Requests** : autoriser ou non les modes de fusion (*merge commit*, *squash*, *rebase*) ; décocher ceux que ton équipe n'utilise pas impose une règle commune ;
- **Danger Zone** : changer la visibilité, transférer, archiver ou supprimer. Ces actions sont irréversibles ou presque : agis avec prudence.

L'option **Archive** rend un dépôt en lecture seule. Elle est parfaite pour un projet terminé que tu veux garder visible sans qu'on y contribue.

## Atelier guidé : organiser un dépôt d'équipe

Compte une heure trente. Utilise deux comptes si tu en as (le tien et celui d'un camarade) ; sinon, simule seulement les étapes de configuration.

1. Crée un dépôt `atelier-depot` avec un README, une licence MIT et un `.gitignore`.
2. Réécris le README avec les rubriques : description, stack, installation, contribution, licence.
3. Ajoute `CONTRIBUTING.md` (convention de branches et de commits) et `SECURITY.md` (adresse pour signaler une faille).
4. Clone le dépôt, crée une branche `docs/ameliore-readme`, modifie, commite, pousse et observe le bandeau « Compare & pull request ».
5. Dans *Settings → Branches*, ajoute une règle de protection sur `main` : PR obligatoire, force push bloqué. Essaie ensuite de pousser directement sur `main` et lis le refus.
6. Active « Automatically delete head branches ».
7. Invite un camarade avec le rôle *Write*, puis change son rôle en *Read*. Note la différence de ce qu'il peut faire.
8. Fais, avec un projet open source simple, l'exercice du fork : forke, clone, ajoute `upstream`, crée une branche, pousse (sans ouvrir de PR si le projet n'attend pas de contributions).
9. Crée un tag `v0.1.0` et publie une release avec des notes générées.
10. Archive le dépôt de test quand tu as terminé (puis désarchive-le).

Pour t'auto-évaluer : explique la différence entre un collaborateur et un fork, et cite trois réglages de protection de `main`.

## Erreurs fréquentes

- **Oublier le fichier `.gitignore` au démarrage.** Les dossiers de dépendances et les fichiers `.env` finissent dans l'historique.
- **Donner le rôle Admin à tout le monde.** Un seul faux pas peut supprimer le dépôt.
- **Laisser `main` sans protection.** Un push direct ou un force push suffit à tout casser.
- **Travailler sur un fork qui a pris du retard.** Les conflits s'accumulent : synchronise-le régulièrement avec `upstream`.
- **Accumuler des branches mortes.** Le sélecteur devient illisible. Active la suppression automatique.
- **Créer une release sans tag annoté ni notes.** Les utilisateurs ne savent pas ce qui a changé.
- **Mettre des secrets dans le README ou les exemples.** Utilise des valeurs fictives (`VOTRE_CLE_ICI`).

## Bonnes pratiques

- Soigne le README : c'est la première chose lue, et souvent la seule.
- Ajoute `LICENSE`, `CONTRIBUTING.md` et `SECURITY.md` dès que le dépôt est partagé.
- Protège `main` : PR obligatoire, tests verts, pas de force push.
- Applique le moindre privilège pour les rôles des collaborateurs.
- Supprime les branches après fusion, automatiquement si possible.
- Publie des releases en SemVer, avec des notes lisibles.
- Utilise une organisation pour les projets d'équipe plutôt qu'un dépôt personnel partagé.

## À retenir

- Les fichiers `README.md`, `LICENSE`, `CONTRIBUTING.md`, `SECURITY.md` et le dossier `.github/` structurent un dépôt.
- La **branche par défaut** est la base des PR ; l'interface permet de comparer, créer et supprimer des branches.
- Les **collaborateurs** poussent des branches sur le dépôt ; le **fork** sert aux contributions externes.
- Les rôles (Read, Triage, Write, Maintain, Admin) s'attribuent selon le moindre privilège.
- La **protection de branche** impose PR, relecture, tests verts et interdit le force push.
- Une **release** publie une version taguée, de préférence en SemVer.
