<?php

namespace App\Services;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class QRCodeService
{
    public function generateQRCode($hoaDon, $bank)
    {
        $qrContent = $this->generateFinalQRCodeContent($hoaDon, $bank);
        $qrCode = new QrCode($qrContent);
        $writer = new PngWriter();
        $result = $writer->write($qrCode);

        return $result->getString();
    }

    private function generateEMVCoQRContent($hoaDon, $bank)
    {
        $amount = number_format(round($hoaDon['total_amount']), 0, '', ',');
        $amountLength = strlen($amount);
        $maHoaDon = $hoaDon['invoice_code'];
        $maDau = "00020101021";
        $dinhDanh = "0010A00000072701";
        $xacThuc = "0208QRIBFTTA530370454";

        return $maDau . $bank['ma_dinh_danh'] . $dinhDanh . $bank['bank_id'] .
            $bank['recipient_account_number'] . $xacThuc . sprintf("%02d", $amountLength) .
            $amount . "5802VN62" . "200816" . $maHoaDon . "6304";
    }

    private function calculateCRC($data)
    {
        $crc = 0xFFFF;
        for ($i = 0; $i < strlen($data); $i++) {
            $crc ^= ord($data[$i]) << 8;
            for ($j = 0; $j < 8; $j++) {
                if (($crc & 0x8000) != 0) {
                    $crc = ($crc << 1) ^ 0x1021;
                } else {
                    $crc <<= 1;
                }
            }
        }
        return strtoupper(sprintf("%04X", $crc & 0xFFFF));
    }

    private function generateFinalQRCodeContent($hoaDon, $bank)
    {
        $qrContent = $this->generateEMVCoQRContent($hoaDon, $bank);
        $crc = $this->calculateCRC($qrContent);
        return $qrContent . $crc;
    }
}