---
title: Comprendre la cascade
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

CSS (*Cascading Style Sheets*) donne à tes pages leur apparence. Sa syntaxe se lit en cinq minutes, mais beaucoup de débutants passent des heures à se demander pourquoi « ma règle ne marche pas ». Presque toujours, la réponse tient dans un seul mot : la **cascade**. Ce chapitre t'apprend la mécanique qui décide quelle règle gagne quand plusieurs s'appliquent au même élément, ainsi que le modèle de boîte qui gouverne les dimensions de tout ce que tu affiches.

À la fin du chapitre, tu seras capable de :

- écrire une règle CSS et la relier à une page HTML ;
- cibler des éléments avec les sélecteurs essentiels ;
- calculer la **spécificité** d'un sélecteur et prédire quelle règle l'emporte ;
- expliquer l'**héritage** et utiliser les valeurs `inherit`, `initial` ;
- maîtriser le **modèle de boîte** (contenu, padding, bordure, marge) et `box-sizing` ;
- utiliser les variables CSS et les unités relatives ;
- déboguer un style avec les outils de développement.

Prérequis : savoir écrire une page HTML valide (cours HTML, chapitres 1 et 2). Prévois deux heures. Un simple fichier `index.html` et un fichier `style.css` suffisent.

## Anatomie d'une règle CSS

Une feuille de style est une suite de **règles**. Chaque règle comporte un **sélecteur** (qui cibler) et un **bloc de déclarations** (quoi appliquer) :

```css
h1 {
  color: #0f172a;
  font-size: 2rem;
}
```

- `h1` est le sélecteur ;
- `color` et `font-size` sont des **propriétés** ;
- `#0f172a` et `2rem` sont leurs **valeurs** ;
- chaque déclaration se termine par un point-virgule.

### Relier le CSS au HTML

Trois façons existent, mais une seule est recommandée pour un vrai projet :

```html
<!-- 1. Fichier externe (recommandé) : dans le head -->
<link rel="stylesheet" href="css/style.css">

<!-- 2. Balise style : utile pour un test rapide -->
<style>
  p { line-height: 1.6; }
</style>

<!-- 3. Attribut style : à éviter, difficile à maintenir et très prioritaire -->
<p style="color: red;">Texte rouge</p>
```

Le fichier externe est mis en cache par le navigateur, partagé entre toutes les pages et facile à maintenir. L'attribut `style` mélange contenu et apparence, et sa priorité élevée rend le débogage pénible.

## Les sélecteurs essentiels

Pour appliquer un style, il faut d'abord **choisir** les éléments. Voici l'essentiel :

| Sélecteur | Exemple | Cible |
| --- | --- | --- |
| Élément | `p` | Tous les paragraphes |
| Classe | `.carte` | Tout élément avec `class="carte"` |
| Identifiant | `#entete` | L'élément avec `id="entete"` |
| Groupe | `h1, h2, h3` | Plusieurs sélecteurs à la fois |
| Descendant | `nav a` | Les liens à l'intérieur d'une `nav` |
| Enfant direct | `ul > li` | Les `li` enfants directs d'un `ul` |
| Voisin direct | `h2 + p` | Le paragraphe immédiatement après un `h2` |
| Attribut | `input[type="email"]` | Les champs de type e-mail |
| Pseudo-classe | `a:hover` | Un lien survolé |
| Pseudo-élément | `p::first-line` | La première ligne d'un paragraphe |

```css
.carte {
  border: 1px solid #e2e8f0;
  border-radius: 0.5rem;
}

.carte > h2 {
  font-size: 1.25rem;
}

.carte.mise-en-avant {
  border-color: #2563eb;
}

a:hover,
a:focus-visible {
  text-decoration: underline;
}
```

Le sélecteur `.carte.mise-en-avant` (sans espace) cible un élément qui possède **les deux** classes. Avec un espace, `.carte .mise-en-avant`, il cible un descendant. Une espace de plus ou de moins change tout.

Pour la liste des pseudo-classes utiles (`:first-child`, `:nth-child(2n)`, `:not()`, `:is()`, `:has()`), garde un onglet MDN ouvert : tu les découvriras au fil des projets.

> **Astuce** : en CSS, préfère les **classes**. Elles sont réutilisables, d'une spécificité modérée et décrivent un rôle (`.bouton-principal`) plutôt qu'un emplacement.

## La cascade : qui gagne ?

Imagine ce code :

```html
<p class="intro" id="premier">Bonjour</p>
```

```css
p { color: black; }
.intro { color: blue; }
#premier { color: red; }
p { color: green; }
```

De quelle couleur s'affiche le texte ? **Rouge**. Quatre règles visent le même paragraphe, et le navigateur doit trancher. Il suit trois critères, dans cet ordre :

1. **L'origine et l'importance** : les styles du navigateur, ceux de l'utilisateur, ceux de l'auteur (toi). Une déclaration marquée `!important` passe devant.
2. **La spécificité** : plus un sélecteur est précis, plus il pèse.
3. **L'ordre d'apparition** : à spécificité égale, **la dernière règle écrite gagne**.

### Calculer la spécificité

On la représente par trois chiffres (A, B, C) :

| Composant | Pèse | Exemples |
| --- | --- | --- |
| A : identifiants | le plus lourd | `#premier` donne (1,0,0) |
| B : classes, attributs, pseudo-classes | moyen | `.intro`, `[type]`, `:hover` donnent (0,1,0) |
| C : éléments, pseudo-éléments | le plus léger | `p`, `::before` donnent (0,0,1) |

On compare colonne par colonne, de gauche à droite, **sans jamais convertir** en un seul nombre. Dix classes ne battent pas un identifiant : (0,10,0) reste inférieur à (1,0,0).

```css
p                  /* (0,0,1) */
.intro             /* (0,1,0) */
p.intro            /* (0,1,1) */
nav ul li a        /* (0,0,4) */
nav .menu a:hover  /* (0,2,2) */
#premier           /* (1,0,0) */
```

Les styles en ligne (`style="…"`) passent devant tous les sélecteurs. Le sélecteur universel, les combinateurs (`>`, `+`, espace) et `:where()` pèsent zéro ; `:is()` et `:not()` prennent la spécificité de leur argument le plus fort.

Dans l'exemple du début : `p` vaut (0,0,1), `.intro` (0,1,0), `#premier` (1,0,0). Le dernier gagne, donc rouge. La deuxième règle `p { color: green; }` apparaît plus tard mais pèse moins : l'ordre ne compte qu'à égalité.

:::quiz
Un élément `p` possède la classe `note`. Quelle règle l'emporte ?
`.note { color: blue; }` contre `p { color: red; }`, la règle `p` étant écrite en dernier.
- [ ] La règle p, car elle est écrite en dernier
- [x] La règle .note, car sa spécificité est plus élevée
- [ ] Aucune des deux
- [ ] La règle p, car les éléments pèsent plus que les classes
> Une classe (0,1,0) l'emporte sur un élément (0,0,1). L'ordre ne départage que des spécificités identiques.
:::

### Le piège du !important

`!important` force une déclaration à passer devant les autres :

```css
.bouton { background: blue !important; }
```

C'est tentant quand un style ne s'applique pas, mais c'est une dette technique : pour l'écraser, il faut un autre `!important`, et on s'enfonce. Réserve-le aux cas exceptionnels (classes utilitaires, surcharge d'une bibliothèque tierce). Le vrai remède est de comprendre la spécificité et de garder des sélecteurs simples.

> **Erreur fréquente** : multiplier les sélecteurs imbriqués (`div.page section.bloc ul li a.lien`) pour « gagner ». Tu obtiens une spécificité énorme, impossible à surcharger proprement plus tard. Une seule classe bien nommée suffit.

## L'héritage

Certaines propriétés **se transmettent** des parents aux enfants. Si tu règles la couleur du texte sur `body`, tous les éléments contenus l'héritent, sans que tu aies à répéter :

```css
body {
  font-family: system-ui, sans-serif;
  color: #1e293b;
  line-height: 1.6;
}
```

Sont généralement héritées les propriétés liées au **texte** : `color`, `font-family`, `font-size`, `line-height`, `text-align`. Ne le sont pas celles liées à la **boîte** : `margin`, `padding`, `border`, `background`, `width`. Cela serait absurde que chaque enfant ait la bordure de son parent.

Quatre mots-clés pilotent ce comportement :

- `inherit` : force l'héritage de la valeur du parent ;
- `initial` : remet la valeur par défaut de la spécification ;
- `unset` : se comporte comme `inherit` si la propriété est héritée, sinon comme `initial` ;
- `revert` : retourne au style par défaut du navigateur.

```css
a {
  color: inherit;        /* les liens prennent la couleur du texte environnant */
  text-decoration: none;
}
```

## Le modèle de boîte

En CSS, **tout élément est une boîte rectangulaire**, composée de quatre couches, de l'intérieur vers l'extérieur :

1. le **contenu** (`width` et `height`) ;
2. le **padding** : espace intérieur entre contenu et bordure ;
3. la **bordure** (`border`) ;
4. la **marge** (`margin`) : espace extérieur qui sépare de la boîte voisine.

```css
.carte {
  width: 300px;
  padding: 20px;
  border: 2px solid #cbd5e1;
  margin: 16px;
}
```

Combien de place cette carte prend-elle en largeur ? Par défaut, `width` ne concerne que le **contenu** : 300 + 2 × 20 de padding + 2 × 2 de bordure = **344 px**, plus les marges autour. C'est contre-intuitif, et la source de nombreux débordements. La solution, que presque tout projet moderne applique :

```css
*,
*::before,
*::after {
  box-sizing: border-box;
}
```

Avec `box-sizing: border-box`, `width` inclut désormais le padding et la bordure : la carte ci-dessus occupe exactement 300 px. Plus besoin de faire des soustractions. Place ce bloc au tout début de chaque feuille de style.

### Les raccourcis padding et margin

```css
.boite {
  padding: 10px;                /* les quatre côtés */
  padding: 10px 20px;           /* haut/bas 10, gauche/droite 20 */
  padding: 10px 20px 30px;      /* haut 10, gauche/droite 20, bas 30 */
  padding: 10px 20px 30px 40px; /* haut, droite, bas, gauche : sens horaire */
}
```

Les marges verticales entre deux blocs voisins **fusionnent** : un bloc avec `margin-bottom: 30px` suivi d'un bloc avec `margin-top: 20px` laissent 30 px entre eux, pas 50. Pour centrer horizontalement un bloc de largeur fixée, on utilise `margin-inline: auto` (ou `margin: 0 auto`).

Mieux encore, plutôt que de gérer des marges haut et bas partout, utilise une seule direction : par exemple, n'espace que par `margin-bottom`, ou délègue l'espacement à un conteneur avec `gap` (chapitres suivants).

:::quiz
Avec `box-sizing: border-box`, un élément de `width: 300px` avec 20px de padding et 2px de bordure de chaque côté occupe en largeur :
- [ ] 344 px
- [ ] 322 px
- [x] 300 px
- [ ] 260 px
> En border-box, la largeur déclarée inclut padding et bordure : l'élément fait exactement 300 px, le contenu rétrécit pour compenser.
:::

## Variables CSS et unités

### Les custom properties

Les **variables CSS** évitent de répéter des valeurs et permettent de changer un thème en un point :

```css
:root {
  --couleur-principale: #2563eb;
  --couleur-texte: #1e293b;
  --rayon: 0.5rem;
  --espace: 1rem;
}

.bouton {
  background: var(--couleur-principale);
  border-radius: var(--rayon);
  padding: var(--espace) calc(var(--espace) * 2);
}
```

`:root` est l'élément `html` avec une spécificité plus haute ; les variables y sont accessibles partout. Elles se **redéfinissent localement** et sont héritées : en ajoutant `.sombre { --couleur-texte: #f8fafc; }`, tout ce qui se trouve dans un conteneur `.sombre` change.

### Les unités à connaître

| Unité | Nature | Usage |
| --- | --- | --- |
| `px` | Absolue | Bordures fines, ombres |
| `rem` | Relative à la taille de police de `html` | Tailles de texte, espacements |
| `em` | Relative à la police de l'élément | Espacements qui suivent le texte |
| `%` | Relative au parent | Largeurs fluides |
| `vw`, `vh` | Pourcentage de la fenêtre | Sections plein écran |
| `ch` | Largeur du chiffre « 0 » | Longueur de ligne de texte |

Pour l'accessibilité, exprime tes tailles de texte en `rem`, afin qu'elles respectent le réglage de taille de police de l'utilisateur. Limite la largeur d'un bloc de texte à environ 65 caractères pour la lisibilité : `max-width: 65ch`.

## Déboguer avec les outils de développement

Quand « ça ne marche pas », ne devine pas : inspecte. Dans le navigateur, clique droit sur l'élément puis **Inspecter** :

- le panneau **Styles** liste toutes les règles qui s'appliquent, de la plus forte à la plus faible ; celles qui perdent sont **barrées** ;
- une icône d'avertissement signale une propriété invalide ou ignorée ;
- l'onglet **Calculé** (*Computed*) affiche les valeurs finales ;
- le schéma de la **boîte** montre contenu, padding, bordure et marge en couleur ;
- tu peux cocher/décocher une déclaration, changer une valeur et voir le résultat en direct.

Une checklist quand un style ne s'applique pas : le fichier CSS est-il chargé (onglet Réseau) ? Le sélecteur cible-t-il vraiment l'élément ? Une règle plus spécifique ou plus tardive le barre-t-elle ? La propriété est-elle valide pour cet élément ?

## Atelier guidé : habiller la carte de roadmap

Compte une heure et demie.

1. Crée `index.html` avec une `main` contenant trois `article` de classe `carte` (titre `h2`, un paragraphe, un lien). Crée `css/style.css` et relie-le.
2. Commence la feuille par la remise à zéro `box-sizing: border-box`, puis définis des variables dans `:root` (couleurs, rayon, espace).
3. Règle `body` : police système, couleur, `line-height: 1.6`, marges nulles.
4. Style `.carte` : bordure, rayon, padding, marge basse. Vérifie la boîte dans l'inspecteur.
5. Ajoute `.carte:hover` avec une ombre ou un changement de bordure.
6. Style `.carte a` : couleur héritée, soulignement au survol.
7. Provoque un conflit : ajoute `p { color: red; }` après `.carte p { color: blue; }`. Prédis le résultat avant de recharger, puis explique-le avec la spécificité.
8. Ajoute volontairement `#carte-1 { color: green; }` sur une carte, observe, puis supprime-le.
9. Utilise l'inspecteur pour trouver quelle règle est barrée et pourquoi.
10. Remplace chaque valeur répétée par une variable, et change la couleur principale en un seul endroit pour voir tout le site s'adapter.

Auto-évaluation : peux-tu calculer à la main la spécificité de `nav .menu a:hover` ? Peux-tu expliquer pourquoi `border-box` est utile et pourquoi les marges verticales fusionnent ?

## Erreurs fréquentes

- **Oublier le point-virgule.** La déclaration suivante est ignorée sans message d'erreur.
- **Confondre `.classe` et `#id`.** Le point cible une classe, le dièse un identifiant.
- **Un espace de trop dans un sélecteur composé.** `.a .b` et `.a.b` ne ciblent pas la même chose.
- **Abuser de `!important`.** Il masque le vrai problème et s'accumule.
- **Oublier `box-sizing: border-box`.** Les boîtes débordent de leur conteneur.
- **Penser que l'ordre suffit.** Une règle moins spécifique écrite plus tard ne gagne pas.
- **Utiliser des identifiants pour le style.** Ils pèsent trop et ne sont pas réutilisables.
- **Écrire des sélecteurs trop longs.** Ils rendent la feuille fragile.

## Bonnes pratiques

- Charge une feuille externe unique, chargée dans le `head`.
- Commence par une remise à zéro minimale et `box-sizing: border-box`.
- Stylise surtout avec des **classes** simples et bien nommées.
- Garde une spécificité basse et homogène : elle te laisse libre de surcharger.
- Déclare couleurs, espacements et rayons sous forme de variables.
- Utilise `rem` pour les tailles de texte et `ch` pour la longueur des lignes.
- Exploite l'héritage en réglant la typographie sur `body`.
- Inspecte avant de modifier au hasard.

## À retenir

- Une règle CSS = sélecteur + déclarations (propriété et valeur).
- La **cascade** tranche selon l'importance, la **spécificité** puis l'ordre d'écriture.
- La spécificité se lit (identifiants, classes, éléments) colonne par colonne.
- Les propriétés de texte sont héritées ; celles de boîte ne le sont pas.
- Chaque élément est une boîte : contenu, padding, bordure, marge. `box-sizing: border-box` rend les calculs prévisibles.
- Les variables CSS centralisent les valeurs ; `rem` et `ch` respectent l'utilisateur.
- L'inspecteur du navigateur est l'outil n° 1 pour comprendre un conflit de styles.
