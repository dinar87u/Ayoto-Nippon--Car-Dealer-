<section
    x-data="{
        showCurrent: false,
        showNew: false,
        showConfirm: false,
        pwdBaru: '',
        pwdKonfirm: '',
        get cukupPanjang() { return this.pwdBaru.length >= 8; },
        get adaHuruf() { return /[a-zA-Z]/.test(this.pwdBaru); },
        get adaAngkaAtauSimbol() { return /[0-9!@#$%^&*()_+\-=\[\]{};':&quot;\\|,.<>\/?]/.test(this.pwdBaru); },
        get skor() {
            if (!this.pwdBaru) return 0;
            let s = 0;
            if (this.cukupPanjang) s++;
            if (this.adaHuruf) s++;
            if (this.adaAngkaAtauSimbol) s++;
            return s;
        },
        get labelKekuatan() {
            return ['', 'Lemah', 'Cukup', 'Kuat'][this.skor] || '';
        },
        get warnaBar() {
            return ['', 'bg-rose-500 w-1/3', 'bg-amber-500 w-2/3', 'bg-emerald-500 w-full'][this.skor] || 'w-0';
        }
    }"
>
    <header>
        <h2 class="text-lg font-bold text-ink">Ganti Password</h2>
        <p class="mt-1 text-sm text-muted">
            Gunakan password minimal 8 karakter dengan kombinasi huruf dan angka. Mengganti password akan mengeluarkan sesi di perangkat lain.
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="'Password Saat Ini'" />
            <div class="relative mt-1">
                <input
                    id="update_password_current_password"
                    name="current_password"
                    :type="showCurrent ? 'text' : 'password'"
                    autocomplete="current-password"
                    class="block w-full pr-11 rounded-lg border border-line bg-surface text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent"
                />
                <button type="button" @click="showCurrent = !showCurrent" class="absolute inset-y-0 right-0 px-3 text-muted hover:text-ink inline-flex items-center" :aria-label="showCurrent ? 'Sembunyikan password saat ini' : 'Tampilkan password saat ini'">
                    <svg x-show="!showCurrent" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showCurrent" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="'Password Baru'" />
            <div class="relative mt-1">
                <input
                    id="update_password_password"
                    name="password"
                    x-model="pwdBaru"
                    :type="showNew ? 'text' : 'password'"
                    autocomplete="new-password"
                    class="block w-full pr-11 rounded-lg border border-line bg-surface text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent"
                />
                <button type="button" @click="showNew = !showNew" class="absolute inset-y-0 right-0 px-3 text-muted hover:text-ink inline-flex items-center" :aria-label="showNew ? 'Sembunyikan password baru' : 'Tampilkan password baru'">
                    <svg x-show="!showNew" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showNew" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>

            <!-- Indikator Kekuatan Password -->
            <div x-show="pwdBaru.length > 0" x-cloak class="mt-2.5 space-y-2">
                <div class="flex items-center justify-between text-[11px]">
                    <span class="text-muted">Kekuatan password</span>
                    <span class="font-bold" :class="skor >= 3 ? 'text-emerald-700 dark:text-emerald-300' : (skor === 2 ? 'text-amber-700 dark:text-amber-300' : 'text-rose-600 dark:text-rose-400')" x-text="labelKekuatan"></span>
                </div>
                <div class="h-1.5 w-full rounded-full bg-surface-2 overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-200" :class="warnaBar"></div>
                </div>
                <div class="flex flex-wrap gap-2 text-[11px]">
                    <span :class="cukupPanjang ? 'text-emerald-700 dark:text-emerald-300' : 'text-muted'">
                        <span x-text="cukupPanjang ? '✓' : '•'"></span> Min. 8 karakter
                    </span>
                    <span :class="adaHuruf ? 'text-emerald-700 dark:text-emerald-300' : 'text-muted'">
                        <span x-text="adaHuruf ? '✓' : '•'"></span> Huruf
                    </span>
                    <span :class="adaAngkaAtauSimbol ? 'text-emerald-700 dark:text-emerald-300' : 'text-muted'">
                        <span x-text="adaAngkaAtauSimbol ? '✓' : '•'"></span> Angka / simbol
                    </span>
                </div>
            </div>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="'Konfirmasi Password Baru'" />
            <div class="relative mt-1">
                <input
                    id="update_password_password_confirmation"
                    name="password_confirmation"
                    x-model="pwdKonfirm"
                    :type="showConfirm ? 'text' : 'password'"
                    autocomplete="new-password"
                    class="block w-full pr-11 rounded-lg border border-line bg-surface text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent"
                />
                <button type="button" @click="showConfirm = !showConfirm" class="absolute inset-y-0 right-0 px-3 text-muted hover:text-ink inline-flex items-center" :aria-label="showConfirm ? 'Sembunyikan konfirmasi password' : 'Tampilkan konfirmasi password'">
                    <svg x-show="!showConfirm" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showConfirm" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            <p x-show="pwdKonfirm.length > 0 && pwdKonfirm !== pwdBaru" x-cloak class="mt-1.5 text-xs text-rose-600 dark:text-rose-400">
                Konfirmasi password belum cocok.
            </p>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>Simpan</x-primary-button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 4000)"
                    class="text-xs font-semibold text-emerald-700 dark:text-emerald-300"
                >Password diperbarui &amp; sesi perangkat lain dikeluarkan.</p>
            @endif
        </div>
    </form>
</section>
