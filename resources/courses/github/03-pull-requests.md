---
title: Pull Requests
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

La **pull request** (PR) est le cœur de la collaboration sur GitHub. C'est une demande adressée à l'équipe : « voici mes modifications, relisez-les et intégrez-les à la branche principale ». Elle sert à la fois de discussion, de contrôle qualité, de documentation et de point de passage obligé avant la fusion.

À la fin du chapitre, tu seras capable de :

- ouvrir une pull request depuis une branche ;
- rédiger un titre et une description qui facilitent la relecture ;
- relire le code d'un collègue : commenter, suggérer, approuver ou demander des changements ;
- répondre aux retours et mettre à jour ta PR ;
- choisir entre les trois modes de fusion : **merge**, **squash** et **rebase** ;
- résoudre un conflit directement signalé par la PR ;
- utiliser des modèles de PR et lier une PR à une issue.

Prérequis : les deux chapitres précédents. Prévois deux heures trente. Pour t'entraîner, l'idéal est de travailler avec un camarade, sinon tu ouvriras des PR sur ton propre dépôt.

## Pourquoi passer par une pull request ?

Sans PR, un développeur pousse son code sur `main` et espère que personne ne s'en rende compte si c'est cassé. Avec une PR :

- le code est **relu** par au moins une autre personne avant d'arriver en production ;
- les **tests automatiques** s'exécutent avant la fusion ;
- les décisions techniques sont **tracées** dans la discussion ;
- les relecteurs apprennent le code des autres, et les juniors progressent grâce aux retours.

```text
feature/filtre ──▶ Pull Request ──▶ Relecture + tests ──▶ Merge dans main
                        ▲                  │
                        └── corrections ◀──┘
```

La PR n'est pas une formalité : c'est l'endroit où la qualité se construit.

## Ouvrir une pull request

Le cycle part du terminal :

```bash
git switch main
git pull --rebase
git switch -c feature/filtre-niveau

# ... développe et commite ...
git push -u origin feature/filtre-niveau
```

Dans la sortie du `push`, GitHub affiche un lien pour créer la PR. Sur la page du dépôt, un bandeau jaune **Compare & pull request** apparaît également. Avec la CLI :

```bash
gh pr create --title "feat(roadmaps): ajoute le filtre par niveau" --body "Voir description"
```

Le formulaire de création demande :

- **base** : la branche qui **reçoit** les changements (en général `main`) ;
- **compare** : ta branche de fonctionnalité ;
- un **titre** et une **description** ;
- des **relecteurs** (*Reviewers*), des **assignés**, des **labels** ;
- l'option **Create draft pull request** : une PR en brouillon signale que le travail n'est pas terminé mais que tu veux déjà de l'avis ou des tests.

## Bien rédiger sa PR

Une bonne description fait gagner des heures au relecteur. Modèle simple :

```markdown
## Contexte
Les utilisateurs ne peuvent pas filtrer les roadmaps par niveau (#42).

## Changements
- Ajoute un composant `FiltreNiveau` (React)
- Ajoute le scope `niveau` sur le modèle `Roadmap`
- Met à jour la page d'index

## Comment tester
1. `php artisan migrate:fresh --seed`
2. Ouvrir /roadmaps et choisir « Débutant »
3. Vérifier que seules les roadmaps débutantes s'affichent

## Captures d'écran
(avant / après)

Closes #42
```

Principes :

- **Une PR = un sujet.** Une PR de 2 000 lignes est relue en diagonale ; une PR de 200 lignes est relue pour de bon.
- Explique le **pourquoi**, pas seulement le quoi : le code montre déjà le quoi.
- Dis **comment tester**.
- Ajoute des **captures d'écran** pour toute modification visuelle.
- Écris un titre au format Conventional Commits : il deviendra souvent le message de fusion.

La ligne `Closes #42` est un **mot-clé de liaison** : à la fusion de la PR, l'issue 42 est fermée automatiquement. D'autres mots fonctionnent : `Fixes #42`, `Resolves #42`.

### Modèle de PR

Pour que chaque PR parte avec une structure cohérente, crée un fichier `.github/pull_request_template.md` contenant les rubriques ci-dessus. GitHub le préremplit à chaque nouvelle PR.

:::quiz
À quoi sert la ligne `Closes #42` dans la description d'une pull request ?
- [ ] À supprimer le commit numéro 42
- [x] À fermer automatiquement l'issue 42 quand la PR est fusionnée
- [ ] À copier la PR 42 dans la nouvelle
- [ ] À bloquer la fusion tant que 42 commentaires n'ont pas été postés
> Les mots-clés `Closes`, `Fixes` et `Resolves` suivis d'un numéro d'issue lient la PR à l'issue et la ferment à la fusion.
:::

## Les onglets d'une pull request

Une fois créée, la PR comporte quatre vues principales :

- **Conversation** : la description, la discussion, les revues, l'état des tests et le bouton de fusion ;
- **Commits** : la liste des commits de la branche ;
- **Checks** : le résultat des vérifications automatiques ;
- **Files changed** : le diff, ligne par ligne, où se fait la relecture.

Dans *Files changed*, tu peux masquer les modifications d'espaces, passer en vue côte à côte (*split*) et marquer les fichiers comme « Viewed » au fil de ta lecture.

## Relire le code d'un autre

Relire n'est pas chercher des fautes pour montrer sa supériorité. C'est un travail d'équipe pour que le résultat soit meilleur.

### Comment procéder

1. Lis d'abord la **description** : comprends l'objectif avant de juger le code.
2. Parcours *Files changed*. Clique sur le `+` bleu à gauche d'une ligne pour **commenter** cette ligne ; clique-glisse pour commenter plusieurs lignes.
3. Utilise le bloc **suggestion** pour proposer une modification précise que l'auteur peut accepter en un clic :

Dans le champ de commentaire, le bouton « Add a suggestion » (icône avec un `±`) insère un bloc spécial : tu remplaces son contenu par la version corrigée de la ligne, et l'auteur voit un bouton « Commit suggestion » pour l'appliquer d'un clic.


4. Quand tu as terminé, clique sur **Review changes** et choisis :

| Choix | Signification |
| --- | --- |
| **Comment** | Remarques générales, sans décision |
| **Approve** | Le code est bon, tu valides la fusion |
| **Request changes** | Des corrections sont nécessaires avant fusion |

### Que regarder ?

- **Correction** : le code fait-il ce que la PR annonce ? Gère-t-il les cas limites ?
- **Lisibilité** : noms clairs, fonctions courtes, pas de duplication ?
- **Tests** : la nouvelle logique est-elle couverte ?
- **Sécurité** : entrées validées, aucune clé en dur, droits vérifiés ?
- **Performance** : pas de requête en boucle, pas de gros traitement inutile ?

### Ton et formulation

Critique le **code**, jamais la personne. Pose des questions plutôt que d'ordonner :

- Éviter : « Ce code est mauvais. »
- Préférer : « Que penses-tu d'extraire cette logique dans une fonction ? Ce serait plus facile à tester. »

Distingue ce qui bloque de ce qui est une simple préférence en préfixant : `nit:` pour un détail mineur, `question:` pour une demande de clarification, `blocking:` pour ce qui empêche la fusion. Et n'hésite pas à féliciter quand quelque chose est bien fait.

> **Astuce** : en tant qu'auteur, relis ta propre PR dans l'interface avant de solliciter quelqu'un. Tu verras des oublis (fichier de debug, `console.log`) que tu n'aurais pas remarqués dans ton éditeur.

## Répondre aux retours

En tant qu'auteur, tu reçois des commentaires. Le réflexe : **remercier, corriger, répondre**.

1. Corrige dans ta branche locale.
2. Commite et pousse : la PR se met à jour automatiquement.

```bash
git add .
git commit -m "refactor(roadmaps): extrait le calcul du filtre"
git push
```

3. Réponds à chaque commentaire (« Corrigé », ou explique pourquoi tu ne suis pas la suggestion), puis clique sur **Resolve conversation**.
4. Si tu as besoin d'un nouvel avis, utilise **Re-request review**.

Si le relecteur propose une suggestion, tu peux utiliser **Commit suggestion** pour l'appliquer directement.

:::quiz
Un relecteur trouve un défaut mineur de style qu'il ne considère pas comme bloquant. Quelle formulation est la plus adaptée ?
- [ ] « Ton code est illisible, refais tout. »
- [x] « nit: tu pourrais renommer `x` en `niveauChoisi` pour plus de clarté. »
- [ ] « Request changes » sans aucune explication
- [ ] Ne rien dire et fusionner sans lire
> Un commentaire précis, courtois et clairement marqué comme mineur aide l'auteur à comprendre ce qui est essentiel et ce qui est optionnel.
:::

## Les vérifications automatiques

En bas de l'onglet *Conversation*, GitHub affiche l'état des **checks** : tests, linter, compilation. Un voyant vert signifie que tout passe, un rouge qu'il faut corriger avant de fusionner. Tu mettras en place ces vérifications avec GitHub Actions dans un chapitre suivant. Retiens dès maintenant que **la fusion d'une PR dont les tests sont rouges est une erreur**, sauf cas exceptionnel justifié.

## Fusionner : trois façons

Quand la PR est approuvée et les checks verts, le bouton de fusion propose trois modes :

| Mode | Résultat dans `main` | À utiliser quand |
| --- | --- | --- |
| **Create a merge commit** | Tous les commits de la branche + un commit de merge | Tu veux garder l'historique exact de la branche |
| **Squash and merge** | **Un seul** commit regroupant tout | La branche contient des commits de brouillon |
| **Rebase and merge** | Les commits de la branche rejoués, sans commit de merge | Les commits sont déjà propres et tu veux un historique linéaire |

```text
Merge commit :   ●──●──●────────M      (M = commit de merge)
                      \       /
                       ●──●──●

Squash :         ●──●──●──S            (S = un commit résumant la branche)

Rebase :         ●──●──●──a'──b'──c'   (commits rejoués)
```

**Squash and merge** est le choix le plus courant : `main` reste lisible, un commit par fonctionnalité, et les « wip » de la branche disparaissent. Le message du commit de squash reprend le titre de la PR, d'où l'importance de bien le rédiger.

Après fusion, supprime la branche (bouton *Delete branch*) puis, en local :

```bash
git switch main
git pull --rebase
git branch -d feature/filtre-niveau
```

## Les conflits dans une PR

Si ta branche modifie les mêmes lignes qu'une branche déjà fusionnée, GitHub affiche **« This branch has conflicts that must be resolved »**. Tu as deux options.

**Option 1 : en local (recommandée).**

```bash
git fetch origin
git rebase origin/main      # ou git merge origin/main
# résoudre les conflits, git add, git rebase --continue
git push --force-with-lease
```

**Option 2 : sur GitHub**, avec le bouton *Resolve conflicts*, pour les cas simples. L'éditeur affiche les marqueurs et tu choisis quoi garder.

Dans les deux cas, relance les tests après résolution : la PR doit redevenir verte.

## Atelier guidé : un cycle de PR complet

Compte une heure trente. Travaille avec un camarade (chacun relit l'autre) ou simule les deux rôles sur deux comptes.

1. Dans un dépôt de test, crée `.github/pull_request_template.md` avec les rubriques Contexte, Changements, Comment tester, Captures.
2. Crée une issue « Ajouter une section À propos » (note son numéro, par exemple 1).
3. Crée la branche `feature/a-propos`, ajoute un fichier `a-propos.md`, commite deux fois, pousse.
4. Ouvre une PR en **brouillon** avec un titre `feat(docs): ajoute la page à propos` et la ligne `Closes #1`.
5. Passe-la en « Ready for review » et demande une relecture à ton camarade.
6. Camarade : va dans *Files changed*, laisse un commentaire sur une ligne, propose une suggestion, puis choisis *Request changes*.
7. Auteur : applique la suggestion, corrige le reste, pousse, résous les conversations et redemande une relecture.
8. Camarade : approuve la PR.
9. Provoque un conflit en modifiant la même ligne sur `main` pendant ce temps, puis résous-le en local avec un rebase.
10. Fusionne avec **Squash and merge**. Vérifie que l'issue 1 est fermée et que la branche est supprimée.
11. Compare l'historique de `main` avec `git log --oneline` : un seul commit pour toute la fonctionnalité.

Pour t'auto-évaluer : explique la différence entre merge commit, squash et rebase, et dis lequel tu choisirais pour une branche contenant dix commits « wip ».

## Erreurs fréquentes

- **PR géante.** Impossible à relire sérieusement. Découpe en plusieurs PR successives.
- **Description vide.** Le relecteur devine le contexte ; il passe à côté des vrais problèmes.
- **Fusionner avec des checks rouges.** Un bug en production est souvent né d'un check ignoré.
- **Approuver sans lire.** Le « LGTM » (*looks good to me*) automatique n'aide personne.
- **Critiquer la personne au lieu du code.** Cela dégrade l'ambiance et la qualité des PR suivantes.
- **Pousser une correction sans répondre aux commentaires.** Le relecteur ne sait pas si son remarque a été prise en compte.
- **Oublier `Closes #n`.** L'issue reste ouverte alors que le travail est fait.
- **Utiliser `--force` après une relecture.** Les commentaires liés aux anciens commits peuvent être perdus ; préfère `--force-with-lease` et signale-le.

## Bonnes pratiques

- Fais des PR petites et centrées sur un seul sujet.
- Ouvre une PR en brouillon dès que possible pour avoir un retour précoce.
- Écris pour le relecteur : contexte, changements, procédure de test, captures.
- Relis ta PR toi-même avant de demander de l'aide.
- Réponds à chaque commentaire et résous les conversations.
- Utilise le squash pour garder un historique de `main` lisible.
- Exige des relectures et des checks verts via la protection de branche.
- Reste courtois : un commentaire de revue est un échange, pas un verdict.

## À retenir

- Une **pull request** propose des changements d'une branche à une autre, avec relecture, discussion et vérifications.
- Une bonne PR est petite, bien décrite (contexte, changements, test) et liée à une issue avec `Closes #n`.
- La relecture porte sur la correction, la lisibilité, les tests et la sécurité, avec un ton constructif.
- Trois choix de revue : *Comment*, *Approve*, *Request changes*.
- Trois modes de fusion : *merge commit*, *squash* et *rebase* ; le squash est le plus courant.
- Les conflits se résolvent de préférence en local, puis les tests sont relancés.
- Une PR dont les checks sont rouges ne se fusionne pas.
