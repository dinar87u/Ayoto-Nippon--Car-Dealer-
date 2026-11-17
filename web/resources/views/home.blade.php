<x-site-layout judul="Mobil Baru Harga OTR Jelas" deskripsi="Ayoto Nippon: katalog mobil baru dengan harga OTR jelas per cabang, simulasi kredit, test drive, dan servis di Jakarta dan Bandung.">
    <x-site-header transparan />

    <main class="bx" id="home-page">
        <!-- Hero: 100vh, foto penuh, overlay, watermark, arch bawah (klon Boxcars) -->
        <section class="hero" aria-labelledby="hero-title">
            <div class="hero__media">
                <img src="{{ asset($slides->first()->foto) }}" alt="{{ $slides->first()->nama }}" fetchpriority="high" width="1920" height="960">
                <div class="hero__overlay"></div>
            </div>

            <div class="hero__watermark" aria-hidden="true">
                <span>AYOTO NIPPON</span>
            </div>

            <div class="hero__content">
                <span class="dot-badge dot-badge--light">{{ $totalUnit }} unit siap kirim</span>
                <h1 id="hero-title" class="hero__title">Temukan Mobil Baru Anda</h1>
                <p class="hero__subtitle">Unit baru terkurasi, harga OTR resmi, siap kirim.</p>

                <div class="hero__actions">
                    <a href="{{ route('katalog.index') }}" class="btn btn--primary">Lihat Katalog ↗</a>
                    <a href="#cara-kerja" class="btn btn--white">Cara Kerjanya</a>
                </div>
            </div>

            <div class="hero__types-bar">
                <p class="hero__types-label">Atau telusuri per tipe</p>
                <div class="hero__types">
                    @php
                        $ikonBody = ['SUV' => 'icon-suv.svg', 'MPV' => 'icon-mpv.svg', 'Sedan' => 'icon-sedan.svg', 'Listrik' => 'icon-electric.svg'];
                    @endphp
                    @foreach ($tipeBodies as $body)
                        @if (isset($ikonBody[$body]))
                            <a href="{{ route('katalog.index', ['body' => $body]) }}" class="type-pill">
                                <img src="{{ asset('images/icons/'.$ikonBody[$body]) }}" alt="" width="22" height="22" aria-hidden="true">
                                <span>{{ $body }}</span>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="hero__bottom-arch" aria-hidden="true"></div>
        </section>

        <!-- Jelajahi Merek Kami -->
        <section class="brands-section">
            <div class="container">
                <div class="section-head">
                    <div class="section-head__left">
                        <span class="dot-badge">Merek Resmi</span>
                        <h2 class="section-title">Jelajahi Merek Kami</h2>
                    </div>
                    <a href="{{ route('katalog.index') }}" class="link-arrow">Telusuri per merek ↗</a>
                </div>

                <div class="brands-grid" data-reveal-stagger>
                    @foreach ($brands->take(16) as $brand)
                        <a href="{{ route('katalog.index', ['brand' => $brand->slug]) }}" class="brand-card reveal">
                            <img src="{{ $brand->logo_url }}" alt="" aria-hidden="true" loading="lazy">
                            <span>{{ $brand->nama }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Unit Unggulan -->
        @if ($unggulan->isNotEmpty())
            <section class="section">
                <div class="container">
                    <div class="section-head">
                        <div class="section-head__left">
                            <span class="dot-badge">Katalog Kami</span>
                            <h2 class="section-title">Unit unggulan</h2>
                        </div>
                        <a href="{{ route('katalog.index') }}" class="link-arrow">Lihat semua unit ↗</a>
                    </div>

                    <div class="vehicles-grid" data-reveal-stagger>
                        @foreach ($unggulan as $top)
                            @php
                                $fotoTop = $top->units->where('status', 'Tersedia')->first()?->foto_utama ?? $top->units->first()?->foto_utama;
                                $mulaiTop = $top->priceLists->min('otr');
                                $stokTop = $top->units->where('status', 'Tersedia')->count();
                                $tahunTop = $top->units->max('tahun_rakit');
                                $angsuranTop = ($mulaiTop && $bungaMin !== null)
                                    ? (int) round((($mulaiTop * 0.8) + ($mulaiTop * 0.8 * $bungaMin / 100 * 48 / 12)) / 48)
                                    : null;
                            @endphp
                            <a href="{{ route('katalog.show', $top->slug) }}" class="vehicle-card reveal">
                                <div class="vehicle-card__media">
                                    @if ($fotoTop)
                                        <img src="{{ asset($fotoTop) }}" alt="{{ $top->carModel->brand->nama }} {{ $top->nama }}" loading="lazy" width="640" height="427">
                                    @endif
                                    <span class="vehicle-card__badge {{ $stokTop > 0 ? 'vehicle-card__badge--baru' : '' }}">{{ $stokTop > 0 ? 'Tersedia' : 'Dipesan' }}</span>
                                </div>
                                <div class="vehicle-card__body">
                                    <h3 class="vehicle-card__title">{{ $top->carModel->brand->nama }} {{ $top->nama }}</h3>
                                    <p class="vehicle-card__subtitle">{{ $top->carModel->nama }}</p>
                                    <div class="vehicle-card__specs">
                                        <div class="vehicle-card__spec">
                                            <span class="vehicle-card__spec-label">Tahun</span>
                                            <strong>{{ $tahunTop ?? '–' }}</strong>
                                        </div>
                                        <div class="vehicle-card__spec">
                                            <span class="vehicle-card__spec-label">Bahan Bakar</span>
                                            <strong>{{ $top->bbm }}</strong>
                                        </div>
                                        <div class="vehicle-card__spec">
                                            <span class="vehicle-card__spec-label">Transmisi</span>
                                            <strong>{{ $top->transmisi }}</strong>
                                        </div>
                                    </div>
                                    <div class="vehicle-card__footer">
                                        <div>
                                            <div class="vehicle-card__price">{{ $mulaiTop ? 'Rp '.number_format($mulaiTop, 0, ',', '.') : 'Hubungi Cabang' }}</div>
                                            @if ($angsuranTop)
                                                <div class="vehicle-card__monthly">Estimasi Rp {{ number_format($angsuranTop, 0, ',', '.') }}/bln - DP 20%, tenor 48 bln</div>
                                            @endif
                                        </div>
                                        <span class="vehicle-card__cta">Lihat Detail ↗</span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <!-- Banner promo: cari mobil -->
        <section class="section" style="padding-top:0;">
            <div class="container">
                <div class="dual-banner" style="grid-template-columns: 1fr;">
                    <article class="promo-card reveal">
                        <img src="{{ asset('images/unit/foto-5.jpg') }}" alt="Unit baru Ayoto Nippon di showroom" loading="lazy" width="686" height="395">
                        <div class="promo-card__overlay"></div>
                        <div class="promo-card__content">
                            <h2 class="promo-card__title">Mencari mobil berikutnya?</h2>
                            <p class="promo-card__text">Jelajahi unit baru kami dengan harga OTR resmi, siap kirim dari stok cabang.</p>
                            <a href="{{ route('katalog.index') }}" class="btn btn--primary">Lihat katalog ↗</a>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <!-- Cara Kerja: kiri sticky, kanan kartu langkah -->
        <section class="section section--how" id="cara-kerja">
            <div class="container how-layout">
                <div class="how-sticky">
                    <span class="dot-badge">Cara Kerja</span>
                    <h2 class="section-title">Dari memilih hingga mengemudi, dalam empat langkah</h2>
                    <p class="section-subtitle">Tanpa tekanan dan tanpa istilah rumit. Proses pembelian mobil dibuat jelas dari awal sampai serah terima.</p>

                    <div style="margin-top:28px;">
                        <a href="{{ route('test-drive.create') }}" class="btn btn--dark">Jadwalkan test drive ↗</a>
                    </div>
                </div>

                <div class="steps-stack" data-reveal-stagger>
                    @foreach ([
                        ['Pilih & amankan unit', 'Jelajahi seluruh unit di katalog dan filter sesuai merek, harga, atau tipe bodi untuk menemukan yang cocok.', 'steps/step-1.webp'],
                        ['Bayar booking fee resmi', 'Transfer tanda jadi ke rekening resmi cabang sebelum batas waktu kedaluwarsa, lalu lacak statusnya kapan pun.', 'steps/step-2.webp'],
                        ['SPK & invoice terbit', 'Admin memverifikasi transaksi dan menerbitkan dokumen SPK serta invoice resmi untuk pesanan Anda.', 'steps/step-3.webp'],
                        ['Serah terima di cabang', 'Selesaikan pelunasan di cabang dan unit diserahkan bersama berita acara serah terima (BAST).', 'steps/step-4.webp'],
                    ] as $i => [$judul, $teks, $foto])
                        <article class="step-card reveal" data-reveal="right" style="--i: {{ $i }};">
                            <div class="step-card__media">
                                <img src="{{ asset('images/'.$foto) }}" alt="{{ $judul }}" loading="lazy" width="640" height="427">
                            </div>
                            <div class="step-card__body">
                                <div class="step-card__head">
                                    <h3 class="step-card__title">{{ $judul }}</h3>
                                    <span class="step-card__num">0{{ $i + 1 }}</span>
                                </div>
                                <p class="step-card__text">{{ $teks }}</p>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Kenapa Ayoto Nippon + statistik -->
        <section class="section" style="padding-block:0;">
            <div class="container">
                <div class="trust-split reveal" data-reveal="scale">
                    <div class="trust-video-box">
                        <img src="{{ asset('images/unit/foto-11.jpg') }}" alt="Showroom Ayoto Nippon" loading="lazy" width="640" height="427">
                    </div>

                    <div class="trust-content-box">
                        <span class="dot-badge">Kenapa Ayoto Nippon</span>
                        <h2 class="section-title">Lebih dari dealer, partner yang bisa diandalkan</h2>
                        <p class="section-subtitle">Kami membantu Anda mendapatkan mobil yang tepat dengan saran yang jujur dan proses yang bisa dilacak dari awal sampai serah terima.</p>

                        <ul class="checklist">
                            <li>
                                <span class="checklist__icon" aria-hidden="true">✓</span>
                                <span>Stok fisik tercatat per nomor rangka (VIN)</span>
                            </li>
                            <li>
                                <span class="checklist__icon" aria-hidden="true">✓</span>
                                <span>Harga OTR resmi per cabang tanpa biaya mendadak</span>
                            </li>
                            <li>
                                <span class="checklist__icon" aria-hidden="true">✓</span>
                                <span>Booking fee hanya ke rekening resmi cabang</span>
                            </li>
                        </ul>

                        <div>
                            <a href="{{ route('cabang.index') }}" class="btn btn--primary">Lihat jaringan cabang ↗</a>
                        </div>
                    </div>
                </div>

                <div class="stats-row" data-reveal-stagger>
                    <div class="reveal">
                        <div class="stat-item__value" data-counter="{{ $stats['unit'] }}" data-decimals="0">{{ $stats['unit'] }}</div>
                        <div class="stat-item__label">Unit Siap Kirim</div>
                    </div>
                    <div class="reveal">
                        <div class="stat-item__value" data-counter="{{ $stats['cabang'] }}" data-decimals="0">{{ $stats['cabang'] }}</div>
                        <div class="stat-item__label">Cabang Resmi</div>
                    </div>
                    <div class="reveal">
                        <div class="stat-item__value" data-counter="{{ $stats['merek'] }}" data-decimals="0">{{ $stats['merek'] }}</div>
                        <div class="stat-item__label">Merek Mobil</div>
                    </div>
                    <div class="reveal">
                        <div class="stat-item__value" data-counter="{{ $stats['model'] }}" data-decimals="0">{{ $stats['model'] }}</div>
                        <div class="stat-item__label">Model Mobil</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA lebar penuh -->
        <section class="cta-full reveal">
            <img src="{{ asset('images/unit/foto-13.jpg') }}" alt="Mobil baru Ayoto Nippon" loading="lazy" width="1920" height="650">
            <div class="cta-full__overlay"></div>
            <div class="cta-full__content">
                <span class="dot-badge dot-badge--light">Mulai Perjalanan Anda</span>
                <h2 class="cta-full__title">Siap menemukan mobil baru Anda?</h2>
                <p class="cta-full__text">Jelajahi seluruh unit siap kirim di katalog, atau kunjungi cabang kami untuk melihat unit secara langsung.</p>
                <div class="cta-full__actions">
                    <a href="{{ route('katalog.index') }}" class="btn btn--primary">Lihat Katalog ↗</a>
                    <a href="{{ route('cabang.index') }}" class="btn btn--white">Kunjungi Cabang</a>
                </div>
            </div>
        </section>
    </main>

    <x-site-footer />
</x-site-layout>
