<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\CarModel;
use App\Models\Color;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CarModelController extends Controller
{
    public function index()
    {
        $models = CarModel::with(['brand', 'variants.units.color'])->orderBy('nama')->paginate(15);

        return view('admin.models.index', compact('models'));
    }

    public function create()
    {
        return view('admin.models.form', [
            'model' => new CarModel(),
            'brands' => Brand::orderBy('nama')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['slug'] = Str::slug($data['nama']);
        CarModel::create($data);

        return redirect()->route('admin.models.index')->with('sukses', 'Model berhasil ditambah.');
    }

    public function edit(CarModel $model)
    {
        $model->load(['variants.units.color', 'variants.units.branch']);

        return view('admin.models.form', [
            'model' => $model,
            'brands' => Brand::orderBy('nama')->get(),
            'colors' => Color::where('aktif', true)->orderBy('nama')->get(),
        ]);
    }

    public function update(Request $request, CarModel $model)
    {
        $model->update($this->validated($request));

        $extra = $request->validate([
            'variants' => ['nullable', 'array'],
            'variants.*.nama' => ['required', 'string', 'max:100'],
            'variants.*.transmisi' => ['required', 'in:MT,AT,CVT'],
            'variants.*.bbm' => ['required', 'string', 'max:30'],
            'variants.*.aktif' => ['nullable', 'boolean'],
            'new_variant.nama' => ['nullable', 'string', 'max:100'],
            'new_variant.transmisi' => ['nullable', 'in:MT,AT,CVT'],
            'new_variant.bbm' => ['nullable', 'string', 'max:30'],
            'new_variant.aktif' => ['nullable', 'boolean'],
            'unit_colors' => ['nullable', 'array'],
            'unit_colors.*' => ['required', 'integer', 'exists:colors,id'],
            'new_color.nama' => ['nullable', 'string', 'max:50'],
            'new_color.kode_hex' => ['nullable', 'string', 'max:10'],
            'new_color.unit_id' => ['nullable', 'integer'],
        ]);

        if (! empty($extra['variants'])) {
            $existingVariants = $model->variants()->get()->keyBy('id');
            foreach ($extra['variants'] as $variantId => $vData) {
                if ($variant = $existingVariants->get((int) $variantId)) {
                    $variant->update([
                        'nama' => $vData['nama'],
                        'transmisi' => $vData['transmisi'],
                        'bbm' => $vData['bbm'],
                        'aktif' => ! empty($vData['aktif']),
                    ]);
                }
            }
        }

        if (! empty($extra['new_variant']['nama'])) {
            $nv = $extra['new_variant'];
            $model->variants()->create([
                'nama' => $nv['nama'],
                'slug' => Str::slug($nv['nama']),
                'transmisi' => $nv['transmisi'] ?? 'AT',
                'bbm' => $nv['bbm'] ?? 'Bensin',
                'aktif' => array_key_exists('aktif', $nv) ? (bool) $nv['aktif'] : true,
            ]);
        }

        if (! empty($extra['unit_colors']) || ! empty($extra['new_color']['nama'])) {
            $units = $model->variants()->with('units')->get()->flatMap->units->keyBy('id');
            foreach ($extra['unit_colors'] ?? [] as $unitId => $colorId) {
                if ($unit = $units->get((int) $unitId)) {
                    $unit->update(['color_id' => (int) $colorId]);
                }
            }

            if (! empty($extra['new_color']['nama'])) {
                $nc = $extra['new_color'];
                $newColor = Color::firstOrCreate(
                    ['nama' => trim($nc['nama'])],
                    ['kode_hex' => $nc['kode_hex'] ?: '#6B7280', 'aktif' => true]
                );
                if (! empty($nc['unit_id']) && ($unit = $units->get((int) $nc['unit_id']))) {
                    $unit->update(['color_id' => $newColor->id]);
                }
            }
        }

        return redirect()->route('admin.models.index')->with('sukses', 'Model, varian & warna kendaraan berhasil diperbarui.');
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'brand_id' => ['required', 'exists:brands,id'],
            'nama' => ['required', 'string', 'max:100'],
            'deskripsi' => ['nullable', 'string'],
            'aktif' => ['nullable', 'boolean'],
        ]);
        $data['aktif'] = $request->boolean('aktif');

        return $data;
    }
}
