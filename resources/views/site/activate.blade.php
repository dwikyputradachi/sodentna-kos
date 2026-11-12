<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Aktivasi Akun · Kos Sodentna</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a'},brand:{DEFAULT:'#0f9d94',50:'#e6f6f5',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-800 antialiased">
    <div class="w-full max-w-sm space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @if ($valid)
            <div><h1 class="text-xl font-semibold text-navy">Aktivasi akun</h1><p class="text-sm text-slate-500">Halo {{ $name }}, buat kata sandi untuk masuk ke portal penghuni.</p></div>
            @if ($errors->any())<div class="rounded-md bg-red-50 p-2 text-sm text-red-700">@foreach ($errors->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
            <form method="POST" action="{{ route('activation.store', $token) }}" class="space-y-3">@csrf
                <label class="block text-sm"><span class="mb-1 block font-medium">Kata sandi (min. 8 karakter)</span><input type="password" name="password" required autocomplete="new-password" class="w-full rounded-md border border-slate-300 px-3 py-2"></label>
                <label class="block text-sm"><span class="mb-1 block font-medium">Ulangi kata sandi</span><input type="password" name="password_confirmation" required autocomplete="new-password" class="w-full rounded-md border border-slate-300 px-3 py-2"></label>
                <button class="w-full rounded-md bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Aktifkan akun</button>
            </form>
        @else
            <h1 class="text-xl font-semibold text-navy">Tautan tidak berlaku</h1>
            <p class="text-sm text-slate-500">Tautan ini sudah dipakai atau kedaluwarsa. Hubungi pengelola untuk meminta tautan baru.</p>
        @endif
    </div>
</body>
</html>
