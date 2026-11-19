<?php

namespace App\Http\Controllers;

use App\Jobs\SendMailJob;
use App\Jobs\SendWaJob;
use App\Support\Notifikasi;
use App\Models\Branch;
use App\Models\Color;
use App\Models\Customer;
use App\Models\Order;
use App\Models\ServiceBooking;
use App\Models\Sparepart;
use App\Models\Unit;
use App\Models\User;
use App\Models\Variant;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    public const SLOT_LIMIT = 5;

    public function index(Request $request)
    {
        $user = $request->user();

        // Lingkup data mengikuti peran: mekanik = tugasnya atau antrean cabang belum ditugaskan, SA/admin-cabang = cabangnya.
        $lingkup = ServiceBooking::query()
            ->when($user->hasRole('mekanik'), fn ($q) => $q->where(fn ($qq) => $qq->where('mekanik_id', $user->id)->orWhereNull('mekanik_id')))
            ->when($user->hasAnyRole(['service-advisor', 'admin-cabang', 'mekanik']) && $user->branch_id,
                fn ($q) => $q->where('branch_id', $user->branch_id));

        $bookings = (clone $lingkup)
            ->with(['customer', 'branch', 'mekanik'])
            ->when(in_array($request->status, ['Menunggu', 'Dikonfirmasi', 'Dikerjakan', 'Selesai', 'Batal'], true),
                fn ($q) => $q->where('status', $request->status))
            ->latest()->paginate(15)->withQueryString();

        // Hitungan per status untuk chip filter (ikut lingkup peran).
        $jumlah = collect(['Menunggu', 'Dikonfirmasi', 'Dikerjakan', 'Selesai', 'Batal'])
            ->mapWithKeys(fn ($s) => [$s => (clone $lingkup)->where('status', $s)->count()])
            ->put('semua', (clone $lingkup)->count());

        return view('admin.services.index', compact('bookings', 'jumlah'));
    }

    public function show(Request $request, ServiceBooking $service)
    {
        $this->otorisasiServis($request->user(), $service);
        $service->load(['customer', 'branch', 'mekanik', 'details.sparepart']);
        $mekaniks = User::role('mekanik')
            ->when(! $request->user()->hasAnyRole(['pimpinan', 'superadmin', 'owner']),
                fn ($q) => $q->where('branch_id', $service->branch_id))
            ->orderBy('name')->get();
        $parts = Sparepart::with('branch')
            ->where('aktif', true)
            ->where('branch_id', $service->branch_id)
            ->orderBy('nama')
            ->get();

        return view('admin.services.show', compact('service', 'mekaniks', 'parts'));
    }

    // Mekanik hanya tugasnya sendiri, SA/admin-cabang hanya cabangnya. Manajemen bebas.
    private function otorisasiServis($user, ServiceBooking $service): void
    {
        if ($user->hasAnyRole(['pimpinan', 'superadmin', 'owner'])) {
            return;
        }
        if ($user->hasRole('mekanik')) {
            abort_if($service->mekanik_id !== null && $service->mekanik_id !== $user->id, 403, 'Tugas servis ini bukan tanggung jawab Anda.');
        }
        abort_if($user->branch_id === null || $service->branch_id !== $user->branch_id, 403, 'Servis ini di luar cabang Anda.');
    }

    public function create(Request $request)
    {
        $branches = Branch::where('aktif', true)->orderBy('nama')->get();
        $variants = Variant::with('carModel.brand')
            ->where('aktif', true)
            ->get()
            ->sortBy([
                fn ($a, $b) => ($a->carModel?->brand?->nama ?? '') <=> ($b->carModel?->brand?->nama ?? ''),
                fn ($a, $b) => ($a->carModel?->nama ?? '') <=> ($b->carModel?->nama ?? ''),
                fn ($a, $b) => $a->nama <=> $b->nama,
            ])
            ->values();
        $colors = Color::where('aktif', true)->orderBy('nama')->get();

        $customer = null;
        $ownedUnits = collect();
        if ($request->user()) {
            $customerIds = Customer::where('user_id', $request->user()->id)->pluck('id')->all();
            $customer = Customer::where('user_id', $request->user()->id)->first();
            if ($customerIds) {
                $ownedUnits = Order::with(['unit.variant.carModel.brand', 'unit.color', 'branch'])
                    ->whereIn('customer_id', $customerIds)
                    ->whereNotIn('status', ['Batal', 'Expired'])
                    ->latest()
                    ->get()
                    ->pluck('unit')
                    ->filter()
                    ->unique('id')
                    ->values();
            }
        }

        return view('servis.create', compact('branches', 'variants', 'colors', 'customer', 'ownedUnits'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'nik' => ['required', 'digits:16'],
            'telp' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'alamat' => ['required', 'string'],
            'branch_id' => ['required', 'exists:branches,id'],
            'variant_id' => ['nullable', 'exists:variants,id'],
            'color_id' => ['nullable', 'exists:colors,id'],
            'nopol' => ['required', 'string', 'max:15'],
            'vin' => ['nullable', 'string', 'max:30'],
            'km' => ['required', 'integer', 'min:0'],
            'keluhan' => ['required', 'string'],
            'jadwal' => ['required', 'date', 'after:now'],
        ]);

        // Jika VIN cocok dengan unit dealer, otomatis lengkapi variant_id dan color_id bila belum dipilih.
        if (! empty($data['vin']) && (empty($data['variant_id']) || empty($data['color_id']))) {
            $unitMatch = Unit::where('vin', trim($data['vin']))->first();
            if ($unitMatch) {
                $data['variant_id'] = $data['variant_id'] ?? $unitMatch->variant_id;
                $data['color_id'] = $data['color_id'] ?? $unitMatch->color_id;
            }
        }

        $jadwal = new \DateTime($data['jadwal']);
        $penuh = ServiceBooking::where('branch_id', $data['branch_id'])
            ->where('status', '!=', 'Batal')
            ->whereBetween('jadwal', [$jadwal->format('Y-m-d H:00:00'), $jadwal->format('Y-m-d H:59:59')])
            ->count() >= self::SLOT_LIMIT;

        abort_if($penuh, 422, 'Slot jam tersebut di cabang ini sudah penuh. Pilih jam lain.');

        $booking = DB::transaction(function () use ($data, $request) {
            $customer = Customer::firstOrCreate(
                ['nik' => $data['nik']],
                ['user_id' => $request->user()?->id, 'nama' => $data['nama'], 'telp' => $data['telp'], 'alamat' => $data['alamat']]
            );

            $tahun = now()->format('Y');
            $seq = ServiceBooking::where('no_svc', 'like', "SVC-{$tahun}-%")->lockForUpdate()->count() + 1;

            return ServiceBooking::create([
                'no_svc' => sprintf('SVC-%s-%04d', $tahun, $seq),
                'customer_id' => $customer->id,
                'branch_id' => $data['branch_id'],
                'variant_id' => $data['variant_id'] ?? null,
                'color_id' => $data['color_id'] ?? null,
                'nopol' => strtoupper($data['nopol']),
                'vin' => $data['vin'] ?? null,
                'km' => $data['km'],
                'keluhan' => $data['keluhan'],
                'jadwal' => $data['jadwal'],
                'status' => 'Menunggu',
            ]);
        });

        $booking->load(['customer', 'branch']);
        foreach (User::role('service-advisor')->where('branch_id', $booking->branch_id)->whereNotNull('email')->get() as $sa) {
            if (! ($keSa = Notifikasi::emailStaf($sa->email))) {
                continue;
            }
            SendMailJob::dispatch($keSa, "Booking servis baru {$booking->no_svc} di {$booking->branch->nama}",
                view('emails.notifikasi', [
                    'judul' => "Booking servis baru {$booking->no_svc}",
                    'baris' => explode("\n", Notifikasi::servisBaruUntukBengkel($booking)),
                    'tombol' => null,
                    'kontak' => null,
                ])->render(), [], true);
        }

        return redirect()->route('home')->with('sukses', "Booking servis {$booking->no_svc} diterima. Datang sesuai jadwal {$booking->jadwal->format('d M Y H:i')}.");
    }

    public function update(Request $request, ServiceBooking $service)
    {
        $user = $request->user();
        $this->otorisasiServis($user, $service);
        $mekanik = $user->hasRole('mekanik');

        if ($mekanik && in_array($request->input('status'), ['Dikonfirmasi', 'Batal'], true)) {
            abort(403, 'Mekanik tidak dapat mengonfirmasi atau membatalkan booking.');
        }

        $data = $request->validate([
            'status' => ['required', 'in:Dikonfirmasi,Dikerjakan,Selesai,Batal'],
            'estimasi_biaya' => ['nullable', 'integer', 'min:0'],
            'metode_bayar' => ['nullable', 'in:Cash,Transfer'],
            'mekanik_id' => ['nullable', 'exists:users,id'],
        ]);

        // Estimasi wajib ada saat booking dikonfirmasi.
        if ($data['status'] === 'Dikonfirmasi' && (int) ($data['estimasi_biaya'] ?? $service->estimasi_biaya) <= 0) {
            throw ValidationException::withMessages(['estimasi_biaya' => 'Estimasi biaya wajib diisi saat konfirmasi booking.']);
        }
        // Metode pembayaran wajib dipilih oleh SA/Admin saat servis diselesaikan.
        if (! $mekanik && $data['status'] === 'Selesai' && ! ($data['metode_bayar'] ?? $service->metode_bayar)) {
            throw ValidationException::withMessages(['metode_bayar' => 'Pilih metode pembayaran (Cash atau Transfer) saat menyelesaikan servis.']);
        }

        // Mekanik: hanya Dikerjakan/Selesai, tanpa ubah estimasi dan penugasan.
        if ($mekanik) {
            abort_if(array_key_exists('estimasi_biaya', $data) && (int) $data['estimasi_biaya'] !== (int) $service->estimasi_biaya, 403, 'Estimasi biaya hanya dapat diubah service advisor.');
            abort_if(array_key_exists('mekanik_id', $data) && (int) $data['mekanik_id'] !== (int) $service->mekanik_id, 403, 'Penugasan mekanik hanya dapat diubah service advisor.');
        }

        $mekanikLama = $service->mekanik_id;
        $statusLama = $service->status;
        DB::transaction(function () use ($data, $service, $mekanik, $user) {
            if ($data['status'] === 'Selesai') {
                foreach ($service->details()->where('jenis', 'Sparepart')->get() as $detail) {
                    $sp = $detail->sparepart;
                    abort_if(!$sp || $sp->stok < $detail->qty, 422, "Stok {$detail->deskripsi} tidak cukup.");
                    $sp->decrement('stok', $detail->qty);
                }
            }
            $service->update(array_merge(
                [
                    'status' => $data['status'],
                    'estimasi_biaya' => $data['estimasi_biaya'] ?? $service->estimasi_biaya,
                    'metode_bayar' => $data['metode_bayar'] ?? $service->metode_bayar,
                    'mekanik_id' => $data['mekanik_id'] ?? $service->mekanik_id,
                ],
                $mekanik ? ['estimasi_biaya' => $service->estimasi_biaya, 'mekanik_id' => $service->mekanik_id ?? $user->id] : [],
            ));
        });

        $fresh = $service->fresh();
        if ($statusLama !== 'Dikerjakan' && $fresh->status === 'Dikerjakan' && $fresh->details()->exists()) {
            $this->kirimEstimasiBerjalan($fresh);
        }
        if ($fresh->mekanik_id && $fresh->mekanik_id !== $mekanikLama && ($keMekanik = Notifikasi::emailStaf($fresh->mekanik->email))) {
            SendMailJob::dispatch($keMekanik, "Tugas servis baru {$fresh->no_svc}",
                view('emails.notifikasi', [
                    'judul' => "Tugas servis baru {$fresh->no_svc}",
                    'baris' => explode("\n", Notifikasi::tugasServisUntukMekanik($fresh)),
                    'tombol' => null,
                    'kontak' => null,
                ])->render(), [], true);
        }
        if (in_array($fresh->status, ['Dikonfirmasi', 'Selesai'])) {
            $pesan = $fresh->status === 'Selesai'
                ? Notifikasi::servisSelesai($fresh, $fresh->total())
                : Notifikasi::servisDikonfirmasi($fresh);
            SendWaJob::dispatch($fresh->customer->telp, $pesan);
        }
        if ($statusLama !== 'Dikonfirmasi' && $fresh->status === 'Dikonfirmasi' && ($emailKonfirmasi = $fresh->customer->user?->email)) {
            $baris = explode("\n", Notifikasi::servisDikonfirmasi($fresh));
            if ($fresh->details->isNotEmpty()) {
                $baris[] = 'Rincian:';
                foreach ($fresh->details as $detail) {
                    $baris[] = "[{$detail->jenis}] {$detail->deskripsi} x {$detail->qty}: Rp ".number_format($detail->subtotal, 0, ',', '.');
                }
            }
            SendMailJob::dispatch($emailKonfirmasi, "Booking servis {$fresh->no_svc} dikonfirmasi",
                view('emails.notifikasi', [
                    'judul' => "Booking servis {$fresh->no_svc} dikonfirmasi",
                    'baris' => $baris,
                    'tombol' => null,
                    'kontak' => null,
                ])->render(), [], true);
        }
        if ($fresh->status === 'Selesai' && ($emailCustomer = $fresh->customer->user?->email)) {
            $baris = explode("\n", Notifikasi::servisSelesai($fresh, $fresh->total()));
            if ($fresh->details->isNotEmpty()) {
                $baris[] = 'Rincian:';
                foreach ($fresh->details as $detail) {
                    $baris[] = "[{$detail->jenis}] {$detail->deskripsi} x {$detail->qty}: Rp ".number_format($detail->subtotal, 0, ',', '.');
                }
            }
            $pdf = Pdf::loadView('invoice.servis', ['service' => $fresh->load(['customer', 'branch', 'mekanik', 'details'])]);
            $pdfPath = "invoices/{$fresh->no_svc}.pdf";
            Storage::disk('local')->put($pdfPath, $pdf->output());
            SendMailJob::dispatch($emailCustomer, "Servis {$fresh->no_svc} selesai, total Rp ".number_format($fresh->total(), 0, ',', '.'),
                view('emails.notifikasi', [
                    'judul' => "Servis {$fresh->no_svc} selesai",
                    'baris' => $baris,
                    'tombol' => null,
                    'kontak' => "Ambil unit di {$fresh->branch->nama}".($fresh->branch->telp ? " ({$fresh->branch->telp})" : '').'.',
                ])->render(), [$pdfPath], true);
        }

        return redirect()->route('admin.services.show', $service)->with('sukses', 'Status servis diperbarui.');
    }

    public function tanggapi(Request $request, ServiceBooking $service)
    {
        $this->otorisasiServis($request->user(), $service);

        $data = $request->validate(['tanggapan' => ['required', 'string', 'max:2000']]);
        $service->update(['tanggapan' => $data['tanggapan']]);

        $fresh = $service->fresh();
        $email = $fresh->customer->user?->email;
        if ($email) {
            $pesan = Notifikasi::tanggapanServis($fresh);
            SendMailJob::dispatch($email, "Tanggapan keluhan servis {$fresh->no_svc}",
                view('emails.notifikasi', [
                    'judul' => "Tanggapan keluhan servis {$fresh->no_svc}",
                    'baris' => explode("\n", $pesan),
                    'tombol' => null,
                    'kontak' => null,
                ])->render(), [], true);
        }

        return back()->with('sukses', 'Tanggapan tersimpan dan dikirim ke customer.');
    }

    // Email estimasi berjalan ke customer setiap rincian bertambah saat Dikerjakan.
    private function kirimEstimasiBerjalan(ServiceBooking $booking): void
    {
        $booking->loadMissing(['details', 'customer', 'branch']);
        $email = $booking->customer->user?->email;
        if (! $email) {
            return;
        }
        $pesan = Notifikasi::estimasiBerjalan($booking);
        SendMailJob::dispatch($email, "Estimasi servis {$booking->no_svc}: Rp ".number_format($booking->total(), 0, ',', '.'),
            view('emails.notifikasi', [
                'judul' => "Estimasi servis {$booking->no_svc}",
                'baris' => explode("\n", $pesan),
                'tombol' => null,
                'kontak' => null,
            ])->render(), [], true);
    }

    public function invoice(Request $request, ServiceBooking $service)
    {
        abort_unless($service->status === 'Selesai', 404);
        $milik = $service->customer->user_id !== null && $service->customer->user_id === $request->user()->id;
        $staf = $request->user()->hasAnyRole(['service-advisor', 'mekanik', 'admin-cabang', 'pimpinan', 'superadmin', 'owner']);
        abort_if(! $milik && ! $staf, 403, 'Invoice ini bukan milik Anda.');

        $service->load(['customer', 'branch', 'mekanik', 'details']);

        return Pdf::loadView('invoice.servis', ['service' => $service])->download($service->no_svc.'.pdf');
    }

    public function addDetail(Request $request, ServiceBooking $service)
    {
        $user = $request->user();
        $this->otorisasiServis($user, $service);
        abort_if(in_array($service->status, ['Selesai', 'Batal']), 422, 'Servis yang sudah selesai atau batal tidak bisa ditambah.');
        if ($user->hasRole('mekanik') && $service->mekanik_id === null) {
            $service->update(['mekanik_id' => $user->id]);
        }

        $data = $request->validate([
            'jenis' => ['required', 'in:Pekerjaan,Sparepart'],
            'sparepart_id' => ['required_if:jenis,Sparepart', 'nullable', 'exists:spareparts,id'],
            'deskripsi' => ['required_unless:jenis,Sparepart', 'nullable', 'string', 'max:255'],
            'qty' => ['required', 'integer', 'min:1'],
            'harga_satuan' => ['nullable', 'integer', 'min:0'],
        ]);

        if ($data['jenis'] === 'Sparepart') {
            $sp = Sparepart::findOrFail($data['sparepart_id']);
            $deskripsi = $sp->nama;
            $harga = $sp->harga;
        } else {
            $deskripsi = $data['deskripsi'];
            $harga = (int) ($data['harga_satuan'] ?? 0);
        }

        $service->details()->create([
            'jenis' => $data['jenis'],
            'deskripsi' => $deskripsi,
            'qty' => $data['qty'],
            'harga_satuan' => $harga,
            'subtotal' => $harga * $data['qty'],
            'sparepart_id' => $data['sparepart_id'] ?? null,
        ]);

        $fresh = $service->fresh();
        if ($fresh->status === 'Dikerjakan') {
            $this->kirimEstimasiBerjalan($fresh);
        }

        return back()->with('sukses', 'Detail servis ditambah.');
    }

    public function destroyDetail(Request $request, ServiceBooking $service, \App\Models\ServiceDetail $detail)
    {
        $this->otorisasiServis($request->user(), $service);
        abort_if($detail->service_booking_id !== $service->id, 404);
        abort_if(in_array($service->status, ['Selesai', 'Batal']), 422, 'Servis yang sudah selesai atau batal tidak bisa diubah.');

        $detail->delete();

        return redirect()->route('admin.services.show', $service)->with('sukses', 'Rincian servis dihapus.');
    }
}
