<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\WaitingListEntry as Entry;
use App\Support\Owner\Billing;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class WaitlistController extends Controller
{
    /** v1 runs one property; public queries never rely on the signed-in user's scope. */
    private function property(): Property
    {
        return Property::where('is_active', true)->orderBy('id')->firstOrFail();
    }

    public function show()
    {
        $p = $this->property();

        return view('site.waitlist', [
            'types' => RoomType::withoutGlobalScopes()->where('property_id', $p->id)->orderBy('name')->get(),
            'free' => Room::withoutGlobalScopes()->where('property_id', $p->id)->where('status', 'available')->count(),
        ]);
    }

    public function store(Request $r)
    {
        $p = $this->property();
        $today = Carbon::today('Asia/Jakarta')->toDateString();
        $r->merge(['phone' => preg_replace('/[\s\-\.]/', '', (string) $r->input('phone'))]);

        $d = $r->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'regex:/^(\+?62|0)8[0-9]{8,12}$/'],
            'room_type_id' => ['nullable', Rule::exists('room_types', 'id')->where('property_id', $p->id)],
            'occupants' => ['required', 'integer', 'in:1,2'],
            'desired_start' => ['required', 'date', 'after_or_equal:' . $today],
            'notes' => ['nullable', 'string', 'max:300'],
            'couple_ack' => ['accepted_if:occupants,2'],
        ]);

        if ($d['room_type_id'] ?? null) {
            $type = RoomType::withoutGlobalScopes()->find($d['room_type_id']);
            if ((int) $d['occupants'] > $type->capacity) {
                return back()->withInput()->withErrors(['occupants' => 'Tipe kamar ini hanya untuk ' . $type->capacity . ' orang.']);
            }
        }

        $phone = Billing::phone($d['phone']);
        $typeId = $d['room_type_id'] ?? null;

        // Same person, same room type, still active -> reuse instead of queueing twice.
        $entry = Entry::withoutGlobalScopes()->where('property_id', $p->id)->where('phone', $phone)->where('room_type_id', $typeId)
            ->whereIn('status', ['waiting', 'offered'])->first();
        $existing = (bool) $entry;

        $entry ??= Entry::create([
            'property_id' => $p->id, 'code' => $this->newCode(), 'name' => $d['name'], 'phone' => $phone, 'room_type_id' => $typeId,
            'occupants' => $d['occupants'], 'desired_start' => $d['desired_start'], 'notes' => $d['notes'] ?? null, 'status' => 'waiting',
        ]);

        return redirect()->route('site.waitlist')->with('done', ['code' => $entry->code, 'position' => Entry::positionOf($entry), 'existing' => $existing]);
    }

    private function newCode(): string
    {
        do {
            $code = 'DT-' . random_int(10000, 99999);
        } while (Entry::withoutGlobalScopes()->where('code', $code)->exists());

        return $code;
    }
}
