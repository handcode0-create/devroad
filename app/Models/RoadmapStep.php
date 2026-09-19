<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'exercise_title',
        'exercise_description',
        'exercise_hint',
        'exercise_solution',
        'estimated_minutes',
        'position',
        'status',
    ];

    protected $casts = [
        'estimated_minutes' => 'integer',
        'exercise_completed_at' => 'datetime',
    ];

    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}
