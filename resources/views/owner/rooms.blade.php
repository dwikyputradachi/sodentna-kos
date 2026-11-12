@php
use App\Models\Room;
use App\Support\Owner\Fmt;
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
@endphp
<x-owner.layout title="Kamar & Lantai" active="rooms">
    <x-owner.header title="Kamar & Lantai" subtitle="Atur lantai, tipe kamar, tarif, dan status kamar." />

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <div class="flex flex-wrap gap-3">
        @foreach (Room::STATUSES as $k => $label)
            <span class="flex items-center gap-1.5"><x-owner.badge :label="$label" /><span class="text-xs text-slate-500">{{ $counts[$k] ?? 0 }}</span></span>
        @endforeach
    </div>

    <div x-data="{ f: null }" class="space-y-4">
        <div class="flex flex-wrap gap-2 text-sm">
            @foreach (['floor' => 'Tambah lantai', 'room' => 'Tambah kamar', 'type' => 'Tambah tipe kamar'] as $k => $l)
                <button @click="f = (f === '{{ $k }}' ? null : '{{ $k }}')" :class="f === '{{ $k }}' ? 'bg-navy text-white' : 'border border-slate-300 bg-white'" class="rounded-md px-3 py-2 font-medium">{{ $l }}</button>
            @endforeach
        </div>

        <x-owner.card x-show="f === 'floor'" x-cloak title="Tambah lantai">
            <form method="POST" action="{{ route('owner.floors.store') }}" class="flex gap-2 p-4">@csrf
                <input name="name" placeholder="Lantai 1" required class="{{ $in }} sm:w-64"><button class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white">Simpan</button></form>
        </x-owner.card>

        <x-owner.card x-show="f === 'room'" x-cloak title="Tambah kamar">
            <form method="POST" action="{{ route('owner.rooms.store') }}" class="grid gap-3 p-4 sm:grid-cols-4">@csrf
                <label class="text-sm"><span class="mb-1 block font-medium">Nomor kamar</span><input name="code" placeholder="101" required class="{{ $in }}"></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Lantai</span><select name="floor_id" required class="{{ $in }}">@foreach ($floors as $fl)<option value="{{ $fl->id }}">{{ $fl->name }}</option>@endforeach</select></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Tipe</span><select name="room_type_id" required class="{{ $in }}">@foreach ($types as $ty)<option value="{{ $ty->id }}">{{ $ty->name }}</option>@endforeach</select></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Catatan</span><input name="notes" class="{{ $in }}"></label>
                <div class="sm:col-span-4"><button class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white">Simpan kamar</button>
                    @if ($floors->isEmpty() || $types->isEmpty())<span class="ml-2 text-xs text-amber-700">Buat lantai dan tipe kamar terlebih dahulu.</span>@endif</div>
            </form>
        </x-owner.card>

        <x-owner.card x-show="f === 'type'" x-cloak title="Tambah tipe kamar" note="Tarif bulanan mengikuti jumlah penghuni.">
            @include('owner._type-form', ['action' => route('owner.types.store')])
        </x-owner.card>
    </div>

    @forelse ($floors as $fl)
        <x-owner.card :title="$fl->name">
            @if ($fl->rooms->isEmpty())<p class="p-4 text-sm text-slate-500">Belum ada kamar di lantai ini.</p>@endif
            <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-3 xl:grid-cols-6">
                @foreach ($fl->rooms as $r)
                    <a href="{{ route('owner.rooms.show', $r) }}" class="rounded-lg border p-3 hover:border-brand {{ match($r->status) { 'available' => 'border-emerald-200 bg-emerald-50/40', 'reserved' => 'border-amber-200 bg-amber-50/40', 'maintenance' => 'border-red-200 bg-red-50/40', default => 'border-slate-200 bg-white' } }}">
                        <div class="text-lg font-semibold text-navy">{{ $r->code }}</div>
                        <div class="text-xs text-slate-500">{{ $r->type->name }}</div>
                        <div class="mt-2"><x-owner.badge :label="$r->statusLabel()" /></div>
                    </a>
                @endforeach
            </div>
        </x-owner.card>
    @empty
        <x-owner.card><p class="p-8 text-center text-sm text-slate-500">Belum ada lantai. Mulai dengan "Tambah lantai".</p></x-owner.card>
    @endforelse

    <x-owner.card title="Tipe kamar & tarif">
        <div class="divide-y divide-slate-100">
            @forelse ($types as $ty)
                <details class="p-4 text-sm">
                    <summary class="flex cursor-pointer flex-wrap items-center justify-between gap-2">
                        <span class="font-medium">{{ $ty->name }} <span class="font-normal text-slate-500">· {{ $ty->priceLabel() }} · deposit {{ Fmt::rp($ty->deposit_amount) }}</span></span>
                        <span class="text-brand-700">Ubah</span>
                    </summary>
                    <div class="mt-3 rounded-md border border-slate-200">@include('owner._type-form', ['action' => route('owner.types.update', $ty), 't' => $ty])</div>
                </details>
            @empty
                <p class="p-4 text-sm text-slate-500">Belum ada tipe kamar.</p>
            @endforelse
        </div>
    </x-owner.card>
</x-owner.layout>
