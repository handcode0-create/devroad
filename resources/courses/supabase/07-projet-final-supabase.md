---
title: Projet final Supabase
minutes: 360
level: professional
---

## Ce que tu vas apprendre

Tu as vu chaque brique séparément. Ce projet les assemble en une application complète, comme tu le ferais pour un client : **Académie Mobile**, une plateforme de vente de formations en ligne pour des apprenants d'Afrique de l'Ouest, avec paiement par Mobile Money, espace privé, reçus téléchargeables et suivi de paiement en direct.

Ce n'est pas un exercice guidé ligne par ligne. Tu reçois un cahier des charges, une architecture et des étapes ; à toi de prendre les décisions de détail. Le résultat peut entrer dans ton portfolio.

À la fin du projet, tu auras :

- conçu un schéma relationnel complet, versionné en migrations ;
- mis en place l'authentification avec création automatique de profil ;
- sécurisé **toutes** les tables et tous les fichiers avec la RLS ;
- livré un flux de paiement sécurisé avec Edge Functions et webhook signé ;
- affiché l'état d'un paiement en direct grâce à Realtime ;
- vérifié le tout avec une checklist d'acceptation et des tests d'attaque.

Prérequis : les chapitres 1 à 6. Prévois six heures, que tu peux répartir sur plusieurs séances. Stack : Next.js (App Router), Supabase, Tailwind CSS. Le fournisseur de paiement est **simulé** : tu écriras un petit script qui joue le rôle de l'opérateur (Wave, Orange Money ou MTN MoMo), sans argent réel.

## Cahier des charges

### Contexte

Un formateur d'Abidjan vend des mini-formations (Laravel, React, Design) entre 5 000 et 25 000 FCFA. Ses clients paient depuis leur téléphone. Il veut un site rapide, léger pour les connexions mobiles, où chaque client retrouve ses achats et télécharge son reçu.

### Rôles

| Rôle | Droits |
| --- | --- |
| **Visiteur** | Voit le catalogue des formations publiées |
| **Apprenant** | Achète, voit ses achats, ses paiements et ses reçus |
| **Administrateur** | Crée et publie des formations, voit tous les paiements |

### Fonctionnalités obligatoires

1. **Catalogue public** : liste des formations publiées avec titre, description, niveau, prix en FCFA et image de couverture.
2. **Comptes** : inscription et connexion par e-mail et mot de passe ; un profil est créé automatiquement.
3. **Achat** : l'apprenant choisit un opérateur (Wave, Orange Money, MTN MoMo) et lance un paiement.
4. **Suivi en direct** : l'écran passe de « en attente » à « confirmé » sans rechargement.
5. **Accès au contenu** : après paiement confirmé, l'apprenant accède aux leçons de la formation.
6. **Reçus** : un reçu PDF ou image est stocké dans un bucket privé et téléchargeable via une URL signée de 60 secondes.
7. **Avatar** : l'utilisateur envoie une photo de profil (2 Mo maximum).
8. **Administration** : l'administrateur voit tous les paiements et publie ou dépublie une formation.

### Exigences non fonctionnelles

- **Sécurité** : RLS activée sur toutes les tables du schéma `public`, policies sur `storage.objects`, aucune clé secrète côté client.
- **Intégrité** : le prix et le statut d'un paiement ne sont jamais fixés par le client.
- **Robustesse** : le webhook est signé et idempotent.
- **Performance** : requêtes imbriquées plutôt que multiples, index sur les clés étrangères et colonnes de policies, images de moins de 200 Ko pour la couverture.
- **Qualité** : toute modification du schéma passe par une migration.

## Architecture

```text
Navigateur (Next.js, clé anon)
   |-- lecture catalogue ------------------> API Supabase (RLS)
   |-- inscription / connexion ------------> Supabase Auth
   |-- avatar, reçus ----------------------> Storage (policies par dossier)
   |-- supabase.functions.invoke ---------> Edge Function creer-paiement
   |-- Realtime (statut paiement) <--------- Postgres Changes

Opérateur (simulé) --webhook signé--------> Edge Function webhook-paiement
                                                 |
                                                 v
                                        service_role : payments.status = paid
                                        + création du reçu + inscription au cours
```

Règle directrice : **le client demande, le serveur décide.** Seules les Edge Functions utilisent la clé `service_role`.

## Modèle de données

Voici le schéma de départ. Écris-le dans des migrations, pas directement dans l'éditeur.

```sql
create table profiles (
  id uuid primary key references auth.users (id) on delete cascade,
  username text not null unique,
  display_name text not null,
  avatar_url text,
  created_at timestamptz not null default now()
);

create table courses (
  id bigint generated always as identity primary key,
  slug text not null unique,
  title text not null,
  description text,
  level text not null default 'beginner'
    check (level in ('beginner', 'intermediate', 'professional')),
  price_fcfa bigint not null check (price_fcfa >= 0),
  cover_url text,
  is_published boolean not null default false,
  created_at timestamptz not null default now()
);

create table lessons (
  id bigint generated always as identity primary key,
  course_id bigint not null references courses (id) on delete cascade,
  position integer not null check (position > 0),
  title text not null,
  content text not null,
  unique (course_id, position)
);

create table payments (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references profiles (id) on delete restrict,
  course_id bigint not null references courses (id) on delete restrict,
  amount_fcfa bigint not null check (amount_fcfa >= 0),
  provider text not null check (provider in ('wave', 'orange_money', 'mtn_momo')),
  status text not null default 'pending'
    check (status in ('pending', 'paid', 'failed')),
  receipt_path text,
  created_at timestamptz not null default now(),
  paid_at timestamptz
);

create table enrollments (
  profile_id uuid not null references profiles (id) on delete cascade,
  course_id bigint not null references courses (id) on delete cascade,
  payment_id uuid references payments (id),
  enrolled_at timestamptz not null default now(),
  primary key (profile_id, course_id)
);

create table admins (
  profile_id uuid primary key references profiles (id) on delete cascade
);

create index payments_profile_idx on payments (profile_id);
create index payments_course_idx on payments (course_id);
create index lessons_course_idx on lessons (course_id);
create index enrollments_course_idx on enrollments (course_id);
```

À toi de compléter : le trigger `handle_new_user` (chapitre 3), l'activation de la RLS sur chaque table, la fonction `is_admin()` (chapitre 4), les buckets `avatars` et `receipts` (chapitre 5).

:::quiz
Pourquoi la clé étrangère payments.profile_id utilise-t-elle on delete restrict plutôt que cascade ?
- [ ] Parce que cascade n'existe pas pour les paiements
- [x] Pour ne pas perdre l'historique comptable si un profil est supprimé par erreur
- [ ] Pour accélérer les requêtes
- [ ] Parce que restrict désactive la RLS
> Un paiement est une trace comptable. restrict empêche la suppression silencieuse d'un historique financier.
:::

## Étape 1 : le socle (45 minutes)

1. Crée un projet Supabase `academie-mobile` et un projet Next.js avec Tailwind CSS.
2. Initialise la CLI : `npx supabase init`, puis `link` vers ton projet.
3. Écris les migrations dans l'ordre : tables, index, trigger de profil, fonction `is_admin`.
4. Applique-les avec `npx supabase db push` et vérifie le résultat dans le Table Editor.
5. Insère trois formations publiées et une non publiée, avec cinq leçons chacune, dans un fichier `supabase/seed.sql`.

Point de contrôle : tu peux lister les formations depuis le SQL Editor.

## Étape 2 : sécurité d'abord (60 minutes)

Écris **toutes** les policies avant de construire l'interface. C'est le meilleur moment pour y penser.

```sql
alter table profiles enable row level security;
alter table courses enable row level security;
alter table lessons enable row level security;
alter table payments enable row level security;
alter table enrollments enable row level security;
alter table admins enable row level security;

-- Catalogue : seulement les formations publiées, sauf pour l'admin
create policy "Catalogue public"
  on courses for select to anon, authenticated
  using ( is_published or (select public.is_admin()) );

create policy "Admin gère les formations"
  on courses for all to authenticated
  using ( (select public.is_admin()) )
  with check ( (select public.is_admin()) );

-- Leçons : réservées aux inscrits
create policy "Leçons des formations achetées"
  on lessons for select to authenticated
  using ( exists (
    select 1 from enrollments e
    where e.course_id = lessons.course_id
      and e.profile_id = (select auth.uid())
  ));

-- Paiements : lecture par le propriétaire ou l'admin, aucune écriture client
create policy "Lecture de ses paiements"
  on payments for select to authenticated
  using ( profile_id = (select auth.uid()) or (select public.is_admin()) );
```

Remarque l'absence de policy `insert` ou `update` sur `payments` : le client **ne peut pas** écrire de paiement. Seules les Edge Functions, avec `service_role`, créent et modifient ces lignes. À toi d'écrire les policies de `profiles` (lecture et mise à jour de sa propre ligne, avec `with check`), de `enrollments` (lecture de ses inscriptions uniquement, aucune écriture client) et celles de Storage (dossier de l'utilisateur).

Point de contrôle : avec `set local role authenticated` et un faux `sub`, un utilisateur ne voit que ses lignes, et un visiteur anonyme ne voit que le catalogue publié.

## Étape 3 : authentification et profil (45 minutes)

Réutilise le chapitre 3 :

- clients `client.js` et `server.js` avec `@supabase/ssr`, plus le middleware ;
- pages `/inscription` et `/connexion`, avec message d'erreur générique ;
- `/compte` protégée côté serveur avec `getUser()`, qui affiche le profil et le formulaire d'avatar du chapitre 5 ;
- déconnexion.

Prévois un composant d'en-tête qui affiche « Connexion » ou le nom de l'utilisateur selon la session. Utilise `onAuthStateChange` côté client pour qu'il se mette à jour sans rechargement.

## Étape 4 : catalogue et pages de formation (45 minutes)

Le catalogue est une page **serveur** qui lit les formations et indique, si l'utilisateur est connecté, celles qu'il possède déjà.

```jsx
// app/page.js
import { createClient } from '@/lib/supabase/server';
import CarteFormation from '@/components/CarteFormation';

export default async function Accueil() {
  const supabase = await createClient();

  const { data: formations, error } = await supabase
    .from('courses')
    .select('id, slug, title, level, price_fcfa, cover_url')
    .eq('is_published', true)
    .order('created_at', { ascending: false });

  if (error) throw new Error('Catalogue indisponible');

  return (
    <main className="mx-auto max-w-5xl p-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
      {formations.map((f) => (
        <CarteFormation key={f.id} formation={f} />
      ))}
    </main>
  );
}
```

Formate les prix avec `Intl.NumberFormat('fr-FR')` : `15 000 FCFA` est plus lisible que `15000`. La page `/formations/[slug]` affiche les détails ; si l'utilisateur est inscrit, elle liste les leçons (la RLS les renvoie seulement pour lui), sinon elle propose le bouton d'achat.

## Étape 5 : le flux de paiement (75 minutes)

C'est le cœur du projet. Reprends les deux fonctions du chapitre 6 en les adaptant.

**`creer-paiement`** reçoit `{ courseId, provider }`, vérifie le jeton avec `getUser()`, lit le **prix en base**, vérifie que le cours est publié et que l'utilisateur n'est pas déjà inscrit, puis insère un paiement `pending` avec `service_role`. Elle renvoie `paymentId`.

**`webhook-paiement`**, déployé avec `--no-verify-jwt`, vérifie la signature HMAC, puis effectue de façon **atomique** et **idempotente** :

1. passage du paiement de `pending` à `paid` (condition `status = 'pending'`) ;
2. création de l'inscription dans `enrollments` ;
3. génération du reçu et enregistrement dans `receipt_path`.

Pour garantir l'atomicité des étapes 1 et 2, déplace-les dans une fonction SQL appelée depuis le webhook :

```sql
create function public.confirmer_paiement(p_payment_id uuid)
returns boolean
language plpgsql
security definer set search_path = ''
as $$
declare
  v_payment public.payments%rowtype;
begin
  update public.payments
     set status = 'paid', paid_at = now()
   where id = p_payment_id and status = 'pending'
  returning * into v_payment;

  if not found then
    return false;   -- déjà traité ou inexistant : rien à faire
  end if;

  insert into public.enrollments (profile_id, course_id, payment_id)
  values (v_payment.profile_id, v_payment.course_id, v_payment.id)
  on conflict do nothing;

  return true;
end;
$$;

-- Réservée au serveur : retire l'accès aux rôles de l'API publique
revoke execute on function public.confirmer_paiement(uuid) from public, anon, authenticated;
```

Le `revoke` est essentiel : une fonction `security definer` appelable par un client lui permettrait de se déclarer lui-même « payé ». Seul `service_role` (donc ton webhook) doit pouvoir l'exécuter.

Côté interface, le composant `SuiviPaiement` du chapitre 5 écoute le paiement et déclenche l'affichage des leçons quand le statut devient `paid`. Prévois aussi un état d'échec avec un bouton « Réessayer ».

:::quiz
Pourquoi retirer le droit d'exécuter confirmer_paiement aux rôles anon et authenticated ?
- [ ] Pour accélérer la fonction
- [x] Pour qu'un client ne puisse pas s'auto-confirmer un paiement via rpc
- [ ] Parce que security definer l'exige toujours
- [ ] Pour désactiver Realtime
> Une fonction security definer s'exécute avec des droits élevés. Si l'API publique peut l'appeler, n'importe qui peut valider un paiement sans payer.
:::

## Étape 6 : reçus et fichiers (30 minutes)

Génère un reçu simple (texte ou HTML converti) dans le webhook, avec le numéro de paiement, le montant en FCFA, l'opérateur et la date. Enregistre-le dans le bucket privé `receipts` sous `profile_id/payment_id.html` avec la clé `service_role`. Dans l'espace « Mes paiements », propose :

```js
async function telechargerRecu(chemin) {
  const { data, error } = await supabase.storage
    .from('receipts')
    .createSignedUrl(chemin, 60);
  if (error) return alert('Reçu indisponible');
  window.open(data.signedUrl, '_blank');
}
```

Vérifie avec un second compte qu'il est impossible de lire le reçu d'autrui, même en devinant le chemin.

## Étape 7 : administration (30 minutes)

Crée une route `/admin` protégée côté serveur : elle appelle `getUser()`, vérifie l'appartenance à `admins`, sinon renvoie un 404. Elle affiche le tableau des paiements avec le nom du client et de la formation, grâce à une requête imbriquée :

```js
const { data: paiements } = await supabase
  .from('payments')
  .select('id, amount_fcfa, provider, status, created_at, profiles ( display_name ), courses ( title )')
  .order('created_at', { ascending: false })
  .limit(50);
```

Ajoute un total des paiements `paid` par opérateur (agrégat SQL dans une vue en `security_invoker`, ou calcul côté serveur) et un bouton pour publier ou dépublier une formation.

## Étape 8 : le simulateur d'opérateur (15 minutes)

Écris un script Node qui lit un `paymentId` en argument, construit le JSON `{ reference, status: 'success' }`, calcule la signature HMAC avec le secret partagé et appelle ton webhook. Tu simules ainsi une confirmation, un échec, un doublon et une fausse signature, sans argent réel.

## Étape 9 : tests d'attaque et finitions (45 minutes)

Joue le rôle d'un pirate. Pour chacune de ces tentatives, l'application doit résister :

- appeler `rpc('confirmer_paiement', …)` depuis la console du navigateur ;
- faire `insert` ou `update` sur `payments` avec la clé anon ;
- lire les leçons d'un cours non acheté ;
- envoyer un montant de 1 FCFA à `creer-paiement` ;
- appeler le webhook avec une mauvaise signature ;
- envoyer un fichier dans le dossier d'un autre utilisateur ;
- se déclarer administrateur en modifiant `user_metadata`.

Termine par le Security Advisor du tableau de bord : aucune alerte critique ne doit rester.

:::quiz
Un utilisateur modifie son user_metadata pour y ajouter role: admin. Que doit-il se passer dans ton application ?
- [ ] Il devient administrateur
- [x] Rien : les droits d'administration viennent de la table admins, pas du metadata modifiable
- [ ] Il est automatiquement banni
- [ ] Les policies sont désactivées
> Le rôle d'administrateur est lu dans une table protégée via is_admin(). Le metadata modifiable par l'utilisateur n'a aucune autorité.
:::

## Checklist d'acceptation

Ton projet est terminé quand **chaque** case est vraie :

- Toutes les tables de `public` ont la RLS activée et au moins une policy adaptée.
- Un visiteur voit seulement les formations publiées.
- Un utilisateur ne voit que ses paiements, ses inscriptions et ses reçus.
- Un client ne peut ni insérer ni modifier un paiement depuis le navigateur.
- Le prix d'un achat vient toujours de la base.
- Le webhook refuse une signature invalide avec un code 401.
- Le même webhook envoyé deux fois ne crée qu'une inscription.
- L'écran de l'apprenant passe à « confirmé » sans rechargement.
- Les leçons ne sont lisibles qu'après un paiement confirmé.
- Le reçu s'ouvre via une URL signée qui expire après 60 secondes.
- Un avatar de plus de 2 Mo ou d'un mauvais format est refusé par le bucket.
- La clé `service_role` n'apparaît ni dans le code client, ni dans le dépôt Git.
- Chaque changement de schéma existe sous forme de migration rejouable.
- Le Security Advisor ne signale aucune alerte critique.
- Les trois états (chargement, erreur, succès) sont gérés sur chaque écran qui interroge la base.
- Le site reste utilisable sur un petit écran et une connexion lente.

## Atelier guidé : plan de livraison

Pour clore le projet, organise ta livraison comme un freelance.

1. Déploie le front sur Vercel avec les variables d'environnement de production.
2. Déploie les migrations et les deux Edge Functions sur le projet de production.
3. Configure l'URL du site et les Redirect URLs dans Authentication.
4. Rejoue la checklist d'acceptation sur l'environnement de production.
5. Rédige une page de démonstration pour ton client : captures, parcours d'achat, limites connues.

Pour t'auto-évaluer : présente ton architecture en cinq minutes à un pair, en expliquant, pour chaque attaque de l'étape 9, quel mécanisme la bloque.

## Erreurs fréquentes

- **Construire l'interface avant la sécurité.** On découvre trop tard des tables ouvertes ; écris les policies d'abord.
- **Laisser une fonction `security definer` appelable par tous.** Retire l'exécution aux rôles publics.
- **Mettre à jour le paiement et l'inscription en deux appels séparés.** Une panne entre les deux laisse un client qui a payé sans accès ; utilise une fonction SQL atomique.
- **Se fier à la page de retour après paiement.** Seul le webhook signé confirme.
- **Oublier l'index sur les colonnes de policies.** Le site ralentit avec la croissance.
- **Tester avec un seul compte.** Les fuites entre utilisateurs ne se voient qu'avec deux comptes.
- **Mettre la clé `service_role` dans `.env.local` avec le préfixe `NEXT_PUBLIC_`.** Elle serait publiée dans le navigateur.
- **Modifier le schéma depuis le tableau de bord sans migration.** Ton environnement de production diverge.

## Bonnes pratiques

- Travaille par petites étapes avec un point de contrôle à chaque fin de phase, et fais un commit Git à chacun.
- Sépare un projet de développement et un projet de production, chacun avec ses clés.
- Écris une courte note `README` : installation, variables d'environnement, commandes de migration, façon de simuler un paiement.
- Garde le prix, les droits et les statuts sous contrôle du serveur ; le client ne fait que demander.
- Documente chaque policy par une phrase qui décrit la règle en français.
- Surveille les logs des fonctions après le déploiement, et teste un paiement complet de bout en bout.
- Pense à ton public : pages légères, images compressées, messages d'erreur clairs en français.

## À retenir

- Une application Supabase complète combine schéma relationnel, Auth, RLS, Storage, Realtime et Edge Functions, chacun à sa place.
- La sécurité se conçoit en premier : RLS partout, jamais de clé secrète côté client.
- Le flux de paiement fiable repose sur trois piliers : prix décidé par le serveur, webhook signé, traitement idempotent et atomique.
- Les fonctions `security definer` doivent être explicitement fermées à l'API publique.
- Les migrations, les tests d'attaque et la checklist transforment un prototype en projet livrable.
- Tu sais maintenant construire de bout en bout un produit qui encaisse du Mobile Money en sécurité.
