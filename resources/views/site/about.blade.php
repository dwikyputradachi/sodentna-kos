@php use App\Support\Site\DemoSite; @endphp
<x-site.layout title="Tentang Kos" active="about">
    <div class="max-w-2xl space-y-3">
        <h1 class="text-2xl font-semibold text-navy">Tentang Kos Sodentna</h1>
        <p class="text-slate-600">Kos campur di Sekupang, Batam, dikelola langsung oleh pemilik. Setiap kamar memiliki kamar mandi dalam, dan penghuni mendapat Wi-Fi, pengawasan CCTV, serta area parkir kendaraan.</p>
    </div>
    <div class="grid gap-4 md:grid-cols-2">
        <x-owner.card title="Alamat"><p class="p-4 text-sm">{{ DemoSite::ADDRESS }}</p></x-owner.card>
        <x-owner.card title="Fasilitas">
            <ul class="grid grid-cols-2 gap-1 p-4 text-sm">@foreach (DemoSite::facilities() as $f)<li>• {{ $f }}</li>@endforeach</ul>
        </x-owner.card>
    </div>
    <x-owner.card title="Peraturan singkat">
        <ul class="list-disc space-y-1 p-4 pl-8 text-sm">
            @foreach (\App\Support\Tenant\DemoTenant::rules() as $r)<li>{{ $r }}</li>@endforeach
        </ul>
    </x-owner.card>
</x-site.layout>
