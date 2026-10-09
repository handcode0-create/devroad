---
title: Edge Functions et API
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

L'API automatique de Supabase couvre la plupart des besoins : lire, créer, modifier. Mais certaines opérations ne doivent **jamais** se faire dans le navigateur : appeler l'API d'un opérateur de paiement avec une clé secrète, confirmer qu'un paiement Wave a bien eu lieu, envoyer un e-mail, appeler un modèle d'IA. Pour cela, Supabase propose les **Edge Functions** (du code serveur en TypeScript) et les **fonctions de base de données** appelables par `rpc`.

À la fin du chapitre, tu seras capable de :

- décider quand une logique doit passer du client vers le serveur ;
- créer, tester en local et déployer une **Edge Function** ;
- gérer le **CORS** et les **secrets** ;
- identifier l'utilisateur qui appelle ta fonction ;
- écrire un **webhook** qui reçoit la confirmation d'un paiement, avec vérification de signature ;
- créer des fonctions SQL et les appeler avec `supabase.rpc` ;
- rendre tes traitements **idempotents** pour supporter les doublons.

Prérequis : les chapitres 1 à 5, des bases de TypeScript ou JavaScript moderne (`async`, `fetch`). Prévois trois heures. Il te faut Node.js, la CLI Supabase et, pour tester en local, Docker.

## Quand le client ne suffit plus

Dans le chapitre 4, tu as appris qu'on ne doit pas laisser le client écrire le statut d'un paiement. Qui l'écrit alors ? Ton **serveur**, après avoir reçu la confirmation de l'opérateur. Voici les situations qui exigent du code serveur :

- utiliser une **clé secrète** (opérateur de paiement, service d'e-mail, IA) ;
- recevoir un **webhook**, c'est-à-dire un appel venant d'un service externe ;
- exécuter une logique qui ne doit pas être falsifiable (calcul d'un prix, attribution de crédits) ;
- enchaîner plusieurs écritures de façon atomique ;
- planifier une tâche récurrente.

Une Edge Function est une petite fonction qui tourne sur le réseau de Supabase, proche de tes utilisateurs. Elle utilise **Deno**, un environnement JavaScript et TypeScript moderne, et s'appelle par une simple URL HTTPS.

:::quiz
Parmi ces opérations, laquelle doit absolument s'exécuter côté serveur ?
- [ ] Afficher la liste des roadmaps
- [ ] Filtrer un tableau selon le niveau
- [x] Appeler l'API d'un opérateur de paiement avec une clé secrète
- [ ] Mettre en forme une date
> Une clé secrète ne doit jamais atteindre le navigateur. L'appel doit donc passer par une Edge Function ou un autre serveur.
:::

## Ta première Edge Function

Avec la CLI, dans ton projet :

```bash
npx supabase functions new bonjour
npx supabase start               # lance Supabase en local (Docker)
npx supabase functions serve     # sert les fonctions avec rechargement
```

Le fichier `supabase/functions/bonjour/index.ts` contient une fonction de départ. Écrivons-en une propre :

```ts
// supabase/functions/bonjour/index.ts
Deno.serve(async (req) => {
  const { nom } = await req.json();

  const reponse = { message: `Bonjour ${nom ?? 'toi'}, bienvenue sur DevRoad !` };

  return new Response(JSON.stringify(reponse), {
    headers: { 'Content-Type': 'application/json' },
  });
});
```

`Deno.serve` reçoit une `Request` standard du web et doit renvoyer une `Response`. Ce sont les mêmes objets que dans `fetch`. Teste-la :

```bash
curl -X POST http://127.0.0.1:54321/functions/v1/bonjour \
  -H "Authorization: Bearer <ta-cle-anon-locale>" \
  -H "Content-Type: application/json" \
  -d '{"nom": "Awa"}'
```

### Appeler depuis supabase-js

Plutôt que `fetch`, utilise `functions.invoke` : il ajoute automatiquement l'en-tête d'autorisation avec la session de l'utilisateur.

```js
const { data, error } = await supabase.functions.invoke('bonjour', {
  body: { nom: 'Awa' },
});
```

## CORS : autoriser le navigateur

Un navigateur applique la règle de **même origine** : une page sur `devroad.example` n'a pas le droit d'appeler librement un autre domaine. Avant l'appel réel, il envoie une requête de contrôle `OPTIONS` (le *preflight*). Ta fonction doit y répondre avec les bons en-têtes :

```ts
const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

Deno.serve(async (req) => {
  // 1. Répondre au preflight
  if (req.method === 'OPTIONS') {
    return new Response('ok', { headers: corsHeaders });
  }

  // 2. Le traitement normal
  const { nom } = await req.json();
  return new Response(JSON.stringify({ message: `Bonjour ${nom}` }), {
    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
  });
});
```

Ajoute `corsHeaders` à **toutes** les réponses, y compris les erreurs. En production, remplace `*` par le domaine exact de ton application.

## Les secrets

Les clés d'API ne se mettent jamais dans le code. Tu les enregistres comme **secrets** :

```bash
npx supabase secrets set PAYMENT_API_KEY=sk_live_xxxxx
npx supabase secrets set PAYMENT_WEBHOOK_SECRET=whsec_xxxxx
```

Dans la fonction, tu les lis avec `Deno.env.get`. Supabase injecte aussi automatiquement `SUPABASE_URL`, `SUPABASE_ANON_KEY` et `SUPABASE_SERVICE_ROLE_KEY` :

```ts
const cle = Deno.env.get('PAYMENT_API_KEY');
if (!cle) throw new Error('PAYMENT_API_KEY manquante');
```

Pour le développement local, place-les dans un fichier `supabase/functions/.env` et ajoute-le à `.gitignore`.

## Savoir qui appelle : authentifier l'utilisateur

Quand le client appelle ta fonction avec `invoke`, le jeton de l'utilisateur voyage dans l'en-tête `Authorization`. Tu peux créer un client Supabase **au nom de cet utilisateur** : la RLS s'applique alors normalement.

```ts
import { createClient } from 'npm:@supabase/supabase-js@2';

Deno.serve(async (req) => {
  const authHeader = req.headers.get('Authorization');
  if (!authHeader) {
    return new Response('Non authentifié', { status: 401 });
  }

  const supabase = createClient(
    Deno.env.get('SUPABASE_URL')!,
    Deno.env.get('SUPABASE_ANON_KEY')!,
    { global: { headers: { Authorization: authHeader } } }
  );

  const { data: { user }, error } = await supabase.auth.getUser();
  if (error || !user) {
    return new Response('Jeton invalide', { status: 401 });
  }

  // user.id est maintenant fiable
  return Response.json({ userId: user.id });
});
```

Comme au chapitre 3, on utilise `getUser()` qui vérifie le jeton auprès du serveur. Tu peux ensuite lire des données avec ce client, et la RLS limitera les résultats à cet utilisateur.

### Quand utiliser la clé service_role ?

Pour des actions qui doivent **dépasser** les droits de l'utilisateur, tu crées un second client avec `SUPABASE_SERVICE_ROLE_KEY`. Ce client ignore la RLS. Règle d'or : vérifie l'identité et les droits **d'abord**, puis utilise la clé puissante uniquement pour l'écriture précise qui l'exige.

## Un cas réel : lancer un paiement Mobile Money

Imaginons un achat de formation à 15 000 FCFA. Le client ne doit pas pouvoir choisir lui-même le prix. Il envoie seulement l'identifiant de la roadmap ; le serveur décide du montant.

```ts
// supabase/functions/creer-paiement/index.ts
import { createClient } from 'npm:@supabase/supabase-js@2';

const corsHeaders = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Headers': 'authorization, x-client-info, apikey, content-type',
};

const json = (corps: unknown, status = 200) =>
  new Response(JSON.stringify(corps), {
    status,
    headers: { ...corsHeaders, 'Content-Type': 'application/json' },
  });

Deno.serve(async (req) => {
  if (req.method === 'OPTIONS') return new Response('ok', { headers: corsHeaders });

  const authHeader = req.headers.get('Authorization');
  if (!authHeader) return json({ erreur: 'Non authentifié' }, 401);

  const client = createClient(
    Deno.env.get('SUPABASE_URL')!,
    Deno.env.get('SUPABASE_ANON_KEY')!,
    { global: { headers: { Authorization: authHeader } } }
  );
  const { data: { user } } = await client.auth.getUser();
  if (!user) return json({ erreur: 'Jeton invalide' }, 401);

  const { roadmapId, provider } = await req.json();
  if (!['wave', 'orange_money', 'mtn_momo'].includes(provider)) {
    return json({ erreur: 'Opérateur inconnu' }, 400);
  }

  // Le prix vient de la base, jamais du client
  const { data: roadmap } = await client
    .from('roadmaps')
    .select('id, price_fcfa')
    .eq('id', roadmapId)
    .single();
  if (!roadmap) return json({ erreur: 'Roadmap introuvable' }, 404);

  const admin = createClient(
    Deno.env.get('SUPABASE_URL')!,
    Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!
  );
  const { data: paiement, error } = await admin
    .from('payments')
    .insert({
      profile_id: user.id,
      amount_fcfa: roadmap.price_fcfa,
      provider,
      status: 'pending',
    })
    .select('id')
    .single();
  if (error) return json({ erreur: 'Création impossible' }, 500);

  // Ici : appeler l'API de l'opérateur avec Deno.env.get('PAYMENT_API_KEY'),
  // en passant paiement.id comme référence de transaction.

  return json({ paymentId: paiement.id });
});
```

Le principe à retenir : le client **demande**, le serveur **décide**. Le prix, l'identité et le statut initial viennent tous de sources fiables.

:::quiz
Dans cette fonction, pourquoi le prix est-il relu depuis la base plutôt que reçu du client ?
- [ ] Parce que le JSON est trop lent
- [x] Parce qu'un client peut envoyer n'importe quel montant, y compris 1 FCFA
- [ ] Parce que Deno ne sait pas lire les nombres
- [ ] Parce que la RLS l'interdit
> Toute donnée venant du client est falsifiable. Le montant doit être déterminé par le serveur à partir de sources de confiance.
:::

## Recevoir un webhook de paiement

Le client ne sait pas si le paiement a réussi : c'est l'opérateur qui le sait. Il appelle ton URL (un **webhook**) quand le client a validé. Cet appel ne vient pas d'un utilisateur connecté, donc aucun jeton Supabase. Tu dois le déployer sans vérification JWT et vérifier **toi-même** la signature.

```bash
npx supabase functions deploy webhook-paiement --no-verify-jwt
```

La plupart des opérateurs signent leurs notifications avec un secret partagé (HMAC). Consulte la documentation de ton opérateur pour le nom exact de l'en-tête et l'algorithme ; le principe est toujours le même :

```ts
// supabase/functions/webhook-paiement/index.ts
import { createClient } from 'npm:@supabase/supabase-js@2';

async function signatureValide(corps: string, recue: string, secret: string) {
  const encodeur = new TextEncoder();
  const cle = await crypto.subtle.importKey(
    'raw', encodeur.encode(secret),
    { name: 'HMAC', hash: 'SHA-256' }, false, ['sign']
  );
  const signature = await crypto.subtle.sign('HMAC', cle, encodeur.encode(corps));
  const attendue = Array.from(new Uint8Array(signature))
    .map((o) => o.toString(16).padStart(2, '0')).join('');
  return attendue === recue;
}

Deno.serve(async (req) => {
  const corps = await req.text();
  const recue = req.headers.get('x-signature') ?? '';

  if (!(await signatureValide(corps, recue, Deno.env.get('PAYMENT_WEBHOOK_SECRET')!))) {
    return new Response('Signature invalide', { status: 401 });
  }

  const evenement = JSON.parse(corps);   // { reference, status }
  const admin = createClient(
    Deno.env.get('SUPABASE_URL')!,
    Deno.env.get('SUPABASE_SERVICE_ROLE_KEY')!
  );

  // Idempotent : on ne change que si le paiement est encore en attente
  const { error } = await admin
    .from('payments')
    .update({ status: evenement.status === 'success' ? 'paid' : 'failed' })
    .eq('id', evenement.reference)
    .eq('status', 'pending');

  if (error) return new Response('Erreur', { status: 500 });
  return new Response('ok');
});
```

Deux notions capitales ici. La **vérification de signature** garantit que l'appel vient réellement de l'opérateur : sans elle, n'importe qui pourrait appeler ton URL et déclarer un paiement réussi. L'**idempotence** garantit qu'un même événement reçu deux fois (les opérateurs réessayent souvent) produit le même résultat : la condition `.eq('status', 'pending')` empêche de repasser un paiement déjà traité.

Grâce au chapitre 5, dès que le statut passe à `paid`, l'écran du client se met à jour tout seul par Realtime.

> **Attention** : ne fais jamais confiance au contenu d'un webhook sans vérifier sa signature, et ne marque jamais un paiement comme payé sur la seule base de la redirection du navigateur après le paiement.

## Fonctions SQL et supabase.rpc

Pour une opération qui touche plusieurs tables et doit réussir ou échouer **en bloc**, une fonction Postgres est souvent plus simple qu'une Edge Function. Elle s'exécute dans une transaction unique.

```sql
create function public.inscrire_a_roadmap(p_roadmap_id bigint)
returns void
language plpgsql
security invoker
set search_path = ''
as $$
begin
  insert into public.enrollments (profile_id, roadmap_id)
  values ((select auth.uid()), p_roadmap_id)
  on conflict do nothing;
end;
$$;
```

Appel depuis le client :

```js
const { error } = await supabase.rpc('inscrire_a_roadmap', { p_roadmap_id: 3 });
if (error) console.error(error.message);
```

Avec `security invoker`, la fonction respecte la RLS de l'appelant. N'utilise `security definer` que si tu en as vraiment besoin, et dans ce cas contrôle les droits à l'intérieur. Une fonction qui renvoie des lignes s'appelle de la même façon et se filtre avec `.eq`, `.limit`, etc.

## Déployer et surveiller

```bash
npx supabase functions deploy creer-paiement
npx supabase functions logs creer-paiement      # ou l'onglet Logs du tableau de bord
```

Les logs (via `console.log` et `console.error`) sont ton principal outil de diagnostic. Évite d'y écrire des données personnelles ou des clés.

:::quiz
Un opérateur de paiement envoie deux fois le même webhook de succès. Quel mécanisme évite de traiter la commande deux fois ?
- [ ] Désactiver la vérification de signature
- [ ] Redémarrer la fonction
- [x] Une mise à jour conditionnelle (idempotence), par exemple uniquement si le statut est encore pending
- [ ] Supprimer la ligne de paiement
> Les webhooks sont souvent renvoyés. Un traitement idempotent produit le même résultat, que l'événement arrive une ou plusieurs fois.
:::

## Atelier guidé : paiement sécurisé pour DevRoad

Prévois deux heures. Utilise un faux opérateur : tu simuleras les appels avec `curl`.

1. Ajoute la colonne `price_fcfa bigint not null default 0` à `roadmaps` dans une migration.
2. Crée la fonction `creer-paiement` et teste-la en local avec `functions serve`.
3. Vérifie qu'un appel sans jeton renvoie 401 et qu'un opérateur inconnu renvoie 400.
4. Appelle-la depuis un bouton React avec `supabase.functions.invoke`.
5. Configure le secret `PAYMENT_WEBHOOK_SECRET` et crée `webhook-paiement`.
6. Génère une signature HMAC valide avec un petit script Node et envoie un webhook de test avec `curl`.
7. Envoie le même webhook deux fois : vérifie que la seconde fois ne change rien.
8. Envoie un webhook avec une mauvaise signature : tu dois recevoir 401.
9. Crée la fonction SQL `inscrire_a_roadmap` et appelle-la avec `rpc`.
10. Branche `SuiviPaiement` (chapitre 5) pour voir le statut passer à `paid` en direct.

Pour t'auto-évaluer : explique pourquoi le client ne peut jamais fixer le prix ni le statut, et ce que ferait un pirate si tu oubliais la signature.

## Erreurs fréquentes

- **Oublier la réponse au preflight `OPTIONS`.** Le navigateur bloque l'appel avec une erreur CORS.
- **Oublier `corsHeaders` sur les réponses d'erreur.** L'erreur réelle est masquée par une erreur CORS.
- **Faire confiance au montant envoyé par le client.** Relis toujours le prix en base.
- **Déployer un webhook sans vérifier la signature.** N'importe qui peut déclarer un paiement réussi.
- **Mettre une clé secrète dans le code ou dans une variable `NEXT_PUBLIC_`.** Utilise les secrets.
- **Utiliser la clé `service_role` avant d'avoir vérifié l'utilisateur.** Tu contournes la RLS sans contrôle.
- **Traiter un webhook sans idempotence.** Un doublon crédite deux fois.
- **Oublier `--no-verify-jwt` pour un webhook.** L'opérateur reçoit 401 et réessaie sans fin.

## Bonnes pratiques

- Le client demande, le serveur décide : prix, droits et statuts se calculent côté serveur.
- Vérifie l'identité avec `getUser()` dès le début de chaque fonction protégée.
- Garde les fonctions petites et centrées sur une seule responsabilité.
- Renvoie des codes HTTP justes : 400, 401, 404, 500.
- Réponds vite aux webhooks (quelques secondes) pour éviter les nouvelles tentatives inutiles.
- Utilise `rpc` pour les opérations purement base de données, et les Edge Functions pour les services externes.

## À retenir

- Une Edge Function est du code serveur TypeScript exécuté sur Deno, appelé en HTTPS ou avec `functions.invoke`.
- Elle sert aux secrets, aux webhooks et à toute logique qui ne doit pas être falsifiable.
- CORS exige de répondre au preflight et d'ajouter les en-têtes à chaque réponse.
- Les clés se stockent avec `supabase secrets set` et se lisent avec `Deno.env.get`.
- Un webhook se déploie avec `--no-verify-jwt`, mais doit vérifier sa signature et être idempotent.
- Les fonctions SQL appelées par `supabase.rpc` offrent des opérations atomiques proches des données.
