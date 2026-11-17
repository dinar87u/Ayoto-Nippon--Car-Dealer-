<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Order Sparepart</h2></x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-muted max-w-3xl">
            Order part dari halaman beli sparepart. Order <strong class="text-ink">MenungguBayar</strong> menunggu keputusan Anda:
            <strong class="text-ink">Lunas</strong> setelah bukti transfer diterima, atau <strong class="text-ink">Batal</strong>
            (stok otomatis dikembalikan). Keputusan mengirim WA + email ke pembeli.
        </p>

        @if (session('sukses'))
            <p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif

        <!-- Chip filter status -->
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.sparepart-orders.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ ! $status ? 'bg-ink text-surface ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Semua
                <span class="px-1.5 py-0.5 rounded-md {{ ! $status ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['semua'] }}</span>
            </a>
            <a href="{{ route('admin.sparepart-orders.index', ['status' => 'MenungguBayar']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ $status === 'MenungguBayar' ? 'bg-amber-500 text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Menunggu Bayar
                <span class="px-1.5 py-0.5 rounded-md {{ $status === 'MenungguBayar' ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['MenungguBayar'] }}</span>
            </a>
            <a href="{{ route('admin.sparepart-orders.index', ['status' => 'Lunas']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ $status === 'Lunas' ? 'bg-emerald-600 text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Lunas
                <span class="px-1.5 py-0.5 rounded-md {{ $status === 'Lunas' ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['Lunas'] }}</span>
            </a>
            <a href="{{ route('admin.sparepart-orders.index', ['status' => 'Batal']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ $status === 'Batal' ? 'bg-red-600 text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Batal
                <span class="px-1.5 py-0.5 rounded-md {{ $status === 'Batal' ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['Batal'] }}</span>
            </a>
        </div>

        @if ($orders->isEmpty())
            <div class="bg-surface p-8 rounded-2xl border border-line shadow-sm text-center">
                <p class="font-semibold text-ink">Belum ada order.</p>
                <p class="text-sm text-muted mt-1">Order dari halaman beli sparepart di website tampil di sini.</p>
            </div>
        @else
            <div class="bg-surface rounded-2xl border border-line shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60">
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">No. Order</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Pembeli</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Isi Order</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-ink">Total</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Status</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-ink w-56">Keputusan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($orders as $order)
                            <tr class="border-b border-line last:border-0 align-top hover:bg-surface-2/40 transition-colors">
                                <td class="px-5 py-4">
                                    <p class="font-mono font-semibold text-ink">{{ $order->no_spo }}</p>
                                    <p class="text-xs text-muted tabular-nums mt-0.5">{{ $order->created_at->format('d M Y H:i') }}</p>
                                    <p class="text-xs text-muted mt-0.5">{{ $order->branch->nama }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-ink">{{ $order->customer->nama }}</p>
                                    <p class="text-xs text-muted tabular-nums">{{ $order->customer->telp }}</p>
                                    <a href="{{ \App\Support\Wa::chat($order->customer->telp, 'Halo ' . $order->customer->nama . ', saya dari Ayoto Nippon mengenai order sparepart ' . $order->no_spo . '.') }}"
                                        target="_blank" rel="noopener"
                                        class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[28px]">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                        Chat Pembeli
                                    </a>
                                </td>
                                <td class="px-4 py-4 max-w-72">
                                    <ul class="space-y-1">
                                        @foreach ($order->items as $item)
                                            <li class="text-ink">{{ $item->sparepart->nama }} <span class="text-muted tabular-nums">× {{ $item->qty }}</span></li>
                                        @endforeach
                                    </ul>
                                </td>
                                <td class="px-4 py-4 text-right tabular-nums text-ink font-semibold whitespace-nowrap">Rp {{ number_format($order->total, 0, ',', '.') }}</td>
                                <td class="px-4 py-4">
                                    <x-status-badge :status="$order->status" />
                                    @if ($order->status === 'MenungguBayar' && $order->deadline_at)
                                        <p class="text-xs mt-1.5 {{ $order->deadline_at->isPast() ? 'text-red-600 dark:text-red-400' : 'text-muted' }} tabular-nums">
                                            Batas bayar {{ $order->deadline_at->format('d M H:i') }}
                                        </p>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    @if ($order->status === 'MenungguBayar')
                                        <form action="{{ route('admin.sparepart-orders.confirm', $order) }}" method="POST" class="flex gap-2">
                                            @csrf @method('PATCH')
                                            <button name="aksi" value="lunas"
                                                class="flex-1 px-3 py-2.5 text-xs font-bold text-white bg-emerald-700 hover:bg-emerald-800 rounded-lg min-h-[40px] transition">Tandai Lunas</button>
                                            <button name="aksi" value="batal"
                                                class="px-3 py-2.5 text-xs font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-lg min-h-[40px] transition">Batal</button>
                                        </form>
                                    @else
                                        <span class="text-xs text-muted">Sudah diputus</span>
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
