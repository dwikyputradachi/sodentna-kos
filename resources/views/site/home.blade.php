@php use App\Support\Owner\Fmt; use App\Support\Site\DemoSite; @endphp
<x-site.layout title="Beranda" active="home">
    <section class="grid items-center gap-8 md:grid-cols-2">
        <div class="space-y-4">
            <span class="inline-block rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">{{ $free }} kamar tersedia saat ini</span>
            <h1 class="text-3xl font-semibold leading-tight text-navy sm:text-4xl">Kos nyaman di Sekupang, siap ditempati.</h1>
            <p class="max-w-md text-slate-600">Kamar dengan AC dan kamar mandi dalam, Wi-Fi, CCTV, dan parkir. Ajukan sewa online, bayar, dan pantau tagihan dari satu tempat.</p>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('site.apply') }}" class="rounded-md bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Ajukan sewa</a>
                <a href="{{ route('site.rooms') }}" class="rounded-md border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium">Lihat ketersediaan</a>
            </div>
        </div>
        <div class="flex aspect-[4/3] items-end rounded-xl bg-gradient-to-br from-navy to-brand p-5 text-white">
            <div><div class="text-sm text-white/70">Jl. Kartini VI, Sekupang</div><div class="text-lg font-semibold">Kos Sodentna</div></div>
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-semibold text-navy">Pilihan kamar</h2>
        <div class="grid gap-4 md:grid-cols-2">
            @foreach ($types as $slug => $t)
                <a href="{{ route('site.room', $slug) }}" class="rounded-lg border border-slate-200 bg-white p-5 hover:border-brand">
                    <div class="flex items-start justify-between"><h3 class="font-semibold text-navy">{{ $t['name'] }}</h3><span class="text-xs text-slate-500">{{ $t['size'] }} · {{ $t['cap'] }} orang</span></div>
                    <div class="mt-3 text-2xl font-semibold text-navy">{{ $t['price'] }}<span class="text-sm font-normal text-slate-500"> / bulan</span></div>
                    @if ($t['daily'])<div class="text-sm text-slate-500">atau {{ Fmt::rp($t['daily']) }} / hari</div>@endif
                    <p class="mt-3 text-sm text-slate-600">{{ $t['note'] }}</p>
                </a>
            @endforeach
        </div>
    </section>

    <section class="space-y-3">
        <h2 class="text-lg font-semibold text-navy">Fasilitas kamar</h2>
        <div class="flex flex-wrap gap-2">
            @foreach (DemoSite::facilities() as $f)<span class="rounded-full border border-slate-200 bg-white px-3 py-1 text-sm">{{ $f }}</span>@endforeach
        </div>
    </section>
</x-site.layout>
