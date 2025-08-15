<?php

namespace App\Http\Controllers\API;

use App\Events\CallVideoEvent;
use App\Models\User;

use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\CallVideo;
use Illuminate\Support\Facades\Auth;

class CallsVideoController extends Controller
{

    use GlobalTraits;

    public function Calls()
    {



        $calls = CallVideo::with(['caller', 'receiver'])->get();
        return $this->SendResponse($calls, "success all calls video ", 200);
    }

    public function StartCall(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
        ]);
        $callerId = Auth::id();
        // إنشاء المكالمة في قاعدة البيانات
        $videoCall = CallVideo::create([
            'caller_id' => $callerId,
            'receiver_id' => $request->receiver_id,
            'started_at' => now(),
            // يمكنك إضافة المزيد من الحقول هنا إذا لزم الأمر
        ]);
        // بث الحدث
        broadcast(new CallVideoEvent([
            'call_id' => $videoCall->id,
            'caller_id' => $callerId,
            'receiver_id' => $request->receiver_id,
            "status" => "start"
        ]));
        return response()->json(['message' => 'Call started', 'call_id' => $videoCall->id], 200);
    }

    public function EndCall(Request $request)
    {
        // تحقق من وجود المكالمة
        $videoCall = CallVideo::findOrFail($request->id);
        // تعيين وقت انتهاء المكالمة
        $videoCall->ended_at = now();
        // حساب مدة المكالمة وتحديث الحقل
        $videoCall->duration = $videoCall->calculateDuration();
        $videoCall->save();
        // بث الحدث عند انتهاء المكالمة
        event(new CallVideoEvent([
            'call_id' => $videoCall->id,
            'caller_id' => $videoCall->caller_id,
            'receiver_id' => $videoCall->receiver_id,
            "status" => "end"
        ]));

        return response()->json(['message' => 'Call ended', 'duration' => $videoCall->duration], 200);
    }
}
