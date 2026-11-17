<x-site-layout judul="Ajukan SPK" deskripsi="Ajukan SPK mobil baru Ayoto Nippon tanpa perlu membuat akun terlebih dahulu.">
    <x-site-header />

    <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 py-8 sm:py-12">
        <div class="mb-8">
            <h1 class="text-2xl sm:text-4xl font-extrabold text-ink tracking-tight">Ajukan Pemesanan (SPK)</h1>
            <p class="mt-2 text-sm sm:text-base text-muted max-w-3xl leading-relaxed">
                Tidak perlu membuat akun dulu. Isi nama dan nomor WhatsApp, sales kami akan menghubungi Anda untuk melanjutkan pemesanan.
            </p>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
            <!-- Ringkasan unit -->
            <div class="lg:col-span-5 xl:col-span-4 bg-surface-2 border border-line rounded-3xl p-6 sm:p-8">
                <p class="text-xs font-semibold tracking-wider text-accent uppercase">Unit yang Anda minati</p>
                @if ($unit || $variant)
                    <div class="mt-4 bg-surface border border-line rounded-2xl overflow-hidden">
                        @if ($unit?->foto_utama)
                            <img src="{{ asset($unit->foto_utama) }}"
                                alt="{{ $variant?->carModel?->nama ?? 'Unit' }}" class="w-full h-44 object-cover">
                        @endif
                        <div class="p-5">
                            <h3 class="font-bold text-ink leading-snug">
                                {{ $variant ? ($variant->carModel->brand->nama . ' ' . $variant->carModel->nama . ' ' . $variant->nama) : 'Unit terpilih' }}
                            </h3>
                            <dl class="mt-3 space-y-1.5 text-xs text-muted">
                                @if ($unit)
                                    <div class="flex justify-between gap-3"><span>VIN</span><span class="font-semibold text-ink">{{ $unit->vin }}</span></div>
                                    <div class="flex justify-between gap-3"><span>Tahun</span><span class="font-semibold text-ink">{{ $unit->tahun_rakit }}</span></div>
                                    @if ($unit->color)
                                        <div class="flex justify-between gap-3"><span>Warna</span><span class="font-semibold text-ink">{{ $unit->color->nama }}</span></div>
                                    @endif
                                @endif
                                @if ($branch)
                                    <div class="flex justify-between gap-3"><span>Cabang</span><span class="font-semibold text-ink">{{ $branch->nama }}</span></div>
                                @endif
                                @if ($otr)
                                    <div class="flex justify-between gap-3"><span>Harga OTR</span><span class="font-extrabold text-accent tabular-nums">Rp {{ number_format($otr, 0, ',', '.') }}</span></div>
                                @endif
                            </dl>
                        </div>
                    </div>
                @else
                    <p class="mt-3 text-sm text-muted">
                        Anda belum memilih unit spesifik. Sales kami akan membantu mencarikan unit yang cocok sesuai kebutuhan Anda.
                    </p>
                @endif

                <div class="mt-6 space-y-3 text-xs text-muted">
                    <p class="flex gap-2"><span class="text-accent font-bold">✓</span> Tanpa akun, tanpa password.</p>
                    <p class="flex gap-2"><span class="text-accent font-bold">✓</span> Data hanya dipakai sales untuk menghubungi Anda.</p>
                    <p class="flex gap-2"><span class="text-accent font-bold">✓</span> Dokumen & akun cukup disiapkan saat deal.</p>
                </div>
            </div>

            <!-- Form -->
            <div class="lg:col-span-7 xl:col-span-8 bg-surface border border-line rounded-3xl p-6 sm:p-10 shadow-xl shadow-black/5">
                <div class="pb-6 border-b border-line flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <p class="text-xs font-semibold tracking-wider text-accent uppercase">Langkah 1 dari 2</p>
                        <h2 class="mt-1 text-xl sm:text-2xl font-bold text-ink">Data Kontak Anda</h2>
                    </div>
                    <span class="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-800 dark:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 px-3 py-1.5 rounded-full">
                        Cukup 1 Menit
                    </span>
                </div>

                <form action="{{ route('spk.guest.store') }}" method="POST" class="mt-8 space-y-6">
                    @csrf

                    {{-- Honeypot anti-bot: wajib dibiarkan kosong --}}
                    <div class="hidden" aria-hidden="true">
                        <label for="website">Website</label>
                        <input id="website" name="website" type="text" tabindex="-1" autocomplete="off">
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                        <div>
                            <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-muted">Nama Lengkap</label>
                            <input id="nama" name="nama" value="{{ old('nama') }}" required
                                placeholder="Nama sesuai KTP / SIM"
                                class="mt-1.5 w-full bg-surface border border-line rounded-xl text-ink placeholder-gray-500 text-sm focus:border-accent focus:ring-1 focus:ring-accent p-3.5">
                        </div>
                        <div>
                            <label for="telp" class="block text-xs font-semibold uppercase tracking-wider text-muted">Nomor WhatsApp</label>
                            <input id="telp" name="telp" value="{{ old('telp') }}" required inputmode="tel"
                                placeholder="Contoh: 081234567890"
                                class="mt-1.5 w-full bg-surface border border-line rounded-xl text-ink placeholder-gray-500 text-sm focus:border-accent focus:ring-1 focus:ring-accent p-3.5">
                        </div>
                    </div>

                    <div>
                        <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-muted">Email (Opsional)</label>
                        <input id="email" name="email" type="email" value="{{ old('email') }}"
                            placeholder="Untuk kirim penawaran tertulis"
                            class="mt-1.5 w-full bg-surface border border-line rounded-xl text-ink placeholder-gray-500 text-sm focus:border-accent focus:ring-1 focus:ring-accent p-3.5">
                    </div>

                    <div>
                        <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-muted">Catatan (Opsional)</label>
                        <textarea id="catatan" name="catatan" rows="2"
                            placeholder="Misal: ingin cicilan di bawah Rp 4 juta/bulan, atau tanya warna lain"
                            class="mt-1.5 w-full bg-surface border border-line rounded-xl text-ink placeholder-gray-500 text-sm focus:border-accent focus:ring-1 focus:ring-accent p-3.5">{{ old('catatan') }}</textarea>
                    </div>

                    <x-input-error :messages="$errors->all()" class="text-accent text-xs" />

                    <button type="submit"
                        class="w-full py-4 px-6 text-center font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl shadow-lg shadow-accent/25 transition-all min-h-[52px]">
                        Kirim & Minta Sales Menghubungi Saya
                    </button>

                    <p class="text-xs text-muted text-center leading-relaxed">
                        Dengan mengirim, Anda setuju dihubungi tim sales Ayoto Nippon via WhatsApp atau telepon.
                    </p>
                </form>
            </div>
        </div>
    </div>
</x-site-layout>
