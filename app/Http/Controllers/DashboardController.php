<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $user = $request->user();

        // Toutes les étapes de l'utilisateur en UNE requête (via ses roadmaps)
        $steps = RoadmapStep::query()
            ->join('roadmaps', 'roadmaps.id', '=', 'roadmap_steps.roadmap_id')
            ->where('roadmaps.user_id', $user->id)
            ->selectRaw(
                'count(*) as total, sum(case when roadmap_steps.status = ? then 1 else 0 end) as completed',
                [RoadmapStep::COMPLETED]
            )
            ->first();

        $stepsTotal = (int) $steps->total;
        $stepsCompleted = (int) $steps->completed;

        $continueRoadmap = $user->roadmaps()
            ->withProgress()
            ->whereHas('steps', fn ($query) => $query->where('status', '!=', RoadmapStep::COMPLETED))
            ->latest('updated_at')
            ->first();

        if ($continueRoadmap) {
            $currentStep = $continueRoadmap->steps()
                ->where('status', '!=', RoadmapStep::COMPLETED)
                ->orderBy('position')
                ->first();

            $continueRoadmapData = [
                'id' => $continueRoadmap->id,
                'title' => $continueRoadmap->title,
                'technology' => $continueRoadmap->technology,
                'status' => $continueRoadmap->status,
                'steps_count' => $continueRoadmap->steps_count,
                'completed_steps_count' => $continueRoadmap->completed_steps_count,
                'progress' => $continueRoadmap->progress,
                'current_step' => $currentStep ? [
                    'id' => $currentStep->id,
                    'title' => $currentStep->title,
                    'position' => $currentStep->position,
                    'status' => $currentStep->status,
                ] : null,
            ];
        } else {
            $continueRoadmapData = null;
        }

        $recentRoadmaps = $user->roadmaps()
            ->withProgress()
            ->with([
                'steps' => fn ($query) => $query
                    ->reorder()
                    ->whereIn('status', [
                        RoadmapStep::IN_PROGRESS,
                        RoadmapStep::TODO,
                    ])
                    ->orderByRaw(
                        "case when status = ? then 0 else 1 end",
                        [RoadmapStep::IN_PROGRESS]
                    )
                    ->orderBy('position'),
            ])
            ->latest('updated_at')
            ->limit(3)
            ->get()
            ->map(fn (Roadmap $roadmap) => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'technology' => $roadmap->technology,
                'status' => $roadmap->status,
                'steps_count' => $roadmap->steps_count,
                'completed_steps_count' => $roadmap->completed_steps_count,
                'progress' => $roadmap->progress,
                'current_step' => ($currentStep = $roadmap->steps->first())
                    ? [
                        'id' => $currentStep->id,
                        'title' => $currentStep->title,
                        'position' => $currentStep->position,
                        'status' => $currentStep->status,
                    ]
                    : null,
            ]);

        // Catalogue de cours de l'accueil : les étapes (cours) de l'utilisateur,
        // en cours d'abord, puis à démarrer, puis terminés.
        $courses = RoadmapStep::query()
            ->with('roadmap:id,title,technology,user_id,updated_at')
            ->whereHas('roadmap', fn ($query) => $query->where('user_id', $user->id))
            ->orderByRaw(
                'case status when ? then 0 when ? then 1 when ? then 2 else 3 end',
                [RoadmapStep::IN_PROGRESS, RoadmapStep::TODO, RoadmapStep::BLOCKED]
            )
            ->orderByDesc('last_viewed_at')
            ->orderBy('roadmap_id')
            ->orderBy('position')
            ->limit(60)
            ->get(['id', 'roadmap_id', 'title', 'status', 'difficulty_level', 'estimated_minutes', 'position'])
            ->map(fn (RoadmapStep $step) => [
                'id' => $step->id,
                'title' => $step->title,
                'status' => $step->status,
                'difficulty_level' => $step->difficulty_level,
                'estimated_minutes' => $step->estimated_minutes,
                'technology' => $step->roadmap?->technology,
                'roadmap_title' => $step->roadmap?->title,
            ])
            ->values();

        $suggestions = $this->suggestedCourses(
            $user->roadmaps()->pluck('technology')->filter()->unique()->all(),
            (array) ($user->learningProfile?->technologies ?? [])
        );

        return Inertia::render('Dashboard', [
            'courses' => $courses,
            'suggested_courses' => $suggestions,
            'stats' => [
                'roadmaps' => $user->roadmaps()->count(),
                'memos' => $user->memos()->count(),
                'steps_total' => $stepsTotal,
                'steps_completed' => $stepsCompleted,
                // Indicateur d'activité du §5.2 du CDC : progression globale
                'progress' => $stepsTotal === 0
                    ? 0
                    : (int) round($stepsCompleted / $stepsTotal * 100),
            ],
            'recent_roadmaps' => $recentRoadmaps,
            // Maquette « Mobile — Accueil » : les 3 fiches modifiées le plus récemment.
            'recent_memos' => $user->memos()
                ->with(['tags:id,name,slug', 'folder:id,name'])
                ->latest('updated_at')
                ->limit(3)
                ->get(['id', 'title', 'folder_id', 'is_favorite', 'updated_at'])
                ->map(fn ($memo) => [
                    'id' => $memo->id,
                    'title' => $memo->title,
                    'is_favorite' => $memo->is_favorite,
                    'updated_at' => $memo->updated_at,
                    'folder' => $memo->folder?->name,
                    'tag' => $memo->tags->first()?->name,
                ]),
            'continue_roadmap' => $continueRoadmapData,
            'learning_profile' => $user->learningProfile ? [
                'level' => $user->learningProfile->level,
                'academic_level' => $user->learningProfile->academic_level,
                'technologies' => $user->learningProfile->technologies ?? [],
                'goals' => $user->learningProfile->goals ?? [],
                'assessment_scores' => $user->learningProfile->assessment_scores ?? null,
            ] : null,
        ]);
    }

    /**
     * Cours proposés directement sur l'accueil : un par technologie du catalogue que
     * l'utilisateur n'a pas encore démarrée, ses technologies choisies à l'onboarding d'abord.
     */
    private function suggestedCourses(array $startedTechnologies, array $preferred): array
    {
        $catalog = config('devroad_courses', []);
        $labels = config('devroad.technologies', []);

        $keys = collect(array_keys($catalog))
            ->reject(fn (string $key) => in_array($key, $startedTechnologies, true))
            ->sortBy(fn (string $key) => in_array($key, $preferred, true) ? 0 : 1)
            ->values();

        return $keys->take(12)->map(function (string $key) use ($catalog, $labels) {
            $stats = Cache::remember("dashboard.course-stats.{$key}", 3600, function () use ($key, $catalog) {
                $minutes = 0;
                $level = null;
                $files = glob(resource_path("courses/{$key}/*.md")) ?: [];
                sort($files);

                foreach ($files as $file) {
                    $head = (string) file_get_contents($file, false, null, 0, 400);
                    $minutes += preg_match('/^minutes:\s*(\d+)/m', $head, $m) ? (int) $m[1] : 0;
                    $level ??= preg_match('/^level:\s*(\w+)/m', $head, $l) ? $l[1] : null;
                }

                return [
                    'minutes' => $minutes,
                    'level' => $level ?? 'beginner',
                    'chapters' => max(count($files), count($catalog[$key]['lessons'] ?? [])),
                ];
            });

            return [
                'technology' => $key,
                'title' => 'Apprendre '.($labels[$key] ?? ($catalog[$key]['title'] ?? ucfirst($key))),
                'description' => $catalog[$key]['description'] ?? null,
                'level' => $stats['level'],
                'minutes' => $stats['minutes'],
                'chapters' => $stats['chapters'],
            ];
        })->all();
    }
}
