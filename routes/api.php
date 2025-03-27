<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AdminController;




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

// Routes  Students
Route::prefix('students')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [StudentController::class, 'index']);         // Lấy danh sách students
    Route::post('/', [StudentController::class, 'store']);        // Tạo mới student
    Route::get('/{user}', [StudentController::class, 'show']);    // Xem chi tiết student
    Route::put('/{user}', [StudentController::class, 'update']);  // Cập nhật student
    Route::delete('/{user}', [StudentController::class, 'destroy']); // Xóa student
});

//routes teachers
Route::prefix('teachers')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [TeacherController::class, 'index']);         // Lấy danh sách teachers
    Route::post('/', [TeacherController::class, 'store']);        // Tạo mới teacher
    Route::get('/{user}', [TeacherController::class, 'show']);    // Xem chi tiết teacher
    Route::put('/{user}', [TeacherController::class, 'update']);  // Cập nhật teacher
    Route::delete('/{user}', [TeacherController::class, 'destroy']); // Xóa teacher
});

Route::prefix('admins')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index']);         // Lấy danh sách admins
    Route::post('/', [AdminController::class, 'store']);        // Tạo mới admin
    Route::get('/{user}', [AdminController::class, 'show']);    // Xem chi tiết admin
    Route::put('/{user}', [AdminController::class, 'update']);  // Cập nhật admin
    Route::delete('/{user}', [AdminController::class, 'destroy']); // Xóa admin
});
