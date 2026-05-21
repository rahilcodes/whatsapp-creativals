<?php

use Illuminate\Support\Facades\Mail;

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing SMTP connection to Resend...\n";
echo "Mailer: " . config('mail.default') . "\n";
echo "Host: " . config('mail.mailers.smtp.host') . "\n";
echo "Port: " . config('mail.mailers.smtp.port') . "\n";
echo "Username: " . config('mail.mailers.smtp.username') . "\n";
echo "From: " . config('mail.from.address') . "\n";

try {
    Mail::raw('This is a test email to verify that the Resend SMTP setup in iChatUp works correctly.', function ($message) {
        $message->to('onboarding@resend.dev') // Try sending to onboarding@resend.dev or the account owner's email
                ->subject('iChatUp Resend SMTP Verification Test');
    });
    echo "\nSUCCESS: The email was successfully queued/sent via SMTP!\n";
} catch (\Throwable $e) {
    echo "\nFAILURE: An error occurred while sending email:\n";
    echo $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
