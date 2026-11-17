@php
    use App\Support\Owner\Billing;
    use App\Support\Site\DemoSite;
    use App\Support\Tenant\DemoTenant;

    $waLink = 'https://wa.me/' . Billing::phone(DemoSite::WA) . '?text=' . rawurlencode('Halo pengelola Kos Sodentna, saya ingin bertanya tentang fasilitas dan ketersediaan kamar.');
@endphp

<x-site.layout title="Tentang Kos" active="about">
    <div class="space-y-8">

        {{-- 1. HEADER & CERITA PROPERTI --}}
        <section class="rounded-2xl border border-slate-200 bg-white p-6 sm:p-8">
            <div class="max-w-2xl space-y-3">
                <div class="inline-flex items-center gap-2 rounded-full bg-brand-50 px-3 py-1 text-xs font-medium text-brand-700">
                    <span>Sekupang, Batam · Kos Campur</span>
                </div>
                <h1 class="text-2xl font-semibold tracking-tight text-navy sm:text-3xl">Tentang Kos Sodentna</h1>
                <p class="text-sm leading-relaxed text-slate-600 sm:text-base">
                    Hunian kos campur di kawasan Sungai Harapan, Sekupang, yang dikelola langsung oleh pemilik. Dirancang untuk pekerja maupun mahasiswa yang mengutamakan kebersihan, ketenangan, dan kejelasan administrasi sewa.
                </p>
            </div>

            {{-- Slot Galeri Properti (Siap diisi aset foto asli) --}}
            <div class="mt-6 grid gap-3 sm:grid-cols-3">
                <div class="flex aspect-[16/10] flex-col justify-end rounded-xl bg-gradient-to-br from-navy via-[#1a3863] to-brand p-4 text-white">
                    <span class="text-xs font-medium text-white/75">Tampak Bangunan</span>
                    <span class="text-sm font-semibold">Lingkungan Tenang &amp; Terawat</span>
                </div>
                <div class="flex aspect-[16/10] flex-col justify-end rounded-xl border border-slate-200 bg-slate-100 p-4 text-navy">
                    <span class="text-xs font-medium text-slate-500">Interior Kamar</span>
                    <span class="text-sm font-semibold">AC &amp; Kamar Mandi Dalam</span>
                </div>
                <div class="flex aspect-[16/10] flex-col justify-end rounded-xl border border-slate-200 bg-slate-100 p-4 text-navy">
                    <span class="text-xs font-medium text-slate-500">Area Bersama</span>
                    <span class="text-sm font-semibold">Parkir Kendaraan &amp; CCTV 24 Jam</span>
                </div>
            </div>
        </section>

        {{-- 2. ALAMAT & FASILITAS --}}
        <section class="grid gap-5 md:grid-cols-12">
            {{-- Kartu Alamat --}}
            <div class="md:col-span-5">
                <x-owner.card title="Alamat & Lokasi">
                    <div class="flex h-full flex-col justify-between space-y-5 p-5">
                        <div class="space-y-2">
                            <p class="text-sm font-medium leading-relaxed text-navy">{{ DemoSite::ADDRESS }}</p>
                            <p class="text-xs leading-relaxed text-slate-500">
                                Akses mudah menuju kawasan perkantoran Sekupang, Pelabuhan Feri Sekupang, dan fasilitas umum sekitar Sungai Harapan.
                            </p>
                        </div>

                        <div class="flex flex-wrap gap-2 border-t border-slate-100 pt-4">
                            <a
                                href="https://maps.google.com/?q={{ urlencode(DemoSite::ADDRESS) }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-lg border border-slate-300 bg-white px-3 py-2 text-xs font-semibold text-navy transition hover:bg-slate-50"
                            >
                                <svg class="h-4 w-4 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                                </svg>
                                <span>Google Maps</span>
                            </a>
                            <a
                                href="{{ $waLink }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 rounded-lg bg-brand-50 px-3 py-2 text-xs font-semibold text-brand-700 transition hover:bg-brand-100"
                            >
                                <span>Tanya via WhatsApp</span>
                            </a>
                        </div>
                    </div>
                </x-owner.card>
            </div>

            {{-- Kartu Fasilitas --}}
            <div class="md:col-span-7">
                <x-owner.card title="Fasilitas Kos">
                    <div class="p-5">
                        <ul class="grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                            @foreach (DemoSite::facilities() as $f)
                                <li class="flex items-center gap-2.5 rounded-lg border border-slate-200/80 bg-slate-50/70 px-3.5 py-2.5 text-sm text-slate-700">
                                    <svg class="h-4 w-4 shrink-0 text-brand" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                                    </svg>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </x-owner.card>
            </div>
        </section>

        {{-- 3. PERATURAN & KETENTUAN HUNIAN --}}
        <section>
            <x-owner.card title="Peraturan & Ketentuan Singkat">
                <div class="space-y-4 p-5">
                    {{-- Highlight Kebijakan Utama --}}
                    <div class="grid gap-3 rounded-xl border border-amber-200/80 bg-amber-50/70 p-4 text-xs text-amber-900 sm:grid-cols-3">
                        <div>
                            <span class="font-semibold">Kapasitas &amp; Status</span>
                            <p class="mt-0.5 text-amber-800">Kamar 2 penghuni tidak diperuntukkan bagi pasangan suami istri (pasutri).</p>
                        </div>
                        <div>
                            <span class="font-semibold">Sewa Harian</span>
                            <p class="mt-0.5 text-amber-800">Maksimal 1 penghuni per kamar untuk mode sewa harian.</p>
                        </div>
                        <div>
                            <span class="font-semibold">Dana Deposit</span>
                            <p class="mt-0.5 text-amber-800">Dibayar sekali di awal dan dikembalikan saat keluar setelah cek kamar.</p>
                        </div>
                    </div>

                    {{-- Daftar Peraturan dari DemoTenant --}}
                    <ol class="space-y-2.5 text-sm text-slate-700">
                        @foreach (DemoTenant::rules() as $index => $r)
                            <li class="flex items-start gap-3">
                                <span class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-navy">
                                    {{ $index + 1 }}
                                </span>
                                <span class="leading-relaxed">{{ $r }}</span>
                            </li>
                        @endforeach
                    </ol>
                </div>
            </x-owner.card>
        </section>

        {{-- 4. AJAKAN TINDAKAN (CTA) --}}
        <section class="flex flex-col items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 sm:flex-row sm:items-center">
            <div>
                <h2 class="text-base font-semibold text-navy">Tertarik tinggal di Kos Sodentna?</h2>
                <p class="mt-0.5 text-sm text-slate-500">Periksa kamar yang sedang tersedia pada denah lantai atau langsung kirim pengajuan sewa.</p>
            </div>
            <div class="flex flex-wrap gap-2.5">
                <a href="{{ route('site.rooms') }}" class="rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-semibold text-navy transition hover:bg-slate-50">
                    Cek Ketersediaan
                </a>
                <a href="{{ route('site.apply') }}" class="rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700">
                    Ajukan Sewa
                </a>
            </div>
        </section>

    </div>
</x-site.layout>