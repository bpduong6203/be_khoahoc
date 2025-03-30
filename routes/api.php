<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;

// =============    LƯU Ý KHI TẠO API!!!!! ==========================
// Mình sẽ kiểm soát quyền truy cập ở đay thay vì controller nhé 
// thêm kiểm tra quyền truy cập bằng middleware với 
// chỉ cần thêm ->middleware('can:< quyền truy cập >');
// ------------------------------------------------------------------
//  dùng quyền can của Gate 
// 
//  admin-access
//  teacher-access
//  student-access
//  teacher-or-admin
//
// ------------------------------------------------------------------
//  ...   muốn tuỳ chỉnh vào thêm các role khác App\Providers\AuthServiceProvider
//        ví dụ
// ------------------------------------------------------------------
// group
// Route::middleware('auth:sanctum')->group(function () {
//     Route::get('/courses', [CourseController::class, 'index'])->middleware('can:teacher-or-admin');
//     Route::get('/courses/{courseId}', [CourseController::class, 'show'])->middleware('can:view-course,courseId');
// });
// ------------------------------------------------------------------
// đơn lẻ 
// Route::get('/courses', [CourseController::class, 'index'])->middleware('can:teacher-or-admin');
// ------------------------------------------------------------------



// ------------------------------------------------------------------

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route::post('/send-email', [MailController::class, 'sendMail']);

// login mạng xã hội
Route::get('/auth/{provider}', [GoogleController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [GoogleController::class, 'handleProviderCallback']);

// ------------------------------------------------------------------

// lấy thông tin người dùng
Route::middleware('auth:sanctum')->get('/user', [AuthController::class, 'getUser']);

//Thanh toán QR đang thử nghiệm
Route::get('/generate-qr', [PaymentController::class, 'generateQRCode']);


// ------------------------------------------------------------------
// API cho Categories
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']); 
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('can:admin-access');
    Route::get('/categories/{id}', [CategoryController::class, 'show']); 
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('can:admin-access');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->middleware('can:admin-access');
});

// ------------------------------------------------------------------
// API cho Courses
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/courses', [CourseController::class, 'index']);
    Route::post('/courses', [CourseController::class, 'store'])->middleware('can:teacher-or-admin');
    Route::get('/courses/{courseId}', [CourseController::class, 'show'])->middleware('can:view-course,courseId');
    Route::put('/courses/{courseId}', [CourseController::class, 'update'])->middleware('can:update-course,courseId');
    Route::delete('/courses/{courseId}', [CourseController::class, 'destroy'])->middleware('can:delete-course,courseId');
    Route::post('/courses/{courseId}/enroll', [CourseController::class, 'enroll'])->middleware('can:student-access');
    Route::get('/my-courses', [CourseController::class, 'myCourses'])->middleware('can:teacher-or-admin');
    Route::get('/my-enrolled-courses', [CourseController::class, 'myEnrolledCourses'])->middleware('can:student-access');
});