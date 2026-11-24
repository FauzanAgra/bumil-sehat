<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestRedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_admin_visiting_login_redirects_to_admin_panel(): void
    {
        $admin = User::factory()->create([
            'phone' => '08130000001',
            'role' => 'admin',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('login'));

        $response->assertRedirect(url('/admin'));
    }

    public function test_authenticated_mother_visiting_login_redirects_to_mother_dashboard(): void
    {
        $mother = User::factory()->create([
            'phone' => '08130000002',
            'role' => 'mother',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($mother)->get(route('login'));

        $response->assertRedirect(route('mother.dashboard'));
    }

    public function test_authenticated_midwife_visiting_login_redirects_to_midwife_dashboard(): void
    {
        $midwife = User::factory()->create([
            'phone' => '08130000003',
            'role' => 'midwife',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($midwife)->get(route('login'));

        $response->assertRedirect(route('midwife.dashboard'));
    }

    public function test_authenticated_doctor_visiting_login_redirects_to_doctor_dashboard(): void
    {
        $doctor = User::factory()->create([
            'phone' => '08130000004',
            'role' => 'doctor',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($doctor)->get(route('login'));

        $response->assertRedirect(route('doctor.dashboard'));
    }
}
