<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class DocEntry extends Model
{
    protected $fillable = ['doc_source_id', 'name', 'search_name', 'type', 'path', 'fragment', 'position'];

    public function source(): BelongsTo
    {
        return $this->belongsTo(DocSource::class, 'doc_source_id');
    }

    /** Forme normalisée pour la recherche : minuscules, sans accents. */
    public static function normalize(string $value): string
    {
        return Str::of($value)->ascii()->lower()->squish()->toString();
    }
}
