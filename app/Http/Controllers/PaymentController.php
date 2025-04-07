<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QRCodeService;
use App\Models\Payment;
use App\Models\Enrollment; // Thêm model Enrollment
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;

class PaymentController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Tạo mới thanh toán và sinh QR code
     */
    public function createPayment(Request $request)
    {
        try {
            // Validate request
            $request->validate([
                'enrollment_id' => 'required|uuid|exists:enrollments,id',
                'payment_method' => 'required|in:Bank', // Chỉ hỗ trợ Bank như trong code gốc
            ]);

            // Lấy enrollment để lấy giá trị price
            $enrollment = Enrollment::findOrFail($request->enrollment_id);
            $amount = $enrollment->price; // Tự động lấy price từ enrollment

            // Tạo invoice_code theo định dạng HD-<ngày tháng năm>-<số tăng dần>
            $date = Carbon::now()->format('dmY');
            $lastPayment = Payment::where('invoice_code', 'like', "HD-{$date}%")
                ->orderBy('invoice_code', 'desc')
                ->first();
            
            $sequence = $lastPayment ? (int)substr($lastPayment->invoice_code, -4) + 1 : 1;
            $invoiceCode = sprintf("HD-%s-%04d", $date, $sequence);

            // Tạo bản ghi payment trong database
            $payment = DB::transaction(function () use ($request, $invoiceCode, $amount) {
                return Payment::create([
                    'id' => Str::uuid(),
                    'invoice_code' => $invoiceCode,
                    'enrollment_id' => $request->enrollment_id,
                    'user_id' => $request->user()->id,
                    'amount' => $amount, // Sử dụng price từ enrollment
                    'payment_method' => $request->payment_method,
                    'status' => 'Pending',
                    'billing_info' => $request->billing_info ? json_encode($request->billing_info) : null,
                ]);
            });

            // Nếu phương thức là Bank, sinh QR code
            if ($request->payment_method === 'Bank') {
                $hoaDon = [
                    'invoice_code' => $invoiceCode,
                    'total_amount' => $amount, // Dùng amount từ enrollment
                ];

                $bank = [
                    'ma_dinh_danh' => '23854',
                    'bank_id' => '2400069704360110',
                    'recipient_account_number' => '1025267307',
                ];

                $qrImage = $this->qrCodeService->generateQRCode($hoaDon, $bank);

                return response([
                    'payment' => $payment,
                    'qr_code' => base64_encode($qrImage)
                ], 201);
            }

            return response()->json([
                'payment' => $payment,
                'message' => 'Payment created successfully'
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create payment',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cập nhật trạng thái thanh toán
     */
    public function updatePaymentStatus(Request $request, $paymentId)
    {
        try {
            $request->validate([
                'status' => 'required|in:Pending,Completed,Failed,Refunded',
                'transaction_id' => 'nullable|string|max:100'
            ]);

            $payment = Payment::findOrFail($paymentId);

            // Cập nhật trạng thái payment
            $payment->update([
                'status' => $request->status,
                'transaction_id' => $request->transaction_id,
                'updated_at' => now()
            ]);

            // Cập nhật enrollment tương ứng
            if ($payment->enrollment_id) {
                $enrollment = $payment->enrollment()->first();
                if ($enrollment) {
                    $enrollment->update([
                        'payment_status' => $request->status,
                        'status' => $request->status === 'Completed' ? 'Active' : $enrollment->status,
                        'payment_method' => $payment->payment_method,
                        'transaction_id' => $request->transaction_id
                    ]);
                }
            }

            return response()->json([
                'payment' => $payment->fresh(),
                'message' => 'Payment status updated successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update payment status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}