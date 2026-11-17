@props(['items', 'skala', 'klik' => false])
@php
    $lebar = 720; $tinggi = 276; $kiri = 76; $kanan = 16; $atas = 34; $bawah = 50;
    $plotL = $lebar - $kiri - $kanan;
    $plotT = $tinggi - $atas - $bawah;
    $n = max(1, count($items));
    $slotW = $plotL / $n;
    $barW = min(56, max(18, round($slotW * 0.56, 1)));
    $gradId = 'omzet-bar-' . uniqid();

    $ringkas = function ($rp) {
        if ($rp >= 1000000000) {
            return 'Rp '.rtrim(rtrim(number_format($rp / 1000000000, 1, ',', '.'), '0'), ',').' M';
        }
        if ($rp >= 1000000) {
            return 'Rp '.rtrim(rtrim(number_format($rp / 1000000, 1, ',', '.'), '0'), ',').' jt';
        }

        return 'Rp '.number_format($rp, 0, ',', '.');
    };
@endphp
<div x-data="{ hoverIdx: null }" class="relative mt-2">
    <svg viewBox="0 0 {{ $lebar }} {{ $tinggi }}" class="w-full h-auto" role="img" aria-label="Grafik omzet">
        <defs>
            <linearGradient id="{{ $gradId }}" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" stop-color="rgb(var(--accent))" stop-opacity="1" />
                <stop offset="100%" stop-color="rgb(var(--accent))" stop-opacity="0.72" />
            </linearGradient>
        </defs>

        @for ($g = 0; $g <= 4; $g++)
            @php $gy = $atas + $plotT - ($g / 4) * $plotT; @endphp
            <line x1="{{ $kiri }}" y1="{{ $gy }}" x2="{{ $lebar - $kanan }}" y2="{{ $gy }}"
                  stroke="rgb(var(--line))" stroke-width="{{ $g === 0 ? '1.2' : '1' }}"
                  @if ($g > 0) stroke-dasharray="4 4" @endif />
            <text x="{{ $kiri - 8 }}" y="{{ $gy + 4 }}" text-anchor="end" font-size="11" fill="rgb(var(--muted))">{{ $ringkas($skala * $g / 4) }}</text>
        @endfor

        @foreach ($items as $i => $bar)
            @php
                $cx = round($kiri + ($i + 0.5) * $slotW, 1);
                $bx = round($cx - $barW / 2, 1);
                $rasio = $skala > 0 ? min(1, max(0, $bar['nominal'] / $skala)) : 0;
                $bh = $bar['nominal'] > 0 ? max(8, round($rasio * $plotT, 1)) : 0;
                $by = round($atas + $plotT - $bh, 1);
                $bisaKlik = $klik && ! empty($bar['kunci']);
            @endphp

            <g @mouseenter="hoverIdx = {{ $i }}" @mouseleave="hoverIdx = null"
               @if ($bisaKlik)
                   @click="level = 'bulan'; tahun = '{{ $bar['kunci'] }}'"
                   class="cursor-pointer"
                   tabindex="0"
                   role="button"
                   aria-label="Lihat bulan {{ $bar['label'] }}"
                   @keydown.enter="level = 'bulan'; tahun = '{{ $bar['kunci'] }}'"
               @endif>
                <title>{{ $bar['label'] }}: Rp {{ number_format($bar['nominal'], 0, ',', '.') }} ({{ $bar['unit'] }} unit)</title>
                <rect x="{{ $bx }}" y="{{ $atas }}" width="{{ $barW }}" height="{{ $plotT }}" rx="6"
                      fill="rgb(var(--surface-2))" :opacity="hoverIdx === {{ $i }} ? 0.95 : 0.6" />
                @if ($bh > 0)
                    <rect x="{{ $bx }}" y="{{ $by }}" width="{{ $barW }}" height="{{ $bh }}" rx="6"
                          fill="url(#{{ $gradId }})" :opacity="hoverIdx === {{ $i }} ? 1 : 0.9" />
                @endif
                <rect x="{{ round($cx - $slotW / 2, 1) }}" y="{{ $atas - 10 }}" width="{{ round($slotW, 1) }}" height="{{ $plotT + 54 }}" fill="transparent" />

                @if ($bar['nominal'] > 0)
                    <text x="{{ $cx }}" y="{{ $by - 8 }}" text-anchor="middle"
                          font-size="{{ $n > 10 ? '10' : '11' }}" font-weight="700"
                          fill="rgb(var(--ink))">
                        {{ $ringkas($bar['nominal']) }}
                    </text>
                @endif

                <text x="{{ $cx }}" y="{{ $tinggi - 26 }}" text-anchor="middle"
                      font-size="{{ $n > 12 ? '10' : '11' }}" font-weight="600"
                      fill="rgb(var(--ink))">{{ $bar['label'] }}</text>
                @if ($bar['unit'] > 0 || $n <= 12)
                    <text x="{{ $cx }}" y="{{ $tinggi - 11 }}" text-anchor="middle"
                          font-size="10" fill="rgb(var(--muted))">{{ $bar['unit'] }} unit</text>
                @endif
            </g>
        @endforeach
    </svg>
</div>
