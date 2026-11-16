@php use App\Support\Owner\Fmt; @endphp
<x-tenant.layout title="Peraturan Kos" active="rules">
    <div><h1 class="text-xl font-semibold text-navy">Peraturan Kos</h1><p class="text-sm text-slate-500">Ketentuan yang berlaku untuk seluruh penghuni.</p></div>
    <x-owner.card>
        <ol class="list-decimal space-y-2 p-4 pl-9 text-sm leading-relaxed">@foreach ($rules as $r)<li>{{ $r }}</li>@endforeach</ol>
    </x-owner.card>
    @if ($acked)<p class="text-sm text-slate-500">Anda menyetujui peraturan ini pada {{ Fmt::date($acked->timezone('Asia/Jakarta')) }}.</p>@endif
</x-tenant.layout>
