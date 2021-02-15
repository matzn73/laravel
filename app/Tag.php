<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    protected $fillable = [
        'name',
    ];

    public function getHashtagAttribute(): string //アクセサ
    {
        return '#' . $this->name;
    }

    public function articles(): BelongsToMany //タグと記事は多対多
    {
        return $this->belongsToMany('App\Article')->withTimestamps();
    }
}
