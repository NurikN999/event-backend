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

    /**
     * Register a new user
     *  @param RegisterUserRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *    path="/register",
     *   tags={"Auth"},
     *  summary="Register a new user",
     * description="Register a new user",
     * operationId="register",
     * @OA\RequestBody(
     *      required=true,
     *      description="User data",
     *      @OA\JsonContent(
     *         required={"full_name", "phone_number"},
     *        @OA\Property(property="full_name", type="string", format="text", example="John Doe"),
     *       @OA\Property(property="phone_number", type="string", format="text", example="1234567890"),
     *     )
     * ),
     * @OA\Response(
     *     response=200,
     *    description="User registered successfully",
     *   @OA\JsonContent(
     *     @OA\Property(property="message", type="string", example="Мы отправили SMS с кодом активации на ваш телефон 1234567890")
     * )
     * )
     * )
     */
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

    /**
     * Verify user's phone number
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *   path="/verify",
     *  tags={"Auth"},
     * summary="Verify user's phone number",
     * description="Verify user's phone number",
     * operationId="verify",
     * @OA\RequestBody(
     *     required=true,
     *    description="Verification data",
     *  @OA\JsonContent(
     *    required={"phone_number", "code"},
     *  @OA\Property(property="phone_number", type="string", format="text", example="1234567890"),
     * @OA\Property(property="code", type="string", format="text", example="12345"),
     * )
     * ),
     * @OA\Response(
     *    response=200,
     *  description="User verified successfully",
     * @OA\JsonContent(
     *  @OA\Property(property="message", type="string", example="Вы успешно зарегистрированы"),
     * @OA\Property(property="token", type="string", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJzdWIiOjEsImlzcyI6Imh0dHBzOi8vYXBpLmV4YW1wbGUuY29tL2p3dC9hdXRoL2xvZ2luIiwiaWF0IjoxNjIwNzIwNzI5LCJleHAiOjE2MjA3MjQzMjksIm5iZiI6MTYyMDcyMDcyOSwianRpIjoi
     * ")
     * )
     * )
     * )
     * @OA\Response(
     * response=400,
     * description="Code verification failed",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Код верификации не совпадает")
     * )
     * )
     * @OA\Response(
     * response=404,
     * description="Code not found",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Код верификации не найден")
     * )
     * )
     */
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

        Cache::forget($request->phone_number);

        $user = User::where('phone_number', $request->phone_number)->first();

        if ($user) {
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Вы успешно авторизованы',
                'token' => $token,
                'data' => $user
            ]);
        } else {
            $user = User::create([
                'phone_number' => $request->phone_number,
            ]);

            $token = JWTAuth::fromUser($user);

            return response()->json([
                'message' => 'Вы успешно зарегистрированы',
                'token' => $token,
                'data' => $user
            ]);
        }
    }

    /**
     * Login user
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @OA\Post(
     *  path="/login",
     * tags={"Auth"},
     * summary="Login user",
     * description="Login user",
     * operationId="login",
     * @OA\RequestBody(
     * required=true,
     * description="User data",
     * @OA\JsonContent(
     * required={"phone_number"},
     * @OA\Property(property="phone_number", type="string", format="text", example="1234567890"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="Code sent successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Мы отправили SMS с кодом активации на ваш телефон 1234567890")
     *  )
     * ),
     * @OA\Response(
     * response=404,
     * description="User not found",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="Пользователь не найден")
     * )
     * )
     * )
     */
    public function login(Request $request)
    {
        $phoneNumber = $request->input('phone_number');
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Пользователь не найден'
            ], 404);
        }

        $code = rand(10000, 99999);

        dispatch(new SendVerificationCode($user->phone_number, $code));

        Cache::put($user->phone_number, ['user' => $user, 'code' => $code], 600);

        return response()->json([
            'message' => 'Мы отправили SMS с кодом активации на ваш телефон ' . $user->phone_number
        ]);
    }

}
