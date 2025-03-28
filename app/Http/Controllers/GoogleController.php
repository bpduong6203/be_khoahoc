<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Http\Request;

class GoogleController extends Controller
{
    public function redirectToProvider($provider)
    {
        $allowedProviders = ['google', 'facebook', 'github'];
        if (!in_array($provider, $allowedProviders)) {
            return response()->json(['error' => 'Nhà cung cấp không hợp lệ'], 400);
        }

        $url = Socialite::driver($provider)->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function handleProviderCallback($provider, Request $request)
    {
        try {
            $allowedProviders = ['google', 'facebook', 'github'];
            if (!in_array($provider, $allowedProviders)) {
                return response()->json(['error' => 'Nhà cung cấp không hợp lệ'], 400);
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

            auth()->login($user);
            $user->tokens()->delete();

            $token = $user->createToken('authToken')->plainTextToken;

            $redirectUrl = env('FRONTEND_URL') . '/callback?token=' . $token;
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đăng nhập thất bại: ' . $e->getMessage()], 500);
        }
    }
}
