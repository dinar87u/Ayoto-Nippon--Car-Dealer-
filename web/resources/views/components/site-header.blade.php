@props(['transparan' => false])
<header x-data="{ menu: false, scrolled: false }" @keydown.escape.window="menu = false"
    class="{{ $transparan ? 'site-header--overlay' : 'bg-surface/90 backdrop-blur-md border-b border-line sticky top-0 z-40 transition-colors' }}"
    @if ($transparan) :class="scrolled ? 'is-scrolled' : ''" @scroll.window.passive="scrolled = window.scrollY > 30" @endif>
    @php
        $staf = auth()->user()?->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']) ?? false;
    @endphp
    <div class="w-full px-4 sm:px-6 lg:px-8 py-3.5 flex items-center justify-between lg:grid lg:grid-cols-[1fr_auto_1fr] gap-4">
        <a href="{{ route('home') }}" class="flex items-center gap-3 min-h-[44px] group lg:justify-self-start">
            <div class="relative">
                <img src="{{ asset('images/logo-an.png') }}" alt="Logo Ayoto Nippon" class="h-9 w-9 object-contain drop-shadow-[0_0_12px_rgba(225,29,72,0.35)] transition-transform group-hover:scale-105">
            </div>
            <div class="flex flex-col">
                <span class="text-base sm:text-lg font-bold tracking-tight text-ink group-hover:text-accent flex items-center gap-1.5">
                    Ayoto Nippon
                </span>
                <span class="text-[10px] tracking-widest uppercase text-muted font-medium -mt-0.5">Official Dealer</span>
            </div>
        </a>

        <nav class="hidden lg:flex items-center gap-6 lg:justify-self-center" aria-label="Navigasi utama">
            @foreach ([['katalog.index', 'Katalog'], ['simulasi.form', 'Simulasi'], ['test-drive.create', 'Test Drive'], ['servis.create', 'Servis'], ['promo.index', 'Promo'], ['cabang.index', 'Cabang']] as [$route, $label])
                @php
                    $isAktif = request()->routeIs($route) || (str_contains($route, '.') && request()->routeIs(explode('.', $route)[0].'.*'));
                @endphp
                <a href="{{ route($route) }}"
                   @if ($isAktif) aria-current="page" @endif
                   class="nav-underline-link py-1.5 text-sm font-medium transition-colors min-h-[40px] inline-flex items-center whitespace-nowrap {{ $isAktif ? 'is-active text-accent font-semibold' : 'text-muted hover:text-accent' }}">
                    {{ $label }}
                </a>
            @endforeach
        </nav>

        <div class="hidden lg:flex items-center gap-4 lg:justify-self-end">
            <x-theme-toggle />
            @auth
                @unless ($staf)
                    <a href="{{ route('pesanan.index') }}"
                       @if (request()->routeIs('pesanan.*')) aria-current="page" @endif
                       class="nav-underline-link py-1.5 text-sm font-medium transition-colors min-h-[40px] inline-flex items-center whitespace-nowrap {{ request()->routeIs('pesanan.*') ? 'is-active text-accent font-semibold' : 'text-muted hover:text-accent' }}">
                        Pesanan Saya
                    </a>
                    <span class="text-muted" aria-hidden="true">|</span>
                @endunless
                <a href="{{ $staf ? route('dashboard') : route('pesanan.index') }}" class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-xl hover:bg-surface-2 border border-transparent hover:border-accent transition-all min-h-[44px]" aria-label="{{ $staf ? 'Dashboard' : 'Pesanan Saya' }} {{ Auth::user()->name }}">
                    <span class="w-8 h-8 rounded-full bg-gradient-to-br from-accent-strong to-[#9E0E19] text-white font-bold text-xs inline-flex items-center justify-center shrink-0 shadow-sm" aria-hidden="true">{{ strtoupper(collect(explode(' ', Auth::user()->name ?? ''))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('')) }}</span>
                    <span class="text-sm font-medium text-ink max-w-[140px] truncate">{{ Auth::user()->name }}</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-2 text-sm font-medium text-muted hover:text-accent rounded-lg hover:bg-surface-2 transition-colors min-h-[44px]">Keluar</button>
                </form>
            @else
                <a href="{{ route('login') }}" class="nav-underline-link py-1.5 text-sm font-medium text-muted hover:text-accent transition-colors min-h-[40px] inline-flex items-center whitespace-nowrap">Masuk</a>
            @endauth
        </div>

        <x-theme-toggle class="lg:hidden" />

        <button type="button" @click="menu = !menu" aria-label="Menu" :aria-expanded="menu" class="lg:hidden p-2.5 rounded-xl text-muted hover:text-ink bg-surface border border-line hover:border-accent min-w-[44px] min-h-[44px] inline-flex items-center justify-center transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path x-show="!menu" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                <path x-show="menu" x-cloak stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Mobile Drawer -->
    <nav x-show="menu" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-2"
         data-lenis-prevent
         class="lg:hidden absolute inset-x-0 top-full z-50 bg-surface/98 backdrop-blur-2xl border-b border-line shadow-2xl max-h-[80vh] overflow-y-auto px-4 py-3 divide-y divide-line" aria-label="Navigasi seluler">
        <div class="space-y-1 pb-3">
            @foreach ([['katalog.index', 'Katalog Mobil'], ['simulasi.form', 'Simulasi Kredit'], ['test-drive.create', 'Booking Test Drive'], ['servis.create', 'Jadwal Servis'], ['promo.index', 'Promo Spesial'], ['cabang.index', 'Jaringan Cabang']] as [$route, $label])
                <a href="{{ route($route) }}" class="block px-3 py-2.5 text-sm font-medium text-ink hover:text-accent hover:bg-surface-2 rounded-xl min-h-[44px] flex items-center">{{ $label }}</a>
            @endforeach
        </div>

        <div class="pt-3">
            @auth
                @unless ($staf)
                    <a href="{{ route('pesanan.index') }}" class="block px-3 py-2.5 text-sm font-medium text-ink hover:text-accent hover:bg-surface-2 rounded-xl min-h-[44px] flex items-center">Pesanan Saya</a>
                @endunless
                <a href="{{ $staf ? route('dashboard') : route('profile.edit') }}" class="flex items-center gap-3 px-3 py-2.5 rounded-xl hover:bg-surface-2 min-h-[44px]">
                    <span class="w-8 h-8 rounded-full bg-gradient-to-br from-accent-strong to-[#9E0E19] text-white font-bold text-xs inline-flex items-center justify-center shrink-0" aria-hidden="true">{{ strtoupper(collect(explode(' ', Auth::user()->name ?? ''))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('')) }}</span>
                    <span class="text-sm font-medium text-ink">{{ Auth::user()->name }} ({{ $staf ? 'Dashboard' : 'Profil' }})</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="block w-full text-left px-3 py-2.5 text-sm font-medium text-accent hover:text-accent hover:bg-surface-2 rounded-xl min-h-[44px] flex items-center">Keluar</button>
                </form>
            @else
                <div class="pt-1">
                    <a href="{{ route('login') }}" class="px-3 py-2.5 text-center text-sm font-medium text-ink bg-surface border border-line rounded-xl min-h-[44px] flex items-center justify-center">Masuk</a>
                </div>
            @endauth
        </div>
    </nav>
</header>
