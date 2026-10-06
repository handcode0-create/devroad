<?php

return [
    'lessons' => [
        'Comprendre Git' => [
            'description' => 'Découvre comment Git enregistre l’historique d’un projet grâce au dépôt, au répertoire de travail et à l’index. Tu initialises ton premier dépôt et tu fais ton premier commit.',
            'objective' => 'Initialiser un dépôt, expliquer le rôle du working tree, de l’index et du dépôt, puis réaliser un premier commit visible dans l’historique.',
            'content' => <<<'MD'
## Pourquoi cette notion

Imagine que tu développes le site d’une boutique à Abidjan. Un client te demande de revenir à la version d’hier parce que la nouvelle page de paiement bloque. Sans outil de versionnement, tu cherches dans des dossiers nommés « site_final », « site_final_v2 » et « site_final_VRAI ». Avec Git, tu reviens à n’importe quel état du projet en quelques secondes.

Git est le standard de l’industrie. Presque toutes les entreprises, agences et projets open source l’utilisent. Savoir t’en servir est une condition d’embauche, et c’est aussi la base pour travailler à plusieurs sans écraser le travail des autres.

## Les concepts clés

### Un système distribué

Git est un système de contrôle de version distribué. Cela signifie que chaque développeur possède une copie complète de l’historique sur sa machine. Tu peux travailler sans connexion internet, ce qui est précieux quand le réseau est instable, et tu ne dépends pas d’un serveur central pour consulter le passé du projet.

### Les trois zones de travail

Git raisonne avec trois zones. Le répertoire de travail, appelé « working tree », contient les fichiers que tu modifies. L’index, aussi appelé « staging area », est une zone de préparation où tu choisis ce qui fera partie du prochain commit. Le dépôt, stocké dans le dossier caché « .git », contient tout l’historique des commits.

Le trajet normal d’une modification est donc : tu modifies un fichier, tu l’ajoutes à l’index avec « git add », puis tu l’enregistres définitivement avec « git commit ».

### Des instantanés, pas des différences

Un commit est un instantané complet de ton projet à un moment donné, accompagné d’un message, d’un auteur, d’une date et d’une référence vers le commit précédent. Chaque commit est identifié par un hachage unique, une longue suite de caractères dont on utilise souvent les sept premiers.

### L’état des fichiers

Un fichier peut être non suivi, modifié, indexé ou validé. La commande « git status » te dit exactement dans quel état se trouve chaque fichier. C’est la commande à lancer le plus souvent, sans modération.

## Exemple pas à pas

Le code d’exemple suit le parcours complet d’un premier dépôt. À l’étape 1, tu configures ton identité, car Git signe chaque commit avec ton nom et ton email. À l’étape 2, « git init » crée le dossier caché « .git » et transforme le dossier courant en dépôt.

À l’étape 3, tu crées un fichier README et tu lances « git status » : Git le signale comme non suivi. À l’étape 4, « git add » place le fichier dans l’index, et « git status » le montre maintenant prêt à être validé. À l’étape 5, « git commit -m » enregistre l’instantané avec un message. Enfin, à l’étape 6, « git log --oneline » affiche l’historique : tu y vois ton commit avec son hachage court.

## Erreurs fréquentes

- Oublier de configurer user.name et user.email : les commits portent une identité incorrecte ou Git refuse de commiter. Corrige avec « git config --global user.name » et « git config --global user.email ».
- Lancer « git init » dans le mauvais dossier, par exemple ton dossier personnel : Git suit alors des milliers de fichiers inutiles. Vérifie toujours ton dossier avec « pwd » avant, et supprime le dossier « .git » créé par erreur.
- Penser que « git add » enregistre dans l’historique : ce n’est qu’une préparation. Seul « git commit » crée l’instantané.
- Modifier un fichier après « git add » et croire que la modification est incluse : l’index garde la version au moment du « add ». Relance « git add » pour inclure les nouveaux changements.
- Ne jamais lancer « git status » : tu perds la visibilité sur ce qui est suivi ou non. Prends le réflexe de l’utiliser avant et après chaque action.
- Confondre Git et GitHub : Git est l’outil local, GitHub est un service d’hébergement. Tu peux utiliser Git sans GitHub.

## Bonnes pratiques

- Configure ton identité Git dès l’installation, avec l’email que tu utiliseras sur ta plateforme d’hébergement.
- Lance « git status » avant chaque commit pour savoir exactement ce que tu vas enregistrer.
- Crée un fichier « .gitignore » dès le début pour exclure dépendances, fichiers de configuration locale et secrets.
- Rédige un message de commit court et explicite, qui dit ce que le commit apporte.
- Initialise un dépôt par projet, jamais un seul dépôt géant pour tous tes travaux.

## Auto-évaluation

- Quelle est la différence entre le working tree, l’index et le dépôt ?
- Que crée exactement la commande « git init » et où ?
- Pourquoi dit-on que Git est distribué ?
- Quelle commande te permet de voir l’état des fichiers, et quels états connais-tu ?
- Quelle différence y a-t-il entre « git add » et « git commit » ?

## À retenir

- Git enregistre des instantanés du projet, identifiés par un hachage unique.
- Les trois zones sont le working tree, l’index et le dépôt.
- Le trajet d’une modification est modifier, ajouter à l’index, puis commiter.
- Chaque développeur possède l’historique complet en local.
- « git status » est ta boussole : utilise-le sans cesse.
- Git et GitHub sont deux choses différentes.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : configurer son identité (une seule fois par machine)
git config --global user.name "Awa Koné"
git config --global user.email "awa@example.com"

# Étape 2 : créer un dossier de projet et l'initialiser comme dépôt Git
mkdir boutique-awa
cd boutique-awa
git init

# Étape 3 : créer un premier fichier puis observer l'état du dépôt
echo "# Boutique Awa" > README.md
echo "Catalogue en ligne pour ma boutique de pagnes." >> README.md
git status
# Résultat : README.md apparaît dans « Untracked files » (non suivi)

# Étape 4 : placer le fichier dans l'index (zone de préparation)
git add README.md
git status
# Résultat : README.md apparaît dans « Changes to be committed »

# Étape 5 : enregistrer l'instantané dans le dépôt avec un message
git commit -m "docs: ajoute le README de la boutique"

# Étape 6 : consulter l'historique (un commit par ligne)
git log --oneline
# Exemple de sortie : 3f9a1c2 docs: ajoute le README de la boutique

# Bonus : vérifier que le dossier caché .git existe bien
ls -a
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Ton premier dépôt Git',
            'exercise_description' => <<<'MD'
Crée un dépôt pour un petit projet « carnet-de-commandes » destiné à noter les commandes d’un vendeur de jus de bissap. Tu dois initialiser le dépôt, créer deux fichiers, puis faire deux commits distincts, un par fichier.

Critères de réussite :
- Le dossier « carnet-de-commandes » contient un dossier caché « .git ».
- Le fichier « README.md » est commité avec un message explicite.
- Le fichier « commandes.txt » est commité séparément dans un second commit.
- La commande « git log --oneline » affiche exactement deux commits.
- La commande « git status » indique « nothing to commit, working tree clean ».
MD,
            'exercise_hint' => 'Fais « git add » sur un seul fichier à la fois : l’index te permet de choisir ce qui entre dans chaque commit. Vérifie avec « git status » entre les deux commits.',
            'exercise_solution' => <<<'CODE'
# Créer et initialiser le projet
mkdir carnet-de-commandes
cd carnet-de-commandes
git init

# Premier fichier : le README
echo "# Carnet de commandes" > README.md
echo "Suivi des commandes de jus de bissap." >> README.md
git add README.md
git commit -m "docs: ajoute le README du carnet"

# Second fichier : les commandes, dans un commit séparé
echo "Commande 1 : 10 bouteilles - Mme Traoré" > commandes.txt
git add commandes.txt
git commit -m "feat: ajoute la première commande"

# Vérifications
git log --oneline
# Doit afficher deux lignes
git status
# Doit afficher : nothing to commit, working tree clean
CODE,
        ],

        'Commits et historique' => [
            'description' => 'Apprends à construire des commits propres, à rédiger de bons messages et à explorer l’historique d’un projet. Tu sais aussi corriger une erreur récente sans tout casser.',
            'objective' => 'Produire une série de commits atomiques avec des messages clairs, puis retrouver un changement précis grâce à git log, git show et git diff.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans une équipe, l’historique Git est la mémoire du projet. Quand un bug apparaît en production, on remonte l’historique pour comprendre quand et pourquoi le comportement a changé. Un historique composé de messages comme « modifs » ou « test » est inutilisable. Un historique clair fait gagner des heures à toute l’équipe, y compris à toi dans six mois.

C’est aussi un signe de professionnalisme : les recruteurs et les clients qui lisent ton dépôt jugent ta rigueur à la qualité de tes commits.

## Les concepts clés

### Le commit atomique

Un commit doit représenter une unité logique de changement : une correction, une fonctionnalité, un renommage. Si tu mélanges la correction d’un bug, un changement de style et une nouvelle page dans le même commit, il devient impossible à relire ou à annuler proprement. La règle simple est qu’un commit doit pouvoir être décrit en une phrase sans utiliser le mot « et ».

### Le message de commit

Un bon message commence par un verbe à l’infinitif ou à l’impératif et décrit l’intention. De nombreuses équipes utilisent la convention des préfixes : « feat » pour une fonctionnalité, « fix » pour une correction, « docs » pour la documentation, « refactor » pour une réorganisation du code. Le résumé tient en une ligne courte, et un corps facultatif explique le pourquoi.

### Lire l’historique

La commande « git log » liste les commits du plus récent au plus ancien. L’option « --oneline » condense chaque commit sur une ligne, et « --stat » ajoute les fichiers touchés. La commande « git show » affiche le détail d’un commit précis : message et modifications. La commande « git diff » compare l’état actuel avec l’index, et « git diff --staged » compare l’index avec le dernier commit.

### Corriger une erreur récente

Si tu viens de commiter avec une faute dans le message ou si tu as oublié un fichier, « git commit --amend » remplace le dernier commit. Pour annuler un commit déjà partagé sans réécrire l’historique, « git revert » crée un nouveau commit qui inverse les changements.

## Exemple pas à pas

Le code d’exemple simule le suivi d’un catalogue de produits. À l’étape 1, tu crées le fichier et tu le commites avec un préfixe « feat ». À l’étape 2, tu modifies le fichier et tu utilises « git diff » pour voir ce qui a changé avant d’indexer. À l’étape 3, tu ajoutes les changements et tu utilises « git diff --staged » pour relire ce qui sera commité.

À l’étape 4, tu commites avec un message « fix ». À l’étape 5, tu constates une faute dans ton message et tu la corriges avec « --amend ». Enfin, aux étapes 6 et 7, « git log --oneline » puis « git show » te permettent d’explorer l’historique et d’inspecter un commit en détail.

## Erreurs fréquentes

- Écrire des messages vagues comme « update » : personne ne comprend ce qui a changé. Décris l’intention, par exemple « fix: corrige le total du panier ».
- Faire des commits géants mélangeant plusieurs sujets : ils sont impossibles à annuler partiellement. Indexe fichier par fichier, ou utilise « git add -p » pour choisir les morceaux.
- Utiliser « git add . » sans regarder : tu risques de commiter des fichiers sensibles ou temporaires. Lance « git status » et « git diff --staged » avant de valider.
- Utiliser « --amend » sur un commit déjà envoyé au dépôt distant : tu réécris un historique que d’autres ont récupéré. Utilise « git revert » dans ce cas.
- Penser que « git revert » supprime le commit : il en crée un nouveau qui inverse l’ancien, l’historique reste intact, ce qui est voulu.
- Oublier que « git diff » ne montre rien après un « git add » : les changements sont dans l’index, utilise « git diff --staged ».

## Bonnes pratiques

- Commite souvent, par petites unités cohérentes, plutôt qu’une fois par jour.
- Adopte une convention de messages avec préfixes et garde la première ligne courte.
- Relis toujours « git diff --staged » avant de commiter.
- Ne commite jamais du code qui ne compile pas ou qui casse les tests existants.
- Utilise « git log --oneline » régulièrement pour garder une vue d’ensemble de ton travail.

## Auto-évaluation

- Qu’est-ce qu’un commit atomique et pourquoi est-ce utile ?
- Quelle commande compare l’index avec le dernier commit ?
- Quand utiliser « git commit --amend » et quand l’éviter ?
- Quelle est la différence entre « git revert » et la suppression d’un commit ?
- Comment afficher les modifications exactes introduites par un commit donné ?

## À retenir

- Un commit représente une unité logique de changement.
- Un bon message décrit l’intention, avec un préfixe de type.
- « git log », « git show » et « git diff » sont tes outils d’exploration.
- « git diff --staged » permet de relire avant de valider.
- « --amend » sert aux commits locaux, « revert » aux commits déjà partagés.
MD,
            'code_example' => <<<'CODE'
# Contexte : suivi d'un catalogue de produits pour une boutique en ligne
mkdir catalogue && cd catalogue && git init

# Étape 1 : premier commit avec un préfixe de type
echo "Pagne wax : 5000 FCFA" > produits.txt
git add produits.txt
git commit -m "feat: ajoute le premier produit au catalogue"

# Étape 2 : modifier le fichier, puis voir ce qui a changé (zone de travail)
echo "Sac en cuir : 12000 FCFA" >> produits.txt
git diff

# Étape 3 : indexer, puis relire ce qui sera réellement commité
git add produits.txt
git diff --staged

# Étape 4 : valider avec un message de type « fix » ou « feat »
git commit -m "feat: ajoute le sac en cuir"

# Étape 5 : corriger le message du DERNIER commit (local uniquement)
git commit --amend -m "feat: ajoute le sac en cuir au catalogue"

# Étape 6 : lire l'historique de façon condensée puis avec les fichiers touchés
git log --oneline
git log --stat

# Étape 7 : inspecter un commit précis (HEAD = le plus récent)
git show HEAD

# Annuler proprement un commit déjà partagé : crée un nouveau commit inverse
# git revert HEAD
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Un historique lisible pour une boutique',
            'exercise_description' => <<<'MD'
Dans un dépôt « menu-restaurant », construis un historique de trois commits atomiques pour le menu d’un maquis. Tu dois ensuite corriger le message du dernier commit et inspecter un commit avec « git show ».

Critères de réussite :
- Le dépôt contient exactement trois commits, chacun avec un préfixe « feat » ou « fix ».
- Chaque commit ne concerne qu’un seul changement logique (un plat ajouté, un prix corrigé, etc.).
- Le message du dernier commit a été corrigé avec « --amend » et ne contient pas de faute.
- Tu as utilisé « git diff --staged » avant au moins un commit.
- La commande « git log --oneline » affiche trois lignes lisibles.
MD,
            'exercise_hint' => 'Modifie le fichier, indexe, puis lance « git diff --staged » avant de commiter. Pour corriger le dernier message, utilise « git commit --amend -m » avec le nouveau texte.',
            'exercise_solution' => <<<'CODE'
mkdir menu-restaurant && cd menu-restaurant && git init

# Commit 1 : premier plat
echo "Garba : 1000 FCFA" > menu.txt
git add menu.txt
git commit -m "feat: ajoute le garba au menu"

# Commit 2 : second plat
echo "Poulet braisé : 3500 FCFA" >> menu.txt
git add menu.txt
git diff --staged
git commit -m "feat: ajoute le poulet braisé"

# Commit 3 : correction de prix (avec un message volontairement fautif)
sed -i 's/Garba : 1000 FCFA/Garba : 1500 FCFA/' menu.txt
git add menu.txt
git commit -m "fix: corige le prix du garba"

# Correction du message du dernier commit
git commit --amend -m "fix: corrige le prix du garba"

# Vérification et inspection
git log --oneline
git show HEAD
CODE,
        ],

        'Branches et merge' => [
            'description' => 'Comprends ce qu’est une branche, comment en créer une et comment fusionner son travail dans la branche principale. Tu distingues le fast-forward du merge commit.',
            'objective' => 'Créer une branche de fonctionnalité, y commiter, puis la fusionner dans main en comprenant le type de fusion réalisé.',
            'content' => <<<'MD'
## Pourquoi cette notion

Tu travailles sur la page d’accueil d’un site quand le client t’appelle pour corriger d’urgence une faute de prix sur la page produit. Si tout ton travail est au même endroit, tu ne peux pas livrer la correction sans livrer aussi la page d’accueil à moitié terminée. Les branches résolvent ce problème : chaque tâche vit dans sa propre ligne de développement, isolée des autres.

En entreprise, personne ne travaille directement sur la branche principale. Chaque fonctionnalité, chaque correction a sa branche, fusionnée une fois terminée et validée.

## Les concepts clés

### Une branche est un pointeur

Contrairement à ce que l’on imagine, une branche ne copie pas les fichiers. C’est un simple pointeur léger vers un commit. Créer une branche est donc instantané et ne coûte presque rien. La branche principale s’appelle généralement « main », et le pointeur spécial « HEAD » indique la branche sur laquelle tu travailles actuellement.

### Créer et changer de branche

La commande « git switch -c nom » crée une branche et s’y place immédiatement. « git switch nom » change de branche. « git branch » liste les branches locales et marque la branche courante. Quand tu changes de branche, Git remplace les fichiers du répertoire de travail par ceux de cette branche, c’est pourquoi il faut commiter ou mettre de côté tes modifications avant de changer.

### Fusionner avec merge

La commande « git merge » intègre dans la branche courante les commits d’une autre branche. Tu te places sur la branche qui reçoit, généralement « main », puis tu fusionnes la branche de fonctionnalité. Deux cas existent.

Dans le premier, si « main » n’a pas avancé depuis la création de la branche, Git déplace simplement le pointeur : c’est un « fast-forward », sans nouveau commit. Dans le second, si « main » a évolué entre-temps, Git crée un commit de fusion à deux parents qui rassemble les deux historiques.

### Nettoyer après la fusion

Une branche fusionnée n’a plus d’utilité. Tu la supprimes avec « git branch -d nom », qui refuse de supprimer une branche non fusionnée par sécurité.

## Exemple pas à pas

Le code d’exemple part d’un projet avec un premier commit sur « main ». À l’étape 1, tu crées la branche « feature/panier » et tu t’y places. À l’étape 2, tu y ajoutes un fichier et tu commites deux fois. À l’étape 3, tu reviens sur « main » : le fichier du panier disparaît du dossier, preuve que chaque branche a son propre état.

À l’étape 4, tu fusionnes la branche : comme « main » n’a pas bougé, c’est un fast-forward. À l’étape 5, tu crées une seconde branche et tu fais avancer « main » en parallèle pour provoquer un vrai commit de fusion avec l’option « --no-ff ». À l’étape 6, tu supprimes les branches terminées et tu visualises le graphe avec « git log --graph ».

## Erreurs fréquentes

- Commiter par erreur sur « main » au lieu de la branche de fonctionnalité : vérifie la branche avec « git branch » avant de travailler, et crée toujours la branche en premier.
- Changer de branche avec des modifications non commitées : Git refuse ou emporte les changements avec toi. Commite d’abord, ou utilise « git stash » pour les mettre de côté.
- Fusionner dans la mauvaise branche : le « merge » s’applique à la branche courante. Place-toi sur la destination avant de lancer la commande.
- Supprimer une branche non fusionnée avec « -D » : le travail devient difficile à retrouver. Préfère « -d » qui protège.
- Garder des branches vivantes pendant des semaines : elles divergent et la fusion devient pénible. Fusionne souvent et garde des branches courtes.
- Croire qu’une branche copie les fichiers : c’est un pointeur, ne crains donc pas d’en créer beaucoup.

## Bonnes pratiques

- Une branche par fonctionnalité ou correction, avec un nom explicite comme « feature/panier » ou « fix/prix-produit ».
- Garde « main » toujours dans un état stable et déployable.
- Lance « git status » et « git branch » avant de commencer à travailler.
- Supprime les branches une fois fusionnées pour garder le dépôt lisible.
- Visualise l’historique avec « git log --oneline --graph --all » pour comprendre la forme du projet.

## Auto-évaluation

- Qu’est-ce qu’une branche pour Git et pourquoi est-elle légère ?
- Que représente « HEAD » ?
- Quelle différence y a-t-il entre un fast-forward et un merge commit ?
- Sur quelle branche dois-tu te placer avant de lancer « git merge » ?
- Pourquoi « git branch -d » est-il plus sûr que « -D » ?

## À retenir

- Une branche est un pointeur léger vers un commit.
- « git switch -c » crée une branche et s’y place.
- « git merge » intègre une branche dans la branche courante.
- Le fast-forward déplace un pointeur, le merge commit réunit deux historiques.
- Supprime les branches terminées et garde-les courtes.
MD,
            'code_example' => <<<'CODE'
# Contexte : une boutique en ligne avec une branche principale « main »
git init -b main boutique && cd boutique
echo "Accueil" > accueil.txt
git add . && git commit -m "feat: ajoute la page d'accueil"

# Étape 1 : créer une branche de fonctionnalité et s'y placer
git switch -c feature/panier
git branch   # l'étoile indique la branche courante

# Étape 2 : travailler sur la branche (deux commits)
echo "Panier : liste des articles" > panier.txt
git add panier.txt && git commit -m "feat: ajoute la page panier"
echo "Total : calcul en FCFA" >> panier.txt
git add panier.txt && git commit -m "feat: affiche le total du panier"

# Étape 3 : revenir sur main, le fichier panier.txt n'existe plus ici
git switch main
ls

# Étape 4 : fusionner (main n'a pas bougé => fast-forward)
git merge feature/panier

# Étape 5 : provoquer un vrai commit de fusion avec --no-ff
git switch -c feature/contact
echo "Contact : WhatsApp" > contact.txt
git add contact.txt && git commit -m "feat: ajoute la page contact"
git switch main
git merge --no-ff feature/contact -m "merge: intègre la page contact"

# Étape 6 : nettoyer et visualiser le graphe
git branch -d feature/panier feature/contact
git log --oneline --graph --all
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Une fonctionnalité sur sa branche',
            'exercise_description' => <<<'MD'
Dans un dépôt « app-livraison », développe la fonctionnalité « suivi de commande » sur une branche dédiée, puis fusionne-la dans « main » avec un commit de fusion explicite.

Critères de réussite :
- Une branche « feature/suivi-commande » est créée à partir de « main ».
- Cette branche contient au moins deux commits avec des messages clairs.
- La fusion dans « main » utilise « --no-ff » et produit un commit de fusion.
- La branche « feature/suivi-commande » est supprimée après la fusion.
- La commande « git log --oneline --graph » montre la forme de la fusion.
MD,
            'exercise_hint' => 'Commence par un commit initial sur « main ». Pense à te replacer sur « main » avant de lancer « git merge --no-ff ». Utilise « git branch -d » une fois la fusion faite.',
            'exercise_solution' => <<<'CODE'
git init -b main app-livraison && cd app-livraison

# Commit initial sur main
echo "Application de livraison" > README.md
git add . && git commit -m "docs: initialise le projet de livraison"

# Branche de fonctionnalité
git switch -c feature/suivi-commande

# Deux commits sur la branche
echo "Statut : en préparation" > suivi.txt
git add suivi.txt && git commit -m "feat: ajoute le statut de commande"
echo "Statut : en route" >> suivi.txt
git add suivi.txt && git commit -m "feat: ajoute le statut en route"

# Retour sur main puis fusion avec commit de fusion explicite
git switch main
git merge --no-ff feature/suivi-commande -m "merge: intègre le suivi de commande"

# Suppression de la branche fusionnée
git branch -d feature/suivi-commande

# Visualisation du graphe
git log --oneline --graph
CODE,
        ],

        'Rebase et conflits' => [
            'description' => 'Découvre comment rebase réécrit une branche pour garder un historique linéaire, et comment résoudre un conflit en comprenant les deux versions en présence.',
            'objective' => 'Provoquer puis résoudre un conflit de fusion, puis rebaser une branche sur main en obtenant un historique linéaire.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dès que deux personnes modifient la même ligne d’un fichier, Git ne peut pas deviner laquelle garder. C’est un conflit. Les conflits ne sont pas une erreur de ta part : ils sont normaux dans le travail d’équipe. Ce qui distingue un débutant d’un développeur à l’aise, c’est la sérénité face à ces situations et la méthode pour les résoudre.

Le rebase, lui, répond à un besoin d’hygiène : garder un historique linéaire et lisible plutôt qu’un enchevêtrement de commits de fusion.

## Les concepts clés

### Qu’est-ce qu’un conflit

Un conflit survient quand deux branches modifient les mêmes lignes d’un même fichier, ou quand l’une modifie un fichier que l’autre supprime. Git s’arrête alors, marque les fichiers concernés et te demande de trancher. Les parties en conflit sont délimitées par des marqueurs : la ligne « <<<<<<< » ouvre la version courante, « ======= » sépare les deux versions, et « >>>>>>> » ferme la version entrante.

### Résoudre un conflit

La méthode tient en quatre gestes. Tu ouvres le fichier et tu lis les deux versions. Tu décides du contenu final, qui peut garder l’une, l’autre ou combiner les deux. Tu supprimes les marqueurs. Enfin tu indexes le fichier avec « git add » puis tu termines l’opération : « git commit » pour un merge, « git rebase --continue » pour un rebase. Si tu es perdu, « git merge --abort » ou « git rebase --abort » te ramène à l’état précédent.

### Le rebase

Rebaser une branche, c’est rejouer ses commits au-dessus d’une autre base. Si ta branche de fonctionnalité est partie d’un ancien état de « main », « git rebase main » reprend tes commits un à un et les place après les derniers commits de « main ». Le résultat est un historique en ligne droite, sans commit de fusion. Attention : le rebase crée de nouveaux commits avec de nouveaux hachages, il réécrit donc l’historique.

### Merge ou rebase

Le merge conserve l’historique tel qu’il s’est passé, au prix de commits de fusion. Le rebase produit un historique propre, mais il ne faut jamais rebaser des commits déjà partagés avec d’autres. La règle d’or est de rebaser uniquement tes branches locales et personnelles.

## Exemple pas à pas

Le code d’exemple fabrique volontairement un conflit sur un fichier de prix. À l’étape 1, tu crées le fichier sur « main ». À l’étape 2, tu crées deux branches, chacune modifiant la même ligne différemment. À l’étape 3, tu fusionnes la première, ce qui passe sans problème.

À l’étape 4, tu fusionnes la seconde : Git signale un conflit. À l’étape 5, tu inspectes le fichier avec ses marqueurs, tu le réécris proprement puis tu fais « git add » et « git commit ». À l’étape 6, tu rebases une troisième branche sur « main » pour voir l’historique devenir linéaire, puis tu vérifies avec « git log --graph ».

## Erreurs fréquentes

- Laisser les marqueurs de conflit dans le fichier : le code ne fonctionne plus. Cherche « <<<<<<< » dans le projet avant de valider.
- Accepter une version sans lire l’autre : tu perds le travail d’un collègue. Lis toujours les deux côtés et combine si nécessaire.
- Oublier « git add » après avoir résolu : Git considère le conflit non résolu. Indexe chaque fichier résolu.
- Rebaser une branche déjà poussée et partagée : les collègues se retrouvent avec des historiques divergents. Utilise merge pour les branches partagées.
- Paniquer et supprimer le dossier : « git merge --abort » ou « git rebase --abort » annule proprement l’opération en cours.
- Ne pas lancer « git status » pendant un conflit : il liste exactement les fichiers à résoudre et la suite à suivre.

## Bonnes pratiques

- Intègre souvent les changements de « main » dans ta branche pour limiter la taille des conflits.
- Communique avec ton équipe quand vous touchez les mêmes fichiers.
- Après résolution, relance les tests ou l’application avant de valider.
- Rebase seulement tes branches locales, jamais l’historique partagé.
- Garde les commits petits : les conflits sont plus faciles à résoudre.

## Auto-évaluation

- Dans quels cas Git déclare-t-il un conflit ?
- Que signifient les marqueurs « <<<<<<< », « ======= » et « >>>>>>> » ?
- Quelles commandes terminent un merge puis un rebase après résolution ?
- Quelle différence de résultat y a-t-il entre merge et rebase ?
- Pourquoi ne faut-il pas rebaser des commits déjà partagés ?

## À retenir

- Un conflit est normal : il demande une décision humaine.
- Résous en lisant les deux versions, en supprimant les marqueurs, puis en indexant.
- « --abort » permet de revenir en arrière sans risque.
- Le rebase rejoue des commits pour obtenir un historique linéaire.
- Ne rebase jamais un historique déjà partagé.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : un fichier de prix sur main
git init -b main tarifs && cd tarifs
echo "Livraison Abidjan : 1000 FCFA" > tarifs.txt
git add . && git commit -m "feat: ajoute le tarif de livraison"

# Étape 2 : deux branches modifient la MÊME ligne différemment
git switch -c feature/tarif-a
echo "Livraison Abidjan : 1500 FCFA" > tarifs.txt
git commit -am "feat: passe la livraison à 1500 FCFA"

git switch main
git switch -c feature/tarif-b
echo "Livraison Abidjan : 2000 FCFA" > tarifs.txt
git commit -am "feat: passe la livraison à 2000 FCFA"

# Étape 3 : première fusion sans problème
git switch main
git merge feature/tarif-a

# Étape 4 : seconde fusion => CONFLIT
git merge feature/tarif-b
git status   # liste « both modified: tarifs.txt »

# Étape 5 : résoudre. Le fichier contient les marqueurs <<<<<<< ======= >>>>>>>
cat tarifs.txt
# On décide du contenu final puis on supprime les marqueurs
echo "Livraison Abidjan : 1500 FCFA" > tarifs.txt
git add tarifs.txt
git commit -m "merge: retient le tarif de 1500 FCFA"

# Étape 6 : rebase d'une branche locale pour obtenir un historique linéaire
git switch -c feature/horaires
echo "Horaires : 8h-18h" > horaires.txt
git add . && git commit -m "feat: ajoute les horaires"
git rebase main    # rejoue le commit au-dessus de main
git log --oneline --graph --all

# En cas de panique pendant une opération :
# git merge --abort   ou   git rebase --abort
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Résoudre ton premier conflit',
            'exercise_description' => <<<'MD'
Dans un dépôt « site-ecole », deux branches modifient la même ligne du fichier « titre.txt ». Tu dois provoquer le conflit, le résoudre en combinant les deux versions, puis rebaser une troisième branche.

Critères de réussite :
- Le fichier « titre.txt » est modifié différemment sur deux branches.
- Un conflit est bien déclenché lors de la seconde fusion.
- Le fichier final ne contient plus aucun marqueur de conflit et combine les deux idées.
- Le conflit est conclu par un commit de fusion.
- Une troisième branche est rebasée sur « main » et « git log --oneline --graph » montre un historique linéaire pour elle.
MD,
            'exercise_hint' => 'Fais « cat titre.txt » pendant le conflit pour voir les marqueurs. Réécris le fichier entièrement avec « echo », puis « git add » et « git commit ». Pour le rebase, crée la troisième branche après la fusion.',
            'exercise_solution' => <<<'CODE'
git init -b main site-ecole && cd site-ecole
echo "Lycée Moderne" > titre.txt
git add . && git commit -m "docs: ajoute le titre du site"

# Deux branches modifient la même ligne
git switch -c feature/titre-a
echo "Lycée Moderne de Cocody" > titre.txt
git commit -am "feat: précise la commune"

git switch main
git switch -c feature/titre-b
echo "Lycée Moderne - Excellence" > titre.txt
git commit -am "feat: ajoute le slogan"

# Première fusion OK, seconde en conflit
git switch main
git merge feature/titre-a
git merge feature/titre-b   # CONFLIT attendu

# Résolution : on combine les deux versions, sans marqueurs
echo "Lycée Moderne de Cocody - Excellence" > titre.txt
git add titre.txt
git commit -m "merge: combine commune et slogan"

# Troisième branche, rebasée sur main
git switch -c feature/contact
echo "Contact : 01 02 03 04" > contact.txt
git add . && git commit -m "feat: ajoute le contact"
git rebase main
git log --oneline --graph --all
CODE,
        ],

        'Remote et collaboration' => [
            'description' => 'Apprends à relier ton dépôt local à un dépôt distant et à synchroniser le travail avec fetch, pull et push. Tu comprends les branches de suivi.',
            'objective' => 'Connecter un dépôt local à un remote, publier une branche avec push, puis récupérer des changements avec fetch et pull.',
            'content' => <<<'MD'
## Pourquoi cette notion

Jusqu’ici, tout ton travail reste sur ta machine. Si ton ordinateur tombe en panne ou si un collègue doit récupérer ton code, tu es bloqué. Un dépôt distant, hébergé sur un serveur accessible à tous, sert à la fois de sauvegarde et de point de rendez-vous de l’équipe. C’est là que se retrouvent les développeurs d’une même agence, qu’ils soient à Abidjan, Dakar ou ailleurs.

Comprendre la différence entre « fetch », « pull » et « push » évite la plupart des mauvaises surprises en collaboration.

## Les concepts clés

### Le remote

Un remote est une référence nommée vers un autre dépôt Git, identifié par une URL. Le nom conventionnel du dépôt principal est « origin ». La commande « git remote add origin url » enregistre ce lien, et « git remote -v » liste les remotes configurés. Quand tu clones un dépôt avec « git clone », Git crée automatiquement le remote « origin ».

### Les branches de suivi

Quand tu récupères des informations du dépôt distant, Git garde une copie locale de l’état des branches distantes, nommée par exemple « origin/main ». Ces références en lecture seule te montrent où en était le serveur la dernière fois que tu as communiqué avec lui. Une branche locale peut être liée à une branche distante : c’est le suivi, ou « tracking ». L’option « -u » de « git push -u » établit ce lien une fois pour toutes.

### Push, fetch et pull

La commande « git push » envoie tes commits locaux vers le dépôt distant. La commande « git fetch » télécharge les nouveautés distantes et met à jour les branches « origin/... » sans toucher à ton travail en cours : elle est sûre. La commande « git pull » combine « fetch » puis l’intégration, par merge ou par rebase selon la configuration. Elle est pratique mais modifie ta branche, donc utilise-la en sachant ce qu’elle fait.

### Quand le push est refusé

Si quelqu’un a poussé avant toi sur la même branche, ton push est rejeté car ton historique n’inclut pas ses commits. La solution est de récupérer d’abord ses changements, de les intégrer, puis de pousser de nouveau.

## Exemple pas à pas

Le code d’exemple simule un dépôt distant avec un dossier local, ce qui permet de s’exercer sans compte en ligne. À l’étape 1, on crée un dépôt « nu » qui joue le rôle de serveur. À l’étape 2, on crée le projet local, on commite et on ajoute le remote « origin ».

À l’étape 3, « git push -u origin main » publie la branche et établit le suivi. À l’étape 4, on clone le dépôt dans un second dossier pour simuler un collègue, qui commite et pousse. À l’étape 5, depuis le premier dossier, « git fetch » télécharge les nouveautés, « git log origin/main » les montre, puis « git pull » les intègre. À l’étape 6, « git branch -vv » affiche le suivi.

## Erreurs fréquentes

- Confondre « fetch » et « pull » : « fetch » ne modifie pas ta branche, « pull » oui. Utilise « fetch » pour observer, « pull » pour intégrer.
- Pousser sans avoir récupéré : le push est rejeté. Lance « git pull » ou « git fetch » puis intègre, avant de repousser.
- Utiliser « git push --force » pour contourner un rejet : tu écrases le travail des autres. Préfère « --force-with-lease » et seulement sur tes branches personnelles.
- Oublier « -u » au premier push : Git ne sait pas quelle branche distante suivre et te le rappelle. Ajoute « -u » une fois.
- Se tromper d’URL de remote : le push échoue. Vérifie avec « git remote -v » et corrige avec « git remote set-url ».
- Commiter des secrets puis pousser : ils deviennent publics. Vérifie ton contenu avant de pousser.

## Bonnes pratiques

- Fais « git fetch » ou « git pull » avant de commencer à travailler, pour partir d’une base à jour.
- Pousse régulièrement pour sauvegarder ton travail et le rendre visible.
- Utilise « git status » : il indique si ta branche est en avance ou en retard sur son suivi.
- Ne force jamais un push sur une branche partagée.
- Garde des noms de branches identiques en local et en distant.

## Auto-évaluation

- Qu’est-ce qu’un remote et que signifie le nom « origin » ?
- Quelle différence y a-t-il entre « git fetch » et « git pull » ?
- À quoi sert l’option « -u » dans « git push -u origin main » ?
- Pourquoi un push peut-il être rejeté et comment corriger ?
- Que représente la référence « origin/main » ?

## À retenir

- Un remote est une référence vers un dépôt distant, souvent nommé « origin ».
- « fetch » télécharge sans rien changer, « pull » télécharge puis intègre.
- « push -u » publie et établit le suivi de branche.
- Un push rejeté signifie qu’il faut d’abord intégrer les changements distants.
- N’utilise pas « --force » sur des branches partagées.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : simuler un serveur distant avec un dépôt « nu » (bare)
mkdir serveur && cd serveur
git init --bare -b main boutique.git
cd ..

# Étape 2 : créer le projet local et le relier au « serveur » (remote origin)
git init -b main moi && cd moi
echo "Boutique en ligne" > README.md
git add . && git commit -m "docs: initialise la boutique"
git remote add origin ../serveur/boutique.git
git remote -v   # vérifier l'URL du remote

# Étape 3 : publier la branche et établir le suivi avec -u
git push -u origin main

# Étape 4 : un collègue clone le dépôt, commite et pousse
cd ..
git clone serveur/boutique.git collegue && cd collegue
git config user.name "Koffi" && git config user.email "koffi@example.com"
echo "Paiement Wave et Orange Money" >> README.md
git commit -am "feat: documente les moyens de paiement"
git push

# Étape 5 : retour chez moi, récupérer sans modifier ma branche (fetch)
cd ../moi
git fetch
git log --oneline origin/main   # le commit du collègue est visible ici
git pull                        # intègre le changement dans main

# Étape 6 : vérifier le suivi entre branche locale et distante
git branch -vv
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Synchroniser deux copies d’un projet',
            'exercise_description' => <<<'MD'
Simule un travail à deux sans compte en ligne : un dépôt « nu » sert de serveur, tu travailles dans un premier dossier et un « collègue » dans un second. Vous échangez des commits via le remote.

Critères de réussite :
- Un dépôt nu « projet.git » existe et sert de remote « origin ».
- Le premier push utilise « -u » et « git branch -vv » montre le suivi vers « origin/main ».
- Le second dossier est obtenu avec « git clone » et y pousse un commit.
- Tu utilises « git fetch » puis « git log origin/main » pour voir le commit du collègue avant de l’intégrer.
- Après « git pull », les deux dossiers ont le même historique avec « git log --oneline ».
MD,
            'exercise_hint' => 'Un dépôt nu se crée avec « git init --bare ». Pour le collègue, pense à configurer user.name et user.email dans son dossier si Git le demande.',
            'exercise_solution' => <<<'CODE'
mkdir atelier && cd atelier

# Le « serveur » distant
git init --bare -b main projet.git

# Mon dossier de travail
git init -b main moi && cd moi
echo "Projet de réservation" > README.md
git add . && git commit -m "docs: initialise le projet"
git remote add origin ../projet.git
git push -u origin main
git branch -vv   # montre [origin/main]

# Le collègue clone et pousse
cd ..
git clone projet.git collegue && cd collegue
git config user.name "Aya" && git config user.email "aya@example.com"
echo "Réservation par téléphone" >> README.md
git commit -am "feat: ajoute la réservation par téléphone"
git push

# Je regarde avant d'intégrer, puis j'intègre
cd ../moi
git fetch
git log --oneline origin/main
git pull
git log --oneline
CODE,
        ],

        'Workflow professionnel' => [
            'description' => 'Adopte un workflow d’équipe reproductible : branches courtes, commits lisibles, revue de code et intégration régulière. Tu découvres aussi stash, tags et .gitignore.',
            'objective' => 'Appliquer un cycle complet de travail en équipe : branche, commits conventionnels, mise à jour depuis main, push et préparation d’une pull request.',
            'content' => <<<'MD'
## Pourquoi cette notion

Connaître les commandes ne suffit pas : une équipe a besoin de règles communes pour ne pas se marcher dessus. Dans une agence ou une start-up, chaque développeur suit le même parcours : il crée une branche, il commite, il fait relire son travail, puis il fusionne. Ce cadre s’appelle un workflow. Il garantit que la branche principale reste stable et que chaque changement est relu avant d’arriver en production.

Quand tu postules dans une entreprise, on attend de toi que tu comprennes immédiatement ce fonctionnement.

## Les concepts clés

### Le workflow par branches de fonctionnalité

Le principe est simple. La branche « main » contient le code stable. Pour toute nouvelle tâche, tu crées une branche courte à partir de « main », tu travailles, tu pousses ta branche, tu ouvres une demande de fusion appelée pull request, un collègue relit, puis la branche est fusionnée et supprimée. Les branches courtes, de quelques jours au maximum, limitent les conflits.

### Nommer et rédiger

Les branches portent des noms parlants avec un préfixe : « feature/ » pour une fonctionnalité, « fix/ » pour une correction, « docs/ » pour la documentation. Les messages de commit suivent une convention partagée, comme les préfixes « feat », « fix » ou « docs », avec un résumé court à l’impératif.

### Rester à jour

Avant de pousser, tu intègres les dernières évolutions de « main » dans ta branche, par merge ou par rebase selon la règle de l’équipe. Ainsi, tu résous les conflits chez toi plutôt que dans la revue.

### Les outils du quotidien

La commande « git stash » met de côté des modifications non commitées pour changer de branche, et « git stash pop » les restitue. Le fichier « .gitignore » liste ce que Git doit ignorer : dépendances, fichiers de configuration locale, secrets. Les tags, créés avec « git tag », marquent une version précise comme « v1.0.0 ».

## Exemple pas à pas

Le code d’exemple déroule une journée de travail type. À l’étape 1, tu configures le « .gitignore » pour exclure le fichier « .env » et le dossier « node_modules ». À l’étape 2, tu mets à jour « main » puis tu crées la branche « feature/paiement-wave ».

À l’étape 3, tu commites en deux temps avec des messages conventionnels. À l’étape 4, une urgence arrive : tu utilises « git stash » pour mettre ton travail de côté, tu corriges, puis tu reprends avec « git stash pop ». À l’étape 5, tu intègres « main » dans ta branche. À l’étape 6, tu pousses avec « -u » pour ouvrir la pull request. À l’étape 7, une fois la fusion faite, tu poses un tag de version.

## Erreurs fréquentes

- Travailler pendant des semaines sur une longue branche : la fusion devient un cauchemar. Découpe en petites tâches et intègre souvent.
- Commiter le fichier « .env » avec des clés d’API : elles sont exposées dans l’historique. Ajoute-le au « .gitignore » dès le début, et change les clés si elles ont fuité.
- Ajouter un « .gitignore » après avoir commité un fichier : Git continue de le suivre. Retire-le de l’index avec « git rm --cached ».
- Perdre du travail mis en « stash » : on l’oublie ensuite. Utilise « git stash list » pour retrouver ce que tu as mis de côté.
- Pousser directement sur « main » : tu contournes la revue. Passe toujours par une branche et une pull request.
- Écrire des messages sans convention : l’historique devient illisible. Mets-toi d’accord avec l’équipe sur le format.

## Bonnes pratiques

- Une tâche, une branche, une pull request de taille raisonnable.
- Mets à jour ta branche depuis « main » avant de demander la relecture.
- Protège « main » pour empêcher les poussées directes quand la plateforme le permet.
- Documente les règles de contribution dans un fichier dédié du dépôt.
- Utilise les tags pour marquer chaque version livrée au client.

## Auto-évaluation

- Quelles sont les étapes du workflow par branches de fonctionnalité ?
- Pourquoi garder des branches courtes ?
- À quoi sert « git stash » et comment récupérer le travail mis de côté ?
- Que faire quand un fichier déjà suivi doit être ignoré ?
- À quoi servent les tags ?

## À retenir

- Un workflow commun protège la stabilité de « main ».
- Branches courtes, commits conventionnels, revue avant fusion.
- Intègre régulièrement « main » dans ta branche.
- « .gitignore » évite de commiter secrets et dépendances.
- Stash et tags sont des outils pratiques au quotidien.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : préparer le .gitignore (secrets et dépendances jamais commités)
cat > .gitignore <<'EOF'
.env
node_modules/
*.log
EOF
git add .gitignore
git commit -m "chore: ajoute le .gitignore"

# Étape 2 : partir d'une base à jour puis créer une branche courte
git switch main
git pull
git switch -c feature/paiement-wave

# Étape 3 : commits conventionnels, petits et cohérents
echo "Paiement Wave : formulaire" > paiement.txt
git add paiement.txt
git commit -m "feat: ajoute le formulaire de paiement Wave"
echo "Paiement Wave : confirmation" >> paiement.txt
git commit -am "feat: affiche la confirmation de paiement"

# Étape 4 : urgence ! on met le travail en cours de côté
echo "brouillon" >> paiement.txt
git stash                # met les modifs de côté
git switch main          # ... correction urgente ici ...
git switch feature/paiement-wave
git stash pop            # on reprend où on s'était arrêté

# Étape 5 : intégrer les dernières évolutions de main dans la branche
git fetch origin
git rebase origin/main   # ou git merge origin/main selon la règle d'équipe

# Étape 6 : publier la branche, puis ouvrir la pull request sur la plateforme
git push -u origin feature/paiement-wave

# Étape 7 : après fusion, marquer la version livrée
git switch main && git pull
git tag -a v1.0.0 -m "Première version avec paiement Wave"
git push origin v1.0.0
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Un cycle de travail d’équipe complet',
            'exercise_description' => <<<'MD'
Dans un dépôt « app-reservation » relié à un remote (un dépôt nu local suffit), applique un cycle professionnel complet pour ajouter la fonctionnalité « annulation de réservation ».

Critères de réussite :
- Un fichier « .gitignore » excluant « .env » et « node_modules/ » est commité sur « main ».
- La fonctionnalité est développée sur la branche « feature/annulation » avec au moins deux commits conventionnels.
- Tu utilises « git stash » puis « git stash pop » sans perdre de modification.
- La branche est poussée avec « -u » vers le remote.
- Un tag « v0.1.0 » est créé sur « main » après la fusion.
MD,
            'exercise_hint' => 'Prépare d’abord un remote avec « git init --bare ». Après avoir fusionné la branche dans « main », crée le tag avec « git tag -a v0.1.0 -m ».',
            'exercise_solution' => <<<'CODE'
mkdir exo && cd exo
git init --bare -b main serveur.git
git init -b main app-reservation && cd app-reservation
git remote add origin ../serveur.git

# .gitignore sur main
printf ".env\nnode_modules/\n" > .gitignore
git add .gitignore
git commit -m "chore: ajoute le .gitignore"
git push -u origin main

# Branche de fonctionnalité
git switch -c feature/annulation
echo "Annulation : bouton" > annulation.txt
git add . && git commit -m "feat: ajoute le bouton d'annulation"
echo "Annulation : remboursement" >> annulation.txt
git commit -am "feat: gère le remboursement à l'annulation"

# Stash puis récupération
echo "brouillon" >> annulation.txt
git stash
git stash pop
git checkout -- annulation.txt   # on écarte le brouillon

# Publication de la branche
git push -u origin feature/annulation

# Fusion (simule la pull request validée), puis tag
git switch main
git merge --no-ff feature/annulation -m "merge: ajoute l'annulation"
git push
git tag -a v0.1.0 -m "Version 0.1.0"
git push origin v0.1.0
CODE,
        ],

        'Projet final Git' => [
            'description' => 'Mets en pratique tout le parcours : tu simules un projet collaboratif avec remote, branches, pull request, conflit et fusion propre.',
            'objective' => 'Livrer un dépôt collaboratif complet avec deux contributeurs simulés, un conflit résolu, une fusion et une version taguée.',
            'content' => <<<'MD'
## Pourquoi cette notion

Ce projet final rassemble tout ce que tu as appris : initialiser, commiter, créer des branches, fusionner, résoudre des conflits, synchroniser avec un remote et suivre un workflow d’équipe. C’est exactement ce que tu feras le premier jour dans une agence, avec la différence que les conflits arriveront sans prévenir. S’entraîner en simulant deux contributeurs te donne l’aisance nécessaire pour rester calme le moment venu.

Ce livrable peut aussi devenir une pièce de ton portfolio : un dépôt propre avec un historique lisible rassure un recruteur ou un client.

## Les concepts clés

### Simuler une équipe

Tu peux jouer plusieurs rôles sur ta machine en utilisant plusieurs dossiers clonés depuis un même dépôt distant. Chaque dossier représente un développeur avec sa propre identité Git configurée localement. Cette technique te permet de provoquer de vrais conflits et de vraiment pousser et tirer des modifications.

### Le cycle complet

Le cycle d’une fonctionnalité est : synchroniser « main », créer une branche, commiter par petites unités, mettre à jour depuis « main », pousser, faire relire, fusionner, supprimer la branche. Quand la plateforme propose les pull requests, la fusion se fait par ce biais. En local, tu peux la reproduire avec « git merge --no-ff ».

### La gestion du conflit en équipe

Un conflit en équipe se résout en lisant l’intention de chaque contributeur. Tu ne choisis pas au hasard : tu combines ou tu demandes à ton collègue. Après la résolution, tu relances ce qui peut être vérifié, tu commites et tu repousses.

### Livrer une version

Quand la fonctionnalité est stable sur « main », tu poses un tag de version. Il fige un état précis du projet que tu peux livrer au client ou retrouver plus tard.

## Exemple pas à pas

Le code d’exemple met en scène deux contributeurs sur un projet de « liste de courses ». À l’étape 1, on prépare le dépôt distant nu et un premier commit partagé. À l’étape 2, Aya et Koffi clonent le dépôt dans deux dossiers distincts.

À l’étape 3, chacun crée sa branche et modifie la même ligne du même fichier. À l’étape 4, Aya pousse et fusionne en premier. À l’étape 5, Koffi met à jour sa branche depuis « main », provoque le conflit, le résout et pousse. À l’étape 6, la fusion de Koffi est réalisée, les branches sont nettoyées et la version « v1.0.0 » est taguée. Enfin, « git log --graph » montre le résultat final.

## Erreurs fréquentes

- Oublier de configurer l’identité dans chaque dossier cloné : tous les commits portent le même auteur et la simulation perd son sens. Utilise « git config » sans « --global » dans chaque dossier.
- Travailler sur « main » au lieu de la branche : tu contournes le workflow. Vérifie avec « git branch » avant de commiter.
- Pousser avant de récupérer : le push est rejeté. Lance « git pull » puis résous.
- Résoudre un conflit en supprimant une des deux versions sans réfléchir : tu perds du travail. Lis les deux côtés et combine.
- Livrer un dépôt sans README ni historique lisible : il est difficile à évaluer. Rédige le README et des messages explicites.
- Oublier de pousser le tag : « git push » n’envoie pas les tags. Utilise « git push origin v1.0.0 ».

## Bonnes pratiques

- Vérifie ton historique avec « git log --oneline --graph --all » avant de livrer.
- Garde des commits atomiques avec une convention de messages.
- Mets à jour ta branche depuis « main » avant chaque fusion.
- Ajoute un README qui explique le projet et comment contribuer.
- Teste le résultat final après résolution d’un conflit.

## Auto-évaluation

- Comment simuler deux développeurs sur une seule machine ?
- Quelles étapes composent le cycle complet d’une fonctionnalité ?
- Comment résous-tu un conflit sans perdre le travail d’un collègue ?
- Pourquoi un tag n’est-il pas poussé avec « git push » simple ?
- Quelles commandes permettent de vérifier la forme finale de l’historique ?

## À retenir

- Plusieurs dossiers clonés permettent de simuler une équipe réelle.
- Le workflow reste le même : branche, commits, mise à jour, push, fusion.
- Un conflit se résout en comprenant les deux versions.
- Les tags marquent les versions et se poussent explicitement.
- Un historique propre est une carte de visite professionnelle.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : dépôt distant nu + premier commit partagé
mkdir projet-final && cd projet-final
git init --bare -b main courses.git
git clone courses.git initial && cd initial
git config user.name "Admin" && git config user.email "admin@example.com"
printf "# Liste de courses\nMarché du samedi\n" > README.md
echo "riz" > liste.txt
git add . && git commit -m "docs: initialise la liste de courses"
git push -u origin main
cd ..

# Étape 2 : deux contributeurs clonent le dépôt
git clone courses.git aya && git clone courses.git koffi
(cd aya && git config user.name "Aya" && git config user.email "aya@example.com")
(cd koffi && git config user.name "Koffi" && git config user.email "koffi@example.com")

# Étape 3 : chacun modifie la MÊME ligne sur sa branche
cd aya && git switch -c feature/liste-aya
echo "riz parfumé" > liste.txt
git commit -am "feat: précise le type de riz"
git push -u origin feature/liste-aya
cd ../koffi && git switch -c feature/liste-koffi
echo "riz 25kg" > liste.txt
git commit -am "feat: précise la quantité de riz"
git push -u origin feature/liste-koffi

# Étape 4 : Aya fusionne en premier (simule sa pull request validée)
cd ../aya && git switch main
git merge --no-ff feature/liste-aya -m "merge: intègre la liste d'Aya"
git push

# Étape 5 : Koffi met à jour sa branche, rencontre le conflit et le résout
cd ../koffi && git fetch origin
git merge origin/main        # CONFLIT sur liste.txt
echo "riz parfumé 25kg" > liste.txt   # combinaison des deux idées
git add liste.txt && git commit -m "merge: combine type et quantité"
git push

# Étape 6 : fusion finale, version et vérification de l'historique
git switch main && git pull
git merge --no-ff feature/liste-koffi -m "merge: intègre la liste de Koffi"
git push
git tag -a v1.0.0 -m "Première version" && git push origin v1.0.0
git log --oneline --graph --all
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : dépôt collaboratif simulé',
            'exercise_description' => <<<'MD'
Réalise un dépôt collaboratif complet pour un projet « annuaire-prestataires ». Deux contributeurs simulés ajoutent chacun une information différente sur la même ligne d’un fichier, ce qui provoque un conflit que tu dois résoudre avant de livrer une version.

Livrables : un dépôt distant nu, deux clones avec identités distinctes, un README, un conflit résolu et un tag de version.

Critères de réussite :
- Un dépôt nu sert de remote et deux dossiers clonés ont des identités Git différentes (visibles dans « git log »).
- Chaque contributeur travaille sur sa propre branche et pousse avec « -u ».
- Un conflit réel est provoqué, puis résolu en combinant les deux informations sans marqueur résiduel.
- Les deux branches sont fusionnées dans « main » avec « --no-ff » et poussées.
- Un tag « v1.0.0 » est poussé et « git log --oneline --graph --all » montre les fusions.
MD,
            'exercise_hint' => 'Réutilise la structure de l’exemple. Pour provoquer le conflit, fais modifier la ligne « Plombier » du fichier annuaire.txt de deux façons différentes. Pense à pousser le tag explicitement.',
            'exercise_solution' => <<<'CODE'
mkdir annuaire && cd annuaire
git init --bare -b main annuaire.git

# Dépôt initial
git clone annuaire.git base && cd base
git config user.name "Admin" && git config user.email "admin@example.com"
printf "# Annuaire des prestataires\n" > README.md
echo "Plombier : à renseigner" > annuaire.txt
git add . && git commit -m "docs: initialise l'annuaire"
git push -u origin main
cd ..

# Deux contributeurs
git clone annuaire.git aya && git clone annuaire.git koffi
(cd aya && git config user.name "Aya" && git config user.email "aya@example.com")
(cd koffi && git config user.name "Koffi" && git config user.email "koffi@example.com")

# Aya ajoute le nom
cd aya && git switch -c feature/nom
echo "Plombier : M. Diallo" > annuaire.txt
git commit -am "feat: ajoute le nom du plombier"
git push -u origin feature/nom

# Koffi ajoute le téléphone sur la même ligne
cd ../koffi && git switch -c feature/telephone
echo "Plombier : 07 00 00 00 00" > annuaire.txt
git commit -am "feat: ajoute le téléphone du plombier"
git push -u origin feature/telephone

# Aya fusionne en premier
cd ../aya && git switch main
git merge --no-ff feature/nom -m "merge: ajoute le nom"
git push

# Koffi rencontre le conflit et le résout
cd ../koffi && git fetch origin
git merge origin/main   # CONFLIT
echo "Plombier : M. Diallo - 07 00 00 00 00" > annuaire.txt
git add annuaire.txt && git commit -m "merge: combine nom et téléphone"
git push

# Fusion finale, tag, vérification
git switch main && git pull
git merge --no-ff feature/telephone -m "merge: ajoute le téléphone"
git push
git tag -a v1.0.0 -m "Version 1.0.0" && git push origin v1.0.0
git log --oneline --graph --all
CODE,
        ],
    ],
];
