@php use App\Support\Owner\Fmt; @endphp
<x-tenant.layout title="Riwayat Pembayaran" active="history">
    <div><h1 class="text-xl font-semibold text-navy">Riwayat Pembayaran</h1><p class="text-sm text-slate-500">Pembayaran yang sudah dicatat beserta kuitansinya.</p></div>
    <x-owner.card>
        @if ($payments->isEmpty())
            <p class="p-6 text-center text-sm text-slate-500">Belum ada pembayaran tercatat.</p>
        @else
            <table class="w-full text-sm">
                <thead class="bg-slate-50"><tr>@foreach (['Periode', 'Tanggal bayar', 'Jumlah', 'Metode', 'Kuitansi'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($payments as $p)
                    <tr>
                        <td class="px-4 py-3 font-medium">{{ $p->invoice->periodLabel() }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ Fmt::date($p->paid_on) }}</td>
                        <td class="px-4 py-3">{{ Fmt::rp($p->amount) }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $p->methodLabel() }}</td>
                        <td class="px-4 py-3">@if ($p->receipt)<a href="{{ $p->receipt->url() }}" target="_blank" class="font-medium text-brand-700">{{ $p->receipt->number }}</a>@else — @endif</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        @endif
    </x-owner.card>
</x-tenant.layout>
