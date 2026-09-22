<?php

namespace App\Http\Controllers;

use App\Models\MemoFolder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

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
            'position' => $this->endOfGroup($request, $parentId),
        ]);

        return back()->with('success', 'Dossier créé.');
    }

    public function update(Request $request, MemoFolder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 404);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:120'],
            // present (pas nullable seul) : on doit pouvoir distinguer
            // « champ absent, ne pas y toucher » de « remettre à la racine ».
            'parent_id' => ['sometimes', 'present', 'nullable', 'integer'],
        ]);

        $changingParent = array_key_exists('parent_id', $data);
        $parentId = $changingParent ? $data['parent_id'] : $folder->parent_id;

        if ($parentId !== null) {
            abort_unless($request->user()->memoFolders()->whereKey($parentId)->exists(), 404);
            abort_if((int) $parentId === (int) $folder->id, 422, 'Un dossier ne peut pas être son propre parent.');
            abort_if(
                $this->isDescendantOf($folder, (int) $parentId),
                422,
                'Un dossier ne peut pas être déplacé dans l\'un de ses propres sous-dossiers.'
            );
        }

        $attributes = [];
        if (array_key_exists('name', $data)) {
            $attributes['name'] = trim($data['name']);
        }
        if ($changingParent && (int) ($folder->parent_id ?? 0) !== (int) ($parentId ?? 0)) {
            // On change de groupe de frères : direction la fin du nouveau groupe.
            // Une réorganisation précise se fait ensuite avec reorder().
            $attributes['parent_id'] = $parentId;
            $attributes['position'] = $this->endOfGroup($request, $parentId, exclude: $folder->id);
        } elseif ($changingParent) {
            $attributes['parent_id'] = $parentId;
        }

        $folder->update($attributes);

        return back()->with('success', 'Dossier mis à jour.');
    }

    /**
     * Glisser-déposer : nouvel ordre d'un groupe de dossiers frères (même parent).
     * orderedIds doit contenir EXACTEMENT les dossiers de ce groupe, dans le
     * nouvel ordre voulu ; la position de chacun devient son index dans la liste.
     */
    public function reorder(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'parent_id' => ['nullable', 'integer'],
            'ordered_ids' => ['required', 'array', 'min:1'],
            'ordered_ids.*' => ['integer'],
        ]);

        $parentId = $data['parent_id'] ?? null;

        $siblings = $request->user()->memoFolders()
            ->where('parent_id', $parentId)
            ->pluck('id')
            ->all();

        $submitted = $data['ordered_ids'];

        abort_unless(
            count($submitted) === count($siblings) && empty(array_diff($submitted, $siblings)) && empty(array_diff($siblings, $submitted)),
            422,
            'La liste ne correspond pas aux dossiers de ce niveau.'
        );

        foreach ($submitted as $position => $id) {
            $request->user()->memoFolders()->whereKey($id)->update(['position' => $position]);
        }

        return back()->with('success', 'Dossiers réorganisés.');
    }

    public function destroy(Request $request, MemoFolder $folder): RedirectResponse
    {
        abort_unless($folder->user_id === $request->user()->id, 404);

        $request->user()->memos()->where('folder_id', $folder->id)->update(['folder_id' => null]);
        $folder->children()->update(['parent_id' => $folder->parent_id]);
        $folder->delete();

        return back()->with('success', 'Dossier supprimé.');
    }

    private function endOfGroup(Request $request, ?int $parentId, ?int $exclude = null): int
    {
        return (int) $request->user()->memoFolders()
            ->where('parent_id', $parentId)
            ->when($exclude, fn ($query) => $query->whereKeyNot($exclude))
            ->max('position') + 1;
    }

    /**
     * Vrai si $candidateParentId est $folder lui-même ou l'un de ses descendants
     * (empêche de créer une boucle en déposant un dossier dans son sous-dossier).
     */
    private function isDescendantOf(MemoFolder $folder, int $candidateParentId): bool
    {
        $current = MemoFolder::find($candidateParentId);

        while ($current !== null) {
            if ((int) $current->id === (int) $folder->id) {
                return true;
            }
            $current = $current->parent_id !== null ? MemoFolder::find($current->parent_id) : null;
        }

        return false;
    }
}
