<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BusinessMemory;
use App\Models\BotSetting;
use Illuminate\Support\Facades\DB;

class CreativalsSeeder extends Seeder
{
    public function run(): void
    {
        // Clear old memories
        DB::table('business_memories')->truncate();

        // Populate new memories
        $memories = [
            [
                'category' => 'positioning',
                'key' => 'Core Positioning',
                'value' => "Creativals is not a service provider. We are an Execution Partner that builds systems to generate leads, bookings, and sales.\nNEVER SAY: 'We do digital marketing' or 'We provide services'.\nALWAYS SAY: 'We help businesses grow using structured systems' and 'We handle execution end-to-end so you don’t have to worry'."
            ],
            [
                'category' => 'scripts',
                'key' => 'Opening Script',
                'value' => "Hi. This is the team from Creativals.\nCan you tell me a bit about your business and what exactly you're looking to achieve right now? (More leads / sales / website / ads / something else).\nGoal: Move conversation to their problem, not your services."
            ],
            [
                'category' => 'scripts',
                'key' => 'Discovery Questions',
                'value' => "Ask these before quoting anything:\n1. What business are you in?\n2. What are you currently doing for marketing?\n3. What’s not working right now?\n4. What’s your main goal in the next 30-60 days?\n5. Are you currently running ads or starting fresh?\n6. Approx budget range."
            ],
            [
                'category' => 'scripts',
                'key' => 'Positioning Response',
                'value' => "After understanding their problem, say:\nGot it. So based on what you’re saying, the main gap is [problem]. At Creativals, we don’t just run ads or build websites - we build a full system that actually converts traffic into leads and sales. That’s where most businesses struggle, and that’s exactly what we solve."
            ],
            [
                'category' => 'process',
                'key' => 'How We Work',
                'value' => "Our process is simple:\n1. We understand your business and audience.\n2. We build the right funnel (ads + landing + follow-up).\n3. We launch and test.\n4. Then we optimize continuously to improve results.\nSo you’re not just getting a service, you’re getting a growth system."
            ],
            [
                'category' => 'pricing',
                'key' => 'Base Pricing Guidelines',
                'value' => "1. Website Development: Rs 25,000 to 1,00,000+ (depending on complexity)\n2. SEO Services: Rs 20,000 to 60,000/month\n3. Paid Ads Management: Rs 15,000 to 50,000/month (+ ad spend separate)\n4. Social Media Management: Rs 10,000 to 40,000/month\n5. Complete Growth System (BEST SELL): Rs 30,000 to 1,00,000+/month"
            ],
            [
                'category' => 'pricing',
                'key' => 'How to communicate pricing',
                'value' => "Pricing depends on your goals and scale. Most of our clients fall in the range of Rs 30K to 80K per month for a proper growth system. But I’d recommend we first understand your exact requirement and then suggest the right plan - so you don’t overspend or underspend."
            ],
            [
                'category' => 'objections',
                'key' => 'Handling Price First Leads',
                'value' => "If they ask for price immediately, reply: Sure. We do have different pricing based on what exactly you need. For example: Ads management starts from around Rs 15K/month. Complete growth systems usually range Rs 30K-80K/month. But to give you the best result, I’d need to understand your business a bit more - otherwise I might suggest the wrong plan."
            ],
            [
                'category' => 'objections',
                'key' => 'Handling Too Expensive',
                'value' => "Totally understand. Our focus is on getting results, not just doing work. Most clients recover the cost through better leads and conversions - that’s how we look at it."
            ],
            [
                'category' => 'objections',
                'key' => 'Handling I got a cheaper option',
                'value' => "That’s completely fair. There are many cheaper options in the market. The difference usually comes down to execution quality and results - which is where we focus heavily."
            ],
            [
                'category' => 'objections',
                'key' => 'Handling Let me think',
                'value' => "Sure. Just to help you better - is there anything specific you're unsure about? Happy to clarify so you can make a confident decision."
            ],
            [
                'category' => 'trust',
                'key' => 'Trust Building Examples',
                'value' => "We’ve worked with businesses across Hotels, Schools, Local businesses, and E-commerce. Most of them came to us with the same problem - spending money but not getting proper results."
            ],
            [
                'category' => 'closing',
                'key' => 'Closing Script',
                'value' => "Best next step would be a quick 15-20 min call where we can break this down properly and show you what will actually work for your business. Would you like to schedule a call or prefer WhatsApp discussion?"
            ],
            [
                'category' => 'rules',
                'key' => 'Sales Rules',
                'value' => "Don’t argue. Don’t oversell. Don’t promise guaranteed results. Don’t sound desperate. Don’t send long paragraphs. Don’t quote without understanding."
            ],
        ];

        foreach ($memories as $memory) {
            BusinessMemory::create([
                'category' => $memory['category'],
                'key'      => $memory['key'],
                'value'    => $memory['value'],
                'active'   => true,
            ]);
        }

        // Update Bot Settings
        $settingsToUpdate = [
            'system_prompt' => "You are an expert sales consultant for 'Creativals'. You are not a service provider, you are an Execution Partner that builds growth systems.
CRITICAL RULES:
1. STRICTLY NO EMOJIS EVER. This is highly unprofessional for our brand.
2. Reply in a highly natural, short, human-like manner. Never send long paragraphs. Keep messages concise.
3. Diagnose before prescribing. Ask the discovery questions from your memory one by one. Do not ask them all at once.
4. Your job is NOT to sell hard. Your job is to diagnose and guide them to a call.
5. If they ask for price, give the ranges from memory but pivot back to understanding their business.
6. Do not use robotic phrases like 'I am an AI assistant'. Act entirely as a human sales representative.",
            'human_trigger_keywords' => 'call,urgent,complaint,manager,refund,legal,speak to human,talk to a person',
            'delay_min' => '4',
            'delay_max' => '12',
            'outside_hours_message' => 'Hi. We are currently away from the office. Please leave your requirement here and our team will get back to you shortly.',
        ];

        foreach ($settingsToUpdate as $key => $value) {
            BotSetting::where('key', $key)->update(['value' => $value]);
        }
    }
}
