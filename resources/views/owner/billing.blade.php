@php
use App\Support\Owner\Billing;
use App\Support\Owner\Fmt;
$tabs = ['perhatian' => 'Perlu perhatian', 'belum' => 'Belum jatuh tempo', 'lunas' => 'Lunas', 'semua' => 'Semua'];
$in = 'w-full rounded-md border border-slate-300 px-2.5 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
@endphp
<x-owner.layout title="Tagihan & Pembayaran" active="billing">
    <x-owner.header title="Tagihan & Pembayaran" subtitle="Tombol WhatsApp membuka chat dengan pesan siap kirim; Anda yang menekan Kirim.">
        <a href="{{ route('owner.whatsapp') }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium">Pengiriman otomatis</a>
    </x-owner.header>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if (session('err'))<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('err') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
        <x-owner.stat label="Tagihan bulan ini" :value="Fmt::rp($stats['total'])" />
        <x-owner.stat label="Sudah lunas (bulan ini)" :value="Fmt::rp($stats['paid'])" />
        <x-owner.stat label="Belum dibayar (semua)" :value="Fmt::rp($stats['open'])" />
        <x-owner.stat label="Terlambat" :value="Fmt::rp($stats['late'])" />
    </div>

    <form method="GET" action="{{ route('owner.billing') }}" class="flex flex-wrap gap-2">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari nama, nomor kamar, atau nomor tagihan" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-base focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand sm:w-96 sm:text-sm">
        <button class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium">Cari</button>
    </form>

    <div class="flex flex-wrap gap-2 text-sm">
        @foreach ($tabs as $k => $l)
            <a href="{{ route('owner.billing', ['tab' => $k, 'q' => $q ?: null]) }}" class="rounded-md px-3 py-1.5 {{ $tab === $k ? 'bg-navy text-white' : 'border border-slate-300 bg-white' }}">{{ $l }} ({{ $counts[$k] ?? 0 }})</a>
        @endforeach
    </div>

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Tagihan', 'Penghuni', 'Kamar', 'Nominal', 'Jatuh tempo', 'Status', 'Aksi'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($rows as $row)
                @php $i = $row['i']; $s = $row['s']; $logs = $last[$i->id] ?? collect(); @endphp
                <tr class="align-top">
                    <td class="px-4 py-3"><div class="font-medium">{{ $i->number }}</div><div class="text-xs text-slate-400">{{ $i->periodLabel() }}</div></td>
                    <td class="px-4 py-3"><a href="{{ route('owner.tenants.show', $i->rental->tenant_id) }}" class="font-medium text-brand-700">{{ $i->rental->tenant->name }}</a></td>
                    <td class="px-4 py-3">{{ $i->rental->room->code }}</td>
                    <td class="px-4 py-3">{{ Fmt::rp($i->total_amount) }}@if ($i->deposit_amount > 0)<div class="text-xs text-slate-400">termasuk deposit {{ Fmt::rp($i->deposit_amount) }}</div>@endif</td>
                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ Fmt::date($i->due_date) }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$s['label']" /></td>
                    <td class="px-4 py-3">
                        <div class="flex flex-col items-start gap-1.5">
                            @if ($s['action'])
                                <form method="POST" action="{{ route('owner.billing.send', [$i, $s['action']]) }}" target="_blank">@csrf
                                    <button class="rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white hover:bg-brand-700">{{ Billing::actionLabel($s['action']) }}</button></form>
                                @if ($logs->has($s['action']))<x-owner.log-note :log="$logs[$s['action']]" />@endif
                            @endif

                            @if ($i->status === 'unpaid')
                                <details class="w-64 rounded-md border border-slate-200 bg-white">
                                    <summary class="cursor-pointer px-2.5 py-1 text-xs font-medium text-navy">Catat pembayaran</summary>
                                    <form method="POST" action="{{ route('owner.billing.pay', $i) }}" onsubmit="return confirm('Catat pembayaran {{ Fmt::rp($i->total_amount) }} dari {{ $i->rental->tenant->name }}?')" class="space-y-2 border-t border-slate-100 p-2.5">@csrf
                                        <input type="date" name="paid_on" value="{{ $today->toDateString() }}" max="{{ $today->toDateString() }}" required class="{{ $in }}">
                                        <select name="method" class="{{ $in }}"><option value="transfer">Transfer</option><option value="cash">Tunai</option></select>
                                        <input name="note" maxlength="200" placeholder="Catatan (opsional)" class="{{ $in }}">
                                        <button class="w-full rounded-md bg-navy px-2.5 py-1.5 text-xs font-medium text-white">Simpan pembayaran</button>
                                    </form>
                                </details>
                                <details class="w-64">
                                    <summary class="cursor-pointer px-1 text-[11px] text-slate-400">Batalkan tagihan</summary>
                                    <form method="POST" action="{{ route('owner.billing.void', $i) }}" onsubmit="return confirm('Batalkan tagihan ini?')" class="mt-1 space-y-1.5">@csrf
                                        <input name="reason" required maxlength="200" placeholder="Alasan" class="{{ $in }}">
                                        <button class="rounded-md border border-red-200 px-2.5 py-1 text-xs text-red-700">Batalkan</button>
                                    </form>
                                </details>
                            @elseif ($i->status === 'paid' && $i->payment?->receipt)
                                <form method="POST" action="{{ route('owner.billing.send', [$i, 'receipt']) }}" target="_blank">@csrf
                                    <button class="rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white hover:bg-brand-700">Kirim kuitansi via WhatsApp</button></form>
                                @if ($logs->has('receipt'))<x-owner.log-note :log="$logs['receipt']" />@endif
                                <a href="{{ $i->payment->receipt->url() }}" target="_blank" class="text-xs font-medium text-brand-700">Lihat kuitansi {{ $i->payment->receipt->number }}</a>
                                <div class="text-[11px] text-slate-400">Dibayar {{ Fmt::date($i->payment->paid_on) }} · {{ $i->payment->methodLabel() }}</div>
                                <details class="w-64">
                                    <summary class="cursor-pointer px-1 text-[11px] text-slate-400">Batalkan pembayaran</summary>
                                    <form method="POST" action="{{ route('owner.payments.void', $i->payment) }}" onsubmit="return confirm('Batalkan pembayaran ini? Tagihan kembali belum lunas.')" class="mt-1 space-y-1.5">@csrf
                                        <input name="reason" required maxlength="200" placeholder="Alasan" class="{{ $in }}">
                                        <button class="rounded-md border border-red-200 px-2.5 py-1 text-xs text-red-700">Batalkan pembayaran</button>
                                    </form>
                                </details>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">{{ $q !== '' ? 'Tidak ada hasil.' : 'Tidak ada tagihan di tab ini.' }}</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
    <p class="text-xs text-slate-400">Tagihan bulanan dibuat otomatis 7 hari sebelum jatuh tempo, mengikuti tanggal masuk penghuni.</p>
</x-owner.layout>
