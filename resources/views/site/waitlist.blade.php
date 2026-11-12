@php $in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand'; @endphp
<x-site.layout title="Daftar Tunggu" active="waitlist">
    <div class="max-w-2xl space-y-5">
        <div>
            <h1 class="text-2xl font-semibold text-navy">Daftar Tunggu</h1>
            <p class="mt-1 text-sm text-slate-500">Kamar penuh? Tinggalkan data Anda. Saat ada kamar kosong, pengelola menghubungi Anda lewat WhatsApp sesuai urutan pendaftaran. Mendaftar tidak menjamin kamar dan tidak ada pembayaran.</p>
        </div>

        @if (session('done'))
            @php $d = session('done'); @endphp
            <div class="space-y-1 rounded-lg border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
                <div class="font-semibold">{{ $d['existing'] ? 'Anda sudah ada di daftar tunggu.' : 'Anda masuk daftar tunggu.' }}</div>
                <p>Kode: <span class="font-semibold">{{ $d['code'] }}</span> · Antrean ke-{{ $d['position'] }}</p>
                <p class="text-xs">Simpan kode ini. Pengelola akan menghubungi nomor WhatsApp Anda.</p>
            </div>
        @endif

        @if ($free > 0)
            <div class="rounded-md border border-amber-200 bg-amber-50 p-3 text-sm text-amber-800">Saat ini ada {{ $free }} kamar tersedia. <a href="{{ route('site.apply') }}" class="font-medium underline">Ajukan sewa langsung</a>.</div>
        @endif

        @if ($errors->any())
            <div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('site.waitlist.store') }}" x-data="{ occ: {{ (int) old('occupants', 1) }} }" class="space-y-4 rounded-lg border border-slate-200 bg-white p-5">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <label class="text-sm"><span class="mb-1 block font-medium">Nama lengkap</span><input name="name" value="{{ old('name') }}" required class="{{ $in }}"></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Nomor WhatsApp</span><input name="phone" value="{{ old('phone') }}" inputmode="tel" placeholder="0812xxxxxxxx" required class="{{ $in }}"></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Tipe kamar</span>
                    <select name="room_type_id" class="{{ $in }}"><option value="">Tipe apa saja</option>
                        @foreach ($types as $t)<option value="{{ $t->id }}" @selected(old('room_type_id') == $t->id)>{{ $t->name }} ({{ $t->capacity }} orang)</option>@endforeach</select></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Jumlah penghuni</span>
                    <select name="occupants" x-model.number="occ" class="{{ $in }}"><option value="1">1 orang</option><option value="2">2 orang</option></select></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Tanggal mulai yang diinginkan</span><input type="date" name="desired_start" value="{{ old('desired_start') }}" required class="{{ $in }}"></label>
                <label class="text-sm"><span class="mb-1 block font-medium">Catatan (opsional)</span><input name="notes" value="{{ old('notes') }}" maxlength="300" class="{{ $in }}"></label>
            </div>
            <label x-show="occ === 2" x-cloak class="flex gap-2 text-sm"><input type="checkbox" name="couple_ack" value="1" :required="occ === 2" class="mt-1"> <span>Saya memahami kamar tidak diperuntukkan bagi pasangan suami istri.</span></label>
            <button class="rounded-md bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Gabung daftar tunggu</button>
            <p class="text-xs text-slate-400">Hanya sewa bulanan. Data Anda hanya dilihat pengelola.</p>
        </form>
    </div>
</x-site.layout>
