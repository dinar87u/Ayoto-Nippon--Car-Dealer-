@php
    $aktif = $transaksiAktif ?? ['spk' => 0, 'servis' => 0, 'sparepart' => 0, 'total' => 0];
    $adaTransaksiAktif = ($aktif['total'] ?? 0) > 0;
    $ringkasAktif = collect([
        ($aktif['spk'] ?? 0) > 0 ? $aktif['spk'] . ' SPK aktif' : null,
        ($aktif['servis'] ?? 0) > 0 ? $aktif['servis'] . ' Booking Servis aktif' : null,
        ($aktif['sparepart'] ?? 0) > 0 ? $aktif['sparepart'] . ' Order Sparepart aktif' : null,
    ])->filter()->implode(', ');
@endphp

<section class="space-y-5">
    <header>
        <h2 class="text-lg font-bold text-red-700 dark:text-red-300">
            Hapus Akun
        </h2>

        <p class="mt-1 text-sm text-muted">
            Akun yang dihapus tidak bisa dikembalikan, dan semua data terkait akan ikut terhapus. Unduh data penting Anda sebelum melanjutkan.
        </p>
    </header>

    @if ($adaTransaksiAktif)
        <div class="p-3.5 rounded-xl bg-amber-500/10 border border-amber-500/30 text-xs text-amber-800 dark:text-amber-200 space-y-1">
            <p class="font-bold">Penghapusan Akun Diblokir Sementara</p>
            <p class="leading-relaxed">
                Masih terdapat transaksi berjalan ({{ $ringkasAktif }}). Selesaikan atau batalkan seluruh transaksi aktif terlebih dahulu sebelum menghapus akun.
            </p>
        </div>

        <button
            type="button"
            disabled
            class="inline-flex items-center px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-widest bg-surface-2 text-muted border border-line cursor-not-allowed"
        >
            Hapus Akun Tidak Tersedia
        </button>
    @else
        <x-danger-button
            x-data=""
            x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
        >Hapus Akun</x-danger-button>

        <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
            <form method="post" action="{{ route('profile.destroy') }}" class="p-6" x-data="{ showDelPass: false }">
                @csrf
                @method('delete')

                <h2 class="text-lg font-medium text-ink">
                    Yakin ingin menghapus akun Anda?
                </h2>

                <p class="mt-1 text-sm text-muted">
                    Setelah dihapus, seluruh data akun hilang permanen. Masukkan password Anda untuk mengonfirmasi.
                </p>

                <div class="mt-6">
                    <x-input-label for="password" value="Password" class="sr-only" />

                    <div class="relative mt-1 w-full sm:w-3/4">
                        <input
                            id="password"
                            name="password"
                            :type="showDelPass ? 'text' : 'password'"
                            class="block w-full pr-11 rounded-lg border border-line bg-surface text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent"
                            placeholder="Masukkan password akun Anda"
                        />
                        <button type="button" @click="showDelPass = !showDelPass" class="absolute inset-y-0 right-0 px-3 text-muted hover:text-ink inline-flex items-center" :aria-label="showDelPass ? 'Sembunyikan password' : 'Tampilkan password'">
                            <svg x-show="!showDelPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            <svg x-show="showDelPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                        </button>
                    </div>

                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>

                <div class="mt-6 flex justify-end">
                    <x-secondary-button x-on:click="$dispatch('close')">
                        Batal
                    </x-secondary-button>

                    <x-danger-button class="ms-3">
                        Hapus Akun
                    </x-danger-button>
                </div>
            </form>
        </x-modal>
    @endif
</section>
