<?php

namespace App\Http\Controllers\Owner;

use App\Actions\CreateTenantManually;
use App\Actions\EndRental;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Rental;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Support\CurrentProperty;
use App\Support\Invitations;
use App\Support\Owner\Billing;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TenantController extends Controller
{
    private const CURRENT = ['active', 'pending_checkout'];

    private function pid(): int
    {
        return CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
    }

    /** Search by name, co-occupant name, WhatsApp number or room code; tabs: aktif / dipesan / alumni. */
    public function index(Request $r)
    {
        $this->pid();
        $tab = in_array($r->query('tab'), ['aktif', 'dipesan', 'alumni'], true) ? $r->query('tab') : 'aktif';
        $q = trim((string) $r->query('q'));

        $query = Tenant::with(['rentals' => fn ($x) => $x->with('room')]);
        if ($q !== '') {
            $digits = preg_replace('/\D+/', '', $q);
            $query->where(function ($w) use ($q, $digits) {
                $w->where('name', 'like', "%$q%")
                    ->orWhereHas('rentals', fn ($x) => $x->where('co_occupant_name', 'like', "%$q%")->orWhereHas('room', fn ($y) => $y->where('code', 'like', "%$q%")));
                if ($digits !== '') $w->orWhere('phone', 'like', '%' . ltrim($digits, '0') . '%');
            });
        }

        $groups = ['aktif' => [], 'dipesan' => [], 'alumni' => []];
        foreach ($query->orderBy('name')->get() as $t) {
            $cur = $t->rentals->first(fn ($x) => in_array($x->status, self::CURRENT, true));
            $res = $t->rentals->first(fn ($x) => $x->status === 'reserved');
            $key = $cur ? 'aktif' : ($res ? 'dipesan' : 'alumni');
            $groups[$key][] = ['t' => $t, 'r' => $cur ?? $res ?? $t->rentals->first()];
        }

        $rows = collect($groups[$tab])->filter(fn ($x) => $x['r'])->sortBy(
            $tab === 'alumni' ? fn ($x) => -($x['r']->end_date?->timestamp ?? 0) : fn ($x) => $x['r']->room->code, SORT_NATURAL
        )->values();

        // Next unpaid invoice per rental, so the list can show H-2 / due / overdue and the WhatsApp button.
        $invoices = \App\Models\Invoice::where('status', 'unpaid')->whereIn('rental_id', $rows->map(fn ($x) => $x['r']->id)->all())
            ->orderBy('due_date')->get()->groupBy('rental_id')->map->first();

        return view('owner.tenants', ['rows' => $rows, 'tab' => $tab, 'q' => $q, 'counts' => array_map('count', $groups), 'invoices' => $invoices]);
    }

    public function create()
    {
        $this->pid();

        return view('owner.tenant-create', [
            'rooms' => Room::with(['type', 'floor'])->where('status', 'available')->get()->sortBy('code', SORT_NATURAL)->values()
                ->map(fn ($r) => ['id' => $r->id, 'label' => $r->code . ' · ' . $r->floor->name, 'cap' => $r->type->capacity,
                    'rate_one' => (int) $r->type->rate_one, 'rate_two' => (int) ($r->type->rate_two ?? $r->type->rate_one), 'deposit' => (int) $r->type->deposit_amount])->all(),
        ]);
    }

    public function store(Request $r, CreateTenantManually $action)
    {
        $pid = $this->pid();
        $r->merge(['phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('phone')), 'emergency_phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('emergency_phone')),
            'create_account' => $r->boolean('create_account') ? 1 : 0]);
        $phone = 'regex:/^(\+?62|0)8[0-9]{8,12}$/';

        $d = $r->validate([
            'name' => ['required', 'string', 'max:100'], 'name_2' => ['nullable', 'string', 'max:100'], 'phone' => ['required', $phone],
            'room_id' => ['required', Rule::exists('rooms', 'id')->where('property_id', $pid)], 'occupants' => ['required', 'integer', 'in:1,2'],
            'start_date' => ['required', 'date'], 'next_due_date' => ['nullable', 'date'], 'rate' => ['required', 'integer', 'min:0', 'max:100000000'], 'deposit_amount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'id_number' => ['nullable', 'string', 'max:30'], 'birth_place' => ['nullable', 'string', 'max:80'], 'birth_date' => ['nullable', 'date', 'before:today'],
            'address' => ['nullable', 'string', 'max:500'], 'job' => ['nullable', 'string', 'max:80'], 'institution' => ['nullable', 'string', 'max:120'],
            'emergency_name' => ['nullable', 'string', 'max:100'], 'emergency_relation' => ['nullable', 'string', 'max:50'],
            'emergency_phone' => ['nullable', $phone], 'emergency_address' => ['nullable', 'string', 'max:500'], 'create_account' => ['boolean'],
        ]);

        try {
            [$tenant, , $url] = $action->handle($d, $r->user());
        } catch (DomainException $e) {
            return back()->withInput()->withErrors(['room_id' => $e->getMessage()]);
        }

        return redirect()->route('owner.tenants.show', $tenant)->with('ok', 'Penghuni dicatat dan kamar kini Terisi.')
            ->with('activation', $url ? $this->activation($url, $tenant, false) : null);
    }

    public function show(Tenant $tenant)
    {
        $tenant->load(['user', 'rentals.room.floor']);
        $current = $tenant->rentals->first(fn ($x) => in_array($x->status, ['reserved', ...self::CURRENT], true));

        $rentalIds = $tenant->rentals->pluck('id');

        return view('owner.tenant-show', [
            't' => $tenant, 'current' => $current, 'activation' => session('activation'),
            'invoices' => \App\Models\Invoice::with('payment.receipt')->whereIn('rental_id', $rentalIds)->orderByDesc('due_date')->get(),
        ]);
    }

    public function endRental(Request $r, Tenant $tenant, EndRental $action)
    {
        $current = Rental::where('tenant_id', $tenant->id)->whereIn('status', ['reserved', ...self::CURRENT])->latest('id')->firstOrFail();

        $d = $r->validate([
            'end_date' => ['required', 'date', 'after_or_equal:' . $current->start_date->toDateString()],
            'room_status' => ['required', Rule::in(['available', 'maintenance'])],
            'note' => ['nullable', 'string', 'max:200'],
        ]);

        try {
            $action->handle($current, $d['end_date'], $d['room_status'], $d['note'] ?? null, $r->user());
        } catch (DomainException $e) {
            return back()->withErrors(['end_date' => $e->getMessage()]);
        }

        return redirect()->route('owner.tenants.show', $tenant)->with('ok', 'Sewa diakhiri. ' . $tenant->name . ' kini tercatat sebagai alumni dan riwayatnya tetap tersimpan.');
    }

    /** No account yet -> create it + one-time link. Account not activated -> new link. Active -> login address only. */
    public function invitation(Tenant $tenant)
    {
        $user = $tenant->user;

        if (! $user) {
            $user = User::where('phone', $tenant->phone)->first();
            abort_if($user && $user->role !== User::TENANT, 422, 'Nomor WhatsApp ini terdaftar sebagai akun pengelola.');
            $user ??= User::create(['name' => $tenant->name, 'phone' => $tenant->phone, 'role' => User::TENANT]);
            $tenant->update(['user_id' => $user->id]);
        }

        if ($user->password) {
            return back()->with('activation', $this->activation(route('login'), $tenant, true));
        }

        return back()->with('ok', 'Tautan aktivasi dibuat. Tautan sebelumnya tidak berlaku.')->with('activation', $this->activation(Invitations::issue($user), $tenant, false));
    }

    public function idDocument(Tenant $tenant)
    {
        abort_unless($tenant->id_doc_path && Storage::exists($tenant->id_doc_path), 404);

        return Storage::response($tenant->id_doc_path);
    }

    private function activation(string $url, Tenant $tenant, bool $existing): array
    {
        $property = Property::find($this->pid());
        $head = 'Halo ' . $tenant->name . ', ini akun penghuni Anda di ' . $property->name . '. ';
        $msg = $existing
            ? $head . 'Silakan masuk di ' . $url . ' dengan nomor WhatsApp ini dan kata sandi Anda. Terima kasih.'
            : $head . 'Silakan aktifkan akun melalui tautan berikut (berlaku ' . Invitations::DAYS . ' hari): ' . $url . ' Terima kasih.';

        return ['url' => $url, 'existing' => $existing, 'wa' => 'https://wa.me/' . Billing::phone($tenant->phone) . '?text=' . rawurlencode($msg)];
    }
}
