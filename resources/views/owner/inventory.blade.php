@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Persediaan Barang" active="inventory">
    <x-owner.header title="Persediaan Barang" subtitle="Stok perlengkapan dan perbaikan. Berbeda dari catatan pemeliharaan.">
        <button disabled class="cursor-not-allowed rounded-md bg-brand px-3 py-2 text-sm font-medium text-white opacity-50" title="Tersedia di Fase 7">Tambah barang</button>
    </x-owner.header>

    <div class="grid grid-cols-3 gap-3">
        <x-owner.stat label="Jenis barang" :value="count($items)" />
        <x-owner.stat label="Stok menipis / habis" :value="$low" />
        <x-owner.stat label="Nilai stok" :value="Fmt::rp($value)" />
    </div>

    <x-owner.card title="Daftar barang">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Barang', 'Stok', 'Minimum', 'Harga beli', 'Lokasi', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($items as $i)
                <tr><td class="px-4 py-3 font-medium">{{ $i['n'] }}</td><td class="px-4 py-3">{{ $i['stock'] }} {{ $i['unit'] }}</td><td class="px-4 py-3 text-slate-500">{{ $i['min'] }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($i['cost']) }}</td><td class="px-4 py-3 text-slate-500">{{ $i['loc'] }}</td><td class="px-4 py-3"><x-owner.badge :label="$i['st']" /></td></tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>

    <x-owner.card title="Mutasi stok terbaru">
        <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
            @foreach ($moves as $m)
                <tr><td class="px-4 py-2 text-slate-500">{{ Fmt::date($m['date']) }}</td><td class="px-4 py-2">{{ $m['item'] }}</td>
                    <td class="px-4 py-2 font-medium {{ $m['type'] === 'Masuk' ? 'text-emerald-600' : 'text-red-600' }}">{{ $m['type'] === 'Masuk' ? '+' : '-' }}{{ $m['qty'] }}</td><td class="px-4 py-2 text-slate-500">{{ $m['ref'] }}</td></tr>
            @endforeach
        </tbody></table>
    </x-owner.card>
</x-owner.layout>
