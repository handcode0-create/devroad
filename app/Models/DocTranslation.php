<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DocTranslation extends Model
{
    protected $fillable = ['doc_page_id', 'chunk', 'html'];
}
