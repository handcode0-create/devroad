---
title: Fichiers, modules et environnements virtuels
minutes: 120
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, tes programmes Python vivaient dans un seul fichier et oubliaient tout dès qu'ils se terminaient. Un vrai programme lit des données, les enregistre, se découpe en plusieurs fichiers et utilise des bibliothèques écrites par d'autres. Ce chapitre t'apprend à faire exactement cela, proprement, avec Python 3.12.

À la fin du chapitre, tu seras capable de :

- lire et écrire des fichiers texte avec `open` et le bloc `with` ;
- manipuler des chemins de façon portable avec `pathlib` ;
- sauvegarder et recharger des données au format **JSON** et **CSV** ;
- découper ton code en **modules** et en **paquets** et les importer ;
- créer et activer un **environnement virtuel** avec `venv` ;
- installer des dépendances avec `pip` et les figer dans un fichier `requirements.txt`.

Prérequis : avoir suivi les chapitres 1 à 3 (variables, fonctions, listes, dictionnaires). Prévois deux heures. Tu as seulement besoin de Python 3.12, d'un terminal et d'un éditeur de code.

## Lire et écrire un fichier texte

Le moyen standard d'ouvrir un fichier est la fonction `open`, utilisée avec un bloc `with`. Le bloc garantit que le fichier est **fermé automatiquement**, même si une erreur survient au milieu :

```python
with open("notes.txt", "w", encoding="utf-8") as fichier:
    fichier.write("Ligne 1 : bonjour Abidjan\n")
    fichier.write("Ligne 2 : apprendre Python\n")
```

Le deuxième argument est le **mode d'ouverture** :

| Mode | Signification |
| --- | --- |
| `"r"` | Lecture (par défaut). Le fichier doit exister |
| `"w"` | Écriture. Crée le fichier ou **efface** son contenu s'il existe |
| `"a"` | Ajout à la fin du fichier, sans rien effacer |
| `"x"` | Création exclusive : erreur si le fichier existe déjà |

Précise toujours `encoding="utf-8"`. Sans cela, Python utilise l'encodage par défaut du système, et tes accents (é, è, ô) peuvent être déformés sous Windows.

Pour relire le fichier, tu as plusieurs options :

```python
with open("notes.txt", encoding="utf-8") as fichier:
    contenu = fichier.read()          # tout le texte d'un coup

with open("notes.txt", encoding="utf-8") as fichier:
    for ligne in fichier:             # ligne par ligne, économe en mémoire
        print(ligne.strip())
```

Parcourir le fichier ligne par ligne est préférable pour les gros fichiers : Python ne charge pas tout en mémoire. La méthode `strip()` retire le retour à la ligne final.

> **Attention** : le mode `"w"` détruit sans avertissement le contenu existant. Si tu veux ajouter une ligne à un journal, utilise `"a"`.

:::quiz
Tu veux ajouter une ligne à la fin d'un fichier existant sans perdre son contenu. Quel mode choisis-tu ?
- [ ] `"r"`
- [ ] `"w"`
- [x] `"a"`
- [ ] `"x"`
> Le mode `"a"` (append) écrit à la fin du fichier. `"w"` effacerait tout, `"r"` ne permet que la lecture et `"x"` échoue si le fichier existe.
:::

## Les chemins avec pathlib

Écrire des chemins sous forme de chaînes (`"data/clients.csv"`) pose problème : Windows utilise `\`, Linux et macOS utilisent `/`. Le module `pathlib` règle cela avec un objet `Path` qui fonctionne partout :

```python
from pathlib import Path

dossier = Path("data")
dossier.mkdir(exist_ok=True)          # crée le dossier s'il n'existe pas

fichier = dossier / "clients.txt"     # l'opérateur / assemble les chemins
fichier.write_text("Kouassi\nAminata\n", encoding="utf-8")

print(fichier.read_text(encoding="utf-8"))
print(fichier.exists())               # True
print(fichier.suffix)                 # .txt
print(fichier.name)                   # clients.txt
print(fichier.parent)                 # data
```

Pour lister les fichiers d'un dossier, `glob` accepte un motif :

```python
for chemin in Path("data").glob("*.txt"):
    print(chemin.name)
```

Les méthodes `read_text` et `write_text` couvrent la majorité des cas simples en une seule ligne.

> **Astuce** : pour retrouver un fichier à côté de ton script, quel que soit le dossier depuis lequel on le lance, utilise `Path(__file__).parent / "data.json"`. Le chemin devient indépendant du dossier courant.

## Le format JSON

Un fichier texte brut est limité : comment stocker une liste de tâches avec un titre, un statut et une date ? Le format **JSON** représente des dictionnaires et des listes sous forme de texte. Il est lisible par un humain et compris par tous les langages, y compris JavaScript côté React.

```python
import json
from pathlib import Path

commandes = [
    {"id": 1, "client": "Aminata", "montant": 15000, "paye": True},
    {"id": 2, "client": "Yao", "montant": 8500, "paye": False},
]

chemin = Path("commandes.json")

# Écriture : Python -> JSON
chemin.write_text(
    json.dumps(commandes, indent=2, ensure_ascii=False),
    encoding="utf-8",
)

# Lecture : JSON -> Python
donnees = json.loads(chemin.read_text(encoding="utf-8"))
total = sum(c["montant"] for c in donnees)
print(f"Total : {total} FCFA")           # Total : 23500 FCFA
```

Les deux fonctions à retenir :

- `json.dumps(objet)` transforme un objet Python en **chaîne** JSON ;
- `json.loads(texte)` fait l'inverse.

L'option `indent=2` rend le fichier lisible, et `ensure_ascii=False` conserve les accents au lieu de les écrire en `é`. Les correspondances de types sont naturelles : dictionnaire vers objet, liste vers tableau, `True` vers `true`, `None` vers `null`.

Attention, JSON ne connaît pas les dates ni les ensembles. Stocke les dates sous forme de texte ISO (`"2026-10-09"`), que `datetime.date.fromisoformat` sait relire.

## Le format CSV

Le **CSV** est le format des tableurs. Chaque ligne est un enregistrement, les colonnes sont séparées par un point-virgule ou une virgule. Excel en version française exporte souvent avec le point-virgule. Le module `csv` gère les cas délicats (valeurs contenant le séparateur, guillemets) :

```python
import csv

with open("ventes.csv", "w", newline="", encoding="utf-8") as f:
    ecrivain = csv.DictWriter(f, fieldnames=["produit", "quantite", "prix"], delimiter=";")
    ecrivain.writeheader()
    ecrivain.writerow({"produit": "Attiéké", "quantite": 12, "prix": 500})
    ecrivain.writerow({"produit": "Alloco", "quantite": 30, "prix": 300})

with open("ventes.csv", newline="", encoding="utf-8") as f:
    for ligne in csv.DictReader(f, delimiter=";"):
        total = int(ligne["quantite"]) * int(ligne["prix"])
        print(f"{ligne['produit']} : {total} FCFA")
```

Deux détails importants : `newline=""` évite les lignes vides sous Windows, et tout ce qui est lu depuis un CSV est du **texte**. Il faut convertir avec `int(...)` ou `float(...)`.

:::quiz
Que renvoie `csv.DictReader` pour chaque ligne du fichier ?
- [ ] Une liste de nombres
- [ ] Un tuple sans noms de colonnes
- [x] Un dictionnaire dont les clés sont les noms de colonnes et les valeurs du texte
- [ ] Un objet DataFrame
> `DictReader` utilise la première ligne comme en-tête et produit un dictionnaire par enregistrement. Toutes les valeurs sont des chaînes qu'il faut convertir si besoin.
:::

## Découper son code en modules

Un **module** est simplement un fichier `.py`. Quand ton programme dépasse quelques centaines de lignes, tu le sépares par responsabilité. Imaginons un petit outil de gestion de stock :

```text
stock/
├── main.py
├── calculs.py
└── stockage.py
```

Le fichier `calculs.py` :

```python
def valeur_stock(articles: list[dict]) -> int:
    """Retourne la valeur totale du stock en FCFA."""
    return sum(a["quantite"] * a["prix"] for a in articles)


def articles_en_rupture(articles: list[dict], seuil: int = 5) -> list[dict]:
    return [a for a in articles if a["quantite"] <= seuil]
```

Dans `main.py`, tu importes ce dont tu as besoin :

```python
from calculs import valeur_stock, articles_en_rupture
import stockage

articles = stockage.charger()
print(valeur_stock(articles))
```

Il existe trois formes d'import courantes : `import module` (on écrit `module.fonction`), `from module import fonction` (on écrit `fonction` directement) et `import module as alias`. Évite l'import « étoile » (`from module import` suivi d'une étoile), qui pollue l'espace de noms et rend le code difficile à lire.

### Le point d'entrée du programme

Quand Python exécute un fichier directement, la variable `__name__` vaut `"__main__"`. Quand le fichier est importé, elle vaut le nom du module. Cela permet d'écrire un module qui est à la fois réutilisable et exécutable :

```python
def main() -> None:
    print("Démarrage de l'application")


if __name__ == "__main__":
    main()
```

Sans cette protection, le code serait exécuté à chaque `import`, ce qui est rarement voulu, et complique fortement les tests du chapitre 6.

### Les paquets

Un **paquet** est un dossier contenant des modules, avec traditionnellement un fichier `__init__.py` (qui peut rester vide) :

```text
gestion/
├── __init__.py
├── modeles.py
└── outils/
    ├── __init__.py
    └── formats.py
```

Tu importes alors avec des points : `from gestion.outils.formats import formater_fcfa`.

> **Erreur fréquente** : nommer son fichier comme un module de la bibliothèque standard, par exemple `json.py` ou `random.py`. Python importe alors ton fichier à la place du vrai module et plus rien ne fonctionne. Choisis toujours des noms distincts.

## Les environnements virtuels

Quand tu installes une bibliothèque avec `pip`, elle va par défaut dans l'installation globale de Python. Si le projet A a besoin de la version 1 d'une bibliothèque et le projet B de la version 2, c'est le conflit assuré. La solution : un **environnement virtuel**, une copie isolée de Python avec ses propres bibliothèques, propre à chaque projet.

Crée-le à la racine de ton projet :

```bash
python3.12 -m venv .venv
```

Active-le :

```bash
# Linux et macOS
source .venv/bin/activate

# Windows (PowerShell)
.venv\Scripts\Activate.ps1
```

Ton invite de commande affiche alors `(.venv)`. Désormais, `python` et `pip` pointent vers l'environnement isolé. Pour en sortir, tape `deactivate`.

Installe une bibliothèque et fige la liste :

```bash
pip install requests
pip freeze > requirements.txt
```

Un collègue (ou toi sur un autre ordinateur) recrée l'environnement identique avec :

```bash
python3.12 -m venv .venv
source .venv/bin/activate
pip install -r requirements.txt
```

Le dossier `.venv` ne se partage jamais : il est volumineux et spécifique à la machine. Ajoute-le à ton `.gitignore`, et versionne seulement `requirements.txt`.

:::quiz
Pourquoi utilise-t-on un environnement virtuel par projet ?
- [ ] Pour que le code s'exécute plus vite
- [x] Pour isoler les dépendances de chaque projet et éviter les conflits de versions
- [ ] Pour chiffrer le code source
- [ ] Parce que Python refuse de fonctionner sans
> Chaque environnement possède ses propres paquets installés. Les projets ne se perturbent plus entre eux et le projet reste reproductible.
:::

## Atelier guidé : un carnet de dépenses

Compte une heure et demie. Tu construis un petit carnet de dépenses en FCFA qui enregistre ses données en JSON.

1. Crée un dossier `carnet`, entre dedans, puis crée et active un environnement virtuel `.venv`.
2. Crée un fichier `.gitignore` contenant la ligne `.venv/`.
3. Installe `rich` avec `pip install rich`, puis exécute `pip freeze > requirements.txt`.
4. Crée un module `stockage.py` avec deux fonctions : `charger(chemin)` qui retourne la liste lue dans un fichier JSON (ou une liste vide si le fichier n'existe pas) et `sauvegarder(chemin, depenses)`.
5. Crée un module `calculs.py` avec `total(depenses)` et `total_par_categorie(depenses)` qui retourne un dictionnaire.
6. Dans `main.py`, utilise `Path(__file__).parent / "depenses.json"` comme chemin des données.
7. Demande à l'utilisateur, avec `input`, une description, un montant et une catégorie ; convertis le montant avec `int`, puis ajoute la dépense et sauvegarde.
8. Affiche le total par catégorie dans un tableau `rich.table.Table`.
9. Protège le lancement avec `if __name__ == "__main__":`.
10. Ajoute une fonction `exporter_csv(depenses, chemin)` qui crée un fichier lisible dans Excel.

Pour t'auto-évaluer : supprime `.venv`, recrée-le à partir de `requirements.txt` et vérifie que le programme repart sans modification. Puis explique avec tes mots la différence entre `json.dumps` et `json.dump`.

## Erreurs fréquentes

- **Oublier `encoding="utf-8"`.** Les accents s'affichent mal selon le système.
- **Ouvrir en mode `"w"` pour ajouter.** Le contenu précédent est perdu : utilise `"a"`.
- **Ne pas convertir les valeurs lues d'un CSV.** Multiplier la chaîne `"12"` par 500 répète du texte au lieu de calculer.
- **Nommer son module `json.py` ou `csv.py`.** Il masque le module standard.
- **Travailler sans environnement virtuel.** Les dépendances s'accumulent dans l'installation globale et deviennent ingérables.
- **Versionner le dossier `.venv`.** Seul `requirements.txt` doit être partagé.
- **Imports circulaires.** Si `a.py` importe `b.py` qui importe `a.py`, Python échoue. Sépare le code partagé dans un troisième module.

## Bonnes pratiques

- Toujours utiliser `with` pour ouvrir un fichier.
- Utiliser `pathlib` plutôt que des chaînes pour les chemins.
- Un module = une responsabilité (lecture de données, calculs, affichage).
- Protéger le point d'entrée avec `if __name__ == "__main__":`.
- Un environnement virtuel par projet, nommé `.venv`, ignoré par Git.
- Figer les dépendances dans `requirements.txt` et le mettre à jour quand tu en ajoutes.
- Écrire les données dans un format standard (JSON, CSV) pour pouvoir les réutiliser ailleurs.

## À retenir

- `with open(chemin, mode, encoding="utf-8")` ouvre et ferme proprement un fichier ; `"r"`, `"w"` et `"a"` sont les modes essentiels.
- `pathlib.Path` rend les chemins portables et offre `read_text`, `write_text`, `mkdir`, `glob`.
- `json` sauvegarde dictionnaires et listes ; `csv` échange avec les tableurs.
- Un module est un fichier `.py` ; un paquet est un dossier de modules.
- Un environnement virtuel (`python -m venv .venv`) isole les dépendances d'un projet.
- `pip freeze > requirements.txt` puis `pip install -r requirements.txt` rendent un projet reproductible.
