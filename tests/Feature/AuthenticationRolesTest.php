<?php

namespace Tests\Feature;

use App\Domain\Identity\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\{DB, Hash};
use Tests\TestCase;

class AuthenticationRolesTest extends TestCase
{
    use RefreshDatabase, \Tests\Concerns\ConfiguresOAuthKeys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureOAuthKeys();
    }


    private function account(Role $role = Role::User): User
    {
        $user = new User(['name' => 'Example', 'email' => uniqid().'@example.com', 'password' => 'strong-password-123']);
        $user->role = $role;
        $user->save();
        return $user;
    }

    private function token(User $user): string
    {
        app(\Laravel\Passport\ClientRepository::class)->createPersonalAccessGrantClient('Tests', 'users');
        return $user->createToken('test', ['account.view', 'content.write', 'users.assign-roles'])->accessToken;
    }

    public function test_registration_hashes_password_and_creates_only_normal_users(): void
    {
        $data = ['name' => 'Example', 'email' => 'new@example.com', 'password' => 'strong-password-123', 'password_confirmation' => 'strong-password-123'];
        $this->postJson('/api/v1/auth/register', $data + ['role' => 'admin'])->assertUnprocessable()->assertJsonValidationErrors('role');
        $response = $this->postJson('/api/v1/auth/register', $data)->assertCreated()->assertJsonPath('data.role', 'user');
        $user = User::firstOrFail();
        $this->assertTrue(Hash::check($data['password'], $user->password));
        $this->assertNotSame($data['password'], $user->password);
        $this->assertArrayNotHasKey('password', $response->json('data'));
        $this->assertDatabaseCount('oauth_access_tokens', 0);
        $this->withToken($this->token($user))->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.permissions', ['account.view']);
    }

    public function test_admin_and_manager_can_write_but_normal_users_cannot(): void
    {
        foreach ([Role::Admin, Role::Manager, Role::User] as $role) {
            app('auth')->forgetGuards();
            $user = $this->account($role);
            $response = $this->withToken($this->token($user))->postJson('/api/v1/practice-areas', ['slug' => $role->value, 'name' => $role->value]);
            $role === Role::User ? $response->assertForbidden() : $response->assertCreated();
        }
        $this->assertDatabaseCount('practice_areas', 2);
    }

    public function test_only_admins_can_assign_roles_and_changes_affect_existing_tokens(): void
    {
        $admin = $this->account(Role::Admin);
        $target = $this->account();
        $targetToken = $this->token($target);
        $adminToken = $this->token($admin);
        $url = '/api/v1/users/'.$target->id.'/role';
        $this->withToken($targetToken)->postJson($url, ['role' => 'admin'])->assertForbidden();
        app('auth')->forgetGuards();
        $this->withToken($adminToken)->postJson($url, ['role' => 'manager'])->assertOk()->assertJsonPath('data.role', 'manager');
        app('auth')->forgetGuards();
        $this->withToken($targetToken)->postJson($url, ['role' => 'admin'])->assertForbidden();
        $this->postJson('/api/v1/practice-areas', ['slug' => 'law', 'name' => 'Law'])->assertCreated();
        app('auth')->forgetGuards();
        $this->withToken($adminToken)->postJson($url, ['role' => 'user'])->assertOk();
        $this->postJson('/api/v1/users/'.$admin->id.'/role', ['role' => 'user'])->assertUnprocessable();
        $this->postJson($url, ['role' => 'super-admin'])->assertUnprocessable();
        app('auth')->forgetGuards();
        $this->withToken($targetToken)->postJson('/api/v1/practice-areas', ['slug' => 'other', 'name' => 'Other'])->assertForbidden();
    }

    public function test_role_cannot_be_mass_assigned_and_cli_can_bootstrap_admin(): void
    {
        $user = User::create(['name' => 'Example', 'email' => 'normal@example.com', 'password' => 'strong-password-123', 'role' => 'admin']);
        $this->assertSame(Role::User, $user->role);
        $this->artisan('users:create', ['email' => 'admin@example.com', '--name' => 'Administrator', '--role' => 'admin'])
            ->expectsQuestion('Password (at least 12 characters)', 'strong-password-123')->assertSuccessful();
        $this->assertDatabaseHas('users', ['email' => 'admin@example.com', 'role' => 'admin']);
    }

    public function test_authentication_is_required_and_browser_login_is_rate_limited(): void
    {
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->postJson('/api/v1/users/1/role', ['role' => 'admin'])->assertUnauthorized();
        for ($i = 0; $i < 5; $i++) {
            $this->postJson('/login', ['email' => 'missing@example.com', 'password' => 'wrong'])->assertUnprocessable();
        }
        $this->postJson('/login', [])->assertStatus(429);
    }

    public function test_legacy_roles_migrate_to_user_and_preserve_admins_and_managers(): void
    {
        foreach (['customer', 'unknown', 'admin', 'manager'] as $role) {
            DB::table('users')->insert(['name' => $role, 'email' => $role.'@example.com', 'password' => Hash::make('strong-password-123'), 'role' => $role]);
        }
        $migration = require database_path('migrations/2026_10_30_000001_normalize_user_roles.php');
        $migration->up();
        foreach (['customer' => 'user', 'unknown' => 'user', 'admin' => 'admin', 'manager' => 'manager'] as $email => $role) {
            $this->assertDatabaseHas('users', ['email' => $email.'@example.com', 'role' => $role]);
        }
        DB::table('users')->insert(['name' => 'Default', 'email' => 'default@example.com', 'password' => Hash::make('strong-password-123')]);
        $this->assertDatabaseHas('users', ['email' => 'default@example.com', 'role' => 'user']);
    }

    public function test_service_token_cannot_manage_roles_and_api_errors_are_json_without_accept_header(): void
    {
        $this->get('/api/v1/me')->assertUnauthorized()->assertHeader('Content-Type', 'application/json');
        config(['website-content.write_token' => str_repeat('x', 40)]);
        $user = $this->account();
        $this->withToken(str_repeat('x', 40))->postJson('/api/v1/users/'.$user->id.'/role', ['role' => 'admin'])->assertUnauthorized();
        $this->assertSame(Role::User, $user->fresh()->role);
    }
}
