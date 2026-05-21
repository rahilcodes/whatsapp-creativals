<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        $otpCode = rand(100000, 999999);
        $user->otp_code = $otpCode;
        $user->otp_expires_at = now()->addMinutes(15);
        $user->save();

        try {
            \Illuminate\Support\Facades\Mail::to($user->email)->send(new \App\Mail\SendOtpMail($otpCode));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('OTP Email Resend Failed: ' . $e->getMessage());
        }

        return back()->with('status', 'verification-link-sent');
    }
}
