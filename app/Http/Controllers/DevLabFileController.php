<?php

namespace App\Http\Controllers;

use App\Models\DevLabFile;
use App\Models\DevLabProject;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DevLabFileController extends Controller
{
    private const MAX_PATH_LENGTH = 180;
    private const MAX_FILE_SIZE = 512 * 1024;
    private const MAX_FILES_PER_PROJECT = 100;

    public function store(Request $request, DevLabProject $project): JsonResponse
    {
        $this->authorize('manageFiles', $project);
        $validated = $this->validateFile($request, $project);
        if ($project->files()->count() >= self::MAX_FILES_PER_PROJECT) {
            return response()->json(['message' => 'Limite de fichiers atteinte.'], 422);
        }
        $file = $project->files()->create(['path' => $validated['path'], 'content' => $validated['content'], 'size' => strlen($validated['content'])]);
        return response()->json(['file' => $file], 201);
    }

    public function update(Request $request, DevLabProject $project, DevLabFile $file): JsonResponse
    {
        $this->authorizeFile($project, $file);
        $validated = $this->validateFile($request, $project, $file);
        $file->update(['path' => $validated['path'], 'content' => $validated['content'], 'size' => strlen($validated['content'])]);
        return response()->json(['file' => $file->fresh()]);
    }

    public function destroy(Request $request, DevLabProject $project, DevLabFile $file): JsonResponse
    {
        $this->authorizeFile($project, $file);
        if ($project->files()->count() <= 1) return response()->json(['message' => 'Un projet doit conserver au moins un fichier.'], 422);
        $file->delete();
        return response()->json([], 204);
    }

    private function authorizeFile(DevLabProject $project, DevLabFile $file): void
    {
        abort_unless($file->devlab_project_id === $project->id, 404);
        $this->authorize('manageFiles', $project);
    }

    private function validateFile(Request $request, DevLabProject $project, ?DevLabFile $file = null): array
    {
        // Normaliser avant la règle unique : « foo\\bar » et « foo/bar »
        // désignent le même fichier dans le workspace.
        $request->merge([
            'path' => str_replace('\\', '/', trim((string) $request->input('path', ''))),
        ]);

        $validated = $request->validate([
            'path' => ['required', 'string', 'max:' . self::MAX_PATH_LENGTH,
                Rule::unique('devlab_files', 'path')->where(fn ($query) => $query->where('devlab_project_id', $project->id))->ignore($file?->id)],
            // nullable : le middleware ConvertEmptyStringsToNull transforme '' en null,
            // or un fichier vide est valide (l'utilisateur peut tout effacer).
            'content' => ['present', 'nullable', 'string', 'max:' . self::MAX_FILE_SIZE],
        ]);
        $path = str_replace('\\', '/', trim($validated['path']));
        if ($path === '' || str_starts_with($path, '/') || preg_match('/^[A-Za-z]:\//', $path)
            || str_contains($path, '..') || preg_match('/(^|\/)\.env(?:\.|$)/i', $path)
            || preg_match('/(^|\/)(?:\.git|node_modules|vendor|storage)(?:\/|$)/i', $path)) {
            abort(422, 'Chemin de fichier invalide.');
        }
        $validated['path'] = $path;
        $validated['content'] = $validated['content'] ?? '';
        return $validated;
    }
}
