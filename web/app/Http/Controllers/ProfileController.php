<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\SparepartOrder;
use App\Models\TestDrive;
use App\Models\Unit;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $staf = $user->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']);

        // Akun customer: muat seluruh customer ID miliknya (dipakai SPK, test drive, servis, sparepart).
        $customerIds = ! $staf
            ? Customer::where('user_id', $user->id)->pluck('id')->all()
            : [];
        $customer = ! $staf
            ? Customer::where('user_id', $user->id)->first()
            : null;

        // Dashboard pribadi: ringkasan aktivitas user ini sendiri.
        $aktivitas = ! $staf ? [
            'spk' => $customerIds ? Order::whereIn('customer_id', $customerIds)->count() : 0,
            'testDrive' => $customerIds ? TestDrive::whereIn('customer_id', $customerIds)->count() : 0,
            'servis' => $customerIds ? ServiceBooking::whereIn('customer_id', $customerIds)->count() : 0,
            'sparepart' => $customerIds ? SparepartOrder::whereIn('customer_id', $customerIds)->count() : 0,
        ] : null;

        // Data Kendaraan Saya & Servis Terakhir untuk Dashboard Pelanggan
        $servisTerakhir = null;
        $riwayatServis = collect();
        $kendaraanList = collect();

        if (! $staf && $customerIds) {
            $riwayatServis = ServiceBooking::whereIn('customer_id', $customerIds)
                ->with(['branch', 'mekanik', 'details', 'variant.carModel.brand', 'color'])
                ->orderByDesc('jadwal')
                ->get();
            $servisTerakhir = $riwayatServis->first();

            // Helper cari foto unit dari variant + color (fallback ke unit lain dari variant/model sama)
            $cariFotoUnit = function (?Unit $unit, ?int $variantId = null, ?int $colorId = null): ?string {
                if ($unit && $unit->foto_utama) {
                    return $unit->foto_utama;
                }
                $vId = $variantId ?? $unit?->variant_id;
                $cId = $colorId ?? $unit?->color_id;
                if ($vId) {
                    if ($cId) {
                        $fotoWarna = Unit::where('variant_id', $vId)->where('color_id', $cId)->whereNotNull('foto_utama')->value('foto_utama');
                        if ($fotoWarna) {
                            return $fotoWarna;
                        }
                    }
                    $fotoVarian = Unit::where('variant_id', $vId)->whereNotNull('foto_utama')->value('foto_utama');
                    if ($fotoVarian) {
                        return $fotoVarian;
                    }
                }

                return null;
            };

            // 1. Mobil dari SPK (Deal / MenungguVerifikasi / MenungguBayar)
            $orders = Order::whereIn('customer_id', $customerIds)
                ->with(['unit.variant.carModel.brand', 'unit.color', 'branch', 'invoice'])
                ->latest()
                ->get();

            $svcByNopol = $riwayatServis->groupBy(fn ($s) => strtoupper(trim($s->nopol ?? '')));
            $matchedNopols = [];

            foreach ($orders as $idxOrder => $order) {
                $unit = $order->unit;
                $variant = $unit?->variant;
                $model = $variant?->carModel;
                $brand = $model?->brand;
                $namaMobil = trim(($brand?->nama ?? '') . ' ' . ($model?->nama ?? '') . ' ' . ($variant?->nama ?? ''));

                // Cari apakah ada servis dengan VIN yang sama, atau pasangkan dengan nopol servis pelanggan
                $svcMatch = $riwayatServis->first(fn ($s) => $s->vin && $unit && strtoupper($s->vin) === strtoupper($unit->vin));
                if (! $svcMatch && $idxOrder === 0 && $riwayatServis->isNotEmpty()) {
                    $svcMatch = $riwayatServis->first();
                }
                if ($svcMatch && $svcMatch->nopol) {
                    $matchedNopols[] = strtoupper(trim($svcMatch->nopol));
                }

                $kendaraanList->push([
                    'nama' => $namaMobil ?: 'Unit Kendaraan',
                    'foto' => $cariFotoUnit($unit),
                    'nopol' => $svcMatch?->nopol ?: ($order->stnk_selesai_at ? 'STNK Terbit' : 'Proses STNK'),
                    'vin' => $unit?->vin ?? '-',
                    'warna' => $unit?->color?->nama ?? '-',
                    'warna_hex' => $unit?->color?->kode_hex ?? '#9CA3AF',
                    'tahun' => $unit?->tahun_rakit ?? date('Y'),
                    'transmisi' => $variant?->transmisi ?? '-',
                    'bbm' => $variant?->bbm ?? '-',
                    'cabang' => $order->branch?->nama ?? '-',
                    'no_spk' => $order->no_spk,
                    'status_spk' => $order->statusTampil(),
                    'km_terakhir' => $svcMatch?->km,
                    'servis_terakhir_at' => $svcMatch?->jadwal,
                    'servis_terakhir_status' => $svcMatch?->status,
                ]);
            }

            // 2. Kendaraan dari Booking Servis yang nopolnya belum masuk di daftar SPK di atas
            foreach ($svcByNopol as $nopolKey => $grupSvc) {
                if (! $nopolKey || in_array($nopolKey, $matchedNopols, true)) {
                    continue;
                }
                $latestSvc = $grupSvc->first();
                $unitByVin = $latestSvc->vin
                    ? Unit::with(['variant.carModel.brand', 'color'])->where('vin', $latestSvc->vin)->first()
                    : null;
                $variantSvc = $unitByVin?->variant ?? $latestSvc->variant;
                $colorSvc = $unitByVin?->color ?? $latestSvc->color;

                $namaMobilSvc = $variantSvc
                    ? trim(($variantSvc->carModel?->brand?->nama ?? '') . ' ' . ($variantSvc->carModel?->nama ?? '') . ' ' . ($variantSvc->nama ?? ''))
                    : 'Kendaraan Servis Terdaftar';

                $kendaraanList->push([
                    'nama' => $namaMobilSvc ?: 'Kendaraan Servis Terdaftar',
                    'foto' => $cariFotoUnit($unitByVin, $variantSvc?->id, $colorSvc?->id),
                    'nopol' => $latestSvc->nopol,
                    'vin' => $latestSvc->vin ?: '-',
                    'warna' => $colorSvc?->nama ?? '-',
                    'warna_hex' => $colorSvc?->kode_hex ?? '#9CA3AF',
                    'tahun' => $unitByVin?->tahun_rakit ?? '-',
                    'transmisi' => $variantSvc?->transmisi ?? '-',
                    'bbm' => $variantSvc?->bbm ?? '-',
                    'cabang' => $latestSvc->branch?->nama ?? '-',
                    'no_spk' => null,
                    'status_spk' => 'Terdaftar Servis',
                    'km_terakhir' => $latestSvc->km,
                    'servis_terakhir_at' => $latestSvc->jadwal,
                    'servis_terakhir_status' => $latestSvc->status,
                ]);
            }
        }

        return view('profile.edit', [
            'user' => $user,
            'customer' => $customer,
            'aktivitas' => $aktivitas,
            'servisTerakhir' => $servisTerakhir,
            'riwayatServis' => $riwayatServis,
            'kendaraanList' => $kendaraanList,
        ]);
    }

    /**
     * Halaman khusus Data Diri & Keamanan Akun Pelanggan.
     */
    public function dataDiri(Request $request): View
    {
        $user = $request->user();
        $staf = $user->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']);
        $customer = ! $staf
            ? Customer::where('user_id', $user->id)->first()
            : null;

        return view('profile.data-diri', [
            'user' => $user,
            'customer' => $customer,
            'staf' => $staf,
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill(app(ProfileUpdateRequest::class)->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $staf = $user->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']);

        // Data diri khusus akun customer, tersimpan di tabel customers (dipakai SPK, test drive, servis).
        if (! $staf && $request->anyFilled(['telp', 'nik', 'alamat'])) {
            $dataDiri = $request->validate([
                'telp' => ['nullable', 'regex:/^[0-9]{9,15}$/'],
                'nik' => ['nullable', 'digits:16'],
                'alamat' => ['nullable', 'string', 'max:500'],
            ]);

            $customer = Customer::firstOrCreate(
                ['user_id' => $user->id],
                ['nama' => $user->name, 'telp' => $dataDiri['telp'] ?? '']
            );
            $customer->fill(array_filter($dataDiri, fn ($v) => $v !== null));
            $customer->nama = $user->name;
            $customer->save();
        }

        $referer = (string) $request->headers->get('referer', '');
        $ruteKembali = str_contains($referer, '/profile/data-diri') ? 'profile.data-diri' : 'profile.edit';

        return Redirect::route($ruteKembali)->with('status', 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
