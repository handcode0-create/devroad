<?php

return [
    'lessons' => [
        'Découvrir GitHub' => [
            'description' => 'Découvre ce que GitHub ajoute à Git : hébergement, collaboration, issues, pull requests et automatisation. Tu crées ton profil et tu publies un premier dépôt.',
            'objective' => 'Publier un dépôt local sur GitHub avec un README clair et expliquer le rôle des principales fonctionnalités de la plateforme.',
            'content' => <<<'MD'
## Pourquoi cette notion

Git fonctionne très bien tout seul, mais il ne sait pas organiser le travail d’une équipe : qui doit corriger quel bug, qui relit quel code, comment présenter un projet au monde. GitHub répond à ces besoins. C’est la plus grande plateforme d’hébergement de code, utilisée par les entreprises, les agences et les projets open source.

Pour un développeur, le profil GitHub joue le rôle d’un CV vivant. Un recruteur ou un client qui visite ton profil y voit tes projets, ta régularité et la qualité de ton travail.

## Les concepts clés

### GitHub n’est pas Git

Git est l’outil de versionnement qui tourne sur ta machine. GitHub est un service en ligne qui héberge des dépôts Git et y ajoute une couche collaborative. D’autres plateformes similaires existent, mais les notions apprises ici se transposent facilement.

### Les briques de la plateforme

Un repository, ou dépôt, contient le code et son historique. Les issues servent à décrire des bugs, des tâches ou des idées. Les pull requests proposent un changement à relire avant fusion. Les Projects offrent un tableau pour organiser le travail. Les Actions automatisent des tâches comme les tests. Les Discussions permettent d’échanger de façon moins formelle.

### Le README

Le fichier README.md, affiché automatiquement sur la page d’accueil du dépôt, est la vitrine du projet. Il explique ce que fait le projet, comment l’installer et comment l’utiliser. Il est écrit en Markdown, un format de texte léger.

### Visibilité et permissions

Un dépôt est public, visible par tous, ou privé, réservé aux personnes invitées. Les collaborateurs reçoivent des rôles qui limitent leurs droits, par exemple lecture, écriture ou administration.

### Authentification

Pour pousser du code, GitHub exige une authentification : un jeton d’accès personnel en HTTPS, ou une clé SSH enregistrée sur ton compte. Le mot de passe du compte n’est plus accepté par les commandes Git.

## Exemple pas à pas

Le code d’exemple montre deux façons de publier un projet. À l’étape 1, tu prépares le dépôt local avec un README. À l’étape 2, tu crées le dépôt sur GitHub, soit via le site, soit avec l’outil en ligne de commande « gh » si tu l’as installé.

À l’étape 3, tu relies le dépôt local au remote et tu pousses. À l’étape 4, tu clones un dépôt existant, ce qui est le geste le plus fréquent quand tu rejoins un projet. Enfin, à l’étape 5, tu ouvres la page du dépôt : le README s’y affiche, preuve que la publication a réussi.

## Erreurs fréquentes

- Confondre Git et GitHub : tu penses ne pas pouvoir utiliser Git sans compte. Git fonctionne seul, GitHub est un service optionnel.
- Utiliser son mot de passe pour pousser : l’authentification échoue. Crée un jeton d’accès ou une clé SSH.
- Publier un dépôt public contenant des secrets : n’importe qui peut les lire. Vérifie le contenu avant de pousser et utilise un dépôt privé en cas de doute.
- Négliger le README : personne ne comprend le projet. Rédige-le dès le premier commit.
- Créer le dépôt GitHub avec un README alors que le dépôt local existe déjà : les historiques divergent au premier push. Crée un dépôt distant vide.
- Pousser sur la mauvaise branche : le dépôt semble vide. Vérifie le nom de ta branche avec « git branch ».

## Bonnes pratiques

- Choisis un nom de dépôt court, en minuscules avec des tirets.
- Rédige un README avec description, installation et utilisation.
- Ajoute une licence si le projet est public.
- Utilise une clé SSH ou un jeton plutôt que des mots de passe.
- Épingle sur ton profil les projets dont tu es le plus fier.

## Auto-évaluation

- Quelle différence y a-t-il entre Git et GitHub ?
- À quoi servent les issues, les pull requests et les Actions ?
- Pourquoi le README est-il important ?
- Comment t’authentifies-tu pour pousser du code ?
- Quelle différence y a-t-il entre un dépôt public et privé ?

## À retenir

- GitHub héberge des dépôts Git et ajoute la collaboration.
- Issues, pull requests, Projects et Actions structurent le travail.
- Le README est la vitrine du projet.
- Un jeton ou une clé SSH remplace le mot de passe.
- Ton profil GitHub est un portfolio vivant.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : préparer un dépôt local avec un README
mkdir boutique-en-ligne && cd boutique-en-ligne
git init -b main
cat > README.md <<'EOF'
# Boutique en ligne

Catalogue de pagnes et accessoires, paiement par mobile money.

## Installation
Ouvrir index.html dans un navigateur.
EOF
git add README.md
git commit -m "docs: ajoute le README"

# Étape 2 : créer un dépôt VIDE sur GitHub (via le site, ou avec gh)
gh auth login                    # se connecter une fois
gh repo create boutique-en-ligne --public --source=. --remote=origin

# Étape 3 (variante sans gh) : relier manuellement puis pousser
# git remote add origin git@github.com:TON-COMPTE/boutique-en-ligne.git
git push -u origin main

# Étape 4 : cloner un dépôt existant pour rejoindre un projet
cd ..
git clone https://github.com/TON-COMPTE/boutique-en-ligne.git copie-test

# Étape 5 : ouvrir la page du dépôt dans le navigateur
cd boutique-en-ligne
gh repo view --web
CODE,
            'estimated_minutes' => 40,
            'exercise_title' => 'Publier ton premier projet sur GitHub',
            'exercise_description' => <<<'MD'
Crée un dépôt local « portfolio-smith » avec un README présentable, publie-le sur GitHub, puis clone-le dans un autre dossier pour vérifier que tout est en ligne.

Critères de réussite :
- Le dépôt local contient un README.md avec un titre, une description et une section « Contact ».
- Le dépôt GitHub correspondant existe et le README s’y affiche.
- La branche « main » locale suit « origin/main » (vérifiable avec « git branch -vv »).
- Un clone dans un second dossier contient le même README.
- Aucun fichier secret n’est présent dans le dépôt.
MD,
            'exercise_hint' => 'Crée le dépôt GitHub vide, sans README ni licence, avant de pousser. Utilise « git remote -v » pour contrôler l’URL.',
            'exercise_solution' => <<<'CODE'
mkdir portfolio-smith && cd portfolio-smith
git init -b main

cat > README.md <<'EOF'
# Portfolio

Mes projets de développement web.

## Contact
Email : contact@example.com
EOF

git add README.md
git commit -m "docs: ajoute le README du portfolio"

# Création du dépôt distant vide puis publication
gh repo create portfolio-smith --public --source=. --remote=origin
git push -u origin main

# Vérifications
git remote -v
git branch -vv

# Clone dans un autre dossier
cd ..
git clone https://github.com/TON-COMPTE/portfolio-smith.git verification
cat verification/README.md
CODE,
        ],

        'Repositories et branches' => [
            'description' => 'Configure un dépôt propre : README, .gitignore, licence, règles de contribution et protection de la branche principale.',
            'objective' => 'Mettre en place un dépôt GitHub professionnel avec fichiers de base, stratégie de branches et protection de la branche main.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un dépôt mal préparé fait perdre du temps à tout le monde : les nouveaux arrivants ne savent pas comment installer le projet, les contributeurs ne savent pas quelles règles suivre et n’importe qui peut casser la branche principale. Une agence ou une équipe produit prépare donc chaque dépôt avec soin dès le premier jour.

Cette préparation montre aussi ton sérieux à un client qui te confie son code : il voit un projet cadré, pas un dossier jeté en ligne.

## Les concepts clés

### Les fichiers de base

Un dépôt bien tenu contient plusieurs fichiers. Le README présente le projet. Le fichier « .gitignore » exclut ce qui ne doit pas être versionné. Le fichier « LICENSE » précise les droits d’utilisation. Le fichier « CONTRIBUTING.md » explique comment proposer des changements. GitHub reconnaît aussi un dossier spécial « .github » qui contient des modèles et des workflows.

### La stratégie de branches

La stratégie la plus simple est celle d’une branche principale stable et de branches courtes par tâche, comme « feature/recherche » ou « fix/total-panier ». Pour les petites équipes, c’est largement suffisant. Les stratégies plus lourdes, avec des branches de développement et de version, conviennent surtout aux produits à cycles de livraison longs.

### Protéger la branche principale

GitHub permet de définir des règles de protection sur une branche. Tu peux interdire les poussées directes, exiger une pull request, demander au moins une approbation et imposer que les vérifications automatiques réussissent avant la fusion. Ces règles se trouvent dans les paramètres du dépôt. Leur disponibilité peut dépendre du type de compte et de la visibilité du dépôt.

### Les modèles

Un modèle de pull request, placé dans « .github », pré-remplit la description avec une checklist. Il guide les contributeurs et uniformise les revues.

### Collaborateurs et rôles

Tu invites des personnes en leur donnant un rôle adapté. Le principe du moindre privilège veut qu’on accorde uniquement les droits nécessaires.

## Exemple pas à pas

Le code d’exemple structure un projet de réservation. À l’étape 1, tu crées l’arborescence avec le README, le « .gitignore » et la licence. À l’étape 2, tu ajoutes un guide de contribution décrivant la convention de branches.

À l’étape 3, tu crées le modèle de pull request dans « .github ». À l’étape 4, tu commites et tu pousses sur « main ». À l’étape 5, tu crées une branche de fonctionnalité et tu la publies. À l’étape 6, tu actives la protection de « main » via les paramètres du dépôt, ou avec « gh api » si tu préfères la ligne de commande.

## Erreurs fréquentes

- Laisser « main » ouverte à tous : une erreur est poussée directement en production. Active la protection de branche.
- Oublier le « .gitignore » : dépendances et secrets entrent dans l’historique. Ajoute-le avant le premier commit.
- Ne pas mettre de licence sur un projet public : les autres n’ont légalement pas le droit de le réutiliser. Choisis une licence adaptée.
- Nommer les branches « test » ou « new » : personne ne comprend leur objet. Utilise des préfixes et des noms explicites.
- Donner des droits d’administration à tout le monde : un faux geste suffit à supprimer le dépôt. Applique le moindre privilège.
- Laisser vivre des branches mortes : le dépôt devient confus. Supprime les branches fusionnées.

## Bonnes pratiques

- Prépare README, .gitignore et licence avant le premier push.
- Documente la stratégie de branches dans CONTRIBUTING.md.
- Protège la branche principale dès que plusieurs personnes contribuent.
- Utilise des modèles de pull request pour standardiser les revues.
- Supprime automatiquement les branches après fusion si l’option est disponible.

## Auto-évaluation

- Quels fichiers fait-on figurer dans un dépôt bien préparé ?
- Qu’est-ce que la protection de branche permet d’imposer ?
- À quoi sert le dossier « .github » ?
- Pourquoi appliquer le moindre privilège aux collaborateurs ?
- Comment nommer clairement une branche de correction ?

## À retenir

- Un dépôt propre commence par README, .gitignore et licence.
- Les branches courtes avec préfixes gardent l’historique lisible.
- La protection de branche sécurise la branche principale.
- Les modèles dans « .github » uniformisent les contributions.
- Donne à chacun uniquement les droits dont il a besoin.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : structure de base du projet de réservation
mkdir reservation-salon && cd reservation-salon
git init -b main
mkdir -p .github

cat > README.md <<'EOF'
# Réservation salon
Application de prise de rendez-vous pour un salon de coiffure.
EOF

# Exclure ce qui ne doit jamais être versionné
printf ".env\nnode_modules/\n*.log\n" > .gitignore

# Licence (exemple minimal, à remplacer par le texte complet choisi)
echo "MIT License - Copyright (c) 2026 Mon Studio" > LICENSE

# Étape 2 : guide de contribution avec la convention de branches
cat > CONTRIBUTING.md <<'EOF'
# Contribuer
- Une branche par tâche : feature/..., fix/..., docs/...
- Messages de commit : feat:, fix:, docs:
- Toute modification passe par une pull request relue.
EOF

# Étape 3 : modèle de pull request
cat > .github/pull_request_template.md <<'EOF'
## Description
Qu'est-ce qui change et pourquoi ?

## Vérifications
- [ ] J'ai testé mon changement
- [ ] J'ai mis à jour la documentation
EOF

# Étape 4 : premier commit puis publication
git add . && git commit -m "chore: structure initiale du dépôt"
gh repo create reservation-salon --public --source=. --remote=origin --push

# Étape 5 : branche de fonctionnalité publiée
git switch -c feature/prise-rendez-vous
git push -u origin feature/prise-rendez-vous

# Étape 6 : protection de main : à faire dans Settings > Branches (ou via gh api)
# Règles conseillées : pull request obligatoire + 1 approbation minimum
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Préparer un dépôt professionnel',
            'exercise_description' => <<<'MD'
Prépare le dépôt « suivi-stock-maquis » comme le ferait une équipe sérieuse, avec ses fichiers de base et une branche de travail.

Critères de réussite :
- Le dépôt contient README.md, .gitignore (excluant « .env »), LICENSE et CONTRIBUTING.md.
- Le dossier « .github » contient un modèle de pull request avec une checklist.
- Le CONTRIBUTING.md décrit la convention de nommage des branches.
- La branche « feature/liste-produits » existe sur GitHub.
- La protection de la branche « main » est activée dans les paramètres, ou tu as noté les règles choisies si ton compte ne le permet pas.
MD,
            'exercise_hint' => 'Crée tous les fichiers avant le premier commit. Pour la protection de branche, va dans les paramètres du dépôt, section des règles de branches.',
            'exercise_solution' => <<<'CODE'
mkdir suivi-stock-maquis && cd suivi-stock-maquis
git init -b main
mkdir -p .github

printf "# Suivi de stock du maquis\nGestion des boissons et des ventes.\n" > README.md
printf ".env\nnode_modules/\n*.log\n" > .gitignore
echo "MIT License - Copyright (c) 2026" > LICENSE

cat > CONTRIBUTING.md <<'EOF'
# Contribuer
Nommage des branches : feature/<sujet>, fix/<sujet>, docs/<sujet>.
Chaque changement passe par une pull request relue.
EOF

cat > .github/pull_request_template.md <<'EOF'
## Description
## Vérifications
- [ ] Testé en local
- [ ] Documentation à jour
EOF

git add . && git commit -m "chore: structure initiale du dépôt"
gh repo create suivi-stock-maquis --public --source=. --remote=origin --push

git switch -c feature/liste-produits
git push -u origin feature/liste-produits

# Protection de main : Settings > Branches > Add rule pour « main »
# - Require a pull request before merging (1 approbation)
CODE,
        ],

        'Pull Requests' => [
            'description' => 'Apprends le cycle complet d’une pull request : création, description, revue, corrections et fusion. Tu découvres les types de fusion proposés par GitHub.',
            'objective' => 'Créer une pull request claire depuis une branche, répondre à une revue, puis la fusionner proprement.',
            'content' => <<<'MD'
## Pourquoi cette notion

La pull request est le cœur du travail collaboratif sur GitHub. Elle propose un changement, l’explique et le soumet à la relecture avant qu’il n’atteigne la branche principale. Elle évite que des erreurs passent en production, elle diffuse la connaissance dans l’équipe et elle laisse une trace de chaque décision.

Dans une entreprise, aucun code n’arrive en production sans pull request approuvée. Savoir en rédiger une bonne et relire celle d’un collègue est une compétence centrale.

## Les concepts clés

### Anatomie d’une pull request

Une pull request relie une branche source à une branche cible, généralement « main ». Elle comporte un titre, une description, la liste des commits, les fichiers modifiés avec leurs différences, un fil de discussion et le résultat des vérifications automatiques.

### Rédiger une bonne description

Une bonne description répond à trois questions : quel problème résout-elle, qu’est-ce qui change, et comment le vérifier. Tu peux lier une issue avec une formule comme « Closes #12 », qui fermera automatiquement l’issue à la fusion. Une pull request de taille raisonnable, centrée sur un seul sujet, est relue plus vite et plus sérieusement.

### La revue de code

Le reviewer examine le comportement, la lisibilité, les risques et les tests. Il peut commenter des lignes précises, puis choisir entre trois verdicts : commenter, approuver ou demander des changements. L’auteur répond, corrige en ajoutant de nouveaux commits sur la même branche, et la pull request se met à jour automatiquement.

### Les modes de fusion

GitHub propose généralement trois façons de fusionner. Le merge commit conserve tous les commits et ajoute un commit de fusion. Le « squash and merge » condense tous les commits de la branche en un seul. Le « rebase and merge » rejoue les commits sans commit de fusion. Le choix dépend des habitudes de l’équipe.

### Les brouillons

Une pull request en brouillon, ou « draft », signale un travail en cours et empêche la fusion tant qu’elle n’est pas marquée prête.

## Exemple pas à pas

Le code d’exemple déroule le cycle avec l’outil « gh ». À l’étape 1, tu crées une branche et tu commites une fonctionnalité de recherche. À l’étape 2, tu publies la branche. À l’étape 3, tu ouvres la pull request avec un titre et une description qui lie l’issue.

À l’étape 4, le reviewer demande un changement : tu corriges et tu pousses un nouveau commit, la pull request se met à jour. À l’étape 5, tu consultes l’état et les vérifications. À l’étape 6, une fois approuvée, tu fusionnes avec « squash », tu supprimes la branche et tu mets à jour ton « main » local.

## Erreurs fréquentes

- Ouvrir une pull request énorme mêlant plusieurs sujets : elle est relue en diagonale. Découpe en pull requests ciblées.
- Laisser une description vide : le reviewer doit deviner le contexte. Explique le pourquoi et comment tester.
- Ouvrir la pull request vers la mauvaise branche : le changement part au mauvais endroit. Vérifie la branche cible avant de valider.
- Fusionner sans attendre la revue ni les vérifications : tu contournes le filet de sécurité. Attends l’approbation.
- Créer une nouvelle pull request pour chaque correction de revue : l’historique se disperse. Pousse simplement de nouveaux commits sur la même branche.
- Prendre les commentaires comme une attaque : la revue vise le code, pas toi. Réponds avec ouverture.

## Bonnes pratiques

- Garde des pull requests courtes et centrées sur un seul objectif.
- Rédige un titre clair et une description avec contexte et étapes de test.
- Relis ta propre pull request avant de demander une revue.
- Sois précis et bienveillant quand tu relis le code des autres.
- Supprime la branche après la fusion.

## Auto-évaluation

- Quels éléments compose une pull request ?
- Comment fermer automatiquement une issue à la fusion ?
- Quelle différence y a-t-il entre merge commit, squash et rebase ?
- Comment réagir à une demande de changements ?
- À quoi sert une pull request en brouillon ?

## À retenir

- La pull request propose, explique et fait relire un changement.
- Une bonne description donne le problème, le changement et le test.
- Les corrections se poussent sur la même branche.
- Trois modes de fusion existent, selon la politique de l’équipe.
- Petites pull requests, relectures meilleures.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : créer la branche et développer la fonctionnalité de recherche
git switch main && git pull
git switch -c feature/recherche-produit
echo "Recherche par nom de produit" > recherche.txt
git add recherche.txt
git commit -m "feat: ajoute la recherche par nom de produit"

# Étape 2 : publier la branche
git push -u origin feature/recherche-produit

# Étape 3 : ouvrir la pull request (la description lie l'issue n°12)
gh pr create \
  --base main \
  --title "feat: recherche de produits par nom" \
  --body "## Contexte
Les clients veulent retrouver un produit rapidement.

## Changements
- Ajout de la recherche par nom

## Comment tester
Taper un nom de produit et vérifier les résultats.

Closes #12"

# Étape 4 : le relecteur demande un changement => on corrige sur la MÊME branche
echo "Recherche insensible à la casse" >> recherche.txt
git commit -am "fix: rend la recherche insensible à la casse"
git push    # la pull request se met à jour toute seule

# Étape 5 : suivre l'état de la pull request et des vérifications
gh pr view
gh pr checks

# Étape 6 : après approbation, fusion en squash et nettoyage
gh pr merge --squash --delete-branch
git switch main && git pull
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Ouvrir et fusionner une pull request',
            'exercise_description' => <<<'MD'
Dans ton dépôt d’entraînement, propose la fonctionnalité « affichage du prix en FCFA » via une pull request complète, avec une correction après revue (tu peux t’auto-relire).

Critères de réussite :
- La fonctionnalité est développée sur la branche « feature/prix-fcfa » avec au moins deux commits.
- La pull request cible « main » et contient une description avec contexte, changements et test.
- La description contient une formule « Closes # » vers une issue existante.
- Une correction est poussée sur la même branche après un commentaire de revue.
- La pull request est fusionnée et la branche distante est supprimée.
MD,
            'exercise_hint' => 'Crée d’abord une issue pour avoir un numéro à lier. Utilise « gh pr create --body » avec ta description et « gh pr merge » pour terminer.',
            'exercise_solution' => <<<'CODE'
# Issue à lier
gh issue create --title "Afficher les prix en FCFA" --body "Les prix doivent s'afficher en FCFA."
# On suppose que l'issue porte le numéro 1

git switch main && git pull
git switch -c feature/prix-fcfa

echo "Prix : 5000 FCFA" > prix.txt
git add prix.txt && git commit -m "feat: affiche le prix en FCFA"
git push -u origin feature/prix-fcfa

gh pr create --base main \
  --title "feat: affichage du prix en FCFA" \
  --body "## Contexte
Les clients lisent les prix en FCFA.

## Changements
- Format du prix en FCFA

## Comment tester
Ouvrir la page produit et lire le prix.

Closes #1"

# Correction après revue, sur la même branche
echo "Séparateur de milliers : 5 000 FCFA" >> prix.txt
git commit -am "fix: ajoute le séparateur de milliers"
git push

# Fusion et nettoyage
gh pr merge --squash --delete-branch
git switch main && git pull
CODE,
        ],

        'Issues et projet' => [
            'description' => 'Transforme les besoins et les bugs en travail traçable grâce aux issues, labels, milestones et tableaux Projects.',
            'objective' => 'Rédiger des issues exploitables, les organiser avec labels et milestone, et les suivre dans un tableau de projet.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans un projet réel, les demandes arrivent de partout : un client écrit sur WhatsApp, un testeur signale un bug, un collègue a une idée. Si tout reste dans des messages éparpillés, rien n’est suivi et tout se perd. Les issues centralisent chaque besoin dans un endroit unique, daté, discuté et lié au code qui le traite.

C’est aussi ton outil de pilotage : en un coup d’œil, tu sais ce qui est fait, en cours ou à faire, et tu peux rendre des comptes à un client.

## Les concepts clés

### Qu’est-ce qu’une issue

Une issue est une fiche numérotée qui décrit un bug, une tâche ou une demande de fonctionnalité. Elle possède un titre, une description, des commentaires, un ou plusieurs assignés et des étiquettes. Chaque issue reçoit un numéro, par exemple « #12 », que tu peux citer dans les commits et les pull requests.

### Écrire une issue exploitable

Pour un bug, indique les étapes pour le reproduire, le comportement attendu et le comportement observé, avec le contexte utile comme le navigateur ou l’appareil. Pour une fonctionnalité, décris le besoin de l’utilisateur et le résultat souhaité. Une issue claire se traite sans aller poser dix questions.

### Labels et milestones

Les labels classent les issues : « bug », « enhancement », « documentation », ou des étiquettes de priorité. Un milestone regroupe des issues autour d’un objectif daté, comme « Version 1.0 » ou « Livraison client ». Il affiche la progression en pourcentage.

### GitHub Projects

Un Project est un tableau qui organise les issues et les pull requests en colonnes comme « À faire », « En cours » et « Terminé ». Tu peux le consulter en vue tableau ou en vue liste, et ajouter des champs comme la priorité ou l’échéance.

### Lier le code aux issues

Écrire « Closes #12 » ou « Fixes #12 » dans une pull request ferme l’issue au moment de la fusion. Tu obtiens ainsi une traçabilité complète entre le besoin, le code et la livraison.

## Exemple pas à pas

Le code d’exemple utilise « gh » pour piloter les issues depuis le terminal. À l’étape 1, tu crées les labels propres à ton projet. À l’étape 2, tu crées un milestone « v1.0 ». À l’étape 3, tu ouvres une issue de bug bien rédigée avec ses étapes de reproduction.

À l’étape 4, tu ouvres une issue de fonctionnalité en l’assignant à toi-même et en lui donnant un label. À l’étape 5, tu listes les issues ouvertes avec un filtre. À l’étape 6, tu commentes une issue, puis tu la fermes avec un message. Le tableau Projects se configure ensuite sur le site pour y glisser ces issues.

## Erreurs fréquentes

- Écrire un titre vague comme « ça marche pas » : personne ne sait de quoi il s’agit. Écris un titre précis qui décrit le symptôme.
- Oublier les étapes de reproduction d’un bug : le développeur ne peut pas le retrouver. Détaille chaque étape.
- Mélanger plusieurs sujets dans une même issue : le suivi devient impossible. Une issue égale un sujet.
- Ne pas assigner l’issue : tout le monde pense que quelqu’un d’autre s’en occupe. Assigne une personne responsable.
- Laisser des issues ouvertes pendant des mois sans mise à jour : le tableau perd sa crédibilité. Ferme ou reclasse régulièrement.
- Ne pas lier la pull request à l’issue : la traçabilité disparaît. Utilise « Closes # ».

## Bonnes pratiques

- Utilise des modèles d’issues pour imposer les informations nécessaires.
- Définis un petit ensemble de labels cohérents et garde-le stable.
- Regroupe le travail par milestones avec des dates réalistes.
- Mets à jour le tableau Projects au fil de l’eau.
- Cite toujours le numéro de l’issue dans la pull request correspondante.

## Auto-évaluation

- Quelles informations doit contenir une bonne issue de bug ?
- À quoi servent les labels et les milestones ?
- Comment fermer automatiquement une issue depuis une pull request ?
- Quelle est l’utilité d’un tableau Projects ?
- Pourquoi une issue ne doit-elle traiter qu’un seul sujet ?

## À retenir

- Une issue centralise un bug, une tâche ou une idée.
- Une issue claire se traite sans questions supplémentaires.
- Labels et milestones classent et planifient le travail.
- Les Projects visualisent l’avancement.
- Les mots-clés comme « Closes # » relient le code au besoin.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : créer des labels propres au projet
gh label create "priorité-haute" --color d73a4a --description "À traiter en premier"
gh label create "mobile-money" --color 0e8a16 --description "Paiements mobile money"

# Étape 2 : créer un milestone daté pour regrouper les issues (via l'API)
gh api repos/:owner/:repo/milestones \
  -f title="v1.0" \
  -f due_on="2026-12-31T00:00:00Z" \
  -f description="Première version livrée au client"

# Étape 3 : ouvrir une issue de bug bien rédigée
gh issue create \
  --title "Le total du panier est faux avec plus de 3 articles" \
  --label "bug,priorité-haute" \
  --milestone "v1.0" \
  --body "## Étapes pour reproduire
1. Ajouter 4 articles au panier
2. Ouvrir la page panier

## Comportement attendu
Total = somme des prix

## Comportement observé
Le dernier article n'est pas compté

## Contexte
Navigateur mobile, connexion lente"

# Étape 4 : issue de fonctionnalité, assignée à soi-même
gh issue create \
  --title "Ajouter le paiement Orange Money" \
  --label "enhancement,mobile-money" \
  --assignee "@me" \
  --body "Les clients veulent payer avec Orange Money au moment de la commande."

# Étape 5 : lister les issues ouvertes avec un filtre
gh issue list --label "bug" --state open

# Étape 6 : commenter puis fermer une issue
gh issue comment 1 --body "Corrigé dans la PR #4."
gh issue close 1 --reason completed
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Organiser un projet avec des issues',
            'exercise_description' => <<<'MD'
Pour le projet « livraison-repas », crée un petit système de suivi avec labels, milestone et trois issues bien rédigées, puis ferme-en une depuis une pull request.

Critères de réussite :
- Au moins deux labels personnalisés sont créés dans le dépôt.
- Un milestone « v1.0 » existe et contient les trois issues.
- Une issue de bug contient étapes de reproduction, comportement attendu et observé.
- Une issue de fonctionnalité est assignée à toi-même.
- Une pull request contenant « Closes # » ferme automatiquement l’une des issues à sa fusion.
MD,
            'exercise_hint' => 'Crée le milestone avant les issues pour pouvoir les y rattacher. Teste la fermeture automatique avec une pull request minuscule, par exemple la modification d’une ligne du README.',
            'exercise_solution' => <<<'CODE'
# Labels
gh label create "livraison" --color 1d76db --description "Sujet livraison"
gh label create "urgent" --color d73a4a --description "À traiter vite"

# Milestone
gh api repos/:owner/:repo/milestones -f title="v1.0" -f description="Première version"

# Issue 1 : bug détaillé
gh issue create --title "Le prix de livraison ne s'affiche pas sur mobile" \
  --label "bug,urgent" --milestone "v1.0" \
  --body "## Étapes pour reproduire
1. Ouvrir la page de commande sur mobile
2. Choisir une adresse

## Attendu
Le prix de livraison s'affiche

## Observé
Le champ reste vide"

# Issue 2 : fonctionnalité assignée
gh issue create --title "Suivre la position du livreur" \
  --label "enhancement,livraison" --milestone "v1.0" --assignee "@me" \
  --body "Le client veut voir où est son livreur."

# Issue 3 : documentation
gh issue create --title "Documenter l'installation" \
  --label "documentation" --milestone "v1.0" \
  --body "Ajouter la section installation au README."

# Fermeture automatique par pull request
git switch -c docs/installation
echo "## Installation" >> README.md
git commit -am "docs: ajoute la section installation"
git push -u origin docs/installation
gh pr create --title "docs: section installation" --body "Closes #3"
gh pr merge --squash --delete-branch
CODE,
        ],

        'GitHub Actions' => [
            'description' => 'Découvre l’intégration continue avec GitHub Actions : workflows, événements, jobs et steps pour lancer automatiquement tes tests.',
            'objective' => 'Écrire un workflow YAML qui s’exécute à chaque push et pull request pour installer les dépendances et lancer les tests.',
            'content' => <<<'MD'
## Pourquoi cette notion

Lancer les tests à la main avant chaque fusion, c’est oublier un jour. Quand l’oubli arrive, un bug part en production. L’intégration continue, souvent appelée CI, automatise ces vérifications : à chaque changement, un serveur installe le projet, exécute les tests et signale le résultat directement dans la pull request.

C’est un standard en entreprise. Une pull request dont la CI est rouge ne se fusionne pas, et cette règle simple évite énormément d’incidents.

## Les concepts clés

### Le vocabulaire

Un workflow est un processus automatisé défini dans un fichier YAML placé dans le dossier « .github/workflows ». Il se déclenche sur un événement comme un push ou une pull request. Il contient un ou plusieurs jobs, qui s’exécutent sur une machine appelée runner. Chaque job est une suite de steps, c’est-à-dire d’étapes qui lancent une commande ou utilisent une action réutilisable.

### Les déclencheurs

La clé « on » définit quand le workflow se lance : « push », « pull_request », un déclenchement manuel avec « workflow_dispatch », ou un horaire avec « schedule ». Tu peux restreindre à certaines branches pour économiser des minutes d’exécution.

### Jobs et steps

La clé « runs-on » choisit le système du runner, souvent un Linux. La clé « steps » liste les étapes dans l’ordre. Une étape avec « uses » emploie une action existante, comme « actions/checkout » pour récupérer le code. Une étape avec « run » exécute une commande shell. Les jobs s’exécutent en parallèle par défaut, et la clé « needs » impose un ordre entre eux.

### Matrice et cache

Une stratégie en matrice lance le même job avec plusieurs versions d’un langage. Le cache de dépendances accélère les exécutions suivantes en évitant de tout retélécharger.

### Lire le résultat

L’onglet « Actions » du dépôt affiche chaque exécution, ses logs et son statut. Une coche verte signale le succès, une croix rouge l’échec. Dans une pull request, le statut apparaît en bas et peut bloquer la fusion si la protection de branche l’exige.

## Exemple pas à pas

Le code d’exemple est un workflow pour une application Node.js. Les premières lignes donnent un nom et déclenchent le workflow sur les push vers « main » et sur toutes les pull requests. Les permissions sont réduites à la lecture.

Le job « tests » tourne sur un runner Linux. L’étape 1 récupère le code avec « actions/checkout ». L’étape 2 installe Node.js et active le cache npm. L’étape 3 installe les dépendances avec « npm ci ». L’étape 4 lance le linter, et l’étape 5 lance les tests. Si une commande échoue, le job entier échoue et la pull request affiche une croix rouge.

## Erreurs fréquentes

- Placer le fichier au mauvais endroit : le workflow ne se lance jamais. Il doit se trouver dans « .github/workflows » avec l’extension « .yml » ou « .yaml ».
- Se tromper d’indentation en YAML : le fichier est invalide. Utilise des espaces, jamais des tabulations, et vérifie dans l’éditeur.
- Oublier « actions/checkout » : le runner n’a pas ton code. Ajoute cette étape en premier.
- Utiliser « npm install » au lieu de « npm ci » en CI : les versions peuvent varier d’une exécution à l’autre. Préfère « npm ci », reproductible.
- Écrire des secrets en clair dans le workflow : ils fuitent dans le dépôt. Utilise les secrets chiffrés de GitHub.
- Lancer le workflow sur tous les événements : tu consommes des minutes pour rien. Restreins les déclencheurs.

## Bonnes pratiques

- Garde des workflows courts et rapides, pour un retour en quelques minutes.
- Utilise le cache de dépendances.
- Donne des noms explicites aux workflows, jobs et étapes.
- Épingle les actions à une version précise pour la stabilité.
- Exige que la CI réussisse avant toute fusion.

## Auto-évaluation

- Que sont un workflow, un job et une step ?
- Où doit se trouver le fichier d’un workflow ?
- À quoi sert la clé « on » ?
- Quelle différence y a-t-il entre « uses » et « run » ?
- Pourquoi préférer « npm ci » en intégration continue ?

## À retenir

- Un workflow YAML dans « .github/workflows » automatise les vérifications.
- Un workflow contient des jobs, qui contiennent des steps.
- Les événements déclenchent l’exécution.
- La CI rouge doit bloquer la fusion.
- Restreins déclencheurs et permissions, et garde les workflows rapides.
MD,
            'code_example' => <<<'CODE'
# Fichier : .github/workflows/tests.yml
# Workflow d'intégration continue pour une application Node.js

name: Tests

# Déclencheurs : push sur main et toute pull request
on:
  push:
    branches: [main]
  pull_request:

# Permissions minimales : lecture seule sur le code
permissions:
  contents: read

jobs:
  tests:
    # Machine virtuelle Linux fournie par GitHub
    runs-on: ubuntu-latest

    steps:
      # Étape 1 : récupérer le code du dépôt
      - name: Récupérer le code
        uses: actions/checkout@v4

      # Étape 2 : installer Node.js avec cache des dépendances npm
      - name: Installer Node.js
        uses: actions/setup-node@v4
        with:
          node-version: 22
          cache: npm

      # Étape 3 : installation reproductible des dépendances
      - name: Installer les dépendances
        run: npm ci

      # Étape 4 : analyse statique du code
      - name: Linter
        run: npm run lint

      # Étape 5 : exécuter les tests (un échec ici fait échouer le job)
      - name: Lancer les tests
        run: npm test
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Ton premier workflow de tests',
            'exercise_description' => <<<'MD'
Ajoute à un projet Node.js minimal un workflow GitHub Actions qui vérifie chaque changement, puis observe son résultat dans l’onglet Actions.

Critères de réussite :
- Le fichier se trouve dans « .github/workflows/ci.yml » et est valide en YAML.
- Le workflow se déclenche sur les push vers « main » et sur les pull requests.
- Le job utilise « actions/checkout », installe Node.js et exécute « npm ci » puis « npm test ».
- Les permissions sont limitées à « contents: read ».
- Une exécution réussie (coche verte) est visible dans l’onglet Actions.
MD,
            'exercise_hint' => 'Ton projet doit avoir un package.json avec un script « test » et un fichier package-lock.json pour « npm ci ». Un script « test » qui lance simplement « node --test » suffit.',
            'exercise_solution' => <<<'CODE'
# Fichier : .github/workflows/ci.yml
name: CI

on:
  push:
    branches: [main]
  pull_request:

permissions:
  contents: read

jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - name: Récupérer le code
        uses: actions/checkout@v4

      - name: Installer Node.js
        uses: actions/setup-node@v4
        with:
          node-version: 22
          cache: npm

      - name: Installer les dépendances
        run: npm ci

      - name: Lancer les tests
        run: npm test

# Dans package.json, le script de test minimal :
#   "scripts": { "test": "node --test" }
# Puis : git add . && git commit -m "ci: ajoute le workflow de tests" && git push
CODE,
        ],

        'Sécurité du dépôt' => [
            'description' => 'Protège ton dépôt contre les fuites de secrets, les permissions excessives et les dépendances vulnérables grâce aux outils de sécurité de GitHub.',
            'objective' => 'Mettre en place secrets chiffrés, permissions minimales et surveillance des dépendances, et savoir réagir à une fuite de secret.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un développeur qui pousse par erreur une clé d’API de paiement dans un dépôt public s’expose à de lourdes conséquences : des robots scrutent GitHub en permanence à la recherche de secrets, et une clé exposée peut être exploitée en quelques minutes. Pour un projet qui manipule des paiements mobile money, un tel incident peut coûter de l’argent et la confiance d’un client.

La sécurité n’est pas un module réservé aux experts : ce sont des habitudes simples à appliquer dès le premier jour.

## Les concepts clés

### Ne jamais commiter de secret

Un secret est une information sensible : mot de passe, clé d’API, jeton, certificat. Il ne doit jamais figurer dans le code ni dans l’historique. On le place dans un fichier d’environnement ignoré par Git, et on fournit un fichier modèle sans valeurs réelles, souvent nommé « .env.example ».

### Les secrets GitHub

Pour qu’un workflow utilise une clé, par exemple pour déployer, on la stocke dans les secrets du dépôt ou de l’environnement. GitHub les chiffre et les masque dans les logs. Le workflow y accède par une expression comme « secrets.NOM_DU_SECRET ».

### Permissions minimales

Le principe du moindre privilège s’applique partout. Dans un workflow, la clé « permissions » limite ce que le jeton automatique peut faire. Pour les personnes, on accorde le rôle le plus bas suffisant. Pour les accès par jeton personnel, on choisit des droits restreints et une date d’expiration.

### Surveiller les dépendances

Ton projet dépend de dizaines de bibliothèques tierces, qui peuvent contenir des failles. GitHub propose plusieurs outils : le graphe de dépendances, les alertes de sécurité, et Dependabot qui ouvre automatiquement des pull requests pour mettre à jour les dépendances vulnérables ou obsolètes. L’analyse de secrets signale les clés poussées par erreur, selon les fonctionnalités disponibles pour ton type de dépôt.

### Réagir à une fuite

Si un secret a été commité, il faut le considérer comme compromis. Supprimer le fichier dans un nouveau commit ne suffit pas, car il reste dans l’historique. La première action est de révoquer et de régénérer la clé chez le fournisseur. Le nettoyage de l’historique vient ensuite, en complément.

## Exemple pas à pas

Le code d’exemple regroupe quatre protections. À l’étape 1, le « .gitignore » exclut « .env » et un « .env.example » documente les variables attendues. À l’étape 2, tu enregistres un secret avec « gh secret set » au lieu de l’écrire dans un fichier.

À l’étape 3, un workflow utilise ce secret via « secrets » et réduit ses permissions. À l’étape 4, un fichier « dependabot.yml » configure les mises à jour automatiques hebdomadaires de npm et des actions. Enfin, l’étape 5 rappelle la procédure en cas de fuite : révoquer, régénérer, puis nettoyer.

## Erreurs fréquentes

- Commiter le fichier « .env » : les clés sont publiques. Ajoute « .env » au « .gitignore » avant le premier commit.
- Croire qu’un commit de suppression efface le secret : il reste dans l’historique. Révoque la clé immédiatement.
- Afficher un secret avec « echo » dans un workflow : il peut apparaître dans les logs. Ne l’affiche jamais et laisse GitHub le masquer.
- Donner un jeton personnel avec tous les droits et sans expiration : un vol est catastrophique. Limite les droits et la durée.
- Ignorer les alertes de dépendances : les failles connues restent exploitables. Traite les alertes et fusionne les mises à jour proposées.
- Utiliser les mêmes clés en développement et en production : une fuite locale touche la production. Sépare les environnements.

## Bonnes pratiques

- Fournis un fichier « .env.example » sans valeurs réelles.
- Stocke les secrets dans GitHub Secrets, jamais dans le code.
- Réduis les permissions des workflows et des collaborateurs.
- Active Dependabot et traite régulièrement ses pull requests.
- Active l’authentification à deux facteurs sur ton compte.

## Auto-évaluation

- Pourquoi supprimer un fichier secret dans un nouveau commit est-il insuffisant ?
- Comment un workflow utilise-t-il un secret en toute sécurité ?
- Que signifie le principe du moindre privilège ?
- Que fait Dependabot ?
- Quelle est la première action à mener après une fuite de clé ?

## À retenir

- Un secret ne se commite jamais.
- Un secret exposé est un secret compromis : révoque-le.
- Les secrets GitHub sont chiffrés et masqués dans les logs.
- Les permissions minimales réduisent les dégâts possibles.
- Dependabot et les alertes surveillent tes dépendances.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : ignorer le fichier de secrets et documenter les variables attendues
printf ".env\n" >> .gitignore
cat > .env.example <<'EOF'
# Copier ce fichier en .env puis renseigner les vraies valeurs
CINETPAY_API_KEY=
DATABASE_URL=
EOF
git add .gitignore .env.example
git commit -m "chore: ignore .env et documente les variables"

# Étape 2 : enregistrer un secret chiffré dans GitHub (jamais dans le code)
gh secret set CINETPAY_API_KEY   # la valeur est demandée de façon masquée

# Étape 3 : utiliser le secret dans un workflow, avec permissions minimales
# Fichier : .github/workflows/deploy.yml
cat > .github/workflows/deploy.yml <<'EOF'
name: Déploiement
on:
  workflow_dispatch:
permissions:
  contents: read
jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Déployer
        run: ./deploy.sh
        env:
          CINETPAY_API_KEY: ${{ secrets.CINETPAY_API_KEY }}
EOF

# Étape 4 : mises à jour automatiques des dépendances avec Dependabot
mkdir -p .github
cat > .github/dependabot.yml <<'EOF'
version: 2
updates:
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "weekly"
EOF

# Étape 5 : en cas de fuite d'un secret
#  1. RÉVOQUER la clé chez le fournisseur et en générer une nouvelle
#  2. mettre à jour le secret : gh secret set CINETPAY_API_KEY
#  3. nettoyer l'historique ensuite (outil de réécriture), en complément
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Sécuriser un dépôt de paiement',
            'exercise_description' => <<<'MD'
Applique les protections essentielles à un dépôt « paiement-api » qui utilise une clé d’API de paiement mobile money.

Critères de réussite :
- Le fichier « .env » est dans le « .gitignore » et un « .env.example » sans valeur réelle est commité.
- La clé est enregistrée comme secret GitHub nommé « MOMO_API_KEY » et n’apparaît dans aucun fichier.
- Un workflow utilise ce secret via « secrets.MOMO_API_KEY » avec « permissions: contents: read ».
- Un fichier « dependabot.yml » configure des mises à jour hebdomadaires.
- Un fichier « SECURITY.md » décrit en trois lignes la procédure en cas de fuite de clé.
MD,
            'exercise_hint' => 'Ne crée jamais de vrai fichier « .env » dans le dépôt pendant l’exercice. Pour le secret, utilise « gh secret set » qui demande la valeur en saisie.',
            'exercise_solution' => <<<'CODE'
printf ".env\nnode_modules/\n" >> .gitignore
cat > .env.example <<'EOF'
MOMO_API_KEY=
EOF

gh secret set MOMO_API_KEY

mkdir -p .github/workflows
cat > .github/workflows/paiement.yml <<'EOF'
name: Vérification paiement
on:
  workflow_dispatch:
permissions:
  contents: read
jobs:
  verifier:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Vérifier la configuration
        run: ./verifier.sh
        env:
          MOMO_API_KEY: ${{ secrets.MOMO_API_KEY }}
EOF

cat > .github/dependabot.yml <<'EOF'
version: 2
updates:
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
EOF

cat > SECURITY.md <<'EOF'
# Sécurité
En cas de fuite de clé : 1) révoquer la clé chez le fournisseur,
2) générer une nouvelle clé et mettre à jour le secret GitHub,
3) nettoyer l'historique du dépôt.
EOF

git add . && git commit -m "chore: sécurise le dépôt de paiement" && git push
CODE,
        ],

        'Projet final GitHub' => [
            'description' => 'Assemble tout le parcours : dépôt structuré, issues, pull requests relues et intégration continue sur un mini-projet complet.',
            'objective' => 'Livrer un dépôt GitHub complet avec issues, pull requests reliées, workflow CI et protection de branche.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans un vrai projet, tout ce que tu as vu fonctionne ensemble : une demande devient une issue, l’issue devient une branche, la branche devient une pull request vérifiée par la CI, puis le tout est fusionné dans une branche protégée. Ce projet final te fait vivre ce cycle de bout en bout, comme dans une équipe produit.

Le résultat est un dépôt que tu peux montrer à un recruteur ou à un client pour prouver ta maîtrise du travail collaboratif.

## Les concepts clés

### Le flux complet

Le cycle est le suivant : un besoin est consigné dans une issue, tu crées une branche dédiée, tu développes par petits commits, tu ouvres une pull request qui cite l’issue, la CI s’exécute, un relecteur approuve, la fusion ferme l’issue et la branche est supprimée. Chaque maillon laisse une trace consultable.

### Une CI qui protège

Le workflow de tests est le garde-fou. En l’associant à la protection de branche, tu exiges que les vérifications réussissent avant toute fusion. Une pull request rouge ne peut donc pas atteindre « main ».

### Le dépôt comme vitrine

Le README doit expliquer le projet, l’installation, l’usage et la contribution. Un badge de statut de la CI peut s’y afficher. Les issues et les pull requests fermées montrent ta manière de travailler.

### Un projet à ta portée

Choisis un projet simple pour te concentrer sur le processus : une petite fonction JavaScript de calcul de frais de livraison avec un test, par exemple. Le but n’est pas la complexité du code, mais la qualité du flux de travail.

## Exemple pas à pas

Le code d’exemple suit un parcours en cinq temps. À l’étape 1, tu crées le dépôt avec ses fichiers de base. À l’étape 2, tu ajoutes le workflow de CI sur « main ». À l’étape 3, tu ouvres deux issues, une par fonctionnalité.

À l’étape 4, tu développes la première fonctionnalité sur sa branche et tu ouvres la pull request qui ferme l’issue, puis tu observes la CI. À l’étape 5, tu fais de même pour la seconde issue, tu fusionnes, et tu vérifies que les issues sont fermées automatiquement et que les workflows sont verts. Les règles de protection de « main » se configurent dans les paramètres.

## Erreurs fréquentes

- Commencer par coder sans issue : le besoin n’est pas tracé. Rédige l’issue d’abord, puis crée la branche.
- Oublier « Closes # » dans la pull request : l’issue reste ouverte. Ajoute la formule dans la description.
- Fusionner une pull request dont la CI est rouge : tu casses « main ». Corrige les tests d’abord.
- Pousser le workflow avec une erreur YAML : aucune exécution ne démarre. Valide l’indentation et regarde l’onglet Actions.
- Avoir un README vide : le projet n’est pas présentable. Prépare-le avec installation et usage.
- Travailler sur « main » directement : tu contournes toute la chaîne. Passe toujours par une branche.

## Bonnes pratiques

- Une issue, une branche, une pull request.
- Lance tes tests en local avant de pousser.
- Relis ta pull request comme si tu étais le reviewer.
- Garde les workflows et les permissions au strict nécessaire.
- Vérifie à la fin que tout est cohérent : issues fermées, branches supprimées, CI verte.

## Auto-évaluation

- Quelles sont les étapes du cycle entre une issue et sa livraison ?
- Comment la CI protège-t-elle la branche principale ?
- Comment une pull request ferme-t-elle une issue ?
- Quels éléments font un dépôt présentable ?
- Que vérifier à la fin du projet ?

## À retenir

- Le cycle issue, branche, pull request, CI, fusion est le socle du travail d’équipe.
- La CI et la protection de branche garantissent la stabilité de « main ».
- Les mots-clés relient le code au besoin.
- Un dépôt propre est une vitrine professionnelle.
- La qualité du processus compte autant que celle du code.
MD,
            'code_example' => <<<'CODE'
# Étape 1 : créer le dépôt avec ses fichiers de base
mkdir frais-livraison && cd frais-livraison
git init -b main
printf "# Frais de livraison\nCalcul des frais de livraison en FCFA.\n" > README.md
printf "node_modules/\n.env\n" > .gitignore
npm init -y
npm pkg set scripts.test="node --test"
npm install   # génère package-lock.json pour npm ci
git add . && git commit -m "chore: initialise le projet"
gh repo create frais-livraison --public --source=. --remote=origin --push

# Étape 2 : ajouter la CI sur main
mkdir -p .github/workflows
cat > .github/workflows/ci.yml <<'EOF'
name: CI
on:
  push:
    branches: [main]
  pull_request:
permissions:
  contents: read
jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 22
          cache: npm
      - run: npm ci
      - run: npm test
EOF
git add . && git commit -m "ci: ajoute le workflow de tests" && git push

# Étape 3 : une issue par fonctionnalité
gh issue create --title "Calculer les frais selon la distance" --body "Frais = 500 FCFA + 100 FCFA par km."
gh issue create --title "Livraison gratuite au-dessus de 20000 FCFA" --body "Frais à 0 si le panier dépasse 20000 FCFA."

# Étape 4 : première fonctionnalité sur sa branche, avec test, via pull request
git switch -c feature/frais-distance
cat > frais.js <<'EOF'
// Frais de base de 500 FCFA + 100 FCFA par kilomètre
export const frais = (km) => 500 + 100 * km;
EOF
cat > frais.test.js <<'EOF'
import test from "node:test";
import assert from "node:assert";
import { frais } from "./frais.js";
test("frais pour 3 km", () => assert.equal(frais(3), 800));
EOF
npm pkg set type=module
git add . && git commit -m "feat: calcule les frais selon la distance"
git push -u origin feature/frais-distance
gh pr create --title "feat: frais selon la distance" --body "Closes #1"

# Étape 5 : attendre la CI verte, fusionner, vérifier la fermeture de l'issue
gh pr checks --watch
gh pr merge --squash --delete-branch
gh issue list --state closed
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : workflow GitHub complet',
            'exercise_description' => <<<'MD'
Réalise le dépôt « calcul-remise » : une petite fonction JavaScript calculant une remise de 10 % au-dessus de 10000 FCFA, développée en suivant tout le workflow GitHub.

Livrables : dépôt public avec README, deux issues, deux pull requests fusionnées, workflow CI, règles de protection de « main » notées ou activées.

Critères de réussite :
- Le dépôt contient un README, un .gitignore, un package.json avec script « test » et un workflow « .github/workflows/ci.yml ».
- Deux issues sont ouvertes puis fermées automatiquement par deux pull requests contenant « Closes # ».
- Chaque pull request est développée sur une branche dédiée, avec au moins un test.
- L’onglet Actions affiche une exécution verte pour chaque pull request fusionnée.
- La protection de « main » exige une pull request et la réussite de la CI (ou les règles choisies sont documentées dans le README).
MD,
            'exercise_hint' => 'Utilise « node --test » pour éviter toute dépendance. N’oublie pas « npm install » pour générer package-lock.json avant d’utiliser « npm ci » en CI.',
            'exercise_solution' => <<<'CODE'
mkdir calcul-remise && cd calcul-remise
git init -b main
printf "# Calcul de remise\nRemise de 10 pourcent au-dessus de 10000 FCFA.\n" > README.md
printf "node_modules/\n.env\n" > .gitignore
npm init -y
npm pkg set type=module scripts.test="node --test"
npm install
mkdir -p .github/workflows
cat > .github/workflows/ci.yml <<'EOF'
name: CI
on:
  push:
    branches: [main]
  pull_request:
permissions:
  contents: read
jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 22
          cache: npm
      - run: npm ci
      - run: npm test
EOF
git add . && git commit -m "chore: initialise le projet et la CI"
gh repo create calcul-remise --public --source=. --remote=origin --push

gh issue create --title "Appliquer 10% de remise au-dessus de 10000 FCFA" --body "Remise de 10 pourcent."
gh issue create --title "Refuser les montants négatifs" --body "Lever une erreur si le montant est négatif."

# Pull request 1 : la remise
git switch -c feature/remise
cat > remise.js <<'EOF'
// Remise de 10 % au-dessus de 10000 FCFA
export const remise = (montant) => (montant > 10000 ? montant * 0.1 : 0);
EOF
cat > remise.test.js <<'EOF'
import test from "node:test";
import assert from "node:assert";
import { remise } from "./remise.js";
test("remise sur 20000", () => assert.equal(remise(20000), 2000));
test("pas de remise sur 5000", () => assert.equal(remise(5000), 0));
EOF
git add . && git commit -m "feat: calcule la remise de 10 pourcent"
git push -u origin feature/remise
gh pr create --title "feat: remise de 10 pourcent" --body "Closes #1"
gh pr checks --watch && gh pr merge --squash --delete-branch
git switch main && git pull

# Pull request 2 : validation
git switch -c feature/validation
cat > remise.js <<'EOF'
// Remise de 10 % au-dessus de 10000 FCFA, montant négatif refusé
export const remise = (montant) => {
  if (montant < 0) throw new Error("Montant négatif");
  return montant > 10000 ? montant * 0.1 : 0;
};
EOF
cat >> remise.test.js <<'EOF'
test("montant négatif refusé", () => assert.throws(() => remise(-1)));
EOF
git add . && git commit -m "feat: refuse les montants négatifs"
git push -u origin feature/validation
gh pr create --title "feat: validation du montant" --body "Closes #2"
gh pr checks --watch && gh pr merge --squash --delete-branch

# Protection de main : Settings > Branches > règle sur « main »
# - Require a pull request before merging
# - Require status checks to pass (job « tests »)
CODE,
        ],
    ],
];
