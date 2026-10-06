<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // Barre latérale desktop (maquette « Desktop — Fiches ») : compteurs et dossiers.
            'nav' => fn () => $request->user() ? [
                'roadmaps' => $request->user()->roadmaps()->count(),
                'memos' => $request->user()->memos()->count(),
                'folders' => $request->user()->memoFolders()
                    ->withCount('memos')
                    ->orderBy('position')
                    ->orderBy('name')
                    ->get(['id', 'parent_id', 'name'])
                    ->map(fn ($folder) => [
                        'id' => $folder->id,
                        'parent_id' => $folder->parent_id,
                        'name' => $folder->name,
                        'memos_count' => $folder->memos_count,
                    ]),
            ] : null,
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                // Action « Annuler » proposée dans la notification (corbeille, déplacement…).
                'undo' => fn () => $request->session()->get('undo'),
            ],
        ];
    }
}
