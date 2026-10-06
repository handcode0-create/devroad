<?php

namespace App\Http\Controllers;

use App\Models\TeachingGroup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/** Côté élève : rejoindre / quitter un groupe avec un code. */
class GroupMembershipController extends Controller
{
    public function index(Request $request): Response
    {
        $groups = $request->user()->joinedGroups()
            ->with('teacher:id,name')
            ->get()
            ->map(fn (TeachingGroup $group) => [
                'id' => $group->id,
                'name' => $group->name,
                'technology' => $group->technology,
                'teacher' => $group->teacher?->name,
                'archived' => $group->isArchived(),
                'joined_at' => $group->pivot->joined_at,
            ]);

        return Inertia::render('Groups/Index', [
            'groups' => $groups,
            // Transparence : ce que le professeur peut voir de l'élève.
            'shared_with_teacher' => ['name', 'progress', 'last_activity_at', 'current_step'],
        ]);
    }

    public function join(Request $request): RedirectResponse
    {
        $request->validate(['code' => ['required', 'string', 'max:32']]);

        $group = TeachingGroup::where('join_code', TeachingGroup::normalizeCode($request->string('code')))
            ->whereNull('archived_at')
            ->first();

        if (! $group) {
            return back()->withErrors(['code' => 'Code invalide ou groupe fermé.']);
        }

        $user = $request->user();

        if ($group->user_id === $user->id) {
            return back()->withErrors(['code' => 'Tu animes déjà ce groupe.']);
        }

        if ($user->isTeacher()) {
            return back()->withErrors(['code' => 'Un compte professeur ne peut pas rejoindre un groupe comme élève.']);
        }

        $group->students()->syncWithoutDetaching([$user->id => ['joined_at' => now()]]);

        return redirect()->route('groups.index')->with('success', 'Tu as rejoint « '.$group->name.' ».');
    }

    public function leave(Request $request, TeachingGroup $group): RedirectResponse
    {
        $request->user()->joinedGroups()->detach($group->id);

        return redirect()->route('groups.index')->with('success', 'Tu as quitté le groupe.');
    }
}
