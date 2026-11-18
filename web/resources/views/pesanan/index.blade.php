<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">Pusat Aktivitas Akun</p>
                <h1 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">Riwayat &amp; Pesanan Saya</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('spk.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors">
                    + Ajukan SPK
                </a>
                <a href="{{ route('servis.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-semibold bg-surface border border-line text-ink hover:border-accent transition-colors">
                    + Booking Servis
                </a>
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">

        <!-- Section 1: SPK -->
        <section aria-labelledby="riwayat-spk" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 bg-surface border-b border-line flex items-center justify-between">
                <div>
                    <h2 id="riwayat-spk" class="font-bold text-base text-ink">Surat Pesanan Kendaraan (SPK)</h2>
                    <p class="text-xs text-muted">Daftar pemesanan unit baru dan status alokasi nomor rangka.</p>
                </div>
            </div>
            <div class="divide-y divide-line">
                @forelse ($spks as $order)
                    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-4 hover:bg-surface-2 transition-colors">
                        <div class="flex items-center gap-4 min-w-0">
                            <div class="w-16 h-11 rounded-lg overflow-hidden bg-surface-2 shrink-0">
                                @if ($order->unit->foto_utama)
                                    <img src="{{ asset($order->unit->foto_utama) }}" alt="" aria-hidden="true" class="w-full h-full object-cover" loading="lazy">
                                @endif
                            </div>
                            <div class="min-w-0">
                                <a href="{{ route('spk.show', $order->no_spk) }}" class="font-mono font-bold text-sm text-accent hover:text-accent underline underline-offset-2 min-h-[44px] inline-flex items-center">
                                    {{ $order->no_spk }}
                                </a>
                                <p class="text-xs sm:text-sm text-muted truncate">
                                    {{ $order->unit->variant->carModel->brand->nama ?? '' }} {{ $order->unit->variant->carModel->nama ?? '' }} {{ $order->unit->variant->nama ?? '' }}
                                    <span class="text-muted">-</span>
                                    <span class="text-muted">{{ $order->branch->nama }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="text-sm font-semibold text-ink tabular-nums">Rp {{ number_format($order->unit->harga, 0, ',', '.') }}</span>
                            <x-status-badge :status="$order->statusTampil()" />
                            <a href="{{ route('spk.show', $order->no_spk) }}" class="text-xs font-semibold text-muted hover:text-ink min-h-[44px] inline-flex items-center">
                                Rincian &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-muted">
                        <p>Belum ada pemesanan SPK aktif.</p>
                        <a href="{{ route('katalog.index') }}" class="mt-2 inline-block font-semibold text-accent hover:underline">Jelajahi Katalog Mobil</a>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Section 2: Test Drive -->
        <section aria-labelledby="riwayat-td" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 bg-surface border-b border-line">
                <h2 id="riwayat-td" class="font-bold text-base text-ink">Jadwal Uji Kemudi (Test Drive)</h2>
                <p class="text-xs text-muted">Kelola atau ajukan penjadwalan ulang selama status masih aktif.</p>
            </div>
            <div class="divide-y divide-line">
                @forelse ($testDrives as $td)
                    <div class="px-6 py-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="font-bold text-sm text-ink">{{ $td->variant->carModel->brand->nama }} {{ $td->variant->carModel->nama }} {{ $td->variant->nama }}</span>
                                <p class="text-xs text-muted mt-0.5 tabular-nums">{{ $td->jadwal->format('d M Y H:i') }} WIB - Cabang {{ $td->branch->nama }}</p>
                            </div>
                            <x-status-badge :status="$td->status" />
                        </div>

                        @if (in_array($td->status, ['Menunggu', 'Dikonfirmasi'], true))
                            <div class="pt-3 border-t border-line flex flex-wrap items-center gap-3">
                                <form action="{{ route('pesanan.test-drive.jadwal', $td) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label for="jadwal-td-{{ $td->id }}" class="text-xs text-muted">Ubah Waktu:</label>
                                    <input id="jadwal-td-{{ $td->id }}" type="datetime-local" name="jadwal" required 
                                        class="bg-surface border border-line rounded-xl text-ink text-xs min-h-[40px] px-3 focus:border-accent">
                                    <button type="submit" class="px-3.5 py-2 text-xs font-bold text-ink bg-surface border border-line hover:bg-surface-2 rounded-xl min-h-[40px] transition-colors">
                                        Simpan Jadwal Baru
                                    </button>
                                </form>
                                <form action="{{ route('pesanan.test-drive.batal', $td) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan jadwal test drive ini?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3.5 py-2 text-xs font-bold text-accent bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 rounded-xl min-h-[40px] transition-colors">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-muted">
                        <p>Belum ada jadwal test drive yang terdaftar.</p>
                        <a href="{{ route('test-drive.create') }}" class="mt-2 inline-block font-semibold text-accent hover:underline">Booking Test Drive Sekarang</a>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Section 3: Servis -->
        <section aria-labelledby="riwayat-svc" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 bg-surface border-b border-line">
                <h2 id="riwayat-svc" class="font-bold text-base text-ink">Booking Servis Berkala</h2>
                <p class="text-xs text-muted">Pantau proses perbaikan dan unduh invoice resmi servis.</p>
            </div>
            <div class="divide-y divide-line">
                @forelse ($servis as $svc)
                    <div class="px-6 py-5 space-y-3">
                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <div>
                                <span class="font-mono font-bold text-sm text-ink">{{ $svc->no_svc }}</span>
                                <span class="text-xs text-muted font-mono ms-2 px-2 py-0.5 bg-surface border border-line rounded-md">{{ $svc->nopol }}</span>
                                <p class="text-xs text-muted mt-1 tabular-nums">{{ $svc->jadwal->format('d M Y H:i') }} WIB - Cabang {{ $svc->branch->nama }}</p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($svc->status === 'Selesai')
                                    <a href="{{ route('servis.invoice', $svc) }}" class="text-xs font-bold text-emerald-700 dark:text-emerald-300 hover:underline min-h-[44px] inline-flex items-center">
                                        Unduh Invoice PDF &rarr;
                                    </a>
                                @endif
                                <x-status-badge :status="$svc->status" />
                            </div>
                        </div>

                        @if (in_array($svc->status, ['Menunggu', 'Dikonfirmasi'], true))
                            <div class="pt-3 border-t border-line flex flex-wrap items-center gap-3">
                                <form action="{{ route('pesanan.servis.jadwal', $svc) }}" method="POST" class="flex flex-wrap items-center gap-2">
                                    @csrf @method('PATCH')
                                    <label for="jadwal-svc-{{ $svc->id }}" class="text-xs text-muted">Ubah Waktu:</label>
                                    <input id="jadwal-svc-{{ $svc->id }}" type="datetime-local" name="jadwal" required 
                                        class="bg-surface border border-line rounded-xl text-ink text-xs min-h-[40px] px-3 focus:border-accent">
                                    <button type="submit" class="px-3.5 py-2 text-xs font-bold text-ink bg-surface border border-line hover:bg-surface-2 rounded-xl min-h-[40px] transition-colors">
                                        Simpan Jadwal Baru
                                    </button>
                                </form>
                                <form action="{{ route('pesanan.servis.batal', $svc) }}" method="POST" onsubmit="return confirm('Batalkan booking servis ini?')">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="px-3.5 py-2 text-xs font-bold text-accent bg-rose-500/10 border border-rose-500/20 hover:bg-rose-500/20 rounded-xl min-h-[40px] transition-colors">
                                        Batalkan
                                    </button>
                                </form>
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-muted">
                        <p>Belum ada riwayat booking servis.</p>
                        <a href="{{ route('servis.create') }}" class="mt-2 inline-block font-semibold text-accent hover:underline">Booking Servis Sekarang</a>
                    </div>
                @endforelse
            </div>
        </section>

        <!-- Section 4: Order Sparepart -->
        <section aria-labelledby="riwayat-spo" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-xl">
            <div class="px-6 py-4 bg-surface border-b border-line">
                <h2 id="riwayat-spo" class="font-bold text-base text-ink">Pesanan Suku Cadang (SPO)</h2>
                <p class="text-xs text-muted">Riwayat transaksi pembelian suku cadang asli dealer.</p>
            </div>
            <div class="divide-y divide-line">
                @forelse ($spos as $spo)
                    <div class="px-6 py-4 flex flex-wrap items-center justify-between gap-3 hover:bg-surface-2 transition-colors">
                        <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-4">
                            <a href="{{ route('sparepart.order.show', $spo->no_spo) }}" class="font-mono font-bold text-sm text-accent hover:text-accent underline underline-offset-2 min-h-[44px] inline-flex items-center">
                                {{ $spo->no_spo }}
                            </a>
                            <span class="text-xs sm:text-sm text-muted tabular-nums">
                                Total: Rp {{ number_format($spo->total, 0, ',', '.') }}
                                <span class="text-muted">-</span>
                                <span class="text-muted">{{ $spo->branch->nama }}</span>
                            </span>
                        </div>
                        <div class="flex items-center gap-3">
                            <x-status-badge :status="$spo->status" />
                            <a href="{{ route('sparepart.order.show', $spo->no_spo) }}" class="text-xs font-semibold text-muted hover:text-ink min-h-[44px] inline-flex items-center">
                                Detail &rarr;
                            </a>
                        </div>
                    </div>
                @empty
                    <div class="px-6 py-8 text-center text-sm text-muted">
                        <p>Belum ada pesanan suku cadang.</p>
                        <a href="{{ route('sparepart.index') }}" class="mt-2 inline-block font-semibold text-accent hover:underline">Buka Katalog Sparepart</a>
                    </div>
                @endforelse
            </div>
        </section>
    </div>
</x-app-layout>
