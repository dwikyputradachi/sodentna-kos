@php
    $waNumber = class_exists(\App\Support\Site\DemoSite::class) && class_exists(\App\Support\Owner\Billing::class)
        ? \App\Support\Owner\Billing::phone(\App\Support\Site\DemoSite::WA)
        : '6281364449270';
    $waResetLink = 'https://wa.me/' . $waNumber . '?text=' . rawurlencode('Halo pengelola Kos Sodentna, tautan aktivasi akun saya sudah kedaluwarsa. Mohon bantuannya untuk mengirim tautan baru.');
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktivasi Akun · Kos Sodentna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        navy: { DEFAULT: '#12294a', 800: '#1a3863' },
                        brand: { DEFAULT: '#0f9d94', 50: '#e6f6f5', 600: '#0d8a82', 700: '#0b7a73' }
                    },
                    fontFamily: {
                        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif']
                    }
                }
            }
        }
    </script>
    <style>[x-cloak] { display: none !important; }</style>
</head>
<body class="flex min-h-screen flex-col justify-between bg-slate-50 px-4 py-8 font-sans text-slate-800 antialiased">

    {{-- Top Bar --}}
    <div class="mx-auto flex w-full max-w-sm items-center justify-between">
        <a href="{{ url('/') }}" class="inline-flex items-center gap-2.5 text-navy transition hover:opacity-85">
            <span class="flex h-8 w-8 items-center justify-center rounded-lg bg-brand text-sm font-bold text-white">S</span>
            <span class="text-base font-bold tracking-tight">SODENTNA<span class="text-brand">.</span></span>
        </a>
        <a href="{{ url('/') }}" class="inline-flex items-center gap-1 text-xs font-medium text-slate-500 transition hover:text-navy">
            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" />
            </svg>
            Beranda
        </a>
    </div>

    {{-- Kartu Utama --}}
    <main class="mx-auto my-6 w-full max-w-sm">
        <div class="rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs sm:p-7">
            @if ($valid)
                <div class="space-y-5">
                    {{-- Header State Valid --}}
                    <div class="space-y-2">
                        <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-brand-50 text-brand">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 0 1 3 3m3 0a6 6 0 0 1-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1 1 21.75 8.25Z" />
                            </svg>
                        </div>
                        <h1 class="text-xl font-semibold tracking-tight text-navy">Aktivasi Akun</h1>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Halo <span class="font-medium text-slate-700">{{ $name }}</span>, buat kata sandi untuk mulai masuk ke portal penghuni.
                        </p>
                    </div>

                    {{-- Pesan Error Validasi --}}
                    @if ($errors->any())
                        <div class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                            <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                            </svg>
                            <div class="space-y-0.5">
                                @foreach ($errors->all() as $e)
                                    <p>{{ $e }}</p>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- Form Buat Kata Sandi --}}
                    <form
                        method="POST"
                        action="{{ route('activation.store', $token) }}"
                        x-data="{ pass: '', confirm: '', showPass: false, showConfirm: false }"
                        class="space-y-4"
                    >
                        @csrf

                        {{-- Kata Sandi Baru --}}
                        <div>
                            <label for="password" class="mb-1.5 block text-sm font-medium text-slate-700">Kata sandi baru</label>
                            <div class="relative">
                                <input
                                    id="password"
                                    :type="showPass ? 'text' : 'password'"
                                    name="password"
                                    x-model="pass"
                                    placeholder="Minimal 8 karakter"
                                    required
                                    autofocus
                                    autocomplete="new-password"
                                    class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-300 bg-red-50/30' : 'border-slate-300 bg-white' }} py-2.5 pl-3.5 pr-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-brand focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand/20"
                                >
                                <button
                                    type="button"
                                    @click="showPass = !showPass"
                                    :aria-label="showPass ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 focus:outline-none"
                                >
                                    <svg x-show="!showPass" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showPass" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            <p class="mt-1.5 flex items-center gap-1.5 text-xs" :class="pass.length >= 8 ? 'text-emerald-600' : 'text-slate-400'">
                                <span class="h-1.5 w-1.5 rounded-full" :class="pass.length >= 8 ? 'bg-emerald-500' : 'bg-slate-300'"></span>
                                <span>Minimal 8 karakter</span>
                            </p>
                        </div>

                        {{-- Konfirmasi Kata Sandi --}}
                        <div>
                            <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-slate-700">Ulangi kata sandi</label>
                            <div class="relative">
                                <input
                                    id="password_confirmation"
                                    :type="showConfirm ? 'text' : 'password'"
                                    name="password_confirmation"
                                    x-model="confirm"
                                    placeholder="Ketik ulang kata sandi"
                                    required
                                    autocomplete="new-password"
                                    class="w-full rounded-lg border border-slate-300 bg-white py-2.5 pl-3.5 pr-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-brand focus:outline-none focus:ring-2 focus:ring-brand/20"
                                >
                                <button
                                    type="button"
                                    @click="showConfirm = !showConfirm"
                                    :aria-label="showConfirm ? 'Sembunyikan konfirmasi kata sandi' : 'Lihat konfirmasi kata sandi'"
                                    class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 focus:outline-none"
                                >
                                    <svg x-show="!showConfirm" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                    <svg x-show="showConfirm" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                </button>
                            </div>
                            <p x-show="confirm.length > 0" x-cloak class="mt-1.5 text-xs" :class="pass === confirm ? 'text-emerald-600' : 'text-amber-600'">
                                <span x-text="pass === confirm ? 'Kata sandi cocok' : 'Kata sandi belum cocok'"></span>
                            </p>
                        </div>

                        <button type="submit" class="w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand/40 focus:ring-offset-1">
                            Aktifkan Akun
                        </button>
                    </form>
                </div>
            @else
                {{-- State Tautan Tidak Berlaku / Kedaluwarsa --}}
                <div class="space-y-4">
                    <div class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-amber-50 text-amber-600">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                        </svg>
                    </div>

                    <div class="space-y-1.5">
                        <h1 class="text-xl font-semibold tracking-tight text-navy">Tautan Tidak Berlaku</h1>
                        <p class="text-sm leading-relaxed text-slate-500">
                            Tautan aktivasi ini sudah pernah dipakai atau masa berlakunya telah habis.
                        </p>
                    </div>

                    <div class="space-y-2.5 pt-2">
                        <a
                            href="{{ $waResetLink }}"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="flex w-full items-center justify-center gap-2 rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-700"
                        >
                            <span>Minta Tautan Baru ke Pengelola</span>
                        </a>
                        <a
                            href="{{ route('login') }}"
                            class="flex w-full items-center justify-center rounded-lg border border-slate-300 bg-white px-4 py-2.5 text-sm font-medium text-navy transition hover:bg-slate-50"
                        >
                            Sudah aktif? Masuk ke portal
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </main>

    {{-- Footer Ringkas --}}
    <footer class="mx-auto w-full max-w-sm text-center text-xs text-slate-400">
        <p>© {{ date('Y') }} Kos Sodentna · Jl. Kartini VI No. A4, Batam</p>
    </footer>

</body>
</html>