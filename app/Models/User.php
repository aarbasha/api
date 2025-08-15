<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Models\Block;
use Laravel\Sanctum\HasApiTokens;
use Laratrust\Contracts\LaratrustUser;
use Illuminate\Notifications\Notifiable;
use Laratrust\Traits\HasRolesAndPermissions;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable implements LaratrustUser
{
    use HasApiTokens, HasFactory, Notifiable, HasRolesAndPermissions;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'avatar',
        'address',
        'street',
        'city',
        'country',
        'status',
        'phone',
        'is_online',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function categories()
    {
        return $this->belongsToMany(Categorie::class, 'categories_users', 'categorie_id', 'user_id');
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }

    public function favorites()
    {
        return $this->hasOne(Favorite::class);
    }

    public function blocker()
    {
        return $this->hasMany(Block::class, 'blocker_id');
    }

    public function blocked()
    {
        return $this->hasMany(Block::class, 'blocked_id');
    }


    // العلاقة مع المكالمات التي قام بها المستخدم
    public function callsMadeVideo()
    {
        return $this->hasMany(CallVideo::class, 'caller_id');
    }

    // العلاقة مع المكالمات التي تلقاها المستخدم
    public function callsReceivedVideo()
    {
        return $this->hasMany(CallVideo::class, 'receiver_id');
    }


     // العلاقة مع المكالمات التي قام بها المستخدم
     public function callsMadeAudio()
     {
         return $this->hasMany(CallAudio::class, 'caller_id');
     }

     // العلاقة مع المكالمات التي تلقاها المستخدم
     public function callsReceivedAudio()
     {
         return $this->hasMany(CallAudio::class, 'receiver_id');
     }


}
