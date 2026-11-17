<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center gap-3">
            <h2 class="font-semibold text-xl text-ink leading-tight font-mono">{{ $service->no_svc }}</h2>
            <x-status-badge :status="$service->status" />
        </div>
    </x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <a href="{{ route('admin.services.index') }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center min-h-[36px]">← Kembali ke antrean servis</a>

        @if (session('sukses'))
            <p class="mt-3 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif
        @if ($errors->any())
            <div class="mt-3 p-3 text-sm bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                @foreach ($errors->all() as $galat)<p>{{ $galat }}</p>@endforeach
            </div>
        @endif

        @php
            $pengguna = auth()->user();
            $kelola = $pengguna->hasAnyRole(['service-advisor', 'admin-cabang', 'pimpinan', 'superadmin', 'owner']);
            $tugasSaya = $pengguna->hasRole('mekanik') && ($service->mekanik_id === $pengguna->id || $service->mekanik_id === null);
            $bolehRincian = $tugasSaya || $pengguna->hasAnyRole(['service-advisor', 'admin-cabang', 'pimpinan', 'superadmin', 'owner']);
        @endphp

        <div class="mt-3 grid grid-cols-1 lg:grid-cols-3 gap-5 items-start">
            <div class="lg:col-span-2 space-y-5">
            <!-- Info booking -->
            <div class="bg-surface rounded-2xl border border-line shadow-sm p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 pb-4 border-b border-line">
                    <div>
                        <p class="text-xs font-bold uppercase tracking-wider text-muted">Customer</p>
                        <p class="mt-1 text-lg font-bold text-ink">{{ $service->customer->nama }}</p>
                        <p class="text-sm text-muted tabular-nums">{{ $service->customer->telp }}</p>
                    </div>
                    <div class="flex flex-wrap gap-2">
                        @if ($service->customer?->telp)
                            <a href="{{ \App\Support\Wa::chat($service->customer->telp, \App\Support\Notifikasi::servisChat($service)) }}"
                                target="_blank" rel="noopener"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-xl transition min-h-[44px]">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                Chat Pembeli
                            </a>
                        @endif
                        @if ($service->status === 'Selesai')
                            <a href="{{ route('servis.invoice', $service) }}"
                                class="inline-flex items-center gap-2 px-4 py-2.5 text-sm font-bold text-ink bg-surface-2 hover:bg-line rounded-xl transition min-h-[44px]">
                                Unduh Invoice ↓
                            </a>
                        @endif
                    </div>
                </div>

                <dl class="mt-4 grid grid-cols-1 sm:grid-cols-3 gap-4 text-sm">
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted">Kendaraan</dt>
                        <dd class="mt-1 text-ink font-mono font-semibold">{{ $service->nopol }}</dd>
                        <dd class="text-muted tabular-nums">{{ number_format($service->km, 0, ',', '.') }} km</dd>
                        <dd class="text-muted font-mono text-xs">VIN {{ $service->vin ?? '-' }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted">Jadwal</dt>
                        <dd class="mt-1 text-ink font-semibold tabular-nums">{{ $service->jadwal->format('d M Y H:i') }}</dd>
                        <dd class="text-muted">{{ $service->branch->nama }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-semibold uppercase tracking-wider text-muted">Pengerjaan</dt>
                        <dd class="mt-1 text-ink font-medium">{{ $service->mekanik->name ?? 'Belum ditugaskan' }}</dd>
                        <dd class="text-muted tabular-nums">Bayar: {{ $service->metode_bayar ?? 'belum dipilih' }}</dd>
                        <dd class="text-muted tabular-nums">Estimasi: Rp {{ number_format($service->estimasi_biaya, 0, ',', '.') }}</dd>
                    </div>
                </dl>

                <div class="mt-5">
                    <p class="text-xs font-bold uppercase tracking-wider text-muted mb-2">Keluhan Customer</p>
                    <p class="text-sm text-ink leading-relaxed bg-surface-2 border border-line rounded-xl p-4">{{ $service->keluhan ?? '-' }}</p>
                </div>
            </div>

            <!-- Rincian pekerjaan & sparepart -->
            <select class="sr-only" tabindex="-1" aria-hidden="true">
                @foreach ($parts as $part)
                    <option value="{{ $part->id }}" data-harga="{{ $part->harga }}">{{ $part->nama }}</option>
                @endforeach
            </select>

            @if ($kelola || $service->details->isNotEmpty())
                <div class="bg-surface rounded-2xl border border-line shadow-sm p-6 {{ $kelola ? 'min-h-[420px] flex flex-col' : '' }}">
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <h3 class="font-bold text-ink">Rincian Pekerjaan &amp; Sparepart</h3>
                    </div>

                    @if ($service->details->isEmpty())
                        <p class="mt-3 text-sm text-muted">Belum ada rincian. Tambahkan pekerjaan atau sparepart di bawah.</p>
                    @else
                        <div class="mt-3 overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead>
                                    <tr class="border-b border-line">
                                        <th class="text-left py-2 font-semibold text-muted">Jenis</th>
                                        <th class="text-left py-2 font-semibold text-muted">Deskripsi</th>
                                        <th class="text-right py-2 font-semibold text-muted">Qty</th>
                                        @if ($kelola && ! in_array($service->status, ['Selesai', 'Batal']))
                                            <th class="text-right py-2 font-semibold text-muted">Aksi</th>
                                        @endif
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($service->details as $detail)
                                        <tr class="border-b border-line last:border-0">
                                            <td class="py-2.5 pe-4">
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[11px] font-semibold {{ $detail->jenis === 'Sparepart' ? 'bg-blue-50 dark:bg-blue-500/10 text-blue-700 dark:text-blue-300' : 'bg-surface-2 text-muted' }} ring-1 ring-line">{{ $detail->jenis }}</span>
                                            </td>
                                            <td class="py-2.5 pe-4 text-ink">{{ $detail->deskripsi }}</td>
                                            <td class="py-2.5 text-right tabular-nums text-muted">× {{ $detail->qty }}</td>
                                            @if ($kelola && ! in_array($service->status, ['Selesai', 'Batal']))
                                                <td class="py-2.5 ps-3 text-right">
                                                    <form action="{{ route('admin.services.detail.destroy', [$service, $detail]) }}" method="POST" class="inline">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="text-xs font-semibold text-red-600 hover:underline">Hapus</button>
                                                    </form>
                                                </td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif

                    @if ($kelola)
                        @php
                            $partsJson = $parts->map(fn ($p) => [
                                'id' => (string) $p->id,
                                'nama' => $p->nama,
                                'kode' => $p->kode,
                                'cabang' => $p->branch->nama ?? '-',
                                'stok' => (int) $p->stok,
                                'label' => $p->nama.' (stok '.$p->stok.')',
                            ])->values();
                        @endphp
                        <form action="{{ route('admin.services.detail', $service) }}" method="POST"
                              x-data="{
                                  jenis: 'Sparepart',
                                  parts: @js($partsJson),
                                  query: '',
                                  selectedId: '',
                                  open: false,
                                  get filtered() {
                                      const q = this.query.trim().toLowerCase();
                                      if (!q) return this.parts.slice(0, 20);
                                      return this.parts.filter(p =>
                                          p.nama.toLowerCase().includes(q) ||
                                          (p.kode && p.kode.toLowerCase().includes(q))
                                      ).slice(0, 20);
                                  },
                                  pilih(p) {
                                      this.selectedId = p.id;
                                      this.query = p.label;
                                      this.jenis = 'Sparepart';
                                      this.open = false;
                                  }
                              }"
                              class="mt-5 pt-5 border-t border-line flex-1 flex flex-col gap-4">
                            @csrf
                            <input type="hidden" name="jenis" :value="jenis">
                            <input type="hidden" name="sparepart_id" :value="selectedId">
                            <div class="flex flex-wrap items-center justify-between gap-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-muted">Tambah Rincian</span>
                                <div class="inline-flex p-1 rounded-xl bg-surface-2 border border-line">
                                    <button type="button" @click="jenis = 'Sparepart'"
                                            :class="jenis === 'Sparepart' ? 'bg-surface text-ink shadow-xs font-semibold' : 'text-muted hover:text-ink'"
                                            class="px-3.5 py-1.5 rounded-lg text-xs transition min-h-[32px]">
                                        Sparepart
                                    </button>
                                    <button type="button" @click="jenis = 'Pekerjaan'"
                                            :class="jenis === 'Pekerjaan' ? 'bg-surface text-ink shadow-xs font-semibold' : 'text-muted hover:text-ink'"
                                            class="px-3.5 py-1.5 rounded-lg text-xs transition min-h-[32px]">
                                        Pekerjaan / Jasa
                                    </button>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 sm:grid-cols-12 gap-3 items-start">
                                <div class="sm:col-span-9 relative" x-show="jenis === 'Sparepart'" @click.outside="open = false">
                                    <label for="detail-sparepart-search" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Cari Sparepart</label>
                                    <div class="relative">
                                        <input id="detail-sparepart-search" type="text" x-model="query"
                                               @focus="open = true"
                                               @input="open = true; if (!query) selectedId = ''"
                                               @keydown.escape="open = false"
                                               autocomplete="off"
                                               placeholder="Ketik nama atau kode sparepart..."
                                               class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[44px] pl-3 pr-9">
                                        <button type="button" x-show="query || selectedId" x-cloak
                                                @click="query = ''; selectedId = ''; open = true"
                                                aria-label="Hapus pilihan sparepart"
                                                class="absolute right-2 top-1/2 -translate-y-1/2 p-1 text-muted hover:text-ink">
                                            &times;
                                        </button>
                                    </div>
                                    <div x-show="open" x-cloak
                                         class="absolute left-0 right-0 top-full mt-2 z-40 bg-surface border border-line rounded-xl shadow-md max-h-44 overflow-y-auto divide-y divide-line">
                                        <template x-for="p in filtered" :key="p.id">
                                            <button type="button" @click="pilih(p)"
                                                    class="w-full text-left px-3 py-2.5 hover:bg-surface-2 transition-colors flex items-center justify-between gap-2 text-xs">
                                                <span class="font-semibold text-ink truncate" x-text="p.nama"></span>
                                                <span class="text-muted shrink-0" x-text="(p.kode ? p.kode + ' · ' : '') + 'stok ' + p.stok"></span>
                                            </button>
                                        </template>
                                        <div x-show="filtered.length === 0" class="px-3 py-3 text-xs text-muted text-center">
                                            Sparepart tidak ditemukan.
                                        </div>
                                    </div>
                                </div>

                                <div class="sm:col-span-9" x-show="jenis === 'Pekerjaan'" x-cloak>
                                    <label for="detail-deskripsi" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Deskripsi Pekerjaan</label>
                                    <input id="detail-deskripsi" name="deskripsi" placeholder="Contoh: Jasa ganti oli & cek rem" class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[44px] px-3">
                                </div>

                                <div class="sm:col-span-3">
                                    <label for="detail-qty" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Qty</label>
                                    <input id="detail-qty" type="number" name="qty" value="1" min="1" class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[44px] px-3">
                                </div>
                            </div>

                            <div class="mt-auto pt-4 flex justify-end">
                                <button class="px-5 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">+ Tambah Rincian</button>
                            </div>
                        </form>
                    @endif
                </div>
            @endif
            </div>

            <!-- Panel status & tanggapan -->
            <div class="space-y-5">
                @if ($kelola || $tugasSaya)
                    <div class="bg-surface rounded-2xl border border-line shadow-sm p-6">
                        <h3 class="font-bold text-ink">Perbarui Status</h3>
                        <form action="{{ route('admin.services.update', $service) }}" method="POST" class="mt-4 space-y-3">
                            @csrf @method('PATCH')
                            <div>
                                <label for="status" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Status pengerjaan</label>
                                <select id="status" name="status" class="w-full rounded-lg border-line text-sm min-h-[44px]">
                                    @foreach ($kelola ? ['Dikonfirmasi', 'Dikerjakan', 'Selesai', 'Batal'] : ['Dikerjakan', 'Selesai'] as $s)
                                        <option value="{{ $s }}" @selected($service->status === $s)>{{ $s }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if ($kelola)
                                <div>
                                    <label for="metode_bayar" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Metode bayar</label>
                                    <select id="metode_bayar" name="metode_bayar" class="w-full rounded-lg border-line text-sm min-h-[44px]">
                                        <option value="">Pilih pembayaran</option>
                                        @foreach (['Cash', 'Transfer'] as $bayar)
                                            <option value="{{ $bayar }}" @selected($service->metode_bayar === $bayar)>{{ $bayar }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label for="estimasi-input" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Estimasi biaya (Rp)</label>
                                    <input id="estimasi-input" type="number" name="estimasi_biaya" min="0" value="{{ $service->estimasi_biaya }}" placeholder="Estimasi Rp" data-total="{{ $service->total() }}" class="w-full rounded-lg border-line text-sm min-h-[44px]">
                                    <button type="button" id="estimasi-sync" class="mt-2 w-full px-4 py-2 text-xs font-semibold text-ink bg-surface-2 hover:bg-line border border-line rounded-lg min-h-[36px]">Samakan dengan total rincian</button>
                                </div>
                                <div>
                                    <label for="mekanik_id" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Tugaskan mekanik</label>
                                    <select id="mekanik_id" name="mekanik_id" class="w-full rounded-lg border-line text-sm min-h-[44px]">
                                        <option value="">Tanpa mekanik</option>
                                        @foreach ($mekaniks as $mekanik)
                                            <option value="{{ $mekanik->id }}" @selected($service->mekanik_id === $mekanik->id)>{{ $mekanik->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                            <button class="w-full px-4 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">Simpan</button>
                        </form>
                    </div>
                @endif

                <div class="bg-surface rounded-2xl border border-line shadow-sm p-6">
                    <h3 class="font-bold text-ink">Tanggapan Bengkel</h3>
                    @if ($service->tanggapan)
                        <p class="mt-2 text-sm text-ink leading-relaxed">{{ $service->tanggapan }}</p>
                    @else
                        <p class="mt-2 text-sm text-muted">Belum ada tanggapan untuk keluhan customer.</p>
                    @endif
                    @if ($kelola || $tugasSaya)
                        <form action="{{ route('admin.services.tanggapi', $service) }}" method="POST" class="mt-4 space-y-3">
                            @csrf @method('PATCH')
                            <textarea name="tanggapan" required rows="3" placeholder="Tulis jawaban atas keluhan customer, otomatis terkirim via email"
                                class="w-full rounded-lg border-line text-sm">{{ old('tanggapan', $service->tanggapan) }}</textarea>
                            <button class="w-full px-4 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">Kirim Tanggapan</button>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        @if ($bolehRincian)
            <script>
                (function () {
                    const estimasi = document.getElementById('estimasi-input');
                    const sinkron = document.getElementById('estimasi-sync');
                    if (estimasi && sinkron) {
                        if ((!estimasi.value || Number(estimasi.value) === 0) && Number(estimasi.dataset.total) > 0) {
                            estimasi.value = estimasi.dataset.total;
                        }
                        sinkron.addEventListener('click', () => {
                            estimasi.value = estimasi.dataset.total;
                        });
                    }
                })();
            </script>
        @endif
    </div>
</x-app-layout>
