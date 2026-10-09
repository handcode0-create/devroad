---
title: Projet final TypeScript
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Ce projet final te fait construire, de bout en bout, une application complète en **React + TypeScript** : **DevRoad Tracker**, un suivi de roadmaps avec authentification simulée, API typée, validation Zod, tests et vérification continue. Tu y appliques tout ce qui précède : types primitifs, interfaces, unions discriminées, generics, hooks typés, configuration stricte et qualité.

À la fin du projet, tu auras :

- initialisé un projet React + TypeScript strict avec lint, format et tests ;
- modélisé le domaine par des types, des unions discriminées et des types utilitaires ;
- construit une couche d'API générique, avec validation Zod à la frontière ;
- développé des composants et hooks entièrement typés ;
- géré l'état global avec un reducer et un contexte ;
- livré une application vérifiée en continu (`tsc`, ESLint, Vitest), documentée et prête à déployer.

Prérequis : les six chapitres TypeScript précédents et de bonnes bases de React. Durée : environ six heures, à répartir en plusieurs séances. Outils : Node.js 20 ou plus, un éditeur avec TypeScript, Git.

## Cahier des charges

L'application permet à un apprenant de suivre ses roadmaps et d'enregistrer sa progression.

### Fonctionnalités obligatoires

1. **Connexion simulée** : un formulaire (e-mail + nom) enregistre un utilisateur dans le contexte et le mémorise. Une route protégée affiche le tableau de bord seulement si l'utilisateur est connecté.
2. **Catalogue** : liste des roadmaps depuis une API simulée, avec niveau, nombre de leçons et durée totale.
3. **Détail** : page d'une roadmap avec ses leçons, durée et état.
4. **Progression** : valider ou annuler une leçon ; pourcentage par roadmap et global ; persistance après rechargement.
5. **Filtres et tri** : par niveau, par texte, par titre ou durée.
6. **Notes** : ajouter, modifier et supprimer une note sur une leçon, avec validation.
7. **États de chargement** : chargement, succès, erreur avec bouton « Réessayer », résultat vide.
8. **Statistiques** : temps total étudié, leçons terminées par niveau.

### Contraintes techniques

- React 18 ou plus, TypeScript en mode `strict` avec `noUncheckedIndexedAccess`.
- **Aucun `any`**, aucun `@ts-ignore` ; les assertions `as` sont justifiées et rares.
- Données externes validées avec Zod ; les types du domaine sont déduits des schémas.
- États modélisés par des unions discriminées, avec vérification exhaustive.
- Au moins 15 tests (calculs, reducer, schémas, un composant).
- Accessibilité de base : éléments sémantiques, labels, focus visible, messages d'erreur avec `role="alert"`.

## Étape 1 : initialiser le projet

```bash
npm create vite@latest devroad-tracker -- --template react-ts
cd devroad-tracker
npm install
npm install zod react-router-dom
npm install --save-dev vitest @testing-library/react @testing-library/jest-dom jsdom prettier typescript-eslint
git init && git add . && git commit -m "chore: initialise le projet"
```

Renforce `tsconfig.app.json` : `strict`, `noUncheckedIndexedAccess`, `noImplicitReturns`, `noUnusedLocals`, `noFallthroughCasesInSwitch`, et l'alias `@/*` vers `src/*` (à reporter dans `vite.config.ts`). Configure ESLint avec `no-explicit-any`, `no-floating-promises` et `consistent-type-imports`, puis le script `verifier` du chapitre précédent.

Structure cible :

```text
src/
├── domaine/
│   ├── schemas.ts        (schémas Zod et types déduits)
│   ├── calculs.ts        (fonctions pures)
│   └── progression.ts    (reducer et actions)
├── api/
│   ├── client.ts         (getJson générique et gestion d'erreurs)
│   └── roadmaps.ts       (appels typés)
├── contextes/
│   ├── AuthContext.tsx
│   └── ProgressionContext.tsx
├── hooks/
│   ├── useLocalStorage.ts
│   └── useRessource.ts
├── composants/
├── pages/
├── App.tsx
└── main.tsx
```

## Étape 2 : modéliser le domaine avec Zod

Dans `src/domaine/schemas.ts`, une définition produit à la fois la validation et le type :

```ts
import { z } from 'zod';

export const SchemaNiveau = z.enum(['beginner', 'intermediate', 'professional']);
export type Niveau = z.infer<typeof SchemaNiveau>;

export const SchemaLecon = z.object({
  id: z.string().min(1),
  titre: z.string().min(1),
  minutes: z.number().int().positive(),
});
export type Lecon = z.infer<typeof SchemaLecon>;

export const SchemaRoadmap = z.object({
  id: z.string().min(1),
  titre: z.string().min(1),
  description: z.string().optional(),
  niveau: SchemaNiveau,
  lecons: z.array(SchemaLecon),
});
export type Roadmap = z.infer<typeof SchemaRoadmap>;

export const SchemaNote = z.object({
  idLecon: z.string(),
  texte: z.string().trim().min(1, 'La note ne peut pas être vide').max(500),
});
export type Note = z.infer<typeof SchemaNote>;
```

Place au moins quatre roadmaps dans `public/data/roadmaps.json`, avec 3 à 5 leçons chacune. Écris ensuite des tests qui vérifient qu'un JSON valide passe et qu'un JSON invalide (minutes négatives, niveau inconnu) est rejeté avec `safeParse`.

## Étape 3 : les calculs purs

Dans `src/domaine/calculs.ts`, écris des fonctions génériques et typées :

```ts
import type { Niveau, Roadmap } from './schemas';

export const dureeTotale = (r: Roadmap): number =>
  r.lecons.reduce((somme, l) => somme + l.minutes, 0);

export function pourcentage(r: Roadmap, terminees: ReadonlySet<string>): number {
  if (r.lecons.length === 0) return 0;
  const faites = r.lecons.filter((l) => terminees.has(l.id)).length;
  return Math.round((faites / r.lecons.length) * 100);
}

export function grouperPar<T, K extends string>(
  liste: readonly T[],
  cle: (element: T) => K
): Partial<Record<K, T[]>> {
  const resultat: Partial<Record<K, T[]>> = {};
  for (const element of liste) {
    const groupe = cle(element);
    (resultat[groupe] ??= []).push(element);
  }
  return resultat;
}

export interface Filtre {
  niveau: Niveau | 'tous';
  recherche: string;
  tri: 'titre' | 'duree';
}

export function appliquerFiltre(roadmaps: readonly Roadmap[], filtre: Filtre): Roadmap[] {
  const terme = filtre.recherche.trim().toLowerCase();
  const filtrees = roadmaps.filter(
    (r) =>
      (filtre.niveau === 'tous' || r.niveau === filtre.niveau) &&
      (terme === '' || r.titre.toLowerCase().includes(terme))
  );
  return filtre.tri === 'duree'
    ? filtrees.sort((a, b) => dureeTotale(b) - dureeTotale(a))
    : filtrees.sort((a, b) => a.titre.localeCompare(b.titre, 'fr'));
}
```

Ajoute `statistiquesParNiveau`, `tempsEtudie` et `formaterDuree`. Écris au moins 8 tests sur ce module, dont les cas limites : liste vide, aucun résultat, tri sans mutation de l'entrée.

:::quiz
Pourquoi déduire les types TypeScript des schémas Zod avec `z.infer` ?
- [ ] Parce que Zod est plus rapide que TypeScript
- [x] Pour avoir une seule définition qui sert à la fois à la validation à l'exécution et au typage
- [ ] Parce que les interfaces sont interdites avec React
- [ ] Parce que Zod génère du CSS
> Avec `z.infer`, le schéma est la source de vérité unique : on évite que le type et la validation divergent avec le temps.
:::

## Étape 4 : la couche d'API générique

Dans `src/api/client.ts`, un client unique qui valide systématiquement :

```ts
import type { ZodType } from 'zod';

export class ErreurApi extends Error {
  constructor(
    message: string,
    readonly statut?: number
  ) {
    super(message);
    this.name = 'ErreurApi';
  }
}

export async function getValide<T>(url: string, schema: ZodType<T>): Promise<T> {
  let reponse: Response;
  try {
    reponse = await fetch(url);
  } catch {
    throw new ErreurApi('Connexion impossible. Vérifie ton réseau.');
  }
  if (!reponse.ok) {
    throw new ErreurApi(`Erreur serveur (${reponse.status})`, reponse.status);
  }
  const brut: unknown = await reponse.json();
  const resultat = schema.safeParse(brut);
  if (!resultat.success) {
    throw new ErreurApi('Données reçues invalides.');
  }
  return resultat.data;
}
```

Dans `src/api/roadmaps.ts` : `export const chargerRoadmaps = () => getValide('/data/roadmaps.json', z.array(SchemaRoadmap));`. Le type retourné est déduit : `Promise<Roadmap[]>`, sans aucune assertion.

## Étape 5 : un hook de ressource

Un hook générique qui expose l'état sous forme d'union discriminée :

```ts
import { useCallback, useEffect, useState } from 'react';

export type Ressource<T> =
  | { statut: 'chargement' }
  | { statut: 'succes'; donnees: T }
  | { statut: 'erreur'; message: string };

export function useRessource<T>(charger: () => Promise<T>) {
  const [etat, setEtat] = useState<Ressource<T>>({ statut: 'chargement' });

  const executer = useCallback(() => {
    setEtat({ statut: 'chargement' });
    charger()
      .then((donnees) => setEtat({ statut: 'succes', donnees }))
      .catch((e: unknown) =>
        setEtat({ statut: 'erreur', message: e instanceof Error ? e.message : 'Erreur inconnue' })
      );
  }, [charger]);

  useEffect(() => {
    executer();
  }, [executer]);

  return { etat, recharger: executer };
}
```

Passe une fonction **stable** (déclarée hors du composant ou mémorisée) comme `charger`, sinon l'effet se redéclenche à chaque rendu. Crée un composant `Ressource` ou un `switch` exhaustif qui affiche chargement, erreur avec « Réessayer » et contenu.

## Étape 6 : la progression avec reducer et contexte

Modélise les actions par une union discriminée et couvre-les toutes :

```ts
export interface EtatProgression {
  terminees: string[];
  notes: Record<string, string>;
}

export type ActionProgression =
  | { type: 'basculer'; idLecon: string }
  | { type: 'noter'; idLecon: string; texte: string }
  | { type: 'supprimer-note'; idLecon: string }
  | { type: 'reinitialiser' };

export function reducerProgression(etat: EtatProgression, action: ActionProgression): EtatProgression {
  switch (action.type) {
    case 'basculer':
      return {
        ...etat,
        terminees: etat.terminees.includes(action.idLecon)
          ? etat.terminees.filter((id) => id !== action.idLecon)
          : [...etat.terminees, action.idLecon],
      };
    case 'noter':
      return { ...etat, notes: { ...etat.notes, [action.idLecon]: action.texte } };
    case 'supprimer-note': {
      const { [action.idLecon]: _supprimee, ...reste } = etat.notes;
      return { ...etat, notes: reste };
    }
    case 'reinitialiser':
      return { terminees: [], notes: {} };
    default: {
      const inattendu: never = action;
      return inattendu;
    }
  }
}
```

Expose le reducer par un `ProgressionContext` et un hook `useProgression()` qui lève une erreur hors fournisseur. Persiste l'état avec `useLocalStorage<EtatProgression>` **et valide** la valeur relue avec un schéma Zod pour qu'un stockage corrompu ne casse pas l'application. Écris au moins 5 tests sur le reducer (chaque action, bascule deux fois, note inexistante).

:::quiz
À quoi sert l'affectation `const inattendu: never = action;` dans le `default` du reducer ?
- [ ] À ignorer silencieusement une action inconnue
- [x] À provoquer une erreur de compilation si une action de l'union n'est pas traitée
- [ ] À convertir l'action en chaîne
- [ ] À accélérer le reducer
> Si tous les cas sont couverts, `action` vaut `never` dans le `default`. Dès qu'un nouveau type d'action est ajouté sans traitement, l'affectation échoue à la compilation.
:::

## Étape 7 : authentification simulée et routes

Crée `AuthContext` avec une union : `{ statut: 'anonyme' } | { statut: 'connecte'; utilisateur: Utilisateur }`. Le formulaire valide l'e-mail et le nom avec un schéma Zod et affiche les messages d'erreur. Écris un composant `RouteProtegee` :

```tsx
import { Navigate, Outlet } from 'react-router-dom';
import { useAuth } from '@/contextes/AuthContext';

export function RouteProtegee() {
  const { session } = useAuth();
  return session.statut === 'connecte' ? <Outlet /> : <Navigate to="/connexion" replace />;
}
```

Déclare les routes : `/connexion`, puis sous `RouteProtegee` : `/` (catalogue), `/roadmaps/:id` (détail) et `/statistiques`. Le paramètre d'URL est une `string | undefined` : gère proprement le cas d'une roadmap introuvable.

## Étape 8 : composants et finitions

Construis des composants petits et typés : `CarteRoadmap`, `ListeLecons`, `BarreProgression` (avec `role="progressbar"` et `aria-valuenow`), `FiltreRoadmaps`, `EditeurNote`, `MessageErreur`. Chaque composant a sa propre interface de props, aucune prop `any`, et des callbacks typés (`onBasculer: (idLecon: string) => void`).

Termine par :

1. `npm run typecheck`, `npm run lint`, `npm test` : tout doit passer, avec au moins **15 tests**.
2. Test d'un composant avec Testing Library : `BarreProgression` affiche la bonne valeur et le bon `aria-valuenow`.
3. Navigation clavier complète, focus visible, labels sur tous les champs.
4. `npm run build` sans erreur, puis aperçu avec `npm run preview`.
5. Un `README.md` : présentation, captures, commandes, architecture, choix techniques.
6. Un workflow GitHub Actions qui exécute `npm run verifier`.

## Livrables

- Dépôt Git avec au moins 10 commits au message clair (`feat:`, `fix:`, `test:`, `refactor:`, `docs:`).
- Application fonctionnelle respectant le cahier des charges.
- `README.md` complet et workflow de CI.
- Zéro erreur `tsc`, zéro erreur ESLint, au moins 15 tests verts.
- Optionnel : déploiement gratuit sur Vercel ou Netlify.

## Checklist de validation

- [ ] `tsconfig` en `strict` avec `noUncheckedIndexedAccess` et aucune erreur `tsc --noEmit`.
- [ ] Aucun `any` ni `@ts-ignore` dans le code ; chaque `as` est justifié.
- [ ] Les types du domaine sont déduits des schémas Zod.
- [ ] Les données de l'API et du stockage local sont validées à l'exécution.
- [ ] Les états de chargement utilisent une union discriminée et un traitement exhaustif.
- [ ] Le reducer traite chaque action avec une vérification `never`.
- [ ] La connexion est validée et les routes protégées redirigent un visiteur anonyme.
- [ ] Le catalogue, le détail, les filtres, le tri et les statistiques fonctionnent ensemble.
- [ ] La progression et les notes survivent au rechargement, et un stockage corrompu ne plante pas l'application.
- [ ] Les erreurs réseau et les données invalides affichent un message clair avec « Réessayer ».
- [ ] Au moins 15 tests passent (calculs, reducer, schémas, composant).
- [ ] `npm run verifier` réussit en local et en CI.
- [ ] Les composants sont accessibles au clavier et les messages d'erreur ont `role="alert"`.
- [ ] Le README permet à quelqu'un d'installer et de lancer le projet en moins de cinq minutes.

## Pistes d'amélioration

- Remplacer l'API simulée par un vrai back-end Laravel et passer à l'authentification par jeton.
- Ajouter React Query pour le cache et la synchronisation des requêtes.
- Générer les types depuis une spécification OpenAPI.
- Ajouter des tests de bout en bout avec Playwright.
- Gérer le mode sombre et la préférence de mouvement réduit.
- Migrer le projet vers Next.js avec composants serveur typés.

## Atelier guidé : plan de travail suggéré

1. Séance 1 (45 min) : étape 1, projet strict, lint, tests et CI en place, premier commit.
2. Séance 2 (60 min) : étapes 2 et 3, schémas, calculs purs et leurs tests.
3. Séance 3 (60 min) : étapes 4 et 5, client d'API et hook `useRessource`.
4. Séance 4 (75 min) : étape 6, reducer, contexte, persistance validée, tests.
5. Séance 5 (75 min) : étape 7 et composants du catalogue et du détail.
6. Séance 6 (45 min) : notes, statistiques, étape 8 de finitions, README, déploiement.

Auto-évaluation finale : lance `grep -rn "any" src` et vérifie qu'aucune occurrence n'est un type. Puis ajoute un nouveau type d'action au reducer sans le traiter : le compilateur doit refuser. Si les deux réponses sont conformes, ton application tire vraiment parti de TypeScript.

## Erreurs fréquentes

- **Définir les types à la main et les schémas séparément.** Ils divergent avec le temps ; déduis les uns des autres.
- **Valider seulement l'API et pas le `localStorage`.** Les données du navigateur sont tout aussi externes.
- **Oublier de rendre la fonction `charger` stable.** L'effet se relance à chaque rendu et boucle.
- **Utiliser `as` sur le résultat de `JSON.parse`.** Valide avec le schéma.
- **Modifier l'état du reducer au lieu de retourner une copie.** React ne détecte pas le changement.
- **Oublier le cas « roadmap introuvable » sur la page de détail.** Un paramètre d'URL peut être invalide.
- **Mélanger logique métier et composants.** Place les calculs dans `domaine/` pour pouvoir les tester.
- **Ne pas lancer `tsc` en CI.** Vite ne vérifie pas les types lors du build.

## Bonnes pratiques

- Commence par le domaine (schémas, calculs, tests), puis l'API, puis l'interface.
- Rends les états impossibles impossibles à représenter : unions discriminées partout.
- Valide à la frontière, fais confiance aux types à l'intérieur.
- Commit petit et fréquent : chaque commit laisse `verifier` au vert.
- Nomme les types d'après le métier (`Roadmap`, `Lecon`) et non d'après la technique.
- Garde les composants fins : ils affichent, les hooks et le domaine décident.
- Écris le README pour un lecteur pressé : ce qu'est le projet, comment le lancer, pourquoi ces choix.

## À retenir

- Un projet TypeScript professionnel combine configuration stricte, validation à l'exécution et vérification continue.
- Zod fait de chaque schéma la source de vérité du type et de la validation.
- Les unions discriminées et la vérification exhaustive avec `never` rendent les états et les actions sûrs.
- Les generics donnent des utilitaires réutilisables (`getValide`, `useRessource`, `grouperPar`) sans perdre le typage.
- Les calculs purs dans un module séparé se testent simplement et protègent la logique métier.
- `npm run verifier` en local et en CI garantit que le code livré respecte toujours les types, le lint et les tests.
- Tu possèdes maintenant la base pour aborder Next.js, React Query et les architectures full-stack typées.
