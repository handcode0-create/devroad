<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Memo;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    private const MIN_LENGTH = 2;

    public function __invoke(SearchRequest $request): Response
    {
        $user = $request->user();
        $term = trim((string) $request->validated('q', ''));
        $type = $request->validated('type') ?? 'all';

        // Terme trop court : on n'interroge pas la base.
        if (mb_strlen($term) < self::MIN_LENGTH) {
            return Inertia::render('Search/Index', [
                'query' => $term,
                'type' => $type,
                'results' => null,
                'counts' => null,
            ]);
        }

        $like = $this->likePattern($term);

        // Requêtes de base, toutes limitées aux données de l'utilisateur.
        $roadmaps = $user->roadmaps()
            ->whereRaw("title LIKE ? ESCAPE '!'", [$like]);

        $memos = $user->memos()
            ->where(fn ($q) => $q
                ->whereRaw("title LIKE ? ESCAPE '!'", [$like])
                ->orWhereRaw("content LIKE ? ESCAPE '!'", [$like]));

        // Une étape n'a pas de user_id : on passe par sa roadmap.
        $steps = RoadmapStep::query()
            ->whereHas('roadmap', fn ($q) => $q->where('user_id', $user->id))
            ->whereRaw("title LIKE ? ESCAPE '!'", [$like]);

        $counts = [
            'roadmaps' => (clone $roadmaps)->count(),
            'memos' => (clone $memos)->count(),
            'steps' => (clone $steps)->count(),
        ];

        // Onglet « Tout » : 5 résultats par section. Onglet précis : 10.
        $perPage = $type === 'all' ? 5 : 10;
        $wants = fn (string $section) => $type === 'all' || $type === $section;

        $results = [
            'roadmaps' => $wants('roadmaps')
                ? (clone $roadmaps)->withProgress()->latest()
                    ->paginate($perPage, ['*'], 'roadmaps_page')
                    ->withQueryString()
                    ->through(fn (Roadmap $r) => [
                        'id' => $r->id,
                        'title' => $r->title,
                        'status' => $r->status,
                        'steps_count' => $r->steps_count,
                        'progress' => $r->progress,
                    ])
                : null,

            'memos' => $wants('memos')
                ? (clone $memos)->with('tags:id,name,slug')->latest('updated_at')
                    ->paginate($perPage, ['*'], 'memos_page')
                    ->withQueryString()
                    ->through(fn (Memo $m) => [
                        'id' => $m->id,
                        'title' => $m->title,
                        'excerpt' => Str::limit($m->content, 140),
                        'is_favorite' => $m->is_favorite,
                        'tags' => $m->tags->map(fn ($t) => ['id' => $t->id, 'name' => $t->name, 'slug' => $t->slug])->values(),
                    ])
                : null,

            'steps' => $wants('steps')
                ? (clone $steps)->with('roadmap:id,title')->latest()
                    ->paginate($perPage, ['*'], 'steps_page')
                    ->withQueryString()
                    ->through(fn (RoadmapStep $s) => [
                        'id' => $s->id,
                        'title' => $s->title,
                        'status' => $s->status,
                        'roadmap_id' => $s->roadmap_id,
                        'roadmap_title' => $s->roadmap->title,
                    ])
                : null,
        ];

        return Inertia::render('Search/Index', [
            'query' => $term,
            'type' => $type,
            'results' => $results,
            'counts' => $counts,
        ]);
    }

    /**
     * Motif LIKE sûr : % et _ saisis par l'utilisateur sont traités comme du
     * texte, pas comme des jokers. On utilise « ! » comme caractère
     * d'échappement (ESCAPE '!') car il se comporte pareil sur MySQL et SQLite,
     * contrairement à l'antislash.
     */
    private function likePattern(string $term): string
    {
        return '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';
    }
}