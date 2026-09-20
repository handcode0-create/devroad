<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('devlab_projects', function (Blueprint $table) {
            $table->foreignId('roadmap_step_id')
                ->nullable()
                ->after('user_id')
                ->constrained('roadmap_steps')
                ->nullOnDelete();

            $table->unique(['user_id', 'roadmap_step_id']);
        });
    }

    public function down(): void
    {
        Schema::table('devlab_projects', function (Blueprint $table) {
            $table->dropUnique('devlab_projects_user_id_roadmap_step_id_unique');
            $table->dropForeign(['roadmap_step_id']);
            $table->dropColumn('roadmap_step_id');
        });
    }
};
