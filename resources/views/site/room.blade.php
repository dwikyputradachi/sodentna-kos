@php use App\Support\Owner\Fmt; use App\Support\Site\DemoSite; @endphp
<x-site.layout :title="$t['name']" active="rooms">
    <a href="{{ route('site.rooms') }}" class="text-sm text-brand-700">Kembali ke ketersediaan</a>
    <div class="grid gap-6 md:grid-cols-3">
        <div class="space-y-5 md:col-span-2">
            <div class="flex aspect-[16/9] items-end rounded-xl bg-gradient-to-br from-navy-700 to-brand p-5 text-white"><span class="text-xl font-semibold">{{ $t['name'] }}</span></div>
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">{{ $t['rule'] }}</div>
            <x-owner.card title="Spesifikasi">
                <dl class="grid grid-cols-2 gap-3 p-4 text-sm"><dt class="text-slate-500">Ukuran</dt><dd>{{ $t['size'] }}</dd><dt class="text-slate-500">Kapasitas</dt><dd>{{ $t['cap'] }} orang</dd><dt class="text-slate-500">Kamar mandi</dt><dd>Dalam</dd><dt class="text-slate-500">Deposit</dt><dd>{{ Fmt::rp($t['deposit']) }} (dikembalikan sesuai ketentuan)</dd></dl>
            </x-owner.card>
            <x-owner.card title="Fasilitas"><ul class="grid grid-cols-2 gap-1 p-4 text-sm">@foreach (DemoSite::facilities() as $f)<li>• {{ $f }}</li>@endforeach</ul></x-owner.card>
        </div>
        <aside class="h-fit space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            <div><div class="text-xs text-slate-500">Bulanan</div><div class="text-2xl font-semibold text-navy">{{ $t['price'] }}</div></div>
            <div><div class="text-xs text-slate-500">Harian</div><div class="text-lg font-semibold text-navy">{{ $t['daily'] ? Fmt::rp($t['daily']) : 'Tidak tersedia' }}</div></div>
            <a href="{{ route('site.apply', ['kamar' => $slug]) }}" class="block rounded-md bg-brand px-4 py-2.5 text-center text-sm font-medium text-white hover:bg-brand-700">Ajukan sewa</a>
        </aside>
    </div>
</x-site.layout>
