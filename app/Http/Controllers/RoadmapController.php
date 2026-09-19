<?php

namespace App\Http\Controllers;

use App\Models\Roadmap;
use App\Services\RoadmapGenerator;
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
                'technology' => $roadmap->technology,
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
        return Inertia::render('Roadmaps/Create', [
            'technologies' => config('devroad.technologies', []),
        ]);
    }

    /**
     * Création d'une feuille de route.
     */
    public function store(
        \App\Http\Requests\StoreRoadmapRequest $request,
        RoadmapGenerator $generator
    ): RedirectResponse {
        $data = $request->validated();

        $technology = $data['technology'];

        $catalog = config("devroad.catalog.{$technology}", []);

        // Si l'utilisateur ne fournit pas de titre ou de description métier,
        // le catalogue fournit des valeurs cohérentes pour le parcours.
        $data['title'] = $data['title'] ?: ($catalog['title'] ?? config("devroad.technologies.{$technology}"));
        $data['description'] = $data['description'] ?: ($catalog['description'] ?? null);

        $roadmap = $generator->create(
            $request->user(),
            $data,
            $technology
        );

        return redirect()
            ->route('roadmaps.show', $roadmap)
            ->with('success', 'Parcours créé avec ses cours.');
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
                'technology' => $roadmap->technology,
                'description' => $roadmap->description,
                'status' => $roadmap->status,
                'progress' => $roadmap->progress,
                'steps_count' => $roadmap->steps->count(),
                'completed_steps_count' => $roadmap->steps
                    ->where('status', 'completed')
                    ->count(),
                'resources' => config("devroad.catalog.{$roadmap->technology}.resources", []),
                'about' => [
                    'technology' => $roadmap->technology,
                    'description' => config("devroad.catalog.{$roadmap->technology}.description"),
                ],
                'steps' => $roadmap->steps->map(
                    fn ($step) => [
                        'id' => $step->id,
                        'title' => $step->title,
                        'description' => $step->description,
                        'objective' => $step->objective,
                        'estimated_minutes' => $step->estimated_minutes,
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
            'technologies' => config('devroad.technologies', []),
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