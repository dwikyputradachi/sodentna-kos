<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Support\Owner\Billing;
use App\Support\Owner\DemoData as D;

class PageController extends Controller
{
    /** Invoices enriched with billing state + WhatsApp link. */
    private function invoices(): array
    {
        $today = D::today();
        return array_map(function ($inv) use ($today) {
            $s = Billing::state($inv, $today);
            return $inv + [
                'label' => $s['label'],
                'action' => $s['action'],
                'action_label' => Billing::actionLabel($s['action']),
                'wa' => $s['action'] ? Billing::link($s['action'], $inv, D::PROPERTY) : null,
            ];
        }, D::invoices());
    }

    public function dashboard()
    {
        $inv = $this->invoices();
        $by = fn ($label) => count(array_filter($inv, fn ($i) => $i['label'] === $label));
        return view('owner.dashboard', [
            'queue' => [
                ['H-2 belum bayar', $by('H-2'), 'yellow', 'owner.billing'],
                ['Jatuh tempo hari ini', $by('Jatuh Tempo'), 'yellow', 'owner.billing'],
                ['Terlambat', $by('Terlambat'), 'red', 'owner.billing'],
                ['Bukti menunggu verifikasi', $by('Menunggu Verifikasi'), 'blue', 'owner.billing'],
                ['Pengajuan sewa baru', 2, 'gray', 'owner.applications'],
                ['Kamar dalam perbaikan', D::roomCounts()['Dalam Perbaikan'], 'red', 'owner.maintenance'],
            ],
            'fin' => D::financeTotals(),
            'deposit_held' => array_sum(array_map(fn ($d) => $d['st'] === 'Dikembalikan' ? 0 : $d['init'], D::deposits())),
            'rooms' => D::roomCounts(),
            'ledger' => array_slice(D::ledger(), 0, 5),
            'tickets' => array_slice(D::tickets(), 0, 4),
        ]);
    }

    public function rooms()
    {
        return view('owner.rooms', ['floors' => D::floors(), 'counts' => D::roomCounts()]);
    }

    public function tenants()
    {
        return view('owner.tenants', ['tenants' => D::tenants()]);
    }

    public function billing()
    {
        $inv = $this->invoices();
        return view('owner.billing', [
            'invoices' => $inv,
            'sum' => [
                'total' => array_sum(array_column($inv, 'amount')),
                'paid' => array_sum(array_map(fn ($i) => $i['label'] === 'Lunas' ? $i['amount'] : 0, $inv)),
                'pending' => array_sum(array_map(fn ($i) => $i['label'] === 'Menunggu Verifikasi' ? $i['amount'] : 0, $inv)),
                'late' => array_sum(array_map(fn ($i) => $i['label'] === 'Terlambat' ? $i['amount'] : 0, $inv)),
            ],
        ]);
    }

    public function finance()
    {
        return view('owner.finance', ['fin' => D::financeTotals(), 'ledger' => D::ledger()]);
    }

    public function deposits()
    {
        $rows = D::deposits();
        return view('owner.deposits', [
            'rows' => $rows,
            'held' => array_sum(array_map(fn ($d) => $d['st'] === 'Dikembalikan' ? 0 : $d['init'], $rows)),
            'refunded' => array_sum(array_map(fn ($d) => $d['st'] === 'Dikembalikan' ? $d['refund'] : 0, $rows)),
            'deducted' => array_sum(array_column($rows, 'ded')),
        ]);
    }

    public function reports()
    {
        $fin = D::financeTotals();
        $exp = [];
        foreach (D::ledger() as $l) if ($l['type'] === 'Pengeluaran') $exp[$l['desc']] = ($exp[$l['desc']] ?? 0) + abs($l['amt']);
        return view('owner.reports', ['fin' => $fin, 'exp' => $exp]);
    }

    public function maintenance()
    {
        $t = D::tickets();
        return view('owner.maintenance', [
            'tickets' => $t,
            'open' => count(array_filter($t, fn ($x) => $x['st'] !== 'Selesai')),
            'cost' => array_sum(array_map(fn ($x) => $x['cost'] ?? 0, $t)),
        ]);
    }

    public function documents()
    {
        return view('owner.documents', ['receipts' => D::receipts()]);
    }

    public function soon(string $page)
    {
        $titles = ['applications' => 'Pengajuan Sewa', 'inventory' => 'Persediaan Barang', 'settings' => 'Pengaturan'];
        return view('owner.soon', ['title' => $titles[$page], 'active' => $page]);
    }
}
