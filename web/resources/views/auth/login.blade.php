@php
    $staf = ($portal ?? 'pelanggan') === 'staf';
@endphp

<x-site-layout :judul="$staf ? 'Login Staf' : 'Masuk Akun'">
    <div class="min-h-screen flex items-center justify-center px-4 py-10 bg-surface-2">
        <div class="w-full max-w-5xl bg-surface border border-line rounded-3xl overflow-hidden shadow-2xl shadow-black/10 grid grid-cols-1 lg:grid-cols-2">

            <!-- Panel Form -->
            <div class="p-8 sm:p-10 lg:p-12 flex flex-col justify-center">
                <div class="flex items-center justify-between gap-3">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2.5 group focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-accent rounded-xl">
                        <img src="{{ asset('images/logo-an.png') }}" alt="Logo Ayoto Nippon" class="h-9 w-9 object-contain transition-transform group-hover:scale-105">
                        <span class="text-sm font-extrabold tracking-tight text-ink group-hover:text-accent transition-colors">Ayoto Nippon</span>
                    </a>
                </div>

                <x-auth-session-status class="mt-6 text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 p-3 rounded-xl text-xs" :status="session('status')" />

                <div class="mt-6">
                    <h1 class="text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">{{ $staf ? 'Masuk Portal Staf' : 'Selamat datang kembali!' }}</h1>
                    <p class="mt-2 text-sm text-muted leading-relaxed">
                        {{ $staf
                            ? 'Akses sistem untuk sales consultant, admin cabang, service advisor, dan mekanik.'
                            : 'Masukkan email dan kata sandi untuk mengakses SPK, test drive, servis, dan riwayat transaksi Anda.' }}
                    </p>
                </div>

                <form method="POST" action="{{ $staf ? route('login.staf.store') : route('login.store') }}" class="mt-7 space-y-5" x-data="{ lihat: false }">
                    @csrf

                    <div class="space-y-4">
                        <div>
                            <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-muted">Alamat Email</label>
                            <input id="email" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username"
                                placeholder="nama@email.com"
                                class="mt-1.5 block w-full bg-surface border border-line rounded-xl text-ink placeholder-gray-500 text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[48px] px-3.5">
                            @error('email')
                                <p class="mt-1.5 text-xs text-accent font-medium">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <div class="flex items-center justify-between">
                                <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-muted">Kata Sandi</label>
                                @if (Route::has('password.request'))
                                    <a href="{{ route('password.request') }}" class="text-xs font-medium text-ink hover:text-accent transition-colors">Lupa kata sandi?</a>
                                @endif
                            </div>
                            <div class="relative mt-1.5">
                                <input id="password" :type="lihat ? 'text' : 'password'" name="password" required autocomplete="current-password"
                                    placeholder="Masukkan kata sandi"
                                    class="block w-full bg-surface border border-line rounded-xl text-ink placeholder-gray-500 text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[48px] px-3.5 pr-12">
                                <button type="button" @click="lihat = !lihat" aria-label="Tampilkan atau sembunyikan kata sandi"
                                    class="absolute inset-y-0 right-0 px-3.5 inline-flex items-center text-muted hover:text-ink transition-colors min-w-[44px] min-h-[48px] justify-center">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" x-show="!lihat" aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7z"/><circle cx="12" cy="12" r="3"/>
                                    </svg>
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" x-show="lihat" x-cloak aria-hidden="true">
                                        <path d="M2 12s3.5-7 10-7c2 0 3.8.7 5.3 1.7M22 12s-3.5 7-10 7c-2 0-3.8-.7-5.3-1.7"/><path d="M3 3l18 18"/>
                                    </svg>
                                </button>
                            </div>
                            @error('password')
                                <p class="mt-1.5 text-xs text-accent font-medium">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <label for="remember_me" class="inline-flex items-center min-h-[36px] cursor-pointer text-xs">
                        <input id="remember_me" type="checkbox" name="remember"
                            class="rounded bg-surface border-line text-accent focus:ring-accent focus:ring-offset-0">
                        <span class="ms-2 text-muted">Ingat sesi saya</span>
                    </label>

                    <button type="submit"
                        class="w-full py-3.5 px-6 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[48px] shadow-lg shadow-accent/25 transition-all flex items-center justify-center active:scale-[0.99]">
                        {{ $staf ? 'Masuk Portal Staf' : 'Masuk' }}
                    </button>
                </form>

                @unless ($staf)
                    <div class="mt-7 pt-5 border-t border-line text-xs text-muted leading-relaxed">
                        <p>Belum memiliki akun pelanggan? <a href="{{ route('register') }}" class="font-semibold text-ink hover:text-accent underline underline-offset-2 transition-colors">Daftar sekarang</a></p>
                    </div>
                @endunless
            </div>

            <!-- Panel Brand: Logo -->
            <div class="hidden lg:flex flex-col items-center justify-center gap-8 bg-surface border-l border-line p-12">
                <img src="{{ asset('images/logo-an.png') }}" alt="Logo Ayoto Nippon" class="w-[70%] max-w-[340px] object-contain">
                <div class="text-center">
                    <p class="text-[11px] font-bold tracking-[0.22em] uppercase text-accent">Official Dealer Mobil</p>
                    <p class="mt-1.5 text-xs text-muted">Jakarta &amp; Bandung</p>
                </div>
                <div class="w-16 h-px bg-line" aria-hidden="true"></div>
                <p class="text-xs text-muted text-center max-w-[270px] leading-relaxed font-medium">
                    Dirancang untuk kemudahan. Dibuat untuk setiap perjalanan Anda.
                </p>
            </div>
        </div>
    </div>
</x-site-layout>
