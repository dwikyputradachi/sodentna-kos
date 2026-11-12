@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Tagihan & Pembayaran" active="billing">
    <x-owner.header title="Tagihan & Pembayaran" subtitle="Tombol WhatsApp hanya membuka chat; pesan dikirim manual oleh Anda." />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-owner.stat label="Total tagihan" :value="Fmt::rp($sum['total'])" />
        <x-owner.stat label="Sudah lunas" :value="Fmt::rp($sum['paid'])" />
        <x-owner.stat label="Menunggu verifikasi" :value="Fmt::rp($sum['pending'])" />
        <x-owner.stat label="Terlambat" :value="Fmt::rp($sum['late'])" />
    </div>

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>
                @foreach (['Tagihan', 'Penghuni', 'Kamar', 'Nominal', 'Jatuh tempo', 'Status', 'Aksi'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($invoices as $i)
                <tr>
                    <td class="px-4 py-3"><div class="font-medium">{{ $i['no'] }}</div><div class="text-xs text-slate-400">{{ $i['period'] }}</div></td>
                    <td class="px-4 py-3">{{ $i['tenant'] }}</td>
                    <td class="px-4 py-3">{{ $i['room'] }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($i['amount']) }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($i['due']) }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$i['label']" /></td>
                    <td class="px-4 py-3">
                        @if ($i['wa'])
                            <a href="{{ $i['wa'] }}" target="_blank" rel="noopener" class="inline-block rounded-md bg-brand px-3 py-1.5 text-xs font-medium text-white hover:bg-brand-700">{{ $i['action_label'] }}</a>
                        @elseif ($i['label'] === 'Menunggu Verifikasi')
                            <span class="text-xs text-slate-500">Periksa bukti (Fase 5)</span>
                        @else
                            <span class="text-xs text-slate-400">—</span>
                        @endif
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
