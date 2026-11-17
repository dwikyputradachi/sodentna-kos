@php
    $in = 'w-full rounded-lg border border-slate-300 bg-white px-3.5 py-2.5 text-base text-slate-800 placeholder:text-slate-400 transition sm:text-sm focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20';
    $lbl = 'mb-1.5 block text-sm font-medium text-slate-700';
    $fileIn = 'block w-full rounded-lg border border-slate-300 bg-white text-sm text-slate-600 file:mr-3.5 file:border-0 file:border-r file:border-slate-200 file:bg-brand-50 file:px-3.5 file:py-2.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 focus:border-brand focus:outline-none';
@endphp

<x-site.layout title="Pengajuan Sewa" active="apply">
    <div class="mx-auto max-w-3xl space-y-6 pb-6">

        {{-- Header Halaman --}}
        <div class="space-y-1.5">
            <h1 class="text-2xl font-semibold tracking-tight text-navy sm:text-3xl">Pengajuan Sewa</h1>
            <p class="text-sm leading-relaxed text-slate-500">
                Pilih paket sewa, tentukan kamar, lalu lengkapi biodata. Kamar belum dikunci sampai pengajuan disetujui pengelola.
            </p>
        </div>

        @if (session('done'))
            @php $d = session('done'); @endphp
            <div
                class="space-y-4 rounded-2xl border border-emerald-200 bg-emerald-50/80 p-6 text-sm text-emerald-900"
                x-data
                x-init="try { const k = 'sodentna_apps'; const l = JSON.parse(localStorage.getItem(k) || '[]').filter(i => i.code !== @js($d['code'])); l.unshift({ code: @js($d['code']), last4: @js($d['last4']), plan: @js($d['plan']), at: new Date().toISOString() }); localStorage.setItem(k, JSON.stringify(l.slice(0, 10))) } catch (e) {}"
            >
                <div class="flex items-start gap-3">
                    <span class="mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-emerald-600 text-white">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2.2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                    </span>
                    <div class="space-y-1">
                        <h2 class="text-base font-semibold text-emerald-950">
                            {{ $d['existing'] ? 'Pengajuan Anda sudah tercatat sebelumnya.' : 'Pengajuan sewa berhasil terkirim.' }}
                        </h2>
                        <p class="text-xs leading-relaxed text-emerald-800">
                            Simpan kode di bawah ini untuk memantau konfirmasi pengelola. Kode juga otomatis tersimpan pada perangkat ini di halaman Status Pengajuan.
                        </p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-emerald-200 bg-white px-4 py-3">
                    <div>
                        <span class="block text-[11px] font-medium tracking-wider text-slate-400 uppercase">Kode Pengajuan</span>
                        <span class="font-mono text-2xl font-bold tracking-wider text-navy">{{ $d['code'] }}</span>
                    </div>
                    <x-copy :text="$d['code']" />
                </div>

                <div class="flex flex-wrap gap-2.5 pt-1">
                    <a
                        href="https://wa.me/{{ $d['phone'] }}?text={{ rawurlencode('Kode pengajuan sewa Kos Sodentna saya: ' . $d['code']) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 rounded-lg bg-brand px-4 py-2.5 text-xs font-semibold text-white transition hover:bg-brand-700"
                    >
                        <span>Simpan ke WhatsApp saya</span>
                    </a>
                    <a
                        href="{{ route('site.status') }}"
                        class="inline-flex items-center gap-1.5 rounded-lg border border-emerald-300 bg-white px-4 py-2.5 text-xs font-semibold text-emerald-900 transition hover:bg-emerald-100/60"
                    >
                        <span>Cek Status Pengajuan</span>
                    </a>
                </div>
            </div>

        @elseif (! $any)
            <div class="flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-900">
                <svg class="mt-0.5 h-5 w-5 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                </svg>
                <div>
                    <p class="font-semibold">Pengajuan sewa belum dibuka</p>
                    <p class="mt-0.5 text-xs text-amber-800">Silakan hubungi pengelola via WhatsApp untuk informasi ketersediaan kamar terbaru.</p>
                </div>
            </div>

        @else
            @if ($errors->any())
                <div class="rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-700">
                    <p class="mb-1 font-semibold">Periksa kembali isian formulir:</p>
                    <ul class="list-disc space-y-0.5 pl-5 text-xs">
                        @foreach ($errors->all() as $e)
                            <li>{{ $e }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form
                method="POST"
                action="{{ route('site.apply.store') }}"
                enctype="multipart/form-data"
                class="space-y-6"
                x-data="{
                    plan: '{{ old('plan', $sel) }}',
                    plans: @js($plans),
                    rooms: @js($rooms),
                    room: '{{ old('room_id', $selRoom) }}',
                    job: '{{ old('job_choice', 'Mahasiswa') }}',
                    start: '{{ old('start_date') }}',
                    end: '{{ old('end_date') }}',
                    rp(n) { return 'Rp ' + Number(n || 0).toLocaleString('id-ID') },
                    get daily() { return this.plan === 'daily' },
                    get nights() { if (!this.start || !this.end) return 0; return Math.max(0, Math.round((new Date(this.end) - new Date(this.start)) / 86400000)) },
                    get r() { return this.rooms[this.room] },
                    eligible(r) { return this.plan === 'two' ? (r.cap >= 2 && !!r.rate_two) : (this.daily ? (r.allows_daily && !!r.daily_rate) : true) },
                    roomOk(id) { const r = this.rooms[id]; return r.available && this.eligible(r) },
                    roomLabel(id) { const r = this.rooms[id]; if (!r.available) return r.statusLabel; if (this.eligible(r)) return 'Tersedia'; return this.plan === 'two' ? 'Untuk 1 orang' : 'Tanpa sewa harian' },
                    pickRoom(id) { if (this.roomOk(id)) this.room = String(id) },
                    rate() { const r = this.r; if (!r) return this.plans[this.plan].price; return this.plan === 'two' ? r.rate_two : (this.daily ? r.daily_rate : r.rate_one) },
                    get total() { if (this.daily) return this.nights * this.rate(); const r = this.r; return r ? this.rate() + r.deposit : this.plans[this.plan].total },
                    pick(k) { if (this.plans[k].available) { this.plan = k; if (this.room && !this.roomOk(this.room)) this.room = '' } }
                }"
            >
                @csrf
                <input type="hidden" name="plan" :value="plan">

                {{-- 1. PILIH PAKET SEWA --}}
                <section class="space-y-3">
                    <div class="flex items-center justify-between">
                        <h2 class="text-sm font-semibold text-navy">1. Pilih Paket Sewa</h2>
                        <span class="text-xs text-slate-400 sm:hidden">Geser untuk melihat paket</span>
                    </div>

                    <div class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-1 sm:mx-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:px-0">
                        @foreach ($plans as $k => $pl)
                            <button
                                type="button"
                                @click="pick('{{ $k }}')"
                                @disabled(! $pl['available'])
                                :class="plan === '{{ $k }}' ? 'border-brand bg-brand-50/70 ring-1 ring-brand' : 'border-slate-200 bg-white hover:border-slate-300'"
                                class="flex min-w-[15.5rem] shrink-0 snap-center flex-col justify-between space-y-3 rounded-xl border p-4 text-left transition disabled:cursor-not-allowed disabled:opacity-50 sm:min-w-0"
                            >
                                <div class="flex items-start justify-between gap-2">
                                    <span class="flex items-center gap-2 text-sm font-semibold text-navy">
                                        <span class="flex h-4 w-4 shrink-0 items-center justify-center rounded-full border bg-white" :class="plan === '{{ $k }}' ? 'border-brand' : 'border-slate-300'">
                                            <span x-show="plan === '{{ $k }}'" class="h-2 w-2 rounded-full bg-brand"></span>
                                        </span>
                                        {{ $pl['label'] }}
                                    </span>

                                    @if ($pl['available'])
                                        <span
                                            class="shrink-0 rounded-md px-2 py-0.5 text-[11px] font-semibold text-white transition"
                                            :class="plan === '{{ $k }}' ? 'bg-brand' : 'bg-navy'"
                                        >
                                            Rp {{ number_format($pl['price'], 0, ',', '.') }}/{{ $pl['mode'] === 'daily' ? 'mlm' : 'bln' }}
                                        </span>
                                    @else
                                        <span class="rounded-md bg-slate-100 px-2 py-0.5 text-[11px] font-medium text-slate-500">Belum tersedia</span>
                                    @endif
                                </div>

                                @if ($pl['available'] && $pl['mode'] === 'monthly')
                                    <dl class="space-y-1.5 border-t border-slate-200/80 pt-2.5 text-xs text-slate-600">
                                        <div class="flex justify-between">
                                            <dt>Sewa bulan pertama</dt>
                                            <dd class="font-medium text-slate-700">Rp {{ number_format($pl['price'], 0, ',', '.') }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt>Deposit (dana titipan)</dt>
                                            <dd class="font-medium text-slate-700">Rp {{ number_format($pl['deposit'], 0, ',', '.') }}</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-slate-200/80 pt-1.5 font-semibold text-navy">
                                            <dt>Total awal</dt>
                                            <dd>Rp {{ number_format($pl['total'], 0, ',', '.') }}</dd>
                                        </div>
                                    </dl>
                                @elseif ($pl['available'])
                                    <dl class="space-y-1.5 border-t border-slate-200/80 pt-2.5 text-xs text-slate-600">
                                        <div class="flex justify-between">
                                            <dt>Tarif per malam</dt>
                                            <dd class="font-medium text-slate-700">Rp {{ number_format($pl['price'], 0, ',', '.') }}</dd>
                                        </div>
                                        <div class="flex justify-between">
                                            <dt>Kapasitas</dt>
                                            <dd class="font-medium text-slate-700">Maks. 1 orang</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-slate-200/80 pt-1.5 font-semibold text-navy">
                                            <dt>Total</dt>
                                            <dd>Tarif × malam</dd>
                                        </div>
                                    </dl>
                                @endif
                            </button>
                        @endforeach
                    </div>

                    {{-- Peringatan Aturan Kapasitas Sesuai Paket --}}
                    <div x-show="plan === 'two'" x-cloak class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-xs leading-relaxed text-amber-900">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span><strong>Ketentuan Kamar 2 Penghuni:</strong> Kamar untuk 2 orang tidak diperuntukkan bagi pasangan suami istri (pasutri).</span>
                    </div>
                    <div x-show="daily" x-cloak class="flex items-start gap-2.5 rounded-xl border border-amber-200 bg-amber-50/90 px-4 py-3 text-xs leading-relaxed text-amber-900">
                        <svg class="mt-0.5 h-4 w-4 shrink-0 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                        <span><strong>Ketentuan Sewa Harian:</strong> Maksimal 1 orang per kamar. Ketentuan pembayaran sewa harian akan dikonfirmasi oleh pengelola.</span>
                    </div>
                </section>

                {{-- 2. PILIH KAMAR --}}
                <x-owner.card title="2. Pilih Kamar" note="Pilih kamar yang tersedia sesuai paket sewa. Status kamar berubah dipesan setelah disetujui pengelola.">
                    <div class="space-y-4 p-4 sm:p-5">
                        <input type="hidden" name="room_id" :value="room">

                        {{-- Legenda Status Denah --}}
                        <div class="flex flex-wrap items-center gap-4 border-b border-slate-100 pb-3 text-xs text-slate-500">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3 w-3 rounded border border-emerald-300 bg-emerald-50"></span>
                                Tersedia
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3 w-3 rounded border border-brand bg-brand-50 ring-1 ring-brand"></span>
                                Dipilih
                            </span>
                            <span class="inline-flex items-center gap-1.5">
                                <span class="h-3 w-3 rounded border border-slate-200 bg-slate-100"></span>
                                Terisi / tidak sesuai paket
                            </span>
                        </div>

                        @forelse ($floors as $f)
                            <div class="space-y-2">
                                <div class="text-xs font-semibold tracking-wide text-slate-500 uppercase">{{ $f['name'] }}</div>
                                <div class="grid grid-cols-3 gap-2.5 sm:grid-cols-5">
                                    @foreach ($f['rooms'] as $rm)
                                        <button
                                            type="button"
                                            @click="pickRoom({{ $rm['id'] }})"
                                            :disabled="!roomOk({{ $rm['id'] }})"
                                            :class="room == '{{ $rm['id'] }}'
                                                ? 'border-brand bg-brand-50 text-navy ring-2 ring-brand'
                                                : (roomOk({{ $rm['id'] }})
                                                    ? 'border-emerald-200 bg-emerald-50/40 text-navy hover:border-brand hover:bg-brand-50/30'
                                                    : 'border-slate-200 bg-slate-50 text-slate-400')"
                                            class="rounded-xl border px-2.5 py-2.5 text-center transition disabled:cursor-not-allowed"
                                        >
                                            <div class="text-sm font-bold">{{ $rm['code'] }}</div>
                                            <div class="mt-0.5 truncate text-[11px]" x-text="roomLabel({{ $rm['id'] }})"></div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada data kamar.</p>
                        @endforelse

                        {{-- Ringkasan Kamar Terpilih --}}
                        <div x-show="r" x-cloak class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-brand/25 bg-brand-50/60 px-3.5 py-2.5 text-xs text-navy">
                            <span class="font-semibold" x-text="r ? 'Kamar ' + r.code + ' · ' + r.type : ''"></span>
                            <span class="font-semibold text-brand-700" x-text="r ? rp(rate()) + (daily ? ' / malam' : ' / bulan') + (!daily && r.deposit ? ' (+ deposit ' + rp(r.deposit) + ')' : '') : ''"></span>
                        </div>

                        <p class="text-xs text-slate-500">
                            Kamar yang Anda inginkan sedang penuh?
                            <a href="{{ route('site.waitlist') }}" class="font-semibold text-brand-700 underline decoration-brand/40 underline-offset-2 hover:text-brand">Gabung daftar tunggu</a>.
                        </p>
                    </div>
                </x-owner.card>

                {{-- 3. DATA PENGHUNI --}}
                <x-owner.card title="3. Data Penghuni">
                    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                        <label class="block">
                            <span class="{{ $lbl }}" x-text="plan === 'two' ? 'Nama lengkap penghuni 1' : 'Nama lengkap'"></span>
                            <input name="name" value="{{ old('name') }}" placeholder="Sesuai KTP / identitas" autocomplete="name" required class="{{ $in }}">
                        </label>

                        <template x-if="plan === 'two'">
                            <label class="block">
                                <span class="{{ $lbl }}">Nama lengkap penghuni 2</span>
                                <input name="name_2" value="{{ old('name_2') }}" placeholder="Sesuai KTP / identitas" required class="{{ $in }}">
                            </label>
                        </template>

                        <template x-if="!daily">
                            <div class="contents">
                                <label class="block">
                                    <span class="{{ $lbl }}">Tempat lahir</span>
                                    <input name="birth_place" value="{{ old('birth_place') }}" placeholder="Kota kelahiran" required class="{{ $in }}">
                                </label>
                                <label class="block">
                                    <span class="{{ $lbl }}">Tanggal lahir</span>
                                    <input type="date" name="birth_date" value="{{ old('birth_date') }}" required class="{{ $in }}">
                                </label>
                            </div>
                        </template>

                        <label class="block">
                            <span class="{{ $lbl }}">No. KTP / identitas</span>
                            <input name="id_number" value="{{ old('id_number') }}" placeholder="16 digit NIK / nomor identitas" inputmode="text" required class="{{ $in }}">
                        </label>

                        <label class="block">
                            <span class="{{ $lbl }}">No. telepon / WhatsApp</span>
                            <input name="phone" value="{{ old('phone') }}" type="tel" inputmode="tel" placeholder="0812xxxxxxxx" required class="{{ $in }}">
                        </label>

                        <label class="block sm:col-span-2">
                            <span class="{{ $lbl }}">Alamat asal</span>
                            <textarea name="address" rows="2" placeholder="Alamat lengkap sesuai identitas" required class="{{ $in }}">{{ old('address') }}</textarea>
                        </label>

                        <template x-if="!daily">
                            <div class="contents">
                                <label class="block">
                                    <span class="{{ $lbl }}">Agama <span class="font-normal text-slate-400">(opsional)</span></span>
                                    <input name="religion" value="{{ old('religion') }}" class="{{ $in }}">
                                </label>

                                <div class="sm:col-span-2">
                                    <span class="{{ $lbl }}">Pekerjaan / status</span>
                                    <div class="grid grid-cols-3 gap-2">
                                        @foreach (['Mahasiswa', 'Karyawan', 'Lainnya'] as $j)
                                            <label
                                                class="flex cursor-pointer items-center justify-center gap-2 rounded-lg border px-3 py-2 text-sm font-medium transition"
                                                :class="job === '{{ $j }}' ? 'border-brand bg-brand-50 text-brand-700' : 'border-slate-200 bg-white text-slate-600 hover:bg-slate-50'"
                                            >
                                                <input type="radio" name="job_choice" value="{{ $j }}" x-model="job" class="accent-brand">
                                                <span>{{ $j }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <input x-show="job === 'Lainnya'" x-cloak name="job_other" value="{{ old('job_other') }}" placeholder="Sebutkan pekerjaan atau aktivitas" :required="job === 'Lainnya'" class="{{ $in }} mt-2.5">
                                </div>

                                <label class="block sm:col-span-2">
                                    <span class="{{ $lbl }}">Nama instansi / kampus / perusahaan</span>
                                    <input name="institution" value="{{ old('institution') }}" placeholder="Contoh: Politeknik Negeri Batam / PT ..." required class="{{ $in }}">
                                </label>
                            </div>
                        </template>

                        <label class="block sm:col-span-2">
                            <span class="{{ $lbl }}" x-text="plan === 'two' ? 'Foto identitas penghuni 1 (KTP/SIM/Paspor)' : 'Foto identitas (KTP/SIM/Paspor)'"></span>
                            <input type="file" name="id_doc" accept="image/jpeg,image/png,application/pdf" required class="{{ $fileIn }}">
                            <span class="mt-1 block text-xs text-slate-400">Format JPG, PNG, atau PDF maksimal 5 MB. Disimpan privat dan hanya dapat diakses pengelola.</span>
                        </label>

                        <template x-if="plan === 'two'">
                            <label class="block sm:col-span-2">
                                <span class="{{ $lbl }}">Foto identitas penghuni 2 <span class="font-normal text-slate-400">(opsional)</span></span>
                                <input type="file" name="id_doc_2" accept="image/jpeg,image/png,application/pdf" class="{{ $fileIn }}">
                            </label>
                        </template>
                    </div>
                </x-owner.card>

                {{-- 4. KONTAK DARURAT --}}
                <x-owner.card title="4. Kontak Darurat" note="Keluarga atau kerabat terdekat yang dapat dihubungi dalam keadaan mendesak.">
                    <div class="grid gap-4 p-4 sm:grid-cols-2 sm:p-5">
                        <label class="block">
                            <span class="{{ $lbl }}">Nama lengkap</span>
                            <input name="emergency_name" value="{{ old('emergency_name') }}" required class="{{ $in }}">
                        </label>
                        <label class="block">
                            <span class="{{ $lbl }}">Hubungan</span>
                            <input name="emergency_relation" value="{{ old('emergency_relation') }}" placeholder="Contoh: Orang tua / Saudara" required class="{{ $in }}">
                        </label>
                        <label class="block">
                            <span class="{{ $lbl }}">No. telepon darurat</span>
                            <input name="emergency_phone" value="{{ old('emergency_phone') }}" type="tel" inputmode="tel" placeholder="0812xxxxxxxx" required class="{{ $in }}">
                        </label>
                        <label class="block">
                            <span class="{{ $lbl }}">Alamat kontak darurat <span x-show="daily" class="font-normal text-slate-400">(opsional)</span></span>
                            <input name="emergency_address" value="{{ old('emergency_address') }}" :required="!daily" class="{{ $in }}">
                        </label>
                    </div>
                </x-owner.card>

                {{-- 5. JADWAL SEWA & PEMBAYARAN --}}
                <x-owner.card title="5. Jadwal Masuk & Metode Pembayaran">
                    <div class="space-y-3 p-4 sm:p-5">
                        <div class="grid gap-4 sm:grid-cols-2">
                            <label class="block">
                                <span class="{{ $lbl }}">Tanggal masuk</span>
                                <input type="date" name="start_date" x-model="start" required class="{{ $in }}">
                            </label>

                            <template x-if="daily">
                                <label class="block">
                                    <span class="{{ $lbl }}">Tanggal keluar</span>
                                    <input type="date" name="end_date" x-model="end" :min="start" required class="{{ $in }}">
                                </label>
                            </template>

                            <label class="block">
                                <span class="{{ $lbl }}">Metode pembayaran</span>
                                <select name="payment_method" class="{{ $in }}">
                                    <option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer Bank</option>
                                    <option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option>
                                </select>
                            </label>
                        </div>

                        <p x-show="daily" x-cloak class="rounded-lg bg-slate-50 px-3.5 py-2.5 text-xs font-medium text-slate-600" x-text="nights > 0 ? 'Durasi menginap: ' + nights + ' malam × ' + rp(rate()) + ' = ' + rp(total) : 'Pilih tanggal masuk dan keluar untuk menghitung perkiraan total.'"></p>
                    </div>
                </x-owner.card>

                {{-- 6. PERNYATAAN & PERSETUJUAN --}}
                <div class="space-y-3 rounded-xl border border-slate-200 bg-white p-4 text-sm text-slate-700 sm:p-5">
                    <label class="flex cursor-pointer items-start gap-2.5">
                        <input type="checkbox" name="consent_rules" value="1" required class="mt-1 h-4 w-4 rounded border-slate-300 accent-brand">
                        <span class="leading-relaxed">
                            Saya telah membaca dan menyetujui <a href="{{ route('site.about') }}" target="_blank" class="font-medium text-brand-700 underline decoration-brand/40 underline-offset-2 hover:text-brand">peraturan kos</a>, termasuk ketentuan deposit dana titipan.
                        </span>
                    </label>

                    <template x-if="plan === 'two'">
                        <label class="flex cursor-pointer items-start gap-2.5">
                            <input type="checkbox" name="couple_ack" value="1" required class="mt-1 h-4 w-4 rounded border-slate-300 accent-brand">
                            <span class="leading-relaxed">Kami memahami bahwa kamar untuk 2 orang tidak diperuntukkan bagi pasangan suami istri.</span>
                        </label>
                    </template>

                    <label class="flex cursor-pointer items-start gap-2.5">
                        <input type="checkbox" name="consent_data" value="1" required class="mt-1 h-4 w-4 rounded border-slate-300 accent-brand">
                        <span class="leading-relaxed">
                            Data yang saya isi benar dan dapat dipertanggungjawabkan, serta saya menyetujui pemrosesan data pribadi untuk keperluan administrasi sewa, pengingat tagihan, dan kuitansi melalui WhatsApp.
                        </span>
                    </label>
                </div>

                {{-- STICKY SUBMIT BAR --}}
                <div class="sticky bottom-0 z-30 -mx-4 flex items-center justify-between gap-4 border-t border-slate-200 bg-white/95 px-4 py-3.5 shadow-sm backdrop-blur-xs sm:mx-0 sm:rounded-xl sm:border sm:px-5">
                    <div class="min-w-0">
                        <div class="truncate text-xs text-slate-500" x-text="!room ? 'Pilih kamar terlebih dahulu' : (daily ? 'Perkiraan total (' + nights + ' malam)' : 'Total awal (sewa + deposit)')"></div>
                        <div class="text-base font-bold text-navy sm:text-lg" x-text="total ? rp(total) : '—'"></div>
                    </div>

                    <button
                        type="submit"
                        :disabled="!room"
                        class="shrink-0 rounded-lg bg-brand px-6 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand/40 disabled:cursor-not-allowed disabled:opacity-40"
                    >
                        Kirim Pengajuan
                    </button>
                </div>
            </form>
        @endif
    </div>
</x-site.layout>