<x-app-layout>
    @php
        $bagian = $bagian ?? 'semua';
        $metaBagian = [
            'semua' => ['Pusat Aktivitas Akun', 'Riwayat & Pesanan Saya'],
            'spk' => ['Pesanan Saya', 'Surat Pesanan Kendaraan (SPK)'],
            'test-drive' => [null, 'Jadwal Uji Kemudi (Test Drive)'],
            'servis' => [null, 'Booking Servis Berkala'],
            'sparepart' => ['Pesanan Saya', 'Pesanan Suku Cadang (SPO)'],
        ];
        [$subJudul, $judulUtama] = $metaBagian[$bagian] ?? $metaBagian['semua'];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                @if ($subJudul)
                    <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">{{ $subJudul }}</p>
                @endif
                <h1 class="{{ $subJudul ? 'mt-0.5 ' : '' }}text-2xl font-extrabold text-ink tracking-tight">{{ $judulUtama }}</h1>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if (in_array($bagian, ['semua', 'spk'], true))
                    <a href="{{ route('spk.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors">
                        + Ajukan SPK
                    </a>
                @endif
                @if ($bagian === 'test-drive')
                    <a href="{{ route('test-drive.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors">
                        + Booking Test Drive
                    </a>
                @endif
                @if (in_array($bagian, ['semua', 'servis'], true))
                    <a href="{{ route('servis.create') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold {{ $bagian === 'servis' ? 'bg-accent-strong text-white hover:bg-[#9E0E19]' : 'bg-surface border border-line text-ink hover:border-accent' }} transition-colors">
                        + Booking Servis
                    </a>
                @endif
                @if ($bagian === 'sparepart')
                    <a href="{{ route('sparepart.index') }}" class="px-3.5 py-2 rounded-xl text-xs font-bold bg-accent-strong text-white hover:bg-[#9E0E19] transition-colors">
                        + Beli Sparepart
                    </a>
                @endif
            </div>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('sukses'))
            <div class="p-4 text-sm font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                {{ session('sukses') }}
            </div>
        @endif

        @if (in_array($bagian, ['semua', 'spk'], true))
        <!-- Section 1: SPK -->
        <section aria-labelledby="riwayat-spk" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-sm">
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
        @endif

        @if (in_array($bagian, ['semua', 'test-drive'], true))
        <!-- Section 2: Test Drive -->
        <section aria-labelledby="riwayat-td" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-sm">
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
        @endif

        @if (in_array($bagian, ['semua', 'servis'], true))
        <!-- Section 3: Servis -->
        <section aria-labelledby="riwayat-svc" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-sm">
            <div class="px-6 py-4 bg-surface border-b border-line">
                <h2 id="riwayat-svc" class="font-bold text-base text-ink">Booking Servis Berkala</h2>
                <p class="text-xs text-muted">Pantau proses perbaikan dan unduh invoice resmi servis.</p>
            </div>
            <div class="divide-y divide-line">
                @forelse ($servis as $svc)
                    @php
                        $totalRincian = (int) $svc->details->sum('subtotal');
                        $biayaTampil = $totalRincian > 0 ? $totalRincian : (int) $svc->estimasi_biaya;
                    @endphp
                    <div class="relative overflow-hidden px-6 py-5 space-y-4">
                        @if ($svc->status === 'Selesai' && $svc->details->isEmpty())
                            <div aria-hidden="true" class="pointer-events-none select-none absolute inset-0 z-0 flex items-center justify-center overflow-hidden">
                                <div class="-rotate-12 px-6 py-2 rounded-xl border-[3px] border-red-600/25 dark:border-red-400/25">
                                    <span class="block font-black text-4xl tracking-[0.28em] text-red-600/20 dark:text-red-400/20 uppercase leading-none">
                                        LUNAS
                                    </span>
                                </div>
                            </div>
                        @endif
                        <div class="relative z-10 flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-mono font-extrabold text-sm text-accent">{{ $svc->no_svc }}</span>
                                    <span class="text-xs text-ink font-mono font-bold px-2.5 py-0.5 bg-surface-2 border border-line rounded-md">{{ $svc->nopol }}</span>
                                </div>
                                <p class="text-xs text-muted mt-1.5 tabular-nums">
                                    {{ $svc->jadwal->locale('id')->translatedFormat('d F Y, H:i') }} WIB &middot; Cabang {{ $svc->branch?->nama ?? '-' }}
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if ($svc->status === 'Selesai')
                                    <a href="{{ route('servis.invoice', $svc) }}" class="px-3.5 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 hover:bg-emerald-500/20 transition-colors inline-flex items-center gap-1.5">
                                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5m0 0l5-5m-5 5V4" />
                                        </svg>
                                        <span>Unduh Invoice</span>
                                    </a>
                                @else
                                    <x-status-badge :status="$svc->status" />
                                @endif
                            </div>
                        </div>

                        <dl class="relative z-10 grid grid-cols-2 lg:grid-cols-4 gap-3 text-xs">
                            <div class="p-3 rounded-xl bg-surface-2/60 border border-line">
                                <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Nomor Rangka (VIN)</dt>
                                <dd class="mt-1 font-mono font-bold text-ink break-all">{{ $svc->vin ?: '-' }}</dd>
                            </div>
                            <div class="p-3 rounded-xl bg-surface-2/60 border border-line">
                                <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Odometer Saat Servis</dt>
                                <dd class="mt-1 font-bold text-ink tabular-nums">{{ number_format($svc->km, 0, ',', '.') }} km</dd>
                            </div>
                            <div class="p-3 rounded-xl bg-surface-2/60 border border-line">
                                <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Mekanik Penanggung Jawab</dt>
                                <dd class="mt-1 font-bold text-ink">{{ $svc->mekanik?->name ?? 'Menunggu Penugasan' }}</dd>
                            </div>
                            <div class="p-3 rounded-xl bg-surface-2/60 border border-line">
                                <dt class="text-[10px] font-semibold uppercase tracking-wider text-muted">Biaya / Estimasi</dt>
                                <dd class="mt-1 font-bold text-ink tabular-nums">
                                    {{ $biayaTampil > 0 ? 'Rp ' . number_format($biayaTampil, 0, ',', '.') : 'Menunggu Estimasi' }}
                                    @if ($svc->metode_bayar)
                                        <span class="block text-[11px] font-medium text-muted mt-0.5">Bayar: {{ $svc->metode_bayar }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        <div class="grid grid-cols-1 {{ $svc->tanggapan ? 'md:grid-cols-2' : '' }} gap-3 text-xs">
                            <div class="p-3.5 rounded-xl bg-surface-2/40 border border-line space-y-1">
                                <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted">Keluhan / Permintaan Servis</span>
                                <p class="text-ink font-medium leading-relaxed">{{ $svc->keluhan ?: 'Servis berkala rutin.' }}</p>
                            </div>
                            @if ($svc->tanggapan)
                                <div class="p-3.5 rounded-xl bg-surface-2/40 border border-line space-y-1">
                                    <span class="block text-[10px] font-semibold uppercase tracking-wider text-muted">Catatan / Tanggapan Bengkel</span>
                                    <p class="text-ink font-medium leading-relaxed">{{ $svc->tanggapan }}</p>
                                </div>
                            @endif
                        </div>

                        @if ($svc->details->isNotEmpty())
                            <div class="relative z-10 rounded-2xl border border-line overflow-hidden bg-surface">
                                @if ($svc->status === 'Selesai')
                                    <div aria-hidden="true" class="pointer-events-none select-none absolute inset-0 z-0 flex items-center justify-center overflow-hidden">
                                        <div class="-rotate-12 px-5 py-1.5 rounded-lg border-[3px] border-red-600/25 dark:border-red-400/25 ring-1 ring-inset ring-red-600/20 dark:ring-red-400/20">
                                            <span class="block font-black text-3xl sm:text-4xl tracking-[0.28em] text-red-600/20 dark:text-red-400/20 uppercase leading-none">
                                                LUNAS
                                            </span>
                                        </div>
                                    </div>
                                @endif
                                <div class="relative z-10 px-4 py-2.5 bg-surface-2/60 border-b border-line flex items-center justify-between">
                                    <span class="text-[11px] font-bold uppercase tracking-wider text-muted">Rincian Pekerjaan &amp; Suku Cadang</span>
                                    <span class="text-xs font-extrabold text-ink tabular-nums">Total: Rp {{ number_format($totalRincian, 0, ',', '.') }}</span>
                                </div>
                                <div class="relative z-10 overflow-x-auto">
                                    <table class="w-full text-xs text-left">
                                        <thead class="text-[10px] uppercase tracking-wider text-muted border-b border-line">
                                            <tr>
                                                <th class="px-4 py-2.5 font-semibold">Jenis</th>
                                                <th class="px-4 py-2.5 font-semibold">Deskripsi</th>
                                                <th class="px-4 py-2.5 font-semibold text-right">Qty</th>
                                                <th class="px-4 py-2.5 font-semibold text-right">Harga Satuan</th>
                                                <th class="px-4 py-2.5 font-semibold text-right">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-line">
                                            @foreach ($svc->details as $item)
                                                <tr>
                                                    <td class="px-4 py-2.5 font-semibold text-muted">{{ $item->jenis }}</td>
                                                    <td class="px-4 py-2.5 font-medium text-ink">{{ $item->deskripsi }}</td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums text-ink">{{ $item->qty }}</td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums text-muted">Rp {{ number_format($item->harga_satuan, 0, ',', '.') }}</td>
                                                    <td class="px-4 py-2.5 text-right tabular-nums font-bold text-ink">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif

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
        @endif

        @if (in_array($bagian, ['semua', 'sparepart'], true))
        <!-- Section 4: Order Sparepart -->
        <section aria-labelledby="riwayat-spo" class="bg-surface border border-line rounded-3xl overflow-hidden shadow-sm">
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
        @endif
    </div>
</x-app-layout>
