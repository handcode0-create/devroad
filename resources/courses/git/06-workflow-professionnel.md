---
title: Workflow professionnel
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Connaître les commandes ne suffit pas : une équipe a besoin de **règles communes** sur la manière de les utiliser. Quelle branche pour quoi ? Comment livrer une version ? Que faire d'un bug en production ? Ce chapitre présente les workflows Git utilisés en entreprise et les outils qui les font respecter.

À la fin du chapitre, tu seras capable de :

- comparer les trois grands workflows : **GitHub Flow**, **Git Flow** et **trunk-based development** ;
- choisir un workflow adapté à la taille et au rythme de ton équipe ;
- versionner une application avec les **tags** et le **versionnement sémantique** ;
- gérer un **hotfix** sans bloquer le développement ;
- automatiser des contrôles locaux avec les **hooks Git** ;
- retrouver l'origine d'un bug avec `git bisect` et `git blame` ;
- configurer des alias et un fichier `.gitattributes` pour fiabiliser le dépôt.

Prérequis : les cinq chapitres précédents. Prévois deux heures trente. Les exemples s'appuient sur DevRoad, une application Laravel + React.

## Pourquoi un workflow ?

Sans règles, chacun invente sa propre manière de travailler. Résultat : des commits directs sur `main`, des branches qui traînent, des déploiements qui cassent. Un **workflow** répond à trois questions :

1. **Où** le code en cours de développement vit-il ?
2. **Comment** une modification arrive-t-elle dans la branche stable ?
3. **Quand** et comment une version est-elle livrée ?

Il n'existe pas de workflow universel. Le bon choix dépend de la taille de l'équipe, de la fréquence des livraisons et du niveau d'automatisation des tests.

## GitHub Flow : simple et rapide

C'est le workflow le plus répandu pour les applications web déployées en continu. Il tient en six règles :

1. `main` est **toujours déployable**.
2. Pour tout travail, tu crées une branche à partir de `main`, avec un nom explicite.
3. Tu commites régulièrement et tu pousses la branche.
4. Tu ouvres une **demande de fusion** (*pull request*) pour obtenir une relecture.
5. Après approbation et tests verts, tu fusionnes dans `main`.
6. Tu déploies `main`, puis tu supprimes la branche.

```text
main      ●────────●────────●────────●──────────▶
           \      /          \      /
feature     ●──●──●            ●──●─●
```

Avantages : peu de règles, aucun surcoût. Limite : il suppose que `main` peut être déployée à tout moment, donc des tests automatisés fiables et une seule version en production.

## Git Flow : plusieurs versions en parallèle

Git Flow structure le travail avec plusieurs branches permanentes. Il convient aux produits livrés par versions numérotées (applications installées, bibliothèques avec support de plusieurs versions).

| Branche | Rôle |
| --- | --- |
| `main` | Code en production, chaque commit est une version livrée |
| `develop` | Intégration des fonctionnalités terminées |
| `feature/…` | Une fonctionnalité, créée depuis `develop` |
| `release/…` | Préparation d'une version (corrections finales, numéro) |
| `hotfix/…` | Correction urgente créée depuis `main` |

```text
main      ●───────────────────●─────────●──────▶   (versions)
           \                 / \       /
release     \          ●──●─/   \     /
             \        /          \   /
develop       ●──●──●──●───●──●───●─●──────────▶
               \    /
feature         ●──●
```

Avantage : cadre très clair pour les livraisons planifiées. Inconvénient : beaucoup de branches de longue durée, donc plus de fusions pénibles et un cycle plus lent. Pour une application web déployée en continu, c'est souvent trop lourd.

## Trunk-based development : tout sur le tronc

Les équipes très matures s'en tiennent à une branche principale (*trunk*). Les développeurs intègrent leurs changements **plusieurs fois par jour**, par de toutes petites branches (quelques heures) ou directement. Les fonctionnalités inachevées sont cachées derrière des **drapeaux de fonctionnalité** (*feature flags*), c'est-à-dire des interrupteurs de configuration.

Avantage : presque plus de conflits et une livraison très rapide. Condition : une suite de tests solide, une intégration continue exigeante et une équipe disciplinée.

:::quiz
Une petite équipe déploie son application web plusieurs fois par semaine, avec une seule version en production. Quel workflow est le plus adapté ?
- [ ] Git Flow avec branches `develop` et `release`
- [x] GitHub Flow : branches courtes depuis `main`, relecture, fusion et déploiement
- [ ] Un dépôt par développeur sans branche commune
- [ ] Commiter directement sur `main` sans relecture
> GitHub Flow est léger et adapté au déploiement continu avec une seule version en production. Git Flow sert plutôt aux produits à versions multiples.
:::

## Versionner avec les tags

Un **tag** est une étiquette fixe sur un commit, utilisée pour marquer une version publiée. Contrairement à une branche, il ne bouge pas.

```bash
git tag v1.2.0                          # tag léger
git tag -a v1.2.0 -m "Roadmaps filtrables"   # tag annoté (recommandé)
git tag                                  # lister
git push origin v1.2.0                   # les tags ne partent pas avec un push normal
git push origin --tags                   # pousser tous les tags
git show v1.2.0                          # détails
git checkout v1.2.0                      # explorer cette version (HEAD détaché)
```

Préfère les tags **annotés** : ils contiennent l'auteur, la date et un message.

### Le versionnement sémantique

Le standard **SemVer** nomme les versions `MAJEUR.MINEUR.CORRECTIF`, par exemple `2.4.1` :

- **MAJEUR** : changement incompatible avec les versions précédentes ;
- **MINEUR** : nouvelle fonctionnalité compatible ;
- **CORRECTIF** : correction de bug compatible.

Ainsi, passer de `1.4.2` à `1.4.3` corrige un bug ; à `1.5.0`, ajoute une fonctionnalité ; à `2.0.0`, change ce qui peut casser les utilisateurs. Combiné à Conventional Commits, les types `fix`, `feat` et `feat!` (ou `BREAKING CHANGE`) déterminent automatiquement le prochain numéro.

## Gérer un hotfix

Un bug grave est découvert en production pendant que l'équipe travaille sur la prochaine version. Le but : corriger vite, sans embarquer des fonctionnalités inachevées.

```bash
git switch main
git pull --rebase
git switch -c hotfix/erreur-paiement

# ... correction minimale et test ...
git commit -am "fix(paiement): corrige l'arrondi du total en FCFA"

git push -u origin hotfix/erreur-paiement
# relecture express, fusion dans main, puis :
git tag -a v1.4.3 -m "Correctif arrondi paiement"
git push origin v1.4.3
```

Dans Git Flow, le hotfix est aussi fusionné dans `develop` pour ne pas perdre la correction. Un `cherry-pick` règle ce besoin ponctuel.

> **Attention** : un hotfix doit rester **minimal**. Profiter de l'urgence pour glisser un refactoring est le meilleur moyen de transformer un incident en deuxième incident.

## Automatiser avec les hooks Git

Un **hook** est un script que Git exécute automatiquement à un moment précis (avant un commit, avant un push…). Les hooks vivent dans `.git/hooks/`, qui n'est pas versionné. Pour les partager avec l'équipe, on utilise un outil dédié : **Husky** dans un projet JavaScript, ou `core.hooksPath` pour pointer vers un dossier versionné.

Exemple de hook `pre-commit` qui bloque un commit si le code ne respecte pas le style :

```bash
#!/bin/sh
# .githooks/pre-commit
npx prettier --check resources/js || {
  echo "Format incorrect : lance npx prettier --write resources/js"
  exit 1
}
./vendor/bin/pint --test || exit 1
```

Activation pour toute l'équipe :

```bash
chmod +x .githooks/pre-commit
git config core.hooksPath .githooks
```

Un hook `commit-msg` peut aussi vérifier que le message suit la convention Conventional Commits. Garde les hooks **rapides** (quelques secondes) : un hook lent est contourné avec `--no-verify`, et il perd son intérêt. Les contrôles lourds (suite de tests complète) appartiennent à l'intégration continue.

## Chercher l'origine d'un bug

### git blame : qui a modifié cette ligne, et pourquoi ?

```bash
git blame resources/js/Pages/Roadmaps/Index.jsx
git blame -L 20,40 fichier.php
```

Chaque ligne est annotée avec le commit et l'auteur. Le but n'est pas de trouver un coupable, mais de lire le **message du commit** pour comprendre l'intention. C'est là qu'un bon message de commit prend sa valeur.

### git bisect : trouver le commit fautif par dichotomie

Un bug existe aujourd'hui, mais fonctionnait il y a deux semaines. Entre les deux, 200 commits. `git bisect` fait une recherche binaire :

```bash
git bisect start
git bisect bad                 # la version actuelle est cassée
git bisect good v1.3.0         # cette version fonctionnait
```

Git extrait un commit au milieu. Tu testes, puis tu réponds :

```bash
git bisect good    # ou : git bisect bad
```

Après environ huit étapes (log2 de 200), Git désigne le premier mauvais commit. Termine avec `git bisect reset`. Si tu as un test automatisé, `git bisect run php artisan test --filter=Roadmap` fait tout seul le travail.

:::quiz
Quel est l'intérêt de `git bisect` ?
- [ ] Fusionner deux branches en deux étapes
- [ ] Supprimer les commits en double
- [x] Trouver par recherche binaire le commit qui a introduit un bug
- [ ] Séparer un commit en deux
> `git bisect` divise l'intervalle d'historique en deux à chaque test : on localise le commit fautif en quelques étapes même parmi des centaines.
:::

## Fiabiliser le dépôt

### .gitattributes

Ce fichier règle des détails qui évitent des conflits absurdes. Les fins de ligne en sont l'exemple classique : Windows utilise `CRLF`, Linux et macOS `LF`. Sans règle, chaque fichier peut apparaître entièrement modifié.

```text
* text=auto eol=lf
*.png binary
*.woff2 binary
composer.lock -diff
package-lock.json -diff
```

### Branches protégées et revue

Sur la plateforme, on **protège** `main` : interdiction de pousser directement, relecture obligatoire, tests verts exigés avant fusion. C'est la ceinture de sécurité du workflow, et tu la configureras dans le parcours GitHub.

### Alias utiles

```bash
git config --global alias.st "status -sb"
git config --global alias.lg "log --oneline --graph --all --decorate"
git config --global alias.last "log -1 HEAD --stat"
git config --global alias.undo "reset --soft HEAD~1"
```

## Choisir : le résumé

| Critère | GitHub Flow | Git Flow | Trunk-based |
| --- | --- | --- | --- |
| Taille d'équipe | Petite à moyenne | Moyenne à grande | Toute taille, très mature |
| Livraison | Continue | Par versions | Continue, très fréquente |
| Branches longues | Aucune | `main` et `develop` | Aucune |
| Exigence de tests | Bonne | Moyenne | Très élevée |

Pour une agence qui livre des sites et des applications à des clients, GitHub Flow, avec protection de `main` et tags de version, couvre l'immense majorité des besoins.

## Atelier guidé : mettre en place un workflow d'équipe

Compte une heure trente dans un dépôt de test (`atelier-workflow`) poussé sur un remote.

1. Crée un dépôt avec un `README.md`, un `.gitignore` et un `.gitattributes` (règle de fins de ligne text=auto avec eol=lf), puis pousse `main`.
2. Écris une courte charte `CONTRIBUTING.md` : nommage des branches, format des commits (Conventional Commits), règle « jamais de commit direct sur `main` ».
3. Crée `feature/page-contact` depuis `main`, fais deux commits, pousse la branche.
4. Fusionne-la dans `main` (en local pour l'exercice, avec `--no-ff`), puis tague `v0.1.0` avec un tag annoté et pousse le tag.
5. Crée un `hotfix/typo-contact`, corrige une faute, fusionne et tague `v0.1.1`.
6. Crée un dossier `.githooks` avec un hook `commit-msg` qui refuse un message ne commençant pas par `feat`, `fix`, `docs`, `chore`, `refactor` ou `test`. Active-le avec `core.hooksPath` et teste-le.
7. Ajoute volontairement un bug dans un fichier au milieu de quatre commits, puis retrouve le commit fautif avec `git bisect`.
8. Utilise `git blame -L` sur une ligne pour retrouver son auteur et le message associé.
9. Définis tes alias `st`, `lg` et `undo`.

Pour t'auto-évaluer, justifie par écrit le choix d'un workflow pour trois cas : un freelance seul, une équipe de cinq personnes qui déploie chaque jour, une bibliothèque open source qui maintient les versions 1.x et 2.x.

## Erreurs fréquentes

- **Adopter Git Flow par mimétisme.** Plus de branches durables signifie plus de fusions à gérer, sans bénéfice pour un site déployé en continu.
- **Branches de fonctionnalité qui durent des semaines.** Plus elles vivent, plus l'intégration est douloureuse.
- **Oublier de pousser les tags.** Un tag local n'existe pas pour les collègues ni pour le serveur de déploiement.
- **Hooks trop lents ou obligatoires sans documentation.** Ils sont contournés avec `--no-verify`.
- **Hotfix surchargé.** On y glisse du refactoring et la correction devient risquée.
- **Numéro de version arbitraire.** Sans SemVer, personne ne sait si une mise à jour risque de casser quelque chose.
- **Tout régler par les hooks locaux.** Ils ne remplacent pas les contrôles de l'intégration continue, qu'on peut contourner en local.

## Bonnes pratiques

- Écris le workflow de l'équipe dans un `CONTRIBUTING.md` à la racine du dépôt.
- Garde `main` déployable et protège-la sur la plateforme.
- Préfère des branches courtes et des demandes de fusion petites, faciles à relire.
- Tague chaque livraison en SemVer, avec des tags annotés.
- Automatise ce qui est automatisable : formatage, lint, tests, vérification des messages.
- Documente la procédure de hotfix **avant** d'en avoir besoin.
- Utilise `git bisect` et `git blame` plutôt que de chercher un bug « à l'intuition ».

## À retenir

- Un workflow définit où vit le code, comment il arrive dans `main`, et comment il est livré.
- **GitHub Flow** : branches courtes depuis `main`, revue, fusion, déploiement ; adapté à la plupart des applications web.
- **Git Flow** : `develop`, `release`, `hotfix` ; adapté aux produits livrés par versions.
- **Trunk-based** : intégration plusieurs fois par jour, appuyée par des feature flags et de solides tests.
- Les **tags annotés** et **SemVer** (`MAJEUR.MINEUR.CORRECTIF`) rendent les versions lisibles.
- Un **hotfix** part de `main`, reste minimal, puis est tagué.
- Les hooks, `git blame` et `git bisect` fiabilisent le travail et accélèrent le diagnostic.
