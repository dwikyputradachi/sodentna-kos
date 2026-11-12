@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Deposit" active="deposits">
    <x-owner.header title="Deposit" subtitle="Dana titipan penghuni, bukan pendapatan sewa." />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
        <x-owner.stat label="Deposit ditahan" :value="Fmt::rp($held)" />
        <x-owner.stat label="Potongan disetujui" :value="Fmt::rp($deducted)" hint="Harus ada rincian dan bukti" />
        <x-owner.stat label="Sudah dikembalikan" :value="Fmt::rp($refunded)" />
    </div>

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>
                @foreach (['Penghuni', 'Kamar', 'Deposit awal', 'Potongan', 'Dikembalikan', 'Diterima', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($rows as $d)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $d['who'] }}</td><td class="px-4 py-3">{{ $d['room'] }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($d['init']) }}</td>
                    <td class="px-4 py-3">{{ $d['ded'] ? Fmt::rp($d['ded']) : '—' }}</td>
                    <td class="px-4 py-3">{{ $d['st'] === 'Dikembalikan' ? Fmt::rp($d['refund']) : '—' }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($d['date']) }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$d['st']" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>
    <p class="text-xs text-slate-500">Pengembalian = deposit awal dikurangi potongan yang disetujui. Proses pengembalian dibuat di Fase 8.</p>
</x-owner.layout>
