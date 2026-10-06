<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_groups', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // le professeur
            $table->string('name');
            $table->string('technology', 40)->nullable(); // clé de config/devroad_courses.php
            $table->string('join_code', 12)->unique();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });

        Schema::create('teaching_group_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('teaching_group_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // l'élève
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();

            $table->unique(['teaching_group_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_group_members');
        Schema::dropIfExists('teaching_groups');
    }
};
