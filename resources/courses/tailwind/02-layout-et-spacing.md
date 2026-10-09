---
title: Layout et spacing
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Une belle interface repose d'abord sur une bonne **mise en page** : où se placent les éléments, quelle distance les sépare, comment ils s'alignent. Les couleurs viennent après. Ce chapitre te donne les outils de Tailwind pour organiser une page avec **Flexbox** et **Grid**, gérer les espacements proprement et contrôler la taille des blocs.

À la fin du chapitre, tu seras capable de :

- comprendre le modèle de boîte et utiliser `p-`, `m-`, `gap-` et `space-` à bon escient ;
- aligner des éléments avec **Flexbox** (`flex`, `justify-`, `items-`, `flex-col`, `flex-wrap`) ;
- construire des grilles avec **Grid** (`grid-cols-`, `col-span-`, `gap-`) ;
- centrer un bloc ou un contenu horizontalement et verticalement ;
- contrôler largeur, hauteur, débordement et positionnement ;
- construire une mise en page classique : en-tête, barre latérale, contenu, pied de page.

Prérequis : le chapitre « Découvrir Tailwind », avec un projet Tailwind v3 fonctionnel. Prévois deux heures.

## Le modèle de boîte

Chaque élément HTML est une boîte composée de quatre couches, de l'intérieur vers l'extérieur : le **contenu**, le **padding** (espace interne), la **bordure** et la **marge** (espace externe).

```html
<div class="m-4 border-2 border-slate-300 p-6">Contenu</div>
```

Ici, `p-6` éloigne le texte de la bordure, et `m-4` éloigne la boîte de ses voisines. Règle pratique : **le padding dépend du composant**, **la marge dépend de son voisinage**. Une bonne carte porte son propre padding, mais c'est son parent qui décide de l'espace entre deux cartes.

Par défaut, Tailwind met `box-sizing: border-box` partout (via Preflight) : la largeur d'un élément inclut son padding et sa bordure. Un `w-64 p-4` fait donc bien 16 rem de large, pas davantage.

## Margin, padding, gap et space : qui fait quoi

Quatre outils d'espacement, souvent confondus :

| Outil | Rôle | Exemple |
| --- | --- | --- |
| `p-` | espace interne d'une boîte | `p-4`, `px-6`, `pt-2` |
| `m-` | espace externe d'une boîte | `mt-8`, `mx-auto`, `-mt-2` |
| `gap-` | espace entre les enfants d'un conteneur flex ou grid | `gap-4`, `gap-x-6`, `gap-y-2` |
| `space-x-` / `space-y-` | marge entre les enfants (alternative hors flex ou grid) | `space-y-4` |

Pour espacer les enfants d'un conteneur, **préfère `gap-`** dès que le parent est en `flex` ou `grid` : c'est plus propre et plus prévisible. Utilise `space-y-4` pour une pile verticale simple (un formulaire, une liste de paragraphes) :

```html
<form class="space-y-4">
  <div>...</div>
  <div>...</div>
  <button>Envoyer</button>
</form>
```

Une marge négative est possible avec un tiret : `-mt-4`. Et `mx-auto` centre horizontalement un bloc dont la largeur est limitée (`max-w-md mx-auto`).

> **Astuce** : choisis une échelle réduite et respecte-la : par exemple 2, 4, 6, 8, 12 et 16. Une interface avec peu de valeurs différentes paraît plus ordonnée qu'une interface où chaque espace est légèrement différent.

:::quiz
Quelle classe espace uniformément les enfants d'un conteneur en `flex` ?
- [ ] `p-4`
- [ ] `m-4`
- [x] `gap-4`
- [ ] `w-4`
> `gap-` ajoute un espace entre les enfants directs d'un conteneur flex ou grid, sans marge superflue au début ni à la fin.
:::

## Flexbox : aligner sur un axe

Dès qu'un conteneur reçoit `flex`, ses enfants se placent **sur une ligne**. Deux axes à connaître : l'**axe principal** (la direction du flux) et l'**axe secondaire** (perpendiculaire).

```html
<div class="flex items-center justify-between gap-4">
  <span>Logo</span>
  <nav>Menu</nav>
  <button>Connexion</button>
</div>
```

- `justify-` gère l'**axe principal** : `justify-start`, `justify-center`, `justify-end`, `justify-between` (espace entre les éléments), `justify-around`, `justify-evenly` ;
- `items-` gère l'**axe secondaire** : `items-start`, `items-center`, `items-end`, `items-stretch` (par défaut), `items-baseline` ;
- `flex-row` (défaut) et `flex-col` choisissent la direction ; `flex-wrap` autorise le retour à la ligne.

Quand tu passes en `flex-col`, l'axe principal devient vertical : `justify-` agit donc de haut en bas, et `items-` de gauche à droite. Cette inversion est la source de confusion numéro un.

### Les enfants flex : grandir, rétrécir, fixer

Les classes sur les enfants contrôlent leur taille :

- `flex-1` : l'élément prend tout l'espace restant ;
- `shrink-0` : empêche l'élément de rétrécir (utile pour un avatar ou une icône) ;
- `grow` : l'élément peut grandir ;
- `self-start`, `self-center`, `self-end` : alignement individuel ;
- `order-first`, `order-last` : changer l'ordre visuel.

Exemple classique, une ligne de liste avec avatar, texte flexible et action :

```html
<li class="flex items-center gap-3 rounded-lg p-3">
  <img src="/avatar.png" alt="" class="h-10 w-10 shrink-0 rounded-full" />
  <div class="min-w-0 flex-1">
    <p class="truncate font-medium text-slate-900">Awa Koné — Roadmap Laravel</p>
    <p class="truncate text-sm text-slate-500">Dernier chapitre terminé : Models</p>
  </div>
  <button class="shrink-0 text-sm text-indigo-600">Voir</button>
</li>
```

Le `min-w-0` est un classique : sans lui, un enfant flex ne rétrécit jamais en dessous de la largeur de son contenu, et `truncate` ne fonctionne pas.

### Centrer parfaitement

Le grand classique, centrer un bloc dans l'écran :

```html
<div class="flex min-h-screen items-center justify-center">
  <div class="rounded-xl bg-white p-8 shadow">Je suis centré</div>
</div>
```

## Grid : organiser en deux dimensions

Quand la mise en page est une **grille** (cartes, galerie, tableau de bord), `grid` est l'outil adapté :

```html
<div class="grid grid-cols-3 gap-6">
  <div class="rounded-lg bg-white p-4">1</div>
  <div class="rounded-lg bg-white p-4">2</div>
  <div class="rounded-lg bg-white p-4">3</div>
  <div class="rounded-lg bg-white p-4">4</div>
</div>
```

`grid-cols-3` crée trois colonnes de largeur égale ; les éléments se placent de gauche à droite puis passent à la ligne. Les options utiles :

- `grid-cols-1` à `grid-cols-12` ; `grid-rows-` pour les lignes ;
- `col-span-2` : un élément occupe deux colonnes ; `row-span-` pour les lignes ;
- `gap-x-` et `gap-y-` pour des espacements différents ;
- `place-items-center` pour centrer le contenu de chaque cellule ;
- `grid-cols-[200px_1fr]` : colonnes personnalisées (une colonne fixe de 200 px et une flexible).

Une grille de cartes qui s'adapte automatiquement à la largeur, sans media query, avec une valeur arbitraire :

```html
<div class="grid grid-cols-[repeat(auto-fill,minmax(16rem,1fr))] gap-6">
  <!-- autant de colonnes de 16 rem minimum que la largeur le permet -->
</div>
```

### Flex ou Grid ?

- **Flex** pour une seule dimension : une barre de navigation, une ligne de boutons, le contenu d'une carte.
- **Grid** pour deux dimensions : une grille de cartes, une mise en page complète d'application.
- Les deux se **combinent** : une grille de cartes, chacune organisée avec flex.

:::quiz
Dans un conteneur `flex flex-col`, quelle classe centre horizontalement les enfants ?
- [ ] `justify-center`
- [x] `items-center`
- [ ] `text-center`
- [ ] `content-center`
> En colonne, l'axe principal est vertical : `justify-` agit sur la verticale et `items-` sur l'horizontale.
:::

## Tailles, débordement et position

### Dimensions

- largeurs : `w-full`, `w-1/2`, `w-64`, `w-screen`, `w-fit`, `w-auto` ;
- limites : `max-w-md`, `max-w-prose` (une largeur confortable pour du texte), `min-w-0` ;
- hauteurs : `h-10`, `h-screen`, `min-h-screen` ;
- carrés : `size-10` (largeur et hauteur identiques, disponible à partir de Tailwind 3.4) ;
- ratio : `aspect-video` (16/9) et `aspect-square`.

Pour un contenu centré dans la page, la recette standard est un conteneur : `mx-auto max-w-5xl px-4`. Le `px-4` garde un petit retrait sur les petits écrans.

### Débordement

`overflow-hidden` coupe ce qui dépasse (utile avec `rounded-xl` pour arrondir une image), `overflow-auto` ajoute une barre de défilement si nécessaire, `overflow-x-auto` gère uniquement l'horizontale (idéal pour un tableau large sur mobile). `truncate` coupe un texte unique avec « … ».

### Position

```html
<div class="relative">
  <img src="/couverture.jpg" alt="" class="w-full" />
  <span class="absolute right-3 top-3 rounded bg-black/60 px-2 py-1 text-xs text-white">
    Nouveau
  </span>
</div>
```

`relative` sur le parent crée le repère, `absolute` sort l'enfant du flux et le place avec `top-`, `right-`, `bottom-`, `left-` (ou `inset-0` pour remplir le parent). `fixed` colle à la fenêtre (barre de navigation fixe), `sticky` colle au défilement (`sticky top-0`). Pour l'empilement, `z-10`, `z-20`, `z-50`.

Remarque aussi la syntaxe `bg-black/60` : une couleur avec **60 % d'opacité**.

## Une mise en page complète

Assemblons les pièces pour une disposition classique d'application : en-tête fixe, barre latérale, contenu, pied de page.

```html
<div class="flex min-h-screen flex-col bg-slate-50">
  <header class="sticky top-0 z-10 flex items-center justify-between border-b border-slate-200 bg-white px-6 py-3">
    <span class="text-lg font-bold text-indigo-600">DevRoad</span>
    <button class="rounded-lg bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white">
      Mon profil
    </button>
  </header>

  <div class="mx-auto flex w-full max-w-6xl flex-1 gap-6 p-6">
    <aside class="w-56 shrink-0 space-y-1">
      <a href="#" class="block rounded-lg bg-indigo-50 px-3 py-2 font-medium text-indigo-700">Roadmaps</a>
      <a href="#" class="block rounded-lg px-3 py-2 text-slate-600">Progression</a>
      <a href="#" class="block rounded-lg px-3 py-2 text-slate-600">Paramètres</a>
    </aside>

    <main class="grid flex-1 grid-cols-2 content-start gap-6">
      <article class="rounded-xl bg-white p-6 shadow-sm">React</article>
      <article class="rounded-xl bg-white p-6 shadow-sm">Laravel</article>
    </main>
  </div>

  <footer class="border-t border-slate-200 py-4 text-center text-sm text-slate-500">
    © 2026 DevRoad
  </footer>
</div>
```

Observe la technique du **pied de page collé en bas** : le conteneur racine est `flex min-h-screen flex-col`, et la zone centrale reçoit `flex-1` pour absorber l'espace libre. Aucune position absolue nécessaire.

## Atelier guidé : un tableau de bord statique

Compte une heure et demie, dans ton projet Tailwind v3.

1. Crée la structure racine `flex min-h-screen flex-col bg-slate-50`.
2. Ajoute un en-tête `sticky` avec logo à gauche et deux boutons à droite grâce à `justify-between`.
3. Crée un conteneur central `mx-auto max-w-6xl` avec une barre latérale de `w-56 shrink-0` et une zone `flex-1`.
4. Dans la zone principale, construis une grille de 3 colonnes (`grid-cols-3 gap-6`) avec 6 cartes de statistiques.
5. Fais en sorte que la première carte occupe 2 colonnes avec `col-span-2`.
6. Dans chaque carte, utilise `flex items-center justify-between` pour aligner un libellé et un chiffre.
7. Ajoute une liste de lignes avec avatar (`shrink-0 rounded-full`) et texte tronqué (`min-w-0 flex-1 truncate`).
8. Place un badge « Nouveau » en `absolute` dans le coin d'une carte `relative`.
9. Ajoute un pied de page qui reste en bas même quand le contenu est court.
10. Remplace un `space-y-` par `gap-` et un `gap-` par `space-y-` dans deux endroits adaptés, et compare le résultat.

Auto-évaluation :

- Peux-tu expliquer ce qui change entre `justify-center` et `items-center` en `flex-col` ?
- Tous les espaces entre cartes viennent-ils de `gap-` plutôt que de marges individuelles ?
- Le pied de page reste-t-il en bas avec peu de contenu ?
- Les textes longs sont-ils tronqués sans casser la mise en page ?

:::quiz
Un texte avec `truncate` dans un enfant flex déborde quand même. Que manque-t-il probablement ?
- [ ] `overflow-visible` sur le texte
- [x] `min-w-0` sur l'enfant flex qui contient le texte
- [ ] `flex-col` sur le parent
- [ ] `w-screen` sur le texte
> Un enfant flex ne rétrécit pas en dessous de la largeur de son contenu par défaut. `min-w-0` lève cette limite, ce qui permet à `truncate` de fonctionner.
:::

## Erreurs fréquentes

- **Oublier `flex` sur le parent** et se demander pourquoi `justify-between` ne fait rien : ces classes ne s'appliquent qu'aux conteneurs flex ou grid.
- **Confondre `justify-` et `items-`** après un passage en `flex-col`.
- **Empiler des marges** au lieu d'utiliser `gap-` sur le parent : on obtient des espaces en trop au début ou à la fin.
- **Oublier `relative`** sur le parent d'un enfant `absolute` : l'élément se positionne par rapport à la page entière.
- **Mettre `h-screen` partout** : `min-h-screen` évite de couper le contenu quand il est long.
- **Oublier `shrink-0`** sur un avatar : il s'écrase quand le texte est long.
- **Texte trop large** : sans `max-w-prose` ou conteneur, les lignes dépassent 100 caractères et deviennent illisibles.

## Bonnes pratiques

- Pense « conteneur et enfants » : le parent décide de la disposition et de l'espace (`flex`, `grid`, `gap-`).
- Choisis une échelle d'espacements réduite et tiens-toi à elle.
- Utilise `max-w-` et `mx-auto` pour un contenu centré, avec `px-4` de retrait.
- Préfère `min-h-screen` à `h-screen`.
- Combine Grid pour la structure globale et Flex pour l'intérieur des composants.
- Ouvre l'inspecteur du navigateur : l'outil d'affichage de Flexbox et Grid dessine les axes et les écarts.

## À retenir

- Padding = espace interne, margin = espace externe, `gap-` = espace entre enfants.
- `flex` aligne sur un axe : `justify-` pour l'axe principal, `items-` pour l'axe secondaire.
- `flex-col` inverse les rôles de `justify-` et `items-`.
- `grid grid-cols-N gap-N` construit une grille ; `col-span-N` fait occuper plusieurs colonnes.
- `flex-1`, `shrink-0` et `min-w-0` règlent la plupart des problèmes de taille d'un enfant flex.
- `relative` plus `absolute` positionne un élément dans son parent ; `sticky top-0` colle au défilement.
- Une mise en page complète tient en un conteneur `flex min-h-screen flex-col` avec une zone centrale `flex-1`.
