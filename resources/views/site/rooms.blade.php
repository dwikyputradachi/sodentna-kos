@php use App\Support\Owner\Fmt; @endphp
<x-site.layout title="Ketersediaan Kamar" active="rooms">
    <div><h1 class="text-2xl font-semibold text-navy">Ketersediaan Kamar</h1>
        <p class="mt-1 text-sm text-slate-500">Status diperbarui oleh pengelola. Kamar dipastikan setelah pengajuan disetujui.</p></div>

    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($types as $slug => $t)
            <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-4">
                <div><div class="font-medium">{{ $t['name'] }}</div><div class="text-sm text-slate-500">{{ $t['price'] }}/bulan · maks. {{ $t['cap'] }} orang</div></div>
                <a href="{{ route('site.room', $slug) }}" class="text-sm font-medium text-brand-700">Detail</a>
            </div>
        @endforeach
    </div>

    @foreach ($floors as $floor => $rooms)
        <x-owner.card :title="$floor">
            <div class="grid grid-cols-3 gap-3 p-4 sm:grid-cols-6">
                @foreach ($rooms as $r)
                    <a @if ($r['st'] === 'Tersedia') href="{{ route('site.apply', ['kamar' => $r['id']]) }}" @endif class="block rounded-lg border p-3 text-center {{ match($r['st']) { 'Tersedia' => 'border-emerald-200 bg-emerald-50/50', 'Dipesan' => 'border-amber-200 bg-amber-50/50', default => 'border-slate-200 bg-slate-50 text-slate-400' } }}">
                        <div class="font-semibold">{{ $r['code'] }}</div>
                        <div class="mt-1"><x-owner.badge :label="$r['st']" /></div>
                        @if ($r['st'] === 'Tersedia')<div class="mt-1 text-[11px] font-medium text-brand-700">Ketuk untuk ajukan</div>@endif
                    </a>
                @endforeach
            </div>
        </x-owner.card>
    @endforeach
    <div class="flex flex-wrap items-center justify-between gap-3 rounded-lg border border-brand/30 bg-brand-50 p-4 text-sm text-brand-700">
        <span>Kamar yang Anda inginkan penuh? Gabung daftar tunggu, kami hubungi saat ada yang kosong.</span>
        <a href="{{ route('site.waitlist') }}" class="rounded-md bg-brand px-3 py-1.5 font-medium text-white">Gabung daftar tunggu</a>
    </div>
</x-site.layout>
