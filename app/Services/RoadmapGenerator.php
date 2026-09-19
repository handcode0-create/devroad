<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class RoadmapGenerator
{
    /**
     * Create a roadmap and generate its lessons from the DevRoad course catalog.
     *
     * The catalog is configuration-driven so the same templates can be reused
     * by HTTP flows, seeders and future admin tooling.
     */
    public function create(array $roadmapData, string $technology): Roadmap
    {
        $course = config("devroad_courses.{$technology}");

        if (! is_array($course) || empty($course['lessons'])) {
            throw new InvalidArgumentException("Aucun parcours n'est configuré pour la technologie [{$technology}].");
        }

        return DB::transaction(function () use ($roadmapData, $technology, $course): Roadmap {
            $roadmap = Roadmap::create($roadmapData);

            $steps = $course['lessons'];
            $codeExamples = $course['code'] ?? [];

            foreach ($steps as $index => $lesson) {
                $roadmap->steps()->create([
                    'title' => $lesson[0],
                    'description' => $lesson[1] ?? null,
                    'objective' => $lesson[2] ?? null,
                    'content' => $this->buildContent($lesson[0], $lesson[3] ?? null),
                    'code_example' => $codeExamples[$index] ?? null,
                    'estimated_minutes' => $index === count($steps) - 1 ? 60 : 30,
                    'position' => $index + 1,
                    'status' => $index === 0
                        ? RoadmapStep::IN_PROGRESS
                        : RoadmapStep::TODO,
                ]);
            }

            return $roadmap->load('steps');
        });
    }

    private function buildContent(string $title, ?string $content): ?string
    {
        if (! $content) {
            return null;
        }

        return "## {$title}\n\n{$content}\n\n## À retenir\n\n- Comprends le concept avant de mémoriser la syntaxe.\n- Pratique avec un petit exercice avant de passer à l’étape suivante.\n- Relie toujours la notion à un besoin concret de projet.";
    }
}
