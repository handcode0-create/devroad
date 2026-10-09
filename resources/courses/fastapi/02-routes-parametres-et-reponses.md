---
title: Routes, paramètres et réponses
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Une API ne sert à rien si elle ne sait pas recevoir des informations et répondre proprement. Dans ce chapitre, tu apprends les trois façons d'envoyer des données à FastAPI (chemin, requête, corps), comment choisir le bon code de statut, comment signaler une erreur avec `HTTPException`, et comment structurer un CRUD complet pour les roadmaps de DevRoad.

À la fin du chapitre, tu seras capable de :

- utiliser des **paramètres de chemin** et des **paramètres de requête** (*query*) ;
- recevoir un **corps JSON** décrit par un modèle ;
- choisir la méthode HTTP et le code de statut adaptés à chaque opération ;
- lever des erreurs `404`, `409` ou `400` avec `HTTPException` ;
- filtrer, paginer et trier une liste ;
- contrôler la forme de la réponse avec `response_model` ;
- écrire un CRUD complet sur une liste en mémoire.

Prérequis : le chapitre 1 (projet FastAPI qui démarre, `APIRouter`, `/docs`). Prévois environ deux heures.

## Les méthodes HTTP et le CRUD

Un **CRUD** regroupe les quatre opérations de base sur une ressource : *Create*, *Read*, *Update*, *Delete*. Chaque opération correspond à une méthode HTTP.

| Opération | Méthode | URL | Statut de succès |
| --- | --- | --- | --- |
| Lister | `GET` | `/roadmaps` | `200` |
| Lire un élément | `GET` | `/roadmaps/{id}` | `200` |
| Créer | `POST` | `/roadmaps` | `201` |
| Remplacer | `PUT` | `/roadmaps/{id}` | `200` |
| Modifier en partie | `PATCH` | `/roadmaps/{id}` | `200` |
| Supprimer | `DELETE` | `/roadmaps/{id}` | `204` |

Dans FastAPI, il existe un décorateur par méthode : `@router.get`, `@router.post`, `@router.put`, `@router.patch`, `@router.delete`. L'URL désigne une **ressource** (un nom au pluriel : `/roadmaps`), et la méthode indique l'action. On évite les URL comme `/creerRoadmap` ou `/supprimer-roadmap/3`.

## Les paramètres de chemin

Un paramètre de chemin fait partie de l'URL. Tu l'as déjà vu au chapitre 1 :

```python
@router.get("/{roadmap_id}")
def detail_roadmap(roadmap_id: int):
    ...
```

Tu peux ajouter des contraintes avec `Path` :

```python
from typing import Annotated

from fastapi import Path


@router.get("/{roadmap_id}")
def detail_roadmap(
    roadmap_id: Annotated[int, Path(ge=1, description="Identifiant de la roadmap")],
):
    ...
```

`ge=1` signifie « *greater or equal* à 1 » : `/roadmaps/0` renverra une erreur `422` sans que tu écrives de `if`. L'écriture `Annotated[type, métadonnées]` est la façon actuelle et recommandée de déclarer ces contraintes.

> **Attention** : l'ordre des routes compte. Si tu déclares `/roadmaps/{roadmap_id}` avant `/roadmaps/populaires`, la seconde URL sera interprétée comme un identifiant (`populaires`) et échouera. Place toujours les routes fixes **avant** les routes à paramètre.

## Les paramètres de requête

Tout paramètre de fonction qui n'apparaît pas dans le chemin est considéré comme un **paramètre de requête**, lu dans la partie `?cle=valeur` de l'URL.

```python
@router.get("")
def lister_roadmaps(niveau: str | None = None, limite: int = 10, decalage: int = 0):
    resultat = ROADMAPS
    if niveau is not None:
        resultat = [r for r in resultat if r["niveau"] == niveau]
    return resultat[decalage : decalage + limite]
```

Quelques appels possibles :

```bash
curl "http://127.0.0.1:8000/roadmaps"
curl "http://127.0.0.1:8000/roadmaps?niveau=debutant"
curl "http://127.0.0.1:8000/roadmaps?limite=2&decalage=2"
```

Les règles à connaître :

- un paramètre **avec valeur par défaut** est optionnel ;
- un paramètre **sans valeur par défaut** est obligatoire ;
- `str | None = None` signifie « texte facultatif » ;
- le type convertit et valide : `limite=abc` donne une erreur `422`.

Pour imposer des bornes, utilise `Query` :

```python
from typing import Annotated

from fastapi import Query


@router.get("")
def lister_roadmaps(
    niveau: Annotated[str | None, Query(max_length=20)] = None,
    limite: Annotated[int, Query(ge=1, le=50)] = 10,
    decalage: Annotated[int, Query(ge=0)] = 0,
):
    ...
```

La **pagination** par `limite` et `decalage` est indispensable : renvoyer 10 000 lignes d'un coup ralentit le serveur et le client.

:::quiz
Dans `def lister(niveau: str | None = None, limite: int = 10)`, comment FastAPI traite-t-il `limite` si l'URL est `/roadmaps` ?
- [ ] Il renvoie une erreur car le paramètre est absent
- [x] Il utilise la valeur par défaut 10
- [ ] Il le lit dans le corps de la requête
- [ ] Il le considère comme un paramètre de chemin
> Un paramètre absent du chemin est un paramètre de requête ; avec une valeur par défaut, il est optionnel et prend cette valeur quand il n'est pas fourni.
:::

## Le corps de la requête

Pour créer une roadmap, le client envoie des données en JSON dans le **corps**. On les décrit avec un modèle Pydantic (le chapitre 3 détaille tout ce que Pydantic sait faire) :

```python
from pydantic import BaseModel


class RoadmapCreate(BaseModel):
    titre: str
    niveau: str = "debutant"
    description: str | None = None
```

Il suffit de l'utiliser comme type d'un paramètre :

```python
@router.post("", status_code=201)
def creer_roadmap(donnees: RoadmapCreate):
    nouvelle = {"id": prochain_id(), **donnees.model_dump()}
    ROADMAPS.append(nouvelle)
    return nouvelle
```

FastAPI sait que `donnees` est un corps car son type est un modèle Pydantic. Il lit le JSON, le valide, et te donne un objet. Si `titre` manque, la réponse est une erreur `422` détaillée. Test avec `curl` :

```bash
curl -X POST http://127.0.0.1:8000/roadmaps \
  -H "Content-Type: application/json" \
  -d '{"titre": "Git", "niveau": "debutant"}'
```

La méthode `model_dump()` convertit le modèle en dictionnaire. `status_code=201` indique le statut de succès de cette route.

## Choisir le bon code de statut

Le statut est le premier message que lit un client : il doit être précis. Tu peux le fixer par route avec `status_code`, en utilisant le module `status` pour des noms lisibles :

```python
from fastapi import status


@router.delete("/{roadmap_id}", status_code=status.HTTP_204_NO_CONTENT)
def supprimer_roadmap(roadmap_id: int):
    ...
```

| Code | Quand l'utiliser |
| --- | --- |
| `200` | Lecture ou modification réussie |
| `201` | Ressource créée |
| `204` | Succès sans contenu à renvoyer (suppression) |
| `400` | Requête incorrecte au sens métier |
| `404` | Ressource introuvable |
| `409` | Conflit (par exemple un titre déjà utilisé) |
| `422` | Données mal formées (généré par FastAPI) |
| `500` | Bug côté serveur |

## Signaler une erreur avec `HTTPException`

Pour interrompre une route et renvoyer une erreur, lève une `HTTPException`. Corrigeons enfin le défaut du chapitre 1 :

```python
from fastapi import HTTPException, status


def trouver_roadmap(roadmap_id: int) -> dict:
    for roadmap in ROADMAPS:
        if roadmap["id"] == roadmap_id:
            return roadmap
    raise HTTPException(
        status_code=status.HTTP_404_NOT_FOUND,
        detail=f"Roadmap {roadmap_id} introuvable",
    )


@router.get("/{roadmap_id}")
def detail_roadmap(roadmap_id: int):
    return trouver_roadmap(roadmap_id)
```

La réponse JSON contient alors `{"detail": "Roadmap 99 introuvable"}` avec le statut `404`. Utilise la même idée pour un doublon :

```python
if any(r["titre"].lower() == donnees.titre.lower() for r in ROADMAPS):
    raise HTTPException(status_code=409, detail="Ce titre existe déjà")
```

:::quiz
Un client demande `GET /roadmaps/99` et cette roadmap n'existe pas. Quelle réponse est la plus correcte ?
- [ ] Statut 200 avec un message d'erreur dans le JSON
- [ ] Statut 500 avec le détail
- [x] Statut 404 via `raise HTTPException(status_code=404, ...)`
- [ ] Statut 422 car l'identifiant est invalide
> Le `404` signale qu'une ressource n'existe pas. Le `422` est réservé aux données mal formées, et le `500` aux bugs du serveur.
:::

## Contrôler la réponse avec `response_model`

Que renvoie ta route ? Tout ce que tu retournes part au client. Si ton dictionnaire contient un champ interne (un mot de passe, une note privée), il fuit. Le paramètre `response_model` filtre et documente la sortie :

```python
class RoadmapRead(BaseModel):
    id: int
    titre: str
    niveau: str
    description: str | None = None


@router.get("", response_model=list[RoadmapRead])
def lister_roadmaps():
    return ROADMAPS


@router.get("/{roadmap_id}", response_model=RoadmapRead)
def detail_roadmap(roadmap_id: int):
    return trouver_roadmap(roadmap_id)
```

FastAPI valide la valeur retournée, **retire les champs qui ne sont pas dans le modèle** et affiche la forme exacte dans `/docs`. Séparer `RoadmapCreate` (ce qu'on reçoit) et `RoadmapRead` (ce qu'on renvoie) est une habitude que tu garderas toute ta carrière.

## Un CRUD complet

Assemblons le tout dans `app/routers/roadmaps.py` :

```python
from fastapi import APIRouter, HTTPException, Query, status
from pydantic import BaseModel
from typing import Annotated

router = APIRouter(prefix="/roadmaps", tags=["roadmaps"])

ROADMAPS: list[dict] = [
    {"id": 1, "titre": "Python", "niveau": "debutant", "description": None},
    {"id": 2, "titre": "FastAPI", "niveau": "intermediaire", "description": None},
]


class RoadmapCreate(BaseModel):
    titre: str
    niveau: str = "debutant"
    description: str | None = None


class RoadmapUpdate(BaseModel):
    titre: str | None = None
    niveau: str | None = None
    description: str | None = None


class RoadmapRead(RoadmapCreate):
    id: int


def prochain_id() -> int:
    return max((r["id"] for r in ROADMAPS), default=0) + 1


def trouver(roadmap_id: int) -> dict:
    for r in ROADMAPS:
        if r["id"] == roadmap_id:
            return r
    raise HTTPException(status_code=404, detail="Roadmap introuvable")


@router.get("", response_model=list[RoadmapRead])
def lister(limite: Annotated[int, Query(ge=1, le=50)] = 10):
    return ROADMAPS[:limite]


@router.get("/{roadmap_id}", response_model=RoadmapRead)
def lire(roadmap_id: int):
    return trouver(roadmap_id)


@router.post("", response_model=RoadmapRead, status_code=status.HTTP_201_CREATED)
def creer(donnees: RoadmapCreate):
    nouvelle = {"id": prochain_id(), **donnees.model_dump()}
    ROADMAPS.append(nouvelle)
    return nouvelle


@router.patch("/{roadmap_id}", response_model=RoadmapRead)
def modifier(roadmap_id: int, donnees: RoadmapUpdate):
    roadmap = trouver(roadmap_id)
    roadmap.update(donnees.model_dump(exclude_unset=True))
    return roadmap


@router.delete("/{roadmap_id}", status_code=status.HTTP_204_NO_CONTENT)
def supprimer(roadmap_id: int):
    ROADMAPS.remove(trouver(roadmap_id))
```

Deux détails importants. Dans `modifier`, `exclude_unset=True` ne garde que les champs que le client a **réellement envoyés** : c'est ce qui distingue un `PATCH` (modification partielle) d'un `PUT` (remplacement complet). Dans `supprimer`, la route ne retourne rien : avec `204`, le corps de la réponse reste vide.

## Atelier guidé : le CRUD des roadmaps

Compte quarante-cinq minutes.

1. Reprends le projet du chapitre 1 et remplace le contenu de `app/routers/roadmaps.py` par le CRUD ci-dessus.
2. Lance le serveur et teste chaque route depuis `/docs`.
3. Ajoute le filtre `niveau` sur la liste et vérifie `/roadmaps?niveau=debutant`.
4. Ajoute `decalage` et teste la pagination avec `limite=1`.
5. Empêche la création de deux roadmaps de même titre : renvoie un `409`.
6. Ajoute une route fixe `GET /roadmaps/stats` qui retourne le nombre de roadmaps par niveau. Place-la avant `/{roadmap_id}` et observe ce qui se passe si tu l'inverses.
7. Ajoute un second routeur `cours` avec `GET /roadmaps/{roadmap_id}/cours` qui retourne une liste codée en dur de leçons ; renvoie un `404` si la roadmap n'existe pas.
8. Appelle toutes les routes avec `curl` et note, pour chacune, le statut obtenu.

Auto-évaluation : pour chaque opération du CRUD, peux-tu citer la méthode, l'URL et le statut de succès sans regarder le tableau ?

## Erreurs fréquentes

- **Mettre les routes à paramètre avant les routes fixes.** `/roadmaps/stats` est lu comme un identifiant.
- **Renvoyer `200` pour une erreur.** Utilise toujours `HTTPException` avec le statut adapté.
- **Utiliser `POST` pour tout.** Chaque méthode a une signification ; respecte-la.
- **Oublier `exclude_unset=True` dans un `PATCH`.** Les champs non envoyés écrasent les valeurs existantes par `None`.
- **Retourner un dictionnaire brut avec des champs sensibles.** Utilise `response_model`.
- **Mettre un corps dans une requête `GET`.** Les données de filtrage passent par la query string.

## Bonnes pratiques

- Des URL qui désignent des ressources au pluriel, et une méthode qui indique l'action.
- Un modèle pour l'entrée (`Create`, `Update`) et un autre pour la sortie (`Read`).
- Une pagination par défaut sur toutes les listes, avec une limite maximale.
- Des messages d'erreur en `detail` clairs et utiles au développeur du client.
- Des fonctions utilitaires (`trouver`) pour ne pas répéter la logique de `404`.
- Contraintes déclaratives avec `Annotated`, `Path` et `Query` plutôt que des `if` manuels.

## À retenir

- Les paramètres du chemin sont dans l'URL, les paramètres de requête dans `?cle=valeur`, le corps dans le JSON envoyé.
- Un paramètre avec valeur par défaut est optionnel.
- Chaque opération CRUD a sa méthode et son statut : `200`, `201`, `204`.
- `HTTPException` interrompt la route et renvoie l'erreur choisie, comme `404` ou `409`.
- `response_model` filtre et documente la réponse.
- `exclude_unset=True` rend un `PATCH` réellement partiel.
- Les routes fixes se déclarent avant les routes à paramètre.
