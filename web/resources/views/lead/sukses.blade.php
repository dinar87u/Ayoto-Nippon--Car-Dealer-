<x-site-layout judul="Pengajuan Diterima" deskripsi="Pengajuan SPK Anda sudah kami terima, sales Ayoto Nippon akan segera menghubungi.">
    <x-site-header />

    @php
        $waSukses = \App\Support\Wa::chat(
            config('services.wa.sales'),
            'Halo Ayoto Nippon, saya baru mengirim pengajuan SPK lewat website. Mohon dilanjutkan prosesnya, terima kasih.'
        );
    @endphp

    <div class="w-full px-4 sm:px-6 lg:px-8 xl:px-12 py-16 sm:py-24">
        <div class="max-w-xl mx-auto text-center bg-surface border border-line rounded-3xl p-8 sm:p-12 shadow-xl shadow-black/5">
            <div class="mx-auto w-16 h-16 rounded-full bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center">
                <svg class="w-8 h-8 text-emerald-700 dark:text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
            </div>

            <h1 class="mt-6 text-2xl sm:text-3xl font-extrabold text-ink tracking-tight">Pengajuan Anda Diterima</h1>
            <p class="mt-3 text-sm sm:text-base text-muted leading-relaxed">
                Terima kasih! Sales kami akan menghubungi Anda lewat <strong class="text-ink">WhatsApp</strong> dalam waktu dekat
                untuk melanjutkan proses pemesanan (SPK).
            </p>

            <div class="mt-8 bg-surface-2 border border-line rounded-2xl p-5 text-left text-sm text-muted space-y-2.5">
                <p class="flex gap-2.5"><span class="text-accent font-bold">1.</span> Sales menghubungi Anda untuk konfirmasi unit & harga.</p>
                <p class="flex gap-2.5"><span class="text-accent font-bold">2.</span> Setuju? Booking fee resmi ke rekening cabang.</p>
                <p class="flex gap-2.5"><span class="text-accent font-bold">3.</span> SPK & invoice terbit, dokumen diurus bersama sales.</p>
            </div>

            <div class="mt-8 flex flex-col sm:flex-row gap-3 justify-center">
                <a href="{{ $waSukses }}" target="_blank" rel="noopener"
                    class="py-3 px-6 text-center font-bold text-sm text-white bg-[#25D366] hover:bg-[#1FB857] rounded-xl shadow-lg shadow-black/10 transition-all min-h-[48px] flex items-center justify-center gap-2">
                    <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.52.149-.174.198-.298.297-.497.099-.198.05-.371-.05-.52-.099-.148-.669-1.612-.916-2.207-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.096 3.2 5.077 4.487.709.306 1.263.489 1.694.625.712.227 1.36.195 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413"/>
                    </svg>
                    Langsung Chat Sales Sekarang
                </a>
                <a href="{{ route('katalog.index') }}"
                    class="py-3 px-6 text-center font-bold text-sm text-white bg-accent-strong hover:bg-[#9E0E19] rounded-xl shadow-lg shadow-accent/25 transition-all min-h-[48px] flex items-center justify-center">
                    Lihat Mobil Lain
                </a>
                @guest
                    <a href="{{ route('register') }}"
                        class="py-3 px-6 text-center font-semibold text-sm text-ink bg-surface border border-line hover:border-accent rounded-xl transition-all min-h-[48px] flex items-center justify-center">
                        Buat Akun (Opsional)
                    </a>
                @endguest
            </div>
            <p class="mt-4 text-xs text-muted">
                Akun tidak wajib. Data Anda sudah masuk ke tim sales kami.
            </p>
        </div>
    </div>
</x-site-layout>
