<?php

namespace App\Services;

use App\Models\User;
use App\Models\SocialAccount;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\Auth;

class SocialAuthService
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function handleSocialLogin($provider)
    {
        $allowedProviders = ['google', 'facebook', 'github'];
        if (!in_array($provider, $allowedProviders)) {
            throw new \Exception('Nhà cung cấp không hợp lệ');
        }

        $socialUser = Socialite::driver($provider)->stateless()->user();

        $socialAccount = SocialAccount::where('provider_name', $provider)
            ->where('provider_id', $socialUser->getId())
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $user = User::where('email', $socialUser->getEmail())->first();

            if (!$user) {
                $user = User::create([
                    'name' => $socialUser->getName(),
                    'email' => $socialUser->getEmail(),
                    'password' => bcrypt('random-password'),
                ]);
            }

            SocialAccount::create([
                'user_id' => $user->id,
                'provider_name' => $provider,
                'provider_id' => $socialUser->getId(),
            ]);
        }

        Auth::login($user);
        $token = $this->authService->generateAuthToken($user);

        return [
            'user' => $user,
            'token' => $token,
        ];
    }
}