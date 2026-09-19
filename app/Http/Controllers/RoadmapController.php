<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class RoadmapController extends Controller
{
    /**
     * Liste des feuilles de route de l'utilisateur connecté.
     */
    public function index(Request $request): Response
    {
        $roadmaps = $request->user()
            ->roadmaps()
            ->withProgress()
            ->latest('updated_at')
            ->paginate(10)
            ->withQueryString()
            ->through(fn (Roadmap $roadmap) => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'description' => $roadmap->description,
                'status' => $roadmap->status,
                'steps_count' => $roadmap->steps_count,
                'completed_steps_count' => $roadmap->completed_steps_count,
                'progress' => $roadmap->progress,
            ]);

        return Inertia::render('Roadmaps/Index', [
            'roadmaps' => $roadmaps,
        ]);
    }

    /**
     * Formulaire de création.
     */
    public function create(): Response
    {
        return Inertia::render('Roadmaps/Create');
    }

    /**
     * Création d'une feuille de route.
     */
    public function store(
        \App\Http\Requests\StoreRoadmapRequest $request
    ): RedirectResponse {
        $roadmap = $request->user()
            ->roadmaps()
            ->create($request->validated());

        return redirect()
            ->route('roadmaps.show', $roadmap)
            ->with('success', 'Feuille de route créée.');
    }

    /**
     * Affichage d'une feuille de route et de ses étapes.
     */
    public function show(
        Request $request,
        Roadmap $roadmap
    ): Response {
        $this->authorize('view', $roadmap);

        $roadmap->load([
            'steps' => fn ($query) => $query
                ->orderBy('position'),
        ]);

        return Inertia::render('Roadmaps/Show', [
            'roadmap' => [
                'id' => $roadmap->id,
                'title' => $roadmap->title,
                'description' => $roadmap->description,
                'status' => $roadmap->status,
                'progress' => $roadmap->progress,
                'steps_count' => $roadmap->steps->count(),
                'completed_steps_count' => $roadmap->steps
                    ->where('status', 'completed')
                    ->count(),
                'steps' => $roadmap->steps->map(
                    fn ($step) => [
                        'id' => $step->id,
                        'title' => $step->title,
                        'description' => $step->description,
                        'position' => $step->position,
                        'status' => $step->status,
                    ]
                )->values(),
            ],
        ]);
    }

    /**
     * Formulaire de modification.
     */
    public function edit(
        Request $request,
        Roadmap $roadmap
    ): Response {
        $this->authorize('update', $roadmap);

        return Inertia::render('Roadmaps/Edit', [
            'roadmap' => $roadmap,
        ]);
    }

    /**
     * Mise à jour.
     */
    public function update(
        \App\Http\Requests\UpdateRoadmapRequest $request,
        Roadmap $roadmap
    ): RedirectResponse {
        $this->authorize('update', $roadmap);

        $roadmap->update($request->validated());

        return redirect()
            ->route('roadmaps.show', $roadmap)
            ->with('success', 'Feuille de route mise à jour.');
    }

    /**
     * Suppression.
     */
    public function destroy(
        Roadmap $roadmap
    ): RedirectResponse {
        $this->authorize('delete', $roadmap);

        $roadmap->delete();

        return redirect()
            ->route('roadmaps.index')
            ->with('success', 'Feuille de route supprimée.');
    }
}