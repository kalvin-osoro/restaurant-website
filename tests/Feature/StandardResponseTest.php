<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Route};
use Tests\TestCase;

class StandardResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_and_empty_paginated_results_have_a_stable_envelope(): void
    {
        $this->getJson('/api/v1/health')->assertOk()->assertJsonPath('status', 200)
            ->assertJsonPath('response_code', 0)->assertJsonPath('error', false)
            ->assertJsonPath('message', 'Service healthy')->assertJsonPath('data.status', 'ok');
        $this->getJson('/api/v1/team')->assertOk()->assertJsonPath('data', [])
            ->assertJsonPath('error', false)->assertJsonPath('meta.total', 0)->assertJsonStructure(['links', 'meta']);
    }

    public function test_validation_and_missing_routes_use_the_envelope_and_keep_field_errors(): void
    {
        $response = $this->postJson('/api/v1/consultations', [])->assertUnprocessable()
            ->assertJsonPath('status', 422)->assertJsonPath('response_code', 0)
            ->assertJsonPath('error', true)->assertJsonPath('message', 'Validation error')
            ->assertJsonValidationErrors(['fullName', 'email']);
        $this->assertSame($response->json('errors'), $response->json('data'));
        $this->getJson('/api/v1/does-not-exist')->assertNotFound()->assertJsonPath('status', 404)->assertJsonPath('error', true);
    }

    public function test_throttling_headers_survive_exception_formatting(): void
    {
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/api/v1/consultations', [])->assertUnprocessable();
        }
        $this->postJson('/api/v1/consultations', [])->assertStatus(429)
            ->assertHeader('Retry-After')->assertJsonPath('status', 429)->assertJsonPath('error', true);
    }

    public function test_server_errors_hide_debug_details_and_payloads_are_not_logged(): void
    {
        config(['app.debug' => true]);
        Route::post('/api/v1/test-server-error', fn () => throw new \RuntimeException('secret-provider-password'));
        $this->postJson('/api/v1/test-server-error', ['password' => 'request-secret'])
            ->assertStatus(500)->assertExactJson(['status' => 500, 'response_code' => 0, 'error' => true, 'message' => 'Server error', 'data' => []]);
        $audit = json_encode(DB::table('tbl_request_logs')->get());
        $this->assertStringNotContainsString('request-secret', $audit);
        $this->assertStringNotContainsString('secret-provider-password', $audit);
        $this->assertDatabaseCount('tbl_request_logs', 1);
    }

    public function test_helper_handles_legacy_status_codes_falsy_data_and_suppression_without_payload_logs(): void
    {
        $response = result('Legacy success', false, -1, 7, true, ['status' => 500]);
        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame(['status' => 200, 'response_code' => 7, 'error' => false, 'message' => 'Legacy success', 'data' => false], $response->getData(true));
        $this->assertSame([], result('Empty')->getData(true)['data']);
        $this->assertSame(0, result('Zero', 0)->getData(true)['data']);
        $this->assertDatabaseCount('tbl_request_logs', 0);
        Route::post('/api/v1/test-suppressed', fn () => result('Saved', ['token' => 'response-secret'], 201, 0, true));
        $this->postJson('/api/v1/test-suppressed', ['password' => 'request-secret'])->assertCreated()->assertJsonPath('status', 201);
        $this->assertDatabaseCount('tbl_request_logs', 1);
        $logs = json_encode(DB::table('tbl_request_logs')->get());
        $this->assertStringNotContainsString('response-secret', $logs);
        $this->assertStringNotContainsString('request-secret', $logs);
    }
}
