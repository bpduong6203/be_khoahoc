<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Mail\PasswordResetMail;
use Illuminate\Support\Facades\DB;

class PasswordResetController extends Controller
{
    /**
     * Gửi mã xác thực qua email
     */
    public function sendResetCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');

        // Kiểm tra tài khoản tồn tại
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy tài khoản'], 404);
        }

        // Tạo mã xác thực ngẫu nhiên 6 chữ số
        $code = sprintf('%06d', mt_rand(1, 999999));
        $expiresAt = now()->addMinutes(15); // Mã có hiệu lực trong 15 phút

        // Lưu mã vào database
        PasswordResetCode::create([
            'email' => $email,
            'code' => $code,
            'expires_at' => $expiresAt,
        ]);

        // Gửi mã qua email
        Mail::to($email)->send(new PasswordResetMail($code));

        return response()->json(['message' => 'Mã xác thực đã được gửi đến email của bạn']);
    }

    /**
     * Xác thực mã và cho phép đặt lại mật khẩu
     */
    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');
        $code = $request->input('code');

        // Tìm mã xác thực hợp lệ
        $resetCode = PasswordResetCode::where('email', $email)
            ->where('code', $code)
            ->where('expires_at', '>', now())
            ->latest()
            ->first();

        if (!$resetCode) {
            return response()->json(['message' => 'Mã xác thực không hợp lệ hoặc đã hết hạn'], 400);
        }

        // Tạo token tạm thời để sử dụng khi đặt lại mật khẩu
        $resetToken = Str::random(60);
        
        // Lưu token vào bảng password_reset_tokens
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->updateOrInsert(
            ['email' => $email],
            ['token' => $resetToken, 'created_at' => now()]
        );

        return response()->json([
            'message' => 'Mã xác thực hợp lệ',
            'reset_token' => $resetToken
        ]);
    }

    /**
     * Đặt lại mật khẩu mới
     */
    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:8|confirmed',
            'reset_token' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $email = $request->input('email');
        $password = $request->input('password');
        $resetToken = $request->input('reset_token');

        // Kiểm tra token hợp lệ
        $tokenData =\Illuminate\Support\Facades\DB::table('password_reset_tokens')
            ->where('email', $email)
            ->where('token', $resetToken)
            ->first();

        if (!$tokenData) {
            return response()->json(['message' => 'Token không hợp lệ'], 400);
        }

        // Tìm user và cập nhật mật khẩu
        $user = User::where('email', $email)->first();

        if (!$user) {
            return response()->json(['message' => 'Không tìm thấy tài khoản'], 404);
        }

        // Cập nhật mật khẩu
        $user->password = Hash::make($password);
        $user->save();

        // Xóa token đã sử dụng
        \Illuminate\Support\Facades\DB::table('password_reset_tokens')->where('email', $email)->delete();

        // Xóa các mã xác thực cũ
        PasswordResetCode::where('email', $email)->delete();

        return response()->json(['message' => 'Mật khẩu đã được đặt lại thành công']);
    }
}

