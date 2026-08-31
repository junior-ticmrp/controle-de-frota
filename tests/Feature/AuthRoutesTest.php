<?php

namespace Tests\Feature;

use Tests\TestCase;

class AuthRoutesTest extends TestCase
{
    public function test_login_pages_are_available(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Acesso corporativo');
        $this->get(route('recovery.login'))->assertOk()->assertSee('Recuperação local');
    }

    public function test_dashboard_redirects_guests_to_corporate_login(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
