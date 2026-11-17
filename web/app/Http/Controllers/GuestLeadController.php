<?php

namespace App\Http\Controllers;

use App\Models\Branch;
use App\Models\Customer;
use App\Models\Lead;
use App\Models\Unit;
use App\Models\Variant;
use Illuminate\Http\Request;

/**
 * Menangkap niat pembeli tanpa akun (lead dulu, akun belakangan).
 * SPK resmi tetap lewat SpkController yang membutuhkan login.
 */
class GuestLeadController extends Controller
{
    public function create(Request $request)
    {
        $unit = null;
        $variant = null;
        $branch = null;

        $unitId = $request->query('unit_id');
        $variantId = $request->query('variant_id');
        $branchId = $request->query('branch_id');

        if ($unitId) {
            $unit = Unit::with(['variant.carModel.brand', 'branch', 'color'])->find($unitId);
            if (! $unit || $unit->status !== 'Tersedia' || ! $unit->variant->aktif) {
                $unit = null;
            } else {
                $variant = $unit->variant;
                $branch = $unit->branch;
                $variantId = (string) $unit->variant_id;
                $branchId = (string) $unit->branch_id;
            }
        }

        if (! $variant && $variantId) {
            $variant = Variant::with('carModel.brand')->where('aktif', true)->find($variantId);
        }

        if (! $branch && $branchId) {
            $branch = Branch::where('aktif', true)->find($branchId);
        }

        // OTR dari harga resmi cabang tujuan; bila tidak ada, ambil yang termurah.
        $otr = null;
        if ($variant) {
            $otr = $variant->priceLists()
                ->when($branch, fn ($q) => $q->where('branch_id', $branch->id))
                ->min('otr');
        }

        return view('lead.form', compact('unit', 'variant', 'branch', 'otr'));
    }

    public function store(Request $request)
    {
        // Honeypot: bot yang mengisi kolom tersembunyi dianggap sukses tanpa menyimpan apa pun.
        if ($request->filled('website')) {
            return redirect()->route('spk.guest.sukses');
        }

        $data = $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'telp' => ['required', 'regex:/^[0-9]{10,15}$/'],
            'email' => ['nullable', 'email', 'max:150'],
            'catatan' => ['nullable', 'string', 'max:500'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'variant_id' => ['nullable', 'exists:variants,id'],
            'branch_id' => ['nullable', 'exists:branches,id'],
        ]);

        $unit = Unit::with(['variant.carModel.brand', 'branch', 'color'])
            ->find($data['unit_id'] ?? null);
        if ($unit && ($unit->status !== 'Tersedia' || ! $unit->variant->aktif)) {
            $unit = null;
        }
        if ($unit) {
            $data['variant_id'] = $unit->variant_id;
            $data['branch_id'] = $unit->branch_id;
        }

        $variant = Variant::with('carModel.brand')->where('aktif', true)
            ->find($data['variant_id'] ?? null);
        $branch = $unit?->branch ?? Branch::where('aktif', true)->find($data['branch_id'] ?? null);

        $customer = Customer::firstOrCreate(
            ['telp' => $data['telp']],
            ['user_id' => $request->user()?->id, 'nama' => $data['nama']]
        );

        $detail = [];
        $detail[] = '[Tanpa akun] Niat SPK via form publik.';
        $detail[] = 'Nama: ' . $data['nama'];
        if ($variant) {
            $detail[] = 'Mobil: ' . $variant->carModel->brand->nama . ' ' . $variant->carModel->nama . ' ' . $variant->nama;
        }
        if ($unit) {
            $detail[] = 'Unit spesifik: ' . $unit->vin . ($unit->color ? ', warna ' . $unit->color->nama : '') . ', tahun ' . $unit->tahun_rakit;
        }
        if ($branch) {
            $detail[] = 'Cabang tujuan: ' . $branch->nama;
        }
        if (! empty($data['email'])) {
            $detail[] = 'Email: ' . $data['email'];
        }
        if (! empty($data['catatan'])) {
            $detail[] = 'Catatan: ' . $data['catatan'];
        }

        Lead::create([
            'customer_id' => $customer->id,
            'sumber' => 'Web',
            'status' => 'Baru',
            'kebutuhan_text' => implode(' ', $detail),
        ]);

        return redirect()->route('spk.guest.sukses');
    }

    public function sukses()
    {
        return view('lead.sukses');
    }
}
