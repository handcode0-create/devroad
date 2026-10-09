---
title: Interfaces et types
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Les applications manipulent des objets : un utilisateur, une roadmap, une leçon, une commande. TypeScript te permet de décrire précisément la **forme** de ces objets. Ce chapitre présente les deux outils pour cela, `interface` et `type`, ainsi que les outils qui permettent de les composer, les transformer et les réutiliser.

À la fin du chapitre, tu seras capable de :

- décrire la forme d'un objet avec une `interface` ou un `type` ;
- marquer des propriétés optionnelles ou en lecture seule ;
- décider entre `interface` et `type` ;
- composer des types par extension et par intersection ;
- utiliser les types utilitaires `Partial`, `Pick`, `Omit`, `Required` et `Record` ;
- typer des objets imbriqués et des tableaux d'objets ;
- typer des méthodes et des classes simples.

Prérequis : les deux premiers chapitres TypeScript. Prévois deux heures. Utilise le Playground ou un projet avec `strict` activé.

## Décrire un objet

Sans type, rien n'empêche une faute de frappe sur une propriété. Avec une **interface**, tu définis un contrat :

```ts
interface Lecon {
  id: number;
  titre: string;
  minutes: number;
  terminee: boolean;
}

const lecon: Lecon = {
  id: 1,
  titre: 'Les types',
  minutes: 90,
  terminee: false,
};
```

Le compilateur vérifie trois choses : toutes les propriétés obligatoires sont présentes, elles ont le bon type, et il n'y en a pas d'inconnue.

```ts
const mauvaise: Lecon = {
  id: 2,
  titre: 'Interfaces',
  minute: 90,     // faute de frappe
};
// Erreur : 'minute' n'existe pas dans le type Lecon (et 'minutes' et 'terminee' sont manquantes).
```

On peut aussi écrire le même contrat avec un **alias de type** :

```ts
type LeconAlias = {
  id: number;
  titre: string;
  minutes: number;
  terminee: boolean;
};
```

### Propriétés optionnelles et en lecture seule

```ts
interface Roadmap {
  readonly id: number;        // ne peut pas être modifiée après création
  titre: string;
  description?: string;       // optionnelle : string | undefined
  lecons: Lecon[];            // tableau d'objets Lecon
}

const roadmap: Roadmap = { id: 1, titre: 'TypeScript', lecons: [] };

roadmap.titre = 'TypeScript avancé';   // OK
roadmap.id = 2;
// Erreur : Cannot assign to 'id' because it is a read-only property.

console.log(roadmap.description.length);
// Erreur : 'roadmap.description' is possibly 'undefined'.
console.log(roadmap.description?.length ?? 0);   // OK
```

### Objets imbriqués

Les types se composent librement : une `Roadmap` contient des `Lecon`, une `Lecon` peut contenir un `Auteur`.

```ts
interface Auteur {
  nom: string;
  pays: string;
}

interface LeconDetaillee extends Lecon {
  auteur: Auteur;
  tags: string[];
}
```

> **À retenir** : une interface ne décrit pas seulement des données, elle documente ton domaine métier. Un nouveau développeur comprend DevRoad en lisant ses types.

:::quiz
Que se passe-t-il quand on tente de modifier une propriété déclarée `readonly` ?
- [ ] Elle est modifiée sans problème
- [ ] La modification est ignorée à l'exécution sans erreur
- [x] TypeScript signale une erreur de compilation
- [ ] L'objet est supprimé
> `readonly` est vérifié à la compilation : l'affectation est refusée. Comme les types disparaissent à l'exécution, ce n'est pas une protection runtime.
:::

## interface ou type ?

Les deux décrivent des formes d'objets, et dans la plupart des cas ils sont interchangeables. Voici les différences utiles.

| Besoin | `interface` | `type` |
| --- | --- | --- |
| Décrire un objet | Oui | Oui |
| Étendre un autre type | `extends` | Intersection `&` |
| Union de plusieurs types | Non | Oui |
| Types primitifs, tuples, fonctions | Non | Oui |
| Fusion de déclarations (même nom déclaré deux fois) | Oui | Non |

Une règle simple et très répandue :

- utilise `interface` pour décrire la forme d'**objets** et de **classes** ;
- utilise `type` pour tout le reste : unions, tuples, alias de fonctions, types calculés.

Le plus important est la **cohérence** dans ton projet. Les deux marchent.

```ts
type Niveau = 'beginner' | 'intermediate' | 'professional';       // union : type
type Predicat<T> = (valeur: T) => boolean;                         // fonction : type
type Coordonnees = [number, number];                               // tuple : type
interface Utilisateur { id: number; nom: string; niveau: Niveau } // objet : interface
```

## Étendre et combiner

### extends

Une interface peut hériter d'une autre et ajouter des propriétés :

```ts
interface Entite {
  readonly id: number;
  creeLe: string;
}

interface Utilisateur extends Entite {
  nom: string;
  email: string;
}

interface Admin extends Utilisateur {
  permissions: string[];
}
```

### Intersection

Avec `type`, on combine plusieurs types avec `&` : l'objet doit satisfaire **tous** les types.

```ts
type AvecDates = { creeLe: string; modifieLe: string };
type AvecAuteur = { auteur: string };

type Article = AvecDates & AvecAuteur & { titre: string };

const article: Article = {
  titre: 'Hello',
  auteur: 'Awa',
  creeLe: '2026-01-01',
  modifieLe: '2026-01-02',
};
```

## Les types utilitaires

TypeScript fournit des types qui **transforment** d'autres types. Ils t'évitent de recopier des définitions.

Prenons ce type de base :

```ts
interface Lecon {
  id: number;
  titre: string;
  minutes: number;
  terminee: boolean;
}
```

### Partial et Required

`Partial<T>` rend toutes les propriétés optionnelles : parfait pour une mise à jour partielle. `Required<T>` fait l'inverse.

```ts
function mettreAJour(lecon: Lecon, modifications: Partial<Lecon>): Lecon {
  return { ...lecon, ...modifications };
}

mettreAJour(lecon, { terminee: true });   // OK : seule une propriété
```

### Pick et Omit

`Pick<T, 'a' | 'b'>` garde uniquement certaines propriétés ; `Omit<T, 'a'>` retire des propriétés.

```ts
type LeconResume = Pick<Lecon, 'id' | 'titre'>;
// { id: number; titre: string }

type NouvelleLecon = Omit<Lecon, 'id'>;
// { titre: string; minutes: number; terminee: boolean }

function creer(donnees: NouvelleLecon): Lecon {
  return { id: Date.now(), ...donnees };
}
```

Le type `NouvelleLecon` correspond exactement à ce qu'on envoie à l'API pour créer une leçon (l'identifiant est généré côté serveur).

### Record

`Record<Cle, Valeur>` décrit un objet dont les clés sont d'un type et les valeurs d'un autre :

```ts
type Niveau = 'beginner' | 'intermediate' | 'professional';

const libelles: Record<Niveau, string> = {
  beginner: 'Débutant',
  intermediate: 'Intermédiaire',
  professional: 'Professionnel',
};
```

Si tu ajoutes un niveau à l'union mais que tu oublies son libellé, le compilateur signale l'oubli. Excellent filet de sécurité.

On peut aussi s'en servir pour des dictionnaires :

```ts
const parId: Record<number, Lecon> = {};
parId[1] = lecon;
```

### Readonly et ReturnType

`Readonly<T>` rend toutes les propriétés en lecture seule. `ReturnType<typeof fonction>` extrait le type de retour d'une fonction, utile pour ne pas dupliquer :

```ts
function calculerStats(lecons: Lecon[]) {
  return { total: lecons.length, minutes: lecons.reduce((s, l) => s + l.minutes, 0) };
}

type Stats = ReturnType<typeof calculerStats>;
// { total: number; minutes: number }
```

:::quiz
Quel type utilitaire représente « toutes les propriétés de `Lecon`, sauf `id` » ?
- [ ] Pick<Lecon, 'id'>
- [ ] Partial<Lecon>
- [x] Omit<Lecon, 'id'>
- [ ] Required<Lecon>
> `Omit` retire les propriétés indiquées. `Pick` fait l'inverse en gardant seulement celles qu'on liste, et `Partial` les rend toutes optionnelles.
:::

## Signatures d'index et clés dynamiques

Quand tu ne connais pas à l'avance les noms des propriétés, une **signature d'index** décrit un objet dictionnaire :

```ts
interface Scores {
  [matiere: string]: number;
}

const scores: Scores = { html: 90, css: 75 };
scores.js = 60;
```

Avec `strict`, tu peux activer `noUncheckedIndexedAccess` : l'accès `scores.php` retourne alors `number | undefined`, ce qui reflète la réalité.

Pour une liste de clés connue, préfère `Record` ou une union de littéraux.

### keyof et accès typé

`keyof T` produit l'union des noms de propriétés. Avec lui, tu écris des fonctions génériques sûres :

```ts
type CleLecon = keyof Lecon;   // 'id' | 'titre' | 'minutes' | 'terminee'

function lire(lecon: Lecon, cle: CleLecon) {
  return lecon[cle];
}

lire(lecon, 'titre');       // OK
lire(lecon, 'duree');
// Erreur : l'argument de type '"duree"' n'est pas assignable à CleLecon.
```

## Méthodes et classes

Une interface peut décrire des méthodes, et une classe peut s'engager à la respecter avec `implements` :

```ts
interface Suivi {
  valider(idLecon: number): void;
  pourcentage(): number;
}

class SuiviProgression implements Suivi {
  private terminees = new Set<number>();

  constructor(private readonly total: number) {}

  valider(idLecon: number): void {
    this.terminees.add(idLecon);
  }

  pourcentage(): number {
    return this.total === 0 ? 0 : Math.round((this.terminees.size / this.total) * 100);
  }
}

const suivi = new SuiviProgression(10);
suivi.valider(1);
console.log(suivi.pourcentage());   // 10
```

`private` empêche l'accès depuis l'extérieur de la classe (vérification à la compilation), et `readonly` interdit la réaffectation. Écrire `constructor(private readonly total: number)` déclare et initialise la propriété en une seule ligne.

:::quiz
Dans quel cas un alias `type` est-il nécessaire plutôt qu'une `interface` ?
- [ ] Pour décrire un objet avec des méthodes
- [ ] Pour étendre une autre interface
- [x] Pour définir une union comme `'a' | 'b'`
- [ ] Pour rendre une propriété optionnelle
> Une interface ne peut décrire que des formes d'objets. Les unions, tuples et alias de fonctions nécessitent le mot-clé `type`.
:::

## Atelier guidé : modéliser DevRoad

Compte une heure. Crée un fichier `modeles.ts` dans un projet avec `strict`.

1. Définis `type Niveau` comme une union de trois littéraux.
2. Écris l'interface `Lecon` (`id`, `titre`, `minutes`, `niveau`, `terminee`) et `Roadmap` (`id` en `readonly`, `titre`, `description?`, `lecons: Lecon[]`).
3. Crée une constante `roadmapLaravel: Roadmap` avec trois leçons, puis introduis une faute de frappe pour lire l'erreur.
4. Crée `NouvelleLecon` avec `Omit<Lecon, 'id' | 'terminee'>` et une fonction `creerLecon(donnees: NouvelleLecon): Lecon`.
5. Écris `mettreAJourLecon(lecon: Lecon, modifs: Partial<Omit<Lecon, 'id'>>): Lecon` qui empêche de modifier l'identifiant.
6. Crée `libelles: Record<Niveau, string>` ; ajoute ensuite un quatrième niveau à l'union et observe l'erreur.
7. Écris `resumer(roadmap: Roadmap): Pick<Roadmap, 'id' | 'titre'> & { nbLecons: number }`.
8. Écris `trierPar(lecons: Lecon[], cle: 'titre' | 'minutes'): Lecon[]` sans modifier le tableau d'origine.
9. Écris une classe `SuiviProgression` qui implémente une interface `Suivi`, comme dans le cours.
10. Lance `npx tsc --noEmit` pour confirmer l'absence d'erreur.

Pour t'auto-évaluer : sans regarder, écris les types que tu utiliserais pour (a) créer une leçon, (b) la mettre à jour partiellement, (c) l'afficher dans une liste.

## Erreurs fréquentes

- **Dupliquer des types presque identiques.** Utilise `Pick`, `Omit` et `Partial` pour les dériver.
- **Rendre trop de propriétés optionnelles.** Chaque `?` oblige à gérer `undefined` partout.
- **Utiliser `object` ou `{}` comme type.** Ils sont trop larges ; décris la forme réelle.
- **Confondre `readonly` et immutabilité à l'exécution.** Il n'existe qu'à la compilation.
- **Oublier que les types ne valident pas le JSON reçu.** Une réponse d'API doit être vérifiée à l'exécution.
- **Étendre avec `&` deux types aux propriétés incompatibles.** Le résultat peut devenir `never`.
- **Mettre une logique métier dans les types.** Ils décrivent des formes, pas des règles de calcul.

## Bonnes pratiques

- Nomme les types au singulier et en PascalCase : `Lecon`, `Roadmap`.
- Une interface par concept métier, regroupées dans un fichier `types.ts` ou à côté du code concerné.
- Utilise `readonly` pour les identifiants et les données qui ne doivent pas changer.
- Dérive plutôt que dupliquer : un type d'envoi vient du type principal.
- Préfère les unions de littéraux aux chaînes libres.
- Exporte seulement les types dont d'autres fichiers ont besoin.
- Choisis `interface` ou `type` pour les objets et garde ce choix partout.

## À retenir

- Une `interface` ou un `type` décrit la forme d'un objet : propriétés obligatoires, optionnelles (`?`) ou `readonly`.
- `interface` pour les objets et les classes, `type` pour unions, tuples et fonctions.
- `extends` et `&` combinent les types.
- `Partial`, `Required`, `Pick`, `Omit`, `Record`, `Readonly` et `ReturnType` dérivent de nouveaux types.
- `keyof` produit l'union des clés d'un type et sécurise les accès dynamiques.
- Une classe peut s'engager à respecter une interface avec `implements`.
- Les types décrivent des promesses à la compilation, ils ne valident pas les données à l'exécution.
