@php use App\Support\Owner\Fmt; $t = $st['totals']; @endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex,nofollow">
    <title>Laporan Keuangan {{ $p['label'] }}</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a'},brand:{DEFAULT:'#0f9d94',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
</head>
<body class="bg-white p-6 font-sans text-sm text-slate-800 antialiased">
    <div class="mx-auto max-w-3xl space-y-5">
        <header class="border-b border-slate-300 pb-3"><div class="text-lg font-semibold text-navy">Laporan Keuangan</div><div class="text-slate-500">{{ $property->name }} · {{ $p['label'] }}</div></header>
        <section><h2 class="mb-1 font-semibold">Hasil operasional</h2>
            <table class="w-full"><tbody class="divide-y divide-slate-200">
                <tr><td class="py-1">Pendapatan sewa</td><td class="py-1 text-right">{{ Fmt::rp($t['rent']) }}</td></tr>
                @foreach ($byCategory as $cat => $amt)<tr><td class="py-1 pl-4 text-slate-600">{{ $cat }}</td><td class="py-1 text-right">-{{ Fmt::rp($amt) }}</td></tr>@endforeach
                <tr class="font-semibold"><td class="py-1">Hasil operasional</td><td class="py-1 text-right">{{ Fmt::rp($t['operating']) }}</td></tr>
            </tbody></table></section>
        <section><h2 class="mb-1 font-semibold">Arus kas</h2>
            <table class="w-full"><tbody class="divide-y divide-slate-200">
                <tr><td class="py-1">Saldo awal</td><td class="py-1 text-right">{{ Fmt::rp($st['opening']) }}</td></tr>
                <tr><td class="py-1">Kas masuk — sewa</td><td class="py-1 text-right">{{ Fmt::rp($t['rent']) }}</td></tr>
                <tr><td class="py-1">Kas masuk — titipan deposit</td><td class="py-1 text-right">{{ Fmt::rp($t['deposit']) }}</td></tr>
                <tr><td class="py-1">Kas keluar — pengeluaran</td><td class="py-1 text-right">-{{ Fmt::rp($t['expense']) }}</td></tr>
                <tr class="font-semibold"><td class="py-1">Saldo akhir</td><td class="py-1 text-right">{{ Fmt::rp($st['closing']) }}</td></tr>
            </tbody></table></section>
        <section><h2 class="mb-1 font-semibold">Rincian transaksi</h2>
            <table class="w-full text-xs"><thead><tr class="border-b border-slate-300 text-left">@foreach (['Tanggal', 'Keterangan', 'Masuk', 'Keluar', 'Saldo'] as $h)<th class="py-1">{{ $h }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">@foreach ($live as $e)<tr><td class="py-1">{{ Fmt::date($e['date']) }}</td><td class="py-1">{{ $e['desc'] }}</td><td class="py-1">{{ $e['in'] ? Fmt::rp($e['in']) : '' }}</td><td class="py-1">{{ $e['out'] ? Fmt::rp($e['out']) : '' }}</td><td class="py-1">{{ Fmt::rp($e['balance']) }}</td></tr>@endforeach</tbody></table></section>
        <button onclick="window.print()" class="w-full rounded-md bg-brand px-4 py-2.5 font-medium text-white print:hidden">Cetak / Simpan PDF</button>
    </div>
</body>
</html>
