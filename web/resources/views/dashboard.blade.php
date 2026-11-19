<x-app-layout>
    @php
        $isOwner = Auth::user()->hasRole('owner');
        $isSuperAdmin = Auth::user()->hasRole('superadmin');
        $isSuper = $isOwner || $isSuperAdmin;
        $isManajemen = Auth::user()->hasAnyRole(['superadmin', 'owner', 'pimpinan']);
        $judulDashboard = $isOwner ? 'Dashboard Owner' : ($isSuperAdmin ? 'Dashboard Super Admin' : 'Dashboard');
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">
                    {{ $isSuper ? 'Pusat Kendali Utama' : (Auth::user()->branch?->nama ?? 'Operasional Dealer') }}
                </p>
                <h2 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">
                    {{ $judulDashboard }}
                </h2>
            </div>

            <div class="flex flex-wrap items-center gap-2">
                @hasanyrole('admin-cabang|pimpinan|superadmin|owner')
                    <a href="{{ route('admin.verify.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors inline-flex items-center gap-1.5">
                        Verifikasi SPK
                        @if ($spkMenunggu > 0)
                            <span class="px-1.5 py-0.5 rounded-md bg-white/20 text-[10px] tabular-nums">{{ $spkMenunggu }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.units.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-surface border border-line text-ink hover:border-accent transition-colors">
                        + Tambah Unit
                    </a>
                @endhasanyrole
                @hasanyrole('superadmin|owner')
                    <a href="{{ route('admin.users.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-surface border border-line text-ink hover:border-accent transition-colors">
                        Kelola Staf
                    </a>
                @endhasanyrole
                @hasanyrole('pimpinan|admin-cabang|superadmin|owner')
                    <a href="{{ route('admin.laporan.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-surface border border-line text-ink hover:border-accent transition-colors">
                        Laporan
                    </a>
                @endhasanyrole
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        <!-- KPI Utama -->
        <div class="grid grid-cols-2 {{ isset($mekanikStats) ? 'lg:grid-cols-3' : 'lg:grid-cols-4' }} gap-4">
            @hasanyrole('sales|admin-cabang|pimpinan|superadmin|owner')
                @php
                    $ruteSpkKpi = Auth::user()->hasAnyRole(['admin-cabang', 'pimpinan', 'superadmin', 'owner']) ? route('admin.verify.index') : route('admin.pesanan.index');
                @endphp
                <a href="{{ $ruteSpkKpi }}" class="bg-surface p-5 rounded-2xl border border-line border-l-4 border-l-accent hover:border-accent transition-all">
                    <div class="flex items-center justify-between gap-2">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">SPK menunggu verifikasi</p>
                    </div>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($spkMenunggu, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $spkMenunggu > 0 ? 'Perlu pengecekan bukti bayar' : 'Semua bukti sudah diproses' }}</p>
                </a>

                <a href="{{ route('admin.leads.index') }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Leads baru</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($leadsBaru, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">Prospek masuk menunggu follow-up</p>
                </a>
            @endhasanyrole

            @if (isset($mekanikStats))
                <a href="{{ route('admin.services.index') }}" class="bg-surface p-5 rounded-2xl border border-line border-l-4 border-l-accent hover:border-accent transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Tugas Servis Saya</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ $tugasSaya }}</p>
                    <p class="mt-1 text-xs text-muted">Unit ditugaskan ke Anda</p>
                </a>
                <a href="{{ route('admin.services.index', ['status' => 'Dikerjakan']) }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Sedang Dikerjakan</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ $mekanikStats['dikerjakan'] }}</p>
                    <p class="mt-1 text-xs text-muted">{{ $mekanikStats['antrean'] }} antrean siap dikerjakan</p>
                </a>
                <a href="{{ route('admin.services.index', ['status' => 'Selesai']) }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Selesai Dikerjakan</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ $mekanikStats['selesai'] }}</p>
                    <p class="mt-1 text-xs text-muted">Riwayat servis tuntas</p>
                </a>
            @else
                @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                    <a href="{{ route('admin.services.index') }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Servis aktif</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($servisAktif, 0, ',', '.') }}</p>
                        <p class="mt-1 text-xs text-muted">Antrean &amp; pengerjaan bengkel</p>
                    </a>
                @endhasanyrole

                <a href="{{ Auth::user()->hasAnyRole(['sales', 'admin-cabang', 'pimpinan', 'superadmin', 'owner']) ? route('admin.units.index') : route('katalog.index') }}"
                   class="bg-surface p-5 rounded-2xl border border-line hover:border-ink/40 transition-all">
                    <p class="text-xs font-semibold uppercase tracking-wider text-muted">Unit tersedia</p>
                    <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ number_format($unitTersedia, 0, ',', '.') }}</p>
                    <p class="mt-1 text-xs text-muted">Stok siap kirim ber-VIN aktif</p>
                </a>

                @if (isset($leadsSaya))
                    <div class="bg-surface p-5 rounded-2xl border border-line">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">Leads saya</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ $leadsSaya }}</p>
                        <p class="mt-1 text-xs text-muted">{{ $followupJatuh }} jatuh tempo</p>
                    </div>
                @endif
                @if (isset($spkSaya))
                    <a href="{{ route('pesanan.index') }}" class="bg-surface p-5 rounded-2xl border border-line hover:border-accent transition-all">
                        <p class="text-xs font-semibold uppercase tracking-wider text-muted">SPK saya</p>
                        <p class="mt-2 text-3xl font-extrabold tabular-nums text-ink">{{ $spkSaya }}</p>
                        <p class="mt-1 text-xs font-semibold text-accent">Lihat pesanan saya</p>
                    </a>
                @endif
            @endif
        </div>

        <!-- Grid Konten Utama: Kiri (8 col) & Kanan (4 col) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-start">
            <!-- Kolom Kiri -->
            <div class="{{ isset($mekanikStats) ? 'lg:col-span-12' : 'lg:col-span-8' }} space-y-6">
                @hasanyrole('owner|pimpinan|superadmin|admin-cabang|sales')
                    @if (isset($grafikOmzet))
                        @php
                            $tahunDefault = array_key_last($grafikOmzet['bulanan']) ?? now()->format('Y');
                            $daftarTahunGrafik = array_keys($grafikOmzet['bulanan']);
                            $judulDasarGrafik = $grafikOmzet['judul_dasar'] ?? 'Omzet';
                        @endphp
                        <section class="bg-surface p-6 rounded-2xl border border-line"
                                 x-data="{ level: 'bulan', tahun: '{{ $tahunDefault }}', judul: @js($judulDasarGrafik) }">
                            <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 mb-4">
                                <div>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-accent/10 text-accent mb-1.5">
                                        {{ $grafikOmzet['label_peran'] ?? 'Analitik' }}
                                    </span>
                                    <h3 class="font-bold text-base text-ink"
                                        x-text="level === 'hari' ? judul + ' (14 Hari Terakhir)' : (level === 'bulan' ? judul + ' — Tahun ' + tahun : judul + ' per Tahun')">{{ $judulDasarGrafik }} — Tahun {{ $tahunDefault }}</h3>
                                    <p class="text-xs text-muted mt-0.5">{{ $grafikOmzet['peruntukan'] ?? '' }}</p>
                                </div>
                                <div class="flex flex-wrap items-center gap-2 shrink-0">
                                    @if (count($daftarTahunGrafik) > 1)
                                        <select x-show="level === 'bulan'" x-model="tahun" aria-label="Pilih tahun grafik"
                                                class="bg-surface border border-line rounded-lg text-xs font-semibold text-ink min-h-[36px] pl-3 pr-8">
                                            @foreach ($daftarTahunGrafik as $th)
                                                <option value="{{ $th }}">{{ $th }}</option>
                                            @endforeach
                                        </select>
                                    @endif
                                    <div class="inline-flex items-center p-1 rounded-xl bg-surface-2 border border-line">
                                        <button type="button" @click="level = 'hari'"
                                                :class="level === 'hari' ? 'bg-surface text-ink shadow-xs font-semibold' : 'text-muted hover:text-ink'"
                                                class="px-3 py-1.5 rounded-lg text-xs transition min-h-[32px]">
                                            Per Hari
                                        </button>
                                        <button type="button" @click="level = 'bulan'"
                                                :class="level === 'bulan' ? 'bg-surface text-ink shadow-xs font-semibold' : 'text-muted hover:text-ink'"
                                                class="px-3 py-1.5 rounded-lg text-xs transition min-h-[32px]">
                                            Per Bulan
                                        </button>
                                        <button type="button" @click="level = 'tahun'"
                                                :class="level === 'tahun' ? 'bg-surface text-ink shadow-xs font-semibold' : 'text-muted hover:text-ink'"
                                                class="px-3 py-1.5 rounded-lg text-xs transition min-h-[32px]">
                                            Per Tahun
                                        </button>
                                    </div>
                                    <button type="button" x-show="level === 'bulan'" @click="level = 'tahun'" class="sr-only">
                                        Kembali ke tahun
                                    </button>
                                </div>
                            </div>
                            <div x-show="level === 'hari'" x-cloak>
                                <x-grafik-garis :items="$grafikOmzet['harian']['items']" :skala="$grafikOmzet['harian']['skala']" />
                            </div>
                            <div x-show="level === 'tahun'" x-cloak>
                                <x-grafik-garis :items="$grafikOmzet['tahun']['items']" :skala="$grafikOmzet['tahun']['skala']" :klik="true" />
                            </div>
                            @foreach ($grafikOmzet['bulanan'] as $tahun => $data)
                                <div x-show="level === 'bulan' && tahun === '{{ $tahun }}'" x-cloak>
                                    <x-grafik-garis :items="$data['items']" :skala="$data['skala']" />
                                </div>
                            @endforeach
                        </section>
                    @endif

                    @if (isset($ringkasanCabang) && $ringkasanCabang->isNotEmpty())
                        <section class="bg-surface rounded-2xl border border-line overflow-hidden">
                            <div class="px-5 py-4 border-b border-line flex items-center justify-between gap-3">
                                <div>
                                    <h3 class="font-bold text-base text-ink">Performa Cabang Resmi</h3>
                                    <p class="text-xs text-muted">Ringkasan stok siap kirim, SPK Deal, dan antrean bengkel tiap cabang.</p>
                                </div>
                                <a href="{{ route('admin.laporan.index') }}" class="text-xs font-semibold text-accent hover:underline">Buka Laporan ↗</a>
                            </div>
                            <div class="overflow-x-auto">
                                <table class="w-full text-sm">
                                    <thead>
                                        <tr class="border-b border-line bg-surface-2/60 text-left text-[11px] uppercase tracking-wider text-muted">
                                            <th class="px-5 py-3 font-bold">Cabang</th>
                                            <th class="px-4 py-3 font-bold text-right">Stok Siap</th>
                                            <th class="px-4 py-3 font-bold text-right">SPK Verifikasi</th>
                                            <th class="px-4 py-3 font-bold text-right">SPK Deal</th>
                                            <th class="px-4 py-3 font-bold text-right">Servis Aktif</th>
                                            <th class="px-5 py-3 font-bold text-right">Nilai Unit Deal</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-line">
                                        @foreach ($ringkasanCabang as $c)
                                            <tr class="hover:bg-surface-2/40 transition-colors">
                                                <td class="px-5 py-3.5">
                                                    <span class="font-semibold text-ink">{{ $c['nama'] }}</span>
                                                    <span class="block text-[11px] text-muted font-mono">{{ $c['kode'] }}</span>
                                                </td>
                                                <td class="px-4 py-3.5 text-right font-semibold text-ink tabular-nums">{{ number_format($c['unit_siap'], 0, ',', '.') }}</td>
                                                <td class="px-4 py-3.5 text-right tabular-nums">
                                                    <span class="{{ $c['spk_menunggu'] > 0 ? 'font-bold text-accent' : 'text-muted' }}">{{ $c['spk_menunggu'] }}</span>
                                                </td>
                                                <td class="px-4 py-3.5 text-right font-semibold text-ink tabular-nums">{{ $c['spk_deal'] }}</td>
                                                <td class="px-4 py-3.5 text-right text-muted tabular-nums">{{ $c['servis_aktif'] }}</td>
                                                <td class="px-5 py-3.5 text-right font-bold text-ink tabular-nums">Rp {{ number_format($c['omzet'], 0, ',', '.') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </section>
                    @endif
                @endhasanyrole

                @if (isset($antreanSpk))
                    @php
                        $bisaVerif = Auth::user()->hasAnyRole(['admin-cabang', 'pimpinan', 'superadmin', 'owner']);
                    @endphp
                    <section aria-labelledby="antrean-judul" class="bg-surface rounded-2xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between gap-3">
                            <div>
                                <h3 id="antrean-judul" class="font-bold text-base text-ink">Perlu verifikasi</h3>
                                <p class="text-xs text-muted">SPK menunggu pengecekan pembayaran.</p>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center rounded-full bg-red-50 dark:bg-red-500/10 text-accent text-xs font-semibold px-2.5 py-0.5 tabular-nums">{{ $antreanSpk->count() }} antrean</span>
                                <a href="{{ $bisaVerif ? route('admin.verify.index') : route('admin.pesanan.index') }}" class="text-xs font-semibold text-accent hover:underline">Semua ↗</a>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-line bg-surface-2/60 text-left text-[11px] uppercase tracking-wider text-muted">
                                        <th scope="col" class="px-5 py-3 font-bold">No. SPK</th>
                                        <th scope="col" class="px-4 py-3 font-bold">Pelanggan &amp; Unit</th>
                                        <th scope="col" class="px-5 py-3 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @forelse ($antreanSpk as $order)
                                        <tr class="hover:bg-surface-2/40 transition-colors">
                                            <td class="px-5 py-3.5 font-mono text-xs font-semibold text-ink whitespace-nowrap">{{ $order->no_spk }}</td>
                                            <td class="px-4 py-3.5">
                                                <span class="font-medium text-ink">{{ $order->customer->nama }}</span>
                                                <span class="block text-xs text-muted">{{ $order->branch->nama }} · {{ $order->unit?->variant?->carModel?->nama ?? '-' }}</span>
                                            </td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ $bisaVerif ? route('admin.verify.index') : route('admin.pesanan.index') }}" class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-accent border border-accent/30 hover:bg-accent-strong hover:text-white transition-colors">
                                                    {{ $bisaVerif ? 'Verifikasi' : 'Lihat SPK' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="px-5 py-6 text-center text-sm text-muted">
                                                Tidak ada SPK menunggu verifikasi.
                                                <a href="{{ $bisaVerif ? route('admin.verify.index') : route('admin.pesanan.index') }}" class="font-semibold text-accent underline ml-1">{{ $bisaVerif ? 'Buka verifikasi' : 'Buka pesanan' }}</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if (isset($antreanBengkel))
                    @php
                        $isMekanikOnly = Auth::user()->hasRole('mekanik');
                    @endphp
                    <section class="bg-surface rounded-2xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between gap-3">
                            <div>
                                <h3 class="font-bold text-base text-ink">{{ $isMekanikOnly ? 'Daftar Pengerjaan Servis' : 'Antrean & Pengerjaan Servis Bengkel' }}</h3>
                                <p class="text-xs text-muted">{{ $isMekanikOnly ? 'Kendaraan yang perlu Anda periksa dan kerjakan di bengkel.' : 'Kelola jadwal, estimasi biaya, penugasan mekanik, dan rincian sparepart.' }}</p>
                            </div>
                            <a href="{{ route('admin.services.index') }}" class="text-xs font-semibold text-accent hover:underline">Semua Servis ↗</a>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-line bg-surface-2/60 text-left text-[11px] uppercase tracking-wider text-muted">
                                        <th class="px-5 py-3 font-bold">No. Servis</th>
                                        <th class="px-4 py-3 font-bold">Kendaraan &amp; Keluhan</th>
                                        <th class="px-4 py-3 font-bold">{{ $isMekanikOnly ? 'Rincian Tugas' : 'Mekanik' }}</th>
                                        <th class="px-4 py-3 font-bold">Status</th>
                                        <th class="px-5 py-3 font-bold text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @forelse ($antreanBengkel as $svc)
                                        <tr class="hover:bg-surface-2/40 transition-colors">
                                            <td class="px-5 py-3.5">
                                                <span class="font-mono font-semibold text-ink">{{ $svc->no_svc }}</span>
                                                <span class="block text-xs text-muted tabular-nums">{{ $svc->jadwal?->format('d M Y H:i') }}</span>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                <span class="font-medium text-ink">{{ $svc->nopol }} · {{ $svc->customer->nama ?? '-' }}</span>
                                                <span class="block text-xs text-muted">{{ \Illuminate\Support\Str::limit($svc->keluhan, 50) }}</span>
                                            </td>
                                            <td class="px-4 py-3.5">
                                                @if ($isMekanikOnly)
                                                    <span class="text-xs font-medium text-ink">{{ $svc->details->count() }} item rincian</span>
                                                @elseif ($svc->mekanik)
                                                    <span class="font-medium text-ink">{{ $svc->mekanik->name }}</span>
                                                @else
                                                    <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium bg-amber-500/10 text-amber-700 dark:text-amber-300">Belum ditugaskan</span>
                                                @endif
                                            </td>
                                            <td class="px-4 py-3.5"><x-status-badge :status="$svc->status" /></td>
                                            <td class="px-5 py-3.5 text-right">
                                                <a href="{{ route('admin.services.show', $svc) }}" class="px-3 py-1.5 rounded-lg text-xs font-bold text-white bg-accent-strong hover:bg-[#9E0E19] inline-flex items-center">
                                                    {{ $isMekanikOnly ? 'Kerjakan →' : 'Kelola →' }}
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="px-5 py-6 text-center text-sm text-muted">Tidak ada antrean servis aktif saat ini.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif
            </div>

            <!-- Kolom Kanan: Antrean Divisi, Master Data & Aktivitas -->
            <div class="lg:col-span-4 space-y-6">
                @if (isset($timMekanik))
                    <section class="bg-surface rounded-2xl border border-line p-5 space-y-3">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-base text-ink">Tim Mekanik Cabang</h3>
                            <a href="{{ route('admin.services.index') }}" class="text-xs font-semibold text-accent hover:underline">Tugaskan ↗</a>
                        </div>
                        <div class="divide-y divide-line text-sm">
                            @forelse ($timMekanik as $m)
                                <div class="py-2.5 flex items-center justify-between gap-2">
                                    <div>
                                        <p class="font-semibold text-ink">{{ $m['nama'] }}</p>
                                        <p class="text-xs text-muted">{{ $m['email'] }}</p>
                                    </div>
                                    <div class="text-right text-xs">
                                        <span class="inline-flex px-2 py-0.5 rounded-full font-semibold {{ $m['aktif'] > 0 ? 'bg-blue-500/10 text-blue-700 dark:text-blue-300' : 'bg-surface-2 text-muted' }}">
                                            {{ $m['aktif'] }} aktif
                                        </span>
                                        <span class="block text-muted mt-0.5">{{ $m['selesai'] }} selesai</span>
                                    </div>
                                </div>
                            @empty
                                <p class="text-xs text-muted py-2">Belum ada mekanik terdaftar di cabang ini.</p>
                            @endforelse
                        </div>
                    </section>
                @endif

                @if (isset($statsBengkel))
                    <section class="bg-surface rounded-2xl border border-line p-5 space-y-3">
                        <h3 class="font-bold text-base text-ink">Ringkasan Operasional Bengkel</h3>
                        <div class="divide-y divide-line text-sm">
                            <a href="{{ route('admin.services.index', ['status' => 'Menunggu']) }}" class="py-2.5 flex items-center justify-between hover:text-accent">
                                <span class="text-muted">Booking Menunggu Konfirmasi</span>
                                <strong class="tabular-nums {{ $statsBengkel['menunggu'] > 0 ? 'text-accent' : 'text-ink' }}">{{ $statsBengkel['menunggu'] }}</strong>
                            </a>
                            <a href="{{ route('admin.services.index', ['status' => 'Dikerjakan']) }}" class="py-2.5 flex items-center justify-between hover:text-accent">
                                <span class="text-muted">Sedang Dikonfirmasi / Dikerjakan</span>
                                <strong class="tabular-nums text-ink">{{ $statsBengkel['dikerjakan'] }}</strong>
                            </a>
                            <a href="{{ route('admin.services.index', ['status' => 'Selesai']) }}" class="py-2.5 flex items-center justify-between hover:text-accent">
                                <span class="text-muted">Servis Selesai</span>
                                <strong class="tabular-nums text-ink">{{ $statsBengkel['selesai'] }}</strong>
                            </a>
                            <a href="{{ route('admin.spareparts.index', ['filter' => 'stok-rendah']) }}" class="py-2.5 flex items-center justify-between hover:text-accent">
                                <span class="text-muted">Sparepart Stok Rendah (≤3)</span>
                                <strong class="tabular-nums {{ $statsBengkel['partStokRendah'] > 0 ? 'text-accent' : 'text-ink' }}">{{ $statsBengkel['partStokRendah'] }}</strong>
                            </a>
                            @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                                <a href="{{ route('admin.sparepart-orders.index') }}" class="py-2.5 flex items-center justify-between hover:text-accent">
                                    <span class="text-muted">Order Part Menunggu Bayar</span>
                                    <strong class="tabular-nums {{ $statsBengkel['orderPartMenunggu'] > 0 ? 'text-accent' : 'text-ink' }}">{{ $statsBengkel['orderPartMenunggu'] }}</strong>
                                </a>
                            @endhasanyrole
                        </div>
                    </section>
                @endif
                @if (isset($antreanOperasional))
                    <section class="bg-surface rounded-2xl border border-line p-5 space-y-3">
                        <h3 class="font-bold text-base text-ink">Ringkasan Antrean Cabang</h3>
                        <div class="divide-y divide-line text-sm">
                            <a href="{{ route('admin.test-drives.index') }}" class="py-2.5 flex items-center justify-between gap-2 hover:text-accent transition-colors">
                                <span class="text-muted">Test Drive Menunggu</span>
                                <strong class="tabular-nums {{ $antreanOperasional['testDriveMenunggu'] > 0 ? 'text-accent' : 'text-ink' }}">{{ $antreanOperasional['testDriveMenunggu'] }}</strong>
                            </a>
                            @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                                <a href="{{ route('admin.sparepart-orders.index') }}" class="py-2.5 flex items-center justify-between gap-2 hover:text-accent transition-colors">
                                    <span class="text-muted">Order Sparepart Menunggu Bayar</span>
                                    <strong class="tabular-nums {{ $antreanOperasional['orderPartMenunggu'] > 0 ? 'text-accent' : 'text-ink' }}">{{ $antreanOperasional['orderPartMenunggu'] }}</strong>
                                </a>
                            @endhasanyrole
                            <a href="{{ route('admin.pesanan.index') }}" class="py-2.5 flex items-center justify-between gap-2 hover:text-accent transition-colors">
                                <span class="text-muted">Total SPK Deal</span>
                                <strong class="tabular-nums text-ink">{{ $antreanOperasional['spkDeal'] }}</strong>
                            </a>
                        </div>
                    </section>
                @endif

                @if (isset($followups))
                    <section aria-labelledby="followup-judul" class="bg-surface rounded-2xl border border-line overflow-hidden">
                        <div class="px-5 py-4 border-b border-line flex items-center justify-between gap-3">
                            <div>
                                <h3 id="followup-judul" class="font-bold text-base text-ink">Follow-up jatuh tempo</h3>
                                <p class="text-xs text-muted">Leads yang perlu segera dihubungi.</p>
                            </div>
                            <div class="flex items-center gap-2.5">
                                <span class="inline-flex items-center rounded-full bg-surface-2 text-ink text-xs font-semibold px-2.5 py-0.5 tabular-nums">{{ $followups->count() }} leads</span>
                                <a href="{{ route('admin.leads.index') }}" class="text-xs font-semibold text-accent hover:underline">Semua ↗</a>
                            </div>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-line bg-surface-2/60 text-left text-[11px] uppercase tracking-wider text-muted">
                                        <th scope="col" class="px-5 py-3 font-bold">Leads</th>
                                        <th scope="col" class="px-5 py-3 font-bold text-right">Jatuh tempo</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-line">
                                    @forelse ($followups as $lead)
                                        <tr class="hover:bg-surface-2/40 transition-colors">
                                            <td class="px-5 py-3.5">
                                                <a href="{{ route('admin.leads.show', $lead) }}" class="font-medium text-ink hover:text-accent underline">
                                                    #{{ $lead->id }} {{ $lead->customer->nama ?? '' }}
                                                </a>
                                            </td>
                                            <td class="px-5 py-3.5 text-right text-xs text-muted tabular-nums">{{ $lead->followup_at->format('d M Y H:i') }}</td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="2" class="px-5 py-6 text-center text-sm text-muted">
                                                Tidak ada follow-up jatuh tempo.
                                                <a href="{{ route('admin.leads.index') }}" class="font-semibold text-accent underline ml-1">Buka leads</a>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endif

                @if (isset($masterStats))
                    <section class="bg-surface rounded-2xl border border-line p-5 space-y-4">
                        <div class="flex items-center justify-between">
                            <h3 class="font-bold text-base text-ink">Master Data &amp; Pengaturan</h3>
                            <span class="text-[11px] font-semibold uppercase tracking-wider text-muted">Pusat</span>
                        </div>
                        <div class="grid grid-cols-2 gap-2.5 text-xs">
                            @foreach ([
                                ['admin.brands.index', 'Merek Mobil', $masterStats['brands']],
                                ['admin.models.index', 'Model & Varian', $masterStats['models']],
                                ['admin.branches.index', 'Cabang Resmi', $masterStats['branches']],
                                ['admin.leasings.index', 'Mitra Leasing', $masterStats['leasings']],
                                ['admin.users.index', 'Akun Staf', $masterStats['users']],
                                ['admin.promos.index', 'Promo Aktif', \App\Models\Promo::where('aktif', true)->count()],
                            ] as [$rute, $label, $jumlah])
                                <a href="{{ route($rute) }}" class="p-3 rounded-xl bg-surface-2/60 border border-line hover:border-accent transition-colors">
                                    <span class="block text-muted">{{ $label }}</span>
                                    <strong class="mt-0.5 block text-lg font-extrabold text-ink tabular-nums">{{ $jumlah }}</strong>
                                </a>
                            @endforeach
                        </div>
                    </section>
                @endif

                @if (isset($aktivitasTerbaru))
                    <section class="bg-surface rounded-2xl border border-line p-5 space-y-3">
                        <h3 class="font-bold text-base text-ink">Aktivitas Sistem Terbaru</h3>
                        @if ($aktivitasTerbaru->isEmpty())
                            <p class="text-xs text-muted py-2">Belum ada catatan aktivitas terbaru.</p>
                        @else
                            <div class="divide-y divide-line text-xs">
                                @foreach ($aktivitasTerbaru as $log)
                                    <div class="py-2.5 space-y-0.5">
                                        <div class="flex items-center justify-between gap-2">
                                            <strong class="text-ink">{{ $log->aksi }}</strong>
                                            <span class="text-[11px] text-muted tabular-nums">{{ $log->created_at?->diffForHumans() }}</span>
                                        </div>
                                        @if ($log->deskripsi)
                                            <p class="text-muted leading-snug">{{ $log->deskripsi }}</p>
                                        @endif
                                        <p class="text-[11px] text-muted">Oleh: {{ $log->user?->name ?? 'Sistem' }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </section>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
