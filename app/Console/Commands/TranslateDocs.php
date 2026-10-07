<?php

namespace App\Console\Commands;

use App\Models\DocSource;
use App\Services\Ai\AiException;
use App\Services\Docs\DocsLibrary;
use App\Services\Docs\DocsTranslator;
use Illuminate\Console\Command;
use Throwable;

/**
 * Pré-traduit en français les pages anglaises d'une documentation avec la clé DevRoad,
 * pour que les élèves les trouvent déjà prêtes (sans attendre ni consommer leur quota).
 */
class TranslateDocs extends Command
{
    protected $signature = 'docs:translate
        {source : Clé de la documentation (laravel, node, typescript, tailwindcss, git, postgresql…)}
        {--limit=20 : Nombre maximum de pages à traduire pendant cet appel}
        {--path=* : Chemins de pages précis (sinon : dans l\'ordre de l\'index)}';

    protected $description = 'Traduit en français des pages de documentation anglaises avec la clé DevRoad (DEVROAD_TRANSLATE_*).';

    public function handle(DocsLibrary $library, DocsTranslator $translator): int
    {
        if (! $translator->platformEnabled()) {
            $this->error('Aucune clé DevRoad : renseigne DEVROAD_TRANSLATE_PROVIDER et DEVROAD_TRANSLATE_API_KEY.');

            return self::FAILURE;
        }

        $source = DocSource::where('key', $this->argument('source'))->first();
        if (! $source) {
            $this->error('Documentation inconnue ou non synchronisée : ' . $this->argument('source'));

            return self::FAILURE;
        }

        $paths = $this->option('path') ?: $source->entries()->orderBy('position')->pluck('path')->unique()->values()->all();
        $limit = max(1, (int) $this->option('limit'));
        $done = 0;

        foreach ($paths as $path) {
            if ($done >= $limit) {
                break;
            }
            if ($source->pages()->where('path', $path)->where('locale', 'fr')->where('missing', false)->exists()) {
                continue;
            }

            try {
                $english = $library->page($source, $path);
                $total = count($translator->chunks($english));
                for ($chunk = 0; $chunk < $total; $chunk++) {
                    $translator->translateChunk(null, $english, $chunk);
                }
                $this->line("  ✔ {$path} ({$total} passage" . ($total > 1 ? 's' : '') . ')');
                $done++;
            } catch (AiException $exception) {
                $this->error("  ✘ {$path} : " . $exception->getMessage());

                return self::FAILURE;
            } catch (Throwable $exception) {
                $this->warn("  - {$path} ignorée : " . $exception->getMessage());
            }
        }

        $this->info("{$done} page(s) traduite(s). Relance la commande pour continuer.");

        return self::SUCCESS;
    }
}
