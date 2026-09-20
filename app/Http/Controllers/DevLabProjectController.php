<?php

namespace App\Http\Controllers;

use App\Models\DevLabFile;
use App\Models\DevLabProject;
use App\Services\DevLabProjectTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DevLabProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $projects = $request->user()->devLabProjects()->withCount('files')->latest('last_opened_at')->latest('id')->get();
        return response()->json(['projects' => $projects]);
    }

    public function store(Request $request, DevLabProjectTemplateService $templates): JsonResponse
    {
        $this->authorize('create', DevLabProject::class);
        $validated = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'template' => ['required', 'string', 'in:' . implode(',', DevLabProject::TEMPLATES)],
            'description' => ['nullable', 'string', 'max:500'],
        ]);
        $template = $templates->normalizeTemplate($validated['template']);
        $files = $templates->filesFor($template);

        $project = DB::transaction(function () use ($request, $validated, $template, $templates, $files) {
            $project = $request->user()->devLabProjects()->create([
                'name' => trim($validated['name']),
                'template' => $template,
                'runtime' => $templates->runtimeFor($template),
                'description' => $validated['description'] ?? null,
                'last_opened_at' => now(),
            ]);
            $project->files()->createMany(array_map(fn (array $file) => [
                'path' => $file['path'], 'content' => $file['content'], 'size' => strlen($file['content']),
            ], $files));
            return $project;
        });

        return response()->json(['project' => $project->load('files')], 201);
    }

    public function show(Request $request, DevLabProject $project): JsonResponse
    {
        $this->authorize('view', $project);
        $project->updateQuietly(['last_opened_at' => now()]);
        return response()->json(['project' => $project->load('files')]);
    }

    public function update(Request $request, DevLabProject $project): JsonResponse
    {
        $this->authorize('update', $project);
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:120'],
            'description' => ['sometimes', 'nullable', 'string', 'max:500'],
        ]);
        if (array_key_exists('name', $validated)) $validated['name'] = trim($validated['name']);
        $project->update($validated);
        return response()->json(['project' => $project->fresh()->load('files')]);
    }

    public function destroy(Request $request, DevLabProject $project): JsonResponse
    {
        $this->authorize('delete', $project);
        $project->delete();
        return response()->json([], 204);
    }

    public function importLegacy(Request $request): JsonResponse
    {
        $this->authorize('create', DevLabProject::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'template' => ['required', 'string', 'in:' . implode(',', DevLabProject::TEMPLATES)],
            'files' => ['required', 'array', 'min:1', 'max:100'],
            'files.*.path' => ['required', 'string', 'max:180'],
            'files.*.content' => ['required', 'string', 'max:' . (512 * 1024)],
        ]);

        $paths = [];
        $totalSize = 0;

        foreach ($validated['files'] as &$file) {
            $path = str_replace('\\', '/', trim($file['path']));

            if (
                $path === '' ||
                str_starts_with($path, '/') ||
                preg_match('/^[A-Za-z]:\\//', $path) ||
                str_contains($path, '..') ||
                preg_match('/(^|\\/)\\.env(?:\\.|$)/i', $path) ||
                preg_match('/(^|\\/)(?:\\.git|node_modules|vendor|storage)(?:\\/|$)/i', $path)
            ) {
                return response()->json(['message' => 'Chemin de fichier invalide.'], 422);
            }

            if (in_array($path, $paths, true)) {
                return response()->json(['message' => 'Deux fichiers portent le même chemin.'], 422);
            }

            $file['path'] = $path;
            $file['size'] = strlen($file['content']);
            $totalSize += $file['size'];
            $paths[] = $path;
        }
        unset($file);

        if ($totalSize > 5 * 1024 * 1024) {
            return response()->json(['message' => 'Taille totale de l’import trop élevée.'], 422);
        }

        $template = $validated['template'];
        $project = DB::transaction(function () use ($request, $validated, $template) {
            $project = $request->user()->devLabProjects()->create([
                'name' => trim($validated['name']),
                'template' => $template,
                'runtime' => in_array($template, ['node', 'php', 'laravel'], true) ? 'server' : 'browser',
                'last_opened_at' => now(),
            ]);

            $project->files()->createMany($validated['files']);

            return $project;
        });

        return response()->json(['project' => $project->load('files')], 201);
    }

    public function duplicate(Request $request, DevLabProject $project): JsonResponse
    {
        $this->authorize('view', $project);
        $duplicate = DB::transaction(function () use ($request, $project) {
            $copy = $request->user()->devLabProjects()->create([
                'name' => $project->name . ' — copie',
                'template' => $project->template,
                'runtime' => $project->runtime,
                'description' => $project->description,
                'last_opened_at' => now(),
            ]);
            $copy->files()->createMany($project->files->map(fn (DevLabFile $file) => [
                'path' => $file->path, 'content' => $file->content, 'size' => $file->size,
            ])->all());
            return $copy;
        });
        return response()->json(['project' => $duplicate->load('files')], 201);
    }
}
