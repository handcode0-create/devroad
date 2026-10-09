---
title: SEO et structure
minutes: 120
level: intermediate
---

## Ce que tu vas apprendre

Construire un beau site ne sert à rien si personne ne le trouve. Le **SEO** (*Search Engine Optimization*, optimisation pour les moteurs de recherche) est l'ensemble des pratiques qui aident Google, Bing et les autres à comprendre ton contenu et à le proposer aux bonnes personnes. Bonne nouvelle : une grande partie du SEO technique est du pur HTML. C'est un atout concret quand tu vends tes services à un client entrepreneur : « ton site sera trouvé » est un argument fort.

À la fin du chapitre, tu seras capable de :

- expliquer comment un moteur de recherche explore, indexe et classe une page ;
- rédiger un `title` et une `meta description` efficaces ;
- structurer une page avec un plan de titres exploitable ;
- configurer l'URL canonique, les balises `robots` et la langue ;
- partager proprement ta page sur les réseaux avec Open Graph ;
- ajouter des données structurées au format JSON-LD ;
- vérifier la performance et la compatibilité mobile.

Prérequis : les chapitres 1 à 5. Prévois deux heures.

## Comment fonctionne un moteur de recherche

Trois étapes se succèdent :

1. **L'exploration** (*crawl*) : un robot suit les liens de page en page et télécharge le HTML.
2. **L'indexation** : le moteur analyse le contenu, comprend de quoi il parle et le stocke dans son index géant.
3. **Le classement** (*ranking*) : à chaque recherche, il sélectionne les pages de l'index et les ordonne selon leur pertinence, leur qualité, leur vitesse, leur adaptation au mobile et de nombreux autres signaux.

Conséquence directe : un robot **lit ton HTML**. Un contenu absent du HTML initial, uniquement fabriqué par JavaScript après coup, est plus difficile à indexer. Un lien qui n'est pas une vraie balise `a` avec `href` n'est pas suivi. Et une page que rien ne relie au reste du site risque de ne jamais être découverte.

> **À retenir** : les robots sont des visiteurs qui ne voient pas ton CSS et ne cliquent pas sur les boutons. Écris ta page comme si elle devait se comprendre en texte brut, ce que la sémantique vue plus haut garantit déjà.

## Le titre et la description : ta vitrine dans les résultats

Dans une page de résultats, l'utilisateur voit généralement un titre bleu, une URL et deux lignes de description. Ces éléments viennent de ton `head` :

```html
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Roadmap Laravel : apprendre pas à pas | DevRoad</title>
  <meta name="description"
        content="Suis la roadmap Laravel de DevRoad : 12 chapitres pratiques, des exercices corrigés et un projet final. Gratuit, en français.">
  <link rel="canonical" href="https://devroad.app/roadmaps/laravel">
</head>
```

### Écrire un bon title

- **Unique** sur chaque page du site ;
- placé du plus important au moins important : le sujet d'abord, la marque à la fin ;
- d'environ **50 à 60 caractères** pour ne pas être tronqué ;
- naturel, avec le mot-clé principal, sans bourrage de mots-clés.

### Écrire une bonne meta description

La description n'influence pas directement le classement, mais elle détermine le **taux de clics** : c'est le slogan de ta page. Écris-la comme une invitation :

- environ **120 à 155 caractères** ;
- une promesse claire et un bénéfice concret ;
- unique pour chaque page ;
- elle peut se terminer par un appel à l'action léger (« Découvre-la »).

Google peut la réécrire s'il juge qu'un autre extrait répond mieux à la recherche, mais en proposer une bonne reste utile.

:::quiz
Quel est l'effet principal d'une bonne meta description ?
- [ ] Elle augmente directement la vitesse de chargement
- [x] Elle incite les internautes à cliquer sur ton résultat
- [ ] Elle remplace la balise title
- [ ] Elle s'affiche dans le corps de la page
> La meta description est l'extrait montré dans les résultats : elle sert surtout à convaincre de cliquer, ce qui améliore le trafic.
:::

## La structure du contenu

Les robots utilisent la structure pour comprendre la hiérarchie des idées. Reprends les acquis du chapitre « Sémantique » en y ajoutant l'angle SEO :

- **un seul `h1`** qui reprend le sujet de la page, proche du `title` sans forcément l'être à l'identique ;
- des `h2` et `h3` qui reflètent les questions que se posent tes lecteurs ;
- des paragraphes courts et des listes quand c'est pertinent ;
- des éléments sémantiques `main`, `article`, `nav`, `footer` qui délimitent le contenu principal du reste.

### Les liens internes

Les liens entre tes pages permettent aux robots de les découvrir et répartissent l'importance au sein du site. Utilise des **textes de lien descriptifs** :

```html
<!-- Faible -->
<a href="/roadmaps/laravel">Cliquez ici</a>

<!-- Fort -->
<a href="/roadmaps/laravel">Découvrir la roadmap Laravel complète</a>
```

### Les URL

Une URL lisible aide les humains et les robots :

```text
Bon  : https://devroad.app/roadmaps/laravel
Bon  : https://devroad.app/fiches/flexbox
Mauvais : https://devroad.app/index.php?p=482&cat=7&x=12
```

Utilise des minuscules, des tirets pour séparer les mots, pas d'accents ni d'espaces, et reflète la hiérarchie du site.

### Les images

Le `alt` sert aussi au SEO (recherche d'images). Nomme tes fichiers de façon descriptive (`roadmap-laravel.webp` plutôt que `IMG_0482.jpg`) et fournis des dimensions pour éviter les décalages de mise en page.

## Contrôler l'indexation : robots, canonical, langue

### La balise meta robots

Par défaut, une page est indexée et ses liens suivis. Pour changer ce comportement :

```html
<!-- Page à ne pas faire apparaître dans les résultats (espace admin, page de merci) -->
<meta name="robots" content="noindex, nofollow">
```

Utilise `noindex` pour des pages sans intérêt pour la recherche : tableau de bord, résultats de recherche interne, pages de confirmation. Attention : ne mets **jamais** `noindex` sur une page que tu veux voir classée, et pense à le retirer à la mise en production d'un site construit en préproduction.

### L'URL canonique

Une même page peut être accessible par plusieurs adresses (`?utm_source=facebook`, avec ou sans `www`, avec ou sans slash final). Le moteur y voit du **contenu dupliqué**. La balise canonique désigne l'adresse de référence :

```html
<link rel="canonical" href="https://devroad.app/roadmaps/laravel">
```

### La langue et les variantes

L'attribut `lang` de la balise `html` est déjà en place. Si ton site existe en plusieurs langues, relie les versions avec `hreflang` :

```html
<link rel="alternate" hreflang="fr" href="https://devroad.app/fr/">
<link rel="alternate" hreflang="en" href="https://devroad.app/en/">
<link rel="alternate" hreflang="x-default" href="https://devroad.app/">
```

### Les fichiers robots.txt et sitemap.xml

Deux fichiers placés à la racine du site complètent le dispositif :

```text
# robots.txt
User-agent: *
Disallow: /admin/
Sitemap: https://devroad.app/sitemap.xml
```

`robots.txt` indique aux robots ce qu'ils ne doivent pas explorer. `sitemap.xml` liste les pages importantes avec leur date de modification. Tu soumets ce dernier à la **Google Search Console**, l'outil gratuit qui t'indique quelles pages sont indexées, quelles recherches amènent des visiteurs et quelles erreurs existent. Tout site professionnel devrait y être déclaré.

## Open Graph et cartes de partage

Quand quelqu'un colle ton lien dans WhatsApp, Facebook ou LinkedIn, l'application affiche une carte avec une image, un titre et une description. Elle les lit dans des balises **Open Graph** :

```html
<meta property="og:type" content="website">
<meta property="og:title" content="Roadmap Laravel | DevRoad">
<meta property="og:description" content="12 chapitres pratiques pour maîtriser Laravel, gratuits et en français.">
<meta property="og:url" content="https://devroad.app/roadmaps/laravel">
<meta property="og:image" content="https://devroad.app/img/og-laravel.jpg">
<meta property="og:locale" content="fr_FR">

<meta name="twitter:card" content="summary_large_image">
```

L'image idéale mesure environ 1200 par 630 pixels, avec un texte lisible en petit. Pour un entrepreneur qui partage beaucoup son lien sur WhatsApp et les réseaux, c'est souvent le premier contact avec ton travail : une belle carte de partage fait une vraie différence. Utilise des **URL absolues** dans ces balises, et teste avec les débogueurs des plateformes (Facebook Sharing Debugger, par exemple), qui gardent les anciennes versions en cache.

## Données structurées : JSON-LD

Les **données structurées** décrivent explicitement la nature du contenu dans un vocabulaire commun (schema.org). Les moteurs peuvent alors afficher des résultats enrichis : étoiles, prix, questions dépliables, fil d'Ariane. On les place dans une balise `script` de type JSON-LD :

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "Course",
  "name": "Roadmap Laravel",
  "description": "12 chapitres pratiques pour maîtriser Laravel.",
  "provider": {
    "@type": "Organization",
    "name": "DevRoad",
    "sameAs": "https://devroad.app"
  },
  "inLanguage": "fr"
}
</script>
```

Quelques types courants : `Organization`, `LocalBusiness` (parfait pour un commerce à Abidjan avec adresse, horaires et téléphone), `Article`, `Product`, `FAQPage`, `BreadcrumbList`. Valide-les avec l'outil « Test des résultats enrichis » de Google. Il faut que le JSON-LD décrive ce qui est réellement visible sur la page ; le tromper est sanctionné.

:::quiz
À quoi sert la balise link rel canonical ?
- [ ] À accélérer le chargement de la page
- [ ] À interdire l'indexation de la page
- [x] À indiquer l'adresse de référence quand plusieurs URL donnent le même contenu
- [ ] À traduire la page automatiquement
> La balise canonique évite les problèmes de contenu dupliqué en désignant l'URL principale à indexer.
:::

## Performance et mobile : des facteurs de classement

Google classe selon l'expérience réelle. Les **Core Web Vitals** mesurent trois choses :

| Indicateur | Mesure | Objectif |
| --- | --- | --- |
| **LCP** (*Largest Contentful Paint*) | Temps d'affichage du plus grand élément visible | Moins de 2,5 s |
| **INP** (*Interaction to Next Paint*) | Réactivité aux interactions | Moins de 200 ms |
| **CLS** (*Cumulative Layout Shift*) | Stabilité visuelle | Moins de 0,1 |

Les leviers HTML pour les améliorer sont ceux de tout ce cours :

- images compressées, aux dimensions déclarées, en chargement paresseux (sauf l'image principale) ;
- `<meta name="viewport">` correctement renseignée : Google indexe en priorité la **version mobile** ;
- scripts chargés avec `defer` pour ne pas bloquer l'affichage ;
- peu de polices, préchargées si critiques ;
- HTML léger et bien structuré.

```html
<script src="/js/app.js" defer></script>
<link rel="preload" as="image" href="/img/hero.webp" fetchpriority="high">
```

Mesure avec **PageSpeed Insights** et Lighthouse. Dans un contexte de réseau mobile et de forfaits limités, un site rapide est à la fois un meilleur SEO et un meilleur service.

> **Astuce** : le meilleur « hack » SEO reste un contenu utile, original et bien organisé. Les techniques ne remplacent pas un site qui répond vraiment à la question de l'internaute.

## Atelier guidé : optimiser une page de roadmap

Compte une heure et demie. Prends la page `a-propos.html` ou une nouvelle page `roadmap-laravel.html`.

1. Écris un `title` unique de moins de 60 caractères, avec le sujet en premier et la marque à la fin.
2. Rédige une meta description de 120 à 155 caractères qui donne envie de cliquer.
3. Vérifie le plan de titres : un seul `h1`, des `h2` qui répondent à des questions, aucun saut de niveau.
4. Ajoute une URL canonique absolue.
5. Ajoute les balises Open Graph et Twitter avec une image de 1200 par 630 pixels.
6. Ajoute un bloc JSON-LD de type `Course` ou `Organization` et valide-le avec l'outil de test des résultats enrichis.
7. Crée `robots.txt` et un `sitemap.xml` de trois URL à la racine du dossier.
8. Ajoute un fil d'Ariane dans une `nav` avec `aria-label="Fil d'Ariane"` et une liste ordonnée de liens :

```html
<nav aria-label="Fil d'Ariane">
  <ol>
    <li><a href="/">Accueil</a></li>
    <li><a href="/roadmaps">Roadmaps</a></li>
    <li aria-current="page">Laravel</li>
  </ol>
</nav>
```

9. Vérifie que les liens internes ont des textes descriptifs.
10. Lance Lighthouse (catégories Performance, SEO, Accessibilité) et corrige deux problèmes remontés.
11. Partage le lien dans une conversation WhatsApp (ou le débogueur Facebook) et vérifie la carte.

Auto-évaluation : sais-tu expliquer la différence entre `title` et `h1` ? Entre `noindex` et `robots.txt` ? Pourquoi les URL absolues dans Open Graph ?

## Erreurs fréquentes

- **Le même `title` partout.** Le moteur ne distingue plus tes pages.
- **Le bourrage de mots-clés.** Cela nuit à la lecture et peut être pénalisé.
- **Oublier de retirer `noindex` à la mise en ligne.** Le site disparaît des résultats.
- **Plusieurs `h1`, ou aucun.** La page n'a plus de sujet clair.
- **Des liens fabriqués par JavaScript sans `href`.** Les robots ne les suivent pas.
- **Des URL relatives dans Open Graph.** Les réseaux ne trouvent pas l'image.
- **Un contenu textuel seulement dans des images.** Le moteur ne le lit pas.
- **Négliger le mobile.** Google indexe d'abord la version mobile.

## Bonnes pratiques

- Un `title` et une meta description uniques par page, rédigés pour des humains.
- Un plan de titres limpide et un contenu structuré par la sémantique.
- Des URL lisibles, stables et cohérentes avec la hiérarchie du site.
- Une balise canonique sur toutes les pages indexables.
- Un `sitemap.xml` soumis à la Google Search Console, et un suivi régulier de celle-ci.
- Des images légères avec `alt`, nom de fichier descriptif et dimensions.
- Des données structurées fidèles au contenu visible.
- Une mesure régulière avec Lighthouse et PageSpeed Insights.

## À retenir

- Un moteur **explore**, **indexe** puis **classe** : il lit ton HTML, pas ton design.
- Le `title` (50 à 60 caractères) et la meta description (120 à 155) forment ta vitrine dans les résultats.
- Un seul `h1`, une hiérarchie propre, des liens internes descriptifs et des URL lisibles structurent le contenu.
- `canonical`, `robots`, `hreflang`, `robots.txt` et `sitemap.xml` contrôlent l'indexation.
- Open Graph soigne l'aperçu de tes liens partagés ; JSON-LD permet des résultats enrichis.
- La performance (LCP, INP, CLS) et le mobile influencent le classement.
- Le meilleur SEO reste un contenu utile et bien construit.
