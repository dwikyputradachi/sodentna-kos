<?php

namespace App\Support\Finance;

use App\Models\Expense;
use App\Models\Payment;
use App\Support\BillingSchedule;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Support\Collection;

/**
 * The cash ledger is DERIVED from payments and expenses (no second copy to keep in sync), so reports always
 * reconcile with it and nothing can be posted twice. Cancelled items are listed for audit but never counted.
 * Rent is income; deposits are held money (a liability) and never count as income.
 */
class Ledger
{
    public static function entries(int $pid, Carbon $from, Carbon $to): Collection
    {
        $f = $from->toDateString();
        $t = $to->toDateString();
        $rows = collect();

        $pays = Payment::withoutGlobalScopes()->with([
            'receipt' => fn ($q) => $q->withoutGlobalScopes(),
            'invoice' => fn ($q) => $q->withoutGlobalScopes()->with(['rental' => fn ($r) => $r->withoutGlobalScopes()
                ->with(['tenant' => fn ($x) => $x->withoutGlobalScopes(), 'room' => fn ($x) => $x->withoutGlobalScopes()])]),
        ])->where('property_id', $pid)->whereDate('paid_on', '>=', $f)->whereDate('paid_on', '<=', $t)->get();

        foreach ($pays as $p) {
            $rental = $p->invoice->rental;
            $who = $rental->tenant->name . ' (kamar ' . $rental->room->code . ')';
            $base = ['date' => $p->paid_on, 'ref' => $p->receipt?->number, 'url' => $p->receipt?->url(), 'method' => $p->method, 'void' => (bool) $p->voided_at, 'void_reason' => $p->void_reason, 'expense_id' => null];

            if ($p->rent_amount > 0) {
                $rows->push($base + ['id' => 'p' . $p->id . 'a', 'payment_id' => $p->id, 'kind' => 'rent', 'category' => 'Pendapatan sewa',
                    'desc' => 'Sewa — ' . $who . ' — ' . $p->invoice->periodLabel(), 'in' => (int) $p->rent_amount, 'out' => 0]);
            }
            if ($p->deposit_amount > 0) {
                $rows->push($base + ['id' => 'p' . $p->id . 'b', 'payment_id' => $p->rent_amount > 0 ? null : $p->id, 'kind' => 'deposit', 'category' => 'Titipan deposit',
                    'desc' => 'Deposit — ' . $who, 'in' => (int) $p->deposit_amount, 'out' => 0]);
            }
        }

        $exp = Expense::withoutGlobalScopes()->with(['room' => fn ($q) => $q->withoutGlobalScopes()])->where('property_id', $pid)->whereDate('spent_on', '>=', $f)->whereDate('spent_on', '<=', $t)->get();
        foreach ($exp as $e) {
            $rows->push(['date' => $e->spent_on, 'id' => 'e' . $e->id, 'payment_id' => null, 'expense_id' => $e->id, 'kind' => 'expense', 'category' => $e->category,
                'desc' => ($e->description ?: $e->category) . ($e->room ? ' (kamar ' . $e->room->code . ')' : ''), 'ref' => 'EXP-' . str_pad((string) $e->id, 4, '0', STR_PAD_LEFT),
                'url' => null, 'method' => $e->method, 'in' => 0, 'out' => (int) $e->amount, 'void' => (bool) $e->voided_at, 'void_reason' => $e->void_reason, 'has_photo' => (bool) $e->receipt_path]);
        }

        return $rows->sortBy(fn ($r) => sprintf('%010d', $r['date']->timestamp) . $r['id'])->values();
    }

    private static function net(Collection $entries): int
    {
        return (int) $entries->reject(fn ($r) => $r['void'])->sum(fn ($r) => $r['in'] - $r['out']);
    }

    /** @return array{entries:Collection,opening:int,closing:int,totals:array} */
    public static function statement(int $pid, Carbon $from, Carbon $to): array
    {
        $tz = BillingSchedule::TZ;
        $od = Settings::get($pid, 'opening_date');
        $start = $od ? Carbon::parse($od, $tz)->startOfDay() : null;
        $ob = $start ? (int) Settings::get($pid, 'opening_balance', 0) : 0;

        // Nothing before the opening date counts: the opening balance already includes it.
        $effFrom = $start && $from->lt($start) ? $start->copy() : $from->copy();

        if (! $start) $opening = self::net(self::entries($pid, Carbon::create(2000, 1, 1, 0, 0, 0, $tz), $from->copy()->subDay()));
        elseif ($effFrom->gt($start)) $opening = $ob + self::net(self::entries($pid, $start, $effFrom->copy()->subDay()));
        else $opening = $ob;

        $entries = $effFrom->gt($to) ? collect() : self::entries($pid, $effFrom, $to);

        $balance = $opening;
        $entries = $entries->map(function ($r) use (&$balance) {
            if (! $r['void']) $balance += $r['in'] - $r['out'];

            return $r + ['balance' => $balance];
        });

        $live = $entries->reject(fn ($r) => $r['void']);
        $rent = (int) $live->where('kind', 'rent')->sum('in');
        $deposit = (int) $live->where('kind', 'deposit')->sum('in');
        $expense = (int) $live->where('kind', 'expense')->sum('out');
        $net = $rent + $deposit - $expense;

        $methodNet = fn (string $m) => (int) $live->where('method', $m)->sum(fn ($r) => $r['in'] - $r['out']);

        return [
            'entries' => $entries, 'opening' => $opening, 'closing' => $opening + $net,
            'totals' => ['rent' => $rent, 'deposit' => $deposit, 'expense' => $expense, 'in' => $rent + $deposit, 'out' => $expense, 'net' => $net,
                'operating' => $rent - $expense, 'cash' => $methodNet('cash'), 'transfer' => $methodNet('transfer')],
        ];
    }

    /** Deposits held for tenants (received, not yet settled). Refunds arrive with the checkout feature. */
    public static function depositHeld(int $pid, Carbon $upTo): int
    {
        return (int) Payment::withoutGlobalScopes()->where('property_id', $pid)->whereNull('voided_at')->whereDate('paid_on', '<=', $upTo->toDateString())->sum('deposit_amount');
    }
}
