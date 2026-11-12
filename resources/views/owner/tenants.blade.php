@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Penghuni" active="tenants">
    <x-owner.header title="Penghuni" subtitle="Penghuni aktif, calon penghuni, dan proses keluar." />

    <div x-data="{ q: '' }" class="space-y-3">
        <input x-model="q" type="search" placeholder="Cari nama, kamar, atau nomor WhatsApp"
               class="w-full rounded-md border border-slate-200 bg-white px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand sm:w-96">
        <x-owner.card>
            <table class="w-full text-sm">
                <thead class="bg-slate-50"><tr>
                    @foreach (['Penghuni', 'Kamar', 'WhatsApp', 'Sewa berakhir', 'Tarif', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                @foreach ($tenants as $t)
                    <tr x-show="'{{ strtolower($t['name'].' '.$t['room'].' '.$t['ph']) }}'.includes(q.toLowerCase())">
                        <td class="px-4 py-3 font-medium">{{ $t['name'] }}</td>
                        <td class="px-4 py-3">{{ $t['room'] }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $t['ph'] }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ Fmt::date($t['end']) }}</td>
                        <td class="px-4 py-3">{{ Fmt::rp($t['rent']) }}</td>
                        <td class="px-4 py-3"><x-owner.badge :label="$t['st']" /></td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </x-owner.card>
    </div>
</x-owner.layout>
