@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Pengaturan" active="settings">
    <x-owner.header title="Pengaturan" subtitle="Semua aturan bisa diubah pemilik. Tampilan ini hanya contoh; penyimpanan aktif di Fase 2." />

    <div x-data="{ tab: 'aturan' }" class="space-y-4">
        <div class="flex gap-1 overflow-x-auto border-b border-slate-200 text-sm">
            @foreach (['properti' => 'Properti', 'aturan' => 'Aturan sewa', 'penagihan' => 'Penagihan', 'pesan' => 'Pesan WhatsApp', 'keputusan' => 'Keputusan owner'] as $k => $l)
                <button @click="tab = '{{ $k }}'" :class="tab === '{{ $k }}' ? 'border-brand font-medium text-navy' : 'border-transparent text-slate-500'" class="whitespace-nowrap border-b-2 px-3 py-2">{{ $l }}</button>
            @endforeach
        </div>

        <div x-show="tab === 'properti'" class="grid gap-4 rounded-lg border border-slate-200 bg-white p-4 text-sm sm:grid-cols-2">
            @foreach ([['Nama properti', 'Kos Sodentna Kartini 6'], ['Alamat', \App\Support\Site\DemoSite::ADDRESS], ['WhatsApp pengelola', '0813 6444 9270'], ['Zona waktu', 'Asia/Jakarta']] as [$l, $v])
                <label><span class="mb-1 block font-medium">{{ $l }}</span><input value="{{ $v }}" class="w-full rounded-md border border-slate-300 px-3 py-2"></label>
            @endforeach
        </div>

        <div x-show="tab === 'aturan'" x-cloak class="rounded-lg border border-slate-200 bg-white p-4 text-sm">
            <p>Kapasitas, tarif per jumlah penghuni, deposit, dan sewa harian kini diatur per tipe kamar.</p>
            <a href="{{ route('owner.rooms') }}" class="mt-2 inline-block font-medium text-brand-700">Buka Kamar &amp; Lantai</a>
        </div>

        <div x-show="tab === 'penagihan'" x-cloak class="space-y-4 rounded-lg border border-slate-200 bg-white p-4 text-sm">
            <label class="block"><span class="mb-1 block font-medium">Metode pembayaran</span><input value="Transfer, Tunai" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:w-80"></label>
            <label class="block"><span class="mb-1 block font-medium">Pengingat sebelum jatuh tempo</span><input value="H-2" class="w-full rounded-md border border-slate-300 px-3 py-2 sm:w-80"></label>
            <label class="block"><span class="mb-1 block font-medium">Mulai sewa tanggal 29, 30, 31</span>
                <select class="w-full rounded-md border border-slate-300 px-3 py-2 sm:w-80"><option>Belum ditentukan</option><option>Jatuh tempo di hari terakhir bulan pendek</option></select></label>
            <p class="text-xs text-slate-500">Nomor rekening transfer diisi di sini dan tampil di portal penghuni.</p>
        </div>

        <div x-show="tab === 'pesan'" x-cloak class="space-y-3">
            @foreach (['reminder' => 'H-2', 'due' => 'Jatuh tempo', 'overdue' => 'Terlambat'] as $k => $l)
                <div class="rounded-lg border border-slate-200 bg-white p-4 text-sm"><div class="mb-1 font-medium text-navy">{{ $l }}</div><p class="text-slate-600">{{ $sample[$k] }}</p></div>
            @endforeach
            <p class="text-xs text-slate-500">Contoh pesan dengan data tagihan demo. Pesan dikirim manual lewat WhatsApp.</p>
        </div>

        <div x-show="tab === 'keputusan'" x-cloak class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
            <div class="mb-2 font-medium">Perlu dikonfirmasi pemilik sebelum aturan dikunci</div>
            <ul class="list-disc space-y-1 pl-5">@foreach ($decisions as $d)<li>{{ $d }}</li>@endforeach</ul>
        </div>
    </div>
</x-owner.layout>
