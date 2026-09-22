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
        $trash = $request->boolean('trash');
        $folderId = $request->query('folder');
        $folderId = is_numeric($folderId) ? (int) $folderId : null;
        $sort = (string) $request->query('sort', 'updated_desc');
        $sorts = ['updated_desc', 'updated_asc', 'title_asc', 'title_desc', 'favorite'];
        abort_unless(in_array($sort, $sorts, true), 422);

        $memosQuery = $trash ? $user->memos()->onlyTrashed() : $user->memos();
        $memos = $memosQuery
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
            ->when($sort === 'updated_desc', fn ($query) => $query->orderByDesc('updated_at'))
            ->when($sort === 'updated_asc', fn ($query) => $query->orderBy('updated_at'))
            ->when($sort === 'title_asc', fn ($query) => $query->orderBy('title'))
            ->when($sort === 'title_desc', fn ($query) => $query->orderByDesc('title'))
            ->when($sort === 'favorite', fn ($query) => $query->orderByDesc('is_favorite')->orderByDesc('updated_at'))
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
                'folder_id' => $memo->folder_id,
                'icon' => $memo->icon,
                'is_full_width' => $memo->is_full_width,
                'deleted_at' => $memo->deleted_at,
            ]);

        $tags = $user->tags()->has('memos')->withCount('memos')->orderBy('name')->get(['id', 'name', 'slug']);
        $folders = $user->memoFolders()->withCount('memos')->orderBy('position')->orderBy('name')->get(['id', 'parent_id', 'name', 'icon', 'position', 'memos_count']);

        $counts = [
            'total' => $user->memos()->count(),
            'favorites' => $user->memos()->where('is_favorite', true)->count(),
            'recent' => $user->memos()->where('updated_at', '>=', Carbon::now()->subDays(7))->count(),
            'trash' => $user->memos()->onlyTrashed()->count(),
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
                'trash' => $trash,
                'sort' => $sort,
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
        $memo->load(['tags:id,name,slug', 'folder:id,name,parent_id', 'folder.parent', 'attachments:id,memo_id,name,mime_type,size,created_at']);

        return Inertia::render('Memos/Show', [
            'memo' => [
                ...$memo->only('id', 'title', 'content', 'formatting', 'is_favorite', 'created_at', 'updated_at'),
                'tags' => $this->formatTags($memo),
                'folder' => $memo->folder ? $memo->folder->only('id', 'name', 'parent_id') : null,
                'icon' => $memo->icon,
                'cover_attachment_id' => $memo->cover_attachment_id,
                'is_full_width' => $memo->is_full_width,
                'attachments' => $this->formatAttachments($memo),
                'cover' => $this->formatCover($memo),
                'breadcrumbs' => $this->folderBreadcrumbs($memo->folder),
            ],
        ]);
    }

    public function edit(Memo $memo): Response
    {
        Gate::authorize('update', $memo);
        $memo->load(['tags:id,name,slug', 'folder:id,name,parent_id', 'attachments:id,memo_id,name,mime_type,size,created_at']);

        return Inertia::render('Memos/Edit', [
            'memo' => [
                ...$memo->only('id', 'title', 'content', 'formatting', 'is_favorite', 'icon', 'cover_attachment_id', 'is_full_width'),
                'tags' => $this->formatTags($memo),
                'folder' => $memo->folder ? $memo->folder->only('id', 'name', 'parent_id') : null,
                'attachments' => $this->formatAttachments($memo),
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

    public function duplicate(Memo $memo): RedirectResponse
    {
        Gate::authorize('view', $memo);

        $copy = DB::transaction(function () use ($memo) {
            $memo->loadMissing(['attachments', 'tags']);
            $copy = $memo->replicate();
            $copy->title = Str::limit($memo->title . ' — Copie', 255, '');
            $copy->is_favorite = false;
            $copy->cover_attachment_id = null;
            $copy->save();

            foreach ($memo->attachments as $attachment) {
                $newAttachment = $attachment->replicate();
                $newAttachment->memo_id = $copy->id;
                $newAttachment->user_id = $copy->user_id;
                $newAttachment->save();
                if ($memo->cover_attachment_id === $attachment->id) {
                    $copy->cover_attachment_id = $newAttachment->id;
                }
            }

            $copy->save();
            $copy->tags()->sync($memo->tags->modelKeys());

            return $copy;
        });

        return redirect()->route('memos.edit', $copy)->with('success', 'Fiche dupliquée.');
    }

    public function bulk(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1', 'max:100'],
            'ids.*' => ['integer'],
            'action' => ['required', 'in:move,favorite,unfavorite,delete,restore,force_delete'],
            'folder_id' => ['nullable', 'integer'],
        ]);

        $query = in_array($data['action'], ['restore', 'force_delete'], true)
            ? $request->user()->memos()->withTrashed()->onlyTrashed()
            : $request->user()->memos();
        $memos = $query->whereIn('id', $data['ids'])->get();

        if ($data['action'] === 'move' && ($data['folder_id'] ?? null) !== null) {
            abort_unless($request->user()->memoFolders()->whereKey($data['folder_id'])->exists(), 422);
        }

        DB::transaction(function () use ($memos, $data) {
            foreach ($memos as $memo) {
                Gate::authorize(in_array($data['action'], ['delete', 'restore', 'force_delete'], true) ? 'delete' : 'update', $memo);

                match ($data['action']) {
                    'move' => $memo->update(['folder_id' => $data['folder_id'] ?? null]),
                    'favorite' => $memo->update(['is_favorite' => true]),
                    'unfavorite' => $memo->update(['is_favorite' => false]),
                    'delete' => $memo->delete(),
                    'restore' => $memo->restore(),
                    'force_delete' => $memo->forceDelete(),
                    default => null,
                };
            }
        });

        return back()->with('success', count($memos) . ' fiche(s) mise(s) à jour.');
    }

    public function forceDestroy(Request $request, int $memo): RedirectResponse
    {
        $model = $request->user()->memos()->withTrashed()->findOrFail($memo);
        Gate::authorize('delete', $model);
        abort_unless($model->trashed(), 404);
        $model->forceDelete();

        return back()->with('success', 'Fiche supprimée définitivement.');
    }

    public function emptyTrash(Request $request): RedirectResponse
    {
        $models = $request->user()->memos()->onlyTrashed()->get();

        DB::transaction(function () use ($models) {
            foreach ($models as $memo) {
                Gate::authorize('delete', $memo);
                $memo->forceDelete();
            }
        });

        return back()->with('success', 'Corbeille vidée.');
    }

    public function restore(Request $request, int $memo): RedirectResponse
    {
        $model = $request->user()->memos()->withTrashed()->findOrFail($memo);
        Gate::authorize('delete', $model);
        abort_unless($model->trashed(), 404);
        $model->restore();

        return redirect()->route('memos.index')->with('success', 'Fiche restaurée.');
    }

    /**
     * Glisser-déposer d'une fiche sur un dossier : ne change QUE le rangement,
     * sans repasser par la validation du titre/contenu.
     */
    public function move(Request $request, Memo $memo): RedirectResponse
    {
        Gate::authorize('update', $memo);

        $data = $request->validate([
            'folder_id' => ['nullable', 'integer'],
        ]);

        if ($data['folder_id'] !== null) {
            abort_unless(
                $request->user()->memoFolders()->whereKey($data['folder_id'])->exists(),
                404
            );
        }

        $memo->update(['folder_id' => $data['folder_id']]);

        return back()->with('success', 'Fiche déplacée.');
    }

    private function assertFolderBelongsToUser(Request $request): void
    {
        $folderId = $request->input('folder_id');
        if ($folderId !== null && ! $request->user()->memoFolders()->whereKey($folderId)->exists()) {
            abort(422, 'Le dossier sélectionné est invalide.');
        }

        $coverId = $request->input('cover_attachment_id');
        if ($coverId !== null && ! $request->user()->memoAttachments()->whereKey($coverId)->exists()) {
            abort(422, 'La couverture sélectionnée est invalide.');
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

    private function formatCover(Memo $memo): ?array
    {
        $attachment = $memo->cover_attachment_id
            ? $memo->attachments->firstWhere('id', $memo->cover_attachment_id)
            : null;

        return $attachment && $attachment->isImage()
            ? ['id' => $attachment->id, 'url' => '/memos/attachments/' . $attachment->id, 'name' => $attachment->name]
            : null;
    }

    private function folderBreadcrumbs(?MemoFolder $folder): array
    {
        $items = [];
        $current = $folder;
        $guard = 0;
        while ($current && $guard++ < 30) {
            array_unshift($items, $current->only('id', 'name', 'parent_id'));
            $current = $current->parent;
        }
        return $items;
    }

    private function formatTags(Memo $memo): array
    {
        return $memo->tags
            ->map(fn ($tag) => ['id' => $tag->id, 'name' => $tag->name, 'slug' => $tag->slug])
            ->values()
            ->all();
    }
}
