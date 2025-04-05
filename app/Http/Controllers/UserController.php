<?php

namespace App\Http\Controllers;

use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
    protected $userService;
    
    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }
    
    /**
     * Display a listing of users
     */
    public function index()
    {
        try {
            $users = $this->userService->getUsers();
            
            return response()->json([
                'data' => $users,
                'message' => 'Users retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Store a new user
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);
        
        try {
            $user = $this->userService->createUser($validatedData);
            
            return response()->json([
                'data' => $user->toArray(),
                'message' => 'User created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Display the specified user
     */
    public function show($id)
    {
        try {
            $user = $this->userService->getUserById($id);
            
            return response()->json([
                'data' => $user->toArray(),
                'message' => 'User retrieved successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Update the specified user
     */
    public function update(Request $request, $id)
    {
        $validatedData = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'sometimes|required|string|min:8',
            'roles' => 'nullable|array',
            'roles.*' => 'exists:roles,id'
        ]);
        
        try {
            $user = $this->userService->updateUser($id, $validatedData);
            
            return response()->json([
                'data' => $user->toArray(),
                'message' => 'User updated successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Remove the specified user
     */
    public function destroy($id)
    {
        try {
            $this->userService->deleteUser($id);
            
            return response()->json([
                'message' => 'User deleted successfully'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'User not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}