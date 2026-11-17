<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Model Mobil</h2></x-slot>
    <div class="py-6 w-full sm:px-6 lg:px-8"
         x-data="{ previewSrc: null, previewTitle: '' }"
         @keydown.escape.window="previewSrc = null">
        @if (session('sukses'))<p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>@endif
        <a href="{{ route('admin.models.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-accent-strong rounded-lg min-h-[44px]">Tambah Model</a>
        <div class="mt-4 bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line bg-surface-2/60 text-left text-xs uppercase tracking-wider text-muted">
                        <th class="px-4 py-3">Preview</th>
                        <th class="px-4 py-3">Brand</th>
                        <th class="px-4 py-3">Model</th>
                        <th class="px-4 py-3">Varian</th>
                        <th class="px-4 py-3">Warna Kendaraan</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($models as $model)
                        @php
                            $units = $model->variants->flatMap->units;
                            $foto = $units->where('status', 'Tersedia')->first()?->foto_utama
                                ?? $units->first()?->foto_utama;
                            $warnaUnik = $units->pluck('color')->filter()->unique('id')->values();
                        @endphp
                        <tr class="hover:bg-surface-2/40 transition-colors">
                            <td class="px-4 py-2.5">
                                @if ($foto)
                                    <button type="button"
                                            @click="previewSrc = @js(asset($foto)); previewTitle = @js($model->brand->nama . ' ' . $model->nama)"
                                            title="Klik untuk perbesar foto"
                                            class="group relative block rounded-lg overflow-hidden border border-line focus:outline-none focus:ring-2 focus:ring-accent cursor-zoom-in">
                                        <img src="{{ asset($foto) }}" alt="{{ $model->brand->nama }} {{ $model->nama }}" loading="lazy"
                                             class="w-20 h-12 object-cover bg-surface-2 transition-transform duration-150 group-hover:scale-105">
                                    </button>
                                @else
                                    <span class="w-20 h-12 rounded-lg bg-surface-2 border border-line text-[10px] text-muted inline-flex items-center justify-center">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-muted">{{ $model->brand->nama }}</td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $model->nama }}</td>
                            <td class="px-4 py-3">
                                @if ($model->variants->isNotEmpty())
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach ($model->variants as $variant)
                                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-xs border {{ $variant->aktif ? 'bg-surface-2 border-line text-ink' : 'bg-surface border-line text-muted line-through' }}">
                                                <span>{{ $variant->nama }}</span>
                                                <span class="text-[10px] text-muted">{{ $variant->transmisi }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if ($warnaUnik->isNotEmpty())
                                    <div class="flex flex-wrap items-center gap-1.5">
                                        @foreach ($warnaUnik as $warna)
                                            <span class="inline-flex items-center gap-1.5 px-2 py-0.5 rounded-full text-xs bg-surface-2 border border-line text-ink">
                                                <span class="w-2.5 h-2.5 rounded-full border border-black/15 shrink-0" style="background: {{ $warna->kode_hex ?: '#ccc' }}"></span>
                                                <span>{{ $warna->nama }}</span>
                                            </span>
                                        @endforeach
                                    </div>
                                @else
                                    <span class="text-xs text-muted">-</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.models.edit', $model) }}" class="underline font-medium text-accent min-h-[44px] inline-flex items-center">Ubah</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-6 text-muted text-center">Belum ada model.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $models->links() }}</div>

        <div x-show="previewSrc" x-cloak
             @click.self="previewSrc = null"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-xs">
            <div class="relative max-w-3xl w-full bg-surface border border-line rounded-2xl overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between px-4 py-3 border-b border-line">
                    <h3 class="text-sm font-semibold text-ink" x-text="previewTitle"></h3>
                    <button type="button" @click="previewSrc = null"
                            class="px-2.5 py-1 text-xs font-medium rounded-lg border border-line text-muted hover:text-ink hover:bg-surface-2">
                        Tutup
                    </button>
                </div>
                <div class="p-3 bg-surface-2 flex items-center justify-center">
                    <img :src="previewSrc" :alt="previewTitle" class="max-h-[75vh] w-auto object-contain rounded-xl">
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
