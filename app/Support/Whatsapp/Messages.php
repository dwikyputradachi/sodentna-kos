<?php

namespace App\Support\Whatsapp;

use App\Models\Invoice;
use App\Models\Receipt;
use App\Support\Owner\Fmt;

/** Indonesian message templates. Never include identity numbers. */
class Messages
{
    public static function invoice(string $kind, Invoice $i, string $property): string
    {
        $v = [
            '{nama}' => $i->rental->tenant->name, '{nama_properti}' => $property, '{nomor_kamar}' => $i->rental->room->code,
            '{nominal}' => Fmt::rp($i->total_amount), '{tanggal_jatuh_tempo}' => Fmt::date($i->due_date),
        ];

        return strtr(match ($kind) {
            'reminder' => 'Halo {nama}, mengingatkan bahwa pembayaran sewa {nama_properti}, kamar {nomor_kamar}, sebesar {nominal} akan jatuh tempo pada {tanggal_jatuh_tempo}. Terima kasih.',
            'due' => 'Halo {nama}, tagihan sewa {nama_properti}, kamar {nomor_kamar}, sebesar {nominal} telah jatuh tempo hari ini ({tanggal_jatuh_tempo}). Silakan melakukan pembayaran dan mengonfirmasi kepada pengelola. Terima kasih.',
            default => 'Halo {nama}, pembayaran sewa kamar {nomor_kamar} sebesar {nominal} telah melewati tanggal jatuh tempo {tanggal_jatuh_tempo}. Mohon konfirmasi pembayaran kepada pengelola. Terima kasih.',
        }, $v);
    }

    public static function receipt(Receipt $r, Invoice $i, string $property): string
    {
        return 'Halo ' . $i->rental->tenant->name . ', pembayaran sewa ' . $property . ', kamar ' . $i->rental->room->code . ', ' . strtolower($i->periodLabel())
            . ' sebesar ' . Fmt::rp($i->total_amount) . ' telah kami terima. Kuitansi ' . $r->number . ': ' . $r->url() . ' Terima kasih.';
    }
}
