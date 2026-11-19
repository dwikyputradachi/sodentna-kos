@php use App\Support\Owner\Fmt; $t = $st['totals']; $q = request()->only(['bulan', 'tahun']); @endphp
<x-owner.layout title="Laporan" active="reports">
    <x-owner.header title="Laporan keuangan" :subtitle="$property->name . ' · ' . $p['label']">
        <a href="{{ route('owner.reports.excel', $q) }}" class="rounded-md bg-brand px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Unduh Excel</a>
        <a href="{{ route('owner.reports.print', $q) }}" target="_blank" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium">Cetak / PDF</a>
    </x-owner.header>
    @include('owner._period', ['route' => 'owner.reports', 'p' => $p])

    <div class="grid gap-4 lg:grid-cols-2">
        <x-owner.card title="Hasil operasional" note="Pendapatan sewa dikurangi pengeluaran. Deposit tidak dihitung.">
            <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
                <tr class="bg-emerald-50/50"><td class="px-4 py-2 font-medium">Pendapatan sewa</td><td class="px-4 py-2 text-right font-medium">{{ Fmt::rp($t['rent']) }}</td></tr>
                @forelse ($byCategory as $cat => $amt)<tr><td class="px-4 py-2 pl-8 text-slate-600">{{ $cat }}</td><td class="px-4 py-2 text-right text-red-600">-{{ Fmt::rp($amt) }}</td></tr>@empty<tr><td class="px-4 py-2 pl-8 text-slate-400" colspan="2">Tidak ada pengeluaran.</td></tr>@endforelse
                <tr class="bg-slate-50"><td class="px-4 py-2 font-semibold">Hasil operasional</td><td class="px-4 py-2 text-right font-semibold text-navy">{{ Fmt::rp($t['operating']) }}</td></tr>
            </tbody></table>
        </x-owner.card>
        <x-owner.card title="Arus kas" note="Semua uang yang benar-benar masuk dan keluar.">
            <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
                <tr><td class="px-4 py-2">Saldo awal</td><td class="px-4 py-2 text-right">{{ Fmt::rp($st['opening']) }}</td></tr>
                <tr><td class="px-4 py-2">Kas masuk — sewa</td><td class="px-4 py-2 text-right text-emerald-600">{{ Fmt::rp($t['rent']) }}</td></tr>
                <tr><td class="px-4 py-2">Kas masuk — titipan deposit</td><td class="px-4 py-2 text-right text-emerald-600">{{ Fmt::rp($t['deposit']) }}</td></tr>
                <tr><td class="px-4 py-2">Kas keluar — pengeluaran</td><td class="px-4 py-2 text-right text-red-600">-{{ Fmt::rp($t['expense']) }}</td></tr>
                <tr class="bg-slate-50"><td class="px-4 py-2 font-semibold">Saldo akhir</td><td class="px-4 py-2 text-right font-semibold text-navy">{{ Fmt::rp($st['closing']) }}</td></tr>
                <tr><td class="px-4 py-2 text-slate-500">Titipan deposit tercatat (s/d akhir periode)</td><td class="px-4 py-2 text-right text-slate-500">{{ Fmt::rp($depositHeld) }}</td></tr>
            </tbody></table>
        </x-owner.card>
    </div>

    <x-owner.card title="Rincian transaksi">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Tanggal', 'Keterangan', 'Referensi', 'Masuk', 'Keluar', 'Saldo'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($live as $e)
                <tr><td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ Fmt::date($e['date']) }}</td><td class="px-4 py-2">{{ $e['desc'] }}</td><td class="px-4 py-2 text-xs text-slate-500">{{ $e['ref'] }}</td>
                    <td class="px-4 py-2 text-emerald-600">{{ $e['in'] ? Fmt::rp($e['in']) : '' }}</td><td class="px-4 py-2 text-red-600">{{ $e['out'] ? Fmt::rp($e['out']) : '' }}</td><td class="px-4 py-2">{{ Fmt::rp($e['balance']) }}</td></tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada transaksi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
