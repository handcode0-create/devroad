<?php

use App\Models\Roadmap;
use App\Services\RoadmapGenerator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Applique les chapitres Markdown des autres technologies (resources/courses/*) aux
     * roadmaps existantes. Les statuts et la progression sont conservés.
     */
    public function up(): void
    {
        $generator = app(RoadmapGenerator::class);

        Roadmap::query()
            ->whereIn('technology', ['react', 'nextjs', 'javascript', 'typescript', 'php', 'html', 'css', 'tailwind', 'node', 'git', 'github', 'docker', 'mysql', 'postgresql'])
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
