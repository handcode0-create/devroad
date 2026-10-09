---
title: Découvrir GitHub
minutes: 90
level: beginner
---

## Ce que tu vas apprendre

Tu connais Git, l'outil qui suit l'historique de ton code sur ta machine. GitHub est la plateforme qui héberge ces dépôts en ligne et qui ajoute tout ce dont une équipe a besoin : relecture de code, suivi des tâches, automatisation, sécurité. C'est aussi ta vitrine professionnelle : les recruteurs et les clients regardent ton profil GitHub.

À la fin du chapitre, tu seras capable de :

- expliquer la différence entre **Git** et **GitHub** ;
- créer un compte, le sécuriser et soigner ton profil ;
- connaître les grandes fonctionnalités de la plateforme ;
- créer ton premier dépôt et y envoyer du code ;
- choisir entre dépôt public et privé, et une licence adaptée ;
- t'authentifier avec une clé SSH ou un jeton d'accès ;
- repérer les alternatives (GitLab, Bitbucket) et savoir pourquoi GitHub domine.

Prérequis : les bases de Git (init, add, commit) du parcours précédent. Prévois une heure trente. Tu auras besoin d'une adresse e-mail et d'un navigateur.

## Git et GitHub : ne pas confondre

Une comparaison simple : Git est un **moteur**, GitHub est un **garage** équipé. Git fonctionne seul, sans internet. GitHub est un service en ligne qui :

- stocke une copie de ton dépôt sur un serveur (sauvegarde et partage) ;
- facilite la collaboration avec les **pull requests** et les **issues** ;
- automatise tests et déploiements avec **GitHub Actions** ;
- analyse ton code pour détecter des failles de sécurité ;
- héberge de la documentation et même des sites web (**GitHub Pages**).

```text
Ton ordinateur                          GitHub (serveur)
┌──────────────────┐   git push   ┌──────────────────────────┐
│ Dépôt Git local  │ ───────────▶ │ Dépôt distant (origin)   │
│                  │ ◀─────────── │ + Issues, PR, Actions…   │
└──────────────────┘   git pull   └──────────────────────────┘
```

Il existe des concurrents qui reposent sur le même Git : **GitLab** (très orienté DevOps, installable sur ses propres serveurs), **Bitbucket** (intégré à l'écosystème Atlassian), **Gitea** ou **Forgejo** (léger, auto-hébergé). Les concepts de ce parcours se transposent presque tous à ces plateformes avec des noms différents (par exemple, *merge request* sur GitLab).

:::quiz
Quelle affirmation est correcte ?
- [ ] GitHub est l'outil installé sur ta machine pour suivre l'historique du code
- [ ] On ne peut pas utiliser Git sans GitHub
- [x] Git fonctionne en local ; GitHub est un service en ligne qui héberge des dépôts Git et ajoute des outils de collaboration
- [ ] GitHub et Git sont deux noms pour le même logiciel
> Git est le système de gestion de versions. GitHub est une plateforme construite autour de Git. On peut utiliser Git sans GitHub, mais pas l'inverse.
:::

## Créer et sécuriser ton compte

1. Rends-toi sur github.com et choisis « Sign up ».
2. Choisis un **nom d'utilisateur** durable et professionnel. Il apparaît dans toutes tes URL (`github.com/ton-nom`). Évite les surnoms puérils si tu cherches un emploi ou des clients.
3. Confirme ton adresse e-mail.

La sécurité compte dès le premier jour. Un compte GitHub piraté, c'est tout ton code (et parfois des clés d'API) exposé :

- utilise un **mot de passe long et unique**, idéalement géré par un gestionnaire de mots de passe ;
- active l'**authentification à deux facteurs (2FA)** dans *Settings → Password and authentication*. Une application d'authentification est préférable au SMS, et une clé de sécurité matérielle ou une *passkey* est meilleure encore ;
- conserve les **codes de récupération** dans un endroit sûr, hors de ton ordinateur.

> **Attention** : GitHub impose désormais la 2FA aux contributeurs de code. Active-la tout de suite et teste les codes de récupération, avant de perdre l'accès à ton téléphone.

### Soigner son profil

Ton profil est visible par tous. Dans *Settings → Public profile*, renseigne :

- une photo et ton vrai nom (ou un nom de marque cohérent) ;
- une courte **bio** : ce que tu fais, avec quelles technologies ;
- ta localisation et un lien vers ton site ;
- ton adresse e-mail publique, ou rien du tout.

Tu peux aussi créer un **README de profil** : un dépôt public portant exactement ton nom d'utilisateur et contenant un `README.md`. Son contenu s'affiche en haut de ta page de profil.

## Faire le tour de l'interface

Une fois connecté, voici les zones à connaître :

| Zone | Rôle |
| --- | --- |
| **Dashboard** (page d'accueil) | Activité de tes dépôts, de ceux que tu suis |
| **Repositories** | Liste de tes dépôts |
| **Pull requests / Issues** | Ce qui t'attend, ce que tu as ouvert ou qui te mentionne |
| **Explore** | Découverte de projets et de tendances |
| **Settings** (de ton compte) | Sécurité, clés, notifications, profil |

Dans un dépôt, une barre d'onglets donne accès aux outils principaux :

- **Code** : fichiers, branches, historique ;
- **Issues** : suivi des bugs et des tâches ;
- **Pull requests** : propositions de modifications à relire ;
- **Actions** : automatisations (tests, déploiement) ;
- **Projects** : tableaux de suivi ;
- **Security** : alertes de vulnérabilités ;
- **Insights** : statistiques, contributeurs ;
- **Settings** : réglages du dépôt.

> **Astuce** : appuie sur la touche `t` dans l'onglet Code pour rechercher un fichier par son nom, et sur `.` (point) pour ouvrir un éditeur VS Code dans le navigateur.

## Créer ton premier dépôt

Clique sur le bouton **New repository**. Le formulaire demande :

- **Owner** : ton compte ou une organisation ;
- **Repository name** : en minuscules avec des tirets, sans espaces (`mon-portfolio`) ;
- **Description** : une phrase qui dit à quoi sert le projet ;
- **Public ou Private** : voir ci-dessous ;
- **Add a README** : coche cette case si tu pars de zéro ;
- **.gitignore template** : choisis la techno (Node, Laravel…) ;
- **License** : voir ci-dessous.

### Public ou privé ?

- Un dépôt **public** est lisible par n'importe qui. Idéal pour l'open source et le portfolio.
- Un dépôt **privé** n'est visible que par les personnes que tu invites. Indispensable pour le code client ou commercial.

Règle simple : **rien de confidentiel dans un dépôt public** (clés d'API, mots de passe, données de clients). Tu peux basculer la visibilité plus tard dans les paramètres, mais tout ce qui a été publié a pu être copié.

### Les licences

Sans licence, ton code public est légalement **tous droits réservés** : personne n'a le droit de le réutiliser. Pour autoriser la réutilisation, choisis une licence :

| Licence | Esprit |
| --- | --- |
| **MIT** | Très permissive : on peut tout faire, en gardant la mention de l'auteur |
| **Apache 2.0** | Permissive, avec une protection sur les brevets |
| **GPL v3** | Les travaux dérivés doivent rester libres sous la même licence |
| Aucune | Code propriétaire : à choisir pour du code client |

Pour un petit projet personnel que tu veux partager, MIT est un choix courant.

## Envoyer un projet local sur GitHub

Deux situations se présentent.

**Cas 1 : tu pars de GitHub.** Clone le dépôt vide ou avec README :

```bash
git clone git@github.com:ton-nom/mon-portfolio.git
cd mon-portfolio
```

**Cas 2 : tu as déjà un projet local.** Crée un dépôt **vide** sur GitHub (sans README), puis relie-le :

```bash
cd mon-projet
git init
git add .
git commit -m "chore: initialise le projet"
git branch -M main
git remote add origin git@github.com:ton-nom/mon-projet.git
git push -u origin main
```

Actualise la page du dépôt : tes fichiers sont en ligne. Chaque `git push` ultérieur mettra à jour GitHub.

GitHub affiche automatiquement le fichier `README.md` sur la page d'accueil du dépôt. C'est la vitrine de ton projet : un bon README contient le nom, une description, une capture d'écran, la procédure d'installation et le mode d'emploi.

## S'authentifier : SSH ou jeton

Pour pousser du code, GitHub doit savoir qui tu es. Deux méthodes recommandées :

**1. Clé SSH.** Tu génères une paire de clés sur ta machine et tu déposes la clé **publique** sur GitHub (*Settings → SSH and GPG keys → New SSH key*).

```bash
ssh-keygen -t ed25519 -C "ton-email@example.com"
cat ~/.ssh/id_ed25519.pub
ssh -T git@github.com
```

Si le test affiche « Hi ton-nom! You've successfully authenticated », tout est prêt.

**2. HTTPS avec jeton d'accès personnel.** GitHub n'accepte plus le mot de passe en HTTPS. Tu crées un **Personal Access Token** (*Settings → Developer settings*) avec les droits minimaux nécessaires, et tu le saisis à la place du mot de passe, idéalement via un gestionnaire d'identifiants (`gh auth login` configure cela pour toi).

> **Astuce** : installe la CLI officielle `gh` (GitHub CLI). La commande `gh auth login` gère l'authentification, et `gh repo create` crée un dépôt directement depuis le terminal.

```bash
gh auth login
gh repo create mon-projet --private --source=. --push
```

:::quiz
Pourquoi faut-il être prudent avec le contenu d'un dépôt public ?
- [ ] Parce que GitHub supprime les dépôts trop gros
- [x] Parce que n'importe qui peut le lire et le copier : aucun secret (clé d'API, mot de passe) ne doit y figurer
- [ ] Parce qu'un dépôt public ne peut pas être modifié
- [ ] Parce que Git refuse de pousser des fichiers de configuration
> Tout ce qui est dans un dépôt public est visible de tous, y compris l'historique. Un secret publié doit être considéré comme compromis et être remplacé.
:::

## Étoiles, forks et suivi

Quelques notions sociales à connaître :

- **Star** (étoile) : un signet pour retrouver un projet et un signe d'appréciation ;
- **Watch** : recevoir des notifications sur l'activité d'un dépôt ;
- **Fork** : copier le dépôt d'un autre dans ton compte pour proposer des modifications (vu au chapitre suivant) ;
- **Follow** : suivre l'activité d'une personne.

Tu peux aussi épingler six dépôts sur ton profil : choisis ceux qui montrent le mieux ton travail, avec un README soigné.

:::quiz
Quel est le meilleur choix de visibilité pour le code source d'un projet client confidentiel ?
- [ ] Public, avec une licence MIT
- [x] Privé, avec accès limité aux personnes invitées
- [ ] Public, mais sans README
- [ ] Public, en supprimant ensuite les fichiers sensibles
> Le code d'un client ne doit pas être exposé. Un dépôt privé limite l'accès aux collaborateurs autorisés.
:::

## Atelier guidé : ta présence sur GitHub

Compte une heure.

1. Crée ton compte (ou ouvre celui que tu as déjà), choisis un nom d'utilisateur professionnel.
2. Active la 2FA avec une application d'authentification et enregistre les codes de récupération.
3. Complète ton profil : photo, bio d'une phrase, lien vers ton site, localisation.
4. Génère une clé SSH `ed25519`, ajoute la clé publique à GitHub et valide avec `ssh -T git@github.com`.
5. Crée un dépôt public `mon-premier-depot` avec un README, une licence MIT et un `.gitignore` adapté. Explore chaque onglet.
6. Clone-le en local, modifie le README (ajoute ton nom et une présentation), puis fais `git add`, `git commit`, `git push`.
7. Recharge la page du dépôt et retrouve ton commit dans *Commits*.
8. Crée un second dépôt **privé** depuis un projet local existant, en suivant le cas 2.
9. Crée le dépôt spécial `ton-nom/ton-nom` avec un README de profil et vérifie son affichage.
10. Épingle le dépôt de ton choix sur ton profil.

Pour t'auto-évaluer : explique en deux phrases ce que GitHub apporte en plus de Git, puis dis pourquoi ta clé privée ne doit jamais quitter ta machine.

## Erreurs fréquentes

- **Penser que GitHub est indispensable à Git.** Git reste un outil local ; GitHub est un service parmi d'autres.
- **Créer le dépôt avec un README, puis pousser un projet local existant.** Les historiques divergent et Git refuse. Crée le dépôt vide dans ce cas.
- **Publier un secret.** Les robots scannent GitHub en permanence : une clé publiée est souvent exploitée en quelques minutes.
- **Oublier d'activer la 2FA.** Un simple vol de mot de passe suffirait à prendre le contrôle du compte.
- **Choisir un pseudonyme embarrassant.** Il est difficile à changer sans casser les liens existants.
- **Publier sans licence tout en espérant que les autres réutilisent le code.** Légalement, ils ne le peuvent pas.

## Bonnes pratiques

- Active la 2FA et utilise un gestionnaire de mots de passe.
- Écris un README clair pour chaque dépôt : objectif, installation, utilisation.
- Choisis une licence pour tout code que tu veux partager.
- Mets le code client dans des dépôts privés.
- Nomme tes dépôts en minuscules, avec des tirets, et un nom explicite.
- Préfère SSH ou `gh auth login` aux mots de passe en clair.
- Soigne ton profil : c'est souvent la première impression qu'un client ou recruteur aura de toi.

## À retenir

- **Git** est l'outil local ; **GitHub** est la plateforme qui héberge les dépôts et ajoute la collaboration, l'automatisation et la sécurité.
- Active la **2FA** dès la création du compte et protège ta clé privée.
- Un dépôt est **public** (visible par tous) ou **privé** (accès sur invitation) ; aucun secret dans le public.
- Sans **licence**, un code public n'est pas réutilisable légalement ; MIT est un choix simple.
- Pour publier un projet local : dépôt vide sur GitHub, `git remote add origin`, `git push -u origin main`.
- La CLI `gh` simplifie l'authentification et la création de dépôts.
