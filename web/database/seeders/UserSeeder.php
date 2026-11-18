<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\LeasingPartner;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\Sparepart;
use App\Models\SparepartOrder;
use App\Models\SparepartOrderItem;
use App\Models\TestDrive;
use App\Models\Unit;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $jkt = Branch::where('kode', 'JKT-01')->first() ?? Branch::first();
        $bdg = Branch::where('kode', 'BDG-01')->first() ?? Branch::skip(1)->first() ?? $jkt;

        $users = [
            // Owner (pemilik dealer, akses lintas cabang)
            ['name' => 'Pemilik Ayoto Nippon', 'email' => 'owner@ayotonippon.com', 'role' => 'owner', 'branch_id' => null],

            // Super Admin
            ['name' => 'Super Admin', 'email' => 'admin@ayotonippon.com', 'role' => 'superadmin', 'branch_id' => null],

            // Sales
            ['name' => 'Raihan', 'email' => 'raihan@ayotonippon.com', 'role' => 'sales', 'branch_id' => $jkt?->id],
            ['name' => 'Dinar', 'email' => 'dinar@ayotonippon.com', 'role' => 'sales', 'branch_id' => $jkt?->id],
            ['name' => 'Fauzan', 'email' => 'fauzan@ayotonippon.com', 'role' => 'sales', 'branch_id' => $bdg?->id],

            // Mekanik
            ['name' => 'Joko', 'email' => 'joko@ayotonippon.com', 'role' => 'mekanik', 'branch_id' => $jkt?->id],
            ['name' => 'Juki', 'email' => 'juki@ayotonippon.com', 'role' => 'mekanik', 'branch_id' => $bdg?->id],

            // Admin Cabang
            ['name' => 'Owprabo', 'email' => 'owprabo@ayotonippon.com', 'role' => 'admin-cabang', 'branch_id' => $jkt?->id],
            ['name' => 'Okwijo', 'email' => 'okwijo@ayotonippon.com', 'role' => 'admin-cabang', 'branch_id' => $bdg?->id],

            // Pimpinan
            ['name' => 'Yunnita Rohmalia', 'email' => 'yunnita@ayotonippon.com', 'role' => 'pimpinan', 'branch_id' => $jkt?->id],
            ['name' => 'Rian Pratama', 'email' => 'rian.pimpinan@ayotonippon.com', 'role' => 'pimpinan', 'branch_id' => $bdg?->id],

            // Service Advisor
            ['name' => 'Rusdi', 'email' => 'rusdi@ayotonippon.com', 'role' => 'service-advisor', 'branch_id' => $jkt?->id],
            ['name' => 'Gilang Ramadhan', 'email' => 'gilang.sa@ayotonippon.com', 'role' => 'service-advisor', 'branch_id' => $bdg?->id],
        ];

        User::whereIn('email', ['hendra.pimpinan@ayotonippon.com', 'doni.sa@ayotonippon.com'])->delete();

        foreach ($users as $data) {
            $user = User::updateOrCreate(
                ['email' => $data['email']],
                [
                    'name' => $data['name'],
                    'password' => 'password',
                    'branch_id' => $data['branch_id'],
                ]
            );
            $user->syncRoles([$data['role']]);
        }

        // Akun Pelanggan Demo beserta Transaksi Awal (SPK, Test Drive, Servis, Sparepart, Lead)
        $customers = [
            [
                'name' => 'Zaki',
                'email' => 'zaki@gmail.com',
                'nik' => '3171010101010001',
                'telp' => '081234567801',
                'alamat' => 'Jl. Sudirman No. 12, Jakarta Selatan',
                'branch' => $jkt,
                'no_spk' => 'SPK-2026-9001',
                'metode' => 'Transfer',
                'status_spk' => 'Deal',
                'no_svc' => 'SVC-2026-9001',
                'nopol' => 'B 1001 ZKI',
                'km' => 10000,
                'keluhan' => 'Servis berkala 10.000 km & cek rem',
                'no_spo' => 'SPO-2026-9001',
            ],
            [
                'name' => 'Supri',
                'email' => 'supri@gmail.com',
                'nik' => '3273020202020002',
                'telp' => '081234567802',
                'alamat' => 'Jl. Dago No. 45, Bandung',
                'branch' => $bdg,
                'no_spk' => 'SPK-2026-9002',
                'metode' => 'Cash',
                'status_spk' => 'MenungguBayar',
                'no_svc' => 'SVC-2026-9002',
                'nopol' => 'D 2002 SPR',
                'km' => 15000,
                'keluhan' => 'Ganti oli mesin dan cek AC kurang dingin',
                'no_spo' => 'SPO-2026-9002',
            ],
            [
                'name' => 'Valen',
                'email' => 'valen@gmail.com',
                'nik' => '3174030303030003',
                'telp' => '081234567803',
                'alamat' => 'Jl. Kemang Raya No. 88, Jakarta Selatan',
                'branch' => $jkt,
                'no_spk' => 'SPK-2026-9003',
                'metode' => 'Kredit',
                'status_spk' => 'MenungguBayar',
                'no_svc' => 'SVC-2026-9003',
                'nopol' => 'B 3003 VLN',
                'km' => 20000,
                'keluhan' => 'Cek kaki-kaki bunyi saat melewati jalan rusak',
                'no_spo' => 'SPO-2026-9003',
            ],
        ];

        $leasing = LeasingPartner::first();
        $salesJkt = User::where('email', 'raihan@ayotonippon.com')->first();

        foreach ($customers as $idx => $c) {
            $u = User::updateOrCreate(
                ['email' => $c['email']],
                ['name' => $c['name'], 'password' => '12345678', 'branch_id' => null]
            );
            $u->syncRoles(['customer']);

            $cust = Customer::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'nama' => $c['name'],
                    'nik' => $c['nik'],
                    'telp' => $c['telp'],
                    'alamat' => $c['alamat'],
                ]
            );

            $branch = $c['branch'];
            if (! $branch) {
                continue;
            }

            if ($cust->orders()->count() === 0) {
                $unit = Unit::where('branch_id', $branch->id)->skip($idx)->first() ?? Unit::first();
                if ($unit) {
                    Order::firstOrCreate(
                        ['no_spk' => $c['no_spk']],
                        [
                            'customer_id' => $cust->id,
                            'unit_id' => $unit->id,
                            'branch_id' => $branch->id,
                            'metode' => $c['metode'],
                            'leasing_id' => $c['metode'] === 'Kredit' ? $leasing?->id : null,
                            'dp_rencana' => 0,
                            'tenor' => $c['metode'] === 'Kredit' ? 36 : null,
                            'booking_fee' => 5000000,
                            'status' => $c['status_spk'],
                            'deadline_at' => now()->addDay(),
                            'verified_at' => $c['status_spk'] === 'Deal' ? now() : null,
                        ]
                    );
                }
            }

            if ($cust->testDrives()->count() === 0) {
                $variant = Variant::skip($idx)->first() ?? Variant::first();
                if ($variant) {
                    TestDrive::create([
                        'customer_id' => $cust->id,
                        'branch_id' => $branch->id,
                        'variant_id' => $variant->id,
                        'jadwal' => now()->addDays($idx + 2)->setTime(10, 0),
                        'status' => 'Menunggu',
                    ]);
                }
            }

            if ($cust->services()->count() === 0) {
                ServiceBooking::firstOrCreate(
                    ['no_svc' => $c['no_svc']],
                    [
                        'customer_id' => $cust->id,
                        'branch_id' => $branch->id,
                        'nopol' => $c['nopol'],
                        'km' => $c['km'],
                        'keluhan' => $c['keluhan'],
                        'jadwal' => now()->addDays($idx + 3)->setTime(13, 0),
                        'status' => 'Menunggu',
                        'estimasi_biaya' => 0,
                    ]
                );
            }

            if ($cust->sparepartOrders()->count() === 0) {
                $part = Sparepart::where('branch_id', $branch->id)->first();
                if ($part) {
                    $spo = SparepartOrder::firstOrCreate(
                        ['no_spo' => $c['no_spo']],
                        [
                            'customer_id' => $cust->id,
                            'branch_id' => $branch->id,
                            'total' => $part->harga,
                            'status' => 'MenungguBayar',
                            'deadline_at' => now()->addDay(),
                        ]
                    );
                    if ($spo->items()->count() === 0) {
                        SparepartOrderItem::create([
                            'sparepart_order_id' => $spo->id,
                            'sparepart_id' => $part->id,
                            'qty' => 1,
                            'harga_satuan' => $part->harga,
                            'subtotal' => $part->harga,
                        ]);
                    }
                }
            }

            if ($cust->leads()->count() === 0) {
                Lead::create([
                    'customer_id' => $cust->id,
                    'sales_id' => $idx === 0 ? $salesJkt?->id : null,
                    'sumber' => 'Web',
                    'status' => $c['status_spk'] === 'Deal' ? 'Deal' : 'Baru',
                    'kebutuhan_text' => 'Pemesanan unit kendaraan cabang ' . $branch->nama,
                ]);
            }
        }
    }
}
