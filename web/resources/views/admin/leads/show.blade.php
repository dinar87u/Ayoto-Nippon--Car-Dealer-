<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="font-semibold text-xl text-ink leading-tight">Lead #{{ $lead->id }}</h2>
            <x-status-badge :status="$lead->status" />
        </div>
    </x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <a href="{{ route('admin.leads.index') }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center min-h-[36px]">← Kembali ke daftar leads</a>

        @if (session('sukses'))
            <p class="mt-3 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif

        <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            <!-- Detail lead -->
            <div class="lg:col-span-2 bg-surface rounded-2xl border border-line shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-line">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Customer</p>
                        <p class="mt-1 text-lg font-bold text-ink">{{ $lead->customer->nama ?? '-' }}</p>
                        <p class="text-sm text-muted tabular-nums">{{ $lead->customer->telp ?? '-' }}</p>
                    </div>
                    @if ($lead->customer?->telp)
                        <a href="{{ \App\Support\Wa::chat($lead->customer->telp, \App\Support\Notifikasi::followupLead($lead)) }}"
                            target="_blank" rel="noopener"
                            class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-xl transition min-h-[44px]">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                            Chat Pembeli
                        </a>
                    @endif
                </div>

                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted">Sumber</dt>
                        <dd class="mt-1"><span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold bg-surface-2 text-muted ring-1 ring-line">{{ $lead->sumber }}</span></dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted">Sales</dt>
                        <dd class="mt-1 text-ink font-medium">{{ $lead->sales->name ?? 'Belum ditetapkan' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted">Reminder</dt>
                        <dd class="mt-1 text-ink tabular-nums">{{ $lead->followup_at?->format('d M Y H:i') ?? '-' }}</dd>
                    </div>
                </dl>

                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Kebutuhan Customer</p>
                    <p class="text-sm text-ink leading-relaxed bg-surface-2 border border-line rounded-xl p-4">{{ $lead->kebutuhan_text ?? '-' }}</p>
                </div>
            </div>

            <!-- Tetapkan sales -->
            <div class="bg-surface rounded-2xl border border-line shadow-sm p-6">
                <h3 class="font-bold text-ink">Tetapkan Sales</h3>
                <p class="text-xs text-muted mt-1">Sales yang menangani lead ini untuk follow-up.</p>
                <form action="{{ route('admin.leads.assign', $lead) }}" method="POST" class="mt-4 space-y-3">
                    @csrf @method('PATCH')
                    <select name="sales_id" class="w-full rounded-lg border-line text-sm min-h-[44px]">
                        <option value="">Tanpa sales</option>
                        @foreach ($salesList as $sales)
                            <option value="{{ $sales->id }}" @selected($lead->sales_id === $sales->id)>{{ $sales->name }}</option>
                        @endforeach
                    </select>
                    <button class="w-full px-4 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">Simpan Penugasan</button>
                </form>
            </div>
        </div>

        <!-- Form follow-up -->
        <div class="mt-5 bg-surface rounded-2xl border border-line shadow-sm p-6">
            <h3 class="font-bold text-ink">Tambah Follow-up</h3>
            @if ($lead->status === 'Lunas')
                <p class="mt-2 text-xs text-amber-700 dark:text-amber-300">Status Lunas tidak bisa diubah lagi. Catatan follow-up masih bisa ditambah.</p>
            @endif
            <form action="{{ route('admin.leads.followup', $lead) }}" method="POST" class="mt-4 space-y-4">
                @csrf
                <div>
                    <label for="catatan" class="block text-xs font-semibold uppercase tracking-wider text-muted">Catatan hasil</label>
                    <textarea id="catatan" name="catatan" required rows="2" placeholder="Hasil menghubungi customer, mis. 'sudah di-WA, minta waktu pikir 2 hari'"
                        class="mt-1.5 w-full rounded-lg border-line text-sm"></textarea>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-muted">Ubah status</label>
                        <select id="status" name="status" class="mt-1.5 w-full rounded-lg border-line text-sm min-h-[44px]">
                            <option value="">{{ $lead->status === 'Lunas' ? 'Lunas (terkunci)' : 'Jangan ubah status' }}</option>
                            @foreach (['Baru', 'Dihubungi', 'TestDrive', 'SPK', 'Deal', 'Lunas', 'Batal'] as $s)
                                @continue($lead->status === 'Lunas' && $s === 'Lunas')
                                <option value="{{ $s }}" @disabled($lead->status === 'Lunas')>{{ $s }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="next_reminder" class="block text-xs font-semibold uppercase tracking-wider text-muted">Ingatkan kembali</label>
                        <input id="next_reminder" type="datetime-local" name="next_reminder" class="mt-1.5 w-full rounded-lg border-line text-sm min-h-[44px]">
                    </div>
                    <div class="flex items-end">
                        <button class="w-full px-4 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">Simpan Follow-up</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Riwayat follow-up -->
        <div class="mt-5 bg-surface rounded-2xl border border-line shadow-sm p-6">
            <h3 class="font-bold text-ink">Riwayat Follow-up</h3>
            @forelse ($lead->followups as $f)
                <div class="mt-4 flex gap-3">
                    <div class="flex flex-col items-center">
                        <span class="w-2.5 h-2.5 rounded-full bg-accent mt-1.5"></span>
                        <span class="w-px flex-1 bg-line"></span>
                    </div>
                    <div class="pb-4 flex-1">
                        <p class="text-sm text-ink leading-relaxed">{{ $f->catatan }}</p>
                        <p class="text-xs text-muted mt-1 tabular-nums">
                            {{ $f->created_at->format('d M Y H:i') }}
                            @if ($f->sales) · oleh {{ $f->sales->name }}@endif
                            @if ($f->next_reminder) · ingatkan lagi {{ $f->next_reminder->format('d M Y H:i') }}@endif
                        </p>
                    </div>
                </div>
            @empty
                <p class="mt-3 text-sm text-muted">Belum ada follow-up. Catat hasil menghubungi customer di atas.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
