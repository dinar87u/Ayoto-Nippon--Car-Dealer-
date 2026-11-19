<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-ink leading-tight">{{ $part->exists ? 'Ubah Sparepart' : 'Tambah Sparepart' }}</h2>
    </x-slot>

    <div class="py-6 w-full sm:px-6 lg:px-8">
        <a href="{{ route('admin.spareparts.index') }}" class="text-xs font-semibold text-accent hover:underline inline-flex items-center min-h-[36px]">← Kembali ke daftar sparepart</a>

        <form action="{{ $part->exists ? route('admin.spareparts.update', $part) : route('admin.spareparts.store') }}" method="POST"
            class="mt-3 max-w-2xl bg-surface rounded-2xl border border-line shadow-sm p-6 space-y-5">
            @csrf @if ($part->exists) @method('PUT') @endif

            <div>
                <label for="branch_id" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Cabang</label>
                <select id="branch_id" name="branch_id" required class="w-full rounded-lg border-line text-sm min-h-[44px]">
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}" @selected(old('branch_id', $part->branch_id) == $branch->id)>{{ $branch->nama }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="nama" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Nama sparepart</label>
                    <input id="nama" name="nama" value="{{ old('nama', $part->nama) }}" required placeholder="mis. Filter Oli"
                        class="w-full rounded-lg border-line text-sm min-h-[44px]">
                </div>
                <div>
                    <label for="kode" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Kode (opsional)</label>
                    <input id="kode" name="kode" value="{{ old('kode', $part->kode) }}" placeholder="mis. FO-2024"
                        class="w-full rounded-lg border-line text-sm font-mono min-h-[44px]">
                </div>
                <div>
                    <label for="harga" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Harga jual (Rp)</label>
                    <input id="harga" type="number" name="harga" value="{{ old('harga', $part->harga ?? 0) }}" required min="0"
                        class="w-full rounded-lg border-line text-sm tabular-nums min-h-[44px]">
                </div>
                <div>
                    <label for="stok" class="block text-xs font-semibold uppercase tracking-wider text-muted mb-1.5">Stok</label>
                    <input id="stok" type="number" name="stok" value="{{ old('stok', $part->stok ?? 0) }}" required min="0"
                        class="w-full rounded-lg border-line text-sm tabular-nums min-h-[44px]">
                </div>
            </div>

            <label class="flex items-center gap-3 text-sm text-ink">
                <input type="checkbox" name="aktif" value="1" @checked(old('aktif', $part->aktif ?? true)) class="min-w-[20px] min-h-[20px]">
                Aktif dijual (sparepart tampil di katalog beli sparepart customer).
            </label>

            <x-input-error :messages="$errors->all()" />

            <div class="flex gap-3 pt-1">
                <button class="flex-1 px-6 py-3 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition">Simpan</button>
                <a href="{{ route('admin.spareparts.index') }}" class="px-6 py-3 text-sm font-semibold text-ink bg-surface-2 hover:bg-line border border-line rounded-xl min-h-[44px] transition">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
