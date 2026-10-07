<?php

namespace Tests\Feature;

use Tests\TestCase;

class ProxyTest extends TestCase
{
    public function test_redirects_use_https_when_the_proxy_forwards_an_https_request(): void
    {
        $this->withHeaders(['X-Forwarded-Proto' => 'https', 'X-Forwarded-Host' => 'app.example.com'])
            ->get('/')
            ->assertRedirect('https://app.example.com/login');
    }
}
