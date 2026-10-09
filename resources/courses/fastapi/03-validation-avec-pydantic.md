---
title: Validation avec Pydantic
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Une API ne doit jamais faire confiance aux données qu'elle reçoit. Un titre vide, un e-mail mal écrit, une progression de 250 % : tout cela doit être refusé avant d'atteindre ta logique métier. FastAPI délègue ce travail à **Pydantic**, une bibliothèque qui valide et convertit les données à partir des annotations de type. Ce chapitre utilise **Pydantic v2**, la version actuelle.

À la fin du chapitre, tu seras capable de :

- définir des modèles avec `BaseModel` et des types variés ;
- ajouter des contraintes avec `Field` (longueur, bornes, motif) ;
- écrire des validateurs personnalisés avec `field_validator` et `model_validator` ;
- séparer les schémas `Create`, `Update` et `Read` ;
- lire et personnaliser les erreurs `422` ;
- configurer un modèle avec `model_config` et le construire à partir d'un objet (`from_attributes`) ;
- charger des paramètres d'environnement avec `pydantic-settings`.

Prérequis : les chapitres 1 et 2. Prévois environ deux heures.

## Pourquoi valider ?

Imagine que l'API DevRoad reçoive ceci pour enregistrer la progression d'un apprenant :

```json
{"apprenant": "", "cours_id": "trois", "avancement": 250}
```

Sans validation, ton code devrait tester chaque champ à la main : est-il présent, du bon type, dans les bornes ? Ce code est long, répétitif, et on en oublie toujours un morceau. Avec Pydantic, tu **décris** la forme attendue une seule fois, et la validation est automatique.

> **À retenir** : Pydantic valide **et** convertit. La chaîne `"42"` devient l'entier `42` si le champ est de type `int`. En revanche, `"abc"` provoque une erreur.

## Premiers modèles

Un modèle est une classe qui hérite de `BaseModel`. Chaque attribut annoté est un champ :

```python
from datetime import date

from pydantic import BaseModel


class Apprenant(BaseModel):
    nom: str
    email: str
    age: int | None = None
    inscription: date
    competences: list[str] = []
```

Utilisation directe, sans FastAPI, pour comprendre le mécanisme :

```python
a = Apprenant(nom="Awa", email="awa@exemple.ci", inscription="2026-09-01")
print(a.inscription)       # datetime.date(2026, 9, 1), converti depuis le texte
print(a.model_dump())      # dictionnaire Python
print(a.model_dump_json()) # chaîne JSON

Apprenant(nom="Awa")       # lève ValidationError : email et inscription manquent
```

Types utiles : `str`, `int`, `float`, `bool`, `date`, `datetime`, `list[str]`, `dict[str, int]`, `Literal["debutant", "intermediaire"]`, `Enum`. Un champ sans valeur par défaut est **obligatoire** ; `| None = None` le rend facultatif.

Pour les e-mails, installe l'extra dédié et utilise `EmailStr` :

```bash
pip install email-validator
```

```python
from pydantic import BaseModel, EmailStr


class Inscription(BaseModel):
    email: EmailStr
```

## Contraindre avec `Field`

`Field` ajoute des règles et des métadonnées de documentation :

```python
from typing import Annotated, Literal

from pydantic import BaseModel, Field


class RoadmapCreate(BaseModel):
    titre: str = Field(min_length=3, max_length=80, examples=["Apprendre FastAPI"])
    niveau: Literal["debutant", "intermediaire", "professionnel"] = "debutant"
    description: str | None = Field(default=None, max_length=500)
    duree_heures: int = Field(default=10, ge=1, le=500)
```

Contraintes courantes :

| Contrainte | Applicable à | Sens |
| --- | --- | --- |
| `min_length`, `max_length` | texte, listes | taille minimale et maximale |
| `ge`, `gt`, `le`, `lt` | nombres | supérieur ou égal, strictement supérieur, inférieur ou égal, strictement inférieur |
| `pattern` | texte | expression régulière |
| `multiple_of` | nombres | multiple d'une valeur |

Avec `Literal`, seules les valeurs listées sont acceptées : `"expert"` produit une erreur claire, et `/docs` affiche la liste des choix. Tu peux aussi réutiliser une contrainte grâce à `Annotated` :

```python
Pourcentage = Annotated[int, Field(ge=0, le=100)]


class Progression(BaseModel):
    cours_id: int
    avancement: Pourcentage
```

:::quiz
Quel champ refuse la valeur 250 mais accepte 80 ?
- [ ] `avancement: int`
- [x] `avancement: int = Field(ge=0, le=100)`
- [ ] `avancement: str = Field(max_length=100)`
- [ ] `avancement: int = Field(min_length=0)`
> `ge` et `le` fixent des bornes pour un nombre. `min_length` et `max_length` concernent la taille d'un texte ou d'une liste.
:::

## Validateurs personnalisés

Quand une règle dépasse les contraintes simples, écris un validateur. En Pydantic v2, on utilise `field_validator` pour un champ et `model_validator` pour plusieurs champs.

```python
from pydantic import BaseModel, field_validator


class UtilisateurCreate(BaseModel):
    nom: str
    mot_de_passe: str

    @field_validator("nom")
    @classmethod
    def nettoyer_nom(cls, valeur: str) -> str:
        valeur = valeur.strip()
        if not valeur:
            raise ValueError("Le nom ne peut pas être vide")
        return valeur.title()

    @field_validator("mot_de_passe")
    @classmethod
    def verifier_mot_de_passe(cls, valeur: str) -> str:
        if len(valeur) < 8:
            raise ValueError("Au moins 8 caractères")
        if valeur.isalpha() or valeur.isdigit():
            raise ValueError("Mélange des lettres et des chiffres")
        return valeur
```

Un validateur reçoit la valeur, **la retourne** (éventuellement transformée) ou lève une `ValueError`. N'oublie ni le décorateur `@classmethod` ni le `return`.

Pour comparer deux champs, utilise `model_validator` :

```python
from datetime import date

from pydantic import BaseModel, model_validator


class Session(BaseModel):
    debut: date
    fin: date

    @model_validator(mode="after")
    def verifier_dates(self):
        if self.fin < self.debut:
            raise ValueError("La fin doit suivre le début")
        return self
```

Avec `mode="after"`, la méthode s'exécute une fois tous les champs validés individuellement ; `self` est le modèle complet.

> **Attention** : en Pydantic v1 on utilisait `@validator` et `@root_validator`. Ces anciens décorateurs sont dépréciés. Si tu lis un tutoriel qui les emploie, adapte-le avec `field_validator` et `model_validator`.

## Séparer Create, Update et Read

Un même concept (la roadmap) se présente sous plusieurs formes selon le moment. Une bonne pratique consiste à avoir un schéma par usage :

```python
from pydantic import BaseModel, ConfigDict, Field


class RoadmapBase(BaseModel):
    titre: str = Field(min_length=3, max_length=80)
    niveau: str = "debutant"
    description: str | None = None


class RoadmapCreate(RoadmapBase):
    pass


class RoadmapUpdate(BaseModel):
    titre: str | None = Field(default=None, min_length=3, max_length=80)
    niveau: str | None = None
    description: str | None = None


class RoadmapRead(RoadmapBase):
    id: int

    model_config = ConfigDict(from_attributes=True)
```

Pourquoi trois classes ?

- `Create` : ce que le client **doit** fournir. Pas d'`id`, il est généré par le serveur.
- `Update` : tous les champs sont facultatifs, pour un `PATCH`.
- `Read` : ce que l'API **renvoie**. On y ajoute l'`id` et jamais de donnée sensible.

L'héritage depuis `RoadmapBase` évite de répéter les champs communs. Cette séparation protège aussi contre les failles de **mass assignment** : un client ne peut pas envoyer un champ `est_admin` si ton schéma `Create` ne le déclare pas.

## `model_config` et `from_attributes`

`model_config` configure le comportement du modèle. Les deux réglages les plus utiles :

```python
from pydantic import BaseModel, ConfigDict


class Exemple(BaseModel):
    model_config = ConfigDict(
        from_attributes=True,   # accepter un objet (ORM) en entrée
        extra="forbid",         # refuser les champs inconnus
        str_strip_whitespace=True,  # retirer les espaces autour du texte
    )
    nom: str
```

- `from_attributes=True` permet de construire le modèle à partir d'un **objet** et non d'un dictionnaire : `RoadmapRead.model_validate(objet_sqlalchemy)`. C'est indispensable pour le chapitre 4.
- `extra="forbid"` rejette tout champ non déclaré, utile pour détecter les fautes de frappe d'un client.
- Par défaut, les champs inconnus sont simplement ignorés.

:::quiz
À quoi sert `from_attributes=True` dans `model_config` ?
- [ ] À accepter des champs supplémentaires inconnus
- [ ] À chiffrer les attributs sensibles
- [x] À construire le modèle à partir d'un objet (par exemple un modèle de base de données) et non d'un simple dictionnaire
- [ ] À rendre tous les champs facultatifs
> Avec `from_attributes=True`, Pydantic lit les attributs d'un objet. C'est ce qui permet de convertir un objet SQLAlchemy en schéma de réponse.
:::

## Comprendre et personnaliser les erreurs 422

Quand la validation échoue, FastAPI renvoie un statut `422` et un JSON détaillé :

```json
{
  "detail": [
    {
      "type": "string_too_short",
      "loc": ["body", "titre"],
      "msg": "String should have at least 3 characters",
      "input": "Go",
      "ctx": {"min_length": 3}
    }
  ]
}
```

Chaque erreur indique `loc` (où : `body`, `query`, `path`, puis le champ), `msg` (le message) et `type`. Un client front-end peut donc afficher l'erreur à côté du bon champ de formulaire.

Pour adapter le format, tu peux déclarer un gestionnaire d'exception :

```python
from fastapi import FastAPI, Request
from fastapi.exceptions import RequestValidationError
from fastapi.responses import JSONResponse

app = FastAPI()


@app.exception_handler(RequestValidationError)
async def erreurs_de_validation(request: Request, exc: RequestValidationError):
    champs = {
        ".".join(str(p) for p in e["loc"][1:]): e["msg"] for e in exc.errors()
    }
    return JSONResponse(
        status_code=422,
        content={"message": "Données invalides", "champs": champs},
    )
```

Les clés du dictionnaire sont les noms de champs, ce qui simplifie l'affichage côté interface.

## Configurer l'application avec `pydantic-settings`

Les mots de passe de base de données, les clés secrètes et les URL ne se codent jamais en dur. On les lit depuis l'environnement avec `pydantic-settings` :

```bash
pip install pydantic-settings
```

```python
from pydantic_settings import BaseSettings, SettingsConfigDict


class Settings(BaseSettings):
    model_config = SettingsConfigDict(env_file=".env")

    nom_application: str = "DevRoad API"
    database_url: str = "sqlite:///./devroad.db"
    secret_key: str
    debug: bool = False


settings = Settings()
```

Avec un fichier `.env` :

```text
SECRET_KEY=une-longue-valeur-aleatoire
DEBUG=true
```

Les noms des variables d'environnement correspondent aux champs (sans tenir compte de la casse). Les valeurs sont **validées et converties** : `DEBUG=true` devient le booléen `True`. Si `SECRET_KEY` manque, l'application refuse de démarrer avec un message clair, ce qui vaut mieux qu'une erreur étrange en production. Ajoute `.env` à `.gitignore`.

## Atelier guidé : valider la progression de DevRoad

Compte quarante-cinq minutes.

1. Dans `app/schemas.py`, définis `RoadmapBase`, `RoadmapCreate`, `RoadmapUpdate` et `RoadmapRead` comme ci-dessus.
2. Ajoute `Literal` pour le niveau (`debutant`, `intermediaire`, `professionnel`) et vérifie dans `/docs` que la liste de choix apparaît.
3. Crée le schéma `ProgressionCreate` avec `cours_id: int` et `avancement` borné entre 0 et 100.
4. Ajoute un `field_validator` sur le titre pour retirer les espaces et refuser un titre fait uniquement d'espaces.
5. Ajoute un `model_validator` sur un schéma `SessionCreate` pour exiger `fin` postérieure à `debut`.
6. Utilise ces schémas dans le routeur du chapitre 2 à la place des classes locales.
7. Envoie des données invalides avec `curl` et lis les erreurs `loc` et `msg`.
8. Crée `app/config.py` avec une classe `Settings` et un fichier `.env`, puis fais lire `settings.nom_application` par `FastAPI(title=...)`.
9. Écris un gestionnaire pour `RequestValidationError` qui renvoie un dictionnaire de messages par champ.

Auto-évaluation : explique à quelqu'un pourquoi on a trois schémas pour une même ressource, et ce qui se passerait si on n'en avait qu'un.

## Erreurs fréquentes

- **Oublier `@classmethod` sous `@field_validator`.** Le validateur ne fonctionne pas ou lève une erreur.
- **Oublier de retourner la valeur.** Le champ devient `None` après validation.
- **Utiliser les anciens `@validator` de Pydantic v1.** Passe à `field_validator`.
- **Valeur par défaut mutable partagée.** Avec Pydantic, `[]` est copié, mais dans une fonction ordinaire c'est un piège ; préfère `Field(default_factory=list)` pour être explicite.
- **Un seul schéma pour tout.** Le client pourrait envoyer un `id` ou lire un champ privé.
- **Oublier `from_attributes=True`.** La conversion d'un objet de base de données échoue au chapitre suivant.
- **Committer le fichier `.env`.** Une clé secrète publiée doit être considérée comme compromise.

## Bonnes pratiques

- Valide à la frontière : tout ce qui entre passe par un schéma.
- Un schéma par usage (`Create`, `Update`, `Read`) et un schéma de base pour factoriser.
- Préfère les contraintes déclaratives de `Field` aux validateurs lorsque c'est possible.
- Écris des messages d'erreur clairs, en français si ton public l'est.
- Utilise `Literal` ou `Enum` pour les listes de valeurs fermées.
- Centralise la configuration dans une classe `Settings` et ne la lis qu'à un seul endroit.

## À retenir

- Pydantic valide et convertit les données à partir des annotations de type.
- `Field` ajoute des contraintes (`min_length`, `ge`, `le`, `pattern`) et de la documentation.
- `field_validator` vérifie un champ ; `model_validator` vérifie plusieurs champs ensemble.
- Les schémas `Create`, `Update` et `Read` séparent entrée, modification partielle et sortie.
- `from_attributes=True` permet de lire un objet de base de données.
- Les erreurs `422` indiquent `loc`, `msg` et `type`, et se personnalisent avec un gestionnaire.
- `pydantic-settings` charge et valide la configuration depuis l'environnement.
