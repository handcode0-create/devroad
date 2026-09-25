<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class MemoAttachmentTest extends TestCase
{
    use RefreshDatabase;

    private function memoDe(User $user)
    {
        return $user->memos()->create(['title' => 'Fiche', 'content' => 'contenu']);
    }

    public function test_un_visiteur_est_redirige_vers_la_connexion(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);

        $this->post('/memos/' . $memo->id . '/attachments', [])->assertRedirect('/login');
    }

    public function test_le_proprietaire_peut_joindre_un_fichier(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $fichier = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

        $this->actingAs($user)
            ->post('/memos/' . $memo->id . '/attachments', ['attachments' => [$fichier]])
            ->assertRedirect();

        $this->assertDatabaseHas('memo_attachments', [
            'memo_id' => $memo->id,
            'user_id' => $user->id,
            'name' => 'notes.txt',
        ]);
    }

    public function test_on_ne_peut_pas_joindre_un_fichier_a_la_fiche_dun_autre(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $memo = $this->memoDe($proprietaire);
        $fichier = UploadedFile::fake()->create('notes.txt', 10, 'text/plain');

        $this->actingAs($intrus)
            ->post('/memos/' . $memo->id . '/attachments', ['attachments' => [$fichier]])
            ->assertNotFound();

        $this->assertDatabaseCount('memo_attachments', 0);
    }

    public function test_un_type_de_fichier_non_autorise_est_refuse(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $fichier = UploadedFile::fake()->create('script.exe', 10, 'application/x-msdownload');

        $this->actingAs($user)
            ->post('/memos/' . $memo->id . '/attachments', ['attachments' => [$fichier]])
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('memo_attachments', 0);
    }

    public function test_un_fichier_trop_lourd_est_refuse(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $fichier = UploadedFile::fake()->create('gros.pdf', 5121, 'application/pdf'); // > 5120 Ko

        $this->actingAs($user)
            ->post('/memos/' . $memo->id . '/attachments', ['attachments' => [$fichier]])
            ->assertSessionHasErrors('attachments.0');

        $this->assertDatabaseCount('memo_attachments', 0);
    }

    public function test_plus_de_huit_fichiers_a_la_fois_est_refuse(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $fichiers = collect(range(1, 9))->map(fn ($i) => UploadedFile::fake()->create("fichier{$i}.txt", 1, 'text/plain'))->all();

        $this->actingAs($user)
            ->post('/memos/' . $memo->id . '/attachments', ['attachments' => $fichiers])
            ->assertSessionHasErrors('attachments');

        $this->assertDatabaseCount('memo_attachments', 0);
    }

    public function test_une_image_saffiche_directement_avec_len_tete_nosniff(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $attachment = $memo->attachments()->create([
            'user_id' => $user->id,
            'name' => 'photo.png',
            'mime_type' => 'image/png',
            'size' => 3,
            'data' => 'abc',
        ]);

        $response = $this->actingAs($user)->get('/memos/attachments/' . $attachment->id);

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->assertStringStartsWith('inline', $response->headers->get('Content-Disposition'));
    }

    public function test_un_fichier_texte_est_toujours_propose_en_telechargement_pas_en_affichage_direct(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $attachment = $memo->attachments()->create([
            'user_id' => $user->id,
            'name' => 'notes.txt',
            'mime_type' => 'text/plain',
            'size' => 3,
            'data' => 'abc',
        ]);

        $response = $this->actingAs($user)->get('/memos/attachments/' . $attachment->id);

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        // Pas de sniffing possible côté navigateur : jamais affiché en ligne pour un type non listé comme sûr.
        $this->assertStringStartsWith('attachment', $response->headers->get('Content-Disposition'));
    }

    public function test_un_intrus_ne_peut_ni_voir_ni_telecharger_ni_supprimer_une_piece_jointe(): void
    {
        $proprietaire = User::factory()->create();
        $intrus = User::factory()->create();
        $memo = $this->memoDe($proprietaire);
        $attachment = $memo->attachments()->create([
            'user_id' => $proprietaire->id,
            'name' => 'privé.txt',
            'mime_type' => 'text/plain',
            'size' => 3,
            'data' => 'abc',
        ]);

        $this->actingAs($intrus)->get('/memos/attachments/' . $attachment->id)->assertNotFound();
        $this->actingAs($intrus)->get('/memos/attachments/' . $attachment->id . '/download')->assertNotFound();
        $this->actingAs($intrus)->delete('/memos/attachments/' . $attachment->id)->assertNotFound();

        $this->assertDatabaseHas('memo_attachments', ['id' => $attachment->id]);
    }

    public function test_le_proprietaire_peut_supprimer_sa_piece_jointe(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $attachment = $memo->attachments()->create([
            'user_id' => $user->id,
            'name' => 'a-jeter.txt',
            'mime_type' => 'text/plain',
            'size' => 3,
            'data' => 'abc',
        ]);

        $this->actingAs($user)
            ->delete('/memos/attachments/' . $attachment->id)
            ->assertRedirect();

        $this->assertDatabaseMissing('memo_attachments', ['id' => $attachment->id]);
    }

    public function test_une_fiche_ne_peut_pas_depasser_huit_pieces_jointes_meme_en_modification(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        foreach (range(1, 8) as $i) {
            $memo->attachments()->create([
                'user_id' => $user->id,
                'name' => "existant{$i}.txt",
                'mime_type' => 'text/plain',
                'size' => 1,
                'data' => 'a',
            ]);
        }

        $fichier = UploadedFile::fake()->create('neuvieme.txt', 1, 'text/plain');
        $this->actingAs($user)
            ->put(route('memos.update', $memo), [
                'title' => 'Fiche',
                'content' => 'contenu',
                'attachments' => [$fichier],
            ])
            ->assertStatus(422);

        $this->assertSame(8, $memo->attachments()->count());
    }

    public function test_supprimer_une_piece_jointe_retire_aussi_ses_references_inline(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $attachment = $memo->attachments()->create([
            'user_id' => $user->id,
            'name' => 'image.png',
            'mime_type' => 'image/png',
            'size' => 3,
            'data' => 'abc',
        ]);
        $memo->update([
            'content' => '<p>Avant</p><figure data-attachment-id="' . $attachment->id . '"><img src="/memos/attachments/' . $attachment->id . '" alt="image"></figure><p><a href="/memos/attachments/' . $attachment->id . '/download">image.png</a></p><p>Après</p>',
        ]);

        $this->actingAs($user)
            ->delete('/memos/attachments/' . $attachment->id)
            ->assertRedirect();

        $saved = $memo->fresh()->content;
        $this->assertStringNotContainsString('data-attachment-id="' . $attachment->id . '"', $saved);
        $this->assertStringNotContainsString('/memos/attachments/' . $attachment->id, $saved);
        $this->assertStringContainsString('Avant', $saved);
        $this->assertStringContainsString('Après', $saved);
    }

    public function test_supprimer_une_fiche_supprime_ses_pieces_jointes(): void
    {
        $user = User::factory()->create();
        $memo = $this->memoDe($user);
        $memo->attachments()->create([
            'user_id' => $user->id,
            'name' => 'a.txt',
            'mime_type' => 'text/plain',
            'size' => 1,
            'data' => 'a',
        ]);

        $this->actingAs($user)->delete('/memos/' . $memo->id)->assertRedirect();

        $this->assertDatabaseHas('memo_attachments', ['memo_id' => $memo->id]);

        $this->actingAs($user)->delete('/memos/' . $memo->id . '/force-delete')->assertRedirect();
        $this->assertDatabaseMissing('memo_attachments', ['memo_id' => $memo->id]);
    }
}
