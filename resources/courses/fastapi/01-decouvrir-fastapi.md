---
title: Découvrir FastAPI
minutes: 100
level: beginner
---

## Ce que tu vas apprendre

FastAPI est un framework Python moderne pour construire des **APIs web**. Il est rapide à écrire, rapide à exécuter, et il génère tout seul une documentation interactive de ton API. Dans ce premier chapitre, tu installes l'outil, tu lances ton premier serveur et tu comprends comment une requête HTTP devient une fonction Python.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est une API et le rôle de FastAPI, d'Uvicorn et de Starlette ;
- créer un environnement virtuel et installer FastAPI proprement ;
- écrire une application avec plusieurs routes et la lancer avec `uvicorn` ;
- utiliser la documentation automatique `/docs` pour tester ton API ;
- organiser un petit projet en plusieurs fichiers ;
- distinguer `def` et `async def` à un niveau d'introduction.

Prérequis : les bases de Python (variables, fonctions, dictionnaires, listes, `import`) et une notion de ce qu'est HTTP. Prévois environ une heure quarante. Tu as besoin de Python 3.10 ou plus récent et d'un terminal.

## Qu'est-ce qu'une API et pourquoi FastAPI ?

Une **API** (*Application Programming Interface*) est une porte d'entrée par laquelle d'autres programmes parlent à ton application. Une application mobile, un site React ou un autre service envoie une requête HTTP ; ton API répond, la plupart du temps en **JSON**.

Pour DevRoad, l'API pourrait exposer les roadmaps et la progression des apprenants :

```text
GET  /roadmaps          -> la liste des roadmaps
GET  /roadmaps/3        -> le détail de la roadmap numéro 3
POST /roadmaps          -> créer une roadmap
```

FastAPI s'appuie sur deux briques :

| Brique | Rôle |
| --- | --- |
| **Starlette** | Le moteur web : routage, requêtes, réponses, middlewares |
| **Pydantic** | La validation et la conversion des données (chapitre 3) |
| **Uvicorn** | Le serveur qui écoute le réseau et exécute ton application |

Ce qui rend FastAPI populaire :

- il utilise les **annotations de type** de Python pour valider les données automatiquement ;
- il génère une documentation **OpenAPI** et une page interactive (Swagger UI) sans effort ;
- il est asynchrone par conception, donc performant pour les appels réseau ;
- l'éditeur de code comprend ton code : autocomplétion et erreurs détectées tôt.

> **À retenir** : FastAPI = Starlette (web) + Pydantic (données) + les types Python. Uvicorn est le serveur qui fait tourner le tout.

:::quiz
Quel est le rôle d'Uvicorn dans un projet FastAPI ?
- [ ] Valider les données reçues
- [ ] Générer la documentation OpenAPI
- [x] Servir l'application : écouter le réseau et exécuter ton code
- [ ] Gérer la base de données
> Uvicorn est un serveur ASGI. Il reçoit les requêtes HTTP et les transmet à ton application FastAPI. La validation vient de Pydantic, la documentation de FastAPI lui-même.
:::

## Installer un environnement propre

Ne installe jamais tes dépendances « globalement ». Crée un **environnement virtuel** par projet : un dossier isolé qui contient ses propres paquets.

```bash
mkdir devroad-api
cd devroad-api
python -m venv .venv
```

Active-le :

```bash
# Linux et macOS
source .venv/bin/activate

# Windows (PowerShell)
.venv\Scripts\Activate.ps1
```

Ton terminal affiche maintenant `(.venv)` au début de la ligne. Installe FastAPI avec ses extras standards, qui incluent Uvicorn :

```bash
pip install "fastapi[standard]"
pip freeze > requirements.txt
```

Le fichier `requirements.txt` garde la liste des versions installées : un collègue (ou ton futur toi sur un autre ordinateur) pourra recréer l'environnement avec `pip install -r requirements.txt`. N'oublie pas d'ajouter `.venv/` à ton `.gitignore`.

> **Astuce** : vérifie toujours que l'environnement est actif avant d'installer. Un `pip install` fait hors de l'environnement pollue ton Python système.

## Ta première application

Crée un fichier `main.py` :

```python
from fastapi import FastAPI

app = FastAPI(title="DevRoad API", version="0.1.0")


@app.get("/")
def accueil():
    return {"message": "Bienvenue sur l'API DevRoad"}
```

Trois choses à comprendre :

1. `app = FastAPI(...)` crée l'application. C'est cet objet qu'Uvicorn va servir.
2. `@app.get("/")` est un **décorateur**. Il dit : « quand une requête `GET` arrive sur `/`, appelle la fonction juste en dessous ».
3. La fonction retourne un dictionnaire Python ; FastAPI le convertit en JSON et ajoute les bons en-têtes.

Lance le serveur :

```bash
fastapi dev main.py
```

La commande `fastapi dev` démarre Uvicorn en mode développement, avec **rechargement automatique** : dès que tu sauvegardes un fichier, le serveur redémarre. Tu peux aussi utiliser directement Uvicorn :

```bash
uvicorn main:app --reload
```

Ici `main` est le nom du fichier (sans `.py`) et `app` le nom de la variable. Ouvre `http://127.0.0.1:8000` dans ton navigateur : tu vois le JSON. Teste aussi avec `curl` :

```bash
curl http://127.0.0.1:8000/
```

## La documentation automatique

Va sur `http://127.0.0.1:8000/docs`. FastAPI y a construit une interface **Swagger UI** : la liste de toutes tes routes, leurs paramètres, et un bouton « Try it out » pour les appeler depuis le navigateur. Une seconde version, plus lisible pour la lecture, existe sur `/redoc`. Le schéma brut, au format OpenAPI, est sur `/openapi.json`.

Cette documentation n'est jamais « périmée » : elle est générée à partir de ton code. Quand tu ajoutes une route ou un paramètre, elle se met à jour toute seule. Pour une équipe front-end qui consomme ton API, c'est un gain de temps considérable.

:::quiz
Tu viens d'ajouter une nouvelle route dans `main.py` et le rechargement automatique est actif. Que dois-tu faire pour la voir dans `/docs` ?
- [ ] Écrire la documentation à la main dans un fichier séparé
- [ ] Redéployer l'application sur un serveur
- [x] Rien : actualiser la page suffit, la documentation est générée depuis le code
- [ ] Installer un plugin Swagger
> FastAPI construit le schéma OpenAPI à partir des routes déclarées. Après un rechargement, la page `/docs` reflète le code actuel.
:::

## Plusieurs routes pour DevRoad

Ajoutons de vraies routes. Pour l'instant, les données vivent dans une liste Python (la base de données arrive au chapitre 4) :

```python
from fastapi import FastAPI

app = FastAPI(title="DevRoad API", version="0.1.0")

ROADMAPS = [
    {"id": 1, "titre": "Python", "niveau": "debutant"},
    {"id": 2, "titre": "FastAPI", "niveau": "intermediaire"},
    {"id": 3, "titre": "Docker", "niveau": "intermediaire"},
]


@app.get("/")
def accueil():
    return {"message": "Bienvenue sur l'API DevRoad"}


@app.get("/roadmaps")
def lister_roadmaps():
    return ROADMAPS


@app.get("/roadmaps/{roadmap_id}")
def detail_roadmap(roadmap_id: int):
    for roadmap in ROADMAPS:
        if roadmap["id"] == roadmap_id:
            return roadmap
    return {"erreur": "Roadmap introuvable"}
```

Regarde `{roadmap_id}` dans l'URL et `roadmap_id: int` dans la fonction. FastAPI relie les deux : la valeur de l'URL est **convertie en entier**. Si tu appelles `/roadmaps/abc`, FastAPI répond tout seul avec une erreur `422` expliquant que `abc` n'est pas un entier. Tu n'as écrit aucune ligne de validation : l'annotation de type suffit.

Note aussi un défaut volontaire : quand la roadmap n'existe pas, on renvoie un JSON d'erreur avec un statut `200`. C'est incorrect. Nous le corrigerons au chapitre suivant avec `HTTPException` et le code `404`.

## `def` ou `async def` ?

Tu verras les deux dans la documentation de FastAPI. Pour démarrer, retiens la règle simple :

- **`def`** : fonction classique. FastAPI l'exécute dans un pool de threads pour ne pas bloquer le serveur. C'est le bon choix si tu utilises des bibliothèques bloquantes (beaucoup de pilotes de base de données, `requests`, etc.).
- **`async def`** : coroutine. À utiliser quand tu appelles des bibliothèques asynchrones avec `await` (par exemple `httpx.AsyncClient`).

```python
import httpx


@app.get("/version-python")
async def version_python():
    async with httpx.AsyncClient() as client:
        reponse = await client.get("https://www.python.org")
    return {"statut": reponse.status_code}
```

Dans le doute, utilise `def`. Une fonction `async def` qui appelle du code bloquant gèle tout le serveur le temps de l'opération, ce qui est pire qu'un simple `def`. Nous approfondirons ce point au chapitre 6.

## Organiser le projet

Un fichier unique convient pour apprendre, pas pour un vrai projet. Voici une structure de départ que tu enrichiras dans les chapitres suivants :

```text
devroad-api/
├── .venv/
├── requirements.txt
└── app/
    ├── __init__.py
    ├── main.py          # crée l'application et branche les routeurs
    ├── data.py          # données provisoires
    └── routers/
        ├── __init__.py
        └── roadmaps.py  # routes des roadmaps
```

Dans `app/routers/roadmaps.py`, on utilise un `APIRouter` au lieu de `app` :

```python
from fastapi import APIRouter

from app.data import ROADMAPS

router = APIRouter(prefix="/roadmaps", tags=["roadmaps"])


@router.get("")
def lister_roadmaps():
    return ROADMAPS
```

Et dans `app/main.py` :

```python
from fastapi import FastAPI

from app.routers import roadmaps

app = FastAPI(title="DevRoad API", version="0.1.0")
app.include_router(roadmaps.router)


@app.get("/")
def accueil():
    return {"message": "Bienvenue sur l'API DevRoad"}
```

Le serveur se lance alors avec `fastapi dev app/main.py` ou `uvicorn app.main:app --reload`. Le paramètre `tags` regroupe les routes dans `/docs`, ce qui garde la page lisible quand l'API grandit.

## Atelier guidé : le squelette de l'API DevRoad

Compte quarante minutes.

1. Crée le dossier `devroad-api`, un environnement virtuel, active-le et installe `fastapi[standard]`.
2. Crée la structure `app/`, `app/routers/` avec leurs fichiers `__init__.py`.
3. Dans `app/data.py`, définis une liste `ROADMAPS` de quatre roadmaps (id, titre, niveau).
4. Dans `app/routers/roadmaps.py`, crée un `APIRouter` avec le préfixe `/roadmaps` et une route qui liste les roadmaps.
5. Ajoute une route `GET /roadmaps/{roadmap_id}` qui retourne une roadmap ; pour l'instant, retourne `None` si elle n'existe pas.
6. Dans `app/main.py`, crée l'application, branche le routeur et ajoute une route `GET /sante` qui retourne `{"statut": "ok"}`.
7. Lance `fastapi dev app/main.py`, ouvre `/docs` et exécute chaque route avec « Try it out ».
8. Appelle `/roadmaps/abc` et observe l'erreur `422` : lis le message, repère le champ `loc` et le champ `msg`.
9. Génère `requirements.txt` avec `pip freeze`.

Pour t'auto-évaluer, réponds sans regarder le cours : que se passe-t-il entre le moment où `curl` envoie la requête et le moment où ta fonction Python s'exécute ? Cite Uvicorn, Starlette et le décorateur.

## Erreurs fréquentes

- **Oublier d'activer l'environnement virtuel.** Le message `ModuleNotFoundError: No module named 'fastapi'` apparaît alors que tu l'as installé ailleurs.
- **Se tromper dans `uvicorn main:app`.** Le format est `fichier:variable`. Avec un dossier, utilise des points : `app.main:app`.
- **Oublier `__init__.py`.** Selon la configuration, l'import `from app.routers import roadmaps` échoue sans lui.
- **Appeler `/docs` alors que le serveur est arrêté.** Vérifie le terminal : une erreur de syntaxe stoppe le rechargement.
- **Utiliser `async def` avec du code bloquant.** Le serveur semble « figé » pendant l'appel.
- **Renvoyer des erreurs avec un statut `200`.** Un client ne peut pas deviner qu'il y a eu un problème ; on corrige cela au chapitre 2.

## Bonnes pratiques

- Un environnement virtuel par projet, et un `requirements.txt` à jour.
- Donne un `title`, une `version` et des `tags` : ta documentation en sera plus claire.
- Utilise des `APIRouter` dès le début, même pour un petit projet.
- Annote toujours les paramètres de tes fonctions : c'est ce qui déclenche la validation.
- Garde `fastapi dev` pour le développement uniquement ; la production se prépare au chapitre 6.
- Choisis `def` par défaut, `async def` seulement quand tout ce que tu appelles est asynchrone.

## À retenir

- FastAPI construit des APIs en Python à partir des **annotations de type** ; il génère la documentation automatiquement.
- Uvicorn sert l'application ; Starlette gère le web ; Pydantic gère les données.
- Un décorateur comme `@app.get("/chemin")` associe une URL et une méthode HTTP à une fonction.
- Les paramètres de chemin sont convertis selon leur type, et une valeur invalide donne une réponse `422`.
- `/docs` est une interface interactive toujours synchronisée avec ton code.
- On range les routes dans des `APIRouter` et on les branche avec `include_router`.
- En cas de doute, écris `def` ; réserve `async def` aux appels asynchrones.
