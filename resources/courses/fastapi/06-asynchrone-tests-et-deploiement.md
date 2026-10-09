---
title: Asynchrone, tests et déploiement
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une API qui fonctionne sur ton ordinateur n'est pas encore une API de production. Il reste trois sujets pour passer d'un prototype à un service fiable : comprendre l'**asynchrone** pour ne pas bloquer le serveur, écrire des **tests automatisés** pour modifier le code sans crainte, et **déployer** l'application proprement. Ce chapitre couvre les trois.

À la fin du chapitre, tu seras capable de :

- expliquer la différence entre `def` et `async def` et choisir le bon pour chaque route ;
- appeler un service externe sans bloquer le serveur, avec `httpx` ;
- lancer des tâches en arrière-plan avec `BackgroundTasks` ;
- écrire des tests avec `pytest` et `TestClient` ;
- isoler les tests de la vraie base grâce à `dependency_overrides` ;
- tester l'authentification et les cas d'erreur ;
- conteneuriser l'application avec Docker et préparer sa mise en production.

Prérequis : les chapitres 1 à 5 et les bases de `pytest` vues dans le cours Python (fonctions de test, `assert`). Prévois deux heures trente.

## Comprendre l'asynchrone

Un serveur reçoit des dizaines de requêtes en même temps. La plupart du temps, une requête **attend** : la réponse de la base, d'un service de paiement, d'un disque. Pendant cette attente, le processeur ne fait rien. L'asynchrone permet de **servir d'autres requêtes pendant qu'une requête attend**.

En Python, cela repose sur `async def` et `await` :

```python
import asyncio


async def telecharger(nom: str, secondes: float) -> str:
    await asyncio.sleep(secondes)  # attente qui libère la boucle d'événements
    return f"{nom} terminé"


async def main():
    resultats = await asyncio.gather(
        telecharger("a", 1),
        telecharger("b", 1),
        telecharger("c", 1),
    )
    print(resultats)  # environ 1 seconde au total, pas 3


asyncio.run(main())
```

Les trois attentes se chevauchent : la durée totale est celle de la plus longue. `await` marque les endroits où le programme peut céder la main à une autre tâche. Un seul fil d'exécution, la **boucle d'événements** (*event loop*), orchestre tout cela.

> **À retenir** : l'asynchrone accélère les programmes qui **attendent** (réseau, disque, base). Il n'accélère pas les calculs lourds, qui occupent le processeur.

## `def` ou `async def` dans FastAPI ?

FastAPI accepte les deux, et il agit différemment selon ton choix :

| Déclaration | Où elle s'exécute | À utiliser quand |
| --- | --- | --- |
| `async def` | Directement dans la boucle d'événements | Tu utilises `await` avec des bibliothèques asynchrones (`httpx`, pilotes async) |
| `def` | Dans un pool de threads, hors de la boucle | Ton code est bloquant (SQLAlchemy synchrone, `requests`, lecture de fichiers) |

La règle d'or : **ne bloque jamais la boucle d'événements**. Voici l'erreur classique :

```python
import time

from fastapi import FastAPI

app = FastAPI()


@app.get("/mauvais")
async def mauvais():
    time.sleep(5)  # BLOQUE toute l'application pendant 5 secondes
    return {"ok": True}


@app.get("/bon")
def bon():
    time.sleep(5)  # tourne dans un thread, les autres requêtes continuent
    return {"ok": True}
```

Dans `/mauvais`, `time.sleep` est bloquant et tourne *dans* la boucle : pendant 5 secondes, **aucune autre requête** n'est traitée. Dans `/bon`, FastAPI exécute la fonction dans un thread séparé, et le serveur reste réactif.

Comme ton accès à la base utilise SQLAlchemy en mode synchrone, tes routes avec `db: DbSession` doivent rester des `def` simples. C'est un choix tout à fait valable et très répandu. SQLAlchemy propose aussi un mode asynchrone (`create_async_engine`, `AsyncSession`) avec des pilotes dédiés comme `asyncpg`, mais il demande de tout écrire en `async`/`await` : n'y passe que si tu en as réellement besoin.

:::quiz
Une route déclarée `async def` appelle `requests.get(...)` (bibliothèque bloquante). Quel est le risque ?
- [ ] La route renvoie une erreur de syntaxe
- [ ] Python convertit automatiquement l'appel en asynchrone
- [x] La boucle d'événements est bloquée et les autres requêtes attendent
- [ ] La requête est exécutée deux fois
> Un appel bloquant dans une route `async def` gèle la boucle d'événements. Utilise `def`, ou un client asynchrone comme `httpx.AsyncClient`.
:::

## Appeler un service externe avec `httpx`

`httpx` offre un client HTTP synchrone et asynchrone. Pour appeler une API externe (par exemple un service de notification) depuis une route `async def` :

```python
import httpx
from fastapi import APIRouter, HTTPException

router = APIRouter(prefix="/externe", tags=["externe"])


@router.get("/citation")
async def citation():
    try:
        async with httpx.AsyncClient(timeout=5.0) as client:
            reponse = await client.get("https://api.exemple.com/citation-du-jour")
            reponse.raise_for_status()
    except httpx.HTTPError:
        raise HTTPException(status_code=502, detail="Service externe indisponible")
    return reponse.json()
```

Deux réflexes à garder : toujours définir un **timeout** (sinon une requête peut attendre indéfiniment) et traduire les échecs du service tiers en un code clair (`502 Bad Gateway` ou `504 Gateway Timeout`) au lieu de laisser planter l'API.

## Tâches en arrière-plan

Certaines actions n'ont pas besoin de retarder la réponse : envoyer un e-mail de bienvenue, écrire un journal, recalculer des statistiques. `BackgroundTasks` les exécute **après** l'envoi de la réponse :

```python
from fastapi import BackgroundTasks


def envoyer_bienvenue(email: str) -> None:
    print(f"Envoi de l'e-mail de bienvenue à {email}")  # remplace par un vrai envoi


@router.post("/inscription", status_code=201)
def inscrire(donnees: UtilisateurCreate, db: DbSession, taches: BackgroundTasks):
    utilisateur = creer_utilisateur(db, donnees)
    taches.add_task(envoyer_bienvenue, utilisateur.email)
    return utilisateur
```

C'est idéal pour des tâches courtes. Pour des traitements longs ou qui doivent survivre à un redémarrage, utilise une vraie file de tâches (Celery, RQ, ARQ) : `BackgroundTasks` s'exécute dans le même processus et ne garantit rien si celui-ci s'arrête.

## Pourquoi tester ?

Un test automatisé est un petit programme qui vérifie qu'un morceau de code se comporte comme prévu. Sans tests, chaque modification est un pari : tu changes une route, et tu ne sais pas si tu as cassé la connexion. Avec une suite de tests, tu lances une commande et tu as la réponse en quelques secondes.

Installe les outils :

```bash
pip install pytest httpx
```

`TestClient` de FastAPI s'appuie sur `httpx` : il appelle ton application **sans démarrer de serveur**, directement en mémoire.

## Premier test

Crée un dossier `tests/` avec un fichier `test_accueil.py` :

```python
from fastapi.testclient import TestClient

from app.main import app

client = TestClient(app)


def test_accueil():
    reponse = client.get("/")
    assert reponse.status_code == 200
    assert reponse.json() == {"message": "Bienvenue sur l'API DevRoad"}
```

Lance les tests :

```bash
pytest -v
```

`pytest` découvre automatiquement les fichiers nommés `test_*.py` et les fonctions nommées `test_*`. Un test réussit s'il ne lève aucune exception ; un `assert` faux le fait échouer et `pytest` affiche précisément les valeurs comparées.

## Isoler les tests de la vraie base

Un test ne doit **jamais** toucher ta base de développement : il polluerait tes données et dépendrait de leur état. On utilise une base temporaire en mémoire, et on **remplace** la dépendance `get_db` pour que l'application l'utilise. Crée `tests/conftest.py` :

```python
import pytest
from fastapi.testclient import TestClient
from sqlalchemy import create_engine
from sqlalchemy.orm import sessionmaker
from sqlalchemy.pool import StaticPool

from app.database import Base, get_db
from app.main import app


@pytest.fixture()
def client():
    moteur = create_engine(
        "sqlite://",                       # base en mémoire
        connect_args={"check_same_thread": False},
        poolclass=StaticPool,              # une seule connexion partagée
    )
    Base.metadata.create_all(bind=moteur)
    SessionTest = sessionmaker(bind=moteur, autoflush=False, expire_on_commit=False)

    def get_db_test():
        db = SessionTest()
        try:
            yield db
        finally:
            db.close()

    app.dependency_overrides[get_db] = get_db_test
    with TestClient(app) as c:
        yield c

    app.dependency_overrides.clear()
    Base.metadata.drop_all(bind=moteur)
```

Plusieurs notions sont à l'œuvre :

- une **fixture** (`@pytest.fixture`) prépare un décor réutilisable. Tout test qui déclare un paramètre `client` le reçoit automatiquement ;
- `yield` sépare la préparation (avant) du nettoyage (après) ;
- `app.dependency_overrides` remplace une dépendance par une autre : c'est la récompense d'avoir utilisé `Depends` pour la session ;
- chaque test obtient une base **vierge**, donc les tests ne s'influencent pas.

> **Astuce** : `conftest.py` est un fichier spécial de `pytest`. Ses fixtures sont disponibles dans tous les tests du dossier, sans import.

## Tester le CRUD

```python
def test_creer_et_lire_une_roadmap(client):
    creation = client.post("/roadmaps", json={"titre": "Apprendre FastAPI"})
    assert creation.status_code == 201
    identifiant = creation.json()["id"]

    lecture = client.get(f"/roadmaps/{identifiant}")
    assert lecture.status_code == 200
    assert lecture.json()["titre"] == "Apprendre FastAPI"


def test_roadmap_introuvable(client):
    reponse = client.get("/roadmaps/999")
    assert reponse.status_code == 404


def test_titre_trop_court_refuse(client):
    reponse = client.post("/roadmaps", json={"titre": "ab"})
    assert reponse.status_code == 422
```

Un bon test suit le schéma **Arrange, Act, Assert** : préparer les données, exécuter l'action, vérifier le résultat. Teste aussi les cas d'échec : une API fiable se juge souvent à la façon dont elle refuse les mauvaises requêtes.

Pour éviter la répétition, regroupe des cas similaires avec `parametrize` :

```python
import pytest


@pytest.mark.parametrize("titre", ["", "a", "ab", "x" * 81])
def test_titres_invalides(client, titre):
    reponse = client.post("/roadmaps", json={"titre": titre})
    assert reponse.status_code == 422
```

:::quiz
À quoi sert `app.dependency_overrides[get_db] = get_db_test` dans les tests ?
- [ ] À supprimer la route `get_db` de l'application
- [x] À remplacer la session de production par une session sur une base de test
- [ ] À accélérer FastAPI en désactivant la validation
- [ ] À créer automatiquement les routes manquantes
> Ce mécanisme substitue une dépendance par une autre pendant les tests, ce qui permet d'utiliser une base temporaire sans modifier le code de l'application.
:::

## Tester l'authentification

Pour les routes protégées, crée une fixture qui inscrit un utilisateur, se connecte, et fournit l'en-tête prêt à l'emploi :

```python
@pytest.fixture()
def entetes_auth(client):
    client.post("/auth/inscription", json={"email": "awa@exemple.ci", "mot_de_passe": "motdepasse123"})
    reponse = client.post(
        "/auth/connexion",
        data={"username": "awa@exemple.ci", "password": "motdepasse123"},
    )
    jeton = reponse.json()["access_token"]
    return {"Authorization": f"Bearer {jeton}"}


def test_route_protegee_sans_jeton(client):
    assert client.post("/roadmaps", json={"titre": "Test"}).status_code == 401


def test_route_protegee_avec_jeton(client, entetes_auth):
    reponse = client.post("/roadmaps", json={"titre": "Test"}, headers=entetes_auth)
    assert reponse.status_code == 201
```

Remarque : la connexion utilise `data=` (formulaire) et non `json=`, car `OAuth2PasswordRequestForm` attend un formulaire. Ajoute aussi des tests pour un mauvais mot de passe (401) et pour un utilisateur sans le rôle admin qui tente une suppression (403).

## Mesurer la couverture

La **couverture** indique quelles lignes de ton code sont exécutées par les tests :

```bash
pip install pytest-cov
pytest --cov=app --cov-report=term-missing
```

Une couverture élevée ne garantit pas la qualité, mais une couverture très basse signale des zones jamais vérifiées. Vise d'abord les parties critiques : authentification, permissions, calculs de progression.

## Préparer la production

### Configuration par variables d'environnement

Ton application lit déjà ses réglages via `pydantic-settings`. En production, ne copie pas `.env` sur le serveur : définis les variables dans l'environnement de la plateforme (`DATABASE_URL`, `SECRET_KEY`, origines CORS). Ajoute une route de santé que les plateformes peuvent interroger :

```python
@app.get("/sante", tags=["technique"])
def sante():
    return {"statut": "ok"}
```

### Conteneuriser avec Docker

Docker emballe l'application et ses dépendances dans une image identique partout. Crée un `Dockerfile` à la racine :

```dockerfile
FROM python:3.12-slim

WORKDIR /code

COPY requirements.txt .
RUN pip install --no-cache-dir -r requirements.txt

COPY app ./app
COPY migrations ./migrations
COPY alembic.ini .

EXPOSE 8000
CMD ["sh", "-c", "alembic upgrade head && uvicorn app.main:app --host 0.0.0.0 --port 8000"]
```

L'ordre des instructions est volontaire : on copie d'abord `requirements.txt` et on installe les dépendances, **puis** le code. Tant que les dépendances ne changent pas, Docker réutilise sa couche en cache et la construction reste rapide. Ajoute un `.dockerignore` listant `.venv`, `.env`, `.git` et `__pycache__`.

```bash
docker build -t devroad-api .
docker run -p 8000:8000 --env-file .env devroad-api
```

Pour associer l'API à PostgreSQL en local, un fichier `docker-compose.yml` déclare les deux services. Le cours Docker détaille cette étape.

### Lancer le serveur en production

En développement, `fastapi dev` recharge le code à chaque modification. En production :

- lance `uvicorn` sans `--reload` ;
- place l'application derrière un **proxy inverse** (Nginx, Caddy ou celui de ta plateforme) qui gère HTTPS ;
- exécute plusieurs processus (`--workers 2` ou plus) pour exploiter plusieurs cœurs ;
- applique les migrations Alembic **avant** de démarrer la nouvelle version.

Des plateformes comme Render, Railway ou Fly.io déploient directement une image Docker ou un dépôt Git, avec une base PostgreSQL managée. Un VPS classique convient aussi si tu veux garder la main.

> **Attention** : désactive la documentation interactive si elle ne doit pas être publique, avec `FastAPI(docs_url=None, redoc_url=None)`, ou protège-la derrière une authentification.

## Atelier guidé : tester et conteneuriser DevRoad

Compte environ une heure trente.

1. Installe `pytest`, `httpx` et `pytest-cov`. Crée le dossier `tests/` et le fichier `conftest.py` avec la fixture `client` utilisant une base en mémoire.
2. Écris `test_accueil` et vérifie qu'il passe avec `pytest -v`.
3. Écris les tests du CRUD des roadmaps : création, lecture, liste, modification partielle, suppression et 404.
4. Ajoute un test `parametrize` couvrant plusieurs titres invalides.
5. Crée la fixture `entetes_auth` et teste les routes protégées avec et sans jeton.
6. Teste un mot de passe incorrect (401) et la suppression par un non-administrateur (403).
7. Lance `pytest --cov=app --cov-report=term-missing` et note deux zones non couvertes à traiter.
8. Ajoute la route `/sante`, puis écris le `Dockerfile` et le `.dockerignore`.
9. Construis l'image, lance le conteneur avec `--env-file` et vérifie `/sante` depuis ton navigateur.

Pour t'auto-évaluer : explique pourquoi une route qui utilise SQLAlchemy synchrone doit être un `def`, et pourquoi un test ne doit jamais utiliser la base de développement.

## Erreurs fréquentes

- **Bloquer la boucle d'événements** avec `time.sleep` ou `requests` dans une route `async def`.
- **Oublier `await`** : on obtient un objet coroutine au lieu du résultat, avec un avertissement.
- **Tester contre la vraie base** : les tests deviennent dépendants de l'état des données.
- **Tests qui s'influencent** : ne pas nettoyer entre deux tests provoque des échecs aléatoires.
- **Envoyer `json=` à la route de connexion** au lieu de `data=` dans les tests.
- **Appels externes sans timeout** : une API tierce lente fige tes requêtes.
- **Copier `.env` dans l'image Docker** : les secrets se retrouvent dans l'image.
- **Lancer `--reload` en production**, ce qui est lent et inutile.

## Bonnes pratiques

- Choisis `def` pour du code bloquant, `async def` seulement si tout ce que tu appelles est asynchrone.
- Définis un timeout et gère les erreurs pour chaque appel à un service externe.
- Un test, un comportement : des noms explicites comme `test_titre_trop_court_refuse`.
- Teste les cas d'échec autant que les cas de succès.
- Exécute les tests automatiquement à chaque `push` avec GitHub Actions.
- Garde les images Docker petites (`slim`) et ordonne les couches pour profiter du cache.
- Applique les migrations avant de démarrer la nouvelle version, jamais à la main en urgence.
- Ajoute une route de santé et surveille les journaux.

## À retenir

- L'asynchrone sert à ne pas rester inactif pendant les attentes ; il ne rend pas les calculs plus rapides.
- `async def` s'exécute dans la boucle d'événements, `def` dans un thread : ne bloque jamais la boucle.
- `httpx.AsyncClient` appelle des services externes, avec timeout et gestion d'erreurs.
- `BackgroundTasks` convient aux petites tâches après la réponse, pas aux traitements critiques.
- `TestClient` et `pytest` testent l'API sans serveur ; les fixtures préparent le décor.
- `dependency_overrides` permet de substituer la base par une base de test.
- Docker, des variables d'environnement, un proxy HTTPS et des migrations automatiques forment la base d'un déploiement sérieux.
