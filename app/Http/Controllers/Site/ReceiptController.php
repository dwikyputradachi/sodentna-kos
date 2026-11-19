<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Property;
use App\Models\Receipt;

/** Public receipt page behind an unguessable token. Shows no phone number or identity number. */
class ReceiptController extends Controller
{
    public function show(string $token)
    {
        $receipt = Receipt::withoutGlobalScopes()->where('token', $token)->firstOrFail();
        $payment = Payment::withoutGlobalScopes()->findOrFail($receipt->payment_id);
        $invoice = Invoice::withoutGlobalScopes()->with(['rental' => fn ($q) => $q->withoutGlobalScopes()->with(['tenant' => fn ($t) => $t->withoutGlobalScopes(), 'room' => fn ($x) => $x->withoutGlobalScopes()])])->findOrFail($payment->invoice_id);

        return view('site.receipt', ['r' => $receipt, 'p' => $payment, 'i' => $invoice, 'property' => Property::findOrFail($receipt->property_id), 'voided' => (bool) $payment->voided_at]);
    }
}
