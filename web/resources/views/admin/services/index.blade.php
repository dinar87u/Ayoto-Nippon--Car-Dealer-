<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Booking Servis</h2></x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-muted max-w-3xl">
            Antrean pengerjaan bengkel. Status <strong class="text-ink">Dikerjakan</strong> dan <strong class="text-ink">Selesai</strong>
            diperbarui lewat halaman detail, lengkap dengan rincian pekerjaan & sparepart-nya.
        </p>

        @if (session('sukses'))
            <p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif

        <!-- Chip filter status -->
        <div class="mb-5 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.services.index') }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ ! request('status') ? 'bg-ink text-surface ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Semua
                <span class="px-1.5 py-0.5 rounded-md {{ ! request('status') ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['semua'] }}</span>
            </a>
            @foreach (['Menunggu', 'Dikonfirmasi', 'Dikerjakan', 'Selesai', 'Batal'] as $s)
                <a href="{{ route('admin.services.index', ['status' => $s]) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ request('status') === $s ? 'bg-accent-strong text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                    {{ $s }}
                    <span class="px-1.5 py-0.5 rounded-md {{ request('status') === $s ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah[$s] }}</span>
                </a>
            @endforeach
        </div>

        @if ($bookings->isEmpty())
            <div class="bg-surface p-8 rounded-2xl border border-line shadow-sm text-center">
                <p class="font-semibold text-ink">Belum ada booking.</p>
                <p class="text-sm text-muted mt-1">Booking dari halaman "Booking Servis" di website tampil di sini.</p>
            </div>
        @else
            <div class="bg-surface rounded-2xl border border-line shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60">
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">No. Servis</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Customer</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Kendaraan</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Jadwal & Cabang</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Mekanik</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bookings as $booking)
                            <tr class="border-b border-line last:border-0 align-top hover:bg-surface-2/40 transition-colors">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.services.show', $booking) }}" class="font-mono font-semibold text-accent hover:underline min-h-[44px] inline-flex items-center">{{ $booking->no_svc }}</a>
                                    <p class="text-xs text-muted tabular-nums mt-0.5">{{ $booking->created_at->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-ink">{{ $booking->customer->nama }}</p>
                                    <p class="text-xs text-muted tabular-nums">{{ $booking->customer->telp }}</p>
                                    @if ($booking->customer?->telp)
                                        <a href="{{ \App\Support\Wa::chat($booking->customer->telp, \App\Support\Notifikasi::servisChat($booking)) }}"
                                            target="_blank" rel="noopener"
                                            class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[28px]">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                            Chat Pembeli
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-mono font-semibold text-ink">{{ $booking->nopol }}</p>
                                    <p class="text-xs text-muted tabular-nums mt-0.5">{{ number_format($booking->km, 0, ',', '.') }} km</p>
                                    @if ($booking->keluhan)
                                        <p class="text-xs text-muted mt-1 max-w-52">📝 {{ \Illuminate\Support\Str::limit($booking->keluhan, 60) }}</p>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <p class="text-ink font-semibold tabular-nums">{{ $booking->jadwal->format('d M Y H:i') }}</p>
                                    @if ($booking->jadwal->isToday())
                                        <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-bold bg-accent/10 text-accent">Hari ini</span>
                                    @elseif ($booking->jadwal->isPast() && ! in_array($booking->status, ['Selesai', 'Batal'], true))
                                        <span class="mt-1 inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">Terlewat</span>
                                    @endif
                                    <p class="text-xs text-muted mt-1">{{ $booking->branch->nama }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    @if ($booking->mekanik)
                                        <span class="text-ink font-medium">{{ $booking->mekanik->name }}</span>
                                    @else
                                        <span class="text-xs text-muted">Belum ditugaskan</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <x-status-badge :status="$booking->status" />
                                        @if ($booking->status === 'Selesai')
                                            <a href="{{ route('servis.invoice', $booking) }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center gap-1">Invoice ↓</a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $bookings->links() }}</div>
        @endif
    </div>
</x-app-layout>
