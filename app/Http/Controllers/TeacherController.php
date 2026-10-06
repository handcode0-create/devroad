<?php

namespace App\Http\Controllers;

use App\Models\TeachingGroup;
use App\Models\User;
use App\Services\TeachingGroupStats;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

/** Parcours professeur : activation, groupes, statistiques. */
class TeacherController extends Controller
{
    public function activate(Request $request): RedirectResponse
    {
        $user = $request->user();

        // Un élève déjà rattaché à des groupes garde ce rôle : pas de double casquette silencieuse.
        if (! $user->isTeacher() && $user->joinedGroups()->exists()) {
            return back()->with('error', 'Quitte d\'abord tes groupes d\'élève pour activer le mode professeur.');
        }

        $user->forceFill(['role' => User::ROLE_TEACHER])->save();

        return redirect()->route('teacher.dashboard')->with('success', 'Mode professeur activé.');
    }

    public function dashboard(Request $request): Response
    {
        $this->authorize('viewAny', TeachingGroup::class);

        $groups = $request->user()->teachingGroups()
            ->withCount('students')
            ->latest()
            ->get()
            ->map(fn (TeachingGroup $group) => $this->summary($group));

        return Inertia::render('Teacher/Dashboard', [
            'groups' => $groups,
            'technologies' => $this->technologies(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', TeachingGroup::class);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'technology' => ['nullable', 'string', Rule::in(array_keys(config('devroad_courses', [])))],
        ]);

        $group = new TeachingGroup($data);
        $group->user_id = $request->user()->id;
        $group->join_code = TeachingGroup::generateUniqueCode();
        $group->save();

        return redirect()->route('teacher.groups.show', $group)->with('success', 'Groupe créé.');
    }

    public function show(Request $request, TeachingGroup $group, TeachingGroupStats $stats): Response
    {
        $this->authorize('view', $group);

        return Inertia::render('Teacher/GroupShow', [
            'group' => $this->summary($group->loadCount('students')),
            'stats' => $stats->for($group),
            'technologies' => $this->technologies(),
        ]);
    }

    public function update(Request $request, TeachingGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:120'],
            'technology' => ['sometimes', 'nullable', 'string', Rule::in(array_keys(config('devroad_courses', [])))],
            'archived' => ['sometimes', 'boolean'],
        ]);

        if (array_key_exists('archived', $data)) {
            $group->archived_at = $data['archived'] ? now() : null;
            unset($data['archived']);
        }

        $group->fill($data)->save();

        return back()->with('success', 'Groupe mis à jour.');
    }

    public function regenerateCode(TeachingGroup $group): RedirectResponse
    {
        $this->authorize('update', $group);

        $group->join_code = TeachingGroup::generateUniqueCode();
        $group->save();

        return back()->with('success', 'Nouveau code généré. L\'ancien ne fonctionne plus.');
    }

    public function destroy(TeachingGroup $group): RedirectResponse
    {
        $this->authorize('delete', $group);

        $group->delete();

        return redirect()->route('teacher.dashboard')->with('success', 'Groupe supprimé.');
    }

    public function removeStudent(TeachingGroup $group, User $student): RedirectResponse
    {
        $this->authorize('update', $group);

        $group->students()->detach($student->id);

        return back()->with('success', 'Élève retiré du groupe.');
    }

    private function summary(TeachingGroup $group): array
    {
        return [
            'id' => $group->id,
            'name' => $group->name,
            'technology' => $group->technology,
            'join_code' => $group->join_code,
            'archived' => $group->isArchived(),
            'students_count' => $group->students_count ?? 0,
            'created_at' => $group->created_at?->toIso8601String(),
        ];
    }

    private function technologies(): array
    {
        return array_keys(config('devroad_courses', []));
    }
}
