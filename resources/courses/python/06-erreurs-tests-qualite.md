---
title: Erreurs, tests et qualité
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Un programme qui marche sur ton ordinateur avec tes données ne prouve pas grand-chose. Le vrai code de production doit survivre aux fichiers manquants, aux saisies absurdes et aux modifications futures. Ce chapitre t'apprend trois réflexes de professionnel : **gérer les erreurs** proprement, **tester** automatiquement avec pytest, et **maintenir la qualité** du code grâce à des outils.

À la fin du chapitre, tu seras capable de :

- lire une **trace d'erreur** (*traceback*) et en déduire la cause ;
- intercepter des erreurs avec `try`, `except`, `else` et `finally` ;
- lever tes propres exceptions et créer des **exceptions personnalisées** ;
- écrire des tests avec **pytest** : assertions, `fixtures`, paramétrage, `raises` ;
- mesurer la couverture et appliquer un linter et un formateur (ruff) ;
- journaliser avec le module `logging` au lieu de `print`.

Prérequis : les chapitres 1 à 5, en particulier les fonctions, les classes et l'environnement virtuel. Prévois deux heures trente. Tout le travail se fait dans un dossier avec un environnement virtuel actif (`python3.12 -m venv .venv`).

## Lire une trace d'erreur

Quand une exception n'est pas interceptée, Python affiche une trace :

```text
Traceback (most recent call last):
  File "caisse.py", line 12, in <module>
    print(moyenne([]))
  File "caisse.py", line 5, in moyenne
    return sum(valeurs) / len(valeurs)
ZeroDivisionError: division by zero
```

Lis-la **de bas en haut** : la dernière ligne donne le type d'erreur et son message, les lignes au-dessus montrent la chaîne d'appels qui y a mené. Ici, `moyenne` a été appelée avec une liste vide, ce qui divise par zéro à la ligne 5. Le vrai bug est peut-être dans l'appelant, pas dans la ligne signalée.

Les exceptions les plus courantes :

| Exception | Cause typique |
| --- | --- |
| `ValueError` | Bon type de valeur, mauvais contenu : `int("abc")` |
| `TypeError` | Mauvais type : `"5" + 3` |
| `KeyError` | Clé absente d'un dictionnaire |
| `IndexError` | Indice hors d'une liste |
| `FileNotFoundError` | Fichier inexistant |
| `AttributeError` | Attribut ou méthode inexistant sur l'objet |
| `ZeroDivisionError` | Division par zéro |

## Intercepter les exceptions

La structure complète est la suivante :

```python
import json


def charger_config(chemin: str) -> dict:
    try:
        with open(chemin, encoding="utf-8") as f:
            config = json.load(f)
    except FileNotFoundError:
        print("Fichier absent, configuration par défaut utilisée.")
        return {}
    except json.JSONDecodeError as erreur:
        print(f"JSON invalide à la ligne {erreur.lineno}")
        return {}
    else:
        print("Configuration chargée.")
        return config
    finally:
        print("Tentative de chargement terminée.")
```

Chaque bloc a un rôle précis :

- `try` contient le code risqué, et **seulement** lui ;
- `except Type` intercepte un type d'erreur précis ; on peut en enchaîner plusieurs ;
- `else` s'exécute si **aucune** exception n'est survenue ;
- `finally` s'exécute **toujours**, utile pour libérer une ressource (même si dans la plupart des cas, `with` fait ce travail pour toi).

> **Erreur fréquente** : écrire `except:` tout seul ou `except Exception: pass`. Tu avales silencieusement toutes les erreurs, y compris celles que tu n'avais pas prévues, et le bug devient introuvable. Intercepte le type le plus précis possible, et fais toujours quelque chose d'utile : corriger, journaliser ou relancer.

### Lever ses propres exceptions

Le mot-clé `raise` signale qu'une règle n'est pas respectée :

```python
def calculer_remise(prix: int, pourcentage: int) -> int:
    if not 0 <= pourcentage <= 100:
        raise ValueError(f"Pourcentage invalide : {pourcentage}")
    return prix * (100 - pourcentage) // 100
```

Pour les erreurs propres à ton domaine, crée une classe d'exception dédiée. Elle hérite d'`Exception` :

```python
class ErreurPaiement(Exception):
    """Erreur de base pour les paiements."""


class SoldeInsuffisant(ErreurPaiement):
    def __init__(self, solde: int, montant: int):
        super().__init__(f"Solde {solde} FCFA insuffisant pour {montant} FCFA")
        self.solde = solde
        self.montant = montant
```

Le code appelant peut alors intercepter `SoldeInsuffisant` pour proposer une recharge, ou `ErreurPaiement` pour traiter tous les cas de paiement d'un coup. C'est exactement le mécanisme de l'héritage vu au chapitre précédent.

> **Astuce** : suis le principe « mieux vaut demander pardon que la permission » (EAFP) : tente l'opération et intercepte l'erreur, plutôt que de multiplier les tests préalables. C'est plus pythonique et évite les conditions de concurrence.

:::quiz
Quel bloc s'exécute **dans tous les cas**, qu'il y ait une exception ou non ?
- [ ] `else`
- [ ] `except`
- [x] `finally`
- [ ] `try`
> `finally` est toujours exécuté, même si une exception est levée ou si un `return` survient dans le bloc. `else` ne s'exécute que sans exception.
:::

## Pourquoi tester ?

Sans test automatique, chaque modification te fait peur : « est-ce que j'ai cassé autre chose ? ». Avec des tests, tu le sais en deux secondes. Les tests documentent aussi le comportement attendu, et te forcent à écrire du code plus modulaire. Python inclut `unittest`, mais la communauté préfère largement **pytest**, plus simple et plus lisible.

Installe-le dans ton environnement virtuel :

```bash
pip install pytest
```

## Ton premier test avec pytest

Imaginons un module `remises.py` :

```python
def appliquer_remise(prix: int, pourcentage: int) -> int:
    if not 0 <= pourcentage <= 100:
        raise ValueError("pourcentage hors limites")
    return prix * (100 - pourcentage) // 100
```

Crée un fichier `test_remises.py` à côté :

```python
import pytest
from remises import appliquer_remise


def test_remise_dix_pour_cent():
    assert appliquer_remise(10000, 10) == 9000


def test_aucune_remise():
    assert appliquer_remise(5000, 0) == 5000


def test_pourcentage_invalide():
    with pytest.raises(ValueError):
        appliquer_remise(5000, 150)
```

Lance :

```bash
pytest -v
```

pytest découvre automatiquement les fichiers dont le nom commence par `test_` (et se termine par `.py`) et les fonctions dont le nom commence par `test_`. Un test **réussit** quand aucune exception ne survient. Le mot-clé `assert` suffit : en cas d'échec, pytest affiche les valeurs comparées, sans que tu aies besoin de méthodes spéciales.

La structure d'un bon test suit le schéma **Arrange, Act, Assert** : préparer les données, exécuter l'action, vérifier le résultat. Un test = un comportement vérifié, avec un nom qui dit ce qui est attendu (`test_pourcentage_invalide`, pas `test2`).

:::quiz
Comment vérifier avec pytest qu'une fonction lève bien une `ValueError` ?
- [ ] `assert appliquer_remise(5000, 150) == ValueError`
- [ ] `try: ... except: pass`
- [x] `with pytest.raises(ValueError):` autour de l'appel
- [ ] `assert not appliquer_remise(5000, 150)`
> `pytest.raises` est le gestionnaire de contexte prévu : le test réussit si l'exception est levée et échoue sinon.
:::

## Tester plusieurs cas avec le paramétrage

Plutôt que de copier-coller dix tests presque identiques, on utilise `parametrize` :

```python
import pytest
from remises import appliquer_remise


@pytest.mark.parametrize(
    "prix, pourcentage, attendu",
    [
        (10000, 10, 9000),
        (10000, 0, 10000),
        (10000, 100, 0),
        (2500, 20, 2000),
    ],
)
def test_appliquer_remise(prix, pourcentage, attendu):
    assert appliquer_remise(prix, pourcentage) == attendu
```

pytest exécute la fonction une fois par ligne du tableau et rapporte chaque cas séparément. Ajouter un cas, c'est ajouter une ligne. Pense toujours aux **cas limites** : zéro, valeurs extrêmes, listes vides, texte vide.

## Les fixtures : préparer l'environnement

Une **fixture** est une fonction qui prépare quelque chose dont les tests ont besoin. Elle est injectée par simple nom de paramètre :

```python
import pytest
from boutique import Panier, Produit


@pytest.fixture
def panier_garni():
    panier = Panier()
    panier.ajouter(Produit("Attiéké", 500, 100), 4)
    panier.ajouter(Produit("Poisson", 1500, 50), 2)
    return panier


def test_total(panier_garni):
    assert panier_garni.total() == 5000


def test_nombre_lignes(panier_garni):
    assert len(panier_garni) == 2
```

Chaque test reçoit un panier **neuf**, ce qui garantit leur indépendance : l'ordre d'exécution ne doit jamais compter. pytest fournit aussi des fixtures prêtes à l'emploi, comme `tmp_path` qui donne un dossier temporaire :

```python
import json


def test_sauvegarde(tmp_path):
    chemin = tmp_path / "donnees.json"
    chemin.write_text(json.dumps([{"id": 1}]), encoding="utf-8")
    assert json.loads(chemin.read_text(encoding="utf-8")) == [{"id": 1}]
```

Le test n'écrit jamais dans ton vrai dossier de projet : c'est une règle d'or. Un test ne doit pas dépendre d'internet, de l'heure ou de fichiers réels. Pour isoler ces dépendances, on peut les remplacer par des doublures avec `monkeypatch` ou `unittest.mock`.

## Mesurer la couverture

La **couverture** indique le pourcentage de lignes exécutées par les tests. Installe le plugin et lance :

```bash
pip install pytest-cov
pytest --cov=. --cov-report=term-missing
```

La colonne `Missing` liste les lignes jamais exécutées. Une couverture élevée est un bon indicateur, mais pas une garantie : 100 % de couverture avec des tests qui ne vérifient rien ne vaut rien. Vise des tests qui **échouent quand le comportement change**.

## La qualité du code : ruff et logging

Un code correct doit aussi être lisible et cohérent. **Ruff** est un outil rapide qui joue le rôle de linter (détecte erreurs et mauvaises pratiques) et de formateur :

```bash
pip install ruff
ruff check .          # signale variables inutilisées, imports en trop, erreurs
ruff check . --fix    # corrige ce qui peut l'être
ruff format .         # uniformise la mise en forme
```

Un fichier `pyproject.toml` à la racine permet de configurer l'outil et pytest ensemble :

```toml
[tool.ruff]
line-length = 88
target-version = "py312"

[tool.ruff.lint]
select = ["E", "F", "I", "B"]

[tool.pytest.ini_options]
testpaths = ["tests"]
```

Pour le suivi du programme en fonctionnement, remplace les `print` de débogage par le module **logging**, qui gère niveaux de gravité, horodatage et fichiers :

```python
import logging

logging.basicConfig(
    level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
)
logger = logging.getLogger(__name__)


def payer(montant: int) -> None:
    logger.info("Paiement de %s FCFA demandé", montant)
    try:
        ...
    except Exception:
        logger.exception("Échec du paiement")
        raise
```

Les niveaux, du moins grave au plus grave : `DEBUG`, `INFO`, `WARNING`, `ERROR`, `CRITICAL`. `logger.exception` enregistre aussi la trace complète. En production, on règle le niveau sur `INFO` ou `WARNING` sans toucher au code.

## Atelier guidé : sécuriser un calculateur de caisse

Compte une heure et demie.

1. Crée un dossier `caisse` avec un environnement virtuel, puis installe `pytest`, `pytest-cov` et `ruff`.
2. Crée `caisse.py` avec une fonction `total_ticket(lignes)` qui reçoit une liste de tuples (prix, quantité) et retourne le total en FCFA.
3. Écris d'abord les tests dans `tests/test_caisse.py` : ticket vide, un article, plusieurs articles.
4. Fais passer les tests avec `pytest -v`. Cette approche, écrire le test avant le code, s'appelle le TDD.
5. Ajoute une règle : un prix ou une quantité négatifs lèvent `ValueError`. Teste-la avec `pytest.raises`.
6. Crée une exception `TicketInvalide(Exception)` et utilise-la à la place de `ValueError`. Adapte tes tests.
7. Ajoute une fonction `appliquer_remise(total, pourcentage)` et teste-la avec `parametrize` sur cinq cas, dont les limites 0 et 100.
8. Écris une fixture `ticket_type` et réutilise-la dans trois tests.
9. Lance `pytest --cov=. --cov-report=term-missing` et complète les tests jusqu'à couvrir toutes les lignes utiles.
10. Passe `ruff check .` et `ruff format .`, puis remplace tes `print` éventuels par du `logging`.

Pour t'auto-évaluer : introduis volontairement un bug dans `total_ticket` (par exemple une addition à la place d'une multiplication) et vérifie qu'au moins un test échoue. Si aucun test n'échoue, tes tests ne protègent pas assez.

## Erreurs fréquentes

- **Un `except` trop large.** `except Exception: pass` masque les vrais problèmes.
- **Mettre trop de code dans le `try`.** Tu ne sais plus quelle ligne a échoué.
- **Tester plusieurs choses dans un seul test.** Quand il échoue, la cause est floue.
- **Des tests dépendants les uns des autres.** Ils passent dans un ordre et échouent dans un autre.
- **Tester l'implémentation plutôt que le comportement.** Le moindre refactoring casse tout.
- **Oublier le préfixe `test_`.** pytest ignore silencieusement le fichier ou la fonction.
- **Tester avec de vraies données ou un vrai réseau.** Les tests deviennent lents et instables.
- **Courir après les 100 % de couverture.** Mieux vaut des tests pertinents sur la logique importante.

## Bonnes pratiques

- Intercepte des exceptions précises et traite-les vraiment.
- Crée une hiérarchie d'exceptions pour ton domaine métier.
- Lève une erreur claire plutôt que de renvoyer une valeur magique comme `-1`.
- Écris les tests avec le code, pas « plus tard » : plus tard n'arrive jamais.
- Un test, un comportement, un nom explicite.
- Couvre les cas limites et les cas d'erreur, pas seulement le chemin heureux.
- Lance `ruff` et `pytest` avant chaque commit, idéalement automatiquement dans une intégration continue.
- Utilise `logging` plutôt que `print` dans toute application destinée à tourner sans toi.

## À retenir

- Une trace d'erreur se lit de bas en haut : type d'erreur à la fin, chemin d'appels au-dessus.
- `try/except/else/finally` gère les erreurs ; on intercepte des types précis, jamais un `except` nu.
- `raise` signale un problème ; les exceptions personnalisées expriment les règles du métier.
- pytest découvre les fichiers et fonctions préfixés par `test_`, utilise `assert`, `pytest.raises`, `parametrize` et les fixtures.
- La couverture montre ce qui n'est pas testé, sans garantir la qualité des tests.
- ruff vérifie et formate le code ; `logging` remplace les `print` de débogage.
