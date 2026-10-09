---
title: Policies et autorisation
minutes: 120
level: intermediate
---

## Ce que tu vas apprendre

Se connecter prouve **qui** tu es. L'autorisation décide **ce que tu as le droit de faire**. Ce sont deux questions différentes, et confondre les deux est l'une des failles les plus répandues dans les applications web.

À la fin du chapitre, tu seras capable de :

- distinguer **authentification** et **autorisation** ;
- expliquer la faille IDOR et comment elle se produit ;
- créer une **Policy** pour un modèle et en écrire les méthodes ;
- appliquer une policy dans un contrôleur, dans une route et dans l'interface ;
- choisir entre une réponse 403 et une réponse 404 ;
- tester qu'un utilisateur ne peut pas toucher aux données d'un autre.

On sécurise ici les roadmaps de DevRoad : chacun ne doit voir et modifier que les siennes.

## Authentification et autorisation

Prends l'exemple d'un hôtel :

- l'**authentification**, c'est la réception qui vérifie ton identité et te donne une carte ;
- l'**autorisation**, c'est ce que ta carte ouvre : ta chambre, pas celle du voisin.

Dans Laravel :

- le middleware `auth` (et Breeze) gèrent l'**authentification** : l'utilisateur est-il connecté ?
- les **policies** et les **gates** gèrent l'**autorisation** : cet utilisateur peut-il faire *cette* action sur *cette* ressource ?

Une application où tout le monde est connecté mais où chacun peut tout faire n'est pas sécurisée. Le piège est subtil : tout semble fonctionner, parce qu'en usage normal les utilisateurs ne vont que sur leurs propres pages. Le problème apparaît quand quelqu'un essaie autre chose.

## La faille IDOR : comprendre le danger

Imagine que ta roadmap soit à l'adresse `/roadmaps/12`. Un utilisateur curieux change le chiffre dans la barre d'adresse : `/roadmaps/13`. Si ton code se contente de retrouver la roadmap numéro 13 et de l'afficher, il vient de lire les données d'un autre. C'est une **IDOR** (*Insecure Direct Object Reference*, référence directe non sécurisée à un objet).

```php
// Code vulnérable : n'importe quel utilisateur connecté peut lire n'importe quelle roadmap
public function show(Roadmap $roadmap)
{
    return Inertia::render('Roadmaps/Show', ['roadmap' => $roadmap]);
}
```

La liaison de modèle (chapitre sur les routes) charge la roadmap, mais ne vérifie **pas** à qui elle appartient. Le même problème existe pour `update` et `destroy` : un utilisateur pourrait **modifier ou supprimer** les données d'un autre. C'est exactement ce que les policies empêchent.

:::quiz
Un utilisateur connecté change l'identifiant dans l'adresse pour consulter les données d'un autre utilisateur. Comment appelle-t-on cette faille ?
- [ ] Une injection SQL
- [ ] Une faille XSS
- [x] Une IDOR (référence directe non sécurisée à un objet)
- [ ] Une attaque CSRF
> L'IDOR consiste à accéder à une ressource en modifiant son identifiant, parce que l'application ne vérifie pas que l'utilisateur en est le propriétaire.
:::

## Créer une Policy

Une **policy** est une classe qui regroupe toutes les règles d'autorisation d'un modèle. Génère-la :

```bash
php artisan make:policy RoadmapPolicy --model=Roadmap
```

Laravel crée `app/Policies/RoadmapPolicy.php` avec une méthode par action. Complète-la :

```php
<?php

namespace App\Policies;

use App\Models\Roadmap;
use App\Models\User;

class RoadmapPolicy
{
    // Peut-il voir la liste ? La liste est déjà filtrée sur ses propres roadmaps.
    public function viewAny(User $user): bool
    {
        return true;
    }

    // Peut-il voir CETTE roadmap ?
    public function view(User $user, Roadmap $roadmap): bool
    {
        return $this->owns($user, $roadmap);
    }

    // Peut-il en créer ?
    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Roadmap $roadmap): bool
    {
        return $this->owns($user, $roadmap);
    }

    public function delete(User $user, Roadmap $roadmap): bool
    {
        return $this->owns($user, $roadmap);
    }

    private function owns(User $user, Roadmap $roadmap): bool
    {
        return $roadmap->user_id === $user->id;
    }
}
```

Chaque méthode reçoit l'**utilisateur connecté** en premier paramètre, puis le modèle concerné, et retourne `true` (autorisé) ou `false` (interdit). Une petite méthode privée `owns` évite de répéter la comparaison.

Le lien entre `Roadmap` et `RoadmapPolicy` est **automatique**, grâce à la convention de nommage : `Roadmap` → `RoadmapPolicy`. Tu n'as rien à enregistrer.

> **Attention** : compare toujours les identifiants avec `===` entre deux valeurs du même type. Un `user_id` lu depuis la base et un `id` d'utilisateur sont des entiers : si l'un était une chaîne, la comparaison stricte échouerait.

## Appliquer une policy

Il existe plusieurs façons d'utiliser la policy. Retiens-les toutes, car tu les rencontreras.

### Dans le contrôleur

Le contrôleur de base de Laravel fournit la méthode `authorize` :

```php
public function show(Roadmap $roadmap)
{
    $this->authorize('view', $roadmap);

    return Inertia::render('Roadmaps/Show', ['roadmap' => $roadmap]);
}

public function update(UpdateRoadmapRequest $request, Roadmap $roadmap)
{
    $this->authorize('update', $roadmap);

    $roadmap->update($request->validated());

    return back();
}

public function destroy(Roadmap $roadmap)
{
    $this->authorize('delete', $roadmap);

    $roadmap->delete();

    return redirect()->route('roadmaps.index');
}
```

Si la policy retourne `false`, Laravel répond automatiquement par une erreur **403** (interdit) : ton code n'a pas à s'en occuper.

Pour les actions qui n'ont pas de modèle (comme `create`), passe la classe :

```php
$this->authorize('create', Roadmap::class);
```

### Dans la route

Tu peux aussi protéger au niveau de la route, avec le middleware `can` :

```php
Route::get('/roadmaps/{roadmap}', [RoadmapController::class, 'show'])
    ->middleware('can:view,roadmap');
```

Le premier mot est l'action de la policy, le second le nom du paramètre de la route.

### Partout dans le code

Sur l'utilisateur, on peut interroger les droits à tout moment :

```php
if ($request->user()->can('update', $roadmap)) {
    // ...
}
```

### Dans l'interface React

Le navigateur doit savoir quels boutons afficher. Évite de dupliquer la règle côté JavaScript : le serveur calcule les droits et les transmet à la page.

```php
return Inertia::render('Roadmaps/Show', [
    'roadmap' => $roadmap,
    'can' => [
        'update' => $request->user()->can('update', $roadmap),
        'delete' => $request->user()->can('delete', $roadmap),
    ],
]);
```

```jsx
export default function Show({ roadmap, can }) {
    return (
        <>
            <h1>{roadmap.title}</h1>
            {can.update && <a href={`/roadmaps/${roadmap.id}/edit`}>Modifier</a>}
            {can.delete && <button type="button">Supprimer</button>}
        </>
    );
}
```

> **À retenir** : cacher un bouton est une question de confort. Seul le contrôle côté serveur protège réellement les données. Même si un bouton est masqué, le serveur doit refuser l'action si la requête est envoyée quand même.

:::quiz
Un bouton « Supprimer » est masqué pour un utilisateur qui n'est pas propriétaire. Cela suffit-il à protéger la donnée ?
- [ ] Oui, l'utilisateur ne peut plus rien faire
- [x] Non, le serveur doit aussi refuser l'action avec une policy
- [ ] Oui, si la page est en HTTPS
- [ ] Non, mais seulement sur mobile
> Masquer un bouton ne bloque pas une requête envoyée à la main. Le contrôle d'autorisation doit toujours avoir lieu côté serveur.
:::

## 403 ou 404 ?

Quand un utilisateur demande une ressource qui n'est pas la sienne, que lui répondre ?

- **403 Interdit** : « cette ressource existe, mais tu n'y as pas droit ». C'est clair, mais cela révèle que la roadmap numéro 13 existe.
- **404 Introuvable** : « cette ressource n'existe pas (pour toi) ». Cela ne donne aucune information à un curieux.

Pour les données privées, beaucoup d'applications préfèrent le 404. Laravel permet de le faire depuis la policy :

```php
use Illuminate\Auth\Access\Response;

public function view(User $user, Roadmap $roadmap): bool|Response
{
    return $roadmap->user_id === $user->id
        ? true
        : Response::denyAsNotFound();
}
```

Tu choisis selon le contexte : un 403 est parfait pour les actions (« tu n'es pas admin »), un 404 est plus discret pour les ressources privées.

## Super-administrateur et méthode before

Parfois, un administrateur doit pouvoir tout faire. Plutôt que d'ajouter `|| $user->isAdmin()` dans chaque méthode, la policy offre `before`, qui s'exécute en premier :

```php
public function before(User $user, string $ability): ?bool
{
    if ($user->role === 'admin') {
        return true;   // l'admin passe partout
    }

    return null;       // sinon, on laisse les autres méthodes décider
}
```

Retourner `null` est essentiel : cela signifie « je ne décide pas, passe aux règles normales ». Si tu retournais `false`, tu bloquerais tout le monde.

## Les Gates, pour les droits sans modèle

Une policy est liée à un modèle. Pour une règle générale (« peut-il accéder à l'espace professeur ? »), on utilise une **gate**, déclarée dans `AppServiceProvider` :

```php
use Illuminate\Support\Facades\Gate;

public function boot(): void
{
    Gate::define('access-teacher-area', fn (User $user) => $user->role === 'teacher');
}
```

```php
Gate::authorize('access-teacher-area');
```

Règle de choix : un droit sur **une ressource précise** (modifier cette roadmap) relève d'une policy ; un droit **général** (entrer dans cet espace) relève d'une gate.

## Tester les autorisations

Une règle de sécurité non testée est une règle qui finira par casser sans que personne ne s'en aperçoive. Les tests sont simples et courts :

```php
public function test_un_utilisateur_ne_peut_pas_modifier_la_roadmap_d_un_autre(): void
{
    $proprietaire = User::factory()->create();
    $intrus = User::factory()->create();
    $roadmap = $proprietaire->roadmaps()->create(['title' => 'Privée']);

    $this->actingAs($intrus)
        ->patch(route('roadmaps.update', $roadmap), ['title' => 'Piratée'])
        ->assertForbidden();

    $this->assertSame('Privée', $roadmap->fresh()->title);
}
```

Ce test fait deux choses : il vérifie la **réponse** (403), puis il s'assure que la **donnée n'a pas changé**. Le second contrôle est le plus important : une réponse 403 qui aurait quand même modifié la base serait un bug grave.

:::quiz
Que doit retourner la méthode before() d'une policy quand l'utilisateur n'est pas administrateur ?
- [ ] false
- [ ] true
- [x] null
- [ ] Une exception
> Retourner null laisse les autres méthodes de la policy décider. false bloquerait tout le monde, true autoriserait tout le monde.
:::

## Atelier guidé : sécuriser les roadmaps

Compte une heure. Utilise les roadmaps et le contrôleur du chapitre sur les contrôleurs.

1. Génère la policy : `php artisan make:policy RoadmapPolicy --model=Roadmap`.
2. Implémente `view`, `update` et `delete` : le propriétaire seulement. `viewAny` et `create` : tout utilisateur connecté.
3. Dans `RoadmapController`, ajoute `$this->authorize(...)` dans `show`, `update` et `destroy`.
4. Crée deux utilisateurs et une roadmap pour le premier. Connecte-toi avec le second et visite `/roadmaps/{id}` de la roadmap du premier : tu dois obtenir un 403.
5. Change la policy pour répondre en 404 avec `Response::denyAsNotFound()` pour `view`. Compare.
6. Passe un tableau `can` à la page `Show` et affiche les boutons selon les droits.
7. Écris deux tests : l'intrus reçoit 403 sur `update`, et la donnée reste inchangée.
8. Ajoute la méthode `before` pour un rôle `admin` et teste qu'un administrateur peut désormais voir la roadmap.

Pour t'auto-évaluer : explique la différence entre le middleware `auth` et une policy, avec un exemple chacun.

## Erreurs fréquentes

- **Se croire protégé par `auth`.** Il garantit seulement que l'utilisateur est connecté, pas qu'il est propriétaire.
- **Oublier `authorize` dans une méthode.** Une seule méthode oubliée suffit pour ouvrir une faille.
- **Ne masquer qu'un bouton.** Le contrôle serveur est indispensable.
- **Retourner `false` dans `before`.** Cela bloque tout, y compris les propriétaires.
- **Filtrer la liste mais pas le détail.** Une liste filtrée sur l'utilisateur ne protège pas `/roadmaps/13`.
- **Ne pas tester les cas négatifs.** On teste volontiers que ça marche, rarement que l'accès est refusé.

## Bonnes pratiques

- Applique le principe du moindre privilège : par défaut, rien n'est permis.
- Une policy par modèle, avec une méthode par action.
- Appelle `authorize` dans chaque méthode qui touche à une ressource particulière.
- Calcule les droits côté serveur et transmets-les à l'interface.
- Choisis le 404 pour les ressources privées, le 403 pour les actions refusées.
- Écris au moins un test « un autre utilisateur ne peut pas » par ressource sensible.

## À retenir

- L'authentification établit l'identité ; l'autorisation décide des droits.
- Une IDOR survient quand on retrouve une ressource par son identifiant sans vérifier son propriétaire.
- Une policy regroupe les règles d'un modèle ; la convention `Modèle` → `ModèlePolicy` la relie automatiquement.
- On l'applique avec `$this->authorize`, le middleware `can`, ou `$user->can(...)`.
- Masquer un bouton ne suffit jamais : le serveur doit refuser l'action.
- `before` (qui retourne `null` par défaut) gère les administrateurs ; une gate sert pour les droits sans modèle.
