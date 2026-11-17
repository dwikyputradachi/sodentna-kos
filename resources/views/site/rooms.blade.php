<x-site.layout title="Ketersediaan Kamar" active="rooms">
    <div class="flex flex-col justify-between gap-3 sm:flex-row sm:items-end">
        <div>
            <h1 class="text-2xl font-semibold tracking-tight text-navy">Ketersediaan Kamar</h1>
            <p class="mt-1 text-sm text-slate-500">Status diperbarui pengelola. Kamar baru dikunci setelah pengajuan disetujui.</p>
        </div>

        {{-- Legenda Status --}}
        <div class="flex flex-wrap items-center gap-3 text-xs text-slate-600">
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-emerald-500"></span>Tersedia</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-amber-400"></span>Dipesan</span>
            <span class="inline-flex items-center gap-1.5"><span class="h-2.5 w-2.5 rounded-full bg-slate-300"></span>Terisi / Perbaikan</span>
        </div>
    </div>

    {{-- Ringkasan Tipe Kamar --}}
    <div class="grid gap-3 md:grid-cols-2">
        @foreach ($types as $slug => $t)
            <a href="{{ route('site.room', $slug) }}" class="group flex items-center justify-between gap-4 rounded-xl border border-slate-200 bg-white p-4 transition hover:border-brand">
                <div class="space-y-1">
                    <div class="flex flex-wrap items-center gap-2">
                        <span class="font-semibold text-navy group-hover:text-brand-700">{{ $t['name'] }}</span>
                        <span class="rounded bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-600">Maks. {{ $t['cap'] }} orang</span>
                        @if (($t['cap'] ?? 1) >= 2)
                            <span class="rounded bg-amber-50 px-2 py-0.5 text-[11px] font-medium text-amber-800">Bukan pasutri</span>
                        @endif
                    </div>
                    <div class="text-sm text-slate-600">
                        <span class="font-semibold text-navy">{{ $t['price'] }}</span>/bulan · <span class="text-xs text-slate-500">Deposit Rp 500.000 (sekali)</span>
                    </div>
                </div>
                <span class="shrink-0 text-sm font-medium text-brand-700 group-hover:translate-x-0.5 transition-transform">Detail →</span>
            </a>
        @endforeach
    </div>

    {{-- Denah Matriks Kamar per Lantai --}}
    @foreach ($floors as $floor => $rooms)
        <x-owner.card :title="$floor">
            <div class="grid grid-cols-2 gap-3 p-4 sm:grid-cols-4 md:grid-cols-6">
                @foreach ($rooms as $r)
                    @if ($r['st'] === 'Tersedia')
                        <a href="{{ route('site.apply', ['kamar' => $r['id']]) }}" class="group block rounded-xl border border-emerald-200 bg-emerald-50/40 p-3 text-center transition hover:border-brand hover:bg-brand-50/50">
                            <div class="text-base font-bold text-navy">{{ $r['code'] }}</div>
                            <div class="mt-1.5"><x-owner.badge :label="$r['st']" /></div>
                            <div class="mt-1.5 text-[11px] font-semibold text-brand-700 group-hover:underline">Ajukan →</div>
                        </a>
                    @else
                        <div class="rounded-xl border p-3 text-center {{ $r['st'] === 'Dipesan' ? 'border-amber-200 bg-amber-50/40' : 'border-slate-200 bg-slate-50/80 text-slate-400' }}">
                            <div class="text-base font-semibold {{ $r['st'] === 'Dipesan' ? 'text-navy' : 'text-slate-400' }}">{{ $r['code'] }}</div>
                            <div class="mt-1.5"><x-owner.badge :label="$r['st']" /></div>
                            <div class="mt-1.5 text-[11px] text-slate-400">—</div>
                        </div>
                    @endif
                @endforeach
            </div>
        </x-owner.card>
    @endforeach

    {{-- Banner Daftar Tunggu --}}
    <div class="flex flex-col items-start justify-between gap-3 rounded-xl border border-brand/25 bg-brand-50/70 p-4 text-sm sm:flex-row sm:items-center">
        <span class="text-slate-700">Kamar yang Anda inginkan penuh? Gabung daftar tunggu, pengelola akan menghubungi saat ada kamar kosong.</span>
        <a href="{{ route('site.waitlist') }}" class="shrink-0 rounded-lg bg-brand px-4 py-2 text-xs font-semibold text-white transition hover:bg-brand-700">Gabung Daftar Tunggu</a>
    </div>
</x-site.layout>