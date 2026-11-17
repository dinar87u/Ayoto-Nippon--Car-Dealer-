<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Verifikasi SPK</h2></x-slot>

    @php
        $ekstensiGambar = ['jpg', 'jpeg', 'png', 'webp'];
    @endphp

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <p class="mb-4 text-sm text-muted max-w-3xl">
            Cocokkan bukti transfer dengan nominal booking fee & rekening cabang tujuan, lalu pilih keputusan.
            <strong class="text-ink">Terima</strong> membuat SPK jadi Deal (invoice PDF otomatis), <strong class="text-ink">Tolak</strong> memberi batas waktu baru atau membatalkan.
        </p>

        @if (session('sukses'))
            <p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>
        @endif
        @if ($errors->any())
            <div class="mb-4 p-3 text-sm bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/30 rounded-lg">
                @foreach ($errors->all() as $galat)<p>{{ $galat }}</p>@endforeach
            </div>
        @endif

        <!-- Antrean terpadu: satu pipeline, dua tahap -->
        <div class="mb-6 bg-surface rounded-2xl border border-line shadow-sm overflow-hidden" x-data="{ tab: '{{ $orders->total() > 0 ? 'verif' : 'bukti' }}' }">
            <div class="px-4 py-3 border-b border-line bg-surface-2/60 flex flex-wrap items-center gap-2">
                <h3 class="font-semibold text-ink mr-2 px-1">Antrean</h3>
                <button type="button" @click="tab = 'verif'"
                    :class="tab === 'verif' ? 'bg-accent-strong text-white shadow-sm' : 'text-muted hover:text-ink'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] inline-flex items-center gap-2">
                    Perlu Keputusan
                    <span class="px-1.5 py-0.5 rounded-md" :class="tab === 'verif' ? 'bg-white/20' : 'bg-surface-2 ring-1 ring-line'">{{ $orders->total() }}</span>
                </button>
                <button type="button" @click="tab = 'bukti'"
                    :class="tab === 'bukti' ? 'bg-amber-500 text-white shadow-sm' : 'text-muted hover:text-ink'"
                    class="px-3.5 py-2 rounded-xl text-xs font-bold transition min-h-[36px] inline-flex items-center gap-2">
                    Menunggu Bukti Bayar
                    <span class="px-1.5 py-0.5 rounded-md" :class="tab === 'bukti' ? 'bg-white/20' : 'bg-surface-2 ring-1 ring-line'">{{ $menungguBayar->count() }}</span>
                </button>
            </div>

            {{-- Panel 1: bukti sudah di-upload, butuh keputusan admin --}}
            <div x-show="tab === 'verif'" x-cloak>
                @if ($orders->isEmpty())
                    <div class="p-6">
                        <p class="font-semibold text-ink">Tidak ada SPK untuk diputuskan.</p>
                        <p class="text-sm text-muted mt-1">Tab ini hanya berisi SPK yang sudah meng-upload bukti bayar. SPK yang belum bayar ada di tab <strong>Menunggu Bukti Bayar</strong>.</p>
                    </div>
                @else
                    <div class="p-5 space-y-4">

                @foreach ($orders as $order)
                    @php
                        $lewat = $order->deadline_at->isPast();
                        $sisaJam = abs(ceil(now()->diffInHours($order->deadline_at)));
                        $rekening = $order->branch->bankAccounts->where('aktif', true);
                        $buktiGambar = $order->bukti_path && in_array(pathinfo($order->bukti_path, PATHINFO_EXTENSION), $ekstensiGambar);
                    @endphp

                    <div class="bg-surface rounded-2xl border border-line shadow-sm overflow-hidden">
                        <!-- Kepala kartu -->
                        <div class="flex flex-wrap items-center gap-2.5 px-5 py-3.5 border-b border-line bg-surface-2/60">
                            <span class="font-mono font-bold text-ink">{{ $order->no_spk }}</span>
                            <x-status-badge status="MenungguVerifikasi" />
                            <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $lewat ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-500/30' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30' }}">
                                {{ $lewat ? "Lewat batas {$sisaJam} jam" : "Batas bayar: sisa {$sisaJam} jam" }}
                                <span class="font-normal opacity-80">({{ $order->deadline_at->format('d M H:i') }})</span>
                            </span>
                            <span class="ml-auto text-xs text-muted">Diajukan {{ $order->created_at->format('d M Y H:i') }}</span>
                        </div>

                        <div class="p-5 grid grid-cols-1 lg:grid-cols-3 gap-6">
                            <!-- Pembeli & dokumen -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Pembeli & Dokumen</h4>
                                <p class="font-bold text-ink">{{ $order->customer->nama }}</p>
                                <p class="text-sm text-muted">{{ $order->customer->telp }}</p>
                                @if ($order->customer->nik)
                                    <p class="text-xs text-muted mt-1">NIK <span class="font-mono">{{ $order->customer->nik }}</span></p>
                                @endif
                                <a href="{{ \App\Support\Wa::chat($order->customer->telp, 'Halo, saya dari Ayoto Nippon mengenai SPK ' . $order->no_spk . '.') }}"
                                    target="_blank" rel="noopener"
                                    class="mt-3 inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[36px]">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                    Chat Pembeli
                                </a>
                                <div class="mt-3 flex flex-wrap gap-2">
                                    @if ($order->customer->ktp_path)
                                        <a href="{{ route('admin.verify.file', [$order, 'ktp']) }}" target="_blank" rel="noopener"
                                            class="px-3 py-1.5 text-xs font-semibold text-ink bg-surface-2 ring-1 ring-line rounded-lg hover:border-accent transition min-h-[32px] inline-flex items-center">KTP ↗</a>
                                    @else
                                        <span class="px-3 py-1.5 text-xs text-muted bg-surface-2 rounded-lg">KTP tidak ada</span>
                                    @endif
                                    @if ($order->customer->kk_path)
                                        <a href="{{ route('admin.verify.file', [$order, 'kk']) }}" target="_blank" rel="noopener"
                                            class="px-3 py-1.5 text-xs font-semibold text-ink bg-surface-2 ring-1 ring-line rounded-lg hover:border-accent transition min-h-[32px] inline-flex items-center">KK ↗</a>
                                    @endif
                                </div>
                            </div>

                            <!-- Unit & pembayaran -->
                            <div>
                                <h4 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Unit & Bukti Bayar</h4>
                                <p class="font-bold text-ink leading-snug">{{ $order->unit->variant->carModel->brand->nama }} {{ $order->unit->variant->carModel->nama }} {{ $order->unit->variant->nama }}</p>
                                <p class="text-xs text-muted mt-0.5">VIN <span class="font-mono">{{ $order->unit->vin }}</span> · Cabang {{ $order->branch->nama }}</p>
                                <p class="text-xs text-muted mt-2">
                                    Metode <strong class="text-ink">{{ $order->metode }}</strong>
                                    @if ($order->metode === 'Kredit' && $order->leasing)
                                        · {{ $order->leasing->nama }} · DP {{ 'Rp ' . number_format($order->dp_rencana, 0, ',', '.') }} · tenor {{ $order->tenor }} bln
                                    @endif
                                </p>
                                <div class="mt-3 p-3 rounded-xl bg-surface-2 border border-line text-sm">
                                    <p class="text-xs text-muted">Nominal yang harus dibayar</p>
                                    <p class="font-extrabold text-ink tabular-nums">Rp {{ number_format($order->booking_fee, 0, ',', '.') }}</p>
                                    @foreach ($rekening as $rek)
                                        <p class="text-xs text-muted mt-1">{{ $rek->bank }} <span class="font-mono font-semibold text-ink">{{ $rek->no_rek }}</span> a.n. {{ $rek->atas_nama }}</p>
                                    @endforeach
                                    @if ($rekening->isEmpty())
                                        <p class="text-xs text-amber-700 dark:text-amber-300 mt-1 font-semibold">⚠ Rekening cabang belum tersedia</p>
                                    @endif
                                </div>
                                @if ($order->bukti_path)
                                    <div class="mt-3">
                                        <a href="{{ route('admin.verify.file', [$order, 'bukti']) }}" target="_blank" rel="noopener" class="group relative block">
                                            @if ($buktiGambar)
                                                <img src="{{ route('admin.verify.file', [$order, 'bukti']) }}" alt="Bukti bayar SPK {{ $order->no_spk }}"
                                                    class="w-full max-w-52 h-36 object-cover rounded-xl ring-1 ring-line group-hover:ring-accent transition">
                                                <span class="absolute bottom-2 left-2 px-2 py-0.5 text-[10px] font-bold bg-black/60 text-white rounded">Bukti Transfer · klik utk perbesar</span>
                                            @else
                                                <span class="px-3 py-1.5 text-xs font-semibold text-ink bg-surface-2 ring-1 ring-line rounded-lg inline-flex items-center min-h-[32px]">Bukti bayar (PDF) ↗</span>
                                            @endif
                                        </a>
                                    </div>
                                @else
                                    <p class="mt-3 text-xs text-red-700 dark:text-red-300 font-semibold">⚠ Tanpa bukti bayar</p>
                                @endif
                            </div>

                            <!-- Keputusan -->
                            <div x-data="{ keputusan: '', loading: false }" class="bg-surface-2 border border-line rounded-2xl p-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-muted mb-3">Keputusan</h4>
                                <form action="{{ route('admin.verify.update', $order) }}" method="POST"
                                      @submit="if (loading) { $event.preventDefault(); return; } loading = true"
                                      class="space-y-3">
                                    @csrf @method('PATCH')

                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-line bg-surface cursor-pointer has-[:checked]:border-emerald-500 has-[:checked]:ring-1 has-[:checked]:ring-emerald-500 transition">
                                        <input type="radio" name="keputusan" value="terima" @change="keputusan = 'terima'" class="mt-1 min-w-[18px] min-h-[18px]">
                                        <span>
                                            <strong class="text-sm text-ink">Terima — jadikan Deal</strong>
                                            <span class="block text-xs text-muted mt-0.5">Unit jadi Terjual, invoice & PDF dibuat, pembeli di-WA otomatis.</span>
                                        </span>
                                    </label>

                                    <label class="flex items-start gap-3 p-3 rounded-xl border border-line bg-surface cursor-pointer has-[:checked]:border-red-400 has-[:checked]:ring-1 has-[:checked]:ring-red-400 transition">
                                        <input type="radio" name="keputusan" value="tolak" @change="keputusan = 'tolak'" class="mt-1 min-w-[18px] min-h-[18px]">
                                        <span>
                                            <strong class="text-sm text-ink">Tolak bukti ini</strong>
                                            <span class="block text-xs text-muted mt-0.5">Beri batas waktu baru (bukti lama dihapus) atau batalkan SPK.</span>
                                        </span>
                                    </label>

                                    <div x-show="keputusan === 'tolak'" x-cloak class="space-y-2.5 pl-1">
                                        <select name="tolak_aksi" :disabled="keputusan !== 'tolak'" class="w-full rounded-lg border-line text-sm min-h-[44px]">
                                            <option value="perpanjang">Beri batas waktu baru (+1 hari)</option>
                                            <option value="batal">Batalkan SPK (unit dilepas)</option>
                                        </select>
                                        <textarea name="tolak_alasan" rows="2" :required="keputusan === 'tolak'" :disabled="keputusan !== 'tolak'"
                                            placeholder="Wajib: alasan penolakan, mis. 'nominal tidak sesuai'"
                                            class="w-full rounded-lg border-line text-sm placeholder-gray-500"></textarea>
                                    </div>

                                    <button type="submit" :disabled="!keputusan || loading"
                                        :class="keputusan === 'tolak' ? 'bg-red-700 hover:bg-red-800' : 'bg-emerald-700 hover:bg-emerald-800'"
                                        class="w-full px-4 py-2.5 text-sm font-bold text-white rounded-xl min-h-[44px] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                        <span x-text="loading ? 'Memproses...' : (keputusan === 'tolak' ? 'Simpan Penolakan' : (keputusan === 'terima' ? 'Setujui & Jadikan Deal' : 'Pilih keputusan dulu'))"></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
                    </div>

                    <div class="px-5 pb-5">{{ $orders->links() }}</div>
                @endif
            </div>

            {{-- Panel 2: SPK terbit tapi belum ada bukti bayar --}}
            <div x-show="tab === 'bukti'" x-cloak>
                @if ($menungguBayar->isEmpty())
                    <div class="p-6">
                        <p class="font-semibold text-ink">Tidak ada SPK yang menunggu bukti bayar.</p>
                        <p class="text-sm text-muted mt-1">Semua SPK yang terbit sudah meng-upload bukti dan masuk tab <strong>Perlu Keputusan</strong>.</p>
                    </div>
                @else
                    <div class="p-5">
                        <p class="text-xs text-muted mb-3">SPK ini sudah diterbitkan tapi belum ada bukti bayar, jadi belum bisa diputuskan. Kirim pengingat — akan pindah sendiri ke tab <strong>Perlu Keputusan</strong> setelah pembeli meng-upload bukti.</p>
                        <div class="overflow-x-auto ring-1 ring-line rounded-xl">
<table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-line">
                                <th class="text-left px-4 py-3 font-semibold text-ink">No. SPK</th>
                                <th class="text-left px-4 py-3 font-semibold text-ink">Pembeli</th>
                                <th class="text-left px-4 py-3 font-semibold text-ink">Unit</th>
                                <th class="text-left px-4 py-3 font-semibold text-ink">Tanda Jadi</th>
                                <th class="text-left px-4 py-3 font-semibold text-ink">Batas Bayar</th>
                                <th class="text-right px-4 py-3 font-semibold text-ink">Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($menungguBayar as $belum)
                                @php
                                    $lewatBatas = $belum->deadline_at->isPast();
                                    $sisaJam = abs(ceil(now()->diffInHours($belum->deadline_at)));
                                @endphp
                                <tr class="border-b border-line last:border-0 align-top">
                                    <td class="px-4 py-3 font-mono">{{ $belum->no_spk }}</td>
                                    <td class="px-4 py-3">{{ $belum->customer->nama }}<br><span class="text-muted text-xs">{{ $belum->customer->telp }}</span></td>
                                    <td class="px-4 py-3">{{ $belum->unit->variant->carModel->brand->nama }} {{ $belum->unit->variant->nama }}<br><span class="font-mono text-muted text-xs">{{ $belum->unit->vin }}</span></td>
                                    <td class="px-4 py-3 tabular-nums font-semibold">Rp {{ number_format($belum->booking_fee, 0, ',', '.') }}</td>
                                    <td class="px-4 py-3">
                                        <span class="inline-flex items-center text-xs font-semibold px-2.5 py-1 rounded-full {{ $lewatBatas ? 'bg-red-50 dark:bg-red-500/10 text-red-700 dark:text-red-300 ring-1 ring-red-200 dark:ring-red-500/30' : 'bg-amber-50 dark:bg-amber-500/10 text-amber-700 dark:text-amber-300 ring-1 ring-amber-200 dark:ring-amber-500/30' }}">
                                            {{ $lewatBatas ? "Lewat {$sisaJam} jam" : "sisa {$sisaJam} jam" }}
                                        </span>
                                        <span class="block text-muted text-xs mt-1">{{ $belum->deadline_at->format('d M Y H:i') }}</span>
                                    </td>
                                    <td class="px-4 py-3 text-right">
                                        <a href="{{ \App\Support\Wa::chat($belum->customer->telp, \App\Support\Notifikasi::reminderBayar($belum)) }}"
                                            target="_blank" rel="noopener"
                                            class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[36px]">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                            Kirim Pengingat
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Riwayat -->
        <div class="mt-8 mb-3 flex items-center gap-2">
            <h3 class="font-semibold text-ink">Riwayat Verifikasi</h3>
            <span class="text-xs text-muted">10 keputusan terakhir</span>
        </div>
        @if ($riwayat->isEmpty())
            <div class="bg-surface p-6 rounded-xl shadow-sm border border-line">
                <p class="font-semibold text-ink">Belum ada riwayat.</p>
                <p class="text-sm text-muted mt-1">SPK yang sudah diverifikasi akan tampil di sini.</p>
            </div>
        @else
            <div class="bg-surface rounded-xl shadow-sm border border-line overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line">
                            <th class="text-left px-4 py-3 font-semibold text-ink">No. SPK</th>
                            <th class="text-left px-4 py-3 font-semibold text-ink">Pembeli</th>
                            <th class="text-left px-4 py-3 font-semibold text-ink">Unit</th>
                            <th class="text-left px-4 py-3 font-semibold text-ink">Keputusan</th>
                            <th class="text-left px-4 py-3 font-semibold text-ink">Diverifikasi</th>
                            <th class="text-left px-4 py-3 font-semibold text-ink">Tindak Lanjut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($riwayat as $selesai)
                            <tr class="border-b border-line last:border-0 align-top">
                                <td class="px-4 py-3 font-mono">{{ $selesai->no_spk }}</td>
                                <td class="px-4 py-3">{{ $selesai->customer->nama }}<br><span class="text-muted text-xs">{{ $selesai->customer->telp }}</span></td>
                                <td class="px-4 py-3">{{ $selesai->unit->variant->carModel->nama }} {{ $selesai->unit->variant->nama }}<br><span class="font-mono text-muted text-xs">{{ $selesai->unit->vin }}</span></td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-col items-start gap-1.5">
                                        <x-status-badge status="{{ $selesai->status }}" />
                                        @if ($selesai->tolak_alasan)
                                            <p class="text-xs text-muted max-w-48">{{ $selesai->tolak_alasan }}</p>
                                        @endif
                                        @if ($selesai->status === 'Deal' && $selesai->invoice)
                                            <a href="{{ route('admin.invoices.download', $selesai->invoice) }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center gap-1">Invoice {{ $selesai->invoice->no_inv }} ↓</a>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-muted text-xs">{{ $selesai->verifier->name ?? '-' }}<br>{{ $selesai->verified_at?->format('d M Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <a href="{{ \App\Support\Wa::chat($selesai->customer->telp, $selesai->status === 'Deal' ? \App\Support\Notifikasi::spkDealKonfirmasi($selesai) : \App\Support\Notifikasi::spkTolakKonfirmasi($selesai)) }}"
                                        target="_blank" rel="noopener"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-bold text-white bg-[#25D366] hover:bg-[#1FB857] rounded-lg transition min-h-[36px]">
                                        <svg class="w-4 h-4 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/></svg>
                                        Chat Pembeli
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
