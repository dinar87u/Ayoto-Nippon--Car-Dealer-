<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>
            document.documentElement.classList.add('js');
            try {
                if (window.matchMedia('(min-width: 1024px)').matches && localStorage.getItem('ayoto-sidebar-rail') === '1') {
                    document.documentElement.classList.add('sidebar-rail-init');
                }
            } catch (e) {}
        </script>
        @include('partials.theme-script')

        <title>{{ config('app.name', 'Laravel') }}</title>
        <link rel="icon" type="image/png" href="{{ asset('images/logo-an.png') }}">

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=plus-jakarta-sans:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-ink antialiased bg-surface-2/40 dark:bg-surface">
        <div class="min-h-screen lg:flex" x-data="{
            open: false,
            ready: false,
            rail: (() => { try { return window.matchMedia('(min-width: 1024px)').matches && localStorage.getItem('ayoto-sidebar-rail') === '1'; } catch (e) { return false; } })(),
            init() {
                document.documentElement.classList.add('sidebar-ready');
                requestAnimationFrame(() => {
                    document.documentElement.classList.remove('sidebar-rail-init');
                    requestAnimationFrame(() => { this.ready = true; });
                });
            },
            toggleSidebar() {
                if (window.matchMedia('(min-width: 1024px)').matches) {
                    this.rail = !this.rail;
                    try { localStorage.setItem('ayoto-sidebar-rail', this.rail ? '1' : '0'); } catch (e) {}
                } else {
                    this.rail = false;
                    this.open = !this.open;
                }
            }
        }" @resize.window="if (window.matchMedia('(min-width: 1024px)').matches) { open = false; } else { rail = false; }">
            @include('layouts.navigation')

            <div class="flex-1 min-w-0 flex flex-col">
                <div class="bg-surface/95 backdrop-blur-md border-b border-line px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3 sticky top-0 z-30">
                    <button type="button" @click="toggleSidebar()" aria-label="Menu" :aria-expanded="window.matchMedia('(min-width: 1024px)').matches ? !rail : open" class="p-2 -ml-2 rounded-lg text-ink hover:bg-surface-2 min-w-[40px] min-h-[40px] inline-flex items-center justify-center transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    <span class="lg:hidden flex items-center gap-2 font-bold text-ink">
                        <x-application-logo class="block h-7 w-auto" />
                        Ayoto Nippon
                    </span>
                    <div class="hidden sm:flex items-center gap-2 text-xs text-muted">
                        <span class="font-semibold text-ink">{{ Auth::user()->name }}</span>
                        <span aria-hidden="true">/</span>
                        <span class="capitalize">{{ Auth::user()?->getRoleNames()->first() ?? 'Pelanggan' }}</span>
                        @if (Auth::user()?->branch)
                            <span aria-hidden="true">/</span>
                            <span>{{ Auth::user()->branch->nama }}</span>
                        @endif
                    </div>
                    <span class="flex-1"></span>
                    @php
                        $isStafBar = Auth::user()?->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']) ?? false;
                    @endphp
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-line text-xs font-semibold text-ink bg-surface hover:bg-surface-2 hover:border-accent transition-colors">
                        <svg class="w-4 h-4 text-accent shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                        <span>{{ $isStafBar ? 'Lihat Web Publik' : 'Kembali ke Halaman Utama' }}</span>
                    </a>
                    <x-theme-toggle />
                </div>

                <!-- Page Heading -->
                @if (isset($header))
                    <header class="bg-surface border-b border-line">
                        <div class="w-full py-5 px-4 sm:px-6 lg:px-8">
                            {{ $header }}
                        </div>
                    </header>
                @endif

                <!-- Page Content -->
                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
