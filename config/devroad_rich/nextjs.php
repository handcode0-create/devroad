<?php

return [
    'lessons' => [

        'Découvrir Next.js' => [
            'description' => 'Comprendre ce que Next.js ajoute à React : routing par dossiers, rendu côté serveur, layouts, endpoints d’API et optimisations intégrées.',
            'objective' => 'Expliquer le rôle d’un framework full-stack par rapport à React seul et décrire comment un fichier devient une page.',
            'content' => <<<'MD'
## Pourquoi cette notion

Avec React seul, tu construis des composants, mais tu dois toi-même choisir et assembler tout le reste : la navigation entre les pages, le chargement des données, le référencement, la mise en ligne, les endpoints côté serveur. Chaque équipe réinvente alors sa propre solution, avec des choix souvent incohérents.

Next.js est un framework construit sur React qui fournit ces briques de façon cohérente. Il est très utilisé en entreprise pour des sites vitrines, des boutiques, des tableaux de bord et des SaaS. Pour un projet destiné à des utilisateurs mobiles avec une connexion limitée, il apporte un avantage direct : une partie du travail est faite sur le serveur, ce qui envoie moins de JavaScript au téléphone et affiche la page plus vite.

## Les concepts clés

### Un framework, pas seulement une bibliothèque

React décrit comment construire l’interface. Next.js décide de la structure du projet et s’occupe de ce qui entoure : routes, rendu, build, optimisation des images, métadonnées. Tu écris moins de configuration et tu suis des conventions que toute l’équipe connaît.

### Le routing par dossiers

Dans l’App Router, le dossier « app » définit les URL. Un dossier correspond à un segment d’URL et un fichier « page.tsx » dans ce dossier devient la page accessible à cette adresse. Le dossier « app/contact » avec un « page.tsx » donne la route « /contact ». Il n’y a aucune table de routes à maintenir.

### Le rendu côté serveur

Par défaut, les composants de l’App Router s’exécutent sur le serveur. Le navigateur reçoit du HTML déjà prêt, que React rend ensuite interactif quand c’est nécessaire. L’utilisateur voit le contenu plus tôt et les moteurs de recherche lisent directement le texte de la page. Tu verras plus tard comment choisir précisément ce qui s’exécute où.

### Les layouts

Un fichier « layout.tsx » décrit une structure partagée par plusieurs pages, comme l’en-tête et le pied de page. Il reçoit les pages en « children ». Le layout racine est obligatoire et contient les balises « html » et « body ».

### Ce que Next.js apporte de plus

Next.js propose aussi des endpoints HTTP dans le même projet, un composant d’image optimisé, la gestion des métadonnées, les pages de chargement et d’erreur. Tout cela se fait par des fichiers aux noms réservés.

## Exemple pas à pas

Le code d’exemple montre trois fichiers d’un site de salon de coiffure. À l’étape 1, le layout racine définit la langue de la page, une navigation et affiche « children ». À l’étape 2, « app/page.tsx » devient la page d’accueil, sans aucune configuration de route. À l’étape 3, « app/tarifs/page.tsx » devient la page « /tarifs » simplement parce que le dossier porte ce nom. Remarque que ces pages sont de simples fonctions qui retournent du JSX, comme en React.

## Erreurs fréquentes

- Croire que Next.js remplace React : c’est une couche construite dessus. Tes composants, props et hooks restent les mêmes.
- Chercher un fichier de configuration des routes : il n’existe pas dans l’App Router. Crée un dossier avec un fichier « page.tsx ».
- Nommer le fichier autrement que « page.tsx » : la route n’existe pas et tu obtiens une erreur 404. Respecte exactement le nom réservé.
- Oublier que le layout racine est obligatoire : le projet ne démarre pas correctement. Garde « app/layout.tsx » avec « html » et « body ».
- Utiliser un hook comme « useState » dans une page sans précaution : le composant s’exécute sur le serveur par défaut. Tu verras la solution dans la leçon sur les Client Components.

## Bonnes pratiques

- Suis les conventions de nommage de Next.js, elles sont la même d’un projet à l’autre.
- Garde les pages courtes et délègue l’affichage à des composants.
- Pense d’abord au contenu envoyé au navigateur, surtout pour les connexions mobiles lentes.
- Lis la documentation officielle pour les détails dépendant de ta version.

## Auto-évaluation

- Quelle différence y a-t-il entre React et Next.js ?
- Comment un dossier et un fichier deviennent-ils une URL ?
- À quoi sert un fichier « layout.tsx » ?
- Où s’exécutent les composants par défaut dans l’App Router ?
- Cite deux fonctionnalités que Next.js fournit en plus de React.

## À retenir

- Next.js est un framework full-stack construit sur React.
- Le dossier « app » définit les routes, et « page.tsx » rend une page.
- Les composants s’exécutent par défaut sur le serveur.
- Un layout partage une structure commune entre plusieurs pages.
- Les conventions de fichiers remplacent la configuration manuelle.
MD,
            'code_example' => <<<'CODE'
// ===== app/layout.tsx : étape 1, la structure commune à toutes les pages =====
import Link from 'next/link';
import type { ReactNode } from 'react';

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="fr">
      <body>
        <header>
          <strong>Salon Belle Allure</strong>
          {/* Link navigue sans recharger toute la page */}
          <nav>
            <Link href="/">Accueil</Link> | <Link href="/tarifs">Tarifs</Link>
          </nav>
        </header>
        {/* Chaque page est insérée ici */}
        <main>{children}</main>
        <footer>Cocody, Abidjan</footer>
      </body>
    </html>
  );
}

// ===== app/page.tsx : étape 2, la route "/" =====
export default function Accueil() {
  return (
    <section>
      <h1>Bienvenue au salon</h1>
      <p>Coiffure, tresses et soins, sur rendez-vous.</p>
    </section>
  );
}

// ===== app/tarifs/page.tsx : étape 3, la route "/tarifs" =====
const tarifs = [
  { id: 1, nom: 'Tresses simples', prix: 8000 },
  { id: 2, nom: 'Coupe et soin', prix: 5000 },
];

export default function Tarifs() {
  return (
    <section>
      <h1>Nos tarifs</h1>
      <ul>
        {tarifs.map((t) => (
          <li key={t.id}>{t.nom} : {t.prix.toLocaleString('fr-FR')} FCFA</li>
        ))}
      </ul>
    </section>
  );
}
CODE,
            'estimated_minutes' => 45,
            'exercise_title' => 'Mini-site d’un restaurant',
            'exercise_description' => <<<'TXT'
Conçois la structure d’un mini-site de restaurant avec l’App Router. Tu dois écrire un layout racine avec navigation, une page d’accueil, une page « menu » qui liste au moins trois plats et une page « contact » avec une adresse et un numéro.

Critères de réussite :
- Le layout racine contient « html » avec l’attribut « lang », une navigation et affiche « children ».
- Les trois routes « / », « /menu » et « /contact » existent grâce à des fichiers « page.tsx » dans les bons dossiers.
- La navigation utilise le composant « Link » de Next.js et non des balises « a » classiques pour les liens internes.
- La page « menu » affiche les plats avec « map » et une « key » stable, et les prix en FCFA.
- Chaque fichier exporte un composant par défaut dont le nom commence par une majuscule.
TXT,
            'exercise_hint' => 'Arborescence attendue : app/layout.tsx, app/page.tsx, app/menu/page.tsx et app/contact/page.tsx. Pour la navigation, importe « Link » depuis « next/link » et utilise la prop « href ».',
            'exercise_solution' => <<<'CODE'
// ===== app/layout.tsx =====
import Link from 'next/link';
import type { ReactNode } from 'react';

export default function RootLayout({ children }: { children: ReactNode }) {
  return (
    <html lang="fr">
      <body>
        <nav>
          <Link href="/">Accueil</Link> | <Link href="/menu">Menu</Link> |{' '}
          <Link href="/contact">Contact</Link>
        </nav>
        <main>{children}</main>
      </body>
    </html>
  );
}

// ===== app/page.tsx =====
export default function Accueil() {
  return <h1>Chez Maman Adjoua, cuisine ivoirienne</h1>;
}

// ===== app/menu/page.tsx =====
const plats = [
  { id: 1, nom: 'Kédjenou de poulet', prix: 4000 },
  { id: 2, nom: 'Poisson braisé', prix: 5000 },
  { id: 3, nom: 'Foutou sauce graine', prix: 3500 },
];

export default function Menu() {
  return (
    <section>
      <h1>Notre menu</h1>
      <ul>
        {plats.map((p) => (
          <li key={p.id}>{p.nom} : {p.prix.toLocaleString('fr-FR')} FCFA</li>
        ))}
      </ul>
    </section>
  );
}

// ===== app/contact/page.tsx =====
export default function Contact() {
  return (
    <section>
      <h1>Contact</h1>
      <p>Adresse : Marcory, Abidjan</p>
      <p>Téléphone : 01 23 45 67 89</p>
    </section>
  );
}
CODE,
        ],

        'Créer et lancer un projet' => [
            'description' => 'Installer un projet Next.js avec create-next-app, le lancer en local et comprendre le rôle des dossiers et des scripts générés.',
            'objective' => 'Créer un projet Next.js, le lancer, modifier la page d’accueil et produire un build de production.',
            'content' => <<<'MD'
## Pourquoi cette notion

Tout projet professionnel commence par une base propre. Si tu ne comprends pas ce que contient le projet généré, tu perds du temps à chaque erreur, et tu risques de supprimer par erreur un fichier important. Savoir créer, lancer, construire et déployer une application est aussi la première chose qu’un collègue attend de toi dans une équipe.

## Les concepts clés

### La commande de création

L’outil officiel s’appelle create-next-app. Tu le lances avec npx, qui exécute un paquet sans l’installer durablement. Il te pose quelques questions : utiliser TypeScript, activer ESLint, utiliser Tailwind CSS, utiliser le dossier « src », utiliser l’App Router. Pour ce cours, choisis TypeScript et l’App Router. Les options proposées peuvent évoluer avec les versions : lis les questions avant de répondre.

### Les scripts du package.json

Trois scripts sont essentiels. Le script « dev » lance le serveur de développement avec rechargement à chaud, tu l’utilises tout le temps pendant le travail. Le script « build » compile l’application pour la production et signale les erreurs de types ou de code. Le script « start » lance cette version de production et doit être précédé d’un « build ». Tu les exécutes avec « npm run » suivi du nom, ou « npm start » pour le dernier.

### Les dossiers importants

Le dossier « app » contient les routes, les layouts et les pages. Le dossier « public » contient les fichiers servis tels quels, comme les images et le favicon. Le fichier « next.config » contient la configuration du framework. Le dossier « .next » est généré par le build : il ne se modifie jamais à la main et ne se versionne pas. Le dossier « node_modules » contient les dépendances et ne se versionne pas non plus.

### Les variables d’environnement

Les secrets comme une clé d’API se placent dans un fichier « .env.local », jamais dans le code ni dans Git. Une variable n’est visible côté navigateur que si son nom commence par « NEXT_PUBLIC_ ». Toutes les autres restent côté serveur, ce qui protège tes secrets.

## Exemple pas à pas

Le code d’exemple suit le parcours complet. À l’étape 1, tu crées le projet avec la commande et tu entres dans le dossier. À l’étape 2, tu lances « npm run dev » et tu ouvres l’adresse locale indiquée dans le terminal. À l’étape 3, tu modifies « app/page.tsx » : la page se met à jour sans rechargement manuel. À l’étape 4, tu lis une variable d’environnement côté serveur depuis la page. À l’étape 5, tu lances « npm run build » pour vérifier que le projet compile.

## Erreurs fréquentes

- Lancer les commandes hors du dossier du projet : npm ne trouve pas le fichier package.json. Utilise « cd » pour entrer dans le dossier créé.
- Lancer « npm start » sans avoir fait « npm run build » : le serveur refuse de démarrer faute de build. Construis d’abord.
- Mettre un secret dans une variable « NEXT_PUBLIC_ » : il devient lisible par tous les visiteurs. Réserve ce préfixe aux valeurs publiques.
- Versionner « .env.local » : tes secrets se retrouvent dans Git. Vérifie qu’il figure dans « .gitignore ».
- Modifier des fichiers dans « .next » : tes changements sont écrasés à chaque build. Modifie uniquement les sources.
- Ignorer les erreurs du terminal : le build échoue ensuite en production. Corrige les messages dès qu’ils apparaissent.

## Bonnes pratiques

- Lance « npm run build » avant chaque mise en ligne pour attraper les erreurs tôt.
- Garde un fichier « .env.example » sans valeur secrète pour documenter les variables attendues.
- Fais un premier commit juste après la création, puis un commit par fonctionnalité.
- Supprime le contenu de démonstration pour partir d’une page propre.

## Auto-évaluation

- Quelle commande crée un projet Next.js et quelles options choisis-tu ?
- Quelle est la différence entre « dev », « build » et « start » ?
- À quoi servent les dossiers « app » et « public » ?
- Pourquoi ne faut-il pas versionner « .env.local » ?
- Quel préfixe rend une variable visible dans le navigateur ?

## À retenir

- create-next-app génère un projet prêt à l’emploi.
- « dev » sert au travail quotidien, « build » vérifie la production, « start » la lance.
- Le dossier « app » contient les routes et « public » les fichiers statiques.
- Les secrets vont dans « .env.local » et ne se versionnent jamais.
- Seules les variables préfixées par NEXT_PUBLIC_ sont exposées au navigateur.
MD,
            'code_example' => <<<'CODE'
// Étape 1 : à taper dans le terminal (pas dans un fichier TypeScript)
//   npx create-next-app@latest boutique-next
//   cd boutique-next
// Étape 2 : lancer le serveur de développement
//   npm run dev
// Étape 5 : vérifier la version de production
//   npm run build && npm start

// ===== .env.local (jamais versionné) =====
// NOM_BOUTIQUE=Boutique Akwaba
// NEXT_PUBLIC_DEVISE=FCFA

// ===== app/page.tsx : étapes 3 et 4 =====
export default function Accueil() {
  // Lue côté serveur uniquement : absente du navigateur
  const boutique = process.env.NOM_BOUTIQUE ?? 'Ma boutique';

  // Variable publique : visible aussi côté navigateur
  const devise = process.env.NEXT_PUBLIC_DEVISE ?? 'FCFA';

  const produits = [
    { id: 1, nom: 'Pagne wax', prix: 7500 },
    { id: 2, nom: 'Sac en cuir', prix: 15000 },
  ];

  return (
    <main>
      {/* Modifie ce titre et enregistre : la page se met à jour seule */}
      <h1>{boutique}</h1>
      <ul>
        {produits.map((p) => (
          <li key={p.id}>
            {p.nom} : {p.prix.toLocaleString('fr-FR')} {devise}
          </li>
        ))}
      </ul>
    </main>
  );
}

// ===== .gitignore (extrait à vérifier) =====
// node_modules
// .next
// .env*.local
CODE,
            'estimated_minutes' => 50,
            'exercise_title' => 'Initialiser le projet d’une agence de livraison',
            'exercise_description' => <<<'TXT'
Crée un projet Next.js nommé « livraison-express » avec TypeScript et l’App Router, puis prépare une base propre. Remplace la page d’accueil par une page qui affiche le nom de l’agence lu depuis une variable d’environnement serveur, ainsi qu’une zone de couverture lue depuis une variable publique.

Critères de réussite :
- Le projet démarre avec « npm run dev » et la page d’accueil affiche ton propre contenu, sans le contenu de démonstration.
- Le nom de l’agence vient de « NOM_AGENCE » dans « .env.local » et la zone de couverture de « NEXT_PUBLIC_ZONE ».
- Le fichier « .env.local » n’est pas versionné et un fichier « .env.example » sans valeur sensible est fourni.
- La commande « npm run build » se termine sans erreur.
- Un premier commit Git décrit l’initialisation du projet.
TXT,
            'exercise_hint' => 'Redémarre le serveur de développement après avoir créé ou modifié « .env.local », car les variables sont lues au démarrage. Utilise « process.env.NOM » dans la page et prévois une valeur de repli avec « ?? ».',
            'exercise_solution' => <<<'CODE'
// Terminal :
//   npx create-next-app@latest livraison-express
//   cd livraison-express
//   git add . && git commit -m "Initialisation du projet"

// ===== .env.local (non versionné) =====
// NOM_AGENCE=Livraison Express Abidjan
// NEXT_PUBLIC_ZONE=Abidjan et banlieue

// ===== .env.example (versionné, sans secret) =====
// NOM_AGENCE=
// NEXT_PUBLIC_ZONE=

// ===== app/page.tsx =====
export default function Accueil() {
  // Variable serveur avec valeur de repli
  const agence = process.env.NOM_AGENCE ?? 'Agence de livraison';
  // Variable publique, exposée au navigateur
  const zone = process.env.NEXT_PUBLIC_ZONE ?? 'Zone non définie';

  return (
    <main>
      <h1>{agence}</h1>
      <p>Zone de couverture : {zone}</p>
      <p>Livraison le jour même pour toute commande avant midi.</p>
    </main>
  );
}

// Vérifications finales dans le terminal :
//   npm run dev     -> la page affiche le nom de l'agence
//   npm run build   -> aucun message d'erreur
CODE,
        ],

        'App Router et routing' => [
            'description' => 'Construire une navigation avec des pages, des layouts imbriqués, des routes dynamiques et le composant Link.',
            'objective' => 'Créer une arborescence de routes avec layout imbriqué, route dynamique et navigation par Link.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application réelle contient des dizaines de pages : une liste de produits, la fiche de chaque produit, un panier, un espace client. Il faut une manière claire de déclarer ces pages, de partager des éléments communs comme un menu latéral et de gérer des adresses qui contiennent un identifiant, comme la fiche du produit numéro 12. L’App Router répond à tout cela uniquement avec la structure des dossiers.

## Les concepts clés

### Pages et segments

Chaque dossier dans « app » est un segment d’URL. Un dossier ne devient une route accessible que s’il contient un fichier « page.tsx ». Des dossiers imbriqués créent des URL imbriquées : « app/boutique/produits/page.tsx » donne « /boutique/produits ». Un dossier sans « page.tsx » sert seulement de regroupement.

### Les layouts imbriqués

Un « layout.tsx » placé dans un dossier enveloppe toutes les pages de ce dossier et de ses sous-dossiers. Les layouts s’emboîtent : le layout racine contient le layout de la section, qui contient la page. Un layout n’est pas recréé quand tu navigues entre deux pages qu’il enveloppe, ce qui conserve son état et accélère la navigation.

### Les routes dynamiques

Un nom de dossier entre crochets, comme « [id] », déclare un segment variable. Le dossier « app/produits/[id]/page.tsx » répond à « /produits/1 », « /produits/2 » et ainsi de suite. La valeur du segment arrive dans la prop « params » de la page. Dans les versions récentes, « params » est une promesse que l’on attend avec « await » : l’écrire ainsi fonctionne aussi dans les versions précédentes. Une valeur issue de l’URL est toujours du texte : convertis-la en nombre si nécessaire et vérifie qu’elle est valide.

### Navigation

Le composant « Link » de « next/link » remplace la balise « a » pour les liens internes. Il navigue sans recharger toute la page et peut précharger la destination. Pour naviguer depuis du code, après un formulaire par exemple, on utilise les outils de navigation de Next.js. Pour afficher une page 404, on appelle la fonction « notFound », qui affiche la page prévue pour ce cas.

### Groupes et organisation

Un dossier entre parenthèses, comme « (vitrine) », regroupe des routes sans apparaître dans l’URL. C’est utile pour donner des layouts différents à deux parties du site.

## Exemple pas à pas

Le code d’exemple présente un petit catalogue. À l’étape 1, un layout propre à la section « produits » ajoute un titre commun. À l’étape 2, la page liste affiche chaque produit avec un « Link » vers sa fiche. À l’étape 3, la page dynamique attend « params », lit l’identifiant et cherche le produit. À l’étape 4, si le produit n’existe pas, « notFound » affiche la page 404. À l’étape 5, la page fiche affiche le nom et le prix.

## Erreurs fréquentes

- Créer un dossier sans fichier « page.tsx » et s’étonner d’un 404 : un dossier seul n’est pas une route. Ajoute « page.tsx ».
- Utiliser une balise « a » pour un lien interne : la page se recharge entièrement. Utilise « Link ».
- Oublier de convertir l’identifiant de l’URL : comparer le texte « 1 » au nombre 1 échoue. Convertis avec « Number » et vérifie le résultat.
- Ne pas gérer un identifiant inexistant : la page plante ou affiche du vide. Appelle « notFound ».
- Écrire « [id] » avec une autre syntaxe, comme « :id » : ce n’est pas reconnu par Next.js. Utilise toujours les crochets.

## Bonnes pratiques

- Garde des URL lisibles et en minuscules, comme « /produits/12 ».
- Place la structure commune dans un layout plutôt que de la répéter dans chaque page.
- Valide toujours les paramètres d’URL avant de les utiliser.
- Utilise des groupes de routes pour organiser sans modifier les adresses.

## Auto-évaluation

- Qu’est-ce qui rend un dossier accessible comme une route ?
- Comment fonctionnent les layouts imbriqués ?
- Comment déclarer une route dynamique et récupérer sa valeur ?
- Pourquoi préférer « Link » à une balise « a » ?
- Que fait « notFound » et quand l’appeler ?

## À retenir

- Les dossiers de « app » forment les URL, « page.tsx » rend la page.
- Les layouts s’emboîtent et ne sont pas recréés lors de la navigation.
- « [id] » déclare un segment variable lu depuis « params ».
- « Link » assure une navigation rapide sans rechargement complet.
- Les paramètres d’URL sont du texte à valider, avec « notFound » en cas d’échec.
MD,
            'code_example' => <<<'CODE'
// Données de démonstration (dans un vrai projet : base de données ou API)
// ===== lib/produits.ts =====
export const produits = [
  { id: 1, nom: 'Pagne wax', prix: 7500 },
  { id: 2, nom: 'Sac en cuir', prix: 15000 },
  { id: 3, nom: 'Sandales artisanales', prix: 9000 },
];

// ===== app/produits/layout.tsx : étape 1, layout de la section =====
import type { ReactNode } from 'react';

export default function ProduitsLayout({ children }: { children: ReactNode }) {
  return (
    <section>
      <h2>Notre catalogue</h2>
      {children}
    </section>
  );
}

// ===== app/produits/page.tsx : étape 2, la liste =====
import Link from 'next/link';
import { produits } from '@/lib/produits';

export default function ListeProduits() {
  return (
    <ul>
      {produits.map((p) => (
        <li key={p.id}>
          <Link href={`/produits/${p.id}`}>{p.nom}</Link>
        </li>
      ))}
    </ul>
  );
}

// ===== app/produits/[id]/page.tsx : étapes 3 à 5, la fiche =====
import { notFound } from 'next/navigation';
import { produits } from '@/lib/produits';

export default async function FicheProduit({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  // Étape 3 : params est attendu, puis converti en nombre
  const { id } = await params;
  const produit = produits.find((p) => p.id === Number(id));

  // Étape 4 : identifiant inconnu, on affiche la page 404
  if (!produit) notFound();

  // Étape 5 : affichage de la fiche
  return (
    <article>
      <h1>{produit.nom}</h1>
      <p>{produit.prix.toLocaleString('fr-FR')} FCFA</p>
    </article>
  );
}
CODE,
            'estimated_minutes' => 60,
            'exercise_title' => 'Annuaire des formations avec fiches',
            'exercise_description' => <<<'TXT'
Crée la section « formations » d’un centre de formation. Prépare un fichier de données avec au moins quatre formations (identifiant, titre, durée, prix en FCFA). Construis une page liste, une page fiche dynamique et un layout propre à la section.

Critères de réussite :
- La route « /formations » liste toutes les formations avec un « Link » vers chaque fiche.
- La route « /formations/[id] » affiche le titre, la durée et le prix de la formation demandée.
- Un identifiant inexistant ou non numérique, comme « /formations/999 » ou « /formations/abc », affiche la page 404 grâce à « notFound ».
- Un fichier « layout.tsx » dans le dossier « formations » ajoute un titre commun aux deux pages.
- Aucune balise « a » n’est utilisée pour les liens internes.
TXT,
            'exercise_hint' => 'Pour le cas non numérique, « Number("abc") » donne « NaN » et « find » ne trouvera rien : le « notFound » s’exécutera donc naturellement. N’oublie pas d’attendre « params » avec « await ».',
            'exercise_solution' => <<<'CODE'
// ===== lib/formations.ts =====
export const formations = [
  { id: 1, titre: 'HTML et CSS', duree: 12, prix: 15000 },
  { id: 2, titre: 'JavaScript', duree: 20, prix: 25000 },
  { id: 3, titre: 'React', duree: 24, prix: 30000 },
  { id: 4, titre: 'Next.js', duree: 24, prix: 35000 },
];

// ===== app/formations/layout.tsx =====
import type { ReactNode } from 'react';

export default function FormationsLayout({ children }: { children: ReactNode }) {
  return (
    <section>
      <h2>Nos formations</h2>
      {children}
    </section>
  );
}

// ===== app/formations/page.tsx =====
import Link from 'next/link';
import { formations } from '@/lib/formations';

export default function Liste() {
  return (
    <ul>
      {formations.map((f) => (
        <li key={f.id}>
          <Link href={`/formations/${f.id}`}>{f.titre}</Link>
        </li>
      ))}
    </ul>
  );
}

// ===== app/formations/[id]/page.tsx =====
import Link from 'next/link';
import { notFound } from 'next/navigation';
import { formations } from '@/lib/formations';

export default async function Fiche({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  // "abc" donne NaN : aucune formation trouvée, donc 404
  const formation = formations.find((f) => f.id === Number(id));
  if (!formation) notFound();

  return (
    <article>
      <h1>{formation.titre}</h1>
      <p>Durée : {formation.duree} h</p>
      <p>Prix : {formation.prix.toLocaleString('fr-FR')} FCFA</p>
      <Link href="/formations">Retour à la liste</Link>
    </article>
  );
}
CODE,
        ],

        'Server et Client Components' => [
            'description' => 'Comprendre la frontière entre composants serveur et composants client, et choisir où placer chaque morceau d’interface.',
            'objective' => 'Décider pour chaque composant s’il s’exécute sur le serveur ou dans le navigateur, et isoler l’interactivité dans de petits Client Components.',
            'content' => <<<'MD'
## Pourquoi cette notion

C’est la notion la plus déroutante de l’App Router, et la plus importante. Elle décide de la quantité de JavaScript envoyée au téléphone de l’utilisateur, de la sécurité de tes secrets et de la façon d’accéder aux données. Une boutique dont toute l’interface est exécutée dans le navigateur charge lentement sur une connexion faible. Une boutique qui exécute sur le serveur tout ce qui n’a pas besoin d’interaction envoie moins de code et s’affiche plus vite.

## Les concepts clés

### Les Server Components

Par défaut, un composant de l’App Router est un Server Component. Il s’exécute uniquement sur le serveur, au moment de la requête ou du build. Il peut être asynchrone, donc utiliser « await » directement, lire une base de données, appeler une API avec une clé secrète. Son code n’est pas envoyé au navigateur : seul le résultat l’est. En contrepartie, il ne peut pas utiliser de state, d’effet ni d’événement comme un clic.

### Les Client Components

Un fichier qui commence par la directive « use client » déclare un Client Component. Il est envoyé au navigateur et peut utiliser « useState », « useEffect », les gestionnaires d’événements et les API du navigateur comme le stockage local. Il est d’abord rendu en HTML sur le serveur, puis rendu interactif dans le navigateur.

### La frontière

La directive « use client » marque une frontière : le fichier et tout ce qu’il importe passent côté client. Il faut donc la placer le plus bas possible dans l’arbre. Une page entière marquée « use client » perd les avantages du serveur. La bonne approche consiste à garder la page en Server Component et à n’extraire en Client Component que le petit morceau interactif, comme un bouton « Ajouter au panier ».

### Passer des données de l’un à l’autre

Un Server Component peut importer un Client Component et lui passer des props, à condition qu’elles soient sérialisables : textes, nombres, tableaux, objets simples. Il ne peut pas lui passer une fonction ordinaire. Un Client Component peut afficher des Server Components reçus en « children ». Un Client Component ne peut pas importer directement un Server Component.

### Comment choisir

Pose-toi une question : ce morceau a-t-il besoin d’un événement, d’un state, d’un effet ou d’une API du navigateur ? Si oui, c’est un Client Component. Sinon, laisse-le côté serveur.

## Exemple pas à pas

Le code d’exemple affiche une fiche produit avec un bouton de quantité. À l’étape 1, « BoutonQuantite » est un Client Component : il commence par « use client » et utilise « useState ». À l’étape 2, la page « FicheProduit » reste un Server Component asynchrone qui récupère les données et lit une variable secrète. À l’étape 3, la page importe le bouton et lui passe uniquement des props simples, le prix et le nom. Le serveur fait le travail lourd, le navigateur ne reçoit que l’interactivité nécessaire.

## Erreurs fréquentes

- Utiliser « useState » dans un composant sans « use client » : Next.js affiche une erreur. Extrais la partie interactive dans un fichier marqué « use client ».
- Mettre « use client » tout en haut de chaque page par précaution : tu perds les bénéfices du serveur. Descends la directive au plus petit composant.
- Utiliser un secret dans un Client Component : il est exposé au navigateur. Garde les secrets dans le code serveur.
- Passer une fonction en prop d’un Server Component à un Client Component : ce n’est pas sérialisable. Définis la fonction dans le composant client ou utilise une Server Action.
- Importer un Server Component dans un Client Component : il devient client à son tour. Passe-le plutôt en « children ».

## Bonnes pratiques

- Garde la valeur par défaut serveur et ajoute « use client » seulement quand c’est nécessaire.
- Isole les éléments interactifs dans de petits composants nommés clairement.
- Récupère les données dans les Server Components, au plus près de leur usage.
- Ne place jamais de clé secrète dans du code qui peut s’exécuter côté client.

## Auto-évaluation

- Quelle est la nature par défaut d’un composant dans l’App Router ?
- Que peut faire un Server Component qu’un Client Component ne peut pas faire, et inversement ?
- Que fait la directive « use client » et où la placer ?
- Quels types de props peut-on passer d’un composant serveur à un composant client ?
- Comment décider si un composant doit être client ?

## À retenir

- Les composants sont des Server Components par défaut.
- « use client » est nécessaire pour le state, les effets et les événements.
- La frontière client doit rester le plus bas possible dans l’arbre.
- Les props passées au client doivent être sérialisables.
- Les secrets et l’accès direct aux données restent côté serveur.
MD,
            'code_example' => <<<'CODE'
// ===== components/BoutonQuantite.tsx : étape 1, Client Component =====
'use client';

import { useState } from 'react';

type Props = { nom: string; prix: number };

export default function BoutonQuantite({ nom, prix }: Props) {
  // useState n'est permis que dans un Client Component
  const [quantite, setQuantite] = useState(1);

  return (
    <div>
      <button onClick={() => setQuantite((q) => Math.max(1, q - 1))}>-</button>
      <span> {quantite} </span>
      <button onClick={() => setQuantite((q) => q + 1)}>+</button>
      <p>
        {nom} x {quantite} = {(prix * quantite).toLocaleString('fr-FR')} FCFA
      </p>
    </div>
  );
}

// ===== app/produits/[id]/page.tsx : étape 2, Server Component =====
import { notFound } from 'next/navigation';
import BoutonQuantite from '@/components/BoutonQuantite';

type Produit = { id: number; nom: string; prix: number };

// Fonction serveur : peut utiliser une clé secrète sans l'exposer
async function chargerProduit(id: string): Promise<Produit | null> {
  const reponse = await fetch(`${process.env.API_URL}/produits/${id}`, {
    headers: { Authorization: `Bearer ${process.env.API_SECRET}` },
    cache: 'no-store',
  });
  if (!reponse.ok) return null;
  return reponse.json();
}

export default async function FicheProduit({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const produit = await chargerProduit(id);
  if (!produit) notFound();

  return (
    <article>
      <h1>{produit.nom}</h1>
      {/* Étape 3 : seulement des props simples vers le composant client */}
      <BoutonQuantite nom={produit.nom} prix={produit.prix} />
    </article>
  );
}
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Carte de plat avec compteur de portions',
            'exercise_description' => <<<'TXT'
Construis la page d’un plat pour une application de commande de repas. Le plat est récupéré côté serveur dans un tableau local. L’utilisateur peut choisir le nombre de portions avec des boutons et voir le total mis à jour. Sépare correctement ce qui est serveur et ce qui est client.

Critères de réussite :
- La page du plat est un Server Component asynchrone, sans la directive « use client ».
- Un composant « SelecteurPortions » commence par « use client » et gère le nombre de portions avec « useState ».
- Les props passées au composant client sont uniquement un nom (texte) et un prix (nombre).
- Le nombre de portions ne descend jamais sous 1 et le total est affiché en FCFA.
- Le total est calculé pendant le rendu et n’est pas stocké dans le state.
TXT,
            'exercise_hint' => 'Commence par la page serveur qui affiche le titre du plat, puis crée le composant client dans « components ». Le total vaut « prix * portions » et se calcule directement dans le JSX.',
            'exercise_solution' => <<<'CODE'
// ===== components/SelecteurPortions.tsx =====
'use client';

import { useState } from 'react';

export default function SelecteurPortions({
  nom,
  prix,
}: {
  nom: string;
  prix: number;
}) {
  const [portions, setPortions] = useState(1);
  // Valeur dérivée : calculée au rendu, pas stockée
  const total = prix * portions;

  return (
    <div>
      <button onClick={() => setPortions((p) => Math.max(1, p - 1))}>-</button>
      <span> {portions} portion(s) </span>
      <button onClick={() => setPortions((p) => p + 1)}>+</button>
      <p>
        {nom} : {total.toLocaleString('fr-FR')} FCFA
      </p>
    </div>
  );
}

// ===== app/plats/[id]/page.tsx : Server Component (aucun "use client") =====
import { notFound } from 'next/navigation';
import SelecteurPortions from '@/components/SelecteurPortions';

const plats = [
  { id: 1, nom: 'Poulet braisé', prix: 3500, description: 'Servi avec attiéké.' },
  { id: 2, nom: 'Alloco poisson', prix: 4000, description: 'Banane plantain frite.' },
];

export default async function PagePlat({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const plat = plats.find((p) => p.id === Number(id));
  if (!plat) notFound();

  return (
    <article>
      <h1>{plat.nom}</h1>
      <p>{plat.description}</p>
      {/* Seulement un texte et un nombre : props sérialisables */}
      <SelecteurPortions nom={plat.nom} prix={plat.prix} />
    </article>
  );
}
CODE,
        ],

        'Données, formulaires et API' => [
            'description' => 'Récupérer des données dans les Server Components, créer des endpoints avec les Route Handlers et traiter des formulaires avec les Server Actions.',
            'objective' => 'Charger des données côté serveur, exposer un endpoint GET et POST validé, et traiter un formulaire avec une Server Action.',
            'content' => <<<'MD'
## Pourquoi cette notion

Une application sans données n’est qu’une vitrine. Une boutique affiche des produits, un service de réservation enregistre des demandes, un portail d’école lit des notes. Il faut donc savoir lire les données, en écrire et exposer des points d’accès pour d’autres clients, par exemple une application mobile. Next.js permet de faire tout cela dans le même projet, sans serveur séparé pour les besoins courants.

## Les concepts clés

### Lire les données dans un Server Component

Un Server Component asynchrone peut appeler « fetch » ou interroger une base de données directement, avec « await ». Il n’y a pas d’effet, pas d’état de chargement à écrire à la main : la page est envoyée une fois les données prêtes. Tu peux contrôler la mise en cache des réponses : demander des données toujours fraîches, ou accepter qu’elles soient revalidées après un délai. Les comportements par défaut ont évolué selon les versions, donc précise ton choix explicitement quand il compte.

### Les Route Handlers

Un fichier « route.ts » dans un dossier de « app » définit un endpoint HTTP. Tu y exportes des fonctions nommées d’après les méthodes : « GET », « POST », « PUT », « DELETE ». Chacune reçoit la requête et retourne une réponse, par exemple avec « Response.json ». Ces endpoints servent à une application mobile, à un service externe ou à un composant client qui doit appeler le serveur. Un dossier ne peut pas contenir à la fois un « page.tsx » et un « route.ts » à la même adresse.

### Valider les entrées

Tout ce qui vient du client est suspect : corps de requête, formulaires, paramètres d’URL. Vérifie la présence, le type et les limites avant d’utiliser une valeur. Retourne un code HTTP adapté : 400 pour une requête invalide, 404 pour une ressource absente, 201 pour une création réussie.

### Les Server Actions

Une Server Action est une fonction asynchrone marquée « use server », exécutée sur le serveur et branchée directement sur un formulaire via la propriété « action ». Elle reçoit les données du formulaire sous forme de « FormData ». Après une modification, on appelle « revalidatePath » pour que la page se mette à jour avec les nouvelles données. Une Server Action est un point d’entrée public : valide ses entrées comme pour un endpoint.

### Quel outil choisir

Pour un formulaire de ton application, la Server Action est la plus simple. Pour une API consommée par d’autres clients, utilise un Route Handler.

## Exemple pas à pas

Le code d’exemple gère des avis clients. À l’étape 1, un Route Handler « GET » retourne la liste en JSON. À l’étape 2, le « POST » du même fichier valide le corps et répond 400 ou 201. À l’étape 3, la page serveur affiche les avis. À l’étape 4, une Server Action lit « FormData », valide et ajoute l’avis. À l’étape 5, « revalidatePath » rafraîchit la page. Le formulaire est branché avec la propriété « action », sans gestionnaire côté navigateur.

## Erreurs fréquentes

- Faire confiance aux données du formulaire : une valeur vide ou trop longue corrompt les données. Valide systématiquement côté serveur.
- Retourner toujours le code 200, même en cas d’erreur : le client ne peut pas distinguer les cas. Utilise 400, 404 ou 500 selon la situation.
- Oublier « use server » : la fonction est traitée comme du code ordinaire et ne peut pas servir d’action. Ajoute la directive en tête de la fonction ou du fichier.
- Ne pas appeler « revalidatePath » : la page affiche encore les anciennes données. Revalide après chaque écriture.
- Appeler son propre Route Handler depuis un Server Component en passant par le réseau : c’est un détour inutile. Appelle directement la fonction de données partagée.
- Exposer un secret dans une réponse JSON : il devient public. Ne retourne que les champs nécessaires.

## Bonnes pratiques

- Centralise l’accès aux données dans un module partagé par pages, actions et endpoints.
- Valide toutes les entrées et retourne des messages d’erreur clairs.
- Utilise les codes HTTP de façon cohérente.
- Précise ta stratégie de cache quand la fraîcheur des données compte.
- Vérifie l’identité de l’utilisateur dans chaque action sensible.

## Auto-évaluation

- Comment un Server Component récupère-t-il des données ?
- Comment déclare-t-on un endpoint GET et POST ?
- Quels codes HTTP utiliser pour une création, une erreur de validation et une ressource absente ?
- Qu’est-ce qu’une Server Action et comment la branche-t-on sur un formulaire ?
- Pourquoi appeler « revalidatePath » après une écriture ?

## À retenir

- Les Server Components lisent les données directement avec « await ».
- « route.ts » définit des endpoints HTTP par méthode.
- Toute entrée utilisateur se valide côté serveur.
- Une Server Action traite un formulaire sans écrire d’API.
- « revalidatePath » met la page à jour après une modification.
MD,
            'code_example' => <<<'CODE'
// ===== lib/avis.ts : accès aux données partagé (mémoire, pour l'exemple) =====
export type Avis = { id: number; auteur: string; message: string };
const avis: Avis[] = [{ id: 1, auteur: 'Awa', message: 'Livraison rapide.' }];

export const listerAvis = () => avis;
export function ajouterAvis(auteur: string, message: string): Avis {
  const nouveau = { id: Date.now(), auteur, message };
  avis.push(nouveau);
  return nouveau;
}

// ===== app/api/avis/route.ts : étapes 1 et 2 =====
import { ajouterAvis, listerAvis } from '@/lib/avis';

export async function GET() {
  return Response.json(listerAvis());
}

export async function POST(request: Request) {
  const corps = await request.json().catch(() => null);
  // Validation : 400 si les champs sont absents ou vides
  if (!corps?.auteur?.trim() || !corps?.message?.trim()) {
    return Response.json({ erreur: 'Auteur et message requis' }, { status: 400 });
  }
  return Response.json(ajouterAvis(corps.auteur.trim(), corps.message.trim()), {
    status: 201,
  });
}

// ===== app/avis/actions.ts : étape 4, Server Action =====
'use server';
import { revalidatePath } from 'next/cache';
import { ajouterAvis } from '@/lib/avis';

export async function envoyerAvis(formData: FormData) {
  const auteur = String(formData.get('auteur') ?? '').trim();
  const message = String(formData.get('message') ?? '').trim();
  if (!auteur || !message) return; // validation côté serveur
  ajouterAvis(auteur, message);
  revalidatePath('/avis'); // étape 5 : rafraîchit la page
}

// ===== app/avis/page.tsx : étape 3, Server Component =====
import { listerAvis } from '@/lib/avis';
import { envoyerAvis } from './actions';

export default function PageAvis() {
  return (
    <main>
      <ul>
        {listerAvis().map((a) => (
          <li key={a.id}>{a.auteur} : {a.message}</li>
        ))}
      </ul>
      <form action={envoyerAvis}>
        <input name="auteur" placeholder="Votre nom" required />
        <input name="message" placeholder="Votre avis" required />
        <button type="submit">Envoyer</button>
      </form>
    </main>
  );
}
CODE,
            'estimated_minutes' => 75,
            'exercise_title' => 'Prise de rendez-vous pour une clinique',
            'exercise_description' => <<<'TXT'
Construis un petit module de rendez-vous. Les données sont stockées en mémoire dans un module partagé. Crée un Route Handler pour lister et créer des rendez-vous, et une page avec un formulaire branché sur une Server Action. Chaque rendez-vous a un nom de patient, un motif et une date.

Critères de réussite :
- « GET /api/rendez-vous » retourne la liste en JSON.
- « POST /api/rendez-vous » retourne 400 si le nom ou le motif est vide, et 201 avec le rendez-vous créé sinon.
- Le formulaire de la page utilise une Server Action marquée « use server » qui lit un « FormData ».
- La Server Action valide les champs côté serveur et appelle « revalidatePath » après l’ajout.
- La page est un Server Component qui lit les rendez-vous directement depuis le module de données, sans appel réseau vers l’API.
TXT,
            'exercise_hint' => 'Écris d’abord le module de données avec « lister » et « ajouter », puis réutilise-le dans le Route Handler, la Server Action et la page. Pour la validation, teste « trim() » avant d’ajouter.',
            'exercise_solution' => <<<'CODE'
// ===== lib/rendez-vous.ts =====
export type RendezVous = { id: number; patient: string; motif: string; date: string };
const rendezVous: RendezVous[] = [];

export const listerRendezVous = () => rendezVous;
export function ajouterRendezVous(patient: string, motif: string, date: string) {
  const nouveau = { id: Date.now(), patient, motif, date };
  rendezVous.push(nouveau);
  return nouveau;
}

// ===== app/api/rendez-vous/route.ts =====
import { ajouterRendezVous, listerRendezVous } from '@/lib/rendez-vous';

export async function GET() {
  return Response.json(listerRendezVous());
}

export async function POST(request: Request) {
  const corps = await request.json().catch(() => null);
  const patient = String(corps?.patient ?? '').trim();
  const motif = String(corps?.motif ?? '').trim();
  const date = String(corps?.date ?? '').trim();
  if (!patient || !motif) {
    return Response.json({ erreur: 'Patient et motif requis' }, { status: 400 });
  }
  return Response.json(ajouterRendezVous(patient, motif, date), { status: 201 });
}

// ===== app/rendez-vous/actions.ts =====
'use server';
import { revalidatePath } from 'next/cache';
import { ajouterRendezVous } from '@/lib/rendez-vous';

export async function prendreRendezVous(formData: FormData) {
  const patient = String(formData.get('patient') ?? '').trim();
  const motif = String(formData.get('motif') ?? '').trim();
  const date = String(formData.get('date') ?? '').trim();
  if (!patient || !motif) return; // refus silencieux côté serveur
  ajouterRendezVous(patient, motif, date);
  revalidatePath('/rendez-vous');
}

// ===== app/rendez-vous/page.tsx : Server Component =====
import { listerRendezVous } from '@/lib/rendez-vous';
import { prendreRendezVous } from './actions';

export default function PageRendezVous() {
  const liste = listerRendezVous();
  return (
    <main>
      <h1>Rendez-vous</h1>
      {liste.length === 0 && <p>Aucun rendez-vous pour le moment.</p>}
      <ul>
        {liste.map((r) => (
          <li key={r.id}>{r.patient} : {r.motif} ({r.date})</li>
        ))}
      </ul>
      <form action={prendreRendezVous}>
        <input name="patient" placeholder="Nom du patient" required />
        <input name="motif" placeholder="Motif" required />
        <input name="date" type="date" required />
        <button type="submit">Réserver</button>
      </form>
    </main>
  );
}
CODE,
        ],

        'Performance et bonnes pratiques' => [
            'description' => 'Optimiser images, chargements, erreurs et métadonnées avec les fichiers et composants natifs de Next.js.',
            'objective' => 'Utiliser next/image, loading.tsx, error.tsx, not-found et les métadonnées pour construire une page rapide et robuste.',
            'content' => <<<'MD'
## Pourquoi cette notion

Les utilisateurs de ton application naviguent souvent sur un téléphone, avec une connexion mobile qui varie et un forfait de données limité. Une page lente, une image de plusieurs mégaoctets ou un écran blanc quand le serveur échoue font partir les visiteurs et abîment l’image du client. Next.js fournit des outils intégrés pour traiter ces problèmes sans bibliothèque supplémentaire : à toi de savoir les utiliser.

## Les concepts clés

### Les images avec next/image

Le composant « Image » de « next/image » redimensionne les images, les sert dans des formats modernes adaptés au navigateur et les charge de façon différée quand elles sont loin de l’écran. Tu dois fournir la largeur et la hauteur pour éviter que la page saute pendant le chargement, ou utiliser le mode de remplissage dans un conteneur dimensionné. La propriété « alt » est obligatoire et décrit l’image pour l’accessibilité. Pour une image distante, il faut autoriser son domaine dans la configuration. Pour l’image principale visible dès l’ouverture, on peut demander un chargement prioritaire.

### Les états de chargement avec loading.tsx

Un fichier « loading.tsx » dans un dossier est affiché automatiquement pendant que la page de ce dossier attend ses données. L’utilisateur voit tout de suite une structure ou un message, au lieu d’un écran vide. Pour un contrôle plus fin, tu enveloppes une partie lente dans « Suspense » avec un contenu de remplacement : le reste de la page s’affiche sans attendre.

### Les erreurs avec error.tsx et not-found

Un fichier « error.tsx » capture les erreurs d’exécution de son segment et affiche une interface de secours. Ce doit être un Client Component. Il reçoit l’erreur et une fonction pour réessayer. Un fichier « not-found.tsx » personnalise la page affichée quand « notFound » est appelé ou quand la route n’existe pas. Ces fichiers évitent de montrer à l’utilisateur une page technique incompréhensible.

### Les métadonnées

L’export « metadata » d’une page ou d’un layout définit le titre et la description affichés dans l’onglet et dans les résultats de recherche. Pour une page dynamique, la fonction « generateMetadata » calcule ces valeurs à partir des données, comme le nom du produit. Un bon titre et une bonne description améliorent le référencement et l’aperçu lors d’un partage sur WhatsApp ou les réseaux.

### Le reste de la performance

Envoie moins de JavaScript en gardant les composants côté serveur. Charge à la demande les composants lourds avec l’import dynamique. 

## Exemple pas à pas

Le code d’exemple optimise une page de produit. À l’étape 1, « generateMetadata » produit un titre propre à chaque produit. À l’étape 2, la page utilise « Image » avec dimensions, « alt » et chargement prioritaire. À l’étape 3, « loading.tsx » affiche un message pendant l’attente. À l’étape 4, « error.tsx », marqué « use client », affiche un message en français et un bouton pour réessayer. À l’étape 5, « not-found.tsx » donne une page 404 claire avec un lien de retour.

## Erreurs fréquentes

- Utiliser une balise « img » avec une image de plusieurs mégaoctets : la page est lourde et lente. Utilise « Image » et des fichiers raisonnables.
- Oublier la largeur et la hauteur : Next.js lève une erreur ou la page saute au chargement. Fournis les dimensions.
- Oublier « alt » : l’image n’est pas accessible. Décris l’image en une phrase courte.
- Écrire « error.tsx » sans « use client » : il ne fonctionne pas, car c’est un composant client obligatoire. Ajoute la directive.
- Charger une image distante sans autoriser son domaine : l’image ne s’affiche pas. Déclare le domaine dans la configuration.

## Bonnes pratiques

- Ajoute un « loading.tsx » aux pages qui attendent des données.
- Définis un titre et une description uniques pour chaque page.
- Teste ton site avec une connexion lente simulée dans les outils du navigateur.
- Garde le JavaScript client au minimum en laissant un maximum de composants côté serveur.

## Auto-évaluation

- Quels avantages apporte le composant « Image » par rapport à une balise « img » ?
- Quand « loading.tsx » est-il affiché ?
- Pourquoi « error.tsx » doit-il être un Client Component ?
- Comment définir un titre différent pour chaque produit ?
- Quels moyens réduisent la quantité de JavaScript envoyée au navigateur ?

## À retenir

- « Image » optimise taille, format et chargement des images.
- « loading.tsx » et « Suspense » améliorent la perception de la vitesse.
- « error.tsx » et « not-found.tsx » protègent l’utilisateur des pages cassées.
- Les métadonnées servent le référencement et le partage.
- Moins de JavaScript côté client signifie une page plus rapide sur mobile.
MD,
            'code_example' => <<<'CODE'
// ===== app/produits/[id]/page.tsx =====
import Image from 'next/image';
import { notFound } from 'next/navigation';
import type { Metadata } from 'next';

const produits = [
  { id: 1, nom: 'Pagne wax', prix: 7500, image: '/images/pagne.jpg' },
];

const trouver = (id: string) => produits.find((p) => p.id === Number(id));
// Étape 1 : titre et description propres à chaque produit
export async function generateMetadata({
  params,
}: {
  params: Promise<{ id: string }>;
}): Promise<Metadata> {
  const { id } = await params;
  const produit = trouver(id);
  return { title: produit ? `${produit.nom} | Ma boutique` : 'Produit introuvable' };
}

export default async function PageProduit({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const produit = trouver(id);
  if (!produit) notFound(); // déclenche not-found.tsx

  return (
    <article>
      <h1>{produit.nom}</h1>
      {/* Étape 2 : dimensions, alt et chargement prioritaire (image visible d'emblée) */}
      <Image src={produit.image} alt={`Photo de ${produit.nom}`} width={600} height={400} priority />
      <p>{produit.prix.toLocaleString('fr-FR')} FCFA</p>
    </article>
  );
}

// ===== app/produits/[id]/loading.tsx : étape 3 =====
export default function Loading() {
  return <p>Chargement du produit...</p>;
}

// ===== app/produits/[id]/error.tsx : étape 4, Client Component obligatoire =====
'use client';

export default function Erreur({ reset }: { error: Error; reset: () => void }) {
  return <button onClick={() => reset()}>Un problème est survenu : réessayer</button>;
}

// ===== app/produits/[id]/not-found.tsx : étape 5 =====
import Link from 'next/link';

export default function NonTrouve() {
  return (
    <p>
      Ce produit n'existe pas. <Link href="/produits">Retour au catalogue</Link>
    </p>
  );
}
CODE,
            'estimated_minutes' => 65,
            'exercise_title' => 'Rendre robuste la page d’un hôtel',
            'exercise_description' => <<<'TXT'
Améliore la page de détail d’une chambre d’hôtel (« /chambres/[id] ») pour la rendre rapide et robuste. Elle doit afficher une photo, un titre et un prix, avoir ses propres métadonnées et gérer l’attente, l’erreur et l’absence de chambre.

Critères de réussite :
- La photo utilise « Image » de « next/image » avec largeur, hauteur et un « alt » descriptif.
- Une fonction « generateMetadata » retourne un titre contenant le nom de la chambre.
- Un fichier « loading.tsx » affiche un message d’attente en français.
- Un fichier « error.tsx » commence par « use client », affiche un message clair et propose un bouton « Réessayer » qui appelle « reset ».
- Un fichier « not-found.tsx » s’affiche pour un identifiant inexistant grâce à « notFound » et propose un lien de retour.
TXT,
            'exercise_hint' => 'Place les quatre fichiers « loading.tsx », « error.tsx », « not-found.tsx » et « page.tsx » dans le même dossier « app/chambres/[id] ». Réutilise le schéma de l’exemple : trouver la chambre, appeler « notFound » si elle est absente.',
            'exercise_solution' => <<<'CODE'
// ===== app/chambres/[id]/page.tsx =====
import Image from 'next/image';
import { notFound } from 'next/navigation';
import type { Metadata } from 'next';

const chambres = [
  { id: 1, nom: 'Chambre Lagune', prix: 35000, photo: '/images/lagune.jpg' },
  { id: 2, nom: 'Suite Palmier', prix: 80000, photo: '/images/palmier.jpg' },
];

const trouver = (id: string) => chambres.find((c) => c.id === Number(id));

export async function generateMetadata({
  params,
}: {
  params: Promise<{ id: string }>;
}): Promise<Metadata> {
  const { id } = await params;
  const chambre = trouver(id);
  return { title: chambre ? `${chambre.nom} | Hôtel Akwaba` : 'Chambre introuvable' };
}

export default async function PageChambre({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const chambre = trouver(id);
  if (!chambre) notFound();

  return (
    <article>
      <h1>{chambre.nom}</h1>
      <Image
        src={chambre.photo}
        alt={`Photo de la ${chambre.nom}`}
        width={800}
        height={500}
        priority
      />
      <p>{chambre.prix.toLocaleString('fr-FR')} FCFA la nuit</p>
    </article>
  );
}

// ===== app/chambres/[id]/loading.tsx =====
export default function Loading() {
  return <p>Chargement de la chambre...</p>;
}

// ===== app/chambres/[id]/error.tsx =====
'use client';

export default function Erreur({ reset }: { error: Error; reset: () => void }) {
  return (
    <div role="alert">
      <p>Impossible d'afficher cette chambre pour le moment.</p>
      <button onClick={() => reset()}>Réessayer</button>
    </div>
  );
}

// ===== app/chambres/[id]/not-found.tsx =====
import Link from 'next/link';

export default function NonTrouvee() {
  return (
    <p>
      Cette chambre n'existe pas. <Link href="/chambres">Voir toutes les chambres</Link>
    </p>
  );
}
CODE,
        ],

        'Projet final Next.js' => [
            'description' => 'Assembler routing, composants serveur et client, données, formulaire et endpoint dans un tableau de bord complet.',
            'objective' => 'Livrer un mini-projet Next.js structuré avec routes protégées, liste, page détail, formulaire validé et endpoint API.',
            'content' => <<<'MD'
## Pourquoi cette notion

Ce projet rassemble tout ce que tu as vu : routing, layouts, séparation entre serveur et client, données, formulaires, API, performance. C’est aussi le type de livrable qu’on présente à un client ou à un recruteur. Un tableau de bord avec une liste, un détail et un formulaire correspond à la plupart des applications de gestion réelles, qu’il s’agisse de suivre des commandes, des clients, des stocks ou des réservations.

## Les concepts clés

### Cadrer le projet

Écris le périmètre avant de coder. Par exemple, un tableau de bord de suivi de commandes pour un commerce : une page de connexion simple, une liste des commandes, une page de détail pour chacune, un formulaire pour en ajouter et un endpoint JSON qui expose la liste. Ce périmètre court te donne aussi les critères pour savoir quand tu as terminé.

### Organiser le code

Place les routes dans « app », les composants réutilisables dans « components » et l’accès aux données dans « lib ». Un module de données unique est utilisé par les pages, les actions et les endpoints : tu ne dupliques jamais la logique. Un groupe de routes entre parenthèses, comme « (tableau-de-bord) », permet de donner un layout avec navigation aux pages internes sans changer les URL.

### Protéger les pages

Une authentification complète demande une bibliothèque ou un service spécialisé, ce qui dépasse ce cours. Pour ce projet, une protection minimale suffit pour comprendre le principe : un cookie de session défini par une Server Action après vérification d’un mot de passe, lu côté serveur dans le layout du tableau de bord, avec une redirection vers la page de connexion si le cookie est absent. N’oublie pas qu’une protection côté interface ne suffit jamais : chaque Server Action et chaque endpoint doit aussi vérifier la session.

### Serveur d’abord, client si nécessaire

Garde les pages en Server Components qui lisent les données. N’utilise un Client Component que pour une vraie interaction, comme un filtre instantané ou un bouton avec confirmation. Ajoute « loading.tsx », « error.tsx » et « not-found.tsx » pour les cas limites.

### Le livrable

Un projet terminé inclut un fichier README, un fichier « .env.example », un build qui passe sans erreur et un historique Git lisible. Teste aussi sur la largeur d’un téléphone.

## Exemple pas à pas

Le code d’exemple montre le cœur de la protection et du formulaire. À l’étape 1, une Server Action de connexion compare le mot de passe fourni à une variable d’environnement et pose un cookie. À l’étape 2, le layout du tableau de bord lit ce cookie côté serveur et redirige si besoin. À l’étape 3, la page liste lit les commandes depuis le module de données. À l’étape 4, une Server Action ajoute une commande après validation et revalide la page. À l’étape 5, un Route Handler vérifie aussi le cookie avant de répondre. Chaque point d’entrée contrôle donc la session lui-même.

## Erreurs fréquentes

- Protéger seulement l’affichage : n’importe qui peut appeler directement une action ou un endpoint. Vérifie la session dans chaque point d’entrée.
- Stocker un mot de passe en clair dans le code : il fuite avec Git. Place-le dans « .env.local » et ne le versionne pas.
- Marquer des pages entières avec « use client » : le JavaScript envoyé grossit inutilement. Isole seulement l’interactivité.
- Oublier « revalidatePath » après un ajout : la liste ne se met pas à jour. Revalide après chaque écriture.
- Ne pas gérer les cas vides, d’erreur et d’identifiant inconnu : l’application paraît cassée. Ajoute les fichiers dédiés et les messages.
- Livrer sans lancer « npm run build » : des erreurs de types apparaissent à la mise en ligne. Construis avant de livrer.

## Bonnes pratiques

- Avance par fonctionnalités verticales : une route complète à la fois, avec un commit à chaque fois.
- Garde un module de données unique, utilisé par toutes les couches.
- Valide chaque entrée côté serveur et retourne des codes HTTP cohérents.
- Documente le lancement et les variables dans le README et le fichier d’exemple.
- Teste le parcours complet sur la taille d’écran d’un téléphone.

## Auto-évaluation

- Quelles routes composent ton application et lesquelles sont protégées ?
- Où se trouve la logique d’accès aux données et qui l’utilise ?
- Quels composants sont des Client Components et pourquoi ?
- Comment vérifies-tu la session dans une Server Action et un endpoint ?
- Que se passe-t-il pour une commande inexistante, une saisie invalide ou une panne ?

## À retenir

- Un projet commence par un périmètre écrit et une organisation claire des dossiers.
- Un module de données unique évite la duplication entre pages, actions et API.
- La protection doit être vérifiée dans chaque point d’entrée côté serveur.
- Les composants restent côté serveur sauf besoin réel d’interaction.
- Un livrable complet inclut README, variables documentées et build réussi.
MD,
            'code_example' => <<<'CODE'
// ===== lib/commandes.ts : module de données unique =====
export type Commande = { id: number; client: string; montant: number };
const commandes: Commande[] = [{ id: 1, client: 'Koffi', montant: 12500 }];
export const lister = () => commandes;
export const trouver = (id: number) => commandes.find((c) => c.id === id);
export function ajouter(client: string, montant: number) {
  commandes.push({ id: Date.now(), client, montant });
}

// ===== lib/session.ts : lecture du cookie côté serveur =====
import { cookies } from 'next/headers';
export async function estConnecte() {
  return (await cookies()).get('session')?.value === 'ok';
}

// ===== app/connexion/actions.ts : étape 1 =====
'use server';
import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';

export async function seConnecter(formData: FormData) {
  // Le mot de passe vient de l'environnement, jamais du code
  if (formData.get('motdepasse') !== process.env.MOT_DE_PASSE_ADMIN) return;
  (await cookies()).set('session', 'ok', { httpOnly: true, path: '/' });
  redirect('/commandes');
}

// ===== app/(tableau-de-bord)/layout.tsx : étape 2, protection =====
import { redirect } from 'next/navigation';
import type { ReactNode } from 'react';
import { estConnecte } from '@/lib/session';

export default async function TableauLayout({ children }: { children: ReactNode }) {
  if (!(await estConnecte())) redirect('/connexion');
  return <main>{children}</main>;
}

// ===== app/(tableau-de-bord)/commandes/actions.ts : étape 4 =====
'use server';
import { revalidatePath } from 'next/cache';
import { ajouter } from '@/lib/commandes';
import { estConnecte } from '@/lib/session';

export async function creerCommande(formData: FormData) {
  if (!(await estConnecte())) return; // chaque action vérifie la session
  const client = String(formData.get('client') ?? '').trim();
  const montant = Number(formData.get('montant'));
  if (!client || !(montant > 0)) return;
  ajouter(client, montant);
  revalidatePath('/commandes');
}

// ===== app/api/commandes/route.ts : étape 5 =====
import { lister } from '@/lib/commandes';
import { estConnecte } from '@/lib/session';

export async function GET() {
  if (!(await estConnecte())) return Response.json({ erreur: 'Non autorisé' }, { status: 401 });
  return Response.json(lister());
}
CODE,
            'estimated_minutes' => 90,
            'exercise_title' => 'Mini-projet : tableau de bord de commandes',
            'exercise_description' => <<<'TXT'
Construis un tableau de bord de suivi de commandes avec Next.js et l’App Router. Il comprend une page de connexion, une liste des commandes, une page détail, un formulaire d’ajout et un endpoint JSON. Les données peuvent rester en mémoire dans un module partagé.

Livrables :
- Un projet Next.js fonctionnel avec dossiers « app », « components » et « lib » organisés.
- Un fichier README expliquant l’objectif, le lancement et les variables d’environnement, et un fichier « .env.example ».
- Un historique Git avec au moins quatre commits parlants.

Critères de réussite :
- Les pages internes redirigent vers « /connexion » sans session valide, et le mot de passe vient d’une variable d’environnement.
- La liste « /commandes » et la fiche « /commandes/[id] » existent, avec « notFound » pour un identifiant invalide.
- Le formulaire utilise une Server Action qui valide les champs, vérifie la session et appelle « revalidatePath ».
- « GET /api/commandes » renvoie le JSON si la session est valide et le code 401 sinon.
- « npm run build » passe sans erreur et seuls les composants réellement interactifs portent « use client ».
TXT,
            'exercise_hint' => 'Procède dans cet ordre : module de données, liste, fiche, formulaire, endpoint, puis protection. Pour la fiche, convertis l’identifiant avec « Number » et appelle « notFound » si la commande est absente. Pense à créer « loading.tsx » et « error.tsx » à la fin.',
            'exercise_solution' => <<<'CODE'
// ===== lib/commandes.ts =====
export type Commande = { id: number; client: string; montant: number };
const commandes: Commande[] = [
  { id: 1, client: 'Koffi', montant: 12500 },
  { id: 2, client: 'Aminata', montant: 8000 },
];
export const lister = () => commandes;
export const trouver = (id: number) => commandes.find((c) => c.id === id);
export function ajouter(client: string, montant: number) {
  commandes.push({ id: Date.now(), client, montant });
}

// ===== lib/session.ts =====
import { cookies } from 'next/headers';
export async function estConnecte() {
  return (await cookies()).get('session')?.value === 'ok';
}

// ===== app/connexion/actions.ts =====
'use server';
import { cookies } from 'next/headers';
import { redirect } from 'next/navigation';

export async function seConnecter(formData: FormData) {
  if (formData.get('motdepasse') !== process.env.MOT_DE_PASSE_ADMIN) return;
  (await cookies()).set('session', 'ok', { httpOnly: true, path: '/' });
  redirect('/commandes');
}

// ===== app/connexion/page.tsx =====
import { seConnecter } from './actions';

export default function Connexion() {
  return (
    <form action={seConnecter}>
      <input name="motdepasse" type="password" placeholder="Mot de passe" required />
      <button type="submit">Se connecter</button>
    </form>
  );
}

// ===== app/(tableau-de-bord)/layout.tsx =====
import { redirect } from 'next/navigation';
import type { ReactNode } from 'react';
import { estConnecte } from '@/lib/session';

export default async function TableauLayout({ children }: { children: ReactNode }) {
  if (!(await estConnecte())) redirect('/connexion');
  return <main>{children}</main>;
}

// ===== app/(tableau-de-bord)/commandes/actions.ts =====
'use server';
import { revalidatePath } from 'next/cache';
import { ajouter } from '@/lib/commandes';
import { estConnecte } from '@/lib/session';

export async function creerCommande(formData: FormData) {
  if (!(await estConnecte())) return;
  const client = String(formData.get('client') ?? '').trim();
  const montant = Number(formData.get('montant'));
  if (!client || !(montant > 0)) return;
  ajouter(client, montant);
  revalidatePath('/commandes');
}

// ===== app/(tableau-de-bord)/commandes/page.tsx =====
import Link from 'next/link';
import { lister } from '@/lib/commandes';
import { creerCommande } from './actions';

export default function ListeCommandes() {
  const commandes = lister();
  return (
    <section>
      <h1>Commandes</h1>
      {commandes.length === 0 && <p>Aucune commande.</p>}
      <ul>
        {commandes.map((c) => (
          <li key={c.id}>
            <Link href={`/commandes/${c.id}`}>{c.client}</Link> :{' '}
            {c.montant.toLocaleString('fr-FR')} FCFA
          </li>
        ))}
      </ul>
      <form action={creerCommande}>
        <input name="client" placeholder="Client" required />
        <input name="montant" type="number" min="1" placeholder="Montant" required />
        <button type="submit">Ajouter</button>
      </form>
    </section>
  );
}

// ===== app/(tableau-de-bord)/commandes/[id]/page.tsx =====
import { notFound } from 'next/navigation';
import { trouver } from '@/lib/commandes';

export default async function DetailCommande({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;
  const commande = trouver(Number(id));
  if (!commande) notFound();
  return (
    <article>
      <h1>Commande n°{commande.id}</h1>
      <p>Client : {commande.client}</p>
      <p>Montant : {commande.montant.toLocaleString('fr-FR')} FCFA</p>
    </article>
  );
}

// ===== app/api/commandes/route.ts =====
import { lister } from '@/lib/commandes';
import { estConnecte } from '@/lib/session';

export async function GET() {
  if (!(await estConnecte())) {
    return Response.json({ erreur: 'Non autorisé' }, { status: 401 });
  }
  return Response.json(lister());
}

// ===== .env.example =====
// MOT_DE_PASSE_ADMIN=
CODE,
        ],

    ],
];
