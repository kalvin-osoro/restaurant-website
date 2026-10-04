<?php

namespace App\Providers;

use App\Domain\Consultations\Contracts\ConsultationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentConsultationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        ConsultationRepository::class => EloquentConsultationRepository::class,
    ];
}
