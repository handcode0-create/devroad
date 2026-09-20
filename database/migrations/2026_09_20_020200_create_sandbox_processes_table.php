<?php

use IlluminateDatabaseMigrationsMigration;
use IlluminateDatabaseSchemaBlueprint;
use IlluminateSupportFacadesSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sandbox_processes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sandbox_instance_id')->constrained('sandbox_instances')->cascadeOnDelete();
            $table->string('name', 80);
            $table->text('command');
            $table->unsignedSmallInteger('port')->nullable();
            $table->string('status', 20)->default('stopped');
            $table->string('provider_process_id', 255)->nullable();
            $table->integer('exit_code')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['sandbox_instance_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sandbox_processes');
    }
};
