<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_privacy_policy_page_returns_a_successful_response(): void
    {
        $response = $this->get('/privacy-policy');

        $response->assertStatus(200);
        $response->assertSee('Privacy Policy');
    }

    public function test_terms_and_conditions_page_returns_a_successful_response(): void
    {
        $response = $this->get('/terms-and-conditions');

        $response->assertStatus(200);
        $response->assertSee('Terms');
    }

    public function test_refund_policy_page_returns_a_successful_response(): void
    {
        $response = $this->get('/refund-policy');

        $response->assertStatus(200);
        $response->assertSee('Refund Policy');
    }
}
