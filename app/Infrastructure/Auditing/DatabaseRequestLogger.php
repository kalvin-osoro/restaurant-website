<?php

namespace App\Infrastructure\Auditing;

use Illuminate\Support\Facades\DB;

final class DatabaseRequestLogger
{
    public function start(string $requestId, string $method, ?string $ip): void
    {
        DB::table('tbl_request_logs')->insert([
            'request_id' => $requestId,
            'method' => substr($method, 0, 16),
            'ip_hash' => hash_hmac('sha256', $ip ?? '', config('app.key')),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function finish(string $requestId, string $route, int $status, int $durationMs): void
    {
        DB::table('tbl_request_logs')->where('request_id', $requestId)->update([
            'route' => substr($route, 0, 255),
            'status' => $status,
            'duration_ms' => max(0, $durationMs),
            'updated_at' => now(),
        ]);
    }
}
