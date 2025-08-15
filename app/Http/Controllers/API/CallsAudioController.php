<?php

namespace App\Http\Controllers\API;

use App\Events\CallAudioEvent;
use App\Models\User;
use App\Models\AudioCall;

use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CallAudio;
use Illuminate\Support\Facades\Auth;

class CallsAudioController extends Controller
{

    use GlobalTraits;

    public function Calls()
    {
        $calls = CallAudio::with(['caller', 'receiver'])->get();
        return $this->SendResponse($calls, "success all calls audio ", 200);
    }

    public function StartCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);
        $callerId = Auth::id();
        // إنشاء المكالمة في قاعدة البيانات
        $videoCall = CallAudio::create([
            'caller_id' => $callerId,
            'receiver_id' => $request->receiver_id,
            'started_at' => now(),
            // يمكنك إضافة المزيد من الحقول هنا إذا لزم الأمر
        ]);
        // بث الحدث
        broadcast(new CallAudioEvent([
            'call_id' => $videoCall->id,
            'caller_id' => $callerId,
            'receiver_id' => $request->receiver_id,
            "status" => "start"
        ]));
        return $this->SendResponse(['call_id' => $videoCall->id], 'Call start', 200);
    }

    public function EndCall(Request $request)
    {
        // تحقق من وجود المكالمة
        $videoCall = CallAudio::findOrFail($request->id);
        // تعيين وقت انتهاء المكالمة
        $videoCall->ended_at = now();
        // حساب مدة المكالمة وتحديث الحقل
        $videoCall->duration = $videoCall->calculateDuration();
        $videoCall->save();
        // بث الحدث عند انتهاء المكالمة
        broadcast(new CallAudioEvent([
            'call_id' => $videoCall->id,
            'caller_id' => $videoCall->caller_id,
            'receiver_id' => $videoCall->receiver_id,
            "status" => "end"
        ]));
        return $this->SendResponse(['duration' => $videoCall->duration], 'Call ended', 200);
    }
}
