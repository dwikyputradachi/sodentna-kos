@php
$in = 'w-full rounded-md border border-slate-300 px-3 py-2.5 text-base sm:text-sm focus:border-brand focus:outline-none focus:ring-1 focus:ring-brand disabled:bg-slate-50 disabled:text-slate-500';
$masked = $tenant->id_number ? (strlen($tenant->id_number) > 6 ? substr($tenant->id_number, 0, 4) . str_repeat('•', strlen($tenant->id_number) - 8) . substr($tenant->id_number, -4) : '••••••') : '—';
@endphp
<x-tenant.layout title="Profil" active="profile">
    <div><h1 class="text-xl font-semibold text-navy">Profil</h1><p class="text-sm text-slate-500">Nama, identitas, dan nomor WhatsApp (untuk masuk) hanya dapat diubah pengelola.</p></div>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif
    @if ($errors->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700"><ul class="list-disc pl-5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>@endif

    <form method="POST" action="{{ route('tenant.profile.update') }}" class="space-y-4 rounded-lg border border-slate-200 bg-white p-4">@csrf @method('PUT')
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="text-sm"><span class="mb-1 block font-medium">Nama lengkap</span><input value="{{ $tenant->name }}" disabled class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Nomor identitas</span><input value="{{ $masked }}" disabled class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Nomor WhatsApp</span><input value="{{ $tenant->phone }}" disabled class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Email (opsional)</span><input type="email" name="email" value="{{ old('email', $user->email) }}" class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Kontak darurat (nama)</span><input name="emergency_name" value="{{ old('emergency_name', $tenant->emergency_name) }}" required class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Hubungan</span><input name="emergency_relation" value="{{ old('emergency_relation', $tenant->emergency_relation) }}" required class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Kontak darurat (telepon)</span><input type="tel" inputmode="tel" name="emergency_phone" value="{{ old('emergency_phone', $tenant->emergency_phone) }}" required class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Alamat kontak darurat</span><input name="emergency_address" value="{{ old('emergency_address', $tenant->emergency_address) }}" class="{{ $in }}"></label>
        </div>
        <button class="rounded-md bg-brand px-4 py-2.5 text-sm font-medium text-white hover:bg-brand-700">Simpan profil</button>
    </form>

    <form method="POST" action="{{ route('tenant.profile.password') }}" class="space-y-4 rounded-lg border border-slate-200 bg-white p-4">@csrf @method('PUT')
        <h2 class="text-sm font-semibold text-navy">Ganti kata sandi</h2>
        @if (session('ok_password'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok_password') }}</div>@endif
        @if ($errors->password->any())<div class="rounded-md bg-red-50 p-3 text-sm text-red-700">@foreach ($errors->password->all() as $e)<div>{{ $e }}</div>@endforeach</div>@endif
        <div class="grid gap-4 sm:grid-cols-3">
            <label class="text-sm"><span class="mb-1 block font-medium">Kata sandi saat ini</span><input type="password" name="current_password" autocomplete="current-password" required class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Kata sandi baru</span><input type="password" name="password" autocomplete="new-password" required class="{{ $in }}"></label>
            <label class="text-sm"><span class="mb-1 block font-medium">Ulangi kata sandi baru</span><input type="password" name="password_confirmation" autocomplete="new-password" required class="{{ $in }}"></label>
        </div>
        <button class="rounded-md border border-slate-300 px-4 py-2.5 text-sm font-medium">Ganti kata sandi</button>
    </form>
</x-tenant.layout>
