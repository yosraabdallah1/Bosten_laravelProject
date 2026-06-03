<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tests d'authentification : inscription, connexion, déconnexion, accès admin.
 */
class AuthTest extends TestCase
{
    use RefreshDatabase;

    // ─── Inscription ─────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_register_with_valid_data(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Hedia Ben Salah',
            'email'                 => 'hedia@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', ['email' => 'hedia@example.com']);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_fails_with_duplicate_email(): void
    {
        User::factory()->create(['email' => 'existing@example.com']);

        $response = $this->post('/register', [
            'name'                  => 'Autre',
            'email'                 => 'existing@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('email');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_fails_with_mismatched_passwords(): void
    {
        $response = $this->post('/register', [
            'name'                  => 'Test',
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'DifferentPassword!',
        ]);

        $response->assertSessionHasErrors('password');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function registration_fails_without_name(): void
    {
        $response = $this->post('/register', [
            'name'                  => '',
            'email'                 => 'test@example.com',
            'password'              => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertSessionHasErrors('name');
    }

    // ─── Connexion ────────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_can_login_with_correct_credentials(): void
    {
        $user = User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('secret123'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@example.com',
            'password' => 'secret123',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticatedAs($user);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function user_cannot_login_with_wrong_password(): void
    {
        User::factory()->create([
            'email'    => 'user@example.com',
            'password' => bcrypt('correctpassword'),
        ]);

        $response = $this->post('/login', [
            'email'    => 'user@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function login_page_is_accessible(): void
    {
        $this->get('/login')->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function register_page_is_accessible(): void
    {
        $this->get('/register')->assertStatus(200);
    }

    // ─── Déconnexion ──────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post('/logout');

        $this->assertGuest();
    }

    // ─── Accès admin ─────────────────────────────────────────────────────────

    #[\PHPUnit\Framework\Attributes\Test]
    public function admin_can_access_admin_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertStatus(200);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function normal_user_cannot_access_admin_dashboard(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $response = $this->actingAs($user)->get('/admin/dashboard');

        // Redirigé ou 403
        $this->assertContains($response->status(), [302, 403]);
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_access_admin_dashboard(): void
    {
        $response = $this->get('/admin/dashboard');

        $response->assertRedirect('/login');
    }

    #[\PHPUnit\Framework\Attributes\Test]
    public function guest_cannot_access_protected_routes(): void
    {
        $this->get('/panier')->assertRedirect('/login');
        $this->get('/commandes')->assertRedirect('/login');
        $this->get('/profile')->assertRedirect('/login');
    }
}
