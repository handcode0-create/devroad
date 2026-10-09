---
title: Projet final CSS
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Dans ce projet de fin de cours, tu vas transformer un contenu HTML brut en une **interface complète, responsive, accessible et maintenable**, en mobilisant tout ce que tu as appris : la cascade, Flexbox, Grid, le responsive, les états et animations, l'architecture. Tu ne partiras pas d'une feuille blanche mais d'un cahier des charges comme en agence : un client, des contraintes, une liste de critères à valider.

À la fin du projet, tu auras produit :

- un **mini design system** (tokens, composants, thème clair et sombre) ;
- une **landing page** de produit et un **tableau de bord** de suivi, tous deux responsive ;
- des états et animations soignés, qui respectent les préférences de mouvement ;
- une architecture CSS organisée en couches et en composants ;
- un score Lighthouse supérieur à 95 en performance et accessibilité sur mobile ;
- une page de documentation de tes composants et un dépôt prêt pour ton portfolio.

Prérequis : les chapitres 1 à 6 du cours CSS et les bases HTML (balises sémantiques, formulaires). Prévois six heures, en plusieurs sessions. **Aucun framework CSS** (ni Tailwind, ni Bootstrap) : tout est écrit à la main, c'est le but.

## Le sujet : l'interface de « DevRoad Pro »

Une version payante imaginaire de DevRoad, destinée aux équipes de formation, a besoin de deux écrans :

1. une **landing page** qui présente le produit et pousse à s'inscrire ;
2. un **tableau de bord** où un formateur suit la progression de ses apprenants.

Le public cible est composé de développeurs et de responsables de formation d'Afrique francophone, qui consultent surtout depuis un smartphone avec une connexion variable. Le design doit donc être **léger, rapide, lisible en plein soleil** et fonctionner dès 320 pixels de largeur.

### Cahier des charges

| Écran | Fichier | Contenu attendu |
| --- | --- | --- |
| Landing page | `index.html` | En-tête et menu, section héros, 3 à 6 fonctionnalités, tarifs (3 offres), témoignages, FAQ, appel à l'action, pied de page |
| Tableau de bord | `dashboard.html` | Barre latérale, en-tête avec recherche, 4 cartes de statistiques, tableau des apprenants, liste d'activité récente, graphique simple en CSS |
| Documentation | `styleguide.html` | Palette, typographie, espacements, boutons, cartes, formulaires et états |

Exigences transversales :

1. **Mobile first** : le style de base vise 360 px ; trois points de rupture maximum.
2. **Design tokens** : toutes les couleurs, espacements, rayons, ombres et tailles de texte passent par des variables à deux niveaux.
3. **Thème clair et sombre** : suit `prefers-color-scheme` et se bascule avec un bouton persistant (`localStorage`).
4. **Architecture** : `@layer`, un fichier par composant, nommage BEM, spécificité plate, aucun `!important` hors utilitaires et réduction de mouvement.
5. **Layout** : Grid pour la structure des pages et les grilles de cartes, Flexbox pour les composants.
6. **Animations** : apparitions échelonnées, transitions sur tous les états interactifs, uniquement `transform` et `opacity`, désactivées si l'utilisateur le demande.
7. **Accessibilité** : navigation entière au clavier, focus visible, contrastes AA, `lang`, titres cohérents, lien d'évitement.
8. **Performance** : CSS total sous 30 Ko non compressé, pas de bibliothèque, une seule police (ou la police système), images en WebP.

## Étape 1 : structure du projet et tokens (45 min)

Prépare l'arborescence :

```text
devroad-pro/
├── index.html
├── dashboard.html
├── styleguide.html
├── css/
│   ├── main.css
│   ├── 1-settings/tokens.css
│   ├── 2-base/reset.css
│   ├── 2-base/typographie.css
│   ├── 3-layout/conteneur.css
│   ├── 3-layout/grille.css
│   ├── 4-components/
│   │   ├── bouton.css
│   │   ├── carte.css
│   │   ├── menu.css
│   │   ├── formulaire.css
│   │   ├── tableau.css
│   │   ├── badge.css
│   │   └── stat.css
│   └── 5-utilities/utilitaires.css
├── js/
│   ├── theme.js
│   └── menu.js
├── img/
└── README.md
```

Dans `main.css`, déclare d'abord les couches, puis importe chaque fichier dans sa couche :

```css
@layer reset, base, layout, composants, utilitaires;

@import url("1-settings/tokens.css");
@import url("2-base/reset.css") layer(reset);
@import url("2-base/typographie.css") layer(base);
@import url("3-layout/conteneur.css") layer(layout);
@import url("3-layout/grille.css") layer(layout);
@import url("4-components/bouton.css") layer(composants);
@import url("5-utilities/utilitaires.css") layer(utilitaires);
```

> **Attention** : en production, `@import` de multiples fichiers ralentit le chargement car le navigateur les découvre en cascade. Pour livrer, concatène-les avec Vite, esbuild ou un outil équivalent (ou fusionne-les à la fin du projet).

Dans `tokens.css`, définis la palette (primitives), les rôles clairs et sombres, l'échelle d'espacements (4, 8, 16, 24, 40, 64 px exprimés en `rem`), l'échelle typographique (fluide avec `clamp()`), les rayons, les ombres et les durées de transition. Place le thème sombre sous `prefers-color-scheme` et sous `[data-theme="sombre"]`.

Commit : « Tokens et architecture ».

## Étape 2 : base, composants de base et styleguide (60 min)

1. Le reset : `box-sizing`, marges nulles, `img { max-width: 100%; height: auto; display: block; }`, formulaires héritant de la police.
2. La typographie : corps à `1rem` avec `line-height: 1.6`, titres avec `clamp()`, largeur de texte limitée à 65 `ch`.
3. Les composants. Pour chacun, nomme en BEM et écris tous ses états :
   - `bouton` avec variantes `--principal`, `--secondaire`, `--fantome`, `--danger` et taille `--grand` ;
   - `carte` avec `__image`, `__titre`, `__texte`, `__action` et la variante `--mise-en-avant` ;
   - `badge` pour les statuts (`--succes`, `--attention`, `--erreur`) ;
   - `champ` et `formulaire` (label, input, select, aide, erreur) ;
   - `stat` (valeur, libellé, variation).
4. Dans `styleguide.html`, affiche chaque composant dans chaque état. Cette page est ton laboratoire et ta documentation.

```css
.bouton {
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: var(--espace-2);
  min-height: 2.75rem;                 /* cible tactile de 44px */
  padding-inline: var(--espace-4);
  border: 0;
  border-radius: var(--rayon);
  background: var(--couleur-principale);
  color: var(--couleur-sur-principale);
  font: inherit;
  font-weight: 600;
  cursor: pointer;
  transition: background-color var(--duree-courte) ease-out,
              transform var(--duree-courte) ease-out;
}

.bouton:hover { background: var(--couleur-principale-survol); }
.bouton:active { transform: translateY(1px); }
.bouton:focus-visible { outline: 3px solid var(--couleur-focus); outline-offset: 3px; }
.bouton:disabled { opacity: 0.5; cursor: not-allowed; }
```

Vérifie le contraste de **chaque** couple texte/fond dans les deux thèmes. Commit.

:::quiz
Pourquoi les composants du design system doivent-ils utiliser des tokens de rôle (comme la couleur principale) plutôt que la palette brute (comme bleu 500) ?
- [ ] Parce que les variables de palette sont plus lentes
- [x] Parce que changer de thème ou d'identité revient à redéfinir les rôles, sans toucher aux composants
- [ ] Parce que la palette brute n'est pas supportée par les navigateurs
- [ ] Parce que cela évite d'écrire des media queries
> Les rôles découplent l'usage (couleur principale, fond) de la valeur. Un thème sombre ou un changement de marque ne demande alors que de nouvelles valeurs de rôle.
:::

## Étape 3 : la landing page (75 min)

Construis `index.html` en mobile first.

1. **En-tête** : logo à gauche, menu à droite. Sur mobile, un bouton déplie le menu (classe + `aria-expanded`) ; à partir de 768 px, c'est une rangée Flexbox. Ajoute le bouton de thème.
2. **Héros** : titre fluide, sous-titre, deux boutons, une illustration (image WebP ou forme en CSS). Grille à une colonne sur mobile, deux colonnes à partir de 900 px avec `align-items: center`.
3. **Fonctionnalités** : six cartes avec `repeat(auto-fit, minmax(min(16rem, 100%), 1fr))`.
4. **Tarifs** : trois offres. La carte du milieu est mise en avant (`--mise-en-avant`) et légèrement surélevée sur grand écran. Chaque offre est une carte avec une liste de bénéfices.
5. **Témoignages** : citations avec `blockquote` et avatars ronds (`aspect-ratio: 1; object-fit: cover`).
6. **FAQ** : `details` et `summary` stylés, avec une flèche qui tourne à l'ouverture grâce à `transform`.
7. **Appel à l'action** final sur un fond contrasté et pied de page en `grid` à plusieurs colonnes.
8. **Animations** : apparition échelonnée des cartes au chargement, uniquement sous `prefers-reduced-motion: no-preference`.

Teste à 320, 360, 768, 1024 et 1440 px : aucun défilement horizontal, aucun texte coupé. Commit.

## Étape 4 : le tableau de bord (75 min)

La mise en page se fait avec `grid-template-areas`.

```css
.app {
  display: grid;
  min-height: 100vh;
  grid-template-areas:
    "entete"
    "principal";
  grid-template-rows: auto 1fr;
}

@media (min-width: 900px) {
  .app {
    grid-template-columns: 16rem 1fr;
    grid-template-areas:
      "menu entete"
      "menu principal";
  }
}
```

1. **Barre latérale** : navigation verticale (liste Flexbox), lien actif marqué par `aria-current="page"` et stylé grâce au sélecteur d'attribut. Sur mobile, elle devient un menu replié.
2. **En-tête** : champ de recherche (`flex: 1`), avatar, bouton de thème.
3. **Statistiques** : quatre cartes `stat` dans une grille auto-adaptative, chacune avec une valeur, un libellé et une variation colorée accompagnée d'une flèche (pas de couleur seule).
4. **Tableau des apprenants** : `table` sémantique (`caption`, `th scope`). Sur mobile, enveloppe-le dans un conteneur `overflow-x: auto` avec `tabindex="0"` et un nom accessible, ou bascule en cartes empilées. Ligne survolée mise en évidence, badges de statut.
5. **Graphique** : un histogramme de progression hebdomadaire construit avec des `div` de hauteur variable (variable CSS `--valeur`) dans une grille, avec une animation de croissance en `transform: scaleY()`. Fournis une alternative textuelle (tableau masqué visuellement ou `aria-label`).
6. **Activité récente** : liste avec points de timeline en pseudo-éléments.
7. **Container query** : la carte de statistique change de disposition selon la largeur de son conteneur.

Commit après chaque bloc.

## Étape 5 : thème, interactions et finitions (30 min)

Écris `theme.js` : lit la préférence enregistrée, sinon celle du système, pose `data-theme` sur `html`, enregistre le choix et met à jour `aria-pressed` du bouton.

```js
const racine = document.documentElement;
const bouton = document.querySelector('[data-theme-toggle]');

function appliquer(theme) {
  racine.dataset.theme = theme;
  bouton?.setAttribute('aria-pressed', String(theme === 'sombre'));
  try { localStorage.setItem('theme', theme); } catch {}
}

const sauvegarde = (() => {
  try { return localStorage.getItem('theme'); } catch { return null; }
})();
const systeme = matchMedia('(prefers-color-scheme: dark)').matches ? 'sombre' : 'clair';
appliquer(sauvegarde ?? systeme);

bouton?.addEventListener('click', () => {
  appliquer(racine.dataset.theme === 'sombre' ? 'clair' : 'sombre');
});
```

Ajoute la réduction des mouvements, un style d'impression minimal pour le tableau de bord, `scroll-behavior: smooth` conditionné au mouvement autorisé, et le lien d'évitement. Passe en revue chaque composant : tous les états sont-ils visibles ?

## Étape 6 : audit, optimisation et livraison (45 min)

1. **Accessibilité** : parcours complet au clavier des trois pages, test avec un lecteur d'écran sur le tableau de bord, Lighthouse et axe.
2. **Contrastes** : vérifie les deux thèmes, y compris les états survolé, désactivé et focus.
3. **Responsive** : 320, 360, 768, 1024, 1440 px, zoom 200 %, mode paysage, vrai téléphone.
4. **Performance** : concatène et minifie le CSS, supprime le code mort avec l'onglet Couverture, vérifie qu'aucune animation ne déclenche de recalcul de mise en page. Lighthouse mobile avec réseau ralenti.
5. **Qualité** : validateur HTML du W3C, Stylelint si disponible, zéro `!important` hors exceptions.
6. **Mise en ligne** gratuite sur Netlify, Cloudflare Pages ou GitHub Pages.
7. **README** : objectif, capture d'écran des deux thèmes, arborescence, décisions d'architecture, scores obtenus, comment lancer le projet.

:::quiz
Tu animes l'apparition d'une carte. Quelle combinaison de propriétés garantit la meilleure fluidité sur un téléphone modeste ?
- [ ] top et left
- [ ] width et height
- [x] transform et opacity
- [ ] margin-top et background
> transform et opacity ne passent que par la composition, sans recalcul de la mise en page : les animations restent fluides.
:::

## Livrables

- le dépôt Git avec un historique de commits lisibles ;
- l'URL des trois pages en ligne ;
- un `README.md` avec captures d'écran et scores Lighthouse ;
- la page `styleguide.html` documentant tous les composants ;
- un court texte (dix lignes) justifiant trois décisions d'architecture et une décision d'accessibilité.

## Liste de validation (critères d'acceptation)

**Architecture et tokens**

- [ ] Couches `@layer` déclarées, un fichier par composant, nommage BEM cohérent
- [ ] Aucune valeur de couleur ou d'espacement en dur dans les composants
- [ ] Tokens à deux niveaux (palette puis rôles)
- [ ] Aucun `!important` hors utilitaires et réduction de mouvement
- [ ] Aucun sélecteur de plus de deux niveaux et aucun identifiant de style

**Layout et responsive**

- [ ] Style de base mobile, trois points de rupture maximum, aucune media query inutile
- [ ] Grid pour la structure et les grilles, Flexbox pour les composants
- [ ] Aucun défilement horizontal de 320 à 1920 px
- [ ] Au moins une container query et une valeur `clamp()`
- [ ] Images avec `max-width: 100%`, `aspect-ratio`, dimensions déclarées

**États et animations**

- [ ] Chaque élément interactif possède des états hover, focus-visible, active et disabled
- [ ] Transitions de 150 à 300 ms, jamais de `transition: all`
- [ ] Seuls `transform` et `opacity` sont animés
- [ ] `prefers-reduced-motion` neutralise toutes les animations

**Thème et accessibilité**

- [ ] Thème clair et sombre, choix mémorisé et valeur initiale selon le système
- [ ] Contrastes AA vérifiés dans les deux thèmes
- [ ] Parcours complet au clavier avec focus toujours visible
- [ ] Information jamais transmise par la couleur seule
- [ ] Lighthouse mobile : accessibilité 95 ou plus, performance 90 ou plus

**Livraison**

- [ ] CSS total sous 30 Ko non compressé
- [ ] Les trois pages en ligne, README complet, styleguide à jour

## Erreurs fréquentes

- **Commencer par le desktop.** Tu défais ensuite des heures de travail pour le mobile.
- **Coder sans tokens, puis les extraire à la fin.** Tu manques des valeurs et les incohérences sont déjà installées.
- **Tester un seul thème.** Le thème sombre révèle des contrastes et des ombres oubliés.
- **Animer des propriétés coûteuses** pour un effet décoratif.
- **Oublier le tableau sur mobile.** Il élargit la page entière si tu ne le gères pas.
- **Négliger les états** de focus et de désactivation des composants.
- **Une architecture trop ambitieuse.** Mieux vaut peu de fichiers cohérents que cinquante vides.
- **Ne pas tester sur un vrai téléphone**, où surgissent les problèmes de taille de cible et de lisibilité.

## Bonnes pratiques

- Construis d'abord le styleguide : un composant validé là-bas se réutilise partout sans surprise.
- Avance par petites étapes testables et commite à chaque bloc terminé.
- Teste dès le début en mode sombre, en réduction de mouvement et avec le clavier.
- Réutilise ta grille et tes composants plutôt que de réécrire du style par écran.
- Mesure avant d'optimiser : Couverture, Performance, Lighthouse.
- Documente tes décisions : un README clair vaut un entretien d'embauche.
- Demande à une personne extérieure d'essayer l'interface sans t'aider, sur son téléphone.
- Garde ce projet comme point de départ pour les futures commandes de HANCODE STUDIO ou de tes clients.

## À retenir

- Un projet CSS professionnel s'appuie sur une **architecture** : couches, composants, tokens, nommage cohérent.
- La combinaison **mobile first + Grid + Flexbox + clamp** couvre l'essentiel du responsive sans accumuler les media queries.
- Un **design system** documenté (styleguide) accélère toutes les pages suivantes et garantit la cohérence.
- Les **thèmes** se pilotent par des variables de rôle, sans toucher aux composants.
- Les animations utiles utilisent `transform` et `opacity` et respectent les préférences de mouvement.
- L'accessibilité et la performance se vérifient sur des cas réels, pas seulement dans un outil.
- Tu disposes maintenant des bases CSS pour passer sereinement à Tailwind, à React et à la conception de vrais produits.
