<?php

return [
    'lessons' => [
        'Comprendre HTML' => [
            'description' => 'Découvre ce qu’est un document HTML, comment il est composé d’éléments et d’attributs, et comment le navigateur le lit pour afficher une page.',
            'objective' => 'Écrire de zéro une page HTML valide avec doctype, langue, métadonnées, titre, un contenu minimal, et l’ouvrir dans un navigateur sans erreur.',
            'content' => <<<'MD'
## Pourquoi cette notion

## Les concepts clés

HTML signifie HyperText Markup Language. Ce n’est pas un langage de programmation : il n’y a ni calcul ni condition. C’est un langage de balisage, c’est-à-dire qu’il décrit la nature du contenu : ceci est un titre, ceci est un paragraphe, ceci est un lien.

### Éléments, balises et attributs

Un élément est composé d’une balise ouvrante, d’un contenu et d’une balise fermante, par exemple un paragraphe composé d’une balise « p » ouvrante, du texte « Bonjour » et d’une balise « p » fermante. Certains éléments sont dits vides, comme « img » ou « br », car ils n’ont pas de contenu ni de balise fermante.

### La structure minimale d’un document

Un document valide commence par la déclaration « doctype html » qui demande au navigateur d’utiliser le mode standard. Vient ensuite l’élément racine « html » avec l’attribut « lang » qui indique la langue du contenu. À l’intérieur, tu trouves deux parties : « head » qui contient les informations invisibles (titre de l’onglet, encodage, métadonnées) et « body » qui contient tout ce qui s’affiche.

### Le rôle de l’encodage et du viewport

La balise « meta charset="utf-8" » garantit que les accents français, comme é, è ou ç, s’affichent correctement. La balise « meta name="viewport" » prépare l’affichage sur téléphone, ce qui est capital quand la majorité de ton audience navigue sur mobile.

### L’arbre du document

Le navigateur transforme ton HTML en un arbre d’objets appelé DOM. Chaque élément imbriqué devient un enfant de l’élément qui le contient. Comprendre cette imbrication t’aidera plus tard en CSS et en JavaScript.

## Exemple pas à pas

Le code_example construit la page d’accueil d’une petite boutique de pagnes. À l’étape un, on déclare le doctype et l’élément « html » avec la langue française. À l’étape deux, le « head » reçoit l’encodage, le viewport et un titre d’onglet clair. À l’étape trois, le « body » contient un titre principal unique, puis un paragraphe de présentation. À l’étape quatre, on ajoute une liste d’articles et un lien vers une page de contact avec l’attribut « href ». Enfin, un commentaire HTML rappelle que les commentaires ne s’affichent pas. Ouvre le fichier dans ton navigateur, puis utilise les outils de développement pour observer l’arbre DOM.

## Erreurs fréquentes

- Oublier le doctype : le navigateur passe en mode de compatibilité et le rendu devient imprévisible. Ajoute toujours la déclaration doctype en première ligne.
- Ne pas fermer une balise : le contenu suivant se retrouve imbriqué par erreur. Vérifie chaque balise ouvrante et utilise l’indentation pour repérer les oublis.
- Mélanger l’ordre des fermetures : les éléments doivent se fermer dans l’ordre inverse de leur ouverture. Indente pour le vérifier.
- Utiliser plusieurs « h1 » ou choisir un titre pour sa taille : le titre exprime la hiérarchie. Garde un seul « h1 » et règle la taille en CSS.
- Oublier l’attribut « lang » : les lecteurs d’écran prononcent mal le texte. Écris « lang= » sur « html ».

## Bonnes pratiques

- Indente systématiquement avec deux espaces pour visualiser l’imbrication.
- Écris les noms de balises et d’attributs en minuscules et mets toujours les valeurs entre guillemets doubles.
- Donne un titre d’onglet unique et descriptif à chaque page.
- Valide ton document avec le validateur du W3C avant de livrer.

## Auto-évaluation

- Quelle est la différence entre une balise et un élément ?
- À quoi sert l’élément « head » et pourquoi son contenu ne s’affiche-t-il pas dans la page ?
- Pourquoi l’attribut « lang » est-il important ?
- Cite deux éléments vides et explique pourquoi ils n’ont pas de balise fermante.

## À retenir

- HTML décrit la structure et le sens du contenu, pas son apparence.
- Un document valide possède un doctype, un élément « html » avec « lang », un « head » et un « body ».
- Les attributs enrichissent les balises ouvrantes avec des paires nom et valeur.
- Le navigateur construit un arbre DOM à partir de ton code.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<!-- Étape 1 : doctype + langue du document -->
<html lang="fr">
  <head>
    <!-- Étape 2 : métadonnées invisibles -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Pagnes d'Abidjan | Boutique en ligne</title>
  </head>
  <body>
    <!-- Étape 3 : un seul titre principal -->
    <h1>Pagnes d'Abidjan</h1>

    <p>
      Nous vendons des pagnes traditionnels et modernes,
      livrés partout en Côte d'Ivoire.
    </p>

    <!-- Étape 4 : liste d'articles -->
    <h2>Nos articles</h2>
    <ul>
      <li>Pagne Wax - 15 000 FCFA</li>
      <li>Pagne Bogolan - 12 000 FCFA</li>
      <li>Kita - 18 000 FCFA</li>
    </ul>

    <!-- Lien avec attribut href -->
    <p>
      Une question ? <a href="contact.html">Contactez-nous</a>.
    </p>

    <!-- Ce commentaire ne s'affiche pas dans la page -->
  </body>
</html>
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Ta première page de présentation',
            'exercise_description' => <<<'TXT'
Crée un fichier « index.html » qui présente une activité de ton choix (un salon de coiffure, un maquis, un cours de soutien scolaire). La page doit s’ouvrir dans le navigateur sans aucune erreur.

Critères de réussite :
- Le fichier commence par le doctype et contient « html lang="fr" », un « head » et un « body ».
- Le « head » contient l’encodage UTF-8, le viewport et un « title » descriptif.
- Le « body » contient exactement un « h1 », au moins un paragraphe, et une liste « ul » d’au moins 3 éléments.
- La page contient au moins un lien avec un attribut « href » et un commentaire HTML.
- Toutes les balises sont fermées dans le bon ordre et le fichier est validé sans erreur par le validateur du W3C.
TXT,
            'exercise_hint' => 'Pars de la structure du code_example, remplace le contenu par ton activité, puis passe le fichier dans le validateur W3C et corrige les erreurs une par une en commençant par la première.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Salon Belle Allure | Coiffure à Cocody</title>
  </head>
  <body>
    <h1>Salon Belle Allure</h1>

    <p>
      Salon de coiffure situé à Cocody, ouvert du lundi au samedi.
      Tresses, coupes et soins du cuir chevelu.
    </p>

    <h2>Nos prestations</h2>
    <ul>
      <li>Tresses africaines</li>
      <li>Coupe et brushing</li>
      <li>Soin hydratant</li>
    </ul>

    <p>Pour réserver, <a href="contact.html">écris-nous</a>.</p>

    <!-- Page à compléter : ajouter les horaires -->
  </body>
</html>
CODE,
        ],

        'Sémantique' => [
            'description' => 'Apprends à choisir les éléments qui portent du sens, comme header, nav, main, section, article et footer, plutôt que d’empiler des div.',
            'objective' => 'Structurer une page de blog en utilisant au moins six éléments sémantiques différents, correctement imbriqués, avec une hiérarchie de titres cohérente.',
            'content' => <<<'MD'
## Pourquoi cette notion

## Les concepts clés

Un élément sémantique décrit le rôle de son contenu, contrairement à « div » et « span » qui sont neutres. Tu ne les choisis pas pour leur apparence, mais pour leur signification.

### Les éléments de structure de page

L’élément « header » regroupe l’en-tête d’une page ou d’une section, souvent avec le logo et le titre. L’élément « nav » contient un bloc de liens de navigation principaux. L’élément « main » contient le contenu principal et ne doit apparaître qu’une seule fois par page. L’élément « footer » termine une page ou une section avec les mentions légales ou les contacts. L’élément « aside » regroupe un contenu complémentaire, comme une publicité ou une liste d’articles liés.

### Section et article

L’élément « section » regroupe un thème de contenu, et il porte généralement un titre. L’élément « article » représente un contenu autonome qui garde du sens s’il est extrait de la page, comme un billet de blog, une fiche produit ou un commentaire. Pose-toi la question : puis-je publier ce bloc ailleurs sans le reste de la page ? Si oui, c’est un « article ».

### La hiérarchie des titres

Les éléments « h1 » à « h6 » forment un plan du document. Un « h1 » décrit la page, les « h2 » ses grandes parties, les « h3 » les sous-parties. On ne saute pas de niveau juste pour obtenir une taille de texte. Les lecteurs d’écran permettent de naviguer de titre en titre, donc un plan cohérent est un vrai gain d’usage.

### Quand garder une div

Les éléments « div » et « span » restent utiles quand aucun élément sémantique ne convient, par exemple pour regrouper des éléments à des fins de style. Le but n’est pas de les bannir, mais de ne les utiliser qu’en dernier recours.

## Exemple pas à pas

Le code_example présente un blog d’actualités sur le mobile money. À l’étape un, le « header » contient le titre du site et un « nav » avec trois liens. À l’étape deux, l’élément « main » englobe le contenu principal, avec un seul « h1 ». À l’étape trois, deux « article » se suivent, chacun avec son propre « h2 », sa date dans un élément « time » et un paragraphe. À l’étape quatre, un « aside » propose des liens utiles. Enfin, le « footer » affiche le contact. Remarque que l’on pourrait extraire chaque article sans perdre son sens, et que la hiérarchie des titres reste logique du début à la fin.

## Erreurs fréquentes

- Tout construire avec des « div » : la page perd son sens. Remplace chaque « div » par l’élément sémantique adapté quand il existe.
- Mettre plusieurs « main » dans une même page : seul un contenu principal doit exister. Garde un seul « main » par page.
- Choisir un « h3 » parce que sa taille plaît : la hiérarchie devient incohérente. Utilise le bon niveau et ajuste l’apparence en CSS.
- Confondre « article » et « section » : un « article » est autonome, une « section » regroupe un thème. Applique le test d’extraction.

## Bonnes pratiques

- Dessine d’abord le plan du document avec les titres avant d’écrire le HTML.
- Utilise « header », « nav », « main » et « footer » dans toutes tes pages.
- Garde un seul « h1 » par page et ne saute aucun niveau de titre.
- Choisis les éléments pour leur sens et laisse le CSS gérer l’apparence.

## Auto-évaluation

- Quelle est la différence entre « article » et « section » ?
- Pourquoi un seul « main » est-il autorisé par page ?
- Que se passe-t-il pour un utilisateur de lecteur d’écran si tous les titres sont des « div » stylés ?
- Dans quel cas est-il légitime d’utiliser une « div » ?

## À retenir

- Les éléments sémantiques décrivent le rôle du contenu, pas son apparence.
- Les éléments « header », « nav », « main », « aside » et « footer » structurent la page.
- Un « article » est autonome, une « section » regroupe un thème.
- Les titres forment un plan : un seul « h1 », pas de niveau sauté.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Actu Mobile Money | Blog</title>
</head>
<body>
  <!-- Étape 1 : en-tête et navigation principale -->
  <header>
    <p>Actu Mobile Money</p>
    <nav aria-label="Navigation principale">
      <ul>
        <li><a href="/">Accueil</a></li>
        <li><a href="/articles">Articles</a></li>
        <li><a href="/contact">Contact</a></li>
      </ul>
    </nav>
  </header>

  <!-- Étape 2 : contenu principal, unique par page -->
  <main>
    <h1>Les dernières actualités</h1>

    <!-- Étape 3 : chaque article est autonome -->
    <article>
      <h2>Payer son électricité par mobile money</h2>
      <p>Publié le <time datetime="2026-03-12">12 mars 2026</time></p>
      <p>Les paiements de factures se font désormais en quelques minutes.</p>
    </article>

    <article>
      <h2>Sécuriser son compte mobile money</h2>
      <p>Publié le <time datetime="2026-03-20">20 mars 2026</time></p>
      <p>Ne partage jamais ton code secret, même avec un faux agent.</p>
    </article>

    <!-- Étape 4 : contenu complémentaire -->
    <aside>
      <h2>Liens utiles</h2>
      <ul>
        <li><a href="/guide">Guide du débutant</a></li>
      </ul>
    </aside>
  </main>

  <footer>
    <p>Contact : contact@exemple.ci</p>
  </footer>
</body>
</html>
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Structurer une page de blog',
            'exercise_description' => <<<'TXT'
Construis la page d’accueil d’un blog sur un sujet de ton choix (cuisine ivoirienne, football, études). Tu ne dois utiliser « div » que si aucun élément sémantique ne convient.

Critères de réussite :
- La page contient « header », « nav », « main » et « footer », chacun une seule fois sauf « nav ».
- Le « main » contient un seul « h1 » et au moins deux « article », chacun avec un « h2 ».
- La page contient un « aside » avec un titre et au moins un lien.
- La hiérarchie des titres ne saute aucun niveau.
- Le document est valide au validateur du W3C et ne contient aucune « div » inutile.
TXT,
            'exercise_hint' => 'Écris d’abord le plan sur papier : h1, puis h2 pour chaque article. Ajoute ensuite les éléments autour, et pour chaque div que tu es tenté d’écrire, demande-toi quel élément existe déjà.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Saveurs d'Abidjan | Blog de cuisine</title>
</head>
<body>
  <header>
    <p>Saveurs d'Abidjan</p>
    <nav aria-label="Navigation principale">
      <ul>
        <li><a href="/">Accueil</a></li>
        <li><a href="/recettes">Recettes</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h1>Recettes de chez nous</h1>

    <article>
      <h2>Attiéké poisson braisé</h2>
      <p>Un classique des maquis, facile à reproduire à la maison.</p>
    </article>

    <article>
      <h2>Sauce graine</h2>
      <p>Une sauce riche à préparer pour les repas de famille.</p>
    </article>

    <aside>
      <h2>À lire aussi</h2>
      <ul>
        <li><a href="/astuces">Astuces de cuisson</a></li>
      </ul>
    </aside>
  </main>

  <footer>
    <p>Contact : blog@exemple.ci</p>
  </footer>
</body>
</html>
CODE,
        ],

        'Texte, liens et médias' => [
            'description' => 'Structure du texte avec titres, paragraphes et citations, crée des liens fiables et intègre des images et vidéos avec des alternatives accessibles.',
            'objective' => 'Produire un article complet contenant titres, listes, liens internes et externes, une image avec alt pertinent et une vidéo avec contrôles et sous-titres.',
            'content' => <<<'MD'
## Pourquoi cette notion

Le contenu est ce que ton visiteur est venu chercher. Une fiche produit, un article ou une page de présentation reposent sur du texte, des liens et des images. Un client qui commande ton site vitrine te demandera presque toujours des galeries photos, une vidéo de présentation et un menu qui fonctionne.

## Les concepts clés

### Le texte et sa structure

Les paragraphes s’écrivent avec « p », les titres avec « h1 » à « h6 ». Pour mettre en valeur, « strong » marque une importance forte et « em » une emphase. Ces éléments portent un sens, contrairement à « b » et « i » qui sont purement visuels. L’élément « blockquote » sert aux citations longues et « code » aux extraits de code.

### Les liens

L’élément « a » avec l’attribut « href » crée un lien. Un lien interne pointe vers une autre page du même site, comme « /contact ». Un lien externe pointe vers un autre domaine et commence par « https:// ». Un lien vers une ancre utilise « # » suivi de l’identifiant d’un élément. Pour un lien externe ouvert dans un nouvel onglet, ajoute « target="_blank" » avec « rel="noopener noreferrer" » pour éviter des failles de sécurité.

### Les images

L’élément « img » prend un attribut « src » pour le fichier et un attribut « alt » pour le texte alternatif. Le texte « alt » décrit l’image pour ceux qui ne la voient pas, et il s’affiche si l’image ne charge pas. Une image purement décorative reçoit « alt » vide. Ajoute « width » et « height » pour que le navigateur réserve l’espace et évite que la page saute, et « loading="lazy" » pour différer le chargement des images situées plus bas.

### L’audio et la vidéo

L’élément « video » intègre une vidéo, avec l’attribut « controls » pour afficher les commandes. Évite « autoplay » : il consomme des données et gêne l’utilisateur. L’élément « track » ajoute des sous-titres, indispensables pour l’accessibilité et pour les environnements bruyants. L’attribut « poster » affiche une image avant la lecture.

## Exemple pas à pas

Le code_example compose un article sur une plantation de cacao. À l’étape un, le « h1 » et un paragraphe introduisent le sujet, avec un mot en « strong ». À l’étape deux, une liste « ul » détaille les étapes de la récolte. À l’étape trois, un lien interne et un lien externe sécurisé sont ajoutés. À l’étape quatre, une « figure » regroupe l’image avec son « alt » descriptif, ses dimensions et le chargement différé. À l’étape cinq, la vidéo reçoit « controls », « poster » et un « track » de sous-titres en français.

## Erreurs fréquentes

- Oublier l’attribut « alt » : l’image devient muette pour un lecteur d’écran. Décris toujours son contenu utile, ou mets « alt » vide si elle est décorative.
- Écrire « alt="image" » : cette valeur n’apporte rien. Décris ce que montre réellement l’image.
- Utiliser « cliquez ici » comme texte de lien : il est incompréhensible hors contexte. Rédige un texte explicite.
- Activer « autoplay » avec le son : l’utilisateur subit la lecture. Laisse-lui le contrôle avec « controls ».

## Bonnes pratiques

- Utilise « strong » et « em » pour le sens, et le CSS pour l’apparence.
- Rédige des textes de liens explicites qui se comprennent hors contexte.
- Fournis toujours « alt », « width » et « height » sur les images, et « loading="lazy" » sous la ligne de flottaison.
- Compresse tes images avant de les publier et ajoute des sous-titres à chaque vidéo.

## Auto-évaluation

- Quelle est la différence entre « strong » et « b » ?
- Quand faut-il laisser l’attribut « alt » vide ?
- Pourquoi ajouter « width » et « height » sur une image ?
- Que fait « rel="noopener noreferrer" » et quand l’utiliser ?

## À retenir

- Le texte se structure avec des éléments qui portent du sens.
- Un lien doit avoir un texte explicite et, s’il s’ouvre ailleurs, un « rel » sécurisé.
- Toute image informative nécessite un « alt » pertinent.
- Les dimensions et « loading="lazy" » améliorent la performance perçue.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Récolte du cacao à Soubré</title>
</head>
<body>
  <main>
    <!-- Étape 1 : titre et texte avec mise en valeur -->
    <h1>La récolte du cacao</h1>
    <p>
      Le cacao est une <strong>culture majeure</strong> en Côte d'Ivoire.
      Voici les grandes étapes de la récolte.
    </p>

    <!-- Étape 2 : liste ordonnée -->
    <ol>
      <li>Cueillir les cabosses mûres</li>
      <li>Ouvrir les cabosses et extraire les fèves</li>
      <li>Faire fermenter puis sécher les fèves</li>
    </ol>

    <!-- Étape 3 : lien interne et lien externe sécurisé -->
    <p>
      <a href="/contact">Contacter la coopérative</a> ou
      <a href="https://www.w3.org" target="_blank"
         rel="noopener noreferrer">consulter le site du W3C</a>.
    </p>

    <!-- Étape 4 : image avec légende -->
    <figure>
      <img src="cabosses.jpg"
           alt="Cabosses de cacao jaunes et rouges sur un arbre"
           width="640" height="400" loading="lazy">
      <figcaption>Cabosses prêtes à être récoltées</figcaption>
    </figure>

    <!-- Étape 5 : vidéo avec commandes et sous-titres -->
    <video controls poster="apercu.jpg" width="640">
      <source src="recolte.mp4" type="video/mp4">
      <track kind="subtitles" src="recolte-fr.vtt"
             srclang="fr" label="Français" default>
      Ton navigateur ne supporte pas la vidéo.
    </video>
  </main>
</body>
</html>
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Un article complet et accessible',
            'exercise_description' => <<<'TXT'
Rédige un article de présentation sur une activité de ton choix (marché local, artisan, plat traditionnel) en intégrant du texte, des liens et des médias. Utilise des fichiers fictifs pour l’image et la vidéo.

Critères de réussite :
- L’article contient un « h1 », au moins deux paragraphes dont un avec « strong » ou « em », et une liste « ul » ou « ol ».
- La page contient un lien interne et un lien externe avec « target="_blank" » et « rel="noopener noreferrer" », chacun avec un texte explicite.
- Une « figure » contient une « img » avec « alt » descriptif, « width », « height » et « loading="lazy" », ainsi qu’une « figcaption ».
- Une « video » contient « controls », « poster » et un élément « track » de sous-titres.
- Aucun lien ne s’appelle « cliquez ici » et le document est valide au W3C.
TXT,
            'exercise_hint' => 'Écris le texte d’abord, puis ajoute les médias. Pour l’alt, imagine que tu décris la photo au téléphone à quelqu’un qui ne la voit pas.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Le grand marché de Treichville</title>
</head>
<body>
  <main>
    <h1>Le grand marché de Treichville</h1>

    <p>
      Ce marché réunit des centaines de commerçants chaque jour.
      On y trouve des <strong>produits frais</strong> et de l'artisanat.
    </p>
    <p>Venez tôt le matin pour profiter des meilleurs prix.</p>

    <ul>
      <li>Fruits et légumes</li>
      <li>Poissons et viandes</li>
      <li>Tissus et artisanat</li>
    </ul>

    <p>
      <a href="/plan-du-marche">Voir le plan du marché</a> ou
      <a href="https://fr.wikipedia.org" target="_blank"
         rel="noopener noreferrer">en savoir plus sur Wikipédia</a>.
    </p>

    <figure>
      <img src="marche.jpg"
           alt="Étals de fruits et légumes colorés sous des parasols"
           width="640" height="400" loading="lazy">
      <figcaption>Les étals du marché en matinée</figcaption>
    </figure>

    <video controls poster="marche-apercu.jpg" width="640">
      <source src="marche.mp4" type="video/mp4">
      <track kind="subtitles" src="marche-fr.vtt"
             srclang="fr" label="Français" default>
      Ton navigateur ne supporte pas la vidéo.
    </video>
  </main>
</body>
</html>
CODE,
        ],

        'Formulaires' => [
            'description' => 'Construis des formulaires avec label, name, type, required et les contraintes de validation natives du navigateur.',
            'objective' => 'Réaliser un formulaire d’inscription avec au moins six champs de types différents, chacun relié à un label et validé nativement, sans JavaScript.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un formulaire est le point de contact entre ton utilisateur et ton service. Inscription, commande, réservation, demande de devis : toutes ces actions passent par des champs à remplir. Un formulaire mal conçu fait perdre des clients, car l’utilisateur abandonne au premier obstacle.

## Les concepts clés

### L’élément form

L’élément « form » regroupe les champs. L’attribut « action » indique l’adresse qui reçoit les données et « method » indique la méthode HTTP : « get » place les données dans l’adresse, « post » les place dans le corps de la requête. On utilise « post » pour les données sensibles ou qui modifient quelque chose.

### Les champs et leurs types

L’élément « input » change de comportement selon son attribut « type » : « text », « email », « tel », « password », « number », « date », « checkbox », « radio » et d’autres. Un type adapté déclenche le bon clavier sur mobile, par exemple le clavier numérique pour « tel ».

### Label et name

Chaque champ doit avoir un « label » relié par « for » à l’« id » du champ. Cette liaison agrandit la zone cliquable et permet aux lecteurs d’écran d’annoncer le champ. L’attribut « name » est différent : c’est la clé sous laquelle la valeur est envoyée au serveur. Sans « name », le champ n’est tout simplement pas transmis.

### La validation native

Le navigateur peut valider avant l’envoi grâce à des attributs : « required » pour un champ obligatoire, « minlength » et « maxlength » pour la longueur, « min » et « max » pour les nombres et les dates, « pattern » pour une expression régulière, et le type « email » qui vérifie le format. L’attribut « placeholder » donne un exemple de saisie, mais il ne remplace jamais le « label ».

## Exemple pas à pas

Le code_example propose un formulaire de réservation pour un restaurant. À l’étape un, la balise « form » reçoit « action » et « method="post" ». À l’étape deux, le champ du nom utilise « label », « id », « name » et « required ». À l’étape trois, le champ téléphone utilise « type="tel" » avec un « pattern » adapté. À l’étape quatre, un champ « date » et un champ « number » portent des bornes « min » et « max ». À l’étape cinq, un « select » propose le choix de l’heure et un « fieldset » regroupe les boutons radio de l’espace souhaité. Enfin, un bouton « submit » envoie le tout.

## Erreurs fréquentes

- Remplacer le « label » par un « placeholder » : le texte disparaît à la saisie et n’est pas fiable pour l’accessibilité. Ajoute toujours un « label » visible.
- Oublier l’attribut « name » : la valeur n’est pas envoyée au serveur. Donne un « name » à chaque champ à transmettre.
- Utiliser « type="text" » partout : l’utilisateur n’a pas le bon clavier. Choisis « email », « tel » ou « number » selon la donnée.
- Lier mal « for » et « id » : le clic sur le label ne fait rien. Vérifie que les deux valeurs sont identiques.

## Bonnes pratiques

- Un « label » visible pour chaque champ, relié par « for » et « id ».
- Choisis le « type » le plus précis pour profiter des claviers adaptés.
- Ajoute « autocomplete » pour faciliter le remplissage des données courantes.
- Regroupe les champs liés avec « fieldset » et « legend ».

## Auto-évaluation

- Quelle est la différence entre « id » et « name » sur un champ ?
- Pourquoi relier un « label » à son champ ?
- Quels attributs permettent de rendre un champ obligatoire et de limiter sa longueur ?
- Quand utiliser « post » plutôt que « get » ?

## À retenir

- Un formulaire envoie les champs qui possèdent un attribut « name ».
- Chaque champ a un « label » relié par « for » et « id ».
- Le « type » choisit le clavier et la validation de base.
- Les attributs « required », « min », « max » et « pattern » valident côté navigateur.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Réserver une table</title>
</head>
<body>
  <main>
    <h1>Réserver une table</h1>

    <!-- Étape 1 : le formulaire envoie ses données en POST -->
    <form action="/reservations" method="post">

      <!-- Étape 2 : label relié au champ, champ obligatoire -->
      <p>
        <label for="nom">Nom complet</label>
        <input id="nom" name="nom" type="text"
               required minlength="3" autocomplete="name">
      </p>

      <!-- Étape 3 : téléphone avec motif (10 chiffres) -->
      <p>
        <label for="tel">Téléphone</label>
        <input id="tel" name="tel" type="tel"
               required pattern="[0-9]{10}"
               placeholder="0102030405">
      </p>

      <!-- Étape 4 : date et nombre avec bornes -->
      <p>
        <label for="date">Date</label>
        <input id="date" name="date" type="date" required min="2026-01-01">
      </p>
      <p>
        <label for="personnes">Nombre de personnes</label>
        <input id="personnes" name="personnes" type="number"
               min="1" max="12" value="2" required>
      </p>

      <!-- Étape 5 : liste déroulante et groupe de boutons radio -->
      <p>
        <label for="heure">Heure</label>
        <select id="heure" name="heure" required>
          <option value="">Choisir</option>
          <option value="19:00">19h00</option>
          <option value="20:00">20h00</option>
        </select>
      </p>
      <fieldset>
        <legend>Espace souhaité</legend>
        <label><input type="radio" name="espace" value="salle" checked> Salle</label>
        <label><input type="radio" name="espace" value="terrasse"> Terrasse</label>
      </fieldset>

      <button type="submit">Réserver ma table</button>
    </form>
  </main>
</body>
</html>
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Formulaire d’inscription à un cours',
            'exercise_description' => <<<'TXT'
Crée un formulaire d’inscription à une formation en ligne, avec validation native et sans JavaScript.

Critères de réussite :
- Le formulaire contient au moins six champs de types différents, dont « email », « tel », « password » et « select » ou « radio ».
- Chaque champ possède un « label » relié par « for » et « id », et un attribut « name » unique.
- Au moins trois champs sont obligatoires, et un champ utilise « minlength » ou « pattern ».
- Les boutons radio ou cases à cocher sont regroupés dans un « fieldset » avec « legend ».
- Le formulaire utilise « method="post" » et un bouton « submit » au libellé explicite.
TXT,
            'exercise_hint' => 'Commence par lister les champs et leur type, puis ajoute label et name pour chacun. Teste en soumettant le formulaire vide : le navigateur doit signaler les champs obligatoires.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Inscription à la formation</title>
</head>
<body>
  <main>
    <h1>Inscription à la formation</h1>

    <form action="/inscriptions" method="post">
      <p>
        <label for="nom">Nom complet</label>
        <input id="nom" name="nom" type="text" required minlength="3">
      </p>
      <p>
        <label for="email">Adresse email</label>
        <input id="email" name="email" type="email" required>
      </p>
      <p>
        <label for="tel">Téléphone</label>
        <input id="tel" name="tel" type="tel" pattern="[0-9]{10}">
      </p>
      <p>
        <label for="mdp">Mot de passe</label>
        <input id="mdp" name="mdp" type="password" required minlength="8">
      </p>
      <p>
        <label for="ville">Ville</label>
        <select id="ville" name="ville">
          <option value="abidjan">Abidjan</option>
          <option value="bouake">Bouaké</option>
          <option value="yamoussoukro">Yamoussoukro</option>
        </select>
      </p>
      <fieldset>
        <legend>Niveau actuel</legend>
        <label><input type="radio" name="niveau" value="debutant" checked> Débutant</label>
        <label><input type="radio" name="niveau" value="intermediaire"> Intermédiaire</label>
      </fieldset>

      <button type="submit">M'inscrire à la formation</button>
    </form>
  </main>
</body>
</html>
CODE,
        ],

        'Accessibilité' => [
            'description' => 'Comprends comment rendre une page utilisable au clavier et avec un lecteur d’écran grâce aux labels, au focus, aux alternatives textuelles et aux attributs ARIA.',
            'objective' => 'Corriger une page volontairement inaccessible pour qu’elle soit entièrement utilisable au clavier, avec des noms accessibles pour tous les contrôles.',
            'content' => <<<'MD'
## Pourquoi cette notion

L’accessibilité, c’est permettre à tout le monde d’utiliser ton site : une personne aveugle avec un lecteur d’écran, quelqu’un qui ne peut pas utiliser une souris, une personne âgée qui a besoin de textes plus lisibles, ou simplement un utilisateur sous le soleil avec un écran peu contrasté. Cela concerne beaucoup plus de monde qu’on ne le croit.

## Les concepts clés

### Le HTML natif d’abord

Un « button » natif reçoit le focus au clavier, réagit aux touches Entrée et Espace et est annoncé comme un bouton. Une « div » stylée comme un bouton n’a rien de tout cela. La règle première est donc d’utiliser le bon élément : « button » pour une action, « a » pour une navigation, « input » avec « label » pour un champ.

### Le nom accessible

Chaque contrôle doit avoir un nom compréhensible par un lecteur d’écran. Il vient du contenu textuel, d’un « label » relié, ou de l’attribut « aria-label » quand aucun texte visible n’existe, par exemple pour un bouton qui ne contient qu’une icône. Les images informatives reçoivent un « alt » pertinent.

### Clavier et focus

Un utilisateur doit pouvoir tout faire avec la touche Tab, Maj plus Tab, Entrée et Espace. Le focus est l’indicateur de l’élément actif. Ne le supprime jamais avec « outline: none » sans proposer une alternative visible. L’ordre de tabulation suit l’ordre du code source, donc ton HTML doit suivre un ordre logique. Évite « tabindex » avec une valeur positive. Un lien d’évitement placé au début de la page, du type « Aller au contenu », permet de sauter la navigation.

### ARIA et ses limites

ARIA est un ensemble d’attributs qui complètent le HTML quand il ne suffit pas, par exemple « aria-expanded » pour un menu déroulant ou « aria-live » pour annoncer un message dynamique. La première règle d’ARIA est de ne pas l’utiliser quand un élément natif existe. Un mauvais ARIA est pire que pas d’ARIA, car il induit en erreur.

### Contraste et lisibilité

Le texte doit avoir un contraste suffisant avec son fond et l’information ne doit jamais reposer sur la couleur seule. Un champ en erreur doit être signalé par un message texte, pas seulement par une bordure rouge.

## Exemple pas à pas

Le code_example corrige une page de contact. À l’étape un, un lien d’évitement mène au contenu principal. À l’étape deux, la navigation reçoit un « aria-label » et le « main » reçoit un « id ». À l’étape trois, le bouton qui ne contient qu’une icône reçoit un « aria-label ». À l’étape quatre, le champ email est relié à son « label » et à un texte d’aide par « aria-describedby ». À l’étape cinq, un message de confirmation utilise « role="status" » pour être annoncé. Le style de focus visible est défini dans la balise « style ».

## Erreurs fréquentes

- Utiliser une « div » avec un clic en guise de bouton : elle n’est pas atteignable au clavier. Remplace-la par un « button ».
- Supprimer l’outline du focus : le clavier devient inutilisable. Définis un style « :focus-visible » clairement visible.
- Laisser un bouton icône sans nom : le lecteur d’écran annonce seulement « bouton ». Ajoute « aria-label ».
- Indiquer une erreur uniquement par la couleur rouge : certains utilisateurs ne la perçoivent pas. Ajoute un message écrit.

## Bonnes pratiques

- Teste ta page uniquement avec le clavier avant chaque livraison.
- Utilise des éléments natifs et sémantiques avant toute solution personnalisée.
- Vérifie le contraste des couleurs avec un outil dédié.
- Associe les messages d’aide et d’erreur aux champs avec « aria-describedby ».

## Auto-évaluation

- Pourquoi un « button » est-il préférable à une « div » cliquable ?
- Comment donner un nom accessible à un bouton qui ne contient qu’une icône ?
- Pourquoi ne faut-il pas supprimer l’outline du focus ?
- Quelle est la première règle d’ARIA ?

## À retenir

- L’accessibilité commence par un HTML sémantique et des contrôles natifs.
- Tout contrôle doit avoir un nom accessible et être utilisable au clavier.
- Le focus doit toujours rester visible.
- ARIA complète le HTML, il ne le remplace pas.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Contact | Pharmacie du Plateau</title>
  <style>
    /* Focus toujours visible pour la navigation au clavier */
    :focus-visible { outline: 3px solid #1a56db; outline-offset: 2px; }
    /* Lien d'évitement : visible uniquement quand il reçoit le focus */
    .evitement { position: absolute; left: -999px; }
    .evitement:focus { left: 8px; top: 8px; background: #fff; padding: 8px; }
  </style>
</head>
<body>
  <!-- Étape 1 : lien d'évitement, premier élément focusable -->
  <a class="evitement" href="#contenu">Aller au contenu</a>

  <!-- Étape 2 : navigation nommée et zone principale ciblable -->
  <header>
    <nav aria-label="Navigation principale">
      <a href="/">Accueil</a>
      <a href="/contact">Contact</a>
      <!-- Étape 3 : bouton icône avec nom accessible -->
      <button type="button" aria-label="Ouvrir la recherche">&#128269;</button>
    </nav>
  </header>

  <main id="contenu">
    <h1>Nous contacter</h1>
    <form action="/contact" method="post">
      <!-- Étape 4 : label relié + aide associée -->
      <label for="email">Adresse email</label>
      <input id="email" name="email" type="email" required
             aria-describedby="aide-email">
      <p id="aide-email">Nous te répondons sous 24 heures.</p>

      <label for="message">Message</label>
      <textarea id="message" name="message" required></textarea>

      <button type="submit">Envoyer le message</button>
    </form>

    <!-- Étape 5 : message annoncé par les lecteurs d'écran -->
    <p role="status">Aucun message envoyé pour le moment.</p>
  </main>
</body>
</html>
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Rendre une page accessible au clavier',
            'exercise_description' => <<<'TXT'
Écris une page d’une petite pharmacie avec un menu, un bouton icône et un formulaire, puis garantis qu’elle est utilisable uniquement au clavier.

Critères de réussite :
- La page contient un lien d’évitement qui mène à un « main » portant un « id ».
- Le style « :focus-visible » rend le focus nettement visible sur les liens, boutons et champs.
- Tout bouton qui ne contient qu’une icône possède un « aria-label ».
- Chaque champ du formulaire a un « label » relié, et un champ possède une aide liée par « aria-describedby ».
- La page n’utilise aucune « div » ou « span » cliquable à la place d’un « button » ou d’un « a ».
TXT,
            'exercise_hint' => 'Teste en débranchant mentalement la souris : parcours la page avec Tab. Si tu ne vois pas où est le focus ou si un élément est sauté, corrige-le.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Pharmacie de la Paix | Contact</title>
  <style>
    :focus-visible { outline: 3px solid #1a56db; outline-offset: 2px; }
    .evitement { position: absolute; left: -999px; }
    .evitement:focus { left: 8px; top: 8px; background: #fff; padding: 8px; }
  </style>
</head>
<body>
  <a class="evitement" href="#contenu">Aller au contenu</a>

  <header>
    <nav aria-label="Navigation principale">
      <a href="/">Accueil</a>
      <a href="/gardes">Pharmacies de garde</a>
      <button type="button" aria-label="Ouvrir la recherche">&#128269;</button>
    </nav>
  </header>

  <main id="contenu">
    <h1>Demander un renseignement</h1>
    <form action="/renseignements" method="post">
      <p>
        <label for="nom">Nom</label>
        <input id="nom" name="nom" type="text" required>
      </p>
      <p>
        <label for="tel">Téléphone</label>
        <input id="tel" name="tel" type="tel" aria-describedby="aide-tel">
        <span id="aide-tel">Format : 10 chiffres sans espace.</span>
      </p>
      <p>
        <label for="question">Votre question</label>
        <textarea id="question" name="question" required></textarea>
      </p>
      <button type="submit">Envoyer ma demande</button>
    </form>
  </main>
</body>
</html>
CODE,
        ],

        'SEO et structure' => [
            'description' => 'Découvre comment title, meta description, titres, URL, balises Open Graph et données structurées aident les moteurs de recherche et les réseaux sociaux à comprendre ta page.',
            'objective' => 'Optimiser le head et la structure d’une page pour qu’elle ait un title unique, une meta description, des balises Open Graph, un lien canonique et un bloc JSON-LD valide.',
            'content' => <<<'MD'
## Pourquoi cette notion

Construire un beau site ne sert à rien si personne ne le trouve. Quand un client potentiel cherche « traiteur Abidjan » ou « cours de maths Yopougon », Google doit comprendre de quoi parle ta page pour la proposer. Le SEO, pour Search Engine Optimization, regroupe les pratiques qui aident les moteurs de recherche à lire, comprendre et classer tes pages.

## Les concepts clés

### Le title et la meta description

L’élément « title » est le titre affiché dans l’onglet et comme lien bleu dans les résultats de recherche. Il doit être unique par page, descriptif et concis, souvent entre 50 et 60 caractères. La balise « meta name="description" » résume la page en une ou deux phrases. Elle n’améliore pas directement le classement, mais elle influence le clic car Google l’affiche souvent sous le titre.

### La structure du contenu

Un seul « h1 » qui reprend le sujet principal, suivi de « h2 » et « h3 » logiques, aide les moteurs à comprendre le plan de la page. Les éléments sémantiques comme « main », « article » et « nav » précisent le rôle des zones. Les URL doivent être lisibles, comme « /services/creation-site-web », plutôt que « /page?id=47 ».

### Les balises de contrôle

La balise « link rel="canonical" » indique l’adresse de référence quand une même page est accessible par plusieurs URL, et évite le contenu dupliqué. La balise « meta name="robots" » avec « noindex » demande aux moteurs de ne pas indexer une page, utile pour une page de remerciement ou d’administration. L’attribut « lang » précise la langue.

### Open Graph pour les réseaux sociaux

Quand on partage un lien sur WhatsApp, Facebook ou LinkedIn, ces plateformes lisent des balises « meta property » commençant par « og: », comme « og:title », « og:description », « og:image » et « og:url ». Elles contrôlent l’aperçu affiché. Dans un pays où le partage de liens sur WhatsApp est massif, c’est un gain réel de professionnalisme.

### Les données structurées

Les données structurées décrivent le contenu dans un format standard, le plus souvent JSON-LD à l’intérieur d’un « script type="application/ld+json" ». Avec le vocabulaire schema.org, tu précises qu’une page décrit une entreprise locale, un produit ou un événement. Cela peut permettre des affichages enrichis dans les résultats, sans garantie : les moteurs décident seuls.

## Exemple pas à pas

Le code_example optimise la page d’une entreprise de traiteur. À l’étape un, le « title » combine le service et la ville. À l’étape deux, la meta description présente l’offre en une phrase. À l’étape trois, le lien canonique fixe l’URL de référence. À l’étape quatre, les balises Open Graph préparent l’aperçu de partage. À l’étape cinq, un bloc JSON-LD décrit l’entreprise avec son adresse. Dans le « body », un seul « h1 » reprend le sujet principal et les « h2 » découpent la page.

## Erreurs fréquentes

- Mettre le même « title » sur toutes les pages : les résultats se ressemblent et se concurrencent. Rédige un titre unique par page.
- Oublier la meta description : Google choisit un extrait au hasard. Écris une description claire d’environ 150 caractères.
- Utiliser plusieurs « h1 » ou aucun : le plan est confus. Garde un seul « h1 » par page.
- Mettre « noindex » par erreur en production : la page disparaît de Google. Vérifie la balise « robots » avant la mise en ligne.

## Bonnes pratiques

- Un « title » et une meta description uniques pour chaque page.
- Un seul « h1 », puis des titres hiérarchisés.
- Des URL lisibles, en minuscules et séparées par des tirets.
- Vérifier le JSON-LD avec un outil de test officiel avant la livraison.

## Auto-évaluation

- À quoi sert la meta description et influence-t-elle directement le classement ?
- Pourquoi un seul « h1 » par page ?
- Que fait « rel="canonical" » ?
- Quelles balises contrôlent l’aperçu d’un lien partagé sur WhatsApp ?

## À retenir

- Le « title » et la meta description sont les premiers éléments vus dans les résultats de recherche.
- Un plan de titres clair aide les moteurs et les lecteurs.
- Le lien canonique évite les doublons, « noindex » retire une page de l’index.
- Les balises Open Graph contrôlent l’aperçu des liens partagés.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <!-- Étape 1 : titre unique, service + ville -->
  <title>Traiteur à Abidjan | Saveurs de Cocody</title>

  <!-- Étape 2 : description affichée dans les résultats -->
  <meta name="description"
        content="Traiteur à Abidjan pour mariages, baptêmes et réunions. Devis gratuit sous 24 heures.">

  <!-- Étape 3 : URL de référence -->
  <link rel="canonical" href="https://www.exemple.ci/traiteur-abidjan">

  <!-- Étape 4 : aperçu pour WhatsApp, Facebook, LinkedIn -->
  <meta property="og:type" content="website">
  <meta property="og:title" content="Traiteur à Abidjan | Saveurs de Cocody">
  <meta property="og:description" content="Mariages, baptêmes et réunions.">
  <meta property="og:url" content="https://www.exemple.ci/traiteur-abidjan">
  <meta property="og:image" content="https://www.exemple.ci/images/apercu.jpg">

  <!-- Étape 5 : données structurées JSON-LD -->
  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "Saveurs de Cocody",
    "telephone": "+225 00 00 00 00 00",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Abidjan",
      "addressCountry": "CI"
    }
  }
  </script>
</head>
<body>
  <main>
    <h1>Traiteur à Abidjan</h1>
    <h2>Nos formules</h2>
    <p>Buffets, cocktails et repas servis pour toutes vos réceptions.</p>
    <h2>Demander un devis</h2>
    <p><a href="/devis">Remplir le formulaire de devis</a></p>
  </main>
</body>
</html>
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Optimiser la page d’un commerce local',
            'exercise_description' => <<<'TXT'
Choisis un commerce (salon, garage, école) et écris la page d’accueil en soignant tout le « head » pour le référencement et le partage.

Critères de réussite :
- Le « title » est unique, descriptif, et mentionne l’activité et la ville.
- Une meta description d’une ou deux phrases et un « link rel="canonical" » sont présents.
- Les balises Open Graph « og:title », « og:description », « og:url » et « og:image » sont renseignées.
- Un bloc JSON-LD valide de type « LocalBusiness » contient le nom, le téléphone et la ville.
- Le « body » contient un seul « h1 », au moins deux « h2 » et aucun saut de niveau de titre.
TXT,
            'exercise_hint' => 'Rédige d’abord le title et la description comme si tu écrivais le résultat Google. Pour le JSON-LD, vérifie les virgules et les guillemets : un seul oubli rend le bloc invalide.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Garage automobile à Yopougon | Garage Fiabilité</title>
  <meta name="description"
        content="Garage à Yopougon : vidange, freins, diagnostic électronique. Prenez rendez-vous en ligne.">
  <link rel="canonical" href="https://www.exemple.ci/">

  <meta property="og:type" content="website">
  <meta property="og:title" content="Garage Fiabilité | Yopougon">
  <meta property="og:description" content="Vidange, freins et diagnostic électronique.">
  <meta property="og:url" content="https://www.exemple.ci/">
  <meta property="og:image" content="https://www.exemple.ci/images/garage.jpg">

  <script type="application/ld+json">
  {
    "@context": "https://schema.org",
    "@type": "LocalBusiness",
    "name": "Garage Fiabilité",
    "telephone": "+225 00 00 00 00 00",
    "address": {
      "@type": "PostalAddress",
      "addressLocality": "Abidjan",
      "addressCountry": "CI"
    }
  }
  </script>
</head>
<body>
  <main>
    <h1>Garage automobile à Yopougon</h1>

    <h2>Nos services</h2>
    <p>Vidange, freins, pneus et diagnostic électronique.</p>

    <h2>Prendre rendez-vous</h2>
    <p><a href="/rendez-vous">Réserver un créneau</a></p>
  </main>
</body>
</html>
CODE,
        ],

        'Projet final HTML' => [
            'description' => 'Assemble sémantique, médias, formulaire, accessibilité et SEO dans une page de présentation complète, sans framework ni CSS complexe.',
            'objective' => 'Livrer une page de présentation HTML complète, valide au W3C, accessible au clavier, avec navigation, sections, galerie et formulaire de contact fonctionnel côté navigateur.',
            'content' => <<<'MD'
## Pourquoi cette notion

Ce projet final rassemble tout ce que tu as vu dans le parcours HTML. En entreprise, on te demandera rarement un exercice isolé : on te demandera une page complète pour un client, avec un menu, plusieurs sections, des photos et un moyen de contact. C’est exactement la structure d’un site vitrine, le premier produit que vend la plupart des agences web.

## Les concepts clés

### Partir du contenu et du plan

Avant d’écrire une balise, définis le but de la page, la cible et les sections nécessaires. Une page de présentation contient en général une introduction, une présentation des services ou réalisations, une galerie, et un moyen de contact. Écris le plan des titres : un « h1 » pour la page, un « h2 » par section.

### Assembler les briques

Chaque notion du parcours a son rôle. La sémantique donne le squelette avec « header », « nav », « main », « section » et « footer ». Les médias illustrent le propos avec des « figure », des « img » et leurs « alt ». Le formulaire permet le contact avec des « label », des types précis et la validation native. L’accessibilité garantit l’usage au clavier avec un lien d’évitement et un focus visible. Le SEO prépare l’indexation avec un « title », une description et des balises de partage.

### La navigation interne

Dans une page unique, les liens du menu peuvent pointer vers des ancres grâce aux attributs « id » des sections, par exemple « href="#contact" » pour atteindre la section qui porte « id="contact" ». Chaque « id » doit rester unique dans la page.

### La validation et les tests

Un projet fini se vérifie : validateur HTML du W3C, test au clavier, test à la largeur d’un téléphone, test de chaque lien, et relecture des textes. Ces contrôles font la différence entre un exercice et un livrable professionnel.

## Exemple pas à pas

Le code_example présente la page d’un photographe de Grand-Bassam. À l’étape un, le « head » regroupe encodage, viewport, titre et description. À l’étape deux, le lien d’évitement et le « header » avec sa navigation par ancres ouvrent la page. À l’étape trois, la section « À propos » présente le photographe. À l’étape quatre, la section « Galerie » utilise plusieurs « figure ». À l’étape cinq, la section « Contact » contient le formulaire avec ses « label », ses types précis et ses champs obligatoires. Le « footer » termine la page avec les informations légales.

## Erreurs fréquentes

- Commencer à coder sans plan : la structure devient incohérente. Écris d’abord la liste des sections et des titres.
- Oublier les « id » des sections : les liens du menu ne mènent nulle part. Vérifie que chaque ancre correspond à un « id » existant.
- Dupliquer un « id » dans la page : le document devient invalide et les ancres se comportent mal. Garde des identifiants uniques.
- Livrer sans test sur mobile : la page peut être inutilisable sur un petit écran. Teste avec les outils de développement du navigateur.

## Bonnes pratiques

- Définis le plan de la page et la hiérarchie des titres avant de coder.
- Utilise uniquement des éléments sémantiques adaptés, avec les « div » en dernier recours.
- Teste au clavier, au validateur et sur une largeur de téléphone.
- Garde des noms de fichiers et des « id » simples, en minuscules et sans accents.

## Auto-évaluation

- Comment un lien du menu atteint-il une section précise de la même page ?
- Quels éléments sémantiques structurent une page de présentation ?
- Quelles vérifications fais-tu avant de considérer la page terminée ?
- Pourquoi chaque image de la galerie doit-elle avoir un « alt » propre ?

## À retenir

- Un projet commence par un plan de contenu et de titres.
- Le menu d’une page unique s’appuie sur des ancres et des « id » uniques.
- Sémantique, médias, formulaire, accessibilité et SEO se combinent dans une même page.
- La validation et les tests font la qualité d’un livrable.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <!-- Étape 1 : métadonnées et SEO -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Photographe à Grand-Bassam | Studio Lagune</title>
  <meta name="description"
        content="Photographe à Grand-Bassam : portraits, mariages et reportages. Demandez un devis.">
</head>
<body>
  <!-- Étape 2 : accès rapide + en-tête -->
  <a href="#contenu">Aller au contenu</a>
  <header>
    <p>Studio Lagune</p>
    <nav aria-label="Navigation principale">
      <ul><li><a href="#apropos">À propos</a></li><li><a href="#galerie">Galerie</a></li><li><a href="#contact">Contact</a></li></ul>
    </nav>
  </header>

  <main id="contenu">
    <h1>Studio Lagune, photographe à Grand-Bassam</h1>

    <!-- Étape 3 : présentation -->
    <section id="apropos">
      <h2>À propos</h2>
      <p>Je photographie les portraits, mariages et reportages depuis dix ans.</p>
    </section>

    <!-- Étape 4 : galerie d'images -->
    <section id="galerie">
      <h2>Galerie</h2>
      <figure>
        <img src="mariage.jpg" alt="Couple de mariés devant l'église" width="480" height="320" loading="lazy">
        <figcaption>Mariage à Grand-Bassam</figcaption>
      </figure>
      <figure>
        <img src="portrait.jpg" alt="Portrait d'une femme souriante en pagne" width="480" height="320" loading="lazy">
        <figcaption>Portrait en studio</figcaption>
      </figure>
    </section>

    <!-- Étape 5 : formulaire de contact -->
    <section id="contact">
      <h2>Contact</h2>
      <form action="/contact" method="post">
        <p><label for="nom">Nom</label> <input id="nom" name="nom" type="text" required></p>
        <p><label for="email">Email</label> <input id="email" name="email" type="email" required></p>
        <p><label for="message">Message</label> <textarea id="message" name="message" required></textarea></p>
        <button type="submit">Demander un devis</button>
      </form>
    </section>
  </main>

  <footer><p>Studio Lagune, Grand-Bassam</p></footer></body>
</html>
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : page de présentation complète',
            'exercise_description' => <<<'TXT'
Construis une page de présentation pour une activité réelle ou fictive (artisan, école, freelance, restaurant), en un seul fichier « index.html », sans framework et sans bibliothèque.

Livrables :
- Un fichier « index.html » et au moins deux images ou un dossier « images ».
- Une courte note listant les vérifications effectuées (validateur, clavier, mobile).

Critères de réussite :
- Le document est valide au validateur du W3C, avec « lang », un « title » unique et une meta description.
- La structure utilise « header », « nav » avec ancres, « main », au moins trois « section » avec un « h2 » chacune, et « footer », avec un seul « h1 ».
- Une galerie d’au moins trois « figure » contient des images avec « alt », « width », « height » et « loading="lazy" ».
- Un formulaire de contact possède au moins quatre champs avec « label », « name », des types précis et des champs « required ».
- La page contient un lien d’évitement, et tous les liens et ancres du menu fonctionnent.
TXT,
            'exercise_hint' => 'Écris d’abord le plan des titres, puis le squelette sémantique sans contenu, puis remplis section par section. Termine par les tests : validateur, touche Tab, puis vue mobile.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Menuiserie Kouadio | Meubles sur mesure à Bouaké</title>
  <meta name="description"
        content="Menuiserie à Bouaké : meubles sur mesure, cuisines et portes. Devis gratuit.">
</head>
<body>
  <a href="#contenu">Aller au contenu</a>

  <header>
    <p>Menuiserie Kouadio</p>
    <nav aria-label="Navigation principale">
      <ul>
        <li><a href="#services">Services</a></li>
        <li><a href="#realisations">Réalisations</a></li>
        <li><a href="#contact">Contact</a></li>
      </ul>
    </nav>
  </header>

  <main id="contenu">
    <h1>Menuiserie Kouadio, meubles sur mesure à Bouaké</h1>

    <section id="services">
      <h2>Nos services</h2>
      <ul>
        <li>Meubles sur mesure</li>
        <li>Cuisines équipées</li>
        <li>Portes et fenêtres en bois</li>
      </ul>
    </section>

    <section id="realisations">
      <h2>Nos réalisations</h2>
      <figure>
        <img src="images/table.jpg" alt="Table en bois massif avec six chaises" width="480" height="320" loading="lazy">
        <figcaption>Table de salle à manger</figcaption>
      </figure>
      <figure>
        <img src="images/cuisine.jpg" alt="Cuisine en bois clair avec plan de travail" width="480" height="320" loading="lazy">
        <figcaption>Cuisine équipée</figcaption>
      </figure>
      <figure>
        <img src="images/porte.jpg" alt="Porte d'entrée en bois sculpté" width="480" height="320" loading="lazy">
        <figcaption>Porte d'entrée sculptée</figcaption>
      </figure>
    </section>

    <section id="contact">
      <h2>Demander un devis</h2>
      <form action="/devis" method="post">
        <p><label for="nom">Nom</label>
           <input id="nom" name="nom" type="text" required></p>
        <p><label for="email">Email</label>
           <input id="email" name="email" type="email" required></p>
        <p><label for="tel">Téléphone</label>
           <input id="tel" name="tel" type="tel" required></p>
        <p><label for="projet">Votre projet</label>
           <textarea id="projet" name="projet" required></textarea></p>
        <button type="submit">Envoyer ma demande</button>
      </form>
    </section>
  </main>

  <footer><p>Menuiserie Kouadio, Bouaké</p></footer>
</body>
</html>
CODE,
        ],
    ],
];
