<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Memo extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'title',
        'content',
        'formatting',
        'is_favorite',
        'folder_id',
        'icon',
        'cover_attachment_id',
        'is_full_width',
    ];

    protected function casts(): array
    {
        return [
            'formatting' => 'array',
            'is_favorite' => 'boolean',
            'is_full_width' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function folder(): BelongsTo
    {
        return $this->belongsTo(MemoFolder::class, 'folder_id');
    }

    public function coverAttachment(): BelongsTo
    {
        return $this->belongsTo(MemoAttachment::class, 'cover_attachment_id');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MemoAttachment::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class);
    }

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
