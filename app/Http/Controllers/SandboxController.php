<?php

namespace App\Http\Controllers;

use App\Contracts\SandboxExecutor;
use App\Exceptions\SandboxRuntimeUnavailable;
use App\Models\SandboxProject;
use App\Services\Sandbox\SandboxTemplateService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SandboxController extends Controller
{
    public function index(Request $request, SandboxTemplateService $templates): Response
    {
        $projects = $request->user()
            ->sandboxProjects()
            ->latest('updated_at')
            ->get([
                'id', 'name', 'template', 'runtime', 'runtime_version',
                'status', 'preview_url', 'last_started_at', 'last_stopped_at',
            ]);

        return Inertia::render('Sandbox/Index', [
            'projects' => $projects,
            'templates' => $templates->all(),
            'runtime_enabled' => (bool) config('sandbox.enabled'),
            'runtime_driver' => config('sandbox.driver'),
        ]);
    }

    public function store(Request $request, SandboxTemplateService $templates): JsonResponse
    {
        $this->authorize('create', SandboxProject::class);

        $validated = $request->validate([
            'name' => ['required', 'string', 'min:1', 'max:120'],
            'template' => ['required', 'string', 'in:' . implode(',', SandboxProject::TEMPLATES)],
        ]);

        $definition = $templates->definition($validated['template']);

        $project = $request->user()->sandboxProjects()->create([
            'name' => trim($validated['name']),
            'template' => $validated['template'],
            'runtime' => $definition['runtime'],
            'runtime_version' => $definition['version'],
            'status' => 'stopped',
            'settings' => [
                'region' => config('sandbox.default_region', 'auto'),
            ],
        ]);

        return response()->json(['project' => $project], 201);
    }

    public function show(Request $request, SandboxProject $project): JsonResponse
    {
        $this->authorize('view', $project);

        return response()->json([
            'project' => $project->load([
                'instances' => fn ($query) => $query->latest('id')->limit(5),
            ]),
        ]);
    }

    public function update(Request $request, SandboxProject $project): JsonResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'min:1', 'max:120'],
        ]);

        if (array_key_exists('name', $validated)) {
            $validated['name'] = trim($validated['name']);
        }

        $project->update($validated);

        return response()->json(['project' => $project->fresh()]);
    }

    public function command(Request $request, SandboxProject $project, SandboxExecutor $executor): JsonResponse
    {
        $this->authorize('run', $project);

        $validated = $request->validate([
            'command' => ['required', 'string', 'max:4000'],
            'timeout' => ['sometimes', 'integer', 'min:1', 'max:300'],
        ]);

        try {
            $result = $executor->executeCommand(
                $project,
                $validated['command'],
                (int) ($validated['timeout'] ?? 120)
            );
        } catch (SandboxRuntimeUnavailable) {
            return $this->runtimeUnavailable();
        }

        return response()->json([
            'output' => $result['output'],
            'exit_code' => $result['exit_code'],
        ]);
    }

    public function destroy(Request $request, SandboxProject $project, SandboxExecutor $executor): JsonResponse
    {
        $this->authorize('delete', $project);

        if ($project->activeInstance()->exists()) {
            try {
                $executor->destroy($project);
            } catch (SandboxRuntimeUnavailable) {
                return response()->json([
                    'message' => 'Arrête le Sandbox avant de le supprimer.',
                ], 409);
            }
        }

        $project->delete();

        return response()->json([], 204);
    }

    public function start(Request $request, SandboxProject $project, SandboxExecutor $executor): JsonResponse
    {
        return $this->runAction($request, $project, $executor, 'start');
    }

    public function stop(Request $request, SandboxProject $project, SandboxExecutor $executor): JsonResponse
    {
        return $this->runAction($request, $project, $executor, 'stop');
    }

    public function restart(Request $request, SandboxProject $project, SandboxExecutor $executor): JsonResponse
    {
        return $this->runAction($request, $project, $executor, 'restart');
    }

    public function status(Request $request, SandboxProject $project, SandboxExecutor $executor): JsonResponse
    {
        $this->authorize('view', $project);

        if (! config('sandbox.enabled')) {
            return response()->json([
                'project' => $project,
                'runtime_available' => false,
            ]);
        }

        try {
            $instance = $executor->status($project);
        } catch (SandboxRuntimeUnavailable) {
            return response()->json([
                'project' => $project,
                'runtime_available' => false,
            ]);
        }

        $project->updateQuietly(['status' => $instance->status]);

        return response()->json([
            'project' => $project->fresh(),
            'instance' => $instance,
            'runtime_available' => true,
        ]);
    }

    private function runAction(
        Request $request,
        SandboxProject $project,
        SandboxExecutor $executor,
        string $action
    ): JsonResponse {
        $this->authorize('run', $project);

        if (! config('sandbox.enabled')) {
            return $this->runtimeUnavailable();
        }

        try {
            $instance = $executor->{$action}($project);
        } catch (SandboxRuntimeUnavailable) {
            return $this->runtimeUnavailable();
        }

        $project->update([
            'status' => $instance->status,
            'last_started_at' => $action !== 'stop' ? now() : $project->last_started_at,
            'last_stopped_at' => $action === 'stop' ? now() : $project->last_stopped_at,
        ]);

        return response()->json([
            'project' => $project->fresh(),
            'instance' => $instance,
        ]);
    }

    private function runtimeUnavailable(): JsonResponse
    {
        return response()->json([
            'message' => 'Le runtime Sandbox n’est pas encore configuré sur cet environnement.',
            'runtime_available' => false,
        ], 503);
    }
}
