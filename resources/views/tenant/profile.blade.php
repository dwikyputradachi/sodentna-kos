<x-tenant.layout title="Profil" active="profile">
    <div><h1 class="text-xl font-semibold text-navy">Profil</h1><p class="text-sm text-slate-500">Data pribadi Anda. Perubahan nama atau identitas hubungi pengelola.</p></div>

    <form x-data="{ saved: false }" @submit.prevent="saved = true" class="space-y-4 rounded-lg border border-slate-200 bg-white p-4">
        @php
        $f = [
            ['Nama lengkap', $p['name'], true], ['Nomor identitas', $p['ktp_masked'], true], ['Email', $p['email'], false],
            ['Nomor WhatsApp', $p['phone'], false], ['Kontak darurat (nama)', $p['emergency_name'], false],
            ['Kontak darurat (telepon)', $p['emergency_phone'], false],
        ];
        @endphp
        <div class="grid gap-4 sm:grid-cols-2">
            @foreach ($f as [$label, $val, $ro])
                <label class="block text-sm">
                    <span class="mb-1 block font-medium">{{ $label }}</span>
                    <input value="{{ $val }}" @disabled($ro)
                           class="w-full rounded-md border border-slate-200 px-3 py-2 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand disabled:bg-slate-50 disabled:text-slate-500">
                </label>
            @endforeach
        </div>
        <div class="flex items-center gap-3">
            <button class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-700">Simpan profil</button>
            <span x-show="saved" x-cloak class="text-sm text-emerald-600">Tersimpan (simulasi).</span>
        </div>
    </form>
</x-tenant.layout>
