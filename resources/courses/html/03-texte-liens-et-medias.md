---
title: Texte, liens et médias
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Une page web, c'est du texte relié à d'autres pages et enrichi d'images, de sons et de vidéos. Le **lien** est l'âme du web, et les **médias** en sont le visage. Mais ils pèsent lourd : sur un réseau mobile avec un forfait data limité, une image mal préparée peut faire fuir un visiteur. Ce chapitre t'apprend à les intégrer correctement et légèrement.

À la fin du chapitre, tu seras capable de :

- créer des liens vers une page, une ancre, un e-mail ou un téléphone ;
- distinguer chemins absolus et relatifs sans te perdre dans les dossiers ;
- insérer des images avec un texte alternatif pertinent ;
- proposer plusieurs tailles et formats avec `srcset` et `picture` ;
- charger les images paresseusement pour économiser la bande passante ;
- intégrer de l'audio, de la vidéo et un contenu externe (iframe) de façon sûre.

Prérequis : les chapitres « Comprendre HTML » et « Sémantique ». Prévois deux heures.

## Les liens : l'élément a

Un lien se crée avec l'élément `a` (*anchor*, ancre) et son attribut `href` (*hypertext reference*) :

```html
<a href="https://developer.mozilla.org/fr/">Documentation MDN</a>
```

Le texte du lien est ce que l'utilisateur voit et clique. Il doit **se comprendre hors contexte**, car les lecteurs d'écran proposent de lister tous les liens d'une page.

```html
<!-- À éviter -->
<p>Pour lire la suite, <a href="/article-12">cliquez ici</a>.</p>

<!-- Préférable -->
<p><a href="/article-12">Lire la suite : les bases de Laravel</a></p>
```

### Les différentes cibles

Un lien peut pointer vers bien plus qu'une page :

```html
<!-- Une autre page de ton site -->
<a href="/roadmaps">Toutes les roadmaps</a>

<!-- Une ancre dans la même page -->
<a href="#faq">Aller à la FAQ</a>
<h2 id="faq">Questions fréquentes</h2>

<!-- Une adresse e-mail -->
<a href="mailto:contact@devroad.app">Écrire à l'équipe</a>

<!-- Un numéro de téléphone (ouvre l'application d'appel sur mobile) -->
<a href="tel:+2250160706221">Appeler</a>

<!-- Un téléchargement -->
<a href="/docs/guide.pdf" download>Télécharger le guide (PDF, 2 Mo)</a>
```

Les ancres (`#faq`) reposent sur un `id` unique placé sur la cible. Elles servent aux sommaires et aux liens « Retour en haut ».

### Ouvrir dans un nouvel onglet

L'attribut `target="_blank"` ouvre le lien dans un nouvel onglet. Pour des raisons de sécurité et de confidentialité, ajoute `rel="noopener noreferrer"` pour les liens vers d'autres sites :

```html
<a href="https://exemple.com" target="_blank" rel="noopener noreferrer">
  Un site externe (nouvel onglet)
</a>
```

Les navigateurs récents appliquent `noopener` automatiquement, mais l'écrire reste une bonne habitude. **Préviens l'utilisateur** dans le texte quand un lien s'ouvre ailleurs : imposer un nouvel onglet sans le dire désoriente.

> **Astuce** : réserve `target="_blank"` aux cas utiles (document externe, formulaire en cours de remplissage). Par défaut, laisse l'utilisateur décider.

## Chemins absolus et relatifs

Une source de confusion permanente pour les débutants : comment écrire l'adresse d'un fichier ?

| Type | Exemple | Signification |
| --- | --- | --- |
| Absolu complet | `https://devroad.app/img/logo.png` | Adresse entière, valable partout |
| Relatif à la racine | `/img/logo.png` | Depuis la racine du site |
| Relatif au fichier | `img/logo.png` | Depuis le dossier du fichier actuel |
| Remonter d'un dossier | `../img/logo.png` | Un niveau au-dessus |

Imagine cette arborescence :

```text
mon-site/
├── index.html
├── contact.html
├── img/
│   └── logo.png
└── cours/
    └── html.html
```

Depuis `index.html`, l'image s'écrit `img/logo.png`. Depuis `cours/html.html`, qui est un niveau plus bas, elle devient `../img/logo.png`. Le chemin `/img/logo.png` fonctionne partout, mais seulement une fois le site hébergé sur un serveur, pas en ouvrant le fichier directement.

Autres conseils : des noms de fichiers en minuscules, sans espaces ni accents (`photo-profil.jpg`), car les serveurs Linux distinguent majuscules et minuscules.

:::quiz
Tu es dans le fichier `cours/html.html` et tu veux afficher `img/logo.png` situé à la racine du site. Quel chemin relatif convient ?
- [ ] img/logo.png
- [ ] cours/img/logo.png
- [x] ../img/logo.png
- [ ] ./logo.png
> Le fichier est dans le dossier cours : il faut remonter d'un niveau avec .. pour retrouver le dossier img à la racine.
:::

## Les images : l'élément img

```html
<img src="img/roadmap-laravel.jpg"
     alt="Schéma de la roadmap Laravel, de l'installation au déploiement"
     width="800" height="450">
```

Chaque attribut a une raison d'être :

- `src` : le chemin du fichier ;
- `alt` : le **texte alternatif**, lu par les lecteurs d'écran et affiché si l'image ne charge pas ;
- `width` et `height` : les dimensions intrinsèques en pixels. Elles permettent au navigateur de réserver la place avant le chargement et d'éviter que la page « saute » (c'est ce qu'on appelle le décalage de mise en page, mesuré par le score CLS).

### Écrire un bon texte alternatif

Le `alt` répond à la question : « que dirais-je à quelqu'un au téléphone pour lui décrire cette image **dans ce contexte** ? »

- Image informative : décris l'information. `alt="Courbe de progression : 60 % en un mois"`.
- Image **décorative** (un trait, un motif) : `alt=""` vide, pour que le lecteur d'écran l'ignore. N'omets **jamais** l'attribut, car sans lui certains lecteurs lisent le nom du fichier.
- Image dans un lien : le `alt` décrit la **destination**, pas l'image. `alt="Retour à l'accueil"` pour un logo cliquable.
- Évite « image de » ou « photo de » : le lecteur d'écran annonce déjà qu'il s'agit d'une image.

### Choisir le bon format

| Format | Usage idéal |
| --- | --- |
| **JPEG** | Photos |
| **PNG** | Captures, images avec transparence |
| **SVG** | Logos, icônes, schémas (vectoriel, net à toutes les tailles) |
| **WebP / AVIF** | Photos et illustrations, bien plus légers que JPEG et PNG |

Une photo de 4 Mo sortie d'un téléphone n'a rien à faire sur ta page. Redimensionne-la à la taille d'affichage réelle et compresse-la (Squoosh, TinyPNG). Viser moins de 150 Ko par image de contenu est un bon objectif.

## Images adaptatives : srcset, sizes et picture

Un smartphone n'a pas besoin de l'image prévue pour un écran 4K. L'attribut `srcset` propose plusieurs versions et laisse le navigateur choisir :

```html
<img src="img/hero-800.jpg"
     srcset="img/hero-400.jpg 400w,
             img/hero-800.jpg 800w,
             img/hero-1600.jpg 1600w"
     sizes="(max-width: 600px) 100vw, 800px"
     alt="Une développeuse code dans un espace de coworking à Abidjan"
     width="800" height="450">
```

- `srcset` liste les fichiers avec leur largeur réelle (`400w`) ;
- `sizes` indique la largeur à laquelle l'image sera affichée : ici, toute la largeur sur petit écran, sinon 800 pixels ;
- le navigateur combine ces informations avec la densité d'écran et la qualité de connexion pour télécharger la bonne version.

Pour changer de **format** ou faire un **recadrage différent** selon l'écran, on utilise `picture` :

```html
<picture>
  <source srcset="img/hero.avif" type="image/avif">
  <source srcset="img/hero.webp" type="image/webp">
  <img src="img/hero.jpg" alt="Équipe en réunion" width="800" height="450">
</picture>
```

Le navigateur prend la première `source` qu'il comprend, et se rabat sur `img` sinon. L'élément `img` final est **obligatoire**, c'est lui qui porte le `alt`.

### Le chargement paresseux

Les images situées sous la ligne de flottaison n'ont pas besoin d'être téléchargées tout de suite :

```html
<img src="img/galerie-3.jpg" alt="Atelier de formation" width="600" height="400" loading="lazy">
```

Avec `loading="lazy"`, l'image ne se charge que lorsque l'utilisateur approche. **N'utilise pas** cet attribut sur l'image principale tout en haut de la page, qui doit apparaître immédiatement. Pour elle, tu peux même ajouter `fetchpriority="high"`.

:::quiz
Une image purement décorative (un trait de séparation) doit avoir :
- [ ] Pas d'attribut alt du tout
- [ ] Un alt qui contient le nom du fichier
- [x] Un attribut alt vide
- [ ] Un alt « image décorative »
> Un alt vide indique aux technologies d'assistance d'ignorer l'image. Omettre l'attribut est une erreur, car certains lecteurs annoncent alors le nom du fichier.
:::

## Audio et vidéo

HTML sait lire sons et vidéos nativement, sans plugin :

```html
<video controls width="640" height="360" poster="img/apercu.jpg" preload="metadata">
  <source src="media/presentation.webm" type="video/webm">
  <source src="media/presentation.mp4" type="video/mp4">
  <track kind="subtitles" src="media/presentation-fr.vtt" srclang="fr" label="Français">
  <p>Ton navigateur ne lit pas cette vidéo. <a href="media/presentation.mp4">Télécharge-la ici.</a></p>
</video>

<audio controls src="media/podcast-episode-1.mp3">
  Ton navigateur ne supporte pas l'audio.
</audio>
```

À retenir sur ces éléments :

- `controls` affiche les boutons de lecture. **Garde-le toujours**, sauf si tu fournis tes propres contrôles accessibles ;
- `poster` est l'image affichée avant la lecture ;
- `preload="metadata"` ne charge que les informations de base : précieux pour la data mobile ;
- `track` ajoute des **sous-titres** : indispensables pour les personnes sourdes et utiles dans les lieux bruyants ;
- `autoplay` avec son est bloqué par les navigateurs et gênant. Si tu veux une vidéo d'ambiance, utilise `autoplay muted loop playsinline`, mais reste mesuré : elle pèse lourd.

Pour une longue vidéo, héberge-la sur une plateforme spécialisée (YouTube, Vimeo, Bunny) plutôt que sur ton serveur.

## Intégrer du contenu externe : iframe

L'élément `iframe` affiche une autre page à l'intérieur de la tienne : vidéo YouTube, carte, widget de paiement.

```html
<iframe
  src="https://www.youtube-nocookie.com/embed/IDENTIFIANT"
  title="Présentation de DevRoad en 3 minutes"
  width="560" height="315"
  loading="lazy"
  allowfullscreen
  referrerpolicy="strict-origin-when-cross-origin">
</iframe>
```

L'attribut `title` est obligatoire pour l'accessibilité : il nomme le cadre. Comme une iframe charge du code étranger, applique le principe de précaution : ne l'utilise qu'avec des sources de confiance, et restreins ses droits avec `sandbox` si tu intègres un contenu moins sûr. Préfère le domaine `youtube-nocookie.com`, qui limite le pistage.

> **Attention** : chaque iframe, image ou vidéo supplémentaire alourdit ta page. Sur une audience mobile en Afrique de l'Ouest, vise une page de moins de 1 Mo au premier affichage.

## Mettre en forme le texte avec sens

Dernier volet : le texte courant. Quelques éléments en ligne à bien employer :

```html
<p>Le mot <dfn>hypertexte</dfn> désigne un texte relié à d'autres.</p>
<p>La balise <code>&lt;a&gt;</code> crée un lien.</p>
<p>Température : H<sub>2</sub>O, surface en m<sup>2</sup>.</p>
<p>Prix : <del>25 000 FCFA</del> <ins>20 000 FCFA</ins></p>
<p>Une phrase<br>avec un retour à la ligne volontaire.</p>
<pre><code>function bonjour() {
  console.log('Salut');
}</code></pre>
```

Pour afficher une balise comme texte, remplace `<` par `&lt;` et `>` par `&gt;` (ces séquences s'appellent des **entités**). `&amp;` produit le symbole « et commercial » et `&nbsp;` une espace insécable, utile entre un nombre et son unité (`20&nbsp;000&nbsp;FCFA`) pour éviter qu'un retour à la ligne les sépare.

:::quiz
Quel attribut permet de ne charger une image que lorsqu'elle approche de la zone visible ?
- [ ] preload="none"
- [ ] defer
- [x] loading="lazy"
- [ ] async
> loading="lazy" diffère le téléchargement des images hors écran et économise de la bande passante.
:::

## Atelier guidé : une page « À propos » riche et légère

Compte une heure.

1. Crée l'arborescence : `a-propos.html`, un dossier `img` et un dossier `media`. Prépare deux photos (une grande, une petite) et réduis-les sous 150 Ko.
2. Écris le squelette complet avec `header`, `nav`, `main` et `footer` (chapitre précédent).
3. Dans le `header`, ajoute un logo dans un lien vers `index.html`, avec un `alt` qui décrit la destination.
4. Dans `main`, ajoute un `h1`, un paragraphe d'introduction et une `figure` contenant ta photo principale avec `figcaption`, `width` et `height`.
5. Transforme cette photo en `picture` avec une source WebP et un repli JPEG.
6. Ajoute une seconde image avec `srcset` et `sizes`, plus `loading="lazy"`.
7. Crée un sommaire en haut avec trois liens d'ancre vers trois `h2` situés plus bas. Ajoute un lien « Retour en haut ».
8. Ajoute une section « Contact » avec un lien `mailto:` et un lien `tel:`.
9. Ajoute un lien externe en nouvel onglet avec `rel="noopener noreferrer"` et une mention claire.
10. Intègre une vidéo YouTube en `iframe` avec un `title`, ou une balise `video` avec `controls` et `poster`.
11. Ouvre l'onglet Réseau des outils de développement et note le poids total de la page. Cherche une image à alléger.

Auto-évaluation : chaque image a-t-elle un `alt` adapté à son contexte (ou vide si décorative) ? Chaque lien se comprend-il hors contexte ? Les chemins fonctionnent-ils depuis un sous-dossier ?

## Erreurs fréquentes

- **Oublier l'attribut `alt`.** Mets-le toujours, même vide pour les images décoratives.
- **Écrire « cliquez ici » comme texte de lien.** Il ne dit rien hors contexte.
- **Utiliser des chemins avec espaces ou majuscules.** Cela casse sur un serveur Linux.
- **Se tromper dans les chemins relatifs.** Un `../` manquant et l'image disparaît.
- **Charger des images énormes.** Redimensionne et compresse avant de publier.
- **Mettre `loading="lazy"` sur l'image principale.** Elle apparaît plus tard et pénalise la performance perçue.
- **Omettre `width` et `height`.** La page saute pendant le chargement.
- **Intégrer une iframe sans `title`.** Elle devient muette pour les lecteurs d'écran.

## Bonnes pratiques

- Donne à chaque lien un texte explicite, et préviens quand il ouvre un nouvel onglet ou un fichier lourd (« PDF, 2 Mo »).
- Rédige le `alt` selon le contexte : l'information utile, pas une description littérale de pixels.
- Compresse et convertis les images en WebP ou AVIF avec un repli JPEG.
- Fournis `width` et `height` pour toutes les images.
- Utilise `srcset` pour les images de contenu importantes et `loading="lazy"` pour celles du bas de page.
- Ajoute des sous-titres à tes vidéos et ne lance jamais de son automatiquement.
- Privilégie le SVG pour les logos et icônes.
- Mesure le poids de ta page et vise un chargement rapide, même en 3G.

## À retenir

- Le lien `a` avec `href` relie des pages, des ancres, des e-mails (`mailto:`) et des numéros (`tel:`).
- Un chemin relatif dépend du fichier courant ; `../` remonte d'un dossier ; un chemin commençant par `/` part de la racine du site.
- Toute image a un `alt` pertinent (vide si décorative), des dimensions et un poids maîtrisé.
- `srcset`, `sizes` et `picture` servent la bonne image au bon écran ; `loading="lazy"` économise la data.
- `video` et `audio` s'utilisent avec `controls`, des sous-titres et un chargement mesuré.
- Une `iframe` demande un `title` et une source de confiance.
- Les entités (`&lt;`, `&amp;`, `&nbsp;`) permettent d'afficher des caractères spéciaux.
