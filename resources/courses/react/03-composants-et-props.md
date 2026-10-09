---
title: Composants et props
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Dans les chapitres précédents, tu as écrit tes premiers composants et monté un projet Vite. Mais tes composants affichaient toujours la même chose. Un composant réutilisable doit pouvoir **s'adapter** : afficher une roadmap différente à chaque fois qu'on l'utilise. C'est le rôle des **props**.

À la fin du chapitre, tu seras capable de :

- passer des données à un composant avec des **props** et les lire par déstructuration ;
- définir des **valeurs par défaut** et comprendre que les props sont en lecture seule ;
- utiliser la prop spéciale **children** pour créer des composants « conteneurs » ;
- composer plusieurs composants pour construire une page ;
- transmettre des fonctions et des objets sous forme de props ;
- découper une maquette en composants cohérents.

Prérequis : avoir lu les chapitres « Découvrir React » et « Créer un projet React », avoir un projet Vite qui tourne. Prévois deux heures.

## Les props : les paramètres d'un composant

Un composant est une fonction. Et une fonction peut recevoir des paramètres. Dans React, tous les paramètres d'un composant arrivent dans **un seul objet**, appelé `props` (pour *properties*).

Voici une carte qui affiche une roadmap de DevRoad :

```jsx
function CarteRoadmap(props) {
  return (
    <article className="carte">
      <h2>{props.titre}</h2>
      <p>{props.description}</p>
    </article>
  );
}
```

On passe les valeurs comme des attributs HTML :

```jsx
function App() {
  return (
    <main>
      <CarteRoadmap titre="React" description="Construire des interfaces." />
      <CarteRoadmap titre="Laravel" description="Un framework PHP complet." />
    </main>
  );
}
```

Le même composant affiche maintenant deux contenus différents. C'est la clé de la réutilisation : **un composant, plusieurs usages**.

### La déstructuration

Écrire `props.titre` partout est lourd. En JavaScript, on peut **déstructurer** l'objet directement dans la signature de la fonction :

```jsx
function CarteRoadmap({ titre, description }) {
  return (
    <article className="carte">
      <h2>{titre}</h2>
      <p>{description}</p>
    </article>
  );
}
```

C'est la forme que tu verras dans presque tous les projets. Elle a un avantage : en lisant la première ligne, on sait immédiatement **quelles données le composant attend**.

### Passer autre chose que du texte

Une chaîne de caractères se passe entre guillemets. Tout le reste (nombre, booléen, tableau, objet, fonction) se passe **entre accolades** :

```jsx
<CarteRoadmap
  titre="React"
  nombreDeChapitres={7}
  populaire={true}
  tags={['frontend', 'javascript']}
  auteur={{ nom: 'Awa', pays: 'CI' }}
/>
```

Pour un booléen à `true`, on peut écrire simplement `<CarteRoadmap populaire />`. Les accolades ne sont pas un « double niveau » : la première paire signifie « voici du JavaScript », et dans le cas de l'objet, la seconde paire est l'objet lui-même (d'où `{{ ... }}`).

> **Attention** : `nombreDeChapitres="7"` passe la **chaîne** « 7 », pas le nombre 7. Pour un nombre, écris toujours `nombreDeChapitres={7}`.

:::quiz
Comment passer le nombre 7 à la prop `chapitres` d'un composant `Carte` ?
- [ ] `<Carte chapitres="7" />`
- [x] `<Carte chapitres={7} />`
- [ ] `<Carte chapitres=7 />`
- [ ] `<Carte {chapitres: 7} />`
> Les guillemets passent une chaîne. Pour un nombre, un booléen, un tableau, un objet ou une fonction, on utilise des accolades.
:::

## Valeurs par défaut

Que se passe-t-il si on oublie de fournir une prop ? Elle vaut `undefined`, et l'interface peut afficher « undefined » ou planter. On évite cela avec une **valeur par défaut**, directement dans la déstructuration :

```jsx
function Badge({ texte, couleur = 'gris' }) {
  return <span className={`badge badge-${couleur}`}>{texte}</span>;
}

<Badge texte="Nouveau" />                  {/* couleur = 'gris' */}
<Badge texte="Populaire" couleur="orange" />
```

Une valeur par défaut ne s'applique que si la prop est absente ou `undefined`. Si tu passes `null`, c'est `null` qui est utilisé.

## Les props sont en lecture seule

C'est une règle fondamentale : **un composant ne modifie jamais ses props**. Elles appartiennent à son parent.

```jsx
function Titre({ texte }) {
  texte = texte.toUpperCase();   // à éviter : on réaffecte la prop
  return <h1>{texte}</h1>;
}
```

Même si JavaScript ne t'en empêche pas toujours (surtout pour les objets et tableaux), c'est une mauvaise pratique. Si tu as besoin d'une version transformée, **calcule une nouvelle variable** :

```jsx
function Titre({ texte }) {
  const texteMajuscule = texte.toUpperCase();
  return <h1>{texteMajuscule}</h1>;
}
```

Pense à une fonction pure : mêmes arguments, même résultat, et aucun effet de bord sur ce qu'on lui donne. Les données descendent du parent vers l'enfant, **jamais l'inverse**. Pour qu'un enfant puisse déclencher un changement chez son parent, on passe une fonction en prop (nous y venons plus bas), et le chapitre suivant introduit l'état qui rend cela vivant.

## Afficher une liste de composants

Dans la plupart des applications, les données viennent d'un tableau. On combine `map` et props :

```jsx
const roadmaps = [
  { id: 1, titre: 'React', niveau: 'Débutant', chapitres: 7 },
  { id: 2, titre: 'Laravel', niveau: 'Intermédiaire', chapitres: 13 },
  { id: 3, titre: 'Tailwind', niveau: 'Débutant', chapitres: 7 },
];

function ListeRoadmaps() {
  return (
    <section>
      {roadmaps.map((r) => (
        <CarteRoadmap
          key={r.id}
          titre={r.titre}
          niveau={r.niveau}
          chapitres={r.chapitres}
        />
      ))}
    </section>
  );
}
```

La `key` doit être **unique parmi les frères** et **stable** (l'identifiant de la donnée, pas l'index du tableau si la liste peut changer). Elle n'est pas transmise au composant : `key` n'est pas une prop que l'on peut lire.

Quand un objet contient exactement les props attendues, tu peux utiliser l'**opérateur de décomposition** :

```jsx
{roadmaps.map((r) => <CarteRoadmap key={r.id} {...r} />)}
```

C'est pratique, mais utilise-le avec parcimonie : la lecture est moins explicite, et on peut transmettre des props par accident.

## La prop spéciale `children`

Certains composants servent d'**enveloppe** : une carte, une modale, un encadré. Ils ne savent pas à l'avance ce qu'ils contiennent. Ce qu'on écrit entre la balise ouvrante et la balise fermante arrive dans la prop `children` :

```jsx
function Encadre({ titre, children }) {
  return (
    <aside className="encadre">
      <h3>{titre}</h3>
      <div className="encadre-contenu">{children}</div>
    </aside>
  );
}

function Exemple() {
  return (
    <Encadre titre="Astuce">
      <p>Relis toujours le message d'erreur en entier.</p>
      <button>J'ai compris</button>
    </Encadre>
  );
}
```

`children` peut être du texte, un élément, un tableau d'éléments, ou rien. Cette technique s'appelle la **composition** : au lieu de créer un composant avec dix options, tu crées une enveloppe et tu laisses le parent décider du contenu. C'est la façon idiomatique de construire des layouts, comme `AppLayout` dans DevRoad qui entoure chaque page avec la navigation.

> **Astuce** : si tu te surprends à ajouter une cinquième prop booléenne pour contrôler ce que le composant affiche (`avecIcone`, `avecBouton`, `avecPied`…), c'est le signe qu'un `children` ferait mieux le travail.

:::quiz
Que contient la prop `children` d'un composant `Encadre` utilisé ainsi : `<Encadre><p>Salut</p></Encadre>` ?
- [ ] La chaîne « Salut »
- [ ] Rien, il faut la déclarer dans le parent
- [x] L'élément `<p>Salut</p>` écrit entre les balises
- [ ] Le nom du composant parent
> Tout ce qui est écrit entre la balise ouvrante et la balise fermante d'un composant est reçu dans `children`.
:::

## Transmettre des fonctions

Une prop peut être une fonction. C'est ainsi qu'un enfant « remonte » une information à son parent :

```jsx
function BoutonSupprimer({ onSupprimer }) {
  return <button onClick={onSupprimer}>Supprimer</button>;
}

function Parent() {
  function supprimerRoadmap() {
    console.log('Roadmap supprimée');
  }
  return <BoutonSupprimer onSupprimer={supprimerRoadmap} />;
}
```

Convention de nommage : les props de type fonction commencent par `on` (`onSupprimer`, `onChange`, `onSelect`), et les fonctions définies dans le parent par `handle` (`handleSupprimer`). Cela rend le sens de circulation évident.

Attention à la différence entre **passer** une fonction et l'**appeler** :

```jsx
<button onClick={supprimer}>OK</button>      {/* passe la fonction : correct */}
<button onClick={supprimer()}>Mauvais</button> {/* l'appelle au rendu : bug */}
<button onClick={() => supprimer(id)}>OK</button> {/* fonction fléchée avec argument */}
```

## Composer une page : penser en arbre

Une page est un **arbre de composants**. Les données descendent par les props. Prenons la page d'accueil de DevRoad :

```text
Accueil
├── EnTete (titre, utilisateur)
├── ListeRoadmaps (roadmaps)
│   └── CarteRoadmap (titre, niveau, chapitres) x N
│       └── Badge (texte, couleur)
└── PiedDePage
```

Pour découper une maquette, applique cette méthode en quatre temps :

1. **Dessine des rectangles** autour de chaque zone de la maquette ;
2. **Nomme** chaque rectangle d'un nom qui décrit son rôle (pas son apparence) ;
3. **Repère les répétitions** : tout ce qui apparaît plusieurs fois devient un composant ;
4. **Liste les données** nécessaires à chaque composant : ce sont ses props.

### Le « prop drilling »

Si une donnée doit traverser quatre niveaux de composants pour arriver à celui qui l'utilise, tu fais du *prop drilling*. Pour un ou deux niveaux, c'est parfaitement normal. Au-delà, on réorganise (la composition avec `children` aide souvent) ou on utilise un contexte, ce que nous verrons dans le chapitre sur l'architecture.

## Atelier guidé : la page des roadmaps

Compte une heure et demie. Tu travailles dans le projet Vite créé au chapitre précédent.

1. Dans `src/components/`, crée `Badge.jsx` : il reçoit `texte` et `couleur` (défaut `'gris'`) et affiche un `span` avec les classes `badge badge-` suivie de la couleur.
2. Crée `CarteRoadmap.jsx` : props `titre`, `description`, `niveau`, `chapitres`. Elle affiche un `article` avec un `h2`, la description, un `Badge` pour le niveau et un texte « N chapitres ».
3. Dans `src/data/roadmaps.js`, exporte un tableau de cinq roadmaps (avec `id`, `titre`, `description`, `niveau`, `chapitres`).
4. Crée `ListeRoadmaps.jsx` qui reçoit la prop `roadmaps` et affiche une `CarteRoadmap` par élément, avec une `key` basée sur `id`.
5. Si `roadmaps` est vide, affiche « Aucune roadmap pour le moment » (retour anticipé).
6. Crée `Section.jsx` avec les props `titre` et `children`, et utilise-la pour envelopper `ListeRoadmaps` dans `App.jsx`.
7. Ajoute une prop `onChoisir` à `CarteRoadmap` : un bouton « Voir » appelle `onChoisir(id)`. Dans `App`, fais un `console.log` de l'identifiant reçu.
8. Ajoute la mention « Populaire » (un `Badge` orange) uniquement quand la prop booléenne `populaire` vaut `true`.

Auto-évaluation : peux-tu répondre « oui » à chaque question ?

- Chaque composant est-il dans son propre fichier avec un export par défaut ?
- Les props ont-elles des noms qui décrivent leur rôle ?
- As-tu évité de modifier une prop dans un composant ?
- La console est-elle exempte d'avertissements (clés manquantes) ?
- Pourrais-tu réutiliser `Badge` ailleurs sans le modifier ?

:::quiz
Dans quelle direction circulent normalement les données entre composants React ?
- [ ] De l'enfant vers le parent, via les props
- [ ] Dans les deux sens, automatiquement
- [x] Du parent vers l'enfant, via les props
- [ ] Entre frères, sans passer par le parent
> Les props descendent du parent vers l'enfant. Pour remonter une information, le parent passe une fonction que l'enfant appelle.
:::

## Erreurs fréquentes

- **Oublier la déstructuration et écrire `titre` au lieu de `props.titre`.** Sans accolades dans la signature, `titre` n'existe pas : écris `({ titre })`.
- **Passer un nombre entre guillemets.** `age="12"` est une chaîne : `"12" + 1` donne `"121"`.
- **Appeler la fonction au lieu de la passer.** `onClick={supprimer()}` s'exécute à chaque rendu.
- **Modifier une prop.** Calcule une nouvelle variable à la place.
- **Utiliser l'index comme `key` dans une liste qui change.** Les éléments se mélangent lors des suppressions ou des tris.
- **Lire `props.key`.** Elle n'est pas transmise ; si tu en as besoin, passe aussi un `id`.
- **Déclarer un composant à l'intérieur d'un autre.** Il est recréé à chaque rendu, ce qui cause des pertes de contenu inattendues. Déclare-le au niveau du module.

## Bonnes pratiques

- Donne aux props des noms clairs et cohérents (`onSelect`, `isActive`, `titre`).
- Garde peu de props : au-delà de cinq ou six, demande-toi si le composant n'en fait pas trop.
- Préfère la composition avec `children` à une multitude d'options booléennes.
- Fournis des valeurs par défaut raisonnables pour les props facultatives.
- Un composant par fichier, nommé comme son export, en PascalCase.
- Passe uniquement ce qui est nécessaire : un composant d'avatar reçoit `src` et `nom`, pas tout l'objet utilisateur.

## À retenir

- Les **props** sont les paramètres d'un composant, reçus dans un objet unique que l'on déstructure.
- Les chaînes se passent avec des guillemets, tout le reste avec des accolades.
- Les props sont **en lecture seule** : les données descendent du parent vers l'enfant.
- Une valeur par défaut se définit dans la déstructuration : `couleur = 'gris'`.
- `children` permet de créer des composants conteneurs réutilisables par composition.
- Une prop peut être une fonction (`onSelect`) : c'est ainsi qu'un enfant prévient son parent.
- Une page est un arbre de composants : découpe d'abord la maquette, puis liste les données de chacun.
