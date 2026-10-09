---
title: Sémantique
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Tu sais écrire une page valide. Reste à écrire une page **qui a du sens**. Deux pages peuvent s'afficher de façon identique à l'écran tout en étant radicalement différentes pour un lecteur d'écran, un moteur de recherche ou un autre développeur. La différence tient à un mot : la **sémantique**.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est la sémantique HTML et pourquoi elle compte ;
- remplacer les `div` génériques par les éléments de structure adaptés ;
- hiérarchiser correctement les titres de `h1` à `h6` ;
- choisir entre `section`, `article`, `aside` et `div` ;
- construire des listes, des tableaux et des citations avec les bons éléments ;
- relire une page et repérer les problèmes de sémantique.

Prérequis : le chapitre « Comprendre HTML » (éléments, attributs, squelette). Prévois deux heures.

## Pourquoi la sémantique compte

Observe ces deux extraits qui produisent un résultat visuel comparable après un peu de CSS :

```html
<div class="header">
  <div class="logo">DevRoad</div>
  <div class="menu">
    <div class="item">Roadmaps</div>
    <div class="item">Fiches</div>
  </div>
</div>
```

```html
<header>
  <p class="logo">DevRoad</p>
  <nav>
    <ul>
      <li><a href="/roadmaps">Roadmaps</a></li>
      <li><a href="/fiches">Fiches</a></li>
    </ul>
  </nav>
</header>
```

Le premier se compose uniquement de boîtes anonymes. Le navigateur ne sait pas qu'il y a un menu, ni que ses entrées sont cliquables. Le second dit explicitement : « ceci est un en-tête, ceci une navigation, ceci une liste de liens ». Les bénéfices sont concrets :

- **Accessibilité** : un lecteur d'écran annonce « navigation » et permet de sauter directement à cette zone. Les liens sont atteignables au clavier sans code supplémentaire.
- **Référencement** : les moteurs de recherche comprennent mieux la hiérarchie de ton contenu.
- **Maintenance** : un collègue (ou toi dans six mois) lit la structure sans deviner le rôle de chaque `div`.
- **Fonctionnalités gratuites** : mode lecture des navigateurs, outils de traduction, aperçu dans les réseaux sociaux.

> **À retenir** : choisis toujours l'élément pour ce qu'il **signifie**, jamais pour son apparence. L'apparence, c'est le travail de CSS.

## Les éléments de structure de page

HTML5 a introduit des éléments qui nomment les grandes zones d'une page. Voici la carte à connaître :

| Élément | Rôle | Remarques |
| --- | --- | --- |
| `header` | En-tête d'une page ou d'une section | Logo, titre, navigation d'introduction |
| `nav` | Bloc de navigation principal | Réserve-le aux grands menus, pas à tout groupe de liens |
| `main` | Contenu principal et unique de la page | **Un seul** par page |
| `section` | Regroupement thématique avec un titre | Doit avoir un titre |
| `article` | Contenu autonome et réutilisable | Billet, fiche, carte de produit |
| `aside` | Contenu complémentaire | Barre latérale, encadré |
| `footer` | Pied de page ou de section | Mentions légales, contacts |

Voici le squelette d'une page DevRoad bien structurée :

```html
<body>
  <header>
    <a href="/" class="logo">DevRoad</a>
    <nav aria-label="Navigation principale">
      <ul>
        <li><a href="/roadmaps">Roadmaps</a></li>
        <li><a href="/fiches">Fiches mémo</a></li>
        <li><a href="/profil">Mon profil</a></li>
      </ul>
    </nav>
  </header>

  <main>
    <h1>Mes roadmaps</h1>

    <section>
      <h2>En cours</h2>
      <article>
        <h3>Devenir développeur Laravel</h3>
        <p>12 chapitres, 60 % terminé.</p>
      </article>
    </section>

    <aside>
      <h2>Astuce du jour</h2>
      <p>Révise un peu chaque jour plutôt que beaucoup une fois par mois.</p>
    </aside>
  </main>

  <footer>
    <p>&copy; 2026 DevRoad. Tous droits réservés.</p>
  </footer>
</body>
```

Remarque l'attribut `aria-label` sur `nav` : quand une page contient plusieurs navigations (principale, pied de page), il leur donne un nom distinct.

### section ou article ou div ?

C'est la confusion la plus fréquente. Pose-toi ces questions dans l'ordre :

1. Le morceau a-t-il un sens **autonome**, qu'on pourrait publier seul (carte, billet, commentaire) ? Alors c'est un `article`.
2. Est-ce un groupe thématique qui possède un **titre** ? Alors c'est une `section`.
3. Est-ce un simple conteneur pour le style ou le script, sans signification ? Alors c'est une `div`.

Une `div` n'est pas interdite ! C'est un outil légitime pour la mise en page. Elle devient un problème quand elle remplace un élément porteur de sens qui existait.

:::quiz
Tu affiches une liste de cartes, chacune représentant une roadmap indépendante avec son titre. Quel élément est le plus adapté pour chaque carte ?
- [ ] div
- [ ] span
- [x] article
- [ ] aside
> Une carte de roadmap est un contenu autonome qui pourrait être réutilisé ailleurs : c'est le cas d'usage de article.
:::

## Les titres : la table des matières de ta page

Les titres vont de `h1` (le plus important) à `h6`. Ils créent le **plan** de la page, que les lecteurs d'écran exploitent pour naviguer : beaucoup d'utilisateurs aveugles parcourent d'abord la liste des titres, comme tu survoles un sommaire.

Les règles à respecter :

- **un seul `h1` par page**, qui décrit le sujet principal ;
- ne **saute pas de niveau** : après un `h2`, le suivant est un `h2` ou un `h3`, jamais un `h4` ;
- les titres décrivent la **hiérarchie**, pas la taille. Un `h3` trop grand par défaut ? Corrige-le en CSS, ne change pas de niveau.

```html
<h1>Apprendre PHP</h1>
  <h2>Les bases</h2>
    <h3>Variables</h3>
    <h3>Fonctions</h3>
  <h2>La programmation objet</h2>
    <h3>Classes</h3>
```

Une astuce pour vérifier : écris seulement les titres de ta page à la suite, comme un plan de dissertation. S'il est cohérent, ta structure l'est aussi.

> **Erreur fréquente** : utiliser `<h4>` parce que « le texte est joli ». C'est la preuve que l'on choisit l'élément pour son apparence. Pense toujours au plan.

## Les groupes de texte et de contenu

### Paragraphes, citations et emphase

```html
<p>Le code est lu bien plus souvent qu'il n'est écrit.</p>

<blockquote cite="https://example.com/source">
  <p>La simplicité est la sophistication suprême.</p>
  <footer>Attribué à Léonard de Vinci</footer>
</blockquote>

<p>Utilise <em>toujours</em> les clés de sécurité, mais
<strong>jamais</strong> ton mot de passe en clair.</p>
```

`em` marque une **emphase** (on insiste dans la voix), `strong` marque une **importance forte**. Ni l'un ni l'autre ne se choisit pour « mettre en italique » ou « en gras » : ça, c'est du CSS. Une citation courte dans une phrase utilise `q`, et une abréviation `abbr` avec `title` pour sa signification.

### Les listes

Trois types de listes existent, chacune avec un sens précis :

```html
<!-- Liste non ordonnée : l'ordre n'a pas d'importance -->
<ul>
  <li>HTML</li>
  <li>CSS</li>
  <li>JavaScript</li>
</ul>

<!-- Liste ordonnée : l'ordre est significatif -->
<ol>
  <li>Installer l'éditeur</li>
  <li>Créer le fichier index.html</li>
  <li>Ouvrir la page dans le navigateur</li>
</ol>

<!-- Liste de description : termes et définitions -->
<dl>
  <dt>DOM</dt>
  <dd>Représentation du document sous forme d'arbre.</dd>
  <dt>Balise</dt>
  <dd>Étiquette qui délimite un élément.</dd>
</dl>
```

Un menu de navigation est une **liste de liens** : c'est pourquoi on trouve `ul` à l'intérieur de `nav`. Les seuls enfants directs de `ul` et `ol` sont des `li`.

### Les tableaux

Un tableau sert à présenter des **données** à deux dimensions, jamais à faire la mise en page (c'était la pratique des années 2000, aujourd'hui abandonnée).

```html
<table>
  <caption>Avancement par roadmap</caption>
  <thead>
    <tr>
      <th scope="col">Roadmap</th>
      <th scope="col">Chapitres</th>
      <th scope="col">Progression</th>
    </tr>
  </thead>
  <tbody>
    <tr>
      <th scope="row">Laravel</th>
      <td>12</td>
      <td>60 %</td>
    </tr>
    <tr>
      <th scope="row">React</th>
      <td>9</td>
      <td>25 %</td>
    </tr>
  </tbody>
</table>
```

Les éléments clés : `caption` (titre du tableau), `thead`, `tbody` et `tfoot` (groupes de lignes), `th` (cellule d'en-tête, avec `scope="col"` ou `scope="row"`) et `td` (cellule de données). Sans `th` ni `scope`, un lecteur d'écran ne peut pas annoncer « Progression, 60 % » en lisant une cellule.

:::quiz
Dans quel cas utilise-t-on un tableau HTML ?
- [ ] Pour placer le menu à gauche et le contenu à droite
- [ ] Pour centrer une image sur la page
- [x] Pour présenter des données organisées en lignes et en colonnes
- [ ] Pour aligner des boutons
> Un tableau décrit des données tabulaires. La mise en page se fait avec CSS (Flexbox, Grid), jamais avec des tableaux.
:::

## Figure, time et autres éléments utiles

Quelques éléments souvent oubliés rendent ton HTML beaucoup plus expressif :

```html
<figure>
  <img src="schema-dom.png" alt="Arbre du DOM avec html, head et body">
  <figcaption>Figure 1 : le DOM d'une page simple.</figcaption>
</figure>

<p>Publié le <time datetime="2026-10-09">9 octobre 2026</time>.</p>

<p>Tape <kbd>Ctrl</kbd> + <kbd>S</kbd> pour enregistrer.</p>

<p>La commande <code>php artisan serve</code> lance le serveur.</p>

<details>
  <summary>Pourquoi mon CSS ne s'applique pas ?</summary>
  <p>Vérifie le chemin du fichier dans la balise link.</p>
</details>
```

- `figure` et `figcaption` associent un média à sa légende.
- `time` avec l'attribut `datetime` rend une date lisible par les machines.
- `kbd` et `code` marquent une touche et un extrait de code.
- `details` et `summary` créent un bloc dépliable **sans JavaScript**, accessible au clavier.
- `mark` surligne, `small` marque une mention secondaire, `sub` et `sup` gèrent indices et exposants.

## Relire sa page : le test du plan

Une méthode simple pour évaluer la sémantique d'une page :

1. **Retire mentalement le CSS** : la page a-t-elle encore un sens ? Les titres sont-ils dans l'ordre ?
2. **Compte les `div`** : pour chacune, demande-toi si un élément plus précis existe.
3. **Navigue au clavier** (touche Tab) : tout ce qui est cliquable est-il atteignable ?
4. **Liste les titres** : forment-ils un plan cohérent ?
5. **Passe le validateur** du W3C.

Les extensions de navigateur comme HeadingsMap ou l'onglet Accessibilité des outils de développement t'aident à visualiser ce plan.

:::quiz
Laquelle de ces structures de titres est correcte ?
- [ ] h1, h3, h2, h4
- [ ] h2, h2, h1, h1
- [x] h1, h2, h3, h2
- [ ] h1, h1, h1, h1
> Un seul h1, puis une descente progressive sans saut de niveau. On peut remonter d'un niveau (de h3 à h2) quand une nouvelle section commence.
:::

## Atelier guidé : restructurer une page « tout en div »

Compte une heure. Tu vas réparer une page mal construite.

1. Crée `fiche.html` avec le squelette complet (doctype, `lang`, charset, viewport, titre).
2. Copie ce corps volontairement pauvre dans le `body` :

```html
<div class="top"><div class="name">DevRoad</div>
<div class="links"><a href="/">Accueil</a> <a href="/fiches">Fiches</a></div></div>
<div class="content">
<div class="big">Fiche mémo : les listes HTML</div>
<div class="text">Les listes servent à regrouper des éléments liés.</div>
<div class="side">Astuce : relis toujours ton plan.</div>
</div>
<div class="bottom">DevRoad 2026</div>
```

3. Remplace `div.top` par un `header` et `div.links` par une `nav` contenant une liste `ul` de liens.
4. Entoure le contenu principal d'un `main` et transforme `div.big` en `h1`.
5. Transforme `div.text` en `p` et `div.side` en `aside` avec son propre titre `h2`.
6. Ajoute une `section` « Les trois types de listes » avec un `h2`, puis un exemple de `ul`, de `ol` et de `dl`.
7. Ajoute un tableau comparatif avec `caption`, `thead`, `th scope="col"` et deux lignes de données.
8. Remplace `div.bottom` par un `footer` avec le symbole copyright écrit `&copy;`.
9. Ajoute une date avec `time` et un bloc `details` contenant une question fréquente.
10. Teste : vérifie le plan des titres, navigue au clavier avec Tab, valide la page.

Auto-évaluation : peux-tu justifier chaque élément choisi en une phrase ? Sais-tu expliquer pourquoi `main` n'apparaît qu'une fois ? Si une `div` reste dans ta page, sais-tu dire pourquoi c'est légitime ?

## Erreurs fréquentes

- **Tout faire en `div`.** C'est la « divite » : la page s'affiche mais ne signifie rien.
- **Choisir un titre pour sa taille.** Les niveaux traduisent la hiérarchie, pas l'apparence.
- **Plusieurs `h1` sans raison.** Cela brouille le sujet principal.
- **Mettre `nav` autour de chaque groupe de liens.** Réserve-le aux grands blocs de navigation.
- **Utiliser `b` et `i` à la place de `strong` et `em`.** Ils n'ont pas la même signification.
- **Utiliser `section` sans titre.** Si aucun titre ne convient, c'est sans doute une `div`.
- **Faire une mise en page avec un tableau.** Le tableau est réservé aux données.

## Bonnes pratiques

- Dessine d'abord le plan de ta page (en-tête, navigation, contenu, pied) puis traduis-le en éléments.
- N'utilise `div` et `span` qu'en dernier recours, quand aucun élément porteur de sens ne convient.
- Place un `main` unique et un `h1` unique sur chaque page.
- Donne un `aria-label` aux navigations multiples.
- Utilise `th` avec `scope` et un `caption` pour tous tes tableaux.
- Regarde tes pages sans CSS pour vérifier leur lisibilité brute.
- Laisse CSS gérer l'apparence : si un élément te plaît par son look, c'est un mauvais critère.

## À retenir

- La **sémantique** consiste à choisir chaque élément pour son sens, au bénéfice de l'accessibilité, du référencement et de la maintenance.
- Les éléments de structure sont `header`, `nav`, `main`, `section`, `article`, `aside` et `footer`.
- Un `article` est autonome, une `section` est thématique avec un titre, une `div` est un conteneur neutre.
- Les titres forment le plan : un seul `h1`, aucun saut de niveau.
- Les listes (`ul`, `ol`, `dl`) et les tableaux (`caption`, `th`, `td`) ont chacun un usage précis.
- `figure`, `time`, `details` et `code` ajoutent du sens sans effort.
- Si tu retires le CSS et que la page reste compréhensible, la sémantique est bonne.
