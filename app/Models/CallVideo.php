<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CallVideo extends Model
{
    use HasFactory;



    protected $fillable = ['caller_id', 'receiver_id', 'started_at', 'ended_at', 'duration'];

    // يمكنك إضافة دالة لحساب المدة
    public function calculateDuration()
    {
        if ($this->started_at && $this->ended_at) {
            return $this->ended_at->diffInSeconds($this->started_at);
        }
        return null; // إذا لم تكن هناك مدة متاحة
    }

    // العلاقة مع المستخدم المتصل (Caller)
    public function caller()
    {
        return $this->belongsTo(User::class, 'caller_id');
    }

    // العلاقة مع المستخدم المستقبل (Receiver)
    public function receiver()
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }
}
