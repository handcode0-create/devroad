<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DevLabFile extends Model
{
    use HasFactory;

    protected $fillable = ['path', 'content', 'size'];

    protected $casts = ['size' => 'integer'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(DevLabProject::class, 'devlab_project_id');
    }
}
