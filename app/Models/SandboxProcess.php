<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SandboxProcess extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'command',
        'port',
        'status',
        'provider_process_id',
        'exit_code',
        'started_at',
        'stopped_at',
        'metadata',
    ];

    protected $casts = [
        'port' => 'integer',
        'exit_code' => 'integer',
        'started_at' => 'datetime',
        'stopped_at' => 'datetime',
        'metadata' => 'array',
    ];

    protected $hidden = ['metadata'];

    public function instance(): BelongsTo
    {
        return $this->belongsTo(SandboxInstance::class, 'sandbox_instance_id');
    }
}
