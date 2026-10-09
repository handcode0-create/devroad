---
title: Données, formulaires et API
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application sans données n'est qu'une vitrine. Dans ce chapitre, tu apprends à lire des données côté serveur, à les mettre en cache intelligemment, à recevoir des formulaires avec les **Server Actions** et à exposer ta propre API avec les **Route Handlers**. C'est ce qui transforme ton projet Next.js en véritable produit.

À la fin du chapitre, tu seras capable de :

- récupérer des données avec `fetch` dans un composant serveur et contrôler leur mise en cache ;
- invalider le cache avec `revalidatePath` et `revalidateTag` ;
- écrire une **Server Action** pour traiter un formulaire sans créer d'API ;
- valider les données reçues avec **Zod** et afficher les erreurs ;
- gérer l'état d'envoi avec `useActionState` et `useFormStatus` ;
- créer des endpoints REST avec les **Route Handlers** (`route.ts`) ;
- choisir entre Server Action et Route Handler.

Prérequis : le chapitre « Server et Client Components » et les bases de HTML (formulaires). Prévois environ deux heures trente.

## Lire des données dans un composant serveur

Tu l'as vu au chapitre précédent : un composant serveur `async` peut appeler `fetch` directement.

```tsx
// src/app/roadmaps/page.tsx
type Roadmap = { id: number; slug: string; titre: string };

export default async function PageRoadmaps() {
  const reponse = await fetch(`${process.env.API_URL}/roadmaps`);
  if (!reponse.ok) {
    throw new Error('Impossible de charger les roadmaps');
  }
  const roadmaps: Roadmap[] = await reponse.json();

  return (
    <ul>
      {roadmaps.map((r) => (
        <li key={r.id}>{r.titre}</li>
      ))}
    </ul>
  );
}
```

L'erreur levée est interceptée par `error.tsx` du segment (chapitre 3). Vérifie donc toujours `reponse.ok` : `fetch` ne rejette la promesse que pour une panne réseau, pas pour un statut 404 ou 500.

Tu peux aussi interroger directement une base de données depuis un composant serveur, avec un ORM comme Prisma ou un client comme Supabase. Le principe est identique : une fonction `async`, un `await`, et le résultat est du HTML.

```tsx
import { prisma } from '@/lib/prisma';

export default async function PageRoadmaps() {
  const roadmaps = await prisma.roadmap.findMany({ orderBy: { titre: 'asc' } });
  return <p>{roadmaps.length} roadmaps</p>;
}
```

## Comprendre le cache de Next.js 15

Le cache est un sujet qui a changé. Dans Next.js 15, **les requêtes `fetch` ne sont plus mises en cache par défaut**. Chaque rendu dynamique refait la requête. Tu choisis explicitement le comportement avec l'option `cache` ou `next.revalidate` :

```tsx
// Toujours récupérer des données fraîches (comportement par défaut en v15)
await fetch(url, { cache: 'no-store' });

// Mettre en cache indéfiniment (jusqu'à invalidation manuelle)
await fetch(url, { cache: 'force-cache' });

// Mettre en cache, puis rafraîchir toutes les heures
await fetch(url, { next: { revalidate: 3600 } });

// Associer une étiquette pour invalider ensuite ciblé
await fetch(url, { next: { tags: ['roadmaps'] } });
```

| Option | Effet | Cas d'usage |
| --- | --- | --- |
| `cache: 'no-store'` | Pas de cache | Tableau de bord, données personnalisées |
| `cache: 'force-cache'` | Cache durable | Contenu quasi immuable |
| `next: { revalidate: N }` | Cache de N secondes | Liste mise à jour toutes les heures |
| `next: { tags: [...] }` | Cache invalidable par étiquette | Données modifiées par une action |

Pour des fonctions qui ne passent pas par `fetch` (un appel Prisma, par exemple), Next.js propose aussi `unstable_cache` ou la directive `'use cache'` selon la version et la configuration. Reporte-toi à la documentation officielle de ta version avant d'y recourir, car ces API évoluent vite.

> **Astuce** : en cas de doute, ne mets rien en cache au départ. Un site correct et un peu lent vaut mieux qu'un site rapide qui affiche des données périmées. Ajoute le cache quand tu as mesuré un besoin.

:::quiz
Dans Next.js 15, que se passe-t-il par défaut pour un fetch dans un composant serveur ?
- [ ] La réponse est mise en cache pour toujours
- [x] La réponse n'est pas mise en cache, sauf si tu l'indiques
- [ ] La requête est exécutée dans le navigateur
- [ ] La requête est refusée sans option cache
> Depuis Next.js 15, les requêtes fetch ne sont plus mises en cache par défaut. Tu actives le cache avec cache: 'force-cache' ou next.revalidate.
:::

## Les formulaires avec les Server Actions

Traditionnellement, un formulaire React demande beaucoup de code : un état par champ, un gestionnaire `onSubmit`, un appel `fetch` vers une API, la gestion des erreurs. Les **Server Actions** simplifient tout cela : une fonction `async` exécutée sur le serveur, appelée directement depuis un formulaire.

Une Server Action est marquée par la directive `'use server'`. Prenons l'ajout d'une fiche mémo à DevRoad :

```ts
// src/app/fiches/actions.ts
'use server';

import { revalidatePath } from 'next/cache';
import { redirect } from 'next/navigation';
import { prisma } from '@/lib/prisma';

export async function creerFiche(formData: FormData) {
  const titre = String(formData.get('titre') ?? '');
  const contenu = String(formData.get('contenu') ?? '');

  await prisma.fiche.create({ data: { titre, contenu } });

  revalidatePath('/fiches');
  redirect('/fiches');
}
```

Et le formulaire, qui peut rester un **composant serveur** :

```tsx
// src/app/fiches/nouvelle/page.tsx
import { creerFiche } from '../actions';

export default function NouvelleFiche() {
  return (
    <form action={creerFiche} className="mx-auto max-w-lg space-y-4 p-8">
      <label className="block">
        Titre
        <input name="titre" required className="mt-1 w-full rounded border p-2" />
      </label>
      <label className="block">
        Contenu
        <textarea name="contenu" required rows={5} className="mt-1 w-full rounded border p-2" />
      </label>
      <button type="submit" className="rounded bg-blue-600 px-4 py-2 text-white">
        Enregistrer
      </button>
    </form>
  );
}
```

Observe ce qui manque : aucun `useState`, aucun `onSubmit`, aucun `fetch`. La propriété `action` reçoit directement la fonction. Les champs sont lus par leur attribut `name` dans l'objet `FormData`. Le formulaire fonctionne même si le JavaScript n'est pas encore chargé : c'est l'**amélioration progressive**.

> **Attention** : une Server Action est un point d'entrée **public** de ton serveur, comme n'importe quelle route d'API. Quelqu'un peut l'appeler avec des données forgées. Valide toujours les entrées et vérifie toujours l'autorisation de l'utilisateur à l'intérieur de l'action.

### Valider avec Zod

N'utilise jamais les données du formulaire telles quelles. La bibliothèque **Zod** décrit la forme attendue et vérifie les valeurs.

```bash
npm install zod
```

```ts
// src/app/fiches/actions.ts
'use server';

import { z } from 'zod';
import { revalidatePath } from 'next/cache';
import { prisma } from '@/lib/prisma';

const schemaFiche = z.object({
  titre: z.string().trim().min(3, 'Le titre doit contenir au moins 3 caractères'),
  contenu: z.string().trim().min(10, 'Le contenu doit contenir au moins 10 caractères'),
});

export type EtatFormulaire = {
  erreurs?: { titre?: string[]; contenu?: string[] };
  message?: string;
};

export async function creerFiche(
  _etatPrecedent: EtatFormulaire,
  formData: FormData,
): Promise<EtatFormulaire> {
  const resultat = schemaFiche.safeParse({
    titre: formData.get('titre'),
    contenu: formData.get('contenu'),
  });

  if (!resultat.success) {
    return { erreurs: resultat.error.flatten().fieldErrors };
  }

  await prisma.fiche.create({ data: resultat.data });
  revalidatePath('/fiches');
  return { message: 'Fiche enregistrée !' };
}
```

`safeParse` ne lève pas d'exception : il retourne `success: true` avec les données nettoyées, ou `success: false` avec les erreurs. La méthode `flatten().fieldErrors` les organise par champ.

### Afficher les erreurs avec useActionState

Pour afficher ces erreurs, le formulaire doit devenir un composant client qui utilise le hook `useActionState` de React 19 :

```tsx
// src/components/FormulaireFiche.tsx
'use client';

import { useActionState } from 'react';
import { useFormStatus } from 'react-dom';
import { creerFiche, type EtatFormulaire } from '@/app/fiches/actions';

const etatInitial: EtatFormulaire = {};

function BoutonEnvoi() {
  const { pending } = useFormStatus();
  return (
    <button
      type="submit"
      disabled={pending}
      className="rounded bg-blue-600 px-4 py-2 text-white disabled:opacity-50"
    >
      {pending ? 'Envoi…' : 'Enregistrer'}
    </button>
  );
}

export default function FormulaireFiche() {
  const [etat, action] = useActionState(creerFiche, etatInitial);

  return (
    <form action={action} className="space-y-4">
      <div>
        <input name="titre" placeholder="Titre" className="w-full rounded border p-2" />
        {etat.erreurs?.titre && (
          <p role="alert" className="text-sm text-red-600">{etat.erreurs.titre[0]}</p>
        )}
      </div>
      <div>
        <textarea name="contenu" placeholder="Contenu" rows={5} className="w-full rounded border p-2" />
        {etat.erreurs?.contenu && (
          <p role="alert" className="text-sm text-red-600">{etat.erreurs.contenu[0]}</p>
        )}
      </div>
      <BoutonEnvoi />
      {etat.message && <p className="text-green-700">{etat.message}</p>}
    </form>
  );
}
```

Deux hooks, deux rôles. `useActionState` renvoie l'état retourné par l'action et une version de l'action prête à placer dans `action={...}`. `useFormStatus` doit être utilisé dans un composant **enfant** du formulaire (ici `BoutonEnvoi`) pour savoir si l'envoi est en cours et désactiver le bouton, ce qui évite les doubles envois.

:::quiz
Pourquoi faut-il valider les données dans une Server Action même si le formulaire a des champs required ?
- [ ] Parce que required ne fonctionne pas dans Next.js
- [ ] Parce que Zod est obligatoire pour que l'action s'exécute
- [x] Parce que l'action est un point d'entrée public qui peut être appelé avec des données forgées
- [ ] Parce que le serveur ne reçoit jamais de FormData
> Les contrôles HTML ne protègent que le confort de l'utilisateur. Un attaquant peut appeler directement l'action : la validation côté serveur est la seule vraie protection.
:::

## Les Route Handlers : créer une API

Les Server Actions couvrent les formulaires et mutations internes. Mais parfois, tu as besoin d'une vraie **API HTTP** : pour une application mobile Flutter, un webhook de paiement (Wave, Orange Money, CinetPay), ou un service externe. C'est le rôle des **Route Handlers**, définis dans un fichier `route.ts`.

```ts
// src/app/api/roadmaps/route.ts
import { NextResponse } from 'next/server';
import { z } from 'zod';
import { prisma } from '@/lib/prisma';

export async function GET(requete: Request) {
  const { searchParams } = new URL(requete.url);
  const niveau = searchParams.get('niveau');

  const roadmaps = await prisma.roadmap.findMany({
    where: niveau ? { niveau } : undefined,
  });

  return NextResponse.json(roadmaps);
}

const schema = z.object({
  titre: z.string().min(3),
  niveau: z.enum(['debutant', 'intermediaire', 'professionnel']),
});

export async function POST(requete: Request) {
  const corps = await requete.json().catch(() => null);
  const resultat = schema.safeParse(corps);

  if (!resultat.success) {
    return NextResponse.json(
      { erreurs: resultat.error.flatten().fieldErrors },
      { status: 422 },
    );
  }

  const roadmap = await prisma.roadmap.create({ data: resultat.data });
  return NextResponse.json(roadmap, { status: 201 });
}
```

Chaque fonction porte le nom de la méthode HTTP qu'elle traite : `GET`, `POST`, `PUT`, `PATCH`, `DELETE`. Pour une route dynamique, crée `src/app/api/roadmaps/[id]/route.ts` ; le second argument contient `params`, asynchrone comme pour les pages :

```ts
// src/app/api/roadmaps/[id]/route.ts
import { NextResponse } from 'next/server';
import { prisma } from '@/lib/prisma';

export async function GET(
  _requete: Request,
  { params }: { params: Promise<{ id: string }> },
) {
  const { id } = await params;
  const roadmap = await prisma.roadmap.findUnique({ where: { id: Number(id) } });

  if (!roadmap) {
    return NextResponse.json({ message: 'Introuvable' }, { status: 404 });
  }
  return NextResponse.json(roadmap);
}
```

Respecte les conventions HTTP : `200` pour un succès, `201` après une création, `400` ou `422` pour des données invalides, `401` sans authentification, `404` pour une ressource absente.

> **Exemple** : un webhook CinetPay confirmant un paiement est typiquement un `POST /api/paiements/webhook`. C'est un Route Handler, car c'est un service externe, et non un formulaire de ton site, qui l'appelle.

### Server Action ou Route Handler ?

| Critère | Server Action | Route Handler |
| --- | --- | --- |
| Appelant | Ton propre front Next.js | N'importe quel client (mobile, tiers) |
| Usage typique | Formulaire, bouton de mutation | API publique, webhook, export |
| Format | Fonction appelée directement | Requête / réponse HTTP |
| Typage de bout en bout | Facile | À gérer à la main |

Règle simple : si seul ton interface Next.js l'appelle, Server Action. Si quelqu'un d'autre l'appelle, Route Handler.

## Mettre à jour le cache après une modification

Après avoir créé une fiche, la liste affichée doit se rafraîchir. Deux outils :

```ts
import { revalidatePath, revalidateTag } from 'next/cache';

revalidatePath('/fiches');      // invalide les données de cette page
revalidateTag('fiches');        // invalide tout fetch étiqueté 'fiches'
```

Appelle l'un d'eux dans la Server Action ou le Route Handler qui modifie les données. Sans cela, l'utilisateur pourrait voir l'ancienne liste jusqu'à l'expiration du cache.

## Atelier guidé : les fiches mémo de DevRoad Web

Compte deux heures. Pour rester simple, tu peux remplacer Prisma par un tableau en mémoire dans `src/lib/fiches.ts` (les données seront perdues au redémarrage, ce n'est pas grave pour l'exercice).

1. Crée `src/lib/fiches.ts` avec un tableau de fiches et des fonctions `listerFiches()` et `ajouterFiche()`.
2. Crée la page `/fiches` (composant serveur `async`) qui affiche la liste. Ajoute `export const dynamic = 'force-dynamic'` pour être sûr de voir les changements.
3. Installe Zod et écris le schéma `schemaFiche` (titre de 3 caractères minimum, contenu de 10 minimum).
4. Écris la Server Action `creerFiche(etat, formData)` qui valide, ajoute la fiche, appelle `revalidatePath('/fiches')` et retourne un message de succès.
5. Crée le composant client `FormulaireFiche` avec `useActionState`, l'affichage des erreurs et un bouton désactivé via `useFormStatus`.
6. Teste avec un titre trop court : l'erreur doit s'afficher sans recharger la page.
7. Crée un Route Handler `GET /api/fiches` qui renvoie la liste en JSON, puis `POST /api/fiches` qui valide avec le même schéma et répond `201`.
8. Teste le POST avec `curl` :

```bash
curl -X POST http://localhost:3000/api/fiches \
  -H "Content-Type: application/json" \
  -d '{"titre":"Hooks React","contenu":"useState, useEffect et les autres."}'
```

9. Envoie un corps invalide et vérifie la réponse `422` avec le détail des erreurs.

Pour t'auto-évaluer : explique pourquoi `useFormStatus` doit être appelé dans un composant enfant du `form`, et dans quel cas tu choisirais un Route Handler plutôt qu'une Server Action.

## Erreurs fréquentes

- **Oublier `'use server'` dans le fichier d'actions.** La fonction s'exécuterait côté client ou échouerait.
- **Ne pas valider côté serveur.** Les attributs HTML `required` ou `minlength` sont contournables.
- **Appeler `useFormStatus` dans le composant qui contient le `form`.** Le hook ne fonctionne que dans un enfant du formulaire.
- **Ne pas vérifier `reponse.ok` après un `fetch`.** Une erreur 500 serait traitée comme des données valides.
- **Oublier `revalidatePath` après une mutation.** L'utilisateur ne voit pas son ajout.
- **Appeler `redirect` dans un bloc `try / catch`.** `redirect` fonctionne en levant une exception interne ; place-le après le bloc ou relance l'erreur.
- **Retourner un statut `200` pour une erreur de validation.** Utilise `400` ou `422` dans les Route Handlers.
- **Croire que le cache est actif par défaut dans Next.js 15.** Il ne l'est plus pour `fetch`.

## Bonnes pratiques

- Valide toute entrée avec un schéma Zod partagé entre le formulaire, l'action et l'API quand c'est possible.
- Vérifie l'authentification et les droits dans chaque action et chaque route sensible.
- Isole l'accès aux données dans `src/lib/` : les pages restent lisibles et les requêtes réutilisables.
- Retourne des messages d'erreur clairs, en français pour l'utilisateur, sans détails techniques.
- Désactive le bouton pendant l'envoi pour éviter les doublons de paiement ou de commande.
- Documente tes endpoints : méthode, URL, corps attendu, codes de réponse.
- Place les secrets dans des variables d'environnement non publiques.

## À retenir

- Dans Next.js 15, `fetch` n'est pas mis en cache par défaut ; tu choisis avec `cache` et `next.revalidate`.
- Un composant serveur `async` lit directement ses données ; vérifie toujours `reponse.ok`.
- Une **Server Action** (`'use server'`) traite un formulaire sans API intermédiaire ; elle doit valider et autoriser.
- **Zod** valide les entrées ; `useActionState` affiche les erreurs ; `useFormStatus` gère l'état d'envoi.
- Les **Route Handlers** (`route.ts`) exposent une API HTTP pour des clients externes et des webhooks.
- `revalidatePath` et `revalidateTag` rafraîchissent les données après une modification.
- Server Action pour ton propre front, Route Handler pour tout le reste.
