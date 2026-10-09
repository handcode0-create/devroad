---
title: Storage et Realtime
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Une application moderne ne se limite pas à des lignes dans des tables. Les utilisateurs envoient des photos de profil, des reçus, des CV en PDF, et ils attendent que l'écran se mette à jour sans recharger la page quand un paiement est confirmé. Supabase répond à ces deux besoins avec **Storage**, pour les fichiers, et **Realtime**, pour les mises à jour en direct.

À la fin du chapitre, tu seras capable de :

- créer des **buckets** publics et privés ;
- envoyer, lister et supprimer des fichiers avec `supabase-js` ;
- générer des URL publiques et des **URL signées** temporaires ;
- protéger les fichiers avec des policies sur `storage.objects` ;
- valider le type et la taille d'un fichier avant l'envoi ;
- écouter les changements d'une table avec **Postgres Changes** ;
- utiliser **Broadcast** et **Presence** pour des échanges éphémères ;
- nettoyer correctement les abonnements dans React.

Prérequis : les chapitres 1 à 4. Prévois deux heures trente.

## Storage : comment ça marche

Les fichiers sont rangés dans des **buckets**, comparables à des dossiers racines. À l'intérieur, tu organises les fichiers avec des chemins (`avatars/awa/photo.png`). Un bucket est soit :

- **public** : toute personne qui connaît l'URL peut télécharger le fichier, sans connexion ;
- **privé** : l'accès exige un utilisateur autorisé ou une URL signée temporaire.

| Cas d'usage | Type de bucket |
| --- | --- |
| Avatars, logos, images de couverture | Public |
| Reçus de paiement, pièces d'identité, CV | Privé |
| Fichiers modifiables par leur propriétaire seul | Privé, avec policies |

Pour créer un bucket, va dans Storage > New bucket, ou utilise SQL :

```sql
insert into storage.buckets (id, name, public, file_size_limit, allowed_mime_types)
values
  ('avatars', 'avatars', true, 2097152, array['image/png', 'image/jpeg', 'image/webp']),
  ('receipts', 'receipts', false, 5242880, array['application/pdf', 'image/jpeg', 'image/png']);
```

Les deux dernières options sont précieuses : elles limitent la taille (ici 2 Mo et 5 Mo) et les types acceptés **côté serveur**, donc impossibles à contourner depuis le navigateur. Pense à la connexion des utilisateurs : une photo de 8 Mo sur un forfait mobile est un vrai coût.

## Envoyer et lire des fichiers

Voici l'envoi d'une photo de profil :

```js
async function envoyerAvatar(userId, fichier) {
  const extension = fichier.name.split('.').pop();
  const chemin = `${userId}/avatar.${extension}`;

  const { data, error } = await supabase.storage
    .from('avatars')
    .upload(chemin, fichier, {
      cacheControl: '3600',
      upsert: true,           // remplace si le fichier existe déjà
      contentType: fichier.type,
    });

  if (error) throw error;
  return data.path;
}
```

Sans `upsert: true`, un second envoi vers le même chemin échoue avec une erreur « already exists ». Remarque la structure du chemin : le **premier dossier est l'identifiant de l'utilisateur**. Ce détail est la clé de la sécurité, comme tu vas le voir.

### URL publique et URL signée

Pour un bucket public, l'URL se calcule sans appel réseau :

```js
const { data } = supabase.storage
  .from('avatars')
  .getPublicUrl(`${userId}/avatar.png`);

console.log(data.publicUrl);
```

Pour un bucket privé, tu demandes une **URL signée** valable un temps limité, en secondes :

```js
const { data, error } = await supabase.storage
  .from('receipts')
  .createSignedUrl(`${userId}/recu-001.pdf`, 60);   // valable 60 secondes

if (!error) window.open(data.signedUrl);
```

Après 60 secondes, le lien ne fonctionne plus. C'est idéal pour un reçu : tu peux le partager brièvement sans jamais l'exposer en permanence.

### Lister et supprimer

```js
// lister les fichiers d'un dossier
const { data: fichiers } = await supabase.storage
  .from('receipts')
  .list(userId, { limit: 20, sortBy: { column: 'created_at', order: 'desc' } });

// supprimer
await supabase.storage.from('receipts').remove([`${userId}/recu-001.pdf`]);
```

:::quiz
Un reçu de paiement doit pouvoir être téléchargé temporairement par son propriétaire. Quelle approche choisis-tu ?
- [ ] Un bucket public avec un nom de fichier difficile à deviner
- [x] Un bucket privé et une URL signée à durée limitée
- [ ] Stocker le fichier dans une colonne texte en clair
- [ ] Envoyer la clé service_role au navigateur
> Les fichiers sensibles vont dans un bucket privé. L'URL signée donne un accès temporaire sans rendre le fichier public.
:::

## Sécuriser Storage avec des policies

Storage s'appuie sur la table `storage.objects`, et donc sur la **RLS** que tu connais. Sans policy, un utilisateur connecté ne peut ni envoyer ni lire de fichier dans un bucket privé. La règle classique : **chacun gère uniquement les fichiers de son dossier**.

La fonction `storage.foldername(name)` renvoie un tableau avec les dossiers du chemin. Le premier élément est l'identifiant de l'utilisateur :

```sql
create policy "Un utilisateur envoie dans son dossier"
  on storage.objects for insert
  to authenticated
  with check (
    bucket_id = 'receipts'
    and (storage.foldername(name))[1] = (select auth.uid())::text
  );

create policy "Un utilisateur lit ses fichiers"
  on storage.objects for select
  to authenticated
  using (
    bucket_id = 'receipts'
    and (storage.foldername(name))[1] = (select auth.uid())::text
  );

create policy "Un utilisateur supprime ses fichiers"
  on storage.objects for delete
  to authenticated
  using (
    bucket_id = 'receipts'
    and (storage.foldername(name))[1] = (select auth.uid())::text
  );
```

Pour que `upsert` fonctionne, il faut aussi une policy `update` du même modèle. Les avatars d'un bucket **public** se lisent par URL sans policy `select`, mais il faut quand même des policies d'écriture pour que seul le propriétaire modifie son image :

```sql
create policy "Avatars : écriture dans son dossier"
  on storage.objects for insert
  to authenticated
  with check (
    bucket_id = 'avatars'
    and (storage.foldername(name))[1] = (select auth.uid())::text
  );
```

### Un composant d'envoi complet

```jsx
'use client';

import { useState } from 'react';
import { supabase } from '@/lib/supabase';

const TYPES = ['image/png', 'image/jpeg', 'image/webp'];
const TAILLE_MAX = 2 * 1024 * 1024; // 2 Mo

export default function AvatarUpload({ userId, onTermine }) {
  const [envoi, setEnvoi] = useState(false);
  const [erreur, setErreur] = useState('');

  async function choisir(e) {
    const fichier = e.target.files?.[0];
    if (!fichier) return;

    if (!TYPES.includes(fichier.type)) return setErreur('Format non accepté (PNG, JPEG ou WebP).');
    if (fichier.size > TAILLE_MAX) return setErreur('Image trop lourde (2 Mo maximum).');

    setErreur('');
    setEnvoi(true);
    const chemin = `${userId}/avatar.${fichier.type.split('/')[1]}`;
    const { error } = await supabase.storage
      .from('avatars')
      .upload(chemin, fichier, { upsert: true, contentType: fichier.type });
    setEnvoi(false);

    if (error) return setErreur(error.message);
    const { data } = supabase.storage.from('avatars').getPublicUrl(chemin);
    onTermine(data.publicUrl);
  }

  return (
    <div>
      <input type="file" accept={TYPES.join(',')} onChange={choisir} disabled={envoi} />
      {envoi && <p>Envoi en cours…</p>}
      {erreur && <p role="alert">{erreur}</p>}
    </div>
  );
}
```

La validation côté navigateur donne un retour immédiat à l'utilisateur, mais elle n'est qu'un confort. La vraie limite est celle du bucket (`file_size_limit` et `allowed_mime_types`), qui ne peut pas être contournée.

> **Astuce** : après l'envoi, enregistre le chemin ou l'URL dans `profiles.avatar_url`. L'application affiche alors l'image avec une simple balise `img`, sans interroger Storage.

## Realtime : réagir aux changements

Realtime permet à ton application de recevoir des événements dès qu'une ligne change. Trois fonctionnalités existent :

| Fonctionnalité | Rôle |
| --- | --- |
| **Postgres Changes** | Notifie les insertions, modifications et suppressions d'une table |
| **Broadcast** | Messages éphémères entre clients (curseurs, « en train d'écrire ») |
| **Presence** | Savoir qui est connecté à un salon |

### Postgres Changes

Il faut d'abord activer Realtime sur la table, en l'ajoutant à la publication Postgres :

```sql
alter publication supabase_realtime add table payments;
```

(Dans le tableau de bord, l'option est aussi disponible dans Database > Replication.) Ensuite, côté code, tu ouvres un **channel** et tu t'abonnes :

```js
const channel = supabase
  .channel('mes-paiements')
  .on(
    'postgres_changes',
    {
      event: 'UPDATE',
      schema: 'public',
      table: 'payments',
      filter: `profile_id=eq.${userId}`,
    },
    (payload) => {
      console.log('Paiement mis à jour :', payload.new);
    }
  )
  .subscribe();

// plus tard, quand on n'en a plus besoin
supabase.removeChannel(channel);
```

Le `payload` contient `new` (la ligne après l'événement) et `old` (avant, avec des données limitées par défaut). Le `filter` réduit le trafic : ici, le client ne reçoit que ses propres paiements.

Les **policies RLS s'appliquent aussi à Realtime** : un utilisateur ne reçoit que les événements des lignes qu'il a le droit de lire. Ta sécurité du chapitre 4 reste donc la protection principale.

### Un composant qui suit un paiement en direct

Imagine un client qui paie par Orange Money. Il valide sur son téléphone, ton serveur reçoit la confirmation et passe le statut à `paid`. L'écran du client doit changer tout seul :

```jsx
'use client';

import { useEffect, useState } from 'react';
import { supabase } from '@/lib/supabase';

export default function SuiviPaiement({ paymentId, statutInitial }) {
  const [statut, setStatut] = useState(statutInitial);

  useEffect(() => {
    const channel = supabase
      .channel(`paiement-${paymentId}`)
      .on(
        'postgres_changes',
        { event: 'UPDATE', schema: 'public', table: 'payments', filter: `id=eq.${paymentId}` },
        (payload) => setStatut(payload.new.status)
      )
      .subscribe();

    return () => {
      supabase.removeChannel(channel);
    };
  }, [paymentId]);

  if (statut === 'paid') return <p>Paiement confirmé, merci !</p>;
  if (statut === 'failed') return <p role="alert">Le paiement a échoué.</p>;
  return <p>En attente de confirmation sur ton téléphone…</p>;
}
```

La fonction de retour du `useEffect` supprime le channel au démontage. Sans elle, chaque navigation ouvre une nouvelle connexion sans fermer l'ancienne : fuite mémoire, doublons d'événements et facture de connexions simultanées qui monte.

:::quiz
Pourquoi faut-il appeler removeChannel dans la fonction de nettoyage de useEffect ?
- [ ] Pour supprimer la table de la base
- [x] Pour éviter les connexions et écouteurs qui s'accumulent après chaque démontage
- [ ] Pour désactiver la RLS
- [ ] Parce que subscribe ne fonctionne qu'une fois
> Un channel non fermé continue de recevoir des événements et occupe une connexion. Le nettoyage libère les ressources.
:::

## Broadcast et Presence

Postgres Changes écoute la base. **Broadcast** envoie des messages directement entre clients, sans passer par une table : parfait pour des informations qui n'ont pas besoin d'être sauvegardées.

```js
const salon = supabase.channel('salon-react');

salon
  .on('broadcast', { event: 'ecrit' }, ({ payload }) => {
    console.log(`${payload.nom} est en train d'écrire…`);
  })
  .subscribe((status) => {
    if (status === 'SUBSCRIBED') {
      salon.send({ type: 'broadcast', event: 'ecrit', payload: { nom: 'Awa' } });
    }
  });
```

**Presence** partage un état par utilisateur connecté, ce qui permet d'afficher « 12 personnes lisent cette roadmap » :

```js
const salon = supabase.channel('roadmap-12', {
  config: { presence: { key: userId } },
});

salon
  .on('presence', { event: 'sync' }, () => {
    const etat = salon.presenceState();
    console.log(`${Object.keys(etat).length} lecteur(s) en ligne`);
  })
  .subscribe(async (status) => {
    if (status === 'SUBSCRIBED') {
      await salon.track({ nom: 'Awa', en_ligne_a: new Date().toISOString() });
    }
  });
```

Utilise Broadcast et Presence pour l'éphémère, et Postgres Changes pour réagir aux données réellement enregistrées.

:::quiz
Tu veux afficher « Awa est en train d'écrire… » sans enregistrer cette information. Quelle fonctionnalité choisis-tu ?
- [ ] Postgres Changes avec une table temporaire
- [x] Broadcast
- [ ] Une URL signée
- [ ] Un trigger SQL
> Broadcast échange des messages éphémères entre clients sans écriture en base, ce qui convient aux indicateurs de saisie.
:::

## Atelier guidé : avatars et suivi de paiement

Prévois une heure et demie.

1. Crée les buckets `avatars` (public) et `receipts` (privé) avec limites de taille et types MIME.
2. Écris les policies `insert`, `update` et `delete` sur `storage.objects` limitées au dossier de l'utilisateur.
3. Ajoute la colonne `avatar_url` à `profiles` dans une migration.
4. Construis le composant `AvatarUpload`, vérifie qu'un fichier de 5 Mo est refusé.
5. Enregistre l'URL publique dans `profiles.avatar_url` après l'envoi et affiche l'image.
6. Envoie un PDF dans `receipts` et affiche un bouton « Télécharger » qui utilise `createSignedUrl` sur 60 secondes.
7. Essaie, avec un second compte, d'envoyer un fichier dans le dossier du premier : la policy doit refuser.
8. Ajoute `payments` à la publication `supabase_realtime`.
9. Construis le composant `SuiviPaiement`, puis change le statut dans le Table Editor et observe l'écran se mettre à jour.
10. Navigue entre deux pages plusieurs fois et vérifie, dans l'onglet réseau, qu'une seule connexion WebSocket reste ouverte.

Pour t'auto-évaluer : explique pourquoi le premier dossier du chemin est l'identifiant de l'utilisateur, et ce que cela change pour la sécurité.

## Erreurs fréquentes

- **Mettre des documents sensibles dans un bucket public.** Quiconque a le lien y accède.
- **Oublier les policies sur `storage.objects`.** L'envoi échoue avec une erreur de violation de règle.
- **Ne pas préfixer le chemin par l'identifiant utilisateur.** Les policies par dossier ne peuvent plus s'appliquer.
- **Oublier `upsert`.** Le second envoi du même fichier échoue.
- **Ne pas ajouter la table à la publication `supabase_realtime`.** L'abonnement réussit mais aucun événement n'arrive.
- **Ne pas nettoyer les channels.** Connexions et événements en double s'accumulent.
- **Se contenter de la validation côté client.** Un utilisateur malveillant l'ignore ; configure les limites du bucket.

## Bonnes pratiques

- Limite la taille et les types MIME au niveau du bucket.
- Range les fichiers privés sous `identifiant_utilisateur/…`.
- Compresse les images avant l'envoi : tes utilisateurs te remercieront en forfait data limité.
- Donne aux URL signées la durée la plus courte possible.
- Filtre les abonnements Realtime (`filter`) pour réduire le trafic.
- Ne mets en Realtime que les tables qui en ont vraiment besoin.
- Garde l'état de référence dans la base et utilise Realtime pour l'actualiser, pas pour le remplacer.

## À retenir

- Storage range les fichiers dans des buckets publics ou privés ; les privés s'ouvrent avec des URL signées.
- Les droits d'accès aux fichiers s'écrivent en policies RLS sur `storage.objects`.
- `file_size_limit` et `allowed_mime_types` sont des protections côté serveur.
- Postgres Changes diffuse les changements de tables ; la RLS filtre ce que chaque utilisateur reçoit.
- Broadcast et Presence servent aux échanges éphémères et à la présence en ligne.
- Dans React, ferme toujours un channel dans la fonction de nettoyage de `useEffect`.
