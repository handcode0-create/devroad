<?php

return [
    'lessons' => [
        'Fondamentaux JavaScript' => [
            'description' => 'Tu découvres les variables, les types de données, les opérateurs et les structures de contrôle qui forment la base de tout programme JavaScript.',
            'objective' => 'Écrire un script Node.js qui déclare des variables avec let et const, utilise if, switch et for, et affiche un résultat calculé correct.',
            'content' => <<<'MD'
## Pourquoi cette notion

JavaScript est le seul langage que tous les navigateurs comprennent nativement. Quand tu cliques sur un bouton de paiement mobile money, quand un formulaire t'indique qu'un champ est invalide ou quand une liste de produits se met à jour sans recharger la page, c'est du JavaScript qui travaille. Côté serveur, Node.js permet aussi de l'utiliser pour construire des APIs. En entreprise, c'est donc la compétence de base pour presque tous les postes de développement web.

## Les concepts clés

### Variables : let et const

Une variable est un nom associé à une valeur. Le mot-clé « const » déclare une liaison qui ne peut pas être réaffectée, tandis que « let » déclare une liaison qui peut changer. Dans un projet sérieux, tu utilises « const » par défaut et tu passes à « let » uniquement quand la valeur doit évoluer, par exemple un compteur ou un total cumulé.

### Types de données

JavaScript possède des types primitifs : « string » pour le texte, « number » pour les nombres entiers et décimaux, « boolean » pour vrai ou faux, « undefined » pour une variable sans valeur, « null » pour une absence volontaire de valeur. Tout le reste est un objet, y compris les tableaux. L'opérateur « typeof » te donne le type d'une valeur.

### Opérateurs et comparaisons

Les opérateurs arithmétiques sont l'addition, la soustraction, la multiplication, la division et « % » pour le reste d'une division. Pour comparer, utilise toujours l'égalité stricte « === » et « !== », qui compare la valeur et le type. L'égalité souple « == » convertit les types en coulisses et produit des résultats surprenants.

### Contrôle du flux

« if » et « else » exécutent un bloc selon une condition. « switch » est pratique quand une même valeur peut prendre plusieurs cas précis, avec un « break » à la fin de chaque cas. La boucle « for » répète un bloc un nombre connu de fois, et « for...of » parcourt directement les éléments d'un tableau.

## Exemple pas à pas

L'exemple de code simule le calcul d'un panier dans une boutique. À l'étape 1, on déclare des constantes pour le nom du client et le taux de remise, puis un « let » pour le total qui va changer. À l'étape 2, un tableau de prix est parcouru avec une boucle « for...of » et chaque prix est ajouté au total. À l'étape 3, une condition « if » applique la remise seulement si le total dépasse un seuil. À l'étape 4, un « switch » traduit un code de livraison en libellé lisible. Enfin, l'affichage utilise un gabarit de texte avec des backticks pour insérer les valeurs. Lance le fichier avec Node.js, modifie les prix et observe comment le résultat change.

## Erreurs fréquentes

- Utiliser « == » au lieu de « === » : la conversion implicite fausse les comparaisons, par exemple « 0 == "" » est vrai. Prends l'habitude d'écrire toujours « === ».
- Réaffecter une variable déclarée avec « const » : cela provoque une erreur « Assignment to constant variable ». Déclare avec « let » si la valeur doit changer.
- Oublier « break » dans un « switch » : l'exécution continue dans le cas suivant. Termine chaque cas par « break » ou par « return ».
- Concaténer des nombres reçus d'un formulaire : « "5" + 3 » donne « 53 ». Convertis d'abord avec « Number(valeur) » puis vérifie le résultat avec « Number.isNaN ».

## Bonnes pratiques

- Choisis des noms de variables explicites en camelCase, comme « totalPanier » plutôt que « t ».
- Utilise « const » par défaut et « let » seulement quand c'est nécessaire.
- Compare toujours avec « === » et « !== ».
- Garde des blocs courts et indentés de façon cohérente pour que la logique se lise d'un coup d'oeil.

## Auto-évaluation

- Quelle est la différence entre « let » et « const » et lequel choisis-tu par défaut ?
- Pourquoi préfère-t-on « === » à « == » ?
- Que renvoie « typeof » pour un tableau et pourquoi ?
- Quand utiliser « switch » plutôt qu'une série de « if » ?

## À retenir

- JavaScript s'exécute dans le navigateur et avec Node.js.
- « const » par défaut, « let » si la valeur change, « var » à éviter.
- Les types primitifs sont string, number, boolean, undefined, null.
- L'égalité stricte « === » évite les conversions surprenantes.
- « if », « switch » et les boucles pilotent le déroulement du programme.
MD,
            'code_example' => <<<'CODE'
// Calcul d'un panier pour une boutique en ligne (à lancer avec : node panier.js)

// Étape 1 : constantes et variable qui évolue
const client = "Awa";
const tauxRemise = 0.1; // 10 % de remise
const seuilRemise = 20000; // la remise s'applique au-delà de 20 000 FCFA
let total = 0;

// Étape 2 : on additionne les prix (en FCFA, entiers)
const prix = [7500, 12000, 3500];
for (const p of prix) {
  total += p;
}

// Étape 3 : condition sur le total
let remise = 0;
if (total > seuilRemise) {
  remise = Math.round(total * tauxRemise);
}
const aPayer = total - remise;

// Étape 4 : switch sur un code de livraison
const codeLivraison = "EXP";
let libelle;
switch (codeLivraison) {
  case "STD":
    libelle = "Livraison standard (3 jours)";
    break;
  case "EXP":
    libelle = "Livraison express (24 h)";
    break;
  default:
    libelle = "Retrait en boutique";
}

// Affichage final avec un gabarit de texte
console.log(`Client : ${client}`);
console.log(`Total : ${total} FCFA, remise : ${remise} FCFA`);
console.log(`À payer : ${aPayer} FCFA`);
console.log(libelle);
console.log(typeof aPayer === "number"); // true
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Calculateur de note de restaurant',
            'exercise_description' => <<<'TXT'
Crée un fichier « note.js » qui calcule la note d'un client dans un maquis. Le programme déclare un tableau de plats avec leur prix en FCFA, calcule le total, applique un pourboire selon un code et affiche le résultat.

Critères de réussite
- Le tableau « plats » contient au moins 3 prix entiers et le total est calculé avec une boucle « for...of ».
- Les valeurs fixes sont déclarées avec « const » et le total avec « let ».
- Si le total dépasse 15000 FCFA, une réduction de 5 % est appliquée avec « if », arrondie avec Math.round.
- Un « switch » sur un code (« S », « M », « G ») ajoute un pourboire de 0, 500 ou 1000 FCFA, avec un cas par défaut.
- Le script affiche le total, la réduction et le montant final avec console.log, et utilise « === » pour toutes les comparaisons.
TXT,
            'exercise_hint' => 'Commence par le total avec la boucle, puis ajoute la condition sur le seuil, puis le switch. Teste avec un total sous le seuil et un total au-dessus pour vérifier les deux chemins du if.',
            'exercise_solution' => <<<'CODE'
// note.js : note d'un client au maquis
const plats = [4500, 6000, 7500]; // prix en FCFA
const seuil = 15000;
const tauxReduction = 0.05;
const codePourboire = "M";

let total = 0;
for (const prix of plats) {
  total += prix;
}

let reduction = 0;
if (total > seuil) {
  reduction = Math.round(total * tauxReduction);
}

let pourboire;
switch (codePourboire) {
  case "S":
    pourboire = 0;
    break;
  case "M":
    pourboire = 500;
    break;
  case "G":
    pourboire = 1000;
    break;
  default:
    pourboire = 0;
}

const montantFinal = total - reduction + pourboire;

console.log(`Total : ${total} FCFA`);
console.log(`Réduction : ${reduction} FCFA`);
console.log(`Pourboire : ${pourboire} FCFA`);
console.log(`Montant final : ${montantFinal} FCFA`);
CODE,
        ],

        'Fonctions et portée' => [
            'description' => 'Tu apprends à écrire des fonctions, à comprendre où vivent les variables et à utiliser les closures pour garder un état privé.',
            'objective' => 'Écrire au moins trois fonctions pures et une closure qui conserve un état, en expliquant la portée de chaque variable utilisée.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans une vraie application, tu ne répètes jamais deux fois le même calcul. Le calcul d'une commission sur un transfert, la validation d'un numéro de téléphone ou le formatage d'un prix en FCFA sont écrits une seule fois dans une fonction, puis réutilisés partout. Une équipe qui relit ton code cherche d'abord des fonctions courtes, bien nommées et prévisibles.

## Les concepts clés

### Déclarer et appeler une fonction

Une fonction se déclare avec le mot-clé « function », ou sous forme de fonction fléchée avec « => ». Elle reçoit des paramètres, exécute des instructions et renvoie une valeur avec « return ». Sans « return », elle renvoie « undefined ». Les paramètres peuvent avoir une valeur par défaut, par exemple « function frais(montant, taux = 0.01) ». Une fonction est une valeur comme une autre : on peut la stocker dans une variable, la passer en argument ou la renvoyer.

### Fonctions pures et effets de bord

Une fonction pure renvoie toujours le même résultat pour les mêmes arguments et ne modifie rien en dehors d'elle. Elle est facile à tester et à comprendre. Une fonction qui modifie une variable globale, écrit à l'écran ou lit l'heure possède un effet de bord. Les effets de bord sont nécessaires, mais il vaut mieux les isoler dans quelques endroits précis.

### Portée lexicale

La portée d'une variable dépend de l'endroit où elle est écrite dans le code. Une variable déclarée avec « let » ou « const » existe dans le bloc entre accolades où elle est déclarée. Une variable déclarée dans une fonction est invisible à l'extérieur. Une fonction peut lire les variables des portées qui l'entourent, jamais l'inverse. Le moteur cherche un nom d'abord dans la portée courante, puis dans la portée parente, jusqu'à la portée globale.

### Closures

Une closure est une fonction qui garde l'accès aux variables de la portée où elle a été créée, même après que cette portée a fini de s'exécuter. C'est ce qui permet de fabriquer un compteur dont la valeur est privée : personne ne peut la modifier sans passer par les fonctions que tu exposes.

## Exemple pas à pas

L'exemple de code construit un petit module de caisse. À l'étape 1, la fonction « calculerFrais » est pure : elle prend un montant et un taux par défaut, et renvoie les frais sans toucher à rien d'autre. À l'étape 2, la fonction fléchée « formaterFCFA » transforme un nombre en texte lisible. À l'étape 3, « creerCaisse » déclare une variable « solde » à l'intérieur de la fonction. À l'étape 4, elle renvoie un objet de deux fonctions, « encaisser » et « lireSolde », qui partagent « solde » grâce à la closure. À l'étape 5, on utilise la caisse et on constate qu'on ne peut pas lire « solde » directement depuis l'extérieur.

## Erreurs fréquentes

- Oublier le « return » : la fonction renvoie « undefined » et le calcul disparaît. Vérifie que chaque chemin d'exécution renvoie une valeur quand on en attend une.
- Modifier une variable globale depuis une fonction : le comportement dépend de l'état extérieur et devient imprévisible. Passe les données en paramètres et renvoie le résultat.
- Utiliser « var » dans une boucle avec des callbacks : toutes les fonctions partagent la même variable. Utilise « let », qui crée une nouvelle liaison à chaque tour.
- Appeler une fonction sans parenthèses : « calculerFrais » désigne la fonction, « calculerFrais(1000) » l'exécute. Ajoute les parenthèses pour obtenir le résultat.

## Bonnes pratiques

- Nomme les fonctions avec un verbe : « calculerFrais », « validerTelephone », « formaterPrix ».
- Garde chaque fonction courte et centrée sur une seule tâche.
- Préfère les fonctions pures pour la logique métier et isole les effets de bord.
- Limite le nombre de paramètres, et utilise un objet quand il y en a plus de trois.

## Auto-évaluation

- Que renvoie une fonction qui n'a pas d'instruction « return » ?
- Quelle différence y a-t-il entre une fonction pure et une fonction avec effet de bord ?
- Où une variable déclarée avec « let » dans un bloc est-elle accessible ?
- Explique avec tes mots ce qu'est une closure et donne un cas d'usage.

## À retenir

- Une fonction est une valeur : on peut la stocker, la passer et la renvoyer.
- Les paramètres par défaut évitent beaucoup de vérifications.
- La portée est lexicale : elle dépend de l'endroit où le code est écrit.
- Une closure garde l'accès aux variables de sa portée de création.
- Les fonctions pures sont plus simples à tester et à réutiliser.
MD,
            'code_example' => <<<'CODE'
// Module de caisse : fonctions pures et closure (node caisse.js)

// Étape 1 : fonction pure avec paramètre par défaut
function calculerFrais(montant, taux = 0.01) {
  return Math.round(montant * taux);
}

// Étape 2 : fonction fléchée pour formater un montant en FCFA
const formaterFCFA = (valeur) => `${valeur.toLocaleString("fr-FR")} FCFA`;

// Étape 3 : une fonction qui crée un état privé
function creerCaisse(soldeInitial = 0) {
  let solde = soldeInitial; // variable invisible depuis l'extérieur

  // Étape 4 : ces fonctions « se souviennent » de solde (closure)
  function encaisser(montant) {
    if (montant <= 0) {
      return solde; // on refuse les montants invalides
    }
    solde += montant - calculerFrais(montant);
    return solde;
  }

  function lireSolde() {
    return solde;
  }

  return { encaisser, lireSolde };
}

// Étape 5 : utilisation
const caisse = creerCaisse(5000);
caisse.encaisser(20000); // frais de 200 FCFA
caisse.encaisser(10000); // frais de 100 FCFA

console.log(formaterFCFA(caisse.lireSolde())); // 34 700 FCFA
console.log(caisse.solde); // undefined : la variable est privée
console.log(formaterFCFA(calculerFrais(50000, 0.02)));
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Compteur de visites avec closure',
            'exercise_description' => <<<'TXT'
Crée « visites.js » pour une petite école. Le programme gère le nombre de visiteurs entrés dans l'établissement grâce à une closure et utilise des fonctions pures pour les calculs.

Critères de réussite
- Une fonction pure « tauxRemplissage(visiteurs, capacite) » renvoie un pourcentage arrondi et ne modifie aucune variable extérieure.
- Une fonction « creerCompteur(capacite) » garde la variable « visiteurs » privée avec une closure.
- Le compteur expose « entrer() », « sortir() » et « lire() ». « entrer() » ne dépasse jamais la capacité et « sortir() » ne descend jamais sous 0.
- Le paramètre « capacite » a une valeur par défaut de 50.
- Le script affiche le taux de remplissage après plusieurs entrées et une sortie, et vérifie que « compteur.visiteurs » vaut undefined.
TXT,
            'exercise_hint' => 'La variable visiteurs se déclare avec let dans creerCompteur, avant de renvoyer l objet. Dans entrer(), teste visiteurs < capacite avant d incrémenter.',
            'exercise_solution' => <<<'CODE'
// visites.js
function tauxRemplissage(visiteurs, capacite) {
  return Math.round((visiteurs / capacite) * 100);
}

function creerCompteur(capacite = 50) {
  let visiteurs = 0; // privé grâce à la closure

  function entrer() {
    if (visiteurs < capacite) {
      visiteurs += 1;
    }
    return visiteurs;
  }

  function sortir() {
    if (visiteurs > 0) {
      visiteurs -= 1;
    }
    return visiteurs;
  }

  function lire() {
    return visiteurs;
  }

  return { entrer, sortir, lire };
}

const compteur = creerCompteur(4);
compteur.entrer();
compteur.entrer();
compteur.entrer();
compteur.sortir();
compteur.entrer();
compteur.entrer();
compteur.entrer(); // refusé : la capacité de 4 est atteinte

console.log(`Visiteurs : ${compteur.lire()}`);
console.log(`Remplissage : ${tauxRemplissage(compteur.lire(), 4)} %`);
console.log(compteur.visiteurs); // undefined
CODE,
        ],

        'Objets, tableaux et méthodes' => [
            'description' => 'Tu manipules les objets et les tableaux, puis tu transformes des listes de données avec map, filter, find et reduce.',
            'objective' => 'Transformer une liste d objets métier en utilisant map, filter, find et reduce sans boucle manuelle, et produire un résumé chiffré.',
            'content' => <<<'MD'
## Pourquoi cette notion

Presque toutes les données d'une application sont des listes d'objets : des produits, des commandes, des élèves, des courses de livraison. Une API renvoie un tableau d'objets au format JSON, et ton travail consiste à filtrer, trier, regrouper et résumer ces données avant de les afficher. Savoir le faire proprement fait gagner un temps considérable et rend le code beaucoup plus lisible.

## Les concepts clés

### Les objets

Un objet regroupe des propriétés sous forme de paires clé-valeur : « { nom: "Riz", prix: 600 } ». On lit une propriété avec « objet.nom » ou « objet["nom"] ». La déstructuration extrait des propriétés en une ligne : « const { nom, prix } = produit ». L'opérateur de décomposition « ... » copie un objet ou en crée une version modifiée sans toucher à l'original : « { ...produit, prix: 650 } ».

### Les tableaux

Un tableau est une liste ordonnée accessible par un indice qui commence à 0. Les méthodes « push » et « pop » modifient le tableau en place, alors que « map », « filter » et « slice » renvoient un nouveau tableau. Cette distinction compte beaucoup : modifier un tableau en place peut avoir des conséquences ailleurs dans le programme, car les objets et tableaux sont passés par référence.

### map, filter, find et reduce

« map » transforme chaque élément et renvoie un tableau de même taille. « filter » garde uniquement les éléments pour lesquels la fonction renvoie vrai. « find » renvoie le premier élément qui correspond, ou « undefined ». « some » et « every » répondent par vrai ou faux. « reduce » combine tous les éléments en une seule valeur, comme une somme, avec une valeur de départ. On peut enchaîner ces méthodes : filtrer, transformer puis résumer en une expression lisible.

### Trier sans surprise

La méthode « sort » modifie le tableau d'origine et, sans fonction de comparaison, trie en texte. Pour des nombres, passe une comparaison : « (a, b) => a - b ». Copie d'abord le tableau avec « [...tableau] » si tu veux conserver l'ordre initial.

## Exemple pas à pas

L'exemple de code manipule le catalogue d'une petite épicerie. À l'étape 1, le tableau « produits » contient des objets avec nom, prix, stock et catégorie. À l'étape 2, « filter » garde les produits disponibles. À l'étape 3, « map » crée une liste de libellés prêts à afficher en utilisant la déstructuration. À l'étape 4, « find » retrouve un produit par son nom. À l'étape 5, « reduce » calcule la valeur totale du stock. À l'étape 6, on copie puis on trie pour obtenir un classement par prix sans modifier la liste d'origine. Remarque qu'aucune boucle « for » n'est écrite et que chaque étape se lit comme une phrase.

## Erreurs fréquentes

- Utiliser « map » pour un simple parcours sans utiliser le résultat : le code est trompeur. Utilise « forEach » ou « for...of » quand tu n'as pas besoin d'un nouveau tableau.
- Oublier la valeur initiale de « reduce » : sur un tableau vide, cela provoque une erreur. Passe toujours la valeur de départ, par exemple 0.
- Oublier le « return » dans une fonction fléchée avec accolades : « map » renvoie alors des « undefined ». Utilise la forme courte sans accolades ou ajoute « return ».
- Trier des nombres sans comparaison : « [10, 9, 1].sort() » donne « [1, 10, 9] ». Fournis « (a, b) => a - b ».

## Bonnes pratiques

- Préfère les méthodes qui renvoient un nouveau tableau aux modifications en place.
- Enchaîne les méthodes seulement tant que la lecture reste claire, sinon nomme des étapes intermédiaires.
- Utilise la déstructuration pour rendre explicites les propriétés utilisées.
- Choisis la méthode qui exprime l'intention : « find » pour chercher, « filter » pour sélectionner, « some » pour tester.

## Auto-évaluation

- Quelle est la différence entre « map » et « filter » ?
- Que renvoie « find » quand rien ne correspond ?
- À quoi sert le deuxième argument de « reduce » ?
- Pourquoi copier un tableau avant d'appeler « sort » ?

## À retenir

- Objets et tableaux sont passés par référence.
- « map » transforme, « filter » sélectionne, « find » cherche, « reduce » résume.
- Ces méthodes renvoient de nouvelles valeurs et laissent l'original intact, sauf « sort », « push » et « pop ».
- La déstructuration et la décomposition rendent le code plus court et plus clair.
MD,
            'code_example' => <<<'CODE'
// Catalogue d'une petite épicerie (node catalogue.js)

// Étape 1 : une liste d'objets (prix en FCFA)
const produits = [
  { nom: "Riz 5 kg", prix: 3500, stock: 12, categorie: "base" },
  { nom: "Huile 1 L", prix: 1500, stock: 0, categorie: "base" },
  { nom: "Savon", prix: 400, stock: 40, categorie: "hygiene" },
  { nom: "Sucre 1 kg", prix: 800, stock: 25, categorie: "base" },
];

// Étape 2 : filter garde les produits en stock
const disponibles = produits.filter((produit) => produit.stock > 0);

// Étape 3 : map transforme chaque objet en libellé (avec déstructuration)
const libelles = disponibles.map(({ nom, prix }) => `${nom} : ${prix} FCFA`);
console.log(libelles);

// Étape 4 : find retrouve un produit précis
const savon = produits.find((produit) => produit.nom === "Savon");
console.log(savon ? savon.prix : "introuvable");

// Étape 5 : reduce calcule la valeur totale du stock
const valeurStock = produits.reduce(
  (total, produit) => total + produit.prix * produit.stock,
  0 // valeur de départ
);
console.log(`Valeur du stock : ${valeurStock} FCFA`);

// Étape 6 : copie puis tri, la liste d'origine reste intacte
const parPrix = [...produits].sort((a, b) => a.prix - b.prix);
console.log(parPrix.map((produit) => produit.nom));

// Copie d'un objet avec une propriété modifiée
const savonAugmente = { ...savon, prix: 450 };
console.log(savon.prix, savonAugmente.prix); // 400 450
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Bilan des commandes de livraison',
            'exercise_description' => <<<'TXT'
Crée « commandes.js » avec un tableau d'au moins 5 commandes de livraison. Chaque commande est un objet avec « id », « client », « montant » (FCFA), « statut » (« livree » ou « en_cours ») et « ville ».

Critères de réussite
- Une constante « livrees » est obtenue avec « filter » sur le statut « livree ».
- Un tableau « resumes » est obtenu avec « map » et affiche des chaînes du type « #1 Awa : 12000 FCFA ».
- Le chiffre d'affaires des commandes livrées est calculé avec « reduce » et une valeur initiale de 0.
- Une fonction utilise « find » pour retrouver une commande par « id » et gère le cas où elle n'existe pas.
- Le classement par montant décroissant est fait sur une copie du tableau, et le tableau d'origine garde son ordre.
TXT,
            'exercise_hint' => 'Pour trier par montant décroissant, la comparaison est (a, b) => b.montant - a.montant. Pense à copier le tableau avec [...commandes] avant de trier.',
            'exercise_solution' => <<<'CODE'
// commandes.js
const commandes = [
  { id: 1, client: "Awa", montant: 12000, statut: "livree", ville: "Abidjan" },
  { id: 2, client: "Koffi", montant: 8500, statut: "en_cours", ville: "Bouaké" },
  { id: 3, client: "Mariam", montant: 23000, statut: "livree", ville: "Abidjan" },
  { id: 4, client: "Yao", montant: 4500, statut: "livree", ville: "Yamoussoukro" },
  { id: 5, client: "Fatou", montant: 15000, statut: "en_cours", ville: "Abidjan" },
];

const livrees = commandes.filter((commande) => commande.statut === "livree");

const resumes = livrees.map(
  ({ id, client, montant }) => `#${id} ${client} : ${montant} FCFA`
);

const chiffreAffaires = livrees.reduce(
  (total, commande) => total + commande.montant,
  0
);

function trouverCommande(id) {
  const commande = commandes.find((c) => c.id === id);
  return commande ? commande : null;
}

const classement = [...commandes].sort((a, b) => b.montant - a.montant);

console.log(resumes);
console.log(`Chiffre d'affaires : ${chiffreAffaires} FCFA`);
console.log(trouverCommande(3));
console.log(trouverCommande(99)); // null
console.log(classement.map((c) => c.id)); // [3, 5, 1, 2, 4]
console.log(commandes.map((c) => c.id)); // [1, 2, 3, 4, 5] : ordre intact
CODE,
        ],

        'Asynchrone et Promises' => [
            'description' => 'Tu comprends comment JavaScript gère les opérations lentes comme les appels réseau, avec les Promises, async/await et fetch.',
            'objective' => 'Écrire une fonction asynchrone qui appelle une API avec fetch, gère les erreurs réseau et HTTP, et enchaîne ou parallélise plusieurs appels.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application web passe son temps à attendre : une réponse du serveur, une confirmation de paiement mobile money, le chargement d'une image, la lecture d'un fichier. Si JavaScript bloquait pendant chaque attente, la page serait figée. Le langage utilise donc un modèle asynchrone : on lance l'opération, on continue le travail, et on reprend quand le résultat arrive.

## Les concepts clés

### Les Promises

Une Promise représente le résultat futur d'une opération. Elle est dans l'un de ces états : en attente, résolue avec une valeur, ou rejetée avec une erreur. On branche des actions avec « then » pour le succès, « catch » pour l'erreur et « finally » pour le nettoyage. Chaque « then » renvoie une nouvelle Promise, ce qui permet d'enchaîner les étapes.

### async et await

Le mot-clé « async » devant une fonction la fait renvoyer une Promise. À l'intérieur, « await » suspend la fonction jusqu'à ce que la Promise soit réglée, sans bloquer le reste du programme. Le code se lit alors de haut en bas comme du code synchrone. On gère les erreurs avec un bloc « try...catch », comme pour n'importe quelle erreur.

### fetch et les réponses HTTP

La fonction « fetch(url) » renvoie une Promise qui se résout avec un objet « Response ». Point crucial : « fetch » ne rejette la Promise qu'en cas de problème réseau. Une réponse 404 ou 500 est une réponse valide pour fetch. Tu dois donc tester « response.ok » toi-même et lever une erreur si besoin. Pour lire le JSON, on appelle « await response.json() », qui renvoie elle aussi une Promise.

### Enchaîner ou paralléliser

Quand deux appels dépendent l'un de l'autre, on les enchaîne avec deux « await » successifs. Quand ils sont indépendants, on les lance ensemble avec « Promise.all », qui attend que toutes les Promises soient résolues et renvoie un tableau de résultats. « Promise.all » échoue dès qu'une Promise est rejetée, alors que « Promise.allSettled » attend la fin de toutes et rapporte le statut de chacune.

## Exemple pas à pas

L'exemple de code récupère des informations sur une boutique. À l'étape 1, la fonction « attendre » enveloppe un « setTimeout » dans une Promise pour simuler une pause. À l'étape 2, « chargerJson » appelle fetch, teste « response.ok » et lève une erreur claire si le statut n'est pas bon. À l'étape 3, la fonction principale utilise « try...catch » pour capturer toute erreur. À l'étape 4, deux appels indépendants sont lancés en parallèle avec « Promise.all ». À l'étape 5, un bloc « finally » affiche un message de fin quel que soit le résultat. Lance le script avec Node.js, puis casse volontairement l'URL pour observer le message d'erreur.

## Erreurs fréquentes

- Oublier « await » devant un appel asynchrone : tu manipules une Promise au lieu du résultat et obtiens « Promise { pending } ». Ajoute « await » ou enchaîne avec « then ».
- Croire que fetch rejette sur une erreur 404 : le code continue avec des données invalides. Teste « response.ok » et lève une erreur toi-même.
- Utiliser « await » dans une fonction non déclarée « async » : c'est une erreur de syntaxe en dehors d'un module. Déclare la fonction avec « async ».
- Enchaîner des appels indépendants avec des « await » séparés : l'attente totale s'additionne inutilement. Utilise « Promise.all » pour les lancer ensemble.

## Bonnes pratiques

- Écris des fonctions asynchrones petites, chacune avec une seule responsabilité.
- Centralise la logique de fetch dans une fonction qui vérifie « response.ok » et renvoie le JSON.
- Gère toujours le cas d'erreur et affiche un message compréhensible à l'utilisateur.
- Lance en parallèle tout ce qui est indépendant.

## Auto-évaluation

- Quels sont les trois états d'une Promise ?
- Que fait « await » et dans quel type de fonction peut-on l'utiliser ?
- Pourquoi faut-il tester « response.ok » avec fetch ?
- Quand préfères-tu « Promise.all » à deux « await » successifs ?

## À retenir

- Le modèle asynchrone évite de bloquer le programme pendant les attentes.
- Une fonction « async » renvoie toujours une Promise.
- fetch ne rejette que sur une erreur réseau, pas sur un statut HTTP d'erreur.
- « try...catch » gère les erreurs des fonctions « async ».
- « Promise.all » accélère les appels indépendants.
MD,
            'code_example' => <<<'CODE'
// Appels asynchrones avec fetch (Node.js récent ou navigateur)
// node async.js

// Étape 1 : une Promise qui se résout après un délai
function attendre(ms) {
  return new Promise((resolve) => setTimeout(resolve, ms));
}

// Étape 2 : fonction générique qui vérifie le statut HTTP
async function chargerJson(url) {
  const response = await fetch(url);
  if (!response.ok) {
    // fetch ne rejette pas sur un 404 : on lève l'erreur nous-mêmes
    throw new Error(`Erreur HTTP ${response.status} pour ${url}`);
  }
  return response.json();
}

// Étape 3 : fonction principale avec gestion d'erreur
async function afficherBoutique() {
  try {
    console.log("Chargement...");
    await attendre(300); // petite pause pour simuler une attente

    // Étape 4 : deux appels indépendants lancés en parallèle
    const [utilisateur, taches] = await Promise.all([
      chargerJson("https://jsonplaceholder.typicode.com/users/1"),
      chargerJson("https://jsonplaceholder.typicode.com/todos?userId=1"),
    ]);

    const terminees = taches.filter((tache) => tache.completed).length;
    console.log(`${utilisateur.name} : ${terminees}/${taches.length} tâches terminées`);
  } catch (erreur) {
    console.error("Impossible de charger les données :", erreur.message);
  } finally {
    // Étape 5 : exécuté dans tous les cas
    console.log("Fin du chargement");
  }
}

afficherBoutique();
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Chargeur de données robuste',
            'exercise_description' => <<<'TXT'
Crée « chargeur.js » qui récupère des données depuis une API publique de test (par exemple jsonplaceholder.typicode.com) et gère correctement les erreurs.

Critères de réussite
- Une fonction « async » nommée « chargerJson(url) » utilise fetch, teste « response.ok » et lève une erreur avec le statut si la réponse n'est pas valide.
- Une fonction principale « async » utilise « try...catch...finally » et affiche un message dans chaque cas.
- Deux ressources indépendantes (par exemple « posts » et « users ») sont chargées en parallèle avec « Promise.all ».
- Le script affiche le nombre d'éléments reçus pour chaque ressource.
- Un appel volontaire vers une URL inexistante (par exemple « /posts/99999999 ») est capturé et affiche un message d'erreur lisible, sans faire planter le script.
TXT,
            'exercise_hint' => 'Mets l appel volontaire dans un second try...catch pour montrer que l erreur est gérée sans arrêter le programme. Pense à await sur Promise.all.',
            'exercise_solution' => <<<'CODE'
// chargeur.js
const BASE = "https://jsonplaceholder.typicode.com";

async function chargerJson(url) {
  const response = await fetch(url);
  if (!response.ok) {
    throw new Error(`Erreur HTTP ${response.status} pour ${url}`);
  }
  return response.json();
}

async function principal() {
  try {
    console.log("Chargement en cours...");
    const [posts, users] = await Promise.all([
      chargerJson(`${BASE}/posts`),
      chargerJson(`${BASE}/users`),
    ]);
    console.log(`${posts.length} articles reçus`);
    console.log(`${users.length} utilisateurs reçus`);
  } catch (erreur) {
    console.error("Échec du chargement :", erreur.message);
  } finally {
    console.log("Chargement terminé");
  }

  // Cas d'erreur volontaire : l'erreur est capturée, le script continue
  try {
    await chargerJson(`${BASE}/posts/99999999`);
  } catch (erreur) {
    console.error("Erreur attendue :", erreur.message);
  }
}

principal();
CODE,
        ],

        'Modules et navigateur' => [
            'description' => 'Tu organises ton code en modules ES et tu utilises le DOM, les événements et localStorage pour construire une page interactive.',
            'objective' => 'Construire une page avec plusieurs modules ES qui lit des éléments du DOM, réagit à des événements et sauvegarde des données dans localStorage.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dès qu'un projet dépasse quelques dizaines de lignes, tout mettre dans un seul fichier devient ingérable. Les modules permettent de découper le code en fichiers cohérents, chacun avec une responsabilité claire. C'est la base de toute application moderne, qu'elle utilise un framework ou non.

## Les concepts clés

### Les modules ES

Un module est un fichier qui expose certaines valeurs avec « export » et en importe d'autres avec « import ». Dans une page HTML, tu charges le point d'entrée avec « type="module" ». Les modules ont leur propre portée : une variable non exportée reste privée. Un export nommé s'importe avec des accolades, et un export par défaut s'importe sans accolades. Les chemins relatifs doivent inclure l'extension, par exemple « ./stockage.js ». Pour que les modules fonctionnent, la page doit être servie par un serveur local et non ouverte directement depuis le disque.

### Le DOM

Le DOM est la représentation de la page sous forme d'arbre d'objets. Tu sélectionnes un élément avec « document.querySelector » et tu modifies son contenu avec « textContent ». Tu crées des éléments avec « document.createElement » et tu les ajoutes avec « append ». Pour afficher du texte provenant d'un utilisateur, utilise « textContent » et non « innerHTML », afin d'éviter l'injection de code malveillant.

### Les événements

Un événement signale qu'une action s'est produite : un clic, une saisie, l'envoi d'un formulaire. Tu écoutes avec « addEventListener(type, fonction) ». Pour un formulaire, appelle « event.preventDefault() » afin d'empêcher le rechargement de la page. La délégation d'événements consiste à écouter sur un parent pour gérer les clics sur ses enfants, même ceux ajoutés plus tard.

### localStorage

localStorage conserve des paires clé-valeur de type texte dans le navigateur, sans date d'expiration. Pour stocker un tableau ou un objet, convertis-le avec « JSON.stringify » à l'écriture et « JSON.parse » à la lecture. Ne stocke jamais d'informations sensibles comme un mot de passe, car n'importe quel script de la page peut lire ces données.

## Exemple pas à pas

L'exemple de code est un mini carnet de courses réparti en deux modules. À l'étape 1, le module « stockage.js » exporte « charger » et « sauvegarder », qui encapsulent localStorage et JSON. À l'étape 2, le module « app.js » importe ces fonctions. À l'étape 3, il sélectionne le formulaire, le champ et la liste. À l'étape 4, la fonction « afficher » vide la liste puis crée un élément pour chaque course avec « textContent ». À l'étape 5, l'écouteur « submit » empêche le rechargement, ajoute la course, sauvegarde et réaffiche. À l'étape 6, au chargement, on affiche les données déjà sauvegardées. Le fichier HTML donne le squelette de la page.

## Erreurs fréquentes

- Ouvrir la page directement avec « file:// » : le navigateur bloque les modules. Lance un serveur local, par exemple avec « npx serve » ou l'extension Live Server.
- Oublier « type="module" » sur la balise script : « import » provoque une erreur de syntaxe. Ajoute l'attribut sur le script d'entrée.
- Oublier « preventDefault » dans l'écouteur d'un formulaire : la page se recharge et le travail disparaît. Appelle « event.preventDefault() » en première ligne.
- Stocker un objet sans « JSON.stringify » : localStorage enregistre « [object Object] ». Convertis toujours avant d'écrire et après avoir lu.

## Bonnes pratiques

- Un module, une responsabilité : stockage, appels API, rendu et logique métier séparés.
- Garde les fonctions de rendu séparées des fonctions de données.
- Entoure la lecture de localStorage d'une valeur par défaut au cas où la clé n'existe pas.
- Utilise des noms de clés explicites et préfixés, comme « carnet.courses ».

## Auto-évaluation

- Comment exportes-tu et importes-tu une fonction entre deux fichiers ?
- Pourquoi un module ne fonctionne-t-il pas en ouvrant le fichier HTML directement ?
- Que fait « event.preventDefault() » sur un formulaire ?
- Pourquoi convertir avec JSON avant d'utiliser localStorage ?

## À retenir

- Les modules ES découpent le code avec « import » et « export ».
- Le DOM est un arbre d'objets que JavaScript lit et modifie.
- « addEventListener » relie une action de l'utilisateur à une fonction.
- localStorage ne stocke que du texte, d'où JSON.stringify et JSON.parse.
- Ne stocke jamais de données sensibles dans localStorage.
MD,
            'code_example' => <<<'CODE'
// ===== stockage.js (module 1) =====
// Étape 1 : encapsule localStorage et JSON
const CLE = "carnet.courses";

export function charger() {
  try {
    const brut = localStorage.getItem(CLE);
    return brut ? JSON.parse(brut) : []; // valeur par défaut
  } catch {
    return [];
  }
}

export function sauvegarder(courses) {
  localStorage.setItem(CLE, JSON.stringify(courses));
}

// ===== app.js (module 2) =====
// Étape 2 : import des fonctions du premier module
import { charger, sauvegarder } from "./stockage.js";

// Étape 3 : sélection des éléments de la page
const formulaire = document.querySelector("#formulaire");
const champ = document.querySelector("#course");
const liste = document.querySelector("#liste");

let courses = charger();

// Étape 4 : rendu de la liste (textContent : pas d'injection de code)
function afficher() {
  liste.replaceChildren();
  for (const course of courses) {
    const li = document.createElement("li");
    li.textContent = course;
    liste.append(li);
  }
}

// Étape 5 : réaction à l'envoi du formulaire
formulaire.addEventListener("submit", (event) => {
  event.preventDefault(); // évite le rechargement de la page
  const valeur = champ.value.trim();
  if (valeur === "") return; // on refuse une saisie vide
  courses.push(valeur);
  sauvegarder(courses);
  champ.value = "";
  afficher();
});

// Étape 6 : affichage initial des données sauvegardées
afficher();

// ===== index.html =====
// <form id="formulaire">
//   <input id="course" placeholder="Ex : riz, huile">
//   <button>Ajouter</button>
// </form>
// <ul id="liste"></ul>
// <script type="module" src="./app.js"></script>
CODE,
            'estimated_minutes' => 75,
            'exercise_title' => 'Liste de tâches persistante en modules',
            'exercise_description' => <<<'TXT'
Construis une petite page « Mes tâches du jour » avec trois fichiers : « index.html », « stockage.js » et « app.js ». Sers-la avec un serveur local.

Critères de réussite
- « stockage.js » exporte « chargerTaches » et « sauvegarderTaches » avec localStorage et JSON, et renvoie un tableau vide si rien n'est enregistré.
- « app.js » importe ces fonctions et le script est chargé avec « type="module" ».
- L'envoi du formulaire appelle « preventDefault », refuse un texte vide et ajoute la tâche à la liste.
- Chaque tâche est affichée avec « textContent » et un clic dessus la supprime de la liste et du stockage.
- Après rechargement de la page, les tâches sont toujours présentes.
TXT,
            'exercise_hint' => 'Pour la suppression, ajoute un écouteur click sur chaque li dans la fonction afficher, puis filtre le tableau par indice et sauvegarde.',
            'exercise_solution' => <<<'CODE'
// ===== index.html =====
// <!doctype html>
// <html lang="fr">
// <head><meta charset="utf-8"><title>Mes tâches</title></head>
// <body>
//   <h1>Mes tâches du jour</h1>
//   <form id="formulaire">
//     <input id="tache" placeholder="Nouvelle tâche">
//     <button>Ajouter</button>
//   </form>
//   <ul id="liste"></ul>
//   <script type="module" src="./app.js"></script>
// </body>
// </html>

// ===== stockage.js =====
const CLE = "taches.jour";

export function chargerTaches() {
  try {
    const brut = localStorage.getItem(CLE);
    return brut ? JSON.parse(brut) : [];
  } catch {
    return [];
  }
}

export function sauvegarderTaches(taches) {
  localStorage.setItem(CLE, JSON.stringify(taches));
}

// ===== app.js =====
import { chargerTaches, sauvegarderTaches } from "./stockage.js";

const formulaire = document.querySelector("#formulaire");
const champ = document.querySelector("#tache");
const liste = document.querySelector("#liste");

let taches = chargerTaches();

function afficher() {
  liste.replaceChildren();
  taches.forEach((tache, index) => {
    const li = document.createElement("li");
    li.textContent = tache;
    li.addEventListener("click", () => {
      taches = taches.filter((_, i) => i !== index);
      sauvegarderTaches(taches);
      afficher();
    });
    liste.append(li);
  });
}

formulaire.addEventListener("submit", (event) => {
  event.preventDefault();
  const valeur = champ.value.trim();
  if (valeur === "") return;
  taches.push(valeur);
  sauvegarderTaches(taches);
  champ.value = "";
  afficher();
});

afficher();
CODE,
        ],

        'Qualité et debugging' => [
            'description' => 'Tu apprends à gérer les erreurs, à déboguer méthodiquement, à écrire des tests automatisés et à utiliser un linter.',
            'objective' => 'Lever et capturer des erreurs explicites, écrire au moins cinq tests automatisés avec le test runner de Node.js et corriger un bug en utilisant les outils de debugging.',
            'content' => <<<'MD'
## Pourquoi cette notion

Écrire du code qui marche une fois ne suffit pas. En entreprise, le code est lu, modifié et exécuté pendant des années par plusieurs personnes. Un bug dans un calcul de paiement ou de stock coûte de l'argent réel et de la confiance. Les développeurs qui progressent vite ne sont pas ceux qui ne font jamais d'erreurs, mais ceux qui savent les détecter tôt, les comprendre rapidement et empêcher leur retour.

## Les concepts clés

### Erreurs et exceptions

Quand quelque chose ne va pas, on lève une erreur avec « throw new Error("message") ». On capture l'erreur avec « try...catch » seulement là où on sait quoi en faire : afficher un message, réessayer ou arrêter proprement. Attraper une erreur pour l'ignorer en silence est l'une des pires habitudes, car elle cache le problème.

### Déboguer avec méthode

Déboguer, c'est trouver la cause, pas essayer au hasard. La démarche est toujours la même : reproduire le bug de façon fiable, formuler une hypothèse, la vérifier, corriger, puis revérifier. Pour observer le programme, utilise « console.log » pour des vérifications rapides, et surtout le débogueur : l'instruction « debugger », les points d'arrêt dans les outils du navigateur ou dans ton éditeur te permettent de suspendre l'exécution, de lire les variables et d'avancer pas à pas. Lis toujours le message d'erreur et la pile d'appels en entier : la première ligne de ton propre code indique où regarder.

### Tests automatisés

Un test est un petit programme qui appelle ton code avec des entrées connues et vérifie le résultat. Node.js fournit un test runner intégré dans le module « node:test », associé à « node:assert ». Un bon test est court, indépendant des autres et teste un seul comportement. Pense aux cas normaux, aux cas limites comme zéro ou une liste vide, et aux cas d'erreur. Les tests te permettent de modifier le code sans craindre de casser l'existant.

### Linting

Un linter comme ESLint analyse ton code sans l'exécuter et signale les problèmes probables : variable inutilisée, variable non déclarée, comparaison suspecte. Il applique aussi des règles de style communes à toute l'équipe.

## Exemple pas à pas

L'exemple de code teste une fonction de calcul de frais de transfert. À l'étape 1, la fonction « calculerFrais » valide son entrée et lève une erreur claire si le montant n'est pas un nombre positif. À l'étape 2, on importe « test » et « assert » depuis les modules de Node.js. À l'étape 3, un premier test vérifie un cas normal. À l'étape 4, un test vérifie un cas limite, le montant minimum. À l'étape 5, un test vérifie qu'une erreur est bien levée pour une valeur négative avec « assert.throws ». Lance le tout avec « node --test » et regarde le rapport. Casse ensuite volontairement la fonction pour voir un test échouer et lire le message.

## Erreurs fréquentes

- Ignorer une erreur avec un « catch » vide : le problème disparaît de la vue mais pas du programme. Journalise ou relance l'erreur.
- Lever une chaîne au lieu d'une erreur, comme « throw "erreur" » : tu perds la pile d'appels. Lève toujours « new Error(...) ».
- Déboguer en modifiant plusieurs choses à la fois : tu ne sais plus ce qui a corrigé quoi. Change une seule chose, puis vérifie.
- Tester seulement le cas heureux : les bugs se cachent aux limites. Ajoute des tests pour zéro, valeur négative, liste vide et valeur manquante.

## Bonnes pratiques

- Valide les entrées au début des fonctions et échoue vite avec un message clair.
- Reproduis chaque bug par un test avant de le corriger, pour qu'il ne revienne pas.
- Utilise le débogueur plutôt que des dizaines de « console.log » oubliés.
- Lance les tests et le linter avant chaque envoi de code.

## Auto-évaluation

- Pourquoi est-il préférable de lever « new Error(...) » plutôt qu'une chaîne ?
- Quelles sont les étapes d'un débogage méthodique ?
- Qu'est-ce qu'un cas limite et peux-tu en citer deux pour une fonction de calcul de frais ?
- Que vérifie « assert.throws » ?

## À retenir

- Une bonne erreur est explicite, avec la valeur fautive dans le message.
- Ne capture une erreur que si tu sais la traiter.
- Déboguer, c'est reproduire, supposer, vérifier, corriger.
- Les tests protègent contre les régressions et documentent le comportement.
- Le linter détecte des problèmes avant l'exécution.
MD,
            'code_example' => <<<'CODE'
// frais.test.js : à lancer avec  node --test
import test from "node:test";
import assert from "node:assert/strict";

// Étape 1 : fonction à tester, avec validation d'entrée
function calculerFrais(montant) {
  if (typeof montant !== "number" || Number.isNaN(montant) || montant <= 0) {
    throw new Error(`Montant invalide : ${montant}`);
  }
  // 1 % de frais, minimum 100 FCFA
  return Math.max(100, Math.round(montant * 0.01));
}

// Étape 3 : cas normal
test("calcule 1 % de frais sur un montant courant", () => {
  assert.equal(calculerFrais(50000), 500);
});

// Étape 4 : cas limite, le minimum s'applique
test("applique le minimum de 100 FCFA sur un petit montant", () => {
  assert.equal(calculerFrais(2000), 100);
});

// Étape 5 : cas d'erreur
test("refuse un montant négatif avec un message clair", () => {
  assert.throws(() => calculerFrais(-500), /Montant invalide : -500/);
});

test("refuse une valeur qui n'est pas un nombre", () => {
  assert.throws(() => calculerFrais("abc"), /Montant invalide/);
});

// Pour déboguer pas à pas, ajoute l'instruction debugger dans la fonction
// puis lance : node --inspect-brk --test
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Sécuriser et tester un calcul de remise',
            'exercise_description' => <<<'TXT'
Crée « remise.js » et « remise.test.js ». La fonction « appliquerRemise(prix, pourcentage) » renvoie le prix après remise, en FCFA, arrondi à l'entier.

Critères de réussite
- « appliquerRemise » est exportée et lève « new Error » avec un message contenant la valeur fautive si le prix n'est pas un nombre positif ou si le pourcentage n'est pas entre 0 et 100.
- Le fichier de test utilise « node:test » et « node:assert/strict » et contient au moins 5 tests.
- Les tests couvrent un cas normal, un pourcentage de 0, un pourcentage de 100, un prix négatif et un pourcentage supérieur à 100.
- Les erreurs sont vérifiées avec « assert.throws ».
- La commande « node --test » termine avec tous les tests au vert.
TXT,
            'exercise_hint' => 'Écris les tests en premier, regarde-les échouer, puis implémente la fonction. Pour 100 %, le résultat attendu est 0.',
            'exercise_solution' => <<<'CODE'
// ===== remise.js =====
export function appliquerRemise(prix, pourcentage) {
  if (typeof prix !== "number" || Number.isNaN(prix) || prix <= 0) {
    throw new Error(`Prix invalide : ${prix}`);
  }
  if (
    typeof pourcentage !== "number" ||
    Number.isNaN(pourcentage) ||
    pourcentage < 0 ||
    pourcentage > 100
  ) {
    throw new Error(`Pourcentage invalide : ${pourcentage}`);
  }
  return Math.round(prix - (prix * pourcentage) / 100);
}

// ===== remise.test.js =====
import test from "node:test";
import assert from "node:assert/strict";
import { appliquerRemise } from "./remise.js";

test("applique 10 % sur 20000 FCFA", () => {
  assert.equal(appliquerRemise(20000, 10), 18000);
});

test("0 % laisse le prix inchangé", () => {
  assert.equal(appliquerRemise(15000, 0), 15000);
});

test("100 % donne un prix nul", () => {
  assert.equal(appliquerRemise(15000, 100), 0);
});

test("refuse un prix négatif", () => {
  assert.throws(() => appliquerRemise(-1000, 10), /Prix invalide : -1000/);
});

test("refuse un pourcentage supérieur à 100", () => {
  assert.throws(() => appliquerRemise(1000, 150), /Pourcentage invalide : 150/);
});
CODE,
        ],

        'Projet final JavaScript' => [
            'description' => 'Tu assembles tout ce que tu as appris dans une application de gestion de tâches sans framework, avec recherche, filtres, formulaire et sauvegarde locale.',
            'objective' => 'Livrer une application JavaScript structurée en modules, avec ajout, recherche, filtrage, suppression et persistance dans localStorage, ainsi que des tests sur la logique métier.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet final prouve que tu sais relier les notions entre elles. Savoir écrire une boucle ou un appel fetch est une chose, construire une application complète qui reste lisible en est une autre. C'est aussi ce que regardent les recruteurs et les clients : un projet fonctionnel, organisé, que tu peux montrer et expliquer.

## Les concepts clés

### Séparer les responsabilités

Découpe l'application en modules à responsabilité unique. Un module de données contient la logique métier : créer, modifier, supprimer et filtrer des tâches, sans jamais toucher au DOM. Un module de stockage lit et écrit dans localStorage. Un module d'interface affiche l'état et branche les événements. Un fichier principal assemble le tout. Cette séparation rend la logique testable sans navigateur.

### Un état unique et un rendu prévisible

Garde l'état de l'application dans une seule structure, par exemple un tableau de tâches et un filtre courant. Chaque action modifie l'état, puis une fonction « afficher » reconstruit l'interface à partir de cet état. Tu évites ainsi les incohérences entre ce que voit l'utilisateur et ce que contient la mémoire. Les fonctions de logique renvoient de nouveaux tableaux plutôt que de modifier l'ancien.

### Identifiants et données fiables

Donne un identifiant unique à chaque tâche, par exemple avec « crypto.randomUUID() » ou un compteur. Ne te base pas sur la position dans le tableau, qui change après un filtre ou une suppression. Valide les données lues dans localStorage : elles peuvent être absentes ou corrompues, donc entoure la lecture d'un « try...catch » et renvoie un tableau vide par défaut.

### Recherche et filtres

La recherche compare le texte saisi au titre en minuscules avec « includes ». Le filtre de statut garde les tâches terminées, en cours ou toutes. Combine les deux avec « filter ». Pour une saisie rapide, tu peux limiter la fréquence des mises à jour, mais pour un petit volume de données, un simple rendu à chaque frappe suffit.

## Exemple pas à pas

L'exemple de code montre le coeur de la logique métier, dans un module « taches.js » qui n'utilise pas le DOM. À l'étape 1, « creerTache » construit un objet avec un identifiant, un titre nettoyé et un statut. À l'étape 2, « ajouter » renvoie un nouveau tableau avec la tâche en plus. À l'étape 3, « basculer » inverse le statut d'une tâche par son identifiant avec « map ». À l'étape 4, « supprimer » utilise « filter ». À l'étape 5, « filtrer » combine recherche et statut. À l'étape 6, un petit scénario d'utilisation affiche le résultat. Dans ton projet, tu brancheras ces fonctions à une interface et à localStorage.

## Erreurs fréquentes

- Mélanger logique et DOM dans les mêmes fonctions : impossible à tester et difficile à lire. Garde la logique dans un module sans accès à « document ».
- Utiliser l'indice du tableau comme identifiant : après un filtre, tu supprimes la mauvaise tâche. Utilise un identifiant unique stocké dans chaque tâche.
- Oublier de sauvegarder après chaque modification : les données disparaissent au rechargement. Appelle la sauvegarde dans une seule fonction après chaque changement d'état.
- Faire confiance aux données de localStorage : une valeur corrompue casse l'application au démarrage. Entoure « JSON.parse » d'un « try...catch ».

## Bonnes pratiques

- Commence par la logique métier et ses tests, puis ajoute l'interface.
- Avance par petites étapes et vérifie dans le navigateur après chacune.
- Garde des fonctions courtes, nommées par un verbe, et des modules d'une seule responsabilité.
- Prévois les états vides : un message clair quand il n'y a aucune tâche ou aucun résultat.

## Auto-évaluation

- Pourquoi garder la logique métier sans accès au DOM ?
- Pourquoi utiliser un identifiant unique plutôt que l'indice du tableau ?
- Que se passe-t-il si localStorage contient du texte invalide et comment l'anticiper ?
- Comment combiner une recherche textuelle et un filtre de statut ?

## À retenir

- Un projet bien découpé se lit, se teste et évolue plus facilement.
- L'état est centralisé, l'interface est reconstruite à partir de lui.
- Chaque tâche a un identifiant unique et stable.
- Les données de localStorage sont à valider avant usage.
- Les fonctions pures de logique sont faciles à tester.
MD,
            'code_example' => <<<'CODE'
// taches.js : logique métier du gestionnaire de tâches (sans DOM)
// Exécutable avec Node.js : node taches.js

// Étape 1 : création d'une tâche valide
export function creerTache(titre) {
  const propre = titre.trim();
  if (propre === "") {
    throw new Error("Le titre de la tâche est obligatoire");
  }
  return { id: crypto.randomUUID(), titre: propre, terminee: false };
}

// Étape 2 : ajout sans modifier le tableau d'origine
export function ajouter(taches, titre) {
  return [...taches, creerTache(titre)];
}

// Étape 3 : inversion du statut par identifiant
export function basculer(taches, id) {
  return taches.map((t) => (t.id === id ? { ...t, terminee: !t.terminee } : t));
}

// Étape 4 : suppression par identifiant
export function supprimer(taches, id) {
  return taches.filter((t) => t.id !== id);
}

// Étape 5 : recherche et statut combinés
export function filtrer(taches, recherche = "", statut = "toutes") {
  const mot = recherche.trim().toLowerCase();
  return taches.filter((t) => {
    const okTexte = t.titre.toLowerCase().includes(mot);
    const okStatut =
      statut === "toutes" ||
      (statut === "terminees" && t.terminee) ||
      (statut === "en_cours" && !t.terminee);
    return okTexte && okStatut;
  });
}

// Étape 6 : petit scénario d'utilisation
let liste = [];
liste = ajouter(liste, "Appeler le fournisseur");
liste = ajouter(liste, "Livrer la commande de Awa");
liste = basculer(liste, liste[0].id);

console.log(filtrer(liste, "", "terminees").map((t) => t.titre));
console.log(filtrer(liste, "livrer").map((t) => t.titre));
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : gestionnaire de tâches complet',
            'exercise_description' => <<<'TXT'
Construis un gestionnaire de tâches sans framework, avec des fichiers « index.html », « taches.js », « stockage.js » et « app.js », servi par un serveur local. Ajoute un fichier « taches.test.js » pour la logique métier et un court « README.md ».

Livrables
- Un formulaire d'ajout qui refuse un titre vide et utilise « preventDefault ».
- Une liste affichée avec « textContent », où l'on peut marquer une tâche comme terminée et la supprimer par son identifiant unique.
- Un champ de recherche et un filtre de statut (toutes, en cours, terminées) combinés avec « filter ».
- Une sauvegarde dans localStorage avec JSON, qui survit au rechargement et tolère des données corrompues.
- Au moins 5 tests avec « node:test » sur « ajouter », « basculer », « supprimer » et « filtrer », qui passent avec « node --test ».

Critères de réussite
- La logique métier est dans « taches.js » sans aucun accès à « document ».
- Chaque tâche possède un « id » unique et aucune opération ne s'appuie sur l'indice.
- Un message s'affiche quand la liste ou le résultat de recherche est vide.
- Le README explique comment lancer l'application et les tests.
TXT,
            'exercise_hint' => 'Commence par taches.js et ses tests, puis stockage.js, puis seulement l interface. Après chaque action, mets à jour l état, sauvegarde et appelle afficher.',
            'exercise_solution' => <<<'CODE'
// ===== taches.js =====
export function creerTache(titre) {
  const propre = titre.trim();
  if (propre === "") {
    throw new Error("Le titre de la tâche est obligatoire");
  }
  return { id: crypto.randomUUID(), titre: propre, terminee: false };
}
export const ajouter = (taches, titre) => [...taches, creerTache(titre)];
export const basculer = (taches, id) =>
  taches.map((t) => (t.id === id ? { ...t, terminee: !t.terminee } : t));
export const supprimer = (taches, id) => taches.filter((t) => t.id !== id);
export function filtrer(taches, recherche = "", statut = "toutes") {
  const mot = recherche.trim().toLowerCase();
  return taches.filter((t) => {
    const okTexte = t.titre.toLowerCase().includes(mot);
    const okStatut =
      statut === "toutes" ||
      (statut === "terminees" && t.terminee) ||
      (statut === "en_cours" && !t.terminee);
    return okTexte && okStatut;
  });
}

// ===== stockage.js =====
const CLE = "gestionnaire.taches";
export function charger() {
  try {
    const brut = localStorage.getItem(CLE);
    const donnees = brut ? JSON.parse(brut) : [];
    return Array.isArray(donnees) ? donnees : [];
  } catch {
    return [];
  }
}
export function sauvegarder(taches) {
  localStorage.setItem(CLE, JSON.stringify(taches));
}

// ===== app.js =====
import { ajouter, basculer, supprimer, filtrer } from "./taches.js";
import { charger, sauvegarder } from "./stockage.js";

const formulaire = document.querySelector("#formulaire");
const champ = document.querySelector("#titre");
const recherche = document.querySelector("#recherche");
const statut = document.querySelector("#statut");
const liste = document.querySelector("#liste");
const vide = document.querySelector("#vide");

let taches = charger();

function maj(nouvelles) {
  taches = nouvelles;
  sauvegarder(taches);
  afficher();
}

function afficher() {
  const visibles = filtrer(taches, recherche.value, statut.value);
  liste.replaceChildren();
  vide.hidden = visibles.length > 0;
  for (const tache of visibles) {
    const li = document.createElement("li");
    const texte = document.createElement("span");
    texte.textContent = tache.titre;
    if (tache.terminee) texte.style.textDecoration = "line-through";
    const bascule = document.createElement("button");
    bascule.textContent = tache.terminee ? "Rouvrir" : "Terminer";
    bascule.addEventListener("click", () => maj(basculer(taches, tache.id)));
    const retirer = document.createElement("button");
    retirer.textContent = "Supprimer";
    retirer.addEventListener("click", () => maj(supprimer(taches, tache.id)));
    li.append(texte, bascule, retirer);
    liste.append(li);
  }
}

formulaire.addEventListener("submit", (event) => {
  event.preventDefault();
  try {
    maj(ajouter(taches, champ.value));
    champ.value = "";
  } catch (erreur) {
    alert(erreur.message);
  }
});
recherche.addEventListener("input", afficher);
statut.addEventListener("change", afficher);
afficher();

// ===== taches.test.js =====
// import test from "node:test"; import assert from "node:assert/strict";
// import { ajouter, basculer, supprimer, filtrer } from "./taches.js";
// test("ajouter crée une tâche", () => {
//   assert.equal(ajouter([], "Appeler").length, 1);
// });
// test("ajouter refuse un titre vide", () => {
//   assert.throws(() => ajouter([], "  "), /obligatoire/);
// });
// test("basculer inverse le statut", () => {
//   const l = ajouter([], "A");
//   assert.equal(basculer(l, l[0].id)[0].terminee, true);
// });
// test("supprimer retire par id", () => {
//   const l = ajouter([], "A");
//   assert.equal(supprimer(l, l[0].id).length, 0);
// });
// test("filtrer combine recherche et statut", () => {
//   const l = ajouter(ajouter([], "Livrer"), "Appeler");
//   assert.equal(filtrer(l, "liv", "en_cours").length, 1);
// });
CODE,
        ],
    ],
];
