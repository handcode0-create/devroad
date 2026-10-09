---
title: Formulaires, authentification et admin
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Une application qui se contente d'afficher des données reste une vitrine. Dès que des utilisateurs doivent saisir des informations, se connecter et gérer du contenu, trois outils de Django entrent en scène : les **formulaires**, le système d'**authentification** et l'**interface d'administration**. Ils font partie des « piles incluses » du framework et t'évitent des semaines de travail et de nombreuses failles de sécurité.

À la fin du chapitre, tu seras capable de :

- créer un formulaire avec `Form` et `ModelForm` et valider les données ;
- afficher un formulaire dans un template, avec la protection CSRF ;
- appliquer le motif **POST puis redirection** ;
- gérer l'inscription, la connexion et la déconnexion ;
- protéger des vues avec `login_required` ;
- lier des données à l'utilisateur connecté ;
- personnaliser l'admin Django pour gérer tes modèles.

Prérequis : les chapitres 1 à 4, avec les modèles `Roadmap`, `Etape` et `Tag` migrés. Prévois trois heures.

## Pourquoi un formulaire Django ?

Un formulaire HTML envoie des données au serveur. Le serveur doit ensuite les **valider** (sont-elles complètes, du bon type, dans les limites ?), les **nettoyer** et réafficher des erreurs claires si besoin. Écrire tout cela à la main est long et risqué. Django résume le travail en une classe qui décrit les champs, valide, convertit et produit le HTML.

Voici un formulaire de contact simple, dans `roadmaps/forms.py` :

```python
from django import forms


class ContactForm(forms.Form):
    nom = forms.CharField(max_length=80)
    email = forms.EmailField()
    message = forms.CharField(widget=forms.Textarea, min_length=10)
```

Et la vue qui le traite :

```python
from django.shortcuts import redirect, render

from .forms import ContactForm


def contact(request):
    if request.method == "POST":
        form = ContactForm(request.POST)
        if form.is_valid():
            donnees = form.cleaned_data
            # envoyer un e-mail, enregistrer, etc.
            return redirect("roadmaps:merci")
    else:
        form = ContactForm()
    return render(request, "roadmaps/contact.html", {"form": form})
```

Cette structure est universelle. Retiens-la :

1. une requête **GET** affiche un formulaire vide ;
2. une requête **POST** crée un formulaire rempli avec `request.POST` ;
3. `is_valid()` lance la validation et remplit `cleaned_data` ;
4. si tout va bien, on traite puis on **redirige** ; sinon, on réaffiche le formulaire avec ses erreurs.

La redirection après un POST réussi s'appelle le motif **PRG** (*Post / Redirect / Get*). Elle évite qu'un rafraîchissement de la page renvoie le formulaire une seconde fois.

> **Attention** : n'utilise jamais `request.POST["champ"]` directement pour enregistrer des données. Passe toujours par `form.cleaned_data`, qui contient des valeurs validées et converties.

## Afficher le formulaire et la protection CSRF

Dans le template, on affiche le formulaire et on ajoute obligatoirement un jeton CSRF :

```html
<form method="post">
  {% csrf_token %}
  {{ form.as_p }}
  <button type="submit">Envoyer</button>
</form>
```

Le jeton `csrf_token` protège contre les attaques **CSRF** (*Cross-Site Request Forgery*), où un site malveillant fait envoyer un formulaire à un utilisateur connecté, à son insu. Django rejette tout POST sans jeton valide avec une erreur 403. Ne désactive jamais cette protection pour « faire marcher » un formulaire.

`form.as_p` est pratique pour démarrer. Pour contrôler le rendu, tu peux afficher chaque champ séparément :

```html
{% for champ in form %}
  <div class="champ">
    {{ champ.label_tag }}
    {{ champ }}
    {% for erreur in champ.errors %}
      <p class="erreur">{{ erreur }}</p>
    {% endfor %}
  </div>
{% endfor %}
```

:::quiz
Pourquoi redirige-t-on après un POST réussi plutôt que d'afficher directement une page ?
- [ ] Pour accélérer le chargement de la page
- [ ] Parce que Django interdit de renvoyer du HTML après un POST
- [x] Pour éviter qu'un rafraîchissement renvoie le formulaire une seconde fois
- [ ] Pour contourner la protection CSRF
> Le motif Post/Redirect/Get remplace la requête POST par une requête GET. Si l'utilisateur recharge la page, rien n'est renvoyé en double.
:::

## ModelForm : un formulaire construit depuis un modèle

Quand un formulaire sert à créer ou modifier un objet, `ModelForm` évite de répéter les champs :

```python
from django import forms

from .models import Roadmap


class RoadmapForm(forms.ModelForm):
    class Meta:
        model = Roadmap
        fields = ["titre", "slug", "description", "niveau"]
        widgets = {
            "description": forms.Textarea(attrs={"rows": 4}),
        }
```

Django déduit les champs et les règles de validation à partir du modèle : `max_length`, `unique`, `choices`, etc. La méthode `save()` crée ou met à jour l'objet :

```python
from django.contrib.auth.decorators import login_required


@login_required
def creer_roadmap(request):
    form = RoadmapForm(request.POST or None)
    if form.is_valid():
        roadmap = form.save(commit=False)
        roadmap.auteur = request.user
        roadmap.save()
        return redirect("roadmaps:detail", slug=roadmap.slug)
    return render(request, "roadmaps/formulaire.html", {"form": form})
```

Trois points à noter :

- `request.POST or None` est un raccourci : en GET, le formulaire est vide et `is_valid()` renvoie `False` sans afficher d'erreurs ;
- `save(commit=False)` retourne l'objet **sans l'écrire en base**, ce qui permet de compléter un champ comme `auteur` avant `save()` ;
- on liste explicitement `fields`, **jamais** `"__all__"` : sinon un utilisateur malin pourrait envoyer un champ que tu ne voulais pas exposer.

Pour ajouter une règle métier, écris une méthode `clean_<champ>` :

```python
class RoadmapForm(forms.ModelForm):
    class Meta:
        model = Roadmap
        fields = ["titre", "slug", "description", "niveau"]

    def clean_titre(self):
        titre = self.cleaned_data["titre"].strip()
        if len(titre) < 5:
            raise forms.ValidationError("Le titre doit faire au moins 5 caractères.")
        return titre
```

Pour que le champ `auteur` existe, ajoute-le au modèle `Roadmap` avec `auteur = models.ForeignKey("auth.User", on_delete=models.CASCADE)` puis migre. Dans un vrai projet, on référence l'utilisateur avec `settings.AUTH_USER_MODEL`, un réglage que tu verras au chapitre final.

## L'authentification intégrée

Django embarque un système d'utilisateurs complet : modèle `User`, mots de passe hachés, sessions, permissions. Les mots de passe ne sont **jamais** stockés en clair : Django utilise un algorithme de hachage robuste avec sel.

Les vues de connexion et de déconnexion existent déjà. Il suffit de les brancher dans le `urls.py` du projet :

```python
from django.contrib import admin
from django.urls import include, path

urlpatterns = [
    path("admin/", admin.site.urls),
    path("comptes/", include("django.contrib.auth.urls")),
    path("comptes/", include("comptes.urls")),
    path("", include("roadmaps.urls")),
]
```

`django.contrib.auth.urls` fournit `login/`, `logout/`, `password_change/` et `password_reset/`. Il faut juste créer le template `registration/login.html` :

```html
{% extends "base.html" %}
{% block contenu %}
  <h1>Connexion</h1>
  <form method="post">
    {% csrf_token %}
    {{ form.as_p }}
    <button type="submit">Se connecter</button>
  </form>
{% endblock %}
```

Puis, dans `settings.py`, tu indiques où aller après la connexion et la déconnexion :

```python
LOGIN_URL = "login"
LOGIN_REDIRECT_URL = "roadmaps:liste"
LOGOUT_REDIRECT_URL = "roadmaps:liste"
```

Dans les templates, la variable `user` est disponible partout :

```html
{% if user.is_authenticated %}
  Bonjour {{ user.username }}
  <form method="post" action="{% url 'logout' %}">
    {% csrf_token %}
    <button type="submit">Se déconnecter</button>
  </form>
{% else %}
  <a href="{% url 'login' %}">Se connecter</a>
{% endif %}
```

Depuis Django 5, la déconnexion doit se faire par **POST** : c'est pourquoi elle passe par un petit formulaire et non par un simple lien.

### L'inscription

Django ne fournit pas de vue d'inscription toute prête, mais un formulaire `UserCreationForm` qui gère validation et hachage :

```python
from django.contrib.auth import login
from django.contrib.auth.forms import UserCreationForm
from django.shortcuts import redirect, render


def inscription(request):
    form = UserCreationForm(request.POST or None)
    if form.is_valid():
        user = form.save()
        login(request, user)
        return redirect("roadmaps:liste")
    return render(request, "registration/inscription.html", {"form": form})
```

L'appel à `login(request, user)` ouvre directement la session de l'utilisateur fraîchement créé.

## Protéger des vues et lier les données à l'utilisateur

Pour réserver une vue fonction aux utilisateurs connectés, on utilise le décorateur `login_required`. Pour une vue basée sur une classe, c'est un **mixin** :

```python
from django.contrib.auth.mixins import LoginRequiredMixin
from django.views.generic import CreateView


class RoadmapCreateView(LoginRequiredMixin, CreateView):
    model = Roadmap
    form_class = RoadmapForm
    template_name = "roadmaps/formulaire.html"

    def form_valid(self, form):
        form.instance.auteur = self.request.user
        return super().form_valid(form)
```

Un visiteur non connecté est redirigé vers `LOGIN_URL`, avec un paramètre `next` qui le ramène sur la page demandée après connexion.

Mais être connecté ne suffit pas : un utilisateur ne doit modifier que **ses propres** objets. Filtre donc toujours par propriétaire :

```python
@login_required
def modifier_roadmap(request, slug):
    roadmap = get_object_or_404(Roadmap, slug=slug, auteur=request.user)
    form = RoadmapForm(request.POST or None, instance=roadmap)
    if form.is_valid():
        form.save()
        return redirect("roadmaps:detail", slug=roadmap.slug)
    return render(request, "roadmaps/formulaire.html", {"form": form})
```

Si l'utilisateur essaie d'éditer la roadmap d'un autre, il obtient une 404. C'est la défense contre les failles dites **IDOR** (accès direct à un objet qui ne t'appartient pas), très courantes dans les applications mal conçues.

:::quiz
Un utilisateur connecté peut modifier n'importe quelle roadmap en changeant le slug dans l'URL. Quelle est la bonne correction ?
- [ ] Ajouter `login_required` sur la vue
- [ ] Cacher le lien de modification dans le template
- [x] Filtrer l'objet par propriétaire : `get_object_or_404(Roadmap, slug=slug, auteur=request.user)`
- [ ] Passer la méthode de la requête de GET à POST
> L'authentification ne suffit pas : il faut aussi vérifier l'autorisation côté serveur. Masquer un lien n'empêche personne de saisir l'URL à la main.
:::

## L'interface d'administration

L'admin est l'un des plus grands atouts de Django. Une fois les modèles enregistrés, tu obtiens un back-office complet : listes, recherche, filtres, formulaires de création et d'édition, gestion des utilisateurs.

Crée d'abord un super-utilisateur :

```bash
python manage.py createsuperuser
```

Puis enregistre tes modèles dans `roadmaps/admin.py` :

```python
from django.contrib import admin

from .models import Etape, Roadmap, Tag


class EtapeInline(admin.TabularInline):
    model = Etape
    extra = 1


@admin.register(Roadmap)
class RoadmapAdmin(admin.ModelAdmin):
    list_display = ("titre", "niveau", "publiee", "cree_le")
    list_filter = ("niveau", "publiee")
    search_fields = ("titre", "description")
    prepopulated_fields = {"slug": ("titre",)}
    inlines = [EtapeInline]
    actions = ["publier"]

    @admin.action(description="Publier les roadmaps sélectionnées")
    def publier(self, request, queryset):
        queryset.update(publiee=True)


admin.site.register(Tag)
```

Chaque option a un effet visible : `list_display` choisit les colonnes, `list_filter` ajoute une barre de filtres, `search_fields` active la recherche, `prepopulated_fields` remplit le slug en tapant le titre, `inlines` permet d'éditer les étapes directement dans la page de la roadmap, et `actions` ajoute une opération en masse.

Visite `http://127.0.0.1:8000/admin/`, connecte-toi et explore. Pour une application interne, un client ou un petit back-office, l'admin suffit souvent, ce qui te fait gagner un temps précieux.

> **Astuce** : l'admin est conçu pour les équipes internes, pas pour tes utilisateurs finaux. Ne le personnalise pas à l'infini : si une page devient sophistiquée, construis-la comme une vraie vue.

## Atelier guidé : comptes et contenus de DevRoad

Prévois deux heures.

1. Ajoute le champ `auteur` à `Roadmap` (clé étrangère vers `settings.AUTH_USER_MODEL`), migre en donnant une valeur par défaut pour les lignes existantes.
2. Crée un super-utilisateur et enregistre `Roadmap`, `Etape` et `Tag` dans l'admin avec `list_display`, `list_filter` et `search_fields`.
3. Ajoute l'inline `EtapeInline` et l'action « Publier ».
4. Branche `django.contrib.auth.urls`, crée `registration/login.html` et configure `LOGIN_REDIRECT_URL`.
5. Écris la vue `inscription` avec `UserCreationForm` et connecte l'utilisateur après la création.
6. Affiche dans `base.html` le nom de l'utilisateur et un bouton de déconnexion en POST.
7. Crée `RoadmapForm` (ModelForm) avec une règle `clean_titre`.
8. Écris la vue `creer_roadmap` protégée par `login_required`, qui renseigne `auteur` avec `commit=False`.
9. Écris `modifier_roadmap` en filtrant par auteur, puis teste avec un second compte qu'il obtient une 404.
10. Enlève volontairement `{% csrf_token %}` dans un formulaire, constate l'erreur 403, puis remets-le.

Auto-évaluation : explique à voix haute pourquoi `fields = "__all__"` est dangereux et ce que fait `commit=False`.

## Erreurs fréquentes

- **Oublier `{% csrf_token %}`.** Le POST est rejeté avec une erreur 403.
- **Ne pas rediriger après un POST valide.** Un rafraîchissement renvoie les données en double.
- **Utiliser `fields = "__all__"`.** Tu risques d'exposer des champs sensibles à la modification.
- **Se contenter de `login_required`.** Sans vérifier le propriétaire, n'importe quel utilisateur connecté accède aux données des autres.
- **Faire une déconnexion par lien GET.** Depuis Django 5, elle exige un POST.
- **Oublier de lancer `migrate` après avoir ajouté `auteur`.** La table ne contient pas la colonne.
- **Créer ses propres champs de mot de passe.** Utilise toujours `UserCreationForm` ou les vues d'authentification, qui hachent correctement.

## Bonnes pratiques

- Valide toujours côté serveur, même si le navigateur vérifie déjà les champs.
- Liste explicitement les champs des `ModelForm`.
- Applique PRG après chaque envoi réussi et affiche un message de confirmation avec le framework `messages`.
- Vérifie les droits dans la vue : l'authentification identifie, l'autorisation décide.
- Utilise l'admin comme outil interne et personnalise-le avec mesure.
- Garde les mots de passe, le `SECRET_KEY` et les identifiants hors du dépôt Git.

## À retenir

- Un **Form** décrit les champs, valide et nettoie ; `cleaned_data` contient les valeurs sûres.
- Le motif GET / POST / redirection structure toutes les vues de formulaire.
- `{% csrf_token %}` est obligatoire dans tout formulaire POST.
- `ModelForm` construit un formulaire depuis un modèle ; `commit=False` permet de compléter l'objet avant l'enregistrement.
- L'authentification intégrée fournit connexion, déconnexion, sessions et hachage des mots de passe.
- `login_required` identifie ; filtrer par propriétaire autorise.
- L'admin donne un back-office complet en quelques lignes grâce à `ModelAdmin`.
