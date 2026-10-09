---
title: Rebase et conflits
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Tu sais fusionner des branches avec `merge`. Mais dans une équipe, l'historique devient vite un enchevêtrement de commits de fusion difficile à lire. Git propose une seconde manière d'intégrer des changements : le **rebase**. Utilisé avec discernement, il produit un historique linéaire et propre. Utilisé n'importe comment, il casse le travail des autres. Ce chapitre t'apprend à faire la différence.

À la fin du chapitre, tu seras capable de :

- expliquer ce que fait `git rebase` et en quoi il diffère de `git merge` ;
- rebaser une branche de fonctionnalité sur `main` ;
- nettoyer tes commits avec le **rebase interactif** (`squash`, `reword`, `fixup`, `drop`) ;
- résoudre des conflits pendant un rebase et reprendre avec `--continue` ;
- déplacer un commit précis avec `git cherry-pick` ;
- appliquer la **règle d'or** : ne jamais rebaser un historique partagé.

Prérequis : branches, merge et conflits simples (chapitre précédent). Prévois deux heures trente. Entraîne-toi dans un dépôt de test : un rebase mal maîtrisé réécrit l'historique.

## Merge ou rebase : deux façons d'intégrer

Reprenons une situation courante. Tu as créé `feature` à partir de `main`, puis `main` a avancé de son côté :

```text
          A ── B ── C ── F ── G      ← main
                     \
                      D ── E          ← feature
```

**Option 1 : merge.** Tu fusionnes `main` dans `feature` (ou l'inverse). Git crée un commit de fusion `M` :

```text
          A ── B ── C ── F ── G
                     \         \
                      D ── E ── M     ← feature
```

L'historique est fidèle à ce qui s'est passé, mais il contient des « losanges » qui s'accumulent.

**Option 2 : rebase.** Git prend tes commits `D` et `E`, les met de côté, déplace le point de départ de la branche au bout de `main`, puis **rejoue** `D` et `E` un par un :

```text
          A ── B ── C ── F ── G
                              \
                               D' ── E'     ← feature
```

Le résultat est une ligne droite. Remarque les apostrophes : `D'` et `E'` sont de **nouveaux commits** (nouveaux identifiants) avec le même contenu. Le rebase ne déplace rien, il **recrée** les commits.

> **À retenir** : merge préserve l'histoire telle qu'elle s'est déroulée ; rebase la réécrit pour qu'elle paraisse linéaire. Les deux produisent le même code final.

## Rebaser une branche sur main

La commande tient en deux lignes :

```bash
git switch feature/filtre-niveau
git rebase main
```

Lis-la comme : « rejoue mes commits par-dessus la dernière version de `main` ». Si tout se passe bien, Git affiche `Successfully rebased and updated`. Vérifie avec :

```bash
git log --oneline --graph --all
```

Ensuite, depuis `main`, la fusion devient un simple fast-forward, sans commit de merge :

```bash
git switch main
git merge feature/filtre-niveau
```

Ce flux (rebase de la branche, puis fast-forward) est très répandu : `main` reste une ligne droite où chaque commit est une étape lisible.

:::quiz
Que se passe-t-il pour les commits d'une branche quand on la rebase sur `main` ?
- [ ] Ils sont déplacés sans changer, avec le même identifiant
- [x] Ils sont rejoués par-dessus `main` et reçoivent de nouveaux identifiants
- [ ] Ils sont supprimés définitivement
- [ ] Ils sont fusionnés en un seul commit automatiquement
> Un commit est identifié par son contenu *et* son parent. Comme le parent change, chaque commit rejoué devient un nouveau commit.
:::

## La règle d'or du rebase

Parce que le rebase **crée de nouveaux commits**, il remplace les anciens. Si quelqu'un d'autre a déjà récupéré les anciens, vos historiques deviennent incompatibles : vous vous retrouvez avec des commits en double, des conflits absurdes et des heures perdues.

**Règle d'or : ne rebase jamais des commits que tu as déjà publiés et que d'autres ont pu récupérer.** En particulier, ne rebase jamais `main`.

Tu peux sans risque rebaser :

- une branche **locale**, que tu n'as jamais poussée ;
- une branche personnelle que tu es seul à utiliser, même poussée (au prix d'un push forcé, voir plus bas).

Le choix pratique : sur une branche partagée, utilise `merge`. Sur ta branche de travail perso, avant de proposer ton travail, utilise `rebase` pour la nettoyer.

## Le rebase interactif : nettoyer avant de partager

C'est ici que le rebase devient un outil d'artisan. En cours de travail, tu produis des commits de brouillon : « wip », « oups », « fix typo », « encore un fix ». Avant de soumettre ta branche, tu veux un historique propre. Lance :

```bash
git rebase -i HEAD~4
```

Git ouvre ton éditeur avec la liste des 4 derniers commits, du plus ancien au plus récent :

```text
pick a1b2c3d feat: ajoute le filtre par niveau
pick b2c3d4e wip
pick c3d4e5f oups j'avais oublié le tri
pick d4e5f6a fix typo

# Commands:
# p, pick   = utiliser le commit tel quel
# r, reword = utiliser le commit mais modifier son message
# s, squash = fusionner avec le commit précédent, en combinant les messages
# f, fixup  = comme squash, mais en jetant le message de ce commit
# d, drop   = supprimer le commit
```

Remplace le mot en début de ligne pour indiquer l'action :

```text
pick a1b2c3d feat: ajoute le filtre par niveau
fixup b2c3d4e wip
fixup c3d4e5f oups j'avais oublié le tri
fixup d4e5f6a fix typo
```

Enregistre et ferme l'éditeur : les quatre commits deviennent un seul, avec le message du premier. Autres usages :

- **`reword`** pour corriger un message sans toucher au contenu ;
- **`drop`** pour supprimer un commit qui n'aurait pas dû exister ;
- **réordonner** les lignes pour changer l'ordre des commits.

> **Astuce** : pour préparer un nettoyage futur, commite avec `git commit --fixup a1b2c3d` : Git marque le commit comme correction de `a1b2c3d`. Ensuite, `git rebase -i --autosquash main` réordonne et fusionne tout seul.

Si quelque chose tourne mal pendant un rebase, tu peux toujours tout annuler :

```bash
git rebase --abort
```

:::quiz
Tu as quatre commits de brouillon à regrouper en un seul avant de partager ta branche. Quelle commande lances-tu ?
- [ ] git merge --squash HEAD~4 sur main
- [ ] git reset --hard HEAD~4
- [x] git rebase -i HEAD~4, puis tu remplaces pick par squash ou fixup sur les trois derniers
- [ ] git revert HEAD~4
> Le rebase interactif permet de réécrire les derniers commits : les marquer en squash ou fixup les fusionne avec le précédent.
:::

## Les conflits pendant un rebase

Un rebase rejoue tes commits un par un. À chaque commit, un conflit peut apparaître. Git s'arrête alors et te laisse la main :

```text
Auto-merging src/Filtre.jsx
CONFLICT (content): Merge conflict in src/Filtre.jsx
error: could not apply a1b2c3d... feat: ajoute le filtre par niveau
hint: Resolve all conflicts manually, mark them as resolved with
hint: "git add/rm <conflicted_files>", then run "git rebase --continue".
```

La procédure, à chaque arrêt :

1. `git status` te montre les fichiers en conflit (« both modified »).
2. Ouvre-les, résous les marqueurs `<<<<<<<`, `=======`, `>>>>>>>` comme dans une fusion.
3. `git add src/Filtre.jsx` pour marquer le conflit comme résolu.
4. `git rebase --continue` pour passer au commit suivant.

Si un autre conflit apparaît sur le commit suivant, recommence. Autres commandes de secours :

```bash
git rebase --skip     # ignore le commit en cours (il disparaît)
git rebase --abort    # annule tout, retour à l'état d'avant
```

Attention à un détail déroutant : pendant un rebase, les rôles sont **inversés**. `HEAD` (la partie « ours ») désigne la branche sur laquelle tu rejoues, par exemple `main`, et la partie « incoming » correspond à **ton** commit en cours de rejeu. Lis donc le contenu avant de choisir.

### Comprendre un conflit plutôt que le subir

Quelques réflexes qui rendent les conflits moins pénibles :

- `git diff --name-only --diff-filter=U` liste les fichiers encore en conflit ;
- `git log --merge -p` montre les commits qui ont touché la zone litigieuse ;
- `git config --global merge.conflictStyle zdiff3` ajoute à chaque conflit la version d'origine, ce qui aide à comprendre qui a changé quoi ;
- active la mémorisation des résolutions avec `git config --global rerere.enabled true` : Git rejouera automatiquement une résolution déjà faite.

> **Attention** : n'accepte jamais « Accept Both » ou « Accept Current » sans relire. Un conflit résolu mécaniquement peut produire du code qui compile mais ne fait plus ce que les deux auteurs voulaient.

## Cherry-pick : prendre un seul commit

Parfois, tu veux récupérer **un** commit d'une autre branche, sans fusionner le reste. Exemple : une correction faite sur `feature/x` dont `main` a besoin tout de suite.

```bash
git switch main
git cherry-pick 8b1d4e7
```

Git rejoue ce commit sur la branche courante, avec un nouvel identifiant. Pour plusieurs commits : `git cherry-pick A..B`. En cas de conflit, la procédure est la même que pour le rebase (`--continue`, `--abort`).

Garde le cherry-pick pour les cas particuliers : il crée des doublons, et Git ne saura pas que les deux commits sont « le même » lors d'une fusion ultérieure.

## Pousser après un rebase

Si tu as déjà poussé ta branche avant de la rebaser, l'historique local et celui du serveur ont divergé. Un `git push` normal sera refusé. Il faut forcer, mais en sécurité :

```bash
git push --force-with-lease
```

`--force-with-lease` refuse d'écraser le serveur si quelqu'un d'autre y a poussé un commit que tu n'as pas vu. Elle est infiniment plus sûre que `--force`. Et sur une branche partagée, la bonne réponse reste : ne pas rebaser du tout.

## Merge ou rebase : comment choisir ?

| Situation | Choix |
| --- | --- |
| Nettoyer mes commits avant une revue | `rebase -i` |
| Mettre à jour ma branche perso avec `main` | `rebase main` |
| Intégrer une branche partagée par plusieurs personnes | `merge` |
| Fusion finale d'une fonctionnalité dans `main` | merge (ou squash merge, vu avec GitHub) |
| Récupérer un seul commit ailleurs | `cherry-pick` |

Il n'y a pas de camp à choisir : beaucoup d'équipes rebasent leurs branches locales puis fusionnent dans `main`.

## Atelier guidé : rebase et nettoyage

Compte une heure trente dans un dépôt `atelier-rebase`, avec deux ou trois commits sur `main`.

1. Crée `feature/filtre` depuis `main` et fais quatre petits commits : un vrai (`feat: ajoute le filtre`), puis `wip`, `oups`, `fix typo`.
2. Reviens sur `main` et fais deux commits indépendants (par exemple dans `README.md`).
3. Affiche le graphe avec `git log --oneline --graph --all` : tu dois voir deux lignes qui divergent.
4. Reviens sur `feature/filtre` et lance `git rebase main`. Relis le graphe : l'historique est-il maintenant linéaire ? Compare les identifiants des commits avant et après.
5. Lance `git rebase -i HEAD~4` et transforme les trois commits de brouillon en `fixup`. Vérifie le résultat avec `git log --oneline`.
6. Modifie le message du commit restant avec `reword` en relançant un rebase interactif.
7. Prépare un conflit : modifie la même ligne de `README.md` sur `main` et sur `feature/filtre`. Lance `git rebase main`, résous le conflit, puis `git add` et `git rebase --continue`.
8. Relance le même scénario et teste `git rebase --abort` pour voir que tout revient à l'état initial.
9. Crée un commit sur une autre branche, puis ramène-le sur `main` avec `git cherry-pick`.
10. Active `rerere` et provoque deux fois le même conflit pour observer la résolution automatique.

Pour t'auto-évaluer : explique à un camarade (ou à voix haute) pourquoi on ne rebase pas `main`, et ce que signifie le fait que le rebase « recrée » les commits.

## Erreurs fréquentes

- **Rebaser une branche partagée.** C'est l'erreur n°1 : les collègues se retrouvent avec des commits en double et des conflits en cascade.
- **Utiliser `git push --force` au lieu de `--force-with-lease`.** Tu risques d'écraser le travail de quelqu'un.
- **Résoudre un conflit en se trompant de côté.** Pendant un rebase, « ours » et « theirs » sont inversés par rapport à un merge.
- **Oublier `git add` avant `git rebase --continue`.** Git refuse de continuer tant que le conflit n'est pas marqué comme résolu.
- **Rebase interactif sans plan.** Supprimer une ligne dans l'éditeur supprime le commit. Si tu as un doute, `git rebase --abort`.
- **Rebaser très loin en arrière.** Plus tu remontes, plus tu rejoues de conflits potentiels.

## Bonnes pratiques

- Rebase uniquement ce qui n'est pas encore partagé ; sinon, merge.
- Nettoie ta branche (`rebase -i`) juste avant de demander une relecture.
- Garde un commit par idée : regroupe les brouillons, sépare les sujets.
- Utilise `--force-with-lease`, jamais `--force`.
- Avant un rebase risqué, crée une branche de sauvegarde : `git branch backup`. Tu pourras y revenir.
- Active `rerere` et `zdiff3` pour rendre les conflits plus lisibles.
- Relance les tests après un rebase : un rebase réussi n'implique pas un code correct.

## À retenir

- `git rebase main` rejoue tes commits au-dessus de `main` pour un historique linéaire.
- Un rebase crée de **nouveaux commits** : il réécrit l'historique.
- Règle d'or : ne jamais rebaser des commits déjà partagés.
- `git rebase -i` permet de `squash`, `fixup`, `reword` et `drop` pour nettoyer ses commits.
- En cas de conflit : résoudre, `git add`, `git rebase --continue` ; ou `--abort` pour tout annuler.
- `git cherry-pick` reprend un commit isolé sur la branche courante.
- Après un rebase d'une branche déjà poussée : `git push --force-with-lease`.
