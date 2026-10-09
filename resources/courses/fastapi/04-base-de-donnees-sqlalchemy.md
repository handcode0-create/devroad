---
title: Base de données avec SQLAlchemy
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, ton API stocke ses roadmaps dans une liste Python. Dès que le serveur redémarre, tout disparaît. Ce chapitre remplace cette liste par une vraie base de données grâce à **SQLAlchemy 2**, l'ORM (*Object-Relational Mapper*) le plus utilisé en Python. Tu manipuleras des classes Python au lieu d'écrire du SQL à la main, tout en gardant le contrôle sur les requêtes.

À la fin du chapitre, tu seras capable de :

- créer un moteur (`engine`) et une session SQLAlchemy ;
- déclarer des modèles avec la syntaxe moderne `Mapped` et `mapped_column` ;
- définir des relations un-à-plusieurs et plusieurs-à-plusieurs ;
- écrire des requêtes avec `select()` et les exécuter avec une session ;
- injecter la session dans tes routes avec `Depends` ;
- brancher les modèles SQLAlchemy sur tes schémas Pydantic grâce à `from_attributes` ;
- faire évoluer le schéma avec Alembic.

Prérequis : avoir suivi les chapitres précédents (routes, CRUD, Pydantic) et connaître les bases de SQL (`SELECT`, `INSERT`, clés étrangères). Si les classes Python te posent encore problème, revois le cours Python. Prévois deux heures trente.

## Pourquoi un ORM ?

Une base de données relationnelle stocke des **tables** avec des lignes et des colonnes. Ton code Python, lui, manipule des **objets**. Un ORM fait le pont : une classe correspond à une table, une instance à une ligne, un attribut à une colonne.

| Monde SQL | Monde Python (SQLAlchemy) |
| --- | --- |
| Table `roadmaps` | Classe `Roadmap` |
| Ligne de la table | Instance de `Roadmap` |
| Colonne `titre` | Attribut `roadmap.titre` |
| Clé étrangère | Attribut de relation (`roadmap.etapes`) |
| `INSERT`, `UPDATE`, `DELETE` | `session.add()`, modification d'attribut, `session.delete()` |

L'ORM t'évite de construire des chaînes SQL à la main, ce qui supprime une grande famille de failles (l'injection SQL) et rend le code plus lisible. Il ne t'interdit pas pour autant d'écrire du SQL brut le jour où c'est nécessaire.

## Installation et moteur

Dans ton environnement virtuel :

```bash
pip install "sqlalchemy>=2.0" alembic
```

Pour développer, on utilise **SQLite** : un simple fichier, aucun serveur à installer. En production, tu passeras à PostgreSQL en changeant une seule ligne (voir plus bas). Crée `app/database.py` :

```python
from sqlalchemy import create_engine
from sqlalchemy.orm import DeclarativeBase, sessionmaker

DATABASE_URL = "sqlite:///./devroad.db"

engine = create_engine(
    DATABASE_URL,
    connect_args={"check_same_thread": False},  # nécessaire uniquement pour SQLite
)

SessionLocal = sessionmaker(bind=engine, autoflush=False, expire_on_commit=False)


class Base(DeclarativeBase):
    """Classe parente de tous les modèles."""
```

Trois objets à comprendre :

- le **moteur** (`engine`) gère la connexion à la base et un *pool* de connexions réutilisables ;
- la **session** (`Session`) est l'espace de travail : tu y ajoutes, modifies, supprimes des objets, puis tu valides le tout d'un coup avec `commit()` ;
- la **base déclarative** (`Base`) est la classe dont héritent tes modèles.

> **Astuce** : ne mets jamais l'URL de la base en dur dans un vrai projet. Lis-la depuis `pydantic-settings`, comme vu au chapitre précédent, avec une variable `DATABASE_URL`.

## Déclarer les modèles

Crée `app/models.py`. SQLAlchemy 2 utilise les annotations de type Python pour déduire les colonnes :

```python
from datetime import datetime

from sqlalchemy import ForeignKey, String, Text, func
from sqlalchemy.orm import Mapped, mapped_column, relationship

from app.database import Base


class Roadmap(Base):
    __tablename__ = "roadmaps"

    id: Mapped[int] = mapped_column(primary_key=True)
    titre: Mapped[str] = mapped_column(String(80), index=True)
    niveau: Mapped[str] = mapped_column(String(20), default="debutant")
    description: Mapped[str | None] = mapped_column(Text, default=None)
    cree_le: Mapped[datetime] = mapped_column(server_default=func.now())

    etapes: Mapped[list["Etape"]] = relationship(
        back_populates="roadmap",
        cascade="all, delete-orphan",
        order_by="Etape.position",
    )


class Etape(Base):
    __tablename__ = "etapes"

    id: Mapped[int] = mapped_column(primary_key=True)
    roadmap_id: Mapped[int] = mapped_column(ForeignKey("roadmaps.id", ondelete="CASCADE"))
    titre: Mapped[str] = mapped_column(String(120))
    position: Mapped[int] = mapped_column(default=0)

    roadmap: Mapped["Roadmap"] = relationship(back_populates="etapes")
```

Points clés :

- `Mapped[str]` signifie « colonne obligatoire » ; `Mapped[str | None]` signifie « colonne nullable ». Le type Python porte donc l'information sur la nullité ;
- `mapped_column()` précise ce que le type seul ne dit pas : clé primaire, longueur, index, valeur par défaut ;
- `server_default=func.now()` laisse la **base** remplir la date, alors que `default=` est évalué côté Python ;
- `relationship()` ne crée aucune colonne : il décrit comment naviguer d'un objet à l'autre. La vraie liaison est la clé étrangère `roadmap_id` ;
- `back_populates` relie les deux côtés de la relation, pour que `etape.roadmap` et `roadmap.etapes` restent synchronisés ;
- `cascade="all, delete-orphan"` supprime les étapes d'une roadmap quand on supprime la roadmap.

### Créer les tables

Pour un premier essai, `create_all` suffit. Dans `app/main.py` :

```python
from app import models  # noqa: F401  (importer les modèles pour qu'ils soient connus de Base)
from app.database import Base, engine

Base.metadata.create_all(bind=engine)
```

Cette approche ne sait que **créer** des tables absentes. Elle ne modifie jamais une table existante : on la remplacera par Alembic dans la dernière partie du chapitre.

:::quiz
Que signifie `description: Mapped[str | None]` dans un modèle SQLAlchemy 2 ?
- [ ] La colonne est une clé primaire
- [ ] La colonne est obligatoire et ne peut pas être vide
- [x] La colonne accepte la valeur NULL en base
- [ ] La colonne est calculée automatiquement
> Avec `Mapped`, l'annotation `| None` rend la colonne nullable. Sans elle, la colonne est déclarée `NOT NULL`.
:::

## Fournir une session avec `Depends`

Chaque requête HTTP doit avoir **sa propre session**, ouverte au début et fermée à la fin, même en cas d'erreur. C'est le rôle d'une dépendance FastAPI avec `yield`. Ajoute à `app/database.py` :

```python
from collections.abc import Iterator
from typing import Annotated

from fastapi import Depends
from sqlalchemy.orm import Session


def get_db() -> Iterator[Session]:
    db = SessionLocal()
    try:
        yield db
    finally:
        db.close()


DbSession = Annotated[Session, Depends(get_db)]
```

Le code avant `yield` s'exécute avant la route, celui du `finally` après la réponse. Grâce à l'alias `DbSession`, une route déclare simplement `db: DbSession` et reçoit une session prête à l'emploi. On verra les dépendances plus en détail au chapitre suivant.

## Écrire des requêtes avec `select()`

Avec SQLAlchemy 2, on construit une requête avec `select()` puis on l'exécute via la session :

```python
from sqlalchemy import select

# Toutes les roadmaps de niveau débutant, triées par titre
requete = select(Roadmap).where(Roadmap.niveau == "debutant").order_by(Roadmap.titre)
roadmaps = db.scalars(requete).all()

# Une seule roadmap par clé primaire (retourne None si absente)
roadmap = db.get(Roadmap, 1)

# Pagination
page = db.scalars(select(Roadmap).offset(20).limit(10)).all()

# Recherche partielle, insensible à la casse
trouvees = db.scalars(select(Roadmap).where(Roadmap.titre.ilike("%python%"))).all()
```

Quelques méthodes à distinguer :

- `db.scalars(requete).all()` retourne une liste d'objets ;
- `db.scalars(requete).first()` retourne le premier objet ou `None` ;
- `db.scalar(requete)` retourne directement une valeur ou un objet, utile pour un `count` ;
- `db.get(Modele, id)` est le raccourci pour une recherche par clé primaire.

Pour compter :

```python
from sqlalchemy import func, select

total = db.scalar(select(func.count()).select_from(Roadmap))
```

> **Erreur fréquente** : utiliser l'ancienne syntaxe `db.query(Roadmap).filter(...)`. Elle fonctionne encore mais appartient à SQLAlchemy 1.x. Dans un projet récent, écris `select()` : c'est ce que la documentation et la communauté utilisent désormais.

## Écrire dans la base : ajouter, modifier, supprimer

Les écritures passent aussi par la session. Rien n'est enregistré avant le `commit()` :

```python
# Ajouter
roadmap = Roadmap(titre="FastAPI", niveau="intermediaire")
db.add(roadmap)
db.commit()
db.refresh(roadmap)  # recharge l'objet : roadmap.id est maintenant renseigné

# Modifier : il suffit de changer l'attribut
roadmap.description = "Construire des API modernes"
db.commit()

# Supprimer
db.delete(roadmap)
db.commit()
```

Si une erreur survient entre deux `commit()`, tu peux annuler les changements en attente avec `db.rollback()`. La session regroupe plusieurs opérations en une **transaction** : soit tout est enregistré, soit rien.

## Brancher la base sur les routes

Reprenons le CRUD du chapitre 2, cette fois avec la base. Les schémas Pydantic gardent le même rôle, mais `RoadmapRead` reçoit `from_attributes=True` pour lire des objets SQLAlchemy :

```python
from typing import Annotated

from fastapi import APIRouter, HTTPException, Query, status
from sqlalchemy import select

from app.database import DbSession
from app.models import Roadmap
from app.schemas import RoadmapCreate, RoadmapRead, RoadmapUpdate

router = APIRouter(prefix="/roadmaps", tags=["roadmaps"])


def trouver_ou_404(db: DbSession, roadmap_id: int) -> Roadmap:
    roadmap = db.get(Roadmap, roadmap_id)
    if roadmap is None:
        raise HTTPException(status_code=404, detail="Roadmap introuvable")
    return roadmap


@router.get("", response_model=list[RoadmapRead])
def lister(
    db: DbSession,
    limite: Annotated[int, Query(ge=1, le=50)] = 10,
    decalage: Annotated[int, Query(ge=0)] = 0,
):
    requete = select(Roadmap).order_by(Roadmap.id).offset(decalage).limit(limite)
    return db.scalars(requete).all()


@router.get("/{roadmap_id}", response_model=RoadmapRead)
def lire(roadmap_id: int, db: DbSession):
    return trouver_ou_404(db, roadmap_id)


@router.post("", response_model=RoadmapRead, status_code=status.HTTP_201_CREATED)
def creer(donnees: RoadmapCreate, db: DbSession):
    roadmap = Roadmap(**donnees.model_dump())
    db.add(roadmap)
    db.commit()
    db.refresh(roadmap)
    return roadmap


@router.patch("/{roadmap_id}", response_model=RoadmapRead)
def modifier(roadmap_id: int, donnees: RoadmapUpdate, db: DbSession):
    roadmap = trouver_ou_404(db, roadmap_id)
    for champ, valeur in donnees.model_dump(exclude_unset=True).items():
        setattr(roadmap, champ, valeur)
    db.commit()
    db.refresh(roadmap)
    return roadmap


@router.delete("/{roadmap_id}", status_code=status.HTTP_204_NO_CONTENT)
def supprimer(roadmap_id: int, db: DbSession):
    db.delete(trouver_ou_404(db, roadmap_id))
    db.commit()
```

Remarque : on ne retourne jamais un modèle SQLAlchemy directement vers l'extérieur sans `response_model`. Le schéma Pydantic décide exactement quels champs sortent.

## Les relations en pratique

Avec la relation `etapes`, ajouter une étape à une roadmap devient très naturel :

```python
roadmap = db.get(Roadmap, 1)
roadmap.etapes.append(Etape(titre="Installer l'environnement", position=1))
db.commit()
```

Pour exposer les étapes dans la réponse, déclare-les dans le schéma de lecture :

```python
class EtapeRead(BaseModel):
    model_config = ConfigDict(from_attributes=True)
    id: int
    titre: str
    position: int


class RoadmapDetail(RoadmapRead):
    etapes: list[EtapeRead] = []
```

### Le piège du N+1

Par défaut, SQLAlchemy charge une relation **à la demande** : quand tu lis `roadmap.etapes`, il lance une requête. Si tu parcours 50 roadmaps et que tu lis leurs étapes, tu déclenches 1 requête pour la liste puis 50 pour les étapes : c'est le problème **N+1**. La solution est de demander le chargement groupé :

```python
from sqlalchemy.orm import selectinload

requete = select(Roadmap).options(selectinload(Roadmap.etapes))
roadmaps = db.scalars(requete).all()  # 2 requêtes au total, quel que soit N
```

### Relation plusieurs-à-plusieurs

Une roadmap peut avoir plusieurs tags et un tag appartenir à plusieurs roadmaps. On passe par une **table d'association** :

```python
from sqlalchemy import Column, ForeignKey, Table

roadmap_tags = Table(
    "roadmap_tags",
    Base.metadata,
    Column("roadmap_id", ForeignKey("roadmaps.id", ondelete="CASCADE"), primary_key=True),
    Column("tag_id", ForeignKey("tags.id", ondelete="CASCADE"), primary_key=True),
)


class Tag(Base):
    __tablename__ = "tags"

    id: Mapped[int] = mapped_column(primary_key=True)
    nom: Mapped[str] = mapped_column(String(30), unique=True)
    roadmaps: Mapped[list["Roadmap"]] = relationship(
        secondary=roadmap_tags, back_populates="tags"
    )
```

Côté `Roadmap`, on ajoute `tags: Mapped[list["Tag"]] = relationship(secondary=roadmap_tags, back_populates="roadmaps")`. Ensuite, `roadmap.tags.append(tag)` suffit pour créer la ligne d'association.

:::quiz
Tu affiches 50 roadmaps avec leurs étapes et tu constates 51 requêtes SQL. Quelle est la bonne correction ?
- [ ] Augmenter la limite de connexions du pool
- [ ] Passer de SQLite à PostgreSQL
- [x] Charger la relation avec `selectinload(Roadmap.etapes)`
- [ ] Appeler `db.commit()` après chaque lecture
> C'est le problème N+1. Le chargement groupé (`selectinload`) récupère toutes les étapes en une seule requête supplémentaire.
:::

## Passer à PostgreSQL

SQLAlchemy abstrait le moteur de base de données. Pour PostgreSQL, installe le pilote puis change l'URL :

```bash
pip install "psycopg[binary]"
```

```text
postgresql+psycopg://utilisateur:motdepasse@localhost:5432/devroad
```

Retire alors l'argument `connect_args`, spécifique à SQLite. Le reste du code ne change pas. C'est tout l'intérêt de l'ORM, même si certaines fonctionnalités avancées restent propres à un moteur.

## Migrations avec Alembic

`create_all` ne modifie pas les tables existantes. Dès que tu ajoutes une colonne à un modèle, il te faut une **migration** : un script versionné qui fait évoluer le schéma, en avant comme en arrière. Alembic est l'outil officiel de SQLAlchemy.

```bash
alembic init migrations
```

Dans `migrations/env.py`, importe `Base` et `models`, puis définis `target_metadata = Base.metadata`. Dans `alembic.ini`, renseigne `sqlalchemy.url`. Ensuite :

```bash
alembic revision --autogenerate -m "creation des tables"
alembic upgrade head
```

La première commande compare tes modèles à la base et génère un script dans `migrations/versions/`. La seconde l'applique. Relis **toujours** le script généré avant de l'appliquer : l'autogénération ne détecte pas tout (renommages de colonnes, par exemple). Pour revenir en arrière : `alembic downgrade -1`.

Une fois Alembic en place, supprime l'appel à `create_all` : le schéma est désormais géré uniquement par les migrations.

## Atelier guidé : persister les roadmaps DevRoad

Compte environ une heure trente.

1. Installe `sqlalchemy` et `alembic` dans ton environnement virtuel, puis crée `app/database.py` avec le moteur, `SessionLocal`, `Base` et la dépendance `get_db`.
2. Crée `app/models.py` avec les modèles `Roadmap` et `Etape` et leur relation un-à-plusieurs.
3. Ajoute `from_attributes=True` à tes schémas de lecture dans `app/schemas.py`.
4. Réécris les cinq routes du CRUD pour qu'elles utilisent `DbSession` au lieu de la liste Python.
5. Initialise Alembic, génère une première migration et applique-la. Vérifie que le fichier `devroad.db` apparaît.
6. Crée trois roadmaps via `/docs`, redémarre le serveur et vérifie qu'elles sont toujours là.
7. Ajoute une route `POST /roadmaps/{roadmap_id}/etapes` qui crée une étape, et un schéma `RoadmapDetail` qui renvoie les étapes.
8. Ajoute une colonne `publiee: Mapped[bool]` à `Roadmap`, génère une seconde migration et applique-la sans perdre les données.
9. Active temporairement `echo=True` dans `create_engine` pour observer le SQL généré, puis vérifie que le chargement des étapes ne produit pas de N+1.

Pour t'auto-évaluer : peux-tu expliquer à quoi sert la session, pourquoi on la ferme à la fin de la requête, et la différence entre `create_all` et une migration ?

## Erreurs fréquentes

- **Oublier `commit()`.** L'objet est ajouté en mémoire mais rien n'est écrit : la donnée disparaît à la fermeture de la session.
- **Partager une session entre requêtes.** Une session globale mène à des erreurs imprévisibles. Utilise `get_db` pour en avoir une par requête.
- **Retourner un modèle SQLAlchemy sans schéma de sortie.** Sans `response_model` ni `from_attributes`, la sérialisation échoue ou expose trop de champs.
- **Provoquer un N+1.** Parcourir une relation dans une boucle sans `selectinload`.
- **Modifier un modèle sans migration.** La base et le code divergent, et l'API plante avec « no such column ».
- **Mélanger ancienne et nouvelle syntaxe.** `db.query()` et `select()` dans le même projet rendent le code difficile à lire.
- **Confondre modèle SQLAlchemy et schéma Pydantic.** Le premier décrit la table, le second décrit ce qui entre et sort de l'API.

## Bonnes pratiques

- Garde séparés `models.py` (base de données) et `schemas.py` (API).
- Une session par requête, fermée dans un `finally`.
- Lis `DATABASE_URL` depuis la configuration, jamais en dur.
- Utilise Alembic dès le premier jour sur un projet sérieux.
- Relis chaque migration générée avant de l'appliquer.
- Ajoute un `index=True` sur les colonnes souvent filtrées et des contraintes `unique` là où la logique métier l'exige.
- Charge explicitement les relations dont tu as besoin avec `selectinload`.
- Place la logique de requête réutilisable dans des fonctions plutôt que de la dupliquer dans les routes.

## À retenir

- Un ORM relie classes et tables ; SQLAlchemy 2 utilise `Mapped` et `mapped_column` pour déclarer les colonnes.
- Le moteur gère la connexion, la session regroupe les opérations en transaction, `commit()` les valide.
- Une dépendance avec `yield` fournit une session par requête et la ferme proprement.
- Les requêtes s'écrivent avec `select()` et s'exécutent avec `db.scalars()` ou `db.get()`.
- `relationship` navigue entre objets ; `selectinload` évite le problème N+1.
- Les schémas Pydantic avec `from_attributes=True` convertissent les objets SQLAlchemy en réponses JSON.
- Alembic versionne l'évolution du schéma ; `create_all` ne sert qu'aux essais.
