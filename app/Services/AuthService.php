<?php

namespace App\Services;

use App\Models\User;

class AuthService
{
    /**
     * Tạo token cho user
     *
     * @param User $user
     * @param string $tokenName
     * @return string
     */
    public function generateAuthToken(User $user, $tokenName = 'authToken')
    {
        $user->tokens()->delete();
        $token = $user->createToken($tokenName)->plainTextToken; 
        return $token; 
    }
}