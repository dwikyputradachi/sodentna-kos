@props(['label'])
@php
$tone = match ($label) {
    'Tersedia', 'Lunas', 'Aktif', 'Selesai', 'Dikembalikan', 'Disetujui', 'Aman' => 'bg-emerald-50 text-emerald-700 ring-emerald-200',
    'Dipesan', 'Menunggu', 'H-2', 'Jatuh Tempo', 'Proses', 'Proses Keluar', 'Dikerjakan', 'Ditahan', 'Calon Penghuni', 'Menipis', 'Ditawari' => 'bg-amber-50 text-amber-700 ring-amber-200',
    'Terlambat', 'Ditolak', 'Dalam Perbaikan', 'Habis' => 'bg-red-50 text-red-700 ring-red-200',
    'Terisi', 'Menunggu Verifikasi' => 'bg-sky-50 text-sky-700 ring-sky-200',
    default => 'bg-slate-100 text-slate-600 ring-slate-200',
};
@endphp
<span class="inline-flex items-center whitespace-nowrap rounded-full px-2 py-0.5 text-xs font-medium ring-1 ring-inset {{ $tone }}">{{ $label }}</span>
