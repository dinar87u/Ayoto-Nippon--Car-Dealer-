<x-site-layout judul="{{ $variant->carModel->brand->nama }} {{ $variant->carModel->nama }} {{ $variant->nama }}" deskripsi="Spesifikasi, harga OTR, stok cabang, dan simulasi kredit {{ $variant->nama }} di Ayoto Nippon.">
    <x-site-header />

    @php
        $unitSpk = $variant->units->where('status', 'Tersedia')->first();
        $galeri = $variant->units->filter(fn ($u) => $u->foto_utama)->unique('foto_utama')->map(fn ($u) => ['foto' => $u->foto_utama, 'warna' => $u->color_id])->values();
        $warnaTersedia = $variant->units->where('status', 'Tersedia')->map(fn ($u) => $u->color)->filter()->unique('id')->values();
        $spek = $variant->spekLengkap();
        $mulaiOtr = $variant->priceLists->min('otr');
        $mulaiBooking = $variant->priceLists->map(fn ($p) => $p->branch->booking_fee)->filter()->min();
        // Tombol WhatsApp pembeli -> sales, pesan terisi otomatis sesuai unit.
        $waSales = \App\Support\Wa::chat(
            config('services.wa.sales'),
            'Halo Ayoto Nippon, saya tertarik dengan ' . $variant->carModel->brand->nama . ' ' . $variant->carModel->nama . ' ' . $variant->nama
                . ($unitSpk ? ' (VIN ' . $unitSpk->vin . ')' : '')
                . ($mulaiOtr ? ', harga mulai Rp ' . number_format($mulaiOtr, 0, ',', '.') : '')
                . '. Masih tersedia? Mohon info lengkapnya.'
        );
        $bungaAcuan = $leasings->min('bunga_dasar');
        $angsuranEstimasi = ($mulaiOtr && $bungaAcuan !== null)
            ? (int) round((($mulaiOtr * 0.8) + ($mulaiOtr * 0.8 * $bungaAcuan / 100 * 48 / 12)) / 48)
            : null;
        $kursi = $variant->spesifikasi['kursi'] ?? null;
        $tahunRakit = $variant->units->max('tahun_rakit') ?? date('Y');
        $bodiModel = null;
        if ($variant->carModel->deskripsi && preg_match('/^(.*)\s+\d+\s+penumpang$/', $variant->carModel->deskripsi, $mBodi)) {
            $bodiModel = trim($mBodi[1]);
        }
        $totalStokTersedia = $stok->sum();

        $overviewItems = [
            ['Tipe Bodi', $bodiModel ?: '-', '<path d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0v4a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-4m-14 0h14"/>'],
            ['Kondisi', 'Unit Baru', '<path d="M9 12l2 2 4-4m5 2a8 8 0 11-16 0 8 8 0 0116 0z"/>'],
            ['Tempat Duduk', $kursi ? $kursi.' Orang' : ($spek['Dimensi dan kapasitas']['Kapasitas kursi'] ?? '-'), '<path d="M7 13V6a2 2 0 012-2h6a2 2 0 012 2v7m-10 0h10m-10 0l-1 5h12l-1-5"/>'],
            ['Bagasi', $spek['Dimensi dan kapasitas']['Bagasi'] ?? '-', '<rect x="6" y="7" width="12" height="13" rx="2"/><path d="M10 7V4h4v3"/>'],
            ['Bahan Bakar', $spek['Mesin dan performa']['Bahan bakar'] ?? $variant->bbm, '<path d="M5 20V5a2 2 0 012-2h6a2 2 0 012 2v15M4 20h12M8 7h4v4H8V7zm7 3h1.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 003 0V9.5l-2-2"/>'],
            ['Tenaga Maks.', $spek['Mesin dan performa']['Tenaga maksimum'] ?? '-', '<circle cx="12" cy="12" r="8"/><path d="M12 12l3-3"/>'],
            ['Transmisi', $spek['Mesin dan performa']['Transmisi'] ?? $variant->transmisi, '<circle cx="6" cy="6" r="1.5"/><circle cx="12" cy="6" r="1.5"/><circle cx="18" cy="6" r="1.5"/><circle cx="6" cy="18" r="1.5"/><circle cx="12" cy="18" r="1.5"/><path d="M6 7.5v9M12 7.5v9M18 7.5V12H6"/>'],
            ['Penggerak', $spek['Mesin dan performa']['Penggerak'] ?? '-', '<circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M7 15V9m10 6V9M7 12h10"/>'],
            ['Tahun Rakit', (string) $tahunRakit, '<rect x="4" y="5" width="16" height="15" rx="2"/><path d="M8 3v4m8-4v4M4 10h16"/>'],
            ['Pilihan Warna', $warnaTersedia->isNotEmpty() ? $warnaTersedia->count().' Warna' : 'Sesuai Cabang', '<path d="M12 3a9 9 0 100 18 2 2 0 002-2v-1a2 2 0 012-2h1a4 4 0 004-4 9 9 0 00-9-9z"/>'],
            ['Ketersediaan', $totalStokTersedia > 0 ? $totalStokTersedia.' Unit Siap Kirim' : 'Inden / Dipesan', '<path d="M12 3l7 3v5c0 4.5-3 8.2-7 10-4-1.8-7-5.5-7-10V6l7-3z"/><path d="M9.5 12l1.8 1.8L15 10"/>'],
            ['Keselamatan', $spek['Kaki-kaki dan keselamatan']['Airbag'] ?? '-', '<path d="M12 3l7 3v6c0 4.5-3 7.5-7 9-4-1.5-7-4.5-7-9V6z"/>'],
        ];
    @endphp

    <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 py-8 sm:py-10 space-y-10">
        <!-- Detail Layout: Left Gallery + Overview + Features | Right Sticky Price Sidebar -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 lg:gap-10 items-start">
            <!-- Left Top: Photo Gallery + Color Swatches -->
            <div class="order-1 lg:order-none lg:col-span-8 lg:col-start-1 lg:row-start-1 space-y-6">
                <!-- Photo Gallery -->
                <div class="space-y-3">
                    <div class="relative bg-surface border border-line rounded-3xl overflow-hidden shadow-xl shadow-black/5">
                        @if ($galeri->isNotEmpty())
                            <img id="galeri-utama" src="{{ asset($galeri[0]['foto']) }}" alt="{{ $variant->carModel->brand->nama }} {{ $variant->carModel->nama }} {{ $variant->nama }}" fetchpriority="high" class="w-full aspect-[16/10] object-cover transition-all duration-300">
                            @if ($galeri->count() > 1)
                                <button type="button" id="galeri-prev" aria-label="Foto sebelumnya"
                                    class="absolute left-3.5 top-1/2 -translate-y-1/2 w-10 h-10 rounded-full bg-black/45 hover:bg-black/70 text-white backdrop-blur-md flex items-center justify-center transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                                </button>
                                <button type="button" id="galeri-next" aria-label="Foto berikutnya"
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
                                <button type="button" data-galeri="{{ asset($item['foto']) }}" data-foto-warna="{{ $item['warna'] }}" aria-label="Lihat foto {{ $i + 1 }}"
                                    class="rounded-xl overflow-hidden border-2 {{ $i === 0 ? 'border-accent' : 'border-line' }} hover:border-accent/60 focus:border-accent transition-all min-h-[44px] aspect-video bg-surface">
                                    <img src="{{ asset($item['foto']) }}" alt="" loading="lazy" class="w-full h-full object-cover">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Color Selection Swatches -->
                @if ($warnaTersedia->isNotEmpty())
                    <div class="p-5 bg-surface border border-line rounded-2xl">
                        <div class="flex items-center justify-between mb-3">
                            <h2 class="text-xs font-semibold uppercase tracking-wider text-muted">Pilihan Warna Siap Kirim</h2>
                            <span class="text-xs text-muted">Klik untuk filter alokasi</span>
                        </div>
                        <div class="flex flex-wrap gap-2.5">
                            @foreach ($warnaTersedia as $warna)
                                <button type="button" data-warna="{{ $warna->id }}" data-nama="{{ $warna->nama }}" aria-pressed="false" title="Pilih warna {{ $warna->nama }}"
                                    class="flex flex-col items-center gap-1.5 p-2 rounded-xl border border-line hover:border-accent bg-surface transition-all min-w-[64px] min-h-[44px]">
                                    <span class="swatch block w-8 h-8 rounded-full border border-line shadow-inner" style="background-color: {{ $warna->kode_hex }}"></span>
                                    <span class="text-[10px] text-muted font-medium truncate max-w-[64px]">{{ $warna->nama }}</span>
                                </button>
                            @endforeach
                        </div>
                        <p id="warna-pilihan" class="hidden mt-3 text-xs text-accent font-medium"></p>
                    </div>
                @endif
            </div>

            <!-- Left Main Content: Ringkasan, Deskripsi, Fitur, Simulasi, Stok Cabang -->
            <div class="order-3 lg:order-none lg:col-span-8 lg:col-start-1 lg:row-start-2 space-y-10">
                <!-- Ringkasan Kendaraan (Boxcars Car Overview) -->
                <section class="space-y-4">
                    <h2 class="text-2xl font-bold text-ink tracking-tight">Ringkasan Kendaraan<span class="sr-only"> - Ringkasan Performa</span></h2>
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
                        {{ $variant->carModel->brand->nama }} {{ $variant->carModel->nama }} {{ $variant->nama }} hadir sebagai unit baru resmi dengan transmisi {{ $spek['Mesin dan performa']['Transmisi'] ?? $variant->transmisi }} dan pilihan bahan bakar {{ strtolower($spek['Mesin dan performa']['Bahan bakar'] ?? $variant->bbm) }}.
                        @if ($variant->carModel->deskripsi)
                            Konfigurasi {{ strtolower($variant->carModel->deskripsi) }} ini dipersiapkan dengan standar pengecekan dealer resmi Ayoto Nippon dan catatan nomor rangka (VIN) yang transparan.
                        @endif
                        @if ($mulaiOtr)
                            Tersedia mulai harga OTR Rp {{ number_format($mulaiOtr, 0, ',', '.') }} dengan opsi pembiayaan leasing mitra resmi dan layanan serah terima langsung di cabang Jakarta maupun Bandung.
                        @endif
                    </p>
                </section>

                <!-- Fitur & Spesifikasi Lengkap (Boxcars Checkmark Columns) -->
                <section class="pt-6 border-t border-line space-y-6">
                    <h2 class="text-2xl font-bold text-ink tracking-tight">Fitur &amp; Spesifikasi</h2>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                        @foreach ($spek as $grup => $baris)
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
                    <p class="text-xs text-muted">Keterangan spesifikasi bersumber dari brosur pabrikan resmi. Konsultasikan dengan staf dealer kami untuk detail pembaruan teranyar.</p>
                </section>

                <!-- Interactive Loan Calculator (Collapsible / Toggleable) -->
                <section id="simulasi" aria-labelledby="simulasi-judul" class="border border-line bg-surface rounded-3xl p-6 sm:p-8 shadow-xl" hidden>
            <div class="pb-6 border-b border-line">
                <p class="text-xs font-semibold tracking-wider text-accent uppercase">Fasilitas Finansial</p>
                <h2 id="simulasi-judul" tabindex="-1" class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">Kalkulator Simulasi Kredit</h2>
                <p class="mt-1 text-sm text-muted">{{ $variant->carModel->brand->nama }} {{ $variant->carModel->nama }} {{ $variant->nama }}</p>
            </div>

            @php $bungaAcuan = $leasings->min('bunga_dasar'); @endphp
            @if ($variant->priceLists->isNotEmpty() && $leasings->isNotEmpty())
                <div class="mt-6 grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    <form id="simulasi-form" action="{{ route('simulasi.hitung') }}" method="POST" class="lg:col-span-7 space-y-6">
                        @csrf
                        <input type="hidden" name="variant_id" value="{{ $variant->id }}">

                        <div>
                            <label for="sim-branch" class="block text-xs font-semibold uppercase tracking-wider text-muted">Pilih Cabang OTR Pengiriman</label>
                            <select id="sim-branch" name="branch_id" required 
                                class="mt-2 w-full bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[48px] px-3.5">
                                @foreach ($variant->priceLists as $price)
                                    <option value="{{ $price->branch_id }}" data-otr="{{ $price->otr }}">{{ $price->branch->nama }} (OTR Rp {{ number_format($price->otr, 0, ',', '.') }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Uang Muka (DP Minimum 20%)</span>
                                <span id="sim-dp-persen-label" class="text-xs font-bold text-accent tabular-nums">20% OTR</span>
                            </div>

                            <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-4 gap-2" role="group" aria-label="Persen DP">
                                @foreach ([20, 25, 30, 40] as $p)
                                    <button type="button" data-dp-persen="{{ $p }}" 
                                        class="p-3 text-left bg-surface border border-line rounded-xl min-h-[44px] hover:border-accent transition-colors">
                                        <span class="block text-sm font-bold text-ink tabular-nums">{{ $p }}%</span>
                                        <span data-dp-rp="{{ $p }}" class="block text-xs text-muted tabular-nums mt-0.5">-</span>
                                    </button>
                                @endforeach
                            </div>

                            <div class="mt-3 flex items-center gap-3">
                                <span class="text-sm font-semibold text-muted">Rp</span>
                                <input id="sim-dp" type="number" name="dp" min="0" required 
                                    class="flex-1 bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5 tabular-nums">
                            </div>

                            <style>
                                #sim-dp-range { -webkit-appearance: none; appearance: none; height: 6px; border-radius: 9999px; background: rgb(var(--line)); outline-offset: 4px; }
                                #sim-dp-range::-webkit-slider-thumb { -webkit-appearance: none; appearance: none; width: 22px; height: 22px; border-radius: 9999px; background: rgb(var(--accent-strong)); border: 2px solid rgb(var(--surface)); cursor: pointer; box-shadow: 0 0 10px rgba(225,29,72,0.5); }
                                #sim-dp-range::-moz-range-track { height: 6px; border-radius: 9999px; background: rgb(var(--line)); }
                                #sim-dp-range::-moz-range-thumb { width: 22px; height: 22px; border-radius: 9999px; background: rgb(var(--accent-strong)); border: 2px solid rgb(var(--surface)); cursor: pointer; }
                            </style>
                            <div class="min-h-[44px] flex items-center mt-3">
                                <input id="sim-dp-range" type="range" min="0" max="100" value="20" aria-label="Geser persen DP" class="w-full">
                            </div>
                            <p id="sim-dp-hint" class="mt-1 text-xs text-muted"></p>
                        </div>

                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-muted">Jangka Waktu Tenor</span>
                            <div class="mt-2.5 grid grid-cols-2 sm:grid-cols-4 gap-2" role="group" aria-label="Tenor">
                                @foreach ([12, 24, 36, 48] as $t)
                                    <button type="button" data-tenor="{{ $t }}" 
                                        class="p-3 text-left bg-surface border border-line rounded-xl min-h-[44px] hover:border-accent transition-colors">
                                        <span class="block text-sm font-bold text-ink tabular-nums">{{ $t }} Bulan</span>
                                        <span class="block text-xs text-muted mt-0.5">{{ $t / 12 }} Tahun</span>
                                    </button>
                                @endforeach
                            </div>
                            <input type="hidden" id="sim-tenor" name="tenor" value="12">
                        </div>

                        <div>
                            <span class="block text-xs font-semibold uppercase tracking-wider text-muted">Lembaga Pembiayaan Mitra Resmi</span>
                            <div class="mt-2.5 grid grid-cols-1 sm:grid-cols-2 gap-2" role="group" aria-label="Leasing">
                                <input type="hidden" id="sim-leasing" name="leasing_id" value="{{ $leasings->first()->id }}">
                                @foreach ($leasings as $leasing)
                                    <button type="button" data-leasing="{{ $leasing->id }}" data-dp-min="{{ $leasing->dp_min_persen }}" data-bunga="{{ $leasing->bunga_dasar }}" 
                                        aria-pressed="{{ $loop->first ? 'true' : 'false' }}" 
                                        class="text-left border rounded-xl p-3.5 min-h-[44px] transition-all {{ $loop->first ? 'border-accent bg-accent-strong/10' : 'border-line bg-surface hover:border-accent' }}">
                                        <span class="block text-xs font-bold text-ink">{{ $leasing->nama }}</span>
                                        <span class="block text-xs text-muted mt-0.5">Bunga {{ $leasing->bunga_dasar }}% p.a., DP min {{ $leasing->dp_min_persen }}%
                                            @if ($leasing->bunga_dasar == $bungaAcuan)
                                                <span class="ml-1 px-1.5 py-0.5 text-[9px] font-bold text-white bg-accent-strong rounded">Terbaik</span>
                                            @endif
                                        </span>
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <p id="simulasi-error" class="hidden text-sm font-medium text-accent bg-rose-500/10 border border-rose-500/20 p-3 rounded-xl" role="alert"></p>
                    </form>

                    <!-- Summary Panel -->
                    <div class="lg:col-span-5 bg-surface border border-line rounded-3xl p-6 shadow-xl">
                        <div class="flex items-center justify-between pb-4 border-b border-line">
                            <h3 class="text-sm font-bold text-ink uppercase tracking-wider">Ringkasan Pembiayaan</h3>
                            <span id="ring-tenor" class="text-xs font-semibold text-accent tabular-nums"></span>
                        </div>

                        <div id="simulasi-hasil" class="mt-4" aria-live="polite">
                            <p class="text-sm text-muted">Pilih parameter pembiayaan lalu klik hitung.</p>
                        </div>

                        <dl class="mt-4 text-xs sm:text-sm border-t border-line pt-4 hidden space-y-2.5" id="ring-rincian">
                            <div class="flex justify-between py-1 border-b border-line">
                                <dt class="text-muted">Harga OTR Kendaraan</dt>
                                <dd id="ring-otr" class="font-bold tabular-nums text-ink">-</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-line">
                                <dt class="text-muted">Uang Muka (DP)</dt>
                                <dd id="ring-dp" class="font-bold tabular-nums text-ink">-</dd>
                            </div>
                            <div class="flex justify-between py-1 border-b border-line">
                                <dt class="text-muted">Pokok Hutang</dt>
                                <dd id="ring-pokok" class="font-bold tabular-nums text-ink">-</dd>
                            </div>
                            <div class="flex justify-between py-1">
                                <dt class="text-muted">Suku Bunga Flat</dt>
                                <dd id="ring-bunga" class="font-bold tabular-nums text-ink">-</dd>
                            </div>
                        </dl>

                        <div id="ring-angsuran-wrap" class="hidden mt-6 p-4 bg-surface border border-line rounded-2xl text-center">
                            <p class="text-[11px] text-muted font-medium uppercase tracking-wider">Estimasi Angsuran Bulanan</p>
                            <p class="mt-1 text-2xl sm:text-3xl font-black text-accent tabular-nums">
                                <span id="ring-angsuran">Rp 0</span>
                                <span class="text-xs font-medium text-muted">/bulan</span>
                            </p>
                        </div>

                        <button type="submit" form="simulasi-form" id="sim-submit" 
                            class="mt-6 w-full py-3.5 px-6 font-bold text-sm text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl shadow-lg shadow-accent/25 transition-all min-h-[48px] flex items-center justify-center">
                            Hitung Simulasi
                        </button>
                        <a id="sim-lanjut" href="#" 
                            class="hidden mt-6 w-full py-3.5 px-6 font-bold text-sm text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl shadow-lg shadow-accent/25 transition-all min-h-[48px] flex items-center justify-center">
                            Lanjut ke Pengajuan SPK
                        </a>
                        <p class="mt-4 text-[11px] text-muted leading-relaxed text-center">
                            Hasil kalkulasi adalah estimasi referensi. Sales consultant cabang kami akan mengonfirmasi rincian akhir saat penerbitan SPK.
                        </p>
                    </div>
                </div>
            @else
                <p class="mt-4 text-sm text-muted">Simulasi pembiayaan belum tersedia untuk varian ini.</p>
            @endif
        </section>

        <!-- Branch Stock & Allocation Table -->
        <section class="space-y-6 pt-6 border-t border-line">
            <div class="pb-4 border-b border-line">
                <p class="text-xs font-semibold tracking-wider text-accent uppercase">Alokasi Dealer</p>
                <h2 class="mt-1 text-2xl font-bold text-ink tracking-tight">Harga OTR &amp; Ketersediaan Stok Cabang</h2>
            </div>

            <div class="overflow-x-auto bg-surface border border-line rounded-3xl shadow-xl">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface text-muted text-xs uppercase tracking-wider">
                            <th class="text-left font-bold px-6 py-4">Cabang Dealer</th>
                            <th class="text-left font-bold px-6 py-4">Pilihan Warna Fisik</th>
                            <th class="text-right font-bold px-6 py-4">Harga OTR Resmi</th>
                            <th class="text-center font-bold px-6 py-4">Ketersediaan</th>
                            <th class="text-right font-bold px-6 py-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($variant->priceLists as $price)
                            <tr class="hover:bg-surface-2 transition-colors">
                                <td class="px-6 py-4 font-semibold text-ink">{{ $price->branch->nama }}</td>
                                <td class="px-6 py-4 text-muted text-xs">{{ ($warnaPerCabang[$price->branch_id] ?? collect())->join(', ') ?: 'Stok belum tersedia' }}</td>
                                <td class="px-6 py-4 text-right font-extrabold text-ink tabular-nums">Rp {{ number_format($price->otr, 0, ',', '.') }}</td>
                                <td class="px-6 py-4 text-center">
                                    <x-status-badge status="{{ ($stok[$price->branch_id] ?? 0) > 0 ? 'Tersedia' : 'Dipesan' }}" />
                                </td>
                                <td class="px-6 py-4 text-right">
                                    @if (($stok[$price->branch_id] ?? 0) > 0)
                                        <a href="{{ route('spk.create', ['variant_id' => $variant->id, 'branch_id' => $price->branch_id]) }}" class="inline-flex items-center text-xs font-semibold text-accent hover:text-accent min-h-[44px]">
                                            Ajukan SPK &rarr;
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </section>
            </div>

            <!-- Right Sticky Sidebar (Boxcars Sticky Price + Showroom Card) -->
            <aside class="order-2 lg:order-none lg:col-span-4 lg:col-start-9 lg:row-start-1 lg:row-span-2 lg:sticky lg:top-24 space-y-5">
                <div class="bg-surface border border-line rounded-3xl p-6 shadow-xl shadow-black/5 space-y-5">
                    <div>
                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-surface-2 border border-line text-ink uppercase tracking-wider">
                            {{ $totalStokTersedia > 0 ? 'Unit Baru Tersedia' : 'Pemesanan Inden' }}
                        </span>
                        <h1 class="mt-3 text-2xl sm:text-3xl font-extrabold text-ink leading-tight tracking-tight">
                            {{ $variant->carModel->brand->nama }} {{ $variant->carModel->nama }} {{ $variant->nama }}
                        </h1>
                        <p class="mt-1.5 text-sm text-muted">
                            {{ $tahunRakit }} - {{ $spek['Mesin dan performa']['Transmisi'] ?? $variant->transmisi }} - {{ $spek['Mesin dan performa']['Bahan bakar'] ?? $variant->bbm }}
                        </p>
                    </div>

                    @if (($varianModel ?? collect())->count() > 1)
                        <div class="pt-3 border-t border-line space-y-2">
                            <span class="block text-xs font-semibold uppercase tracking-wider text-muted">Pilih Varian ({{ $varianModel->count() }})</span>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach ($varianModel as $vItem)
                                    @php $aktifVarian = $vItem->id === $variant->id; @endphp
                                    <a href="{{ route('katalog.show', $vItem->slug) }}"
                                       class="px-3 py-2 rounded-xl border text-xs transition-all {{ $aktifVarian ? 'border-accent bg-accent-strong/10 text-ink font-bold' : 'border-line bg-surface hover:border-accent text-muted' }}">
                                        <span class="block truncate text-ink font-semibold">{{ $vItem->nama }}</span>
                                        <span class="block text-[10px] text-muted">{{ $vItem->transmisi }} - {{ $vItem->bbm }}</span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Quick 3-metric row -->
                    <div class="py-3.5 border-y border-line grid grid-cols-3 gap-2 text-xs text-muted">
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7 13V6a2 2 0 012-2h6a2 2 0 012 2v7m-10 0h10m-10 0l-1 5h12l-1-5"/></svg>
                            <span class="truncate">{{ $kursi ? $kursi.' Kursi' : '-' }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 20V5a2 2 0 012-2h6a2 2 0 012 2v15M4 20h12"/></svg>
                            <span class="truncate">{{ $variant->bbm }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 min-w-0">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><circle cx="6" cy="6" r="1.5"/><circle cx="12" cy="6" r="1.5"/><circle cx="6" cy="18" r="1.5"/><path stroke-linecap="round" d="M6 7.5v9M12 7.5V12H6"/></svg>
                            <span class="truncate">{{ $variant->transmisi }}</span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs text-muted font-medium uppercase tracking-wider">Harga OTR Mulai</p>
                        <p class="mt-1 text-3xl font-black text-ink tabular-nums tracking-tight">
                            {{ $mulaiOtr ? 'Rp '.number_format($mulaiOtr, 0, ',', '.') : 'Mengikuti Cabang' }}
                        </p>
                        @if ($angsuranEstimasi)
                            <p class="mt-1 text-xs text-muted tabular-nums">
                                Estimasi mulai Rp {{ number_format($angsuranEstimasi, 0, ',', '.') }}/bln - DP 20%, 48 bln
                            </p>
                        @endif
                        @if ($mulaiBooking)
                            <div class="mt-3 pt-3 border-t border-line flex items-center justify-between text-xs text-muted">
                                <span>Tanda Jadi (Booking Fee)</span>
                                <strong class="text-accent font-bold tabular-nums">Rp {{ number_format($mulaiBooking, 0, ',', '.') }}</strong>
                            </div>
                        @endif
                    </div>

                    <div class="hidden lg:flex flex-col gap-2.5 pt-1">
                        <a data-cta-spk href="{{ route('spk.create', array_filter(['unit_id' => $unitSpk?->id, 'variant_id' => $variant->id])) }}"
                           class="w-full py-3.5 px-5 text-center font-bold text-sm text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl shadow-lg shadow-accent/25 transition-all min-h-[48px] flex items-center justify-center">
                            Ajukan Pemesanan SPK ↗
                        </a>

                        <a data-cta-testdrive href="{{ route('test-drive.create', ['variant_id' => $variant->id]) }}"
                            class="w-full py-3 px-5 text-center font-semibold text-sm text-ink bg-surface border border-line hover:border-accent rounded-xl transition-all min-h-[44px] flex items-center justify-center">
                            Jadwalkan Test Drive ↗
                        </a>

                        <button type="button" data-simulasi-toggle aria-expanded="false" aria-controls="simulasi"
                            class="w-full py-3 px-5 text-center font-semibold text-sm text-ink bg-surface-2 hover:border-accent border border-line rounded-xl transition-all min-h-[44px]">
                            Kalkulator Simulasi Kredit
                        </button>
                    </div>
                </div>

                @if ($variant->priceLists->isNotEmpty())
                    <div class="bg-surface border border-line rounded-3xl p-6 space-y-4">
                        <h2 class="text-base font-bold text-ink">Kunjungi Cabang Resmi</h2>
                        <div class="space-y-3 text-xs sm:text-sm">
                            @foreach ($variant->priceLists as $price)
                                <div class="pb-3 border-b border-line last:border-b-0 last:pb-0">
                                    <div class="flex items-center justify-between gap-2">
                                        <strong class="text-ink">{{ $price->branch->nama }}</strong>
                                        <span class="text-xs font-bold text-ink tabular-nums">Rp {{ number_format($price->otr, 0, ',', '.') }}</span>
                                    </div>
                                    <p class="mt-0.5 text-xs text-muted">
                                        {{ ($stok[$price->branch_id] ?? 0) > 0 ? ($stok[$price->branch_id]).' unit siap kirim' : 'Stok inden cabang' }}
                                    </p>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('cabang.index') }}" class="w-full py-2.5 px-4 text-center text-xs font-semibold text-ink bg-surface border border-line hover:border-accent rounded-xl transition-colors min-h-[44px] flex items-center justify-center">
                            Lihat Jaringan Cabang ↗
                        </a>
                    </div>
                @endif
            </aside>
        </div>

        @if (($produkSerupa ?? collect())->isNotEmpty())
            <!-- Produk Serupa (Highlight Mobil Setipe) -->
            <section class="pt-10 border-t border-line space-y-6">
                <div class="flex flex-wrap items-end justify-between gap-4">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-accent uppercase">
                            {{ $bodiModel ? 'Kategori '.$bodiModel : 'Rekomendasi Dealer' }}
                        </p>
                        <h2 class="mt-1 text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">Jelajahi kendaraan serupa</h2>
                    </div>
                    <a href="{{ route('katalog.index', $bodiModel ? ['body' => $bodiModel] : []) }}" class="text-sm font-semibold text-ink hover:text-accent transition-colors">
                        Lihat Semua {{ $bodiModel ?: 'Katalog' }} ↗
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

    <!-- Tombol WhatsApp mengapung: pembeli -> sales, pesan terisi otomatis -->
    <a href="{{ $waSales }}" target="_blank" rel="noopener"
        aria-label="Chat sales via WhatsApp"
        class="fixed right-4 bottom-[88px] lg:bottom-6 z-40 flex items-center justify-center w-14 h-14 lg:w-auto lg:px-4 lg:h-12 rounded-full bg-[#25D366] text-white shadow-lg shadow-black/20 hover:scale-105 active:scale-95 transition-transform">
        <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/>
        </svg>
        <span class="hidden lg:inline text-sm font-bold whitespace-nowrap ml-2">Chat Sales</span>
    </a>

    <!-- Mobile Bottom Fixed Bar -->
    <div class="lg:hidden sticky bottom-0 bg-surface/95 backdrop-blur-xl border-t border-line p-3 flex gap-2 z-30">
        <a data-cta-spk href="{{ route('spk.create', array_filter(['unit_id' => $unitSpk?->id, 'variant_id' => $variant->id])) }}" 
           class="flex-1 px-4 py-3 text-center text-sm font-bold text-white bg-accent-strong rounded-xl min-h-[44px] flex items-center justify-center shadow-lg shadow-accent/20">
            Ajukan SPK
        </a>
        <button type="button" data-simulasi-toggle aria-expanded="false" aria-controls="simulasi" 
            class="px-4 py-3 text-center text-xs font-semibold text-ink bg-surface border border-line rounded-xl min-h-[44px]">
            Simulasi
        </button>
        <a data-cta-testdrive href="{{ route('test-drive.create', ['variant_id' => $variant->id]) }}" 
            class="px-4 py-3 text-center text-xs font-semibold text-ink bg-surface border border-line rounded-xl min-h-[44px] flex items-center justify-center">
            Test Drive
        </a>
    </div>

    <x-site-footer />

    <!-- Interactive Script (preserves all calculations and state binding) -->
    <script>
        (function () {
            const panel = document.getElementById('simulasi');
            if (!panel) return;
            const toggles = document.querySelectorAll('[data-simulasi-toggle]');
            const judul = document.getElementById('simulasi-judul');
            function buka(tampilkan) {
                panel.hidden = !tampilkan;
                toggles.forEach((t) => t.setAttribute('aria-expanded', tampilkan ? 'true' : 'false'));
                if (tampilkan) {
                    panel.scrollIntoView({ behavior: 'smooth' });
                    judul.focus();
                }
            }
            toggles.forEach((t) => t.addEventListener('click', () => buka(panel.hidden)));
            const utama = document.getElementById('galeri-utama');
            const thumbBtns = Array.from(document.querySelectorAll('[data-galeri]'));
            const prevBtn = document.getElementById('galeri-prev');
            const nextBtn = document.getElementById('galeri-next');
            let galeriIndex = 0;

            function tampilkanFoto(idx) {
                if (!utama || !thumbBtns.length) return;
                galeriIndex = (idx + thumbBtns.length) % thumbBtns.length;
                utama.src = thumbBtns[galeriIndex].dataset.galeri;
                thumbBtns.forEach((b, i) => {
                    b.classList.toggle('border-accent', i === galeriIndex);
                    b.classList.toggle('border-line', i !== galeriIndex);
                });
            }

            function fotoWarna(idWarna) {
                const idx = thumbBtns.findIndex((b) => String(b.dataset.fotoWarna) === String(idWarna));
                return idx >= 0 ? idx : -1;
            }
            thumbBtns.forEach((btn, i) => {
                btn.addEventListener('click', () => tampilkanFoto(i));
            });
            if (prevBtn) prevBtn.addEventListener('click', () => tampilkanFoto(galeriIndex - 1));
            if (nextBtn) nextBtn.addEventListener('click', () => tampilkanFoto(galeriIndex + 1));
            const spkBase = "{{ route('spk.create') }}";
            const tdBase = "{{ route('test-drive.create') }}";
            const varianId = "{{ $variant->id }}";
            const unitPerWarna = @json($variant->units->where('status', 'Tersedia')->groupBy('color_id')->map(fn ($g) => ['unit_id' => $g->first()->id, 'branch_id' => $g->first()->branch_id]));
            const warnaBtns = Array.from(document.querySelectorAll('[data-warna]'));
            const spkCtas = Array.from(document.querySelectorAll('[data-cta-spk]'));
            const tdCtas = Array.from(document.querySelectorAll('[data-cta-testdrive]'));
            const spkAwal = new Map(spkCtas.map((a) => [a, a.getAttribute('href')]));
            const infoWarna = document.getElementById('warna-pilihan');
            let warna = null;
            function unitWarna(idWarna) {
                return (typeof unitPerWarna !== 'undefined' && unitPerWarna[idWarna]) ? unitPerWarna[idWarna] : null;
            }
            function syncCta() {
                spkCtas.forEach((a) => {
                    if (!warna) {
                        a.setAttribute('href', spkAwal.get(a));
                        return;
                    }
                    let href = spkBase + '?variant_id=' + varianId + '&color_id=' + warna;
                    const u = unitWarna(warna);
                    if (u) {
                        href += '&unit_id=' + u.unit_id + '&branch_id=' + u.branch_id;
                    }
                    a.setAttribute('href', href);
                });
                tdCtas.forEach((a) => {
                    let href = tdBase + '?variant_id=' + varianId;
                    if (warna) {
                        const u = unitWarna(warna);
                        href += '&color_id=' + warna;
                        if (u) {
                            href += '&branch_id=' + u.branch_id;
                        }
                    }
                    a.setAttribute('href', href);
                });
                warnaBtns.forEach((b) => {
                    const aktif = b.dataset.warna === warna;
                    b.setAttribute('aria-pressed', aktif ? 'true' : 'false');
                    const swatch = b.querySelector('.swatch');
                    if (swatch) {
                        swatch.style.outline = aktif ? '2px solid #C1121F' : '';
                        swatch.style.outlineOffset = aktif ? '2px' : '';
                    }
                });
                if (utama && thumbBtns.length) {
                    if (warna) {
                        const cocokIdx = fotoWarna(warna);
                        if (cocokIdx >= 0) tampilkanFoto(cocokIdx);
                    } else {
                        tampilkanFoto(0);
                    }
                }
                thumbBtns.forEach((b) => {
                    b.classList.toggle('opacity-40', !!(warna && String(b.dataset.fotoWarna) !== String(warna)));
                });
                if (infoWarna) {
                    if (warna) {
                        const btn = warnaBtns.find((b) => b.dataset.warna === warna);
                        infoWarna.textContent = 'Warna dipilih: ' + (btn ? btn.dataset.nama : '') + '. Pengajuan SPK dan Test Drive akan dialokasikan untuk varian warna ini.';
                        infoWarna.classList.remove('hidden');
                    } else {
                        infoWarna.classList.add('hidden');
                    }
                }
            }
            warnaBtns.forEach((b) => b.addEventListener('click', () => {
                warna = (warna === b.dataset.warna) ? null : b.dataset.warna;
                syncCta();
            }));
            syncCta();
            const form = document.getElementById('simulasi-form');
            if (!form) return;
            const branch = document.getElementById('sim-branch');
            const dp = document.getElementById('sim-dp');
            const dpRange = document.getElementById('sim-dp-range');
            const dpLabel = document.getElementById('sim-dp-persen-label');
            const tenorInput = document.getElementById('sim-tenor');
            const hint = document.getElementById('sim-dp-hint');
            const galat = document.getElementById('simulasi-error');
            const hasil = document.getElementById('simulasi-hasil');
            const rupiah = (n) => 'Rp ' + Number(n).toLocaleString('id-ID');
            function paintRange() {
                if (!dpRange) return;
                const min = Number(dpRange.min || 0);
                const max = Number(dpRange.max || 100);
                const p = max > min ? Math.round((Number(dpRange.value) - min) / (max - min) * 100) : 0;
                dpRange.style.background = 'linear-gradient(to right, rgb(var(--accent-strong)) ' + p + '%, rgb(var(--line)) ' + p + '%)';
            }
            const leasingInput = document.getElementById('sim-leasing');
            const pilLeasing = Array.from(form.querySelectorAll('[data-leasing]'));
            const leasingAktif = () => pilLeasing.find((b) => b.dataset.leasing === leasingInput.value) || null;
            function otrAktif() {
                return Number(branch.selectedOptions[0].dataset.otr || 0);
            }
            function minPersen() {
                const l = leasingAktif();
                return l ? Number(l.dataset.dpMin || l.dataset.dpmin || 0) : 0;
            }
            function syncLeasing(aktif) {
                pilLeasing.forEach((b) => {
                    const nyala = b === aktif;
                    b.setAttribute('aria-pressed', nyala ? 'true' : 'false');
                    b.classList.toggle('border-accent', nyala);
                    b.classList.toggle('bg-accent-strong/10', nyala);
                    b.classList.toggle('border-line', !nyala);
                    b.classList.toggle('bg-surface', !nyala);
                });
                if (aktif) leasingInput.value = aktif.dataset.leasing;
            }
            const ringkasRp = (n) => n >= 1000000000
                ? 'Rp ' + (Math.round(n / 100000000) / 10).toLocaleString('id-ID') + ' M'
                : 'Rp ' + Math.round(n / 1000000) + ' jt';
            function tandaiPil(pil, aktif) {
                pil.forEach((b) => {
                    const nyala = b === aktif;
                    b.setAttribute('aria-pressed', nyala ? 'true' : 'false');
                    b.classList.toggle('border-accent', nyala);
                    b.classList.toggle('bg-accent-strong/10', nyala);
                    b.classList.toggle('border-line', !nyala);
                    b.classList.toggle('bg-surface', !nyala);
                });
            }
            const pilDp = Array.from(form.querySelectorAll('[data-dp-persen]'));
            const pilTenor = Array.from(form.querySelectorAll('[data-tenor]'));
            function syncDp(persen) {
                const otr = otrAktif();
                const min = Math.ceil(otr * minPersen() / 100);
                const nilai = Math.max(Math.ceil(otr * persen / 100), min);
                dp.min = min;
                dp.value = nilai;
                if (dpRange) {
                    dpRange.min = minPersen();
                    dpRange.max = 100;
                    dpRange.value = Math.round(nilai / otr * 100);
                    paintRange();
                }
                if (dpLabel) dpLabel.textContent = Math.round(nilai / otr * 100) + '% OTR';
                tandaiPil(pilDp, pilDp.find((b) => Number(b.dataset.dpPersen) === Math.round(nilai / otr * 100)) || null);
                pilDp.forEach((b) => {
                    const rp = b.querySelector('[data-dp-rp]');
                    if (rp) rp.textContent = ringkasRp(Math.max(Math.ceil(otr * Number(b.dataset.dpPersen) / 100), min));
                });
                hint.textContent = 'DP minimal ' + rupiah(min) + ' (' + minPersen() + '% dari OTR ' + rupiah(otr) + ').';
            }
            pilDp.forEach((b) => b.addEventListener('click', () => {
                syncDp(Number(b.dataset.dpPersen));
                keTahapHitung();
            }));
            if (dpRange) dpRange.addEventListener('input', () => {
                paintRange();
                const otr = otrAktif();
                const nilai = Math.max(Math.round(otr * Number(dpRange.value) / 100), Math.ceil(otr * minPersen() / 100));
                dp.value = nilai;
                if (dpLabel) dpLabel.textContent = Math.round(nilai / otr * 100) + '% OTR';
                tandaiPil(pilDp, pilDp.find((x) => Number(x.dataset.dpPersen) === Math.round(nilai / otr * 100)) || null);
                hint.textContent = 'DP ' + rupiah(nilai) + ' dari OTR ' + rupiah(otr) + '.';
            });
            dp.addEventListener('input', () => {
                const otr = otrAktif();
                const persen = otr ? Math.round(Number(dp.value || 0) / otr * 100) : 0;
                if (dpLabel && otr) dpLabel.textContent = persen + '% OTR';
                tandaiPil(pilDp, pilDp.find((x) => Number(x.dataset.dpPersen) === persen) || null);
            });
            function syncTenor(aktif) {
                tenorInput.value = aktif ? aktif.dataset.tenor : tenorInput.value;
                tandaiPil(pilTenor, aktif || pilTenor.find((x) => x.dataset.tenor === tenorInput.value) || null);
            }
            pilTenor.forEach((b) => b.addEventListener('click', () => {
                syncTenor(b);
                keTahapHitung();
            }));
            pilLeasing.forEach((b) => b.addEventListener('click', () => {
                syncLeasing(b);
                const otr = otrAktif();
                const persen = otr ? Math.max(Math.round(Number(dp.value || 0) / otr * 100), minPersen()) : minPersen();
                syncDp(persen);
                keTahapHitung();
            }));
            syncLeasing(pilLeasing[0]);
            branch.addEventListener('change', () => syncDp(20));
            syncDp(20);
            syncTenor(pilTenor[0]);
            const tombolKirim = document.getElementById('sim-submit');
            const linkLanjut = document.getElementById('sim-lanjut');
            let terkirim = false;
            function isiRingkasan(d) {
                document.getElementById('ring-tenor').textContent = d.tenor + ' Bulan (' + (d.tenor / 12) + ' Tahun)';
                document.getElementById('ring-otr').textContent = rupiah(d.otr);
                document.getElementById('ring-dp').textContent = rupiah(d.dp);
                document.getElementById('ring-pokok').textContent = rupiah(d.pokok);
                document.getElementById('ring-bunga').textContent = d.bunga + '% p.a.';
                document.getElementById('ring-rincian').classList.remove('hidden');
                document.getElementById('ring-angsuran-wrap').classList.remove('hidden');
                document.getElementById('ring-angsuran').textContent = rupiah(d.angsuran);
            }
            function keTahapHitung() {
                terkirim = false;
                document.getElementById('ring-rincian').classList.add('hidden');
                document.getElementById('ring-angsuran-wrap').classList.add('hidden');
                if (linkLanjut) linkLanjut.classList.add('hidden');
                if (tombolKirim) tombolKirim.classList.remove('hidden');
                galat.classList.add('hidden');
                hasil.innerHTML = '<p class="text-sm text-muted">Pilih parameter pembiayaan lalu klik hitung.</p>';
            }
            ['sim-branch', 'sim-dp', 'sim-tenor'].forEach((id) => {
                const el = document.getElementById(id);
                if (el) el.addEventListener(id === 'sim-dp' ? 'input' : 'change', keTahapHitung);
            });
            if (dpRange) dpRange.addEventListener('input', keTahapHitung);
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                if (terkirim) return;
                if (!form.checkValidity()) {
                    form.reportValidity();
                    return;
                }
                galat.classList.add('hidden');
                hasil.innerHTML = '<p class="text-sm text-muted">Menghitung simulasi...</p>';
                fetch(form.action, {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                    body: new FormData(form),
                }).then(async (res) => {
                    const data = await res.json().catch(() => ({}));
                    if (!res.ok || !data.angsuran) {
                        const pesan = data.errors ? Object.values(data.errors).flat().join(' ') : (data.message || 'Gagal menghitung simulasi. Silakan periksa kembali formulir.');
                        galat.textContent = pesan;
                        galat.classList.remove('hidden');
                        hasil.innerHTML = '';
                        keTahapHitung();
                        return;
                    }
                    isiRingkasan(data);
                    hasil.innerHTML = '<p class="text-xs text-emerald-700 dark:text-emerald-300 font-medium">Estimasi pembiayaan berhasil dihitung. Klik tombol di bawah untuk melanjutkan ke pengajuan SPK.</p>';
                    terkirim = true;
                    if (tombolKirim) tombolKirim.classList.add('hidden');
                    if (linkLanjut) {
                        const param = new URLSearchParams({
                            variant_id: varianId,
                            branch_id: branch.value,
                            dp_rencana: dp.value || 0,
                            tenor: tenorInput.value,
                            leasing_id: leasingInput.value,
                        });
                        if (warna) param.set('color_id', warna);
                        linkLanjut.setAttribute('href', spkBase + '?' + param.toString());
                        linkLanjut.classList.remove('hidden');
                    }
                }).catch(() => {
                    galat.textContent = 'Terjadi gangguan jaringan. Coba beberapa saat lagi.';
                    galat.classList.remove('hidden');
                    hasil.innerHTML = '';
                });
            });
        })();
    </script>
</x-site-layout>
