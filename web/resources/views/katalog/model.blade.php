<x-site-layout judul="{{ $m->brand->nama }} {{ $m->nama }}" deskripsi="Pilih varian {{ $m->nama }} dengan harga OTR dan estimasi cicilan di Ayoto Nippon.">
    <x-site-header />

    @php
        $semuaUnit = $m->variants->flatMap->units;
        $unitSiap = $semuaUnit->where('status', 'Tersedia');
        $galeri = $semuaUnit->filter(fn ($u) => $u->foto_utama)->unique('foto_utama')->map(fn ($u) => ['foto' => $u->foto_utama, 'warna' => $u->color_id])->values();
        $fotoUtama = $unitSiap->first()?->foto_utama ?? $semuaUnit->first()?->foto_utama;
        $warnaTersedia = $unitSiap->map(fn ($u) => $u->color)->filter()->unique('id')->values();
        $adaStok = $unitSiap->isNotEmpty();
        $tahunKatalog = $semuaUnit->max('tahun_rakit') ?? date('Y');
        $varianUtama = $m->variants->first();
        $spekVarian = $varianUtama ? $varianUtama->spekLengkap() : [];

        $cabangTermurah = null;
        $minOtr = null;
        $minBooking = null;
        foreach ($m->variants->flatMap->priceLists as $pl) {
            if ($minOtr === null || $pl->otr < $minOtr) {
                $minOtr = $pl->otr;
                $cabangTermurah = $pl->branch->nama;
            }
            if ($pl->branch?->booking_fee && ($minBooking === null || $pl->branch->booking_fee < $minBooking)) {
                $minBooking = $pl->branch->booking_fee;
            }
        }
        $estimasi = null;
        if ($minOtr && $bungaMin !== null) {
            $pokok = $minOtr * 0.8;
            $estimasi = (int) round(($pokok + $pokok * $bungaMin / 100 * 48 / 12) / 48);
        }

        $transmisiTeks = $m->variants->pluck('transmisi')->unique()->map(fn ($t) => ['MT' => 'Manual', 'AT' => 'Otomatis', 'CVT' => 'CVT'][$t] ?? $t)->implode(' / ');
        $bbmTeks = $m->variants->pluck('bbm')->unique()->implode(' / ');
        $kursiTeks = ($kategori['kursi'] ?? null) ? $kategori['kursi'].' Kursi' : ($spekVarian['Dimensi dan kapasitas']['Kapasitas tempat duduk'] ?? '-');

        $overviewItems = [
            ['Tipe Body', $kategori['body'] ?? ($m->deskripsi ?: '-'), '<path d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0v4a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-4m-14 0h14"/>'],
            ['Kondisi', 'Unit Baru', '<path d="M9 12l2 2 4-4m5 2a8 8 0 11-16 0 8 8 0 0116 0z"/>'],
            ['Tempat Duduk', $kursiTeks, '<path d="M7 13V6a2 2 0 012-2h6a2 2 0 012 2v7m-10 0h10m-10 0l-1 5h12l-1-5"/>'],
            ['Bagasi', $spekVarian['Dimensi dan kapasitas']['Bagasi'] ?? '-', '<rect x="6" y="7" width="12" height="13" rx="2"/><path d="M10 7V4h4v3"/>'],
            ['Bahan Bakar', $bbmTeks ?: '-', '<path d="M5 20V5a2 2 0 012-2h6a2 2 0 012 2v15M4 20h12M8 7h4v4H8V7zm7 3h1.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 003 0V9.5l-2-2"/>'],
            ['Tenaga Maks.', $spekVarian['Mesin dan performa']['Tenaga maksimum'] ?? '-', '<circle cx="12" cy="12" r="8"/><path d="M12 12l3-3"/>'],
            ['Transmisi', $transmisiTeks ?: '-', '<circle cx="6" cy="6" r="1.5"/><circle cx="12" cy="6" r="1.5"/><circle cx="18" cy="6" r="1.5"/><circle cx="6" cy="18" r="1.5"/><circle cx="12" cy="18" r="1.5"/><path d="M6 7.5v9M12 7.5v9M18 7.5V12H6"/>'],
            ['Penggerak', $spekVarian['Mesin dan performa']['Penggerak'] ?? '-', '<circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M7 15V9m10 6V9M7 12h10"/>'],
            ['Tahun Rakit', (string) $tahunKatalog, '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4m8-4v4M4 10h16"/>'],
            ['Jumlah Varian', $m->variants->count().' Varian', '<path d="M12 3l9 5-9 5-9-5z"/><path d="M3 13l9 5 9-5"/>'],
            ['Unit Siap Kirim', $unitSiap->count().' Unit', '<path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9.5 12l1.8 1.8L15 10"/>'],
            ['Keselamatan', $spekVarian['Kaki-kaki dan keselamatan']['Airbag'] ?? '-', '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/>'],
        ];
    @endphp

    <div class="w-full max-w-[1356px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-10 space-y-10">
        <!-- Detail Layout: Left Gallery + Overview + Features + Variants | Right Sticky Price Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            <!-- Left Top: Photo Gallery + Color Swatches -->
            <div class="order-1 lg:order-none lg:col-span-8 lg:col-start-1 lg:row-start-1 space-y-6">
                <div class="space-y-3">
                    <div class="relative bg-surface border border-line rounded-3xl overflow-hidden shadow-xl shadow-black/5">
                        @if ($fotoUtama)
                            <img id="model-galeri-utama" src="{{ asset($fotoUtama) }}" alt="{{ $m->brand->nama }} {{ $m->nama }}" fetchpriority="high" class="w-full aspect-[16/10] object-cover transition-all duration-300">
                            @if ($galeri->count() > 1)
                                <button type="button" id="model-galeri-prev" aria-label="Foto sebelumnya"
                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/45 hover:bg-black/70 text-white backdrop-blur-md flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" id="model-galeri-next" aria-label="Foto berikutnya"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/45 hover:bg-black/70 text-white backdrop-blur-md flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                                </button>
                            @endif
                        @else
                            <div class="w-full aspect-[16/10] bg-surface flex items-center justify-center text-sm text-muted">Foto menyusul</div>
                        @endif
                    </div>

                    @if ($galeri->count() > 1)
                        <div class="grid grid-cols-4 sm:grid-cols-6 gap-2.5">
                            @foreach ($galeri->take(6) as $i => $item)
                                <button type="button" data-model-galeri="{{ asset($item['foto']) }}" aria-label="Lihat foto {{ $i + 1 }}"
                                    class="rounded-xl overflow-hidden border-2 {{ $i === 0 ? 'border-accent' : 'border-line' }} hover:border-accent/60 focus:border-accent transition-all min-h-[44px] aspect-video bg-surface">
                                    <img src="{{ asset($item['foto']) }}" alt="" loading="lazy" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                @if ($warnaTersedia->isNotEmpty())
                    <div class="p-5 bg-surface border border-line rounded-2xl">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-muted">Pilihan Warna Siap Kirim</h2>
                            <span class="text-xs text-muted">{{ $warnaTersedia->count() }} warna tersedia</span>
                        </div>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach ($warnaTersedia as $warna)
                                <div class="flex flex-col items-center gap-1.5 p-2 rounded-xl border border-line bg-surface min-w-[64px]">
                                    <span class="block w-8 h-8 rounded-full border border-line shadow-inner" style="background-color: {{ $warna->kode_hex }}"></span>
                                    <span class="text-[10px] text-muted font-medium truncate max-w-[64px]">{{ $warna->nama }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            <!-- Left Main Content: Ringkasan, Deskripsi, Fitur & Spesifikasi, Daftar Varian -->
            <div class="order-3 lg:order-none lg:col-span-8 lg:col-start-1 lg:row-start-2 space-y-10">
                <!-- Ringkasan Kendaraan (Boxcars Car Overview) -->
                <section class="space-y-4">
                    <h2 class="text-2xl font-bold text-ink tracking-tight">Ringkasan Kendaraan</h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-10">
                        @foreach ($overviewItems as [$label, $nilai, $ikon])
                            <div class="flex items-center justify-between gap-4 py-3.5 border-b border-line text-sm">
                                <span class="flex items-center gap-2.5 text-muted">
                                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">{!! $ikon !!}</svg>
                                    <span>{{ $label }}</span>
                                </span>
                                <strong class="font-semibold text-ink text-right">{{ $nilai }}</strong>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- Deskripsi -->
                <section class="pt-6 border-t border-line space-y-3">
                    <h2 class="text-2xl font-bold text-ink tracking-tight">Deskripsi</h2>
                    <p class="text-sm sm:text-base text-muted leading-relaxed">
                        {{ $m->brand->nama }} {{ $m->nama }} tersedia dalam {{ $m->variants->count() }} pilihan varian resmi dengan konfigurasi {{ strtolower($m->deskripsi ?: 'unit baru dealer resmi') }}.
                        {{ $ringkasan }}
                        Seluruh unit dilengkapi dokumen nomor rangka (VIN) transparan, opsi pembiayaan leasing mitra resmi, serta jaminan layanan purnajual di jaringan cabang Ayoto Nippon.
                    </p>
                </section>

                <!-- Fitur & Spesifikasi Lengkap (Boxcars Checkmark Columns) -->
                @if (! empty($spekVarian))
                    <section class="pt-6 border-t border-line space-y-6">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <h2 class="text-2xl font-bold text-ink tracking-tight">Fitur &amp; Spesifikasi</h2>
                            @if ($varianUtama)
                                <span class="text-xs text-muted">Acuan varian {{ $varianUtama->nama }}</span>
                            @endif
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                            @foreach ($spekVarian as $grup => $baris)
                                <div>
                                    <h3 class="text-sm font-bold text-ink mb-4">{{ $grup }}</h3>
                                    <ul class="space-y-3">
                                        @foreach ($baris as $label => $nilai)
                                            <li class="flex items-start gap-2.5 text-xs sm:text-sm">
                                                <svg class="w-4 h-4 text-accent shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                <span class="text-muted"><strong class="font-medium text-ink">{{ $label }}:</strong> {{ $nilai }}</span>
                                            </li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-muted">Pilih salah satu varian di bawah untuk membuka kalkulator simulasi kredit dan ketersediaan warna per cabang.</p>
                    </section>
                @endif

                <!-- Daftar Varian Section -->
                <section id="daftar-varian" class="pt-6 border-t border-line space-y-6">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-accent uppercase">Pilihan Trim &amp; Konfigurasi</p>
                        <h2 class="mt-1 text-2xl font-bold text-ink tracking-tight">Daftar Varian {{ $m->nama }} ({{ $m->variants->count() }})</h2>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        @foreach ($m->variants as $variant)
                            @php
                                $unitSiapVarian = $variant->units->where('status', 'Tersedia');
                                $foto = $unitSiapVarian->first()?->foto_utama ?? $variant->units->first()?->foto_utama;
                                $mulaiVarian = $variant->priceLists->min('otr');
                                $cabangVarian = $unitSiapVarian->map(fn ($u) => $u->branch->nama)->unique()->values();
                                $tahunVarian = $unitSiapVarian->max('tahun_rakit') ?? $tahunKatalog;
                            @endphp
                            <a href="{{ route('katalog.show', $variant->slug) }}"
                               class="group bg-surface-2/80 dark:bg-surface border border-line rounded-3xl p-5 flex flex-col justify-between transition-all duration-300 hover:border-accent hover:shadow-2xl hover:shadow-black/5 hover:-translate-y-1 focus-visible:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent">
                                <div class="relative rounded-2xl overflow-hidden bg-surface aspect-[16/10]">
                                    @if ($foto)
                                        <img src="{{ asset($foto) }}" alt="{{ $variant->nama }}" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="h-full w-full bg-surface flex items-center justify-center text-sm text-muted">Foto menyusul</div>
                                    @endif
                                    <div class="absolute top-3 left-3">
                                        <x-status-badge status="{{ $unitSiapVarian->isNotEmpty() ? 'Tersedia' : 'Dipesan' }}" />
                                    </div>
                                </div>

                                <div class="pt-5 flex flex-col flex-1 justify-between">
                                    <div>
                                        <div class="flex items-start justify-between gap-2">
                                            <h3 class="font-extrabold text-xl text-ink group-hover:text-accent transition-colors tracking-tight">{{ $m->nama }} {{ $variant->nama }}</h3>
                                            @if ($tahunVarian)
                                                <span class="text-xs font-semibold text-muted tabular-nums px-2.5 py-0.5 bg-surface border border-line rounded-lg">{{ $tahunVarian }}</span>
                                            @endif
                                        </div>

                                        <div class="mt-4 grid grid-cols-2 gap-3 text-xs">
                                            <div class="px-3 py-2 bg-surface border border-line rounded-xl">
                                                <span class="block text-[10px] uppercase tracking-wider text-muted">Transmisi</span>
                                                <strong class="text-ink font-bold">{{ $variant->transmisi }}</strong>
                                            </div>
                                            <div class="px-3 py-2 bg-surface border border-line rounded-xl">
                                                <span class="block text-[10px] uppercase tracking-wider text-muted">Bahan Bakar</span>
                                                <strong class="text-ink font-bold">{{ $variant->bbm }}</strong>
                                            </div>
                                        </div>

                                        <p class="mt-3 text-xs text-muted">
                                            Tersedia di: <strong class="text-ink">{{ $cabangVarian->isNotEmpty() ? $cabangVarian->join(' dan ') : '-' }}</strong>
                                        </p>
                                    </div>

                                    <div class="mt-5 pt-4 border-t border-line flex items-center justify-between gap-3">
                                        <div>
                                            <p class="text-[11px] text-muted font-medium">Harga OTR Mulai</p>
                                            <p class="text-lg font-extrabold text-ink tabular-nums tracking-tight">
                                                {{ $mulaiVarian ? 'Rp '.number_format($mulaiVarian, 0, ',', '.') : 'Hubungi Cabang' }}
                                            </p>
                                        </div>
                                        <span class="px-4 py-2.5 inline-flex items-center justify-center text-xs font-bold text-ink bg-surface border border-line group-hover:text-white group-hover:bg-accent-strong group-hover:border-accent rounded-xl transition-all duration-200 min-h-[40px]">
                                            Spesifikasi &amp; SPK ↗
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </section>
            </div>

            <!-- Right Sticky Sidebar (Boxcars Sticky Price + Showroom Card) -->
            <aside class="order-2 lg:order-none lg:col-span-4 lg:col-start-9 lg:row-start-1 lg:row-span-2 lg:sticky lg:top-24 space-y-5">
                <div class="bg-surface border border-line rounded-3xl p-6 shadow-xl shadow-black/5 space-y-5">
                    <div>
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-surface-2 border border-line text-ink uppercase tracking-wider">
                                {{ $m->brand->nama }}
                            </span>
                            @if ($cabangStok->isNotEmpty())
                                <span class="text-xs font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-2.5 py-1 rounded-full">
                                    Tersedia di {{ $cabangStok->count() }} cabang
                                </span>
                            @endif
                        </div>

                        <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-ink leading-tight tracking-tight">
                            {{ $m->brand->nama }} {{ $m->nama }}
                        </h1>
                        <p class="mt-1.5 text-sm text-muted">
                            {{ $tahunKatalog }} - {{ $transmisiTeks ?: '-' }} - {{ $bbmTeks ?: '-' }}
                        </p>
                    </div>

                    <!-- Quick 3-metric row -->
                    <div class="py-3.5 border-y border-line grid grid-cols-3 gap-2 text-xs text-muted">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 13V6a2 2 0 012-2h6a2 2 0 012 2v7m-10 0h10m-10 0l-1 5h12l-1-5"/></svg>
                            <span class="truncate">{{ $kursiTeks }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 20V5a2 2 0 012-2h6a2 2 0 012 2v15M4 20h12"/></svg>
                            <span class="truncate">{{ $bbmTeks ?: '-' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="6" cy="6" r="1.5"/><circle cx="12" cy="6" r="1.5"/><circle cx="6" cy="18" r="1.5"/><path stroke-linecap="round" d="M6 7.5v9M12 7.5V12H6"/></svg>
                            <span class="truncate">{{ $transmisiTeks ?: '-' }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-muted font-medium uppercase tracking-wider">
                            Harga On The Road{{ $cabangTermurah ? ' ('.$cabangTermurah.')' : '' }}
                        </p>
                        <p class="mt-1 text-3xl font-black text-ink tabular-nums tracking-tight">
                            {{ $minOtr ? 'Mulai Rp '.number_format($minOtr, 0, ',', '.') : 'Hubungi Cabang' }}
                        </p>
                        @if ($estimasi)
                            <p class="mt-1 text-xs text-muted tabular-nums">
                                Estimasi Rp {{ number_format($estimasi, 0, ',', '.') }}/bln - Tenor 48 bln, DP 20%
                            </p>
                        @endif
                        @if ($minBooking)
                            <div class="mt-3 pt-3 border-t border-line flex items-center justify-between text-xs text-muted">
                                <span>Tanda Jadi (Booking Fee)</span>
                                <strong class="text-accent font-bold tabular-nums">Rp {{ number_format($minBooking, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                        <p class="mt-3 text-xs text-muted leading-relaxed">{{ $ringkasan }}</p>
                    </div>

                    <div class="flex flex-col gap-2.5 pt-1">
                        @if ($varianUtama)
                            <a href="{{ route('katalog.show', $varianUtama->slug) }}"
                               class="w-full py-3.5 px-5 text-center font-bold text-sm text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl shadow-lg shadow-accent/25 transition-all min-h-[48px] flex items-center justify-center">
                                Detail Varian &amp; Ajukan SPK ↗
                            </a>
                            <a href="{{ route('test-drive.create', ['variant_id' => $varianUtama->id]) }}"
                               class="w-full py-3 px-5 text-center font-semibold text-sm text-ink bg-surface border border-line hover:border-accent rounded-xl transition-all min-h-[44px] flex items-center justify-center">
                                Jadwalkan Test Drive ↗
                            </a>
                            <a href="{{ route('katalog.show', $varianUtama->slug) }}#simulasi"
                               class="w-full py-3 px-5 text-center font-semibold text-sm text-ink bg-surface-2 hover:border-accent border border-line rounded-xl transition-all min-h-[44px] flex items-center justify-center">
                                Kalkulator Simulasi Kredit
                            </a>
                        @endif
                    </div>
                </div>

                <div class="bg-surface border border-line rounded-3xl p-6 space-y-4">
                    <h2 class="text-base font-bold text-ink">Alokasi Wilayah &amp; Cabang Resmi</h2>
                    <div class="space-y-2 text-xs sm:text-sm">
                        <div class="flex items-center justify-between py-2 border-b border-line">
                            <span class="text-muted">Unit Siap Kirim</span>
                            <strong class="text-ink tabular-nums">{{ $unitSiap->count() }} unit</strong>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-line gap-3">
                            <span class="text-muted shrink-0">Cabang Tersedia</span>
                            <strong class="text-ink text-right">{{ $cabangStok->isNotEmpty() ? $cabangStok->join(', ') : 'Stok inden' }}</strong>
                        </div>
                    </div>
                    <a href="{{ route('cabang.index') }}" class="w-full py-2.5 px-4 text-center text-xs font-semibold text-ink bg-surface border border-line hover:border-accent rounded-xl transition-colors min-h-[44px] flex items-center justify-center">
                        Lihat Jaringan Cabang ↗
                    </a>
                </div>
            </aside>
        </div>

        @if (($produkSerupa ?? collect())->isNotEmpty())
            <!-- Produk Serupa (Highlight Mobil Setipe) -->
            <section class="pt-10 border-t border-line space-y-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-accent uppercase">
                            {{ ($kategori['body'] ?? null) ? 'Kategori '.$kategori['body'] : 'Rekomendasi Dealer' }}
                        </p>
                        <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">Jelajahi kendaraan serupa</h2>
                    </div>
                    <a href="{{ route('katalog.index', ($kategori['body'] ?? null) ? ['body' => $kategori['body']] : []) }}" class="text-sm font-semibold text-ink hover:text-accent transition-colors">
                        Lihat Semua {{ $kategori['body'] ?? 'Katalog' }} ↗
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    @foreach ($produkSerupa as $serupa)
                        @php
                            $vUtamaSerupa = $serupa->variants->sortBy(fn ($v) => $v->priceLists->min('otr') ?? PHP_INT_MAX)->first() ?? $serupa->variants->first();
                            $unitSiapSerupa = $serupa->variants->flatMap->units->where('status', 'Tersedia');
                            $fotoSerupa = $unitSiapSerupa->first()?->foto_utama ?? $serupa->variants->flatMap->units->first()?->foto_utama;
                            $hargaSerupa = $serupa->variants->flatMap->priceLists->min('otr');
                            $transmisiSerupa = $serupa->variants->pluck('transmisi')->unique()->filter()->join(' / ') ?: '-';
                            $bbmSerupa = $serupa->variants->pluck('bbm')->unique()->filter()->join(' / ') ?: '-';
                            $bodiSerupa = null;
                            $kursiSerupa = null;
                            if ($serupa->deskripsi && preg_match('/^(.*)\s+(\d+)\s+penumpang$/', $serupa->deskripsi, $mSerupa)) {
                                $bodiSerupa = trim($mSerupa[1]);
                                $kursiSerupa = $mSerupa[2].' Kursi';
                            }
                            $tautanSerupa = $vUtamaSerupa ? route('katalog.show', $vUtamaSerupa->slug) : route('katalog.model', [$serupa->brand->slug, $serupa->slug]);
                        @endphp
                        <a href="{{ $tautanSerupa }}"
                           class="group bg-surface-2/80 dark:bg-surface border border-line rounded-3xl p-5 flex flex-col justify-between transition-all duration-300 hover:border-accent hover:shadow-2xl hover:shadow-black/5 hover:-translate-y-1">
                            <div>
                                <div class="relative rounded-2xl overflow-hidden bg-surface aspect-[16/10]">
                                    @if ($fotoSerupa)
                                        <img src="{{ asset($fotoSerupa) }}" alt="{{ $serupa->brand->nama }} {{ $serupa->nama }}" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                    @else
                                        <div class="h-full w-full flex items-center justify-center text-sm text-muted">Foto menyusul</div>
                                    @endif
                                    <div class="absolute top-3 left-3 rounded-full bg-surface/90 backdrop-blur-sm shadow-sm">
                                        <x-status-badge status="{{ $unitSiapSerupa->isNotEmpty() ? 'Tersedia' : 'Dipesan' }}" />
                                    </div>
                                </div>

                                <div class="pt-5">
                                    <p class="text-[11px] font-semibold uppercase tracking-wider text-muted">{{ $serupa->brand->nama }}</p>
                                    <h3 class="mt-0.5 text-xl font-extrabold text-ink group-hover:text-accent transition-colors tracking-tight">
                                        {{ $serupa->brand->nama }} {{ $serupa->nama }}
                                    </h3>

                                    <div class="mt-3 flex flex-wrap gap-1.5 text-xs">
                                        @if ($bodiSerupa)
                                            <span class="px-2.5 py-1 rounded-lg bg-surface border border-line text-ink font-semibold">{{ $bodiSerupa }}</span>
                                        @endif
                                        @if ($kursiSerupa)
                                            <span class="px-2.5 py-1 rounded-lg bg-surface border border-line text-muted">{{ $kursiSerupa }}</span>
                                        @endif
                                        <span class="px-2.5 py-1 rounded-lg bg-surface border border-line text-muted">{{ $transmisiSerupa }}</span>
                                        <span class="px-2.5 py-1 rounded-lg bg-surface border border-line text-muted">{{ $bbmSerupa }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="mt-5 pt-4 border-t border-line flex items-center justify-between gap-3">
                                <div>
                                    <span class="block text-[10px] uppercase tracking-wider text-muted">Harga OTR Mulai</span>
                                    <strong class="text-base font-extrabold text-ink tabular-nums">
                                        {{ $hargaSerupa ? 'Rp '.number_format($hargaSerupa, 0, ',', '.') : 'Hubungi Cabang' }}
                                    </strong>
                                </div>
                                <span class="text-xs font-bold text-accent group-hover:translate-x-0.5 transition-transform">
                                    Lihat Detail ↗
                                </span>
                            </div>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>

    @if ($galeri->count() > 1)
        <script>
            (function () {
                var utama = document.getElementById('model-galeri-utama');
                var thumbs = Array.from(document.querySelectorAll('[data-model-galeri]'));
                var prev = document.getElementById('model-galeri-prev');
                var next = document.getElementById('model-galeri-next');
                var idxAktif = 0;

                function tampilkan(idx) {
                    if (!utama || !thumbs.length) return;
                    idxAktif = (idx + thumbs.length) % thumbs.length;
                    utama.src = thumbs[idxAktif].getAttribute('data-model-galeri');
                    thumbs.forEach(function (b, i) {
                        b.classList.toggle('border-accent', i === idxAktif);
                        b.classList.toggle('border-line', i !== idxAktif);
                    });
                }

                thumbs.forEach(function (btn, idx) {
                    btn.addEventListener('click', function () { tampilkan(idx); });
                });
                if (prev) prev.addEventListener('click', function () { tampilkan(idxAktif - 1); });
                if (next) next.addEventListener('click', function () { tampilkan(idxAktif + 1); });
            })();
        </script>
    @endif

    <x-site-footer />
</x-site-layout>
