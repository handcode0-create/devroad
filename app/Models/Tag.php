<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    // user_id défini via $user->tags()->firstOrCreate(...)
    protected $fillable = [
        'name',
        'slug',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function memos(): BelongsToMany
    {
        return $this->belongsToMany(Memo::class);
    }
}