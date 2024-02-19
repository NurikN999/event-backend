<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    /**
     * Display a listing of the resource.
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *   path="/users",
     *  tags={"Users"},
     * summary="Get all users",
     * description="Get all users",
     * operationId="index",
     * @OA\Response(
     *    response=200,
     *  description="List of users",
     * )
     * )
     *
     */
    public function index()
    {
        $users = User::all();

        return response()->json([
            'data' => $users
        ], 200);
    }

    /**
     * Display the specified resource.
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @OA\Get(
     *  path="/users/{user}",
     * tags={"Users"},
     * summary="Get user by id",
     * description="Get user by id",
     * operationId="show",
     * @OA\Parameter(
     *   name="user",
     * in="path",
     * required=true,
     * description="User id",
     * @OA\Schema(
     *    type="integer"
     * )
     * ),
     * @OA\Response(
     *   response=200,
     * description="User data",
     * )
     * )
     */
    public function show(User $user)
    {
        return response()->json([
            'data' => $user
        ], 200);
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     * @OA\Put(
     * path="/users/{user}",
     * tags={"Users"},
     * summary="Update user by id",
     * description="Update user by id",
     * operationId="update",
     * @OA\Parameter(
     *  name="user",
     * in="path",
     * required=true,
     * description="User id",
     * @OA\Schema(
     *   type="integer"
     * )
     * ),
     * @OA\RequestBody(
     * required=true,
     * description="User data",
     * @OA\JsonContent(
     * required={"name", "email"},
     * @OA\Property(property="name", type="string", format="text", example="John Doe"),
     * @OA\Property(property="email", type="string", format="email", example="johndoe@mail.com"),
     * )
     * ),
     * @OA\Response(
     * response=200,
     * description="User updated successfully",
     * @OA\JsonContent(
     * @OA\Property(property="message", type="string", example="User updated successfully")
     * )
     * )
     * )
     *
     */
    public function update(Request $request, User $user)
    {
        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully'
        ], 200);
    }

}
