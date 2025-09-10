<?php

namespace App\Http\Controllers\Auth;

use App\Models\User;
use App\Mail\EmailVerfiy;
use Laratrust\Models\Role;
use App\Traits\GlobalTraits;
use Illuminate\Http\Request;
use App\Mail\PasswordResetCode;
use App\Providers\AwsSnsService;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use App\Services\SnsService;

class AuthController extends Controller
{
    use GlobalTraits;
    //=================================================================================
    private $UsersController;
    protected $SnsService;

    public function __construct(UsersController $UsersController, SnsService $SnsService)
    {
        $this->UsersController = $UsersController;
        $this->SnsService = $SnsService;
    }


    ## tha function for register user
    public function register(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|between:2,100',
            'email' => 'required|string|email|max:100|unique:users',
            'username' => 'required|string|min:6|unique:users',
            'password' => 'required|string|min:8',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }

        $user = Role::find(3); //user
        $user = User::create([
            'name' => $request->name,
            'username' => $request->username,
            'email' => trim($request->email),
            'password' => bcrypt($request->password),
            'last_seen_at' => now(), // adation ....
            'color' => $this->generateColor(),
        ])->addRole($user);


        if ($user) {
            $code = $this->generateResetCode();
            $expirationTime = now()->addMinutes(5); // على سبيل المثال، انتهاء الصلاحية بعد 10 دقيقة
            $user->code_expires_at = $expirationTime;
            $user->code = $code;
            $user->save();
            try {
                Mail::to($user->email)->send(new EmailVerfiy($code, $user->email));
            } catch (\Throwable $th) {
                return response()->json("Error send email");
            }
            return $this->SendResponse([], 'Success Register User  ', 333);
        }
        return $this->SendResponse(null, 'Error Register User  ', 404);
    }
    //=================================================================================

    ## tha function for login user
    public function login(Request $request)
    {
        $validator = Validator($request->all(), [
            'password' => 'required',
            'email' => 'required|email',
        ]);
        if ($validator->fails()) {
            return $this->SendResponse([], $validator->errors(), 401);
        }
        try {
            if (Auth::attempt($request->only('email', 'password'))) {
                $user = $request->user();
                if ($user->email_verify === 1  && $user->auth_2_factory === 0) { //  can use  && auth_2_factory
                    $user->last_seen_at = now();
                    $user->is_online = 1;
                    $user->save();
                    $token = $user->createToken('ACCESS_TOKEN')->plainTextToken;
                    $cookie = cookie('ACCESS_TOKEN', $token, 60 * 24 * 7);  // 1 week
                    $roleName = Auth::user()->roles->first()->name;
                    $role = Role::where('name', $roleName)->first();
                    $permissions = $role->permissions->pluck('name');
                    $xxx = $this->SendResponse([
                        'user' => $user,
                        'role' => $roleName,
                        'permissions' => $permissions,
                        'token' => $token,
                    ], "success login ", 200);
                    $this->UsersController->users();
                    return response()->json($xxx)->withCookie($cookie);
                } else {
                    $code = $this->generateResetCode();
                    $expirationTime = now()->addMinutes(5); // على سبيل المثال، انتهاء الصلاحية بعد 10 دقيقة
                    $user->code_expires_at = $expirationTime;
                    $user->code = $code;
                    $user->save();
                    try {
                        Mail::to($user->email)->send(new EmailVerfiy($code, $user->email));
                    } catch (\Throwable $th) {
                        return response()->json("Error send email");
                    }
                    return $this->SendResponse([], 'يجب عليك تاكيد البريد الالكتروني', 333);
                }
            } else {
                return $this->SendResponse([], 'Invalid login credentials', 401);
            }
        } catch (\Exception $e) {
            return $this->SendResponse([],  $e->getMessage(), 500);
        }
    }
    //=================================================================================

    ## tha function for profile
    public function profile(Request $request)
    {

        $roleName = Auth::user()->roles->first()->name;
        $Following = Auth::user()->Following;
        $Followers = Auth::user()->Followers;
        $role = Role::where('name', $roleName)->first();
        $permissions = $role->permissions->pluck('name');


        $user = $request->user();
        $user->last_seen_at = now();
        $user->is_online = 1;
        $user->save();
        // broadcast(new setOnline($user))->toOthers();
        return $this->SendResponse([
            'user' => $user,
            'role' => $roleName,
            'permissions' => $permissions,
            'Following' => $Following,
            'Followers' => $Followers,
        ],  "success", 200);
    }
    //=================================================================================
    public function logout(Request $request)
    {
        $user = $request->user();
        $user->is_online = 0;
        if ($user->auth_2_factory === 1) {
            $user->auth_2_factory === 1;
        }
        $user->save();
        $this->UsersController->users();
        $cookie = cookie('ACCESS_TOKEN', '', 0, '/', null, false, true);
        return response()->json(['message' => 'Logged out successfully'])->withCookie($cookie);
    }

    public function RemoveProfileAvatar(Request $request)
    {
        try {
            $user = Auth::user();
            $filePath = public_path('avatars/' . $user->avatar);

            if ($user->avatar && file_exists($filePath)) {
                unlink($filePath);
                //echo 'delete image file';
            }

            $user->avatar = null;

            if ($user instanceof User) {
                $user->save();
            }
            return $this->SendResponse(["user" => $user, "roles" => $user->roles], "success", 200);
        } catch (\Exception $e) {
            Log::error($e);
            return $this->SendResponse(null, "error", 500); // استخدم 500 للأخطاء الداخلية
        }
    }

    public function updateProfile(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:855',
            'username' => 'required|string|max:855',
            'address' => 'string|max:855',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse([], $validator->errors(), 401);
        }

        $user = $request->user();

        if ($request->hasFile('avatar')) {

            $filePath = public_path('avatars/' . $user->avatar);

            if ($user->avatar && file_exists($filePath)) {
                unlink($filePath);
                //echo 'delete image file';
            }


            $avatar = $request->file("avatar");
            $imageName = time() . '_' . $avatar->getClientOriginalName();
            $avatar->move(public_path("avatars/"), $imageName);
            $user->avatar = $imageName;
        }

        $user->name = $request->name;
        $user->username = $request->username;
        $user->address = $request->address;
        $user->street = $request->street;
        $user->city = $request->city;
        $user->country = $request->country;

        // تحقق مما إذا كان البريد الإلكتروني قد تغير
        if ($request->email && $request->email !== $user->email) {
            $user->email = trim($request->email);
            $user->email_verify = false; // إعادة تعيين تأكيد البريد الإلكتروني
        }

        if ($request->password) {
            $user->password = bcrypt($request->password);
        }

        if ($request->phone && $request->phone !== $user->phone) {
            $user->phone = $request->phone;
            $user->phone_verify = false; // إعادة تعيين تأكيد الهاتف
        }

        $user->status = $request->status;
        $user->save(); // استخدم save بدلاً من update

        $this->UsersOnlineOffline();
        $this->UsersController->users();

        return $this->SendResponse(["user" => $user, "roles" => $user->roles], 'Success update profile User', 200);
    }

    public function setOnline(Request $request)
    {
        $timeout = now()->subMinutes(1);
        $user = $request->user();
        $user->last_seen_at = now();
        $user->is_online = 1;
        $user->save();
        $usersOnline = User::where('is_online', 1)->get();
        $usersOffline = User::where('is_online',  0)->get();
        // broadcast(new UsersOnline($user, $usersOnline->toArray(), $usersOffline->toArray()))->toOthers();
        // broadcast(new UserStatusEvent($user, null, [],  [],  []))->toOthers();
        return response()->json($user);
    }

    public function UsersOnlineOffline()
    {
        $user = Auth::user();

        // show all users is online
        $usersOnline = User::where('is_online', 1)->get();
        $usersOffline = User::where('is_online',  0)->get();
        //  broadcast(new UsersOnline($user, $usersOnline->toArray(), $usersOffline->toArray()))->toOthers();
        // foreach ($usersOffline as $user) {
        //     $user->is_online = 0;
        //     $user->save();
        // }



        // broadcast(new UserStatusEvent($user, null, [],  [], []))->toOthers();
        return response()->json(['online' => $usersOnline, 'offline' => $usersOffline]);
    }

    public function logoutAnyUser($id)
    {
        $user = Auth::logoutOtherDevices($id);

        if ($user) {
            return $this->SendResponse($user, 'success logout user in sidout system   ', 200);
        }
        return $this->SendResponse(null, 'error logout user in sidout system   ', 400);
    }

    public function UsersCount()
    {
        $count = user::count();
        return $count;
    }

    public function RefreshToken(Request $request)
    {

        return  $request->user()->currentAccessToken()->plainTextToken;


        // $cookieName = 'sanctum';
        // $currentToken = $request->cookie($cookieName);
        // if (!$currentToken) {
        //     return response()->json(['message' => 'Token cookie not found'], 401);
        // }
        // $user = Auth::user();
        // if (!$user) {
        //     return response()->json(['message' => 'User not authenticated'], 401);
        // }
        // $newToken = $user->createToken('sanctum')->plainTextToken;
        // $expiresAt = now()->addMinutes(config('sanctum.expiration'));

        // return response()->json(['token' => $newToken, 'expires_at' => $expiresAt])
        //     ->withCookie(cookie($cookieName, $newToken, config('sanctum.expiration')));
    }

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('email', $request->email)->first();
        if (!$user) {
            return response()->json([
                'message' => 'This email is not registered',
                "status" => 404
            ], 404);
        }
        $code = $this->generateResetCode();
        $expirationTime = now()->addMinutes(5); // على سبيل المثال، انتهاء الصلاحية بعد 5 دقيقة
        $user->code_expires_at = $expirationTime;
        $user->code = $code;
        $user->save();

        // Send email with reset code
        $pathLogo = storage_path("/app/public/images/logo.png");

        try {
            Mail::to($user->email)->send(new PasswordResetCode($code, $user->email, $pathLogo));
        } catch (\Throwable $th) {
            //throw $th;
            return $th;
        }


        return response()->json([
            // "code" => $code,
            'message' => 'Reset password code sent to your email',
            "status" => 200
        ], 200);
    }


    public function UpdatePasswordCurrent(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:8',
        ]);
        // تحقق من كلمة المرور القديمة
        $user = Auth::user();
        if (!Hash::check($request->current_password, $user->password)) {
            return $this->SendResponse(null, "كلمة المرور القديمة غير صحيحة.", 401);
        }
        if ($user) {
            $user->password = Hash::make($request->new_password);

            if ($user instanceof User) {
                $user->save();
            }
        }

        return $this->SendResponse(null, "تم تحديث كلمة المرور بنجاح!", 200);
    }

    public function UpdatePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'string|min:8',
        ]);

        if ($validator->fails()) {
            return $this->SendResponse(null, $validator->errors(), 401);
        }
        $user = Auth::user();
        if ($request->password === $request->re_password) {
            $user->password = hash::make($request->password);
            $user->code = null;
            $user->code_expires_at = null;

            if ($user instanceof User) {
                $user->save();
            }
        }
        if ($user) {
            return response()->json(["message" => "success update password", "status" => 200]);
        } else {
            return response()->json(["message" => "Error update password", "status" => 401]);
        }
    }


    public function Active2faAuth(Request $request)
    {
        $user = Auth::user();
        if ($request->active ===  true) {
            $code = $this->generateResetCode();
            $expirationTime = now()->addMinutes(3); // على سبيل المثال، انتهاء الصلاحية بعد 10 دقيقة
            $user->code_expires_at = $expirationTime;
            $user->code = $code;
            if ($user instanceof User) {
                $user->save();
            }
            try {
                Mail::to($user->email)->send(new EmailVerfiy($code, $user->email));
            } catch (\Throwable $th) {
                return response()->json("Error send email");
            }
            return $this->SendResponse(null, "send code with your email for active 2 factory", 200);
        } else {
            $user->auth_2_factory = false;
            if ($user instanceof User) {
                $user->save();
            }
            return $this->SendResponse($user, "success stop 2fa", 200);
        }
    }

    public function verifyCodeFirst(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('email', $request->email)
            ->where('code', $request->code)
            ->where('code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return $this->SendResponse(null, "Invalid code or code has expired ", 400);
        }
        $user = User::where("email", $request->email)->get()->first();
        $user->auth_2_factory = true;
        $user->code = null;
        $user->code_expires_at = null;
        $user->save();

        if ($user) {
            return $this->SendResponse($user, "success active 2fa ", 200);
        }
        return $this->SendResponse(null, "error active 2fa ", 401);
    }

    // for all :  reset password - 2fa - email vervif
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('email', $request->email)
            ->where('code', $request->code)
            ->where('code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return response()->json([
                'message' => 'Invalid code or code has expired'
            ], 400);
        }
        $user = User::where("email", $request->email)->get()->first();
        $roleName = $user->roles->first()->name;
        $role = Role::where('name', $roleName)->first();
        $permissions = $role->permissions->pluck('name');
        $token = $user->createToken('ACCESS_TOKEN')->plainTextToken;
        $cookie = cookie('ACCESS_TOKEN', $token, 60 * 24 * 7);  // 1 week
        if ($request->active) {
            $user->auth_2_factory = 1;
        }

        if ($user->email_verify !== 1) {
            $user->email_verify = 1;
            $user->email_verified_at = now();
        }
        $user->code = null;
        $user->code_expires_at = null;
        $user->save();

        return response()->json([
            'message' => 'Code verified',
            'success' => true,
            "user" => $user,
            'role' => $roleName,
            'permissions' => $permissions,
            'token' => $token,
            "status" => 200
        ], 200)->withCookie($cookie);
    }

    // create code 6 for all :  reset password - 2fa - email vervif
    private function generateResetCode()
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }


    private function generateColor()
    {
        $colors = ['ff9d00', '00ad65', '00b8c7', '008aeb', '0060ff', '6c00ff', 'fd00ff', 'ff0020', 'ff7d6e', 'ff7724', 'ee8700', '00bad8', '000000', '254abd', 'c61480', '00baff', '6a6aba', '3cbfdc', 'ff60bb'];

        $XColor = $colors[array_rand($colors)];

        return  $XColor;
    }

    public function test()
    {
        $snsService = app(AwsSnsService::class);
        $smsResult = $snsService->sendSms('+4917620792218', 'Hello from AWS SNS!');

        if ($smsResult) {
            return "  // تم إرسال الرسالة بنجاح";
        } else {
            return "    // فشل في إرسال الرسالة";
        }
    }

    public function sendVerificationCode(Request $request)
    {
        $request->validate([
            'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/', // تحقق من صحة رقم الهاتف
        ]);
        try {
            $code = $this->generateResetCode();
            $message = "Your verification code is: $code";
            $user = User::where('phone', $request->phone)->first();
            $expirationTime = now()->addMinutes(5); // على سبيل المثال، انتهاء الصلاحية بعد 5 دقيقة
            $user->code_expires_at = $expirationTime;
            $user->code = $code;
            $user->save();
            $this->SnsService->sendSms($request->phone, $message);
            return $this->SendResponse($user, "Verification code sent! ", 200);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->SendResponse(null, "Verification code Feild sent! ", 401);
        }
    }

    public function verifyCodePhone(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|regex:/^\+?[1-9]\d{1,14}$/', // تحقق من صحة رقم الهاتف
            'code' => 'required|digits:6',
        ]);
        if ($validator->fails()) {
            return response()->json([
                'errors' => $validator->errors()
            ], 422);
        }
        $user = User::where('phone', $request->phone)
            ->where('code', $request->code)
            ->where('code_expires_at', '>', now())
            ->first();

        if (!$user) {
            return $this->SendResponse(null, "Invalid code or code has expired ", 400);
        }
        $user = User::where("phone", $request->phone)->get()->first();
        $user->code = null;
        $user->code_expires_at = null;
        $user->phone_verify = true;
        $user->save();

        if ($user) {
            return $this->SendResponse($user, "success verifycation your phone  ", 200);
        }
        return $this->SendResponse(null, "error verifycation ", 401);
    }
}
