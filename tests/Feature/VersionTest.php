<?php

namespace Tests\Feature;

use Tests\TestCase;

class VersionTest extends TestCase
{
    public function test_version_endpoint_reports_the_configured_version_without_login(): void
    {
        config(['app.version' => 'abc1234']);

        $this->getJson('/version')
            ->assertOk()
            ->assertExactJson(['version' => 'abc1234']);
    }
}
