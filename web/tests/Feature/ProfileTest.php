<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\Unit;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_page_is_displayed(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->get('/profile');

        $response->assertOk();
    }

    public function test_profile_information_can_be_updated(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'test@example.com',
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $user->refresh();

        $this->assertSame('Test User', $user->name);
        $this->assertSame('test@example.com', $user->email);
        $this->assertNull($user->email_verified_at);
    }

    public function test_sensitive_profile_change_requires_current_password(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile/data-diri')
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => 'baru@example.com',
            ]);

        $response
            ->assertSessionHasErrors('current_password')
            ->assertRedirect('/profile/data-diri');
    }

    public function test_nik_cannot_be_changed_when_bound_to_active_spk(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nik' => '1826374681273641',
            'telp' => '081234567890',
            'alamat' => 'Jl. Aman',
        ]);
        $branch = Branch::create(['kode' => 'JKT', 'nama' => 'Jakarta', 'telp' => '0210000001', 'booking_fee' => 5000000, 'aktif' => true]);
        $brand = Brand::create(['nama' => 'Toyota', 'slug' => 'toyota']);
        $model = CarModel::create(['brand_id' => $brand->id, 'nama' => 'Avanza', 'slug' => 'avanza']);
        $variant = Variant::create(['car_model_id' => $model->id, 'nama' => '1.5 G', 'slug' => '1-5-g', 'transmisi' => 'CVT', 'bbm' => 'Bensin', 'aktif' => true]);
        $color = Color::create(['nama' => 'Putih', 'kode_hex' => '#FFFFFF', 'aktif' => true]);
        $unit = Unit::create(['vin' => 'VINSEC00000000001', 'variant_id' => $variant->id, 'color_id' => $color->id, 'branch_id' => $branch->id, 'status' => 'Terjual', 'tahun_rakit' => 2026, 'harga' => 250000000]);
        Order::create([
            'no_spk' => 'SPK-2026-9001',
            'customer_id' => $customer->id,
            'unit_id' => $unit->id,
            'branch_id' => $branch->id,
            'metode' => 'Cash',
            'booking_fee' => 5000000,
            'status' => 'Deal',
            'deadline_at' => now()->addDay(),
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile/data-diri')
            ->patch('/profile', [
                'name' => $user->name,
                'email' => $user->email,
                'telp' => $customer->telp,
                'nik' => '9999999999999999',
                'current_password' => 'password',
            ]);

        $response
            ->assertSessionHasErrors('nik')
            ->assertRedirect('/profile/data-diri');
        $this->assertSame('1826374681273641', $customer->fresh()->nik);
    }

    public function test_account_deletion_blocked_when_active_service_exists(): void
    {
        $user = User::factory()->create();
        $customer = Customer::create([
            'user_id' => $user->id,
            'nama' => $user->name,
            'nik' => '1826374681273642',
            'telp' => '081234567891',
            'alamat' => 'Jl. Aktif',
        ]);
        $branch = Branch::create(['kode' => 'BDG', 'nama' => 'Bandung', 'telp' => '0220000001', 'booking_fee' => 5000000, 'aktif' => true]);
        ServiceBooking::create([
            'no_svc' => 'SVC-2026-9001',
            'customer_id' => $customer->id,
            'branch_id' => $branch->id,
            'nopol' => 'D 1234 AA',
            'km' => 5000,
            'keluhan' => 'Ganti oli',
            'jadwal' => now()->addDays(2),
            'status' => 'Menunggu',
        ]);

        $response = $this
            ->actingAs($user)
            ->from('/profile/data-diri')
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile/data-diri');
        $this->assertNotNull($user->fresh());
    }

    public function test_email_verification_status_is_unchanged_when_the_email_address_is_unchanged(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->patch('/profile', [
                'name' => 'Test User',
                'email' => $user->email,
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/profile');

        $this->assertNotNull($user->refresh()->email_verified_at);
    }

    public function test_user_can_delete_their_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->delete('/profile', [
                'password' => 'password',
            ]);

        $response
            ->assertSessionHasNoErrors()
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertNull($user->fresh());
    }

    public function test_correct_password_must_be_provided_to_delete_account(): void
    {
        $user = User::factory()->create();

        $response = $this
            ->actingAs($user)
            ->from('/profile')
            ->delete('/profile', [
                'password' => 'wrong-password',
            ]);

        $response
            ->assertSessionHasErrorsIn('userDeletion', 'password')
            ->assertRedirect('/profile');

        $this->assertNotNull($user->fresh());
    }
}
