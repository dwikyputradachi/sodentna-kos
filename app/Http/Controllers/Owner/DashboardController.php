<?php

namespace App\Http\Controllers\Owner;

use App\Actions\GenerateInvoices;
use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\RentalApplication;
use App\Models\Room;
use App\Models\WaitingListEntry;
use App\Support\BillingSchedule;
use App\Support\CurrentProperty;
use App\Support\Finance\Ledger;
use App\Support\InvoiceState;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(GenerateInvoices $generator)
    {
        $pid = CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
        $generator->run($pid);

        $today = Carbon::today(BillingSchedule::TZ);
        $unpaid = Invoice::with(['rental.tenant', 'rental.room'])->where('status', 'unpaid')->orderBy('due_date')->get()
            ->map(fn ($i) => ['i' => $i, 's' => InvoiceState::of($i, $today)]);
        $count = fn (string $key) => $unpaid->where('s.key', $key)->count();
        $rooms = Room::selectRaw('status, count(*) as n')->groupBy('status')->pluck('n', 'status');

        $from = $today->copy()->startOfMonth();
        $st = Ledger::statement($pid, $from, $today->copy()->endOfMonth()->startOfDay());

        return view('owner.dashboard', [
            'queue' => [
                ['H-2 belum bayar', $count('h2'), 'yellow', route('owner.billing')],
                ['Jatuh tempo hari ini', $count('due'), 'yellow', route('owner.billing')],
                ['Terlambat', $count('overdue'), 'red', route('owner.billing')],
                ['Pengajuan sewa baru', RentalApplication::where('status', 'pending')->count(), 'gray', route('owner.applications')],
                ['Daftar tunggu', WaitingListEntry::where('status', 'waiting')->count(), 'gray', route('owner.waitlist')],
                ['Kamar dalam perbaikan', (int) ($rooms['maintenance'] ?? 0), 'red', route('owner.rooms')],
            ],
            'attention' => $unpaid->filter(fn ($x) => $x['s']['action'])->take(6)->values(),
            'rooms' => $rooms, 'st' => $st, 'monthLabel' => \App\Support\Owner\Fmt::date($from, 'F Y'),
            'depositHeld' => Ledger::depositHeld($pid, $today),
            'recent' => $st['entries']->reject(fn ($x) => $x['void'])->reverse()->take(6)->values(),
        ]);
    }
}
