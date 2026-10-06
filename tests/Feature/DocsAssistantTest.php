<?php

namespace Tests\Feature;

use App\Models\DocEntry;
use App\Models\DocPage;
use App\Models\DocSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DocsAssistantTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function seedDocs(): void
    {
        $source = DocSource::create(['key' => 'laravel', 'provider' => 'laravel', 'remote_slug' => '12.x', 'name' => 'Laravel', 'entries_count' => 1, 'synced_at' => now()]);
        DocEntry::create(['doc_source_id' => $source->id, 'name' => 'Eloquent: Relationships', 'search_name' => 'eloquent: relationships', 'type' => 'Eloquent ORM', 'path' => 'eloquent-relationships']);
        DocPage::create(['doc_source_id' => $source->id, 'path' => 'eloquent-relationships', 'title' => 'Eloquent: Relationships', 'html' => '<p>belongsToMany</p>', 'text' => 'Many-to-many relations use the belongsToMany method and a pivot table.']);
    }

    private function userWithKey(array $attributes = []): User
    {
        $user = User::factory()->create();
        $user->forceFill($attributes + ['ai_provider' => 'anthropic', 'ai_model' => 'claude-haiku-4-5', 'ai_api_key' => 'sk-ant-secret-1234', 'ai_key_hint' => '1234'])->save();

        return $user;
    }

    public function test_la_cle_est_testee_chiffree_et_jamais_renvoyee_au_navigateur(): void
    {
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => 'OK']]])]);
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->put('/profile/ai', ['provider' => 'anthropic', 'api_key' => 'sk-ant-api03-abcdefWXYZ'])
            ->assertRedirect('/profile')->assertSessionHasNoErrors();

        Http::assertSent(fn ($request) => $request->hasHeader('x-api-key', 'sk-ant-api03-abcdefWXYZ'));
        $user->refresh();
        $this->assertSame('WXYZ', $user->ai_key_hint);
        $this->assertSame('sk-ant-api03-abcdefWXYZ', $user->ai_api_key);
        $this->assertStringNotContainsString('abcdefWXYZ', DB::table('users')->where('id', $user->id)->value('ai_api_key'));

        $this->actingAs($user)->get('/profile')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('aiSettings.enabled', true)
            ->where('aiSettings.hint', 'WXYZ')
            ->where('auth.user', fn ($shared) => ! array_key_exists('ai_api_key', collect($shared)->all())));
        $this->assertStringNotContainsString('abcdefWXYZ', $this->actingAs($user)->get('/profile')->getContent());
    }

    public function test_une_cle_refusee_n_est_pas_enregistree_et_le_message_est_clair(): void
    {
        Http::fake(['api.openai.com/*' => Http::response(['error' => ['message' => 'Incorrect API key provided']], 401)]);
        $user = User::factory()->create();

        $this->actingAs($user)->from('/profile')->put('/profile/ai', ['provider' => 'openai', 'api_key' => 'sk-wrong-key-000'])
            ->assertSessionHasErrors(['api_key' => 'Clé refusée par OpenAI. Vérifie qu’elle est complète et toujours active.']);
        $this->assertNull($user->fresh()->ai_api_key);
    }

    public function test_supprimer_la_cle_desactive_l_assistant(): void
    {
        $user = $this->userWithKey();
        $this->actingAs($user)->from('/profile')->delete('/profile/ai')->assertRedirect('/profile');
        $this->assertFalse($user->fresh()->hasAiAssistant());
    }

    public function test_l_ia_repond_a_partir_de_la_doc_avec_les_sources(): void
    {
        $this->seedDocs();
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => "Utilise `belongsToMany` [1].\n\n```php\n\$tags[1];\n```"]]])]);

        $response = $this->actingAs($this->userWithKey())->postJson('/docs/ask', ['question' => 'Comment faire une relation many-to-many avec Eloquent ?']);

        $response->assertOk()
            ->assertJsonPath('sources.0.title', 'Eloquent: Relationships')
            ->assertJsonPath('sources.0.url', '/docs/laravel/eloquent-relationships');
        $this->assertStringContainsString('<a href="/docs/laravel/eloquent-relationships" data-source="1">[1]</a>', $response->json('html'));
        // Les crochets dans le code ne deviennent pas des liens.
        $this->assertStringContainsString('$tags[1];', $response->json('html'));

        Http::assertSent(fn ($request) => str_contains($request['messages'][0]['content'], 'belongsToMany method and a pivot table')
            && str_contains($request['system'], 'UNIQUEMENT'));
    }

    public function test_sans_cle_l_assistant_explique_comment_l_activer(): void
    {
        $this->seedDocs();
        $this->actingAs(User::factory()->create())->postJson('/docs/ask', ['question' => 'relation eloquent'])
            ->assertStatus(422)->assertJsonPath('message', 'Ajoute d’abord ta clé d’IA dans Paramètres → Assistant IA.');
    }

    public function test_quota_epuise_donne_un_message_comprehensible(): void
    {
        $this->seedDocs();
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response(['error' => ['message' => 'Resource has been exhausted (e.g. check quota).']], 429)]);
        $user = $this->userWithKey(['ai_provider' => 'gemini', 'ai_model' => 'gemini-2.5-flash']);

        $this->actingAs($user)->postJson('/docs/ask', ['question' => 'eloquent relationships'])
            ->assertStatus(422)->assertJsonPath('message', fn ($message) => str_contains($message, 'Quota ou crédit épuisé chez Google Gemini'));
    }

    public function test_garder_une_reponse_en_fiche_memo(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->from('/docs')->post('/docs/memo', ['title' => 'Relation many-to-many', 'html' => '<p>Réponse <script>x</script></p>'])
            ->assertRedirect('/docs')->assertSessionHas('undo');

        $memo = $user->memos()->firstOrFail();
        $this->assertSame('Relation many-to-many', $memo->title);
        $this->assertStringNotContainsString('<script', $memo->content);
    }
}
