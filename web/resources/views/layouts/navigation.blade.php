<div x-show="open" @click="open = false" x-cloak class="fixed inset-0 z-40 bg-black/50 lg:hidden" aria-hidden="true"></div>
<aside :class="(rail ? 'w-20 ' : 'w-72 ') + (open ? 'translate-x-0' : '-translate-x-full lg:translate-x-0')" style="background:#000" class="sidebar-shell w-72 -translate-x-full lg:translate-x-0 fixed lg:sticky lg:top-0 z-50 h-screen shrink-0 bg-black text-gray-200 flex flex-col overflow-hidden" aria-label="Navigasi admin">
    <div class="px-3 py-3 flex items-center gap-2 border-b border-white/10">
        <div class="flex items-center gap-2 min-w-0">
            <x-application-logo class="block h-9 w-auto bg-white rounded-lg shrink-0" />
            <span x-show="!rail" class="font-bold text-white truncate">Ayoto Nippon</span>
        </div>
    </div>
    @php
        $stafNav = Auth::user()?->hasAnyRole(['sales', 'admin-cabang', 'service-advisor', 'mekanik', 'pimpinan', 'superadmin', 'owner']) ?? false;
    @endphp
    <nav class="sidebar-track flex-1 overflow-y-auto px-3 py-4 space-y-5 text-sm">
        @if ($stafNav)
        <a href="{{ route('dashboard') }}" title="Dashboard" aria-current="{{ request()->routeIs('dashboard') ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs('dashboard') ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
            @if (request()->routeIs('dashboard'))
                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
            @endif
            <x-nav-icon name="grid" />
            <span x-show="!rail">Dashboard</span>
        </a>
        @else
        <div x-data="{ buka: true }">
            <button type="button" @click="buka = !buka" :aria-expanded="buka" class="w-full flex items-center justify-between px-3 mb-1 min-h-[44px] rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                <span x-show="!rail" class="text-[11px] font-semibold tracking-widest text-gray-400">AKUN SAYA</span>
                <x-nav-icon name="chevron" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': buka }" />
            </button>
            <div x-show="buka" class="space-y-0.5">
                @foreach ([
                    ['profile.edit', 'profile.edit', 'Dashboard Akun', 'grid'],
                    ['pesanan.index', 'pesanan.*', 'Pesanan Saya', 'doc'],
                    ['profile.data-diri', 'profile.data-diri', 'Data Diri', 'user'],
                ] as [$route, $pola, $label, $ikon])
                    <a href="{{ route($route) }}" title="{{ $label }}" aria-current="{{ request()->routeIs($pola) ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs($pola) ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                        @if (request()->routeIs($pola))
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                        @endif
                        <x-nav-icon :name="$ikon" />
                        <span x-show="!rail">{{ $label }}</span>
                    </a>
                @endforeach
            </div>
        </div>
        @endif
        @hasanyrole('sales|admin-cabang|pimpinan|superadmin|owner')
            <div x-data="{ buka: true }">
                <button type="button" @click="buka = !buka" :aria-expanded="buka" class="w-full flex items-center justify-between px-3 mb-1 min-h-[44px] rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                    <span x-show="!rail" class="text-[11px] font-semibold tracking-widest text-gray-400">PENJUALAN</span>
                    <x-nav-icon name="chevron" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': buka }" />
                </button>
                <div x-show="buka" class="space-y-0.5">
                    @php
                        $menuPenjualan = [
                            ['admin.pesanan.index', 'admin.pesanan.*', 'Pesanan', 'doc'],
                            ['admin.leads.index', 'admin.leads.*', 'Leads', 'users'],
                            ['admin.units.index', 'admin.units.*', 'Stok Unit', 'box'],
                            ['admin.test-drives.index', 'admin.test-drives.*', 'Test Drive', 'car'],
                        ];
                    @endphp
                    @hasanyrole('admin-cabang|pimpinan|superadmin|owner')
                        <a href="{{ route('admin.verify.index') }}" title="Verifikasi SPK" aria-current="{{ request()->routeIs('admin.verify.*') ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs('admin.verify.*') ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                            @if (request()->routeIs('admin.verify.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                            @endif
                            <x-nav-icon name="check" />
                            <span x-show="!rail">Verifikasi SPK</span>
                        </a>
                    @endhasanyrole
                    @foreach ($menuPenjualan as [$route, $pola, $label, $ikon])
                        <a href="{{ route($route) }}" title="{{ $label }}" aria-current="{{ request()->routeIs($pola) ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs($pola) ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                            @if (request()->routeIs($pola))
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                            @endif
                            <x-nav-icon :name="$ikon" />
                            <span x-show="!rail">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endhasanyrole
        @hasanyrole('service-advisor|mekanik|admin-cabang|pimpinan|superadmin|owner')
            <div x-data="{ buka: true }">
                <button type="button" @click="buka = !buka" :aria-expanded="buka" class="w-full flex items-center justify-between px-3 mb-1 min-h-[44px] rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                    <span x-show="!rail" class="text-[11px] font-semibold tracking-widest text-gray-400">BENGKEL</span>
                    <x-nav-icon name="chevron" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': buka }" />
                </button>
                <div x-show="buka" class="space-y-0.5">
                    <a href="{{ route('admin.services.index') }}" title="Servis" aria-current="{{ request()->routeIs('admin.services.*') ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs('admin.services.*') ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                        @if (request()->routeIs('admin.services.*'))
                            <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                        @endif
                        <x-nav-icon name="wrench" />
                        <span x-show="!rail">Servis</span>
                    </a>
                    @hasanyrole('service-advisor|admin-cabang|pimpinan|superadmin|owner')
                        <a href="{{ route('admin.spareparts.index') }}" title="Sparepart" aria-current="{{ request()->routeIs('admin.spareparts.*') ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs('admin.spareparts.*') ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                            @if (request()->routeIs('admin.spareparts.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                            @endif
                            <x-nav-icon name="box" />
                            <span x-show="!rail">Sparepart</span>
                        </a>
                        <a href="{{ route('admin.sparepart-orders.index') }}" title="Order Part" aria-current="{{ request()->routeIs('admin.sparepart-orders.*') ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs('admin.sparepart-orders.*') ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                            @if (request()->routeIs('admin.sparepart-orders.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                            @endif
                            <x-nav-icon name="clipboard" />
                            <span x-show="!rail">Order Part</span>
                        </a>
                    @endhasanyrole
                    @role('service-advisor')
                        <a href="{{ route('admin.laporan.show', 'servis') }}" title="Laporan Servis" aria-current="{{ request()->routeIs('admin.laporan.*') ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs('admin.laporan.*') ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                            @if (request()->routeIs('admin.laporan.*'))
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                            @endif
                            <x-nav-icon name="chart" />
                            <span x-show="!rail">Laporan Servis</span>
                        </a>
                    @endrole
                </div>
            </div>
        @endhasanyrole
        @hasanyrole('superadmin|pimpinan|admin-cabang|owner')
            <div x-data="{ buka: true }">
                <button type="button" @click="buka = !buka" :aria-expanded="buka" class="w-full flex items-center justify-between px-3 mb-1 min-h-[44px] rounded-lg focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60">
                    <span x-show="!rail" class="text-[11px] font-semibold tracking-widest text-gray-400">KELOLA</span>
                    <x-nav-icon name="chevron" class="w-4 h-4 transition-transform" ::class="{ 'rotate-180': buka }" />
                </button>
                <div x-show="buka" class="space-y-0.5">
                    @hasanyrole('superadmin|owner')
                        @foreach ([['admin.brands.index', 'admin.brands.*', 'Brand', 'tag'], ['admin.models.index', 'admin.models.*', 'Model', 'car'], ['admin.leasings.index', 'admin.leasings.*', 'Leasing', 'bank'], ['admin.branches.index', 'admin.branches.*', 'Cabang', 'pin'], ['admin.hero-slides.index', 'admin.hero-slides.*', 'Hero', 'image'], ['admin.users.index', 'admin.users.*', 'Users', 'user']] as [$route, $pola, $label, $ikon])
                            <a href="{{ route($route) }}" title="{{ $label }}" aria-current="{{ request()->routeIs($pola) ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs($pola) ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                                @if (request()->routeIs($pola))
                                    <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                                @endif
                                <x-nav-icon :name="$ikon" />
                                <span x-show="!rail">{{ $label }}</span>
                            </a>
                        @endforeach
                    @endhasanyrole
                    @foreach ([['admin.promos.index', 'admin.promos.*', 'Promo', 'percent'], ['admin.contents.index', 'admin.contents.*', 'Konten', 'doc'], ['admin.laporan.index', 'admin.laporan.*', 'Laporan', 'chart']] as [$route, $pola, $label, $ikon])
                        <a href="{{ route($route) }}" title="{{ $label }}" aria-current="{{ request()->routeIs($pola) ? 'page' : 'false' }}" class="relative flex items-center gap-3 px-3 py-2.5 rounded-lg min-h-[44px] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-white/60 {{ request()->routeIs($pola) ? 'bg-white/10 font-semibold text-white' : 'hover:bg-white/10 hover:text-white' }}">
                            @if (request()->routeIs($pola))
                                <span class="absolute left-0 top-2 bottom-2 w-1 rounded-full bg-[#C1121F]"></span>
                            @endif
                            <x-nav-icon :name="$ikon" />
                            <span x-show="!rail">{{ $label }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
        @endhasanyrole
    </nav>
    <div class="px-3 py-3 border-t border-white/10 flex items-center gap-3">
        <a href="{{ route('profile.edit') }}" title="Profil" class="flex items-center gap-3 flex-1 min-w-0 rounded-lg min-h-[44px]">
            <span class="w-10 h-10 rounded-full bg-[#C1121F] text-white font-bold text-sm inline-flex items-center justify-center shrink-0" aria-hidden="true">{{ strtoupper(collect(explode(' ', Auth::user()->name ?? ''))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode('')) }}</span>
            <span x-show="!rail" class="flex-1 min-w-0">
                <span class="block font-medium text-white truncate">{{ Auth::user()->name }}</span>
                <span class="block text-xs text-gray-400 capitalize">{{ Auth::user()?->getRoleNames()->first() ?? '-' }}</span>
            </span>
        </a>
        <form method="POST" action="{{ route('logout') }}" x-show="!rail" class="shrink-0">
            @csrf
            <button type="submit" title="Keluar" aria-label="Keluar" class="p-2 rounded-lg text-gray-300 hover:text-white hover:bg-white/10 min-w-[44px] min-h-[44px] inline-flex items-center justify-center">
                <svg class="w-5 h-5 shrink-0" width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" aria-hidden="true"><path d="M14 4H5v16h9M11 8l4 4-4 4M15 12H3" stroke-linecap="round" stroke-linejoin="round"/></svg>
            </button>
        </form>
    </div>
</aside>
<script>
    (function () {
        var nav = document.querySelector('aside[aria-label="Navigasi admin"] .sidebar-track');
        if (!nav) return;
        var key = 'ayoto-admin-sidebar-scroll';
        try {
            var saved = sessionStorage.getItem(key);
            if (saved !== null) {
                nav.scrollTop = parseInt(saved, 10) || 0;
            } else {
                var active = nav.querySelector('[aria-current="page"]');
                if (active) active.scrollIntoView({ block: 'nearest' });
            }
            nav.addEventListener('scroll', function () {
                try { sessionStorage.setItem(key, String(nav.scrollTop)); } catch (e) {}
            }, { passive: true });
            nav.querySelectorAll('a[href]').forEach(function (link) {
                link.addEventListener('click', function () {
                    try { sessionStorage.setItem(key, String(nav.scrollTop)); } catch (e) {}
                });
            });
        } catch (e) {}
    })();
</script>
