<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->text('exercise_title')->nullable()->after('code_example');
            $table->longText('exercise_description')->nullable()->after('exercise_title');
            $table->longText('exercise_hint')->nullable()->after('exercise_description');
            $table->longText('exercise_solution')->nullable()->after('exercise_hint');
            $table->timestamp('exercise_completed_at')->nullable()->after('exercise_solution');
            $table->timestamp('last_viewed_at')->nullable()->index()->after('exercise_completed_at');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->dropColumn([
                'exercise_title',
                'exercise_description',
                'exercise_hint',
                'exercise_solution',
                'exercise_completed_at',
                'last_viewed_at',
            ]);
        });
    }
};
