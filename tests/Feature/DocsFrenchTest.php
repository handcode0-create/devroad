<?php

namespace Tests\Feature;

use App\Models\DocEntry;
use App\Models\DocPage;
use App\Models\DocSource;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

/** Documentation en français d'abord : MDN/React/PHP officiels, sinon traduction par l'IA. */
class DocsFrenchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function source(string $key, string $provider = 'devdocs'): DocSource
    {
        $source = DocSource::create(['key' => $key, 'provider' => $provider, 'remote_slug' => $provider === 'laravel' ? '12.x' : $key, 'name' => ucfirst($key), 'entries_count' => 1, 'synced_at' => now()]);
        DocEntry::create(['doc_source_id' => $source->id, 'name' => 'x', 'search_name' => 'x', 'path' => 'x']);

        return $source;
    }

    public function test_la_version_francaise_de_mdn_est_affichee_par_defaut(): void
    {
        $this->source('html');
        Http::fake([
            '*/files/fr/_redirects.txt' => Http::response("/fr/docs/Web/HTML/Element/div\t/fr/docs/Web/HTML/Reference/Elements/div\n/fr/docs/Web/HTML/Element/span\t/fr/docs/Web/HTML/Reference/Elements/span\n"),
            '*/files/fr/web/html/reference/elements/div/index.md' => Http::response("---\ntitle: \"Élément HTML `<div>`\"\nslug: Web/HTML/Reference/Elements/div\n---\n\nL'élément **`<div>`** est un conteneur générique. {{HTMLElement(\"span\")}} {{Compat}}\n\n## Attributs\n\n- `align` {{Deprecated_Inline}}\n  - : Alignement.\n\nVoir [le guide](/fr/docs/Web/HTML/Element/span#exemples).\n"),
        ]);

        $this->actingAs(User::factory()->create())->get('/docs/html/element/div')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('page.locale', 'fr')
                ->where('french', 'official')
                ->where('page.title', 'Élément HTML <div>')
                ->where('page.html', fn ($html) => str_contains($html, 'conteneur générique')
                    && str_contains($html, '<em>(obsolète)</em>')
                    && ! str_contains($html, '{{')
                    && str_contains($html, 'href="/docs/html/reference/elements/span"')
                    && str_contains($html, 'href="/docs/html/reference/elements/span#exemples"'))
                ->where('originalFrUrl', 'https://developer.mozilla.org/fr/docs/Web/HTML/Reference/Elements/div'));
    }

    public function test_sans_traduction_officielle_la_page_anglaise_est_affichee_et_l_absence_memorisee(): void
    {
        $source = $this->source('javascript');
        Http::fake([
            '*/files/fr/_redirects.txt' => Http::response(''),
            '*/files/fr/web/javascript/reference/global_objects/temporal/index.md' => Http::response('', 404),
            'documents.devdocs.io/javascript/global_objects/temporal.html' => Http::response('<h1>Temporal</h1><p>The Temporal object.</p>'),
        ]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/docs/javascript/global_objects/temporal')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('page.locale', 'en')->where('french', 'missing')->where('translation.total', 1));
        $this->assertTrue(DocPage::where('locale', 'fr')->where('path', 'global_objects/temporal')->value('missing'));

        // Deuxième visite : on ne redemande pas la version française.
        Http::fake(fn () => throw new \RuntimeException('Pas de réseau attendu.'));
        $this->actingAs($user)->get('/docs/javascript/global_objects/temporal')->assertOk();
        $this->assertSame(1, $source->pages()->where('locale', 'en')->count());
    }

    public function test_l_anglais_reste_disponible_et_la_preference_est_enregistree(): void
    {
        $source = $this->source('laravel', 'laravel');
        DocPage::create(['doc_source_id' => $source->id, 'path' => 'routing', 'locale' => 'en', 'title' => 'Routing', 'html' => '<p>Routes</p>', 'text' => 'Routes']);
        $user = User::factory()->create();
        $this->assertSame('fr', $user->fresh()->docs_locale);

        $this->actingAs($user)->get('/docs/laravel/routing')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('page.locale', 'en')->where('french', 'none')->where('want', 'fr'));

        $this->actingAs($user)->from('/docs')->patch('/docs/locale', ['locale' => 'en'])->assertRedirect('/docs');
        $this->assertSame('en', $user->fresh()->docs_locale);
        $this->actingAs($user)->get('/docs/laravel/routing')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('want', 'en')->where('translation', null));
    }

    public function test_une_page_anglaise_est_traduite_par_morceaux_puis_servie_en_francais_a_tous(): void
    {
        $source = $this->source('laravel', 'laravel');
        DocPage::create(['doc_source_id' => $source->id, 'path' => 'routing', 'locale' => 'en', 'title' => 'Routing', 'html' => '<h2 id="basic">Basic Routing</h2><p>The most basic routes accept a URI.</p><pre><code>Route::get(\'/\');</code></pre>', 'text' => '']);
        Http::fake(['api.anthropic.com/*' => Http::response(['content' => [['type' => 'text', 'text' => "```html\n<h1>Routage</h1><h2 id=\"basic\">Routage de base</h2><p>Les routes les plus simples acceptent une URI.</p><pre><code>Route::get('/');</code></pre>\n```"]]])]);
        $translator = User::factory()->create();
        $translator->forceFill(['ai_provider' => 'anthropic', 'ai_api_key' => 'sk-ant-key-0000', 'ai_key_hint' => '0000'])->save();

        $this->actingAs($translator)->postJson('/docs/translate', ['source' => 'laravel', 'path' => 'routing', 'chunk' => 0])
            ->assertOk()->assertJsonPath('total', 1)->assertJsonPath('done', true)->assertJsonPath('title', 'Routage');

        Http::assertSent(fn ($request) => str_contains($request['system'], 'Ne traduis jamais le contenu des balises <code>'));

        // Un autre utilisateur, sans clé d'IA, lit directement la traduction.
        $this->actingAs(User::factory()->create())->get('/docs/laravel/routing')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('page.locale', 'fr')
                ->where('french', 'machine')
                ->where('page.title', 'Routage')
                ->where('page.html', fn ($html) => str_contains($html, 'Les routes les plus simples') && ! str_contains($html, '```')));
    }

    public function test_traduire_sans_cle_explique_comment_faire(): void
    {
        $source = $this->source('laravel', 'laravel');
        DocPage::create(['doc_source_id' => $source->id, 'path' => 'routing', 'locale' => 'en', 'title' => 'Routing', 'html' => '<p>Routes</p>', 'text' => '']);

        $this->actingAs(User::factory()->create())->postJson('/docs/translate', ['source' => 'laravel', 'path' => 'routing', 'chunk' => 0])
            ->assertStatus(422)->assertJsonPath('message', 'Ajoute ta clé d’IA dans Paramètres → Assistant IA pour traduire cette page.');
    }

    public function test_react_en_francais_garde_les_ancres_et_les_encadres(): void
    {
        $this->source('react');
        Http::fake([
            '*/fr.react.dev/main/src/sidebarReference.json' => Http::response(['routes' => [['path' => '/reference/react/useState']]]),
            '*/fr.react.dev/main/src/sidebarLearn.json' => Http::response([]),
            '*/fr.react.dev/main/src/content/reference/react/useState.md' => Http::response("---\ntitle: useState\n---\n\n<Intro>\n\n`useState` est un Hook React.\n\n</Intro>\n\n<InlineToc />\n\n## Utilisation {/*usage*/}\n\n<Pitfall>\n\nNe l'appelez pas dans une boucle.\n\n</Pitfall>\n\nVoir [l'état](/learn/state-a-components-memory).\n"),
        ]);

        $this->actingAs(User::factory()->create())->get('/docs/react/reference/react/usestate')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->where('page.locale', 'fr')
                ->where('page.html', fn ($html) => str_contains($html, '<h2 id="usage">Utilisation</h2>')
                    && str_contains($html, '<strong>Piège</strong>')
                    && ! str_contains($html, 'Intro')
                    && str_contains($html, 'href="/docs/react/learn/state-a-components-memory"')));
    }
}
