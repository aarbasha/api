<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Chat extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender', // المرسل
        'receiver', // المتلقي
        'message', // الرساله
        'chats_channels_id', // معرف القناه
        'is_read', // حاله القراءه
        'is_typing', // جاري الكتابه ٫٫٫
        'type'
    ];

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender');
    }

    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver');
    }

    public function ChatsChannels(): BelongsTo
    {
        return $this->belongsTo(ChatsChannels::class);
    }

    public function ChatsMedia(): HasMany
    {
        return $this->hasMany(ChatsMedia::class);
    }
}
