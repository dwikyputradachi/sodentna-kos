@php use App\Support\Owner\Fmt; $tabs = ['aktif' => 'Aktif', 'dipesan' => 'Dipesan', 'alumni' => 'Alumni']; @endphp
<x-owner.layout title="Penghuni" active="tenants">
    <x-owner.header title="Penghuni" subtitle="Cari berdasarkan nama, nomor kamar, atau nomor WhatsApp.">
        <a href="{{ route('owner.tenants.create') }}" class="rounded-md bg-brand px-3 py-2 text-sm font-medium text-white hover:bg-brand-700">Tambah penghuni</a>
    </x-owner.header>

    <form method="GET" action="{{ route('owner.tenants') }}" class="flex flex-wrap gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, nomor kamar, atau WhatsApp" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-base focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand sm:w-96 sm:text-sm">
        <button class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium">Cari</button>
        @if ($q !== '')<a href="{{ route('owner.tenants', ['tab' => $tab]) }}" class="px-2 py-2 text-sm text-slate-500">Hapus pencarian</a>@endif
    </form>

    <div class="flex gap-2 text-sm">
        @foreach ($tabs as $k => $l)
            <a href="{{ route('owner.tenants', ['tab' => $k, 'q' => $q ?: null]) }}" class="rounded-md px-3 py-1.5 {{ $tab === $k ? 'bg-navy text-white' : 'border border-slate-300 bg-white' }}">{{ $l }} ({{ $counts[$k] }})</a>
        @endforeach
    </div>

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>
                @foreach (array_merge(['Penghuni', 'Kamar', 'WhatsApp', $tab === 'alumni' ? 'Periode terakhir' : 'Mulai', 'Tarif'], $tab === 'alumni' ? [] : ['Tagihan'], ['Status']) as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($rows as $row)
                @php $t = $row['t']; $rt = $row['r']; @endphp
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3"><a href="{{ route('owner.tenants.show', $t) }}" class="font-medium text-brand-700">{{ $t->name }}</a>@if ($rt->co_occupant_name)<div class="text-xs text-slate-500">& {{ $rt->co_occupant_name }}</div>@endif</td>
                    <td class="px-4 py-3">{{ $rt->room->code }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $t->phone }}</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $tab === 'alumni' ? Fmt::date($rt->start_date) . ' – ' . Fmt::date($rt->end_date) : Fmt::date($rt->start_date) }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($rt->rate) }}</td>
                    @if ($tab !== 'alumni')
                        @php $iv = $invoices[$rt->id] ?? null; $st = $iv ? \App\Support\InvoiceState::of($iv) : null; @endphp
                        <td class="px-4 py-3">
                            @if ($iv)
                                <x-owner.badge :label="$st['label']" />
                                @if ($st['action'])
                                    <form method="POST" action="{{ route('owner.billing.send', [$iv, $st['action']]) }}" target="_blank" class="mt-1">@csrf
                                        <button class="rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white hover:bg-brand-700">{{ \App\Support\Owner\Billing::actionLabel($st['action']) }}</button></form>
                                @endif
                            @else<span class="text-xs text-slate-400">—</span>@endif
                        </td>
                    @endif
                    <td class="px-4 py-3"><x-owner.badge :label="$tab === 'alumni' ? 'Alumni' : $rt->statusLabel()" /></td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-8 text-center text-slate-500">{{ $q !== '' ? 'Tidak ada hasil untuk "' . $q . '" di tab ini.' : 'Belum ada data di tab ini.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
