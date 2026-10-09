---
title: Grid
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Flexbox range des éléments sur une ligne ou une colonne. **CSS Grid**, lui, dessine une **grille** : des lignes et des colonnes que tu définis une fois, puis dans lesquelles tu places tes éléments. C'est l'outil idéal pour les mises en page de pages entières (en-tête, barre latérale, contenu, pied de page), les galeries et les tableaux de bord. Là où Flexbox part du contenu, Grid part de la **structure**.

À la fin du chapitre, tu seras capable de :

- créer une grille avec `display: grid`, `grid-template-columns` et `grid-template-rows` ;
- utiliser l'unité fractionnaire `fr`, `repeat()` et `minmax()` ;
- espacer les cellules avec `gap` ;
- placer des éléments par numéros de lignes ou avec des zones nommées ;
- produire une grille responsive sans media query grâce à `auto-fit` et `auto-fill` ;
- aligner le contenu des cellules et décider entre Grid et Flexbox.

Prérequis : « Comprendre la cascade » et « Flexbox ». Prévois deux heures.

## Le principe : définir une grille

Comme avec Flexbox, il y a un **conteneur grid** et des **éléments** (ses enfants directs). La différence : c'est le conteneur qui décrit la structure.

```html
<div class="grille">
  <div>1</div>
  <div>2</div>
  <div>3</div>
  <div>4</div>
  <div>5</div>
  <div>6</div>
</div>
```

```css
.grille {
  display: grid;
  grid-template-columns: 200px 200px 200px;
  gap: 1rem;
}
```

Le conteneur définit **trois colonnes** de 200 px. Les six éléments se rangent automatiquement, de gauche à droite puis ligne par ligne : deux rangées de trois. Tu n'as pas déclaré de lignes : Grid en crée de nouvelles à mesure que nécessaire (on les appelle les lignes **implicites**).

Quelques mots de vocabulaire indispensables :

- une **piste** (*track*) est une colonne ou une rangée ;
- une **cellule** est l'intersection d'une colonne et d'une rangée ;
- une **zone** est un rectangle formé de plusieurs cellules ;
- les **lignes de grille** sont les traits qui délimitent les pistes, numérotées à partir de 1.

> **À retenir** : une grille de trois colonnes possède **quatre** lignes verticales, numérotées de 1 à 4. Ce décalage d'un cran sert quand tu places un élément par numéros.

## L'unité fr et la fonction repeat

Les largeurs fixes en pixels ne s'adaptent pas à l'écran. Grid apporte l'unité **`fr`** (*fraction*), qui représente une part de l'espace libre :

```css
.grille {
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 1rem;
}
```

Trois colonnes de largeurs égales, qui se partagent la largeur du conteneur après déduction des écarts. Les proportions se mélangent librement :

```css
.page {
  display: grid;
  grid-template-columns: 250px 1fr;   /* barre latérale fixe + contenu souple */
}

.stats {
  display: grid;
  grid-template-columns: 2fr 1fr 1fr; /* première colonne deux fois plus large */
}
```

Pour éviter de répéter, la fonction `repeat()` :

```css
grid-template-columns: repeat(3, 1fr);       /* 1fr 1fr 1fr */
grid-template-columns: repeat(4, 100px);     /* quatre colonnes de 100px */
grid-template-columns: 100px repeat(2, 1fr); /* mélange possible */
```

### minmax() : un intervalle de taille

`minmax(min, max)` fixe une taille minimale et maximale pour une piste :

```css
grid-template-columns: minmax(200px, 1fr) 3fr;
grid-template-rows: minmax(100px, auto);
```

La première colonne ne descend jamais sous 200 px mais peut grandir d'une fraction. Pour les rangées, `minmax(100px, auto)` garantit 100 px au minimum et laisse la hauteur grandir avec le contenu, ce qui évite un texte qui déborde.

### gap

Comme en Flexbox, `gap` espace les pistes : `gap: 1rem` (les deux axes), ou `row-gap` et `column-gap` séparément (`gap: 2rem 1rem`).

:::quiz
Que produit `grid-template-columns: 1fr 2fr` dans un conteneur de 900 px sans gap ?
- [ ] Deux colonnes de 450 px
- [x] Une colonne de 300 px et une de 600 px
- [ ] Une colonne de 100 px et une de 200 px
- [ ] Une colonne de 600 px et une de 300 px
> L'espace est divisé en 3 fractions de 300 px : la première colonne en prend une, la seconde deux.
:::

## Placer des éléments

Par défaut, Grid place les éléments dans l'ordre. Mais tu peux décider précisément où va chacun.

### Par numéros de lignes

```css
.entete {
  grid-column: 1 / 4;      /* de la ligne 1 à la ligne 4 : couvre trois colonnes */
}

.vedette {
  grid-column: 1 / span 2; /* commence à la ligne 1 et s'étend sur 2 colonnes */
  grid-row: span 2;        /* s'étend sur 2 rangées */
}

.pied {
  grid-column: 1 / -1;     /* de la première à la dernière ligne : toute la largeur */
}
```

`grid-column: 1 / -1` est un idiome précieux : l'élément occupe toute la largeur quel que soit le nombre de colonnes. `-1` désigne la dernière ligne.

### Par zones nommées

La méthode la plus lisible pour une page entière : tu **dessines** ta mise en page en texte.

```html
<div class="app">
  <header>En-tête</header>
  <nav>Menu</nav>
  <main>Contenu</main>
  <aside>Infos</aside>
  <footer>Pied</footer>
</div>
```

```css
.app {
  display: grid;
  grid-template-columns: 220px 1fr 200px;
  grid-template-rows: auto 1fr auto;
  grid-template-areas:
    "entete entete entete"
    "menu   contenu infos"
    "pied   pied    pied";
  min-height: 100vh;
  gap: 1rem;
}

.app > header { grid-area: entete; }
.app > nav    { grid-area: menu; }
.app > main   { grid-area: contenu; }
.app > aside  { grid-area: infos; }
.app > footer { grid-area: pied; }
```

Chaque chaîne représente une rangée, chaque mot une cellule. Un nom répété (`entete entete entete`) forme une zone rectangulaire. Un point (`.`) laisse une cellule vide. Pour un mobile, il suffira de redéfinir seulement `grid-template-areas` et `grid-template-columns` dans une media query (chapitre suivant) : le HTML ne bouge pas.

## Les grilles qui s'adaptent seules

La combinaison la plus utile de Grid pour un développeur web :

```css
.galerie {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
  gap: 1rem;
}
```

Lis-la ainsi : « crée autant de colonnes que possible, chacune d'au moins 240 px, qui se partagent l'espace restant à parts égales ». Sur un téléphone de 360 px : une colonne. Sur une tablette : deux ou trois. Sur un grand écran : quatre ou cinq. **Zéro media query.**

La différence entre les deux mots-clés :

- `auto-fill` crée autant de colonnes que possible, y compris **vides** si peu d'éléments existent ;
- `auto-fit` fait de même, mais **replie** les colonnes vides pour que les éléments présents s'étirent.

Avec peu d'éléments, `auto-fit` les étire pour occuper la largeur, `auto-fill` les laisse à leur taille. Choisis `auto-fit` pour des cartes qui doivent remplir la ligne.

```css
.galerie > .grande {
  grid-column: span 2;   /* une carte plus large dans la galerie */
}
```

> **Attention** : si `minmax(240px, 1fr)` est plus large que l'écran, tu obtiens un débordement horizontal sur petit mobile. Pour un cas strict, écris `minmax(min(240px, 100%), 1fr)`.

## Aligner dans une grille

Les propriétés d'alignement de Flexbox ont des équivalents, avec une nuance : Grid a deux jeux, un pour les **cellules** (les éléments dans leur zone), un pour la **grille entière** dans son conteneur.

| Propriété | Agit sur | Exemple |
| --- | --- | --- |
| `justify-items` | Les éléments dans leur cellule, axe horizontal | `center` |
| `align-items` | Les éléments dans leur cellule, axe vertical | `center` |
| `place-items` | Raccourci des deux | `center` |
| `justify-self` / `align-self` | Un seul élément | `end` |
| `justify-content` / `align-content` | La grille entière si elle est plus petite que le conteneur | `space-between` |

```css
.centre {
  display: grid;
  place-items: center;
  min-height: 100vh;
}
```

Voilà le centrage parfait en **deux** lignes : `place-items: center` suffit pour un élément unique dans un conteneur grid.

## Les lignes implicites : grid-auto-rows et grid-auto-flow

Quand tu ne déclares pas de rangées, Grid en crée à la demande, de hauteur automatique. Pour les contrôler :

```css
.liste {
  display: grid;
  grid-template-columns: repeat(3, 1fr);
  grid-auto-rows: minmax(120px, auto);
}
```

Chaque nouvelle rangée mesurera au moins 120 px et grandira avec son contenu. `grid-auto-flow: dense` demande à Grid de combler les trous laissés par de grands éléments, au prix d'un ordre visuel qui s'écarte de l'ordre du HTML : à utiliser avec prudence, comme `order` en Flexbox.

:::quiz
Quelle déclaration crée une grille responsive de colonnes d'au moins 200 px, sans media query ?
- [ ] grid-template-columns: repeat(3, 200px)
- [ ] grid-template-columns: 1fr 1fr 1fr
- [x] grid-template-columns: repeat(auto-fit, minmax(200px, 1fr))
- [ ] grid-auto-flow: column
> Le trio repeat, auto-fit et minmax ajuste automatiquement le nombre de colonnes à la largeur disponible tout en imposant une taille minimale.
:::

## Grid ou Flexbox ?

Les deux technologies se complètent. Voici un guide de décision :

| Situation | Choix |
| --- | --- |
| Barre de navigation, rangée de boutons | Flexbox |
| Centrer un élément | Flexbox ou Grid |
| Alignement sur une seule direction | Flexbox |
| Mise en page de page entière | Grid |
| Galerie de cartes alignées en lignes **et** colonnes | Grid |
| Contenu dont la taille dicte la mise en page | Flexbox |
| Structure dont la grille dicte la taille du contenu | Grid |

Dans DevRoad, par exemple, la page d'une roadmap se pose très bien avec Grid (barre latérale de chapitres et contenu), la liste de cartes aussi, tandis que l'en-tête et les groupes de boutons relèvent de Flexbox. Rien n'interdit d'imbriquer : un élément de grille peut être lui-même un conteneur flex.

> **Astuce** : dans les outils de développement de Chrome et Firefox, un badge `grid` à côté du conteneur active une superposition qui affiche les lignes, leurs numéros et les noms de zones. Utilise-le systématiquement.

## Atelier guidé : le tableau de bord DevRoad

Compte une heure et demie.

1. Crée `grid.html` et `css/grid.css` avec `box-sizing: border-box` et des variables.
2. Écris la structure `header`, `nav`, `main`, `aside`, `footer` dans un conteneur `.app`.
3. Pose la grille avec `grid-template-areas` comme dans le cours : en-tête et pied sur toute la largeur, menu, contenu et infos au milieu.
4. Active la superposition de grille dans les outils de développement et repère les lignes et les noms.
5. Dans `main`, crée une section `.cartes` de huit cartes de roadmap avec `repeat(auto-fit, minmax(240px, 1fr))` et `gap: 1rem`.
6. Fais en sorte que la première carte soit plus grande avec `grid-column: span 2` et `grid-row: span 2`.
7. Dans `aside`, crée une grille `.stats` de quatre statistiques (chapitres lus, jours consécutifs, quiz réussis, badges) en 2 colonnes.
8. Utilise `grid-column: 1 / -1` sur un titre de section pour qu'il occupe toute la largeur de la grille de cartes.
9. Centre le contenu de chaque statistique avec `place-items: center`.
10. Redimensionne la fenêtre. Note à quelle largeur la disposition devient mauvaise : c'est le point de départ du prochain chapitre.

Auto-évaluation : peux-tu expliquer sans aide la différence entre `auto-fit` et `auto-fill` ? Sais-tu décrire quand tu choisirais Grid plutôt que Flexbox ?

## Erreurs fréquentes

- **Oublier que seuls les enfants directs deviennent des éléments de grille.**
- **Se tromper dans les numéros de lignes.** Trois colonnes, ce sont quatre lignes, donc `1 / 4`.
- **Mettre des tailles fixes en pixels partout.** Utilise `fr` et `minmax` pour la souplesse.
- **Un nom de zone avec une forme non rectangulaire** dans `grid-template-areas` : la déclaration est entièrement ignorée.
- **Des rangées de hauteur fixe qui coupent le contenu.** Préfère `minmax(…, auto)`.
- **Utiliser `grid-auto-flow: dense`** sans mesurer l'impact sur l'ordre de lecture.
- **Oublier `min-width: 0`** sur un élément de grille qui contient un long texte ou un tableau : il élargit la colonne.

## Bonnes pratiques

- Dessine ta mise en page sur papier, puis traduis-la en colonnes, rangées ou zones.
- Utilise `fr`, `minmax()` et `repeat()` plutôt que des pixels fixes.
- Préfère `grid-template-areas` pour les gabarits de page : le code se lit comme un plan.
- Pour les listes de cartes, adopte le motif `repeat(auto-fit, minmax(…, 1fr))`.
- Utilise `gap` plutôt que des marges.
- Garde l'ordre du HTML logique et ne le contredis pas visuellement.
- Combine Grid pour la structure et Flexbox pour les composants.

## À retenir

- `display: grid` avec `grid-template-columns` et `grid-template-rows` dessine une grille à deux dimensions.
- L'unité `fr`, `repeat()` et `minmax()` rendent les pistes souples ; `gap` les espace.
- On place les éléments par numéros de lignes (`grid-column: 1 / -1`) ou par zones nommées (`grid-template-areas`).
- `repeat(auto-fit, minmax(240px, 1fr))` crée une grille responsive sans media query.
- `place-items: center` centre en une ligne ; les propriétés `justify` et `align` (items, self, content) alignent cellules et grille.
- Grid pour la structure en deux dimensions, Flexbox pour les rangées ou colonnes simples ; les deux se combinent.
