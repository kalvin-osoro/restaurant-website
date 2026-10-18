<?php
namespace Tests\Feature;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Queue;
use App\Infrastructure\Persistence\Eloquent\Models\Consultation;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationLog;
use App\Infrastructure\Notifications\Jobs\SendConsultationNotification;
class ConsultationTest extends TestCase {
    use RefreshDatabase;
    private function payload(): array { return ['fullName' => 'Jane Counsel', 'organization' => 'Example Firm', 'email' => 'jane@example.com', 'phone' => '+254712345678', 'natureOfInquiry' => 'Corporate Representation', 'details' => 'Confidential matter for discussion.']; }
    public function test_booking_is_encrypted_and_creates_durable_notifications(): void {
        $this->postJson('/api/consultations', $this->payload() + ['status' => 'approved'])->assertCreated()->assertJsonPath('success', true);
        $this->assertDatabaseCount('consultations', 1);
        $this->assertDatabaseCount('notification_logs', 2);
        $this->assertSame($this->payload()['details'], Consultation::first()->details);
        $this->assertStringNotContainsString('Confidential', DB::table('consultations')->value('details'));
        $this->assertDatabaseHas('tbl_request_logs', ['status' => 201, 'route' => 'api/consultations']);
        $this->assertStringNotContainsString('jane@example.com', json_encode(DB::table('tbl_request_logs')->first()));
        Queue::fake();
        $this->artisan('consultations:dispatch')->assertSuccessful();
        $this->artisan('consultations:dispatch')->assertSuccessful();
        Queue::assertPushed(SendConsultationNotification::class, 2);
    }
    public function test_validation_and_unmatched_requests_are_logged(): void {
        $this->postJson('/api/consultations', array_replace($this->payload(), ['phone' => '123', 'natureOfInquiry' => 'unknown']))->assertUnprocessable()->assertJsonValidationErrors(['phone', 'natureOfInquiry']);
        $this->getJson('/api/missing')->assertNotFound();
        $this->assertDatabaseCount('consultations', 0);
        $this->assertDatabaseHas('tbl_request_logs', ['status' => 422]);
        $this->assertDatabaseHas('tbl_request_logs', ['status' => 404]);
    }
    public function test_rate_limit_is_logged(): void {
        for ($i = 0; $i < 5; $i++) $this->postJson('/api/consultations', [])->assertUnprocessable();
        $this->postJson('/api/consultations', [])->assertStatus(429);
        $this->assertDatabaseHas('tbl_request_logs', ['status' => 429]);
    }
    public function test_email_and_sms_delivery_and_duplicate_job_guard(): void {
        Mail::fake(); Http::preventStrayRequests();
        config(['consultations.twilio.sid' => 'ACexample', 'consultations.twilio.token' => 'secret', 'consultations.channels.sms.from' => '+15551234567']);
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SMexample'], 201)]);
        $this->postJson('/api/consultations', $this->payload())->assertCreated();
        foreach (NotificationLog::all() as $log) {
            $job = new SendConsultationNotification($log->id); $job->handle(); $job->handle();
            $this->assertSame('sent', $log->fresh()->status);
            $this->assertSame(1, $log->fresh()->attempts);
        }
        Mail::assertSentCount(1); Http::assertSentCount(1);
        Http::assertSent(fn ($request) => $request['To'] === '+254712345678' && !str_contains($request['Body'], 'Confidential'));
    }
    public function test_provider_failure_is_sanitized_and_recorded(): void {
        $this->postJson('/api/consultations', $this->payload())->assertCreated();
        $log = NotificationLog::where('channel', 'sms')->first();
        $job = new SendConsultationNotification($log->id);
        try { $job->handle(); $this->fail('Expected provider configuration failure'); }
        catch (\RuntimeException $e) { $this->assertStringNotContainsString('jane', $e->getMessage()); }
        $this->assertSame('retrying', $log->fresh()->status);
        $job->failed(null); $this->assertSame('failed', $log->fresh()->status);
    }
    public function test_outbox_dispatch_persists_database_jobs(): void {
        config(['queue.default' => 'database']);
        $this->postJson('/api/consultations', $this->payload())->assertCreated();
        $this->artisan('consultations:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('jobs', 2);
        $this->assertDatabaseHas('notification_logs', ['channel' => 'email', 'status' => 'queued']);
    }
    public function test_whatsapp_uses_configured_template_and_can_be_disabled(): void {
        config(['consultations.channels.email.enabled' => false, 'consultations.channels.sms.enabled' => false,
            'consultations.channels.whatsapp.enabled' => true, 'consultations.channels.whatsapp.from' => 'whatsapp:+15551234567',
            'consultations.twilio.sid' => 'ACexample', 'consultations.twilio.token' => 'secret', 'consultations.twilio.whatsapp_template' => 'HXexample']);
        Http::preventStrayRequests();
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SMwhatsapp'], 201)]);
        $this->postJson('/api/consultations', $this->payload())->assertCreated();
        $this->assertDatabaseCount('notification_logs', 1);
        $log = NotificationLog::first();
        (new SendConsultationNotification($log->id))->handle();
        Http::assertSent(fn ($request) => $request['To'] === 'whatsapp:+254712345678' && $request['ContentSid'] === 'HXexample');
        $this->assertSame('SMwhatsapp', $log->fresh()->provider_reference);
    }
}
