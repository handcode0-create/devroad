<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoadmapStepRequest;
use App\Http\Requests\UpdateRoadmapStepRequest;
use App\Http\Requests\UpdateStepStatusRequest;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapStepController extends Controller
{
    /**
     * Afficher le cours d'une étape.
     */
    public function show(RoadmapStep $step): Response
    {
        $this->authorize('view', $step);

        $step->load([
            'roadmap',
        ]);

        $roadmap = $step->roadmap;

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
                'objective' => $step->objective,
                'content' => $step->content,
                'code_example' => $step->code_example,
                'estimated_minutes' => $step->estimated_minutes,
            ],

            'roadmap' => fn () => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'technology' => $roadmap->technology,
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

        $step->update([
            'status' => $request->validated('status'),
        ]);

        return back()->with(
            'success',
            'Progression mise à jour.'
        );
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