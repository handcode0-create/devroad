---
title: Issues et projet
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Le code n'est que la moitié du travail : il faut aussi savoir **quoi** construire, **dans quel ordre**, et **qui** s'en occupe. GitHub intègre pour cela un outil de suivi : les **issues** (tickets) pour décrire les bugs et les tâches, les **labels** et **milestones** pour les organiser, et **GitHub Projects** pour les visualiser sur un tableau Kanban. Tu n'as pas besoin de Jira ou Trello : tout reste au même endroit que ton code.

À la fin du chapitre, tu seras capable de :

- rédiger des issues claires (bug, fonctionnalité, tâche) ;
- utiliser des **labels**, des **assignés** et des **milestones** ;
- créer des **modèles d'issue** et des formulaires structurés ;
- relier issues, commits et pull requests ;
- construire un tableau **GitHub Projects** (Kanban) avec des champs personnalisés ;
- découper un besoin en sous-tâches et planifier une itération ;
- utiliser les **Discussions** pour les échanges qui ne sont pas des tâches.

Prérequis : les chapitres sur les dépôts et les pull requests. Prévois deux heures trente. Tu appliqueras tout sur DevRoad ou sur un dépôt de test.

## Qu'est-ce qu'une issue ?

Une **issue** est une fiche qui décrit une unité de travail ou un problème. Elle a un titre, une description en Markdown, un numéro unique (`#42`), un état (ouverte ou fermée), et un fil de discussion. Elle sert à :

- signaler un **bug** ;
- proposer ou décrire une **fonctionnalité** ;
- suivre une **tâche** technique (migration, refactoring, documentation) ;
- poser une **question** ou documenter une décision.

Le numéro est partagé entre issues et pull requests : le `#42` désigne l'un ou l'autre, jamais les deux. Tu peux le mentionner n'importe où (commit, PR, commentaire) et GitHub crée automatiquement un lien.

```bash
git commit -m "fix(auth): empêche la double soumission du formulaire (#42)"
```

Dans l'onglet *Issues* du dépôt, la barre de recherche accepte des filtres puissants :

```text
is:open is:issue label:bug assignee:@me
is:open milestone:"v1.0" sort:created-asc
no:assignee label:"good first issue"
```

## Rédiger une bonne issue

Une mauvaise issue : « Ça marche pas ». Une bonne issue permet à quelqu'un d'autre de reproduire le problème sans te poser de question.

### Un bug

```markdown
**Titre** : Le filtre par niveau n'affiche rien avec « Professionnel »

## Comportement observé
Sélectionner « Professionnel » vide la liste.

## Comportement attendu
Les roadmaps de niveau professionnel s'affichent.

## Étapes pour reproduire
1. Aller sur /roadmaps
2. Choisir « Professionnel » dans le filtre
3. La liste est vide

## Environnement
- Navigateur : Chrome 128, Android
- Version : v1.3.0
```

### Une fonctionnalité

Décris le besoin de l'utilisateur, pas la solution technique :

```markdown
**Titre** : Pouvoir marquer un chapitre comme terminé

## Contexte
En tant qu'apprenant, je veux voir ma progression pour rester motivé.

## Critères d'acceptation
- [ ] Un bouton « Terminé » est visible sur chaque chapitre
- [ ] La progression se met à jour sur la roadmap
- [ ] L'état est conservé après rechargement
```

Les **critères d'acceptation** sous forme de cases à cocher sont précieux : ils disent précisément quand l'issue est terminée, et GitHub affiche leur avancement (« 2 of 3 tasks ») dans la liste.

> **Astuce** : une issue = un problème ou un objectif. Si le titre contient « et », découpe-la. Les petites issues se terminent, les grosses stagnent.

:::quiz
Quel élément rend une issue de bug vraiment exploitable par un autre développeur ?
- [ ] Un titre en majuscules
- [x] Les étapes précises pour reproduire, le comportement attendu et l'environnement
- [ ] Une longue description des sentiments de l'utilisateur
- [ ] Un lien vers un autre dépôt
> Sans étapes de reproduction ni contexte, personne ne peut confirmer ni corriger le bug. Une issue claire fait gagner du temps à toute l'équipe.
:::

## Labels, assignés et milestones

Pour organiser des dizaines d'issues, GitHub propose trois outils.

### Les labels

Un **label** est une étiquette colorée. Les labels par défaut sont `bug`, `enhancement`, `documentation`, `good first issue`, `help wanted`, `question`. Beaucoup d'équipes construisent leurs propres familles, par exemple :

| Famille | Exemples |
| --- | --- |
| Type | `type: bug`, `type: feature`, `type: chore` |
| Priorité | `priority: high`, `priority: low` |
| Zone | `area: frontend`, `area: api` |
| Statut | `status: blocked`, `status: needs-info` |

Garde un jeu **limité et cohérent** : vingt labels bien utilisés valent mieux que cent labels ignorés. Tu les gères dans *Issues → Labels*.

### Les assignés

Un **assigné** est la personne responsable de l'issue. Une issue sans assigné n'appartient à personne, donc personne ne la fait. Assigne dès que quelqu'un s'engage à la traiter. Pour te l'attribuer en un geste, utilise `gh issue edit 42 --add-assignee @me`.

### Les milestones

Un **milestone** (jalon) regroupe les issues et PR d'une même livraison ou itération, avec une date d'échéance éventuelle. Exemples : `v1.0`, `Sprint 12`, `MVP`. GitHub affiche une barre de progression (issues fermées / total). C'est un outil simple pour répondre à « où en est-on avant la livraison ? ».

## Modèles d'issue

Pour que chaque issue arrive bien structurée, crée des modèles dans `.github/ISSUE_TEMPLATE/`.

### Modèle Markdown simple

Fichier `.github/ISSUE_TEMPLATE/bug.md` :

```text
---
name: Signaler un bug
about: Un comportement inattendu ou une erreur
labels: bug
---

## Comportement observé

## Comportement attendu

## Étapes pour reproduire
1.
2.

## Environnement
- Navigateur :
- Version :
```

### Formulaire structuré (YAML)

Un fichier `.yml` crée un vrai formulaire avec des champs obligatoires :

```yaml
name: Signaler un bug
description: Un comportement inattendu
labels: ["bug"]
body:
  - type: textarea
    id: observe
    attributes:
      label: Comportement observé
    validations:
      required: true
  - type: textarea
    id: etapes
    attributes:
      label: Étapes pour reproduire
    validations:
      required: true
  - type: dropdown
    id: navigateur
    attributes:
      label: Navigateur
      options: [Chrome, Firefox, Safari, Autre]
```

Ajoute aussi un fichier `config.yml` dans le même dossier pour désactiver les issues vides ou rediriger les questions vers les Discussions :

```yaml
blank_issues_enabled: false
contact_links:
  - name: Poser une question
    url: https://github.com/hancode/devroad/discussions
    about: Pour les questions d'usage, utilise les Discussions
```

## Relier issues, branches et PR

Le suivi devient puissant quand tout est relié :

- crée la branche à partir de l'issue : `gh issue develop 42 --checkout` crée une branche liée ;
- mentionne `#42` dans les commits ;
- écris `Closes #42` dans la PR : l'issue se ferme à la fusion ;
- dans l'issue, la section *Development* affiche les branches et PR liées.

Tu peux aussi créer des **listes de tâches** (*task lists*) dans une issue parente :

```markdown
## Sous-tâches
- [ ] #43 Créer le modèle Progression
- [ ] #44 Ajouter le bouton Terminé
- [ ] #45 Afficher la barre de progression
```

Chaque numéro devient un lien dont l'état (ouvert ou fermé) est visible. C'est la manière de découper une fonctionnalité en morceaux livrables séparément.

## GitHub Projects : le tableau de bord

**GitHub Projects** est un outil de planification qui se branche sur tes issues et PR. Tu crées un projet depuis l'onglet *Projects* du dépôt ou de l'organisation (*New project*), et tu choisis un modèle : *Board* (Kanban), *Table* (tableur) ou *Roadmap* (frise chronologique).

### Le tableau Kanban

Un Kanban organise le travail en colonnes qui représentent l'avancement :

```text
┌─ Backlog ─┐ ┌─ À faire ─┐ ┌─ En cours ─┐ ┌─ En revue ─┐ ┌─ Terminé ─┐
│ #51       │ │ #44       │ │ #43        │ │ #40 (PR)   │ │ #38       │
│ #52       │ │ #45       │ │            │ │            │ │ #39       │
└───────────┘ └───────────┘ └────────────┘ └────────────┘ └───────────┘
```

Tu déplaces les cartes de gauche à droite. Le principe clé : **limiter le travail en cours**. Trois cartes dans « En cours » pour une équipe de trois, pas quinze.

Pour ajouter des éléments : bouton `+` en bas d'une colonne, puis `#` pour chercher une issue ou une PR. Tu peux aussi créer une issue directement depuis le projet.

### Les champs personnalisés

Le champ **Status** détermine la colonne. Tu peux en ajouter d'autres :

- **Priority** (liste : Haute, Moyenne, Basse) ;
- **Size** ou **Estimate** (nombre ou taille : S, M, L) ;
- **Iteration** (période de deux semaines) ;
- **Target date** (date).

Dans la vue *Table*, tu groupes par statut, tu filtres par assigné, tu trie par priorité. Tu peux créer plusieurs **vues** du même projet : un tableau pour l'équipe, un tableau de bord par personne, une frise pour le client.

### Automatiser le tableau

Dans *Workflows* du projet, des règles intégrées font gagner du temps :

- quand une issue est ajoutée, son statut passe à « À faire » ;
- quand une PR est fusionnée, l'élément passe à « Terminé » ;
- quand une issue est fermée, idem.

Ainsi, personne ne perd de temps à déplacer des cartes manuellement.

:::quiz
Quel est l'avantage d'une règle d'automatisation du projet comme « quand une PR est fusionnée, passer l'élément en Terminé » ?
- [ ] Elle supprime les issues anciennes
- [x] Elle garde le tableau à jour sans intervention manuelle
- [ ] Elle fusionne les PR toute seule
- [ ] Elle remplace les relectures de code
> Les workflows de projet synchronisent le tableau avec l'activité réelle (PR fusionnée, issue fermée), ce qui évite des données périmées.
:::

## Planifier une itération

Voici une manière simple de piloter un projet avec ces outils, adaptée à une petite équipe :

1. **Collecter** : toutes les idées et bugs arrivent sous forme d'issues dans le *Backlog*.
2. **Trier** : une fois par semaine, ajoute labels et priorités. Ferme les doublons avec une référence (`Duplicate of #12`).
3. **Planifier** : choisis ce qu'on livre pendant les deux prochaines semaines, affecte-le à l'itération et aux personnes.
4. **Réaliser** : chaque personne prend une carte « À faire », la passe « En cours », ouvre une PR liée.
5. **Revoir** : en fin d'itération, regarde ce qui est terminé, ce qui ne l'est pas et pourquoi.
6. **Livrer** : tague la version, publie la release, ferme le milestone.

Le but n'est pas de suivre un processus à la lettre mais d'avoir une **vue partagée** : tout le monde sait ce qui est fait, en cours et à venir.

## Discussions, wiki et autres outils

Toutes les conversations ne sont pas des tâches. Les **Discussions** (à activer dans les paramètres) accueillent les questions, les idées en vrac, les annonces et les sondages. Une discussion peut être convertie en issue quand elle débouche sur une tâche concrète.

Le **Wiki** héberge de la documentation longue rédigée en Markdown ; pour la documentation liée au code, un dossier `docs/` versionné avec le dépôt reste préférable, car il suit les branches et passe par la revue.

## Atelier guidé : monter le suivi d'un projet

Compte une heure trente dans un dépôt de test (ou DevRoad si tu y as accès).

1. Crée un jeu de labels cohérent : trois pour le type, trois pour la priorité, deux pour la zone. Supprime les labels inutiles.
2. Crée deux modèles d'issue dans `.github/ISSUE_TEMPLATE/` : un pour les bugs (formulaire YAML) et un pour les fonctionnalités. Ajoute `config.yml` pour désactiver les issues vides.
3. Pousse sur `main` via une PR et vérifie que le bouton *New issue* propose tes modèles.
4. Crée un milestone `v0.1` avec une échéance dans deux semaines.
5. Ouvre une issue de fonctionnalité « Marquer un chapitre comme terminé » avec des critères d'acceptation en cases à cocher, puis découpe-la en trois sous-issues listées dans une task list.
6. Ouvre au moins trois autres issues (deux bugs, une tâche) avec labels, assigné et milestone.
7. Crée un projet GitHub avec la vue *Board* : colonnes Backlog, À faire, En cours, En revue, Terminé. Ajoute un champ **Priority**.
8. Ajoute toutes tes issues au projet et active les workflows automatiques (ajout, fermeture, fusion de PR).
9. Prends une issue, crée sa branche avec `gh issue develop`, fais un commit, ouvre une PR avec `Closes #n` et observe le déplacement automatique de la carte.
10. Crée une vue *Table* groupée par priorité et filtrée sur toi-même.

Pour t'auto-évaluer : explique comment une idée circule, de la discussion jusqu'à la carte « Terminé », en citant les objets GitHub utilisés à chaque étape.

## Erreurs fréquentes

- **Issues vagues.** « Bug sur la page » ne se corrige pas. Exige des étapes de reproduction.
- **Trop de labels.** Au-delà d'une vingtaine, plus personne ne sait lesquels utiliser.
- **Issues sans assigné.** Si personne n'en est responsable, elles stagnent.
- **Colonne « En cours » surchargée.** Commencer dix choses en finit zéro.
- **Une issue fourre-tout.** Elle reste ouverte des mois. Découpe-la.
- **Ne jamais fermer ni trier.** Un backlog de 300 issues obsolètes décourage tout le monde ; fais un tri régulier.
- **Mélanger discussion et tâche.** Les questions ouvertes encombrent les issues : oriente-les vers les Discussions.
- **Oublier de relier PR et issue.** L'issue reste ouverte alors que le code est livré.

## Bonnes pratiques

- Une issue = un objectif livrable, avec des critères d'acceptation.
- Utilise des modèles pour imposer la structure aux auteurs.
- Garde un jeu de labels court et documenté.
- Assigne et planifie : sans responsable ni échéance, il n'y a pas d'engagement.
- Relie toujours commit, PR et issue (`Closes #n`).
- Limite le travail en cours sur le tableau.
- Automatise les mouvements de cartes pour que le tableau reste fiable.
- Fais un tri hebdomadaire du backlog.

## À retenir

- Une **issue** décrit un bug, une fonctionnalité ou une tâche ; son numéro est partagé avec les PR.
- Les **labels** classent, les **assignés** responsabilisent, les **milestones** regroupent par livraison.
- Les **modèles d'issue** (Markdown ou formulaires YAML) garantissent des tickets complets.
- Les mots-clés `Closes`, `Fixes` et `Resolves` relient une PR à une issue et la ferment à la fusion.
- **GitHub Projects** offre un tableau Kanban, une table et une frise, avec des champs personnalisés et des automatisations.
- Découpe les gros besoins en sous-tâches et limite le travail en cours.
- Les **Discussions** accueillent ce qui n'est pas une tâche.
