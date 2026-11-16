@php
$in = 'w-full rounded-md border border-slate-300 px-3 py-2.5 text-base sm:text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand';
$lbl = 'mb-1 block text-sm font-medium';
@endphp
<x-site.layout title="Pengajuan Sewa" active="apply">
    <div class="mx-auto max-w-3xl space-y-5 pb-4">
        <div><h1 class="text-2xl font-semibold text-navy">Pengajuan Sewa</h1><p class="mt-1 text-sm text-slate-500">Pilih jenis sewa, lalu isi data. Kamar belum dipesan sampai pengajuan disetujui pengelola.</p></div>

        @if (session('done'))
            @php $d = session('done'); @endphp
            <div class="space-y-3 rounded-lg border border-emerald-200 bg-emerald-50 p-5 text-sm text-emerald-800"
                 x-data x-init="try { const k = 'sodentna_apps'; const l = JSON.parse(localStorage.getItem(k) || '[]').filter(i => i.code !== @js($d['code'])); l.unshift({ code: @js($d['code']), last4: @js($d['last4']), plan: @js($d['plan']), at: new Date().toISOString() }); localStorage.setItem(k, JSON.stringify(l.slice(0, 10))) } catch (e) {}">
                <div class="font-semibold">{{ $d['existing'] ? 'Pengajuan Anda sudah tercatat sebelumnya.' : 'Pengajuan terkirim.' }}</div>
                <div class="flex flex-wrap items-center gap-2">
                    <span class="text-2xl font-semibold tracking-wide">{{ $d['code'] }}</span><x-copy :text="$d['code']" />
                </div>
                <p>Simpan kode ini. Kode juga tersimpan di perangkat ini pada halaman Status Pengajuan.</p>
                <div class="flex flex-wrap gap-2">
                    <a href="https://wa.me/{{ $d['phone'] }}?text={{ rawurlencode('Kode pengajuan sewa Kos Sodentna saya: ' . $d['code']) }}" target="_blank" rel="noopener" class="rounded-md bg-brand px-3 py-2 font-medium text-white">Simpan ke WhatsApp saya</a>
                    <a href="{{ route('site.status') }}" class="rounded-md border border-emerald-300 px-3 py-2 font-medium">Cek status</a>
                </div>
            </div>
        @elseif (! $any)
            <div class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">Pengajuan belum dibuka. Silakan hubungi pengelola.</div>
        @else
            @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

            <form method="POST" action="{{ route('site.apply.store') }}" enctype="multipart/form-data" class="space-y-5"
                  x-data="{
                    plan: '{{ old('plan', $sel) }}', plans: @js($plans), rooms: @js($rooms), room: '{{ old('room_id', $selRoom) }}', job: '{{ old('job_choice', 'Mahasiswa') }}', start: '{{ old('start_date') }}', end: '{{ old('end_date') }}',
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
                  }">
                @csrf
                <input type="hidden" name="plan" :value="plan">

                {{-- Plan cards: horizontal swipe on phones, 3 columns on larger screens --}}
                <div class="-mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto px-4 pb-2 sm:mx-0 sm:grid sm:grid-cols-3 sm:overflow-visible sm:px-0">
                    @foreach ($plans as $k => $pl)
                        <button type="button" @click="pick('{{ $k }}')" @disabled(! $pl['available'])
                                :class="plan === '{{ $k }}' ? 'border-brand bg-brand-50 ring-1 ring-brand' : 'border-slate-200 bg-white'"
                                class="min-w-[15rem] shrink-0 snap-center space-y-2 rounded-xl border p-4 text-left disabled:cursor-not-allowed disabled:opacity-50 sm:min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="flex items-center gap-2 font-semibold text-navy">
                                    <span class="flex h-4 w-4 items-center justify-center rounded-full border" :class="plan === '{{ $k }}' ? 'border-brand' : 'border-slate-300'"><span x-show="plan === '{{ $k }}'" class="h-2 w-2 rounded-full bg-brand"></span></span>
                                    {{ $pl['label'] }}
                                </span>
                                @if ($pl['available'])
                                    <span class="rounded-md bg-navy px-2 py-0.5 text-xs font-medium text-white" :class="plan === '{{ $k }}' && 'bg-brand'">Rp {{ number_format($pl['price'], 0, ',', '.') }}/{{ $pl['mode'] === 'daily' ? 'mlm' : 'bln' }}</span>
                                @else
                                    <span class="text-xs text-slate-500">Belum tersedia</span>
                                @endif
                            </div>
                            @if ($pl['available'] && $pl['mode'] === 'monthly')
                                <dl class="space-y-1 border-t border-slate-200 pt-2 text-sm text-slate-600">
                                    <div class="flex justify-between"><dt>Sewa bulan pertama</dt><dd>Rp {{ number_format($pl['price'], 0, ',', '.') }}</dd></div>
                                    <div class="flex justify-between"><dt>Deposit dana titipan</dt><dd>Rp {{ number_format($pl['deposit'], 0, ',', '.') }}</dd></div>
                                    <div class="flex justify-between border-t border-slate-200 pt-1 font-semibold text-navy"><dt>Total awal ({{ strtolower($pl['label']) }})</dt><dd>Rp {{ number_format($pl['total'], 0, ',', '.') }}</dd></div>
                                </dl>
                            @elseif ($pl['available'])
                                <dl class="space-y-1 border-t border-slate-200 pt-2 text-sm text-slate-600">
                                    <div class="flex justify-between"><dt>Tarif per malam</dt><dd>Rp {{ number_format($pl['price'], 0, ',', '.') }}</dd></div>
                                    <div class="flex justify-between"><dt>Maksimal</dt><dd>1 orang</dd></div>
                                    <div class="flex justify-between border-t border-slate-200 pt-1 font-semibold text-navy"><dt>Total</dt><dd>tarif × malam</dd></div>
                                </dl>
                            @endif
                        </button>
                    @endforeach
                </div>

                <div x-show="plan === 'two'" x-cloak class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">Kamar untuk 2 orang tidak diperuntukkan bagi pasangan suami istri.</div>
                <div x-show="daily" x-cloak class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">Sewa harian maksimal 1 orang. Ketentuan pembayaran sewa harian dikonfirmasi oleh pengelola.</div>

                <x-owner.card title="Pilih kamar" note="Pilih sendiri kamar yang Anda inginkan. Kamar baru dipesan setelah pengajuan disetujui pengelola.">
                    <div class="space-y-4 p-4">
                        <input type="hidden" name="room_id" :value="room">
                        @forelse ($floors as $f)
                            <div>
                                <div class="mb-2 text-xs font-medium text-slate-500">{{ $f['name'] }}</div>
                                <div class="grid grid-cols-3 gap-2 sm:grid-cols-5">
                                    @foreach ($f['rooms'] as $rm)
                                        <button type="button" @click="pickRoom({{ $rm['id'] }})" :disabled="!roomOk({{ $rm['id'] }})"
                                                :class="room == '{{ $rm['id'] }}' ? 'border-brand bg-brand-50 ring-2 ring-brand' : (roomOk({{ $rm['id'] }}) ? 'border-emerald-200 bg-emerald-50/40 hover:border-brand' : 'border-slate-200 bg-slate-50 text-slate-400')"
                                                class="rounded-lg border p-2 text-center disabled:cursor-not-allowed">
                                            <div class="font-semibold">{{ $rm['code'] }}</div>
                                            <div class="text-[11px]" x-text="roomLabel({{ $rm['id'] }})"></div>
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <p class="text-sm text-slate-500">Belum ada data kamar.</p>
                        @endforelse
                        <p x-show="r" x-cloak class="text-sm font-medium text-navy" x-text="r ? 'Dipilih: Kamar ' + r.code + ' · ' + r.type + ' · ' + rp(rate()) + (daily ? ' / malam' : ' / bulan') : ''"></p>
                        <p class="text-xs text-slate-500">Kamar yang Anda inginkan penuh? <a href="{{ route('site.waitlist') }}" class="font-medium text-brand-700 underline">Gabung daftar tunggu</a>.</p>
                    </div>
                </x-owner.card>

                <x-owner.card title="Data penghuni">
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <label class="text-sm"><span class="{{ $lbl }}" x-text="plan === 'two' ? 'Nama lengkap 1' : 'Nama lengkap'"></span><input name="name" value="{{ old('name') }}" autocomplete="name" required class="{{ $in }}"></label>
                        <template x-if="plan === 'two'"><label class="text-sm"><span class="{{ $lbl }}">Nama lengkap 2</span><input name="name_2" value="{{ old('name_2') }}" required class="{{ $in }}"></label></template>
                        <template x-if="!daily">
                            <div class="contents">
                                <label class="text-sm"><span class="{{ $lbl }}">Tempat lahir</span><input name="birth_place" value="{{ old('birth_place') }}" required class="{{ $in }}"></label>
                                <label class="text-sm"><span class="{{ $lbl }}">Tanggal lahir</span><input type="date" name="birth_date" value="{{ old('birth_date') }}" required class="{{ $in }}"></label>
                            </div>
                        </template>
                        <label class="text-sm"><span class="{{ $lbl }}">No. KTP / identitas</span><input name="id_number" value="{{ old('id_number') }}" inputmode="text" required class="{{ $in }}"></label>
                        <label class="text-sm"><span class="{{ $lbl }}">No. telepon / WhatsApp</span><input name="phone" value="{{ old('phone') }}" type="tel" inputmode="tel" placeholder="0812xxxxxxxx" required class="{{ $in }}"></label>
                        <label class="text-sm sm:col-span-2"><span class="{{ $lbl }}">Alamat asal</span><textarea name="address" rows="2" required class="{{ $in }}">{{ old('address') }}</textarea></label>
                        <template x-if="!daily">
                            <div class="contents">
                                <label class="text-sm"><span class="{{ $lbl }}">Agama <span class="font-normal text-slate-400">(opsional)</span></span><input name="religion" value="{{ old('religion') }}" class="{{ $in }}"></label>
                                <div class="text-sm sm:col-span-2">
                                    <span class="{{ $lbl }}">Pekerjaan / status</span>
                                    <div class="flex flex-wrap gap-4 py-1">
                                        @foreach (['Mahasiswa', 'Karyawan', 'Lainnya'] as $j)<label class="flex items-center gap-2"><input type="radio" name="job_choice" value="{{ $j }}" x-model="job"> {{ $j }}</label>@endforeach
                                    </div>
                                    <input x-show="job === 'Lainnya'" name="job_other" value="{{ old('job_other') }}" placeholder="Sebutkan" :required="job === 'Lainnya'" class="{{ $in }} mt-1">
                                </div>
                                <label class="text-sm sm:col-span-2"><span class="{{ $lbl }}">Nama instansi / kampus / perusahaan</span><input name="institution" value="{{ old('institution') }}" required class="{{ $in }}"></label>
                            </div>
                        </template>
                        <label class="text-sm sm:col-span-2"><span class="{{ $lbl }}" x-text="plan === 'two' ? 'Foto identitas penghuni 1' : 'Foto identitas'"></span>
                            <input type="file" name="id_doc" accept="image/jpeg,image/png,application/pdf" required class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700">
                            <span class="text-xs text-slate-400">JPG, PNG, atau PDF, maksimal 5 MB. Disimpan privat dan hanya dilihat pengelola.</span></label>
                        <template x-if="plan === 'two'"><label class="text-sm sm:col-span-2"><span class="{{ $lbl }}">Foto identitas penghuni 2 <span class="font-normal text-slate-400">(opsional)</span></span>
                            <input type="file" name="id_doc_2" accept="image/jpeg,image/png,application/pdf" class="block w-full text-sm file:mr-3 file:rounded-md file:border-0 file:bg-brand-50 file:px-3 file:py-2 file:text-brand-700"></label></template>
                    </div>
                </x-owner.card>

                <x-owner.card title="Kontak darurat">
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <label class="text-sm"><span class="{{ $lbl }}">Nama</span><input name="emergency_name" value="{{ old('emergency_name') }}" required class="{{ $in }}"></label>
                        <label class="text-sm"><span class="{{ $lbl }}">Hubungan</span><input name="emergency_relation" value="{{ old('emergency_relation') }}" required class="{{ $in }}"></label>
                        <label class="text-sm"><span class="{{ $lbl }}">No. telepon darurat</span><input name="emergency_phone" value="{{ old('emergency_phone') }}" type="tel" inputmode="tel" required class="{{ $in }}"></label>
                        <label class="text-sm"><span class="{{ $lbl }}">Alamat kontak darurat <span x-show="daily" class="font-normal text-slate-400">(opsional)</span></span><input name="emergency_address" value="{{ old('emergency_address') }}" :required="!daily" class="{{ $in }}"></label>
                    </div>
                </x-owner.card>

                <x-owner.card title="Sewa">
                    <div class="grid gap-4 p-4 sm:grid-cols-2">
                        <label class="text-sm"><span class="{{ $lbl }}" x-text="daily ? 'Tanggal masuk' : 'Tanggal masuk'"></span><input type="date" name="start_date" x-model="start" required class="{{ $in }}"></label>
                        <template x-if="daily"><label class="text-sm"><span class="{{ $lbl }}">Tanggal keluar</span><input type="date" name="end_date" x-model="end" required class="{{ $in }}"></label></template>
                        <label class="text-sm"><span class="{{ $lbl }}">Metode pembayaran</span>
                            <select name="payment_method" class="{{ $in }}"><option value="transfer" @selected(old('payment_method') === 'transfer')>Transfer</option><option value="cash" @selected(old('payment_method') === 'cash')>Tunai</option></select></label>
                    </div>
                    <p x-show="daily" x-cloak class="px-4 pb-4 text-sm text-slate-600" x-text="nights > 0 ? nights + ' malam × ' + rp(rate()) + ' = ' + rp(total) : 'Isi tanggal masuk dan keluar untuk melihat total.'"></p>
                </x-owner.card>

                <div class="space-y-2 text-sm">
                    <label class="flex gap-2"><input type="checkbox" name="consent_rules" value="1" required class="mt-1"> <span>Saya telah membaca dan menyetujui <a href="{{ route('site.about') }}" target="_blank" class="text-brand-700 underline">peraturan kos</a>, termasuk ketentuan deposit.</span></label>
                    <template x-if="plan === 'two'"><label class="flex gap-2"><input type="checkbox" name="couple_ack" value="1" required class="mt-1"> <span>Kami memahami kamar untuk 2 orang tidak diperuntukkan bagi pasangan suami istri.</span></label></template>
                    <label class="flex gap-2"><input type="checkbox" name="consent_data" value="1" required class="mt-1"> <span>Data yang saya isi benar dan dapat dipertanggungjawabkan, dan saya setuju data pribadi saya diproses pengelola untuk keperluan sewa, termasuk menerima pengingat tagihan dan kuitansi lewat WhatsApp.</span></label>
                </div>

                <div class="sticky bottom-0 -mx-4 flex items-center justify-between gap-3 border-t border-slate-200 bg-white/95 px-4 py-3 backdrop-blur sm:mx-0 sm:rounded-lg sm:border">
                    <div><div class="text-xs text-slate-500" x-text="!room ? 'Pilih kamar dulu' : (daily ? 'Perkiraan total' : 'Total awal')"></div><div class="font-semibold text-navy" x-text="total ? rp(total) : '—'"></div></div>
                    <button :disabled="!room" class="rounded-md bg-brand px-6 py-3 text-sm font-medium text-white hover:bg-brand-700 disabled:cursor-not-allowed disabled:opacity-40">Kirim pengajuan</button>
                </div>
            </form>
        @endif
    </div>
</x-site.layout>
