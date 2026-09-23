<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->string('difficulty_level', 32)->nullable()->index();
            $table->string('academic_level', 32)->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->dropColumn(['difficulty_level', 'academic_level']);
        });
    }
};
