<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\RedirectResponse;

class VerifyEmailController extends Controller
{
    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(EmailVerificationRequest $request): RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        if ($request->user()->markEmailAsVerified()) {
            event(new Verified($request->user()));
        }

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }

    /**
     * Verify the 6-digit OTP code submitted by the user.
     */
    public function verifyOtp(\Illuminate\Http\Request $request): RedirectResponse
    {
        $user = $request->user();

        if ($user->hasVerifiedEmail()) {
            return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
        }

        $request->validate([
            'otp' => ['required', 'string', 'size:6'],
        ]);

        if ($user->otp_code !== $request->otp || !$user->otp_expires_at || now()->gt($user->otp_expires_at)) {
            return back()->withErrors(['otp' => 'The provided verification code is invalid or has expired.']);
        }

        // Mark user email as verified
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->email_verified_at = now();
        $user->save();

        event(new Verified($user));

        return redirect()->intended(route('dashboard', absolute: false).'?verified=1');
    }
}
