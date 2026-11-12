<?php

namespace App\Support\Owner;

use Carbon\CarbonInterface;

/**
 * Billing state evaluation + WhatsApp click-to-chat links (semi-automated, v1).
 * A click only opens WhatsApp; it is NOT proof of delivery.
 */
class Billing
{
    /** @return array{label:string, action:?string} action = reminder|due|overdue|null */
    public static function state(array $inv, CarbonInterface $today): array
    {
        if ($inv['db'] === 'paid') return ['label' => 'Lunas', 'action' => null];
        // Pending verification suppresses all reminders.
        if ($inv['db'] === 'submitted') return ['label' => 'Menunggu Verifikasi', 'action' => null];

        $days = (int) $today->diffInDays($inv['due']->copy()->startOfDay(), false); // due - today
        $rejected = $inv['db'] === 'rejected';

        if ($days < 0) return ['label' => $rejected ? 'Ditolak' : 'Terlambat', 'action' => 'overdue'];
        if ($days === 0) return ['label' => $rejected ? 'Ditolak' : 'Jatuh Tempo', 'action' => 'due'];
        if ($days <= 2) return ['label' => $rejected ? 'Ditolak' : 'H-2', 'action' => 'reminder'];
        return ['label' => $rejected ? 'Ditolak' : 'Belum Jatuh Tempo', 'action' => null];
    }

    public static function actionLabel(?string $a): ?string
    {
        return match ($a) {
            'reminder' => 'Kirim Pengingat',
            'due' => 'Kirim Tagihan',
            'overdue' => 'Kirim Pengingat Keterlambatan',
            default => null,
        };
    }

    public static function phone(string $raw): string
    {
        $d = preg_replace('/\D+/', '', $raw);
        return match (true) {
            str_starts_with($d, '0') => '62' . substr($d, 1),
            str_starts_with($d, '62') => $d,
            str_starts_with($d, '8') => '62' . $d,
            default => $d,
        };
    }

    public static function message(string $action, array $inv, string $property): string
    {
        $v = [
            '{nama}' => $inv['tenant'], '{nama_properti}' => $property, '{nomor_kamar}' => $inv['room'],
            '{nominal}' => Fmt::rp($inv['amount']), '{tanggal_jatuh_tempo}' => Fmt::date($inv['due']),
        ];
        $tpl = match ($action) {
            'reminder' => 'Halo {nama}, mengingatkan bahwa pembayaran sewa {nama_properti}, kamar {nomor_kamar}, sebesar {nominal} akan jatuh tempo pada {tanggal_jatuh_tempo}. Terima kasih.',
            'due' => 'Halo {nama}, tagihan sewa {nama_properti}, kamar {nomor_kamar}, sebesar {nominal} telah jatuh tempo hari ini ({tanggal_jatuh_tempo}). Silakan melakukan pembayaran dan mengunggah bukti melalui portal penghuni. Terima kasih.',
            default => 'Halo {nama}, pembayaran sewa kamar {nomor_kamar} sebesar {nominal} telah melewati tanggal jatuh tempo {tanggal_jatuh_tempo}. Mohon konfirmasi pembayaran melalui portal penghuni. Terima kasih.',
        };
        return strtr($tpl, $v);
    }

    public static function link(string $action, array $inv, string $property): string
    {
        return 'https://wa.me/' . self::phone($inv['phone']) . '?text=' . rawurlencode(self::message($action, $inv, $property));
    }
}
