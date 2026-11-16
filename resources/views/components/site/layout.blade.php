@props(['title' => 'Beranda', 'active' => 'home'])
@php
$nav = [['home', 'Beranda'], ['about', 'Tentang Kos'], ['rooms', 'Ketersediaan Kamar'], ['waitlist', 'Daftar Tunggu'], ['apply', 'Pengajuan Sewa'], ['status', 'Status Pengajuan']];
$wa = 'https://wa.me/' . \App\Support\Owner\Billing::phone(\App\Support\Site\DemoSite::WA) . '?text=' . rawurlencode('Halo, saya ingin bertanya tentang Kos Sodentna.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Kos Sodentna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    {{-- Dev convenience: swap for compiled Tailwind (Vite) before production. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a',700:'#1b3a63'},brand:{DEFAULT:'#0f9d94',50:'#e6f6f5',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak]{display:none!important}</style>
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <header class="border-b border-slate-200 bg-white">
        <div class="mx-auto flex max-w-6xl items-center justify-between px-4 py-3">
            <a href="{{ route('site.home') }}" class="font-semibold text-navy">Kos Sodentna</a>
            @auth
                <a href="{{ route(auth()->user()->isStaff() ? 'owner.dashboard' : 'tenant.home') }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium">Dashboard</a>
            @else
                <a href="{{ route('login') }}" class="rounded-md border border-slate-200 px-3 py-1.5 text-sm font-medium">Masuk</a>
            @endauth
        </div>
        <nav class="mx-auto flex max-w-6xl gap-1 overflow-x-auto px-3 pb-2 text-sm">
            @foreach ($nav as [$k, $l])
                <a href="{{ route('site.' . $k) }}" class="whitespace-nowrap rounded-md px-3 py-1.5 {{ $active === $k ? 'bg-brand-50 font-medium text-brand-700' : 'text-slate-500 hover:text-navy' }}">{{ $l }}</a>
            @endforeach
        </nav>
    </header>
    <main class="mx-auto max-w-6xl space-y-8 px-4 py-8">{{ $slot }}</main>
    <footer class="mt-8 bg-navy text-slate-300">
        <div class="mx-auto flex max-w-6xl flex-wrap justify-between gap-4 px-4 py-8 text-sm">
            <div><div class="font-semibold text-white">Kos Sodentna</div><p class="mt-1 max-w-xs">{{ \App\Support\Site\DemoSite::ADDRESS }}</p></div>
            <div><div class="font-medium text-white">Hubungi pemilik</div><a href="{{ $wa }}" target="_blank" rel="noopener" class="mt-1 inline-block underline">WhatsApp 0813 6444 9270</a></div>
        </div>
    </footer>
</body>
</html>
