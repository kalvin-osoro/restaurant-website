<?php

namespace Tests\Feature;

use App\Infrastructure\Persistence\Eloquent\Models\{Attorney, PracticeArea};
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Schema};
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class TablePrefixMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_fresh_installation_uses_prefixed_application_framework_and_oauth_tables(): void
    {
        foreach (Schema::getTableListing() as $table) {
            $table = basename(str_replace('.', '/', $table));
            if (!str_starts_with($table, 'sqlite_')) {
                $this->assertStringStartsWith('tbl_', $table);
            }
        }
        foreach (['users', 'attorneys', 'practice_areas', 'attorney_practice_area', 'case_studies', 'case_categories',
            'consultations', 'notification_logs', 'cache', 'cache_locks', 'sessions', 'jobs', 'failed_jobs',
            'oauth_clients', 'oauth_access_tokens', 'oauth_refresh_tokens', 'oauth_auth_codes', 'oauth_device_codes',
            'personal_access_tokens', 'migrations', 'request_logs'] as $table) {
            $this->assertTrue(Schema::hasTable('tbl_'.$table), $table);
        }
        $this->assertFalse(Schema::hasTable('tbl_tbl_request_logs'));
    }

    public function test_legacy_upgrade_and_rollback_preserve_data_relationships_and_migration_history(): void
    {
        $this->artisan('website-content:demo')->assertSuccessful();
        $user = User::create(['name' => 'Preserved', 'email' => 'preserved@example.com', 'password' => 'strong-password-123']);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient('Preserved client', ['https://example.com/callback'], false);
        $attorney = Attorney::with('practiceAreas')->firstOrFail();
        $areaIds = $attorney->practiceAreas->modelKeys();
        $password = $user->password;
        $ledgerCount = DB::table('tbl_migrations')->count();

        $this->artisan('migrate:rollback', ['--step' => 1, '--force' => true])->assertSuccessful();
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertTrue(Schema::hasTable('migrations'));
        $this->assertFalse(Schema::hasTable('tbl_users'));
        $this->assertDatabaseHas('users', ['id' => $user->id, 'password' => $password]);
        $this->assertDatabaseHas('oauth_clients', ['id' => $client->id]);
        $this->assertSame($ledgerCount - 1, DB::table('migrations')->count());
        $this->artisan('migrate:status')->assertSuccessful();

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertDatabaseHas('tbl_users', ['id' => $user->id, 'password' => $password]);
        $this->assertDatabaseHas('tbl_oauth_clients', ['id' => $client->id]);
        $this->assertSame($ledgerCount, DB::table('tbl_migrations')->count());
        $this->assertSame($areaIds, Attorney::findOrFail($attorney->id)->practiceAreas->modelKeys());
        $this->assertTrue(Schema::hasTable('tbl_request_logs'));
        $this->assertFalse(Schema::hasTable('migrations'));
        $this->artisan('migrate', ['--force' => true])->assertSuccessful();
        $this->assertSame($ledgerCount, DB::table('tbl_migrations')->count());
    }

    public function test_conflicting_table_names_are_rejected_before_any_rename(): void
    {
        $this->artisan('migrate:rollback', ['--step' => 1, '--force' => true])->assertSuccessful();
        Schema::create('tbl_attorneys', fn ($table) => $table->id());
        $migration = require database_path('migrations/2026_11_09_000001_prefix_database_tables.php');
        try {
            $migration->up();
            $this->fail('A conflicting table should prevent renaming.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('tbl_attorneys already exists', $exception->getMessage());
        }
        $this->assertTrue(Schema::hasTable('users'));
        $this->assertFalse(Schema::hasTable('tbl_users'));
        $this->assertTrue(Schema::hasTable('migrations'));
    }
    public function test_duplicate_migration_ledgers_fail_without_merging_history(): void
    {
        $count = DB::table('tbl_migrations')->count();
        Schema::create('migrations', function ($table) {
            $table->id(); $table->string('migration'); $table->integer('batch');
        });
        try {
            app('migration.repository')->repositoryExists();
            $this->fail('Duplicate ledgers must not be merged automatically.');
        } catch (\RuntimeException $exception) {
            $this->assertStringContainsString('Both migrations and tbl_migrations exist', $exception->getMessage());
        }
        $this->assertSame($count, DB::table('tbl_migrations')->count());
        $this->assertDatabaseCount('migrations', 0);
    }
}
