<?php

namespace App\Http\Controllers\Owner;

use App\Actions\GenerateInvoices;
use App\Actions\RecordPayment;
use App\Actions\VoidPayment;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\MessageLog;
use App\Models\Payment;
use App\Models\Property;
use App\Support\BillingSchedule;
use App\Support\CurrentProperty;
use App\Support\InvoiceState;
use App\Support\Owner\Billing;
use App\Support\Whatsapp\AutoMessenger;
use App\Support\Whatsapp\Messages;
use Carbon\Carbon;
use DomainException;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    private function pid(): int
    {
        return CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
    }

    public function index(Request $r, GenerateInvoices $generator)
    {
        $pid = $this->pid();
        $generator->run($pid);

        $tab = in_array($r->query('tab'), ['perhatian', 'belum', 'lunas', 'semua'], true) ? $r->query('tab') : 'perhatian';
        $q = trim((string) $r->query('q'));
        $today = Carbon::today(BillingSchedule::TZ);

        $query = Invoice::with(['rental.tenant', 'rental.room', 'payment.receipt'])->orderBy('due_date');
        if ($q !== '') {
            $query->where(fn ($w) => $w->where('number', 'like', "%$q%")
                ->orWhereHas('rental.tenant', fn ($x) => $x->where('name', 'like', "%$q%"))
                ->orWhereHas('rental.room', fn ($x) => $x->where('code', 'like', "%$q%")));
        }

        $all = $query->get()->map(fn ($i) => ['i' => $i, 's' => InvoiceState::of($i, $today)]);
        $groups = [
            'perhatian' => $all->filter(fn ($x) => $x['i']->status === 'unpaid' && $x['s']['action'])->values(),
            'belum' => $all->filter(fn ($x) => $x['i']->status === 'unpaid' && ! $x['s']['action'])->values(),
            'lunas' => $all->filter(fn ($x) => $x['i']->status === 'paid')->sortByDesc(fn ($x) => $x['i']->paid_at)->values(),
            'semua' => $all->sortByDesc(fn ($x) => $x['i']->due_date)->values(),
        ];

        $month = $all->filter(fn ($x) => $x['i']->due_date->format('Y-m') === $today->format('Y-m') && $x['i']->status !== 'void');
        $stats = [
            'total' => $month->sum(fn ($x) => $x['i']->total_amount),
            'paid' => $month->where('i.status', 'paid')->sum(fn ($x) => $x['i']->total_amount),
            'open' => $all->where('i.status', 'unpaid')->sum(fn ($x) => $x['i']->total_amount),
            'late' => $all->filter(fn ($x) => $x['s']['key'] === 'overdue')->sum(fn ($x) => $x['i']->total_amount),
        ];

        $last = MessageLog::whereIn('invoice_id', $all->pluck('i.id'))->orderBy('id')->get()->groupBy('invoice_id')
            ->map(fn ($g) => $g->groupBy('kind')->map(fn ($k) => $k->last()));

        return view('owner.billing', ['rows' => $groups[$tab], 'tab' => $tab, 'q' => $q, 'counts' => collect($groups)->map->count()->all(), 'stats' => $stats, 'last' => $last, 'today' => $today]);
    }

    public function pay(Request $r, Invoice $invoice, RecordPayment $action)
    {
        $d = $r->validate([
            'paid_on' => ['required', 'date', 'before_or_equal:' . Carbon::today(BillingSchedule::TZ)->toDateString()],
            'method' => ['required', 'in:cash,transfer'],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $payment = $action->handle($invoice, $d, $r->user());
        } catch (DomainException $e) {
            return back()->withErrors(['paid_on' => $e->getMessage()]);
        }

        // Automatic receipt (only when the owner switched the bot on). Never breaks the payment itself.
        $auto = null;
        try {
            $auto = app(AutoMessenger::class)->receiptFor($payment);
        } catch (\Throwable $e) {
            report($e);
        }

        return back()->with('ok', 'Pembayaran dicatat dan kuitansi dibuat. ' . match ($auto) {
            true => 'Kuitansi terkirim otomatis lewat WhatsApp.',
            false => 'Kuitansi gagal dikirim otomatis, kirim manual dengan tombol.',
            default => 'Anda bisa mengirim kuitansi lewat WhatsApp.',
        });
    }

    /** Wrong bill? Cancel it (kept in history, never deleted). The same period cannot be regenerated. */
    public function void(Request $r, Invoice $invoice)
    {
        $d = $r->validate(['reason' => ['required', 'string', 'max:200']]);

        $n = Invoice::whereKey($invoice->id)->where('status', 'unpaid')->update(['status' => 'void', 'void_reason' => $d['reason']]);

        return back()->with($n ? 'ok' : 'err', $n ? 'Tagihan dibatalkan.' : 'Hanya tagihan yang belum dibayar yang bisa dibatalkan.');
    }

    /** Wrong payment? Cancel it (kept in the books for audit); the invoice becomes payable again. */
    public function voidPayment(Request $r, Payment $payment, VoidPayment $action)
    {
        $d = $r->validate(['reason' => ['required', 'string', 'max:200']]);

        try {
            $action->handle($payment, $d['reason'], $r->user());
        } catch (DomainException $e) {
            return back()->with('err', $e->getMessage());
        }

        return back()->with('ok', 'Pembayaran dibatalkan. Tagihan kembali belum lunas dan bisa dicatat ulang; kuitansi lama ditandai dibatalkan.');
    }

    /**
     * Semi-automatic: logs the action, then sends the owner to WhatsApp with the message prefilled.
     * The owner still presses Send; "opened" is not delivery.
     */
    public function send(Request $r, Invoice $invoice, string $kind)
    {
        $invoice->load(['rental.tenant', 'rental.room', 'payment.receipt']);
        $property = Property::findOrFail($invoice->property_id);

        if ($kind === 'receipt') {
            $receipt = $invoice->payment?->receipt;
            abort_unless($invoice->status === 'paid' && $receipt, 422, 'Kuitansi belum tersedia.');
            $body = Messages::receipt($receipt, $invoice, $property->name);
        } else {
            abort_unless($invoice->status === 'unpaid' && InvoiceState::of($invoice)['action'] === $kind, 422, 'Tahap pengingat tidak sesuai dengan status tagihan.');
            $body = Messages::invoice($kind, $invoice, $property->name);
        }

        $phone = Billing::phone($invoice->rental->tenant->phone);
        MessageLog::create([
            'property_id' => $invoice->property_id, 'tenant_id' => $invoice->rental->tenant_id, 'invoice_id' => $invoice->id, 'kind' => $kind,
            'channel' => 'whatsapp_link', 'status' => 'opened', 'to_phone' => $phone, 'body' => $body, 'user_id' => $r->user()->id,
        ]);

        return redirect()->away('https://wa.me/' . $phone . '?text=' . rawurlencode($body));
    }
}
