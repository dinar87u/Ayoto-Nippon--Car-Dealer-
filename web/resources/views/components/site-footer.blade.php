<footer class="site-footer mt-auto w-full">
    <div class="site-footer__main w-full max-w-[1356px] mx-auto px-6 sm:px-8 lg:px-10 grid grid-cols-1 md:grid-cols-4 gap-10 lg:gap-12">
        <div class="md:col-span-2 pr-0 md:pr-8">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-an.png') }}" alt="Logo Ayoto Nippon" class="h-9 w-9 object-contain">
                <div class="flex flex-col">
                    <span class="site-footer__brand-title text-xl font-extrabold tracking-tight uppercase">
                        Ayoto Nippon
                    </span>
                    <span class="site-footer__brand-sub text-[10px] tracking-[0.18em] uppercase font-medium">Authorized Automobile Partner</span>
                </div>
            </div>
            <p class="site-footer__desc mt-4 text-sm leading-relaxed max-w-sm">
                Jaringan dealer resmi mobil baru terpercaya di Jakarta dan Bandung. Menyediakan unit siap kirim dengan transparansi harga OTR, kemudahan simulasi pembiayaan, servis berkala, dan jaminan suku cadang asli.
            </p>
            <div class="mt-6 flex flex-wrap gap-2 text-xs">
                <span class="site-footer__pill">Cabang Jakarta &amp; Bandung</span>
                <span class="site-footer__pill">Unit VIN Jelas</span>
                <span class="site-footer__pill">Layanan Resmi 3S</span>
            </div>
        </div>

        <nav class="site-footer__col" aria-label="Layanan">
            <h3>Layanan</h3>
            <ul>
                @foreach ([['katalog.index', 'Katalog Mobil'], ['simulasi.form', 'Simulasi Kredit'], ['test-drive.create', 'Booking Test Drive'], ['servis.create', 'Jadwal Servis Berkala'], ['sparepart.index', 'Suku Cadang Asli']] as [$route, $label])
                    <li><a href="{{ route($route) }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>

        <nav class="site-footer__col" aria-label="Bantuan dan Akun">
            <h3>Bantuan &amp; Akun</h3>
            <ul>
                @foreach ([['promo.index', 'Promo Berjalan'], ['cabang.index', 'Lokasi Cabang'], ['pesanan.index', 'Pesanan Saya'], ['faq.index', 'Pertanyaan Umum'], ['login', 'Masuk Akun'], ['register', 'Pendaftaran Baru']] as [$route, $label])
                    <li><a href="{{ route($route) }}">{{ $label }}</a></li>
                @endforeach
            </ul>
        </nav>
    </div>

    <div class="w-full max-w-[1356px] mx-auto px-6 sm:px-8 lg:px-10">
        <div class="site-footer__bottom reveal" data-reveal="up">
            <p>&copy; {{ date('Y') }} PT Ayoto Nippon. Seluruh hak cipta dilindungi undang-undang.</p>
            <p>Standar Transaksi Aman &amp; Otomasi Pemesanan Dealer</p>
        </div>
    </div>

    <div class="site-footer__watermark reveal" data-reveal="watermark" data-reveal-delay="80" aria-hidden="true">
        <span>AYOTO NIPPON</span>
    </div>
</footer>
