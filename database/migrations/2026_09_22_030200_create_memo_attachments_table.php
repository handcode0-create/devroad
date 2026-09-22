<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('memo_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('memo_id')->constrained('memos')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('name', 255);
            $table->string('mime_type', 120);
            $table->unsignedBigInteger('size');
            $table->binary('data');
            $table->timestamps();
            $table->index(['memo_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('memo_attachments');
    }
};