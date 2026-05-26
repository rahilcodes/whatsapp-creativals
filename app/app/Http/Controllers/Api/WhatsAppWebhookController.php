<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\BotSetting;
use App\Models\FlaggedConversation;
use App\Models\Message;
use App\Services\AIService;
use App\Services\BotService;
use App\Services\MemoryService;
use App\Services\SafetyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    public function __construct(
        private SafetyService  $safety,
        private MemoryService  $memory,
        private AIService      $ai,
        private BotService     $bot,
    ) {}

    // ── POST /api/whatsapp/message ────────────────────────────
    public function receive(Request $request): JsonResponse
    {
        // Validate shared secret
        if ($request->header('X-Bot-Secret') !== env('SHARED_SECRET', 'whatsapp_ai_secret_2026')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Resolve tenant from Node.js header — CRITICAL for data isolation
        $tenantId = (int) ($request->header('X-Tenant-ID') ?? 1);
        app()->instance('tenant_id', $tenantId);

        // Check if tenant exists and is active (not suspended/inactive)
        $tenant = \App\Models\Tenant::find($tenantId);
        if (!$tenant || $tenant->status !== 'active') {
            return response()->json(['status' => 'tenant_suspended']);
        }

        $phone     = $request->string('phone')->toString();
        $jid       = $request->string('jid')->toString();
        $message   = $request->string('message')->toString();
        $messageId = $request->string('message_id')->toString();

        if (!$phone || !$message) {
            return response()->json(['error' => 'phone and message required'], 422);
        }

        // ── STEP 1: Duplicate check ───────────────────────────
        if ($messageId && $this->safety->isDuplicate($messageId)) {
            return response()->json(['status' => 'duplicate_ignored']);
        }

        // Log incoming
        ActivityLog::record('message_received', "Message from {$phone}", $phone, [
            'preview' => substr($message, 0, 80),
            'has_image' => $request->has('image_payload') && !empty($request->input('image_payload')),
        ]);

        // Capture and store image payload if present (Phase 2)
        $imagePath = null;
        if ($request->has('image_payload') && !empty($request->input('image_payload'))) {
            $imagePath = $this->compressAndStoreImage($request->input('image_payload'), $tenantId);
        }

        // ── STEP 2: Save incoming user message ────────────────
        try {
            $this->memory->saveMessage($phone, $jid, 'user', $message, $messageId, $imagePath);
        } catch (\Illuminate\Database\UniqueConstraintViolationException $e) {
            return response()->json(['status' => 'duplicate_ignored']);
        }
        $this->memory->touchActivity($phone);

        // ── STEP 2.2: Smart Vision AI Receipt Check (Phase 2) ───
        if ($imagePath && $request->has('image_payload') && !empty($request->input('image_payload'))) {
            try {
                $analyzer = app(\App\Services\ReceiptAnalyzerService::class);
                $visionReply = $analyzer->processReceiptForLead($phone, $request->input('image_payload'), $tenantId);
                
                if ($visionReply) {
                    // Save reply & send to WhatsApp, bypassing standard AI generation entirely!
                    $savedMessage = $this->memory->saveMessage($phone, $jid, 'assistant', $visionReply);
                    $this->bot->sendReply($jid, $visionReply, false, $savedMessage->id);
                    
                    ActivityLog::record('ai_reply', "Smart Vision receipt reply sent to {$phone}", $phone);
                    
                    // Dispatch background lead intelligence and profiling job
                    \App\Jobs\ProcessLeadEngagement::dispatch($tenantId, $phone, $message);
                    
                    return response()->json(['status' => 'ok', 'reply' => 'Receipt acknowledged']);
                }
            } catch (\Throwable $e) {
                Log::error("Failed inside Smart Vision check: " . $e->getMessage());
            }
        }

        // ── STEP 2.5: Subscription / Trial check ─────────────
        // Messages are always saved above so they appear in leads/chats.
        // But AI replies are blocked when trial has expired or subscription
        // is inactive — prevents free unlimited usage after trial ends.
        $subStatus    = $tenant->subscription_status ?? 'trialing';
        $trialExpired = ($subStatus === 'trialing')
            && $tenant->trial_ends_at
            && now()->gt($tenant->trial_ends_at);

        if ($trialExpired) {
            // Lazy-update status to 'expired' so dashboard reflects correctly
            $tenant->subscription_status = 'expired';
            $tenant->save();
        }

        if ($trialExpired || in_array($subStatus, ['expired', 'cancelled', 'past_due'])) {
            ActivityLog::record('subscription_block', "AI reply blocked — trial/subscription expired for tenant {$tenantId}", $phone);
            return response()->json(['status' => 'subscription_required']);
        }

        // ── STEP 3: Human takeover active → skip AI ──────────
        if ($this->safety->hasHumanTakeover($phone)) {
            ActivityLog::record('human_takeover', "Human takeover active — AI skipped", $phone);
            return response()->json(['status' => 'human_takeover']);
        }

        // ── STEP 4: Bot enabled? ──────────────────────────────
        if (!$this->safety->isBotEnabled()) {
            return response()->json(['status' => 'bot_disabled']);
        }

        // ── STEP 4.5: Failsafe / Ban Risk Check ───────────────
        $wa = \App\Models\WhatsappStatus::current();
        if ($wa->isBanRisk()) {
            $fallback = "We are currently experiencing high volume. Our team will get back to you shortly.";
            $savedFallback = $this->memory->saveMessage($phone, $jid, 'assistant', $fallback);
            // Enqueue the fallback message. It will sit in the queue until the session is manually reconnected.
            $this->bot->sendReply($jid, $fallback, false, $savedFallback->id);
            ActivityLog::record('safety_block', "Ban risk active — AI skipped, fallback enqueued", $phone);
            return response()->json(['status' => 'ban_risk_paused']);
        }

        // ── STEP 5: Working hours? ────────────────────────────
        if (!$this->safety->isWithinWorkingHours()) {
            $fallback = $this->safety->outsideHoursMessage();
            $savedFallback = $this->memory->saveMessage($phone, $jid, 'assistant', $fallback);
            $this->bot->sendReply($jid, $fallback, false, $savedFallback->id);
            ActivityLog::record('safety_block', "Outside working hours — fallback sent", $phone);
            return response()->json(['status' => 'outside_hours']);
        }

        // ── STEP 6: Rate limited? ────────────────────────────
        if ($this->safety->isRateLimited($phone)) {
            ActivityLog::record('safety_block', "Rate limited — skipped", $phone);
            return response()->json(['status' => 'rate_limited']);
        }

        // ── STEP 7: Keyword-based human trigger? ─────────────
        if ($this->safety->requiresHumanTakeover($message)) {
            FlaggedConversation::flagPhone($phone, 'keyword_trigger', $message);
            ActivityLog::record('human_flag', "Flagged for human — keyword triggered", $phone, [
                'message' => substr($message, 0, 100),
            ]);
            $this->notifyAdmins($phone, 'keyword_trigger', $message);
            return response()->json(['status' => 'flagged_human']);
        }

        // ── STEP 8: Build context & generate AI reply ─────────
        $context = $this->memory->buildContext($phone);

        try {
            $result = $this->ai->generateReply($phone, $message, $context);
        } catch (\Throwable $e) {
            Log::error('AI generation failed', ['error' => $e->getMessage(), 'phone' => $phone]);
            ActivityLog::record('error', 'AI generation failed: ' . $e->getMessage(), $phone);
            return response()->json(['status' => 'ai_error'], 500);
        }

        if (!$result) {
            // AI returned null — send a warm fallback so customer isn't left on read
            $fallback = BotSetting::get(
                'ai_fallback_message',
                "Thanks for reaching out! 😊 We've received your message and will get back to you shortly."
            );
            $savedFallback = $this->memory->saveMessage($phone, $jid, 'assistant', $fallback);
            $this->bot->sendReply($jid, $fallback, false, $savedFallback->id);
            ActivityLog::record('error', 'AI unavailable — fallback reply sent (check OpenAI quota/key)', $phone);

            // Dispatch background lead intelligence and profiling job
            \App\Jobs\ProcessLeadEngagement::dispatch($tenantId, $phone, $message);

            return response()->json(['status' => 'ai_fallback_sent']);
        }

        // ── STEP 9: Post-check — flag human if AI uncertain ───
        if ($result['flag_human']) {
            FlaggedConversation::flagPhone($phone, $result['flag_reason'] ?? 'ai_uncertain', $message);
            ActivityLog::record('human_flag', "AI uncertain — flagged for human ({$result['flag_reason']})", $phone);
            $this->notifyAdmins($phone, $result['flag_reason'] ?? 'ai_uncertain', $message);

            // Dispatch background lead intelligence and profiling job
            \App\Jobs\ProcessLeadEngagement::dispatch($tenantId, $phone, $message);

            return response()->json(['status' => 'flagged_uncertain']);
        }

        // ── STEP 10: Save reply & send ────────────────────────
        $reply = $result['reply'];
        $savedMessage = $this->memory->saveMessage($phone, $jid, 'assistant', $reply);
        $this->bot->sendReply($jid, $reply, false, $savedMessage->id);

        ActivityLog::record('ai_reply', "AI reply sent to {$phone}", $phone, [
            'preview' => substr($reply, 0, 80),
        ]);

        // Dispatch background lead intelligence and profiling job
        \App\Jobs\ProcessLeadEngagement::dispatch($tenantId, $phone, $message);

        // ── STEP 11: Background: update user summary every 10 msgs
        $msgCount = Message::where('phone', $phone)->count();
        if ($msgCount > 0 && $msgCount % 10 === 0) {
            $history = $this->memory->getShortTermMemory($phone, 20);
            $summary = $this->ai->generateUserSummary($phone, $history);
            if ($summary) {
                $this->memory->updateUserSummary($phone, $summary);
            }
        }

        return response()->json(['status' => 'ok', 'reply' => substr($reply, 0, 60) . '...']);
    }

    private function notifyAdmins(string $phone, string $reason, string $message): void
    {
        $adminsRaw = \App\Models\BotSetting::get('admin_phones', '');
        if (!$adminsRaw) return;

        $admins = array_map('trim', explode(',', $adminsRaw));
        $alertText = "🚨 *HOT LEAD ALERT* 🚨\n"
                   . "Phone: +{$phone}\n"
                   . "Trigger: \"{$reason}\"\n"
                   . "Message: \"{$message}\"\n\n"
                   . "Action: AI has paused. Please reply to them manually now.";

        foreach ($admins as $adminPhone) {
            if ($adminPhone) {
                $adminJid = "{$adminPhone}@s.whatsapp.net";
                $this->bot->sendReply($adminJid, $alertText, true);
            }
        }
    }

    // ── POST /api/whatsapp/status (from Node bot) ─────────────
    public function statusUpdate(Request $request): JsonResponse
    {
        if ($request->header('X-Bot-Secret') !== env('SHARED_SECRET', 'whatsapp_ai_secret_2026')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Resolve tenant from Node.js header — CRITICAL for data isolation
        $tenantId = (int) ($request->header('X-Tenant-ID') ?? 1);
        app()->instance('tenant_id', $tenantId);

        $status = $request->string('status')->toString();
        \App\Models\WhatsappStatus::updateStatus($status, $request->all());
        
        $reason = $request->input('session_state') === 'banned_risk' ? 'ban risk detected' : 'status changed';
        ActivityLog::record('status_change', "WhatsApp status changed to: {$status} ({$reason})");

        return response()->json(['ok' => true]);
    }

    /**
     * Smart Vision AI image receiver and native GD compressor.
     * Compresses the receipt to max 800px width/height and saves it locally.
     */
    private function compressAndStoreImage(string $base64Data, int $tenantId): ?string
    {
        try {
            $imgData = base64_decode($base64Data);
            if (!$imgData) return null;

            // Load image using native GD (Phase 2)
            $srcImg = @imagecreatefromstring($imgData);
            if (!$srcImg) {
                Log::warning("GD Library could not parse base64 image data.");
                return null;
            }

            $width = imagesx($srcImg);
            $height = imagesy($srcImg);
            $maxWidth = 800;

            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = (int) ($height * ($maxWidth / $width));
                $dstImg = imagescale($srcImg, $newWidth, $newHeight);
                
                ob_start();
                imagejpeg($dstImg, null, 75); // 75% quality compression
                $compressedData = ob_get_clean();
                
                imagedestroy($srcImg);
                imagedestroy($dstImg);
            } else {
                ob_start();
                imagejpeg($srcImg, null, 75);
                $compressedData = ob_get_clean();
                imagedestroy($srcImg);
            }

            // Save to storage
            $filename = 'receipt_' . time() . '_' . uniqid() . '.jpg';
            $directory = storage_path("app/public/receipts/{$tenantId}");
            if (!file_exists($directory)) {
                mkdir($directory, 0755, true);
            }

            $path = "{$directory}/{$filename}";
            file_put_contents($path, $compressedData);

            return "storage/receipts/{$tenantId}/{$filename}";
        } catch (\Throwable $e) {
            Log::error("Failed to compress and store image receipt: " . $e->getMessage());
            return null;
        }
    }
}
