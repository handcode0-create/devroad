---
title: Responsive design
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Aujourd'hui, une majorité du trafic web mondial vient de téléphones, et c'est encore plus vrai en Afrique de l'Ouest où le smartphone est souvent le seul écran, avec des forfaits data limités et des réseaux variables. Un site qui n'est pas conçu pour le mobile est un site qui perd la plupart de ses visiteurs. Le **responsive design** consiste à faire en sorte qu'une même page s'adapte à toutes les tailles d'écran, du petit téléphone au grand moniteur.

À la fin du chapitre, tu seras capable de :

- expliquer l'approche **mobile first** et pourquoi elle est préférable ;
- configurer correctement le viewport ;
- écrire des **media queries** et choisir des points de rupture pertinents ;
- construire des mises en page fluides avec `%`, `min()`, `max()` et `clamp()` ;
- adapter les images, la typographie et les espacements ;
- utiliser les **container queries** pour des composants autonomes ;
- tester sur plusieurs appareils et conditions de réseau.

Prérequis : les chapitres « Comprendre la cascade », « Flexbox » et « Grid ». Prévois deux heures et demie.

## Le viewport : la base de tout

Sans réglage, un navigateur mobile fait semblant d'avoir un écran d'environ 980 pixels de large et rétrécit la page pour la faire tenir. Ton site apparaît en miniature illisible. La balise suivante, à placer dans le `head` de chaque page, désactive ce comportement :

```html
<meta name="viewport" content="width=device-width, initial-scale=1">
```

Elle dit : « la largeur de la page est celle de l'écran réel, sans zoom initial ». Sans elle, aucune media query ne fonctionnera comme prévu sur téléphone. Ne mets jamais `user-scalable=no` ni `maximum-scale=1` : tu empêcherais les personnes malvoyantes de zoomer.

## Mobile first

L'approche **mobile first** consiste à écrire d'abord le CSS pour le plus petit écran, puis à **ajouter** des règles pour les écrans plus grands avec `min-width`.

```css
/* Base : mobile, sans media query */
.cartes {
  display: grid;
  grid-template-columns: 1fr;
  gap: 1rem;
}

/* Tablette et plus */
@media (min-width: 768px) {
  .cartes {
    grid-template-columns: repeat(2, 1fr);
  }
}

/* Ordinateur et plus */
@media (min-width: 1100px) {
  .cartes {
    grid-template-columns: repeat(3, 1fr);
  }
}
```

Pourquoi cet ordre est-il meilleur que d'écrire pour le bureau puis de défaire pour le mobile (`max-width`) ?

- le mobile, avec ses contraintes, force à **prioriser** le contenu essentiel ;
- le code de base est simple, et les appareils les plus modestes téléchargent le moins de règles à interpréter ;
- on **ajoute** des améliorations plutôt que de défaire, ce qui produit moins de surcharges et de conflits de spécificité ;
- la majorité des visiteurs sont sur mobile : c'est leur expérience qui compte le plus.

> **À retenir** : mobile first = écrire le style de base pour le téléphone, puis utiliser `@media (min-width: …)` pour enrichir à mesure que l'écran grandit.

## Les media queries

Une **media query** applique un bloc de règles seulement si une condition est vraie :

```css
@media (min-width: 768px) {
  /* appliqué à partir de 768 px de largeur */
}

@media (min-width: 600px) and (max-width: 900px) {
  /* seulement entre 600 et 900 */
}

@media (orientation: landscape) {
  /* appareil en mode paysage */
}
```

Au-delà de la largeur, des requêtes sur les **préférences de l'utilisateur** améliorent l'accessibilité :

```css
@media (prefers-color-scheme: dark) {
  :root {
    --fond: #0f172a;
    --texte: #f1f5f9;
  }
}

@media (prefers-reduced-motion: reduce) {
  html { scroll-behavior: auto; }
}

@media (hover: none) {
  /* appareils tactiles : pas de survol */
}

@media print {
  nav, footer { display: none; }
}
```

### Choisir ses points de rupture

Il n'existe pas de liste « officielle ». Des repères courants, que tu peux adopter :

| Nom | Largeur minimale | Cible |
| --- | --- | --- |
| Base | Aucune | Téléphones |
| Moyen | 640 à 768 px | Grands téléphones, tablettes |
| Large | 1024 à 1100 px | Ordinateurs portables |
| Très large | 1280 px et plus | Grands écrans |

Mais la meilleure méthode est de **laisser le contenu décider** : redimensionne la fenêtre, et ajoute un point de rupture là où la mise en page devient mauvaise (lignes trop longues, éléments écrasés), pas parce qu'un appareil précis existe. Trois ou quatre points de rupture suffisent presque toujours.

Place les valeurs dans une convention du projet et utilise des `em` si tu veux que les points de rupture respectent aussi la taille de police choisie par l'utilisateur (`@media (min-width: 48em)`).

:::quiz
Dans une approche mobile first, quelle media query utilises-tu pour adapter le style aux grands écrans ?
- [ ] @media (max-width: 1100px)
- [x] @media (min-width: 1100px)
- [ ] @media (orientation: landscape)
- [ ] @media screen
> Le style de base vise le mobile ; on l'enrichit à partir d'une largeur minimale avec min-width.
:::

## Des mises en page fluides

Les media queries sont des interrupteurs ; la fluidité vient d'abord des unités et des fonctions de CSS.

### Largeurs flexibles

Évite les largeurs fixes en pixels pour les conteneurs. Préfère des maximums :

```css
.conteneur {
  width: 100%;
  max-width: 70rem;
  margin-inline: auto;
  padding-inline: 1rem;
}
```

Sur petit écran, le conteneur occupe toute la largeur moins ses marges internes. Sur grand écran, il plafonne à 70 rem et se centre.

### min(), max() et clamp()

Ces fonctions permettent des valeurs qui s'adaptent **sans media query** :

```css
.titre {
  /* jamais en dessous de 1.75rem, jamais au-dessus de 3rem, fluide entre les deux */
  font-size: clamp(1.75rem, 4vw + 1rem, 3rem);
}

.section {
  padding-block: clamp(2rem, 8vw, 6rem);
}

.texte {
  width: min(100% - 2rem, 65ch);
}
```

`clamp(minimum, préféré, maximum)` fixe une valeur qui suit une formule (ici liée à la largeur de la fenêtre) mais reste bornée. C'est la technique moderne pour une **typographie fluide** : un titre qui grandit en douceur plutôt que de sauter d'une taille à l'autre aux points de rupture.

### Flexbox et Grid font déjà le travail

Souviens-toi des deux motifs vus aux chapitres précédents. Ils produisent un comportement responsive sans une seule media query :

```css
.rangee {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}
.rangee > * { flex: 1 1 15rem; }

.galerie {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(min(15rem, 100%), 1fr));
  gap: 1rem;
}
```

Garde les media queries pour les **changements de structure** (menu qui se replie, barre latérale qui passe sous le contenu), et laisse la fluidité aux unités et aux grilles.

## Images et médias adaptables

Une image plus large que son conteneur déborde. La règle de base à appliquer à tout projet :

```css
img,
video {
  max-width: 100%;
  height: auto;
  display: block;
}
```

`max-width: 100%` empêche le débordement, `height: auto` conserve les proportions, `display: block` supprime l'espace parasite sous une image en ligne. Pour réserver la place avant le chargement et éviter les sauts de page, ajoute `aspect-ratio` :

```css
.vignette {
  width: 100%;
  aspect-ratio: 16 / 9;
  object-fit: cover;   /* recadre l'image pour remplir sans la déformer */
}
```

Côté HTML, `srcset`, `sizes` et `picture` (cours HTML) servent des fichiers plus légers aux petits écrans. Pour les arrière-plans CSS, une media query peut changer l'image :

```css
.hero { background-image: url("img/hero-480.webp"); }

@media (min-width: 900px) {
  .hero { background-image: url("img/hero-1600.webp"); }
}
```

## Un menu qui s'adapte

Exemple complet : sur mobile, le menu est une colonne cachée que l'on déplie ; sur grand écran, c'est une rangée visible.

```css
.menu-toggle { display: inline-flex; }

.menu {
  display: none;
  flex-direction: column;
  gap: 0.5rem;
  list-style: none;
  padding: 0;
}
.menu.ouvert { display: flex; }

@media (min-width: 768px) {
  .menu-toggle { display: none; }
  .menu {
    display: flex;
    flex-direction: row;
    gap: 1.5rem;
  }
}
```

Le JavaScript se limite à basculer la classe `ouvert` et l'attribut `aria-expanded` (cours HTML, chapitre accessibilité). Les cibles tactiles doivent mesurer au moins 44 par 44 pixels, avec assez d'espace entre elles pour éviter les clics manqués.

## Container queries : des composants vraiment autonomes

Une media query regarde la **fenêtre**. Or un composant, comme une carte de roadmap, peut se trouver dans une colonne étroite sur un grand écran, ou dans une zone large sur un petit. Les **container queries** lui permettent de réagir à la taille de **son conteneur** :

```css
.zone-carte {
  container-type: inline-size;
  container-name: carte;
}

.carte {
  display: grid;
  gap: 0.75rem;
}

@container carte (min-width: 28rem) {
  .carte {
    grid-template-columns: 8rem 1fr;
    align-items: center;
  }
}
```

Quand `.zone-carte` atteint 28 rem de large, la carte passe en deux colonnes (image à gauche, texte à droite), qu'importe la taille de l'écran. Ces requêtes sont supportées par tous les navigateurs actuels. Elles rendent les composants réutilisables n'importe où, un atout majeur pour un design system.

:::quiz
Quand préfères-tu une container query à une media query ?
- [ ] Quand tu veux cibler un type d'appareil précis
- [ ] Quand tu veux changer la couleur du thème
- [x] Quand un composant doit s'adapter à l'espace dont il dispose, quelle que soit la taille de l'écran
- [ ] Quand tu veux imprimer la page
> Une container query observe le conteneur du composant, ce qui le rend indépendant de la fenêtre et réutilisable dans différents contextes.
:::

## Tester le responsive

Un site responsive se vérifie, il ne se suppose pas :

1. **Mode appareil** des outils de développement (icône téléphone) : redimensionne librement et choisis des profils d'appareils.
2. **Limitation du réseau** (onglet Réseau, profil « 3G rapide ») : constate le poids et le temps de chargement.
3. **Un vrai téléphone**, de préférence modeste : c'est la seule façon de sentir la fluidité, la taille des cibles et la lisibilité en plein soleil.
4. **Le zoom du navigateur** à 200 % et la taille de police agrandie dans les réglages.
5. **Les deux orientations** et, si pertinent, l'impression.
6. **Aucun défilement horizontal** à 320 px de large : c'est un bon test de robustesse.

Un débordement horizontal vient presque toujours d'un élément trop large (une image, un tableau, une longue URL, une largeur fixe). L'inspecteur révèle le coupable ; pour un texte long, `overflow-wrap: anywhere` aide.

## Atelier guidé : rendre la page DevRoad responsive

Compte une heure et demie. Reprends la page du chapitre Grid, ou construis une page d'accueil avec en-tête, grille de cartes, section d'appel à l'action et pied de page.

1. Vérifie la balise viewport. Ouvre le mode appareil et regarde le résultat à 360 px avant toute modification.
2. Réécris le CSS en mobile first : une colonne, texte lisible, espacements généreux, cibles tactiles de 44 px minimum.
3. Mets en place un `.conteneur` avec `max-width`, `margin-inline: auto` et `padding-inline`.
4. Remplace le titre principal par une taille fluide avec `clamp()`.
5. Ajoute le menu qui se déplie sur mobile et devient une rangée à partir de 768 px.
6. Fais passer la grille de cartes à 2 puis 3 colonnes avec deux media queries `min-width` (ou avec `auto-fit`).
7. Applique à toutes les images `max-width: 100%`, `height: auto` et un `aspect-ratio` aux vignettes.
8. Transforme une carte en composant réactif grâce à une container query.
9. Ajoute un thème sombre via `prefers-color-scheme` en redéfinissant tes variables.
10. Teste à 320, 360, 768, 1024 et 1440 px, avec la limitation réseau activée. Corrige tout défilement horizontal.
11. Lance Lighthouse en mode mobile et note les scores.

Auto-évaluation : peux-tu justifier chaque point de rupture par un problème observé dans le contenu ? Ta page reste-t-elle utilisable à 200 % de zoom ?

## Erreurs fréquentes

- **Oublier la balise viewport.** Aucune media query ne se comporte comme prévu sur mobile.
- **Raisonner par appareil** (« iPhone », « iPad ») au lieu de laisser le contenu dicter les ruptures.
- **Écrire du CSS desktop puis tout défaire** avec `max-width` : le code devient lourd et conflictuel.
- **Utiliser des largeurs fixes en pixels** pour des conteneurs ou des images.
- **Désactiver le zoom** avec `user-scalable=no`.
- **Des cibles tactiles minuscules**, collées entre elles.
- **Multiplier les points de rupture.** Plus il y en a, plus le style devient difficile à maintenir.
- **Ne tester qu'à la souris, sur un écran confortable.**

## Bonnes pratiques

- Conçois d'abord pour 360 px, puis enrichis avec `min-width`.
- Laisse `clamp()`, `min()`, Flexbox et Grid assurer la fluidité ; garde les media queries pour la structure.
- Limite la largeur des lignes de texte (`max-width: 65ch`).
- Exprime les tailles en `rem` et respecte les préférences utilisateur (taille, contraste, mouvement).
- Optimise le poids : images adaptées, WebP, `loading="lazy"`, peu de polices.
- Utilise les container queries pour les composants réutilisables.
- Teste sur un vrai téléphone et sous une connexion lente.

## À retenir

- La balise viewport est indispensable pour que le responsive fonctionne sur mobile.
- **Mobile first** : base simple pour le petit écran, puis `@media (min-width: …)` pour enrichir.
- Choisis tes points de rupture selon le contenu, pas selon les appareils.
- `clamp()`, `min()`, `max()`, `auto-fit` et `flex-wrap` rendent les mises en page fluides sans media query.
- Les images utilisent `max-width: 100%`, `height: auto` et `aspect-ratio`.
- Les container queries adaptent un composant à son conteneur.
- Teste réellement : mode appareil, réseau lent, vrai téléphone, zoom à 200 %.
