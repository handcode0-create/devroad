<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMemoRequest;
use App\Http\Requests\UpdateMemoRequest;
use App\Models\Memo;
use App\Models\MemoFolder;
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
        $folderId = $request->query('folder');
        $folderId = is_numeric($folderId) ? (int) $folderId : null;

        $memos = $user->memos()
            ->with(['tags:id,name,slug', 'folder:id,name,parent_id'])
            ->when($request->boolean('favorites'), fn ($query) => $query->where('is_favorite', true))
            ->when($recent, fn ($query) => $query->where('updated_at', '>=', Carbon::now()->subDays(7)))
            ->when($tag, fn ($query) => $query->whereHas('tags', fn ($t) => $t->where('slug', $tag)))
            ->when($folderId, fn ($query) => $query->where('folder_id', $folderId))
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
                'folder' => $memo->folder ? $memo->folder->only('id', 'name', 'parent_id') : null,
                'attachments' => $this->formatAttachments($memo),
                'folder_id' => $memo->folder_id,
            ]);

        $tags = $user->tags()->has('memos')->withCount('memos')->orderBy('name')->get(['id', 'name', 'slug']);
        $folders = $user->memoFolders()->withCount('memos')->orderBy('position')->orderBy('name')->get(['id', 'parent_id', 'name', 'icon', 'position', 'memos_count']);

        $counts = [
            'total' => $user->memos()->count(),
            'favorites' => $user->memos()->where('is_favorite', true)->count(),
            'recent' => $user->memos()->where('updated_at', '>=', Carbon::now()->subDays(7))->count(),
        ];

        return Inertia::render('Memos/Index', [
            'memos' => $memos,
            'tags' => $tags,
            'folders' => $folders,
            'counts' => $counts,
            'filters' => [
                'favorites' => $request->boolean('favorites'),
                'recent' => $recent,
                'tag' => $tag,
                'q' => $q,
                'folder' => $folderId,
            ],
        ]);
    }

    public function create(Request $request): Response
    {
        Gate::authorize('create', Memo::class);

        return Inertia::render('Memos/Create', [
            'folders' => $request->user()->memoFolders()->orderBy('position')->orderBy('name')->get(['id', 'parent_id', 'name', 'icon', 'position']),
            'defaultFolderId' => $request->query('folder') ? (int) $request->query('folder') : null,
        ]);
    }

    public function store(StoreMemoRequest $request): RedirectResponse
    {
        Gate::authorize('create', Memo::class);

        $memo = DB::transaction(function () use ($request) {
            $this->assertFolderBelongsToUser($request);
            $memo = $request->user()->memos()->create($request->safe()->except(['tags', 'attachments']));
            $memo->syncTagNames($request->validated('tags', []) ?? []);
            $this->storeAttachments($request, $memo);

            return $memo;
        });

        return redirect()->route('memos.show', $memo);
    }

    public function storeFromStep(StoreMemoRequest $request, RoadmapStep $step): RedirectResponse
    {
        Gate::authorize('view', $step);

        $memo = DB::transaction(function () use ($request) {
            $this->assertFolderBelongsToUser($request);
            $memo = $request->user()->memos()->create($request->safe()->except(['tags', 'attachments']));
            $memo->syncTagNames($request->validated('tags', []) ?? []);

            return $memo;
        });

        return redirect()->route('steps.show', $step)->with('success', 'Mémo créé depuis le cours.');
    }

    public function show(Memo $memo): Response
    {
        Gate::authorize('view', $memo);
        $memo->load(['tags:id,name,slug', 'folder:id,name,parent_id', 'attachments:id,memo_id,name,mime_type,size,created_at']);

        return Inertia::render('Memos/Show', [
            'memo' => [
                ...$memo->only('id', 'title', 'content', 'formatting', 'is_favorite', 'created_at', 'updated_at'),
                'tags' => $this->formatTags($memo),
                'folder' => $memo->folder ? $memo->folder->only('id', 'name', 'parent_id') : null,
                'attachments' => $this->formatAttachments($memo),
            ],
        ]);
    }

    public function edit(Memo $memo): Response
    {
        Gate::authorize('update', $memo);
        $memo->load(['tags:id,name,slug', 'folder:id,name,parent_id', 'attachments:id,memo_id,name,mime_type,size,created_at']);

        return Inertia::render('Memos/Edit', [
            'memo' => [
                ...$memo->only('id', 'title', 'content', 'formatting', 'is_favorite'),
                'tags' => $this->formatTags($memo),
                'folder' => $memo->folder ? $memo->folder->only('id', 'name', 'parent_id') : null,
                'attachments' => $this->formatAttachments($memo),
                'folder' => $memo->folder ? $memo->folder->only('id', 'name', 'parent_id') : null,
            ],
            'folders' => $memo->user->memoFolders()->orderBy('position')->orderBy('name')->get(['id', 'parent_id', 'name', 'icon', 'position']),
        ]);
    }

    public function update(UpdateMemoRequest $request, Memo $memo): RedirectResponse
    {
        Gate::authorize('update', $memo);

        DB::transaction(function () use ($request, $memo) {
            $this->assertFolderBelongsToUser($request);
            $memo->update($request->safe()->except(['tags', 'attachments']));

            if ($request->has('tags')) {
                $memo->syncTagNames($request->validated('tags', []) ?? []);
            }
            $this->storeAttachments($request, $memo);
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

    private function assertFolderBelongsToUser(Request $request): void
    {
        $folderId = $request->input('folder_id');
        if ($folderId !== null && ! $request->user()->memoFolders()->whereKey($folderId)->exists()) {
            abort(422, 'Le dossier sélectionné est invalide.');
        }
    }

    private function storeAttachments(Request $request, Memo $memo): void
    {
        foreach ($request->file('attachments', []) as $file) {
            $memo->attachments()->create([
                'user_id' => $request->user()->id,
                'name' => mb_substr($file->getClientOriginalName(), 0, 255),
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
                'data' => $file->getContent(),
            ]);
        }
    }

    private function formatAttachments(Memo $memo): array
    {
        return $memo->attachments->map(fn ($file) => [
            'id' => $file->id,
            'name' => $file->name,
            'mime_type' => $file->mime_type,
            'size' => $file->size,
            'is_image' => $file->isImage(),
            'url' => '/memos/attachments/' . $file->id,
            'download_url' => '/memos/attachments/' . $file->id . '/download',
            'created_at' => $file->created_at,
        ])->values()->all();
    }

    private function formatTags(Memo $memo): array
    {
        return $memo->tags
            ->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug])
            ->values()
            ->all();
    }
}
