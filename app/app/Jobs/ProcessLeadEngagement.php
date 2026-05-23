<?php

namespace App\Jobs;

use App\Models\Lead;
use App\Models\Message;
use App\Services\LeadCaptureService;
use App\Services\LeadIntelligenceService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessLeadEngagement implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(
        private int $tenantId,
        private string $phone,
        private string $messageContent
    ) {}

    /**
     * Execute the job.
     */
    public function handle(
        LeadCaptureService $capture,
        LeadIntelligenceService $intel
    ): void {
        // Enforce tenant scoping inside the background queue worker
        app()->instance('tenant_id', $this->tenantId);

        try {
            // 1. Gather context details (short term history)
            $shortTerm = Message::where('phone', $this->phone)
                ->orderByDesc('created_at')
                ->limit(15)
                ->get()
                ->reverse()
                ->map(fn($m) => [
                    'role'    => $m->role,
                    'content' => $m->content,
                ])
                ->values()
                ->toArray();

            // Count total user messages to calculate conversation depth
            $msgCount = Message::where('phone', $this->phone)
                ->where('role', 'user')
                ->count();

            // 2. Perform AI intelligence analysis (Intent, Mood, Summary, Extracted Name)
            $insights = $intel->analyzeEngagement($this->phone, $this->messageContent, $shortTerm);

            // 3. Perform regex checks for other missing contact info (email/phone)
            $extractedEmail = $capture->extractEmail($this->messageContent);
            $extractedPhone = $capture->extractPhone($this->messageContent);

            // 4. Retrieve or create lead record
            $lead = Lead::firstOrNew([
                'phone' => $this->phone,
            ]);

            // Fill details progressively if not already captured
            if (empty($lead->captured_name) && !empty($insights['name'])) {
                $lead->captured_name = $insights['name'];
            }
            if (empty($lead->captured_email) && !empty($extractedEmail)) {
                $lead->captured_email = $extractedEmail;
            }
            if (empty($lead->captured_phone) && !empty($extractedPhone)) {
                $lead->captured_phone = $extractedPhone;
            }

            // Calculate lead score
            $score = $intel->calculateLeadScore($insights['intent'], $insights['mood'], $msgCount);

            // Determine stage progression
            $currentStage = $lead->capture_stage ?? 'new';
            $newStage = $capture->determineStage($currentStage, $insights['intent'], $score);

            // Update lead record
            $lead->intent = $insights['intent'];
            $lead->mood = $insights['mood'];
            $lead->summary = $insights['summary'];
            $lead->lead_score = $score;
            $lead->capture_stage = $newStage;
            $lead->human_required = $insights['human_required'] || ($newStage === 'human_required');
            $lead->last_activity_at = now();
            $lead->save();

            Log::info("Lead captured successfully in background", [
                'phone' => $this->phone,
                'score' => $score,
                'stage' => $newStage,
            ]);

        } catch (\Throwable $e) {
            Log::error("Failed to process background lead engagement", [
                'error' => $e->getMessage(),
                'phone' => $this->phone,
            ]);
        }
    }
}
