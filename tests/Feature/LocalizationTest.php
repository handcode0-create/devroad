<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_les_messages_d_authentification_existent_en_francais(): void
    {
        app()->setLocale('fr');

        $this->assertSame(
            'Adresse e-mail ou mot de passe incorrect. Vérifie-les et réessaie.',
            __('auth.failed')
        );
    }

    public function test_un_identifiant_incorrect_renvoie_l_erreur_en_francais(): void
    {
        app()->setLocale('fr');
        $user = User::factory()->create();

        $this->post('/login', ['email' => $user->email, 'password' => 'mauvais-mot-de-passe'])
            ->assertSessionHasErrors(['email' => 'Adresse e-mail ou mot de passe incorrect. Vérifie-les et réessaie.']);
    }

    public function test_les_erreurs_d_inscription_sont_en_francais(): void
    {
        app()->setLocale('fr');
        User::factory()->create(['email' => 'deja@exemple.com']);

        $this->post('/register', [
            'name' => '',
            'email' => 'deja@exemple.com',
            'password' => 'court',
            'password_confirmation' => 'autre',
        ])->assertSessionHasErrors([
            'name' => 'Le champ nom est obligatoire.',
            'email' => 'Cette adresse e-mail a déjà un compte. Connecte-toi ou utilise une autre adresse.',
            'password' => 'La confirmation du champ mot de passe ne correspond pas.',
        ]);
    }

    public function test_les_clefs_manquantes_retombent_sur_l_anglais_sans_afficher_de_clef_brute(): void
    {
        app()->setLocale('fr');

        // « between » n'est pas traduit : on doit obtenir un texte, pas « validation.between.numeric ».
        $this->assertNotSame('validation.between.numeric', __('validation.between.numeric'));
    }
}
