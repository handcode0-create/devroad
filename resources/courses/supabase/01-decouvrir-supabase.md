---
title: Découvrir Supabase
minutes: 120
level: beginner
---

## Ce que tu vas apprendre

Construire le back-end d'une application, c'est beaucoup de travail : une base de données, un système de comptes, une API, du stockage de fichiers, de la sécurité. **Supabase** te donne tout cela d'un seul coup, au-dessus d'une vraie base **PostgreSQL**. Ce chapitre te montre ce qu'est Supabase, comment créer un projet et comment lire et écrire des données depuis JavaScript.

À la fin du chapitre, tu seras capable de :

- expliquer ce que Supabase fournit et en quoi il se distingue d'une base de données seule ;
- créer un projet et repérer ses clés, son URL et son tableau de bord ;
- distinguer la clé **anon** (publique) de la clé **service_role** (secrète) ;
- installer `@supabase/supabase-js` (version 2) et créer un client ;
- lire, insérer, modifier et supprimer des lignes avec l'API JavaScript ;
- gérer correctement les erreurs renvoyées par Supabase.

Prérequis : bases de JavaScript (fonctions, `async`/`await`, objets) et une idée de ce qu'est une table. Avoir lu le début du cours React ou Next.js aide, mais n'est pas obligatoire. Prévois deux heures. Il te faut un compte gratuit sur supabase.com : la formule gratuite suffit largement pour tout le cours.

## Qu'est-ce que Supabase ?

Supabase se présente comme une alternative open source à Firebase, mais avec une différence majeure : au cœur du produit, il y a **PostgreSQL**, une base relationnelle solide, avec du SQL, des jointures et des contraintes. Tu ne stockes pas des documents sans structure : tu modélises des tables.

Autour de cette base, Supabase assemble des services prêts à l'emploi :

| Service | Rôle |
| --- | --- |
| **Database** | Une base PostgreSQL complète, accessible en SQL |
| **Auth** | Inscription, connexion, sessions, connexion avec Google |
| **API automatique** | Chaque table devient une API REST, sans écrire de back-end |
| **Storage** | Stockage de fichiers (photos, PDF) avec règles d'accès |
| **Realtime** | Réception des changements de la base en direct |
| **Edge Functions** | Fonctions serveur en TypeScript pour la logique sensible |

Concrètement, pour une application comme DevRoad, tu peux stocker les roadmaps et les étapes, gérer les comptes des apprenants, enregistrer leur progression et envoyer des avatars, sans écrire un seul serveur. Pour un entrepreneur d'Abidjan qui veut lancer un MVP vite et à moindre coût, c'est un gain de temps considérable.

> **À retenir** : Supabase = PostgreSQL + Auth + API + Storage + Realtime + Functions, reliés entre eux. Tu gardes la puissance de SQL et tu évites de construire l'infrastructure.

:::quiz
Quel élément est au cœur de Supabase ?
- [ ] Une base de documents sans schéma
- [x] Une base PostgreSQL classique avec du SQL
- [ ] Un serveur Node.js que tu dois héberger toi-même
- [ ] Un système de fichiers uniquement
> Supabase repose sur PostgreSQL. Les autres services (Auth, Storage, Realtime) s'appuient dessus.
:::

## Comment ça marche : l'API automatique

Quand tu crées une table, Supabase génère automatiquement une API pour elle, grâce à un composant appelé **PostgREST**. Tu n'écris pas de routes `GET /roadmaps` : elles existent déjà. Le navigateur appelle directement cette API, soit en HTTP brut, soit, plus simplement, via la bibliothèque `supabase-js`.

Voici le schéma mental :

```text
Navigateur / App Next.js
        |
        |  supabase-js (HTTPS)
        v
   API Supabase (PostgREST)
        |
        v
   Base PostgreSQL  <-- règles d'accès (RLS)
```

Une question se pose tout de suite : si le navigateur parle directement à la base, qu'est-ce qui empêche n'importe qui de tout lire ? La réponse s'appelle **Row Level Security** (RLS), que tu étudieras au chapitre 4. Retiens pour l'instant qu'une table accessible par l'API doit toujours être protégée par des règles. Sans règle, personne ne peut rien lire quand la RLS est activée ; sans RLS, tout le monde peut tout lire. Nous y reviendrons en détail.

## Créer ton premier projet

1. Connecte-toi sur supabase.com et clique sur « New project ».
2. Choisis un nom (par exemple `devroad-cours`) et une organisation.
3. Génère un **mot de passe de base de données** solide et conserve-le dans un gestionnaire de mots de passe.
4. Choisis la **région**. Pour des utilisateurs en Côte d'Ivoire, prends la région européenne la plus proche (Paris ou Frankfurt) afin de réduire la latence.
5. Attends une minute que le projet s'initialise.

Une fois le projet prêt, tu arrives sur le tableau de bord. Les rubriques à connaître :

- **Table Editor** : un tableur pour voir et modifier les données ;
- **SQL Editor** : un éditeur pour exécuter du SQL ;
- **Authentication** : la liste des utilisateurs et les fournisseurs de connexion ;
- **Storage** : les « buckets » de fichiers ;
- **Project Settings > API** : l'URL du projet et les clés.

### L'URL et les deux clés

Dans les réglages d'API, tu trouves :

- **Project URL** : l'adresse de ton projet, de la forme `https://abcdefgh.supabase.co` ;
- une clé **anon** (ou « publishable ») : **publique**, elle peut figurer dans le code du navigateur ;
- une clé **service_role** (ou « secret ») : **secrète**, elle contourne toutes les règles de sécurité.

> **Attention** : la clé `service_role` donne un accès total à ta base. Ne la mets jamais dans du code envoyé au navigateur, jamais dans un dépôt Git public, jamais dans une variable qui commence par `NEXT_PUBLIC_`. Utilise-la uniquement côté serveur.

La clé anon n'est pas dangereuse en soi, à une condition : que tes tables soient protégées par la RLS. C'est la RLS, et non le secret de la clé, qui protège tes données.

:::quiz
Quelle clé peut être utilisée dans le code JavaScript exécuté dans le navigateur ?
- [ ] service_role, car elle est plus puissante
- [x] anon, à condition que les tables soient protégées par la RLS
- [ ] Le mot de passe de la base de données
- [ ] Aucune clé, il faut passer par un serveur
> La clé anon est faite pour être publique. La sécurité repose sur la RLS. La clé service_role contourne la RLS et doit rester côté serveur.
:::

## Créer une première table

Dans le SQL Editor, crée une table de roadmaps. Tu verras le SQL en détail au chapitre 2 ; recopie simplement :

```sql
create table roadmaps (
  id bigint generated always as identity primary key,
  title text not null,
  description text,
  level text not null default 'beginner',
  created_at timestamptz not null default now()
);

insert into roadmaps (title, description, level) values
  ('Laravel de zéro', 'Les bases du framework PHP', 'beginner'),
  ('React moderne', 'Composants, état et hooks', 'beginner'),
  ('Supabase', 'Un back-end complet sans serveur', 'intermediate');
```

Ouvre le Table Editor : tes trois lignes sont là. Pour permettre la lecture publique, active la RLS et ajoute une règle de lecture (tu comprendras chaque mot au chapitre 4) :

```sql
alter table roadmaps enable row level security;

create policy "Lecture publique des roadmaps"
  on roadmaps for select
  using (true);
```

## Installer supabase-js et créer le client

Dans un projet Next.js (ou n'importe quel projet JavaScript), installe la bibliothèque :

```bash
npm install @supabase/supabase-js
```

Place l'URL et la clé anon dans un fichier `.env.local` :

```text
NEXT_PUBLIC_SUPABASE_URL=https://abcdefgh.supabase.co
NEXT_PUBLIC_SUPABASE_ANON_KEY=ta-cle-anon
```

Puis crée le client dans un fichier réutilisable, par exemple `lib/supabase.js` :

```js
import { createClient } from '@supabase/supabase-js';

export const supabase = createClient(
  process.env.NEXT_PUBLIC_SUPABASE_URL,
  process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
);
```

Cet objet `supabase` est le point d'entrée pour toutes les opérations. Tu le crées **une fois** et tu l'importes partout.

## Lire des données : select

La méthode `from` choisit la table, `select` indique les colonnes. Toutes les opérations sont asynchrones et renvoient un objet `{ data, error }` :

```js
const { data, error } = await supabase
  .from('roadmaps')
  .select('id, title, level');

if (error) {
  console.error('Lecture impossible :', error.message);
} else {
  console.log(data);
}
```

`data` est un tableau d'objets : `[{ id: 1, title: 'Laravel de zéro', level: 'beginner' }, …]`. Pour tout récupérer, tu peux appeler `select()` sans argument, mais choisis plutôt les colonnes dont tu as besoin : moins de données circulent, ce qui compte quand tes utilisateurs ont un forfait mobile limité.

### Filtrer, trier, limiter

Les méthodes se chaînent comme des phrases :

```js
const { data } = await supabase
  .from('roadmaps')
  .select('id, title')
  .eq('level', 'beginner')          // level = 'beginner'
  .order('created_at', { ascending: false })
  .limit(10);
```

Voici les filtres les plus courants :

| Méthode | Équivalent SQL |
| --- | --- |
| `.eq('level', 'beginner')` | `level = 'beginner'` |
| `.neq('level', 'beginner')` | `level <> 'beginner'` |
| `.gt('price', 5000)` | `price > 5000` |
| `.in('level', ['beginner', 'intermediate'])` | `level in (...)` |
| `.ilike('title', '%react%')` | recherche insensible à la casse |
| `.is('description', null)` | `description is null` |

Pour récupérer **une seule** ligne, ajoute `.single()` : tu obtiens un objet au lieu d'un tableau, et une erreur s'il n'y a pas exactement une ligne.

```js
const { data: roadmap } = await supabase
  .from('roadmaps')
  .select('*')
  .eq('id', 1)
  .single();
```

## Écrire des données : insert, update, delete

```js
// Créer
const { data, error } = await supabase
  .from('roadmaps')
  .insert({ title: 'Docker pour débutants', level: 'beginner' })
  .select()          // demande de renvoyer la ligne créée
  .single();

// Modifier
await supabase
  .from('roadmaps')
  .update({ level: 'intermediate' })
  .eq('id', 3);

// Supprimer
await supabase
  .from('roadmaps')
  .delete()
  .eq('id', 3);
```

> **Erreur fréquente** : oublier `.eq(...)` sur un `update` ou un `delete`. Sans filtre, la commande vise toutes les lignes que la RLS te laisse atteindre. Vérifie toujours la condition.

Remarque le `.select()` après `insert` : par défaut, une insertion ne renvoie rien. Ajoute `.select()` pour récupérer la ligne créée, avec son `id` et sa date.

:::quiz
Que se passe-t-il si tu écris `supabase.from('roadmaps').delete()` sans aucun filtre ?
- [ ] Rien, Supabase refuse toujours
- [ ] Seule la première ligne est supprimée
- [x] La commande vise toutes les lignes accessibles, ce qui est dangereux
- [ ] La table elle-même est supprimée
> Sans filtre, un delete s'applique à toutes les lignes que les règles RLS autorisent. Les clients récents bloquent parfois ce cas, mais ne compte jamais là-dessus : ajoute toujours un filtre.
:::

## Gérer les erreurs

Une requête Supabase **ne lève pas d'exception** quand elle échoue : elle renvoie `error`. Si tu l'ignores, ton application continue avec `data` valant `null`, et le bug apparaît ailleurs, loin de sa cause.

```js
async function chargerRoadmaps() {
  const { data, error } = await supabase
    .from('roadmaps')
    .select('id, title');

  if (error) {
    throw new Error(`Chargement des roadmaps : ${error.message}`);
  }
  return data;
}
```

Une erreur fréquente chez les débutants : la requête renvoie un tableau vide alors que la table contient des lignes. Dans la quasi-totalité des cas, c'est la **RLS** : elle est activée et aucune règle ne t'autorise à lire. Il n'y a alors pas d'erreur, simplement zéro ligne.

## Utiliser Supabase dans un composant React

Voici un composant client qui affiche les roadmaps :

```jsx
'use client';

import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';

export default function ListeRoadmaps() {
  const [roadmaps, setRoadmaps] = useState([]);
  const [erreur, setErreur] = useState(null);
  const [chargement, setChargement] = useState(true);

  useEffect(() => {
    async function charger() {
      const { data, error } = await supabase
        .from('roadmaps')
        .select('id, title, level')
        .order('title');

      if (error) setErreur(error.message);
      else setRoadmaps(data);
      setChargement(false);
    }
    charger();
  }, []);

  if (chargement) return <p>Chargement…</p>;
  if (erreur) return <p role="alert">Erreur : {erreur}</p>;

  return (
    <ul>
      {roadmaps.map((r) => (
        <li key={r.id}>{r.title} ({r.level})</li>
      ))}
    </ul>
  );
}
```

Trois états sont gérés : chargement, erreur, succès. Toute requête réseau les a, prends l'habitude de les afficher tous les trois.

:::quiz
Une requête select renvoie un tableau vide alors que la table contient des données. Quelle est la cause la plus probable ?
- [ ] La clé anon est expirée
- [x] La RLS est activée sans règle de lecture adaptée
- [ ] supabase-js ne supporte pas les tableaux
- [ ] La table est corrompue
> Quand la RLS est activée et qu'aucune règle ne permet la lecture, Supabase ne renvoie aucune erreur : il renvoie simplement zéro ligne.
:::

## Atelier guidé : ta première roadmap en ligne

Prévois une heure.

1. Crée un projet Supabase nommé `devroad-cours` et note l'URL et la clé anon.
2. Dans le SQL Editor, crée la table `roadmaps` et insère trois lignes avec le SQL de ce chapitre.
3. Active la RLS et ajoute la règle de lecture publique.
4. Crée un projet Next.js (`npx create-next-app@latest`) et installe `@supabase/supabase-js`.
5. Ajoute `.env.local` avec l'URL et la clé anon, puis crée `lib/supabase.js`.
6. Affiche la liste des roadmaps avec le composant `ListeRoadmaps`.
7. Ajoute un filtre sur le niveau avec `.eq('level', ...)` choisi dans un menu déroulant.
8. Teste un `insert` depuis une console de navigateur et observe l'erreur renvoyée : la RLS refuse l'écriture, car tu n'as défini qu'une règle de lecture.

Pour t'auto-évaluer : explique à un ami pourquoi la clé anon peut être publique, et ce qui protège réellement tes données.

## Erreurs fréquentes

- **Mettre la clé `service_role` côté navigateur.** C'est la faille la plus grave : elle donne un accès total.
- **Ignorer `error`.** Tu travailles avec `data` à `null` sans comprendre pourquoi.
- **Oublier `.select()` après un `insert`.** Tu ne récupères pas la ligne créée.
- **Un `update` ou `delete` sans `.eq()`.** Tu touches plus de lignes que prévu.
- **Penser que la RLS n'existe pas.** Un tableau vide sans erreur vient presque toujours d'elle.
- **Créer un nouveau client à chaque rendu de composant.** Crée-le une fois dans un module et importe-le.

## Bonnes pratiques

- Garde l'URL et les clés dans des variables d'environnement, jamais en dur dans le code.
- Active la RLS sur **toutes** les tables exposées dès leur création.
- Sélectionne uniquement les colonnes nécessaires plutôt que tout.
- Gère toujours les trois états : chargement, erreur, succès.

## À retenir

- Supabase est une plateforme construite autour de PostgreSQL : base, Auth, API, Storage, Realtime et Functions.
- Chaque table devient automatiquement une API, utilisable avec `supabase-js`.
- La clé **anon** est publique ; la clé **service_role** est secrète et réservée au serveur.
- Les requêtes renvoient `{ data, error }` et ne lèvent pas d'exception : vérifie `error`.
- `select`, `insert`, `update`, `delete` se combinent avec des filtres comme `eq`, `in`, `ilike`, `order`, `limit`.
- Ce sont les règles RLS, pas le secret de la clé anon, qui protègent tes données.
