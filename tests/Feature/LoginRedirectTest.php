<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LoginRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_signed_in_users_are_sent_to_their_own_dashboard(): void
    {
        $owner = User::create(['name' => 'P', 'phone' => '6281200000031', 'role' => 'owner', 'password' => 'rahasia123']);
        $tenant = User::create(['name' => 'T', 'phone' => '6281200000032', 'role' => 'tenant', 'password' => 'rahasia123']);

        $this->actingAs($owner)->get('/masuk')->assertRedirect(route('owner.dashboard'));
        $this->actingAs($tenant)->get('/masuk')->assertRedirect(route('tenant.home'));
    }

    public function test_guest_sees_the_login_form(): void
    {
        $this->get('/masuk')->assertOk()->assertSee('Masuk');
    }
}
