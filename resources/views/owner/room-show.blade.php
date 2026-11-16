@php
use App\Models\Room;
use App\Support\Owner\Fmt;
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
@endphp
<x-owner.layout :title="'Kamar ' . $room->code" active="rooms">
    <a href="{{ route('owner.rooms') }}" class="text-sm text-brand-700">Kembali ke Kamar & Lantai</a>
    <x-owner.header :title="'Kamar ' . $room->code" :subtitle="$room->floor->name . ' · ' . $room->type->name" />

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-owner.card title="Status">
            <form method="POST" action="{{ route('owner.rooms.status', $room) }}" onsubmit="return confirm('Ubah status kamar ini?')" class="space-y-3 p-4 text-sm">@csrf
                <div>Saat ini: <x-owner.badge :label="$room->statusLabel()" /></div>
                <select name="status" class="{{ $in }}">@foreach (Room::STATUSES as $k => $l)<option value="{{ $k }}" @selected($room->status === $k)>{{ $l }}</option>@endforeach</select>
                <input name="note" placeholder="Keterangan (opsional)" class="{{ $in }}">
                <button class="rounded-md bg-navy px-4 py-2 font-medium text-white">Ubah status</button>
                <p class="text-xs text-slate-400">Status Terisi/Dipesan nantinya diatur otomatis oleh data sewa.</p>
            </form>
        </x-owner.card>

        <x-owner.card title="Data kamar">
            <form method="POST" action="{{ route('owner.rooms.update', $room) }}" class="space-y-3 p-4 text-sm">@csrf @method('PUT')
                <label class="block"><span class="mb-1 block font-medium">Lantai</span><select name="floor_id" class="{{ $in }}">@foreach ($floors as $f)<option value="{{ $f->id }}" @selected($room->floor_id === $f->id)>{{ $f->name }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1 block font-medium">Tipe</span><select name="room_type_id" class="{{ $in }}">@foreach ($types as $t)<option value="{{ $t->id }}" @selected($room->room_type_id === $t->id)>{{ $t->name }}</option>@endforeach</select></label>
                <label class="block"><span class="mb-1 block font-medium">Catatan</span><input name="notes" value="{{ old('notes', $room->notes) }}" class="{{ $in }}"></label>
                <div class="text-xs text-slate-500">Tarif: {{ $room->type->priceLabel() }} · Deposit {{ Fmt::rp($room->type->deposit_amount) }}</div>
                <button class="rounded-md bg-brand px-4 py-2 font-medium text-white">Simpan</button>
            </form>
        </x-owner.card>
    </div>

    <x-owner.card title="Riwayat status">
        <table class="w-full text-sm"><tbody class="divide-y divide-slate-100">
            @forelse ($room->logs as $l)
                <tr><td class="px-4 py-2 text-slate-500">{{ Fmt::date($l->created_at->timezone('Asia/Jakarta'), 'j M Y H:i') }}</td>
                    <td class="px-4 py-2">{{ Room::STATUSES[$l->from_status] ?? '—' }} → {{ Room::STATUSES[$l->to_status] ?? $l->to_status }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $l->note }}</td><td class="px-4 py-2 text-slate-500">{{ $l->user->name ?? '—' }}</td></tr>
            @empty
                <tr><td class="px-4 py-6 text-center text-slate-500">Belum ada perubahan status.</td></tr>
            @endforelse
        </tbody></table>
    </x-owner.card>
    <x-owner.card title="Riwayat penghuni kamar" note="Siapa saja yang pernah dan sedang menempati kamar ini.">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Penghuni', 'Periode', 'Tarif', 'Status'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($rentals as $rt)
                <tr>
                    <td class="px-4 py-2"><a href="{{ route('owner.tenants.show', $rt->tenant_id) }}" class="font-medium text-brand-700">{{ $rt->tenant->name }}</a>@if ($rt->co_occupant_name)<span class="text-slate-500"> & {{ $rt->co_occupant_name }}</span>@endif</td>
                    <td class="px-4 py-2 text-slate-500">{{ Fmt::date($rt->start_date) }} – {{ $rt->end_date ? Fmt::date($rt->end_date) : 'sekarang' }}</td>
                    <td class="px-4 py-2">{{ Fmt::rp($rt->rate) }}</td>
                    <td class="px-4 py-2"><x-owner.badge :label="$rt->statusLabel()" /></td>
                </tr>
            @empty
                <tr><td colspan="4" class="px-4 py-6 text-center text-slate-500">Belum pernah ditempati.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
    <p class="text-xs text-slate-400">Catatan masalah dan penggantian barang tampil di sini setelah fitur pemeliharaan dibuat.</p>
</x-owner.layout>
