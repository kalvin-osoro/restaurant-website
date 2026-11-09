<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    private const TABLES = [
        'users' => 'tbl_users',
        'sessions' => 'tbl_sessions',
        'cache' => 'tbl_cache',
        'cache_locks' => 'tbl_cache_locks',
        'jobs' => 'tbl_jobs',
        'failed_jobs' => 'tbl_failed_jobs',
        'consultations' => 'tbl_consultations',
        'notification_logs' => 'tbl_notification_logs',
        'practice_areas' => 'tbl_practice_areas',
        'attorneys' => 'tbl_attorneys',
        'attorney_practice_area' => 'tbl_attorney_practice_area',
        'case_categories' => 'tbl_case_categories',
        'case_studies' => 'tbl_case_studies',
        'oauth_auth_codes' => 'tbl_oauth_auth_codes',
        'oauth_access_tokens' => 'tbl_oauth_access_tokens',
        'oauth_refresh_tokens' => 'tbl_oauth_refresh_tokens',
        'oauth_clients' => 'tbl_oauth_clients',
        'oauth_device_codes' => 'tbl_oauth_device_codes',
        'personal_access_tokens' => 'tbl_personal_access_tokens',
        'password_reset_tokens' => 'tbl_password_reset_tokens',
        'job_batches' => 'tbl_job_batches',
        'migrations' => 'tbl_migrations',
    ];

    public function up(): void
    {
        $this->renameTables(self::TABLES);
    }

    public function down(): void
    {
        $this->renameTables(array_flip(array_reverse(self::TABLES, true)));
    }

    private function renameTables(array $tables): void
    {
        // Check every name before renaming anything; never merge or overwrite tables.
        foreach ($tables as $from => $to) {
            if (Schema::hasTable($from) && Schema::hasTable($to)) {
                throw new RuntimeException("Cannot rename {$from}: {$to} already exists.");
            }
        }
        foreach ($tables as $from => $to) {
            if (Schema::hasTable($from)) {
                Schema::rename($from, $to);
            }
        }
        // tbl_request_logs already follows the convention and remains unchanged.
    }
};
