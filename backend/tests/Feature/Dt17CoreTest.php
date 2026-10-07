<?php

namespace Tests\Feature;

use Tests\TestCase;

class Dt17CoreTest extends TestCase
{
    public function test_home_is_reachable(): void
    {
        $this->get('/')->assertOk();
    }

    public function test_program_catalog_endpoint_is_reachable(): void
    {
        $this->getJson('/api/v1/programs')->assertOk();
    }

    public function test_program_not_found_returns_404(): void
    {
        $this->getJson('/api/v1/programs/999999')->assertNotFound();
    }

    public function test_login_page_contains_csrf(): void
    {
        $this->get('/dang-nhap')->assertOk()->assertSee('name="_token"', false);
    }

    public function test_python_recommendation_route_is_registered(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertNotNull(
            $routes->getByName('programs.recommend')
        );
    }
}
