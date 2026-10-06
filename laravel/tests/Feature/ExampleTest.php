<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_homepage_is_public_for_guests(): void
    {
        $response = $this->get('/');
        $response->assertOk()
            ->assertSee('Look good.', false)
            ->assertDontSee('Featured Services');
    }
}
