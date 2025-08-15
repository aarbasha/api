<?php

namespace App\Http\Controllers\API;


use App\Models\Chat;
use App\Models\User;
use App\Events\DeleteChat;
use App\Events\UsersChats;
use App\Events\SendMassage;
use App\Events\TypeingUser;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use App\Models\ChatsChannels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ChatController extends Controller
{
    use GlobalTraits;

    public function getAllMessages()
    {
        $user_id = Auth::user()->id;

        $senderUsers = DB::table('chats')
            ->selectRaw('DISTINCT sender, MAX(created_at) as max_created_at')
            ->where('receiver', $user_id)
            ->groupBy('sender')
            ->get();

        $receiverUsers = DB::table('chats')
            ->selectRaw('DISTINCT receiver, MAX(created_at) as max_created_at')
            ->where('sender', $user_id)
            ->groupBy('receiver')
            ->get();

        $users = $senderUsers->merge($receiverUsers)->unique('sender')->sortByDesc('max_created_at')->pluck('sender')->toArray();

        $lastMessages = [];
        foreach ($users as $user) {
            $lastMessage = DB::table('chats')
                ->where(function ($query) use ($user, $user_id) {
                    $query->where('sender', $user)
                        ->where('receiver', $user_id);
                })
                ->orWhere(function ($query) use ($user, $user_id) {
                    $query->where('sender', $user_id)
                        ->where('receiver', $user);
                })
                ->orderByDesc('created_at')
                ->first();

            $MapUsers = User::where('id', $user)->first();
            if ($MapUsers) {
                $lastMessages[] = [
                    'user' => $MapUsers,
                    'chat' => $lastMessage,
                    'channelId' => $lastMessage->chats_channels_id
                ];
            }
        }

        // Sort the $lastMessages array by the last message date in descending order
        usort($lastMessages, function ($a, $b) {
            if ($a['chat'] && $b['chat']) {
                return $b['chat']->created_at <=> $a['chat']->created_at;
            } elseif ($a['chat']) {
                return -1;
            } elseif ($b['chat']) {
                return 1;
            } else {
                return 0;
            }
        });


        broadcast(new UsersChats($lastMessages, $user_id, null))->toOthers();
        return $this->SendResponse($lastMessages, 'جميع الرسائل ٫٫٫', 200);
    }


    public function GetNoReedMassage()
    {
        $userId = Auth::user()->id; // ID للمستخدم المصادق عليه

        // جلب جميع الرسائل غير المقروءة من وإلى المستخدم الحالي
        $messages = \App\Models\Chat::where(function ($query) use ($userId) {
            $query->where('sender', $userId)
                ->where('is_read', 0);
        })->orWhere(function ($query) use ($userId) {
            $query->where('receiver', $userId)
                ->where('is_read', 0);
        })
            ->orderBy('created_at', 'asc')
            ->get();

        return $this->SendResponse($messages, 'success, these are all unread messages', 200);
    }

    //الرسائل  مقروءه
    public function OldMessage($id)
    {
        $sender = Auth::user()->id; // The authenticated user's ID
        $receiver = $id; // The other user's ID

        $messages = \App\Models\Chat::where(function ($query) use ($sender, $receiver) {
            $query->where('sender', $sender)
                ->where('receiver', $receiver)
                ->where('is_read', 1);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender', $receiver)
                ->where('receiver', $sender)
                ->where('is_read', 1);
        })
            ->orderBy('created_at', 'asc')->get();
        return $this->SendResponse($messages, 'success this all message', 200);
    }

    //الرسائل الغير مقروءه

    public function NewMessage($id)
    {
        $sender = Auth::user()->id; // The authenticated user's ID
        $receiver = $id; // The other user's ID

        $messages = \App\Models\Chat::where(function ($query) use ($sender, $receiver) {
            $query->where('sender', $sender)
                ->where('receiver', $receiver)
                ->where('is_read', 0);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender', $receiver)
                ->where('receiver', $sender)
                ->where('is_read', 0);
        })

            ->orderBy('created_at', 'asc')->get();

        return $this->SendResponse($messages, 'success this all message', 200);
    }

    //الرسائل  مقروءه تحديث + جلب الرسائل

    public function SetReedMessages($id)
    {
        $sender = Auth::user()->id; // ID للمستخدم المصادق عليه
        $receiver = $id; // ID للمستخدم الآخر

        // تحديث جميع الرسائل غير المقروءة إلى مقروءة
        $messages =   Chat::where(function ($query) use ($sender, $receiver) {
            $query->where('sender', $sender)
                ->where('receiver', $receiver)
                ->where('is_read', 0);
        })->orWhere(function ($query) use ($sender, $receiver) {
            $query->where('sender', $receiver)
                ->where('receiver', $sender)
                ->where('is_read', 0);
        })->update(['is_read' => 1]); // تحديث إلى مقروءة

        // // // يمكنك جلب الرسائل بعد التحديث إذا كنت بحاجة إلى ذلك
        // $messages = \App\Models\Chat::where(function ($query) use ($sender, $receiver) {
        //     $query->where('sender', $sender)
        //         ->where('receiver', $receiver);
        // })->orWhere(function ($query) use ($sender, $receiver) {
        //     $query->where('sender', $receiver)
        //         ->where('receiver', $sender);
        // })->orderBy('created_at', 'asc')->get();

        return $this->SendResponse($receiver, 'success, all messages marked as read', 200);
    }

    public function DeleteChatForm($id)
    {
        $sender = Auth::user()->id; // The authenticated user's ID
        $receiver = $id; // The other user's ID
        // Delete the associated chat channel
        $chatChannel = ChatsChannels::whereHas('chat', function ($query) use ($sender, $receiver) {
            $query->where(function ($q) use ($sender, $receiver) {
                $q->where('sender', $sender)
                    ->where('receiver', $receiver);
            })->orWhere(function ($q) use ($sender, $receiver) {
                $q->where('sender', $receiver)
                    ->where('receiver', $sender);
            });
        })->first();

        broadcast(new DeleteChat($receiver));

        if ($chatChannel) {
            $chatChannel->delete();
        }

        return $this->sendResponse(null, ' deleted chat successfully', 200);
    }

    public function sendMessage(Request $request, $id)
    {
        $sender = Auth::user()->id; // المرسل
        $receiver = $id; // المتلقي
        // Check if a private channel already exists between the users
        $privateChannel = ChatsChannels::whereHas('chat', function ($query) use ($sender, $receiver) {
            $query->where(function ($q) use ($sender, $receiver) {
                $q->where('sender', $sender)
                    ->where('receiver', $receiver);
            })->orWhere(function ($q) use ($sender, $receiver) {
                $q->where('sender', $receiver)
                    ->where('receiver', $sender);
            });
        })->first();

        if ($privateChannel) {
            $channelId = $privateChannel->id;
            // استخدم $channelId هنا
        }
        if (!$privateChannel) {
            $privateChannel = ChatsChannels::create([
                'name' => 'Private-Channel-User' . $sender . "-" . 'User' . $receiver,
            ]);

            $channelId = $privateChannel->id;
        }
        $chat = $privateChannel->chat()->create([
            'sender' => $sender,
            'receiver' => json_decode($receiver),
            'message' => $request->message,
            'is_read' => false,
            'type' => "message",
        ]);

        // $table->boolean('is_mute')->default(false);
        // $table->boolean('is_blocked')->default(false);
        // You can also attach media to the chat message

        if ($request->hasFile('media')) {
            $file = $request->file('media');
            $fileType = $file->getClientMimeType();

            // جلب المسار
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'chat_media/' . $fileName;

            // التحقق من أنواع الملفات المسموحة
            $allowedTypes = ['image/jpeg', 'image/png', 'video/mp4', 'audio/mpeg', 'application/pdf'];
            if (in_array($fileType, $allowedTypes)) {
                // نقل الملف إلى المجلد المناسب
                // $file->storeAs('public', $filePath);

                $file->move(public_path("chat_media/"), $fileName);

                // تخزين معلومات الملف في قاعدة البيانات
                $chat->ChatsMedia()->create([
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                ]);
            } else {
                // إرسال رسالة خطأ إذا لم يكن نوع الملف مسموحًا
                return response()->json(['error' => 'نوع الملف غير مسموح به'], 400);
            }
        }

        // Retrieve the Chat model instance for the recipient
        $channelName = 'Channel-User' . $sender . "-" . 'User' . $receiver;
        //$receiverModel = Chat::where('receiver', $receiver)->first();

        $receiverModel = User::where('id', $sender)->first();


        $this->getAllMessages($sender);

        broadcast(new SendMassage($receiverModel, $chat, $channelName, $channelId))->toOthers();

        // event(new TypeingUserEvent($receiver, $user,   0, false, $channelId));

        // event(new NewMessage($receiverModel, $chat, $channelName, $channelId));



        if ($chat) {
            return response()->json(['msg' => "success send", "data" => $chat]);
        }
        return response()->json("Falid send");
    }



    public function sendMedia(Request $request, $id)
    {
        $sender = Auth::user()->id; // المرسل
        $receiver = $id; // المتلقي

        // Check if a private channel already exists between the users
        $privateChannel = ChatsChannels::whereHas('chat', function ($query) use ($sender, $receiver) {
            $query->where(function ($q) use ($sender, $receiver) {
                $q->where('sender', $sender)
                    ->where('receiver', $receiver);
            })->orWhere(function ($q) use ($sender, $receiver) {
                $q->where('sender', $receiver)
                    ->where('receiver', $sender);
            });
        })->first();

        if (!$privateChannel) {
            $privateChannel = ChatsChannels::create([
                'name' => 'Private-Channel-User' . $sender . "-" . 'User' . $receiver,
            ]);
        }

        $channelId = $privateChannel->id;



        if ($request->hasFile('audio')) {

            $file = $request->file('audio');
            $fileType = $file->getClientMimeType();
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = 'chat_media/' . $fileName;
            $allowedTypes = ['audio/mpeg', 'audio/wav', 'audio/mp3']; //     المسموح بها التأكد من أنواع الملفات
            $chat = $privateChannel->chat()->create([
                'sender' => $sender,
                'receiver' => json_decode($receiver),
                'message' => $filePath,
                'is_read' => false,
                'type' => "audio",
            ]);

            if (in_array($fileType, $allowedTypes)) {
                $file->move(public_path("chat_media/"), $fileName);
                $chat->ChatsMedia()->create([
                    'file_path' => $filePath,
                    'file_type' => $fileType,
                ]);
            } else {
                return response()->json(['error' => 'نوع الملف غير مسموح به'], 400);
            }
        }

        $channelName = 'Channel-User' . $sender . "-" . 'User' . $receiver;
        $receiverModel = User::where('id', $receiver)->first();
        $this->getAllMessages($sender);
        broadcast(new SendMassage($receiverModel, $chat, $channelName, $channelId))->toOthers();

        return response()->json(['msg' => "success send", "data" => $chat]);
    }

    public function updateTypingStatus(Request $request, $id)
    {

        $sender = Auth::user()->id; // المرسل
        $off_typing = false;
        $isTyping = $request->input('is_typing');
        event(new TypeingUser($sender, $isTyping, $off_typing));
        return response()->json(['success' => true, "typingLength" => $request->is_typing, "off_typing" => $off_typing]);
    }
}
