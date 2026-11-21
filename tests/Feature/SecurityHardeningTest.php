<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    /**
     * A basic feature test example.
     */
    public function test_example(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_halaman_publik_tidak_memakai_atribut_style_inline(): void
    {
        $this->withoutVite();

        $this->get(route('home'))
            ->assertOk()
            ->assertDontSee('style=', false)
            ->assertSee('auth-backdrop');

        $this->get(route('login'))
            ->assertOk()
            ->assertDontSee('style=', false)
            ->assertSee('auth-backdrop');
    }

    public function test_csp_memblokir_style_inline_saat_penguatan_aktif(): void
    {
        config(['app.drop_style_attributes' => true]);

        $csp = $this->get(route('login'))->headers->get('Content-Security-Policy');

        $this->assertStringContainsString("style-src 'self' https://fonts.googleapis.com", $csp);
        $this->assertStringNotContainsString('unsafe-inline', $csp);
        $this->assertStringContainsString("script-src 'self' 'nonce-", $csp);
    }
}
