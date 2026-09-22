<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            $table->string('icon', 32)->default('📝')->after('title');
            $table->foreignId('cover_attachment_id')->nullable()->after('folder_id')
                ->constrained('memo_attachments')->nullOnDelete();
            $table->boolean('is_full_width')->default(false)->after('cover_attachment_id');
            $table->softDeletes();
            $table->index(['user_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::table('memos', function (Blueprint $table) {
            $table->dropForeign(['cover_attachment_id']);
            $table->dropColumn(['icon', 'cover_attachment_id', 'is_full_width', 'deleted_at']);
        });
    }
};