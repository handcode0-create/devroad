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

    // Toute modification d'une étape met à jour updated_at de sa roadmap :
    // « les roadmaps récentes » du dashboard reflètent ainsi l'activité réelle.
    protected $touches = ['roadmap'];

    // roadmap_id est défini via $roadmap->steps()->create(...)
    protected $fillable = [
        'title',
        'description',
        'position',
        'status',
    ];

    public function roadmap(): BelongsTo
    {
        return $this->belongsTo(Roadmap::class);
    }
}   