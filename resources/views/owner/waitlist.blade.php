@php use App\Support\Owner\Fmt; @endphp
<x-owner.layout title="Daftar Tunggu" active="applications">
    <x-owner.header title="Daftar Tunggu" subtitle="Calon penghuni yang menunggu kamar kosong, urut siapa mendaftar lebih dulu." />

    @if (session('ok'))<div class="rounded-md bg-emerald-50 p-3 text-sm text-emerald-700">{{ session('ok') }}</div>@endif

    <x-owner.card>
        <table class="w-full text-sm">
            <thead class="bg-slate-50"><tr>@foreach (['Antrean', 'Pemohon', 'Tipe', 'Penghuni', 'Mulai', 'Status', 'Aksi'] as $h)<th class="px-4 py-2 text-left text-xs font-medium text-slate-500">{{ $h }}</th>@endforeach</tr></thead>
            <tbody class="divide-y divide-slate-100">
            @forelse ($rows as $row)
                @php $e = $row['e']; @endphp
                <tr>
                    <td class="px-4 py-3 font-medium">{{ $row['pos'] ?? '—' }}<div class="text-xs font-normal text-slate-400">{{ $e->code }}</div></td>
                    <td class="px-4 py-3">{{ $e->name }}<div class="text-xs text-slate-400">{{ $e->phone }}</div></td>
                    <td class="px-4 py-3 text-slate-500">{{ $e->type->name ?? 'Apa saja' }}</td>
                    <td class="px-4 py-3">{{ $e->occupants }} orang</td>
                    <td class="px-4 py-3 text-slate-500">{{ Fmt::date($e->desired_start) }}</td>
                    <td class="px-4 py-3"><x-owner.badge :label="$e->statusLabel()" /></td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1.5">
                            @if (in_array($e->status, ['waiting', 'offered']))
                                <a href="{{ $row['wa'] }}" target="_blank" rel="noopener" class="rounded-md bg-brand px-2.5 py-1 text-xs font-medium text-white">Tawarkan via WhatsApp</a>
                            @endif
                            @foreach (['waiting' => ['offered', 'Tandai ditawari'], 'offered' => ['converted', 'Selesai']] as $from => [$to, $label])
                                @if ($e->status === $from)
                                    <form method="POST" action="{{ route('owner.waitlist.status', $e) }}">@csrf<input type="hidden" name="status" value="{{ $to }}"><button class="rounded-md border border-slate-300 px-2.5 py-1 text-xs">{{ $label }}</button></form>
                                @endif
                            @endforeach
                            @if (in_array($e->status, ['waiting', 'offered']))
                                <form method="POST" action="{{ route('owner.waitlist.status', $e) }}" onsubmit="return confirm('Batalkan antrean ini?')">@csrf<input type="hidden" name="status" value="cancelled"><button class="rounded-md border border-red-200 px-2.5 py-1 text-xs text-red-700">Batalkan</button></form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="px-4 py-8 text-center text-slate-500">Belum ada yang menunggu.</td></tr>
            @endforelse
            </tbody>
        </table>
    </x-owner.card>
    <p class="text-xs text-slate-400">Tombol WhatsApp hanya membuka chat; pesan dikirim manual. Masa berlaku tawaran belum ditetapkan (keputusan pemilik).</p>
</x-owner.layout>
