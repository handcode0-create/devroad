<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoadmapStepRequest;
use App\Http\Requests\UpdateRoadmapStepRequest;
use App\Http\Requests\UpdateStepStatusRequest;
use App\Http\Requests\UpdateStepExerciseRequest;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Http\Requests\RunCodeRequest;
use App\Services\DevLabRuntimeService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapStepController extends Controller
{
    /**
     * Formulaire de création d'un cours.
     */
    public function create(Roadmap $roadmap): Response
    {
        $this->authorize('update', $roadmap);

        return Inertia::render('Steps/Create', [
            'roadmap' => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'technology' => $roadmap->technology,
            ],
            'next_position' => ($roadmap->steps()->max('position') ?? 0) + 1,
        ]);
    }

    /**
     * Formulaire de modification d'un cours.
     */
    public function edit(RoadmapStep $step): Response
    {
        $this->authorize('update', $step);
        $step->load('roadmap');

        return Inertia::render('Steps/Edit', [
            'roadmap' => [
                'id' => $step->roadmap->id,
                'title' => $step->roadmap->title,
                'technology' => $step->roadmap->technology,
            ],
            'step' => [
                'id' => $step->id,
                'title' => $step->title,
                'description' => $step->description,
                'objective' => $step->objective,
                'content' => $step->content,
                'code_example' => $step->code_example,
                'workspace_file' => $step->workspace_file,
                'workspace_language' => $step->workspace_language,
                'workspace_files' => $step->workspace_files ?? [],
                'exercise_title' => $step->exercise_title,
                'exercise_description' => $step->exercise_description,
                'exercise_hint' => $step->exercise_hint,
                'exercise_solution' => $step->exercise_solution,
                'estimated_minutes' => $step->estimated_minutes,
                'position' => $step->position,
                'status' => $step->status,
                'difficulty_level' => $step->difficulty_level,
                'academic_level' => $step->academic_level,
            ],
        ]);
    }

    /**
     * Afficher le cours d'une étape.
     */
    public function show(RoadmapStep $step): Response|RedirectResponse
    {
        $this->authorize('view', $step);

        $step->load([
            'roadmap',
            'devLabProject',
        ]);

        $roadmap = $step->roadmap;

        $currentStep = $roadmap
            ->steps()
            ->where('status', '!=', RoadmapStep::COMPLETED)
            ->orderBy('position')
            ->first();

        if ($step->status === RoadmapStep::TODO) {
            $step->update([
                'status' => RoadmapStep::IN_PROGRESS,
            ]);

            if ($roadmap->status === 'draft') {
                $roadmap->update(['status' => 'active']);
            }
        }

        if (! $step->exercise_title) {
            $this->ensureExercise($step);
        }

        $step->updateQuietly([
            'last_viewed_at' => now(),
        ]);

        $step->refresh();

        $previousStep = $roadmap
            ->steps()
            ->where('position', '<', $step->position)
            ->orderByDesc('position')
            ->first();

        $nextStep = $roadmap
            ->steps()
            ->where('position', '>', $step->position)
            ->orderBy('position')
            ->first();

        return Inertia::render('Steps/Show', [
            'step' => fn () => [
                'id' => $step->id,
                'title' => $step->title,
                'description' => $step->description,
                'position' => $step->position,
                'status' => $step->status,
                'difficulty_level' => $step->difficulty_level,
                'academic_level' => $step->academic_level,
                'objective' => $step->objective,
                'content' => $step->content,
                'code_example' => $step->code_example,
                'estimated_minutes' => $step->estimated_minutes,
                'devlab_project' => $step->devLabProject
                    ? [
                        'id' => $step->devLabProject->id,
                        'name' => $step->devLabProject->name,
                    ]
                    : null,
                'workspace' => $this->workspaceFor(
                    $roadmap->technology,
                    $step->code_example,
                    $step->workspace_file,
                    $step->workspace_language,
                    $step->workspace_files,
                ),
                'exercise' => $step->exercise_title ? [
                    'title' => $step->exercise_title,
                    'description' => $step->exercise_description,
                    'hint' => $step->exercise_hint,
                    'solution' => $step->exercise_solution,
                    'completed' => $step->exercise_completed_at !== null,
                ] : null,
            ],

            'roadmap' => fn () => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'technology' => $roadmap->technology,
                'steps_count' => $roadmap->steps()->count(),
                'completed_steps_count' => $roadmap->steps()
                    ->where('status', RoadmapStep::COMPLETED)
                    ->count(),
            ],

            'previous_step' => fn () => $previousStep
                ? [
                    'id' => $previousStep->id,
                    'title' => $previousStep->title,
                ]
                : null,

            'next_step' => fn () => $nextStep
                ? [
                    'id' => $nextStep->id,
                    'title' => $nextStep->title,
                ]
                : null,
        ]);
    }

    /**
     * Exécuter le code de la leçon dans le sandbox DevRoad.
     */
    public function runCode(
        RunCodeRequest $request,
        RoadmapStep $step
    ): JsonResponse {
        $this->authorize('view', $step);

        if ($this->isLockedForProgression($step)) {
            return response()->json([
                'status' => 'locked',
                'stdout' => '',
                'stderr' => 'Cette leçon est encore verrouillée.',
                'exit_code' => null,
                'duration_ms' => 0,
            ], 403);
        }

        $technology = $step->roadmap?->technology;
        $validated = $request->validated();
        $language = $validated['language'];

        if (! in_array($language, $this->workspaceLanguages($technology), true)) {
            return response()->json([
                'status' => 'unsupported',
                'stdout' => '',
                'stderr' => 'Ce langage n’est pas encore disponible pour cette technologie.',
                'exit_code' => null,
                'duration_ms' => 0,
            ], 422);
        }

        $runtime = $this->workspaceRuntime(
            $technology,
            $step->workspace_language,
        );

        if ($runtime !== 'server') {
            return response()->json([
                'status' => 'unsupported',
                'stdout' => '',
                'stderr' => 'Cette leçon utilise un runtime navigateur.',
                'exit_code' => null,
                'duration_ms' => 0,
            ], 422);
        }

        return response()->json(
            app(DevLabRuntimeService::class)->run(
                $language,
                $request->user()->id,
                $step->id,
                $validated['files'] ?? [[
                    'path' => $validated['file_path'] ?? 'main.' . ($language === 'node' ? 'js' : 'php'),
                    'content' => $validated['code'] ?? '',
                ]],
                $validated['file_path']
                    ?? ($step->workspace_file ?: ($language === 'node' ? 'main.js' : 'main.php')),
                $validated['command']
                    ?? match ($language) {
                        'laravel' => 'php artisan route:list',
                        'node' => 'node ' . ($step->workspace_file ?: 'main.js'),
                        'php' => 'php ' . ($step->workspace_file ?: 'main.php'),
                        default => 'run',
                    },
            )
        );
    }

    private function workspaceRuntime(
        ?string $technology,
        ?string $workspaceLanguage
    ): string {
        return match ($workspaceLanguage ?? $technology) {
            'node', 'php', 'laravel' => 'server',
            default => 'browser',
        };
    }

    private function workspaceFor(
        ?string $technology,
        ?string $codeExample,
        ?string $workspaceFile = null,
        ?string $workspaceLanguage = null,
        ?array $workspaceFiles = null,
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
                'run_command' => 'node main.js',
                'starter' => "console.log('Bonjour DevRoad');\n",
                'preview' => true,
            ],
            'typescript' => [
                'language' => 'typescript',
                'label' => 'TypeScript',
                'filename' => $workspaceFile ?: 'src/main.ts',
                'run_command' => 'run',
                'starter' => "const message: string = 'Bonjour DevRoad';\nconsole.log(message);\n",
                'preview' => false,
            ],
            'javascript' => [
                'language' => 'javascript',
                'label' => 'JavaScript',
                'filename' => $workspaceFile ?: 'main.js',
                'run_command' => 'node main.js',
                'starter' => "console.log('Bonjour DevRoad');\n",
                'preview' => true,
            ],
            'react' => [
                'language' => 'react',
                'label' => 'React',
                'filename' => $workspaceFile ?: 'src/App.jsx',
                'run_command' => 'run',
                'starter' => "export default function App() {\n    return <h1>Bonjour DevRoad</h1>;\n}\n",
                'preview' => false,
            ],
            'nextjs' => [
                'language' => 'nextjs',
                'label' => 'Next.js',
                'filename' => $workspaceFile ?: 'app/page.tsx',
                'run_command' => 'run',
                'starter' => "export default function Page() {\n    return <h1>Bonjour DevRoad</h1>;\n}\n",
                'preview' => false,
            ],
            'tailwind' => [
                'language' => 'tailwind',
                'label' => 'Tailwind CSS',
                'filename' => $workspaceFile ?: 'index.html',
                'run_command' => 'preview',
                'starter' => "<main class=\"p-6\"><h1 class=\"text-2xl font-bold\">Bonjour DevRoad</h1></main>\n",
                'preview' => true,
            ],
            'git' => [
                'language' => 'git',
                'label' => 'Git',
                'filename' => $workspaceFile ?: 'README.md',
                'run_command' => 'run',
                'starter' => "# DevRoad\n",
                'preview' => false,
            ],
            'github' => [
                'language' => 'github',
                'label' => 'GitHub',
                'filename' => $workspaceFile ?: 'README.md',
                'run_command' => 'run',
                'starter' => "# DevRoad\n",
                'preview' => false,
            ],
            'docker' => [
                'language' => 'docker',
                'label' => 'Docker',
                'filename' => $workspaceFile ?: 'Dockerfile',
                'run_command' => 'run',
                'starter' => "FROM node:22-alpine\nWORKDIR /app\n",
                'preview' => false,
            ],
            'mysql' => [
                'language' => 'mysql',
                'label' => 'MySQL',
                'filename' => $workspaceFile ?: 'schema.sql',
                'run_command' => 'run',
                'starter' => "CREATE TABLE courses (id BIGINT PRIMARY KEY);\n",
                'preview' => false,
            ],
            'postgresql' => [
                'language' => 'postgresql',
                'label' => 'PostgreSQL',
                'filename' => $workspaceFile ?: 'schema.sql',
                'run_command' => 'run',
                'starter' => "CREATE TABLE courses (id BIGSERIAL PRIMARY KEY);\n",
                'preview' => false,
            ],
            default => null,
        };

        if ($profile === null) {
            return [
                'enabled' => false,
                'runtime' => $this->workspaceRuntime($technology, $workspaceLanguage),
                'preview_enabled' => false,
                'language' => null,
                'label' => null,
                'filename' => null,
                'run_command' => null,
                'initial_code' => '',
                'files' => [],
            ];
        }

        $files = collect($workspaceFiles ?: [])
            ->filter(fn ($file) => is_array($file) && ! empty($file['path']))
            ->map(fn ($file) => [
                'path' => $file['path'],
                'content' => (string) ($file['content'] ?? ''),
            ])
            ->values()
            ->all();

        if ($files === []) {
            $files = [[
                'path' => $profile['filename'],
                'content' => $codeExample ?: $profile['starter'],
            ]];
        }

        return [
            'enabled' => true,
            'runtime' => $this->workspaceRuntime($technology, $workspaceLanguage),
            'preview_enabled' => $profile['preview'],
            'language' => $profile['language'],
            'label' => $profile['label'],
            'filename' => $files[0]['path'] ?? $profile['filename'],
            'run_command' => $profile['run_command'],
            'initial_code' => $files[0]['content'] ?? ($codeExample ?: $profile['starter']),
            'files' => $files,
        ];
    }

    private function workspaceLanguages(?string $technology): array
    {
        return match ($technology) {
            'laravel' => ['laravel'],
            'php' => ['php'],
            'node' => ['node'],
            'javascript' => ['javascript'],
            'react' => ['react'],
            'nextjs' => ['nextjs'],
            'typescript' => ['typescript'],
            'html' => ['html'],
            'css' => ['css'],
            'tailwind' => ['tailwind'],
            'git' => ['git'],
            'github' => ['github'],
            'docker' => ['docker'],
            'mysql' => ['mysql'],
            'postgresql' => ['postgresql'],
            default => [],
        };
    }

    /**
     * Création d'une étape.
     */
    public function store(
        StoreRoadmapStepRequest $request,
        Roadmap $roadmap
    ): RedirectResponse {
        $this->authorize('update', $roadmap);

        $data = $request->validated();

        if (!array_key_exists('position', $data)) {
            $lastPosition = $roadmap->steps()->max('position');

            $data['position'] = $lastPosition === null
                ? 1
                : $lastPosition + 1;
        }

        $roadmap->steps()->create($data);

        return redirect()
            ->route('roadmaps.show', $roadmap)
            ->with('success', 'Étape ajoutée.');
    }

    /**
     * Mise à jour d'une étape.
     */
    public function update(
        UpdateRoadmapStepRequest $request,
        RoadmapStep $step
    ): RedirectResponse {
        $this->authorize('update', $step);

        $step->update(
            $request->validated()
        );

        return redirect()
            ->route('roadmaps.show', $step->roadmap_id)
            ->with('success', 'Étape mise à jour.');
    }

    /**
     * Mise à jour rapide du statut.
     */
    public function updateStatus(
        UpdateStepStatusRequest $request,
        RoadmapStep $step
    ): RedirectResponse {
        $this->authorize('update', $step);

        $status = $request->validated('status');

        if ($this->isLockedForProgression($step)) {
            return back()->withErrors([
                'status' => 'Cette leçon est encore verrouillée.',
            ]);
        }

        if (
            $status === RoadmapStep::COMPLETED &&
            $step->exercise_title &&
            ! $step->exercise_completed_at
        ) {
            return back()->withErrors([
                'status' => "Termine d'abord l'exercice de cette leçon.",
            ]);
        }

        DB::transaction(function () use ($step, $status) {
            $step->update([
                'status' => $status,
            ]);

            $roadmap = $step->roadmap()->first();

            if ($status === RoadmapStep::COMPLETED) {
                $nextStep = RoadmapStep::query()
                    ->where('roadmap_id', $step->roadmap_id)
                    ->where('position', '>', $step->position)
                    ->where('status', '!=', RoadmapStep::COMPLETED)
                    ->orderBy('position')
                    ->first();

                if ($nextStep && $nextStep->status === RoadmapStep::TODO) {
                    $nextStep->update([
                        'status' => RoadmapStep::IN_PROGRESS,
                    ]);
                }

                $hasIncompleteSteps = RoadmapStep::query()
                    ->where('roadmap_id', $step->roadmap_id)
                    ->where('status', '!=', RoadmapStep::COMPLETED)
                    ->exists();

                if ($roadmap && ! $hasIncompleteSteps && $roadmap->status !== 'archived') {
                    $roadmap->update(['status' => 'completed']);
                } elseif ($roadmap && $roadmap->status === 'draft') {
                    $roadmap->update(['status' => 'active']);
                }
            } elseif ($roadmap && $roadmap->status === 'completed') {
                $roadmap->update(['status' => 'active']);
            }
        });

        return back()->with(
            'success',
            'Progression mise à jour.'
        );
    }

    /**
     * Valider ou réouvrir l'exercice d'une étape.
     */
    public function updateExercise(
        UpdateStepExerciseRequest $request,
        RoadmapStep $step
    ): RedirectResponse {
        $this->authorize('update', $step);

        if (! $step->exercise_title) {
            return back();
        }

        if ($this->isLockedForProgression($step)) {
            return back()->withErrors([
                'completed' => 'Cette leçon est encore verrouillée.',
            ]);
        }

        $completed = $request->boolean('completed');

        $step->update([
            'exercise_completed_at' => $completed ? now() : null,
        ]);

        if (! $completed && $step->status === RoadmapStep::COMPLETED) {
            $step->update([
                'status' => RoadmapStep::IN_PROGRESS,
            ]);

            if ($step->roadmap->status === 'completed') {
                $step->roadmap->update(['status' => 'active']);
            }
        }

        return back()->with(
            'success',
            $completed ? 'Exercice validé.' : 'Exercice réouvert.'
        );
    }

    private function ensureExercise(RoadmapStep $step): void
    {
        $step->update([
            'exercise_title' => "Exercice — {$step->title}",
            'exercise_description' => $step->objective
                ? "Mets en pratique l'objectif suivant : {$step->objective}"
                : "Crée un petit exemple pratique pour démontrer que tu maîtrises « {$step->title} ».",
            'exercise_hint' => 'Commence par écrire une version minimale, puis vérifie que tu peux expliquer chaque partie de ta solution.',
            'exercise_solution' => $step->code_example
                ?: "Construis un exemple minimal lié à « {$step->title} ».",
        ]);
    }

    private function isLockedForProgression(RoadmapStep $step): bool
    {
        if ($step->status === RoadmapStep::COMPLETED) {
            return false;
        }

        $currentStep = $step->roadmap
            ->steps()
            ->where('status', '!=', RoadmapStep::COMPLETED)
            ->orderBy('position')
            ->first();

        return $currentStep
            && $currentStep->id !== $step->id
            && $step->position > $currentStep->position;
    }

    /**
     * Suppression.
     */
    public function destroy(
        RoadmapStep $step
    ): RedirectResponse {
        $this->authorize('delete', $step);

        $roadmapId = $step->roadmap_id;

        $step->delete();

        return redirect()
            ->route('roadmaps.show', $roadmapId)
            ->with('success', 'Étape supprimée.');
    }
}
