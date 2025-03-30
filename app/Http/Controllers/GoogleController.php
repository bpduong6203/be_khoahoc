<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use App\Services\SocialAuthService;

class GoogleController extends Controller
{
    protected $socialAuthService;

    public function __construct(SocialAuthService $socialAuthService)
    {
        $this->socialAuthService = $socialAuthService;
    }

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
            $result = $this->socialAuthService->handleSocialLogin($provider);
            $redirectUrl = env('FRONTEND_URL') . '/callback?token=' . $result['token'];

            return redirect($redirectUrl);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Đăng nhập thất bại: ' . $e->getMessage()], 500);
        }
    }
}