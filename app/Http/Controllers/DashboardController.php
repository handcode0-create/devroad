<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\Request;
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

        return Inertia::render('Dashboard', [
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
            'continue_roadmap' => $continueRoadmapData,
        ]);
    }
}