@php use App\Support\Owner\Fmt; $t = $st['totals']; @endphp
<x-owner.layout title="Dashboard" active="dashboard">
    <x-owner.header title="Dashboard" subtitle="Yang perlu ditangani hari ini." />

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach ($queue as [$label, $n, $tone, $url])
            <a href="{{ $url }}" class="rounded-lg border bg-white p-4 hover:border-brand {{ $n > 0 && $tone === 'red' ? 'border-red-200' : ($n > 0 && $tone === 'yellow' ? 'border-amber-200' : 'border-slate-200') }}">
                <div class="text-2xl font-semibold {{ $n > 0 && $tone === 'red' ? 'text-red-600' : ($n > 0 && $tone === 'yellow' ? 'text-amber-600' : 'text-navy') }}">{{ $n }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ $label }}</div>
            </a>
        @endforeach
    </div>

    @if ($attention->isNotEmpty())
        <x-owner.card title="Perlu ditagih" note="Buka Tagihan untuk mengirim pengingat atau mencatat pembayaran.">
            <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
                @foreach ($attention as $x)
                    <tr><td class="px-4 py-2 font-medium">{{ $x['i']->rental->tenant->name }}</td><td class="px-4 py-2 text-slate-500">Kamar {{ $x['i']->rental->room->code }}</td><td class="px-4 py-2">{{ Fmt::rp($x['i']->total_amount) }}</td>
                        <td class="px-4 py-2"><x-owner.badge :label="$x['s']['label']" /></td><td class="px-4 py-2 text-right"><a href="{{ route('owner.billing') }}" class="text-xs font-medium text-brand-700">Buka</a></td></tr>
                @endforeach
            </tbody></table>
        </x-owner.card>
    @endif

    <div class="text-xs font-medium uppercase tracking-wide text-slate-400">Keuangan {{ $monthLabel }}</div>
    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <x-owner.stat label="Kas masuk" :value="Fmt::rp($t['in'])" />
        <x-owner.stat label="Kas keluar" :value="Fmt::rp($t['out'])" />
        <x-owner.stat label="Pendapatan sewa" :value="Fmt::rp($t['rent'])" />
        <x-owner.stat label="Titipan deposit" :value="Fmt::rp($depositHeld)" hint="Bukan pendapatan" />
        <x-owner.stat label="Saldo kas" :value="Fmt::rp($st['closing'])" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-owner.card title="Okupansi" class="lg:col-span-1">
            <div class="space-y-3 p-4 text-sm">
                @foreach (['available' => 'Tersedia', 'reserved' => 'Dipesan', 'occupied' => 'Terisi', 'maintenance' => 'Dalam Perbaikan'] as $k => $label)
                    <div class="flex items-center justify-between"><x-owner.badge :label="$label" /><span class="font-medium">{{ $rooms[$k] ?? 0 }} kamar</span></div>
                @endforeach
                <div class="h-2 overflow-hidden rounded-full bg-slate-100"><div class="h-full bg-brand" style="width: {{ round(($rooms['occupied'] ?? 0) / max($rooms->sum(), 1) * 100) }}%"></div></div>
                <a href="{{ route('owner.rooms') }}" class="text-xs font-medium text-brand-700">Lihat denah kamar</a>
            </div>
        </x-owner.card>
        <x-owner.card title="Transaksi terbaru" class="lg:col-span-2">
            <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
                @forelse ($recent as $e)
                    <tr><td class="px-4 py-2 text-slate-500">{{ Fmt::date($e['date'], 'j M') }}</td><td class="px-4 py-2">{{ $e['desc'] }}</td>
                        <td class="px-4 py-2 text-right font-medium {{ $e['out'] ? 'text-red-600' : 'text-emerald-600' }}">{{ $e['out'] ? '-' . Fmt::rp($e['out']) : Fmt::rp($e['in']) }}</td></tr>
                @empty
                    <tr><td class="px-4 py-6 text-center text-slate-500">Belum ada transaksi bulan ini.</td></tr>
                @endforelse
            </tbody></table>
        </x-owner.card>
    </div>
</x-owner.layout>
