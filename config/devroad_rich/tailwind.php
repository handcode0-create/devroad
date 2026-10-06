<?php

return [
    'lessons' => [
        'Découvrir Tailwind' => [
            'description' => 'Découvre l’approche utility-first de Tailwind : composer l’apparence d’un élément avec de petites classes dédiées plutôt que d’écrire une feuille CSS par composant.',
            'objective' => 'Construire une carte de produit complète uniquement avec des classes utilitaires Tailwind, en expliquant le rôle de chaque classe et sans écrire une seule ligne de CSS personnalisé.',
            'content' => <<<'MD'
## Pourquoi cette notion

Quand on écrit du CSS classique, on invente des noms de classes, on les définit dans une feuille, puis on jongle entre le HTML et le CSS. Au bout de quelques semaines, la feuille grossit, les noms se contredisent et personne n’ose rien supprimer. Tailwind CSS propose une autre voie : des classes utilitaires déjà prêtes, chacune faisant une seule chose, que tu combines directement dans ton HTML.

Tailwind est devenu très répandu dans les projets Next.js, React, Laravel et Svelte. Les agences l’apprécient pour la vitesse d’intégration et la cohérence visuelle entre développeurs. Pour un freelance qui livre vite à des clients, c’est un outil de productivité majeur.

## Les concepts clés

### L’approche utility-first

Une classe utilitaire correspond à une déclaration CSS. La classe « p-4 » ajoute un padding, « text-center » centre le texte, « rounded-xl » arrondit les coins, « bg-orange-500 » applique une couleur de fond. Pour styliser un élément, tu additionnes ces classes.

### L’échelle de design

Tailwind ne te laisse pas inventer des valeurs au hasard. Les espacements suivent une échelle : « 1 » vaut 0,25 rem, « 4 » vaut 1 rem, « 8 » vaut 2 rem. Les couleurs sont nommées avec une teinte et un niveau, comme « slate-900 » ou « orange-500 ». Les tailles de texte vont de « text-sm » à « text-3xl ».

### Comment Tailwind fonctionne

Tailwind analyse tes fichiers, repère les classes utilisées et génère uniquement le CSS correspondant. Le fichier final reste donc léger, un avantage important pour des utilisateurs avec une connexion lente. Il faut donc écrire les noms de classes en entier dans ton code : une classe construite par concaténation de morceaux de texte n’est pas détectée.

### Les préoccupations fréquentes

On reproche à Tailwind d’alourdir le HTML avec de longues chaînes de classes. Cette longueur se gère en extrayant des composants réutilisables, un sujet de la leçon sur les composants. Tu peux aussi utiliser les extensions d’éditeur qui proposent l’autocomplétion et trient les classes.

## Exemple pas à pas

Le code_example crée une carte de produit pour une boutique en ligne. À l’étape un, le conteneur reçoit « max-w-sm », « rounded-2xl », « bg-white » et « shadow-md » pour la largeur, les coins arrondis, le fond et l’ombre. À l’étape deux, l’image est étirée avec « w-full » et « h-48 », avec « object-cover » pour éviter la déformation. À l’étape trois, le contenu reçoit « p-5 » et un « space-y-2 » pour espacer ses enfants. À l’étape quatre, le titre et le prix utilisent des classes de typographie et de couleur. À l’étape cinq, le bouton combine fond orange, texte blanc, padding et coins arrondis. Les commentaires du fichier détaillent chaque classe.

## Erreurs fréquentes

- Inventer des classes qui n’existent pas, comme « padding-4 » : rien ne s’applique. Vérifie le nom dans la documentation ou grâce à l’autocomplétion.
- Construire des noms de classes par morceaux, par exemple « bg-ligne-» plus une variable : Tailwind ne les détecte pas. Écris les noms complets.
- Oublier que Tailwind réinitialise les styles : un « h1 » n’a plus de taille ni de marge. Ajoute explicitement les classes de texte.
- Multiplier les valeurs arbitraires entre crochets : le projet perd sa cohérence. Reste sur l’échelle de design.
- Penser qu’il remplace la connaissance du CSS : sans comprendre le box model et Flexbox, tu seras bloqué. Révise les bases du CSS en parallèle.

## Bonnes pratiques

- Garde l’échelle de design et évite les valeurs arbitraires.
- Utilise l’extension d’éditeur de Tailwind pour l’autocomplétion et le tri des classes.
- Écris toujours les classes complètes, sans concaténation.
- Consulte la documentation officielle dès qu’un nom de classe te manque.

## Auto-évaluation

- Qu’est-ce qu’une classe utilitaire ?
- Pourquoi le CSS final de Tailwind reste-t-il léger ?
- Pourquoi ne faut-il pas assembler des noms de classes par concaténation ?
- Que signifie « p-4 » en pratique ?

## À retenir

- Tailwind compose l’apparence avec de petites classes utilitaires.
- L’échelle de design garantit la cohérence des espacements, tailles et couleurs.
- Seules les classes utilisées sont générées dans le CSS final.
- Les noms de classes doivent être écrits en entier dans le code.
- Les bases du CSS restent nécessaires.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Carte produit Tailwind</title>
  <!-- Script de démonstration : en projet réel, installe Tailwind via ton outil de build -->
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-6">

  <!-- Étape 1 : conteneur de la carte (largeur max, coins, fond, ombre) -->
  <article class="mx-auto max-w-sm overflow-hidden rounded-2xl bg-white shadow-md">

    <!-- Étape 2 : image qui remplit sa zone sans se déformer -->
    <img class="h-48 w-full object-cover"
         src="https://placehold.co/600x400"
         alt="Sac en pagne wax coloré">

    <!-- Étape 3 : contenu avec espace vertical régulier entre enfants -->
    <div class="space-y-2 p-5">

      <!-- Étape 4 : typographie et couleurs -->
      <h2 class="text-xl font-bold text-slate-900">Sac en pagne wax</h2>
      <p class="text-sm text-slate-600">
        Fabriqué à la main à Abidjan, résistant et léger.
      </p>
      <p class="text-lg font-semibold text-orange-600">8 000 FCFA</p>

      <!-- Étape 5 : bouton (fond, texte, espacement, coins) -->
      <button type="button"
              class="mt-2 w-full rounded-xl bg-orange-500 px-4 py-2 font-semibold text-white">
        Ajouter au panier
      </button>
    </div>
  </article>

</body>
</html>
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Carte de profil en utilitaires',
            'exercise_description' => <<<'TXT'
Crée une carte de profil pour un artisan ou un prestataire de service (photo, nom, métier, courte description, bouton de contact) uniquement avec des classes Tailwind.

Critères de réussite :
- Aucune balise « style » ni feuille CSS personnalisée n’est utilisée.
- La carte a une largeur maximale, un fond, des coins arrondis et une ombre.
- L’image est dimensionnée avec « object-cover » et possède un texte « alt » descriptif.
- Le titre, le métier et la description utilisent au moins trois tailles ou couleurs de texte différentes.
- Le bouton de contact a un fond coloré, un texte lisible, un padding et des coins arrondis, sans valeur arbitraire entre crochets.
TXT,
            'exercise_hint' => 'Commence par le conteneur, puis ajoute les éléments un par un en actualisant la page à chaque étape. Si une classe ne produit rien, vérifie son orthographe dans la documentation.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Profil artisan</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 p-6">

  <article class="mx-auto max-w-xs overflow-hidden rounded-2xl bg-white shadow-lg">
    <img class="h-56 w-full object-cover"
         src="https://placehold.co/400x400"
         alt="Portrait d'un couturier dans son atelier">

    <div class="space-y-2 p-5 text-center">
      <h2 class="text-2xl font-bold text-slate-900">Koffi Yao</h2>
      <p class="text-sm font-semibold uppercase text-orange-600">Couturier</p>
      <p class="text-slate-600">
        Créations sur mesure à Bouaké depuis dix ans : robes, costumes et tenues de cérémonie.
      </p>

      <a href="tel:+2250000000000"
         class="mt-3 inline-block rounded-xl bg-orange-500 px-6 py-2 font-semibold text-white">
        Me contacter
      </a>
    </div>
  </article>

</body>
</html>
CODE,
        ],

        'Layout et spacing' => [
            'description' => 'Maîtrise les utilitaires flex, grid, gap, padding et margin de Tailwind pour construire des mises en page propres et un espacement cohérent.',
            'objective' => 'Réaliser un en-tête, une grille de services et un pied de page uniquement avec les utilitaires de layout et de spacing, en justifiant le choix entre flex et grid.',
            'content' => <<<'MD'
## Pourquoi cette notion

La majorité du travail d’intégration consiste à placer des éléments : aligner un logo et un menu, répartir des cartes, espacer des blocs. Avec Tailwind, tu exprimes cette structure directement dans le HTML grâce à des classes qui correspondent à Flexbox, à Grid et au box model. Tu n’ouvres plus de feuille CSS pour dire qu’un bloc est en ligne.

En entreprise, un espacement régulier fait la différence entre une interface qui paraît professionnelle et une qui paraît bricolée. Tailwind impose une échelle d’espacement unique, ce qui rend le résultat cohérent même quand plusieurs développeurs travaillent sur le même projet.

## Les concepts clés

### Padding et margin

Les classes de padding commencent par « p » : « p-4 » sur tous les côtés, « px-6 » à l’horizontale, « py-2 » à la verticale, « pt-4 » en haut. Pour les marges, c’est « m » : « mt-4 », « mx-auto » pour centrer un bloc. Les valeurs suivent l’échelle, où chaque unité vaut 0,25 rem.

### Flexbox

La classe « flex » transforme un élément en conteneur flex. Avec « flex-col », les enfants s’empilent. Les classes « justify-between », « justify-center » et « items-center » correspondent à « justify-content » et « align-items ».

### Grid

La classe « grid » active la grille. Tu définis les colonnes avec « grid-cols-3 » pour trois colonnes égales, et tu espaces avec « gap-4 ». Un élément qui s’étend sur deux colonnes reçoit « col-span-2 ». Pour une colonne latérale fixe et un contenu souple, la valeur arbitraire « grid-cols-[220px_1fr] » existe.

### Largeurs et conteneurs

Les classes « w-full », « max-w-md » et « max-w-6xl » contrôlent la largeur. Un conteneur centré classique s’écrit « mx-auto max-w-6xl px-4 ». La classe « min-h-screen » donne à un bloc au moins la hauteur de l’écran. Pour choisir entre flex et grid : flex pour aligner des éléments sur un axe, grid pour structurer en lignes et colonnes.

## Exemple pas à pas

Le code_example assemble la page d’un service de coursiers. À l’étape un, l’en-tête utilise « flex », « items-center » et « justify-between » pour séparer logo et menu. À l’étape deux, le menu est un conteneur flex avec « gap-6 ». À l’étape trois, la section principale est centrée avec « mx-auto max-w-6xl px-4 py-12 ». À l’étape quatre, la grille de services utilise « grid grid-cols-3 gap-6 ». À l’étape cinq, le pied de page empile ses éléments avec « space-y-2 ». Chaque classe est commentée.

## Erreurs fréquentes

- Utiliser « justify-between » sans « flex » : la classe est ignorée car l’élément n’est pas un conteneur flex. Ajoute « flex » sur le parent.
- Mettre « gap-4 » sur un élément sans flex ni grid : l’espace n’apparaît pas. Applique « gap » sur un conteneur flex ou grid.
- Utiliser des marges pour espacer des éléments dans un conteneur flex : le dernier élément garde un espace en trop. Préfère « gap » ou « space-y ».
- Oublier « mx-auto » avec « max-w » : le bloc reste collé à gauche. Ajoute « mx-auto ».
- Multiplier les valeurs arbitraires pour chaque marge : l’espacement devient incohérent. Reste sur l’échelle.

## Bonnes pratiques

- Utilise « gap » dans les conteneurs flex et grid pour espacer les enfants.
- Garde un conteneur de page standard réutilisé partout, comme « mx-auto max-w-6xl px-4 ».
- Choisis flex pour une dimension et grid pour deux.
- Limite-toi aux valeurs de l’échelle d’espacement.

## Auto-évaluation

- Que signifient « px-6 » et « mt-4 » ?
- Quelle classe centre un bloc horizontalement quand il a une largeur maximale ?
- Quelle différence entre « gap-4 » et « space-y-4 » ?
- Comment obtenir trois colonnes égales ?

## À retenir

- Les utilitaires de padding, margin, flex et grid décrivent la structure dans le HTML.
- « gap » et « space-y » espacent proprement les enfants.
- Un conteneur centré se compose de « mx-auto », « max-w » et « px ».
- Flex convient à une dimension, grid à deux.
- L’échelle d’espacement garantit la cohérence.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Coursiers Express</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-white text-slate-800">

  <!-- Étape 1 : en-tête, logo à gauche et menu à droite -->
  <header class="flex items-center justify-between bg-slate-900 px-6 py-4 text-white">
    <a href="/" class="text-xl font-bold">Coursiers Express</a>

    <!-- Étape 2 : menu en flex avec un espace régulier -->
    <nav class="flex gap-6 text-sm">
      <a href="#services">Services</a>
      <a href="#contact">Contact</a>
    </nav>
  </header>

  <!-- Étape 3 : conteneur de page centré -->
  <main class="mx-auto max-w-6xl px-4 py-12">
    <h1 class="mb-8 text-3xl font-bold">Nos services de livraison</h1>

    <!-- Étape 4 : grille de trois colonnes égales -->
    <section id="services" class="grid grid-cols-3 gap-6">
      <article class="rounded-xl border border-slate-200 p-6">
        <h2 class="font-semibold">Express</h2>
        <p class="mt-2 text-sm text-slate-600">Livraison en moins de deux heures.</p>
      </article>
      <article class="rounded-xl border border-slate-200 p-6">
        <h2 class="font-semibold">Standard</h2>
        <p class="mt-2 text-sm text-slate-600">Livraison le jour même.</p>
      </article>
      <article class="col-span-1 rounded-xl border border-slate-200 p-6">
        <h2 class="font-semibold">Colis lourds</h2>
        <p class="mt-2 text-sm text-slate-600">Transport par camionnette.</p>
      </article>
    </section>
  </main>

  <!-- Étape 5 : pied de page avec éléments empilés -->
  <footer id="contact" class="space-y-2 bg-slate-900 px-6 py-8 text-center text-sm text-slate-300">
    <p>Abidjan, Côte d'Ivoire</p>
    <p>contact@exemple.ci</p>
  </footer>

</body>
</html>
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Page vitrine en flex et grid',
            'exercise_description' => <<<'TXT'
Construis une page vitrine pour un restaurant avec un en-tête, une section de plats en grille, une bande d’informations en flex et un pied de page, uniquement avec des utilitaires de layout.

Critères de réussite :
- L’en-tête utilise « flex », « items-center » et « justify-between ».
- Le contenu principal est centré avec « mx-auto », « max-w-… » et « px-… ».
- Une grille de quatre plats utilise « grid », « grid-cols-… » et « gap-… ».
- Une bande d’informations utilise « flex » avec « gap-… » et « flex-wrap ».
- Aucun style personnalisé n’est écrit et aucune marge n’est utilisée pour espacer des enfants d’un conteneur flex.
TXT,
            'exercise_hint' => 'Teste ton layout en réduisant la fenêtre : si la bande d’informations déborde, c’est qu’il manque « flex-wrap ». Pour l’espacement entre éléments, cherche d’abord à utiliser « gap ».',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Chez Maman Adjoua</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-amber-50 text-slate-800">

  <header class="flex items-center justify-between bg-amber-900 px-6 py-4 text-white">
    <a href="/" class="text-xl font-bold">Chez Maman Adjoua</a>
    <nav class="flex gap-6 text-sm">
      <a href="#plats">Plats</a>
      <a href="#infos">Infos</a>
    </nav>
  </header>

  <main class="mx-auto max-w-5xl px-4 py-12">
    <h1 class="mb-8 text-3xl font-bold">Nos plats du jour</h1>

    <section id="plats" class="grid grid-cols-2 gap-6">
      <article class="rounded-xl bg-white p-6 shadow">Attiéké poisson</article>
      <article class="rounded-xl bg-white p-6 shadow">Kedjenou de poulet</article>
      <article class="rounded-xl bg-white p-6 shadow">Foutou sauce graine</article>
      <article class="rounded-xl bg-white p-6 shadow">Riz gras</article>
    </section>

    <section id="infos" class="mt-10 flex flex-wrap gap-4 text-sm">
      <p class="rounded-lg bg-amber-200 px-4 py-2">Ouvert de 11h à 22h</p>
      <p class="rounded-lg bg-amber-200 px-4 py-2">Livraison à Cocody</p>
      <p class="rounded-lg bg-amber-200 px-4 py-2">Paiement mobile money</p>
    </section>
  </main>

  <footer class="space-y-2 bg-amber-900 px-6 py-8 text-center text-sm text-amber-100">
    <p>Abidjan, Cocody</p>
    <p>contact@exemple.ci</p>
  </footer>

</body>
</html>
CODE,
        ],

        'Responsive design' => [
            'description' => 'Utilise les variantes sm, md, lg et xl de Tailwind pour adapter ton interface à tous les écrans avec une approche mobile-first.',
            'objective' => 'Rendre une page responsive en partant des classes mobiles sans préfixe, puis en ajoutant des variantes md et lg pour la grille, la navigation et les tailles de texte.',
            'content' => <<<'MD'
## Pourquoi cette notion

La majorité de ton audience en Afrique de l’Ouest navigue sur téléphone. Une interface qui ne s’adapte pas à la taille de l’écran fait perdre des clients dès la première visite. Avec CSS classique, il faut écrire des media queries dans la feuille. Tailwind les remplace par des préfixes que tu places directement devant les classes.

Savoir penser mobile-first est une compétence attendue par tous les employeurs. Elle te permet de livrer des pages légères, lisibles sur un petit écran et enrichies progressivement sur un grand.

## Les concepts clés

### Les variantes de breakpoint

Tailwind fournit des préfixes appliqués à partir d’une largeur minimale : « sm » pour environ 640 pixels, « md » pour 768, « lg » pour 1024, « xl » pour 1280 et « 2xl » pour 1536. Une classe comme « md:grid-cols-2 » signifie : à partir de la largeur « md », utiliser deux colonnes. Ces valeurs par défaut peuvent être personnalisées dans la configuration.

### Mobile-first

Une classe sans préfixe s’applique à toutes les tailles, y compris le mobile. Un préfixe ajoute un comportement à partir d’un seuil, donc « md:flex-row » n’agit que sur les écrans moyens et plus grands. Il faut donc écrire d’abord le style mobile sans préfixe, puis ajouter des préfixes pour élargir.

### Les schémas courants

Pour une grille adaptable : « grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3 ». Pour une direction qui change : « flex flex-col md:flex-row ». Pour afficher ou masquer : « hidden md:block » cache un élément sur mobile et l’affiche à partir de « md », et « md:hidden » fait l’inverse, utile pour un bouton de menu mobile. Pour le texte : « text-2xl md:text-4xl ». Les paddings et les espacements s’adaptent de la même manière.

### Combiner avec d’autres variantes

Les variantes se cumulent, comme « md:hover:bg-orange-600 ». Il existe aussi des variantes de préférence, comme « motion-reduce » pour respecter la réduction de mouvement. Pense à ajouter la balise viewport dans le « head » du HTML.

## Exemple pas à pas

Le code_example adapte la page d’une école de formation. À l’étape un, le HTML contient la balise viewport. À l’étape deux, le conteneur d’en-tête passe de colonne à ligne avec « flex-col md:flex-row ». À l’étape trois, le menu complet est masqué sur mobile avec « hidden md:flex » et un bouton « md:hidden » le remplace. À l’étape quatre, le titre grossit avec « text-3xl md:text-5xl ». À l’étape cinq, la grille des formations passe d’une à deux puis trois colonnes. Chaque préfixe est commenté.

## Erreurs fréquentes

- Utiliser « sm: » pour cibler le mobile : il ne s’applique qu’à partir de 640 pixels. Écris la classe sans préfixe pour le mobile.
- Écrire d’abord le style pour grand écran avec « lg: » puis défaire : le code se complique. Pars de la version mobile.
- Oublier la balise viewport : les préfixes ne se déclenchent pas comme prévu sur téléphone. Ajoute-la dans le « head ».
- Cacher du contenu essentiel sur mobile avec « hidden » : l’utilisateur n’y a plus accès. Propose une alternative comme un menu replié.
- Se fier uniquement au navigateur du PC : l’affichage réel diffère. Teste avec le simulateur d’appareils et un vrai téléphone.

## Bonnes pratiques

- Écris les classes sans préfixe pour le mobile, puis ajoute « md: » et « lg: ».
- Limite-toi à deux ou trois breakpoints pour garder un code lisible.
- Garde la lisibilité du texte et des zones tactiles confortables sur mobile.
- Teste à 360 pixels de large avant de livrer.

## Auto-évaluation

- Que signifie « md:grid-cols-2 » ?
- Pourquoi « sm: » ne désigne-t-il pas le mobile ?
- Comment masquer un élément sur mobile et l’afficher sur bureau ?
- Comment faire passer un conteneur de colonne à ligne à partir de 768 pixels ?

## À retenir

- Les préfixes de breakpoint s’appliquent à partir d’une largeur minimale.
- Les classes sans préfixe constituent le style mobile.
- Mobile-first : base d’abord, puis ajouts avec « md: » et « lg: ».
- « hidden » et « md:block » règlent l’affichage selon l’écran.
- Un test sur petit écran est indispensable.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <!-- Étape 1 : viewport indispensable au responsive -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>École Excellence</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-white text-slate-800">

  <!-- Étape 2 : colonne sur mobile, ligne à partir de md (768px) -->
  <header class="flex flex-col gap-3 bg-indigo-900 p-4 text-white md:flex-row md:items-center md:justify-between md:px-8">
    <a href="/" class="text-xl font-bold">École Excellence</a>

    <!-- Étape 3 : menu visible seulement à partir de md -->
    <nav class="hidden gap-6 md:flex">
      <a href="#formations">Formations</a>
      <a href="#contact">Contact</a>
    </nav>
    <!-- Bouton visible uniquement sur mobile -->
    <button type="button" class="rounded-lg bg-indigo-700 px-4 py-2 md:hidden">Menu</button>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-10">
    <!-- Étape 4 : texte plus grand sur grand écran -->
    <h1 class="text-3xl font-bold md:text-5xl">Réussis ta formation</h1>
    <p class="mt-3 text-base text-slate-600 md:text-lg">
      Des cours du soir et du week-end pour tous les niveaux.
    </p>

    <!-- Étape 5 : 1 colonne, puis 2 (md), puis 3 (xl) -->
    <section id="formations" class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article class="rounded-xl border border-slate-200 p-5">Comptabilité</article>
      <article class="rounded-xl border border-slate-200 p-5">Informatique</article>
      <article class="rounded-xl border border-slate-200 p-5">Anglais</article>
      <article class="rounded-xl border border-slate-200 p-5">Marketing</article>
      <article class="rounded-xl border border-slate-200 p-5">Gestion de projet</article>
      <article class="rounded-xl border border-slate-200 p-5">Secrétariat</article>
    </section>
  </main>

</body>
</html>
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Page de réservation responsive',
            'exercise_description' => <<<'TXT'
Réalise une page de réservation d’hôtel avec en-tête, grille de chambres et bandeau d’informations, entièrement responsive, avec des classes Tailwind.

Critères de réussite :
- La balise viewport est présente et les classes sans préfixe définissent la version mobile.
- L’en-tête passe de colonne à ligne à partir de « md » avec « flex-col md:flex-row ».
- Le menu complet est caché sur mobile avec « hidden md:flex » et un bouton « md:hidden » est affiché à sa place.
- La grille de chambres affiche une colonne sur mobile, deux à partir de « md » et trois à partir de « xl ».
- Le titre principal change de taille avec au moins un préfixe « md: » ou « lg: ».
TXT,
            'exercise_hint' => 'Écris d’abord toute la page sans aucun préfixe et vérifie qu’elle est lisible à 360 pixels. Ajoute ensuite les préfixes un par un et teste avec le simulateur d’appareils.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Hôtel Les Palmiers</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 text-slate-800">

  <header class="flex flex-col gap-3 bg-teal-900 p-4 text-white md:flex-row md:items-center md:justify-between md:px-8">
    <a href="/" class="text-xl font-bold">Hôtel Les Palmiers</a>
    <nav class="hidden gap-6 md:flex">
      <a href="#chambres">Chambres</a>
      <a href="#infos">Infos</a>
    </nav>
    <button type="button" class="rounded-lg bg-teal-700 px-4 py-2 md:hidden">Menu</button>
  </header>

  <main class="mx-auto max-w-6xl px-4 py-10">
    <h1 class="text-3xl font-bold md:text-5xl">Réservez votre séjour</h1>
    <p class="mt-3 text-slate-600 md:text-lg">À Grand-Bassam, face à la mer.</p>

    <section id="chambres" class="mt-8 grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-3">
      <article class="rounded-xl bg-white p-5 shadow">Chambre simple - 25 000 FCFA</article>
      <article class="rounded-xl bg-white p-5 shadow">Chambre double - 35 000 FCFA</article>
      <article class="rounded-xl bg-white p-5 shadow">Suite familiale - 60 000 FCFA</article>
    </section>

    <section id="infos" class="mt-8 flex flex-col gap-3 text-sm md:flex-row">
      <p class="rounded-lg bg-teal-100 px-4 py-2">Petit-déjeuner inclus</p>
      <p class="rounded-lg bg-teal-100 px-4 py-2">Wi-Fi gratuit</p>
      <p class="rounded-lg bg-teal-100 px-4 py-2">Paiement mobile money</p>
    </section>
  </main>

</body>
</html>
CODE,
        ],

        'Composants et états' => [
            'description' => 'Construis des boutons, cartes et champs de formulaire avec les variantes hover, focus et disabled, puis extrais-les en composants réutilisables.',
            'objective' => 'Créer un bouton avec variantes, une carte et un champ de formulaire avec états hover, focus-visible et disabled, puis factoriser les classes répétées dans un composant réutilisable.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une fois que tu sais poser des classes, un nouveau problème apparaît : les mêmes longues chaînes de classes se répètent sur dix boutons et vingt cartes. Changer la couleur d’un bouton demande de modifier tous les endroits. De plus, une interface réelle doit réagir : survol, focus au clavier, élément désactivé, état de chargement.

En entreprise, un projet Tailwind sain repose sur des composants UI réutilisables, pas sur des classes copiées partout. C’est ce qui sépare une intégration rapide mais jetable d’un design system maintenable.

## Les concepts clés

### Les variantes d’état

Tailwind gère les états grâce à des préfixes, comme pour le responsive. La classe « hover:bg-orange-600 » change le fond au survol. « focus-visible:ring-2 » ajoute un anneau de focus lors de la navigation clavier. « active:scale-95 » réduit légèrement l’élément pendant le clic. « disabled:opacity-50 » grise un contrôle désactivé, et « disabled:cursor-not-allowed » adapte le curseur. La classe « transition » ajoute une animation fluide aux changements.

### Un bouton accessible

Un bon bouton cumule plusieurs aspects : une apparence de base, un survol, un focus visible, un état désactivé. Les classes « focus:outline-none » seules sont dangereuses : sans alternative, le clavier n’a plus de repère. Associe-la à « focus-visible:ring-2 » et « focus-visible:ring-offset-2 ». Les champs de formulaire suivent la même logique avec « focus:border-orange-500 » et un anneau.

### Variantes de composant

Un composant comme un bouton existe en plusieurs variantes : principal, secondaire, danger. Chaque variante change les couleurs mais garde la même base. Dans un projet React ou Next.js, on crée un composant « Bouton » qui reçoit une propriété « variante » et assemble les classes correspondantes. Dans un projet Laravel avec Blade, on crée un composant Blade équivalent.

### Factoriser avec discernement

Il y a plusieurs niveaux de réutilisation. Le premier est de créer un composant du framework, ce qui est la solution recommandée. Le deuxième est la directive « @apply » pour regrouper des utilitaires dans une classe CSS, à réserver aux cas où un composant n’est pas possible, par exemple du HTML généré par un contenu externe. Les variantes conditionnelles basées sur l’état d’un parent se font avec « group » et « group-hover ».

## Exemple pas à pas

Le code_example présente un bouton avec deux variantes et un champ de formulaire. À l’étape un, le bouton principal rassemble les états hover, active, focus-visible et disabled. À l’étape deux, la variante secondaire garde la même structure avec des couleurs différentes. À l’étape trois, un bouton désactivé illustre « disabled: ». À l’étape quatre, le champ de saisie reçoit un style de focus. À l’étape cinq, une carte utilise « group » pour que le survol de la carte change l’apparence de sa flèche. Une note en commentaire montre comment extraire ces classes dans un composant.

## Erreurs fréquentes

- Supprimer le focus avec « focus:outline-none » seul : la navigation clavier est impossible. Ajoute « focus-visible:ring-2 ».
- Copier-coller la même chaîne de classes partout : les modifications deviennent pénibles. Crée un composant.
- Utiliser « @apply » pour tout : tu retrouves les problèmes du CSS classique. Réserve-le à des cas précis.
- Oublier l’état désactivé : l’utilisateur clique sans comprendre. Ajoute « disabled: » et l’attribut « disabled ».
- Compter sur « hover: » pour une information essentielle : elle est invisible sur mobile. Rends-la accessible autrement.

## Bonnes pratiques

- Un composant par élément d’interface répété, avec des variantes définies.
- Un focus visible sur tous les éléments interactifs.
- Les états hover, active, focus-visible et disabled couverts pour chaque bouton.
- Un petit ensemble de variantes plutôt qu’une explosion d’options.

## Auto-évaluation

- Quel préfixe applique un style au survol ?
- Pourquoi ne pas utiliser « focus:outline-none » tout seul ?
- Que fait « group-hover » ?
- Quand préférer un composant à « @apply » ?

## À retenir

- Les états s’expriment par des préfixes : « hover: », « focus-visible: », « disabled: ».
- Le focus clavier doit toujours rester visible.
- Les composants évitent de répéter de longues chaînes de classes.
- Les variantes partagent une base et changent les couleurs.
- « @apply » est une solution de dernier recours.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Composants Tailwind</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="space-y-8 bg-slate-100 p-8">

  <!-- Étape 1 : bouton principal avec tous ses états -->
  <button type="button"
          class="rounded-xl bg-orange-500 px-5 py-2.5 font-semibold text-white transition
                 hover:bg-orange-600 active:scale-95
                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-orange-700 focus-visible:ring-offset-2
                 disabled:cursor-not-allowed disabled:opacity-50">
    Payer par mobile money
  </button>

  <!-- Étape 2 : variante secondaire, même structure, couleurs différentes -->
  <button type="button"
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 font-semibold text-slate-700 transition
                 hover:bg-slate-50 active:scale-95
                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">
    Annuler
  </button>

  <!-- Étape 3 : état désactivé -->
  <button type="button" disabled
          class="rounded-xl bg-orange-500 px-5 py-2.5 font-semibold text-white
                 disabled:cursor-not-allowed disabled:opacity-50">
    Stock épuisé
  </button>

  <!-- Étape 4 : champ de formulaire avec focus -->
  <div>
    <label for="tel" class="mb-1 block text-sm font-medium text-slate-700">Numéro de téléphone</label>
    <input id="tel" type="tel"
           class="w-full max-w-xs rounded-lg border border-slate-300 px-3 py-2
                  focus:border-orange-500 focus:outline-none focus:ring-2 focus:ring-orange-200">
  </div>

  <!-- Étape 5 : carte avec group-hover -->
  <a href="#" class="group block max-w-xs rounded-2xl bg-white p-5 shadow transition hover:shadow-lg">
    <h2 class="font-bold">Abonnement mensuel</h2>
    <p class="mt-1 text-sm text-slate-600">Accès illimité aux cours.</p>
    <span class="mt-3 inline-block text-orange-600 transition group-hover:translate-x-1">Découvrir &rarr;</span>
  </a>

  <!-- Note : en React, extrais le bouton dans un composant Bouton qui reçoit
       une propriété variante et choisit les classes correspondantes. -->
</body>
</html>
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Bibliothèque de mini-composants',
            'exercise_description' => <<<'TXT'
Crée une page présentant trois composants d’interface pour une application de livraison : un bouton avec variantes, un champ de formulaire et une carte de commande.

Critères de réussite :
- Le bouton existe en variante principale et secondaire, chacune avec « hover: », « active: » et « focus-visible: ».
- Un bouton désactivé utilise l’attribut « disabled » et les classes « disabled:opacity-50 » et « disabled:cursor-not-allowed ».
- Le champ de formulaire a un « label » relié par « for » et « id » et un style « focus: » visible.
- La carte de commande utilise « group » et « group-hover: » sur au moins un élément enfant.
- Un commentaire explique comment extraire le bouton dans un composant réutilisable plutôt que de copier les classes.
TXT,
            'exercise_hint' => 'Teste chaque état à la souris puis au clavier avec la touche Tab. Si le focus n’est pas visible, c’est qu’il manque une classe « focus-visible: ».',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Composants livraison</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="space-y-8 bg-slate-100 p-8">

  <button type="button"
          class="rounded-xl bg-emerald-600 px-5 py-2.5 font-semibold text-white transition
                 hover:bg-emerald-700 active:scale-95
                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-emerald-800 focus-visible:ring-offset-2
                 disabled:cursor-not-allowed disabled:opacity-50">
    Commander
  </button>

  <button type="button"
          class="rounded-xl border border-slate-300 bg-white px-5 py-2.5 font-semibold text-slate-700 transition
                 hover:bg-slate-50 active:scale-95
                 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-slate-500 focus-visible:ring-offset-2">
    Voir le menu
  </button>

  <button type="button" disabled
          class="rounded-xl bg-emerald-600 px-5 py-2.5 font-semibold text-white
                 disabled:cursor-not-allowed disabled:opacity-50">
    Restaurant fermé
  </button>

  <div>
    <label for="adresse" class="mb-1 block text-sm font-medium text-slate-700">Adresse de livraison</label>
    <input id="adresse" type="text"
           class="w-full max-w-sm rounded-lg border border-slate-300 px-3 py-2
                  focus:border-emerald-500 focus:outline-none focus:ring-2 focus:ring-emerald-200">
  </div>

  <a href="#" class="group block max-w-xs rounded-2xl bg-white p-5 shadow transition hover:shadow-lg">
    <h2 class="font-bold">Commande n° 1042</h2>
    <p class="mt-1 text-sm text-slate-600">Poulet braisé, attiéké - 4 500 FCFA</p>
    <span class="mt-3 inline-block text-emerald-700 transition group-hover:translate-x-1">Suivre &rarr;</span>
  </a>

  <!-- Pour éviter de copier les classes : crée un composant Bouton qui reçoit
       une propriété variante (principal ou secondaire) et applique la chaîne de classes
       correspondante, puis réutilise ce composant partout. -->
</body>
</html>
CODE,
        ],

        'Dark mode et tokens' => [
            'description' => 'Configure le mode sombre avec la variante dark, des tokens de couleur et de typographie centralisés, pour créer une identité visuelle évolutive.',
            'objective' => 'Mettre en place un thème clair et sombre avec la variante dark et des tokens de couleur de marque personnalisés, et appliquer ce thème à une carte et à un en-tête.',
            'content' => <<<'MD'
## Pourquoi cette notion

Beaucoup d’utilisateurs préfèrent le mode sombre, surtout le soir ou sur des écrans qui économisent la batterie. Les clients demandent donc de plus en plus souvent cette fonctionnalité. Par ailleurs, une marque a une identité : ses couleurs, ses polices, ses arrondis. Si tu écris ces valeurs en dur dans chaque classe, le moindre changement de charte graphique devient un chantier.

Les tokens de design répondent à ce besoin : des valeurs nommées, centralisées, que tous les composants réutilisent. En agence, c’est ce qui permet de livrer à un client un site qui reflète sa marque et que l’on peut faire évoluer sans tout reprendre.

## Les concepts clés

### La variante dark

Tailwind propose la variante « dark: ». Une classe comme « dark:bg-slate-900 » ne s’applique que lorsque le mode sombre est actif. Tu écris d’abord le style clair, puis ses équivalents sombres : « bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 ». Par défaut, le mode sombre suit la préférence du système de l’utilisateur.

### Choisir la stratégie

Deux stratégies existent. La première suit automatiquement le réglage du système. La seconde utilise un mécanisme manuel, par exemple une classe « dark » placée sur l’élément « html » ou un attribut de données, qui permet d’ajouter un bouton de bascule et de mémoriser le choix de l’utilisateur. La façon exacte de configurer cette stratégie dépend de la version de Tailwind utilisée, donc consulte la documentation de ton projet.

### Les tokens de design

Dans la configuration de Tailwind, tu étends le thème avec tes propres valeurs : une couleur de marque, une police, un rayon par défaut. Selon la version du projet, cela se fait dans le fichier de configuration JavaScript ou directement dans le CSS avec la directive « @theme ». Une fois déclarée, la couleur de marque devient utilisable comme « bg-marque-500 » ou « text-marque-600 », et un changement de valeur met à jour tout le site.

### Tokens sémantiques

Nomme les tokens selon leur rôle plutôt que leur apparence : « fond », « surface », « texte » et « accent » sont plus durables que « gris-clair ». Avec des variables CSS, les valeurs de « fond » et « texte » changent selon le thème, et les composants n’ont plus besoin de répéter « dark: » partout.

## Exemple pas à pas

Le code_example affiche une carte et un en-tête compatibles avec les deux thèmes. À l’étape un, la configuration déclare une couleur de marque et choisit la stratégie par classe. À l’étape deux, le « body » combine un style clair et son équivalent « dark: ». À l’étape trois, l’en-tête applique le même principe aux fonds et aux bordures. À l’étape quatre, la carte utilise la couleur de marque et ses équivalents sombres. À l’étape cinq, un script ajoute ou retire la classe « dark » sur l’élément « html » et mémorise le choix dans le stockage du navigateur, avec protection en cas d’erreur.

## Erreurs fréquentes

- Oublier les équivalents « dark: » pour le texte : le texte sombre reste sombre sur fond sombre et devient illisible. Teste chaque composant dans les deux thèmes.
- Utiliser du noir pur et du blanc pur : le contraste fatigue. Choisis des gris foncés et des blancs adoucis.
- Écrire les couleurs de marque en dur avec des valeurs arbitraires : le changement de charte devient pénible. Déclare des tokens.
- Nommer les tokens selon l’apparence comme « bleu-clair » : ils perdent leur sens quand la marque change. Nomme-les selon le rôle.
- Ne pas prévoir de mémorisation du thème choisi : l’utilisateur le perd à chaque visite. Enregistre le choix et protège l’accès au stockage.

## Bonnes pratiques

- Définis les tokens de marque dès le début du projet.
- Teste chaque écran en thème clair et sombre.
- Vérifie le contraste du texte dans les deux thèmes.
- Prévois la bascule manuelle et la mémorisation du choix.

## Auto-évaluation

- Comment applique-t-on un fond différent en mode sombre ?
- Quelle différence entre suivre le système et une bascule manuelle ?
- Pourquoi nommer un token « surface » plutôt que « gris-clair » ?
- Où déclare-t-on une couleur de marque dans un projet Tailwind ?

## À retenir

- La variante « dark: » applique des styles en mode sombre.
- Les tokens centralisent couleurs, polices et arrondis.
- Les noms sémantiques résistent aux changements de charte.
- La stratégie de bascule dépend de la configuration du projet.
- Chaque thème se teste séparément.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Thème clair et sombre</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    // Étape 1 : tokens de marque (en projet réel : configuration Tailwind)
    tailwind.config = {
      darkMode: 'class', // thème sombre piloté par la classe "dark" sur html
      theme: { extend: { colors: { marque: { 500: '#ea580c', 600: '#c2410c' } } } }
    };
  </script>
</head>
<!-- Étape 2 : styles clairs puis équivalents dark: -->
<body class="bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  <!-- Étape 3 : en-tête adapté aux deux thèmes -->
  <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4
                 dark:border-slate-800 dark:bg-slate-900">
    <span class="text-lg font-bold text-marque-600 dark:text-marque-500">Boutique Soleil</span>
    <button id="bascule" type="button"
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm hover:bg-slate-100
                   dark:border-slate-700 dark:hover:bg-slate-800">Changer de thème</button>
  </header>
  <main class="mx-auto max-w-md p-6">
    <!-- Étape 4 : carte avec token de marque -->
    <article class="rounded-2xl bg-white p-6 shadow dark:bg-slate-900">
      <h1 class="text-xl font-bold">Panier du jour</h1>
      <p class="mt-2 text-slate-600 dark:text-slate-300">3 articles pour 18 500 FCFA.</p>
      <button type="button" class="mt-4 rounded-xl bg-marque-500 px-5 py-2 font-semibold text-white hover:bg-marque-600">
        Valider</button>
    </article>
  </main>
  <script>
    // Étape 5 : bascule + mémorisation protégée par try/catch
    var racine = document.documentElement;
    try { if (localStorage.getItem('theme') === 'dark') racine.classList.add('dark'); } catch (e) {}
    document.getElementById('bascule').addEventListener('click', function () {
      var sombre = racine.classList.toggle('dark');
      try { localStorage.setItem('theme', sombre ? 'dark' : 'light'); } catch (e) {}
    });
  </script>
</body>
</html>
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Thème de marque clair et sombre',
            'exercise_description' => <<<'TXT'
Crée une page d’application (en-tête, carte d’information, bouton) avec un thème de marque personnalisé et un mode sombre commutable.

Critères de réussite :
- Une couleur de marque personnalisée est déclarée dans le thème et utilisée au moins dans deux éléments.
- Le fond, le texte, les bordures et la carte ont chacun un équivalent « dark: ».
- Un bouton de bascule ajoute ou retire la classe « dark » sur l’élément « html ».
- Le choix de thème est mémorisé dans « localStorage » avec une protection « try/catch ».
- Le texte reste lisible dans les deux thèmes et aucun noir pur n’est utilisé.
TXT,
            'exercise_hint' => 'Déclare d’abord la couleur de marque, puis habille la page en clair avant d’ajouter les classes « dark: ». Active ensuite la bascule et vérifie chaque élément dans les deux thèmes.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Suivi des ventes</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          colors: {
            marque: { 500: '#0d9488', 600: '#0f766e' }
          }
        }
      }
    };
  </script>
</head>
<body class="bg-slate-50 text-slate-900 dark:bg-slate-900 dark:text-slate-100">

  <header class="flex items-center justify-between border-b border-slate-200 bg-white px-6 py-4
                 dark:border-slate-700 dark:bg-slate-800">
    <span class="text-lg font-bold text-marque-600 dark:text-marque-500">VenteSuivi</span>
    <button id="bascule" type="button"
            class="rounded-lg border border-slate-300 px-3 py-1.5 text-sm
                   hover:bg-slate-100 dark:border-slate-600 dark:hover:bg-slate-700">
      Changer de thème
    </button>
  </header>

  <main class="mx-auto max-w-md p-6">
    <article class="rounded-2xl border border-slate-200 bg-white p-6 shadow
                    dark:border-slate-700 dark:bg-slate-800">
      <h1 class="text-xl font-bold">Ventes du jour</h1>
      <p class="mt-2 text-slate-600 dark:text-slate-300">42 commandes, 215 000 FCFA.</p>
      <button type="button"
              class="mt-4 rounded-xl bg-marque-500 px-5 py-2 font-semibold text-white hover:bg-marque-600">
        Voir le détail
      </button>
    </article>
  </main>

  <script>
    var racine = document.documentElement;
    try {
      if (localStorage.getItem('theme') === 'dark') racine.classList.add('dark');
    } catch (e) { /* stockage indisponible */ }

    document.getElementById('bascule').addEventListener('click', function () {
      var sombre = racine.classList.toggle('dark');
      try { localStorage.setItem('theme', sombre ? 'dark' : 'light'); } catch (e) {}
    });
  </script>
</body>
</html>
CODE,
        ],

        'Architecture Tailwind' => [
            'description' => 'Garde un projet Tailwind maintenable avec des composants UI, des variantes contrôlées, des tokens et des règles d’équipe plutôt qu’une accumulation de classes arbitraires.',
            'objective' => 'Refactorer une interface répétitive en composants UI avec variantes, fusionner proprement les classes, centraliser les valeurs en tokens et appliquer des règles de lisibilité du code.',
            'content' => <<<'MD'
## Pourquoi cette notion

Tailwind est rapide au démarrage, mais un projet qui dure des mois peut dégénérer : classes copiées partout, boutons légèrement différents d’une page à l’autre, valeurs arbitraires qui se multiplient. Les nouveaux développeurs ne savent plus quelle version est la bonne. L’architecture Tailwind regroupe les pratiques qui évitent cette dérive.

En agence ou en équipe produit, cette discipline est ce qui permet à un site de rester cohérent et de se modifier vite. Un client qui change de couleur de marque doit pouvoir être servi en quelques minutes, pas en quelques jours.

## Les concepts clés

### Composants plutôt que copier-coller

Quand tu retrouves la même chaîne de classes trois fois, extrais-la dans un composant : un composant React ou Next.js, un composant Blade dans Laravel, ou un composant Svelte. Le composant porte la structure, les classes et le comportement, et le reste du projet l’utilise comme une brique. Une seule modification se propage partout.

### Variantes contrôlées

Un composant de bouton propose un petit ensemble de variantes : « principal », « secondaire », « danger », et de tailles : « sm », « md », « lg ». Des bibliothèques comme class-variance-authority permettent de décrire proprement ces variantes, mais tu peux commencer avec un simple objet qui associe un nom de variante à une chaîne de classes. L’essentiel est que les classes complètes soient écrites en toutes lettres dans le code pour être détectées par Tailwind.

### Fusion de classes

Quand un composant accepte une propriété « className » pour être personnalisé, deux classes peuvent entrer en conflit, comme « p-4 » et « p-2 ». Les deux classes existent dans le CSS et c’est l’ordre de la feuille, pas l’ordre dans l’attribut, qui décide. Un utilitaire comme tailwind-merge résout ces conflits, et clsx assemble des classes conditionnelles. Ces outils sont courants mais ne sont pas obligatoires.

### Tokens, règles et outils

Les couleurs, polices et rayons passent par la configuration plutôt que par des valeurs arbitraires. L’équipe se met d’accord sur des règles : ordre des classes, usage des crochets, nommage des composants. Le plugin Prettier officiel trie automatiquement les classes, ce qui rend les relectures plus simples. Pour les cas vraiment répétitifs et hors framework, la directive « @apply » existe, mais elle reste l’exception.

## Exemple pas à pas

Le code_example refactore une interface avec un composant de bouton à variantes. À l’étape un, un objet « variantes » associe chaque nom à ses classes complètes. À l’étape deux, un objet « tailles » fait de même. À l’étape trois, la fonction « classes » assemble base, variante, taille et classes additionnelles. À l’étape quatre, le composant « Bouton » applique la fonction et transmet ses autres propriétés au bouton natif. À l’étape cinq, la page utilise le composant sans dupliquer de classes. L’exemple est en JavaScript pur pour s’exécuter sans dépendance.

## Erreurs fréquentes

- Construire des classes par concaténation, comme « bg- » suivi d’une variable : Tailwind ne les détecte pas. Associe chaque nom à une chaîne complète.
- Copier le même bouton avec de légères variations : l’interface devient incohérente. Crée un composant avec des variantes.
- Autoriser n’importe quelle classe à écraser un composant : des conflits apparaissent. Fusionne avec tailwind-merge ou limite les variantes.
- Multiplier les valeurs arbitraires entre crochets : les tokens perdent leur sens. Ajoute le token au thème.
- Abuser de « @apply » : tu reviens à un CSS classique difficile à maintenir. Préfère un composant.

## Bonnes pratiques

- Extrais un composant dès la troisième répétition d’un motif.
- Limite les variantes de chaque composant à un petit ensemble documenté.
- Centralise couleurs, polices et arrondis dans les tokens du thème.
- Utilise le plugin Prettier de Tailwind pour garder un ordre de classes cohérent.
- Documente quelques règles d’équipe dans le dépôt.

## Auto-évaluation

- Pourquoi les noms de classes doivent-ils être écrits en entier dans le code ?
- Quand extraire un composant plutôt que copier des classes ?
- Quel problème résout tailwind-merge ?
- Pourquoi limiter les valeurs arbitraires entre crochets ?

## À retenir

- Les composants sont la principale technique de factorisation avec Tailwind.
- Les variantes doivent être peu nombreuses et décrites avec des classes complètes.
- Les conflits de classes se règlent par fusion contrôlée.
- Les tokens du thème remplacent les valeurs arbitraires.
- Des règles d’équipe et un outil de tri gardent le code lisible.
MD,
            'code_example' => <<<'CODE'
// Composant Bouton à variantes, en JavaScript pur (exécutable avec Node).
// Même idée dans React, Blade ou Svelte : un composant, des variantes contrôlées.

// Étape 1 : variantes de couleur, classes écrites EN ENTIER
const variantes = {
  principal:  'bg-orange-500 text-white hover:bg-orange-600',
  secondaire: 'border border-slate-300 bg-white text-slate-700 hover:bg-slate-50',
  danger:     'bg-red-600 text-white hover:bg-red-700',
};

// Étape 2 : tailles disponibles
const tailles = {
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-5 py-2.5 text-base',
  lg: 'px-7 py-3 text-lg',
};

// Base commune à tous les boutons : focus clavier et état désactivé inclus
const base =
  'rounded-xl font-semibold transition focus-visible:outline-none ' +
  'focus-visible:ring-2 focus-visible:ring-offset-2 ' +
  'disabled:cursor-not-allowed disabled:opacity-50';

// Étape 3 : assemblage des classes (équivalent simple de clsx)
function classes(variante = 'principal', taille = 'md', extra = '') {
  if (!variantes[variante]) throw new Error('Variante inconnue : ' + variante);
  if (!tailles[taille]) throw new Error('Taille inconnue : ' + taille);
  return [base, variantes[variante], tailles[taille], extra].filter(Boolean).join(' ');
}

// Étape 4 : composant qui produit le HTML du bouton
function Bouton({ texte, variante, taille, className, disabled = false }) {
  const attr = disabled ? ' disabled' : '';
  return '<button type="button" class="' + classes(variante, taille, className) + '"' + attr + '>' + texte + '</button>';
}

// Étape 5 : utilisation sans dupliquer de classes
console.log(Bouton({ texte: 'Payer 8 000 FCFA' }));
console.log(Bouton({ texte: 'Annuler', variante: 'secondaire', taille: 'sm' }));
console.log(Bouton({ texte: 'Supprimer', variante: 'danger', disabled: true }));

// Une variante inconnue est refusée explicitement
try { Bouton({ texte: 'Test', variante: 'rose' }); }
catch (e) { console.log('Erreur attendue : ' + e.message); }
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Composant Carte et Badge à variantes',
            'exercise_description' => <<<'TXT'
Écris en JavaScript pur un petit module qui génère le HTML Tailwind d’un composant Badge et d’un composant Carte, avec des variantes contrôlées, pour un tableau de suivi de commandes.

Critères de réussite :
- Un objet « variantesBadge » associe « succes », « attente » et « erreur » à des chaînes de classes complètes, sans concaténation.
- Le composant Badge lance une erreur claire si la variante est inconnue.
- Le composant Carte accepte un titre, un contenu et une variante « normale » ou « alerte ».
- Les classes communes sont définies une seule fois dans des constantes de base.
- Le script s’exécute avec Node et affiche au moins trois composants différents dans la console.
TXT,
            'exercise_hint' => 'Écris les objets de variantes en premier, avec chaque chaîne de classes complète. Écris ensuite une petite fonction qui assemble base et variante et refuse les noms inconnus.',
            'exercise_solution' => <<<'CODE'
// Module de composants Tailwind : Badge et Carte (JavaScript pur, exécutable avec Node)

const baseBadge = 'inline-block rounded-full px-3 py-1 text-xs font-semibold';
const variantesBadge = {
  succes:  'bg-green-100 text-green-800',
  attente: 'bg-amber-100 text-amber-800',
  erreur:  'bg-red-100 text-red-800',
};

const baseCarte = 'rounded-2xl border p-5 shadow-sm';
const variantesCarte = {
  normale: 'border-slate-200 bg-white',
  alerte:  'border-red-300 bg-red-50',
};

function Badge({ texte, variante = 'attente' }) {
  if (!variantesBadge[variante]) {
    throw new Error('Variante de badge inconnue : ' + variante);
  }
  return '<span class="' + baseBadge + ' ' + variantesBadge[variante] + '">' + texte + '</span>';
}

function Carte({ titre, contenu, variante = 'normale' }) {
  if (!variantesCarte[variante]) {
    throw new Error('Variante de carte inconnue : ' + variante);
  }
  return (
    '<article class="' + baseCarte + ' ' + variantesCarte[variante] + '">' +
    '<h2 class="font-bold text-slate-900">' + titre + '</h2>' +
    '<p class="mt-2 text-sm text-slate-600">' + contenu + '</p>' +
    '</article>'
  );
}

console.log(Badge({ texte: 'Livrée', variante: 'succes' }));
console.log(Badge({ texte: 'En attente' }));
console.log(Carte({ titre: 'Commande n° 1042', contenu: 'Livraison prévue à 15h.' }));
console.log(Carte({ titre: 'Paiement refusé', contenu: 'Vérifie le solde.', variante: 'alerte' }));

try { Badge({ texte: 'Test', variante: 'rose' }); }
catch (e) { console.log('Erreur attendue : ' + e.message); }
CODE,
        ],

        'Projet final Tailwind' => [
            'description' => 'Réunis layout, responsive, composants, états et thème dans un tableau de bord SaaS sombre avec barre latérale, cartes statistiques, tableau, formulaire et navigation mobile.',
            'objective' => 'Livrer un tableau de bord SaaS responsive avec thème sombre, barre latérale en bureau et navigation basse en mobile, cartes statistiques, tableau scrollable et formulaire, entièrement en classes Tailwind avec composants factorisés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Le tableau de bord est le produit le plus courant des agences : suivi de ventes, gestion de stock, administration d’une école, suivi de livraisons. Il demande de combiner presque tout ce que tu as appris : une structure à plusieurs zones, des composants répétés, des états, un comportement différent sur mobile et sur bureau, et un thème cohérent.

Ce projet final te donne une pièce de portfolio concrète. Un tableau de bord propre, responsive et accessible démontre immédiatement à un client ou à un recruteur que tu sais construire une interface sérieuse avec Tailwind.

## Les concepts clés

### Découper avant de coder

Dessine d’abord les zones. Sur bureau : une barre latérale à gauche, une zone principale à droite avec une ligne de cartes statistiques, un tableau de données et un formulaire. Sur mobile : la barre latérale devient une navigation fixée en bas, les cartes s’empilent et le tableau défile horizontalement. Chaque élément répété, comme la carte statistique ou le bouton, devient un composant.

### Le layout principal

Un conteneur « min-h-screen » avec « md:flex » place la barre latérale et le contenu côte à côte. La barre latérale utilise « hidden md:flex md:w-60 md:flex-col » et la navigation mobile utilise « fixed bottom-0 inset-x-0 md:hidden ». Le contenu principal reçoit un padding de bas de page sur mobile pour ne pas passer sous la navigation fixe.

### Thème sombre et tokens

Un tableau de bord sombre utilise des fonds gris très foncés comme « bg-slate-950 » et « bg-slate-900 » pour les surfaces, des bordures discrètes comme « border-slate-800 » et un accent lumineux. Les composants réutilisent ces valeurs, idéalement via des tokens du thème. Vérifie le contraste du texte secondaire.

### Tableaux, formulaires et accessibilité

Un tableau large s’enveloppe dans un conteneur « overflow-x-auto ». Les champs ont un « label » relié, un focus visible et une taille tactile confortable. La navigation utilise des liens natifs avec « aria-label », et l’élément actif est signalé par un style et « aria-current="page" ». Tout se teste au clavier.

## Exemple pas à pas

Le code_example construit un tableau de bord de suivi de ventes. À l’étape un, le conteneur global et la barre latérale de bureau sont posés avec « md:flex » et « hidden md:flex ». À l’étape deux, la navigation basse mobile utilise « fixed » et « md:hidden ». À l’étape trois, quatre cartes statistiques s’affichent dans une grille adaptable. À l’étape quatre, le tableau est enveloppé dans « overflow-x-auto ». À l’étape cinq, le formulaire d’ajout de vente combine labels, champs et bouton avec états de focus et de survol. Un petit script JavaScript génère les lignes de tableau pour éviter de répéter le HTML.

## Erreurs fréquentes

- Oublier le padding de bas de page sur mobile : la navigation fixe cache le contenu. Ajoute « pb-20 » au contenu pour mobile.
- Laisser le tableau déborder : un défilement horizontal apparaît sur toute la page. Enveloppe-le dans « overflow-x-auto ».
- Mettre un contenu essentiel uniquement dans la barre latérale cachée sur mobile : l’utilisateur ne peut plus naviguer. Fournis la navigation basse.
- Utiliser du noir pur et des couleurs saturées sur fond sombre : le contraste fatigue. Choisis des gris foncés et un accent doux.
- Copier les cartes statistiques à la main : la modification devient pénible. Génère-les à partir d’un tableau de données ou crée un composant.

## Bonnes pratiques

- Découpe l’écran en zones puis en composants avant de coder.
- Écris d’abord la version mobile, puis ajoute « md: » et « xl: ».
- Vérifie la page à 360 pixels de large et au clavier.
- Utilise des tokens et des composants pour tout ce qui se répète.

## Auto-évaluation

- Comment faire apparaître la barre latérale seulement à partir de 768 pixels ?
- Comment empêcher la navigation fixe de cacher le bas du contenu ?
- Comment éviter qu’un tableau large casse la page sur mobile ?
- Quels éléments de ton projet sont devenus des composants ?

## À retenir

- Un projet commence par le découpage en zones et en composants.
- Barre latérale en bureau, navigation basse en mobile, avec « hidden » et « md: ».
- Les tableaux larges se placent dans un conteneur « overflow-x-auto ».
- Le thème sombre repose sur des gris foncés et des tokens.
- Accessibilité et tests au clavier font partie du livrable.
MD,
            'code_example' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord ventes</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100">
  <div class="min-h-screen md:flex">
    <!-- Étape 1 : barre latérale visible à partir de md -->
    <aside class="hidden border-r border-slate-800 bg-slate-900 p-4 md:flex md:w-60 md:flex-col md:gap-2">
      <span class="mb-4 text-lg font-bold text-amber-400">VenteSuivi</span>
      <a href="#" aria-current="page" class="rounded-lg bg-slate-800 px-3 py-2">Accueil</a>
      <a href="#" class="rounded-lg px-3 py-2 hover:bg-slate-800">Ventes</a>
    </aside>
    <!-- Étape 2 : navigation basse sur mobile uniquement -->
    <nav aria-label="Navigation mobile"
         class="fixed inset-x-0 bottom-0 flex justify-around border-t border-slate-800 bg-slate-900 md:hidden">
      <a href="#" class="px-4 py-4">Accueil</a><a href="#" class="px-4 py-4">Ventes</a>
    </nav>
    <main class="min-w-0 flex-1 space-y-6 p-4 pb-20 md:p-8 md:pb-8">
      <h1 class="text-2xl font-bold md:text-3xl">Tableau de bord</h1>
      <!-- Étape 3 : cartes générées depuis des données -->
      <section id="stats" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"></section>
      <!-- Étape 4 : tableau scrollable horizontalement -->
      <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full min-w-[480px] text-left text-sm">
          <thead class="border-b border-slate-800 text-slate-400">
            <tr><th class="p-3">Client</th><th class="p-3">Montant</th></tr>
          </thead>
          <tbody id="lignes"></tbody>
        </table>
      </div>
      <!-- Étape 5 : formulaire avec états -->
      <form class="max-w-sm space-y-3 rounded-2xl border border-slate-800 bg-slate-900 p-5">
        <label for="client" class="block text-sm text-slate-300">Client</label>
        <input id="client" type="text" class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2
               focus:border-amber-400 focus:outline-none focus:ring-2 focus:ring-amber-400/30">
        <button type="submit" class="rounded-xl bg-amber-400 px-5 py-2.5 font-semibold text-slate-900
                hover:bg-amber-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-amber-200">
          Ajouter la vente</button>
      </form>
    </main>
  </div>
  <script>
    // Données d'exemple : on génère le HTML au lieu de le répéter
    var stats = [['Ventes du jour', '215 000 FCFA'], ['Commandes', '42'], ['Clients', '18'], ['Stock bas', '5']];
    var ventes = [['Awa Traoré', '12 500 FCFA'], ['Yao Koffi', '8 000 FCFA']];
    document.getElementById('stats').innerHTML = stats.map(function (s) {
      return '<article class="rounded-2xl border border-slate-800 bg-slate-900 p-5"><p class="text-sm text-slate-400">' +
             s[0] + '</p><p class="mt-1 text-2xl font-bold">' + s[1] + '</p></article>';
    }).join('');
    document.getElementById('lignes').innerHTML = ventes.map(function (v) {
      return '<tr class="border-b border-slate-800"><td class="p-3">' + v[0] + '</td><td class="p-3">' + v[1] + '</td></tr>';
    }).join('');
  </script>
</body>
</html>
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : tableau de bord SaaS sombre',
            'exercise_description' => <<<'TXT'
Réalise un tableau de bord SaaS sombre pour une activité de ton choix (boutique, école, livraison, hôtel), en HTML et classes Tailwind, avec un peu de JavaScript pour générer les données.

Livrables :
- Un fichier « index.html » utilisant Tailwind.
- Un court fichier de notes qui liste les composants extraits et les breakpoints choisis.

Critères de réussite :
- La barre latérale est visible à partir de « md » avec « hidden md:flex », et une navigation fixée en bas s’affiche sur mobile avec « md:hidden ».
- Au moins quatre cartes statistiques utilisent une grille responsive avec « sm: » et « xl: » et sont générées à partir d’un tableau de données.
- Un tableau d’au moins trois lignes est placé dans un conteneur « overflow-x-auto », sans défilement horizontal global à 360 pixels.
- Un formulaire possède au moins trois champs avec « label » relié, « focus: » visible, et un bouton avec « hover: » et « focus-visible: ».
- Le thème est sombre avec des gris foncés, sans noir pur, et le contenu principal garde un padding bas sur mobile pour ne pas passer sous la navigation.
TXT,
            'exercise_hint' => 'Construis d’abord la version mobile à 360 pixels avec la navigation basse, puis ajoute les préfixes « md: ». Génère les cartes et les lignes du tableau depuis des tableaux JavaScript pour ne pas copier le HTML.',
            'exercise_solution' => <<<'CODE'
<!doctype html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Tableau de bord école</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-950 text-slate-100">

  <div class="min-h-screen md:flex">

    <aside class="hidden border-r border-slate-800 bg-slate-900 p-4 md:flex md:w-60 md:flex-col md:gap-2">
      <span class="mb-4 text-lg font-bold text-sky-400">EcoleGestion</span>
      <a href="#" aria-current="page" class="rounded-lg bg-slate-800 px-3 py-2">Accueil</a>
      <a href="#" class="rounded-lg px-3 py-2 hover:bg-slate-800">Élèves</a>
      <a href="#" class="rounded-lg px-3 py-2 hover:bg-slate-800">Notes</a>
    </aside>

    <nav aria-label="Navigation mobile"
         class="fixed inset-x-0 bottom-0 flex justify-around border-t border-slate-800 bg-slate-900 md:hidden">
      <a href="#" class="px-4 py-4">Accueil</a>
      <a href="#" class="px-4 py-4">Élèves</a>
      <a href="#" class="px-4 py-4">Notes</a>
    </nav>

    <main class="min-w-0 flex-1 space-y-6 p-4 pb-20 md:p-8 md:pb-8">
      <h1 class="text-2xl font-bold md:text-3xl">Tableau de bord</h1>

      <section id="stats" class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4"></section>

      <div class="overflow-x-auto rounded-2xl border border-slate-800 bg-slate-900">
        <table class="w-full min-w-[480px] text-left text-sm">
          <thead class="border-b border-slate-800 text-slate-400">
            <tr><th class="p-3">Classe</th><th class="p-3">Effectif</th><th class="p-3">Moyenne</th></tr>
          </thead>
          <tbody id="lignes"></tbody>
        </table>
      </div>

      <form class="max-w-sm space-y-3 rounded-2xl border border-slate-800 bg-slate-900 p-5">
        <div>
          <label for="nom" class="mb-1 block text-sm text-slate-300">Nom de l'élève</label>
          <input id="nom" type="text"
                 class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2
                        focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400/30">
        </div>
        <div>
          <label for="classe" class="mb-1 block text-sm text-slate-300">Classe</label>
          <input id="classe" type="text"
                 class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2
                        focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400/30">
        </div>
        <div>
          <label for="moyenne" class="mb-1 block text-sm text-slate-300">Moyenne</label>
          <input id="moyenne" type="number" min="0" max="20"
                 class="w-full rounded-lg border border-slate-700 bg-slate-950 px-3 py-2
                        focus:border-sky-400 focus:outline-none focus:ring-2 focus:ring-sky-400/30">
        </div>
        <button type="submit"
                class="rounded-xl bg-sky-400 px-5 py-2.5 font-semibold text-slate-900 transition
                       hover:bg-sky-300 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-sky-200">
          Enregistrer
        </button>
      </form>
    </main>
  </div>

  <script>
    var stats = [['Élèves', '320'], ['Classes', '12'], ['Moyenne générale', '12,8'], ['Absences', '7']];
    var classes = [['6e A', '38', '12,4'], ['5e B', '35', '13,1'], ['3e C', '30', '11,9']];

    document.getElementById('stats').innerHTML = stats.map(function (s) {
      return '<article class="rounded-2xl border border-slate-800 bg-slate-900 p-5">' +
             '<p class="text-sm text-slate-400">' + s[0] + '</p>' +
             '<p class="mt-1 text-2xl font-bold">' + s[1] + '</p></article>';
    }).join('');

    document.getElementById('lignes').innerHTML = classes.map(function (c) {
      return '<tr class="border-b border-slate-800"><td class="p-3">' + c[0] +
             '</td><td class="p-3">' + c[1] + '</td><td class="p-3">' + c[2] + '</td></tr>';
    }).join('');
  </script>
</body>
</html>
CODE,
        ],
    ],
];
