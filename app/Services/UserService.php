<?php

namespace App\Services;

use App\Models\User;
use App\DTO\UserDTO;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserService
{
    /**
     * Get all users
     */
    public function getUsers()
    {
        $users = User::with('roles')->get();
        return $users->map(function($user) {
            return UserDTO::fromUser($user, ['id', 'name', 'email', 'roles']);
        });
    }
    
    /**
     * Create a new user
     */
    public function createUser(array $data)
    {
        $user = User::create([
            'id' => Str::uuid(),
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);
        
        // Assign roles if provided
        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }
        
        return UserDTO::fromUser($user->fresh(['roles']), ['id', 'name', 'email', 'roles']);
    }
    
    /**
     * Get user by ID
     */
    public function getUserById($id)
    {
        $user = User::with('roles')->findOrFail($id);
        return UserDTO::fromUser($user, ['id', 'name', 'email', 'roles']);
    }
    
    /**
     * Update user
     */
    public function updateUser($id, array $data)
    {
        $user = User::findOrFail($id);
        
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }
        
        $user->update($data);
        
        // Update roles if provided
        if (isset($data['roles'])) {
            $user->roles()->sync($data['roles']);
        }
        
        return UserDTO::fromUser($user->fresh(['roles']), ['id', 'name', 'email', 'roles']);
    }
    
    /**
     * Delete user
     */
    public function deleteUser($id)
    {
        $user = User::findOrFail($id);
        $user->tokens()->delete(); // Delete all associated tokens
        $user->delete();
        
        return true;
    }
}