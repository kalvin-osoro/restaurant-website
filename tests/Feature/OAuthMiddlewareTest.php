<?php

namespace Tests\Feature;

use App\Domain\Identity\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OAuthMiddlewareTest extends TestCase
{
    use RefreshDatabase, \Tests\Concerns\ConfiguresOAuthKeys;

    private const WRITE_PATHS = [
        'team', 'team/example', 'attorneys', 'attorneys/example',
        'cases', 'cases/example', 'practice-areas', 'case-categories',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureOAuthKeys();
    }

    private function account(Role $role = Role::Manager): User
    {
        $user = new User(['name' => 'Editor', 'email' => 'editor@example.com', 'password' => 'strong-password-123']);
        $user->role = $role;
        $user->save();

        return $user;
    }

    public function test_every_content_write_requires_a_bearer_token_even_without_an_accept_header(): void
    {
        foreach (self::WRITE_PATHS as $path) {
            $this->post('/api/v1/'.$path)->assertUnauthorized()
                ->assertHeader('Content-Type', 'application/json')
                ->assertJsonPath('error', true);
        }
        $this->assertNoContentWritten();
    }

    public function test_invalid_tokens_cannot_reach_any_content_write(): void
    {
        foreach (self::WRITE_PATHS as $path) {
            $this->withToken('invalid-token')->postJson('/api/v1/'.$path)->assertUnauthorized();
        }
        $this->assertNoContentWritten();
    }

    public function test_a_browser_session_does_not_authenticate_content_api_requests(): void
    {
        $this->actingAs($this->account(), 'web');

        foreach (self::WRITE_PATHS as $path) {
            $this->postJson('/api/v1/'.$path)->assertUnauthorized();
        }
        $this->assertNoContentWritten();
    }

    public function test_every_content_write_requires_the_scope_and_current_role_permission(): void
    {
        $user = $this->account(Role::Admin);
        app(ClientRepository::class)->createPersonalAccessGrantClient('Tests', 'users');
        $limited = $user->createToken('limited', ['account.view'])->accessToken;

        foreach (self::WRITE_PATHS as $path) {
            $this->withToken($limited)->postJson('/api/v1/'.$path)->assertForbidden();
        }

        // A content scope cannot elevate a normal user's current role.
        $user->role = Role::User;
        $user->save();
        app('auth')->forgetGuards();
        $scoped = $user->createToken('scoped', ['content.write'])->accessToken;

        foreach (self::WRITE_PATHS as $path) {
            $this->withToken($scoped)->postJson('/api/v1/'.$path)->assertForbidden();
        }
        $this->assertNoContentWritten();
    }

    public function test_a_real_oauth_token_can_write_and_revocation_prevents_further_writes(): void
    {
        $user = $this->account();
        app(ClientRepository::class)->createPersonalAccessGrantClient('Tests', 'users');
        $issued = $user->createToken('editor', ['content.write']);
        $this->withToken($issued->accessToken)->postJson('/api/v1/practice-areas', [
            'slug' => 'law', 'name' => 'Law',
        ])->assertCreated();

        $issued->token->revoke();
        app('auth')->forgetGuards();

        foreach (self::WRITE_PATHS as $path) {
            $this->withToken($issued->accessToken)->postJson('/api/v1/'.$path)->assertUnauthorized();
        }
        $this->assertDatabaseCount('tbl_practice_areas', 1);
    }

    private function assertNoContentWritten(): void
    {
        foreach (['tbl_attorneys', 'tbl_case_studies', 'tbl_practice_areas', 'tbl_case_categories'] as $table) {
            $this->assertDatabaseCount($table, 0);
        }
    }
}
