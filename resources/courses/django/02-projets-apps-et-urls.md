---
title: Projets, apps et URLs
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Tu sais lancer un projet Django et écrire une vue. Mais une vraie application ne tient pas dans un seul fichier. Django organise le code en **apps** : des modules autonomes, chacun responsable d'une partie du site. Ce chapitre t'apprend à découper proprement ton projet et à maîtriser le routage des URLs.

À la fin du chapitre, tu seras capable de :

- distinguer un **projet** d'une **app** et décider quand créer une nouvelle app ;
- créer une app, l'enregistrer dans `INSTALLED_APPS` et lire sa structure ;
- écrire des routes avec `path`, des paramètres typés et des routes nommées ;
- inclure les URLs d'une app avec `include` et les regrouper par espace de noms ;
- générer des liens et des redirections avec `reverse` et `redirect` ;
- renvoyer une erreur 404 proprement.

Prérequis : le chapitre « Découvrir Django » (environnement virtuel, projet, première vue). Durée estimée : deux heures.

## Projet et apps : qui fait quoi ?

Un **projet** Django est l'ensemble du site : sa configuration, ses URLs principales, sa base de données. Une **app** est une brique fonctionnelle réutilisable : un blog, un système de comptes, un catalogue de cours.

Pour DevRoad, on pourrait découper ainsi :

| App | Responsabilité |
| --- | --- |
| `roadmaps` | Les parcours d'apprentissage et leurs étapes |
| `courses` | Les cours et leurs chapitres |
| `progress` | La progression de chaque utilisateur |
| `accounts` | Inscription, connexion, profil |

Une bonne règle : une app doit pouvoir se décrire en **une phrase** sans utiliser le mot « et ». Si tu dis « elle gère les roadmaps et les paiements », il y a probablement deux apps.

> **À retenir** : le projet est le chef d'orchestre, les apps sont les musiciens. Une app peut même être copiée dans un autre projet.

## Créer une app

Depuis le dossier qui contient `manage.py` :

```bash
python manage.py startapp roadmaps
```

Django génère :

```text
roadmaps/
├── __init__.py
├── admin.py
├── apps.py
├── migrations/
│   └── __init__.py
├── models.py
├── tests.py
└── views.py
```

- **`models.py`** : les modèles de données (chapitre 4).
- **`views.py`** : les vues de l'app.
- **`admin.py`** : l'enregistrement dans l'administration (chapitre 5).
- **`migrations/`** : l'historique des changements de base de données.
- **`apps.py`** : la configuration de l'app.
- **`tests.py`** : les tests automatisés.

Créer le dossier ne suffit pas : il faut **déclarer l'app** dans `config/settings.py`, sinon Django l'ignore.

```python
INSTALLED_APPS = [
    "django.contrib.admin",
    "django.contrib.auth",
    "django.contrib.contenttypes",
    "django.contrib.sessions",
    "django.contrib.messages",
    "django.contrib.staticfiles",
    # Nos apps
    "roadmaps",
]
```

Tu peux aussi écrire `"roadmaps.apps.RoadmapsConfig"`, mais le nom court suffit.

:::quiz
Que se passe-t-il si tu crées une app avec startapp mais que tu oublies de l'ajouter à INSTALLED_APPS ?
- [ ] Rien : Django détecte automatiquement les dossiers
- [x] Django ignore l'app : ses modèles, templates d'app et migrations ne sont pas pris en compte
- [ ] Le serveur refuse de démarrer
- [ ] L'app est installée mais en lecture seule
> Seules les apps listées dans INSTALLED_APPS sont chargées. Sans cela, les modèles n'ont pas de migrations et les templates de l'app ne sont pas trouvés.
:::

## Les URLs : le routeur de Django

Le routeur lit la variable `urlpatterns` d'un fichier `urls.py`. Chaque élément est un appel à `path(route, vue, name=...)`.

```python
from django.urls import path
from . import views

urlpatterns = [
    path("", views.liste, name="liste"),
    path("<int:pk>/", views.detail, name="detail"),
    path("<slug:slug>/", views.par_slug, name="par_slug"),
]
```

### Les convertisseurs de chemin

Entre chevrons, tu captures une partie de l'URL et tu la transmets à la vue comme argument. Le convertisseur précise le type attendu :

| Convertisseur | Correspond à | Exemple |
| --- | --- | --- |
| `str` | Texte sans slash (défaut) | `laravel` |
| `int` | Entier positif | `42` |
| `slug` | Lettres, chiffres, tirets, soulignés | `apprendre-django` |
| `uuid` | Identifiant UUID | `075194d3-6885-417e-a8a8-6c931e272f00` |
| `path` | Texte avec slashs | `cours/django/intro` |

La vue reçoit la valeur sous le nom donné entre chevrons :

```python
from django.http import HttpResponse


def detail(request, pk):
    return HttpResponse(f"Roadmap numéro {pk}")
```

Avec `<int:pk>`, l'URL `/roadmaps/abc/` ne correspond plus à cette route : Django passera à la suivante ou renverra une 404. Le typage te protège des valeurs absurdes.

### Un point sur l'ordre

Django parcourt les routes **dans l'ordre** et s'arrête à la première qui correspond. Place donc les routes spécifiques avant les routes génériques. Dans l'exemple plus haut, `<int:pk>/` est testée avant `<slug:slug>/`, car un nombre comme `42` serait aussi un slug valide.

> **Erreur fréquente** : mettre une route très générale (par exemple `<str:nom>/`) en premier. Elle « avale » toutes les autres et tes pages semblent introuvables.

## Inclure les URLs d'une app

Mettre toutes les routes dans le fichier du projet devient vite illisible. Chaque app possède son propre `urls.py`, et le projet l'inclut avec `include`.

Fichier `roadmaps/urls.py` (à créer, Django ne le génère pas) :

```python
from django.urls import path
from . import views

app_name = "roadmaps"

urlpatterns = [
    path("", views.liste, name="liste"),
    path("<int:pk>/", views.detail, name="detail"),
]
```

Fichier `config/urls.py` :

```python
from django.contrib import admin
from django.urls import include, path

urlpatterns = [
    path("admin/", admin.site.urls),
    path("roadmaps/", include("roadmaps.urls")),
]
```

Le préfixe `roadmaps/` s'ajoute devant chaque route de l'app : `liste` répond à `/roadmaps/`, `detail` à `/roadmaps/3/`. Si demain tu veux renommer l'URL en `parcours/`, tu modifies **une seule ligne**.

### L'espace de noms avec app_name

La ligne `app_name = "roadmaps"` crée un **espace de noms**. Deux apps peuvent ainsi avoir chacune une route nommée `detail` sans collision : tu écriras `roadmaps:detail` ou `courses:detail`.

## Les vues de l'app

Voici des vues d'exemple dans `roadmaps/views.py`. Pour l'instant, les données sont une liste Python ; le chapitre 4 les remplacera par la base de données.

```python
from django.http import Http404, HttpResponse

ROADMAPS = [
    {"id": 1, "titre": "Laravel de zéro", "niveau": "débutant"},
    {"id": 2, "titre": "React moderne", "niveau": "intermédiaire"},
    {"id": 3, "titre": "Django complet", "niveau": "débutant"},
]


def liste(request):
    lignes = [f"{r['id']} - {r['titre']}" for r in ROADMAPS]
    return HttpResponse("<br>".join(lignes))


def detail(request, pk):
    for r in ROADMAPS:
        if r["id"] == pk:
            return HttpResponse(f"{r['titre']} ({r['niveau']})")
    raise Http404("Cette roadmap n'existe pas")
```

`raise Http404(...)` interrompt la vue et Django renvoie une vraie page 404 avec le bon code de statut. Avec `DEBUG = True`, tu vois ton message ; en production, c'est ta page `404.html` personnalisée qui s'affiche.

Il existe un raccourci pratique, `get_object_or_404`, que tu utiliseras dès que les données viendront de la base.

:::quiz
Quelle est l'URL complète servie par la route path("<int:pk>/", views.detail) incluse avec path("roadmaps/", include("roadmaps.urls")) pour pk égal à 3 ?
- [ ] /3/
- [ ] /roadmaps/pk/
- [x] /roadmaps/3/
- [ ] /roadmaps/detail/3/
> Le préfixe de include s'ajoute devant la route de l'app. Le convertisseur int capture 3 et le transmet à la vue sous le nom pk.
:::

## Ne jamais écrire les URLs en dur

Écrire `/roadmaps/3/` à la main dans un lien est fragile : si tu changes la structure, tous les liens cassent. Django sait **construire** l'URL à partir du nom de la route.

### Dans le code Python

```python
from django.shortcuts import redirect
from django.urls import reverse


def ancienne_page(request):
    # reverse() renvoie la chaîne de l'URL
    url = reverse("roadmaps:detail", kwargs={"pk": 3})   # "/roadmaps/3/"
    return redirect(url)


def vers_liste(request):
    # redirect() accepte directement un nom de route
    return redirect("roadmaps:liste")
```

`redirect` renvoie une réponse HTTP 302 qui invite le navigateur à aller ailleurs. On l'utilise beaucoup après avoir traité un formulaire.

### Dans les templates

Dans un template, la balise `url` fait le même travail. Elle sera détaillée au chapitre suivant :

```django
<a href="{% url 'roadmaps:detail' pk=roadmap.id %}">Voir la roadmap</a>
```

Quand tu changes `roadmaps/` en `parcours/` dans `config/urls.py`, tous les liens suivent sans modification.

## Plusieurs méthodes HTTP et paramètres de requête

L'objet `request` donne accès à beaucoup d'informations. Voici les plus utiles :

```python
def recherche(request):
    # URL : /roadmaps/recherche/?q=django&niveau=debutant
    terme = request.GET.get("q", "")
    niveau = request.GET.get("niveau")

    # Méthode HTTP utilisée
    if request.method == "POST":
        return HttpResponse("Données envoyées", status=201)

    return HttpResponse(f"Recherche de : {terme} (niveau : {niveau})")
```

- `request.GET` contient les paramètres après le `?` dans l'URL.
- `request.POST` contient les données d'un formulaire envoyé en POST.
- `request.method` vaut `"GET"`, `"POST"`, etc.
- `request.user` représente l'utilisateur connecté (chapitre 5).
- `request.path` est le chemin demandé.

Pour limiter une vue à certaines méthodes, utilise un décorateur :

```python
from django.views.decorators.http import require_http_methods


@require_http_methods(["GET", "POST"])
def formulaire(request):
    ...
```

Toute autre méthode recevra automatiquement une réponse 405 (méthode non autorisée).

## Les expressions régulières avec re_path

Dans de rares cas, les convertisseurs ne suffisent pas. Django propose `re_path` avec une expression régulière :

```python
from django.urls import re_path

urlpatterns = [
    re_path(r"^archives/(?P<annee>[0-9]{4})/$", views.archives, name="archives"),
]
```

Ici, seule une année de quatre chiffres est acceptée. Garde cette arme pour les cas particuliers : `path` couvre 95 % des besoins et se lit bien mieux.

:::quiz
Pourquoi vaut-il mieux utiliser reverse ou la balise url plutôt que d'écrire l'URL en dur ?
- [ ] Parce que c'est plus rapide à l'exécution
- [ ] Parce que les URLs en dur sont interdites par Django
- [x] Parce que si la structure des URLs change, les liens se mettent à jour automatiquement
- [ ] Parce que reverse chiffre l'URL
> Les routes nommées sont une seule source de vérité : modifier le motif dans urls.py met à jour tous les liens générés.
:::

## Atelier guidé : l'app roadmaps de DevRoad

Prévois une heure. Tu pars du projet du chapitre précédent.

1. Crée l'app avec `python manage.py startapp roadmaps` et ajoute `"roadmaps"` dans `INSTALLED_APPS`.
2. Crée `roadmaps/urls.py` avec `app_name = "roadmaps"` et deux routes : `liste` et `detail` avec `<int:pk>`.
3. Dans `config/urls.py`, supprime les anciennes vues de test et inclus `roadmaps.urls` sous le préfixe `roadmaps/`.
4. Dans `roadmaps/views.py`, copie la liste `ROADMAPS` et écris les vues `liste` et `detail` avec `Http404` si l'identifiant n'existe pas.
5. Teste `/roadmaps/`, `/roadmaps/2/` et `/roadmaps/99/` : la dernière doit afficher une 404.
6. Ajoute une route `recherche/` qui filtre la liste selon `request.GET["q"]` (insensible à la casse).
7. Ajoute une vue `racine` à `config/urls.py` qui redirige l'accueil `/` vers `roadmaps:liste` avec `redirect`.
8. Vérifie avec `reverse` dans le shell (`python manage.py shell`) que `reverse("roadmaps:detail", kwargs={"pk": 2})` donne `/roadmaps/2/`.
9. Change le préfixe `roadmaps/` en `parcours/` et constate que la redirection de l'étape 7 fonctionne toujours.

Auto-évaluation : peux-tu expliquer la différence entre `app_name` et le `name` d'une route ? Et pourquoi l'ordre des routes compte ?

## Erreurs fréquentes

- **Oublier `include` ou le fichier `roadmaps/urls.py`.** Django ne le crée pas : il faut le faire toi-même.
- **Oublier `app_name`.** Le nom `roadmaps:detail` provoque alors une erreur `NoReverseMatch` ou un avertissement d'espace de noms.
- **Mettre un slash initial dans une route.** On écrit `path("liste/", ...)` et non `path("/liste/", ...)`.
- **Oublier le slash final.** Avec la configuration par défaut, `/roadmaps` redirige vers `/roadmaps/`, mais mieux vaut rester cohérent.
- **Nom de paramètre incohérent.** Si la route déclare `<int:pk>`, la vue doit accepter un argument `pk`, pas `id`.
- **Route générale placée trop haut.** Elle capture des URLs destinées à d'autres routes.

## Bonnes pratiques

- Une app = une responsabilité claire, avec un nom au pluriel et en minuscules (`roadmaps`).
- Un `urls.py` par app, inclus dans le projet, avec `app_name`.
- Donne un `name` à toutes les routes et utilise `reverse`, `redirect` et la balise `url`.
- Utilise des URLs lisibles et stables, comme `/roadmaps/3/` ou `/roadmaps/laravel-de-zero/`.
- Préfère les convertisseurs typés (`int`, `slug`) pour valider l'entrée dès le routage.
- Lève `Http404` plutôt que de renvoyer un message d'erreur avec un code 200.

## À retenir

- Un **projet** contient la configuration ; une **app** est un module fonctionnel déclaré dans `INSTALLED_APPS`.
- `startapp` crée la structure : `models.py`, `views.py`, `admin.py`, `migrations/`.
- Les routes se déclarent avec `path`, des convertisseurs (`int`, `slug`, `str`, `uuid`, `path`) et un `name`.
- Chaque app a son `urls.py`, inclus avec `include`, et un `app_name` pour l'espace de noms.
- `reverse`, `redirect` et la balise `url` génèrent les liens à partir des noms de routes.
- `Http404` produit une vraie réponse 404 ; l'ordre des routes détermine laquelle est choisie.
