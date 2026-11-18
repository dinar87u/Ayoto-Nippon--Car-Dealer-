<x-app-layout>
    @php
        $staf = auth()->user()?->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']) ?? false;
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">
                    {{ $staf ? (Auth::user()->branch?->nama ?? 'Akun Staf Dealer') : 'Portal Pelanggan Resmi' }}
                </p>
                <h1 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">
                    Halo, {{ \Illuminate\Support\Str::before($user->name, ' ') }}
                </h1>
            </div>

            @unless ($staf)
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('pesanan.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors inline-flex items-center gap-1.5">
                        Pesanan Saya
                    </a>
                </div>
            @endunless
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($aktivitas)
            <!-- KPI Aktivitas Pelanggan (Gaya Dashboard Admin) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <a href="{{ route('pesanan.index') }}#riwayat-spk" class="bg-surface p-5 rounded-2xl border border-line border-l-4 border-l-accent hover:border-accent transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Pengajuan SPK</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['spk'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">Surat Pesanan Kendaraan</p>
                </a>

                <a href="{{ route('pesanan.index') }}#riwayat-td" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Booking Test Drive</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['testDrive'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">Jadwal uji kemudi unit</p>
                </a>

                <a href="{{ route('pesanan.index') }}#riwayat-svc" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Booking Servis</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['servis'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">Perawatan berkala bengkel</p>
                </a>

                <a href="{{ route('pesanan.index') }}#riwayat-spo" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Order Sparepart</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['sparepart'], 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">Pembelian suku cadang asli</p>
                </a>
            </div>
        @endif

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
