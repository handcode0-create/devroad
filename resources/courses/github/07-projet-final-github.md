---
title: Projet final GitHub
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Dans ce projet, tu vas monter de A à Z l'**infrastructure collaborative complète** d'un dépôt professionnel sur GitHub : organisation, modèles, suivi des tâches, relecture, intégration continue, sécurité et publication de versions. À l'issue du parcours, tu auras un dépôt modèle que tu pourras dupliquer pour chacun de tes projets clients, à la manière d'une agence qui industrialise sa façon de travailler.

À la fin du projet, tu seras capable de :

- structurer un dépôt avec tous les fichiers de cadrage attendus par une équipe ;
- planifier un projet avec des issues, des milestones et un tableau GitHub Projects ;
- appliquer un cycle complet issue, branche, pull request, revue, fusion ;
- mettre en place une intégration continue obligatoire avec GitHub Actions ;
- durcir le dépôt : protection de branche, Dependabot, secret scanning, CodeQL ;
- publier des versions SemVer avec des releases ;
- documenter tes choix pour qu'un nouveau venu soit opérationnel en dix minutes.

Prérequis : le parcours Git complet et les six chapitres GitHub précédents. Durée : **six heures**, répartissables en deux sessions. Il te faut un compte GitHub avec 2FA, une clé SSH, Git, Node.js (version 20 ou plus) et idéalement un camarade pour relire tes PR. À défaut, tu peux utiliser un second compte, ou relire toi-même en simulant le rôle de relecteur.

## Le contexte du projet

Tu fondes **hancode-starter**, un dépôt « gabarit » destiné à une agence web. Il contient une petite application front-end (une page de présentation de projets en HTML, CSS et JavaScript, avec quelques tests) qui sert uniquement de support. L'important n'est pas l'application, mais **tout ce qui l'entoure**.

Pour avoir quelque chose à tester, initialise un projet Node minimal :

```bash
mkdir hancode-starter && cd hancode-starter
git init -b main
npm init -y
npm install --save-dev vitest
```

Dans `package.json`, définis les scripts `test` (`vitest run`), `build` (copie des fichiers vers `dist/`) et `lint` (au choix : ESLint ou une vérification simple). Écris deux fonctions utilitaires dans `src/` (par exemple `formaterPrix` qui affiche un montant en FCFA, et `filtrerProjets`) avec leurs tests dans `tests/`. Vérifie que `npm test` passe en local avant de continuer.

```text
hancode-starter/
├── .github/
│   ├── CODEOWNERS
│   ├── dependabot.yml
│   ├── pull_request_template.md
│   ├── ISSUE_TEMPLATE/
│   │   ├── bug.yml
│   │   ├── fonctionnalite.yml
│   │   └── config.yml
│   └── workflows/
│       ├── ci.yml
│       ├── codeql.yml
│       └── release.yml
├── src/
├── tests/
├── .gitignore
├── CONTRIBUTING.md
├── LICENSE
├── README.md
├── SECURITY.md
└── package.json
```

## Cahier des charges

### Exigences fonctionnelles (le dépôt)

1. Un dépôt **public** nommé `hancode-starter`, avec licence MIT.
2. Un README complet : présentation, stack, installation, scripts, workflow de contribution, badge d'état du CI.
3. Des fichiers de gouvernance : `CONTRIBUTING.md`, `SECURITY.md`, `CODEOWNERS`.
4. Des modèles d'issue (bug et fonctionnalité) et un modèle de pull request.
5. Un jeu de **labels** cohérent (type, priorité, zone) et deux **milestones** (`v0.1.0`, `v0.2.0`).
6. Un **tableau GitHub Projects** (Backlog, À faire, En cours, En revue, Terminé) automatisé.
7. Un workflow **CI** (lint, tests, build) obligatoire avant fusion.
8. Un workflow **CodeQL** et la configuration **Dependabot**.
9. Un workflow de **release** qui crée une release GitHub quand un tag `vX.Y.Z` est poussé.
10. Au moins **huit issues** traitées via PR, deux versions publiées.

### Contraintes

- Aucun commit direct sur `main` après le commit initial : tout passe par une PR relue.
- Historique de `main` linéaire : fusion en *squash*.
- Titres de PR au format Conventional Commits.
- Aucun secret dans le dépôt ; les valeurs sensibles éventuelles passent par les secrets Actions.
- Chaque PR est liée à une issue (`Closes #n`).

> **À retenir** : le projet est réussi si un inconnu peut cloner le dépôt, comprendre comment contribuer, proposer un correctif et voir son travail validé automatiquement, sans te poser une seule question.

:::quiz
Parmi ces éléments, lequel garantit le mieux qu'une PR ne casse pas la branche principale ?
- [ ] Un message de commit bien écrit
- [ ] Un fichier LICENSE
- [x] Un workflow CI dont les checks sont rendus obligatoires par la protection de branche
- [ ] Un README détaillé
> Seuls des checks automatiques obligatoires bloquent réellement la fusion d'un code qui échoue aux tests. Les autres éléments aident, mais ne sont pas contraignants.
:::

## Phase 1 : créer et cadrer le dépôt (50 min)

1. Sur GitHub, crée le dépôt public `hancode-starter` **vide** (sans README, ni licence, pour pousser ton projet local sans conflit d'historique).
2. Ajoute `.gitignore` (`node_modules/`, `dist/`, `.env`, `.DS_Store`) et `LICENSE` (texte MIT avec ton nom et l'année).
3. Fais un commit initial `chore: initialise le projet`, puis :

```bash
git remote add origin git@github.com:TON-COMPTE/hancode-starter.git
git add .
git commit -m "chore: initialise le projet"
git push -u origin main
```

4. Rédige `README.md`. Il doit contenir au minimum : titre, description en une phrase, stack, installation (commandes), scripts disponibles, structure du dépôt, section « Contribuer » renvoyant à `CONTRIBUTING.md`, licence.
5. Rédige `CONTRIBUTING.md` : conventions de branches (`feature/`, `fix/`, `docs/`, `chore/`), Conventional Commits, cycle issue → branche → PR → revue → squash, règles de relecture (délai de réponse, ton), comment lancer les tests en local.
6. Rédige `SECURITY.md` avec la procédure de signalement privé et la politique de versions supportées.
7. Crée un `CODEOWNERS` : toi sur tout le dépôt, et sur `/.github/` en particulier.

Tant que `main` n'est pas protégée (elle le sera en phase 3), tu peux pousser ces fichiers de cadrage directement dans un second commit `docs: ajoute les fichiers de gouvernance`. C'est la seule exception à la règle « tout passe par une PR ».

## Phase 2 : organiser le travail (45 min)

1. Crée les **labels** : `type: bug`, `type: feature`, `type: chore`, `priority: high`, `priority: medium`, `priority: low`, `area: ci`, `area: docs`, `area: app`. Supprime les labels par défaut inutiles.
2. Crée les **modèles d'issue** en formulaire YAML (`bug.yml`, `fonctionnalite.yml`) et `config.yml` désactivant les issues vides.
3. Crée `.github/pull_request_template.md` (Contexte, Changements, Comment tester, Checklist : tests ajoutés, documentation mise à jour, `Closes #`).
4. Crée les **milestones** `v0.1.0` (échéance à une semaine) et `v0.2.0`.
5. Crée le **projet** « hancode-starter : feuille de route » en vue *Board*, avec les colonnes demandées et un champ *Priority*. Active les workflows automatiques (ajout, fermeture, fusion).
6. Crée au moins **huit issues** couvrant le backlog (liste ci-dessous).
7. Affecte labels, milestones et priorités, et ajoute-les au tableau.

Issues du backlog à créer :

- `ci : lint, tests et build sur chaque PR` ;
- `ci : analyse CodeQL` ;
- `securite : configurer Dependabot` ;
- `docs : guide de contribution` ;
- `feature : fonction formaterPrix` ;
- `feature : fonction filtrerProjets` ;
- `chore : workflow de release` ;
- `bug : formaterPrix affiche mal les montants de plus d'un million`.

Commite les modèles et fichiers de cette phase directement sur `main`, avant d'activer la protection : c'est la dernière fois que tu pousses sans passer par une PR.

## Phase 3 : la CI et la protection de branche (60 min)

Crée une branche `chore/ci` liée à l'issue correspondante (`gh issue develop <n> --checkout`) et écris `.github/workflows/ci.yml` :

```yaml
name: CI

on:
  pull_request:
  push:
    branches: [main]

permissions:
  contents: read

jobs:
  qualite:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: npm
      - run: npm ci
      - run: npm run lint
      - run: npm test
      - run: npm run build
```

Étapes :

1. Ouvre la PR avec `Closes #n`, observe l'exécution dans l'onglet *Actions*, puis fusionne en *squash* une fois verte.
2. Casse volontairement un test sur une branche jetable, constate le check rouge, corrige. Ce scénario te servira de démonstration dans ton README.
3. Ajoute le **badge** du CI en haut du README :

```text
![CI](https://github.com/TON-COMPTE/hancode-starter/actions/workflows/ci.yml/badge.svg)
```

4. Active la **protection de `main`** (ruleset) : PR obligatoire avec au moins une approbation (si tu as un relecteur), check `qualite` obligatoire et branche à jour avant fusion, résolution des conversations obligatoire, historique linéaire exigé, force push et suppression bloqués, revue des *code owners* sur le dossier `.github`.
5. Dans *Settings → General*, autorise uniquement **Squash merge** et active la suppression automatique des branches fusionnées.
6. Teste la protection : tente un `git push origin main` direct depuis le terminal et lis le refus.

:::quiz
Pourquoi autoriser uniquement le mode de fusion « Squash and merge » sur le dépôt ?
- [ ] Parce que les autres modes sont dangereux pour le serveur
- [x] Pour garder un historique de `main` linéaire, avec un commit par PR correspondant au titre de la PR
- [ ] Pour empêcher les relectures
- [ ] Parce que GitHub l'impose
> Le squash regroupe les commits de brouillon en un seul commit propre. Combiné à un titre de PR soigné, il donne un historique lisible.
:::

## Phase 4 : développer par pull requests (75 min)

Traite maintenant les issues de fonctionnalités et de bug, **une par une**, selon le cycle imposé :

1. Déplace la carte en « En cours » et assigne-toi l'issue.
2. Crée la branche à partir de l'issue.
3. Développe en commits Conventional Commits, avec les tests.
4. Pousse et ouvre la PR avec le modèle rempli et `Closes #n`.
5. Demande une relecture. **Relecteur** : lis la description, commente au moins deux lignes dans *Files changed*, propose une suggestion, puis demande des changements ou approuve. **Auteur** : réponds à chaque commentaire, pousse les corrections, résous les conversations.
6. Une fois les checks verts et la PR approuvée, fusionne en squash, vérifie que l'issue est fermée et que la carte est passée en « Terminé ».

À faire dans cette phase :

- les fonctions `formaterPrix` et `filtrerProjets`, chacune avec ses tests ;
- la correction du bug des montants supérieurs à un million (écris d'abord un test qui échoue, puis la correction) ;
- un conflit volontaire : modifie `README.md` dans deux PR ouvertes en parallèle, fusionne la première, puis résous le conflit de la seconde en local par un rebase et un `git push --force-with-lease`.

Quand le milestone `v0.1.0` est complété, fais une PR « docs: met à jour le README » qui ajoute la liste des fonctionnalités livrées.

## Phase 5 : sécurité et automatisation (55 min)

1. **Dependabot** : crée `.github/dependabot.yml` pour `npm` et `github-actions` (fréquence hebdomadaire). Passe par une PR. Dans les paramètres, active les alertes et les mises à jour de sécurité.
2. **Secret scanning et push protection** : active-les dans *Settings → Code security*. Teste en tentant de pousser un faux jeton de test (fourni par la documentation d'un fournisseur) sur une branche jetable et vérifie le blocage.
3. **CodeQL** : ajoute `.github/workflows/codeql.yml` (voir le chapitre précédent) pour `javascript-typescript`, puis rends le check obligatoire une fois qu'il a tourné au moins une fois.
4. **Release automatique** : crée `.github/workflows/release.yml` qui se déclenche sur les tags `v` suivis de chiffres et crée une release avec les notes générées :

```yaml
name: Release

on:
  push:
    tags: ["v*.*.*"]

permissions:
  contents: write

jobs:
  release:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: npm
      - run: npm ci
      - run: npm test
      - run: npm run build
      - name: Créer la release
        run: gh release create "$GITHUB_REF_NAME" --generate-notes
        env:
          GH_TOKEN: ${{ secrets.GITHUB_TOKEN }}
```

5. Vérifie la section *Security* du dépôt : aucune alerte critique ouverte.
6. Applique le **moindre privilège** : relis les `permissions` de tes trois workflows, ainsi que tes jetons personnels et clés SSH dans les paramètres du compte.

## Phase 6 : livrer et auditer (55 min)

1. Vérifie que tous les éléments du milestone `v0.1.0` sont fermés, puis publie la version :

```bash
git switch main
git pull --rebase
git tag -a v0.1.0 -m "Première version du gabarit"
git push origin v0.1.0
```

2. Observe le workflow de release : une release `v0.1.0` doit apparaître avec ses notes.
3. Prépare `v0.2.0` avec une dernière fonctionnalité (par exemple un script `npm run format`), suivie du même cycle, puis publie `v0.2.0`.
4. Fais l'**audit final** comme le ferait un relecteur extérieur : ouvre le dépôt en navigation privée (le README est-il clair, le badge vert ?), clone-le dans un dossier vierge et suis ton propre README (l'installation fonctionne-t-elle du premier coup ?), lis `git log --oneline --graph` (l'historique est-il linéaire ?), tente un push direct sur `main` (est-il refusé ?), puis ouvre l'onglet *Security* (alertes, secret scanning et code scanning sont-ils actifs ?).
5. Transforme ton dépôt en **gabarit** : *Settings → General → Template repository*. Tu pourras alors créer un nouveau projet avec « Use this template », qui conserve tous les fichiers de gouvernance.

## Atelier guidé : plan de route

Voici l'enchaînement à suivre, avec un repère de temps.

1. Phase 1 : dépôt, README, fichiers de gouvernance (50 min).
2. Phase 2 : labels, modèles, milestones, tableau, backlog (45 min).
3. Phase 3 : CI, badge, protection de `main`, squash (60 min).
4. Phase 4 : fonctionnalités et bug via PR relues, un conflit résolu (75 min).
5. Phase 5 : Dependabot, secret scanning, CodeQL, release automatique (55 min).
6. Phase 6 : publication de `v0.1.0` et `v0.2.0`, audit, gabarit (55 min).
7. Marge pour les blocages : environ 20 minutes.

Si une étape échoue, lis d'abord les logs de l'onglet *Actions* et le message d'erreur de Git. Ne contourne jamais une protection en la désactivant : cherche pourquoi elle bloque.

Pour t'auto-évaluer, reprends la checklist ci-dessous et note chaque ligne : acquis, partiel, à revoir.

## Checklist d'acceptation

**Dépôt et documentation**

- Le dépôt est public, sous licence MIT, avec un README complet et un badge CI.
- `CONTRIBUTING.md`, `SECURITY.md` et `CODEOWNERS` existent et sont à jour.
- Le dépôt est marqué comme gabarit (*template repository*).

**Organisation du travail**

- Les labels, les deux milestones et le tableau GitHub Projects existent et sont automatisés.
- Les modèles d'issue (formulaires YAML) et le modèle de PR sont actifs.
- Au moins huit issues ont été créées, assignées, et fermées par des PR.

**Collaboration**

- Chaque PR a un titre Conventional Commits, un `Closes #n` et au moins une relecture avec commentaires.
- Au moins une suggestion a été appliquée et un conflit a été résolu.
- Aucune fusion n'a eu lieu avec un check rouge.

**Intégration continue et livraison**

- Le workflow CI (lint, tests, build) tourne sur chaque PR et chaque push sur `main`.
- Les checks `qualite` et CodeQL sont obligatoires avant fusion.
- Les releases `v0.1.0` et `v0.2.0` ont été créées automatiquement par un workflow, avec des notes.

**Sécurité**

- `main` est protégée : PR obligatoire, force push et suppression bloqués, historique linéaire.
- Dependabot, secret scanning, push protection et code scanning sont activés.
- Aucun secret n'apparaît dans le dépôt ni dans les logs ; les workflows déclarent des `permissions` minimales.

**Historique**

- Le journal de `main` est linéaire, avec un commit par PR.
- Aucun commit direct sur `main` après la phase de gouvernance initiale.

## Erreurs fréquentes

- **Protéger `main` trop tôt ou trop tard.** Trop tôt, tu te bloques toi-même ; trop tard, les premiers commits échappent à la relecture. Respecte l'ordre des phases.
- **Créer le dépôt avec un README et pousser un projet local.** Les historiques divergent. Crée le dépôt vide.
- **Nom du check mal rédigé.** Le check obligatoire doit porter le nom exact du job, et le workflow doit avoir tourné une fois pour apparaître dans la liste.
- **PR sans lien avec une issue.** Le tableau ne se met pas à jour, la traçabilité est perdue.
- **Workflow de release déclenché sur un tag mal formé.** Un tag `0.1.0` sans le `v` n'est pas pris en compte par le motif.
- **Droits insuffisants du jeton.** Sans `contents: write`, la création de release échoue avec une erreur 403.
- **Oublier de relancer les tests après résolution d'un conflit.** La PR a l'air verte mais le comportement a changé.
- **Tout faire en une seule grosse PR.** La relecture devient superficielle et les erreurs passent.

## Bonnes pratiques

- Commence par la gouvernance (documents, modèles, labels) avant d'écrire du code : c'est ce qui rend le reste fluide.
- Une issue, une branche, une PR, un commit squashé sur `main`.
- Rends tous les contrôles automatiques **obligatoires**, jamais facultatifs.
- Documente les décisions dans la PR et dans le README plutôt que dans ta tête.
- Applique le moindre privilège partout : rôles, jetons, `permissions` des workflows.
- Teste toi-même chaque protection (push direct, secret factice, test cassé) pour vérifier qu'elle fonctionne vraiment.
- Mets à jour ce gabarit à chaque amélioration de ta manière de travailler, puis réutilise-le pour tous tes projets.
- Relis ton dépôt avec les yeux d'un nouveau venu avant de le déclarer terminé.

## À retenir

- Un dépôt professionnel est un **système** : documentation, modèles, suivi, CI, sécurité et releases fonctionnent ensemble.
- Le cycle complet : **issue**, branche, PR relue, checks verts, **squash**, carte « Terminé », release.
- La **protection de branche** et les **checks obligatoires** transforment les bonnes pratiques en règles appliquées.
- **Dependabot**, le **secret scanning**, la **push protection** et **CodeQL** couvrent dépendances, secrets et code.
- Les **releases automatisées** sur tags SemVer rendent les livraisons reproductibles.
- Un **dépôt gabarit** te permet de démarrer chaque nouveau projet avec ce socle déjà en place.
- La qualité se mesure à ce qu'un inconnu peut faire seul : cloner, contribuer, être validé.
