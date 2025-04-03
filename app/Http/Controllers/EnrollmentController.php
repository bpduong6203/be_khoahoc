<?php

namespace App\Http\Controllers;

use App\Services\EnrollmentService;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class EnrollmentController extends Controller
{
    protected $enrollmentService;

    public function __construct(EnrollmentService $enrollmentService)
    {
        $this->enrollmentService = $enrollmentService;
    }

    /**
     * Lấy danh sách khóa học đã đăng ký
     */
    public function index(Request $request)
    {
        try {
            $status = $request->get('status');
            $enrollments = $this->enrollmentService->getUserEnrollments($request->user()->id, $status);
            
            return response()->json([
                'data' => $enrollments,
                'message' => 'Danh sách khóa học đã đăng ký'
            ]);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Đăng ký khóa học
     */
    public function store(Request $request, $courseId)
    {
        $validatedData = $request->validate([
            'payment_method' => 'nullable|in:Momo,Bank,Paypal,Cash',
        ]);

        try {
            $enrollment = $this->enrollmentService->enrollCourse(
                $courseId, 
                $request->user()->id, 
                $validatedData['payment_method'] ?? null
            );
            
            return response()->json([
                'data' => $enrollment,
                'message' => $enrollment->payment_status === 'Completed' 
                    ? 'Đăng ký khóa học thành công' 
                    : 'Đăng ký khóa học thành công, vui lòng thanh toán để kích hoạt'
            ], 201);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy khóa học'], 404);
        } catch (\Exception $e) {
            $code = $e->getCode();
            // Only use exception code if it's a valid HTTP status code
            if ($code < 100 || $code > 599) {
                $code = 400; // Default to 400 Bad Request
            }
            return response()->json(['message' => $e->getMessage()], $code);
        }
    }

    /**
     * 
     * 
     * 
     * 
     * 
     * 
     * 
     * Lấy chi tiết đăng ký
     */
    public function show(Request $request, $id)
    {
        try {
            $enrollment = $this->enrollmentService->getEnrollment($id, $request->user()->id);
            
            return response()->json([
                'data' => $enrollment,
                'message' => 'Chi tiết đăng ký khóa học'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    /**
     * Hủy đăng ký
     */
    public function cancel(Request $request, $id)
    {
        try {
            $enrollment = $this->enrollmentService->cancelEnrollment($id, $request->user()->id);
            
            return response()->json([
                'data' => $enrollment,
                'message' => 'Đã hủy đăng ký khóa học'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode() ?: 400);
        }
    }

    /**
     * Cập nhật trạng thái thanh toán (webhook từ cổng thanh toán)
     */
    public function updatePayment(Request $request, $id)
    {
        $validatedData = $request->validate([
            'status' => 'required|in:Pending,Completed,Failed,Refunded',
            'transaction_id' => 'nullable|string',
        ]);

        try {
            // Kiểm tra quyền (chỉ admin hoặc API webhook từ cổng thanh toán)
            if (!$request->user()->hasRole('admin') && !$request->has('webhook_secret')) {
                return response()->json(['message' => 'Không có quyền truy cập'], 403);
            }
            
            // Kiểm tra webhook_secret nếu có
            if ($request->has('webhook_secret') && $request->webhook_secret !== config('app.payment_webhook_secret')) {
                return response()->json(['message' => 'Webhook secret không hợp lệ'], 403);
            }
            
            $enrollment = $this->enrollmentService->updatePaymentStatus(
                $id, 
                $validatedData['status'],
                $validatedData['transaction_id'] ?? null
            );
            
            return response()->json([
                'data' => $enrollment,
                'message' => 'Đã cập nhật trạng thái thanh toán'
            ]);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Không tìm thấy thông tin đăng ký'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}