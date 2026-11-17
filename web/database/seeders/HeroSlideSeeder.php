<?php

namespace Database\Seeders;

use App\Models\HeroSlide;
use Illuminate\Database\Seeder;

class HeroSlideSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['images/hero-car.webp', 'images/unit/foto-7.jpg', 'images/unit/foto-9.jpg'] as $i => $foto) {
            HeroSlide::firstOrCreate(
                ['foto' => $foto],
                ['nama' => 'Default '.($i + 1), 'urutan' => $i, 'aktif' => true]
            );
        }
    }
}
