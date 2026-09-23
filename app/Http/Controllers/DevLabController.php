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
        ?string $workspaceLanguage = null,
        ?array $workspaceFiles = null
    ): array {
        $profiles = [
            'laravel' => ['label' => 'Laravel', 'filename' => 'routes/web.php', 'preview' => false],
            'php' => ['label' => 'PHP', 'filename' => 'main.php', 'preview' => false],
            'node' => ['label' => 'Node.js', 'filename' => 'main.js', 'preview' => false],
            'javascript' => ['label' => 'JavaScript', 'filename' => 'main.js', 'preview' => true],
            'typescript' => ['label' => 'TypeScript', 'filename' => 'main.ts', 'preview' => false],
            'react' => ['label' => 'React', 'filename' => 'src/App.jsx', 'preview' => false],
            'nextjs' => ['label' => 'Next.js', 'filename' => 'app/page.tsx', 'preview' => false],
            'html' => ['label' => 'HTML', 'filename' => 'index.html', 'preview' => true],
            'css' => ['label' => 'CSS', 'filename' => 'styles.css', 'preview' => true],
            'tailwind' => ['label' => 'Tailwind CSS', 'filename' => 'index.html', 'preview' => true],
            'git' => ['label' => 'Git', 'filename' => 'README.md', 'preview' => false],
            'github' => ['label' => 'GitHub', 'filename' => 'README.md', 'preview' => false],
            'docker' => ['label' => 'Docker', 'filename' => 'Dockerfile', 'preview' => false],
            'mysql' => ['label' => 'MySQL', 'filename' => 'schema.sql', 'preview' => false],
            'postgresql' => ['label' => 'PostgreSQL', 'filename' => 'schema.sql', 'preview' => false],
        ];
        $key = $workspaceLanguage ?? $technology;
        $profile = $profiles[$key] ?? null;
        if ($profile === null) {
            return ['enabled'=>false,'runtime'=>'browser','preview_enabled'=>false,'language'=>null,'label'=>null,'filename'=>null,'run_command'=>null,'initial_code'=>'','files'=>[]];
        }
        $files = collect($workspaceFiles ?: [])->filter(fn($file)=>is_array($file)&&!empty($file['path']))
            ->map(fn($file)=>['path'=>$file['path'],'content'=>(string)($file['content']??'')])->values()->all();
        if ($files === []) {
            $files = [['path'=>$workspaceFile ?: $profile['filename'],'content'=>$codeExample ?: '']];
        }
        return [
            'enabled'=>true,
            'runtime'=>in_array($key,['laravel','php','node'],true)?'server':'browser',
            'preview_enabled'=>$profile['preview'],
            'language'=>$key,
            'label'=>$profile['label'],
            'filename'=>$files[0]['path'] ?? $profile['filename'],
            'run_command'=>in_array($key,['laravel','php','node'],true) ? ($key==='laravel'?'php artisan route:list':($key==='node'?'node '.$files[0]['path']:'php '.$files[0]['path'])) : null,
            'initial_code'=>$files[0]['content'] ?? '',
            'files'=>$files,
        ];
    }

}
