<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between gap-4">
            <h2 class="font-semibold text-xl text-ink leading-tight">{{ $model->exists ? 'Ubah Model' : 'Tambah Model' }}</h2>
            <a href="{{ route('admin.models.index') }}" class="text-sm font-medium text-muted hover:text-ink underline">Kembali ke Model</a>
        </div>
    </x-slot>
    @php
        $colorMap = collect($colors ?? [])->mapWithKeys(fn ($c) => [$c->id => ($c->kode_hex ?: '#ccc')]);
        $modelUnits = $model->exists
            ? $model->variants->flatMap(fn ($variant) => $variant->units->map(fn ($unit) => ['variant' => $variant, 'unit' => $unit]))
            : collect();
        $fotoModel = $model->exists
            ? ($model->variants->flatMap->units->where('status', 'Tersedia')->first()?->foto_utama
                ?? $model->variants->flatMap->units->first()?->foto_utama)
            : null;
    @endphp
    <div class="py-6 w-full sm:px-6 lg:px-8"
         x-data="{ previewSrc: null, colorMap: @js($colorMap) }"
         @keydown.escape.window="previewSrc = null">
        <form action="{{ $model->exists ? route('admin.models.update', $model) : route('admin.models.store') }}" method="POST"
              class="{{ $model->exists ? 'grid lg:grid-cols-12 gap-6 items-start' : 'max-w-3xl' }}">
            @csrf @if ($model->exists) @method('PUT') @endif

            <div class="{{ $model->exists ? 'lg:col-span-7 space-y-6' : '' }}">
                <div class="bg-surface border border-line p-5 sm:p-6 rounded-xl shadow-sm space-y-5">
                    @if ($fotoModel)
                        <div class="flex items-center gap-4 p-3 rounded-xl bg-surface-2/70 border border-line">
                            <button type="button"
                                    @click="previewSrc = @js(asset($fotoModel))"
                                    title="Klik untuk perbesar foto"
                                    class="group shrink-0 rounded-lg overflow-hidden border border-line focus:outline-none focus:ring-2 focus:ring-accent cursor-zoom-in">
                                <img src="{{ asset($fotoModel) }}" alt="Preview {{ $model->nama }}" class="w-32 h-20 object-cover transition-transform duration-150 group-hover:scale-105">
                            </button>
                            <div class="text-xs text-muted space-y-0.5">
                                <p class="text-sm font-semibold text-ink">{{ $model->brand->nama ?? '' }} {{ $model->nama }}</p>
                                <p>Preview foto unit model ini</p>
                                <p class="text-accent font-medium">Klik gambar untuk memperbesar</p>
                            </div>
                        </div>
                    @endif

                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="brand_id" class="block text-sm font-medium text-ink">Brand</label>
                            <select id="brand_id" name="brand_id" required class="mt-1.5 w-full bg-surface border border-line rounded-lg text-ink min-h-[44px] pl-3 pr-9">
                                @foreach ($brands as $brand)
                                    <option value="{{ $brand->id }}" @selected(old('brand_id', $model->brand_id) == $brand->id)>{{ $brand->nama }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label for="nama" class="block text-sm font-medium text-ink">Nama model</label>
                            <input id="nama" name="nama" value="{{ old('nama', $model->nama) }}" required class="mt-1.5 w-full bg-surface border border-line rounded-lg text-ink min-h-[44px] px-3.5">
                        </div>
                    </div>

                    <div>
                        <label for="deskripsi" class="block text-sm font-medium text-ink">Deskripsi</label>
                        <textarea id="deskripsi" name="deskripsi" rows="3" class="mt-1.5 w-full bg-surface border border-line rounded-lg text-ink p-3">{{ old('deskripsi', $model->deskripsi) }}</textarea>
                    </div>

                    <label class="inline-flex items-center gap-2.5 text-sm font-medium text-ink cursor-pointer">
                        <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $model->aktif ?? true)) class="min-w-[20px] min-h-[20px] rounded border-line">
                        <span>Aktif di katalog</span>
                    </label>

                    @if (! $model->exists)
                        <x-input-error :messages="$errors->all()" />
                        <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                            <a href="{{ route('admin.models.index') }}" class="px-4 py-2.5 text-sm font-medium text-muted hover:text-ink border border-line rounded-lg min-h-[44px] inline-flex items-center">Batal</a>
                            <button class="px-6 py-2.5 text-sm font-semibold text-white bg-accent-strong rounded-lg min-h-[44px]">Simpan</button>
                        </div>
                    @endif
                </div>

                @if ($model->exists)
                    <div class="bg-surface border border-line p-5 sm:p-6 rounded-xl shadow-sm space-y-4">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="text-base font-semibold text-ink">Varian Model</h3>
                                <p class="text-xs text-muted mt-0.5">Kelola varian, transmisi, dan BBM untuk model ini.</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-surface-2 border border-line text-muted shrink-0 tabular-nums">
                                {{ $model->variants->count() }} varian
                            </span>
                        </div>

                        @if ($model->variants->isNotEmpty())
                            <div class="space-y-3">
                                @foreach ($model->variants as $variant)
                                    <div class="p-3.5 rounded-xl border border-line bg-surface-2/30 grid sm:grid-cols-12 gap-3 items-end">
                                        <div class="sm:col-span-5">
                                            <label class="block text-xs font-medium text-muted mb-1">Nama Varian</label>
                                            <input name="variants[{{ $variant->id }}][nama]"
                                                   value="{{ old("variants.{$variant->id}.nama", $variant->nama) }}"
                                                   required
                                                   class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] px-3">
                                        </div>
                                        <div class="sm:col-span-3">
                                            <label class="block text-xs font-medium text-muted mb-1">Transmisi</label>
                                            <select name="variants[{{ $variant->id }}][transmisi]"
                                                    class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] pl-3 pr-8">
                                                @foreach (['MT', 'AT', 'CVT'] as $t)
                                                    <option value="{{ $t }}" @selected(old("variants.{$variant->id}.transmisi", $variant->transmisi) === $t)>{{ $t }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="sm:col-span-2">
                                            <label class="block text-xs font-medium text-muted mb-1">BBM</label>
                                            <input name="variants[{{ $variant->id }}][bbm]"
                                                   value="{{ old("variants.{$variant->id}.bbm", $variant->bbm) }}"
                                                   required
                                                   class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] px-3">
                                        </div>
                                        <div class="sm:col-span-2 flex items-center sm:justify-end min-h-[40px]">
                                            <label class="inline-flex items-center gap-2 text-xs font-medium text-ink cursor-pointer">
                                                <input type="checkbox" name="variants[{{ $variant->id }}][aktif]" value="1"
                                                       @checked(old("variants.{$variant->id}.aktif", $variant->aktif))
                                                       class="min-w-[18px] min-h-[18px] rounded border-line">
                                                <span>Aktif</span>
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="pt-3 border-t border-line" x-data="{ tambahVarian: @js((bool) old('new_variant.nama')) }">
                            <button type="button" @click="tambahVarian = !tambahVarian"
                                    class="text-xs font-semibold text-accent hover:underline inline-flex items-center gap-1">
                                <span x-text="tambahVarian ? '− Batal tambah varian' : '+ Tambah varian baru'"></span>
                            </button>
                            <div x-show="tambahVarian" x-cloak class="mt-3 p-3.5 rounded-xl border border-dashed border-line bg-surface-2/40 grid sm:grid-cols-12 gap-3 items-end">
                                <div class="sm:col-span-5">
                                    <label class="block text-xs font-medium text-muted mb-1">Nama Varian Baru</label>
                                    <input name="new_variant[nama]" value="{{ old('new_variant.nama') }}" placeholder="Contoh: Luxury AT"
                                           class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] px-3">
                                </div>
                                <div class="sm:col-span-3">
                                    <label class="block text-xs font-medium text-muted mb-1">Transmisi</label>
                                    <select name="new_variant[transmisi]" class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] pl-3 pr-8">
                                        @foreach (['AT', 'MT', 'CVT'] as $t)
                                            <option value="{{ $t }}" @selected(old('new_variant.transmisi', 'AT') === $t)>{{ $t }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="sm:col-span-2">
                                    <label class="block text-xs font-medium text-muted mb-1">BBM</label>
                                    <input name="new_variant[bbm]" value="{{ old('new_variant.bbm', 'Bensin') }}"
                                           class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] px-3">
                                </div>
                                <div class="sm:col-span-2 flex items-center sm:justify-end min-h-[40px]">
                                    <label class="inline-flex items-center gap-2 text-xs font-medium text-ink cursor-pointer">
                                        <input type="checkbox" name="new_variant[aktif]" value="1" @checked(old('new_variant.aktif', true)) class="min-w-[18px] min-h-[18px] rounded border-line">
                                        <span>Aktif</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($model->exists)
                <div class="lg:col-span-5 bg-surface border border-line p-5 sm:p-6 rounded-xl shadow-sm space-y-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h3 class="text-base font-semibold text-ink">Warna Kendaraan</h3>
                            <p class="text-xs text-muted mt-0.5">Ubah warna unit kendaraan untuk model ini langsung di sini.</p>
                        </div>
                        <span class="px-2.5 py-1 rounded-full text-xs font-medium bg-surface-2 border border-line text-muted shrink-0 tabular-nums">
                            {{ $modelUnits->count() }} unit
                        </span>
                    </div>

                    @if ($modelUnits->isNotEmpty())
                        <div class="divide-y divide-line rounded-xl border border-line bg-surface-2/30 max-h-[460px] overflow-y-auto">
                            @foreach ($modelUnits as $item)
                                @php($unit = $item['unit'])
                                @php($variant = $item['variant'])
                                @php($selectedColor = old("unit_colors.{$unit->id}", $unit->color_id))
                                <div class="p-3.5 flex items-center justify-between gap-3"
                                     x-data="{ colorId: '{{ $selectedColor }}' }">
                                    <div class="min-w-0">
                                        <div class="flex items-center gap-2">
                                            <p class="text-sm font-medium text-ink truncate">{{ $variant->nama }}</p>
                                            <span class="px-1.5 py-0.5 rounded text-[11px] font-medium {{ $unit->status === 'Tersedia' ? 'bg-green-500/10 text-green-600 dark:text-green-400' : 'bg-amber-500/10 text-amber-600 dark:text-amber-400' }}">
                                                {{ $unit->status }}
                                            </span>
                                        </div>
                                        <p class="text-xs text-muted truncate mt-0.5">{{ $unit->branch->nama ?? '-' }} · {{ $unit->nomor_rangka ?: 'Tanpa VIN' }}</p>
                                    </div>
                                    <div class="flex items-center gap-2 shrink-0">
                                        <span class="w-4 h-4 rounded-full border border-black/20 shrink-0"
                                              :style="'background:' + (colorMap[colorId] || '#ccc')"></span>
                                        <select name="unit_colors[{{ $unit->id }}]"
                                                x-model="colorId"
                                                aria-label="Warna untuk {{ $variant->nama }}"
                                                class="bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] pl-3 pr-9">
                                            @foreach ($colors ?? [] as $color)
                                                <option value="{{ $color->id }}" @selected($selectedColor == $color->id)>
                                                    {{ $color->nama }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="p-4 rounded-xl border border-dashed border-line text-center text-xs text-muted">
                            Belum ada unit kendaraan pada model ini.
                        </div>
                    @endif

                    <div class="pt-3 border-t border-line" x-data="{ tambahWarna: @js((bool) old('new_color.nama')) }">
                        <button type="button" @click="tambahWarna = !tambahWarna"
                                class="text-xs font-semibold text-accent hover:underline inline-flex items-center gap-1">
                            <span x-text="tambahWarna ? '− Batal tambah warna' : '+ Tambah warna baru'"></span>
                        </button>
                        <div x-show="tambahWarna" x-cloak class="mt-3 p-3.5 rounded-xl border border-dashed border-line bg-surface-2/40 space-y-3">
                            <div class="grid grid-cols-12 gap-3 items-end">
                                <div class="col-span-8">
                                    <label class="block text-xs font-medium text-muted mb-1">Nama Warna Baru</label>
                                    <input name="new_color[nama]" value="{{ old('new_color.nama') }}" placeholder="Contoh: Abu-abu Metalik"
                                           class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] px-3">
                                </div>
                                <div class="col-span-4">
                                    <label class="block text-xs font-medium text-muted mb-1">Warna</label>
                                    <input type="color" name="new_color[kode_hex]" value="{{ old('new_color.kode_hex', '#6B7280') }}"
                                           class="w-full h-[40px] bg-surface border border-line rounded-lg p-1 cursor-pointer">
                                </div>
                            </div>
                            @if ($modelUnits->isNotEmpty())
                                <div>
                                    <label class="block text-xs font-medium text-muted mb-1">Terapkan ke Unit (Opsional)</label>
                                    <select name="new_color[unit_id]" class="w-full bg-surface border border-line rounded-lg text-sm text-ink min-h-[40px] pl-3 pr-9">
                                        <option value="">Hanya tambah ke daftar warna</option>
                                        @foreach ($modelUnits as $item)
                                            <option value="{{ $item['unit']->id }}" @selected(old('new_color.unit_id') == $item['unit']->id)>
                                                {{ $item['variant']->nama }} · {{ $item['unit']->branch->nama ?? '-' }} ({{ $item['unit']->color->nama ?? '-' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-12 bg-surface border border-line p-4 sm:px-6 rounded-xl shadow-sm flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                    <x-input-error :messages="$errors->all()" />
                    <div class="flex items-center justify-end gap-3 sm:ml-auto">
                        <a href="{{ route('admin.models.index') }}" class="px-4 py-2.5 text-sm font-medium text-muted hover:text-ink border border-line rounded-lg min-h-[44px] inline-flex items-center">Batal</a>
                        <button class="px-6 py-2.5 text-sm font-semibold text-white bg-accent-strong rounded-lg min-h-[44px]">Simpan Perubahan</button>
                    </div>
                </div>
            @endif
        </form>

        <div x-show="previewSrc" x-cloak
             @click.self="previewSrc = null"
             class="fixed inset-0 z-50 flex items-center justify-center bg-black/75 p-4 backdrop-blur-xs">
            <div class="relative max-w-3xl w-full bg-surface border border-line rounded-2xl overflow-hidden shadow-2xl">
                <div class="flex items-center justify-between px-4 py-3 border-b border-line">
                    <h3 class="text-sm font-semibold text-ink">Preview {{ $model->brand->nama ?? '' }} {{ $model->nama }}</h3>
                    <button type="button" @click="previewSrc = null"
                            class="px-2.5 py-1 text-xs font-medium rounded-lg border border-line text-muted hover:text-ink hover:bg-surface-2">
                        Tutup
                    </button>
                </div>
                <div class="p-3 bg-surface-2 flex items-center justify-center">
                    <img :src="previewSrc" alt="Preview {{ $model->nama }}" class="max-h-[75vh] w-auto object-contain rounded-xl">
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
