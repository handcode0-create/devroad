<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreRoadmapStepRequest;
use App\Http\Requests\UpdateRoadmapStepRequest;
use App\Http\Requests\UpdateStepStatusRequest;
use App\Http\Requests\UpdateStepExerciseRequest;
use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapStepController extends Controller
{
    /**
     * Afficher le cours d'une étape.
     */
    public function show(RoadmapStep $step): Response|RedirectResponse
    {
        $this->authorize('view', $step);

        $step->load([
            'roadmap',
        ]);

        $roadmap = $step->roadmap;

        $currentStep = $roadmap
            ->steps()
            ->where('status', '!=', RoadmapStep::COMPLETED)
            ->orderBy('position')
            ->first();

        if (
            $currentStep &&
            $currentStep->id !== $step->id &&
            $step->status !== RoadmapStep::COMPLETED &&
            $step->position > $currentStep->position
        ) {
            return redirect()->route('steps.show', $currentStep);
        }

        if ($step->status === RoadmapStep::TODO) {
            $step->update([
                'status' => RoadmapStep::IN_PROGRESS,
            ]);
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
                'objective' => $step->objective,
                'content' => $step->content,
                'code_example' => $step->code_example,
                'estimated_minutes' => $step->estimated_minutes,
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

        $status = $request->validated('status');

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
