<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Resource extends Model
{
    // todo
    protected $fillable = [
        'type', 'name', 'original_name', 'uri', 'description', 'public',
    ];  // 可以批量填充的字段

    public function getDates()
    {
        return $this->dates;
    }

    public function scopePublic(Builder $query)
    {
        $query->where('public', 1);
    }

    // 资源表和用户表之间的关系，反向多对一
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id', 'id');
    }

}
