---
title: Structures de contrôle et fonctions
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Un programme qui exécute toujours les mêmes lignes dans le même ordre est peu utile. Pour réagir aux données, il doit pouvoir **décider** (conditions), **répéter** (boucles) et **s'organiser** en blocs réutilisables (fonctions). Ce chapitre couvre ces trois piliers, qui reviennent dans absolument tous les programmes Python.

À la fin du chapitre, tu seras capable de :

- écrire des conditions avec `if`, `elif`, `else` et le `match` de Python 3.10+ ;
- parcourir des séquences avec `for` et `range`, et répéter avec `while` ;
- contrôler une boucle avec `break`, `continue` et `else` ;
- définir une fonction avec paramètres, valeurs par défaut et valeur de retour ;
- comprendre la portée des variables (locale ou globale) ;
- documenter une fonction avec une docstring et des annotations de type.

Prérequis : le chapitre « Premiers pas avec Python » (variables, types, f-strings, indentation). Prévois deux heures.

## Prendre des décisions avec if, elif, else

La structure conditionnelle exécute un bloc seulement si une condition est vraie :

```python
progression = 65

if progression == 100:
    statut = "Terminé"
elif progression >= 50:
    statut = "Bien avancé"
elif progression > 0:
    statut = "Commencé"
else:
    statut = "Pas commencé"

print(statut)   # Bien avancé
```

Python teste les conditions **dans l'ordre** et exécute le premier bloc qui correspond, puis saute le reste. D'où l'importance de l'ordre : si tu avais placé `progression > 0` en premier, il aurait capté tous les cas.

Rappel : deux-points en fin de ligne, quatre espaces d'indentation. Tu peux combiner les conditions avec `and`, `or`, `not` et utiliser les comparaisons enchaînées :

```python
age = 17
if 13 <= age < 18 and not est_banni:
    print("Compte adolescent")
```

(Pour que cet exemple tourne, il faut bien sûr définir `est_banni` au préalable.)

### Exploiter les valeurs vraies et fausses

Au lieu de comparer explicitement à zéro ou à la chaîne vide, profite du fait que ces valeurs sont « fausses » :

```python
nom = ""
if not nom:
    print("Le nom est obligatoire")

lecons = []
if lecons:
    print("Il y a des leçons")
else:
    print("Aucune leçon pour l'instant")
```

C'est plus court et c'est le style attendu en Python.

### L'expression conditionnelle

Pour choisir entre deux valeurs en une ligne :

```python
label = "Terminé" if progression == 100 else "En cours"
```

À réserver aux cas simples. Dès que la ligne devient difficile à lire, reviens à un `if` classique.

### Le match

Depuis Python 3.10, `match` permet de comparer une valeur à plusieurs motifs, un peu comme le `switch` d'autres langages mais en plus puissant :

```python
def libelle_niveau(niveau):
    match niveau:
        case "beginner":
            return "Débutant"
        case "intermediate":
            return "Intermédiaire"
        case "professional":
            return "Professionnel"
        case _:
            return "Inconnu"
```

Le motif `_` joue le rôle de cas par défaut. `match` brille surtout quand on décompose des structures (tuples, dictionnaires), mais pour l'instant retiens son usage simple.

:::quiz
Dans ce code, que s'affiche-t-il ? `note = 12` puis `if note >= 10: print("A")`, `elif note >= 12: print("B")`, `else: print("C")`.
- [x] A
- [ ] B
- [ ] A puis B
- [ ] C
> Les conditions sont testées dans l'ordre et seul le premier bloc vrai s'exécute. Comme 12 est supérieur ou égal à 10, le elif n'est jamais évalué.
:::

## Répéter avec for

La boucle `for` parcourt les éléments d'une séquence : liste, chaîne, tuple, etc. (les listes sont détaillées au chapitre suivant, une introduction suffit ici).

```python
technos = ["Python", "Laravel", "React"]

for techno in technos:
    print(f"J'apprends {techno}")
```

Pour répéter un nombre précis de fois, on utilise `range` :

```python
for i in range(5):          # 0, 1, 2, 3, 4
    print(i)

for i in range(1, 6):       # 1, 2, 3, 4, 5
    print(i)

for i in range(0, 10, 2):   # 0, 2, 4, 6, 8
    print(i)
```

`range(debut, fin, pas)` ne produit jamais la valeur `fin` : la borne haute est exclue. C'est la même logique que les tranches de chaînes.

Quand tu as besoin de l'index en plus de la valeur, utilise `enumerate` plutôt que de gérer un compteur à la main :

```python
for numero, techno in enumerate(technos, start=1):
    print(f"{numero}. {techno}")
```

Et pour parcourir deux séquences en parallèle, `zip` :

```python
titres = ["Bases", "Fonctions", "POO"]
durees = [90, 120, 150]

for titre, duree in zip(titres, durees):
    print(f"{titre} : {duree} min")
```

## Répéter tant que : while

La boucle `while` continue tant que sa condition est vraie. On l'emploie quand on ne sait pas à l'avance combien de tours il faudra :

```python
tentatives = 0
mot_de_passe = ""

while mot_de_passe != "devroad" and tentatives < 3:
    mot_de_passe = input("Mot de passe : ")
    tentatives += 1

if mot_de_passe == "devroad":
    print("Bienvenue")
else:
    print("Compte bloqué")
```

Le danger numéro un est la **boucle infinie** : si la condition ne devient jamais fausse, le programme tourne sans fin. Vérifie toujours que quelque chose change à chaque tour (ici, `tentatives`). En cas de blocage dans le terminal, Ctrl+C interrompt le programme.

### break, continue et else

- `break` sort immédiatement de la boucle ;
- `continue` saute au tour suivant ;
- un bloc `else` placé après la boucle s'exécute seulement si la boucle s'est terminée **sans** `break`.

```python
nombres = [4, 8, 15, 16, 23, 42]

for n in nombres:
    if n % 2 == 1:
        print(f"Premier impair trouvé : {n}")
        break
else:
    print("Aucun nombre impair")
```

Le `else` de boucle est peu connu mais pratique pour les recherches : il traite le cas « rien trouvé ».

## Les fonctions : donner un nom à un bloc de code

Dès que tu copies-colles un morceau de code, c'est le signe qu'il faut une **fonction**. Elle se définit avec `def` :

```python
def saluer(prenom):
    print(f"Bonjour {prenom} !")

saluer("Awa")
saluer("Kofi")
```

Le mot « prenom » est un **paramètre** (le nom dans la définition). Quand on appelle `saluer("Awa")`, la valeur `"Awa"` est un **argument**.

### Retourner une valeur

`print` affiche, mais n'est pas utilisable ailleurs. Pour **produire** un résultat réutilisable, une fonction utilise `return` :

```python
def calculer_progression(terminees, total):
    return terminees / total * 100

pourcentage = calculer_progression(7, 20)
print(f"{pourcentage:.0f} %")   # 35 %
```

Dès qu'un `return` s'exécute, la fonction s'arrête. Une fonction sans `return` renvoie `None`.

> **Erreur fréquente** : écrire `print` dans une fonction qui devrait `return`. Tu ne pourras alors pas réutiliser le résultat dans un calcul ou un test. Règle simple : les fonctions calculent et renvoient, c'est le code appelant qui affiche.

### Valeurs par défaut et arguments nommés

Un paramètre peut avoir une valeur par défaut. À l'appel, on peut aussi nommer les arguments, ce qui rend le code plus clair et indépendant de l'ordre :

```python
def titre_lecon(nom, minutes=60, niveau="beginner"):
    return f"{nom} ({minutes} min, {niveau})"

print(titre_lecon("Variables"))
print(titre_lecon("Boucles", 90))
print(titre_lecon("Fonctions", niveau="intermediate", minutes=120))
```

Les paramètres avec valeur par défaut se placent **après** ceux qui n'en ont pas.

### Retourner plusieurs valeurs

Une fonction peut renvoyer plusieurs valeurs, qui forment en réalité un tuple. On les récupère par déballage :

```python
def stats(notes):
    return min(notes), max(notes), sum(notes) / len(notes)

mini, maxi, moyenne = stats([12, 15, 9, 18])
print(mini, maxi, moyenne)   # 9 18 13.5
```

### Docstrings et annotations de type

Une **docstring** est une chaîne placée juste sous la ligne `def`. Elle décrit la fonction. Les **annotations de type** précisent les types attendus ; Python ne les vérifie pas à l'exécution, mais l'éditeur et les outils d'analyse s'en servent.

```python
def calculer_progression(terminees: int, total: int) -> float:
    """Renvoie le pourcentage de leçons terminées (entre 0 et 100)."""
    if total == 0:
        return 0.0
    return terminees / total * 100
```

Remarque la garde `if total == 0` : elle règle le problème de division par zéro rencontré au chapitre précédent.

:::quiz
Que renvoie une fonction Python qui n'exécute aucune instruction return ?
- [ ] 0
- [ ] Une chaîne vide
- [x] None
- [ ] Une erreur est levée
> Sans return, la fonction se termine normalement et renvoie None. C'est une cause classique de bugs quand on oublie le return.
:::

## La portée des variables

Une variable créée dans une fonction est **locale** : elle n'existe que pendant l'appel.

```python
def compter():
    total = 10
    return total

compter()
print(total)   # NameError : total n'existe pas ici
```

Une variable définie en dehors des fonctions est **globale**, et une fonction peut la lire. Mais pour la modifier, il faudrait le mot-clé `global`, ce qu'il vaut mieux éviter. La bonne approche : passer les données en paramètres et récupérer le résultat avec `return`.

```python
# À éviter
compteur = 0
def incrementer():
    global compteur
    compteur += 1

# Préférable
def incrementer(compteur):
    return compteur + 1

compteur = incrementer(compteur)
```

Une fonction qui ne dépend que de ses paramètres est prévisible et facile à tester, ce que tu feras au chapitre sur la qualité.

### Le piège de la valeur par défaut mutable

Ne mets jamais une liste ou un dictionnaire vide comme valeur par défaut :

```python
def ajouter(element, liste=[]):   # piège !
    liste.append(element)
    return liste

print(ajouter(1))   # [1]
print(ajouter(2))   # [1, 2] et non [2]
```

La liste par défaut est créée **une seule fois**, à la définition, puis partagée entre tous les appels. La solution consiste à utiliser `None` :

```python
def ajouter(element, liste=None):
    if liste is None:
        liste = []
    liste.append(element)
    return liste
```

:::quiz
Quelle boucle affiche les nombres de 1 à 5 inclus ?
- [ ] for i in range(5)
- [ ] for i in range(1, 5)
- [x] for i in range(1, 6)
- [ ] for i in range(0, 5)
> La borne de fin de range est exclue. Pour atteindre 5, il faut donc écrire range(1, 6).
:::

## Atelier guidé : un mini-moteur de progression

Compte une heure. Crée un fichier `moteur.py`.

1. Écris une fonction `calculer_progression(terminees, total)` avec annotations, docstring et garde contre la division par zéro.
2. Écris une fonction `statut(pourcentage)` qui renvoie « Pas commencé », « Commencé », « Bien avancé » ou « Terminé » grâce à `if` / `elif` / `else`.
3. Écris une fonction `niveau_label(niveau)` basée sur `match`, comme dans le cours.
4. Crée deux listes parallèles : les titres de cinq leçons et leur durée en minutes.
5. Avec `zip` et `enumerate`, affiche un sommaire numéroté : « 1. Variables (30 min) ».
6. Écris une fonction `duree_totale(durees)` avec une boucle `for` et un accumulateur, sans utiliser `sum`, puis compare ton résultat avec `sum`.
7. Avec un `while`, demande à l'utilisateur combien de leçons il a terminées jusqu'à obtenir un entier valide entre 0 et 5 (utilise `isdigit()` sur la saisie).
8. Affiche enfin le pourcentage et le statut obtenus avec tes fonctions.

Pour t'auto-évaluer : peux-tu expliquer la différence entre `break` et `continue`, et pourquoi une fonction devrait plutôt renvoyer que afficher ?

## Erreurs fréquentes

- **Oublier les deux-points** après `if`, `else`, `for`, `while` ou `def`.
- **Confondre `range(5)` avec 1 à 5.** Il va de 0 à 4.
- **Écrire une boucle `while` sans rien changer** dans la condition : boucle infinie.
- **Appeler une fonction sans parenthèses.** `saluer` désigne la fonction, `saluer()` l'exécute.
- **Utiliser `print` à la place de `return`.**
- **Définir une valeur par défaut mutable** (`[]` ou `{}`).
- **Modifier une variable globale** depuis une fonction au lieu de renvoyer une valeur.
- **Mettre les paramètres avec défaut avant ceux sans défaut** : erreur de syntaxe.

## Bonnes pratiques

- Une fonction fait une seule chose et porte un nom de verbe : `calculer_progression`, `charger_roadmap`.
- Garde les fonctions courtes, une vingtaine de lignes au maximum.
- Ajoute une docstring et des annotations de type dès le début.
- Utilise des gardes (retours anticipés) pour traiter d'abord les cas d'erreur et éviter les `if` imbriqués.
- Préfère `for` à `while` dès que tu parcours une collection.
- Évite `global` : passe des paramètres, renvoie des valeurs.

## À retenir

- `if`, `elif`, `else` choisissent le premier bloc vrai ; `match` compare à des motifs, `_` étant le cas par défaut.
- `for` parcourt une séquence, `range` génère des nombres (borne haute exclue), `enumerate` et `zip` simplifient les cas courants.
- `while` répète tant qu'une condition est vraie : attention aux boucles infinies.
- `break` sort, `continue` passe au tour suivant, `else` de boucle s'exécute sans `break`.
- Une fonction se définit avec `def`, renvoie une valeur avec `return` (sinon `None`), accepte des valeurs par défaut et des arguments nommés.
- Les variables d'une fonction sont locales ; évite `global` et les valeurs par défaut mutables.
- Docstrings et annotations de type rendent le code lisible et vérifiable par les outils.
