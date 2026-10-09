<?php

use App\Models\Roadmap;
use App\Services\RoadmapGenerator;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /** Applique les chapitres Python, Django, FastAPI et Flutter aux roadmaps existantes. */
    public function up(): void
    {
        $generator = app(RoadmapGenerator::class);

        Roadmap::query()
            ->whereIn('technology', ['python', 'django', 'fastapi', 'flutter'])
            ->orderBy('id')
            ->chunkById(100, function ($roadmaps) use ($generator): void {
                foreach ($roadmaps as $roadmap) {
                    $generator->syncRoadmap($roadmap);
                }
            });
    }

    public function down(): void
    {
    }
};
