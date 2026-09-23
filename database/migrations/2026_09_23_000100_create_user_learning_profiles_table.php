<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_learning_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->string('academic_level', 32)->nullable();
            $table->string('level', 32)->nullable();
            $table->string('level_source', 32)->default('assessment');
            $table->unsignedSmallInteger('experience_years')->nullable();
            $table->json('technologies')->nullable();
            $table->json('goals')->nullable();
            $table->json('assessment_scores')->nullable();
            $table->json('assessment_answers')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_learning_profiles');
    }
};
