<?php

namespace Tests\Feature;

use App\Models\MemoFolder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class NavigationSharedDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_la_barre_laterale_recoit_les_compteurs_et_les_dossiers_de_l_utilisateur(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();
        $folder = MemoFolder::create(['user_id' => $user->id, 'name' => 'Dossier Laravel']);
        MemoFolder::create(['user_id' => $other->id, 'name' => 'Dossier privé']);
        $user->memos()->create(['title' => 'Une fiche', 'content' => 'x', 'folder_id' => $folder->id]);
        $user->memos()->create(['title' => 'Deux fiches', 'content' => 'x']);
        $other->memos()->create(['title' => 'Autre', 'content' => 'x']);
        $user->roadmaps()->create(['title' => 'Laravel', 'technology' => 'laravel']);

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('nav.memos', 2)
                ->where('nav.roadmaps', 1)
                ->has('nav.folders', 1)
                ->where('nav.folders.0.name', 'Dossier Laravel')
                ->where('nav.folders.0.memos_count', 1));
    }

    public function test_un_visiteur_ne_recoit_pas_de_donnees_de_navigation(): void
    {
        $this->get('/login')->assertInertia(fn (AssertableInertia $page) => $page->where('nav', null));
    }
}
