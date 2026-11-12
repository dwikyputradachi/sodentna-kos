@php use App\Support\Owner\Fmt; @endphp
<x-tenant.layout title="Tagihan" active="bills">
    <div><h1 class="text-xl font-semibold text-navy">Tagihan</h1><p class="text-sm text-slate-500">Lihat tagihan dan unggah bukti pembayaran.</p></div>

    @forelse ($invoices as $i)
        <div x-data="{ file: null, sent: false, err: '' }" class="space-y-4 rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                    <div class="text-xs text-slate-400">{{ $i['no'] }}</div>
                    <div class="text-2xl font-semibold text-navy">{{ Fmt::rp($i['amount']) }}</div>
                    <div class="text-sm text-slate-500">Sewa {{ $i['period'] }} · jatuh tempo {{ Fmt::date($i['due']) }}</div>
                </div>
                <div x-show="!sent"><x-owner.badge :label="$i['label']" /></div>
                <div x-show="sent" x-cloak><x-owner.badge label="Menunggu Verifikasi" /></div>
            </div>

            <div class="rounded-md bg-slate-50 p-3 text-sm text-slate-600">
                <div class="font-medium text-slate-700">Cara pembayaran</div>
                <p class="mt-1">Transfer sesuai nominal ke rekening pengelola, lalu unggah bukti di bawah. Nomor rekening dikonfirmasi oleh pengelola.</p>
            </div>

            <div x-show="!sent" class="space-y-3">
                <label class="block text-sm font-medium">Bukti pembayaran</label>
                <input type="file" accept="image/jpeg,image/png,application/pdf"
                       @change="file = $event.target.files[0]; err = (file && file.size > 5*1024*1024) ? 'Ukuran file maksimal 5 MB.' : ''"
                       class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700">
                <p class="text-xs text-slate-400">Format JPG, PNG, atau PDF. Maksimal 5 MB.</p>
                <p x-show="err" x-text="err" class="text-sm text-red-600"></p>
                <button @click="sent = true" :disabled="!file || err" class="rounded-md bg-brand px-4 py-2 text-sm font-medium text-white hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-40">Kirim bukti</button>
            </div>
            <p x-show="sent" x-cloak class="rounded-md bg-sky-50 p-3 text-sm text-sky-700">Bukti terkirim. Pengelola akan memeriksanya; pengingat dihentikan selama verifikasi.</p>
            <p class="text-xs text-slate-400">Tampilan simulasi: bukti belum tersimpan sampai backend dibuat.</p>
        </div>
    @empty
        <x-owner.card><p class="p-6 text-center text-sm text-slate-500">Tidak ada tagihan berjalan.</p></x-owner.card>
    @endforelse
</x-tenant.layout>
