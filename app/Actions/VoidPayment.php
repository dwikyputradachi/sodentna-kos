<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use DomainException;
use Illuminate\Support\Facades\DB;

/** Correction without deleting: the payment stays in the books (marked cancelled) and the invoice is payable again. */
class VoidPayment
{
    public function handle(Payment $payment, string $reason, User $by): void
    {
        DB::transaction(function () use ($payment, $reason, $by) {
            $p = Payment::lockForUpdate()->findOrFail($payment->id);
            if ($p->voided_at) throw new DomainException('Pembayaran ini sudah dibatalkan.');

            $p->update(['voided_at' => now(), 'void_reason' => $reason, 'voided_by' => $by->id]);
            Invoice::whereKey($p->invoice_id)->update(['status' => 'unpaid', 'paid_at' => null]);
        });
    }
}
