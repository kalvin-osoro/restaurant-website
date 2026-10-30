<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Passport\ClientRepository;
use Tests\TestCase;

class OAuthFlowTest extends TestCase
{
    use RefreshDatabase, \Tests\Concerns\ConfiguresOAuthKeys;

    protected function setUp(): void
    {
        parent::setUp();
        $this->configureOAuthKeys();
    }

    private function authorizeCode(array $extra = []): array
    {
        $user = User::create(['name' => 'Example', 'email' => 'oauth@example.com', 'password' => 'strong-password-123']);
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient('Frontend', ['https://frontend.example/callback'], false);
        $verifier = Str::random(64);
        $query = array_merge([
            'client_id' => $client->id, 'redirect_uri' => 'https://frontend.example/callback',
            'response_type' => 'code', 'scope' => 'account.view', 'state' => Str::random(32),
            'code_challenge' => rtrim(strtr(base64_encode(hash('sha256', $verifier, true)), '+/', '-_'), '='),
            'code_challenge_method' => 'S256',
        ], $extra);
        $this->actingAs($user, 'web')->get('/api/v1/oauth/authorize?'.http_build_query($query))
            ->assertOk()->assertSee('Authorize Frontend');
        $response = $this->post('/api/v1/oauth/authorize', ['auth_token' => session('authToken')])->assertRedirect();
        parse_str(parse_url($response->headers->get('Location'), PHP_URL_QUERY), $callback);
        $this->assertSame($query['state'], $callback['state']);
        app('auth')->forgetGuards();
        return [['grant_type' => 'authorization_code', 'client_id' => $client->id,
            'redirect_uri' => 'https://frontend.example/callback', 'code' => $callback['code'], 'code_verifier' => $verifier], $user];
    }

    public function test_pkce_code_exchange_refresh_and_logout_revoke_real_oauth_tokens(): void
    {
        [$request] = $this->authorizeCode();
        $tokens = $this->postJson('/api/v1/oauth/token', $request)->assertOk()->assertJsonStructure(['access_token', 'refresh_token', 'token_type', 'expires_in']);
        $this->assertGreaterThan(0, $tokens->json('expires_in'));
        $this->assertLessThanOrEqual(3600, $tokens->json('expires_in'));
        $access = $tokens->json('access_token');
        $this->withToken($access)->getJson('/api/v1/me')->assertOk()->assertJsonPath('data.role', 'user');
        app('auth')->forgetGuards();
        $this->withToken($access)->postJson('/api/v1/practice-areas', ['slug' => 'law', 'name' => 'Law'])->assertForbidden();
        $refresh = ['grant_type' => 'refresh_token', 'client_id' => $request['client_id'], 'refresh_token' => $tokens->json('refresh_token')];
        $new = $this->postJson('/api/v1/oauth/token', $refresh)->assertOk();
        app('auth')->forgetGuards();
        $this->withToken($new->json('access_token'))->postJson('/api/v1/auth/logout')->assertOk();
        app('auth')->forgetGuards();
        $this->withToken($new->json('access_token'))->getJson('/api/v1/me')->assertUnauthorized();
        $refresh['refresh_token'] = $new->json('refresh_token');
        $this->postJson('/api/v1/oauth/token', $refresh)->assertStatus(400);
        $this->postJson('/api/v1/oauth/token', $request)->assertStatus(400);
    }

    public function test_wrong_pkce_verifier_cannot_exchange_code(): void
    {
        [$request] = $this->authorizeCode();
        $request['code_verifier'] = Str::random(64);
        $this->postJson('/api/v1/oauth/token', $request)->assertStatus(400)->assertJsonPath('error', 'invalid_grant');
    }

    public function test_unauthenticated_authorization_redirects_to_login_and_bad_redirect_is_rejected(): void
    {
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient('Frontend', ['https://frontend.example/callback'], false);
        $query = ['client_id' => $client->id, 'response_type' => 'code', 'redirect_uri' => 'https://frontend.example/callback',
            'code_challenge' => Str::random(64), 'code_challenge_method' => 'S256', 'state' => Str::random(32)];
        $this->get('/api/v1/oauth/authorize?'.http_build_query($query))->assertRedirect(route('login'));
        $this->get('/login')->assertOk()->assertSee('Sign in');
        $query['redirect_uri'] = 'https://attacker.example/callback';
        $response = $this->get('/api/v1/oauth/authorize?'.http_build_query($query));
        $this->assertContains($response->getStatusCode(), [400, 401]);
        $this->assertFalse($response->isRedirect());
    }

    public function test_password_grant_and_missing_pkce_are_rejected(): void
    {
        $client = app(ClientRepository::class)->createAuthorizationCodeGrantClient('Frontend', ['https://frontend.example/callback'], false);
        $this->postJson('/api/v1/oauth/token', ['grant_type' => 'password', 'client_id' => $client->id])->assertStatus(400)->assertJsonPath('error', 'unsupported_grant_type');
        $this->getJson('/api/v1/oauth/authorize?'.http_build_query(['client_id' => $client->id, 'response_type' => 'code', 'redirect_uri' => 'https://frontend.example/callback']))->assertStatus(400);
    }

    public function test_browser_login_establishes_session_and_does_not_issue_tokens(): void
    {
        User::create(['name' => 'Example', 'email' => 'login@example.com', 'password' => 'strong-password-123']);
        $this->post('/login', ['email' => 'login@example.com', 'password' => 'strong-password-123'])->assertRedirect('/');
        $this->assertAuthenticated('web');
        $this->assertDatabaseCount('oauth_access_tokens', 0);
        $this->post('/logout')->assertRedirect(route('login'));
        $this->assertGuest('web');
    }
    public function test_oauth_scopes_limit_even_administrator_tokens(): void
    {
        $user = new User(['name' => 'Admin', 'email' => 'admin@example.com', 'password' => 'strong-password-123']);
        $user->role = \App\Domain\Identity\Role::Admin;
        $user->save();
        app(ClientRepository::class)->createPersonalAccessGrantClient('Tests', 'users');
        $token = $user->createToken('limited', ['account.view'])->accessToken;
        $this->withToken($token)->getJson('/api/v1/me')->assertOk();
        $this->postJson('/api/v1/practice-areas', ['slug' => 'law', 'name' => 'Law'])->assertForbidden();
        $this->postJson('/api/v1/users/'.$user->id.'/role', ['role' => 'manager'])->assertForbidden();
        $this->assertDatabaseCount('practice_areas', 0);
    }

}
