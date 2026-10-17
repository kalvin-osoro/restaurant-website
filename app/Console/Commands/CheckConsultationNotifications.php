<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Throwable;

class CheckConsultationNotifications extends Command
{
    protected $signature = 'consultations:check';
    protected $description = 'Check notification configuration and database prerequisites without sending messages';

    public function handle(): int
    {
        $issues = [];
        if (config('queue.default') !== 'database') {
            $issues[] = 'Set QUEUE_CONNECTION=database for atomic notification dispatch.';
        }
        if (config('cache.default') !== 'database') {
            $issues[] = 'Use CACHE_STORE=database so scheduler and worker locks are shared.';
        }

        try {
            foreach (['consultations', 'notification_logs', 'jobs', 'failed_jobs', 'cache', 'cache_locks'] as $table) {
                if (!Schema::hasTable($table)) {
                    $issues[] = "Missing table: {$table}. Run php artisan migrate.";
                }
            }
        } catch (Throwable) {
            // Connection exceptions can include database credentials or host details.
            $issues[] = 'Cannot inspect the database. Check connectivity and database permissions.';
        }

        if (config('consultations.channels.email.enabled')) {
            if (config('mail.default') !== 'smtp') {
                $issues[] = 'Email is enabled but MAIL_MAILER is not smtp; log/array mailers do not deliver email.';
            } else {
                if (!filled(config('mail.mailers.smtp.host'))) {
                    $issues[] = 'Set MAIL_HOST to a reachable SMTP server.';
                }
                $port = filter_var(config('mail.mailers.smtp.port'), FILTER_VALIDATE_INT);
                if (!$port || $port < 1 || $port > 65535) {
                    $issues[] = 'Set MAIL_PORT to a valid TCP port.';
                }
                if (!filter_var(config('mail.from.address'), FILTER_VALIDATE_EMAIL)) {
                    $issues[] = 'Set MAIL_FROM_ADDRESS to a valid provider-approved sender.';
                }
                if (!in_array(config('mail.mailers.smtp.scheme'), [null, '', 'smtp', 'smtps'], true)) {
                    $issues[] = 'MAIL_SCHEME must be smtp or smtps (or unset for automatic selection).';
                }
                $username = filled(config('mail.mailers.smtp.username'));
                $password = filled(config('mail.mailers.smtp.password'));
                if ($username !== $password) {
                    $issues[] = 'Set both MAIL_USERNAME and MAIL_PASSWORD when SMTP authentication is required.';
                }
            }
        }

        $sms = config('consultations.channels.sms.enabled');
        $whatsapp = config('consultations.channels.whatsapp.enabled');
        if ($sms || $whatsapp) {
            foreach (['sid' => 'TWILIO_ACCOUNT_SID', 'token' => 'TWILIO_AUTH_TOKEN'] as $key => $name) {
                if (!filled(config("consultations.twilio.{$key}"))) {
                    $issues[] = "Set {$name} for enabled Twilio channels.";
                }
            }
            foreach (['sms' => $sms, 'whatsapp' => $whatsapp] as $channel => $enabled) {
                if ($enabled && !filled(config("consultations.channels.{$channel}.from"))) {
                    $issues[] = 'Set '.($channel === 'sms' ? 'TWILIO_SMS_FROM' : 'TWILIO_WHATSAPP_FROM').' for the enabled channel.';
                }
            }
            if ($whatsapp && !filled(config('consultations.twilio.whatsapp_template'))) {
                $issues[] = 'Set TWILIO_WHATSAPP_CONTENT_SID to an approved template.';
            }
        }

        foreach ($issues as $issue) {
            $this->error($issue);
        }
        $this->line('No messages were sent. Provider connectivity, running processes, and delivery require separate verification.');
        if ($issues !== []) {
            return self::FAILURE;
        }
        $this->info('Notification configuration and database prerequisites passed.');
        return self::SUCCESS;
    }
}
