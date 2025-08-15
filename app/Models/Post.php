<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'info',
        'url',
        'cover',
        'images',
        'number',
        'categorie_id',
    ];


    public function Categories()
    {
        return $this->belongsTo(Categorie::class, 'categorie_id');
    }

    public function images()
    {
        return $this->hasMany(Image::class, 'post_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->belongsToMany(Favorite::class, 'favorites_posts', 'post_id', 'favorite_id');
    }
}
