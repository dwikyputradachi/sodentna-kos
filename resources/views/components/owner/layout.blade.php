@props(['title' => 'Dashboard', 'active' => 'dashboard'])
@php
$nav = [
    ['dashboard', 'Dashboard'], ['rooms', 'Kamar & Lantai'], ['tenants', 'Penghuni'], ['applications', 'Pengajuan Sewa'],
    ['billing', 'Tagihan & Pembayaran'], ['finance', 'Keuangan'], ['deposits', 'Deposit'], ['reports', 'Laporan'],
    ['maintenance', 'Pemeliharaan'], ['inventory', 'Persediaan Barang'], ['documents', 'Dokumen & Kuitansi'], ['settings', 'Pengaturan'],
];
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Sodentna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    {{-- Dev convenience: swap for compiled Tailwind (Vite) before production. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a',700:'#1b3a63'},brand:{DEFAULT:'#0f9d94',50:'#e6f6f5',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased" x-data="{ open: false }">
    <div class="flex min-h-screen">
        <aside class="fixed inset-y-0 left-0 z-30 w-64 -translate-x-full bg-navy text-slate-300 transition-transform lg:static lg:translate-x-0"
               :class="{ '!translate-x-0': open }">
            <div class="border-b border-white/10 px-5 py-4">
                <div class="font-semibold text-white">Kos Sodentna</div>
                <div class="text-xs text-slate-400">Kartini 6 · Batam</div>
            </div>
            <nav class="space-y-0.5 p-3 text-sm">
                @foreach ($nav as [$key, $label])
                    <a href="{{ route('owner.' . $key) }}"
                       class="block rounded-md border-l-2 px-3 py-2 {{ $active === $key ? 'border-brand bg-white/10 font-medium text-white' : 'border-transparent hover:bg-white/5 hover:text-white' }}">{{ $label }}</a>
                @endforeach
            </nav>
        </aside>
        <div x-show="open" x-cloak @click="open=false" class="fixed inset-0 z-20 bg-black/40 lg:hidden"></div>

        <div class="min-w-0 flex-1">
            <header class="flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 lg:px-8">
                <button @click="open=true" class="rounded border border-slate-200 px-2 py-1 text-sm lg:hidden" aria-label="Buka menu">Menu</button>
                <div class="text-sm text-slate-500">{{ \App\Support\Owner\Fmt::date(\App\Support\Owner\DemoData::today(), 'l, j F Y') }}</div>
                <div class="flex items-center gap-3 text-sm"><span class="font-medium">{{ auth()->user()->name ?? 'Pemilik' }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="text-slate-500 hover:text-navy">Keluar</button></form></div>
            </header>
            <main class="mx-auto max-w-7xl space-y-6 p-4 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
