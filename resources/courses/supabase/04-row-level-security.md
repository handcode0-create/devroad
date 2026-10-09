---
title: Row Level Security
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Dans une application Supabase classique, le navigateur parle directement à la base de données. C'est ce qui rend le développement rapide, mais cela pose une question de sécurité évidente : qu'est-ce qui empêche un utilisateur de lire les paiements de quelqu'un d'autre ? La réponse est la **Row Level Security** (RLS), la sécurité au niveau des lignes. C'est le chapitre le plus important du cours : une RLS mal écrite, c'est une fuite de données.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi la RLS est indispensable et ce qui se passe quand elle est activée ;
- écrire des **policies** pour `select`, `insert`, `update` et `delete` ;
- distinguer `using` et `with check` ;
- utiliser `auth.uid()` pour limiter chaque utilisateur à ses propres lignes ;
- écrire des règles basées sur des relations (appartenance, rôle administrateur) ;
- tester tes règles directement en SQL ;
- éviter les pièges de performance et de sécurité les plus courants.

Prérequis : les chapitres 1 à 3, en particulier les tables `profiles`, `roadmaps`, `enrollments` et `payments`. Prévois trois heures.

## Pourquoi la RLS

Rappelle-toi l'architecture : l'application appelle l'API Supabase avec la clé **anon**, qui est publique. N'importe qui peut la copier depuis ton code JavaScript et envoyer ses propres requêtes avec Postman. Si ta table n'est pas protégée, cette personne peut lire, modifier ou supprimer **toutes** les lignes.

La RLS déplace la sécurité là où elle ne peut pas être contournée : dans la base elle-même. Chaque requête passe par des règles qui décident, ligne par ligne, ce que l'appelant a le droit de voir ou de modifier. L'interface peut mentir, le client peut être piraté, les règles restent.

Le comportement par défaut est radical :

- table **sans** RLS activée : tout le monde, avec la clé anon, a un accès complet ;
- RLS activée **sans** policy : personne ne peut rien faire (sauf `service_role`) ;
- RLS activée **avec** policies : seules les actions autorisées par au moins une policy passent.

```sql
alter table payments enable row level security;
```

Active la RLS sur **toutes** les tables du schéma `public`, sans exception. Le tableau de bord affiche d'ailleurs un avertissement sur les tables qui ne l'ont pas.

> **Attention** : une table sans RLS est une porte ouverte. C'est la cause numéro un des fuites de données sur les applications construites avec Supabase. Active la RLS dès la création de la table, avant d'y mettre des données.

:::quiz
Une table a la RLS activée mais aucune policy. Que voit un utilisateur connecté qui la lit ?
- [ ] Toutes les lignes
- [ ] Seulement ses propres lignes
- [x] Aucune ligne, sans message d'erreur
- [ ] Une erreur de syntaxe
> Sans policy, tout est refusé par défaut. La lecture renvoie un tableau vide et non une erreur, ce qui surprend souvent les débutants.
:::

## Écrire une policy

Une policy est un petit bout de SQL attaché à une table :

```sql
create policy "nom lisible de la règle"
  on nom_de_table
  for select            -- select | insert | update | delete | all
  to authenticated      -- anon | authenticated (par défaut : tous)
  using ( condition );
```

Les **rôles** à connaître :

| Rôle | Qui est-ce ? |
| --- | --- |
| `anon` | Visiteur non connecté, avec la clé anon |
| `authenticated` | Utilisateur connecté (JWT valide) |
| `service_role` | Clé secrète du serveur : **contourne la RLS** |

La **condition** est une expression SQL évaluée pour chaque ligne. Si elle vaut vrai, la ligne est accessible. Si plusieurs policies s'appliquent à la même action, il suffit qu'**une seule** soit vraie : elles sont combinées avec un OU.

### Lecture publique

La policy la plus simple : tout le monde peut lire les roadmaps, y compris les visiteurs non connectés.

```sql
create policy "Roadmaps lisibles par tous"
  on roadmaps for select
  to anon, authenticated
  using ( true );
```

Personne d'autre que toi (via le tableau de bord ou `service_role`) ne peut les modifier, puisqu'aucune policy n'autorise `insert`, `update` ou `delete`.

## Chacun ses propres lignes : auth.uid()

Le cas le plus fréquent : un utilisateur ne doit accéder qu'à ses données. La fonction `auth.uid()` renvoie l'identifiant de l'utilisateur lu dans le JWT. Il suffit de le comparer à la colonne propriétaire.

```sql
alter table profiles enable row level security;

create policy "Un utilisateur lit son profil"
  on profiles for select
  to authenticated
  using ( id = (select auth.uid()) );

create policy "Un utilisateur modifie son profil"
  on profiles for update
  to authenticated
  using ( id = (select auth.uid()) )
  with check ( id = (select auth.uid()) );
```

Tu remarques l'écriture `(select auth.uid())` avec des parenthèses. C'est volontaire : elle permet à Postgres d'évaluer la fonction **une seule fois** par requête au lieu d'une fois par ligne. Sur une grande table, la différence est énorme.

### using ou with check ?

Ces deux clauses sont souvent confondues :

- `using` filtre les lignes **existantes** : lesquelles peut-on lire, modifier ou supprimer ?
- `with check` valide les lignes **nouvelles ou modifiées** : le résultat de l'écriture est-il acceptable ?

Pour un `insert`, seul `with check` a un sens (il n'y a pas encore de ligne existante). Pour un `delete` ou un `select`, seul `using`. Pour un `update`, utilise les deux : `using` dit quelles lignes tu peux toucher, `with check` dit ce que tu as le droit d'y écrire.

Sans le `with check` sur l'`update` ci-dessus, un utilisateur pourrait modifier son propre profil en changeant son `id` pour celui d'un autre, et donner sa ligne à quelqu'un. Avec `with check`, la nouvelle valeur doit toujours lui appartenir.

```sql
create policy "Un utilisateur crée ses paiements"
  on payments for insert
  to authenticated
  with check ( profile_id = (select auth.uid()) );

create policy "Un utilisateur lit ses paiements"
  on payments for select
  to authenticated
  using ( profile_id = (select auth.uid()) );
```

Ici, un client ne peut créer ou voir que des paiements à son nom. Remarque qu'il ne peut ni modifier ni supprimer ses paiements : faute de policy `update` et `delete`, ces actions sont refusées. C'est exactement ce qu'on veut pour un historique de paiements.

:::quiz
Pour une policy d'insertion, quelle clause permet de vérifier que la ligne créée appartient à l'utilisateur ?
- [ ] using
- [x] with check
- [ ] Les deux sont interdites en insertion
- [ ] returning
> À l'insertion, il n'y a pas de ligne existante à filtrer : with check valide les valeurs de la nouvelle ligne.
:::

## Des règles basées sur des relations

Souvent, l'appartenance n'est pas portée par la ligne elle-même, mais par une table liée. Exemple : un apprenant peut lire les **étapes** d'une roadmap seulement s'il y est inscrit. La table `roadmap_steps` n'a pas de colonne `profile_id` ; on passe par `enrollments` avec une sous-requête.

```sql
alter table roadmap_steps enable row level security;

create policy "Étapes visibles aux inscrits"
  on roadmap_steps for select
  to authenticated
  using (
    exists (
      select 1
      from enrollments e
      where e.roadmap_id = roadmap_steps.roadmap_id
        and e.profile_id = (select auth.uid())
    )
  );
```

Pour que ce type de règle reste rapide, crée un index sur les colonnes utilisées dans la sous-requête :

```sql
create index enrollments_profile_idx on enrollments (profile_id, roadmap_id);
```

Attention : la sous-requête sur `enrollments` est elle-même soumise aux policies de `enrollments`. Il faut donc que l'utilisateur ait le droit de lire ses propres inscriptions :

```sql
alter table enrollments enable row level security;

create policy "Un utilisateur gère ses inscriptions"
  on enrollments for all
  to authenticated
  using ( profile_id = (select auth.uid()) )
  with check ( profile_id = (select auth.uid()) );
```

## Rôles et administrateurs

Comment autoriser un administrateur à tout voir ? Première solution, une table de rôles :

```sql
create table admins (
  profile_id uuid primary key references profiles (id) on delete cascade
);
alter table admins enable row level security;
-- aucune policy : personne ne la lit via l'API

create function public.is_admin()
returns boolean
language sql
security definer set search_path = ''
stable
as $$
  select exists (
    select 1 from public.admins where profile_id = (select auth.uid())
  );
$$;

create policy "Les admins lisent tous les paiements"
  on payments for select
  to authenticated
  using ( (select public.is_admin()) );
```

La fonction `is_admin()` est en `security definer` : elle lit `admins` avec les droits de son propriétaire, même si la table est fermée à l'API. Comme les policies sont combinées avec un OU, un administrateur voit tous les paiements, et un client normal garde sa règle personnelle.

> **Erreur fréquente** : stocker le rôle dans `user_metadata` du JWT. Cette zone est **modifiable par l'utilisateur lui-même** avec `updateUser`. Pour un rôle de sécurité, utilise une table protégée ou `app_metadata`, que seul le serveur peut modifier.

## Tester tes policies

Ne fais jamais confiance à une règle que tu n'as pas testée. Dans le SQL Editor, tu peux jouer le rôle d'un utilisateur :

```sql
begin;

-- se faire passer pour l'utilisateur connecté
set local role authenticated;
select set_config(
  'request.jwt.claims',
  '{"sub": "11111111-1111-1111-1111-111111111111", "role": "authenticated"}',
  true
);

select * from payments;   -- uniquement les paiements de cet utilisateur

rollback;
```

Le `rollback` final annule tout. Teste au minimum quatre cas pour chaque table :

1. un visiteur **anon** ne voit rien de privé ;
2. un utilisateur voit **ses** lignes ;
3. un utilisateur ne voit **pas** les lignes d'un autre ;
4. un utilisateur ne peut pas **écrire** une ligne au nom d'un autre.

Depuis l'application, le test le plus parlant consiste à créer deux comptes et à vérifier qu'ils ne se voient pas mutuellement.

## Pièges et subtilités

### La clé service_role contourne tout

Dans une Edge Function ou une route serveur, le client créé avec la clé `service_role` ignore la RLS. C'est utile pour des tâches d'administration, dangereux si tu y passes des données non vérifiées. Quand tu l'utilises, c'est à **toi** de contrôler l'identité et les droits dans ton code.

### Les vues ignorent la RLS par défaut

Une vue s'exécute avec les droits de son créateur, donc elle peut exposer des lignes que la RLS cacherait. Depuis Postgres 15, ajoute `security_invoker = true` pour qu'elle respecte les droits de l'appelant :

```sql
create view mes_paiements
with (security_invoker = true) as
  select id, amount_fcfa, provider, status, created_at
  from payments;
```

### Restreindre des colonnes

La RLS filtre des **lignes**, pas des colonnes. Si un utilisateur peut modifier sa ligne, il peut modifier toutes ses colonnes, y compris `level` ou un champ `status` de paiement. Pour protéger une colonne, utilise un trigger, ou ne donne pas l'accès en écriture et passe par une Edge Function (chapitre 6) :

```sql
create function public.empecher_changement_statut()
returns trigger language plpgsql as $$
begin
  if current_user = 'authenticated'
     and new.status is distinct from old.status then
    raise exception 'Le statut ne peut pas être modifié depuis le client';
  end if;
  return new;
end;
$$;

create trigger payments_statut_protege
  before update on payments
  for each row execute function public.empecher_changement_statut();
```

Le test sur `current_user` laisse ton serveur (`service_role`) modifier le statut, mais pas le client. Un point crucial pour un système de paiement : le statut « payé » ne doit **jamais** pouvoir être écrit par le client. Il doit être posé par ton serveur, après confirmation de Wave, d'Orange Money ou de MTN MoMo.

:::quiz
Pourquoi stocker le rôle administrateur dans user_metadata est-il dangereux ?
- [ ] Parce que cette zone est trop petite
- [ ] Parce que le JWT ne contient pas user_metadata
- [x] Parce que l'utilisateur peut modifier lui-même cette zone
- [ ] Parce que Postgres ne sait pas lire le JSON
> user_metadata est modifiable par l'utilisateur via updateUser. Un rôle sensible doit venir de app_metadata ou d'une table que le client ne peut pas modifier.
:::

## Atelier guidé : sécuriser DevRoad

Prévois deux heures.

1. Active la RLS sur `profiles`, `roadmaps`, `roadmap_steps`, `enrollments` et `payments`.
2. Rends `roadmaps` lisible par tous, mais non modifiable.
3. Écris les policies `select` et `update` sur `profiles` (avec `with check`).
4. Crée la policy `for all` sur `enrollments` limitée au propriétaire.
5. Écris la policy de lecture des `roadmap_steps` réservée aux inscrits.
6. Crée la table `admins`, la fonction `is_admin()` et la policy de lecture globale des paiements.
7. Teste en SQL avec `set local role authenticated` les quatre cas décrits plus haut.
8. Crée deux comptes depuis ton application et vérifie qu'ils ne voient pas leurs paiements respectifs.
9. Désactive volontairement une policy et observe le tableau vide dans l'interface.
10. Dans le tableau de bord, ouvre Security Advisor et corrige chaque alerte.

Pour t'auto-évaluer : explique à un collègue pourquoi tes règles restent efficaces même si quelqu'un modifie le code JavaScript de ton site.

## Erreurs fréquentes

- **Oublier d'activer la RLS.** La table est entièrement publique via l'API.
- **Écrire `using (true)` sur une table privée.** Tout le monde la lit ; réserve-le aux données publiques.
- **Oublier `with check` sur un `update`.** L'utilisateur peut réaffecter ses lignes à quelqu'un d'autre.
- **Écrire `auth.uid()` sans `select`.** La fonction est recalculée pour chaque ligne et ralentit tout.
- **Oublier l'index sur les colonnes de la policy.** Les requêtes deviennent lentes avec le volume.
- **Confondre « pas de résultat » et « pas de droit ».** La RLS ne renvoie pas d'erreur, seulement moins de lignes.
- **Utiliser `user_metadata` pour les rôles.** Il est modifiable par l'utilisateur.
- **Laisser une vue sans `security_invoker`.** Elle expose des données que la RLS cachait.

## Bonnes pratiques

- Active la RLS dès la création de chaque table, dans la même migration.
- Écris une policy par action (`select`, `insert`, `update`, `delete`) plutôt qu'un `for all` trop large, sauf cas simple.
- Nomme les policies avec des phrases lisibles qui décrivent la règle.
- Précise toujours `to authenticated` ou `to anon` pour être explicite.
- Entoure les fonctions d'un `select` : `(select auth.uid())`.
- Garde les statuts sensibles (paiement, rôle, crédits) hors de portée du client.
- Teste chaque policy avec au moins deux utilisateurs différents.
- Versionne les policies dans des migrations, comme le reste du schéma.

## À retenir

- La RLS protège les données dans la base, là où le client ne peut pas tricher.
- Activée sans policy, une table refuse tout ; sans RLS, elle est ouverte à tous.
- `using` filtre les lignes existantes, `with check` valide les lignes écrites.
- `(select auth.uid())` identifie l'utilisateur et reste performant.
- Les policies se combinent avec un OU ; les relations s'expriment avec `exists`.
- `service_role` contourne la RLS, les vues demandent `security_invoker`, et la RLS ne protège pas les colonnes.
- Une règle non testée est une règle qu'on ne peut pas croire.
