<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Laravel\Socialite\Facades\Socialite;
use App\Models\User;
use App\Models\SocialAccount;
use Illuminate\Http\Request;
use App\Services\AuthService;
use Illuminate\Support\Facades\Auth;


class FacebookController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function redirectToProvider()
    {
        $url = Socialite::driver('facebook')->stateless()->redirect()->getTargetUrl();
        return response()->json(['url' => $url]);
    }

    public function handleProviderCallback(Request $request)
    {
        try {
            $socialUser = Socialite::driver('facebook')->stateless()->user();

            $socialAccount = SocialAccount::where('provider_name', 'facebook')
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
                    'provider_name' => 'facebook',
                    'provider_id' => $socialUser->getId(),
                ]);
            }

            Auth::login($user);

            $token = $this->authService->generateAuthToken($user); 

            $redirectUrl = env('FRONTEND_URL') . '/callback?token=' . $token;
            return redirect($redirectUrl);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đăng nhập thất bại: ' . $e->getMessage()], 500);
        }
    }
}