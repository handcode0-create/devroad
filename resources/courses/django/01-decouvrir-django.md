---
title: Découvrir Django
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Django est un framework web écrit en Python. Il est utilisé par des sites de toute taille, des petits projets d'agence jusqu'à des plateformes qui reçoivent des millions de visites. Sa devise officielle : « le framework web pour les perfectionnistes avec des deadlines ». Dans ce chapitre, tu vas comprendre ce qu'il apporte, comment il organise une application, et tu vas lancer ton premier site en local.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est un framework web et ce que Django fournit « dans la boîte » ;
- décrire le modèle **MVT** (Modèle, Vue, Template) et le cycle d'une requête ;
- créer un environnement virtuel Python et installer Django 5.1 ;
- générer un projet, lancer le serveur de développement et lire la structure des fichiers ;
- écrire une première vue qui renvoie une réponse HTTP ;
- situer Django par rapport à Laravel, que tu connais peut-être déjà.

Prérequis : les bases de Python (variables, fonctions, listes, dictionnaires, classes simples) et savoir utiliser un terminal. Prévois deux heures. Il te faut Python 3.12 installé : vérifie avec `python --version`.

## Pourquoi un framework ?

Imagine que tu écrives un site avec Python seul. Il faudrait lire les requêtes HTTP, découper les URLs, parler à la base de données, construire du HTML, gérer les connexions des utilisateurs, protéger les formulaires contre les attaques... Chaque projet répéterait ces mêmes tâches. Un **framework** regroupe ces solutions éprouvées pour que tu te concentres sur ce qui rend ton application unique.

Django suit la philosophie « **batteries included** » : presque tout est livré avec lui.

| Besoin | Ce que Django fournit |
| --- | --- |
| Base de données | Un ORM (accès aux tables via des classes Python) et des migrations |
| Pages dynamiques | Un moteur de templates |
| Formulaires | Génération, validation et affichage des erreurs |
| Comptes utilisateurs | Inscription, connexion, mots de passe hachés, permissions |
| Administration | Une interface d'admin générée automatiquement |
| Sécurité | Protection CSRF, XSS, injection SQL, clickjacking |
| Internationalisation | Traduction et formats de dates par langue |

Pour un développeur qui travaille seul ou en petite équipe, c'est un atout considérable : tu n'as pas à choisir et assembler dix bibliothèques avant d'écrire ta première ligne utile.

> **À retenir** : Django te donne une structure et des outils cohérents. Tu écris la logique métier, il s'occupe de la plomberie.

:::quiz
Que signifie la philosophie « batteries included » de Django ?
- [ ] Django fonctionne uniquement sur des appareils sur batterie
- [x] Le framework fournit nativement la plupart des briques courantes : ORM, formulaires, authentification, admin
- [ ] Il faut installer une dizaine de bibliothèques avant de commencer
- [ ] Django ne gère que l'affichage des pages
> Django embarque ORM, templates, formulaires, authentification, administration et protections de sécurité, ce qui évite d'assembler soi-même ces briques.
:::

## Le modèle MVT

Beaucoup de frameworks utilisent le motif MVC (Modèle, Vue, Contrôleur). Django adopte une variante appelée **MVT** :

- **Modèle** (*Model*) : décrit les données et leur structure. C'est une classe Python qui correspond à une table.
- **Vue** (*View*) : contient la logique. Elle reçoit une requête, va chercher les données, et retourne une réponse. C'est l'équivalent d'un contrôleur ailleurs.
- **Template** : le fichier HTML qui met en forme les données. C'est ce que l'utilisateur voit.

Attention au vocabulaire : ce que d'autres frameworks appellent « vue » (le HTML) s'appelle ici **template**. Une « vue » Django est du code Python.

Et le « C » du MVC ? Il est assuré par le framework lui-même : c'est le **routeur d'URLs** qui décide quelle vue appeler.

### Le cycle d'une requête

Voici ce qui se passe quand quelqu'un ouvre `/roadmaps/` sur ton site :

```text
Navigateur  ->  URLconf (urls.py)  ->  Vue (views.py)  ->  Modèle (base de données)
                                          |
Navigateur  <-  Réponse HTTP  <-  Template (HTML rendu)
```

1. Le navigateur envoie la requête.
2. Django compare l'URL aux motifs déclarés dans les fichiers `urls.py`.
3. Il appelle la vue associée, en lui passant un objet `request`.
4. La vue interroge éventuellement les modèles, puis choisit un template.
5. Le template est rempli avec les données et devient du HTML.
6. Django renvoie ce HTML dans une réponse HTTP.

Garde ce schéma en tête : tous les chapitres suivants détaillent une de ces étapes.

:::quiz
Dans Django, quel élément reçoit la requête HTTP et décide quelle réponse renvoyer ?
- [ ] Le template
- [ ] Le modèle
- [x] La vue
- [ ] Le fichier settings.py
> La vue (une fonction ou une classe Python) reçoit l'objet request, s'appuie sur les modèles si besoin, et retourne la réponse. Le template ne fait que mettre en forme.
:::

## Préparer l'environnement

Chaque projet Python doit avoir son propre **environnement virtuel** : un dossier qui contient sa version de Python et ses bibliothèques, isolées du reste de ta machine. Cela évite les conflits entre projets.

```bash
# Crée un dossier de travail
mkdir devroad-django
cd devroad-django

# Crée l'environnement virtuel nommé .venv
python -m venv .venv

# Active-le (Linux et macOS)
source .venv/bin/activate

# Sur Windows (PowerShell)
# .venv\Scripts\Activate.ps1
```

Une fois activé, ton terminal affiche `(.venv)` au début de la ligne. Installe alors Django :

```bash
pip install "Django==5.1.*"
python -m django --version
```

La seconde commande doit afficher un numéro commençant par 5.1. Pour garder une trace des dépendances, génère un fichier de requirements :

```bash
pip freeze > requirements.txt
```

Quelqu'un qui récupère ton projet pourra alors faire `pip install -r requirements.txt` et obtenir exactement les mêmes versions.

> **Astuce** : ajoute `.venv/` à ton fichier `.gitignore`. On ne versionne jamais l'environnement virtuel, seulement `requirements.txt`.

## Créer le projet

La commande `django-admin` génère le squelette d'un projet. Le point final est important : il demande de créer les fichiers dans le dossier courant au lieu d'ajouter un sous-dossier superflu.

```bash
django-admin startproject config .
```

Ici, `config` est le nom du paquet de configuration. Beaucoup d'équipes préfèrent ce nom à celui du site, car il dit clairement ce qu'il contient. Tu obtiens :

```text
devroad-django/
├── manage.py
└── config/
    ├── __init__.py
    ├── settings.py
    ├── urls.py
    ├── asgi.py
    └── wsgi.py
```

Que contient chaque fichier ?

- **`manage.py`** : ton outil en ligne de commande pour ce projet (serveur, migrations, tests...).
- **`settings.py`** : toute la configuration (base de données, applications installées, langue, fichiers statiques).
- **`urls.py`** : la table d'aiguillage principale des URLs.
- **`wsgi.py` et `asgi.py`** : points d'entrée utilisés par les serveurs de production. Tu n'y touches presque jamais.

### Lancer le serveur de développement

```bash
python manage.py runserver
```

Ouvre `http://127.0.0.1:8000/` : tu vois une fusée et le message « The install worked successfully! ». Le serveur recharge automatiquement le code à chaque sauvegarde. Arrête-le avec Ctrl+C.

Dans le terminal, tu verras aussi un avertissement sur des « migrations non appliquées ». C'est normal : tu les appliqueras avec la commande suivante, qui crée les tables internes de Django (utilisateurs, sessions...).

```bash
python manage.py migrate
```

Par défaut, Django utilise **SQLite**, une base stockée dans un simple fichier `db.sqlite3`. Parfait pour apprendre et pour développer ; on passera à PostgreSQL en production.

## Régler la langue et le fuseau horaire

Ouvre `config/settings.py`. Quelques réglages valent la peine d'être adaptés dès le début, surtout pour un public francophone d'Abidjan :

```python
LANGUAGE_CODE = "fr-fr"
TIME_ZONE = "Africa/Abidjan"
USE_I18N = True
USE_TZ = True
```

Avec `fr-fr`, les messages d'erreur de formulaires et l'interface d'administration passent en français. `USE_TZ = True` indique à Django de stocker les dates en UTC dans la base et de les convertir à l'affichage : c'est le comportement recommandé.

Deux autres réglages à connaître dès maintenant :

```python
DEBUG = True
ALLOWED_HOSTS = []
```

`DEBUG = True` affiche de belles pages d'erreur détaillées, mais elles révéleraient des informations sensibles en production. Plus tard, tu mettras `DEBUG = False` et tu renseigneras `ALLOWED_HOSTS` avec ton nom de domaine.

> **Attention** : ne publie jamais la valeur de `SECRET_KEY` sur un dépôt public. Dans le projet final, tu la sortiras du code grâce à une variable d'environnement.

## Ta première vue

Une vue est une fonction Python qui prend un objet `HttpRequest` et retourne un objet `HttpResponse`. Pour rester simple, crée un fichier `config/views.py` :

```python
from django.http import HttpResponse


def accueil(request):
    return HttpResponse("Bienvenue sur DevRoad !")
```

Relie ensuite cette vue à une URL dans `config/urls.py` :

```python
from django.contrib import admin
from django.urls import path

from .views import accueil

urlpatterns = [
    path("", accueil, name="accueil"),
    path("admin/", admin.site.urls),
]
```

`path("", accueil, ...)` signifie : « quand l'URL est la racine du site, appelle la fonction `accueil` ». Recharge `http://127.0.0.1:8000/` : ton message s'affiche. Tu viens de parcourir tout le cycle requête, URL, vue, réponse.

Tu peux aussi renvoyer du HTML directement, ou lire des informations de la requête :

```python
def salutation(request):
    prenom = request.GET.get("prenom", "visiteur")
    return HttpResponse(f"<h1>Salut {prenom}</h1>")
```

En visitant `/salutation/?prenom=Awa` (après avoir ajouté la route correspondante), la page affiche « Salut Awa ». Remarque : écrire du HTML dans Python devient vite pénible. Les templates, vus au chapitre 3, résolvent ce problème.

:::quiz
Dans quel fichier déclare-t-on, au niveau du projet, le lien entre une URL et une vue ?
- [ ] settings.py
- [ ] manage.py
- [x] urls.py
- [ ] wsgi.py
> Le fichier urls.py contient la liste urlpatterns : chaque entrée associe un motif d'URL à une vue.
:::

## Django et Laravel : ce qui change et ce qui ne change pas

Si tu connais Laravel (le back-end de DevRoad), voici une correspondance utile pour ne pas partir de zéro :

| Laravel | Django |
| --- | --- |
| `routes/web.php` | `urls.py` |
| Contrôleur | Vue (fonction ou classe) |
| Blade | Templates Django |
| Eloquent | ORM Django |
| `php artisan` | `python manage.py` |
| Migrations | Migrations (générées automatiquement depuis les modèles) |
| Composer, `composer.json` | pip, `requirements.txt` |
| Panel d'admin (Filament, Nova) | Admin Django, inclus d'origine |

Les concepts sont les mêmes ; le vocabulaire et quelques réflexes changent. Un point fort de Django : l'admin et les migrations déduites des modèles te font gagner beaucoup de temps.

## Atelier guidé : le squelette de DevRoad en Django

Compte environ quarante-cinq minutes. Tu vas construire un mini-site de trois pages textuelles.

1. Crée un dossier `devroad-django`, entre dedans et crée un environnement virtuel `.venv`, puis active-le.
2. Installe Django 5.1 et génère `requirements.txt`.
3. Lance `django-admin startproject config .` et vérifie la structure obtenue avec `ls`.
4. Applique `python manage.py migrate`, puis démarre le serveur et ouvre la page d'accueil de Django.
5. Dans `settings.py`, passe `LANGUAGE_CODE` à `fr-fr` et `TIME_ZONE` à `Africa/Abidjan`.
6. Crée `config/views.py` avec trois vues : `accueil` (« Bienvenue sur DevRoad »), `roadmaps` (« Liste des roadmaps ») et `apropos` (« DevRoad aide les développeurs à progresser »).
7. Déclare trois chemins dans `urls.py` : la racine, `roadmaps/` et `a-propos/`, chacun avec un `name`.
8. Teste les trois URLs dans le navigateur. Puis ajoute une vue `bonjour` qui lit le paramètre `prenom` dans `request.GET`.
9. Provoque volontairement une erreur (par exemple une faute de frappe dans un nom de vue) et lis la page d'erreur de Django : repère le fichier et la ligne fautifs.
10. Crée un fichier `.gitignore` contenant `.venv/`, `db.sqlite3` et `__pycache__/`.

Auto-évaluation : sans regarder le cours, dessine le chemin d'une requête depuis le navigateur jusqu'à la réponse. Si tu peux citer le rôle de `urls.py`, d'une vue et d'un template, tu as acquis l'essentiel.

## Erreurs fréquentes

- **Oublier d'activer l'environnement virtuel.** Tu installes alors Django globalement, ou la commande `django-admin` est introuvable. Vérifie la présence de `(.venv)` dans ton terminal.
- **Oublier le point dans `startproject config .`.** Django crée un dossier supplémentaire imbriqué, source de confusion.
- **Oublier la virgule dans `urlpatterns`.** Une liste sans virgules entre les `path(...)` provoque une erreur de syntaxe.
- **Ne pas exécuter `migrate`.** Le serveur démarre mais l'admin et les sessions échouent.
- **Lancer `manage.py` depuis le mauvais dossier.** Il faut être dans le dossier qui contient `manage.py`.
- **Modifier `settings.py` sans redémarrer le serveur.** Le rechargement automatique gère le code, mais en cas de doute, relance.

## Bonnes pratiques

- Un environnement virtuel par projet, et un fichier `requirements.txt` à jour.
- Versionne ton code avec Git dès le premier jour, sans `.venv` ni la base locale.
- Donne un `name` à chaque route : tu pourras ensuite générer les liens sans écrire les URLs en dur.
- Lis les messages d'erreur de Django en entier : ils sont parmi les plus clairs de l'écosystème.
- Consulte la documentation officielle (docs.djangoproject.com), disponible en français pour une grande partie.
- Garde `DEBUG = True` uniquement en développement.

## À retenir

- Django est un framework Python « batteries included » : ORM, templates, formulaires, authentification, admin et sécurité sont fournis.
- Son architecture est **MVT** : Modèle pour les données, Vue pour la logique, Template pour l'affichage.
- Une requête passe par le routeur d'URLs, une vue, éventuellement les modèles, puis un template, et revient en réponse HTTP.
- On travaille dans un **environnement virtuel**, avec `pip` et `requirements.txt`.
- `startproject` crée `manage.py` et le paquet de configuration ; `runserver` lance le serveur de développement.
- Une vue est une fonction qui reçoit `request` et retourne une `HttpResponse`, branchée sur une URL via `urls.py`.
