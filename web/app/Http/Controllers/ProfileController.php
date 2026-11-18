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

        return view('profile.edit', [
            'user' => $user,
            'customer' => $customer,
            'aktivitas' => $aktivitas,
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

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
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
