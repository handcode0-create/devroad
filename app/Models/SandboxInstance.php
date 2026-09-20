<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SandboxInstance extends Model
{
    use HasFactory;

    protected $fillable = [
        'driver',
        'provider_instance_id',
        'status',
        'region',
        'cpu_millicores',
        'memory_mb',
        'storage_mb',
        'started_at',
        'stopped_at',
        'last_heartbeat_at',
        'metadata',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'last_heartbeat_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $hidden = ['metadata'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(SandboxProject::class, 'sandbox_project_id');
    }

    public function processes(): HasMany
    {
        return $this->hasMany(SandboxProcess::class);
    }
}
