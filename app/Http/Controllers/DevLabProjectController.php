<?php

namespace App\Http\Controllers;

use App\Models\DevLabFile;
use App\Models\DevLabProject;
use App\Models\RoadmapStep;
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

    public function openForStep(
        Request $request,
        RoadmapStep $step,
        DevLabProjectTemplateService $templates
    ): JsonResponse {
        $this->authorize('view', $step);

        $project = $request->user()
            ->devLabProjects()
            ->where('roadmap_step_id', $step->id)
            ->with('files')
            ->first();

        if ($project) {
            $project->updateQuietly(['last_opened_at' => now()]);

            return response()->json([
                'project' => $project->fresh()->load('files'),
                'created' => false,
            ]);
        }

        $template = match ($step->workspace_language ?? $step->roadmap->technology) {
            'laravel' => 'laravel',
            'php' => 'php',
            'node' => 'node',
            default => 'html',
        };

        $files = $templates->filesFor($template);
        $workspacePath = $step->workspace_file ?: match ($template) {
            'laravel' => 'routes/web.php',
            'php' => 'main.php',
            'node' => 'main.js',
            default => 'index.html',
        };

        if ($step->code_example) {
            $replaced = false;

            foreach ($files as &$file) {
                if ($file['path'] === $workspacePath) {
                    $file['content'] = $step->code_example;
                    $replaced = true;
                    break;
                }
            }
            unset($file);

            if (! $replaced) {
                $files[] = [
                    'path' => $workspacePath,
                    'content' => $step->code_example,
                ];
            }
        }

        $project = DB::transaction(function () use ($request, $step, $template, $templates, $files) {
            $project = $request->user()->devLabProjects()->create([
                'name' => 'Étape ' . $step->position . ' — ' . $step->title,
                'template' => $template,
                'runtime' => $templates->runtimeFor($template),
                'description' => 'Workspace DevLab lié à l’étape « ' . $step->title . ' ».',
                'roadmap_step_id' => $step->id,
                'last_opened_at' => now(),
            ]);

            $project->files()->createMany(array_map(
                fn (array $file) => [
                    'path' => $file['path'],
                    'content' => $file['content'],
                    'size' => strlen($file['content']),
                ],
                $files
            ));

            return $project;
        });

        return response()->json([
            'project' => $project->load('files'),
            'created' => true,
        ], 201);
    }

    public function importLegacy(Request $request): JsonResponse
    {
        $this->authorize('create', DevLabProject::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'template' => ['required', 'string', 'in:' . implode(',', DevLabProject::TEMPLATES)],
            'files' => ['required', 'array', 'min:1', 'max:100'],
            'files.*.path' => ['required', 'string', 'max:180'],
            'files.*.content' => ['present', 'nullable', 'string', 'max:' . (512 * 1024)],
        ]);

        $paths = [];
        $totalSize = 0;

        foreach ($validated['files'] as &$file) {
            $file['content'] = $file['content'] ?? '';
            $path = str_replace('\\', '/', trim($file['path']));

            $segments = array_values(array_filter(explode('/', $path), static fn (string $segment): bool => $segment !== ''));
            $lowerSegments = array_map('strtolower', $segments);
            $isWindowsAbsolute = strlen($path) >= 3
                && $path[1] === ':'
                && $path[2] === '/'
                && ctype_alpha($path[0]);

            $blocked = array_reduce(
                $lowerSegments,
                static fn (bool $carry, string $segment): bool =>
                    $carry
                    || $segment === '.git'
                    || $segment === 'node_modules'
                    || $segment === 'vendor'
                    || $segment === 'storage'
                    || str_starts_with($segment, '.env'),
                false
            );

            if ($path === '' || str_starts_with($path, '/') || $isWindowsAbsolute || str_contains($path, '..') || $blocked) {
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
