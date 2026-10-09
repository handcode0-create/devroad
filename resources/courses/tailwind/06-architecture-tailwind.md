---
title: Architecture Tailwind
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Tailwind est simple à démarrer, mais sur un projet de plusieurs mois, deux questions reviennent : comment garder un code propre quand les listes de classes s'allongent, et comment s'assurer que tout le monde dans l'équipe (ou toi dans six mois) produit une interface cohérente ? Ce chapitre répond avec une **architecture** : composants, couches CSS, plugins, conventions et optimisation du build.

À la fin du chapitre, tu seras capable de :

- décider quand extraire un composant, quand utiliser `@apply` et quand ne rien faire ;
- organiser le CSS avec `@layer base`, `@layer components` et `@layer utilities` ;
- créer une petite **bibliothèque de composants** cohérente ;
- utiliser les plugins officiels (`@tailwindcss/forms`, `@tailwindcss/typography`) ;
- écrire un plugin simple et ajouter des utilitaires personnalisés ;
- organiser les classes avec Prettier et ESLint, et comprendre le préfixe `important` ;
- optimiser le build et diagnostiquer un CSS trop gros ou des classes manquantes.

Prérequis : les chapitres 1 à 5 de cette roadmap. Prévois deux heures et demie.

## Le vrai problème : la répétition

Voici le reproche le plus fréquent fait à Tailwind :

```html
<button class="inline-flex items-center justify-center gap-2 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm transition-colors hover:bg-indigo-700 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 disabled:opacity-50">
  Enregistrer
</button>
```

Si cette ligne apparaît dans trente fichiers, changer la couleur devient un cauchemar. Mais le problème n'est pas Tailwind : c'est la **duplication**. Les solutions sont, par ordre de préférence :

1. **Extraire un composant** (React, Blade, Vue…) ;
2. **Utiliser des tokens de thème** (couleurs et rayons dans la configuration) ;
3. **Utiliser `@apply`** dans un cas particulier ;
4. **Ne rien faire** : si la répétition est de 2 ou 3 occurrences proches, elle est tolérable.

### Règle des trois

Duplique une fois, puis deux fois sans souci. Dès la **troisième** occurrence, extrais. Extraire trop tôt crée des abstractions qui ne correspondent pas au besoin réel.

## Extraire un composant, la solution par défaut

Dans un projet React (comme le front de DevRoad), le composant est l'unité de réutilisation :

```jsx
// src/components/ui/Button.jsx
import { cn } from '@/lib/cn';

const variantes = {
  primaire: 'bg-accent text-accent-texte hover:bg-accent/90',
  secondaire: 'bg-surface text-texte ring-1 ring-inset ring-bordure hover:bg-fond',
};

export default function Button({ variante = 'primaire', className, ...props }) {
  return (
    <button
      type="button"
      className={cn(
        'inline-flex items-center gap-2 rounded-lg px-4 py-2 text-sm font-medium transition-colors',
        'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
        'disabled:cursor-not-allowed disabled:opacity-50',
        variantes[variante],
        className
      )}
      {...props}
    />
  );
}
```

Avec Laravel Blade, l'équivalent est un **composant anonyme** `resources/views/components/button.blade.php` qui utilise `$attributes->merge(['class' => '...'])`. Le principe est identique : la composition remplace les classes sémantiques du CSS traditionnel.

## @apply : l'exception, pas la règle

La directive `@apply` permet de composer des utilitaires dans une classe CSS :

```css
@layer components {
  .btn-primaire {
    @apply inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700;
  }
}
```

Cela peut sembler idéal. Mais l'équipe Tailwind elle-même la déconseille en général, pour plusieurs raisons :

- tu retrouves le problème des **noms à inventer** et du va-et-vient entre fichiers ;
- les classes générées par `@apply` sont **couplées** au CSS : la suppression devient risquée ;
- les **variantes** deviennent lourdes (un bouton avec quatre variantes et trois tailles = beaucoup de classes à nommer) ;
- l'ordre et la priorité des règles peuvent surprendre.

Les cas où `@apply` reste raisonnable :

- **styliser du HTML qu'on ne contrôle pas** : du contenu venant d'un éditeur de texte riche ou de Markdown (mais le plugin `typography` fait ça mieux) ;
- **de petits éléments de base très stables** (un style de lien, `.container-page`) utilisés dans des gabarits qui ne peuvent pas être des composants ;
- **la migration** progressive d'un CSS existant.

> **À retenir** : si tu as un moteur de composants (React, Blade, Vue, Svelte), utilise-le. `@apply` sert quand il n'y en a pas.

:::quiz
Quelle est la solution à privilégier quand un bloc de classes Tailwind se répète dans de nombreux fichiers ?
- [ ] Copier-coller le bloc partout
- [ ] Remplacer tout le projet par du CSS classique
- [x] Extraire un composant (React, Blade, etc.)
- [ ] Utiliser `@apply` systématiquement
> Un composant centralise structure et styles, accepte des variantes par props et évite de nommer et maintenir des classes CSS supplémentaires.
:::

## Organiser le CSS avec @layer

Le fichier CSS principal contient les directives et tes ajouts. La directive `@layer` place tes règles dans la bonne **couche** : Tailwind sait alors les trier correctement et les **retirer** si elles ne sont pas utilisées (pour `components` et `utilities`).

```css
/* resources/css/app.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  html {
    @apply scroll-smooth;
  }
  body {
    @apply bg-fond font-sans text-texte antialiased;
  }
  h1, h2, h3 {
    @apply font-titre tracking-tight;
  }
  a {
    @apply text-accent underline-offset-4 hover:underline;
  }
}

@layer components {
  .container-page {
    @apply mx-auto w-full max-w-6xl px-4 sm:px-6 lg:px-8;
  }
}

@layer utilities {
  .text-balance {
    text-wrap: balance;
  }
}
```

- **`base`** : styles pour les balises HTML nues (titres, liens, corps de page) ;
- **`components`** : classes de composants que les utilitaires peuvent encore surcharger ;
- **`utilities`** : tes propres utilitaires, qui s'utilisent ensuite avec les variantes (`md:text-balance`, `hover:text-balance`).

Pour le CSS qui ne dépend pas de Tailwind (animations, `@font-face`), écris-le simplement en dehors des couches.

## Les plugins officiels

### @tailwindcss/forms

Preflight retire l'apparence native des champs, ce qui complique leur stylisation. Le plugin `forms` leur redonne une base cohérente et stylable avec des utilitaires :

```bash
npm install -D @tailwindcss/forms @tailwindcss/typography
```

```js
// tailwind.config.js
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

export default {
  // ...
  plugins: [forms, typography],
};
```

### @tailwindcss/typography

Quand tu affiches du contenu long dont tu ne contrôles pas le HTML (cours en Markdown, articles de blog), la classe `prose` met en forme titres, listes, citations et code d'un coup :

```html
<article class="prose prose-slate max-w-none dark:prose-invert lg:prose-lg">
  {!! $contenuHtml !!}
</article>
```

`dark:prose-invert` adapte les couleurs au thème sombre, et `prose-lg` agrandit le texte. C'est la méthode adaptée pour afficher le contenu des chapitres d'une plateforme de cours.

## Écrire un plugin simple

Tailwind permet d'ajouter ses propres utilitaires ou composants avec l'API `plugin`. Par exemple, un utilitaire qui masque la barre de défilement :

```js
// tailwind.config.js
import plugin from 'tailwindcss/plugin';

export default {
  // ...
  plugins: [
    plugin(function ({ addUtilities, matchUtilities, theme }) {
      addUtilities({
        '.scrollbar-masquee': {
          '-ms-overflow-style': 'none',
          'scrollbar-width': 'none',
          '&::-webkit-scrollbar': { display: 'none' },
        },
      });

      matchUtilities(
        { 'delai-anim': (valeur) => ({ animationDelay: valeur }) },
        { values: theme('transitionDelay') }
      );
    }),
  ],
};
```

`addUtilities` ajoute des classes fixes, `matchUtilities` génère des classes à valeurs (`delai-anim-150`) à partir des valeurs du thème. Les deux produisent des classes qui profitent de toutes les variantes (`hover:`, `md:`, `dark:`).

## Conventions d'équipe

### Trier les classes automatiquement

Le plugin `prettier-plugin-tailwindcss` ordonne les classes dans un ordre canonique à chaque sauvegarde :

```bash
npm install -D prettier prettier-plugin-tailwindcss
```

```json
{
  "plugins": ["prettier-plugin-tailwindcss"],
  "tailwindFunctions": ["cn", "clsx"]
}
```

Plus de débats sur l'ordre ; les diffs de code restent lisibles. L'option `tailwindFunctions` indique à Prettier de trier aussi les classes passées à `cn()`.

### Une structure pour la bibliothèque UI

```text
src/components/
├── ui/            composants génériques (Button, Champ, Badge, Carte, Modal)
├── layout/        Navbar, Sidebar, Footer, Conteneur
└── features/      composants métier qui assemblent les précédents
```

Les composants `ui/` ne connaissent pas le métier et n'utilisent que les **tokens** (`bg-surface`, `text-texte`), jamais des couleurs brutes comme `bg-indigo-600`. Les composants métier, eux, composent des composants `ui/`.

### Règles à écrire dans un guide de style

- on utilise les tokens sémantiques, pas de couleurs brutes dans les composants ;
- les espacements viennent de l'échelle (2, 4, 6, 8, 12, 16) ;
- pas de valeur arbitraire sans commentaire justificatif ;
- toute interaction a des états `hover`, `focus-visible` et `disabled` ;
- tout composant est testé en clair, en sombre et à 360 px.

## Maîtriser la spécificité : important et préfixe

Dans un projet qui mélange Tailwind et un CSS existant (une bibliothèque tierce, Bootstrap, un thème WordPress), des conflits apparaissent. Deux options de configuration :

```js
export default {
  prefix: 'tw-',        // toutes les classes deviennent tw-flex, tw-p-4...
  important: '#app',    // augmente la spécificité en préfixant avec #app
  // ...
};
```

`prefix` évite les collisions de noms, `important` donne l'avantage aux utilitaires. Pour un projet neuf, n'utilise ni l'un ni l'autre. Le modificateur `!` permet de forcer un cas isolé : `!mt-0`.

## Optimiser le build

En production, Tailwind ne génère que les classes détectées : un projet moyen produit souvent un CSS de 10 à 30 Ko compressé. Quelques conseils pour y rester :

- **liste précisément** les chemins dans `content`, sans inclure `node_modules` ni le dossier de build ;
- **n'ajoute pas** des chemins trop larges (par exemple tout le dossier racine) : l'analyse devient lente ;
- **utilise `safelist`** uniquement pour des classes réellement dynamiques (venant d'une base de données) ;
- **vérifie le résultat** : `npm run build` affiche le poids du CSS ; un fichier de plusieurs centaines de Ko révèle un problème de configuration ;
- **compresse** avec gzip ou Brotli côté serveur.

Pour un projet Laravel, n'oublie pas les vues Blade et les composants :

```js
content: [
  './resources/views/**/*.blade.php',
  './resources/js/**/*.{js,jsx}',
  './app/View/Components/**/*.php',
],
```

### Diagnostiquer les classes manquantes

Quand une classe ne fonctionne pas, parcours cette liste :

1. le fichier est-il couvert par `content` ?
2. la classe est-elle écrite **en entier** dans le code (pas de concaténation partielle) ?
3. as-tu redémarré le serveur de développement après avoir modifié `tailwind.config.js` ?
4. la classe est-elle surchargée par un autre CSS (inspecteur : règle barrée) ?
5. le nom est-il correct ? L'extension IntelliSense souligne les classes inconnues.

:::quiz
Où ajouter ses propres styles pour que Tailwind les trie correctement et les supprime s'ils ne sont pas utilisés ?
- [ ] Dans un fichier CSS séparé chargé après Tailwind
- [x] Dans `@layer components` ou `@layer utilities`
- [ ] Dans le HTML avec l'attribut `style`
- [ ] Dans `package.json`
> La directive `@layer` place les règles dans la bonne couche ; Tailwind peut ainsi gérer l'ordre et retirer les classes inutilisées.
:::

## Atelier guidé : structurer un projet pour l'équipe

Compte deux heures.

1. Dans ton projet, crée les dossiers `components/ui`, `components/layout` et `components/features`.
2. Déplace `Button`, `Champ`, `Badge` et `Carte` dans `ui/` et remplace les couleurs brutes par des tokens.
3. Ajoute `@layer base` pour le corps de page, les titres et les liens, avec `@apply`.
4. Ajoute un composant `.container-page` dans `@layer components` et utilise-le dans trois pages.
5. Installe `@tailwindcss/typography` et affiche un texte Markdown converti en HTML avec `prose dark:prose-invert`.
6. Installe `@tailwindcss/forms` et vérifie l'apparence d'un `select` et d'une case à cocher avant et après.
7. Écris le plugin `scrollbar-masquee` et utilise-le sur une liste horizontale défilante.
8. Installe `prettier-plugin-tailwindcss` avec `tailwindFunctions: ["cn"]` et lance Prettier sur le dossier `src`.
9. Lance `npm run build`, note le poids du CSS, puis ajoute volontairement un chemin trop large dans `content` et compare.
10. Rédige une page `STYLE-GUIDE.md` de dix lignes avec les règles d'équipe de ce chapitre.

Auto-évaluation :

- Un nouveau développeur peut-il trouver où modifier la couleur d'un bouton en moins d'une minute ?
- Aucun composant `ui/` ne contient-il de couleur brute ?
- Le CSS de production reste-t-il sous les 50 Ko avant compression ?
- Chaque `@apply` de ton projet est-il justifié ?

## Erreurs fréquentes

- **Abuser de `@apply`** et recréer un CSS classique avec une couche d'abstraction en plus.
- **Extraire trop tôt** un composant pour un bloc utilisé une seule fois.
- **Placer du CSS personnalisé hors des couches** puis ne pas comprendre pourquoi un utilitaire ne le surcharge pas.
- **Oublier les fichiers Blade ou PHP dans `content`** : les classes y sont ignorées et disparaissent en production.
- **Utiliser `safelist` pour tout** : le CSS gonfle inutilement.
- **Ne pas redémarrer** le serveur après un changement de configuration.
- **Définir `important: true`** globalement : tout devient forcé et les conflits sont difficiles à déboguer.
- **Multiplier les valeurs arbitraires** au lieu d'étendre le thème.

## Bonnes pratiques

- Composant d'abord, `@apply` en dernier recours.
- Un seul endroit pour les tokens : `tailwind.config.js` et les variables CSS.
- Trie les classes automatiquement avec Prettier.
- Sépare composants génériques (`ui/`) et composants métier (`features/`).
- Liste précisément les chemins de `content`, vérifie le poids du CSS à chaque livraison.
- Documente les règles dans un guide de style court et vivant.
- Utilise `typography` pour le contenu riche que tu ne contrôles pas.

## À retenir

- La répétition se règle par des **composants**, pas par des classes CSS inventées.
- `@apply` reste une exception : contenu non maîtrisé, petites bases stables, migration.
- `@layer base`, `components` et `utilities` placent ton CSS au bon endroit.
- `@tailwindcss/forms` et `@tailwindcss/typography` couvrent deux besoins récurrents.
- L'API `plugin` ajoute des utilitaires personnalisés qui supportent toutes les variantes.
- Prettier trie les classes ; un guide de style court aligne l'équipe.
- Le CSS de production ne contient que les classes détectées : surveille `content` et le poids du build.
