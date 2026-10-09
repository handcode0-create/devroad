---
title: Authentification et sécurité
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une API publique sans contrôle d'accès est une porte ouverte : n'importe qui peut lire, modifier ou supprimer les données. Ce chapitre ajoute à l'API DevRoad un système d'inscription et de connexion, des routes protégées et quelques protections de base. Tu t'appuieras sur les **dépendances** de FastAPI, l'un de ses mécanismes les plus puissants.

À la fin du chapitre, tu seras capable de :

- distinguer **authentification** (qui es-tu ?) et **autorisation** (as-tu le droit ?) ;
- écrire et réutiliser des dépendances avec `Depends` ;
- hacher et vérifier un mot de passe sans jamais le stocker en clair ;
- émettre et vérifier un jeton **JWT** ;
- protéger des routes avec le schéma OAuth2 « password » et le bouton *Authorize* de `/docs` ;
- gérer des rôles (apprenant, administrateur) ;
- configurer **CORS** et appliquer les réflexes de sécurité essentiels.

Prérequis : les chapitres 1 à 4 (en particulier la session SQLAlchemy et les schémas Pydantic). Prévois deux heures trente.

## Authentification et autorisation

Ces deux mots sont souvent confondus :

| Notion | Question posée | Exemple |
| --- | --- | --- |
| Authentification | Qui es-tu ? | Vérifier l'e-mail et le mot de passe, émettre un jeton |
| Autorisation | As-tu le droit de le faire ? | Seul un administrateur peut supprimer une roadmap |

Une API est **sans état** (*stateless*) : le serveur ne se souvient pas de toi d'une requête à l'autre. Il faut donc que chaque requête prouve son identité. Le schéma le plus courant : le client se connecte une fois, reçoit un **jeton** (*token*), puis l'envoie dans l'en-tête `Authorization` de chaque requête suivante :

```text
Authorization: Bearer eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9...
```

## Les dépendances : le cœur de FastAPI

Une **dépendance** est une fonction que FastAPI exécute *avant* ta route, dont il injecte le résultat dans tes paramètres. Tu en as déjà utilisé une avec `get_db`. Voici une dépendance simple :

```python
from typing import Annotated

from fastapi import Depends, FastAPI

app = FastAPI()


def pagination(limite: int = 10, decalage: int = 0) -> dict:
    return {"limite": limite, "decalage": decalage}


Pagination = Annotated[dict, Depends(pagination)]


@app.get("/cours")
def lister_cours(page: Pagination):
    return {"page": page}
```

Les paramètres `limite` et `decalage` de la dépendance deviennent des paramètres de requête de la route, et apparaissent dans `/docs`. Les dépendances peuvent elles-mêmes dépendre d'autres dépendances : FastAPI résout toute la chaîne. C'est ce mécanisme qui permettra d'écrire « cette route exige un utilisateur connecté » en une seule ligne.

> **À retenir** : une dépendance factorise du code répété (session, pagination, utilisateur courant) et le rend testable, puisqu'on peut la remplacer dans les tests.

## Stocker les utilisateurs

Ajoute un modèle `Utilisateur` dans `app/models.py` :

```python
class Utilisateur(Base):
    __tablename__ = "utilisateurs"

    id: Mapped[int] = mapped_column(primary_key=True)
    email: Mapped[str] = mapped_column(String(255), unique=True, index=True)
    mot_de_passe_hache: Mapped[str] = mapped_column(String(255))
    role: Mapped[str] = mapped_column(String(20), default="apprenant")
    actif: Mapped[bool] = mapped_column(default=True)
```

Remarque le nom de la colonne : `mot_de_passe_hache`. On ne stocke **jamais** un mot de passe en clair, ni chiffré de façon réversible. Si la base fuit, les mots de passe ne doivent pas fuiter avec elle.

Crée la migration avec Alembic (`alembic revision --autogenerate -m "utilisateurs"` puis `alembic upgrade head`).

## Hacher les mots de passe

Un **hachage** est une transformation à sens unique : on peut calculer l'empreinte d'un mot de passe, mais on ne peut pas retrouver le mot de passe depuis l'empreinte. On utilise des algorithmes volontairement lents (Argon2, bcrypt) pour décourager les attaques par force brute. Installe la bibliothèque :

```bash
pip install "pwdlib[argon2]" pyjwt
```

Crée `app/security.py` :

```python
from pwdlib import PasswordHash

hasher = PasswordHash.recommended()


def hacher(mot_de_passe: str) -> str:
    return hasher.hash(mot_de_passe)


def verifier(mot_de_passe: str, empreinte: str) -> bool:
    return hasher.verify(mot_de_passe, empreinte)
```

Chaque appel à `hacher("secret")` produit une empreinte différente, car l'algorithme ajoute un **sel** aléatoire, stocké dans l'empreinte elle-même. C'est pourquoi on compare avec `verifier` et jamais avec `==`.

:::quiz
Pourquoi stocke-t-on l'empreinte d'un mot de passe plutôt que le mot de passe chiffré ?
- [ ] Parce que l'empreinte est plus courte à stocker
- [ ] Parce que le chiffrement n'existe pas en Python
- [x] Parce qu'une empreinte ne peut pas être inversée pour retrouver le mot de passe
- [ ] Parce que l'empreinte est identique pour tous les utilisateurs
> Un chiffrement est réversible avec la clé, un hachage ne l'est pas. En cas de fuite de la base, les mots de passe restent protégés.
:::

## Comprendre le jeton JWT

Un **JWT** (*JSON Web Token*) est une chaîne de trois parties séparées par des points : un en-tête, une **charge utile** (*payload*) contenant des informations, et une **signature**. Le serveur signe le jeton avec une clé secrète ; en le recevant, il vérifie la signature et sait que le contenu n'a pas été altéré.

La charge utile contient des « revendications » (*claims*) :

- `sub` (*subject*) : l'identifiant de l'utilisateur ;
- `exp` (*expiration*) : la date après laquelle le jeton est refusé.

> **Attention** : la charge utile d'un JWT est **encodée, pas chiffrée**. N'importe qui peut la lire. N'y mets jamais de mot de passe ni de donnée sensible. La signature garantit l'intégrité, pas la confidentialité.

Ajoute la configuration dans `app/config.py` (avec `pydantic-settings`) : une clé secrète longue et aléatoire, lue depuis le fichier `.env`.

```python
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env")

    database_url: str = "sqlite:///./devroad.db"
    secret_key: str
    algorithme: str = "HS256"
    duree_jeton_minutes: int = 30


settings = Settings()
```

Génère une clé avec `python -c "import secrets; print(secrets.token_hex(32))"` et place-la dans `.env` (`SECRET_KEY=...`). Ajoute `.env` à ton `.gitignore` : une clé committée est une clé compromise.

Complète `app/security.py` :

```python
from datetime import datetime, timedelta, timezone

import jwt

from app.config import settings


def creer_jeton(utilisateur_id: int) -> str:
    expiration = datetime.now(timezone.utc) + timedelta(minutes=settings.duree_jeton_minutes)
    charge = {"sub": str(utilisateur_id), "exp": expiration}
    return jwt.encode(charge, settings.secret_key, algorithm=settings.algorithme)


def lire_jeton(jeton: str) -> int | None:
    try:
        charge = jwt.decode(jeton, settings.secret_key, algorithms=[settings.algorithme])
        return int(charge["sub"])
    except (jwt.InvalidTokenError, KeyError, ValueError):
        return None
```

`jwt.decode` vérifie automatiquement la signature **et** l'expiration. Précise toujours `algorithms=[...]` explicitement : ne laisse jamais le jeton choisir l'algorithme qui servira à le vérifier.

## Inscription et connexion

Définis les schémas :

```python
class UtilisateurCreate(BaseModel):
    email: EmailStr
    mot_de_passe: str = Field(min_length=8, max_length=128)


class UtilisateurRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    email: EmailStr
    role: str


class Jeton(BaseModel):
    access_token: str
    token_type: str = "bearer"
```

`UtilisateurRead` ne contient pas le mot de passe haché : il ne doit jamais sortir de l'API. Crée maintenant `app/routers/auth.py` :

```python
from typing import Annotated

from fastapi import APIRouter, Depends, HTTPException, status
from fastapi.security import OAuth2PasswordRequestForm
from sqlalchemy import select

from app.database import DbSession
from app.models import Utilisateur
from app.schemas import Jeton, UtilisateurCreate, UtilisateurRead
from app.security import creer_jeton, hacher, verifier

router = APIRouter(prefix="/auth", tags=["authentification"])


@router.post("/inscription", response_model=UtilisateurRead, status_code=status.HTTP_201_CREATED)
def inscrire(donnees: UtilisateurCreate, db: DbSession):
    existant = db.scalar(select(Utilisateur).where(Utilisateur.email == donnees.email))
    if existant:
        raise HTTPException(status_code=409, detail="Cet e-mail est déjà utilisé")
    utilisateur = Utilisateur(
        email=donnees.email,
        mot_de_passe_hache=hacher(donnees.mot_de_passe),
    )
    db.add(utilisateur)
    db.commit()
    db.refresh(utilisateur)
    return utilisateur


@router.post("/connexion", response_model=Jeton)
def connecter(formulaire: Annotated[OAuth2PasswordRequestForm, Depends()], db: DbSession):
    utilisateur = db.scalar(select(Utilisateur).where(Utilisateur.email == formulaire.username))
    if not utilisateur or not verifier(formulaire.password, utilisateur.mot_de_passe_hache):
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Identifiants incorrects",
            headers={"WWW-Authenticate": "Bearer"},
        )
    return Jeton(access_token=creer_jeton(utilisateur.id))
```

Trois choix délibérés :

- `OAuth2PasswordRequestForm` lit un formulaire standard avec les champs `username` et `password` (le champ s'appelle `username` même si tu y mets un e-mail). C'est ce format qu'attend le bouton *Authorize* de `/docs` ;
- le message d'erreur est identique que l'e-mail n'existe pas ou que le mot de passe soit faux : on ne révèle pas quels comptes existent ;
- l'inscription renvoie `409 Conflict` pour un e-mail déjà pris.

## Protéger les routes : l'utilisateur courant

Voici la dépendance centrale. Crée `app/dependances.py` :

```python
from typing import Annotated

from fastapi import Depends, HTTPException, status
from fastapi.security import OAuth2PasswordBearer

from app.database import DbSession
from app.models import Utilisateur
from app.security import lire_jeton

oauth2_scheme = OAuth2PasswordBearer(tokenUrl="/auth/connexion")


def utilisateur_courant(
    jeton: Annotated[str, Depends(oauth2_scheme)],
    db: DbSession,
) -> Utilisateur:
    utilisateur_id = lire_jeton(jeton)
    utilisateur = db.get(Utilisateur, utilisateur_id) if utilisateur_id else None
    if utilisateur is None or not utilisateur.actif:
        raise HTTPException(
            status_code=status.HTTP_401_UNAUTHORIZED,
            detail="Jeton invalide ou expiré",
            headers={"WWW-Authenticate": "Bearer"},
        )
    return utilisateur


UtilisateurConnecte = Annotated[Utilisateur, Depends(utilisateur_courant)]
```

`OAuth2PasswordBearer` extrait le jeton de l'en-tête `Authorization` et déclare le schéma de sécurité dans OpenAPI : `/docs` affiche alors un cadenas et le bouton *Authorize*. Il suffit ensuite d'ajouter `UtilisateurConnecte` à une route pour la protéger :

```python
@router.get("/moi", response_model=UtilisateurRead)
def profil(utilisateur: UtilisateurConnecte):
    return utilisateur


@router.post("", response_model=RoadmapRead, status_code=201)
def creer(donnees: RoadmapCreate, db: DbSession, utilisateur: UtilisateurConnecte):
    roadmap = Roadmap(**donnees.model_dump(), auteur_id=utilisateur.id)
    ...
```

Sans jeton valide, FastAPI répond `401 Unauthorized` avant même d'entrer dans ta fonction.

:::quiz
Que se passe-t-il quand un client appelle une route protégée par `UtilisateurConnecte` sans en-tête `Authorization` ?
- [ ] La route s'exécute avec un utilisateur vide
- [x] FastAPI répond 401 sans exécuter le corps de la route
- [ ] FastAPI répond 404 car la route est cachée
- [ ] La route s'exécute mais retourne une liste vide
> `OAuth2PasswordBearer` lève une erreur 401 quand le jeton est absent, avant l'exécution de la route.
:::

## Autorisation : les rôles

Pour limiter une action aux administrateurs, crée une dépendance qui s'appuie sur la précédente :

```python
def administrateur(utilisateur: UtilisateurConnecte) -> Utilisateur:
    if utilisateur.role != "admin":
        raise HTTPException(status_code=status.HTTP_403_FORBIDDEN, detail="Accès réservé aux administrateurs")
    return utilisateur


Admin = Annotated[Utilisateur, Depends(administrateur)]


@router.delete("/{roadmap_id}", status_code=204)
def supprimer(roadmap_id: int, db: DbSession, _: Admin):
    ...
```

Retiens la différence entre les deux codes :

- **401** : tu n'es pas identifié (jeton absent, invalide ou expiré) ;
- **403** : tu es identifié, mais tu n'as pas le droit.

Pense aussi à l'autorisation **par ressource** : un apprenant doit pouvoir modifier *sa* progression, pas celle d'un autre. Compare toujours `ressource.utilisateur_id` à `utilisateur.id` avant d'agir.

## CORS : autoriser le navigateur

Quand ton interface React (sur `http://localhost:5173`) appelle l'API (sur `http://localhost:8000`), le navigateur bloque la requête par sécurité : les origines diffèrent. Le serveur doit déclarer explicitement qui a le droit de l'appeler :

```python
from fastapi.middleware.cors import CORSMiddleware

app.add_middleware(
    CORSMiddleware,
    allow_origins=["http://localhost:5173", "https://devroad.exemple.ci"],
    allow_credentials=True,
    allow_methods=["GET", "POST", "PATCH", "DELETE"],
    allow_headers=["Authorization", "Content-Type"],
)
```

> **Erreur fréquente** : mettre une origine générique (le caractère étoile) dans `allow_origins` avec `allow_credentials=True`. C'est refusé par les navigateurs et dangereux. Liste précisément les origines autorisées.

CORS protège le **navigateur de l'utilisateur**, pas ton serveur : un script ou un outil comme `curl` ne le respecte pas. Il ne remplace donc jamais l'authentification.

## Réflexes de sécurité

- **HTTPS partout** en production : un jeton envoyé en clair peut être intercepté.
- **Durée de vie courte** pour le jeton d'accès, avec un jeton de rafraîchissement si besoin.
- **Secrets hors du dépôt** : `.env` ignoré par Git, variables d'environnement sur le serveur.
- **Validation stricte** des entrées avec Pydantic (longueurs, formats, `extra="forbid"` pour les schémas sensibles).
- **Limitation du débit** (*rate limiting*) sur `/auth/connexion` pour freiner les essais répétés ; une bibliothèque comme `slowapi` ou un proxy en amont s'en charge.
- **Messages d'erreur neutres** : ne dis pas si c'est l'e-mail ou le mot de passe qui est faux.
- **Principe du moindre privilège** : donne à chaque rôle uniquement ce dont il a besoin.

## Atelier guidé : sécuriser l'API DevRoad

Compte environ une heure trente.

1. Installe `pwdlib[argon2]` et `pyjwt`, crée `app/config.py` et un fichier `.env` avec une `SECRET_KEY` générée. Ajoute `.env` au `.gitignore`.
2. Ajoute le modèle `Utilisateur`, génère et applique la migration Alembic.
3. Écris `app/security.py` avec `hacher`, `verifier`, `creer_jeton` et `lire_jeton`.
4. Crée le routeur `auth` avec `/auth/inscription` et `/auth/connexion`, puis branche-le dans `main.py`.
5. Écris la dépendance `utilisateur_courant` et la route `GET /auth/moi`.
6. Dans `/docs`, inscris-toi, clique sur *Authorize*, connecte-toi, puis appelle `/auth/moi`.
7. Protège `POST`, `PATCH` et `DELETE` des roadmaps avec `UtilisateurConnecte`, en laissant les `GET` publics.
8. Ajoute le rôle `admin` : seule cette catégorie peut supprimer une roadmap. Teste avec deux comptes.
9. Configure CORS pour l'origine de ton front et vérifie qu'un `curl` avec un jeton expiré renvoie `401`.

Pour t'auto-évaluer : explique pourquoi le message d'erreur de connexion est volontairement vague, et la différence entre les codes 401 et 403.

## Erreurs fréquentes

- **Stocker le mot de passe en clair** ou utiliser un hachage rapide (MD5, SHA-1).
- **Committer la clé secrète** ou la laisser à une valeur par défaut comme `"secret"`.
- **Mettre des données sensibles dans le JWT.** La charge utile est lisible par tous.
- **Oublier `algorithms=[...]`** dans `jwt.decode`.
- **Confondre 401 et 403**, ce qui complique le travail du client.
- **Renvoyer le mot de passe haché** dans une réponse parce qu'on a oublié le `response_model`.
- **Protéger la route mais pas la ressource** : un utilisateur connecté modifie les données d'un autre.
- **Autoriser toutes les origines (l'étoile) dans `allow_origins`** avec des cookies ou des jetons en production.

## Bonnes pratiques

- Mets l'authentification dans des dépendances réutilisables plutôt que de la recopier dans chaque route.
- Utilise des alias `Annotated` (`UtilisateurConnecte`, `Admin`) pour garder les signatures lisibles.
- Teste les cas négatifs : sans jeton, jeton expiré, mauvais rôle, ressource d'un autre utilisateur.
- Garde les durées d'expiration courtes et configurables.
- Journalise les échecs de connexion, sans consigner les mots de passe.
- Fais auditer ou relire le code d'authentification : c'est la partie où une erreur coûte le plus cher.

## À retenir

- Authentification : prouver son identité ; autorisation : vérifier ses droits.
- Une dépendance est une fonction injectée avec `Depends`, composable et remplaçable dans les tests.
- On ne stocke jamais un mot de passe : seulement son empreinte (Argon2 ou bcrypt).
- Un JWT est signé, pas chiffré : il prouve l'intégrité, pas la confidentialité.
- `OAuth2PasswordBearer` et une dépendance `utilisateur_courant` protègent une route en une ligne.
- 401 signifie non identifié, 403 signifie identifié mais interdit.
- CORS gère l'accès depuis les navigateurs ; il ne remplace pas l'authentification.
