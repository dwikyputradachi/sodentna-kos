@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Dokumen & Kuitansi" active="documents">
    <x-owner.header title="Dokumen & Kuitansi" subtitle="Kuitansi pembayaran yang sudah disetujui." />
    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>
                @foreach (['Nomor', 'Penghuni', 'Kamar', 'Tanggal', 'Jumlah', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($receipts as $r)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $r['no'] }}</td><td class="px-4 py-3">{{ $r['who'] }}</td><td class="px-4 py-3">{{ $r['room'] }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($r['date']) }}</td><td class="px-4 py-3">{{ Fmt::rp($r['amt']) }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$r['st']" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
