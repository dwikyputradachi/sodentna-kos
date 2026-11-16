@php use App\Support\Owner\Fmt; @endphp
@if ($rental && $first && $rental->status === 'reserved')
    <x-owner.card title="Pembayaran awal" note="Kamar Anda sudah dipesan. Selesaikan pembayaran awal ke pengelola untuk memulai sewa.">
        <dl class="space-y-2 p-4 text-sm">
            <div class="flex justify-between"><dt class="text-slate-500">{{ $first['label'] }}</dt><dd>{{ Fmt::rp($first['rent']) }}</dd></div>
            @if ($first['deposit'] !== null)<div class="flex justify-between"><dt class="text-slate-500">Deposit dana titipan</dt><dd>{{ Fmt::rp($first['deposit']) }}</dd></div>@endif
            <div class="flex justify-between border-t border-slate-100 pt-2 font-semibold text-navy"><dt>Total awal</dt><dd>{{ Fmt::rp($first['total']) }}</dd></div>
            @if ($rental->mode === 'daily')<p class="text-xs text-slate-400">Ketentuan deposit sewa harian dikonfirmasi oleh pengelola.</p>@endif
            <p class="text-xs text-slate-400">Nomor rekening dan petunjuk transfer diberikan pengelola lewat WhatsApp. Unggah bukti pembayaran akan tersedia di halaman Tagihan.</p>
        </dl>
    </x-owner.card>
@endif
