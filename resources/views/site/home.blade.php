@php
    use App\Support\Owner\Fmt;
    use App\Support\Site\DemoSite;
@endphp

<x-site.layout title="Beranda" active="home">
    <div class="space-y-14 sm:space-y-20">

        {{-- 1. HERO SECTION --}}
        <section class="grid items-center gap-8 lg:grid-cols-12 lg:gap-10">
            <div class="space-y-5 lg:col-span-7">
                {{-- Status Ketersediaan --}}
                <div class="inline-flex items-center gap-2 rounded-full border border-brand/20 bg-brand-50 px-3.5 py-1.5 text-xs font-medium text-brand-700">
                    <span class="h-2 w-2 rounded-full {{ $free > 0 ? 'bg-brand animate-pulse' : 'bg-amber-500' }}"></span>
                    <span>
                        {{ $free > 0 ? $free . ' kamar tersedia saat ini' : 'Kamar saat ini penuh · Cek jadwal tersedia' }}
                    </span>
                </div>

                <div class="space-y-3">
                    <h1 class="text-3xl font-semibold tracking-tight text-navy sm:text-4xl lg:leading-[1.18]">
                        Kos nyaman di Sekupang, siap ditempati.
                    </h1>
                    <p class="max-w-xl text-base leading-relaxed text-slate-600">
                        Hunian terawat di Jl. Kartini VI dengan AC, kamar mandi dalam, Wi-Fi, CCTV, dan area parkir. Cek ketersediaan kamar, ajukan sewa, dan pantau tagihan dari satu tempat.
                    </p>
                </div>

                {{-- CTA Utama --}}
                <div class="flex flex-wrap items-center gap-3 pt-1">
                    <a href="{{ route('site.rooms') }}" class="inline-flex items-center justify-center gap-2 rounded-lg bg-brand px-5 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700">
                        <span>Cek Ketersediaan Kamar</span>
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                        </svg>
                    </a>
                    <a href="{{ route('site.apply') }}" class="inline-flex items-center justify-center rounded-lg border border-slate-300 bg-white px-5 py-2.5 text-sm font-semibold text-navy transition hover:border-slate-400 hover:bg-slate-50">
                        Ajukan Sewa
                    </a>
                </div>

                {{-- 3 Poin Ringkas (Baseline Figma) --}}
                <div class="grid grid-cols-3 gap-4 border-t border-slate-200/80 pt-5">
                    <div>
                        <p class="text-xs font-semibold text-navy">Sewa jelas</p>
                        <p class="mt-0.5 text-xs text-slate-500">Pilihan bulanan &amp; harian</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-navy">Deposit sekali</p>
                        <p class="mt-0.5 text-xs text-slate-500">Dana titipan, bukan sewa</p>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-navy">Dikelola langsung</p>
                        <p class="mt-0.5 text-xs text-slate-500">Tanpa perantara</p>
                    </div>
                </div>
            </div>

            {{-- Visual Card Properti --}}
            <div class="lg:col-span-5">
                <div class="relative overflow-hidden rounded-2xl border border-slate-200 bg-gradient-to-br from-navy via-[#17355e] to-brand p-6 text-white shadow-sm sm:aspect-[4/3] sm:flex sm:flex-col sm:justify-between">
                    <div class="flex items-center justify-between gap-2">
                        <span class="rounded-full bg-white/15 px-3 py-1 text-xs font-medium backdrop-blur-xs">
                            Kos Campur · Terawat
                        </span>
                        <span class="rounded-full bg-emerald-400/20 px-2.5 py-1 text-xs font-medium text-emerald-200">
                            Sekupang, Batam
                        </span>
                    </div>

                    <div class="mt-16 rounded-xl border border-white/15 bg-navy/65 p-4 backdrop-blur-xs sm:mt-0">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-base font-semibold text-white">Kos Sodentna</p>
                                <p class="mt-0.5 text-xs leading-relaxed text-slate-200">
                                    Jl. Kartini VI No. A4, Sungai Harapan, Sekupang, Kota Batam
                                </p>
                            </div>
                            <span class="shrink-0 rounded-md bg-brand px-2.5 py-1 text-xs font-semibold text-white">
                                {{ $free }} Kosong
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- 2. PILIHAN KAMAR & ATURAN KAPASITAS --}}
        <section class="space-y-4">
            <div class="flex flex-col justify-between gap-2 sm:flex-row sm:items-end">
                <div>
                    <h2 class="text-xl font-semibold tracking-tight text-navy">Pilihan Kamar</h2>
                    <p class="text-sm text-slate-500">Tarif transparan sesuai jumlah penghuni. Deposit Rp 500.000 dibayar sekali saat masuk.</p>
                </div>
                <a href="{{ route('site.rooms') }}" class="inline-flex items-center gap-1 text-sm font-medium text-brand-700 hover:text-brand">
                    <span>Lihat denah lantai</span>
                    <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                @foreach ($types as $slug => $t)
                    <a href="{{ route('site.room', $slug) }}" class="group flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-6 transition hover:border-brand hover:shadow-xs">
                        <div>
                            {{-- Header Nama & Kapasitas --}}
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <h3 class="text-lg font-semibold text-navy group-hover:text-brand-700">{{ $t['name'] }}</h3>
                                    <p class="mt-0.5 text-xs text-slate-500">Ukuran {{ $t['size'] }} · Maks. {{ $t['cap'] }} orang</p>
                                </div>

                                {{-- Badge Aturan Wajib Sesuai Kapasitas --}}
                                <div class="flex flex-wrap gap-1.5">
                                    @if (($t['cap'] ?? 1) >= 2)
                                        <span class="rounded-md border border-amber-200 bg-amber-50 px-2.5 py-1 text-[11px] font-medium text-amber-800">
                                            Bukan untuk pasutri
                                        </span>
                                    @else
                                        <span class="rounded-md bg-slate-100 px-2.5 py-1 text-[11px] font-medium text-slate-600">
                                            1 Penghuni
                                        </span>
                                    @endif
                                </div>
                            </div>

                            {{-- Harga Sewa --}}
                            <div class="mt-5 rounded-lg bg-slate-50 p-3.5">
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-2xl font-bold tracking-tight text-navy">{{ $t['price'] }}</span>
                                    <span class="text-sm text-slate-500">/ bulan</span>
                                </div>
                                @if (!empty($t['daily']))
                                    <p class="mt-1 text-xs text-slate-600">
                                        Tersedia sewa harian: <span class="font-semibold text-navy">{{ Fmt::rp($t['daily']) }} / hari</span> (maks. 1 orang)
                                    </p>
                                @endif
                            </div>

                            <p class="mt-4 text-sm leading-relaxed text-slate-600">{{ $t['note'] }}</p>
                        </div>

                        <div class="mt-5 flex items-center justify-between border-t border-slate-100 pt-4 text-xs">
                            <span class="text-slate-500">Deposit awal Rp 500.000 (dikembalikan saat keluar)</span>
                            <span class="inline-flex items-center gap-1 font-semibold text-brand-700 group-hover:translate-x-0.5 transition-transform">
                                Detail kamar
                                <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5 21 12m0 0-7.5 7.5M21 12H3" />
                                </svg>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>

        {{-- 3. FASILITAS & LOKASI --}}
        <section class="grid gap-5 lg:grid-cols-12">
            {{-- Fasilitas --}}
            <div class="rounded-xl border border-slate-200 bg-white p-6 lg:col-span-7">
                <h2 class="text-lg font-semibold text-navy">Fasilitas Kamar &amp; Bersama</h2>
                <p class="mt-1 text-sm text-slate-500">Kelengkapan standar untuk kenyamanan tinggal harian maupun bulanan.</p>

                <div class="mt-4 grid grid-cols-2 gap-2.5 sm:grid-cols-3">
                    @foreach (DemoSite::facilities() as $f)
                        <div class="flex items-center gap-2 rounded-lg border border-slate-200/80 bg-slate-50/70 px-3 py-2 text-sm text-slate-700">
                            <svg class="h-4 w-4 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <span class="truncate">{{ $f }}</span>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Lokasi --}}
            <div class="flex flex-col justify-between rounded-xl border border-slate-200 bg-white p-6 lg:col-span-5">
                <div>
                    <span class="text-xs font-semibold tracking-wider text-brand-700 uppercase">Lokasi Properti</span>
                    <h2 class="mt-1 text-lg font-semibold text-navy">Jl. Kartini VI No. A4</h2>
                    <p class="mt-1 text-sm leading-relaxed text-slate-600">
                        Sungai Harapan, Kec. Sekupang, Kota Batam, Kepulauan Riau. Lingkungan tenang dengan akses mudah ke area kerja dan pelabuhan Sekupang.
                    </p>
                </div>

                <div class="mt-5 flex flex-wrap items-center gap-2.5 border-t border-slate-100 pt-4">
                    <a href="https://maps.google.com/?q=Jalan+Kartini+VI+Sungai+Harapan+Sekupang+Batam" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3.5 py-2 text-xs font-semibold text-navy transition hover:bg-slate-50">
                        <svg class="h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        Buka di Google Maps
                    </a>
                </div>
            </div>
        </section>

        {{-- 4. ALUR PENGAJUAN SEWA (6 LANGKAH) --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="max-w-xl">
                <h2 class="text-xl font-semibold tracking-tight text-navy">Dari Cari Kamar Sampai Siap Tinggal</h2>
                <p class="mt-1 text-sm text-slate-500">Proses pengajuan sewa jelas tanpa biaya tersembunyi. Kamar baru dikunci setelah pengajuan dikonfirmasi pemilik.</p>
            </div>

            @php
                $steps = [
                    ['no' => '1', 'title' => 'Cek ketersediaan', 'desc' => 'Pilih kamar yang tersedia di denah lantai sesuai durasi sewa.'],
                    ['no' => '2', 'title' => 'Isi formulir & biodata', 'desc' => 'Lengkapi data diri, kontak darurat, dan setujui tata tertib kos.'],
                    ['no' => '3', 'title' => 'Pemilik tinjau', 'desc' => 'Pengelola memverifikasi pengajuan dan mengonfirmasi ketersediaan.'],
                    ['no' => '4', 'title' => 'Bayar & unggah bukti', 'desc' => 'Lakukan pembayaran sewa awal + deposit lalu unggah bukti transfer.'],
                    ['no' => '5', 'title' => 'Verifikasi & kuitansi', 'desc' => 'Pemilik memverifikasi pembayaran, akun aktif, dan kuitansi terbit.'],
                    ['no' => '6', 'title' => 'Pindah masuk', 'desc' => 'Penghuni siap menempati kamar sesuai tanggal mulai sewa.'],
                ];
            @endphp

            <div class="mt-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($steps as $step)
                    <div class="rounded-xl border border-slate-200/80 bg-slate-50/50 p-4">
                        <div class="flex items-center gap-2.5">
                            <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-brand text-xs font-bold text-white">
                                {{ $step['no'] }}
                            </span>
                            <h3 class="text-sm font-semibold text-navy">{{ $step['title'] }}</h3>
                        </div>
                        <p class="mt-2 text-xs leading-relaxed text-slate-600">{{ $step['desc'] }}</p>
                    </div>
                @endforeach
            </div>
        </section>

        {{-- 5. BANNER BANTUAN WHATSAPP --}}
        <section class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-base font-semibold text-navy">Ada pertanyaan atau ingin jadwal survei kamar?</h2>
                <p class="mt-0.5 text-sm text-slate-500">Hubungi pengelola langsung via WhatsApp untuk konfirmasi jadwal kunjungan.</p>
            </div>
            <a href="https://wa.me/6281364449270?text={{ urlencode('Halo pengelola Kos Sodentna, saya ingin bertanya mengenai ketersediaan kamar.') }}" target="_blank" rel="noopener noreferrer" class="inline-flex shrink-0 items-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H8.25m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0H12m4.125 0a.375.375 0 1 1-.75 0 .375.375 0 0 1 .75 0Zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 0 1-2.555-.337A5.972 5.972 0 0 1 5.41 20.97a5.969 5.969 0 0 1-.474-.065 4.48 4.48 0 0 0 .978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25Z" />
                </svg>
                <span>Hubungi via WhatsApp</span>
            </a>
        </section>

    </div>
</x-site.layout>