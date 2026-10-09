---
title: Templates et vues
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Renvoyer du texte brut depuis une vue ne suffit pas pour un vrai site. Django sépare la **logique** (la vue en Python) de la **présentation** (le template en HTML). Ce chapitre t'apprend à construire des pages complètes, cohérentes et maintenables : héritage de templates, fichiers statiques, contexte, filtres, et vues basées sur des classes.

À la fin du chapitre, tu seras capable de :

- créer des templates et les afficher avec `render` ;
- transmettre des données à un template grâce au **contexte** ;
- utiliser les variables, les filtres et les balises (`if`, `for`, `url`) du langage de templates ;
- factoriser la mise en page avec `extends` et `block` ;
- servir du CSS, du JavaScript et des images grâce aux fichiers statiques ;
- écrire une vue fonction et son équivalent en vue basée sur une classe (`ListView`, `DetailView`) ;
- comprendre pourquoi Django échappe le HTML automatiquement.

Prérequis : le chapitre « Projets, apps et URLs ». Prévois deux heures et demie.

## Configurer les templates

Dans `settings.py`, la section `TEMPLATES` indique où Django cherche les fichiers HTML. Deux mécanismes se combinent :

```python
TEMPLATES = [
    {
        "BACKEND": "django.template.backends.django.DjangoTemplates",
        "DIRS": [BASE_DIR / "templates"],
        "APP_DIRS": True,
        "OPTIONS": {
            "context_processors": [
                "django.template.context_processors.debug",
                "django.template.context_processors.request",
                "django.contrib.auth.context_processors.auth",
                "django.contrib.messages.context_processors.messages",
            ],
        },
    },
]
```

- `DIRS` pointe vers un dossier `templates/` à la racine du projet : il accueille les gabarits communs (la mise en page générale).
- `APP_DIRS = True` fait chercher aussi dans `roadmaps/templates/` : chaque app range ses propres pages.

Par convention, on ajoute un sous-dossier portant le nom de l'app, pour éviter les collisions :

```text
roadmaps/
└── templates/
    └── roadmaps/
        ├── liste.html
        └── detail.html
templates/
└── base.html
```

On référencera alors `roadmaps/liste.html` dans le code. Sans ce sous-dossier, deux apps possédant chacune un `liste.html` se marcheraient dessus.

## Afficher un template avec render

La fonction `render` prend la requête, le chemin du template et un dictionnaire appelé **contexte**. Elle retourne la réponse HTTP prête à envoyer.

```python
from django.shortcuts import render

ROADMAPS = [
    {"id": 1, "titre": "Laravel de zéro", "niveau": "débutant", "terminee": True},
    {"id": 2, "titre": "React moderne", "niveau": "intermédiaire", "terminee": False},
    {"id": 3, "titre": "Django complet", "niveau": "débutant", "terminee": False},
]


def liste(request):
    contexte = {
        "titre_page": "Toutes les roadmaps",
        "roadmaps": ROADMAPS,
    }
    return render(request, "roadmaps/liste.html", contexte)
```

Les clés du dictionnaire deviennent des **variables** utilisables dans le template.

## Le langage de templates

Le langage de Django est volontairement limité : il sert à afficher, pas à programmer. Il repose sur trois éléments.

### Les variables

Les doubles accolades affichent une valeur. Le point sert à accéder à une clé de dictionnaire, un attribut d'objet ou un élément de liste.

```django
<h1>{{ titre_page }}</h1>
<p>Première roadmap : {{ roadmaps.0.titre }}</p>
```

### Les filtres

Un filtre transforme une valeur, avec le symbole `|` (tuyau) :

```django
<p>{{ roadmap.titre|upper }}</p>
<p>{{ roadmap.description|truncatewords:15 }}</p>
<p>{{ roadmap.created_at|date:"d/m/Y" }}</p>
<p>{{ roadmap.sous_titre|default:"Aucun sous-titre" }}</p>
<p>{{ roadmaps|length }} parcours disponibles</p>
```

Les filtres courants : `upper`, `lower`, `title`, `length`, `default`, `date`, `truncatewords`, `join`, `floatformat`, `pluralize`.

### Les balises

Les balises, entre accolades et pourcentages, apportent de la logique d'affichage :

```django
{% if roadmaps %}
  <ul>
    {% for r in roadmaps %}
      <li class="{% if r.terminee %}fini{% endif %}">
        {{ forloop.counter }}. {{ r.titre }} ({{ r.niveau }})
      </li>
    {% endfor %}
  </ul>
{% else %}
  <p>Aucune roadmap pour le moment.</p>
{% endif %}
```

La balise `for` accepte aussi `empty`, ce qui évite le `if` précédent :

```django
{% for r in roadmaps %}
  <li>{{ r.titre }}</li>
{% empty %}
  <li>Aucune roadmap pour le moment.</li>
{% endfor %}
```

Dans une boucle, la variable `forloop` donne `forloop.counter` (numéro à partir de 1), `forloop.first` et `forloop.last`.

> **Attention** : dans les templates, on n'écrit pas de parenthèses pour appeler une méthode. `roadmap.get_niveau_display` appelle bien la méthode. Et il n'est pas possible de lui passer des arguments : prépare les valeurs dans la vue.

:::quiz
Quelle écriture affiche correctement le titre d'une roadmap dans un template Django ?
- [ ] Le titre se place entre les symboles dollar et accolades
- [ ] Le titre se place entre deux crochets
- [x] Le nom de la variable se place entre doubles accolades, par exemple roadmap.titre
- [ ] On appelle print avec le titre dans une balise
> Les doubles accolades affichent la valeur d'une variable ; le point donne accès aux clés ou attributs. Les balises avec pourcentages servent à la logique (if, for).
:::

## L'héritage de templates

Tes pages partagent la même structure : en-tête, menu, pied de page. Au lieu de recopier ce code, tu crées un **gabarit de base** avec des zones modifiables appelées `block`.

Fichier `templates/base.html` :

```django
{% load static %}
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>{% block titre %}DevRoad{% endblock %}</title>
  <link rel="stylesheet" href="{% static 'css/style.css' %}">
</head>
<body>
  <header>
    <a href="{% url 'roadmaps:liste' %}">DevRoad</a>
    <nav>
      <a href="{% url 'roadmaps:liste' %}">Roadmaps</a>
    </nav>
  </header>

  <main>
    {% block contenu %}{% endblock %}
  </main>

  <footer>
    <p>HANCODE STUDIO</p>
  </footer>
</body>
</html>
```

Une page enfant déclare qu'elle étend ce gabarit et remplit les zones. Fichier `roadmaps/templates/roadmaps/liste.html` :

```django
{% extends "base.html" %}

{% block titre %}{{ titre_page }} - DevRoad{% endblock %}

{% block contenu %}
  <h1>{{ titre_page }}</h1>
  <ul>
    {% for r in roadmaps %}
      <li><a href="{% url 'roadmaps:detail' pk=r.id %}">{{ r.titre }}</a></li>
    {% empty %}
      <li>Aucune roadmap pour le moment.</li>
    {% endfor %}
  </ul>
{% endblock %}
```

Règles à respecter : `extends` doit être la **première balise** du fichier, et seul le contenu des `block` est affiché dans une page enfant. Pour inclure un petit morceau réutilisable, comme une carte, utilise `include` :

```django
{% include "roadmaps/_carte.html" with roadmap=r %}
```

## Les fichiers statiques

Les fichiers **statiques** sont ceux qui ne changent pas : CSS, JavaScript, images, polices. Configure-les dans `settings.py` :

```python
STATIC_URL = "static/"
STATICFILES_DIRS = [BASE_DIR / "static"]
STATIC_ROOT = BASE_DIR / "staticfiles"
```

- `STATIC_URL` est le préfixe public des fichiers.
- `STATICFILES_DIRS` liste les dossiers de développement (ici `static/` à la racine).
- `STATIC_ROOT` est le dossier où la commande `collectstatic` rassemble tout pour la production.

Place ton CSS dans `static/css/style.css`. Dans un template, charge la bibliothèque avec la balise `load` suivie de `static` (en haut du fichier) puis référence-le avec la balise `static`, comme dans `base.html` plus haut. En développement, `runserver` sert ces fichiers tout seul. En production, on lance `python manage.py collectstatic` et le serveur web les distribue.

> **Astuce** : si tu utilises Tailwind CSS comme sur d'autres projets de ton portfolio, tu peux simplement charger le fichier CSS compilé via la balise `static`. Aucun changement de principe.

## Une page de détail avec la sécurité intégrée

Voici la vue de détail et son template :

```python
from django.http import Http404
from django.shortcuts import render


def detail(request, pk):
    roadmap = next((r for r in ROADMAPS if r["id"] == pk), None)
    if roadmap is None:
        raise Http404("Roadmap introuvable")
    return render(request, "roadmaps/detail.html", {"roadmap": roadmap})
```

```django
{% extends "base.html" %}

{% block contenu %}
  <h1>{{ roadmap.titre }}</h1>
  <p>Niveau : {{ roadmap.niveau|capfirst }}</p>
  <p>{% if roadmap.terminee %}Terminée{% else %}En cours{% endif %}</p>
  <a href="{% url 'roadmaps:liste' %}">Retour à la liste</a>
{% endblock %}
```

Django applique l'**échappement automatique** : si un utilisateur saisit `<script>alert(1)</script>` dans un titre, le template l'affiche comme du texte inoffensif au lieu de l'exécuter. C'est ta protection de base contre les attaques XSS. Ne désactive cette protection (filtre `safe` ou balise `autoescape`) que pour du HTML que tu maîtrises à 100 %.

:::quiz
Que fait l'échappement automatique de Django dans les templates ?
- [ ] Il compresse le HTML pour accélérer la page
- [x] Il convertit les caractères spéciaux (comme les chevrons) en texte inoffensif pour éviter l'injection de code
- [ ] Il supprime tous les espaces inutiles
- [ ] Il traduit la page dans la langue du visiteur
> Les variables sont échappées par défaut : un script saisi par un utilisateur s'affiche comme du texte et ne s'exécute pas. C'est la défense de base contre le XSS.
:::

## Les vues basées sur des classes

Jusqu'ici, tes vues sont des **fonctions**. Django propose aussi des **vues basées sur des classes** (*class-based views*, ou CBV) qui encapsulent des comportements courants : lister des objets, afficher le détail d'un objet, traiter un formulaire.

Une vue de base se définit en héritant de `View` et en écrivant une méthode par verbe HTTP :

```python
from django.views import View
from django.shortcuts import render


class AccueilView(View):
    def get(self, request):
        return render(request, "accueil.html", {"message": "Bienvenue"})
```

Dans `urls.py`, on appelle `as_view()` pour la brancher :

```python
path("", AccueilView.as_view(), name="accueil"),
```

Les vues génériques vont plus loin : tu donnes le modèle et le template, elles font le reste. Au chapitre 4, ton modèle `Roadmap` existera en base ; voici déjà à quoi ressemblera le code :

```python
from django.views.generic import DetailView, ListView

from .models import Roadmap


class RoadmapListView(ListView):
    model = Roadmap
    template_name = "roadmaps/liste.html"
    context_object_name = "roadmaps"
    paginate_by = 10


class RoadmapDetailView(DetailView):
    model = Roadmap
    template_name = "roadmaps/detail.html"
    context_object_name = "roadmap"
```

Ces quelques lignes fournissent la requête, la pagination, la gestion du 404 et le contexte. Pour brancher `DetailView`, la route doit contenir `pk` ou `slug`.

### Fonction ou classe ?

Pas de dogme. Une vue fonction est plus lisible pour une logique spécifique ; une vue générique fait gagner du temps pour les cas standards (liste, détail, création). Tu peux mélanger les deux dans un même projet.

## Atelier guidé : les pages de DevRoad

Prévois une heure et quart. Tu reprends l'app `roadmaps` du chapitre précédent.

1. Dans `settings.py`, vérifie `DIRS` avec `BASE_DIR / "templates"` et ajoute `STATICFILES_DIRS`.
2. Crée `templates/base.html` avec les blocs `titre` et `contenu`, un en-tête, un menu et un pied de page.
3. Crée `static/css/style.css` avec quelques styles de base (police, marges, couleur de fond) et charge-le dans `base.html`.
4. Crée `roadmaps/templates/roadmaps/liste.html` qui étend `base.html` et affiche la liste avec `for ... empty`.
5. Modifie la vue `liste` pour utiliser `render` avec un contexte contenant `titre_page` et `roadmaps`.
6. Crée le template `detail.html` : titre, niveau avec le filtre `capfirst`, statut avec `if` et lien de retour via la balise `url`.
7. Crée un fragment `_carte.html` pour une roadmap et appelle-le avec `include` dans la liste.
8. Ajoute dans le contexte une roadmap dont le titre contient `<b>gras</b>` et constate que Django l'affiche comme du texte.
9. Ajoute la balise `if` pour appliquer une classe CSS `fini` aux roadmaps terminées.
10. Bonus : réécris la vue d'accueil en vue basée sur une classe avec `View`.

Auto-évaluation : explique à voix haute la différence entre `extends` et `include`, et dis pourquoi on range les templates dans un sous-dossier portant le nom de l'app.

## Erreurs fréquentes

- **`TemplateDoesNotExist`.** Le chemin est faux, l'app n'est pas dans `INSTALLED_APPS` ou le sous-dossier du nom de l'app manque.
- **Oublier la balise `load` avec `static`.** La balise `static` n'est alors pas reconnue et la page lève une erreur.
- **`extends` pas en première ligne.** Django exige qu'elle soit la première balise du fichier.
- **Variable mal orthographiée.** Django n'affiche rien, sans erreur : vérifie le nom dans le contexte.
- **Oublier de fermer `endif`, `endfor` ou `endblock`.** L'erreur indique la balise manquante.
- **Désactiver l'échappement par habitude.** Utiliser `safe` sur une donnée saisie par un utilisateur ouvre une faille XSS.
- **Mettre de la logique lourde dans le template.** Calcule dans la vue, affiche dans le template.

## Bonnes pratiques

- Un `base.html` unique, des blocs bien nommés (`titre`, `contenu`, `scripts`).
- Des fragments préfixés d'un souligné (`_carte.html`) pour les morceaux réutilisables.
- Garde les templates « bêtes » : aucune requête ni calcul complexe.
- Utilise toujours la balise `url` pour les liens, jamais d'URL écrite en dur.
- Choisis des noms de contexte explicites (`roadmaps`, `roadmap`), pas `data` ou `x`.
- Privilégie les vues génériques pour les cas simples, les fonctions pour les cas particuliers.
- Pense mobile d'abord : balise `viewport` et styles adaptables, car une grande partie de ton public navigue sur téléphone.

## À retenir

- Une vue prépare un **contexte** et le transmet à un template avec `render`.
- Le langage de templates comporte des **variables**, des **filtres** et des **balises** (`if`, `for`, `url`, `include`).
- `extends` et `block` permettent de factoriser la mise en page dans un `base.html`.
- Les fichiers statiques se déclarent dans `settings.py` et se chargent avec `load static` puis la balise `static`.
- L'échappement automatique protège contre le XSS ; n'utilise `safe` qu'avec un contenu de confiance.
- Les vues basées sur des classes (`View`, `ListView`, `DetailView`) évitent de réécrire le code répétitif des cas courants.
