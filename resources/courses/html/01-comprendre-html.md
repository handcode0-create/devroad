---
title: Comprendre HTML
minutes: 90
level: beginner
---

## Ce que tu vas apprendre

Chaque page web que tu as déjà visitée, de la plus simple à la plus spectaculaire, repose sur un même fondement : un document **HTML**. Avant de parler de style, d'animations ou de frameworks, il faut comprendre ce que ce langage fait vraiment, comment le navigateur le lit, et comment écrire une page propre dès la première ligne.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est HTML et ce qu'il n'est pas (ce n'est pas un langage de programmation) ;
- décrire le trajet d'une page : du serveur jusqu'à l'écran ;
- lire et écrire des **éléments**, des **balises** et des **attributs** ;
- écrire le squelette minimal d'un document valide ;
- distinguer les éléments de type bloc et les éléments en ligne ;
- utiliser les outils de développement du navigateur pour inspecter une page.

Prérequis : aucun. Il te faut seulement un éditeur de texte (Visual Studio Code est un excellent choix) et un navigateur moderne. Prévois une heure et demie.

## HTML, c'est quoi ?

**HTML** signifie *HyperText Markup Language*, soit « langage de balisage d'hypertexte ». Décortiquons ces mots.

- **Hypertexte** : du texte qui contient des liens vers d'autres documents. C'est l'idée fondatrice du web.
- **Balisage** : on entoure le contenu de **balises** qui indiquent sa nature. Ceci est un titre, ceci un paragraphe, ceci une image.
- **Langage** : un ensemble de règles partagées entre toi et le navigateur.

HTML ne calcule rien, ne prend aucune décision, ne contient ni boucle ni condition. Il **décrit la structure et le sens** d'un contenu. Trois langages se partagent le travail dans une page web :

| Langage | Rôle | Analogie avec une maison |
| --- | --- | --- |
| **HTML** | Structure et sens du contenu | Les murs, les pièces, les portes |
| **CSS** | Apparence | La peinture, la décoration |
| **JavaScript** | Comportement | L'électricité, les interrupteurs |

Une maison sans peinture reste habitable. Une maison sans murs, non. C'est pour cela qu'on commence par HTML : une page bien structurée fonctionne même si le CSS ou le JavaScript ne se charge pas, par exemple sur une connexion mobile instable, ce qui arrive souvent en Afrique de l'Ouest.

## Le trajet d'une page web

Quand tu tapes une adresse dans la barre du navigateur, voici ce qui se passe, très simplifié :

1. Le navigateur envoie une **requête** au serveur qui héberge le site.
2. Le serveur répond avec un fichier texte : le document HTML.
3. Le navigateur **analyse** (on dit *parser*) ce texte et construit en mémoire un arbre d'objets appelé le **DOM** (*Document Object Model*).
4. Il découvre au passage d'autres ressources (feuilles CSS, images, scripts) et les télécharge.
5. Il calcule la mise en page et **affiche** le résultat.

Le point essentiel : ton fichier HTML est du **texte brut**. Tu peux l'écrire dans le Bloc-notes. C'est le navigateur qui lui donne vie. Et même si tu fais une faute, il essaie de deviner ce que tu voulais dire au lieu d'afficher une erreur, ce qui est pratique mais peut cacher des bugs.

> **À retenir** : HTML est un fichier texte. Le navigateur le transforme en un arbre (le DOM) puis l'affiche. Plus ton HTML est propre, plus le résultat est prévisible.

:::quiz
Quel est le rôle principal de HTML dans une page web ?
- [ ] Définir les couleurs et les polices
- [ ] Exécuter des calculs et des conditions
- [x] Décrire la structure et le sens du contenu
- [ ] Stocker les données dans une base
> HTML est un langage de balisage : il structure le contenu et lui donne du sens. L'apparence relève de CSS, le comportement de JavaScript.
:::

## Anatomie d'un élément

L'unité de base s'appelle un **élément**. Il est généralement composé d'une balise ouvrante, d'un contenu et d'une balise fermante :

```html
<p>Bienvenue sur DevRoad</p>
```

- `<p>` est la **balise ouvrante** ;
- `Bienvenue sur DevRoad` est le **contenu** ;
- `</p>` est la **balise fermante**, identique à l'ouvrante avec une barre oblique en plus.

L'ensemble des trois forme **l'élément** `p`, pour *paragraph*. On dit souvent « balise » à la place d'« élément », c'est toléré, mais garde la distinction en tête : la balise est l'étiquette, l'élément est l'objet complet.

### Les éléments vides

Certains éléments n'ont pas de contenu, donc pas de balise fermante :

```html
<br>
<hr>
<img src="logo.png" alt="Logo de DevRoad">
<input type="text">
```

`br` insère un retour à la ligne, `hr` une ligne de séparation, `img` une image, `input` un champ de saisie.

### L'imbrication

Les éléments se placent **les uns dans les autres**, comme des poupées russes. La règle : ce qui s'ouvre en dernier se ferme en premier.

```html
<p>Apprends <strong>HTML</strong> en un jour.</p>
```

Ici `strong` est **enfant** de `p`, et `p` est **parent** de `strong`. Cette relation parent-enfant dessine l'arbre du DOM. Une imbrication croisée comme `<p><strong>texte</p></strong>` est une erreur : le navigateur la corrige à sa façon, parfois de manière surprenante.

### Les attributs

Une balise ouvrante peut porter des **attributs**, qui précisent son comportement. Ils s'écrivent `nom="valeur"` :

```html
<a href="https://devroad.app" target="_blank" rel="noopener">Visiter DevRoad</a>
```

Ici `href` donne la destination du lien, `target` demande à l'ouvrir dans un nouvel onglet, `rel` précise la relation avec la page cible. Quelques règles :

- les attributs se placent dans la balise **ouvrante** uniquement ;
- séparés par des espaces, sans virgule ;
- la valeur va entre guillemets doubles (`"`) ;
- certains attributs sont **booléens** : leur seule présence suffit, comme `disabled` ou `required`.

Deux attributs existent sur presque tous les éléments : `id` (identifiant **unique** dans la page) et `class` (une ou plusieurs étiquettes réutilisables, très utilisées par CSS).

```html
<p id="intro" class="texte important">Commence ici.</p>
```

## Le squelette d'un document

Tout document HTML complet suit la même structure. Crée un dossier `mon-site` avec un fichier `index.html` :

```html
<!DOCTYPE html>
<html lang="fr">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Ma première page</title>
  </head>
  <body>
    <h1>Bonjour le web</h1>
    <p>Voici ma première page HTML.</p>
  </body>
</html>
```

Reprenons chaque ligne, car aucune n'est décorative :

- `<!DOCTYPE html>` indique au navigateur d'utiliser les règles modernes. Sans elle, il bascule en « mode quirks » avec des comportements historiques imprévisibles.
- `<html lang="fr">` est la racine du document. L'attribut `lang` déclare la langue : indispensable pour les lecteurs d'écran, la traduction automatique et le référencement.
- `<head>` contient les **métadonnées** : tout ce qui concerne la page mais ne s'affiche pas dans sa zone de contenu.
- `<meta charset="UTF-8">` déclare l'encodage. Sans lui, « é » peut devenir « Ã© ». Pour le français, c'est vital.
- `<meta name="viewport" ...>` demande au navigateur mobile d'utiliser la vraie largeur de l'écran. Sans elle, ta page s'affiche en miniature sur téléphone.
- `<title>` donne le titre de l'onglet, des favoris et des résultats de recherche.
- `<body>` contient tout ce que l'utilisateur voit.

> **Attention** : n'écris jamais de contenu visible dans le `head`, ni de métadonnées dans le `body`. Chaque partie a son rôle.

Ouvre le fichier en double-cliquant dessus : il s'affiche dans ton navigateur. Modifie le texte, enregistre, actualise la page (F5). Tu viens de comprendre la boucle de travail du développeur web.

## Les commentaires et les espaces

Un commentaire est ignoré par le navigateur. Il sert à t'expliquer ou à désactiver temporairement du code :

```html
<!-- Section d'introduction : à compléter -->
<p>Texte visible</p>
```

Autre particularité : HTML **réduit les espaces**. Plusieurs espaces, tabulations et retours à la ligne consécutifs sont affichés comme un seul espace. Écrire trois lignes vides dans ton code ne crée donc aucun espace à l'écran. Pour séparer visuellement des blocs, on utilise CSS, jamais des `<br>` en série.

```html
<p>Ligne     avec     beaucoup
d'espaces
et de retours</p>
```

Ce paragraphe s'affichera : « Ligne avec beaucoup d'espaces et de retours ». Cela te permet d'indenter ton code librement pour le rendre lisible.

## Bloc ou en ligne ?

Par défaut, les éléments se comportent de deux manières :

| Type | Comportement | Exemples |
| --- | --- | --- |
| **Bloc** | Commence sur une nouvelle ligne et occupe toute la largeur disponible | `p`, `h1` à `h6`, `div`, `ul`, `section` |
| **En ligne** | Se place dans le flux du texte, ne prend que la place nécessaire | `a`, `strong`, `em`, `span`, `img` |

```html
<p>Un paragraphe contient du texte avec un <strong>mot important</strong>
et un <a href="#">lien</a> en ligne.</p>
<p>Un second paragraphe commence sur sa propre ligne.</p>
```

On met normalement des éléments en ligne **dans** des éléments bloc, pas l'inverse. Ce comportement par défaut peut être modifié avec CSS, mais il aide à comprendre pourquoi une page « se comporte » comme elle le fait avant toute mise en forme.

Deux conteneurs génériques existent quand aucun élément ne convient : `div` (bloc) et `span` (en ligne). Ils n'ont **aucun sens** en eux-mêmes. Nous verrons au prochain chapitre pourquoi il faut leur préférer, quand c'est possible, des éléments porteurs de sens.

:::quiz
Dans ce code, quel est l'élément parent de `strong` ?
`<p>Apprends <strong>HTML</strong> vite.</p>`
- [ ] body
- [x] p
- [ ] html
- [ ] Aucun, il est seul
> `strong` est placé à l'intérieur de `p`, qui est donc son parent direct. Le parent de `p` sera ici `body`.
:::

## Inspecter une page avec les outils de développement

Tu disposes d'un laboratoire gratuit : le site que tu visites. Dans Chrome, Edge ou Firefox, fais un **clic droit** sur un élément puis **Inspecter** (ou touche F12). Le panneau **Éléments** montre le DOM de la page :

- survole une ligne : l'élément correspondant est mis en évidence ;
- double-clique sur un texte pour le modifier en direct (rien n'est sauvegardé, un rechargement efface tout) ;
- l'onglet **Console** affiche les erreurs ;
- l'icône de téléphone active la simulation mobile.

Prends l'habitude d'inspecter les pages que tu admires. Tu verras comment elles sont réellement construites, et tu comprendras vite que derrière un site complexe se cache surtout une bonne structure.

> **Astuce** : « Afficher le code source » (Ctrl+U) montre le HTML **tel que reçu du serveur**. Le panneau Éléments montre le DOM **après** exécution de JavaScript. Pour une page React, la différence est énorme.

:::quiz
Quelle ligne doit apparaître en toute première position d'un document HTML moderne ?
- [ ] Le titre de la page
- [ ] La balise html avec l'attribut lang
- [x] La déclaration du type de document, DOCTYPE html
- [ ] La balise meta charset
> La déclaration DOCTYPE vient avant tout : elle active le mode standard du navigateur. La balise html et les métadonnées suivent.
:::

## Atelier guidé : la page de présentation de DevRoad

Compte quarante minutes. Tu vas écrire à la main une petite page de présentation.

1. Crée un dossier `devroad-html` et un fichier `index.html` dans ton éditeur.
2. Écris le squelette complet : doctype, `html` avec `lang="fr"`, `head` avec charset, viewport et titre « DevRoad, apprends à coder », puis `body`.
3. Dans le `body`, ajoute un titre `h1` : « DevRoad ».
4. Ajoute un paragraphe qui présente la plateforme en deux phrases, avec le mot « roadmaps » mis en valeur par `strong`.
5. Ajoute une ligne de séparation avec `hr`.
6. Ajoute un second paragraphe contenant un lien vers `https://developer.mozilla.org/fr/` avec le texte « Documentation MDN » ouvert dans un nouvel onglet.
7. Ajoute un commentaire HTML au-dessus du lien expliquant son rôle.
8. Ouvre la page dans le navigateur, inspecte-la et repère l'arbre : `html`, `head`, `body`, puis leurs enfants.
9. Provoque volontairement une erreur : supprime une balise fermante, recharge, et observe comment le navigateur répare le DOM dans l'inspecteur.
10. Remets la balise et vérifie ta page avec le validateur officiel (validator.w3.org, onglet « Validate by Direct Input »).

Auto-évaluation : sans regarder ce chapitre, peux-tu écrire de mémoire le squelette d'un document et expliquer le rôle de chaque ligne ? Peux-tu dire pourquoi la déclaration de langue et l'encodage comptent ? Si oui, passe au chapitre suivant. Sinon, refais l'atelier en changeant le contenu.

## Erreurs fréquentes

- **Oublier la balise fermante.** Le navigateur devine, souvent mal, et la mise en page casse plus loin.
- **Croiser les balises.** `<p><em>texte</p></em>` est invalide : respecte l'ordre d'ouverture et de fermeture.
- **Mettre des guillemets typographiques** (« » ou “ ”) autour des valeurs d'attributs, ce qui arrive en copiant depuis un traitement de texte. Utilise toujours des guillemets droits `"`.
- **Utiliser `<br>` pour créer de l'espace.** Cela mélange contenu et présentation ; l'espace se règle en CSS.
- **Dupliquer un `id`.** Il doit être unique dans la page, sinon les liens d'ancre et le JavaScript se comportent mal.
- **Oublier `<meta charset>`.** Les accents s'affichent de travers.
- **Nommer le fichier d'accueil autrement que `index.html`.** La plupart des serveurs cherchent ce nom par défaut.

## Bonnes pratiques

- Écris tes balises et attributs en **minuscules** et tes valeurs entre guillemets doubles.
- **Indente** de deux espaces chaque niveau d'imbrication pour voir l'arbre d'un coup d'œil.
- Déclare toujours `lang`, `charset`, `viewport` et `title`.
- Donne des noms de fichiers sans espaces ni accents : `page-contact.html`, pas `Page Contact.html`.
- Valide régulièrement ton code avec le validateur du W3C.
- Choisis un élément pour **ce qu'il signifie**, pas pour l'effet visuel qu'il produit par défaut.
- Consulte la documentation MDN (developer.mozilla.org) : c'est la référence de tous les professionnels.

## À retenir

- HTML est un langage de **balisage** qui décrit la structure et le sens d'un contenu ; CSS gère l'apparence, JavaScript le comportement.
- Le navigateur lit le texte HTML, construit le **DOM** puis affiche la page.
- Un élément = balise ouvrante + contenu + balise fermante ; les éléments vides n'ont pas de fermeture.
- Les éléments s'imbriquent en respectant l'ordre : le dernier ouvert est le premier fermé.
- Les attributs `nom="valeur"` précisent un élément ; `id` est unique, `class` est réutilisable.
- Un document valide contient `DOCTYPE`, `html lang`, `head` (charset, viewport, title) et `body`.
- Les éléments sont de type **bloc** ou **en ligne** ; `div` et `span` sont des conteneurs sans signification.
- Les outils de développement du navigateur sont ton meilleur professeur.
