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
    public function syncRoadmap(Roadmap $roadmap): Roadmap
    {
        $technology = $roadmap->technology;
        $course = config("devroad_courses.{$technology}");
        $enrichment = config("devroad_course_enrichment.{$technology}", []);

        if (! is_array($course) || empty($course['lessons'])) {
            throw new InvalidArgumentException("Aucun parcours n'est configuré pour la technologie [{$technology}].");
        }

        return DB::transaction(function () use ($roadmap, $course, $enrichment, $technology): Roadmap {
            $steps = $course['lessons'];
            $codeExamples = $course['code'] ?? [];
            $sourceFooter = $this->buildSourceFooter($enrichment['sources'] ?? []);

            foreach ($steps as $index => $lesson) {
                $payload = $this->buildStepPayload(
                    $technology,
                    $lesson,
                    $index,
                    count($steps),
                    $codeExamples,
                    $enrichment,
                    $sourceFooter,
                );

                $step = $roadmap->steps()->where('position', $index + 1)->first();

                if ($step) {
                    $step->update($payload);
                    continue;
                }

                $payload['status'] = RoadmapStep::TODO;
                $roadmap->steps()->create($payload);
            }

            return $roadmap->fresh('steps');
        });
    }

    public function create(User $user, array $roadmapData, string $technology): Roadmap
    {
        $course = config("devroad_courses.{$technology}");
        $enrichment = config("devroad_course_enrichment.{$technology}", []);

        if (! is_array($course) || empty($course['lessons'])) {
            throw new InvalidArgumentException("Aucun parcours n'est configuré pour la technologie [{$technology}].");
        }

        return DB::transaction(function () use ($user, $roadmapData, $technology, $course, $enrichment): Roadmap {
            $roadmap = $user->roadmaps()->create($roadmapData);

            $steps = $course['lessons'];
            $codeExamples = $course['code'] ?? [];
            $sourceFooter = $this->buildSourceFooter($enrichment['sources'] ?? []);

            foreach ($steps as $index => $lesson) {
                $isStructured = array_key_exists('title', $lesson);

                $title = $isStructured ? ($lesson['title'] ?? 'Cours') : ($lesson[0] ?? 'Cours');
                $lessonOverride = $enrichment['lessons'][$title] ?? [];

                $description = $lessonOverride['description']
                    ?? ($isStructured ? ($lesson['description'] ?? null) : ($lesson[1] ?? null));
                $objective = $lessonOverride['objective']
                    ?? ($isStructured ? ($lesson['objective'] ?? null) : ($lesson[2] ?? null));
                $content = $lessonOverride['content']
                    ?? ($isStructured
                        ? ($lesson['content'] ?? null)
                        : $this->buildContent($title, $lesson[3] ?? null));

                if ($sourceFooter && $content) {
                    $content = rtrim($content) . "\n\n" . $sourceFooter;
                }

                $codeExample = $lessonOverride['code_example']
                    ?? ($isStructured ? ($lesson['code_example'] ?? null) : ($codeExamples[$index] ?? null));
                $estimatedMinutes = $lessonOverride['estimated_minutes']
                    ?? ($isStructured ? ($lesson['estimated_minutes'] ?? 30) : ($index === count($steps) - 1 ? 60 : 30));

                $workspace = $this->buildWorkspaceMetadata(
                    $technology,
                    $title,
                    $codeExample,
                    $lessonOverride['workspace_file']
                        ?? ($isStructured ? ($lesson['workspace_file'] ?? null) : null),
                    $lessonOverride['workspace_language']
                        ?? ($isStructured ? ($lesson['workspace_language'] ?? null) : null),
                    $lessonOverride['workspace_files']
                        ?? ($isStructured ? ($lesson['workspace_files'] ?? null) : null),
                );

                $exercise = $this->buildExercise(
                    $title,
                    $description,
                    $objective,
                    $codeExample,
                    array_merge($isStructured ? $lesson : [], $lessonOverride)
                );

                $roadmap->steps()->create([
                    'title' => $title,
                    'description' => $description,
                    'objective' => $objective,
                    'content' => $content,
                    'code_example' => $codeExample,
                    'workspace_file' => $workspace['file'],
                    'workspace_language' => $workspace['language'],
                    'workspace_files' => $workspace['files'],
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

    private function buildStepPayload(
        string $technology,
        array $lesson,
        int $index,
        int $stepCount,
        array $codeExamples,
        array $enrichment,
        ?string $sourceFooter,
    ): array {
        $isStructured = array_key_exists('title', $lesson);

        $title = $isStructured ? ($lesson['title'] ?? 'Cours') : ($lesson[0] ?? 'Cours');
        $lessonOverride = $enrichment['lessons'][$title] ?? [];

        $description = $lessonOverride['description']
            ?? ($isStructured ? ($lesson['description'] ?? null) : ($lesson[1] ?? null));
        $objective = $lessonOverride['objective']
            ?? ($isStructured ? ($lesson['objective'] ?? null) : ($lesson[2] ?? null));
        $content = $lessonOverride['content']
            ?? ($isStructured
                ? ($lesson['content'] ?? null)
                : $this->buildContent($title, $lesson[3] ?? null));

        if ($sourceFooter && $content) {
            $content = rtrim($content) . "\n\n" . $sourceFooter;
        }

        $codeExample = $lessonOverride['code_example']
            ?? ($isStructured ? ($lesson['code_example'] ?? null) : ($codeExamples[$index] ?? null));
        $estimatedMinutes = $lessonOverride['estimated_minutes']
            ?? ($isStructured ? ($lesson['estimated_minutes'] ?? 30) : ($index === $stepCount - 1 ? 60 : 30));

        $workspace = $this->buildWorkspaceMetadata(
            $technology,
            $title,
            $codeExample,
            $lessonOverride['workspace_file']
                ?? ($isStructured ? ($lesson['workspace_file'] ?? null) : null),
            $lessonOverride['workspace_language']
                ?? ($isStructured ? ($lesson['workspace_language'] ?? null) : null),
            $lessonOverride['workspace_files']
                ?? ($isStructured ? ($lesson['workspace_files'] ?? null) : null),
        );

        $exercise = $this->buildExercise(
            $title,
            $description,
            $objective,
            $codeExample,
            array_merge($isStructured ? $lesson : [], $lessonOverride)
        );

        return [
            'title' => $title,
            'description' => $description,
            'objective' => $objective,
            'content' => $content,
            'code_example' => $codeExample,
            'workspace_file' => $workspace['file'],
            'workspace_language' => $workspace['language'],
            'workspace_files' => $workspace['files'],
            'exercise_title' => $exercise['title'],
            'exercise_description' => $exercise['description'],
            'exercise_hint' => $exercise['hint'],
            'exercise_solution' => $exercise['solution'],
            'estimated_minutes' => $estimatedMinutes,
        ];
    }

    private function buildWorkspaceMetadata(
        string $technology,
        string $title,
        ?string $codeExample,
        ?string $workspaceFile,
        ?string $workspaceLanguage,
        ?array $workspaceFiles = null,
    ): array {
        $language = $workspaceLanguage;

        if (! $language) {
            $language = match ($technology) {
                'laravel' => 'laravel',
                'php' => 'php',
                'html' => 'html',
                'css' => 'css',
                'javascript', 'react', 'nextjs' => 'javascript',
                'node' => 'node',
                'typescript' => 'typescript',
                'tailwind' => 'tailwind',
                'git', 'github', 'docker', 'mysql', 'postgresql' => $technology,
                default => null,
            };
        }

        $file = $workspaceFile;

        if (! $file && is_array($workspaceFiles) && $workspaceFiles !== []) {
            $file = $workspaceFiles[0]['path'] ?? null;
        }

        if (! $file) {
            $file = match ($language) {
                'laravel' => 'routes/web.php',
                'php' => 'main.php',
                'javascript' => 'main.js',
                'typescript' => 'main.ts',
                'node' => 'index.js',
                'html' => 'index.html',
                'css', 'tailwind' => 'styles.css',
                'git' => 'README.md',
                'github' => '.github/workflows/ci.yml',
                'docker' => 'Dockerfile',
                'mysql', 'postgresql' => 'schema.sql',
                default => null,
            };
        }

        $files = collect($workspaceFiles ?: [])
            ->filter(fn ($item) => is_array($item) && ! empty($item['path']))
            ->map(fn ($item) => [
                'path' => $item['path'],
                'content' => (string) ($item['content'] ?? ''),
            ])
            ->values()
            ->all();

        if ($files === []) {
            $files = [[
                'path' => $file,
                'content' => $codeExample ?? '',
            ]];
        }

        return [
            'language' => $language,
            'file' => $file,
            'files' => $files,
        ];
    }

    private function buildSourceFooter(array $sources): ?string
    {
        $sources = collect($sources)
            ->filter(fn ($source) => is_array($source) && ! empty($source[0]) && ! empty($source[1]))
            ->map(fn ($source) => '- [' . $source[0] . '](' . $source[1] . ')' . (! empty($source[2]) ? ' — ' . $source[2] : ''))
            ->values()
            ->all();

        if ($sources === []) {
            return null;
        }

        return "## Références de travail\n\nCe cours est une adaptation pédagogique originale construite à partir de la documentation publique suivante. Le contenu de DevRoad n'est pas une copie de ces ressources.\n\n" . implode("\n", $sources);
    }

    private function buildExercise(
        string $title,
        ?string $description,
        ?string $objective,
        ?string $codeExample,
        array $lesson
    ): array {
        return [
            'title' => $lesson['exercise_title'] ?? "Exercice — {$title}",
            'description' => $lesson['exercise_description']
                ?? $this->defaultExerciseDescription($title, $description, $objective, $codeExample),
            'hint' => $lesson['exercise_hint']
                ?? 'Commence par identifier le concept principal, puis applique-le dans un petit exemple concret.',
            'solution' => $lesson['exercise_solution']
                ?? ($codeExample ?: "Construis un exemple minimal lié à « {$title} » et vérifie que tu peux expliquer chaque partie de ta solution."),
        ];
    }

    private function defaultExerciseDescription(
        string $title,
        ?string $description,
        ?string $objective,
        ?string $codeExample
    ): string {
        if ($objective) {
            return $codeExample
                ? "Reproduis puis adapte l'exemple de code de cette leçon. L'objectif est de vérifier que tu peux le réécrire sans copier-coller."
                : "Mets en pratique l'objectif suivant : {$objective}";
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
