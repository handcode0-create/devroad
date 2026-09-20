<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devlab_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('devlab_project_id')->constrained('devlab_projects')->cascadeOnDelete();
            $table->string('path', 180);
            $table->longText('content');
            $table->unsignedInteger('size')->default(0);
            $table->timestamps();

            $table->unique(['devlab_project_id', 'path']);
            $table->index(['devlab_project_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devlab_files');
    }
};
