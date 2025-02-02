<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // ==================== REGISTER ====================
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('realtime-chat')->plainTextToken;

        return response()->json([
            'message' => 'User created successfully',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    // ==================== LOGIN ====================
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !\Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Invalid credentials',
            ], 401);
        }

        $token = $user->createToken('realtime-chat')->plainTextToken;

        return response()->json([
            'message' => 'User logged in successfully',
            'user' => $user,
            'token' => $token,
        ], 200);
    }

    // ==================== LOGOUT ====================
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'User logged out successfully',
        ], 200);
    }

    // =============== SEARCH USER ============
    public function searchUsers(Request $request) 
    {
        $authUserId = Auth::id();
        $name = $request->name;

        if($name) {
            $users = User::where('name', 'like', '%'. $name . '%')
                ->where('id', '!=', $authUserId)
                ->get();

            return response()->json([
                'users' => $users,
                'message' => 'Users fetched succesful!'
            ], 200);
        }
    }

    // ================ USER DETAIL =========
    public function getUserDetail(Request $request)
    {
        $user = User::findOrFail($request->user_id);

        if($user) {
            return response()->json([
                'user' => $user,
                'message' => 'Fetched user detail!',
            ], 200);
        }else {
            return response()->json([
                'user' => null,
                'message' => "User not found!"
            ], 404);
        }
    }
}
