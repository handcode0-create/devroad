---
title: Projet final Tailwind
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu connais maintenant les utilitaires, le layout, le responsive, les états, le dark mode, les tokens et l'architecture. Ce projet final les rassemble dans une réalisation que tu peux montrer à un client ou ajouter à ton portfolio : **une landing page professionnelle et un mini design system** pour un produit fictif, **KôroPay**, une application de paiement mobile pour petits commerçants d'Abidjan (Wave, Orange Money, MTN MoMo).

À la fin du projet, tu auras :

- une page marketing complète, **responsive** de 360 px à 1536 px ;
- un **thème clair et sombre** reposant sur des tokens sémantiques ;
- une petite **bibliothèque de composants** (bouton, carte, badge, champ, accordéon) ;
- une page de démonstration du design system ;
- un build de production léger, accessible et vérifié.

Prérequis : les six chapitres précédents. Tu peux réaliser le projet en HTML pur avec Vite, ou en React si tu es à l'aise. Les exemples utilisent React. Prévois six heures, à répartir sur plusieurs sessions.

## Cahier des charges

### Contexte

KôroPay est une application qui permet à un commerçant d'accepter des paiements par Mobile Money et de suivre ses ventes. L'équipe veut une page d'accueil qui rassure, explique en trente secondes et donne envie de s'inscrire. Le public cible utilise surtout un smartphone Android d'entrée de gamme avec une connexion variable : la page doit être **légère et lisible**.

### Sections de la page

1. **Barre de navigation** : logo, liens d'ancrage, bouton « Créer mon compte », bascule de thème, menu mobile.
2. **Héros** : titre accrocheur, sous-titre, deux boutons (principal et secondaire), illustration ou maquette de téléphone réalisée en CSS.
3. **Bandeau de confiance** : logos ou noms des moyens de paiement pris en charge.
4. **Fonctionnalités** : six cartes en grille responsive avec icône, titre et description.
5. **Comment ça marche** : trois étapes numérotées.
6. **Tarifs** : trois formules (Gratuit, Pro, Business) dont une mise en avant, tarifs en FCFA.
7. **Témoignages** : trois citations de commerçants fictifs.
8. **Questions fréquentes** : accordéon accessible (éléments `details` et `summary`).
9. **Appel à l'action final** avec formulaire d'inscription (nom, téléphone, e-mail).
10. **Pied de page** : liens, réseaux, mentions.

### Contraintes techniques

- Tailwind CSS **v3** avec `darkMode: 'class'` ;
- couleurs, rayons, ombres et polices définis comme **tokens** dans la configuration, sans couleur brute dans les composants ;
- mobile first : tout est conçu à 360 px puis enrichi avec `sm:`, `md:`, `lg:` ;
- aucun défilement horizontal à aucune largeur ;
- états `hover`, `focus-visible`, `active`, `disabled` sur tous les éléments interactifs ;
- animations discrètes, respect de `motion-reduce` ;
- images optimisées, `alt` renseignés, `loading="lazy"` sous la ligne de flottaison ;
- poids total de la page inférieur à 300 Ko hors polices, CSS inférieur à 30 Ko compressé.

### Hors périmètre

Backend d'inscription réel, paiement réel, multilingue. Le formulaire peut afficher un message de succès simulé.

## Phase 1 : mise en place et tokens (45 min)

Crée le projet et installe les dépendances :

```bash
npm create vite@latest koropay -- --template react
cd koropay
npm install
npm install -D tailwindcss@3 postcss autoprefixer @tailwindcss/forms prettier prettier-plugin-tailwindcss
npx tailwindcss init -p
```

Définis d'abord **le design**, avant la moindre section. Choisis une couleur d'accent, une police de titre et une police de texte (deux familles maximum), puis traduis-les en tokens. Voici une base à adapter :

```css
/* src/index.css */
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --fond: 250 250 249;
    --surface: 255 255 255;
    --texte: 28 25 23;
    --texte-discret: 87 83 78;
    --bordure: 231 229 228;
    --accent: 5 122 85;
    --accent-texte: 255 255 255;
    --accent-doux: 209 250 229;
  }
  .dark {
    --fond: 12 10 9;
    --surface: 28 25 23;
    --texte: 250 250 249;
    --texte-discret: 168 162 158;
    --bordure: 68 64 60;
    --accent: 52 211 153;
    --accent-texte: 6 78 59;
    --accent-doux: 6 78 59;
  }
  html { @apply scroll-smooth; }
  body { @apply bg-fond font-sans text-texte antialiased; }
}
```

```js
// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

const token = (nom) => `rgb(var(--${nom}) / <alpha-value>)`;

export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        fond: token('fond'),
        surface: token('surface'),
        texte: { DEFAULT: token('texte'), discret: token('texte-discret') },
        bordure: token('bordure'),
        accent: {
          DEFAULT: token('accent'),
          texte: token('accent-texte'),
          doux: token('accent-doux'),
        },
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        titre: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
      },
      borderRadius: { carte: '1.25rem' },
      boxShadow: { douce: '0 8px 30px -12px rgb(0 0 0 / 0.18)' },
    },
  },
  plugins: [forms],
};
```

La fonction `token` évite de répéter la syntaxe `<alpha-value>`. Ajoute le script anti-flash dans `index.html` (voir le chapitre sur le dark mode) et le hook `useTheme` avec les trois modes.

> **Astuce** : vérifie le contraste de ton accent sur le fond et de `accent-texte` sur l'accent **dès maintenant**, dans les deux thèmes. Corriger une palette plus tard coûte beaucoup plus cher que la valider au départ.

:::quiz
Pourquoi définir les tokens avant de construire les sections ?
- [ ] Parce que Tailwind refuse de compiler sans tokens
- [x] Pour que tous les composants soient cohérents et que le dark mode fonctionne sans répéter `dark:` partout
- [ ] Parce que les tokens accélèrent la connexion réseau
- [ ] Parce que React l'exige
> Des tokens sémantiques posés d'emblée garantissent la cohérence visuelle et un changement de thème sans réécrire les composants.
:::

## Phase 2 : composants du design system (60 min)

Dans `src/components/ui/`, construis les briques réutilisables en t'appuyant sur le chapitre « Composants et états » :

1. **`cn`** dans `src/lib/cn.js`.
2. **`Button`** : variantes `primaire`, `secondaire`, `fantome`, tailles `sm`, `md`, `lg`, prop `href` (rend un lien au lieu d'un bouton), état `disabled`.
3. **`Badge`** : variantes neutre, accent, avertissement.
4. **`Carte`** : conteneur avec `rounded-carte`, `border border-bordure`, `bg-surface`, option `mettreEnAvant` qui ajoute un anneau d'accent.
5. **`Champ`** : label, aide, erreur, état désactivé, `aria-describedby`.
6. **`Accordeon`** : basé sur `details` et `summary` (accessible nativement), avec une flèche qui pivote via `group-open:rotate-180`.
7. **`Section`** : enveloppe avec `id`, titre, sous-titre et un conteneur `mx-auto max-w-6xl px-4 sm:px-6 lg:px-8` pour harmoniser les espacements verticaux (`py-16 md:py-24`).

Voici l'accordéon, qui illustre `group-open` sans une ligne de JavaScript :

```jsx
export default function Accordeon({ question, children }) {
  return (
    <details className="group rounded-xl border border-bordure bg-surface p-4 open:shadow-douce">
      <summary className="flex cursor-pointer list-none items-center justify-between gap-4 font-medium focus-visible:outline focus-visible:outline-2 focus-visible:outline-accent [&::-webkit-details-marker]:hidden">
        {question}
        <span aria-hidden="true" className="transition-transform group-open:rotate-180 motion-reduce:transition-none">
          ▾
        </span>
      </summary>
      <div className="mt-3 text-texte-discret">{children}</div>
    </details>
  );
}
```

Crée aussi une page `/design-system` (ou une section masquée) qui affiche **tous** les composants dans tous leurs états, en clair et en sombre. Elle te sert de référence et de test visuel.

## Phase 3 : navigation et héros (50 min)

### Navigation

- barre `sticky top-0 z-40` avec fond translucide `bg-fond/80 backdrop-blur` et bordure basse ;
- liens d'ancrage visibles à partir de `md:`, menu mobile ouvert par un bouton avec `aria-expanded` et `aria-controls` en dessous ;
- bascule de thème visible sur toutes les tailles ;
- le menu mobile se ferme quand on clique sur un lien.

### Héros

Construis le héros en mobile first : une colonne avec le texte puis la maquette, qui devient deux colonnes à partir de `lg:`.

```jsx
<section className="mx-auto grid max-w-6xl items-center gap-12 px-4 py-16 sm:px-6 lg:grid-cols-2 lg:gap-8 lg:py-24 lg:px-8">
  <div>
    <Badge variante="accent">Paiements Mobile Money simplifiés</Badge>
    <h1 className="mt-4 font-titre text-4xl font-extrabold tracking-tight text-balance sm:text-5xl lg:text-6xl">
      Encaisse tes clients, <span className="text-accent">sans stress</span>.
    </h1>
    <p className="mt-6 max-w-prose text-lg leading-relaxed text-texte-discret">
      Wave, Orange Money et MTN MoMo dans une seule application. Suis tes ventes en temps réel, même avec une connexion lente.
    </p>
    <div className="mt-8 flex flex-col gap-3 sm:flex-row">
      <Button taille="lg" href="#inscription">Créer mon compte</Button>
      <Button taille="lg" variante="secondaire" href="#fonctionnement">Voir comment ça marche</Button>
    </div>
  </div>
  {/* maquette de téléphone en CSS à droite */}
</section>
```

Réalise la maquette de téléphone **en CSS** (un conteneur `aspect-[9/19]`, `rounded-[2.5rem]`, bordure épaisse, quelques barres et pastilles colorées à l'intérieur) pour éviter une image lourde. Ajoute une légère animation d'apparition avec une classe `animate-` personnalisée dans `theme.extend.keyframes`, désactivée avec `motion-reduce`.

## Phase 4 : sections de contenu (80 min)

Construis les sections une par une, en testant à 360 px après chacune.

### Fonctionnalités

Une grille `grid gap-6 sm:grid-cols-2 lg:grid-cols-3` de six cartes. Chaque carte contient une icône dans un carré `bg-accent-doux`, un titre `font-titre` et une description. Utilise des icônes SVG en ligne (par exemple de la bibliothèque Heroicons, en copiant uniquement celles dont tu as besoin) pour éviter une dépendance lourde.

### Comment ça marche

Trois étapes avec un numéro dans un cercle (`flex size-10 items-center justify-center rounded-full bg-accent text-accent-texte`). En mobile, empile verticalement ; à partir de `md:`, place-les en ligne avec un trait de liaison.

### Tarifs

Trois cartes dans une grille `lg:grid-cols-3`. La formule centrale est mise en avant : bordure d'accent, étiquette « Le plus choisi », léger agrandissement `lg:-translate-y-2`. Affiche les prix en FCFA avec séparateur de milliers (`Intl.NumberFormat('fr-FR')`) :

```js
const prix = new Intl.NumberFormat('fr-FR').format(5000); // "5 000"
```

Chaque formule liste ses avantages avec une icône « coche » et se termine par un bouton pleine largeur (`w-full`).

### Témoignages et FAQ

Trois témoignages dans une grille, avec initiales dans un cercle plutôt que des photos (économie de données). La FAQ comprend six questions avec le composant `Accordeon`.

### Inscription

Formulaire avec trois `Champ` (nom, téléphone en `type="tel"`, e-mail), validation simple côté client, états d'erreur et message de succès simulé. Utilise `autocomplete="name"`, `autocomplete="tel"` et `autocomplete="email"` pour faciliter la saisie sur mobile.

:::quiz
À partir de quel moment faut-il tester le rendu à 360 px ?
- [ ] À la fin, une fois toute la page terminée
- [x] Après chaque section, pendant la construction
- [ ] Seulement si un client se plaint
- [ ] Jamais, le responsive est automatique
> Tester à chaque étape évite d'accumuler des défauts de mise en page qui deviennent coûteux à corriger quand la page est terminée.
:::

## Phase 5 : finitions, accessibilité et performance (55 min)

### Accessibilité

- tous les éléments interactifs sont atteignables au clavier dans un ordre logique ;
- chaque champ a un `label` ; les erreurs sont annoncées avec `role="alert"` ;
- un lien d'évitement « Aller au contenu » en tête de page : `<a href="#contenu" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 ...">` ;
- une hiérarchie de titres cohérente : un seul `h1`, des `h2` par section ;
- le contraste du texte atteint 4,5:1 dans les deux thèmes ;
- chaque image décorative a `alt=""`, chaque image informative un `alt` descriptif.

### Performance

- polices : maximum deux familles, deux ou trois graisses chacune, `font-display: swap` ;
- aucune grosse image : privilégie le CSS, les SVG et les formats WebP ou AVIF ;
- `loading="lazy"` sur les images sous la ligne de flottaison ;
- exécute `npm run build` et note le poids du CSS ;
- un audit **Lighthouse** en mode mobile : vise au moins 90 en performance, accessibilité et bonnes pratiques.

### Qualité du code

- lance Prettier avec `prettier-plugin-tailwindcss` sur tout `src/` ;
- vérifie qu'aucun composant de `ui/` ne contient une couleur brute (`bg-emerald-600`, `text-gray-500`) : recherche-les avec ta barre de recherche d'éditeur ;
- supprime les valeurs arbitraires non justifiées ;
- relis chaque `@apply` : est-il vraiment nécessaire ?

## Checklist d'acceptation

Coche chaque ligne avant de déclarer le projet terminé.

**Design et responsive**

- La page est soignée et lisible à 360, 768, 1024 et 1536 px.
- Il n'y a aucun défilement horizontal, à aucune largeur.
- Le menu mobile s'ouvre, se ferme et se pilote au clavier.
- Les trois formules de tarifs passent de une colonne à trois sans casser.
- Les zones tactiles mesurent au moins 44 px.

**Thème**

- Le bouton de bascule propose clair, sombre et système, et le choix est mémorisé.
- Aucun flash blanc au chargement en mode sombre.
- Le contraste du texte atteint 4,5:1 dans les deux thèmes.
- Les composants n'utilisent que des tokens sémantiques.

**Composants et code**

- Les composants `ui/` gèrent hover, focus-visible, active et disabled.
- Aucune classe n'est construite par concaténation partielle.
- La page `/design-system` affiche chaque composant dans ses états.
- Le code est trié par Prettier et rangé dans `ui/`, `layout/`, `features/`.

**Accessibilité et performance**

- Le lien d'évitement fonctionne ; un seul `h1` existe.
- Les formulaires ont labels, erreurs annoncées et attributs `autocomplete`.
- Les animations respectent `motion-reduce`.
- Lighthouse mobile atteint 90 ou plus dans les trois catégories mesurées.
- Le CSS compressé pèse moins de 30 Ko.

## Pour aller plus loin

- Ajoute un second thème de marque (`[data-theme="ocean"]`) qui ne redéfinit que l'accent.
- Écris un plugin Tailwind qui ajoute un utilitaire de dégradé de marque.
- Publie ta bibliothèque `ui/` sous forme de package interne réutilisable.
- Ajoute une version anglaise de la page.
- Migre la page vers Laravel avec des composants Blade en gardant les mêmes classes.

## Erreurs fréquentes

- **Commencer par les sections sans définir les tokens.** Tu réécriras des dizaines de classes.
- **Tester uniquement sur grand écran.** Les défauts de mobile apparaissent à la fin.
- **Mettre des couleurs brutes dans les composants.** Le dark mode se casse alors section par section.
- **Charger de grosses images ou plusieurs familles de polices.** Le public mobile paie le prix.
- **Oublier les états de focus.** Le clavier devient inutilisable.
- **Hériter d'un `h-screen` partout.** Les contenus longs sont coupés sur petit écran.
- **Négliger la FAQ et le formulaire** au profit du héros : ce sont eux qui convertissent.
- **Laisser des classes dynamiques** (`bg-${couleur}-600`) : elles disparaissent du build de production.

## Bonnes pratiques

- Design d'abord (tokens, échelle, typographie), composants ensuite, sections en dernier.
- Un composant par besoin réel, extrait à la troisième répétition.
- Mobile first, avec un test à 360 px à chaque étape.
- Accessibilité intégrée dès le départ, pas ajoutée à la fin.
- Mesure : poids du CSS, score Lighthouse, contraste, avant de livrer.
- Documente le design system dans une page dédiée et un guide de style court.
- Travaille en commits par phase pour pouvoir revenir en arrière.

## À retenir

- Un projet professionnel repose sur un **système** (tokens, composants, conventions) plus que sur une accumulation de classes.
- Le mobile first, les états d'interaction et le dark mode se prévoient dès la première ligne.
- Les tokens sémantiques rendent un thème changeable en un seul endroit.
- L'accessibilité (clavier, contraste, labels, `motion-reduce`) fait partie du résultat fini.
- Un build léger et un audit Lighthouse prouvent la qualité devant un client.
- Une page de démonstration du design system sert de documentation, de test visuel et d'argument commercial.
