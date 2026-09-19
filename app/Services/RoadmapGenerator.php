<?php

namespace App\Services;

use App\Models\Roadmap;
use App\Models\RoadmapStep;
use App\Models\User;
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
    public function create(User $user, array $roadmapData, string $technology): Roadmap
    {
        $course = config("devroad_courses.{$technology}");

        if (! is_array($course) || empty($course['lessons'])) {
            throw new InvalidArgumentException("Aucun parcours n'est configuré pour la technologie [{$technology}].");
        }

        return DB::transaction(function () use ($user, $roadmapData, $technology, $course): Roadmap {
            $roadmap = $user->roadmaps()->create($roadmapData);

            $steps = $course['lessons'];
            $codeExamples = $course['code'] ?? [];

            foreach ($steps as $index => $lesson) {
                $isStructured = array_key_exists('title', $lesson);

                $title = $isStructured ? $lesson['title'] : ($lesson[0] ?? 'Cours');
                $description = $isStructured ? ($lesson['description'] ?? null) : ($lesson[1] ?? null);
                $objective = $isStructured ? ($lesson['objective'] ?? null) : ($lesson[2] ?? null);
                $content = $isStructured
                    ? ($lesson['content'] ?? null)
                    : $this->buildContent($title, $lesson[3] ?? null);
                $codeExample = $isStructured
                    ? ($lesson['code_example'] ?? null)
                    : ($codeExamples[$index] ?? null);
                $estimatedMinutes = $isStructured
                    ? ($lesson['estimated_minutes'] ?? 30)
                    : ($index === count($steps) - 1 ? 60 : 30);

                $exercise = $this->buildExercise(
                    $title,
                    $description,
                    $objective,
                    $isStructured ? $lesson : []
                );

                $roadmap->steps()->create([
                    'title' => $title,
                    'description' => $description,
                    'objective' => $objective,
                    'content' => $content,
                    'code_example' => $codeExample,
                    'exercise_title' => $exercise['title'],
                    'exercise_description' => $exercise['description'],
                    'exercise_hint' => $exercise['hint'],
                    'exercise_solution' => $exercise['solution'],
                    'estimated_minutes' => $estimatedMinutes,
                    'position' => $index + 1,
                    'status' => $index === 0
                        ? RoadmapStep::IN_PROGRESS
                        : RoadmapStep::TODO,
                ]);
            }

            return $roadmap->load('steps');
        });
    }

    private function buildExercise(
        string $title,
        ?string $description,
        ?string $objective,
        array $lesson
    ): array {
        return [
            'title' => $lesson['exercise_title'] ?? "Exercice — {$title}",
            'description' => $lesson['exercise_description']
                ?? $this->defaultExerciseDescription($title, $description, $objective),
            'hint' => $lesson['exercise_hint']
                ?? 'Commence par identifier le concept principal, puis applique-le dans un petit exemple concret.',
            'solution' => $lesson['exercise_solution']
                ?? "Construis un exemple minimal lié à « {$title} » et vérifie que tu peux expliquer chaque partie de ta solution.",
        ];
    }

    private function defaultExerciseDescription(
        string $title,
        ?string $description,
        ?string $objective
    ): string {
        if ($objective) {
            return "Mets en pratique l'objectif suivant : {$objective}";
        }

        if ($description) {
            return "Mets en pratique la notion « {$title} » : {$description}";
        }

        return "Crée un petit exemple pratique pour démontrer que tu maîtrises « {$title} ».";
    }

    private function buildContent(string $title, ?string $content): ?string
    {
        if (! $content) {
            return null;
        }

        return "## {$title}\n\n{$content}\n\n## À retenir\n\n- Comprends le concept avant de mémoriser la syntaxe.\n- Pratique avec un petit exercice avant de passer à l’étape suivante.\n- Relie toujours la notion à un besoin concret de projet.";
    }
}
