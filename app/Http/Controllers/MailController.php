<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class MailController extends Controller
{
    public function sendMail(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        $email = $request->input('email');

        Mail::raw('This is a test email sent from Laravel!', function ($message) use ($email) {
            $message->to($email)
                    ->subject('Test Email from Laravel');
        });

        return response()->json([
            'message' => 'Email sent successfully to ' . $email,
        ], 200);
    }
}
