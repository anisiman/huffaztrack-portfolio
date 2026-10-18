<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

/**
 * Role-based access to /admin and /lecturer routes.
 *
 * Uses unsaved, in-memory users so no database or real records are needed.
 * Every request below is rejected (or handled by a stub route) before any
 * controller runs, so no queries are made.
 */
class RoleAccessTest extends TestCase
{
    private function userWithRole(string $role): User
    {
        $user = new User(['name' => 'Test ' . $role, 'email' => $role . '@example.test', 'role' => $role]);
        $user->id = 1;

        return $user;
    }

    public function test_every_admin_route_requires_the_admin_role(): void
    {
        $this->assertRoutesUnderPrefixRequire('admin', 'role:admin');
    }

    public function test_every_lecturer_route_requires_the_lecturer_role(): void
    {
        $this->assertRoutesUnderPrefixRequire('lecturer', 'role:lecturer');
    }

    public function test_guest_is_redirected_to_login_from_admin_routes(): void
    {
        $this->post('/admin/admin-accounts')->assertRedirect(route('login'));
    }

    public function test_student_cannot_create_admin_accounts(): void
    {
        $this->actingAs($this->userWithRole('student'))
            ->post('/admin/admin-accounts', [
                'name' => 'Intruder',
                'email' => 'intruder@example.test',
                'password' => 'password123',
                'password_confirmation' => 'password123',
            ])
            ->assertForbidden();
    }

    public function test_lecturer_cannot_create_admin_accounts(): void
    {
        $this->actingAs($this->userWithRole('lecturer'))
            ->post('/admin/admin-accounts')
            ->assertForbidden();
    }

    public function test_non_admins_cannot_modify_student_records(): void
    {
        foreach (['student', 'lecturer'] as $role) {
            $user = $this->userWithRole($role);

            $this->actingAs($user)->post('/admin/masterlist')->assertForbidden();
            $this->actingAs($user)->put('/admin/masterlist/DEMO000001')->assertForbidden();
            $this->actingAs($user)->delete('/admin/masterlist/DEMO000001')->assertForbidden();
            $this->actingAs($user)->post('/admin/students')->assertForbidden();
            $this->actingAs($user)->get('/admin/dashboard')->assertForbidden();
        }
    }

    public function test_non_lecturers_cannot_use_lecturer_routes(): void
    {
        foreach (['student', 'admin'] as $role) {
            $this->actingAs($this->userWithRole($role))
                ->post('/lecturer/hifz/save-all')
                ->assertForbidden();
        }
    }

    public function test_role_middleware_allows_matching_role(): void
    {
        Route::middleware(['web', 'auth', 'role:admin'])->get('/_test/admin-only', fn () => 'ok');
        Route::middleware(['web', 'auth', 'role:lecturer'])->get('/_test/lecturer-only', fn () => 'ok');

        $this->actingAs($this->userWithRole('admin'))->get('/_test/admin-only')->assertOk();
        $this->actingAs($this->userWithRole('lecturer'))->get('/_test/lecturer-only')->assertOk();
        $this->actingAs($this->userWithRole('student'))->get('/_test/admin-only')->assertForbidden();
    }

    private function assertRoutesUnderPrefixRequire(string $prefix, string $middleware): void
    {
        $checked = 0;

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            if ($uri !== $prefix && ! str_starts_with($uri, $prefix . '/')) {
                continue;
            }

            $this->assertContains($middleware, $route->gatherMiddleware(), "Route [{$uri}] is missing {$middleware}.");
            $checked++;
        }

        $this->assertGreaterThan(0, $checked, "No routes found under /{$prefix}.");
    }
}
