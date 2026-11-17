@props(['title' => 'Beranda', 'active' => 'home'])
@php
    $nav = [
        ['home', 'Beranda'],
        ['about', 'Tentang Kos'],
        ['rooms', 'Ketersediaan Kamar'],
        ['waitlist', 'Daftar Tunggu'],
        ['apply', 'Pengajuan Sewa'],
        ['status', 'Status Pengajuan'],
    ];
    $wa = 'https://wa.me/' . \App\Support\Owner\Billing::phone(\App\Support\Site\DemoSite::WA) . '?text=' . rawurlencode('Halo, saya ingin bertanya tentang Kos Sodentna.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · Kos Sodentna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    {{-- Dev convenience: swap for compiled Tailwind (Vite) before production. --}}
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { DEFAULT: '#12294a', 700: '#1b3a63', 800: '#163259', 900: '#0d1e36' },
                        brand: { DEFAULT: '#0f9d94', 50: '#e6f6f5', 100: '#ccf0ed', 600: '#0d8a82', 700: '#0b7a73' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="flex min-h-screen flex-col bg-slate-50 font-sans text-slate-800 antialiased">

    {{-- HEADER --}}
    <header x-data="{ mobileMenu: false }" class="sticky top-0 z-40 border-b border-slate-200/90 bg-white/95 backdrop-blur-xs">
        <div class="mx-auto flex max-w-6xl items-center justify-between gap-4 px-4 py-3.5 sm:px-6">
            {{-- Logo Brand --}}
            <a href="{{ route('site.home') }}" class="inline-flex shrink-0 items-center gap-2.5 text-navy transition hover:opacity-90">
                <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand text-sm font-bold text-white shadow-2xs">S</span>
                <span class="text-base font-bold tracking-tight">SODENTNA<span class="text-brand">.</span></span>
            </a>

            {{-- Navigasi Desktop --}}
            <nav aria-label="Navigasi utama" class="hidden items-center gap-1 lg:flex">
                @foreach ($nav as [$k, $l])
                    <a
                        href="{{ route('site.' . $k) }}"
                        class="rounded-lg px-3 py-2 text-sm transition {{ $active === $k ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-slate-600 hover:bg-slate-100/80 hover:text-navy' }}"
                    >
                        {{ $l }}
                    </a>
                @endforeach
            </nav>

            {{-- Aksi Kanan (Masuk / Dashboard + Hamburger Mobile) --}}
            <div class="flex items-center gap-2">
                @auth
                    <a
                        href="{{ route(auth()->user()->isStaff() ? 'owner.dashboard' : 'tenant.home') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-navy px-3.5 py-2 text-xs font-semibold text-white transition hover:bg-navy-700 sm:text-sm"
                    >
                        <svg class="h-4 w-4 text-brand-100" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                        </svg>
                        <span>Dashboard</span>
                    </a>
                @else
                    <a
                        href="{{ route('login') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-navy transition hover:border-slate-400 hover:bg-slate-50 sm:text-sm"
                    >
                        <svg class="h-4 w-4 text-slate-500" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                        </svg>
                        <span>Masuk</span>
                    </a>
                @endauth

                {{-- Tombol Menu Mobile --}}
                <button
                    type="button"
                    @click="mobileMenu = !mobileMenu"
                    :aria-expanded="mobileMenu"
                    aria-label="Buka menu navigasi"
                    class="inline-flex items-center justify-center rounded-lg border border-slate-200 p-2 text-slate-600 transition hover:bg-slate-100 hover:text-navy lg:hidden"
                >
                    <svg x-show="!mobileMenu" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    <svg x-show="mobileMenu" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>

        {{-- Dropdown Navigasi Mobile --}}
        <nav
            x-show="mobileMenu"
            x-cloak
            x-transition.opacity.duration.150ms
            aria-label="Navigasi mobile"
            class="border-t border-slate-100 bg-white px-4 py-3 lg:hidden"
        >
            <div class="grid gap-1">
                @foreach ($nav as [$k, $l])
                    <a
                        href="{{ route('site.' . $k) }}"
                        class="rounded-lg px-3 py-2.5 text-sm transition {{ $active === $k ? 'bg-brand-50 font-semibold text-brand-700' : 'font-medium text-slate-600 hover:bg-slate-50 hover:text-navy' }}"
                    >
                        {{ $l }}
                    </a>
                @endforeach
            </div>
        </nav>
    </header>

    {{-- KONTEN UTAMA --}}
    <main class="mx-auto w-full max-w-6xl flex-1 space-y-8 px-4 py-8 sm:px-6 sm:py-10">
        {{ $slot }}
    </main>

    {{-- FOOTER --}}
    <footer class="mt-12 bg-navy text-slate-300">
        <div class="mx-auto max-w-6xl px-4 py-10 sm:px-6">
            <div class="flex flex-col justify-between gap-8 sm:flex-row sm:items-start">
                {{-- Kolom Kiri: Brand & Alamat --}}
                <div class="space-y-2.5">
                    <div class="inline-flex items-center gap-2 text-white">
                        <span class="flex h-6 w-6 items-center justify-center rounded-md bg-brand text-xs font-bold text-white">S</span>
                        <span class="text-base font-bold tracking-tight">SODENTNA<span class="text-brand">.</span></span>
                    </div>
                    <p class="max-w-sm text-sm leading-relaxed text-slate-300">
                        Hunian terawat dengan suasana tenang dan pengelolaan langsung oleh pemilik.
                    </p>
                    <p class="max-w-sm text-xs text-slate-400">
                        {{ \App\Support\Site\DemoSite::ADDRESS }}
                    </p>
                </div>

                {{-- Kolom Kanan: Kontak Pemilik --}}
                <div class="space-y-2 sm:text-right">
                    <div class="text-sm font-semibold text-white">Hubungi pemilik</div>
                    <div>
                        <a
                            href="{{ $wa }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-100 underline decoration-brand/50 underline-offset-4 transition hover:text-white"
                        >
                            <span>WhatsApp 0813 6444 9270</span>
                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                            </svg>
                        </a>
                    </div>
                    <p class="text-xs text-slate-400">Senin–Sabtu, 09.00–17.00 WIB</p>
                </div>
            </div>

            {{-- Bottom Bar --}}
            <div class="mt-8 flex flex-col items-center justify-between gap-3 border-t border-white/10 pt-6 text-xs text-slate-400 sm:flex-row">
                <p>© {{ date('Y') }} Kos Sodentna · Sekupang, Kota Batam</p>
                <div class="flex items-center gap-4">
                    <a href="{{ route('site.about') }}" class="transition hover:text-white">Tentang &amp; Peraturan Kos</a>
                    <a href="{{ route('site.rooms') }}" class="transition hover:text-white">Cek Kamar</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>