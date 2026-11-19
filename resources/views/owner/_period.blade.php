@php
$today = \Carbon\Carbon::today('Asia/Jakarta');
$ctl = 'rounded-md border border-slate-300 bg-white px-2.5 py-1.5 text-sm';
@endphp
<div class="flex flex-wrap items-center gap-2">
    <a href="{{ route($route, $p['prev']) }}" class="{{ $ctl }}" aria-label="Sebelumnya">‹</a>
    <span class="min-w-[8rem] text-center text-sm font-medium text-navy">{{ $p['label'] }}</span>
    <a href="{{ route($route, $p['next']) }}" class="{{ $ctl }}" aria-label="Berikutnya">›</a>
    <form method="GET" action="{{ route($route) }}" class="flex gap-1">
        @if ($p['mode'] === 'year')<input type="number" name="tahun" value="{{ $p['key'] }}" min="2000" max="2100" class="{{ $ctl }} w-24">
        @else<input type="month" name="bulan" value="{{ $p['key'] }}" class="{{ $ctl }}">@endif
        <button class="{{ $ctl }}">Lihat</button>
    </form>
    <a href="{{ route($route, $p['mode'] === 'year' ? ['bulan' => $today->format('Y-m')] : ['tahun' => substr($p['key'], 0, 4)]) }}" class="px-1 text-sm text-brand-700">{{ $p['mode'] === 'year' ? 'Per bulan' : 'Per tahun' }}</a>
</div>
