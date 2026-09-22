<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            $table->foreignId('folder_id')->nullable()->after('formatting')->constrained('memo_folders')->nullOnDelete();
            $table->index(['user_id', 'folder_id']);
        });
    }

    public function down(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('folder_id');
        });
    }
};