---
title: Projet final Git
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru tout le parcours : init, commits, branches, merge, rebase, remotes, workflows. Il est temps de tout assembler dans un projet réaliste. Tu vas simuler la vie d'une petite équipe qui développe et livre un produit, de la création du dépôt à la publication de deux versions, en passant par un conflit, un hotfix et un bug à retrouver.

À la fin du projet, tu seras capable de :

- structurer un dépôt professionnel dès le premier commit ;
- appliquer un workflow complet (branches courtes, revues, fusions propres) ;
- produire un historique lisible grâce aux Conventional Commits et au rebase interactif ;
- résoudre des conflits en situation réelle ;
- livrer des versions taguées en SemVer et gérer un hotfix ;
- diagnostiquer un bug avec `git bisect` ;
- documenter ta méthode pour qu'une équipe puisse la reprendre.

Prérequis : les six chapitres précédents. Durée : **six heures**, à répartir sur une ou deux sessions. Il te faut un compte sur une plateforme d'hébergement (GitHub ou GitLab), une clé SSH configurée, et idéalement un camarade pour jouer le rôle d'un second développeur ; à défaut, tu joueras les deux rôles avec deux dossiers de travail.

## Le contexte du projet

Tu es le développeur principal de **MiniRoad**, une mini-application qui liste des parcours d'apprentissage (roadmaps), version simplifiée de DevRoad. Pour garder le focus sur Git, l'application est volontairement minuscule : une page HTML statique et un script JavaScript. Tu peux utiliser n'importe quelle technologie si tu préfères, à condition de pouvoir lancer un test simple.

```text
miniroad/
├── .githooks/
│   └── commit-msg
├── .gitattributes
├── .gitignore
├── CONTRIBUTING.md
├── README.md
├── CHANGELOG.md
├── index.html
├── src/
│   ├── roadmaps.js
│   └── filtre.js
└── tests/
    └── roadmaps.test.js
```

Le projet n'a aucun intérêt fonctionnel en soi : ce qui compte, c'est l'**historique** que tu vas produire. Il sera évalué comme un livrable à part entière.

## Cahier des charges

### Fonctionnalités à développer (via Git)

1. **Version 0.1.0** : la page affiche une liste de roadmaps codée en dur (au moins quatre éléments avec nom, niveau et nombre de chapitres).
2. **Version 0.2.0** : un filtre par niveau (débutant, intermédiaire, professionnel) et une barre de recherche par nom.
3. **Version 0.2.1** : un correctif urgent (hotfix) sur un bug en production, volontairement introduit par ton « collègue ».
4. **Version 0.3.0** : un tri alphabétique et une mention « terminée » pour chaque roadmap.

### Contraintes sur le dépôt

- Nom des branches : `feature/…`, `fix/…`, `hotfix/…`, `docs/…`, `chore/…`.
- Messages de commit au format **Conventional Commits**, rédigés en français.
- `main` toujours stable : aucun commit direct après le commit initial, tout passe par des branches.
- Historique de `main` **linéaire** : fusions en fast-forward ou squash, aucun commit de merge inutile.
- Chaque version livrée porte un **tag annoté** SemVer.
- Un hook `commit-msg` vérifie le format des messages.
- `CHANGELOG.md` mis à jour à chaque version.

> **À retenir** : le projet est réussi quand quelqu'un qui découvre ton dépôt comprend, rien qu'avec `git log --oneline --graph`, ce qui s'est passé et pourquoi.

:::quiz
Parmi ces choix, lequel garantit le mieux un historique de `main` linéaire et lisible ?
- [ ] Commiter directement sur `main` pour aller plus vite
- [x] Rebaser sa branche sur `main`, nettoyer les commits de brouillon, puis fusionner en fast-forward
- [ ] Fusionner sans cesse `main` dans sa branche avec des commits de merge
- [ ] Forcer le push de `main` après chaque modification
> Rebaser puis fusionner en fast-forward produit une ligne droite. Les commits de brouillon sont regroupés avant le partage.
:::

## Phase 1 : initialiser un dépôt professionnel (40 min)

Avant toute ligne de code, le dépôt doit être sain.

```bash
mkdir miniroad && cd miniroad
git init
git config core.hooksPath .githooks
```

Crée les fichiers de base :

- `.gitignore` : `node_modules/`, `.env`, `.DS_Store`, `.idea/`, `.vscode/`, `dist/`.
- `.gitattributes` : règle de fins de ligne text=auto avec eol=lf, comme au chapitre précédent.
- `README.md` : description, lancement du projet, lien vers `CONTRIBUTING.md`.
- `CONTRIBUTING.md` : règles de branches, format des commits, procédure de revue et de livraison.

Écris le hook `.githooks/commit-msg`. Il reçoit en argument le fichier contenant le message :

```bash
#!/bin/sh
motif='^(feat|fix|docs|style|refactor|test|chore)(\([a-z0-9-]+\))?!?: .{3,}'
if ! head -n 1 "$1" | grep -Eq "$motif"; then
  echo "Message refuse. Format attendu : type(portee): resume"
  echo "Types : feat, fix, docs, style, refactor, test, chore"
  exit 1
fi
```

```bash
chmod +x .githooks/commit-msg
git add .
git commit -m "chore: initialise le projet"
git commit --allow-empty -m "nimporte quoi"   # doit être refusé
```

Crée ensuite le dépôt vide sur la plateforme, relie-le et pousse :

```bash
git remote add origin git@github.com:TON-COMPTE/miniroad.git
git branch -M main
git push -u origin main
```

Active ensuite sur la plateforme la protection de `main` si elle est disponible : pousser directement interdit, historique linéaire exigé.

## Phase 2 : livrer la version 0.1.0 (45 min)

Tu travailles uniquement par branches.

```bash
git switch -c feature/liste-roadmaps
```

1. Crée `index.html` et `src/roadmaps.js` avec la liste des roadmaps.
2. Fais **au moins trois commits** distincts : structure HTML, données, affichage. Utilise `git add -p` si tu as modifié plusieurs choses à la fois.
3. Simule du désordre : ajoute un commit `wip` et un commit `oups`.
4. Nettoie avant de partager :

```bash
git rebase -i main
```

Fusionne `wip` et `oups` dans les commits concernés avec `fixup`, reformule un message avec `reword` si besoin.

5. Pousse la branche, simule la revue (relis `git diff main...feature/liste-roadmaps`), puis intègre :

```bash
git switch main
git merge --ff-only feature/liste-roadmaps
git push
git branch -d feature/liste-roadmaps
git push origin --delete feature/liste-roadmaps
```

6. Mets à jour `CHANGELOG.md`, commite sur une branche `docs/changelog-0-1-0`, intègre, puis tague :

```bash
git tag -a v0.1.0 -m "Première version : liste des roadmaps"
git push origin v0.1.0
```

## Phase 3 : travailler à deux et gérer un conflit (75 min)

Pour la version 0.2.0, deux développeurs interviennent en parallèle. Si tu es seul, clone ton dépôt dans un second dossier `miniroad-collegue` : il jouera ton collègue.

**Toi** : branche `feature/filtre-niveau`, tu crées `src/filtre.js` et tu modifies `index.html` pour ajouter un menu déroulant.

**Ton collègue** : branche `feature/recherche`, il ajoute une barre de recherche dans **le même emplacement** de `index.html`. Les deux branches modifient donc les mêmes lignes.

Étapes :

1. Chacun fait deux ou trois commits propres sur sa branche et pousse.
2. Le collègue intègre en premier : sa branche est rebasée, fusionnée en fast-forward dans `main` et poussée.
3. Toi, tu mets à jour ta branche :

```bash
git fetch origin
git rebase origin/main
```

4. Un **conflit** apparaît dans `index.html`. Résous-le en conservant **les deux** fonctionnalités : le menu de filtre et la barre de recherche. Puis :

```bash
git add index.html
git rebase --continue
```

5. Vérifie à la main que la page fonctionne, relance les tests, puis `git push --force-with-lease` (ta branche a été réécrite).
6. Fusionne dans `main` en fast-forward, tague `v0.2.0`, mets à jour le changelog.

Active `rerere` avant de commencer, et observe-le si tu rejoues le scénario.

> **Astuce** : écris dans `CONTRIBUTING.md` la règle de qui intègre en premier et qui se resynchronise. Un conflit est moins un accident qu'un signal de coordination.

## Phase 4 : le hotfix (45 min)

Ton collègue, en tentant d'optimiser, introduit un bug dans la recherche : taper une majuscule ne trouve plus rien. Il passe sans être vu, la version est déjà en production avec le tag `v0.2.0`.

1. Simule le bug : avant de créer le tag `v0.2.0`, fais intégrer par ton collègue un commit `refactor(recherche): simplifie la comparaison` qui supprime le passage en minuscules, puis ajoute trois autres commits sans rapport. Le bug est ainsi présent dans la version livrée.
2. Reçois le « rapport utilisateur » : *la recherche ne fonctionne pas avec une majuscule*.
3. Retrouve le commit fautif sans lire tout le code :

```bash
git bisect start
git bisect bad HEAD
git bisect good v0.1.0
# teste, puis : git bisect good / git bisect bad
git bisect reset
```

Si tu as écrit un test (`tests/roadmaps.test.js`), automatise avec `git bisect run node tests/roadmaps.test.js`.

4. Utilise `git blame -L` et `git show` pour comprendre l'intention du commit fautif.
5. Corrige dans une branche `hotfix/recherche-majuscules` partie de `main`, avec un commit `fix(recherche): ignore la casse`. Ajoute un test.
6. Intègre, tague `v0.2.1` avec un message précis, pousse le tag.

:::quiz
Lors d'un hotfix, quelle attitude est la plus professionnelle ?
- [ ] Profiter de l'occasion pour refactorer tout le module concerné
- [ ] Corriger directement sur `main` sans test
- [x] Créer une branche `hotfix/…` depuis `main`, faire la correction minimale avec un test, puis tagger un correctif
- [ ] Supprimer le commit fautif de l'historique avec un push forcé
> Un hotfix doit être minimal, testé et traçable. Réécrire l'historique partagé ou élargir le périmètre multiplie les risques.
:::

## Phase 5 : la version 0.3.0 et le nettoyage (50 min)

1. Crée `feature/tri-alphabetique` et `feature/statut-terminee`. Développe-les en parallèle (deux branches, deux jeux de commits).
2. Sur l'une, fais exprès un commit incorrect, puis annule-le avec `git revert` plutôt qu'un reset, et explique pourquoi dans le message.
3. Utilise `git stash` pour interrompre l'une des deux fonctionnalités et traiter une petite correction de documentation sur une branche `docs/…`.
4. Intègre les deux fonctionnalités dans `main` (rebase, nettoyage, fast-forward). Utilise `git cherry-pick` pour ramener un seul commit utile d'une branche abandonnée.
5. Mets à jour `CHANGELOG.md`, tague `v0.3.0`.
6. Fais le ménage : supprime les branches fusionnées en local et sur le serveur, lance `git fetch --prune`, vérifie avec `git branch -a`.

## Phase 6 : audit et livraison (45 min)

Passe ton dépôt au crible comme le ferait un relecteur extérieur.

```bash
git log --oneline --graph --all --decorate
git tag -n
git shortlog -sn
git log --format="%h %s" | head -40
```

Vérifie en particulier :

- aucun message du type « wip », « oups », « test » ;
- aucun fichier secret ou généré dans l'historique (`git log --all --stat | grep -i env`) ;
- l'historique de `main` est linéaire ;
- chaque tag pointe sur un commit où le projet fonctionne.

Termine en rédigeant la section « Historique et méthode » du README : workflow choisi, justification, procédure de hotfix, et commandes utiles.

## Atelier guidé : résumé du parcours

Voici l'enchaînement à suivre, avec une estimation de temps pour garder le rythme.

1. Phase 1 : dépôt, hook, charte (40 min).
2. Phase 2 : v0.1.0, rebase interactif, premier tag (45 min).
3. Phase 3 : travail à deux, conflit, v0.2.0 (75 min).
4. Phase 4 : bug, `bisect`, hotfix, v0.2.1 (45 min).
5. Phase 5 : v0.3.0, revert, stash, cherry-pick, nettoyage (50 min).
6. Phase 6 : audit, README, relecture finale (45 min).
7. Marge, essais et reprise d'erreurs : le reste du temps.

Si tu bloques sur une manipulation, ne supprime pas le dépôt : commence par `git status`, `git reflog` et `git log --oneline --graph --all`. Presque tout est récupérable.

## Checklist d'acceptation

Ton projet est terminé quand tu peux cocher chacun de ces points.

**Structure**

- Le dépôt contient `.gitignore`, `.gitattributes`, `README.md`, `CONTRIBUTING.md` et `CHANGELOG.md`.
- Un hook `commit-msg` versionné rejette un message mal formé.
- Aucun secret, `node_modules` ou fichier généré n'a été commité.

**Historique**

- Tous les messages respectent Conventional Commits.
- Chaque commit représente un changement logique unique.
- L'historique de `main` est linéaire : aucun commit de merge superflu.
- Aucun commit de brouillon (`wip`, `oups`) ne subsiste.

**Branches et versions**

- Toutes les fonctionnalités ont été développées sur des branches nommées selon la convention.
- Les tags `v0.1.0`, `v0.2.0`, `v0.2.1` et `v0.3.0` existent, sont annotés et poussés.
- Les branches fusionnées ont été supprimées en local et à distance.

**Compétences démontrées**

- Un conflit réel a été résolu pendant un rebase, avec les deux fonctionnalités conservées.
- `git bisect` a permis d'identifier le commit fautif du bug.
- Un `git revert` et un `git cherry-pick` figurent dans l'historique.
- Aucun `push --force` brut : seulement `--force-with-lease`, et uniquement sur tes branches.

**Documentation**

- Le README décrit le workflow, le choix de ce workflow et la procédure de hotfix.
- Le changelog liste les quatre versions avec leurs changements.

Pour t'auto-évaluer, reprends chaque point de la checklist et note : acquis, à revoir, ou non traité. Pour les points « à revoir », retourne au chapitre correspondant.

## Erreurs fréquentes

- **Commencer à coder avant d'avoir mis le dépôt en ordre.** Un `.gitignore` oublié se paye ensuite en nettoyage d'historique.
- **Fusionner des branches sans les rebaser.** L'historique se remplit de commits de merge que la contrainte linéaire interdisait.
- **Tester le hook avec un message valide uniquement.** Vérifie aussi qu'il refuse un mauvais message.
- **Oublier de pousser les tags.** Le tag local n'est pas une livraison.
- **Résoudre le conflit en ne gardant qu'une version.** On perd la fonctionnalité de l'autre développeur.
- **Corriger le bug de la phase 4 sans test.** Il reviendra.
- **Réécrire l'historique de `main`.** Une fois publiée, on ne la modifie plus : on la corrige avec de nouveaux commits.

## Bonnes pratiques

- Mets en place les garde-fous (hook, protection de branche, charte) avant le premier développement.
- Un sujet, une branche, une demande de revue.
- Relis ton `git diff main...ta-branche` comme si tu étais le relecteur.
- Écris le changelog au fil de l'eau plutôt qu'à la fin.
- Sauvegarde avant les manipulations risquées : `git branch backup-avant-rebase`.
- Documente tes choix : un dépôt se lit plus souvent qu'il ne s'écrit.
- Garde ce projet comme modèle de départ pour tes futurs dépôts : copie la charte, le hook et les fichiers de configuration.

## À retenir

- Un dépôt professionnel commence par des fichiers de cadrage : `.gitignore`, `.gitattributes`, charte de contribution, hook de messages.
- Le cycle complet : branche courte, commits propres, rebase interactif, revue, fusion en fast-forward, tag.
- Les conflits se résolvent en conservant l'intention de chacun, puis en testant.
- `git bisect`, `git blame` et un bon historique réduisent le temps de diagnostic d'un bug.
- Un hotfix est minimal, testé, tagué en SemVer ; `revert` annule sans réécrire l'historique partagé.
- La qualité d'un dépôt se juge à la lisibilité de son historique, pas au volume de code.
