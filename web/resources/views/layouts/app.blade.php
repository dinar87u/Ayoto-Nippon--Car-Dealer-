<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <script>document.documentElement.classList.add('js');</script>
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
        <div class="min-h-screen lg:flex" x-data="{ open: window.matchMedia('(min-width: 1024px)').matches, rail: false }">
            @include('layouts.navigation')

            <div class="flex-1 min-w-0 flex flex-col">
                <div class="bg-surface/95 backdrop-blur-md border-b border-line px-4 sm:px-6 lg:px-8 h-14 flex items-center gap-3 sticky top-0 z-30">
                    <button type="button" @click="window.matchMedia('(min-width: 1024px)').matches ? rail = !rail : open = !open" aria-label="Menu" :aria-expanded="open" class="p-2 -ml-2 rounded-lg text-ink hover:bg-surface-2 min-w-[40px] min-h-[40px] inline-flex items-center justify-center transition-colors">
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
                    <a href="{{ route('home') }}" class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-line text-xs font-medium text-muted hover:text-ink hover:border-ink/30 transition-colors">
                        Lihat Web Publik ↗
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
