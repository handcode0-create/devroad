<?php

use App\Models\Roadmap;
use App\Services\RoadmapGenerator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Synchronise les roadmaps existantes avec les catalogues pédagogiques enrichis.
     *
     * Les statuts et la progression des étapes déjà présentes sont conservés.
     * Les étapes manquantes sont créées, mais les éventuelles étapes
     * supplémentaires ne sont pas supprimées afin d'éviter toute perte de données.
     */
    public function up(): void
    {
        $generator = app(RoadmapGenerator::class);

        Roadmap::query()
            ->orderBy('id')
            ->chunkById(100, function ($roadmaps) use ($generator): void {
                foreach ($roadmaps as $roadmap) {
                    $generator->syncRoadmap($roadmap);
                }
            });
    }

    public function down(): void
    {
        // Cette synchronisation met à jour du contenu pédagogique existant.
        // Aucun rollback destructif n'est effectué.
    }
};
