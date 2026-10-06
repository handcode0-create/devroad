<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocPage extends Model
{
    protected $fillable = ['doc_source_id', 'path', 'locale', 'title', 'html', 'text', 'missing', 'machine_translated'];

    protected function casts(): array
    {
        return ['missing' => 'boolean', 'machine_translated' => 'boolean'];
    }

    public function source(): BelongsTo
    {
        return $this->belongsTo(DocSource::class, 'doc_source_id');
    }

    /** Morceaux traduits par l'IA (pages anglaises uniquement). */
    public function translations(): HasMany
    {
        return $this->hasMany(DocTranslation::class);
    }
}
