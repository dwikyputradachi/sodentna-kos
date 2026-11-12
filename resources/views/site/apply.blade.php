@php
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
$fields = [['name', 'Nama lengkap', 'text'], ['birth_place', 'Tempat lahir', 'text'], ['birth_date', 'Tanggal lahir', 'date'], ['id_number', 'Nomor identitas (KTP)', 'text'],
    ['phone', 'Nomor WhatsApp', 'tel'], ['job', 'Pekerjaan / status', 'text'], ['institution', 'Instansi / kampus / perusahaan', 'text']];
$emerg = [['emergency_name', 'Nama'], ['emergency_relation', 'Hubungan'], ['emergency_phone', 'Nomor telepon'], ['emergency_address', 'Alamat']];
@endphp
<x-site.layout title="Pengajuan Sewa" active="apply">
    <div class="max-w-3xl space-y-5">
        <div><h1 class="text-2xl font-semibold text-navy">Pengajuan Sewa</h1><p class="mt-1 text-sm text-slate-500">Isi data dengan benar. Kamar belum dipesan sampai pengajuan disetujui pengelola.</p></div>

        @if (session('done'))
            @php $d = session('done'); @endphp
            <div class="space-y-2 rounded-lg border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800">
                <div class="font-semibold">{{ $d['existing'] ? 'Pengajuan Anda sudah tercatat sebelumnya.' : 'Pengajuan terkirim.' }}</div>
                <p>Kode pengajuan: <span class="font-semibold">{{ $d['code'] }}</span>. Simpan kode ini.</p>
                <p>Cek status di halaman <a href="{{ route('site.status') }}" class="font-medium underline">Status Pengajuan</a> dengan kode dan 4 digit terakhir nomor WhatsApp Anda.</p>
            </div>
        @elseif (empty($types))
            <div class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">Pengajuan belum dibuka. Silakan hubungi pengelola.</div>
        @else
            @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('site.apply.store') }}" enctype="multipart/form-data" class="space-y-6"
                  x-data="{ type: '{{ old('room_type_id', $sel) }}', mode: '{{ old('mode', $mode) }}', occ: {{ (int) old('occupants', 1) }},
                    types: @js(collect($types)->map(fn ($t) => ['cap' => $t['cap'], 'daily' => (bool) $t['daily']])->all()),
                    get dailyOk() { return this.types[this.type].daily },
                    get maxOcc() { return this.mode === 'daily' ? 1 : this.types[this.type].cap },
                    fix() { if (this.mode === 'daily' && !this.dailyOk) this.mode = 'monthly'; if (this.occ > this.maxOcc) this.occ = this.maxOcc } }" x-init="fix()">
                @csrf
                <x-owner.card title="Pilihan kamar">
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <label class="text-sm"><span class="mb-1 block font-medium">Tipe kamar</span>
                            <select name="room_type_id" x-model="type" @change="fix()" class="{{ $in }}">
                                @foreach ($types as $id => $t)<option value="{{ $id }}">{{ $t['name'] }} — {{ $t['price'] }}/bulan</option>@endforeach
                            </select></label>
                        <label class="text-sm"><span class="mb-1 block font-medium">Jenis sewa</span>
                            <select name="mode" x-model="mode" @change="fix()" class="{{ $in }}"><option value="monthly">Bulanan</option><option value="daily" :disabled="!dailyOk">Harian</option></select></label>
                        <label class="text-sm"><span class="mb-1 block font-medium">Jumlah penghuni</span>
                            <input type="number" name="occupants" min="1" :max="maxOcc" x-model.number="occ" required class="{{ $in }}"><span class="text-xs text-slate-400" x-text="'Maksimal ' + maxOcc + ' orang'"></span></label>
                        <label class="text-sm"><span class="mb-1 block font-medium">Tanggal mulai</span><input type="date" name="start_date" value="{{ old('start_date') }}" required class="{{ $in }}"></label>
                        <label class="text-sm" x-show="mode === 'daily'" x-cloak><span class="mb-1 block font-medium">Tanggal selesai</span><input type="date" name="end_date" value="{{ old('end_date') }}" :required="mode === 'daily'" class="{{ $in }}"></label>
                        <label class="text-sm"><span class="mb-1 block font-medium">Metode pembayaran</span>
                            <select name="payment_method" class="{{ $in }}"><option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option><option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option></select></label>
                    </div>
                    <div x-show="mode === 'daily'" x-cloak class="mx-4 mb-4 rounded-md bg-amber-50 p-3 text-sm text-amber-800">Sewa harian maksimal satu orang per kamar.</div>
                    <div x-show="types[type].cap >= 2" x-cloak class="mx-4 mb-4 rounded-md bg-amber-50 p-3 text-sm text-amber-800">Kamar ini tidak diperuntukkan bagi pasangan suami istri.</div>
                </x-owner.card>

                <x-owner.card title="Data diri">
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        @foreach ($fields as [$n, $l, $ty])
                            <label class="text-sm"><span class="mb-1 block font-medium">{{ $l }}</span><input type="{{ $ty }}" name="{{ $n }}" value="{{ old($n) }}" required class="{{ $in }}"></label>
                        @endforeach
                        <label class="text-sm sm:col-span-2"><span class="mb-1 block font-medium">Alamat asal</span><textarea name="address" rows="2" required class="{{ $in }}">{{ old('address') }}</textarea></label>
                        <label class="text-sm sm:col-span-2"><span class="mb-1 block font-medium">Foto identitas</span>
                            <input type="file" name="id_doc" accept="image/jpeg,image/png,application/pdf" required class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700">
                            <span class="text-xs text-slate-400">JPG, PNG, atau PDF, maksimal 5 MB. Disimpan privat dan hanya dilihat pengelola.</span></label>
                    </div>
                </x-owner.card>

                <x-owner.card title="Kontak darurat">
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        @foreach ($emerg as [$n, $l])
                            <label class="text-sm"><span class="mb-1 block font-medium">{{ $l }}</span><input name="{{ $n }}" value="{{ old($n) }}" required class="{{ $in }}"></label>
                        @endforeach
                    </div>
                </x-owner.card>

                <div class="space-y-2 text-sm">
                    <label class="flex gap-2"><input type="checkbox" name="consent_rules" value="1" required class="mt-1"> <span>Saya telah membaca dan menyetujui <a href="{{ route('site.about') }}" target="_blank" class="text-brand-700 underline">peraturan kos</a>.</span></label>
                    <label x-show="occ === 2" x-cloak class="flex gap-2"><input type="checkbox" name="couple_ack" value="1" :required="occ === 2" class="mt-1"> <span>Saya memahami kamar tidak untuk pasangan suami istri.</span></label>
                    <label class="flex gap-2"><input type="checkbox" name="consent_data" value="1" required class="mt-1"> <span>Data yang saya isi benar, dan saya setuju data pribadi saya diproses pengelola untuk keperluan sewa.</span></label>
                </div>
                <button class="rounded-md bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Kirim pengajuan</button>
            </form>
        @endif
    </div>
</x-site.layout>
