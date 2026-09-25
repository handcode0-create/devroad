<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MemoFolderTest extends TestCase
{
    use RefreshDatabase;

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $this->post('/memo-folders', ['name' => 'Travail'])->assertRedirect('/login');
    }

    public function test_un_dossier_racine_est_cree_en_fin_de_liste(): void
    {
        $user = User::factory()->create();
        $user->memoFolders()->create(['name' => 'Existant', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($user)
            ->post('/memo-folders', ['name' => 'Nouveau'])
            ->assertRedirect();

        $this->assertDatabaseHas('memo_folders', [
            'user_id' => $user->id,
            'name' => 'Nouveau',
            'parent_id' => null,
            'position' => 1,
        ]);
    }

    public function test_un_sous_dossier_peut_etre_cree(): void
    {
        $user = User::factory()->create();
        $parent = $user->memoFolders()->create(['name' => 'Projets', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($user)
            ->post('/memo-folders', ['name' => 'DevRoad', 'parent_id' => $parent->id])
            ->assertRedirect();

        $this->assertDatabaseHas('memo_folders', ['name' => 'DevRoad', 'parent_id' => $parent->id]);
    }

    public function test_on_ne_peut_pas_creer_un_dossier_dans_le_dossier_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $parent = $proprietaire->memoFolders()->create(['name' => 'Privé', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($intrus)
            ->post('/memo-folders', ['name' => 'Intrusion', 'parent_id' => $parent->id])
            ->assertNotFound();

        $this->assertDatabaseCount('memo_folders', 1);
    }

    public function test_un_nom_vide_est_refuse(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/memo-folders', ['name' => '   '])
            ->assertSessionHasErrors('name');
    }

    public function test_le_proprietaire_peut_renommer_son_dossier(): void
    {
        $user = User::factory()->create();
        $folder = $user->memoFolders()->create(['name' => 'Ancien nom', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($user)
            ->patch('/memo-folders/' . $folder->id, ['name' => 'Nouveau nom'])
            ->assertRedirect();

        $this->assertSame('Nouveau nom', $folder->fresh()->name);
    }

    public function test_un_intrus_ne_peut_ni_renommer_ni_deplacer_ni_supprimer(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $folder = $proprietaire->memoFolders()->create(['name' => 'Privé', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($intrus)->patch('/memo-folders/' . $folder->id, ['name' => 'Piraté'])->assertNotFound();
        $this->actingAs($intrus)->delete('/memo-folders/' . $folder->id)->assertNotFound();

        $this->assertSame('Privé', $folder->fresh()->name);
        $this->assertNotNull($folder->fresh());
    }

    public function test_un_dossier_ne_peut_pas_devenir_son_propre_parent(): void
    {
        $user = User::factory()->create();
        $folder = $user->memoFolders()->create(['name' => 'Boucle', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($user)
            ->patch('/memo-folders/' . $folder->id, ['parent_id' => $folder->id])
            ->assertStatus(422);

        $this->assertNull($folder->fresh()->parent_id);
    }

    public function test_un_dossier_ne_peut_pas_etre_deplace_dans_un_de_ses_sous_dossiers(): void
    {
        $user = User::factory()->create();
        $racine = $user->memoFolders()->create(['name' => 'Racine', 'icon' => 'folder', 'position' => 0]);
        $enfant = $user->memoFolders()->create(['name' => 'Enfant', 'icon' => 'folder', 'position' => 0, 'parent_id' => $racine->id]);
        $petitEnfant = $user->memoFolders()->create(['name' => 'Petit-enfant', 'icon' => 'folder', 'position' => 0, 'parent_id' => $enfant->id]);

        // Déposer « Racine » sur son propre petit-enfant créerait une boucle.
        $this->actingAs($user)
            ->patch('/memo-folders/' . $racine->id, ['parent_id' => $petitEnfant->id])
            ->assertStatus(422);

        $this->assertNull($racine->fresh()->parent_id);
    }

    public function test_deplacer_un_dossier_vers_un_autre_parent_le_met_en_fin_de_groupe(): void
    {
        $user = User::factory()->create();
        $ancienParent = $user->memoFolders()->create(['name' => 'Ancien', 'icon' => 'folder', 'position' => 0]);
        $nouveauParent = $user->memoFolders()->create(['name' => 'Nouveau', 'icon' => 'folder', 'position' => 1]);
        $nouveauParent2 = $user->memoFolders()->create(['name' => 'Déjà présent', 'icon' => 'folder', 'position' => 0, 'parent_id' => $nouveauParent->id]);
        $dossier = $user->memoFolders()->create(['name' => 'Voyageur', 'icon' => 'folder', 'position' => 0, 'parent_id' => $ancienParent->id]);

        $this->actingAs($user)
            ->patch('/memo-folders/' . $dossier->id, ['parent_id' => $nouveauParent->id])
            ->assertRedirect();

        $dossier->refresh();
        $this->assertSame($nouveauParent->id, $dossier->parent_id);
        $this->assertSame(1, $dossier->position); // après « Déjà présent » (position 0)
    }

    public function test_remettre_un_dossier_a_la_racine(): void
    {
        $user = User::factory()->create();
        $parent = $user->memoFolders()->create(['name' => 'Parent', 'icon' => 'folder', 'position' => 0]);
        $enfant = $user->memoFolders()->create(['name' => 'Enfant', 'icon' => 'folder', 'position' => 0, 'parent_id' => $parent->id]);

        $this->actingAs($user)
            ->patch('/memo-folders/' . $enfant->id, ['parent_id' => null])
            ->assertRedirect();

        $this->assertNull($enfant->fresh()->parent_id);
    }

    public function test_reorganiser_les_dossiers_dun_meme_niveau(): void
    {
        $user = User::factory()->create();
        $a = $user->memoFolders()->create(['name' => 'A', 'icon' => 'folder', 'position' => 0]);
        $b = $user->memoFolders()->create(['name' => 'B', 'icon' => 'folder', 'position' => 1]);
        $c = $user->memoFolders()->create(['name' => 'C', 'icon' => 'folder', 'position' => 2]);

        $this->actingAs($user)
            ->patch('/memo-folders/reorder', [
                'parent_id' => null,
                'ordered_ids' => [$c->id, $a->id, $b->id],
            ])
            ->assertRedirect();

        $this->assertSame(1, $a->fresh()->position);
        $this->assertSame(2, $b->fresh()->position);
        $this->assertSame(0, $c->fresh()->position);
    }

    public function test_reorganiser_avec_une_liste_incomplete_est_refuse(): void
    {
        $user = User::factory()->create();
        $a = $user->memoFolders()->create(['name' => 'A', 'icon' => 'folder', 'position' => 0]);
        $user->memoFolders()->create(['name' => 'B', 'icon' => 'folder', 'position' => 1]);

        $this->actingAs($user)
            ->patch('/memo-folders/reorder', ['parent_id' => null, 'ordered_ids' => [$a->id]])
            ->assertStatus(422);
    }

    public function test_reorganiser_avec_le_dossier_dun_autre_est_refuse(): void
    {
        $user = User::factory()->create();
        $autre = User::factory()->create();
        $a = $user->memoFolders()->create(['name' => 'A', 'icon' => 'folder', 'position' => 0]);
        $etranger = $autre->memoFolders()->create(['name' => 'Étranger', 'icon' => 'folder', 'position' => 0]);

        $this->actingAs($user)
            ->patch('/memo-folders/reorder', ['parent_id' => null, 'ordered_ids' => [$a->id, $etranger->id]])
            ->assertStatus(422);

        $this->assertSame(0, $etranger->fresh()->position);
    }

    public function test_supprimer_un_dossier_recompose_les_positions_du_groupe_parent(): void
    {
        $user = User::factory()->create();
        $a = $user->memoFolders()->create(['name' => 'A', 'icon' => 'folder', 'position' => 0]);
        $middle = $user->memoFolders()->create(['name' => 'À supprimer', 'icon' => 'folder', 'position' => 1]);
        $b = $user->memoFolders()->create(['name' => 'B', 'icon' => 'folder', 'position' => 2]);
        $child = $user->memoFolders()->create(['name' => 'Enfant', 'icon' => 'folder', 'position' => 0, 'parent_id' => $middle->id]);

        $this->actingAs($user)
            ->delete('/memo-folders/' . $middle->id)
            ->assertRedirect();

        $this->assertSame(0, $a->fresh()->position);
        $this->assertSame(1, $b->fresh()->position);
        $this->assertSame(null, $child->fresh()->parent_id);
        $this->assertSame(2, $child->fresh()->position);
    }

    public function test_supprimer_un_dossier_detache_ses_fiches_et_remonte_ses_sous_dossiers(): void
    {
        $user = User::factory()->create();
        $parent = $user->memoFolders()->create(['name' => 'Parent', 'icon' => 'folder', 'position' => 0]);
        $enfant = $user->memoFolders()->create(['name' => 'Enfant', 'icon' => 'folder', 'position' => 0, 'parent_id' => $parent->id]);
        $memo = $user->memos()->create(['title' => 'Note', 'content' => 'x', 'folder_id' => $parent->id]);

        $this->actingAs($user)
            ->delete('/memo-folders/' . $parent->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('memo_folders', ['id' => $parent->id]);
        $this->assertNull($enfant->fresh()->parent_id);
        $this->assertNull($memo->fresh()->folder_id);
    }
}
