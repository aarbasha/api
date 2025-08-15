<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Events\ToogleBlocke;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use App\Models\ChatsChannels;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class BlockController extends Controller
{
    use GlobalTraits;

    public function AddBlock($id)
    {
        $user = Auth::user();
        $blockedUser = User::findOrFail($id);
        if ($user->blocker()->where('blocked_id', $id)->exists()) {
            return $this->SendResponse(null, 'User is already blocked.', 400);
        } else {
            $user->blocker()->create([
                'blocked_id' => $id,
            ]);
            broadcast(new ToogleBlocke(json_decode($id), $blockedUser, "BLOCKE"));
            return $this->SendResponse($blockedUser, 'User has been blocked successfully.', 200);
        }
    }

    public function unBlock($id)
    {
        $user = Auth::user();
        $blockedUser = User::findOrFail($id);
        if ($user->blocker()->where('blocked_id', $id)->exists()) {
            $user->blocker()->where('blocked_id', $id)->first()->delete();
            broadcast(new ToogleBlocke(json_decode($id), $blockedUser, "unBLOCKE"));
            return $this->SendResponse($blockedUser, 'User has been unblocked successfully.', 200);
        } else {
            return $this->SendResponse(null, 'User is not blocked.', 200);
        }
    }

    public function usersMyBlock()
    {

        $blockedUsers = Auth::user()->blocker()->orderByDesc('created_at')->pluck('blocked_id');

        $user = User::whereIn('id', $blockedUsers)->get();


        return $this->SendResponse($user, 'المستخدمين المحظورين', 200);
    }

    public function usersBlockToMy()
    {

        $blockedUsers = Auth::user()->blocked()->orderByDesc('created_at')->pluck('blocker_id');

        $user = User::whereIn('id', $blockedUsers)->get();


        return $this->SendResponse($user, 'المستخدمين اللذين قامو بحظري', 200);
    }

    public function Blocked_blocker()
    {
        $user_id = Auth::user()->id;
        $blocked_Users = DB::table('blocks') // المانع
            ->selectRaw('DISTINCT blocker_id, MAX(created_at) as max_created_at')
            ->where('blocked_id', $user_id)
            ->groupBy('blocker_id')
            ->get();

        $blocker_Users = DB::table('blocks') // الممنوع
            ->selectRaw('DISTINCT blocked_id, MAX(created_at) as max_created_at')
            ->where('blocker_id', $user_id)
            ->groupBy('blocked_id')
            ->get();
        $users = $blocked_Users->merge($blocker_Users)->unique('blocked_id')->sortByDesc('max_created_at')->pluck('blocked_id')->toArray();
        // Remove null values from the $users array
        $users = array_values(array_filter($users));


        $blocker = $blocked_Users->pluck('blocker_id')->toArray(); // الحاظرين
        $blocked = $blocker_Users->pluck('blocked_id')->toArray(); // المحظورين
        $data = [
            "user_me_block" => $blocker,
            "user_i_block" => $blocked,
        ];

        //event(new Blocked_Blocker_Users($data));
        return $this->SendResponse($data, 'tha user is blocked', 200);
    }
}
