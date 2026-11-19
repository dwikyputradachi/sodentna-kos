<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Support\CurrentProperty;
use App\Support\Finance\Ledger;
use App\Support\Finance\Period;
use App\Support\Finance\SimpleXlsx;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    private function data(Request $r): array
    {
        $pid = CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
        $p = Period::resolve($r);
        $st = Ledger::statement($pid, $p['from'], $p['to']);
        $live = $st['entries']->reject(fn ($x) => $x['void']);

        return [
            'p' => $p, 'st' => $st, 'property' => Property::findOrFail($pid), 'live' => $live,
            'byCategory' => $live->where('kind', 'expense')->groupBy('category')->map(fn ($g) => (int) $g->sum('out'))->sortDesc(),
            'depositHeld' => Ledger::depositHeld($pid, $p['to']),
        ];
    }

    public function show(Request $r)
    {
        return view('owner.reports', $this->data($r));
    }

    public function print(Request $r)
    {
        return view('owner.report-print', $this->data($r));
    }

    public function excel(Request $r)
    {
        $d = $this->data($r);
        $t = $d['st']['totals'];

        $summary = [
            [['b', 'Laporan Keuangan — ' . $d['property']->name]],
            ['Periode', $d['p']['label']],
            [],
            [['b', 'Ringkasan operasional']],
            ['Pendapatan sewa', $t['rent']],
            ['Pengeluaran', $t['expense']],
            ['Hasil operasional', $t['operating']],
            [],
            [['b', 'Arus kas']],
            ['Saldo awal', $d['st']['opening']],
            ['Kas masuk — sewa', $t['rent']],
            ['Kas masuk — titipan deposit', $t['deposit']],
            ['Kas keluar — pengeluaran', $t['expense']],
            ['Saldo akhir', $d['st']['closing']],
            [],
            [['b', 'Titipan deposit tercatat sampai akhir periode'], $d['depositHeld']],
            [],
            [['b', 'Pengeluaran per kategori']],
        ];
        foreach ($d['byCategory'] as $cat => $amount) $summary[] = [$cat, (int) $amount];

        $ledger = [[['b', 'Tanggal'], ['b', 'Keterangan'], ['b', 'Kategori'], ['b', 'Referensi'], ['b', 'Metode'], ['b', 'Masuk'], ['b', 'Keluar'], ['b', 'Saldo']]];
        foreach ($d['live'] as $e) {
            $ledger[] = [$e['date'], $e['desc'], $e['category'], (string) $e['ref'], $e['method'] === 'cash' ? 'Tunai' : 'Transfer', $e['in'], $e['out'], $e['balance']];
        }

        return (new SimpleXlsx())
            ->addSheet('Ringkasan', $summary, [38, 18])
            ->addSheet('Mutasi Kas', $ledger, [12, 58, 26, 18, 10, 14, 14, 14])
            ->download('laporan-keuangan-' . $d['p']['key'] . '.xlsx');
    }
}
