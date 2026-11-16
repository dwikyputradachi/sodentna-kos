@props(['log'])
@php $t = \App\Support\Owner\Fmt::date($log->created_at->copy()->timezone('Asia/Jakarta'), 'j M H:i'); @endphp
@if ($log->status === 'sent')
    <div class="text-[11px] text-emerald-600">Terkirim otomatis {{ $t }}</div>
@elseif ($log->status === 'failed')
    <div class="text-[11px] text-red-600">Gagal kirim otomatis {{ $t }}. Kirim manual dengan tombol.</div>
@else
    <div class="text-[11px] text-slate-400">Dibuka {{ $t }}</div>
@endif
