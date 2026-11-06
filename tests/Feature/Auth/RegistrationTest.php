<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_distribution' => true,
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('profile', absolute: false));

        $user = User::firstWhere('email', 'test@example.com');

        $this->assertSame('testuser', $user->username);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertNotNull($user->privacy_consent_at);
        $this->assertNotNull($user->distribution_consent_at);
        $this->assertSame(config('legal.version'), $user->privacy_version);
    }

    public function test_new_users_can_register_without_email(): void
    {
        $this->post('/register', [
            'name' => 'Test User',
            'username' => 'noemail',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_distribution' => true,
        ]);

        $this->assertAuthenticated();

        $user = User::firstWhere('username', 'noemail');

        $this->assertNull($user->email);
    }

    public function test_registration_requires_consents(): void
    {
        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'username' => 'testuser',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'password',
        ]);

        $response->assertRedirect('/register');
        $response->assertSessionHasErrors([
            'accept_terms',
            'accept_privacy',
            'accept_distribution',
        ]);

        $this->assertGuest();
        $this->assertDatabaseMissing('users', ['username' => 'testuser']);
    }

    public function test_registration_requires_invite_code_when_configured(): void
    {
        config(['auth.registration_invite_code' => 'let-me-in']);

        $payload = [
            'name' => 'Beta User',
            'username' => 'beta',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_distribution' => true,
        ];

        $this->from('/register')
            ->post('/register', $payload)
            ->assertSessionHasErrors('invite_code');

        $this->post('/register', [...$payload, 'invite_code' => 'wrong'])
            ->assertSessionHasErrors('invite_code');

        $this->assertGuest();

        $this->post('/register', [...$payload, 'invite_code' => 'let-me-in'])
            ->assertRedirect(route('profile', absolute: false));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['username' => 'beta']);
    }

    public function test_username_is_required_and_unique(): void
    {
        User::factory()->create(['username' => 'taken']);

        $response = $this->from('/register')->post('/register', [
            'name' => 'Test User',
            'username' => 'taken',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_distribution' => true,
        ]);

        $response->assertSessionHasErrors(['username']);

        $this->post('/register', [
            'name' => 'Test User',
            'password' => 'password',
            'password_confirmation' => 'password',
            'accept_terms' => true,
            'accept_privacy' => true,
            'accept_distribution' => true,
        ])->assertSessionHasErrors(['username']);

        $this->assertGuest();
    }
}
