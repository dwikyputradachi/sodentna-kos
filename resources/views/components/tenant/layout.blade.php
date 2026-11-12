@props(['title' => 'Beranda', 'active' => 'home'])
@php
$nav = [['home', 'Beranda'], ['bills', 'Tagihan'], ['history', 'Riwayat Pembayaran'], ['profile', 'Profil'], ['rules', 'Peraturan Kos']];
$wa = 'https://wa.me/' . \App\Support\Owner\Billing::phone(\App\Support\Tenant\DemoTenant::OWNER_WA) . '?text=' . rawurlencode('Halo, saya penghuni Kos Sodentna dan ingin bertanya.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Portal Penghuni Sodentna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    {{-- Dev convenience: swap for compiled Tailwind (Vite) before production. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a',700:'#1b3a63'},brand:{DEFAULT:'#0f9d94',50:'#e6f6f5',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <header class="bg-navy text-white">
        <div class="mx-auto flex max-w-4xl items-center justify-between px-4 py-3">
            <span class="font-semibold">Sodentna</span>
            <div class="flex items-center gap-3 text-sm text-slate-300"><span>{{ auth()->user()->name ?? 'Penghuni' }}</span><form method="POST" action="{{ route('logout') }}">@csrf<button class="hover:text-white">Keluar</button></form></div>
        </div>
        <nav class="mx-auto flex max-w-4xl gap-1 overflow-x-auto px-3 pb-2 text-sm">
            @foreach ($nav as [$k, $l])
                <a href="{{ route('tenant.' . $k) }}" class="whitespace-nowrap rounded-md px-3 py-1.5 {{ $active === $k ? 'bg-white/15 font-medium' : 'text-slate-300 hover:text-white' }}">{{ $l }}</a>
            @endforeach
        </nav>
    </header>
    <main class="mx-auto max-w-4xl space-y-5 px-4 py-6">{{ $slot }}</main>
    <footer class="mx-auto max-w-4xl px-4 pb-10">
        <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-brand/30 bg-brand-50 p-4 text-sm text-brand-700">
            <span>Butuh bantuan? Hubungi pengelola via WhatsApp.</span>
            <a href="{{ $wa }}" target="_blank" rel="noopener" class="rounded-md bg-brand px-3 py-1.5 font-medium text-white">Hubungi pengelola</a>
        </div>
    </footer>
</body>
</html>
