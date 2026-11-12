<?php

namespace App\Models\Scopes;

use App\Support\CurrentProperty;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;

/** Fail-closed: a signed-in user without a property sees nothing. Guests/console are not filtered. */
class PropertyScope implements Scope
{
    public function apply(Builder $builder, Model $model): void
    {
        if (! auth()->check()) return;

        $id = CurrentProperty::id();
        $id ? $builder->where($model->getTable() . '.property_id', $id) : $builder->whereRaw('1 = 0');
    }
}
