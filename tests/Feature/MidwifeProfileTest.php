<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MidwifeProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_verified_midwife_can_update_profile_and_redirect_to_profile_setting(): void
    {
        $midwife = User::factory()->create([
            'name' => 'Bidan Lama',
            'email' => 'bidan.lama@example.com',
            'phone' => '08120000001',
            'role' => 'midwife',
            'is_verified' => true,
        ]);

        $response = $this->actingAs($midwife)->post(route('midwife.profile.update'), [
            'name' => 'Bidan Baru',
            'email' => 'bidan.baru@example.com',
        ]);

        $response->assertRedirect(route('midwife.profile.setting'))
            ->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $midwife->id,
            'name' => 'Bidan Baru',
            'email' => 'bidan.baru@example.com',
        ]);
    }

    public function test_verified_midwife_can_update_password_and_redirect_to_profile_setting(): void
    {
        $midwife = User::factory()->create([
            'phone' => '08120000002',
            'role' => 'midwife',
            'is_verified' => true,
            'password' => Hash::make('OldPass123'),
        ]);

        $response = $this->actingAs($midwife)->post(route('midwife.profile.password.update'), [
            'current_password' => 'OldPass123',
            'password' => 'NewPass123',
            'password_confirmation' => 'NewPass123',
        ]);

        $response->assertRedirect(route('midwife.profile.setting'))
            ->assertSessionHas('success');

        $this->assertTrue(Hash::check('NewPass123', $midwife->fresh()->password));
    }

    public function test_midwife_password_update_rejects_wrong_current_password(): void
    {
        $midwife = User::factory()->create([
            'phone' => '08120000003',
            'role' => 'midwife',
            'is_verified' => true,
            'password' => Hash::make('OldPass123'),
        ]);

        $response = $this->actingAs($midwife)
            ->from(route('midwife.profile.password'))
            ->post(route('midwife.profile.password.update'), [
                'current_password' => 'WrongPass123',
                'password' => 'NewPass123',
                'password_confirmation' => 'NewPass123',
            ]);

        $response->assertRedirect(route('midwife.profile.password'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check('OldPass123', $midwife->fresh()->password));
    }
}
