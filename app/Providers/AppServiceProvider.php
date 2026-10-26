<?php

namespace App\Providers;

use App\Domain\Consultations\Contracts\ConsultationRepository;
use App\Infrastructure\Persistence\Eloquent\Repositories\EloquentConsultationRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        \App\Domain\WebsiteContent\Contracts\ContentWriter::class => \App\Infrastructure\Persistence\Eloquent\Repositories\EloquentContentWriter::class,
        \App\Domain\WebsiteContent\Contracts\PublicContentRepository::class => \App\Infrastructure\Persistence\Eloquent\Repositories\EloquentPublicContentRepository::class,
        ConsultationRepository::class => EloquentConsultationRepository::class,
    ];
}
