<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\UserLearningProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboardingAssessmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_bonne_reponse_n_est_pas_toujours_en_premiere_position(): void
    {
        $user = User::factory()->create();
        UserLearningProfile::create(['user_id' => $user->id, 'level' => 'beginner']);

        $response = $this->actingAs($user)->get(route('onboarding.create'));
        $response->assertOk();

        $positions = [];
        foreach ($response->viewData('page')['props']['categories'] as $category) {
            foreach ($category['questions'] as $question) {
                foreach (array_values($question['options']) as $index => $option) {
                    if ($option['score'] > 0) {
                        $positions[] = $index;
                    }
                }
            }
        }

        $this->assertGreaterThan(1, count(array_unique($positions)), 'Les bonnes réponses sont toutes à la même place.');

        $again = $this->actingAs($user)->get(route('onboarding.create'))->viewData('page')['props']['categories'];
        $this->assertEquals($response->viewData('page')['props']['categories'], $again, "L'ordre doit rester stable au rechargement.");
    }
}
