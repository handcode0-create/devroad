---
title: Architecture CSS
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Écrire du CSS pour une page est facile. Écrire du CSS pour un produit qui vit pendant des années, sur lequel travaillent plusieurs personnes et qui compte des centaines de composants, est un tout autre métier. Sans règles, les feuilles de style deviennent un champ de mines : on n'ose plus rien supprimer, chaque modification casse un écran lointain, la spécificité explose, et les `!important` s'accumulent. L'**architecture CSS** est l'ensemble des conventions qui évitent ce destin.

À la fin du chapitre, tu seras capable de :

- diagnostiquer les symptômes d'un CSS mal organisé ;
- nommer tes classes avec la méthodologie **BEM** ;
- structurer tes fichiers selon une organisation en couches ;
- créer un système de **design tokens** avec les variables CSS ;
- maîtriser la spécificité avec `@layer`, `:where()` et la règle du « plat » ;
- comparer les approches utilitaires (Tailwind), les modules CSS et le CSS scopé, en lien avec React et DevRoad ;
- mettre en place un thème clair et sombre piloté par des variables.

Prérequis : les chapitres 1 à 5 du cours CSS. Prévois deux heures et demie.

## Les symptômes d'un CSS qui dérive

Voici ce qu'on observe dans un projet sans architecture :

- des **sélecteurs longs** qui dépendent de la structure (`.page .contenu ul li a.lien`) ;
- des `!important` partout, parce qu'une règle précédente était trop forte ;
- des **noms vagues** (`.box`, `.container2`, `.rouge`) dont personne ne connaît le rôle ;
- du **CSS mort** : des règles que plus aucun HTML n'utilise, que personne n'ose retirer ;
- des **valeurs en doublon** : douze nuances de bleu presque identiques, dix espacements différents ;
- des **effets de bord** : changer un bouton modifie un formulaire ailleurs ;
- un fichier unique de 3 000 lignes dans lequel on perd une heure à retrouver un style.

Toutes ces maladies ont la même cause : le CSS est **global**. Une règle écrite quelque part peut toucher n'importe quel élément de n'importe quelle page. Toute architecture CSS est donc une stratégie pour **limiter la portée** des styles et **rendre leur effet prévisible**.

> **À retenir** : une bonne architecture répond à trois questions pour chaque règle : où la ranger, comment la nommer, et quelle force lui donner.

## Nommer : la méthodologie BEM

**BEM** (*Block, Element, Modifier*) est une convention de nommage qui rend le rôle d'une classe lisible dès qu'on la lit.

- **Block** : un composant autonome (`carte`, `menu`, `bouton`).
- **Element** : une partie du bloc, qui n'a pas de sens hors de lui (`carte__titre`, `menu__lien`). Séparateur : deux tirets bas.
- **Modifier** : une variante ou un état (`carte--mise-en-avant`, `bouton--grand`). Séparateur : deux tirets.

```html
<article class="carte carte--mise-en-avant">
  <img class="carte__image" src="laravel.webp" alt="" width="400" height="225">
  <h3 class="carte__titre">Roadmap Laravel</h3>
  <p class="carte__texte">12 chapitres pour maîtriser le framework.</p>
  <a class="carte__action bouton bouton--principal" href="/roadmaps/laravel">
    Commencer
  </a>
</article>
```

```css
.carte {
  display: flex;
  flex-direction: column;
  border: 1px solid var(--bordure);
  border-radius: var(--rayon);
}

.carte__titre {
  font-size: 1.25rem;
}

.carte__action {
  margin-top: auto;
}

.carte--mise-en-avant {
  border-color: var(--couleur-principale);
  box-shadow: var(--ombre-forte);
}
```

Les avantages sont nombreux :

- **spécificité uniforme** : tout se fait avec une classe simple (0,1,0), donc pas de guerre de poids ;
- **indépendance de la structure** : tu peux déplacer `carte__titre` dans le HTML sans casser le style ;
- **lisibilité** : en lisant le HTML, on sait que `carte__titre` appartient à `carte` ;
- **suppression sûre** : si tu retires le bloc, tu retires ses règles, sans effet de bord.

Quelques règles pour bien l'utiliser : pas d'éléments d'éléments (`carte__corps__titre` est un signal qu'il faut découper en deux blocs), un modifier s'utilise **avec** la classe de base (`carte carte--mise-en-avant`), et le nom décrit le **rôle** (`bouton--danger`) plutôt que l'apparence (`bouton--rouge`), qui deviendrait faux le jour où la couleur change.

BEM n'est pas la seule convention (SMACSS, OOCSS en sont d'autres), mais son idée centrale se retrouve partout : des **composants** aux noms explicites, stylés par des **classes plates**.

:::quiz
Dans la convention BEM, que représente la classe `menu__lien--actif` ?
- [ ] Un bloc nommé menu__lien
- [x] Un élément lien du bloc menu, avec le modificateur actif
- [ ] Un modificateur du bloc actif
- [ ] Un identifiant JavaScript
> Le double tiret bas introduit l'élément (lien, partie du bloc menu) et le double tiret introduit le modificateur (actif, un état ou une variante).
:::

## Organiser les fichiers

Une architecture de fichiers courante, inspirée de l'approche **ITCSS** (*Inverted Triangle CSS*), range le code du plus **général** au plus **spécifique**, du moins au plus fort en spécificité :

```text
css/
├── 1-settings/      variables : couleurs, espacements, polices (tokens)
├── 2-base/          reset, box-sizing, styles des éléments (body, h1, a)
├── 3-layout/        structures de page : conteneur, grille, en-tête, pied
├── 4-components/    composants réutilisables : bouton, carte, menu, formulaire
├── 5-utilities/     petites classes à usage unique : .sr-only, .texte-centre
└── main.css         importe tout, dans cet ordre
```

L'ordre n'est pas un hasard : on déclare d'abord les valeurs (tokens), puis on règle les éléments bruts, puis les structures, puis les composants, et enfin les utilitaires qui doivent pouvoir **tout surcharger**. Grâce à l'ordre d'écriture, les couches plus tardives l'emportent à spécificité égale.

Chaque composant tient dans **son propre fichier** (`bouton.css`, `carte.css`), nommé comme sa classe de bloc. Quand tu cherches le style d'une carte, tu sais où aller. Dans un projet avec un outil de build comme Vite (celui de DevRoad), un point d'entrée importe les autres :

```css
/* main.css */
@import "./1-settings/tokens.css";
@import "./2-base/reset.css";
@import "./3-layout/conteneur.css";
@import "./4-components/bouton.css";
@import "./4-components/carte.css";
@import "./5-utilities/utilitaires.css";
```

## Les design tokens

Un **token** est une valeur de design nommée : une décision (« notre bleu principal ») plutôt qu'un chiffre (`#2563eb`). Avec les variables CSS, on les définit une fois et on les utilise partout. Une organisation efficace à deux niveaux :

```css
:root {
  /* Niveau 1 : la palette brute (primitives) */
  --bleu-500: #2563eb;
  --bleu-700: #1d4ed8;
  --gris-50: #f8fafc;
  --gris-200: #e2e8f0;
  --gris-900: #0f172a;

  /* Niveau 2 : les rôles (tokens sémantiques) */
  --couleur-fond: var(--gris-50);
  --couleur-texte: var(--gris-900);
  --couleur-principale: var(--bleu-500);
  --couleur-principale-survol: var(--bleu-700);
  --couleur-bordure: var(--gris-200);

  /* Échelles */
  --espace-1: 0.25rem;
  --espace-2: 0.5rem;
  --espace-3: 1rem;
  --espace-4: 1.5rem;
  --espace-5: 2.5rem;

  --rayon: 0.5rem;
  --ombre-carte: 0 1px 3px rgb(0 0 0 / 0.1);
  --police-base: system-ui, sans-serif;
}
```

Les composants utilisent **seulement les rôles** (`var(--couleur-principale)`), jamais la palette brute. Ainsi, changer l'identité de la marque ou ajouter un thème sombre revient à redéfinir quelques variables de rôle.

### Un thème sombre en quelques lignes

```css
:root[data-theme="sombre"],
:root:not([data-theme]) {
  color-scheme: light;
}

@media (prefers-color-scheme: dark) {
  :root:not([data-theme="clair"]) {
    --couleur-fond: var(--gris-900);
    --couleur-texte: var(--gris-50);
    --couleur-bordure: #334155;
  }
}

:root[data-theme="sombre"] {
  --couleur-fond: var(--gris-900);
  --couleur-texte: var(--gris-50);
  --couleur-bordure: #334155;
}
```

Aucun composant n'a besoin d'être modifié : ils lisent les mêmes variables. Le thème suit la préférence du système, et l'utilisateur peut forcer son choix avec un attribut `data-theme` posé par un petit script. C'est exactement ce que font les design systems modernes.

## Maîtriser la spécificité : @layer et :where()

Quand le projet grandit, on voudrait que les **utilitaires** l'emportent sur les **composants**, et les composants sur la **base**, quelle que soit la spécificité des sélecteurs. Les **cascade layers** répondent à ce besoin :

```css
@layer reset, base, composants, utilitaires;

@layer reset {
  *, *::before, *::after { box-sizing: border-box; margin: 0; }
}

@layer base {
  body { font-family: var(--police-base); color: var(--couleur-texte); }
}

@layer composants {
  .bouton { padding: 0.75rem 1.25rem; background: var(--couleur-principale); }
}

@layer utilitaires {
  .texte-centre { text-align: center; }
}
```

La première ligne déclare **l'ordre de priorité** des couches : une règle d'une couche plus tardive l'emporte sur celle d'une couche plus précoce, **même si elle a une spécificité plus faible**. Les styles hors de toute couche gagnent contre ceux en couche (sauf avec `!important`, qui s'inverse). Tu peux ainsi intégrer une bibliothèque tierce dans une couche basse et la surcharger sans lutter.

L'autre outil est `:where()`, dont la spécificité est toujours **nulle** :

```css
:where(.contenu) a {
  color: var(--couleur-principale);   /* spécificité (0,0,1) : facile à surcharger */
}
```

Utilisé pour les styles par défaut (reset, typographie de contenu), il rend les règles très faciles à écraser.

### Les règles d'or de la spécificité

1. **Reste plat** : une classe par règle, de préférence. Évite les imbrications profondes.
2. **Pas d'identifiants** pour le style.
3. **Pas de `!important`** sauf utilitaires et surcharge de bibliothèque.
4. **Pas de sélecteurs liés à la structure** (`div > div > span`).
5. **Ne style pas un élément selon son contexte** quand une classe de modifier suffit.

## CSS natif imbriqué

Le CSS moderne accepte l'imbrication, qui rapproche les règles liées sans préprocesseur :

```css
.carte {
  border: 1px solid var(--couleur-bordure);

  &:hover {
    box-shadow: var(--ombre-carte);
  }

  & .carte__titre {
    font-size: 1.25rem;
  }

  @media (min-width: 768px) {
    flex-direction: row;
  }
}
```

Le `&` représente le sélecteur parent. Comme en Sass, n'abuse pas : **deux niveaux maximum**, sous peine de recréer les sélecteurs longs que tu voulais fuir.

## Les alternatives modernes : utilitaires, modules, scoping

BEM n'est qu'une approche. Les projets React comme DevRoad en croisent plusieurs :

| Approche | Principe | Avantage | Limite |
| --- | --- | --- | --- |
| **BEM + fichiers** | Classes nommées par convention | Aucun outil, très lisible | Discipline humaine requise |
| **Utilitaires (Tailwind)** | De petites classes à composer dans le HTML | Rapide, cohérent avec des tokens, pas de CSS mort | HTML chargé, apprentissage |
| **CSS Modules** | Classes locales à un fichier, noms générés | Isolation garantie par l'outil | Pas de style partagé simple |
| **CSS-in-JS** | Styles écrits dans le JavaScript | Dynamisme, proximité | Coût d'exécution, complexité |

Tailwind, utilisé par DevRoad, applique en fait les mêmes principes que ce chapitre : un système de tokens (échelle d'espacements, de couleurs), une spécificité plate (une classe par déclaration), et un CSS final qui ne contient que ce qui est utilisé. La différence est de placer les décisions dans les noms de classes plutôt que dans des fichiers `.css`.

```jsx
function CarteRoadmap({ titre, texte }) {
  return (
    <article className="flex flex-col rounded-lg border border-slate-200 p-4 hover:shadow-md">
      <h3 className="text-xl font-semibold">{titre}</h3>
      <p className="mt-2 text-slate-600">{texte}</p>
    </article>
  );
}
```

Quel que soit l'outil, la leçon reste identique : **composants isolés, valeurs issues de tokens, spécificité maîtrisée**. Comprendre l'architecture CSS fait de toi un meilleur utilisateur de Tailwind, car tu sais pourquoi il fonctionne.

> **Astuce** : choisis une approche par projet et tiens-t'y. Un projet qui mélange BEM, utilitaires et styles en ligne sans règle est pire que n'importe laquelle de ces approches utilisée seule.

:::quiz
Quel est l'intérêt principal de `@layer` ?
- [ ] Accélérer le chargement du CSS
- [ ] Remplacer les variables CSS
- [x] Définir explicitement l'ordre de priorité entre groupes de règles, indépendamment de la spécificité
- [ ] Rendre les classes privées à un fichier
> Les couches décident quel groupe l'emporte : une couche tardive gagne sur une couche précoce même si ses sélecteurs sont moins spécifiques.
:::

## Écrire du CSS qu'on peut supprimer

Un CSS sain est un CSS qu'on peut **faire évoluer et nettoyer**. Quelques habitudes :

- **Un fichier par composant**, supprimable d'un bloc ;
- **Repérer le code mort** avec l'onglet Couverture (*Coverage*) de Chrome, qui indique la proportion de CSS inutilisée sur une page ;
- **Linter** : Stylelint détecte les erreurs, les doublons et impose des conventions ;
- **Documenter** les tokens et composants (une simple page HTML de démonstration, ou un outil comme Storybook) ;
- **Éviter les valeurs magiques** : un `margin-top: 37px` sans explication est un problème futur ; utilise un token ou commente-le ;
- **Revoir** régulièrement : à chaque revue de code, une règle qui ne sert plus est supprimée.

## Atelier guidé : refactoriser une page en système

Compte une heure et demie. Prends la page de cartes DevRoad produite aux chapitres précédents, ou un petit projet de ton choix, et organise-la.

1. Compte les problèmes : sélecteurs longs, `!important`, couleurs répétées, valeurs en pixels dispersées. Note-les dans un fichier texte (le « avant »).
2. Crée l'arborescence `1-settings` à `5-utilities` et un `main.css` qui importe tout, avec un `@layer` d'ordre déclaré en première ligne.
3. Extrait tous les codes de couleurs, espacements, rayons, ombres et polices dans `tokens.css`, en deux niveaux (palette puis rôles).
4. Identifie trois blocs : `bouton`, `carte`, `menu`. Pour chacun, crée un fichier et renomme les classes en BEM.
5. Supprime chaque sélecteur imbriqué ou lié à la structure : remplace-les par des classes d'élément et de modifier.
6. Ajoute `bouton--secondaire` et `bouton--danger` sans écrire une seule ligne de spécificité plus forte.
7. Écris trois classes utilitaires (`texte-centre`, `sr-only`, `marge-haut`) placées dans la couche la plus forte.
8. Ajoute le thème sombre en redéfinissant seulement les variables de rôle, plus un bouton qui bascule `data-theme`.
9. Lance l'onglet Couverture de Chrome et supprime les règles inutilisées.
10. Compare avant et après : nombre de lignes, nombre de `!important`, longueur du plus long sélecteur. Passe Stylelint si tu le souhaites.

Auto-évaluation : peux-tu supprimer le fichier `carte.css` sans rien casser d'autre ? Peux-tu changer la couleur principale du site en modifiant une seule ligne ?

## Erreurs fréquentes

- **Écrire des sélecteurs qui dépendent de la structure du HTML.** Le moindre changement de balisage casse tout.
- **Nommer d'après l'apparence** (`.rouge`, `.gros-titre`). Le nom devient faux dès que le design change.
- **Utiliser la palette brute dans les composants** plutôt que les tokens de rôle.
- **Abuser de l'imbrication** (plus de deux ou trois niveaux).
- **Régler une guerre de spécificité par `!important`.** Elle empire.
- **Tout mettre dans un seul fichier**, sans découpage par composant.
- **Mélanger plusieurs conventions** sans règle commune dans un projet.
- **Ne jamais supprimer de CSS.** Le poids et la confusion augmentent à chaque sprint.

## Bonnes pratiques

- Découpe en composants, nomme-les par leur rôle et garde une spécificité plate.
- Définis des tokens à deux niveaux et n'utilise que les rôles dans les composants.
- Déclare l'ordre des couches avec `@layer` et place les utilitaires en dernier.
- Prévois dès le début un thème piloté par variables, même si tu ne livres qu'un thème.
- Préfère les classes aux identifiants et aux balises dans les sélecteurs de composants.
- Mesure le CSS inutilisé et nettoie régulièrement.
- Documente les conventions en début de projet : quelques lignes dans le README suffisent.
- Choisis un outil (BEM, Tailwind, modules) et tiens-toi à ce choix.

## À retenir

- Le CSS est global : une architecture sert à **limiter la portée** et à rendre les effets **prévisibles**.
- **BEM** nomme les blocs, éléments et modificateurs, et garde une spécificité plate.
- L'organisation par couches (paramètres, base, layout, composants, utilitaires) range du plus général au plus spécifique.
- Les **design tokens** à deux niveaux (palette puis rôles) permettent de changer d'identité ou de thème en modifiant quelques variables.
- `@layer` fixe la priorité entre groupes de règles ; `:where()` neutralise la spécificité des styles par défaut.
- Tailwind, CSS Modules et BEM répondent au même besoin : isolation, cohérence, maintenance.
- Un bon CSS est un CSS qu'on peut supprimer sans peur.
