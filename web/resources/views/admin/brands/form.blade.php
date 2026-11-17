<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">{{ $brand->exists ? 'Ubah Brand' : 'Tambah Brand' }}</h2></x-slot>
    <div class="py-6 w-full sm:px-6 lg:px-8">
        <form action="{{ $brand->exists ? route('admin.brands.update', $brand) : route('admin.brands.store') }}" method="POST" class="bg-surface border border-line p-5 rounded-xl shadow-sm space-y-4">
            @csrf @if ($brand->exists) @method('PUT') @endif
            <div>
                <label for="nama" class="block text-sm font-medium text-ink">Nama brand</label>
                <input id="nama" name="nama" value="{{ old('nama', $brand->nama) }}" required class="mt-1 w-full bg-surface border border-line rounded-lg text-ink min-h-[44px] px-3.5">
            </div>
            @php
                $initialLogo = old('logo', $brand->logo);
                $fallbackUrl = $brand->exists ? $brand->logo_url : asset('images/logo-an.png');
            @endphp
            <div x-data="{ logo: @js($initialLogo ?? ''), baseUrl: @js(rtrim(asset(''), '/')), fallback: @js($fallbackUrl) }">
                <label for="logo" class="block text-sm font-medium text-ink">Logo Brand</label>
                <div class="mt-1 grid sm:grid-cols-2 gap-3">
                    <select aria-label="Pilih logo dari source"
                            x-model="logo"
                            class="w-full bg-surface border border-line rounded-lg text-ink min-h-[44px] px-3.5">
                        <option value="">Otomatis dari slug / pilih source...</option>
                        @foreach ($sourceLogos ?? [] as $path)
                            <option value="{{ $path }}" @selected($initialLogo === $path)>{{ basename($path) }}</option>
                        @endforeach
                    </select>
                    <input id="logo" name="logo" x-model="logo" placeholder="images/brands/toyota.png"
                           class="w-full bg-surface border border-line rounded-lg text-ink min-h-[44px] px-3.5">
                </div>
                <div class="mt-2.5 inline-flex items-center gap-3 p-2.5 rounded-xl bg-surface-2 border border-line">
                    <img :src="logo ? (logo.startsWith('http') ? logo : baseUrl + '/' + logo.replace(/^\/+/, '')) : fallback"
                         alt="Preview {{ $brand->nama ?: 'Brand' }}"
                         class="w-16 h-12 object-contain bg-white rounded-lg p-1 border border-line">
                    <span class="text-xs text-muted" x-text="logo || 'Otomatis: ' + fallback.replace(baseUrl + '/', '')"></span>
                </div>
            </div>
            <label class="flex items-center gap-2 text-sm text-ink"><input type="checkbox" name="aktif" value="1" @checked(old('aktif', $brand->aktif ?? true)) class="min-w-[20px] min-h-[20px]"> Aktif</label>
            <x-input-error :messages="$errors->all()" />
            <button class="w-full px-6 py-3 font-semibold text-white bg-accent-strong rounded-lg min-h-[44px]">Simpan</button>
        </form>
    </div>
</x-app-layout>
