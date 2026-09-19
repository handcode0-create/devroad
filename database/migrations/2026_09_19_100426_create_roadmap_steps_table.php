<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roadmap_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('roadmap_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->unsignedInteger('position')->default(0);
            // todo | in_progress | completed | blocked
            $table->string('status', 20)->default('todo');
            $table->timestamps();

            // Étapes ordonnées + comptage des étapes terminées par roadmap
            $table->index(['roadmap_id', 'position']);
            $table->index(['roadmap_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roadmap_steps');
    }
};