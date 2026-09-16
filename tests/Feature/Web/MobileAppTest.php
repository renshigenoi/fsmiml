<?php

namespace Tests\Feature\Web;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MobileAppTest extends TestCase
{
    #[Test]
    public function mobile_app_shell_is_available(): void
    {
        // C5: HTML di-serve dengan injeksi <base href="/mobile/"> supaya aset
        // relatif build resolve ke subpath /mobile/ di browser.
        $this->get('/mobile')
            ->assertOk()
            ->assertHeader('Content-Type', 'text/html; charset=UTF-8')
            ->assertSee('<base href="/mobile/">', false);
    }

    #[Test]
    public function mobile_manifest_is_served(): void
    {
        $this->get('/mobile/manifest.webmanifest')
            ->assertOk()
            ->assertJsonPath('short_name', 'FSM Teknisi');
    }

    #[Test]
    public function service_worker_is_served_as_javascript(): void
    {
        $this->get('/mobile/sw.js')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/javascript');
    }
}
