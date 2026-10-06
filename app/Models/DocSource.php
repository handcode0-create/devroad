<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DocSource extends Model
{
    protected $fillable = ['key', 'provider', 'remote_slug', 'name', 'version', 'attribution', 'home_url', 'entries_count', 'synced_at'];

    protected function casts(): array
    {
        return ['synced_at' => 'datetime', 'entries_count' => 'integer'];
    }

    public function entries(): HasMany
    {
        return $this->hasMany(DocEntry::class);
    }

    public function pages(): HasMany
    {
        return $this->hasMany(DocPage::class);
    }
}
