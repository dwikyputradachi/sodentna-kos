@php
$t = $t ?? null;
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
@endphp
<form method="POST" action="{{ $action }}" class="grid gap-3 p-4 sm:grid-cols-3"
      x-data="{ cap: {{ (int) old('capacity', $t->capacity ?? 1) }}, daily: {{ old('allows_daily', $t->allows_daily ?? false) ? 'true' : 'false' }} }">
    @csrf
    @if ($t) @method('PUT') @endif
    <label class="text-sm"><span class="mb-1 block font-medium">Nama tipe</span><input name="name" value="{{ old('name', $t->name ?? '') }}" required class="{{ $in }}"></label>
    <label class="text-sm"><span class="mb-1 block font-medium">Ukuran</span><input name="size" value="{{ old('size', $t->size ?? '') }}" placeholder="3 × 4 m" class="{{ $in }}"></label>
    <label class="text-sm"><span class="mb-1 block font-medium">Kapasitas</span>
        <select name="capacity" x-model.number="cap" class="{{ $in }}"><option value="1">1 orang</option><option value="2">2 orang</option></select></label>
    <label class="text-sm"><span class="mb-1 block font-medium">Tarif 1 orang / bulan (Rp)</span><input type="number" name="rate_one" min="0" value="{{ old('rate_one', $t->rate_one ?? '') }}" required class="{{ $in }}"></label>
    <label class="text-sm" x-show="cap === 2"><span class="mb-1 block font-medium">Tarif 2 orang / bulan (Rp)</span><input type="number" name="rate_two" min="0" value="{{ old('rate_two', $t->rate_two ?? '') }}" :required="cap === 2" class="{{ $in }}"></label>
    <label class="text-sm"><span class="mb-1 block font-medium">Deposit (Rp)</span><input type="number" name="deposit_amount" min="0" value="{{ old('deposit_amount', $t->deposit_amount ?? 500000) }}" required class="{{ $in }}"></label>
    <label class="flex items-center gap-2 pt-6 text-sm"><input type="checkbox" name="allows_daily" value="1" x-model="daily" @checked(old('allows_daily', $t->allows_daily ?? false))> Sewa harian (maks. 1 orang)</label>
    <label class="text-sm" x-show="daily"><span class="mb-1 block font-medium">Tarif harian (Rp)</span><input type="number" name="daily_rate" min="0" value="{{ old('daily_rate', $t->daily_rate ?? '') }}" :required="daily" class="{{ $in }}"></label>
    <div class="sm:col-span-3"><button class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Simpan tipe</button></div>
</form>
