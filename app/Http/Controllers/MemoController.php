<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemoRequest;
use App\Http\Requests\UpdateMemoRequest;
use App\Models\Memo;
use App\Models\RoadmapStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class MemoController extends Controller
{
    public function index(Request $request): Response
    {
        $user = $request->user();
        $tag = $request->query('tag');
        $tag = is_string($tag) && $tag !== '' ? $tag : null;
        $q = $request->query('q');
        $q = is_string($q) ? trim($q) : '';
        $recent = $request->boolean('recent');

        $memos = $user->memos()
            ->with('tags:id,name,slug')
            ->when($request->boolean('favorites'), fn ($query) => $query->where('is_favorite', true))
            ->when($recent, fn ($query) => $query->where('updated_at', '>=', Carbon::now()->subDays(7)))
            ->when($tag, fn ($query) => $query->whereHas('tags', fn ($t) => $t->where('slug', $tag)))
            ->when($q !== '', function ($query) use ($q) {
                $query->where(function ($search) use ($q) {
                    $search->where('title', 'ilike', '%' . $q . '%')
                        ->orWhere('content', 'ilike', '%' . $q . '%')
                        ->orWhereHas('tags', fn ($tags) => $tags->where('name', 'ilike', '%' . $q . '%'));
                });
            })
            ->latest('updated_at')
            ->paginate(12)
            ->withQueryString()
            ->through(fn (Memo $memo) => [
                'id' => $memo->id,
                'title' => $memo->title,
                'excerpt' => Str::limit($memo->content, 140),
                'is_favorite' => $memo->is_favorite,
                'updated_at' => $memo->updated_at,
                'tags' => $this->formatTags($memo),
            ]);

        $tags = $user->tags()->has('memos')->withCount('memos')->orderBy('name')->get(['id', 'name', 'slug']);

        $counts = [
            'total' => $user->memos()->count(),
            'favorites' => $user->memos()->where('is_favorite', true)->count(),
            'recent' => $user->memos()->where('updated_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return Inertia::render('Memos/Index', [
            'memos' => $memos,
            'tags' => $tags,
            'counts' => $counts,
            'filters' => [
                'favorites' => $request->boolean('favorites'),
                'recent' => $recent,
                'tag' => $tag,
                'q' => $q,
            ],
        ]);
    }

    public function create(): Response
    {
        Gate::authorize('create', Memo::class);

        return Inertia::render('Memos/Create');
    }

    public function store(StoreMemoRequest $request): RedirectResponse
    {
        Gate::authorize('create', Memo::class);

        $memo = DB::transaction(function () use ($request) {
            $memo = $request->user()->memos()->create($request->safe()->except('tags'));
            $memo->syncTagNames($request->validated('tags', []) ?? []);

            return $memo;
        });

        return redirect()->route('memos.show', $memo);
    }

    public function storeFromStep(StoreMemoRequest $request, RoadmapStep $step): RedirectResponse
    {
        Gate::authorize('view', $step);

        $memo = DB::transaction(function () use ($request) {
            $memo = $request->user()->memos()->create($request->safe()->except('tags'));
            $memo->syncTagNames($request->validated('tags', []) ?? []);

            return $memo;
        });

        return redirect()->route('steps.show', $step)->with('success', 'Mémo créé depuis le cours.');
    }

    public function show(Memo $memo): Response
    {
        Gate::authorize('view', $memo);
        $memo->load('tags:id,name,slug');

        return Inertia::render('Memos/Show', [
            'memo' => [
                ...$memo->only('id', 'title', 'content', 'formatting', 'is_favorite', 'created_at', 'updated_at'),
                'tags' => $this->formatTags($memo),
            ],
        ]);
    }

    public function edit(Memo $memo): Response
    {
        Gate::authorize('update', $memo);
        $memo->load('tags:id,name,slug');

        return Inertia::render('Memos/Edit', [
            'memo' => [
                ...$memo->only('id', 'title', 'content', 'formatting', 'is_favorite'),
                'tags' => $this->formatTags($memo),
            ],
        ]);
    }

    public function update(UpdateMemoRequest $request, Memo $memo): RedirectResponse
    {
        Gate::authorize('update', $memo);

        DB::transaction(function () use ($request, $memo) {
            $memo->update($request->safe()->except('tags'));

            if ($request->has('tags')) {
                $memo->syncTagNames($request->validated('tags', []) ?? []);
            }
        });

        return redirect()->route('memos.show', $memo);
    }

    public function toggleFavorite(Memo $memo): RedirectResponse
    {
        Gate::authorize('update', $memo);
        $memo->update(['is_favorite' => ! $memo->is_favorite]);

        return back();
    }

    public function destroy(Memo $memo): RedirectResponse
    {
        Gate::authorize('delete', $memo);
        $memo->delete();

        return redirect()->route('memos.index');
    }

    private function formatTags(Memo $memo): array
    {
        return $memo->tags
            ->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug])
            ->values()
            ->all();
    }
}
