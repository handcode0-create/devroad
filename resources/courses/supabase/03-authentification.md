---
title: Authentification
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Presque toute application a des comptes : un apprenant qui suit sa progression, un commerçant qui gère son stock, un client qui paie par Mobile Money. Supabase Auth gère l'inscription, la connexion, les sessions et la récupération de mot de passe, sans que tu stockes toi-même un seul mot de passe. Ce chapitre t'apprend à l'utiliser dans une application Next.js.

À la fin du chapitre, tu seras capable de :

- expliquer comment fonctionnent une session et un **JWT** ;
- inscrire et connecter un utilisateur par e-mail et mot de passe ;
- connecter un utilisateur par lien magique ou par code à usage unique ;
- ajouter une connexion avec **Google** ;
- réagir aux changements d'état avec `onAuthStateChange` ;
- créer automatiquement un profil à l'inscription grâce à un **trigger** ;
- utiliser `@supabase/ssr` pour lire la session côté serveur dans Next.js ;
- protéger des pages avec un middleware.

Prérequis : les chapitres 1 et 2, et des notions de composants React. Prévois deux heures trente.

## Comment fonctionne l'authentification

Quand un utilisateur se connecte, Supabase Auth vérifie ses identifiants puis renvoie une **session** composée de deux éléments :

- un **access token**, un JWT (*JSON Web Token*) valable environ une heure ;
- un **refresh token**, utilisé pour obtenir un nouvel access token sans redemander le mot de passe.

Le JWT est une chaîne signée qui contient l'identité de l'utilisateur : son identifiant (`sub`), son e-mail, son rôle. À chaque requête, `supabase-js` l'envoie à l'API. La base sait alors **qui** fait la requête, et c'est cette information qui servira aux règles RLS du chapitre 4 : « chaque utilisateur ne voit que ses propres lignes ».

```text
1. L'utilisateur envoie e-mail + mot de passe
2. Supabase Auth vérifie et renvoie access token + refresh token
3. supabase-js stocke la session et l'envoie avec chaque requête
4. La base lit l'identité dans le JWT : auth.uid()
```

Les comptes sont rangés dans un schéma spécial, `auth`, table `auth.users`. Tu peux les voir dans l'onglet Authentication du tableau de bord, mais tu ne modifies pas cette table directement : tu passes par l'API.

:::quiz
À quoi sert le refresh token ?
- [ ] À stocker le mot de passe de l'utilisateur
- [x] À obtenir un nouvel access token sans que l'utilisateur ressaisisse son mot de passe
- [ ] À chiffrer la base de données
- [ ] À envoyer un e-mail de confirmation
> L'access token expire vite. Le refresh token permet de le renouveler en arrière-plan, ce qui garde l'utilisateur connecté.
:::

## Inscription et connexion par e-mail

Dans Authentication > Providers, le fournisseur **Email** est activé par défaut. Voici l'inscription :

```js
const { data, error } = await supabase.auth.signUp({
  email: 'awa@example.com',
  password: 'un-mot-de-passe-solide',
  options: {
    data: { username: 'awa', display_name: 'Awa Koné' }, // métadonnées
  },
});

if (error) console.error(error.message);
```

Par défaut, Supabase envoie un e-mail de confirmation : tant que l'utilisateur n'a pas cliqué, il ne peut pas se connecter. En développement, tu peux désactiver cette confirmation dans les réglages pour gagner du temps, mais garde-la en production pour éviter les faux comptes.

La connexion se fait avec :

```js
const { data, error } = await supabase.auth.signInWithPassword({
  email: 'awa@example.com',
  password: 'un-mot-de-passe-solide',
});

if (error) {
  // « Invalid login credentials » : mauvais e-mail ou mot de passe
  setMessage('Identifiants incorrects.');
}
```

Et la déconnexion :

```js
await supabase.auth.signOut();
```

### Un formulaire complet

```jsx
'use client';

import { useState } from 'react';
import { supabase } from '@/lib/supabase';

export default function Connexion() {
  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [message, setMessage] = useState('');

  async function seConnecter(e) {
    e.preventDefault();
    const { error } = await supabase.auth.signInWithPassword({ email, password });
    setMessage(error ? 'Identifiants incorrects.' : 'Connecté !');
  }

  return (
    <form onSubmit={seConnecter}>
      <input type="email" value={email} onChange={(e) => setEmail(e.target.value)} required />
      <input type="password" value={password} onChange={(e) => setPassword(e.target.value)} required minLength={8} />
      <button type="submit">Se connecter</button>
      {message && <p role="alert">{message}</p>}
    </form>
  );
}
```

> **Astuce** : affiche un message générique (« identifiants incorrects ») plutôt que de préciser si c'est l'e-mail ou le mot de passe qui est faux. Cela évite de révéler quels comptes existent.

## Lien magique et code à usage unique

Beaucoup d'utilisateurs oublient leurs mots de passe. Une alternative simple est le **lien magique** : tu envoies un e-mail avec un lien de connexion, sans mot de passe du tout.

```js
const { error } = await supabase.auth.signInWithOtp({
  email: 'awa@example.com',
  options: { emailRedirectTo: 'https://devroad.example/dashboard' },
});
```

L'utilisateur clique sur le lien reçu et se retrouve connecté. Selon la configuration du modèle d'e-mail, il peut aussi recevoir un **code à six chiffres** à saisir, que tu vérifies ainsi :

```js
const { data, error } = await supabase.auth.verifyOtp({
  email: 'awa@example.com',
  token: '123456',
  type: 'email',
});
```

## Connexion avec Google

Pour un bouton « Continuer avec Google », active le fournisseur dans Authentication > Providers. Il faut créer des identifiants OAuth dans la console Google Cloud, puis copier le *client ID* et le *client secret* dans Supabase. Côté code, c'est une seule ligne :

```js
await supabase.auth.signInWithOAuth({
  provider: 'google',
  options: { redirectTo: `${window.location.origin}/auth/callback` },
});
```

L'utilisateur est redirigé vers Google, accepte, puis revient sur ton URL de rappel. N'oublie pas d'ajouter cette URL dans la liste des **Redirect URLs** autorisées (Authentication > URL Configuration), sinon la redirection est refusée.

:::quiz
Que faut-il impérativement configurer pour que la redirection après connexion Google fonctionne ?
- [ ] Désactiver la RLS sur toutes les tables
- [ ] Utiliser la clé service_role dans le navigateur
- [x] Ajouter l'URL de rappel dans les Redirect URLs autorisées de Supabase
- [ ] Créer une table google_users
> Supabase n'accepte de rediriger que vers des URL déclarées. Une URL de rappel absente de la liste provoque un refus.
:::

## Suivre l'état de la session

Pour afficher « Se connecter » ou « Mon profil » selon que l'utilisateur est connecté, écoute les changements avec `onAuthStateChange` :

```jsx
'use client';

import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';

export function useSession() {
  const [session, setSession] = useState(null);
  const [pret, setPret] = useState(false);

  useEffect(() => {
    supabase.auth.getSession().then(({ data }) => {
      setSession(data.session);
      setPret(true);
    });

    const { data: abonnement } = supabase.auth.onAuthStateChange((_event, nouvelleSession) => {
      setSession(nouvelleSession);
    });

    return () => abonnement.subscription.unsubscribe();
  }, []);

  return { session, pret };
}
```

Ce hook lit la session au chargement, puis se met à jour à chaque connexion, déconnexion ou renouvellement. Le `return` du `useEffect` désabonne l'écouteur quand le composant disparaît : sans lui, tu accumules des écouteurs.

### getSession ou getUser ?

Deux méthodes, deux niveaux de confiance :

- `getSession()` lit la session **stockée localement**, sans vérification auprès du serveur ;
- `getUser()` interroge le serveur Auth et **vérifie** le jeton.

Côté navigateur, pour de l'affichage, `getSession` suffit. Côté serveur, pour **décider d'un accès**, utilise toujours `getUser()` : un cookie peut avoir été falsifié, et seul le serveur peut confirmer.

## Créer un profil automatiquement

La table `auth.users` n'est pas faite pour stocker le nom d'affichage ou le niveau de l'utilisateur. On crée donc une table `profiles` (vue au chapitre 2) et on la remplit **automatiquement** à chaque inscription grâce à un **trigger** : une fonction que la base exécute toute seule lors d'un événement.

```sql
-- Le profil référence le compte d'authentification
alter table profiles
  alter column id drop default,
  add constraint profiles_id_fkey
    foreign key (id) references auth.users (id) on delete cascade;

create function public.handle_new_user()
returns trigger
language plpgsql
security definer set search_path = ''
as $$
begin
  insert into public.profiles (id, username, display_name)
  values (
    new.id,
    coalesce(new.raw_user_meta_data ->> 'username', split_part(new.email, '@', 1)),
    coalesce(new.raw_user_meta_data ->> 'display_name', 'Nouvel apprenant')
  );
  return new;
end;
$$;

create trigger on_auth_user_created
  after insert on auth.users
  for each row execute function public.handle_new_user();
```

Quelques points à comprendre. `new` désigne la ligne qui vient d'être insérée dans `auth.users`. `raw_user_meta_data` contient les métadonnées passées dans `options.data` lors du `signUp`. `security definer` fait exécuter la fonction avec les droits de son propriétaire, nécessaire car l'utilisateur n'a pas encore de droits. Et `set search_path = ''` est une précaution de sécurité : elle oblige à écrire les noms de tables avec leur schéma.

Désormais, chaque inscription crée une ligne dans `profiles` avec le même `id` que le compte.

## Authentification côté serveur avec Next.js

Dans une application Next.js moderne (App Router), une partie du code s'exécute sur le serveur. Pour que le serveur connaisse la session, elle doit être stockée dans des **cookies**, et non dans le stockage local du navigateur. Supabase fournit pour cela le paquet `@supabase/ssr` :

```bash
npm install @supabase/ssr
```

Tu crées deux clients : un pour le navigateur, un pour le serveur.

```js
// lib/supabase/client.js : composants client
import { createBrowserClient } from '@supabase/ssr';

export function createClient() {
  return createBrowserClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY
  );
}
```

```js
// lib/supabase/server.js : composants serveur et actions
import { createServerClient } from '@supabase/ssr';
import { cookies } from 'next/headers';

export async function createClient() {
  const cookieStore = await cookies();

  return createServerClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
    {
      cookies: {
        getAll: () => cookieStore.getAll(),
        setAll: (liste) => {
          try {
            liste.forEach(({ name, value, options }) =>
              cookieStore.set(name, value, options)
            );
          } catch {
            // appelé depuis un composant serveur : le middleware s'en charge
          }
        },
      },
    }
  );
}
```

Dans un composant serveur, tu peux alors vérifier l'utilisateur :

```jsx
import { redirect } from 'next/navigation';
import { createClient } from '@/lib/supabase/server';

export default async function Dashboard() {
  const supabase = await createClient();
  const { data: { user } } = await supabase.auth.getUser();

  if (!user) redirect('/connexion');

  return <h1>Bonjour {user.email}</h1>;
}
```

### Le middleware : renouveler la session

Les composants serveur ne peuvent pas écrire de cookies. Un **middleware** s'exécute avant chaque requête et renouvelle le jeton quand il expire :

```js
// middleware.js
import { createServerClient } from '@supabase/ssr';
import { NextResponse } from 'next/server';

export async function middleware(request) {
  let response = NextResponse.next({ request });

  const supabase = createServerClient(
    process.env.NEXT_PUBLIC_SUPABASE_URL,
    process.env.NEXT_PUBLIC_SUPABASE_ANON_KEY,
    {
      cookies: {
        getAll: () => request.cookies.getAll(),
        setAll: (liste) => {
          liste.forEach(({ name, value }) => request.cookies.set(name, value));
          response = NextResponse.next({ request });
          liste.forEach(({ name, value, options }) =>
            response.cookies.set(name, value, options)
          );
        },
      },
    }
  );

  const { data: { user } } = await supabase.auth.getUser();

  if (!user && request.nextUrl.pathname.startsWith('/dashboard')) {
    return NextResponse.redirect(new URL('/connexion', request.url));
  }
  return response;
}

export const config = { matcher: ['/dashboard/:path*'] };
```

Ce middleware rafraîchit la session et redirige vers `/connexion` tout visiteur non connecté qui tente d'ouvrir `/dashboard`.

:::quiz
Pourquoi utiliser getUser() plutôt que getSession() côté serveur pour protéger une page ?
- [ ] getUser est plus rapide
- [x] getUser vérifie le jeton auprès du serveur Auth, alors que getSession lit seulement ce qui est stocké
- [ ] getSession n'existe pas côté serveur
- [ ] getUser évite d'utiliser des cookies
> Les cookies peuvent être falsifiés. getUser contacte Supabase Auth pour confirmer l'identité ; getSession fait simplement confiance aux données locales.
:::

## Mot de passe oublié

Le flux comporte deux étapes. D'abord, l'envoi du lien :

```js
await supabase.auth.resetPasswordForEmail('awa@example.com', {
  redirectTo: 'https://devroad.example/nouveau-mot-de-passe',
});
```

Sur la page de destination, l'utilisateur est automatiquement connecté pour un court instant, et tu appelles :

```js
const { error } = await supabase.auth.updateUser({ password: nouveauMotDePasse });
```

## Atelier guidé : comptes DevRoad

Prévois une heure et demie.

1. Dans ton projet, vérifie que le fournisseur Email est actif et que la confirmation est désactivée pour le développement.
2. Crée le trigger `handle_new_user` avec le SQL de ce chapitre.
3. Construis une page `/inscription` avec nom d'utilisateur, e-mail et mot de passe, qui appelle `signUp` avec `options.data`.
4. Vérifie dans Table Editor qu'une ligne est apparue dans `profiles` avec le bon `id`.
5. Construis `/connexion` avec `signInWithPassword` et un message d'erreur générique.
6. Installe `@supabase/ssr`, crée les clients `client.js` et `server.js`, puis le middleware.
7. Crée `/dashboard` en composant serveur qui affiche le nom du profil et redirige les visiteurs non connectés.
8. Ajoute un bouton de déconnexion qui appelle `signOut` puis redirige.
9. Ajoute une connexion par lien magique en option.

Pour t'auto-évaluer : explique la différence entre `getSession` et `getUser`, et pourquoi le middleware est nécessaire.

## Erreurs fréquentes

- **Utiliser `getSession` côté serveur pour autoriser un accès.** Un cookie falsifié passerait ; utilise `getUser`.
- **Oublier les Redirect URLs.** Google ou le lien magique renvoie une erreur de redirection.
- **Ne pas désabonner `onAuthStateChange`.** Les écouteurs s'accumulent à chaque rendu.
- **Stocker des données de profil dans `auth.users`.** Crée une table `profiles` liée par l'identifiant.
- **Oublier `security definer` sur la fonction du trigger.** L'inscription échoue car l'insertion est refusée.
- **Mettre la clé `service_role` dans le client d'authentification.** Elle contourne toute sécurité.
- **Préciser « e-mail inconnu » dans un message d'erreur.** Tu révèles quels comptes existent.

## Bonnes pratiques

- Garde la confirmation d'e-mail activée en production.
- Exige un mot de passe d'au moins huit caractères et propose un gestionnaire de mots de passe.
- Vérifie l'accès côté serveur avec `getUser`, jamais seulement dans l'interface.
- Sépare les comptes (`auth.users`) des données métier (`profiles`).
- Configure un fournisseur d'e-mail personnalisé (SMTP) en production : l'envoi par défaut de Supabase est limité.
- Prévois plusieurs moyens de connexion pour tes utilisateurs, car beaucoup perdent leurs mots de passe.

## À retenir

- Une session = access token (JWT, courte durée) + refresh token (renouvellement).
- Le JWT transporte l'identité de l'utilisateur jusqu'à la base, ce qui rend la RLS possible.
- `signUp`, `signInWithPassword`, `signInWithOtp`, `signInWithOAuth` et `signOut` couvrent les cas courants.
- `onAuthStateChange` te permet de réagir à chaque changement de session.
- Un trigger sur `auth.users` crée automatiquement le profil de chaque nouvel utilisateur.
- Avec Next.js, `@supabase/ssr` stocke la session dans des cookies, et le middleware la renouvelle ; côté serveur, fie-toi à `getUser`.
