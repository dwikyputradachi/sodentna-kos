<?php

namespace App\Actions;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Receipt;
use App\Models\User;
use Carbon\Carbon;
use DomainException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/** One transaction: payment + invoice paid + receipt. Double clicks cannot create a second payment. */
class RecordPayment
{
    public function handle(Invoice $invoice, array $d, User $by): Payment
    {
        return DB::transaction(function () use ($invoice, $d, $by) {
            $inv = Invoice::lockForUpdate()->findOrFail($invoice->id);
            if ($inv->status === 'paid') throw new DomainException('Tagihan ini sudah lunas.');
            if ($inv->status === 'void') throw new DomainException('Tagihan ini sudah dibatalkan.');

            $paidOn = Carbon::parse($d['paid_on'], 'Asia/Jakarta');

            $payment = Payment::create([
                'property_id' => $inv->property_id, 'invoice_id' => $inv->id, 'rental_id' => $inv->rental_id, 'amount' => $inv->total_amount,
                'rent_amount' => $inv->rent_amount, 'deposit_amount' => $inv->deposit_amount, 'method' => $d['method'], 'paid_on' => $paidOn,
                'recorded_by' => $by->id, 'note' => $d['note'] ?? null,
            ]);
            $inv->update(['status' => 'paid', 'paid_at' => now()]);

            Receipt::create([
                'property_id' => $inv->property_id, 'payment_id' => $payment->id, 'number' => sprintf('%03d/KSA/%s', $payment->id, $paidOn->format('m/Y')),
                'token' => Str::random(40), 'issued_on' => $paidOn,
            ]);

            return $payment;
        });
    }
}
