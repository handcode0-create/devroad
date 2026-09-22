<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MemoAttachment extends Model
{
    protected $fillable = ['memo_id', 'user_id', 'name', 'mime_type', 'size', 'data'];

    protected $hidden = ['data'];

    protected function casts(): array
    {
        return ['size' => 'integer'];
    }

    public function memo(): BelongsTo
    {
        return $this->belongsTo(Memo::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isImage(): bool
    {
        return str_starts_with((string) $this->mime_type, 'image/');
    }
}