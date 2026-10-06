<?php

namespace App\Console\Commands;

use App\Models\Roadmap;
use App\Services\RoadmapGenerator;
use Illuminate\Console\Command;
use InvalidArgumentException;

/**
 * Applique le catalogue actuel (contenu riche inclus) aux parcours déjà créés.
 * Ne touche ni au statut des leçons, ni à la validation des exercices.
 */
class SyncCourses extends Command
{
    protected $signature = 'devroad:sync-courses {--technology= : Limiter à une technologie}';

    protected $description = 'Met à jour le contenu des leçons des parcours existants depuis le catalogue';

    public function handle(RoadmapGenerator $generator): int
    {
        $query = Roadmap::query();

        if ($technology = $this->option('technology')) {
            $query->where('technology', $technology);
        }

        $done = 0;
        $skipped = 0;

        $query->each(function (Roadmap $roadmap) use ($generator, &$done, &$skipped) {
            try {
                $generator->syncRoadmap($roadmap);
                $done++;
            } catch (InvalidArgumentException) {
                $skipped++;
            }
        });

        $this->info("{$done} parcours synchronisé(s), {$skipped} ignoré(s) (technologie sans catalogue).");

        return self::SUCCESS;
    }
}
