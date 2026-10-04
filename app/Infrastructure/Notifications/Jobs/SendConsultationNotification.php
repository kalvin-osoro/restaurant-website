<?php
namespace App\Infrastructure\Notifications\Jobs;
use App\Domain\Consultations\Contracts\ConsultationChannel;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationLog;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Throwable;
class SendConsultationNotification implements ShouldQueue {
    use Queueable;
    public $tries = 3;
    public $timeout = 30;
    public function __construct(public int $logId) {}
    public function backoff(): array { return [60, 300]; }
    public function middleware(): array { return [(new WithoutOverlapping('consultation-notification-'.$this->logId))->releaseAfter(60)->expireAfter(60)]; }
    public function handle(): void {
        $log = NotificationLog::findOrFail($this->logId);
        if ($log->status === 'sent' || $log->status === 'failed') return;
        $log->increment('attempts');
        try {
            $driver = app(config("consultations.channels.{$log->channel}.driver"));
            if (!$driver instanceof ConsultationChannel) throw new \RuntimeException('Invalid notification driver.');
            $consultation = $log->consultation;
            $recipient = new \App\Domain\Consultations\Data\NotificationRecipient(
                $consultation->reference, $consultation->email, $consultation->phone,
            );
            $reference = $driver->send($recipient, $log->channel, 'consultation-'.$log->id);
            $log->update(['status' => 'sent', 'provider_reference' => $reference, 'sent_at' => now(), 'error_code' => null]);
        } catch (Throwable $e) {
            $log->update(['status' => 'retrying', 'error_code' => class_basename($e)]);
            // Do not leak provider responses, credentials, or recipient details into failed_jobs.
            throw new \RuntimeException('Consultation notification delivery failed. See notification log '.$log->id.'.');
        }
    }
    public function failed(?Throwable $exception): void {
        NotificationLog::whereKey($this->logId)->update(['status' => 'failed']);
    }
}
