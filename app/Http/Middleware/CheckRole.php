<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Kiểm tra xem người dùng đã đăng nhập chưa
        if (!Auth::check()) {
            return response()->json(['message' => 'Chưa đăng nhập'], 401);
        }

        // Lấy vai trò của người dùng
        $userRole = Auth::user()->role->name ?? null;

        // Kiểm tra xem vai trò của người dùng có trong danh sách các vai trò được phép không
        if (!$userRole || !in_array($userRole, $roles)) {
            return response()->json([
                'message' => 'Bạn không có quyền truy cập',
                'user_role' => $userRole,
                'required_roles' => $roles
            ], 403);
        }

        return $next($request);
    }
}
