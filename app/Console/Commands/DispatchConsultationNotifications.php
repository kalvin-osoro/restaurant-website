<?php

namespace App\Console\Commands;

use App\Infrastructure\Notifications\Jobs\SendConsultationNotification;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationLog;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DispatchConsultationNotifications extends Command
{
    protected $signature = 'consultations:dispatch';
    protected $description = 'Queue pending consultation notifications';

    public function handle(): int
    {
        NotificationLog::where('status', 'pending')->eachById(function (NotificationLog $log): void {
            DB::transaction(function () use ($log): void {
                $locked = NotificationLog::whereKey($log->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'pending') {
                    return;
                }

                $locked->update(['status' => 'queued']);
                SendConsultationNotification::dispatch($locked->id);
            });
        });

        return self::SUCCESS;
    }
}
