@php
    $stafMode = $staf ?? false;
    $nikTerkunci = $nikTerkunci ?? false;
    $telpAsli = (string) ($customer?->telp ?? '');
    $nikAsli = (string) ($customer?->nik ?? '');
    $alamatAsli = (string) ($customer?->alamat ?? '');

    $sensor = function (?string $nilai, int $awal = 4, int $akhir = 4): string {
        $v = trim((string) $nilai);
        if ($v === '') {
            return 'Belum diisi';
        }
        $len = mb_strlen($v);
        if ($len <= $awal + $akhir) {
            return str_repeat('*', max(4, $len));
        }
        return mb_substr($v, 0, $awal) . str_repeat('*', $len - $awal - $akhir) . mb_substr($v, -$akhir);
    };

    $telpSensor = $sensor($telpAsli, 4, 4);
    $nikSensor = $sensor($nikAsli, 4, 4);
    $adaErrorForm = $errors->hasAny(['name', 'email', 'telp', 'nik', 'alamat', 'current_password']);
@endphp

<section
    x-data="{
        modeEdit: {{ $adaErrorForm ? 'true' : 'false' }},
        lihatTelp: false,
        lihatNik: false,
        lihatPass: false,
        emailAsli: @js(strtolower(trim((string) $user->email))),
        telpAsli: @js(trim($telpAsli)),
        nikAsli: @js(trim($nikAsli)),
        email: @js(old('email', $user->email)),
        telp: @js(old('telp', $telpAsli)),
        nik: @js(old('nik', $nikAsli)),
        staf: {{ $stafMode ? 'true' : 'false' }},
        get perluKonfirmasi() {
            const emailUbah = (this.email || '').trim().toLowerCase() !== this.emailAsli;
            const telpUbah = !this.staf && (this.telp || '').trim() !== this.telpAsli;
            const nikUbah = !this.staf && (this.nik || '').trim() !== this.nikAsli;
            return emailUbah || telpUbah || nikUbah;
        }
    }"
>
    <header class="pb-4 border-b border-line">
        <div class="flex items-center gap-2">
            <h2 class="text-lg font-bold text-ink">Data Diri</h2>
            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border border-emerald-500/20">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                Terlindungi
            </span>
        </div>
        <p class="mt-1 text-sm text-muted">
            Data sensitif disensor secara default. Perubahan Email, No. HP, atau NIK memerlukan konfirmasi password.
        </p>
    </header>

    @if (session('status') === 'profile-updated')
        <div
            x-data="{ show: true }"
            x-show="show"
            x-transition
            x-init="setTimeout(() => show = false, 4000)"
            class="mt-4 p-3.5 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-xs font-semibold text-emerald-700 dark:text-emerald-300"
        >
            Perubahan data diri berhasil disimpan.
        </div>
    @endif

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <!-- Mode Baca (Read-Only dengan Sensor Privasi) -->
    <div x-show="!modeEdit" class="mt-5 space-y-4">
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3.5 text-sm">
            <div class="p-3.5 rounded-xl bg-surface-2/50 border border-line">
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-muted">Nama Lengkap</dt>
                <dd class="mt-1 font-bold text-ink">{{ $user->name }}</dd>
            </div>

            <div class="p-3.5 rounded-xl bg-surface-2/50 border border-line">
                <dt class="text-[11px] font-semibold uppercase tracking-wider text-muted">Email Akun</dt>
                <dd class="mt-1 font-bold text-ink break-all">{{ $user->email }}</dd>
                @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                    <button form="send-verification" class="mt-1 text-xs font-semibold text-accent hover:underline">
                        Belum terverifikasi, kirim ulang tautan &rarr;
                    </button>
                @endif
            </div>

            @unless ($stafMode)
                <div class="p-3.5 rounded-xl bg-surface-2/50 border border-line">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-muted">No. HP / WhatsApp</dt>
                        @if ($telpAsli !== '')
                            <button type="button" @click="lihatTelp = !lihatTelp" class="text-xs text-muted hover:text-ink inline-flex items-center gap-1" :aria-label="lihatTelp ? 'Sembunyikan No. HP' : 'Tampilkan No. HP'">
                                <svg x-show="!lihatTelp" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="lihatTelp" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        @endif
                    </div>
                    <dd class="mt-1 font-mono font-bold text-ink tabular-nums" x-text="lihatTelp ? @js($telpAsli ?: 'Belum diisi') : @js($telpSensor)">{{ $telpSensor }}</dd>
                </div>

                <div class="p-3.5 rounded-xl bg-surface-2/50 border border-line">
                    <div class="flex items-center justify-between gap-2">
                        <dt class="text-[11px] font-semibold uppercase tracking-wider text-muted flex items-center gap-1.5">
                            <span>NIK KTP</span>
                            @if ($nikTerkunci)
                                <span class="px-1.5 py-0.5 rounded bg-amber-500/15 text-amber-700 dark:text-amber-300 text-[9px] font-bold">TERKUNCI</span>
                            @endif
                        </dt>
                        @if ($nikAsli !== '')
                            <button type="button" @click="lihatNik = !lihatNik" class="text-xs text-muted hover:text-ink inline-flex items-center gap-1" :aria-label="lihatNik ? 'Sembunyikan NIK' : 'Tampilkan NIK'">
                                <svg x-show="!lihatNik" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="lihatNik" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        @endif
                    </div>
                    <dd class="mt-1 font-mono font-bold text-ink tabular-nums" x-text="lihatNik ? @js($nikAsli ?: 'Belum diisi') : @js($nikSensor)">{{ $nikSensor }}</dd>
                </div>

                <div class="sm:col-span-2 p-3.5 rounded-xl bg-surface-2/50 border border-line">
                    <dt class="text-[11px] font-semibold uppercase tracking-wider text-muted">Alamat Domisili</dt>
                    <dd class="mt-1 font-medium text-ink">{{ $alamatAsli ?: 'Belum diisi' }}</dd>
                </div>
            @endunless
        </dl>

        <div class="pt-2 flex justify-end">
            <button
                type="button"
                @click="modeEdit = true"
                class="px-4 py-2 rounded-xl text-xs font-bold text-ink bg-surface border border-line hover:border-accent hover:text-accent transition-colors inline-flex items-center gap-1.5"
            >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Ubah Data Diri
            </button>
        </div>
    </div>

    <!-- Mode Edit -->
    <form x-show="modeEdit" x-cloak method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-5">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="'Nama Lengkap'" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="'Email'" />
            <x-text-input id="email" name="email" type="email" x-model="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-ink">
                        Email Anda belum terverifikasi.
                        <button form="send-verification" class="underline text-sm text-muted hover:text-ink rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            Kirim ulang email verifikasi
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            Tautan verifikasi baru telah dikirim ke email Anda.
                        </p>
                    @endif
                </div>
            @endif
        </div>

        @unless ($stafMode)
            <div class="pt-4 border-t border-line grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="telp" :value="__('No. HP / WhatsApp')" />
                    <x-text-input id="telp" name="telp" type="text" inputmode="numeric" x-model="telp" class="mt-1 block w-full tabular-nums" :value="old('telp', $telpAsli)" placeholder="08xxxxxxxxxx" autocomplete="tel" />
                    <x-input-error class="mt-2" :messages="$errors->get('telp')" />
                </div>
                <div>
                    <div class="flex items-center justify-between gap-2">
                        <x-input-label for="nik" :value="__('NIK KTP (16 digit)')" />
                        @if ($nikTerkunci)
                            <span class="text-[10px] font-bold uppercase tracking-wider text-amber-700 dark:text-amber-300">Terkunci</span>
                        @endif
                    </div>
                    @if ($nikTerkunci)
                        <input id="nik" name="nik" type="text" value="{{ $nikAsli }}" readonly
                            class="mt-1 block w-full rounded-lg border border-line bg-surface-2 text-muted text-sm font-mono tabular-nums cursor-not-allowed px-3 py-2">
                        <p class="mt-1.5 text-[11px] text-amber-700 dark:text-amber-300">
                            NIK sudah terikat pada dokumen SPK/STNK. Hubungi admin cabang jika perlu perubahan.
                        </p>
                    @else
                        <x-text-input id="nik" name="nik" type="text" inputmode="numeric" maxlength="16" x-model="nik" class="mt-1 block w-full tabular-nums font-mono" :value="old('nik', $nikAsli)" placeholder="16 digit sesuai KTP" autocomplete="off" />
                    @endif
                    <x-input-error class="mt-2" :messages="$errors->get('nik')" />
                </div>
                <div class="sm:col-span-2">
                    <x-input-label for="alamat" :value="__('Alamat Domisili')" />
                    <textarea id="alamat" name="alamat" rows="2" placeholder="Alamat domisili (untuk faktur & dokumen SPK)"
                        class="mt-1 block w-full rounded-lg border border-line bg-surface text-ink text-sm p-3 focus:border-accent focus:ring-1 focus:ring-accent">{{ old('alamat', $alamatAsli) }}</textarea>
                    <x-input-error class="mt-2" :messages="$errors->get('alamat')" />
                </div>
            </div>
        @endunless

        <!-- Konfirmasi Password Otomatis Muncul Jika Data Sensitif Diubah -->
        <div x-show="perluKonfirmasi || {{ $errors->has('current_password') ? 'true' : 'false' }}" x-cloak class="p-4 rounded-xl bg-amber-500/10 border border-amber-500/30 space-y-3">
            <div class="flex items-start gap-2.5">
                <svg class="w-4 h-4 text-amber-700 dark:text-amber-300 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                <div>
                    <p class="text-xs font-bold text-amber-800 dark:text-amber-200">Verifikasi Keamanan Diperlukan</p>
                    <p class="text-xs text-amber-800/80 dark:text-amber-200/80 mt-0.5">
                        Anda mengubah data sensitif (Email, No. HP, atau NIK). Masukkan password akun Anda saat ini untuk menyimpan perubahan.
                    </p>
                </div>
            </div>

            <div>
                <x-input-label for="profile_current_password" :value="'Password Saat Ini'" />
                <div class="relative mt-1">
                    <input
                        id="profile_current_password"
                        name="current_password"
                        :type="lihatPass ? 'text' : 'password'"
                        :required="perluKonfirmasi"
                        autocomplete="current-password"
                        placeholder="Masukkan password akun Anda"
                        class="block w-full pr-11 rounded-lg border border-line bg-surface text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent"
                    >
                    <button type="button" @click="lihatPass = !lihatPass" class="absolute inset-y-0 right-0 px-3 text-muted hover:text-ink inline-flex items-center" :aria-label="lihatPass ? 'Sembunyikan password' : 'Tampilkan password'">
                        <svg x-show="!lihatPass" class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        <svg x-show="lihatPass" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                    </button>
                </div>
                <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
            </div>
        </div>

        <div class="flex flex-wrap items-center gap-3 pt-2">
            <x-primary-button>Simpan Perubahan</x-primary-button>
            <button
                type="button"
                @click="modeEdit = false; email = emailAsli; telp = telpAsli; nik = nikAsli;"
                class="px-4 py-2 rounded-lg text-xs font-semibold uppercase tracking-widest text-ink bg-surface border border-line hover:bg-surface-2 transition-colors"
            >
                Batal
            </button>
        </div>
    </form>
</section>
