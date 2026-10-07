<?php

namespace Tests\Feature;

use App\Models\DocPage;
use App\Models\DocSource;
use App\Models\User;
use App\Services\Docs\DocHtml;
use App\Services\Docs\DocsLibrary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class DocsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::preventStrayRequests();
    }

    private function fakeDevDocs(): void
    {
        Http::fake([
            'devdocs.io/docs.json' => Http::response([
                ['name' => 'JavaScript', 'slug' => 'javascript', 'type' => 'mdn', 'mtime' => 1700000000, 'attribution' => '&copy; MDN contributors<br>Licensed under CC-BY-SA 2.5.<script>alert(1)</script>', 'links' => ['home' => 'https://developer.mozilla.org']],
                ['name' => 'Node.js', 'slug' => 'node~22_lts', 'type' => 'node', 'version' => '22 LTS', 'mtime' => 1700000001],
            ]),
            'documents.devdocs.io/javascript/index.json*' => Http::response(['entries' => [
                ['name' => 'Array.prototype.map()', 'path' => 'global_objects/array/map', 'type' => 'Array'],
                ['name' => 'Array', 'path' => 'global_objects/array', 'type' => 'Array'],
                ['name' => 'fetch()', 'path' => 'global_objects/fetch#syntax', 'type' => 'Fetch'],
            ], 'types' => []]),
            'documents.devdocs.io/javascript/global_objects/array/map.html' => Http::response(
                '<h1>Array.prototype.map()</h1><p onclick="steal()">The <code>map()</code> method creates a new array.</p>'
                . '<h2 id="syntax">Syntax</h2><pre data-language="js">arr.map(fn)</pre><script>alert(1)</script>'
                . '<p>See <a href="../array">Array</a>, <a href="https://example.com">site</a> and <a href="javascript:alert(1)">x</a>.</p>'
            ),
        ]);
    }

    public function test_la_page_documentation_demande_d_etre_connecte(): void
    {
        $this->get('/docs')->assertRedirect('/login');
    }

    public function test_synchroniser_devdocs_cree_l_index_et_la_recherche_le_trouve(): void
    {
        $this->fakeDevDocs();
        $this->artisan('docs:sync', ['source' => ['javascript']])->assertSuccessful();

        $source = DocSource::where('key', 'javascript')->firstOrFail();
        $this->assertSame(3, $source->entries_count);
        $this->assertStringNotContainsString('<script', $source->attribution);
        $this->assertDatabaseHas('doc_entries', ['path' => 'global_objects/fetch', 'fragment' => 'syntax']);

        $user = User::factory()->create();
        $this->actingAs($user)->get('/docs?q=map')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Docs/Index')
                ->where('results.entries.0.name', 'Array.prototype.map()')
                ->where('results.entries.0.url', '/docs/javascript/global_objects/array/map'));
    }

    public function test_choisir_une_doc_sans_recherche_liste_ses_categories_et_ses_entrees(): void
    {
        $this->fakeDevDocs();
        $this->artisan('docs:sync', ['source' => ['javascript']])->assertSuccessful();
        $user = User::factory()->create();

        $this->actingAs($user)->get('/docs?source=javascript')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Docs/Index')
                ->where('results', null)
                ->has('browse.types')
                ->where('browse.total', fn ($total) => $total >= 1)
                ->has('browse.entries.0.url'));

        // Une recherche remplace la vue « parcourir ».
        $this->actingAs($user)->get('/docs?source=javascript&q=map')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('browse', null));

        // Sans doc choisie : pas de vue « parcourir ».
        $this->actingAs($user)->get('/docs')->assertInertia(fn (AssertableInertia $page) => $page->where('browse', null));
    }

    public function test_une_page_est_telechargee_nettoyee_puis_mise_en_cache(): void
    {
        $this->fakeDevDocs();
        $this->artisan('docs:sync', ['source' => ['javascript']]);
        $user = User::factory()->create();

        $this->actingAs($user)->get('/docs/javascript/global_objects/array/map?lang=en')
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Docs/Show')
                ->where('page.title', 'Array.prototype.map()')
                ->where('page.headings.0.id', 'syntax')
                ->where('page.html', fn ($html) => ! str_contains($html, '<script')
                    && ! str_contains($html, 'onclick')
                    && ! str_contains($html, 'javascript:')
                    && str_contains($html, 'href="/docs/javascript/global_objects/array"')
                    && str_contains($html, 'target="_blank"')));

        // Deuxième lecture : servie depuis le cache, sans nouvel appel réseau.
        Http::fake(fn () => throw new \RuntimeException('Pas de réseau attendu.'));
        $this->actingAs($user)->get('/docs/javascript/global_objects/array/map?lang=en')->assertOk();
        $this->assertSame(1, DocPage::count());
    }

    public function test_une_page_injoignable_affiche_un_etat_d_erreur(): void
    {
        $this->fakeDevDocs();
        $this->artisan('docs:sync', ['source' => ['javascript']]);
        Http::fake(['documents.devdocs.io/*' => Http::response('', 503)]);

        $this->actingAs(User::factory()->create())->get('/docs/javascript/global_objects/array?lang=en')
            ->assertInertia(fn (AssertableInertia $page) => $page->where('page', null)->where('error', fn ($error) => filled($error)));
    }

    public function test_le_slug_versionne_le_plus_recent_est_choisi(): void
    {
        $this->fakeDevDocs();
        Http::fake(['documents.devdocs.io/node~22_lts/index.json*' => Http::response(['entries' => [['name' => 'fs', 'path' => 'fs', 'type' => 'fs']]])]);
        app(DocsLibrary::class)->sync('node');

        $this->assertSame('node~22_lts', DocSource::where('key', 'node')->value('remote_slug'));
    }

    public function test_resolution_des_liens_relatifs(): void
    {
        $this->assertSame('global_objects/array', DocHtml::resolvePath('global_objects/array/map', '../array'));
        $this->assertSame('global_objects/array/filter#syntax', DocHtml::resolvePath('global_objects/array/map', 'filter#syntax'));
        $this->assertSame('dom/index', DocHtml::resolvePath('a/b', '/dom/index.html'));
    }

    public function test_une_source_en_echec_ne_bloque_pas_les_autres(): void
    {
        Http::fake(['*' => Http::response('', 500)]);
        $this->artisan('docs:sync', ['source' => ['javascript', 'css']])->assertFailed();
    }
}
