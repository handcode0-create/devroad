---
title: Projet final Django
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as vu les briques de Django une par une. Il est temps de les assembler dans un vrai produit, de bout en bout, jusqu'à la mise en ligne. Dans ce projet guidé, tu construis **SuiviDev**, une application de **suivi d'apprentissage** : chaque utilisateur crée des objectifs, y rattache des ressources (cours, vidéos, livres), enregistre ses séances de travail et visualise sa progression. L'application expose aussi une API REST et se déploie en production.

À la fin du projet, tu seras capable de :

- concevoir un modèle de données cohérent à partir d'un besoin métier ;
- structurer un projet Django en plusieurs apps et en configurations par environnement ;
- implémenter authentification, formulaires, vues et templates sur des données **propres à chaque utilisateur** ;
- calculer des statistiques avec l'ORM (agrégations, annotations) ;
- exposer une API REST sécurisée avec Django REST Framework ;
- écrire des tests qui protègent les règles d'accès ;
- préparer et réaliser un déploiement (variables d'environnement, fichiers statiques, base PostgreSQL, serveur WSGI).

Prérequis : les six chapitres précédents. Prévois **six heures**, idéalement réparties sur deux ou trois sessions. Travaille dans un dépôt Git et fais un commit à la fin de chaque étape : tu garderas ainsi une trace claire de ta progression.

## Le cahier des charges

### Contexte et utilisateurs

Un apprenant autodidacte jongle entre plusieurs sources : vidéos, articles, livres, exercices. Il perd vite le fil de ce qu'il a fait, du temps passé et de ce qu'il lui reste à apprendre. SuiviDev lui donne un tableau de bord personnel, simple et motivant. Chaque utilisateur ne voit **que** ses propres données.

### Fonctionnalités attendues

1. **Comptes** : inscription, connexion, déconnexion, page de profil minimale.
2. **Objectifs** : créer, lister, modifier, archiver et supprimer des objectifs d'apprentissage (par exemple « Maîtriser Django »), avec une date cible facultative.
3. **Ressources** : rattacher à un objectif des ressources (titre, type, lien, statut « à faire », « en cours » ou « terminée »).
4. **Séances** : enregistrer une séance de travail (date, durée en minutes, note libre) liée à un objectif et, si besoin, à une ressource.
5. **Tableau de bord** : temps total étudié, temps des sept derniers jours, nombre de ressources terminées, et progression de chaque objectif en pourcentage.
6. **API REST** : lecture et écriture des objectifs, ressources et séances de l'utilisateur connecté, avec pagination et filtres.
7. **Administration** : back-office pour l'équipe, avec recherche et filtres.

### Contraintes techniques

- Python 3.12 ou plus, Django 5, Django REST Framework.
- SQLite en développement, PostgreSQL en production, choisi par variable d'environnement.
- Aucune donnée sensible dans le dépôt : tout secret vient de l'environnement.
- Au moins **dix tests automatisés**, dont des tests d'isolation entre utilisateurs.
- Aucune requête N+1 sur le tableau de bord et sur les listes.

### Modèle de données

| Modèle | Champs principaux | Relations |
| --- | --- | --- |
| `Objectif` | titre, description, date_cible, archive, cree_le | appartient à un utilisateur |
| `Ressource` | titre, type, url, statut | appartient à un objectif |
| `Seance` | date, minutes, note | appartient à un objectif, ressource facultative |

Règle de calcul de la progression d'un objectif : pourcentage de ressources au statut « terminée » parmi toutes ses ressources, ou 0 s'il n'en a aucune.

## Architecture du projet

Voici l'arborescence cible :

```text
suividev/
├── manage.py
├── requirements.txt
├── .env.example
├── .gitignore
├── config/
│   ├── settings/
│   │   ├── base.py
│   │   ├── dev.py
│   │   └── prod.py
│   ├── urls.py
│   └── wsgi.py
├── suivi/
│   ├── models.py
│   ├── forms.py
│   ├── views.py
│   ├── urls.py
│   ├── admin.py
│   ├── api.py
│   ├── serializers.py
│   ├── permissions.py
│   ├── tests/
│   └── templates/suivi/
├── templates/
│   ├── base.html
│   └── registration/
└── static/
```

Séparer les réglages en `base.py`, `dev.py` et `prod.py` permet d'activer `DEBUG` et SQLite chez toi, et un mode strict sur le serveur, sans modifier le code. Dans `dev.py`, tu importes tout le contenu de `base` avec une instruction d'import global, puis tu surcharges ce qui change. Dans `manage.py` et `wsgi.py`, la variable `DJANGO_SETTINGS_MODULE` pointe sur le bon fichier.

Le fichier `.env.example` documente les variables attendues, sans leurs vraies valeurs :

```text
DJANGO_SECRET_KEY=change-me
DJANGO_DEBUG=0
DJANGO_ALLOWED_HOSTS=suividev.exemple.ci
DATABASE_URL=postgres://user:motdepasse@hote:5432/suividev
```

## Les modèles

Le cœur du projet est un modèle de données propre. Voici une base à adapter dans `suivi/models.py` :

```python
from django.conf import settings
from django.db import models
from django.db.models import Count, Q


class Objectif(models.Model):
    proprietaire = models.ForeignKey(
        settings.AUTH_USER_MODEL, on_delete=models.CASCADE, related_name="objectifs"
    )
    titre = models.CharField(max_length=120)
    description = models.TextField(blank=True)
    date_cible = models.DateField(null=True, blank=True)
    archive = models.BooleanField(default=False)
    cree_le = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ["archive", "-cree_le"]

    def __str__(self):
        return self.titre

    def progression(self):
        total = self.ressources.count()
        if total == 0:
            return 0
        terminees = self.ressources.filter(statut=Ressource.Statut.TERMINEE).count()
        return round(100 * terminees / total)


class Ressource(models.Model):
    class Type(models.TextChoices):
        VIDEO = "video", "Vidéo"
        ARTICLE = "article", "Article"
        LIVRE = "livre", "Livre"
        EXERCICE = "exercice", "Exercice"

    class Statut(models.TextChoices):
        A_FAIRE = "a_faire", "À faire"
        EN_COURS = "en_cours", "En cours"
        TERMINEE = "terminee", "Terminée"

    objectif = models.ForeignKey(
        Objectif, on_delete=models.CASCADE, related_name="ressources"
    )
    titre = models.CharField(max_length=160)
    type = models.CharField(max_length=20, choices=Type.choices, default=Type.ARTICLE)
    url = models.URLField(blank=True)
    statut = models.CharField(
        max_length=20, choices=Statut.choices, default=Statut.A_FAIRE
    )

    def __str__(self):
        return self.titre


class Seance(models.Model):
    objectif = models.ForeignKey(
        Objectif, on_delete=models.CASCADE, related_name="seances"
    )
    ressource = models.ForeignKey(
        Ressource, on_delete=models.SET_NULL, null=True, blank=True
    )
    date = models.DateField()
    minutes = models.PositiveIntegerField()
    note = models.TextField(blank=True)

    class Meta:
        ordering = ["-date"]
```

La méthode `progression` fait deux requêtes par objectif : acceptable pour une page de détail, mais pas pour une liste. Pour le tableau de bord, tu calculeras ces valeurs avec `annotate` :

```python
objectifs = Objectif.objects.filter(proprietaire=request.user).annotate(
    nb_ressources=Count("ressources", distinct=True),
    nb_terminees=Count(
        "ressources", filter=Q(ressources__statut="terminee"), distinct=True
    ),
)
```

Un objet `Seance` doit aussi vérifier que sa `ressource`, si elle est renseignée, appartient bien au même objectif. C'est une règle métier à placer dans la méthode `clean` du modèle ou du formulaire.

:::quiz
Pourquoi chaque `Objectif` porte-t-il un champ `proprietaire` plutôt que de laisser les objectifs partagés entre tous ?
- [ ] Pour accélérer les requêtes SQL
- [ ] Parce que Django l'exige pour tout modèle
- [x] Pour pouvoir filtrer les données par utilisateur et garantir l'isolation entre comptes
- [ ] Pour afficher le nom de l'utilisateur dans l'admin
> Le propriétaire permet de n'exposer à chaque utilisateur que ses propres données. Chaque requête, vue et endpoint doit ensuite filtrer sur ce champ.
:::

## Les vues, formulaires et URLs

Chaque vue qui manipule des données doit être protégée **et** filtrée par propriétaire. Une astuce pour ne pas l'oublier : centralise le filtrage dans un mixin.

```python
from django.contrib.auth.mixins import LoginRequiredMixin
from django.urls import reverse_lazy
from django.views.generic import CreateView, DeleteView, ListView, UpdateView

from .forms import ObjectifForm
from .models import Objectif


class ObjectifsDeLUtilisateur(LoginRequiredMixin):
    def get_queryset(self):
        return Objectif.objects.filter(proprietaire=self.request.user)


class ObjectifListView(ObjectifsDeLUtilisateur, ListView):
    template_name = "suivi/objectif_liste.html"
    context_object_name = "objectifs"


class ObjectifCreateView(LoginRequiredMixin, CreateView):
    form_class = ObjectifForm
    template_name = "suivi/formulaire.html"
    success_url = reverse_lazy("suivi:objectifs")

    def form_valid(self, form):
        form.instance.proprietaire = self.request.user
        return super().form_valid(form)


class ObjectifUpdateView(ObjectifsDeLUtilisateur, UpdateView):
    form_class = ObjectifForm
    template_name = "suivi/formulaire.html"
    success_url = reverse_lazy("suivi:objectifs")


class ObjectifDeleteView(ObjectifsDeLUtilisateur, DeleteView):
    template_name = "suivi/confirmer_suppression.html"
    success_url = reverse_lazy("suivi:objectifs")
```

Comme `get_queryset` ne retourne que les objectifs de l'utilisateur, `UpdateView` et `DeleteView` renvoient une 404 pour l'objectif d'un autre. Le principe vaut pour les ressources et les séances : leur queryset est filtré sur `objectif__proprietaire=request.user`, et les formulaires limitent le champ `objectif` aux seuls objectifs de l'utilisateur, ce que tu fais dans le `__init__` du formulaire :

```python
class SeanceForm(forms.ModelForm):
    class Meta:
        model = Seance
        fields = ["objectif", "ressource", "date", "minutes", "note"]

    def __init__(self, *args, utilisateur, **kwargs):
        super().__init__(*args, **kwargs)
        self.fields["objectif"].queryset = Objectif.objects.filter(
            proprietaire=utilisateur
        )
        self.fields["ressource"].queryset = Ressource.objects.filter(
            objectif__proprietaire=utilisateur
        )
```

La vue passe l'utilisateur au formulaire via `get_form_kwargs`. Sans cela, un utilisateur pourrait forger une requête pointant vers l'objectif d'un autre.

> **Attention** : masquer un objet dans la liste ne suffit jamais. Les vues de détail, de modification et de suppression, ainsi que l'API, doivent chacune refuser l'accès aux données d'autrui.

## Le tableau de bord et les statistiques

Le tableau de bord affiche des chiffres calculés par la base. Voici la vue :

```python
from datetime import timedelta

from django.contrib.auth.decorators import login_required
from django.db.models import Count, Q, Sum
from django.shortcuts import render
from django.utils import timezone

from .models import Objectif, Ressource, Seance


@login_required
def tableau_de_bord(request):
    il_y_a_7_jours = timezone.localdate() - timedelta(days=7)
    seances = Seance.objects.filter(objectif__proprietaire=request.user)

    totaux = seances.aggregate(
        total=Sum("minutes"),
        semaine=Sum("minutes", filter=Q(date__gte=il_y_a_7_jours)),
    )
    objectifs = Objectif.objects.filter(
        proprietaire=request.user, archive=False
    ).annotate(
        nb_ressources=Count("ressources", distinct=True),
        nb_terminees=Count(
            "ressources", filter=Q(ressources__statut="terminee"), distinct=True
        ),
    )
    contexte = {
        "minutes_total": totaux["total"] or 0,
        "minutes_semaine": totaux["semaine"] or 0,
        "terminees": Ressource.objects.filter(
            objectif__proprietaire=request.user, statut="terminee"
        ).count(),
        "objectifs": objectifs,
    }
    return render(request, "suivi/tableau_de_bord.html", contexte)
```

Remarque le `or 0` : `Sum` renvoie `None` quand il n'y a aucune ligne. Dans le template, tu calcules le pourcentage avec un petit filtre ou une propriété. Une barre de progression en HTML suffit :

```html
{% for o in objectifs %}
  <article>
    <h3>{{ o.titre }}</h3>
    <progress max="{{ o.nb_ressources }}" value="{{ o.nb_terminees }}"></progress>
    <p>{{ o.nb_terminees }} / {{ o.nb_ressources }} ressources terminées</p>
  </article>
{% empty %}
  <p>Crée ton premier objectif pour commencer.</p>
{% endfor %}
```

:::quiz
Un utilisateur sans aucune séance ouvre le tableau de bord. Pourquoi écrit-on `totaux["total"] or 0` ?
- [ ] Parce que `Sum` renvoie une chaîne de caractères
- [x] Parce que `Sum` renvoie `None` quand il n'y a aucune ligne à additionner
- [ ] Parce que Django interdit les valeurs nulles dans un template
- [ ] Pour convertir les minutes en heures
> L'agrégation d'un ensemble vide donne `None`. Sans valeur de secours, le template afficherait « None » ou provoquerait une erreur dans un calcul.
:::

## L'API REST

L'API reprend les mêmes règles d'isolation. Les serializers listent explicitement leurs champs, le propriétaire est en lecture seule, et le ViewSet filtre par utilisateur :

```python
from rest_framework import permissions, serializers, viewsets

from .models import Objectif, Ressource, Seance


class ObjectifSerializer(serializers.ModelSerializer):
    progression = serializers.IntegerField(read_only=True)

    class Meta:
        model = Objectif
        fields = ["id", "titre", "description", "date_cible", "archive", "progression"]


class ObjectifViewSet(viewsets.ModelViewSet):
    serializer_class = ObjectifSerializer
    permission_classes = [permissions.IsAuthenticated]
    filterset_fields = ["archive"]
    search_fields = ["titre"]

    def get_queryset(self):
        return Objectif.objects.filter(proprietaire=self.request.user)

    def perform_create(self, serializer):
        serializer.save(proprietaire=self.request.user)
```

Pour les ressources et les séances, valide dans le serializer que l'objectif demandé appartient bien à l'utilisateur, par exemple dans `validate_objectif` :

```python
def validate_objectif(self, objectif):
    if objectif.proprietaire != self.context["request"].user:
        raise serializers.ValidationError("Objectif introuvable.")
    return objectif
```

Le message volontairement neutre évite de révéler l'existence d'un objet qui n'appartient pas à l'appelant.

## Atelier guidé : réalise le projet étape par étape

Chaque étape se termine par un commit Git. Garde l'ordre : il est conçu pour que l'application reste fonctionnelle à tout moment.

1. **Initialise** le dépôt, l'environnement virtuel, installe Django, DRF, `django-filter`, `whitenoise`, `gunicorn`, `dj-database-url` et `psycopg[binary]`, puis génère `requirements.txt`.
2. **Crée** le projet `config` et l'app `suivi`, découpe `settings` en `base`, `dev` et `prod`, et ajoute un `.gitignore` (venv, `db.sqlite3`, `.env`).
3. **Modélise** `Objectif`, `Ressource` et `Seance`, migre, et enregistre-les dans l'admin avec `list_display`, `list_filter`, `search_fields` et des inlines.
4. **Branche l'authentification** : vues de connexion et de déconnexion, inscription avec `UserCreationForm`, template `base.html` avec menu conditionnel.
5. **Implémente le CRUD des objectifs** avec le mixin de filtrage par propriétaire et un test manuel avec deux comptes.
6. **Ajoute les ressources** (création, changement de statut, suppression) accessibles depuis la page de détail d'un objectif.
7. **Ajoute les séances** avec `SeanceForm` limité aux objets de l'utilisateur et la règle de cohérence ressource/objectif.
8. **Construis le tableau de bord** avec `aggregate` et `annotate`, puis vérifie le nombre de requêtes (une dizaine au plus).
9. **Écris l'API** : serializers, ViewSets, routeur, pagination, filtres et jetons ; vérifie avec `curl`.
10. **Rédige les tests** : isolation des objectifs, refus 404/403 pour un autre utilisateur, calcul de la progression, total du tableau de bord, création par l'API.
11. **Durcis la configuration** : variables d'environnement, `prod.py` (HTTPS, cookies sécurisés, HSTS), puis `python manage.py check --deploy`.
12. **Prépare le déploiement** : WhiteNoise pour les fichiers statiques, `collectstatic`, `DATABASE_URL`, et un `Procfile` ou une commande de démarrage `gunicorn config.wsgi`.
13. **Déploie** sur l'hébergeur de ton choix, applique `migrate`, crée un super-utilisateur, puis vérifie en ligne l'inscription, une séance et le tableau de bord.
14. **Documente** : un `README.md` avec l'installation locale, les variables d'environnement, la commande de test et l'adresse de l'application.

Voici les réglages minimaux de `prod.py` pour l'étape 12 :

```python
import dj_database_url

from .base import *  # noqa: F401,F403

DEBUG = False
DATABASES = {"default": dj_database_url.config(conn_max_age=600)}

MIDDLEWARE.insert(1, "whitenoise.middleware.WhiteNoiseMiddleware")
STATIC_ROOT = BASE_DIR / "staticfiles"
STORAGES = {
    "default": {"BACKEND": "django.core.files.storage.FileSystemStorage"},
    "staticfiles": {
        "BACKEND": "whitenoise.storage.CompressedManifestStaticFilesStorage"
    },
}

SECURE_SSL_REDIRECT = True
SESSION_COOKIE_SECURE = True
CSRF_COOKIE_SECURE = True
SECURE_HSTS_SECONDS = 31536000
SECURE_PROXY_SSL_HEADER = ("HTTP_X_FORWARDED_PROTO", "https")
```

Et une série de commandes de déploiement typique :

```bash
pip install -r requirements.txt
python manage.py collectstatic --noinput
python manage.py migrate
gunicorn config.wsgi --bind 0.0.0.0:$PORT
```

### Checklist d'acceptation

Ton projet est terminé quand tu peux cocher chacun de ces points :

- Un visiteur anonyme est redirigé vers la connexion pour toute page privée.
- Deux comptes distincts ne voient jamais les données l'un de l'autre, dans les pages comme dans l'API.
- Tu peux créer, modifier, archiver et supprimer un objectif, puis lui ajouter des ressources et des séances.
- Le tableau de bord affiche le temps total, le temps de la semaine, les ressources terminées et une progression par objectif.
- Aucune page ne dépasse une dizaine de requêtes SQL, et il n'y a pas de N+1 dans les listes.
- L'API est paginée, filtrable, protégée par jeton, et renvoie des codes de statut corrects.
- La suite de tests passe avec au moins dix tests, dont l'isolation entre utilisateurs.
- `check --deploy` ne renvoie plus d'avertissement bloquant.
- Aucun secret n'apparaît dans l'historique Git ; `.env` est ignoré.
- L'application est accessible en ligne en HTTPS et le README permet à quelqu'un d'autre de l'installer.

### Auto-évaluation

Explique à voix haute, comme devant un recruteur : comment tu garantis qu'un utilisateur ne voit pas les données d'un autre, pourquoi tu as séparé les réglages par environnement, et comment tu as évité les requêtes N+1 sur le tableau de bord. Si une réponse reste floue, relis le chapitre correspondant.

## Erreurs fréquentes

- **Filtrer seulement les listes.** Les pages de détail, de modification, de suppression et l'API restent ouvertes si elles ne filtrent pas aussi par propriétaire.
- **Laisser le client choisir le propriétaire.** Renseigne-le toujours côté serveur à partir de `request.user`.
- **Oublier `distinct=True` dans plusieurs `Count`.** Deux jointures sur le même objectif multiplient les lignes et faussent les totaux.
- **Calculer la progression par boucle Python.** Utilise `annotate` pour laisser la base travailler.
- **Déployer avec `DEBUG = True` ou un `SECRET_KEY` du dépôt.** C'est la faille la plus répandue des petits projets.
- **Oublier `collectstatic`.** En production, la page apparaît sans style.
- **Commiter la base SQLite ou le fichier `.env`.** Vérifie `git status` avant chaque commit.
- **Ne tester que le cas heureux.** Les bugs de sécurité se trouvent dans les tests « un autre utilisateur essaie ».

## Bonnes pratiques

- Avance par petites étapes et fais un commit par fonctionnalité, avec un message clair.
- Garde la logique métier dans les modèles, les formulaires et les serializers, pas dans les templates.
- Écris les tests d'isolation dès la première fonctionnalité, pas à la fin.
- Mesure les requêtes d'une page avec Django Debug Toolbar pendant le développement.
- Sépare configuration et code : une seule base de code, plusieurs environnements.
- Documente tout ce qu'une autre personne devra faire pour lancer le projet.
- Soigne l'expérience : messages de confirmation, états vides parlants, dates lisibles en français.

## À retenir

- Un projet réel assemble modèles, vues, formulaires, API, tests et déploiement autour d'un besoin métier.
- L'isolation entre utilisateurs repose sur un champ propriétaire et un filtrage **systématique** dans chaque vue et chaque endpoint.
- Les statistiques se calculent dans la base avec `aggregate` et `annotate`, pas dans des boucles Python.
- Les réglages se découpent par environnement et les secrets restent dans les variables d'environnement.
- `check --deploy`, WhiteNoise, `collectstatic`, PostgreSQL et gunicorn forment le socle d'un déploiement Django.
- Des tests ciblés sur les règles d'accès te protègent mieux que n'importe quelle relecture.
