<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DevLabShellPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_devlab_page_exposes_the_users_projects(): void
    {
        $user = User::factory()->create();
        $user->devLabProjects()->create([
            'name' => 'Portfolio',
            'template' => 'html',
            'runtime' => 'browser',
        ]);

        $response = $this->actingAs($user)->get('/devlab');

        $response->assertOk();
        $response->assertInertia(fn ($page) => $page
            ->component('DevLab/Index')
            ->has('projects', 1)
            ->where('projects.0.name', 'Portfolio')
        );
    }
}
