<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Kos Sodentna</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
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
</head>
<body class="flex min-h-screen flex-col justify-between bg-slate-50 px-4 py-8 font-sans text-slate-800 antialiased">

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

    <main class="mx-auto my-6 w-full max-w-sm">
        <form method="POST" action="{{ route('login') }}" x-data="{ showPassword: false }" class="space-y-5 rounded-2xl border border-slate-200/90 bg-white p-6 shadow-xs sm:p-7">
            @csrf

            <div class="space-y-1">
                <h1 class="text-xl font-semibold tracking-tight text-navy">Masuk ke Portal</h1>
                <p class="text-sm text-slate-500">Akses khusus pengelola dan penghuni Kos Sodentna.</p>
            </div>

            @if (session('status'))
                <div class="rounded-lg border border-brand/20 bg-brand-50 px-3.5 py-2.5 text-xs font-medium text-brand-700">
                    {{ session('status') }}
                </div>
            @endif

            @error('login')
                <div class="flex items-start gap-2 rounded-lg border border-red-200 bg-red-50 px-3.5 py-2.5 text-xs text-red-700">
                    <svg class="mt-0.5 h-4 w-4 shrink-0 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" />
                    </svg>
                    <span>{{ $message }}</span>
                </div>
            @enderror

            <div class="space-y-4">
                <div>
                    <label for="login" class="mb-1.5 block text-sm font-medium text-slate-700">Nomor WhatsApp atau email</label>
                    <input
                        id="login"
                        type="text"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="0812xxxxxxxx atau nama@email.com"
                        autocomplete="username"
                        required
                        autofocus
                        class="w-full rounded-lg border {{ $errors->has('login') ? 'border-red-300 bg-red-50/30' : 'border-slate-300 bg-white' }} px-3.5 py-2.5 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-brand focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand/20"
                    >
                </div>

                {{-- Input Kata Sandi + Toggle Lihat --}}
                <div>
                    <div class="mb-1.5 flex items-center justify-between">
                        <label for="password" class="block text-sm font-medium text-slate-700">Kata sandi</label>
                    </div>
                    <div class="relative">
                        <input
                            id="password"
                            :type="showPassword ? 'text' : 'password'"
                            name="password"
                            placeholder="Masukkan kata sandi"
                            autocomplete="current-password"
                            required
                            class="w-full rounded-lg border {{ $errors->has('password') ? 'border-red-300 bg-red-50/30' : 'border-slate-300 bg-white' }} py-2.5 pl-3.5 pr-10 text-sm text-slate-800 placeholder:text-slate-400 transition focus:border-brand focus:bg-white focus:outline-none focus:ring-2 focus:ring-brand/20"
                        >
                        <button
                            type="button"
                            @click="showPassword = !showPassword"
                            :aria-label="showPassword ? 'Sembunyikan kata sandi' : 'Lihat kata sandi'"
                            class="absolute inset-y-0 right-0 flex items-center px-3 text-slate-400 transition hover:text-slate-600 focus:outline-none"
                        >
                            <svg x-show="!showPassword" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                            <svg x-show="showPassword" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Remember Me --}}
                <div class="flex items-center justify-between pt-0.5">
                    <label class="inline-flex cursor-pointer items-center gap-2 text-sm text-slate-600 select-none">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }} class="h-4 w-4 rounded border-slate-300 text-brand accent-brand focus:ring-brand/30">
                        <span>Ingat saya</span>
                    </label>
                </div>
            </div>

            <button type="submit" class="w-full rounded-lg bg-brand px-4 py-2.5 text-sm font-semibold text-white shadow-xs transition hover:bg-brand-700 focus:outline-none focus:ring-2 focus:ring-brand/40 focus:ring-offset-1">
                Masuk
            </button>

            <div class="border-t border-slate-100 pt-4 text-xs leading-relaxed text-slate-500">
                <p>
                    Belum punya akun? Akun penghuni dibuat oleh pengelola setelah
                    <a href="{{ url('/ketersediaan-kamar') }}" class="font-medium text-brand-700 underline decoration-brand/40 underline-offset-2 hover:text-brand">pengajuan sewa</a>
                    Anda disetujui.
                </p>
            </div>
        </form>
    </main>

    <footer class="mx-auto w-full max-w-sm text-center text-xs text-slate-400">
        <p>© {{ date('Y') }} Kos Sodentna · Jl. Kartini VI No. A4, Batam</p>
    </footer>

</body>
</html>