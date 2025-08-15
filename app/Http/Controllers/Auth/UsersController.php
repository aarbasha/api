<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use Laratrust\Models\Role;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UsersController extends Controller
{

    use GlobalTraits;
    // private $AuthController;

    // public function __construct(AuthController $AuthController)
    // {
    //     $this->AuthController = $AuthController;
    // }


    public function all_users()
    {

        $all_users = User::all();

        return $this->SendResponse($all_users, "success this all users", 200);
    }


    public function users()
    {
        $users = User::with('roles')->orderBy('created_at', 'desc')->paginate(8); //asc

        $usersWithAuthorities = [];

        foreach ($users as $user) {
            $userAuthorities = $user->roles->pluck('name');

            $usersWithAuthorities[$user->id] = [
                'user' => $user,
                'authorities' => $userAuthorities, // user auth naw
            ];
        }
        // $this->AuthController->UsersOnlineOffline();

        // broadcast(new UsersEvent($users->toArray()))->toOthers();

        return $this->SendResponse($users, "success this all users", 200);
    }

    public function user($id)
    {

        $user = Auth::user();
        // $sender = Auth::user()->id; // المرسل
        // $receiver = $id; // المتلقي
        // Check if a private channel already exists between the users
        // $privateChannel = ChatsChannels::whereHas('chat', function ($query) use ($sender, $receiver) {
        //     $query->where(function ($q) use ($sender, $receiver) {
        //         $q->where('sender', $sender)
        //             ->where('receiver', $receiver);
        //     })->orWhere(function ($q) use ($sender, $receiver) {
        //         $q->where('sender', $receiver)
        //             ->where('receiver', $sender);
        //     });
        // })->first();

        // if (!$privateChannel) {
        //     $privateChannel = ChatsChannels::create([
        //         'name' => 'Private-Channel-User' . $sender . "-" . 'User' . $receiver,
        //     ]);

        //     $channelId = $privateChannel->id;
        // }


        //  $channelId = $privateChannel ? $privateChannel->id : null;


        $user = User::find($id);
        if (!$user) {
            return $this->SendResponse(null, 'User not found', 404);
        }

        $roleNames = [];
        foreach ($user->roles as $role) {
            $roleNames[] = $role->name;
        }

        $permissions = [];
        foreach ($roleNames as $roleName) {
            $role = Role::where('name', $roleName)->first();
            if ($role) {
                $permissions[] = $role->permissions;
            }
        }

        // $Following = $user->Following;
        // $Followers = $user->Followers;

        // broadcast(new UserStatusEvent($user, $roleNames, $permissions, $Following->toArray(), $Followers->toArray()))->toOthers();

        // return response()->json([
        //     'user' => $user,
        //     'roles' => $roleNames,
        //     'permissions' => $permissions,
        //     //'Following' => $Following,
        //     //'Followers' => $Followers,
        //     //'channelId' =>    $channelId,
        // ]);

        return $this->SendResponse($user, "success", 200);
    }

    public function store(Request $request)
    {
        $validator = Validator($request->all(), [
            //'status' => 'required',
            "name" => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'username' => 'required|string|unique:users|min:4',
            'address' => 'string|min:4',
            // 'phone' => 'min:9',
            'password' => 'string|min:8',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->SendResponse([], $validator->errors(), 401);
        }
        $users = new User;
        if ($request->hasFile('avatar')) {
            $avatar = $request->file("avatar");
            $imageName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path("avatars/"), $imageName);
            $users->avatar = $imageName;
        }
        $users->name = $request->name;
        $users->color = $this->generateColor();
        $users->username = $request->username;
        // $users->address = $request->address;
        // $users->street = $request->street;
        // $users->city = $request->city;
        //$users->country = $request->country;
        $users->email = trim($request->email);
        if ($request->password) {
            $users->password = bcrypt($request->password);
        }
        $users->phone = $request->phone;
        $users->status = json_decode($request->status);
        $users->email_verify = json_decode($request->email_verify);

        $users->save();

        // $this->users();
        //Default Roles
        $user = Role::find(3); //user
        if (!$request->type) {
            $users->addRole($user);
        } else if ($request->type) {
            $inputString = $request->type;
            $group = json_decode($inputString);
            $roles = [];
            foreach ($group as $item) {
                $role = Role::find($item);
                if ($role) {
                    $roles[] = $role;
                }
            }
            $users->addRoles($roles);
        }
        if ($users) {
            return $this->SendResponse($users, 'Success add  User  ', 200);
        }
        return $this->SendResponse(null, 'error add  User  ', 400);
    }

    public function update(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            "name" => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100',
            'username' => 'required|string',
            'address' => 'string|min:4',
            //'phone' => 'regex:/^([0-9\s\-\+\(\)]*)$/|min:9',
            'password' => 'string|min:8',
            'status' => 'required'
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }
        $user = User::find($id);
        if ($request->hasFile("avatar")) {

            $filePath = public_path('avatars/' . $user->avatar);

            if ($user->avatar && file_exists($filePath)) {
                unlink($filePath);
                //echo 'delete image file';
            }


            $file = $request->file("avatar");
            $imageName = time() . '_' . $file->getClientOriginalName();
            $file->move(public_path("avatars/"), $imageName);
            $user->avatar = $imageName;
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->username = $request->username;
        // $user->address = $request->address;
        // $user->street = $request->street;
        // $user->city = $request->city;
        // $user->country = $request->country;
        $user->phone = $request->phone;
        $user->status = json_decode($request->status);
        $user->email_verify = json_decode($request->email_verify);
        if ($request->password) {
            $user->password = bcrypt($request->password);
        }
        $user->update();
        // $this->users();
        if ($request->type) {
            $inputString = $request->type;
            $group = json_decode($inputString);
            if (is_array($group)) {
                $roles = [];
                foreach ($group as $item) {
                    $role = Role::find($item);
                    if ($role) {
                        $roles[] = $role->id;
                    }
                }
                $user->syncRoles($roles);
            }
        }
        if ($user) {
            return $this->SendResponse($user, "success", 200);
        }
        return $this->SendResponse(null, "Error", 401);
    }

    public function destroy($id)
    {
        $user  = User::findOrFail($id);

        $filePath = public_path('avatars/' . $user->avatar);

        if ($user->avatar && file_exists($filePath)) {
            unlink($filePath);
            //echo 'delete image file';
        }


        $user->delete();

        if ($user) {
            $this->users();
            return $this->SendResponse($user, 'Success delete User  ', 200);
        }
        return $this->SendResponse(null, 'error delete  User  ', 400);
    }


    public function ToggleActive(Request $request, $id)
    {
        $user  = User::findOrFail($id);
        $user->status = $request->data;
        $user->save();

        if ($user) {
            $this->users();
            return $this->SendResponse($user, 'Success DisActive User  ', 200);
        }
        return $this->SendResponse(null, 'error DisActive  User  ', 400);
    }

    public function UsersOnlineOffline()
    {
        // show all users is online
        $timeout = now()->subMinutes(1);
        $usersOnlin = User::where('last_seen_at', '>=', $timeout)->get();
        $usersOffline = User::where('last_seen_at', '<', $timeout)->get();
        foreach ($usersOffline as $user) {
            $user->is_online = 0;
            $user->save();
            // broadcast(new UsersOnline($user))->toOthers();
        }
        return response()->json(['online' => $usersOnlin, "offline" => $usersOffline]);
    }


    public function userTypeRoles($id)
    {
        $adminUsers = User::whereHas('roles', function ($query) use ($id) {
            $query->where('id', $id);
        })->get();
        return $this->SendResponse($adminUsers, "success this users async roles", 200);
    }



    public function searchUsers($name)
    {

        $searchTerm = $name;
        // الحصول على مصطلح البحث من الطلب

        $users = User::with('roles')
            ->when($searchTerm, function ($query, $searchTerm) {
                $query->where(function ($q) use ($searchTerm) {
                    $q->where('name', 'LIKE', "%$searchTerm%")
                        ->orWhere('email', 'LIKE', "%$searchTerm%")
                        ->orWhere('username', 'LIKE', "%$searchTerm%")
                        ->orWhere('phone', 'LIKE', "%$searchTerm%")
                        ->orWhereHas('roles', function ($q) use ($searchTerm) {
                            $q->where('name', 'LIKE', "%$searchTerm%");
                        });
                });
            })
            ->orderBy('created_at', 'desc')
            ->paginate(12);




        // $usersWithAuthorities = $users->map(function ($user) {
        //     return [
        //         'user' => $user,
        //         'authorities' => $user->roles->pluck('name'),
        //     ];
        // });

        return $this->SendResponse($users, "success this all users", 200);
    }


    private function generateColor()
    {
        $colors = ['ff9d00', '00ad65', '00b8c7', '008aeb', '0060ff', '6c00ff', 'fd00ff', 'ff0020', 'ff7d6e', 'ff7724', 'ee8700', '00bad8', '000000', '254abd', 'c61480', '00baff', '6a6aba', '3cbfdc', 'ff60bb'];

        $XColor = $colors[array_rand($colors)];

        return  $XColor;
    }
}
