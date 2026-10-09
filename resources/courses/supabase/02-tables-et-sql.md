---
title: Tables et SQL
minutes: 150
level: beginner
---

## Ce que tu vas apprendre

Une application solide commence par une base bien modélisée. Dans ce chapitre, tu apprends à créer des tables avec les bons types et les bonnes contraintes, à les relier entre elles avec des clés étrangères, puis à lire des données liées en une seule requête avec `supabase-js`. Tu découvres aussi les migrations, qui permettent de faire évoluer ta base sans rien casser.

À la fin du chapitre, tu seras capable de :

- choisir le bon type pour chaque colonne (`text`, `integer`, `boolean`, `timestamptz`, `uuid`, `jsonb`) ;
- poser des contraintes : `primary key`, `not null`, `unique`, `check`, `default` ;
- relier des tables avec des **clés étrangères** et choisir un comportement de suppression ;
- modéliser une relation un-à-plusieurs et une relation plusieurs-à-plusieurs ;
- écrire des requêtes SQL avec jointures, agrégats et regroupements ;
- lire des données liées avec la syntaxe d'imbrication de `supabase-js` ;
- écrire une migration et la rejouer avec la CLI Supabase.

Prérequis : le chapitre 1 et ton projet `devroad-cours`. Prévois deux heures trente. Tout se fait dans le SQL Editor du tableau de bord.

## Les types de colonnes

Chaque colonne a un type. Choisir le bon type évite des erreurs et des conversions plus tard.

| Type | Usage | Exemple |
| --- | --- | --- |
| `text` | Texte de longueur libre | un titre, un nom |
| `integer` / `bigint` | Nombres entiers | une quantité, un montant en FCFA |
| `numeric(12,2)` | Décimaux exacts | un prix avec centimes |
| `boolean` | Vrai ou faux | `is_published` |
| `timestamptz` | Date et heure avec fuseau | `created_at` |
| `date` | Une date seule | une date d'échéance |
| `uuid` | Identifiant universel | l'identifiant d'un utilisateur |
| `jsonb` | Document JSON | des préférences |

Deux conseils importants. Pour un montant en **francs CFA**, qui n'a pas de centimes en pratique, utilise un entier (`bigint`) : `15000` signifie 15 000 FCFA. N'utilise jamais `float` pour de l'argent, car les arrondis binaires créent des erreurs. Et pour les dates, prends toujours `timestamptz` plutôt que `timestamp` : il stocke l'instant exact en UTC et évite les décalages horaires.

## Créer une table avec des contraintes

Les contraintes sont des règles que la base fait respecter, quoi que fasse l'application. C'est ta dernière ligne de défense contre les données invalides.

```sql
create table profiles (
  id uuid primary key default gen_random_uuid(),
  username text not null unique,
  display_name text not null,
  level text not null default 'beginner'
    check (level in ('beginner', 'intermediate', 'professional')),
  created_at timestamptz not null default now()
);
```

Chaque contrainte a un rôle précis :

- `primary key` : identifie chaque ligne de façon unique (implique `not null` et `unique`) ;
- `not null` : la colonne doit toujours avoir une valeur ;
- `unique` : deux lignes ne peuvent pas partager la même valeur ;
- `default` : valeur utilisée si tu n'en fournis pas ;
- `check` : condition arbitraire que toute ligne doit respecter.

### Quelle clé primaire choisir ?

Deux options courantes. Un **identifiant numérique auto-incrémenté** est compact et lisible :

```sql
id bigint generated always as identity primary key
```

Un **uuid** est impossible à deviner et peut être généré côté client sans conflit :

```sql
id uuid primary key default gen_random_uuid()
```

Règle simple : utilise un `uuid` pour tout ce qui est exposé aux utilisateurs ou lié à un compte, et un entier pour les tables de référence internes. Pour les utilisateurs, l'identifiant vient du système d'authentification (chapitre 3) et c'est un `uuid`.

:::quiz
Quel type est le plus adapté pour stocker un montant de 15 000 FCFA sans risque d'erreur d'arrondi ?
- [ ] float
- [ ] text
- [x] bigint (entier)
- [ ] boolean
> Le franc CFA s'utilise sans centimes : un entier est exact et simple. Les types flottants introduisent des erreurs d'arrondi inacceptables pour de l'argent.
:::

## Relier des tables : les clés étrangères

Une **clé étrangère** (*foreign key*) force une colonne à pointer vers une ligne qui existe vraiment dans une autre table. Ajoutons les étapes d'une roadmap :

```sql
create table roadmap_steps (
  id bigint generated always as identity primary key,
  roadmap_id bigint not null references roadmaps (id) on delete cascade,
  position integer not null check (position > 0),
  title text not null,
  content text,
  unique (roadmap_id, position)
);
```

La partie `references roadmaps (id)` crée le lien. Il devient impossible d'insérer une étape pour une roadmap qui n'existe pas. La contrainte `unique (roadmap_id, position)` garantit que deux étapes d'une même roadmap n'ont pas le même rang.

### Que se passe-t-il à la suppression ?

La clause `on delete` précise ce qui arrive aux lignes enfants quand le parent disparaît :

| Option | Comportement |
| --- | --- |
| `cascade` | Les lignes enfants sont supprimées aussi |
| `restrict` (ou `no action`, le comportement par défaut) | La suppression du parent est refusée tant qu'il a des enfants |
| `set null` | La colonne enfant passe à `null` |

Pour des étapes appartenant à une roadmap, `cascade` est logique : sans roadmap, les étapes n'ont plus de sens. Pour des factures liées à un client, préfère `restrict` : on ne veut pas perdre l'historique comptable par accident.

> **Attention** : `on delete cascade` est puissant. Supprimer un seul utilisateur peut effacer des centaines de lignes en cascade. Réfléchis à chaque relation avant de choisir.

## Relation un-à-plusieurs et plusieurs-à-plusieurs

La relation **un-à-plusieurs** est la plus fréquente : une roadmap a plusieurs étapes, mais une étape appartient à une seule roadmap. La clé étrangère se place du côté « plusieurs », comme ci-dessus.

La relation **plusieurs-à-plusieurs** demande une **table de liaison**. Un apprenant suit plusieurs roadmaps, et une roadmap est suivie par plusieurs apprenants :

```sql
create table enrollments (
  profile_id uuid not null references profiles (id) on delete cascade,
  roadmap_id bigint not null references roadmaps (id) on delete cascade,
  enrolled_at timestamptz not null default now(),
  primary key (profile_id, roadmap_id)
);
```

La clé primaire est ici **composée** de deux colonnes : un apprenant ne peut pas s'inscrire deux fois à la même roadmap. La table de liaison peut aussi porter ses propres données, comme la date d'inscription.

Autre exemple, avec le Mobile Money : une table `payments` reliée à un client, qui enregistre le montant, l'opérateur et le statut.

```sql
create table payments (
  id uuid primary key default gen_random_uuid(),
  profile_id uuid not null references profiles (id) on delete restrict,
  amount_fcfa bigint not null check (amount_fcfa > 0),
  provider text not null check (provider in ('wave', 'orange_money', 'mtn_momo')),
  status text not null default 'pending'
    check (status in ('pending', 'paid', 'failed')),
  created_at timestamptz not null default now()
);
```

Les `check` empêchent d'enregistrer un montant négatif ou un opérateur inventé, même si ton code a un bug.

:::quiz
Comment modéliser « un apprenant suit plusieurs roadmaps, et une roadmap a plusieurs apprenants » ?
- [ ] Une colonne roadmap_id dans la table des apprenants
- [ ] Une colonne texte contenant la liste des identifiants séparés par des virgules
- [x] Une table de liaison avec deux clés étrangères
- [ ] Deux tables identiques
> Une relation plusieurs-à-plusieurs se représente par une table de liaison qui contient une clé étrangère vers chaque côté.
:::

## Lire et modifier avec SQL

Voici les requêtes essentielles, que tu peux tester dans le SQL Editor.

```sql
-- Insérer
insert into profiles (username, display_name)
values ('awa', 'Awa Koné');

-- Lire avec filtre et tri
select title, level
from roadmaps
where level = 'beginner'
order by created_at desc
limit 5;

-- Modifier
update roadmaps set level = 'intermediate' where id = 3;

-- Supprimer
delete from roadmap_steps where roadmap_id = 3;
```

### Jointures

Une **jointure** combine des lignes de plusieurs tables. `inner join` ne garde que les correspondances ; `left join` garde toutes les lignes de gauche, même sans correspondance.

```sql
select r.title, s.position, s.title as step_title
from roadmaps r
join roadmap_steps s on s.roadmap_id = r.id
order by r.title, s.position;
```

### Agrégats et regroupements

Pour compter ou additionner, on regroupe avec `group by` :

```sql
-- Nombre d'étapes par roadmap (même celles sans étape)
select r.title, count(s.id) as nb_etapes
from roadmaps r
left join roadmap_steps s on s.roadmap_id = r.id
group by r.id, r.title
order by nb_etapes desc;

-- Total encaissé par opérateur
select provider, sum(amount_fcfa) as total
from payments
where status = 'paid'
group by provider;
```

Remarque `count(s.id)` plutôt que le comptage de toutes les colonnes : il ignore les valeurs nulles et donne donc 0 pour une roadmap sans étape grâce au `left join`.

## Lire des données liées avec supabase-js

Écrire des jointures en SQL, c'est bien. Mais depuis ton application, `supabase-js` sait lire des relations directement : il détecte les clés étrangères et te laisse **imbriquer** les tables dans `select`.

```js
const { data, error } = await supabase
  .from('roadmaps')
  .select(`
    id,
    title,
    roadmap_steps ( id, position, title )
  `)
  .order('created_at', { ascending: false });
```

Le résultat contient, pour chaque roadmap, un tableau `roadmap_steps` :

```js
[
  {
    id: 1,
    title: 'Laravel de zéro',
    roadmap_steps: [
      { id: 10, position: 1, title: 'Installer PHP' },
      { id: 11, position: 2, title: 'Créer un projet' }
    ]
  }
]
```

Tu peux filtrer et trier la table imbriquée, et même filtrer le parent sur l'enfant :

```js
// trier les étapes de chaque roadmap
const { data } = await supabase
  .from('roadmaps')
  .select('title, roadmap_steps ( position, title )')
  .order('position', { referencedTable: 'roadmap_steps' });

// l'inverse : une étape et sa roadmap
const { data: etapes } = await supabase
  .from('roadmap_steps')
  .select('title, roadmaps ( title )')
  .eq('roadmap_id', 1);
```

Pour la relation plusieurs-à-plusieurs, traverse la table de liaison :

```js
const { data: mesRoadmaps } = await supabase
  .from('enrollments')
  .select('enrolled_at, roadmaps ( id, title )')
  .eq('profile_id', userId);
```

> **Astuce** : une seule requête avec imbrication est presque toujours préférable à plusieurs requêtes successives. Elle réduit les allers-retours réseau, ce qui se ressent vraiment sur une connexion mobile instable.

## Index et performances

Un **index** accélère les recherches comme l'index d'un livre. Postgres en crée un automatiquement pour les clés primaires et les contraintes `unique`, mais **pas** pour les clés étrangères. Si tu filtres souvent sur `roadmap_id`, ajoute-en un :

```sql
create index roadmap_steps_roadmap_id_idx
  on roadmap_steps (roadmap_id);
```

Ne multiplie pas les index : chacun ralentit légèrement les écritures. Crée-les là où tu filtres et joins souvent.

## Migrations : faire évoluer la base proprement

Modifier la base à la main dans le tableau de bord est pratique pour apprendre, mais risqué en équipe : personne ne sait ce qui a changé ni dans quel ordre. Une **migration** est un fichier SQL daté, versionné avec ton code, qui décrit un changement.

```bash
npm install supabase --save-dev
npx supabase init
npx supabase migration new creer_roadmap_steps
```

La commande crée un fichier `supabase/migrations/20260101120000_creer_roadmap_steps.sql`. Tu y écris ton SQL, puis tu l'appliques :

```bash
npx supabase login
npx supabase link --project-ref abcdefgh
npx supabase db push
```

Chaque migration est exécutée une fois, dans l'ordre. Pour corriger une erreur, **n'édite pas** une migration déjà appliquée : crée-en une nouvelle (`alter table …`). Ton historique reste fiable et reproductible sur un nouvel environnement.

:::quiz
Tu as déjà appliqué une migration et tu veux ajouter une colonne. Que fais-tu ?
- [ ] Je modifie le fichier de migration existant et je relance
- [x] Je crée une nouvelle migration avec un alter table
- [ ] Je supprime la base et je recommence
- [ ] J'ajoute la colonne à la main sans fichier
> Les migrations appliquées ne se modifient pas. On ajoute une nouvelle migration pour garder un historique fiable et rejouable.
:::

## Atelier guidé : le modèle de données de DevRoad

Prévois une heure et demie. Travaille dans une migration (ou dans le SQL Editor).

1. Crée la table `profiles` avec `username` unique et un niveau contraint par `check`.
2. Crée `roadmap_steps` avec une clé étrangère vers `roadmaps` et `unique (roadmap_id, position)`.
3. Crée la table de liaison `enrollments` avec une clé primaire composée.
4. Insère trois roadmaps, cinq étapes par roadmap et deux profils.
5. Écris une requête SQL qui compte les étapes par roadmap, y compris celles sans étape.
6. Essaie d'insérer une étape avec un `roadmap_id` inexistant et lis le message d'erreur.
7. Essaie d'insérer deux étapes avec la même position et observe la contrainte `unique`.
8. Depuis Next.js, affiche les roadmaps avec leurs étapes imbriquées, triées par `position`.
9. Ajoute l'index sur `roadmap_steps (roadmap_id)`.
10. Transforme toutes ces étapes en fichiers de migration et applique-les avec `db push`.

Pour t'auto-évaluer : explique la différence entre `cascade` et `restrict`, et donne un cas où chacun est adapté.

## Erreurs fréquentes

- **Utiliser `float` pour de l'argent.** Les arrondis faussent les totaux ; prends un entier ou `numeric`.
- **Oublier `not null`.** Des valeurs vides s'infiltrent et font planter l'affichage.
- **Mettre `cascade` partout.** Une suppression anodine efface un historique important.
- **Stocker une liste dans une colonne texte.** Utilise une table de liaison.
- **Oublier l'index sur une clé étrangère.** Les requêtes ralentissent quand la table grossit.
- **Modifier une migration déjà appliquée.** L'historique devient incohérent entre les environnements.
- **Ne pas déclarer la clé étrangère.** Sans elle, l'imbrication de `supabase-js` ne fonctionne pas.

## Bonnes pratiques

- Nomme tables et colonnes en `snake_case`, au pluriel pour les tables (`roadmaps`) et au singulier pour les colonnes.
- Mets des contraintes dans la base, pas seulement dans l'interface : un script ou une API peuvent contourner ton formulaire.
- Ajoute `created_at` sur chaque table ; tu en auras besoin pour trier et déboguer.
- Prends `timestamptz` pour les dates et un entier pour les montants en FCFA.
- Versionne chaque changement de schéma dans une migration.
- Récupère les données liées en une seule requête imbriquée.

## À retenir

- Le type et les contraintes d'une colonne protègent tes données mieux que n'importe quel code d'interface.
- Une clé étrangère relie deux tables ; `on delete` décide du sort des lignes enfants.
- Le plusieurs-à-plusieurs passe par une table de liaison, souvent avec une clé primaire composée.
- `join`, `group by` et les agrégats (`count`, `sum`) répondent à la plupart des questions de gestion.
- `supabase-js` lit les relations avec une imbrication de type `table_liee ( colonnes )` dans `select`, en une seule requête.
- Les migrations rendent l'évolution de la base traçable et reproductible.
