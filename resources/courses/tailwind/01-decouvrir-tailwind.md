---
title: Découvrir Tailwind
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Tailwind CSS change la façon d'écrire les styles : au lieu de créer des classes CSS et de leur donner des noms, tu composes l'apparence directement dans le HTML avec de petites classes **utilitaires**. Cela surprend au début, puis devient très rapide. Ce chapitre t'explique la philosophie, t'installe l'outil et te fait construire ta première carte.

À la fin du chapitre, tu seras capable de :

- expliquer l'approche **utility-first** et la comparer au CSS classique ;
- installer Tailwind CSS v3 dans un projet Vite ;
- utiliser les classes de base : couleurs, texte, espacements, bordures, ombres ;
- comprendre l'**échelle de design** (spacing, tailles, palettes) qui garantit la cohérence ;
- utiliser les **valeurs arbitraires** quand l'échelle ne suffit pas ;
- lire la documentation officielle efficacement.

Prérequis : savoir écrire du HTML et connaître les notions CSS de base (sélecteur, propriété, boîte). Prévois deux heures. Tout ce cours utilise la version 3 de Tailwind.

## Le CSS classique et ses frictions

Prenons une carte de roadmap en CSS traditionnel :

```html
<div class="carte-roadmap">
  <h2 class="carte-roadmap__titre">React</h2>
  <p class="carte-roadmap__texte">Construire des interfaces.</p>
</div>
```

```css
.carte-roadmap {
  background: white;
  border-radius: 12px;
  padding: 24px;
  box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
}
.carte-roadmap__titre {
  font-size: 20px;
  font-weight: 700;
  color: #0f172a;
}
.carte-roadmap__texte {
  margin-top: 8px;
  color: #475569;
}
```

Ça marche, mais tu rencontres vite des frictions :

- **inventer des noms** (`carte-roadmap__titre`, `carte-roadmap-titre-grand`…) prend du temps et de l'énergie ;
- **naviguer** entre le fichier HTML et le fichier CSS pour chaque ajustement ;
- **peur de supprimer** : impossible de savoir si une classe est encore utilisée ailleurs, donc le CSS ne fait que grossir ;
- **valeurs magiques** : 24px ici, 25px là, 22px ailleurs, car chacun choisit « à l'œil ».

## L'approche utility-first

Tailwind fournit des milliers de petites classes qui font **une seule chose chacune**. Tu les assembles dans le HTML :

```html
<div class="rounded-xl bg-white p-6 shadow-sm">
  <h2 class="text-xl font-bold text-slate-900">React</h2>
  <p class="mt-2 text-slate-600">Construire des interfaces.</p>
</div>
```

Chaque classe se lit comme une phrase :

| Classe | Effet CSS |
| --- | --- |
| `rounded-xl` | `border-radius: 0.75rem` |
| `bg-white` | `background-color: #fff` |
| `p-6` | `padding: 1.5rem` |
| `shadow-sm` | une petite ombre portée |
| `text-xl` | `font-size: 1.25rem` (et la hauteur de ligne associée) |
| `font-bold` | `font-weight: 700` |
| `text-slate-900` | une couleur de texte très foncée de la palette « slate » |
| `mt-2` | `margin-top: 0.5rem` |

Le résultat est le même que le CSS plus haut, mais sans fichier à écrire, sans nom à inventer, et avec des valeurs prises dans une **échelle commune**.

### « Mais c'est du style en ligne ! »

C'est l'objection classique. Non, pour quatre raisons :

1. les utilitaires s'appuient sur un **système de design** (une échelle limitée de valeurs) au lieu de valeurs libres ;
2. ils supportent les **états et les écrans** (`hover:`, `md:`, `dark:`) que le style en ligne ne gère pas ;
3. ils sont **générés à la demande** : seul ce que tu utilises est dans le fichier final ;
4. ils sont **réutilisables** : tu extrais un composant React, pas une classe CSS.

> **À retenir** : tu écris le style là où tu écris la structure. Pour éviter la répétition, tu crées un **composant** (React, Blade, Vue…), pas une nouvelle classe.

:::quiz
Quel est le principe de l'approche utility-first ?
- [ ] Écrire une classe CSS par composant avec un nom sémantique
- [x] Assembler de petites classes à usage unique directement dans le HTML
- [ ] Utiliser uniquement des styles en ligne
- [ ] Remplacer le HTML par du JavaScript
> Tailwind fournit des classes utilitaires (une propriété ou presque chacune) qu'on combine dans le HTML, en s'appuyant sur une échelle de design cohérente.
:::

## Installer Tailwind CSS v3 avec Vite

Crée un projet Vite (ou utilise celui de la roadmap React) puis installe Tailwind et ses outils :

```bash
npm create vite@latest demo-tailwind -- --template react
cd demo-tailwind
npm install
npm install -D tailwindcss@3 postcss autoprefixer
npx tailwindcss init -p
```

La dernière commande crée deux fichiers : `tailwind.config.js` et `postcss.config.js`. Indique à Tailwind **où chercher des classes** :

```js
// tailwind.config.js
/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx,ts,tsx}'],
  theme: {
    extend: {},
  },
  plugins: [],
};
```

Tailwind lit les fichiers listés dans `content`, repère les noms de classes et ne génère que le CSS correspondant. Si un fichier n'est pas listé, ses classes ne fonctionneront pas : c'est la première chose à vérifier en cas de problème.

Dans ton fichier CSS principal (`src/index.css`), remplace tout par les trois directives :

```css
@tailwind base;
@tailwind components;
@tailwind utilities;
```

Chacune injecte une couche : `base` (remise à zéro de Preflight), `components` (tes classes de composants) et `utilities` (les classes utilitaires). Vérifie que `main.jsx` importe bien `./index.css`, puis teste :

```jsx
export default function App() {
  return (
    <main className="min-h-screen bg-slate-100 p-8">
      <h1 className="text-3xl font-bold text-indigo-600">Tailwind fonctionne !</h1>
    </main>
  );
}
```

Lance `npm run dev` : un titre indigo, en gras, sur fond gris clair.

> **Astuce** : installe l'extension **Tailwind CSS IntelliSense** dans VS Code. Elle propose l'autocomplétion des classes, affiche le CSS généré au survol, et signale les erreurs. Avec le plugin **Prettier** `prettier-plugin-tailwindcss`, tes classes sont aussi triées automatiquement dans un ordre standard.

### Preflight : la remise à zéro

Tailwind applique une base qui retire les styles par défaut du navigateur : les titres n'ont plus de taille particulière, les listes n'ont plus de puces, les marges de `body` sont à zéro, les images sont en `display: block`. Si un `h1` te semble « sans style », c'est normal : tu décides de tout avec les classes.

## L'échelle de design

La force de Tailwind est son échelle. Au lieu de choisir librement un nombre de pixels, tu choisis un cran.

### Espacements

Le nombre dans `p-4`, `m-2`, `gap-6` suit une échelle où **1 unité = 0,25 rem = 4 px** (si la taille de police racine est 16 px) :

| Classe | Valeur | Pixels |
| --- | --- | --- |
| `p-1` | 0.25rem | 4 px |
| `p-2` | 0.5rem | 8 px |
| `p-4` | 1rem | 16 px |
| `p-6` | 1.5rem | 24 px |
| `p-8` | 2rem | 32 px |
| `p-12` | 3rem | 48 px |

Le préfixe désigne le côté : `p` (tous), `px` (horizontal), `py` (vertical), `pt`, `pr`, `pb`, `pl`. Même logique avec `m` pour les marges : `mx-auto` centre un bloc de largeur définie, `mt-4` ajoute une marge en haut.

### Couleurs

Chaque couleur de la palette a des nuances de **50** (très claire) à **950** (très foncée) : `bg-indigo-50`, `bg-indigo-500`, `bg-indigo-900`. Le préfixe indique la propriété : `bg-` (fond), `text-` (texte), `border-` (bordure), `ring-` (anneau de focus). Une règle empirique : **500 à 600** pour les boutons et accents, **50 à 100** pour les fonds discrets, **700 à 900** pour le texte.

### Typographie

- tailles : `text-sm`, `text-base`, `text-lg`, `text-xl`, `text-2xl`… jusqu'à `text-9xl` ;
- graisse : `font-normal`, `font-medium`, `font-semibold`, `font-bold` ;
- alignement : `text-left`, `text-center`, `text-right` ;
- interligne : `leading-tight`, `leading-normal`, `leading-relaxed` ;
- transformations : `uppercase`, `capitalize`, `truncate`.

### Bordures, arrondis, ombres

`border` (1 px), `border-2`, `border-slate-200`, `rounded` / `rounded-lg` / `rounded-full`, `shadow-sm` / `shadow` / `shadow-lg`. Pour un cercle (avatar), combine une largeur et une hauteur égales avec `rounded-full`.

### Tailles

`w-full` (100 %), `w-1/2` (50 %), `w-64` (16 rem), `h-10`, `max-w-md`, `min-h-screen`. Les largeurs maximales `max-w-sm` à `max-w-7xl` servent à limiter les lignes de texte et à centrer le contenu avec `mx-auto`.

:::quiz
Que fait la classe `px-6` ?
- [ ] Ajoute une marge de 6 px sur tous les côtés
- [x] Ajoute un remplissage horizontal (gauche et droite) de 1,5 rem
- [ ] Définit la largeur à 6 pixels
- [ ] Ajoute un remplissage vertical de 6 rem
> `p` est le padding, `x` l'axe horizontal, et `6` correspond à 6 × 0,25 rem = 1,5 rem.
:::

## Les valeurs arbitraires

Parfois, l'échelle ne contient pas la valeur exacte voulue : une largeur de 340 px imposée par une maquette, une couleur de marque. Tailwind accepte des valeurs entre crochets :

```html
<div class="w-[340px] bg-[#0ea5e9] p-[18px] text-[15px]">...</div>
```

C'est pratique pour un cas isolé. Mais si tu répètes `#0ea5e9` à dix endroits, c'est le signe qu'il faut créer une **couleur de thème** (nous le ferons dans un chapitre ultérieur). Considère les valeurs arbitraires comme une porte de secours, pas comme la règle.

## Construire une première carte

Assemblons tout pour une carte de roadmap complète :

```html
<article class="mx-auto max-w-sm rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
  <span class="inline-block rounded-full bg-indigo-50 px-3 py-1 text-xs font-semibold text-indigo-700">
    Débutant
  </span>

  <h2 class="mt-4 text-2xl font-bold text-slate-900">Roadmap React</h2>

  <p class="mt-2 leading-relaxed text-slate-600">
    Apprends les composants, le state et les effets en sept chapitres.
  </p>

  <div class="mt-6 flex items-center justify-between">
    <span class="text-sm text-slate-500">7 chapitres</span>
    <a href="#" class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white">
      Commencer
    </a>
  </div>
</article>
```

Lis les classes par groupes : **disposition** (`mx-auto`, `max-w-sm`, `flex`), **boîte** (`p-6`, `border`, `rounded-2xl`), **couleurs** (`bg-white`, `text-slate-600`), **typographie** (`text-2xl`, `font-bold`). Avec l'habitude, tu les liras aussi vite que du CSS.

### Lire la documentation

Le site officiel (tailwindcss.com/docs) est ta référence principale. Sa barre de recherche (raccourci `Ctrl + K`) trouve une propriété CSS par son nom (« box-shadow », « flex-direction ») et te montre la classe équivalente. Chaque page contient des exemples interactifs et des tableaux de correspondance : tu n'as **pas besoin de tout mémoriser**.

## Atelier guidé : ta première page

Compte une heure et demie.

1. Crée un projet Vite avec React et installe Tailwind v3 comme indiqué. Vérifie que le titre indigo s'affiche.
2. Dans `App.jsx`, crée un `main` centré (`mx-auto`, `max-w-5xl`, `p-6`) avec un fond `bg-slate-50` sur toute la page (`min-h-screen`).
3. Ajoute un en-tête avec un titre `text-3xl font-bold` et un sous-titre `text-slate-600`.
4. Construis la carte de roadmap de ce chapitre.
5. Duplique la carte trois fois en changeant le titre, le niveau et la couleur du badge (`green`, `amber`, `indigo`).
6. Ajoute un bouton avec les classes de base, puis ajoute `hover:bg-indigo-700` et `transition-colors` pour un effet au survol.
7. Utilise une valeur arbitraire pour donner à un bloc la largeur exacte `w-[320px]`, puis remplace-la par une classe de l'échelle (`w-80`) et compare.
8. Ouvre l'extension IntelliSense : survole une classe pour voir le CSS généré.
9. Retire volontairement le chemin des fichiers source de `content`, observe que les styles disparaissent, puis remets-le.

Auto-évaluation :

- Peux-tu décrire en une phrase la différence entre `p-4`, `px-4` et `pt-4` ?
- Sais-tu retrouver dans la doc la classe correspondant à une propriété CSS ?
- Tes cartes ont-elles toutes un espacement et des arrondis cohérents ?
- As-tu respecté l'échelle plutôt que d'abuser des crochets ?

:::quiz
Les styles Tailwind ne s'appliquent pas, alors que les classes sont correctes. Que vérifier en premier ?
- [ ] Le navigateur utilisé
- [x] Que les fichiers concernés sont listés dans `content` du fichier `tailwind.config.js`
- [ ] Que les noms de classes sont en majuscules
- [ ] Que le projet utilise TypeScript
> Tailwind ne génère que les classes qu'il trouve dans les fichiers listés dans `content`. Un fichier oublié donne des classes sans effet.
:::

## Erreurs fréquentes

- **Oublier `@tailwind base; components; utilities;`** dans le CSS : aucune classe ne fonctionne.
- **Ne pas importer le fichier CSS** dans `main.jsx`.
- **Un chemin incorrect dans `content`.** Les classes écrites dans un fichier non listé n'existent pas.
- **Construire un nom de classe dynamiquement** : `` `bg-${couleur}-500` `` n'est jamais détecté. Écris les classes complètes dans le code (`'bg-green-500'`), même derrière une condition.
- **Mélanger `class` et `className`** en React : en JSX, c'est `className`.
- **Installer la version 4 par erreur** en suivant un tutoriel récent : la configuration est différente. Fixe `tailwindcss@3`.
- **Confondre `p-4` et `m-4`** : l'un est interne, l'autre externe.

## Bonnes pratiques

- Reste dans l'échelle (`p-4`, `text-lg`) et réserve les crochets aux exceptions.
- Écris les classes dans un ordre cohérent : disposition, boîte, typographie, couleurs, états ; laisse Prettier les trier.
- Installe IntelliSense dès le premier jour.
- Évite de copier-coller le même bloc de classes dix fois : fais-en un composant.
- Lis la documentation par propriété CSS, pas par mémoire.
- Écris des classes complètes et statiques pour que Tailwind les détecte.

## À retenir

- Tailwind est une bibliothèque de classes **utilitaires** à assembler dans le HTML.
- L'installation v3 : `tailwindcss`, `postcss`, `autoprefixer`, `tailwind.config.js` avec `content`, et les trois directives `@tailwind`.
- Tailwind ne génère que les classes qu'il trouve dans les fichiers de `content`.
- L'échelle de design (1 unité = 4 px, palettes 50 à 950) garantit la cohérence.
- Les crochets `w-[340px]` sont une issue de secours pour les valeurs hors échelle.
- On évite la répétition en créant des **composants**, pas des classes CSS.
