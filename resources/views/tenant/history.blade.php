@php use App\Support\Owner\Fmt; @endphp
<x-tenant.layout title="Riwayat Pembayaran" active="history">
    <div><h1 class="text-xl font-semibold text-navy">Riwayat Pembayaran</h1><p class="text-sm text-slate-500">Pembayaran yang sudah disetujui beserta kuitansinya.</p></div>
    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Periode', 'Tanggal bayar', 'Jumlah', 'Kuitansi'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($payments as $p)
                <tr><td class="px-4 py-3 font-medium">{{ $p['period'] }}</td><td class="px-4 py-3 text-slate-500">{{ Fmt::date($p['paid']) }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($p['amount']) }}</td>
                    <td class="px-4 py-3"><span class="text-slate-500">{{ $p['receipt'] }}</span> <span class="ml-1 text-xs text-slate-400">(unduh aktif di Fase 6)</span></td></tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>
</x-tenant.layout>
