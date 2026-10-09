<?php

use App\Models\Roadmap;
use App\Services\RoadmapGenerator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Applique les chapitres Markdown de Laravel (resources/courses/laravel) aux
     * roadmaps Laravel existantes. Les statuts et la progression sont conservés.
     */
    public function up(): void
    {
        $generator = app(RoadmapGenerator::class);

        Roadmap::query()
            ->where('technology', 'laravel')
            ->orderBy('id')
            ->chunkById(100, function ($roadmaps) use ($generator): void {
                foreach ($roadmaps as $roadmap) {
                    $generator->syncRoadmap($roadmap);
                }
            });
    }

    public function down(): void
    {
        // Mise à jour de contenu pédagogique : aucun retour en arrière destructif.
    }
};
