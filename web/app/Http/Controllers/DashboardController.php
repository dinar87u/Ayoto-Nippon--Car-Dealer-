<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Branch;
use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Lead;
use App\Models\LeasingPartner;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\SparepartOrder;
use App\Models\TestDrive;
use App\Models\Unit;
use App\Models\User;
use App\Models\Variant;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $cabangId = ($user->hasAnyRole(['admin-cabang', 'sales', 'service-advisor', 'mekanik']) && ! $user->hasAnyRole(['superadmin', 'owner', 'pimpinan']) && $user->branch_id)
            ? $user->branch_id
            : null;

        $data = [
            'spkMenunggu' => Order::where('status', 'MenungguVerifikasi')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
            'leadsBaru' => Lead::where('status', 'Baru')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
            'servisAktif' => ServiceBooking::whereIn('status', ['Menunggu', 'Dikonfirmasi', 'Dikerjakan'])->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
            'unitTersedia' => Unit::tersedia()->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
        ];

        if ($user->hasRole('sales')) {
            $data['leadsSaya'] = Lead::where('sales_id', $user->id)->whereNotIn('status', ['Deal', 'Lunas', 'Batal'])->count();
            $data['followupJatuh'] = Lead::where('sales_id', $user->id)->whereNotNull('followup_at')->where('followup_at', '<=', now()->addDay())->count();
        }

        if ($user->hasRole('mekanik')) {
            $lingkupMekanik = fn ($q) => $q
                ->where(fn ($qq) => $qq->where('mekanik_id', $user->id)->orWhereNull('mekanik_id'))
                ->when($cabangId, fn ($qq) => $qq->where('branch_id', $cabangId));

            $data['tugasSaya'] = ServiceBooking::where('mekanik_id', $user->id)->whereIn('status', ['Dikonfirmasi', 'Dikerjakan'])->count();
            $data['mekanikStats'] = [
                'antrean' => ServiceBooking::tap($lingkupMekanik)->whereIn('status', ['Menunggu', 'Dikonfirmasi'])->count(),
                'dikerjakan' => ServiceBooking::where('mekanik_id', $user->id)->where('status', 'Dikerjakan')->count(),
                'selesai' => ServiceBooking::where('mekanik_id', $user->id)->where('status', 'Selesai')->count(),
            ];
            $data['antreanBengkel'] = ServiceBooking::with(['customer', 'details', 'branch'])
                ->tap($lingkupMekanik)
                ->whereIn('status', ['Menunggu', 'Dikonfirmasi', 'Dikerjakan'])
                ->orderByRaw("CASE WHEN mekanik_id = ? THEN 0 ELSE 1 END", [$user->id])
                ->orderBy('jadwal')
                ->limit(8)
                ->get();
        }

        if ($user->hasRole('service-advisor')) {
            $data['antreanBengkel'] = ServiceBooking::with(['customer', 'mekanik', 'branch'])
                ->whereIn('status', ['Menunggu', 'Dikonfirmasi', 'Dikerjakan'])
                ->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))
                ->orderBy('jadwal')
                ->limit(8)
                ->get();

            $data['timMekanik'] = User::role('mekanik')
                ->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))
                ->orderBy('name')
                ->get()
                ->map(fn (User $m) => [
                    'id' => $m->id,
                    'nama' => $m->name,
                    'email' => $m->email,
                    'aktif' => ServiceBooking::where('mekanik_id', $m->id)->whereIn('status', ['Dikonfirmasi', 'Dikerjakan'])->count(),
                    'selesai' => ServiceBooking::where('mekanik_id', $m->id)->where('status', 'Selesai')->count(),
                ]);

            $data['statsBengkel'] = [
                'menunggu' => ServiceBooking::where('status', 'Menunggu')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
                'dikerjakan' => ServiceBooking::whereIn('status', ['Dikonfirmasi', 'Dikerjakan'])->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
                'selesai' => ServiceBooking::where('status', 'Selesai')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
                'orderPartMenunggu' => SparepartOrder::where('status', 'MenungguBayar')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
                'partStokRendah' => \App\Models\Sparepart::where('aktif', true)->where('stok', '<=', 3)->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
            ];
        }

        if (! $user->roles()->exists()) {
            $data['spkSaya'] = Order::whereHas('customer', fn ($q) => $q->where('user_id', $user->id))->count();
        }

        if ($user->hasAnyRole(['owner', 'pimpinan', 'superadmin', 'admin-cabang', 'sales'])) {
            $data['grafikOmzet'] = $this->omzetGrafik($user, $cabangId);
        }

        if ($user->hasAnyRole(['owner', 'pimpinan', 'superadmin'])) {
            $data['ringkasanCabang'] = Branch::where('aktif', true)
                ->orderBy('nama')
                ->get()
                ->map(function (Branch $branch) {
                    $dealOrders = Order::with('unit:id,harga')
                        ->where('branch_id', $branch->id)
                        ->where('status', 'Deal')
                        ->get(['id', 'unit_id']);

                    return [
                        'id' => $branch->id,
                        'kode' => $branch->kode,
                        'nama' => $branch->nama,
                        'unit_siap' => Unit::tersedia()->where('branch_id', $branch->id)->count(),
                        'spk_menunggu' => Order::where('branch_id', $branch->id)->where('status', 'MenungguVerifikasi')->count(),
                        'spk_deal' => $dealOrders->count(),
                        'omzet' => $dealOrders->sum(fn ($o) => (int) ($o->unit->harga ?? 0)),
                        'servis_aktif' => ServiceBooking::where('branch_id', $branch->id)->whereIn('status', ['Menunggu', 'Dikonfirmasi', 'Dikerjakan'])->count(),
                    ];
                });
        }

        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            $data['masterStats'] = [
                'brands' => Brand::where('aktif', true)->count(),
                'models' => CarModel::where('aktif', true)->count(),
                'variants' => Variant::where('aktif', true)->count(),
                'branches' => Branch::where('aktif', true)->count(),
                'leasings' => LeasingPartner::where('aktif', true)->count(),
                'users' => User::whereHas('roles')->count(),
            ];
            $data['aktivitasTerbaru'] = ActivityLog::with('user:id,name')->latest()->limit(6)->get();
        }

        if ($user->hasAnyRole(['sales', 'admin-cabang', 'pimpinan', 'superadmin', 'owner'])) {
            $data['antreanOperasional'] = [
                'testDriveMenunggu' => TestDrive::where('status', 'Menunggu')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
                'orderPartMenunggu' => SparepartOrder::where('status', 'MenungguBayar')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
                'spkDeal' => Order::where('status', 'Deal')->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))->count(),
            ];
        }

        if ($user->hasAnyRole(['sales', 'admin-cabang', 'pimpinan', 'superadmin', 'owner'])) {
            $data['antreanSpk'] = Order::with(['customer', 'unit.variant.carModel', 'branch'])
                ->where('status', 'MenungguVerifikasi')
                ->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))
                ->latest()->limit(5)->get();
            $data['followups'] = Lead::with('customer')
                ->whereNotIn('status', ['Deal', 'Lunas', 'Batal'])
                ->whereNotNull('followup_at')
                ->where('followup_at', '<=', now()->addDay())
                ->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))
                ->when($user->hasRole('sales'), fn ($q) => $q->where('sales_id', $user->id))
                ->orderBy('followup_at')->limit(5)->get();
        }

        return view('dashboard', $data);
    }

    // Grafik disesuaikan per peran (Superadmin/Owner, Pimpinan, Admin Cabang, Sales).
    private function omzetGrafik(User $user, ?int $cabangId): array
    {
        $isSales = $user->hasRole('sales') && ! $user->hasAnyRole(['superadmin', 'owner', 'pimpinan', 'admin-cabang']);
        $namaCabang = $user->branch?->nama ?? 'Cabang';

        if ($user->hasAnyRole(['superadmin', 'owner'])) {
            $labelPeran = 'Pusat Nasional';
            $judulDasar = 'Omzet SPK Deal Seluruh Cabang';
            $peruntukan = 'Memantau total nilai realisasi SPK Deal dari seluruh cabang resmi (Jakarta & Bandung).';
        } elseif ($user->hasRole('pimpinan')) {
            $labelPeran = 'Eksekutif Pimpinan';
            $judulDasar = 'Evaluasi Omzet Penjualan';
            $peruntukan = 'Mengevaluasi tren capaian omzet unit Deal harian, bulanan, dan tahunan.';
        } elseif ($user->hasRole('admin-cabang')) {
            $labelPeran = $namaCabang;
            $judulDasar = 'Omzet SPK Cabang';
            $peruntukan = "Memantau nilai SPK aktif & Deal yang masuk di {$namaCabang}.";
        } else {
            $labelPeran = "Target & Prospek · {$namaCabang}";
            $judulDasar = 'Nilai Pengajuan & Deal SPK';
            $peruntukan = "Memantau pergerakan nilai SPK prospek dan realisasi penjualan unit di {$namaCabang}.";
        }

        $perBulan = [];
        $perHari = [];
        Order::with('unit:id,harga')
            ->when(
                $isSales || $user->hasRole('admin-cabang'),
                fn ($q) => $q->whereNotIn('status', ['Batal', 'Ditolak', 'Expired']),
                fn ($q) => $q->where('status', 'Deal')
            )
            ->when($cabangId, fn ($q) => $q->where('branch_id', $cabangId))
            ->get(['id', 'verified_at', 'created_at', 'unit_id'])
            ->each(function ($o) use (&$perBulan, &$perHari) {
                $waktu = $o->verified_at ?? $o->created_at;
                if (! $waktu) {
                    return;
                }
                $kunciBulan = $waktu->format('Y-m');
                $perBulan[$kunciBulan]['unit'] = ($perBulan[$kunciBulan]['unit'] ?? 0) + 1;
                $perBulan[$kunciBulan]['nominal'] = ($perBulan[$kunciBulan]['nominal'] ?? 0) + (int) ($o->unit->harga ?? 0);

                $kunciHari = $waktu->format('Y-m-d');
                $perHari[$kunciHari]['unit'] = ($perHari[$kunciHari]['unit'] ?? 0) + 1;
                $perHari[$kunciHari]['nominal'] = ($perHari[$kunciHari]['nominal'] ?? 0) + (int) ($o->unit->harga ?? 0);
            });

        $daftarTahun = array_unique(array_merge(
            [now()->format('Y')],
            array_map(fn ($k) => substr($k, 0, 4), array_keys($perBulan))
        ));
        sort($daftarTahun);

        $perTahun = [];
        foreach ($daftarTahun as $tahun) {
            $perTahun[$tahun] = ['label' => $tahun, 'kunci' => $tahun, 'unit' => 0, 'nominal' => 0];
        }
        $bulanan = [];
        foreach ($daftarTahun as $tahun) {
            $items = [];
            for ($b = 1; $b <= 12; $b++) {
                $kunci = sprintf('%s-%02d', $tahun, $b);
                $items[] = [
                    'label' => \Carbon\Carbon::createFromFormat('Y-m', $kunci)->translatedFormat('M'),
                    'unit' => (int) ($perBulan[$kunci]['unit'] ?? 0),
                    'nominal' => (int) ($perBulan[$kunci]['nominal'] ?? 0),
                ];
                $perTahun[$tahun]['unit'] += $items[$b - 1]['unit'];
                $perTahun[$tahun]['nominal'] += $items[$b - 1]['nominal'];
            }
            $bulanan[$tahun] = ['items' => $items, 'skala' => $this->skalaBagus(array_column($items, 'nominal'))];
        }

        $harian = [];
        for ($d = 13; $d >= 0; $d--) {
            $tgl = now()->subDays($d);
            $kunci = $tgl->format('Y-m-d');
            $harian[] = [
                'label' => $tgl->translatedFormat('d M'),
                'unit' => (int) ($perHari[$kunci]['unit'] ?? 0),
                'nominal' => (int) ($perHari[$kunci]['nominal'] ?? 0),
            ];
        }

        return [
            'label_peran' => $labelPeran,
            'judul_dasar' => $judulDasar,
            'peruntukan' => $peruntukan,
            'tahun' => ['items' => array_values($perTahun), 'skala' => $this->skalaBagus(array_column($perTahun, 'nominal'))],
            'bulanan' => $bulanan,
            'harian' => ['items' => $harian, 'skala' => $this->skalaBagus(array_column($harian, 'nominal'))],
        ];
    }

    private function skalaBagus(array $nominal): float
    {
        $maks = max(1, max($nominal ?: [0]));
        $mag = pow(10, (int) floor(log10($maks)));
        foreach ([1, 2, 2.5, 5, 10] as $kelipatan) {
            if ($maks <= $kelipatan * $mag) {
                return $kelipatan * $mag;
            }
        }

        return $mag * 10;
    }
}
