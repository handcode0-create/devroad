<?php

return [
    'lessons' => [
        'Comprendre la cascade' => [
            'description' => 'Comprends comment le navigateur choisit la règle CSS gagnante grâce à l’origine, la spécificité et l’ordre, et comment le box model détermine la taille réelle des éléments.',
            'objective' => 'Prédire sans l’essayer quelle règle CSS s’applique dans un cas de conflit, et dimensionner une carte avec padding, border et margin en utilisant box-sizing: border-box.',
            'content' => <<<'MD'
## Pourquoi cette notion

Tu écris une règle CSS, tu enregistres, et rien ne change. Ou pire : le style s’applique sur une page et pas sur une autre. Dans neuf cas sur dix, la cause est la cascade : plusieurs règles visent le même élément et le navigateur en choisit une seule. Les développeurs qui ne comprennent pas ce mécanisme ajoutent des « !important » partout, et leur code devient impossible à maintenir.

En entreprise, tu reprendras souvent la feuille de style d’un autre. Savoir expliquer pourquoi une règle gagne te permet de corriger un bug en deux minutes au lieu de deux heures. Le box model, lui, explique pourquoi une carte « de 300 pixels » déborde de son conteneur.

## Les concepts clés

### Les sélecteurs et l’héritage

Un sélecteur désigne les éléments à styliser : un élément comme « p », une classe comme « .carte », un identifiant comme « #entete », ou un attribut comme « input[type="email"] ». Certaines propriétés sont héritées par les enfants, comme « color » et « font-family ». D’autres ne le sont pas, comme « border » ou « margin ». Tu peux forcer l’héritage avec la valeur « inherit ».

### La cascade et la spécificité

Quand deux règles s’appliquent au même élément pour la même propriété, le navigateur compare dans l’ordre : l’importance (« !important »), puis la spécificité, puis l’ordre d’apparition. La spécificité se calcule avec trois colonnes : les identifiants, puis les classes, attributs et pseudo-classes, puis les éléments. Un identifiant pèse plus que n’importe quel nombre de classes. À spécificité égale, la dernière règle écrite gagne.

### Le box model

Chaque élément est une boîte composée, de l’intérieur vers l’extérieur, du contenu, du « padding » (espace intérieur), du « border » (bordure) et du « margin » (espace extérieur). Par défaut, « width » ne mesure que le contenu : un bloc de 300 pixels avec 20 pixels de padding et 2 de bordure occupe en réalité 344 pixels.

## Exemple pas à pas

Le code_example stylise une carte de produit pour une boutique en ligne. À l’étape un, une règle de réinitialisation applique « box-sizing: border-box » à tous les éléments. À l’étape deux, la classe « .carte » fixe une largeur, un padding et une bordure. À l’étape trois, la règle « .carte p » colore le texte, et la classe « .carte .prix » plus spécifique l’emporte pour le prix. À l’étape quatre, une règle située plus bas redéfinit la couleur de fond : à spécificité égale, la dernière gagne. Les commentaires du code indiquent la spécificité de chaque sélecteur sous la forme de trois nombres.

## Erreurs fréquentes

- Utiliser « !important » pour régler un conflit : il crée une guerre de priorités. Cherche plutôt la règle gagnante dans les outils de développement et ajuste la spécificité.
- Utiliser des identifiants pour le style : leur spécificité élevée complique les surcharges. Préfère les classes.
- Oublier « box-sizing: border-box » : les blocs dépassent leur conteneur. Ajoute la règle en début de feuille.
- Écrire des sélecteurs trop longs comme « div.page ul li a » : ils sont rigides et lourds. Donne une classe à l’élément ciblé.
- Penser que l’ordre n’a pas d’importance : à spécificité égale, la dernière règle gagne. Range tes règles de façon cohérente.

## Bonnes pratiques

- Style avec des classes, garde les identifiants pour les ancres et le JavaScript.
- Ajoute « box-sizing: border-box » à tous les éléments dès le départ.
- Utilise les outils de développement du navigateur pour voir quelle règle gagne et lesquelles sont barrées.
- Garde une spécificité faible et homogène dans toute la feuille.

## Auto-évaluation

- Quelle est la spécificité de « #menu .lien » comparée à « .lien.actif » ?
- Dans quel ordre le navigateur départage-t-il deux règles en conflit ?
- Pourquoi une couleur définie sur « body » se retrouve-t-elle sur un paragraphe, mais pas une bordure ?
- Quelle largeur totale occupe un bloc de 200 pixels avec 10 pixels de padding en « content-box » ?

## À retenir

- La cascade départage les règles par importance, spécificité puis ordre.
- Un identifiant pèse plus qu’un nombre quelconque de classes.
- Certaines propriétés sont héritées, d’autres non.
- Le box model comprend contenu, padding, bordure et marge.
- « box-sizing: border-box » rend les largeurs prévisibles.
MD,
            'code_example' => <<<'CODE'
/* Page HTML associée :
   <article class="carte">
     <h2>Pagne Wax</h2>
     <p>Tissu coloré, 6 yards.</p>
     <p class="prix">15 000 FCFA</p>
   </article>
*/

/* Étape 1 : le padding et la bordure sont inclus dans la largeur */
*, *::before, *::after {
  box-sizing: border-box;
}

/* Étape 2 : la carte, spécificité (0,1,0) */
.carte {
  width: 300px;              /* largeur totale, bordure comprise */
  padding: 20px;             /* espace intérieur */
  border: 2px solid #d97706; /* bordure */
  margin: 16px auto;         /* espace extérieur, centré */
  font-family: Arial, sans-serif;
  color: #1f2937;            /* héritée par les enfants */
}

/* Étape 3 : (0,1,1) bat un simple élément */
.carte p {
  color: #4b5563;
  line-height: 1.5;
}

/* (0,2,0) l'emporte sur (0,1,1) : le prix sera en orange */
.carte .prix {
  color: #d97706;
  font-weight: bold;
}

/* Étape 4 : même spécificité (0,1,0), la dernière règle gagne */
.carte {
  background-color: #fffbeb;
}
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Corriger un conflit de styles',
            'exercise_description' => <<<'TXT'
Crée une page avec une carte d’article, puis écris une feuille CSS qui contient volontairement trois conflits de règles. Tu dois les résoudre sans utiliser « !important ».

Critères de réussite :
- Une règle « * » avec « box-sizing: border-box » est présente.
- La carte mesure exactement 320 pixels de large, padding et bordure compris.
- Le titre de la carte est rouge grâce à un sélecteur de classe plus spécifique, sans « !important » ni identifiant.
- Un commentaire indique la spécificité de chacun des trois sélecteurs en conflit.
- Une règle qui s’applique à la même propriété est placée plus bas pour démontrer que la dernière gagne.
TXT,
            'exercise_hint' => 'Ouvre les outils de développement, clique sur le titre et regarde l’onglet des styles : les règles perdantes apparaissent barrées. Calcule la spécificité sous la forme identifiants, classes, éléments.',
            'exercise_solution' => <<<'CODE'
/* HTML associé :
   <article class="carte">
     <h2 class="titre">Rentrée scolaire</h2>
     <p>Les inscriptions sont ouvertes.</p>
   </article>
*/

*, *::before, *::after {
  box-sizing: border-box;
}

/* Spécificité (0,1,0) */
.carte {
  width: 320px;           /* 320px au total grâce à border-box */
  padding: 16px;
  border: 2px solid #1f2937;
  margin: 24px auto;
  color: #111827;
}

/* Spécificité (0,0,1) : perd contre une classe */
h2 {
  color: blue;
}

/* Spécificité (0,2,0) : bat h2 (0,0,1) sans !important */
.carte .titre {
  color: red;
}

/* Même spécificité (0,1,0) que .carte plus haut : celle-ci gagne */
.carte {
  background-color: #f3f4f6;
}
CODE,
        ],

        'Flexbox' => [
            'description' => 'Apprends à aligner et répartir des éléments sur une ligne ou une colonne avec display: flex, justify-content, align-items, gap et flex.',
            'objective' => 'Construire une barre de navigation et une rangée de cartes avec Flexbox, en maîtrisant l’axe principal, l’axe secondaire, le retour à la ligne et la croissance des éléments.',
            'content' => <<<'MD'
## Pourquoi cette notion

Centrer un élément, aligner un logo à gauche et un menu à droite, répartir trois cartes sur une ligne : ces besoins reviennent dans presque chaque interface. Avant Flexbox, on utilisait des flottants et des astuces fragiles. Aujourd’hui, Flexbox est l’outil standard pour les alignements en une dimension, c’est-à-dire sur une ligne ou sur une colonne.

En agence, tu l’utilises pour les barres de navigation, les en-têtes de cartes, les boutons avec icône, les pieds de page et les formulaires. Maîtriser Flexbox te fait gagner un temps considérable sur chaque intégration.

## Les concepts clés

### Conteneur et éléments

Flexbox se déclare sur le parent avec « display: flex ». Ce parent devient le conteneur flex et ses enfants directs deviennent des éléments flex. Les petits-enfants ne sont pas concernés. Le conteneur définit la direction avec « flex-direction » : « row » (par défaut) place les éléments en ligne, « column » les empile.

### Axe principal et axe secondaire

L’axe principal suit la direction choisie, l’axe secondaire lui est perpendiculaire. La propriété « justify-content » distribue les éléments sur l’axe principal, avec des valeurs comme « flex-start », « center », « flex-end », « space-between » et « space-around ». La propriété « align-items » les aligne sur l’axe secondaire, avec « stretch » par défaut, « center » ou « flex-start ».

### Espacement et retour à la ligne

La propriété « gap » crée un espace régulier entre les éléments, sans marge parasite aux extrémités. Par défaut, les éléments restent sur une seule ligne et peuvent se comprimer. Avec « flex-wrap: wrap », ils passent à la ligne quand la place manque.

### Dimensionnement des éléments

La propriété raccourcie « flex » combine trois valeurs : « flex-grow » (part de l’espace libre à absorber), « flex-shrink » (capacité à rétrécir) et « flex-basis » (taille de départ). Avec « flex: 1 », les éléments se partagent l’espace de manière égale. La propriété « align-self » permet à un élément de s’aligner différemment des autres, et « margin-left: auto » pousse un élément à l’extrémité.

## Exemple pas à pas

Le code_example réalise la barre de navigation d’une application de livraison, puis une rangée de cartes. À l’étape un, « .barre » devient un conteneur flex avec « justify-content: space-between » pour séparer le logo et le menu, et « align-items: center » pour les centrer verticalement. À l’étape deux, le menu est lui-même un conteneur flex avec un « gap ». À l’étape trois, « .cartes » utilise « flex-wrap: wrap » et chaque carte reçoit « flex: 1 1 220px » pour s’adapter à la largeur disponible. Un commentaire explique chaque valeur.

## Erreurs fréquentes

- Appliquer « display: flex » à l’enfant au lieu du parent : rien ne bouge. Place la déclaration sur le conteneur.
- Confondre « justify-content » et « align-items » : l’alignement s’inverse. Rappelle-toi que « justify » suit l’axe principal et « align » l’axe secondaire.
- Oublier « flex-wrap » : les éléments se compriment et débordent sur mobile. Ajoute « flex-wrap: wrap » ou une règle responsive.
- Utiliser des marges pour espacer : elles laissent un espace à la fin. Utilise « gap ».
- Croire que Flexbox gère deux dimensions : des lignes qui se désalignent révèlent le besoin de Grid. Choisis Grid pour les tableaux de cartes alignées en colonnes.

## Bonnes pratiques

- Utilise « gap » plutôt que des marges entre éléments flex.
- Déclare « flex-wrap: wrap » pour tout ce qui peut contenir plusieurs éléments.
- Ajoute « min-width: 0 » aux éléments flex qui contiennent du texte long pour éviter les débordements.
- Garde Flexbox pour les alignements en une dimension et Grid pour les mises en page globales.

## Auto-évaluation

- Quel est l’axe principal d’un conteneur avec « flex-direction: column » ?
- Quelle propriété centre les éléments sur l’axe secondaire ?
- Que signifie « flex: 1 » ?
- Comment pousser un élément à l’extrême droite d’une barre flex ?

## À retenir

- Flexbox se déclare sur le parent et agit sur ses enfants directs.
- « justify-content » agit sur l’axe principal, « align-items » sur l’axe secondaire.
- « gap » espace proprement les éléments.
- « flex-wrap » et « flex » rendent les rangées adaptables.
- Flexbox convient aux layouts en une dimension.
MD,
            'code_example' => <<<'CODE'
/* Page HTML associée :
   <header class="barre">
     <a class="logo" href="/">LivraisonCI</a>
     <nav class="menu"><a href="/">Accueil</a><a href="/panier">Panier</a></nav>
   </header>
   <section class="cartes">
     <article class="carte">Poulet braisé</article> ... (4 cartes)
   </section>
*/
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; }

/* Étape 1 : logo à gauche, menu à droite, centrés verticalement */
.barre {
  display: flex;
  justify-content: space-between; /* axe principal : espace entre */
  align-items: center;            /* axe secondaire : centré */
  padding: 12px 24px;
  background: #111827;
  color: #fff;
}
.logo { color: #fff; font-weight: bold; text-decoration: none; }

/* Étape 2 : le menu est aussi un conteneur flex */
.menu {
  display: flex;
  gap: 16px;                      /* espace régulier entre les liens */
}
.menu a { color: #f59e0b; text-decoration: none; }

/* Étape 3 : cartes qui passent à la ligne selon la place */
.cartes {
  display: flex;
  flex-wrap: wrap;                /* retour à la ligne autorisé */
  gap: 16px;
  padding: 24px;
}
.carte {
  flex: 1 1 220px;                /* grandit, rétrécit, base 220px */
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 12px;
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Barre de navigation et liste de services',
            'exercise_description' => <<<'TXT'
Construis l’en-tête d’un site de réparation de téléphones avec un logo, un menu et un bouton, puis une rangée de cartes de services, uniquement avec Flexbox.

Critères de réussite :
- L’en-tête est un conteneur flex avec le logo à gauche, le menu au centre et un bouton à droite, tous centrés verticalement.
- Le menu utilise « gap » et non des marges pour espacer les liens.
- Les cartes de services utilisent « flex-wrap: wrap » et « flex » avec une base en pixels.
- Au moins une propriété « align-self » ou « margin-left: auto » est utilisée de façon justifiée.
- Aucun « float » ni positionnement absolu n’est utilisé pour l’alignement.
TXT,
            'exercise_hint' => 'Pour séparer logo, menu et bouton, essaie « justify-content: space-between » ou donne « margin-left: auto » au bouton. Réduis la fenêtre pour vérifier que les cartes passent bien à la ligne.',
            'exercise_solution' => <<<'CODE'
/* HTML associé :
   <header class="entete">
     <a class="logo" href="/">RepaPhone</a>
     <nav class="menu"><a href="/">Accueil</a><a href="/tarifs">Tarifs</a></nav>
     <a class="bouton" href="/rdv">Prendre RDV</a>
   </header>
   <section class="services">
     <article class="service">Écran cassé</article> ... (4 cartes)
   </section>
*/
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; }

.entete {
  display: flex;
  align-items: center;            /* centrage vertical */
  gap: 24px;
  padding: 12px 24px;
  background: #0f172a;
  color: #fff;
}
.logo { color: #fff; font-weight: bold; text-decoration: none; }

.menu {
  display: flex;
  gap: 16px;                      /* espacement par gap */
  margin-left: auto;              /* menu poussé vers la droite */
}
.menu a { color: #cbd5e1; text-decoration: none; }

.bouton {
  padding: 8px 16px;
  border-radius: 8px;
  background: #f97316;
  color: #fff;
  text-decoration: none;
}

.services {
  display: flex;
  flex-wrap: wrap;                /* passage à la ligne */
  gap: 16px;
  padding: 24px;
}
.service {
  flex: 1 1 200px;                /* base 200px, extensible */
  padding: 20px;
  border: 1px solid #e2e8f0;
  border-radius: 12px;
}
CODE,
        ],

        'Grid' => [
            'description' => 'Découvre CSS Grid pour organiser une interface en lignes et en colonnes, avec fr, repeat, minmax, auto-fit et les zones nommées.',
            'objective' => 'Réaliser une mise en page de tableau de bord avec zones nommées et une galerie qui s’adapte automatiquement à la largeur disponible grâce à repeat, auto-fit et minmax.',
            'content' => <<<'MD'
## Pourquoi cette notion

Flexbox excelle sur une ligne ou une colonne, mais une interface complète demande de contrôler lignes et colonnes en même temps : un en-tête, une barre latérale, un contenu central et un pied de page. C’est le rôle de CSS Grid, qui permet de dessiner la structure d’une page sans hacks ni calculs de pourcentages.

En entreprise, Grid sert aux tableaux de bord, aux catalogues de produits, aux galeries et aux mises en page éditoriales. Un catalogue qui affiche automatiquement trois, deux ou une colonne selon la largeur, sans une seule media query, est un cas typique.

## Les concepts clés

### Conteneur, lignes et colonnes

On déclare la grille sur le parent avec « display: grid ». Les colonnes se définissent avec « grid-template-columns » et les lignes avec « grid-template-rows ». L’unité « fr » représente une fraction de l’espace disponible : « 1fr 2fr » donne une deuxième colonne deux fois plus large que la première. La propriété « gap » espace lignes et colonnes.

### Des colonnes qui s’adaptent

La fonction « repeat(3, 1fr) » répète un motif sans l’écrire trois fois. La fonction « minmax(220px, 1fr) » définit une taille minimale et maximale. En combinant « repeat(auto-fit, minmax(220px, 1fr)) », le navigateur crée autant de colonnes de 220 pixels minimum que la place le permet, puis répartit le reste. C’est une grille responsive sans media query.

### Placement et zones nommées

Un élément peut occuper plusieurs cases avec « grid-column: span 2 » ou se placer entre des lignes avec « grid-column: 1 / 3 ». Pour une vision plus lisible, « grid-template-areas » décrit la mise en page par un dessin textuel. Chaque enfant reçoit un nom avec « grid-area », et le dessin du conteneur place les zones. Pour changer la mise en page sur mobile, il suffit de redéfinir le dessin.

### Grid ou Flexbox

Utilise Grid quand tu pars de la structure et que tu veux contrôler deux dimensions. Utilise Flexbox quand tu pars du contenu et que tu alignes une série d’éléments. Les deux se combinent très bien : Grid pour la page, Flexbox à l’intérieur des composants.

## Exemple pas à pas

Le code_example construit un tableau de bord pour une application de gestion de boutique. À l’étape un, « .page » déclare des zones nommées avec « grid-template-areas » : « entete » en haut sur deux colonnes, « menu » à gauche, « contenu » au centre et « pied » en bas. À l’étape deux, chaque enfant reçoit sa « grid-area ». À l’étape trois, la galerie de produits utilise « repeat(auto-fit, minmax(200px, 1fr)) » pour adapter le nombre de colonnes. À l’étape quatre, une carte vedette occupe deux colonnes avec « grid-column: span 2 ».

## Erreurs fréquentes

- Déclarer « grid-template-columns » sans « display: grid » : la propriété est ignorée. Ajoute « display: grid » sur le conteneur.
- Confondre « auto-fit » et « auto-fill » : avec « auto-fit », les colonnes vides s’effondrent et les éléments s’étirent. Choisis selon que tu veux des colonnes fantômes ou non.
- Oublier que seuls les enfants directs sont placés : les petits-enfants ignorent la grille. Pense à une grille imbriquée si nécessaire.
- Utiliser des largeurs fixes en pixels pour toutes les colonnes : la grille déborde sur mobile. Utilise « fr » et « minmax ».
- Écrire des zones nommées de forme non rectangulaire : la déclaration devient invalide. Les zones doivent former des rectangles.

## Bonnes pratiques

- Utilise « fr », « minmax » et « auto-fit » pour des grilles responsives.
- Utilise « grid-template-areas » pour les grandes structures de page, c’est très lisible.
- Garde le même « gap » partout grâce à une variable ou une valeur unique.
- Combine Grid pour la page et Flexbox pour l’intérieur des composants.

## Auto-évaluation

- Que signifie l’unité « fr » ?
- Que produit « repeat(auto-fit, minmax(200px, 1fr)) » ?
- Comment faire occuper deux colonnes à un élément ?
- Quand préférer Grid à Flexbox ?

## À retenir

- Grid gère lignes et colonnes en même temps.
- « fr », « repeat » et « minmax » décrivent des colonnes flexibles.
- « auto-fit » avec « minmax » crée une grille responsive sans media query.
- « grid-template-areas » rend la structure de page lisible.
- Flexbox et Grid se complètent.
MD,
            'code_example' => <<<'CODE'
/* Page HTML associée :
   <div class="page">
     <header class="entete">Ma Boutique</header>
     <nav class="menu">Menu</nav>
     <main class="contenu">
       <section class="galerie">
         <article class="produit vedette">Produit vedette</article>
         <article class="produit">Produit</article> ... (6 produits)
       </section>
     </main>
     <footer class="pied">Pied de page</footer>
   </div>
*/
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; }

/* Étape 1 : structure de page avec zones nommées */
.page {
  display: grid;
  grid-template-columns: 220px 1fr;     /* colonne fixe + colonne souple */
  grid-template-rows: auto 1fr auto;
  grid-template-areas:
    "entete entete"
    "menu   contenu"
    "pied   pied";
  min-height: 100vh;
  gap: 16px;
}

/* Étape 2 : chaque enfant reçoit sa zone */
.entete  { grid-area: entete; background: #0f172a; color: #fff; padding: 16px; }
.menu    { grid-area: menu;   background: #e2e8f0; padding: 16px; }
.contenu { grid-area: contenu; padding: 16px; }
.pied    { grid-area: pied;   background: #0f172a; color: #fff; padding: 16px; }

/* Étape 3 : galerie responsive sans media query */
.galerie {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 16px;
}
.produit {
  padding: 24px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
}

/* Étape 4 : la vedette occupe deux colonnes */
.vedette { grid-column: span 2; background: #fff7ed; }
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Catalogue responsive en grille',
            'exercise_description' => <<<'TXT'
Réalise la page d’un catalogue de produits avec une structure de page en zones nommées et une galerie qui s’adapte seule à la largeur de l’écran.

Critères de réussite :
- La structure de page utilise « grid-template-areas » avec au moins quatre zones nommées.
- La galerie utilise « repeat(auto-fit, minmax(...)) » sans aucune media query.
- Un produit en vedette occupe deux colonnes avec « grid-column: span 2 ».
- Le même « gap » est appliqué entre les produits et entre les zones de la page.
- Les produits sont placés dans la grille sans utiliser « float » ni largeurs en pourcentage.
TXT,
            'exercise_hint' => 'Dessine les zones sur papier avant de coder. Pour tester la galerie, redimensionne la fenêtre : le nombre de colonnes doit changer tout seul.',
            'exercise_solution' => <<<'CODE'
/* HTML associé :
   <div class="page">
     <header class="entete">Marché Digital</header>
     <aside class="filtres">Filtres</aside>
     <main class="contenu">
       <section class="catalogue">
         <article class="produit vedette">Offre du jour</article>
         <article class="produit">Sac</article> ... (6 produits)
       </section>
     </main>
     <footer class="pied">Contact</footer>
   </div>
*/
* { box-sizing: border-box; }
body { margin: 0; font-family: Arial, sans-serif; }

:root { --espace: 16px; }

.page {
  display: grid;
  grid-template-columns: 200px 1fr;
  grid-template-rows: auto 1fr auto;
  grid-template-areas:
    "entete  entete"
    "filtres contenu"
    "pied    pied";
  gap: var(--espace);
  min-height: 100vh;
}

.entete  { grid-area: entete;  background: #1e293b; color: #fff; padding: 16px; }
.filtres { grid-area: filtres; background: #f1f5f9; padding: 16px; }
.contenu { grid-area: contenu; padding: 16px; }
.pied    { grid-area: pied;    background: #1e293b; color: #fff; padding: 16px; }

.catalogue {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
  gap: var(--espace);
}
.produit {
  padding: 24px;
  border: 1px solid #cbd5e1;
  border-radius: 12px;
}
.vedette { grid-column: span 2; background: #fff7ed; }
CODE,
        ],

        'Responsive design' => [
            'description' => 'Adapte une interface à tous les écrans avec la balise viewport, les unités fluides, les media queries et l’approche mobile-first.',
            'objective' => 'Rendre une page lisible et utilisable du téléphone à l’ordinateur de bureau, en partant d’un style mobile et en ajoutant deux breakpoints avec des media queries min-width.',
            'content' => <<<'MD'
## Pourquoi cette notion

En Afrique de l’Ouest, la plupart des visiteurs arrivent depuis un smartphone, souvent avec un écran modeste et une connexion limitée. Une page conçue seulement pour un grand écran d’ordinateur est un échec commercial : le client doit zoomer, glisser latéralement et abandonne. Le responsive design consiste à faire fonctionner la même page sur toutes les tailles d’écran.

En entreprise, la question « est-ce que ça marche sur mobile ? » est posée à chaque livraison. Savoir structurer ton CSS pour que le mobile soit le point de départ fait gagner du temps et évite de nombreuses corrections.

## Les concepts clés

### La balise viewport

Sans la balise « meta name="viewport" content="width=device-width, initial-scale=1" » dans le « head », les navigateurs mobiles affichent la page comme sur un grand écran, puis la réduisent. Cette balise est le préalable à tout responsive design.

### Les unités flexibles

Les pixels sont fixes, d’autres unités s’adaptent. L’unité « rem » dépend de la taille de police racine et respecte les préférences de l’utilisateur. L’unité « % » dépend du parent. Les unités « vw » et « vh » dépendent de la fenêtre. La fonction « clamp(min, valeur, max) » fixe une valeur fluide bornée, par exemple pour un titre. Pour les images, « max-width: 100% » et « height: auto » empêchent le débordement.

### Mobile-first et media queries

L’approche mobile-first écrit d’abord le style pour les petits écrans, sans condition, puis ajoute des améliorations pour les écrans plus larges avec « @media (min-width: 768px) ». Le code de base est léger et les surcharges viennent progressivement. L’approche inverse, avec « max-width », oblige à défaire des styles. Les points de rupture se choisissent quand le contenu se casse, pas selon des modèles d’appareils précis. On utilise couramment des valeurs autour de 640, 768 et 1024 pixels.

### Les autres conditions

Une media query peut aussi tester l’orientation, le mode sombre avec « prefers-color-scheme » ou la préférence de mouvement réduit avec « prefers-reduced-motion ». Pense aussi à la taille des zones tactiles : un bouton doit mesurer environ 44 pixels de haut pour être cliqué au doigt.

## Exemple pas à pas

Le code_example adapte la page d’un restaurant. À l’étape un, la balise viewport est rappelée en commentaire. À l’étape deux, le style de base cible le mobile : une seule colonne, des textes en « rem » et un titre avec « clamp ». À l’étape trois, les images utilisent « max-width: 100% ». À l’étape quatre, une première media query à partir de 640 pixels passe le menu en ligne et les plats sur deux colonnes. À l’étape cinq, une seconde à partir de 1024 pixels passe à trois colonnes et centre le contenu avec une largeur maximale.

## Erreurs fréquentes

- Oublier la balise viewport : la page est minuscule sur téléphone. Ajoute-la dans chaque « head ».
- Fixer des largeurs en pixels : la page déborde. Utilise des largeurs maximales, des pourcentages et « fr ».
- Écrire le style pour grand écran puis défaire avec « max-width » : le code grossit. Pars du mobile avec « min-width ».
- Choisir des breakpoints sur des modèles de téléphones : ils vieillissent. Choisis-les selon le moment où ta mise en page se casse.
- Oublier les images : elles débordent. Applique « max-width: 100% » et « height: auto ».

## Bonnes pratiques

- Écris le style de base pour mobile, puis ajoute des media queries « min-width ».
- Teste avec l’outil de simulation du navigateur et avec un vrai téléphone.
- Utilise « rem » pour les tailles de texte et « clamp » pour les titres.
- Garde des zones tactiles confortables d’environ 44 pixels.

## Auto-évaluation

- Que se passe-t-il sans la balise viewport sur un téléphone ?
- Quelle différence entre « min-width » et « max-width » dans une media query ?
- Pourquoi préférer « rem » à « px » pour le texte ?
- Que fait « clamp(1.5rem, 4vw, 3rem) » ?

## À retenir

- La balise viewport est indispensable au responsive.
- Mobile-first : style de base mobile, puis ajouts avec « min-width ».
- Les unités flexibles évitent les débordements.
- Les breakpoints suivent le contenu, pas les appareils.
- Une page responsive se teste sur de vrais écrans.
MD,
            'code_example' => <<<'CODE'
/* Étape 1 : dans le head du HTML, ne pas oublier :
   <meta name="viewport" content="width=device-width, initial-scale=1">

   HTML associé :
   <header class="entete"><h1>Chez Awa</h1>
     <nav class="menu"><a href="#">Plats</a><a href="#">Contact</a></nav></header>
   <main><section class="plats">
     <article class="plat"><img src="plat.jpg" alt="Attiéké poisson"><h2>Attiéké</h2></article>
     ... (6 plats)
   </section></main>
*/
* { box-sizing: border-box; }

/* Étape 2 : style de base pensé pour mobile (une colonne) */
body {
  margin: 0;
  font-family: Arial, sans-serif;
  font-size: 1rem;                       /* respecte la taille de l'utilisateur */
  line-height: 1.5;
}
h1 { font-size: clamp(1.5rem, 6vw, 3rem); margin: 0; }  /* titre fluide */
.entete { padding: 16px; background: #7c2d12; color: #fff; }
.menu a { display: inline-block; padding: 12px; color: #fff; } /* zone tactile */
.plats { display: grid; grid-template-columns: 1fr; gap: 16px; padding: 16px; }

/* Étape 3 : les images ne débordent jamais */
img { max-width: 100%; height: auto; display: block; }

/* Étape 4 : à partir de 640px, deux colonnes */
@media (min-width: 640px) {
  .entete { display: flex; justify-content: space-between; align-items: center; }
  .plats { grid-template-columns: repeat(2, 1fr); }
}

/* Étape 5 : à partir de 1024px, trois colonnes et largeur maximale */
@media (min-width: 1024px) {
  .plats { grid-template-columns: repeat(3, 1fr); max-width: 1100px; margin: 0 auto; }
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Rendre une page de service responsive',
            'exercise_description' => <<<'TXT'
Écris la page d’accueil d’un service de livraison avec en-tête, liste de formules et formulaire, puis rends-la responsive avec une approche mobile-first.

Critères de réussite :
- La page contient la balise viewport et le style de base ne contient aucune media query.
- Au moins deux media queries « min-width » ajoutent des améliorations à partir de 640 et 1024 pixels.
- Les images utilisent « max-width: 100% » et aucune largeur fixe en pixels ne dépasse un petit écran de 360 pixels.
- Un titre utilise « clamp » et le texte utilise l’unité « rem ».
- Les liens et boutons ont une hauteur d’au moins 44 pixels sur mobile.
TXT,
            'exercise_hint' => 'Ouvre le simulateur d’appareils des outils de développement et réduis la largeur à 360 pixels. Si une barre de défilement horizontale apparaît, cherche l’élément qui déborde.',
            'exercise_solution' => <<<'CODE'
/* HTML associé (avec la balise viewport dans le head) :
   <header class="entete"><h1>Livraison Express</h1>
     <nav class="menu"><a href="#">Formules</a><a href="#">Contact</a></nav></header>
   <main><section class="formules">
     <article class="formule"><img src="velo.jpg" alt="Coursier à vélo"><h2>Express</h2></article>
     ... (3 formules)
   </section></main>
*/
* { box-sizing: border-box; }

body {
  margin: 0;
  font-family: Arial, sans-serif;
  font-size: 1rem;
  line-height: 1.5;
}
h1 { font-size: clamp(1.5rem, 6vw, 2.75rem); margin: 0; }

.entete { padding: 16px; background: #0f766e; color: #fff; }
.menu a {
  display: inline-block;
  min-height: 44px;                 /* zone tactile */
  padding: 10px 14px;
  color: #fff;
}

.formules {
  display: grid;
  grid-template-columns: 1fr;       /* mobile : une colonne */
  gap: 16px;
  padding: 16px;
}
.formule { padding: 16px; border: 1px solid #99f6e4; border-radius: 12px; }
img { max-width: 100%; height: auto; display: block; }

@media (min-width: 640px) {
  .entete { display: flex; justify-content: space-between; align-items: center; }
  .formules { grid-template-columns: repeat(2, 1fr); }
}

@media (min-width: 1024px) {
  .formules {
    grid-template-columns: repeat(3, 1fr);
    max-width: 1100px;
    margin: 0 auto;
  }
}
CODE,
        ],

        'États et animations' => [
            'description' => 'Rends une interface vivante avec les pseudo-classes hover, focus et active, les transitions pour les changements d’état et les keyframes pour les animations répétées.',
            'objective' => 'Créer un bouton et une carte avec des états hover, focus-visible et active accessibles, une transition fluide, une animation keyframes et une règle prefers-reduced-motion.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une interface qui ne réagit pas à l’utilisateur paraît cassée. Quand tu passes la souris sur un bouton, quand tu navigues au clavier ou quand tu appuies sur un lien, tu attends un retour visuel. Ces petits signaux, appelés états, confirment que l’élément est cliquable et que ton action a été prise en compte.

En entreprise, les états et les animations font la différence entre un site amateur et un produit soigné. Ils guident l’attention, indiquent un chargement et rendent l’interface plus agréable. Mais ils doivent rester sobres et accessibles, surtout sur des téléphones d’entrée de gamme.

## Les concepts clés

### Les pseudo-classes d’état

Une pseudo-classe cible un élément selon son état. « :hover » s’applique au survol de la souris, « :active » pendant le clic, « :focus » quand l’élément reçoit le focus. La variante « :focus-visible » n’affiche le focus que lors d’une navigation clavier, ce qui évite un contour gênant au clic de souris tout en restant accessible. D’autres sont utiles : « :disabled » pour un contrôle désactivé, « :checked » pour une case cochée, et « :first-child » ou « :nth-child() » pour la position.

### Les transitions

Une transition anime le passage d’une valeur à une autre quand l’état change. Elle se déclare sur l’état de base avec « transition: propriété durée fonction-de-temps ». Par exemple, « transition: transform 200ms ease » rend le déplacement fluide. Une durée de 150 à 300 millisecondes est confortable.

### Les animations keyframes

Pour une animation qui se joue seule ou en boucle, on définit des étapes avec « @keyframes nom », puis on l’applique avec « animation: nom durée fonction répétition ». C’est utile pour un indicateur de chargement ou une apparition de contenu.

### Performance et accessibilité

Anime de préférence « transform » et « opacity », que le navigateur traite sans recalculer toute la mise en page. Évite d’animer « width », « height » ou « top ». Respecte les utilisateurs sensibles au mouvement avec la media query « prefers-reduced-motion: reduce », qui permet de réduire ou supprimer les animations.

## Exemple pas à pas

Le code_example stylise un bouton de commande et une pastille de chargement. À l’étape un, le bouton reçoit un style de base et une « transition » sur « transform » et « background-color ». À l’étape deux, « :hover » le soulève légèrement et « :active » le repousse. À l’étape trois, « :focus-visible » ajoute un contour net pour le clavier. À l’étape quatre, « :disabled » grise le bouton. À l’étape cinq, « @keyframes tourner » fait pivoter un indicateur de chargement. À l’étape six, la règle « prefers-reduced-motion » coupe les animations.

## Erreurs fréquentes

- Placer « transition » sur l’état « :hover » seulement : le retour est brusque. Mets la transition sur l’état de base pour qu’elle joue dans les deux sens.
- Supprimer le focus avec « outline: none » : la navigation clavier devient aveugle. Définis un style « :focus-visible » visible.
- Animer « width » ou « margin » : l’animation saccade sur mobile. Utilise « transform » et « opacity ».
- Compter sur « :hover » pour afficher une information clé : elle reste invisible sur téléphone. Rends l’information accessible autrement.
- Faire des animations longues ou infinies partout : elles distraient et fatiguent. Garde des durées courtes et ajoute « prefers-reduced-motion ».

## Bonnes pratiques

- Donne un retour visuel pour chaque état : survol, focus, actif, désactivé.
- Anime « transform » et « opacity », avec des durées entre 150 et 300 millisecondes.
- Respecte « prefers-reduced-motion » dans tous tes projets.
- Réserve les animations infinies aux indicateurs de chargement.

## Auto-évaluation

- Quelle différence entre « :focus » et « :focus-visible » ?
- Pourquoi mettre la transition sur l’état de base plutôt que sur « :hover » ?
- Quelles propriétés est-il préférable d’animer et pourquoi ?
- Comment respecter un utilisateur qui demande moins de mouvement ?

## À retenir

- Les pseudo-classes d’état donnent un retour visuel à l’utilisateur.
- Une transition anime un changement d’état, les keyframes une animation autonome.
- Anime « transform » et « opacity » pour de bonnes performances.
- « :focus-visible » garde la navigation clavier lisible.
- « prefers-reduced-motion » protège les utilisateurs sensibles au mouvement.
MD,
            'code_example' => <<<'CODE'
/* HTML associé :
   <button class="bouton" type="button">Commander</button>
   <button class="bouton" type="button" disabled>Rupture de stock</button>
   <div class="chargement" aria-hidden="true"></div>
*/
* { box-sizing: border-box; }

/* Étape 1 : style de base + transition sur l'état de base */
.bouton {
  padding: 12px 24px;
  border: 0;
  border-radius: 10px;
  background-color: #ea580c;
  color: #fff;
  font-size: 1rem;
  cursor: pointer;
  /* la transition joue à l'aller et au retour */
  transition: transform 200ms ease, background-color 200ms ease;
}

/* Étape 2 : survol et clic */
.bouton:hover  { background-color: #c2410c; transform: translateY(-2px); }
.bouton:active { transform: translateY(0); }

/* Étape 3 : focus visible pour la navigation au clavier */
.bouton:focus-visible {
  outline: 3px solid #1d4ed8;
  outline-offset: 3px;
}

/* Étape 4 : état désactivé */
.bouton:disabled {
  background-color: #9ca3af;
  cursor: not-allowed;
  transform: none;
}

/* Étape 5 : animation de chargement avec keyframes */
@keyframes tourner {
  from { transform: rotate(0deg); }
  to   { transform: rotate(360deg); }
}
.chargement {
  width: 32px;
  height: 32px;
  border: 4px solid #fed7aa;
  border-top-color: #ea580c;
  border-radius: 50%;
  animation: tourner 800ms linear infinite;
}

/* Étape 6 : respect de la préférence de mouvement réduit */
@media (prefers-reduced-motion: reduce) {
  .bouton, .chargement { transition: none; animation: none; }
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Carte interactive et indicateur de chargement',
            'exercise_description' => <<<'TXT'
Crée une carte de produit cliquable avec ses états, et un indicateur de chargement animé, en respectant l’accessibilité et la performance.

Critères de réussite :
- La carte réagit à « :hover », « :focus-visible » et « :active », avec une « transition » déclarée sur l’état de base.
- Les animations de la carte n’utilisent que « transform » et « opacity ».
- Un bouton « :disabled » a un style distinct et un curseur adapté.
- Un indicateur de chargement utilise « @keyframes » avec « animation » répétée.
- Une règle « prefers-reduced-motion: reduce » désactive transition et animation.
TXT,
            'exercise_hint' => 'Teste ta carte uniquement au clavier avec la touche Tab pour vérifier le focus visible. Pour simuler la réduction de mouvement, active l’option dans les outils de développement.',
            'exercise_solution' => <<<'CODE'
/* HTML associé :
   <a class="carte" href="/produit">Sac en pagne - 8 000 FCFA</a>
   <button class="valider" type="button" disabled>Indisponible</button>
   <div class="spinner" aria-hidden="true"></div>
*/
* { box-sizing: border-box; }

.carte {
  display: block;
  width: 260px;
  padding: 20px;
  border: 1px solid #e5e7eb;
  border-radius: 14px;
  color: #111827;
  text-decoration: none;
  /* transition sur l'état de base */
  transition: transform 200ms ease, opacity 200ms ease;
}
.carte:hover  { transform: translateY(-4px); }
.carte:active { transform: translateY(0); opacity: 0.85; }
.carte:focus-visible {
  outline: 3px solid #1d4ed8;
  outline-offset: 3px;
}

.valider {
  padding: 10px 20px;
  border: 0;
  border-radius: 8px;
  background: #15803d;
  color: #fff;
  cursor: pointer;
}
.valider:disabled {
  background: #9ca3af;
  cursor: not-allowed;
}

@keyframes tourner {
  to { transform: rotate(360deg); }
}
.spinner {
  width: 28px;
  height: 28px;
  border: 4px solid #bbf7d0;
  border-top-color: #15803d;
  border-radius: 50%;
  animation: tourner 900ms linear infinite;
}

@media (prefers-reduced-motion: reduce) {
  .carte, .spinner { transition: none; animation: none; }
}
CODE,
        ],

        'Architecture CSS' => [
            'description' => 'Organise tes styles avec des variables CSS, une convention de nommage comme BEM, des couches et une spécificité maîtrisée pour garder une base saine.',
            'objective' => 'Refactorer une feuille de style en y introduisant des variables CSS, des composants nommés selon BEM, une structure en sections et aucun identifiant ni !important.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une feuille de style qui grandit sans règle devient vite un cauchemar. Pour modifier la couleur d’un bouton, tu la cherches à quinze endroits. Tu changes un style et tu casses une autre page. Les développeurs ajoutent « !important » pour s’en sortir, ce qui aggrave le problème. L’architecture CSS est l’ensemble des règles qui évitent cette dérive.

En agence, un projet vit plusieurs années et passe entre plusieurs mains. Une base CSS organisée permet à un nouveau développeur de comprendre le code en une heure, et à toi de livrer une modification sans peur des effets de bord.

## Les concepts clés

### Les variables CSS

Les variables CSS, aussi appelées propriétés personnalisées, se déclarent avec deux tirets : « --couleur-principale: #ea580c; ». On les lit avec « var(--couleur-principale) ». On les place généralement dans « :root » pour qu’elles soient disponibles partout. Elles centralisent les couleurs, espacements, rayons et polices, ce qu’on appelle des tokens de design. Changer un token met à jour tout le site, et un thème sombre se fait en redéfinissant les variables.

### Une convention de nommage

BEM, pour Block, Element, Modifier, est une convention répandue. Le bloc est un composant autonome, comme « .carte ». L’élément est une partie du bloc, écrit « .carte__titre ». Le modificateur est une variante, écrit « .carte--vedette ». Les noms disent tout de suite le rôle de la classe et évitent les conflits. Peu importe la convention choisie, l’essentiel est de la garder dans toute l’équipe.

### Spécificité faible et isolation

Style avec des classes simples, sans imbriquer de longs sélecteurs, et sans identifiants. Chaque composant se stylise lui-même et ne dépend pas de l’endroit où il est placé. Ainsi tu peux déplacer un composant sans le casser. La directive « @layer » permet en plus de déclarer des couches ordonnées, par exemple réinitialisation, base, composants et utilitaires, pour que l’ordre de priorité soit explicite plutôt que dépendant de la spécificité.

### L’organisation de la feuille

Range les styles dans un ordre constant : variables, réinitialisation, éléments de base, mise en page, composants, utilitaires. Dans un grand projet, chaque composant a son propre fichier. Évite le code mort en supprimant les règles qui ne servent plus.

## Exemple pas à pas

Le code_example organise le style d’une application de gestion de stock. À l’étape un, « :root » déclare les tokens de couleur, d’espacement et de rayon. À l’étape deux, une courte réinitialisation fixe « box-sizing » et la police. À l’étape trois, le bloc « .carte » est défini avec ses éléments « .carte__titre » et « .carte__valeur ». À l’étape quatre, le modificateur « .carte--alerte » change seulement la couleur d’accent en redéfinissant une variable. À l’étape cinq, un bloc « .bouton » avec ses modificateurs montre la réutilisation des tokens. Un thème sombre est ajouté en redéfinissant les variables dans une media query.

## Erreurs fréquentes

- Répéter les mêmes valeurs partout : un changement de couleur demande dix modifications. Crée des variables.
- Imbriquer des sélecteurs trop profonds comme « .page .liste .item a » : le style devient fragile. Donne une classe BEM à l’élément.
- Utiliser des identifiants et « !important » : les surcharges deviennent impossibles. Reste sur des classes à faible spécificité.
- Mélanger plusieurs conventions de nommage : personne ne retrouve ses classes. Choisis une convention et documente-la.
- Laisser du code mort : la feuille grossit et inquiète. Supprime régulièrement les règles inutilisées.

## Bonnes pratiques

- Définis des tokens de design en variables CSS dès le début du projet.
- Choisis une convention de nommage et applique-la partout.
- Garde une spécificité basse et uniforme : une classe par règle autant que possible.
- Organise la feuille ou les fichiers dans un ordre stable et documenté.

## Auto-évaluation

- Comment déclare-t-on et utilise-t-on une variable CSS ?
- Que signifient bloc, élément et modificateur en BEM ?
- Pourquoi éviter les identifiants pour le style ?
- Comment faire un thème sombre avec des variables ?

## À retenir

- Une architecture CSS évite la dérive d’une feuille qui grandit.
- Les variables centralisent les valeurs de design.
- Une convention de nommage rend les classes lisibles et sans conflit.
- Une spécificité basse permet de surcharger sans « !important ».
- Un ordre de feuille stable facilite la maintenance.
MD,
            'code_example' => <<<'CODE'
/* HTML associé :
   <article class="carte carte--alerte">
     <h3 class="carte__titre">Riz 25 kg</h3>
     <p class="carte__valeur">3 sacs restants</p>
     <button class="bouton bouton--principal">Réapprovisionner</button>
   </article>
*/

/* Étape 1 : tokens de design, source unique de vérité */
:root {
  --couleur-fond: #ffffff;
  --couleur-texte: #1f2937;
  --couleur-accent: #15803d;
  --couleur-alerte: #b91c1c;
  --espace: 16px;
  --rayon: 12px;
}

/* Thème sombre : on redéfinit seulement les variables */
@media (prefers-color-scheme: dark) {
  :root { --couleur-fond: #111827; --couleur-texte: #f3f4f6; }
}

/* Étape 2 : réinitialisation minimale */
*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--couleur-fond);
  color: var(--couleur-texte);
  font-family: Arial, sans-serif;
}

/* Étape 3 : bloc BEM avec ses éléments */
.carte {
  --accent: var(--couleur-accent);        /* variable locale du composant */
  padding: var(--espace);
  border: 2px solid var(--accent);
  border-radius: var(--rayon);
}
.carte__titre  { margin: 0 0 8px; }
.carte__valeur { margin: 0 0 var(--espace); color: var(--accent); }

/* Étape 4 : modificateur, on change seulement la variable locale */
.carte--alerte { --accent: var(--couleur-alerte); }

/* Étape 5 : un autre bloc réutilise les mêmes tokens */
.bouton {
  padding: 10px 20px;
  border: 0;
  border-radius: var(--rayon);
  color: #fff;
  cursor: pointer;
}
.bouton--principal { background: var(--couleur-accent); }
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Refactorer une feuille de style désordonnée',
            'exercise_description' => <<<'TXT'
Pars d’une page avec trois composants (carte, bouton, badge) et écris une feuille de style propre qui respecte une architecture claire, avec un thème clair et un thème sombre.

Critères de réussite :
- Au moins cinq variables CSS sont déclarées dans « :root » et utilisées avec « var() ».
- Les composants suivent la convention BEM avec au moins un modificateur chacun pour deux composants.
- Aucun identifiant ni « !important » n’est utilisé dans la feuille.
- Aucun sélecteur ne dépasse deux niveaux d’imbrication.
- Le thème sombre est obtenu uniquement en redéfinissant des variables dans une media query.
TXT,
            'exercise_hint' => 'Commence par lister toutes les valeurs de couleur répétées et transforme-les en variables. Pour chaque règle, vérifie si une seule classe suffit à la cibler.',
            'exercise_solution' => <<<'CODE'
/* HTML associé :
   <article class="carte carte--succes">
     <h3 class="carte__titre">Ventes du jour</h3>
     <span class="badge badge--info">Nouveau</span>
     <button class="bouton bouton--secondaire">Voir le détail</button>
   </article>
*/

:root {
  --fond: #ffffff;
  --texte: #1f2937;
  --accent: #2563eb;
  --succes: #15803d;
  --espace: 16px;
  --rayon: 12px;
}

@media (prefers-color-scheme: dark) {
  :root { --fond: #0f172a; --texte: #e2e8f0; }
}

*, *::before, *::after { box-sizing: border-box; }
body {
  margin: 0;
  background: var(--fond);
  color: var(--texte);
  font-family: Arial, sans-serif;
}

.carte {
  --couleur: var(--accent);
  padding: var(--espace);
  border: 2px solid var(--couleur);
  border-radius: var(--rayon);
}
.carte__titre { margin: 0 0 8px; color: var(--couleur); }
.carte--succes { --couleur: var(--succes); }

.badge {
  display: inline-block;
  padding: 2px 10px;
  border-radius: 999px;
  background: var(--accent);
  color: #fff;
  font-size: 0.8rem;
}
.badge--info { background: var(--accent); }

.bouton {
  padding: 10px 20px;
  border: 0;
  border-radius: var(--rayon);
  cursor: pointer;
}
.bouton--secondaire {
  background: transparent;
  border: 2px solid var(--accent);
  color: var(--accent);
}
CODE,
        ],

        'Projet final CSS' => [
            'description' => 'Combine layout, composants, états, responsive et architecture dans un tableau de bord sombre complet avec barre latérale, cartes, tableau et formulaire.',
            'objective' => 'Livrer un tableau de bord sombre responsive avec barre latérale en bureau et navigation basse en mobile, cartes, tableau scrollable et formulaire, écrit en CSS organisé avec variables et BEM.',
            'content' => <<<'MD'
## Pourquoi cette notion

Le tableau de bord est l’une des interfaces les plus demandées par les clients : gestion de stock, suivi de ventes, administration d’école, suivi de livraisons. Il rassemble presque toutes les difficultés du CSS : une mise en page à plusieurs zones, des composants répétés, des tableaux de données, des formulaires et un comportement différent sur mobile et sur bureau.

Ce projet final te permet de prouver que tu sais combiner tout le parcours : cascade, Flexbox, Grid, responsive, états et architecture. Réalisé proprement, il devient une vraie pièce de portfolio à montrer à des clients.

## Les concepts clés

### Penser en zones et en composants

Commence par découper l’écran. Sur bureau, une barre latérale à gauche et une zone principale à droite. Sur mobile, la barre latérale disparaît au profit d’une navigation fixée en bas. Dans la zone principale, une ligne de cartes statistiques, un tableau et un formulaire. Chaque morceau devient un composant avec son bloc BEM : « .barre-laterale », « .carte-stat », « .tableau » et « .formulaire ».

### Le layout principal

Grid convient au squelette de la page avec « grid-template-columns » pour la barre latérale et le contenu. Flexbox convient à l’intérieur des composants, comme l’en-tête d’une carte. Les cartes statistiques utilisent « repeat(auto-fit, minmax(...)) » pour s’adapter seules. Une approche mobile-first part d’une seule colonne, puis ajoute la barre latérale à partir d’un breakpoint.

### Un thème sombre cohérent

Un thème sombre ne consiste pas à inverser les couleurs. Il utilise des fonds gris très foncés plutôt que du noir pur, des textes clairs mais pas blancs purs, et un accent lumineux. Tout est défini en variables pour garantir la cohérence. Vérifie toujours le contraste entre le texte et son fond.

### Tableaux et formulaires

Un tableau large déborde sur mobile. La solution consiste à l’envelopper dans un conteneur avec « overflow-x: auto » pour qu’il défile horizontalement sans casser la page. Les champs de formulaire reçoivent un style homogène, avec un état « :focus-visible » clair et une taille de zone tactile confortable.

## Exemple pas à pas

Le code_example construit un tableau de bord de boutique. À l’étape un, les variables sombres sont déclarées dans « :root ». À l’étape deux, « .app » est une grille d’une colonne et la barre latérale est fixée en bas comme navigation mobile. À l’étape trois, une media query à partir de 900 pixels place la barre à gauche en colonne. À l’étape quatre, les cartes statistiques utilisent « auto-fit ». À l’étape cinq, le tableau est enveloppé dans « .tableau-conteneur » avec défilement horizontal. À l’étape six, le formulaire et le bouton reçoivent des états de survol et de focus.

## Erreurs fréquentes

- Commencer par le bureau puis défaire pour mobile : le code se complique. Écris d’abord le mobile, puis ajoute des media queries « min-width ».
- Laisser un tableau déborder de la page : un défilement horizontal apparaît sur tout l’écran. Enveloppe-le dans un conteneur « overflow-x: auto ».
- Choisir un noir pur et un blanc pur : le contraste fatigue les yeux. Utilise des gris foncés et des textes légèrement adoucis.
- Dupliquer les valeurs de couleur : le thème devient incohérent. Passe par des variables.
- Oublier les états de focus sur les champs et boutons : la navigation clavier est impossible. Ajoute « :focus-visible » partout.

## Bonnes pratiques

- Découpe l’interface en zones puis en composants avant d’écrire du CSS.
- Combine Grid pour la structure et Flexbox pour les composants.
- Vérifie ton design avec le simulateur mobile à 360 pixels de large.
- Garde des variables pour toutes les couleurs, espaces et rayons.
- Teste au clavier avant de livrer.

## Auto-évaluation

- Comment organiser la navigation différemment sur mobile et sur bureau ?
- Comment éviter qu’un tableau large casse la mise en page mobile ?
- Quels outils de layout utilises-tu pour la page et pour un composant ?
- Pourquoi éviter le noir pur dans un thème sombre ?

## À retenir

- Un projet commence par un découpage en zones et en composants.
- Grid structure la page, Flexbox organise l’intérieur des composants.
- Le mobile-first part d’une colonne et ajoute des media queries « min-width ».
- Les tableaux larges se placent dans un conteneur à défilement horizontal.
- Variables et BEM gardent le projet maintenable.
MD,
            'code_example' => <<<'CODE'
/* HTML associé :
   <div class="app">
     <aside class="barre-laterale"><a href="#">Accueil</a><a href="#">Ventes</a><a href="#">Stock</a></aside>
     <main class="principal">
       <section class="stats"><article class="carte-stat">...</article> ... (3 cartes)</section>
       <div class="tableau-conteneur"><table class="tableau">...</table></div>
       <form class="formulaire"><label>...<input class="champ">... <button class="bouton">Ajouter</button></form>
     </main>
   </div>
*/

/* Étape 1 : thème sombre en variables */
:root {
  --fond: #0f172a;
  --surface: #1e293b;
  --bordure: #334155;
  --texte: #e2e8f0;
  --accent: #f59e0b;
  --espace: 16px;
  --rayon: 12px;
}
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; background: var(--fond); color: var(--texte); font-family: Arial, sans-serif; }

/* Étape 2 : mobile d'abord, navigation fixée en bas */
.app { display: grid; grid-template-columns: 1fr; min-height: 100vh; padding-bottom: 64px; }
.barre-laterale {
  position: fixed; bottom: 0; left: 0; right: 0;
  display: flex; justify-content: space-around;
  background: var(--surface); border-top: 1px solid var(--bordure);
}
.barre-laterale a { padding: 18px 12px; color: var(--texte); text-decoration: none; }
.principal { padding: var(--espace); display: grid; gap: var(--espace); }

/* Étape 3 : sur bureau, barre latérale à gauche */
@media (min-width: 900px) {
  .app { grid-template-columns: 220px 1fr; padding-bottom: 0; }
  .barre-laterale {
    position: static; flex-direction: column; justify-content: flex-start;
    border-top: 0; border-right: 1px solid var(--bordure);
  }
}

/* Étape 4 : cartes statistiques responsives */
.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--espace); }
.carte-stat { padding: var(--espace); background: var(--surface); border: 1px solid var(--bordure); border-radius: var(--rayon); }

/* Étape 5 : tableau scrollable sur petit écran */
.tableau-conteneur { overflow-x: auto; }
.tableau { width: 100%; min-width: 480px; border-collapse: collapse; }
.tableau th, .tableau td { padding: 10px; text-align: left; border-bottom: 1px solid var(--bordure); }

/* Étape 6 : formulaire avec états */
.champ { padding: 10px; background: var(--fond); color: var(--texte); border: 1px solid var(--bordure); border-radius: 8px; }
.champ:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
.bouton { padding: 10px 20px; border: 0; border-radius: 8px; background: var(--accent); color: #111827; cursor: pointer; transition: transform 150ms ease; }
.bouton:hover { transform: translateY(-2px); }
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : tableau de bord sombre responsive',
            'exercise_description' => <<<'TXT'
Réalise un tableau de bord sombre pour une activité de ton choix (boutique, école, livraison), en HTML et CSS uniquement, sans framework.

Livrables :
- Un fichier « index.html » et un fichier « style.css ».
- Un court fichier de notes qui liste les points de rupture choisis et la convention de nommage utilisée.

Critères de réussite :
- La barre latérale est à gauche à partir de 900 pixels et devient une navigation fixée en bas sur mobile.
- Au moins trois cartes statistiques utilisent « repeat(auto-fit, minmax(...)) » et un tableau est enveloppé dans un conteneur « overflow-x: auto ».
- Un formulaire contient au moins trois champs avec des états « :focus-visible » et un bouton avec « :hover ».
- Toutes les couleurs, espaces et rayons passent par des variables CSS et les classes suivent BEM, sans identifiant ni « !important ».
- La page ne présente aucun défilement horizontal global à 360 pixels de large.
TXT,
            'exercise_hint' => 'Construis d’abord la version mobile à 360 pixels, puis ajoute la media query de 900 pixels. Si une barre de défilement horizontale apparaît, cherche l’élément qui déborde avec les outils de développement.',
            'exercise_solution' => <<<'CODE'
/* style.css
   HTML associé :
   <div class="app">
     <aside class="barre-laterale"><a href="#">Accueil</a><a href="#">Élèves</a><a href="#">Notes</a></aside>
     <main class="principal">
       <section class="stats"><article class="carte-stat">Élèves : 320</article> ... (3 cartes)</section>
       <div class="tableau-conteneur"><table class="tableau"><tr><th>Classe</th><th>Moyenne</th></tr>...</table></div>
       <form class="formulaire"><input class="champ" placeholder="Nom"> ... <button class="bouton">Ajouter</button></form>
     </main>
   </div>
*/
:root {
  --fond: #0f172a;
  --surface: #1e293b;
  --bordure: #334155;
  --texte: #e2e8f0;
  --accent: #38bdf8;
  --espace: 16px;
  --rayon: 12px;
}
*, *::before, *::after { box-sizing: border-box; }
body { margin: 0; background: var(--fond); color: var(--texte); font-family: Arial, sans-serif; }

.app { display: grid; grid-template-columns: 1fr; min-height: 100vh; padding-bottom: 64px; }
.barre-laterale {
  position: fixed; bottom: 0; left: 0; right: 0;
  display: flex; justify-content: space-around;
  background: var(--surface); border-top: 1px solid var(--bordure);
}
.barre-laterale a { padding: 18px 12px; color: var(--texte); text-decoration: none; }
.principal { padding: var(--espace); display: grid; gap: var(--espace); min-width: 0; }

@media (min-width: 900px) {
  .app { grid-template-columns: 220px 1fr; padding-bottom: 0; }
  .barre-laterale {
    position: static; flex-direction: column; justify-content: flex-start;
    border-top: 0; border-right: 1px solid var(--bordure);
  }
}

.stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: var(--espace); }
.carte-stat { padding: var(--espace); background: var(--surface); border: 1px solid var(--bordure); border-radius: var(--rayon); }

.tableau-conteneur { overflow-x: auto; }
.tableau { width: 100%; min-width: 480px; border-collapse: collapse; }
.tableau th, .tableau td { padding: 10px; text-align: left; border-bottom: 1px solid var(--bordure); }

.formulaire { display: grid; gap: 12px; max-width: 420px; }
.champ { padding: 10px; background: var(--fond); color: var(--texte); border: 1px solid var(--bordure); border-radius: 8px; }
.champ:focus-visible { outline: 3px solid var(--accent); outline-offset: 2px; }
.bouton { padding: 10px 20px; border: 0; border-radius: 8px; background: var(--accent); color: #0f172a; cursor: pointer; transition: transform 150ms ease; }
.bouton:hover { transform: translateY(-2px); }
CODE,
        ],
    ],
];
