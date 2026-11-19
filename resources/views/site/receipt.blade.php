@php use App\Support\Owner\Fmt; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Kuitansi {{ $r->number }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a'},brand:{DEFAULT:'#0f9d94',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
</head>
<body class="bg-slate-50 p-4 font-sans text-slate-800 antialiased print:bg-white print:p-0">
    <div class="mx-auto max-w-xl space-y-4">
        <article class="rounded-xl border border-slate-200 bg-white p-6 print:border-0">
            @if ($voided)<div class="mb-3 rounded bg-red-50 p-2 text-center text-xs font-semibold text-red-700">KUITANSI INI TELAH DIBATALKAN</div>@endif
            <header class="border-b border-slate-200 pb-4 text-center">
                <div class="text-lg font-semibold text-navy">{{ $property->name }}</div>
                <div class="text-xs text-slate-500">{{ $property->address }}</div>
                <div class="mt-3 text-sm font-semibold tracking-wide">KUITANSI PEMBAYARAN</div>
                <div class="text-xs text-slate-500">Nomor {{ $r->number }}</div>
            </header>

            <dl class="grid grid-cols-3 gap-2 py-4 text-sm">
                <dt class="text-slate-500">Telah terima dari</dt><dd class="col-span-2 font-medium">{{ $i->rental->tenant->name }}{{ $i->rental->co_occupant_name ? ' & ' . $i->rental->co_occupant_name : '' }}</dd>
                <dt class="text-slate-500">Kamar</dt><dd class="col-span-2">{{ $i->rental->room->code }}</dd>
                <dt class="text-slate-500">Untuk pembayaran</dt><dd class="col-span-2">Sewa kamar — {{ $i->periodLabel() }}</dd>
                <dt class="text-slate-500">Metode</dt><dd class="col-span-2">{{ $p->methodLabel() }}</dd>
                <dt class="text-slate-500">Tanggal bayar</dt><dd class="col-span-2">{{ Fmt::date($p->paid_on) }}</dd>
            </dl>

            <table class="w-full border-t border-slate-200 text-sm">
                <tbody class="divide-y divide-slate-100">
                    <tr><td class="py-2">Sewa kamar</td><td class="py-2 text-right">{{ Fmt::rp($p->rent_amount) }}</td></tr>
                    @if ($p->deposit_amount > 0)<tr><td class="py-2">Deposit (dana titipan)</td><td class="py-2 text-right">{{ Fmt::rp($p->deposit_amount) }}</td></tr>@endif
                    <tr class="font-semibold"><td class="py-2">TOTAL</td><td class="py-2 text-right text-navy">{{ Fmt::rp($p->amount) }}</td></tr>
                </tbody>
            </table>

            <footer class="mt-6 text-right text-xs text-slate-500">
                <div>{{ Fmt::date($r->issued_on) }}</div>
                <div class="mt-8 font-medium text-slate-700">Pengelola {{ $property->name }}</div>
            </footer>
        </article>
        <button onclick="window.print()" class="w-full rounded-md bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-700 print:hidden">Cetak / Simpan PDF</button>
    </div>
</body>
</html>
