<?php

namespace App\Http\Controllers\Site;

use App\Http\Controllers\Controller;
use App\Models\Floor;
use App\Models\Room;
use App\Models\RoomType;
use App\Support\Owner\Fmt;
use App\Support\SiteProperty;

/** Public pages read from the database. Never exposes tenant data: only room code, status and prices. */
class PageController extends Controller
{
    public static function typeView(RoomType $t): array
    {
        $two = $t->capacity >= 2 && $t->rate_two;

        return [
            'name' => $t->name, 'size' => $t->size ?: '—', 'cap' => (int) $t->capacity, 'monthly' => (int) $t->rate_one,
            'price' => $two ? Fmt::rp($t->rate_one) . ' – ' . Fmt::rp($t->rate_two) : Fmt::rp($t->rate_one),
            'daily' => $t->allows_daily ? (int) $t->daily_rate : null, 'deposit' => (int) $t->deposit_amount,
            'note' => $two ? 'Untuk 1–2 orang: 1 orang ' . Fmt::rp($t->rate_one) . ', 2 orang ' . Fmt::rp($t->rate_two) . ' per bulan.' : 'Satu penghuni per kamar.',
            'rule' => $t->capacity >= 2 ? 'Tidak diperuntukkan bagi pasangan suami istri.' : ($t->allows_daily ? 'Sewa harian maksimal satu orang per kamar.' : 'Satu penghuni per kamar.'),
        ];
    }

    public static function types(): array
    {
        $p = SiteProperty::find();
        if (! $p) return [];

        return RoomType::withoutGlobalScopes()->where('property_id', $p->id)->orderBy('name')->get()->mapWithKeys(fn ($t) => [$t->id => self::typeView($t)])->all();
    }

    public function home()
    {
        $p = SiteProperty::find();

        return view('site.home', ['types' => self::types(), 'free' => $p ? Room::withoutGlobalScopes()->where('property_id', $p->id)->where('status', 'available')->count() : 0]);
    }

    public function about()
    {
        return view('site.about');
    }

    public function rooms()
    {
        $p = SiteProperty::find();
        $floors = ! $p ? [] : Floor::withoutGlobalScopes()->where('property_id', $p->id)->orderBy('sort_order')->get()->mapWithKeys(fn ($f) => [
            $f->name => Room::withoutGlobalScopes()->where('floor_id', $f->id)->orderBy('code')->get()
                ->map(fn ($r) => ['code' => $r->code, 'st' => $r->statusLabel()])->all(),
        ])->all();

        return view('site.rooms', ['floors' => $floors, 'types' => self::types()]);
    }

    public function room(int $id)
    {
        $p = SiteProperty::get();
        $t = RoomType::withoutGlobalScopes()->where('property_id', $p->id)->findOrFail($id);

        return view('site.room', ['slug' => $t->id, 't' => self::typeView($t)]);
    }
}
