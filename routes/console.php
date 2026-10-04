<?php

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', fn () => $this->comment('Build useful tools and serve clients well.'));

Schedule::command('consultations:dispatch')->everyMinute()->withoutOverlapping();
