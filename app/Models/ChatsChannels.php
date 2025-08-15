<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ChatsChannels extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
    ];

    public function chat(): HasMany
    {
        return $this->hasMany(Chat::class);
    }


}
