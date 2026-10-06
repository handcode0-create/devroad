<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Les actions réversibles (corbeille, déplacement) proposent « Annuler »
 * dans la notification, pour réparer une mauvaise manipulation.
 */
class MemoUndoTest extends TestCase
{
    use RefreshDatabase;

    public function test_mettre_une_fiche_a_la_corbeille_propose_de_la_restaurer(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x']);

        $this->actingAs($user)->delete('/memos/' . $memo->id)
            ->assertRedirect(route('memos.index'))
            ->assertSessionHas('undo', fn ($undo) => $undo['url'] === route('memos.restore', $memo->id) && $undo['method'] === 'post');

        $this->assertSoftDeleted($memo);

        $this->actingAs($user)->post($this->undoUrl())->assertRedirect();
        $this->assertNotSoftDeleted($memo);
    }

    public function test_depuis_la_liste_la_corbeille_garde_la_vue_courante(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x']);

        $this->actingAs($user)->from('/memos?favorites=1')
            ->delete('/memos/' . $memo->id, ['stay' => 1])
            ->assertRedirect('/memos?favorites=1');
    }

    public function test_une_corbeille_groupee_peut_etre_annulee(): void
    {
        $user = User::factory()->create();
        $a = $user->memos()->create(['title' => 'Note A', 'content' => 'x']);
        $b = $user->memos()->create(['title' => 'Note B', 'content' => 'x']);

        $response = $this->actingAs($user)->from('/memos')
            ->post('/memos/bulk', ['ids' => [$a->id, $b->id], 'action' => 'delete']);

        $response->assertSessionHas('undo');
        $undo = session('undo');
        $this->assertSame(['ids' => [$a->id, $b->id], 'action' => 'restore'], $undo['data']);

        $this->actingAs($user)->post($undo['url'], $undo['data'])->assertRedirect();
        $this->assertNotSoftDeleted($a);
        $this->assertNotSoftDeleted($b);
    }

    public function test_un_deplacement_peut_etre_annule_vers_le_dossier_d_origine(): void
    {
        $user = User::factory()->create();
        $origine = $user->memoFolders()->create(['name' => 'Origine']);
        $cible = $user->memoFolders()->create(['name' => 'Cible']);
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x', 'folder_id' => $origine->id]);

        $this->actingAs($user)->from('/memos')
            ->patch('/memos/' . $memo->id . '/move', ['folder_id' => $cible->id])
            ->assertSessionHas('undo', fn ($undo) => $undo['data'] === ['folder_id' => $origine->id] && $undo['method'] === 'patch');

        $this->actingAs($user)->from('/memos')->patch(route('memos.move', $memo), ['folder_id' => $origine->id]);
        $this->assertSame($origine->id, $memo->fresh()->folder_id);
    }

    private function undoUrl(): string
    {
        return session('undo')['url'];
    }
}
