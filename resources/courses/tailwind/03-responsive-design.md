---
title: Responsive design
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Une partie importante des utilisateurs de DevRoad et de ses projets visitent les sites depuis un téléphone, parfois petit et avec une connexion lente. Une interface qui n'est confortable que sur ordinateur est une interface à moitié finie. Tailwind rend le **responsive design** presque naturel grâce à ses préfixes de points de rupture.

À la fin du chapitre, tu seras capable de :

- appliquer la méthode **mobile first** ;
- utiliser les préfixes `sm:`, `md:`, `lg:`, `xl:` et `2xl:` ;
- adapter une grille, une navigation et des tailles de texte selon la largeur de l'écran ;
- afficher ou masquer des éléments selon l'écran (`hidden`, `block`, `md:flex`) ;
- créer un menu mobile avec un bouton d'ouverture ;
- personnaliser les points de rupture et gérer les images responsives ;
- tester ton interface sur différentes tailles.

Prérequis : les chapitres « Découvrir Tailwind » et « Layout et spacing ». Prévois deux heures.

## La balise viewport

Rien de responsive ne fonctionne sans cette ligne dans le `<head>` de ta page :

```html
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
```

Sans elle, un téléphone affiche la page comme sur un grand écran (environ 980 px de large) puis la réduit, rendant le texte minuscule. Le gabarit de Vite la contient déjà, mais vérifie-la sur un projet Laravel ou statique.

## Mobile first : la philosophie de Tailwind

Tailwind utilise une approche **mobile first**. Une classe **sans préfixe** s'applique à **tous** les écrans, en commençant par les plus petits. Une classe **avec préfixe** s'applique à partir d'une largeur minimale **et au-dessus**.

Les points de rupture par défaut :

| Préfixe | Largeur minimale | Typiquement |
| --- | --- | --- |
| (aucun) | 0 px | téléphone |
| `sm:` | 640 px | grand téléphone, petit format paysage |
| `md:` | 768 px | tablette |
| `lg:` | 1024 px | petit ordinateur |
| `xl:` | 1280 px | ordinateur |
| `2xl:` | 1536 px | grand écran |

Lis `md:grid-cols-2` comme « à partir de 768 px, deux colonnes ». Voici l'exemple de base :

```html
<div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3">
  <article class="rounded-xl bg-white p-4">React</article>
  <article class="rounded-xl bg-white p-4">Laravel</article>
  <article class="rounded-xl bg-white p-4">Tailwind</article>
</div>
```

- sur téléphone : **1 colonne** ;
- à partir de 768 px : **2 colonnes** ;
- à partir de 1024 px : **3 colonnes**.

> **Erreur fréquente** : penser que `sm:` veut dire « petit écran ». C'est faux : `sm:` signifie « 640 px et plus ». Pour cibler un téléphone, on écrit la classe **sans** préfixe, puis on ajuste vers le haut.

### Pourquoi commencer par le mobile ?

Concevoir d'abord pour le petit écran t'oblige à identifier l'essentiel : une seule colonne, des éléments empilés, des boutons larges. Ajouter de la complexité vers les grands écrans est plus simple que d'en retirer. Cela évite aussi du CSS inutile pour les appareils les moins puissants.

:::quiz
Que signifie la classe `lg:flex-row` ?
- [ ] Disposition en ligne uniquement en dessous de 1024 px
- [x] Disposition en ligne à partir de 1024 px de large et au-delà
- [ ] Disposition en ligne uniquement sur grand écran de 1024 px exactement
- [ ] Disposition en ligne sur tous les écrans
> Les préfixes sont des largeurs minimales : `lg:` s'applique à partir de 1024 px, et pour toutes les largeurs supérieures.
:::

## Adapter la disposition

Le schéma le plus courant : une pile verticale sur mobile qui devient une ligne sur grand écran.

```html
<section class="flex flex-col gap-6 md:flex-row md:items-center">
  <img
    src="/hero.webp"
    alt="Illustration de parcours d'apprentissage"
    class="w-full rounded-2xl md:w-1/2"
  />
  <div class="md:w-1/2">
    <h1 class="text-3xl font-bold md:text-5xl">Apprends à ton rythme</h1>
    <p class="mt-4 text-slate-600 md:text-lg">
      Des roadmaps claires, des chapitres concrets et un suivi de progression.
    </p>
  </div>
</section>
```

Plusieurs propriétés changent avec la largeur : la direction (`flex-col` puis `md:flex-row`), les largeurs (`w-full` puis `md:w-1/2`), la taille de texte (`text-3xl` puis `md:text-5xl`). Tu peux empiler les préfixes pour affiner : `p-4 md:p-6 lg:p-10`.

### Espacements et conteneur

Un conteneur adaptable typique :

```html
<div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
  ...
</div>
```

Le retrait latéral augmente avec l'écran, et `max-w-6xl` empêche le contenu de s'étirer à l'infini sur un moniteur large. Tailwind propose aussi une classe `container` : elle fixe une largeur maximale à chaque point de rupture, mais n'est pas centrée par défaut. Beaucoup de développeurs préfèrent le trio `mx-auto max-w-* px-*`, plus explicite.

## Afficher ou masquer selon l'écran

Les classes `hidden` (`display: none`) et `block`, `flex`, `grid` s'associent aux préfixes :

```html
<!-- visible uniquement sur mobile -->
<button class="md:hidden">Menu</button>

<!-- visible uniquement à partir de md -->
<nav class="hidden md:flex md:gap-6">
  <a href="#">Roadmaps</a>
  <a href="#">Progression</a>
  <a href="#">Profil</a>
</nav>
```

La règle de lecture : `hidden md:flex` = « caché par défaut, en flex à partir de 768 px ». L'élément n'est pas seulement invisible : avec `display: none`, il disparaît aussi des lecteurs d'écran, ce qui est voulu ici.

Une alternative accessible pour du contenu uniquement destiné aux lecteurs d'écran : `sr-only`.

## Un menu de navigation mobile

Un cas complet qui combine préfixes et état React. Sur mobile, un bouton ouvre un menu vertical ; sur ordinateur, les liens s'affichent en ligne.

```jsx
import { useState } from 'react';

const liens = [
  { href: '/roadmaps', label: 'Roadmaps' },
  { href: '/progression', label: 'Progression' },
  { href: '/profil', label: 'Profil' },
];

export default function Navbar() {
  const [ouvert, setOuvert] = useState(false);

  return (
    <header className="border-b border-slate-200 bg-white">
      <div className="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
        <a href="/" className="text-lg font-bold text-indigo-600">DevRoad</a>

        <nav className="hidden gap-6 md:flex">
          {liens.map((l) => (
            <a key={l.href} href={l.href} className="text-slate-600 hover:text-indigo-600">
              {l.label}
            </a>
          ))}
        </nav>

        <button
          type="button"
          onClick={() => setOuvert(!ouvert)}
          aria-expanded={ouvert}
          aria-controls="menu-mobile"
          className="rounded-lg p-2 text-slate-700 md:hidden"
        >
          {ouvert ? 'Fermer' : 'Menu'}
        </button>
      </div>

      <nav
        id="menu-mobile"
        className={`${ouvert ? 'flex' : 'hidden'} flex-col border-t border-slate-200 px-4 py-2 md:hidden`}
      >
        {liens.map((l) => (
          <a key={l.href} href={l.href} className="py-3 text-slate-700">
            {l.label}
          </a>
        ))}
      </nav>
    </header>
  );
}
```

Remarque que `flex` et `hidden` sont écrits en **toutes lettres** dans le code (`'flex'` et `'hidden'`), ce qui permet à Tailwind de les détecter. Les attributs `aria-expanded` et `aria-controls` informent les lecteurs d'écran de l'état du menu.

> **Attention** : les zones tactiles doivent être assez grandes. Vise au moins **44 × 44 px** pour un bouton ou un lien sur mobile (par exemple `py-3` sur chaque lien, ou `p-3` sur un bouton icône).

## Textes, images et tableaux

### Typographie responsive

Augmente progressivement les tailles : `text-2xl sm:text-3xl lg:text-5xl`. Garde les paragraphes lisibles avec `max-w-prose` et un `leading-relaxed`. Évite `text-xs` pour du contenu essentiel : 12 px est trop petit sur mobile.

### Images

Une image doit s'adapter à son conteneur et ne jamais déborder :

```html
<img src="/photo.webp" alt="Équipe en atelier" class="h-auto w-full rounded-xl object-cover" />
```

Avec une hauteur fixe, `object-cover` recadre sans déformer : `h-48 w-full object-cover md:h-72`. Ajoute `loading="lazy"` sur les images sous la ligne de flottaison pour économiser les données mobiles, et privilégie les formats légers (WebP, AVIF).

### Tableaux

Un tableau large casse la mise en page sur téléphone. Enveloppe-le dans un conteneur à défilement horizontal :

```html
<div class="overflow-x-auto">
  <table class="min-w-full text-left text-sm">...</table>
</div>
```

## Personnaliser les points de rupture

Les valeurs par défaut conviennent à la majorité des cas. Pour les modifier ou en ajouter, utilise `theme.screens` (ou `theme.extend.screens` pour ajouter sans remplacer) :

```js
// tailwind.config.js
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      screens: {
        xs: '480px',
        '3xl': '1792px',
      },
    },
  },
};
```

Tu peux alors écrire `xs:grid-cols-2`. Tailwind propose aussi les variantes **max-** (`max-md:hidden` : « en dessous de 768 px ») et des plages (`md:max-lg:flex`), utiles pour un comportement limité à un intervalle. Pour des besoins précis, la valeur arbitraire fonctionne aussi : `min-[400px]:flex`.

:::quiz
Quel est le comportement de `hidden md:block` ?
- [ ] Visible sur mobile, caché à partir de 768 px
- [x] Caché sur mobile, visible à partir de 768 px
- [ ] Toujours caché
- [ ] Toujours visible
> `hidden` s'applique à tous les écrans, puis `md:block` rétablit l'affichage à partir de 768 px.
:::

## Tester le responsive

1. Ouvre les outils de développement (`F12`) puis le mode **appareil** (icône de téléphone) : choisis un modèle ou redimensionne librement.
2. Teste à **360 px** (petit téléphone Android très répandu), **768 px** et **1280 px** au minimum.
3. Teste aussi l'orientation paysage et le **zoom du texte** à 200 %.
4. Vérifie qu'il n'y a **aucun défilement horizontal** de la page.
5. Si possible, teste sur un vrai téléphone en ouvrant l'adresse réseau de Vite (`npm run dev -- --host`).

Pense également à la **connexion lente** : dans l'onglet Réseau, active le profil « Slow 3G » et observe le poids des images et la vitesse d'affichage.

## Atelier guidé : une page d'accueil responsive

Compte une heure et demie. Construis une landing page pour une roadmap.

1. Vérifie la balise viewport dans `index.html`.
2. Crée un conteneur `mx-auto max-w-6xl px-4 sm:px-6 lg:px-8`.
3. Construis le héros : image au-dessus du texte sur mobile, côte à côte à partir de `md:`.
4. Adapte les tailles de titre : `text-3xl md:text-5xl`.
5. Ajoute une grille de fonctionnalités : 1 colonne, 2 colonnes dès `sm:`, 3 colonnes dès `lg:`.
6. Construis la barre de navigation avec le menu mobile de ce chapitre.
7. Ajoute un tableau de tarifs (ou de chapitres) enveloppé dans `overflow-x-auto`.
8. Passe le bouton principal en pleine largeur sur mobile (`w-full`) et en largeur automatique ensuite (`sm:w-auto`).
9. Ajoute un breakpoint `xs` personnalisé et utilise-le à un endroit.
10. Teste à 360 px, 768 px et 1280 px, et corrige tout défilement horizontal.

Auto-évaluation :

- As-tu écrit d'abord la version mobile, puis ajouté des préfixes ?
- Les zones cliquables font-elles au moins 44 px ?
- Aucune ligne de texte ne dépasse 75 caractères sur grand écran ?
- Les images sont-elles légères, avec `alt` et `loading="lazy"` si nécessaire ?

:::quiz
Pourquoi écrire d'abord le style mobile ?
- [ ] Parce que Tailwind ne fonctionne pas sur ordinateur
- [x] Parce que les classes sans préfixe s'appliquent partout et que les préfixes ajoutent des variantes pour les écrans plus larges
- [ ] Parce que les téléphones n'acceptent pas `md:`
- [ ] Parce que les grands écrans ignorent les classes
> L'approche mobile first part de l'essentiel pour le petit écran et enrichit progressivement avec `sm:`, `md:`, `lg:`, plus simple que de retirer du style.
:::

## Erreurs fréquentes

- **Croire que `sm:` cible les téléphones.** Il commence à 640 px ; le mobile, c'est l'absence de préfixe.
- **Oublier la balise viewport.** Les préfixes ne se déclenchent pas comme prévu sur mobile.
- **Écrire d'abord pour le bureau** puis tout corriger en `max-*` : le CSS devient confus.
- **Largeurs fixes en pixels** (`w-[900px]`) qui provoquent un défilement horizontal.
- **Menu mobile sans bouton accessible.** Ajoute `aria-expanded`, un libellé clair, et une zone tactile suffisante.
- **Composer une classe dynamiquement** comme `` `md:grid-cols-${n}` ``. Tailwind ne la détecte pas : écris des classes complètes.
- **Tester uniquement en redimensionnant la fenêtre** : essaie aussi un vrai appareil.

## Bonnes pratiques

- Pense mobile first : style de base sans préfixe, enrichissement vers le haut.
- Limite le nombre de points de rupture utilisés dans une même page.
- Utilise `max-w-*` et `mx-auto` pour que le contenu reste lisible sur grand écran.
- Garde des zones tactiles d'au moins 44 px et des textes de 16 px pour le contenu.
- Optimise les images (formats modernes, `loading="lazy"`, dimensions adaptées).
- Teste à 360, 768 et 1280 px, ainsi qu'avec le réseau ralenti.

## À retenir

- La balise `viewport` est indispensable au responsive.
- Tailwind est **mobile first** : une classe sans préfixe vaut pour tous les écrans, un préfixe signifie « à partir de ».
- Points de rupture par défaut : `sm` 640, `md` 768, `lg` 1024, `xl` 1280, `2xl` 1536 px.
- `hidden md:flex` et `md:hidden` permettent d'afficher des éléments différents selon l'écran.
- Une pile verticale devient une ligne avec `flex-col md:flex-row`, une grille gagne des colonnes avec `md:grid-cols-2 lg:grid-cols-3`.
- Les points de rupture se personnalisent dans `theme.extend.screens`.
- On vérifie toujours l'interface sur de petites largeurs, des appareils réels et une connexion lente.
