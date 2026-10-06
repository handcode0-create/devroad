<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\TeachingGroup;
use Illuminate\Support\Carbon;

/**
 * Statistiques d'un groupe pour le professeur.
 *
 * Définitions (volontairement simples et vérifiables) :
 * - progression d'un élève = leçons terminées / leçons totales de ses parcours
 *   (du parcours de la technologie du groupe si elle est définie, sinon de tous ses parcours) ;
 * - dernière activité = dernière modification d'une de ses leçons ;
 * - élève inactif = aucune activité depuis STALLED_DAYS jours et parcours non terminé ;
 * - étape de blocage = leçon « en cours » partagée par le plus d'élèves.
 */
class TeachingGroupStats
{
    public const ACTIVE_DAYS = 7;

    public const STALLED_DAYS = 7;

    public function for(TeachingGroup $group): array
    {
        $students = $group->students()->orderBy('name')->get(['users.id', 'users.name']);
        $ids = $students->pluck('id');

        $roadmaps = Roadmap::query()
            ->whereIn('user_id', $ids)
            ->when($group->technology, fn ($q) => $q->where('technology', $group->technology))
            ->get(['id', 'user_id']);

        $steps = RoadmapStep::query()
            ->whereIn('roadmap_id', $roadmaps->pluck('id'))
            ->get(['id', 'roadmap_id', 'title', 'status', 'position', 'updated_at']);

        $roadmapOwner = $roadmaps->pluck('user_id', 'id');
        $stepsByUser = $steps->groupBy(fn ($step) => $roadmapOwner[$step->roadmap_id]);

        $now = Carbon::now();

        $members = $students->map(function ($student) use ($stepsByUser, $now) {
            $own = $stepsByUser->get($student->id, collect());
            $total = $own->count();
            $done = $own->where('status', RoadmapStep::COMPLETED)->count();
            $progress = $total > 0 ? (int) round($done / $total * 100) : 0;
            $last = $own->max('updated_at');
            $current = $own->where('status', RoadmapStep::IN_PROGRESS)->sortBy('position')->first();

            return [
                'id' => $student->id,
                'name' => $student->name,
                'progress' => $progress,
                'completed_steps' => $done,
                'total_steps' => $total,
                'last_activity_at' => $last?->toIso8601String(),
                'active_recently' => $last !== null && $last->gte($now->copy()->subDays(self::ACTIVE_DAYS)),
                'stalled' => $total > 0 && $progress < 100
                    && ($last === null || $last->lt($now->copy()->subDays(self::STALLED_DAYS))),
                'current_step' => $current?->title,
            ];
        })->values();

        $blocking = $steps->where('status', RoadmapStep::IN_PROGRESS)
            ->groupBy('title')
            ->map->count()
            ->sortDesc();

        return [
            'members_count' => $members->count(),
            'average_progress' => $members->isEmpty() ? 0 : (int) round($members->avg('progress')),
            'active_last_7_days' => $members->where('active_recently', true)->count(),
            'stalled_count' => $members->where('stalled', true)->count(),
            'blocking_step' => $blocking->isEmpty() ? null : [
                'title' => $blocking->keys()->first(),
                'students' => $blocking->first(),
            ],
            'members' => $members->all(),
        ];
    }
}
