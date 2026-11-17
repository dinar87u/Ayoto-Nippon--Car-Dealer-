<x-site-layout judul="Katalog Mobil" deskripsi="Katalog mobil baru dengan harga OTR per cabang dan stok nyata per varian di Ayoto Nippon.">
    <x-site-header />

    <div class="w-full px-4 sm:px-6 lg:px-8 py-10 space-y-8">
        <!-- Header -->
        <div class="pb-6 border-b border-line">
            <p class="text-xs font-semibold tracking-wider text-accent uppercase">Showroom Digital</p>
            <h1 class="mt-1 text-3xl sm:text-4xl font-extrabold text-ink tracking-tight">Katalog Kendaraan Baru</h1>
            <p class="mt-2 text-sm text-muted tabular-nums">
                Tersedia <span class="text-ink font-semibold">{{ $models->total() }}</span> model dan <span class="text-ink font-semibold">{{ number_format($totalUnit, 0, ',', '.') }}</span> unit siap kirim di {{ $branches->count() }} cabang dealer resmi Ayoto Nippon.
            </p>
        </div>

        @php
            $adaFilter = request('q') || request('brand') || request('body') || request('transmisi') || request('bbm') || request('cabang') || request('urut');
            $labelTransmisi = ['AT' => 'Otomatis (AT)', 'CVT' => 'Otomatis (CVT)', 'MT' => 'Manual (MT)'];
        @endphp
        <span class="sr-only">{{ $models->total() }} model ditemukan</span>

        <!-- Easy Interactive Filter Panel -->
        <div class="bg-surface border border-line rounded-3xl p-5 sm:p-6 shadow-lg shadow-black/5 space-y-5">
            <form id="katalog-filter-form" action="{{ route('katalog.index') }}" method="GET" class="space-y-4">
                @if (request('urut'))
                    <input type="hidden" name="urut" value="{{ request('urut') }}">
                @endif
                @if (request('body'))
                    <input type="hidden" name="body" value="{{ request('body') }}">
                @endif

                <!-- Single Cohesive Row: Search on Left + 4 Auto-Submit Dropdowns -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3.5 items-end">
                    <div class="sm:col-span-2 lg:col-span-4">
                        <label for="filter-q" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Pencarian Mobil</label>
                        <div class="relative flex items-center">
                            <svg class="w-4 h-4 text-muted absolute left-3.5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                <circle cx="11" cy="11" r="8"/><path stroke-linecap="round" d="M21 21l-4.3-4.3"/>
                            </svg>
                            <input id="filter-q" type="text" name="q" value="{{ request('q') }}" placeholder="Cth: BYD Seal, Honda Jazz..." aria-label="Cari nama model"
                                class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink placeholder-muted text-sm font-medium focus:border-accent focus:ring-1 focus:ring-accent h-[44px] pl-10 {{ request('q') ? 'pr-24' : 'pr-16' }}">
                            @if (request('q'))
                                <a href="{{ route('katalog.index', request()->except('q', 'page')) }}" aria-label="Hapus pencarian"
                                   class="absolute right-[62px] w-6 h-6 rounded-full bg-surface border border-line text-muted hover:text-ink hover:border-accent inline-flex items-center justify-center transition-colors">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endif
                            <button type="submit" class="absolute right-1.5 h-[32px] px-3.5 text-xs font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-lg transition-colors inline-flex items-center justify-center">
                                Cari
                            </button>
                        </div>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="filter-brand" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Merek Kendaraan</label>
                        <select id="filter-brand" name="brand" aria-label="Merek" onchange="this.form.submit()"
                            class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm font-medium focus:border-accent focus:ring-1 focus:ring-accent h-[44px] px-3.5">
                            <option value="">Semua Merek ({{ $brands->count() }})</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->slug }}" @selected(request('brand') === $brand->slug)>{{ $brand->nama }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="filter-transmisi" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Transmisi</label>
                        <select id="filter-transmisi" name="transmisi" aria-label="Transmisi" onchange="this.form.submit()"
                            class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm font-medium focus:border-accent focus:ring-1 focus:ring-accent h-[44px] px-3.5">
                            <option value="">Semua Transmisi</option>
                            @foreach (['AT', 'CVT', 'MT'] as $t)
                                <option value="{{ $t }}" @selected(request('transmisi') === $t)>{{ $labelTransmisi[$t] ?? $t }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="filter-bbm" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Bahan Bakar</label>
                        <select id="filter-bbm" name="bbm" aria-label="BBM" onchange="this.form.submit()"
                            class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm font-medium focus:border-accent focus:ring-1 focus:ring-accent h-[44px] px-3.5">
                            <option value="">Semua Bahan Bakar</option>
                            @foreach ($bbms as $b)
                                <option value="{{ $b }}" @selected(request('bbm') === $b)>{{ $b }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="lg:col-span-2">
                        <label for="filter-cabang" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Lokasi Cabang</label>
                        <select id="filter-cabang" name="cabang" aria-label="Cabang" onchange="this.form.submit()"
                            class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm font-medium focus:border-accent focus:ring-1 focus:ring-accent h-[44px] px-3.5">
                            <option value="">Semua Cabang Dealer</option>
                            @foreach ($branches as $branch)
                                <option value="{{ $branch->kode }}" @selected(request('cabang') === $branch->kode)>{{ $branch->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                @if ($adaFilter)
                    @php
                        $namaMerekAktif = $brands->firstWhere('slug', request('brand'))?->nama;
                        $namaCabangAktif = $branches->firstWhere('kode', request('cabang'))?->nama;
                        $chipAktif = array_filter([
                            'q' => request('q') ? 'Pencarian: "'.request('q').'"' : null,
                            'body' => request('body') ? 'Tipe: '.request('body') : null,
                            'brand' => $namaMerekAktif ? 'Merek: '.$namaMerekAktif : null,
                            'transmisi' => request('transmisi') ? 'Transmisi: '.($labelTransmisi[request('transmisi')] ?? request('transmisi')) : null,
                            'bbm' => request('bbm') ? 'BBM: '.request('bbm') : null,
                            'cabang' => $namaCabangAktif ? 'Cabang: '.$namaCabangAktif : null,
                        ]);
                    @endphp
                    <div class="pt-3.5 border-t border-line flex flex-wrap items-center justify-between gap-3 text-xs">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="text-muted font-medium">Filter aktif:</span>
                            @foreach ($chipAktif as $kunci => $teksChip)
                                <a href="{{ route('katalog.index', request()->except($kunci, 'page')) }}"
                                   class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-surface-2 border border-line text-ink font-semibold leading-none hover:border-accent transition-colors">
                                    <span>{{ $teksChip }}</span>
                                    <svg class="w-3 h-3 text-muted shrink-0" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                                </a>
                            @endforeach
                        </div>
                        <a href="{{ route('katalog.index') }}" class="font-bold text-accent hover:underline py-1">
                            Hapus Semua Filter
                        </a>
                    </div>
                @endif
            </form>
        </div>

        <!-- Models Catalog Grid -->
        @if ($models->isEmpty())
            <div class="p-12 border border-line bg-surface rounded-3xl text-center max-w-lg mx-auto">
                <div class="w-16 h-16 rounded-2xl bg-surface border border-line flex items-center justify-center mx-auto text-muted mb-4">
                    <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="text-lg font-bold text-ink">Tidak Ada Unit Ditemukan</h3>
                <p class="mt-2 text-sm text-muted">Coba ubah kata kunci pencarian atau sesuaikan pilihan filter.</p>
                <a href="{{ route('katalog.index') }}" class="mt-6 inline-flex items-center px-5 py-2.5 text-sm font-semibold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition-colors shadow-lg shadow-accent/20">
                    Lihat Semua Katalog
                </a>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach ($models as $model)
                    @php
                        $tersedia = $model->variants->flatMap->units->where('status', 'Tersedia');
                        $foto = $tersedia->first()?->foto_utama ?? $model->variants->flatMap->units->first()?->foto_utama;
                        $harga = $model->variants->flatMap(function ($v) use ($kodeCabang) {
                            return $kodeCabang ? $v->priceLists->where('branch.kode', $kodeCabang) : $v->priceLists;
                        })->min('otr');

                        $bodi = null;
                        $kursi = null;
                        if ($model->deskripsi && preg_match('/^(.*)\s+(\d+)\s+penumpang$/', $model->deskripsi, $cocok)) {
                            $bodi = trim($cocok[1]);
                            $kursi = $cocok[2].' Kursi';
                        }

                        $transmisi = $model->variants->pluck('transmisi')->unique()->filter()->join(' / ') ?: '-';
                        $bbm = $model->variants->pluck('bbm')->unique()->filter()->join(' / ') ?: '-';
                        $hargaTeks = $harga ? 'Rp '.number_format($harga, 0, ',', '.') : 'Hubungi Cabang';
                        $stokTeks = $tersedia->count().' Unit ('.$model->variants->count().' Varian)';
                        $varianUtama = $model->variants->sortBy(fn ($v) => $v->priceLists->min('otr') ?? PHP_INT_MAX)->first() ?? $model->variants->first();
                        $tautanDetail = $varianUtama ? route('katalog.show', $varianUtama->slug) : route('katalog.model', [$model->brand->slug, $model->slug]);
                    @endphp
                    <a href="{{ $tautanDetail }}"
                       class="group bg-surface-2/80 dark:bg-surface border border-line rounded-3xl p-4 sm:p-5 flex flex-col justify-between transition-all duration-300 hover:border-accent hover:shadow-2xl hover:shadow-black/5 hover:-translate-y-1 focus-visible:-translate-y-1 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent">
                        <div>
                            <div class="relative rounded-2xl overflow-hidden bg-surface aspect-[16/10]">
                                @if ($foto)
                                    <img src="{{ asset($foto) }}" alt="{{ $model->brand->nama }} {{ $model->nama }}" class="h-full w-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                                @else
                                    <div class="h-full w-full flex items-center justify-center text-sm text-muted">Foto menyusul</div>
                                @endif
                                <div class="absolute top-3 left-3 rounded-full bg-surface/90 backdrop-blur-sm shadow-sm">
                                    <x-status-badge status="{{ $tersedia->isNotEmpty() ? 'Tersedia' : 'Dipesan' }}" />
                                </div>
                            </div>

                            <div class="mt-4 flex items-baseline justify-between gap-2">
                                <div class="min-w-0">
                                    <p class="text-[11px] font-semibold text-muted uppercase tracking-wider truncate">{{ $model->brand->nama }}</p>
                                    <h2 class="mt-0.5 text-xl font-extrabold text-ink tracking-tight group-hover:text-accent transition-colors truncate">{{ $model->nama }}</h2>
                                </div>
                                <span class="text-xs font-semibold text-accent shrink-0 group-hover:translate-x-0.5 transition-transform">Lihat ↗</span>
                            </div>

                            <div class="mt-2.5 pb-3.5 border-b border-line flex items-baseline justify-between gap-2">
                                <div class="min-w-0">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted">OTR Mulai{{ $kodeCabang ? ' ('.$chipCabang.')' : '' }}</span>
                                    <strong class="mt-0.5 block text-lg font-black text-ink tracking-tight tabular-nums leading-tight">{{ $hargaTeks }}</strong>
                                </div>
                                <span class="text-[11px] font-semibold text-muted shrink-0">{{ $stokTeks }}</span>
                            </div>

                            <div class="mt-3.5 grid grid-cols-2 gap-x-3 gap-y-3">
                                {{-- 1. Tempat Duduk --}}
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-8 h-8 rounded-lg border border-[#243B6B]/35 dark:border-white/20 text-[#243B6B] dark:text-slate-200 flex items-center justify-center shrink-0" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 13V6a2 2 0 012-2h6a2 2 0 012 2v7m-10 0h10m-10 0l-1 5h12l-1-5M9 18v2m6-2v2"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="block text-[10px] font-medium uppercase tracking-wider text-muted">Kursi</span>
                                        <strong class="block text-[11px] font-bold uppercase text-ink truncate">{{ $kursi ?? '-' }}</strong>
                                    </div>
                                </div>

                                {{-- 2. Tipe Bodi --}}
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-8 h-8 rounded-lg border border-[#243B6B]/35 dark:border-white/20 text-[#243B6B] dark:text-slate-200 flex items-center justify-center shrink-0" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l1.5-4.5A2 2 0 018.4 7h7.2a2 2 0 011.9 1.5L19 13m-14 0v4a1 1 0 001 1h1a1 1 0 001-1v-1h8v1a1 1 0 001 1h1a1 1 0 001-1v-4m-14 0h14M7.5 13.5h.01M16.5 13.5h.01"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="block text-[10px] font-medium uppercase tracking-wider text-muted">Bodi</span>
                                        <strong class="block text-[11px] font-bold uppercase text-ink truncate">{{ $bodi ?? $model->brand->nama }}</strong>
                                    </div>
                                </div>

                                {{-- 3. Transmisi --}}
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-8 h-8 rounded-lg border border-[#243B6B]/35 dark:border-white/20 text-[#243B6B] dark:text-slate-200 flex items-center justify-center shrink-0" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                            <circle cx="6" cy="6" r="1.5"/><circle cx="12" cy="6" r="1.5"/><circle cx="18" cy="6" r="1.5"/>
                                            <circle cx="6" cy="18" r="1.5"/><circle cx="12" cy="18" r="1.5"/>
                                            <path stroke-linecap="round" d="M6 7.5v9M12 7.5v9M18 7.5V12H6"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="block text-[10px] font-medium uppercase tracking-wider text-muted">Transmisi</span>
                                        <strong class="block text-[11px] font-bold uppercase text-ink truncate">{{ $transmisi }}</strong>
                                    </div>
                                </div>

                                {{-- 4. Bahan Bakar --}}
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <span class="w-8 h-8 rounded-lg border border-[#243B6B]/35 dark:border-white/20 text-[#243B6B] dark:text-slate-200 flex items-center justify-center shrink-0" aria-hidden="true">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 20V5a2 2 0 012-2h6a2 2 0 012 2v15M4 20h12M8 7h4v4H8V7zm7 3h1.5a1.5 1.5 0 011.5 1.5v5a1.5 1.5 0 003 0V9.5l-2-2"/>
                                        </svg>
                                    </span>
                                    <div class="min-w-0">
                                        <span class="block text-[10px] font-medium uppercase tracking-wider text-muted">Bahan Bakar</span>
                                        <strong class="block text-[11px] font-bold uppercase text-ink truncate">{{ $bbm }}</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>

            <div class="mt-8 pt-6 border-t border-line text-muted">
                {{ $models->links() }}
            </div>
        @endif
    </div>

    <x-site-footer />
</x-site-layout>
