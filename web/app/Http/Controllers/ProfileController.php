<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Customer;
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

        // Akun customer: muat data dirinya (telp, NIK, alamat) yang dipakai SPK/test drive.
        $customer = ! $staf
            ? Customer::where('user_id', $user->id)->first()
            : null;

        // Dashboard pribadi: ringkasan aktivitas user ini sendiri.
        $aktivitas = ! $staf ? [
            'spk' => $customer ? $customer->orders()->count() : 0,
            'testDrive' => $customer ? $customer->testDrives()->count() : 0,
            'servis' => $customer ? $customer->services()->count() : 0,
            'sparepart' => $customer ? $customer->sparepartOrders()->count() : 0,
        ] : null;

        // Data Kendaraan Saya & Servis Terakhir untuk Dashboard Pelanggan
        $servisTerakhir = null;
        $riwayatServis = collect();
        $kendaraanList = collect();

        if (! $staf && $customer) {
            $riwayatServis = $customer->services()
                ->with(['branch', 'mekanik', 'details'])
                ->orderByDesc('jadwal')
                ->get();
            $servisTerakhir = $riwayatServis->first();

            // 1. Mobil dari SPK (Deal / MenungguVerifikasi / MenungguBayar)
            $orders = $customer->orders()
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
                    'foto' => $unit?->foto_utama,
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
                    ? \App\Models\Unit::with(['variant.carModel.brand', 'color'])->where('vin', $latestSvc->vin)->first()
                    : null;
                $namaMobilSvc = $unitByVin
                    ? trim(($unitByVin->variant?->carModel?->brand?->nama ?? '') . ' ' . ($unitByVin->variant?->carModel?->nama ?? '') . ' ' . ($unitByVin->variant?->nama ?? ''))
                    : 'Kendaraan Servis Terdaftar';

                $kendaraanList->push([
                    'nama' => $namaMobilSvc,
                    'foto' => $unitByVin?->foto_utama,
                    'nopol' => $latestSvc->nopol,
                    'vin' => $latestSvc->vin ?: '-',
                    'warna' => $unitByVin?->color?->nama ?? '-',
                    'warna_hex' => $unitByVin?->color?->kode_hex ?? '#9CA3AF',
                    'tahun' => $unitByVin?->tahun_rakit ?? '-',
                    'transmisi' => $unitByVin?->variant?->transmisi ?? '-',
                    'bbm' => $unitByVin?->variant?->bbm ?? '-',
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

        // Data diri khusus akun customer — tersimpan di tabel customers (dipakai SPK, test drive, servis).
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
