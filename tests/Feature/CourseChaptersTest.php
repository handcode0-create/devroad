<?php

namespace Tests\Feature;

use App\Models\User;
use App\Services\RoadmapGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseChaptersTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_cours_laravel_sont_des_chapitres_longs_avec_quiz(): void
    {
        $user = User::factory()->create();

        $roadmap = app(RoadmapGenerator::class)->create(
            $user,
            ['title' => 'Laravel', 'technology' => 'laravel'],
            'laravel'
        );

        $this->assertCount(13, $roadmap->steps);

        foreach ($roadmap->steps as $step) {
            $words = count(preg_split('/\s+/u', trim((string) $step->content)));

            $this->assertGreaterThan(1500, $words, "« {$step->title} » est trop court ({$words} mots).");
            $this->assertStringContainsString(':::quiz', $step->content, "« {$step->title} » n'a pas de quiz.");
            $this->assertGreaterThanOrEqual(120, $step->estimated_minutes);
        }

        $this->assertSame('beginner', $roadmap->steps->first()->difficulty_level);
    }

    public function test_toutes_les_technologies_ont_des_chapitres_longs_avec_quiz(): void
    {
        $user = User::factory()->create();
        $generator = app(RoadmapGenerator::class);

        foreach (['react', 'nextjs', 'javascript', 'typescript', 'php', 'html', 'css', 'tailwind', 'node', 'git', 'github', 'docker', 'mysql', 'postgresql', 'python', 'django', 'fastapi', 'flutter'] as $tech) {
            $roadmap = $generator->create($user, ['title' => $tech, 'technology' => $tech], $tech);

            $this->assertCount(7, $roadmap->steps, $tech);

            foreach ($roadmap->steps as $step) {
                $words = count(preg_split('/\s+/u', trim((string) $step->content)));

                $this->assertGreaterThan(1500, $words, "[{$tech}] « {$step->title} » est trop court ({$words} mots).");
                $this->assertStringContainsString(':::quiz', $step->content, "[{$tech}] « {$step->title} » n'a pas de quiz.");
            }

            $this->assertSame(360, $roadmap->steps->last()->estimated_minutes, $tech);
            $this->assertSame('professional', $roadmap->steps->last()->difficulty_level, $tech);
        }
    }

    public function test_la_synchronisation_conserve_le_statut_des_etapes(): void
    {
        $user = User::factory()->create();
        $generator = app(RoadmapGenerator::class);

        $roadmap = $generator->create($user, ['title' => 'Laravel', 'technology' => 'laravel'], 'laravel');
        $roadmap->steps()->where('position', 2)->update(['status' => 'completed', 'content' => 'ancien contenu']);

        $generator->syncRoadmap($roadmap->fresh());

        $step = $roadmap->steps()->where('position', 2)->first();

        $this->assertSame('completed', $step->status);
        $this->assertStringContainsString(':::quiz', $step->content);
    }
}
