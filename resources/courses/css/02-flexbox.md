---
title: Flexbox
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Pendant des années, aligner deux éléments côte à côte ou centrer quelque chose verticalement était un casse-tête. **Flexbox** a changé cela : c'est un mode de mise en page conçu pour distribuer l'espace et aligner des éléments le long d'**un seul axe**, horizontal ou vertical. Tu t'en serviras tous les jours : barre de navigation, ligne de boutons, carte avec pied de page, centrage parfait.

À la fin du chapitre, tu seras capable de :

- activer Flexbox avec `display: flex` et identifier le conteneur et ses éléments ;
- expliquer la différence entre l'axe principal et l'axe transversal ;
- positionner et espacer des éléments avec `justify-content`, `align-items` et `gap` ;
- gérer le retour à la ligne avec `flex-wrap` ;
- contrôler la taille des éléments avec `flex-grow`, `flex-shrink` et `flex-basis` ;
- réaliser des mises en page classiques : navbar, centrage, cartes égales, pied de page collé en bas.

Prérequis : le chapitre « Comprendre la cascade » (sélecteurs, modèle de boîte). Prévois deux heures. Un fichier HTML et un fichier CSS suffisent, ou l'éditeur en ligne CodePen.

## Le principe : un conteneur et ses éléments

Flexbox fonctionne avec une relation parent-enfant. Tu déclares le parent comme **conteneur flex**, et ses **enfants directs** deviennent des **éléments flex** :

```html
<nav class="menu">
  <a href="/">Accueil</a>
  <a href="/roadmaps">Roadmaps</a>
  <a href="/fiches">Fiches</a>
</nav>
```

```css
.menu {
  display: flex;
}
```

Sans autre règle, les trois liens se placent désormais **côte à côte** sur une ligne, au lieu de s'empiler comme le font les blocs. Seul le premier niveau d'enfants est concerné : les petits-enfants gardent leur comportement habituel, sauf si tu actives aussi Flexbox sur leur parent.

> **À retenir** : les propriétés de Flexbox se répartissent en deux familles. Certaines se posent sur le **conteneur** (`display`, `flex-direction`, `justify-content`, `align-items`, `gap`, `flex-wrap`), d'autres sur les **éléments** (`flex`, `flex-grow`, `align-self`, `order`).

## Les deux axes

Tout Flexbox repose sur deux axes perpendiculaires :

- l'**axe principal** (*main axis*) : la direction dans laquelle les éléments se suivent ;
- l'**axe transversal** (*cross axis*) : perpendiculaire à l'axe principal.

La propriété `flex-direction` choisit l'axe principal :

```css
.conteneur {
  display: flex;
  flex-direction: row;            /* par défaut : de gauche à droite */
  /* flex-direction: row-reverse;    de droite à gauche */
  /* flex-direction: column;         de haut en bas */
  /* flex-direction: column-reverse; de bas en haut */
}
```

Avec `row`, l'axe principal est horizontal et l'axe transversal vertical. Avec `column`, c'est l'inverse. C'est le point que tout le monde confond au début, alors retiens ceci :

| Propriété | Agit sur | En `row` | En `column` |
| --- | --- | --- | --- |
| `justify-content` | Axe principal | Horizontal | Vertical |
| `align-items` | Axe transversal | Vertical | Horizontal |

Quand tu inverses la direction, les deux propriétés échangent leur rôle visuel. Ne les apprends pas par « horizontal » et « vertical », mais par « principal » et « transversal ».

## Aligner et distribuer

### justify-content : l'axe principal

Cette propriété répartit l'espace libre **le long de l'axe principal** :

```css
.barre {
  display: flex;
  justify-content: space-between;
}
```

Les valeurs les plus utiles :

- `flex-start` (par défaut) : éléments groupés au début ;
- `flex-end` : groupés à la fin ;
- `center` : centrés ;
- `space-between` : premier collé au début, dernier collé à la fin, espaces égaux entre ;
- `space-around` : espaces égaux autour de chaque élément ;
- `space-evenly` : espaces strictement identiques partout.

`space-between` est la valeur reine de la barre de navigation : le logo à gauche, les liens à droite.

### align-items : l'axe transversal

```css
.barre {
  display: flex;
  align-items: center;
}
```

Valeurs : `stretch` (par défaut, les éléments s'étirent sur toute la hauteur), `flex-start`, `flex-end`, `center`, `baseline` (alignement sur la ligne de base du texte). Pour aligner verticalement un logo et un menu de hauteurs différentes, `center` est la réponse.

### Le centrage parfait

Le casse-tête historique tient en trois lignes :

```css
.hero {
  display: flex;
  justify-content: center;
  align-items: center;
  min-height: 100vh;
}
```

Le contenu de `.hero` est centré horizontalement et verticalement dans la hauteur de l'écran. (La propriété raccourcie `place-content: center` fait la même chose avec Grid.)

### gap : l'espacement propre

La propriété `gap` définit l'espace **entre** les éléments, sans marge à gérer ni espace parasite aux extrémités :

```css
.boutons {
  display: flex;
  gap: 1rem;
}
```

Les marges, elles, s'ajoutent aussi autour du premier et du dernier élément, ce qui oblige à des exceptions. Préfère `gap` : il est apparu dans Flexbox il y a plusieurs années et fonctionne dans tous les navigateurs actuels.

:::quiz
Dans un conteneur avec `flex-direction: column`, quelle propriété centre les éléments horizontalement ?
- [ ] justify-content: center
- [x] align-items: center
- [ ] align-content: space-between
- [ ] flex-wrap: wrap
> En colonne, l'axe principal est vertical : justify-content agit donc de haut en bas. L'axe transversal est horizontal, c'est align-items qui le gère.
:::

## Le retour à la ligne : flex-wrap

Par défaut, Flexbox essaie de tout faire tenir sur **une seule ligne**, quitte à écraser les éléments. Pour autoriser le passage à la ligne :

```css
.galerie {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.galerie > * {
  width: 200px;
}
```

(La règle `.galerie > *` cible tous les enfants directs.) Avec `wrap`, quand la ligne est pleine, les éléments suivants passent dessous. Dès qu'il y a plusieurs lignes, `align-content` répartit l'espace entre les lignes, tandis que `align-items` aligne les éléments à l'intérieur de chaque ligne. Le raccourci `flex-flow: row wrap` combine `flex-direction` et `flex-wrap`.

## La taille des éléments : flex-grow, shrink, basis

C'est la partie la plus puissante, et la plus mal comprise. Trois propriétés déterminent comment un élément occupe l'espace :

- **`flex-basis`** : la taille de départ sur l'axe principal (comme une largeur, mais prioritaire en Flexbox) ;
- **`flex-grow`** : la part d'espace libre excédentaire qu'il peut absorber (0 par défaut, il ne grandit pas) ;
- **`flex-shrink`** : sa capacité à rétrécir si l'espace manque (1 par défaut).

On les écrit presque toujours avec le raccourci `flex` :

```css
.item {
  flex: 1;              /* grow 1, shrink 1, basis 0 : partage équitable */
  flex: 0 0 200px;      /* ne grandit pas, ne rétrécit pas, taille fixe 200px */
  flex: 1 1 250px;      /* part de 250px, peut grandir et rétrécir */
}
```

Imagine un conteneur de 900 px avec trois éléments :

```css
.a { flex: 1; }
.b { flex: 1; }
.c { flex: 2; }
```

L'espace est divisé en 1 + 1 + 2 = 4 parts : `.a` et `.b` reçoivent 225 px, `.c` en reçoit 450. Le principe : `flex-grow` est un **poids** de répartition de l'espace libre.

Exemple classique : un champ de recherche qui prend toute la place restante à côté d'un bouton.

```css
.recherche {
  display: flex;
  gap: 0.5rem;
}
.recherche input {
  flex: 1;           /* occupe tout l'espace disponible */
}
.recherche button {
  flex: 0 0 auto;    /* garde sa taille naturelle */
}
```

> **Astuce** : `min-width: 0` sur un élément flex corrige un débordement fréquent. Par défaut, un élément flex ne rétrécit pas en dessous de la taille de son contenu, ce qui peut faire sortir un long texte ou une image de son conteneur.

### align-self et order

Un élément peut contredire l'alignement du conteneur avec `align-self` :

```css
.item-special {
  align-self: flex-end;
}
```

La propriété `order` change l'ordre visuel (par défaut 0, valeurs négatives en premier). Attention : elle ne change **pas** l'ordre de tabulation ni de lecture, ce qui crée un décalage trompeur pour le clavier et les lecteurs d'écran. Évite-la pour réorganiser du contenu qui compte.

:::quiz
Trois éléments dans un conteneur de 600 px ont `flex: 1`, `flex: 1` et `flex: 2`, avec une base à zéro. Quelle largeur reçoit le troisième ?
- [ ] 100 px
- [ ] 200 px
- [x] 300 px
- [ ] 400 px
> L'espace est partagé en 4 parts de 150 px : les deux premiers en reçoivent une chacun (150 px), le troisième deux (300 px).
:::

## Les mises en page classiques

### La barre de navigation

```html
<header class="entete">
  <a href="/" class="logo">DevRoad</a>
  <nav>
    <ul class="liens">
      <li><a href="/roadmaps">Roadmaps</a></li>
      <li><a href="/fiches">Fiches</a></li>
      <li><a href="/profil">Profil</a></li>
    </ul>
  </nav>
</header>
```

```css
.entete {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1rem 1.5rem;
}

.liens {
  display: flex;
  gap: 1.5rem;
  list-style: none;
  margin: 0;
  padding: 0;
}
```

Deux conteneurs flex imbriqués : l'en-tête écarte le logo et la navigation, la liste dispose ses liens en ligne. Flexbox s'applique à autant de niveaux que nécessaire.

### Des cartes de même hauteur avec le bouton en bas

```css
.cartes {
  display: flex;
  flex-wrap: wrap;
  gap: 1rem;
}

.carte {
  flex: 1 1 250px;
  display: flex;
  flex-direction: column;
  padding: 1rem;
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.carte .action {
  margin-top: auto;
}
```

Par défaut (`align-items: stretch`), les cartes d'une même ligne ont la même hauteur. En faisant de chaque carte une colonne flex, la marge automatique en haut du bouton le pousse tout en bas, qu'importe la longueur du texte. `margin-top: auto` absorbe l'espace libre : une astuce de professionnels.

### Le pied de page collé en bas

```css
body {
  min-height: 100vh;
  display: flex;
  flex-direction: column;
}

main {
  flex: 1;
}
```

Sur une page courte, le `main` grandit pour occuper l'espace restant et repousse le `footer` au bas de la fenêtre. Sur une page longue, rien ne change.

### Flexbox ou Grid ?

Flexbox est conçu pour **une dimension** : une ligne ou une colonne d'éléments. Pour une mise en page à **deux dimensions** (lignes et colonnes alignées simultanément), le chapitre suivant sur Grid est plus adapté. Une règle simple : si tu penses en « rangée » ou « pile », choisis Flexbox ; si tu penses en « tableau », choisis Grid. Les deux se combinent très bien.

## Atelier guidé : la navbar et les cartes de DevRoad

Compte une heure et demie.

1. Crée `flex.html` et `css/flex.css`. Mets en place `box-sizing: border-box` et des variables de couleur (chapitre précédent).
2. Construis l'en-tête de l'exemple ci-dessus (logo + navigation). Ajoute `justify-content: space-between` et `align-items: center`.
3. Transforme la liste de liens en rangée avec `gap`. Retire les puces avec `list-style: none`.
4. Ajoute un bouton « Connexion » dans la navigation, séparé des autres liens grâce à `margin-left: auto`.
5. Crée une section de six cartes de roadmap (titre, texte de longueurs différentes, bouton « Commencer ») avec `flex-wrap`.
6. Donne `flex: 1 1 250px` aux cartes et vérifie qu'elles se répartissent selon la largeur de la fenêtre (redimensionne-la).
7. Fais de chaque carte une colonne flex et pousse le bouton en bas avec `margin-top: auto`.
8. Ajoute au-dessus des cartes une barre de recherche : un champ avec `flex: 1` et un bouton à taille naturelle.
9. Mets en place la structure de page `header`, `main`, `footer` avec le pied de page collé en bas, et teste avec très peu de contenu.
10. Ouvre les outils de développement : clique sur le badge `flex` à côté d'un conteneur dans le panneau Éléments pour visualiser les axes et les espaces.

Auto-évaluation : pour chaque propriété du conteneur, peux-tu dire sur quel axe elle agit ? Peux-tu expliquer le partage de l'espace avec `flex: 1` et `flex: 2` ?

## Erreurs fréquentes

- **Appliquer les propriétés au mauvais niveau.** `justify-content` se met sur le parent, pas sur les enfants.
- **Confondre les axes** après un `flex-direction: column`.
- **Oublier `flex-wrap`.** Les éléments s'écrasent sur une seule ligne au lieu de passer dessous.
- **Utiliser des marges au lieu de `gap`.** On se retrouve avec des exceptions pour le premier ou le dernier élément.
- **Croire que `flex: 1` signifie « largeur 100 % ».** C'est une part de l'espace libre, pas une taille.
- **Un contenu qui déborde.** Pense à `min-width: 0` et à `flex-wrap`.
- **Réordonner avec `order`.** L'ordre visuel et l'ordre de lecture divergent.
- **Vouloir faire une grille complexe en Flexbox.** Passe à Grid.

## Bonnes pratiques

- Réfléchis d'abord à l'axe principal, puis choisis `justify-content` et `align-items`.
- Utilise `gap` pour tous les espacements entre éléments flex.
- Combine `flex-wrap` et `flex: 1 1 <taille>` pour des mises en page qui s'adaptent sans media query.
- Garde le HTML dans un ordre logique : n'utilise pas `order` pour compenser une mauvaise structure.
- Imbrique plusieurs conteneurs flex, chacun avec une seule responsabilité.
- Inspecte les conteneurs avec l'outil visuel Flexbox du navigateur.
- Choisis Grid dès que tu raisonnes sur deux dimensions.

## À retenir

- `display: flex` rend le parent conteneur et ses enfants directs éléments flex.
- Il y a un axe principal (`flex-direction`) et un axe transversal : `justify-content` agit sur le premier, `align-items` sur le second.
- `gap` espace proprement ; `flex-wrap: wrap` autorise le retour à la ligne.
- `flex: grow shrink basis` distribue l'espace ; `flex: 1` partage équitablement, `margin-top: auto` pousse un élément vers le bas.
- Centrer parfaitement = `display: flex; justify-content: center; align-items: center`.
- Flexbox gère une dimension, Grid en gère deux.
