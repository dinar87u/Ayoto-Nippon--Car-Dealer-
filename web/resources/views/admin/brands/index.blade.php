<x-app-layout>
    <x-slot name="header"><h2 class="font-semibold text-xl text-ink leading-tight">Brand</h2></x-slot>
    <div class="py-6 w-full sm:px-6 lg:px-8">
        @if (session('sukses'))<p class="mb-4 p-3 text-sm bg-green-50 dark:bg-green-500/10 border border-green-200 dark:border-green-500/30 rounded-lg">{{ session('sukses') }}</p>@endif
        <a href="{{ route('admin.brands.create') }}" class="inline-flex items-center px-4 py-2 text-sm font-semibold text-white bg-accent-strong rounded-lg min-h-[44px]">Tambah Brand</a>
        <div class="mt-4 bg-surface border border-line rounded-xl shadow-sm overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-line bg-surface-2/60 text-left text-xs uppercase tracking-wider text-muted">
                        <th class="px-4 py-3">Preview</th>
                        <th class="px-4 py-3">Nama Brand</th>
                        <th class="px-4 py-3">Model</th>
                        <th class="px-4 py-3">Aktif</th>
                        <th class="px-4 py-3 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-line">
                    @forelse ($brands as $brand)
                        <tr class="hover:bg-surface-2/40 transition-colors">
                            <td class="px-4 py-2.5">
                                <img src="{{ $brand->logo_url }}" alt="Logo {{ $brand->nama }}" loading="lazy"
                                     class="w-14 h-10 object-contain rounded-lg bg-white border border-line p-1">
                            </td>
                            <td class="px-4 py-3 font-medium text-ink">{{ $brand->nama }}</td>
                            <td class="px-4 py-3 text-muted tabular-nums">{{ $brand->car_models_count }}</td>
                            <td class="px-4 py-3">{{ $brand->aktif ? 'Ya' : 'Tidak' }}</td>
                            <td class="px-4 py-3 text-right"><a href="{{ route('admin.brands.edit', $brand) }}" class="underline font-medium text-accent min-h-[44px] inline-flex items-center">Ubah</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-6 text-muted text-center">Belum ada brand.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $brands->links() }}</div>
    </div>
</x-app-layout>
