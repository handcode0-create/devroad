<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SandboxProject extends Model
{
    use HasFactory;

    public const TEMPLATES = ['react', 'nextjs', 'node', 'php', 'laravel'];

    public const STATUSES = ['stopped', 'starting', 'running', 'stopping', 'sleeping', 'error'];

    protected $fillable = [
        'name',
        'template',
        'runtime',
        'runtime_version',
        'status',
        'preview_url',
        'settings',
        'metadata',
        'last_started_at',
        'last_stopped_at',
    ];

    protected $casts = [
        'settings' => 'array',
        'metadata' => 'array',
        'last_started_at' => 'datetime',
        'last_stopped_at' => 'datetime',
    ];

    protected $hidden = [
        'metadata',
        'settings',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function instances(): HasMany
    {
        return $this->hasMany(SandboxInstance::class);
    }

    public function activeInstance(): HasMany
    {
        return $this->instances()->whereIn('status', ['starting', 'running', 'sleeping']);
    }
}
