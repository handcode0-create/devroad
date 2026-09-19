<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Roadmap extends Model
{
    public const STATUSES = ['draft', 'active', 'completed', 'archived'];

    // user_id volontairement absent : le propriétaire est défini par
    // $request->user()->roadmaps()->create(...), jamais par le frontend.
    protected $fillable = [
        'title',
        'technology',
        'description',
        'status',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function steps(): HasMany
    {
        return $this->hasMany(RoadmapStep::class)->orderBy('position');
    }

    /**
     * Ajoute steps_count et completed_steps_count en une seule requête SQL.
     * Usage : $user->roadmaps()->withProgress()->paginate(10)
     */
    public function scopeWithProgress(Builder $query): Builder
    {
        return $query->withCount([
            'steps',
            'steps as completed_steps_count' => fn ($q) => $q->where('status', RoadmapStep::COMPLETED),
        ]);
    }

    /**
     * progression = (terminées / total) × 100, et 0 si total = 0.
     * Utilise les compteurs de withProgress() s'ils sont chargés,
     * sinon fait deux requêtes COUNT.
     */
    protected function progress(): Attribute
    {
        return Attribute::get(function (): int {
            $total = $this->steps_count ?? $this->steps()->count();

            if ($total === 0) {
                return 0;
            }

            $completed = $this->completed_steps_count
                ?? $this->steps()->where('status', RoadmapStep::COMPLETED)->count();

            return (int) round($completed / $total * 100);
        });
    }
}