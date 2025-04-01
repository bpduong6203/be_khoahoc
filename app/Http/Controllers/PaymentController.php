<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\QRCodeService;

class PaymentController extends Controller
{
    protected $qrCodeService;

    public function __construct(QRCodeService $qrCodeService)
    {
        $this->qrCodeService = $qrCodeService;
    }

    public function generateQRCode(Request $request)
    {
        $hoaDon = [
            'invoice_code' => $request->input('invoice_code', 'HD-310325-0002'),
            'total_amount' => $request->input('total_amount', 50000),
        ];

        $bank = [
            'ma_dinh_danh' => '23854',
            'bank_id' => '2400069704360110',
            'recipient_account_number' => '1025267307',
        ];

        $qrImage = $this->qrCodeService->generateQRCode($hoaDon, $bank);

        return response($qrImage, 200)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', 'inline; filename="qr_payment.png"');
    }
}