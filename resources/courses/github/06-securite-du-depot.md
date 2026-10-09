---
title: Sécurité du dépôt
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Un dépôt de code est une cible : il contient la logique de ton application, parfois des clés d'accès, et il alimente directement ce qui tourne en production. Une seule clé d'API publiée par erreur peut coûter des milliers d'euros de facture cloud en quelques heures, ou donner accès aux données de tes clients. La bonne nouvelle : GitHub fournit gratuitement la plupart des outils nécessaires, et quelques habitudes évitent l'essentiel des incidents.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi un secret commité est **compromis** pour toujours, et réagir correctement ;
- activer le **secret scanning** et la **push protection** ;
- surveiller les dépendances avec **Dependabot** (alertes et mises à jour automatiques) ;
- analyser ton code avec **CodeQL** (code scanning) ;
- protéger les branches et les tags avec des **rulesets** ;
- configurer les rôles, la 2FA, les clés et les jetons avec le moindre privilège ;
- signer tes commits et publier une politique de sécurité (`SECURITY.md`) ;
- sécuriser les workflows GitHub Actions.

Prérequis : les chapitres précédents, en particulier protection de branche et Actions. Prévois deux heures trente.

## Le modèle de menace

Avant de choisir des outils, pose-toi la question : **qu'est-ce qui peut mal tourner ?** Pour un dépôt, les risques les plus fréquents sont :

| Risque | Exemple |
| --- | --- |
| **Fuite de secrets** | Un `.env` ou une clé d'API commités et publiés |
| **Dépendance vulnérable** | Une bibliothèque npm ou Composer avec une faille connue |
| **Code vulnérable** | Une injection SQL, une faille XSS écrite par erreur |
| **Compte compromis** | Mot de passe volé, sans double authentification |
| **Branche modifiée sans contrôle** | Un push direct qui introduit une porte dérobée |
| **Pipeline détourné** | Un workflow qui exécute du code non fiable avec des secrets |

La sécurité d'un dépôt est une succession de couches : chacune réduit le risque, aucune ne suffit seule.

## Gérer les secrets : le principe de base

Un **secret** est toute information qui donne un accès : mot de passe, clé d'API, jeton, clé privée SSH, chaîne de connexion à une base de données, fichier `.env`.

Règle absolue : **un secret n'a pas sa place dans Git**. Pourquoi ? Parce que Git conserve tout l'historique. Supprimer le fichier dans un commit ultérieur ne l'efface pas : n'importe qui peut remonter à l'ancien commit. Si le dépôt est public, des robots scrutent chaque push en continu et exploitent les clés en quelques minutes.

### Comment faire à la place

- **Configuration** : un fichier `.env` ignoré par Git (`.gitignore`), plus un `.env.example` versionné avec des valeurs fictives ;
- **Production** : variables d'environnement du serveur ou du service d'hébergement ;
- **CI/CD** : secrets GitHub Actions ;
- **Équipe** : gestionnaire de mots de passe partagé ou coffre-fort (Vault, Doppler, 1Password).

```text
# .env.example (versionné)
APP_KEY=
DB_PASSWORD=
STRIPE_SECRET=sk_test_xxx   # valeur fictive

# .env (jamais versionné)
APP_KEY=base64:K3p...
DB_PASSWORD=Un-vrai-mot-de-passe
STRIPE_SECRET=sk_live_...
```

### Si un secret a fuité

Réagis dans cet ordre, **sans attendre** :

1. **Révoque et remplace** le secret immédiatement auprès du service concerné (nouvelle clé, nouveau mot de passe). C'est l'étape qui compte vraiment.
2. Vérifie les journaux d'utilisation du service pour détecter un usage frauduleux.
3. Retire le secret du code et utilise une variable d'environnement.
4. Si tu veux nettoyer l'historique, utilise `git filter-repo` ou BFG Repo-Cleaner, puis force le push. Cela réécrit l'historique et oblige tous les collaborateurs à recloner. Ne le fais que **en complément** de la révocation, jamais à sa place.

> **Erreur fréquente** : supprimer le fichier, commiter, et considérer l'affaire close. Le secret reste dans l'historique et dans les clones déjà faits : il faut le considérer comme public.

:::quiz
Tu viens de pousser par erreur une clé d'API dans un dépôt public. Quelle est la première action à faire ?
- [ ] Supprimer le fichier et faire un nouveau commit
- [ ] Rendre le dépôt privé et attendre
- [x] Révoquer la clé auprès du service concerné et en générer une nouvelle
- [ ] Renommer le dépôt
> Tant que la clé reste valide, elle est exploitable. La révocation immédiate est la seule mesure qui neutralise vraiment la fuite ; le nettoyage de l'historique vient ensuite.
:::

## Secret scanning et push protection

GitHub peut détecter automatiquement les secrets de centaines de fournisseurs (clés AWS, jetons GitHub, Stripe, Google, etc.).

- **Secret scanning** analyse le dépôt et son historique, et alerte en cas de découverte. Il est activé par défaut sur les dépôts publics ; sur les dépôts privés, il dépend de ton offre.
- **Push protection** va plus loin : elle **bloque le `git push`** si un secret est détecté dans les commits, avant même que le secret n'arrive sur GitHub.

Pour les activer : *Settings → Code security* (ou *Advanced Security*), puis activer *Secret scanning* et *Push protection*.

```text
remote: error: GH013: Repository rule violations found for refs/heads/main.
remote: - Push cannot contain secrets
remote:   —— GitHub Personal Access Token ————————————
remote:     locations: commit: 3f2a9c1, path: config.js:12
```

Si le push est bloqué, ne cherche pas à le contourner : retire le secret du commit (avec `git commit --amend` ou un rebase interactif si le commit n'est pas partagé), puis repousse.

En complément local, des outils comme **gitleaks** ou **trufflehog** analysent ton dépôt avant le push, par exemple dans un hook `pre-commit`.

```bash
gitleaks detect --source . --verbose
```

## Dépendances : Dependabot

Un projet moderne repose sur des centaines de paquets tiers. Une faille dans l'un d'eux devient une faille chez toi. **Dependabot** surveille ça de deux façons.

### Alertes

Activées dans *Settings → Code security → Dependabot alerts*. GitHub compare tes fichiers de verrouillage (`package-lock.json`, `composer.lock`) à une base de vulnérabilités connues (les *advisories*) et t'avertit dans l'onglet *Security*.

### Mises à jour automatiques

Crée `.github/dependabot.yml` : Dependabot ouvrira lui-même des PR pour mettre à jour tes dépendances.

```yaml
version: 2
updates:
  - package-ecosystem: "composer"
    directory: "/"
    schedule:
      interval: "weekly"
  - package-ecosystem: "npm"
    directory: "/"
    schedule:
      interval: "weekly"
    groups:
      minor-et-patch:
        update-types: ["minor", "patch"]
  - package-ecosystem: "github-actions"
    directory: "/"
    schedule:
      interval: "monthly"
```

Chaque PR de Dependabot passe par ton CI : si les tests sont verts, tu peux fusionner sereinement. C'est exactement pour cela qu'un bon pipeline de tests est un investissement de sécurité. Le regroupement (`groups`) évite d'être noyé sous des dizaines de petites PR.

Pour contrôler ce que tu installes en local :

```bash
npm audit
composer audit
```

Ne retarde pas indéfiniment les mises à jour de sécurité : plus tu attends, plus le saut de versions est risqué.

## Analyser le code : CodeQL

Le **code scanning** analyse ton propre code pour détecter des vulnérabilités classiques : injection SQL, XSS, désérialisation dangereuse, chemins de fichiers non validés. L'outil de GitHub s'appelle **CodeQL**. Il est gratuit pour les dépôts publics.

Pour l'activer : *Security → Code scanning → Set up → Default*. GitHub configure un workflow qui analyse le code à chaque PR et chaque push, et affiche les résultats directement dans la PR, ligne par ligne. Pour une configuration avancée, tu peux ajouter un workflow dédié :

```yaml
name: CodeQL

on:
  push:
    branches: [main]
  pull_request:
  schedule:
    - cron: "0 4 * * 1"

jobs:
  analyze:
    runs-on: ubuntu-latest
    permissions:
      security-events: write
      contents: read
    strategy:
      matrix:
        language: [javascript-typescript]
    steps:
      - uses: actions/checkout@v4
      - uses: github/codeql-action/init@v3
        with:
          languages: ${{ matrix.language }}
      - uses: github/codeql-action/analyze@v3
```

Un scanner produit parfois de faux positifs : lis chaque alerte, décide si elle est réelle, corrige ou ferme-la avec une justification (« faux positif », « utilisé dans les tests »).

:::quiz
Quelle différence y a-t-il entre Dependabot et CodeQL ?
- [ ] Dependabot analyse ton code, CodeQL met à jour tes dépendances
- [x] Dependabot surveille et met à jour les dépendances tierces ; CodeQL analyse ton propre code pour y trouver des vulnérabilités
- [ ] Ils font exactement la même chose
- [ ] Dependabot ne fonctionne que sur les dépôts privés
> Dependabot concerne la chaîne d'approvisionnement (paquets tiers), CodeQL concerne les failles dans le code que tu écris toi-même.
:::

## Protéger les branches et les tags

Tu as déjà vu la protection de `main`. Les **rulesets** (*Settings → Rules → Rulesets*) généralisent le principe et se combinent. Un ruleset de base :

- cible la branche par défaut ;
- **exige une PR** avec au moins une approbation, et rejette les approbations périmées quand de nouveaux commits arrivent ;
- **exige les checks** (tests, CodeQL) ;
- **bloque les force push** et les suppressions ;
- **exige des commits signés** (voir ci-dessous) pour les projets sensibles ;
- peut aussi protéger les **tags** de version (ceux qui commencent par v) pour empêcher leur modification.

Le fichier **CODEOWNERS** désigne qui doit relire quoi :

```text
# .github/CODEOWNERS
*                   @hancode/equipe-dev
/.github/           @hancode/securite
/app/Payments/      @awa-kouassi
```

Si la règle « Require review from Code Owners » est active, une PR touchant `/app/Payments/` ne peut pas être fusionnée sans l'approbation du propriétaire déclaré. C'est une barrière efficace pour les zones critiques : paiement, authentification, workflows.

## Comptes, accès et jetons

La plupart des compromissions viennent des comptes, pas du code.

- **2FA obligatoire** pour tous les membres : dans une organisation, *Settings → Authentication security → Require two-factor authentication*. Préfère les *passkeys* ou clés matérielles.
- **Moindre privilège** : donne le rôle *Read* par défaut, *Write* à ceux qui développent, *Admin* à une ou deux personnes.
- **Retire les accès** des personnes qui quittent le projet, et fais un audit des collaborateurs tous les trimestres.
- **Jetons d'accès** : préfère les *fine-grained personal access tokens* (limités à un dépôt, à des droits précis, avec une date d'expiration) aux jetons classiques à large périmètre. Révoque ceux que tu n'utilises plus (*Settings → Developer settings*).
- **Clés SSH** : une clé par machine, avec phrase secrète. Supprime les clés des machines perdues ou anciennes.
- **Deploy keys** : pour un serveur qui doit seulement lire le dépôt, utilise une clé de déploiement en lecture seule plutôt que ta clé personnelle.

Consulte aussi *Settings → Security log* pour repérer des connexions ou actions suspectes.

## Signer ses commits

Sur Git, n'importe qui peut écrire n'importe quel nom et e-mail dans `user.name` et `user.email` : rien n'empêche d'usurper l'identité d'un collègue dans l'historique. La **signature** cryptographique prouve que le commit vient bien de toi. GitHub affiche alors le badge **Verified**.

Configuration avec SSH (la plus simple si tu as déjà une clé) :

```bash
git config --global gpg.format ssh
git config --global user.signingkey ~/.ssh/id_ed25519.pub
git config --global commit.gpgsign true
```

Puis ajoute la même clé publique sur GitHub, dans *Settings → SSH and GPG keys*, avec le type **Signing Key**. Les commits que tu fais ensuite sont signés automatiquement. La signature est surtout utile pour les projets sensibles ou open source.

## Publier une politique de sécurité

Si quelqu'un découvre une faille dans ton projet, il doit savoir **où** la signaler, de préférence en privé, sans l'étaler dans une issue publique. Crée un fichier `SECURITY.md` à la racine :

```markdown
# Politique de sécurité

## Versions supportées
Seule la dernière version mineure reçoit des correctifs.

## Signaler une vulnérabilité
Ne crée pas d'issue publique. Utilise l'onglet « Security → Report a vulnerability »
ou écris à securite@exemple.com. Réponse sous 72 heures.
```

Active aussi *Private vulnerability reporting* dans les paramètres : le chercheur te contacte via un canal privé, vous corrigez ensemble, puis vous publiez un avis de sécurité (*security advisory*).

## Sécuriser ses workflows

Les workflows Actions manipulent des secrets et exécutent du code : ce sont des cibles de choix.

- **`permissions` minimales** : `contents: read` par défaut, élargi uniquement quand nécessaire ;
- **épingler les actions** tierces sur une version précise (idéalement un hash de commit) et mettre à jour via Dependabot ;
- **ne jamais exécuter de code de PR externe avec des secrets** : l'événement `pull_request_target` est dangereux s'il fait un `checkout` du code de la PR ;
- **ne pas injecter** directement des données non fiables (titre de PR, nom de branche) dans une commande `run` : passe-les par des variables d'environnement ;
- **limiter les environnements de production** avec une approbation manuelle.

```yaml
# À éviter : injection possible via le titre de la PR
- run: echo "${{ github.event.pull_request.title }}"

# Préférable
- run: echo "$TITRE"
  env:
    TITRE: ${{ github.event.pull_request.title }}
```

## Atelier guidé : durcir un dépôt

Compte une heure trente. Utilise un dépôt de test (public de préférence, pour accéder à toutes les fonctions gratuites).

1. Ajoute `.env` au `.gitignore`, crée un `.env.example` avec des valeurs fictives.
2. Active dans *Settings → Code security* : Dependabot alerts, Dependabot security updates, Secret scanning et Push protection.
3. Crée `.github/dependabot.yml` pour `npm` (ou `composer`) et `github-actions`, avec une fréquence hebdomadaire.
4. Tente de pousser un faux secret au format reconnu (un jeton de test fourni par la documentation du fournisseur) et observe le blocage de la push protection. Retire-le proprement.
5. Active le code scanning avec la configuration par défaut de CodeQL et attends la première analyse. Lis le résultat dans *Security*.
6. Crée un ruleset sur `main` : PR obligatoire, checks obligatoires, force push bloqué, suppression bloquée.
7. Ajoute un fichier `CODEOWNERS` qui désigne toi-même comme propriétaire du dossier `.github/`, et active la revue des propriétaires.
8. Configure la signature de commits avec ta clé SSH et vérifie le badge *Verified* sur un nouveau commit.
9. Écris `SECURITY.md` et active le signalement privé de vulnérabilités.
10. Fais l'audit de ton compte : 2FA active, liste des clés SSH et des jetons, révocation de ce qui est obsolète.
11. Lance `npm audit` ou `composer audit` en local et note ce que tu corrigerais en premier.

Pour t'auto-évaluer : liste les couches de protection mises en place et, pour chacune, le risque qu'elle réduit.

## Erreurs fréquentes

- **Supprimer un secret sans le révoquer.** Il reste valide et présent dans l'historique.
- **Ignorer les alertes Dependabot.** Elles s'accumulent jusqu'à devenir ingérables.
- **Fusionner des PR de Dependabot sans tests.** Sans CI, une mise à jour peut casser la production.
- **Donner des jetons trop puissants.** Un jeton à large périmètre qui fuit donne accès à tous tes dépôts.
- **Protéger `main` mais pas les workflows.** Un workflow modifié peut exfiltrer des secrets : mets `.github/` sous CODEOWNERS.
- **Contourner la push protection** par réflexe au lieu de comprendre l'alerte.
- **Pas de 2FA sur le compte administrateur.** C'est le maillon le plus faible de toute la chaîne.
- **Confondre privé et sécurisé.** Un dépôt privé peut fuiter via un collaborateur ou un clone : les secrets n'ont pas leur place dedans non plus.

## Bonnes pratiques

- Aucun secret dans Git : `.env` ignoré, secrets dans les coffres adaptés.
- Active secret scanning, push protection, Dependabot et CodeQL dès la création du dépôt.
- Fais passer les mises à jour de dépendances par le CI et fusionne-les régulièrement.
- Protège `main` et les tags par un ruleset, avec CODEOWNERS pour les zones sensibles.
- Applique le moindre privilège aux rôles, jetons, clés et permissions de workflows.
- Exige la 2FA pour tous et audite les accès périodiquement.
- Publie un `SECURITY.md` et un canal de signalement privé.
- Prépare à l'avance la procédure en cas de fuite : qui révoque quoi, et comment.

## À retenir

- Un secret commité est **compromis** : la première action est de le **révoquer**, le nettoyage de l'historique n'est que complémentaire.
- **Secret scanning** détecte les fuites ; la **push protection** les bloque avant publication.
- **Dependabot** surveille et met à jour les dépendances ; **CodeQL** analyse ton propre code.
- Les **rulesets**, le fichier **CODEOWNERS** et les checks obligatoires verrouillent la branche principale.
- La **2FA**, le moindre privilège et les jetons à portée limitée protègent les comptes.
- La **signature de commits** prouve l'identité de l'auteur ; `SECURITY.md` organise le signalement des failles.
- Les workflows Actions se durcissent : `permissions` minimales, actions épinglées, pas de données non fiables dans `run`.
