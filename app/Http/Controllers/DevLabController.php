<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DevLabController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $roadmapModels = $request->user()
            ->roadmaps()
            ->withProgress()
            ->with([
                'steps' => fn ($query) => $query
                    ->reorder()
                    ->orderBy('position'),
            ])
            ->latest('updated_at')
            ->limit(12)
            ->get();

        $selectedRoadmap = $request->filled('roadmap')
            ? $roadmapModels->firstWhere('id', (int) $request->input('roadmap'))
            : null;

        $selectedRoadmap ??= $roadmapModels->first();

        $selectedStep = $selectedRoadmap?->steps
            ->first(fn (RoadmapStep $step) => $step->status !== RoadmapStep::COMPLETED)
            ?? $selectedRoadmap?->steps->first();

        $roadmaps = $roadmapModels->map(function (Roadmap $roadmap) {
            $currentStep = $roadmap->steps
                ->first(fn (RoadmapStep $step) => $step->status !== RoadmapStep::COMPLETED);

            $workspaceReady = in_array(
                $roadmap->technology,
                ['laravel', 'php', 'javascript', 'react', 'nextjs', 'html', 'css', 'node'],
                true
            );

            return [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'technology' => $roadmap->technology,
                'status' => $roadmap->status,
                'progress' => $roadmap->progress,
                'steps_count' => $roadmap->steps_count,
                'completed_steps_count' => $roadmap->completed_steps_count,
                'workspace_ready' => $workspaceReady,
                'current_step' => $currentStep
                    ? [
                        'id' => $currentStep->id,
                        'title' => $currentStep->title,
                        'position' => $currentStep->position,
                    ]
                    : null,
            ];
        })->values();

        $projects = $request->user()
            ->devLabProjects()
            ->withCount('files')
            ->latest('last_opened_at')
            ->latest('id')
            ->get(['id', 'name', 'template', 'runtime', 'description', 'last_opened_at']);

        return Inertia::render('DevLab/Index', [
            'projects' => $projects,
            'open_project_id' => $request->integer('project') ?: null,

            'roadmaps' => $roadmaps,
            'active_roadmap' => $selectedRoadmap
                ? [
                    'id' => $selectedRoadmap->id,
                    'title' => $selectedRoadmap->title,
                    'technology' => $selectedRoadmap->technology,
                    'progress' => $selectedRoadmap->progress,
                ]
                : null,
            'active_step' => $selectedStep
                ? [
                    'id' => $selectedStep->id,
                    'title' => $selectedStep->title,
                    'position' => $selectedStep->position,
                    'status' => $selectedStep->status,
                    'workspace' => $this->workspaceFor(
                        $selectedRoadmap?->technology,
                        $selectedStep->code_example,
                        $selectedStep->workspace_file,
                        $selectedStep->workspace_language,
                    ),
                ]
                : null,
        ]);
    }

    private function workspaceFor(
        ?string $technology,
        ?string $codeExample,
        ?string $workspaceFile = null,
        ?string $workspaceLanguage = null
    ): array {
        $profile = match ($workspaceLanguage ?? $technology) {
            'laravel' => [
                'language' => 'laravel',
                'label' => 'Laravel',
                'filename' => $workspaceFile ?: 'routes/web.php',
                'run_command' => 'php artisan route:list',
                'starter' => "<?php\n\nuse Illuminate\\Support\\Facades\\Route;\n\n",
                'preview' => false,
            ],
            'php' => [
                'language' => 'php',
                'label' => 'PHP',
                'filename' => $workspaceFile ?: 'main.php',
                'run_command' => 'php main.php',
                'starter' => "<?php\n\n",
                'preview' => false,
            ],
            'node' => [
                'language' => 'node',
                'label' => 'Node.js',
                'filename' => $workspaceFile ?: 'main.js',
                'run_command' => 'node main.js',
                'starter' => "console.log('Bonjour DevRoad');\n",
                'preview' => false,
            ],
            'html' => [
                'language' => 'html',
                'label' => 'HTML',
                'filename' => $workspaceFile ?: 'index.html',
                'run_command' => 'preview',
                'starter' => "<!doctype html>\n<html lang=\"fr\">\n<head>\n    <meta charset=\"UTF-8\">\n</head>\n<body>\n    <h1>Bonjour DevRoad</h1>\n</body>\n</html>",
                'preview' => true,
            ],
            'css' => [
                'language' => 'css',
                'label' => 'CSS',
                'filename' => $workspaceFile ?: 'styles.css',
                'run_command' => 'preview',
                'starter' => "body {\n    font-family: system-ui, sans-serif;\n}",
                'preview' => true,
            ],
            'javascript' => [
                'language' => 'javascript',
                'label' => 'JavaScript',
                'filename' => $workspaceFile ?: 'main.js',
                'run_command' => 'run',
                'starter' => "console.log('Bonjour DevRoad');\n",
                'preview' => true,
            ],
            default => null,
        };

        if ($profile === null) {
            return [
                'enabled' => false,
                'runtime' => 'browser',
                'preview_enabled' => false,
                'language' => null,
                'label' => null,
                'filename' => null,
                'run_command' => null,
                'initial_code' => '',
                'files' => [],
            ];
        }

        $runtime = in_array($profile['language'], ['node', 'php', 'laravel'], true)
            ? 'server'
            : 'browser';

        return [
            'enabled' => true,
            'runtime' => $runtime,
            'preview_enabled' => $profile['preview'],
            'language' => $profile['language'],
            'label' => $profile['label'],
            'filename' => $profile['filename'],
            'run_command' => $profile['run_command'],
            'initial_code' => $codeExample ?: $profile['starter'],
            'files' => [
                [
                    'path' => $profile['filename'],
                    'content' => $codeExample ?: $profile['starter'],
                ],
            ],
        ];
    }
}
