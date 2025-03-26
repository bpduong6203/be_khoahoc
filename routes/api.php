<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;


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

Route::prefix('roles')->group(function () {
    Route::get('/', [RoleController::class, 'index']);
    Route::post('/', [RoleController::class, 'store']);
    Route::get('/{id}', [RoleController::class, 'show']);
    Route::put('/{id}', [RoleController::class, 'update']);
    Route::delete('/{id}', [RoleController::class, 'destroy']);
});

// Routes cho User (CRUD student và teacher, chỉ admin truy cập)
Route::prefix('users')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [UserController::class, 'index']);         // Lấy danh sách student và teacher
    Route::post('/', [UserController::class, 'store']);        // Tạo mới student hoặc teacher
    Route::get('/{user}', [UserController::class, 'show']);    // Xem chi tiết (tuỳ chọn)
    Route::put('/{user}', [UserController::class, 'update']);  // Cập nhật student hoặc teacher
    Route::delete('/{user}', [UserController::class, 'destroy']); // Xóa student hoặc teacher
});
