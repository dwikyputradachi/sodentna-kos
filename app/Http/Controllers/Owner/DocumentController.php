<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Support\CurrentProperty;
use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function index(Request $r)
    {
        CurrentProperty::id() ?? abort(403, 'Akun Anda belum terhubung ke properti.');
        $q = trim((string) $r->query('q'));

        $query = Payment::with(['receipt', 'invoice.rental.tenant', 'invoice.rental.room'])->whereHas('receipt')->orderByDesc('paid_on')->orderByDesc('id');
        if ($q !== '') {
            $query->where(fn ($w) => $w->whereHas('receipt', fn ($x) => $x->where('number', 'like', "%$q%"))
                ->orWhereHas('invoice.rental.tenant', fn ($x) => $x->where('name', 'like', "%$q%"))
                ->orWhereHas('invoice.rental.room', fn ($x) => $x->where('code', 'like', "%$q%")));
        }

        return view('owner.documents', ['payments' => $query->limit(200)->get(), 'q' => $q]);
    }
}
