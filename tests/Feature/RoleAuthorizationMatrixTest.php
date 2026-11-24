<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleAuthorizationMatrixTest extends TestCase
{
    use RefreshDatabase;

    public function test_each_role_is_denied_from_other_role_route_groups(): void
    {
        $mother = User::factory()->create(['phone' => '08330000001', 'role' => 'mother', 'is_verified' => true]);
        $midwife = User::factory()->create(['phone' => '08330000002', 'role' => 'midwife', 'is_verified' => true]);
        $doctor = User::factory()->create(['phone' => '08330000003', 'role' => 'doctor', 'is_verified' => true]);
        $admin = User::factory()->create(['phone' => '08330000004', 'role' => 'admin', 'is_verified' => true]);

        // Mother ditolak dari midwife & doctor
        $this->actingAs($mother)->get(route('midwife.dashboard'))->assertRedirect(route('mother.dashboard'));
        $this->actingAs($mother)->get(route('doctor.dashboard'))->assertRedirect(route('mother.dashboard'));

        // Midwife ditolak dari mother & doctor
        $this->actingAs($midwife)->get(route('mother.dashboard'))->assertRedirect(route('midwife.dashboard'));
        $this->actingAs($midwife)->get(route('doctor.dashboard'))->assertRedirect(route('midwife.dashboard'));

        // Doctor ditolak dari mother & midwife
        $this->actingAs($doctor)->get(route('mother.dashboard'))->assertRedirect(route('doctor.dashboard'));
        $this->actingAs($doctor)->get(route('midwife.dashboard'))->assertRedirect(route('doctor.dashboard'));

        // Admin ditolak dari mother, midwife, doctor
        $this->actingAs($admin)->get(route('mother.dashboard'))->assertRedirect('/admin');
        $this->actingAs($admin)->get(route('midwife.dashboard'))->assertRedirect('/admin');
        $this->actingAs($admin)->get(route('doctor.dashboard'))->assertRedirect('/admin');
    }

    public function test_unverified_medical_staff_redirected_to_waiting_approval(): void
    {
        $unverifiedMidwife = User::factory()->create(['phone' => '08330000005', 'role' => 'midwife', 'is_verified' => false]);
        $unverifiedDoctor = User::factory()->create(['phone' => '08330000006', 'role' => 'doctor', 'is_verified' => false]);

        $this->actingAs($unverifiedMidwife)->get(route('midwife.dashboard'))->assertRedirect(route('waiting-approval'));
        $this->actingAs($unverifiedDoctor)->get(route('doctor.dashboard'))->assertRedirect(route('waiting-approval'));
    }

    public function test_unauthenticated_users_redirected_to_login(): void
    {
        $this->get(route('mother.dashboard'))->assertRedirect(route('login'));
        $this->get(route('midwife.dashboard'))->assertRedirect(route('login'));
        $this->get(route('doctor.dashboard'))->assertRedirect(route('login'));
    }
}
