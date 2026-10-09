---
title: Projet final FastAPI
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as vu les routes, la validation, la base de données, la sécurité, les tests et le déploiement. Il est temps de tout assembler dans un vrai projet : l'**API des roadmaps de DevRoad**. Tu vas construire, tester et livrer un service complet, comme tu le ferais pour un client. C'est aussi une pièce de portfolio que tu peux montrer à un recruteur.

À la fin du projet, tu seras capable de :

- partir d'un cahier des charges et le traduire en modèle de données et en routes ;
- structurer une API en couches claires (routes, schémas, modèles, services) ;
- implémenter authentification, rôles et autorisation par ressource ;
- gérer pagination, filtres, recherche et statistiques avec SQLAlchemy ;
- écrire une suite de tests qui protège les règles métier ;
- conteneuriser et documenter l'API pour qu'un autre développeur puisse la lancer en cinq minutes.

Prérequis : les six chapitres précédents. Durée : environ six heures, à répartir sur plusieurs sessions. Travaille dans un dépôt Git et fais un commit à la fin de chaque étape.

## Le contexte

DevRoad est une application où des développeurs suivent des **roadmaps** : des parcours d'apprentissage composés d'étapes (par exemple « Python », « FastAPI », « Docker »). Un apprenant s'inscrit, consulte les roadmaps publiées, marque des étapes comme terminées et suit sa progression. Des administrateurs créent et publient les roadmaps.

Ton rôle : construire l'API qui alimentera l'interface web et, plus tard, une application mobile. Les consommateurs de l'API sont des développeurs front-end : la documentation `/docs` doit donc être claire, les erreurs prévisibles et les formats stables.

## Spécifications fonctionnelles

### Utilisateurs

- Un visiteur peut s'inscrire avec un e-mail et un mot de passe (8 caractères minimum).
- Un utilisateur inscrit peut se connecter et reçoit un jeton JWT valable 30 minutes.
- Il existe deux rôles : `apprenant` (par défaut) et `admin`.
- Un utilisateur peut consulter son profil sur `GET /auth/moi`.

### Roadmaps et étapes

- Une roadmap a un titre (3 à 80 caractères), un niveau (`debutant`, `intermediaire`, `professionnel`), une description facultative, un état de publication et des étapes ordonnées.
- Seuls les administrateurs peuvent créer, modifier, publier et supprimer une roadmap ou ses étapes.
- Les visiteurs et apprenants ne voient que les roadmaps **publiées** ; les administrateurs voient tout.
- Une roadmap peut porter plusieurs tags (relation plusieurs-à-plusieurs).

### Progression

- Un apprenant peut marquer une étape comme terminée, et annuler.
- Il consulte sa progression sur une roadmap : nombre d'étapes terminées, total, pourcentage.
- Un apprenant ne peut agir que sur **sa propre** progression.

### Consultation

- La liste des roadmaps est paginée (`limite`, `decalage`) et filtrable par niveau, par tag et par recherche textuelle sur le titre.
- Une route de statistiques réservée aux administrateurs retourne le nombre d'utilisateurs, de roadmaps publiées et d'étapes terminées.

## Contraintes techniques

- FastAPI, Pydantic v2, SQLAlchemy 2 (syntaxe `Mapped`), Alembic, pytest.
- SQLite pour les tests, PostgreSQL pour le déploiement (même code, URL différente).
- Aucun secret dans le dépôt ; configuration via `pydantic-settings`.
- Mots de passe hachés avec Argon2 ; JWT signés avec une clé lue dans l'environnement.
- Réponses toujours typées avec `response_model` ; jamais de champ sensible exposé.
- Chaque erreur métier renvoie un code HTTP adapté et un message en français.

## Modèle de données

Voici les tables à créer. Dessine-les sur papier avant de coder : un bon modèle évite des heures de refonte.

| Table | Colonnes principales | Remarques |
| --- | --- | --- |
| `utilisateurs` | id, email (unique), mot_de_passe_hache, role, actif | Index sur email |
| `roadmaps` | id, titre, niveau, description, publiee, cree_le | Index sur titre |
| `etapes` | id, roadmap_id, titre, position | Suppression en cascade |
| `tags` | id, nom (unique) | |
| `roadmap_tags` | roadmap_id, tag_id | Clé primaire composée |
| `progressions` | utilisateur_id, etape_id, terminee_le | Clé primaire composée |

La table `progressions` est une association avec une donnée en plus (la date de complétion). Une clé primaire composée sur `(utilisateur_id, etape_id)` empêche d'enregistrer deux fois la même étape pour un utilisateur, sans code supplémentaire.

```python
class Progression(Base):
    __tablename__ = "progressions"

    utilisateur_id: Mapped[int] = mapped_column(
        ForeignKey("utilisateurs.id", ondelete="CASCADE"), primary_key=True
    )
    etape_id: Mapped[int] = mapped_column(
        ForeignKey("etapes.id", ondelete="CASCADE"), primary_key=True
    )
    terminee_le: Mapped[datetime] = mapped_column(server_default=func.now())
```

## Routes attendues

| Méthode | Chemin | Accès | Rôle |
| --- | --- | --- | --- |
| POST | `/auth/inscription` | Public | Créer un compte |
| POST | `/auth/connexion` | Public | Obtenir un jeton |
| GET | `/auth/moi` | Connecté | Profil courant |
| GET | `/roadmaps` | Public | Liste paginée, filtrée |
| GET | `/roadmaps/{id}` | Public | Détail avec étapes et tags |
| POST | `/roadmaps` | Admin | Créer |
| PATCH | `/roadmaps/{id}` | Admin | Modifier, publier |
| DELETE | `/roadmaps/{id}` | Admin | Supprimer |
| POST | `/roadmaps/{id}/etapes` | Admin | Ajouter une étape |
| PUT | `/roadmaps/{id}/tags` | Admin | Remplacer les tags |
| PUT | `/etapes/{id}/progression` | Connecté | Marquer terminée |
| DELETE | `/etapes/{id}/progression` | Connecté | Annuler |
| GET | `/roadmaps/{id}/progression` | Connecté | Mon avancement |
| GET | `/admin/statistiques` | Admin | Chiffres globaux |
| GET | `/sante` | Public | Vérification technique |

## Architecture proposée

Sépare les responsabilités pour que chaque fichier reste court et testable :

```text
devroad-api/
├── alembic.ini
├── Dockerfile
├── docker-compose.yml
├── requirements.txt
├── .env.example
├── README.md
├── migrations/
├── app/
│   ├── main.py
│   ├── config.py
│   ├── database.py
│   ├── models.py
│   ├── schemas.py
│   ├── security.py
│   ├── dependances.py
│   ├── services/
│   │   ├── roadmaps.py
│   │   └── progression.py
│   └── routers/
│       ├── auth.py
│       ├── roadmaps.py
│       ├── progression.py
│       └── admin.py
└── tests/
    ├── conftest.py
    ├── test_auth.py
    ├── test_roadmaps.py
    └── test_progression.py
```

Les **routeurs** reçoivent les requêtes et renvoient des réponses. Les **services** contiennent la logique métier et les requêtes (« calculer la progression », « lister les roadmaps visibles »). Cette séparation permet de tester la logique sans passer par HTTP, et de garder des routes de quelques lignes.

## Étapes du projet

### Étape 1 : fondations (environ 40 minutes)

Crée le dépôt, l'environnement virtuel, `requirements.txt` et la structure ci-dessus. Écris `config.py`, `database.py` et `main.py` avec la route `/sante`. Ajoute `.env.example` (sans vraies valeurs) et `.gitignore`. Vérifie que `fastapi dev app/main.py` démarre et que `/docs` s'affiche.

### Étape 2 : modèles et migrations (environ 45 minutes)

Écris les six modèles. Initialise Alembic, branche `target_metadata`, génère la migration initiale, **relis-la**, puis applique-la. Contrôle avec un outil comme DB Browser for SQLite ou `psql` que les clés étrangères, index et contraintes d'unicité existent bien.

### Étape 3 : authentification et rôles (environ 50 minutes)

Réutilise `security.py` et les dépendances `UtilisateurConnecte` et `Admin`. Ajoute un moyen de créer le premier administrateur sans exposer de route publique : une commande de script (`python -m app.creer_admin`) ou une variable d'environnement `ADMIN_EMAIL` lue au démarrage. Réfléchis : pourquoi ne faut-il surtout pas permettre à `/auth/inscription` de choisir le rôle ?

### Étape 4 : roadmaps et étapes (environ 60 minutes)

Écris les schémas `RoadmapCreate`, `RoadmapUpdate`, `RoadmapRead` et `RoadmapDetail`. Place la requête de liste dans le service :

```python
from sqlalchemy import select
from sqlalchemy.orm import Session, selectinload

from app.models import Roadmap, Tag


def lister_roadmaps(
    db: Session,
    *,
    visibles_seulement: bool,
    niveau: str | None = None,
    tag: str | None = None,
    recherche: str | None = None,
    limite: int = 10,
    decalage: int = 0,
) -> list[Roadmap]:
    requete = select(Roadmap).options(selectinload(Roadmap.tags))
    if visibles_seulement:
        requete = requete.where(Roadmap.publiee.is_(True))
    if niveau:
        requete = requete.where(Roadmap.niveau == niveau)
    if tag:
        requete = requete.where(Roadmap.tags.any(Tag.nom == tag))
    if recherche:
        requete = requete.where(Roadmap.titre.ilike(f"%{recherche}%"))
    requete = requete.order_by(Roadmap.id).offset(decalage).limit(limite)
    return list(db.scalars(requete).all())
```

Le routeur détermine `visibles_seulement` selon le rôle de l'utilisateur éventuel. Pour une route publique qui change de comportement quand un jeton est présent, crée une dépendance `utilisateur_optionnel` basée sur `OAuth2PasswordBearer(tokenUrl=..., auto_error=False)`.

### Étape 5 : tags (environ 25 minutes)

Implémente `PUT /roadmaps/{id}/tags` : le corps contient une liste de noms ; les tags inexistants sont créés, les autres réutilisés, et la roadmap reçoit exactement cette liste. Normalise les noms (minuscules, espaces retirés) dans un validateur Pydantic.

### Étape 6 : progression (environ 55 minutes)

Rends l'opération **idempotente** : marquer deux fois la même étape ne doit ni échouer ni créer un doublon. Le calcul de l'avancement se fait en base, pas en Python :

```python
from sqlalchemy import func, select

from app.models import Etape, Progression


def calculer_avancement(db: Session, utilisateur_id: int, roadmap_id: int) -> dict:
    total = db.scalar(
        select(func.count()).select_from(Etape).where(Etape.roadmap_id == roadmap_id)
    )
    terminees = db.scalar(
        select(func.count())
        .select_from(Progression)
        .join(Etape, Etape.id == Progression.etape_id)
        .where(Progression.utilisateur_id == utilisateur_id, Etape.roadmap_id == roadmap_id)
    )
    pourcentage = round(100 * terminees / total) if total else 0
    return {"terminees": terminees, "total": total, "pourcentage": pourcentage}
```

Pense au cas limite : une roadmap sans étape ne doit pas provoquer de division par zéro. Vérifie aussi qu'on ne peut pas marquer l'étape d'une roadmap non publiée.

### Étape 7 : statistiques et documentation (environ 25 minutes)

Écris `GET /admin/statistiques` avec trois comptages. Complète la documentation : `summary` et `description` sur les routes, `responses={404: ...}` pour les erreurs documentées, exemples dans les schémas avec `examples`. Une API se juge aussi à la clarté de son `/docs`.

### Étape 8 : tests (environ 60 minutes)

Réutilise la fixture `client` avec base en mémoire et ajoute des fixtures `entetes_auth` et `entetes_admin`. Couvre au minimum :

- l'inscription (succès, e-mail en double, mot de passe trop court) ;
- la connexion (succès, mauvais mot de passe) ;
- l'accès refusé : 401 sans jeton, 403 pour un apprenant sur une route admin ;
- la visibilité : un apprenant ne voit pas une roadmap non publiée, un admin oui ;
- la pagination, le filtre par niveau et la recherche ;
- la progression : idempotence, calcul du pourcentage, interdiction d'agir sur la progression d'un autre ;
- le cas de la roadmap sans étape.

Vise au moins 85 % de couverture sur `app/services` et `app/routers`.

### Étape 9 : conteneurisation et livraison (environ 40 minutes)

Écris le `Dockerfile` et un `docker-compose.yml` avec deux services : l'API et PostgreSQL. Applique les migrations au démarrage. Rédige le `README.md` : présentation, prérequis, installation, variables d'environnement, lancement local, lancement des tests, déploiement. Un développeur extérieur doit pouvoir lancer le projet en suivant uniquement ce fichier.

```yaml
services:
  db:
    image: postgres:16
    environment:
      POSTGRES_DB: devroad
      POSTGRES_USER: devroad
      POSTGRES_PASSWORD: ${DB_PASSWORD}
    volumes:
      - donnees:/var/lib/postgresql/data

  api:
    build: .
    env_file: .env
    environment:
      DATABASE_URL: postgresql+psycopg://devroad:${DB_PASSWORD}@db:5432/devroad
    ports:
      - "8000:8000"
    depends_on:
      - db

volumes:
  donnees:
```

:::quiz
Pourquoi marquer une étape comme terminée doit-il être idempotent ?
- [ ] Pour que la base de données soit plus rapide
- [x] Pour qu'un double clic ou une requête rejouée ne crée ni erreur ni doublon
- [ ] Pour éviter d'utiliser une clé primaire
- [ ] Pour que le pourcentage dépasse 100
> Une opération idempotente produit le même résultat qu'on l'exécute une ou plusieurs fois. C'est essentiel avec des clients mobiles ou des réseaux instables qui rejouent les requêtes.
:::

:::quiz
Un apprenant appelle `DELETE /roadmaps/3` avec un jeton valide. Quelle réponse est correcte ?
- [ ] 401, car son jeton est invalide
- [x] 403, car il est identifié mais n'a pas le rôle administrateur
- [ ] 404, pour cacher l'existence de la route
- [ ] 204, la suppression est acceptée
> 401 concerne l'absence d'identité valide, 403 un manque de droits. Ici l'identité est connue, c'est le rôle qui manque.
:::

:::quiz
Où faut-il placer la requête qui calcule la progression d'un apprenant ?
- [ ] Dans le modèle Pydantic de réponse
- [ ] Dans le `Dockerfile`
- [x] Dans une fonction de service, appelée par le routeur
- [ ] Dans le fichier `.env`
> La logique métier et les requêtes vivent dans les services : elles restent testables sans HTTP et les routes restent courtes.
:::

## Atelier guidé : plan de travail sur six heures

Organise ton temps ainsi, et coche mentalement chaque étape avant de passer à la suivante.

1. Heure 1 : étapes 1 et 2. À la fin, l'application démarre et la base contient les six tables.
2. Heure 2 : étape 3. À la fin, tu peux t'inscrire, te connecter et appeler `/auth/moi` depuis `/docs`.
3. Heures 3 et 4 : étapes 4 et 5. À la fin, un admin crée et publie une roadmap avec ses étapes et ses tags, et un visiteur la consulte.
4. Heure 5 : étapes 6 et 7. À la fin, un apprenant suit sa progression et l'admin consulte les statistiques.
5. Heure 6 : étapes 8 et 9. À la fin, les tests passent, le conteneur démarre et le README est relu.

Pour t'auto-évaluer, demande à quelqu'un (ou à toi dans une semaine) de cloner le dépôt et de lancer l'API en suivant le README sans te poser de question. Chaque blocage rencontré indique une documentation à améliorer.

## Checklist de validation

Avant de considérer le projet terminé, vérifie chaque point. Il ne faut pas de réponse « à peu près » : chaque ligne doit être vérifiée.

Fonctionnel :

- Un visiteur peut s'inscrire, se connecter et consulter les roadmaps publiées.
- Un apprenant ne voit jamais une roadmap non publiée.
- Seul un admin peut créer, modifier, publier ou supprimer.
- La progression est idempotente et le pourcentage est exact, y compris pour une roadmap vide.
- La liste est paginée, filtrable par niveau et par tag, et recherchable par titre.

Sécurité :

- Aucun mot de passe en clair, aucun champ sensible dans les réponses.
- La clé secrète et les mots de passe de base sont hors du dépôt.
- Les routes protégées répondent 401 sans jeton et 403 sans le bon rôle.
- Un utilisateur ne peut pas modifier la progression d'un autre.
- CORS liste précisément les origines autorisées.

Qualité :

- Les tests passent avec une base en mémoire et couvrent les cas d'échec.
- La couverture atteint au moins 85 % sur les services et routeurs.
- Aucune requête N+1 sur la liste et le détail des roadmaps.
- Les migrations Alembic s'appliquent sur une base vide sans erreur.
- `/docs` est lisible : résumés, exemples, erreurs documentées.

Livraison :

- `docker compose up` démarre l'API et PostgreSQL, et `/sante` répond.
- Le README permet une installation en moins de dix minutes.
- Le dépôt Git a un historique clair, avec un commit par étape.

## Pour aller plus loin

Une fois le socle terminé, choisis une extension :

- un jeton de rafraîchissement et la déconnexion ;
- l'import d'une roadmap depuis un fichier JSON ;
- un cache simple pour la liste des roadmaps publiées ;
- une intégration continue GitHub Actions qui lance les tests à chaque `push` ;
- la limitation du débit sur `/auth/connexion` ;
- des journaux structurés au format JSON.

## Erreurs fréquentes

- **Coder avant de modéliser.** Un schéma de données bâclé se paie à chaque étape suivante.
- **Mettre la logique métier dans les routes.** Elles deviennent longues et difficiles à tester.
- **Oublier les cas limites** : roadmap vide, étape d'une autre roadmap, doublons de tags.
- **Laisser le client choisir son rôle** à l'inscription.
- **Tester uniquement le chemin heureux.** Les bugs se cachent dans les refus et les erreurs.
- **Générer une migration sans la relire**, puis découvrir une colonne supprimée par erreur.
- **Négliger le README.** Un projet que personne ne sait lancer a peu de valeur.
- **Faire un seul énorme commit** : impossible de revenir en arrière proprement.

## Bonnes pratiques

- Avance par petites étapes vérifiables, avec un commit à chaque étape.
- Garde les routeurs fins, les services riches et les schémas explicites.
- Écris les tests en même temps que les routes, pas à la fin.
- Nomme les erreurs métier clairement et renvoie des messages utiles au développeur front-end.
- Documente chaque variable d'environnement dans `.env.example`.
- Relis ton code comme si tu étais le recruteur : noms, structure, tests, README.
- Présente le projet dans ton portfolio avec une capture de `/docs` et le lien du dépôt.

## À retenir

- Un projet réussi commence par un cahier des charges, un modèle de données et une liste de routes.
- Une architecture en routeurs, services, modèles et schémas garde le code lisible et testable.
- Les rôles, l'autorisation par ressource et les messages d'erreur neutres protègent l'API.
- L'idempotence et les cas limites distinguent une API fiable d'un prototype.
- Les tests, les migrations et Docker rendent le projet reproductible par n'importe qui.
- Un bon README et une documentation `/docs` claire font partie du livrable.
