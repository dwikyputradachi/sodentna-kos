<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Property;
use App\Models\WaitingListEntry as Entry;
use App\Support\CurrentProperty;
use App\Support\Owner\Billing;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WaitlistController extends Controller
{
    public function index()
    {
        $pid = CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
        $property = Property::findOrFail($pid);

        $entries = Entry::with('type')->orderBy('id')->get();
        $count = [];
        $rows = $entries->map(function (Entry $e) use (&$count, $property) {
            $pos = null;
            if ($e->status === 'waiting') $pos = $count[(int) $e->room_type_id] = ($count[(int) $e->room_type_id] ?? 0) + 1;
            $msg = 'Halo ' . $e->name . ', kami dari ' . $property->name . '. Kamar ' . ($e->type->name ?? 'kami') . ' kini tersedia. Apakah Anda masih berminat? Silakan balas pesan ini atau ajukan sewa melalui website. Terima kasih.';

            return ['e' => $e, 'pos' => $pos, 'wa' => 'https://wa.me/' . Billing::phone($e->phone) . '?text=' . rawurlencode($msg)];
        });

        return view('owner.waitlist', ['rows' => $rows->sortBy(fn ($r) => [$r['e']->status === 'waiting' ? 0 : 1, $r['e']->id])->values()]);
    }

    public function status(Request $r, Entry $entry)
    {
        $d = $r->validate(['status' => ['required', Rule::in(array_keys(Entry::STATUSES))]]);

        if ($d['status'] !== $entry->status) {
            $entry->update(['status' => $d['status'], 'offered_at' => $d['status'] === 'offered' ? now() : $entry->offered_at]);
        }

        return back()->with('ok', 'Status daftar tunggu diperbarui.');
    }
}
