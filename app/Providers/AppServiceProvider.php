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
    public function boot(): void
    {
        \Laravel\Passport\Passport::authorizationView('auth.authorize');
        \Laravel\Passport\Passport::tokensExpireIn(now()->addHour());
        \Laravel\Passport\Passport::personalAccessTokensExpireIn(now()->addHour());
        \Laravel\Passport\Passport::refreshTokensExpireIn(now()->addDays(30));
        \Laravel\Passport\Passport::tokensCan([
            'account.view' => 'Read your account details',
            'content.write' => 'Manage website content within your role permissions',
            'users.assign-roles' => 'Assign account roles if you are an administrator',
        ]);
        \Laravel\Passport\Passport::defaultScopes(['account.view']);

        foreach (\App\Domain\Identity\Permission::cases() as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission->value,
                fn (\App\Models\User $user) => $user->hasPermission($permission));
        }
    }
}
