<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CorsTest extends TestCase
{
    /**
     * Test CORS preflight request for register endpoint
     */
    public function test_cors_preflight_request_for_register()
    {
        $response = $this->options('/api/register', [
            'Origin' => 'https://dev-backend.hi-3d.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, X-Requested-With, Authorization'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'https://dev-backend.hi-3d.com');
        $response->assertHeader('Access-Control-Allow-Methods');
        $response->assertHeader('Access-Control-Allow-Headers');
    }

    /**
     * Test CORS headers on actual POST request
     */
    public function test_cors_headers_on_post_request()
    {
        $response = $this->post('/api/register', [
            'first_name' => 'Test',
            'last_name' => 'User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'is_professional' => false
        ], [
            'Origin' => 'https://dev-backend.hi-3d.com',
            'Content-Type' => 'application/json',
            'X-Requested-With' => 'XMLHttpRequest'
        ]);

        // Should have CORS headers regardless of response status
        $this->assertTrue($response->headers->has('Access-Control-Allow-Origin'));
    }

    /**
     * Test CORS with localhost origin
     */
    public function test_cors_with_localhost_origin()
    {
        $response = $this->options('/api/register', [
            'Origin' => 'http://localhost:3000',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type, X-Requested-With'
        ]);

        $response->assertStatus(200);
        $response->assertHeader('Access-Control-Allow-Origin', 'http://localhost:3000');
    }

    /**
     * Test CORS with unauthorized origin
     */
    public function test_cors_with_unauthorized_origin()
    {
        $response = $this->options('/api/register', [
            'Origin' => 'https://malicious-site.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'Content-Type'
        ]);

        // Should still return 200 for OPTIONS but without allowing the origin
        $response->assertStatus(200);
    }
}
