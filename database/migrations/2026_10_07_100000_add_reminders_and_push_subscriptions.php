<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('reminder_time', 5)->default('18:00')->after('learning_reminders');
            $table->json('reminder_days')->nullable()->after('reminder_time');
            $table->date('last_reminder_on')->nullable()->after('reminder_days');
        });

        Schema::create('push_subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('endpoint_hash', 64)->unique();
            $table->text('endpoint');
            $table->string('public_key');
            $table->string('auth_token');
            $table->string('content_encoding', 20)->default('aesgcm');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('push_subscriptions');
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['reminder_time', 'reminder_days', 'last_reminder_on']);
        });
    }
};
