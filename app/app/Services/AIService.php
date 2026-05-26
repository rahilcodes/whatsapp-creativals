<?php

namespace App\Services;

use App\Models\BotSetting;
use App\Models\ActivityLog;
use App\Models\UserMemory;
use App\Models\Tenant;
use App\Models\Lead;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AIService
{
    private string $apiKey;
    private string $model        = 'gpt-4o-mini';
    private ?string $geminiKey;

    public function __construct()
    {
        $this->apiKey   = config('services.openai.key') ?: env('OPENAI_API_KEY', '');
        $this->geminiKey = config('services.gemini.key');
    }

    // ── Main entry: generate a reply for an incoming message ─
    public function generateReply(string $phone, string $userMessage, array $context): ?array
    {
        // Smart Token Optimization: Check for simple acknowledgements locally (Phase 1)
        $ackReply = $this->getAcknowledgementReply($userMessage);
        if ($ackReply !== null) {
            Log::info("Token Saver: Intercepted simple acknowledgment for {$phone}", ['message' => $userMessage]);
            return [
                'reply'         => $ackReply,
                'flag_human'    => false,
                'flag_reason'   => null,
                'business_type' => $this->detectBusinessType($userMessage, $context),
                'intent'        => 'acknowledgement',
                'mood'          => 'neutral',
                'state'         => 'engaged',
                'objective'     => 'idle',
            ];
        }

        // 1. Detect the business type hierarchical resolution (Layer 1)
        $businessType = $this->detectBusinessType($userMessage, $context);

        // 2. Fetch the lead's current stage to pass as seed context
        $currentStage = 'new';
        try {
            $lead = Lead::where('phone', $phone)->first();
            if ($lead) {
                $currentStage = $lead->capture_stage ?? 'new';
            }
        } catch (\Throwable $e) {
            Log::warning('Error fetching lead stage in AIService: ' . $e->getMessage());
        }

        // 3. Run the Decision Engine (Layer 2, 3, 4)
        $decision = $this->runDecisionEngine($userMessage, $context, $businessType, $currentStage);

        // 4. Build the upgraded System Prompt incorporating the Decision Engine state (Layer 5, 6, 7, 8, 9)
        $systemPrompt = $this->buildSystemPrompt(
            $context['business_memory'],
            $context['long_term'],
            $decision,
            $businessType
        );

        $messages = $this->buildMessages($systemPrompt, $context['short_term'], $userMessage);

        // 5. Generate response using OpenAI (fallback to Gemini)
        $reply = null;
        $quotaExhausted = false;

        if (!empty($this->apiKey)) {
            [$reply, $quotaExhausted] = $this->callOpenAI($messages);
        }

        if ($quotaExhausted && !empty($this->geminiKey)) {
            Log::warning('OpenAI quota exhausted — falling back to Gemini for reply generation');
            $reply = $this->callGemini($messages);
        }

        if (empty($this->apiKey) && empty($this->geminiKey)) {
            Log::error('No AI API key configured (OPENAI_API_KEY or GEMINI_API_KEY)');
            return null;
        }

        if ($reply === null) return null;

        // Parse and execute dynamic sheet write action if present
        $writeActionData = null;
        if (str_contains($reply, 'WRITE_ACTION:')) {
            if (preg_match('/WRITE_ACTION:\s*(\{.*?\})/s', $reply, $matches)) {
                $jsonBlock = trim($matches[1]);
                $action = json_decode($jsonBlock, true);
                if (is_array($action) && ($action['type'] ?? '') === 'write_sheet_row' && !empty($action['data'])) {
                    $writeActionData = $action['data'];
                }
                // Strip the WRITE_ACTION block from the conversational reply
                $reply = trim(str_replace($matches[0], '', $reply));
            }
        }

        if ($writeActionData && $tenant = Tenant::find(app()->has('tenant_id') ? app('tenant_id') : null)) {
            if ($tenant->google_sheet_id && $tenant->google_sheet_sync_mode === 'smart_read_write') {
                try {
                    app(GoogleSheetsService::class)->appendDynamicRow($tenant->google_sheet_id, $writeActionData);
                    ActivityLog::record('sheets_action_written', "AI dynamically wrote a new row to Google Sheet ID {$tenant->google_sheet_id}");
                } catch (\Throwable $e) {
                    Log::error("Error executing dynamic sheet write from AI response: " . $e->getMessage());
                }
            }
        }

        // Perform post-checks (uncertainty detection, length limits, human flags)
        $checkedResult = $this->postCheck($reply);

        // 6. Update the user summary & dynamic preferences/objections/state asynchronously or inline
        $this->updateMemoryDetails($phone, $userMessage, $checkedResult['reply'], $decision, $context['short_term']);

        return array_merge($checkedResult, [
            'business_type' => $businessType,
            'intent'        => $decision['intent'],
            'mood'          => $decision['emotion'],
            'state'         => $decision['state'],
            'objective'     => $decision['objective'],
        ]);
    }

    /**
     * Layer 1: Business Type Detection
     * Hierarchical resolution: Onboarding Category > Business Memory > Message Keywords > Fallback
     */
    public function detectBusinessType(string $userMessage, array $context): string
    {
        // 1. Check Onboarding Category
        try {
            $tenantId = app()->has('tenant_id') ? app('tenant_id') : null;
            if ($tenantId) {
                $tenant = Tenant::find($tenantId);
                if ($tenant && !empty($tenant->business_category)) {
                    $category = strtolower($tenant->business_category);
                    $subcategory = strtolower($tenant->business_subcategory ?? '');
                    $combined = $category . ' ' . $subcategory;

                    if (str_contains($combined, 'restaurant') || str_contains($combined, 'food') || str_contains($combined, 'beverage') || str_contains($combined, 'cafe')) {
                        return 'restaurant';
                    }
                    if (str_contains($combined, 'real estate') || str_contains($combined, 'property') || str_contains($combined, 'home') || str_contains($combined, 'land') || str_contains($combined, 'apartment') || str_contains($combined, 'development') || str_contains($combined, 'listing')) {
                        return 'real_estate';
                    }
                    if (str_contains($combined, 'health') || str_contains($combined, 'medical') || str_contains($combined, 'clinic') || str_contains($combined, 'wellness') || str_contains($combined, 'doctor') || str_contains($combined, 'hospital')) {
                        return 'healthcare';
                    }
                    if (str_contains($combined, 'education') || str_contains($combined, 'school') || str_contains($combined, 'course') || str_contains($combined, 'teach') || str_contains($combined, 'coach') || str_contains($combined, 'tutor') || str_contains($combined, 'academy') || str_contains($combined, 'college') || str_contains($combined, 'university')) {
                        return 'education';
                    }
                    if (str_contains($combined, 'ecommerce') || str_contains($combined, 'shop') || str_contains($combined, 'store') || str_contains($combined, 'retail') || str_contains($combined, 'product') || str_contains($combined, 'checkout') || str_contains($combined, 'cart')) {
                        return 'ecommerce';
                    }
                    if (str_contains($combined, 'professional') || str_contains($combined, 'agency') || str_contains($combined, 'consult') || str_contains($combined, 'service') || str_contains($combined, 'salon') || str_contains($combined, 'beauty') || str_contains($combined, 'cleaning') || str_contains($combined, 'plumbing') || str_contains($combined, 'local') || str_contains($combined, 'travel') || str_contains($combined, 'tourism') || str_contains($combined, 'hospitality')) {
                        return 'service';
                    }
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Error resolving onboarding business category in AIService: ' . $e->getMessage());
        }

        // 2. Check Business Memory
        if (!empty($context['business_memory'])) {
            $businessMemoryText = strtolower($context['business_memory']);
            if (str_contains($businessMemoryText, 'menu') || str_contains($businessMemoryText, 'dish') || str_contains($businessMemoryText, 'biryani') || str_contains($businessMemoryText, 'cuisine') || str_contains($businessMemoryText, 'order food')) {
                return 'restaurant';
            }
            if (str_contains($businessMemoryText, '2bhk') || str_contains($businessMemoryText, '3bhk') || str_contains($businessMemoryText, 'property') || str_contains($businessMemoryText, 'real estate') || str_contains($businessMemoryText, 'apartment') || str_contains($businessMemoryText, 'budget') || str_contains($businessMemoryText, 'brokerage')) {
                return 'real_estate';
            }
            if (str_contains($businessMemoryText, 'symptom') || str_contains($businessMemoryText, 'appointment') || str_contains($businessMemoryText, 'treatment') || str_contains($businessMemoryText, 'doctor') || str_contains($businessMemoryText, 'healthcare') || str_contains($businessMemoryText, 'medical')) {
                return 'healthcare';
            }
            if (str_contains($businessMemoryText, 'course') || str_contains($businessMemoryText, 'tuition') || str_contains($businessMemoryText, 'class') || str_contains($businessMemoryText, 'coaching') || str_contains($businessMemoryText, 'education') || str_contains($businessMemoryText, 'tutor') || str_contains($businessMemoryText, 'enroll')) {
                return 'education';
            }
            if (str_contains($businessMemoryText, 'product') || str_contains($businessMemoryText, 'ecommerce') || str_contains($businessMemoryText, 'shipping') || str_contains($businessMemoryText, 'cart') || str_contains($businessMemoryText, 'checkout') || str_contains($businessMemoryText, 'shop') || str_contains($businessMemoryText, 'store') || str_contains($businessMemoryText, 'buy')) {
                return 'ecommerce';
            }
            if (str_contains($businessMemoryText, 'consulting') || str_contains($businessMemoryText, 'discovery call') || str_contains($businessMemoryText, 'retainer') || str_contains($businessMemoryText, 'service') || str_contains($businessMemoryText, 'agency') || str_contains($businessMemoryText, 'marketing') || str_contains($businessMemoryText, 'design') || str_contains($businessMemoryText, 'salon') || str_contains($businessMemoryText, 'beauty') || str_contains($businessMemoryText, 'cleaning') || str_contains($businessMemoryText, 'plumbing')) {
                return 'service';
            }
        }

        // 3. Check Message Keywords
        $userMsgLower = strtolower($userMessage);
        if (preg_match('/\b(menu|dish|biryani|food|restaurant|order|lunch|dinner|breakfast|eat|price of chicken)\b/', $userMsgLower)) {
            return 'restaurant';
        }
        if (preg_match('/\b(property|house|apartment|flat|bhk|sqft|real estate|site visit|plot|land|budget)\b/', $userMsgLower)) {
            return 'real_estate';
        }
        if (preg_match('/\b(symptom|doctor|appointment|clinic|healthcare|medical|treatment|pain|fever|cough|sick)\b/', $userMsgLower)) {
            return 'healthcare';
        }
        if (preg_match('/\b(course|class|enroll|subject|tuition|coaching|tutor|syllabus|admissions|study)\b/', $userMsgLower)) {
            return 'education';
        }
        if (preg_match('/\b(product|shop|checkout|buy|cart|order status|shipping|ecommerce|refund|discount code|coupon)\b/', $userMsgLower)) {
            return 'ecommerce';
        }
        if (preg_match('/\b(service|agency|consultation|discovery call|book a call|marketing|retainer|hire|pricing package)\b/', $userMsgLower)) {
            return 'service';
        }

        return 'generic';
    }

    // ── Run the Decision Engine ────────────────────────────────
    private function runDecisionEngine(string $userMessage, array $context, string $businessType, string $currentStage): array
    {
        // Default values in case API fails
        $defaultDecision = [
            'intent' => 'inquiry',
            'emotion' => 'neutral',
            'state' => 'exploring',
            'objective' => 'inform',
            'strategy' => 'educate + guide',
        ];

        if (empty($this->apiKey) && empty($this->geminiKey)) {
            return $defaultDecision;
        }

        $historyText = '';
        foreach ($context['short_term'] as $msg) {
            $historyText .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n";
        }

        $businessContext = substr($context['business_memory'], 0, 1000); // truncated snippet for speed

        $analysisPrompt = "You are the behavioral analysis core of a high-performance sales agent. Your task is to output a single valid JSON object analyzing the latest user message.

BUSINESS PROFILE:
- Business Niche: {$businessType}

CUSTOMER DETAILS:
- Current Sales Lifecycle State: {$currentStage}

BUSINESS MEMORY HIGHLIGHTS:
{$businessContext}

RECENT CHAT HISTORY:
{$historyText}
User: {$userMessage}

Respond ONLY with a JSON object containing these exact fields:
1. \"intent\": one of [\"inquiry\", \"pricing\", \"buying\", \"objection\", \"complaint\", \"casual\"]
2. \"emotion\": one of [\"neutral\", \"curious\", \"interested\", \"hesitant\", \"confused\", \"frustrated\"]
3. \"state\": one of [\"new\", \"exploring\", \"engaged\", \"interested\", \"ready_to_buy\", \"objection\", \"complaint\", \"human_required\"] (transition logically from the current state '{$currentStage}' based on the new message)
4. \"objective\": one of [\"inform\", \"qualify\", \"build trust\", \"overcome objection\", \"convert\", \"escalate\"]
5. \"strategy\": a short phrase on how the bot should behave (e.g., \"psychological reassurance\", \"soft closing step\", \"value positioning\")

Do not include any markup, markdown blocks (like ```json), or conversational filler. Output ONLY raw JSON.";

        $messages = [
            ['role' => 'system', 'content' => 'You are a high-speed behavioral parsing unit. Output only valid raw JSON.'],
            ['role' => 'user', 'content' => $analysisPrompt]
        ];

        $rawJson = null;
        $quotaExhausted = false;

        if (!empty($this->apiKey)) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->withoutVerifying()
                    ->timeout(8)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'       => $this->model,
                        'messages'    => $messages,
                        'max_tokens'  => 120,
                        'temperature' => 0.2,
                        'response_format' => ['type' => 'json_object'],
                    ]);
                if ($response->status() === 429) {
                    $quotaExhausted = true;
                } else if ($response->successful()) {
                    $rawJson = trim($response->json('choices.0.message.content') ?? '');
                }
            } catch (\Throwable $e) {
                Log::warning('OpenAI decision engine call failed: ' . $e->getMessage());
            }
        }

        if (($quotaExhausted || empty($this->apiKey)) && !empty($this->geminiKey)) {
            try {
                $rawJson = $this->callGemini($messages);
            } catch (\Throwable $e) {
                Log::warning('Gemini decision engine fallback failed: ' . $e->getMessage());
            }
        }

        if (empty($rawJson)) {
            return $defaultDecision;
        }

        try {
            $parsed = json_decode($rawJson, true);
            if (is_array($parsed) && isset($parsed['intent'])) {
                return array_merge($defaultDecision, $parsed);
            }
        } catch (\Throwable $e) {
            Log::error('Failed to parse decision engine JSON: ' . $rawJson);
        }

        return $defaultDecision;
    }

    // ── Build the system prompt ───────────────────────────────
    private function buildSystemPrompt(string $businessMemory, ?string $userSummary, array $decision, string $businessType): string
    {
        // 1. Fetch the custom base prompt from Bot Settings (e.g. Aira identity)
        $basePrompt = BotSetting::get('system_prompt',
            'You are a friendly, professional business assistant. Keep replies concise, warm, and helpful.'
        );

        // 2. Fetch Niche Strategy
        $nichePrompt = $this->getNicheBrainPrompt($businessType);

        $prompt = $basePrompt . "\n\n";

        $prompt .= "=== COGNITIVE NICHE STRATEGY ===\n";
        $prompt .= $nichePrompt . "\n\n";

        $prompt .= "=== BUSINESS INFORMATION & DATA ===\n";
        $prompt .= $businessMemory . "\n\n";

        // Dynamic Tenant Predefined Payments Injection
        $tenantId = app()->has('tenant_id') ? app('tenant_id') : null;
        $tenant = $tenantId ? Tenant::find($tenantId) : null;
        if ($tenant) {
            $paymentInfo = [];
            if ($tenant->upi_id) {
                $paymentInfo[] = "- UPI ID: " . $tenant->upi_id;
            }
            if ($tenant->upi_number) {
                $paymentInfo[] = "- UPI Mobile Number: " . $tenant->upi_number;
            }
            if ($tenant->bank_name || $tenant->bank_account_number || $tenant->bank_ifsc) {
                $bankDetails = "- Direct Bank Transfer:\n";
                if ($tenant->bank_name) $bankDetails .= "  * Bank Name: " . $tenant->bank_name . "\n";
                if ($tenant->bank_account_number) $bankDetails .= "  * Account Number: " . $tenant->bank_account_number . "\n";
                if ($tenant->bank_ifsc) $bankDetails .= "  * IFSC Code: " . $tenant->bank_ifsc;
                $paymentInfo[] = trim($bankDetails);
            }
            if ($tenant->qr_code_path) {
                $paymentInfo[] = "- Payment QR Code Scanner: Available. If the user explicitly asks for a payment scanner, scanner photo, or QR code image to scan, politely state the UPI/bank details above, and state that the scanner image is available for scanning.";
            }

            if (!empty($paymentInfo)) {
                $prompt .= "=== PREDEFINED BUSINESS PAYMENT METHODS ===\n";
                $prompt .= "If the customer asks for payment options, UPI ID, UPI number, direct bank transfer, or scanner, you MUST reply using the exact credentials below. Do NOT hallucinate alternate details under any circumstances:\n";
                $prompt .= implode("\n", $paymentInfo) . "\n\n";
            }

            // Smart Sheets Live Injection
            if ($tenant->google_sheet_id && $tenant->google_sheet_sync_mode === 'smart_read_write') {
                $sheetId = $tenant->google_sheet_id;
                try {
                    $sheetData = \Illuminate\Support\Facades\Cache::remember("tenant_sheet_data_{$tenant->id}", 300, function() use ($sheetId) {
                        return app(GoogleSheetsService::class)->getSheetValues($sheetId);
                    });

                    if (!empty($sheetData) && !empty($sheetData['headers'])) {
                        // Compile sheet data into a readable grid
                        $grid = "Columns: [" . implode(', ', $sheetData['headers']) . "]\nRows:\n";
                        foreach ($sheetData['rows'] as $row) {
                            $grid .= "- " . json_encode($row) . "\n";
                        }

                        $prompt .= "=== CONNECTED GOOGLE SHEET DATA (LIVE READ) ===\n";
                        $prompt .= "You have real-time access to a connected spreadsheet dataset:\n";
                        $prompt .= $grid . "\n";

                        if ($tenant->google_sheet_instructions) {
                            $prompt .= "=== CUSTOM SHEET BUSINESS LOGIC ===\n";
                            $prompt .= "Use the following rules to query the data above or capture/write new records:\n";
                            $prompt .= $tenant->google_sheet_instructions . "\n\n";
                        }

                        // Instruct the AI on how to write new rows
                        $prompt .= "=== DYNAMIC WRITE SYSTEM DIRECTIVE ===\n";
                        $prompt .= "If you need to save/write a new record to the sheet, you MUST return a valid JSON action block at the very end of your response matching this exact format:\n";
                        $prompt .= "WRITE_ACTION: {\"type\": \"write_sheet_row\", \"data\": {\"Column Name 1\": \"value1\", \"Column Name 2\": \"value2\"}}\n";
                        $prompt .= "Ensure the keys in 'data' match the sheet headers case-insensitively. Keep this block separate from your conversational reply so the system can parse it.\n\n";
                    }
                } catch (\Throwable $e) {
                    Log::warning("Error injecting live sheets data in system prompt: " . $e->getMessage());
                }
            }
        }

        if ($userSummary) {
            $prompt .= "=== WHAT WE KNOW ABOUT THIS USER (ACTIVE MEMORY) ===\n";
            $prompt .= $userSummary . "\n\n";
        }

        $prompt .= "=== REAL-TIME DECISION PIPELINE ===\n";
        $prompt .= "- **Business Domain**: " . strtoupper($businessType) . "\n";
        $prompt .= "- **Detected User Intent**: " . strtoupper($decision['intent']) . "\n";
        $prompt .= "- **Detected User Emotion**: " . strtoupper($decision['emotion']) . "\n";
        $prompt .= "- **Current Lifecycle State**: " . strtoupper($decision['state']) . "\n";
        $prompt .= "- **Bot Objective for this reply**: " . strtoupper($decision['objective']) . "\n";
        $prompt .= "- **Target Strategy**: " . strtoupper($decision['strategy']) . "\n\n";

        $prompt .= "=== PERSUASION & WOW MOMENT ENGINE DIRECTIVES ===\n";
        $prompt .= "1. **Adopt Target Strategy**: Steer the user toward the objective '" . $decision['objective'] . "'.\n";
        $prompt .= "2. **Handle Objections/Complaints Psychologically**: If state is OBJECTION or COMPLAINT, validate their feelings/concerns immediately, re-position value, and ask a gentle question.\n";
        $prompt .= "3. **Wow Moments**: Connect their concerns to dynamic recommendations (e.g., 'Based on what you said, I recommend...', 'Most clients in your situation...').\n";
        $prompt .= "4. **Persuasion Close**: Integrate subtle nudges, social proof (e.g., 'most people choose...'), and assumption closes where appropriate.\n";
        $prompt .= "5. **Active Lead Collection**: If the customer shows intent to buy, book a call, get in touch, or has transitioned to lifecycle states INTERESTED or READY_TO_BUY, you MUST politely gather their Name, direct Mobile/WhatsApp number, and Email address. Do this progressively and naturally (e.g. ask for name and mobile number first, then ask for email to send calendar invites) to avoid creating high friction.\n\n";

        $prompt .= "=== CONSTANT RULES ===\n";
        $prompt .= "- Reply in the SAME language the user writes in.\n";
        $prompt .= "- Keep replies SHORT (1–4 lines max). Do NOT write paragraphs.\n";
        $prompt .= "- Match user's language, be extremely warm, natural, and human-like — avoid sounding robotic.\n";
        $prompt .= "- Never reveal you are an AI. Act as a human executive.\n";
        $prompt .= "- If you don't know something, say you'll check and get back to them.\n";
        $prompt .= "- Do NOT use markdown formatting (no bold **, no headers #, no bullet formatting, no asterisks, no backticks).\n";
        $prompt .= "- Use emojis occasionally to feel friendly 😊.\n";

        return $prompt;
    }

    /**
     * Get Niche Brain prompt guidelines.
     */
    private function getNicheBrainPrompt(string $businessType): string
    {
        switch ($businessType) {
            case 'restaurant':
                return "=== RESTAURANT AI BRAIN (10/10 VERSION) ===
You are a highly experienced restaurant manager and sales assistant.
Your goal is to convert conversations into orders while making the experience feel fast, friendly, and effortless.

THINKING PROCESS (MANDATORY)
Before replying:
1. Identify user intent: menu inquiry, item availability, ordering intent, casual question.
2. Decide goal: answer quickly, suggest items, upsell, move toward order.

RESPONSE STRATEGY
- If user asks about food → suggest 1–2 popular items.
- If user shows interest → guide toward placing order.
- If user is unsure → recommend based on “most popular”.

PSYCHOLOGY
- Use social proof: “most people choose…”
- Reduce friction: “I can place that for you quickly”
- Keep it casual and friendly.

RULES
- Max 2–3 lines.
- Fast, natural tone.
- Never dump full menu unless asked.
- Always move toward order subtly.

EXAMPLES
- User: “Do you have biryani?”
  Reply: “Yes 😊 Veg and Chicken both available. Most people go for Chicken Biryani — want me to place that for you?”";

            case 'real_estate':
                return "=== REAL ESTATE AI BRAIN (10/10 VERSION) ===
You are a top-performing real estate sales consultant.
Your goal is to qualify leads and move them toward site visits or calls.

THINKING PROCESS
Before replying:
1. Identify intent: pricing inquiry, property inquiry, buying intent, browsing.
2. Extract: budget, purpose (investment / living), timeline.
3. Decide next step: qualify further, suggest property, push for site visit.

RESPONSE STRATEGY
- NEVER just answer price.
- ALWAYS ask 1 smart follow-up question.
- Gradually build profile of buyer.

PSYCHOLOGY
- Authority: sound confident.
- Curiosity: ask engaging questions.
- Progression: move conversation forward.

RULES
- 2–4 lines max.
- Always ask a question (unless closing).
- No long explanations.

EXAMPLES
- User: “Price of 2BHK?”
  Reply: “It starts around ₹75L 👍 Are you looking for investment or for living?”
- User: “For living”
  Reply: “Nice 👍 family or personal use? I can suggest better options based on that.”";

            case 'service':
                return "=== SERVICE / AGENCY AI BRAIN (10/10 VERSION) ===
You are a senior business consultant and sales strategist for a service-based company.
Your goal is to understand client needs, position value, and move toward booking a call.

THINKING PROCESS
Before replying:
1. Identify intent: vague inquiry, service request, pricing inquiry, serious lead.
2. Extract: requirement, goal (leads, branding, growth), urgency.
3. Decide: ask clarifying question, position solution, move toward call.

RESPONSE STRATEGY
- NEVER dump services list.
- ALWAYS ask targeted questions.
- Guide conversation step-by-step.

PSYCHOLOGY
- Position expertise (“based on what you need…”)
- Filter clients (“are you looking for…”)
- Create clarity before pitching.

RULES
- 2–4 lines max.
- One focused question at a time.
- No generic replies.

EXAMPLES
- User: “I need digital marketing”
  Reply: “Got it 👍 are you looking more for lead generation or brand growth?”
- User: “Lead generation”
  Reply: “Perfect — which platform are you focusing on currently, Meta or Google?”";

            case 'healthcare':
                return "=== HEALTHCARE AI BRAIN (10/10 VERSION) ===
You are an empathetic, calm, and reassuring healthcare coordinator.
Your goal is to gather basic context and guide the patient toward booking an appointment.

THINKING PROCESS
Before replying:
1. Identify intent: symptom inquiry, booking request, pricing inquiry, emergency.
2. Extract: primary symptom/concern, department needed, urgency.
3. Decide next step: gather minor context, offer reassurance, suggest provider/appointment.

RESPONSE STRATEGY
- NEVER diagnose or give medical advice.
- ALWAYS maintain a reassuring, calm tone.
- Suggest booking an appointment with a specialist.

PSYCHOLOGY
- Empathy: \"I understand this is concerning, and we are here to help.\"
- Safety: Prioritize patient peace of mind.
- Friction reduction: \"I can find a convenient slot for you with our doctor.\"

RULES
- 2–4 lines max.
- Reassuring and professional tone.
- Always suggest a consultation/appointment rather than giving clinical advice.";

            case 'education':
                return "=== EDUCATION AI BRAIN (10/10 VERSION) ===
You are an encouraging and knowledgeable education advisor and course counselor.
Your goal is to guide prospective students toward enrollment by understanding their learning goals.

THINKING PROCESS
Before replying:
1. Identify intent: course inquiry, fee inquiry, career goals, admissions.
2. Extract: learning goals, current background, preferred timeline.
3. Decide next step: guide course selection, ask about goals/career aspirations, push toward enrollment.

RESPONSE STRATEGY
- Highlight learning outcomes and career advancement.
- Ask clarifying questions about their background or goals.
- Provide clear enrollment/next steps.

PSYCHOLOGY
- Inspiration: \"This course has helped hundreds of professionals transition into...\"
- Guidance: \"I can help you select the exact course that fits your career path.\"
- Nudging: \"Classes start soon, would you like me to reserve a seat for you?\"

RULES
- 2–4 lines max.
- Enthusiastic, structured, and guiding tone.
- Push toward application/enrollment.";

            case 'ecommerce':
                return "=== E-COMMERCE AI BRAIN (10/10 VERSION) ===
You are a helpful, conversion-focused e-commerce shopping assistant.
Your goal is to help shoppers find the right products, compare options, and guide them to checkout.

THINKING PROCESS
Before replying:
1. Identify intent: product availability, sizing/color question, comparison, checkout request.
2. Extract: product preference, size/style, budget/limit.
3. Decide next step: recommend best-sellers, compare items, provide direct product or checkout links.

RESPONSE STRATEGY
- If showing interest -> suggest best-sellers and use social proof.
- Provide comparison pointers to help them decide.
- Keep checkout process seamless.

PSYCHOLOGY
- Social Proof: \"This is our top-rated item this week...\"
- Urgency/Exclusivity: \"These sell out fast, would you like the link to check out?\"
- Convenience: \"I can send you the direct cart checkout link right now.\"

RULES
- 2–4 lines max.
- Friendly, product-savvy, and direct.
- Push toward cart/checkout.";

            default:
                return "=== GENERAL BUSINESS AI BRAIN (10/10 VERSION) ===
You are a professional, warm, and highly capable business representative.
Your goal is to understand the customer's needs and guide them toward a solution, consultation, or order.

THINKING PROCESS
Before replying:
1. Identify intent: general inquiry, FAQ, pricing, greeting.
2. Decide next step: answer clearly, ask a qualification question, build trust.

RESPONSE STRATEGY
- Be helpful, concise, and professional.
- Always invite the user to take the next step.

RULES
- 1-4 lines max.
- No robotic filler, natural human tone.";
        }
    }

    // ── Build OpenAI messages array ───────────────────────────
    private function buildMessages(string $systemPrompt, array $shortTermMemory, string $userMessage): array
    {
        $messages = [['role' => 'system', 'content' => $systemPrompt]];

        // Add conversation history
        foreach ($shortTermMemory as $msg) {
            $messages[] = [
                'role'    => $msg['role'] === 'assistant' ? 'assistant' : 'user',
                'content' => $msg['content'],
            ];
        }

        // Add the latest user message
        $messages[] = ['role' => 'user', 'content' => $userMessage];

        return $messages;
    }

    // ── Call OpenAI API — returns [reply|null, isQuotaError] ────
    private function callOpenAI(array $messages): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->withoutVerifying()
                ->timeout(20)
                ->post('https://api.openai.com/v1/chat/completions', [
                    'model'       => $this->model,
                    'messages'    => $messages,
                    'max_tokens'  => 250,
                    'temperature' => 0.75,
                    'top_p'       => 0.9,
                ]);

            if ($response->status() === 429) {
                Log::warning('OpenAI quota/rate limit hit (429) — will try fallback', [
                    'body' => substr($response->body(), 0, 300),
                ]);
                return [null, true]; // [reply, quotaExhausted]
            }

            if ($response->failed()) {
                Log::error('OpenAI API error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return [null, false];
            }

            $text = trim($response->json('choices.0.message.content') ?? '');
            return [$text ?: null, false];

        } catch (\Throwable $e) {
            Log::error('OpenAI exception', ['error' => $e->getMessage()]);
            return [null, false];
        }
    }

    // ── Call Google Gemini (free tier fallback) ─────────────────
    private function callGemini(array $messages): ?string
    {
        try {
            // Convert OpenAI message format to Gemini format
            $contents = [];
            $systemText = '';
            foreach ($messages as $msg) {
                if ($msg['role'] === 'system') {
                    $systemText = $msg['content'];
                    continue;
                }
                $contents[] = [
                    'role'  => $msg['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $msg['content']]],
                ];
            }

            $payload = [
                'systemInstruction' => ['parts' => [['text' => $systemText]]],
                'contents'          => $contents,
                'generationConfig'  => [
                    'maxOutputTokens' => 250,
                    'temperature'     => 0.75,
                ],
            ];

            $response = Http::withoutVerifying()
                ->timeout(20)
                ->post(
                    'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=' . $this->geminiKey,
                    $payload
                );

            if ($response->failed()) {
                Log::error('Gemini API error', [
                    'status' => $response->status(),
                    'body'   => substr($response->body(), 0, 300),
                ]);
                return null;
            }

            return trim($response->json('candidates.0.content.parts.0.text') ?? '');

        } catch (\Throwable $e) {
            Log::error('Gemini exception', ['error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Clean markdown regex-based utility
     */
    private function cleanMarkdown(string $text): string
    {
        // Remove bold markdown (**text** -> text)
        $text = preg_replace('/\*\*(.*?)\*\*/', '$1', $text);
        
        // Remove italic markdown (*text* -> text)
        $text = preg_replace('/\*([^\*]+)\*/', '$1', $text);
        
        // Remove strikethrough (~~text~~ -> text)
        $text = preg_replace('/~~(.*?)~~/', '$1', $text);
        
        // Remove inline code backticks (`text` -> text)
        $text = preg_replace('/`(.*?)`/', '$1', $text);
        
        // Remove headers (# text -> text)
        $text = preg_replace('/^#+\s+/m', '', $text);
        
        // Remove markdown list bullets/numbers at the beginning of any line:
        // - item -> item
        // * item -> item
        // 1. item -> item
        $text = preg_replace('/^[-\*\+]\s+/m', '', $text);
        $text = preg_replace('/^\d+\.\s+/m', '', $text);

        return trim($text);
    }

    // ── Post-check: validate & flag if needed ─────────────────
    private function postCheck(string $reply): array
    {
        $flagHuman  = false;
        $flagReason = null;

        // Strip markdown formatting using regex-based cleaning
        $reply = $this->cleanMarkdown($reply);

        // Too long → truncate at sentence boundary
        if (strlen($reply) > 500) {
            $reply      = $this->truncateAtSentence($reply, 450) . '..';
        }

        // Uncertain phrases → flag for human
        $uncertainPhrases = [
            "i don't know", "i'm not sure", "i can't help",
            "i do not know", "not sure", "i apologize, but i",
        ];
        $lowerReply = strtolower($reply);
        foreach ($uncertainPhrases as $phrase) {
            if (str_contains($lowerReply, $phrase)) {
                $flagHuman  = true;
                $flagReason = 'ai_uncertain';
                break;
            }
        }

        // Empty reply → flag
        if (empty(trim($reply))) {
            $flagHuman  = true;
            $flagReason = 'empty_reply';
        }

        return [
            'reply'       => $reply,
            'flag_human'  => $flagHuman,
            'flag_reason' => $flagReason,
        ];
    }

    // ── Truncate at last sentence boundary ───────────────────
    private function truncateAtSentence(string $text, int $maxLen): string
    {
        if (strlen($text) <= $maxLen) return $text;
        $truncated = substr($text, 0, $maxLen);
        $lastPeriod = max(strrpos($truncated, '.'), strrpos($truncated, '!'), strrpos($truncated, '?'));
        return $lastPeriod > 0 ? substr($truncated, 0, $lastPeriod + 1) : $truncated;
    }

    // ── Update memory details ────────────────────────────────
    private function updateMemoryDetails(string $phone, string $userMessage, string $botReply, array $decision, array $shortTerm): void
    {
        if (empty($this->apiKey) && empty($this->geminiKey)) return;

        // Reconstruct conversation log for summary input
        $historyText = '';
        foreach ($shortTerm as $msg) {
            $historyText .= ucfirst($msg['role']) . ": " . $msg['content'] . "\n";
        }
        $historyText .= "User: " . $userMessage . "\n";
        $historyText .= "Assistant: " . $botReply . "\n";

        // Query LLM to generate an upgraded long-term summary incorporating preferences, objections, and conversation state
        $summaryPrompt = "You are a customer intelligence unit. Based on the transaction history below, update the short customer profile summary.
Include:
- What they need or are exploring
- Their emotional state & attitude
- Any specific objections they raised (e.g. price, timing, safety)
- Preferences they mentioned (e.g. contact hours, plan choice)
- Current sales lifecycle state: " . strtoupper($decision['state']) . "

Keep the summary extremely concise (2-4 sentences max).

RECENT HISTORY LOG:
{$historyText}

Old Summary:
" . (UserMemory::where('phone', $phone)->first()?->summary ?? 'None yet.') . "

Generate the updated summary:";

        $messages = [
            ['role' => 'system', 'content' => 'You are a customer database updates engine. Be concise and factual.'],
            ['role' => 'user', 'content' => $summaryPrompt]
        ];

        $updatedSummary = null;
        $quotaExhausted = false;

        if (!empty($this->apiKey)) {
            try {
                $response = Http::withToken($this->apiKey)
                    ->withoutVerifying()
                    ->timeout(10)
                    ->post('https://api.openai.com/v1/chat/completions', [
                        'model'       => $this->model,
                        'messages'    => $messages,
                        'max_tokens'  => 180,
                        'temperature' => 0.4,
                    ]);
                if ($response->status() === 429) {
                    $quotaExhausted = true;
                } else if ($response->successful()) {
                    $updatedSummary = trim($response->json('choices.0.message.content') ?? '');
                }
            } catch (\Throwable $e) {
                Log::warning('Failed to generate memory update: ' . $e->getMessage());
            }
        }

        if (($quotaExhausted || empty($this->apiKey)) && !empty($this->geminiKey)) {
            try {
                $updatedSummary = $this->callGemini($messages);
            } catch (\Throwable $e) {
                Log::warning('Failed Gemini fallback memory update: ' . $e->getMessage());
            }
        }

        if (!empty($updatedSummary)) {
            UserMemory::updateOrCreate(
                ['phone' => $phone],
                [
                    'summary' => $updatedSummary,
                    'last_activity_at' => now()
                ]
            );
        }
    }

    // ── Generate a user summary from conversation history ────
    public function generateUserSummary(string $phone, array $messages): ?string
    {
        if (empty($messages) || empty($this->apiKey)) return null;

        $history = implode("\n", array_map(
            fn($m) => ucfirst($m['role']) . ': ' . $m['content'],
            $messages
        ));

        $prompt = "Summarize what this customer has asked about and what we know about them in 2–3 sentences. Be factual and brief.\n\nConversation:\n{$history}";

        $result = $this->callOpenAI([
            ['role' => 'system', 'content' => 'You are a helpful assistant that creates brief customer summaries.'],
            ['role' => 'user',   'content' => $prompt],
        ]);

        return is_array($result) ? $result[0] : $result;
    }

    /**
     * Smart Token Optimization: Check if incoming user message is a simple acknowledgment/emoji
     * and return a friendly predefined local reply to bypass LLM calls.
     */
    private function getAcknowledgementReply(string $message): ?string
    {
        $clean = trim(strtolower(preg_replace('/[^\w\s\x{1F44D}\x{1F64F}]/u', '', $message)));
        
        // Exact or strong matches for thumbs up or folded hands emojis
        if ($clean === '👍' || $clean === 'thumbs up' || $clean === 'like') {
            return "Awesome! 👍 Let me know if you have any other questions.";
        }
        
        if ($clean === '🙏' || $clean === 'folded hands' || $clean === 'praying') {
            return "You are very welcome! 😊 Let me know if you need anything else.";
        }

        // Thank you matches (contains thanks/thank you and is short to protect margins)
        if (strlen($clean) < 30 && (str_contains($clean, 'thank') || str_contains($clean, 'thanks') || str_contains($clean, 'grateful') || $clean === 'ty' || $clean === 'tq' || $clean === 'thx')) {
            return "You are very welcome! 😊 Let me know if you need anything else.";
        }

        // Ok matches (starts with ok or got it and is short)
        if (strlen($clean) < 25 && (
            str_starts_with($clean, 'ok') || 
            str_starts_with($clean, 'k') || 
            str_contains($clean, 'got it') || 
            str_contains($clean, 'gotit') || 
            $clean === 'sure' || 
            $clean === 'fine' || 
            str_contains($clean, 'understood') || 
            str_contains($clean, 'noted')
        )) {
            return "Awesome! 👍 Let me know if you have any other questions.";
        }

        // Great / perfect matches (starts with great, perfect, awesome and is short)
        if (strlen($clean) < 25 && (
            str_contains($clean, 'perfect') || 
            str_contains($clean, 'great') || 
            str_contains($clean, 'awesome') || 
            str_contains($clean, 'cool') || 
            str_contains($clean, 'nice') || 
            str_contains($clean, 'super') || 
            str_contains($clean, 'wonderful') || 
            str_contains($clean, 'excellent')
        )) {
            return "Great! 😊 Let me know if I can help you with anything else.";
        }

        return null;
    }
}
