@php use App\Support\Owner\Fmt; @endphp
<x-tenant.layout title="Beranda" active="home">
    <div>
        <h1 class="text-xl font-semibold text-navy">Halo, {{ explode(' ', $profile['name'])[0] }}.</h1>
        <p class="text-sm text-slate-500">Ringkasan sewa dan pembayaran Anda.</p>
    </div>

    <div class="grid gap-4 md:grid-cols-2">
        <x-owner.card title="Kamar Anda">
            <div class="space-y-2 p-4 text-sm">
                <div class="flex items-center justify-between"><span class="text-2xl font-semibold text-navy">{{ $rental['room'] }}</span><x-owner.badge label="Aktif" /></div>
                <div class="text-slate-500">{{ $rental['type'] }} · {{ $rental['floor'] }} · Sewa {{ $rental['mode'] }}</div>
                <div>{{ Fmt::date($rental['start']) }} – {{ Fmt::date($rental['end']) }}</div>
                <div class="font-medium">{{ Fmt::rp($rental['rent']) }} / bulan</div>
                <div class="text-xs text-slate-400">Deposit {{ Fmt::rp($rental['deposit']) }}</div>
            </div>
        </x-owner.card>

        <x-owner.card title="Pembayaran berikutnya">
            <div class="space-y-2 p-4 text-sm">
                @if ($invoice)
                    <div class="text-2xl font-semibold text-navy">{{ Fmt::rp($invoice['amount']) }}</div>
                    <div class="text-slate-500">Sewa {{ $invoice['period'] }} · jatuh tempo {{ Fmt::date($invoice['due']) }}</div>
                    <x-owner.badge :label="$invoice['label']" />
                    <div class="pt-2"><a href="{{ route('tenant.bills') }}" class="inline-block rounded-md bg-brand px-3 py-2 font-medium text-white hover:bg-brand-700">Bayar & unggah bukti</a></div>
                @else
                    <p class="text-slate-500">Tidak ada tagihan berjalan.</p>
                @endif
            </div>
        </x-owner.card>
    </div>

    <x-owner.card title="Pembayaran terakhir">
        <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
            @foreach ($payments as $p)
                <tr><td class="px-4 py-2">{{ $p['period'] }}</td><td class="px-4 py-2 text-slate-500">{{ Fmt::date($p['paid']) }}</td>
                    <td class="px-4 py-2 text-right">{{ Fmt::rp($p['amount']) }}</td><td class="px-4 py-2 text-right"><x-owner.badge label="Lunas" /></td></tr>
            @endforeach
        </tbody></table>
    </x-owner.card>
</x-tenant.layout>
