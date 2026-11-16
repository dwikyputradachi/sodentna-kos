<x-site.layout title="Status Pengajuan" active="status">
    <div class="max-w-xl space-y-4"
         x-data="{ items: [], init() { try { this.items = JSON.parse(localStorage.getItem('sodentna_apps') || '[]') } catch (e) {} }, use(i) { this.$refs.code.value = i.code; this.$refs.last4.value = i.last4; this.$refs.form.submit() } }">
        <div><h1 class="text-2xl font-semibold text-navy">Status Pengajuan</h1><p class="mt-1 text-sm text-slate-500">Masukkan kode pengajuan dan 4 digit terakhir nomor WhatsApp yang Anda daftarkan.</p></div>

        <template x-if="items.length">
            <div class="space-y-2 rounded-lg border border-slate-200 bg-white p-4">
                <div class="text-sm font-medium text-navy">Pengajuan di perangkat ini</div>
                <template x-for="i in items" :key="i.code">
                    <div class="flex items-center justify-between gap-2 text-sm">
                        <div><span class="font-medium" x-text="i.code"></span> <span class="text-slate-500" x-text="'· ' + i.plan + ' · ' + new Date(i.at).toLocaleDateString('id-ID')"></span></div>
                        <button type="button" @click="use(i)" class="rounded-md border border-slate-300 px-2.5 py-1 text-xs font-medium">Lihat status</button>
                    </div>
                </template>
            </div>
        </template>

        @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <form x-ref="form" method="POST" action="{{ route('site.status.check') }}" class="flex flex-wrap gap-2">@csrf
            <input x-ref="code" name="code" value="{{ old('code') }}" required placeholder="SDN-12345" class="w-40 rounded-md border border-slate-300 px-3 py-2 text-base sm:text-sm">
            <input x-ref="last4" name="last4" inputmode="numeric" maxlength="4" required placeholder="4 digit" class="w-28 rounded-md border border-slate-300 px-3 py-2 text-base sm:text-sm">
            <button class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white">Cek status</button>
        </form>
        <p class="text-xs text-slate-400">Lupa kode? Hubungi pengelola, kode dapat dikirim ulang lewat WhatsApp.</p>

        @if ($result === false)
            <p class="text-sm text-red-600">Kode atau nomor tidak cocok. Periksa kembali.</p>
        @elseif ($result)
            <div class="space-y-1 rounded-lg border border-slate-200 bg-white p-4 text-sm">
                <div class="flex items-center gap-2 text-xs text-slate-400">{{ $result['code'] }} <x-copy :text="$result['code']" /></div>
                @if ($result['status'] === 'pending')
                    <div class="font-semibold text-amber-700">Menunggu ditinjau</div><p class="text-slate-600">Pengajuan Anda sudah diterima dan sedang ditinjau pengelola.</p>
                @elseif ($result['status'] === 'approved')
                    <div class="font-semibold text-emerald-700">Disetujui</div>
                    @if ($result['room'])<p class="text-slate-600">Kamar {{ $result['room'] }} sudah dipesan untuk Anda.</p>@endif
                    @if ($result['active'])
                        <p class="text-slate-600">Akun penghuni Anda sudah aktif. <a href="{{ route('login') }}" class="font-medium text-brand-700 underline">Masuk ke portal</a> dengan nomor WhatsApp dan kata sandi Anda.</p>
                    @else
                        <p class="text-slate-600">Akun Anda menunggu diaktifkan. Cek WhatsApp: pengelola mengirim tautan aktivasi. Belum menerima? Hubungi pengelola.</p>
                    @endif
                @else
                    <div class="font-semibold text-red-700">Belum dapat disetujui</div><p class="text-slate-600">{{ $result['reason'] ?: 'Hubungi pengelola untuk informasi lebih lanjut.' }}</p>
                @endif
            </div>
        @endif
    </div>
</x-site.layout>
