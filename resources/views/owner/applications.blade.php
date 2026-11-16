@php use App\Models\RentalApplication as A; use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Pengajuan Sewa" active="applications">
    <x-owner.header title="Pengajuan Sewa" subtitle="Tinjau pengajuan, lalu setujui atau tolak. Kamar belum dipesan sebelum disetujui.">
        <a href="{{ route('owner.waitlist') }}" class="rounded-md border border-slate-300 bg-white px-3 py-2 text-sm font-medium">Daftar Tunggu</a>
    </x-owner.header>

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif

    <div class="flex flex-wrap gap-2 text-sm">
        <a href="{{ route('owner.applications') }}" class="rounded-md px-3 py-1.5 {{ $status ? 'border border-slate-300 bg-white' : 'bg-navy text-white' }}">Semua</a>
        @foreach (A::STATUSES as $k => $l)
            <a href="{{ route('owner.applications', ['status' => $k]) }}" class="rounded-md px-3 py-1.5 {{ $status === $k ? 'bg-navy text-white' : 'border border-slate-300 bg-white' }}">{{ $l }} ({{ $counts[$k] ?? 0 }})</a>
        @endforeach
    </div>

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Kode', 'Pemohon', 'Kamar', 'Mulai', 'Masuk', 'Status', ''] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($apps as $a)
                <tr>
                    <td class="whitespace-nowrap px-4 py-3 font-medium">{{ $a->code }} <x-copy :text="$a->code" class="ml-1" /></td>
                    <td class="px-4 py-3">{{ $a->name }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ $a->type->name }} · {{ $a->mode === 'daily' ? 'harian' : 'bulanan' }} · {{ $a->occupants }} orang</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($a->start_date) }}</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($a->created_at->timezone('Asia/Jakarta'), 'j M H:i') }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$a->statusLabel()" /></td>
                    <td class="px-4 py-3 text-right"><a href="{{ route('owner.applications.show', $a) }}" class="font-medium text-brand-700">Tinjau</a></td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada pengajuan.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
</x-owner.layout>
