---
title: Inertia et React
minutes: 150
level: intermediate
---

## Ce que tu vas apprendre

Tu sais maintenant recevoir une requête, valider, autoriser et lire la base. Il reste à **afficher** le résultat dans une vraie interface. DevRoad utilise React, relié à Laravel par **Inertia**, qui évite d'écrire une API séparée.

À la fin du chapitre, tu seras capable de :

- expliquer ce qu'est Inertia et quel problème il résout ;
- envoyer des données du contrôleur à une page React avec `Inertia::render` ;
- naviguer sans rechargement avec `Link` et `router` ;
- gérer un formulaire avec `useForm` : saisie, envoi, erreurs, état d'envoi ;
- partager des données communes (utilisateur connecté, messages) avec `share` ;
- afficher les messages flash et la pagination ;
- éviter les erreurs de débutant : modèles entiers exposés, props trop lourdes.

Prévois deux heures et demie. Ouvre `resources/js/Pages` dans ton projet : tu y retrouveras chaque exemple.

## Le problème qu'Inertia résout

Il y a deux grandes façons de construire une application avec une interface moderne.

**Première approche : deux applications.** Un back-end Laravel qui expose une **API** (des adresses qui renvoient du JSON), et un front-end React séparé qui l'appelle. Cela fonctionne, mais tu dupliques beaucoup de choses : deux dépôts, deux déploiements, de l'authentification par jetons à configurer, des routes à déclarer deux fois.

**Seconde approche : Inertia.** Tu gardes **une seule application**. Les routes, les contrôleurs, la validation et l'authentification restent dans Laravel, comme tu les as appris. Mais au lieu de renvoyer du HTML, le contrôleur indique **quel composant React afficher** et lui transmet les données. Inertia fait le lien.

Concrètement, la première visite charge la page complète. Ensuite, chaque clic sur un lien envoie une requête en arrière-plan, récupère les données JSON et remplace seulement le composant : la navigation est **fluide, sans rechargement**, comme une application à page unique, mais sans API à maintenir.

> **À retenir** : avec Inertia, Laravel reste le chef d'orchestre (routes, contrôleurs, droits), et React se contente d'**afficher**. Tu écris du Laravel classique et du React classique.

:::quiz
Quel est le principal avantage d'Inertia ?
- [ ] Il remplace PHP par JavaScript
- [x] Il permet d'utiliser React sans construire ni maintenir une API séparée
- [ ] Il supprime le besoin de base de données
- [ ] Il remplace Composer
> Inertia relie contrôleurs Laravel et composants React directement : pas d'API REST à écrire, mais une navigation fluide.
:::

## Envoyer des données à une page

Le contrôleur utilise `Inertia::render`, avec deux arguments : le **nom de la page** et un tableau de **props**.

```php
use Inertia\Inertia;

public function index(Request $request)
{
    $roadmaps = $request->user()
        ->roadmaps()
        ->withCount('steps')
        ->latest('updated_at')
        ->paginate(12);

    return Inertia::render('Roadmaps/Index', [
        'roadmaps' => $roadmaps,
    ]);
}
```

Le nom `'Roadmaps/Index'` désigne le fichier `resources/js/Pages/Roadmaps/Index.jsx`. Les props arrivent comme paramètres du composant :

```jsx
import { Head } from '@inertiajs/react';

export default function Index({ roadmaps }) {
    return (
        <>
            <Head title="Parcours" />
            <h1>Mes parcours</h1>
            <ul>
                {roadmaps.data.map((roadmap) => (
                    <li key={roadmap.id}>
                        {roadmap.title} ({roadmap.steps_count} étapes)
                    </li>
                ))}
            </ul>
        </>
    );
}
```

Quelques remarques importantes :

- `Head` modifie le titre de l'onglet du navigateur ;
- une liste paginée expose ses éléments dans `roadmaps.data`, avec des informations de pagination à côté ;
- chaque élément d'une liste React a besoin d'une `key` unique : l'`id` convient.

### Ne transmets que ce qui est nécessaire

Tout ce que tu envoies en props est **visible** dans le code de la page, donc par l'utilisateur. Évite de passer des modèles entiers par réflexe : choisis les champs.

```php
return Inertia::render('Roadmaps/Show', [
    'roadmap' => [
        'id' => $roadmap->id,
        'title' => $roadmap->title,
        'progress' => $roadmap->progress,
        'steps' => $roadmap->steps->map(fn ($step) => [
            'id' => $step->id,
            'title' => $step->title,
            'status' => $step->status,
        ]),
    ],
]);
```

Tu gagnes en sécurité (aucune colonne sensible n'est exposée par accident) et en performance (moins de données à transférer).

> **Attention** : n'envoie jamais de secret (clé d'API, jeton) dans une prop. Même masqué à l'écran, il reste lisible dans la réponse envoyée au navigateur.

## Naviguer : Link et router

Pour aller d'une page à l'autre sans rechargement, utilise le composant `Link` d'Inertia à la place d'une balise `<a>` classique.

```jsx
import { Link } from '@inertiajs/react';

<Link href="/roadmaps">Mes parcours</Link>
<Link href={`/roadmaps/${roadmap.id}`}>Ouvrir</Link>
```

Pour une action qui modifie des données, comme une suppression, utilise `router` avec la bonne méthode HTTP :

```jsx
import { router } from '@inertiajs/react';

function supprimer(roadmap) {
    if (!window.confirm('Supprimer cette roadmap ?')) return;

    router.delete(`/roadmaps/${roadmap.id}`, {
        preserveScroll: true,
    });
}
```

`router` propose `get`, `post`, `patch`, `put` et `delete`. L'option `preserveScroll` évite de remonter en haut de la page après l'action, ce qui est agréable dans une longue liste.

Un lien `Link` peut aussi porter une méthode : `<Link href="/logout" method="post" as="button">Se déconnecter</Link>`. C'est la bonne façon de gérer la déconnexion, qui ne doit pas être une simple requête `GET`.

## Les formulaires avec useForm

Le hook `useForm` gère à ta place tout ce qui est pénible dans un formulaire : l'état des champs, l'envoi, les erreurs de validation renvoyées par Laravel, l'indicateur de chargement.

```jsx
import { useForm } from '@inertiajs/react';

export default function Create() {
    const form = useForm({
        title: '',
        technology: 'laravel',
        description: '',
    });

    function submit(event) {
        event.preventDefault();

        form.post('/roadmaps', {
            onSuccess: () => form.reset(),
        });
    }

    return (
        <form onSubmit={submit}>
            <label htmlFor="title">Titre</label>
            <input
                id="title"
                value={form.data.title}
                onChange={(e) => form.setData('title', e.target.value)}
            />
            {form.errors.title && <p role="alert">{form.errors.title}</p>}

            <label htmlFor="technology">Technologie</label>
            <select
                id="technology"
                value={form.data.technology}
                onChange={(e) => form.setData('technology', e.target.value)}
            >
                <option value="laravel">Laravel</option>
                <option value="react">React</option>
            </select>
            {form.errors.technology && <p role="alert">{form.errors.technology}</p>}

            <button type="submit" disabled={form.processing}>
                {form.processing ? 'Création…' : 'Créer la roadmap'}
            </button>
        </form>
    );
}
```

Ce que `useForm` te donne :

| Élément | Rôle |
| --- | --- |
| `form.data` | Les valeurs actuelles des champs |
| `form.setData('champ', valeur)` | Modifie un champ |
| `form.post`, `form.patch`, `form.put`, `form.delete` | Envoient le formulaire avec la bonne méthode |
| `form.errors` | Les erreurs renvoyées par la validation Laravel, par champ |
| `form.processing` | `true` pendant l'envoi : sert à désactiver le bouton |
| `form.reset()` | Remet les champs à leur valeur de départ |

Le lien avec le chapitre précédent est direct : une règle `title` de ta Form Request qui échoue produit une erreur dans `form.errors.title`. Aucun code supplémentaire : les noms correspondent.

> **Astuce** : désactive toujours le bouton d'envoi avec `form.processing`. Sinon, un utilisateur impatient clique trois fois et crée trois enregistrements.

:::quiz
Que contient `form.errors.title` après un échec de validation ?
- [ ] La valeur saisie dans le champ
- [x] Le message d'erreur de la règle de validation sur le champ title
- [ ] Le code HTTP de la réponse
- [ ] Un booléen indiquant l'envoi
> Inertia transmet les erreurs de validation de Laravel, indexées par nom de champ : form.errors.title contient le message du champ title.
:::

## Les données partagées avec toutes les pages

Certaines informations sont nécessaires partout : l'utilisateur connecté, les messages flash, les compteurs de la barre latérale. Plutôt que de les passer depuis chaque contrôleur, Inertia propose un middleware, `HandleInertiaRequests`, dont la méthode `share` s'applique à **toutes** les pages.

```php
public function share(Request $request): array
{
    return [
        ...parent::share($request),
        'auth' => [
            'user' => $request->user()
                ? $request->user()->only('id', 'name', 'email')
                : null,
        ],
        'flash' => [
            'success' => fn () => $request->session()->get('success'),
        ],
    ];
}
```

Dans n'importe quel composant, tu les lis avec `usePage` :

```jsx
import { usePage } from '@inertiajs/react';

export default function Header() {
    const { auth, flash } = usePage().props;

    return (
        <header>
            <span>Bonjour {auth.user?.name}</span>
            {flash.success && <p role="status">{flash.success}</p>}
        </header>
    );
}
```

Remarque deux détails :

- `->only('id', 'name', 'email')` limite les champs exposés : jamais l'utilisateur complet ;
- la valeur `fn () => ...` est une **évaluation paresseuse** : Laravel ne la calcule que si la page en a besoin. C'est idéal pour des données un peu coûteuses, comme les compteurs de la barre latérale.

C'est ainsi qu'un message `->with('success', 'Roadmap créée.')`, posé dans un contrôleur, apparaît à l'écran après la redirection.

## La pagination

Quand un contrôleur renvoie `paginate(12)`, la prop contient les éléments (`data`) et les liens de pagination (`links`). Un petit composant suffit pour les afficher :

```jsx
import { Link } from '@inertiajs/react';

export default function Pagination({ links }) {
    if (links.length <= 3) return null;

    return (
        <nav aria-label="Pagination">
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        aria-current={link.active ? 'page' : undefined}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span key={index} dangerouslySetInnerHTML={{ __html: link.label }} />
                ),
            )}
        </nav>
    );
}
```

Les libellés fournis par Laravel (« Previous », « 1 », « Next ») contiennent parfois des entités HTML, d'où `dangerouslySetInnerHTML`. Tu ne l'utilises ici que sur des libellés fournis par Laravel, **jamais** sur du contenu saisi par un utilisateur, ce qui ouvrirait une faille XSS.

## Les rechargements partiels

Par défaut, un rechargement recalcule toutes les props. Pour une page lourde, tu peux ne redemander que certaines données :

```jsx
router.reload({ only: ['roadmaps'] });
```

Côté serveur, tu peux aussi rendre une prop **facultative**, calculée seulement quand on la demande, avec `Inertia::optional(fn () => ...)`. C'est une optimisation à garder pour plus tard : commence simple, et optimise quand tu mesures un vrai ralentissement.

:::quiz
Où place-t-on les données dont toutes les pages ont besoin, comme l'utilisateur connecté ?
- [ ] Dans chaque contrôleur, à la main
- [ ] Dans le fichier .env
- [x] Dans la méthode share() du middleware HandleInertiaRequests
- [ ] Dans routes/web.php
> share() ajoute ces props à toutes les pages ; on les lit ensuite avec usePage().props.
:::

## Atelier guidé : une page de parcours complète

Compte une heure et demie.

1. Dans `RoadmapController@index`, retourne `Inertia::render('Roadmaps/Index', ...)` avec la liste paginée de l'utilisateur et `withCount('steps')`.
2. Crée `resources/js/Pages/Roadmaps/Index.jsx` qui affiche chaque roadmap avec son titre et son nombre d'étapes.
3. Ajoute un lien `Link` vers la page de chaque roadmap, et un lien « Nouvelle roadmap ».
4. Crée `Pages/Roadmaps/Create.jsx` avec `useForm` : titre, technologie, description. Affiche chaque erreur sous son champ.
5. Dans `HandleInertiaRequests::share`, partage `flash.success`, puis affiche-le en haut de la page après une création.
6. Ajoute un bouton « Supprimer » qui appelle `router.delete` avec une confirmation.
7. Envoie le formulaire vide : les erreurs de la Form Request doivent apparaître. Vérifie que le bouton est désactivé pendant l'envoi.
8. Remplace `roadmap` entier par un tableau choisi (`id`, `title`, `steps_count`) dans le contrôleur.

Pour t'auto-évaluer : explique pourquoi on évite de passer un modèle entier en prop, et comment un message flash arrive de `redirect()->with()` jusqu'à l'écran.

## Erreurs fréquentes

- **Utiliser `<a>` au lieu de `Link`.** Chaque clic recharge toute la page.
- **Oublier de désactiver le bouton pendant l'envoi.** Cela crée des doublons.
- **Exposer un modèle entier.** Des champs inattendus fuient vers le navigateur.
- **Mettre des secrets dans les props.** Tout est visible côté client.
- **Oublier la `key` dans une liste.** React affiche un avertissement et peut mal mettre à jour l'interface.
- **Une page dont le nom ne correspond pas au fichier.** `Roadmaps/Index` doit exister exactement dans `resources/js/Pages`, majuscules comprises.

## Bonnes pratiques

- Le contrôleur prépare des données **choisies** ; la page ne fait qu'afficher.
- Utilise `useForm` pour tout formulaire, avec `processing` et `errors`.
- Partage seulement le minimum dans `share`.
- Préfère les listes paginées aux listes complètes.
- Rends l'interface accessible : libellés (`label`), `role="alert"` pour les erreurs, `aria-current` pour la page active.
- Garde les composants courts ; extrais des composants réutilisables dès que tu copies du code.

## À retenir

- Inertia relie contrôleurs Laravel et composants React sans API séparée, avec une navigation sans rechargement.
- `Inertia::render('Dossier/Page', $props)` désigne un fichier de `resources/js/Pages`.
- `Link` et `router` naviguent et envoient des actions ; `useForm` gère champs, erreurs et état d'envoi.
- Les erreurs de validation Laravel arrivent dans `form.errors`, champ par champ.
- `share()` fournit les données communes ; `usePage().props` les lit.
- N'envoie que les champs nécessaires : tout ce qui part en props est visible par l'utilisateur.
