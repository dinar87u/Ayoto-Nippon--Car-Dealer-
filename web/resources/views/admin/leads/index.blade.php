<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Leads</h2></x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-muted max-w-3xl">
            Semua calon pembeli yang tertangkap dari website, test drive, dan SPK, termasuk yang masuk
            <strong class="text-ink">tanpa akun</strong>. Klik lead untuk melihat riwayat follow-up atau menetapkan sales.
        </p>

        <!-- Chip filter status -->
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <a href="{{ route('admin.leads.index', array_filter(['sales' => request('sales')])) }}"
                class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ ! request('status') ? 'bg-ink text-surface ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                Semua
                <span class="px-1.5 py-0.5 rounded-md {{ ! request('status') ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah['semua'] }}</span>
            </a>
            @foreach (['Baru', 'Dihubungi', 'TestDrive', 'SPK', 'Deal', 'Lunas', 'Batal'] as $s)
                <a href="{{ route('admin.leads.index', array_filter(['status' => $s, 'sales' => request('sales')])) }}"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] ring-1 {{ request('status') === $s ? 'bg-accent-strong text-white ring-transparent shadow-sm' : 'bg-surface text-muted ring-line hover:text-ink' }}">
                    {{ $s }}
                    <span class="px-1.5 py-0.5 rounded-md {{ request('status') === $s ? 'bg-white/20' : 'bg-surface-2' }}">{{ $jumlah[$s] }}</span>
                </a>
            @endforeach
        </div>

        <!-- Filter sales -->
        @if ($salesList->isNotEmpty())
            <form method="GET" action="{{ route('admin.leads.index') }}" class="mb-4 flex flex-wrap items-center gap-2">
                @if (request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <label for="filter-sales" class="text-xs font-semibold uppercase tracking-wider text-muted">Sales</label>
                <select id="filter-sales" name="sales" onchange="this.form.submit()"
                    class="rounded-lg border-line text-sm min-h-[40px] min-w-56 bg-surface">
                    <option value="">Semua sales</option>
                    @foreach ($salesList as $sales)
                        <option value="{{ $sales->id }}" @selected((string) request('sales') === (string) $sales->id)>{{ $sales->name }}</option>
                    @endforeach
                </select>
                @if (request('sales') || request('status'))
                    <a href="{{ route('admin.leads.index') }}" class="text-xs font-semibold text-accent hover:underline min-h-[36px] inline-flex items-center">Reset</a>
                @endif
            </form>
        @endif

        @if ($leads->isEmpty())
            <div class="bg-surface p-8 rounded-2xl border border-line shadow-sm text-center">
                <p class="font-semibold text-ink">Belum ada leads.</p>
                <p class="text-sm text-muted mt-1">Leads terbentuk otomatis dari form "Ajukan SPK", test drive, dan SPK resmi.</p>
            </div>
        @else
            <div class="bg-surface rounded-2xl border border-line shadow-sm overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60">
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">Lead</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Customer</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Sumber</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Status</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Sales</th>
                            <th class="text-left px-4 py-3.5 font-semibold text-ink">Follow-up</th>
                            <th class="text-left px-5 py-3.5 font-semibold text-ink">Kebutuhan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($leads as $lead)
                            <tr class="border-b border-line last:border-0 align-top hover:bg-surface-2/40 transition-colors">
                                <td class="px-5 py-4">
                                    <a href="{{ route('admin.leads.show', $lead) }}" class="font-mono font-semibold text-accent hover:underline min-h-[44px] inline-flex items-center">#{{ $lead->id }}</a>
                                    <p class="text-xs text-muted tabular-nums mt-0.5">{{ $lead->created_at->format('d M Y H:i') }}</p>
                                </td>
                                <td class="px-4 py-4">
                                    <p class="font-medium text-ink">{{ $lead->customer->nama ?? '-' }}</p>
                                    <p class="text-xs text-muted tabular-nums">{{ $lead->customer->telp ?? '-' }}</p>
                                    @if ($lead->customer)
                                        <a href="{{ \App\Support\Wa::chat($lead->customer->telp, 'Halo ' . $lead->customer->nama . ', saya dari Ayoto Nippon. Menindaklanjuti ketertarikan Anda, ada yang bisa kami bantu?') }}"
                                            target="_blank" rel="noopener"
                                            class="mt-1.5 inline-flex items-center gap-1 px-2.5 py-1.5 text-[11px] font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[28px]">
                                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                            Chat Pembeli
                                        </a>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-2 text-muted ring-1 ring-line">{{ $lead->sumber }}</span>
                                </td>
                                <td class="px-4 py-4"><x-status-badge :status="$lead->status" /></td>
                                <td class="px-4 py-4">
                                    @if ($lead->sales)
                                        <span class="text-ink font-medium">{{ $lead->sales->name }}</span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30">Belum ditetapkan</span>
                                    @endif
                                </td>
                                <td class="px-4 py-4">
                                    @if ($lead->followup_at)
                                        <p class="text-xs {{ $lead->followup_at->isPast() ? 'text-red-600 dark:text-red-400 font-semibold' : 'text-ink' }} tabular-nums">
                                            {{ $lead->followup_at->isPast() ? '⚠ Jatuh tempo ' : 'Dijadwalkan ' }}
                                        </p>
                                        <p class="text-xs text-muted tabular-nums">{{ $lead->followup_at->format('d M Y H:i') }}</p>
                                    @else
                                        <p class="text-xs text-muted">Belum dijadwalkan</p>
                                    @endif
                                </td>
                                <td class="px-5 py-4 text-muted max-w-72">{{ \Illuminate\Support\Str::limit($lead->kebutuhan_text, 90) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $leads->links() }}</div>
        @endif
    </div>
</x-app-layout>
