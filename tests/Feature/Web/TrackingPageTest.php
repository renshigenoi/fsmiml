<?php

namespace Tests\Feature\Web;

use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrackingPageTest extends TestCase
{
    #[Test]
    public function tracking_page_renders_for_any_token(): void
    {
        $this->get('/tracking/some-random-token')
            ->assertOk()
            ->assertSee('Live Tracking Pemasangan');
    }
}
