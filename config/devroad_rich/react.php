<?php

return [
    'lessons' => [

        'Découvrir React' => [
            'description' => 'Comprendre ce qu’est React, pourquoi on décrit l’interface par des composants et comment fonctionne le rendu déclaratif avec JSX.',
            'objective' => 'Expliquer avec tes mots le modèle composant, JSX et rendu déclaratif, puis écrire un petit arbre de trois composants qui affiche une liste.',
            'content' => <<<'MD'
## Pourquoi cette notion

Imagine la page d’accueil d’une boutique en ligne à Abidjan : un en-tête, une grille de produits, un panier qui affiche le nombre d’articles, un bouton « Payer par Wave ». Sans méthode, tu finis par écrire du code qui cherche des éléments dans la page puis les modifie un par un. Dès que l’application grossit, plus personne ne sait quel morceau de code change quel morceau d’écran.

React répond à ce problème. C’est une bibliothèque JavaScript pour construire des interfaces. Tu la retrouves dans la majorité des projets web professionnels, dans les startups comme dans les grandes entreprises, et elle sert aussi de base à d’autres outils comme Next.js que tu verras plus tard. Comprendre son modèle mental est la compétence la plus rentable de toute la formation.

## Les concepts clés

### Le composant

Un composant est une fonction JavaScript qui retourne la description d’un morceau d’interface. Son nom commence toujours par une majuscule. Une application React est un arbre de composants : un composant « Boutique » contient des composants « CarteProduit », qui contiennent eux-mêmes un « Bouton ». Chaque composant a une seule responsabilité claire.

### JSX

JSX est une syntaxe qui ressemble à du HTML mais qui vit dans du JavaScript. Ce n’est pas du HTML : c’est du JavaScript transformé par l’outil de build. Entre accolades, tu peux insérer n’importe quelle expression JavaScript : une variable, un calcul, un appel de fonction. Quelques différences avec HTML : on écrit « className » au lieu de « class », toutes les balises doivent être fermées, et un composant ne peut retourner qu’un seul élément racine, éventuellement un fragment vide.

### Le rendu déclaratif

C’est l’idée centrale. Tu ne dis pas à React comment modifier la page étape par étape. Tu décris à quoi l’écran doit ressembler pour des données précises, et React s’occupe de mettre à jour le navigateur. Les mêmes données produisent toujours le même résultat. Pour afficher une liste, tu ne boucles pas avec des instructions de création d’éléments : tu transformes un tableau de données en tableau d’éléments avec « map ».

### Les clés de liste

Quand tu affiches une liste, chaque élément a besoin d’une propriété « key » stable et unique, par exemple l’identifiant du produit. React s’en sert pour savoir quel élément a changé, a été ajouté ou supprimé.

## Exemple pas à pas

Le code d’exemple suit un parcours en quatre étapes. À l’étape 1, on prépare un tableau de produits : ce sont les données, séparées de l’affichage. À l’étape 2, le composant « CarteProduit » reçoit un produit et décrit une carte avec son nom et son prix en FCFA. À l’étape 3, le composant « ListeProduits » transforme le tableau en éléments grâce à « map » et donne une « key » à chaque carte. À l’étape 4, le composant « App » assemble le tout avec un titre.

## Erreurs fréquentes

- Nommer un composant en minuscule : React le traite comme une balise HTML native et rien ne s’affiche. Commence toujours le nom par une majuscule.
- Écrire « class » au lieu de « className » : le navigateur affiche un avertissement et le style ne s’applique pas. Utilise « className » dans JSX.
- Oublier la « key » dans une liste : React affiche un avertissement et peut mélanger les éléments lors des mises à jour. Utilise un identifiant stable de la donnée.
- Retourner deux éléments voisins sans parent : JSX exige une seule racine. Enveloppe-les dans un fragment ou dans un élément parent.

## Bonnes pratiques

- Un composant, une responsabilité : si tu as du mal à le nommer, il fait probablement trop de choses.
- Garde les données séparées de l’affichage, même dans un petit exemple.
- Donne des noms qui décrivent le métier, comme « CarteProduit », plutôt que des noms techniques vagues.
- Écris des composants prévisibles : mêmes données en entrée, même affichage en sortie.

## Auto-évaluation

- Qu’est-ce qu’un composant et pourquoi son nom commence-t-il par une majuscule ?
- JSX est-il du HTML ? Cite deux différences concrètes.
- Que signifie « rendu déclaratif » par opposition à une approche où l’on modifie la page étape par étape ?
- À quoi sert la propriété « key » et que choisir comme valeur ?
- Comment transformer un tableau de données en liste d’éléments à l’écran ?

## À retenir

- React construit l’interface avec un arbre de composants, chacun étant une fonction qui retourne du JSX.
- JSX est du JavaScript : les accolades permettent d’y insérer des expressions.
- Tu décris le résultat voulu, React met le navigateur à jour.
- Une liste s’affiche avec « map » et une « key » stable pour chaque élément.
- Un bon composant est petit, nommé selon le métier et prévisible.
MD,
            'code_example' => <<<'CODE'
// Étape 1 : les données, séparées de l'affichage
const produits = [
  { id: 1, nom: 'Pagne wax', prix: 7500 },
  { id: 2, nom: 'Sac en cuir', prix: 15000 },
  { id: 3, nom: 'Sandales artisanales', prix: 9000 },
];

// Étape 2 : un composant qui décrit UNE carte produit
function CarteProduit({ produit }) {
  return (
    <article className="carte">
      <h3>{produit.nom}</h3>
      {/* Les accolades insèrent une expression JavaScript */}
      <p>{produit.prix.toLocaleString('fr-FR')} FCFA</p>
    </article>
  );
}

// Étape 3 : un composant qui transforme le tableau en éléments
function ListeProduits({ produits }) {
  return (
    <section>
      {produits.map((produit) => (
        // La key est unique et stable : l'identifiant du produit
        <CarteProduit key={produit.id} produit={produit} />
      ))}
    </section>
  );
}

// Étape 4 : le composant racine assemble l'arbre
export default function App() {
  return (
    <main>
      <h1>Ma boutique</h1>
      <ListeProduits produits={produits} />
    </main>
  );
}
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Afficher le menu d’un maquis',
            'exercise_description' => <<<'TXT'
Tu dois afficher le menu d’un maquis sous forme de composants React. Crée un tableau d’au moins quatre plats, chacun avec un identifiant, un nom, un prix en FCFA et un booléen « disponible ». Crée un composant « Plat » qui affiche un plat et un composant « Menu » qui affiche tous les plats, puis assemble-les dans « App » avec un titre.

Critères de réussite :
- Les données sont dans un tableau séparé des composants.
- Il existe au moins deux composants dont le nom commence par une majuscule : « Plat » et « Menu ».
- La liste est produite avec « map » et chaque élément a une « key » basée sur l’identifiant.
- Le prix est affiché avec le suffixe « FCFA » et un plat indisponible affiche le texte « Indisponible ».
- Le composant « App » retourne un seul élément racine.
TXT,
            'exercise_hint' => 'Pour afficher un texte différent selon le booléen, utilise une expression conditionnelle entre accolades, par exemple « plat.disponible ? ... : ... ». Pense à passer le plat entier en prop au composant « Plat ».',
            'exercise_solution' => <<<'CODE'
// Données séparées de l'affichage
const plats = [
  { id: 1, nom: 'Poulet braisé', prix: 3500, disponible: true },
  { id: 2, nom: 'Poisson capitaine', prix: 5000, disponible: true },
  { id: 3, nom: 'Attiéké garba', prix: 1500, disponible: false },
  { id: 4, nom: 'Alloco sauce', prix: 2000, disponible: true },
];

// Un composant pour un plat
function Plat({ plat }) {
  return (
    <li>
      <strong>{plat.nom}</strong> : {plat.prix.toLocaleString('fr-FR')} FCFA
      {/* Texte conditionnel selon la disponibilité */}
      {plat.disponible ? null : <em> Indisponible</em>}
    </li>
  );
}

// Un composant pour la liste complète
function Menu({ plats }) {
  return (
    <ul>
      {plats.map((plat) => (
        <Plat key={plat.id} plat={plat} />
      ))}
    </ul>
  );
}

// Racine : un seul élément parent
export default function App() {
  return (
    <main>
      <h1>Menu du maquis</h1>
      <Menu plats={plats} />
    </main>
  );
}
CODE,
        ],

        'Créer un projet React' => [
            'description' => 'Créer une application React avec Vite, la lancer en local et comprendre le rôle de chaque fichier important du projet.',
            'objective' => 'Créer un projet Vite, le lancer, repérer le point d’entrée et ajouter un composant dans une structure de dossiers propre.',
            'content' => <<<'MD'
## Pourquoi cette notion

Avant d’écrire la moindre ligne de React, il faut un environnement qui transforme ton JSX en code compréhensible par le navigateur, qui recharge la page à chaque modification et qui produit une version optimisée pour la mise en ligne. En entreprise, chaque projet commence par cette étape, et un développeur qui ne comprend pas la structure de son projet perd un temps énorme dès qu’une erreur de configuration apparaît.

## Les concepts clés

### Node.js et npm

Vite s’exécute avec Node.js, un environnement qui fait tourner JavaScript hors du navigateur. Avec Node vient npm, le gestionnaire de paquets. Il télécharge les bibliothèques dont ton projet dépend et les range dans le dossier « node_modules ». Ce dossier est volumineux et se régénère : on ne le partage jamais avec Git.

### Le fichier package.json

C’est la carte d’identité du projet. Il liste les dépendances et les scripts. Les scripts les plus courants sont « dev » pour lancer le serveur de développement, « build » pour fabriquer la version de production et « preview » pour tester cette version localement. Tu les lances avec « npm run » suivi du nom du script.

### Le point d’entrée

Le fichier « index.html » à la racine contient une simple balise vide, généralement avec l’identifiant « root », et charge le fichier « src/main.jsx ». Ce dernier crée une racine React avec « createRoot » sur cet élément, puis demande l’affichage du composant « App ». C’est le seul endroit où React se branche sur le DOM de la page. Tout le reste de l’application est une composition de composants.

### Organiser les dossiers

## Exemple pas à pas

Le code d’exemple regroupe la commande de création, puis les deux fichiers essentiels. À l’étape 1, tu crées le projet avec la commande Vite en choisissant le modèle React, tu entres dans le dossier et tu installes les dépendances. À l’étape 2, tu lances le serveur de développement et tu ouvres l’adresse affichée dans le terminal. À l’étape 3, tu lis le point d’entrée : il importe React, le composant « App » et monte l’application dans l’élément « root ». À l’étape 4, tu crées un composant « EnTete » dans le dossier « components » et tu l’utilises dans « App ».

## Erreurs fréquentes

- Lancer « npm run dev » dans le mauvais dossier : tu obtiens une erreur sur un fichier package.json introuvable. Place-toi dans le dossier du projet avec la commande « cd ».
- Oublier « npm install » après avoir cloné ou créé le projet : les modules manquent et le serveur ne démarre pas. Lance l’installation une fois les fichiers présents.
- Nommer un fichier de composant avec une extension « .js » alors qu’il contient du JSX : selon la configuration, l’outil refuse de le compiler. Utilise « .jsx » pour les fichiers contenant du JSX.
- Faire une faute de casse dans un import, comme « entete » au lieu de « EnTete » : cela fonctionne parfois sur un système et plante sur un autre. Respecte exactement la casse du nom de fichier.
- Versionner le dossier « node_modules » : le dépôt devient énorme. Vérifie que « node_modules » figure dans le fichier « .gitignore ».

## Bonnes pratiques

- Lis le terminal : le serveur y affiche les erreurs de compilation avec le fichier et la ligne concernés.
- Fais un commit Git dès que le projet démarre, avant toute modification.
- Une extension de fichier cohérente et un nom de composant identique au nom du fichier facilitent la navigation.
- Supprime le code de démonstration généré par le modèle pour partir d’une base propre.

## Auto-évaluation

- Quelle commande crée un projet Vite avec le modèle React ?
- Que contient le dossier « node_modules » et pourquoi ne le partage-t-on pas ?
- À quoi servent les scripts « dev », « build » et « preview » ?
- Quel fichier relie React à la page HTML et que fait « createRoot » ?
- Comment organiser les premiers dossiers de « src » ?

## À retenir

- Vite fournit le serveur de développement et le build de production.
- Le fichier package.json décrit les dépendances et les scripts du projet.
- Le point d’entrée monte le composant « App » dans l’élément « root ».
- Le dossier « node_modules » se régénère et ne se versionne pas.
- Une structure simple avec « components » et « pages » suffit pour commencer.
MD,
            'code_example' => <<<'CODE'
// Étape 1 : à lancer dans le terminal (pas dans un fichier JS)
//   npm create vite@latest boutique-react -- --template react
//   cd boutique-react
//   npm install
// Étape 2 : démarrer le serveur de développement
//   npm run dev

// ===== src/main.jsx : le point d'entrée (étape 3) =====
import { StrictMode } from 'react';
import { createRoot } from 'react-dom/client';
import App from './App.jsx';

// On monte l'application dans <div id="root"> de index.html
createRoot(document.getElementById('root')).render(
  <StrictMode>
    <App />
  </StrictMode>
);

// ===== src/components/EnTete.jsx (étape 4) =====
export default function EnTete({ nomBoutique }) {
  return (
    <header>
      <h1>{nomBoutique}</h1>
      <p>Livraison à Abidjan et alentours</p>
    </header>
  );
}

// ===== src/App.jsx =====
import EnTete from './components/EnTete.jsx';

export default function App() {
  return (
    <main>
      {/* Modifie ce texte et regarde la page se mettre à jour */}
      <EnTete nomBoutique="Boutique Akwaba" />
    </main>
  );
}
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Poser la base du projet d’une école',
            'exercise_description' => <<<'TXT'
Crée un projet Vite nommé « ecole-react » avec le modèle React et prépare une base propre pour une application de gestion d’école. Supprime le contenu de démonstration, crée un dossier « components » avec deux composants « EnTete » et « PiedDePage », puis affiche-les dans « App » avec le nom de l’école reçu en prop.

Critères de réussite :
- Le projet démarre avec « npm run dev » sans erreur ni avertissement dans la console.
- Le dossier « src/components » contient « EnTete.jsx » et « PiedDePage.jsx », chacun exporté par défaut.
- Le composant « App » importe et utilise les deux composants.
- Le nom de l’école est passé en prop et affiché dans l’en-tête et dans le pied de page.
- Le fichier « .gitignore » contient « node_modules » et un premier commit Git est réalisé.
TXT,
            'exercise_hint' => 'Les commandes de création se tapent dans le terminal. Pour les composants, copie la structure de l’exemple : une fonction qui reçoit un objet de props et retourne du JSX. Pense à l’extension « .jsx » dans les imports.',
            'exercise_solution' => <<<'CODE'
// Commandes à taper dans le terminal :
//   npm create vite@latest ecole-react -- --template react
//   cd ecole-react
//   npm install
//   git init && git add . && git commit -m "Base du projet"
//   npm run dev

// ===== src/components/EnTete.jsx =====
export default function EnTete({ nomEcole }) {
  return (
    <header>
      <h1>{nomEcole}</h1>
      <p>Gestion des classes et des notes</p>
    </header>
  );
}

// ===== src/components/PiedDePage.jsx =====
export default function PiedDePage({ nomEcole }) {
  return (
    <footer>
      <small>© {new Date().getFullYear()} {nomEcole}</small>
    </footer>
  );
}

// ===== src/App.jsx (contenu de démonstration supprimé) =====
import EnTete from './components/EnTete.jsx';
import PiedDePage from './components/PiedDePage.jsx';

export default function App() {
  const nomEcole = 'Collège Les Palmiers';

  return (
    <>
      <EnTete nomEcole={nomEcole} />
      <main>
        <p>Bienvenue sur le portail de l'école.</p>
      </main>
      <PiedDePage nomEcole={nomEcole} />
    </>
  );
}
CODE,
        ],

        'Composants et props' => [
            'description' => 'Découper une interface en composants réutilisables et leur transmettre des données grâce aux props, y compris le contenu imbriqué avec children.',
            'objective' => 'Créer des composants réutilisables paramétrés par des props, avec valeurs par défaut et contenu imbriqué, sans dupliquer de code.',
            'content' => <<<'MD'
## Pourquoi cette notion

Dans une application de réservation d’hôtel, la même carte de chambre apparaît dans la liste, dans les résultats de recherche et dans la page d’accueil. Si tu recopies le code trois fois, la moindre correction doit être refaite trois fois, et tu en oublies forcément une. Les composants paramétrés par des props règlent ce problème : tu écris la carte une seule fois et tu lui donnes des données différentes à chaque usage.

C’est la base de toute interface React professionnelle. Les bibliothèques de composants que tu verras dans les projets d’entreprise reposent entièrement sur ce mécanisme.

## Les concepts clés

### Les props

Les props sont les données qu’un composant reçoit de son parent. Elles arrivent sous la forme d’un seul objet passé en paramètre de la fonction. Tu écris les valeurs comme des attributs : un texte entre guillemets, une expression JavaScript entre accolades. Le composant lit ces valeurs mais ne les modifie jamais : les props sont en lecture seule. C’est ce qui rend le composant prévisible.

### La déstructuration et les valeurs par défaut

Plutôt que d’écrire « props.nom » partout, on déstructure l’objet dans la signature de la fonction. On peut y définir une valeur par défaut avec le signe égal, utilisée quand le parent ne fournit pas la prop. Cela évite les affichages « undefined » à l’écran.

### Le contenu imbriqué avec children

Quand tu places du contenu entre la balise ouvrante et la balise fermante d’un composant, ce contenu arrive dans la prop spéciale « children ». C’est le moyen de créer des conteneurs génériques, comme une carte ou un encadré, qui encadrent n’importe quel contenu sans le connaître à l’avance.

### Découper une interface

Pour décider d’un découpage, cherche les parties qui se répètent et celles qui ont une responsabilité distincte. Une règle utile : si un bloc a un nom naturel dans le métier, comme « carte de chambre » ou « ligne de commande », c’est probablement un composant. Évite l’excès inverse : un composant pour chaque balise rend le code illisible.

### Le sens du flux de données

Les données descendent du parent vers l’enfant. Un enfant ne modifie pas directement les données de son parent. Quand il doit signaler quelque chose, le parent lui passe une fonction en prop, que l’enfant appelle. Tu approfondiras ce mécanisme avec le state.

## Exemple pas à pas

Dans le code d’exemple, l’étape 1 crée un composant « Badge » avec une valeur par défaut pour la couleur. L’étape 2 crée « CarteChambre », qui reçoit le nom, le prix par nuit et un indicateur de disponibilité, et réutilise « Badge ». L’étape 3 crée un conteneur « Encadre » qui affiche ses « children ». L’étape 4 assemble le tout : la même « CarteChambre » est utilisée trois fois avec des données différentes, et un « Encadre » enveloppe une information de pratique.

## Erreurs fréquentes

- Modifier une prop à l’intérieur du composant : React ne l’autorise pas et le comportement devient imprévisible. Copie la valeur dans une variable locale ou remonte la décision au parent.
- Passer un nombre entre guillemets, comme « prix="5000" » : tu reçois une chaîne de caractères et les calculs se comportent mal. Utilise les accolades : « prix={5000} ».
- Oublier les accolades de déstructuration dans la signature : le composant reçoit l’objet entier et « nom » vaut « undefined ». Écris « function Carte({ nom }) ».
- Dupliquer un composant presque identique avec de petites différences : le code diverge avec le temps. Ajoute une prop pour exprimer la différence.
- Passer des dizaines de props à un composant : c’est le signe qu’il fait trop de choses. Découpe-le ou regroupe les données liées dans un objet.

## Bonnes pratiques

- Donne aux props des noms qui décrivent leur rôle, comme « prixParNuit » plutôt que « p ».
- Définis des valeurs par défaut raisonnables pour les props facultatives.
- Garde les composants purs : les mêmes props donnent le même rendu, sans effet caché.
- Utilise « children » pour les conteneurs génériques plutôt que d’ajouter beaucoup de props de contenu.

## Auto-évaluation

- Qu’est-ce qu’une prop et pourquoi est-elle en lecture seule ?
- Comment définir une valeur par défaut pour une prop ?
- À quoi sert la prop « children » ?
- Comment un enfant peut-il prévenir son parent qu’un événement s’est produit ?
- Comment reconnaître qu’un bloc d’interface mérite de devenir un composant ?

## À retenir

- Les props sont les entrées d’un composant, transmises par le parent.
- Elles ne se modifient jamais depuis l’enfant.
- La déstructuration avec valeurs par défaut rend le code clair et robuste.
- « children » permet de créer des conteneurs génériques.
- Un bon découpage supprime la duplication sans multiplier les composants inutiles.
MD,
            'code_example' => <<<'CODE'
// Étape 1 : un petit composant avec une valeur par défaut
function Badge({ texte, couleur = 'gray' }) {
  return (
    <span style={{ background: couleur, color: 'white', padding: '2px 8px' }}>
      {texte}
    </span>
  );
}

// Étape 2 : une carte réutilisable, pilotée uniquement par ses props
function CarteChambre({ nom, prixParNuit, disponible }) {
  return (
    <article>
      <h3>{nom}</h3>
      <p>{prixParNuit.toLocaleString('fr-FR')} FCFA / nuit</p>
      {/* On réutilise Badge avec des props différentes */}
      {disponible ? (
        <Badge texte="Disponible" couleur="green" />
      ) : (
        <Badge texte="Complet" couleur="crimson" />
      )}
    </article>
  );
}

// Étape 3 : un conteneur générique qui affiche ses enfants
function Encadre({ titre, children }) {
  return (
    <aside style={{ border: '1px solid #ccc', padding: 12 }}>
      <strong>{titre}</strong>
      <div>{children}</div>
    </aside>
  );
}

// Étape 4 : assemblage, la même carte est utilisée trois fois
export default function App() {
  return (
    <main>
      <h1>Hôtel Akwaba</h1>
      <CarteChambre nom="Chambre simple" prixParNuit={25000} disponible />
      <CarteChambre nom="Chambre double" prixParNuit={40000} disponible={false} />
      <CarteChambre nom="Suite" prixParNuit={85000} disponible />
      <Encadre titre="Bon à savoir">
        <p>Petit-déjeuner inclus pour toute réservation de deux nuits.</p>
      </Encadre>
    </main>
  );
}
CODE,
            'estimated_minutes' => 55,
            'exercise_title' => 'Cartes de cours pour une école en ligne',
            'exercise_description' => <<<'TXT'
Construis l’affichage du catalogue d’une école en ligne. Crée un composant « CarteCours » qui reçoit un titre, une durée en heures, un prix en FCFA et un niveau facultatif (« Débutant » par défaut). Crée aussi un composant « Section » qui utilise « children » pour afficher un titre suivi de son contenu. Affiche au moins trois cours dans une « Section ».

Critères de réussite :
- « CarteCours » est écrit une seule fois et utilisé au moins trois fois avec des props différentes.
- La prop « niveau » a une valeur par défaut « Débutant » visible quand elle n’est pas fournie.
- Le prix est passé comme un nombre avec les accolades et affiché formaté avec « FCFA ».
- « Section » affiche son contenu grâce à « children ».
- Aucun composant ne modifie ses props et aucun composant n’est défini à l’intérieur d’un autre.
TXT,
            'exercise_hint' => 'Écris la signature avec déstructuration et valeur par défaut : « function CarteCours({ titre, duree, prix, niveau = ... }) ». Pour « Section », déstructure « titre » et « children » puis place « children » dans le JSX.',
            'exercise_solution' => <<<'CODE'
// Conteneur générique avec children
function Section({ titre, children }) {
  return (
    <section>
      <h2>{titre}</h2>
      <div>{children}</div>
    </section>
  );
}

// Carte réutilisable ; "niveau" vaut "Débutant" par défaut
function CarteCours({ titre, duree, prix, niveau = 'Débutant' }) {
  return (
    <article>
      <h3>{titre}</h3>
      <p>Niveau : {niveau}</p>
      <p>Durée : {duree} h</p>
      <p>Prix : {prix.toLocaleString('fr-FR')} FCFA</p>
    </article>
  );
}

export default function App() {
  return (
    <main>
      <h1>Catalogue des cours</h1>
      <Section titre="Cours les plus suivis">
        {/* Même composant, trois jeux de props différents */}
        <CarteCours titre="HTML et CSS" duree={12} prix={15000} />
        <CarteCours titre="JavaScript" duree={20} prix={25000} niveau="Intermédiaire" />
        <CarteCours titre="React" duree={24} prix={30000} niveau="Intermédiaire" />
      </Section>
    </main>
  );
}
CODE,
        ],

        'State et événements' => [
            'description' => 'Gérer les interactions de l’utilisateur avec useState et les gestionnaires d’événements, et comprendre pourquoi un changement de state déclenche un nouveau rendu.',
            'objective' => 'Utiliser useState pour un compteur, un champ contrôlé et une liste, en respectant l’immutabilité et la mise à jour fonctionnelle.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une interface qui ne réagit à rien n’est qu’une affiche. Dès qu’un utilisateur ajoute un article au panier, tape son numéro de téléphone ou coche une tâche, l’application doit se souvenir de quelque chose et se redessiner. Ce « quelque chose » s’appelle le state. Tu le rencontres dans tous les formulaires, tous les paniers, tous les filtres d’une application professionnelle.

Comprendre le state correctement, c’est éviter la source la plus fréquente de bugs chez les débutants : un affichage qui ne se met pas à jour, ou qui se met à jour avec une valeur périmée.

## Les concepts clés

### useState

Le hook « useState » déclare une donnée mémorisée par le composant. Il reçoit la valeur initiale et retourne un tableau de deux éléments : la valeur actuelle et une fonction pour la modifier. Par convention, on les nomme « valeur » et « setValeur ». Appeler la fonction de mise à jour demande à React de refaire le rendu du composant avec la nouvelle valeur.

### Le state est un instantané

À chaque rendu, le composant reçoit une valeur de state figée. Si tu écris une mise à jour puis lis immédiatement la variable, tu lis encore l’ancienne valeur : la nouvelle ne sera disponible qu’au prochain rendu. Pour calculer la valeur suivante à partir de la précédente, passe une fonction à la mise à jour, qui reçoit l’ancienne valeur et retourne la nouvelle. C’est la mise à jour fonctionnelle, indispensable quand plusieurs mises à jour s’enchaînent.

### L’immutabilité

Tu ne modifies jamais directement un objet ou un tableau stocké dans le state. Tu crées une copie modifiée et tu la passes à la fonction de mise à jour. Pour ajouter un élément, tu utilises l’opérateur de décomposition. Pour retirer un élément, tu utilises « filter ». Pour modifier un élément, tu utilises « map ». React compare les références : si tu modifies l’objet en place, la référence reste identique et React ne voit aucun changement.

### Les événements et les champs contrôlés

Tu branches un gestionnaire avec une propriété comme « onClick » ou « onChange », en lui passant une fonction, pas le résultat d’un appel. Un champ contrôlé est un champ de formulaire dont la valeur vient du state : le champ affiche la valeur du state, et chaque frappe met le state à jour. Le state devient la source de vérité du formulaire.

## Exemple pas à pas

Le code d’exemple construit un petit panier. À l’étape 1, on déclare le state du panier, un tableau, et celui du champ de saisie, une chaîne. À l’étape 2, la fonction d’ajout crée un nouveau tableau avec l’opérateur de décomposition. À l’étape 3, la suppression utilise « filter ». À l’étape 4, un compteur de quantité utilise la mise à jour fonctionnelle. À l’étape 5, le JSX affiche le champ contrôlé, les boutons et la liste, avec un total calculé à chaque rendu sans le stocker.

## Erreurs fréquentes

- Modifier un tableau avec « push » puis appeler la mise à jour avec le même tableau : React ne détecte rien. Crée toujours une copie.
- Écrire « onClick={ajouter()} » : la fonction s’exécute au rendu et boucle. Passe la fonction elle-même, « onClick={ajouter} », ou une flèche.
- Lire le state juste après la mise à jour en espérant la nouvelle valeur : tu lis l’ancienne. Utilise une variable locale ou la mise à jour fonctionnelle.
- Stocker dans le state une valeur calculable, comme un total : tu dois ensuite la synchroniser à la main. Calcule-la pendant le rendu.
- Oublier « value » ou « onChange » sur un champ : le champ devient figé ou non contrôlé. Branche les deux.

## Bonnes pratiques

- Garde le state minimal : ne stocke que ce qui ne peut pas être calculé.
- Traite tout objet du state comme en lecture seule et crée des copies.
- Utilise la mise à jour fonctionnelle dès que la nouvelle valeur dépend de l’ancienne.
- Remonte le state vers le parent commun quand deux composants doivent le partager.

## Auto-évaluation

- Que retourne « useState » et que fait la fonction de mise à jour ?
- Pourquoi ne faut-il pas modifier un tableau du state avec « push » ?
- Quelle différence entre « onClick={f} » et « onClick={f()} » ?
- Qu’est-ce qu’un champ contrôlé ?
- Quand utiliser la mise à jour fonctionnelle du state ?

## À retenir

- Le state est la mémoire du composant, et le modifier déclenche un nouveau rendu.
- À chaque rendu, le state est un instantané figé.
- Les tableaux et objets du state se remplacent par des copies, jamais par une modification en place.
- Un champ contrôlé lie sa valeur au state et met à jour le state à chaque saisie.
- Une valeur calculable ne se stocke pas, elle se calcule au rendu.
MD,
            'code_example' => <<<'CODE'
import { useState } from 'react';

export default function Panier() {
  // Étape 1 : deux morceaux de state, un tableau et une chaîne
  const [articles, setArticles] = useState([]);
  const [nom, setNom] = useState('');

  // Étape 2 : ajout sans modifier le tableau d'origine
  function ajouter(e) {
    e.preventDefault(); // évite le rechargement de la page
    if (nom.trim() === '') return;
    setArticles((precedents) => [
      ...precedents,
      { id: Date.now(), nom: nom.trim(), prix: 2500, quantite: 1 },
    ]);
    setNom(''); // on vide le champ
  }

  // Étape 3 : suppression avec filter
  function retirer(id) {
    setArticles((precedents) => precedents.filter((a) => a.id !== id));
  }

  // Étape 4 : modification d'un élément avec map (mise à jour fonctionnelle)
  function changerQuantite(id, delta) {
    setArticles((precedents) =>
      precedents.map((a) =>
        a.id === id ? { ...a, quantite: Math.max(1, a.quantite + delta) } : a
      )
    );
  }

  // Le total est calculé au rendu, pas stocké dans le state
  const total = articles.reduce((somme, a) => somme + a.prix * a.quantite, 0);

  // Étape 5 : affichage avec champ contrôlé
  return (
    <section>
      <form onSubmit={ajouter}>
        <input value={nom} onChange={(e) => setNom(e.target.value)} placeholder="Article" />
        <button type="submit">Ajouter</button>
      </form>
      <ul>
        {articles.map((a) => (
          <li key={a.id}>
            {a.nom} x {a.quantite}
            <button onClick={() => changerQuantite(a.id, 1)}>+</button>
            <button onClick={() => changerQuantite(a.id, -1)}>-</button>
            <button onClick={() => retirer(a.id)}>Retirer</button>
          </li>
        ))}
      </ul>
      <p>Total : {total.toLocaleString('fr-FR')} FCFA</p>
    </section>
  );
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Liste de courses interactive',
            'exercise_description' => <<<'TXT'
Construis une liste de courses pour le marché. L’utilisateur saisit un article dans un champ, valide avec un bouton, l’article apparaît dans la liste. Chaque article peut être coché comme « acheté » et supprimé. Affiche aussi le nombre d’articles restant à acheter.

Critères de réussite :
- Le champ de saisie est contrôlé par le state et se vide après l’ajout.
- Un article vide ou composé uniquement d’espaces n’est pas ajouté.
- Cocher un article modifie uniquement cet article, sans modifier le tableau d’origine en place.
- La suppression utilise « filter » et le nombre d’articles restants est calculé au rendu, pas stocké dans le state.
- Les mises à jour qui dépendent de l’ancienne liste utilisent la forme fonctionnelle.
TXT,
            'exercise_hint' => 'Représente chaque article par un objet avec « id », « nom » et « achete ». Pour cocher, utilise « map » et retourne une copie de l’objet avec « achete » inversé. Pour le compteur, utilise « filter » puis « length » pendant le rendu.',
            'exercise_solution' => <<<'CODE'
import { useState } from 'react';

export default function ListeCourses() {
  const [articles, setArticles] = useState([]);
  const [nom, setNom] = useState('');

  function ajouter(e) {
    e.preventDefault();
    // Refuse les valeurs vides
    if (nom.trim() === '') return;
    setArticles((precedents) => [
      ...precedents,
      { id: Date.now(), nom: nom.trim(), achete: false },
    ]);
    setNom('');
  }

  // Inverse "achete" pour un seul article, sans modifier l'original
  function basculer(id) {
    setArticles((precedents) =>
      precedents.map((a) => (a.id === id ? { ...a, achete: !a.achete } : a))
    );
  }

  function supprimer(id) {
    setArticles((precedents) => precedents.filter((a) => a.id !== id));
  }

  // Valeur dérivée : calculée au rendu
  const restants = articles.filter((a) => !a.achete).length;

  return (
    <main>
      <h1>Courses du marché</h1>
      <form onSubmit={ajouter}>
        <input
          value={nom}
          onChange={(e) => setNom(e.target.value)}
          placeholder="Ex : tomates"
        />
        <button type="submit">Ajouter</button>
      </form>
      <ul>
        {articles.map((a) => (
          <li key={a.id}>
            <label style={{ textDecoration: a.achete ? 'line-through' : 'none' }}>
              <input type="checkbox" checked={a.achete} onChange={() => basculer(a.id)} />
              {a.nom}
            </label>
            <button onClick={() => supprimer(a.id)}>Supprimer</button>
          </li>
        ))}
      </ul>
      <p>{restants} article(s) restant(s) à acheter</p>
    </main>
  );
}
CODE,
        ],

        'Effets et données' => [
            'description' => 'Comprendre useEffect comme outil de synchronisation avec le monde extérieur, charger des données avec fetch et gérer les états de chargement et d’erreur.',
            'objective' => 'Charger des données depuis une API avec useEffect, gérer chargement, erreur et nettoyage, et savoir reconnaître un effet inutile.',
            'content' => <<<'MD'
## Pourquoi cette notion

Presque toute application réelle affiche des données qui ne viennent pas d’elle : la liste des produits d’un serveur, le solde d’un compte, les horaires d’un bus. Charger ces données, les afficher pendant l’attente et gérer l’échec du réseau sont des tâches quotidiennes. Sur une connexion mobile instable, comme c’est souvent le cas, gérer correctement l’attente et l’erreur fait la différence entre une application utilisable et une application qui paraît cassée.

C’est ici qu’intervient useEffect, un hook souvent mal compris et très souvent mal utilisé.

## Les concepts clés

### À quoi sert useEffect

Le rendu d’un composant doit rester pur : il calcule du JSX à partir du state et des props, rien d’autre. Un effet est le code qui s’exécute après l’affichage pour se synchroniser avec quelque chose d’extérieur à React : un appel réseau, un abonnement, un minuteur, le titre de l’onglet du navigateur. Si ton code ne synchronise rien d’extérieur, tu n’as probablement pas besoin d’effet.

### Le tableau de dépendances

useEffect reçoit une fonction et un tableau de dépendances. Avec un tableau vide, l’effet s’exécute une seule fois après le premier affichage. Avec des valeurs dans le tableau, il se relance chaque fois qu’une de ces valeurs change. Sans tableau, il s’exécute après chaque rendu, ce qui est rarement voulu. Toute valeur du composant utilisée dans l’effet doit figurer dans la liste.

### Le nettoyage

La fonction d’un effet peut retourner une fonction de nettoyage. React l’appelle avant de relancer l’effet et lorsque le composant disparaît. Pour un appel réseau, elle sert à ignorer une réponse devenue inutile, par exemple quand l’utilisateur a changé de page avant l’arrivée de la réponse. Sans cela, une ancienne réponse peut écraser une nouvelle.

### Les trois états d’une requête

Une requête a toujours trois situations : en cours, réussie, échouée. Tu les représentes avec trois morceaux de state : les données, un indicateur de chargement et un message d’erreur. L’interface affiche un message d’attente, puis soit les données, soit l’erreur avec une possibilité de réessayer. Pense aussi à vérifier « response.ok », car fetch ne lève pas d’exception pour une réponse 404 ou 500.

### Les effets inutiles

Ne mets pas dans un effet ce qui peut être calculé pendant le rendu, comme filtrer une liste, ni ce qui répond à un clic, qui doit rester dans le gestionnaire d’événement. Pour les données d’une application sérieuse, une bibliothèque dédiée ou le chargement côté serveur d’un framework évite beaucoup de cas limites.

## Exemple pas à pas

Le code d’exemple charge des produits. À l’étape 1, on déclare trois états : produits, chargement et erreur. À l’étape 2, l’effet crée un indicateur « annule » et lance la requête. À l’étape 3, on vérifie « response.ok » avant de lire le JSON, et on ne met à jour le state que si « annule » est resté faux. À l’étape 4, la fonction de nettoyage passe « annule » à vrai. À l’étape 5, le rendu choisit entre message d’attente, message d’erreur et liste. Une catégorie en dépendance relance le chargement quand l’utilisateur la change.

## Erreurs fréquentes

- Oublier le tableau de dépendances : l’effet tourne à chaque rendu et peut créer une boucle infinie de requêtes. Ajoute toujours le tableau.
- Mettre dans l’effet une valeur sans la déclarer en dépendance : l’effet utilise une donnée périmée. Liste toutes les valeurs utilisées.
- Ne pas gérer l’erreur : l’écran reste bloqué sur « Chargement ». Utilise « catch » et un state d’erreur.
- Ignorer « response.ok » : une erreur 500 est traitée comme un succès. Vérifie le statut avant de lire les données.
- Oublier le nettoyage : une réponse tardive écrase une réponse récente. Utilise un indicateur d’annulation ou « AbortController ».
- Utiliser un effet pour filtrer une liste du state : tu crées un rendu supplémentaire inutile. Calcule le résultat pendant le rendu.

## Bonnes pratiques

- Modélise toujours chargement, erreur et succès, même pour un petit écran.
- Affiche un message clair en français et propose un bouton pour réessayer.
- Sépare la fonction d’appel réseau du composant pour pouvoir la réutiliser.
- Demande-toi d’abord si un effet est vraiment nécessaire avant d’en écrire un.
- Garde les effets petits, avec une seule responsabilité chacun.

## Auto-évaluation

- Quand faut-il utiliser useEffect et quand faut-il l’éviter ?
- Que se passe-t-il avec un tableau de dépendances vide, rempli ou absent ?
- À quoi sert la fonction de nettoyage ?
- Quels trois états gérer pour une requête réseau ?
- Pourquoi vérifier « response.ok » avec fetch ?

## À retenir

- Un effet synchronise le composant avec le monde extérieur après l’affichage.
- Le tableau de dépendances décide quand l’effet se relance.
- Le nettoyage évite les réponses périmées et les fuites.
- Une requête a trois états : chargement, succès, erreur.
- Ce qui peut être calculé au rendu ne doit pas passer par un effet.
MD,
            'code_example' => <<<'CODE'
import { useEffect, useState } from 'react';

export default function ListeProduits({ categorie }) {
  // Étape 1 : trois états pour une requête
  const [produits, setProduits] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    // Étape 2 : indicateur pour ignorer une réponse devenue inutile
    let annule = false;
    setChargement(true);
    setErreur(null);

    fetch(`/api/produits?categorie=${encodeURIComponent(categorie)}`)
      .then((response) => {
        // Étape 3 : fetch ne lève pas d'erreur pour un 404 ou un 500
        if (!response.ok) throw new Error('Le serveur a répondu avec une erreur');
        return response.json();
      })
      .then((data) => {
        if (!annule) setProduits(data);
      })
      .catch((e) => {
        if (!annule) setErreur(e.message);
      })
      .finally(() => {
        if (!annule) setChargement(false);
      });

    // Étape 4 : nettoyage appelé avant la relance ou au démontage
    return () => {
      annule = true;
    };
    // La catégorie est utilisée dans l'effet : elle est une dépendance
  }, [categorie]);

  // Étape 5 : on choisit quoi afficher selon l'état
  if (chargement) return <p>Chargement en cours...</p>;
  if (erreur) return <p role="alert">Impossible de charger : {erreur}</p>;

  return (
    <ul>
      {produits.map((p) => (
        <li key={p.id}>{p.nom}</li>
      ))}
    </ul>
  );
}
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Annuaire de pharmacies de garde',
            'exercise_description' => <<<'TXT'
Crée un composant « PharmaciesDeGarde » qui charge une liste depuis l’adresse « /api/pharmacies?commune=... ». Un menu déroulant permet de choisir une commune (Cocody, Yopougon, Marcory) et relance le chargement à chaque changement. Gère les trois états de la requête et propose un bouton « Réessayer » en cas d’erreur.

Critères de réussite :
- Trois états distincts sont gérés : chargement, erreur et liste affichée.
- La commune choisie est une dépendance de l’effet et un changement relance la requête.
- Le statut HTTP est vérifié avec « response.ok » avant de lire le JSON.
- Une fonction de nettoyage empêche une réponse ancienne de remplacer une réponse récente.
- Le bouton « Réessayer » relance la requête sans recharger la page.
TXT,
            'exercise_hint' => 'Pour « Réessayer », ajoute un state numérique « tentative » dans les dépendances de l’effet et incrémente-le au clic. N’oublie pas de remettre « erreur » à null au début de l’effet.',
            'exercise_solution' => <<<'CODE'
import { useEffect, useState } from 'react';

const COMMUNES = ['Cocody', 'Yopougon', 'Marcory'];

export default function PharmaciesDeGarde() {
  const [commune, setCommune] = useState(COMMUNES[0]);
  const [pharmacies, setPharmacies] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);
  // Incrémenté pour forcer une nouvelle requête
  const [tentative, setTentative] = useState(0);

  useEffect(() => {
    let annule = false;
    setChargement(true);
    setErreur(null);

    fetch(`/api/pharmacies?commune=${encodeURIComponent(commune)}`)
      .then((response) => {
        if (!response.ok) throw new Error('Erreur du serveur');
        return response.json();
      })
      .then((data) => {
        if (!annule) setPharmacies(data);
      })
      .catch((e) => {
        if (!annule) setErreur(e.message);
      })
      .finally(() => {
        if (!annule) setChargement(false);
      });

    // Nettoyage : ignore la réponse si la commune a changé entre-temps
    return () => {
      annule = true;
    };
  }, [commune, tentative]);

  return (
    <section>
      <h2>Pharmacies de garde</h2>
      <select value={commune} onChange={(e) => setCommune(e.target.value)}>
        {COMMUNES.map((c) => (
          <option key={c} value={c}>{c}</option>
        ))}
      </select>

      {chargement && <p>Chargement en cours...</p>}

      {erreur && (
        <div role="alert">
          <p>Impossible de charger la liste : {erreur}</p>
          <button onClick={() => setTentative((t) => t + 1)}>Réessayer</button>
        </div>
      )}

      {!chargement && !erreur && (
        <ul>
          {pharmacies.map((p) => (
            <li key={p.id}>{p.nom} : {p.telephone}</li>
          ))}
        </ul>
      )}
    </section>
  );
}
CODE,
        ],

        'Architecture et performance' => [
            'description' => 'Organiser une application React en composants, hooks personnalisés et couche d’accès aux données, et éviter les rendus inutiles sans optimisation prématurée.',
            'objective' => 'Extraire de la logique dans un hook personnalisé, séparer présentation et données, et appliquer useMemo ou memo uniquement quand c’est justifié.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application qui marche pour dix écrans devient vite un cauchemar à maintenir à cinquante. Les composants font des centaines de lignes, la même logique de chargement est recopiée partout et personne n’ose toucher à un fichier de peur de casser autre chose. En entreprise, la maintenabilité compte autant que la fonctionnalité : on relit du code bien plus souvent qu’on n’en écrit.

La performance est le second sujet. Une application lente sur un téléphone d’entrée de gamme ou une connexion limitée perd ses utilisateurs. Mais optimiser trop tôt complique le code pour rien. Cette leçon t’apprend à structurer d’abord, mesurer ensuite, optimiser seulement si nécessaire.

## Les concepts clés

### Séparer présentation, logique et données

Un composant de présentation reçoit des props et retourne du JSX, sans savoir d’où viennent les données. Un hook personnalisé regroupe la logique réutilisable : state, effets, calculs. Une couche d’accès aux données contient les appels réseau dans des fonctions simples. Cette séparation rend chaque partie testable et remplaçable : changer l’API ne touche pas les composants visuels.

### Les hooks personnalisés

Un hook personnalisé est une fonction dont le nom commence par « use » et qui appelle d’autres hooks. Il permet de partager de la logique, pas de l’état : chaque composant qui l’appelle obtient son propre state. Les règles des hooks s’appliquent : on les appelle au niveau supérieur de la fonction, jamais dans une condition ni dans une boucle.

### Comment React décide de refaire un rendu

Un composant est rendu de nouveau quand son state change, quand ses props changent ou quand son parent est rendu de nouveau. Un rendu n’est pas une mise à jour du DOM : React compare d’abord le résultat avec le précédent et ne modifie le navigateur que si nécessaire. Un rendu supplémentaire coûte donc peu dans la plupart des cas.

### Les outils d’optimisation

« useMemo » mémorise le résultat d’un calcul tant que ses dépendances ne changent pas. « memo » évite de refaire le rendu d’un composant si ses props sont identiques. « useCallback » conserve la même référence de fonction entre deux rendus. Ces outils ont un coût de lecture et de comparaison : utilise-les après avoir constaté un problème réel avec les outils de développement du navigateur, pas par réflexe.

## Exemple pas à pas

Le code d’exemple montre trois fichiers. À l’étape 1, une fonction d’accès aux données isole l’appel réseau. À l’étape 2, le hook « useProduits » gère le chargement, l’erreur et expose une recherche. À l’étape 3, le composant « ListeProduits » ne contient plus que du JSX et appelle le hook. À l’étape 4, « useMemo » filtre la liste selon la recherche, car la liste peut être longue. À l’étape 5, « memo » protège la ligne de produit afin qu’elle ne soit pas redessinée quand seule la recherche change.

## Erreurs fréquentes

- Mettre tout dans un seul composant géant : il devient illisible. Extrais des sous-composants et un hook dès que des responsabilités se mélangent.
- Appeler un hook dans une condition : React perd l’ordre des hooks et plante. Appelle-les toujours au niveau supérieur.
- Ajouter « useMemo » et « memo » partout : le code se complique sans gain mesuré. Mesure d’abord, optimise ensuite.
- Créer un objet ou une fonction dans le rendu et le passer à un composant mémorisé : la référence change à chaque rendu et annule « memo ». Mémorise la valeur avec « useMemo » ou « useCallback ».
- Oublier des dépendances dans « useMemo » : le résultat devient périmé. Liste toutes les valeurs utilisées.

## Bonnes pratiques

- Nomme les dossiers selon le métier : « features/commandes » plutôt que « utils2 ».
- Garde les appels réseau hors des composants de présentation.
- Mesure avec le profileur des outils de développement avant d’optimiser.
- Garde chaque composant assez court pour être lu en quelques secondes.

## Auto-évaluation

- Quelle différence entre composant de présentation et hook personnalisé ?
- Quelles règles s’appliquent à l’appel des hooks ?
- Pour quelles raisons un composant est-il rendu de nouveau ?
- Quand « useMemo » ou « memo » est-il justifié et quand est-il inutile ?

## À retenir

- Sépare l’affichage, la logique réutilisable et l’accès aux données.
- Un hook personnalisé partage de la logique, pas de l’état.
- Un rendu supplémentaire est souvent peu coûteux : mesure avant d’optimiser.
- « useMemo », « memo » et « useCallback » répondent à un problème constaté.
MD,
            'code_example' => <<<'CODE'
import { memo, useEffect, useMemo, useState } from 'react';

// Étape 1 : couche d'accès aux données, sans aucun JSX
async function chargerProduits() {
  const response = await fetch('/api/produits');
  if (!response.ok) throw new Error('Erreur du serveur');
  return response.json();
}

// Étape 2 : hook personnalisé qui regroupe la logique
function useProduits() {
  const [produits, setProduits] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    let annule = false;
    chargerProduits()
      .then((data) => !annule && setProduits(data))
      .catch((e) => !annule && setErreur(e.message))
      .finally(() => !annule && setChargement(false));
    return () => {
      annule = true;
    };
  }, []);

  return { produits, chargement, erreur };
}

// Étape 5 : memo évite de redessiner une ligne dont les props n'ont pas changé
const LigneProduit = memo(function LigneProduit({ produit }) {
  return <li>{produit.nom} : {produit.prix.toLocaleString('fr-FR')} FCFA</li>;
});

// Étape 3 : le composant ne s'occupe que de l'affichage
export default function ListeProduits() {
  const { produits, chargement, erreur } = useProduits();
  const [recherche, setRecherche] = useState('');

  // Étape 4 : le filtrage est mémorisé tant que ses dépendances ne changent pas
  const visibles = useMemo(
    () => produits.filter((p) => p.nom.toLowerCase().includes(recherche.toLowerCase())),
    [produits, recherche]
  );

  if (chargement) return <p>Chargement...</p>;
  if (erreur) return <p role="alert">{erreur}</p>;

  return (
    <section>
      <input value={recherche} onChange={(e) => setRecherche(e.target.value)} placeholder="Rechercher" />
      <ul>
        {visibles.map((p) => (
          <LigneProduit key={p.id} produit={p} />
        ))}
      </ul>
    </section>
  );
}
CODE,
            'estimated_minutes' => 70,
            'exercise_title' => 'Refactoriser une liste de commandes',
            'exercise_description' => <<<'TXT'
Tu reçois un composant « Commandes » de 80 lignes qui charge des commandes d’un restaurant, gère un filtre par statut et affiche chaque ligne. Refactorise-le : extrais une fonction d’accès aux données, un hook « useCommandes », un composant « LigneCommande » et mémorise le filtrage. Le comportement visible ne doit pas changer.

Critères de réussite :
- L’appel réseau est dans une fonction séparée, sans JSX.
- Un hook « useCommandes » retourne « commandes », « chargement » et « erreur », et contient l’effet avec nettoyage.
- Le composant « Commandes » ne contient plus d’appel réseau ni d’effet.
- La liste filtrée est calculée avec « useMemo » en listant correctement ses dépendances.
- « LigneCommande » est un composant séparé protégé par « memo ».
TXT,
            'exercise_hint' => 'Commence par déplacer l’effet et les trois états dans le hook, puis retourne un objet. Ensuite, extrais la ligne dans son propre composant. Termine par « useMemo » avec « commandes » et « statut » comme dépendances.',
            'exercise_solution' => <<<'CODE'
import { memo, useEffect, useMemo, useState } from 'react';

// Accès aux données : aucune notion d'interface
async function chargerCommandes() {
  const response = await fetch('/api/commandes');
  if (!response.ok) throw new Error('Impossible de récupérer les commandes');
  return response.json();
}

// Hook : logique de chargement réutilisable
function useCommandes() {
  const [commandes, setCommandes] = useState([]);
  const [chargement, setChargement] = useState(true);
  const [erreur, setErreur] = useState(null);

  useEffect(() => {
    let annule = false;
    chargerCommandes()
      .then((data) => {
        if (!annule) setCommandes(data);
      })
      .catch((e) => {
        if (!annule) setErreur(e.message);
      })
      .finally(() => {
        if (!annule) setChargement(false);
      });
    return () => {
      annule = true;
    };
  }, []);

  return { commandes, chargement, erreur };
}

// Composant de présentation mémorisé
const LigneCommande = memo(function LigneCommande({ commande }) {
  return (
    <li>
      Commande n°{commande.id} : {commande.plat} ({commande.statut})
    </li>
  );
});

export default function Commandes() {
  const { commandes, chargement, erreur } = useCommandes();
  const [statut, setStatut] = useState('toutes');

  // Filtrage mémorisé : recalculé seulement si commandes ou statut changent
  const visibles = useMemo(
    () =>
      statut === 'toutes'
        ? commandes
        : commandes.filter((c) => c.statut === statut),
    [commandes, statut]
  );

  if (chargement) return <p>Chargement...</p>;
  if (erreur) return <p role="alert">{erreur}</p>;

  return (
    <section>
      <select value={statut} onChange={(e) => setStatut(e.target.value)}>
        <option value="toutes">Toutes</option>
        <option value="en cours">En cours</option>
        <option value="livrée">Livrée</option>
      </select>
      <ul>
        {visibles.map((c) => (
          <LigneCommande key={c.id} commande={c} />
        ))}
      </ul>
    </section>
  );
}
CODE,
        ],

        'Projet final React' => [
            'description' => 'Assembler composants, state, formulaires et appels API dans un gestionnaire de tâches complet, avec filtres, détail et persistance.',
            'objective' => 'Livrer un mini-projet React structuré qui combine composants, state, formulaire contrôlé, filtres et persistance, avec chargement et erreurs gérés.',
            'content' => <<<'MD'
## Pourquoi cette notion

Un projet final est ce qui transforme des notions isolées en compétence démontrable. Un recruteur ou un client ne te demande pas de réciter la définition de useState : il veut voir une application qui fonctionne, lisible, organisée et robuste. Ce gestionnaire de tâches est un classique parce qu’il contient, en petit, tout ce qu’on retrouve dans les vrais produits : une liste, un formulaire, des filtres, un écran de détail, de la persistance et des cas d’erreur.

## Les concepts clés

### Définir le périmètre avant de coder

Écris d’abord ce que l’application doit faire en quelques phrases : ajouter une tâche avec un titre et une priorité, la marquer comme terminée, la supprimer, filtrer par statut, ouvrir le détail et retrouver ses données après un rechargement. Un périmètre court et précis évite de s’égarer et te donne une liste de critères pour savoir quand tu as terminé.

### Découper en composants

Dessine l’arbre avant d’écrire le code. Un composant « App » porte le state commun. Un « FormulaireTache » gère la saisie. Une « ListeTaches » affiche les lignes. Un « Filtres » permet de choisir un statut. Un « DetailTache » affiche une tâche sélectionnée. Chaque composant reçoit ses données par props et signale les actions par des fonctions passées en props.

### Où vit le state

Le tableau des tâches et le filtre actif vivent dans « App », le parent commun. Le texte en cours de saisie vit dans le formulaire, car lui seul en a besoin. Le nombre de tâches terminées se calcule au rendu. Cette discipline évite les incohérences.

### La persistance

Pour garder les données au rechargement, tu peux utiliser le stockage local du navigateur ou une API. Le stockage local est simple : tu lis la valeur au démarrage et tu l’écris quand le tableau change, avec un effet qui dépend de ce tableau. N’oublie pas qu’une lecture peut échouer ou renvoyer des données invalides, donc protège-la avec un bloc try et catch. Avec une API, tu retrouves chargement, erreur et réessai, comme dans la leçon précédente.

### La qualité du livrable

Un projet terminé inclut des états vides, un affichage lisible sur mobile, des messages clairs, des noms cohérents et un petit fichier explicatif. Ces détails distinguent un exercice d’un produit.

## Exemple pas à pas

Le code d’exemple présente le cœur du projet. À l’étape 1, une fonction lit le stockage local avec protection. À l’étape 2, « App » déclare le tableau et le filtre, et un effet enregistre le tableau à chaque modification. À l’étape 3, trois fonctions ajoutent, basculent et suppriment une tâche sans modifier le tableau en place. À l’étape 4, la liste visible est calculée avec le filtre. À l’étape 5, le rendu affiche le formulaire, les boutons de filtre, la liste et un message pour l’état vide.

## Erreurs fréquentes

- Commencer à coder sans plan : tu réécris tout en cours de route. Écris le périmètre et dessine l’arbre de composants d’abord.
- Dupliquer le state dans plusieurs composants : les données divergent. Garde une seule source de vérité dans le parent commun.
- Lire le stockage local sans protection : un contenu corrompu fait planter l’application. Entoure la lecture d’un try et catch avec une valeur de repli.
- Oublier l’état vide : l’écran est blanc et déroutant. Affiche un message invitant à créer la première tâche.
- Modifier directement le tableau des tâches : l’affichage ne se met pas à jour. Crée toujours des copies avec « map », « filter » ou la décomposition.
- Livrer sans relire ni tester les cas limites, comme un titre vide ou un tableau vide : des bugs évidents subsistent. Teste chaque critère à la main.

## Bonnes pratiques

- Avance par petites étapes fonctionnelles et fais un commit Git à chacune.
- Valide les entrées du formulaire et affiche un message d’erreur clair.
- Garde les composants courts et les noms parlants en français ou en anglais, de façon cohérente.
- Teste sur la taille d’écran d’un téléphone, ton public principal.

## Auto-évaluation

- Quels sont les composants de ton application et quelles données reçoit chacun ?
- Où vit chaque morceau de state et pourquoi à cet endroit ?
- Comment les données sont-elles conservées après un rechargement ?
- Comment l’application réagit-elle à une saisie vide ou à un stockage corrompu ?
- Quelles valeurs sont calculées au rendu plutôt que stockées ?

## À retenir

- Un projet commence par un périmètre écrit et un arbre de composants.
- Le state partagé vit dans le parent commun, le state local reste local.
- La persistance se protège contre les erreurs de lecture.
- Les états vide, chargement et erreur font partie de la fonctionnalité.
- Un livrable propre inclut tests manuels, lisibilité mobile et README.
MD,
            'code_example' => <<<'CODE'
import { useEffect, useState } from 'react';

const CLE = 'taches-devroad';

// Étape 1 : lecture protégée du stockage local
function lireTaches() {
  try {
    const data = JSON.parse(localStorage.getItem(CLE) || '[]');
    return Array.isArray(data) ? data : [];
  } catch {
    return []; // valeur de repli si le contenu est invalide
  }
}

export default function App() {
  // Étape 2 : state partagé dans le parent et persistance
  const [taches, setTaches] = useState(lireTaches);
  const [filtre, setFiltre] = useState('toutes');
  const [titre, setTitre] = useState('');
  useEffect(() => {
    try {
      localStorage.setItem(CLE, JSON.stringify(taches));
    } catch {} // stockage indisponible : on ignore
  }, [taches]);
  // Étape 3 : actions sans modification en place
  function ajouter(e) {
    e.preventDefault();
    if (titre.trim() === '') return;
    setTaches((t) => [...t, { id: Date.now(), titre: titre.trim(), faite: false }]);
    setTitre('');
  }
  const basculer = (id) =>
    setTaches((t) => t.map((x) => (x.id === id ? { ...x, faite: !x.faite } : x)));
  const supprimer = (id) => setTaches((t) => t.filter((x) => x.id !== id));

  // Étape 4 : liste visible calculée selon le filtre
  const visibles = taches.filter((t) =>
    filtre === 'toutes' ? true : filtre === 'faites' ? t.faite : !t.faite
  );

  // Étape 5 : rendu avec état vide
  return (
    <main>
      <form onSubmit={ajouter}>
        <input value={titre} onChange={(e) => setTitre(e.target.value)} />
        <button type="submit">Ajouter</button>
      </form>
      {['toutes', 'à faire', 'faites'].map((f) => (
        <button key={f} onClick={() => setFiltre(f)} disabled={filtre === f}>{f}</button>
      ))}
      {visibles.length === 0 && <p>Aucune tâche ici.</p>}
      <ul>
        {visibles.map((t) => (
          <li key={t.id}>
            <input type="checkbox" checked={t.faite} onChange={() => basculer(t.id)} />
            {t.titre}
            <button onClick={() => supprimer(t.id)}>Supprimer</button>
          </li>
        ))}
      </ul>
    </main>
  );
}
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : gestionnaire de tâches complet',
            'exercise_description' => <<<'TXT'
Construis un gestionnaire de tâches en React avec Vite. Chaque tâche a un titre, une priorité (basse, moyenne, haute) et un statut. L’utilisateur peut ajouter, terminer, supprimer, filtrer par statut et ouvrir le détail d’une tâche. Les données doivent être conservées après un rechargement de la page.

Livrables :
- Un projet Vite fonctionnel avec une structure « components » claire (au moins « FormulaireTache », « ListeTaches », « Filtres » et « DetailTache »).
- Un fichier README court décrivant l’objectif, le lancement et les fonctionnalités.
- Un historique Git avec au moins quatre commits parlants.

Critères de réussite :
- Une tâche vide est refusée avec un message visible et le champ se vide après un ajout valide.
- Le filtre par statut fonctionne et un message s’affiche quand la liste visible est vide.
- Les données persistent au rechargement et une lecture invalide du stockage ne fait pas planter l’application.
- Aucun tableau du state n’est modifié en place et les valeurs calculables ne sont pas stockées.
- L’interface reste lisible sur la largeur d’un téléphone.
TXT,
            'exercise_hint' => 'Commence par la version minimale : ajout et affichage. Ajoute ensuite la suppression, puis le filtre, puis la persistance, puis le détail, en faisant un commit à chaque étape. Pour le détail, garde dans le state l’identifiant de la tâche sélectionnée plutôt qu’une copie de la tâche.',
            'exercise_solution' => <<<'CODE'
import { useEffect, useState } from 'react';

const CLE = 'taches-projet-final';
const PRIORITES = ['basse', 'moyenne', 'haute'];

function lireTaches() {
  try {
    const data = JSON.parse(localStorage.getItem(CLE) || '[]');
    return Array.isArray(data) ? data : [];
  } catch {
    return [];
  }
}

function FormulaireTache({ onAjouter }) {
  const [titre, setTitre] = useState('');
  const [priorite, setPriorite] = useState('moyenne');
  const [erreur, setErreur] = useState('');

  function valider(e) {
    e.preventDefault();
    if (titre.trim() === '') {
      setErreur('Le titre est obligatoire.');
      return;
    }
    onAjouter({ id: Date.now(), titre: titre.trim(), priorite, faite: false });
    setTitre('');
    setErreur('');
  }

  return (
    <form onSubmit={valider}>
      <input value={titre} onChange={(e) => setTitre(e.target.value)} placeholder="Titre de la tâche" />
      <select value={priorite} onChange={(e) => setPriorite(e.target.value)}>
        {PRIORITES.map((p) => <option key={p} value={p}>{p}</option>)}
      </select>
      <button type="submit">Ajouter</button>
      {erreur && <p role="alert">{erreur}</p>}
    </form>
  );
}

function Filtres({ filtre, onChanger }) {
  return (
    <nav>
      {['toutes', 'à faire', 'faites'].map((f) => (
        <button key={f} onClick={() => onChanger(f)} disabled={filtre === f}>{f}</button>
      ))}
    </nav>
  );
}

function ListeTaches({ taches, onBasculer, onSupprimer, onOuvrir }) {
  if (taches.length === 0) return <p>Aucune tâche à afficher.</p>;
  return (
    <ul>
      {taches.map((t) => (
        <li key={t.id}>
          <input type="checkbox" checked={t.faite} onChange={() => onBasculer(t.id)} />
          <button onClick={() => onOuvrir(t.id)}>{t.titre}</button> ({t.priorite})
          <button onClick={() => onSupprimer(t.id)}>Supprimer</button>
        </li>
      ))}
    </ul>
  );
}

function DetailTache({ tache, onFermer }) {
  if (!tache) return null;
  return (
    <aside>
      <h2>{tache.titre}</h2>
      <p>Priorité : {tache.priorite}</p>
      <p>Statut : {tache.faite ? 'terminée' : 'à faire'}</p>
      <button onClick={onFermer}>Fermer</button>
    </aside>
  );
}

export default function App() {
  const [taches, setTaches] = useState(lireTaches);
  const [filtre, setFiltre] = useState('toutes');
  // On garde l'identifiant, pas une copie, pour rester synchronisé
  const [selectionId, setSelectionId] = useState(null);

  useEffect(() => {
    try {
      localStorage.setItem(CLE, JSON.stringify(taches));
    } catch {
      // ignoré : l'application reste utilisable sans persistance
    }
  }, [taches]);

  const ajouter = (tache) => setTaches((t) => [...t, tache]);
  const basculer = (id) =>
    setTaches((t) => t.map((x) => (x.id === id ? { ...x, faite: !x.faite } : x)));
  const supprimer = (id) => {
    setTaches((t) => t.filter((x) => x.id !== id));
    setSelectionId((s) => (s === id ? null : s));
  };

  // Valeurs dérivées, calculées au rendu
  const visibles = taches.filter((t) =>
    filtre === 'toutes' ? true : filtre === 'faites' ? t.faite : !t.faite
  );
  const selection = taches.find((t) => t.id === selectionId) ?? null;

  return (
    <main>
      <h1>Gestionnaire de tâches</h1>
      <FormulaireTache onAjouter={ajouter} />
      <Filtres filtre={filtre} onChanger={setFiltre} />
      <ListeTaches
        taches={visibles}
        onBasculer={basculer}
        onSupprimer={supprimer}
        onOuvrir={setSelectionId}
      />
      <DetailTache tache={selection} onFermer={() => setSelectionId(null)} />
    </main>
  );
}
CODE,
        ],

    ],
];
