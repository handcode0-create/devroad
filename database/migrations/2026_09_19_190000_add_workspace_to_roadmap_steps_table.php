<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->string('workspace_file', 180)->nullable()->after('code_example');
            $table->string('workspace_language', 30)->nullable()->after('workspace_file');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->dropColumn(['workspace_file', 'workspace_language']);
        });
    }
};
