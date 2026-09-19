<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MemoTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->post('/memos', ['title' => 'Route Model Binding', 'content' => '...'])
            ->assertRedirect('/login');
    }

    public function test_un_memo_cree_appartient_a_lutilisateur_connecte(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();

        // On tente de forcer un autre user_id : il doit être ignoré.
        $this->actingAs($user)->post('/memos', [
            'title' => 'Route Model Binding',
            'content' => "Route::get('/tasks/{task}', ...);",
            'user_id' => $autre->id,
        ])->assertRedirect();

        $this->assertDatabaseHas('memos', [
            'title' => 'Route Model Binding',
            'user_id' => $user->id,
            'is_favorite' => false,
        ]);
        $this->assertDatabaseMissing('memos', ['user_id' => $autre->id]);
    }

    public function test_un_memo_sans_titre_ou_sans_contenu_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/memos', ['title' => '', 'content' => 'abc'])
            ->assertSessionHasErrors('title');

        $this->actingAs($user)->post('/memos', ['title' => 'ab', 'content' => 'abc'])
            ->assertSessionHasErrors('title');

        $this->actingAs($user)->post('/memos', ['title' => 'Un titre', 'content' => ''])
            ->assertSessionHasErrors('content');

        $this->assertDatabaseCount('memos', 0);
    }

    public function test_un_memo_peut_etre_cree_directement_depuis_un_cours(): void
    {
        $user = User::factory()->create();
        $roadmap = $user->roadmaps()->create(['title' => 'Laravel']);
        $step = $roadmap->steps()->create([
            'title' => 'Routing',
            'position' => 1,
            'status' => 'in_progress',
        ]);

        $this->actingAs($user)
            ->post(route('steps.memo', $step), [
                'title' => 'Notes Routing',
                'content' => 'Route::get("/hello", ...);',
                'tags' => ['Laravel', 'Routing'],
            ])
            ->assertRedirect(route('steps.show', $step));

        $memo = $user->memos()->latest('id')->first();

        $this->assertNotNull($memo);
        $this->assertSame('Notes Routing', $memo->title);
        $this->assertSame('Route::get("/hello", ...);', $memo->content);
        $this->assertCount(2, $memo->tags);
        $this->assertSame('laravel', $memo->tags->first()->slug);
    }

    public function test_un_utilisateur_ne_peut_pas_creer_un_memo_depuis_le_cours_d_un_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();

        $roadmap = $proprietaire->roadmaps()->create(['title' => 'Laravel']);
        $step = $roadmap->steps()->create([
            'title' => 'Routing',
            'position' => 1,
        ]);

        $this->actingAs($intrus)
            ->post(route('steps.memo', $step), [
                'title' => 'Intrusion',
                'content' => 'Contenu',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('memos', 0);
    }

    public function test_le_proprietaire_peut_modifier_son_memo(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Git', 'content' => 'git stash']);

        $this->actingAs($user)
            ->put(route('memos.update', $memo), ['title' => 'Git stash', 'content' => 'git stash pop'])
            ->assertRedirect(route('memos.show', $memo));

        $this->assertDatabaseHas('memos', ['id' => $memo->id, 'title' => 'Git stash']);
    }

    public function test_un_utilisateur_ne_peut_ni_voir_ni_modifier_ni_supprimer_le_memo_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $memo = $proprietaire->memos()->create(['title' => 'Secret', 'content' => 'contenu privé']);

        $this->actingAs($intrus)->get(route('memos.show', $memo))->assertForbidden();
        $this->actingAs($intrus)->get(route('memos.edit', $memo))->assertForbidden();

        $this->actingAs($intrus)
            ->put(route('memos.update', $memo), ['title' => 'Piraté', 'content' => 'x'])
            ->assertForbidden();

        $this->actingAs($intrus)->delete(route('memos.destroy', $memo))->assertForbidden();

        $this->assertDatabaseHas('memos', ['id' => $memo->id, 'title' => 'Secret']);
    }

    public function test_le_favori_se_bascule_et_seul_le_proprietaire_peut_le_faire(): void
    {
        $user = User::factory()->create();
        $intrus = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Hooks', 'content' => 'useState']);

        $this->actingAs($user)->patch(route('memos.favorite', $memo))->assertRedirect();
        $this->assertTrue($memo->fresh()->is_favorite);

        $this->actingAs($user)->patch(route('memos.favorite', $memo))->assertRedirect();
        $this->assertFalse($memo->fresh()->is_favorite);

        $this->actingAs($intrus)->patch(route('memos.favorite', $memo))->assertForbidden();
        $this->assertFalse($memo->fresh()->is_favorite);
    }

    public function test_le_proprietaire_peut_supprimer_son_memo(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'À jeter', 'content' => '...']);

        $this->actingAs($user)
            ->delete(route('memos.destroy', $memo))
            ->assertRedirect(route('memos.index'));

        $this->assertDatabaseCount('memos', 0);
    }

    public function test_la_liste_ne_contient_que_les_memos_de_lutilisateur_et_filtre_les_favoris(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $autre = User::factory()->create();

        $user->memos()->create(['title' => 'Mon mémo', 'content' => 'a', 'is_favorite' => true]);
        $user->memos()->create(['title' => 'Mon autre mémo', 'content' => 'b']);
        $autre->memos()->create(['title' => 'Mémo étranger', 'content' => 'c']);

        $this->actingAs($user)->get(route('memos.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Memos/Index', false)
                ->has('memos.data', 2));

        $this->actingAs($user)->get(route('memos.index', ['favorites' => 1]))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Memos/Index', false)
                ->has('memos.data', 1)
                ->where('memos.data.0.title', 'Mon mémo'));
    }
}