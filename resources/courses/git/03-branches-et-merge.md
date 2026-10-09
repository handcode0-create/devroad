---
title: Branches et merge
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Les branches sont la fonctionnalité qui rend Git irremplaçable. Elles te permettent de travailler sur une nouvelle idée, une correction ou une expérience dans un espace isolé, sans jamais toucher au code stable. Quand le travail est prêt, tu le fusionnes (*merge*) dans la branche principale.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est une branche (et pourquoi c'est très léger dans Git) ;
- créer, lister, changer et supprimer des branches avec `git switch` et `git branch` ;
- fusionner une branche avec `git merge` ;
- distinguer une fusion **fast-forward** d'une fusion avec commit de merge ;
- résoudre un **conflit** simple ;
- mettre du travail de côté avec `git stash` ;
- adopter une convention de nommage de branches.

Prérequis : savoir faire des commits propres (chapitre précédent). Prévois deux heures. Tout se fait en local.

## Qu'est-ce qu'une branche ?

Imagine que tu écris un livre. La version publiée est dans la librairie : tu ne la modifies pas. Pour tester un nouveau chapitre, tu photocopies le manuscrit, tu écris dessus, et si le résultat te plaît, tu l'intègres au livre. Sinon, tu jettes la photocopie.

Une **branche** fait exactement cela, sans copier de fichiers. Techniquement, une branche est un **simple pointeur** (un fichier de 41 octets) vers un commit. Créer une branche est donc instantané, même sur un énorme projet.

```text
          A ── B ── C        ← main
                     \
                      D ── E  ← feature/filtre-niveau
```

Ici, `main` est restée en `C` pendant que `feature/filtre-niveau` avance avec les commits `D` et `E`. Les deux lignes de travail évoluent indépendamment.

Et `HEAD` ? Il indique quelle branche est active : c'est sur elle que se placera ton prochain commit.

> **À retenir** : une branche n'est pas une copie du code, c'est une étiquette mobile sur un commit. C'est pourquoi on peut en créer des dizaines sans effort.

## Créer et changer de branche

Commence par voir où tu es :

```bash
git branch
```

```text
* main
```

L'astérisque indique la branche courante. Pour créer une branche et s'y placer :

```bash
git switch -c feature/filtre-niveau
```

`-c` signifie *create*. Tu peux aussi faire les deux étapes séparément :

```bash
git branch feature/filtre-niveau   # crée, sans s'y placer
git switch feature/filtre-niveau   # se place dessus
```

Pour revenir sur `main` :

```bash
git switch main
```

> **Astuce** : tu trouveras partout la commande plus ancienne `git checkout`. Elle fait la même chose, mais elle sert aussi à d'autres opérations, ce qui prête à confusion. `git switch` (changer de branche) et `git restore` (restaurer des fichiers) sont plus clairs.

Fais l'expérience : crée une branche, ajoute un fichier, commite, puis reviens sur `main` et fais un `ls`. Le fichier a disparu ! Git a remplacé le contenu de ton répertoire de travail par l'état de la branche `main`. Retourne sur la branche, il réapparaît.

Autres commandes utiles :

```bash
git branch -a             # toutes les branches, y compris distantes
git branch -v             # avec le dernier commit de chacune
git branch -m ancien nouveau   # renommer
git branch -d feature/x   # supprimer (refuse si non fusionnée)
git branch -D feature/x   # supprimer de force
```

:::quiz
Qu'est-ce qu'une branche dans Git ?
- [ ] Une copie complète de tous les fichiers du projet
- [ ] Un dossier séparé dans le répertoire de travail
- [x] Un pointeur léger vers un commit, qui avance à chaque nouveau commit
- [ ] Un fichier de sauvegarde compressé
> Une branche est simplement une référence vers un commit. C'est ce qui rend sa création quasi instantanée et gratuite.
:::

## Fusionner avec git merge

Ta fonctionnalité est terminée. Pour l'intégrer à `main`, tu te places sur la branche **qui reçoit** et tu fusionnes l'autre :

```bash
git switch main
git merge feature/filtre-niveau
```

Il existe deux cas de figure.

### Fast-forward : la fusion sans bruit

Si `main` n'a pas bougé depuis la création de la branche, Git n'a rien à combiner : il avance simplement le pointeur `main` jusqu'au dernier commit de la branche.

```text
Avant :   A ── B ── C          ← main
                     \
                      D ── E   ← feature

Après :   A ── B ── C ── D ── E  ← main, feature
```

La sortie contient `Fast-forward`. L'historique reste parfaitement linéaire.

### Merge commit : quand les deux branches ont avancé

Si `main` a reçu de nouveaux commits pendant ce temps, les historiques ont **divergé**. Git crée alors un **commit de fusion** à deux parents :

```text
          A ── B ── C ── F ── G      ← main
                     \         \
                      D ── E ───  M   ← M = commit de merge
```

Git ouvre ton éditeur pour le message (`Merge branch 'feature/filtre-niveau'`). Tu peux forcer ce commit même quand un fast-forward serait possible, ce qui garde la trace de l'existence de la branche :

```bash
git merge --no-ff feature/filtre-niveau
```

Visualise le résultat avec `git log --oneline --graph --all`.

:::quiz
Dans quel cas Git réalise-t-il une fusion « fast-forward » ?
- [ ] Quand il y a un conflit à résoudre
- [x] Quand la branche cible n'a pas de nouveaux commits depuis la création de la branche fusionnée
- [ ] Quand on utilise l'option `--hard`
- [ ] Quand les deux branches ont modifié des fichiers différents
> Si la branche cible est un ancêtre direct de la branche à fusionner, Git se contente d'avancer le pointeur, sans créer de commit de fusion.
:::

## Les conflits de fusion

Un **conflit** survient quand les deux branches ont modifié **la même zone du même fichier** de manière différente. Git ne peut pas deviner laquelle garder. Il te demande de trancher.

Mettons en place un conflit volontaire :

```bash
git switch -c feature/titre
echo "Titre : DevRoad v2" > titre.txt
git add titre.txt && git commit -m "feat: titre v2"

git switch main
echo "Titre : DevRoad Pro" > titre.txt
git add titre.txt && git commit -m "feat: titre pro"

git merge feature/titre
```

```text
Auto-merging titre.txt
CONFLICT (content): Merge conflict in titre.txt
Automatic merge failed; fix conflicts and then commit the result.
```

Ouvre `titre.txt` : Git y a placé des marqueurs.

```text
<<<<<<< HEAD
Titre : DevRoad Pro
=======
Titre : DevRoad v2
>>>>>>> feature/titre
```

- entre `<<<<<<< HEAD` et `=======` : la version de ta branche actuelle ;
- entre `=======` et `>>>>>>>` : la version de la branche fusionnée.

Pour résoudre :

1. Édite le fichier : garde une version, l'autre, ou combine-les, et **supprime les trois lignes de marqueurs**.
2. Ajoute le fichier : `git add titre.txt`.
3. Termine la fusion : `git commit` (Git propose un message prérempli).

Si tu paniques, tu peux tout annuler et revenir à l'état d'avant la fusion :

```bash
git merge --abort
```

> **Astuce** : ton éditeur (VS Code notamment) affiche les conflits avec des boutons « Accept Current », « Accept Incoming » ou « Accept Both ». Cela reste un confort : comprends d'abord ce que les marqueurs signifient.

> **Erreur fréquente** : commiter un fichier qui contient encore des marqueurs `<<<<<<<`. Avant de valider, fais une recherche de `<<<<<<<` dans tout le projet, et lance les tests ou le build.

## Mettre du travail de côté : git stash

Tu es en plein travail, des fichiers modifiés partout, quand un collègue te demande une correction urgente sur `main`. Tu ne veux pas commiter du code inachevé, mais `git switch` risque de refuser. La solution est le **stash**, une pile de modifications mises de côté :

```bash
git stash push -m "filtre en cours"   # range les modifs, répertoire propre
git switch main                        # tu peux travailler ailleurs
# ... correction, commit ...
git switch feature/filtre-niveau
git stash list                         # voir la pile
git stash pop                          # récupère et supprime de la pile
```

`git stash apply` récupère sans supprimer de la pile. Par défaut, seuls les fichiers suivis sont rangés ; ajoute `-u` pour inclure les fichiers non suivis.

## Nommer et organiser ses branches

Une convention simple rend le dépôt lisible par toute l'équipe : un **préfixe** indiquant le type, puis un nom court en minuscules séparé par des tirets.

| Préfixe | Usage | Exemple |
| --- | --- | --- |
| `feature/` | Nouvelle fonctionnalité | `feature/filtre-niveau` |
| `fix/` | Correction de bug | `fix/double-soumission-login` |
| `hotfix/` | Correction urgente en production | `hotfix/erreur-paiement` |
| `docs/` | Documentation | `docs/guide-installation` |
| `chore/` | Maintenance | `chore/maj-dependances` |

Principes à suivre :

- **`main` est toujours stable** : elle doit pouvoir être déployée à tout moment ;
- une branche = un sujet, courte durée de vie (quelques jours au plus) ;
- on supprime la branche après sa fusion : `git branch -d feature/filtre-niveau`.

Plus une branche vit longtemps, plus elle s'éloigne de `main` et plus la fusion sera douloureuse. Fusionne tôt, fusionne souvent.

## Atelier guidé : une fonctionnalité sur sa branche

Compte une heure dans un dépôt `atelier-branches` contenant un `README.md` et un fichier `roadmaps.md` de trois lignes, déjà commités sur `main`.

1. Vérifie ta position avec `git branch` et `git status`.
2. Crée la branche `feature/ajout-niveau` avec `git switch -c`.
3. Ajoute à `roadmaps.md` une colonne « niveau » sur chaque ligne et commite avec un message `feat:` clair.
4. Reviens sur `main` avec `git switch main`. Que contient `roadmaps.md` ? Reviens sur la branche et vérifie.
5. Fusionne la branche dans `main` : `git switch main` puis `git merge feature/ajout-niveau`. Quel type de fusion obtiens-tu ?
6. Affiche `git log --oneline --graph --all`, puis supprime la branche avec `git branch -d`.
7. Provoque un fast-forward impossible : crée `feature/a` et `feature/b` depuis `main`, modifie **la même ligne** de `README.md` dans chacune, commite chaque fois.
8. Fusionne `feature/a` (fast-forward), puis `feature/b` : constate le conflit. Résous-le en gardant une combinaison des deux versions, puis termine la fusion.
9. Recommence le conflit et utilise `git merge --abort` pour vérifier que tu sais faire marche arrière.
10. Teste le stash : modifie un fichier, range-le avec `git stash push -m "test"`, vérifie que `git status` est propre, puis récupère avec `git stash pop`.

Pour t'auto-évaluer : dessine au brouillon le graphe de ton dépôt après l'étape 8, et explique pourquoi il y a un commit de merge à un endroit et pas à l'autre.

## Erreurs fréquentes

- **Commiter sur la mauvaise branche.** Vérifie toujours la branche active avec `git branch` avant de travailler. Si l'erreur est faite en local, déplace le commit avec `git switch -c` sur une nouvelle branche, puis remets `main` en ordre avec `git reset`.
- **Fusionner dans le mauvais sens.** On se place sur la branche qui **reçoit** avant de lancer `git merge`.
- **Laisser vivre une branche des semaines.** Les conflits s'accumulent. Découpe le travail en petites branches.
- **Paniquer devant un conflit.** C'est normal et résoluble. `git merge --abort` te ramène en arrière à tout moment.
- **Oublier de commiter avant de changer de branche.** Utilise le stash plutôt que de perdre ton travail ou de commiter du code inachevé.
- **Supprimer une branche non fusionnée avec `-D`.** Les commits deviennent difficiles à retrouver, sauf avec `git reflog`.

## Bonnes pratiques

- Travaille toujours sur une branche dédiée ; ne commite jamais directement sur `main` pour une fonctionnalité.
- Nomme tes branches avec un préfixe et un nom parlant.
- Garde les branches courtes et centrées sur un seul sujet.
- Mets à jour ta branche avec `main` régulièrement pour réduire les conflits futurs.
- Après une fusion, supprime la branche devenue inutile.
- Relance les tests après chaque résolution de conflit : un conflit résolu n'est pas forcément un conflit bien résolu.

## À retenir

- Une branche est un pointeur léger vers un commit ; `HEAD` indique la branche active.
- `git switch -c nom` crée et change de branche ; `git branch -d nom` la supprime.
- `git merge` se lance depuis la branche **qui reçoit** les changements.
- Fast-forward : historique linéaire, sans commit de merge ; merge commit : quand les historiques ont divergé.
- Un conflit se résout en éditant les marqueurs, puis `git add` et `git commit` ; `git merge --abort` annule.
- `git stash` met de côté un travail inachevé.
- Une convention de nommage (`feature/`, `fix/`, `hotfix/`) garde le dépôt lisible.
