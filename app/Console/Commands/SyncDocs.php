<?php

namespace App\Console\Commands;

use App\Services\Docs\DocsLibrary;
use Illuminate\Console\Command;
use Throwable;

class SyncDocs extends Command
{
    protected $signature = 'docs:sync
        {source?* : Clés des sources (par défaut : toutes)}
        {--if-stale : Ne synchronise que les sources absentes ou trop anciennes}';

    protected $description = 'Télécharge les index de la page Documentation (DevDocs et docs officielles Laravel).';

    public function handle(DocsLibrary $library): int
    {
        $keys = $this->argument('source') ?: array_keys(config('devroad_docs.sources'));
        $failures = 0;

        foreach ($keys as $key) {
            if ($this->option('if-stale') && ! $library->isStale($key)) {
                $this->line("  $key : à jour");
                continue;
            }
            try {
                $result = $library->sync($key);
                $this->info("  $key : {$result['entries']} entrées");
            } catch (Throwable $exception) {
                $failures++;
                // Une source indisponible ne doit pas bloquer les autres (ni le déploiement).
                $this->warn("  $key : échec — " . $exception->getMessage());
            }
        }

        return $failures === count($keys) && $keys !== [] ? self::FAILURE : self::SUCCESS;
    }
}
