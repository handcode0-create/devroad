---
title: API REST et sécurité
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Une application moderne ne se limite pas à des pages HTML : une application mobile, un front React ou un partenaire externe ont besoin de **consommer tes données** sous forme d'API. Django REST Framework (DRF) est l'extension de référence pour construire des API REST propres, documentées et sécurisées. Ce chapitre t'apprend à l'utiliser, puis à durcir ton application avec les réglages de sécurité de Django.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est une API REST, ses verbes HTTP et ses codes de statut ;
- installer et configurer Django REST Framework ;
- écrire des **serializers** pour convertir modèles et JSON ;
- construire des endpoints avec les `ViewSet` et les routeurs ;
- contrôler l'accès avec l'authentification et les **permissions** ;
- ajouter pagination, filtres et recherche ;
- appliquer les bases de la sécurité Django : `DEBUG`, `ALLOWED_HOSTS`, secrets, HTTPS, CORS.

Prérequis : les chapitres 1 à 5 (modèles, authentification) et des notions de JSON. Prévois trois heures.

## Qu'est-ce qu'une API REST ?

Une **API** (*Application Programming Interface*) est une porte d'entrée destinée aux programmes plutôt qu'aux humains. Au lieu de renvoyer du HTML, elle renvoie des **données** au format JSON. Une API REST s'organise autour de **ressources** (une roadmap, une étape) identifiées par des URLs, et manipulées par les verbes HTTP :

| Verbe | URL | Action | Statut en cas de succès |
| --- | --- | --- | --- |
| GET | `/api/roadmaps/` | Lister les roadmaps | 200 |
| POST | `/api/roadmaps/` | Créer une roadmap | 201 |
| GET | `/api/roadmaps/3/` | Lire la roadmap 3 | 200 |
| PUT / PATCH | `/api/roadmaps/3/` | Remplacer / modifier partiellement | 200 |
| DELETE | `/api/roadmaps/3/` | Supprimer | 204 |

Les **codes de statut** racontent ce qui s'est passé : `400` pour des données invalides, `401` pour un utilisateur non identifié, `403` pour un accès interdit, `404` pour une ressource introuvable. Une bonne API les utilise correctement, car les applications clientes s'appuient dessus.

Une réponse JSON ressemble à ceci :

```json
{
  "id": 3,
  "titre": "Apprendre Django",
  "slug": "apprendre-django",
  "niveau": "debutant",
  "etapes": [
    { "id": 1, "titre": "Installer Python", "ordre": 1 }
  ]
}
```

:::quiz
Quel code de statut une API doit-elle renvoyer après la création réussie d'une ressource avec POST ?
- [ ] 200
- [x] 201
- [ ] 204
- [ ] 302
> 201 signifie « Created ». 200 est utilisé pour une lecture ou une modification réussie, 204 pour une suppression sans contenu, et 302 est une redirection.
:::

## Installer Django REST Framework

```bash
pip install djangorestframework django-filter
```

Ajoute les applications dans `settings.py`, puis un bloc de configuration global :

```python
INSTALLED_APPS = [
    # ... apps Django ...
    "rest_framework",
    "django_filters",
    "roadmaps",
]

REST_FRAMEWORK = {
    "DEFAULT_AUTHENTICATION_CLASSES": [
        "rest_framework.authentication.SessionAuthentication",
        "rest_framework.authentication.TokenAuthentication",
    ],
    "DEFAULT_PERMISSION_CLASSES": [
        "rest_framework.permissions.IsAuthenticatedOrReadOnly",
    ],
    "DEFAULT_PAGINATION_CLASS": "rest_framework.pagination.PageNumberPagination",
    "PAGE_SIZE": 10,
    "DEFAULT_FILTER_BACKENDS": [
        "django_filters.rest_framework.DjangoFilterBackend",
        "rest_framework.filters.SearchFilter",
        "rest_framework.filters.OrderingFilter",
    ],
}
```

Pour que `TokenAuthentication` fonctionne, ajoute aussi `"rest_framework.authtoken"` dans `INSTALLED_APPS` et lance `python manage.py migrate`.

Un principe important dans cette configuration : par défaut, tout est **authentifié en écriture, lisible en lecture**. Il vaut mieux commencer strict et ouvrir au cas par cas, plutôt que l'inverse.

## Les serializers : traduire modèles et JSON

Un **serializer** convertit un objet Python en JSON (sérialisation) et valide les données JSON entrantes pour créer ou modifier un objet (désérialisation). Le `ModelSerializer` joue le rôle du `ModelForm` pour les API :

```python
from rest_framework import serializers

from .models import Etape, Roadmap


class EtapeSerializer(serializers.ModelSerializer):
    class Meta:
        model = Etape
        fields = ["id", "titre", "ordre"]


class RoadmapSerializer(serializers.ModelSerializer):
    etapes = EtapeSerializer(many=True, read_only=True)
    auteur = serializers.ReadOnlyField(source="auteur.username")
    nb_etapes = serializers.IntegerField(source="etapes.count", read_only=True)

    class Meta:
        model = Roadmap
        fields = [
            "id", "titre", "slug", "description", "niveau",
            "publiee", "auteur", "etapes", "nb_etapes",
        ]

    def validate_titre(self, valeur):
        if len(valeur.strip()) < 5:
            raise serializers.ValidationError("Titre trop court.")
        return valeur.strip()
```

Plusieurs idées à retenir :

- `read_only=True` empêche le client de modifier un champ, ce qui sert à protéger des données comme `auteur` ;
- un serializer peut en **imbriquer** un autre (`EtapeSerializer`) pour inclure des objets liés ;
- `validate_<champ>` fonctionne comme `clean_<champ>` dans les formulaires ;
- comme avec les formulaires, tu listes **explicitement** les champs exposés.

> **Attention** : ce que tu mets dans `fields` est ce que le monde entier peut lire. N'expose jamais un mot de passe, un jeton ou un e-mail privé par simple commodité.

## Les ViewSets et les routeurs

Un **ViewSet** regroupe en une classe toutes les actions d'une ressource (lister, créer, lire, modifier, supprimer). Le **routeur** génère ensuite les URLs :

```python
from rest_framework import viewsets, permissions

from .models import Roadmap
from .serializers import RoadmapSerializer


class RoadmapViewSet(viewsets.ModelViewSet):
    serializer_class = RoadmapSerializer
    filterset_fields = ["niveau", "publiee"]
    search_fields = ["titre", "description"]
    ordering_fields = ["cree_le", "titre"]

    def get_queryset(self):
        return (
            Roadmap.objects.filter(publiee=True)
            .select_related("auteur")
            .prefetch_related("etapes")
        )

    def perform_create(self, serializer):
        serializer.save(auteur=self.request.user)
```

Voici le routage dans `roadmaps/api_urls.py` :

```python
from rest_framework.routers import DefaultRouter

from .api import RoadmapViewSet

router = DefaultRouter()
router.register("roadmaps", RoadmapViewSet, basename="roadmap")

urlpatterns = router.urls
```

Puis dans le `urls.py` du projet :

```python
path("api/", include("roadmaps.api_urls")),
```

Avec ces quelques lignes, tu disposes de tous les endpoints du tableau précédent, d'une pagination (`?page=2`), de filtres (`?niveau=debutant`), de recherche (`?search=django`) et de tri (`?ordering=-cree_le`). Visite `http://127.0.0.1:8000/api/roadmaps/` : DRF fournit une interface navigable pour tester sans outil externe.

On remarque `perform_create` : c'est le point d'accroche idéal pour compléter un objet avec des données de la requête, ici l'utilisateur connecté. Le client **ne choisit pas** l'auteur.

Tu peux tester en ligne de commande :

```bash
curl http://127.0.0.1:8000/api/roadmaps/?niveau=debutant

curl -X POST http://127.0.0.1:8000/api/roadmaps/ \
  -H "Authorization: Token VOTRE_JETON" \
  -H "Content-Type: application/json" \
  -d '{"titre": "Apprendre DRF", "slug": "apprendre-drf"}'
```

:::quiz
Dans `RoadmapViewSet`, pourquoi utilise-t-on `perform_create` pour renseigner l'auteur plutôt que de laisser le client l'envoyer ?
- [ ] Parce que DRF ne sait pas enregistrer les clés étrangères
- [ ] Parce que le champ `auteur` n'existe pas dans le modèle
- [x] Pour que le client ne puisse pas se faire passer pour un autre utilisateur
- [ ] Pour accélérer la requête SQL
> Si le client choisissait l'auteur, il pourrait publier au nom d'un autre compte. La valeur doit venir de la session ou du jeton, c'est-à-dire de `request.user`.
:::

## Authentification et permissions

L'**authentification** répond à « qui es-tu ? », les **permissions** à « as-tu le droit ? ». DRF propose plusieurs mécanismes d'authentification : session (pratique si le front est servi par Django), jeton (simple pour des applications mobiles) et JWT (via une bibliothèque tierce).

Pour obtenir un jeton, ajoute une route de connexion fournie par DRF :

```python
from rest_framework.authtoken.views import obtain_auth_token

urlpatterns += [path("api/token/", obtain_auth_token)]
```

Le client envoie `username` et `password` en POST et reçoit un jeton à placer dans l'en-tête `Authorization: Token ...`.

Les permissions prêtes à l'emploi sont `AllowAny`, `IsAuthenticated`, `IsAuthenticatedOrReadOnly` et `IsAdminUser`. Pour une règle métier, tu écris la tienne : seul l'auteur peut modifier ou supprimer sa roadmap.

```python
from rest_framework import permissions


class EstAuteurOuLectureSeule(permissions.BasePermission):
    def has_object_permission(self, request, view, obj):
        if request.method in permissions.SAFE_METHODS:
            return True
        return obj.auteur == request.user
```

On l'associe au ViewSet :

```python
class RoadmapViewSet(viewsets.ModelViewSet):
    permission_classes = [
        permissions.IsAuthenticatedOrReadOnly,
        EstAuteurOuLectureSeule,
    ]
    # ... reste de la classe ...
```

`SAFE_METHODS` regroupe `GET`, `HEAD` et `OPTIONS`, qui ne modifient rien. Toute autre méthode exige d'être l'auteur. Remarque que c'est la même idée que « filtrer par propriétaire » du chapitre précédent, appliquée à l'API.

### Écrire un test d'API

Une API sans test casse en silence. DRF fournit `APIClient` :

```python
from django.contrib.auth.models import User
from rest_framework.test import APITestCase

from .models import Roadmap


class RoadmapApiTests(APITestCase):
    def setUp(self):
        self.alice = User.objects.create_user("alice", password="secret123")
        self.bob = User.objects.create_user("bob", password="secret123")
        self.roadmap = Roadmap.objects.create(
            titre="Roadmap d'Alice", slug="alice", publiee=True, auteur=self.alice
        )

    def test_lecture_anonyme(self):
        reponse = self.client.get("/api/roadmaps/")
        self.assertEqual(reponse.status_code, 200)

    def test_bob_ne_peut_pas_supprimer(self):
        self.client.force_authenticate(self.bob)
        reponse = self.client.delete(f"/api/roadmaps/{self.roadmap.pk}/")
        self.assertEqual(reponse.status_code, 403)
```

Ces tests se lancent avec `python manage.py test`. Écris-les pour tes règles de permission : ce sont elles qui protègent tes utilisateurs.

## Les bases de la sécurité Django

Django est sûr par défaut : l'ORM protège contre l'injection SQL, l'autoescape des templates contre le XSS, le jeton CSRF contre les requêtes forgées, et les mots de passe sont hachés. Mais la **configuration** reste de ta responsabilité. Voici les réglages critiques avant toute mise en ligne.

### Secrets et variables d'environnement

Ne laisse jamais `SECRET_KEY` dans le dépôt. Lis-la depuis l'environnement :

```python
import os

SECRET_KEY = os.environ["DJANGO_SECRET_KEY"]
DEBUG = os.environ.get("DJANGO_DEBUG", "0") == "1"
ALLOWED_HOSTS = os.environ.get("DJANGO_ALLOWED_HOSTS", "").split(",")
```

- `DEBUG = True` affiche des pages d'erreur détaillées avec ton code et tes réglages : **uniquement en développement** ;
- `ALLOWED_HOSTS` liste les noms de domaine autorisés ; en production, Django refuse les autres.

### HTTPS et cookies

```python
SECURE_SSL_REDIRECT = True
SESSION_COOKIE_SECURE = True
CSRF_COOKIE_SECURE = True
SECURE_HSTS_SECONDS = 31536000
SECURE_HSTS_INCLUDE_SUBDOMAINS = True
```

Ces options forcent HTTPS, protègent les cookies de session et indiquent aux navigateurs de n'utiliser que HTTPS pour ton domaine. Active-les uniquement en production, derrière un certificat valide.

### CORS : autoriser un front sur un autre domaine

Si ton front React tourne sur un autre domaine que l'API, le navigateur applique la politique **CORS**. Avec `django-cors-headers`, tu autorises uniquement les origines de confiance :

```python
CORS_ALLOWED_ORIGINS = ["https://app.exemple.ci"]
CSRF_TRUSTED_ORIGINS = ["https://app.exemple.ci"]
```

Évite `CORS_ALLOW_ALL_ORIGINS = True` en production : cela ouvre ton API à n'importe quel site.

### La commande de contrôle

Django sait auditer sa propre configuration :

```bash
python manage.py check --deploy
```

Elle liste les réglages manquants (HSTS, cookies sécurisés, `DEBUG` actif). Lance-la avant chaque déploiement et traite chaque avertissement.

:::quiz
Quelle est la bonne façon de gérer `SECRET_KEY` en production ?
- [ ] La commiter dans le dépôt pour que toute l'équipe l'ait
- [ ] La mettre dans un template pour la retrouver facilement
- [x] La lire depuis une variable d'environnement, hors du dépôt
- [ ] La remplacer par `DEBUG = False`
> La clé secrète signe les sessions et les jetons. Si elle fuite dans Git, un attaquant peut forger des sessions. Elle doit rester dans l'environnement du serveur.
:::

## Atelier guidé : l'API de DevRoad

Prévois deux heures.

1. Installe `djangorestframework` et `django-filter`, ajoute-les à `INSTALLED_APPS` et configure `REST_FRAMEWORK`.
2. Écris `EtapeSerializer` et `RoadmapSerializer` avec `etapes` imbriquées et `auteur` en lecture seule.
3. Crée `RoadmapViewSet` avec `get_queryset` optimisé (`select_related`, `prefetch_related`) et `perform_create`.
4. Branche le routeur sous `/api/` et parcours l'interface navigable.
5. Teste la pagination, le filtre `?niveau=debutant`, la recherche `?search=django` et le tri `?ordering=-cree_le`.
6. Active `rest_framework.authtoken`, migre, génère un jeton pour un utilisateur et crée une roadmap avec `curl`.
7. Écris la permission `EstAuteurOuLectureSeule` et applique-la au ViewSet.
8. Écris deux tests avec `APITestCase` : lecture anonyme autorisée, suppression par un autre utilisateur refusée (403).
9. Passe `SECRET_KEY`, `DEBUG` et `ALLOWED_HOSTS` en variables d'environnement.
10. Lance `python manage.py check --deploy` avec les réglages de production et note chaque avertissement à corriger.

Auto-évaluation : explique à voix haute la différence entre authentification et permission, et pourquoi `auteur` ne doit jamais venir du JSON envoyé par le client.

## Erreurs fréquentes

- **Renvoyer toujours 200.** Les clients ne peuvent plus distinguer succès et erreur ; utilise 201, 204, 400, 403, 404.
- **Exposer trop de champs.** Écris `fields` à la main au lieu d'une exposition globale.
- **Faire confiance au client.** L'auteur, le propriétaire ou le prix ne doivent pas venir du corps de la requête.
- **Oublier l'optimisation de requêtes.** Un serializer imbriqué sans `prefetch_related` produit un N+1 sur chaque page.
- **Garder `DEBUG = True` en ligne.** Les pages d'erreur dévoilent ton code et tes réglages.
- **Désactiver CSRF ou CORS pour que « ça marche ».** Corrige la cause (origines autorisées, jeton) au lieu de supprimer la protection.
- **Ne pas tester les permissions.** Une règle d'accès non testée finit toujours par casser.

## Bonnes pratiques

- Commence avec des permissions strictes et ouvre uniquement ce qui est nécessaire.
- Versionne ton API (`/api/v1/`) dès que des clients externes l'utilisent.
- Pagine toutes les listes ; ne renvoie jamais une table entière.
- Renvoie des messages d'erreur clairs et cohérents, sans détails internes.
- Garde les secrets dans l'environnement et exécute `check --deploy` en intégration continue.
- Journalise les erreurs en production plutôt que d'afficher les traces.

## À retenir

- Une API REST expose des **ressources** via des URLs, manipulées par des verbes HTTP et des codes de statut précis.
- DRF fournit **serializers**, **ViewSets** et **routeurs** pour construire une API en peu de code.
- Le serializer valide les entrées et choisit les champs exposés ; `read_only` protège les données sensibles.
- Authentification (qui es-tu ?) et permissions (as-tu le droit ?) sont deux étapes distinctes.
- Les permissions d'objet empêchent un utilisateur d'agir sur les données d'un autre.
- Sécurité de production : `DEBUG` désactivé, `ALLOWED_HOSTS`, secrets en environnement, HTTPS, cookies sécurisés, CORS restreint et `check --deploy`.
