@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Dokumen & Kuitansi" active="documents">
    <x-owner.header title="Dokumen & Kuitansi" subtitle="Semua kuitansi pembayaran. Cari berdasarkan nomor, nama, atau kamar." />
    <form method="GET" action="{{ route('owner.documents') }}" class="flex flex-wrap gap-2">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nomor kuitansi, nama, atau kamar" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-base sm:w-96 sm:text-sm">
        <button class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium">Cari</button>
    </form>
    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Nomor', 'Tanggal', 'Penghuni', 'Kamar', 'Periode', 'Jumlah', 'Aksi'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($payments as $p)
                @php $inv = $p->invoice; @endphp
                <tr class="{{ $p->voided_at ? 'text-slate-400' : '' }}">
                    <td class="px-4 py-3 font-medium {{ $p->voided_at ? 'line-through' : '' }}">{{ $p->receipt->number }}@if ($p->voided_at)<div class="text-[11px] font-normal text-red-500 no-underline">Dibatalkan</div>@endif</td>
                    <td class="whitespace-nowrap px-4 py-3">{{ Fmt::date($p->paid_on) }}</td>
                    <td class="px-4 py-3">{{ $inv->rental->tenant->name }}</td>
                    <td class="px-4 py-3">{{ $inv->rental->room->code }}</td>
                    <td class="px-4 py-3">{{ $inv->periodLabel() }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($p->amount) }}</td>
                    <td class="px-4 py-3"><div class="flex flex-wrap items-center gap-2">
                        <a href="{{ $p->receipt->url() }}" target="_blank" class="text-xs font-medium text-brand-700">Lihat / cetak</a>
                        @unless ($p->voided_at)<form method="POST" action="{{ route('owner.billing.send', [$inv, 'receipt']) }}" target="_blank">@csrf<button class="rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white">Kirim via WhatsApp</button></form>@endunless
                    </div></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada kuitansi.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
