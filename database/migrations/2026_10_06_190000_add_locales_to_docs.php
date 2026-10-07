<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Documentation en français d'abord : une page existe par langue, la version
 * française peut être officielle (MDN, React, PHP) ou traduite par l'IA.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('doc_pages', function (Blueprint $table) {
            $table->string('locale', 5)->default('en');
            // Aucune traduction officielle : on ne la redemande pas à chaque visite.
            $table->boolean('missing')->default(false);
            $table->boolean('machine_translated')->default(false);
            // Créé avant de retirer l'ancien : MySQL exige un index pour la clé
            // étrangère doc_source_id, le nouveau (même préfixe) prend le relais.
            $table->unique(['doc_source_id', 'path', 'locale']);
        });

        Schema::table('doc_pages', function (Blueprint $table) {
            $table->dropUnique(['doc_source_id', 'path']);
        });

        Schema::create('doc_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('doc_page_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('chunk');
            $table->longText('html');
            $table->timestamps();

            $table->unique(['doc_page_id', 'chunk']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->string('docs_locale', 5)->default('fr');
        });
    }

    public function down(): void
    {
        Schema::table('users', fn (Blueprint $table) => $table->dropColumn('docs_locale'));
        Schema::dropIfExists('doc_translations');
        Schema::table('doc_pages', fn (Blueprint $table) => $table->unique(['doc_source_id', 'path']));
        Schema::table('doc_pages', function (Blueprint $table) {
            $table->dropUnique(['doc_source_id', 'path', 'locale']);
            $table->dropColumn(['locale', 'missing', 'machine_translated']);
        });
    }
};
