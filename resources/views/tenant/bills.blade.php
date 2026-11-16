@php
use App\Support\Owner\Fmt;
$wa = 'https://wa.me/' . \App\Support\Owner\Billing::phone(\App\Support\Tenant\DemoTenant::OWNER_WA);
@endphp
<x-tenant.layout title="Tagihan" active="bills">
    <div><h1 class="text-xl font-semibold text-navy">Tagihan</h1><p class="text-sm text-slate-500">Tagihan dan status pembayaran Anda.</p></div>

    @if ($invoices->isEmpty())
        @include('tenant._first-payment')
        <x-owner.card><p class="p-6 text-center text-sm text-slate-500">Belum ada tagihan. Tagihan sewa dibuat otomatis dan muncul di sini.</p></x-owner.card>
    @else
        <div class="space-y-3">
            @foreach ($invoices as $row)
                @php $i = $row['i']; $s = $row['s']; @endphp
                <div class="space-y-2 rounded-lg border border-slate-200 bg-white p-4 text-sm">
                    <div class="flex flex-wrap items-start justify-between gap-2">
                        <div><div class="text-xs text-slate-400">{{ $i->number }}</div><div class="text-xl font-semibold text-navy">{{ Fmt::rp($i->total_amount) }}</div>
                            <div class="text-slate-500">{{ $i->periodLabel() }} · jatuh tempo {{ Fmt::date($i->due_date) }}</div>
                            @if ($i->deposit_amount > 0)<div class="text-xs text-slate-400">Sewa {{ Fmt::rp($i->rent_amount) }} + deposit {{ Fmt::rp($i->deposit_amount) }}</div>@endif</div>
                        <x-owner.badge :label="$s['label']" />
                    </div>
                    @if ($i->status === 'unpaid')
                        <a href="{{ $wa . '?text=' . rawurlencode('Halo, saya sudah membayar tagihan ' . $i->number . ' sebesar ' . Fmt::rp($i->total_amount) . '.') }}" target="_blank" rel="noopener" class="inline-block rounded-md border border-slate-300 px-3 py-1.5 text-xs font-medium">Konfirmasi pembayaran via WhatsApp</a>
                    @elseif ($i->payment?->receipt)
                        <a href="{{ $i->payment->receipt->url() }}" target="_blank" class="text-xs font-medium text-brand-700">Lihat kuitansi {{ $i->payment->receipt->number }}</a>
                    @endif
                </div>
            @endforeach
        </div>
        <p class="text-xs text-slate-400">Transfer ke rekening yang diberikan pengelola, lalu konfirmasi lewat WhatsApp. Pengelola akan mencatat pembayaran Anda dan mengirim kuitansi.</p>
    @endif
</x-tenant.layout>
