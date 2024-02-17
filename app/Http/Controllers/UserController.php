<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all();

        return response()->json([
            'data' => $users
        ], 200);
    }

    public function show(User $user)
    {
        return response()->json([
            'data' => $user
        ], 200);
    }

    public function update(Request $request, User $user)
    {
        $user->update($request->all());

        return response()->json([
            'message' => 'User updated successfully'
        ], 200);
    }

}
