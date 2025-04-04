<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\MailController;
use App\Http\Controllers\GoogleController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\CourseController;
use App\Http\Controllers\PasswordResetController;
use App\Http\Controllers\ReviewController;

use App\Http\Controllers\ConversationController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\EnrollmentController;
use App\Http\Controllers\UserController;

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

Route::post('/register', [AuthController::class, 'register']);

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');

// Route::post('/send-email', [MailController::class, 'sendMail']);
// đổi mật khẩu
Route::post('/password/send-reset-code', [PasswordResetController::class, 'sendResetCode']);
Route::post('/password/verify-code', [PasswordResetController::class, 'verifyCode']);
Route::post('/password/reset', [PasswordResetController::class, 'resetPassword']);

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
Route::get('/categories', [CategoryController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/categories', [CategoryController::class, 'store'])->middleware('can:admin-access');
    Route::get('/categories/{id}', [CategoryController::class, 'show']); 
    Route::put('/categories/{id}', [CategoryController::class, 'update'])->middleware('can:admin-access');
    Route::delete('/categories/{id}', [CategoryController::class, 'destroy'])->middleware('can:admin-access');
});

// ------------------------------------------------------------------

// API cho Courses
Route::get('/courses', [CourseController::class, 'index']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/courses', [CourseController::class, 'store'])->middleware('can:teacher-or-admin');
    Route::get('/courses/{courseId}', [CourseController::class, 'show']);
    Route::put('/courses/{courseId}', [CourseController::class, 'update'])->middleware('can:update-course,courseId');
    Route::delete('/courses/{courseId}', [CourseController::class, 'destroy'])->middleware('can:delete-course,courseId');
    Route::get('/my-courses', [CourseController::class, 'myCourses'])->middleware('can:teacher-or-admin');
    Route::get('/enrollments', [EnrollmentController::class, 'index']);
    Route::post('/courses/{courseId}/enroll', [EnrollmentController::class, 'store']);
    Route::get('/enrollments/{id}', [EnrollmentController::class, 'show']);
    Route::post('/enrollments/{id}/cancel', [EnrollmentController::class, 'cancel']);
    Route::post('/enrollments/{id}/payment', [EnrollmentController::class, 'updatePayment'])->middleware('can:admin-access');
    Route::get('/my-enrolled-courses', [EnrollmentController::class, 'index']);
});

// User CRUD routes
Route::middleware(['auth:sanctum', 'can:admin-access'])->group(function () {
    Route::get('/users', [UserController::class, 'index']);
    Route::post('/users', [UserController::class, 'store']);
    Route::get('/users/{id}', [UserController::class, 'show']);
    Route::put('/users/{id}', [UserController::class, 'update']);
    Route::delete('/users/{id}', [UserController::class, 'destroy']);
});

// Chat APIs
Route::middleware('auth:sanctum')->group(function () {
    // Conversations
    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'store']);
    Route::get('/conversations/{id}', [ConversationController::class, 'show']);
    Route::post('/conversations/{id}/members', [ConversationController::class, 'addMember']);
    Route::post('/conversations/{id}/leave', [ConversationController::class, 'leaveConversation']);
    Route::post('/courses/{courseId}/chat-with-teacher', [ConversationController::class, 'createTeacherConversation']);
    
    // Messages
    Route::get('/conversations/{conversationId}/messages', [MessageController::class, 'index']);
    Route::post('/conversations/{conversationId}/messages', [MessageController::class, 'store']);
    Route::post('/conversations/{conversationId}/read', [MessageController::class, 'markAsRead']);
    Route::delete('/conversations/{conversationId}/messages/{messageId}', [MessageController::class, 'destroy']);
    Route::get('/my-enrolled-courses', [CourseController::class, 'myEnrolledCourses'])->middleware('can:student-access');
});

// ------------------------------------------------------------------

