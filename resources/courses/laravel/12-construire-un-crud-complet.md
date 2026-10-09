---
title: Construire un CRUD complet
minutes: 180
level: intermediate
---

## Ce que tu vas apprendre

Un **CRUD** (*Create, Read, Update, Delete*) est la brique de base de presque toute application : créer, lire, modifier, supprimer des éléments. C'est le moment d'assembler tout ce que tu as appris dans les chapitres précédents pour construire une fonctionnalité **complète et sécurisée** de bout en bout.

À la fin du chapitre, tu seras capable de :

- planifier un CRUD dans le bon ordre, du schéma de la base jusqu'à l'interface ;
- écrire la migration, le modèle, la policy, les Form Requests, le contrôleur et les routes d'une ressource ;
- construire les pages React de liste, de création et de modification en partageant un formulaire ;
- afficher des messages de succès et gérer la suppression avec confirmation ;
- écrire des tests qui vérifient le fonctionnement **et** la sécurité ;
- relire ton travail avec une liste de contrôle.

On construit les **fiches mémo** de DevRoad : des notes courtes (titre, contenu, favori) que chaque utilisateur garde pour lui. Prévois trois heures : c'est le chapitre le plus long, mais aussi celui où tout prend sens.

## Le plan de construction

Ne commence jamais par l'interface. Un CRUD se construit **de l'intérieur vers l'extérieur**, dans cet ordre :

1. **La base** : la migration décrit la table.
2. **Le modèle** : `$fillable`, `$casts`, relations.
3. **La policy** : qui a le droit de faire quoi.
4. **Les Form Requests** : quelles données sont acceptées.
5. **Le contrôleur** : le chef d'orchestre.
6. **Les routes** : les adresses.
7. **Les pages** : liste, création, modification.
8. **Les tests** : la preuve que tout marche, et que rien n'est ouvert à tort.

Cet ordre a une raison : chaque étape s'appuie sur la précédente, et tu peux **vérifier chacune** (avec `tinker`, `route:list`, un test) avant d'avancer. Si tu commençais par l'interface, tu découvrirais les problèmes tout à la fin, quand ils sont les plus coûteux.

Génère d'abord les fichiers d'un coup, puis remplis-les :

```bash
php artisan make:model Memo -m
php artisan make:policy MemoPolicy --model=Memo
php artisan make:request StoreMemoRequest
php artisan make:request UpdateMemoRequest
php artisan make:controller MemoController --resource
```

## 1. La base et le modèle

La migration (fichier `..._create_memos_table.php`) :

```php
Schema::create('memos', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->string('title');
    $table->longText('content');
    $table->boolean('is_favorite')->default(false);
    $table->timestamps();
});
```

Le modèle `app/Models/Memo.php` :

```php
class Memo extends Model
{
    protected $fillable = ['title', 'content', 'is_favorite'];

    protected $casts = [
        'is_favorite' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
```

Observe un détail de sécurité : **`user_id` n'est pas dans `$fillable`**. Le propriétaire ne doit jamais venir d'un formulaire. On le renseigne depuis la session, en créant la fiche via `$request->user()->memos()`. Sans cela, un utilisateur pourrait fabriquer une fiche au nom d'un autre.

N'oublie pas la relation inverse dans `User` :

```php
public function memos(): HasMany
{
    return $this->hasMany(Memo::class);
}
```

Lance `php artisan migrate`, puis vérifie la table dans phpMyAdmin et une création dans `tinker`.

:::quiz
Pourquoi `user_id` ne figure-t-il pas dans `$fillable` du modèle Memo ?
- [ ] Parce que la colonne n'existe pas
- [x] Parce que le propriétaire doit venir de la session, jamais d'un formulaire falsifiable
- [ ] Parce que Laravel le remplit automatiquement
- [ ] Parce que c'est une clé primaire
> Si user_id était remplissable en masse, un utilisateur pourrait attribuer une fiche à quelqu'un d'autre. On le renseigne via la relation de l'utilisateur connecté.
:::

## 2. La policy

Chaque fiche appartient à un utilisateur : lui seul la voit et la modifie.

```php
class MemoPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Memo $memo): bool
    {
        return $memo->user_id === $user->id;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Memo $memo): bool
    {
        return $memo->user_id === $user->id;
    }

    public function delete(User $user, Memo $memo): bool
    {
        return $memo->user_id === $user->id;
    }
}
```

La liaison `Memo` → `MemoPolicy` est automatique. Cette petite classe va protéger toutes les routes qui suivent contre les accès à la donnée d'un autre.

## 3. Les Form Requests

Les deux requêtes ont des règles très proches. On les écrit explicitement pour rester lisible.

```php
class StoreMemoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:150'],
            'content' => ['required', 'string', 'max:20000'],
            'is_favorite' => ['sometimes', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'title.required' => 'Donne un titre à ta fiche.',
            'content.required' => 'Écris le contenu de ta fiche.',
        ];
    }
}
```

`UpdateMemoRequest` reprend les mêmes règles ; la différence typique serait d'utiliser `sometimes` pour permettre des modifications partielles. Garde-les séparées : elles évolueront chacune à leur rythme.

## 4. Le contrôleur

Voici le contrôleur complet. Lis-le lentement : chaque ligne applique un principe déjà vu.

```php
class MemoController extends Controller
{
    public function index(Request $request)
    {
        $memos = $request->user()
            ->memos()
            ->latest('updated_at')
            ->paginate(12, ['id', 'title', 'is_favorite', 'updated_at']);

        return Inertia::render('Memos/Index', ['memos' => $memos]);
    }

    public function create()
    {
        $this->authorize('create', Memo::class);

        return Inertia::render('Memos/Create');
    }

    public function store(StoreMemoRequest $request)
    {
        $memo = $request->user()->memos()->create($request->validated());

        return redirect()
            ->route('memos.show', $memo)
            ->with('success', 'Fiche créée.');
    }

    public function show(Memo $memo)
    {
        $this->authorize('view', $memo);

        return Inertia::render('Memos/Show', ['memo' => $memo]);
    }

    public function edit(Memo $memo)
    {
        $this->authorize('update', $memo);

        return Inertia::render('Memos/Edit', ['memo' => $memo]);
    }

    public function update(UpdateMemoRequest $request, Memo $memo)
    {
        $this->authorize('update', $memo);

        $memo->update($request->validated());

        return redirect()
            ->route('memos.show', $memo)
            ->with('success', 'Fiche mise à jour.');
    }

    public function destroy(Memo $memo)
    {
        $this->authorize('delete', $memo);

        $memo->delete();

        return redirect()
            ->route('memos.index')
            ->with('success', 'Fiche supprimée.');
    }
}
```

Fais l'inventaire de ce qui s'applique ici :

- **liste filtrée** sur l'utilisateur connecté, paginée, avec seulement les colonnes utiles ;
- **autorisation** dans chaque méthode qui touche à une fiche précise ;
- **validation** par les Form Requests, et `validated()` pour écrire ;
- **création via la relation**, donc propriétaire fiable ;
- **redirection** avec message flash après chaque écriture.

## 5. Les routes

Une ligne, protégée par `auth` :

```php
Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('memos', MemoController::class);
});
```

Vérifie avec `php artisan route:list --name=memos` : tu dois voir les sept routes, de `memos.index` à `memos.destroy`.

:::quiz
Dans le contrôleur, pourquoi crée-t-on la fiche avec `$request->user()->memos()->create(...)` ?
- [ ] C'est plus rapide
- [ ] Pour éviter la validation
- [x] Pour que le propriétaire soit celui de la session et non une valeur envoyée par le client
- [ ] Parce que create() n'existe pas sur le modèle
> Créer via la relation renseigne user_id depuis l'utilisateur connecté : impossible de créer une fiche au nom de quelqu'un d'autre.
:::

## 6. Les pages React

Trois pages, mais **un seul formulaire** partagé entre création et modification, pour ne pas dupliquer le code.

### Le formulaire réutilisable

`resources/js/Components/Memos/MemoForm.jsx` :

```jsx
export default function MemoForm({ form, onSubmit, submitLabel, processingLabel }) {
    return (
        <form onSubmit={onSubmit} noValidate>
            <label htmlFor="title">Titre</label>
            <input
                id="title"
                value={form.data.title}
                onChange={(e) => form.setData('title', e.target.value)}
                aria-invalid={Boolean(form.errors.title)}
            />
            {form.errors.title && <p role="alert">{form.errors.title}</p>}

            <label htmlFor="content">Contenu</label>
            <textarea
                id="content"
                rows={10}
                value={form.data.content}
                onChange={(e) => form.setData('content', e.target.value)}
                aria-invalid={Boolean(form.errors.content)}
            />
            {form.errors.content && <p role="alert">{form.errors.content}</p>}

            <label>
                <input
                    type="checkbox"
                    checked={form.data.is_favorite}
                    onChange={(e) => form.setData('is_favorite', e.target.checked)}
                />
                Ajouter aux favoris
            </label>

            <button type="submit" disabled={form.processing}>
                {form.processing ? processingLabel : submitLabel}
            </button>
        </form>
    );
}
```

### Création et modification

```jsx
// Pages/Memos/Create.jsx
import { useForm } from '@inertiajs/react';
import MemoForm from '@/Components/Memos/MemoForm';

export default function Create() {
    const form = useForm({ title: '', content: '', is_favorite: false });

    return (
        <MemoForm
            form={form}
            onSubmit={(e) => { e.preventDefault(); form.post('/memos'); }}
            submitLabel="Créer la fiche"
            processingLabel="Création…"
        />
    );
}
```

```jsx
// Pages/Memos/Edit.jsx
import { useForm } from '@inertiajs/react';
import MemoForm from '@/Components/Memos/MemoForm';

export default function Edit({ memo }) {
    const form = useForm({
        title: memo.title,
        content: memo.content,
        is_favorite: memo.is_favorite,
    });

    return (
        <MemoForm
            form={form}
            onSubmit={(e) => { e.preventDefault(); form.patch(`/memos/${memo.id}`); }}
            submitLabel="Enregistrer"
            processingLabel="Enregistrement…"
        />
    );
}
```

La seule différence entre les deux pages : les valeurs de départ et la méthode d'envoi (`post` ou `patch`). Tout le reste est mutualisé.

### La liste, avec suppression

```jsx
// Pages/Memos/Index.jsx
import { Link, router } from '@inertiajs/react';

export default function Index({ memos }) {
    function supprimer(memo) {
        if (window.confirm(`Supprimer « ${memo.title} » ?`)) {
            router.delete(`/memos/${memo.id}`, { preserveScroll: true });
        }
    }

    if (memos.data.length === 0) {
        return (
            <p>
                Aucune fiche pour l'instant. <Link href="/memos/create">Écris la première.</Link>
            </p>
        );
    }

    return (
        <ul>
            {memos.data.map((memo) => (
                <li key={memo.id}>
                    <Link href={`/memos/${memo.id}`}>{memo.title}</Link>
                    <button type="button" onClick={() => supprimer(memo)}>Supprimer</button>
                </li>
            ))}
        </ul>
    );
}
```

Remarque l'**état vide** : une liste sans élément n'affiche pas une page blanche, mais un message utile avec une action. C'est un détail qui change la qualité perçue de l'application.

## 7. Les tests

Un CRUD sans test est un CRUD fragile. Deux familles de tests : ce qui doit **marcher**, et ce qui doit être **refusé**.

```php
class MemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_utilisateur_cree_une_fiche(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('memos.store'), ['title' => 'Routes', 'content' => 'Notes sur les routes.'])
            ->assertRedirect();

        $this->assertDatabaseHas('memos', ['title' => 'Routes', 'user_id' => $user->id]);
    }

    public function test_un_titre_vide_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('memos.store'), ['title' => '', 'content' => 'x'])
            ->assertSessionHasErrors('title');
    }

    public function test_on_ne_voit_pas_la_fiche_d_un_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $memo = $proprietaire->memos()->create(['title' => 'Privée', 'content' => 'Secret']);

        $this->actingAs($intrus)->get(route('memos.show', $memo))->assertForbidden();
    }

    public function test_on_ne_supprime_pas_la_fiche_d_un_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $memo = $proprietaire->memos()->create(['title' => 'Privée', 'content' => 'Secret']);

        $this->actingAs($intrus)->delete(route('memos.destroy', $memo))->assertForbidden();

        $this->assertDatabaseHas('memos', ['id' => $memo->id]);
    }
}
```

Lance-les avec `php artisan test --filter=MemoTest`. Le dernier test est le plus précieux : il vérifie non seulement la réponse 403, mais aussi que la fiche **existe toujours**.

> **Astuce** : `RefreshDatabase` remet la base à zéro entre chaque test, donc tes tests ne se contaminent jamais. Écris un test pour chaque règle de sécurité, avant qu'une régression ne te le rappelle.

:::quiz
Quel test est le plus précieux pour valider la suppression d'une fiche par un intrus ?
- [ ] Vérifier uniquement que la page répond
- [x] Vérifier la réponse 403 ET que la fiche existe toujours en base
- [ ] Vérifier que le bouton est masqué
- [ ] Vérifier que l'URL est correcte
> Une réponse 403 ne prouve rien si la donnée a quand même été supprimée. Il faut contrôler les deux.
:::

## La liste de contrôle d'un CRUD

Avant de déclarer ton CRUD terminé, passe en revue :

- la **migration** a des clés étrangères, des valeurs par défaut et un `down()` ;
- le **modèle** déclare `$fillable` (sans `user_id`) et `$casts` ;
- chaque méthode du contrôleur qui touche une fiche appelle **`authorize`** ;
- l'écriture passe par **`validated()`**, pas par `all()` ;
- la création se fait **via la relation** de l'utilisateur ;
- la liste est **paginée** et filtrée sur l'utilisateur ;
- chaque écriture se termine par une **redirection** avec message ;
- l'interface gère l'**état vide**, l'**erreur** et l'**envoi en cours** ;
- les **tests** couvrent le cas normal, la validation et l'accès d'un intrus.

## Atelier guidé : à toi de jouer

Compte deux heures. Construis le CRUD des **tags** de l'utilisateur, en suivant exactement le plan de ce chapitre, sans copier-coller le code des fiches.

1. Migration `tags` : `user_id`, `name`, `slug`, horodatages, unicité sur `user_id` et `slug`.
2. Modèle `Tag` avec `$fillable` (sans `user_id`) et relation `user`.
3. Policy `TagPolicy` : propriétaire seulement.
4. Form Requests : `name` obligatoire (50 caractères maximum), `slug` unique pour l'utilisateur (avec `ignore` en modification).
5. Contrôleur `TagController` avec `index`, `store`, `update`, `destroy` (pas de `show` ni `create`).
6. Route : `Route::resource('tags', TagController::class)->except(['show', 'create', 'edit']);`.
7. Page de liste avec formulaire d'ajout en haut, modification et suppression en ligne.
8. Tests : création, nom vide refusé, slug en double refusé, intrus refusé sur `update` et `destroy`.

Pour t'auto-évaluer, passe ton travail à la liste de contrôle ci-dessus : chaque case non cochée est un oubli à corriger.

## Erreurs fréquentes

- **Commencer par l'interface.** Les problèmes de base de données arrivent trop tard.
- **Oublier `authorize` dans une seule méthode.** C'est suffisant pour ouvrir une faille.
- **Mettre `user_id` dans `$fillable`.** Un utilisateur peut créer du contenu au nom d'un autre.
- **Dupliquer le formulaire de création et de modification.** Mutualise-le dans un composant.
- **Pas d'état vide ni d'état d'erreur.** L'utilisateur reste face à une page blanche.
- **Ne tester que le cas heureux.** Les failles se cachent dans les cas refusés.

## Bonnes pratiques

- Construis dans l'ordre : base, modèle, policy, requêtes, contrôleur, routes, pages, tests.
- Vérifie chaque étape avant de passer à la suivante.
- Garde le contrôleur mince : validation en Form Request, droits en Policy.
- Crée toujours via la relation de l'utilisateur connecté.
- Écris au moins un test d'accès refusé par ressource.
- Relis la liste de contrôle avant chaque livraison.

## À retenir

- Un CRUD se construit de l'intérieur vers l'extérieur : base, modèle, policy, requêtes, contrôleur, routes, pages, tests.
- Le propriétaire (`user_id`) vient de la session, jamais du formulaire.
- Chaque action sur une fiche précise est protégée par `authorize`, et chaque écriture par `validated()`.
- Un formulaire partagé évite de dupliquer création et modification.
- Après chaque écriture : rediriger avec un message flash.
- Les tests doivent prouver à la fois que la fonctionnalité marche et qu'un intrus ne peut rien faire.
