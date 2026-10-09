---
title: Projet final HTML
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as parcouru tout le cours : structure, sémantique, médias, formulaires, accessibilité et SEO. Il est temps de les assembler dans un livrable que tu peux montrer à un client ou à un recruteur. Ce projet est calqué sur une vraie commande de HANCODE STUDIO ou de n'importe quelle agence : un **site vitrine multi-pages en HTML pur**, sans framework, propre, accessible et référençable.

À la fin du projet, tu auras produit :

- un site de cinq pages cohérentes, entièrement sémantique ;
- un formulaire de contact accessible, validé côté navigateur ;
- des images optimisées et adaptatives ;
- une configuration SEO complète (balises, Open Graph, JSON-LD, plan du site) ;
- un score Lighthouse supérieur à 95 en accessibilité, bonnes pratiques et SEO ;
- un dépôt documenté et une mise en ligne gratuite.

Prérequis : les chapitres 1 à 6 de ce cours. Prévois six heures, que tu peux répartir sur plusieurs sessions. CSS reste volontairement minimal ici : un fichier de style de base t'est fourni, car l'objectif est le HTML.

## Le sujet : un site vitrine pour une entreprise locale

Tu réalises le site de **« Atelier Kossou »**, une menuiserie fictive d'Abidjan qui fabrique des meubles sur mesure. Le client veut être trouvé par les personnes qui cherchent « menuisier Abidjan » ou « meuble sur mesure Cocody », présenter ses réalisations, et recevoir des demandes de devis, y compris depuis un téléphone.

Tu peux transposer le sujet à une activité qui t'est proche (salon de coiffure, restaurant, école de codage) : la structure reste la même. Garde un contenu réaliste, évite le « lorem ipsum » et rédige de vrais textes courts.

### Cahier des charges

| Page | Fichier | Contenu attendu |
| --- | --- | --- |
| Accueil | `index.html` | Présentation, trois points forts, aperçu de réalisations, appel à l'action |
| Réalisations | `realisations.html` | Galerie d'au moins six projets avec images et descriptions |
| Services | `services.html` | Liste des prestations, tableau de tarifs indicatifs en FCFA, FAQ dépliable |
| À propos | `a-propos.html` | Histoire de l'atelier, équipe, valeurs, vidéo ou image forte |
| Contact | `contact.html` | Formulaire de devis, coordonnées, horaires, plan intégré |

Exigences transversales :

1. **Structure commune** : même en-tête, même navigation, même pied de page sur chaque page, avec `aria-current="page"` sur le lien actif et un lien d'évitement.
2. **Sémantique** : un `main`, un `h1` par page, des titres sans saut, aucune `div` là où un élément plus précis existe.
3. **Médias** : au moins douze images, toutes avec `alt` adapté, `width` et `height`, formats WebP avec repli JPEG pour les photos principales, `loading="lazy"` hors de l'écran initial.
4. **Formulaire** : champs nom, téléphone, e-mail, type de meuble (liste), budget (boutons radio), description (zone de texte), consentement ; validation native complète.
5. **Accessibilité** : navigation 100 % clavier, focus visible, contrastes AA, formulaire relié à ses aides et erreurs.
6. **SEO** : `title` et description uniques, canonique, Open Graph, JSON-LD `LocalBusiness`, `robots.txt`, `sitemap.xml`.
7. **Poids** : chaque page sous 1 Mo au premier chargement, chaque image de contenu sous 150 Ko.
8. **Mobile d'abord** : lisible et utilisable dès 360 pixels de large, sans défilement horizontal.

## Étape 1 : préparer le dossier et le dépôt (30 min)

Organise le projet de façon professionnelle :

```text
atelier-kossou/
├── index.html
├── realisations.html
├── services.html
├── a-propos.html
├── contact.html
├── merci.html
├── robots.txt
├── sitemap.xml
├── css/
│   └── style.css
├── js/
│   └── menu.js
├── img/
│   ├── hero-800.webp
│   └── …
└── README.md
```

1. Crée le dossier, initialise un dépôt Git et ajoute un premier commit « Structure du projet ».
2. Prépare tes images : une douzaine de photos libres de droits (Unsplash, Pexels) ou les tiennes. Redimensionne-les en 400, 800 et 1200 pixels de large, convertis-les en WebP avec Squoosh.
3. Choisis une identité simple : un nom, deux couleurs vérifiées pour le contraste, une police système.
4. Écris un `style.css` minimal : variables de couleurs, `box-sizing: border-box`, taille de texte lisible (`font-size: 1rem` avec `line-height: 1.6`), classe `.sr-only`, style `:focus-visible` et `.skip-link`.

> **Astuce** : fais des commits fréquents et descriptifs (« Ajoute le formulaire de contact »). Un historique propre est un excellent argument devant un recruteur.

## Étape 2 : écrire le gabarit commun (45 min)

Commence par un seul fichier modèle, qui servira de base aux cinq pages :

```html
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Titre de la page | Atelier Kossou</title>
  <meta name="description" content="Description unique de la page.">
  <link rel="canonical" href="https://atelier-kossou.example/index.html">
  <link rel="stylesheet" href="css/style.css">
  <script src="js/menu.js" defer></script>
</head>
<body>
  <a class="skip-link" href="#contenu">Aller au contenu principal</a>

  <header class="site-header">
    <a href="index.html" class="logo">
      <img src="img/logo.svg" alt="Atelier Kossou, retour à l'accueil" width="160" height="48">
    </a>
    <button type="button" class="menu-toggle" aria-expanded="false" aria-controls="menu">
      Menu
    </button>
    <nav aria-label="Navigation principale">
      <ul id="menu">
        <li><a href="index.html" aria-current="page">Accueil</a></li>
        <li><a href="realisations.html">Réalisations</a></li>
        <li><a href="services.html">Services</a></li>
        <li><a href="a-propos.html">À propos</a></li>
        <li><a href="contact.html">Contact</a></li>
      </ul>
    </nav>
  </header>

  <main id="contenu" tabindex="-1">
    <h1>Titre principal</h1>
  </main>

  <footer class="site-footer">
    <p>&copy; 2026 Atelier Kossou. Cocody, Abidjan.</p>
    <p><a href="tel:+2250100000000">01 00 00 00 00</a></p>
  </footer>
</body>
</html>
```

Copie ce modèle cinq fois, puis change pour chaque page : `title`, description, URL canonique, lien marqué `aria-current="page"`. Écris le script `menu.js` du chapitre « Accessibilité » pour le bouton de menu mobile. Commit.

## Étape 3 : construire l'accueil et les réalisations (60 min)

### Accueil

Structure suggérée dans `main` :

1. Une `section` d'introduction avec le `h1` (« Meubles sur mesure à Abidjan »), une phrase d'accroche, un lien d'appel à l'action « Demander un devis » et l'image principale en `picture` avec `fetchpriority="high"`.
2. Une `section` « Nos engagements » avec un `h2` et trois `article` (bois local, délai tenu, garantie) avec un titre `h3` chacun.
3. Une `section` « Réalisations récentes » avec trois `figure` (image `loading="lazy"` et `figcaption`) et un lien vers la galerie.
4. Une `section` finale d'appel à l'action.

### Réalisations

Une grille de six projets minimum. Chaque projet est un `article` contenant un `h2`, une `figure` avec `picture`, une description courte, un `dl` pour les détails (matériau, durée, ville) et une date avec `time`. Utilise `srcset` et `sizes` pour toutes les images.

Vérifie à la fin : le plan de titres est-il parfait ? Chaque `alt` décrit-il vraiment la photo ?

## Étape 4 : services, tarifs et FAQ (45 min)

Dans `services.html`, présente les prestations (cuisines, dressings, bureaux, restauration de meubles) avec une liste, puis un **tableau de tarifs indicatifs** :

```html
<table>
  <caption>Tarifs indicatifs (hors livraison)</caption>
  <thead>
    <tr>
      <th scope="col">Prestation</th>
      <th scope="col">À partir de</th>
      <th scope="col">Délai moyen</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Dressing sur mesure</th>
      <td>350&nbsp;000 FCFA</td>
      <td>3 semaines</td>
    </tr>
    <tr>
      <th scope="row">Cuisine équipée</th>
      <td>900&nbsp;000 FCFA</td>
      <td>6 semaines</td>
    </tr>
  </tbody>
</table>
```

Ajoute ensuite une FAQ de cinq questions avec `details` et `summary`. Prévois pour elle un bloc JSON-LD `FAQPage` dans l'étape SEO. Pense à entourer le tableau d'un conteneur à défilement horizontal sur mobile si nécessaire.

## Étape 5 : le formulaire de devis (60 min)

C'est la pièce maîtresse : c'est elle qui transforme un visiteur en client. Respecte tout le chapitre « Formulaires » :

```html
<form action="https://formspree.io/f/VOTRE_ID" method="post" class="form-devis">
  <h2>Demande de devis gratuit</h2>

  <div class="champ">
    <label for="nom">Nom complet</label>
    <input type="text" id="nom" name="nom" required autocomplete="name">
  </div>

  <div class="champ">
    <label for="tel">Téléphone (WhatsApp de préférence)</label>
    <input type="tel" id="tel" name="telephone" required autocomplete="tel"
           pattern="[0-9 +]{10,16}" aria-describedby="tel-aide">
    <p id="tel-aide" class="aide">Exemple : 01 00 00 00 00</p>
  </div>

  <div class="champ">
    <label for="email">E-mail (facultatif)</label>
    <input type="email" id="email" name="email" autocomplete="email">
  </div>

  <div class="champ">
    <label for="type">Type de meuble</label>
    <select id="type" name="type" required>
      <option value="">-- Choisis --</option>
      <option value="cuisine">Cuisine</option>
      <option value="dressing">Dressing</option>
      <option value="bureau">Bureau</option>
      <option value="autre">Autre</option>
    </select>
  </div>

  <fieldset>
    <legend>Ton budget</legend>
    <!-- trois boutons radio avec le même name -->
  </fieldset>

  <div class="champ">
    <label for="description">Décris ton projet</label>
    <textarea id="description" name="description" rows="5" maxlength="800"></textarea>
  </div>

  <div class="champ">
    <input type="checkbox" id="consent" name="consent" required>
    <label for="consent">J'accepte d'être recontacté au sujet de ma demande</label>
  </div>

  <button type="submit">Envoyer ma demande</button>
</form>
```

Complète les boutons radio, ajoute `merci.html` comme page de confirmation (avec `noindex`), et un petit script qui affiche un message `role="alert"` quand la validation échoue. Ajoute aussi un champ piège anti-spam (un champ masqué que seuls les robots remplissent) et rappelle-toi : la validation réelle se fait côté serveur ou chez le service qui reçoit le formulaire.

Sous le formulaire, affiche les coordonnées dans une balise `address`, les horaires dans un `dl` ou un tableau, et une carte via une `iframe` avec `title` et `loading="lazy"`. Ajoute un lien WhatsApp : `https://wa.me/225XXXXXXXXXX?text=Bonjour`.

## Étape 6 : à propos et finitions de contenu (30 min)

Rédige l'histoire de l'atelier, présente l'équipe avec des `figure` portraits, des valeurs dans une liste, une citation avec `blockquote`. Intègre éventuellement une courte vidéo avec `controls`, `poster`, `preload="metadata"` et sous-titres. Relis l'ensemble pour vérifier l'orthographe, l'accentuation et le ton.

## Étape 7 : le référencement (45 min)

Pour chaque page :

1. `title` unique (moins de 60 caractères) et description unique (120 à 155 caractères).
2. URL canonique absolue.
3. Balises Open Graph avec une image 1200 par 630 et `og:locale="fr_FR"`.

Pour le site :

4. Un JSON-LD `LocalBusiness` sur l'accueil et la page contact (nom, adresse, téléphone, horaires, zone desservie, image).
5. Un JSON-LD `FAQPage` sur la page Services.
6. `robots.txt` (autoriser tout, indiquer le sitemap) et `sitemap.xml` avec les cinq pages.
7. Un fil d'Ariane sur les pages internes.

```html
<script type="application/ld+json">
{
  "@context": "https://schema.org",
  "@type": "LocalBusiness",
  "name": "Atelier Kossou",
  "image": "https://atelier-kossou.example/img/og-accueil.jpg",
  "telephone": "+225 01 00 00 00 00",
  "address": {
    "@type": "PostalAddress",
    "streetAddress": "Rue des Artisans",
    "addressLocality": "Abidjan",
    "addressCountry": "CI"
  },
  "openingHours": "Mo-Sa 08:00-18:00"
}
</script>
```

## Étape 8 : audit, correction et mise en ligne (45 min)

1. **Validation** : passe chaque page au validateur du W3C. Zéro erreur est l'objectif.
2. **Accessibilité** : test clavier complet, test d'un lecteur d'écran sur la page de contact, Lighthouse et axe sans erreur critique.
3. **Performance** : PageSpeed Insights en mobile ; chaque page sous 1 Mo, LCP sous 2,5 s.
4. **Liens** : vérifie qu'aucun lien n'est cassé (extension ou outil en ligne).
5. **Responsive** : teste à 360, 768 et 1280 pixels, puis sur un vrai téléphone.
6. **Mise en ligne** gratuite : déploie sur Netlify, Cloudflare Pages ou GitHub Pages, puis remplace tous les domaines `.example` par le domaine réel.
7. **README** : écris un fichier qui décrit le projet, la structure, les choix d'accessibilité et SEO, les scores obtenus et la commande pour ouvrir le site.

:::quiz
Tu viens de déployer le site et il n'apparaît dans aucun résultat de recherche alors que les pages sont en ligne. Quelle est la première chose à vérifier ?
- [ ] Que les images soient en JPEG
- [x] Qu'aucune balise meta robots noindex n'ait été laissée en production
- [ ] Que le site utilise un framework JavaScript
- [ ] Que la page contienne des mots-clés en gras
> Une balise noindex oubliée depuis la préproduction empêche l'indexation. Vérifie aussi robots.txt et déclare le sitemap dans la Search Console.
:::

:::quiz
Pour la page de remerciement après l'envoi du formulaire, quelle balise est la plus adaptée dans le head ?
- [ ] meta robots avec la valeur index, follow
- [x] meta robots avec la valeur noindex
- [ ] link rel canonical vers la page contact
- [ ] meta description très longue
> Une page de confirmation n'a aucun intérêt dans les résultats de recherche : on la marque noindex.
:::

## Livrables

Rends (ou garde dans ton portfolio) :

- le dépôt Git avec un historique lisible ;
- l'URL du site en ligne ;
- le fichier `README.md` avec captures d'écran et scores Lighthouse ;
- un court texte de dix lignes expliquant trois décisions de sémantique ou d'accessibilité que tu as prises et pourquoi.

## Liste de validation (critères d'acceptation)

Coche chaque point avant de déclarer le projet terminé.

**Structure et sémantique**

- [ ] Cinq pages plus une page de remerciement, avec en-tête, navigation et pied de page communs
- [ ] Un seul `main` et un seul `h1` par page, plan de titres sans saut
- [ ] Aucune `div` remplaçable par un élément sémantique
- [ ] Validation W3C sans erreur sur toutes les pages

**Contenu et médias**

- [ ] Douze images minimum, toutes avec `alt` pertinent (ou vide si décoratives)
- [ ] `width` et `height` partout, `loading="lazy"` hors écran initial
- [ ] Images principales en `picture` avec WebP et repli JPEG
- [ ] Chaque page sous 1 Mo, chaque image sous 150 Ko

**Formulaire**

- [ ] Chaque champ possède un `label`, un `name` et le bon `type`
- [ ] Validation native (`required`, `pattern`, `minlength`)
- [ ] `fieldset` et `legend` pour les boutons radio
- [ ] Messages d'erreur et aides reliés par `aria-describedby`

**Accessibilité**

- [ ] Parcours complet au clavier, lien d'évitement, focus toujours visible
- [ ] Contrastes AA vérifiés, aucune information transmise par la couleur seule
- [ ] Lighthouse accessibilité supérieur à 95
- [ ] Test avec un lecteur d'écran réalisé sur le formulaire

**SEO**

- [ ] `title` et description uniques sur chaque page
- [ ] Canonique, Open Graph, JSON-LD valides
- [ ] `robots.txt` et `sitemap.xml` en place, `noindex` uniquement sur la page de remerciement
- [ ] Lighthouse SEO supérieur à 95

**Livraison**

- [ ] Site déployé et fonctionnel avec l'URL réelle
- [ ] Dépôt Git propre, README complet

## Erreurs fréquentes

- **Copier-coller le gabarit sans changer `title`, description et canonique.** Toutes les pages deviennent identiques pour Google.
- **Oublier de mettre à jour `aria-current`** sur chaque page.
- **Images trop lourdes** sorties directement d'un appareil photo.
- **Un formulaire testé seulement à la souris.** Le clavier révèle des champs mal reliés.
- **Laisser des liens `href="#"`** ou des chemins relatifs cassés dans les sous-dossiers.
- **Publier avec les domaines `.example`** dans canonique, Open Graph et sitemap.
- **Négliger le contenu rédigé.** Un site sans vrais textes ne sera ni lu ni référencé.

## Bonnes pratiques

- Travaille page par page et valide au fur et à mesure plutôt qu'à la fin.
- Écris d'abord le contenu et la structure, puis seulement ensuite le style.
- Teste à chaque étape : clavier, mobile, validateur.
- Fais des commits petits et explicites.
- Documente tes choix dans le README : c'est ce qui distingue un professionnel.
- Demande un retour à une autre personne : ouvre ton site sur son téléphone et observe sans l'aider.
- Garde ce projet comme base réutilisable : c'est le point de départ de tes futures commandes clients.

## À retenir

- Un site vitrine professionnel combine **sémantique**, **médias optimisés**, **formulaire accessible** et **SEO** dans un même ensemble cohérent.
- Un gabarit commun garantit la cohérence, mais chaque page doit avoir ses métadonnées uniques.
- Le formulaire de contact est l'élément qui convertit : soigne ses labels, sa validation et ses messages.
- L'accessibilité et la performance se vérifient avec des outils, mais aussi à la main (clavier, lecteur d'écran, vrai téléphone).
- Une liste d'acceptation claire permet de savoir objectivement quand le travail est terminé.
- Tu possèdes maintenant les bases HTML pour passer sereinement au CSS, puis à React avec DevRoad.
