<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keyword extends Model
{
    protected $fillable = ['word'];

    public function articles()
    {
        return $this->hasMany(ArticleKeyword::class);
    }
}
