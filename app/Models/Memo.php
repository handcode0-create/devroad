<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class Memo extends Model
{
    // user_id défini via $request->user()->memos()->create(...)
    protected $fillable = [
        'title',
        'content',
        'is_favorite',
    ];

    protected function casts(): array
    {
        return [
            'is_favorite' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // Table pivot par défaut : memo_tag (ordre alphabétique, singulier)
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

    /**
     * Remplace les tags du mémo par ceux dont on donne les NOMS.
     *
     * On ne reçoit jamais d'ID depuis le frontend : chaque nom est retrouvé
     * ou créé parmi les tags du propriétaire du mémo. Un mémo ne peut donc
     * jamais être relié au tag d'un autre utilisateur.
     *
     * Deux noms qui donnent le même slug (« Laravel » / « laravel »)
     * désignent le même tag.
     */
    public function syncTagNames(array $names): void
    {
        $ids = collect($names)
            ->map(fn ($name) => trim((string) $name))
            ->filter(fn (string $name) => Str::slug($name) !== '')
            ->unique(fn (string $name) => Str::slug($name))
            ->map(fn (string $name) => $this->user->tags()
                ->firstOrCreate(['slug' => Str::slug($name)], ['name' => $name])
                ->id);

        $this->tags()->sync($ids->all());
    }
}