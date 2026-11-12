@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Keuangan" active="finance">
    <x-owner.header title="Keuangan" subtitle="Buku kas bulan berjalan." />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-owner.stat label="Pendapatan sewa" :value="Fmt::rp($fin['Sewa'])" />
        <x-owner.stat label="Pengeluaran operasional" :value="Fmt::rp($fin['Pengeluaran'])" />
        <x-owner.stat label="Hasil operasional" :value="Fmt::rp($fin['operating'])" hint="Sewa dikurangi pengeluaran" />
        <x-owner.stat label="Saldo kas akhir" :value="Fmt::rp($fin['opening'] + $fin['net'])" :hint="'Saldo awal '.Fmt::rp($fin['opening'])" />
    </div>

    <div class="rounded-lg border border-brand/30 bg-brand-50 p-3 text-sm text-brand-700">
        Deposit diterima ({{ Fmt::rp($fin['Deposit diterima']) }}) menambah kas tetapi dicatat sebagai titipan penghuni, bukan pendapatan sewa.
    </div>

    <x-owner.card title="Mutasi kas">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>
                @foreach (['Tanggal', 'Keterangan', 'Kamar', 'Jenis', 'Metode', 'Jumlah'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($ledger as $l)
                <tr>
                    <td class="px-4 py-2 text-slate-500">{{ Fmt::date($l['date']) }}</td><td class="px-4 py-2">{{ $l['desc'] }}</td>
                    <td class="px-4 py-2">{{ $l['room'] }}</td><td class="px-4 py-2 text-slate-600">{{ $l['type'] }}</td><td class="px-4 py-2 text-slate-500">{{ $l['method'] }}</td>
                    <td class="px-4 py-2 text-right font-medium {{ $l['amt'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ Fmt::rp($l['amt']) }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
