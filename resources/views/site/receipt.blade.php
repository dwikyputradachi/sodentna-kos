@php
    use App\Support\Owner\Fmt;

    $terbilangFn = function (int $n) use (&$terbilangFn): string {
        $angka = ['', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan', 'Sepuluh', 'Sebelas'];
        if ($n < 12) return $angka[$n];
        if ($n < 20) return $terbilangFn($n - 10) . ' Belas';
        if ($n < 100) return $terbilangFn(intdiv($n, 10)) . ' Puluh ' . $terbilangFn($n % 10);
        if ($n < 200) return 'Seratus ' . $terbilangFn($n - 100);
        if ($n < 1000) return $terbilangFn(intdiv($n, 100)) . ' Ratus ' . $terbilangFn($n % 100);
        if ($n < 2000) return 'Seribu ' . $terbilangFn($n - 1000);
        if ($n < 1000000) return $terbilangFn(intdiv($n, 1000)) . ' Ribu ' . $terbilangFn($n % 1000);
        if ($n < 1000000000) return $terbilangFn(intdiv($n, 1000000)) . ' Juta ' . $terbilangFn($n % 1000000);
        return $terbilangFn(intdiv($n, 1000000000)) . ' Miliar ' . $terbilangFn($n % 1000000000);
    };

    $amountInt = (int) round((float) $p->amount);
    $terbilang = method_exists(Fmt::class, 'terbilang')
        ? Fmt::terbilang($amountInt)
        : ($amountInt > 0 ? trim(preg_replace('/\s+/', ' ', $terbilangFn($amountInt))) . ' Rupiah' : 'Nol Rupiah');

    $payerName = $i->rental->tenant->name . ($i->rental->co_occupant_name ? ' & ' . $i->rental->co_occupant_name : '');
    $phoneText = $property->phone ?? (\App\Support\Site\DemoSite::WA ?? '0813 6444 9270');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Kuitansi {{ $r->number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { DEFAULT: '#12294a' },
                        brand: { DEFAULT: '#0f9d94', 700: '#0b7a73' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            body {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body class="min-h-screen bg-slate-100 p-4 font-sans text-slate-800 antialiased sm:p-8 print:min-h-0 print:bg-white print:p-0">

    <div class="mx-auto max-w-3xl space-y-4">
        {{-- LEMBAR KUITANSI --}}
        <article class="flex overflow-hidden rounded-xl border border-slate-300 bg-white p-5 shadow-xs sm:p-7 print:rounded-none print:border print:border-slate-800 print:p-5 print:shadow-none">

            {{-- Garis Vertikal Ganda Khas Kuitansi di Sisi Kiri --}}
            <div class="mr-4 flex shrink-0 gap-1.5 sm:mr-6" aria-hidden="true">
                <div class="w-3 bg-navy"></div>
                <div class="w-0.5 bg-navy"></div>
            </div>

            {{-- Isi Utama Kuitansi --}}
            <div class="min-w-0 flex-1 space-y-4 text-xs sm:text-sm">

                {{-- Header Properti --}}
                <header class="space-y-0.5">
                    <h1 class="text-lg font-bold tracking-wide text-navy uppercase sm:text-xl">
                        {{ $property->name }}
                    </h1>
                    <p class="text-xs text-slate-600">{{ $property->address }}</p>
                    @if ($phoneText)
                        <p class="text-xs text-slate-600">Tlp: {{ $phoneText }}</p>
                    @endif
                </header>

                {{-- Judul Kuitansi --}}
                <div class="border-y border-slate-400 py-1.5 text-center">
                    <h2 class="text-sm font-bold tracking-[0.18em] text-navy uppercase sm:text-base">
                        KWITANSI PEMBAYARAN
                    </h2>
                </div>

                {{-- Nomor Kuitansi & Nomor Kamar --}}
                <div class="grid gap-3 pt-0.5 sm:grid-cols-12 sm:gap-6">
                    <div class="flex items-baseline gap-2 sm:col-span-7">
                        <span class="w-28 shrink-0 text-slate-600">Nomor</span>
                        <span class="shrink-0 text-slate-600">:</span>
                        <span class="flex-1 border-b border-slate-400 pb-0.5 font-semibold text-navy">
                            {{ $r->number }}
                        </span>
                    </div>
                    <div class="flex items-baseline gap-2 sm:col-span-5">
                        <span class="w-28 shrink-0 text-slate-600 sm:w-auto">Nomor Kamar</span>
                        <span class="shrink-0 text-slate-600">:</span>
                        <span class="flex-1 border-b border-slate-400 pb-0.5 font-semibold text-navy">
                            {{ $i->rental->room->code }}
                        </span>
                    </div>
                </div>

                {{-- Telah Terima Dari --}}
                <div class="flex items-baseline gap-2">
                    <span class="w-28 shrink-0 text-slate-600">Telah terima dari</span>
                    <span class="shrink-0 text-slate-600">:</span>
                    <span class="flex-1 border-b border-slate-400 pb-0.5 text-sm font-bold text-navy sm:text-base">
                        {{ $payerName }}
                    </span>
                </div>

                {{-- Rincian Pembayaran --}}
                <section class="pt-1">
                    <div class="border-y border-slate-400 py-1 text-xs font-bold tracking-wider text-navy uppercase">
                        RINCIAN PEMBAYARAN
                    </div>

                    <div class="divide-y divide-dotted divide-slate-300">
                        <div class="flex items-baseline gap-2 py-2">
                            <span class="w-28 shrink-0 text-slate-600">Sewa Kamar</span>
                            <span class="shrink-0 text-slate-600">:</span>
                            <span class="font-medium text-slate-800">{{ Fmt::rp($p->rent_amount) }}</span>
                        </div>
                        <div class="flex items-baseline gap-2 py-2">
                            <span class="w-28 shrink-0 text-slate-600">Deposit</span>
                            <span class="shrink-0 text-slate-600">:</span>
                            <span class="font-medium text-slate-800">
                                {{ $p->deposit_amount > 0 ? Fmt::rp($p->deposit_amount) : '-' }}
                            </span>
                        </div>
                    </div>

                    {{-- Kotak Highlight TOTAL --}}
                    <div class="mt-1 flex items-center gap-2 border border-slate-400 bg-slate-100 px-3 py-2 font-bold text-navy">
                        <span class="w-[6.35rem] shrink-0 tracking-wide uppercase">TOTAL</span>
                        <span class="shrink-0">:</span>
                        <span class="text-sm sm:text-base">{{ Fmt::rp($p->amount) }}</span>
                    </div>
                </section>

                {{-- Detail Keterangan & Tanda Tangan --}}
                <footer class="grid gap-6 pt-2 sm:grid-cols-12 sm:items-end">
                    {{-- Kolom Kiri: Terbilang, Untuk Pembayaran, Periode, Metode --}}
                    <div class="space-y-2.5 sm:col-span-8">
                        <div class="flex items-baseline gap-2">
                            <span class="w-28 shrink-0 text-slate-600">Terbilang</span>
                            <span class="shrink-0 text-slate-600">:</span>
                            <span class="flex-1 border-b border-slate-400 pb-0.5 font-medium italic text-slate-800">
                                {{ $terbilang }}
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="w-28 shrink-0 text-slate-600">Untuk pembayaran</span>
                            <span class="shrink-0 text-slate-600">:</span>
                            <span class="flex-1 border-b border-slate-400 pb-0.5 font-medium text-slate-800">
                                Untuk Pembayaran Sewa {{ $property->name }}
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="w-28 shrink-0 text-slate-600">Periode</span>
                            <span class="shrink-0 text-slate-600">:</span>
                            <span class="flex-1 border-b border-slate-400 pb-0.5 font-medium text-slate-800">
                                {{ $i->periodLabel() }}
                            </span>
                        </div>
                        <div class="flex items-baseline gap-2">
                            <span class="w-28 shrink-0 text-slate-600">Metode &amp; Tgl</span>
                            <span class="shrink-0 text-slate-600">:</span>
                            <span class="flex-1 border-b border-slate-400 pb-0.5 text-xs text-slate-700">
                                {{ $p->methodLabel() }} · Dibayar {{ Fmt::date($p->paid_on) }}
                            </span>
                        </div>
                    </div>

                    {{-- Kolom Kanan: Tempat/Tanggal & Tanda Tangan Pengelola --}}
                    <div class="flex flex-col items-center text-center sm:col-span-4">
                        <div class="text-xs text-slate-700">Batam, {{ Fmt::date($r->issued_on) }}</div>

                        {{-- Ruang Tanda Tangan --}}
                        <div class="my-2 flex h-14 w-full items-center justify-center">
                            @if (!empty($property->signature_url))
                                <img src="{{ $property->signature_url }}" alt="Tanda tangan pengelola" class="max-h-12 object-contain">
                            @endif
                        </div>

                        <div class="w-full max-w-[11rem]">
                            @if (!empty($property->manager_name))
                                <div class="border-b border-slate-500 pb-0.5 text-xs font-bold text-navy">
                                    {{ $property->manager_name }}
                                </div>
                                <div class="mt-0.5 text-[11px] italic text-slate-500">
                                    Pengelola {{ $property->name }}
                                </div>
                            @else
                                <div class="border-t border-slate-500 pt-1 text-xs font-bold text-navy">
                                    Pengelola {{ $property->name }}
                                </div>
                            @endif
                        </div>
                    </div>
                </footer>
            </div>
        </article>

        {{-- Tombol Aksi Cetak (Hilang saat Print) --}}
        <div class="flex flex-col gap-2.5 sm:flex-row sm:justify-end print:hidden">
            <button
                type="button"
                onclick="window.print()"
                class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 sm:w-auto"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18 10.5h.008v.008H18V10.5Zm-3 0h.008v.008H15V10.5Z" />
                </svg>
                <span>Cetak / Simpan PDF</span>
            </button>
        </div>
    </div>

</body>
</html>