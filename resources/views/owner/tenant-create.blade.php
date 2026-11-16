@php $in = 'w-full rounded-md border border-slate-300 px-3 py-2.5 text-base sm:text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand'; $lbl = 'mb-1 block text-sm font-medium'; @endphp
<x-owner.layout title="Tambah Penghuni" active="tenants">
    <a href="{{ route('owner.tenants') }}" class="text-sm text-brand-700">Kembali ke Penghuni</a>
    <x-owner.header title="Tambah Penghuni" subtitle="Untuk penghuni yang sudah tinggal di kos (pindahan dari catatan Excel) atau datang langsung. Kamar langsung berstatus Terisi." />

    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('owner.tenants.store') }}" class="space-y-5"
          x-data="{ rooms: @js($rooms), room: '{{ old('room_id') }}', occ: {{ (int) old('occupants', 1) }}, rate: '{{ old('rate') }}', deposit: '{{ old('deposit_amount') }}',
                    get r() { return this.rooms.find(x => String(x.id) === String(this.room)) },
                    sync() { const r = this.r; if (!r) return; if (this.occ > r.cap) this.occ = r.cap; this.rate = this.occ === 2 ? r.rate_two : r.rate_one; this.deposit = r.deposit } }">
        @csrf
        <x-owner.card title="Sewa">
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <label class="text-sm"><span class="{{ $lbl }}">Kamar (yang tersedia)</span>
                    <select name="room_id" x-model="room" @change="sync()" required class="{{ $in }}"><option value="">Pilih kamar</option><template x-for="x in rooms" :key="x.id"><option :value="x.id" x-text="x.label"></option></template></select></label>
                <div class="text-sm"><span class="{{ $lbl }}">Jumlah penghuni</span>
                    <div class="flex gap-4 py-2"><label class="flex items-center gap-2"><input type="radio" name="occupants" value="1" x-model.number="occ" @change="sync()"> 1 orang</label>
                        <label class="flex items-center gap-2"><input type="radio" name="occupants" value="2" x-model.number="occ" @change="sync()" :disabled="r && r.cap < 2"> 2 orang</label></div></div>
                <label class="text-sm"><span class="{{ $lbl }}">Tanggal masuk</span><input type="date" name="start_date" value="{{ old('start_date') }}" required class="{{ $in }}"><span class="text-xs text-slate-400">Boleh tanggal lampau. Jatuh tempo bulanan mengikuti tanggal ini.</span></label>
                <label class="text-sm"><span class="{{ $lbl }}">Tarif per bulan (Rp)</span><input type="number" min="0" name="rate" x-model="rate" required class="{{ $in }}"><span class="text-xs text-slate-400">Terisi otomatis dari tipe kamar; ubah bila penghuni lama punya tarif lain.</span></label>
                <label class="text-sm"><span class="{{ $lbl }}">Deposit (Rp)</span><input type="number" min="0" name="deposit_amount" x-model="deposit" required class="{{ $in }}"></label>
                <label class="text-sm"><span class="{{ $lbl }}">Jatuh tempo berikutnya</span><input type="date" name="next_due_date" value="{{ old('next_due_date') }}" class="{{ $in }}"><span class="text-xs text-slate-400">Kosongkan = tanggal masuk bulan depan. Isi tanggal lampau bila penghuni menunggak, agar tagihannya langsung muncul.</span></label>
            </div>
        </x-owner.card>

        <x-owner.card title="Penghuni">
            <div class="grid gap-4 p-4 sm:grid-cols-2">
                <label class="text-sm"><span class="{{ $lbl }}">Nama lengkap</span><input name="name" value="{{ old('name') }}" required class="{{ $in }}"></label>
                <label class="text-sm" x-show="occ === 2" x-cloak><span class="{{ $lbl }}">Nama lengkap 2</span><input name="name_2" value="{{ old('name_2') }}" class="{{ $in }}"></label>
                <label class="text-sm"><span class="{{ $lbl }}">Nomor WhatsApp</span><input type="tel" inputmode="tel" name="phone" value="{{ old('phone') }}" required class="{{ $in }}"></label>
            </div>
            <details class="border-t border-slate-100 p-4 text-sm"><summary class="cursor-pointer font-medium text-navy">Data tambahan (opsional, bisa dilengkapi nanti)</summary>
                <div class="mt-3 grid gap-4 sm:grid-cols-2">
                    @foreach ([['id_number', 'No. KTP / identitas', 'text'], ['birth_place', 'Tempat lahir', 'text'], ['birth_date', 'Tanggal lahir', 'date'], ['job', 'Pekerjaan / status', 'text'], ['institution', 'Instansi / kampus / perusahaan', 'text'], ['address', 'Alamat asal', 'text'],
                        ['emergency_name', 'Kontak darurat (nama)', 'text'], ['emergency_relation', 'Hubungan', 'text'], ['emergency_phone', 'Kontak darurat (telepon)', 'tel'], ['emergency_address', 'Alamat kontak darurat', 'text']] as [$n, $l, $ty])
                        <label class="text-sm"><span class="{{ $lbl }}">{{ $l }}</span><input type="{{ $ty }}" name="{{ $n }}" value="{{ old($n) }}" class="{{ $in }}"></label>
                    @endforeach
                </div>
            </details>
        </x-owner.card>

        <label class="flex gap-2 text-sm"><input type="checkbox" name="create_account" value="1" checked class="mt-1"> <span>Buat akun portal penghuni (tautan aktivasi dikirim lewat WhatsApp setelah disimpan).</span></label>
        <button class="rounded-md bg-brand px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Simpan penghuni</button>
    </form>
</x-owner.layout>
