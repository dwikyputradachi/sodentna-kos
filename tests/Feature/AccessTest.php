<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $phone): User
    {
        return User::create(['name' => 'Uji ' . $role, 'phone' => $phone, 'role' => $role, 'password' => 'rahasia123']);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/owner')->assertRedirect(route('login'));
        $this->get('/penghuni')->assertRedirect(route('login'));
    }

    public function test_tenant_cannot_open_owner_pages(): void
    {
        $this->actingAs($this->user('tenant', '6281200000001'))->get('/owner')->assertForbidden();
    }

    public function test_owner_cannot_open_tenant_pages(): void
    {
        $this->actingAs($this->user('owner', '6281200000002'))->get('/penghuni')->assertForbidden();
    }

    public function test_owner_login_with_local_phone_format(): void
    {
        $this->user('owner', '6281200000003');
        $this->post('/masuk', ['login' => '081200000003', 'password' => 'rahasia123'])->assertRedirect(route('owner.dashboard'));
        $this->assertAuthenticated();
    }

    public function test_wrong_password_is_rejected(): void
    {
        $this->user('tenant', '6281200000004');
        $this->post('/masuk', ['login' => '081200000004', 'password' => 'salah'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }

    public function test_inactive_user_cannot_login(): void
    {
        User::create(['name' => 'X', 'phone' => '6281200000005', 'role' => 'tenant', 'password' => 'rahasia123', 'is_active' => false]);
        $this->post('/masuk', ['login' => '081200000005', 'password' => 'rahasia123'])->assertSessionHasErrors('login');
        $this->assertGuest();
    }
}
