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
    public function register(): void
    {
        // Read a legacy migration ledger until the rename migration upgrades it.
        $this->app->extend('migration.repository', fn ($repository, $app) =>
            new \App\Infrastructure\Persistence\Eloquent\PrefixedMigrationRepository($app['db'], 'tbl_migrations'));
    }

    public function boot(): void
    {
        \Laravel\Passport\Passport::useAuthCodeModel(\App\Infrastructure\Persistence\Eloquent\Models\OAuth\AuthCode::class);
        \Laravel\Passport\Passport::useClientModel(\App\Infrastructure\Persistence\Eloquent\Models\OAuth\Client::class);
        \Laravel\Passport\Passport::useTokenModel(\App\Infrastructure\Persistence\Eloquent\Models\OAuth\Token::class);
        \Laravel\Passport\Passport::useRefreshTokenModel(\App\Infrastructure\Persistence\Eloquent\Models\OAuth\RefreshToken::class);
        \Laravel\Passport\Passport::useDeviceCodeModel(\App\Infrastructure\Persistence\Eloquent\Models\OAuth\DeviceCode::class);

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
