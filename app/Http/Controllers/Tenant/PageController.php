<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Support\Owner\Billing;
use App\Support\Owner\DemoData;
use App\Support\Tenant\DemoTenant as T;

class PageController extends Controller
{
    private function invoices(): array
    {
        $today = DemoData::today();
        return array_map(fn ($i) => $i + ['label' => Billing::state($i, $today)['label']], T::invoices());
    }

    public function home()
    {
        $inv = $this->invoices();
        return view('tenant.home', ['profile' => T::profile(), 'rental' => T::rental(), 'invoice' => $inv[0] ?? null, 'payments' => array_slice(T::payments(), 0, 3)]);
    }

    public function bills()
    {
        return view('tenant.bills', ['invoices' => $this->invoices()]);
    }

    public function history()
    {
        return view('tenant.history', ['payments' => T::payments()]);
    }

    public function profile()
    {
        return view('tenant.profile', ['p' => T::profile()]);
    }

    public function rules()
    {
        return view('tenant.rules', ['rules' => T::rules(), 'acked' => DemoData::today()->subMonths(3)]);
    }
}
