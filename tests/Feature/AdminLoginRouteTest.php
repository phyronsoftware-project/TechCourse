<?php

namespace Tests\Feature;

use Tests\TestCase;

class AdminLoginRouteTest extends TestCase
{
    public function test_admin_login_uses_the_private_route(): void
    {
        // Keep public user login and the private admin login on separate URLs.
        $this->assertSame(url('/phyron/100203/v1/login'), route('login'));
        $this->assertSame(url('/login'), route('web.login'));
    }

    public function test_guest_admin_request_redirects_to_the_private_login_route(): void
    {
        // Protected admin pages must never redirect guests to the public login form.
        $this->get('/admin/phyron/v1')
            ->assertRedirect('/phyron/100203/v1/login');
    }
}
