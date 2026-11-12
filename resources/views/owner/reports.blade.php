@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Laporan" active="reports">
    <x-owner.header title="Laporan" subtitle="Ringkasan periode berjalan; mengacu pada buku kas.">
        <button disabled class="cursor-not-allowed rounded-md border border-slate-200 bg-white px-3 py-2 text-sm opacity-50" title="Tersedia di Fase 6">Ekspor PDF</button>
        <button disabled class="cursor-not-allowed rounded-md border border-slate-200 bg-white px-3 py-2 text-sm opacity-50" title="Tersedia di Fase 6">Ekspor Excel</button>
    </x-owner.header>

    <x-owner.card title="Laba rugi operasional">
        <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
            <tr class="bg-emerald-50/50"><td class="px-4 py-2 font-medium">Pendapatan sewa</td><td class="px-4 py-2 text-right font-medium">{{ Fmt::rp($fin['Sewa']) }}</td></tr>
            @foreach ($exp as $name => $amt)
                <tr><td class="px-4 py-2 pl-8 text-slate-600">{{ $name }}</td><td class="px-4 py-2 text-right text-red-600">-{{ Fmt::rp($amt) }}</td></tr>
            @endforeach
            <tr class="bg-slate-50"><td class="px-4 py-2 font-semibold">Hasil operasional</td><td class="px-4 py-2 text-right font-semibold text-navy">{{ Fmt::rp($fin['operating']) }}</td></tr>
        </tbody></table>
    </x-owner.card>

    <x-owner.card title="Arus kas" note="Deposit dipisahkan dari pendapatan.">
        <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
            <tr><td class="px-4 py-2">Saldo awal</td><td class="px-4 py-2 text-right">{{ Fmt::rp($fin['opening']) }}</td></tr>
            <tr><td class="px-4 py-2">Kas masuk (sewa + deposit)</td><td class="px-4 py-2 text-right text-emerald-600">{{ Fmt::rp($fin['in']) }}</td></tr>
            <tr><td class="px-4 py-2 pl-8 text-slate-500">Deposit diterima (titipan)</td><td class="px-4 py-2 text-right text-slate-500">{{ Fmt::rp($fin['Deposit diterima']) }}</td></tr>
            <tr><td class="px-4 py-2">Kas keluar (pengeluaran + refund deposit)</td><td class="px-4 py-2 text-right text-red-600">-{{ Fmt::rp($fin['out']) }}</td></tr>
            <tr><td class="px-4 py-2 pl-8 text-slate-500">Refund deposit</td><td class="px-4 py-2 text-right text-slate-500">{{ Fmt::rp($fin['Refund deposit']) }}</td></tr>
            <tr class="bg-slate-50"><td class="px-4 py-2 font-semibold">Saldo akhir</td><td class="px-4 py-2 text-right font-semibold text-navy">{{ Fmt::rp($fin['opening'] + $fin['net']) }}</td></tr>
        </tbody></table>
    </x-owner.card>
</x-owner.layout>
