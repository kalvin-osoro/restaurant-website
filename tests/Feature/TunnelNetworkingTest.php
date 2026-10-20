<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class TunnelNetworkingTest extends TestCase
{
    use RefreshDatabase;

    public function test_trusted_agent_generates_https_links_and_audits_the_remote_client(): void
    {
        config(['trustedproxy.proxies' => ['172.30.79.2']]);
        $this->withServerVariables(['REMOTE_ADDR' => '172.30.79.2'])
            ->withHeaders([
                'X-Forwarded-For' => '198.51.100.10',
                'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'example.ngrok-free.app',
            ])->getJson('/api/attorneys')->assertOk()
            ->assertJsonPath('links.first', 'https://example.ngrok-free.app/api/attorneys?perPage=20&page=1');

        $this->assertDatabaseHas('tbl_request_logs', [
            'ip_hash' => hash_hmac('sha256', '198.51.100.10', config('app.key')),
            'status' => 200,
        ]);
    }

    public function test_untrusted_clients_cannot_spoof_forwarded_ip_or_https(): void
    {
        config(['trustedproxy.proxies' => ['172.30.79.2']]);
        $this->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->withHeaders([
                'X-Forwarded-For' => '198.51.100.10', 'X-Forwarded-Proto' => 'https',
                'X-Forwarded-Host' => 'spoofed.example.com',
            ])->getJson('http://local-api.test/api/attorneys')->assertOk()
            ->assertJsonPath('links.first', 'http://local-api.test/api/attorneys?perPage=20&page=1');
        $this->assertDatabaseHas('tbl_request_logs', [
            'ip_hash' => hash_hmac('sha256', '203.0.113.10', config('app.key')),
        ]);
    }

    public function test_cors_preflight_accepts_configured_remote_frontend_and_ngrok_header(): void
    {
        config(['cors.allowed_origins' => ['https://frontend.example.com']]);
        $this->withHeaders([
            'Origin' => 'https://frontend.example.com',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'content-type,ngrok-skip-browser-warning',
        ])->options('/api/consultations')->assertNoContent()
            ->assertHeader('Access-Control-Allow-Origin', 'https://frontend.example.com')
            ->assertHeader('Access-Control-Allow-Headers', 'content-type,ngrok-skip-browser-warning');
        $this->assertSame(0, DB::table('consultations')->count());
        $this->assertDatabaseHas('tbl_request_logs', ['method' => 'OPTIONS', 'status' => 204]);
    }

    public function test_cors_does_not_grant_browser_access_to_an_unlisted_origin(): void
    {
        config(['cors.allowed_origins' => ['https://frontend.example.com']]);
        $response = $this->withHeaders(['Origin' => 'https://other.example.com'])
            ->getJson('/api/health')->assertOk();
        // A single configured origin may be sent unconditionally; the browser
        // denies access because it does not match the requesting Origin.
        $this->assertNotSame('https://other.example.com', $response->headers->get('Access-Control-Allow-Origin'));
        $this->assertNotSame('*', $response->headers->get('Access-Control-Allow-Origin'));
    }
}
