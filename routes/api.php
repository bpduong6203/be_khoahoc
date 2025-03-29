<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\FacebookController;
use App\Providers\AuthServiceProvider;


Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route::post('/send-email', [MailController::class, 'sendMail']);

// login mạng xã hội
Route::get('/auth/{provider}', [GoogleController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [GoogleController::class, 'handleProviderCallback']);
Route::get('/auth/facebook', [FacebookController::class, 'redirectToProvider']);
Route::get('/auth/facebook/callback', [FacebookController::class, 'handleProviderCallback']);

// lấy thông tin người dùng
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUser']);



// thêm kiểm tra quyền truy cập bằng middleware với 

// chỉ cần thêm ->middleware('can:< quyền truy cập >');


//  admin-access
//  teacher-access
//  student-access
//  eacher-or-admin
//  ...   muốn tuỳ chỉnh vào thêm các role khác App\Providers\AuthServiceProvider

//        ví dụ
// group
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/courses', [CourseController::class, 'index'])->middleware('can:teacher-or-admin');
//     Route::get('/courses/{courseId}', [CourseController::class, 'show'])->middleware('can:view-course,courseId');
// });

// đơn lẻ 
// Route::get('/courses', [CourseController::class, 'index'])->middleware('can:teacher-or-admin');

