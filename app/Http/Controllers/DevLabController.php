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
        $roadmaps = $request->user()
            ->roadmaps()
            ->withProgress()
            ->with([
                'steps' => fn ($query) => $query
                    ->reorder()
                    ->orderBy('position'),
            ])
            ->latest('updated_at')
            ->limit(12)
            ->get()
            ->map(function (Roadmap $roadmap) {
                $currentStep = $roadmap->steps
                    ->first(fn (RoadmapStep $step) => $step->status !== RoadmapStep::COMPLETED);

                $workspaceReady = $roadmap->steps->contains(function (RoadmapStep $step) use ($roadmap) {
                    return in_array(
                        $roadmap->technology,
                        ['laravel', 'php', 'javascript', 'react', 'nextjs', 'html', 'css', 'node'],
                        true
                    );
                });

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
            });

        return Inertia::render('DevLab/Index', [
            'roadmaps' => $roadmaps,
        ]);
    }
}
