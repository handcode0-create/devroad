<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('learning_goal', 120)->nullable()->after('password');
            $table->unsignedSmallInteger('daily_goal_minutes')->default(30)->after('learning_goal');
            $table->unsignedTinyInteger('weekly_goal_sessions')->default(3)->after('daily_goal_minutes');
            $table->string('preferred_technology', 50)->nullable()->after('weekly_goal_sessions');
            $table->boolean('email_notifications')->default(true)->after('preferred_technology');
            $table->boolean('learning_reminders')->default(true)->after('email_notifications');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'learning_goal',
                'daily_goal_minutes',
                'weekly_goal_sessions',
                'preferred_technology',
                'email_notifications',
                'learning_reminders',
            ]);
        });
    }
};
