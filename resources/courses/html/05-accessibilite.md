---
title: Accessibilité
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Environ une personne sur six dans le monde vit avec un handicap. Mais l'accessibilité ne concerne pas qu'elles : un utilisateur qui se casse le bras et n'a plus de souris, quelqu'un qui consulte son téléphone en plein soleil, une personne âgée dont la vue baisse, un visiteur sur une connexion lente, tous bénéficient d'un site bien conçu. L'accessibilité est aussi une qualité professionnelle : un site accessible est mieux structuré, mieux référencé, et dans de nombreux pays une obligation légale.

À la fin du chapitre, tu seras capable de :

- expliquer les quatre principes WCAG (perceptible, utilisable, compréhensible, robuste) ;
- rendre un site entièrement utilisable au clavier ;
- gérer correctement le focus visible et l'ordre de tabulation ;
- utiliser les attributs ARIA avec discernement ;
- vérifier les contrastes et ne jamais dépendre de la couleur seule ;
- tester avec un lecteur d'écran et des outils automatiques ;
- construire un lien d'évitement et des composants simples accessibles.

Prérequis : les chapitres 1 à 4. Prévois deux heures et demie.

## Les quatre principes des WCAG

Les **WCAG** (*Web Content Accessibility Guidelines*) sont le standard international publié par le W3C. Elles s'organisent autour de quatre principes, souvent résumés par l'acronyme anglais POUR :

| Principe | Question | Exemple |
| --- | --- | --- |
| **Perceptible** | L'information peut-elle être perçue ? | Texte alternatif, sous-titres, contraste suffisant |
| **Utilisable** | Peut-on s'en servir ? | Navigation au clavier, temps suffisant, pas de flashs |
| **Compréhensible** | Est-ce clair ? | Langue déclarée, erreurs expliquées, comportement prévisible |
| **Robuste** | Est-ce lisible par différents outils ? | HTML valide, éléments natifs, ARIA correct |

Trois niveaux de conformité existent : A (minimum), **AA** (le niveau visé par la plupart des lois et des clients) et AAA (exigeant). Ton objectif réaliste : le niveau AA.

> **À retenir** : la première règle d'accessibilité est déjà dans les chapitres précédents. Du HTML sémantique, des labels, des alternatives textuelles : plus de la moitié du travail est faite avant même de parler d'ARIA.

## Les éléments natifs d'abord

Un élément natif apporte gratuitement trois choses : un **rôle** (le lecteur d'écran sait ce que c'est), un **comportement clavier** et un **état**. Compare :

```html
<!-- Mauvais : une div qui imite un bouton -->
<div class="btn" onclick="envoyer()">Envoyer</div>

<!-- Bon : un vrai bouton -->
<button type="button" onclick="envoyer()">Envoyer</button>
```

La `div` n'est pas atteignable au clavier, n'est pas annoncée comme un bouton, ne réagit ni à Entrée ni à Espace. Pour la rendre équivalente, il faudrait lui ajouter `role="button"`, `tabindex="0"` et du JavaScript pour deux touches. Le `button` natif fait tout cela sans effort. D'où la **première règle d'ARIA** : si un élément HTML natif existe pour ton besoin, utilise-le.

La distinction clé entre `a` et `button` :

- un **lien** (`a href`) te **emmène** quelque part (une URL) ;
- un **bouton** (`button`) **déclenche une action** sur la page (ouvrir un menu, envoyer, supprimer).

Un `a` sans `href` ou avec `href="#"` utilisé comme bouton est une erreur courante.

## Le clavier : un test que tu peux faire maintenant

Beaucoup d'utilisateurs naviguent sans souris : personnes à mobilité réduite, utilisateurs de lecteurs d'écran, ou simplement des développeurs pressés. Voici le test essentiel, à répéter sur chaque page :

1. Pose ta souris loin de toi.
2. Appuie sur **Tab** pour avancer, **Maj+Tab** pour reculer, **Entrée** pour activer un lien, **Espace** pour un bouton ou une case.
3. Vérifie que tu peux atteindre et activer **tout** ce qui est interactif, dans un ordre logique.
4. Vérifie que tu **vois toujours** où se trouve le focus.

### Le focus visible

Le focus est l'indicateur de position au clavier, généralement un contour autour de l'élément. L'erreur la plus répandue de tous les débutants est celle-ci :

```css
/* NE JAMAIS FAIRE */
button:focus {
  outline: none;
}
```

Cela rend le site inutilisable pour un utilisateur clavier. Mieux : personnaliser le contour avec `:focus-visible`, qui n'apparaît que lorsque le focus provient du clavier.

```css
:focus-visible {
  outline: 3px solid #2563eb;
  outline-offset: 2px;
}
```

### L'ordre de tabulation et tabindex

L'ordre suit l'**ordre du code source**. Si ton code HTML est dans un ordre logique, la navigation l'est aussi. Pour l'attribut `tabindex` :

- `tabindex="0"` rend un élément non interactif atteignable (à éviter sur des éléments qui devraient être natifs) ;
- `tabindex="-1"` retire l'élément de l'ordre de tabulation mais permet de lui donner le focus en JavaScript (utile pour déplacer le focus vers un titre après navigation) ;
- une **valeur positive** (`tabindex="3"`) est à proscrire : elle crée un ordre artificiel impossible à maintenir.

### Le lien d'évitement

Un utilisateur clavier devrait-il appuyer 20 fois sur Tab pour passer le menu à chaque page ? Non : on ajoute un **lien d'évitement**, premier élément focusable de la page :

```html
<body>
  <a class="skip-link" href="#contenu">Aller au contenu principal</a>
  <header>…</header>
  <main id="contenu" tabindex="-1">…</main>
</body>
```

```css
.skip-link {
  position: absolute;
  left: 0.5rem;
  top: -4rem;
  background: #0f172a;
  color: #fff;
  padding: 0.75rem 1rem;
}
.skip-link:focus {
  top: 0.5rem;
}
```

Invisible tant qu'il n'a pas le focus, il apparaît dès le premier appui sur Tab.

:::quiz
Quelle est la meilleure façon de créer un bouton qui ouvre un menu ?
- [ ] Une div avec un gestionnaire de clic
- [ ] Un lien a avec href égal à dièse
- [x] Un élément button
- [ ] Un span avec role button seulement
> L'élément natif button apporte rôle, état et gestion du clavier sans code supplémentaire. Les alternatives demandent d'ajouter manuellement ce que le navigateur offre déjà.
:::

## ARIA : à utiliser avec discernement

**ARIA** (*Accessible Rich Internet Applications*) est un ensemble d'attributs qui enrichissent la sémantique pour les technologies d'assistance. Il **ne change ni l'apparence ni le comportement**, seulement ce qui est annoncé. Un dicton célèbre résume la discipline : « pas d'ARIA vaut mieux que du mauvais ARIA ».

Les trois familles d'attributs :

- les **rôles** (`role="dialog"`, `role="alert"`, `role="tablist"`) disent ce qu'est un élément ;
- les **propriétés** (`aria-label`, `aria-labelledby`, `aria-describedby`) donnent un nom ou une description ;
- les **états** (`aria-expanded`, `aria-pressed`, `aria-current`, `aria-hidden`) expriment une situation changeante.

Voici les usages les plus fréquents et les plus utiles :

```html
<!-- Nommer un bouton qui ne contient qu'une icône -->
<button type="button" aria-label="Fermer la fenêtre">
  <svg aria-hidden="true" focusable="false" width="16" height="16">…</svg>
</button>

<!-- Indiquer la page courante dans un menu -->
<nav aria-label="Navigation principale">
  <ul>
    <li><a href="/" aria-current="page">Accueil</a></li>
    <li><a href="/roadmaps">Roadmaps</a></li>
  </ul>
</nav>

<!-- Un bouton qui déplie un panneau -->
<button type="button" aria-expanded="false" aria-controls="menu-mobile">
  Menu
</button>
<ul id="menu-mobile" hidden>…</ul>
```

`aria-hidden="true"` cache un élément **aux technologies d'assistance** (icône décorative), sans le masquer visuellement. `aria-expanded` doit être mis à jour par JavaScript chaque fois que l'état change. Ne mets jamais `aria-hidden="true"` sur un élément focusable : le focus tomberait sur un élément « invisible ».

### Les régions dynamiques

Quand du contenu change sans rechargement (message d'erreur, notification de succès), le lecteur d'écran ne le sait pas. Une **région live** l'annonce :

```html
<div role="status" aria-live="polite" id="notification"></div>
<div role="alert" id="erreur"></div>
```

`role="status"` (`aria-live="polite"`) annonce quand l'utilisateur est disponible ; `role="alert"` interrompt immédiatement, à réserver aux vraies erreurs. L'élément doit **exister dans la page avant** que tu y insères le texte.

## Couleurs et contrastes

Ne comptabilise pas l'accessibilité comme un simple rapport de conformité : environ 8 % des hommes ont une forme de daltonisme, et un écran sous le soleil de midi à Abidjan réduit drastiquement le contraste perçu. Les règles AA :

| Élément | Ratio de contraste minimum |
| --- | --- |
| Texte normal | 4,5 : 1 |
| Grand texte (24 px, ou 19 px en gras) | 3 : 1 |
| Composants d'interface (bordures de champ, icônes utiles) | 3 : 1 |

Quelques conséquences pratiques :

- le gris clair sur fond blanc, très tendance, échoue souvent ; teste chaque combinaison avec un vérificateur de contraste (WebAIM Contrast Checker, ou l'inspecteur de Chrome qui affiche le ratio) ;
- **ne transmets jamais une information par la couleur seule** : une erreur rouge doit aussi contenir une icône ou un texte (« Erreur : »), un lien dans un paragraphe doit être souligné ;
- respecte les préférences de l'utilisateur : agrandissement du texte jusqu'à 200 % sans casse, et réduction des animations.

```css
@media (prefers-reduced-motion: reduce) {
  * {
    animation-duration: 0.01ms !important;
    transition-duration: 0.01ms !important;
  }
}
```

Utilise des unités relatives (`rem`, `em`) pour les tailles de texte, afin que le réglage de taille de police du navigateur soit respecté.

## Les alternatives textuelles et le contenu non textuel

Tu connais déjà `alt`. Le principe s'étend :

- **vidéo** : sous-titres et, si besoin, transcription ;
- **audio** : transcription complète ;
- **graphique complexe** : description textuelle ou tableau de données équivalent ;
- **icônes seules** : un nom accessible (`aria-label` ou texte masqué visuellement) ;
- **texte caché pour les lecteurs d'écran** : la classe utilitaire classique.

```css
.sr-only {
  position: absolute;
  width: 1px;
  height: 1px;
  padding: 0;
  margin: -1px;
  overflow: hidden;
  clip: rect(0, 0, 0, 0);
  white-space: nowrap;
  border: 0;
}
```

```html
<a href="/panier">
  <svg aria-hidden="true" width="20" height="20">…</svg>
  <span class="sr-only">Panier, 3 articles</span>
</a>
```

N'utilise pas `display: none` pour ce besoin : l'élément disparaîtrait aussi pour le lecteur d'écran.

:::quiz
Parmi ces pratiques, laquelle rend un site inutilisable pour les personnes naviguant au clavier ?
- [ ] Ajouter un lien d'évitement
- [ ] Utiliser :focus-visible
- [x] Supprimer le contour de focus sans le remplacer
- [ ] Utiliser des éléments button natifs
> Sans indication de focus, un utilisateur clavier ne sait plus où il se trouve. Il faut toujours fournir un contour visible, personnalisé si besoin.
:::

## Tester l'accessibilité

Aucun outil automatique ne détecte tout (environ un tiers des problèmes seulement), mais combiner plusieurs méthodes donne une très bonne couverture :

1. **Le clavier seul**, comme décrit plus haut.
2. **Lighthouse** (onglet dans les outils de développement de Chrome) : donne un score d'accessibilité et liste les échecs.
3. **L'extension axe DevTools** ou **WAVE** : repèrent labels manquants, contrastes, rôles invalides.
4. **Un lecteur d'écran** : NVDA (gratuit, Windows), VoiceOver (intégré à Mac et iPhone), TalkBack (Android). Même cinq minutes de test changent ta perception. Écoute ta page sans regarder l'écran.
5. **Le zoom à 200 %** et la navigation sur petit écran.
6. **Le vérificateur de plan des titres** vu au chapitre « Sémantique ».

Une démarche réaliste consiste à intégrer ces vérifications dans ton quotidien plutôt qu'en « audit final » : corriger un contraste pendant la conception coûte presque rien, le corriger après livraison coûte cher.

## Atelier guidé : auditer et corriger une page

Compte une heure et demie. Reprends la page d'inscription du chapitre précédent, ou une page de ton choix.

1. Lance Lighthouse (catégorie Accessibilité) et note le score de départ ainsi que chaque échec.
2. Passe la souris loin de l'écran et fais le parcours complet au clavier. Note les éléments inatteignables ou sans focus visible.
3. Ajoute un lien d'évitement avec son CSS et teste-le avec la touche Tab.
4. Remplace tout `outline: none` par un style `:focus-visible` bien contrasté.
5. Vérifie que chaque champ a un `label` associé et que les aides sont reliées par `aria-describedby`.
6. Ajoute `aria-current="page"` au lien de la page active dans la navigation.
7. Crée un bouton hamburger avec `aria-expanded` et `aria-controls`, et ajoute le JavaScript minimal qui bascule la valeur :

```js
const bouton = document.querySelector('[aria-controls="menu-mobile"]');
const menu = document.getElementById('menu-mobile');

bouton.addEventListener('click', () => {
  const ouvert = bouton.getAttribute('aria-expanded') === 'true';
  bouton.setAttribute('aria-expanded', String(!ouvert));
  menu.hidden = ouvert;
});
```

8. Mesure chaque couleur de texte avec le vérificateur de contraste et corrige celles sous 4,5 : 1.
9. Ajoute une région `role="status"` qui annonce « Compte créé » après envoi simulé.
10. Active NVDA ou VoiceOver, ferme les yeux, et parcours la page. Note ce qui est incompréhensible.
11. Relance Lighthouse et compare avec le score de départ.

Auto-évaluation : un utilisateur sans souris peut-il s'inscrire seul ? Chaque icône cliquable a-t-elle un nom ? Aucune information ne repose-t-elle sur la couleur seule ?

## Erreurs fréquentes

- **Supprimer le contour de focus** sans alternative visible.
- **Détourner une `div` ou un `span` en bouton**, au lieu d'un `button` natif.
- **Mal utiliser ARIA** : un rôle qui contredit l'élément natif (`role="button"` sur un lien qui navigue).
- **Oublier de mettre à jour `aria-expanded`** : le lecteur d'écran annonce un état faux.
- **Croire que l'accessibilité se limite aux personnes aveugles.** Les besoins sont variés : vue, audition, motricité, cognition.
- **Compter sur un test automatique seul.** Il manque la majorité des problèmes réels.
- **Utiliser `tabindex` positif.** L'ordre devient incontrôlable.
- **Cacher du texte avec `display: none`** alors qu'il devait rester lu par les lecteurs d'écran.

## Bonnes pratiques

- Commence par un HTML sémantique et des éléments natifs ; n'ajoute ARIA que pour combler un manque réel.
- Teste au clavier à chaque nouvelle fonctionnalité.
- Garde un focus toujours visible, avec un contraste d'au moins 3 : 1.
- Respecte 4,5 : 1 pour le texte et 3 : 1 pour les composants.
- Ne véhicule jamais une information par la couleur, le son ou la forme seuls.
- Donne un nom accessible à chaque élément interactif.
- Respecte `prefers-reduced-motion` et laisse le texte s'agrandir.
- Fais tester ton site par de vrais utilisateurs en situation de handicap quand c'est possible.

## À retenir

- L'accessibilité repose sur quatre principes WCAG : perceptible, utilisable, compréhensible, robuste. Vise le niveau AA.
- Les éléments HTML natifs apportent rôle, comportement clavier et états ; ARIA ne vient qu'en complément.
- Un site doit être **entièrement utilisable au clavier**, avec un focus toujours visible et un lien d'évitement.
- Un lien emmène quelque part, un bouton déclenche une action.
- Contraste minimal : 4,5 : 1 pour le texte normal, 3 : 1 pour le grand texte et les composants.
- Les régions `role="status"` et `role="alert"` annoncent les changements dynamiques.
- Combine clavier, outils automatiques et lecteur d'écran pour tester.
