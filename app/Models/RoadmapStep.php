<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class RoadmapStep extends Model
{
    public const TODO = 'todo';
    public const IN_PROGRESS = 'in_progress';
    public const COMPLETED = 'completed';
    public const BLOCKED = 'blocked';

    public const STATUSES = [
        self::TODO,
        self::IN_PROGRESS,
        self::COMPLETED,
        self::BLOCKED,
    ];

    protected $touches = ['roadmap'];

    protected $fillable = [
        'title',
        'description',
        'objective',
        'content',
        'code_example',
        'workspace_file',
        'workspace_language',
        'exercise_title',
        'exercise_description',
        'exercise_hint',
        'exercise_solution',
        'exercise_completed_at',
        'last_viewed_at',
        'estimated_minutes',
        'position',
        'status',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'exercise_completed_at' => 'datetime',
        'last_viewed_at' => 'datetime',
    ];

    public function devLabProject(): HasOne
    {
        return $this->hasOne(DevLabProject::class);
    }

    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}
