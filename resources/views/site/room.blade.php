@php
    use App\Support\Owner\{Billing, Fmt};
    use App\Support\Site\DemoSite;
    $wa = 'https://wa.me/' . Billing::phone(DemoSite::WA) . '?text=' . rawurlencode('Halo, saya ingin bertanya tentang ' . $t['name'] . ' di Kos Sodentna.');
@endphp
<x-site.layout :title="$t['name']" active="rooms">
    <a href="{{ route('site.rooms') }}" class="inline-flex items-center gap-1.5 text-sm font-medium text-brand-700 hover:text-brand">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18"/></svg>
        Kembali ke ketersediaan
    </a>

    <div class="grid gap-6 md:grid-cols-3">
        <div class="space-y-5 md:col-span-2">
            <div class="flex aspect-[16/9] flex-col justify-between rounded-xl bg-gradient-to-br from-navy via-navy-700 to-brand p-5 text-white">
                <div class="flex flex-wrap gap-2 text-xs font-medium">
                    <span class="rounded-full bg-white/15 px-3 py-1 backdrop-blur-xs">Kos Sodentna · Sekupang</span>
                    <span class="rounded-full bg-white/15 px-3 py-1 backdrop-blur-xs">Maks. {{ $t['cap'] }} orang</span>
                </div>
                <div>
                    <p class="text-xs text-white/75">Tipe Kamar · {{ $t['size'] }}</p>
                    <h1 class="text-xl font-semibold sm:text-2xl">{{ $t['name'] }}</h1>
                </div>
            </div>

            <div class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50/80 p-3.5 text-sm text-amber-900">
                <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z"/></svg>
                <span>{{ $t['rule'] }}</span>
            </div>

            <x-owner.card title="Spesifikasi Kamar">
                <dl class="grid grid-cols-2 gap-3 p-4 text-sm">
                    <div class="rounded-lg bg-slate-50 p-3"><dt class="text-xs text-slate-500">Ukuran</dt><dd class="mt-0.5 font-semibold text-navy">{{ $t['size'] }}</dd></div>
                    <div class="rounded-lg bg-slate-50 p-3"><dt class="text-xs text-slate-500">Kapasitas</dt><dd class="mt-0.5 font-semibold text-navy">Maks. {{ $t['cap'] }} orang</dd></div>
                    <div class="rounded-lg bg-slate-50 p-3"><dt class="text-xs text-slate-500">Kamar mandi</dt><dd class="mt-0.5 font-semibold text-navy">Dalam (pribadi)</dd></div>
                    <div class="rounded-lg bg-slate-50 p-3"><dt class="text-xs text-slate-500">Deposit (sekali bayar)</dt><dd class="mt-0.5 font-semibold text-navy">{{ Fmt::rp($t['deposit']) }}</dd></div>
                </dl>
            </x-owner.card>

            <x-owner.card title="Fasilitas">
                <ul class="grid grid-cols-2 gap-2.5 p-4 text-sm text-slate-700 sm:grid-cols-3">
                    @foreach (DemoSite::facilities() as $f)
                        <li class="flex items-center gap-2">
                            <svg class="h-4 w-4 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
                            <span>{{ $f }}</span>
                        </li>
                    @endforeach
                </ul>
            </x-owner.card>
        </div>

        <aside class="h-fit space-y-4 rounded-xl border border-slate-200 bg-white p-5 md:sticky md:top-24">
            <div class="border-b border-slate-100 pb-3.5">
                <div class="text-xs font-medium text-slate-500">Sewa Bulanan</div>
                <div class="mt-0.5 text-2xl font-bold text-navy">{{ $t['price'] }}<span class="text-xs font-normal text-slate-500"> / bulan</span></div>
                <p class="mt-1 text-xs text-slate-500">+ Deposit {{ Fmt::rp($t['deposit']) }} (dana titipan, dikembalikan saat keluar)</p>
            </div>

            <div class="border-b border-slate-100 pb-3.5">
                <div class="text-xs font-medium text-slate-500">Sewa Harian</div>
                <div class="mt-0.5 text-base font-semibold text-navy">{{ $t['daily'] ? Fmt::rp($t['daily']) . ' / malam' : 'Tidak tersedia' }}</div>
            </div>

            <div class="space-y-2">
                <a href="{{ route('site.apply') }}" class="block w-full rounded-lg bg-brand px-4 py-2.5 text-center text-sm font-semibold text-white transition hover:bg-brand-700">Ajukan Sewa</a>
                <a href="{{ $wa }}" target="_blank" rel="noopener noreferrer" class="block w-full rounded-lg border border-slate-300 bg-white px-4 py-2 text-center text-xs font-semibold text-navy transition hover:bg-slate-50">Tanya Pengelola via WhatsApp</a>
            </div>
        </aside>
    </div>
</x-site.layout>