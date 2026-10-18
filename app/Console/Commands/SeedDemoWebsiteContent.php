<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SeedDemoWebsiteContent extends Command
{
    protected $signature = 'website-content:demo';
    protected $description = 'Load clearly fictional public website examples in local/testing environments only';

    public function handle(): int
    {
        if (!app()->environment(['local', 'testing'])) {
            $this->error('Demo content is restricted to local and testing environments.');
            return self::FAILURE;
        }

        return $this->call('website-content:import', ['file' => database_path('demo/website-content.json')]);
    }
}
