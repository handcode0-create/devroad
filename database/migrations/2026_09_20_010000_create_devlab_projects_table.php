<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('devlab_projects', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 120);
            $table->string('template', 30)->default('html');
            $table->string('runtime', 20)->default('browser');
            $table->string('description', 500)->nullable();
            $table->timestamp('last_opened_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'last_opened_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('devlab_projects');
    }
};
