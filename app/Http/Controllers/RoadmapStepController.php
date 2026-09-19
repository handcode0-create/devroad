<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoadmapStepRequest;
use App\Http\Requests\UpdateRoadmapStepRequest;
use App\Http\Requests\UpdateStepStatusRequest;
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
        $this->authorize('update', $step);

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
            'step' => [
                'id' => $step->id,
                'title' => $step->title,
                'description' => $step->description,
                'position' => $step->position,
                'status' => $step->status,
            ],

            'roadmap' => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
            ],

            'previous_step' => $previousStep
                ? [
                    'id' => $previousStep->id,
                    'title' => $previousStep->title,
                ]
                : null,

            'next_step' => $nextStep
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
        \App\Models\Roadmap $roadmap
    ): RedirectResponse {
        $this->authorize('update', $roadmap);

        $roadmap->steps()->create(
            $request->validated()
        );

        return back()->with(
            'success',
            'Étape ajoutée.'
        );
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

        return back()->with(
            'success',
            'Étape mise à jour.'
        );
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

        $step->delete();

        return back()->with(
            'success',
            'Étape supprimée.'
        );
    }
}