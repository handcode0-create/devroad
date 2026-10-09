---
title: Découvrir TypeScript
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

TypeScript est JavaScript avec un système de **types**. Il ne remplace pas JavaScript : il l'enrichit d'une couche de vérification qui repère de nombreuses erreurs avant même que tu lances ton programme. Aujourd'hui, la plupart des projets professionnels en React, Next.js ou Node.js utilisent TypeScript. DevRoad lui-même en bénéficie dans son interface.

À la fin du chapitre, tu seras capable de :

- expliquer ce que TypeScript apporte et comment il s'exécute ;
- installer TypeScript et compiler un fichier `.ts` ;
- annoter une variable avec un type et laisser l'**inférence** faire le travail ;
- lire une erreur du compilateur ;
- distinguer erreur de compilation et erreur d'exécution ;
- utiliser le Playground en ligne pour expérimenter.

Prérequis : bases de JavaScript (variables, fonctions, tableaux, objets). Si le chapitre « Fondamentaux JavaScript » est derrière toi, tu es prêt. Prévois deux heures. Tu peux tout tester sans rien installer dans le **TypeScript Playground** (typescriptlang.org/play).

## Le problème que TypeScript résout

Observe ce code JavaScript parfaitement valide :

```js
function calculerTotal(prix, quantite) {
  return prix * quantite;
}

console.log(calculerTotal(1500, 3));       // 4500
console.log(calculerTotal('1500', 3));     // 4500 (conversion silencieuse)
console.log(calculerTotal('abc', 3));      // NaN
console.log(calculerTotal(1500));          // NaN (quantite vaut undefined)
```

JavaScript ne signale rien. Les erreurs apparaissent plus tard, quand un client voit « NaN FCFA » sur sa facture. Plus le projet grandit, plus ces erreurs de type sont fréquentes : une propriété mal orthographiée, un `null` inattendu, une fonction appelée avec les arguments dans le mauvais ordre.

Avec TypeScript, tu déclares ce que la fonction attend :

```ts
function calculerTotal(prix: number, quantite: number): number {
  return prix * quantite;
}

calculerTotal('1500', 3);
// Erreur : Argument of type 'string' is not assignable to parameter of type 'number'.
calculerTotal(1500);
// Erreur : Expected 2 arguments, but got 1.
```

Les deux erreurs sont soulignées en rouge dans l'éditeur, **avant** l'exécution. Tu corriges en quelques secondes ce qui aurait demandé une enquête.

> **À retenir** : TypeScript est un **vérificateur statique**. Il analyse ton code sans l'exécuter et signale les incohérences de types.

## Comment TypeScript fonctionne

Les navigateurs et Node.js ne comprennent pas TypeScript. Le **compilateur** (`tsc`) vérifie les types puis **retire** les annotations pour produire du JavaScript ordinaire.

```text
fichier.ts  ──(tsc : vérification + transformation)──►  fichier.js  ──►  navigateur / Node.js
```

Deux conséquences importantes :

1. Les types **n'existent plus à l'exécution**. Ils ne ralentissent pas ton programme et ne le protègent pas non plus des données externes (réponse d'API, saisie utilisateur).
2. Tout JavaScript valide est du TypeScript valide. Tu peux migrer un projet fichier par fichier.

En pratique, tu utilises rarement `tsc` seul : Vite, Next.js ou `tsx` s'occupent de la transformation, et l'éditeur (VS Code intègre TypeScript) te montre les erreurs en direct.

:::quiz
Que devient le typage TypeScript lorsque le programme s'exécute dans le navigateur ?
- [ ] Il vérifie chaque valeur à chaque instruction
- [x] Il a disparu : le navigateur exécute du JavaScript sans annotations
- [ ] Il est converti en commentaires lisibles par le navigateur
- [ ] Il est envoyé au serveur pour validation
> Le compilateur retire les types lors de la transformation. Ils servent uniquement au développement, pour détecter les erreurs avant l'exécution.
:::

## Installer et lancer TypeScript

Crée un dossier de test et installe TypeScript localement :

```bash
mkdir ts-decouverte && cd ts-decouverte
npm init -y
npm install --save-dev typescript tsx
npx tsc --init
```

La dernière commande crée `tsconfig.json`, le fichier de configuration (nous y reviendrons dans un chapitre dédié). Crée ensuite `bonjour.ts` :

```ts
const prenom: string = 'Awa';
console.log(`Bonjour ${prenom} !`);
```

Deux façons de l'exécuter :

```bash
npx tsc bonjour.ts     # compile : produit bonjour.js
node bonjour.js

npx tsx bonjour.ts     # exécute directement (pratique pour apprendre)
```

Pour vérifier les types sans produire de fichier, utilise `npx tsc --noEmit`. C'est la commande qu'on lance en intégration continue.

## Annoter les types

On ajoute un type après le nom, séparé par deux-points :

```ts
let titre: string = 'Introduction';
let minutes: number = 90;
let terminee: boolean = false;
```

Si tu essaies d'affecter une valeur d'un autre type, le compilateur proteste :

```ts
minutes = 'cent vingt';
// Erreur : Type 'string' is not assignable to type 'number'.
```

Les types primitifs de base sont `string`, `number`, `boolean`, mais aussi `null` et `undefined`. Nous les détaillerons au chapitre suivant. Les types commencent par une **minuscule** : `string` et non `String` (ce dernier désigne un objet enveloppe qu'on n'utilise presque jamais).

## L'inférence de types

Tu n'as pas besoin d'annoter chaque variable. TypeScript **devine** le type à partir de la valeur d'initialisation :

```ts
let titre = 'Introduction';    // TypeScript infère : string
let minutes = 90;              // number
const actif = true;            // boolean (littéral true avec const)

titre = 42;
// Erreur : Type 'number' is not assignable to type 'string'.
```

Survole une variable dans VS Code : l'infobulle affiche le type déduit.

Règle d'usage la plus répandue :

- **laisse l'inférence faire** pour les variables locales initialisées ;
- **annote** les paramètres de fonctions et, souvent, leurs valeurs de retour, car ils forment le « contrat » de ta fonction ;
- **annote** quand l'inférence ne peut pas deviner (variable déclarée sans valeur, tableau vide).

```ts
const lecons: string[] = [];   // sans annotation, le type serait never[]
let resultat: number;          // déclarée sans valeur
```

> **Astuce** : trop d'annotations alourdissent le code. Si l'infobulle de l'éditeur affiche déjà le bon type, tu n'as rien à ajouter.

## Lire une erreur TypeScript

Les messages peuvent paraître longs, mais ils suivent un schéma constant. Lis-les du dernier au premier.

```ts
function afficher(lecon: { titre: string; minutes: number }) {
  console.log(`${lecon.titre} : ${lecon.minutes} min`);
}

afficher({ titre: 'Types', minute: 90 });
```

L'erreur mentionne que l'objet littéral ne peut spécifier que des propriétés connues et que `minute` n'existe pas ; il suggère même « Did you mean 'minutes' ? ». TypeScript vient de trouver une faute de frappe qui, en JavaScript, aurait affiché `undefined min`.

Les erreurs ont un code (`TS2322`, `TS2345`...) que tu peux rechercher. Les plus fréquentes pour un débutant :

| Code | Message résumé | Cause |
| --- | --- | --- |
| TS2322 | Type X is not assignable to type Y | Mauvais type affecté |
| TS2345 | Argument of type X is not assignable to parameter of type Y | Mauvais argument passé à une fonction |
| TS2339 | Property X does not exist on type Y | Propriété inconnue ou mal écrite |
| TS2532 | Object is possibly undefined | Valeur potentiellement absente non vérifiée |

:::quiz
Quelle est la bonne pratique concernant les annotations de type ?
- [ ] Annoter absolument toutes les variables
- [ ] Ne jamais annoter, l'inférence suffit toujours
- [x] Laisser l'inférence pour les variables locales et annoter surtout les paramètres de fonctions
- [ ] Annoter uniquement les constantes
> L'inférence suffit pour une variable initialisée. Les paramètres, eux, définissent le contrat d'une fonction et doivent être annotés.
:::

## Erreur de compilation ou d'exécution ?

Il est essentiel de distinguer deux moments :

- **Compilation** (ou vérification) : TypeScript analyse ton code. Une erreur de type est détectée ici.
- **Exécution** : le JavaScript tourne. Une exception (`TypeError`, réponse réseau invalide) arrive ici.

TypeScript te protège **seulement du premier moment**. Imagine une API qui renvoie un champ `minutes` sous forme de texte alors que tu l'as typé en nombre : le compilateur est satisfait, mais ton code se comportera mal à l'exécution. Pour les données externes, il faudra toujours une **validation** à l'exécution (nous la verrons au chapitre sur React et en projet final).

```ts
const reponse: { minutes: number } = JSON.parse('{"minutes": "90"}');
console.log(reponse.minutes + 1);   // affiche "901" : TypeScript n'a rien vu
```

> **Attention** : annoter une donnée ne la rend pas correcte. Un type est une **promesse** que tu fais au compilateur ; si elle est fausse, il ne peut pas le savoir.

## Un premier exemple complet avec DevRoad

Voici un petit programme typé qui calcule la progression d'une roadmap :

```ts
function calculerPourcentage(terminees: number, total: number): number {
  if (total === 0) return 0;
  return Math.round((terminees / total) * 100);
}

function formaterDuree(minutes: number): string {
  const h = Math.floor(minutes / 60);
  const m = minutes % 60;
  return h === 0 ? `${m} min` : `${h} h ${m}`;
}

const nomRoadmap = 'Laravel';
const lecons = [90, 120, 150, 150];
const terminees = 2;

console.log(
  `${nomRoadmap} : ${calculerPourcentage(terminees, lecons.length)} %`
);
console.log(`Durée totale : ${formaterDuree(lecons.reduce((a, b) => a + b, 0))}`);
```

Remarque que `lecons` est inféré comme `number[]` : `reduce`, `length` et les autres méthodes sont donc vérifiées automatiquement. L'autocomplétion de l'éditeur devient aussi beaucoup plus précise : en tapant `lecons.`, tu vois uniquement les méthodes des tableaux de nombres.

## Atelier guidé : ton premier projet TypeScript

Compte une heure. Tu peux utiliser le Playground ou le projet installé plus haut.

1. Ouvre le Playground et recopie la fonction `calculerTotal` en JavaScript, puis ajoute les annotations `number`. Observe les erreurs avec `'1500'` et avec un argument manquant.
2. Déclare une variable `let minutes = 90;` sans annotation, survole-la pour lire le type inféré, puis essaie de lui affecter une chaîne.
3. Déclare `const lecons = [];` puis ajoute `lecons.push('Types')`. Lis l'erreur ou le type inféré et corrige avec `const lecons: string[] = [];`.
4. Écris `calculerPourcentage` et `formaterDuree` comme dans le cours ; appelle-les avec de mauvais arguments pour voir TS2345.
5. Écris une fonction `afficher(lecon: { titre: string; minutes: number })` et fais volontairement une faute de frappe sur `minutes`.
6. Dans ton projet local, crée `bonjour.ts`, exécute-le avec `npx tsx`, puis lance `npx tsc --noEmit` pour vérifier les types seuls.
7. Provoque une erreur dans `bonjour.ts`, lance `npx tsc --noEmit` et lis le code d'erreur, le fichier et la ligne.
8. Écris la commande `JSON.parse` qui donne un faux `number` pour constater que le compilateur ne voit pas l'erreur d'exécution.

Pour t'auto-évaluer : explique à voix haute pourquoi TypeScript ne peut pas te protéger d'une API qui renvoie des données inattendues, et quelle précaution prendre.

## Erreurs fréquentes

- **Croire que TypeScript s'exécute dans le navigateur.** Il est compilé en JavaScript avant l'exécution.
- **Utiliser `String`, `Number` ou `Boolean` avec majuscule.** Utilise les types primitifs en minuscules.
- **Sur-annoter.** Écrire `let nom: string = 'Awa'` est inutile ; l'inférence suffit.
- **Annoter sans valider les données externes.** Le type ne vérifie rien à l'exécution.
- **Ignorer les erreurs avec des raccourcis.** Utiliser `any` ou `// @ts-ignore` pour faire taire le compilateur supprime la protection.
- **Oublier de lancer la vérification.** L'éditeur montre les erreurs, mais seul `tsc --noEmit` (ou le build) les fait échouer en CI.
- **Penser que TypeScript rend le code plus lent.** Les types sont effacés : aucun coût à l'exécution.

## Bonnes pratiques

- Active l'affichage des erreurs dans l'éditeur et ne laisse jamais de souligné rouge.
- Annote les paramètres et retours des fonctions exportées : ce sont des contrats lisibles.
- Laisse l'inférence pour tout ce qui est évident.
- Lis les messages d'erreur en entier : ils proposent souvent la correction.
- Utilise `npx tsc --noEmit` dans tes scripts (`npm run typecheck`).
- Migre progressivement un projet JavaScript existant : renomme un fichier en `.ts`, corrige, avance.

## À retenir

- TypeScript = JavaScript + vérification statique des types.
- Le compilateur vérifie puis retire les types ; le navigateur n'exécute que du JavaScript.
- Les annotations s'écrivent après le nom : `let minutes: number = 90`.
- L'inférence déduit le type : annote surtout paramètres, retours et cas ambigus.
- Les erreurs de type sont détectées avant l'exécution, mais les données externes doivent être validées à l'exécution.
- Le Playground, `tsx` et `tsc --noEmit` suffisent pour bien démarrer.
