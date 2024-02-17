<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterUserRequest;
use App\Jobs\SendVerificationCode;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\VerificationCode;
use Illuminate\Support\Facades\Cache;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{

    public function register(RegisterUserRequest $request)
    {
        $user = new User([
            'full_name' => $request->full_name,
            'phone_number' => $request->phone_number,
        ]);

        $code = rand(10000, 99999);

        dispatch(new SendVerificationCode($user->phone_number, $code));

        Cache::put($user->phone_number, ['user' => $user, 'code' => $code], 600);

        return response()->json([
            'message' => 'Мы отправили SMS с кодом активации на ваш телефон ' . $user->phone_number
        ]);
    }

    public function verify(Request $request)
    {
        $data = Cache::get($request->phone_number);

        if (!$data) {
            return response()->json([
                'message' => 'Код верификации не найден'
            ], 404);
        }

        if ($data['code'] != $request->code) {
            return response()->json([
                'message' => 'Код верификации не совпадает'
            ], 400);
        }

        $user = $data['user'];

        $user->save();
        $token = JWTAuth::fromUser($user);

        Cache::forget($request->phone_number);

        return response()->json([
            'message' => 'Вы успешно зарегистрированы',
            'token' => $token
        ]);
    }

}
