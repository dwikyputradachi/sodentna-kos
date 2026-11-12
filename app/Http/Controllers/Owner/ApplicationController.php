<?php

namespace App\Http\Controllers\Owner;

use App\Actions\ApproveApplication;
use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\Rental;
use App\Models\RentalApplication as App;
use App\Models\Room;
use App\Models\RoomStatusLog;
use App\Models\Tenant;
use App\Support\CurrentProperty;
use App\Support\Invitations;
use App\Support\Owner\Billing;
use DomainException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ApplicationController extends Controller
{
    private function pid(): int
    {
        return CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
    }

    public function index(Request $r)
    {
        $this->pid();
        $status = $r->query('status');
        $q = App::with('type')->latest('id');
        if (in_array($status, array_keys(App::STATUSES), true)) $q->where('status', $status);

        return view('owner.applications', [
            'apps' => $q->limit(200)->get(), 'status' => $status,
            'counts' => App::select('status', DB::raw('count(*) as n'))->groupBy('status')->pluck('n', 'status'),
        ]);
    }

    public function show(App $application)
    {
        $application->load(['type', 'tenant.user', 'room']);

        $rooms = Room::with(['type', 'floor'])->where('status', 'available')->get()
            ->filter(fn ($r) => $r->type->capacity >= $application->occupants && ($application->mode !== 'daily' || $r->type->allows_daily))
            ->sortBy(fn ($r) => [$r->room_type_id === $application->room_type_id ? 0 : 1, $r->code])->values();

        return view('owner.application-show', [
            'a' => $application, 'rooms' => $rooms, 'rental' => Rental::where('application_id', $application->id)->first(),
            'activation' => session('activation'),
        ]);
    }

    public function approve(Request $r, App $application, ApproveApplication $action)
    {
        $d = $r->validate(['room_id' => ['required', Rule::exists('rooms', 'id')->where('property_id', $this->pid())]]);

        try {
            [, $url, $user] = $action->handle($application, (int) $d['room_id'], $r->user());
        } catch (DomainException $e) {
            return back()->withErrors(['room_id' => $e->getMessage()]);
        }

        return redirect()->route('owner.applications.show', $application)
            ->with('ok', 'Pengajuan disetujui. Data penghuni dan sewa dibuat, kamar kini Dipesan.')
            ->with('activation', $url ? $this->activation($url, $user->name, $user->phone) : null);
    }

    public function reject(Request $r, App $application)
    {
        $d = $r->validate(['reason' => ['required', 'string', 'max:255']]);

        $n = App::whereKey($application->id)->where('status', 'pending')
            ->update(['status' => 'rejected', 'reject_reason' => $d['reason'], 'reviewed_by' => $r->user()->id, 'reviewed_at' => now()]);

        return back()->with($n ? 'ok' : 'err', $n ? 'Pengajuan ditolak.' : 'Pengajuan ini sudah diproses.');
    }

    public function invitation(App $application)
    {
        $user = $application->tenant?->user;
        abort_unless($user && ! $user->password, 422, 'Akun sudah aktif atau belum dibuat.');

        return back()->with('ok', 'Tautan aktivasi baru dibuat. Tautan sebelumnya tidak berlaku.')
            ->with('activation', $this->activation(Invitations::issue($user), $user->name, $user->phone));
    }

    public function checkIn(App $application)
    {
        DB::transaction(function () use ($application) {
            $rental = Rental::lockForUpdate()->where('application_id', $application->id)->first();
            abort_unless($rental && $rental->status === 'reserved', 422, 'Sewa belum dapat dimulai.');

            $room = Room::lockForUpdate()->findOrFail($rental->room_id);
            $from = $room->status;
            $rental->update(['status' => 'active']);
            $room->update(['status' => 'occupied']);
            RoomStatusLog::create(['property_id' => $room->property_id, 'room_id' => $room->id, 'from_status' => $from, 'to_status' => 'occupied',
                'note' => 'Sewa dimulai (' . $application->code . ')', 'user_id' => auth()->id()]);
            Tenant::whereKey($rental->tenant_id)->update(['status' => 'active']);
        });

        return back()->with('ok', 'Sewa dimulai. Kamar kini Terisi.');
    }

    public function idDocument(App $application)
    {
        abort_unless($application->id_doc_path && Storage::exists($application->id_doc_path), 404);

        return Storage::response($application->id_doc_path);
    }

    private function activation(string $url, string $name, string $phone): array
    {
        $property = Property::find($this->pid());
        $msg = 'Halo ' . $name . ', pengajuan sewa Anda di ' . $property->name . ' telah disetujui. Silakan aktifkan akun penghuni Anda melalui tautan berikut (berlaku ' . Invitations::DAYS . ' hari): ' . $url . ' Terima kasih.';

        return ['url' => $url, 'wa' => 'https://wa.me/' . Billing::phone($phone) . '?text=' . rawurlencode($msg)];
    }
}
