---
title: États et animations
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une interface vivante réagit : un bouton qui change au survol, un champ qui se met en évidence quand on le sélectionne, un menu qui glisse, une carte qui apparaît en douceur. Ces **états** et ces **mouvements** guident l'utilisateur, confirment ses actions et donnent une impression de qualité. Mais mal dosés, ils ralentissent la page, fatiguent, ou excluent certaines personnes. Ce chapitre t'apprend à les faire avec élégance, performance et respect de l'accessibilité, uniquement en CSS.

À la fin du chapitre, tu seras capable de :

- styliser les états d'un élément avec les pseudo-classes (`:hover`, `:focus-visible`, `:active`, `:disabled`…) ;
- créer des transitions fluides avec `transition` ;
- transformer des éléments avec `transform` (translation, rotation, échelle) ;
- écrire des animations avec `@keyframes` et `animation` ;
- choisir des propriétés performantes à animer ;
- respecter `prefers-reduced-motion` ;
- construire un bouton, une carte et un indicateur de chargement animés.

Prérequis : les chapitres précédents du cours CSS. Prévois deux heures et demie.

## Les états d'un élément

Un élément interactif a plusieurs états, et chacun doit être visible. Les pseudo-classes les ciblent :

| Pseudo-classe | État |
| --- | --- |
| `:hover` | Le pointeur survole l'élément |
| `:focus-visible` | L'élément a le focus **au clavier** |
| `:active` | L'élément est en cours de clic |
| `:disabled` | Un champ ou bouton désactivé |
| `:checked` | Une case ou un radio coché |
| `:invalid` / `:user-invalid` | Un champ de formulaire invalide |
| `:focus-within` | Un descendant a le focus |
| `[aria-expanded="true"]` | Un contrôle déplié (sélecteur d'attribut) |

Voici un bouton avec tous ses états :

```css
.bouton {
  background: var(--couleur-principale);
  color: white;
  border: 0;
  border-radius: 0.5rem;
  padding: 0.75rem 1.25rem;
  cursor: pointer;
}

.bouton:hover {
  background: var(--couleur-principale-fonce);
}

.bouton:focus-visible {
  outline: 3px solid var(--couleur-focus);
  outline-offset: 3px;
}

.bouton:active {
  transform: translateY(1px);
}

.bouton:disabled {
  background: #cbd5e1;
  color: #64748b;
  cursor: not-allowed;
}
```

### L'ordre compte

Les pseudo-classes ont la même spécificité : l'ordre d'écriture tranche. Respecte la séquence classique pour les liens, souvent mémorisée par « LoVe HAte » : `:link`, `:visited`, `:hover`, `:active`. Place `:focus-visible` et `:disabled` après.

### Ne compte pas sur le survol

Un téléphone n'a pas de survol. N'utilise jamais `:hover` comme **seul** moyen de révéler une information ou une action. Si tu veux un effet réservé aux appareils avec souris, enveloppe-le :

```css
@media (hover: hover) {
  .carte:hover {
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.12);
  }
}
```

:::quiz
Pourquoi faut-il toujours prévoir un état `:focus-visible` pour les éléments interactifs ?
- [ ] Pour que le survol à la souris fonctionne
- [x] Pour que les utilisateurs du clavier voient où ils se trouvent
- [ ] Pour accélérer le chargement de la page
- [ ] Pour désactiver l'animation
> Le focus visible indique la position de l'utilisateur au clavier. Le supprimer sans le remplacer rend l'interface inutilisable pour eux.
:::

## Les transitions

Sans transition, un changement d'état est brutal : la couleur saute d'une valeur à l'autre. La propriété **`transition`** demande au navigateur d'interpoler entre l'état de départ et l'état final :

```css
.bouton {
  background: #2563eb;
  transition: background-color 0.2s ease, transform 0.15s ease;
}

.bouton:hover {
  background: #1d4ed8;
}
```

La transition se déclare sur l'état **de base** (et pas seulement sur `:hover`) pour qu'elle joue aussi au retour. Elle se décompose en quatre éléments :

- **la propriété** concernée (`background-color`, `transform`, ou `all` à éviter) ;
- **la durée** (`0.2s`) ;
- **la fonction de timing** qui donne le rythme ;
- **le délai** facultatif avant le début.

### Les fonctions de timing

| Valeur | Effet |
| --- | --- |
| `linear` | Vitesse constante (mécanique) |
| `ease` | Démarrage et fin doux (par défaut) |
| `ease-in` | Démarre lentement |
| `ease-out` | Termine lentement, naturel pour une apparition |
| `ease-in-out` | Doux aux deux extrémités |
| `cubic-bezier(…)` | Courbe personnalisée |

Pour des interfaces, privilégie `ease-out` pour ce qui apparaît ou répond à l'utilisateur, `ease-in` pour ce qui disparaît. Les durées d'interface se situent généralement **entre 150 et 300 ms** : en dessous, on ne voit rien, au-dessus, on attend. Les grands mouvements d'ensemble peuvent durer jusqu'à 500 ms.

> **Attention** : `transition: all 0.3s` est pratique mais dangereux. Il anime aussi des propriétés que tu n'avais pas prévues (dimensions, polices) et peut coûter cher en performance. Liste explicitement les propriétés.

## Transform : déplacer sans casser la mise en page

La propriété **`transform`** modifie l'apparence d'un élément **sans changer sa place dans le flux** : les voisins ne bougent pas. C'est pour cela qu'elle est idéale pour animer.

```css
.carte:hover {
  transform: translateY(-4px);           /* monte de 4 pixels */
}

.icone:hover {
  transform: rotate(15deg) scale(1.1);   /* tourne et grossit */
}

.image-zoom {
  transition: transform 0.4s ease-out;
}
.image-zoom:hover {
  transform: scale(1.05);
}
```

Les fonctions principales sont `translate(x, y)` (déplacement), `scale(n)` (échelle), `rotate(angle)` et `skew()`. Elles se combinent dans une seule déclaration, l'ordre comptant. Les propriétés `transform-origin` (le point de pivot), `translate`, `scale` et `rotate` existent aussi séparément pour animer chacune indépendamment.

Pour un effet de zoom sur une image sans que celle-ci déborde, cache l'excédent sur le parent avec `overflow: hidden`.

## Les performances : quoi animer ?

Quand une propriété change, le navigateur peut devoir refaire trois étapes : calculer la **mise en page** (*layout*), **dessiner** (*paint*), puis **composer** les couches (*composite*). Plus tu déclenches d'étapes, plus c'est coûteux :

| Propriété animée | Étapes déclenchées | Coût |
| --- | --- | --- |
| `width`, `height`, `margin`, `top`, `left` | Layout + paint + composite | Élevé |
| `color`, `background`, `box-shadow` | Paint + composite | Moyen |
| `transform`, `opacity` | Composite seulement | Faible |

La règle d'or : **n'anime que `transform` et `opacity`** autant que possible. Pour faire glisser un panneau, utilise `translateX` plutôt que `left`. Pour faire apparaître, `opacity` plutôt que `display`. Sur un téléphone d'entrée de gamme, c'est la différence entre une animation fluide à 60 images par seconde et une qui saccade.

Pour dire au navigateur de préparer l'animation, `will-change: transform` existe, mais c'est un outil de dernier recours : utilisé partout, il consomme de la mémoire. Applique-le seulement à un élément qui a un problème de fluidité avéré.

:::quiz
Quelle paire de propriétés est la plus performante à animer ?
- [ ] width et height
- [ ] top et left
- [x] transform et opacity
- [ ] margin et padding
> transform et opacity ne déclenchent que l'étape de composition, sans recalculer la mise en page ni repeindre : elles restent fluides même sur des appareils modestes.
:::

## Les animations avec @keyframes

Une transition va d'un état A à un état B **en réponse à un changement**. Une **animation** se joue d'elle-même, peut comporter plusieurs étapes, se répéter et ne dépend pas d'un événement. On la décrit en deux temps : les images clés, puis son application.

```css
@keyframes apparition {
  from {
    opacity: 0;
    transform: translateY(1rem);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.carte {
  animation: apparition 0.5s ease-out both;
}
```

Les propriétés de `animation`, que tu peux écrire en raccourci ou séparément :

- `animation-name` : le nom des `@keyframes` ;
- `animation-duration` : la durée ;
- `animation-timing-function` : le rythme ;
- `animation-delay` : le délai de départ ;
- `animation-iteration-count` : nombre de répétitions (`infinite` pour une boucle) ;
- `animation-direction` : `normal`, `reverse`, `alternate` (aller-retour) ;
- `animation-fill-mode` : `both` conserve l'état de départ pendant le délai et l'état final après l'animation (sans lui, l'élément « saute » à la fin).

Les images clés peuvent avoir des étapes en pourcentage :

```css
@keyframes pulsation {
  0%   { transform: scale(1); }
  50%  { transform: scale(1.08); }
  100% { transform: scale(1); }
}

.badge-nouveau {
  animation: pulsation 1.6s ease-in-out infinite;
}
```

### Un indicateur de chargement

Un cercle qui tourne se fait avec un élément, une bordure et une rotation :

```html
<span class="spinner" role="status" aria-label="Chargement en cours"></span>
```

```css
.spinner {
  display: inline-block;
  width: 2rem;
  height: 2rem;
  border: 3px solid #e2e8f0;
  border-top-color: #2563eb;
  border-radius: 50%;
  animation: tourne 0.8s linear infinite;
}

@keyframes tourne {
  to { transform: rotate(360deg); }
}
```

Il est important de donner à l'élément un nom accessible, car l'animation seule ne dit rien à un lecteur d'écran. On utilise `linear` pour que la rotation soit régulière.

### Un effet d'apparition échelonné

En jouant sur le délai grâce à une variable, plusieurs éléments apparaissent en cascade :

```css
.liste-cartes > .carte {
  animation: apparition 0.5s ease-out both;
  animation-delay: calc(var(--i) * 80ms);
}
```

```html
<article class="carte" style="--i: 0">…</article>
<article class="carte" style="--i: 1">…</article>
<article class="carte" style="--i: 2">…</article>
```

## Respecter l'utilisateur : prefers-reduced-motion

Pour certaines personnes (troubles vestibulaires, migraines, épilepsie photosensible, troubles de l'attention), les mouvements à l'écran provoquent nausées ou malaises. Les systèmes d'exploitation permettent de demander de **réduire les animations**, et CSS le détecte :

```css
@media (prefers-reduced-motion: reduce) {
  *,
  *::before,
  *::after {
    animation-duration: 0.01ms !important;
    animation-iteration-count: 1 !important;
    transition-duration: 0.01ms !important;
    scroll-behavior: auto !important;
  }
}
```

Ce bloc de sécurité coupe les mouvements sur tout le site. Mieux encore, adopte l'approche inverse : n'active les animations décoratives que si l'utilisateur ne s'y oppose pas.

```css
@media (prefers-reduced-motion: no-preference) {
  .carte {
    animation: apparition 0.5s ease-out both;
  }
}
```

Autres règles de bon sens : ne fais **jamais clignoter** un contenu plus de trois fois par seconde, offre un moyen de mettre en pause tout mouvement automatique de plus de cinq secondes (carrousels), et ne transmets aucune information importante **uniquement** par une animation.

## Aller plus loin : quelques outils modernes

Quelques fonctionnalités récentes méritent d'être connues (vérifie leur support sur caniuse.com avant de les employer) :

- `@starting-style` et `transition-behavior: allow-discrete` permettent d'animer l'apparition d'un élément passant de `display: none` à visible ;
- les **animations pilotées par le défilement** (`animation-timeline: scroll()` ou `view()`) évitent du JavaScript pour des effets au scroll ;
- les **View Transitions** animent le passage entre deux états ou deux pages.

Pour des séquences complexes (timelines, scroll storytelling, SVG), des bibliothèques comme GSAP prennent le relais, mais les bases de ce chapitre te suffisent pour 90 % des besoins d'une interface.

> **Astuce** : dans l'onglet Performance et dans le panneau Animations des outils de développement, tu peux ralentir, rejouer et inspecter les animations. Les « Paint flashing » colorent les zones repeintes pour repérer les animations coûteuses.

## Atelier guidé : un bouton, une carte et un chargement animés

Compte une heure et demie.

1. Crée `etats.html` et `css/etats.css` avec les variables habituelles. Ajoute un bouton principal, un bouton désactivé, un champ de texte, une case à cocher et trois cartes.
2. Donne au bouton tous ses états : `:hover`, `:focus-visible`, `:active` et `:disabled`. Vérifie chacun au clavier et à la souris.
3. Ajoute `transition` (couleur de fond et `transform`) en listant explicitement les propriétés. Règle la durée entre 150 et 250 ms.
4. Style le champ : bordure qui change au `:focus`, contour visible avec `:focus-visible`, et rouge discret avec `:user-invalid`. Ajoute `:focus-within` sur le conteneur du champ pour mettre en évidence son label.
5. Crée la carte avec un effet de survol : légère élévation par `translateY` et ombre plus marquée, uniquement dans `@media (hover: hover)`.
6. Ajoute à l'image de la carte un zoom au survol avec `overflow: hidden` sur le parent.
7. Écris `@keyframes apparition` et applique-le aux cartes avec un délai échelonné calculé par une variable `--i`.
8. Construis le spinner de chargement avec un nom accessible.
9. Ajoute le bloc `prefers-reduced-motion` : active le réglage de ton système (ou l'émulation dans les outils de développement, onglet Rendu) et vérifie que tout se fige.
10. Ouvre le panneau Performance, enregistre un survol de carte et vérifie qu'aucun recalcul de mise en page n'apparaît. Remplace volontairement `translateY` par `top` pour comparer.

Auto-évaluation : peux-tu justifier chaque durée choisie ? Chaque animation a-t-elle une alternative pour les personnes qui réduisent les mouvements ?

## Erreurs fréquentes

- **Déclarer la transition seulement sur `:hover`.** Elle ne joue qu'à l'aller, pas au retour.
- **Utiliser `transition: all`.** Des propriétés non prévues s'animent, parfois au détriment de la performance.
- **Animer `width`, `height`, `top` ou `left`.** Préfère `transform` et `opacity`.
- **Supprimer le focus** ou ne prévoir un effet qu'au survol.
- **Oublier `animation-fill-mode`.** L'élément réapparaît dans son état initial à la fin.
- **Des durées trop longues.** Une interface qui fait attendre 1 seconde pour chaque survol énerve.
- **Ignorer `prefers-reduced-motion`.** Certaines personnes sont rendues malades par les mouvements.
- **Faire clignoter ou rebondir sans fin.** Cela distrait, fatigue et peut être dangereux.

## Bonnes pratiques

- Donne un style distinct à **chaque état** interactif, focus au clavier compris.
- Garde les transitions courtes (150 à 300 ms) et utilise `ease-out` pour les réponses à l'utilisateur.
- Anime `transform` et `opacity` en priorité, et liste les propriétés explicitement.
- Réserve les effets de survol aux appareils qui le permettent avec `@media (hover: hover)`.
- Prévois toujours une version réduite sous `prefers-reduced-motion`.
- Utilise le mouvement pour **informer** (apparition, confirmation, hiérarchie), pas seulement pour décorer.
- Teste sur un téléphone d'entrée de gamme et surveille les images par seconde.
- Garde les animations discrètes et cohérentes dans tout le produit.

## À retenir

- Les pseudo-classes (`:hover`, `:focus-visible`, `:active`, `:disabled`, `:checked`…) permettent de styliser chaque état ; le survol n'existe pas sur mobile.
- `transition` interpole entre deux états ; `animation` avec `@keyframes` joue des séquences autonomes.
- `transform` déplace sans perturber la mise en page ; avec `opacity`, c'est ce qu'il faut animer.
- Durées d'interface : 150 à 300 ms, avec `ease-out` pour les apparitions.
- `animation-fill-mode: both` conserve les états de début et de fin.
- `prefers-reduced-motion` est indispensable pour l'accessibilité.
- Mesure la fluidité avec les outils de performance du navigateur.
