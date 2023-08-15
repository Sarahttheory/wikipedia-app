<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ArticleKeyword extends Model
{
    protected $fillable = ['article_id', 'keyword_id', 'count'];

    public function article()
    {
        return $this->belongsTo(Article::class);
    }

    public function keyword()
    {
        return $this->belongsTo(Keyword::class);
    }
}
