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

    public function test_une_technologie_sans_chapitres_garde_son_contenu(): void
    {
        $user = User::factory()->create();

        $roadmap = app(RoadmapGenerator::class)->create(
            $user,
            ['title' => 'React', 'technology' => 'react'],
            'react'
        );

        $this->assertNotEmpty($roadmap->steps->first()->content);
        $this->assertStringNotContainsString(':::quiz', $roadmap->steps->first()->content);
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
