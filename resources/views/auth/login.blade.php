<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk · Kos Sodentna</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>tailwind.config={theme:{extend:{colors:{navy:{DEFAULT:'#12294a'},brand:{DEFAULT:'#0f9d94',50:'#e6f6f5',700:'#0b7a73'}},fontFamily:{sans:['Inter','ui-sans-serif','system-ui','sans-serif']}}}}</script>
</head>
<body class="flex min-h-screen items-center justify-center bg-slate-50 p-4 font-sans text-slate-800 antialiased">
    <form method="POST" action="{{ route('login') }}" class="w-full max-w-sm space-y-4 rounded-xl border border-slate-200 bg-white p-6">
        @csrf
        <div><h1 class="text-xl font-semibold text-navy">Masuk</h1><p class="text-sm text-slate-500">Kos Sodentna — pemilik dan penghuni.</p></div>
        <label class="block text-sm"><span class="mb-1 block font-medium">Nomor WhatsApp atau email</span>
            <input name="login" value="{{ old('login') }}" autocomplete="username" required autofocus class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"></label>
        <label class="block text-sm"><span class="mb-1 block font-medium">Kata sandi</span>
            <input type="password" name="password" autocomplete="current-password" required class="w-full rounded-md border border-slate-300 px-3 py-2 focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand"></label>
        @error('login')<p class="rounded-md bg-red-50 p-2 text-sm text-red-700">{{ $message }}</p>@enderror
        @error('password')<p class="text-sm text-red-600">{{ $message }}</p>@enderror
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="remember"> Ingat saya</label>
        <button class="w-full rounded-md bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Masuk</button>
        <p class="text-xs text-slate-400">Belum punya akun? Akun dibuat pengelola setelah pengajuan sewa Anda disetujui.</p>
    </form>
</body>
</html>
