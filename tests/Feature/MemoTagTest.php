<?php

namespace Tests\Feature;

use App\Models\Memo;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class MemoTagTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_memo_peut_recevoir_plusieurs_tags(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/memos', [
            'title' => 'Route Model Binding',
            'content' => '...',
            'tags' => ['Laravel', 'Routing', 'Eloquent'],
        ])->assertRedirect();

        $memo = Memo::firstOrFail();

        $this->assertEqualsCanonicalizing(
            ['Laravel', 'Routing', 'Eloquent'],
            $memo->tags->pluck('name')->all()
        );
        // Les tags appartiennent à l'utilisateur qui crée le mémo
        $this->assertSame([$user->id], $memo->tags->pluck('user_id')->unique()->values()->all());
    }

    public function test_un_tag_est_reutilise_sans_doublon_meme_avec_une_casse_differente(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/memos', [
            'title' => 'Premier mémo', 'content' => 'a', 'tags' => ['Laravel', 'laravel'],
        ])->assertRedirect();

        $this->actingAs($user)->post('/memos', [
            'title' => 'Second mémo', 'content' => 'b', 'tags' => ['LARAVEL'],
        ])->assertRedirect();

        $this->assertSame(1, Tag::where('user_id', $user->id)->count());
        $this->assertDatabaseCount('memo_tag', 2);
    }

    public function test_deux_utilisateurs_ont_chacun_leur_propre_tag_du_meme_nom(): void
    {
        $alice = User::factory()->create();
        $bruno = User::factory()->create();

        $this->actingAs($alice)->post('/memos', [
            'title' => 'Mémo Alice', 'content' => 'a', 'tags' => ['Laravel'],
        ]);
        $this->actingAs($bruno)->post('/memos', [
            'title' => 'Mémo Bruno', 'content' => 'b', 'tags' => ['Laravel'],
        ]);

        $this->assertSame(2, Tag::where('slug', 'laravel')->count());
        $this->assertSame(1, Tag::where('slug', 'laravel')->where('user_id', $alice->id)->count());
        $this->assertSame(1, Tag::where('slug', 'laravel')->where('user_id', $bruno->id)->count());
    }

    public function test_modifier_un_memo_remplace_ses_tags_mais_garde_les_tags_existants(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Hooks', 'content' => 'useState']);
        $memo->syncTagNames(['React', 'JavaScript']);

        $this->actingAs($user)->put(route('memos.update', $memo), [
            'title' => 'Hooks',
            'content' => 'useState',
            'tags' => ['React', 'Frontend'],
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(
            ['React', 'Frontend'],
            $memo->fresh()->tags->pluck('name')->all()
        );
        // « JavaScript » n'est plus lié au mémo mais reste dans les tags de l'utilisateur
        $this->assertDatabaseHas('tags', ['user_id' => $user->id, 'slug' => 'javascript']);
    }

    public function test_une_modification_sans_cle_tags_ne_change_pas_les_tags(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Git', 'content' => 'git stash']);
        $memo->syncTagNames(['Git']);

        $this->actingAs($user)->put(route('memos.update', $memo), [
            'title' => 'Git stash',
            'content' => 'git stash pop',
        ])->assertRedirect();

        $this->assertSame(['Git'], $memo->fresh()->tags->pluck('name')->all());
    }

    public function test_envoyer_une_liste_de_tags_vide_retire_tous_les_tags(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Git', 'content' => 'git stash']);
        $memo->syncTagNames(['Git', 'CLI']);

        $this->actingAs($user)->putJson(route('memos.update', $memo), [
            'title' => 'Git',
            'content' => 'git stash',
            'tags' => [],
        ])->assertRedirect();

        $this->assertCount(0, $memo->fresh()->tags);
    }

    public function test_trop_de_tags_ou_un_tag_trop_long_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/memos', [
            'title' => 'Trop de tags',
            'content' => 'x',
            'tags' => array_map(fn ($i) => "tag$i", range(1, 11)),
        ])->assertSessionHasErrors('tags');

        $this->actingAs($user)->post('/memos', [
            'title' => 'Tag trop long',
            'content' => 'x',
            'tags' => [str_repeat('a', 51)],
        ])->assertSessionHasErrors('tags.0');

        $this->assertDatabaseCount('memos', 0);
    }

    public function test_supprimer_un_memo_retire_les_liens_mais_garde_le_tag(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'À jeter', 'content' => '...']);
        $memo->syncTagNames(['Laravel']);

        $this->actingAs($user)->delete(route('memos.destroy', $memo))->assertRedirect();

        $this->assertDatabaseCount('memo_tag', 0);
        $this->assertDatabaseHas('tags', ['user_id' => $user->id, 'slug' => 'laravel']);
    }

    public function test_la_liste_filtre_par_tag_et_naffiche_que_les_tags_de_lutilisateur(): void
    {
        $this->withoutVite();

        $user = User::factory()->create();
        $autre = User::factory()->create();

        $user->memos()->create(['title' => 'Mémo Laravel', 'content' => 'a'])->syncTagNames(['Laravel']);
        $user->memos()->create(['title' => 'Mémo React', 'content' => 'b'])->syncTagNames(['React']);
        $autre->memos()->create(['title' => 'Mémo étranger', 'content' => 'c'])->syncTagNames(['Secret']);

        // Sans filtre : 2 mémos, 2 pastilles (pas « Secret »)
        $this->actingAs($user)->get(route('memos.index'))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Memos/Index', false)
                ->has('memos.data', 2)
                ->has('tags', 2));

        // Filtre ?tag=laravel : 1 mémo, mais les pastilles restent complètes
        $this->actingAs($user)->get(route('memos.index', ['tag' => 'laravel']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Memos/Index', false)
                ->has('memos.data', 1)
                ->where('memos.data.0.title', 'Mémo Laravel')
                ->where('memos.data.0.tags.0.slug', 'laravel')
                ->has('tags', 2)
                ->where('filters.tag', 'laravel'));

        // Le slug d'un tag étranger ne donne rien
        $this->actingAs($user)->get(route('memos.index', ['tag' => 'secret']))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Memos/Index', false)
                ->has('memos.data', 0));
    }
}