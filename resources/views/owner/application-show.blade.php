@php
use App\Support\Owner\Fmt;
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
$rows = [
    ['Kamar pilihan pemohon', $a->room ? $a->room->code . ' · ' . ($a->room->floor->name ?? '') : '—'], ['Tipe kamar', $a->type->name], ['Jenis sewa', $a->mode === 'daily' ? 'Harian' : 'Bulanan'], ['Penghuni', $a->occupants . ' orang'],
    ['Mulai', Fmt::date($a->start_date)], ['Selesai', $a->end_date ? Fmt::date($a->end_date) : '—'], ['Pembayaran', $a->payment_method === 'cash' ? 'Tunai' : 'Transfer'],
    ['Tarif sesuai tipe', $a->mode === 'daily' ? Fmt::rp($a->type->daily_rate ?? 0) . ' / hari' : Fmt::rp($a->type->monthlyRate($a->occupants)) . ' / bulan'],
    ['Deposit', Fmt::rp($a->type->deposit_amount)],
];
$person = [
    ['Nama', $a->name . ($a->name_2 ? ' & ' . $a->name_2 : '')], ['WhatsApp', $a->phone], ['Tempat, tanggal lahir', $a->birth_place ? $a->birth_place . ', ' . Fmt::date($a->birth_date) : '—'], ['Nomor identitas', $a->maskedId()], ['Agama', $a->religion ?: '—'],
    ['Pekerjaan', $a->job ?: '—'], ['Instansi', $a->institution ?: '—'], ['Alamat asal', $a->address],
    ['Kontak darurat', $a->emergency_name . ' (' . $a->emergency_relation . ') · ' . $a->emergency_phone], ['Alamat kontak darurat', $a->emergency_address ?: '—'],
];
@endphp
<x-owner.layout :title="'Pengajuan ' . $a->code" active="applications">
    <a href="{{ route('owner.applications') }}" class="text-sm text-brand-700">Kembali ke Pengajuan Sewa</a>
    <x-owner.header :title="$a->name" :subtitle="$a->code">
        <x-owner.badge :label="$a->statusLabel()" />
    </x-owner.header>

    <div class="flex flex-wrap items-center gap-2 text-sm">
        <span class="text-slate-500">Kode pengajuan:</span><span class="font-semibold">{{ $a->code }}</span><x-copy :text="$a->code" />
        <a href="https://wa.me/{{ \App\Support\Owner\Billing::phone($a->phone) }}?text={{ rawurlencode('Halo ' . $a->name . ', kode pengajuan sewa Kos Sodentna Anda: ' . $a->code . '. Cek status di halaman Status Pengajuan dengan kode ini dan 4 digit terakhir nomor WhatsApp Anda.') }}" target="_blank" rel="noopener" class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium">Kirim kode ke pemohon via WhatsApp</a>
    </div>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if (session('err'))<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('err') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @if ($activation)
        <div class="space-y-2 rounded-lg border border-brand/30 bg-brand-50 p-4 text-sm">
            <div class="font-semibold text-brand-700">{{ ($activation['existing'] ?? false) ? 'Pemberitahuan persetujuan ke penghuni' : 'Tautan aktivasi akun penghuni' }}</div>
            <p class="text-slate-600">{{ ($activation['existing'] ?? false) ? 'Penghuni sudah punya akun aktif, jadi pesan berisi alamat login. Kirim lewat WhatsApp (Anda menekan Kirim sendiri).' : 'Tautan hanya ditampilkan sekali dan sekali pakai. Kirim lewat WhatsApp (Anda menekan Kirim sendiri).' }}</p>
            <div class="flex gap-2"><input readonly value="{{ $activation['url'] }}" onclick="this.select()" class="{{ $in }}"><x-copy :text="$activation['url']" /></div>
            <a href="{{ $activation['wa'] }}" target="_blank" rel="noopener" class="inline-block rounded-md bg-brand px-3 py-2 font-medium text-white">Kirim via WhatsApp</a>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-owner.card title="Permintaan sewa">
            <dl class="grid grid-cols-3 gap-2 p-4 text-sm">@foreach ($rows as [$l, $v])<dt class="text-slate-500">{{ $l }}</dt><dd class="col-span-2">{{ $v }}</dd>@endforeach</dl>
        </x-owner.card>
        <x-owner.card title="Data pemohon">
            <dl class="grid grid-cols-3 gap-2 p-4 text-sm">@foreach ($person as [$l, $v])<dt class="text-slate-500">{{ $l }}</dt><dd class="col-span-2">{{ $v }}</dd>@endforeach
                <dt class="text-slate-500">Foto identitas</dt><dd class="col-span-2"><a href="{{ route('owner.applications.id', $a) }}" target="_blank" class="font-medium text-brand-700">Buka berkas</a></dd>@if ($a->id_doc_2_path)<dt class="text-slate-500">Identitas ke-2</dt><dd class="col-span-2"><a href="{{ route('owner.applications.id2', $a) }}" target="_blank" class="font-medium text-brand-700">Buka berkas</a></dd>@endif</dl>
        </x-owner.card>
    </div>

    @if ($a->status === 'pending')
        <div class="grid gap-4 lg:grid-cols-2">
            <x-owner.card title="Setujui">
                @php $req = $a->room; $reqOk = $req && $req->status === 'available'; @endphp
                <form method="POST" action="{{ route('owner.applications.approve', $a) }}" onsubmit="return confirm('Setujui pengajuan ini?')" class="space-y-3 p-4 text-sm">@csrf
                    @if ($reqOk)
                        <p>Kamar pilihan pemohon: <span class="font-semibold">{{ $req->code }}</span> · {{ $req->floor->name ?? '' }}</p>
                        <input type="hidden" name="room_id" value="{{ $req->id }}">
                        <button class="rounded-md bg-brand px-4 py-2 font-medium text-white">Setujui</button>
                    @else
                        <div class="rounded-md bg-amber-50 p-2 text-amber-800">Kamar pilihan pemohon ({{ $req->code ?? '—' }}) sudah tidak tersedia. Hubungi pemohon, lalu pilih kamar pengganti di bawah atau tolak pengajuan.</div>
                        <select name="room_id" required class="{{ $in }}">
                            @forelse ($rooms as $r)<option value="{{ $r->id }}">{{ $r->code }} · {{ $r->floor->name }} · {{ $r->type->name }}</option>@empty<option value="">Tidak ada kamar tersedia yang cocok</option>@endforelse
                        </select>
                        <button @disabled($rooms->isEmpty()) class="rounded-md bg-brand px-4 py-2 font-medium text-white disabled:cursor-not-allowed disabled:opacity-40">Setujui dengan kamar ini</button>
                    @endif
                    <p class="text-xs text-slate-400">Membuat data penghuni, akun, dan sewa; kamar menjadi Dipesan. Pembayaran awal dicatat pada fase penagihan.</p>
                </form>
            </x-owner.card>
            <x-owner.card title="Tolak">
                <form method="POST" action="{{ route('owner.applications.reject', $a) }}" onsubmit="return confirm('Tolak pengajuan ini?')" class="space-y-3 p-4 text-sm">@csrf
                    <label class="block"><span class="mb-1 block font-medium">Alasan (dilihat pemohon)</span><input name="reason" required maxlength="255" class="{{ $in }}"></label>
                    <button class="rounded-md border border-red-200 px-4 py-2 font-medium text-red-700">Tolak pengajuan</button>
                </form>
            </x-owner.card>
        </div>
    @elseif ($a->status === 'rejected')
        <p class="text-sm text-slate-600">Alasan penolakan: {{ $a->reject_reason }}</p>
    @else
        <x-owner.card title="Setelah disetujui">
            <div class="space-y-3 p-4 text-sm">
                <p>Kamar {{ $a->room->code ?? '—' }} · sewa {{ ['reserved' => 'Dipesan', 'active' => 'Aktif', 'pending_checkout' => 'Proses keluar', 'ended' => 'Selesai'][$rental->status ?? ''] ?? '—' }} · tarif {{ $rental ? Fmt::rp($rental->rate) : '—' }}</p>
                <div class="flex flex-wrap gap-2">
                    @if ($rental && $rental->status === 'reserved')
                        <form method="POST" action="{{ route('owner.applications.checkin', $a) }}" onsubmit="return confirm('Mulai sewa? Kamar akan menjadi Terisi.')">@csrf<button class="rounded-md bg-navy px-4 py-2 font-medium text-white">Mulai sewa (check-in)</button></form>
                    @endif
                    @if ($a->tenant && $a->tenant->user)
                        @php $active = (bool) $a->tenant->user->password; @endphp
                        <p class="w-full text-xs text-slate-500">Akun penghuni: <span class="font-medium {{ $active ? 'text-emerald-700' : 'text-amber-700' }}">{{ $active ? 'sudah aktif' : 'menunggu aktivasi' }}</span></p>
                        <form method="POST" action="{{ route('owner.applications.invitation', $a) }}">@csrf<button class="rounded-md border border-slate-300 px-4 py-2 font-medium">{{ $active ? 'Kirim pemberitahuan via WhatsApp' : 'Buat tautan aktivasi & kirim via WhatsApp' }}</button></form>
                    @endif
                </div>
            </div>
        </x-owner.card>
    @endif
</x-owner.layout>
