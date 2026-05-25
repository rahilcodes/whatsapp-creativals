<?php

namespace App\Http\Controllers;

use App\Models\BotSetting;
use App\Models\BusinessMemory;
use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OnboardingController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        // If user has already onboarded, redirect straight to dashboard
        if ($user->onboarded) {
            return redirect()->route('dashboard');
        }

        return view('auth.onboarding', [
            'user' => $user,
        ]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->onboarded) {
            return response()->json(['success' => true, 'redirect' => route('dashboard')]);
        }

        // Validate basic parameters
        $rules = [
            'account_type' => 'required|in:business,personal',
            'ai_name' => 'required|string|max:255',
            'ai_tone' => 'required|in:professional,warm,casual,sales',
            'greeting_message' => 'required|string',
        ];

        if ($request->input('account_type') === 'business') {
            $rules['business_name'] = 'required|string|max:255';
            $rules['category'] = 'required|string|max:255';
            $rules['subcategory'] = 'nullable|string|max:255';
            $rules['business_description'] = 'nullable|string';
        } else {
            $rules['personal_name'] = 'required|string|max:255';
            $rules['personal_role'] = 'required|string|max:255';
            $rules['personal_description'] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        // Fetch current tenant
        $tenantId = app('tenant_id');
        $tenant = Tenant::find($tenantId);

        if (!$tenant) {
            return response()->json(['error' => 'Tenant context not found.'], 404);
        }

        // Wrap database writes in transaction
        DB::transaction(function () use ($request, $user, $tenant, $validated) {
            $accountType = $validated['account_type'];

            // 1. Update Tenant Info
            if ($accountType === 'business') {
                $tenant->name = $validated['business_name'];
                $tenant->account_type = 'business';
                $tenant->business_category = $validated['category'];
                $tenant->business_subcategory = $validated['subcategory'] ?? null;
                $tenant->save();

                // Seed Niche Specific Business Memory templates
                $this->seedBusinessTemplates($tenant->id, $validated['category'], $validated['business_name']);
            } else {
                $tenant->name = $validated['personal_name'];
                $tenant->account_type = 'personal';
                $tenant->business_category = $validated['personal_role'] ?? null;
                $tenant->business_subcategory = null;
                $tenant->save();

                // Update User name
                $user->name = $validated['personal_name'];
                $user->save();

                // Seed personal branding templates
                $this->seedPersonalTemplates($tenant->id, $validated['personal_role'], $validated['personal_name']);
            }

            // 2. Generate and Set Bot System Prompt
            $systemPrompt = $this->buildSystemPrompt(
                $accountType,
                $validated['ai_name'],
                $validated['ai_tone'],
                $accountType === 'business' ? $validated['business_name'] : $validated['personal_name'],
                $accountType === 'business' ? $request->input('business_description') : $request->input('personal_description'),
                $accountType === 'business' ? $validated['category'] : ($validated['personal_role'] ?? 'Personal Brand')
            );

            BotSetting::set('system_prompt', $systemPrompt);
            BotSetting::set('bot_enabled', '1');

            // 3. Mark User as Onboarded
            $user->onboarded = true;
            $user->save();

            // 4. Start 7-day free trial from onboarding completion
            //    (only set once — never reset it on re-onboarding)
            if (!$tenant->trial_ends_at) {
                $tenant->trial_ends_at       = now()->addDays(7);
                $tenant->subscription_status = 'trialing';
                $tenant->save();
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Onboarding configurations saved successfully.'
        ]);
    }

    public function skip()
    {
        $user = Auth::user();
        $user->onboarded = true;
        $user->save();

        return redirect()->route('dashboard');
    }

    /**
     * Seeds default template memories for the selected business category.
     */
    private function seedBusinessTemplates(int $tenantId, string $category, string $businessName): void
    {
        // Delete existing memories to avoid duplicates
        BusinessMemory::where('tenant_id', $tenantId)->delete();

        $templates = [
            'Retail & E-commerce' => [
                ['category' => 'services', 'key' => 'Primary Offerings', 'value' => "We offer premium products under {$businessName}, including fashion apparel, consumer electronics, and quality home goods."],
                ['category' => 'pricing', 'key' => 'Shipping Policy', 'value' => 'Free standard shipping on orders above $50. Deliveries generally take 3 to 5 business days.'],
                ['category' => 'faqs', 'key' => 'Return & Refund Policy', 'value' => 'We accept returns within 30 days of purchase. Items must be unused and in their original packaging.'],
                ['category' => 'hours', 'key' => 'Customer Support Hours', 'value' => 'Our online shop is open 24/7. Live customer service is available daily from 9:00 AM to 6:00 PM.'],
            ],
            'Food & Beverage (Restaurants, Cafes)' => [
                ['category' => 'services', 'key' => 'Cuisine & Dining', 'value' => "Welcome to {$businessName}. We serve fresh, locally sourced dishes and specialty coffee brewed by expert baristas."],
                ['category' => 'pricing', 'key' => 'Average Spend', 'value' => 'Main courses average between $12 and $28. Daily lunch specials start at $10.'],
                ['category' => 'faqs', 'key' => 'Reservations', 'value' => 'Reservations can be made online for groups of 4 or more. Walk-ins are always accommodated where possible.'],
                ['category' => 'hours', 'key' => 'Operating Hours', 'value' => 'Open Tuesday to Sunday, 11:30 AM to 10:00 PM. Closed on Mondays.'],
            ],
            'Real Estate & Property' => [
                ['category' => 'services', 'key' => 'Real Estate Services', 'value' => "At {$businessName}, we specialize in residential home sales, commercial real estate listings, and rental management."],
                ['category' => 'pricing', 'key' => 'Brokerage Consultations', 'value' => 'Property consultations and valuations are complimentary. Commission rates are tailored individually.'],
                ['category' => 'faqs', 'key' => 'Booking Showings', 'value' => 'Showings are booked with 24 hours advance notice. We verify all buyers are pre-approved prior to showing listings.'],
                ['category' => 'hours', 'key' => 'Office Hours', 'value' => 'Our agents are available Monday to Friday from 9:00 AM to 6:00 PM, and Saturdays by appointment.'],
            ],
            'Healthcare & Wellness' => [
                ['category' => 'services', 'key' => 'Treatments & Services', 'value' => "We offer medical consults, personal training, spa therapy, and yoga/pilates classes designed for health improvement."],
                ['category' => 'pricing', 'key' => 'Pricing Packages', 'value' => 'Individual treatment sessions start at $70. Multi-session packages are discounted by 15%.'],
                ['category' => 'faqs', 'key' => 'Cancellation Policy', 'value' => 'Please provide at least 24 hours notice for all cancellations to avoid a cancellation fee.'],
                ['category' => 'hours', 'key' => 'Opening Hours', 'value' => 'Open Monday to Saturday, 8:00 AM to 8:00 PM by appointment only.'],
            ],
            'Professional Services (Agency, Consult)' => [
                ['category' => 'services', 'key' => 'Core Services', 'value' => "We provide software engineering, strategic marketing, accounting audit, and legal consulting tailored to your business goals."],
                ['category' => 'pricing', 'key' => 'Billing Models', 'value' => 'We support monthly retainer frameworks or fixed-price project contracts with clear milestones.'],
                ['category' => 'faqs', 'key' => 'Discovery Calls', 'value' => 'We offer a free 30-minute introductory call to map project scope and fit.'],
                ['category' => 'contact', 'key' => 'Email Contacts', 'value' => 'Reach out to support@domain.com for billing or custom quotes.'],
            ],
            'Education & Coaching' => [
                ['category' => 'services', 'key' => 'Subjects & Courses', 'value' => 'We provide academic tutoring, professional business coaching, and language classes for kids and adults.'],
                ['category' => 'pricing', 'key' => 'Course Fees', 'value' => 'Tutoring starts at $45 per hour. Semester courses are priced at $490 flat.'],
                ['category' => 'faqs', 'key' => 'Rescheduling', 'value' => 'Lessons can be rescheduled up to 12 hours before start time through our portal.'],
                ['category' => 'hours', 'key' => 'Support availability', 'value' => 'Instructors are online Monday to Saturday, 9:00 AM to 7:00 PM.'],
            ],
            'Travel, Tourism & Hospitality' => [
                ['category' => 'services', 'key' => 'Lodging & Experience', 'value' => "Enjoy comfortable room bookings and guided travel itineraries curated by {$businessName}."],
                ['category' => 'pricing', 'key' => 'Rates & Check-in', 'value' => 'Standard rooms start at $120 per night. Normal check-in is at 3:00 PM; check-out is at 11:00 AM.'],
                ['category' => 'faqs', 'key' => 'Refunds & Cancellation', 'value' => 'Free cancellations are allowed up to 7 days before check-in. Late cancellations incur a 50% charge.'],
                ['category' => 'hours', 'key' => 'Desk Availability', 'value' => 'Reception desk is staffed daily from 8:00 AM to 10:00 PM. Digital check-in available.'],
            ],
            'Beauty & Salon' => [
                ['category' => 'services', 'key' => 'Salon Services', 'value' => 'We specialize in hair coloring, haircuts, manicures, and aesthetic facials.'],
                ['category' => 'pricing', 'key' => 'Service Pricing', 'value' => 'Haircuts start at $35. Coloring starts at $80. Manicures are $25.'],
                ['category' => 'faqs', 'key' => 'Walk-ins Welcome', 'value' => 'We prioritize appointments but accept walk-in clients based on stylist availability.'],
                ['category' => 'hours', 'key' => 'Opening Hours', 'value' => 'Open Tuesday to Saturday, 9:00 AM to 7:00 PM.'],
            ],
            'Local Services (Cleaning, Auto, Plumbing)' => [
                ['category' => 'services', 'key' => 'Services Provided', 'value' => 'We offer home deep cleaning, plumbing fixtures repair, and scheduled auto maintenance.'],
                ['category' => 'pricing', 'key' => 'Rates Structure', 'value' => 'Hourly rates are $70/hour plus materials, or flat-rates for defined service contracts.'],
                ['category' => 'faqs', 'key' => 'Satisfaction Guarantee', 'value' => 'All local repairs are covered by a 100% service satisfaction guarantee for 60 days.'],
                ['category' => 'hours', 'key' => 'Operating Hours', 'value' => 'Available Monday to Friday, 8:00 AM to 6:00 PM.'],
            ],
            'Other / Custom Niche' => [
                ['category' => 'services', 'key' => 'Bespoke Services', 'value' => "We provide local custom solutions matching your exact business needs."],
                ['category' => 'pricing', 'key' => 'Custom Estimates', 'value' => 'Quotes are calculated based on requirements. Drop us a message for a custom estimate.'],
                ['category' => 'faqs', 'key' => 'General Inquiries', 'value' => 'We reply to all inquiries on the same day. Please supply specifications in your text.'],
                ['category' => 'contact', 'key' => 'Reach Out', 'value' => 'Message this official WhatsApp for customer assistance.'],
            ]
        ];

        $list = $templates[$category] ?? $templates['Other / Custom Niche'];

        foreach ($list as $item) {
            BusinessMemory::create([
                'tenant_id' => $tenantId,
                'category'  => $item['category'],
                'key'       => $item['key'],
                'value'     => $item['value'],
                'active'    => true
            ]);
        }
    }

    /**
     * Seeds sandbox template memories for personal accounts.
     */
    private function seedPersonalTemplates(int $tenantId, string $role, string $name): void
    {
        BusinessMemory::where('tenant_id', $tenantId)->delete();

        $list = [
            ['category' => 'services', 'key' => 'Who I Am',        'value' => "{$name} is a {$role}. This AI assistant represents them personally."],
            ['category' => 'faqs',     'key' => 'Inquiries',        'value' => 'For collaboration, press, or business inquiries, please share your details and I will pass them along to ' . $name . '.'],
            ['category' => 'hours',    'key' => 'Response Times',   'value' => 'I am available 24/7. For time-sensitive matters, leave your contact details and you will hear back within 24 hours.'],
            ['category' => 'contact',  'key' => 'Booking & Calls',  'value' => 'Interested in working with ' . $name . '? Send a brief introduction and what you need, and we will connect you shortly.'],
        ];

        foreach ($list as $item) {
            BusinessMemory::create([
                'tenant_id' => $tenantId,
                'category'  => $item['category'],
                'key'       => $item['key'],
                'value'     => $item['value'],
                'active'    => true
            ]);
        }
    }

    /**
     * Compiles a premium system prompt.
     */
    private function buildSystemPrompt(
        string $accountType,
        string $aiName,
        string $aiTone,
        string $ownerName,
        ?string $businessDescription,
        string $category
    ): string {
        $contextLabel = $accountType === 'personal' ? "Personal Brand / {$category}" : "Business · {$category}";
        $descText     = $businessDescription ? "Profile: {$businessDescription}\n" : "";

        // Tone guidelines
        $toneGuide = match ($aiTone) {
            'professional' => 'Tone: Polite, clear, direct, and business-focused. Avoid casual slang.',
            'warm'         => 'Tone: Empathetic, warm, and highly welcoming. Sound like a close friend.',
            'casual'       => 'Tone: Playful, modern, relaxed, and conversational. Sound like a peer.',
            'sales'        => 'Tone: Persuasive, proactive, and opportunity-focused. Guide users toward action.',
            default        => 'Tone: Polite, helpful, and concise.'
        };

        $basePrompt  = "You are a specialized AI assistant named '{$aiName}' representing '{$ownerName}' ({$contextLabel}).\n";
        $basePrompt .= "{$descText}";
        $basePrompt .= "{$toneGuide}\n\n";
        $basePrompt .= "Instructions:\n";
        $basePrompt .= "- Keep all responses brief and concise (1 to 4 lines).\n";
        $basePrompt .= "- Never explicitly state you are an AI unless directly questioned.\n";
        $basePrompt .= "- Refer to provided memory to answer questions accurately.\n";
        $basePrompt .= "- If you do not know an answer, politely say you will check and follow up.";

        return $basePrompt;
    }
}
