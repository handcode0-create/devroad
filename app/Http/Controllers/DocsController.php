<?php

namespace App\Http\Controllers;

use App\Models\DocSource;
use App\Services\Ai\AiClient;
use App\Services\Ai\AiException;
use App\Services\Docs\DocsAssistant;
use App\Services\Docs\DocsLibrary;
use App\Services\Docs\DocsTranslator;
use App\Services\Docs\FrenchDocs;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class DocsController extends Controller
{
    public function __construct(private DocsLibrary $library)
    {
    }

    public function index(Request $request): Response
    {
        $query = trim((string) $request->query('q', ''));
        $source = (string) $request->query('source', '');
        $sources = $this->sources();
        $keys = $source !== '' && $sources->contains('key', $source) ? [$source] : [];

        return Inertia::render('Docs/Index', [
            'query' => $query,
            'source' => $keys[0] ?? null,
            'sources' => $sources->values(),
            'results' => mb_strlen($query) >= 2 ? $this->library->search($query, $keys) : null,
            // Doc choisie, rien cherché : on affiche son contenu à parcourir (catégories + entrées).
            'browse' => $keys !== [] && mb_strlen($query) < 2
                ? $this->browseSource($keys[0], $request->query('type'))
                : null,
            'ai' => $this->aiState($request),
            'locale' => $request->user()->docs_locale ?: 'fr',
        ]);
    }

    public function show(Request $request, string $source, string $path): Response
    {
        $model = DocSource::where('key', $source)->firstOrFail();
        abort_unless(preg_match('#^[\w\-./~:@%+]+$#u', $path) && ! str_contains($path, '..'), 404);

        // Langue : ?lang=en|fr pour cette page, sinon la préférence du compte (français par défaut).
        $want = in_array($request->query('lang'), ['fr', 'en'], true) ? $request->query('lang') : ($request->user()->docs_locale ?: 'fr');

        try {
            $read = $this->library->read($model, $path, $want);
            $page = $read['page'];
            $payload = [
                'title' => $page->title,
                'path' => $page->path,
                'html' => $page->html,
                'headings' => $this->library->headings($page),
                'locale' => $read['locale'],
            ];
            $french = $read['french'];
            $error = null;
        } catch (Throwable $exception) {
            report($exception);
            [$payload, $french] = [null, 'unknown'];
            $error = 'Cette page de documentation n’a pas pu être chargée. Vérifie ta connexion puis réessaie.';
        }

        $english = $payload && $payload['locale'] === 'en' ? $page : null;

        return Inertia::render('Docs/Show', [
            'source' => $this->presentSource($model),
            'page' => $payload,
            'path' => $path,
            'error' => $error,
            'want' => $want,
            // official : traduction de la communauté · machine : traduite par l'IA · missing/none : anglais seulement
            'french' => $french,
            'translation' => $english && $want === 'fr' && in_array($french, ['missing', 'none'], true) ? [
                'total' => count(app(DocsTranslator::class)->chunks($english)),
                'done' => $english->translations()->count(),
            ] : null,
            'originalUrl' => $this->originalUrl($model, $path),
            'originalFrUrl' => rescue(fn () => app(FrenchDocs::class)->originalUrl($model, $path), null, report: false),
            'ai' => $this->aiState($request),
        ]);
    }

    /** Traduit un morceau d'une page anglaise en français (IA de l'utilisateur). */
    public function translate(Request $request, DocsTranslator $translator): JsonResponse
    {
        $data = $request->validate([
            'source' => ['required', 'string', 'max:40'],
            'path' => ['required', 'string', 'max:500'],
            'chunk' => ['required', 'integer', 'min:0', 'max:60'],
        ]);
        $source = DocSource::where('key', $data['source'])->firstOrFail();

        try {
            $english = $this->library->page($source, $data['path']);

            return response()->json($translator->translateChunk($request->user(), $english, (int) $data['chunk']));
        } catch (AiException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'La traduction a échoué. Réessaie dans un instant.'], 500);
        }
    }

    /** Langue préférée de la documentation (français par défaut). */
    public function locale(Request $request): RedirectResponse
    {
        $data = $request->validate(['locale' => ['required', 'in:fr,en']]);
        $request->user()->forceFill(['docs_locale' => $data['locale']])->save();

        return back();
    }

    public function ask(Request $request, DocsAssistant $assistant): JsonResponse
    {
        $data = $request->validate([
            'question' => ['required', 'string', 'min:4', 'max:600'],
            'sources' => ['nullable', 'array', 'max:12'],
            'sources.*' => ['string', 'max:40'],
        ], [
            'question.min' => 'Ta question est un peu courte : précise ce que tu cherches.',
            'question.max' => 'Ta question est trop longue (600 caractères au maximum).',
        ]);

        try {
            return response()->json($assistant->ask($request->user(), $data['question'], $data['sources'] ?? []));
        } catch (AiException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json(['message' => 'L’assistant n’a pas pu répondre. Réessaie dans un instant.'], 500);
        }
    }

    /** « Garder en fiche » : une réponse de l'IA ou un extrait devient une fiche mémo. */
    public function saveMemo(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'html' => ['required', 'string', 'max:60000'],
        ]);

        // Le HTML vient de nos propres réponses mais on le re-nettoie quand même.
        $html = \App\Services\Docs\DocHtml::clean($data['html'], fn (string $href) => str_starts_with($href, '/docs/') ? $href : null)['html'];
        $memo = $request->user()->memos()->create(['title' => Str::limit($data['title'], 250, ''), 'content' => $html]);
        $memo->syncTagNames(['documentation']);

        return back()
            ->with('success', 'Fiche mémo créée.')
            ->with('undo', ['label' => 'Ouvrir', 'method' => 'get', 'url' => route('memos.show', $memo)]);
    }

    private function browseSource(string $key, mixed $type): ?array
    {
        $source = DocSource::where('key', $key)->first();

        return $source ? $this->library->browse($source, is_string($type) ? $type : null) : null;
    }

    private function sources()
    {
        $synced = DocSource::all()->keyBy('key');

        return collect(config('devroad_docs.sources'))->map(function ($config, $key) use ($synced) {
            $source = $synced[$key] ?? null;

            return [
                'key' => $key,
                'name' => $source?->name ?? $config['name'],
                'technology' => $config['technology'] ?? $key,
                'version' => $source?->version,
                'entries' => $source?->entries_count ?? 0,
                'ready' => (bool) $source?->entries_count,
                // Version française officielle disponible pour cette doc (sinon : traduction par l'IA).
                'french' => filled($config['fr']['provider'] ?? null),
            ];
        })->values();
    }

    private function presentSource(DocSource $source): array
    {
        return [
            'key' => $source->key,
            'name' => $source->name,
            'version' => $source->version,
            'technology' => config("devroad_docs.sources.{$source->key}.technology", $source->key),
            'attribution' => $source->attribution,
            'provider' => $source->provider,
        ];
    }

    private function originalUrl(DocSource $source, string $path): string
    {
        return $source->provider === 'laravel'
            ? rtrim(config('devroad_docs.laravel.site_url'), '/') . '/' . $source->remote_slug . '/' . $path
            : 'https://devdocs.io/' . $source->remote_slug . '/' . $path;
    }

    private function aiState(Request $request): array
    {
        $user = $request->user();

        return [
            'enabled' => $user->hasAiAssistant(),
            'provider' => $user->ai_provider ? app(AiClient::class)->name($user->ai_provider) : null,
            // Traduction des pages anglaises : « user » (sa clé), « platform » (clé DevRoad, offerte) ou null.
            'translate' => app(DocsTranslator::class)->mode($user),
        ];
    }
}
