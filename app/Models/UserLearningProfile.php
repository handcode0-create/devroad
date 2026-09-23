<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserLearningProfile extends Model
{
    protected $fillable = [
        'user_id',
        'academic_level',
        'level',
        'level_source',
        'experience_years',
        'technologies',
        'goals',
        'assessment_scores',
        'assessment_answers',
        'completed_at',
    ];

    protected function casts(): array
    {
        return [
            'technologies' => 'array',
            'goals' => 'array',
            'assessment_scores' => 'array',
            'assessment_answers' => 'array',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
