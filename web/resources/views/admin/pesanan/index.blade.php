<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Pesanan & Serah Terima</h2></x-slot>

    @php
        $chip = fn (string $label, ?string $nilai, int $n, string $kelasAktif) => [
            'label' => $label, 'nilai' => $nilai, 'n' => $n, 'kelasAktif' => $kelasAktif,
        ];
        $daftarChip = [
            $chip('Semua', null, $jumlah['semua'], 'bg-ink text-surface'),
            $chip('Menunggu Bayar', 'MenungguBayar', $jumlah['MenungguBayar'], 'bg-amber-500 text-white'),
            $chip('Perlu Verifikasi', 'MenungguVerifikasi', $jumlah['MenungguVerifikasi'], 'bg-blue-600 text-white'),
            $chip('Deal', 'Deal', $jumlah['Deal'], 'bg-emerald-600 text-white'),
            $chip('Lunas', 'Lunas', $jumlah['Lunas'], 'bg-emerald-700 text-white'),
            $chip('Batal / Expired', 'Batal', $jumlah['arsip'], 'bg-red-600 text-white'),
        ];
    @endphp

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-muted max-w-3xl">
            Pantau seluruh SPK dari penerbitan sampai serah terima. SPK yang sudah Deal bisa diisi
            <strong class="text-ink">jadwal serah terima</strong> dan <strong class="text-ink">tenggat STNK/BPKB</strong>-nya di sini.
        </p>

        @if (session('sukses'))
            <p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 text-sm bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                @foreach ($errors->all() as $galat)<p>{{ $galat }}</p>@endforeach
            </div>
        @endif

        <!-- Chip filter status -->
        <div class="mb-5 flex flex-wrap items-center gap-2">
            @foreach ($daftarChip as $c)
                <a href="{{ $c['nilai'] ? route('admin.pesanan.index', ['status' => $c['nilai']]) : route('admin.pesanan.index') }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ $status === $c['nilai'] || ($status === null && $c['nilai'] === null) ? $c['kelasAktif'] . ' ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                    {{ $c['label'] }}
                    <span class="px-1.5 py-0.5 rounded-md {{ $status === $c['nilai'] || ($status === null && $c['nilai'] === null) ? 'bg-white/20' : 'bg-surface-2' }}">{{ $c['n'] }}</span>
                </a>
            @endforeach
        </div>

        @if ($orders->isEmpty())
            <div class="bg-surface p-8 rounded-2xl border border-line shadow-sm text-center">
                <p class="font-semibold text-ink">Tidak ada pesanan.</p>
                <p class="text-sm text-muted mt-1">Belum ada SPK yang cocok dengan filter "{{ $status ? strtolower($status) : 'ini' }}".</p>
            </div>
        @else
            <div class="bg-surface rounded-2xl border border-line shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60">
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">SPK</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Pelanggan</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Unit</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Metode</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Status</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-ink w-72">Serah Terima & Dokumen</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-line last:border-0 align-top hover:bg-surface-2/40 transition-colors">
                                <td class="px-5 py-4">
                                    <a href="{{ route('spk.show', $order->no_spk) }}" class="font-mono font-semibold text-accent hover:underline min-h-[44px] inline-flex items-center">{{ $order->no_spk }}</a>
                                    <p class="text-xs text-muted tabular-nums mt-0.5">{{ $order->created_at->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-ink">{{ $order->customer->nama }}</p>
                                    <p class="text-xs text-muted tabular-nums">{{ $order->customer->telp }}</p>
                                    <a href="{{ \App\Support\Wa::chat($order->customer->telp, 'Halo ' . $order->customer->nama . ', saya dari Ayoto Nippon mengenai pesanan ' . $order->no_spk . '.') }}"
                                        target="_blank" rel="noopener"
                                        class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[28px]">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                        Chat Pembeli
                                    </a>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-ink font-medium leading-snug">{{ $order->unit->variant->carModel->brand->nama }} {{ $order->unit->variant->carModel->nama }} {{ $order->unit->variant->nama }}</p>
                                    <p class="text-xs text-muted font-mono mt-0.5">{{ $order->unit->vin }}</p>
                                    <p class="text-xs text-muted tabular-nums mt-0.5">Rp {{ number_format($order->unit->harga, 0, ',', '.') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <span class="text-ink font-semibold">{{ $order->metode }}</span>
                                    @if ($order->metode === 'Kredit' && $order->leasing)
                                        <p class="text-xs text-muted mt-0.5">{{ $order->leasing->nama }}</p>
                                        <p class="text-xs text-muted">DP {{ 'Rp ' . number_format($order->dp_rencana, 0, ',', '.') }} · {{ $order->tenor }} bln</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <x-status-badge :status="$order->statusTampil()" />
                                        @if ($order->status === 'MenungguBayar')
                                            <p class="text-xs {{ $order->deadline_at->isPast() ? 'text-red-600 dark:text-red-400' : 'text-muted' }}">
                                                Batas bayar {{ $order->deadline_at->format('d M H:i') }}
                                            </p>
                                        @elseif ($order->status === 'MenungguVerifikasi')
                                            <a href="{{ route('admin.verify.index') }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center gap-1">Cek bukti ↗</a>
                                        @elseif ($order->statusTampil() === 'Lunas' && $order->invoice)
                                            <a href="{{ route('admin.invoices.download', $order->invoice) }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center gap-1">Invoice ↓</a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($order->status === 'Deal')
                                        <form action="{{ route('admin.pesanan.update', $order) }}" method="POST" class="space-y-2.5 max-w-64">
                                            @csrf @method('PATCH')
                                            <div>
                                                <label for="serah-{{ $order->id }}" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1">Unit siap diambil</label>
                                                <input id="serah-{{ $order->id }}" type="datetime-local" name="jadwal_serah_at" value="{{ $order->jadwal_serah_at?->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border-line text-ink text-sm min-h-[40px]">
                                            </div>
                                            <div>
                                                <label for="stnk-{{ $order->id }}" class="block text-[11px] font-semibold uppercase tracking-wider text-muted mb-1">STNK & BPKB selesai</label>
                                                <input id="stnk-{{ $order->id }}" type="datetime-local" name="stnk_selesai_at" value="{{ $order->stnk_selesai_at?->format('Y-m-d\TH:i') }}" class="w-full rounded-lg border-line text-ink text-sm min-h-[40px]">
                                            </div>
                                            <button class="w-full px-4 py-2 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-lg min-h-[40px] transition">Simpan</button>
                                        </form>
                                        @if ($order->bast_at)
                                            <p class="mt-2 inline-flex items-center gap-1 text-xs font-semibold text-emerald-700 dark:text-emerald-300">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Diserahkan {{ $order->bast_at->format('d M Y H:i') }}
                                            </p>
                                        @else
                                            <p class="mt-2 text-xs text-muted">BAST diunggah lewat halaman SPK.</p>
                                        @endif
                                    @elseif ($order->jadwal_serah_at || $order->stnk_selesai_at)
                                        @if ($order->jadwal_serah_at)
                                            <p class="text-xs text-ink tabular-nums">Serah terima: {{ $order->jadwal_serah_at->format('d M Y H:i') }}</p>
                                        @endif
                                        @if ($order->stnk_selesai_at)
                                            <p class="text-xs text-ink tabular-nums">STNK: {{ $order->stnk_selesai_at->format('d M Y H:i') }}</p>
                                        @endif
                                    @else
                                        <p class="text-xs text-muted">Menunggu SPK berstatus Deal.</p>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $orders->links() }}</div>
        @endif
    </div>
</x-app-layout>
