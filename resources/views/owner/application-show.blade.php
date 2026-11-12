@php
use App\Support\Owner\Fmt;
$in = 'w-full rounded-md border border-slate-300 px-3 py-2 text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
$rows = [
    ['Tipe kamar', $a->type->name], ['Jenis sewa', $a->mode === 'daily' ? 'Harian' : 'Bulanan'], ['Penghuni', $a->occupants . ' orang'],
    ['Mulai', Fmt::date($a->start_date)], ['Selesai', $a->end_date ? Fmt::date($a->end_date) : '—'], ['Pembayaran', $a->payment_method === 'cash' ? 'Tunai' : 'Transfer'],
    ['Tarif sesuai tipe', $a->mode === 'daily' ? Fmt::rp($a->type->daily_rate ?? 0) . ' / hari' : Fmt::rp($a->type->monthlyRate($a->occupants)) . ' / bulan'],
    ['Deposit', Fmt::rp($a->type->deposit_amount)],
];
$person = [
    ['Nama', $a->name], ['WhatsApp', $a->phone], ['Tempat, tanggal lahir', $a->birth_place . ', ' . Fmt::date($a->birth_date)], ['Nomor identitas', $a->maskedId()],
    ['Pekerjaan', $a->job], ['Instansi', $a->institution], ['Alamat asal', $a->address],
    ['Kontak darurat', $a->emergency_name . ' (' . $a->emergency_relation . ') · ' . $a->emergency_phone], ['Alamat kontak darurat', $a->emergency_address],
];
@endphp
<x-owner.layout :title="'Pengajuan ' . $a->code" active="applications">
    <a href="{{ route('owner.applications') }}" class="text-sm text-brand-700">Kembali ke Pengajuan Sewa</a>
    <x-owner.header :title="$a->name" :subtitle="$a->code">
        <x-owner.badge :label="$a->statusLabel()" />
    </x-owner.header>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if (session('err'))<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('err') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    @if ($activation)
        <div class="space-y-2 rounded-lg border border-brand/30 bg-brand-50 p-4 text-sm">
            <div class="font-semibold text-brand-700">Tautan aktivasi akun penghuni</div>
            <p class="text-slate-600">Tautan hanya ditampilkan sekali. Kirim lewat WhatsApp (Anda menekan Kirim sendiri).</p>
            <input readonly value="{{ $activation['url'] }}" onclick="this.select()" class="{{ $in }}">
            <a href="{{ $activation['wa'] }}" target="_blank" rel="noopener" class="inline-block rounded-md bg-brand px-3 py-2 font-medium text-white">Kirim via WhatsApp</a>
        </div>
    @endif

    <div class="grid gap-4 lg:grid-cols-2">
        <x-owner.card title="Permintaan sewa">
            <dl class="grid grid-cols-3 gap-2 p-4 text-sm">@foreach ($rows as [$l, $v])<dt class="text-slate-500">{{ $l }}</dt><dd class="col-span-2">{{ $v }}</dd>@endforeach</dl>
        </x-owner.card>
        <x-owner.card title="Data pemohon">
            <dl class="grid grid-cols-3 gap-2 p-4 text-sm">@foreach ($person as [$l, $v])<dt class="text-slate-500">{{ $l }}</dt><dd class="col-span-2">{{ $v }}</dd>@endforeach
                <dt class="text-slate-500">Foto identitas</dt><dd class="col-span-2"><a href="{{ route('owner.applications.id', $a) }}" target="_blank" class="font-medium text-brand-700">Buka berkas</a></dd></dl>
        </x-owner.card>
    </div>

    @if ($a->status === 'pending')
        <div class="grid gap-4 lg:grid-cols-2">
            <x-owner.card title="Setujui">
                <form method="POST" action="{{ route('owner.applications.approve', $a) }}" onsubmit="return confirm('Setujui pengajuan ini dan buat akun penghuni?')" class="space-y-3 p-4 text-sm">@csrf
                    <label class="block"><span class="mb-1 block font-medium">Kamar yang diberikan</span>
                        <select name="room_id" required class="{{ $in }}">
                            @forelse ($rooms as $r)<option value="{{ $r->id }}">{{ $r->code }} · {{ $r->floor->name }} · {{ $r->type->name }}</option>@empty<option value="">Tidak ada kamar tersedia yang cocok</option>@endforelse
                        </select></label>
                    <button @disabled($rooms->isEmpty()) class="rounded-md bg-brand px-4 py-2 font-medium text-white disabled:cursor-not-allowed disabled:opacity-40">Setujui</button>
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
                <p>Kamar {{ $a->room->code ?? '—' }} · sewa {{ $rental->status ?? '—' }} · tarif {{ $rental ? Fmt::rp($rental->rate) : '—' }}</p>
                <div class="flex flex-wrap gap-2">
                    @if ($rental && $rental->status === 'reserved')
                        <form method="POST" action="{{ route('owner.applications.checkin', $a) }}" onsubmit="return confirm('Mulai sewa? Kamar akan menjadi Terisi.')">@csrf<button class="rounded-md bg-navy px-4 py-2 font-medium text-white">Mulai sewa (check-in)</button></form>
                    @endif
                    @if ($a->tenant && $a->tenant->user && ! $a->tenant->user->password)
                        <form method="POST" action="{{ route('owner.applications.invitation', $a) }}">@csrf<button class="rounded-md border border-slate-300 px-4 py-2 font-medium">Buat tautan aktivasi baru</button></form>
                    @else
                        <span class="text-xs text-slate-500">Akun penghuni sudah aktif.</span>
                    @endif
                </div>
            </div>
        </x-owner.card>
    @endif
</x-owner.layout>
