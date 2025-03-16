<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailController;


// Route trả về thông tin người dùng đang đăng nhập
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Route đăng ký
Route::post('/register', [AuthController::class, 'register']);

// Route đăng nhập
Route::post('/login', [AuthController::class, 'login']);

// Route đăng xuất
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');


Route::post('/send-email', [MailController::class, 'sendMail']);

