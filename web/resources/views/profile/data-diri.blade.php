<x-app-layout>
    @php
        $staf = auth()->user()?->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']) ?? false;
    @endphp

    <x-slot name="header">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">
                {{ $staf ? (Auth::user()->branch?->nama ?? 'Akun Staf Dealer') : 'Pengaturan Profil & Keamanan' }}
            </p>
            <h1 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">
                Data Diri &amp; Keamanan Akun
            </h1>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri: Data Diri -->
            <div class="lg:col-span-7 bg-surface rounded-2xl border border-line shadow-sm p-6">
                @include('profile.partials.update-profile-information-form')
            </div>

            <!-- Kolom Kanan: Ganti Password & Hapus Akun -->
            <div class="lg:col-span-5 space-y-6">
                <div class="bg-surface rounded-2xl border border-line shadow-sm p-6">
                    @include('profile.partials.update-password-form')
                </div>

                @unless ($staf)
                    <div class="bg-surface rounded-2xl border border-red-200 dark:border-red-500/30 shadow-sm p-6">
                        @include('profile.partials.delete-user-form')
                    </div>
                @endunless
            </div>
        </div>
    </div>
</x-app-layout>
