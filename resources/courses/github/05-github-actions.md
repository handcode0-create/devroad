---
title: GitHub Actions
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Imagine qu'à chaque `git push`, un robot lance les tests, vérifie le style du code, compile le front-end, et te prévient si quelque chose est cassé. Tu n'as plus à penser à « lancer les tests avant de fusionner » : c'est automatique, pour toute l'équipe. C'est la promesse de **GitHub Actions**, le moteur d'automatisation intégré à GitHub, et c'est la base de l'**intégration continue** (CI) et du **déploiement continu** (CD).

À la fin du chapitre, tu seras capable de :

- expliquer les notions de **workflow**, **événement**, **job**, **step** et **runner** ;
- écrire un workflow YAML qui lance les tests d'un projet Laravel et React ;
- utiliser des actions existantes (`checkout`, `setup-node`, `setup-php`) ;
- accélérer les exécutions avec le **cache** et les **matrices** ;
- manipuler les **secrets** et variables d'environnement de façon sûre ;
- rendre un check **obligatoire** avant fusion ;
- lire les logs, diagnostiquer un échec et déclencher un déploiement.

Prérequis : les chapitres sur les pull requests et la protection de branche, et des bases de YAML (indentation par deux espaces, listes avec des tirets). Prévois deux heures trente.

## Le vocabulaire

Un workflow GitHub Actions est un fichier YAML placé dans `.github/workflows/`. Voici les cinq notions à connaître.

| Terme | Définition |
| --- | --- |
| **Workflow** | Un fichier YAML décrivant un processus automatisé |
| **Événement** (*event*) | Ce qui déclenche le workflow : un push, une PR, un horaire… |
| **Job** | Un groupe d'étapes qui s'exécute sur une même machine |
| **Step** | Une étape : une commande shell ou une action réutilisable |
| **Runner** | La machine virtuelle qui exécute le job (Ubuntu, Windows, macOS) |

```text
Événement (push)
      │
      ▼
 Workflow ── Job "tests" (runner ubuntu) ── step 1: checkout
           │                              ├─ step 2: installer PHP
           │                              └─ step 3: lancer les tests
           └─ Job "build" (en parallèle) ── step 1 …
```

Par défaut, les jobs d'un workflow s'exécutent **en parallèle**, chacun sur sa propre machine toute neuve. Pour les enchaîner, on utilise le mot-clé `needs`.

Pour les dépôts publics, les minutes d'exécution sont gratuites. Pour les dépôts privés, un quota mensuel gratuit existe, au-delà duquel c'est facturé : garde un œil sur la consommation.

:::quiz
Que se passe-t-il par défaut quand un workflow contient deux jobs sans lien entre eux ?
- [ ] Ils s'exécutent l'un après l'autre dans l'ordre du fichier
- [x] Ils s'exécutent en parallèle, chacun sur son propre runner
- [ ] Seul le premier est exécuté
- [ ] Ils partagent le même système de fichiers
> Chaque job démarre sur une machine propre et indépendante. Pour imposer un ordre, on utilise `needs`.
:::

## Ton premier workflow

Crée le fichier `.github/workflows/ci.yml` :

```yaml
name: CI

on:
  push:
    branches: [main]
  pull_request:
    branches: [main]

jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - name: Récupérer le code
        uses: actions/checkout@v4

      - name: Dire bonjour
        run: echo "Bonjour depuis le runner, commit ${{ github.sha }}"
```

Décortiquons :

- `name` : le nom affiché dans l'onglet *Actions* ;
- `on` : les événements déclencheurs. Ici, un push sur `main` et toute PR vers `main` ;
- `jobs` : les groupes d'étapes ; `tests` est un identifiant que tu choisis ;
- `runs-on` : la machine (`ubuntu-latest` est le choix le plus courant) ;
- `steps` : la liste ordonnée ; chaque étape utilise soit `uses` (une action existante) soit `run` (une commande) ;
- `${{ ... }}` : une expression qui lit le **contexte** (ici l'identifiant du commit).

Commite et pousse le fichier. Ouvre l'onglet **Actions** du dépôt : tu y vois ton workflow s'exécuter, avec un voyant orange, puis vert ou rouge. Clique sur un job pour lire les logs ligne par ligne.

> **Attention** : l'indentation YAML est significative. Utilise des **espaces** (jamais de tabulations) et deux espaces par niveau. La plupart des erreurs de débutants viennent d'une indentation incorrecte.

## Les événements déclencheurs

La section `on` est très flexible :

```yaml
on:
  push:
    branches: [main]
    paths: ["app/**", "resources/**", "composer.lock"]
  pull_request:
  schedule:
    - cron: "0 3 * * 1"      # tous les lundis à 3 h (UTC)
  workflow_dispatch:           # bouton "Run workflow" manuel
  release:
    types: [published]
```

- `paths` limite l'exécution aux fichiers concernés : inutile de relancer les tests PHP quand tu ne modifies que le README ;
- `schedule` lance le workflow à heure fixe, avec la syntaxe cron ;
- `workflow_dispatch` ajoute un bouton pour le lancer à la main, très pratique pour les déploiements ;
- `release` réagit à la publication d'une version.

## Un vrai pipeline pour Laravel et React

Voici un workflow d'intégration continue complet pour un projet comme DevRoad : tests PHP d'un côté, build du front-end de l'autre.

```yaml
name: CI

on:
  pull_request:
  push:
    branches: [main]

jobs:
  php:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: shivammathur/setup-php@v2
        with:
          php-version: "8.3"
          extensions: mbstring, pdo_sqlite
          coverage: none

      - name: Cache Composer
        uses: actions/cache@v4
        with:
          path: vendor
          key: composer-${{ hashFiles('composer.lock') }}

      - run: composer install --no-interaction --prefer-dist
      - run: cp .env.example .env
      - run: php artisan key:generate
      - run: ./vendor/bin/pint --test
      - run: php artisan test

  front:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: npm

      - run: npm ci
      - run: npm run build
```

Points à retenir :

- `actions/checkout@v4` récupère le code : **sans lui, le runner n'a aucun fichier** ;
- `setup-php` et `setup-node` installent les langages dans la version voulue ;
- `npm ci` (au lieu de `npm install`) installe exactement les versions du lockfile, de façon reproductible ;
- l'option `cache: npm` de `setup-node` met en cache les dépendances ;
- le cache de Composer utilise une **clé** calculée à partir du hash de `composer.lock` : tant que le lockfile ne change pas, le cache est réutilisé, sinon il est recréé ;
- `pint --test` vérifie le style sans le modifier : il échoue si le code n'est pas conforme.

Épingle les versions des actions (`@v4`) pour éviter qu'une mise à jour inattendue ne casse tes workflows. Pour une sécurité maximale, on épingle même sur le hash d'un commit.

## Enchaîner des jobs et utiliser des matrices

Avec `needs`, un job n'attend la fin d'un autre que s'il a réussi :

```yaml
jobs:
  tests:
    runs-on: ubuntu-latest
    steps:
      - run: echo "tests ok"

  deploy:
    needs: tests
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    steps:
      - run: echo "déploiement"
```

Ici, `deploy` ne démarre que si `tests` est vert, et seulement sur la branche `main` (condition `if`).

Une **matrice** exécute le même job avec plusieurs combinaisons, sans copier-coller :

```yaml
jobs:
  tests:
    runs-on: ubuntu-latest
    strategy:
      fail-fast: false
      matrix:
        php: ["8.2", "8.3"]
        node: [18, 20]
    steps:
      - uses: actions/checkout@v4
      - uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
      - uses: actions/setup-node@v4
        with:
          node-version: ${{ matrix.node }}
      - run: echo "PHP ${{ matrix.php }}, Node ${{ matrix.node }}"
```

Cette matrice lance quatre jobs (2 versions de PHP fois 2 versions de Node). Utile pour une bibliothèque qui doit fonctionner partout ; moins pour une application qui n'a qu'un seul environnement de production.

## Secrets et variables

Un workflow a souvent besoin d'informations sensibles : jeton de déploiement, clé d'API, mot de passe de base de données. Tu ne les écris **jamais** dans le fichier YAML. Tu les enregistres dans *Settings → Secrets and variables → Actions*, puis tu les lis ainsi :

```yaml
- name: Déployer
  run: ./deploy.sh
  env:
    DEPLOY_TOKEN: ${{ secrets.DEPLOY_TOKEN }}
    APP_ENV: production
```

Règles importantes :

- GitHub **masque** la valeur d'un secret dans les logs (elle est remplacée par des astérisques) ;
- les secrets ne sont **pas transmis** aux workflows déclenchés par des PR venant d'un fork, pour éviter qu'un contributeur malveillant ne les vole ;
- les **variables** (onglet *Variables*) servent pour les valeurs non sensibles, comme un nom de domaine ;
- les **environnements** (*Settings → Environments*) permettent d'associer des secrets propres à `staging` ou `production`, et d'exiger une **approbation manuelle** avant un déploiement en production.

Par sécurité, limite aussi les droits du jeton automatique `GITHUB_TOKEN` :

```yaml
permissions:
  contents: read
```

Placé en haut du workflow, ce bloc applique le principe du moindre privilège : le workflow ne peut que lire le dépôt.

:::quiz
Où faut-il stocker une clé d'API nécessaire à un déploiement automatique ?
- [ ] En clair dans le fichier de workflow YAML
- [ ] Dans le README du dépôt
- [x] Dans les secrets du dépôt (Settings, Secrets and variables, Actions), lus via `secrets.NOM`
- [ ] Dans un fichier `.env` commité
> Les secrets GitHub sont chiffrés et masqués dans les logs. Écrire une clé dans un fichier versionné la rend lisible par quiconque accède au dépôt.
:::

## Rendre le CI obligatoire

Un workflow qui échoue sans conséquence ne sert à rien. Relie-le à la protection de branche vue précédemment :

1. *Settings → Branches* (ou *Rules*), sur la règle de `main`.
2. Coche **Require status checks to pass before merging**.
3. Recherche et sélectionne les jobs (`php`, `front`).

Désormais, le bouton de fusion d'une PR reste **grisé** tant que les checks ne sont pas verts. Pour que ces noms apparaissent dans la liste, le workflow doit avoir tourné au moins une fois récemment.

## Déployer avec Actions

Le déploiement continu consiste à publier automatiquement le code qui a passé les tests. Exemple de workflow déployant un site statique avec GitHub Pages à chaque push sur `main` :

```yaml
name: Deploy

on:
  push:
    branches: [main]

permissions:
  contents: read
  pages: write
  id-token: write

jobs:
  deploy:
    runs-on: ubuntu-latest
    environment:
      name: github-pages
    steps:
      - uses: actions/checkout@v4
      - uses: actions/setup-node@v4
        with:
          node-version: 20
          cache: npm
      - run: npm ci && npm run build
      - uses: actions/upload-pages-artifact@v3
        with:
          path: dist
      - uses: actions/deploy-pages@v4
```

Pour déployer sur un serveur (VPS), un schéma classique consiste à se connecter en SSH avec une clé stockée en secret, à récupérer le code et à lancer les commandes de mise à jour (`composer install`, `php artisan migrate --force`, `npm run build`). Quel que soit le mode, garde le même principe : **ce qui n'a pas passé les tests ne se déploie pas**.

## Diagnostiquer un échec

Quand un job passe au rouge :

1. Ouvre l'onglet *Actions*, clique sur l'exécution, puis sur le job en échec.
2. Les étapes sont repliées : la première avec une croix rouge est celle qui a échoué. Déplie-la et lis les **dernières lignes**, c'est là que se trouve l'erreur.
3. Reproduis en local avec la même commande (`php artisan test`). Si ça passe chez toi mais pas sur le runner, cherche une différence : version de PHP, fichier `.env` manquant, base de données absente, variable d'environnement.
4. Utilise `Re-run failed jobs` après correction, ou pousse un nouveau commit.

Pour obtenir plus de détails, active la journalisation de debug en ajoutant un secret `ACTIONS_STEP_DEBUG` à `true`. Et pour tester vite un workflow, `workflow_dispatch` t'évite de multiplier des commits d'essai.

## Atelier guidé : un pipeline CI complet

Compte une heure trente. Utilise un petit projet Node ou Laravel disposant d'au moins un test. À défaut, crée un projet Node minimal avec un script `npm test` qui exécute une assertion.

1. Vérifie que les tests passent en local (`npm test` ou `php artisan test`).
2. Crée `.github/workflows/ci.yml` avec le déclencheur `pull_request` et `push` sur `main`, un job `tests`, les étapes `checkout`, installation du langage, installation des dépendances et lancement des tests.
3. Pousse sur une branche `chore/ci`, ouvre une PR et observe l'exécution dans l'onglet *Actions* et dans le bas de la PR.
4. Casse volontairement un test, pousse, observe le check rouge, lis les logs, puis corrige.
5. Ajoute le cache des dépendances et compare la durée de la première exécution avec la deuxième.
6. Ajoute un bloc `permissions` avec `contents: read`.
7. Ajoute un second job `lint` (formatage ou analyse statique) qui s'exécute en parallèle.
8. Ajoute `workflow_dispatch` pour pouvoir lancer le workflow à la main et teste le bouton.
9. Dans la protection de `main`, exige les checks `tests` et `lint`. Vérifie que le bouton de fusion reste bloqué lorsqu'un test échoue.
10. Crée un secret `DEMO_TOKEN`, affiche-le dans un `echo` et constate qu'il est masqué (remplacé par des astérisques) dans les logs.

Pour t'auto-évaluer : explique la différence entre un workflow, un job et une step, puis pourquoi `npm ci` est préférable à `npm install` en CI.

## Erreurs fréquentes

- **Oublier `actions/checkout`.** Le runner démarre vide : les commandes ne trouvent aucun fichier.
- **Indentation YAML fausse ou tabulations.** Le workflow n'est pas reconnu ou une clé est ignorée.
- **Commiter un secret dans le YAML.** Il reste dans l'historique : change-le immédiatement.
- **Cache mal configuré.** Une clé qui ne change jamais conserve des dépendances périmées.
- **Utiliser `@main` ou `@latest` pour les actions.** Une mise à jour d'un tiers peut casser ou compromettre ton pipeline.
- **Tests qui dépendent de l'environnement local.** Ils passent chez toi, échouent sur le runner : utilise `.env.example`, SQLite en mémoire ou des services de test.
- **Ne pas rendre les checks obligatoires.** Le CI devient un simple avis que l'on peut ignorer.
- **Pipelines trop lents.** Si le retour dépasse dix minutes, les développeurs cessent d'attendre : parallélise, mets en cache, limite avec `paths`.

## Bonnes pratiques

- Fais du CI le gardien de `main` : checks obligatoires avant fusion.
- Garde les workflows rapides (cache, jobs parallèles, `paths`).
- Épingle les versions des actions et vérifie la fiabilité des actions tierces.
- Applique le moindre privilège avec `permissions`.
- Stocke chaque secret dans les secrets, par environnement si besoin ; ne les affiche jamais volontairement.
- Utilise des **environnements** avec approbation pour la production.
- Rends tes workflows reproductibles : mêmes versions que ta production, dépendances installées depuis le lockfile.
- Mets un badge d'état du CI dans le README.

## À retenir

- GitHub Actions exécute des **workflows** (fichiers YAML dans `.github/workflows/`) déclenchés par des **événements**.
- Un workflow contient des **jobs** (parallèles par défaut, ordonnés avec `needs`) composés de **steps** exécutés sur un **runner**.
- Les actions (`checkout`, `setup-node`, `setup-php`, `cache`) évitent de tout réécrire.
- Les **secrets** se déclarent dans les paramètres du dépôt, pas dans le code ; `permissions` restreint le jeton.
- Les **matrices** testent plusieurs versions, le **cache** accélère les exécutions.
- Un check n'a de valeur que s'il est **obligatoire** dans la protection de branche.
- Pas de déploiement sans tests verts.
