<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Support\Owner\Billing;
use App\Support\Owner\DemoData;
use App\Support\Owner\DemoMore as M;
use App\Support\Site\DemoSite;

class ExtraController extends Controller
{
    public function applications()
    {
        return view('owner.applications', [
            'apps' => M::applications(),
            'caps' => collect(DemoSite::types())->map(fn ($t) => ['cap' => $t['cap'], 'daily' => (bool) $t['daily'], 'name' => $t['name']])->all(),
        ]);
    }

    public function inventory()
    {
        $items = array_map(fn ($i) => $i + ['st' => $i['stock'] === 0 ? 'Habis' : ($i['stock'] < $i['min'] ? 'Menipis' : 'Aman')], M::inventory());
        return view('owner.inventory', [
            'items' => $items,
            'low' => count(array_filter($items, fn ($i) => $i['st'] !== 'Aman')),
            'value' => array_sum(array_map(fn ($i) => $i['stock'] * $i['cost'], $items)),
            'moves' => M::movements(),
        ]);
    }

    public function settings()
    {
        $inv = DemoData::invoices()[0];
        return view('owner.settings', [
            'types' => DemoSite::types(),
            'sample' => [
                'reminder' => Billing::message('reminder', $inv, DemoData::PROPERTY),
                'due' => Billing::message('due', $inv, DemoData::PROPERTY),
                'overdue' => Billing::message('overdue', $inv, DemoData::PROPERTY),
            ],
            'decisions' => M::decisions(),
        ]);
    }
}
