<?php

return [
    'lessons' => [
        'Découvrir TypeScript' => [
            'description' => 'Tu découvres ce qu\'apporte TypeScript à JavaScript : un système de types vérifié avant l\'exécution, et un compilateur qui produit du JavaScript.',
            'objective' => 'Installer TypeScript, compiler un fichier .ts, et corriger au moins trois erreurs de type signalées par le compilateur.',
            'content' => <<<'MD'
## Pourquoi cette notion

Imagine une application de caisse où une fonction attend un montant en nombre, mais reçoit le texte « 5000 » venu d'un formulaire. En JavaScript, le programme s'exécute et produit un résultat faux, souvent chez le client. TypeScript détecte ce genre d'erreur pendant que tu écris le code, avant même de lancer le programme.

En entreprise, TypeScript est devenu courant dans les projets de taille moyenne ou grande, car il rend le code plus lisible, facilite le travail à plusieurs et sécurise les modifications. Les frameworks comme Next.js et Angular l'utilisent en standard.

## Les concepts clés

### TypeScript est un sur-ensemble de JavaScript

Tout JavaScript valide est du TypeScript valide. Le langage ajoute des annotations de types et quelques constructions supplémentaires. Les navigateurs et Node.js ne comprennent pas TypeScript directement : le compilateur « tsc » vérifie les types puis produit un fichier JavaScript. Les types disparaissent à la compilation, ils n'existent donc qu'au moment du développement.

### Types annotés et types inférés

Tu peux annoter une variable avec « : string », « : number » ou « : boolean », par exemple « const prix: number = 2500 ». Souvent, TypeScript déduit le type tout seul grâce à l'inférence : « const prix = 2500 » est déjà un nombre. Annote surtout les paramètres de fonctions et les données qui viennent de l'extérieur.

### Les erreurs de type

Quand tu utilises une valeur d'une façon incompatible avec son type, le compilateur affiche une erreur avec un code comme « TS2322 » et un message qui décrit le problème. Ces erreurs sont des alliées : elles se lisent de bas en haut, la dernière ligne donnant souvent la cause précise. Ton éditeur les souligne en rouge en temps réel.

### Le type any

Le type « any » désactive la vérification pour une valeur. C'est une porte de sortie, pas une solution : chaque « any » est un endroit où les bugs peuvent revenir. Préfère « unknown » quand tu ne connais pas le type, car il t'oblige à vérifier la valeur avant de l'utiliser.

## Exemple pas à pas

L'exemple de code est un petit calcul de panier. À l'étape 1, on annote une constante et un tableau de nombres. À l'étape 2, la fonction « totalPanier » déclare le type de son paramètre et de son retour. À l'étape 3, l'inférence donne le type de la variable « total » sans annotation. À l'étape 4, des lignes mises en commentaire montrent des erreurs que le compilateur refuserait : affecter un texte à un nombre ou passer un mauvais argument. Pour l'essayer, installe TypeScript avec « npm install -D typescript », puis lance « npx tsc panier.ts » et exécute le fichier « panier.js » obtenu avec Node.js. Décommente ensuite une ligne fautive et lis l'erreur.

## Erreurs fréquentes

- Utiliser « any » partout pour faire taire le compilateur : tu perds tout l'intérêt du typage. Utilise un type précis ou « unknown » et vérifie la valeur.
- Croire que les types existent à l'exécution : une donnée venue d'une API peut ne pas respecter le type annoté. Valide les données externes avant de leur faire confiance.
- Lancer le fichier .ts directement avec Node.js sans outil adapté : cela échoue sur un environnement qui ne gère pas TypeScript. Compile avec « tsc » ou utilise un outil prévu pour cela.
- Ignorer le message d'erreur : on cherche au mauvais endroit. Lis le message complet, il nomme les deux types en conflit.

## Bonnes pratiques

- Laisse l'inférence travailler pour les variables locales évidentes.
- Annote les paramètres et les valeurs de retour des fonctions exportées.
- Corrige les erreurs de type au lieu de les masquer.
- Garde l'éditeur ouvert avec le support TypeScript activé pour voir les erreurs en direct.

## Auto-évaluation

- Que devient le code TypeScript après compilation ?
- Quelle est la différence entre un type annoté et un type inféré ?
- Pourquoi « unknown » est-il plus sûr que « any » ?
- Les types protègent-ils une donnée reçue d'une API à l'exécution ?

## À retenir

- TypeScript ajoute des types à JavaScript et se compile en JavaScript.
- Les types ne sont vérifiés qu'avant l'exécution.
- L'inférence évite d'annoter ce qui est évident.
- « any » désactive la sécurité, « unknown » la conserve.
- Les erreurs de type sont une aide précieuse.
MD,
            'code_example' => <<<'CODE'
// panier.ts : premier contact avec les types
// Compiler : npx tsc panier.ts   puis exécuter : node panier.js

// Étape 1 : types annotés explicitement
const boutique: string = "Chez Awa";
const prixArticles: number[] = [2500, 4000, 1500]; // en FCFA

// Étape 2 : le contrat de la fonction est explicite
function totalPanier(prix: number[], remise: number): number {
  const somme = prix.reduce((acc, p) => acc + p, 0);
  return somme - remise;
}

// Étape 3 : le type de total est inféré (number)
const total = totalPanier(prixArticles, 500);
console.log(`${boutique} : ${total} FCFA`);

// Étape 4 : ces lignes seraient refusées par le compilateur
// const quantite: number = "trois";        // TS2322 : string vers number
// totalPanier("2500", 500);                // TS2345 : mauvais argument
// console.log(total.toUpperCase());        // TS2339 : méthode inexistante

// unknown oblige à vérifier avant d'utiliser la valeur
function afficherMontant(valeur: unknown): string {
  if (typeof valeur === "number") {
    return `${valeur} FCFA`;
  }
  return "Montant invalide";
}
console.log(afficherMontant(total));
console.log(afficherMontant("abc"));
CODE,
            'estimated_minutes' => 40,
            'exercise_title' => 'Compiler et corriger des erreurs de type',
            'exercise_description' => <<<'TXT'
Crée un projet « decouverte-ts » avec TypeScript installé en dépendance de développement. Écris « facture.ts » puis compile-le avec « npx tsc facture.ts ».

Critères de réussite
- Le fichier déclare au moins une constante annotée avec « string » et un tableau annoté « number[] » de montants en FCFA.
- Une fonction « totalFacture(montants: number[]): number » est annotée sur son paramètre et son retour.
- Une fonction « afficher(valeur: unknown): string » utilise « typeof » pour traiter le cas nombre et le cas invalide.
- Le fichier contient trois lignes fautives en commentaire, avec le code d'erreur attendu (par exemple TS2322) pour chacune.
- La compilation du fichier sans les lignes fautives réussit et « node facture.js » affiche le total.
TXT,
            'exercise_hint' => 'Décommente une ligne fautive à la fois et lance tsc pour vérifier que le code d erreur correspond à ton commentaire, puis remets la ligne en commentaire.',
            'exercise_solution' => <<<'CODE'
// facture.ts
// Compiler : npx tsc facture.ts   Exécuter : node facture.js
const client: string = "Koffi";
const montants: number[] = [12000, 8500, 3000];

function totalFacture(montants: number[]): number {
  return montants.reduce((somme, m) => somme + m, 0);
}

function afficher(valeur: unknown): string {
  if (typeof valeur === "number") {
    return `${valeur} FCFA`;
  }
  return "Montant invalide";
}

const total = totalFacture(montants);
console.log(`Facture de ${client}`);
console.log(afficher(total));
console.log(afficher("douze mille"));

// Lignes fautives (à décommenter une par une pour tester)
// const age: number = "vingt";       // TS2322 : string non assignable à number
// totalFacture("12000");             // TS2345 : argument de mauvais type
// console.log(total.toUpperCase());  // TS2339 : propriété inexistante sur number
CODE,
        ],

        'Types primitifs et fonctions' => [
            'description' => 'Tu annotes variables, paramètres et valeurs de retour, et tu utilises les types de base, les tableaux, les tuples et les paramètres optionnels.',
            'objective' => 'Écrire des fonctions entièrement typées, avec paramètres optionnels et par défaut, qui compilent sans erreur en mode strict.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une fonction est un contrat : elle promet de transformer certaines entrées en une certaine sortie. En JavaScript, ce contrat n'existe que dans la tête du développeur ou dans un commentaire. En TypeScript, il est écrit dans le code et vérifié à chaque appel. Quand un collègue appelle ta fonction de calcul de frais mobile money, son éditeur lui indique tout de suite les arguments attendus et le type du résultat.

Les types de base et la signature des fonctions représentent l'essentiel du typage quotidien. Les maîtriser rend la suite beaucoup plus simple.

## Les concepts clés

### Les types de base

Les primitifs sont « string », « number », « boolean », « bigint » et « symbol », avec « null » et « undefined ». Un tableau s'écrit « number[] ». Un tuple est un tableau de longueur fixe avec un type par position, comme « [string, number] » pour un nom et un montant. Un type littéral restreint une valeur à des choix précis, par exemple « "ouvert" ou "ferme" ». Le type « void » désigne une fonction qui ne renvoie rien.

### Signature de fonction

Une signature se compose des types des paramètres et du type de retour : « function frais(montant: number, taux: number): number ». Un paramètre peut être optionnel avec « ? », par exemple « note?: string », et il vaut alors « undefined » s'il est absent. Un paramètre peut aussi avoir une valeur par défaut, et son type est alors inféré. Les paramètres restants s'écrivent « ...valeurs: number[] ».

### Types de fonctions et callbacks

Un type de fonction se décrit comme « (a: number, b: number) => number ». Il sert à typer les callbacks passés en argument. Quand tu passes une fonction à « map » ou « filter », TypeScript déduit le plus souvent les types des paramètres du callback à partir du tableau.

### Le mode strict et les valeurs absentes

Avec « strict » activé, « null » et « undefined » ne sont plus acceptés partout. Une valeur de type « string | undefined » doit être vérifiée avant utilisation. C'est une des protections les plus utiles du langage.

## Exemple pas à pas

L'exemple de code calcule des frais de transfert. À l'étape 1, on définit un type littéral pour l'opérateur. À l'étape 2, « calculerFrais » reçoit un montant, un taux avec valeur par défaut, et une note optionnelle. À l'étape 3, on traite le cas où la note est absente grâce à une vérification. À l'étape 4, un tuple renvoie à la fois le total et les frais. À l'étape 5, une fonction avec paramètres restants additionne un nombre quelconque de montants. À l'étape 6, un callback typé est passé à « map ». Compile avec « npx tsc --strict » pour vérifier que tout est cohérent.

## Erreurs fréquentes

- Oublier le type de retour sur une fonction exportée : l'inférence peut changer sans que tu le voies. Annote le retour pour fixer le contrat.
- Utiliser un paramètre optionnel sans vérifier « undefined » : le compilateur refuse l'opération. Teste la valeur ou donne un défaut.
- Mettre un paramètre optionnel avant un paramètre obligatoire : c'est une erreur de syntaxe. Place les optionnels à la fin.
- Confondre « number[] » et un tuple : un tableau accepte n'importe quelle longueur. Utilise un tuple quand la longueur et les positions sont connues.
- Annoter avec « Number » ou « String » en majuscule : ce sont des types objets à éviter. Utilise « number » et « string ».

## Bonnes pratiques

- Annote les paramètres, et le retour pour les fonctions publiques.
- Utilise les types littéraux plutôt que de simples « string » pour des valeurs en nombre limité.
- Préfère un paramètre par défaut à un paramètre optionnel quand une valeur raisonnable existe.
- Active « strict » dès le début d'un projet.

## Auto-évaluation

- Comment écrit-on le type d'un tableau de chaînes et celui d'un tuple nom-montant ?
- Quelle est la différence entre « void » et « undefined » ?
- Où placer un paramètre optionnel dans la liste ?
- Comment typer un callback qui reçoit un nombre et renvoie un booléen ?

## À retenir

- Une signature typée est un contrat vérifié par le compilateur.
- Les paramètres optionnels se terminent par « ? » et se placent en dernier.
- Les tuples fixent longueur et types par position.
- Les types littéraux limitent les valeurs possibles.
- Le mode strict protège contre « null » et « undefined ».
MD,
            'code_example' => <<<'CODE'
// frais.ts : fonctions typées (npx tsc --strict frais.ts && node frais.js)

// Étape 1 : type littéral limité à trois opérateurs
type Operateur = "wave" | "orange" | "mtn";

// Étape 2 : paramètre avec valeur par défaut et paramètre optionnel
function calculerFrais(montant: number, taux: number = 0.01, note?: string): number {
  const frais = Math.round(montant * taux);
  // Étape 3 : note peut être undefined, on vérifie avant de l'utiliser
  if (note !== undefined) {
    console.log(`Note : ${note.toUpperCase()}`);
  }
  return frais;
}

// Étape 4 : un tuple renvoie le total et les frais
function totalAvecFrais(montant: number, operateur: Operateur): [number, number] {
  const taux = operateur === "wave" ? 0.01 : 0.015;
  const frais = calculerFrais(montant, taux);
  return [montant + frais, frais];
}

// Étape 5 : paramètres restants
function additionner(...montants: number[]): number {
  return montants.reduce((somme, m) => somme + m, 0);
}

// Étape 6 : callback typé
function appliquer(valeurs: number[], regle: (v: number) => number): number[] {
  return valeurs.map(regle);
}

const [total, frais] = totalAvecFrais(20000, "wave");
console.log(total, frais); // 20200 200
console.log(additionner(1000, 2500, 500)); // 4000
console.log(appliquer([1000, 2000], (v) => v * 2)); // [2000, 4000]
console.log(calculerFrais(10000, undefined, "urgent")); // note puis 100
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Calculatrice de commande typée',
            'exercise_description' => <<<'TXT'
Crée « commande.ts » avec des fonctions typées pour une boutique en ligne et compile avec « npx tsc --strict commande.ts ».

Critères de réussite
- Un type littéral « Livraison » vaut « "standard" » ou « "express" ».
- « calculerLivraison(poids: number, mode: Livraison): number » renvoie 1000 FCFA pour standard et 2500 pour express, avec 500 FCFA ajoutés par kg au-delà de 5 kg.
- « totalCommande(sousTotal: number, remise: number = 0, code?: string): number » a un paramètre par défaut et un paramètre optionnel, et affiche le code s'il est fourni.
- Une fonction « resume(...articles: number[]): [number, number] » renvoie un tuple avec le nombre d'articles et leur somme.
- Le fichier compile en mode strict sans erreur et affiche des résultats avec console.log.
TXT,
            'exercise_hint' => 'Pour le poids excédentaire, utilise Math.max(0, poids - 5) puis multiplie par 500. Le paramètre optionnel code doit être testé avec !== undefined.',
            'exercise_solution' => <<<'CODE'
// commande.ts
type Livraison = "standard" | "express";

function calculerLivraison(poids: number, mode: Livraison): number {
  const base = mode === "standard" ? 1000 : 2500;
  const kgSupplementaires = Math.max(0, poids - 5);
  return base + kgSupplementaires * 500;
}

function totalCommande(sousTotal: number, remise: number = 0, code?: string): number {
  if (code !== undefined) {
    console.log(`Code promo appliqué : ${code}`);
  }
  return sousTotal - remise;
}

function resume(...articles: number[]): [number, number] {
  const somme = articles.reduce((total, a) => total + a, 0);
  return [articles.length, somme];
}

console.log(calculerLivraison(3, "standard")); // 1000
console.log(calculerLivraison(8, "express")); // 4000
console.log(totalCommande(15000)); // 15000
console.log(totalCommande(15000, 1500, "RENTREE")); // 13500
const [nombre, somme] = resume(2500, 4000, 1500);
console.log(nombre, somme); // 3 8000
CODE,
        ],

        'Interfaces et types' => [
            'description' => 'Tu modélises les objets métier avec les interfaces et les alias de types, et tu apprends à les combiner, les étendre et les rendre partiellement optionnels.',
            'objective' => 'Définir au moins trois modèles métier liés entre eux avec interface et type, et les utiliser dans des fonctions sans aucune erreur de type.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application manipule des entités : un client, une commande, un produit, un paiement. Chacune a une forme précise. Sans types, rien n'empêche d'oublier un champ ou de mal l'orthographier : « prix » devient « price » dans un fichier, et le bug apparaît chez l'utilisateur. En décrivant la forme des données une seule fois, tu obtiens l'autocomplétion, la détection des fautes de frappe et une documentation vivante.

Quand ton application reçoit des données d'une API, ces modèles deviennent la frontière entre le monde extérieur et ton code.

## Les concepts clés

### Interface

Une interface décrit la forme d'un objet : « interface Produit { id: number; nom: string; prix: number } ». Un objet est compatible s'il possède au moins ces propriétés avec les bons types. Une propriété peut être optionnelle avec « ? » ou en lecture seule avec « readonly ». Une interface peut en étendre une autre avec « extends », ce qui évite de répéter des champs.

### Alias de type

Un alias se déclare avec « type » et peut nommer presque n'importe quel type : un objet, une union, un tuple. Par exemple « type Statut = "paye" | "en_attente" ». Pour décrire des objets, interface et type sont très proches. En pratique, utilise « interface » pour les formes d'objets susceptibles d'être étendues, et « type » pour les unions et les types composés.

### Combiner et dériver des types

Les types peuvent s'assembler. L'intersection « A & B » crée un type qui possède les propriétés des deux. Les types utilitaires évitent de tout réécrire : « Partial<Produit> » rend tous les champs optionnels, utile pour une mise à jour. « Pick<Produit, "id" | "nom"> » garde seulement certains champs, « Omit<Produit, "id"> » en retire, et « Record » décrit un dictionnaire.

### Vérification structurelle

TypeScript compare les formes, pas les noms. Deux types qui ont les mêmes propriétés sont compatibles. Lors de l'affectation d'un objet littéral, le compilateur signale aussi les propriétés en trop, ce qui attrape les fautes de frappe.

## Exemple pas à pas

L'exemple de code modélise une commande de boutique. À l'étape 1, l'interface « Client » décrit un client avec un champ optionnel. À l'étape 2, « Article » utilise « readonly » pour l'identifiant. À l'étape 3, « Commande » combine un client, une liste d'articles et un statut défini par un alias de type. À l'étape 4, « Livraison » étend « Commande » avec une adresse. À l'étape 5, « MiseAJour » est dérivé avec « Partial » et « Pick ». À l'étape 6, des fonctions typées calculent le total et appliquent une mise à jour sans modifier l'original. Si tu changes un nom de propriété, le compilateur indique tous les endroits à corriger.

## Erreurs fréquentes

- Écrire un objet qui ne respecte pas l'interface : le compilateur signale une propriété manquante ou en trop. Complète l'objet ou rends le champ optionnel s'il l'est vraiment.
- Rendre tout optionnel pour éviter les erreurs : tu perds les garanties. N'utilise « ? » que pour les champs réellement facultatifs.
- Modifier une propriété « readonly » : le compilateur refuse. Crée un nouvel objet avec la valeur changée.
- Dupliquer les mêmes champs dans plusieurs interfaces : les modèles divergent avec le temps. Utilise « extends » ou les types utilitaires.
- Croire qu'une interface valide les données à l'exécution : elle disparaît à la compilation. Valide les données d'une API avant de les typer.

## Bonnes pratiques

- Nomme les types au singulier et avec un nom métier : « Commande », « Produit ».
- Centralise les modèles dans un dossier « types » pour les partager.
- Dérive les variantes avec « Partial », « Pick » et « Omit » plutôt que de les copier.
- Utilise des unions de littéraux pour les statuts au lieu de simples « string ».

## Auto-évaluation

- Comment rendre une propriété optionnelle et une autre en lecture seule ?
- Quand préfères-tu « type » à « interface » ?
- Que produit « Partial<Produit> » et dans quel cas l'utilises-tu ?
- Pourquoi une interface ne protège-t-elle pas contre une mauvaise réponse d'API à l'exécution ?

## À retenir

- Une interface décrit la forme d'un objet métier.
- « extends » et « & » combinent des types sans répétition.
- Les types utilitaires dérivent des variantes à partir d'un modèle.
- TypeScript vérifie la structure, pas le nom du type.
- Les modèles sont une documentation vérifiée par le compilateur.
MD,
            'code_example' => <<<'CODE'
// commande.ts : modèles métier (npx tsc --strict commande.ts)

// Étape 1 : interface avec une propriété optionnelle
interface Client {
  nom: string;
  telephone: string;
  email?: string;
}

// Étape 2 : identifiant en lecture seule
interface Article {
  readonly id: number;
  nom: string;
  prix: number; // FCFA
  quantite: number;
}

// Étape 3 : alias pour le statut, interface pour la commande
type Statut = "en_attente" | "payee" | "livree";

interface Commande {
  id: number;
  client: Client;
  articles: Article[];
  statut: Statut;
}

// Étape 4 : extension d'une interface existante
interface Livraison extends Commande {
  adresse: string;
}

// Étape 5 : types dérivés
type MiseAJour = Partial<Pick<Commande, "statut" | "articles">>;

// Étape 6 : fonctions typées, sans modifier l'original
function total(commande: Commande): number {
  return commande.articles.reduce((somme, a) => somme + a.prix * a.quantite, 0);
}

function mettreAJour(commande: Commande, changes: MiseAJour): Commande {
  return { ...commande, ...changes };
}

const commande: Commande = {
  id: 1,
  client: { nom: "Awa Traoré", telephone: "0102030405" },
  articles: [{ id: 10, nom: "Pagne", prix: 7500, quantite: 2 }],
  statut: "en_attente",
};

const livraison: Livraison = { ...commande, adresse: "Cocody, Abidjan" };
const payee = mettreAJour(commande, { statut: "payee" });
console.log(total(commande), payee.statut, livraison.adresse); // 15000 payee Cocody, Abidjan
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Modéliser une réservation d\'hôtel',
            'exercise_description' => <<<'TXT'
Crée « reservation.ts » qui modélise des réservations pour une petite résidence hôtelière, puis compile en mode strict.

Critères de réussite
- Une interface « Chambre » a un « id » en lecture seule, un « numero », un « prixNuit » en FCFA et un champ optionnel « vue ».
- Un alias « StatutReservation » est une union de trois littéraux (« confirmee », « annulee », « terminee »).
- Une interface « Reservation » contient une chambre, un nom de client, un nombre de nuits et un statut, et une interface « ReservationAvecAcompte » l'étend avec un champ « acompte ».
- Un type « ModificationReservation » est dérivé avec « Partial » et « Pick » à partir de « Reservation ».
- Les fonctions « montantTotal(r: Reservation): number » et « modifier(r: Reservation, m: ModificationReservation): Reservation » compilent en mode strict, la seconde sans muter l'original.
TXT,
            'exercise_hint' => 'Pour modifier sans muter, renvoie un nouvel objet avec { ...r, ...m }. Le montant total est le prix de la nuit multiplié par le nombre de nuits.',
            'exercise_solution' => <<<'CODE'
// reservation.ts
interface Chambre {
  readonly id: number;
  numero: string;
  prixNuit: number;
  vue?: string;
}

type StatutReservation = "confirmee" | "annulee" | "terminee";

interface Reservation {
  chambre: Chambre;
  client: string;
  nuits: number;
  statut: StatutReservation;
}

interface ReservationAvecAcompte extends Reservation {
  acompte: number;
}

type ModificationReservation = Partial<Pick<Reservation, "nuits" | "statut">>;

function montantTotal(r: Reservation): number {
  return r.chambre.prixNuit * r.nuits;
}

function modifier(r: Reservation, m: ModificationReservation): Reservation {
  return { ...r, ...m };
}

const chambre: Chambre = { id: 1, numero: "204", prixNuit: 35000, vue: "lagune" };
const reservation: Reservation = { chambre, client: "Koffi Yao", nuits: 2, statut: "confirmee" };
const avecAcompte: ReservationAvecAcompte = { ...reservation, acompte: 20000 };
const prolongee = modifier(reservation, { nuits: 3 });

console.log(montantTotal(reservation)); // 70000
console.log(montantTotal(prolongee)); // 105000
console.log(avecAcompte.acompte, reservation.nuits); // 20000 2
CODE,
        ],

        'Generics et unions' => [
            'description' => 'Tu utilises les unions pour exprimer plusieurs états possibles, le narrowing pour les traiter, et les generics pour écrire du code réutilisable sans perdre les types.',
            'objective' => 'Écrire une union discriminée avec traitement exhaustif, et au moins deux fonctions ou types génériques réutilisables.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans une application réelle, une donnée n'est pas toujours dans le même état. Une requête réseau est en chargement, réussie ou en erreur. Un paiement est en attente, validé ou refusé. Représenter cela avec des champs optionnels éparpillés mène à des états impossibles, comme une réussite avec une erreur. Les unions modélisent précisément les états autorisés.

Les generics répondent à un autre besoin : écrire une fonction ou une structure une seule fois pour plusieurs types, par exemple une fonction qui renvoie le premier élément de n'importe quelle liste, sans perdre l'information sur le type de cet élément.

## Les concepts clés

### Unions et narrowing

Un type union s'écrit avec « | » : « string | number » accepte l'un ou l'autre. Pour utiliser la valeur, tu dois d'abord réduire le type, c'est le narrowing. Les outils sont « typeof », « instanceof », l'opérateur « in » et les comparaisons d'égalité. Dans un bloc « if (typeof v === "string") », TypeScript sait que « v » est une chaîne.

### Unions discriminées

Une union discriminée est une union d'objets qui partagent une propriété littérale commune, appelée discriminant. Par exemple, un état avec « statut: "chargement" », « statut: "succes" » avec des données, ou « statut: "erreur" » avec un message. Un « switch » sur le discriminant donne accès aux bonnes propriétés dans chaque cas. Pour garantir que tous les cas sont traités, on ajoute un cas par défaut qui affecte la valeur à une variable de type « never » : si tu ajoutes un nouvel état et oublies de le gérer, le compilateur signale l'erreur.

### Generics

Un generic est un paramètre de type, écrit entre chevrons : « function premier<T>(liste: T[]): T | undefined ». Quand tu appelles « premier([1, 2, 3]) », TypeScript déduit que « T » vaut « number ».

### Contraintes

Avec « extends », tu limites les types acceptés : « <T extends { id: number }> » exige que « T » possède un « id ». Tu peux alors utiliser « id » dans la fonction tout en gardant le type précis de l'objet.

## Exemple pas à pas

L'exemple de code gère l'état d'un chargement de produits. À l'étape 1, un type générique « Etat<T> » est une union discriminée sur « statut ». À l'étape 2, la fonction « afficher » utilise un « switch » pour traiter chaque cas, avec une vérification d'exhaustivité grâce à « never ». À l'étape 3, la fonction « premier » est générique. À l'étape 4, « trouverParId » utilise une contrainte « extends » pour fonctionner avec n'importe quel objet qui possède un « id ». À l'étape 5, on appelle ces fonctions avec des types différents et TypeScript déduit chaque fois le bon type de résultat.

## Erreurs fréquentes

- Accéder à une propriété sans réduire l'union : le compilateur refuse. Fais un narrowing avec « typeof », « in » ou un « switch » sur le discriminant.
- Utiliser « any » au lieu d'un generic : le type du résultat est perdu. Écris « <T> » pour conserver la relation entre entrée et sortie.
- Oublier le cas par défaut exhaustif : un nouvel état ajouté plus tard n'est pas géré sans alerte. Utilise la variable de type « never » pour forcer l'erreur.
- Utiliser un generic inutile : « function f<T>(x: number) » n'utilise jamais « T ». Ajoute un paramètre de type seulement s'il relie plusieurs types.
- Utiliser un discriminant de type « string » au lieu d'un littéral : le narrowing ne fonctionne pas. Écris des littéraux comme « "succes" ».

## Bonnes pratiques

- Modélise les états avec une union discriminée plutôt qu'avec des booléens et des champs optionnels.
- Garde des noms de paramètres de type clairs, « T » pour un seul, « TDonnees » ou « Item » sinon.
- Ajoute des contraintes pour décrire ce que la fonction exige.
- Vérifie l'exhaustivité des « switch » avec « never ».

## Auto-évaluation

- Qu'est-ce que le narrowing et quels outils le permettent ?
- Qu'est-ce qu'un discriminant et pourquoi doit-il être un littéral ?
- Que signifie « T » dans « function premier<T>(liste: T[]) » ?
- À quoi sert « extends » dans un generic ?

## À retenir

- Une union exprime plusieurs types ou états possibles.
- Le narrowing réduit une union avant utilisation.
- Une union discriminée avec « switch » rend les états explicites.
- Un generic conserve le type entre entrée et sortie.
- Les contraintes décrivent ce qu'un type générique doit posséder.
MD,
            'code_example' => <<<'CODE'
// etat.ts : unions discriminées et generics (npx tsc --strict etat.ts)

interface Produit {
  id: number;
  nom: string;
  prix: number;
}

// Étape 1 : état générique, chaque cas porte un littéral discriminant
type Etat<T> =
  | { statut: "chargement" }
  | { statut: "succes"; donnees: T }
  | { statut: "erreur"; message: string };

// Étape 2 : switch exhaustif grâce à never
function afficher(etat: Etat<Produit[]>): string {
  switch (etat.statut) {
    case "chargement":
      return "Chargement des produits...";
    case "succes":
      return `${etat.donnees.length} produit(s) disponible(s)`;
    case "erreur":
      return `Erreur : ${etat.message}`;
    default: {
      const inattendu: never = etat; // erreur si un cas est oublié
      return inattendu;
    }
  }
}

// Étape 3 : fonction générique simple
function premier<T>(liste: T[]): T | undefined {
  return liste[0];
}

// Étape 4 : generic avec contrainte
function trouverParId<T extends { id: number }>(liste: T[], id: number): T | undefined {
  return liste.find((element) => element.id === id);
}

// Étape 5 : utilisation, les types sont déduits
const produits: Produit[] = [
  { id: 1, nom: "Riz", prix: 3500 },
  { id: 2, nom: "Huile", prix: 1500 },
];

console.log(afficher({ statut: "chargement" }));
console.log(afficher({ statut: "succes", donnees: produits }));
console.log(afficher({ statut: "erreur", message: "Réseau indisponible" }));
console.log(premier(produits)?.nom); // Riz
console.log(premier([10, 20, 30])); // 10
console.log(trouverParId(produits, 2)?.prix); // 1500
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Statut de paiement et utilitaires génériques',
            'exercise_description' => <<<'TXT'
Crée « paiement.ts » pour une application de paiement mobile money et compile avec « npx tsc --strict paiement.ts ».

Critères de réussite
- Un type « Paiement » est une union discriminée sur « statut » avec trois cas : « attente », « valide » (avec un champ « reference: string ») et « refuse » (avec un champ « motif: string »).
- Une fonction « message(p: Paiement): string » utilise un « switch » exhaustif avec une variable de type « never » dans le cas par défaut.
- Une fonction générique « dernier<T>(liste: T[]): T | undefined » renvoie le dernier élément d'une liste.
- Une fonction « grouperParId<T extends { id: number }>(liste: T[]): Record<number, T> » transforme une liste en dictionnaire indexé par « id ».
- Le fichier compile sans erreur et affiche un message pour chacun des trois statuts, ainsi que le résultat des fonctions génériques.
TXT,
            'exercise_hint' => 'Pour grouperParId, crée un objet vide typé Record<number, T> et remplis-le avec une boucle for...of. Pour dernier, utilise liste[liste.length - 1].',
            'exercise_solution' => <<<'CODE'
// paiement.ts
type Paiement =
  | { statut: "attente" }
  | { statut: "valide"; reference: string }
  | { statut: "refuse"; motif: string };

function message(p: Paiement): string {
  switch (p.statut) {
    case "attente":
      return "Paiement en attente de confirmation";
    case "valide":
      return `Paiement validé, référence ${p.reference}`;
    case "refuse":
      return `Paiement refusé : ${p.motif}`;
    default: {
      const inattendu: never = p;
      return inattendu;
    }
  }
}

function dernier<T>(liste: T[]): T | undefined {
  return liste[liste.length - 1];
}

function grouperParId<T extends { id: number }>(liste: T[]): Record<number, T> {
  const resultat: Record<number, T> = {};
  for (const element of liste) {
    resultat[element.id] = element;
  }
  return resultat;
}

console.log(message({ statut: "attente" }));
console.log(message({ statut: "valide", reference: "TX-2026-001" }));
console.log(message({ statut: "refuse", motif: "Solde insuffisant" }));

console.log(dernier([5, 10, 15])); // 15
const clients = [
  { id: 1, nom: "Awa" },
  { id: 2, nom: "Koffi" },
];
console.log(grouperParId(clients)[2]?.nom); // Koffi
CODE,
        ],

        'TypeScript avec React' => [
            'description' => 'Tu types les props, le state, les événements et les réponses d\'API dans des composants React, pour éviter les erreurs courantes dans les interfaces.',
            'objective' => 'Écrire un composant React en TSX avec des props typées, un state typé, un gestionnaire d\'événement typé et une liste de données issues d\'une API typée.',
            'content' => <<<'MD'
## Pourquoi cette notion

React et TypeScript forment un couple très répandu. Dans une interface, les erreurs classiques sont l'oubli d'une prop, le passage d'une valeur du mauvais type, l'accès à une donnée qui n'existe pas encore et la confusion entre les états de chargement. TypeScript attrape la plupart de ces erreurs dans l'éditeur, pendant que tu construis le composant.

Les types servent aussi de documentation : en lisant la définition des props d'un composant, tout le monde comprend comment l'utiliser, sans ouvrir son code. C'est précieux dans une équipe ou sur un projet que l'on reprend après plusieurs mois.

## Les concepts clés

### Typer les props

Les props se décrivent avec une interface ou un alias, puis s'appliquent au paramètre du composant : « function CarteProduit({ nom, prix }: Props) ». Une prop optionnelle utilise « ? », et une prop de type fonction se décrit comme « onAjouter: (id: number) => void ». Les enfants d'un composant se typent avec « ReactNode ». Les fichiers qui contiennent du JSX ont l'extension « .tsx ».

### Typer le state

Avec « useState », TypeScript déduit le type de la valeur initiale : « useState(0) » donne un nombre. Quand la valeur initiale ne suffit pas, par exemple une liste vide ou une valeur nulle, précise le type avec un generic : « useState<Produit[]>([]) » ou « useState<Produit | null>(null) ». Pour un état complexe, réutilise l'union discriminée vue dans la leçon précédente.

### Typer les événements

React fournit des types pour les événements : « ChangeEvent<HTMLInputElement> » pour la saisie dans un champ, « FormEvent<HTMLFormElement> » pour un formulaire, « MouseEvent<HTMLButtonElement> » pour un clic. Avec un gestionnaire écrit directement dans le JSX, le type est souvent déduit. Quand tu l'extrais dans une fonction, tu dois l'annoter toi-même.

### Typer les données d'API

La réponse d'une API arrive en type « any » ou « unknown ». Décris la forme attendue avec une interface et indique-la au moment de l'utiliser. Rappelle-toi que ce type est une promesse, pas une preuve : le serveur peut renvoyer autre chose. Vérifie le statut HTTP et valide les champs critiques avant de t'y fier.

## Exemple pas à pas

L'exemple de code est une liste de produits avec recherche. À l'étape 1, l'interface « Produit » décrit les données de l'API. À l'étape 2, le composant « CarteProduit » reçoit des props typées, dont un callback. À l'étape 3, le composant « Catalogue » déclare le state des produits, de la recherche et de l'erreur avec des generics. À l'étape 4, un effet charge les données avec une fonction typée. À l'étape 5, le gestionnaire « onChange » est typé avec « ChangeEvent ». À l'étape 6, le rendu filtre et affiche la liste, en gérant le chargement et l'erreur. Ce fichier s'intègre dans un projet créé avec un modèle React et TypeScript.

## Erreurs fréquentes

- Laisser « useState([]) » sans generic : TypeScript déduit « never[] » et refuse l'ajout d'éléments. Écris « useState<Produit[]>([]) ».
- Accéder à une valeur potentiellement nulle : « utilisateur.nom » échoue si l'état peut être « null ». Vérifie d'abord ou utilise l'optional chaining « utilisateur?.nom ».
- Typer un événement avec « any » : tu perds l'autocomplétion. Utilise « ChangeEvent<HTMLInputElement> » et lis « event.target.value ».
- Croire qu'un type d'API garantit les données : un champ peut manquer. Valide les champs critiques et gère les erreurs HTTP.
- Dupliquer le type dans chaque composant : les copies divergent. Place les modèles dans un fichier partagé et importe-les.

## Bonnes pratiques

- Définis un type « Props » pour chaque composant, même petit.
- Partage les modèles d'API dans un dossier « types » importé par les composants et les services.
- Utilise une union discriminée pour l'état de chargement, de succès et d'erreur.
- Laisse l'inférence gérer les cas simples et annote les cas ambigus.

## Auto-évaluation

- Comment tape-t-on une prop de type fonction qui reçoit un identifiant ?
- Pourquoi « useState<Produit[]>([]) » a-t-il besoin du generic ?
- Quel type utilises-tu pour un champ de saisie contrôlé ?
- Pourquoi un type d'API ne remplace-t-il pas une validation ?

## À retenir

- Les props se typent avec une interface, les enfants avec « ReactNode ».
- « useState » accepte un generic quand la valeur initiale est ambiguë.
- React fournit des types d'événements précis.
- Un type d'API décrit une attente, pas une garantie.
- Les modèles partagés évitent les doublons de types.
MD,
            'code_example' => <<<'CODE'
// Catalogue.tsx : composant React typé
import { useEffect, useState, type ChangeEvent } from "react";

// Étape 1 : forme des données de l'API (partagée dans types/produit.ts)
interface Produit {
  id: number;
  nom: string;
  prix: number; // FCFA
}

// Étape 2 : props typées, dont un callback
interface CarteProduitProps {
  produit: Produit;
  onAjouter: (id: number) => void;
}

function CarteProduit({ produit, onAjouter }: CarteProduitProps) {
  return (
    <article>
      <h3>{produit.nom}</h3>
      <p>{produit.prix} FCFA</p>
      <button onClick={() => onAjouter(produit.id)}>Ajouter</button>
    </article>
  );
}

// Étape 4 : fonction de chargement typée, avec test du statut HTTP
async function chargerProduits(): Promise<Produit[]> {
  const reponse = await fetch("/api/produits");
  if (!reponse.ok) {
    throw new Error(`Erreur HTTP ${reponse.status}`);
  }
  return (await reponse.json()) as Produit[];
}

export default function Catalogue() {
  // Étape 3 : state typé avec des generics
  const [produits, setProduits] = useState<Produit[]>([]);
  const [recherche, setRecherche] = useState<string>("");
  const [erreur, setErreur] = useState<string | null>(null);

  useEffect(() => {
    chargerProduits()
      .then(setProduits)
      .catch((e: Error) => setErreur(e.message));
  }, []);

  // Étape 5 : événement typé
  const onChange = (event: ChangeEvent<HTMLInputElement>) => {
    setRecherche(event.target.value);
  };

  // Étape 6 : rendu avec gestion de l'erreur
  if (erreur !== null) return <p>Erreur : {erreur}</p>;
  const visibles = produits.filter((p) =>
    p.nom.toLowerCase().includes(recherche.toLowerCase())
  );

  return (
    <section>
      <input value={recherche} onChange={onChange} placeholder="Rechercher" />
      {visibles.map((p) => (
        <CarteProduit key={p.id} produit={p} onAjouter={(id) => console.log("Ajout", id)} />
      ))}
    </section>
  );
}
CODE,
            'estimated_minutes' => 75,
            'exercise_title' => 'Liste de commandes typée en React',
            'exercise_description' => <<<'TXT'
Dans un projet React avec TypeScript, crée « ListeCommandes.tsx » qui affiche des commandes de livraison filtrables par statut.

Critères de réussite
- Une interface « Commande » décrit « id », « client », « montant » et « statut » (union de littéraux « en_cours » et « livree »).
- Un composant « LigneCommande » reçoit des props typées avec une interface, dont un callback « onBasculer: (id: number) => void ».
- Le composant principal utilise « useState<Commande[]> » pour la liste et « useState<"toutes" | Commande["statut"]> » pour le filtre.
- Un « select » de filtre utilise un gestionnaire typé avec « ChangeEvent<HTMLSelectElement> ».
- Le projet compile avec « npx tsc --noEmit » sans erreur et le clic bascule le statut d'une commande sans muter l'état.
TXT,
            'exercise_hint' => 'Pour la valeur du select, convertis avec un as typé ou vérifie qu elle est bien une des trois valeurs. Pour basculer, utilise map et un nouvel objet pour la commande ciblée.',
            'exercise_solution' => <<<'CODE'
// ListeCommandes.tsx
import { useState, type ChangeEvent } from "react";

type Statut = "en_cours" | "livree";

interface Commande {
  id: number;
  client: string;
  montant: number; // FCFA
  statut: Statut;
}

interface LigneCommandeProps {
  commande: Commande;
  onBasculer: (id: number) => void;
}

function LigneCommande({ commande, onBasculer }: LigneCommandeProps) {
  return (
    <li>
      {commande.client} : {commande.montant} FCFA ({commande.statut}){" "}
      <button onClick={() => onBasculer(commande.id)}>Changer le statut</button>
    </li>
  );
}

type Filtre = "toutes" | Statut;

export default function ListeCommandes() {
  const [commandes, setCommandes] = useState<Commande[]>([
    { id: 1, client: "Awa", montant: 12000, statut: "en_cours" },
    { id: 2, client: "Koffi", montant: 8500, statut: "livree" },
    { id: 3, client: "Mariam", montant: 23000, statut: "en_cours" },
  ]);
  const [filtre, setFiltre] = useState<Filtre>("toutes");

  const onFiltre = (event: ChangeEvent<HTMLSelectElement>) => {
    setFiltre(event.target.value as Filtre);
  };

  const basculer = (id: number) => {
    setCommandes((liste) =>
      liste.map((c) =>
        c.id === id ? { ...c, statut: c.statut === "livree" ? "en_cours" : "livree" } : c
      )
    );
  };

  const visibles = commandes.filter((c) => filtre === "toutes" || c.statut === filtre);

  return (
    <section>
      <select value={filtre} onChange={onFiltre}>
        <option value="toutes">Toutes</option>
        <option value="en_cours">En cours</option>
        <option value="livree">Livrées</option>
      </select>
      <ul>
        {visibles.map((c) => (
          <LigneCommande key={c.id} commande={c} onBasculer={basculer} />
        ))}
      </ul>
    </section>
  );
}
CODE,
        ],

        'Configuration et qualité' => [
            'description' => 'Tu comprends le fichier tsconfig, le mode strict et ses options importantes, et tu mets en place un linter pour garder un projet TypeScript fiable.',
            'objective' => 'Configurer un projet avec un tsconfig strict, corriger les erreurs que le mode strict révèle, et lancer un contrôle de types et un linter en une commande.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet TypeScript n'est pas seulement du code : c'est aussi une configuration qui décide à quel point le compilateur est exigeant. Un projet sans mode strict laisse passer des erreurs qu'il aurait pu attraper, et le gain du typage est alors faible. À l'inverse, une configuration bien choisie devient un filet de sécurité pour toute l'équipe.

Quand tu rejoins un projet ou démarres le tien, la première chose à regarder est le fichier « tsconfig.json ». Il est la référence pour l'éditeur, le compilateur et les outils de build. Avec un linter et un script de vérification, tu obtiens un contrôle automatique avant chaque envoi de code.

## Les concepts clés

### Le fichier tsconfig.json

Ce fichier à la racine du projet décrit comment compiler. La section « compilerOptions » contient les réglages principaux : « target » pour la version de JavaScript produite, « module » pour le système de modules, « outDir » pour le dossier de sortie, « rootDir » pour les sources et « include » pour les fichiers pris en compte. Tu peux en générer un avec « npx tsc --init ».

### Le mode strict

L'option « strict » active un ensemble de vérifications. Parmi elles, « noImplicitAny » refuse les types devinés comme « any », « strictNullChecks » traite « null » et « undefined » comme des valeurs à vérifier, et « strictFunctionTypes » contrôle plus finement les fonctions passées en argument. Pour un nouveau projet, active « strict » dès le départ. Pour un projet existant, active les options une par une et corrige progressivement.

### Autres options utiles

« noUnusedLocals » et « noUnusedParameters » signalent le code mort. « noImplicitReturns » vérifie que chaque chemin d'une fonction renvoie une valeur. « noUncheckedIndexedAccess » ajoute « undefined » aux accès par indice, ce qui évite des erreurs sur les tableaux.

### Linting et vérification

Le compilateur vérifie les types, le linter vérifie la qualité et le style. Un outil courant est ESLint avec son support TypeScript. On ajoute dans « package.json » des scripts comme « typecheck » avec « tsc --noEmit » et « lint ». Ils se lancent à la main, dans l'éditeur ou dans une étape automatique avant la fusion du code.

## Exemple pas à pas

L'exemple de code montre un fichier « tsconfig.json » en commentaire, puis du code qui serait refusé ou accepté selon les options. À l'étape 1, on lit la configuration stricte et le rôle de chaque option. À l'étape 2, une fonction sans annotation illustre « noImplicitAny ». À l'étape 3, un accès à une valeur possiblement absente illustre « strictNullChecks ». À l'étape 4, l'accès par indice illustre « noUncheckedIndexedAccess ». À l'étape 5, la version corrigée de chaque cas est donnée. Copie le tsconfig dans un projet, puis lance « npx tsc --noEmit » pour voir les erreurs.

## Erreurs fréquentes

- Désactiver « strict » pour faire disparaître les erreurs : elles reviennent sous forme de bugs en production. Corrige le code, ne désactive pas la vérification.
- Utiliser l'assertion non nulle « ! » partout : tu promets au compilateur que la valeur existe, sans vérification. Teste la valeur ou utilise « ?. » et une valeur par défaut.
- Utiliser des assertions « as » pour forcer un type : tu masques un vrai problème. Préfère le narrowing ou une validation.
- Mal régler « include » : certains fichiers ne sont pas vérifiés sans que tu le voies. Vérifie avec « tsc --listFiles ».
- Ignorer les erreurs avec « @ts-ignore » : le problème reste. Utilise « @ts-expect-error » avec un commentaire, qui alerte quand l'erreur disparaît.

## Bonnes pratiques

- Active « strict » dès la création du projet.
- Ajoute un script « typecheck » et lance-le avant chaque envoi.
- Combine le compilateur et un linter : l'un contrôle les types, l'autre la qualité.
- Documente les raisons de toute exception à la configuration.

## Auto-évaluation

- Que contient l'option « strict » et quelles sont deux de ses vérifications ?
- À quoi sert « tsc --noEmit » ?
- Pourquoi éviter « ! » et « as » pour contourner une erreur ?
- Comment migrer progressivement un projet JavaScript vers le mode strict ?

## À retenir

- Le fichier tsconfig.json règle l'exigence du compilateur.
- « strict » doit être activé dans tout nouveau projet.
- « noImplicitAny » et « strictNullChecks » préviennent beaucoup de bugs.
- Un script de vérification de types et un linter automatisent le contrôle.
- On corrige les erreurs au lieu de les contourner.
MD,
            'code_example' => <<<'CODE'
// strict.ts : effet du mode strict (npx tsc --noEmit)

// Étape 1 : tsconfig.json recommandé à la racine du projet
// {
//   "compilerOptions": {
//     "target": "ES2022",
//     "module": "ESNext",
//     "moduleResolution": "Bundler",
//     "strict": true,
//     "noUnusedLocals": true,
//     "noImplicitReturns": true,
//     "noUncheckedIndexedAccess": true,
//     "skipLibCheck": true
//   },
//   "include": ["src"]
// }

// Étape 2 : noImplicitAny refuse un paramètre sans type
// function doubler(valeur) { return valeur * 2; }   // erreur : type any implicite
function doubler(valeur: number): number {
  return valeur * 2;
}

// Étape 3 : strictNullChecks, la valeur peut être absente
const telephones = new Map<string, string>([["Awa", "0102030405"]]);
// const numero: string = telephones.get("Koffi"); // erreur : string | undefined
const numero: string = telephones.get("Koffi") ?? "Non renseigné";

// Étape 4 : noUncheckedIndexedAccess, un indice peut être hors tableau
const prix: number[] = [2500, 4000];
// const premier: number = prix[5];                // erreur : number | undefined
const premier = prix[0];
if (premier !== undefined) {
  console.log(doubler(premier)); // 5000
}

// Étape 5 : version corrigée et lisible du cas null
function afficherTelephone(nom: string): string {
  const tel = telephones.get(nom);
  if (tel === undefined) {
    return `Aucun numéro pour ${nom}`;
  }
  return `${nom} : ${tel}`;
}

console.log(numero); // Non renseigné
console.log(afficherTelephone("Awa"));
console.log(afficherTelephone("Koffi"));
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Migrer un fichier vers le mode strict',
            'exercise_description' => <<<'TXT'
Crée un dossier avec « package.json », TypeScript installé, un « tsconfig.json » strict et un fichier « src/stock.ts ». Le fichier de départ contient volontairement des erreurs que le mode strict révèle, et tu dois toutes les corriger.

Critères de réussite
- Le « tsconfig.json » active « strict », « noUnusedLocals », « noImplicitReturns » et « noUncheckedIndexedAccess », et inclut le dossier « src ».
- Un script « typecheck » dans « package.json » lance « tsc --noEmit ».
- Aucune erreur ne reste à « npm run typecheck » et le fichier n'utilise ni « any », ni « ! », ni « @ts-ignore ».
- La fonction « quantiteEnStock(stock: Map<string, number>, produit: string): number » renvoie 0 quand le produit est absent.
- La fonction « premierProduit(noms: string[]): string » renvoie « Aucun produit » si la liste est vide.
TXT,
            'exercise_hint' => 'Pour les valeurs possiblement absentes, utilise l opérateur ?? avec une valeur par défaut, ou vérifie undefined dans un if avant d utiliser la valeur.',
            'exercise_solution' => <<<'CODE'
// ===== package.json (extrait) =====
// {
//   "scripts": { "typecheck": "tsc --noEmit" },
//   "devDependencies": { "typescript": "^5.0.0" }
// }

// ===== tsconfig.json =====
// {
//   "compilerOptions": {
//     "target": "ES2022",
//     "module": "ESNext",
//     "moduleResolution": "Bundler",
//     "strict": true,
//     "noUnusedLocals": true,
//     "noImplicitReturns": true,
//     "noUncheckedIndexedAccess": true,
//     "skipLibCheck": true
//   },
//   "include": ["src"]
// }

// ===== src/stock.ts =====
export function quantiteEnStock(stock: Map<string, number>, produit: string): number {
  return stock.get(produit) ?? 0;
}

export function premierProduit(noms: string[]): string {
  const premier = noms[0];
  if (premier === undefined) {
    return "Aucun produit";
  }
  return premier;
}

const stock = new Map<string, number>([
  ["Riz", 12],
  ["Huile", 0],
]);

console.log(quantiteEnStock(stock, "Riz")); // 12
console.log(quantiteEnStock(stock, "Sucre")); // 0
console.log(premierProduit(["Riz", "Huile"])); // Riz
console.log(premierProduit([])); // Aucun produit
CODE,
        ],

        'Projet final TypeScript' => [
            'description' => 'Tu construis une application de bout en bout avec TypeScript : modèles, accès aux données, validation, composants et états explicites.',
            'objective' => 'Livrer un mini dashboard React TypeScript en mode strict, avec des modèles partagés, une couche de données typée, une validation de formulaire et des états de chargement, de succès et d\'erreur.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet final montre que tu sais passer des notions isolées à une application cohérente. Dans le monde professionnel, personne ne te demande d'écrire une fonction typée seule : on attend une application dont les données, la logique et l'interface se parlent sans ambiguïté. Le typage de bout en bout signifie que la forme d'une donnée est définie une fois, puis suivie depuis l'API jusqu'à l'écran.

Ce type de projet est aussi un excellent élément de portfolio : un tableau de bord de ventes pour une boutique, un suivi de livraisons ou une gestion de stock sont des cas concrets que des clients reconnaissent facilement.

## Les concepts clés

### Partir des modèles

Commence par définir les modèles dans un dossier « types » : les entités, leurs statuts, et les formes de réponse de l'API. Dérive les variantes avec les types utilitaires, par exemple le type d'un formulaire de création avec « Omit » pour retirer l'identifiant. Toute l'application s'appuie sur ces définitions.

### Une couche de données typée

Isole les appels réseau dans un dossier « services ». Chaque fonction a une signature explicite, comme « chargerVentes(): Promise<Vente[]> ». C'est là que tu testes « response.ok », convertis les erreurs en messages utiles et, idéalement, valides les données reçues. Les composants ne connaissent pas les détails de fetch, ils appellent seulement ces fonctions.

### Validation aux frontières

Les types ne protègent pas contre les données externes. À chaque frontière, par exemple une réponse d'API ou la saisie d'un formulaire, vérifie les valeurs. Une fonction de validation peut renvoyer un résultat sous forme d'union discriminée : soit « ok » avec la donnée typée, soit « erreurs » avec la liste des messages. Une bibliothèque de validation peut aider, mais une fonction manuelle suffit pour un petit projet.

### États explicites

Modélise chaque écran avec une union discriminée « chargement », « succes » ou « erreur », et gère les trois cas dans le rendu. L'état vide, quand la liste est vide, mérite aussi un message clair. Ainsi, aucun état impossible ne peut apparaître.

## Exemple pas à pas

L'exemple de code représente le cœur du projet, en TypeScript pur pour rester exécutable. À l'étape 1, les modèles « Vente » et « NouvelleVente » sont définis, le second dérivé du premier. À l'étape 2, une fonction de validation renvoie une union discriminée. À l'étape 3, un service simule le chargement de données en renvoyant une Promise typée. À l'étape 4, une fonction calcule des statistiques du tableau de bord à partir de la liste. À l'étape 5, un petit programme principal enchaîne chargement, validation et affichage, avec des états explicites. Dans le projet complet, les composants React appellent ces fonctions et affichent les résultats.

## Erreurs fréquentes

- Définir les types après avoir écrit le reste : tu corriges partout en cascade. Commence par les modèles.
- Mettre les appels fetch directement dans les composants : la logique est dupliquée et difficile à tester. Centralise-les dans des services.
- Faire confiance à la réponse de l'API : un champ manquant provoque une erreur à l'affichage. Valide les champs critiques à la réception.
- Oublier les états vide et erreur : l'écran semble cassé pour l'utilisateur. Gère-les explicitement avec une union.
- Désactiver le mode strict pour avancer vite : la dette de typage grandit. Garde « strict » actif tout le projet.
- Dupliquer les types entre front et services : ils divergent. Importe-les depuis un module commun.

## Bonnes pratiques

- Structure le projet en dossiers « types », « services », « components » et « utils ».
- Écris la validation et les statistiques comme fonctions pures faciles à tester.
- Vérifie « tsc --noEmit » avant chaque étape importante.
- Rédige un README court qui explique comment lancer le projet.
- Montre l'application avec des données réalistes, par exemple des ventes en FCFA.

## Auto-évaluation

- Pourquoi définir les modèles avant les composants ?
- Où placer les appels réseau et pourquoi ?
- Comment représenter le résultat d'une validation avec un type union ?
- Quels états un écran de liste doit-il gérer ?

## À retenir

- Un typage de bout en bout part des modèles et traverse toutes les couches.
- Les services isolent les appels réseau et leurs erreurs.
- La validation protège aux frontières du programme.
- Les états explicites évitent les écrans cassés.
- Garde le mode strict actif du début à la fin.
MD,
            'code_example' => <<<'CODE'
// dashboard.ts : coeur typé du projet (npx tsc --strict dashboard.ts && node dashboard.js)

// Étape 1 : modèles, le formulaire est dérivé du modèle principal
interface Vente {
  id: number;
  produit: string;
  montant: number; // FCFA
  date: string; // AAAA-MM-JJ
}
type NouvelleVente = Omit<Vente, "id">;

// Étape 2 : validation avec union discriminée
type Validation =
  | { ok: true; donnees: NouvelleVente }
  | { ok: false; erreurs: string[] };

function valider(entree: { produit: string; montant: number; date: string }): Validation {
  const erreurs: string[] = [];
  if (entree.produit.trim() === "") erreurs.push("Le produit est obligatoire");
  if (!Number.isFinite(entree.montant) || entree.montant <= 0) {
    erreurs.push("Le montant doit être positif");
  }
  if (!/^\d{4}-\d{2}-\d{2}$/.test(entree.date)) erreurs.push("Date au format AAAA-MM-JJ");
  if (erreurs.length > 0) return { ok: false, erreurs };
  return { ok: true, donnees: { ...entree, produit: entree.produit.trim() } };
}

// Étape 3 : service simulé, Promise typée
async function chargerVentes(): Promise<Vente[]> {
  return [
    { id: 1, produit: "Pagne", montant: 15000, date: "2026-09-01" },
    { id: 2, produit: "Sac", montant: 22000, date: "2026-09-02" },
    { id: 3, produit: "Pagne", montant: 7500, date: "2026-09-03" },
  ];
}

// Étape 4 : statistiques en fonction pure
function statistiques(ventes: Vente[]): { total: number; parProduit: Record<string, number> } {
  const parProduit: Record<string, number> = {};
  let total = 0;
  for (const v of ventes) {
    total += v.montant;
    parProduit[v.produit] = (parProduit[v.produit] ?? 0) + v.montant;
  }
  return { total, parProduit };
}

// Étape 5 : programme principal avec états explicites
async function principal(): Promise<void> {
  try {
    const ventes = await chargerVentes();
    if (ventes.length === 0) {
      console.log("Aucune vente enregistrée");
      return;
    }
    const stats = statistiques(ventes);
    console.log(`Total : ${stats.total} FCFA`, stats.parProduit);

    const resultat = valider({ produit: " ", montant: -5, date: "hier" });
    if (!resultat.ok) console.log(resultat.erreurs);
  } catch (e) {
    console.error("Erreur de chargement", e);
  }
}

principal();
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : dashboard de livraisons typé',
            'exercise_description' => <<<'TXT'
Construis un petit tableau de bord de livraisons avec React et TypeScript en mode strict, qui lit une liste de livraisons depuis un service (un fichier JSON local ou une API simulée est suffisant).

Livrables
- Un dossier « types » avec « Livraison » (id, client, ville, montant en FCFA, statut), un type « NouvelleLivraison » dérivé avec « Omit » et un type d'état en union discriminée « chargement », « succes », « erreur ».
- Un dossier « services » avec « chargerLivraisons(): Promise<Livraison[]> » qui teste « response.ok » ou simule le chargement.
- Une fonction pure « valider(entree) » qui renvoie une union discriminée « ok » ou « erreurs », utilisée par un formulaire d'ajout.
- Des composants typés : une liste, une ligne, un formulaire avec événements typés, et un bloc de statistiques (nombre de livraisons, total, montant par ville).
- Un README court et un script « typecheck » qui passe sans erreur.

Critères de réussite
- « tsconfig.json » active « strict » et « npm run typecheck » termine sans erreur, sans « any », « ! » ni « @ts-ignore ».
- Les trois états chargement, succès et erreur, ainsi que l'état vide, sont affichés.
- Le formulaire refuse un client vide et un montant non positif avec des messages explicites.
- Les statistiques sont calculées par une fonction pure séparée des composants.
TXT,
            'exercise_hint' => 'Commence par les types et la fonction de validation, puis le service et les statistiques, et seulement ensuite les composants. Teste chaque couche avant de passer à la suivante.',
            'exercise_solution' => <<<'CODE'
// ===== src/types/livraison.ts =====
export type StatutLivraison = "en_cours" | "livree";

export interface Livraison {
  id: number;
  client: string;
  ville: string;
  montant: number; // FCFA
  statut: StatutLivraison;
}

export type NouvelleLivraison = Omit<Livraison, "id">;

export type Etat<T> =
  | { statut: "chargement" }
  | { statut: "succes"; donnees: T }
  | { statut: "erreur"; message: string };

// ===== src/services/livraisons.ts =====
import type { Livraison } from "../types/livraison";

export async function chargerLivraisons(): Promise<Livraison[]> {
  const reponse = await fetch("/livraisons.json");
  if (!reponse.ok) {
    throw new Error(`Erreur HTTP ${reponse.status}`);
  }
  return (await reponse.json()) as Livraison[];
}

// ===== src/utils/livraisons.ts =====
import type { Livraison, NouvelleLivraison } from "../types/livraison";

export type Validation =
  | { ok: true; donnees: NouvelleLivraison }
  | { ok: false; erreurs: string[] };

export function valider(entree: { client: string; ville: string; montant: number }): Validation {
  const erreurs: string[] = [];
  if (entree.client.trim() === "") erreurs.push("Le client est obligatoire");
  if (entree.ville.trim() === "") erreurs.push("La ville est obligatoire");
  if (!Number.isFinite(entree.montant) || entree.montant <= 0) {
    erreurs.push("Le montant doit être positif");
  }
  if (erreurs.length > 0) return { ok: false, erreurs };
  return {
    ok: true,
    donnees: {
      client: entree.client.trim(),
      ville: entree.ville.trim(),
      montant: entree.montant,
      statut: "en_cours",
    },
  };
}

export function statistiques(livraisons: Livraison[]) {
  const parVille: Record<string, number> = {};
  let total = 0;
  for (const l of livraisons) {
    total += l.montant;
    parVille[l.ville] = (parVille[l.ville] ?? 0) + l.montant;
  }
  return { nombre: livraisons.length, total, parVille };
}

// ===== src/components/Dashboard.tsx =====
import { useEffect, useState, type ChangeEvent, type FormEvent } from "react";
import type { Etat, Livraison } from "../types/livraison";
import { chargerLivraisons } from "../services/livraisons";
import { statistiques, valider } from "../utils/livraisons";

export default function Dashboard() {
  const [etat, setEtat] = useState<Etat<Livraison[]>>({ statut: "chargement" });
  const [client, setClient] = useState<string>("");
  const [ville, setVille] = useState<string>("");
  const [montant, setMontant] = useState<string>("");
  const [erreurs, setErreurs] = useState<string[]>([]);

  useEffect(() => {
    chargerLivraisons()
      .then((donnees) => setEtat({ statut: "succes", donnees }))
      .catch((e: Error) => setEtat({ statut: "erreur", message: e.message }));
  }, []);

  const onMontant = (event: ChangeEvent<HTMLInputElement>) => setMontant(event.target.value);

  const onSubmit = (event: FormEvent<HTMLFormElement>) => {
    event.preventDefault();
    if (etat.statut !== "succes") return;
    const resultat = valider({ client, ville, montant: Number(montant) });
    if (!resultat.ok) {
      setErreurs(resultat.erreurs);
      return;
    }
    setErreurs([]);
    const id = etat.donnees.reduce((max, l) => Math.max(max, l.id), 0) + 1;
    setEtat({ statut: "succes", donnees: [...etat.donnees, { id, ...resultat.donnees }] });
    setClient("");
    setVille("");
    setMontant("");
  };

  if (etat.statut === "chargement") return <p>Chargement...</p>;
  if (etat.statut === "erreur") return <p>Erreur : {etat.message}</p>;

  const stats = statistiques(etat.donnees);

  return (
    <main>
      <p>
        {stats.nombre} livraison(s), total {stats.total} FCFA
      </p>
      {etat.donnees.length === 0 ? (
        <p>Aucune livraison pour le moment.</p>
      ) : (
        <ul>
          {etat.donnees.map((l) => (
            <li key={l.id}>
              {l.client} ({l.ville}) : {l.montant} FCFA, {l.statut}
            </li>
          ))}
        </ul>
      )}
      <form onSubmit={onSubmit}>
        <input value={client} onChange={(e) => setClient(e.target.value)} placeholder="Client" />
        <input value={ville} onChange={(e) => setVille(e.target.value)} placeholder="Ville" />
        <input value={montant} onChange={onMontant} placeholder="Montant (FCFA)" />
        <button>Ajouter</button>
        {erreurs.map((message) => (
          <p key={message}>{message}</p>
        ))}
      </form>
    </main>
  );
}

// ===== package.json (extrait) =====
// "scripts": { "typecheck": "tsc --noEmit" }
CODE,
        ],
    ],
];
