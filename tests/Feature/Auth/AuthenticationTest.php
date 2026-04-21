<?php

namespace Tests\Feature\Auth;

use App\Models\Rol;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Volt\Volt;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /** Crea un usuario con el rol indicado (admin por defecto) */
    private function userWithRole(string $role = 'admin'): User
    {
        $rol  = Rol::firstOrCreate(['nombre' => $role], ['descripcion' => $role]);
        $user = User::factory()->create();
        $user->roles()->attach($rol->id);
        return $user;
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response
            ->assertOk()
            ->assertSeeVolt('pages.auth.login');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->userWithRole('admin');

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'password');

        $component->call('login');

        $component
            ->assertHasNoErrors()
            ->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticated();
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = $this->userWithRole('admin');

        $component = Volt::test('pages.auth.login')
            ->set('form.email', $user->email)
            ->set('form.password', 'wrong-password');

        $component->call('login');

        $component
            ->assertHasErrors()
            ->assertNoRedirect();

        $this->assertGuest();
    }

    public function test_navigation_menu_can_be_rendered(): void
    {
        $user = $this->userWithRole('admin');

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertOk();
    }

    public function test_users_can_logout(): void
    {
        $user = $this->userWithRole('admin');

        $this->actingAs($user);

        $component = Volt::test('layout.navigation');

        $component->call('logout');

        $component
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
    }
}
