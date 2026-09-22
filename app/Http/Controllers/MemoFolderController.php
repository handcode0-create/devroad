<?php

namespace App\Http\Controllers;

use App\Models\MemoFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class MemoFolderController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $parentId = $data['parent_id'] ?? null;
        if ($parentId !== null) {
            abort_unless($request->user()->memoFolders()->whereKey($parentId)->exists(), 404);
        }

        $request->user()->memoFolders()->create([
            'name' => trim($data['name']),
            'parent_id' => $parentId,
            'icon' => 'folder',
            'position' => (int) $request->user()->memoFolders()->where('parent_id', $parentId)->max('position') + 1,
        ]);

        return back()->with('success', 'Dossier créé.');
    }

    public function update(Request $request, MemoFolder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'parent_id' => ['nullable', 'integer'],
        ]);

        $parentId = $data['parent_id'] ?? null;
        if ($parentId !== null) {
            abort_unless($request->user()->memoFolders()->whereKey($parentId)->exists(), 404);
            abort_if((int) $parentId === (int) $folder->id, 422, 'Un dossier ne peut pas être son propre parent.');
        }

        $folder->update(['name' => trim($data['name']), 'parent_id' => $parentId]);

        return back()->with('success', 'Dossier mis à jour.');
    }

    public function destroy(Request $request, MemoFolder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 404);

        $request->user()->memos()->where('folder_id', $folder->id)->update(['folder_id' => null]);
        $folder->children()->update(['parent_id' => $folder->parent_id]);
        $folder->delete();

        return back()->with('success', 'Dossier supprimé.');
    }
}