@php
use App\Support\Owner\Fmt;
use App\Support\Whatsapp\AutoMessenger;
$kinds = ['reminder' => 'Pengingat H-2', 'due' => 'Jatuh tempo', 'overdue' => 'Keterlambatan', 'receipt' => 'Kuitansi', 'test' => 'Pesan uji'];
$in = 'w-full rounded-md border border-slate-300 px-3 py-2.5 text-base sm:text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
@endphp
<x-owner.layout title="Pengiriman WhatsApp" active="settings">
    <a href="{{ route('owner.billing') }}" class="text-sm text-brand-700">Kembali ke Tagihan</a>
    <x-owner.header title="Pengiriman WhatsApp" subtitle="Atur apakah pengingat, tagihan, dan kuitansi dikirim otomatis oleh bot." />

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if (session('err'))<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">{{ session('err') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif

    <div class="rounded-lg border p-4 text-sm {{ $enabled && $configured ? 'border-emerald-200 bg-emerald-50 text-emerald-800' : 'border-slate-200 bg-white text-slate-700' }}">
        Mode saat ini: <span class="font-semibold">{{ $enabled && $configured ? 'Otomatis lewat bot' : 'Tombol manual' }}</span>.
        @unless ($configured)<div class="mt-1 text-amber-700">Bot belum dikonfigurasi. Isi <code>WA_BOT_URL</code> dan <code>WA_BOT_KEY</code> di file <code>.env</code>, lalu jalankan <code>php artisan config:clear</code>.</div>@endunless
    </div>

    <x-owner.card title="Pengiriman otomatis">
        <form method="POST" action="{{ route('owner.whatsapp.update') }}" class="space-y-4 p-4 text-sm">@csrf @method('PUT')
            <label class="flex items-start gap-2"><input type="checkbox" name="wa_auto" value="1" class="mt-1" @checked($enabled) @disabled(! $configured)> <span><span class="font-medium">Kirim otomatis lewat bot</span><br><span class="text-slate-500">Hanya pukul 08.00–20.00 WIB, satu pesan per tahap per tagihan. Tombol manual tetap tersedia sebagai cadangan.</span></span></label>
            <div class="space-y-2 border-t border-slate-100 pt-3">
                <div class="font-medium text-navy">Pesan yang dikirim otomatis</div>
                @foreach (AutoMessenger::STAGES as $k => $label)
                    <label class="flex items-center gap-2"><input type="checkbox" name="stages[]" value="{{ $k }}" @checked(in_array($k, $stages, true))> {{ $label }}</label>
                @endforeach
                <p class="text-xs text-slate-400">Pengingat keterlambatan hanya dikirim pada 3 hari pertama setelah jatuh tempo.</p>
            </div>
            <button class="rounded-md bg-brand px-4 py-2.5 font-medium text-white hover:bg-brand-700">Simpan pengaturan</button>
        </form>
    </x-owner.card>

    <x-owner.card title="Kirim pesan uji" note="Pastikan bot berfungsi sebelum mengaktifkan mode otomatis.">
        <form method="POST" action="{{ route('owner.whatsapp.test') }}" class="flex flex-wrap gap-2 p-4 text-sm">@csrf
            <input name="phone" type="tel" inputmode="tel" placeholder="Nomor WhatsApp Anda, mis. 0812xxxxxxxx" required class="{{ $in }} sm:w-80" @disabled(! $configured)>
            <button class="rounded-md border border-slate-300 px-4 py-2.5 font-medium disabled:opacity-40" @disabled(! $configured)>Kirim pesan uji</button>
        </form>
    </x-owner.card>

    <x-owner.card title="Catatan penting">
        <ul class="list-disc space-y-1 p-4 pl-8 text-sm text-slate-600">
            <li>Gunakan <span class="font-medium">nomor WhatsApp khusus untuk bot</span>, bukan nomor pribadi.</li>
            <li>Bot ini tidak resmi: sewaktu-waktu bisa terputus. Bila gagal, tagihan menampilkan "Gagal kirim otomatis" dan Anda tinggal memakai tombol manual.</li>
            <li>Tautan kuitansi di pesan hanya bisa dibuka penghuni bila aplikasi sudah punya alamat internet publik (setelah dipasang di server).</li>
            <li>Pengiriman berjalan lewat penjadwal: jalankan <code>php artisan schedule:work</code> (uji coba) atau jadwalkan <code>php artisan schedule:run</code> tiap menit (server).</li>
        </ul>
    </x-owner.card>

    <x-owner.card title="Riwayat pengiriman terakhir">
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Waktu', 'Jenis', 'Tujuan', 'Saluran', 'Status', 'Keterangan'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($logs as $l)
                <tr>
                    <td class="whitespace-nowrap px-4 py-2 text-slate-500">{{ Fmt::date($l->created_at->copy()->timezone('Asia/Jakarta'), 'j M H:i') }}</td>
                    <td class="px-4 py-2">{{ $kinds[$l->kind] ?? $l->kind }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ substr($l->to_phone, 0, 5) . '••••' . substr($l->to_phone, -3) }}</td>
                    <td class="px-4 py-2 text-slate-500">{{ $l->channel === 'whatsapp_bot' ? 'Bot' : 'Tombol' }}</td>
                    <td class="px-4 py-2"><span class="rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ match($l->status) { 'sent' => 'bg-emerald-50 text-emerald-700 ring-emerald-200', 'failed' => 'bg-red-50 text-red-700 ring-red-200', default => 'bg-slate-100 text-slate-600 ring-slate-200' } }}">{{ match($l->status) { 'sent' => 'Terkirim', 'failed' => 'Gagal', default => 'Dibuka' } }}</span></td>
                    <td class="px-4 py-2 text-xs text-slate-500">{{ $l->error }}</td>
                </tr>
            @empty
                <tr><td colspan="6" class="px-4 py-6 text-center text-slate-500">Belum ada pengiriman.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
