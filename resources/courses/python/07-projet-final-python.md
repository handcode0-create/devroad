---
title: Projet final Python
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Ce chapitre est un projet guidé de bout en bout. Tu vas construire **taches**, un gestionnaire de tâches en ligne de commande, comme le ferait un développeur professionnel : structure en paquet, modèle orienté objet, persistance JSON, interface en ligne de commande avec `argparse`, tests pytest, qualité avec ruff, et livraison propre. Tu mobilises tout ce que tu as vu dans les six chapitres précédents.

À la fin du projet, tu seras capable de :

- structurer un projet Python en paquet avec un point d'entrée exécutable ;
- modéliser un domaine métier avec des classes, des dataclasses et des exceptions dédiées ;
- persister des données de manière fiable dans un fichier JSON ;
- construire une CLI avec sous-commandes grâce à `argparse` ;
- couvrir ton code par des tests automatisés isolés du vrai disque ;
- livrer un dépôt documenté, formaté et reproductible.

Prérequis : avoir terminé les chapitres 1 à 6. Prévois six heures, que tu peux répartir sur deux ou trois séances. Matériel : Python 3.12, un terminal, un éditeur de code et Git (facultatif mais recommandé).

## Le cahier des charges

Tu travailles pour un petit freelance d'Abidjan qui jongle entre plusieurs clients. Il veut noter ses tâches depuis le terminal, sans application lourde, et retrouver ce qui est urgent ou en retard. Voici les spécifications.

### Fonctionnalités attendues

| Commande | Rôle |
| --- | --- |
| `ajouter` | Crée une tâche avec un titre, une priorité, une échéance et un projet facultatifs |
| `lister` | Affiche les tâches, avec filtres par statut, projet et priorité |
| `terminer` | Marque une tâche comme faite |
| `modifier` | Change le titre, la priorité ou l'échéance |
| `supprimer` | Supprime une tâche |
| `stats` | Affiche un résumé : total, terminées, en retard, par projet |
| `exporter` | Exporte les tâches au format CSV |

### Règles métier

- Chaque tâche possède un **identifiant entier unique**, jamais réutilisé après suppression.
- Le titre est obligatoire et contient entre 3 et 80 caractères.
- La priorité vaut `basse`, `normale` ou `haute` (par défaut `normale`).
- L'échéance est une date au format `AAAA-MM-JJ`, facultative.
- Une tâche non terminée dont l'échéance est passée est **en retard**.
- Terminer une tâche déjà terminée est une erreur claire, pas un plantage.

### Contraintes techniques

- Python 3.12, bibliothèque standard uniquement pour le programme (pytest et ruff pour le développement).
- Données stockées dans `~/.taches/taches.json` par défaut, chemin modifiable par la variable d'environnement `TACHES_FICHIER`.
- Aucune trace d'erreur brute pour l'utilisateur : des messages en français et un code de sortie non nul en cas d'échec.
- Couverture de tests d'au moins 85 % sur la logique métier.

## Étape 1 : préparer le projet

Crée l'arborescence suivante :

```text
taches/
├── pyproject.toml
├── README.md
├── .gitignore
├── src/
│   └── taches/
│       ├── __init__.py
│       ├── __main__.py
│       ├── modeles.py
│       ├── erreurs.py
│       ├── stockage.py
│       ├── service.py
│       └── cli.py
└── tests/
    ├── conftest.py
    ├── test_modeles.py
    ├── test_stockage.py
    ├── test_service.py
    └── test_cli.py
```

Les responsabilités sont séparées : `modeles` décrit les données, `stockage` lit et écrit le fichier, `service` contient les règles métier, `cli` s'occupe uniquement de l'interface. Cette architecture en couches permet de tester chaque partie séparément.

Crée l'environnement et la configuration :

```bash
mkdir taches && cd taches
python3.12 -m venv .venv
source .venv/bin/activate
pip install pytest pytest-cov ruff
```

Le fichier `pyproject.toml` :

```toml
[project]
name = "taches"
version = "0.1.0"
requires-python = ">=3.12"

[project.scripts]
taches = "taches.cli:main"

[tool.setuptools.packages.find]
where = ["src"]

[tool.pytest.ini_options]
testpaths = ["tests"]
pythonpath = ["src"]

[tool.ruff]
line-length = 88
target-version = "py312"

[tool.ruff.lint]
select = ["E", "F", "I", "B", "UP"]
```

Le fichier `.gitignore` contient au minimum `.venv/`, `__pycache__/` et `.pytest_cache/`. Grâce à `pythonpath = ["src"]`, pytest trouve ton paquet sans installation. Le fichier `__main__.py` permet de lancer `python -m taches` :

```python
from taches.cli import main

raise SystemExit(main())
```

## Étape 2 : les erreurs et le modèle

Commence par les exceptions du domaine, dans `erreurs.py`. Le programme n'interceptera qu'une seule classe de base pour afficher un message propre :

```python
class ErreurTaches(Exception):
    """Erreur de base de l'application."""


class TitreInvalide(ErreurTaches):
    pass


class TacheIntrouvable(ErreurTaches):
    def __init__(self, identifiant: int):
        super().__init__(f"Aucune tâche avec l'identifiant {identifiant}.")
        self.identifiant = identifiant


class TacheDejaTerminee(ErreurTaches):
    pass


class DonneesCorrompues(ErreurTaches):
    pass
```

Puis le modèle, dans `modeles.py`. Une tâche est une dataclass, avec des conversions vers et depuis un dictionnaire pour la sauvegarde :

```python
from __future__ import annotations

from dataclasses import dataclass
from datetime import date
from enum import Enum

from taches.erreurs import TitreInvalide


class Priorite(str, Enum):
    BASSE = "basse"
    NORMALE = "normale"
    HAUTE = "haute"


@dataclass
class Tache:
    id: int
    titre: str
    priorite: Priorite = Priorite.NORMALE
    echeance: date | None = None
    projet: str | None = None
    terminee: bool = False

    def __post_init__(self) -> None:
        self.titre = valider_titre(self.titre)

    def en_retard(self, aujourdhui: date | None = None) -> bool:
        aujourdhui = aujourdhui or date.today()
        return (
            not self.terminee
            and self.echeance is not None
            and self.echeance < aujourdhui
        )

    def vers_dict(self) -> dict:
        return {
            "id": self.id,
            "titre": self.titre,
            "priorite": self.priorite.value,
            "echeance": self.echeance.isoformat() if self.echeance else None,
            "projet": self.projet,
            "terminee": self.terminee,
        }

    @classmethod
    def depuis_dict(cls, d: dict) -> Tache:
        return cls(
            id=d["id"],
            titre=d["titre"],
            priorite=Priorite(d.get("priorite", "normale")),
            echeance=date.fromisoformat(d["echeance"]) if d.get("echeance") else None,
            projet=d.get("projet"),
            terminee=d.get("terminee", False),
        )


def valider_titre(titre: str) -> str:
    titre = titre.strip()
    if not 3 <= len(titre) <= 80:
        raise TitreInvalide("Le titre doit contenir entre 3 et 80 caractères.")
    return titre
```

Points d'attention : `Priorite` est une énumération, ce qui interdit les valeurs fantaisistes ; `__post_init__` valide la donnée dès la création ; `en_retard` accepte une date en paramètre, ce qui rend la méthode **testable** sans dépendre de l'horloge réelle.

:::quiz
Pourquoi `en_retard` accepte-t-elle un paramètre `aujourdhui` facultatif ?
- [ ] Pour que la méthode soit plus rapide
- [x] Pour pouvoir tester la méthode avec une date fixe, sans dépendre de la date réelle
- [ ] Parce que Python l'exige pour les dataclasses
- [ ] Pour permettre de modifier la date système
> Injecter la date en paramètre rend le comportement déterministe dans les tests. En usage normal, la valeur par défaut `date.today()` s'applique.
:::

## Étape 3 : le stockage

La couche de stockage ne connaît que les tâches et un chemin de fichier. Dans `stockage.py` :

```python
import json
import os
from pathlib import Path

from taches.erreurs import DonneesCorrompues
from taches.modeles import Tache


def chemin_par_defaut() -> Path:
    valeur = os.environ.get("TACHES_FICHIER")
    if valeur:
        return Path(valeur)
    return Path.home() / ".taches" / "taches.json"


class Depot:
    """Lit et écrit la liste des tâches dans un fichier JSON."""

    def __init__(self, chemin: Path | None = None):
        self.chemin = chemin or chemin_par_defaut()

    def charger(self) -> tuple[list[Tache], int]:
        if not self.chemin.exists():
            return [], 1
        try:
            brut = json.loads(self.chemin.read_text(encoding="utf-8"))
            taches = [Tache.depuis_dict(d) for d in brut["taches"]]
            return taches, int(brut["prochain_id"])
        except (json.JSONDecodeError, KeyError, ValueError, TypeError) as erreur:
            raise DonneesCorrompues(
                f"Le fichier {self.chemin} est illisible : {erreur}"
            ) from erreur

    def sauvegarder(self, taches: list[Tache], prochain_id: int) -> None:
        self.chemin.parent.mkdir(parents=True, exist_ok=True)
        contenu = {
            "prochain_id": prochain_id,
            "taches": [t.vers_dict() for t in taches],
        }
        temporaire = self.chemin.with_suffix(".tmp")
        temporaire.write_text(
            json.dumps(contenu, indent=2, ensure_ascii=False), encoding="utf-8"
        )
        temporaire.replace(self.chemin)
```

Deux choix de conception méritent une explication. D'abord, on stocke `prochain_id` pour qu'un identifiant supprimé ne soit jamais réutilisé. Ensuite, la sauvegarde écrit dans un fichier temporaire puis le **remplace** d'un coup : si le programme est interrompu en pleine écriture, l'ancien fichier reste intact. C'est une technique d'écriture atomique, utilisée par les vrais logiciels.

> **Astuce** : `raise ... from erreur` conserve l'erreur d'origine dans la trace de débogage tout en présentant à l'utilisateur ton exception métier.

## Étape 4 : le service métier

Le service orchestre le dépôt et applique les règles. Il ne fait aucun `print` et ne lit aucun argument de ligne de commande. Dans `service.py` :

```python
from datetime import date

from taches.erreurs import TacheDejaTerminee, TacheIntrouvable
from taches.modeles import Priorite, Tache, valider_titre
from taches.stockage import Depot


class ServiceTaches:
    def __init__(self, depot: Depot):
        self.depot = depot
        self.taches, self.prochain_id = depot.charger()

    def _enregistrer(self) -> None:
        self.depot.sauvegarder(self.taches, self.prochain_id)

    def ajouter(
        self,
        titre: str,
        priorite: Priorite = Priorite.NORMALE,
        echeance: date | None = None,
        projet: str | None = None,
    ) -> Tache:
        tache = Tache(self.prochain_id, titre, priorite, echeance, projet)
        self.taches.append(tache)
        self.prochain_id += 1
        self._enregistrer()
        return tache

    def trouver(self, identifiant: int) -> Tache:
        for tache in self.taches:
            if tache.id == identifiant:
                return tache
        raise TacheIntrouvable(identifiant)

    def terminer(self, identifiant: int) -> Tache:
        tache = self.trouver(identifiant)
        if tache.terminee:
            raise TacheDejaTerminee(f"La tâche {identifiant} est déjà terminée.")
        tache.terminee = True
        self._enregistrer()
        return tache

    def supprimer(self, identifiant: int) -> None:
        tache = self.trouver(identifiant)
        self.taches.remove(tache)
        self._enregistrer()

    def lister(
        self,
        statut: str = "toutes",
        projet: str | None = None,
        priorite: Priorite | None = None,
    ) -> list[Tache]:
        resultat = self.taches
        if statut == "ouvertes":
            resultat = [t for t in resultat if not t.terminee]
        elif statut == "terminees":
            resultat = [t for t in resultat if t.terminee]
        elif statut == "retard":
            resultat = [t for t in resultat if t.en_retard()]
        if projet:
            resultat = [t for t in resultat if t.projet == projet]
        if priorite:
            resultat = [t for t in resultat if t.priorite == priorite]
        return sorted(resultat, key=lambda t: t.id)
```

À toi d'écrire les méthodes `modifier` (qui réutilise `valider_titre`) et `statistiques`, qui retourne un dictionnaire avec le total, le nombre de terminées, le nombre en retard et un décompte par projet. Pour le décompte, `collections.Counter` est idéal.

## Étape 5 : l'interface en ligne de commande

Le module `argparse` construit des sous-commandes comme `git commit` ou `git status`. Dans `cli.py`, commence par le parseur :

```python
import argparse
import sys
from datetime import date

from taches.erreurs import ErreurTaches
from taches.modeles import Priorite
from taches.service import ServiceTaches
from taches.stockage import Depot


def date_iso(texte: str) -> date:
    try:
        return date.fromisoformat(texte)
    except ValueError:
        raise argparse.ArgumentTypeError(f"Date invalide : {texte} (AAAA-MM-JJ)")


def construire_parseur() -> argparse.ArgumentParser:
    parseur = argparse.ArgumentParser(prog="taches", description="Gestionnaire de tâches")
    sous = parseur.add_subparsers(dest="commande", required=True)

    ajout = sous.add_parser("ajouter", help="créer une tâche")
    ajout.add_argument("titre")
    ajout.add_argument("--priorite", choices=[p.value for p in Priorite], default="normale")
    ajout.add_argument("--echeance", type=date_iso)
    ajout.add_argument("--projet")

    liste = sous.add_parser("lister", help="afficher les tâches")
    liste.add_argument("--statut", choices=["toutes", "ouvertes", "terminees", "retard"], default="toutes")
    liste.add_argument("--projet")

    fin = sous.add_parser("terminer", help="marquer une tâche comme faite")
    fin.add_argument("id", type=int)

    sup = sous.add_parser("supprimer", help="supprimer une tâche")
    sup.add_argument("id", type=int)

    sous.add_parser("stats", help="afficher un résumé")
    return parseur
```

Puis la fonction `main`. Elle reçoit éventuellement une liste d'arguments, ce qui la rend testable, et retourne un **code de sortie** : `0` pour le succès, `1` pour une erreur attendue :

```python
def formater(tache) -> str:
    case = "x" if tache.terminee else " "
    echeance = tache.echeance.isoformat() if tache.echeance else "-"
    retard = " (EN RETARD)" if tache.en_retard() else ""
    return f"[{case}] #{tache.id:<3} {tache.titre} | {tache.priorite.value} | {echeance}{retard}"


def main(argv: list[str] | None = None, depot: Depot | None = None) -> int:
    args = construire_parseur().parse_args(argv)
    try:
        service = ServiceTaches(depot or Depot())
        if args.commande == "ajouter":
            t = service.ajouter(args.titre, Priorite(args.priorite), args.echeance, args.projet)
            print(f"Tâche #{t.id} créée.")
        elif args.commande == "lister":
            for t in service.lister(args.statut, args.projet):
                print(formater(t))
        elif args.commande == "terminer":
            service.terminer(args.id)
            print(f"Tâche #{args.id} terminée.")
        elif args.commande == "supprimer":
            service.supprimer(args.id)
            print(f"Tâche #{args.id} supprimée.")
        elif args.commande == "stats":
            for cle, valeur in service.statistiques().items():
                print(f"{cle} : {valeur}")
    except ErreurTaches as erreur:
        print(f"Erreur : {erreur}", file=sys.stderr)
        return 1
    return 0
```

Remarque l'injection du paramètre `depot` : en test, on passe un dépôt qui pointe vers un dossier temporaire, et l'utilisateur ne voit rien de cette subtilité. Les messages d'erreur sont envoyés sur `sys.stderr`, la sortie réservée aux erreurs, pour ne pas polluer les données affichées sur la sortie standard lorsqu'on redirige un résultat vers un fichier.

Teste à la main :

```bash
python -m taches ajouter "Livrer la maquette client Yopougon" --priorite haute --echeance 2026-10-20 --projet Yopougon
python -m taches lister --statut ouvertes
python -m taches terminer 1
```

## Étape 6 : les tests

Dans `tests/conftest.py`, une fixture fournit un dépôt isolé et un service neuf pour chaque test :

```python
import pytest

from taches.service import ServiceTaches
from taches.stockage import Depot


@pytest.fixture
def depot(tmp_path):
    return Depot(tmp_path / "taches.json")


@pytest.fixture
def service(depot):
    return ServiceTaches(depot)
```

Quelques tests représentatifs du service, de la persistance et du modèle :

```python
from datetime import date

import pytest

from taches.erreurs import TacheDejaTerminee, TacheIntrouvable, TitreInvalide
from taches.modeles import Tache
from taches.service import ServiceTaches


def test_ajouter_attribue_des_ids_croissants(service):
    assert service.ajouter("Premier devis").id == 1
    assert service.ajouter("Second devis").id == 2


def test_id_jamais_reutilise_apres_suppression(service):
    service.ajouter("Une tâche")
    service.supprimer(1)
    assert service.ajouter("Une autre").id == 2


def test_titre_trop_court(service):
    with pytest.raises(TitreInvalide):
        service.ajouter("ab")


def test_terminer_deux_fois(service):
    service.ajouter("Facturer le client")
    service.terminer(1)
    with pytest.raises(TacheDejaTerminee):
        service.terminer(1)


def test_tache_introuvable(service):
    with pytest.raises(TacheIntrouvable):
        service.terminer(99)


def test_persistance(depot):
    ServiceTaches(depot).ajouter("Tâche persistante")
    assert ServiceTaches(depot).lister()[0].titre == "Tâche persistante"


@pytest.mark.parametrize(
    "echeance, terminee, attendu",
    [
        (date(2026, 1, 1), False, True),
        (date(2026, 1, 1), True, False),
        (date(2026, 12, 31), False, False),
        (None, False, False),
    ],
)
def test_en_retard(echeance, terminee, attendu):
    tache = Tache(1, "Test retard", echeance=echeance, terminee=terminee)
    assert tache.en_retard(date(2026, 6, 1)) is attendu
```

Pour la CLI, tu appelles `main` directement et tu utilises la fixture `capsys` de pytest pour capturer ce qui est affiché :

```python
from taches.cli import main


def test_cli_ajouter_et_lister(depot, capsys):
    assert main(["ajouter", "Préparer la démo"], depot=depot) == 0
    assert main(["lister"], depot=depot) == 0
    sortie = capsys.readouterr().out
    assert "Préparer la démo" in sortie


def test_cli_erreur_id_inconnu(depot, capsys):
    assert main(["terminer", "42"], depot=depot) == 1
    assert "Aucune tâche" in capsys.readouterr().err
```

Ajoute aussi un test sur un fichier JSON volontairement cassé (écris `{ pas du json` dans le fichier, puis attends-toi à `DonneesCorrompues`). Mesure ensuite la couverture :

```bash
pytest --cov=taches --cov-report=term-missing
```

:::quiz
Pourquoi la fonction `main` reçoit-elle `argv` et `depot` en paramètres facultatifs ?
- [ ] Pour permettre de lancer plusieurs programmes en parallèle
- [x] Pour pouvoir la tester sans toucher au vrai terminal ni au vrai fichier de données
- [ ] Parce que `argparse` l'impose
- [ ] Pour accélérer le démarrage
> Passer les dépendances en paramètres (injection) isole le code des éléments extérieurs et rend les tests rapides et reproductibles.
:::

## Étape 7 : qualité et livraison

Lance les vérifications dans cet ordre avant chaque commit :

```bash
ruff format .
ruff check . --fix
pytest --cov=taches --cov-report=term-missing
```

Rédige ensuite un `README.md` utile : ce que fait l'outil, comment l'installer (`python3.12 -m venv .venv`, `pip install -e .`), quelques exemples de commandes avec leur résultat, comment lancer les tests, et où sont stockées les données. Un bon README permet à quelqu'un d'utiliser ton projet sans te poser de question.

Enfin, si tu utilises Git, fais des commits atomiques avec des messages clairs (« ajoute la commande stats », « corrige la validation du titre »), plutôt qu'un seul gros commit final.

## Checklist de recette

Avant de considérer le projet comme terminé, vérifie chaque point :

- Le projet s'installe depuis zéro dans un nouvel environnement virtuel, sans erreur.
- `python -m taches --help` affiche l'aide en listant toutes les sous-commandes.
- Les commandes `ajouter`, `lister`, `terminer`, `modifier`, `supprimer`, `stats` et `exporter` fonctionnent.
- Un titre de 2 caractères ou de 81 caractères est refusé avec un message clair.
- Une date mal formée (`20-10-2026`) est refusée par la CLI.
- Terminer deux fois la même tâche affiche une erreur et renvoie le code 1.
- Après suppression de la tâche 3, la prochaine tâche créée porte l'identifiant 4, pas 3.
- Un fichier JSON corrompu produit un message en français, pas une trace Python.
- La variable `TACHES_FICHIER` redirige bien le stockage.
- Aucun test n'écrit dans le dossier personnel de l'utilisateur.
- La couverture de la logique métier dépasse 85 %.
- `ruff check .` et `ruff format --check .` ne signalent rien.
- Le `README.md` est complet et les exemples sont exacts.

## Pour aller plus loin

Une fois la recette validée, voici des extensions classées par difficulté :

1. Ajouter des couleurs et un tableau avec la bibliothèque `rich`.
2. Gérer des sous-tâches ou des étiquettes multiples par tâche.
3. Remplacer le JSON par SQLite via le module `sqlite3` de la bibliothèque standard.
4. Ajouter un rappel : la commande `lister --statut retard` affichée automatiquement au lancement.
5. Publier le projet sur GitHub avec une intégration continue qui lance `ruff` et `pytest` à chaque push.
6. Exposer le même service via une petite API FastAPI, en réutilisant la couche métier sans y changer une ligne.

## Atelier guidé

Voici l'ordre de travail conseillé pour tes six heures :

1. Prépare le projet, l'environnement virtuel et la configuration (30 minutes).
2. Écris `erreurs.py` et `modeles.py`, puis leurs tests (50 minutes).
3. Écris `stockage.py` et teste la sauvegarde, le rechargement et le fichier corrompu (50 minutes).
4. Écris `service.py` avec `modifier` et `statistiques`, en testant chaque méthode (80 minutes).
5. Construis la CLI, y compris la commande `exporter` en CSV, puis ses tests (80 minutes).
6. Passe la checklist, corrige, formate, rédige le README (50 minutes).

Pour t'auto-évaluer : demande à quelqu'un de cloner ton dépôt et de suivre uniquement ton README. S'il réussit à installer, lancer et tester le projet sans ton aide, ton travail est professionnel.

## Erreurs fréquentes

- **Mettre la logique métier dans la CLI.** Elle devient impossible à tester sans simuler un terminal.
- **Appeler `date.today()` en dur partout.** Les tests dépendent alors du jour où tu les lances.
- **Écrire directement dans le fichier de données.** Une interruption pendant l'écriture corrompt tout.
- **Tester avec le vrai fichier utilisateur.** Utilise toujours `tmp_path`.
- **Réutiliser les identifiants supprimés.** Des références anciennes pointent soudain vers une autre tâche.
- **Afficher des traces Python à l'utilisateur.** Intercepte les exceptions métier et formule un message humain.
- **Négliger le README.** Un projet non documenté est un projet que personne n'utilise, pas même toi dans six mois.

## Bonnes pratiques

- Sépare les couches : modèle, stockage, service, interface.
- Injecte les dépendances (chemin, date, dépôt) pour garder le code testable.
- Valide les données à l'entrée et signale les erreurs avec des exceptions du domaine.
- Écris les tests en même temps que le code, et commence par les règles métier les plus importantes.
- Utilise des codes de sortie cohérents et `stderr` pour les erreurs.
- Automatise : `ruff`, `pytest` et la couverture doivent se lancer en une commande.
- Commite souvent, par petites unités cohérentes.

## À retenir

- Un projet professionnel sépare modèle, persistance, logique métier et interface.
- `argparse` et ses sous-commandes offrent une CLI complète sans dépendance externe.
- L'écriture atomique (fichier temporaire puis remplacement) protège les données.
- L'injection de dépendances est la clé de tests rapides et fiables.
- Une recette claire (checklist) définit ce que « terminé » veut dire.
- Tu sais maintenant concevoir, tester et livrer un vrai programme Python de bout en bout.
