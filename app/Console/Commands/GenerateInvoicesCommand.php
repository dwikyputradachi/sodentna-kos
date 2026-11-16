<?php

namespace App\Console\Commands;

use App\Actions\GenerateInvoices;
use Illuminate\Console\Command;

class GenerateInvoicesCommand extends Command
{
    protected $signature = 'billing:generate';

    protected $description = 'Buat tagihan sewa bulanan yang akan jatuh tempo (aman dijalankan berulang).';

    public function handle(GenerateInvoices $generator): int
    {
        $this->info($generator->run() . ' tagihan baru dibuat.');

        return self::SUCCESS;
    }
}
