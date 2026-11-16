<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\RoomType;
use App\Support\CurrentProperty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RoomController extends Controller
{
    private function pid(): int
    {
        return CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
    }

    public function index()
    {
        $this->pid();

        return view('owner.rooms', [
            'floors' => Floor::with('rooms.type')->orderBy('sort_order')->get(),
            'types' => RoomType::orderBy('name')->get(),
            'counts' => Room::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function storeFloor(Request $r)
    {
        $pid = $this->pid();
        $d = $r->validate(['name' => ['required', 'string', 'max:40', Rule::unique('floors', 'name')->where('property_id', $pid)]]);
        Floor::create($d + ['property_id' => $pid, 'sort_order' => (int) Floor::max('sort_order') + 1]);

        return back()->with('ok', 'Lantai ditambahkan.');
    }

    private function typeData(Request $r, ?RoomType $ignore = null): array
    {
        $pid = $this->pid();
        $r->merge(['allows_daily' => $r->boolean('allows_daily') ? 1 : 0]);

        $d = $r->validate([
            'name' => ['required', 'string', 'max:60', Rule::unique('room_types', 'name')->where('property_id', $pid)->ignore($ignore?->id)],
            'size' => ['nullable', 'string', 'max:30'],
            'capacity' => ['required', 'integer', 'in:1,2'],
            'rate_one' => ['required', 'integer', 'min:0', 'max:100000000'],
            'rate_two' => ['nullable', 'required_if:capacity,2', 'integer', 'min:0', 'max:100000000'],
            'deposit_amount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'allows_daily' => ['boolean'],
            'daily_rate' => ['nullable', 'required_if:allows_daily,1', 'integer', 'min:0', 'max:100000000'],
        ]);

        if ((int) $d['capacity'] === 1) $d['rate_two'] = null;
        if (! $d['allows_daily']) $d['daily_rate'] = null;

        return $d;
    }

    public function storeType(Request $r)
    {
        RoomType::create($this->typeData($r) + ['property_id' => $this->pid()]);

        return back()->with('ok', 'Tipe kamar ditambahkan.');
    }

    public function updateType(Request $r, RoomType $type)
    {
        $type->update($this->typeData($r, $type));

        return back()->with('ok', 'Tipe kamar diperbarui.');
    }

    public function storeRoom(Request $r)
    {
        $pid = $this->pid();
        $d = $r->validate([
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Za-z0-9\-]+$/', Rule::unique('rooms', 'code')->where('property_id', $pid)],
            'floor_id' => ['required', Rule::exists('floors', 'id')->where('property_id', $pid)],
            'room_type_id' => ['required', Rule::exists('room_types', 'id')->where('property_id', $pid)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        Room::create($d + ['property_id' => $pid, 'status' => 'available']);

        return back()->with('ok', 'Kamar ditambahkan.');
    }

    public function show(Room $room)
    {
        $room->load(['floor', 'type', 'logs.user']);

        return view('owner.room-show', [
            'room' => $room,
            'floors' => Floor::orderBy('sort_order')->get(),
            'types' => RoomType::orderBy('name')->get(),
            'rentals' => \App\Models\Rental::with('tenant')->where('room_id', $room->id)->orderByDesc('start_date')->orderByDesc('id')->get(),
        ]);
    }

    public function updateRoom(Request $r, Room $room)
    {
        $pid = $this->pid();
        $d = $r->validate([
            'floor_id' => ['required', Rule::exists('floors', 'id')->where('property_id', $pid)],
            'room_type_id' => ['required', Rule::exists('room_types', 'id')->where('property_id', $pid)],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);
        $room->update($d);

        return back()->with('ok', 'Data kamar diperbarui.');
    }

    public function status(Request $r, Room $room)
    {
        $d = $r->validate([
            'status' => ['required', Rule::in(array_keys(Room::STATUSES))],
            'note' => ['nullable', 'string', 'max:255'],
        ]);

        // Only write (and log) when the status really changes: safe against double clicks.
        if ($d['status'] !== $room->status) {
            DB::transaction(function () use ($room, $d) {
                $from = $room->status;
                $room->update(['status' => $d['status']]);
                RoomStatusLog::create([
                    'property_id' => $room->property_id, 'room_id' => $room->id, 'from_status' => $from,
                    'to_status' => $d['status'], 'note' => $d['note'] ?? null, 'user_id' => auth()->id(),
                ]);
            });
        }

        return back()->with('ok', 'Status kamar diperbarui.');
    }
}
