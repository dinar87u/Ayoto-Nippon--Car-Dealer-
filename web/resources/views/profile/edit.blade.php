<x-app-layout>
    @php
        $staf = auth()->user()?->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']) ?? false;
    @endphp

    <x-slot name="header">
        <div>
            <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">
                {{ $staf ? (Auth::user()->branch?->nama ?? 'Akun Staf Dealer') : 'Portal Pelanggan Resmi' }}
            </p>
            <h1 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">
                Halo, {{ \Illuminate\Support\Str::before($user->name, ' ') }}
            </h1>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        @if ($staf)
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <div class="lg:col-span-7 bg-surface rounded-2xl border border-line shadow-sm p-6">
                    @include('profile.partials.update-profile-information-form')
                </div>
                <div class="lg:col-span-5 space-y-6">
                    <div class="bg-surface rounded-2xl border border-line shadow-sm p-6">
                        @include('profile.partials.update-password-form')
                    </div>
                </div>
            </div>
        @else
            @if ($aktivitas)
                <!-- KPI Aktivitas Pelanggan -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <a href="{{ route('pesanan.spk') }}" class="bg-surface p-5 rounded-2xl border border-line border-l-4 border-l-accent hover:border-accent transition-all">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Pengajuan SPK</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['spk'], 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-muted">Surat Pesanan Kendaraan</p>
                    </a>

                    <a href="{{ route('pesanan.test-drive') }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Booking Test Drive</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['testDrive'], 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-muted">Jadwal uji kemudi unit</p>
                    </a>

                    <a href="{{ route('pesanan.servis') }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Booking Servis</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['servis'], 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-muted">Perawatan berkala bengkel</p>
                    </a>

                    <a href="{{ route('pesanan.sparepart') }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Order Sparepart</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($aktivitas['sparepart'], 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-muted">Pembelian suku cadang asli</p>
                    </a>
                </div>
            @endif

            <!-- Grid Utama Dashboard Pelanggan: Kiri (Garasi / Mobil Saya) & Kanan (Servis Terakhir) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
                <!-- Kolom Kiri (7 col): Kendaraan Saya & Detail Nopol / VIN -->
                <section aria-labelledby="judul-kendaraan-saya" class="lg:col-span-7 bg-surface rounded-2xl border border-line shadow-sm overflow-hidden">
                    <div class="px-6 py-4 border-b border-line flex items-center justify-between gap-3">
                        <div>
                            <p class="text-[11px] font-bold uppercase tracking-wider text-accent">Garasi Pelanggan</p>
                            <h2 id="judul-kendaraan-saya" class="text-base font-bold text-ink">Kendaraan Saya &amp; Detail Unit</h2>
                        </div>
                        <a href="{{ route('katalog.index') }}" class="text-xs font-semibold text-accent hover:underline">
                            + Lihat Katalog
                        </a>
                    </div>

                    <div class="divide-y divide-line">
                        @forelse ($kendaraanList ?? [] as $mobil)
                            <div class="p-6 space-y-4">
                                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        @if (! empty($mobil['foto']))
                                            <div class="w-24 h-16 rounded-xl overflow-hidden bg-surface-2 border border-line shrink-0">
                                                <img src="{{ asset($mobil['foto']) }}" alt="{{ $mobil['nama'] }}" class="w-full h-full object-cover" loading="lazy">
                                            </div>
                                        @else
                                            <div class="w-16 h-16 rounded-xl bg-surface-2 border border-line flex items-center justify-center text-muted shrink-0">
                                                <x-nav-icon name="car" />
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="flex flex-wrap items-center gap-2">
                                                <h3 class="text-base font-extrabold text-ink truncate">{{ $mobil['nama'] }}</h3>
                                                <x-status-badge :status="$mobil['status_spk']" />
                                            </div>
                                            <p class="mt-1 text-xs text-muted">
                                                Cabang {{ $mobil['cabang'] }}
                                                @if (! empty($mobil['no_spk']))
                                                    &middot; <a href="{{ route('spk.show', $mobil['no_spk']) }}" class="font-mono font-semibold text-accent hover:underline">{{ $mobil['no_spk'] }}</a>
                                                @endif
                                            </p>
                                        </div>
                                    </div>

                                    <div class="shrink-0">
                                        <span class="inline-flex flex-col items-start sm:items-end px-3.5 py-2 rounded-xl bg-surface-2 border border-line">
                                            <span class="text-[10px] font-semibold uppercase tracking-wider text-muted">Nomor Polisi</span>
                                            <strong class="font-mono text-sm font-black text-ink tracking-wider">{{ $mobil['nopol'] }}</strong>
                                        </span>
                                    </div>
                                </div>

                                <!-- Detail Spesifikasi & Identitas Kendaraan -->
                                <dl class="grid grid-cols-2 sm:grid-cols-4 gap-3 pt-3 border-t border-line text-xs">
                                    <div class="p-3 rounded-xl bg-surface-2/50 border border-line">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Nomor Rangka (VIN)</dt>
                                        <dd class="mt-1 font-mono font-bold text-ink truncate" title="{{ $mobil['vin'] }}">{{ $mobil['vin'] }}</dd>
                                    </div>
                                    <div class="p-3 rounded-xl bg-surface-2/50 border border-line">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Warna &amp; Tahun</dt>
                                        <dd class="mt-1 font-bold text-ink flex items-center gap-1.5">
                                            <span class="w-3 h-3 rounded-full border border-line shrink-0" style="background-color: {{ $mobil['warna_hex'] }}"></span>
                                            <span class="truncate">{{ $mobil['warna'] }} ({{ $mobil['tahun'] }})</span>
                                        </dd>
                                    </div>
                                    <div class="p-3 rounded-xl bg-surface-2/50 border border-line">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Transmisi / BBM</dt>
                                        <dd class="mt-1 font-bold text-ink truncate">{{ $mobil['transmisi'] }} &middot; {{ $mobil['bbm'] }}</dd>
                                    </div>
                                    <div class="p-3 rounded-xl bg-surface-2/50 border border-line">
                                        <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Kilometer Terakhir</dt>
                                        <dd class="mt-1 font-bold text-ink tabular-nums">
                                            {{ ! empty($mobil['km_terakhir']) ? number_format($mobil['km_terakhir'], 0, ',', '.') . ' km' : 'Belum tercatat' }}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                        @empty
                            <div class="p-8 text-center space-y-3">
                                <p class="text-sm font-semibold text-ink">Belum ada data kendaraan terdaftar di akun Anda.</p>
                                <p class="text-xs text-muted max-w-md mx-auto">
                                    Kendaraan Anda akan otomatis tampil di sini setelah mengajukan pemesanan SPK atau melakukan booking servis berkala.
                                </p>
                                <div class="pt-1 flex flex-wrap items-center justify-center gap-2">
                                    <a href="{{ route('katalog.index') }}" class="px-4 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors">
                                        Pilih Mobil di Katalog
                                    </a>
                                    <a href="{{ route('servis.create') }}" class="px-4 py-2 rounded-xl text-xs font-semibold bg-surface border border-line text-ink hover:border-accent transition-colors">
                                        Daftarkan Servis Mobil
                                    </a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </section>

                <!-- Kolom Kanan (5 col): Informasi Servis Terakhir -->
                <div class="lg:col-span-5 space-y-6">
                    <section aria-labelledby="judul-servis-terakhir" class="bg-surface rounded-2xl border border-line shadow-sm overflow-hidden">
                        <div class="px-6 py-4 border-b border-line flex items-center justify-between gap-3">
                            <div>
                                <p class="text-[11px] font-bold uppercase tracking-wider text-accent">Pemantauan Bengkel</p>
                                <h2 id="judul-servis-terakhir" class="text-base font-bold text-ink">Servis Terakhir</h2>
                            </div>
                            <a href="{{ route('pesanan.servis') }}" class="text-xs font-semibold text-accent hover:underline">
                                Lihat Detail Riwayat Servis &rarr;
                            </a>
                        </div>

                        @if ($servisTerakhir)
                            <div class="p-6">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">Terakhir Servis</p>
                                        <p class="mt-1.5 text-xl font-extrabold text-ink tabular-nums">
                                            {{ $servisTerakhir->jadwal->locale('id')->translatedFormat('d F Y') }}
                                        </p>
                                        <p class="mt-0.5 text-sm font-semibold text-muted tabular-nums">
                                            Pukul {{ $servisTerakhir->jadwal->format('H:i') }} WIB &middot; {{ $servisTerakhir->jadwal->locale('id')->diffForHumans() }}
                                        </p>
                                        <p class="mt-1.5 text-xs text-muted">
                                            {{ $servisTerakhir->branch?->nama ?? 'Bengkel Resmi Ayoto Nippon' }}
                                        </p>
                                    </div>
                                    <x-status-badge :status="$servisTerakhir->status" />
                                </div>
                            </div>
                        @else
                            <div class="p-8 text-center space-y-2">
                                <p class="text-sm font-semibold text-ink">Belum ada riwayat servis berkala.</p>
                                <p class="text-xs text-muted">Jadwalkan perawatan berkala kendaraan Anda di bengkel resmi Ayoto Nippon.</p>
                            </div>
                        @endif
                    </section>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
