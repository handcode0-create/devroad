<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Index local de documentation (DevDocs + docs officielles Laravel).
 * Le contenu des pages est téléchargé à la première lecture puis mis en cache.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('doc_sources', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();          // clé DevRoad : javascript, laravel…
            $table->string('provider');               // devdocs | laravel
            $table->string('remote_slug');            // ex. « javascript », « node~22_lts », « 12.x »
            $table->string('name');
            $table->string('version')->nullable();
            $table->text('attribution')->nullable();
            $table->string('home_url')->nullable();
            $table->unsignedInteger('entries_count')->default(0);
            $table->timestamp('synced_at')->nullable();
            $table->timestamps();
        });

        Schema::create('doc_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_source_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('search_name')->index();  // nom en minuscules, sans accents
            $table->string('type')->nullable();
            $table->string('path', 512);             // chemin de la page (sans #ancre)
            $table->string('fragment')->nullable();  // ancre dans la page
            $table->unsignedInteger('position')->default(0);
            $table->timestamps();

            $table->index(['doc_source_id', 'path']);
        });

        Schema::create('doc_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_source_id')->constrained()->cascadeOnDelete();
            $table->string('path', 512);
            $table->string('title')->nullable();
            $table->longText('html');
            $table->longText('text');                // texte brut : recherche et contexte de l'IA
            $table->timestamps();

            $table->unique(['doc_source_id', 'path']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('doc_pages');
        Schema::dropIfExists('doc_entries');
        Schema::dropIfExists('doc_sources');
    }
};
