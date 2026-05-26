<?php

namespace App\Services;

use App\Models\Lead;
use App\Models\Message;
use App\Models\ActivityLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ReceiptAnalyzerService
{
    private string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.openai.key') ?: env('OPENAI_API_KEY', '');
    }

    /**
     * Analyze a base64 receipt/screenshot using GPT-4o-mini (Vision).
     * Extracts details and returns parsed payment metrics.
     */
    public function analyzeReceipt(string $base64Data): array
    {
        if (empty($this->apiKey)) {
            Log::error("OpenAI API key missing inside ReceiptAnalyzerService.");
            return ['is_payment_receipt' => false];
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout(25)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model' => 'gpt-4o-mini',
                    'messages' => [
                        [
                            'role' => 'user',
                            'content' => [
                                [
                                    'type' => 'text',
                                    'text' => "You are an expert financial audit agent. Analyze this image and determine if it is a successful payment transfer receipt/screenshot (such as GPay, Paytm, PhonePe, UPI, bank transfer, IMPS, NEFT, etc.).\n\n" .
                                              "Respond strictly in JSON format with exactly these keys:\n" .
                                              "{\n" .
                                              "  \"is_payment_receipt\": boolean,\n" .
                                              "  \"amount\": float or null,\n" .
                                              "  \"currency\": \"INR\" or \"USD\" or null,\n" .
                                              "  \"reference_number\": string or null (UPI transaction ID, UTR, Ref number),\n" .
                                              "  \"transaction_date\": \"YYYY-MM-DD\" or null,\n" .
                                              "  \"payment_app\": string or null,\n" .
                                              "  \"status\": \"success\" or \"failed\" or \"pending\"\n" .
                                              "}"
                                ],
                                [
                                    'type' => 'image_url',
                                    'image_url' => [
                                        'url' => "data:image/jpeg;base64,{$base64Data}"
                                    ]
                                ]
                            ]
                        ]
                    ],
                    'response_format' => ['type' => 'json_object'],
                    'max_tokens' => 200,
                ]);

            if ($response->failed()) {
                Log::error("OpenAI Vision request failed inside ReceiptAnalyzerService", [
                    'status' => $response->status(),
                    'body' => $response->body()
                ]);
                return ['is_payment_receipt' => false];
            }

            $result = json_decode($response->json('choices.0.message.content'), true);
            return is_array($result) ? $result : ['is_payment_receipt' => false];

        } catch (\Throwable $e) {
            Log::error("ReceiptAnalyzerService Exception: " . $e->getMessage());
            return ['is_payment_receipt' => false];
        }
    }

    /**
     * Process an incoming receipt for a lead.
     * Sets lead status and generates a smart trust-but-verify auto-reply.
     */
    public function processReceiptForLead(string $phone, string $base64Data, int $tenantId): ?string
    {
        Log::info("Smart Vision AI: Analyzing incoming receipt for lead {$phone}...");
        
        $analysis = $this->analyzeReceipt($base64Data);

        if (empty($analysis['is_payment_receipt'])) {
            Log::info("Smart Vision AI: Screenshot from {$phone} is NOT a payment receipt.");
            return null;
        }

        Log::info("Smart Vision AI: Screenshot verified as payment receipt", $analysis);

        // Update lead lifecycle stage to 'payment_pending_verification'
        try {
            $lead = Lead::where('phone', $phone)->first();
            if ($lead) {
                $lead->capture_stage = 'payment_pending_verification';
                
                // Add extracted metrics to the lead summary
                $app = $analysis['payment_app'] ?? 'Bank Transfer';
                $ref = $analysis['reference_number'] ?? 'N/A';
                $amount = $analysis['amount'] ?? 'N/A';
                $currency = $analysis['currency'] ?? 'INR';
                
                $lead->summary = "[$app Payment Screenshot] Amount: $currency $amount, Ref: $ref. " . ($lead->summary ?? '');
                $lead->save();

                ActivityLog::record('payment_received', "Smart Vision: Detected screenshot from {$phone} of amount {$currency} {$amount} via {$app}", $phone, $analysis);
            }
        } catch (\Throwable $e) {
            Log::warning("Could not update lead stage in processReceiptForLead: " . $e->getMessage());
        }

        // Generate smart trust-but-verify response
        $amountStr = isset($analysis['amount']) ? ($analysis['currency'] ?? 'INR') . ' ' . $analysis['amount'] : 'your payment';
        $appStr = $analysis['payment_app'] ?? 'transfer';

        return "Thanks for the payment screenshot of {$amountStr} via {$appStr}! " .
               "Your booking/order has been logged and is now confirmed. 🎉\n\n" .
               "⚠️ Please note: Our accounts team is currently verifying the transfer details. " .
               "In the rare event that the transfer does not reflect in our system within 24 hours, this confirmation will automatically become invalid.";
    }
}
