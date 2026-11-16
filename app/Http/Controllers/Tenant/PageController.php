<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Actions\GenerateInvoices;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Rental;
use App\Models\RentalApplication;
use App\Models\Tenant;
use App\Support\InvoiceState;
use App\Support\Owner\Billing;
use App\Support\Owner\DemoData;
use App\Support\Tenant\DemoTenant;
use Illuminate\Http\Request;

/** Every query is tied to the signed-in user's own tenant record; ids from the browser are never trusted. */
class PageController extends Controller
{
    private function tenant(): ?Tenant
    {
        return Tenant::where('user_id', auth()->id())->latest('id')->first();
    }

    private function rental(Tenant $t): ?Rental
    {
        return Rental::with(['room.floor', 'room.type'])->where('tenant_id', $t->id)
            ->whereIn('status', ['reserved', 'active', 'pending_checkout'])->latest('id')->first();
    }

    /** First payment = first month's rent + deposit (monthly) or the whole stay (daily). */
    private function firstPayment(?Rental $r): ?array
    {
        if (! $r) return null;

        if ($r->mode === 'daily') {
            $nights = max(1, (int) $r->start_date->diffInDays($r->end_date));

            return ['rent' => $r->rate * $nights, 'deposit' => null, 'total' => $r->rate * $nights, 'label' => $nights . ' malam × tarif harian'];
        }

        return ['rent' => $r->rate, 'deposit' => $r->deposit_amount, 'total' => $r->rate + $r->deposit_amount, 'label' => 'Sewa bulan pertama'];
    }

    private function page(string $view, array $data = [])
    {
        $t = $this->tenant();
        if (! $t) return view('tenant.empty');
        $r = $this->rental($t);

        app(GenerateInvoices::class)->run($t->property_id); // keeps upcoming bills fresh even without the scheduler
        $rentalIds = Rental::where('tenant_id', $t->id)->pluck('id');
        $invoices = Invoice::with('payment.receipt')->whereIn('rental_id', $rentalIds)->orderByDesc('due_date')->get()->map(fn ($i) => ['i' => $i, 's' => InvoiceState::of($i)]);
        $next = $invoices->filter(fn ($x) => $x['i']->status === 'unpaid')->sortBy(fn ($x) => $x['i']->due_date)->first();
        $payments = Payment::with(['receipt', 'invoice'])->whereIn('rental_id', $rentalIds)->orderByDesc('paid_on')->orderByDesc('id')->get();

        return view($view, $data + ['tenant' => $t, 'rental' => $r, 'first' => $this->firstPayment($r), 'invoices' => $invoices, 'next' => $next, 'payments' => $payments]);
    }

    public function home()
    {
        return $this->page('tenant.home');
    }

    public function bills()
    {
        return $this->page('tenant.bills');
    }

    public function history()
    {
        return $this->page('tenant.history');
    }

    public function profile()
    {
        return $this->page('tenant.profile', ['user' => auth()->user()]);
    }

    public function rules()
    {
        $t = $this->tenant();
        $acked = $t ? RentalApplication::where('tenant_id', $t->id)->latest('id')->value('consented_at') : null;

        return view('tenant.rules', ['rules' => DemoTenant::rules(), 'acked' => $acked ? \Illuminate\Support\Carbon::parse($acked) : null]);
    }

    public function updateProfile(Request $r)
    {
        $t = $this->tenant() ?? abort(404);
        $r->merge(['emergency_phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('emergency_phone'))]);

        $d = $r->validate([
            'email' => ['nullable', 'email', 'max:150', 'unique:users,email,' . auth()->id()],
            'emergency_name' => ['required', 'string', 'max:100'],
            'emergency_relation' => ['required', 'string', 'max:50'],
            'emergency_phone' => ['required', 'regex:/^(\+?62|0)8[0-9]{8,12}$/'],
            'emergency_address' => ['nullable', 'string', 'max:500'],
        ]);

        // Name, identity number and WhatsApp (the login) are only changed by the manager.
        $t->update([
            'emergency_name' => $d['emergency_name'], 'emergency_relation' => $d['emergency_relation'],
            'emergency_phone' => Billing::phone($d['emergency_phone']), 'emergency_address' => $d['emergency_address'] ?? null,
        ]);
        auth()->user()->update(['email' => $d['email'] ?? null]);

        return back()->with('ok', 'Profil diperbarui.');
    }

    public function updatePassword(Request $r)
    {
        $d = $r->validateWithBag('password', [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        auth()->user()->forceFill(['password' => $d['password']])->save();

        return back()->with('ok_password', 'Kata sandi diganti.');
    }
}
