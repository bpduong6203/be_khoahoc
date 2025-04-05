<?php

namespace App\Services;

use App\Models\Course;
use App\Models\Enrollment;
use App\Models\Payment;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EnrollmentService
{
    /**
     * Đăng ký khóa học
     */
    public function enrollCourse($courseId, $userId, $paymentMethod = null)
    {
        // Kiểm tra khóa học tồn tại
        $course = Course::findOrFail($courseId);
        
        // Kiểm tra đã đăng ký chưa
        $existingEnrollment = Enrollment::where('user_id', $userId)
            ->where('course_id', $courseId)
            ->first();
            
        if ($existingEnrollment) {
            if ($existingEnrollment->status == 'Cancelled') {
                // Nếu đã hủy trước đó, kích hoạt lại
                $existingEnrollment->status = 'Active';
                $existingEnrollment->save();
                return $existingEnrollment;
            }
            
            throw new \Exception('Bạn đã đăng ký khóa học này rồi', 400);
        }
        
        // Xác định giá
        $price = $course->discount_price ?? $course->price;
        
        // Xác định trạng thái thanh toán
        $paymentStatus = 'Pending';
        
        // Nếu khóa học miễn phí hoặc đang test
        if ($price == 0) {
            $paymentStatus = 'Completed';
        }
        
        // Tạo enrollment
        $enrollment = Enrollment::create([
            'id' => Str::uuid(),
            'user_id' => $userId,
            'course_id' => $courseId,
            'price' => $price,
            'payment_status' => $paymentStatus,
            'payment_method' => $paymentMethod,
            'status' => $paymentStatus === 'Completed' ? 'Active' : 'Pending',
            'expiry_date' => now()->addYear(), // Mặc định là 1 năm
        ]);
        
        // Tăng số lượng học viên nếu thanh toán thành công
        if ($paymentStatus === 'Completed') {
            $course->increment('enrollment_count');
        }
        
        // Tạo payment nếu cần thanh toán
        if ($price > 0 && $paymentMethod) {
            $invoiceCode = 'INV-' . strtoupper(Str::random(8));
            
            Payment::create([
                'id' => Str::uuid(),
                'invoice_code' => $invoiceCode,
                'enrollment_id' => $enrollment->id,
                'user_id' => $userId,
                'amount' => $price,
                'payment_method' => $paymentMethod,
                'status' => 'Pending',
            ]);
        }
        
        return $enrollment->load('course');
    }

    /**
     * Lấy danh sách khóa học đã đăng ký
     */
    public function getUserEnrollments($userId, $status = null)
    {
        $query = Enrollment::with(['course', 'course.teacher'])
            ->where('user_id', $userId);
            
        if ($status) {
            $query->where('status', $status);
        }
        
        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * Lấy chi tiết đăng ký khóa học
     */
    public function getEnrollment($id, $userId)
    {
        return Enrollment::with(['course', 'course.lessons', 'payment'])
            ->where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
    }
    
    /**
     * Hủy đăng ký
     */
    public function cancelEnrollment($id, $userId)
    {
        $enrollment = Enrollment::where('id', $id)
            ->where('user_id', $userId)
            ->firstOrFail();
            
        // Chỉ có thể hủy nếu chưa thanh toán
        if ($enrollment->payment_status !== 'Pending') {
            throw new \Exception('Không thể hủy đăng ký đã thanh toán', 400);
        }
        
        $enrollment->status = 'Cancelled';
        $enrollment->save();
        
        return $enrollment;
    }
    
    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus($id, $status, $transactionId = null)
    {
        $enrollment = Enrollment::findOrFail($id);
        
        $enrollment->payment_status = $status;
        
        if ($transactionId) {
            $enrollment->transaction_id = $transactionId;
        }
        
        // Nếu thanh toán thành công, kích hoạt khóa học
        if ($status === 'Completed') {
            $enrollment->status = 'Active';
            
            // Tăng số lượng học viên
            $course = $enrollment->course;
            $course->increment('enrollment_count');
        }
        
        $enrollment->save();
        
        // Cập nhật payment nếu có
        if ($enrollment->payment) {
            $enrollment->payment->status = $status;
            $enrollment->payment->transaction_id = $transactionId;
            $enrollment->payment->save();
        }
        
        return $enrollment;
    }
}