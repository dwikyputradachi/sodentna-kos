@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Dashboard" active="dashboard">
    <x-owner.header title="Dashboard" subtitle="Yang perlu ditangani hari ini." />

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6">
        @foreach ($queue as [$label, $n, $tone, $route])
            <a href="{{ route($route) }}" class="rounded-lg border bg-white p-4 hover:border-brand {{ $n > 0 && $tone === 'red' ? 'border-red-200' : ($n > 0 && $tone === 'yellow' ? 'border-amber-200' : 'border-slate-200') }}">
                <div class="text-2xl font-semibold {{ $n > 0 && $tone === 'red' ? 'text-red-600' : ($n > 0 && $tone === 'yellow' ? 'text-amber-600' : 'text-navy') }}">{{ $n }}</div>
                <div class="mt-1 text-xs text-slate-500">{{ $label }}</div>
            </a>
        @endforeach
    </div>

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-5">
        <x-owner.stat label="Kas masuk" :value="Fmt::rp($fin['in'])" />
        <x-owner.stat label="Kas keluar" :value="Fmt::rp($fin['out'])" />
        <x-owner.stat label="Pendapatan sewa" :value="Fmt::rp($fin['Sewa'])" />
        <x-owner.stat label="Deposit ditahan" :value="Fmt::rp($deposit_held)" hint="Bukan pendapatan" />
        <x-owner.stat label="Saldo kas" :value="Fmt::rp($fin['opening'] + $fin['net'])" />
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <x-owner.card title="Okupansi" class="lg:col-span-1">
            <div class="space-y-3 p-4 text-sm">
                @foreach ($rooms as $st => $n)
                    <div class="flex items-center justify-between"><x-owner.badge :label="$st" /><span class="font-medium">{{ $n }} kamar</span></div>
                @endforeach
                <div class="h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full bg-brand" style="width: {{ round($rooms['Terisi'] / max(array_sum($rooms), 1) * 100) }}%"></div>
                </div>
                <a href="{{ route('owner.rooms') }}" class="text-xs font-medium text-brand-700">Lihat denah kamar</a>
            </div>
        </x-owner.card>

        <x-owner.card title="Transaksi terbaru" class="lg:col-span-2">
            <table class="w-full text-sm">
                <tbody class="divide-y divide-slate-100">
                @foreach ($ledger as $l)
                    <tr><td class="px-4 py-2 text-slate-500">{{ Fmt::date($l['date'], 'j M') }}</td><td class="px-4 py-2">{{ $l['desc'] }}</td>
                        <td class="px-4 py-2 text-right font-medium {{ $l['amt'] < 0 ? 'text-red-600' : 'text-emerald-600' }}">{{ Fmt::rp($l['amt']) }}</td></tr>
                @endforeach
                </tbody>
            </table>
        </x-owner.card>
    </div>

    <x-owner.card title="Pemeliharaan terbaru">
        <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
        @foreach ($tickets as $t)
            <tr><td class="px-4 py-2 font-medium">{{ $t['no'] }}</td><td class="px-4 py-2">Kamar {{ $t['room'] }} · {{ $t['issue'] }}</td><td class="px-4 py-2"><x-owner.badge :label="$t['st']" /></td></tr>
        @endforeach
        </tbody></table>
    </x-owner.card>
</x-owner.layout>
