<?php

namespace App\Console\Commands;

use App\Support\Whatsapp\AutoMessenger;
use Illuminate\Console\Command;

class SendRemindersCommand extends Command
{
    protected $signature = 'billing:remind {--ignore-window : Abaikan batas jam kirim 08.00-20.00 (untuk uji coba)}';

    protected $description = 'Kirim pengingat, tagihan, dan kuitansi otomatis lewat bot WhatsApp (hanya untuk properti yang mengaktifkannya).';

    public function handle(AutoMessenger $messenger): int
    {
        $r = $messenger->run((bool) $this->option('ignore-window'));
        $this->info("Terkirim: {$r['sent']}, gagal: {$r['failed']}.");

        return self::SUCCESS;
    }
}
