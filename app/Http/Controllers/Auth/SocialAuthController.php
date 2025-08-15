<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirectToProvider($provider)
    {
        return Socialite::driver($provider)->redirect();
    }

    public function handleProviderCallback($provider)
    {
        try {
            $socialUser = Socialite::driver($provider)->user();
        } catch (\Exception $e) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // البحث عن المستخدم أو إنشائه
        $user = User::where('email', $socialUser->getEmail())->first();

        if (!$user) {
            $user = User::create([
                'name' => $socialUser->getName(),
                'username' => $socialUser->getName(),
                'email' => $socialUser->getEmail(),
                'avatar' => $socialUser->getAvatar(),
                'email_verified_at' => now(),
                'google_id' => $socialUser->getId(),
                'facebook_id' => $socialUser->getId(),
                'github_id' => $socialUser->getId(),
                'password' => bcrypt(uniqid(16)), // كلمة مرور عشوائية
            ]);
        }

        // تسجيل الدخول
        Auth::login($user, true);

        // إنشاء توكن (إذا كنت تستخدم Passport أو Sanctum)
        $token = $user->createToken('ACCESS_TOKEN')->accessToken;

        // إرجاع التوكن
        return response()->json(['ACCESS_TOKEN' => $token, 'user' => $user]);
    }
}
