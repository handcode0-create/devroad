---
title: Premiers pas avec Python
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Python est l'un des langages les plus enseignés et les plus utilisés au monde : automatisation, analyse de données, intelligence artificielle, scripts système, back-end web. Sa syntaxe lisible en fait un excellent premier langage, et un outil quotidien pour les développeurs expérimentés. Ce chapitre pose les fondations avec Python 3.12 : installer l'outil, exécuter du code, manipuler des valeurs et afficher des résultats.

À la fin du chapitre, tu seras capable de :

- installer Python et vérifier sa version en ligne de commande ;
- utiliser l'interpréteur interactif (REPL) et exécuter un fichier `.py` ;
- déclarer des variables et reconnaître les types de base (`int`, `float`, `str`, `bool`, `None`) ;
- afficher et formater du texte avec `print` et les f-strings ;
- lire une saisie utilisateur avec `input` et convertir les types ;
- utiliser les opérateurs arithmétiques, de comparaison et logiques ;
- expliquer pourquoi l'indentation fait partie de la syntaxe.

Prérequis : savoir ouvrir un terminal et un éditeur de code (VS Code convient très bien). Aucune connaissance de Python n'est nécessaire. Prévois deux heures.

## Installer Python et vérifier la version

Ouvre un terminal et tape :

```bash
python3 --version
```

Tu dois voir une ligne comme `Python 3.12.x`. Sous Windows, la commande est souvent `python --version` ou `py --version`. Si Python n'est pas installé :

- Windows : télécharge l'installeur sur python.org et coche la case « Add python.exe to PATH » ;
- macOS : `brew install python@3.12` ;
- Ubuntu ou Debian : `sudo apt install python3 python3-venv python3-pip`.

Dans ce cours, on écrit `python3` dans les exemples. Si ton système utilise `python`, remplace simplement.

> **Astuce** : installe l'extension officielle « Python » dans VS Code. Elle apporte la coloration, l'autocomplétion et la possibilité de lancer un fichier d'un clic.

## L'interpréteur interactif : ton bac à sable

Tape `python3` sans argument dans le terminal. Tu entres dans le **REPL** (Read, Eval, Print, Loop), une console qui exécute chaque ligne tout de suite :

```python
>>> 2 + 3
5
>>> "Dev" + "Road"
'DevRoad'
>>> len("DevRoad")
7
```

Le REPL est parfait pour tester une idée en dix secondes. Pour en sortir, tape `exit()` ou utilise Ctrl+D (Ctrl+Z puis Entrée sous Windows).

Pour un vrai programme, on écrit le code dans un fichier. Crée un dossier `python-cours` et, dedans, un fichier `bonjour.py` :

```python
print("Bonjour DevRoad !")
```

Exécute-le :

```bash
python3 bonjour.py
```

Python lit le fichier de haut en bas, ligne par ligne. Il n'y a ni point-virgule à la fin des instructions, ni accolades : une instruction par ligne suffit.

## Variables et types de base

Une **variable** est un nom qui pointe vers une valeur. On la crée avec le signe `=`, sans mot-clé de déclaration :

```python
prenom = "Awa"
age = 24
note = 15.5
est_inscrite = True
adresse = None
```

Python devine le type de chaque valeur. Tu peux le vérifier avec `type` :

```python
print(type(prenom))        # <class 'str'>
print(type(age))           # <class 'int'>
print(type(note))          # <class 'float'>
print(type(est_inscrite))  # <class 'bool'>
print(type(adresse))       # <class 'NoneType'>
```

Voici les types à connaître dès maintenant :

| Type | Exemple | Usage |
| --- | --- | --- |
| `int` | `42` | nombres entiers, de taille illimitée |
| `float` | `3.14` | nombres à virgule |
| `str` | `"texte"` | chaînes de caractères |
| `bool` | `True`, `False` | vrai ou faux (majuscule obligatoire) |
| `None` | `None` | l'absence de valeur |

### Règles de nommage

- un nom contient des lettres, des chiffres et des underscores, et ne commence pas par un chiffre ;
- Python distingue les majuscules des minuscules : `Age` et `age` sont deux variables différentes ;
- la convention officielle (PEP 8) est le **snake_case** : `nombre_de_lecons`, pas `nombreDeLecons` ;
- une constante, valeur qui ne doit jamais changer, s'écrit en MAJUSCULES : `TVA = 0.18`. Python ne l'empêche pas de changer, c'est une convention entre développeurs.

Les variables ne sont pas liées à un type : tu peux réassigner `age = "vingt-quatre"` sans erreur. C'est souple, mais cela demande de la rigueur. Un bon nom aide : `nombre_lecons` laisse deviner un entier.

:::quiz
Quelle ligne crée correctement une variable booléenne en Python ?
- [ ] est_valide = true
- [x] est_valide = True
- [ ] bool est_valide = True
- [ ] $est_valide = True
> Les booléens Python s'écrivent True et False avec une majuscule. Il n'y a ni mot-clé de déclaration ni symbole dollar.
:::

## Afficher du texte avec print et les f-strings

La fonction `print` affiche une ou plusieurs valeurs séparées par un espace :

```python
print("Roadmap", "Python", 3)   # Roadmap Python 3
```

Pour insérer des variables dans un texte, la méthode moderne est la **f-string** : un guillemet précédé de la lettre `f`, avec des expressions entre accolades.

```python
prenom = "Awa"
lecons_terminees = 7
total_lecons = 20

print(f"Bonjour {prenom} !")
print(f"Progression : {lecons_terminees}/{total_lecons}")
print(f"Soit {lecons_terminees / total_lecons * 100:.1f} %")
```

Le dernier exemple affiche `35.0 %`. L'écriture `:.1f` signifie « un chiffre après la virgule ». Les f-strings acceptent n'importe quelle expression : calculs, appels de fonction, méthodes.

### Les chaînes de caractères

Une chaîne se délimite par des guillemets simples ou doubles, au choix. Trois guillemets permettent d'écrire sur plusieurs lignes :

```python
message = """Bienvenue sur DevRoad.
Choisis une roadmap pour commencer."""
```

Quelques opérations courantes :

```python
titre = "  roadmap python  "

print(titre.strip())          # 'roadmap python'
print(titre.strip().upper())  # 'ROADMAP PYTHON'
print(titre.strip().title())  # 'Roadmap Python'
print("python" in titre)      # True
print(len(titre.strip()))     # 14
print("a-b-c".split("-"))     # ['a', 'b', 'c']
print(" ".join(["a", "b"]))   # 'a b'
```

Les chaînes sont **immuables** : `titre.strip()` ne modifie pas `titre`, elle renvoie une nouvelle chaîne. Pour conserver le résultat, il faut l'assigner : `titre = titre.strip()`.

On accède à un caractère par son **index**, qui commence à 0, et on extrait un morceau avec une **tranche** (*slice*) :

```python
mot = "DevRoad"
print(mot[0])      # 'D'
print(mot[-1])     # 'd' (le dernier)
print(mot[0:3])    # 'Dev' (de 0 inclus à 3 exclu)
print(mot[3:])     # 'Road'
```

## Les opérateurs

### Arithmétiques

```python
print(7 + 2)    # 9
print(7 - 2)    # 5
print(7 * 2)    # 14
print(7 / 2)    # 3.5  (division réelle, toujours un float)
print(7 // 2)   # 3    (division entière)
print(7 % 2)    # 1    (reste de la division)
print(7 ** 2)   # 49   (puissance)
```

Attention à la différence entre `/` et `//` : en Python 3, `/` renvoie toujours un nombre à virgule, même quand le résultat est entier (`6 / 3` donne `2.0`).

Les opérateurs d'affectation composée raccourcissent le code : `compteur += 1` équivaut à `compteur = compteur + 1`. Idem avec `-=`, `*=`, `/=`.

### De comparaison

Ils renvoient un booléen : `==` (égal), `!=` (différent), `<`, `>`, `<=`, `>=`.

```python
print(3 == 3.0)       # True
print("a" == "A")     # False
print(1 < 5 < 10)     # True : les comparaisons s'enchaînent
```

Ne confonds jamais `=` (affectation) et `==` (comparaison).

### Logiques

Python utilise des mots, pas des symboles : `and`, `or`, `not`.

```python
age = 20
a_un_compte = True

print(age >= 18 and a_un_compte)   # True
print(age < 18 or not a_un_compte) # False
```

### Valeurs « vraies » et « fausses »

Dans une condition, Python considère comme faux : `False`, `None`, `0`, `0.0`, la chaîne vide `""`, et les collections vides. Tout le reste est vrai. Ainsi `bool("")` vaut `False` et `bool("texte")` vaut `True`. Tu t'en serviras très souvent au chapitre suivant.

:::quiz
Quel est le résultat de l'expression `17 // 5` ?
- [ ] 3.4
- [x] 3
- [ ] 2
- [ ] 4
> L'opérateur // est la division entière : 17 contient 3 fois 5 (le reste étant 2). L'opérateur / aurait donné 3.4.
:::

## Lire une saisie et convertir les types

La fonction `input` affiche une question et attend que l'utilisateur tape du texte puis valide :

```python
prenom = input("Ton prénom ? ")
print(f"Bonjour {prenom} !")
```

Point crucial : `input` renvoie **toujours une chaîne**, même si l'utilisateur tape un nombre. Pour calculer, il faut convertir :

```python
reponse = input("Combien de leçons as-tu terminées ? ")
terminees = int(reponse)
print(f"Dans 3 jours : {terminees + 3}")
```

Les fonctions de conversion sont `int()`, `float()`, `str()` et `bool()`. Si la conversion est impossible, par exemple `int("abc")`, Python lève une erreur `ValueError`. On apprendra à la gérer proprement au chapitre sur les erreurs.

> **Erreur fréquente** : additionner une saisie sans la convertir. `"5" + "3"` donne `"53"` (concaténation), pas `8`. Et `"5" + 3` provoque un `TypeError`.

## L'indentation fait partie de la syntaxe

Dans la plupart des langages, les accolades délimitent les blocs de code et l'indentation est un confort. En Python, **l'indentation est la syntaxe**. Un bloc commence après une ligne terminée par deux-points `:` et se compose de lignes décalées de quatre espaces :

```python
note = 14

if note >= 10:
    print("Admis")
    print("Félicitations")
print("Fin du programme")
```

Les deux premiers `print` appartiennent au bloc du `if`. Le dernier est aligné à gauche : il s'exécute toujours. Si tu décales mal une ligne, Python lève une `IndentationError`.

Règles à suivre : quatre espaces par niveau, jamais de mélange tabulations et espaces, et laisse ton éditeur gérer la touche Tab.

Un **commentaire** commence par `#` et s'arrête à la fin de la ligne. Il explique le *pourquoi*, pas le *quoi* :

```python
# La TVA ivoirienne standard est de 18 %
TVA = 0.18
```

## Lire un message d'erreur

Les erreurs font partie du quotidien. Voici un exemple :

```python
print(prenom)
```

```text
Traceback (most recent call last):
  File "bonjour.py", line 1, in <module>
    print(prenom)
NameError: name 'prenom' is not defined
```

Lis toujours **de bas en haut**. La dernière ligne donne le type d'erreur et la cause (`NameError` : la variable n'existe pas). Les lignes au-dessus indiquent le fichier et le numéro de ligne. Dans 90 % des cas, la dernière ligne suffit à comprendre.

:::quiz
Que se passe-t-il avec le code `age = input("Âge ? ")` suivi de `print(age + 1)` si l'utilisateur tape 20 ?
- [ ] Il affiche 21
- [ ] Il affiche 201
- [x] Python lève une TypeError, car age est une chaîne
- [ ] Il affiche None
> input renvoie toujours un str. Additionner une chaîne et un entier est impossible : il faut écrire int(age) + 1.
:::

## Atelier guidé : le calculateur de progression DevRoad

Compte une heure. Crée un fichier `progression.py` dans ton dossier `python-cours`.

1. Demande le prénom de l'apprenant avec `input` et stocke-le dans `prenom`.
2. Demande le nombre total de leçons de la roadmap, convertis-le en `int`.
3. Demande le nombre de leçons terminées, convertis-le aussi.
4. Calcule le pourcentage de progression : `terminees / total * 100`.
5. Affiche, avec une f-string, une phrase du type « Awa, tu as terminé 7 leçons sur 20 (35.0 %) » avec un seul chiffre après la virgule.
6. Calcule le nombre de leçons restantes et, en supposant une leçon par jour, affiche le nombre de semaines complètes avec `//` et les jours restants avec `%`.
7. Affiche un message différent selon que la progression dépasse 50 % ou non, grâce à un `if` / `else` (la syntaxe est détaillée au chapitre suivant, recopie l'exemple de ce chapitre).
8. Teste ton programme avec la valeur 0 comme total de leçons. Observe l'erreur `ZeroDivisionError`, lis le message de bas en haut et note ce qu'il faudrait faire pour l'éviter.

Pour t'auto-évaluer : sans regarder le cours, explique la différence entre `/`, `//` et `%`, puis dis pourquoi `input("5")` ne renvoie pas le nombre 5.

## Erreurs fréquentes

- **Oublier la majuscule de `True`, `False`, `None`.** `true` provoque une `NameError`.
- **Confondre `=` et `==`.** L'un affecte, l'autre compare.
- **Mélanger texte et nombres.** `"Score : " + 15` échoue ; utilise une f-string.
- **Oublier les deux-points** à la fin d'une ligne `if`, `for`, `def`. Cela donne une `SyntaxError`.
- **Mal indenter.** Un décalage d'un seul espace de trop casse le programme.
- **Croire qu'une méthode de chaîne la modifie.** Les chaînes sont immuables : assigne le résultat.
- **Nommer un fichier comme un module standard**, par exemple `random.py` ou `string.py`. Python importerait ton fichier à la place de celui de la bibliothèque.

## Bonnes pratiques

- Choisis des noms explicites en snake_case : `lecons_terminees` plutôt que `lt`.
- Préfère les f-strings à la concaténation avec `+`.
- Teste toute idée rapide dans le REPL avant de l'écrire dans un fichier.
- Garde quatre espaces par niveau d'indentation, sans exception.
- Lis les messages d'erreur de bas en haut avant de chercher sur internet.
- Commente le pourquoi d'une décision, pas ce que la ligne fait évidemment.

## À retenir

- Python est lisible, interprété, et s'exécute avec `python3 fichier.py` ou dans le REPL.
- Une variable se crée avec `=` ; les types de base sont `int`, `float`, `str`, `bool` et `None`.
- Les f-strings (`f"Bonjour {prenom}"`) sont la façon moderne de formater du texte.
- `/` donne un `float`, `//` une division entière, `%` le reste, et le double astérisque la puissance.
- `input` renvoie toujours une chaîne : convertis avec `int()` ou `float()`.
- L'indentation de quatre espaces délimite les blocs ; les deux-points les introduisent.
- Un message d'erreur se lit de bas en haut : le type et la cause sont sur la dernière ligne.
