<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;


trait GlobalTraits
{
    public function SendResponse($data, $message, $status)
    {
        $array = [
            'data' => $data,
            'message' => $message,
            'status' => $status,
        ];
        return $array;
    }

    public function SendToken($token)
    {
        $array = [
            'data' => Auth::user(),
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::factory()->getTTL() * 60,
            'status' => 200,
            'message' => 'success login and Token '
        ];
        return $array;
    }

    public function CreateMultieFolders()
    {
        $path = public_path();
        $folders = array('images', 'photo', 'cover');
        foreach ($folders as $folder) {
            if (!is_dir($path . '/' . $folder)) {
                mkdir($path . '/' . $folder, 0777);
            }
        }
    }
}
