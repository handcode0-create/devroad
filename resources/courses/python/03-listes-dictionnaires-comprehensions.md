---
title: Listes, dictionnaires et compréhensions
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Presque tous les programmes manipulent des **collections** de données : une liste de leçons, un dictionnaire décrivant une roadmap, un ensemble de tags. Python offre des structures natives très puissantes pour cela, et une syntaxe élégante, les **compréhensions**, pour les transformer en une ligne. Maîtriser ce chapitre change radicalement ta façon d'écrire du Python.

À la fin du chapitre, tu seras capable de :

- créer, modifier et parcourir des listes ;
- utiliser des tuples pour des données qui ne changent pas ;
- stocker et lire des paires clé-valeur dans un dictionnaire ;
- utiliser les ensembles (`set`) pour supprimer les doublons et comparer des groupes ;
- écrire des compréhensions de listes, de dictionnaires et d'ensembles ;
- trier, filtrer et agréger des données avec `sorted`, `sum`, `min`, `max` ;
- éviter le piège des copies superficielles.

Prérequis : le chapitre « Structures de contrôle et fonctions ». Prévois deux heures.

## Les listes : des séquences modifiables

Une **liste** est une suite ordonnée de valeurs, entre crochets. Elle peut contenir des types différents, même si on garde en général des éléments homogènes.

```python
roadmaps = ["Python", "PHP", "React"]

print(roadmaps[0])      # 'Python'
print(roadmaps[-1])     # 'React'
print(len(roadmaps))    # 3
print(roadmaps[1:])     # ['PHP', 'React']
```

Les listes sont **modifiables** (*mutables*), contrairement aux chaînes :

```python
roadmaps.append("Docker")        # ajoute à la fin
roadmaps.insert(0, "Git")        # insère à une position
roadmaps.remove("PHP")           # supprime par valeur
dernier = roadmaps.pop()         # retire et renvoie le dernier
roadmaps[0] = "Git avancé"       # remplace un élément
print(roadmaps)
```

Quelques outils indispensables :

```python
notes = [12, 8, 15, 9, 18]

print(sum(notes))               # 62
print(min(notes), max(notes))   # 8 18
print(sorted(notes))            # [8, 9, 12, 15, 18] (nouvelle liste)
print(sorted(notes, reverse=True))
print(15 in notes)              # True
print(notes.index(15))          # 2
notes.sort()                    # trie la liste elle-même
```

Retiens la nuance : `sorted(liste)` renvoie une **nouvelle** liste triée, alors que `liste.sort()` modifie la liste existante et renvoie `None`. Écrire `notes = notes.sort()` est une erreur classique qui remplace ta liste par `None`.

### Parcourir et modifier

On parcourt une liste avec `for`, comme vu précédemment. Évite de modifier une liste pendant que tu la parcours : construis plutôt une nouvelle liste.

```python
lecons = ["Variables", "Boucles", "Fonctions"]
majuscules = []
for titre in lecons:
    majuscules.append(titre.upper())
print(majuscules)
```

Ce schéma « créer une liste vide, boucler, ajouter » est tellement courant que Python propose un raccourci, vu plus bas.

## Les tuples : des séquences figées

Un **tuple** ressemble à une liste mais ne peut pas être modifié après sa création. On l'écrit avec des parenthèses :

```python
point = (48.85, 2.35)
lecon = ("Variables", 30, "beginner")

titre, minutes, niveau = lecon      # déballage
print(titre, minutes, niveau)
```

Utilise un tuple pour regrouper quelques valeurs qui forment un tout (coordonnées, couple titre-durée) ou pour renvoyer plusieurs valeurs d'une fonction. Comme il est immuable, il peut servir de clé de dictionnaire, ce qui n'est pas le cas d'une liste.

Un tuple d'un seul élément exige une virgule : `(5,)`. Sans elle, `(5)` est simplement le nombre 5.

:::quiz
Quelle est la différence essentielle entre une liste et un tuple ?
- [ ] Le tuple est plus rapide à écrire
- [ ] La liste ne peut contenir que des nombres
- [x] La liste est modifiable, le tuple ne l'est pas
- [ ] Le tuple ne peut pas être parcouru par une boucle for
> Les listes sont mutables (append, remove, affectation par index), les tuples sont immuables. Les deux se parcourent de la même façon.
:::

## Les dictionnaires : des paires clé-valeur

Un **dictionnaire** associe des clés à des valeurs. On y accède par la clé, pas par une position :

```python
roadmap = {
    "titre": "Python",
    "niveau": "beginner",
    "lecons": 7,
    "publie": True,
}

print(roadmap["titre"])          # 'Python'
roadmap["lecons"] = 8            # modifier
roadmap["auteur"] = "Smith"      # ajouter
del roadmap["publie"]            # supprimer
print(len(roadmap))              # 4
```

Lire une clé inexistante avec les crochets lève une `KeyError`. La méthode `get` est plus tolérante : elle renvoie `None`, ou une valeur par défaut que tu choisis.

```python
print(roadmap.get("duree"))          # None
print(roadmap.get("duree", 0))       # 0
print("niveau" in roadmap)           # True (teste les clés)
```

### Parcourir un dictionnaire

```python
for cle in roadmap:
    print(cle)

for valeur in roadmap.values():
    print(valeur)

for cle, valeur in roadmap.items():
    print(f"{cle} = {valeur}")
```

Depuis Python 3.7, un dictionnaire conserve l'**ordre d'insertion** des clés. La méthode `items()` est celle que tu utiliseras le plus.

### Dictionnaires imbriqués et listes de dictionnaires

Les vraies données sont souvent des structures combinées. Voici deux roadmaps de DevRoad sous forme de liste de dictionnaires :

```python
roadmaps = [
    {"slug": "python", "titre": "Python", "lecons": 7, "niveau": "beginner"},
    {"slug": "react", "titre": "React", "lecons": 6, "niveau": "intermediate"},
    {"slug": "php", "titre": "PHP", "lecons": 5, "niveau": "beginner"},
]

for r in roadmaps:
    print(f"{r['titre']} : {r['lecons']} leçons")
```

Remarque les apostrophes dans `r['titre']` : à l'intérieur d'une f-string délimitée par des guillemets doubles, on utilise des guillemets simples pour les clés.

Cette forme (liste de dictionnaires) est exactement ce que renvoie une API JSON. Tu la retrouveras en lisant des fichiers au chapitre suivant.

### Compter et regrouper

Deux motifs reviennent sans cesse. Le premier compte des occurrences :

```python
niveaux = ["beginner", "beginner", "intermediate", "beginner"]
compte = {}
for niveau in niveaux:
    compte[niveau] = compte.get(niveau, 0) + 1
print(compte)   # {'beginner': 3, 'intermediate': 1}
```

Le module standard `collections` fournit des outils dédiés, dont `Counter` et `defaultdict` :

```python
from collections import Counter, defaultdict

print(Counter(niveaux))              # Counter({'beginner': 3, 'intermediate': 1})

par_niveau = defaultdict(list)
for r in roadmaps:
    par_niveau[r["niveau"]].append(r["titre"])
print(dict(par_niveau))
# {'beginner': ['Python', 'PHP'], 'intermediate': ['React']}
```

## Les ensembles : unicité et opérations

Un **ensemble** (`set`) est une collection **sans doublon** et sans ordre garanti. C'est l'outil idéal pour dédupliquer et comparer :

```python
tags_a = {"python", "backend", "scripts"}
tags_b = {"python", "web", "backend"}

print(tags_a & tags_b)   # intersection : {'python', 'backend'}
print(tags_a | tags_b)   # union
print(tags_a - tags_b)   # {'scripts'}
print(set([1, 2, 2, 3, 3]))   # {1, 2, 3}
```

Attention : `{}` crée un dictionnaire vide, pas un ensemble. Pour un ensemble vide, écris `set()`.

Le test d'appartenance (`in`) est beaucoup plus rapide dans un ensemble que dans une liste quand les données sont nombreuses.

## Les compréhensions

Une **compréhension de liste** construit une liste en une expression. La forme est : `[expression for element in sequence if condition]`.

```python
nombres = [1, 2, 3, 4, 5, 6]

carres = [n * n for n in nombres]
pairs = [n for n in nombres if n % 2 == 0]
carres_pairs = [n * n for n in nombres if n % 2 == 0]

print(carres)         # [1, 4, 9, 16, 25, 36]
print(pairs)          # [2, 4, 6]
print(carres_pairs)   # [4, 16, 36]
```

C'est l'équivalent compact du motif « liste vide, boucle, append » vu plus haut. Avec nos roadmaps :

```python
titres = [r["titre"] for r in roadmaps]
debutantes = [r["titre"] for r in roadmaps if r["niveau"] == "beginner"]
total_lecons = sum(r["lecons"] for r in roadmaps)
```

Remarque la dernière ligne : `sum` reçoit une **expression génératrice** (sans crochets), qui calcule les valeurs au fur et à mesure sans créer de liste intermédiaire.

### Compréhensions de dictionnaires et d'ensembles

La même idée fonctionne avec des accolades :

```python
lecons_par_slug = {r["slug"]: r["lecons"] for r in roadmaps}
print(lecons_par_slug)   # {'python': 7, 'react': 6, 'php': 5}

niveaux_uniques = {r["niveau"] for r in roadmaps}
print(niveaux_uniques)   # {'beginner', 'intermediate'}
```

Inverser un dictionnaire devient trivial : `{v: k for k, v in d.items()}`.

> **Astuce** : une compréhension doit rester lisible d'un coup d'œil. Au-delà d'une condition et d'une transformation simples, ou si elle déborde d'une ligne, une boucle `for` classique est meilleure. La lisibilité prime sur la concision.

:::quiz
Que contient `resultat` après `resultat = [n for n in range(10) if n % 3 == 0]` ?
- [ ] [3, 6, 9]
- [x] [0, 3, 6, 9]
- [ ] [0, 3, 6]
- [ ] [1, 4, 7]
> range(10) va de 0 à 9 et 0 est divisible par 3. On obtient donc 0, 3, 6 et 9.
:::

## Trier avec une clé

`sorted` accepte un paramètre `key` : une fonction qui indique sur quoi trier. Une petite fonction `lambda` (fonction anonyme d'une ligne) suffit souvent :

```python
par_lecons = sorted(roadmaps, key=lambda r: r["lecons"], reverse=True)
print([r["titre"] for r in par_lecons])   # ['Python', 'React', 'PHP']

plus_courte = min(roadmaps, key=lambda r: r["lecons"])
print(plus_courte["titre"])               # 'PHP'
```

Pour trier sur plusieurs critères, renvoie un tuple : `key=lambda r: (r["niveau"], r["titre"])`.

## Copier une collection : le piège de la référence

Quand tu écris `b = a` avec une liste, tu ne copies pas la liste : tu crées un **second nom pour le même objet**.

```python
a = [1, 2, 3]
b = a
b.append(4)
print(a)   # [1, 2, 3, 4] : a a aussi changé !
```

Pour une vraie copie, utilise `a.copy()`, `list(a)` ou `a[:]`. Mais ces copies sont **superficielles** : si la liste contient d'autres listes ou dictionnaires, ceux-ci restent partagés. Pour tout dupliquer en profondeur :

```python
import copy

c = copy.deepcopy(roadmaps)
```

:::quiz
Que fait `notes = notes.sort()` si notes est une liste de nombres ?
- [ ] Trie la liste et la réassigne correctement
- [x] Trie la liste en place, puis remplace la variable par None
- [ ] Renvoie une nouvelle liste triée
- [ ] Lève une SyntaxError
> La méthode sort modifie la liste en place et renvoie None. L'assigner à la variable détruit donc la liste. Utilise sorted pour obtenir une nouvelle liste.
:::

## Atelier guidé : le catalogue DevRoad en mémoire

Compte une heure. Crée un fichier `catalogue.py`.

1. Définis une liste de cinq dictionnaires représentant des leçons, avec les clés `titre`, `minutes`, `niveau` et `terminee` (booléen).
2. Affiche les titres de toutes les leçons avec une compréhension de liste.
3. Calcule la durée totale avec `sum` et une expression génératrice.
4. Construis la liste des leçons non terminées, puis le pourcentage de progression.
5. Crée un dictionnaire `titre -> minutes` avec une compréhension de dictionnaire.
6. Trie les leçons de la plus longue à la plus courte avec `sorted` et `key`.
7. Avec `Counter`, compte le nombre de leçons par niveau.
8. Utilise un ensemble pour obtenir la liste des niveaux distincts, triée par ordre alphabétique.
9. Écris une fonction `prochaine_lecon(lecons)` qui renvoie la première leçon non terminée, ou `None` s'il n'y en a plus.
10. Prouve avec `copy.deepcopy` qu'une copie profonde ne modifie pas l'original quand tu coches une leçon.

Pour t'auto-évaluer : sans regarder, réécris `[r["titre"] for r in roadmaps if r["lecons"] > 5]` sous forme de boucle `for`, puis explique pourquoi la version compréhension est préférable ici.

## Erreurs fréquentes

- **Accéder à un index inexistant** : `IndexError: list index out of range`. Vérifie `len` ou utilise une boucle.
- **Lire une clé absente** : `KeyError`. Utilise `get` ou le test `in`.
- **Écrire `liste = liste.sort()`** : la variable devient `None`.
- **Croire que `b = a` copie** : les deux noms désignent la même liste.
- **Écrire `{}` pour un ensemble vide** : c'est un dictionnaire.
- **Modifier une liste en la parcourant** : les éléments sautent. Construis une nouvelle liste.
- **Compréhensions illisibles** avec plusieurs boucles et conditions imbriquées.
- **Oublier la virgule** d'un tuple à un seul élément.

## Bonnes pratiques

- Choisis la bonne structure : liste pour l'ordre, dictionnaire pour la recherche par clé, ensemble pour l'unicité, tuple pour un groupe figé.
- Utilise `get` avec une valeur par défaut pour les clés optionnelles.
- Préfère `enumerate`, `zip` et `items()` aux compteurs manuels.
- Utilise les compréhensions pour des transformations simples, et une boucle sinon.
- Nomme les collections au pluriel (`lecons`) et leurs éléments au singulier (`lecon`).
- Ne modifie pas une collection reçue en paramètre sans le documenter : renvoie plutôt une nouvelle valeur.

## À retenir

- Une liste est ordonnée et modifiable ; un tuple est ordonné et immuable.
- Un dictionnaire associe clés et valeurs ; `get`, `items()` et `in` sont tes outils de base.
- Un ensemble garantit l'unicité et offre l'intersection, l'union et la différence.
- Les compréhensions (liste, dictionnaire, ensemble) transforment et filtrent en une expression lisible.
- `sorted`, `min` et `max` acceptent `key` ; `sorted` renvoie une nouvelle liste, `sort` modifie en place.
- Une affectation ne copie pas : utilise `copy` ou `deepcopy` selon la profondeur voulue.
- Une liste de dictionnaires est la forme naturelle des données JSON que tu manipuleras bientôt.
