<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->json('workspace_files')->nullable()->after('workspace_language');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->dropColumn('workspace_files');
        });
    }
};
