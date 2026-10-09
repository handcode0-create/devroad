---
title: Dark mode et tokens
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Beaucoup d'utilisateurs préfèrent un thème sombre, surtout le soir ou sur écran OLED, où il économise aussi de la batterie. Mais un « mode sombre » réussi ne consiste pas à inverser les couleurs : il demande un système. Ce chapitre t'apprend à activer le dark mode avec Tailwind, puis à construire des **tokens de design** (couleurs sémantiques, polices, rayons) pour que changer de thème, ou de marque, devienne une opération simple.

À la fin du chapitre, tu seras capable de :

- choisir entre les stratégies `media` et `class` pour le mode sombre ;
- utiliser le préfixe `dark:` et construire un bouton de bascule qui mémorise le choix ;
- éviter le « flash » de mauvais thème au chargement ;
- personnaliser le thème Tailwind (`theme.extend`) : couleurs, polices, rayons, ombres ;
- définir des **tokens sémantiques** avec des variables CSS qui changent selon le thème ;
- vérifier les contrastes de couleur.

Prérequis : les chapitres précédents sur Tailwind v3. Prévois deux heures et demie.

## Le préfixe dark: et les deux stratégies

Le préfixe `dark:` applique une classe uniquement en mode sombre :

```html
<div class="bg-white text-slate-900 dark:bg-slate-900 dark:text-slate-100">
  Contenu
</div>
```

Tailwind propose deux stratégies, configurées dans `tailwind.config.js` avec la clé `darkMode`.

### La stratégie `media` (par défaut)

Le mode sombre suit la **préférence du système** de l'utilisateur (`prefers-color-scheme: dark`). Rien à configurer, aucun JavaScript, mais l'utilisateur ne peut pas choisir autre chose sur ton site.

### La stratégie `class`

Le mode sombre s'active quand un ancêtre (en général `<html>`) possède la classe `dark`. Tu contrôles tout :

```js
// tailwind.config.js
export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{js,jsx}'],
  // ...
};
```

Avec `class`, tu proposes trois choix à l'utilisateur : **clair**, **sombre** ou **suivre le système**. C'est l'approche la plus répandue pour une application.

> **Astuce** : sur Tailwind 3.4 et plus, `darkMode: ['selector', '[data-theme="sombre"]']` permet d'utiliser un attribut personnalisé à la place de la classe `dark`. C'est pratique si ton projet utilise déjà un attribut de thème.

:::quiz
Quelle stratégie permet à l'utilisateur de choisir lui-même le thème sur ton site ?
- [ ] `media`, car elle lit la préférence du système
- [x] `class`, car tu ajoutes ou retires la classe `dark` selon son choix
- [ ] Aucune, Tailwind ne gère pas le mode sombre
- [ ] `media`, mais seulement sur mobile
> Avec `class`, c'est ton code qui décide quand la classe `dark` est présente, ce qui permet un bouton de bascule et un choix mémorisé.
:::

## Un bouton de bascule qui mémorise le choix

Voici un hook React qui gère trois modes : `clair`, `sombre`, `systeme`.

```jsx
// src/hooks/useTheme.js
import { useEffect, useState } from 'react';

const CLE = 'theme';

function appliquer(mode) {
  const systemeSombre = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const sombre = mode === 'sombre' || (mode === 'systeme' && systemeSombre);
  document.documentElement.classList.toggle('dark', sombre);
}

export function useTheme() {
  const [mode, setMode] = useState(() => {
    try {
      return localStorage.getItem(CLE) || 'systeme';
    } catch {
      return 'systeme';
    }
  });

  useEffect(() => {
    appliquer(mode);
    try {
      localStorage.setItem(CLE, mode);
    } catch {
      /* stockage indisponible : on ignore */
    }

    if (mode !== 'systeme') return;
    const media = window.matchMedia('(prefers-color-scheme: dark)');
    const onChange = () => appliquer('systeme');
    media.addEventListener('change', onChange);
    return () => media.removeEventListener('change', onChange);
  }, [mode]);

  return [mode, setMode];
}
```

Le composant de bascule :

```jsx
import { useTheme } from '@/hooks/useTheme';

const options = [
  { valeur: 'clair', label: 'Clair' },
  { valeur: 'sombre', label: 'Sombre' },
  { valeur: 'systeme', label: 'Système' },
];

export default function ThemeSwitch() {
  const [mode, setMode] = useTheme();
  return (
    <div role="radiogroup" aria-label="Thème" className="inline-flex rounded-lg bg-slate-100 p-1 dark:bg-slate-800">
      {options.map((o) => (
        <button
          key={o.valeur}
          role="radio"
          aria-checked={mode === o.valeur}
          onClick={() => setMode(o.valeur)}
          className={
            mode === o.valeur
              ? 'rounded-md bg-white px-3 py-1 text-sm font-medium text-slate-900 shadow-sm dark:bg-slate-700 dark:text-white'
              : 'rounded-md px-3 py-1 text-sm text-slate-600 dark:text-slate-300'
          }
        >
          {o.label}
        </button>
      ))}
    </div>
  );
}
```

### Éviter le flash au chargement

Si le thème n'est appliqué qu'après le chargement de React, l'utilisateur voit un éclair blanc avant le passage en sombre. Pour l'éviter, applique le thème avec un petit script **bloquant** dans le `<head>` de `index.html`, avant le CSS :

```html
<script>
  (function () {
    try {
      var mode = localStorage.getItem('theme') || 'systeme';
      var sombre = window.matchMedia('(prefers-color-scheme: dark)').matches;
      if (mode === 'sombre' || (mode === 'systeme' && sombre)) {
        document.documentElement.classList.add('dark');
      }
    } catch (e) {}
  })();
</script>
```

Ajoute aussi `color-scheme` pour que les éléments natifs (barres de défilement, champs) suivent le thème : classe `dark:[color-scheme:dark]` sur `html`, ou règle CSS équivalente.

## Personnaliser le thème de Tailwind

Le fichier `tailwind.config.js` contient la clé `theme`. Deux manières de la modifier :

- `theme.extend` : **ajoute** tes valeurs à celles de Tailwind (cas courant) ;
- `theme` directement : **remplace** complètement une section (par exemple toute la palette).

```js
// tailwind.config.js
import defaultTheme from 'tailwindcss/defaultTheme';

export default {
  darkMode: 'class',
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        marque: {
          50: '#eef2ff',
          100: '#e0e7ff',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          900: '#312e81',
        },
      },
      fontFamily: {
        sans: ['Inter', ...defaultTheme.fontFamily.sans],
        titre: ['"Plus Jakarta Sans"', ...defaultTheme.fontFamily.sans],
      },
      borderRadius: {
        carte: '1rem',
      },
      boxShadow: {
        douce: '0 4px 24px -4px rgb(15 23 42 / 0.08)',
      },
    },
  },
  plugins: [],
};
```

Tu peux maintenant écrire `bg-marque-600`, `font-titre`, `rounded-carte`, `shadow-douce`. Chaque valeur du thème génère automatiquement toutes les classes correspondantes (`text-marque-600`, `border-marque-500`, `ring-marque-500`…).

Pour la police, charge-la dans ta page (Google Fonts avec `font-display: swap`, ou en local pour économiser des données) puis référence-la dans `fontFamily`.

## Les tokens sémantiques

Nommer une couleur d'après sa teinte (`marque-600`, `slate-900`) pose un problème en thème sombre : tu dois écrire `bg-white dark:bg-slate-900` partout. Un meilleur système donne des noms selon le **rôle** : `fond`, `surface`, `texte`, `texte-discret`, `bordure`, `accent`. Les composants utilisent ces noms ; seuls les **valeurs** changent selon le thème.

On y arrive avec des **variables CSS**. D'abord les valeurs, dans `index.css`. On les écrit en canaux RGB pour que Tailwind puisse gérer l'opacité :

```css
@tailwind base;
@tailwind components;
@tailwind utilities;

@layer base {
  :root {
    --fond: 248 250 252;        /* slate-50 */
    --surface: 255 255 255;     /* blanc */
    --texte: 15 23 42;          /* slate-900 */
    --texte-discret: 71 85 105; /* slate-600 */
    --bordure: 226 232 240;     /* slate-200 */
    --accent: 79 70 229;        /* indigo-600 */
    --accent-texte: 255 255 255;
  }

  .dark {
    --fond: 2 6 23;             /* slate-950 */
    --surface: 15 23 42;        /* slate-900 */
    --texte: 241 245 249;       /* slate-100 */
    --texte-discret: 148 163 184; /* slate-400 */
    --bordure: 51 65 85;        /* slate-700 */
    --accent: 129 140 248;      /* indigo-400 */
    --accent-texte: 15 23 42;
  }
}
```

Puis on les branche dans la configuration Tailwind, avec la syntaxe `<alpha-value>` qui permet des classes comme `bg-accent/20` :

```js
// tailwind.config.js (extrait)
theme: {
  extend: {
    colors: {
      fond: 'rgb(var(--fond) / <alpha-value>)',
      surface: 'rgb(var(--surface) / <alpha-value>)',
      texte: {
        DEFAULT: 'rgb(var(--texte) / <alpha-value>)',
        discret: 'rgb(var(--texte-discret) / <alpha-value>)',
      },
      bordure: 'rgb(var(--bordure) / <alpha-value>)',
      accent: {
        DEFAULT: 'rgb(var(--accent) / <alpha-value>)',
        texte: 'rgb(var(--accent-texte) / <alpha-value>)',
      },
    },
  },
},
```

Les composants deviennent indépendants du thème :

```html
<article class="rounded-carte border border-bordure bg-surface p-6 text-texte shadow-douce">
  <h2 class="font-titre text-xl font-bold">Roadmap React</h2>
  <p class="mt-2 text-texte-discret">Sept chapitres pour maîtriser les bases.</p>
  <button class="mt-4 rounded-lg bg-accent px-4 py-2 font-medium text-accent-texte hover:bg-accent/90">
    Commencer
  </button>
</article>
```

Aucun `dark:` dans ce bloc : le thème sombre s'applique parce que la classe `dark` redéfinit les variables. Les avantages sont nets :

- **moins de classes** à écrire et moins d'oublis ;
- **un seul endroit** à modifier pour retoucher une couleur ;
- possibilité d'ajouter **d'autres thèmes** (une marque cliente, un thème à fort contraste) en redéfinissant les mêmes variables sous un autre sélecteur, par exemple `[data-theme="ocean"]`.

Garde malgré tout `dark:` pour les cas ponctuels, comme une ombre ou une image à adoucir en mode sombre.

:::quiz
Quel est l'intérêt principal des tokens sémantiques comme `bg-surface` et `text-texte` ?
- [ ] Ils rendent le CSS final plus petit à coup sûr
- [x] Ils décrivent un rôle et changent de valeur selon le thème, sans répéter `dark:` partout
- [ ] Ils remplacent entièrement la configuration de Tailwind
- [ ] Ils fonctionnent sans variables CSS
> Un token décrit l'usage (surface, texte, accent). Les variables CSS prennent des valeurs différentes en thème sombre, donc les composants n'ont pas à connaître le thème.
:::

## Les contrastes et la qualité d'un thème sombre

Un bon thème sombre est un **travail de design**, pas une inversion :

- **évite le noir pur** (`#000`) et le blanc pur sur de grandes surfaces : un gris très foncé (`slate-950`, `slate-900`) fatigue moins l'œil ;
- **adoucis les couleurs d'accent** : un indigo `600` saturé sur fond sombre vibre ; utilise une nuance plus claire (`400`) ;
- **crée la profondeur par la luminosité** plutôt que par les ombres : plus une surface est « proche », plus elle est claire (`bg-slate-900` sur `bg-slate-950`) ;
- **vérifie le contraste** : le rapport minimal recommandé par les règles WCAG AA est de **4,5:1** pour le texte courant et **3:1** pour le grand texte et les éléments d'interface. Les outils de développement des navigateurs affichent le rapport en survolant une couleur dans l'inspecteur ;
- **traite les images** : ajoute par exemple `dark:brightness-90` pour réduire l'éblouissement ;
- **les ombres** deviennent peu visibles en thème sombre ; utilise plutôt une bordure légère (`border-bordure`).

> **Attention** : ne te fie pas à ton œil sur ton écran uniquement. Teste ton thème sur un téléphone en faible luminosité et avec un simulateur de daltonisme (disponible dans l'onglet « Rendu » des outils de développement de Chrome).

## Atelier guidé : un thème complet

Compte deux heures.

1. Passe `darkMode` à `'class'` dans la configuration.
2. Ajoute le script anti-flash dans `index.html` et vérifie qu'un rechargement en mode sombre ne produit aucun éclair blanc.
3. Crée `useTheme` et `ThemeSwitch` avec les trois modes, et place le composant dans l'en-tête.
4. Teste : change le thème du système pendant que le mode « Système » est actif, la page doit suivre.
5. Définis les variables CSS `--fond`, `--surface`, `--texte`, `--texte-discret`, `--bordure`, `--accent` pour le clair et le sombre.
6. Branche-les dans `theme.extend.colors` avec `<alpha-value>`.
7. Réécris la carte de roadmap sans aucune classe `dark:`.
8. Ajoute une police personnalisée dans `fontFamily` et un rayon `rounded-carte`.
9. Crée un second thème `[data-theme="ocean"]` qui change seulement `--accent`, puis bascule-le en modifiant l'attribut sur `html`.
10. Mesure le contraste du texte discret et de l'accent dans les deux thèmes avec l'inspecteur.

Auto-évaluation :

- Le thème choisi est-il conservé après un rechargement ?
- Y a-t-il un flash au chargement ? Le script anti-flash est-il placé avant le CSS ?
- Tous les contrastes de texte atteignent-ils 4,5:1 ?
- Peux-tu changer la couleur de marque en modifiant une seule variable ?

:::quiz
Pourquoi écrire les variables sous forme de canaux RGB (`79 70 229`) plutôt qu'en hexadécimal ?
- [ ] Parce que l'hexadécimal est interdit en CSS
- [x] Pour que Tailwind puisse appliquer des opacités comme `bg-accent/20` grâce à `<alpha-value>`
- [ ] Parce que RGB est plus rapide à afficher
- [ ] Pour désactiver le mode sombre
> Avec des canaux séparés, Tailwind insère la valeur d'opacité dans `rgb(var(--accent) / <alpha-value>)`, ce qui rend les modificateurs d'opacité utilisables.
:::

## Erreurs fréquentes

- **Oublier `darkMode: 'class'`.** Le bouton ajoute la classe `dark` mais rien ne change, car la stratégie `media` reste active.
- **Placer le script anti-flash après le CSS ou en `defer`.** Il doit être exécuté tôt pour éviter l'éclair.
- **Écrire `dark:` sans la version claire.** Définis toujours la valeur par défaut sans préfixe.
- **Remplacer `theme.colors` au lieu d'étendre.** Tu perds toute la palette par défaut (`slate`, `red`…).
- **Définir des variables sans `<alpha-value>`.** `bg-accent/20` ne fonctionne pas.
- **Variables CSS invalides** : écrire `79, 70, 229` avec des virgules casse la syntaxe `rgb(... / ...)`. Utilise des espaces.
- **Utiliser un noir pur sur un blanc pur** et oublier de vérifier les contrastes.
- **Nommer les tokens d'après la couleur** (`bleu-fonce`) au lieu du rôle.

## Bonnes pratiques

- Choisis la stratégie `class` et propose clair, sombre et système.
- Nomme les tokens par rôle : fond, surface, texte, accent.
- Étends le thème de Tailwind plutôt que de le remplacer.
- Applique le thème avant l'affichage pour éviter le flash.
- Vérifie les contrastes dans les deux thèmes (4,5:1 pour le texte).
- Garde `dark:` pour les exceptions ; laisse les variables faire le travail général.
- Charge peu de polices et de graisses : c'est un coût réseau, important pour un public mobile.

## À retenir

- `dark:` applique un style en mode sombre ; `darkMode: 'class'` donne le contrôle à ton code.
- Un script placé dans le `<head>` applique le thème avant l'affichage et évite le flash.
- `theme.extend` ajoute couleurs, polices, rayons et ombres sans perdre les valeurs par défaut.
- Les **tokens sémantiques** s'appuient sur des variables CSS en canaux RGB et la syntaxe `<alpha-value>`.
- Les composants écrits avec des tokens n'ont presque plus besoin de `dark:`.
- Un thème sombre soigné adoucit les couleurs, utilise la luminosité pour la profondeur et respecte le contraste 4,5:1.
