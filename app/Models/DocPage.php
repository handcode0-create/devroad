<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocPage extends Model
{
    protected $fillable = ['doc_source_id', 'path', 'title', 'html', 'text'];

    public function source(): BelongsTo
    {
        return $this->belongsTo(DocSource::class, 'doc_source_id');
    }
}
