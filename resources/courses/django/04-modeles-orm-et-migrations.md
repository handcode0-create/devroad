---
title: Modèles, ORM et migrations
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Jusqu'ici, tes pages affichaient des données écrites en dur dans le code. Une vraie application stocke ses informations dans une base de données. Django propose pour cela un **ORM** (*Object-Relational Mapper*) : tu décris tes tables avec des classes Python, et Django écrit le SQL à ta place. Ce chapitre est l'un des plus importants du cours, car presque toute la valeur d'une application web vient de ses données.

À la fin du chapitre, tu seras capable de :

- définir un modèle avec ses champs et ses options ;
- créer et appliquer des **migrations** pour faire évoluer la base ;
- manipuler les données avec le shell Django : créer, lire, modifier, supprimer ;
- filtrer, trier et compter avec les **QuerySets** ;
- relier des modèles entre eux avec `ForeignKey` et `ManyToManyField` ;
- éviter le piège classique des requêtes N+1 avec `select_related` et `prefetch_related` ;
- brancher tes vues sur de vraies données.

Prérequis : les chapitres 1 à 3, une app `roadmaps` fonctionnelle et des notions de Python orienté objet (classes, attributs). Savoir ce qu'est une table SQL aide, mais n'est pas indispensable. Prévois deux heures et demie.

## Un modèle, c'est une table

Un **modèle** est une classe Python qui hérite de `models.Model`. Chaque attribut de classe représente une colonne de la table. Voici le modèle `Roadmap` de DevRoad, dans `roadmaps/models.py` :

```python
from django.db import models


class Roadmap(models.Model):
    class Niveau(models.TextChoices):
        DEBUTANT = "debutant", "Débutant"
        INTERMEDIAIRE = "intermediaire", "Intermédiaire"
        PRO = "pro", "Professionnel"

    titre = models.CharField(max_length=120)
    slug = models.SlugField(unique=True)
    description = models.TextField(blank=True)
    niveau = models.CharField(
        max_length=20, choices=Niveau.choices, default=Niveau.DEBUTANT
    )
    publiee = models.BooleanField(default=False)
    cree_le = models.DateTimeField(auto_now_add=True)

    class Meta:
        ordering = ["titre"]

    def __str__(self):
        return self.titre
```

Décortiquons les éléments importants :

- `CharField` stocke un texte court et exige `max_length` ; `TextField` stocke un texte long ;
- `SlugField` stocke un identifiant lisible pour les URLs (`apprendre-django`) ;
- `BooleanField`, `DateTimeField`, `IntegerField`, `DecimalField`, `EmailField`, `URLField` couvrent la plupart des besoins ;
- `choices` limite les valeurs possibles ; `TextChoices` rend cela lisible et évite les fautes de frappe ;
- `default` donne une valeur par défaut ; `blank=True` autorise un champ vide dans les formulaires ; `null=True` autorise la valeur `NULL` en base ;
- `auto_now_add=True` remplit la date une seule fois, à la création ;
- `class Meta` règle des options du modèle, comme l'ordre par défaut ;
- `__str__` définit comment l'objet s'affiche : dans l'admin, dans le shell, dans les listes.

Tu n'as pas déclaré de clé primaire : Django ajoute automatiquement un champ `id` entier qui s'incrémente.

> **Attention** : `blank` concerne la **validation** (formulaires), `null` concerne la **base de données**. Pour un champ texte, préfère `blank=True` seul : une chaîne vide est la façon normale de représenter l'absence de texte en Django.

:::quiz
Tu veux qu'un champ `description` puisse rester vide dans un formulaire, sans stocker de `NULL` en base. Que choisis-tu ?
- [ ] `null=True` seul
- [x] `blank=True` seul
- [ ] `null=True, blank=False`
- [ ] `default=None` uniquement
> `blank=True` autorise un formulaire à laisser le champ vide ; Django stockera alors une chaîne vide. `null=True` agit sur la base de données et n'est pas recommandé pour les champs texte.
:::

## Les migrations : versionner la base de données

Écrire la classe ne crée pas la table. Il faut deux commandes :

```bash
python manage.py makemigrations
python manage.py migrate
```

La première **compare tes modèles à l'état connu** et génère un fichier dans `roadmaps/migrations/`, par exemple `0001_initial.py`. Ce fichier décrit les changements en Python. La seconde **applique** ces fichiers à la base. Au premier lancement, `migrate` crée aussi les tables de Django lui-même (utilisateurs, sessions, admin).

Une migration est comme un commit Git pour ta base de données : chaque modification de schéma est un fichier daté, relisible et rejouable. C'est ce qui permet à toute ton équipe, et à ton serveur de production, d'obtenir la même structure.

Pour voir ce que Django va faire, deux commandes de diagnostic :

```bash
python manage.py showmigrations
python manage.py sqlmigrate roadmaps 0001
```

La seconde affiche le SQL exact généré. Par défaut, Django utilise SQLite, un simple fichier `db.sqlite3` parfait pour apprendre. En production, on passe souvent à PostgreSQL en modifiant `DATABASES` dans `settings.py`, sans toucher à tes modèles.

Chaque fois que tu modifies un modèle (nouveau champ, champ renommé), tu relances `makemigrations` puis `migrate`. Si tu ajoutes un champ obligatoire sur une table qui contient déjà des lignes, Django te demande une valeur par défaut : c'est normal, il doit remplir les lignes existantes.

> **Astuce** : commite toujours les fichiers de migration dans Git, mais jamais le fichier `db.sqlite3`. Ajoute-le à ton `.gitignore`.

## Explorer les données dans le shell

Django offre un terminal interactif connaissant tout ton projet :

```bash
python manage.py shell
```

Voici un tour complet du CRUD (créer, lire, modifier, supprimer) :

```python
from roadmaps.models import Roadmap

# Créer
r = Roadmap.objects.create(
    titre="Apprendre Django", slug="apprendre-django", publiee=True
)

# Autre façon : instancier puis sauvegarder
r2 = Roadmap(titre="Apprendre React", slug="apprendre-react")
r2.save()

# Lire
Roadmap.objects.all()
Roadmap.objects.get(slug="apprendre-django")

# Modifier
r.niveau = Roadmap.Niveau.INTERMEDIAIRE
r.save()

# Supprimer
r2.delete()
```

`Roadmap.objects` s'appelle le **manager** : c'est la porte d'entrée vers la table. Remarque que `get` renvoie **un seul** objet et lève une exception s'il n'en trouve aucun (`DoesNotExist`) ou plusieurs (`MultipleObjectsReturned`). Dans une vue, on le remplace par `get_object_or_404`, que tu connais déjà.

## Les QuerySets : interroger sans écrire de SQL

Un **QuerySet** représente une requête vers la base. Il est **paresseux** : rien n'est exécuté tant que tu n'as pas besoin des résultats (boucle, affichage, `list()`). Cela permet d'enchaîner des opérations :

```python
publiees = Roadmap.objects.filter(publiee=True)
recentes = publiees.order_by("-cree_le")[:5]
```

Ici, une seule requête SQL sera envoyée, avec `WHERE`, `ORDER BY` et `LIMIT`. Voici les méthodes les plus utiles :

| Méthode | Rôle |
| --- | --- |
| `filter(...)` | Garde les lignes qui correspondent |
| `exclude(...)` | Retire les lignes qui correspondent |
| `get(...)` | Renvoie exactement un objet |
| `order_by("champ")` | Trie ; le préfixe `-` inverse l'ordre |
| `count()` | Compte les lignes |
| `exists()` | Dit s'il y a au moins une ligne |
| `first()` / `last()` | Premier ou dernier objet, ou `None` |
| `values("champ")` | Renvoie des dictionnaires plutôt que des objets |

Les **lookups** se glissent dans les noms de champ avec un double souligné :

```python
Roadmap.objects.filter(titre__icontains="django")      # contient, sans casse
Roadmap.objects.filter(cree_le__year=2026)             # année précise
Roadmap.objects.filter(niveau__in=["debutant", "pro"]) # parmi une liste
Roadmap.objects.exclude(publiee=False)
```

Pour des conditions combinées avec « ou », on utilise les objets `Q` :

```python
from django.db.models import Q

Roadmap.objects.filter(Q(titre__icontains="web") | Q(description__icontains="web"))
```

Et pour calculer, on utilise l'**agrégation** :

```python
from django.db.models import Count

Roadmap.objects.filter(publiee=True).count()
Roadmap.objects.aggregate(total=Count("id"))
```

:::quiz
Que se passe-t-il quand tu écris `qs = Roadmap.objects.filter(publiee=True)` sans rien faire d'autre ?
- [ ] Django envoie la requête et stocke tous les résultats en mémoire
- [ ] Django lève une erreur car il manque `.all()`
- [x] Aucune requête n'est encore envoyée : le QuerySet est paresseux
- [ ] Django modifie la table pour marquer les lignes publiées
> Un QuerySet n'est évalué que lorsqu'on l'itère, l'affiche ou le convertit en liste. Cela permet de chaîner `filter`, `order_by` et d'autres opérations avant l'unique requête finale.
:::

## Relier les modèles entre eux

Une roadmap contient des étapes. C'est une relation **un-à-plusieurs** : une roadmap, plusieurs étapes. On la crée avec une `ForeignKey` placée sur le modèle « plusieurs » :

```python
class Etape(models.Model):
    roadmap = models.ForeignKey(
        Roadmap, on_delete=models.CASCADE, related_name="etapes"
    )
    titre = models.CharField(max_length=120)
    ordre = models.PositiveIntegerField(default=1)

    class Meta:
        ordering = ["ordre"]

    def __str__(self):
        return f"{self.roadmap.titre} - {self.titre}"
```

Deux paramètres sont essentiels :

- `on_delete` dit ce qui arrive quand la roadmap est supprimée. `CASCADE` supprime aussi ses étapes ; `PROTECT` interdit la suppression tant qu'il reste des étapes ; `SET_NULL` met le champ à `NULL` (il faut alors `null=True`) ;
- `related_name` donne un nom lisible au chemin inverse : `roadmap.etapes.all()` renvoie les étapes d'une roadmap.

Pour une relation **plusieurs-à-plusieurs** (une roadmap a plusieurs tags, un tag appartient à plusieurs roadmaps), on utilise `ManyToManyField` :

```python
class Tag(models.Model):
    nom = models.CharField(max_length=40, unique=True)

    def __str__(self):
        return self.nom


class Roadmap(models.Model):
    # ... autres champs ...
    tags = models.ManyToManyField(Tag, blank=True, related_name="roadmaps")
```

Django crée seul la table intermédiaire. Tu manipules la relation avec `add`, `remove` et `set` :

```python
python_tag = Tag.objects.create(nom="python")
roadmap.tags.add(python_tag)
roadmap.tags.all()
python_tag.roadmaps.filter(publiee=True)
```

Enfin, on traverse les relations dans les filtres avec le double souligné :

```python
Roadmap.objects.filter(etapes__titre__icontains="orm").distinct()
Etape.objects.filter(roadmap__niveau="debutant")
```

## Le piège des requêtes N+1

Voici un code qui paraît innocent :

```python
for etape in Etape.objects.all():
    print(etape.roadmap.titre)
```

Pour 100 étapes, Django envoie **101 requêtes** : une pour la liste, puis une par étape pour récupérer sa roadmap. C'est le fameux problème **N+1**, une cause majeure de lenteur. Deux outils le règlent :

```python
# Relation ForeignKey : une seule requête avec jointure
etapes = Etape.objects.select_related("roadmap")

# Relation inverse ou plusieurs-à-plusieurs : deux requêtes au total
roadmaps = Roadmap.objects.prefetch_related("etapes", "tags")
```

Règle simple : `select_related` pour suivre une clé étrangère « vers l'avant », `prefetch_related` pour les collections. Tu peux compter les requêtes avec `django.db.connection.queries` en mode `DEBUG`, ou avec l'outil Django Debug Toolbar.

> **Astuce** : annote plutôt que de boucler. `Roadmap.objects.annotate(nb_etapes=Count("etapes"))` ajoute un attribut `nb_etapes` calculé par la base, en une seule requête.

:::quiz
Tu affiches 50 étapes avec le titre de leur roadmap et tu constates 51 requêtes. Quelle correction applique-tu ?
- [ ] `Etape.objects.all()[:10]`
- [ ] `Etape.objects.prefetch_related("titre")`
- [x] `Etape.objects.select_related("roadmap")`
- [ ] Passer la base de SQLite à PostgreSQL
> Le problème N+1 vient d'un accès à une clé étrangère dans une boucle. `select_related("roadmap")` récupère les roadmaps avec une jointure dans la requête initiale.
:::

## Brancher les vues sur la base

Il reste à remplacer les données en dur de tes vues :

```python
from django.shortcuts import get_object_or_404, render

from .models import Roadmap


def liste(request):
    roadmaps = Roadmap.objects.filter(publiee=True).prefetch_related("tags")
    return render(request, "roadmaps/liste.html", {"roadmaps": roadmaps})


def detail(request, slug):
    roadmap = get_object_or_404(
        Roadmap.objects.prefetch_related("etapes"), slug=slug, publiee=True
    )
    return render(request, "roadmaps/detail.html", {"roadmap": roadmap})
```

Dans le template de détail, tu parcours la relation inverse comme une liste :

```html
<ol>
  {% for etape in roadmap.etapes.all %}
    <li>{{ etape.ordre }}. {{ etape.titre }}</li>
  {% empty %}
    <li>Aucune étape pour l'instant.</li>
  {% endfor %}
</ol>
```

Pour que `makemigrations` génère les nouvelles tables, n'oublie pas de relancer les deux commandes après avoir ajouté `Etape` et `Tag`.

## Atelier guidé : la base de DevRoad

Prévois une heure et demie.

1. Remplace ton modèle `Roadmap` par celui de ce chapitre (titre, slug, description, niveau, publiee, cree_le) et lance `makemigrations` puis `migrate`.
2. Affiche le SQL de la migration avec `sqlmigrate` et repère la création de la table.
3. Ouvre le shell et crée trois roadmaps, dont une non publiée.
4. Teste `filter`, `exclude`, `order_by("-cree_le")`, `count()` et `exists()` sur ces données.
5. Ajoute le modèle `Etape` avec sa `ForeignKey`, migre, puis crée quatre étapes pour une roadmap.
6. Ajoute le modèle `Tag` et le `ManyToManyField` sur `Roadmap`, migre, puis associe deux tags.
7. Modifie les vues `liste` et `detail` pour lire la base, avec `get_object_or_404`.
8. Dans le template de détail, affiche les étapes avec `roadmap.etapes.all`.
9. Provoque volontairement un N+1 dans le shell, observe `len(connection.queries)` en `DEBUG`, puis corrige-le avec `select_related`.
10. Ajoute `nb_etapes` via `annotate(Count("etapes"))` et affiche-le sur la liste.

Auto-évaluation : explique à voix haute la différence entre `makemigrations` et `migrate`, puis entre `select_related` et `prefetch_related`.

## Erreurs fréquentes

- **Oublier `migrate` après `makemigrations`.** Le fichier existe, mais la table non : tu obtiens `no such table`.
- **Modifier un fichier de migration déjà appliqué.** Crée plutôt une nouvelle migration.
- **Supprimer `db.sqlite3` pour « réparer ».** Tu perds tes données ; apprends plutôt à lire l'erreur de migration.
- **Utiliser `get` quand plusieurs résultats sont possibles.** Préfère `filter(...).first()` ou gère les exceptions.
- **Mettre `null=True` sur un `CharField`.** Tu obtiens deux manières de dire « vide » (chaîne vide et `NULL`).
- **Oublier `related_name` ou `on_delete`.** `on_delete` est obligatoire, et sans `related_name` le chemin inverse s'appelle `etape_set`, peu lisible.
- **Boucler sur des relations sans `select_related`.** Résultat : un N+1 silencieux.

## Bonnes pratiques

- Un modèle représente un concept métier clair, avec un `__str__` explicite.
- Utilise `TextChoices` plutôt que des chaînes libres pour les valeurs limitées.
- Versionne tes migrations et applique-les sur chaque environnement.
- Fais porter la logique de requête par le modèle ou son manager, pas par les templates.
- Mesure le nombre de requêtes d'une page avant de la déclarer terminée.
- Passe à PostgreSQL pour la production, tout en développant avec le même moteur si possible.

## À retenir

- Un **modèle** est une classe Python qui décrit une table ; chaque attribut est une colonne.
- `makemigrations` génère les fichiers de changement, `migrate` les applique à la base.
- L'ORM s'utilise via `Modèle.objects` : `create`, `get`, `filter`, `exclude`, `order_by`, `count`.
- Un **QuerySet** est paresseux et chaînable ; les lookups utilisent le double souligné.
- `ForeignKey` relie un-à-plusieurs, `ManyToManyField` relie plusieurs-à-plusieurs ; pense à `on_delete` et `related_name`.
- `select_related` et `prefetch_related` suppriment les requêtes N+1.
- Les vues lisent la base et passent les objets au template via le contexte.
