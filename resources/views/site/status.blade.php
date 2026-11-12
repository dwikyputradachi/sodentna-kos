<x-site.layout title="Status Pengajuan" active="status">
    <div class="max-w-xl space-y-4">
        <div><h1 class="text-2xl font-semibold text-navy">Status Pengajuan</h1><p class="mt-1 text-sm text-slate-500">Masukkan kode pengajuan dan 4 digit terakhir nomor WhatsApp yang Anda daftarkan.</p></div>
        @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form method="POST" action="{{ route('site.status.check') }}" class="flex flex-wrap gap-2">@csrf
            <input name="code" value="{{ old('code') }}" required placeholder="SDN-12345" class="w-40 rounded-md border border-slate-300 px-3 py-2 text-sm">
            <input name="last4" inputmode="numeric" maxlength="4" required placeholder="4 digit" class="w-28 rounded-md border border-slate-300 px-3 py-2 text-sm">
            <button class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white">Cek status</button>
        </form>

        @if ($result === false)
            <p class="text-sm text-red-600">Kode atau nomor tidak cocok. Periksa kembali.</p>
        @elseif ($result)
            <div class="space-y-1 rounded-lg border border-slate-200 bg-white p-4 text-sm">
                <div class="text-xs text-slate-400">{{ $result['code'] }}</div>
                @if ($result['status'] === 'pending')
                    <div class="font-semibold text-amber-700">Menunggu ditinjau</div><p class="text-slate-600">Pengajuan Anda sudah diterima dan sedang ditinjau pengelola.</p>
                @elseif ($result['status'] === 'approved')
                    <div class="font-semibold text-emerald-700">Disetujui</div><p class="text-slate-600">Pengelola akan menghubungi Anda lewat WhatsApp untuk langkah pembayaran dan aktivasi akun.</p>
                @else
                    <div class="font-semibold text-red-700">Belum dapat disetujui</div><p class="text-slate-600">{{ $result['reason'] ?: 'Hubungi pengelola untuk informasi lebih lanjut.' }}</p>
                @endif
            </div>
        @endif
    </div>
</x-site.layout>
