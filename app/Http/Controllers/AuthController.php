<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['message' => 'User registered and logged in successfully', 
        'token' => $token, 'user' => $user]);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:8',
        ]);

        if (!User::where('email', $credentials['email'])->exists()) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        if (!Auth::attempt($credentials)) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = Auth::user();
        $user->tokens()->delete();
        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json(['message' => 'User logged in successfully', 
        'token' => $token, 'user' => $user]);
    }

    public function logout(Request $request)
    {
        try {
            $user = $request->user();
            if (!$user) {
                logger()->error('User not found during logout');
                return response()->json(['message' => 'User not found'], 404);
            }

            logger()->info('User found', ['user' => $user]);

            $user->tokens()->delete();

            logger()->info('Tokens deleted for user', ['user' => $user]);

            return response()->json(['message' => 'User logged out successfully']);
        } catch (\Exception $e) {
            logger()->error('Error during logout', ['error' => $e->getMessage()]);
            return response()->json(['message' => 'Internal Server Error'], 500);
        }
    }

    // get thông tin user
    public function getUser(Request $request)
    {
        $user = $request->user(); // Lấy user đã authenticate qua Sanctum

        if (!$user) {
            return response()->json(['error' => 'Không tìm thấy người dùng'], 401);
        }

        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'created_at' => $user->created_at,
            'updated_at' => $user->updated_at,
        ]);
    }

}
