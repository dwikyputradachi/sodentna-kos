<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\RentalApplication as App;
use App\Models\RoomType;
use App\Support\Owner\Billing;
use App\Support\SiteProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    public function create(Request $r)
    {
        $types = PageController::types();
        $q = (int) $r->query('kamar');

        return view('site.apply', ['types' => $types, 'sel' => isset($types[$q]) ? $q : array_key_first($types), 'mode' => $r->query('mode') === 'harian' ? 'daily' : 'monthly']);
    }

    public function store(Request $r)
    {
        $p = SiteProperty::get();
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $r->merge(['phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('phone')), 'emergency_phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('emergency_phone'))]);
        $phone = 'regex:/^(\+?62|0)8[0-9]{8,12}$/';

        $d = $r->validate([
            'room_type_id' => ['required', Rule::exists('room_types', 'id')->where('property_id', $p->id)],
            'mode' => ['required', 'in:monthly,daily'],
            'occupants' => ['required', 'integer', 'in:1,2'],
            'start_date' => ['required', 'date', 'after_or_equal:' . $today],
            'end_date' => ['nullable', 'required_if:mode,daily', 'date', 'after:start_date'],
            'payment_method' => ['required', 'in:transfer,cash'],
            'name' => ['required', 'string', 'max:100'],
            'birth_place' => ['required', 'string', 'max:80'],
            'birth_date' => ['required', 'date', 'before:today'],
            'id_number' => ['required', 'regex:/^[A-Za-z0-9]{6,30}$/'],
            'phone' => ['required', $phone],
            'job' => ['required', 'string', 'max:80'],
            'institution' => ['required', 'string', 'max:120'],
            'address' => ['required', 'string', 'max:500'],
            'emergency_name' => ['required', 'string', 'max:100'],
            'emergency_relation' => ['required', 'string', 'max:50'],
            'emergency_phone' => ['required', $phone],
            'emergency_address' => ['required', 'string', 'max:500'],
            'id_doc' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
            'consent_rules' => ['accepted'],
            'consent_data' => ['accepted'],
            'couple_ack' => ['accepted_if:occupants,2'],
        ]);

        $type = RoomType::withoutGlobalScopes()->find($d['room_type_id']);
        if ($d['occupants'] > $type->capacity) return back()->withInput()->withErrors(['occupants' => 'Tipe kamar ini hanya untuk ' . $type->capacity . ' orang.']);
        if ($d['mode'] === 'daily' && (! $type->allows_daily || $d['occupants'] > 1)) {
            return back()->withInput()->withErrors(['mode' => 'Sewa harian hanya untuk 1 orang dan pada tipe kamar yang menyediakannya.']);
        }

        $phoneN = Billing::phone($d['phone']);

        // Same person + same room type with a pending application: do not create a duplicate.
        $app = App::withoutGlobalScopes()->where('property_id', $p->id)->where('phone', $phoneN)->where('room_type_id', $type->id)->where('status', 'pending')->first();
        $existing = (bool) $app;

        $app ??= App::create([
            'property_id' => $p->id, 'code' => $this->newCode(), 'room_type_id' => $type->id, 'mode' => $d['mode'], 'occupants' => $d['occupants'],
            'start_date' => $d['start_date'], 'end_date' => $d['mode'] === 'daily' ? $d['end_date'] : null, 'payment_method' => $d['payment_method'],
            'name' => $d['name'], 'phone' => $phoneN, 'birth_place' => $d['birth_place'], 'birth_date' => $d['birth_date'], 'address' => $d['address'],
            'id_number' => $d['id_number'], 'job' => $d['job'], 'institution' => $d['institution'], 'emergency_name' => $d['emergency_name'],
            'emergency_relation' => $d['emergency_relation'], 'emergency_phone' => Billing::phone($d['emergency_phone']), 'emergency_address' => $d['emergency_address'],
            'id_doc_path' => $r->file('id_doc')->store('identitas'), // private disk, random safe filename
            'consented_at' => now(), 'status' => 'pending',
        ]);

        return redirect()->route('site.apply')->with('done', ['code' => $app->code, 'existing' => $existing]);
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

        return view('site.status', ['result' => ['code' => $app->code, 'status' => $app->status, 'reason' => $app->reject_reason]]);
    }

    private function newCode(): string
    {
        do {
            $code = 'SDN-' . random_int(10000, 99999);
        } while (App::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }
}
