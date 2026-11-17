<x-app-layout>
    @php
        $roleDipilih = old('role', $user->roles->first()?->name ?? 'sales');
        $metaRole = [
            'superadmin' => ['Super Admin', 'Akses penuh ke seluruh sistem, master data pusat, semua cabang, dan manajemen user.', false],
            'owner' => ['Owner', 'Akses penuh manajemen, laporan lintas cabang, dan master data.', false],
            'pimpinan' => ['Pimpinan Cabang', 'Monitoring penjualan, bengkel, serta laporan dan ekspor performa cabang.', true],
            'admin-cabang' => ['Admin Cabang', 'Verifikasi pembayaran SPK, penerbitan faktur/BAST, stok unit, harga OTR, dan operasional cabang.', true],
            'sales' => ['Sales Consultant', 'Follow-up leads, jadwal test drive, pantau pesanan SPK, dan lihat stok unit cabang.', true],
            'service-advisor' => ['Service Advisor', 'Terima booking servis, buat estimasi, kelola stok sparepart, dan order part cabang.', true],
            'mekanik' => ['Mekanik', 'Mengerjakan unit servis yang ditugaskan, tambah rincian jasa/part, dan tandai selesai.', true],
            'customer' => ['Pelanggan', 'Akun pembeli umum tanpa akses dashboard admin.', false],
        ];
    @endphp

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <p class="text-[11px] font-bold uppercase tracking-[0.18em] text-accent">Pengaturan Akses</p>
                <h2 class="mt-0.5 text-2xl font-extrabold text-ink tracking-tight">
                    {{ $user->exists ? 'Ubah Pengguna: '.$user->name : 'Tambah Pengguna Baru' }}
                </h2>
            </div>
            <a href="{{ route('admin.users.index') }}" class="text-xs font-semibold text-muted hover:text-ink transition-colors">
                &larr; Kembali ke Daftar Users
            </a>
        </div>
    </x-slot>

    <div class="py-6 w-full px-4 sm:px-6 lg:px-8 max-w-4xl">
        <form action="{{ $user->exists ? route('admin.users.update', $user) : route('admin.users.store') }}" method="POST"
              x-data="{
                  role: '{{ $roleDipilih }}',
                  meta: @js($metaRole),
                  get wajibCabang() {
                      return this.meta[this.role] ? this.meta[this.role][2] : false;
                  },
                  get infoRole() {
                      return this.meta[this.role] ? this.meta[this.role][1] : '';
                  }
              }"
              class="bg-surface border border-line p-6 sm:p-8 rounded-2xl space-y-6">
            @csrf
            @if ($user->exists)
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="name" class="block text-xs font-bold uppercase tracking-wider text-muted">Nama Lengkap</label>
                    <input id="name" name="name" value="{{ old('name', $user->name) }}" required placeholder="Contoh: Budi Santoso"
                        class="mt-1.5 w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5">
                </div>

                <div>
                    <label for="email" class="block text-xs font-bold uppercase tracking-wider text-muted">Alamat Email</label>
                    <input id="email" type="email" name="email" value="{{ old('email', $user->email) }}" required placeholder="nama@ayotonippon.com"
                        class="mt-1.5 w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5">
                </div>

                <div>
                    <label for="password" class="block text-xs font-bold uppercase tracking-wider text-muted">
                        Password {{ $user->exists ? '(Opsional)' : '(Minimal 8 Karakter)' }}
                    </label>
                    <input id="password" type="password" name="password" @unless($user->exists) required @endunless
                        placeholder="{{ $user->exists ? 'Kosongkan jika tidak ingin diganti' : 'Minimal 8 karakter' }}"
                        class="mt-1.5 w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5">
                </div>

                <div>
                    <label for="telp" class="block text-xs font-bold uppercase tracking-wider text-muted">Nomor Telepon / WA</label>
                    <input id="telp" name="telp" value="{{ old('telp', $user->telp) }}" placeholder="0812..."
                        class="mt-1.5 w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5">
                </div>

                <div>
                    <label for="role" class="block text-xs font-bold uppercase tracking-wider text-muted">Role &amp; Hak Akses</label>
                    <select id="role" name="role" required x-model="role"
                        class="mt-1.5 w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm font-medium focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5">
                        @foreach ($roles as $role)
                            <option value="{{ $role->name }}" @selected($roleDipilih === $role->name)>
                                {{ $metaRole[$role->name][0] ?? $role->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label for="branch_id" class="block text-xs font-bold uppercase tracking-wider text-muted">
                        Penempatan Cabang
                        <span x-show="wajibCabang" class="text-accent">* Wajib</span>
                        <span x-show="!wajibCabang" class="font-normal normal-case">(Opsional)</span>
                    </label>
                    <select id="branch_id" name="branch_id" :required="wajibCabang"
                        class="mt-1.5 w-full bg-surface-2/60 dark:bg-surface border border-line rounded-xl text-ink text-sm focus:border-accent focus:ring-1 focus:ring-accent min-h-[44px] px-3.5">
                        <option value="">Pusat / Tanpa Cabang Khusus</option>
                        @foreach ($branches as $branch)
                            <option value="{{ $branch->id }}" @selected((string) old('branch_id', $user->branch_id) === (string) $branch->id)>{{ $branch->nama }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="p-4 rounded-xl bg-surface-2/60 border border-line text-xs text-muted">
                <strong class="text-ink block mb-0.5">Ringkasan Hak Akses Role Terpilih:</strong>
                <span x-text="infoRole"></span>
            </div>

            <x-input-error :messages="$errors->all()" class="text-xs text-accent" />

            <div class="pt-4 border-t border-line flex items-center justify-end gap-3">
                <a href="{{ route('admin.users.index') }}" class="px-5 py-2.5 text-sm font-semibold text-muted hover:text-ink border border-line rounded-xl min-h-[44px] inline-flex items-center">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 text-sm font-bold text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl min-h-[44px] transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
