<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DevLabProject extends Model
{
    use HasFactory;

    public const TEMPLATES = ['html', 'node', 'php', 'laravel'];
    public const RUNTIMES = ['browser', 'server'];

    protected $table = 'devlab_projects';

    protected $fillable = ['name', 'template', 'runtime', 'description', 'last_opened_at', 'roadmap_step_id'];

    protected $casts = ['last_opened_at' => 'datetime'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roadmapStep(): BelongsTo
    {
        return $this->belongsTo(RoadmapStep::class);
    }

    public function files(): HasMany
    {
        return $this->hasMany(DevLabFile::class, 'devlab_project_id')->orderBy('path');
    }
}
