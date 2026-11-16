<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Property;
use App\Models\RentalApplication as App;
use App\Models\Room;
use App\Models\Tenant;
use App\Models\User;
use App\Support\Owner\Billing;
use App\Support\Site\Plans;
use App\Support\SiteProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    /** Floors with rooms + the pricing/eligibility data the form needs. Status only, never tenant data. */
    private function floors(Property $p): array
    {
        return Floor::withoutGlobalScopes()->where('property_id', $p->id)->orderBy('sort_order')->get()->map(fn ($f) => [
            'name' => $f->name,
            'rooms' => Room::withoutGlobalScopes()->with(['type' => fn ($q) => $q->withoutGlobalScopes()])->where('floor_id', $f->id)->orderBy('code')->get()
                ->map(fn ($r) => [
                    'id' => $r->id, 'code' => $r->code, 'available' => $r->status === 'available', 'statusLabel' => $r->statusLabel(),
                    'cap' => (int) $r->type->capacity, 'rate_one' => (int) $r->type->rate_one, 'rate_two' => $r->type->rate_two ? (int) $r->type->rate_two : null,
                    'allows_daily' => (bool) $r->type->allows_daily, 'daily_rate' => $r->type->daily_rate ? (int) $r->type->daily_rate : null,
                    'deposit' => (int) $r->type->deposit_amount, 'type' => $r->type->name,
                ])->all(),
        ])->all();
    }

    public function create(Request $r)
    {
        $p = SiteProperty::find();
        $plans = Plans::for($p);
        $floors = $p ? $this->floors($p) : [];
        $rooms = collect($floors)->pluck('rooms')->flatten(1)->keyBy('id');

        $selRoom = (int) $r->query('kamar');
        if (! ($rooms[$selRoom]['available'] ?? false)) $selRoom = null;

        $sel = $r->query('paket');
        if (! isset($plans[$sel]) || ! $plans[$sel]['available']) {
            $sel = collect($plans)->first(fn ($x) => $x['available'])['key'] ?? 'one';
        }

        return view('site.apply', [
            'plans' => $plans, 'sel' => $sel, 'floors' => $floors, 'rooms' => $rooms, 'selRoom' => $selRoom,
            'any' => $rooms->contains('available', true),
        ]);
    }

    public function store(Request $r)
    {
        $p = SiteProperty::get();
        $plans = Plans::for($p);
        $key = (string) $r->input('plan');

        if (! isset($plans[$key])) {
            return back()->withInput()->withErrors(['plan' => 'Pilihan sewa tidak valid.']);
        }

        $pl = $plans[$key];
        $monthly = $pl['mode'] === 'monthly';
        $two = $key === 'two';
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $phoneRule = 'regex:/^(\+?62|0)8[0-9]{8,12}$/';
        $strip = fn ($v) => preg_replace('/[\s\-\.]/', '', (string) $v);
        $r->merge(['phone' => $strip($r->input('phone')), 'emergency_phone' => $strip($r->input('emergency_phone'))]);

        $rules = [
            'plan' => ['required', 'in:one,two,daily'],
            'room_id' => ['required', Rule::exists('rooms', 'id')->where('property_id', $p->id)],
            'start_date' => ['required', 'date', 'after_or_equal:' . $today],
            'payment_method' => ['required', 'in:transfer,cash'],
            'name' => ['required', 'string', 'max:100'],
            'id_number' => ['required', 'regex:/^[A-Za-z0-9]{6,30}$/'],
            'phone' => ['required', $phoneRule],
            'address' => ['required', 'string', 'max:500'],
            'emergency_name' => ['required', 'string', 'max:100'],
            'emergency_relation' => ['required', 'string', 'max:50'],
            'emergency_phone' => ['required', $phoneRule],
            'id_doc' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'consent_rules' => ['accepted'],
            'consent_data' => ['accepted'],
        ];

        if ($monthly) {
            $rules += [
                'birth_place' => ['required', 'string', 'max:80'],
                'birth_date' => ['required', 'date', 'before:today'],
                'religion' => ['nullable', 'string', 'max:30'],
                'job_choice' => ['required', 'in:Mahasiswa,Karyawan,Lainnya'],
                'job_other' => ['nullable', 'required_if:job_choice,Lainnya', 'string', 'max:80'],
                'institution' => ['required', 'string', 'max:120'],
                'emergency_address' => ['required', 'string', 'max:500'],
            ];
        } else {
            $rules += ['end_date' => ['required', 'date', 'after:start_date'], 'emergency_address' => ['nullable', 'string', 'max:500']];
        }

        if ($two) {
            $rules += [
                'name_2' => ['required', 'string', 'max:100'],
                'id_doc_2' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
                'couple_ack' => ['accepted'],
            ];
        }

        $d = $r->validate($rules);

        // The applicant chooses the room, so check it again on the server: available and suitable for the plan.
        $room = Room::withoutGlobalScopes()->with(['type' => fn ($q) => $q->withoutGlobalScopes()])->findOrFail($d['room_id']);
        $t = $room->type;
        if ($room->status !== 'available') {
            return back()->withInput()->withErrors(['room_id' => 'Kamar ' . $room->code . ' sudah tidak tersedia. Silakan pilih kamar lain.']);
        }
        if ($pl['occupants'] > $t->capacity) {
            return back()->withInput()->withErrors(['room_id' => 'Kamar ' . $room->code . ' hanya untuk ' . $t->capacity . ' orang.']);
        }
        if (! $monthly && (! $t->allows_daily || ! $t->daily_rate)) {
            return back()->withInput()->withErrors(['room_id' => 'Kamar ' . $room->code . ' tidak tersedia untuk sewa harian.']);
        }

        $phoneN = Billing::phone($d['phone']);

        // Same person + same room with a pending application: do not create a duplicate.
        $app = App::withoutGlobalScopes()->where('property_id', $p->id)->where('phone', $phoneN)->where('room_id', $room->id)->where('status', 'pending')->first();
        $existing = (bool) $app;

        $app ??= App::create([
            'property_id' => $p->id, 'code' => $this->newCode(), 'room_id' => $room->id, 'room_type_id' => $room->room_type_id,
            'mode' => $pl['mode'], 'occupants' => $pl['occupants'],
            'start_date' => $d['start_date'], 'end_date' => $monthly ? null : $d['end_date'], 'payment_method' => $d['payment_method'],
            'name' => $d['name'], 'name_2' => $two ? $d['name_2'] : null, 'phone' => $phoneN, 'address' => $d['address'], 'id_number' => $d['id_number'],
            'birth_place' => $d['birth_place'] ?? null, 'birth_date' => $d['birth_date'] ?? null, 'religion' => $d['religion'] ?? null,
            'job' => $monthly ? ($d['job_choice'] === 'Lainnya' ? $d['job_other'] : $d['job_choice']) : null, 'institution' => $d['institution'] ?? null,
            'emergency_name' => $d['emergency_name'], 'emergency_relation' => $d['emergency_relation'],
            'emergency_phone' => Billing::phone($d['emergency_phone']), 'emergency_address' => $d['emergency_address'] ?? null,
            'id_doc_path' => $r->file('id_doc')->store('identitas'), // private disk, random safe filename
            'id_doc_2_path' => $two && $r->hasFile('id_doc_2') ? $r->file('id_doc_2')->store('identitas') : null,
            'consented_at' => now(), 'status' => 'pending',
        ]);

        return redirect()->route('site.apply')->with('done', [
            'code' => $app->code, 'existing' => $existing, 'last4' => substr($phoneN, -4), 'phone' => $phoneN, 'plan' => $pl['label'] . ' · Kamar ' . $room->code,
        ]);
    }

    public function status()
    {
        return view('site.status', ['result' => null]);
    }

    public function check(Request $r)
    {
        $d = $r->validate(['code' => ['required', 'string', 'max:20'], 'last4' => ['required', 'digits:4']]);
        $app = App::withoutGlobalScopes()->where('code', Str::upper(trim($d['code'])))->first();

        // Same generic answer whether the code or the digits are wrong: no enumeration.
        if (! $app || ! str_ends_with($app->phone, $d['last4'])) {
            return view('site.status', ['result' => false]);
        }

        $room = $app->room_id ? Room::withoutGlobalScopes()->find($app->room_id) : null;
        $userId = $app->status === 'approved' && $app->tenant_id ? Tenant::withoutGlobalScopes()->find($app->tenant_id)?->user_id : null;
        $active = $userId ? (bool) User::find($userId)?->password : false;

        return view('site.status', ['result' => ['code' => $app->code, 'status' => $app->status, 'reason' => $app->reject_reason, 'room' => $room?->code, 'active' => $active]]);
    }

    private function newCode(): string
    {
        do {
            $code = 'SDN-' . random_int(10000, 99999);
        } while (App::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }
}
