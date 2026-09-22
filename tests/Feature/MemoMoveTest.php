<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoMoveTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $user = User::factory()->create();
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x']);

        $this->patch('/memos/' . $memo->id . '/move', ['folder_id' => null])->assertRedirect('/login');
    }

    public function test_deposer_une_fiche_sur_un_dossier_la_range_dedans(): void
    {
        $user = User::factory()->create();
        $folder = $user->memoFolders()->create(['name' => 'Laravel', 'icon' => 'folder', 'position' => 0]);
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x']);

        $this->actingAs($user)
            ->patch('/memos/' . $memo->id . '/move', ['folder_id' => $folder->id])
            ->assertRedirect();

        $this->assertSame($folder->id, $memo->fresh()->folder_id);
    }

    public function test_deposer_une_fiche_sur_la_racine_la_detache_de_son_dossier(): void
    {
        $user = User::factory()->create();
        $folder = $user->memoFolders()->create(['name' => 'Laravel', 'icon' => 'folder', 'position' => 0]);
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x', 'folder_id' => $folder->id]);

        $this->actingAs($user)
            ->patch('/memos/' . $memo->id . '/move', ['folder_id' => null])
            ->assertRedirect();

        $this->assertNull($memo->fresh()->folder_id);
    }

    public function test_deplacer_dans_le_dossier_dun_autre_est_refuse(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();
        $folderEtranger = $autre->memoFolders()->create(['name' => 'Privé', 'icon' => 'folder', 'position' => 0]);
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x']);

        $this->actingAs($user)
            ->patch('/memos/' . $memo->id . '/move', ['folder_id' => $folderEtranger->id])
            ->assertNotFound();

        $this->assertNull($memo->fresh()->folder_id);
    }

    public function test_un_intrus_ne_peut_pas_deplacer_la_fiche_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $folder = $intrus->memoFolders()->create(['name' => 'Dossier intrus', 'icon' => 'folder', 'position' => 0]);
        $memo = $proprietaire->memos()->create(['title' => 'Note privée', 'content' => 'x']);

        $this->actingAs($intrus)
            ->patch('/memos/' . $memo->id . '/move', ['folder_id' => $folder->id])
            ->assertForbidden();

        $this->assertNull($memo->fresh()->folder_id);
    }

    public function test_deplacer_ne_touche_ni_au_titre_ni_au_contenu(): void
    {
        $user = User::factory()->create();
        $folder = $user->memoFolders()->create(['name' => 'Laravel', 'icon' => 'folder', 'position' => 0]);
        $memo = $user->memos()->create(['title' => 'Titre original', 'content' => 'Contenu original']);

        $this->actingAs($user)->patch('/memos/' . $memo->id . '/move', ['folder_id' => $folder->id]);

        $memo->refresh();
        $this->assertSame('Titre original', $memo->title);
        $this->assertSame('Contenu original', $memo->content);
    }
}
