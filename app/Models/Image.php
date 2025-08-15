<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Image extends Model
{
    use HasFactory;

    protected $fillable = ['url', 'post_id'];

    //protected $with = ['posts'];

    public function posts()
    {
        return $this->belongsTo(Post::class, 'post_id');
    }
}
