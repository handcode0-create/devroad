---
title: Composants et états
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une interface vivante ne se limite pas à un état « au repos ». Un bouton réagit au survol, au clic, au focus clavier ; un champ signale une erreur ; un élément désactivé semble inactif. Tailwind gère tout cela avec des **variantes d'état**, directement dans les classes. Dans ce chapitre, tu construis aussi de vrais **composants réutilisables** : boutons, champs, cartes, badges.

À la fin du chapitre, tu seras capable de :

- utiliser les variantes `hover:`, `focus:`, `focus-visible:`, `active:`, `disabled:` ;
- ajouter des transitions et animations légères ;
- styliser les éléments selon leur parent ou leur frère avec `group` et `peer` ;
- construire un bouton à **variantes** (primaire, secondaire, danger) et tailles ;
- construire un champ de formulaire avec états normal, focus, erreur et désactivé ;
- gérer les classes conditionnelles en React avec une petite fonction utilitaire ;
- extraire des composants plutôt que dupliquer des classes.

Prérequis : les chapitres précédents sur Tailwind et les notions de base de React (composants, props). Prévois deux heures et demie.

## Les variantes d'état

Une **variante** est un préfixe qui limite une classe à une situation. Tu en as déjà croisé avec les écrans (`md:`). Pour les états :

```html
<button class="rounded-lg bg-indigo-600 px-4 py-2 font-medium text-white
               hover:bg-indigo-700
               focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600
               active:bg-indigo-800
               disabled:cursor-not-allowed disabled:opacity-50">
  Continuer
</button>
```

Les variantes les plus utiles :

| Variante | Quand elle s'applique |
| --- | --- |
| `hover:` | le pointeur survole l'élément |
| `focus:` | l'élément a le focus (clavier ou clic) |
| `focus-visible:` | le focus est visible, surtout lors de la navigation au clavier |
| `active:` | l'élément est en cours de clic |
| `disabled:` | l'élément a l'attribut `disabled` |
| `first:` / `last:` | premier ou dernier enfant |
| `odd:` / `even:` | lignes impaires ou paires (zèbre de tableau) |
| `placeholder:` | le texte indicatif d'un champ |
| `checked:` | case ou bouton radio coché |

> **Attention** : ne supprime jamais l'indicateur de focus sans le remplacer (`outline-none` seul est une erreur d'accessibilité). Les utilisateurs du clavier ont besoin de voir où ils se trouvent : remplace-le par un `focus-visible:ring-2` ou un contour personnalisé.

### Transitions

Sans transition, les changements sont brusques. Ajoute `transition-colors duration-150` (couleurs seulement) ou `transition` (propriétés courantes) :

```html
<a class="rounded-md px-3 py-2 text-slate-600 transition-colors duration-150 hover:bg-slate-100 hover:text-slate-900">
  Roadmaps
</a>
```

D'autres effets sont disponibles : `hover:-translate-y-0.5 hover:shadow-md` (légère élévation d'une carte), `hover:scale-105` (agrandissement), et les animations intégrées `animate-spin` (indicateur de chargement), `animate-pulse` (squelette), `animate-bounce`.

> **Astuce** : respecte les utilisateurs sensibles aux animations avec `motion-reduce:transition-none` ou `motion-safe:hover:-translate-y-0.5`. Ces variantes tiennent compte de la préférence « réduire les animations » du système.

:::quiz
Pourquoi remplacer `focus:outline-none` seul par `focus-visible:ring-2` ?
- [ ] Parce que `ring` est plus rapide à calculer
- [x] Pour conserver un indicateur de focus visible aux utilisateurs du clavier
- [ ] Parce que `outline-none` est interdit par Tailwind
- [ ] Pour désactiver le clic sur le bouton
> Supprimer le contour sans le remplacer rend la navigation au clavier impossible à suivre. `focus-visible` affiche un anneau quand c'est utile.
:::

## Styliser selon un parent ou un frère

Parfois, un élément doit changer parce qu'un **autre** élément change d'état.

### group : réagir à l'état du parent

Ajoute `group` au parent, puis `group-hover:` aux enfants :

```html
<a href="#" class="group block rounded-xl border border-slate-200 bg-white p-5 transition hover:border-indigo-300 hover:shadow-md">
  <h3 class="font-semibold text-slate-900 group-hover:text-indigo-600">Roadmap React</h3>
  <p class="mt-1 text-sm text-slate-500">7 chapitres</p>
  <span class="mt-3 inline-block text-sm font-medium text-indigo-600 opacity-0 transition-opacity group-hover:opacity-100">
    Ouvrir →
  </span>
</a>
```

Quand on survole la carte, le titre change de couleur et le lien « Ouvrir » apparaît.

### peer : réagir à l'état d'un frère

Ajoute `peer` à un élément, puis `peer-*:` sur un frère **placé après lui** dans le HTML :

```html
<div>
  <input id="email" type="email" required placeholder="toi@exemple.com"
         class="peer w-full rounded-lg border border-slate-300 px-3 py-2" />
  <p class="mt-1 hidden text-sm text-red-600 peer-invalid:block">
    Saisis une adresse e-mail valide.
  </p>
</div>
```

Cela permet des effets de formulaire sans JavaScript. Attention à l'ordre : `peer` ne cible que les frères **suivants**.

### Les états ARIA et de données

Tailwind 3 sait aussi styliser selon des attributs : `aria-expanded:`, `aria-selected:`, `aria-disabled:` ou les variantes de données `data-[state=open]:`. Cela évite de dupliquer la logique entre l'état et les classes :

```html
<button aria-expanded="true" class="rounded-lg px-3 py-2 aria-expanded:bg-indigo-50 aria-expanded:text-indigo-700">
  Détails
</button>
```

## Un bouton à variantes

Voyons comment écrire un bouton prêt pour un projet. Plusieurs variantes, plusieurs tailles, un seul composant React :

```jsx
// src/components/Button.jsx
const base =
  'inline-flex items-center justify-center gap-2 rounded-lg font-medium transition-colors ' +
  'focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 ' +
  'disabled:cursor-not-allowed disabled:opacity-50';

const variantes = {
  primaire:
    'bg-indigo-600 text-white hover:bg-indigo-700 active:bg-indigo-800 focus-visible:outline-indigo-600',
  secondaire:
    'bg-white text-slate-700 ring-1 ring-inset ring-slate-300 hover:bg-slate-50 focus-visible:outline-indigo-600',
  danger:
    'bg-red-600 text-white hover:bg-red-700 active:bg-red-800 focus-visible:outline-red-600',
  fantome:
    'text-slate-700 hover:bg-slate-100 focus-visible:outline-indigo-600',
};

const tailles = {
  sm: 'px-3 py-1.5 text-sm',
  md: 'px-4 py-2 text-sm',
  lg: 'px-5 py-3 text-base',
};

export default function Button({
  variante = 'primaire',
  taille = 'md',
  className = '',
  type = 'button',
  ...props
}) {
  return (
    <button
      type={type}
      className={`${base} ${variantes[variante]} ${tailles[taille]} ${className}`}
      {...props}
    />
  );
}
```

Utilisation :

```jsx
<Button>Enregistrer</Button>
<Button variante="secondaire" taille="sm">Annuler</Button>
<Button variante="danger" disabled>Supprimer</Button>
```

Les points clés de cette conception :

- les noms de classes sont **complets** dans des objets : Tailwind les détecte ;
- la logique de variante est **centralisée** : changer la couleur primaire = une ligne à modifier ;
- les props restantes (`onClick`, `disabled`, `aria-label`…) sont transmises avec `...props` ;
- `type="button"` par défaut évite d'envoyer un formulaire par accident.

### Les classes conditionnelles

Dès que les conditions se multiplient, la concaténation devient pénible. Une petite fonction `cn` règle le problème :

```js
// src/lib/cn.js
export function cn(...classes) {
  return classes.filter(Boolean).join(' ');
}
```

```jsx
<li className={cn(
  'rounded-lg px-3 py-2',
  actif ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600',
  desactive && 'opacity-50'
)}>
```

Les valeurs fausses (`false`, `undefined`, `''`) sont ignorées. Les bibliothèques `clsx` et `tailwind-merge` vont plus loin : cette dernière résout les **conflits** (si on passe `px-4` puis `px-6`, seule la dernière est gardée). Retiens la limite de la version maison : deux classes en conflit (`p-2 p-4`) sont toutes les deux envoyées, et c'est l'**ordre dans la feuille de style**, non l'ordre dans l'attribut, qui décide du gagnant.

:::quiz
Pourquoi définir les classes des variantes dans un objet avec des noms complets ?
- [ ] Parce que JavaScript l'exige
- [x] Pour que Tailwind détecte les noms de classes complets dans le code source
- [ ] Pour que les boutons soient plus rapides
- [ ] Parce que `className` n'accepte que des objets
> Tailwind cherche les noms de classes complets dans les fichiers de `content`. Des classes construites par morceaux (`bg-${couleur}-600`) ne sont pas détectées.
:::

## Un champ de formulaire complet

Un champ a plusieurs états visuels : normal, focus, erreur, désactivé. Voici un composant qui les gère :

```jsx
// src/components/Champ.jsx
import { useId } from 'react';
import { cn } from '@/lib/cn';

export default function Champ({ label, erreur, aide, className = '', ...props }) {
  const id = useId();
  const idErreur = `${id}-erreur`;
  const idAide = `${id}-aide`;

  return (
    <div className={className}>
      <label htmlFor={id} className="block text-sm font-medium text-slate-700">
        {label}
      </label>

      <input
        id={id}
        aria-invalid={Boolean(erreur)}
        aria-describedby={erreur ? idErreur : aide ? idAide : undefined}
        className={cn(
          'mt-1 block w-full rounded-lg border bg-white px-3 py-2 text-slate-900 shadow-sm',
          'placeholder:text-slate-400',
          'focus:outline-none focus:ring-2',
          'disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-500',
          erreur
            ? 'border-red-500 focus:border-red-500 focus:ring-red-200'
            : 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-200'
        )}
        {...props}
      />

      {erreur ? (
        <p id={idErreur} role="alert" className="mt-1 text-sm text-red-600">{erreur}</p>
      ) : aide ? (
        <p id={idAide} className="mt-1 text-sm text-slate-500">{aide}</p>
      ) : null}
    </div>
  );
}
```

Ici, on peut utiliser `focus:outline-none` car le `focus:ring-2` le remplace par un anneau visible. Le hook `useId` génère un identifiant stable pour lier le `label` et le champ. L'erreur ne repose **pas uniquement sur la couleur** : un texte et un rôle `alert` accompagnent la bordure rouge, ce qui aide les personnes daltoniennes.

## Cartes, badges et extraction de composants

Dès que tu copies-colles le même bloc de classes trois fois, extrais un composant. Un badge à variantes sur le même modèle :

```jsx
const couleurs = {
  gris: 'bg-slate-100 text-slate-700',
  vert: 'bg-green-100 text-green-800',
  orange: 'bg-amber-100 text-amber-800',
  rouge: 'bg-red-100 text-red-800',
};

export default function Badge({ couleur = 'gris', children }) {
  return (
    <span className={`inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium ${couleurs[couleur]}`}>
      {children}
    </span>
  );
}
```

Et une carte réutilisable avec `children` :

```jsx
export default function Carte({ titre, children, className = '' }) {
  return (
    <section className={`rounded-xl border border-slate-200 bg-white p-6 shadow-sm ${className}`}>
      {titre && <h2 className="mb-4 text-lg font-semibold text-slate-900">{titre}</h2>}
      {children}
    </section>
  );
}
```

> **À retenir** : la réutilisation en Tailwind passe par les **composants**, pas par `@apply`. La directive `@apply` existe (nous l'évoquons au chapitre sur l'architecture) mais reste l'exception.

## Atelier guidé : une petite bibliothèque de composants

Compte deux heures.

1. Crée `src/lib/cn.js` avec la fonction `cn`.
2. Écris `Button.jsx` avec quatre variantes et trois tailles, puis affiche toutes les combinaisons dans une page de démonstration.
3. Teste le bouton au clavier avec `Tab` : l'anneau de focus doit être visible sur chaque variante.
4. Ajoute à `Button` une prop `chargement` qui désactive le bouton et affiche un petit cercle `animate-spin` (une bordure ronde avec `border-2 border-white border-t-transparent`).
5. Écris `Champ.jsx` avec les états normal, aide, erreur et désactivé, puis affiche les quatre.
6. Écris `Badge.jsx` et `Carte.jsx`.
7. Construis une carte de roadmap interactive avec `group` : titre qui change de couleur et flèche qui apparaît au survol.
8. Ajoute `motion-reduce:transition-none` sur les transitions, puis active « réduire les animations » dans ton système pour tester.
9. Crée un formulaire d'inscription avec deux `Champ`, un `Button` et un message d'erreur provoqué à l'envoi d'un champ vide.

Auto-évaluation :

- Chaque élément interactif a-t-il un état hover, focus-visible et disabled ?
- Aucune classe n'est-elle construite par concaténation partielle ?
- L'erreur d'un champ est-elle signalée autrement que par la couleur ?
- Peux-tu changer la couleur primaire de toute l'application en modifiant un seul endroit ?

:::quiz
Où doit se trouver la classe `peer` par rapport aux éléments qui utilisent `peer-invalid:` ?
- [ ] Sur un élément placé après eux
- [ ] Sur leur parent
- [x] Sur un frère placé avant eux dans le HTML
- [ ] N'importe où dans la page
> Les variantes `peer-*` ne ciblent que les frères qui suivent l'élément marqué `peer`, à cause du fonctionnement du sélecteur CSS frère.
:::

## Erreurs fréquentes

- **Supprimer le focus sans remplacement.** `focus:outline-none` seul rend le clavier inutilisable.
- **Composer des classes avec `bg-${couleur}-500`.** Tailwind ne les génère pas. Utilise un objet de correspondance.
- **Placer `peer` après l'élément qui réagit.** Il doit venir avant dans le HTML.
- **Oublier `group` sur le parent** : `group-hover:` ne fait alors rien.
- **Confondre `focus:` et `focus-visible:`.** Le premier s'applique aussi au clic souris, ce qui est parfois voulu pour des champs, rarement pour des boutons.
- **Conflits de classes** (`p-2` et `p-4` dans le même élément) : le résultat ne dépend pas de l'ordre dans `className`. Utilise `tailwind-merge` ou évite le conflit.
- **Dupliquer cinquante fois le même bouton** au lieu de créer un composant.
- **Utiliser uniquement la couleur pour indiquer une erreur.**

## Bonnes pratiques

- Donne à chaque composant interactif des états hover, focus-visible, active et disabled cohérents.
- Centralise les variantes dans des objets de correspondance lisibles.
- Transmets `...props` pour garder les composants flexibles, avec `className` en extension.
- Ajoute des transitions courtes (100 à 200 ms) et respecte `motion-reduce`.
- Utilise `group` et `peer` pour des interactions simples sans JavaScript.
- Teste chaque composant au clavier et avec les états d'erreur.

## À retenir

- Les variantes (`hover:`, `focus-visible:`, `active:`, `disabled:`) appliquent un style dans un état précis.
- `transition-colors duration-150` adoucit les changements ; `motion-reduce:` respecte les préférences d'accessibilité.
- `group` et `group-hover:` réagissent au parent ; `peer` et `peer-*:` réagissent à un frère précédent.
- Les classes doivent être **complètes** dans le code : utilise des objets de variantes, jamais de concaténation partielle.
- Une fonction `cn` simplifie les classes conditionnelles.
- Un composant bien construit gère tous ses états : normal, survol, focus, erreur, désactivé, chargement.
- La réutilisation se fait en extrayant des composants.
