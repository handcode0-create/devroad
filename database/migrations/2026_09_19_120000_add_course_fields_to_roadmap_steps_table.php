<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->text('objective')->nullable()->after('description');
            $table->longText('content')->nullable()->after('objective');
            $table->longText('code_example')->nullable()->after('content');
            $table->unsignedSmallInteger('estimated_minutes')->nullable()->after('code_example');
        });
    }

    public function down(): void
    {
        Schema::table('roadmap_steps', function (Blueprint $table) {
            $table->dropColumn([
                'objective',
                'content',
                'code_example',
                'estimated_minutes',
            ]);
        });
    }
};