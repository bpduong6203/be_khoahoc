<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    // public function register(Request $request)
    // {
    //     $validatedData = $request->validate([
    //         'name' => 'required|string|max:255',
    //         'email' => 'required|string|email|max:255|unique:users',
    //         'password' => 'required|string|min:8',
    //     ]);

    //     $user = User::create([
    //         'name' => $validatedData['name'],
    //         'email' => $validatedData['email'],
    //         'password' => Hash::make($validatedData['password']),
    //     ]);

    //     $token = $user->createToken('authToken')->plainTextToken;

    //     return response()->json(['message' => 'User registered and logged in successfully', 'token' => $token]);
    // }
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        // Tìm role 'student'
        $studentRole = Role::where('name', 'student')->first();

        // Nếu không tìm thấy role student, báo lỗi
        if (!$studentRole) {
            return response()->json(['message' => 'Student role not found'], 500);
        }

        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => Hash::make($validatedData['password']),
            'role_id' => $studentRole->id, // Gán role_id mặc định là student
        ]);

        $token = $user->createToken('authToken')->plainTextToken;

        return response()->json([
            'message' => 'User registered and logged in successfully',
            'token' => $token,
            'user' => $user
        ]);
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

        return response()->json([
            'message' => 'User logged in successfully',
            'token' => $token,
            'user' => $user
        ]);
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
}
