<?php

use IlluminateDatabaseMigrationsMigration;
use IlluminateDatabaseSchemaBlueprint;
use IlluminateSupportFacadesSchema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sandbox_instances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sandbox_project_id')->constrained('sandbox_projects')->cascadeOnDelete();
            $table->string('driver', 40);
            $table->string('provider_instance_id', 255)->nullable();
            $table->string('status', 20)->default('stopped');
            $table->string('region', 80)->nullable();
            $table->unsignedInteger('cpu_millicores')->default(500);
            $table->unsignedInteger('memory_mb')->default(1024);
            $table->unsignedInteger('storage_mb')->default(2048);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('stopped_at')->nullable();
            $table->timestamp('last_heartbeat_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['sandbox_project_id', 'status']);
            $table->index('provider_instance_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sandbox_instances');
    }
};
