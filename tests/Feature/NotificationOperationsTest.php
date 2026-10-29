<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\NotificationLog;
use App\Infrastructure\Notifications\Mail\ConsultationReceived;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{Cache, Http, Mail, Schema};
use Tests\TestCase;

class NotificationOperationsTest extends TestCase
{
    use RefreshDatabase;

    private function readyConfiguration(): void
    {
        config([
            'queue.default' => 'database', 'cache.default' => 'database',
            'mail.default' => 'smtp', 'mail.mailers.smtp.host' => 'smtp.example.com',
            'mail.mailers.smtp.port' => 587, 'mail.mailers.smtp.scheme' => null,
            'mail.mailers.smtp.username' => 'smtp-user', 'mail.mailers.smtp.password' => 'smtp-secret',
            'mail.from.address' => 'sender@example.com',
            'consultations.channels.email.enabled' => true, 'consultations.channels.sms.enabled' => true,
            'consultations.channels.whatsapp.enabled' => false,
            'consultations.twilio.sid' => 'ACexample', 'consultations.twilio.token' => 'twilio-secret',
            'consultations.channels.sms.from' => '+15551234567',
        ]);
    }

    public function test_readiness_check_does_not_send_or_print_secrets(): void
    {
        $this->readyConfiguration();
        Mail::fake();
        Http::preventStrayRequests();
        $this->artisan('consultations:check')->expectsOutputToContain('prerequisites passed')
            ->doesntExpectOutputToContain('smtp-secret')->doesntExpectOutputToContain('twilio-secret')->assertSuccessful();
        Mail::assertNothingSent();
        Http::assertNothingSent();
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('notification_logs', 0);
    }

    public function test_readiness_rejects_log_mail_and_missing_provider_configuration(): void
    {
        $this->readyConfiguration();
        config(['mail.default' => 'log', 'consultations.twilio.token' => null]);
        $this->artisan('consultations:check')->expectsOutputToContain('MAIL_MAILER is not smtp')
            ->expectsOutputToContain('TWILIO_AUTH_TOKEN')->assertFailed();
    }

    public function test_readiness_detects_missing_lock_table(): void
    {
        $this->readyConfiguration();
        Schema::drop('cache_locks');
        $this->artisan('consultations:check')->expectsOutputToContain('Missing table: cache_locks')->assertFailed();
    }

    public function test_readiness_checks_optional_whatsapp_and_queue_requirements(): void
    {
        $this->readyConfiguration();
        config(['queue.default' => 'sync', 'consultations.channels.whatsapp.enabled' => true]);
        $this->artisan('consultations:check')->expectsOutputToContain('QUEUE_CONNECTION=database')
            ->expectsOutputToContain('TWILIO_WHATSAPP_FROM')->expectsOutputToContain('TWILIO_WHATSAPP_CONTENT_SID')->assertFailed();
    }

    public function test_database_worker_delivers_notifications_with_real_overlap_locks(): void
    {
        $this->readyConfiguration();
        Mail::fake();
        Http::preventStrayRequests();
        Http::fake(['api.twilio.com/*' => Http::response(['sid' => 'SMtest'], 201)]);
        $this->postJson('/api/v1/consultations', [
            'fullName' => 'Controlled Test', 'email' => 'test@example.com', 'phone' => '+254712345678',
            'natureOfInquiry' => 'General Consultation', 'details' => 'Private details must not be delivered.',
        ])->assertCreated();

        // Exercise scheduler mutexes too; tests calling job->handle() bypass these locks.
        $event = collect(app(\Illuminate\Console\Scheduling\Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command ?? '', 'consultations:dispatch'));
        $this->assertNotNull($event);
        $this->assertTrue($event->mutex->create($event));
        $this->assertFalse($event->mutex->create($event));
        $event->mutex->forget($event);

        $this->artisan('consultations:dispatch')->assertSuccessful();
        $this->assertDatabaseCount('jobs', 2);
        for ($i = 0; $i < 2; $i++) {
            $this->artisan('queue:work', ['--once' => true, '--tries' => 3, '--timeout' => 30])->assertSuccessful();
        }
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertSame(2, NotificationLog::where('status', 'sent')->where('attempts', 1)->count());
        Mail::assertSent(ConsultationReceived::class, 1);
        Http::assertSentCount(1);
        $lock = Cache::lock('notification-lock-regression', 30);
        $this->assertTrue($lock->get());
        $this->assertFalse(Cache::lock('notification-lock-regression', 30)->get());
        $lock->release();
    }
}
