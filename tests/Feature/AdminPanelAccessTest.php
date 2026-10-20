<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminPanelAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_panel(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'phone' => '08100000000',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertStatus(200);
    }

    public function test_mother_cannot_access_panel(): void
    {
        $mother = User::factory()->create([
            'role' => 'mother',
            'phone' => '08100000001',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($mother)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_verified_midwife_cannot_access_panel(): void
    {
        $midwife = User::factory()->create([
            'role' => 'midwife',
            'phone' => '08100000002',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($midwife)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_verified_doctor_cannot_access_panel(): void
    {
        $doctor = User::factory()->create([
            'role' => 'doctor',
            'phone' => '08100000003',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($doctor)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_unverified_midwife_cannot_access_panel(): void
    {
        $midwife = User::factory()->create([
            'role' => 'midwife',
            'phone' => '08100000004',
            'is_verified' => false,
        ]);

        $response = $this->actingAs($midwife)->get('/admin');

        $response->assertStatus(403);
    }

    public function test_unauthenticated_user_redirected_to_login(): void
    {
        $response = $this->get('/admin');

        $response->assertRedirect('/admin/login');
    }
}
