<x-app-layout>
    @php
        $tabs = [
            'penjualan' => 'Penjualan',
            'leads' => 'Leads & Konversi',
            'stok' => 'Stok Aging',
            'servis' => 'Servis & Pendapatan',
            'komisi' => 'Komisi Sales',
        ];
    @endphp
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Laporan — {{ $tabs[$jenis] ?? ucfirst($jenis) }}</h2></x-slot>
    <div class="py-6 w-full sm:px-6 lg:px-8 space-y-5">
        <div class="flex flex-wrap items-center gap-2 border-b border-line pb-3">
            @foreach ($tabs as $tabKey => $tabLabel)
                @php($active = $jenis === $tabKey)
                <a href="{{ route('admin.laporan.show', array_merge(['jenis' => $tabKey], request()->only(['dari', 'sampai']))) }}"
                   class="px-4 py-2 rounded-lg text-sm font-medium min-h-[40px] inline-flex items-center transition-colors {{ $active ? 'bg-accent-strong text-white font-semibold shadow-xs' : 'bg-surface border border-line text-muted hover:text-ink hover:bg-surface-2' }}">
                    {{ $tabLabel }}
                </a>
            @endforeach
        </div>

        <div class="flex flex-col sm:flex-row sm:items-center gap-3">
            @if ($jenis !== 'stok')
                <form method="GET" action="{{ route('admin.laporan.show', $jenis) }}" class="flex flex-wrap items-center gap-3 flex-1">
                    <input type="date" name="dari" value="{{ request('dari') }}" aria-label="Dari tanggal" class="bg-surface border border-line rounded-lg text-ink text-sm min-h-[44px] px-3">
                    <input type="date" name="sampai" value="{{ request('sampai') }}" aria-label="Sampai tanggal" class="bg-surface border border-line rounded-lg text-ink text-sm min-h-[44px] px-3">
                    <button class="px-4 py-2 text-sm font-semibold text-white bg-accent-strong rounded-lg min-h-[44px]">Filter</button>
                    @if (request()->filled('dari') || request()->filled('sampai'))
                        <a href="{{ route('admin.laporan.show', $jenis) }}" class="px-3 py-2 text-sm font-medium text-muted hover:text-ink underline min-h-[44px] inline-flex items-center">Reset</a>
                    @endif
                </form>
            @else
                <p class="text-sm text-muted flex-1">Menampilkan stok unit berstatus <strong class="text-ink">Tersedia</strong> saat ini.</p>
            @endif
            <a href="{{ route('admin.laporan.export', array_merge(['jenis' => $jenis], request()->only(['dari', 'sampai']))) }}"
               class="px-4 py-2 text-sm font-semibold text-ink bg-surface border border-line hover:bg-surface-2 rounded-lg min-h-[44px] inline-flex items-center justify-center shrink-0">
                Export Excel
            </a>
        </div>

        @if (auth()->user()->hasRole('admin-cabang'))
            <p class="p-3 text-sm text-ink bg-blue-50 dark:bg-blue-500/10 border border-blue-200 dark:border-blue-500/30 rounded-lg">Menampilkan data {{ auth()->user()->branch->nama }}.</p>
        @endif

        @if ($jenis === 'penjualan' && $total !== null)
            <div class="bg-surface border border-line p-5 rounded-xl shadow-sm">
                <p class="text-sm text-muted">Total omzet{{ auth()->user()->hasRole('admin-cabang') ? ' '.auth()->user()->branch->nama : ' semua cabang' }}</p>
                <p class="text-2xl font-bold text-ink tabular-nums mt-1">Rp {{ number_format($total, 0, ',', '.') }}</p>
            </div>
        @endif

        @if ($jenis === 'komisi')
            <p class="text-xs text-muted">Tarif komisi 1% bersifat sementara dan perlu ketetapan direksi.</p>
        @endif

        @if (empty($rows))
            <div class="bg-surface border border-line p-6 rounded-xl shadow-sm">
                <p class="font-semibold text-ink">Tidak ada data.</p>
                <p class="text-sm text-muted mt-0.5">Ubah rentang tanggal atau cabang.</p>
            </div>
        @else
            <div class="bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60 text-left text-xs uppercase tracking-wider text-muted">
                            @foreach ($headers as $h)<th class="px-4 py-3">{{ $h }}</th>@endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @foreach ($rows as $row)
                            <tr class="hover:bg-surface-2/40 transition-colors">
                                @foreach ($row as $i => $cell)
                                    <td class="px-4 py-3 {{ $i > 0 && is_numeric($cell) ? 'text-right tabular-nums' : '' }}">{{ is_numeric($cell) && $cell > 999 ? number_format($cell, 0, ',', '.') : $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @if (isset($paginator) && $paginator->hasPages())
                <div>{{ $paginator->links() }}</div>
            @endif
        @endif
    </div>
</x-app-layout>
