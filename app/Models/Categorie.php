<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Categorie extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'info', 'cover', 'auther', "path", "icon"];

    public function parent()
    {
        return $this->belongsTo(Categorie::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Categorie::class, 'parent_id');
    }

    public function posts()
    {
        return $this->hasMany(Post::class, 'categorie_id');
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'categories_users', 'categorie_id', 'user_id');
    }
}
