---
title: Validation avec Form Requests
minutes: 120
level: intermediate
---

## Ce que tu vas apprendre

Tout ce qui vient de l'extérieur est **suspect** : un formulaire mal rempli, un champ oublié, une valeur absurde, ou une tentative volontaire de piratage. La validation est ton filtre d'entrée. Bien faite, elle protège ta base de données et guide l'utilisateur avec des messages clairs.

À la fin du chapitre, tu seras capable de :

- expliquer pourquoi la validation côté serveur est indispensable ;
- valider rapidement avec `$request->validate()` ;
- extraire la validation dans une **Form Request** dédiée ;
- utiliser les règles courantes (`required`, `max`, `unique`, `exists`, `in`, `array`) ;
- personnaliser les messages d'erreur en français ;
- valider des modifications sans bloquer l'enregistrement courant (`unique` avec `ignore`) ;
- afficher les erreurs dans un formulaire React avec Inertia.

## Pourquoi valider côté serveur ?

Tu pourrais penser : « mon formulaire React vérifie déjà que le titre est rempli ». C'est utile pour l'**expérience** de l'utilisateur, mais **jamais suffisant pour la sécurité**. Un formulaire côté navigateur se contourne en deux secondes : avec les outils de développement, ou en envoyant la requête depuis un script. Le serveur ne doit jamais supposer que les données sont bonnes.

La règle est donc simple :

- **côté navigateur** : confort (retour immédiat, moins de requêtes inutiles) ;
- **côté serveur** : obligation (c'est la vraie barrière).

Laravel rend cette obligation agréable : tu décris les règles, il fait le reste. Si une règle échoue, il **redirige automatiquement** vers la page précédente avec les erreurs et les anciennes valeurs saisies.

## La validation rapide

La façon la plus simple de valider se fait directement dans le contrôleur :

```php
public function store(Request $request)
{
    $data = $request->validate([
        'title' => ['required', 'string', 'max:255'],
        'technology' => ['required', 'string'],
        'description' => ['nullable', 'string', 'max:2000'],
    ]);

    $roadmap = $request->user()->roadmaps()->create($data);

    return redirect()->route('roadmaps.show', $roadmap);
}
```

Chaque champ reçoit une liste de **règles**. Si toutes passent, `validate` retourne **uniquement** les champs validés dans `$data`. Si l'une échoue, l'exécution s'arrête avant même `create` : l'utilisateur est renvoyé au formulaire avec ses erreurs.

Voici les règles que tu utiliseras le plus :

| Règle | Signification |
| --- | --- |
| `required` | Le champ est obligatoire |
| `nullable` | Le champ peut être vide ou absent |
| `string` / `integer` / `boolean` / `array` | Le type attendu |
| `min:3` / `max:255` | Longueur (texte) ou valeur (nombre) minimale / maximale |
| `email` | Doit être une adresse e-mail valide |
| `in:todo,in_progress,completed` | Doit faire partie de la liste |
| `exists:roadmaps,id` | La valeur doit exister dans la table indiquée |
| `unique:tags,slug` | La valeur ne doit pas déjà exister dans la table |
| `confirmed` | Doit correspondre au champ `xxx_confirmation` (mot de passe) |
| `date` / `after:today` | Date valide, postérieure à aujourd'hui |

> **Attention** : utilise toujours `$data` (le résultat de `validate`) pour enregistrer, jamais `$request->all()`. Avec `all()`, tu récupères aussi les champs que tu n'as pas validés, y compris ceux qu'un utilisateur malveillant a ajoutés.

:::quiz
Pourquoi la validation côté navigateur ne suffit-elle pas ?
- [ ] Elle est trop lente
- [x] Elle peut être contournée : le serveur ne doit jamais faire confiance aux données reçues
- [ ] Elle ne fonctionne pas avec React
- [ ] Elle coûte plus cher
> Un formulaire côté navigateur améliore le confort, mais n'importe qui peut envoyer une requête sans passer par lui. Seule la validation serveur est fiable.
:::

## Extraire dans une Form Request

Quand les règles s'allongent, le contrôleur devient illisible. La **Form Request** est une classe dédiée à la validation d'un formulaire. Génère-la :

```bash
php artisan make:request StoreRoadmapRequest
```

Le fichier `app/Http/Requests/StoreRoadmapRequest.php` contient deux méthodes :

```php
<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreRoadmapRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Qui a le droit d'envoyer ce formulaire ?
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'technology' => ['required', 'string'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }
}
```

- **`authorize()`** répond à la question « cette personne a-t-elle le droit d'utiliser ce formulaire ? ». Retourne `false` et Laravel répond par une erreur 403.
- **`rules()`** retourne le tableau des règles, exactement comme dans `validate`.

Pour l'utiliser, il suffit de **typer le paramètre** du contrôleur. Laravel exécute la validation avant même d'entrer dans ta méthode :

```php
public function store(StoreRoadmapRequest $request)
{
    // Si on arrive ici, les données sont déjà valides.
    $roadmap = $request->user()->roadmaps()->create($request->validated());

    return redirect()->route('roadmaps.show', $roadmap);
}
```

`$request->validated()` retourne uniquement les champs ayant passé les règles. Le contrôleur est devenu court et ne s'occupe plus que de coordonner. Tu retrouves le principe du chapitre sur les contrôleurs : chaque fichier a une seule responsabilité.

> **Astuce** : crée en général deux Form Requests par ressource, `StoreXRequest` et `UpdateXRequest`. Leurs règles sont proches mais pas identiques (une mise à jour a souvent des règles `sometimes` ou un `unique` à adapter).

## Des règles adaptées à chaque situation

### Les listes de valeurs autorisées

Le statut d'une étape ne peut prendre que quelques valeurs. Utilise `Rule::in` pour être sûr de ne jamais enregistrer une valeur imprévue :

```php
use Illuminate\Validation\Rule;
use App\Models\RoadmapStep;

public function rules(): array
{
    return [
        'status' => ['required', Rule::in(RoadmapStep::STATUSES)],
        'position' => ['required', 'integer', 'min:1'],
    ];
}
```

Ici, la liste vient de la constante du modèle : si on ajoute un statut un jour, la validation suit automatiquement.

### L'unicité, avec une exception à la modification

Prenons un tag dont le `slug` doit être unique **pour chaque utilisateur**. À la création, c'est simple. Mais en modification, le tag que tu édites existe déjà : si tu laisses `unique` tel quel, il se bloque lui-même. Il faut ignorer l'enregistrement courant :

```php
public function rules(): array
{
    return [
        'name' => ['required', 'string', 'max:50'],
        'slug' => [
            'required',
            'string',
            Rule::unique('tags', 'slug')
                ->where('user_id', $this->user()->id)   // unique seulement parmi MES tags
                ->ignore($this->route('tag')),           // sauf le tag que j'édite
        ],
    ];
}
```

C'est un classique : sans `ignore`, impossible de sauvegarder un tag sans changer son nom.

### Valider une liste

Pour un formulaire qui envoie un tableau, on utilise la notation avec une étoile :

```php
'tag_ids' => ['nullable', 'array'],
'tag_ids.*' => ['integer', 'exists:tags,id'],
```

La première règle dit que `tag_ids` est un tableau. La seconde s'applique à **chaque élément** : chacun doit être un entier qui existe dans la table `tags`.

### Préparer les données avant validation

Parfois, il faut nettoyer la saisie avant de la contrôler, par exemple retirer les espaces ou fabriquer un `slug` :

```php
use Illuminate\Support\Str;

protected function prepareForValidation(): void
{
    $this->merge([
        'title' => trim((string) $this->input('title')),
        'slug' => Str::slug((string) $this->input('name')),
    ]);
}
```

:::quiz
Tu modifies un tag dont le slug doit être unique. Comment éviter que la règle `unique` bloque l'enregistrement courant ?
- [ ] En supprimant la règle unique
- [ ] En passant le champ en nullable
- [x] En ajoutant ->ignore() avec l'enregistrement édité
- [ ] En changeant la méthode HTTP en POST
> `Rule::unique(...)->ignore($enregistrement)` exclut la ligne en cours de modification de la vérification d'unicité.
:::

## Des messages clairs, en français

Par défaut, les messages sont en anglais (« The title field is required »). Pour un public francophone, deux niveaux de personnalisation existent.

**Traduire tout le projet.** Installe les fichiers de langue français dans `lang/fr/` (avec `php artisan lang:publish` puis les traductions `validation.php`) et règle `APP_LOCALE=fr` dans `.env`. Tous les messages standards passent en français.

**Personnaliser un message précis**, dans la Form Request :

```php
public function messages(): array
{
    return [
        'title.required' => 'Donne un titre à ta roadmap.',
        'title.max' => 'Le titre ne peut pas dépasser 255 caractères.',
        'technology.required' => 'Choisis une technologie.',
    ];
}

public function attributes(): array
{
    return [
        'title' => 'titre',
        'technology' => 'technologie',
    ];
}
```

- `messages()` remplace le texte d'une règle pour un champ donné (clé `champ.règle`) ;
- `attributes()` renomme les champs dans les messages standards (« Le champ **titre** est obligatoire »).

Un bon message d'erreur dit **ce qui s'est passé** et **quoi faire**. « Donne un titre à ta roadmap » vaut mieux que « Champ invalide ».

## Afficher les erreurs avec Inertia et React

Quand la validation échoue, Laravel renvoie l'utilisateur à la page précédente et Inertia met les erreurs à disposition de ton formulaire :

```jsx
import { useForm } from '@inertiajs/react';

export default function Create() {
    const form = useForm({ title: '', technology: '' });

    function submit(event) {
        event.preventDefault();
        form.post('/roadmaps');
    }

    return (
        <form onSubmit={submit}>
            <label htmlFor="title">Titre</label>
            <input
                id="title"
                value={form.data.title}
                onChange={(e) => form.setData('title', e.target.value)}
                aria-invalid={Boolean(form.errors.title)}
                aria-describedby={form.errors.title ? 'title-error' : undefined}
            />
            {form.errors.title && <p id="title-error" role="alert">{form.errors.title}</p>}

            <button type="submit" disabled={form.processing}>Créer</button>
        </form>
    );
}
```

Trois détails comptent :

- `form.errors.title` contient le message du champ `title`, s'il y en a un ;
- le champ saisi garde sa valeur : l'utilisateur n'a pas à tout retaper ;
- `role="alert"` et `aria-describedby` rendent l'erreur lisible par un lecteur d'écran.

Tu approfondiras `useForm` au chapitre sur Inertia. Retiens ici que le lien est automatique : les noms des erreurs correspondent aux noms des champs validés.

## Atelier guidé : valider la création d'une étape

Compte une heure. Tu valides le formulaire d'ajout d'une étape à une roadmap.

1. Crée `StoreRoadmapStepRequest` avec `php artisan make:request StoreRoadmapStepRequest`.
2. Dans `rules()`, exige `title` (texte, 255 maximum), `description` (nullable, 2000 maximum), `status` (dans la liste des statuts du modèle), `estimated_minutes` (entier nullable entre 1 et 600).
3. Dans `authorize()`, retourne `true` pour un utilisateur connecté (tu raffineras avec les policies au chapitre suivant).
4. Ajoute `messages()` avec un message clair pour `title.required` et `status.in`.
5. Type le paramètre de la méthode `store` du contrôleur avec cette Form Request et utilise `$request->validated()`.
6. Envoie le formulaire vide : vérifie que les erreurs s'affichent à côté des champs.
7. Envoie `status` avec une valeur inventée (via les outils du navigateur, ou en modifiant le HTML) : l'erreur `in` doit la bloquer.
8. Écris un test de feature qui envoie un titre vide et vérifie `assertSessionHasErrors('title')`.

Pour t'auto-évaluer : explique pourquoi `$request->validated()` est plus sûr que `$request->all()`.

## Erreurs fréquentes

- **Utiliser `$request->all()` après validation.** Seul `validated()` garantit des données contrôlées.
- **Oublier `ignore()` en modification.** L'enregistrement se bloque lui-même sur une règle `unique`.
- **Se contenter de la validation du navigateur.** Elle est contournable.
- **Messages vagues.** « Invalide » n'aide personne ; dis ce qu'il faut corriger.
- **Valider une liste sans la notation à étoile.** Les éléments du tableau ne sont alors pas contrôlés.
- **Oublier `nullable`.** Un champ facultatif vide déclenche sinon une erreur ou stocke une chaîne vide.

## Bonnes pratiques

- Une Form Request par action d'écriture (`Store`, `Update`).
- Ne retiens que `validated()` pour écrire en base.
- Contrôle les valeurs fermées avec `Rule::in`, les liens avec `exists`, l'unicité avec `unique`.
- Rédige des messages courts, concrets et bienveillants.
- Teste chaque règle importante avec un test de feature.
- Garde la logique d'autorisation et de validation hors du contrôleur.

## À retenir

- La validation serveur est obligatoire : le navigateur n'est qu'un confort.
- `$request->validate()` convient aux cas simples, la Form Request aux cas réels.
- Une Form Request a deux méthodes : `authorize()` (droit d'accès) et `rules()` (règles).
- `Rule::in`, `exists`, `unique` (avec `ignore`) couvrent la plupart des besoins.
- `messages()` et `attributes()` personnalisent les erreurs ; `lang/fr` traduit le reste.
- Avec Inertia, `form.errors.champ` expose chaque message à ton interface.
