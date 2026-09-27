<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\TestCase;

class AdminLoginRouteTest extends TestCase
{
    public function test_admin_login_uses_the_private_route(): void
    {
        // Keep public user login and the private admin login on separate URLs.
        $this->assertSame(url('/phyron/100203/v1/login'), route('login'));
        $this->assertSame(url('/login'), route('web.login'));
        $this->assertSame(url('/phyron/100203/v1/auth/google/redirect'), route('admin.google.redirect'));
        $this->assertSame(url('/phyron/100203/v1/auth/google/callback'), route('admin.google.callback'));
    }

    public function test_admin_login_shows_its_own_google_entry(): void
    {
        // Render the Google option only on the private admin login form.
        config()->set('services.google.client_id', 'test-client');
        config()->set('services.google.client_secret', 'test-secret');

        $this->get('/phyron/100203/v1/login')
            ->assertOk()
            ->assertSee(route('admin.google.redirect'));
    }

    public function test_guest_admin_request_redirects_to_the_private_login_route(): void
    {
        // Protected admin pages must never redirect guests to the public login form.
        $this->get('/admin/phyron/v1')
            ->assertRedirect('/phyron/100203/v1/login');
    }

    public function test_authenticated_admin_opening_login_is_sent_to_dashboard(): void
    {
        // Avoid sending an already authenticated administrator back to the public home page.
        $admin = new User(['role' => 'admin', 'status' => 'active']);
        $admin->id = 100203;

        $this->actingAs($admin)
            ->get('/phyron/100203/v1/login')
            ->assertRedirect('/admin/phyron/v1');
    }
}
