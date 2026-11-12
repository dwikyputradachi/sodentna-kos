@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Pemeliharaan" active="maintenance">
    <x-owner.header title="Pemeliharaan" subtitle="Tindak lanjut masalah kamar beserta biaya dan progresnya." />

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3">
        <x-owner.stat label="Tiket terbuka" :value="$open" />
        <x-owner.stat label="Total tiket" :value="count($tickets)" />
        <x-owner.stat label="Biaya tercatat" :value="Fmt::rp($cost)" />
    </div>

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>
                @foreach (['Tiket', 'Kamar', 'Masalah', 'Dilaporkan', 'Penanggung jawab', 'Biaya', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($tickets as $t)
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $t['no'] }}</td><td class="px-4 py-3">{{ $t['room'] }}</td><td class="px-4 py-3">{{ $t['issue'] }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($t['date']) }}</td><td class="px-4 py-3">{{ $t['who'] }}</td>
                    <td class="px-4 py-3">{{ $t['cost'] ? Fmt::rp($t['cost']) : '—' }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$t['st']" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
