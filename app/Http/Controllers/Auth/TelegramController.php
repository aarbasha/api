<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Telegram\Bot\Api;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Telegram\Bot\Traits\Validator;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{




    //https://api.telegram.org/bot7645043626:AAG6D87H14LeSgbbdlmaF0G1sdIQU1SKzy0/setWebhook?url=http://localhost:8000/api/telegram/getUpdates


    // https://0a1848bf8e5e.ngrok-free.app

    //https://661fc19628ac.ngrok-free.app/

    //https://api.telegram.org/bot7645043626:AAG6D87H14LeSgbbdlmaF0G1sdIQU1SKzy0/setWebhook?url=https://661fc19628ac.ngrok-free.app/api/telegram/getUpdates
    public function handleMessage(Request $request)
    {
        $update = $request->all(); // استلام كل البيانات


       // return "hello telegram";
        // الحصول على معرف المستخدمz
        $userId = $update['message']['from']['id'];
        $username = $update['message']['from']['username'];

        // تحقق مما إذا كان المستخدم موجودًا في قاعدة البيانات
        $user = User::where('telegram_id', $userId)->first();

        if (!$user) {
            // إذا لم يكن موجودًا، أضفه إلى قاعدة البيانات
            $user = User::create([
                'name' => $username, // يمكنك تخصيص الاسم كما تحتاج
                'telegram_id' => $userId,
                // أضف حقول أخرى حسب الحاجة
            ]);
        }

        return response('User stored successfully');
    }



    //دالة لإرسال رسالة تسجيل الدخول
    public function sendLoginRequest($userId)
    {
        $url = route('telegram.login.callback', ['userId' => $userId]);
        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => '✅ Accepted', 'url' => $url . '?action=accept'],
                    ['text' => '❌ Rejected', 'url' => $url . '?action=reject']
                ]
            ]
        ];

        Telegram::sendMessage([
            'chat_id' => $userId,
            'text' => "مرحبًا، تلقينا طلبًا لتسجيل الدخول. يرجى تأكيد الطلب.",
            'reply_markup' => json_encode($keyboard)
        ]);
    }



    //دالة لمعالجة رد المستخدم
    public function loginCallback(Request $request)
    {
        $action = $request->query('action');
        $userId = $request->query('userId');

        if ($action === 'accept') {
            // قم بتسجيل دخول المستخدم
            Auth::loginUsingId($userId);
            return response('تم تسجيل الدخول بنجاح');
        } elseif ($action === 'reject') {
            return response('تم رفض الطلب');
        }
    }



    public function handleContact(Request $request)
    {
        $contact = $request->input('contact');

        if (isset($contact['phone_number'])) {
            $phoneNumber = $contact['phone_number'];
            $userId = $request->input('user_id');

            // تحقق من صحة الرقم
            $validator = Validator::make(['phone_number' => $phoneNumber], [
                'phone_number' => 'required|phone:AUTO',
            ]);

            if ($validator->fails()) {
                return response('رقم الهاتف غير صحيح. يرجى المحاولة مرة أخرى.');
            }

            // ربط الرقم مع حساب المستخدم
            $user = User::find($userId);
            $user->phone_number = $phoneNumber;
            $user->save();

            return response('تم ربط رقم الهاتف بنجاح!');
        }

        return response('فشل في استلام رقم الهاتف.');
    }
}
