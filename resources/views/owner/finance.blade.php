@php
use App\Support\Owner\Fmt;
$t = $st['totals'];
$in = 'w-full rounded-md border border-slate-300 px-2.5 py-2 text-base sm:text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
$tabs = ['semua' => 'Semua', 'masuk' => 'Uang masuk', 'keluar' => 'Uang keluar'];
@endphp
<x-owner.layout title="Keuangan" active="finance">
    <x-owner.header title="Keuangan" subtitle="Uang masuk, uang keluar, dan saldo kas. Semua tercatat otomatis dari pembayaran dan pengeluaran.">
        <a href="{{ route('owner.reports', request()->only(['bulan', 'tahun'])) }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium">Laporan & Excel</a>
    </x-owner.header>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if (session('err'))<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('err') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    @include('owner._period', ['route' => 'owner.finance', 'p' => $p])

    <div class="grid grid-cols-2 gap-3 lg:grid-cols-3 xl:grid-cols-6">
        <x-owner.stat label="Saldo awal" :value="Fmt::rp($st['opening'])" />
        <x-owner.stat label="Pendapatan sewa" :value="Fmt::rp($t['rent'])" />
        <x-owner.stat label="Titipan deposit masuk" :value="Fmt::rp($t['deposit'])" hint="Bukan pendapatan" />
        <x-owner.stat label="Uang keluar" :value="Fmt::rp($t['expense'])" />
        <x-owner.stat label="Hasil operasional" :value="Fmt::rp($t['operating'])" hint="Sewa dikurangi pengeluaran" />
        <x-owner.stat label="Saldo akhir kas" :value="Fmt::rp($st['closing'])" :hint="'Tunai ' . Fmt::rp($t['cash']) . ' · Transfer ' . Fmt::rp($t['transfer'])" />
    </div>
    <div class="rounded-lg border border-brand/30 bg-brand-50 p-3 text-sm text-brand-700">
        Deposit adalah titipan penghuni, bukan pendapatan. Titipan deposit tercatat sampai akhir periode: <span class="font-semibold">{{ Fmt::rp($depositHeld) }}</span>.
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <details class="rounded-lg border border-slate-200 bg-white" @if (session('open_form') || $errors->any()) open @endif>
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-navy">+ Catat pengeluaran</summary>
            <form method="POST" action="{{ route('owner.expenses.store') }}" enctype="multipart/form-data" class="grid gap-3 border-t border-slate-100 p-4 text-sm sm:grid-cols-2">@csrf
                <input type="hidden" name="rid" value="{{ $rid }}">
                <label><span class="mb-1 block font-medium">Tanggal</span><input type="date" name="spent_on" value="{{ old('spent_on', now('Asia/Jakarta')->toDateString()) }}" required class="{{ $in }}"></label>
                <label><span class="mb-1 block font-medium">Kategori</span><select name="category" class="{{ $in }}">@foreach ($categories as $c)<option @selected(old('category') === $c)>{{ $c }}</option>@endforeach</select></label>
                <label><span class="mb-1 block font-medium">Jumlah (Rp)</span><input type="number" name="amount" min="1" inputmode="numeric" value="{{ old('amount') }}" required class="{{ $in }}"></label>
                <label><span class="mb-1 block font-medium">Dibayar dengan</span><select name="method" class="{{ $in }}"><option value="cash">Tunai</option><option value="transfer">Transfer</option></select></label>
                <label class="sm:col-span-2"><span class="mb-1 block font-medium">Keterangan</span><input name="description" maxlength="200" value="{{ old('description') }}" placeholder="mis. Ganti keran kamar mandi" class="{{ $in }}"></label>
                <label><span class="mb-1 block font-medium">Kamar (opsional)</span><select name="room_id" class="{{ $in }}"><option value="">—</option>@foreach ($rooms as $rm)<option value="{{ $rm->id }}">{{ $rm->code }}</option>@endforeach</select></label>
                <label><span class="mb-1 block font-medium">Foto struk (opsional)</span><input type="file" name="photo" accept="image/jpeg,image/png,application/pdf" class="block w-full text-xs file:mr-2 file:rounded-md file:border-0 file:bg-brand-50 file:px-2 file:py-1.5 file:text-brand-700"></label>
                <div class="flex flex-wrap gap-2 sm:col-span-2">
                    <button class="rounded-md bg-brand px-4 py-2.5 font-medium text-white hover:bg-brand-700">Simpan</button>
                    <button name="again" value="1" class="rounded-md border border-slate-300 px-4 py-2.5 font-medium">Simpan & tambah lagi</button>
                </div>
            </form>
        </details>

        <details class="rounded-lg border border-slate-200 bg-white">
            <summary class="cursor-pointer px-4 py-3 text-sm font-semibold text-navy">Saldo awal kas @if ($opening['date'])<span class="font-normal text-slate-500">· {{ Fmt::rp($opening['balance']) }} per {{ Fmt::date(\Carbon\Carbon::parse($opening['date'])) }}</span>@endif</summary>
            <form method="POST" action="{{ route('owner.finance.opening') }}" class="grid gap-3 border-t border-slate-100 p-4 text-sm sm:grid-cols-2" onsubmit="return confirm('Simpan saldo awal? Transaksi sebelum tanggal ini tidak ikut dihitung.')">@csrf @method('PUT')
                <label><span class="mb-1 block font-medium">Saldo kas (Rp)</span><input type="number" name="opening_balance" min="0" value="{{ $opening['balance'] }}" required class="{{ $in }}"></label>
                <label><span class="mb-1 block font-medium">Pada tanggal</span><input type="date" name="opening_date" value="{{ $opening['date'] }}" required class="{{ $in }}"></label>
                <p class="text-xs text-slate-400 sm:col-span-2">Isi sekali saat mulai memakai aplikasi, sesuai uang kas yang ada. Transaksi sebelum tanggal ini dianggap sudah termasuk di saldo awal.</p>
                <div class="sm:col-span-2"><button class="rounded-md border border-slate-300 px-4 py-2 font-medium">Simpan saldo awal</button></div>
            </form>
        </details>
    </div>

    <form method="GET" action="{{ route('owner.finance') }}" class="flex flex-wrap gap-2">
        @foreach (request()->only(['bulan', 'tahun']) as $k => $v)<input type="hidden" name="{{ $k }}" value="{{ $v }}">@endforeach
        <input type="hidden" name="tab" value="{{ $tab }}">
        <input type="search" name="q" value="{{ $q }}" placeholder="Cari keterangan, kategori, atau nomor" class="w-full rounded-md border border-slate-300 bg-white px-3 py-2.5 text-base sm:w-80 sm:text-sm">
        <button class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium">Cari</button>
    </form>
    <div class="flex gap-2 text-sm">
        @foreach ($tabs as $k => $l)<a href="{{ route('owner.finance', array_merge(request()->only(['bulan', 'tahun']), ['tab' => $k, 'q' => $q ?: null])) }}" class="rounded-md px-3 py-1.5 {{ $tab === $k ? 'bg-navy text-white' : 'border border-slate-300 bg-white' }}">{{ $l }}</a>@endforeach
    </div>

    <x-owner.card title="Mutasi kas">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Tanggal', 'Keterangan', 'Referensi', 'Metode', 'Masuk', 'Keluar', 'Saldo', ''] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
                <tr class="bg-slate-50/60"><td class="px-4 py-2 text-slate-500">{{ Fmt::date($p['from']) }}</td><td class="px-4 py-2 font-medium" colspan="5">Saldo awal periode</td><td class="px-4 py-2 font-medium">{{ Fmt::rp($st['opening']) }}</td><td></td></tr>
                @forelse ($rows as $e)
                    <tr class="align-top {{ $e['void'] ? 'text-slate-400' : '' }}">
                        <td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ Fmt::date($e['date'], 'j M Y') }}</td>
                        <td class="px-4 py-2"><span class="{{ $e['void'] ? 'line-through' : '' }}">{{ $e['desc'] }}</span><div class="text-xs {{ $e['void'] ? 'text-red-500' : 'text-slate-400' }}">{{ $e['void'] ? 'Dibatalkan: ' . $e['void_reason'] : $e['category'] }}</div></td>
                        <td class="px-4 py-2 text-xs">@if ($e['url'])<a href="{{ $e['url'] }}" target="_blank" class="font-medium text-brand-700">{{ $e['ref'] }}</a>@else{{ $e['ref'] }}@endif</td>
                        <td class="px-4 py-2 text-slate-500">{{ $e['method'] === 'cash' ? 'Tunai' : 'Transfer' }}</td>
                        <td class="px-4 py-2 font-medium {{ $e['void'] ? '' : 'text-emerald-600' }}">{{ $e['in'] ? Fmt::rp($e['in']) : '' }}</td>
                        <td class="px-4 py-2 font-medium {{ $e['void'] ? '' : 'text-red-600' }}">{{ $e['out'] ? Fmt::rp($e['out']) : '' }}</td>
                        <td class="px-4 py-2">{{ $e['void'] ? '' : Fmt::rp($e['balance']) }}</td>
                        <td class="px-4 py-2">
                            @if (! $e['void'] && $e['payment_id'])
                                <details class="w-56"><summary class="cursor-pointer text-[11px] text-slate-400">Batalkan pembayaran</summary>
                                    <form method="POST" action="{{ route('owner.payments.void', $e['payment_id']) }}" onsubmit="return confirm('Batalkan pembayaran ini? Tagihan kembali belum lunas.')" class="mt-1 space-y-1">@csrf
                                        <input name="reason" required maxlength="200" placeholder="Alasan" class="{{ $in }}"><button class="rounded-md border border-red-200 px-2 py-1 text-xs text-red-700">Batalkan</button></form></details>
                            @elseif (! $e['void'] && $e['expense_id'])
                                @if ($e['has_photo'] ?? false)<a href="{{ route('owner.expenses.file', $e['expense_id']) }}" target="_blank" class="block text-[11px] font-medium text-brand-700">Foto struk</a>@endif
                                <details class="w-56"><summary class="cursor-pointer text-[11px] text-slate-400">Batalkan</summary>
                                    <form method="POST" action="{{ route('owner.expenses.void', $e['expense_id']) }}" onsubmit="return confirm('Batalkan pengeluaran ini?')" class="mt-1 space-y-1">@csrf
                                        <input name="reason" required maxlength="200" placeholder="Alasan" class="{{ $in }}"><button class="rounded-md border border-red-200 px-2 py-1 text-xs text-red-700">Batalkan</button></form></details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-8 text-center text-slate-500">Belum ada transaksi pada periode ini.</td></tr>
                @endforelse
                <tr class="bg-slate-50 font-semibold"><td class="px-4 py-2" colspan="4">Total periode</td><td class="px-4 py-2 text-emerald-600">{{ Fmt::rp($t['in']) }}</td><td class="px-4 py-2 text-red-600">{{ Fmt::rp($t['out']) }}</td><td class="px-4 py-2 text-navy">{{ Fmt::rp($st['closing']) }}</td><td></td></tr>
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
