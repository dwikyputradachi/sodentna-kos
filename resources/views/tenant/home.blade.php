@php use App\Support\Owner\Fmt; @endphp
<x-tenant.layout title="Beranda" active="home">
    <div>
        <h1 class="text-xl font-semibold text-navy">Halo, {{ explode(' ', $tenant->name)[0] }}.</h1>
        <p class="text-sm text-slate-500">Ringkasan sewa dan pembayaran Anda.</p>
    </div>

    @if (! $rental)
        <x-owner.card><div class="p-6 text-center text-sm text-slate-500"><p class="font-medium text-navy">Belum ada sewa aktif.</p><p class="mt-1">Hubungi pengelola jika Anda merasa ini keliru.</p></div></x-owner.card>
    @else
        <div class="grid gap-4 md:grid-cols-2">
            <x-owner.card title="Kamar Anda">
                <div class="space-y-2 p-4 text-sm">
                    <div class="flex items-center justify-between"><span class="text-2xl font-semibold text-navy">{{ $rental->room->code }}</span><x-owner.badge :label="$rental->statusLabel()" /></div>
                    <div class="text-slate-500">{{ $rental->room->type->name }} · {{ $rental->room->floor->name }} · Sewa {{ $rental->mode === 'daily' ? 'Harian' : 'Bulanan' }}</div>
                    <div>{{ $rental->mode === 'daily' ? Fmt::date($rental->start_date) . ' – ' . Fmt::date($rental->end_date) : 'Mulai ' . Fmt::date($rental->start_date) }}</div>
                    <div class="font-medium">{{ Fmt::rp($rental->rate) }} / {{ $rental->mode === 'daily' ? 'malam' : 'bulan' }}</div>
                    <div class="text-xs text-slate-400">{{ $rental->occupants }} penghuni{{ $rental->co_occupant_name ? ' (bersama ' . $rental->co_occupant_name . ')' : '' }} · Deposit {{ Fmt::rp($rental->deposit_amount) }}@if ($rental->billing_anchor_day) · Jatuh tempo tiap tanggal {{ $rental->billing_anchor_day }}@endif</div>
                </div>
            </x-owner.card>

            <x-owner.card title="Pembayaran berikutnya">
                <div class="space-y-2 p-4 text-sm">
                    @if ($next)
                        <div class="text-2xl font-semibold text-navy">{{ Fmt::rp($next['i']->total_amount) }}</div>
                        <p class="text-slate-500">{{ $next['i']->periodLabel() }} · jatuh tempo {{ Fmt::date($next['i']->due_date) }}</p>
                        <x-owner.badge :label="$next['s']['label']" />
                    @elseif ($rental->status === 'reserved' && $first)
                        <div class="text-2xl font-semibold text-navy">{{ Fmt::rp($first['total']) }}</div>
                        <p class="text-slate-500">Pembayaran awal untuk memulai sewa.</p>
                    @else
                        <p class="text-slate-500">Tidak ada tagihan yang menunggu. Terima kasih sudah membayar tepat waktu.</p>
                    @endif
                    <div class="pt-1"><a href="{{ route('tenant.bills') }}" class="inline-block rounded-md bg-brand px-3 py-2 font-medium text-white hover:bg-brand-700">Lihat tagihan</a></div>
                </div>
            </x-owner.card>
        </div>
        @if ($invoices->isEmpty())@include('tenant._first-payment')@endif
    @endif
</x-tenant.layout>
