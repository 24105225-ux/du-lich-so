<?php

namespace Tests\Feature;

use App\Models\Place;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class Dt17CoreTest extends TestCase
{
    use DatabaseTransactions;

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
        $this->get('/dang-nhap')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_register_page_contains_csrf(): void
    {
        $this->get('/dang-ky')
            ->assertOk()
            ->assertSee('name="_token"', false);
    }

    public function test_register_creates_parent_with_hashed_password(): void
    {
        $email = 'autotest' . uniqid() . '@demo.test';
        $password = 'Test@123456';

        $response = $this->post('/dang-ky', [
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $password,
        ]);

        $response->assertRedirectToRoute('login');

        $user = User::where('email', $email)->firstOrFail();

        $this->assertSame('parent', $user->role);
        $this->assertSame('active', $user->status);
        $this->assertTrue(
            Hash::check($password, $user->password_hash)
        );
    }

    public function test_python_recommendation_route_is_registered(): void
    {
        $routes = app('router')->getRoutes();

        $this->assertNotNull(
            $routes->getByName('programs.recommend')
        );
    }

    public function test_place_crud_works_for_admin(): void
    {
        $admin = User::where('role', 'admin')
            ->where('status', 'active')
            ->firstOrFail();

        $this->actingAs($admin);

        $create = $this->postJson('/api/v1/places', [
            'name' => 'Auto Test Place',
            'province' => 'Ha Noi',
            'lat' => 21.0285110,
            'lng' => 105.8048170,
            'best_season' => 'Quanh nam',
            'visit_minutes' => 60,
            'description' => 'Created by automated test.',
            'source_note' => 'DT17 PHPUnit',
        ]);

        $create
            ->assertCreated()
            ->assertJsonPath('data.name', 'Auto Test Place');

        $placeId = $create->json('data.id');

        $this->getJson("/api/v1/places/$placeId")
            ->assertOk()
            ->assertJsonPath('data.id', $placeId);

        $this->putJson("/api/v1/places/$placeId", [
            'name' => 'Auto Test Place Updated',
            'province' => 'Ha Noi',
            'lat' => 21.0300000,
            'lng' => 105.8100000,
            'best_season' => 'Mua thu',
            'visit_minutes' => 90,
            'description' => 'Updated by automated test.',
            'source_note' => 'DT17 PHPUnit UPDATED',
        ])
            ->assertOk()
            ->assertJsonPath('data.name', 'Auto Test Place Updated');

        $this->deleteJson("/api/v1/places/$placeId")
            ->assertOk()
            ->assertJsonPath(
                'message',
                'Xóa địa điểm thành công.'
            );

        $this->assertDatabaseMissing('places', [
            'id' => $placeId,
        ]);
    }

    public function test_place_crud_requires_authorization(): void
    {
        $parent = User::where('role', 'parent')
            ->where('status', 'active')
            ->firstOrFail();

        $this->actingAs($parent);

        $this->postJson('/api/v1/places', [
            'name' => 'Unauthorized Place',
            'province' => 'Ha Noi',
        ])->assertForbidden();
    }
}