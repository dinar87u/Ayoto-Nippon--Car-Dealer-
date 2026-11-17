<x-app-layout>
    @php
        $labelRole = [
            'superadmin' => 'Super Admin',
            'owner' => 'Owner',
            'pimpinan' => 'Pimpinan Cabang',
            'admin-cabang' => 'Admin Cabang',
            'sales' => 'Sales Consultant',
            'service-advisor' => 'Service Advisor',
            'mekanik' => 'Mekanik',
            'customer' => 'Pelanggan',
        ];
        $badgeRole = [
            'superadmin' => 'bg-accent-strong/10 text-accent border-accent/30',
            'owner' => 'bg-accent-strong/10 text-accent border-accent/30',
            'pimpinan' => 'bg-indigo-500/10 text-indigo-700 dark:text-indigo-300 border-indigo-500/25',
            'admin-cabang' => 'bg-sky-500/10 text-sky-700 dark:text-sky-300 border-sky-500/25',
            'sales' => 'bg-emerald-500/10 text-emerald-700 dark:text-emerald-300 border-emerald-500/25',
            'service-advisor' => 'bg-amber-500/10 text-amber-700 dark:text-amber-300 border-amber-500/25',
            'mekanik' => 'bg-surface-2 text-ink border-line',
            'customer' => 'bg-surface-2 text-muted border-line',
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">Pengaturan Akses</p>
                <h2 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">Users &amp; Staf Dealer</h2>
            </div>
            <a href="{{ route('admin.users.create') }}"
               class="inline-flex items-center justify-center px-4 py-2.5 text-xs font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[42px] transition-colors">
                + Tambah User
            </a>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 space-y-6">
        @if (session('sukses'))
            <div class="p-4 text-sm font-medium text-emerald-700 dark:text-emerald-300 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                {{ session('sukses') }}
            </div>
        @endif

        <!-- Ringkasan Akun -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-surface border border-line rounded-2xl p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Total Pengguna</p>
                <p class="mt-1.5 text-2xl font-extrabold text-ink tabular-nums">{{ number_format($ringkasan['total'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface border border-line rounded-2xl p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Staf &amp; Manajemen</p>
                <p class="mt-1.5 text-2xl font-extrabold text-ink tabular-nums">{{ number_format($ringkasan['staf'], 0, ',', '.') }}</p>
            </div>
            <div class="bg-surface border border-line rounded-2xl p-5">
                <p class="text-xs font-semibold uppercase tracking-wider text-muted">Pelanggan Terdaftar</p>
                <p class="mt-1.5 text-2xl font-extrabold text-ink tabular-nums">{{ number_format($ringkasan['pelanggan'], 0, ',', '.') }}</p>
            </div>
        </div>

        <!-- Filter & Pencarian -->
        <div class="bg-surface border border-line rounded-2xl p-4 sm:p-5">
            <form action="{{ route('admin.users.index') }}" method="GET" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-12 gap-3 items-end">
                <div class="lg:col-span-5">
                    <label for="q" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Cari Pengguna</label>
                    <input id="q" type="text" name="q" value="{{ request('q') }}" placeholder="Nama, email, atau nomor telepon..."
                        class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink placeholder-muted text-sm focus:border-accent focus:ring-1 focus:ring-accent h-[42px] px-3.5">
                </div>

                <div class="lg:col-span-3">
                    <label for="role" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Filter Role</label>
                    <select id="role" name="role" onchange="this.form.submit()"
                        class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent h-[42px] px-3">
                        <option value="">Semua Role</option>
                        <option value="staf" @selected(request('role') === 'staf')>Semua Staf Dealer</option>
                        <option value="pelanggan" @selected(request('role') === 'pelanggan')>Pelanggan Saja</option>
                        @foreach ($roles as $r)
                            <option value="{{ $r->name }}" @selected(request('role') === $r->name)>{{ $labelRole[$r->name] ?? $r->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2">
                    <label for="branch_id" class="block text-[11px] font-bold uppercase tracking-wider text-muted mb-1.5">Cabang</label>
                    <select id="branch_id" name="branch_id" onchange="this.form.submit()"
                        class="w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent h-[42px] px-3">
                        <option value="">Semua Cabang</option>
                        @foreach ($branches as $b)
                            <option value="{{ $b->id }}" @selected((string) request('branch_id') === (string) $b->id)>{{ $b->nama }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="lg:col-span-2 flex gap-2">
                    <button type="submit" class="flex-1 h-[42px] px-4 text-xs font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl transition-colors">
                        Cari
                    </button>
                    @if (request('q') || request('role') || request('branch_id'))
                        <a href="{{ route('admin.users.index') }}" class="h-[42px] px-3 inline-flex items-center justify-center text-xs font-semibold text-muted hover:text-ink border border-line rounded-xl">
                            Reset
                        </a>
                    @endif
                </div>
            </form>
        </div>

        <!-- Tabel Users -->
        <div class="bg-surface border border-line rounded-2xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-line bg-surface-2/60 text-left text-[11px] uppercase tracking-wider text-muted">
                            <th class="px-5 py-3.5 font-bold">Pengguna</th>
                            <th class="px-4 py-3.5 font-bold">Email</th>
                            <th class="px-4 py-3.5 font-bold">Role &amp; Hak Akses</th>
                            <th class="px-4 py-3.5 font-bold">Penempatan Cabang</th>
                            <th class="px-5 py-3.5 font-bold text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-line">
                        @forelse ($users as $user)
                            @php
                                $roleName = $user->roles->first()?->name ?? 'customer';
                                $inisial = strtoupper(collect(explode(' ', $user->name))->map(fn ($p) => mb_substr($p, 0, 1))->take(2)->implode(''));
                            @endphp
                            <tr class="hover:bg-surface-2/40 transition-colors">
                                <td class="px-5 py-3.5">
                                    <div class="flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-full bg-surface-2 border border-line text-ink font-bold text-xs inline-flex items-center justify-center shrink-0">
                                            {{ $inisial }}
                                        </span>
                                        <div class="min-w-0">
                                            <span class="block font-semibold text-ink truncate">{{ $user->name }}</span>
                                            <span class="block text-xs text-muted tabular-nums">{{ $user->telp ?: 'Tanpa no. telp' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3.5 text-muted">{{ $user->email }}</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-semibold border {{ $badgeRole[$roleName] ?? 'bg-surface-2 text-ink border-line' }}">
                                        {{ $labelRole[$roleName] ?? $roleName }}
                                    </span>
                                </td>
                                <td class="px-4 py-3.5 text-ink">
                                    {{ $user->branch?->nama ?? (in_array($roleName, ['superadmin', 'owner']) ? 'Semua Cabang (Pusat)' : '-') }}
                                </td>
                                <td class="px-5 py-3.5 text-right">
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold text-ink border border-line hover:border-accent hover:text-accent transition-colors">
                                        Ubah
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-5 py-8 text-center text-sm text-muted">Tidak ada pengguna yang cocok dengan filter.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div>{{ $users->links() }}</div>
    </div>
</x-app-layout>
