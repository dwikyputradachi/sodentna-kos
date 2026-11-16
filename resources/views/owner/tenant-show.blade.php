@php
use App\Support\Owner\Fmt;
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
$rows = [
    ['WhatsApp', $t->phone], ['Tempat, tanggal lahir', $t->birth_place ? $t->birth_place . ', ' . Fmt::date($t->birth_date) : '—'], ['Nomor identitas', $t->maskedId()],
    ['Pekerjaan', $t->job ?: '—'], ['Instansi', $t->institution ?: '—'], ['Alamat asal', $t->address ?: '—'],
    ['Kontak darurat', $t->emergency_name ? $t->emergency_name . ' (' . $t->emergency_relation . ') · ' . $t->emergency_phone : '—'],
];
$account = $t->user ? ($t->user->password ? 'sudah aktif' : 'menunggu aktivasi') : 'belum dibuat';
@endphp
<x-owner.layout :title="$t->name" active="tenants">
    <a href="{{ route('owner.tenants') }}" class="text-sm text-brand-700">Kembali ke Penghuni</a>
    <x-owner.header :title="$t->name" :subtitle="$current ? 'Kamar ' . $current->room->code . ' · ' . $current->room->floor->name : 'Alumni'">
        <x-owner.badge :label="$current ? $current->statusLabel() : 'Alumni'" />
        <a href="https://wa.me/{{ \App\Support\Owner\Billing::phone($t->phone) }}" target="_blank" rel="noopener" class="rounded-md border border-slate-300 bg-white px-3 py-1.5 text-xs font-medium">Chat WhatsApp</a>
    </x-owner.header>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @if ($activation)
        <div class="space-y-2 rounded-lg border border-brand/30 bg-brand-50 p-4 text-sm">
            <div class="font-semibold text-brand-700">{{ $activation['existing'] ? 'Pemberitahuan akun penghuni' : 'Tautan aktivasi akun penghuni' }}</div>
            <p class="text-slate-600">{{ $activation['existing'] ? 'Akun sudah aktif, jadi pesan berisi alamat login.' : 'Tautan sekali pakai dan hanya tampil sekali.' }} Kirim lewat WhatsApp (Anda menekan Kirim sendiri).</p>
            <div class="flex gap-2"><input readonly value="{{ $activation['url'] }}" onclick="this.select()" class="{{ $in }}"><x-copy :text="$activation['url']" /></div>
            <a href="{{ $activation['wa'] }}" target="_blank" rel="noopener" class="inline-block rounded-md bg-brand px-3 py-2 font-medium text-white">Kirim via WhatsApp</a>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-owner.card title="Data penghuni">
            <dl class="grid grid-cols-3 gap-2 p-4 text-sm">
                @foreach ($rows as [$l, $v])<dt class="text-slate-500">{{ $l }}</dt><dd class="col-span-2">{{ $v }}</dd>@endforeach
                @if ($t->id_doc_path)<dt class="text-slate-500">Foto identitas</dt><dd class="col-span-2"><a href="{{ route('owner.tenants.id', $t) }}" target="_blank" class="font-medium text-brand-700">Buka berkas</a></dd>@endif
            </dl>
        </x-owner.card>

        <x-owner.card title="Akun portal">
            <div class="space-y-3 p-4 text-sm">
                <p>Status akun: <span class="font-medium {{ $account === 'sudah aktif' ? 'text-emerald-700' : 'text-amber-700' }}">{{ $account }}</span></p>
                <form method="POST" action="{{ route('owner.tenants.invitation', $t) }}">@csrf
                    <button class="rounded-md border border-slate-300 px-4 py-2 font-medium">{{ $account === 'sudah aktif' ? 'Kirim pemberitahuan via WhatsApp' : ($account === 'belum dibuat' ? 'Buat akun & tautan aktivasi' : 'Buat tautan aktivasi baru') }}</button>
                </form>
            </div>
        </x-owner.card>
    </div>

    <x-owner.card title="Riwayat sewa" note="Setiap kamar yang pernah ditempati, termasuk yang sudah berakhir.">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Kamar', 'Periode', 'Penghuni', 'Tarif', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @foreach ($t->rentals as $rt)
                <tr>
                    <td class="px-4 py-2"><a href="{{ route('owner.rooms.show', $rt->room_id) }}" class="font-medium text-brand-700">{{ $rt->room->code }}</a></td>
                    <td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ Fmt::date($rt->start_date) }} – {{ $rt->end_date ? Fmt::date($rt->end_date) : 'sekarang' }}</td>
                    <td class="px-4 py-2">{{ $rt->occupants }} orang{{ $rt->co_occupant_name ? ' (& ' . $rt->co_occupant_name . ')' : '' }}</td>
                    <td class="px-4 py-2">{{ Fmt::rp($rt->rate) }}</td>
                    <td class="px-4 py-2"><x-owner.badge :label="$rt->statusLabel()" /></td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </x-owner.card>

    <x-owner.card title="Tagihan & riwayat pembayaran" note="Semua tagihan penghuni ini, termasuk yang sudah lunas.">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Tagihan', 'Jatuh tempo', 'Nominal', 'Status', 'Pembayaran'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($invoices as $iv)
                @php $st = \App\Support\InvoiceState::of($iv); @endphp
                <tr>
                    <td class="px-4 py-2"><div class="font-medium">{{ $iv->number }}</div><div class="text-xs text-slate-400">{{ $iv->periodLabel() }}</div></td>
                    <td class="px-4 py-2 text-slate-500">{{ Fmt::date($iv->due_date) }}</td>
                    <td class="px-4 py-2">{{ Fmt::rp($iv->total_amount) }}</td>
                    <td class="px-4 py-2"><x-owner.badge :label="$st['label']" /></td>
                    <td class="px-4 py-2 text-xs text-slate-500">@if ($iv->payment){{ Fmt::date($iv->payment->paid_on) }} · {{ $iv->payment->methodLabel() }}@if ($iv->payment->receipt) · <a href="{{ $iv->payment->receipt->url() }}" target="_blank" class="font-medium text-brand-700">{{ $iv->payment->receipt->number }}</a>@endif @else — @endif</td>
                </tr>
            @empty
                <tr><td colspan="5" class="px-4 py-6 text-center text-slate-500">Belum ada tagihan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>

    @if ($current)
        <x-owner.card title="Akhiri sewa" note="Penghuni pindah ke tab Alumni; semua riwayat tetap tersimpan.">
            <form method="POST" action="{{ route('owner.tenants.end', $t) }}" onsubmit="return confirm('Akhiri sewa {{ $t->name }}? Tindakan ini memindahkannya ke Alumni.')" class="grid gap-3 p-4 text-sm sm:grid-cols-3">@csrf
                <label><span class="mb-1 block font-medium">Tanggal keluar</span><input type="date" name="end_date" value="{{ old('end_date', now('Asia/Jakarta')->toDateString()) }}" required class="{{ $in }}"></label>
                <label><span class="mb-1 block font-medium">Status kamar setelahnya</span>
                    <select name="room_status" class="{{ $in }}"><option value="available">Tersedia</option><option value="maintenance">Dalam Perbaikan</option></select></label>
                <label><span class="mb-1 block font-medium">Catatan (opsional)</span><input name="note" maxlength="200" class="{{ $in }}"></label>
                <div class="sm:col-span-3"><button class="rounded-md border border-red-200 px-4 py-2 font-medium text-red-700">Akhiri sewa</button>
                    <span class="ml-2 text-xs text-slate-400">Penyelesaian deposit dicatat terpisah pada fitur deposit.</span></div>
            </form>
        </x-owner.card>
    @endif
</x-owner.layout>
