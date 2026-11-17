<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-ink leading-tight">Sparepart</h2>
            @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                <a href="{{ route('admin.spareparts.create') }}"
                    class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">
                    + Tambah Sparepart
                </a>
            @endhasanyrole
        </div>
    </x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-muted max-w-3xl">
            Data sparepart per cabang. Stok dipakai otomatis saat order part customer terkonfirmasi dan dikembalikan saat order dibatalkan.
        </p>

        @if (session('sukses'))
            <p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif

        <!-- Chip filter kondisi stok -->
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.spareparts.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ ! request('filter') ? 'bg-ink text-surface ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Semua
                <span class="px-1.5 py-0.5 rounded-md {{ ! request('filter') ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['semua'] }}</span>
            </a>
            <a href="{{ route('admin.spareparts.index', ['filter' => 'aktif']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ request('filter') === 'aktif' ? 'bg-emerald-600 text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Aktif dijual
                <span class="px-1.5 py-0.5 rounded-md {{ request('filter') === 'aktif' ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['aktif'] }}</span>
            </a>
            <a href="{{ route('admin.spareparts.index', ['filter' => 'stok-rendah']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ request('filter') === 'stok-rendah' ? 'bg-amber-500 text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Stok rendah (≤3)
                <span class="px-1.5 py-0.5 rounded-md {{ request('filter') === 'stok-rendah' ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['stok-rendah'] }}</span>
            </a>
            <a href="{{ route('admin.spareparts.index', ['filter' => 'habis']) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ request('filter') === 'habis' ? 'bg-red-600 text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Habis
                <span class="px-1.5 py-0.5 rounded-md {{ request('filter') === 'habis' ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['habis'] }}</span>
            </a>
        </div>

        @if ($parts->isEmpty())
            <div class="bg-surface p-8 rounded-2xl border border-line shadow-sm text-center">
                <p class="font-semibold text-ink">Belum ada sparepart.</p>
                <p class="text-sm text-muted mt-1">Tambahkan lewat tombol "Tambah Sparepart" di atas.</p>
            </div>
        @else
            <div class="bg-surface rounded-2xl border border-line shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60">
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">Sparepart</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Cabang</th>
                            <th class="text-right px-4 py-3.5 font-semibold text-ink">Harga</th>
                            <th class="text-center px-4 py-3.5 font-semibold text-ink">Stok</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Status</th>
                            @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                                <th class="text-right px-5 py-3.5 font-semibold text-ink">Kelola</th>
                            @endhasanyrole
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($parts as $part)
                            <tr class="border-b border-line last:border-0 align-top hover:bg-surface-2/40 transition-colors">
                                <td class="px-5 py-4">
                                    <p class="font-medium text-ink">{{ $part->nama }}</p>
                                    @if ($part->kode)
                                        <p class="text-xs text-muted font-mono mt-0.5">{{ $part->kode }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4 text-muted">{{ $part->branch->nama }}</td>
                                <td class="px-4 py-4 text-right tabular-nums text-ink font-medium">Rp {{ number_format($part->harga, 0, ',', '.') }}</td>
                                <td class="px-4 py-4 text-center">
                                    <span class="inline-flex items-center justify-center px-2.5 py-1 rounded-full text-xs font-bold tabular-nums
                                        {{ $part->stok === 0 ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-500/30' : ($part->stok <= 3 ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30' : 'bg-surface-2 text-ink ring-1 ring-line') }}">
                                        {{ $part->stok }}
                                    </span>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($part->aktif)
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 ring-1 ring-emerald-200 dark:ring-emerald-500/30">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Aktif dijual
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-2 text-muted ring-1 ring-line">Nonaktif</span>
                                    @endif
                                </td>
                                @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                                    <td class="px-5 py-4 text-right">
                                        <a href="{{ route('admin.spareparts.edit', $part) }}"
                                            class="inline-flex items-center px-3 py-2 text-xs font-bold text-ink bg-surface-2 hover:bg-line rounded-lg min-h-[36px] transition">Ubah</a>
                                    </td>
                                @endhasanyrole
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $parts->links() }}</div>
        @endif
    </div>
</x-app-layout>
