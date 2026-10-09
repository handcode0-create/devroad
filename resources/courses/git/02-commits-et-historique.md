---
title: Commits et historique
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Le commit est l'unité de base de Git : une photographie de ton projet, accompagnée d'un message qui explique pourquoi elle existe. Bien commiter est un savoir-faire à part entière. Un historique bien tenu te permet de retrouver un bug en quelques minutes ; un historique bâclé (« fix », « modif », « ok ») ne sert à rien.

À la fin du chapitre, tu seras capable de :

- créer des commits avec `git add` et `git commit` ;
- rédiger de bons messages, en suivant la convention **Conventional Commits** ;
- consulter l'historique avec `git log` et ses options ;
- comparer des versions avec `git diff` et `git show` ;
- annuler des modifications avec `git restore`, `git reset` et `git revert` ;
- corriger le dernier commit avec `git commit --amend`.

Prérequis : le chapitre « Comprendre Git » (les trois zones, `git status`). Prévois deux heures. Tu peux t'entraîner dans un dépôt vide créé pour l'occasion.

## Faire un commit

Reprenons le cycle vu au chapitre précédent. Dans un dépôt neuf :

```bash
echo "# DevRoad" > README.md
git add README.md
git commit -m "docs: ajoute le README"
```

Git répond par un résumé :

```text
[main (root-commit) 3f2a9c1] docs: ajoute le README
 1 file changed, 1 insertion(+)
 create mode 100644 README.md
```

Tu y lis la branche (`main`), l'identifiant court du commit (`3f2a9c1`), le message et les statistiques. Sans l'option `-m`, Git ouvre ton éditeur pour que tu écrives un message plus long.

### Ajouter plusieurs fichiers

```bash
git add fichier.txt          # un fichier précis
git add src/                 # un dossier entier
git add .                    # tout le répertoire courant (hors fichiers ignorés)
git add -p                   # choisir morceau par morceau
```

L'option `-p` (*patch*) est un outil précieux : Git te montre chaque bloc modifié et te demande `y` (oui), `n` (non) ou `s` (découper). Tu peux ainsi séparer deux modifications faites dans le même fichier.

### Raccourci : -a

```bash
git commit -am "fix: corrige le calcul de progression"
```

L'option `-a` ajoute automatiquement les fichiers **déjà suivis** et modifiés, mais ignore les nouveaux fichiers. Pratique, mais elle t'empêche de relire ce que tu commites : utilise-la avec prudence.

> **Attention** : avant chaque commit, lance `git status` et `git diff --staged`. C'est la seule façon de savoir exactement ce que tu enregistres.

## Écrire un bon message de commit

Un message de commit répond à la question : **pourquoi** ce changement existe. Dans six mois, c'est la première chose que tu liras en cherchant l'origine d'un bug.

Structure recommandée :

```text
type(portée): résumé court à l'impératif (50 caractères environ)

Explication facultative : pourquoi ce changement, quel problème il
résout, ce qu'il faut savoir. Lignes de 72 caractères maximum.
```

La convention **Conventional Commits** standardise le début du message avec un type :

| Type | Usage |
| --- | --- |
| `feat` | Nouvelle fonctionnalité |
| `fix` | Correction de bug |
| `docs` | Documentation uniquement |
| `style` | Mise en forme sans changement de logique |
| `refactor` | Réécriture sans changement de comportement |
| `test` | Ajout ou modification de tests |
| `chore` | Maintenance, dépendances, configuration |

Exemples sur DevRoad :

```text
feat(roadmaps): ajoute le filtre par niveau
fix(auth): empêche la double soumission du formulaire de connexion
refactor(models): extrait le calcul de progression dans un scope
chore(deps): met à jour react vers 18.3
```

Mauvais messages à éviter : `modif`, `ok`, `fix`, `update`, `wip`, `truc`. Ils ne disent rien.

Règle d'or : **un commit = un changement logique**. Si ton message contient « et », tu as probablement deux commits. Un bon commit est assez petit pour être relu en quelques minutes, et assez complet pour que le projet fonctionne après lui.

:::quiz
Parmi ces messages de commit, lequel respecte le mieux les bonnes pratiques ?
- [ ] modif
- [ ] J'ai changé plein de choses dans le projet aujourd'hui
- [x] fix(auth): empêche la double soumission du formulaire de connexion
- [ ] update
> Un bon message est précis, indique le type de changement et son contexte. Il permet de comprendre l'intention sans ouvrir le code.
:::

## Consulter l'historique avec git log

Une fois plusieurs commits faits, `git log` les affiche du plus récent au plus ancien :

```bash
git log
```

```text
commit 8b1d4e7a9c0f2d3e4f5a6b7c8d9e0f1a2b3c4d5e (HEAD -> main)
Author: Awa Kouassi <awa@example.com>
Date:   Mon Oct 5 10:12:44 2026 +0000

    feat(roadmaps): ajoute le filtre par niveau

commit 3f2a9c1...
```

Ce format est verbeux. Voici les options que tu utiliseras vraiment :

```bash
git log --oneline                 # un commit par ligne
git log --oneline --graph --all   # avec le dessin des branches
git log -5                        # les 5 derniers
git log --author="Awa"            # filtrer par auteur
git log --since="2 weeks ago"     # filtrer par date
git log --grep="auth"             # chercher dans les messages
git log -p fichier.php            # voir les changements d'un fichier
git log --stat                    # fichiers touchés par commit
```

Crée un alias pour ne pas retaper la commande la plus utile :

```bash
git config --global alias.lg "log --oneline --graph --all --decorate"
git lg
```

### HEAD, le curseur

Tu as vu `HEAD -> main` dans la sortie. **HEAD** est un pointeur : il désigne le commit sur lequel tu te trouves actuellement. Presque toujours, il pointe sur la branche courante, qui pointe sur le dernier commit. On désigne les commits relativement à HEAD :

- `HEAD` : le commit actuel ;
- `HEAD~1` ou `HEAD^` : son parent ;
- `HEAD~3` : trois commits en arrière.

## Comparer : git diff et git show

`git diff` montre ce qui a changé, ligne par ligne. Tout dépend de la zone comparée :

```bash
git diff                  # répertoire de travail vs index (non staged)
git diff --staged         # index vs dernier commit (ce que tu vas commiter)
git diff HEAD~2 HEAD      # entre deux commits
git diff main feature     # entre deux branches
```

Un extrait de sortie :

```text
@@ -3,4 +3,5 @@ function progression($total, $fait)
-    return $fait / $total;
+    if ($total === 0) return 0;
+    return round($fait / $total * 100);
```

Les lignes précédées de `-` ont été supprimées, celles avec `+` ajoutées. Pour inspecter un seul commit :

```bash
git show 3f2a9c1
git show HEAD~1 --stat
```

> **Astuce** : `git diff` ne montre pas les fichiers nouveaux non suivis. Ajoute-les d'abord avec `git add -N fichier` (intent to add) si tu veux les voir apparaître.

:::quiz
Quelle commande affiche les modifications que tu as déjà ajoutées à l'index, donc ce qui sera inclus dans le prochain commit ?
- [ ] git diff
- [x] git diff --staged
- [ ] git log --stat
- [ ] git status -s
> `git diff` seul compare le répertoire de travail à l'index. Avec `--staged`, il compare l'index au dernier commit, c'est-à-dire exactement le contenu du futur commit.
:::

## Annuler : choisir le bon outil

Se tromper est normal. L'important est de savoir quel outil utiliser selon **où** se trouve l'erreur.

### Annuler une modification non commitée

Tu as modifié un fichier et tu veux le remettre dans son état du dernier commit :

```bash
git restore fichier.php
```

> **Attention** : cette opération est **définitive**. Les modifications non commitées n'étant dans aucun historique, Git ne peut pas les récupérer.

### Retirer un fichier de l'index

Tu as fait `git add` trop vite ? Le fichier reste modifié, mais n'est plus prêt à être commité :

```bash
git restore --staged fichier.php
```

### Corriger le dernier commit : --amend

Faute de frappe dans le message, ou fichier oublié ? Tant que tu n'as pas partagé le commit :

```bash
git add fichier-oublie.php
git commit --amend -m "feat(roadmaps): ajoute le filtre par niveau"
```

`--amend` ne crée pas de nouveau commit : il **remplace** le dernier par une version corrigée (avec un nouvel identifiant).

### Reculer dans l'historique : reset

`git reset` déplace la branche vers un commit antérieur. Trois modes selon ce qu'on fait des changements :

| Commande | Historique | Index | Fichiers |
| --- | --- | --- | --- |
| `git reset --soft HEAD~1` | recule | gardé | gardés |
| `git reset --mixed HEAD~1` (défaut) | recule | vidé | gardés |
| `git reset --hard HEAD~1` | recule | vidé | **supprimés** |

Cas d'usage : tu as fait trois mauvais commits en local et tu veux tout refaire proprement, tout en gardant ton code : `git reset --soft HEAD~3`, puis tu recommits correctement.

### Annuler sans réécrire : revert

Si le commit a déjà été **partagé** avec d'autres, ne réécris pas l'historique. Utilise `git revert`, qui crée un **nouveau commit** inversant les effets de l'ancien :

```bash
git revert 3f2a9c1
```

L'historique garde la trace de l'erreur *et* de sa correction, ce qui est sain pour un travail d'équipe.

> **Erreur fréquente** : utiliser `git reset --hard` sur des commits déjà publiés. Les collègues qui les ont récupérés se retrouvent avec un historique incompatible. Pour un commit publié : `revert`, toujours.

## Retrouver un commit « perdu » : reflog

Même après un `reset --hard`, un commit n'est pas immédiatement supprimé. Git garde un journal de tous les déplacements de HEAD :

```bash
git reflog
```

```text
8b1d4e7 HEAD@{0}: reset: moving to HEAD~1
a1c2d3e HEAD@{1}: commit: feat: ajoute la page profil
```

Pour récupérer le commit perdu : `git reset --hard a1c2d3e`. Le reflog est un filet de sécurité local, conservé environ 90 jours. Il ne sauve cependant pas les modifications qui n'ont **jamais** été commitées : voilà pourquoi on commite souvent.

## Supprimer, renommer, déplacer

Pour que Git suive correctement ces opérations, passe par lui :

```bash
git rm ancien.php             # supprime et indexe la suppression
git mv ancien.php nouveau.php # renomme et indexe le renommage
```

Si tu renommes un fichier à la main, Git le détecte aussi (il compare les contenus), mais `git mv` évite une étape.

## Atelier guidé : écrire un historique lisible

Compte une heure dans un nouveau dépôt `atelier-commits`.

1. Initialise le dépôt, crée un `README.md` et un `.gitignore`, puis fais un premier commit `chore: initialise le projet`.
2. Crée `roadmaps.md` avec trois lignes (une par roadmap). Commite avec `feat: ajoute la liste des roadmaps`.
3. Modifie à la fois `README.md` (une phrase) et `roadmaps.md` (une ligne ajoutée). Utilise `git add -p` pour ne commiter **que** la modification de `roadmaps.md`, puis commite le README séparément.
4. Affiche l'historique avec `git log --oneline --stat`. Chaque commit touche-t-il bien un seul sujet ?
5. Fais volontairement une faute dans le message de ton dernier commit, puis corrige-la avec `git commit --amend`.
6. Modifie `roadmaps.md` sans l'ajouter, regarde `git diff`, puis annule avec `git restore`.
7. Fais un mauvais commit, puis annule-le avec `git reset --soft HEAD~1` et recommite correctement.
8. Fais un autre commit, puis annule-le avec `git revert HEAD`. Observe `git log --oneline` : que remarques-tu par rapport au reset ?
9. Lance `git reset --hard HEAD~1` puis récupère le commit perdu grâce à `git reflog`.

Pour t'auto-évaluer : sans aide, dis quelle commande tu choisirais dans chacun de ces cas : fichier modifié à jeter, fichier ajouté à l'index par erreur, dernier commit avec une faute, commit déjà poussé à annuler.

## Erreurs fréquentes

- **Commits fourre-tout.** Un seul commit « travail du jour » avec vingt fichiers est impossible à relire ou à annuler partiellement.
- **Messages vagues.** `fix` ne dit pas quoi, ni pourquoi.
- **`git add .` aveugle.** Tu peux embarquer un fichier de debug ou un secret. Relis avec `git status` et `git diff --staged`.
- **`reset --hard` imprudent.** Il détruit les modifications non commitées sans filet.
- **Amender un commit déjà poussé.** Cela réécrit l'historique partagé et crée des conflits pour les autres.
- **Commiter du code cassé.** Chaque commit devrait laisser le projet dans un état qui fonctionne.

## Bonnes pratiques

- Commite souvent, par petits morceaux cohérents.
- Suis la convention Conventional Commits : elle rend l'historique lisible et peut alimenter un changelog automatique.
- Écris le message à l'impératif ou au présent (« ajoute », « corrige »), toujours dans la même langue pour tout le projet.
- Relis `git diff --staged` avant chaque commit.
- Pour annuler : `restore` pour le non commité, `amend` ou `reset` pour le local, `revert` pour le partagé.
- Garde un alias `git lg` pour visualiser l'historique en un coup d'œil.

## À retenir

- Un commit est un instantané du projet, accompagné d'un message qui explique **pourquoi**.
- `git add -p` permet de composer des commits précis à partir d'un fichier modifié à plusieurs endroits.
- `git log --oneline --graph --all` donne la vue d'ensemble ; `git diff` et `git show` détaillent les changements.
- `HEAD` est le pointeur sur le commit courant ; `HEAD~1` désigne son parent.
- Annuler : `restore` (fichiers), `commit --amend` (dernier commit), `reset` (local), `revert` (partagé).
- `git reflog` permet de retrouver des commits qui semblent perdus.
