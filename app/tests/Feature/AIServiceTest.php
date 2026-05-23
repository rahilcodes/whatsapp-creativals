<?php

namespace Tests\Feature;

use App\Models\Tenant;
use App\Models\User;
use App\Services\AIService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AIServiceTest extends TestCase
{
    use RefreshDatabase;

    private AIService $aiService;
    private Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();
        $this->aiService = new AIService();

        // Create default tenant
        $this->tenant = Tenant::create([
            'name' => 'Niche Test Corp',
            'slug' => 'niche-test',
            'status' => 'active',
        ]);
        app()->instance('tenant_id', $this->tenant->id);
    }

    public function test_detect_business_type_by_onboarding(): void
    {
        // 1. Restaurant
        $this->tenant->business_category = 'Food & Beverage (Restaurants, Cafes)';
        $this->tenant->save();
        $detected = $this->aiService->detectBusinessType('hello', ['business_memory' => '']);
        $this->assertEquals('restaurant', $detected);

        // 2. Real Estate
        $this->tenant->business_category = 'Real Estate (Agency, Development)';
        $this->tenant->save();
        $detected = $this->aiService->detectBusinessType('hello', ['business_memory' => '']);
        $this->assertEquals('real_estate', $detected);

        // 3. Healthcare
        $this->tenant->business_category = 'Healthcare & Wellness';
        $this->tenant->save();
        $detected = $this->aiService->detectBusinessType('hello', ['business_memory' => '']);
        $this->assertEquals('healthcare', $detected);
    }

    public function test_detect_business_type_by_memory(): void
    {
        $this->tenant->business_category = 'Other / Custom Niche';
        $this->tenant->save();

        // Check real estate keywords in memory
        $detected = $this->aiService->detectBusinessType('hello', [
            'business_memory' => 'We offer 2bhk and 3bhk luxury apartments for sale.'
        ]);
        $this->assertEquals('real_estate', $detected);

        // Check education keywords in memory
        $detected = $this->aiService->detectBusinessType('hello', [
            'business_memory' => 'Our coding syllabus covers full stack javascript course'
        ]);
        $this->assertEquals('education', $detected);
    }

    public function test_detect_business_type_by_message_keyword(): void
    {
        $this->tenant->business_category = 'Other / Custom Niche';
        $this->tenant->save();

        // Message has restaurant keywords
        $detected = $this->aiService->detectBusinessType('Can I see your menu or order chicken biryani?', [
            'business_memory' => ''
        ]);
        $this->assertEquals('restaurant', $detected);

        // Message has ecommerce keywords
        $detected = $this->aiService->detectBusinessType('How do I checkout my product cart?', [
            'business_memory' => ''
        ]);
        $this->assertEquals('ecommerce', $detected);
    }

    public function test_detect_business_type_fallback(): void
    {
        $this->tenant->business_category = 'Other / Custom Niche';
        $this->tenant->save();

        $detected = $this->aiService->detectBusinessType('random message with no keywords', [
            'business_memory' => ''
        ]);
        $this->assertEquals('generic', $detected);
    }

    public function test_clean_markdown(): void
    {
        $reflection = new \ReflectionClass(AIService::class);
        $method = $reflection->getMethod('cleanMarkdown');
        $method->setAccessible(true);

        // Text with headers, bold, italics, backticks, list items
        $dirtyText = "# Header Title\n**Bold Text** and *Italic Text*\n`inline code` here.\n- First Item\n* Second Item\n1. Third Item";
        $cleaned = $method->invoke($this->aiService, $dirtyText);

        $this->assertStringNotContainsString('#', $cleaned);
        $this->assertStringNotContainsString('**', $cleaned);
        $this->assertStringNotContainsString('*', $cleaned);
        $this->assertStringNotContainsString('`', $cleaned);
        $this->assertStringNotContainsString('- ', $cleaned);

        // Check line-by-line matches
        $this->assertStringContainsString('Header Title', $cleaned);
        $this->assertStringContainsString('Bold Text and Italic Text', $cleaned);
        $this->assertStringContainsString('First Item', $cleaned);
        $this->assertStringContainsString('Second Item', $cleaned);
        $this->assertStringContainsString('Third Item', $cleaned);
    }
}
