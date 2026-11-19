<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\Room;
use App\Support\CurrentProperty;
use App\Support\Finance\Ledger;
use App\Support\Finance\Period;
use App\Support\Settings;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class FinanceController extends Controller
{
    private function pid(): int
    {
        return CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
    }

    public function index(Request $r)
    {
        $pid = $this->pid();
        $p = Period::resolve($r);
        $st = Ledger::statement($pid, $p['from'], $p['to']);

        $tab = in_array($r->query('tab'), ['masuk', 'keluar'], true) ? $r->query('tab') : 'semua';
        $q = trim((string) $r->query('q'));
        $rows = $st['entries']
            ->when($tab === 'masuk', fn ($c) => $c->filter(fn ($x) => $x['in'] > 0))
            ->when($tab === 'keluar', fn ($c) => $c->filter(fn ($x) => $x['out'] > 0))
            ->when($q !== '', fn ($c) => $c->filter(fn ($x) => stripos($x['desc'] . ' ' . $x['category'] . ' ' . $x['ref'], $q) !== false))
            ->values();

        return view('owner.finance', [
            'p' => $p, 'st' => $st, 'rows' => $rows, 'tab' => $tab, 'q' => $q, 'categories' => Expense::CATEGORIES,
            'rooms' => Room::orderBy('code')->get(['id', 'code']), 'rid' => (string) Str::uuid(),
            'depositHeld' => Ledger::depositHeld($pid, $p['to']),
            'opening' => ['balance' => (int) Settings::get($pid, 'opening_balance', 0), 'date' => Settings::get($pid, 'opening_date')],
        ]);
    }

    public function storeExpense(Request $r)
    {
        $pid = $this->pid();
        $d = $r->validate([
            'rid' => ['nullable', 'string', 'max:40'],
            'spent_on' => ['required', 'date', 'before_or_equal:' . Carbon::today('Asia/Jakarta')->toDateString()],
            'category' => ['required', Rule::in(Expense::CATEGORIES)],
            'amount' => ['required', 'integer', 'min:1', 'max:1000000000'],
            'method' => ['required', 'in:cash,transfer'],
            'description' => ['nullable', 'string', 'max:200'],
            'room_id' => ['nullable', Rule::exists('rooms', 'id')->where('property_id', $pid)],
            'photo' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);

        // Double click / refresh: the same form can only post once.
        if (! empty($d['rid']) && Expense::where('request_id', $d['rid'])->exists()) {
            return back()->with('ok', 'Pengeluaran ini sudah tercatat.');
        }

        Expense::create([
            'property_id' => $pid, 'request_id' => $d['rid'] ?? null, 'spent_on' => $d['spent_on'], 'category' => $d['category'], 'amount' => $d['amount'],
            'method' => $d['method'], 'description' => $d['description'] ?? null, 'room_id' => $d['room_id'] ?? null,
            'receipt_path' => $r->file('photo')?->store('struk'), 'recorded_by' => $r->user()->id,
        ]);

        return back()->with('ok', $r->boolean('again') ? 'Tersimpan. Silakan isi pengeluaran berikutnya.' : 'Pengeluaran dicatat.')->with('open_form', $r->boolean('again'));
    }

    public function voidExpense(Request $r, Expense $expense)
    {
        $d = $r->validate(['reason' => ['required', 'string', 'max:200']]);
        $n = Expense::whereKey($expense->id)->whereNull('voided_at')->update(['voided_at' => now(), 'void_reason' => $d['reason']]);

        return back()->with($n ? 'ok' : 'err', $n ? 'Pengeluaran dibatalkan (tetap tersimpan di riwayat).' : 'Pengeluaran ini sudah dibatalkan.');
    }

    public function expenseFile(Expense $expense)
    {
        abort_unless($expense->receipt_path && Storage::exists($expense->receipt_path), 404);

        return Storage::response($expense->receipt_path);
    }

    public function opening(Request $r)
    {
        $pid = $this->pid();
        $d = $r->validate(['opening_balance' => ['required', 'integer', 'min:0', 'max:100000000000'], 'opening_date' => ['required', 'date']]);
        Settings::set($pid, 'opening_balance', (string) $d['opening_balance']);
        Settings::set($pid, 'opening_date', Carbon::parse($d['opening_date'])->toDateString());

        return back()->with('ok', 'Saldo awal kas disimpan.');
    }
}
